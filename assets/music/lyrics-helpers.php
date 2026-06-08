<?php
/* ════════════════════════════════════════════════════════════════
   LRCLIB — letras de canciones, gratis, sin key.
   API:  https://lrclib.net/docs

   getLyrics($title, $artist, $duration = 0): ?array
     - 1º intenta GET exacto (con duración si hay).
     - 2º fallback a SEARCH fuzzy.
     - Devuelve ['plain' => string|null, 'synced' => string|null]
       o null si no se encontró nada.
     - Cachea 30 días (incluye misses confirmados).
═══════════════════════════════════════════════════════════════════ */
require_once dirname(__DIR__) . '/cache-helpers.php';

function _lrclibFetch(string $url): ?array {
    $ctx = stream_context_create(['http' => [
        'timeout'       => 6,
        'ignore_errors' => true,
        'header'        => "User-Agent: MelonHub/1.0 (+https://melonhub.com)\r\n",
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    /* Capturamos el status code del HTTP. */
    $code = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) $code = (int)$m[1];
    }
    if ($code !== 200) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function getLyrics(string $title, string $artist, int $duration = 0): ?array {
    $title  = trim($title);
    $artist = trim($artist);
    if ($title === '') return null;

    $cacheKey = 'lyrics_' . md5(mb_strtolower($title . '|' . $artist . '|' . $duration));
    $cached = cacheGet($cacheKey);
    if ($cached !== null) {
        if ($cached === 'NULL') return null;
        $decoded = json_decode($cached, true);
        if (is_array($decoded)) return $decoded;
    }

    /* PASO 1: GET exacto. LRCLIB matchea title+artist+album+duration
       y devuelve un solo resultado. Sin album+duración el endpoint
       /get falla, así que solo lo usamos si tenemos duración. */
    $hit = null;
    if ($duration > 0 && $artist !== '') {
        $url = 'https://lrclib.net/api/get?' . http_build_query([
            'track_name'  => $title,
            'artist_name' => $artist,
            'duration'    => $duration,
        ]);
        $hit = _lrclibFetch($url);
    }

    /* PASO 2: SEARCH fuzzy. Si el GET falló o no había duración,
       buscamos y nos quedamos con el primer resultado que tenga
       letras (plainLyrics o syncedLyrics). */
    if (!$hit) {
        $qs = ['track_name' => $title];
        if ($artist !== '') $qs['artist_name'] = $artist;
        $url = 'https://lrclib.net/api/search?' . http_build_query($qs);
        $results = _lrclibFetch($url);
        if (is_array($results) && !empty($results)) {
            foreach ($results as $r) {
                if (!empty($r['syncedLyrics']) || !empty($r['plainLyrics'])) {
                    $hit = $r;
                    break;
                }
            }
        }
    }

    if (!$hit) {
        /* Miss confirmado — cacheamos NULL 30 días. */
        cacheSet($cacheKey, 'NULL', 30 * 24 * 3600);
        return null;
    }

    $result = [
        'plain'  => $hit['plainLyrics']  ?? null,
        'synced' => $hit['syncedLyrics'] ?? null,
        'matched_title'  => $hit['trackName']  ?? $title,
        'matched_artist' => $hit['artistName'] ?? $artist,
    ];
    /* Sin letras útiles → tratar como miss. */
    if ($result['plain'] === null && $result['synced'] === null) {
        cacheSet($cacheKey, 'NULL', 30 * 24 * 3600);
        return null;
    }
    cacheSet($cacheKey, json_encode($result), 30 * 24 * 3600);
    return $result;
}
