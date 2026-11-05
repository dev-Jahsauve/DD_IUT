<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Core\Flash;
use App\Services\AuditService;
use App\Services\ReceiptService;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE', 'AGENT']);
        $pdo = Database::connection();
        $stats = [
            'etudiants' => (int)$pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn(),
            'diplomes' => (int)$pdo->query("SELECT COUNT(*) FROM diplomes")->fetchColumn(),
            'disponibles' => (int)$pdo->query("SELECT COUNT(*) FROM diplomes WHERE statut='PRET_A_REMETTRE'")->fetchColumn(),
            'remis' => (int)$pdo->query("SELECT COUNT(*) FROM diplomes WHERE statut='REMIS'")->fetchColumn(),
            'dossiers_deposes' => (int)$pdo->query("SELECT COUNT(*) FROM diplomes WHERE statut='DOSSIER_DEPOSE'")->fetchColumn()
        ];
        $this->view('admin/dashboard', ['title' => 'Tableau de bord', 'stats' => $stats]);
    }
    public function students(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE', 'AGENT']);
        $rows = Database::connection()->query("SELECT e.*, f.nom AS filiere FROM etudiants e LEFT JOIN filieres f ON f.id=e.filiere_id ORDER BY e.nom,e.prenoms LIMIT 500")->fetchAll();
        $this->view('admin/students', ['title' => 'Étudiants', 'rows' => $rows]);
    }
    public function createStudent(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'CHEF_SERVICE']);
        Security::verifyCsrf();
        $matInput = $_POST['matricule'] ?? null;
        $nomInput = $_POST['nom'] ?? null;
        $prenomsInput = $_POST['prenoms'] ?? null;
        $mat = is_string($matInput) ? trim($matInput) : '';
        $nom = is_string($nomInput) ? trim($nomInput) : '';
        $prenoms = is_string($prenomsInput) ? trim($prenomsInput) : '';
        $date = $_POST['date_naissance'] ?? null;
        $birthDate = is_string($date) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        $dateErrors = \DateTimeImmutable::getLastErrors();
        $validDate = $birthDate !== false && $birthDate->format('Y-m-d') === $date
            && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0));
        if ($nom === '' || $prenoms === '' || !$validDate) {
            Flash::set('danger', 'Veuillez renseigner le nom, les prénoms et une date de naissance valide.');
            $this->redirect('/admin/etudiants');
        }
        $s = Database::connection()->prepare("INSERT INTO etudiants (matricule,nom,prenoms,date_naissance,filiere_id,annee_entree) VALUES (?,?,?,?,NULL,NULL)");
        try {
            $s->execute([$mat === '' ? null : $mat, $nom, $prenoms, $date]);
            AuditService::log('CREATION', 'etudiants', (int)Database::connection()->lastInsertId());
            Flash::set('success', 'Étudiant enregistré.');
        } catch (\PDOException $e) {
            Flash::set('danger', 'Enregistrement impossible : matricule déjà utilisé ou donnée invalide.');
        }
        $this->redirect('/admin/etudiants');
    }
    public function diplomas(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE', 'AGENT']);
        $rows = Database::connection()->query("SELECT d.*,e.matricule,e.nom,e.prenoms FROM diplomes d JOIN etudiants e ON e.id=d.etudiant_id ORDER BY d.id DESC LIMIT 500")->fetchAll();
        $students = Database::connection()->query("SELECT id,matricule,nom,prenoms,date_naissance FROM etudiants ORDER BY nom,prenoms LIMIT 500")->fetchAll();
        $this->view('admin/diplomas', ['title' => 'Diplômes', 'rows' => $rows, 'students' => $students]);
    }
    public function createDiploma(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'CHEF_SERVICE']);
        Security::verifyCsrf();
        $pdo = Database::connection();
        $studentIdInput = $_POST['etudiant_id'] ?? null;
        $typeInput = $_POST['type_diplome'] ?? null;
        $yearInput = $_POST['annee_obtention'] ?? null;
        $studentId = is_string($studentIdInput) || is_int($studentIdInput) ? filter_var($studentIdInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $type = is_string($typeInput) ? trim($typeInput) : '';
        $year = is_string($yearInput) || is_int($yearInput) ? filter_var($yearInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => (int)date('Y') + 1]]) : false;
        if ($studentId === false || $type === '' || mb_strlen($type) > 120 || $year === false) {
            Flash::set('danger', 'Veuillez fournir un étudiant, un type de diplôme et une année valides.');
            $this->redirect('/admin/diplomes');
        }
        $s = $pdo->prepare("INSERT INTO diplomes (etudiant_id,type_diplome,annee_obtention,statut) VALUES (?,?,?,'DOSSIER_DEPOSE')");
        try {
            $s->execute([$studentId, $type, $year]);
            AuditService::log('CREATION', 'diplomes', (int)$pdo->lastInsertId(), null, ['statut' => 'DOSSIER_DEPOSE']);
            Flash::set('success', 'Diplôme enregistré avec le statut « Dossier déposé ».');
        } catch (\Throwable $e) {
            error_log('Diploma creation failed: ' . $e->getMessage());
            Flash::set('danger', 'Impossible de créer ce diplôme. Vérifiez les informations.');
        }
        $this->redirect('/admin/diplomes');
    }
    public function transitionDiploma(string $id): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'CHEF_SERVICE']);
        Security::verifyCsrf();
        if (!ctype_digit($id) || (int)$id < 1) {
            Flash::set('danger', 'Diplôme introuvable.');
            $this->redirect('/admin/diplomes');
        }
        $next = $_POST['statut'] ?? '';
        $allowed = [
            'DOSSIER_DEPOSE' => ['EN_COURS_ETABLISSEMENT', 'ANOMALIE_BLOQUE'],
            'EN_COURS_ETABLISSEMENT' => ['SIGNE', 'ANOMALIE_BLOQUE'],
            'SIGNE' => ['PRET_A_REMETTRE', 'ANOMALIE_BLOQUE'],
            'PRET_A_REMETTRE' => ['ANOMALIE_BLOQUE'],
            'ANOMALIE_BLOQUE' => [],
            'REMIS' => [],
        ];
        $dateColumns = [
            'EN_COURS_ETABLISSEMENT' => 'date_transmission',
            'SIGNE' => 'date_signature',
            'PRET_A_REMETTRE' => 'date_mise_a_disposition',
        ];
        $pdo = Database::connection();
        try {
            $pdo->beginTransaction();
            $query = $pdo->prepare('SELECT statut FROM diplomes WHERE id=? FOR UPDATE');
            $query->execute([(int)$id]);
            $current = $query->fetchColumn();
            if ($current === false || !in_array($next, $allowed[$current] ?? [], true)) {
                throw new \DomainException('Cette transition de statut n’est pas autorisée.');
            }
            $dateColumn = $dateColumns[$next] ?? null;
            $sql = 'UPDATE diplomes SET statut=?,updated_at=NOW()';
            if ($dateColumn !== null) $sql .= ',`' . $dateColumn . '`=CURDATE()';
            $sql .= ' WHERE id=?';
            $update = $pdo->prepare($sql);
            $update->execute([$next, (int)$id]);
            AuditService::log('TRANSITION_STATUT', 'diplomes', (int)$id, ['statut' => $current], ['statut' => $next]);
            $pdo->commit();
            Flash::set('success', 'Statut du diplôme mis à jour.');
        } catch (\DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            Flash::set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Diploma status transition failed: ' . $e->getMessage());
            Flash::set('danger', 'La mise à jour du statut a échoué. Réessayez ou contactez un administrateur.');
        }
        $this->redirect('/admin/diplomes');
    }
    public function audit(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE']);
        $rows = Database::connection()->query("SELECT j.*,u.nom AS agent FROM journal_audit j LEFT JOIN utilisateurs u ON u.id=j.utilisateur_id ORDER BY j.id DESC LIMIT 300")->fetchAll();
        $this->view('admin/audit', ['title' => 'Journal des opérations', 'rows' => $rows]);
    }
    public function withdrawals(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE', 'AGENT']);
        $pdo = Database::connection();
        $rows = $pdo->query("SELECT r.*,d.type_diplome,d.annee_obtention,e.matricule,e.nom,e.prenoms,u.nom AS agent FROM retraits r JOIN diplomes d ON d.id=r.diplome_id JOIN etudiants e ON e.id=d.etudiant_id LEFT JOIN utilisateurs u ON u.id=r.agent_id ORDER BY r.id DESC LIMIT 300")->fetchAll();
        $available = $pdo->query("SELECT d.id,d.type_diplome,d.annee_obtention,e.matricule,e.nom,e.prenoms,e.date_naissance FROM diplomes d JOIN etudiants e ON e.id=d.etudiant_id WHERE d.statut='PRET_A_REMETTRE' ORDER BY e.nom,e.prenoms LIMIT 500")->fetchAll();
        $this->view('withdrawals/index', ['title' => 'Remises enregistrées', 'rows' => $rows, 'available' => $available]);
    }
    public function receipt(string $id): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE', 'AGENT']);
        if (!ctype_digit($id) || (int)$id < 1) {
            http_response_code(404);
            exit('Reçu introuvable.');
        }
        $query = Database::connection()->prepare("SELECT r.reference_recu,r.date_retrait,r.observations,e.nom,e.prenoms,e.matricule,d.type_diplome,d.annee_obtention,u.nom AS agent FROM retraits r JOIN diplomes d ON d.id=r.diplome_id JOIN etudiants e ON e.id=d.etudiant_id LEFT JOIN utilisateurs u ON u.id=r.agent_id WHERE r.id=? LIMIT 1");
        $query->execute([(int)$id]);
        $receipt = $query->fetch();
        if (!$receipt) {
            http_response_code(404);
            exit('Reçu introuvable.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="recu-remise-' . (int)$id . '.pdf"');
        header('Cache-Control: private, no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
        echo ReceiptService::renderPdf($receipt);
        exit;
    }
    public function handover(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'CHEF_SERVICE', 'AGENT']);
        Security::verifyCsrf();
        $pdo = Database::connection();
        $idInput = $_POST['diplome_id'] ?? null;
        $id = is_string($idInput) || is_int($idInput) ? filter_var($idInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        if ($id === false) {
            Flash::set('danger', 'Veuillez sélectionner un diplôme valide.');
            $this->redirect('/admin/retraits');
        }
        if (($_POST['identite_verifiee'] ?? null) !== '1' || ($_POST['signature_recue'] ?? null) !== '1') {
            Flash::set('danger', 'La vérification d’identité et la signature du bénéficiaire sont obligatoires.');
            $this->redirect('/admin/retraits');
        }
        try {
            $pdo->beginTransaction();
            $q = $pdo->prepare("SELECT * FROM diplomes WHERE id=? FOR UPDATE");
            $q->execute([$id]);
            $d = $q->fetch();
            if (!$d || $d['statut'] !== 'PRET_A_REMETTRE') throw new \RuntimeException('Ce diplôme n’est pas prêt à être remis.');
            $ref = 'IUT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $s = $pdo->prepare("INSERT INTO retraits (diplome_id,agent_id,date_retrait,identite_verifiee,signature_recue,reference_recu,observations) VALUES (?,?,NOW(),1,1,?,?)");
            $observations = $_POST['observations'] ?? '';
            $s->execute([$id, Auth::user()['id'], $ref, is_string($observations) ? trim($observations) : '']);
            $pdo->prepare("UPDATE diplomes SET statut='REMIS', date_remise=NOW(), updated_at=NOW() WHERE id=?")->execute([$id]);
            AuditService::log('REMISE_DIPLOME', 'diplomes', $id, ['statut' => $d['statut']], ['statut' => 'REMIS']);
            $pdo->commit();
            Flash::set('success', 'Remise enregistrée. Référence du reçu : ' . $ref);
        } catch (\RuntimeException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            Flash::set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Diploma handover failed: ' . $e->getMessage());
            Flash::set('danger', 'La remise n’a pas pu être enregistrée. Vérifiez le statut du diplôme et réessayez.');
        }
        $this->redirect('/admin/retraits');
    }
}
