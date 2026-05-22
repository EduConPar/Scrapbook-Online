<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$file  = __DIR__ . '/' . $userKey . '-lists.json';
$lists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (is_array($data)) $lists = array_merge($lists, $data);
}
echo json_encode($lists);
