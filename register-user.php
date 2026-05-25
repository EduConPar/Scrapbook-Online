<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/assets/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']); exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || mb_strlen($username) > 30) {
    echo json_encode(['error' => 'Nombre inválido (1-30 caracteres)']); exit;
}
if (!preg_match('/^[A-Za-z0-9_-]+$/', $username)) {
    echo json_encode(['error' => 'Solo letras, números, _ y -']); exit;
}
if (strlen($password) < 1) {
    echo json_encode(['error' => 'Contraseña vacía']); exit;
}

/* Comprobar nombre único (case-insensitive) */
$lowerNew = mb_strtolower($username);
foreach ($loginUsers as $u) {
    if (mb_strtolower($u['label']) === $lowerNew) {
        echo json_encode(['error' => 'Ese nombre ya existe']); exit;
    }
}

/* Foto opcional */
$hasPhoto = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && $_FILES['photo']['size'] > 0;
if ($hasPhoto) {
    $f = $_FILES['photo'];
    if ($f['size'] > 5 * 1024 * 1024) {
        echo json_encode(['error' => 'Foto demasiado grande (máx 5MB)']); exit;
    }
    $mime = function_exists('mime_content_type') ? mime_content_type($f['tmp_name']) : '';
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        echo json_encode(['error' => 'Foto inválida (jpg/png/gif/webp)']); exit;
    }
    $ext = $allowed[$mime];

    /* Quitar fotos previas con el mismo nombre por si acaso */
    $imgDir = __DIR__ . '/assets/img/';
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $oldExt) {
        $oldPath = $imgDir . $username . '.' . $oldExt;
        if (file_exists($oldPath)) @unlink($oldPath);
    }

    $dest = $imgDir . $username . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        echo json_encode(['error' => 'No se pudo guardar la foto']); exit;
    }
}

/* Cargar lista de extras existentes y calcular el siguiente userN libre */
$jsonFile = __DIR__ . '/assets/login-users.json';
$extras = [];
if (file_exists($jsonFile)) {
    $raw = json_decode(file_get_contents($jsonFile), true);
    if (is_array($raw)) $extras = $raw;
}

$maxNum = 0;
foreach (array_keys($loginUsers) as $key) {
    if (preg_match('/^user(\d+)$/', $key, $m)) {
        $n = (int)$m[1];
        if ($n > $maxNum) $maxNum = $n;
    }
}
$newKey = 'user' . ($maxNum + 1);

/* Stub de escritorio (<label-lowercase>-desktop.php) — se crea ANTES de añadir el usuario
   para no dejar un usuario sin escritorio si la escritura falla. */
$desktopStub = __DIR__ . '/' . strtolower($username) . '-desktop.php';
if (!file_exists($desktopStub)) {
    $stubContent = "<?php \$desktopLabel = " . var_export($username, true) . "; require 'desktop-base.php';\n";
    $written = @file_put_contents($desktopStub, $stubContent);
    if ($written === false) {
        echo json_encode(['error' => 'No se pudo crear el escritorio del usuario']); exit;
    }
}

$extras[$newKey] = ['label' => $username, 'password' => $password];
if (@file_put_contents($jsonFile, json_encode($extras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    /* Revertir el stub si no podemos guardar el usuario */
    @unlink($desktopStub);
    echo json_encode(['error' => 'No se pudo guardar el usuario']); exit;
}

echo json_encode(['ok' => true, 'userKey' => $newKey, 'label' => $username]);
