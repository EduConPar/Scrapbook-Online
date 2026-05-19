<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
set_time_limit(600);
require_once __DIR__ . '/spotify-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['tracks']) || !is_array($body['tracks'])) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}

$results = [];
foreach ($body['tracks'] as $track) {
    $title    = isset($track['title'])    ? substr(strip_tags($track['title']),  0, 200) : '';
    $artist   = isset($track['artist'])   ? substr(strip_tags($track['artist']), 0, 200) : '';
    $duration = isset($track['duration']) ? max(0, intval($track['duration']))            : 0;
    if (!$title) continue;
    $videoId = searchYouTubeVideoId($title . ' ' . $artist . ' audio');
    if (!$videoId) continue;
    $results[] = ['videoId' => $videoId, 'title' => $title, 'artist' => $artist, 'duration' => $duration];
    usleep(150000);
}

echo json_encode(['tracks' => $results]);
