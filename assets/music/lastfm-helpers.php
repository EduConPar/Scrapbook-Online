<?php
/* ──────────────────────────────────────────────
   LAST.FM — extracción de géneros por CANCIÓN
   ──────────────────────────────────────────────
   Spotify no expone géneros a nivel de track (solo de artista),
   así que usamos Last.fm `track.getTopTags` que devuelve tags
   crowdsourced por canción específica.

   API key gratis: https://www.last.fm/api/account/create
   Sin key, el wrapped sigue funcionando con géneros de artista
   (Spotify). */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/cache-helpers.php';

/* Whitelist de "stopwords" — tags que la gente añade a Last.fm pero
   que NO son géneros (estados de ánimo opcionales, comentarios, etc.).
   Más restrictivo = géneros más limpios pero menos datos. */
const LASTFM_TAG_STOPWORDS = [
    'favorites', 'favorite', 'favourites', 'favourite',
    'amazing', 'awesome', 'beautiful', 'cool', 'great', 'love',
    'songs', 'song', 'music', 'tracks', 'track',
    'seen live', 'spotify', 'soundtrack',
    'best', 'good', 'good music', 'masterpiece',
    'covers', 'cover',
    'female vocalists', 'male vocalists', 'female vocalist', 'male vocalist',
    'instrumental', 'vocal',
    'english', 'spanish', 'japanese', 'korean', 'french',
    'usa', 'uk', 'japan',
    '00s', '10s', '20s', '60s', '70s', '80s', '90s',
    '2000s', '2010s', '2020s',
    'mp3', 'youtube',
];

/** Devuelve hasta `$limit` géneros para la canción (artist + title).
 *  Tags se ordenan por `count` (popularidad) y se filtran por stopwords.
 *  Cachea 7 días — los tags de Last.fm rara vez cambian. */
function getLastFmTrackGenres(string $artist, string $title, int $limit = 5): array {
    if (LASTFM_API_KEY === '' || $artist === '' || $title === '') return [];

    $key = 'lastfm_tags_' . md5(mb_strtolower($artist . '|' . $title));
    $cached = cacheGet($key);
    if ($cached !== null) {
        $decoded = json_decode($cached, true);
        return is_array($decoded) ? $decoded : [];
    }

    $url = 'http://ws.audioscrobbler.com/2.0/?method=track.gettoptags'
         . '&artist='  . rawurlencode($artist)
         . '&track='   . rawurlencode($title)
         . '&api_key=' . rawurlencode(LASTFM_API_KEY)
         . '&format=json'
         . '&autocorrect=1';
    $ctx = stream_context_create(['http' => [
        'timeout'       => 8,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return [];

    $data = json_decode($raw, true);
    /* Last.fm a veces devuelve `tag` como objeto cuando solo hay uno;
       normalizamos a array. */
    $tags = $data['toptags']['tag'] ?? [];
    if (isset($tags['name'])) $tags = [$tags];

    /* Ordenamos por count DESC (la API ya lo hace pero por seguridad). */
    usort($tags, fn($a, $b) => ($b['count'] ?? 0) - ($a['count'] ?? 0));

    $genres = [];
    foreach ($tags as $t) {
        $name = mb_strtolower(trim((string)($t['name'] ?? '')));
        if ($name === '') continue;
        if (in_array($name, LASTFM_TAG_STOPWORDS, true)) continue;
        /* Filtra años en formato "2024", nombres de artista (heurística:
           si contiene el nombre del artista lo saltamos). */
        if (preg_match('/^[0-9]{4}$/', $name)) continue;
        if (mb_stripos($name, mb_strtolower($artist)) !== false) continue;
        $genres[] = $name;
        if (count($genres) >= $limit) break;
    }
    cacheSet($key, json_encode($genres), 7 * 24 * 3600);
    return $genres;
}
