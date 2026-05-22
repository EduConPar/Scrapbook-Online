<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/notif-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$requester = $_SESSION['user'];
if (!array_key_exists($requester, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$target = isset($body['targetUser']) ? preg_replace('/[^a-z0-9_]/i', '', $body['targetUser']) : '';
if (!$target || !array_key_exists($target, $loginUsers)) { echo json_encode(['error' => 'Usuario no encontrado']); exit; }
if ($target === $requester) { echo json_encode(['error' => 'No puedes seguirte']); exit; }

$file = __DIR__ . '/' . $requester . '-profile.json';
$data = ['quote' => '', 'posts' => [], 'following' => []];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) $data = array_merge($data, $raw);
}
if (!isset($data['following']) || !is_array($data['following'])) $data['following'] = [];

$idx = array_search($target, $data['following'], true);
$following = false;
if ($idx === false) {
    $data['following'][] = $target;
    $following = true;
} else {
    array_splice($data['following'], $idx, 1);
    $following = false;
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($following) {
    addProfileNotif($target, 'follow', $requester);
} else {
    /* Si dejo de seguir, elimino la notif de follow correspondiente */
    removeProfileNotifsMatching($target, function($n) use ($requester) {
        return isset($n['type']) && $n['type'] === 'follow' && isset($n['fromUser']) && $n['fromUser'] === $requester;
    });
}

echo json_encode(['ok' => true, 'following' => $following, 'list' => $data['following']]);
