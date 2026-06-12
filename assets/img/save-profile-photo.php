<?php
/* Sube/actualiza la foto de perfil del usuario logueado.
   Se guarda como <userLabel>.<ext> en assets/img/ y elimina la versión
   anterior si existía con otra extensión. */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
$label = $loginUsers[$userKey]['label'];

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Error al subir el archivo']); exit;
}

$file = $_FILES['photo'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed, true)) { echo json_encode(['error' => 'Formato no permitido (jpg/png/gif/webp)']); exit; }
if ($file['size'] > 5 * 1024 * 1024) { echo json_encode(['error' => 'Máximo 5MB']); exit; }

/* Validación real de imagen */
$info = @getimagesize($file['tmp_name']);
if (!$info) { echo json_encode(['error' => 'Archivo no válido como imagen']); exit; }

/* Nombre base = label sin caracteres raros (igual que getUserImage) */
$baseName = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
$dir      = __DIR__;

/* Borrar versiones previas con cualquier extensión */
foreach ($allowed as $e) {
    $old = $dir . '/' . $baseName . '.' . $e;
    if (file_exists($old)) @unlink($old);
}

$dest = $dir . '/' . $baseName . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'No se pudo guardar la foto (revisa permisos)']); exit;
}

echo json_encode([
    'ok'    => true,
    'photo' => 'assets/img/' . $baseName . '.' . $ext,
]);
