<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$with = isset($_GET['with']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['with']) : '';
if (!$with || !array_key_exists($with, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
if ($with === $me) { echo json_encode(['error' => 'Mismo usuario']); exit; }

$pair = [$me, $with];
sort($pair);
$file = __DIR__ . '/chat-' . $pair[0] . '-' . $pair[1] . '.json';
$data = ['messages' => [], 'lastSeen' => []];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) {
        if (isset($raw['messages']) && isset($raw['lastSeen'])) {
            $data = $raw;
        } else {
            $data['messages'] = $raw;
        }
    }
}

/* Marca como vista la última hora ahora mismo para mi */
$data['lastSeen'][$me] = time();
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true, 'messages' => $data['messages']]);
