<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['error' => 'Datos inválidos']); exit; }

$file = __DIR__ . '/' . $userKey . '-profile.json';
$data = ['quote' => '', 'posts' => [], 'bio' => '', 'pronouns' => '', 'age' => '', 'country' => '', 'steam' => '', 'discord' => '', 'twitter' => '', 'instagram' => ''];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) $data = array_merge($data, $raw);
}

$data['bio']       = mb_substr(trim($body['bio']       ?? ''), 0, 200);
$data['pronouns']  = mb_substr(trim($body['pronouns']  ?? ''), 0, 30);
$data['age']       = mb_substr(preg_replace('/[^0-9]/', '', $body['age'] ?? ''), 0, 3);
$data['country']   = mb_substr(trim($body['country']   ?? ''), 0, 50);
$data['steam']     = mb_substr(trim($body['steam']     ?? ''), 0, 200);
$data['discord']   = mb_substr(trim($body['discord']   ?? ''), 0, 100);
$data['twitter']   = mb_substr(trim($body['twitter']   ?? ''), 0, 100);
$data['instagram'] = mb_substr(trim($body['instagram'] ?? ''), 0, 100);

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
