<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Env;
use App\Core\RateLimiter;
use App\Services\SearchService;
use App\Services\TurnstileService;

final class PublicController extends Controller
{
    private function respond(array $data, int $status = 200): void
    {
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            $data['ok'] = $status < 400;
            $this->json($data, $status);
        }
        if ($status !== 200) http_response_code($status);
        $this->view('public/home', $data);
    }

    public function home(): void
    {
        $key = Security::clientKey();
        $attempts = RateLimiter::count('public-search', $key, 900);
        $data = ['title' => 'Consultation des diplômes', 'result' => null];
        if ($attempts !== null && $attempts >= 3) {
            if (TurnstileService::isConfigured()) $data['turnstileSiteKey'] = TurnstileService::siteKey();
            else $data['turnstileUnavailable'] = true;
        }
        $this->view('public/home', $data);
    }
    public function search(): void
    {
        Security::verifyCsrf();
        $key = Security::clientKey();
        $attempts = RateLimiter::hit('public-search', $key, max(1, (int)Env::get('PUBLIC_SEARCH_MAX_ATTEMPTS', '8')), 900);
        if ($attempts === null) {
            $this->respond(['title' => 'Consultation des diplômes', 'result' => null, 'error' => 'Le service est temporairement indisponible. Réessayez plus tard.'], 503);
            return;
        }
        if ($attempts < 0) {
            $this->respond(['title' => 'Consultation des diplômes', 'result' => null, 'error' => 'Trop de tentatives. Réessayez dans quinze minutes.'], 429);
            return;
        }
        $captchaRequired = $attempts > 3;
        $turnstileToken = $_POST['cf-turnstile-response'] ?? null;
        if ($captchaRequired && (!is_string($turnstileToken) || !TurnstileService::verify($turnstileToken, 'public_search'))) {
            $error = TurnstileService::isConfigured() ? 'La vérification anti-robot a échoué. Rechargez la page et réessayez.' : 'La vérification anti-robot n’est pas configurée. Réessayez plus tard.';
            $this->respond(['title' => 'Consultation des diplômes', 'result' => null, 'error' => $error, 'resetTurnstile' => true, 'turnstileUnavailable' => !TurnstileService::isConfigured()], 422);
            return;
        }
        $nomInput = $_POST['nom'] ?? null;
        $prenomsInput = $_POST['prenoms'] ?? null;
        $birthInput = $_POST['date_naissance'] ?? null;
        $year = $_POST['annee_obtention'] ?? null;
        $nom = is_string($nomInput) ? trim($nomInput) : '';
        $prenoms = is_string($prenomsInput) ? trim($prenomsInput) : '';
        $birth = is_string($birthInput) ? $birthInput : '';
        $birthDate = is_string($birth) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $birth) : false;
        $dateErrors = \DateTimeImmutable::getLastErrors();
        $validDate = $birthDate !== false && $birthDate->format('Y-m-d') === $birth
            && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0));
        $valid = $nom !== '' && mb_strlen($nom) <= 120 && $prenoms !== '' && mb_strlen($prenoms) <= 160
            && $validDate && is_string($year) && preg_match('/^\d{4}$/', $year) === 1;
        $results = $valid ? SearchService::publicLookup($nom, $prenoms, $birth, (int)$year) : [];
        $data = ['title' => 'Consultation des diplômes', 'results' => $results, 'searched' => true];
        if ($attempts >= 3) {
            if (TurnstileService::isConfigured()) $data['turnstileSiteKey'] = TurnstileService::siteKey();
            else $data['turnstileUnavailable'] = true;
        }
        $this->respond($data);
    }
}
