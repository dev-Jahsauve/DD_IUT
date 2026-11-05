<?php

namespace App\Services;

use App\Core\Database;

final class SearchService
{
    public static function publicLookup(string $nom, string $prenoms, string $birth, int $year): array
    {
        $pdo = Database::connection();
        $student = $pdo->prepare('SELECT id FROM etudiants WHERE nom=? AND prenoms=? AND date_naissance=? ORDER BY id LIMIT 2');
        $student->execute([trim($nom), trim($prenoms), $birth]);
        $matches = $student->fetchAll();
        if (count($matches) !== 1) return [];

        $diplomas = $pdo->prepare("SELECT statut, type_diplome, annee_obtention FROM diplomes WHERE etudiant_id=? AND annee_obtention=? AND statut <> 'ANOMALIE_BLOQUE' ORDER BY id LIMIT 10");
        $diplomas->execute([(int)$matches[0]['id'], $year]);
        return $diplomas->fetchAll();
    }
}
