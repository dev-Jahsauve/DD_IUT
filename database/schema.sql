-- IUT-Diplômes — MySQL 8+ / MariaDB compatible
-- Select the target database in phpMyAdmin before importing this file.
-- This file initializes an empty database and intentionally never creates, selects,
-- drops, or replaces a database or existing table.
CREATE TABLE
    roles (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(40) NOT NULL UNIQUE,
        libelle VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB;

INSERT INTO
    roles (code, libelle)
VALUES
    ('ADMIN_TECHNIQUE', 'Administrateur technique'),
    ('SUPERVISEUR', 'Superviseur'),
    ('CHEF_SERVICE', 'Chef de service'),
    ('AGENT', 'Agent');

CREATE TABLE
    utilisateurs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        role_id BIGINT UNSIGNED NOT NULL,
        nom VARCHAR(160) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        actif TINYINT (1) NOT NULL DEFAULT 1,
        derniere_connexion DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles (id)
    ) ENGINE = InnoDB;

CREATE TABLE
    departements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(160) NOT NULL UNIQUE,
        code VARCHAR(30) NULL UNIQUE
    ) ENGINE = InnoDB;

CREATE TABLE
    filieres (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        departement_id BIGINT UNSIGNED NULL,
        nom VARCHAR(160) NOT NULL,
        code VARCHAR(30) NULL,
        UNIQUE KEY uq_filiere (nom, code),
        FOREIGN KEY (departement_id) REFERENCES departements (id) ON DELETE SET NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    annees_academiques (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(20) NOT NULL UNIQUE,
        date_debut DATE NULL,
        date_fin DATE NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    types_diplome (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) NOT NULL UNIQUE,
        libelle VARCHAR(120) NOT NULL
    ) ENGINE = InnoDB;

INSERT INTO
    types_diplome (code, libelle)
VALUES
    ('DUT', 'Diplôme Universitaire de Technologie'),
    ('LICENCE_TECH', 'Licence technologique'),
    ('AUTRE', 'Autre diplôme');

CREATE TABLE
    etudiants (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        matricule VARCHAR(60) NULL UNIQUE,
        nom VARCHAR(120) NOT NULL,
        prenoms VARCHAR(160) NOT NULL,
        date_naissance DATE NOT NULL,
        lieu_naissance VARCHAR(160) NULL,
        email VARCHAR(190) NULL,
        telephone VARCHAR(40) NULL,
        filiere_id BIGINT UNSIGNED NULL,
        annee_entree YEAR NULL,
        source_import VARCHAR(255) NULL,
        source_ligne INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_etudiant_nom (nom, prenoms),
        FOREIGN KEY (filiere_id) REFERENCES filieres (id) ON DELETE SET NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    diplomes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etudiant_id BIGINT UNSIGNED NOT NULL,
        type_diplome VARCHAR(120) NOT NULL,
        annee_obtention YEAR NOT NULL,
        numero_diplome VARCHAR(100) NULL,
        statut ENUM (
            'DOSSIER_DEPOSE',
            'EN_COURS_ETABLISSEMENT',
            'SIGNE',
            'PRET_A_REMETTRE',
            'REMIS',
            'ANOMALIE_BLOQUE'
        ) NOT NULL DEFAULT 'DOSSIER_DEPOSE',
        date_depot_dossier DATE NULL,
        date_transmission DATE NULL,
        date_signature DATE NULL,
        date_mise_a_disposition DATE NULL,
        date_remise DATETIME NULL,
        emplacement_stockage VARCHAR(160) NULL,
        source_import VARCHAR(255) NULL,
        source_ligne INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_diplome_statut (statut),
        INDEX idx_diplome_etudiant (etudiant_id),
        FOREIGN KEY (etudiant_id) REFERENCES etudiants (id)
    ) ENGINE = InnoDB;

CREATE TABLE
    demandes_retrait (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        diplome_id BIGINT UNSIGNED NOT NULL,
        canal ENUM ('EN_LIGNE', 'GUICHET') NOT NULL DEFAULT 'EN_LIGNE',
        statut ENUM ('EN_ATTENTE', 'VALIDEE', 'ANNULEE', 'TRAITEE') NOT NULL DEFAULT 'EN_ATTENTE',
        date_demande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        commentaire TEXT NULL,
        FOREIGN KEY (diplome_id) REFERENCES diplomes (id),
        INDEX idx_demande_statut (statut)
    ) ENGINE = InnoDB;

CREATE TABLE
    retraits (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        diplome_id BIGINT UNSIGNED NOT NULL,
        agent_id BIGINT UNSIGNED NOT NULL,
        date_retrait DATETIME NOT NULL,
        identite_verifiee TINYINT (1) NOT NULL,
        signature_recue TINYINT (1) NOT NULL,
        reference_recu VARCHAR(60) NOT NULL UNIQUE,
        observations TEXT NULL,
        FOREIGN KEY (diplome_id) REFERENCES diplomes (id),
        FOREIGN KEY (agent_id) REFERENCES utilisateurs (id),
        UNIQUE KEY uq_retrait_diplome (diplome_id)
    ) ENGINE = InnoDB;

CREATE TABLE
    journal_audit (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        utilisateur_id BIGINT UNSIGNED NULL,
        action VARCHAR(80) NOT NULL,
        entite VARCHAR(80) NOT NULL,
        entite_id BIGINT UNSIGNED NULL,
        avant_json JSON NULL,
        apres_json JSON NULL,
        adresse_ip VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_date (created_at),
        INDEX idx_audit_entity (entite, entite_id),
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id) ON DELETE SET NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    lots_importation (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        utilisateur_id BIGINT UNSIGNED NOT NULL,
        nom_original VARCHAR(255) NOT NULL,
        nom_stocke VARCHAR(100) NOT NULL,
        format_fichier VARCHAR(10) NOT NULL,
        statut ENUM (
            'A_VALIDER',
            'EN_CONTROLE',
            'VALIDE',
            'INTEGRE',
            'ERREUR'
        ) NOT NULL DEFAULT 'A_VALIDER',
        taille_octets BIGINT UNSIGNED NOT NULL,
        nombre_lignes INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)
    ) ENGINE = InnoDB;

CREATE TABLE
    import_lignes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lot_id BIGINT UNSIGNED NOT NULL,
        numero_ligne INT UNSIGNED NOT NULL,
        donnees_json JSON NOT NULL,
        statut ENUM (
            'A_CONTROLER',
            'ANOMALIE',
            'VALIDEE',
            'INTEGREE',
            'REJETEE'
        ) NOT NULL DEFAULT 'A_CONTROLER',
        erreur TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_import_ligne (lot_id, numero_ligne),
        FOREIGN KEY (lot_id) REFERENCES lots_importation (id) ON DELETE CASCADE
    ) ENGINE = InnoDB;

-- Le statut public "disponible" correspond exclusivement à PRET_A_REMETTRE.
-- Ne pas stocker les scans de pièces d'identité dans un répertoire public.