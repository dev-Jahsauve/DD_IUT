# Dictionnaire des données

| Table | Rôle | Champs principaux |
|---|---|---|
| roles | Profils | code, libelle |
| utilisateurs | Comptes agents | role_id, nom, email, mot_de_passe, actif |
| departements | Organisation | nom, code |
| filieres | Filières | departement_id, nom, code |
| annees_academiques | Années | libelle, dates |
| types_diplome | Référentiel | code, libelle |
| etudiants | Identité étudiant | matricule (facultatif, unique si fourni), nom, prénoms, date_naissance, filiere_id |
| diplomes | Diplôme et cycle de vie | étudiant, type, année, statut, dates de dépôt/transmission/signature/remise |
| demandes_retrait | Pré-demande | diplôme, canal, statut |
| retraits | Remise physique | diplôme, agent, date, identité vérifiée, signature, référence |
| journal_audit | Traçabilité minimisée | acteur, action, entité, avant/après (sans formulaire, token CSRF ni identité détaillée) |
| lots_importation | Fichier source | nom original/stocké, format, statut |
| import_lignes | Lignes temporaires | numéro, JSON, statut, anomalie |

Les champs et contraintes devront être ajustés à partir des fichiers réels. Ne pas importer de données nominatives dans un environnement de démonstration.
