<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
set_time_limit(60);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/music/spotify-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
if (!array_key_exists($_SESSION['user'], $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['error' => 'Datos inválidos']); exit; }

$itemType       = isset($body['itemType'])       ? $body['itemType'] : '';
$title          = isset($body['title'])          ? mb_substr(trim($body['title']), 0, 200) : '';
$artist         = isset($body['artist'])         ? mb_substr(trim($body['artist']), 0, 200) : '';
$ytId           = isset($body['ytId'])           ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['ytId'])           : '';
$ytPlaylistId   = isset($body['ytPlaylistId'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['ytPlaylistId'])   : '';
$spotifyAlbumId = isset($body['spotifyAlbumId']) ? preg_replace('/[^A-Za-z0-9]/',   '', $body['spotifyAlbumId']) : '';
$spotifyId      = isset($body['spotifyId'])      ? preg_replace('/[^A-Za-z0-9]/',   '', $body['spotifyId'])      : '';

if (!in_array($itemType, ['song', 'album'])) {
    echo json_encode(['error' => 'Tipo inválido']); exit;
}

// Searches all queries simultaneously using curl_multi; returns array of videoId|null indexed by query position
function searchYouTubeParallel($queries) {
    if (empty($queries)) return [];
    $curlHeaders = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept-Language: en-US,en;q=0.9',
    ];
    $mh      = curl_multi_init();
    $handles = [];
    foreach ($queries as $i => $q) {
        $ch = curl_init('https://www.youtube.com/results?search_query=' . urlencode($q));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => $curlHeaders,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$i] = $ch;
    }
    $active = null;
    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) curl_multi_select($mh, 1.0);
    } while ($active && $status == CURLM_OK);

    $results = [];
    foreach ($handles as $i => $ch) {
        $html = curl_multi_getcontent($ch);
        $vid  = null;
        if ($html && preg_match('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m)) {
            $vid = $m[1];
        }
        $results[$i] = $vid;
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    return $results;
}

// ── Song ──────────────────────────────────────────────────────────────────
if ($itemType === 'song') {
    if ($ytId) {
        echo json_encode(['tracks' => [['videoId' => $ytId, 'title' => $title, 'artist' => $artist]]]);
        exit;
    }
    $vids = searchYouTubeParallel([trim($title . ' ' . $artist . ' audio')]);
    if (empty($vids[0])) { echo json_encode(['error' => 'No se encontró el vídeo en YouTube']); exit; }
    echo json_encode(['tracks' => [['videoId' => $vids[0], 'title' => $title, 'artist' => $artist]]]);
    exit;
}

// ── Album: YouTube playlist ───────────────────────────────────────────────
if ($ytPlaylistId) {
    $curlHeaders = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept-Language: en-US,en;q=0.9',
    ];
    $ch = curl_init('https://www.youtube.com/playlist?list=' . $ytPlaylistId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => $curlHeaders,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    if (!$html) { echo json_encode(['error' => 'No se pudo cargar la playlist de YouTube']); exit; }

    preg_match_all(
        '/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"[^}]{0,400}?"title"\s*:\s*\{\s*"runs"\s*:\s*\[\s*\{\s*"text"\s*:\s*"([^"]+)"/',
        $html, $pairs
    );
    $seen = []; $tracks = [];
    if (!empty($pairs[1])) {
        for ($i = 0; $i < count($pairs[1]); $i++) {
            $vid = $pairs[1][$i];
            if (isset($seen[$vid])) continue;
            $seen[$vid] = true;
            $tracks[] = ['videoId' => $vid, 'title' => !empty($pairs[2][$i]) ? $pairs[2][$i] : $title, 'artist' => $artist];
            if (count($tracks) >= 50) break;
        }
    }
    if (empty($tracks)) {
        preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m);
        foreach ($m[1] as $vid) {
            if (isset($seen[$vid])) continue;
            $seen[$vid] = true;
            $tracks[] = ['videoId' => $vid, 'title' => $title, 'artist' => $artist];
            if (count($tracks) >= 50) break;
        }
    }
    if (!$tracks) { echo json_encode(['error' => 'No se encontraron vídeos en la playlist']); exit; }
    echo json_encode(['tracks' => $tracks]);
    exit;
}

// ── Album: Spotify album / playlist ──────────────────────────────────────
if ($spotifyAlbumId) {
    $token = getSpotifyToken();
    if (!$token) { echo json_encode(['error' => 'No se pudo conectar con Spotify']); exit; }

    $curlHeaders = [
        'Authorization: Bearer ' . $token,
    ];

    // Fetch Spotify tracks (try album, then playlist)
    $spTracks = null;
    foreach (['albums/' . $spotifyAlbumId . '/tracks', 'playlists/' . $spotifyAlbumId . '/tracks'] as $endpoint) {
        $ch = curl_init('https://api.spotify.com/v1/' . $endpoint . '?limit=50');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => $curlHeaders,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        if (!$raw) continue;
        $data = json_decode($raw, true);
        if (!isset($data['items'])) continue;
        $items = $data['items'];
        // Playlist items wrap tracks in a 'track' key
        if (!empty($items[0]['track'])) {
            $items = array_values(array_filter(array_map(function($i) { return $i['track'] ?? null; }, $items)));
        }
        if (!empty($items)) { $spTracks = $items; break; }
    }
    if (!$spTracks) { echo json_encode(['error' => 'No se encontraron canciones en el álbum']); exit; }

    // Build parallel search queries
    $queries   = [];
    $trackMeta = [];
    foreach ($spTracks as $track) {
        if (empty($track['name'])) continue;
        $tTitle  = $track['name'];
        $tArtist = $track['artists'][0]['name'] ?? $artist;
        $queries[]   = $tTitle . ' ' . $tArtist . ' audio';
        $trackMeta[] = ['title' => $tTitle, 'artist' => $tArtist];
    }
    if (!$queries) { echo json_encode(['error' => 'Sin pistas válidas']); exit; }

    // All YouTube searches at once
    $videoIds = searchYouTubeParallel($queries);

    $tracks = [];
    foreach ($videoIds as $i => $vid) {
        if (!$vid) continue;
        $tracks[] = ['videoId' => $vid, 'title' => $trackMeta[$i]['title'], 'artist' => $trackMeta[$i]['artist']];
    }
    if (!$tracks) { echo json_encode(['error' => 'No se encontraron vídeos en YouTube']); exit; }
    echo json_encode(['tracks' => $tracks]);
    exit;
}

echo json_encode(['error' => 'Sin datos de reproducción']);
