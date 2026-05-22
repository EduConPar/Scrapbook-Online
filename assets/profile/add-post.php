<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$text = isset($body['text']) ? mb_substr(trim($body['text']), 0, 1000) : '';
if (!$text) { echo json_encode(['error' => 'Texto vacío']); exit; }

$file = __DIR__ . '/' . $userKey . '-profile.json';
$data = ['quote' => '', 'posts' => []];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) $data = array_merge($data, $raw);
}

$post = ['id' => 'post_' . time() . '_' . rand(100, 999), 'text' => $text, 'createdAt' => time()];
array_unshift($data['posts'], $post);
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true, 'post' => $post]);
