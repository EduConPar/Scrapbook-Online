<?php
/* ──────────────────────────────────────────────────────────
   TIDAL HELPERS — API oficial (developer.tidal.com)
   ──────────────────────────────────────────────────────────
   Flujo "client credentials": permite leer catálogo y playlists
   públicas. Igual que Spotify, Tidal solo aporta METADATOS
   (título / artista / duración); la reproducción se resuelve a
   YouTube con searchYouTubeVideo() de spotify-helpers.php.

   API v2 → formato JSON:API (data/attributes/included/relationships).
   El token se cachea en SQL (app_cache) vía cache-helpers.php.
   ────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/cache-helpers.php';

/* Token de aplicación (client credentials), cacheado en SQL. */
function getTidalToken() {
    $tok = cacheGet('tidal_app_token');
    if ($tok !== null) return $tok;
    if (!TIDAL_CLIENT_ID || !TIDAL_CLIENT_SECRET) return null;

    $creds = base64_encode(TIDAL_CLIENT_ID . ':' . TIDAL_CLIENT_SECRET);
    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Authorization: Basic {$creds}\r\nContent-Type: application/x-www-form-urlencoded",
        'content'       => 'grant_type=client_credentials',
        'timeout'       => 10,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents('https://auth.tidal.com/v1/oauth2/token', false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!isset($data['access_token'])) return null;
    cacheSet('tidal_app_token', $data['access_token'], (int)($data['expires_in'] ?? 3600) - 60);
    return $data['access_token'];
}

/* GET a una URL absoluta de la API v2 con el bearer. Devuelve el array
   decodificado o null. */
function tidalApiGetUrl($url) {
    $token = getTidalToken();
    if (!$token) return null;
    $ctx = stream_context_create(['http' => [
        'method'        => 'GET',
        'header'        => "Authorization: Bearer {$token}\r\nAccept: application/vnd.api+json",
        'timeout'       => 15,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    return json_decode($raw, true);
}

/* GET a un endpoint v2 por path + params (añade countryCode por defecto). */
function tidalApiGet($path, $params = []) {
    if (!isset($params['countryCode'])) $params['countryCode'] = TIDAL_COUNTRY ?: 'US';
    $url = 'https://openapi.tidal.com/v2/' . ltrim($path, '/') . '?' . http_build_query($params);
    return tidalApiGetUrl($url);
}

/* Duración ISO-8601 (PT3M20S) → segundos. Acepta también enteros. */
function tidalParseDuration($v) {
    if (is_int($v)) return max(0, $v);
    if (!is_string($v) || $v === '') return 0;
    if (ctype_digit($v)) return (int)$v;
    if (preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/i', $v, $m)) {
        return (int)($m[1] ?? 0) * 3600 + (int)($m[2] ?? 0) * 60 + (int)($m[3] ?? 0);
    }
    return 0;
}

/* Extrae el ID de track (numérico) de una URL de Tidal o del propio ID. */
function parseTidalTrackId($url) {
    $url = trim($url);
    if (preg_match('#tidal\.com/(?:browse/)?track/(\d+)#i', $url, $m)) return $m[1];
    if (ctype_digit($url)) return $url;
    return null;
}

/* Extrae el ID de playlist (UUID) de una URL de Tidal o del propio ID. */
function parseTidalPlaylistId($url) {
    $url = trim($url);
    if (preg_match('#tidal\.com/(?:browse/)?playlist/([0-9a-fA-F-]{8,})#i', $url, $m)) return $m[1];
    if (preg_match('/^[0-9a-fA-F-]{8,}$/', $url)) return $url;
    return null;
}

/* Indexa el array "included" de un documento JSON:API por "type/id". */
function tidalIndexIncluded($doc) {
    $inc = [];
    if (isset($doc['included']) && is_array($doc['included'])) {
        foreach ($doc['included'] as $r) {
            if (isset($r['type'], $r['id'])) $inc[$r['type'] . '/' . $r['id']] = $r;
        }
    }
    return $inc;
}

/* Nombre del primer artista de un recurso "track" usando el mapa included. */
function tidalArtistFromTrack($trackRes, $inc) {
    $rel = $trackRes['relationships']['artists']['data'] ?? null;
    if (is_array($rel)) {
        foreach ($rel as $ref) {
            $key = ($ref['type'] ?? 'artists') . '/' . ($ref['id'] ?? '');
            if (isset($inc[$key]['attributes']['name'])) return $inc[$key]['attributes']['name'];
        }
    }
    return '';
}

/* Una canción: ['title','artist','duration'] o null. */
function tidalGetTrack($trackId) {
    $doc = tidalApiGet('tracks/' . rawurlencode($trackId), ['include' => 'artists']);
    if (!isset($doc['data']['attributes'])) return null;
    $attr = $doc['data']['attributes'];
    $title = $attr['title'] ?? '';
    if ($title === '') return null;
    $inc = tidalIndexIncluded($doc);
    return [
        'title'    => $title,
        'artist'   => tidalArtistFromTrack($doc['data'], $inc),
        'duration' => tidalParseDuration($attr['duration'] ?? 0),
    ];
}

/* Una playlist pública: ['name', 'tracks'=>[ ['title','artist','duration'], ... ]]
   o null. Recorre los items paginando por links.next. Los videoId se
   resuelven luego en YouTube (yt-search-batch), igual que el CSV de Spotify. */
function tidalGetPlaylist($playlistId, $maxTracks = 300) {
    /* Nombre de la playlist */
    $meta = tidalApiGet('playlists/' . rawurlencode($playlistId), []);
    $pAttr = $meta['data']['attributes'] ?? [];
    $name  = $pAttr['name'] ?? ($pAttr['title'] ?? 'Tidal playlist');
    if ($meta === null) return null;   // sin acceso → fallo claro

    /* Items (paginado). include=items.artists trae también los artistas. */
    $base = 'https://openapi.tidal.com/v2/playlists/' . rawurlencode($playlistId)
          . '/relationships/items?' . http_build_query([
                'countryCode' => TIDAL_COUNTRY ?: 'US',
                'include'     => 'items.artists',
            ]);

    $tracks = [];
    $url    = $base;
    $pages  = 0;
    while ($url && $pages < 40 && count($tracks) < $maxTracks) {
        $pages++;
        $doc = tidalApiGetUrl($url);
        if (!$doc) break;
        $inc = tidalIndexIncluded($doc);

        /* data = lista ordenada de refs a tracks */
        $refs = (isset($doc['data']) && is_array($doc['data'])) ? $doc['data'] : [];
        foreach ($refs as $ref) {
            if (count($tracks) >= $maxTracks) break;
            if (($ref['type'] ?? '') !== 'tracks') continue;
            $key = 'tracks/' . ($ref['id'] ?? '');
            $res = $inc[$key] ?? null;
            if (!$res || !isset($res['attributes']['title'])) continue;
            $tracks[] = [
                'title'    => $res['attributes']['title'],
                'artist'   => tidalArtistFromTrack($res, $inc),
                'duration' => tidalParseDuration($res['attributes']['duration'] ?? 0),
            ];
        }

        /* Siguiente página (links.next puede ser relativa) */
        $next = $doc['links']['next'] ?? null;
        if (!$next) { $url = null; break; }
        $url = (strpos($next, 'http') === 0) ? $next : ('https://openapi.tidal.com' . $next);
    }

    return ['name' => $name, 'tracks' => $tracks];
}
