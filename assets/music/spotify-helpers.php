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

/** Normaliza una cadena para comparaciones case/diacritic/symbol-insensitive.
 *  "Bad Bunny (feat. X)" → "badbunnyfeatx". */
function _normalizeMusicStr(string $s): string {
    $s = mb_strtolower(trim($s));
    /* Translitera diacríticos (á→a, ñ→n…). */
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) $s = $t;
    }
    /* Quita TODO lo que no sea letra/número. */
    $s = preg_replace('/[^a-z0-9]+/i', '', $s) ?? '';
    return $s;
}

/** Devuelve datos del artista buscado en Spotify: imagen + géneros.
 *  Cachea el resultado como JSON (positivo Y negativo) 7 días.
 *
 *  Verificación contra ambigüedades de nombre:
 *    1. Pide HASTA 10 candidatos (no solo el primero).
 *    2. Filtra por nombre EXACTO normalizado (lowercase + sin
 *       diacríticos + sin símbolos). Si solo uno coincide → ese gana.
 *    3. Si quedan varios, y se pasan `$verificationTitles` (canciones
 *       que el usuario ha escuchado de ESE artista), pide top-tracks
 *       de cada candidato y cuenta cuántos títulos solapan. Mayor
 *       score → mejor match.
 *    4. Fallback: primer candidato Spotify (mismo comportamiento de antes).
 *
 *  La cache-key incluye un hash de los titles de verificación para
 *  que distintos sets de canciones no se pisen entre sí.
 *
 *  Estructura devuelta: ['image' => ?string, 'genres' => string[]] o null. */
function getSpotifyArtistData(string $name, array $verificationTitles = []): ?array {
    $name = trim($name);
    if ($name === '') return null;

    /* Cache key normalizado + suffix con verification para no pisar
       entradas de distintos usuarios con artistas homónimos. */
    $verifySuffix = '';
    if (!empty($verificationTitles)) {
        $sorted = array_map('_normalizeMusicStr', $verificationTitles);
        sort($sorted);
        $verifySuffix = ':v=' . md5(implode('|', $sorted));
    }
    $key = 'spotify_artist_data_' . md5(mb_strtolower($name)) . $verifySuffix;
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
    $url = 'https://api.spotify.com/v1/search?type=artist&limit=10&q=' . rawurlencode($name);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) {
        /* Fallos transitorios — no cacheamos, reintentamos próxima vez. */
        return null;
    }
    $data = json_decode($raw, true);
    $items = $data['artists']['items'] ?? [];
    if (empty($items)) {
        cacheSet($key, 'NULL', 7 * 24 * 3600);
        return null;
    }

    /* PASO 1: filtrar por nombre EXACTO normalizado.
       Si solo uno coincide, ese gana sin más checks. */
    $needle = _normalizeMusicStr($name);
    $exact = array_values(array_filter($items, function($it) use ($needle) {
        return _normalizeMusicStr($it['name'] ?? '') === $needle;
    }));
    $candidates = !empty($exact) ? $exact : $items;

    $best = $candidates[0];

    /* PASO 2: si hay múltiples candidatos Y tenemos títulos para
       verificar, pedimos top-tracks de cada candidato (máx 5) y
       contamos solapamientos con los títulos del usuario. */
    if (count($candidates) > 1 && !empty($verificationTitles)) {
        $userTitlesNorm = array_map('_normalizeMusicStr', $verificationTitles);
        $userTitlesNorm = array_filter($userTitlesNorm);   // descarta vacíos
        if (!empty($userTitlesNorm)) {
            $bestScore = -1;
            foreach (array_slice($candidates, 0, 5) as $cand) {
                $artistId = $cand['id'] ?? '';
                if (!$artistId) continue;
                $topUrl = 'https://api.spotify.com/v1/artists/' . rawurlencode($artistId) . '/top-tracks?market=US';
                $topRaw = @file_get_contents($topUrl, false, $ctx);
                if (!$topRaw) continue;
                $topData = json_decode($topRaw, true);
                $topTracks = $topData['tracks'] ?? [];
                $score = 0;
                foreach ($topTracks as $tr) {
                    $trNorm = _normalizeMusicStr($tr['name'] ?? '');
                    if ($trNorm === '') continue;
                    foreach ($userTitlesNorm as $ut) {
                        /* Match exacto o subcadena (cubre "Title (Remastered)" vs "Title"). */
                        if ($trNorm === $ut
                            || str_contains($trNorm, $ut)
                            || str_contains($ut, $trNorm)) {
                            $score++;
                            break;
                        }
                    }
                }
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $cand;
                }
            }
        }
    }

    $result = [
        'image'  => $best['images'][0]['url'] ?? null,
        'genres' => $best['genres'] ?? [],
    ];
    cacheSet($key, json_encode($result), 7 * 24 * 3600);
    return $result;
}

/** Wrapper de compatibilidad — solo la imagen.
 *  Acepta opcionalmente `$verificationTitles` para que la imagen vuelva
 *  al artista correcto en caso de homónimos. */
function getSpotifyArtistImage(string $name, array $verificationTitles = []): ?string {
    $d = getSpotifyArtistData($name, $verificationTitles);
    return $d ? ($d['image'] ?? null) : null;
}
