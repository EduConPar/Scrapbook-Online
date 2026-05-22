<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/notif-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$userKey = $_SESSION['user'];
if (!array_key_exists($userKey, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$list = readProfileNotifs($userKey);
foreach ($list as &$n) { $n['read'] = true; }
unset($n);
writeProfileNotifs($userKey, $list);

echo json_encode(['ok' => true]);
