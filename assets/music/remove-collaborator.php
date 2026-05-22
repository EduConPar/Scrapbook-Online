<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body           = json_decode(file_get_contents('php://input'), true);
$playlistId     = isset($body['playlistId'])     ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['playlistId'])     : '';
$collaborator   = isset($body['collaborator'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['collaborator'])   : '';
if (!$playlistId || !$collaborator) { echo json_encode(['error' => 'Datos incompletos']); exit; }
if (!array_key_exists($collaborator, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

// Remove from owner's playlist collaborators array
$ownerFile = __DIR__ . '/' . $userKey . '-playlists.json';
$ownerPls  = [];
if (file_exists($ownerFile)) {
    $ownerPls = json_decode(file_get_contents($ownerFile), true);
    if (!is_array($ownerPls)) $ownerPls = [];
}
$playlistName = '';
foreach ($ownerPls as &$pl) {
    if ($pl['id'] === $playlistId) {
        $playlistName = $pl['name'];
        if (isset($pl['collaborators'])) {
            $pl['collaborators'] = array_values(array_filter($pl['collaborators'], function($c) use ($collaborator) {
                return $c !== $collaborator;
            }));
        }
        break;
    }
}
unset($pl);
file_put_contents($ownerFile, json_encode($ownerPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Remove the shared playlist from the collaborator's playlist file
$collabFile = __DIR__ . '/' . $collaborator . '-playlists.json';
$collabPls  = [];
if (file_exists($collabFile)) {
    $collabPls = json_decode(file_get_contents($collabFile), true);
    if (!is_array($collabPls)) $collabPls = [];
}
$collabPls = array_values(array_filter($collabPls, function($pl) use ($playlistId) {
    return $pl['id'] !== $playlistId;
}));
file_put_contents($collabFile, json_encode($collabPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Send removal notification to the collaborator
$notifFile = __DIR__ . '/' . $collaborator . '-invites.json';
$notifs    = [];
if (file_exists($notifFile)) {
    $notifs = json_decode(file_get_contents($notifFile), true);
    if (!is_array($notifs)) $notifs = [];
}
$notifs[] = [
    'id'           => 'rm_' . time() . '_' . rand(1000, 9999),
    'type'         => 'removed',
    'playlistId'   => $playlistId,
    'playlistName' => $playlistName,
    'fromLabel'    => $loginUsers[$userKey]['label'],
    'sentAt'       => time(),
];
file_put_contents($notifFile, json_encode($notifs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true]);
