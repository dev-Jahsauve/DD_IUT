<?php

namespace App\Core;

final class Env
{
    private static array $values = [];
    public static function load(string $file): void
    {
        if (!is_file($file)) return;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            self::$values[trim($k)] = trim(trim($v), "\"'");
        }
    }
    public static function get(string $key, ?string $default = null): ?string
    {
        return self::$values[$key] ?? $_ENV[$key] ?? $default;
    }
    public static function setDefault(string $key, string $value): void
    {
        $current = self::get($key);
        if ($current === null || $current === '') self::$values[$key] = $value;
    }
    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::get($key);
        return $v === null ? $default : filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }
}
