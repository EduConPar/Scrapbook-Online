<?php
/* Sube/actualiza el icono del botón inicio del usuario logueado.
   Se guarda como <userLabel_lowercase>-start-icon.<ext> en
   assets/img/start-icons/ y elimina la versión anterior. */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(dirname(__DIR__)) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
$label = $loginUsers[$userKey]['label'];

if (!isset($_FILES['icon']) || $_FILES['icon']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Error al subir el archivo']); exit;
}

$file = $_FILES['icon'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
if (!in_array($ext, $allowed, true)) { echo json_encode(['error' => 'Formato no permitido (jpg/png/gif/webp/svg)']); exit; }
if ($file['size'] > 1 * 1024 * 1024) { echo json_encode(['error' => 'Máximo 1MB']); exit; }

if ($ext !== 'svg') {
    $info = @getimagesize($file['tmp_name']);
    if (!$info) { echo json_encode(['error' => 'Archivo no válido como imagen']); exit; }
}

$safeLabel = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label));
$theme     = isset($_POST['theme']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['theme']) : '';
$baseName  = $theme !== ''
    ? 'theme-' . $theme . '-' . $safeLabel . '-start-icon'
    : $safeLabel . '-start-icon';
$dir = __DIR__;

foreach ($allowed as $e) {
    $old = $dir . '/' . $baseName . '.' . $e;
    if (file_exists($old)) @unlink($old);
}

$dest = $dir . '/' . $baseName . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'No se pudo guardar el archivo (revisa permisos)']); exit;
}

$relPath = 'assets/img/start-icons/' . $baseName . '.' . $ext;

/* Persistir blob en BD para sobrevivir al deploy (assets/img/start-icons/
   está gitignored pero un FS wipe lo borraría igual). getUserStartIcon()
   restaura desde aquí si el archivo falta. */
require_once dirname(dirname(dirname(__DIR__))) . '/db.php';
try {
    if (function_exists('_ensureUserPhotoColumns')) {
        _ensureUserPhotoColumns($pdo);
    }
    $blob = @file_get_contents($dest);
    if ($blob !== false) {
        if ($theme !== '') {
            $stmt = $pdo->prepare("UPDATE themes t JOIN usuarios u ON t.user_id = u.id
                                   SET t.start_icon = ?, t.start_icon_data = ?, t.start_icon_ext = ?
                                   WHERE u.user_key = ? AND t.name = ?");
            $stmt->bindValue(1, $relPath);
            $stmt->bindParam(2, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(3, $ext);
            $stmt->bindValue(4, $userKey);
            $stmt->bindValue(5, $theme);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET start_icon_data = ?, start_icon_ext = ?
                                   WHERE user_key = ?");
            $stmt->bindParam(1, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(2, $ext);
            $stmt->bindValue(3, $userKey);
            $stmt->execute();
        }
    }
} catch (Throwable $e) {
    /* No bloquea: el icono sigue en filesystem hasta el próximo deploy. */
}

echo json_encode(['ok' => true, 'icon' => $relPath, 'theme' => $theme]);
