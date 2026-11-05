<?php

use App\Core\Security; ?><div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h2">Étudiants</h1><span class="text-muted small">Liste limitée aux 500 derniers résultats alphabétiques</span>
</div>
<div class="panel p-3 mb-4">
    <h2 class="h5">Enregistrer un étudiant</h2>
    <form class="row g-2" method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/etudiants/creer"><input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
        <div class="col-md-3"><input class="form-control" name="matricule" placeholder="Matricule (facultatif)" maxlength="60"></div>
        <div class="col-md-3"><input class="form-control" name="nom" placeholder="Nom" required maxlength="120"></div>
        <div class="col-md-3"><input class="form-control" name="prenoms" placeholder="Prénoms" required maxlength="160"></div>
        <div class="col-md-2"><input class="form-control" type="date" name="date_naissance" required></div>
        <div class="col-md-1"><button class="btn btn-iut w-100" type="submit" aria-label="Enregistrer l’étudiant">+</button></div>
    </form>
</div>
<div class="panel table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom et prénoms</th>
                <th>Date de naissance</th>
                <th>Filière</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
                    <td><?= Security::e($r['matricule'] ?? '—') ?></td>
                    <td><?= Security::e($r['nom'] . ' ' . $r['prenoms']) ?></td>
                    <td><?= Security::e($r['date_naissance']) ?></td>
                    <td><?= Security::e($r['filiere'] ?? '—') ?></td>
                </tr><?php endforeach; ?></tbody>
    </table>
</div>