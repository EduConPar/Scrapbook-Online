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
$postId = isset($body['postId']) ? trim($body['postId']) : '';
if (!$target || !array_key_exists($target, $loginUsers)) { echo json_encode(['error' => 'Usuario no encontrado']); exit; }
if (!$postId) { echo json_encode(['error' => 'Post inválido']); exit; }

$file = __DIR__ . '/' . $target . '-profile.json';
if (!file_exists($file)) { echo json_encode(['error' => 'Sin posts']); exit; }
$data = json_decode(file_get_contents($file), true);
if (!is_array($data) || !isset($data['posts']) || !is_array($data['posts'])) {
    echo json_encode(['error' => 'Sin posts']); exit;
}

$found = false;
$liked = false;
$count = 0;
$postText = '';
foreach ($data['posts'] as &$post) {
    if (!isset($post['id']) || $post['id'] !== $postId) continue;
    $found = true;
    if (!isset($post['likes']) || !is_array($post['likes'])) $post['likes'] = [];
    $idx = array_search($requester, $post['likes'], true);
    if ($idx === false) {
        $post['likes'][] = $requester;
        $liked = true;
    } else {
        array_splice($post['likes'], $idx, 1);
        $liked = false;
    }
    $count = count($post['likes']);
    $postText = isset($post['text']) ? mb_substr($post['text'], 0, 80) : '';
    break;
}
unset($post);

if (!$found) { echo json_encode(['error' => 'Post no encontrado']); exit; }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($liked) {
    addProfileNotif($target, 'like', $requester, ['postId' => $postId, 'postText' => $postText]);
} else {
    removeProfileNotifsMatching($target, function($n) use ($requester, $postId) {
        return isset($n['type']) && $n['type'] === 'like'
            && isset($n['fromUser']) && $n['fromUser'] === $requester
            && isset($n['postId']) && $n['postId'] === $postId;
    });
}

echo json_encode(['ok' => true, 'liked' => $liked, 'count' => $count]);
