<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user'])) { echo json_encode(array('error' => 'No autorizado')); exit; }

$videoId = isset($_GET['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id']) : '';
if (strlen($videoId) !== 11) { echo json_encode(array('error' => 'ID inválido')); exit; }

$url = 'https://www.youtube.com/watch?v=' . $videoId;
$ctx = stream_context_create(array('http' => array(
    'timeout' => 12,
    'ignore_errors' => true,
    'header' => implode("\r\n", array(
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept-Language: en-US,en;q=0.9',
    ))
)));

$html = @file_get_contents($url, false, $ctx);
if (!$html) { echo json_encode(array('error' => 'No se pudo acceder')); exit; }

$duration = 0;
if (preg_match('/"lengthSeconds"\s*:\s*"(\d+)"/', $html, $m)) {
    $duration = intval($m[1]);
}

if (!$duration) { echo json_encode(array('error' => 'No se pudo obtener duración')); exit; }

echo json_encode(array('duration' => $duration));
