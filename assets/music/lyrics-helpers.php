<?php
/* ════════════════════════════════════════════════════════════════
   LRCLIB — letras de canciones, gratis, sin key.
   API:  https://lrclib.net/docs

   getLyrics($title, $artist, $duration = 0): ?array
     Estrategia multi-pasada para maximizar el hit rate:
     1) GET exacto con título y artista originales.
     2) GET exacto con título/artista LIMPIOS (sin "feat.", "Remix", etc).
     3) SEARCH con varias combinaciones (original, limpio, sin artista).
     4) Scoring de candidatos: prefiere synced lyrics, duración cercana,
        match exacto de título/artista.
     - Devuelve ['plain' => string|null, 'synced' => string|null]
       o null si no se encontró nada.
     - Cachea 30 días los hits, 24h los misses.
═══════════════════════════════════════════════════════════════════ */
require_once dirname(__DIR__) . '/cache-helpers.php';

/* Debug log file — escribe cada paso del search para diagnosticar.
   Comentar/quitar tras debugging. */
function _lrclibLog(string $msg): void {
    $logFile = dirname(__DIR__, 2) . '/logs/lyrics-server.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (file_exists($logFile) && filesize($logFile) > 512 * 1024) @rename($logFile, $logFile . '.1');
    $stamp = date('Y-m-d H:i:s.') . substr(microtime(false), 2, 3);
    @file_put_contents($logFile, "[$stamp] $msg\n", FILE_APPEND | LOCK_EX);
}

/* Aplica los opts comunes a un cURL handle individual. */
function _lrclibApplyCurlOpts($ch, string $url): void {
    curl_setopt_array($ch, [
        CURLOPT_URL             => $url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_MAXREDIRS       => 3,
        CURLOPT_CONNECTTIMEOUT  => 4,    /* connect rápido */
        CURLOPT_TIMEOUT         => 15,   /* total request — LRCLIB a veces tarda 6-10s */
        CURLOPT_USERAGENT       => 'MelonHub/1.0 (+https://melonhub.com)',
        CURLOPT_HTTPHEADER      => ['Accept: application/json'],
        CURLOPT_ENCODING        => '',                /* gzip/deflate auto */
        CURLOPT_SSL_VERIFYPEER  => false,             /* XAMPP a menudo no tiene CA bundle */
        CURLOPT_SSL_VERIFYHOST  => 0,
        CURLOPT_TCP_KEEPALIVE   => 1,                 /* keepalive entre calls secuenciales */
        CURLOPT_TCP_KEEPIDLE    => 60,
    ]);
}

/* Dispara N requests en PARALELO con curl_multi y devuelve sus
   respuestas. $urls = ['key1' => url1, 'key2' => url2, ...].
   Devuelve ['key1' => arrayParsed|null, ...].
   Total wall time = max(individual_times) ≈ 300-600ms para todo
   el lote, en lugar de N × 300-600ms sequential. */
function _lrclibFetchParallel(array $urls): array {
    if (empty($urls)) return [];
    if (!function_exists('curl_multi_init')) {
        /* Fallback secuencial si curl_multi no disponible. */
        $out = [];
        foreach ($urls as $key => $url) $out[$key] = _lrclibFetch($url);
        return $out;
    }
    $mh = curl_multi_init();
    $handles = [];
    foreach ($urls as $key => $url) {
        $ch = curl_init();
        _lrclibApplyCurlOpts($ch, $url);
        curl_multi_add_handle($mh, $ch);
        $handles[$key] = $ch;
    }
    $running = null;
    do {
        $status = curl_multi_exec($mh, $running);
        if ($running) curl_multi_select($mh, 0.5);
    } while ($running && $status === CURLM_OK);
    $out = [];
    foreach ($handles as $key => $ch) {
        $body  = curl_multi_getcontent($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $time  = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $errno = curl_errno($ch);
        if ($errno) {
            _lrclibLog("    [$key] curl-error [$errno] " . curl_error($ch) . " ({$time}s)");
            $out[$key] = null;
        } elseif ($code !== 200) {
            _lrclibLog("    [$key] http $code ({$time}s)");
            $out[$key] = null;
        } else {
            $data = json_decode($body, true);
            if (!is_array($data)) {
                _lrclibLog("    [$key] json-decode failed ({$time}s)");
                $out[$key] = null;
            } else {
                $cnt = isset($data[0]) ? count($data) : 1;
                _lrclibLog("    [$key] ok ({$time}s) → $cnt result(s)");
                $out[$key] = $data;
            }
        }
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    return $out;
}

function _lrclibFetch(string $url): ?array {
    /* cURL en lugar de file_get_contents — file_get_contents() con
       HTTPS streams en algunos servidores (sobre todo XAMPP/macOS)
       hace SSL handshake lento o falla silencioso. cURL maneja
       HTTPS de forma fiable, soporta timeouts más granulares. */
    if (!function_exists('curl_init')) {
        _lrclibLog("FETCH ERROR: cURL extension not loaded, falling back to file_get_contents");
        $ctx = stream_context_create(['http' => [
            'timeout'       => 6,
            'ignore_errors' => true,
            'header'        => "User-Agent: MelonHub/1.0\r\n",
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) return null;
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
    $ch = curl_init();
    _lrclibApplyCurlOpts($ch, $url);
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    $errno = curl_errno($ch);
    $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    if ($errno) {
        _lrclibLog("    FETCH curl-error: [$errno] $err (took {$time}s)");
        return null;
    }
    if ($code !== 200) {
        _lrclibLog("    FETCH http $code (took {$time}s)");
        return null;
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        _lrclibLog("    FETCH json-decode failed (raw=" . substr($raw, 0, 200) . ")");
        return null;
    }
    return $data;
}

/* Limpia el título de noise común que YouTube/Spotify añaden y que
   LRCLIB normalmente no incluye:
     - (feat. X), (ft. X), (with X)
     - (Remix), (Live), (Acoustic), (Cover), (Karaoke)
     - (Official Video), (Lyrics), (Remastered 2011), etc.
     - [HD], [4K], [Official], etc.
     - "- single", "- feat. X" al final. */
function _lrclibCleanTitle(string $title): string {
    $t = $title;
    /* Quita lo que sigue tras un "- " separador (común en titles tipo
       "Song Name - Remix" o "Song - feat. Artist"). */
    $t = preg_replace('/\s*-\s*(?:feat\.|ft\.|with|remix|live|acoustic|version|official|remaster(?:ed)?|cover|lyrics?|single).*$/iu', '', $t);
    /* Quita paréntesis con noise dentro. */
    $t = preg_replace('/\s*\((?:feat\.?|ft\.?|with|w\/)\s+[^)]+\)/iu', '', $t);
    $t = preg_replace('/\s*\((?:official\s+\w+|music\s+video|lyric(?:s)?\s+video|audio|remix|live|acoustic|version|cover|karaoke|remaster(?:ed)?|extended|radio\s+edit|edit|instrumental|hd|4k|hq)[^)]*\)/iu', '', $t);
    /* Quita brackets [...] enteros (típicamente "[Official Video]"). */
    $t = preg_replace('/\s*\[[^\]]*\]/u', '', $t);
    /* Colapsa espacios. */
    $t = preg_replace('/\s+/u', ' ', $t);
    return trim($t);
}

/* Limpia el artista: si vienen varios separados por ; , & feat. ft. with,
   nos quedamos con el primero (LRCLIB suele guardar solo el principal). */
function _lrclibCleanArtist(string $artist): string {
    if ($artist === '') return '';
    /* Match: primer artista, hasta el primer separador. */
    if (preg_match('/^\s*([^;,&]+?)(?:\s*(?:[;,&]|\bfeat\.?\b|\bft\.?\b|\bwith\b).*)?$/iu', $artist, $m)) {
        return trim($m[1]);
    }
    return trim($artist);
}

/* Asigna score a un resultado de LRCLIB para elegir el mejor entre
   varias búsquedas. Prefiere:
     - Synced lyrics (vs plain only).
     - Duración cercana (si el cliente envió una).
     - Match exacto de title/artist normalizado. */
function _lrclibScoreResult(array $r, string $wantedTitle, string $wantedArtist, int $wantedDur): int {
    $score = 0;
    if (!empty($r['syncedLyrics'])) $score += 100;
    if (!empty($r['plainLyrics']))  $score += 25;
    if (!empty($r['instrumental'])) $score -= 50;  /* descartar instrumentales. */
    /* Duración cercana → mejor match probable. */
    $rDur = (int)($r['duration'] ?? 0);
    if ($wantedDur > 0 && $rDur > 0) {
        $diff = abs($rDur - $wantedDur);
        if ($diff <= 2)     $score += 60;
        elseif ($diff <= 5) $score += 40;
        elseif ($diff <= 10) $score += 20;
        elseif ($diff <= 20) $score += 5;
        else                 $score -= 10;
    }
    /* Title match normalizado. */
    $rTitle = mb_strtolower(trim($r['trackName'] ?? $r['name'] ?? ''));
    $wTitle = mb_strtolower(trim($wantedTitle));
    if ($wTitle !== '' && $rTitle !== '') {
        if ($rTitle === $wTitle) $score += 60;
        elseif (str_contains($rTitle, $wTitle) || str_contains($wTitle, $rTitle)) $score += 30;
        else {
            /* Similitud via similar_text (cara pero útil para casos borderline). */
            similar_text($rTitle, $wTitle, $pct);
            if ($pct > 80) $score += 25;
            elseif ($pct > 60) $score += 10;
        }
    }
    /* Artist match normalizado. */
    $rArtist = mb_strtolower(trim($r['artistName'] ?? ''));
    $wArtist = mb_strtolower(trim($wantedArtist));
    if ($wArtist !== '' && $rArtist !== '') {
        if ($rArtist === $wArtist) $score += 40;
        elseif (str_contains($rArtist, $wArtist) || str_contains($wArtist, $rArtist)) $score += 25;
        else {
            similar_text($rArtist, $wArtist, $pct);
            if ($pct > 80) $score += 15;
        }
    }
    return $score;
}

function getLyrics(string $title, string $artist, int $duration = 0): ?array {
    $title  = trim($title);
    $artist = trim($artist);
    if ($title === '') return null;

    /* Cache key SIN duración. */
    $cacheKey = 'lyrics_' . md5(mb_strtolower($title . '|' . $artist));
    $cached = cacheGet($cacheKey);
    if ($cached !== null) {
        if ($cached === 'NULL') return null;
        $decoded = json_decode($cached, true);
        if (is_array($decoded)) return $decoded;
    }

    $titleClean    = _lrclibCleanTitle($title);
    $artistClean   = _lrclibCleanArtist($artist);
    $titleNoParens = trim(preg_replace('/\s*\([^)]*\)/u', '', $title));
    $titleNoParens = trim(preg_replace('/\s+/u', ' ', $titleNoParens));

    $tStart = microtime(true);
    _lrclibLog("getLyrics: title='$title' artist='$artist' dur=$duration");

    /* ═══ ESTRATEGIA: SECUENCIAL con EARLY-EXIT AGRESIVO ═══
       LRCLIB a veces está LENTO (6-10s por request). Optimizamos:
         1. SEARCH primero (devuelve metadata + lyrics juntos en 1 call).
            GET endpoint TAMBIÉN tarda, así que no nos ahorra nada
            mantenerlo arriba.
         2. Early-exit en cuanto cualquier candidato tiene synced lyrics
            Y duración ±5s del target — son criterios suficientes para
            decir "esto es la canción".
         3. Pocas queries (~3 max en buen caso) para no acumular tiempo
            si el server está lento. */

    /* SEARCH variants — dedupe por querystring. ORDEN: lo más probable
       primero (title+artist suele matchear), luego degradación. */
    $queries = [];
    $seenQs = [];
    $addSearch = function(array $qs) use (&$queries, &$seenQs): void {
        $key = http_build_query($qs);
        if (isset($seenQs[$key])) return;
        $seenQs[$key] = true;
        $queries[] = ['type' => 'search', 'url' => 'https://lrclib.net/api/search?' . $key];
    };
    /* 1. Title original + artist original (más común para títulos limpios). */
    if ($artist !== '')      $addSearch(['track_name' => $title,         'artist_name' => $artist]);
    /* 2. Title sin paréntesis + artist clean (cubre "Title (extra)" patterns). */
    if ($titleNoParens !== '' && $titleNoParens !== $title && $artistClean !== '') {
        $addSearch(['track_name' => $titleNoParens, 'artist_name' => $artistClean]);
    }
    /* 3. Title clean + artist clean (cubre "Title - Remix" / brackets). */
    if ($titleClean !== '' && $titleClean !== $title && $artistClean !== '') {
        $addSearch(['track_name' => $titleClean,    'artist_name' => $artistClean]);
    }
    /* 4. Si solo hay 1-2 variants útiles, GET endpoint como bonus
       (mismo orden de costo pero a veces único match exacto). */
    if (count($queries) < 2 && $duration > 0 && $artist !== '') {
        $queries[] = ['type' => 'get', 'url' => 'https://lrclib.net/api/get?' . http_build_query([
            'track_name' => $title, 'artist_name' => $artist, 'duration' => $duration,
        ])];
    }
    /* 5. Fallback solo título sin parens (LRCLIB tiene versión single). */
    if ($titleNoParens !== '' && $titleNoParens !== $title) {
        $addSearch(['track_name' => $titleNoParens]);
    }
    /* 6. Último recurso: solo artista. */
    if ($artistClean !== '' && count($queries) < 4) {
        $addSearch(['artist_name' => $artistClean]);
    }

    _lrclibLog("  sequential queries: " . count($queries));

    /* Itera secuencial acumulando candidatos con EARLY-EXIT AGRESIVO.
       Cualquiera de estos disparan exit:
         A) Hay un candidato con synced + duración ±5s del target
            → es claramente la canción correcta, no buscar más.
         B) Hay un candidato con synced + título exacto + artista exacto
            (aunque no tenga duración).
         C) Acumulamos >20 candidatos (suficiente para scoring). */
    $candidates = [];
    $seenIds = [];
    $foundGoodHit = false;
    foreach ($queries as $qi => $q) {
        $tq = microtime(true);
        $data = _lrclibFetch($q['url']);
        $qElapsed = round((microtime(true) - $tq) * 1000);
        if ($data === null) {
            _lrclibLog("    Q$qi [{$q['type']}] null ({$qElapsed}ms)");
            continue;
        }
        $items = ($q['type'] === 'get') ? [$data] : $data;
        $added = 0;
        foreach ($items as $r) {
            if (!is_array($r)) continue;
            if (empty($r['syncedLyrics']) && empty($r['plainLyrics'])) continue;
            if (!empty($r['instrumental'])) continue;
            $rid = $r['id'] ?? null;
            if ($rid && isset($seenIds[$rid])) continue;
            if ($rid) $seenIds[$rid] = true;
            $candidates[] = $r;
            $added++;
            /* Check criteria A y B aquí mismo. */
            if (!$foundGoodHit && !empty($r['syncedLyrics'])) {
                $rDur = (int)($r['duration'] ?? 0);
                $rTitle = mb_strtolower(trim($r['trackName'] ?? ''));
                $rArtist = mb_strtolower(trim($r['artistName'] ?? ''));
                $wTitleLo = mb_strtolower($titleClean);
                $wArtistLo = mb_strtolower($artistClean);
                /* A: synced + duración ±5s. */
                if ($duration > 0 && $rDur > 0 && abs($rDur - $duration) <= 5) {
                    $foundGoodHit = true;
                }
                /* B: synced + title y artist exactos. */
                elseif ($rTitle === $wTitleLo && $rArtist === $wArtistLo) {
                    $foundGoodHit = true;
                }
            }
        }
        _lrclibLog("    Q$qi [{$q['type']}] +$added candidates ({$qElapsed}ms, total=" . count($candidates) . ", goodHit=" . ($foundGoodHit ? 'yes' : 'no') . ')');
        if ($foundGoodHit) {
            _lrclibLog("  early-exit at Q$qi (good synced match found)");
            break;
        }
        if (count($candidates) > 20) {
            _lrclibLog("  early-exit at Q$qi (>20 candidates accumulated)");
            break;
        }
    }

    $hit = null;
    if (!empty($candidates)) {
        usort($candidates, function($a, $b) use ($titleClean, $artistClean, $duration) {
            return _lrclibScoreResult($b, $titleClean, $artistClean, $duration)
                 - _lrclibScoreResult($a, $titleClean, $artistClean, $duration);
        });
        $hit = $candidates[0];
        $hitScore = _lrclibScoreResult($hit, $titleClean, $artistClean, $duration);
        _lrclibLog("  best: '" . ($hit['trackName'] ?? '') . "' by '" . ($hit['artistName'] ?? '') . "' dur=" . ($hit['duration'] ?? '?') . " score=$hitScore synced=" . (empty($hit['syncedLyrics']) ? 'no' : 'yes(' . strlen($hit['syncedLyrics']) . ')'));
    }
    $elapsed = round((microtime(true) - $tStart) * 1000);
    _lrclibLog("getLyrics: " . ($hit ? 'HIT' : 'MISS') . " ({$elapsed}ms, " . count($candidates) . " candidates)");

    if (!$hit) {
        cacheSet($cacheKey, 'NULL', 24 * 3600);
        return null;
    }

    $result = [
        'plain'  => $hit['plainLyrics']  ?? null,
        'synced' => $hit['syncedLyrics'] ?? null,
        'matched_title'  => $hit['trackName']  ?? $title,
        'matched_artist' => $hit['artistName'] ?? $artist,
    ];
    if ($result['plain'] === null && $result['synced'] === null) {
        cacheSet($cacheKey, 'NULL', 24 * 3600);
        return null;
    }
    cacheSet($cacheKey, json_encode($result), 30 * 24 * 3600);
    return $result;
}
