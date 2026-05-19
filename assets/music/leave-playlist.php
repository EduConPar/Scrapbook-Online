<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body       = json_decode(file_get_contents('php://input'), true);
$id         = isset($body['id'])         ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['id'])         : '';
$sharedFrom = isset($body['sharedFrom']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['sharedFrom']) : '';
if (!$id || !$sharedFrom) { echo json_encode(['error' => 'Datos incompletos']); exit; }
if (!array_key_exists($sharedFrom, $loginUsers)) { echo json_encode(['error' => 'Propietario inválido']); exit; }

$myFile = __DIR__ . '/' . $userKey . '-playlists.json';
$myPls  = [];
if (file_exists($myFile)) {
    $myPls = json_decode(file_get_contents($myFile), true);
    if (!is_array($myPls)) $myPls = [];
}
$myPls = array_values(array_filter($myPls, function($pl) use ($id) { return $pl['id'] !== $id; }));
file_put_contents($myFile, json_encode($myPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$ownerFile = __DIR__ . '/' . $sharedFrom . '-playlists.json';
$ownerPls  = [];
if (file_exists($ownerFile)) {
    $ownerPls = json_decode(file_get_contents($ownerFile), true);
    if (!is_array($ownerPls)) $ownerPls = [];
}
foreach ($ownerPls as &$pl) {
    if ($pl['id'] === $id && isset($pl['collaborators'])) {
        $pl['collaborators'] = array_values(array_filter($pl['collaborators'], function($c) use ($userKey) {
            return $c !== $userKey;
        }));
        break;
    }
}
unset($pl);
file_put_contents($ownerFile, json_encode($ownerPls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true]);
