<?php

namespace App\Services;

use App\Core\Env;

final class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    private const LOCAL_TEST_SECRET = '1x0000000000000000000000000000000AA';

    public static function isConfigured(): bool
    {
        return (bool)Env::get('TURNSTILE_SITE_KEY')
            && (bool)Env::get('TURNSTILE_SECRET_KEY')
            && (bool)Env::get('TURNSTILE_HOSTNAME');
    }

    public static function siteKey(): string
    {
        return Env::get('TURNSTILE_SITE_KEY', '') ?? '';
    }

    public static function verify(?string $token, string $expectedAction): bool
    {
        $secret = Env::get('TURNSTILE_SECRET_KEY', '');
        $hostname = Env::get('TURNSTILE_HOSTNAME', '');
        if (
            $secret === null || $secret === '' || $hostname === null || $hostname === ''
            || !is_string($token) || $token === '' || strlen($token) > 2048
            || !function_exists('curl_init')
        ) {
            return false;
        }

        $uuid = random_bytes(16);
        $uuid[6] = chr((ord($uuid[6]) & 0x0f) | 0x40);
        $uuid[8] = chr((ord($uuid[8]) & 0x3f) | 0x80);
        $hex = bin2hex($uuid);
        $idempotencyKey = substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
        $handle = curl_init(self::VERIFY_URL);
        if ($handle === false) return false;

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'secret' => $secret,
                'response' => $token,
                'idempotency_key' => $idempotencyKey,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        try {
            $response = curl_exec($handle);
            $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            if (!is_string($response)) {
                error_log('Turnstile Siteverify transport failure (cURL ' . curl_errno($handle) . ').');
                return false;
            }
            if ($status !== 200) {
                error_log('Turnstile Siteverify returned HTTP ' . $status . '.');
                return false;
            }
            $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if ($secret === self::LOCAL_TEST_SECRET && Env::get('APP_ENV', 'production') === 'local') {
                return ($result['success'] ?? false) === true
                    && ($result['hostname'] ?? null) === 'example.com';
            }
            $valid = ($result['success'] ?? false) === true
                && ($result['action'] ?? null) === $expectedAction
                && strcasecmp((string)($result['hostname'] ?? ''), $hostname) === 0;
            if (!$valid) {
                $codes = is_array($result['error-codes'] ?? null) ? implode(',', $result['error-codes']) : 'response-mismatch';
                error_log('Turnstile Siteverify rejected token: ' . $codes . '.');
            }
            return $valid;
        } catch (\Throwable $exception) {
            error_log('Turnstile verification failed: ' . get_class($exception));
            return false;
        } finally {
            curl_close($handle);
        }
    }
}
