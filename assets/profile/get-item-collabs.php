<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$category = isset($_GET['category']) ? $_GET['category'] : '';
$itemId   = isset($_GET['itemId'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['itemId']) : '';

if (!in_array($category, ['movies', 'books', 'games', 'music']) || !$itemId) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}

$userFile  = __DIR__ . '/' . $userKey . '-lists.json';
$userLists = [];
if (file_exists($userFile)) {
    $raw = json_decode(file_get_contents($userFile), true);
    if (is_array($raw)) $userLists = $raw;
}

$userItem = null;
foreach ($userLists[$category] ?? [] as $it) {
    if ($it['id'] === $itemId) { $userItem = $it; break; }
}
if (!$userItem) { echo json_encode(['error' => 'Item no encontrado']); exit; }

if (isset($userItem['sharedFrom'])) {
    $ownerKey = $userItem['sharedFrom'];
    if (!array_key_exists($ownerKey, $loginUsers)) { echo json_encode(['error' => 'Owner inválido']); exit; }
    $ownerFile  = __DIR__ . '/' . $ownerKey . '-lists.json';
    $ownerLists = [];
    if (file_exists($ownerFile)) {
        $raw = json_decode(file_get_contents($ownerFile), true);
        if (is_array($raw)) $ownerLists = $raw;
    }
    foreach ($ownerLists[$category] ?? [] as $it) {
        if ($it['id'] === $itemId) {
            echo json_encode(['ownerKey' => $ownerKey, 'collaborators' => $it['collaborators'] ?? []]);
            exit;
        }
    }
    echo json_encode(['ownerKey' => $ownerKey, 'collaborators' => []]);
} else {
    echo json_encode(['ownerKey' => $userKey, 'collaborators' => $userItem['collaborators'] ?? []]);
}
