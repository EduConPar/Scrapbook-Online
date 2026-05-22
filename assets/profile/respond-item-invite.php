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
if (!$inviteId || !in_array($action, ['accept', 'reject', 'dismiss'])) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}

$inviteFile = __DIR__ . '/' . $userKey . '-item-invites.json';
$invites = [];
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

if ($action === 'accept' && isset($invite['fromUser']) && array_key_exists($invite['fromUser'], $loginUsers)) {
    $fromUser = $invite['fromUser'];
    $category = $invite['category'];
    $itemId   = $invite['itemId'];

    // Añadir item a la lista del usuario que acepta
    $myListsFile = __DIR__ . '/' . $userKey . '-lists.json';
    $myLists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
    if (file_exists($myListsFile)) {
        $raw = json_decode(file_get_contents($myListsFile), true);
        if (is_array($raw)) $myLists = array_merge($myLists, $raw);
    }
    $alreadyHas = false;
    foreach ($myLists[$category] as $it) {
        if ($it['id'] === $itemId) { $alreadyHas = true; break; }
    }
    if (!$alreadyHas) {
        $newItem = [
            'id'         => $itemId,
            'title'      => $invite['itemTitle'],
            'image'      => $invite['itemImage'] ?? '',
            'sharedFrom' => $fromUser,
        ];
        if ($category === 'music') {
            $newItem['type']     = $invite['itemMusicType'] ?? 'song';
            $newItem['artist']   = $invite['itemArtist']   ?? '';
            $newItem['featured'] = false;
        } else {
            $newItem['status'] = 'pending';
        }
        $myLists[$category][] = $newItem;
        file_put_contents($myListsFile, json_encode($myLists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Añadir colaborador al item del anfitrión
    $ownerListsFile = __DIR__ . '/' . $fromUser . '-lists.json';
    $ownerLists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
    if (file_exists($ownerListsFile)) {
        $raw = json_decode(file_get_contents($ownerListsFile), true);
        if (is_array($raw)) $ownerLists = array_merge($ownerLists, $raw);
    }
    foreach ($ownerLists[$category] as &$it) {
        if ($it['id'] === $itemId) {
            if (!isset($it['collaborators'])) $it['collaborators'] = [];
            if (!in_array($userKey, $it['collaborators'])) $it['collaborators'][] = $userKey;
            break;
        }
    }
    unset($it);
    file_put_contents($ownerListsFile, json_encode($ownerLists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Notificar al anfitrión
if (in_array($action, ['accept', 'reject']) && isset($invite['fromUser']) && array_key_exists($invite['fromUser'], $loginUsers)) {
    $hostNotifFile = __DIR__ . '/' . $invite['fromUser'] . '-item-invites.json';
    $hostNotifs = [];
    if (file_exists($hostNotifFile)) {
        $hostNotifs = json_decode(file_get_contents($hostNotifFile), true);
        if (!is_array($hostNotifs)) $hostNotifs = [];
    }
    $hostNotifs[] = [
        'id'        => 'resp_' . time() . '_' . rand(1000, 9999),
        'type'      => $action === 'accept' ? 'item-accepted' : 'item-rejected',
        'category'  => $invite['category'],
        'itemId'    => $invite['itemId'],
        'itemTitle' => $invite['itemTitle'],
        'fromLabel' => $loginUsers[$userKey]['label'],
        'fromUser'  => $userKey,
        'sentAt'    => time(),
    ];
    file_put_contents($hostNotifFile, json_encode($hostNotifs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

echo json_encode(['ok' => true]);
