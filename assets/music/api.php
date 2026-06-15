<?php
/* ──────────────────────────────────────────────────────────
   MUSIC API — router único de la app Música (SQL)
   ──────────────────────────────────────────────────────────
   Acciones (vía ?action= o $_POST['action']):

   GET   ?action=get-playlists
   GET   ?action=get-users
   GET   ?action=yt-title&id=VIDEOID
   GET   ?action=yt-duration&id=VIDEOID
   GET   ?action=spotify-track&url=...
   GET   ?action=find-album&title=...&artist=...
   GET   ?action=album-tracks&id=SPOTIFY_ALBUM_ID
   GET   ?action=tidal-track&url=...

   POST  ?action=tidal-playlist        { url }
   POST  ?action=save-playlist-item   { id, name, tracks[], sharedFrom? }
   POST  ?action=delete-playlist      { id }
   POST  ?action=leave-playlist       { id, sharedFrom }
   POST  ?action=invite-collaborator  { playlistId, toUser }
   POST  ?action=remove-collaborator  { playlistId, collaborator }
   POST  ?action=respond-invite       { inviteId, action: 'accept'|'reject'|'dismiss' }
   POST  ?action=add-track            { videoId, title, artist? }
   POST  ?action=yt-search-batch      { tracks[] }
   POST  ?action=import-playlist      { url }
   POST  ?action=save-now-playing     { videoId, title, artist, plName, position, duration, isPlaying }
   GET   ?action=get-now-playing

   ALMACENAMIENTO: 100% SQL.
     - playlists / playlist_tracks / playlist_collaborators
     - playlist_invites (incluye notifs collab-accepted/rejected/left/removed
       además de los invite de colaboración)
     - music_extras (tracks añadidos por add-track al pool global del usuario)

   El SSE notifications-stream.php se mantiene aparte; lee directamente de
   playlist_invites.
   ────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';

/* Para el polling de tv.php — fuerza socket nuevo en cada petición.
   Las TVs viejas (Tizen <2018, webOS 3) reusan sockets keep-alive
   cerrados por Apache (KeepAliveTimeout=5s) y todos los polls tras el
   primero fallan con status=0. Aplicar antes que requireAuth para que
   el header esté en cualquier respuesta del endpoint, incluyendo 401. */
if (($_GET['action'] ?? '') === 'get-now-playing') {
    header('Connection: close');
}

/* Bypass de requireAuth para el modo iframe de get-now-playing.
   requireAuth devuelve JSON 401 con Content-Type application/json. El
   iframe del polling de tv.php necesita HTML siempre (si no, dispara
   un error cross-origin al intentar leer contentDocument). Aquí
   devolvemos un HTML con {ok:false,error:"auth"} cuando no hay sesión. */
if (($_GET['action'] ?? '') === 'get-now-playing' && isset($_GET['frame'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $sessUser = $_SESSION['user'] ?? null;
    if (!$sessUser || !isset($loginUsers[$sessUser])) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><body><pre id="d">'
           . htmlspecialchars('{"ok":false,"error":"auth"}', ENT_QUOTES, 'UTF-8')
           . '</pre></body></html>';
        exit;
    }
}

$u       = requireAuth();
$userKey = $u['key'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

/* user_key (string) → usuarios.id (int). Cachea por petición. */
function uidByKey(PDO $pdo, string $userKey): ?int {
    static $cache = [];
    if (array_key_exists($userKey, $cache)) return $cache[$userKey];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $cache[$userKey] = $id ? (int)$id : null;
}

/* ¿El playlist id parece BIGINT existente (en SQL) o es un id cliente
   "pl_xxx" generado en el navegador para una playlist nueva? */
function isNumericId($v): bool {
    return is_int($v) || (is_string($v) && ctype_digit($v));
}

/* Carga las playlists del usuario con sus tracks + colaboradores en una
   estructura compatible con el formato JSON antiguo. */
function loadPlaylistsForUser(PDO $pdo, int $uid, string $userKey): array {
    /* Propias */
    $stmt = $pdo->prepare("SELECT p.id, p.name, ? AS owner
                           FROM playlists p
                           WHERE p.owner_id = ?
                           ORDER BY p.id ASC");
    $stmt->execute([$userKey, $uid]);
    $ownRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Compartidas (collab) */
    $stmt = $pdo->prepare("SELECT p.id, p.name, ou.user_key AS owner, ou.label AS sharedLabel
                           FROM playlist_collaborators c
                           JOIN playlists p ON c.playlist_id = p.id
                           JOIN usuarios ou ON p.owner_id    = ou.id
                           WHERE c.user_id = ?
                           ORDER BY p.id ASC");
    $stmt->execute([$uid]);
    $sharedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($ownRows) && empty($sharedRows)) return [];

    /* Cargar todos los tracks de las playlists implicadas en un único query */
    $allIds = array_merge(
        array_column($ownRows, 'id'),
        array_column($sharedRows, 'id')
    );
    $tracksByPl = [];
    if (!empty($allIds)) {
        $place = implode(',', array_fill(0, count($allIds), '?'));
        $stmt = $pdo->prepare("SELECT playlist_id, video_id AS videoId, title, artist, duration, added_by AS addedBy
                               FROM playlist_tracks
                               WHERE playlist_id IN ($place)
                               ORDER BY playlist_id ASC, position ASC");
        $stmt->execute($allIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tr) {
            $pid = (int)$tr['playlist_id'];
            unset($tr['playlist_id']);
            $tr['duration'] = (int)$tr['duration'];
            $tracksByPl[$pid][] = $tr;
        }
    }

    /* Colaboradores por playlist (solo para las propias; las shared el
       cliente las trata como "no editables masivamente" por sharedFrom). */
    $collabsByPl = [];
    if (!empty($ownRows)) {
        $place = implode(',', array_fill(0, count($ownRows), '?'));
        $params = array_column($ownRows, 'id');
        $stmt = $pdo->prepare("SELECT c.playlist_id, u.user_key
                               FROM playlist_collaborators c
                               JOIN usuarios u ON c.user_id = u.id
                               WHERE c.playlist_id IN ($place)");
        $stmt->execute($params);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $collabsByPl[(int)$r['playlist_id']][] = $r['user_key'];
        }
    }

    $result = [];
    foreach ($ownRows as $r) {
        $pid = (int)$r['id'];
        $result[] = [
            'id'            => $pid,
            'name'          => $r['name'],
            'owner'         => $r['owner'],
            'collaborators' => $collabsByPl[$pid] ?? [],
            'tracks'        => $tracksByPl[$pid]  ?? [],
        ];
    }
    foreach ($sharedRows as $r) {
        $pid = (int)$r['id'];
        $result[] = [
            'id'           => $pid,
            'name'         => $r['name'],
            'owner'        => $r['owner'],
            'sharedFrom'   => $r['owner'],
            'sharedLabel'  => $r['sharedLabel'],
            'tracks'       => $tracksByPl[$pid] ?? [],
        ];
    }
    return $result;
}

/* Sustituye TODOS los tracks de una playlist. Asume autorización ya hecha. */
function replacePlaylistTracks(PDO $pdo, int $playlistId, array $tracks): void {
    $pdo->prepare("DELETE FROM playlist_tracks WHERE playlist_id = ?")->execute([$playlistId]);
    if (empty($tracks)) return;
    $stmt = $pdo->prepare("INSERT INTO playlist_tracks
        (playlist_id, position, video_id, title, artist, duration, added_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($tracks as $pos => $t) {
        $stmt->execute([
            $playlistId, $pos,
            $t['videoId'], $t['title'], $t['artist'], $t['duration'], $t['addedBy'],
        ]);
    }
}

/* Sanitiza el array de tracks que llega del cliente. */
function sanitizeTracks($raw): array {
    $tracks = [];
    if (!is_array($raw)) return $tracks;
    foreach ($raw as $t) {
        $vid = preg_replace('/[^A-Za-z0-9_-]/', '', $t['videoId'] ?? '');
        if (strlen($vid) !== 11) continue;
        $tracks[] = [
            'videoId'  => $vid,
            'title'    => substr(strip_tags($t['title']   ?? ''), 0, 200),
            'artist'   => substr(strip_tags($t['artist']  ?? ''), 0, 200),
            'duration' => max(0, intval($t['duration']    ?? 0)),
            'addedBy'  => substr(strip_tags($t['addedBy'] ?? ''), 0, 100),
        ];
    }
    return $tracks;
}

/* Inserta una notificación tipo collab-* (status). Si el playlist ya no
   existe la inserción falla por FK; lo ignoramos sin romper la respuesta. */
function pushCollabNotif(PDO $pdo, int $toUid, int $fromUid, int $playlistId, string $type): void {
    try {
        $pdo->prepare("INSERT INTO playlist_invites (to_user_id, from_user_id, playlist_id, type)
                       VALUES (?, ?, ?, ?)")
            ->execute([$toUid, $fromUid, $playlistId, $type]);
    } catch (PDOException $e) { /* FK perdida → playlist borrada; sin notif */ }
}

switch ($action) {

/* ─── Playlists ──────────────────────────────────────────── */

case 'get-playlists': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonResponse([]);
    jsonResponse(loadPlaylistsForUser($pdo, $uid, $userKey));
}

case 'save-playlist-item': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    if (!isset($b['id'], $b['name'])) jsonError('Datos incompletos');

    $name       = substr(strip_tags($b['name']), 0, 100);
    $sharedFrom = isset($b['sharedFrom']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $b['sharedFrom']) : null;
    $tracks     = sanitizeTracks($b['tracks'] ?? []);

    /* Caso A: el cliente edita una playlist compartida (colaborador). */
    if ($sharedFrom) {
        if (!isNumericId($b['id'])) jsonError('ID inválido para playlist compartida');
        $playlistId = (int)$b['id'];
        $ownerUid   = uidByKey($pdo, $sharedFrom);
        if (!$ownerUid) jsonError('Propietario inválido');
        /* Verificar que el playlist pertenece al owner y yo soy collab. */
        $stmt = $pdo->prepare("SELECT 1 FROM playlists p
                               JOIN playlist_collaborators c ON c.playlist_id = p.id
                               WHERE p.id = ? AND p.owner_id = ? AND c.user_id = ?");
        $stmt->execute([$playlistId, $ownerUid, $uid]);
        if (!$stmt->fetch()) jsonError('Sin permiso para editar', 403);
        $pdo->beginTransaction();
        try { replacePlaylistTracks($pdo, $playlistId, $tracks); $pdo->commit(); }
        catch (Throwable $e) { $pdo->rollBack(); throw $e; }
        jsonResponse(['ok' => true, 'id' => $playlistId]);
    }

    /* Caso B: playlist propia, ID numérico existente → UPDATE. */
    if (isNumericId($b['id'])) {
        $playlistId = (int)$b['id'];
        $stmt = $pdo->prepare("SELECT 1 FROM playlists WHERE id = ? AND owner_id = ?");
        $stmt->execute([$playlistId, $uid]);
        if ($stmt->fetch()) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE playlists SET name = ? WHERE id = ?")
                    ->execute([$name, $playlistId]);
                replacePlaylistTracks($pdo, $playlistId, $tracks);
                $pdo->commit();
            } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
            jsonResponse(['ok' => true, 'id' => $playlistId]);
        }
        /* Si el cliente mandó un numérico pero no existe / no es suyo →
           tratamos como creación nueva: caemos al caso C. */
    }

    /* Caso C: playlist nueva (id "pl_xxx" del cliente o no encontrada). */
    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO playlists (owner_id, name) VALUES (?, ?)")
            ->execute([$uid, $name]);
        $playlistId = (int)$pdo->lastInsertId();
        replacePlaylistTracks($pdo, $playlistId, $tracks);
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    jsonResponse(['ok' => true, 'id' => $playlistId]);
}

case 'delete-playlist': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b   = jsonBody();
    if (!isNumericId($b['id'] ?? null)) jsonError('ID inválido');
    $playlistId = (int)$b['id'];
    /* CASCADE borra tracks, collaborators e invites asociados. */
    $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND owner_id = ?");
    $stmt->execute([$playlistId, $uid]);
    if (!$stmt->rowCount()) jsonError('Playlist no encontrada o no autorizada', 404);
    jsonResponse(['ok' => true]);
}

case 'leave-playlist': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b          = jsonBody();
    if (!isNumericId($b['id'] ?? null)) jsonError('Datos incompletos');
    $sharedFrom = preg_replace('/[^A-Za-z0-9_-]/', '', $b['sharedFrom'] ?? '');
    if (!$sharedFrom) jsonError('Datos incompletos');
    $ownerUid = uidByKey($pdo, $sharedFrom);
    if (!$ownerUid) jsonError('Propietario inválido');
    $playlistId = (int)$b['id'];

    /* Verificar la relación de colaboración antes de borrar (para que el
       owner sólo reciba notif si realmente había salido un colaborador). */
    $stmt = $pdo->prepare("SELECT p.name FROM playlists p
                           JOIN playlist_collaborators c ON c.playlist_id = p.id
                           WHERE p.id = ? AND p.owner_id = ? AND c.user_id = ?");
    $stmt->execute([$playlistId, $ownerUid, $uid]);
    $playlistName = $stmt->fetchColumn();
    if ($playlistName === false) jsonError('No eres colaborador', 404);

    $pdo->prepare("DELETE FROM playlist_collaborators WHERE playlist_id = ? AND user_id = ?")
        ->execute([$playlistId, $uid]);
    pushCollabNotif($pdo, $ownerUid, $uid, $playlistId, 'collab-left');
    jsonResponse(['ok' => true]);
}

/* ─── Colaboraciones ─────────────────────────────────────── */

case 'invite-collaborator': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b          = jsonBody();
    if (!isNumericId($b['playlistId'] ?? null)) jsonError('Datos incompletos');
    $playlistId = (int)$b['playlistId'];
    $toUser     = preg_replace('/[^A-Za-z0-9_-]/', '', $b['toUser'] ?? '');
    if (!$toUser)             jsonError('Datos incompletos');
    if ($toUser === $userKey) jsonError('No puedes invitarte a ti mismo');
    $toUid = uidByKey($pdo, $toUser);
    if (!$toUid) jsonError('Usuario destino inválido');

    /* Restringir colaboraciones a seguidores mutuos. */
    require_once dirname(__DIR__) . '/social-helpers.php';
    if (!isMutualFollow($pdo, $uid, $toUid)) {
        jsonError('Solo puedes invitar a usuarios con seguimiento mutuo');
    }

    /* Validar que la playlist es del usuario actual */
    $stmt = $pdo->prepare("SELECT name FROM playlists WHERE id = ? AND owner_id = ?");
    $stmt->execute([$playlistId, $uid]);
    $plName = $stmt->fetchColumn();
    if ($plName === false) jsonError('Playlist no encontrada', 404);

    /* ¿Ya es colaborador? */
    $stmt = $pdo->prepare("SELECT 1 FROM playlist_collaborators WHERE playlist_id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $toUid]);
    if ($stmt->fetch()) {
        global $loginUsers;
        jsonError(($loginUsers[$toUser]['label'] ?? $toUser) . ' ya es colaborador');
    }

    /* ¿Ya hay invite pendiente del mismo from→to para esta playlist? */
    $stmt = $pdo->prepare("SELECT 1 FROM playlist_invites
                           WHERE to_user_id = ? AND from_user_id = ? AND playlist_id = ? AND type = 'invite'");
    $stmt->execute([$toUid, $uid, $playlistId]);
    if ($stmt->fetch()) jsonError('Ya existe una invitación pendiente');

    $pdo->prepare("INSERT INTO playlist_invites (to_user_id, from_user_id, playlist_id, type)
                   VALUES (?, ?, ?, 'invite')")
        ->execute([$toUid, $uid, $playlistId]);
    /* Push notification al destinatario. */
    require_once dirname(__DIR__) . '/push/send-push.php';
    global $loginUsers;
    $fromLabel = $loginUsers[$userKey]['label'] ?? $userKey;
    sendPushToUser($pdo, (int)$toUid, buildInvitePushPayload(
        'playlist',
        '📋 ' . $fromLabel . ' te invita',
        'Colaborar en "' . $plName . '"',
    ));
    jsonResponse(['ok' => true]);
}

case 'remove-collaborator': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b            = jsonBody();
    if (!isNumericId($b['playlistId'] ?? null)) jsonError('Datos incompletos');
    $playlistId   = (int)$b['playlistId'];
    $collaborator = preg_replace('/[^A-Za-z0-9_-]/', '', $b['collaborator'] ?? '');
    if (!$collaborator) jsonError('Datos incompletos');
    $collabUid = uidByKey($pdo, $collaborator);
    if (!$collabUid) jsonError('Usuario inválido');

    /* La playlist tiene que ser mía */
    $stmt = $pdo->prepare("SELECT name FROM playlists WHERE id = ? AND owner_id = ?");
    $stmt->execute([$playlistId, $uid]);
    if ($stmt->fetchColumn() === false) jsonError('Playlist no encontrada', 404);

    $pdo->prepare("DELETE FROM playlist_collaborators WHERE playlist_id = ? AND user_id = ?")
        ->execute([$playlistId, $collabUid]);
    pushCollabNotif($pdo, $collabUid, $uid, $playlistId, 'removed');
    jsonResponse(['ok' => true]);
}

case 'respond-invite': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b        = jsonBody();
    $inviteId = (int)($b['inviteId'] ?? 0);
    $act      = $b['action']   ?? '';
    if (!$inviteId || !in_array($act, ['accept','reject','dismiss'], true)) jsonError('Datos inválidos');

    /* La invitación tiene que ser para mí */
    $stmt = $pdo->prepare("SELECT from_user_id, playlist_id, type FROM playlist_invites
                           WHERE id = ? AND to_user_id = ?");
    $stmt->execute([$inviteId, $uid]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inv) jsonError('Invitación no encontrada', 404);

    /* Consumir (borrar) la fila pase lo que pase */
    $pdo->prepare("DELETE FROM playlist_invites WHERE id = ?")->execute([$inviteId]);

    /* Aceptar / rechazar sólo aplican al type 'invite' (colaboración). */
    if ($inv['type'] === 'invite' && $act === 'accept') {
        $pdo->prepare("INSERT IGNORE INTO playlist_collaborators (playlist_id, user_id) VALUES (?, ?)")
            ->execute([(int)$inv['playlist_id'], $uid]);
        pushCollabNotif($pdo, (int)$inv['from_user_id'], $uid, (int)$inv['playlist_id'], 'collab-accepted');
    } elseif ($inv['type'] === 'invite' && $act === 'reject') {
        pushCollabNotif($pdo, (int)$inv['from_user_id'], $uid, (int)$inv['playlist_id'], 'collab-rejected');
    }
    /* dismiss = solo cerrar la notif. No se notifica al otro. */

    jsonResponse(['ok' => true]);
}

/* ─── Usuarios ───────────────────────────────────────────── */

case 'get-users': {
    /* Solo devuelve usuarios con seguimiento mutuo respecto al actual.
       Usado por la UI de invitar colaboradores en playlists; si la lista
       sale filtrada, el usuario nunca ve invitables que iban a fallar. */
    require_once dirname(__DIR__) . '/social-helpers.php';
    global $loginUsers;
    $uid = uidByKey($pdo, $userKey);
    $mutualIds = $uid ? mutualFollowerIds($pdo, $uid) : [];
    /* Resolver user_keys de los IDs mutuos en una sola query. */
    $mutualKeys = [];
    if ($mutualIds) {
        $ph = implode(',', array_fill(0, count($mutualIds), '?'));
        $st = $pdo->prepare("SELECT user_key FROM usuarios WHERE id IN ($ph)");
        $st->execute($mutualIds);
        $mutualKeys = array_flip($st->fetchAll(PDO::FETCH_COLUMN));
    }
    $users = [];
    foreach ($loginUsers as $k => $u2) {
        if ($k === $userKey) continue;
        if (!isset($mutualKeys[$k])) continue;
        $users[] = ['key' => $k, 'label' => $u2['label']];
    }
    jsonResponse($users);
}

/* ─── Tracks extra (music_extras) ────────────────────────── */

case 'add-track': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    if (!isset($b['videoId'], $b['title'])) jsonError('Datos incompletos');
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $b['videoId']);
    if (strlen($videoId) !== 11) jsonError('ID de video inválido');
    $title  = substr(strip_tags($b['title']),  0, 200);
    $artist = substr(strip_tags($b['artist'] ?? ''), 0, 200);
    $pdo->prepare("INSERT INTO music_extras (user_id, video_id, title, artist) VALUES (?, ?, ?, ?)")
        ->execute([$uid, $videoId, $title, $artist]);
    jsonResponse(['ok' => true, 'track' => [
        'title' => $title, 'artist' => $artist, 'videoId' => $videoId,
    ]]);
}

/* ─── YouTube helpers ────────────────────────────────────── */

case 'yt-title': {
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id'] ?? '');
    if (strlen($videoId) !== 11) jsonError('ID inválido');
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $raw = @file_get_contents('https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $videoId . '&format=json', false, $ctx);
    if (!$raw) jsonError('No se pudo obtener el título', 502);
    $data = json_decode($raw, true);
    if (!isset($data['title'])) jsonError('Respuesta inválida', 502);
    jsonResponse(['title' => $data['title'], 'author' => $data['author_name'] ?? '']);
}

case 'yt-duration': {
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id'] ?? '');
    if (strlen($videoId) !== 11) jsonError('ID inválido');
    $ctx = stream_context_create(['http' => [
        'timeout' => 12, 'ignore_errors' => true,
        'header' => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
        ]),
    ]]);
    $html = @file_get_contents('https://www.youtube.com/watch?v=' . $videoId, false, $ctx);
    if (!$html) jsonError('No se pudo acceder', 502);
    $duration = 0;
    if (preg_match('/"lengthSeconds"\s*:\s*"(\d+)"/', $html, $m)) $duration = (int)$m[1];
    if (!$duration) jsonError('No se pudo obtener duración', 502);
    jsonResponse(['duration' => $duration]);
}

case 'yt-search-batch': {
    set_time_limit(600);
    $b = jsonBody();
    if (!isset($b['tracks']) || !is_array($b['tracks'])) jsonError('Datos inválidos');

    /* Preparo metadata por índice para preservar el mapeo cuando hagamos
       los fetches en paralelo. */
    $items = [];
    foreach ($b['tracks'] as $t) {
        $title  = substr(strip_tags($t['title']  ?? ''), 0, 200);
        $artist = substr(strip_tags($t['artist'] ?? ''), 0, 200);
        if (!$title) continue;
        $items[] = [
            'query'    => trim($title . ' ' . $artist . ' audio'),
            'title'    => $title,
            'artist'   => $artist,
            'duration' => max(0, intval($t['duration'] ?? 0)),
            'videoId'  => null,
        ];
    }
    if (empty($items)) jsonResponse(['tracks' => []]);

    /* ── Helper genérico de fetch paralelo con concurrencia limitada.
       Aceptamos gzip (ENCODING) para reducir tamaño de transferencia. */
    $multiFetch = function(array $urls, int $concurrent = 10) {
        $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ];
        $out  = [];
        $keys = array_keys($urls);
        foreach (array_chunk($keys, $concurrent, true) as $chunkKeys) {
            $mh = curl_multi_init();
            $handles = [];
            foreach ($chunkKeys as $k) {
                $ch = curl_init($urls[$k]);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_HTTPHEADER     => $headers,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_ENCODING       => '',   /* acepta gzip → menos bytes en el cable */
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$k] = $ch;
            }
            $active = null;
            do {
                $status = curl_multi_exec($mh, $active);
                if ($active) curl_multi_select($mh, 1.0);
            } while ($active && $status == CURLM_OK);
            foreach ($handles as $k => $ch) {
                $out[$k] = curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
            /* Pausa corta entre chunks → suaviza el rate hacia YouTube
               sin perder los beneficios del paralelismo. */
            if (count($chunkKeys) >= $concurrent) usleep(200000);
        }
        return $out;
    };

    require_once __DIR__ . '/spotify-helpers.php'; /* ytExtract*, ytPickBest* */

    /* ── Fase 1: TODAS las búsquedas en paralelo (chunks de 10).
       Para cada track extraemos los candidatos del HTML de results y
       elegimos el mejor por score combinado (título + artista + canal
       + duración + posición), no el primero. Las trap-words
       (karaoke/cover/instrumental/etc.) penalizan fuerte para evitar
       falsos positivos típicos en imports de CSV de Spotify. */
    $searchUrls = [];
    foreach ($items as $i => $it) {
        $searchUrls[$i] = 'https://www.youtube.com/results?search_query=' . urlencode($it['query']);
    }
    foreach ($multiFetch($searchUrls, 10) as $i => $html) {
        $best = ytPickBestCandidate(ytExtractCandidates($html, 7), $items[$i]);
        if (!$best) continue;
        $items[$i]['videoId'] = $best['videoId'];
        if (!empty($best['duration'])) {
            /* Si la duración vino en el HTML de results, evita el watch
               fetch de Fase 2. */
            $items[$i]['duration'] = $best['duration'];
        }
    }

    /* ── Fase 2: completar duración para los que aún no la tienen
       (la sacamos del HTML de results si pudimos; este watch fetch es
       el fallback). */
    $watchUrls = [];
    foreach ($items as $i => $it) {
        if ($it['videoId'] && !$it['duration']) {
            $watchUrls[$i] = 'https://www.youtube.com/watch?v=' . $it['videoId'];
        }
    }
    if ($watchUrls) {
        foreach ($multiFetch($watchUrls, 10) as $i => $html) {
            if ($html && preg_match('/"lengthSeconds"\s*:\s*"(\d+)"/', $html, $d)) {
                $items[$i]['duration'] = (int)$d[1];
            }
        }
    }

    /* Construye respuesta final solo con los que tienen videoId resuelto. */
    $results = [];
    foreach ($items as $it) {
        if (!$it['videoId']) continue;
        $results[] = [
            'videoId'  => $it['videoId'],
            'title'    => $it['title'],
            'artist'   => $it['artist'],
            'duration' => $it['duration'],
        ];
    }
    jsonResponse(['tracks' => $results]);
}

/* ─── Spotify ─────────────────────────────────────────────── */

case 'spotify-track': {
    require_once __DIR__ . '/spotify-helpers.php';
    $url = trim($_GET['url'] ?? '');
    if (!$url) jsonError('URL requerida');
    $trackId = null;
    if (preg_match('/spotify\.com\/(?:[a-z][a-z0-9-]*\/)?track\/([A-Za-z0-9]+)/', $url, $m)) $trackId = $m[1];
    elseif (preg_match('/spotify:track:([A-Za-z0-9]+)/', $url, $m))                        $trackId = $m[1];
    if (!$trackId) jsonError('URL de track de Spotify inválida');
    $token = getSpotifyToken();
    if (!$token) jsonError('No se pudo autenticar con Spotify', 502);
    $ctx = stream_context_create(['http' => [
        'timeout' => 10, 'ignore_errors' => true,
        'header'  => 'Authorization: Bearer ' . $token,
    ]]);
    $raw = @file_get_contents('https://api.spotify.com/v1/tracks/' . $trackId, false, $ctx);
    if (!$raw) jsonError('No se pudo obtener el track de Spotify', 502);
    $track = json_decode($raw, true);
    if (!isset($track['name'])) jsonError('Track no encontrado en Spotify', 404);
    $title    = $track['name'];
    $artist   = $track['artists'][0]['name'] ?? '';
    $duration = isset($track['duration_ms']) ? (int)round($track['duration_ms'] / 1000) : 0;
    /* Pasamos los metadatos al helper: dentro escoge el mejor match por
       título + canal + duración, no el primer videoId que devuelva
       YouTube (que solía ser un cover o lyrics video). */
    $videoId  = searchYouTubeVideoId(
        $title . ' ' . $artist . ' audio',
        ['title' => $title, 'artist' => $artist, 'duration' => $duration]
    );
    if (!$videoId) jsonError('No se encontró el vídeo en YouTube', 502);
    jsonResponse(['title' => $title, 'artist' => $artist, 'duration' => $duration, 'videoId' => $videoId]);
}

/* Busca el álbum al que pertenece una canción usando Spotify Search.
   Patrón: el cliente llama con title + artist (los datos que tiene del
   track de YouTube) y devolvemos lo que Spotify reporta como mejor match.

   Estrategia:
     1. Cache hit por md5(title:artist) → return.
     2. Spotify search type=track con "track:<title> artist:<artist>"
        (más preciso que query libre — Spotify ignora ruidos como
        "[Official Video]" o "feat. X").
     3. Si no hay match, búsqueda libre (sin filtros) como fallback.
     4. Si nada, devolvemos { notFound: true } y cacheamos también (7d)
        para no martillear el endpoint con queries que no resuelven.

   Singles: en Spotify a veces el "álbum" es la misma canción. Lo
   marcamos con isSingle=true para que el cliente decida si lo muestra
   ("Single" en vez del nombre del álbum). */
case 'find-album': {
    require_once __DIR__ . '/spotify-helpers.php';
    $title   = trim((string)($_GET['title']  ?? ''));
    $artist  = trim((string)($_GET['artist'] ?? ''));
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_GET['videoId'] ?? ''));
    if (strlen($videoId) !== 11) $videoId = '';
    if ($title === '') jsonError('title requerido');

    /* ── Cache key NORMALIZADA agresiva ──
       Antes era `md5(lower(title) . '|' . lower(artist))` — variantes
       triviales del mismo track ("Song (Official Audio)" vs "Song",
       "Song feat. X" vs "Song", "Song - Remastered 2011" vs "Song")
       hashaban distinto y disparaban requests duplicadas a Spotify
       para el MISMO resultado. Strip ese ruido aquí dispara más hits
       sin tocar el ranking del backend (que sigue usando el title
       original para scoring). */
    $normalizeForKey = function (string $s): string {
        $s = mb_strtolower($s);
        if (class_exists('Transliterator')) {
            $tr = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($tr) $s = $tr->transliterate($s);
        }
        /* Strip "(Official ...)", "[HD]", "(Audio)", "(Lyric Video)",
           "(Live)", "(Remastered YYYY)", "(Single Version)", etc. */
        $s = preg_replace('/[\[\(](?:official|music|video|audio|hd|hq|lyric[s]?(?:\s+video)?|mv|m\/v|live(?:\s+performance)?|visualizer|extended|remaster(?:ed)?(?:\s+\d+)?|single(?:\s+version)?|radio(?:\s+edit)?|explicit|clean|stereo|mono)[\]\)\s\-\d]*/i', ' ', $s);
        /* Strip "feat. X" / "ft. X" hasta el siguiente paréntesis o final. */
        $s = preg_replace('/\s*(?:feat\.?|ft\.?|featuring)\s+[^\(\[\-]+/i', ' ', $s);
        /* Strip puntuación + collapse whitespace. */
        $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    };

    /* ── Lookup por VIDEO ID primero (#5) ──
       Si el cliente nos da el videoId del track de YouTube, buscamos
       primero un hit cacheado para ese videoId. Esto sobrevive a
       cambios del título (re-uploads, ediciones del playlist, etc.)
       y comparte cache entre usuarios distintos. Si hit → return
       inmediato sin tocar Spotify. */
    if ($videoId !== '') {
        $vidKey = 'album_track_v1_' . $videoId;
        $cachedVid = cacheGet($vidKey);
        if ($cachedVid !== null) {
            $decoded = json_decode($cachedVid, true);
            if (is_array($decoded)
                && empty($decoded['notFound'])
                && !empty($decoded['spotifyAlbumId'])
                && strpos((string)$decoded['spotifyAlbumId'], 'synthetic:') !== 0) {
                jsonResponse($decoded);
            }
        }
    }

    /* v5 — bumpeamos para invalidar los notFound:true cacheados con la
       key v4 (que no normalizaba el título — entradas tipo "Song
       (Official Audio)" no se reusaban para "Song"). */
    $titleN  = $normalizeForKey($title);
    $artistN = $normalizeForKey($artist);
    $cacheKey = 'album_lookup_v5_' . md5($titleN . '|' . $artistN);
    $cached = cacheGet($cacheKey);
    if ($cached !== null) {
        $decoded = json_decode($cached, true);
        if (is_array($decoded)
            && empty($decoded['notFound'])
            && empty($decoded['isSynthetic'])
            && !empty($decoded['spotifyAlbumId'])
            && strpos((string)$decoded['spotifyAlbumId'], 'synthetic:') !== 0) {
            /* Solo aceptamos cache hit si tiene álbum REAL — un
               notFound o sintético legacy se reprocesa con la cascada. */
            /* Replica al cache por videoId para futuras consultas. */
            if ($videoId !== '') {
                cacheSet('album_track_v1_' . $videoId, $cached, 7 * 24 * 3600);
            }
            jsonResponse($decoded);
        }
    }

    /* ── Fallback al cache v4 ──
       El bump v4→v5 invalidó TODOS los hits previos. Si el cliente
       enviaba un track ya resuelto con v4, ahora cae aquí miss → fetch
       a Spotify. Cuando Spotify está rate-limited, eso devuelve 503 y
       no se ven los álbumes que SÍ se vieron antes. Para evitar la
       degradación: leemos el cache v4 (que sigue ahí 7 días), y si
       tiene un HIT REAL (no notFound, no synthetic) lo aceptamos y lo
       promovemos a v5 + por videoId. Los notFound de v4 NO se aceptan
       (la normalización de v5 puede convertirlos en hit). */
    $cacheKeyV4 = 'album_lookup_v4_' . md5(mb_strtolower($title) . '|' . mb_strtolower($artist));
    $cachedV4 = cacheGet($cacheKeyV4);
    if ($cachedV4 !== null) {
        $decodedV4 = json_decode($cachedV4, true);
        if (is_array($decodedV4)
            && empty($decodedV4['notFound'])
            && empty($decodedV4['isSynthetic'])
            && !empty($decodedV4['spotifyAlbumId'])
            && strpos((string)$decodedV4['spotifyAlbumId'], 'synthetic:') !== 0) {
            cacheSet($cacheKey, $cachedV4, 7 * 24 * 3600);
            if ($videoId !== '') {
                cacheSet('album_track_v1_' . $videoId, $cachedV4, 7 * 24 * 3600);
            }
            jsonResponse($decodedV4);
        }
    }

    /* ══════════════════════════════════════════════════════════════
       CASCADA DE FUENTES (iTunes → Deezer → Spotify)
       ──────────────────────────────────────────────────────────────
       Spotify queda como ÚLTIMO recurso para reducir al máximo nuestra
       presión sobre su API (causa de bans persistentes). iTunes y
       Deezer son endpoints públicos sin auth ni rate-limit estricto.

       Cada hit se cachea bajo el mismo cacheKey/videoId, así que la
       siguiente consulta se sirve del cache directamente — el orden de
       la cascada solo aplica al PRIMER lookup de cada track. */
    require_once __DIR__ . '/itunes-helpers.php';
    require_once __DIR__ . '/deezer-helpers.php';

    $persistAndReturn = function(array $result) use ($cacheKey, $videoId) {
        $payload = json_encode($result, JSON_UNESCAPED_UNICODE);
        cacheSet($cacheKey, $payload, 30 * 24 * 3600); /* 30 días */
        if ($videoId !== '') {
            cacheSet('album_track_v1_' . $videoId, $payload, 30 * 24 * 3600);
        }
        jsonResponse($result);
    };

    /* PRE-LIMPIEZA aplicada a iTunes/Deezer (no solo a Spotify como
       antes). Sin esto, títulos típicos de YouTube tipo "(Official
       Audio)", "[HD]", "feat. X", "- 2012 Remaster", caían fuera de
       los matches por similitud y la cascada terminaba en Spotify
       (que está banneado → 503).
       Artist con `;` (múltiples artistas separados) → tomamos solo
       el primero: las APIs buscan mejor con un único nombre y los
       restantes están casi siempre como "feat." dentro del título. */
    $cleanTitleCascade = preg_replace(
        '/[\[\(](?:official|music|video|audio|hd|hq|lyrics?|mv|m\/v|live|live performance|visualizer|extended|remaster(ed)?(\s+\d+)?)[\]\)\s\-]*/i',
        ' ', $title
    );
    $cleanTitleCascade = preg_replace('/\s*(?:feat\.?|ft\.?)\s+[^\(\[\-]+/i', ' ', (string)$cleanTitleCascade);
    /* Quita guiones flotantes que quedan tras quitar "- 2012 Remaster". */
    $cleanTitleCascade = preg_replace('/\s*-\s*$/', '', (string)$cleanTitleCascade);
    $cleanTitleCascade = trim(preg_replace('/\s{2,}/', ' ', (string)$cleanTitleCascade));
    if ($cleanTitleCascade === '') $cleanTitleCascade = $title;   /* fallback */
    $primaryArtist = trim((string)preg_replace('/[;,&].*$/', '', $artist));
    if ($primaryArtist === '') $primaryArtist = $artist;

    /* iTunes — cobertura altísima en repertorio occidental. */
    $itunes = itunesSearchAlbumForTrack($cleanTitleCascade, $primaryArtist);
    if ($itunes !== null) $persistAndReturn($itunes);

    /* Deezer — cubre los gaps de iTunes (electrónica, francés, etc.). */
    $deezer = deezerSearchAlbumForTrack($cleanTitleCascade, $primaryArtist);
    if ($deezer !== null) $persistAndReturn($deezer);

    /* ── Último recurso: Spotify ──
       Si llegamos aquí, ni iTunes ni Deezer encontraron el track. Solo
       casos raros (releases muy nuevos, k-pop poco conocido, etc.).
       GUARD GLOBAL DE RATE-LIMIT: si el guard está activo, devolvemos
       503 inmediato y dejamos que su ban (que cuenta por IP) expire.
       Cada request que hagamos durante el plazo RENOVARÍA el contador. */
    $rateLimitedUntil = (int)cacheGet('spotify_rate_limited_until');
    if ($rateLimitedUntil > time()) {
        jsonResponse([
            'error'      => 'Spotify rate-limited',
            'code'       => 'rateLimited',
            'retryAfter' => $rateLimitedUntil - time(),
        ], 503);
    }

    $token = getSpotifyToken();
    if (!$token) jsonError('No se pudo autenticar con Spotify', 502);

    /* Limpia ruido típico de títulos de YouTube que confunde la
       búsqueda de Spotify ("(Official Music Video)", "[HD]", "(Audio)",
       "feat. X", etc.). Conservativo: lo aplicamos en el query, NO
       cambiamos el title original (la cache key lo usa para dedupe). */
    $cleanTitle = preg_replace('/[\[\(](?:official|music|video|audio|hd|hq|lyrics?|mv|m\/v|live|live performance|visualizer|extended|remaster(ed)?(\s+\d+)?)[\]\)\s\-]*/i', ' ', $title);
    $cleanTitle = preg_replace('/\s*(?:feat\.?|ft\.?)\s+[^\(\[\-]+/i', ' ', $cleanTitle);
    $cleanTitle = trim(preg_replace('/\s{2,}/', ' ', $cleanTitle));

    /* Trae múltiples candidatos para poder rankear. Spotify ordena por
       su propia relevancia pero a veces el TOP-1 es un cover o remix
       que tiene nombre parecido pero NO es la canción del usuario; con
       limit=10 podemos descartar esos.

       Devuelve [items, rateLimited]. rateLimited=true cuando Spotify
       responde 429 — el caller usa esto para NO cachear notFound
       (sería una decisión basada en un fallo temporal). */
    $tryQuery = function(string $q) use ($token, $pdo) {
        if ($q === '') return [[], false];
        /* ── THROTTLE SERVER-SIDE ──
           Serializa entre TODOS los clientes simultáneos. Cada request
           espera a que pase MIN_INTERVAL_MS desde la anterior (de
           cualquier cliente). Implementado con MySQL GET_LOCK +
           timestamp en app_cache.
           Sin esto, N clientes concurrentes = N veces el rate del
           cliente individual → ban garantizado por mucho que la cola
           client-side espacie 300 ms. */
        $MIN_INTERVAL_MS = 600;  /* ~1.6 req/s sostenido global */
        $CAP_PER_DAY     = 8000; /* tope diario por si algo se desboca */
        $dayKey = 'spotify_calls_' . date('Y-m-d');
        $count = (int)cacheGet($dayKey);
        if ($count >= $CAP_PER_DAY) return [[], true]; /* tope diario → rate-limited soft */

        $gotLock = false;
        try {
            $st = $pdo->query("SELECT GET_LOCK('spotify_throttle', 8)");
            $gotLock = ((int)$st->fetchColumn()) === 1;
        } catch (Throwable $_) {}
        if ($gotLock) {
            try {
                $lastUs = (float)cacheGet('spotify_last_call_us');
                $nowUs  = microtime(true);
                $deltaMs = ($nowUs - $lastUs) * 1000;
                if ($deltaMs < $MIN_INTERVAL_MS) {
                    usleep((int)(($MIN_INTERVAL_MS - $deltaMs) * 1000));
                }
                cacheSet('spotify_last_call_us', (string)microtime(true), 120);
                cacheSet($dayKey, (string)($count + 1), 86400);
            } finally {
                try { $pdo->query("SELECT RELEASE_LOCK('spotify_throttle')"); } catch (Throwable $_) {}
            }
        }

        $url = 'https://api.spotify.com/v1/search?type=track&limit=10&market=US&q=' . rawurlencode($q);
        $ctx = stream_context_create(['http' => [
            'timeout' => 8, 'ignore_errors' => true,
            'header'  => 'Authorization: Bearer ' . $token,
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) return [[], false];
        /* Detección de 429: el body es "Too many requests" en texto plano
           o JSON {"error":{"status":429}}. Spotify a veces incluye el
           header Retry-After con segundos para esperar. */
        $isRateLimited = false;
        if (stripos((string)$raw, 'too many requests') !== false) {
            $isRateLimited = true;
        }
        $data = json_decode((string)$raw, true);
        if (is_array($data) && isset($data['error']['status']) && (int)$data['error']['status'] === 429) {
            $isRateLimited = true;
        }
        /* CUALQUIER 429 → guard global. NO reintentamos.
           Bug histórico: si Retry-After era 0 o <=10s, el código antes
           reintentaba en el sitio (doblando los requests fallidos) y NO
           armaba el guard cuando Spotify devolvía 429 "Too many requests"
           sin header (que es lo más común en bans agresivos). Eso
           mantenía a Spotify renovándonos el ban indefinidamente: cada
           find-album hacía 2-4 requests, todas 429, sin que el guard
           se armara → el siguiente find-album repetía → spam.
           Ahora: 429 detectado → mínimo 1h de guard (cap 24h con el
           Retry-After si viene), return inmediato sin reintentar. */
        if ($isRateLimited) {
            $retryAfter = 0;
            foreach ($http_response_header ?? [] as $h) {
                if (stripos($h, 'retry-after:') === 0) {
                    $retryAfter = max(0, (int)trim(substr($h, 12)));
                    break;
                }
            }
            /* Mínimo 1h de guard incluso sin Retry-After. Cap a 24h. */
            $duration = max(3600, min($retryAfter ?: 3600, 24 * 3600));
            $until    = time() + $duration;
            cacheSet('spotify_rate_limited_until', (string)$until, $duration);
            return [[], true];
        }
        return [$data['tracks']['items'] ?? [], false];
    };

    /* Normalización para comparar strings: lowercase, strip diacritics,
       collapse whitespace y elimina puntuación común. Tolerante a
       variaciones tipo "feat.", apóstrofes curvos, etc. */
    $norm = function(string $s): string {
        $s = mb_strtolower($s);
        if (class_exists('Transliterator')) {
            $tr = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($tr) $s = $tr->transliterate($s);
        }
        $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    };

    $cleanTitleN = $norm($cleanTitle);
    $artistN     = $norm($artist);

    /* Devuelve la MEJOR similitud entre el artista buscado y cualquiera
       de los artistas del candidato (Spotify suele devolver varios:
       ["A", "B", "C"] para "A feat. B feat. C"). Si cualquiera matchea,
       el candidato es válido — los feats arruinaban el match antes. */
    $bestArtistSim = function(array $cand) use ($norm, $artistN) {
        if ($artistN === '') return 100.0;
        $best = 0.0;
        foreach (($cand['artists'] ?? []) as $a) {
            $candArtistN = $norm((string)($a['name'] ?? ''));
            if ($candArtistN === '') continue;
            /* Match exacto = 100. Substring (tolera "A" vs "A & B") = 80. */
            if ($candArtistN === $artistN) return 100.0;
            if (str_contains($candArtistN, $artistN) || str_contains($artistN, $candArtistN)) {
                $best = max($best, 80.0);
                continue;
            }
            $s = 0.0; similar_text($artistN, $candArtistN, $s);
            if ($s > $best) $best = $s;
        }
        return $best;
    };

    /* Score por candidato — combina similitud de título (peso alto) y
       mejor similitud de artista del listado. */
    $scoreCandidate = function(array $cand) use ($norm, $cleanTitleN, $bestArtistSim) {
        $cTitleN  = $norm((string)($cand['name'] ?? ''));
        if ($cTitleN === '') return 0.0;
        $ts = 0.0; similar_text($cleanTitleN, $cTitleN, $ts);
        $as = $bestArtistSim($cand);
        /* Bonus extra si el título normalizado es exactamente igual o
           si uno está contenido en el otro (cubre "Song" vs "Song -
           Remastered"). */
        $bonus = 0.0;
        if ($cTitleN === $cleanTitleN) $bonus += 20;
        elseif (str_contains($cTitleN, $cleanTitleN) || str_contains($cleanTitleN, $cTitleN)) $bonus += 10;
        if ($as >= 100) $bonus += 10;
        return min(100.0, $ts * 0.7 + $as * 0.3 + $bonus);
    };

    /* Recolecta candidatos de varias queries — más opciones para
       rankear. La primera (filtrada con track:/artist:) suele dar el
       mejor; la libre cubre casos donde la estructurada no halló nada.

       wasRateLimited rastrea si alguna sub-query topó con 429. Si todas
       las que devolvieron 0 items fueron por rate-limit, no cacheamos
       el resultado — la próxima vez puede encontrar el álbum. */
    $candidates = [];
    $wasRateLimited = false;
    if ($artist !== '') {
        [$items, $rl] = $tryQuery('track:"' . $cleanTitle . '" artist:"' . $artist . '"');
        $candidates = array_merge($candidates, $items);
        $wasRateLimited = $wasRateLimited || $rl;
    }
    /* Si la primera sub-query ya topó con 429, NO hacemos la segunda:
       Spotify nos está rate-limitando, otra request solo renueva el ban.
       Devolvemos rateLimited a la cascada que termina en 503 + no cache. */
    if (!$wasRateLimited && count($candidates) < 3) {
        [$items, $rl] = $tryQuery(trim($cleanTitle . ' ' . $artist));
        $candidates = array_merge($candidates, $items);
        $wasRateLimited = $wasRateLimited || $rl;
    }

    /* Dedupe por id de track. */
    $seen = []; $uniq = [];
    foreach ($candidates as $c) {
        $cid = $c['id'] ?? '';
        if ($cid === '' || isset($seen[$cid])) continue;
        $seen[$cid] = true;
        $uniq[] = $c;
    }

    /* CASCADA DE FALLBACKS para garantizar que TODAS las canciones
       reciban un álbum cuando Spotify devuelve resultados. Empezamos
       estrictos (artista debe coincidir + score alto) y relajamos
       progresivamente hasta agarrar algo razonable. Si Spotify no
       devolvió nada de nada, caemos al álbum sintético del final. */
    $pickBest = function(array $list, float $minScore) use ($scoreCandidate) {
        $best = null; $bestScore = 0.0;
        foreach ($list as $c) {
            $sc = $scoreCandidate($c);
            if ($sc > $bestScore) { $best = $c; $bestScore = $sc; }
        }
        return ($best && $bestScore >= $minScore) ? $best : null;
    };

    $best = null;
    /* Nivel 1: artista coincide (≥50%) Y score combinado ≥50. */
    if ($artistN !== '') {
        $filtered = array_values(array_filter($uniq, function($c) use ($bestArtistSim) {
            return $bestArtistSim($c) >= 50.0;
        }));
        $best = $pickBest($filtered, 50.0);
    }
    /* Nivel 2: artista coincide pero score muy bajo aún acepta (≥35). */
    if (!$best && $artistN !== '') {
        $filtered = array_values(array_filter($uniq, function($c) use ($bestArtistSim) {
            return $bestArtistSim($c) >= 35.0;
        }));
        $best = $pickBest($filtered, 35.0);
    }
    /* Nivel 3: olvida el filtro de artista, pero score decente (≥40). */
    if (!$best) {
        $best = $pickBest($uniq, 40.0);
    }
    /* Nivel 4: el TOP-1 absoluto, sin importar score. Si Spotify lo
       puso primero, probablemente es lo más cercano que hay. */
    if (!$best && !empty($uniq)) {
        $best = $uniq[0];
    }

    $result = null;
    if ($best && isset($best['album'])) {
        $alb = $best['album'];
        $img = '';
        if (!empty($alb['images'])) {
            $img = $alb['images'][1]['url'] ?? $alb['images'][0]['url'] ?? '';
        }
        $albId = (string)($alb['id'] ?? '');
        $result = [
            'notFound'       => false,
            'source'         => 'spotify',
            'albumKey'       => $albId !== '' ? ('spotify:' . $albId) : '',
            'albumName'      => $alb['name'] ?? '',
            'albumImage'     => $img,
            'spotifyAlbumId' => $albId, /* legacy field — el cliente legacy lo sigue mirando */
            'albumUrl'       => $alb['external_urls']['spotify'] ?? '',
            'isSingle'       => ($alb['album_type'] ?? '') === 'single',
            'releaseDate'    => $alb['release_date'] ?? '',
            'matchTitle'     => $best['name']   ?? '',
            'matchArtist'    => $best['artists'][0]['name'] ?? '',
            /* matchTrackId: el ID de la canción dentro del álbum que
               coincide con la búsqueda. El cliente lo usa para destacar
               la fila correspondiente en el viewer, garantizando que
               "la canción desde la que se busca está en el álbum". */
            'matchTrackId'   => $best['id']     ?? '',
            'isSynthetic'    => false,
        ];
    }

    /* Si nada superó ninguno de los niveles de la cascada (Spotify no
       devolvió candidatos o todos eran muy malos), gestionamos dos
       casos:
         - Rate limit (429): NO cacheamos y devolvemos un error con
           status 503 + code rateLimited. El cliente sabe que es
           transitorio y NO lo cacheará localmente, así la próxima vez
           reintenta.
         - notFound real: cacheamos por sólo 5 min para que un fallo
           transitorio no quede pegado 7 días, pero un álbum real
           encontrado sí se cachea 7 días (no caduca). */
    if ($result === null) {
        if ($wasRateLimited) {
            jsonResponse([
                'error' => 'Spotify rate-limited (try again in a few seconds)',
                'code'  => 'rateLimited',
            ], 503);
        }
        $result = ['notFound' => true];
        cacheSet($cacheKey, json_encode($result, JSON_UNESCAPED_UNICODE), 5 * 60);
    } else {
        $payload = json_encode($result, JSON_UNESCAPED_UNICODE);
        cacheSet($cacheKey, $payload, 7 * 24 * 3600);
        /* Doble-cacheamos también por videoId si el cliente lo envió,
           para que el #5 (lookup por videoId) tenga hit en futuras
           consultas — incluso si cambia el título exacto de YouTube. */
        if ($videoId !== '') {
            cacheSet('album_track_v1_' . $videoId, $payload, 7 * 24 * 3600);
        }
    }
    jsonResponse($result);
}

/* Lista las canciones de un álbum de Spotify dado su ID. NO resuelve
   videoIds de YouTube — eso lo hace el cliente con yt-search-batch (ya
   existente), así esta llamada es rápida y barata. Cache 7 días: los
   álbumes no cambian de tracklist.

   Devuelve: { name, artist, image, tracks: [{title, artist, duration}] } */
case 'album-tracks': {
    require_once __DIR__ . '/spotify-helpers.php';
    require_once __DIR__ . '/itunes-helpers.php';
    require_once __DIR__ . '/deezer-helpers.php';

    /* Acepta dos formas:
         ?key=itunes:1440834378  / ?key=deezer:6975097  / ?key=spotify:abc
         ?id=abc                 (legacy, asume Spotify)
       Esto permite que el viewer del álbum se sirva desde iTunes o
       Deezer sin tocar nunca a Spotify para los hits que vinieron de
       esas fuentes — exactamente lo que reduce nuestra presión a la
       API de Spotify al mínimo. */
    $key   = (string)($_GET['key'] ?? '');
    $idRaw = (string)($_GET['id']  ?? '');
    $source = ''; $albumId = '';
    if ($key !== '' && str_contains($key, ':')) {
        [$src, $rest] = explode(':', $key, 2);
        $source = strtolower(preg_replace('/[^a-z]/i', '', $src));
        $albumId = $rest;
    } elseif ($idRaw !== '') {
        /* Legacy: cliente viejo manda ?id=spotify_id. */
        $source = 'spotify';
        $albumId = $idRaw;
    }
    if ($albumId === '' || !in_array($source, ['spotify', 'itunes', 'deezer'], true)) {
        jsonError('key o id requeridos');
    }
    /* Sanitizamos según fuente — Spotify es alfanumérico, iTunes/Deezer
       son numéricos. */
    if ($source === 'spotify') $albumId = preg_replace('/[^A-Za-z0-9]/', '', $albumId);
    else                       $albumId = preg_replace('/[^0-9]/', '', $albumId);
    if ($albumId === '') jsonError('id inválido');

    $cacheKey = 'album_tracks_' . $source . '_' . $albumId;
    $cached = cacheGet($cacheKey);
    if ($cached !== null) {
        $decoded = json_decode($cached, true);
        if (is_array($decoded)) { jsonResponse($decoded); }
    }

    /* iTunes / Deezer: sin auth ni rate-limit estricto, fetch directo. */
    if ($source === 'itunes') {
        $r = itunesGetAlbumTracks($albumId);
        if (!$r) jsonError('No se pudo leer el álbum de iTunes', 502);
        cacheSet($cacheKey, json_encode($r, JSON_UNESCAPED_UNICODE), 30 * 24 * 3600);
        jsonResponse($r);
    }
    if ($source === 'deezer') {
        $r = deezerGetAlbumTracks($albumId);
        if (!$r) jsonError('No se pudo leer el álbum de Deezer', 502);
        cacheSet($cacheKey, json_encode($r, JSON_UNESCAPED_UNICODE), 30 * 24 * 3600);
        jsonResponse($r);
    }

    /* Spotify: respeta el guard de rate-limit. Si está activo Y el
       cliente nos pasó hints (name + artist), intentamos resolver el
       mismo álbum vía iTunes/Deezer por nombre — sirve para "migrar"
       los items antiguos del perfil que tenían albumKey spotify:* sin
       que el usuario tenga que borrarlos y re-añadirlos.
       Si NO hay hints, devolvemos 503 como antes. */
    $rateLimitedUntil = (int)cacheGet('spotify_rate_limited_until');
    if ($rateLimitedUntil > time()) {
        $hintName   = trim((string)($_GET['name']   ?? ''));
        $hintArtist = trim((string)($_GET['artist'] ?? ''));
        if ($hintName !== '') {
            /* iTunes search por álbum (entity=album) y devuelve sus tracks. */
            $fallback = _albumTracksFallbackByName($hintName, $hintArtist, $cacheKey);
            if ($fallback !== null) jsonResponse($fallback);
        }
        jsonResponse(['error' => 'Spotify rate-limited', 'code' => 'rateLimited'], 503);
    }
    $token = getSpotifyToken();
    if (!$token) jsonError('No se pudo autenticar con Spotify', 502);

    /* Metadata del álbum (nombre, artista principal, cover). Una sola
       request — Spotify ya devuelve los tracks embebidos. */
    $ctx = stream_context_create(['http' => [
        'timeout' => 10, 'ignore_errors' => true,
        'header'  => 'Authorization: Bearer ' . $token,
    ]]);
    $raw = @file_get_contents('https://api.spotify.com/v1/albums/' . $albumId . '?market=US', false, $ctx);
    if (!$raw) jsonError('No se pudo leer el álbum', 502);
    $album = json_decode($raw, true);
    if (!isset($album['name'])) jsonError('Álbum no encontrado', 404);

    $items = $album['tracks']['items'] ?? [];
    /* Si el álbum tiene más de 50 tracks, Spotify pagina. Seguimos el
       cursor 'next' hasta agotar (paranoid loop con tope para no entrar
       en bucle infinito si la API rompe). */
    $next = $album['tracks']['next'] ?? null;
    $safety = 5;
    while ($next && $safety-- > 0) {
        $more = @file_get_contents($next, false, $ctx);
        if (!$more) break;
        $page = json_decode($more, true);
        if (!isset($page['items'])) break;
        $items = array_merge($items, $page['items']);
        $next = $page['next'] ?? null;
    }

    $tracks = [];
    foreach ($items as $t) {
        if (empty($t['name'])) continue;
        $tracks[] = [
            'title'    => (string)$t['name'],
            'artist'   => (string)($t['artists'][0]['name'] ?? ($album['artists'][0]['name'] ?? '')),
            'duration' => isset($t['duration_ms']) ? (int)round($t['duration_ms'] / 1000) : 0,
        ];
    }

    $image = '';
    if (!empty($album['images'])) {
        $image = $album['images'][1]['url'] ?? $album['images'][0]['url'] ?? '';
    }
    $result = [
        'name'   => (string)$album['name'],
        'artist' => (string)($album['artists'][0]['name'] ?? ''),
        'image'  => $image,
        'tracks' => $tracks,
    ];
    cacheSet($cacheKey, json_encode($result, JSON_UNESCAPED_UNICODE), 7 * 24 * 3600);
    jsonResponse($result);
}

/* ─── Tidal ───────────────────────────────────────────────── */

case 'tidal-track': {
    require_once __DIR__ . '/spotify-helpers.php';   /* searchYouTubeVideo */
    require_once __DIR__ . '/tidal-helpers.php';
    $url = trim($_GET['url'] ?? ($_POST['url'] ?? ''));
    if (!$url) jsonError('URL requerida');
    $trackId = parseTidalTrackId($url);
    if (!$trackId) jsonError('URL de track de Tidal inválida');
    if (!TIDAL_CLIENT_ID || !TIDAL_CLIENT_SECRET) jsonError('Falta configurar las credenciales de Tidal (.env)', 503);
    $meta = tidalGetTrack($trackId);
    if (!$meta) jsonError('No se pudo obtener el track de Tidal', 502);
    /* Mismo helper que spotify-track: pasamos title/artist/duration
       para que escoja el mejor candidato (no el primero). */
    $video = searchYouTubeVideo(
        $meta['title'] . ' ' . $meta['artist'] . ' audio',
        ['title' => $meta['title'], 'artist' => $meta['artist'], 'duration' => (int)($meta['duration'] ?? 0)]
    );
    if (!$video) jsonError('No se encontró el vídeo en YouTube', 502);
    jsonResponse([
        'title'    => $meta['title'],
        'artist'   => $meta['artist'],
        'duration' => $video['duration'] ?: $meta['duration'],
        'videoId'  => $video['videoId'],
    ]);
}

case 'tidal-playlist': {
    require_once __DIR__ . '/tidal-helpers.php';
    set_time_limit(120);
    $b   = jsonBody();
    $url = trim($b['url'] ?? ($_GET['url'] ?? ''));
    if (!$url) jsonError('URL requerida');
    $plId = parseTidalPlaylistId($url);
    if (!$plId) jsonError('URL de playlist de Tidal inválida');
    if (!TIDAL_CLIENT_ID || !TIDAL_CLIENT_SECRET) jsonError('Falta configurar las credenciales de Tidal (.env)', 503);
    $pl = tidalGetPlaylist($plId);
    if ($pl === null) jsonError('No se pudo obtener la playlist de Tidal (¿es pública?)', 502);
    if (empty($pl['tracks'])) jsonError('La playlist no tiene canciones o no es accesible', 404);
    /* Devuelve nombre + tracks SIN resolver (title/artist/duration).
       El cliente los resuelve a YouTube vía yt-search-batch (como el CSV). */
    jsonResponse(['name' => $pl['name'], 'tracks' => $pl['tracks']]);
}

/* ─── Import de playlist de YouTube ──────────────────────── */

case 'import-playlist': {
    $b   = jsonBody();
    $url = trim($b['url'] ?? '');
    $playlistId = '';
    if (preg_match('/[?&]list=([A-Za-z0-9_-]+)/', $url, $m)) {
        $playlistId = preg_replace('/[^A-Za-z0-9_-]/', '', $m[1]);
    }
    if (!$playlistId) jsonError('URL de playlist inválida');
    $ctx = stream_context_create(['http' => [
        'timeout' => 20, 'ignore_errors' => true,
        'header'  => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ]),
    ]]);
    $html = @file_get_contents('https://www.youtube.com/playlist?list=' . $playlistId, false, $ctx);
    if (!$html) jsonError('No se pudo acceder a la playlist', 502);
    $marker = 'var ytInitialData = ';
    $pos = strpos($html, $marker);
    if ($pos === false) jsonError('No se pudo parsear la playlist', 502);
    $pos += strlen($marker);
    $endPos = strpos($html, ';</script>', $pos);
    if ($endPos === false) $endPos = strpos($html, ';var ', $pos);
    if ($endPos === false) jsonError('Datos de playlist mal formados', 502);
    $data = json_decode(substr($html, $pos, $endPos - $pos), true);
    if (!$data) jsonError('JSON de playlist inválido', 502);
    $findKey = function($arr, $key) use (&$findKey) {
        if (!is_array($arr)) return null;
        if (isset($arr[$key])) return $arr[$key];
        foreach ($arr as $v) { $r = $findKey($v, $key); if ($r !== null) return $r; }
        return null;
    };
    $videoList = $findKey($data, 'playlistVideoListRenderer');
    if (!$videoList || empty($videoList['contents'])) jsonError('No se encontraron canciones en la playlist', 404);
    $tracks = [];
    foreach ($videoList['contents'] as $item) {
        $v = $item['playlistVideoRenderer'] ?? null;
        if (!$v) continue;
        $vid = preg_replace('/[^A-Za-z0-9_-]/', '', $v['videoId'] ?? '');
        if (strlen($vid) !== 11) continue;
        $title  = $v['title']['runs'][0]['text'] ?? '';
        $artist = $v['shortBylineText']['runs'][0]['text'] ?? '';
        $artist = trim(preg_replace('/\s*-\s*topic$/i', '', $artist));
        $lenText = $v['lengthText']['simpleText'] ?? '';
        $duration = 0;
        if ($lenText) {
            $parts = explode(':', $lenText);
            if (count($parts) === 2)      $duration = (int)$parts[0] * 60 + (int)$parts[1];
            elseif (count($parts) === 3)  $duration = (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
        }
        $tracks[] = [
            'videoId'  => $vid,
            'title'    => substr(strip_tags($title),  0, 200),
            'artist'   => substr(strip_tags($artist), 0, 200),
            'duration' => $duration,
        ];
    }
    if (empty($tracks)) jsonError('No se encontraron canciones en la playlist', 404);
    jsonResponse(['tracks' => $tracks]);
}

/* ─── TV LINK (código de emparejamiento) ──────────────────
   El móvil llama get-tv-code → recibe un código de 6 dígitos.
   La TV (sin sesión) lo introduce en tv.php → tv.php valida con
   la tabla `tv_link_codes` y crea sesión persistente. */
case 'get-tv-code': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    /* Auto-crea tabla idempotente. */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tv_link_codes (
            code VARCHAR(6) PRIMARY KEY,
            user_key VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created (created_at)
        ) DEFAULT CHARSET=utf8mb4
    ");
    /* Limpia códigos caducados (>5 min). */
    $pdo->exec("DELETE FROM tv_link_codes WHERE created_at < NOW() - INTERVAL 5 MINUTE");
    /* Genera código único — random_int es seguro criptográficamente. */
    $code = '';
    for ($i = 0; $i < 10; $i++) {
        $code = sprintf('%06d', random_int(0, 999999));
        $stmt = $pdo->prepare("SELECT 1 FROM tv_link_codes WHERE code = ?");
        $stmt->execute([$code]);
        if (!$stmt->fetchColumn()) break;
    }
    $pdo->prepare("INSERT INTO tv_link_codes (code, user_key) VALUES (?, ?)")
        ->execute([$code, $userKey]);
    jsonResponse(['code' => $code, 'expiresIn' => 300]);
}

/* ─── NOW PLAYING (TV companion view) ──────────────────────
   El shell del móvil publica su estado de reproducción aquí y
   tv.php hace polling cada segundo para mostrarlo en la TV.
   Tabla auto-creada al primer save. */
case 'save-now-playing': {
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    if (!$b) jsonError('Datos inválidos');

    /* Crea la tabla si no existe — idempotente. */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS now_playing (
            user_id    INT PRIMARY KEY,
            track_json TEXT NOT NULL,
            is_playing TINYINT(1) DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) DEFAULT CHARSET=utf8mb4
    ");

    /* Sanitiza y guarda solo los campos que la TV necesita. */
    $track = [
        'videoId'  => isset($b['videoId']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $b['videoId']) : '',
        'title'    => mb_substr(trim($b['title']  ?? ''), 0, 200),
        'artist'   => mb_substr(trim($b['artist'] ?? ''), 0, 200),
        'plName'   => mb_substr(trim($b['plName'] ?? ''), 0, 100),
        'position' => isset($b['position']) ? max(0, (float)$b['position']) : 0,
        'duration' => isset($b['duration']) ? max(0, (float)$b['duration']) : 0,
    ];
    $playing = !empty($b['isPlaying']) ? 1 : 0;

    $pdo->prepare("INSERT INTO now_playing (user_id, track_json, is_playing)
                   VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE track_json=VALUES(track_json), is_playing=VALUES(is_playing)")
        ->execute([$uid, json_encode($track, JSON_UNESCAPED_UNICODE), $playing]);
    jsonResponse(['ok' => true]);
}

/* ─── Letras (LRCLIB) ───────────────────────────────────── */
/* ─── Debug logger del módulo lyric-video del fullscreen player ───
   POSTea {msg} → escribe línea con timestamp en logs/lyrics-debug.log
   Solo para diagnosticar bugs de render/sync de letras. */
case 'client-log': {
    $b = jsonBody();
    $msg = is_string($b['msg'] ?? null) ? substr($b['msg'], 0, 2000) : '';
    if ($msg === '') jsonResponse(['ok' => true]);
    $logDir  = dirname(__DIR__, 2) . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = $logDir . '/lyrics-debug.log';
    /* Rotate al sobrepasar 512KB (evita que crezca eternamente). */
    if (file_exists($logFile) && filesize($logFile) > 512 * 1024) {
        @rename($logFile, $logFile . '.1');
    }
    $stamp = date('Y-m-d H:i:s.') . substr(microtime(false), 2, 3);
    $line  = "[$stamp] {$userKey}: {$msg}\n";
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    jsonResponse(['ok' => true]);
}

case 'get-lyrics': {
    require_once __DIR__ . '/lyrics-helpers.php';
    $title    = trim($_GET['title']    ?? '');
    $artist   = trim($_GET['artist']   ?? '');
    $duration = max(0, (int)($_GET['duration'] ?? 0));
    if ($title === '') jsonError('Falta title', 400);
    $r = getLyrics($title, $artist, $duration);
    if (!$r) {
        jsonResponse(['ok' => true, 'found' => false]);
    }
    jsonResponse([
        'ok'       => true,
        'found'    => true,
        'plain'    => $r['plain'],
        'synced'   => $r['synced'],
        'matched'  => [
            'title'  => $r['matched_title'],
            'artist' => $r['matched_artist'],
        ],
    ]);
}

case 'get-now-playing': {
    /* Connection: close se setea al inicio del script (antes de
       requireAuth) para que aplique también a 401. */
    $uid = uidByKey($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    /* Si la tabla no existe (nunca se llamó save), devolvemos vacío. */
    try {
        $stmt = $pdo->prepare("SELECT track_json, is_playing, UNIX_TIMESTAMP(updated_at) AS ts
                               FROM now_playing WHERE user_id = ?");
        $stmt->execute([$uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { $row = null; }
    /* `age` = segundos transcurridos en el servidor desde el último
       save-now-playing. Permite que la TV compute la posición REAL del
       móvil compensando la latencia de red móvil→servidor. Sin esto,
       el polling de la TV usa una snapshot que puede estar varios
       segundos atrasada → desincronización progresiva. */
    $payload = $row
        ? [
            'ok'        => true,
            'track'     => json_decode($row['track_json'], true),
            'isPlaying' => !!$row['is_playing'],
            'updatedAt' => (int)$row['ts'],
            'age'       => max(0, time() - (int)$row['ts']),
            'serverNow' => time(),
            'user'      => $u['label'],
        ]
        : ['ok' => true, 'track' => null, 'isPlaying' => false];

    /* Modo iframe — para Smart TVs en las que ni XHR ni <script src>
       funcionan tras el primer poll. El padre lee el JSON desde
       iframe.contentDocument tras iframe.onload. Sin script dentro del
       iframe (no corren en navegaciones dinámicas en algunos browsers). */
    if (isset($_GET['frame'])) {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><body><pre id="d">' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '</pre></body></html>';
        exit;
    }

    jsonResponse($payload);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}

/* Fallback de album-tracks cuando el albumKey original es spotify:* y
   Spotify está banneado. Busca el álbum por NOMBRE + artista en iTunes
   primero, luego Deezer; si encuentra, devuelve su tracklist usando la
   misma estructura que el case principal. Cachea el resultado bajo la
   key original (`album_tracks_spotify_<id>`) para que la próxima petición
   se sirva desde cache sin re-resolver. Devuelve null si nada matchea. */
function _albumTracksFallbackByName(string $name, string $artist, string $origCacheKey): ?array {
    require_once __DIR__ . '/cache-helpers.php';
    require_once __DIR__ . '/itunes-helpers.php';
    require_once __DIR__ . '/deezer-helpers.php';

    /* iTunes search por álbum. entity=album devuelve collectionId. */
    $q = $artist !== '' ? ($name . ' ' . $artist) : $name;
    $url = 'https://itunes.apple.com/search?term=' . rawurlencode($q)
         . '&entity=album&limit=5&country=US';
    $ctx = stream_context_create(['http' => [
        'timeout' => 6, 'ignore_errors' => true,
        'header'  => "User-Agent: MelonHub/1.0\r\n",
    ]]);
    $raw  = @file_get_contents($url, false, $ctx);
    $data = $raw ? json_decode($raw, true) : null;
    if (is_array($data) && !empty($data['results'])) {
        $nameN = mb_strtolower($name);
        foreach ($data['results'] as $r) {
            $collId = (string)($r['collectionId'] ?? '');
            if ($collId === '') continue;
            $collName = mb_strtolower((string)($r['collectionName'] ?? ''));
            if ($collName === '' || (strpos($collName, $nameN) === false && strpos($nameN, $collName) === false)) continue;
            $tracks = itunesGetAlbumTracks($collId);
            if ($tracks) {
                cacheSet($origCacheKey, json_encode($tracks, JSON_UNESCAPED_UNICODE), 30 * 24 * 3600);
                return $tracks;
            }
        }
    }
    /* Deezer search por álbum. */
    $dUrl = 'https://api.deezer.com/search/album?q=' . rawurlencode($q) . '&limit=5';
    $dRaw = @file_get_contents($dUrl, false, $ctx);
    $dData = $dRaw ? json_decode($dRaw, true) : null;
    if (is_array($dData) && !empty($dData['data'])) {
        $nameN = mb_strtolower($name);
        foreach ($dData['data'] as $a) {
            $aid   = (string)($a['id'] ?? '');
            if ($aid === '') continue;
            $aName = mb_strtolower((string)($a['title'] ?? ''));
            if ($aName === '' || (strpos($aName, $nameN) === false && strpos($nameN, $aName) === false)) continue;
            $tracks = deezerGetAlbumTracks($aid);
            if ($tracks) {
                cacheSet($origCacheKey, json_encode($tracks, JSON_UNESCAPED_UNICODE), 30 * 24 * 3600);
                return $tracks;
            }
        }
    }
    return null;
}
