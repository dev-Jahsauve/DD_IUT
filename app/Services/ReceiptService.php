<?php

namespace App\Services;

final class ReceiptService
{
    public static function renderPdf(array $receipt): string
    {
        $escape = static fn(?string $value): string => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = $escape(trim(($receipt['nom'] ?? '') . ' ' . ($receipt['prenoms'] ?? '')));
        $agent = $escape($receipt['agent'] ?? '—');
        $html = '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Reçu de retrait</title><style>body{font:14px Arial,sans-serif;color:#24352b;margin:48px}header{border-bottom:3px solid #176b3a;padding-bottom:16px;margin-bottom:28px}h1{font-size:22px;color:#12482d}h2{font-size:16px;margin-top:26px}table{width:100%;border-collapse:collapse}td{padding:10px;border-bottom:1px solid #dfe7df}td:first-child{width:34%;font-weight:bold;color:#56645a}.reference{font-size:17px;font-weight:bold;color:#12482d}.note{margin-top:32px;font-size:12px;color:#59675d}</style><body><header><strong>INSTITUT UNIVERSITAIRE DE TECHNOLOGIE DE DOUALA</strong><h1>Reçu de remise de diplôme</h1></header><p class="reference">Référence : ' . $escape($receipt['reference_recu'] ?? '') . '</p><table><tr><td>Bénéficiaire</td><td>' . $name . '</td></tr><tr><td>Matricule</td><td>' . $escape($receipt['matricule'] ?? '—') . '</td></tr><tr><td>Diplôme</td><td>' . $escape($receipt['type_diplome'] ?? '') . ' (' . $escape((string)($receipt['annee_obtention'] ?? '')) . ')</td></tr><tr><td>Date de remise</td><td>' . $escape($receipt['date_retrait'] ?? '') . '</td></tr><tr><td>Agent habilité</td><td>' . $agent . '</td></tr></table><h2>Confirmation</h2><p>Le diplôme désigné ci-dessus a été remis au bénéficiaire après vérification de son identité et recueil de sa signature.</p><p class="note">Document généré par le service de gestion des diplômes de l’IUT de Douala.</p></body></html>';

        $pdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false, 'isPhpEnabled' => false]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        return $pdf->output();
    }
}
