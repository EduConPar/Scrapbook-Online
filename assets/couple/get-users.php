<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$currentUser = $_SESSION['user'];

$users = [];
foreach ($loginUsers as $key => $user) {
    if ($key === $currentUser) continue;
    $users[] = ['key' => $key, 'label' => $user['label']];
}
echo json_encode($users);