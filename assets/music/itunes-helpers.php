<?php
/* ──────────────────────────────────────────────────────────────────────
   iTunes Search API helpers
   ──────────────────────────────────────────────────────────────────────
   - Endpoint público, sin auth, sin API key.
   - Sin rate-limit documentado; tolera ~20 req/s antes de quejarse.
   - Cobertura altísima en pop/rock/hip-hop occidental (Apple Music es
     una de las bibliotecas más grandes).
   ────────────────────────────────────────────────────────────────────── */

if (!function_exists('itunesSearchAlbumForTrack')) {

/* Devuelve el álbum del MEJOR match para (title, artist) o null si nada
   pasa el threshold. La estructura es la misma que usa find-album al
   final (pero con `source: 'itunes'` y `albumKey: 'itunes:<collectionId>'`). */
function itunesSearchAlbumForTrack(string $title, string $artist): ?array {
    $title = trim($title);
    if ($title === '') return null;
    $q = $artist !== '' ? ($title . ' ' . $artist) : $title;
    $url = 'https://itunes.apple.com/search?term=' . rawurlencode($q)
         . '&entity=song&limit=10&country=US';
    $ctx = stream_context_create(['http' => [
        'timeout'       => 6,
        'ignore_errors' => true,
        'header'        => "User-Agent: MelonHub/1.0\r\n",
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['results'])) return null;

    $titleN  = _itunesNorm($title);
    $artistN = _itunesNorm($artist);
    $best = null; $bestScore = 0.0;
    foreach ($data['results'] as $r) {
        if (($r['kind'] ?? '') !== 'song')   continue;
        if (empty($r['collectionId']))       continue;
        if (empty($r['collectionName']))     continue;
        $tN = _itunesNorm($r['trackName']  ?? '');
        $aN = _itunesNorm($r['artistName'] ?? '');
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
    $img = (string)($best['artworkUrl100'] ?? '');
    /* iTunes devuelve 100x100; pedimos 600x600 sustituyendo en la URL. */
    if ($img) $img = str_replace('100x100', '600x600', $img);
    return [
        'notFound'       => false,
        'source'         => 'itunes',
        'albumKey'       => 'itunes:' . (string)$best['collectionId'],
        'spotifyAlbumId' => '', /* legacy field, vacío cuando no es Spotify */
        'albumName'      => (string)($best['collectionName'] ?? ''),
        'albumImage'     => $img,
        'albumUrl'       => (string)($best['collectionViewUrl'] ?? ''),
        'isSingle'       => (int)($best['trackCount'] ?? 0) === 1,
        'releaseDate'    => (string)($best['releaseDate'] ?? ''),
        'matchTitle'     => (string)($best['trackName']  ?? ''),
        'matchArtist'    => (string)($best['artistName'] ?? ''),
        'matchTrackId'   => (string)($best['trackId']    ?? ''),
        'isSynthetic'    => false,
    ];
}

/* Lista las canciones de un álbum dado su collectionId.
   Devuelve { name, artist, image, tracks: [{title, artist, duration}] }
   o null si no se pudo. */
function itunesGetAlbumTracks(string $collectionId): ?array {
    $id = preg_replace('/[^0-9]/', '', $collectionId);
    if ($id === '') return null;
    $ctx = stream_context_create(['http' => [
        'timeout'       => 7,
        'ignore_errors' => true,
        'header'        => "User-Agent: MelonHub/1.0\r\n",
    ]]);
    /* Los IDs de iTunes son globales, pero las CANCIONES de un álbum solo
       las devuelve la tienda de su región. Muchos álbumes (p.ej. de
       artistas japoneses como Masayoshi Takanaka) salen en el catálogo
       pero la tienda US no lista sus temas → la tracklist venía vacía.
       Probamos US (por defecto) y luego JP/GB/DE; nos quedamos con la
       primera tienda que SÍ traiga pistas. Si ninguna trae pistas pero el
       álbum existe, devolvemos la colección (nombre/artista/portada) para
       que el caller pueda reintentar por nombre en otra fuente. */
    $albumOnly = null;
    foreach (['', 'JP', 'GB', 'DE'] as $store) {
        $url = 'https://itunes.apple.com/lookup?id=' . $id . '&entity=song&limit=200'
             . ($store !== '' ? '&country=' . $store : '');
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) continue;
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['results'])) continue;
        $album = null;
        $tracks = [];
        foreach ($data['results'] as $r) {
            $wt = $r['wrapperType'] ?? '';
            if ($wt === 'collection' && !$album) {
                $img = (string)($r['artworkUrl100'] ?? '');
                if ($img) $img = str_replace('100x100', '600x600', $img);
                $album = [
                    'name'   => (string)($r['collectionName'] ?? ''),
                    'artist' => (string)($r['artistName']     ?? ''),
                    'image'  => $img,
                ];
            } elseif ($wt === 'track' && ($r['kind'] ?? '') === 'song') {
                $tracks[] = [
                    'title'    => (string)($r['trackName']  ?? ''),
                    'artist'   => (string)($r['artistName'] ?? ''),
                    'duration' => isset($r['trackTimeMillis']) ? (int)round($r['trackTimeMillis'] / 1000) : 0,
                ];
            }
        }
        if ($album && $tracks) {
            /* iTunes preserva el orden — los results vienen track 1, 2... */
            $album['tracks'] = $tracks;
            return $album;
        }
        if ($album && !$albumOnly) { $album['tracks'] = []; $albumOnly = $album; }
    }
    return $albumOnly;
}

/* Normalización para comparación: lowercase, strip diacritics + ruido. */
function _itunesNorm(string $s): string {
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
