<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$requester = $_SESSION['user'];
if (!array_key_exists($requester, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$target = isset($_GET['user']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['user']) : '';
if (!$target || !array_key_exists($target, $loginUsers)) {
    echo json_encode(['error' => 'Usuario no encontrado']); exit;
}

$profileFile = __DIR__ . '/' . $target . '-profile.json';
$profile = ['quote' => '', 'posts' => [], 'bio' => '', 'pronouns' => '', 'age' => '', 'country' => '', 'steam' => '', 'discord' => '', 'twitter' => '', 'instagram' => ''];
if (file_exists($profileFile)) {
    $raw = json_decode(file_get_contents($profileFile), true);
    if (is_array($raw)) $profile = array_merge($profile, $raw);
}

$listsFile = __DIR__ . '/' . $target . '-lists.json';
$lists = ['movies' => [], 'books' => [], 'games' => [], 'music' => []];
if (file_exists($listsFile)) {
    $data = json_decode(file_get_contents($listsFile), true);
    if (is_array($data)) $lists = array_merge($lists, $data);
}

$reqFile = __DIR__ . '/' . $requester . '-profile.json';
$isFollowing = false;
if (file_exists($reqFile)) {
    $reqData = json_decode(file_get_contents($reqFile), true);
    if (is_array($reqData) && isset($reqData['following']) && is_array($reqData['following'])) {
        $isFollowing = in_array($target, $reqData['following'], true);
    }
}

echo json_encode([
    'userKey'     => $target,
    'label'       => $loginUsers[$target]['label'],
    'profile'     => $profile,
    'lists'       => $lists,
    'isFollowing' => $isFollowing
]);
