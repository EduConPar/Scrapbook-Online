<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
$userLabel = $loginUsers[$userKey]['label'];

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->execute([strtolower($userLabel)]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo json_encode(['error' => 'Usuario no encontrado']); exit; }
$userId = $user['id'];

$body = json_decode(file_get_contents('php://input'), true);
$momentoId = intval($body['id'] ?? 0);
if (!$momentoId) { echo json_encode(['error' => 'ID inválido']); exit; }

// Verificar que el momento pertenece al usuario
$stmt = $pdo->prepare("SELECT foto FROM momentos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$momentoId, $userId]);
$momento = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$momento) { echo json_encode(['error' => 'No autorizado']); exit; }

// Borrar foto del servidor si existe
if ($momento['foto']) {
    $ruta = __DIR__ . '/../../uploads/momentos/' . $momento['foto'];
    if (file_exists($ruta)) unlink($ruta);
}

$stmt = $pdo->prepare("DELETE FROM momentos WHERE id = ?");
$stmt->execute([$momentoId]);
echo json_encode(['ok' => true]);