<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];

$inviteFile = __DIR__ . '/' . $userKey . '-partner-invites.json';
if (!file_exists($inviteFile)) { echo json_encode([]); exit; }

$invites = json_decode(file_get_contents($inviteFile), true);
echo json_encode(is_array($invites) ? $invites : []);