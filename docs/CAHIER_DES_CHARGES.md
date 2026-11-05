# Cahier des charges — IUT-Diplômes

## 1. Finalité
Mettre à disposition de l'IUT de Douala un système centralisé permettant d'organiser les dossiers et diplômes, d'informer les anciens étudiants de la disponibilité d'un diplôme et de tracer sa remise physique.

## 2. Périmètre de la première version
- Consultation publique par nom, prénoms, date de naissance et année d'obtention; matricule facultatif.
- Gestion administrative des étudiants, filières et diplômes.
- Gestion du cycle de vie des diplômes.
- Import Excel en zone de staging avec validation humaine.
- Enregistrement des retraits et références de reçus.
- Gestion des comptes et rôles.
- Journal d'audit et statistiques élémentaires.

Les documents Word sont des sources complémentaires : ils doivent être analysés, normalisés et contrôlés manuellement. Un traitement automatique de documents Word hétérogènes n'est pas inclus dans ce socle.

## 3. Règles de gestion
1. Un étudiant peut avoir plusieurs diplômes.
2. Un diplôme appartient à un seul étudiant.
3. Le statut « disponible » public est uniquement `PRET_A_REMETTRE`.
4. Une demande en ligne (phase ultérieure) ne constitue pas une remise.
5. Une remise nécessite une identité vérifiée et une signature recueillie.
6. Un diplôme ne peut être remis qu'une seule fois.
7. La remise et le changement de statut se font dans une transaction SQL.
8. Toute opération sensible est auditée.
9. Les données importées passent par une zone tampon et une validation humaine.
10. Les corrections après remise nécessitent une procédure administrative tracée.

## 4. Cycle de vie
`DOSSIER_DEPOSE → EN_COURS_ETABLISSEMENT → SIGNE → PRET_A_REMETTRE → REMIS`
À tout moment, un administrateur technique ou chef de service peut signaler `ANOMALIE_BLOQUE`. Les transitions normales sont séquentielles et contrôlées côté serveur; un agent ne peut ni changer le statut d'établissement ni déclarer un diplôme disponible.

## 5. Profils
- ADMIN_TECHNIQUE : gestion technique et comptes.
- SUPERVISEUR : supervision, statistiques, audit.
- CHEF_SERVICE : opérations administratives et validation.
- AGENT : consultation et enregistrement des remises selon délégation.

## 6. Confidentialité
Public : statut général et consigne de démarche.
Interne : identifiants, date de naissance, filière, historique, détails de diplôme.
Ne jamais afficher publiquement : numéro du diplôme, notes, téléphone, e-mail, adresse, date de naissance, scan de document.
La recherche utilise un message uniforme en cas d'échec et ne retourne que le type, l'année et le statut du diplôme. Nom/date/année sont plus faciles à deviner qu'un matricule : valider le risque d'énumération avec l'IUT avant d'y charger des données réelles.

## 7. Import de données
1. Téléversement contrôlé (taille, extension, MIME).
2. Stockage hors du répertoire public.
3. Lecture des en-têtes et des lignes.
4. Mise en zone tampon.
5. Rapport d'anomalies et de doublons.
6. Validation par un responsable.
7. Intégration idempotente avec conservation de la source et du numéro de ligne.

La livraison inclut le téléversement et l'analyse initiale en staging. Le mapping réel et l'intégration finale doivent être adaptés après réception des vrais fichiers anonymisés.

## 8. Reçus
Référence unique générée au guichet, liée au diplôme et à l'agent. Le reçu PDF est généré après remise; sa mise en page doit être comparée et approuvée avec le modèle officiel de l'IUT. QR vérifiable et archivage signé restent hors périmètre.

## 9. Exigences non fonctionnelles
- Interface mobile-first et légère.
- MySQL InnoDB / utf8mb4.
- Requêtes préparées PDO.
- Échappement HTML systématique.
- Jetons CSRF sur formulaires POST.
- Hachage Argon2id si disponible.
- Régénération d'identifiant de session à la connexion.
- Cookies HttpOnly et SameSite.
- Limitation basique de tentatives de recherche.
- Fichiers d'import stockés hors webroot.
- Journalisation des actions critiques.

## 10. Hors périmètre / à confirmer
- 2FA TOTP obligatoire.
- CAPTCHA de fournisseur.
- Notifications SMS / WhatsApp.
- Prise de rendez-vous.
- Gestion complète des procurations.
- QR public de vérification des reçus.
- Multi-sites incluant Édéa.
- Import automatique des tableaux Word.
- Workflow complet de correction administrative.
- Signature électronique.

## 11. Décisions attendues de la direction
- Validation du domaine et du sous-domaine.
- Validation du responsable de la qualité des données.
- Pièces acceptées pour vérification d'identité.
- Procédure de retrait par mandataire.
- Sites concernés (Douala / Édéa).
- Volumétrie et conservation des données.
- Modèle de reçu et procédure de signature.
- Hébergement et responsabilités de maintenance.
