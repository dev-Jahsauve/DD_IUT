<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Security;
use App\Core\Database;
use App\Core\Flash;
use App\Services\AuditService;

final class ImportController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'CHEF_SERVICE']);
        $rows = Database::connection()->query("SELECT * FROM lots_importation ORDER BY id DESC LIMIT 50")->fetchAll();
        $this->view('imports/index', [
            'title' => 'Import des fichiers Excel',
            'rows' => $rows,
            'xlsxAvailable' => extension_loaded('zip') && class_exists(\ZipArchive::class),
        ]);
    }
    public function upload(): void
    {
        Auth::requireRole(['ADMIN_TECHNIQUE', 'CHEF_SERVICE']);
        Security::verifyCsrf();
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            Flash::set('danger', 'Fichier non reçu.');
            $this->redirect('/admin/imports');
        }
        $f = $_FILES['fichier'];
        if ($f['size'] > 10 * 1024 * 1024) {
            Flash::set('danger', 'Fichier trop volumineux (10 Mo maximum).');
            $this->redirect('/admin/imports');
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            Flash::set('danger', 'Formats acceptés : XLSX, XLS, CSV.');
            $this->redirect('/admin/imports');
        }
        if ($ext === 'xlsx' && (!extension_loaded('zip') || !class_exists(\ZipArchive::class))) {
            Flash::set('danger', 'Les fichiers XLSX sont indisponibles : activez l’extension ZIP dans le PHP d’Apache puis redémarrez Apache.');
            $this->redirect('/admin/imports');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'application/zip', 'text/csv', 'text/plain', 'application/octet-stream'];
        if (!in_array($mime, $allowed, true)) {
            Flash::set('danger', 'Type de fichier non autorisé.');
            $this->redirect('/admin/imports');
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir = dirname(__DIR__, 2) . '/storage/imports';
        if (!is_dir($dir)) mkdir($dir, 0700, true);
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
            Flash::set('danger', 'Impossible de stocker le fichier.');
            $this->redirect('/admin/imports');
        }
        $s = Database::connection()->prepare("INSERT INTO lots_importation (utilisateur_id,nom_original,nom_stocke,format_fichier,statut,taille_octets) VALUES (?,?,?,?,'A_VALIDER',?)");
        $s->execute([Auth::user()['id'], $f['name'], $name, $ext, $f['size']]);
        $id = (int)Database::connection()->lastInsertId();
        try {
            $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class) && is_file($autoload)) require_once $autoload;
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) throw new \RuntimeException('Installez les dépendances avec composer install.');
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($dir . '/' . $name);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($dir . '/' . $name);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            $normalizeHeader = static function ($value): string {
                $value = mb_strtolower(trim((string)$value));
                $value = strtr($value, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c']);
                return trim((string)preg_replace('/[^a-z0-9]+/', '_', $value), '_');
            };
            $aliases = ['annee' => 'annee_obtention', 'annee_diplome' => 'annee_obtention', 'date_de_naissance' => 'date_naissance', 'attestation' => 'type_diplome'];
            $headers = array_map(static function ($value) use ($normalizeHeader, $aliases): string {
                $header = $normalizeHeader($value);
                return $aliases[$header] ?? $header;
            }, array_shift($data) ?? []);
            $count = 0;
            foreach ($data as $lineNo => $row) {
                $mapped = [];
                foreach ($headers as $col => $h) if ($h !== '') $mapped[$h] = $row[$col] ?? null;
                $required = ['nom' => 'nom', 'prenoms' => 'prénoms', 'date_naissance' => 'date de naissance', 'annee_obtention' => 'année du diplôme', 'type_diplome' => 'type d’attestation/diplôme'];
                $missing = [];
                foreach ($required as $field => $label) if (empty($mapped[$field])) $missing[] = $label;
                $error = $missing ? 'Champ(s) requis absent(s) ou vide(s) : ' . implode(', ', $missing) . '.' : null;
                $pdo = Database::connection();
                $pdo->prepare("INSERT INTO import_lignes (lot_id,numero_ligne,donnees_json,statut,erreur) VALUES (?,?,?,'A_CONTROLER',?)")
                    ->execute([$id, $lineNo + 2, json_encode($mapped, JSON_UNESCAPED_UNICODE), $error]);
                $count++;
            }
            Database::connection()->prepare("UPDATE lots_importation SET nombre_lignes=? WHERE id=?")->execute([$count, $id]);
            AuditService::log('IMPORT_TELEVERSE', 'lots_importation', $id, null, ['format' => $ext, 'lignes' => $count]);
            Flash::set('success', "Fichier analysé : $count lignes placées en zone de contrôle. Aucune donnée métier n'a encore été intégrée.");
        } catch (\Throwable $e) {
            error_log('Import analysis failed: ' . $e->getMessage());
            Database::connection()->prepare("UPDATE lots_importation SET statut='ERREUR' WHERE id=?")->execute([$id]);
            Flash::set('danger', 'Fichier stocké mais analyse impossible. Vérifiez son format et sa structure.');
        }
        $this->redirect('/admin/imports');
    }
}
