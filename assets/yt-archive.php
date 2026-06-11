<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cache-helpers.php';

if (!isset($_SESSION['user'])) {
    ob_end_clean(); echo json_encode(array('error' => 'No autorizado')); exit;
}
if (!function_exists('curl_init')) {
    ob_end_clean(); echo json_encode(array('error' => 'cURL no habilitado en php.ini')); exit;
}

$action     = isset($_GET['action']) ? $_GET['action'] : 'playlists';
$playlistId = isset($_GET['list']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['list']) : '';
$videoId    = isset($_GET['id'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id']) : '';

/* Caché de respuestas de Innertube en SQL (app_cache), ya no en disco.
   TTL de 1h. Devuelve false cuando no hay entrada vigente (compat). */
define('YT_CACHE_TTL', 3600);

function ytCacheGet($key) {
    $v = cacheGet('yt_' . $key);
    return $v === null ? false : $v;
}
function ytCacheSet($key, $val) {
    cacheSet('yt_' . $key, $val, YT_CACHE_TTL);
}

$itKey = 'AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8';
$itCtx = array('context' => array('client' => array(
    'hl' => 'en', 'gl' => 'US',
    'clientName' => 'WEB', 'clientVersion' => '2.20240101.05.00',
)));

function innertubeCall($endpoint, $payload) {
    global $itKey, $itCtx;
    $cacheKey = 'it_' . md5($endpoint . json_encode($payload));
    $cached   = ytCacheGet($cacheKey);
    if ($cached !== false) return json_decode($cached, true);

    $body = json_encode(array_merge($itCtx, $payload));
    $url  = 'https://www.youtube.com/youtubei/v1/' . $endpoint
          . '?key=' . $itKey . '&prettyPrint=false';

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/json',
            'X-YouTube-Client-Name: 1',
            'X-YouTube-Client-Version: 2.20240101.05.00',
            'Origin: https://www.youtube.com',
            'Referer: https://www.youtube.com/',
        ),
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 20,
    ));
    $raw = curl_exec($ch);
    curl_close($ch);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if ($data && !isset($data['error'])) ytCacheSet($cacheKey, $raw);
    return $data;
}

function resolveChannelId($handle) {
    $data = innertubeCall('navigation/resolve_url', array(
        'url' => 'https://www.youtube.com/@' . ltrim($handle, '@'),
    ));
    if (isset($data['endpoint']['browseEndpoint']['browseId']))
        return $data['endpoint']['browseEndpoint']['browseId'];
    return null;
}

function innertubePost($payload) {
    return innertubeCall('browse', $payload);
}

function ytBestThumb($thumbs) {
    if (!$thumbs) return '';
    $best = ''; $bw = 0;
    foreach ($thumbs as $t) {
        $w = isset($t['width']) ? $t['width'] : 0;
        if ($w > $bw) { $bw = $w; $best = isset($t['url']) ? $t['url'] : ''; }
    }
    return $best ? $best : (isset($thumbs[0]['url']) ? $thumbs[0]['url'] : '');
}
function ytGetText($node) {
    if (!$node) return '';
    if (isset($node['simpleText'])) return $node['simpleText'];
    if (isset($node['runs'][0]['text'])) return $node['runs'][0]['text'];
    return '';
}
function ytFindKeys($arr, $key, &$out, $d) {
    if ($d > 30 || !is_array($arr)) return;
    foreach ($arr as $k => $v) {
        if ($k === $key) $out[] = $v;
        if (is_array($v)) ytFindKeys($v, $key, $out, $d + 1);
    }
}

ob_end_clean();

/* ── DEBUG PLAYLISTS ── */
if ($action === 'debug_pl') {
    $channelId = resolveChannelId('melondeaguaarchive');
    $homeData  = innertubePost(array('browseId' => $channelId));
    $plParams  = '';
    $allTabs   = array();
    ytFindKeys($homeData, 'tabRenderer', $allTabs, 0);
    ytFindKeys($homeData, 'expandableTabRenderer', $allTabs, 0);
    foreach ($allTabs as $t) {
        if (strpos(strtolower(isset($t['title']) ? $t['title'] : ''), 'playlist') !== false
            && isset($t['endpoint']['browseEndpoint']['params'])) {
            $plParams = urldecode($t['endpoint']['browseEndpoint']['params']); break;
        }
    }
    $data = innertubePost(array('browseId' => $channelId, 'params' => $plParams));
    // Devuelve el primer item encontrado en bruto para ver la estructura
    $found = array();
    foreach (array('gridPlaylistRenderer','lockupViewModel','playlistRenderer','richItemRenderer') as $key) {
        $items = array();
        ytFindKeys($data, $key, $items, 0);
        if ($items) { $found[$key] = $items[0]; }
    }
    echo json_encode(array('params_used' => $plParams, 'found_renderers' => array_keys($found), 'first_items' => $found));
    exit;
}

/* ── DEBUG ── */
if ($action === 'debug') {
    $channelId = resolveChannelId('melondeaguaarchive');
    if (!$channelId) { echo json_encode(array('error' => 'No se pudo resolver el canal')); exit; }
    // Browse sin params para obtener los tabs reales
    $data = innertubePost(array('browseId' => $channelId));
    if (!$data) { echo json_encode(array('error' => 'Innertube no respondió')); exit; }
    if (isset($data['error'])) { echo json_encode(array('api_error' => $data['error'])); exit; }
    // Extraer tabs con su título y params
    $tabs = array();
    $rawTabs = array();
    ytFindKeys($data, 'tabRenderer', $rawTabs, 0);
    ytFindKeys($data, 'expandableTabRenderer', $rawTabs, 0);
    foreach ($rawTabs as $t) {
        $tabs[] = array(
            'title'  => isset($t['title']) ? $t['title'] : '',
            'params' => isset($t['endpoint']['browseEndpoint']['params']) ? $t['endpoint']['browseEndpoint']['params'] : '',
        );
    }
    echo json_encode(array('channelId' => $channelId, 'tabs' => $tabs));
    exit;
}

/* ── PLAYLISTS ── */
if ($action === 'playlists') {
    $channelId = resolveChannelId('melondeaguaarchive');
    if (!$channelId) { echo json_encode(array('error' => 'No se pudo resolver el canal')); exit; }

    // Obtener el params correcto de la pestaña Playlists
    $homeData = innertubePost(array('browseId' => $channelId));
    $plParams  = '';
    if ($homeData) {
        $allTabs = array();
        ytFindKeys($homeData, 'tabRenderer', $allTabs, 0);
        ytFindKeys($homeData, 'expandableTabRenderer', $allTabs, 0);
        foreach ($allTabs as $t) {
            $ttitle = isset($t['title']) ? strtolower($t['title']) : '';
            if (strpos($ttitle, 'playlist') !== false && isset($t['endpoint']['browseEndpoint']['params'])) {
                $plParams = urldecode($t['endpoint']['browseEndpoint']['params']);
                break;
            }
        }
    }
    if (!$plParams) { echo json_encode(array('error' => 'No se encontró la pestaña Playlists')); exit; }

    $data = innertubePost(array('browseId' => $channelId, 'params' => $plParams));
    if (!$data) { echo json_encode(array('error' => 'No se pudo conectar con YouTube (Innertube)')); exit; }

    $playlists = array();

    /* Renderer clásico */
    $r1 = array();
    ytFindKeys($data, 'gridPlaylistRenderer', $r1, 0);
    foreach ($r1 as $r) {
        if (!isset($r['playlistId'])) continue;
        $playlists[] = array(
            'id'    => $r['playlistId'],
            'title' => ytGetText(isset($r['title']) ? $r['title'] : null),
            'thumb' => ytBestThumb(isset($r['thumbnail']['thumbnails']) ? $r['thumbnail']['thumbnails'] : array()),
            'count' => ytGetText(isset($r['videoCountText']) ? $r['videoCountText'] : null),
        );
    }

    /* Renderer nuevo (lockupViewModel) */
    if (empty($playlists)) {
        $r2 = array();
        ytFindKeys($data, 'lockupViewModel', $r2, 0);
        foreach ($r2 as $r) {
            $pid = isset($r['contentId']) ? $r['contentId'] : '';
            if (!$pid || !isset($r['contentType']) || $r['contentType'] !== 'LOCKUP_CONTENT_TYPE_PLAYLIST') continue;
            $title = '';
            if (isset($r['metadata']['lockupMetadataViewModel']['title']['content']))
                $title = $r['metadata']['lockupMetadataViewModel']['title']['content'];
            $thumb = '';
            if (isset($r['contentImage']['collectionThumbnailViewModel']['primaryThumbnail']['thumbnailViewModel']['image']['sources'][0]['url']))
                $thumb = $r['contentImage']['collectionThumbnailViewModel']['primaryThumbnail']['thumbnailViewModel']['image']['sources'][0]['url'];
            $count = '';
            $badges = array();
            ytFindKeys($r, 'thumbnailBadgeViewModel', $badges, 0);
            if ($badges && isset($badges[0]['text'])) $count = $badges[0]['text'];
            $playlists[] = array('id' => $pid, 'title' => $title, 'thumb' => $thumb, 'count' => $count);
        }
    }

    /* Renderer intermedio (playlistRenderer dentro de shelfRenderer) */
    if (empty($playlists)) {
        $r3 = array();
        ytFindKeys($data, 'playlistRenderer', $r3, 0);
        foreach ($r3 as $r) {
            if (!isset($r['playlistId'])) continue;
            $thumbs = isset($r['thumbnails'][0]['thumbnails']) ? $r['thumbnails'][0]['thumbnails'] : array();
            $playlists[] = array(
                'id'    => $r['playlistId'],
                'title' => ytGetText(isset($r['title']) ? $r['title'] : null),
                'thumb' => ytBestThumb($thumbs),
                'count' => ytGetText(isset($r['videoCountText']) ? $r['videoCountText'] : null),
            );
        }
    }

    echo json_encode(array('playlists' => $playlists));

/* ── VIDEOS ── */
} elseif ($action === 'videos' && $playlistId) {
    /* browseId para playlists es VL + playlistId */
    $data = innertubePost(array('browseId' => 'VL' . $playlistId));
    if (!$data) { echo json_encode(array('error' => 'No se pudo obtener los vídeos')); exit; }

    /* Recolecta videos. Algunas playlists no usan playlistVideoRenderer
       (la convención clásica de playlists "regulares") sino otros
       renderers según el tipo:
         · playlistVideoRenderer → playlists públicas estándar
         · gridVideoRenderer     → algunas vistas grid (canales, mixes)
         · videoRenderer         → search-like results y algunos mixes
         · musicResponsiveListItemRenderer → playlists de YT Music
       Para no perder playlists especiales, recolectamos de todos. */
    $videos = array(); $seenIds = array();
    function _ytCollect(&$videos, &$seenIds, $data) {
        $renderers = array(
            'playlistVideoRenderer',
            'gridVideoRenderer',
            'videoRenderer',
            'compactVideoRenderer',
        );
        foreach ($renderers as $rk) {
            $hits = array();
            ytFindKeys($data, $rk, $hits, 0);
            foreach ($hits as $r) {
                $vid = isset($r['videoId']) ? $r['videoId'] : null;
                if (!$vid || isset($seenIds[$vid])) continue;
                $seenIds[$vid] = true;
                $videos[] = array(
                    'id'       => $vid,
                    'title'    => ytGetText(isset($r['title']) ? $r['title'] : (isset($r['headline']) ? $r['headline'] : null)),
                    'thumb'    => ytBestThumb(isset($r['thumbnail']['thumbnails']) ? $r['thumbnail']['thumbnails'] : array()),
                    'duration' => ytGetText(isset($r['lengthText']) ? $r['lengthText'] : null),
                );
            }
        }
    }
    _ytCollect($videos, $seenIds, $data);

    /* Continuation: YouTube devuelve max ~100 videos en la primera
       respuesta. Si la playlist tiene más, hay tokens de continuación
       en el JSON. Los seguimos en bucle (max 20 páginas como guard
       razonable para no colgar el endpoint en playlists enormes). */
    $maxPages = 20;
    while ($maxPages-- > 0) {
        $tokens = array();
        ytFindKeys($data, 'continuationCommand', $tokens, 0);
        $token = null;
        foreach ($tokens as $t) {
            if (isset($t['token'])) { $token = $t['token']; break; }
        }
        if (!$token) break;
        $data = innertubeCall('browse', array('continuation' => $token));
        if (!$data) break;
        $before = count($videos);
        _ytCollect($videos, $seenIds, $data);
        /* Si una página no añade nada nuevo, paramos para evitar loops
           infinitos por tokens redundantes. */
        if (count($videos) === $before) break;
    }

    echo json_encode(array('videos' => $videos));

/* ── INFO DE UN VÍDEO (descripción, autor, vistas) ── */
} elseif ($action === 'video-info' && $videoId) {
    /* Endpoint `player` devuelve videoDetails con shortDescription. */
    $data = innertubeCall('player', array('videoId' => $videoId));
    if (!$data) { echo json_encode(array('error' => 'No se pudo obtener info del vídeo')); exit; }
    $details = isset($data['videoDetails']) ? $data['videoDetails'] : array();
    echo json_encode(array(
        'description' => isset($details['shortDescription']) ? $details['shortDescription'] : '',
        'author'      => isset($details['author'])           ? $details['author']           : '',
        'viewCount'   => isset($details['viewCount'])        ? $details['viewCount']        : '',
        'lengthSec'   => isset($details['lengthSeconds'])    ? (int)$details['lengthSeconds'] : 0,
    ));

} else {
    echo json_encode(array('error' => 'Accion invalida'));
}
