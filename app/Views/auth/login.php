<?php

use App\Core\Security; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="panel p-4 p-lg-5">
            <span class="eyebrow">ESPACE RÉSERVÉ AU PERSONNEL</span>
            <h1 class="h3 mt-2 mb-4">Connexion administrative</h1>
            <form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/connexion">
                <input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
                <div class="mb-3">
                    <label class="form-label" for="admin-email">Adresse e-mail</label>
                    <input class="form-control" id="admin-email" type="email" name="email" required autocomplete="username">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="admin-password">Mot de passe</label>
                    <div class="password-field">
                        <input class="form-control pe-5" id="admin-password" type="password" name="password" required autocomplete="current-password">
                        <button class="password-toggle" type="button" data-toggle-password="admin-password" aria-label="Afficher le mot de passe" aria-controls="admin-password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                                <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4.1-.8" />
                            </svg>
                        </button>
                    </div>
                </div>
                <?php if (!empty($turnstileUnavailable)): ?><div class="alert alert-warning">La vérification anti-robot n’est pas configurée par l’administrateur.</div><?php endif; ?>
                <?php if (!empty($turnstileSiteKey)): ?>
                    <div class="cf-turnstile mb-4" data-sitekey="<?= Security::e($turnstileSiteKey) ?>" data-action="admin_login" data-theme="light" data-size="flexible"></div>
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                <?php endif; ?>
                <button class="btn btn-iut w-100" type="submit">Se connecter</button>
            </form>
            <p class="small text-muted mt-3 mb-0">Accès réservé aux utilisateurs autorisés.</p>
        </div>
    </div>
</div>