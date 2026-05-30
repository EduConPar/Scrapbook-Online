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
    $stmt = $pdo->prepare("SELECT name, colors, wallpaper, start_icon, is_active, is_public, is_downloaded, UNIX_TIMESTAMP(updated_at) AS updated_ts
                           FROM themes WHERE user_id = ?");
    $stmt->execute([$uid]);
    $themes = []; $active = '';
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $themes[$r['name']] = [
            'colors'     => json_decode($r['colors'], true) ?: [],
            'wallpaper'  => $r['wallpaper'] ?: '',
            'startIcon'  => $r['start_icon'] ?: '',
            'updatedAt'  => (int)$r['updated_ts'],
            'public'     => (bool)$r['is_public'],
            'downloaded' => (bool)$r['is_downloaded'],
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
    $body    = jsonBody();
    $name    = isset($body['name'])    ? sanitizeThemeName($body['name'])    : '';
    $oldName = isset($body['oldName']) ? sanitizeThemeName($body['oldName']) : '';
    $colors  = $body['colors'] ?? null;
    $downloaded = !empty($body['downloaded']) ? 1 : 0;
    if ($name === '' || mb_strlen($name) > 30) jsonError('Nombre inválido (1-30, letras/números/_/-)');
    if (!validateThemeColors($colors))         jsonError('Colores inválidos');

    /* Al descargar un tema de la biblioteca NO se duplican archivos: se guarda
       una referencia validada al asset original del autor. Si el autor lo
       borra, el render cae al wallpaper/icono global del descargador. */
    $newWp = ''; $newSi = '';
    if ($downloaded) {
        $srcWp = isset($body['wallpaper']) ? (string)$body['wallpaper'] : '';
        $srcSi = isset($body['startIcon']) ? (string)$body['startIcon'] : '';
        if ($srcWp !== '') $newWp = validateThemeAssetPath($srcWp, 'wallpaper');
        if ($srcSi !== '') $newSi = validateThemeAssetPath($srcSi, 'start-icon');
    }

    if ($oldName !== '' && $oldName !== $name) {
        /* RENOMBRAR: reemplaza el tema original en vez de crear otro.
           Conserva is_public / is_downloaded del original. */
        $chk = $pdo->prepare("SELECT 1 FROM themes WHERE user_id = ? AND name = ?");
        $chk->execute([$uid, $oldName]);
        if ($chk->fetch()) {
            /* ¿el nuevo nombre ya lo ocupa OTRO tema? */
            $dup = $pdo->prepare("SELECT 1 FROM themes WHERE user_id = ? AND name = ?");
            $dup->execute([$uid, $name]);
            if ($dup->fetch()) jsonError('Ya existe un tema con ese nombre');
            $pdo->prepare("UPDATE themes SET name = ?, colors = ?, updated_at = NOW()
                           WHERE user_id = ? AND name = ?")
                ->execute([$name, json_encode($colors, JSON_UNESCAPED_UNICODE), $uid, $oldName]);
            @unlink(themeCssFile($oldName, $label));   /* borrar CSS del nombre viejo */
        } else {
            /* el original ya no existe → insertar como nuevo */
            $pdo->prepare("INSERT INTO themes (user_id, name, colors, is_active, is_downloaded, updated_at)
                           VALUES (?, ?, ?, 0, ?, NOW())
                           ON DUPLICATE KEY UPDATE colors=VALUES(colors), updated_at=NOW()")
                ->execute([$uid, $name, json_encode($colors, JSON_UNESCAPED_UNICODE), $downloaded]);
        }
    } else {
        /* Alta o actualización normal. is_downloaded y los assets (fondo/icono)
           solo se fijan al INSERTAR (ON DUPLICATE no los toca → se conservan). */
        $pdo->prepare("INSERT INTO themes (user_id, name, colors, wallpaper, start_icon, is_active, is_downloaded, updated_at)
                       VALUES (?, ?, ?, ?, ?, 0, ?, NOW())
                       ON DUPLICATE KEY UPDATE colors=VALUES(colors), updated_at=NOW()")
            ->execute([$uid, $name, json_encode($colors, JSON_UNESCAPED_UNICODE),
                       $newWp ?: null, $newSi ?: null, $downloaded]);
    }

    file_put_contents(
        themeCssFile($name, $label),
        generateThemeCss(themeCssClassName($name, $label), $colors)
    );
    jsonResponse([
        'ok'        => true,
        'name'      => $name,
        'cssPath'   => themeCssRelPath($name, $label),
        'className' => themeCssClassName($name, $label),
        'wallpaper' => $newWp,
        'startIcon' => $newSi,
    ]);
}

case 'set-active': {
    $body = jsonBody();
    $name = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    $wallpaper = ''; $startIcon = '';
    /* Validar que el tema existe (si no es vacío) y leer sus assets */
    if ($name !== '') {
        $stmt = $pdo->prepare("SELECT wallpaper, start_icon FROM themes WHERE user_id = ? AND name = ?");
        $stmt->execute([$uid, $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonError('Tema no encontrado', 404);
        $wallpaper = $row['wallpaper'] ?: '';
        $startIcon = $row['start_icon'] ?: '';
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
    jsonResponse(['ok' => true, 'active' => $name, 'wallpaper' => $wallpaper, 'startIcon' => $startIcon]);
}

case 'delete': {
    $body = jsonBody();
    $name = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    if ($name === '') jsonError('Nombre inválido');
    $pdo->prepare("DELETE FROM themes WHERE user_id = ? AND name = ?")->execute([$uid, $name]);
    @unlink(themeCssFile($name, $label));
    jsonResponse(['ok' => true]);
}

/* Publicar / despublicar un tema en la biblioteca compartida */
case 'set-public': {
    $body   = jsonBody();
    $name   = isset($body['name']) ? sanitizeThemeName($body['name']) : '';
    $public = !empty($body['public']) ? 1 : 0;
    if ($name === '') jsonError('Nombre inválido');
    /* No se puede publicar un tema descargado de la biblioteca (no es original) */
    if ($public) {
        $chk = $pdo->prepare("SELECT is_downloaded FROM themes WHERE user_id = ? AND name = ?");
        $chk->execute([$uid, $name]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonError('Tema no encontrado', 404);
        if ((int)$row['is_downloaded'] === 1) jsonError('No puedes publicar un tema descargado de la biblioteca');
    }
    $stmt = $pdo->prepare("UPDATE themes SET is_public = ? WHERE user_id = ? AND name = ?");
    $stmt->execute([$public, $uid, $name]);
    if (!$stmt->rowCount()) {
        $chk = $pdo->prepare("SELECT 1 FROM themes WHERE user_id = ? AND name = ?");
        $chk->execute([$uid, $name]);
        if (!$chk->fetch()) jsonError('Tema no encontrado', 404);
    }
    jsonResponse(['ok' => true, 'public' => (bool)$public]);
}

/* Biblioteca: todos los temas públicos de todos los usuarios + autor */
case 'library': {
    $stmt = $pdo->query(
        "SELECT t.name, t.colors, t.wallpaper, t.start_icon, u.user_key, u.label,
                UNIX_TIMESTAMP(t.updated_at) AS updated_ts
         FROM themes t
         JOIN usuarios u ON t.user_id = u.id
         WHERE t.is_public = 1
         ORDER BY t.updated_at DESC"
    );
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        /* Asset «efectivo»: el propio del tema o, si no tiene, el fondo/icono
           global del autor (lo que realmente ve con ese tema). Así un tema
           publicado siempre lleva el fondo e icono del autor. */
        $wp = $r['wallpaper'] ?: userNamedWallpaper($r['label']);
        $si = $r['start_icon'] ?: (function_exists('getUserStartIcon') ? getUserStartIcon($r['label']) : '');
        $items[] = [
            'name'      => $r['name'],
            'colors'    => json_decode($r['colors'], true) ?: [],
            'wallpaper' => $wp ?: '',
            'startIcon' => $si ?: '',
            'userKey'   => $r['user_key'],
            'label'     => $r['label'],
            'image'     => function_exists('getUserImage') ? getUserImage($r['label']) : '',
            'updated'   => (int)$r['updated_ts'],
        ];
    }
    jsonResponse(['ok' => true, 'items' => $items]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
