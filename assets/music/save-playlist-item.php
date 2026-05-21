<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['id'], $body['name'])) { echo json_encode(['error' => 'Datos incompletos']); exit; }

$id         = preg_replace('/[^A-Za-z0-9_-]/', '', $body['id']);
$name       = substr(strip_tags($body['name']), 0, 100);
$sharedFrom = isset($body['sharedFrom']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['sharedFrom']) : null;
if (!$id) { echo json_encode(['error' => 'ID inválido']); exit; }

$tracks    = [];
$rawTracks = isset($body['tracks']) && is_array($body['tracks']) ? $body['tracks'] : [];
foreach ($rawTracks as $t) {
    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', isset($t['videoId']) ? $t['videoId'] : '');
    if (strlen($videoId) !== 11) continue;
    $tracks[] = [
        'videoId'  => $videoId,
        'title'    => substr(strip_tags(isset($t['title'])    ? $t['title']    : ''), 0, 200),
        'artist'   => substr(strip_tags(isset($t['artist'])   ? $t['artist']   : ''), 0, 200),
        'duration' => isset($t['duration']) ? max(0, intval($t['duration'])) : 0,
        'addedBy'  => substr(strip_tags($t['addedBy'] ?? ''), 0, 100),
    ];
}

if ($sharedFrom) {
    if (!array_key_exists($sharedFrom, $loginUsers)) { echo json_encode(['error' => 'Propietario inválido']); exit; }
    $ownerFile = __DIR__ . '/' . $sharedFrom . '-playlists.json';
    $ownerPls  = [];
    if (file_exists($ownerFile)) {
        $ownerPls = json_decode(file_get_contents($ownerFile), true);
        if (!is_array($ownerPls)) $ownerPls = [];
    }
    $authorized = false;
    foreach ($ownerPls as $pl) {
        if ($pl['id'] === $id) {
            $authorized = isset($pl['collaborators']) && in_array($userKey, $pl['collaborators']);
            break;
        }
    }
    if (!$authorized) { echo json_encode(['error' => 'Sin permiso para editar']); exit; }
    foreach ($ownerPls as &$pl) {
        if ($pl['id'] === $id) { $pl['tracks'] = $tracks; break; }
    }
    unset($pl);
    file_put_contents($ownerFile, json_encode($ownerPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true]);
    exit;
}

$plFile    = __DIR__ . '/' . $userKey . '-playlists.json';
$playlists = [];
if (file_exists($plFile)) {
    $playlists = json_decode(file_get_contents($plFile), true);
    if (!is_array($playlists)) $playlists = [];
}
$found = false;
foreach ($playlists as &$pl) {
    if ($pl['id'] === $id) {
        $pl['name']   = $name;
        $pl['tracks'] = $tracks;
        if (!isset($pl['owner'])) $pl['owner'] = $userKey;
        $found = true;
        break;
    }
}
unset($pl);
if (!$found) {
    $playlists[] = ['id' => $id, 'name' => $name, 'owner' => $userKey, 'collaborators' => [], 'tracks' => $tracks];
}
file_put_contents($plFile, json_encode($playlists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
