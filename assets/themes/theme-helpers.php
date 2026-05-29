<?php
/**
 * Helpers para el sistema de temas custom.
 *
 * Arquitectura:
 *  - Todos los CSS de la app (base, perfil, reproductor, melonarchive,
 *    calendario, themes) usan var(--token) en lugar de literales hex.
 *  - tokens.css define los valores por defecto (Win98).
 *  - themes.css redefine los tokens para body.capi / body.angie.
 *  - Cada tema custom del usuario produce un archivo en
 *    assets/themes/<themeName>-<userLabel>.css con un único bloque
 *    body.<themeName>-<userLabel> { --token: valor; ... }
 *  - El body recibe la clase <themeName>-<userLabel> y los tokens
 *    cascadean a TODA la UI sin reglas selector-específicas.
 */

/* Lista de tokens editables desde la app de Temas. Debe coincidir con
   COLOR_DEFS en apps/temas.php y con tokens.css. */
$THEME_COLOR_KEYS = [
    /* Superficies */
    'winBg', 'winBodyBg', 'surfaceDeep', 'insetBg',
    /* Inputs */
    'inputBg', 'inputText',
    /* Botones */
    'btnBg', 'btnText',
    /* Botón de inicio (taskbar) */
    'startBtnBg', 'startBtnText',
    /* Texto */
    'text', 'textMuted', 'textFaint', 'textInset',
    /* Acento / barra de título */
    'titlebarStart', 'titlebarEnd', 'titlebarText',
    'accent', 'accentText', 'accentDeep',
    /* Iconos de la title-bar (close / minimize / maximize) */
    'titlebarIconColor', 'titlebarIconBg',
    'titlebarIconBezelLight', 'titlebarIconBezelDark',
    /* Bordes */
    'border', 'borderStrong',
    /* Bezels */
    'bezelLight1', 'bezelLight2', 'bezelDark1', 'bezelDark2',
    /* Escritorio */
    'desktopBg',
    /* Estados */
    'linkText', 'errorText', 'warningBg', 'warningText',
    /* Selección / badges */
    'selectionBg', 'selectionText', 'badgeBg', 'badgeText',
    /* Decorativos */
    'starColor',
];

/* Mapeo key → variable CSS */
$THEME_TOKEN_MAP = [
    'winBg'         => '--win-bg',
    'winBodyBg'     => '--win-body-bg',
    'surfaceDeep'   => '--surface-deep',
    'insetBg'       => '--inset-bg',
    'inputBg'       => '--input-bg',
    'inputText'     => '--input-text',
    'btnBg'         => '--btn-bg',
    'btnText'       => '--btn-text',
    'startBtnBg'    => '--start-btn-bg',
    'startBtnText'  => '--start-btn-text',
    'text'          => '--text',
    'textMuted'     => '--text-muted',
    'textFaint'     => '--text-faint',
    'textInset'     => '--text-inset',
    'titlebarStart' => '--titlebar-start',
    'titlebarEnd'   => '--titlebar-end',
    'titlebarText'  => '--titlebar-text',
    'titlebarIconColor'      => '--titlebar-icon-color',
    'titlebarIconBg'         => '--titlebar-icon-bg',
    'titlebarIconBezelLight' => '--titlebar-icon-bezel-light',
    'titlebarIconBezelDark'  => '--titlebar-icon-bezel-dark',
    'accent'        => '--accent',
    'accentText'    => '--accent-text',
    'accentDeep'    => '--accent-deep',
    'border'        => '--border',
    'borderStrong'  => '--border-strong',
    'bezelLight1'   => '--bezel-light-1',
    'bezelLight2'   => '--bezel-light-2',
    'bezelDark1'    => '--bezel-dark-1',
    'bezelDark2'    => '--bezel-dark-2',
    'desktopBg'     => '--desktop-bg',
    'linkText'      => '--link-text',
    'errorText'     => '--error-text',
    'warningBg'     => '--warning-bg',
    'warningText'   => '--warning-text',
    'selectionBg'   => '--selection-bg',
    'selectionText' => '--selection-text',
    'badgeBg'       => '--badge-bg',
    'badgeText'     => '--badge-text',
    'starColor'     => '--star-color',
];

/* Valores por defecto Win98 (usados si el usuario no fija el token). */
$THEME_DEFAULTS = [
    'winBg'         => '#c0c0c0',
    'winBodyBg'     => '#c0c0c0',
    'surfaceDeep'   => '#c0c0c0',
    'insetBg'       => '#808080',
    'inputBg'       => '#ffffff',
    'inputText'     => '#000000',
    'btnBg'         => '#c0c0c0',
    'btnText'       => '#000000',
    'startBtnBg'    => '#c0c0c0',
    'startBtnText'  => '#000000',
    'text'          => '#000000',
    'textMuted'     => '#666666',
    'textFaint'     => '#808080',
    'textInset'     => '#808080',
    'titlebarStart' => '#000080',
    'titlebarEnd'   => '#1084d0',
    'titlebarText'  => '#ffffff',
    'titlebarIconColor'      => '#000000',
    'titlebarIconBg'         => '#c0c0c0',
    'titlebarIconBezelLight' => '#ffffff',
    'titlebarIconBezelDark'  => '#0a0a0a',
    'accent'        => '#000080',
    'accentText'    => '#ffffff',
    'accentDeep'    => '#00004a',
    'border'        => '#808080',
    'borderStrong'  => '#404040',
    'bezelLight1'   => '#ffffff',
    'bezelLight2'   => '#dfdfdf',
    'bezelDark1'    => '#0a0a0a',
    'bezelDark2'    => '#808080',
    'desktopBg'     => '#008080',
    'linkText'      => '#0000ff',
    'errorText'     => '#c00000',
    'warningBg'     => '#fffbe6',
    'warningText'   => '#444444',
    'selectionBg'   => '#000080',
    'selectionText' => '#ffffff',
    'badgeBg'       => '#d72638',
    'badgeText'     => '#ffffff',
    'starColor'     => '#ffd700',
];

/* ── Compat: claves antiguas (themes guardados antes del refactor) ── */
$THEME_LEGACY_MAP = [
    'bg'            => 'winBg',
    'taskbarBg'     => 'surfaceDeep',
    'windowBg'      => 'winBg',
    'windowText'    => 'text',
    'titleBarStart' => 'titlebarStart',
    'titleBarEnd'   => 'titlebarEnd',
    'titleBarText'  => 'titlebarText',
    'windowShadow'  => 'bezelDark1',
];

/* ── Temas por defecto para los perfiles fijos (Capi / Angie) ──
   Coinciden EXACTAMENTE con los valores que themes.css aplica a
   body.capi / body.angie. Sirven como punto de partida editable
   en la app de Temas. */
$CAPI_THEME_COLORS = [
    'winBg'         => '#2d2d2d',
    'winBodyBg'     => '#222222',
    'surfaceDeep'   => '#1e1e1e',
    'insetBg'       => '#111111',
    'inputBg'       => '#1a1a1a',
    'inputText'     => '#d0d0d0',
    'btnBg'         => '#3a3a3a',
    'btnText'       => '#d0d0d0',
    'startBtnBg'    => '#3a3a3a',
    'startBtnText'  => '#d0d0d0',
    'text'          => '#d0d0d0',
    'textMuted'     => '#888888',
    'textFaint'     => '#666666',
    'textInset'     => '#444444',
    'titlebarStart' => '#6b5500',
    'titlebarEnd'   => '#EDC001',
    'titlebarText'  => '#000000',
    'titlebarIconColor'      => '#000000',
    'titlebarIconBg'         => '#3a3a3a',
    'titlebarIconBezelLight' => '#555555',
    'titlebarIconBezelDark'  => '#111111',
    'accent'        => '#EDC001',
    'accentText'    => '#000000',
    'accentDeep'    => '#c8a000',
    'border'        => '#3a3a3a',
    'borderStrong'  => '#555555',
    'bezelLight1'   => '#555555',
    'bezelLight2'   => '#444444',
    'bezelDark1'    => '#111111',
    'bezelDark2'    => '#1a1a1a',
    'desktopBg'     => '#1a1a1a',
    'linkText'      => '#EDC001',
    'errorText'     => '#ff4f6e',
    'warningBg'     => '#2a2200',
    'warningText'   => '#e8cc80',
    'selectionBg'   => '#EDC001',
    'selectionText' => '#000000',
    'badgeBg'       => '#ff4f6e',
    'badgeText'     => '#ffffff',
    'starColor'     => '#EDC001',
];

$ANGIE_THEME_COLORS = [
    'winBg'         => '#F8D0C8',
    'winBodyBg'     => '#F8D0C8',
    'surfaceDeep'   => '#F8D0C8',
    'insetBg'       => '#F3BABA',
    'inputBg'       => '#F3BABA',
    'inputText'     => '#1a1a1a',
    'btnBg'         => '#5B744B',
    'btnText'       => '#F9DDD8',
    'startBtnBg'    => '#5B744B',
    'startBtnText'  => '#F9DDD8',
    'text'          => '#35522B',
    'textMuted'     => '#5B744B',
    'textFaint'     => '#799567',
    'textInset'     => '#799567',
    'titlebarStart' => '#5B744B',
    'titlebarEnd'   => '#799567',
    'titlebarText'  => '#F9DDD8',
    'titlebarIconColor'      => '#35522B',
    'titlebarIconBg'         => '#F8D0C8',
    'titlebarIconBezelLight' => '#F9DDD8',
    'titlebarIconBezelDark'  => '#35522B',
    'accent'        => '#799567',
    'accentText'    => '#F9DDD8',
    'accentDeep'    => '#5B744B',
    'border'        => '#F3BABA',
    'borderStrong'  => '#5B744B',
    'bezelLight1'   => '#F9DDD8',
    'bezelLight2'   => '#F8D0C8',
    'bezelDark1'    => '#35522B',
    'bezelDark2'    => '#5B744B',
    'desktopBg'     => '#F9DDD8',
    'linkText'      => '#5B744B',
    'errorText'     => '#c8456e',
    'warningBg'     => '#F3BABA',
    'warningText'   => '#35522B',
    'selectionBg'   => '#799567',
    'selectionText' => '#F9DDD8',
    'badgeBg'       => '#c8456e',
    'badgeText'     => '#F9DDD8',
    'starColor'     => '#c8a000',
];

function defaultThemeForUser($userKey) {
    /* Sin tema por defecto: TODOS los perfiles usan los tokens Win98 de
       tokens.css salvo que el usuario active uno custom. Las paletas
       $CAPI_THEME_COLORS / $ANGIE_THEME_COLORS se conservan abajo como
       referencia por si se quieren recrear desde la app de Temas. */
    return null;
}

/**
 * Siembra UNA VEZ el tema personal del usuario en su lista de temas (SQL):
 *  - user1 (Capi)  → tema "Capi"
 *  - user2 (Angie) → tema "Angie"
 *  - resto         → no se siembra nada (cae al Win98 por defecto de tokens.css)
 *
 * Solo se siembra si el usuario NO tiene ya un tema con ese nombre. Si lo
 * borró explícitamente desde la app, no vuelve.
 */
function seedDefaultTheme($userKey, $label) {
    $def = defaultThemeForUser($userKey);
    if (!$def) return;
    $uid = userIdByKey($userKey);
    if (!$uid) return;
    $pdo = themesPdo();
    /* ¿Ya tiene algún tema (cualquiera)? Si sí, respetamos su estado. */
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM themes WHERE user_id = ?");
    $stmt->execute([$uid]);
    if ((int)$stmt->fetchColumn() > 0) return;

    $pdo->prepare("INSERT INTO themes (user_id, name, colors, is_active, updated_at)
                   VALUES (?, ?, ?, 0, NOW())")
        ->execute([
            $uid, $def['name'],
            json_encode($def['colors'], JSON_UNESCAPED_UNICODE),
        ]);
    $cssPath = themeCssFile($def['name'], $label);
    $css     = generateThemeCss(themeCssClassName($def['name'], $label), $def['colors']);
    @file_put_contents($cssPath, $css);
}

function themesDir() {
    return __DIR__;
}

/* Asegura que la conexión PDO esté disponible y devuelve el handle.
   db.php se incluye aquí (dentro de la función) — `require_once` deja
   las variables del archivo en SCOPE LOCAL. Por eso el handle se mueve
   manualmente a $GLOBALS para que el resto del código (y futuras
   llamadas a esta función) lo recuperen vía `global $pdo`. */
function themesPdo(): PDO {
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    require_once dirname(__DIR__, 2) . '/db.php';
    $GLOBALS['pdo'] = $pdo;
    return $pdo;
}

/* Resuelve user_key (string) → usuarios.id (int). Cachea por petición. */
function userIdByKey(string $userKey): ?int {
    static $cache = [];
    if (array_key_exists($userKey, $cache)) return $cache[$userKey];
    $stmt = themesPdo()->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $cache[$userKey] = $id ? (int)$id : null;
}

/**
 * Carga TODOS los temas del usuario desde SQL en la misma forma que el
 * antiguo JSON: ['themes' => {name: {colors, updatedAt}}, 'active' => string].
 */
function loadUserThemes($userKey) {
    $uid = userIdByKey($userKey);
    if (!$uid) return ['themes' => new stdClass(), 'active' => ''];
    $rows = themesPdo()->prepare("SELECT name, colors, is_active, UNIX_TIMESTAMP(updated_at) AS updated_ts
                                  FROM themes WHERE user_id = ?");
    $rows->execute([$uid]);
    $themes = [];
    $active = '';
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $themes[$r['name']] = [
            'colors'    => json_decode($r['colors'], true) ?: [],
            'updatedAt' => (int)$r['updated_ts'],
        ];
        if ($r['is_active']) $active = $r['name'];
    }
    return ['themes' => $themes ?: new stdClass(), 'active' => $active];
}

/**
 * Sustituye TODOS los temas del usuario por los del array $data.
 * Mantiene compat con el contrato JSON antiguo. Usa transacción para que
 * un fallo no deje el estado a medias.
 */
function saveUserThemes($userKey, $data) {
    $uid = userIdByKey($userKey);
    if (!$uid) return;
    $pdo = themesPdo();
    $active  = isset($data['active']) ? (string)$data['active'] : '';
    $themes  = (array)($data['themes'] ?? []);
    $pdo->beginTransaction();
    try {
        /* Borra los que ya no están en $themes */
        $names = array_keys($themes);
        if (empty($names)) {
            $pdo->prepare("DELETE FROM themes WHERE user_id = ?")->execute([$uid]);
        } else {
            $place = implode(',', array_fill(0, count($names), '?'));
            $params = array_merge([$uid], $names);
            $pdo->prepare("DELETE FROM themes WHERE user_id = ? AND name NOT IN ($place)")
                ->execute($params);
        }
        /* Inserta o actualiza cada uno */
        $upsert = $pdo->prepare("INSERT INTO themes (user_id, name, colors, is_active, updated_at)
            VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))
            ON DUPLICATE KEY UPDATE colors=VALUES(colors), is_active=VALUES(is_active), updated_at=VALUES(updated_at)");
        foreach ($themes as $name => $info) {
            $ts = isset($info['updatedAt']) && is_numeric($info['updatedAt']) ? (int)$info['updatedAt'] : time();
            $upsert->execute([
                $uid, $name,
                json_encode($info['colors'] ?? [], JSON_UNESCAPED_UNICODE),
                ($name === $active) ? 1 : 0,
                $ts,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function sanitizeThemeName($name) {
    return preg_replace('/[^A-Za-z0-9_-]/', '', trim($name));
}

function themeCssFile($themeName, $label) {
    $safeTheme = sanitizeThemeName($themeName);
    $safeLabel = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    return themesDir() . '/' . $safeTheme . '-' . $safeLabel . '.css';
}

function themeCssRelPath($themeName, $label) {
    $safeTheme = sanitizeThemeName($themeName);
    $safeLabel = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    return 'assets/themes/' . $safeTheme . '-' . $safeLabel . '.css';
}

function themeCssClassName($themeName, $label) {
    $safeTheme = sanitizeThemeName($themeName);
    $safeLabel = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    return $safeTheme . '-' . $safeLabel;
}

/* Wallpaper PROPIO del usuario por nombre ({label}-wallpaper.ext), SIN el
   fallback genérico a base-wallpaper. Devuelve ruta relativa o ''. Sirve
   para resolver el fondo «efectivo» de un tema sin asset propio. */
function userNamedWallpaper($label) {
    $safe = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label));
    $dir  = dirname(__DIR__, 2) . '/assets/img/wallpapers';
    foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
        if (is_file("$dir/$safe-wallpaper.$ext")) {
            return "assets/img/wallpapers/$safe-wallpaper.$ext";
        }
    }
    return '';
}

/**
 * Copia el asset (wallpaper / start-icon) de un tema publicado al espacio
 * del usuario que lo descarga y devuelve la nueva ruta relativa. Así el
 * tema descargado es autónomo: no depende del fichero del autor.
 *
 * @param string $srcRel    ruta de origen (assets/img/{wallpapers|start-icons}/...)
 * @param string $kind      'wallpaper' | 'start-icon'
 * @param string $themeName nombre del tema en el espacio del descargador
 * @param string $label     label del usuario descargador
 * @return string nueva ruta relativa, o '' si no se pudo copiar.
 */
function copyThemeAsset($srcRel, $kind, $themeName, $label) {
    if (!is_string($srcRel) || $srcRel === '') return '';
    $sub    = ($kind === 'start-icon') ? 'start-icons' : 'wallpapers';
    $suffix = ($kind === 'start-icon') ? 'start-icon'  : 'wallpaper';
    /* Validar estrictamente el formato de la ruta de origen */
    if (!preg_match('#^assets/img/' . $sub . '/[A-Za-z0-9._-]+\.(jpe?g|png|gif|webp|svg)$#i', $srcRel)) return '';
    $root   = dirname(__DIR__, 2);
    $srcAbs = $root . '/' . $srcRel;
    if (!is_file($srcAbs)) return '';
    $ext       = strtolower(pathinfo($srcAbs, PATHINFO_EXTENSION));
    $safeTheme = sanitizeThemeName($themeName);
    $safeLabel = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label));
    $baseName  = 'theme-' . $safeTheme . '-' . $safeLabel . '-' . $suffix;
    $dir       = $root . '/assets/img/' . $sub;
    /* Borrar versiones previas del mismo asset (cualquier extensión) */
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'] as $e) {
        $old = $dir . '/' . $baseName . '.' . $e;
        if (is_file($old)) @unlink($old);
    }
    $destAbs = $dir . '/' . $baseName . '.' . $ext;
    if (!@copy($srcAbs, $destAbs)) return '';
    return 'assets/img/' . $sub . '/' . $baseName . '.' . $ext;
}

/* Normaliza un array de colores: aplica el mapa legacy y rellena defaults. */
function normalizeThemeColors($colors) {
    global $THEME_COLOR_KEYS, $THEME_DEFAULTS, $THEME_LEGACY_MAP;
    if (!is_array($colors)) return $THEME_DEFAULTS;
    /* Mapear claves legacy a las nuevas */
    $out = [];
    foreach ($colors as $k => $v) {
        if (isset($THEME_LEGACY_MAP[$k]) && !isset($colors[$THEME_LEGACY_MAP[$k]])) {
            $out[$THEME_LEGACY_MAP[$k]] = $v;
        } else {
            $out[$k] = $v;
        }
    }
    /* Migración suave para tokens nuevos: si un tema antiguo no tiene
       titlebarIcon{Color,Bg,BezelLight,BezelDark}, en vez de irse al
       default Win98 hereda los colores del botón / bezels generales. */
    $hex = '/^#[0-9a-f]{3,8}$/i';
    if (empty($out['titlebarIconColor']) && !empty($out['btnText']) && preg_match($hex, $out['btnText'])) {
        $out['titlebarIconColor'] = $out['btnText'];
    }
    if (empty($out['titlebarIconBg']) && !empty($out['btnBg']) && preg_match($hex, $out['btnBg'])) {
        $out['titlebarIconBg'] = $out['btnBg'];
    }
    if (empty($out['titlebarIconBezelLight']) && !empty($out['bezelLight1']) && preg_match($hex, $out['bezelLight1'])) {
        $out['titlebarIconBezelLight'] = $out['bezelLight1'];
    }
    if (empty($out['titlebarIconBezelDark']) && !empty($out['bezelDark1']) && preg_match($hex, $out['bezelDark1'])) {
        $out['titlebarIconBezelDark'] = $out['bezelDark1'];
    }
    /* Migración suave: si el tema no tiene startBtn{Bg,Text}, hereda los
       colores del botón normal para no romper el look existente. */
    if (empty($out['startBtnBg']) && !empty($out['btnBg']) && preg_match($hex, $out['btnBg'])) {
        $out['startBtnBg'] = $out['btnBg'];
    }
    if (empty($out['startBtnText']) && !empty($out['btnText']) && preg_match($hex, $out['btnText'])) {
        $out['startBtnText'] = $out['btnText'];
    }
    /* Rellenar con defaults los tokens que falten */
    foreach ($THEME_COLOR_KEYS as $k) {
        if (!isset($out[$k]) || !is_string($out[$k]) || !preg_match('/^#[0-9a-f]{3,8}$/i', $out[$k])) {
            $out[$k] = $THEME_DEFAULTS[$k];
        }
    }
    return $out;
}

function validateThemeColors($colors) {
    global $THEME_COLOR_KEYS, $THEME_LEGACY_MAP;
    if (!is_array($colors)) return false;
    /* Aceptamos temas guardados con claves legacy: si las claves nuevas
       no están pero hay equivalentes legacy, el tema sigue siendo válido. */
    foreach ($THEME_COLOR_KEYS as $k) {
        $val = null;
        if (isset($colors[$k])) {
            $val = $colors[$k];
        } else {
            $legacyKey = array_search($k, $THEME_LEGACY_MAP, true);
            if ($legacyKey !== false && isset($colors[$legacyKey])) $val = $colors[$legacyKey];
        }
        if ($val === null) continue; /* token ausente → se rellena con default en normalizeThemeColors */
        if (!preg_match('/^#[0-9a-f]{3,8}$/i', $val)) return false;
    }
    return true;
}

/**
 * Genera el CSS de un tema personalizado. Produce un único bloque de
 * variables CSS para body.<className>; el resto de la UI hereda los
 * estilos token-driven definidos en base/perfil/reproductor/etc.
 */
function generateThemeCss($className, $colors) {
    global $THEME_COLOR_KEYS, $THEME_TOKEN_MAP;
    $norm = normalizeThemeColors($colors);
    $sel = 'body.' . $className;
    $lines = [
        '/* Generado por la app de Temas. NO editar a mano.',
        '   Establece los tokens CSS para que cualquier app que use',
        '   var(--token) reciba la paleta del tema activo. */',
        '',
        $sel . ' {',
    ];
    foreach ($THEME_COLOR_KEYS as $k) {
        $var = $THEME_TOKEN_MAP[$k];
        $val = $norm[$k];
        $lines[] = '    ' . $var . ': ' . $val . ';';
    }
    /* Alias retro-compat (--t-*) para CSS antiguo */
    $lines[] = '';
    $lines[] = '    /* Alias retro-compat */';
    $lines[] = '    --t-bg:           ' . $norm['winBg']       . ';';
    $lines[] = '    --t-bg-alt:       ' . $norm['winBodyBg']   . ';';
    $lines[] = '    --t-text:         ' . $norm['text']        . ';';
    $lines[] = '    --t-text-muted:   ' . $norm['textMuted']   . ';';
    $lines[] = '    --t-accent:       ' . $norm['accent']      . ';';
    $lines[] = '    --t-accent-text:  ' . $norm['accentText']  . ';';
    $lines[] = '    --t-border:       ' . $norm['border']      . ';';
    $lines[] = '    --t-grad:         linear-gradient(90deg, ' . $norm['titlebarStart'] . ', ' . $norm['titlebarEnd'] . ');';
    $lines[] = '}';
    return implode("\n", $lines) . "\n";
}

/* Regenera el CSS del tema activo (si lo hay) según el generador actual. */
function refreshActiveThemeCss($userKey, $label) {
    $data = loadUserThemes($userKey);
    $active = !empty($data['active']) ? sanitizeThemeName($data['active']) : '';
    if ($active === '') return;
    $themes = (array)$data['themes'];
    if (!isset($themes[$active]) || !isset($themes[$active]['colors'])) return;
    if (!validateThemeColors($themes[$active]['colors'])) return;
    $cssPath = themeCssFile($active, $label);
    $css     = generateThemeCss(themeCssClassName($active, $label), $themes[$active]['colors']);
    @file_put_contents($cssPath, $css);
}
