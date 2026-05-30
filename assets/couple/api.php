<?php
/* ──────────────────────────────────────────────────────────
   COUPLE API — router único para Calendario / Pareja
   ──────────────────────────────────────────────────────────
   Acciones (vía ?action= o $_POST['action']):

   GET   ?action=get-momentos&pareja_id=N
   GET   ?action=get-recordatorios&pareja_id=N
   GET   ?action=get-users
   GET   ?action=get-partner-invites

   POST  ?action=save-momento          { pareja_id, titulo, fecha, descripcion, emocion }
   POST  ?action=save-recordatorio     { pareja_id, titulo, fecha, tipo, descripcion }
   POST  ?action=delete-momento        { id }
   POST  ?action=delete-recordatorio   { id }
   POST  ?action=invite-partner        { toUser }
   POST  ?action=respond-partner-invite{ inviteId, action: 'accept'|'reject', fecha? }
   POST  ?action=upload-foto           multipart: momento_id + foto
   ────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once __DIR__ . '/../../db.php';

$u        = requireAuth();
$userKey  = $u['key'];
$action   = $_GET['action'] ?? $_POST['action'] ?? '';

/* Helper: obtiene el id (PK) en la tabla `usuarios` del usuario actual.
   Resuelve por user_key — más fiable que casar username con lower(label). */
function getCurrentUserId(PDO $pdo, string $userKey): ?int {
    static $cache = [];
    if (array_key_exists($userKey, $cache)) return $cache[$userKey];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $cache[$userKey] = $id ? (int)$id : null;
}

switch ($action) {

/* ─── Momentos ─── */
case 'get-momentos': {
    $userId = getCurrentUserId($pdo, $userKey);
    if (!$userId) jsonResponse([]);
    $parejaId = (int)($_GET['pareja_id'] ?? 0);
    if ($parejaId) {
        $stmt = $pdo->prepare("SELECT m.id, m.titulo, m.descripcion, m.emocion, m.fecha, m.foto, u.username AS autor
                               FROM momentos m JOIN usuarios u ON m.usuario_id = u.id
                               WHERE m.pareja_id = ? ORDER BY m.fecha ASC");
        $stmt->execute([$parejaId]);
   } else {
        $stmt = $pdo->prepare("SELECT id, titulo, descripcion, emocion, fecha, foto, ? AS autor
                               FROM momentos WHERE usuario_id = ? AND pareja_id IS NULL
                               ORDER BY fecha ASC");
        $stmt->execute([strtolower($GLOBALS['loginUsers'][$userKey]['label']), $userId]);
    }
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

case 'save-momento': {
    $userId = getCurrentUserId($pdo, $userKey);
    if (!$userId) jsonError('Usuario no encontrado');
    $b      = jsonBody();
    $titulo = trim($b['titulo'] ?? '');
    $fecha  = $b['fecha'] ?? '';
    if (!$titulo || !$fecha) jsonError('Datos incompletos');
    $stmt = $pdo->prepare("INSERT INTO momentos (pareja_id, usuario_id, titulo, descripcion, emocion, fecha)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $pid = (int)($b['pareja_id'] ?? 0);
$stmt->execute([
    $pid > 0 ? $pid : null, $userId, $titulo,
    trim($b['descripcion'] ?? ''), $b['emocion'] ?? '', $fecha,
]);
    jsonResponse(['ok' => true, 'id' => $pdo->lastInsertId()]);
}

case 'delete-momento': {
    $userId = getCurrentUserId($pdo, $userKey);
    if (!$userId) jsonError('Usuario no encontrado');
    $id = (int)(jsonBody()['id'] ?? 0);
    if (!$id) jsonError('ID inválido');
    $stmt = $pdo->prepare("SELECT foto FROM momentos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonError('No autorizado', 403);
    if ($row['foto']) {
        $ruta = __DIR__ . '/../../uploads/momentos/' . $row['foto'];
        if (file_exists($ruta)) @unlink($ruta);
    }
    $pdo->prepare("DELETE FROM momentos WHERE id = ?")->execute([$id]);
    jsonResponse(['ok' => true]);
}

case 'upload-foto': {
    $momentoId = (int)($_POST['momento_id'] ?? 0);
    if (!$momentoId) jsonError('ID de momento inválido');
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) jsonError('Error al subir la foto');
    $file = $_FILES['foto'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) jsonError('Formato no permitido');
    if ($file['size'] > 5 * 1024 * 1024) jsonError('La foto no puede superar 5MB');
    $filename = 'momento_' . $momentoId . '_' . time() . '.' . $ext;
    $destino  = __DIR__ . '/../../uploads/momentos/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destino)) jsonError('Error al guardar la foto', 500);
    $pdo->prepare("UPDATE momentos SET foto = ? WHERE id = ?")->execute([$filename, $momentoId]);
    jsonResponse(['ok' => true, 'foto' => $filename]);
}

/* ─── Recordatorios ─── */
case 'get-recordatorios': {
    $userId = getCurrentUserId($pdo, $userKey);
    if (!$userId) jsonResponse([]);
    $parejaId = (int)($_GET['pareja_id'] ?? 0);
    if ($parejaId) {
        $stmt = $pdo->prepare("SELECT r.id, r.titulo, r.fecha, r.tipo, r.descripcion, u.username AS autor
                               FROM recordatorios r JOIN usuarios u ON r.usuario_id = u.id
                               WHERE r.pareja_id = ? ORDER BY r.fecha ASC");
        $stmt->execute([$parejaId]);
    } else {
        $stmt = $pdo->prepare("SELECT r.id, r.titulo, r.fecha, r.tipo, r.descripcion, u.username AS autor
                               FROM recordatorios r JOIN usuarios u ON r.usuario_id = u.id
                               WHERE r.usuario_id = ? AND r.pareja_id = 0 ORDER BY r.fecha ASC");
        $stmt->execute([$userId]);
    }
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

case 'save-recordatorio': {
    $userId = getCurrentUserId($pdo, $userKey);
    if (!$userId) jsonError('Usuario no encontrado');
    $b      = jsonBody();
    $titulo = trim($b['titulo'] ?? '');
    $fecha  = $b['fecha'] ?? '';
    if (!$titulo || !$fecha) jsonError('Datos incompletos');
    $stmt = $pdo->prepare("INSERT INTO recordatorios (usuario_id, pareja_id, titulo, fecha, tipo, descripcion)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId, (int)($b['pareja_id'] ?? 0), $titulo, $fecha,
        $b['tipo'] ?? 'otro', trim($b['descripcion'] ?? ''),
    ]);
    jsonResponse(['ok' => true]);
}

case 'delete-recordatorio': {
    $userId = getCurrentUserId($pdo, $userKey);
    if (!$userId) jsonError('Usuario no encontrado');
    $id = (int)(jsonBody()['id'] ?? 0);
    if (!$id) jsonError('ID inválido');
    $stmt = $pdo->prepare("SELECT id FROM recordatorios WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) jsonError('No autorizado', 403);
    $pdo->prepare("DELETE FROM recordatorios WHERE id = ?")->execute([$id]);
    jsonResponse(['ok' => true]);
}

/* ─── Usuarios ─── */
case 'get-users': {
    $users = [];
    foreach ($GLOBALS['loginUsers'] as $key => $u2) {
        if ($key === $userKey) continue;
        $users[] = ['key' => $key, 'label' => $u2['label']];
    }
    jsonResponse($users);
}

/* ─── Invitaciones de pareja (SQL: partner_invites) ─── */
case 'get-partner-invites': {
    $uid = getCurrentUserId($pdo, $userKey);
    if (!$uid) jsonResponse([]);
    $stmt = $pdo->prepare("SELECT pi.id, fu.user_key AS fromUser, fu.label AS fromLabel
                           FROM partner_invites pi
                           JOIN usuarios fu ON pi.from_user_id = fu.id
                           WHERE pi.to_user_id = ?
                           ORDER BY pi.created_at ASC");
    $stmt->execute([$uid]);
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

case 'invite-partner': {
    $b      = jsonBody();
    $toUser = isset($b['toUser']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $b['toUser']) : '';
    if (!$toUser) jsonError('Datos incompletos');
    if (!array_key_exists($toUser, $GLOBALS['loginUsers'])) jsonError('Usuario inválido');
    if ($toUser === $userKey) jsonError('No puedes invitarte a ti mismo');

    $fromId = getCurrentUserId($pdo, $userKey);
    $toId   = getCurrentUserId($pdo, $toUser);
    if (!$fromId || !$toId) jsonError('Usuario no encontrado en BD', 500);

    /* UNIQUE(to_user_id, from_user_id) → si ya existe lanza 23000;
       lo traducimos al mismo mensaje que daba la versión JSON. */
    try {
        $pdo->prepare("INSERT INTO partner_invites (to_user_id, from_user_id) VALUES (?, ?)")
            ->execute([$toId, $fromId]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') jsonError('Ya tienes una invitación pendiente');
        throw $e;
    }
    jsonResponse(['ok' => true]);
}

case 'respond-partner-invite': {
    $b        = jsonBody();
    $inviteId = (int)($b['inviteId'] ?? 0);
    $act      = $b['action']   ?? '';
    $fecha    = $b['fecha']    ?? '';
    if (!$inviteId || !in_array($act, ['accept','reject'])) jsonError('Datos incompletos');

    $uid = getCurrentUserId($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);

    /* Solo el destinatario puede responder a la invitación. */
    $stmt = $pdo->prepare("SELECT from_user_id FROM partner_invites WHERE id = ? AND to_user_id = ?");
    $stmt->execute([$inviteId, $uid]);
    $fromId = $stmt->fetchColumn();
    if (!$fromId) jsonError('Invitación no encontrada', 404);

    $pdo->prepare("DELETE FROM partner_invites WHERE id = ?")->execute([$inviteId]);

    if ($act === 'reject') jsonResponse(['ok' => true]);
    if (!$fecha) jsonError('Falta la fecha de inicio');

    /* Aceptar → crear pareja (si no existe ya). */
    $stmt = $pdo->prepare("SELECT id FROM parejas
                           WHERE (usuario1_id = ? AND usuario2_id = ?)
                              OR (usuario1_id = ? AND usuario2_id = ?)");
    $stmt->execute([$fromId, $uid, $uid, $fromId]);
    if ($stmt->fetch()) jsonError('Ya sois pareja');

    $pdo->prepare("INSERT INTO parejas (usuario1_id, usuario2_id, fecha_inicio) VALUES (?, ?, ?)")
        ->execute([(int)$fromId, $uid, $fecha]);
    jsonResponse(['ok' => true]);
}
case 'save-momento-foto-url': {
    $b         = jsonBody();
    $momentoId = (int)($b['momento_id'] ?? 0);
    $fotoUrl   = trim($b['foto_url']    ?? '');
    if (!$momentoId || !$fotoUrl) jsonError('Datos incompletos');
    $pdo->prepare("UPDATE momentos SET foto = ? WHERE id = ?")->execute([$fotoUrl, $momentoId]);
    jsonResponse(['ok' => true]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
