<?php

use App\Core\Auth;
use App\Core\Security;

$role = Auth::user()['role'] ?? '';
$canManageImports = in_array($role, ['ADMIN_TECHNIQUE', 'CHEF_SERVICE'], true);
$canViewAudit = in_array($role, ['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE'], true); ?>
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
    <div>
        <span class="eyebrow">ESPACE ADMINISTRATIF</span>
        <h1 class="h2 mt-1">Tableau de bord</h1>
        <p class="text-muted mb-0">Vue synthétique de la gestion des diplômes.</p>
    </div>
    <span class="user-chip"><?= Security::e(\App\Core\Auth::user()['nom'] ?? '') ?></span>
</div>
<div class="row g-3">
    <?php foreach (['Étudiants' => 'etudiants', 'Diplômes' => 'diplomes', 'Dossiers déposés' => 'dossiers_deposes', 'Prêts à remettre' => 'disponibles', 'Diplômes remis' => 'remis'] as $label => $key): ?>
        <div class="col-6 col-lg">
            <div class="stat-card"><span><?= Security::e($label) ?></span><strong><?= (int) $stats[$key] ?></strong></div>
        </div>
    <?php endforeach; ?>
</div>
<div class="row g-3 mt-3">
    <div class="col-md-6"><a class="action-card" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/etudiants"><b>Gestion des étudiants</b><span>Consulter et enregistrer les dossiers étudiants.</span></a></div>
    <div class="col-md-6"><a class="action-card" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/diplomes"><b>Gestion des diplômes</b><span>Suivre les différentes étapes de traitement.</span></a></div>
    <?php if ($canManageImports): ?><div class="col-md-6"><a class="action-card" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/imports"><b>Importation des données</b><span>Préparer et contrôler les fichiers Excel.</span></a></div><?php endif; ?>
    <div class="col-md-6"><a class="action-card" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/retraits"><b>Remises enregistrées</b><span>Consulter les remises de diplômes enregistrées au guichet.</span></a></div>
    <?php if ($canViewAudit): ?><div class="col-md-6"><a class="action-card" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/audit"><b>Journal des opérations</b><span>Consulter les actions administratives tracées.</span></a></div><?php endif; ?>
</div>