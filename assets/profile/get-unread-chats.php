<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$counts = [];
foreach ($loginUsers as $userKey => $userData) {
    if ($userKey === $me) continue;
    $pair = [$me, $userKey];
    sort($pair);
    $file = __DIR__ . '/chat-' . $pair[0] . '-' . $pair[1] . '.json';
    if (!file_exists($file)) continue;
    $raw = json_decode(file_get_contents($file), true);
    if (!is_array($raw)) continue;
    $messages = isset($raw['messages']) && is_array($raw['messages']) ? $raw['messages'] : (isset($raw[0]) ? $raw : []);
    $lastSeen = isset($raw['lastSeen']) && is_array($raw['lastSeen']) ? $raw['lastSeen'] : [];
    $myLastSeen = isset($lastSeen[$me]) ? (int)$lastSeen[$me] : 0;
    $unread = 0;
    foreach ($messages as $m) {
        if (!isset($m['from']) || $m['from'] === $me) continue;
        $ts = isset($m['sentAt']) ? (int)$m['sentAt'] : 0;
        if ($ts > $myLastSeen) $unread++;
    }
    if ($unread > 0) $counts[$userKey] = $unread;
}

echo json_encode(['ok' => true, 'counts' => $counts]);
