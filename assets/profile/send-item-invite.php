<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body     = json_decode(file_get_contents('php://input'), true);
$toUser   = isset($body['toUser'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['toUser'])   : '';
$category = isset($body['category']) ? $body['category']                                         : '';
$itemId   = isset($body['itemId'])   ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['itemId'])   : '';

if (!$toUser || !$itemId || !in_array($category, ['movies', 'books', 'games', 'music'])) {
    echo json_encode(['error' => 'Datos inválidos']); exit;
}
if (!array_key_exists($toUser, $loginUsers)) { echo json_encode(['error' => 'Usuario destino inválido']); exit; }
if ($toUser === $userKey) { echo json_encode(['error' => 'No puedes invitarte a ti mismo']); exit; }

$listsFile = __DIR__ . '/' . $userKey . '-lists.json';
$lists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
if (file_exists($listsFile)) {
    $raw = json_decode(file_get_contents($listsFile), true);
    if (is_array($raw)) $lists = array_merge($lists, $raw);
}
$item = null;
foreach ($lists[$category] as $it) {
    if ($it['id'] === $itemId) { $item = $it; break; }
}
if (!$item) { echo json_encode(['error' => 'Elemento no encontrado']); exit; }

if (isset($item['collaborators']) && in_array($toUser, $item['collaborators'])) {
    echo json_encode(['error' => $loginUsers[$toUser]['label'] . ' ya es colaborador']); exit;
}

$inviteFile = __DIR__ . '/' . $toUser . '-item-invites.json';
$invites = [];
if (file_exists($inviteFile)) {
    $invites = json_decode(file_get_contents($inviteFile), true);
    if (!is_array($invites)) $invites = [];
}
foreach ($invites as $inv) {
    if (isset($inv['type']) && $inv['type'] === 'invite' && $inv['itemId'] === $itemId && $inv['fromUser'] === $userKey) {
        echo json_encode(['error' => 'Ya existe una invitación pendiente']); exit;
    }
}

$notifEntry = [
    'id'        => 'inv_' . time() . '_' . rand(1000, 9999),
    'type'      => 'invite',
    'category'  => $category,
    'itemId'    => $itemId,
    'itemTitle' => $item['title'],
    'itemImage' => $item['image'] ?? '',
    'fromUser'  => $userKey,
    'fromLabel' => $loginUsers[$userKey]['label'],
    'sentAt'    => time(),
];
if ($category === 'music') {
    $notifEntry['itemMusicType'] = $item['type']   ?? 'song';
    $notifEntry['itemArtist']    = $item['artist'] ?? '';
}
$invites[] = $notifEntry;
file_put_contents($inviteFile, json_encode($invites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
