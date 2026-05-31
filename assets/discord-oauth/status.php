<?php
/* Devuelve el estado del vínculo Discord del usuario logueado. */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$st = $pdo->prepare('SELECT discord_user_id, discord_username FROM usuarios WHERE user_key = ?');
$st->execute([$userKey]);
$row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
echo json_encode([
    'ok'       => true,
    'linked'   => !empty($row['discord_user_id']),
    'username' => $row['discord_username'] ?? null,
]);
