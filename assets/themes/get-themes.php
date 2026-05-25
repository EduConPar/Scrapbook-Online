<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/theme-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$me = $_SESSION['user'];
if (!array_key_exists($me, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }
$label = $loginUsers[$me]['label'];

/* Asegura que el tema por defecto del usuario (Capi/Angie) esté en su lista */
seedDefaultTheme($me, $label);

$data = loadUserThemes($me);
$themes = is_array($data['themes']) || is_object($data['themes']) ? (array)$data['themes'] : [];

/* Regenera el CSS de cada tema con el generador actual (por si cambiaron las reglas) */
foreach ($themes as $name => $info) {
    if (!isset($info['colors']) || !validateThemeColors($info['colors'])) continue;
    $cssPath = themeCssFile($name, $label);
    $css     = generateThemeCss(themeCssClassName($name, $label), $info['colors']);
    @file_put_contents($cssPath, $css);
}

echo json_encode([
    'ok'     => true,
    'themes' => $themes,
    'active' => $data['active']
]);
