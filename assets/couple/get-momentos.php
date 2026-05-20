<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['user'])) { echo json_encode([]); exit; }
$userKey = $_SESSION['user'];
$userLabel = $loginUsers[$userKey]['label'];

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->execute([strtolower($userLabel)]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { echo json_encode([]); exit; }
$userId = $user['id'];

$pareja_id = intval($_GET['pareja_id'] ?? 0);

if ($pareja_id) {
    $stmt = $pdo->prepare("SELECT m.id, m.titulo, m.descripcion, m.emocion, m.fecha, m.foto, u.username as autor FROM momentos m JOIN usuarios u ON m.usuario_id = u.id WHERE m.pareja_id = ? ORDER BY m.fecha ASC");
    $stmt->execute([$pareja_id]);
} else {
    $stmt = $pdo->prepare("SELECT id, titulo, descripcion, emocion, fecha, foto, ? as autor FROM momentos WHERE usuario_id = ? AND pareja_id = 0 ORDER BY fecha ASC");
    $stmt->execute([strtolower($userLabel), $userId]);
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));