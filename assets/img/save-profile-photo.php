<?php
/* Sube/actualiza la foto de perfil del usuario logueado.
   Se guarda como <userLabel>.<ext> en /uploads/profile-photos/.
   ANTES se guardaba en /assets/img/ pero las fotos de Capi/Angie están
   tracked en el repo (como seeds default) y el auto-deploy de Hostinger
   hace `git reset --hard`, sobrescribiendo cualquier foto subida que
   coincida con esos nombres. Las subidas viven ahora fuera del directorio
   tracked, donde sobreviven los deploys.
   Limpia también versiones legacy en assets/img/ por si las hubiera. */
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

/* Nombre base = label sin caracteres raros (igual que getUserImage). */
$baseName  = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
$assetsDir = __DIR__;                                       /* legacy: assets/img/ */
$uploadDir = dirname(__DIR__, 2) . '/uploads/profile-photos';

/* Crea el directorio de subidas si no existe (típico tras un deploy
   limpio o primera instalación). */
if (!is_dir($uploadDir)) {
    if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        echo json_encode(['error' => 'No se pudo crear ' . $uploadDir]); exit;
    }
}

/* Borrar cualquier versión previa, en AMBOS sitios:
   - El nuevo (uploads/profile-photos/): por si el usuario cambia de
     extensión (jpg → png) y el viejo seguiría siendo encontrado.
   - El viejo (assets/img/): para limpiar fotos subidas con el path
     antiguo, que ya no se servirán. Capi.jpg / Angie.jpg en assets/img/
     SE PRESERVAN porque son seed del repo (no se borrarían sin que
     el usuario haya subido una propia, en cuyo caso queremos que la
     suya en uploads/ tome precedencia y aún así borrar el seed de
     assets/img/ es indeseable — sólo borramos cuando la foto antigua
     pertenece a este mismo $baseName y no es la del seed protegido). */
$seedProtected = in_array($baseName, ['Capi', 'Angie'], true);
foreach ($allowed as $e) {
    $oldUploads = $uploadDir   . '/' . $baseName . '.' . $e;
    if (file_exists($oldUploads)) @unlink($oldUploads);
    if (!$seedProtected) {
        $oldAssets = $assetsDir . '/' . $baseName . '.' . $e;
        if (file_exists($oldAssets)) @unlink($oldAssets);
    }
}

$dest = $uploadDir . '/' . $baseName . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'No se pudo guardar la foto (revisa permisos)']); exit;
}

/* Guarda también el binario en la BD (columnas photo_data + photo_ext
   de usuarios). Es la fuente de verdad: si el filesystem se borra en un
   deploy, getUserImage() restaura el archivo desde aquí. */
try {
    require_once dirname(__DIR__, 2) . '/db.php';
    /* Asegura que existan las columnas (idempotente). Si la migración
       no había corrido en Hostinger, sin esto el UPDATE fallaría
       silenciosamente y la foto vivía solo en filesystem → muerte por
       deploy. */
    if (function_exists('_ensureUserPhotoColumns')) {
        _ensureUserPhotoColumns($pdo);
    }
    $blob = file_get_contents($dest);
    if ($blob !== false) {
        $stmt = $pdo->prepare("UPDATE usuarios SET photo_data = ?, photo_ext = ? WHERE user_key = ?");
        $stmt->bindParam(1, $blob, PDO::PARAM_LOB);
        $stmt->bindValue(2, $ext);
        $stmt->bindValue(3, $userKey);
        $stmt->execute();
    }
} catch (Throwable $e) {
    /* Si la BD aún no tiene las columnas (migración pendiente o sin
       permisos de ALTER), no rompemos el upload — la foto sigue
       accesible vía filesystem hasta el próximo deploy. */
}

echo json_encode([
    'ok'    => true,
    'photo' => 'uploads/profile-photos/' . $baseName . '.' . $ext,
]);
