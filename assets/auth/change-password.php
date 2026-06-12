<?php
/* ──────────────────────────────────────────────────────────────────────
   CAMBIAR CONTRASEÑA — endpoint POST con JSON
   ──────────────────────────────────────────────────────────────────────
   Recibe { current, new } en body JSON. Verifica la contraseña actual
   con password_verify contra el hash bcrypt almacenado en `usuarios`,
   y si coincide actualiza el hash con uno nuevo generado vía
   password_hash(PASSWORD_BCRYPT). Devuelve { ok: true } o
   { error: "..." } con código HTTP apropiado.

   Validaciones:
     - Sesión activa (requireAuth)
     - Ambos campos no vacíos
     - Mínimo 6 caracteres en la nueva (no es estricto pero evita "1")
     - Contraseña nueva ≠ actual (evita reset accidental con la misma)
     - Bcrypt cost 10 (default razonable)
   ────────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido', 405);
}

$auth = requireAuth();
$userKey = $auth['key'];

$body = jsonBody();
$current = isset($body['current']) ? (string)$body['current'] : '';
$newPwd  = isset($body['new'])     ? (string)$body['new']     : '';

if ($current === '' || $newPwd === '') {
    jsonError('Faltan campos');
}
if (strlen($newPwd) < 6) {
    jsonError('La nueva contraseña debe tener al menos 6 caracteres');
}
if ($current === $newPwd) {
    jsonError('La nueva contraseña tiene que ser distinta de la actual');
}

try {
    /* Leemos el hash actual directamente de BD — NO usamos $loginUsers
       en memoria porque podría estar desfasado si otro tab acaba de
       cambiar la contraseña (carrera benigna pero confusa). */
    $stmt = $pdo->prepare('SELECT password FROM usuarios WHERE user_key = ? LIMIT 1');
    $stmt->execute([$userKey]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['password'])) {
        jsonError('Usuario no encontrado', 404);
    }
    if (!password_verify($current, $row['password'])) {
        jsonError('La contraseña actual no es correcta', 403);
    }

    $newHash = password_hash($newPwd, PASSWORD_BCRYPT);
    $upd = $pdo->prepare('UPDATE usuarios SET password = ? WHERE user_key = ?');
    $upd->execute([$newHash, $userKey]);

    /* Refrescar $loginUsers en memoria para que cualquier código del
       mismo proceso vea el hash nuevo. */
    global $loginUsers;
    if (isset($loginUsers[$userKey])) $loginUsers[$userKey]['password'] = $newHash;

    jsonResponse(['ok' => true]);
} catch (Throwable $e) {
    jsonError('Error de BD: ' . $e->getMessage(), 500);
}
