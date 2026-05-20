<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }

$momentoId = intval($_POST['momento_id'] ?? 0);
if (!$momentoId) { echo json_encode(['error' => 'ID de momento inválido']); exit; }

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Error al subir la foto']); exit;
}

$file = $_FILES['foto'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $allowed)) { echo json_encode(['error' => 'Formato no permitido']); exit; }
if ($file['size'] > 5 * 1024 * 1024) { echo json_encode(['error' => 'La foto no puede superar 5MB']); exit; }

$filename = 'momento_' . $momentoId . '_' . time() . '.' . $ext;
$destino = __DIR__ . '/../../uploads/momentos/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destino)) {
    echo json_encode(['error' => 'Error al guardar la foto']); exit;
}

$stmt = $pdo->prepare("UPDATE momentos SET foto = ? WHERE id = ?");
$stmt->execute([$filename, $momentoId]);

echo json_encode(['ok' => true, 'foto' => $filename]);
