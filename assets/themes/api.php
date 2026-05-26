<?php
/* ──────────────────────────────────────────────
   THEMES API — router único para la app Temas
   ──────────────────────────────────────────────
   Endpoints (acción vía ?action= o body JSON):
     GET  ?action=get          → lista temas + activo
     POST ?action=save         → {name, colors}
     POST ?action=set-active   → {name}  (vacío = ninguno)
     POST ?action=delete       → {name}
   ────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once __DIR__ . '/theme-helpers.php';

$u      = requireAuth();
$me     = $u['key'];
$label  = $u['label'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

case 'get':
    seedDefaultTheme($me, $label);
    $data   = loadUserThemes($me);
    $themes = is_array($data['themes']) || is_object($data['themes']) ? (array)$data['themes'] : [];
    /* Regenera el CSS de cada tema con el generador actual */
    foreach ($themes as $name => $info) {
        if (!isset($info['colors']) || !validateThemeColors($info['colors'])) continue;
        @file_put_contents(
            themeCssFile($name, $label),
            generateThemeCss(themeCssClassName($name, $label), $info['colors'])
        );
    }
    jsonResponse(['ok' => true, 'themes' => $themes, 'active' => $data['active']]);

case 'save': {
    $body   = jsonBody();
    $name   = isset($body['name'])   ? sanitizeThemeName($body['name']) : '';
    $colors = $body['colors'] ?? null;
    if ($name === '' || mb_strlen($name) > 30) jsonError('Nombre inválido (1-30, letras/números/_/-)');
    if (!validateThemeColors($colors))         jsonError('Colores inválidos');

    $data = loadUserThemes($me);
    $data['themes'] = (array)$data['themes'];
    $data['themes'][$name] = ['colors' => $colors, 'updatedAt' => time()];
    saveUserThemes($me, $data);

    file_put_contents(
        themeCssFile($name, $label),
        generateThemeCss(themeCssClassName($name, $label), $colors)
    );
    jsonResponse([
        'ok'        => true,
        'name'      => $name,
        'cssPath'   => themeCssRelPath($name, $label),
        'className' => themeCssClassName($name, $label),
    ]);
}

case 'set-active': {
    $body = jsonBody();
    $name = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    $data = loadUserThemes($me);
    $data['themes'] = (array)$data['themes'];
    if ($name !== '' && !isset($data['themes'][$name])) jsonError('Tema no encontrado', 404);
    $data['active'] = $name;
    saveUserThemes($me, $data);
    jsonResponse(['ok' => true, 'active' => $name]);
}

case 'delete': {
    $body = jsonBody();
    $name = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    if ($name === '') jsonError('Nombre inválido');
    $data = loadUserThemes($me);
    $data['themes'] = (array)$data['themes'];
    if (isset($data['themes'][$name])) unset($data['themes'][$name]);
    if (($data['active'] ?? '') === $name) $data['active'] = '';
    saveUserThemes($me, $data);
    @unlink(themeCssFile($name, $label));
    jsonResponse(['ok' => true]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
