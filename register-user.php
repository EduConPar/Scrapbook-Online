<?php
/* Registro de usuario: INSERT en `usuarios` con password bcrypt + crea
   stub de escritorio + (opcional) sube foto de perfil. Sin tocar JSON.

   IMPORTANTE: garantizamos que SIEMPRE se devuelva JSON, incluso si hay un
   fatal error de PHP o el DB connect falla. Si no, el frontend ve la página
   HTML de error del host (InfinityFree etc.) y muestra "Error de red". */
header('Content-Type: application/json; charset=utf-8');
ob_start();   // capturar cualquier output accidental (warnings, BOM, etc.)

/* Handler de error/fatal que SI O SI emite JSON parseable */
function _regFatal($msg, $http = 500) {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $msg]);
    exit;
}
set_error_handler(function($severity, $msg, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    _regFatal('PHP error: ' . $msg . ' @ ' . basename($file) . ':' . $line);
});
register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR], true)) {
        _regFatal('Fatal: ' . $e['message'] . ' @ ' . basename($e['file']) . ':' . $e['line']);
    }
});

try {
    require_once __DIR__ . '/db.php';   /* trae también assets/config.php */
} catch (Throwable $e) {
    _regFatal('DB include: ' . $e->getMessage());
}

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

/* Comprobar nombre único (case-insensitive, contra `usuarios.label`) */
$lowerNew = mb_strtolower($username);
$stmt = $pdo->prepare("SELECT 1 FROM usuarios WHERE LOWER(label) = ? OR username = ?");
$stmt->execute([$lowerNew, $lowerNew]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Ese nombre ya existe']); exit;
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

/* Siguiente user_key libre: maxN(user_key 'userN') + 1 */
$stmt = $pdo->query("SELECT user_key FROM usuarios WHERE user_key REGEXP '^user[0-9]+$'");
$maxNum = 0;
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $key) {
    if (preg_match('/^user(\d+)$/', $key, $m)) {
        $n = (int)$m[1];
        if ($n > $maxNum) $maxNum = $n;
    }
}
$newKey = 'user' . ($maxNum + 1);

/* Stub de escritorio (<label-lowercase>-desktop.php) — se crea ANTES del INSERT
   para no dejar un usuario sin escritorio si falla la escritura del fichero. */
$desktopStub = __DIR__ . '/' . strtolower($username) . '-desktop.php';
if (!file_exists($desktopStub)) {
    $stubContent = "<?php \$desktopLabel = " . var_export($username, true) . "; require 'desktop-base.php';\n";
    if (@file_put_contents($desktopStub, $stubContent) === false) {
        echo json_encode(['error' => 'No se pudo crear el escritorio del usuario']); exit;
    }
}

/* INSERT en usuarios con bcrypt */
try {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (user_key, username, label, password)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$newKey, $lowerNew, $username, $hash]);
} catch (Throwable $e) {
    @unlink($desktopStub);
    echo json_encode(['error' => 'No se pudo guardar el usuario en BD']); exit;
}

echo json_encode(['ok' => true, 'userKey' => $newKey, 'label' => $username]);
