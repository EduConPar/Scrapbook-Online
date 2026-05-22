<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/notif-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body     = json_decode(file_get_contents('php://input'), true);
$category = isset($body['category'])  ? preg_replace('/[^a-z]/i', '', $body['category']) : '';
$title    = isset($body['itemTitle']) ? mb_substr(trim($body['itemTitle']), 0, 120) : '';
$mtype    = isset($body['mtype'])     ? preg_replace('/[^a-z]/i', '', $body['mtype']) : '';
if (!$category || !$title) { echo json_encode(['error' => 'Faltan datos']); exit; }
if (!in_array($category, ['movies', 'books', 'games', 'music'], true)) {
    echo json_encode(['error' => 'Categoría inválida']); exit;
}
if ($mtype !== '' && !in_array($mtype, ['album', 'song'], true)) $mtype = '';

/* Para cada usuario que me sigue: añade una notif de tipo 'review'.
   Si ya existía una notif previa para el mismo título (editaste la reseña),
   la elimino primero para evitar duplicados. */
foreach ($loginUsers as $userKey => $userData) {
    if ($userKey === $me) continue;
    $file = __DIR__ . '/' . $userKey . '-profile.json';
    if (!file_exists($file)) continue;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) continue;
    $following = isset($data['following']) && is_array($data['following']) ? $data['following'] : [];
    if (!in_array($me, $following, true)) continue;

    removeProfileNotifsMatching($userKey, function($n) use ($me, $category, $title) {
        return isset($n['type']) && $n['type'] === 'review'
            && isset($n['fromUser']) && $n['fromUser'] === $me
            && isset($n['category']) && $n['category'] === $category
            && isset($n['itemTitle']) && $n['itemTitle'] === $title;
    });
    addProfileNotif($userKey, 'review', $me, [
        'category'  => $category,
        'itemTitle' => $title,
        'mtype'     => $mtype
    ]);
}

echo json_encode(['ok' => true]);
