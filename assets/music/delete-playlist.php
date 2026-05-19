<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$id   = isset($body['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['id']) : '';
if (!$id) { echo json_encode(['error' => 'ID inválido']); exit; }

$plFile    = __DIR__ . '/' . $userKey . '-playlists.json';
$playlists = [];
if (file_exists($plFile)) {
    $playlists = json_decode(file_get_contents($plFile), true);
    if (!is_array($playlists)) $playlists = [];
}

$deleted = null;
foreach ($playlists as $pl) {
    if ($pl['id'] === $id) { $deleted = $pl; break; }
}

$playlists = array_values(array_filter($playlists, function($pl) use ($id) { return $pl['id'] !== $id; }));
file_put_contents($plFile, json_encode($playlists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($deleted && isset($deleted['collaborators'])) {
    foreach ($deleted['collaborators'] as $collabKey) {
        if (!array_key_exists($collabKey, $loginUsers)) continue;
        $collabFile = __DIR__ . '/' . $collabKey . '-playlists.json';
        if (!file_exists($collabFile)) continue;
        $collabPls = json_decode(file_get_contents($collabFile), true);
        if (!is_array($collabPls)) continue;
        $collabPls = array_values(array_filter($collabPls, function($p) use ($id) { return $p['id'] !== $id; }));
        file_put_contents($collabFile, json_encode($collabPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

echo json_encode(['ok' => true]);
