<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user'])) { echo json_encode(array('error' => 'No autorizado')); exit; }

$videoId = isset($_GET['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['id']) : '';
if (strlen($videoId) !== 11) { echo json_encode(array('error' => 'ID inválido')); exit; }

$url = 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $videoId . '&format=json';
$ctx = stream_context_create(array('http' => array('timeout' => 8, 'ignore_errors' => true)));
$raw = @file_get_contents($url, false, $ctx);
if (!$raw) { echo json_encode(array('error' => 'No se pudo obtener el título')); exit; }
$data = json_decode($raw, true);
if (!$data || !isset($data['title'])) { echo json_encode(array('error' => 'Respuesta inválida')); exit; }

echo json_encode(array('title' => $data['title']));
