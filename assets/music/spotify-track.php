<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/spotify-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }

$url = isset($_GET['url']) ? trim($_GET['url']) : '';
if (!$url) { echo json_encode(['error' => 'URL requerida']); exit; }

$trackId = null;
if (preg_match('/spotify\.com\/(?:[a-z][a-z0-9-]*\/)?track\/([A-Za-z0-9]+)/', $url, $m)) {
    $trackId = $m[1];
} elseif (preg_match('/spotify:track:([A-Za-z0-9]+)/', $url, $m)) {
    $trackId = $m[1];
}
if (!$trackId) { echo json_encode(['error' => 'URL de track de Spotify inválida']); exit; }

$token = getSpotifyToken();
if (!$token) { echo json_encode(['error' => 'No se pudo autenticar con Spotify']); exit; }

$ctx = stream_context_create(['http' => [
    'timeout'       => 10,
    'ignore_errors' => true,
    'header'        => 'Authorization: Bearer ' . $token,
]]);
$raw = @file_get_contents('https://api.spotify.com/v1/tracks/' . $trackId, false, $ctx);
if (!$raw) { echo json_encode(['error' => 'No se pudo obtener el track de Spotify']); exit; }

$track = json_decode($raw, true);
if (!isset($track['name'])) { echo json_encode(['error' => 'Track no encontrado en Spotify']); exit; }

$title    = $track['name'];
$artist   = isset($track['artists'][0]['name']) ? $track['artists'][0]['name'] : '';
$duration = isset($track['duration_ms']) ? (int)round($track['duration_ms'] / 1000) : 0;

$videoId = searchYouTubeVideoId($title . ' ' . $artist . ' audio');
if (!$videoId) { echo json_encode(['error' => 'No se encontró el vídeo en YouTube']); exit; }

echo json_encode(['title' => $title, 'artist' => $artist, 'duration' => $duration, 'videoId' => $videoId]);
