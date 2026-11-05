<?php

namespace App\Core;

final class Security
{
    public static function e(?string $v): string
    {
        return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    public static function csrf(): string
    {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf'];
    }
    public static function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
            http_response_code(419);
            exit('Session expirée ou jeton de sécurité invalide. Rechargez la page.');
        }
    }
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
    public static function clientKey(): string
    {
        return hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
