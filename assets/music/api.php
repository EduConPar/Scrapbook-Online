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

   POST  ?action=save-playlist-item   { id, name, tracks[], sharedFrom? }
   POST  ?action=delete-playlist      { id }
   POST  ?action=leave-playlist       { id, sharedFrom }
   POST  ?action=invite-collaborator  { playlistId, toUser }
   POST  ?action=remove-collaborator  { playlistId, collaborator }
   POST  ?action=respond-invite       { inviteId, action: 'accept'|'reject'|'dismiss' }
   POST  ?action=add-track            { videoId, title, artist? }
   POST  ?action=yt-search-batch      { tracks[] }
   POST  ?action=import-playlist      { url }

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
    global $loginUsers;
    $users = [];
    foreach ($loginUsers as $k => $u2) {
        if ($k === $userKey) continue;
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
    jsonResponse(['title' => $data['title']]);
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
    require_once __DIR__ . '/spotify-helpers.php';
    $b = jsonBody();
    if (!isset($b['tracks']) || !is_array($b['tracks'])) jsonError('Datos inválidos');
    $results = [];
    foreach ($b['tracks'] as $t) {
        $title    = substr(strip_tags($t['title']  ?? ''), 0, 200);
        $artist   = substr(strip_tags($t['artist'] ?? ''), 0, 200);
        $duration = max(0, intval($t['duration'] ?? 0));
        if (!$title) continue;
        $video = searchYouTubeVideo($title . ' ' . $artist . ' audio');
        if (!$video) continue;
        $results[] = ['videoId' => $video['videoId'], 'title' => $title, 'artist' => $artist, 'duration' => $video['duration'] ?: $duration];
        usleep(150000);
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
    $videoId  = searchYouTubeVideoId($title . ' ' . $artist . ' audio');
    if (!$videoId) jsonError('No se encontró el vídeo en YouTube', 502);
    jsonResponse(['title' => $title, 'artist' => $artist, 'duration' => $duration, 'videoId' => $videoId]);
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

default:
    jsonError('Acción no válida: ' . $action, 400);
}
