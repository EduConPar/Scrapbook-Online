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
$pareja_id = intval($body['pareja_id'] ?? 0);
$titulo = trim($body['titulo'] ?? '');
$fecha = $body['fecha'] ?? '';
$descripcion = trim($body['descripcion'] ?? '');
$emocion = $body['emocion'] ?? '';

if (!$titulo || !$fecha) { echo json_encode(['error' => 'Datos incompletos']); exit; }

$stmt = $pdo->prepare("INSERT INTO momentos (pareja_id, usuario_id, titulo, descripcion, emocion, fecha) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$pareja_id, $userId, $titulo, $descripcion, $emocion, $fecha]);

echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);