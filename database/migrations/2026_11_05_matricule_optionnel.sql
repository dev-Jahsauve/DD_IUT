-- Appliquer après sauvegarde de la base iut_diplomes.
-- Cette migration conserve les matricules déjà enregistrés et autorise NULL pour les nouveaux dossiers.
ALTER TABLE etudiants MODIFY matricule VARCHAR(60) NULL;

-- Retour arrière, uniquement après avoir renseigné un matricule unique pour chaque ligne NULL :
-- ALTER TABLE etudiants MODIFY matricule VARCHAR(60) NOT NULL;