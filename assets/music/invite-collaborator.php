<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body       = json_decode(file_get_contents('php://input'), true);
$playlistId = isset($body['playlistId']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['playlistId']) : '';
$toUser     = isset($body['toUser'])     ? preg_replace('/[^A-Za-z0-9_-]/', '', $body['toUser'])     : '';
if (!$playlistId || !$toUser) { echo json_encode(['error' => 'Datos incompletos']); exit; }
if (!array_key_exists($toUser, $loginUsers)) { echo json_encode(['error' => 'Usuario destino inválido']); exit; }
if ($toUser === $userKey) { echo json_encode(['error' => 'No puedes invitarte a ti mismo']); exit; }

$plFile    = __DIR__ . '/' . $userKey . '-playlists.json';
$playlists = [];
if (file_exists($plFile)) {
    $playlists = json_decode(file_get_contents($plFile), true);
    if (!is_array($playlists)) $playlists = [];
}
$playlist = null;
foreach ($playlists as $pl) {
    if ($pl['id'] === $playlistId) { $playlist = $pl; break; }
}
if (!$playlist) { echo json_encode(['error' => 'Playlist no encontrada']); exit; }

if (isset($playlist['collaborators']) && in_array($toUser, $playlist['collaborators'])) {
    echo json_encode(['error' => $loginUsers[$toUser]['label'] . ' ya es colaborador']); exit;
}

$inviteFile = __DIR__ . '/' . $toUser . '-invites.json';
$invites    = [];
if (file_exists($inviteFile)) {
    $invites = json_decode(file_get_contents($inviteFile), true);
    if (!is_array($invites)) $invites = [];
}
foreach ($invites as $inv) {
    if ($inv['playlistId'] === $playlistId) {
        echo json_encode(['error' => 'Ya existe una invitación pendiente']); exit;
    }
}

$invites[] = [
    'id'           => 'inv_' . time() . '_' . rand(1000, 9999),
    'playlistId'   => $playlistId,
    'playlistName' => $playlist['name'],
    'fromUser'     => $userKey,
    'fromLabel'    => $loginUsers[$userKey]['label'],
    'sentAt'       => time(),
];
file_put_contents($inviteFile, json_encode($invites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
