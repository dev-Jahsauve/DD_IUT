# Installation XAMPP / phpMyAdmin

1. Installer XAMPP avec PHP 8.1 ou supérieur.
2. Décompresser le projet dans `C:\xampp\htdocs\iut-diplomes`.
3. Démarrer Apache et MySQL.
4. Pour une base neuve uniquement, créer `iut_diplomes` puis importer `database/schema.sql`. Ne jamais réimporter ce schéma sur une base existante.
5. Copier `.env.example` vers `.env` et adapter DB_HOST, DB_NAME, DB_USER, DB_PASS et APP_URL. En local HTTP uniquement, définir `SESSION_SECURE=false`; en production HTTPS, garder `SESSION_SECURE=true` et configurer `APP_URL` avec `https://`.
6. Vérifier que `pdo_mysql`, `mbstring`, `fileinfo` et `openssl` sont activées dans `php.ini`.
7. Installer Composer puis exécuter `composer install` à la racine.
8. Exécuter `php bin/create-admin.php` dans un terminal.
9. Visiter `http://localhost/iut-diplomes/public/`.
10. Se connecter via « Administration ».

Pour une base existante, effectuer une sauvegarde puis appliquer les migrations une par une. La migration `database/migrations/2026_11_05_matricule_optionnel.sql` autorise les étudiants sans matricule sans modifier les lignes existantes. Son retour arrière exige d’attribuer un matricule unique à toute ligne sans matricule.

Pour un hébergement réel, le DocumentRoot doit pointer vers `public/`, jamais vers la racine complète.
