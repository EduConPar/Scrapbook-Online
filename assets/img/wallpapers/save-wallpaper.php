<?php
/* Sube/actualiza un wallpaper.
   - Sin `theme`  → wallpaper global del usuario: <label>-wallpaper.<ext>
   - Con `theme`  → wallpaper del tema: theme-<safeTheme>-<label>-wallpaper.<ext>
     y guarda la ruta en themes.wallpaper para ese usuario+tema. */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(dirname(__DIR__)) . '/config.php';

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

$info = @getimagesize($file['tmp_name']);
if (!$info) { echo json_encode(['error' => 'Archivo no válido como imagen']); exit; }

$safeLabel = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label));
$theme     = isset($_POST['theme']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['theme']) : '';
$baseName  = $theme !== ''
    ? 'theme-' . $theme . '-' . $safeLabel . '-wallpaper'
    : $safeLabel . '-wallpaper';
$dir = __DIR__;

/* Borra otras versiones del mismo wallpaper (cualquier extensión) */
foreach ($allowed as $e) {
    $old = $dir . '/' . $baseName . '.' . $e;
    if (file_exists($old)) @unlink($old);
}

$dest = $dir . '/' . $baseName . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'No se pudo guardar el archivo (revisa permisos)']); exit;
}

$relPath = 'assets/img/wallpapers/' . $baseName . '.' . $ext;

/* Persistir blob en BD para que sobreviva al `git reset --hard` del
   deploy (assets/img/wallpapers/ está gitignored pero un deploy que
   limpie el FS lo borraría también). La BD es fuente de verdad;
   getUserWallpaper() restaura desde aquí si el archivo falta. */
require_once dirname(dirname(dirname(__DIR__))) . '/db.php';
try {
    if (function_exists('_ensureUserPhotoColumns')) {
        _ensureUserPhotoColumns($pdo);
    }
    $blob = @file_get_contents($dest);
    if ($blob !== false) {
        if ($theme !== '') {
            /* Tema: ruta + blob en `themes` para ese user+tema */
            $stmt = $pdo->prepare("UPDATE themes t JOIN usuarios u ON t.user_id = u.id
                                   SET t.wallpaper = ?, t.wallpaper_data = ?, t.wallpaper_ext = ?
                                   WHERE u.user_key = ? AND t.name = ?");
            $stmt->bindValue(1, $relPath);
            $stmt->bindParam(2, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(3, $ext);
            $stmt->bindValue(4, $userKey);
            $stmt->bindValue(5, $theme);
            $stmt->execute();
        } else {
            /* Global del usuario: blob en `usuarios` */
            $stmt = $pdo->prepare("UPDATE usuarios SET wallpaper_data = ?, wallpaper_ext = ?
                                   WHERE user_key = ?");
            $stmt->bindParam(1, $blob, PDO::PARAM_LOB);
            $stmt->bindValue(2, $ext);
            $stmt->bindValue(3, $userKey);
            $stmt->execute();
        }
    }
} catch (Throwable $e) {
    /* Si la BD aún no tiene columnas (migración pendiente) o no hay
       permisos de ALTER, no bloqueamos la subida — el wallpaper sigue
       accesible vía filesystem hasta el próximo deploy. */
}

echo json_encode(['ok' => true, 'wallpaper' => $relPath, 'theme' => $theme]);
