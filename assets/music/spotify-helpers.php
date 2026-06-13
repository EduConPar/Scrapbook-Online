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

/* ── Helpers de matching YouTube ──
   Compartidos por yt-search-batch (api.php), spotify-track y
   tidal-track para que TODOS los flujos de importación elijan el mejor
   resultado en vez del primero. Score por candidato:
     - Título (40%): similar_text contra el título esperado.
     - Canal (25%): similar_text contra el artista esperado; bonus si
       el canal es *Topic o *VEVO (uploads oficiales del label).
     - Duración (25%): proximidad a la esperada (±15s = ~100, >60s = 0).
     - Posición (10%): orden de YouTube.
   Penalización: -25 por cada "trap word" (karaoke/cover/instrumental/
   remix/live/8-bit/sped up/lyrics video/etc.) presente en el título
   del candidato pero NO en el track original. */

function ytNorm(string $s): string {
    $s = mb_strtolower($s);
    if (class_exists('Transliterator')) {
        $tr = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
        if ($tr) $s = $tr->transliterate($s);
    }
    $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

/** Extrae hasta $max candidatos del HTML de results de YouTube con su
 *  videoId, título, canal y duración (cuando están disponibles).
 *  Filtra YouTube Shorts: vienen envueltos en `reelItemRenderer` o
 *  `shortsLockupViewModel`, mientras que los vídeos normales viven en
 *  `videoRenderer`. Importar Shorts como audios para una playlist es
 *  basura por diseño: son verticales, ~60s, mal masterizados — así que
 *  los descartamos antes de scorear. */
function ytExtractCandidates(string $html, int $max = 7): array {
    $candidates = [];
    if ($html === '') return $candidates;
    if (!preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m, PREG_OFFSET_CAPTURE)) return $candidates;
    $seen = [];
    foreach ($m[1] as $idx => $vidMatch) {
        $vid = $vidMatch[0];
        if (isset($seen[$vid])) continue;
        $seen[$vid] = true;
        $offset = $m[0][$idx][1];
        /* Ventana de contexto hacia atrás (~1.5k chars) para identificar
           el contenedor JSON que envuelve a este videoId. Si el último
           marcador de tipo que aparece antes del match es de Short,
           skip — buscamos el más cercano para no confundirnos con
           contenedores hermanos previos del feed. */
        $back  = substr($html, max(0, $offset - 1500), $offset - max(0, $offset - 1500));
        $posVideo = max(strrpos($back, '"videoRenderer"'),
                        strrpos($back, '"compactVideoRenderer"'),
                        strrpos($back, '"playlistVideoRenderer"'));
        $posShort = max(strrpos($back, '"reelItemRenderer"'),
                        strrpos($back, '"shortsLockupViewModel"'),
                        strrpos($back, '"reelWatchEndpoint"'),
                        strrpos($back, '"shortsShelfRenderer"'),
                        strrpos($back, '"reelShelfRenderer"'));
        if ($posShort !== false && $posShort > $posVideo) continue;
        $snippet = substr($html, $offset, 4000);

        $cand = ['videoId' => $vid, 'pos' => count($candidates)];

        if (preg_match('/"title"\s*:\s*\{[^}]*?"text"\s*:\s*"((?:[^"\\\\]|\\\\.)*?)"/', $snippet, $tm)) {
            $cand['title'] = json_decode('"' . $tm[1] . '"');
        }
        if (preg_match('/"(?:longBylineText|ownerText)"\s*:\s*\{[^}]*?"text"\s*:\s*"((?:[^"\\\\]|\\\\.)*?)"/', $snippet, $cm)) {
            $cand['channel'] = json_decode('"' . $cm[1] . '"');
        }
        if (preg_match('/"lengthText"\s*:\s*\{(?:[^}]*?"accessibility"[^}]*?\})?[^}]*?"simpleText"\s*:\s*"([\d:]+)"/', $snippet, $lm)) {
            $parts = array_map('intval', explode(':', $lm[1]));
            $secs = 0;
            foreach ($parts as $p) { $secs = $secs * 60 + $p; }
            $cand['duration'] = $secs;
        }

        $candidates[] = $cand;
        if (count($candidates) >= $max) break;
    }
    return $candidates;
}

const YT_TRAP_WORDS = [
    'karaoke', 'cover', 'covered by', 'instrumental', 'remix', 'live',
    '8 bit', '8bit', 'sped up', 'slowed', 'reverb', 'reaction',
    'lyrics video', 'lyric video', 'tutorial',
    'piano version', 'guitar version', 'metal version', 'parody',
];

/** Score combinado para un candidato vs el track esperado.
 *  $track: ['title'=>string, 'artist'=>string, 'duration'=>int (seg)] */
function ytScoreCandidate(array $cand, array $track): float {
    $expTitleN  = ytNorm($track['title']  ?? '');
    $expArtistN = ytNorm($track['artist'] ?? '');
    $expDur     = (int)($track['duration'] ?? 0);
    $candTitleN = isset($cand['title'])   ? ytNorm($cand['title'])   : '';
    $candChanN  = isset($cand['channel']) ? ytNorm($cand['channel']) : '';

    $titleScore = 0;
    if ($candTitleN !== '' && $expTitleN !== '') {
        $t = 0.0; similar_text($expTitleN, $candTitleN, $t);
        $titleScore = $t;
        if (str_contains($candTitleN, $expTitleN)) $titleScore = max($titleScore, 95);
    }

    $candChanCleanN = trim(preg_replace('/\b(topic|vevo|official|records|music)\b/i', '', $candChanN));
    $candChanCleanN = preg_replace('/\s+/', ' ', $candChanCleanN);
    $chanScore = 50;
    if ($expArtistN !== '' && $candChanCleanN !== '') {
        $c = 0.0; similar_text($expArtistN, $candChanCleanN, $c);
        $chanScore = $c;
        if (str_contains($candChanCleanN, $expArtistN) || str_contains($expArtistN, $candChanCleanN)) {
            $chanScore = max($chanScore, 85);
        }
        if (preg_match('/\b(topic|vevo)\b/i', $candChanN)) $chanScore = min(100, $chanScore + 10);
    }

    $durScore = 50;
    if ($expDur > 0 && !empty($cand['duration'])) {
        $diff = abs((int)$cand['duration'] - $expDur);
        if      ($diff <= 5)  $durScore = 100;
        else if ($diff <= 15) $durScore = 95;
        else if ($diff <= 60) $durScore = 95 - ($diff - 15) * 1.5;
        else                  $durScore = 0;
    }

    $posScore = max(0, 100 - (int)($cand['pos'] ?? 0) * 14);

    $score = $titleScore * 0.40 + $chanScore * 0.25 + $durScore * 0.25 + $posScore * 0.10;

    foreach (YT_TRAP_WORDS as $w) {
        if ($candTitleN !== '' && str_contains($candTitleN, $w) && !str_contains($expTitleN, $w)) {
            $score -= 25;
        }
    }
    if ($candTitleN !== '' && (str_contains($candTitleN, 'official audio') ||
                                str_contains($candTitleN, 'official music video'))) {
        $score += 5;
    }
    return max(0, min(120, $score));
}

/** Elige el mejor candidato para un track. Devuelve null si ninguno
 *  pasa un score mínimo (por ejemplo: queries vacías o results raros). */
function ytPickBestCandidate(array $candidates, array $track, float $minScore = 35.0): ?array {
    if (!$candidates) return null;
    $best = null; $bestScore = 0.0;
    foreach ($candidates as $c) {
        $sc = ytScoreCandidate($c, $track);
        if ($sc > $bestScore) { $bestScore = $sc; $best = $c; }
    }
    return ($best && $bestScore >= $minScore) ? $best : null;
}

/** Lookup completo para un track concreto: busca en YouTube, scoree
 *  los candidatos y devuelve el mejor con videoId + duration. Usado
 *  por spotify-track y tidal-track. */
function searchYouTubeVideo($query, ?array $track = null) {
    $headers = implode("\r\n", [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept-Language: en-US,en;q=0.9',
    ]);

    $searchCtx = stream_context_create(['http' => [
        'timeout' => 10, 'ignore_errors' => true, 'header' => $headers,
    ]]);
    $html = @file_get_contents('https://www.youtube.com/results?search_query=' . urlencode($query), false, $searchCtx);
    if (!$html) return null;

    /* Si tenemos info del track esperado (título/artista/duración),
       usamos el scoring. Si no, fallback al primer videoId que aparezca
       (compat hacia atrás para llamadas viejas que solo pasaban query). */
    if ($track) {
        $cands = ytExtractCandidates($html, 7);
        $best  = ytPickBestCandidate($cands, $track);
        if (!$best) return null;
        $videoId = $best['videoId'];
        $duration = $best['duration'] ?? 0;
    } else {
        if (!preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m)) return null;
        $videoId = $m[1][0];
        $duration = 0;
    }

    /* Fallback de duración: si el HTML de results no la trajo, vamos al
       watch para sacarla. Skip si ya la tenemos. */
    if (!$duration) {
        $watchCtx = stream_context_create(['http' => [
            'timeout' => 10, 'ignore_errors' => true, 'header' => $headers,
        ]]);
        $watchHtml = @file_get_contents('https://www.youtube.com/watch?v=' . $videoId, false, $watchCtx);
        if ($watchHtml && preg_match('/"lengthSeconds"\s*:\s*"(\d+)"/', $watchHtml, $d)) {
            $duration = intval($d[1]);
        }
    }

    return ['videoId' => $videoId, 'duration' => $duration];
}

function searchYouTubeVideoId($query, ?array $track = null) {
    $result = searchYouTubeVideo($query, $track);
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
