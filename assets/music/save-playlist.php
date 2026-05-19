<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(array('error' => 'No autorizado')); exit;
}

$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) {
    echo json_encode(array('error' => 'Usuario inválido')); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!isset($body['tracks']) || !is_array($body['tracks'])) {
    echo json_encode(array('error' => 'Datos inválidos')); exit;
}

$tracks = array();
foreach ($body['tracks'] as $t) {
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', isset($t['videoId']) ? $t['videoId'] : '');
    if (strlen($videoId) !== 11) continue;
    $tracks[] = array(
        'title'   => substr(strip_tags(isset($t['title'])  ? $t['title']  : ''), 0, 200),
        'artist'  => substr(strip_tags(isset($t['artist']) ? $t['artist'] : ''), 0, 200),
        'videoId' => $videoId,
    );
}

$file = __DIR__ . '/' . $userKey . '-custom.json';
file_put_contents($file, json_encode($tracks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(array('ok' => true, 'count' => count($tracks)));
