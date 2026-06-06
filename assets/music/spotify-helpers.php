<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/cache-helpers.php';

function getSpotifyToken() {
    /* Token cacheado en SQL (app_cache), ya no en disco. */
    $tok = cacheGet('spotify_app_token');
    if ($tok !== null) return $tok;

    $creds = base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET);
    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Authorization: Basic {$creds}\r\nContent-Type: application/x-www-form-urlencoded",
        'content'       => 'grant_type=client_credentials',
        'timeout'       => 10,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents('https://accounts.spotify.com/api/token', false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!isset($data['access_token'])) return null;
    /* TTL = vida del token menos 60s de margen (se refresca un poco antes). */
    cacheSet('spotify_app_token', $data['access_token'], (int)$data['expires_in'] - 60);
    return $data['access_token'];
}

function getSpotifyWebToken() {
    $tok = cacheGet('spotify_web_token');
    if ($tok !== null) return $tok;

    $ctx = stream_context_create(['http' => [
        'timeout'       => 10,
        'ignore_errors' => true,
        'header'        => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept: application/json',
            'Referer: https://open.spotify.com/',
        ]),
    ]]);
    $raw = @file_get_contents('https://open.spotify.com/get_access_token?reason=transport&productType=web_player', false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!isset($data['accessToken'])) return null;
    $expires = isset($data['accessTokenExpirationTimestampMs'])
        ? (int)($data['accessTokenExpirationTimestampMs'] / 1000)
        : time() + 3600;
    cacheSet('spotify_web_token', $data['accessToken'], $expires - time() - 60);
    return $data['accessToken'];
}

function searchYouTubeVideo($query) {
    $headers = implode("\r\n", [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept-Language: en-US,en;q=0.9',
    ]);

    $searchCtx = stream_context_create(['http' => [
        'timeout' => 10, 'ignore_errors' => true, 'header' => $headers,
    ]]);
    $html = @file_get_contents('https://www.youtube.com/results?search_query=' . urlencode($query), false, $searchCtx);
    if (!$html) return null;
    if (!preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m)) return null;
    $videoId = $m[1][0];

    $duration = 0;
    $watchCtx = stream_context_create(['http' => [
        'timeout' => 10, 'ignore_errors' => true, 'header' => $headers,
    ]]);
    $watchHtml = @file_get_contents('https://www.youtube.com/watch?v=' . $videoId, false, $watchCtx);
    if ($watchHtml && preg_match('/"lengthSeconds"\s*:\s*"(\d+)"/', $watchHtml, $d)) {
        $duration = intval($d[1]);
    }

    return ['videoId' => $videoId, 'duration' => $duration];
}

function searchYouTubeVideoId($query) {
    $result = searchYouTubeVideo($query);
    return $result ? $result['videoId'] : null;
}

/** Devuelve datos del artista buscado en Spotify: imagen + géneros.
 *  Cachea el resultado como JSON (positivo Y negativo) 7 días.
 *  Estructura: ['image' => ?string, 'genres' => string[]] o null. */
function getSpotifyArtistData(string $name): ?array {
    $name = trim($name);
    if ($name === '') return null;

    /* Cache key normalizado (lowercase). Almacenamos JSON con shape
       `{image, genres}` o sentinel 'NULL' para misses confirmados. */
    $key = 'spotify_artist_data_' . md5(mb_strtolower($name));
    $cached = cacheGet($key);
    if ($cached !== null) {
        if ($cached === 'NULL') return null;
        $decoded = json_decode($cached, true);
        if (is_array($decoded)) return $decoded;
    }

    $token = getSpotifyToken();
    if (!$token) return null;

    $ctx = stream_context_create(['http' => [
        'timeout'       => 8,
        'ignore_errors' => true,
        'header'        => "Authorization: Bearer {$token}",
    ]]);
    $url = 'https://api.spotify.com/v1/search?type=artist&limit=1&q=' . rawurlencode($name);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) {
        /* Fallos transitorios — no cacheamos, reintentamos próxima vez. */
        return null;
    }
    $data = json_decode($raw, true);
    $items = $data['artists']['items'] ?? [];
    if (empty($items[0])) {
        /* Confirmed miss → cacheamos como NULL para no buscar más. */
        cacheSet($key, 'NULL', 7 * 24 * 3600);
        return null;
    }
    $artist = $items[0];
    $result = [
        'image'  => $artist['images'][0]['url'] ?? null,
        'genres' => $artist['genres'] ?? [],
    ];
    cacheSet($key, json_encode($result), 7 * 24 * 3600);
    return $result;
}

/** Wrapper de compatibilidad — solo la imagen. */
function getSpotifyArtistImage(string $name): ?string {
    $d = getSpotifyArtistData($name);
    return $d ? ($d['image'] ?? null) : null;
}
