<?php
/* ──────────────────────────────────────────────────────────────────
   EVENTS API — router para eventos de calendario

   Modelo: un usuario crea un evento (titulo, descripcion, fecha,
   duración, min/max participantes, visibility public|private) y opcional
   invita amigos. Cualquiera puede unirse a públicos no finalizados; los
   privados solo los ve y se une quien tenga invite o ya esté dentro.
   Si el cupo está lleno, el join entra en waitlist y sube auto cuando
   alguien sale.

   Cuando un usuario se une a un evento, se inserta una fila en
   `recordatorios` con un titulo prefijado "📅 Evento: …" para que
   aparezca en la grid del calendario como recordatorio. Al salir,
   se elimina esa fila.

   Acciones:
     GET   ?action=list-events
     GET   ?action=get-event&id=N
     GET   ?action=get-pending-invites
     POST  ?action=create-event  { title, description, eventDate (Y-m-d H:i:s),
                                    durationMin, minParticipants, maxParticipants,
                                    visibility ('public'|'private'),
                                    invitees: [userKey, …] }
     POST  ?action=join-event    { eventId }
     POST  ?action=leave-event   { eventId }
     POST  ?action=invite-to-event { eventId, userKey }
     POST  ?action=respond-event-invite { inviteId, action: 'accept'|'decline' }
     POST  ?action=mark-invites-notified { inviteIds: [N,…] }
     POST  ?action=delete-event  { eventId }
   ────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../social-helpers.php';

$u       = requireAuth();
$userKey = $u['key'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

/* ── Helpers ─────────────────────────────────────────────────────── */
/* jsonResponse/jsonError/jsonBody ya están definidas en assets/auth.php
   (cargado arriba). No las redeclaramos aquí. La firma de jsonError de
   auth.php devuelve {error: msg}; nosotros también pasamos el message
   como primer arg → compat. */
function ev_uid(PDO $pdo, string $userKey): ?int {
    static $cache = [];
    if (array_key_exists($userKey, $cache)) return $cache[$userKey];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $cache[$userKey] = $id ? (int)$id : null;
}
function ev_userKey(PDO $pdo, int $uid): ?string {
    static $cache = [];
    if (array_key_exists($uid, $cache)) return $cache[$uid];
    $stmt = $pdo->prepare("SELECT user_key FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    $k = $stmt->fetchColumn();
    return $cache[$uid] = $k ?: null;
}

/* ── Auto-migración de tablas ──────────────────────────────────────
   Tres tablas:
     events             — el evento en sí
     event_participants — usuarios unidos (joined) o en waitlist
                          (con FK al recordatorio que les puso en su
                           calendario, para borrarlo al salir)
     event_invites      — invitaciones pendientes/aceptadas/rechazadas
*/
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id               INT AUTO_INCREMENT PRIMARY KEY,
            creator_id       INT NOT NULL,
            title            VARCHAR(120) NOT NULL,
            description      TEXT,
            event_date       DATETIME NOT NULL,
            duration_min     INT NOT NULL DEFAULT 60,
            min_participants INT NOT NULL DEFAULT 1,
            max_participants INT NOT NULL DEFAULT 0,
            visibility       ENUM('public','private') NOT NULL DEFAULT 'public',
            created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_date (event_date),
            INDEX idx_creator (creator_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_participants (
            event_id        INT NOT NULL,
            user_id         INT NOT NULL,
            status          ENUM('joined','waitlist') NOT NULL DEFAULT 'joined',
            recordatorio_id INT NULL,
            joined_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (event_id, user_id),
            INDEX idx_user (user_id),
            INDEX idx_event_status (event_id, status, joined_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_invites (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            event_id     INT NOT NULL,
            inviter_id   INT NOT NULL,
            invitee_id   INT NOT NULL,
            status       ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
            created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notified_at  TIMESTAMP NULL,
            UNIQUE KEY uq_event_invitee (event_id, invitee_id),
            INDEX idx_invitee_status (invitee_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Throwable $e) { /* silencio */ }

/* ── Helper: inserta un recordatorio en el calendario del usuario
      para un evento al que se une (joined). Devuelve el id del
      recordatorio o null si falla. ───────────────────────────────── */
function ev_addCalendarReminder(PDO $pdo, int $uid, array $event): ?int {
    $title = '📅 Evento: ' . mb_substr($event['title'], 0, 100);
    $desc  = (string)($event['description'] ?? '');
    $date  = (string)$event['event_date'];
    /* La tabla recordatorios tiene pareja_id NOT NULL DEFAULT 0 → 0 = sin pareja. */
    try {
        $stmt = $pdo->prepare("INSERT INTO recordatorios (usuario_id, pareja_id, titulo, fecha, descripcion, periodicidad)
                               VALUES (?, 0, ?, ?, ?, 'ninguna')");
        $stmt->execute([$uid, $title, $date, $desc]);
        return (int)$pdo->lastInsertId();
    } catch (Throwable $e) { return null; }
}
function ev_removeCalendarReminder(PDO $pdo, ?int $rid, int $uid): void {
    if (!$rid) return;
    try {
        $stmt = $pdo->prepare("DELETE FROM recordatorios WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$rid, $uid]);
    } catch (Throwable $e) { /* silencio */ }
}

/* Promociona al primero de la waitlist a joined cuando alguien sale.
   Si lo promueve, también le crea su recordatorio en el calendario. */
function ev_promoteFromWaitlist(PDO $pdo, int $eventId): void {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) return;
    $cap = (int)$event['max_participants'];
    if ($cap <= 0) return; /* sin cupo no hay waitlist */

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_participants WHERE event_id = ? AND status = 'joined'");
    $stmt->execute([$eventId]);
    $joined = (int)$stmt->fetchColumn();
    if ($joined >= $cap) return;

    $stmt = $pdo->prepare("SELECT user_id FROM event_participants
                           WHERE event_id = ? AND status = 'waitlist'
                           ORDER BY joined_at ASC LIMIT 1");
    $stmt->execute([$eventId]);
    $nextUid = $stmt->fetchColumn();
    if (!$nextUid) return;
    $nextUid = (int)$nextUid;

    /* Promover + insertar recordatorio. */
    $rid = ev_addCalendarReminder($pdo, $nextUid, $event);
    $stmt = $pdo->prepare("UPDATE event_participants
                           SET status = 'joined', recordatorio_id = ?
                           WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$rid, $eventId, $nextUid]);
}

/* Comprueba si el usuario PUEDE ver un evento concreto. */
function ev_canSee(PDO $pdo, int $uid, array $event): bool {
    if ($event['visibility'] === 'public') return true;
    if ((int)$event['creator_id'] === $uid) return true;
    /* Privado: ver si es invitado o ya unido. */
    $stmt = $pdo->prepare("SELECT 1 FROM event_participants WHERE event_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$event['id'], $uid]);
    if ($stmt->fetchColumn()) return true;
    $stmt = $pdo->prepare("SELECT 1 FROM event_invites WHERE event_id = ? AND invitee_id = ? LIMIT 1");
    $stmt->execute([$event['id'], $uid]);
    return (bool)$stmt->fetchColumn();
}

/* Hidrata un evento con counts y datos del usuario actual:
     myStatus    — 'joined' | 'waitlist' | 'invited' | null
     myInviteId  — int|null (si invitado pendiente)
     joinedCount — int
     waitlistCount — int
     creatorKey  — user_key del creador
     participants — array<{key,label,status}> de joined (limitado por privacidad) */
function ev_hydrate(PDO $pdo, int $uid, array $event): array {
    $eid = (int)$event['id'];
    /* Counts. */
    $stmt = $pdo->prepare("SELECT status, COUNT(*) AS c FROM event_participants WHERE event_id = ? GROUP BY status");
    $stmt->execute([$eid]);
    $counts = ['joined' => 0, 'waitlist' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $counts[$row['status']] = (int)$row['c'];
    }
    /* Mi status. */
    $stmt = $pdo->prepare("SELECT status FROM event_participants WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eid, $uid]);
    $myStatus = $stmt->fetchColumn() ?: null;
    $myInviteId = null;
    if (!$myStatus) {
        $stmt = $pdo->prepare("SELECT id FROM event_invites WHERE event_id = ? AND invitee_id = ? AND status = 'pending'");
        $stmt->execute([$eid, $uid]);
        $iid = $stmt->fetchColumn();
        if ($iid) { $myStatus = 'invited'; $myInviteId = (int)$iid; }
    }
    /* Lista participants joined (todos pueden verla si participan o si es público). */
    $stmt = $pdo->prepare("
        SELECT u.user_key AS `key`, u.label AS label, ep.status
          FROM event_participants ep
          JOIN usuarios u ON u.id = ep.user_id
         WHERE ep.event_id = ?
         ORDER BY ep.status DESC, ep.joined_at ASC
    ");
    $stmt->execute([$eid]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    /* Creator. */
    $creatorKey = ev_userKey($pdo, (int)$event['creator_id']) ?? '';
    /* Calcular si está finalizado. */
    $endTs = strtotime($event['event_date']) + ((int)$event['duration_min']) * 60;
    $isFinished = $endTs < time();
    return [
        'id'              => $eid,
        'title'           => (string)$event['title'],
        'description'     => (string)$event['description'],
        'eventDate'       => (string)$event['event_date'],
        'durationMin'     => (int)$event['duration_min'],
        'minParticipants' => (int)$event['min_participants'],
        'maxParticipants' => (int)$event['max_participants'],
        'visibility'      => (string)$event['visibility'],
        'creatorKey'      => $creatorKey,
        'isCreator'       => ((int)$event['creator_id'] === $uid),
        'isFinished'      => $isFinished,
        'joinedCount'     => $counts['joined'],
        'waitlistCount'   => $counts['waitlist'],
        'myStatus'        => $myStatus,
        'myInviteId'      => $myInviteId,
        'participants'    => $participants,
    ];
}

/* ────────────────────────────────────────────────────────────────── */

switch ($action) {

case 'list-events': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonResponse(['ok' => true, 'events' => []]);
    /* Eventos no finalizados (event_date + duration > NOW) que el
       usuario puede ver. Ordenados por fecha ascendente. */
    $stmt = $pdo->prepare("
        SELECT * FROM events
         WHERE DATE_ADD(event_date, INTERVAL duration_min MINUTE) > NOW()
           AND (
                visibility = 'public'
             OR creator_id = ?
             OR id IN (SELECT event_id FROM event_participants WHERE user_id = ?)
             OR id IN (SELECT event_id FROM event_invites WHERE invitee_id = ?)
           )
         ORDER BY event_date ASC
    ");
    $stmt->execute([$uid, $uid, $uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $out = [];
    foreach ($rows as $row) $out[] = ev_hydrate($pdo, $uid, $row);
    jsonResponse(['ok' => true, 'events' => $out]);
}

case 'get-event': {
    $uid = ev_uid($pdo, $userKey);
    $eid = (int)($_GET['id'] ?? 0);
    if (!$uid || !$eid) jsonError('Datos inválidos');
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eid]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) jsonError('Evento no encontrado', 404);
    if (!ev_canSee($pdo, $uid, $event)) jsonError('Sin permiso', 403);
    jsonResponse(['ok' => true, 'event' => ev_hydrate($pdo, $uid, $event)]);
}

case 'create-event': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $title = trim((string)($b['title'] ?? ''));
    if ($title === '' || mb_strlen($title) > 120) jsonError('Título inválido');
    $desc        = trim((string)($b['description'] ?? ''));
    $eventDate   = trim((string)($b['eventDate'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}(?::\d{2})?)?$/', $eventDate)) jsonError('Fecha inválida');
    if (strlen($eventDate) === 10) $eventDate .= ' 00:00:00';
    elseif (strlen($eventDate) === 16) $eventDate .= ':00';
    /* No permitir crear en el pasado. */
    if (strtotime($eventDate) < time() - 60) jsonError('La fecha del evento no puede estar en el pasado');
    $durationMin = max(15, min(60 * 24 * 7, (int)($b['durationMin'] ?? 60)));
    $minP        = max(1, min(10000, (int)($b['minParticipants'] ?? 1)));
    $maxP        = max(0, min(10000, (int)($b['maxParticipants'] ?? 0)));
    if ($maxP > 0 && $maxP < $minP) jsonError('Máximo no puede ser menor que mínimo');
    $visibility = (($b['visibility'] ?? '') === 'private') ? 'private' : 'public';
    $invitees   = is_array($b['invitees'] ?? null) ? $b['invitees'] : [];

    $stmt = $pdo->prepare("INSERT INTO events
        (creator_id, title, description, event_date, duration_min, min_participants, max_participants, visibility)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$uid, $title, $desc, $eventDate, $durationMin, $minP, $maxP, $visibility]);
    $eventId = (int)$pdo->lastInsertId();

    /* El creador se une automáticamente como 'joined'. */
    $event = [
        'id'           => $eventId,
        'title'        => $title,
        'description'  => $desc,
        'event_date'   => $eventDate,
        'duration_min' => $durationMin,
    ];
    $rid = ev_addCalendarReminder($pdo, $uid, $event);
    $pdo->prepare("INSERT INTO event_participants (event_id, user_id, status, recordatorio_id)
                   VALUES (?, ?, 'joined', ?)")
        ->execute([$eventId, $uid, $rid]);

    /* Invitaciones (solo a amigos mutuos para evitar spam). */
    if ($invitees) {
        $mutuals = array_flip(mutualFollowerIds($pdo, $uid));
        $insertInvite = $pdo->prepare("INSERT IGNORE INTO event_invites
                                       (event_id, inviter_id, invitee_id, status)
                                       VALUES (?, ?, ?, 'pending')");
        foreach ($invitees as $key) {
            $iid = ev_uid($pdo, (string)$key);
            if (!$iid || $iid === $uid) continue;
            if (!isset($mutuals[$iid])) continue;
            $insertInvite->execute([$eventId, $uid, $iid]);
        }
    }

    jsonResponse(['ok' => true, 'eventId' => $eventId]);
}

case 'join-event': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $eid = (int)($b['eventId'] ?? 0);
    if (!$eid) jsonError('eventId requerido');

    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eid]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) jsonError('Evento no encontrado', 404);

    /* Privado: solo invitados o ya dentro pueden unirse. */
    if (!ev_canSee($pdo, $uid, $event)) jsonError('Sin permiso', 403);

    /* No unirse si ya finalizó. */
    $endTs = strtotime($event['event_date']) + ((int)$event['duration_min']) * 60;
    if ($endTs < time()) jsonError('El evento ya finalizó');

    /* ¿Ya estás dentro? Idempotente. */
    $stmt = $pdo->prepare("SELECT status FROM event_participants WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eid, $uid]);
    $existing = $stmt->fetchColumn();
    if ($existing) jsonResponse(['ok' => true, 'status' => $existing]);

    /* ¿Hay sitio o waitlist? */
    $cap = (int)$event['max_participants'];
    $status = 'joined';
    if ($cap > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_participants WHERE event_id = ? AND status = 'joined'");
        $stmt->execute([$eid]);
        $joined = (int)$stmt->fetchColumn();
        if ($joined >= $cap) $status = 'waitlist';
    }

    $rid = ($status === 'joined') ? ev_addCalendarReminder($pdo, $uid, $event) : null;
    $stmt = $pdo->prepare("INSERT INTO event_participants (event_id, user_id, status, recordatorio_id)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$eid, $uid, $status, $rid]);

    /* Si tenía invite pendiente, marcarla como accepted. */
    $pdo->prepare("UPDATE event_invites SET status = 'accepted'
                   WHERE event_id = ? AND invitee_id = ? AND status = 'pending'")
        ->execute([$eid, $uid]);

    jsonResponse(['ok' => true, 'status' => $status]);
}

case 'leave-event': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $eid = (int)($b['eventId'] ?? 0);
    if (!$eid) jsonError('eventId requerido');

    /* Obtener su recordatorio si existe y borrar la participación. */
    $stmt = $pdo->prepare("SELECT recordatorio_id, status FROM event_participants
                           WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eid, $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonResponse(['ok' => true]); /* idempotente */

    ev_removeCalendarReminder($pdo, $row['recordatorio_id'] ? (int)$row['recordatorio_id'] : null, $uid);
    $pdo->prepare("DELETE FROM event_participants WHERE event_id = ? AND user_id = ?")
        ->execute([$eid, $uid]);

    /* Si era joined y libera plaza, promover al primero de waitlist. */
    if ($row['status'] === 'joined') ev_promoteFromWaitlist($pdo, $eid);

    jsonResponse(['ok' => true]);
}

case 'invite-to-event': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $eid = (int)($b['eventId'] ?? 0);
    $invKey = (string)($b['userKey'] ?? '');
    if (!$eid || !$invKey) jsonError('Datos inválidos');

    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eid]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) jsonError('Evento no encontrado', 404);

    /* Permiso para invitar: creador SIEMPRE. Si público, cualquier
       participante puede invitar. Si privado, solo el creador. */
    $isCreator = ((int)$event['creator_id'] === $uid);
    if (!$isCreator) {
        if ($event['visibility'] === 'private') jsonError('Solo el creador puede invitar a eventos privados', 403);
        $stmt = $pdo->prepare("SELECT 1 FROM event_participants WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eid, $uid]);
        if (!$stmt->fetchColumn()) jsonError('Debes estar unido al evento para invitar', 403);
    }

    $invUid = ev_uid($pdo, $invKey);
    if (!$invUid || $invUid === $uid) jsonError('Usuario inválido');
    /* Solo amigos mutuos del invitador. */
    $mutuals = array_flip(mutualFollowerIds($pdo, $uid));
    if (!isset($mutuals[$invUid])) jsonError('Solo puedes invitar a amigos mutuos', 403);

    /* No invitar a quien ya esté unido. */
    $stmt = $pdo->prepare("SELECT 1 FROM event_participants WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eid, $invUid]);
    if ($stmt->fetchColumn()) jsonResponse(['ok' => true, 'already' => true]);

    /* INSERT IGNORE re-crea invite si la había cancelada. */
    $stmt = $pdo->prepare("INSERT INTO event_invites (event_id, inviter_id, invitee_id, status)
                           VALUES (?, ?, ?, 'pending')
                           ON DUPLICATE KEY UPDATE status = 'pending', inviter_id = VALUES(inviter_id), notified_at = NULL");
    $stmt->execute([$eid, $uid, $invUid]);

    jsonResponse(['ok' => true]);
}

case 'respond-event-invite': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $iid = (int)($b['inviteId'] ?? 0);
    $act = (string)($b['action'] ?? '');
    if (!$iid || !in_array($act, ['accept','decline'], true)) jsonError('Datos inválidos');

    $stmt = $pdo->prepare("SELECT * FROM event_invites WHERE id = ? AND invitee_id = ?");
    $stmt->execute([$iid, $uid]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inv || $inv['status'] !== 'pending') jsonError('Invitación no válida', 404);

    if ($act === 'decline') {
        $pdo->prepare("UPDATE event_invites SET status = 'declined' WHERE id = ?")->execute([$iid]);
        jsonResponse(['ok' => true, 'status' => 'declined']);
    }

    /* Accept → reusar lógica de join. */
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([(int)$inv['event_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) jsonError('Evento no encontrado', 404);
    $endTs = strtotime($event['event_date']) + ((int)$event['duration_min']) * 60;
    if ($endTs < time()) jsonError('El evento ya finalizó');

    /* Si ya estaba dentro de algún modo: idempotente. */
    $stmt = $pdo->prepare("SELECT status FROM event_participants WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event['id'], $uid]);
    $existing = $stmt->fetchColumn();
    if (!$existing) {
        $cap = (int)$event['max_participants'];
        $status = 'joined';
        if ($cap > 0) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_participants WHERE event_id = ? AND status = 'joined'");
            $stmt->execute([$event['id']]);
            $joined = (int)$stmt->fetchColumn();
            if ($joined >= $cap) $status = 'waitlist';
        }
        $rid = ($status === 'joined') ? ev_addCalendarReminder($pdo, $uid, $event) : null;
        $pdo->prepare("INSERT INTO event_participants (event_id, user_id, status, recordatorio_id)
                       VALUES (?, ?, ?, ?)")
            ->execute([$event['id'], $uid, $status, $rid]);
        $existing = $status;
    }
    $pdo->prepare("UPDATE event_invites SET status = 'accepted' WHERE id = ?")->execute([$iid]);
    jsonResponse(['ok' => true, 'status' => $existing]);
}

case 'get-pending-invites': {
    /* Devuelve invites pending del usuario actual con datos para
       mostrar el haro. NO marca como notified — el cliente debe
       llamar a mark-invites-notified tras renderizar. */
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonResponse(['ok' => true, 'invites' => []]);
    $stmt = $pdo->prepare("
        SELECT ei.id, ei.event_id, ei.notified_at IS NOT NULL AS notified,
               UNIX_TIMESTAMP(ei.created_at) AS sentAt,
               e.title AS eventTitle, e.event_date AS eventDate,
               u.user_key AS inviterKey, u.label AS inviterLabel
          FROM event_invites ei
          JOIN events e   ON e.id = ei.event_id
          JOIN usuarios u ON u.id = ei.inviter_id
         WHERE ei.invitee_id = ? AND ei.status = 'pending'
         ORDER BY ei.created_at DESC
    ");
    $stmt->execute([$uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    jsonResponse(['ok' => true, 'invites' => $rows]);
}

case 'mark-invites-notified': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $ids = is_array($b['inviteIds'] ?? null) ? $b['inviteIds'] : [];
    $ids = array_values(array_filter(array_map('intval', $ids), function($v){ return $v > 0; }));
    if (!$ids) jsonResponse(['ok' => true]);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("UPDATE event_invites SET notified_at = NOW()
                           WHERE invitee_id = ? AND id IN ($placeholders)");
    $stmt->execute(array_merge([$uid], $ids));
    jsonResponse(['ok' => true]);
}

case 'delete-event': {
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    $eid = (int)($b['eventId'] ?? 0);
    if (!$eid) jsonError('eventId requerido');
    $stmt = $pdo->prepare("SELECT creator_id FROM events WHERE id = ?");
    $stmt->execute([$eid]);
    $creatorId = (int)($stmt->fetchColumn() ?: 0);
    if (!$creatorId) jsonError('Evento no encontrado', 404);
    if ($creatorId !== $uid) jsonError('Solo el creador puede eliminar el evento', 403);

    /* Borrar los recordatorios del calendario de todos los participantes. */
    $stmt = $pdo->prepare("SELECT user_id, recordatorio_id FROM event_participants WHERE event_id = ?");
    $stmt->execute([$eid]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        ev_removeCalendarReminder($pdo, $p['recordatorio_id'] ? (int)$p['recordatorio_id'] : null, (int)$p['user_id']);
    }
    $pdo->prepare("DELETE FROM event_participants WHERE event_id = ?")->execute([$eid]);
    $pdo->prepare("DELETE FROM event_invites WHERE event_id = ?")->execute([$eid]);
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$eid]);
    jsonResponse(['ok' => true]);
}

case 'list-mutual-friends': {
    /* Lista amigos mutuos para el form de invitación. */
    $uid = ev_uid($pdo, $userKey);
    if (!$uid) jsonResponse(['ok' => true, 'friends' => []]);
    $ids = mutualFollowerIds($pdo, $uid);
    if (!$ids) jsonResponse(['ok' => true, 'friends' => []]);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT user_key AS `key`, label AS label FROM usuarios WHERE id IN ($placeholders) ORDER BY label");
    $stmt->execute($ids);
    jsonResponse(['ok' => true, 'friends' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []]);
}

default:
    jsonError('Acción desconocida: ' . $action, 400);
}
