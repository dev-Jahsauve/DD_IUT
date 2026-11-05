<?php
namespace App\Core;
use App\Models\User;
final class Auth {
    public static function user(): ?array {return $_SESSION['user'] ?? null;}
    public static function check(): bool {return self::user() !== null;}
    public static function attempt(string $email,string $password): bool {
        $user=User::findByEmail($email);
        if (!$user || !$user['actif'] || !password_verify($password,$user['mot_de_passe'])) return false;
        session_regenerate_id(true);
        $_SESSION['user']=['id'=>(int)$user['id'],'nom'=>$user['nom'],'email'=>$user['email'],'role'=>$user['role_code']];
        User::touch((int)$user['id']);
        return true;
    }
    public static function logout(): void {$_SESSION=[]; session_regenerate_id(true);}
    public static function requireRole(array $roles): void {
        $u=self::user();
        if (!$u) {header('Location: '.rtrim(Env::get('APP_URL',''),'/').'/connexion');exit;}
        if (!in_array($u['role'],$roles,true)) {http_response_code(403);exit('Accès non autorisé.');}
    }
}
