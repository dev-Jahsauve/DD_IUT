# Procédure d'audit et d'import des données

1. Travailler sur une copie des fichiers sources, jamais sur l'original.
2. Anonymiser les fichiers de test.
3. Recenser les onglets, colonnes, formats de dates, nombre de lignes et valeurs vides.
4. Identifier les colonnes sources : nom, prénoms, date de naissance, année du diplôme, type d’attestation/diplôme (le matricule est facultatif), filière et dates de dépôt/transmission/signature.
5. Normaliser accents, espaces, casse et dates sans écraser la valeur originale.
6. Détecter les doublons par matricule quand il est fourni ; sinon, faire un rapprochement prudent nom + prénoms + date de naissance + année du diplôme avec validation humaine.
7. Charger dans import_lignes (staging).
8. Produire un rapport : lignes valides, doublons, champs manquants, incohérences.
9. Faire valider le lot par un responsable.
10. Intégrer en transaction avec conservation du nom de fichier et numéro de ligne.
11. Conserver un journal des corrections et ne jamais supprimer la source sans politique d'archivage validée.

Les documents Word doivent être examinés séparément. Si les tableaux sont réguliers, une extraction peut être envisagée ; sinon, la saisie assistée et la validation humaine sont plus fiables.
