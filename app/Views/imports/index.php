<?php

use App\Core\Security; ?><h1 class="h2 mb-2">Importation des fichiers Excel</h1>
<p class="text-muted">Les fichiers sont placés en zone temporaire. L'import ne modifie pas automatiquement les dossiers étudiants ni les diplômes.</p><?php if (empty($xlsxAvailable)): ?><div class="alert alert-warning">Le PHP d’Apache ne charge pas ZipArchive : les fichiers XLSX sont désactivés. Activez extension=zip dans C:\xampp\php\php.ini puis redémarrez Apache dans XAMPP. Les fichiers CSV et XLS restent acceptés.</div><?php endif; ?><div class="panel p-4 mb-4">
    <form method="post" enctype="multipart/form-data" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/imports/televerser"><input type="hidden" name="_csrf" value="<?= Security::csrf() ?>"><label class="form-label">Sélectionner un fichier XLSX, XLS ou CSV (10 Mo maximum)</label><input class="form-control mb-3" type="file" name="fichier" accept=".xlsx,.xls,.csv" required><button class="btn btn-iut">Analyser le fichier</button></form>
</div>
<div class="panel table-responsive">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Fichier source</th>
                <th>Format</th>
                <th>Lignes</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
                    <td><?= Security::e($r['created_at']) ?></td>
                    <td><?= Security::e($r['nom_original']) ?></td>
                    <td><?= Security::e(strtoupper($r['format_fichier'])) ?></td>
                    <td><?= (int)$r['nombre_lignes'] ?></td>
                    <td><?= Security::e($r['statut']) ?></td>
                </tr><?php endforeach; ?></tbody>
    </table>
</div>