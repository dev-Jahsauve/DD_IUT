# MLD — relations et cardinalités

- ROLES( id PK, code UQ, libelle )
- UTILISATEURS( id PK, role_id FK→ROLES.id, nom, email UQ, mot_de_passe, actif )
- DEPARTEMENTS( id PK, nom UQ, code UQ )
- FILIERES( id PK, departement_id FK→DEPARTEMENTS.id, nom, code )
- ANNEES_ACADEMIQUES( id PK, libelle UQ, date_debut, date_fin )
- TYPES_DIPLOME( id PK, code UQ, libelle )
- ETUDIANTS( id PK, matricule UQ NULL, nom, prenoms, date_naissance, filiere_id FK→FILIERES.id, ... )
- DIPLOMES( id PK, etudiant_id FK→ETUDIANTS.id, type_diplome, annee_obtention, statut, dates métier, ... )
- DEMANDES_RETRAIT( id PK, diplome_id FK→DIPLOMES.id, canal, statut, ... )
- RETRAITS( id PK, diplome_id FK→DIPLOMES.id UQ, agent_id FK→UTILISATEURS.id, reference_recu UQ, ... )
- JOURNAL_AUDIT( id PK, utilisateur_id FK→UTILISATEURS.id, action, entite, entite_id, avant_json, apres_json, ... )
- LOTS_IMPORTATION( id PK, utilisateur_id FK→UTILISATEURS.id, fichier, statut, ... )
- IMPORT_LIGNES( id PK, lot_id FK→LOTS_IMPORTATION.id, numero_ligne, donnees_json, statut, ... )

Cardinalités principales : un rôle possède plusieurs comptes ; un département regroupe plusieurs filières ; une filière concerne plusieurs étudiants ; un étudiant possède plusieurs diplômes ; un diplôme peut recevoir plusieurs demandes, mais au plus un retrait définitif.
