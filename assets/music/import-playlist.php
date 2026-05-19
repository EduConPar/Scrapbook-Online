<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user'])) { echo json_encode(array('error' => 'No autorizado')); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$url  = isset($body['url']) ? trim($body['url']) : '';

// Extract playlist ID
$playlistId = '';
if (preg_match('/[?&]list=([A-Za-z0-9_-]+)/', $url, $m)) {
    $playlistId = preg_replace('/[^A-Za-z0-9_-]/', '', $m[1]);
}
if (!$playlistId) { echo json_encode(array('error' => 'URL de playlist inválida')); exit; }

$fetchUrl = 'https://www.youtube.com/playlist?list=' . $playlistId;
$ctx = stream_context_create(array('http' => array(
    'timeout'       => 20,
    'ignore_errors' => true,
    'header'        => implode("\r\n", array(
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept-Language: en-US,en;q=0.9',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ))
)));

$html = @file_get_contents($fetchUrl, false, $ctx);
if (!$html) { echo json_encode(array('error' => 'No se pudo acceder a la playlist')); exit; }

// Extract ytInitialData JSON blob
$marker = 'var ytInitialData = ';
$pos    = strpos($html, $marker);
if ($pos === false) { echo json_encode(array('error' => 'No se pudo parsear la playlist')); exit; }
$pos += strlen($marker);

$endPos = strpos($html, ';</script>', $pos);
if ($endPos === false) $endPos = strpos($html, ';var ', $pos);
if ($endPos === false) { echo json_encode(array('error' => 'Datos de playlist mal formados')); exit; }

$jsonStr = substr($html, $pos, $endPos - $pos);
$data    = json_decode($jsonStr, true);
if (!$data) { echo json_encode(array('error' => 'JSON de playlist inválido')); exit; }

// Recursively find playlistVideoListRenderer
function findKey($arr, $key) {
    if (!is_array($arr)) return null;
    if (isset($arr[$key])) return $arr[$key];
    foreach ($arr as $v) {
        $r = findKey($v, $key);
        if ($r !== null) return $r;
    }
    return null;
}

$videoList = findKey($data, 'playlistVideoListRenderer');
if (!$videoList || empty($videoList['contents'])) {
    echo json_encode(array('error' => 'No se encontraron canciones en la playlist')); exit;
}

$tracks = array();
foreach ($videoList['contents'] as $item) {
    $v = isset($item['playlistVideoRenderer']) ? $item['playlistVideoRenderer'] : null;
    if (!$v) continue;
    $videoId = isset($v['videoId']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $v['videoId']) : '';
    if (strlen($videoId) !== 11) continue;
    $title    = isset($v['title']['runs'][0]['text'])           ? $v['title']['runs'][0]['text']           : '';
    $artist   = isset($v['shortBylineText']['runs'][0]['text']) ? $v['shortBylineText']['runs'][0]['text'] : '';
    $artist   = trim(preg_replace('/\s*-\s*topic$/i', '', $artist));
    $lenText  = isset($v['lengthText']['simpleText']) ? $v['lengthText']['simpleText'] : '';
    $duration = 0;
    if ($lenText) {
        $parts = explode(':', $lenText);
        if (count($parts) === 2) $duration = intval($parts[0]) * 60 + intval($parts[1]);
        elseif (count($parts) === 3) $duration = intval($parts[0]) * 3600 + intval($parts[1]) * 60 + intval($parts[2]);
    }
    $tracks[] = array(
        'videoId'  => $videoId,
        'title'    => substr(strip_tags($title),  0, 200),
        'artist'   => substr(strip_tags($artist), 0, 200),
        'duration' => $duration,
    );
}

if (empty($tracks)) { echo json_encode(array('error' => 'No se encontraron canciones en la playlist')); exit; }

echo json_encode(array('tracks' => $tracks));
