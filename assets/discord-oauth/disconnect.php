<?php
/* Borra el vínculo Discord del usuario logueado. Solo POST (gesto del usuario). */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST only']); exit; }

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$st = $pdo->prepare('UPDATE usuarios SET discord_user_id = NULL, discord_username = NULL WHERE user_key = ?');
$st->execute([$userKey]);
echo json_encode(['ok' => true]);
