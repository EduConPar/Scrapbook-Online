<?php
/* ──────────────────────────────────────────────────────────────────────
   Deezer Public API helpers
   ──────────────────────────────────────────────────────────────────────
   - Endpoint público, sin auth.
   - Rate-limit oficial: 50 req / 5 s (≈ 10 req/s sostenido).
   - Mejor cobertura que iTunes en música electrónica, francesa,
     europea-no-anglo y k-pop temprano.
   ────────────────────────────────────────────────────────────────────── */

if (!function_exists('deezerSearchAlbumForTrack')) {

/* Mismo contrato que `itunesSearchAlbumForTrack`. Cuando el track tiene
   artista, prueba la query estructurada de Deezer (track:"…" artist:"…")
   primero; si no devuelve nada, cae a búsqueda libre. */
function deezerSearchAlbumForTrack(string $title, string $artist): ?array {
    $title = trim($title);
    if ($title === '') return null;

    $items = [];
    if ($artist !== '') {
        $q = 'track:"' . _deezerEscape($title) . '" artist:"' . _deezerEscape($artist) . '"';
        $items = _deezerSearch($q);
    }
    if (!$items) {
        $q = $artist !== '' ? ($title . ' ' . $artist) : $title;
        $items = _deezerSearch($q);
    }
    if (!$items) return null;

    $titleN  = _deezerNorm($title);
    $artistN = _deezerNorm($artist);
    $best = null; $bestScore = 0.0;
    foreach ($items as $r) {
        if (empty($r['album']['id']))    continue;
        if (empty($r['album']['title'])) continue;
        $tN = _deezerNorm($r['title']             ?? '');
        $aN = _deezerNorm($r['artist']['name']    ?? '');
        $ts = 0.0; if ($titleN  !== '') similar_text($titleN,  $tN, $ts);
        $as = $artistN === '' ? 50.0 : 0.0;
        if ($artistN !== '') {
            similar_text($artistN, $aN, $as);
            if (str_contains($aN, $artistN) || str_contains($artistN, $aN)) {
                $as = max($as, 85.0);
            }
        }
        $score = $ts * 0.6 + $as * 0.4;
        if ($score > $bestScore) { $bestScore = $score; $best = $r; }
    }
    if (!$best || $bestScore < 55) return null;
    $img = (string)($best['album']['cover_xl'] ?? $best['album']['cover_big'] ?? '');
    return [
        'notFound'       => false,
        'source'         => 'deezer',
        'albumKey'       => 'deezer:' . (string)$best['album']['id'],
        'spotifyAlbumId' => '',
        'albumName'      => (string)($best['album']['title'] ?? ''),
        'albumImage'     => $img,
        'albumUrl'       => (string)($best['album']['link'] ?? ''),
        'isSingle'       => false,
        'releaseDate'    => '',
        'matchTitle'     => (string)($best['title']             ?? ''),
        'matchArtist'    => (string)($best['artist']['name']    ?? ''),
        'matchTrackId'   => (string)($best['id']                ?? ''),
        'isSynthetic'    => false,
    ];
}

/* Lista las canciones de un álbum dado su id Deezer.
   Devuelve { name, artist, image, tracks: [...] } o null. */
function deezerGetAlbumTracks(string $albumId): ?array {
    $id = preg_replace('/[^0-9]/', '', $albumId);
    if ($id === '') return null;
    $url = 'https://api.deezer.com/album/' . $id;
    $ctx = stream_context_create(['http' => [
        'timeout'       => 8,
        'ignore_errors' => true,
        'header'        => "User-Agent: MelonHub/1.0\r\n",
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!is_array($data) || isset($data['error'])) return null;
    $tracks = [];
    foreach (($data['tracks']['data'] ?? []) as $t) {
        $tracks[] = [
            'title'    => (string)($t['title']           ?? ''),
            'artist'   => (string)($t['artist']['name']  ?? ($data['artist']['name'] ?? '')),
            'duration' => (int)($t['duration']           ?? 0),
        ];
    }
    return [
        'name'   => (string)($data['title']          ?? ''),
        'artist' => (string)($data['artist']['name'] ?? ''),
        'image'  => (string)($data['cover_xl']       ?? ($data['cover_big'] ?? '')),
        'tracks' => $tracks,
    ];
}

function _deezerSearch(string $q): array {
    $url = 'https://api.deezer.com/search/track?q=' . rawurlencode($q) . '&limit=10';
    $ctx = stream_context_create(['http' => [
        'timeout'       => 6,
        'ignore_errors' => true,
        'header'        => "User-Agent: MelonHub/1.0\r\n",
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return [];
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['data'])) return [];
    return $data['data'];
}

function _deezerEscape(string $s): string {
    return str_replace('"', '\\"', $s);
}

function _deezerNorm(string $s): string {
    $s = mb_strtolower($s);
    if (class_exists('Transliterator')) {
        $tr = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
        if ($tr) $s = $tr->transliterate($s);
    }
    $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

} // if !function_exists
