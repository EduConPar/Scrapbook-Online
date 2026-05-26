<?php
/* ──────────────────────────────────────────────────────────
   MUSIC API — router único de la app Música
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

   ALMACENAMIENTO:
   - Playlists: assets/music/playlist/{userKey}.json
   - Invites de colaboración / notifs: assets/music/{userKey}-invites.json
   - Tracks extra: assets/music/{userKey}-extra.json

   El SSE notifications-stream.php se mantiene como archivo aparte porque
   tiene un formato (text/event-stream) incompatible con un router JSON.
   ────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';

$u         = requireAuth();
$userKey   = $u['key'];
$userLabel = $u['label'];
$action    = $_GET['action'] ?? $_POST['action'] ?? '';

/* ── Helpers de rutas y JSON ─────────────────────────────── */
function playlistFile(string $uk): string {
    return __DIR__ . '/playlist/' . $uk . '.json';
}
function invitesFile(string $uk): string {
    return __DIR__ . '/' . $uk . '-invites.json';
}
function extraFile(string $uk): string {
    return __DIR__ . '/' . $uk . '-extra.json';
}
function readJsonArray(string $path): array {
    if (!file_exists($path)) return [];
    $d = json_decode(file_get_contents($path), true);
    return is_array($d) ? $d : [];
}
function writeJson(string $path, $data): void {
    /* Asegurar que la carpeta exista (importante para playlist/) */
    $dir = dirname($path);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

switch ($action) {

/* ─── Playlists ──────────────────────────────────────────── */

case 'get-playlists': {
    global $loginUsers;
    $list = readJsonArray(playlistFile($userKey));
    $result = [];
    foreach ($list as $entry) {
        if (isset($entry['sharedFrom'])) {
            $ownerKey = $entry['sharedFrom'];
            if (!isset($loginUsers[$ownerKey])) continue;
            $ownerPls = readJsonArray(playlistFile($ownerKey));
            foreach ($ownerPls as $pl) {
                if (($pl['id'] ?? '') === ($entry['id'] ?? '')) {
                    $pl['sharedFrom']  = $ownerKey;
                    $pl['sharedLabel'] = $loginUsers[$ownerKey]['label'];
                    $result[] = $pl;
                    break;
                }
            }
        } else {
            $result[] = $entry;
        }
    }
    /* Migración legacy {userKey}-custom.json (si existía) — sólo la primera vez */
    if (empty($list)) {
        $customFile = __DIR__ . '/' . $userKey . '-custom.json';
        if (file_exists($customFile)) {
            $tracks = json_decode(file_get_contents($customFile), true);
            if (is_array($tracks) && !empty($tracks)) {
                $migrated = [['id' => 'pl_legacy', 'name' => 'Mi playlist', 'owner' => $userKey, 'tracks' => $tracks]];
                writeJson(playlistFile($userKey), $migrated);
                $result = $migrated;
            }
        }
    }
    jsonResponse($result);
}

case 'save-playlist-item': {
    global $loginUsers;
    $b = jsonBody();
    if (!isset($b['id'], $b['name'])) jsonError('Datos incompletos');
    $id         = preg_replace('/[^A-Za-z0-9_-]/', '', $b['id']);
    $name       = substr(strip_tags($b['name']), 0, 100);
    $sharedFrom = isset($b['sharedFrom']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $b['sharedFrom']) : null;
    if (!$id) jsonError('ID inválido');

    /* Sanitizar tracks: videoId 11 chars obligatorio */
    $tracks = [];
    foreach (($b['tracks'] ?? []) as $t) {
        $vid = preg_replace('/[^A-Za-z0-9_-]/', '', $t['videoId'] ?? '');
        if (strlen($vid) !== 11) continue;
        $tracks[] = [
            'videoId'  => $vid,
            'title'    => substr(strip_tags($t['title']  ?? ''), 0, 200),
            'artist'   => substr(strip_tags($t['artist'] ?? ''), 0, 200),
            'duration' => max(0, intval($t['duration'] ?? 0)),
            'addedBy'  => substr(strip_tags($t['addedBy'] ?? ''), 0, 100),
        ];
    }

    if ($sharedFrom) {
        if (!isset($loginUsers[$sharedFrom])) jsonError('Propietario inválido');
        $ownerPls = readJsonArray(playlistFile($sharedFrom));
        $authorized = false;
        foreach ($ownerPls as $pl) {
            if (($pl['id'] ?? '') === $id) {
                $authorized = isset($pl['collaborators']) && in_array($userKey, $pl['collaborators'], true);
                break;
            }
        }
        if (!$authorized) jsonError('Sin permiso para editar', 403);
        foreach ($ownerPls as &$pl) {
            if (($pl['id'] ?? '') === $id) { $pl['tracks'] = $tracks; break; }
        }
        unset($pl);
        writeJson(playlistFile($sharedFrom), $ownerPls);
        jsonResponse(['ok' => true]);
    }

    $playlists = readJsonArray(playlistFile($userKey));
    $found = false;
    foreach ($playlists as &$pl) {
        if (($pl['id'] ?? '') === $id) {
            $pl['name']   = $name;
            $pl['tracks'] = $tracks;
            if (!isset($pl['owner'])) $pl['owner'] = $userKey;
            $found = true;
            break;
        }
    }
    unset($pl);
    if (!$found) {
        $playlists[] = ['id' => $id, 'name' => $name, 'owner' => $userKey, 'collaborators' => [], 'tracks' => $tracks];
    }
    writeJson(playlistFile($userKey), $playlists);
    jsonResponse(['ok' => true]);
}

case 'delete-playlist': {
    global $loginUsers;
    $b  = jsonBody();
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', $b['id'] ?? '');
    if (!$id) jsonError('ID inválido');

    $playlists = readJsonArray(playlistFile($userKey));
    $deleted = null;
    foreach ($playlists as $pl) if (($pl['id'] ?? '') === $id) { $deleted = $pl; break; }
    $playlists = array_values(array_filter($playlists, fn($pl) => ($pl['id'] ?? '') !== $id));
    writeJson(playlistFile($userKey), $playlists);

    if ($deleted && !empty($deleted['collaborators'])) {
        foreach ($deleted['collaborators'] as $ck) {
            if (!isset($loginUsers[$ck])) continue;
            $cps = readJsonArray(playlistFile($ck));
            $cps = array_values(array_filter($cps, fn($p) => ($p['id'] ?? '') !== $id));
            writeJson(playlistFile($ck), $cps);
        }
    }
    jsonResponse(['ok' => true]);
}

case 'leave-playlist': {
    global $loginUsers;
    $b          = jsonBody();
    $id         = preg_replace('/[^A-Za-z0-9_-]/', '', $b['id']         ?? '');
    $sharedFrom = preg_replace('/[^A-Za-z0-9_-]/', '', $b['sharedFrom'] ?? '');
    if (!$id || !$sharedFrom) jsonError('Datos incompletos');
    if (!isset($loginUsers[$sharedFrom])) jsonError('Propietario inválido');

    $myPls = readJsonArray(playlistFile($userKey));
    $myPls = array_values(array_filter($myPls, fn($p) => ($p['id'] ?? '') !== $id));
    writeJson(playlistFile($userKey), $myPls);

    $ownerPls = readJsonArray(playlistFile($sharedFrom));
    $playlistName = '';
    foreach ($ownerPls as &$pl) {
        if (($pl['id'] ?? '') === $id) {
            $playlistName = $pl['name'] ?? '';
            if (isset($pl['collaborators'])) {
                $pl['collaborators'] = array_values(array_filter($pl['collaborators'], fn($c) => $c !== $userKey));
            }
            break;
        }
    }
    unset($pl);
    writeJson(playlistFile($sharedFrom), $ownerPls);

    /* Notificar al dueño */
    $ownerInvs = readJsonArray(invitesFile($sharedFrom));
    $ownerInvs[] = [
        'id'           => 'left_' . time() . '_' . rand(1000, 9999),
        'type'         => 'collab-left',
        'playlistId'   => $id,
        'playlistName' => $playlistName,
        'fromLabel'    => $loginUsers[$userKey]['label'],
        'sentAt'       => time(),
    ];
    writeJson(invitesFile($sharedFrom), $ownerInvs);
    jsonResponse(['ok' => true]);
}

/* ─── Colaboraciones ─────────────────────────────────────── */

case 'invite-collaborator': {
    global $loginUsers;
    $b          = jsonBody();
    $playlistId = preg_replace('/[^A-Za-z0-9_-]/', '', $b['playlistId'] ?? '');
    $toUser     = preg_replace('/[^A-Za-z0-9_-]/', '', $b['toUser']     ?? '');
    if (!$playlistId || !$toUser)             jsonError('Datos incompletos');
    if (!isset($loginUsers[$toUser]))          jsonError('Usuario destino inválido');
    if ($toUser === $userKey)                  jsonError('No puedes invitarte a ti mismo');

    $myPls = readJsonArray(playlistFile($userKey));
    $playlist = null;
    foreach ($myPls as $pl) if (($pl['id'] ?? '') === $playlistId) { $playlist = $pl; break; }
    if (!$playlist) jsonError('Playlist no encontrada', 404);

    if (in_array($toUser, $playlist['collaborators'] ?? [], true)) {
        jsonError($loginUsers[$toUser]['label'] . ' ya es colaborador');
    }

    $invs = readJsonArray(invitesFile($toUser));
    foreach ($invs as $inv) {
        if (($inv['playlistId'] ?? '') === $playlistId) jsonError('Ya existe una invitación pendiente');
    }
    $invs[] = [
        'id'           => 'inv_' . time() . '_' . rand(1000, 9999),
        'playlistId'   => $playlistId,
        'playlistName' => $playlist['name'] ?? '',
        'fromUser'     => $userKey,
        'fromLabel'    => $loginUsers[$userKey]['label'],
        'sentAt'       => time(),
    ];
    writeJson(invitesFile($toUser), $invs);
    jsonResponse(['ok' => true]);
}

case 'remove-collaborator': {
    global $loginUsers;
    $b            = jsonBody();
    $playlistId   = preg_replace('/[^A-Za-z0-9_-]/', '', $b['playlistId']   ?? '');
    $collaborator = preg_replace('/[^A-Za-z0-9_-]/', '', $b['collaborator'] ?? '');
    if (!$playlistId || !$collaborator)         jsonError('Datos incompletos');
    if (!isset($loginUsers[$collaborator]))      jsonError('Usuario inválido');

    $ownerPls = readJsonArray(playlistFile($userKey));
    $playlistName = '';
    foreach ($ownerPls as &$pl) {
        if (($pl['id'] ?? '') === $playlistId) {
            $playlistName = $pl['name'] ?? '';
            if (isset($pl['collaborators'])) {
                $pl['collaborators'] = array_values(array_filter($pl['collaborators'], fn($c) => $c !== $collaborator));
            }
            break;
        }
    }
    unset($pl);
    writeJson(playlistFile($userKey), $ownerPls);

    /* Quitar la playlist compartida de la lista del colaborador */
    $collabPls = readJsonArray(playlistFile($collaborator));
    $collabPls = array_values(array_filter($collabPls, fn($p) => ($p['id'] ?? '') !== $playlistId));
    writeJson(playlistFile($collaborator), $collabPls);

    /* Notificar al colaborador */
    $notifs = readJsonArray(invitesFile($collaborator));
    $notifs[] = [
        'id'           => 'rm_' . time() . '_' . rand(1000, 9999),
        'type'         => 'removed',
        'playlistId'   => $playlistId,
        'playlistName' => $playlistName,
        'fromLabel'    => $loginUsers[$userKey]['label'],
        'sentAt'       => time(),
    ];
    writeJson(invitesFile($collaborator), $notifs);
    jsonResponse(['ok' => true]);
}

case 'respond-invite': {
    global $loginUsers;
    $b        = jsonBody();
    $inviteId = $b['inviteId'] ?? '';
    $act      = $b['action']   ?? '';
    if (!$inviteId || !in_array($act, ['accept','reject','dismiss'], true)) jsonError('Datos inválidos');

    $invites = readJsonArray(invitesFile($userKey));
    $invite = null;
    foreach ($invites as $inv) if (($inv['id'] ?? '') === $inviteId) { $invite = $inv; break; }
    if (!$invite) jsonError('Invitación no encontrada', 404);
    $invites = array_values(array_filter($invites, fn($i) => ($i['id'] ?? '') !== $inviteId));
    writeJson(invitesFile($userKey), $invites);

    if ($act === 'accept') {
        $fromUser   = $invite['fromUser'];
        $playlistId = $invite['playlistId'];
        if (isset($loginUsers[$fromUser])) {
            $myPls = readJsonArray(playlistFile($userKey));
            $has = false; foreach ($myPls as $pl) if (($pl['id'] ?? '') === $playlistId) { $has = true; break; }
            if (!$has) {
                $myPls[] = ['id' => $playlistId, 'sharedFrom' => $fromUser];
                writeJson(playlistFile($userKey), $myPls);
            }
            $ownerPls = readJsonArray(playlistFile($fromUser));
            foreach ($ownerPls as &$pl) {
                if (($pl['id'] ?? '') === $playlistId) {
                    if (!isset($pl['collaborators'])) $pl['collaborators'] = [];
                    if (!in_array($userKey, $pl['collaborators'], true)) $pl['collaborators'][] = $userKey;
                    break;
                }
            }
            unset($pl);
            writeJson(playlistFile($fromUser), $ownerPls);
        }
    }

    if (in_array($act, ['accept','reject'], true) && isset($invite['fromUser'], $loginUsers[$invite['fromUser']])) {
        $ownerNotifs = readJsonArray(invitesFile($invite['fromUser']));
        $ownerNotifs[] = [
            'id'           => 'resp_' . time() . '_' . rand(1000, 9999),
            'type'         => $act === 'accept' ? 'collab-accepted' : 'collab-rejected',
            'playlistId'   => $invite['playlistId'],
            'playlistName' => $invite['playlistName'] ?? '',
            'fromLabel'    => $loginUsers[$userKey]['label'],
            'sentAt'       => time(),
        ];
        writeJson(invitesFile($invite['fromUser']), $ownerNotifs);
    }
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

/* ─── Tracks extra ───────────────────────────────────────── */

case 'add-track': {
    $b = jsonBody();
    if (!isset($b['videoId'], $b['title'])) jsonError('Datos incompletos');
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $b['videoId']);
    if (strlen($videoId) !== 11) jsonError('ID de video inválido');
    $title   = substr(strip_tags($b['title']),  0, 200);
    $artist  = substr(strip_tags($b['artist'] ?? ''), 0, 200);
    $tracks  = readJsonArray(extraFile($userKey));
    $track   = ['title' => $title, 'artist' => $artist, 'videoId' => $videoId];
    $tracks[] = $track;
    writeJson(extraFile($userKey), $tracks);
    jsonResponse(['ok' => true, 'track' => $track]);
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
