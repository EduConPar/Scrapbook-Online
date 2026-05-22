<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$file = __DIR__ . '/' . $userKey . '-profile.json';
$data = ['quote' => '', 'posts' => [], 'bio' => '', 'pronouns' => '', 'age' => '', 'country' => '', 'steam' => '', 'discord' => '', 'twitter' => '', 'instagram' => '', 'following' => []];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) $data = array_merge($data, $raw);
}
echo json_encode($data);
