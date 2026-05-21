<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body   = json_decode(file_get_contents('php://input'), true);
$postId = isset($body['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['id']) : '';
if (!$postId) { echo json_encode(['error' => 'ID inválido']); exit; }

$file = __DIR__ . '/' . $userKey . '-profile.json';
$data = ['quote' => '', 'posts' => []];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) $data = array_merge($data, $raw);
}
$data['posts'] = array_values(array_filter($data['posts'], function($p) use ($postId) {
    return $p['id'] !== $postId;
}));
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
