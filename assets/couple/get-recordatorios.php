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
    $stmt = $pdo->prepare("SELECT r.id, r.titulo, r.fecha, r.tipo, r.descripcion, u.username as autor FROM recordatorios r JOIN usuarios u ON r.usuario_id = u.id WHERE r.pareja_id = ? ORDER BY r.fecha ASC");
    $stmt->execute([$pareja_id]);
} else {
    $stmt = $pdo->prepare("SELECT r.id, r.titulo, r.fecha, r.tipo, r.descripcion, u.username as autor FROM recordatorios r JOIN usuarios u ON r.usuario_id = u.id WHERE r.usuario_id = ? AND r.pareja_id = 0 ORDER BY r.fecha ASC");
    $stmt->execute([$userId]);
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));