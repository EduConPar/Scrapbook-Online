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

/* 3) Test de búsqueda contra Spotify. */
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

$out[] = "";
$out[] = "─ Acciones disponibles ─";
$out[] = "  ?clear=1        → borra el guard + notFound transitorios cacheados.";
$out[] = "  ?set-guard=N    → re-arma el guard por N horas (1-48). Úsalo cuando";
$out[] = "                    Spotify devuelve 429 para parar de pegarle y dejar";
$out[] = "                    que el ban (por IP) expire solo.";
$out[] = "  ?q=TEXTO        → cambia la query de prueba.";
$out[] = "";
$out[] = "⚠ Cada vez que abres este endpoint sin set-guard activo, se HACE 1 request";
$out[] = "  a Spotify (el test de búsqueda). Si Spotify devuelve 429, renovás su contador.";
$out[] = "  → NO lo recargues mientras esté banneado.";

echo implode("\n", $out), "\n";
