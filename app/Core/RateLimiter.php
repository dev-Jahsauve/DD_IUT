<?php

namespace App\Core;

final class RateLimiter
{
    public static function count(string $scope, string $clientKey, int $windowSeconds): ?int
    {
        return self::update($scope, $clientKey, $windowSeconds, static function (array $events): array {
            return [$events, count($events)];
        });
    }

    public static function hit(string $scope, string $clientKey, int $limit, int $windowSeconds): ?int
    {
        return self::update($scope, $clientKey, $windowSeconds, static function (array $events) use ($limit): array {
            if (count($events) >= $limit) return [$events, -1];
            $events[] = time();
            return [$events, count($events)];
        });
    }

    public static function clear(string $scope, string $clientKey, int $windowSeconds): bool
    {
        return self::update($scope, $clientKey, $windowSeconds, static fn(): array => [[], 0]) !== null;
    }

    private static function update(string $scope, string $clientKey, int $windowSeconds, callable $operation): ?int
    {
        $directory = dirname(__DIR__, 2) . '/storage/tmp/rate-limits';
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) return null;

        $file = $directory . '/' . hash('sha256', $scope) . '.json';
        $handle = @fopen($file, 'c+');
        if ($handle === false) return null;
        @chmod($file, 0600);

        try {
            if (!flock($handle, LOCK_EX)) return null;
            rewind($handle);
            $decoded = json_decode(stream_get_contents($handle) ?: '{}', true);
            $cutoff = time() - $windowSeconds;
            $records = is_array($decoded) ? $decoded : [];
            foreach ($records as $recordKey => $recordEvents) {
                $records[$recordKey] = is_array($recordEvents)
                    ? array_values(array_filter($recordEvents, static fn($time): bool => is_int($time) && $time > $cutoff))
                    : [];
                if ($records[$recordKey] === []) unset($records[$recordKey]);
            }
            $clientId = hash('sha256', $clientKey);
            $events = $records[$clientId] ?? [];
            [$events, $result] = $operation($events);
            if ($events === []) unset($records[$clientId]);
            else $records[$clientId] = array_values($events);
            rewind($handle);
            ftruncate($handle, 0);
            $written = fwrite($handle, json_encode($records, JSON_THROW_ON_ERROR));
            fflush($handle);
            flock($handle, LOCK_UN);
            return $written === false ? null : $result;
        } catch (\Throwable) {
            return null;
        } finally {
            fclose($handle);
        }
    }
}
