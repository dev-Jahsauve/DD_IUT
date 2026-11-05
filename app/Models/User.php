<?php
namespace App\Models;
use App\Core\Database;
final class User {
    public static function findByEmail(string $email): ?array {
        $s=Database::connection()->prepare("SELECT u.*, r.code AS role_code FROM utilisateurs u JOIN roles r ON r.id=u.role_id WHERE u.email=? LIMIT 1");
        $s->execute([mb_strtolower(trim($email))]); return $s->fetch() ?: null;
    }
    public static function touch(int $id): void {Database::connection()->prepare("UPDATE utilisateurs SET derniere_connexion=NOW() WHERE id=?")->execute([$id]);}
}
