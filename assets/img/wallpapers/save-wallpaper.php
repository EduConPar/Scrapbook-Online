<?php
/* Sube/actualiza el wallpaper del usuario logueado.
   Lo guarda como <userLabel_lowercase>-wallpaper.<ext> dentro de
   assets/img/wallpapers/ y elimina la versión anterior si existía con
   otra extensión. */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(dirname(__DIR__)) . '/assets/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
$label = $loginUsers[$userKey]['label'];

if (!isset($_FILES['wallpaper']) || $_FILES['wallpaper']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Error al subir el archivo']); exit;
}

$file = $_FILES['wallpaper'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed, true)) { echo json_encode(['error' => 'Formato no permitido (jpg/png/gif/webp)']); exit; }
if ($file['size'] > 8 * 1024 * 1024) { echo json_encode(['error' => 'Máximo 8MB']); exit; }

/* Validación de imagen real (no solo extensión) */
$info = @getimagesize($file['tmp_name']);
if (!$info) { echo json_encode(['error' => 'Archivo no válido como imagen']); exit; }

$baseName = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label)) . '-wallpaper';
$dir      = __DIR__;

/* Borra otras versiones del wallpaper del usuario (cualquier extensión) */
foreach ($allowed as $e) {
    $old = $dir . '/' . $baseName . '.' . $e;
    if (file_exists($old)) @unlink($old);
}

$dest = $dir . '/' . $baseName . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'No se pudo guardar el archivo (revisa permisos)']); exit;
}

echo json_encode([
    'ok'        => true,
    'wallpaper' => 'assets/img/wallpapers/' . $baseName . '.' . $ext,
]);
