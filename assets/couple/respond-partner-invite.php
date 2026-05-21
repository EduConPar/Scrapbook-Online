<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];

$body     = json_decode(file_get_contents('php://input'), true);
$inviteId = $body['inviteId'] ?? '';
$action   = $body['action']   ?? '';
$fecha    = $body['fecha']    ?? '';

if (!$inviteId || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['error' => 'Datos incompletos']); exit;
}

$inviteFile = __DIR__ . '/' . $userKey . '-partner-invites.json';
if (!file_exists($inviteFile)) { echo json_encode(['error' => 'No hay invitaciones']); exit; }

$invites = json_decode(file_get_contents($inviteFile), true);
if (!is_array($invites)) { echo json_encode(['error' => 'Error leyendo invitaciones']); exit; }

$invite = null;
$remaining = [];
foreach ($invites as $inv) {
    if ($inv['id'] === $inviteId) $invite = $inv;
    else $remaining[] = $inv;
}

if (!$invite) { echo json_encode(['error' => 'Invitación no encontrada']); exit; }

file_put_contents($inviteFile, json_encode($remaining, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($action === 'reject') { echo json_encode(['ok' => true]); exit; }

// Aceptar: crear la pareja en la base de datos
if (!$fecha) { echo json_encode(['error' => 'Falta la fecha de inicio']); exit; }

$fromUser = $invite['fromUser'];

// Obtener IDs de la base de datos
$stmt = $pdo->prepare("SELECT id, username FROM usuarios WHERE username = ? OR username = ?");
$stmt->execute([
    strtolower($loginUsers[$fromUser]['label']),
    strtolower($loginUsers[$userKey]['label'])
]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($usuarios) < 2) { echo json_encode(['error' => 'Usuarios no encontrados en la base de datos']); exit; }

$ids = array_column($usuarios, 'id');

// Comprobar si ya existe la pareja
$stmt = $pdo->prepare("SELECT id FROM parejas WHERE (usuario1_id = ? AND usuario2_id = ?) OR (usuario1_id = ? AND usuario2_id = ?)");
$stmt->execute([$ids[0], $ids[1], $ids[1], $ids[0]]);
if ($stmt->fetch()) { echo json_encode(['error' => 'Ya sois pareja']); exit; }

$stmt = $pdo->prepare("INSERT INTO parejas (usuario1_id, usuario2_id, fecha_inicio) VALUES (?, ?, ?)");
$stmt->execute([$ids[0], $ids[1], $fecha]);

echo json_encode(['ok' => true]);