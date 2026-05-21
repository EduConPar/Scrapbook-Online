<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$plFile = __DIR__ . '/' . $userKey . '-playlists.json';

if (file_exists($plFile)) {
    $rawList = json_decode(file_get_contents($plFile), true);
    if (!is_array($rawList)) $rawList = [];

    $result = [];
    foreach ($rawList as $entry) {
        if (isset($entry['sharedFrom'])) {
            $ownerKey = $entry['sharedFrom'];
            if (!array_key_exists($ownerKey, $loginUsers)) continue;
            $ownerFile = __DIR__ . '/' . $ownerKey . '-playlists.json';
            if (!file_exists($ownerFile)) continue;
            $ownerPls  = json_decode(file_get_contents($ownerFile), true);
            if (!is_array($ownerPls)) continue;
            foreach ($ownerPls as $pl) {
                if ($pl['id'] === $entry['id']) {
                    $pl['sharedFrom']  = $ownerKey;
                    $pl['sharedLabel'] = $loginUsers[$ownerKey]['label'];
                    $result[] = $pl;
                    break;
                }
            }
        } else {
            $result[] = $entry;
        }
    }
    echo json_encode($result);
    exit;
}

// Migrate from old custom.json
$playlists  = [];
$customFile = __DIR__ . '/' . $userKey . '-custom.json';
if (file_exists($customFile)) {
    $tracks = json_decode(file_get_contents($customFile), true);
    if (is_array($tracks) && !empty($tracks)) {
        $playlists[] = ['id' => 'pl_legacy', 'name' => 'Mi playlist', 'owner' => $userKey, 'tracks' => $tracks];
        file_put_contents($plFile, json_encode($playlists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
echo json_encode($playlists);
