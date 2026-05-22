<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body     = json_decode(file_get_contents('php://input'), true);
$category = isset($body['category']) ? $body['category'] : '';
$items    = isset($body['items'])    ? $body['items']    : null;

if (!in_array($category, ['movies', 'books', 'games', 'music']) || !is_array($items)) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}

$file  = __DIR__ . '/' . $userKey . '-lists.json';
$lists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (is_array($data)) $lists = array_merge($lists, $data);
}

$isMusic = ($category === 'music');
$clean = [];
foreach ($items as $item) {
    if (!isset($item['title']) || !trim($item['title'])) continue;
    $entry = [
        'id'    => isset($item['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $item['id']) : uniqid(),
        'title' => mb_substr(trim($item['title']), 0, 200),
        'image' => isset($item['image']) ? mb_substr(trim($item['image']), 0, 2000) : '',
    ];
    if ($isMusic) {
        $entry['type']     = in_array($item['type'] ?? '', ['song', 'album']) ? $item['type'] : 'song';
        $entry['artist']   = mb_substr(trim($item['artist'] ?? ''), 0, 200);
        $entry['featured'] = !empty($item['featured']);
        foreach (['ytId', 'spotifyId', 'ytPlaylistId', 'spotifyAlbumId'] as $mf) {
            if (!empty($item[$mf])) $entry[$mf] = preg_replace('/[^A-Za-z0-9_-]/', '', $item[$mf]);
        }
    } else {
        $entry['status'] = in_array($item['status'] ?? '', ['pending', 'in-progress', 'completed'])
                           ? $item['status'] : 'pending';
    }
    if (isset($item['review']) && is_array($item['review'])) {
        $stars = isset($item['review']['stars']) ? round(floatval($item['review']['stars']) * 2) / 2 : 0;
        $entry['review'] = [
            'stars'   => max(0, min(5, $stars)),
            'comment' => mb_substr(trim($item['review']['comment'] ?? ''), 0, 1000),
        ];
    }
    if (isset($item['collaborators']) && is_array($item['collaborators'])) {
        $entry['collaborators'] = array_values(array_filter(array_map(
            function($u) { return preg_replace('/[^A-Za-z0-9_-]/', '', $u); },
            $item['collaborators']
        )));
    }
    if (isset($item['sharedFrom']) && array_key_exists($item['sharedFrom'], $loginUsers)) {
        $entry['sharedFrom'] = preg_replace('/[^A-Za-z0-9_-]/', '', $item['sharedFrom']);
    }
    $clean[] = $entry;
}
function pushNotifSL($toUser, $notif) {
    $f = __DIR__ . '/' . $toUser . '-item-invites.json';
    $n = [];
    if (file_exists($f)) { $r = json_decode(file_get_contents($f), true); if (is_array($r)) $n = $r; }
    $n[] = $notif;
    file_put_contents($f, json_encode($n, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Detect deleted items that had collaborators, remove from their lists and notify them
$newIds = array_column($clean, 'id');
foreach ($lists[$category] as $oldItem) {
    if (in_array($oldItem['id'], $newIds)) continue;
    if (empty($oldItem['collaborators'])) continue;
    foreach ($oldItem['collaborators'] as $collabKey) {
        $collabKey = preg_replace('/[^A-Za-z0-9_-]/', '', $collabKey);
        if (!array_key_exists($collabKey, $loginUsers)) continue;
        $collabFile  = __DIR__ . '/' . $collabKey . '-lists.json';
        $collabLists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
        if (file_exists($collabFile)) {
            $raw = json_decode(file_get_contents($collabFile), true);
            if (is_array($raw)) $collabLists = array_merge($collabLists, $raw);
        }
        $itemId = $oldItem['id'];
        $collabLists[$category] = array_values(array_filter(
            $collabLists[$category],
            function($it) use ($itemId) { return $it['id'] !== $itemId; }
        ));
        file_put_contents($collabFile, json_encode($collabLists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        pushNotifSL($collabKey, [
            'id'        => 'cd_' . time() . '_' . rand(1000, 9999),
            'type'      => 'collab-removed',
            'category'  => $category,
            'itemId'    => $itemId,
            'itemTitle' => $oldItem['title'] ?? '',
            'fromLabel' => $loginUsers[$userKey]['label'],
            'fromUser'  => $userKey,
            'sentAt'    => time(),
        ]);
    }
}

$lists[$category] = $clean;
file_put_contents($file, json_encode($lists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
