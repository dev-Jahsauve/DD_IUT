<?php

use App\Core\Auth;
use App\Core\Security; ?>
<h1 class="h2 mb-3">Diplômes et attestations</h1>
<div class="panel p-3 mb-4">
    <h2 class="h5">Enregistrer un diplôme ou une attestation</h2>
    <form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/diplomes/creer" class="row g-2 align-items-end">
        <input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
        <div class="col-12 col-lg-5">
            <label class="form-label" for="student-id">Étudiant</label>
            <select class="form-select" id="student-id" name="etudiant_id" required>
                <option value="">Sélectionner un étudiant</option>
                <?php foreach ($students as $student): ?>
                    <?php $studentLabel = trim($student['nom'] . ' ' . $student['prenoms']) . ' · ' . $student['date_naissance'] . (!empty($student['matricule']) ? ' · ' . $student['matricule'] : ''); ?>
                    <option value="<?= (int)$student['id'] ?>"><?= Security::e($studentLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <label class="form-label" for="diploma-type">Type de diplôme / attestation</label>
            <input class="form-control" id="diploma-type" name="type_diplome" maxlength="120" required placeholder="Ex. attestation de réussite">
        </div>
        <div class="col-8 col-md-4 col-lg-2">
            <label class="form-label" for="diploma-year">Année d’obtention</label>
            <input class="form-control" id="diploma-year" type="number" name="annee_obtention" min="1900" max="<?= (int)date('Y') + 1 ?>" required>
        </div>
        <div class="col-4 col-md-2 col-lg-1"><button class="btn btn-iut w-100" type="submit" aria-label="Enregistrer le diplôme">+</button></div>
    </form>
</div>
<div class="panel table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Étudiant</th>
                <th>Document</th>
                <th>Année</th>
                <th>Statut</th>
                <th>Étape</th>
            </tr>
        </thead>
        <tbody>
            <?php $statusLabels = ['DOSSIER_DEPOSE' => 'Dossier déposé', 'EN_COURS_ETABLISSEMENT' => 'En cours d’établissement', 'SIGNE' => 'Signé', 'PRET_A_REMETTRE' => 'Prêt à remettre', 'REMIS' => 'Remis', 'ANOMALIE_BLOQUE' => 'Bloqué · anomalie']; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $nextStates = [
                    'DOSSIER_DEPOSE' => ['EN_COURS_ETABLISSEMENT', 'Transmettre au service d’établissement'],
                    'EN_COURS_ETABLISSEMENT' => ['SIGNE', 'Marquer comme signé'],
                    'SIGNE' => ['PRET_A_REMETTRE', 'Déclarer prêt au retrait'],
                ];
                $next = $nextStates[$row['statut']] ?? null;
                $canTransition = in_array(Auth::user()['role'] ?? '', ['ADMIN_TECHNIQUE', 'CHEF_SERVICE'], true);
                $canFlag = $canTransition && in_array($row['statut'], ['DOSSIER_DEPOSE', 'EN_COURS_ETABLISSEMENT', 'SIGNE', 'PRET_A_REMETTRE'], true);
                ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><?= Security::e(trim(($row['nom'] ?? '') . ' ' . ($row['prenoms'] ?? ''))) ?><br><span class="small text-muted"><?= Security::e($row['matricule'] ?? 'Sans matricule') ?></span></td>
                    <td><?= Security::e($row['type_diplome']) ?></td>
                    <td><?= (int)$row['annee_obtention'] ?></td>
                    <td><span class="status"><?= Security::e($statusLabels[$row['statut']] ?? $row['statut']) ?></span></td>
                    <td class="d-flex flex-wrap gap-2">
                        <?php if ($canTransition && $next): ?>
                            <form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/diplomes/<?= (int)$row['id'] ?>/statut">
                                <input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
                                <input type="hidden" name="statut" value="<?= Security::e($next[0]) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit"><?= Security::e($next[1]) ?></button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canFlag): ?>
                            <form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/diplomes/<?= (int)$row['id'] ?>/statut">
                                <input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
                                <input type="hidden" name="statut" value="ANOMALIE_BLOQUE">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Bloquer pour anomalie</button>
                            </form>
                        <?php endif; ?>
                        <?php if (!$canTransition): ?><span class="text-muted small">Lecture seule</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>