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
if (!$body || !isset($body['videoId'], $body['title'])) {
    echo json_encode(array('error' => 'Datos incompletos')); exit;
}

$videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $body['videoId']);
$title   = substr(strip_tags($body['title']), 0, 200);
$artist  = isset($body['artist']) ? substr(strip_tags($body['artist']), 0, 200) : '';

if (strlen($videoId) !== 11) {
    echo json_encode(array('error' => 'ID de video inválido')); exit;
}

$file   = __DIR__ . '/' . $userKey . '-extra.json';
$tracks = array();
if (file_exists($file)) {
    $tracks = json_decode(file_get_contents($file), true);
    if (!is_array($tracks)) $tracks = array();
}

$track    = array('title' => $title, 'artist' => $artist, 'videoId' => $videoId);
$tracks[] = $track;
file_put_contents($file, json_encode($tracks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(array('ok' => true, 'track' => $track));
