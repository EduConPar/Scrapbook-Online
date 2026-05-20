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
$tipo = $body['tipo'] ?? 'otro';
$descripcion = trim($body['descripcion'] ?? '');

if (!$titulo || !$fecha) { echo json_encode(['error' => 'Datos incompletos']); exit; }

$stmt = $pdo->prepare("INSERT INTO recordatorios (usuario_id, pareja_id, titulo, fecha, tipo, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$userId, $pareja_id, $titulo, $fecha, $tipo, $descripcion]);
echo json_encode(['ok' => true]);