<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/theme-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$name = isset($body['name']) ? sanitizeThemeName($body['name']) : '';

$data = loadUserThemes($me);
$data['themes'] = (array)$data['themes'];
if ($name !== '' && !isset($data['themes'][$name])) {
    echo json_encode(['error' => 'Tema no encontrado']); exit;
}
$data['active'] = $name;
saveUserThemes($me, $data);

echo json_encode(['ok' => true, 'active' => $name]);
