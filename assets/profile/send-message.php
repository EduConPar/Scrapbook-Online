<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$to   = isset($body['to'])   ? preg_replace('/[^a-z0-9_]/i', '', $body['to']) : '';
$text = isset($body['text']) ? mb_substr(trim($body['text']), 0, 2000) : '';
if (!$to || !array_key_exists($to, $loginUsers)) { echo json_encode(['error' => 'Destinatario inválido']); exit; }
if ($to === $me) { echo json_encode(['error' => 'No puedes hablarte a ti mismo']); exit; }
if ($text === '') { echo json_encode(['error' => 'Mensaje vacío']); exit; }

/* Comprobar que se siguen mutuamente */
function followsMe($userKey, $me) {
    $file = __DIR__ . '/' . $userKey . '-profile.json';
    if (!file_exists($file)) return false;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) return false;
    $f = isset($data['following']) && is_array($data['following']) ? $data['following'] : [];
    return in_array($me, $f, true);
}
function iFollow($me, $target) {
    return followsMe($me, $target);
}
if (!iFollow($me, $to) || !followsMe($to, $me)) {
    echo json_encode(['error' => 'Necesitas seguirse mutuamente para hablar']); exit;
}

$pair = [$me, $to];
sort($pair);
$file = __DIR__ . '/chat-' . $pair[0] . '-' . $pair[1] . '.json';

$data = ['messages' => [], 'lastSeen' => []];
if (file_exists($file)) {
    $raw = json_decode(file_get_contents($file), true);
    if (is_array($raw)) {
        if (isset($raw['messages']) && isset($raw['lastSeen'])) {
            $data = $raw;
        } else {
            /* formato antiguo (array plano) */
            $data['messages'] = $raw;
        }
    }
}

$now = time();
$msg = [
    'id'     => 'msg_' . $now . '_' . rand(100, 9999),
    'from'   => $me,
    'text'   => $text,
    'sentAt' => $now
];
$data['messages'][] = $msg;
if (count($data['messages']) > 500) $data['messages'] = array_slice($data['messages'], -500);
$data['lastSeen'][$me] = $now; /* el remitente ya ha "visto" su propio mensaje */
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true, 'message' => $msg]);
