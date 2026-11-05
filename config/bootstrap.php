<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;

require dirname(__DIR__) . '/vendor/autoload.php';
Env::load(dirname(__DIR__) . '/.env');
Env::load(dirname(__DIR__) . '/.env.local');
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = rtrim(dirname($scriptName), '/');
Env::setDefault('APP_URL', $basePath === '.' ? '' : $basePath);
$configuredScheme = parse_url(Env::get('APP_URL', '') ?? '', PHP_URL_SCHEME);
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $configuredScheme === 'https';

$isDevelopment = Env::get('APP_ENV', 'production') === 'local' && Env::bool('APP_DEBUG', false);
ini_set('display_errors', $isDevelopment ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-errors.log');
error_reporting(E_ALL);
set_exception_handler(static function (\Throwable $exception) use ($isDevelopment): void {
    error_log((string)$exception);
    if (http_response_code() < 400) http_response_code(500);
    if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
    if ($isDevelopment) {
        echo '<pre>' . htmlspecialchars((string)$exception, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
        return;
    }
    echo 'Le service est temporairement indisponible. Réessayez plus tard.';
});

date_default_timezone_set('Africa/Douala');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://challenges.cloudflare.com; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net data:; connect-src 'self' https://challenges.cloudflare.com; frame-src https://challenges.cloudflare.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
if ($isHttps) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
session_name('IUTDIPLOMESSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => Env::bool('SESSION_SECURE', $isHttps),
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

Database::configure([
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::get('DB_PORT', '3306'),
    'dbname' => Env::get('DB_NAME', 'iut_diplomes'),
    'user' => Env::get('DB_USER', 'root'),
    'pass' => Env::get('DB_PASS', ''),
]);
