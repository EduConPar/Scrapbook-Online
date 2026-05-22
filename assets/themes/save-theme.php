<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/theme-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
$label = $loginUsers[$me]['label'];

$body   = json_decode(file_get_contents('php://input'), true);
$name   = isset($body['name'])    ? sanitizeThemeName($body['name']) : '';
$colors = isset($body['colors'])  ? $body['colors'] : null;

if ($name === '' || mb_strlen($name) > 30) {
    echo json_encode(['error' => 'Nombre inválido (1-30, letras/números/_/-)']); exit;
}
if (!validateThemeColors($colors)) {
    echo json_encode(['error' => 'Colores inválidos']); exit;
}

$data = loadUserThemes($me);
$data['themes'] = (array)$data['themes'];
$data['themes'][$name] = ['colors' => $colors, 'updatedAt' => time()];
saveUserThemes($me, $data);

/* Genera y guarda el CSS */
$cssPath = themeCssFile($name, $label);
$css     = generateThemeCss(themeCssClassName($name, $label), $colors);
file_put_contents($cssPath, $css);

echo json_encode(['ok' => true, 'name' => $name, 'cssPath' => themeCssRelPath($name, $label), 'className' => themeCssClassName($name, $label)]);
