<?php

use App\Core\Security; ?><h1 class="h2 mb-3">Journal des opérations</h1>
<div class="panel table-responsive">
    <table class="table table-sm mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Agent</th>
                <th>Action</th>
                <th>Entité</th>
                <th>ID</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
                    <td><?= Security::e($r['created_at']) ?></td>
                    <td><?= Security::e($r['agent'] ?? 'Système') ?></td>
                    <td><?= Security::e($r['action']) ?></td>
                    <td><?= Security::e($r['entite']) ?></td>
                    <td><?= (int)$r['entite_id'] ?></td>
                </tr><?php endforeach; ?></tbody>
    </table>
</div>