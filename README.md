# IUT-Diplômes — socle applicatif PHP MVC

Application de gestion et de consultation des diplômes de l'IUT de Douala.
Cette livraison est une **première fondation fonctionnelle** : architecture MVC native PHP, schéma MySQL, authentification, rôles, recherche publique protégée, gestion des étudiants/diplômes, import Excel en zone de prévisualisation, retraits, reçus imprimables, journal d'audit et documentation.

## Prérequis
- PHP 8.1+ (8.2 recommandé), extensions `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `json`.
- MySQL 8.0+ ou MariaDB récent, InnoDB, `utf8mb4`.
- Apache avec `mod_rewrite` (XAMPP convient).
- Composer.

## Installation locale (XAMPP / phpMyAdmin)
1. Décompresser le dossier `iut-diplomes` dans `C:\xampp\htdocs\`.
2. Créer une base `iut_diplomes` dans phpMyAdmin.
3. Importer `database/schema.sql` dans cette base.
4. Copier `.env.example` en `.env`, puis renseigner les paramètres MySQL.
5. Dans un terminal à la racine du projet : `composer install`.
6. Créer un premier compte administrateur :
   `php bin/create-admin.php`
   Le script demande le nom et l'e-mail puis fait saisir deux fois un mot de passe masqué; seul son hash est enregistré.
7. Pour tester, ouvrir `http://localhost/iut-diplomes/public/`.

> `database/schema.sql` initialise une base vide; ne le réimportez jamais sur une base qui contient des données. Pour une base existante, sauvegarder puis appliquer uniquement les migrations prévues. Configurer le DocumentRoot Apache sur `public/` et ne jamais exposer `app/`, `bin/`, `config/`, `database/`, `storage/`, `vendor/` ou `.env` sur Internet.

## Dépendances
- `phpoffice/phpspreadsheet` : lecture Excel (import contrôlé).
- `dompdf/dompdf` : génération des reçus PDF après remise.
- Aucun framework applicatif : routage et MVC PHP natifs.

## Compte administrateur
Le compte n'est pas précréé. Utiliser `php bin/create-admin.php`.
Ne pas utiliser de mot de passe de démonstration partagé.

## Parcours inclus
- Accueil et recherche publique par nom, prénoms, date de naissance et année du diplôme, avec réponse publique minimale.
- Authentification administrative et contrôle d'accès par rôles.
- Tableau de bord.
- Consultation/ajout d'étudiants et diplômes.
- Import Excel : téléversement, stockage privé, aperçu, anomalies, validation explicite.
- Enregistrement d'un retrait au guichet avec vérification déclarée de la pièce et reçu imprimable.
- Historique d'audit.

## À compléter avant toute mise en production
- Faire valider les règles de gestion, la base légale, les durées de conservation, le contrôle d'identité et la procuration par la direction / le service juridique.
- Faire un audit réel des fichiers Excel et Word. Aucun fichier source métier n'a été fourni avec cette livraison : le mapping Excel est volontairement configurable et doit être ajusté à vos colonnes réelles.
- Configurer les clés Cloudflare Turnstile (`TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `TURNSTILE_HOSTNAME`) dans `.env.local` en développement ou dans les variables secrètes de l'hébergeur; tester le hostname et le rate limit derrière son proxy. Imposer 2FA au personnel avant toute donnée réelle.
- Configurer HTTPS, cookies Secure, sauvegardes hors serveur et tests de restauration, pare-feu, journaux centralisés, permissions strictes.
- Faire un audit de sécurité indépendant et un pilote sur une filière.
- Faire confirmer le domaine officiel : le site communiqué dans les échanges a été `iut-dla.cm` puis `iut-dla.com` et `iut-dla.cm`/`iut-dla.cm` ne doit pas être supposé sans validation institutionnelle. Le site indiqué le plus récemment est `https://www.iut-dla.cm/`.

## Structure
Voir `docs/ARCHITECTURE.md`, `docs/CAHIER_DES_CHARGES.md`, `docs/DICTIONNAIRE_DONNEES.md` et `docs/diagrams/`.

Cette base de code ne constitue pas une certification de sécurité ni une autorisation de traitement des données personnelles.
