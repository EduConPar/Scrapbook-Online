<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body     = json_decode(file_get_contents('php://input'), true);
$action   = isset($body['action'])   ? $body['action']   : '';
$category = isset($body['category']) ? $body['category'] : '';
$itemId   = isset($body['itemId'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['itemId']) : '';

if (!in_array($action, ['leave', 'remove']) || !in_array($category, ['movies', 'books', 'games', 'music']) || !$itemId) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}

function loadLists($userK) {
    $f = __DIR__ . '/' . $userK . '-lists.json';
    $d = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
    if (file_exists($f)) { $r = json_decode(file_get_contents($f), true); if (is_array($r)) $d = array_merge($d, $r); }
    return $d;
}
function saveLists($userK, $lists) {
    file_put_contents(__DIR__ . '/' . $userK . '-lists.json', json_encode($lists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function pushNotif($toUser, $notif) {
    $f = __DIR__ . '/' . $toUser . '-item-invites.json';
    $n = [];
    if (file_exists($f)) { $r = json_decode(file_get_contents($f), true); if (is_array($r)) $n = $r; }
    $n[] = $notif;
    file_put_contents($f, json_encode($n, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($action === 'leave') {
    // Collaborator abandoning — current user IS the collaborator
    $myLists = loadLists($userKey);
    $ownerKey = null;
    $itemTitle = '';
    foreach ($myLists[$category] as $it) {
        if ($it['id'] === $itemId && isset($it['sharedFrom'])) {
            $ownerKey  = $it['sharedFrom'];
            $itemTitle = $it['title'];
            break;
        }
    }
    if (!$ownerKey || !array_key_exists($ownerKey, $loginUsers)) {
        echo json_encode(['error' => 'No eres colaborador de este elemento']); exit;
    }

    // Remove from collaborator's list
    $myLists[$category] = array_values(array_filter($myLists[$category], function($it) use ($itemId) { return $it['id'] !== $itemId; }));
    saveLists($userKey, $myLists);

    // Remove collaborator from owner's item
    $ownerLists = loadLists($ownerKey);
    foreach ($ownerLists[$category] as &$it) {
        if ($it['id'] === $itemId) {
            if (isset($it['collaborators'])) {
                $it['collaborators'] = array_values(array_filter($it['collaborators'], function($u) use ($userKey) { return $u !== $userKey; }));
            }
            $itemTitle = $it['title'];
            break;
        }
    }
    unset($it);
    saveLists($ownerKey, $ownerLists);

    // Notify owner
    pushNotif($ownerKey, [
        'id'        => 'cl_' . time() . '_' . rand(1000, 9999),
        'type'      => 'collab-left',
        'category'  => $category,
        'itemId'    => $itemId,
        'itemTitle' => $itemTitle,
        'fromLabel' => $loginUsers[$userKey]['label'],
        'fromUser'  => $userKey,
        'sentAt'    => time(),
    ]);

} elseif ($action === 'remove') {
    // Owner removing a collaborator
    $collaboratorUser = isset($body['collaboratorUser']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['collaboratorUser']) : '';
    if (!$collaboratorUser || !array_key_exists($collaboratorUser, $loginUsers)) {
        echo json_encode(['error' => 'Usuario colaborador inválido']); exit;
    }

    $myLists   = loadLists($userKey);
    $itemTitle = '';
    foreach ($myLists[$category] as &$it) {
        if ($it['id'] === $itemId) {
            if (!isset($it['collaborators']) || !in_array($collaboratorUser, $it['collaborators'])) {
                echo json_encode(['error' => 'El usuario no es colaborador']); exit;
            }
            $it['collaborators'] = array_values(array_filter($it['collaborators'], function($u) use ($collaboratorUser) { return $u !== $collaboratorUser; }));
            $itemTitle = $it['title'];
            break;
        }
    }
    unset($it);
    saveLists($userKey, $myLists);

    // Remove item from collaborator's list
    $collabLists = loadLists($collaboratorUser);
    $collabLists[$category] = array_values(array_filter($collabLists[$category], function($it) use ($itemId) { return $it['id'] !== $itemId; }));
    saveLists($collaboratorUser, $collabLists);

    // Notify collaborator
    pushNotif($collaboratorUser, [
        'id'        => 'cr_' . time() . '_' . rand(1000, 9999),
        'type'      => 'collab-removed',
        'category'  => $category,
        'itemId'    => $itemId,
        'itemTitle' => $itemTitle,
        'fromLabel' => $loginUsers[$userKey]['label'],
        'fromUser'  => $userKey,
        'sentAt'    => time(),
    ]);
}

echo json_encode(['ok' => true]);
