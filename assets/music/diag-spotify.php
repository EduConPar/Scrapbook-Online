<?php
/* ──────────────────────────────────────────────────────────────────────
   DIAGNÓSTICO DE BÚSQUEDA DE ÁLBUMES
   ──────────────────────────────────────────────────────────────────────
   Acceso: /scrapbookOnline/assets/music/diag-spotify.php
   - Requiere sesión iniciada (cualquier usuario).
   - Muestra estado del guard de rate-limit, validez del token, y hace
     UNA búsqueda de prueba contra Spotify.
   - `?clear=1` borra el guard de rate-limit + cualquier notFound
     transitorio acumulado del último día (los hits buenos se preservan).
   ────────────────────────────────────────────────────────────────────── */
session_start();
header('Content-Type: text/plain; charset=utf-8');

if (empty($_SESSION['user'])) {
    http_response_code(403);
    echo "No autorizado: inicia sesión primero.\n";
    exit;
}

require_once __DIR__ . '/spotify-helpers.php';
require_once dirname(__DIR__) . '/cache-helpers.php';
require_once dirname(__DIR__, 2) . '/db.php';

$out = [];
$out[] = "=== Diagnóstico Spotify / find-album ===";
$out[] = "Hora servidor: " . date('Y-m-d H:i:s');
$out[] = "";

/* 1) Guard de rate-limit. */
$rl = (int)cacheGet('spotify_rate_limited_until');
if ($rl > time()) {
    $secs = $rl - time();
    $h = floor($secs / 3600); $m = floor(($secs % 3600) / 60); $s = $secs % 60;
    $out[] = "[GUARD RATE-LIMIT] ACTIVO — expira en {$h}h {$m}m {$s}s (timestamp $rl)";
    $out[] = "  Todas las find-album devuelven 503 sin tocar Spotify hasta entonces.";
} else {
    $out[] = "[GUARD RATE-LIMIT] no activo. ✔";
}
$out[] = "";

/* Si pasa ?clear=1, borra el guard + las notFound recientes. */
if (isset($_GET['clear'])) {
    try {
        $pdo->prepare("DELETE FROM app_cache WHERE cache_key = ?")
            ->execute(['spotify_rate_limited_until']);
        $deleted = 0;
        $st = $pdo->prepare("DELETE FROM app_cache
                             WHERE cache_key LIKE 'album_lookup_v%'
                               AND cache_value LIKE '%notFound%'");
        $st->execute();
        $deleted = $st->rowCount();
        $out[] = "[CLEAR] Guard borrado.";
        $out[] = "[CLEAR] $deleted notFound transitorios borrados.";
    } catch (Throwable $e) {
        $out[] = "[CLEAR] Error: " . $e->getMessage();
    }
    $out[] = "";
}

/* ?strip-spotify-keys=1 — LIMPIEZA MASIVA de los items del perfil.
   Borra `spotifyAlbumId` y `albumKey:'spotify:*'` de TODOS los items
   album guardados en `usuarios.music` (JSON). Tras esto, cualquier
   click sobre un álbum del perfil ya NO puede pasar key=spotify:* al
   servidor — el cliente tendrá que resolverlo fresh vía find-album
   contra iTunes/Deezer.
   Se ejecuta sobre TODOS los usuarios; el endpoint solo lo invoca
   alguien con sesión iniciada (el chequeo de session_start arriba). */
if (isset($_GET['strip-spotify-keys'])) {
    $touchedUsers = 0;
    $touchedItems = 0;
    try {
        $st = $pdo->query("SELECT id, user_key, label, music FROM usuarios WHERE music IS NOT NULL");
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $music = json_decode((string)$row['music'], true);
            if (!is_array($music)) continue;
            $changed = false;
            foreach ($music as &$it) {
                if (!is_array($it)) continue;
                if (($it['type'] ?? '') !== 'album') continue;
                $hadKey = isset($it['spotifyAlbumId']) && $it['spotifyAlbumId'] !== '';
                $isSpKey = isset($it['albumKey']) && is_string($it['albumKey']) && strpos($it['albumKey'], 'spotify:') === 0;
                if ($hadKey || $isSpKey) {
                    if ($hadKey) unset($it['spotifyAlbumId']);
                    if ($isSpKey) unset($it['albumKey']);
                    $touchedItems++;
                    $changed = true;
                }
            }
            unset($it);
            if ($changed) {
                $upd = $pdo->prepare("UPDATE usuarios SET music = ? WHERE id = ?");
                $upd->execute([json_encode($music, JSON_UNESCAPED_UNICODE), $row['id']]);
                $touchedUsers++;
                $out[] = "[STRIP] " . $row['label'] . " (" . $row['user_key'] . "): items album limpiados.";
            }
        }
        $out[] = "[STRIP] TOTAL: $touchedItems items album limpiados en $touchedUsers usuarios.";
        $out[] = "  Los próximos clicks sobre estos álbumes van a hacer find-album fresh";
        $out[] = "  contra iTunes/Deezer (ya no tienen key spotify:* que arrastrar).";
    } catch (Throwable $e) {
        $out[] = "[STRIP] Error: " . $e->getMessage();
    }
    $out[] = "";
}

/* ?clear-albums=1 — BORRADO COMPLETO de las caches de álbumes.
   Útil cuando Spotify devolvió álbumes "incorrectos" en su día (p.ej.
   live versions, recopilatorios) y los hits se quedaron cacheados.
   Tras esto, la próxima find-album hace una resolución fresca por la
   cascada iTunes → Deezer → Spotify y guarda el resultado actualizado. */
if (isset($_GET['clear-albums'])) {
    try {
        $totalDeleted = 0;
        $prefixes = [
            'album_track_v%',    /* cache por videoId (v1+) */
            'album_lookup_v%',   /* cache por query (v1..v5+) */
        ];
        foreach ($prefixes as $pat) {
            $st = $pdo->prepare("DELETE FROM app_cache WHERE cache_key LIKE ?");
            $st->execute([$pat]);
            $deleted = $st->rowCount();
            $totalDeleted += $deleted;
            $out[] = "[CLEAR-ALBUMS] $deleted entradas borradas con prefijo '$pat'.";
        }
        $out[] = "[CLEAR-ALBUMS] TOTAL: $totalDeleted entradas borradas.";
        $out[] = "  Recuerda también limpiar la cache CLIENTE para que el navegador";
        $out[] = "  no use sus propias copias en localStorage:";
        $out[] = "    Móvil:    localStorage.removeItem('mu:album-cache:v1'); location.reload();";
        $out[] = "    Desktop:  localStorage.removeItem('reproductor:album-cache:v5'); location.reload();";
    } catch (Throwable $e) {
        $out[] = "[CLEAR-ALBUMS] Error: " . $e->getMessage();
    }
    $out[] = "";
}

/* ?set-guard=HORAS — re-arma el guard para parar de pegar a Spotify y
   dejar que su ban (que cuenta por IP) expire solo. Sin esto, cada
   find-album dispara 429 → renueva contador en el lado de Spotify. */
if (isset($_GET['set-guard'])) {
    $hours = max(1, min(48, (int)$_GET['set-guard']));
    $secs  = $hours * 3600;
    $until = time() + $secs;
    try {
        cacheSet('spotify_rate_limited_until', (string)$until, $secs);
        $out[] = "[SET-GUARD] Guard puesto por {$hours}h (hasta " . date('Y-m-d H:i:s', $until) . ").";
        $out[] = "  Durante este tiempo find-album devuelve 503 sin tocar Spotify.";
        $out[] = "  Eso permite que el ban de Spotify (que cuenta por IP) expire por sí solo.";
    } catch (Throwable $e) {
        $out[] = "[SET-GUARD] Error: " . $e->getMessage();
    }
    $out[] = "";
}

/* 2) Token de Spotify. */
$token = getSpotifyToken();
if (!$token) {
    $out[] = "[TOKEN] ✗ No se pudo obtener token de Spotify (revisa SPOTIFY_CLIENT_ID/SECRET en .env).";
    echo implode("\n", $out), "\n";
    exit;
}
$out[] = "[TOKEN] obtenido (" . substr($token, 0, 8) . "…). ✔";
$out[] = "";

/* 3) Test de búsqueda contra Spotify — OPT-IN con ?test=1.
   Antes corría siempre, así que CADA visita al diagnóstico hacía 1
   request real a Spotify y renovaba el ban cuando estaba activo. */
if (!isset($_GET['test'])) {
    $out[] = "[TEST] omitido (añade ?test=1 para hacer 1 request real a Spotify).";
    $out[] = "";
    $out[] = "─ Acciones disponibles ─";
    $out[] = "  ?clear=1               → borra el guard + notFound transitorios cacheados.";
    $out[] = "  ?strip-spotify-keys=1  → LIMPIA spotifyAlbumId y albumKey:spotify:*";
    $out[] = "                           de TODOS los items album del perfil de TODOS";
    $out[] = "                           los usuarios. Tras esto, ningún click sobre";
    $out[] = "                           un álbum del perfil arrastra key spotify:*";
    $out[] = "                           — el cliente lo resuelve fresh con find-album";
    $out[] = "                           contra iTunes/Deezer.";
    $out[] = "  ?clear-albums=1        → BORRA TODAS las caches de álbumes server-side";
    $out[] = "                    (álbumes incorrectos guardados de Spotify, recopilatorios,";
    $out[] = "                    live versions, etc). Tras esto se re-resuelven frescos.";
    $out[] = "                    Recuerda limpiar también la cache CLIENTE (localStorage).";
    $out[] = "  ?set-guard=N    → re-arma el guard por N horas (1-48). Úsalo cuando";
    $out[] = "                    Spotify devuelve 429 para parar de pegarle y dejar";
    $out[] = "                    que el ban (por IP) expire solo.";
    $out[] = "  ?test=1         → ejecuta UNA búsqueda real a Spotify (renueva el ban si está banneado).";
    $out[] = "  ?q=TEXTO        → cambia la query del test (combina con ?test=1).";
    echo implode("\n", $out), "\n";
    exit;
}
$q = $_GET['q'] ?? 'never gonna give you up rick astley';
$out[] = "[TEST] Búsqueda: " . $q;

$url = 'https://api.spotify.com/v1/search?type=track&limit=3&market=US&q=' . rawurlencode($q);
$start = microtime(true);
$ctx = stream_context_create(['http' => [
    'timeout'       => 10,
    'ignore_errors' => true,
    'header'        => 'Authorization: Bearer ' . $token,
]]);
$raw = @file_get_contents($url, false, $ctx);
$dur = round((microtime(true) - $start) * 1000);

$status = '?';
foreach ($http_response_header ?? [] as $h) {
    if (stripos($h, 'HTTP/') === 0) {
        $parts = explode(' ', $h);
        $status = $parts[1] ?? '?';
    }
}
$out[] = "  HTTP $status en {$dur}ms";

if ($raw === false) {
    $out[] = "  ✗ file_get_contents falló — Spotify no respondió.";
} else {
    $data = json_decode($raw, true);
    if (isset($data['tracks']['items']) && count($data['tracks']['items'])) {
        $t = $data['tracks']['items'][0];
        $out[] = "  ✔ Spotify respondió. Primer hit: " . ($t['name'] ?? '?')
             . " — " . ($t['artists'][0]['name'] ?? '?')
             . " (álbum: " . ($t['album']['name'] ?? '?') . ")";
    } elseif (isset($data['error']['status'])) {
        $code = (int)$data['error']['status'];
        $msg  = $data['error']['message'] ?? '';
        $out[] = "  ✗ Spotify devolvió error $code: $msg";
        if ($code === 429) {
            $retryAfter = 0;
            foreach ($http_response_header ?? [] as $h) {
                if (stripos($h, 'retry-after:') === 0) {
                    $retryAfter = (int)trim(substr($h, 12)); break;
                }
            }
            $out[] = "    Retry-After: {$retryAfter}s";
            if ($retryAfter > 10) {
                $out[] = "    → Spotify nos está rate-limitando AHORA. El guard se renovará al volver a hacer find-album.";
            }
        }
    } else {
        $out[] = "  ? Respuesta inesperada (200 chars): " . substr($raw, 0, 200);
    }
}

echo implode("\n", $out), "\n";
