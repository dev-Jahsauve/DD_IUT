<?php

use App\Core\Security; ?>
<section class="hero p-4 p-lg-5 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8"><span class="eyebrow">INSTITUT UNIVERSITAIRE DE TECHNOLOGIE</span>
            <h1 class="display-5 fw-bold mt-2">Consultez la disponibilité de votre diplôme.</h1>
            <p class="lead">Accédez aux informations relatives à votre diplôme et aux démarches de retrait auprès des services compétents de l'IUT de Douala.</p>
        </div>
        <div class="col-lg-4 text-lg-end"><img class="hero-logo" src="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/assets/img/logo-iut-douala.png" alt="IUT Douala"></div>
    </div>
</section>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel p-4">
            <h2 class="h4 mb-2">Vérifier la disponibilité</h2>
            <p class="text-muted">Renseignez votre nom, vos prénoms, votre date de naissance et l’année d’obtention du diplôme.</p>
            <div id="search-message" class="alert alert-warning" role="status" aria-live="polite" <?= isset($error) ? '' : 'hidden' ?>><?= isset($error) ? Security::e($error) : '' ?></div>
            <form id="public-search-form" data-async-form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/recherche">
                <input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
                <div class="mb-3"><label class="form-label" for="search-nom">Nom</label><input class="form-control form-control-lg" id="search-nom" name="nom" required maxlength="120" autocomplete="family-name"></div>
                <div class="mb-3"><label class="form-label" for="search-prenoms">Prénoms</label><input class="form-control form-control-lg" id="search-prenoms" name="prenoms" required maxlength="160" autocomplete="given-name"></div>
                <div class="mb-3"><label class="form-label" for="search-birth">Date de naissance</label><input class="form-control form-control-lg" id="search-birth" type="date" name="date_naissance" required></div>
                <div class="mb-3"><label class="form-label" for="search-year">Année d’obtention</label><input class="form-control form-control-lg" id="search-year" type="number" name="annee_obtention" min="1900" max="<?= (int)date('Y') + 1 ?>" inputmode="numeric" required></div>
                <?php if (!empty($turnstileUnavailable)): ?><div class="alert alert-warning">La vérification anti-robot n’est pas configurée par l’administrateur.</div><?php endif; ?>
                <div id="public-turnstile" class="mb-3" data-sitekey="<?= Security::e($turnstileSiteKey ?? '') ?>" data-action="public_search" <?= !empty($turnstileSiteKey) ? '' : 'hidden' ?>></div>
                <button class="btn btn-iut btn-lg w-100" type="submit">Vérifier</button>
            </form>
            <div id="search-result" class="result-box mt-4" <?= !empty($searched) ? '' : 'hidden' ?>>
                <?php if (!empty($results)): ?>
                    <?php $labels = ['DOSSIER_DEPOSE' => 'Dossier déposé', 'EN_COURS_ETABLISSEMENT' => 'Diplôme en cours d’établissement', 'SIGNE' => 'Diplôme signé', 'PRET_A_REMETTRE' => 'Diplôme disponible – prêt à être remis', 'REMIS' => 'Diplôme remis']; ?>
                    <h3 class="h5">Résultat de la consultation</h3>
                    <?php foreach ($results as $item): ?>
                        <p class="mb-1"><strong><?= Security::e($item['type_diplome']) ?> · <?= (int)$item['annee_obtention'] ?></strong><br>Statut : <?= Security::e($labels[$item['statut']] ?? 'Information à confirmer auprès de la scolarité') ?></p>
                    <?php endforeach; ?>
                    <p class="small text-muted mb-0 mt-2">La remise nécessite la vérification des pièces au guichet.</p>
                <?php else: ?>
                    <?php if (!empty($searched)): ?>
                        <p class="mb-0">Aucune correspondance trouvée. Vérifiez l’orthographe, la date et l’année, ou rapprochez-vous du service de la scolarité.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel p-4 h-100">
            <h2 class="h5">Informations importantes</h2>
            <ul class="info-list">
                <li>La consultation ne remplace pas la vérification des pièces au guichet.</li>
                <li>Aucun numéro de diplôme, contact ou renseignement personnel détaillé n'est affiché publiquement.</li>
                <li>La remise définitive est enregistrée par un agent habilité.</li>
            </ul>
            <div class="notice mt-4"><strong>Besoin d'assistance ?</strong>
                <p class="mb-0">Contactez le service de scolarité de l'IUT par les canaux institutionnels habituels.</p>
            </div>
        </div>
    </div>
</div>