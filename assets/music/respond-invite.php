<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body     = json_decode(file_get_contents('php://input'), true);
$inviteId = isset($body['inviteId']) ? $body['inviteId'] : '';
$action   = isset($body['action'])   ? $body['action']   : '';
if (!$inviteId || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}

$inviteFile = __DIR__ . '/' . $userKey . '-invites.json';
$invites    = [];
if (file_exists($inviteFile)) {
    $invites = json_decode(file_get_contents($inviteFile), true);
    if (!is_array($invites)) $invites = [];
}
$invite = null;
foreach ($invites as $inv) {
    if ($inv['id'] === $inviteId) { $invite = $inv; break; }
}
if (!$invite) { echo json_encode(['error' => 'Invitación no encontrada']); exit; }

$invites = array_values(array_filter($invites, function($i) use ($inviteId) { return $i['id'] !== $inviteId; }));
file_put_contents($inviteFile, json_encode($invites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($action === 'accept') {
    $fromUser   = $invite['fromUser'];
    $playlistId = $invite['playlistId'];
    if (!array_key_exists($fromUser, $loginUsers)) { echo json_encode(['ok' => true]); exit; }

    $myFile = __DIR__ . '/' . $userKey . '-playlists.json';
    $myPls  = [];
    if (file_exists($myFile)) {
        $myPls = json_decode(file_get_contents($myFile), true);
        if (!is_array($myPls)) $myPls = [];
    }
    $alreadyHas = false;
    foreach ($myPls as $pl) {
        if ($pl['id'] === $playlistId) { $alreadyHas = true; break; }
    }
    if (!$alreadyHas) {
        $myPls[] = ['id' => $playlistId, 'sharedFrom' => $fromUser];
        file_put_contents($myFile, json_encode($myPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $ownerFile = __DIR__ . '/' . $fromUser . '-playlists.json';
    $ownerPls  = [];
    if (file_exists($ownerFile)) {
        $ownerPls = json_decode(file_get_contents($ownerFile), true);
        if (!is_array($ownerPls)) $ownerPls = [];
    }
    foreach ($ownerPls as &$pl) {
        if ($pl['id'] === $playlistId) {
            if (!isset($pl['collaborators'])) $pl['collaborators'] = [];
            if (!in_array($userKey, $pl['collaborators'])) $pl['collaborators'][] = $userKey;
            break;
        }
    }
    unset($pl);
    file_put_contents($ownerFile, json_encode($ownerPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

echo json_encode(['ok' => true]);
