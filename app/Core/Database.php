<?php
namespace App\Core;
use PDO;
use PDOException;
final class Database {
    private static array $config = [];
    private static ?PDO $pdo = null;
    public static function configure(array $config): void { self::$config = $config; }
    public static function connection(): PDO {
        if (self::$pdo) return self::$pdo;
        $c=self::$config;
        $dsn="mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset=utf8mb4";
        self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false
        ]);
        return self::$pdo;
    }
}
