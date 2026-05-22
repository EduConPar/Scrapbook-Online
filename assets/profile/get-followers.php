<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$followers = [];
foreach ($loginUsers as $userKey => $userData) {
    if ($userKey === $me) continue;
    $file = __DIR__ . '/' . $userKey . '-profile.json';
    if (!file_exists($file)) continue;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) continue;
    $following = isset($data['following']) && is_array($data['following']) ? $data['following'] : [];
    if (in_array($me, $following, true)) $followers[] = $userKey;
}

echo json_encode(['ok' => true, 'followers' => $followers]);
