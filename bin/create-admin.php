<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

use App\Core\Database;

if (PHP_OS_FAMILY === 'Windows' && ($argv[1] ?? '') !== '--prepared') {
    $script = __DIR__ . '/create-admin.ps1';
    if (!is_file($script)) {
        fwrite(STDERR, "Script PowerShell de saisie sécurisée introuvable.\n");
        exit(1);
    }
    passthru('powershell.exe -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script) . ' -PhpPath ' . escapeshellarg(PHP_BINARY), $status);
    exit($status);
}

echo "Création d’un compte administrateur IUT-Diplômes\n";
if (($argv[1] ?? '') === '--prepared') {
    $name = trim((string) getenv('IUT_ADMIN_NAME'));
    $email = mb_strtolower(trim((string) getenv('IUT_ADMIN_EMAIL')));
    $password = (string) getenv('IUT_ADMIN_PASSWORD');
} else {
    $name = trim(readline('Nom complet : '));
    $email = mb_strtolower(trim(readline('Adresse e-mail : ')));
    if (PHP_OS_FAMILY === 'Windows') {
        fwrite(STDERR, "Relancez avec php bin/create-admin.php pour la saisie masquée.\n");
        exit(1);
    }
    echo 'Nouveau mot de passe (12 caractères minimum) : ';
    shell_exec('stty -echo');
    try {
        $password = rtrim((string) fgets(STDIN), "\r\n");
    } finally {
        shell_exec('stty echo');
        echo PHP_EOL;
    }
    echo 'Confirmer le mot de passe : ';
    shell_exec('stty -echo');
    try {
        $confirmation = rtrim((string) fgets(STDIN), "\r\n");
    } finally {
        shell_exec('stty echo');
        echo PHP_EOL;
    }
}

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Nom ou adresse e-mail invalide.\n");
    exit(1);
}
if (strlen($password) < 12 || (isset($confirmation) && !hash_equals($password, $confirmation))) {
    fwrite(STDERR, "Mot de passe trop court ou confirmation différente.\n");
    exit(1);
}

$pdo = Database::connection();
if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'iut_diplomes') {
    fwrite(STDERR, "La base active n’est pas iut_diplomes. Vérifiez la configuration locale.\n");
    exit(1);
}

$role = $pdo->prepare("SELECT id FROM roles WHERE code = 'ADMIN_TECHNIQUE' LIMIT 1");
$role->execute();
$roleId = $role->fetchColumn();
if (!$roleId) {
    fwrite(STDERR, "Le rôle ADMIN_TECHNIQUE n’existe pas. Aucun compte n’a été créé.\n");
    exit(1);
}

$existing = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? LIMIT 1');
$existing->execute([$email]);
if ($existing->fetchColumn()) {
    fwrite(STDERR, "Cette adresse e-mail possède déjà un compte. Aucun compte n’a été modifié.\n");
    exit(1);
}

$algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
$hash = password_hash($password, $algorithm);
unset($password, $confirmation);
if (($argv[1] ?? '') === '--prepared') {
    putenv('IUT_ADMIN_PASSWORD');
}
if ($hash === false) {
    fwrite(STDERR, "Impossible de protéger le mot de passe. Aucun compte n’a été créé.\n");
    exit(1);
}

try {
    $insert = $pdo->prepare('INSERT INTO utilisateurs (role_id, nom, email, mot_de_passe, actif) VALUES (?, ?, ?, ?, 1)');
    $insert->execute([(int) $roleId, $name, $email, $hash]);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "Cette adresse e-mail possède déjà un compte. Aucun compte n’a été modifié.\n");
        exit(1);
    }
    throw $exception;
}

echo "Compte créé avec le rôle ADMIN_TECHNIQUE et un mot de passe haché.\n";
