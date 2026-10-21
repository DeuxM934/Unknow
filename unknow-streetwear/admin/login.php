<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../lib.php';
$configPath = __DIR__ . '/../data/admin.json';
$config = is_file($configPath) ? json_decode(file_get_contents($configPath) ?: '{}', true) : [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = (string)($_POST['username'] ?? '');
    $pass = (string)($_POST['password'] ?? '');
    if (is_array($config) && isset($config['username'], $config['password_hash']) && hash_equals((string)$config['username'], $user) && password_verify($pass, (string)$config['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        header('Location: dashboard.php'); exit;
    }
    $error = 'Identifiants incorrects.';
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>UNKNOW® — Admin</title><link rel="stylesheet" href="../style.css"></head><body><header class="header"><a class="brand" href="../index.php">UNKNOW<span>®</span></a><nav><a href="../index.php">RETOUR AU SHOP ↗</a></nav></header><section class="inner-page narrow"><p class="eyebrow">PRIVATE ACCESS</p><h1>ADMIN<span>.</span></h1><?php if ($error): ?><p class="warning"><?= esc($error) ?></p><?php endif; ?><form method="post" class="admin-form"><label>IDENTIFIANT<input name="username" required autocomplete="username"></label><label>MOT DE PASSE<input type="password" name="password" required autocomplete="current-password"></label><button class="button button-light">SE CONNECTER ↗</button></form><p class="muted">Configure d'abord ton compte dans le fichier data/admin.json, selon le guide README.</p></section></body></html>
