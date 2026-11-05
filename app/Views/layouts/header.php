<?php

use App\Core\Security;
use App\Core\Auth;
use App\Core\Flash;

$flash = Flash::get();
$role = Auth::user()['role'] ?? '';
$canManageImports = in_array($role, ['ADMIN_TECHNIQUE', 'CHEF_SERVICE'], true);
$canViewAudit = in_array($role, ['ADMIN_TECHNIQUE', 'SUPERVISEUR', 'CHEF_SERVICE'], true); ?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Security::e($title ?? 'IUT-Diplômes') ?> | IUT de Douala</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/assets/css/app.css">
    <link rel="stylesheet" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/assets/css/institutional.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark iut-nav">
        <div class="container"><a class="navbar-brand d-flex align-items-center gap-2" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/"><img src="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/assets/img/logo-iut-douala.png" alt="Logo IUT Douala"><span>IUT <b>Diplômes</b></span></a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Ouvrir le menu"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/">Accueil</a>
                    <?php if (Auth::check()): ?>
                        <a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin">Administration</a>
                        <a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/etudiants">Étudiants</a>
                        <a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/diplomes">Diplômes</a>
                        <?php if ($canManageImports): ?><a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/imports">Imports</a><?php endif; ?>
                        <a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/retraits">Retraits</a>
                        <?php if ($canViewAudit): ?><a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/audit">Journal d’audit</a><?php endif; ?>
                        <form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/deconnexion" class="ms-lg-2"><input type="hidden" name="_csrf" value="<?= Security::csrf() ?>"><button class="btn btn-sm btn-outline-light">Déconnexion</button></form>
                    <?php else: ?>
                        <a class="nav-link" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/connexion">Administration</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <?php if ($flash): ?><div class="alert alert-<?= Security::e($flash['type']) ?>"><?= Security::e($flash['message']) ?></div><?php endif; ?>