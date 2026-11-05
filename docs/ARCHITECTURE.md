# Architecture technique

## Choix
PHP 8+ natif, MVC léger, PDO, MySQL 8, Bootstrap 5, JavaScript. Pas de Laravel.
Les contrôleurs reçoivent les requêtes, les modèles accèdent aux données, les services portent les règles métier, les vues rendent le HTML.

## Flux
Navigateur → `public/index.php` → Router → Controller → Service/Model → PDO/MySQL → View.

## Répertoires
- `app/Controllers` : orchestration HTTP.
- `app/Core` : routeur, base, sécurité, session, contrôleur.
- `app/Models` : accès aux données.
- `app/Services` : audit, recherche, import.
- `app/Views` : vues publiques et administratives.
- `config` : configuration.
- `database` : SQL initial.
- `public` : seul répertoire exposé.
- `storage` : imports, journaux et reçus privés.
- `docs` : cahier des charges, MCD/MLD, guides.

## Sécurité déjà présente
PDO préparé, CSRF, échappement HTML, cookies HttpOnly/SameSite/Secure selon HTTPS, régénération de session, mots de passe hachés, contrôle des rôles, CAPTCHA progressif local, limitation persistante des tentatives, validation des fichiers, stockage hors public, en-têtes HTTP, gestion d'erreur sans détail en production, transaction atomique lors de la remise, unicité du retrait par diplôme, journal d'audit minimisé.

## Limites avant données réelles
Cette version reste un pilote, pas une certification de production. Le CAPTCHA local est contournable et doit être remplacé par un fournisseur validé côté serveur; le rate limit fichier doit être testé derrière le proxy de l'hébergeur. Ajouter 2FA, politique de comptes et de récupération, journal externe protégé, sauvegardes hors site et restauration testée, analyse de dépendances, tests automatisés, revue indépendante et validation juridique. Le staging d'import n'intègre pas encore les lignes en base métier; aucun fichier métier réel n'a servi à valider le mapping. Le modèle officiel du reçu et le parcours de correction après anomalie doivent être approuvés par l'IUT.
