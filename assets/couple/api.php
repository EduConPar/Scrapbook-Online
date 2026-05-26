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

$u           = requireAuth();
$userKey     = $u['key'];
$userLabel   = $u['label'];
$action      = $_GET['action'] ?? $_POST['action'] ?? '';

/* Helper: obtiene el id (PK) en la tabla `usuarios` del usuario actual */
function getCurrentUserId(PDO $pdo, string $userLabel): ?int {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt->execute([strtolower($userLabel)]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['id'] : null;
}

/* Helper: ruta al fichero de invitaciones de un usuario */
function inviteFile(string $userKey): string {
    return __DIR__ . '/' . $userKey . '-partner-invites.json';
}

switch ($action) {

/* ─── Momentos ─── */
case 'get-momentos': {
    $userId = getCurrentUserId($pdo, $userLabel);
    if (!$userId) jsonResponse([]);
    $parejaId = (int)($_GET['pareja_id'] ?? 0);
    if ($parejaId) {
        $stmt = $pdo->prepare("SELECT m.id, m.titulo, m.descripcion, m.emocion, m.fecha, m.foto, u.username AS autor
                               FROM momentos m JOIN usuarios u ON m.usuario_id = u.id
                               WHERE m.pareja_id = ? ORDER BY m.fecha ASC");
        $stmt->execute([$parejaId]);
    } else {
        $stmt = $pdo->prepare("SELECT id, titulo, descripcion, emocion, fecha, foto, ? AS autor
                               FROM momentos WHERE usuario_id = ? AND pareja_id = 0
                               ORDER BY fecha ASC");
        $stmt->execute([strtolower($userLabel), $userId]);
    }
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

case 'save-momento': {
    $userId = getCurrentUserId($pdo, $userLabel);
    if (!$userId) jsonError('Usuario no encontrado');
    $b      = jsonBody();
    $titulo = trim($b['titulo'] ?? '');
    $fecha  = $b['fecha'] ?? '';
    if (!$titulo || !$fecha) jsonError('Datos incompletos');
    $stmt = $pdo->prepare("INSERT INTO momentos (pareja_id, usuario_id, titulo, descripcion, emocion, fecha)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        (int)($b['pareja_id'] ?? 0), $userId, $titulo,
        trim($b['descripcion'] ?? ''), $b['emocion'] ?? '', $fecha,
    ]);
    jsonResponse(['ok' => true, 'id' => $pdo->lastInsertId()]);
}

case 'delete-momento': {
    $userId = getCurrentUserId($pdo, $userLabel);
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
    $userId = getCurrentUserId($pdo, $userLabel);
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
    $userId = getCurrentUserId($pdo, $userLabel);
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
    $userId = getCurrentUserId($pdo, $userLabel);
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

/* ─── Invitaciones de pareja ─── */
case 'get-partner-invites': {
    $f = inviteFile($userKey);
    if (!file_exists($f)) jsonResponse([]);
    $list = json_decode(file_get_contents($f), true);
    jsonResponse(is_array($list) ? $list : []);
}

case 'invite-partner': {
    $b      = jsonBody();
    $toUser = isset($b['toUser']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $b['toUser']) : '';
    if (!$toUser) jsonError('Datos incompletos');
    if (!array_key_exists($toUser, $GLOBALS['loginUsers'])) jsonError('Usuario inválido');
    if ($toUser === $userKey) jsonError('No puedes invitarte a ti mismo');

    $f = inviteFile($toUser);
    $invites = file_exists($f) ? json_decode(file_get_contents($f), true) : [];
    if (!is_array($invites)) $invites = [];
    foreach ($invites as $inv) {
        if (($inv['fromUser'] ?? '') === $userKey) jsonError('Ya tienes una invitación pendiente');
    }
    $invites[] = [
        'id'        => 'pinv_' . time() . '_' . rand(1000, 9999),
        'fromUser'  => $userKey,
        'fromLabel' => $GLOBALS['loginUsers'][$userKey]['label'],
    ];
    file_put_contents($f, json_encode($invites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    jsonResponse(['ok' => true]);
}

case 'respond-partner-invite': {
    $b        = jsonBody();
    $inviteId = $b['inviteId'] ?? '';
    $act      = $b['action']   ?? '';
    $fecha    = $b['fecha']    ?? '';
    if (!$inviteId || !in_array($act, ['accept','reject'])) jsonError('Datos incompletos');

    $f = inviteFile($userKey);
    if (!file_exists($f)) jsonError('No hay invitaciones', 404);
    $invites = json_decode(file_get_contents($f), true);
    if (!is_array($invites)) jsonError('Error leyendo invitaciones', 500);

    $invite = null; $remaining = [];
    foreach ($invites as $inv) {
        if (($inv['id'] ?? '') === $inviteId) $invite = $inv;
        else $remaining[] = $inv;
    }
    if (!$invite) jsonError('Invitación no encontrada', 404);
    file_put_contents($f, json_encode($remaining, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($act === 'reject') jsonResponse(['ok' => true]);
    if (!$fecha) jsonError('Falta la fecha de inicio');

    $fromUser = $invite['fromUser'];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ? OR username = ?");
    $stmt->execute([
        strtolower($GLOBALS['loginUsers'][$fromUser]['label']),
        strtolower($GLOBALS['loginUsers'][$userKey]['label']),
    ]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($usuarios) < 2) jsonError('Usuarios no encontrados en la base de datos');

    $ids = array_column($usuarios, 'id');
    $stmt = $pdo->prepare("SELECT id FROM parejas
                           WHERE (usuario1_id = ? AND usuario2_id = ?)
                              OR (usuario1_id = ? AND usuario2_id = ?)");
    $stmt->execute([$ids[0], $ids[1], $ids[1], $ids[0]]);
    if ($stmt->fetch()) jsonError('Ya sois pareja');

    $pdo->prepare("INSERT INTO parejas (usuario1_id, usuario2_id, fecha_inicio) VALUES (?, ?, ?)")
        ->execute([$ids[0], $ids[1], $fecha]);
    jsonResponse(['ok' => true]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
