<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];

$body   = json_decode(file_get_contents('php://input'), true);
$toUser = isset($body['toUser']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['toUser']) : '';

if (!$toUser) { echo json_encode(['error' => 'Datos incompletos']); exit; }
if (!array_key_exists($toUser, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
if ($toUser === $userKey) { echo json_encode(['error' => 'No puedes invitarte a ti mismo']); exit; }

$inviteFile = __DIR__ . '/' . $toUser . '-partner-invites.json';
$invites = [];
if (file_exists($inviteFile)) {
    $invites = json_decode(file_get_contents($inviteFile), true);
    if (!is_array($invites)) $invites = [];
}

foreach ($invites as $inv) {
    if ($inv['fromUser'] === $userKey) {
        echo json_encode(['error' => 'Ya tienes una invitación pendiente']); exit;
    }
}

$invites[] = [
    'id'        => 'pinv_' . time() . '_' . rand(1000, 9999),
    'fromUser'  => $userKey,
    'fromLabel' => $loginUsers[$userKey]['label'],
];
file_put_contents($inviteFile, json_encode($invites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);