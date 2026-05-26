<?php
/* ──────────────────────────────────────────────
   THEMES API — router único (backend SQL)
   ──────────────────────────────────────────────
   Endpoints (acción vía ?action= o body JSON):
     GET  ?action=get          → lista temas + activo
     POST ?action=save         → {name, colors}
     POST ?action=set-active   → {name}  (vacío = ninguno)
     POST ?action=delete       → {name}
   ────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';
require_once __DIR__ . '/theme-helpers.php';

$u      = requireAuth();
$me     = $u['key'];
$label  = $u['label'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$uid = userIdByKey($me);
if (!$uid) jsonError('Usuario no encontrado en BD', 500);

switch ($action) {

case 'get': {
    seedDefaultTheme($me, $label);
    /* Cargar todos los temas del usuario directamente desde SQL */
    $stmt = $pdo->prepare("SELECT name, colors, is_active, UNIX_TIMESTAMP(updated_at) AS updated_ts
                           FROM themes WHERE user_id = ?");
    $stmt->execute([$uid]);
    $themes = []; $active = '';
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $themes[$r['name']] = [
            'colors'    => json_decode($r['colors'], true) ?: [],
            'updatedAt' => (int)$r['updated_ts'],
        ];
        if ($r['is_active']) $active = $r['name'];
        /* Regenerar el CSS según el generador actual */
        if (validateThemeColors($themes[$r['name']]['colors'])) {
            @file_put_contents(
                themeCssFile($r['name'], $label),
                generateThemeCss(themeCssClassName($r['name'], $label), $themes[$r['name']]['colors'])
            );
        }
    }
    jsonResponse(['ok' => true, 'themes' => $themes ?: new stdClass(), 'active' => $active]);
}

case 'save': {
    $body   = jsonBody();
    $name   = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    $colors = $body['colors'] ?? null;
    if ($name === '' || mb_strlen($name) > 30) jsonError('Nombre inválido (1-30, letras/números/_/-)');
    if (!validateThemeColors($colors))         jsonError('Colores inválidos');

    $pdo->prepare("INSERT INTO themes (user_id, name, colors, is_active, updated_at)
                   VALUES (?, ?, ?, 0, NOW())
                   ON DUPLICATE KEY UPDATE colors=VALUES(colors), updated_at=NOW()")
        ->execute([$uid, $name, json_encode($colors, JSON_UNESCAPED_UNICODE)]);

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
    /* Validar que el tema existe (si no es vacío) */
    if ($name !== '') {
        $stmt = $pdo->prepare("SELECT 1 FROM themes WHERE user_id = ? AND name = ?");
        $stmt->execute([$uid, $name]);
        if (!$stmt->fetch()) jsonError('Tema no encontrado', 404);
    }
    /* Desactivar todos y activar el elegido en una transacción */
    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE themes SET is_active = 0 WHERE user_id = ?")->execute([$uid]);
        if ($name !== '') {
            $pdo->prepare("UPDATE themes SET is_active = 1 WHERE user_id = ? AND name = ?")
                ->execute([$uid, $name]);
        }
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    jsonResponse(['ok' => true, 'active' => $name]);
}

case 'delete': {
    $body = jsonBody();
    $name = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    if ($name === '') jsonError('Nombre inválido');
    $pdo->prepare("DELETE FROM themes WHERE user_id = ? AND name = ?")->execute([$uid, $name]);
    @unlink(themeCssFile($name, $label));
    jsonResponse(['ok' => true]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
