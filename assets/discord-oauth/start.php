<?php
/* Inicio del OAuth2 de Discord. Genera un CSRF token, lo guarda en
   sesión asociado al user_key actual y redirige al endpoint de autorización. */
session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once __DIR__ . '/helpers.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../../index.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['discord_oauth_state'] = $state;
$_SESSION['discord_oauth_user']  = $userKey;

header('Location: ' . discordAuthUrl($state));
exit;
