<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Auth;
use App\Core\Flash;
use App\Core\RateLimiter;
use App\Services\AuditService;
use App\Services\TurnstileService;

final class AuthController extends Controller
{
    public function login(): void
    {
        $data = ['title' => 'Connexion administration'];
        $attempts = RateLimiter::count('admin-login', Security::clientKey(), 900);
        if ($attempts !== null && $attempts >= 2) {
            if (TurnstileService::isConfigured()) $data['turnstileSiteKey'] = TurnstileService::siteKey();
            else $data['turnstileUnavailable'] = true;
        }
        $this->view('auth/login', $data);
    }
    public function authenticate(): void
    {
        Security::verifyCsrf();
        $key = Security::clientKey();
        $attempts = RateLimiter::hit('admin-login', $key, 5, 900);
        if ($attempts === null) {
            http_response_code(503);
            Flash::set('danger', 'Connexion temporairement indisponible. Réessayez plus tard.');
            $this->redirect('/connexion');
        }
        if ($attempts < 0) {
            http_response_code(429);
            Flash::set('danger', 'Connexion temporairement indisponible. Réessayez plus tard.');
            $this->redirect('/connexion');
        }
        $turnstileToken = $_POST['cf-turnstile-response'] ?? null;
        if ($attempts > 2 && (!is_string($turnstileToken) || !TurnstileService::verify($turnstileToken, 'admin_login'))) {
            Flash::set('danger', TurnstileService::isConfigured() ? 'La vérification anti-robot a échoué. Réessayez.' : 'La vérification anti-robot n’est pas configurée. Contactez un administrateur.');
            $this->redirect('/connexion');
        }
        $emailInput = $_POST['email'] ?? null;
        $passwordInput = $_POST['password'] ?? null;
        $email = is_string($emailInput) ? trim($emailInput) : '';
        $pass = is_string($passwordInput) ? $passwordInput : '';
        if (Auth::attempt($email, $pass)) {
            RateLimiter::clear('admin-login', $key, 900);
            AuditService::log('CONNEXION', 'utilisateurs');
            $this->redirect('/admin');
        }
        Flash::set('danger', 'Identifiants incorrects ou compte désactivé.');
        $this->redirect('/connexion');
    }
    public function logout(): void
    {
        Security::verifyCsrf();
        AuditService::log('DECONNEXION', 'utilisateurs');
        Auth::logout();
        $this->redirect('/');
    }
}
