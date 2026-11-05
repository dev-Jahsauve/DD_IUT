<?php

use App\Core\Security; ?><h1 class="h2 mb-3">Remise physique des diplômes</h1>
<div class="panel p-4 mb-4">
    <h2 class="h5">Enregistrer une remise</h2>
    <?php if ($available): ?>
        <form method="post" action="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/retraits/remettre" class="row g-3"><input type="hidden" name="_csrf" value="<?= Security::csrf() ?>">
            <div class="col-md-7"><label class="form-label" for="ready-diploma">Diplôme prêt à remettre</label><select id="ready-diploma" name="diplome_id" class="form-select" required>
                    <option value="">Sélectionner un diplôme</option><?php foreach ($available as $item): ?><?php $label = trim($item['nom'] . ' ' . $item['prenoms']) . ' · ' . $item['date_naissance'] . ' · ' . $item['type_diplome'] . ' (' . $item['annee_obtention'] . ')'; ?><option value="<?= (int)$item['id'] ?>"><?= Security::e($label) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-5"><label class="form-label" for="handover-notes">Observations</label><input id="handover-notes" name="observations" class="form-control" placeholder="Facultatif"></div>
            <div class="col-12">
                <div class="form-check"><input class="form-check-input" type="checkbox" name="identite_verifiee" value="1" required id="idcheck"><label class="form-check-label" for="idcheck">Identité du bénéficiaire vérifiée selon la procédure de l'IUT</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="signature_recue" value="1" required id="signcheck"><label class="form-check-label" for="signcheck">Signature du bénéficiaire recueillie dans le registre / sur le reçu</label></div>
            </div>
            <div class="col-12"><button class="btn btn-iut">Confirmer la remise et générer la référence</button></div>
        </form>
    <?php else: ?>
        <p class="text-muted mb-0">Aucun diplôme n’est actuellement marqué comme prêt à remettre.</p>
    <?php endif; ?>
    <p class="small text-muted mt-3 mb-0">Cette action est définitive dans le parcours normal. Toute correction doit faire l'objet d'une procédure journalisée.</p>
</div>
<div class="panel table-responsive">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Étudiant</th>
                <th>Diplôme</th>
                <th>Date</th>
                <th>Agent</th>
                <th>Reçu</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
                    <td><?= Security::e($r['reference_recu']) ?></td>
                    <td><?= Security::e(trim($r['nom'] . ' ' . $r['prenoms'])) ?><br><span class="small text-muted"><?= Security::e($r['matricule'] ?? 'Sans matricule') ?></span></td>
                    <td><?= Security::e($r['type_diplome'] . ' (' . $r['annee_obtention'] . ')') ?></td>
                    <td><?= Security::e($r['date_retrait']) ?></td>
                    <td><?= Security::e($r['agent'] ?? '—') ?></td>
                    <td><a class="btn btn-sm btn-outline-secondary" href="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/admin/retraits/<?= (int)$r['id'] ?>/recu" aria-label="Télécharger le reçu PDF <?= Security::e($r['reference_recu']) ?>">Télécharger PDF</a></td>
                </tr><?php endforeach; ?></tbody>
    </table>
</div>