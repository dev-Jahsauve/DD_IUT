<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Auth;

final class AuditService
{
    public static function log(string $action, string $entite, ?int $entiteId = null, ?array $avant = null, ?array $apres = null): void
    {
        $u = Auth::user();
        $s = Database::connection()->prepare("INSERT INTO journal_audit (utilisateur_id, action, entite, entite_id, avant_json, apres_json, adresse_ip, user_agent) VALUES (?,?,?,?,?,?,?,?)");
        $s->execute([$u['id'] ?? null, $action, $entite, $entiteId, $avant ? json_encode($avant, JSON_UNESCAPED_UNICODE) : null, $apres ? json_encode($apres, JSON_UNESCAPED_UNICODE) : null, null, null]);
    }
}
