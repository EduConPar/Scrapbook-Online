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
$recId = intval($body['id'] ?? 0);
if (!$recId) { echo json_encode(['error' => 'ID inválido']); exit; }

$stmt = $pdo->prepare("SELECT id FROM recordatorios WHERE id = ? AND usuario_id = ?");
$stmt->execute([$recId, $userId]);
if (!$stmt->fetch()) { echo json_encode(['error' => 'No autorizado']); exit; }

$stmt = $pdo->prepare("DELETE FROM recordatorios WHERE id = ?");
$stmt->execute([$recId]);
echo json_encode(['ok' => true]);