<?php
/* ──────────────────────────────────────────────────────────────────────
   MOBILE.PHP — Entrada móvil estilo "feature phone" (Nokia/Symbian).
   ──────────────────────────────────────────────────────────────────────
   - Lista de apps en orden alfabético, una por fila.
   - Sin escritorio, sin carpetas, sin arrastrar iconos.
   - Reloj y fecha en la barra de estado superior.
   - Manifest PWA + banner de instalación (Android Chrome via
     beforeinstallprompt; iOS Safari muestra instrucciones manuales).
   - Sesión persistente 30 días → al abrir la PWA va directo aquí sin
     pedir login si el usuario ya entró alguna vez en este dispositivo.
   ────────────────────────────────────────────────────────────────────── */
require_once __DIR__ . '/assets/mobile-detect.php';
setLongSessionCookie();
/* Cookie de bypass del interstitial de ngrok-free. Ver mobile-landing.php
   para detalle. Inocua fuera de túneles ngrok. */
setcookie('abuse_interstitial', 'true', [
    'expires'  => time() + 86400 * 30,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/assets/config.php';

/* MARCADOR PWA
   El start_url del manifest incluye ?pwa=1. Si llega esa marca,
   activamos $_SESSION['is_pwa'] = true para esta sesión: a partir de
   aquí, el usuario navega dentro de Melon Hub. Las navegaciones
   internas (sin ?pwa=1) seguirán siendo PWA gracias a la sesión. */
if (isset($_GET['pwa'])) {
    $_SESSION['is_pwa'] = true;
}

/* AUTO-LOGIN POR TOKEN
   Si la PWA fue instalada desde la landing, su start_url incluye
   ?t=TOKEN. Lo consumimos AQUÍ antes del check de sesión. Si el token
   es válido, montamos $_SESSION['user'] y redirigimos a la URL limpia
   (sin token, sin ?pwa) para no dejarlos en address bar / referers. */
if (isset($_GET['t']) && (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']]))) {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/assets/mobile-token.php';
    $userKey = mtConsumeToken($pdo, (string)$_GET['t']);
    if ($userKey !== null && isset($loginUsers[$userKey])) {
        $_SESSION['user'] = $userKey;
    }
}
/* Si traemos ?pwa o ?t en la URL, redirigimos a la URL limpia. */
if (isset($_GET['pwa']) || isset($_GET['t'])) {
    header('Location: mobile.php');
    exit;
}

if (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    /* Móvil sin sesión → al pitch/login de la PWA. */
    header('Location: mobile-landing.php');
    exit;
}

/* CIERRE ESTRICTO
   El usuario tiene sesión, pero ¿está dentro de la PWA? Si no, lo
   sacamos a la landing — la única forma de entrar a Melon Hub es por
   el icono. (Server-side best-effort; el doble check JS de abajo
   captura el caso Android donde las cookies son compartidas.) */
if (empty($_SESSION['is_pwa'])) {
    header('Location: mobile-landing.php');
    exit;
}
$userKey   = $_SESSION['user'];
$userLabel = $loginUsers[$userKey]['label'];

$projectBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

/* Token de dispositivo PARA EL MANIFEST. Si el usuario instala la PWA
   desde aquí (no desde la landing), también queremos que su start_url
   contenga el token y haga auto-login al abrirla — necesario en iOS
   16.4+ donde la PWA tiene cookie jar aislado del navegador.
   Cacheamos el token por sesión para no crear uno nuevo en cada
   render. */
$tokenForManifest = '';
if (empty($_SESSION['device_token'])) {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/assets/mobile-token.php';
    $uid = (int)$pdo->query('SELECT id FROM usuarios WHERE user_key = '
                            . $pdo->quote($userKey))->fetchColumn();
    if ($uid > 0) {
        $_SESSION['device_token'] = mtCreateToken($pdo, $uid, $_SERVER['HTTP_USER_AGENT'] ?? null);
    }
}
$tokenForManifest = (string)($_SESSION['device_token'] ?? '');

/* Lista de apps disponibles. Las marcadas como `external=true` abren en
   pestaña nueva. Las marcadas como `wip=true` son apps que en el
   escritorio existen como parciales (perfil, reproductor) y todavía no
   tienen entrada standalone para móvil — apuntan al placeholder. */
$apps = [
    ['name' => 'Calendario',   'url' => 'apps/calendario.php',                                                  'emoji' => '📅', 'icon' => null,                                       'external' => false, 'wip' => false],
    ['name' => 'Companion',    'url' => 'https://helldiverscompanion.com',                                      'emoji' => '💀', 'icon' => 'assets/img/appIcons/companionIcon.png',    'external' => true,  'wip' => false],
    ['name' => 'D&D',          'url' => 'apps/dnd.php',                                                         'emoji' => '⚔',  'icon' => null,                                       'external' => false, 'wip' => false],
    ['name' => 'Dibujo',       'url' => 'https://excalidraw.com/#room=scrapbook-melon,clave-secreta-fija',      'emoji' => '✏️', 'icon' => null,                                       'external' => true,  'wip' => false],
    ['name' => 'Galería',      'url' => 'apps/galeria.php',                                                     'emoji' => '🖼', 'icon' => null,                                       'external' => false, 'wip' => false],
    ['name' => 'MelonArchive', 'url' => 'apps/melonarchive.php',                                                'emoji' => '📼', 'icon' => 'assets/img/appIcons/melonArchiveIcon.png', 'external' => false, 'wip' => false],
    ['name' => 'Música',       'url' => 'apps/musica-mobile.php',                                               'emoji' => '🎵', 'icon' => null,                                       'external' => false, 'wip' => false],
    ['name' => 'Perfil',       'url' => 'apps/perfil-mobile.php',                                               'emoji' => '👤', 'icon' => 'assets/img/appIcons/profileIcon.png',      'external' => false, 'wip' => false],
    ['name' => 'Temas',        'url' => 'apps/temas.php',                                                       'emoji' => '🎨', 'icon' => 'assets/img/appIcons/temasIcon.png',        'external' => false, 'wip' => false],
    ['name' => 'Tienda',       'url' => 'apps/tienda.php',                                                      'emoji' => '🛒', 'icon' => 'assets/img/appIcons/tiendaIcon.png',       'external' => false, 'wip' => false],
];
usort($apps, fn($a, $b) => strcasecmp($a['name'], $b['name']));

/* Avatar del usuario para mostrar en la cabecera. */
$userImg = '';
$safe = preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel);
foreach (['png','jpg','jpeg','gif'] as $ext) {
    if (file_exists(__DIR__ . "/assets/img/{$safe}.{$ext}")) {
        $userImg = "assets/img/{$safe}.{$ext}";
        break;
    }
}

/* ── TEMA ACTIVO DEL USUARIO ──
   Misma lógica que desktop-base.php: cargamos el tema del usuario para
   aplicar su paleta de colores (border colors, accent, background…)
   sobre el look base de Win98. Si no tiene tema custom, body queda con
   los tokens por defecto de tokens.css. */
require_once __DIR__ . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(__DIR__ . '/' . $activeThemeCss)) $activeThemeCss = '';
}
$wallpaper = getUserWallpaper($userLabel);

/* Color de fondo del tema del usuario para inyectar en
   <meta name="theme-color">. Android lo usa para tintar el área donde
   está la barra del SO en modo standalone — sin esto se queda el teal
   del default y no coincide con el tema. */
$themeBgColor = '#000000';   /* fallback por defecto = negro */
if ($activeTheme !== '' && isset($_userThemes['themes'][$activeTheme]['colors']['desktopBg'])) {
    $candidate = (string)$_userThemes['themes'][$activeTheme]['colors']['desktopBg'];
    /* Sanitizar: solo aceptamos un hex #RRGGBB o #RGB válido. */
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $candidate)) {
        $themeBgColor = $candidate;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- PWA GUARD: double-check client-side. En Android la PWA y el
         navegador comparten cookies, así que el chequeo server-side
         (is_pwa en sesión) puede colarse. Aquí miramos el display-mode
         REAL: si la página no se está renderizando como standalone
         (icono de la home), redirigimos a la landing antes de pintar
         nada. Inline + síncrono para evitar flash. -->
    <script>
    (function(){
        var sa = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
              || window.navigator.standalone === true;
        if (!sa) window.location.replace('mobile-landing.php');
    })();
    /* --mh-vh: viewport height en px sincronizado con window.innerHeight.
       Sobrevive al bfcache (al volver de otra app, refrescamos en
       `pageshow`) → el footer NO se queda descuelgado fuera del viewport. */
    (function(){
        function setVh(){
            document.documentElement.style.setProperty('--mh-vh', window.innerHeight + 'px');
        }
        setVh();
        window.addEventListener('resize', setVh);
        window.addEventListener('orientationchange', setVh);
        window.addEventListener('pageshow', setVh);
        window.addEventListener('visibilitychange', setVh);
    })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <!-- theme-color = mismo color que el fondo del tema del usuario,
         para que la barra del SO en Android no destaque. -->
    <meta name="theme-color" content="<?= htmlspecialchars($themeBgColor) ?>">
    <title>Melon Hub — <?= htmlspecialchars($userLabel) ?></title>
    <link rel="icon" href="data:,">
    <link rel="manifest" href="manifest.php<?= $tokenForManifest !== '' ? '?token=' . htmlspecialchars($tokenForManifest) : '' ?>">
    <link rel="apple-touch-icon" href="assets/img/start-icons/capi-start-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Melon Hub">
    <!-- Mismo stack visual que el escritorio Win98 + tema del usuario -->
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?= htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/mobile-theme.css?v=<?= filemtime(__DIR__ . '/assets/css/mobile-theme.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
        /* Tweaks específicos de mobile.php — todo lo común vive en
           assets/css/mobile-theme.css. Aquí solo el ajuste de la lista
           de apps para que ocupe el flex restante. */
        .mh-apps { flex: 1; min-height: 0; }

        /* ── SPA SHELL ──
           Dos "vistas" superpuestas: el launcher y un iframe donde
           cargan las sub-apps. Al tap en una app icono → ocultamos el
           launcher y mostramos el iframe con la URL. Al volver al
           menú (postMessage desde la app) revertimos. La sesión, el
           player YT y el mini-player viven aquí y nunca se descargan
           → la música sigue sonando aunque navegues a otra app. */
        #shell-app {
            position: fixed;
            top: 0; left: 0; right: 0;
            bottom: 0;
            z-index: 60;
            background: var(--desktop-bg, #000);
        }
        #shell-app[hidden] { display: none !important; }
        #app-frame {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
            background: transparent;
        }

        /* ── WIDGET FLOTANTE (vinilo) ──
           Reemplaza el mini-player bar. Es un disco circular sin
           fondo, flotando sobre el viewport. Se mueve manteniendo
           pulsado (long-press → drag). Tap rápido abre el fullscreen. */
        .mu-widget {
            position: fixed;
            bottom: calc(90px + env(safe-area-inset-bottom, 0px));
            right: 16px;
            width: 84px;
            height: 84px;
            z-index: 70;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            touch-action: none;
            -webkit-user-select: none;
            user-select: none;
            cursor: grab;
            will-change: transform;
            filter: drop-shadow(0 6px 14px rgba(0,0,0,0.6));
        }
        .mu-widget.visible { opacity: 1; pointer-events: auto; }
        .mu-widget.dragging { transition: none; z-index: 100; cursor: grabbing; }
        .mu-widget-disc {
            position: absolute; inset: 0;
            border-radius: 50%;
            background:
                repeating-radial-gradient(circle at center,
                    rgba(255,255,255,0.04) 0,
                    rgba(255,255,255,0.04) 1px,
                    transparent 1px,
                    transparent 5px),
                radial-gradient(circle at center, #1f1f1f 0%, #0a0a0a 70%, #050505 100%);
            animation: mu-spin 6s linear infinite;
        }
        .mu-widget-disc.paused { animation-play-state: paused; }
        .mu-widget-disc::after {
            content: '';
            position: absolute; inset: 4px;
            border-radius: 50%;
            box-shadow:
                inset 8px -14px 22px rgba(255,255,255,0.04),
                inset -4px 6px 14px rgba(0,0,0,0.6);
            pointer-events: none;
        }
        .mu-widget-label {
            position: absolute;
            top: 50%; left: 50%;
            width: 50%; height: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background-color: var(--accent, #000080);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow: hidden;
            box-shadow: 0 0 0 1.5px rgba(0,0,0,0.6), inset 0 0 0 1px rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            color: rgba(255,255,255,0.85);
        }
        .mu-widget-hole {
            position: absolute;
            top: 50%; left: 50%;
            width: 6%; height: 6%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: #000;
            z-index: 2;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.15);
        }
        @keyframes mu-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* ── FULLSCREEN PLAYER (versión completa con LCD + marquee) ── */
        .mu-full {
            --lcd:      var(--accent, #00ff88);
            --lcd-soft: color-mix(in srgb, var(--accent, #00ff88) 65%, transparent);
            --lcd-dim:  color-mix(in srgb, var(--accent, #00ff88) 35%, transparent);
            --lcd-veil: color-mix(in srgb, var(--accent, #00ff88) 5%, transparent);
            position: fixed; inset: 0;
            z-index: 200;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            overflow: hidden;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .mu-full.visible { transform: translateY(0); }
        .mu-full::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(to bottom,
                var(--lcd-veil) 0,
                var(--lcd-veil) 1px,
                transparent 1px,
                transparent 3px);
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 999;
        }
        .mu-full-splash { display: none; }
        .mu-full-window {
            position: relative; z-index: 2;
            width: 100%; height: 100%;
            margin: 0;
            display: flex; flex-direction: column;
            box-sizing: border-box;
            background: transparent;
            box-shadow: none;
            padding: 3px;
        }
        .mu-full-window > .title-bar { flex-shrink: 0; }
        .mu-full-body {
            flex: 1;
            display: flex; flex-direction: column;
            min-height: 0;
            margin: 0;
            padding: 10px 12px;
            background: transparent;
            overflow: hidden;
            gap: 10px;
        }
        /* Display sunken con vinilo. Brillo radial + scanlines. */
        .mu-full-display {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 12px;
            min-height: 0;
            background: var(--input-bg, #000);
            background-image:
                radial-gradient(ellipse at center, rgba(0,0,0,0) 40%, rgba(0,0,0,0.35) 100%);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a);
            position: relative;
            overflow: hidden;
        }
        .mu-full-display::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(to bottom,
                rgba(255,255,255,0.025) 0,
                rgba(255,255,255,0.025) 1px,
                transparent 1px,
                transparent 3px);
            pointer-events: none;
        }
        .mu-vinyl-wrap {
            width: min(72vw, 300px);
            max-height: 100%;
            aspect-ratio: 1;
            position: relative;
            filter: drop-shadow(0 10px 18px rgba(0,0,0,0.65));
        }
        .mu-vinyl {
            position: absolute; inset: 0;
            border-radius: 50%;
            background:
                repeating-radial-gradient(circle at center,
                    rgba(255,255,255,0.04) 0,
                    rgba(255,255,255,0.04) 1px,
                    transparent 1px,
                    transparent 5px),
                radial-gradient(circle at center,
                    #1f1f1f 0%, #0a0a0a 70%, #050505 100%);
            animation: mu-spin 6s linear infinite;
        }
        .mu-vinyl.paused { animation-play-state: paused; }
        .mu-vinyl::after {
            content: '';
            position: absolute; inset: 6px;
            border-radius: 50%;
            box-shadow:
                inset 12px -20px 30px rgba(255,255,255,0.04),
                inset -6px 8px 18px rgba(0,0,0,0.6);
            pointer-events: none;
        }
        .mu-vinyl-label {
            position: absolute;
            top: 50%; left: 50%;
            width: 36%; height: 36%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background-color: var(--accent, #000080);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            box-shadow:
                0 0 0 2px rgba(0,0,0,0.5),
                inset 0 0 0 1px rgba(255,255,255,0.12);
            overflow: hidden;
        }
        .mu-vinyl-label.empty {
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            color: var(--accent-text, #fff);
        }
        .mu-vinyl-hole {
            position: absolute;
            top: 50%; left: 50%;
            width: 3.2%; height: 3.2%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: #050505;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.18);
            z-index: 2;
        }

        /* Info LCD: marker blink + glow + scanlines internas. */
        .mu-full-info {
            display: flex; align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--input-bg, #fff);
            color: var(--lcd);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a);
            box-sizing: border-box;
            flex-shrink: 0;
            min-height: 50px;
            position: relative;
            overflow: hidden;
        }
        .mu-full-info::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(to bottom,
                var(--lcd-veil) 0,
                var(--lcd-veil) 1px,
                transparent 1px,
                transparent 3px);
            pointer-events: none;
        }
        .mu-full-info-marker {
            font-family: 'VT323', monospace;
            font-size: 24px;
            color: var(--lcd);
            text-shadow: 0 0 8px var(--lcd-soft);
            line-height: 1;
            flex-shrink: 0;
            animation: mu-blink 1s steps(2) infinite;
        }
        @keyframes mu-blink {
            from { opacity: 1; }
            to   { opacity: 0.25; }
        }
        .mu-full-info-text {
            flex: 1; min-width: 0;
            text-align: left;
        }
        /* Wrappers de title/artist con altura fija + marquee. */
        .mu-full-title-wrap {
            font-size: clamp(18px, 5.5vw, 24px);
            line-height: 1.15;
            height: 1.15em;
            overflow: hidden;
            white-space: nowrap;
            margin: 0 0 2px;
        }
        .mu-full-artist-wrap {
            font-size: clamp(13px, 3.8vw, 16px);
            line-height: 1.3;
            height: 1.3em;
            overflow: hidden;
            white-space: nowrap;
        }
        .mu-full-title-track,
        .mu-full-artist-track {
            display: inline-block;
            will-change: transform;
        }
        .mu-full-title,
        .mu-full-artist {
            display: inline-block;
            font-family: 'VT323', monospace;
            letter-spacing: 1px;
            text-shadow: 0 0 6px var(--lcd-soft);
            line-height: inherit;
        }
        .mu-full-title  { color: var(--lcd); }
        .mu-full-artist { color: var(--lcd-soft); }
        .mu-full-title-wrap.marquee .mu-full-title-track,
        .mu-full-artist-wrap.marquee .mu-full-artist-track {
            display: inline-flex;
            gap: 3em;
            animation: mu-marquee var(--mu-marquee-duration, 12s) linear infinite;
        }
        @keyframes mu-marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(calc(-1 * var(--mu-marquee-cycle, 100%))); }
        }

        /* Progress bar + tiempos. */
        .mu-full-progress-row {
            display: flex; align-items: center;
            gap: 8px;
            padding: 0 4px;
            flex-shrink: 0;
        }
        .mu-full-time {
            font-family: 'VT323', monospace;
            font-size: 14px;
            color: var(--lcd) !important;
            text-shadow: 0 0 5px var(--lcd-soft);
            font-variant-numeric: tabular-nums;
            min-width: 38px;
            text-align: center;
        }
        .mu-full-progress {
            flex: 1;
            height: 14px;
            background: var(--input-bg, #fff);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a);
            position: relative;
            overflow: hidden;
            padding: 2px;
            box-sizing: border-box;
        }
        .mu-full-progress-fill {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg,
                var(--accent, #000080) 0%,
                var(--accent-deep, var(--accent, #1084d0)) 100%);
            transition: width 0.25s linear;
        }

        /* Controles grandes. */
        .mu-full-controls {
            display: flex; justify-content: center; align-items: center;
            gap: 10px;
            flex-shrink: 0;
            padding: 4px 0 2px;
        }
        .mu-full-btn {
            min-width: 60px;
            min-height: 52px;
            padding: 0 12px;
            font-size: 20px;
            font-family: inherit;
            line-height: 1;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--text, #000);
        }
        .mu-full-btn.primary {
            min-width: 88px;
            min-height: 60px;
            font-size: 26px;
        }

        /* Iconos play/pausa dibujados con CSS. */
        .mu-icon-play, .mu-icon-pause {
            display: inline-block;
            color: inherit;
            line-height: 0;
        }
        .mu-icon-pause { display: none; }
        .is-playing > .mu-icon-play  { display: none; }
        .is-playing > .mu-icon-pause { display: inline-flex; }
        #mu-full-toggle .mu-icon-play {
            width: 0; height: 0;
            border-style: solid;
            border-width: 11px 0 11px 18px;
            border-color: transparent transparent transparent currentColor;
            margin-left: 4px;
        }
        .mu-icon-pause {
            align-items: center; justify-content: center;
        }
        .mu-icon-pause::before,
        .mu-icon-pause::after {
            content: ''; display: block; background: currentColor;
        }
        #mu-full-toggle .mu-icon-pause::before,
        #mu-full-toggle .mu-icon-pause::after {
            width: 6px; height: 22px; margin: 0 3px;
        }

        /* Acciones extras: shuffle + add + lock (icon-only). */
        .mu-full-extras {
            display: flex; justify-content: center; align-items: stretch;
            gap: 8px;
            flex-shrink: 0;
            padding: 2px 0;
        }
        .mu-full-extra {
            flex: 0 0 auto;
            width: 56px;
            min-height: 36px;
            padding: 0;
            font-family: inherit;
            color: var(--text, #000);
            display: inline-flex; align-items: center; justify-content: center;
        }
        .mu-full-extra svg {
            width: 22px; height: 22px;
            display: block;
        }
        .mu-full-extra.is-on {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey) !important;
            color: var(--accent, #000080);
        }

        /* Footer chevron-down ancho completo. */
        .mu-full-footer {
            flex-shrink: 0;
            display: flex;
            margin: 0 1px 1px;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .mu-full-footer-btn {
            flex: 1;
            min-height: 32px;
            padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--text, #000);
            font-family: inherit;
        }
        .mu-full-footer-btn svg {
            width: 22px; height: 22px;
            display: block;
        }

        /* ── LOCK SCREEN ── */
        .mu-lock {
            position: fixed; inset: 0;
            z-index: 250;
            background: #0a0a0a;
            color: #22cc66;
            opacity: 0;
            visibility: hidden;
            transition: opacity 1.2s ease, visibility 0s linear 1.2s;
        }
        .mu-lock.visible {
            opacity: 1;
            visibility: visible;
            transition: opacity 1.2s ease, visibility 0s linear 0s;
        }
        .mu-lock::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(to bottom,
                rgba(255,255,255,0.04) 0,
                rgba(255,255,255,0.04) 1px,
                transparent 1px, transparent 3px);
            mix-blend-mode: screen;
            pointer-events: none;
        }
        .mu-lock-stage {
            position: relative; z-index: 2;
            height: 100%;
            display: flex; flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 24px;
            gap: 18px;
        }
        .mu-lock-vinyl-wrap {
            width: min(72vw, 300px);
            aspect-ratio: 1;
            position: relative;
            filter: drop-shadow(0 10px 18px rgba(0,0,0,0.6));
        }
        .mu-lock-title {
            font-family: 'VT323', monospace;
            font-size: 22px;
            color: #22cc66;
            text-shadow: 0 0 6px rgba(34, 204, 102, 0.65);
            text-align: center;
            max-width: 90%;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .mu-lock-artist {
            font-family: 'VT323', monospace;
            font-size: 16px;
            color: rgba(34, 204, 102, 0.7);
            text-shadow: 0 0 5px rgba(34, 204, 102, 0.55);
            text-align: center;
        }
        .mu-lock-hint {
            position: absolute;
            left: 50%; bottom: max(40px, env(safe-area-inset-bottom) + 40px);
            transform: translateX(-50%);
            font-family: 'VT323', monospace;
            font-size: 14px;
            color: #22cc66;
            text-shadow: 0 0 6px rgba(34, 204, 102, 0.65);
            letter-spacing: 1.5px;
            opacity: 0.8;
            animation: mu-lock-blink 2s ease-in-out infinite;
            white-space: nowrap;
            pointer-events: none;
            z-index: 5;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        @keyframes mu-lock-blink {
            from { opacity: 1; }
            to   { opacity: 0.25; }
        }

        /* iframe del YT — invisible al usuario, solo lo necesitamos
           para que el audio fluya. */
        #yt-host {
            position: absolute;
            left: -9999px;
            width: 1px; height: 1px;
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="mh-body <?= htmlspecialchars($activeThemeClass) ?>">

<!-- VENTANA Win98 principal con el tema del usuario aplicado -->
<div class="window mh-window" id="melonWindow">
    <div class="title-bar">
        <div class="title-bar-text">🍉 Melon Hub - <?= htmlspecialchars($userLabel) ?></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" onclick="return false;"></button>
        </div>
    </div>
    <div class="window-body">

        <!-- Header: avatar + saludo + reloj -->
        <div class="mh-userbar">
            <div class="mh-userbar-avatar">
                <?php if ($userImg): ?>
                    <img src="<?= htmlspecialchars($userImg) ?>" alt="">
                <?php else: ?>
                    <span>👤</span>
                <?php endif; ?>
            </div>
            <div class="mh-userbar-text">
                <div class="mh-userbar-greeting">Hola,</div>
                <div class="mh-userbar-name"><?= htmlspecialchars($userLabel) ?></div>
            </div>
            <div>
                <div class="mh-userbar-clock" id="mh-clock">--:--</div>
                <div class="mh-userbar-date" id="mh-date">—</div>
            </div>
        </div>

        <!-- Lista de aplicaciones (sunken panel) -->
        <div class="mh-panel mh-apps">
            <?php foreach ($apps as $app):
                $hasIcon = $app['icon'] && file_exists(__DIR__ . '/' . $app['icon']);
                $external = $app['external'];
                $chev = $external ? '↗' : '›';
                /* Apps internas: cargan en el iframe shell (sin navegar).
                   Externas (Companion, Dibujo): siguen abriendo en pestaña nueva. */
                $href  = $external ? htmlspecialchars($app['url']) : '#';
                $extra = $external
                    ? ' target="_blank" rel="noopener"'
                    : ' data-app-url="' . htmlspecialchars($app['url']) . '" data-app-name="' . htmlspecialchars($app['name']) . '"';
            ?>
                <a class="mh-row<?= $external ? '' : ' shell-launch' ?>" href="<?= $href ?>"<?= $extra ?>>
                    <div class="mh-row-icon">
                        <?php if ($hasIcon): ?>
                            <img src="<?= htmlspecialchars($app['icon']) ?>" alt="">
                        <?php else: ?>
                            <span><?= htmlspecialchars($app['emoji']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mh-row-name"><?= htmlspecialchars($app['name']) ?></div>
                    <div class="mh-row-chev"><?= htmlspecialchars($chev) ?></div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Status bar Win98 con accesos rápidos -->
        <div class="mh-statusbar">
            <a href="<?= strtolower(htmlspecialchars($userLabel)) ?>-desktop.php?desktop=1">💻 Versión PC</a>
            <a href="logout.php" class="danger">🚪 Cerrar sesión</a>
        </div>

    </div>
</div>

<!-- VISTA APP: iframe que carga la sub-app cuando el usuario tap una. -->
<div id="shell-app" hidden>
    <iframe id="app-frame" allow="autoplay; encrypted-media; clipboard-write"></iframe>
</div>

<!-- WIDGET FLOTANTE: disco de vinilo arrastrable. Reemplaza el mini-player.
     Tap rápido → abre fullscreen. Long-press → modo drag. -->
<div class="mu-widget" id="mu-widget" aria-label="Reproductor flotante">
    <div class="mu-widget-disc" id="mu-widget-disc">
        <div class="mu-widget-label" id="mu-widget-label">🎵</div>
        <div class="mu-widget-hole"></div>
    </div>
</div>

<!-- FULLSCREEN PLAYER (shell) — versión completa: title-bar con
     playlist name, display sunken con vinilo, info LCD con marker
     blink + marquee, progress + tiempos LCD, 3 botones primarios,
     fila de extras (shuffle/add/lock), footer chevron-down. -->
<div class="mu-full" id="mu-full" aria-hidden="true">
    <div class="mu-full-splash" id="mu-full-splash"></div>
    <div class="window mu-full-window">
        <div class="title-bar">
            <div class="title-bar-text">🎵 Melon Player — <span id="mu-full-tb-pl">Reproduciendo</span></div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="mu-full-close" type="button"></button>
            </div>
        </div>
        <div class="window-body mu-full-body">

            <div class="mu-full-display">
                <div class="mu-vinyl-wrap">
                    <div class="mu-vinyl" id="mu-vinyl">
                        <div class="mu-vinyl-label empty" id="mu-vinyl-label">🎵</div>
                        <div class="mu-vinyl-hole"></div>
                    </div>
                </div>
            </div>

            <div class="mu-full-info">
                <div class="mu-full-info-marker">♪</div>
                <div class="mu-full-info-text">
                    <div class="mu-full-title-wrap" id="mu-full-title-wrap">
                        <span class="mu-full-title-track" id="mu-full-title-track">
                            <span class="mu-full-title" id="mu-full-title">—</span>
                        </span>
                    </div>
                    <div class="mu-full-artist-wrap" id="mu-full-artist-wrap">
                        <span class="mu-full-artist-track" id="mu-full-artist-track">
                            <span class="mu-full-artist" id="mu-full-artist">—</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mu-full-progress-row">
                <span class="mu-full-time" id="mu-full-time-cur">0:00</span>
                <div class="mu-full-progress" id="mu-full-progress">
                    <div class="mu-full-progress-fill" id="mu-full-progress-fill"></div>
                </div>
                <span class="mu-full-time" id="mu-full-time-tot">0:00</span>
            </div>

            <div class="mu-full-controls">
                <button class="button mu-full-btn" id="mu-full-prev" type="button" aria-label="Anterior">⏮</button>
                <button class="button default mu-full-btn primary" id="mu-full-toggle" type="button" aria-label="Play/Pausa">
                    <span class="mu-icon-play"  aria-hidden="true"></span>
                    <span class="mu-icon-pause" aria-hidden="true"></span>
                </button>
                <button class="button mu-full-btn" id="mu-full-next" type="button" aria-label="Siguiente">⏭</button>
            </div>

            <div class="mu-full-extras">
                <button class="button mu-full-extra" id="mu-full-shuffle" type="button" aria-label="Aleatorio" aria-pressed="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="16 3 21 3 21 8"/>
                        <line x1="4" y1="20" x2="21" y2="3"/>
                        <polyline points="21 16 21 21 16 21"/>
                        <line x1="15" y1="15" x2="21" y2="21"/>
                        <line x1="4" y1="4" x2="9" y2="9"/>
                    </svg>
                </button>
                <button class="button mu-full-extra" id="mu-full-add" type="button" aria-label="Añadir a playlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <line x1="12" y1="5"  x2="12" y2="19"/>
                        <line x1="5"  y1="12" x2="19" y2="12"/>
                    </svg>
                </button>
                <button class="button mu-full-extra" id="mu-full-lock" type="button" aria-label="Bloquear pantalla">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="11" width="16" height="10" rx="1.5"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                </button>
            </div>

        </div>
        <div class="mu-full-footer">
            <button class="button mu-full-footer-btn" id="mu-full-menu" type="button" aria-label="Cerrar reproductor">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- LOCK SCREEN (shell) -->
<div class="mu-lock" id="mu-lock" aria-hidden="true">
    <div class="mu-lock-stage">
        <div class="mu-lock-vinyl-wrap">
            <div class="mu-vinyl" id="mu-lock-vinyl">
                <div class="mu-vinyl-label" id="mu-lock-vinyl-label">🎵</div>
                <div class="mu-vinyl-hole"></div>
            </div>
        </div>
        <div class="mu-lock-title"  id="mu-lock-title">—</div>
        <div class="mu-lock-artist" id="mu-lock-artist">—</div>
    </div>
    <div class="mu-lock-hint" id="mu-lock-hint">↑ Desliza para desbloquear</div>
</div>

<!-- Host del YouTube IFrame Player (off-screen). -->
<div id="yt-host"><div id="yt-iframe"></div></div>

<script>
/* ─── Reloj y fecha del header ─────────────────────────────────── */
(function() {
    var clockEl = document.getElementById('mh-clock');
    var dateEl  = document.getElementById('mh-date');
    if (!clockEl || !dateEl) return;
    var DAYS   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    var MONTHS = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    function pad(n){ return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var d = new Date();
        clockEl.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes());
        dateEl.textContent  = DAYS[d.getDay()] + ' ' + d.getDate() + ' ' + MONTHS[d.getMonth()];
    }
    tick();
    /* Cada 15 s basta porque no mostramos segundos. */
    setInterval(tick, 15000);
})();

/* ─── Service worker ─────────────────────────────────────────── */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js', { scope: '<?= $projectBaseUrl ?>' })
        .catch(function(err){ console.warn('[mobile] sw register fail', err); });
}

/* ════════════════════════════════════════════════════════════════
   SPA SHELL — navegación entre apps vía iframe + URL hash routing
   ════════════════════════════════════════════════════════════════ */
(function(){
    var launcherEl = document.getElementById('melonWindow');
    var appShell   = document.getElementById('shell-app');
    var appFrame   = document.getElementById('app-frame');

    /* Carga una app dentro del iframe y oculta el launcher. */
    function openApp(url, name) {
        if (!url) return;
        appFrame.src = url;
        appShell.hidden = false;
        if (launcherEl) launcherEl.style.visibility = 'hidden';
        /* URL hash para que el back-button del SO funcione. */
        try { history.pushState({ app: url, name: name }, '', '#app=' + encodeURIComponent(name || url)); }
        catch (_) {}
    }
    /* Cierra el iframe y vuelve al launcher. */
    function closeApp() {
        appShell.hidden = true;
        appFrame.src = 'about:blank';
        if (launcherEl) launcherEl.style.visibility = '';
        try { if (location.hash) history.replaceState(null, '', location.pathname); } catch (_) {}
    }

    /* Tap en un icono de app interna → cargar en iframe. */
    document.querySelectorAll('.shell-launch').forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            openApp(a.dataset.appUrl, a.dataset.appName);
        });
    });

    /* Apps embebidas → postMessage para volver al menú o lanzar otra. */
    window.addEventListener('message', function(ev){
        var d = ev.data || {};
        if (d.type === 'shell:back')      closeApp();
        else if (d.type === 'shell:open' && d.url) openApp(d.url, d.name);
    });

    /* Back-button del navegador (gesto en Android, swipe en iOS). */
    window.addEventListener('popstate', function(){
        if (!appShell.hidden) closeApp();
    });
})();

/* ════════════════════════════════════════════════════════════════
   MUSIC ENGINE — vive en la shell, NUNCA se descarga al navegar
   ════════════════════════════════════════════════════════════════ */
window.MuShell = (function(){
    var QUEUE = [];
    var CUR_IDX = -1;
    var CUR_PL_NAME = '';
    var SHUFFLE_ON = false;
    var YT_PLAYER = null;
    var YT_READY  = false;
    var pendingLoadId = null;
    var subscribers = [];
    var progressTimer = null;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function fmt(sec){
        sec = Math.max(0, Math.floor(sec || 0));
        var m = Math.floor(sec / 60), s = sec % 60;
        return m + ':' + (s < 10 ? '0' + s : s);
    }

    /* ── YT IFrame API ── */
    window.onYouTubeIframeAPIReady = function() {
        YT_PLAYER = new window.YT.Player('yt-iframe', {
            height: '1', width: '1',
            playerVars: { playsinline: 1, autoplay: 0 },
            events: {
                'onReady':       function(){ YT_READY = true; if (pendingLoadId) { YT_PLAYER.loadVideoById(pendingLoadId); pendingLoadId = null; } },
                'onStateChange': onYTState,
                'onError':       function(){ next(); }
            }
        });
    };
    (function loadYT(){
        var s = document.createElement('script');
        s.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(s);
    })();

    function onYTState(e) {
        var playing = (e.data === 1);
        var ftog = document.getElementById('mu-full-toggle');
        if (ftog) ftog.classList.toggle('is-playing', playing);
        var v1 = document.getElementById('mu-vinyl');
        var v2 = document.getElementById('mu-widget-disc');
        var v3 = document.getElementById('mu-lock-vinyl');
        if (v1) v1.classList.toggle('paused', !playing);
        if (v2) v2.classList.toggle('paused', !playing);
        if (v3) v3.classList.toggle('paused', !playing);
        setMediaPlaybackState(playing ? 'playing' : 'paused');
        if (e.data === 0) next();
        broadcast({ type: 'mushell:state', state: e.data, idx: CUR_IDX });
    }

    /* ── Media Session ── */
    function updateMediaSession(tr) {
        if (!('mediaSession' in navigator)) return;
        if (!tr) { navigator.mediaSession.metadata = null; return; }
        try {
            navigator.mediaSession.metadata = new MediaMetadata({
                title:  tr.title  || 'Track',
                artist: tr.artist || 'Melon Hub',
                album:  'Melon Hub',
                artwork: tr.videoId ? [
                    { src: 'https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg',     sizes: '320x180',  type: 'image/jpeg' },
                    { src: 'https://i.ytimg.com/vi/' + tr.videoId + '/hqdefault.jpg',     sizes: '480x360',  type: 'image/jpeg' },
                    { src: 'https://i.ytimg.com/vi/' + tr.videoId + '/maxresdefault.jpg', sizes: '1280x720', type: 'image/jpeg' }
                ] : []
            });
        } catch (_) {}
    }
    function setMediaPlaybackState(state) {
        if (!('mediaSession' in navigator)) return;
        try { navigator.mediaSession.playbackState = state; } catch (_) {}
    }
    if ('mediaSession' in navigator) {
        try {
            navigator.mediaSession.setActionHandler('play',           function(){ if (YT_PLAYER) YT_PLAYER.playVideo();  });
            navigator.mediaSession.setActionHandler('pause',          function(){ if (YT_PLAYER) YT_PLAYER.pauseVideo(); });
            navigator.mediaSession.setActionHandler('previoustrack',  prev);
            navigator.mediaSession.setActionHandler('nexttrack',      next);
            navigator.mediaSession.setActionHandler('stop',           function(){ if (YT_PLAYER) YT_PLAYER.stopVideo();  });
        } catch (_) {}
    }

    /* ── Render UI con el track actual ── */
    function renderTrack(tr) {
        var thumbUrl = tr && tr.videoId ? 'https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg' : '';
        function setLabel(id, txt) {
            var el = document.getElementById(id);
            if (!el) return;
            if (thumbUrl) {
                el.style.backgroundImage = "url('" + thumbUrl + "')";
                el.classList.remove('empty');
                el.textContent = '';
            } else {
                el.style.backgroundImage = '';
                el.classList.add('empty');
                el.textContent = txt || '🎵';
            }
        }
        setLabel('mu-widget-label', '🎵');
        setLabel('mu-vinyl-label',  '🎵');
        setLabel('mu-lock-vinyl-label', '🎵');
        var title  = tr ? (tr.title  || tr.videoId || '—') : '—';
        var artist = tr ? (tr.artist || '') : '';
        var t1 = document.getElementById('mu-full-title');  if (t1) t1.textContent = title;
        var a1 = document.getElementById('mu-full-artist'); if (a1) a1.textContent = artist;
        var t2 = document.getElementById('mu-lock-title');  if (t2) t2.textContent = title;
        var a2 = document.getElementById('mu-lock-artist'); if (a2) a2.textContent = artist;
        /* Title-bar: nombre de la playlist activa. */
        var tbPl = document.getElementById('mu-full-tb-pl');
        if (tbPl) tbPl.textContent = CUR_PL_NAME || 'Reproduciendo';
        /* Marquee de title y artist en el reproductor grande. */
        setupMarquee('mu-full-title-wrap',  'mu-full-title-track',  'mu-full-title');
        setupMarquee('mu-full-artist-wrap', 'mu-full-artist-track', 'mu-full-artist');
    }

    /* ── Marquee ticker ──
       Si el texto desborda su wrap, lo clona dentro del track y anima
       el track una distancia igual a (ancho del texto + gap) → loop
       continuo con espacio en blanco entre cada pasada. */
    function setupMarquee(wrapId, trackId, textId) {
        var wrap = document.getElementById(wrapId);
        var track = document.getElementById(trackId);
        var text = document.getElementById(textId);
        if (!wrap || !track || !text) return;
        wrap.classList.remove('marquee');
        while (track.children.length > 1) track.removeChild(track.lastChild);
        track.style.removeProperty('--mu-marquee-cycle');
        track.style.removeProperty('--mu-marquee-duration');
        requestAnimationFrame(function(){
            var textW = text.offsetWidth;
            if (textW <= wrap.clientWidth + 4) return;
            var dup = text.cloneNode(true);
            dup.removeAttribute('id');
            dup.setAttribute('aria-hidden', 'true');
            track.appendChild(dup);
            var em  = parseFloat(getComputedStyle(text).fontSize) || 16;
            var gap = em * 3;
            var cycle = textW + gap;
            var duration = Math.max(8, cycle / 50).toFixed(1) + 's';
            track.style.setProperty('--mu-marquee-cycle', cycle + 'px');
            track.style.setProperty('--mu-marquee-duration', duration);
            wrap.classList.add('marquee');
        });
    }

    /* ── Playback control ── */
    function playCurrent() {
        if (CUR_IDX < 0 || CUR_IDX >= QUEUE.length) return;
        var tr = QUEUE[CUR_IDX];
        if (!tr || !tr.videoId) { next(); return; }
        renderTrack(tr);
        document.getElementById('mu-widget').classList.add('visible');
        updateMediaSession(tr);
        startProgressTimer();
        if (!YT_READY || !YT_PLAYER) { pendingLoadId = tr.videoId; return; }
        YT_PLAYER.loadVideoById(tr.videoId);
        broadcast({ type: 'mushell:track', idx: CUR_IDX, total: QUEUE.length, track: tr });
    }
    function pickRandomIdx() {
        if (QUEUE.length <= 1) return CUR_IDX;
        var r;
        do { r = Math.floor(Math.random() * QUEUE.length); } while (r === CUR_IDX);
        return r;
    }
    function next() {
        if (CUR_IDX < 0) return;
        CUR_IDX = SHUFFLE_ON ? pickRandomIdx() : (CUR_IDX + 1) % QUEUE.length;
        playCurrent();
    }
    function prev() {
        if (CUR_IDX < 0) return;
        CUR_IDX = SHUFFLE_ON ? pickRandomIdx() : (CUR_IDX - 1 + QUEUE.length) % QUEUE.length;
        playCurrent();
    }
    function togglePlay() {
        if (!YT_READY || !YT_PLAYER || CUR_IDX < 0) return;
        var st = YT_PLAYER.getPlayerState();
        if (st === 1) YT_PLAYER.pauseVideo();
        else          YT_PLAYER.playVideo();
    }

    /* ── Progress timer ── */
    function startProgressTimer() {
        if (progressTimer) return;
        progressTimer = setInterval(tickProgress, 500);
        tickProgress();
    }
    function tickProgress() {
        if (!YT_READY || !YT_PLAYER) return;
        var cur = 0, tot = 0;
        try { cur = YT_PLAYER.getCurrentTime() || 0; tot = YT_PLAYER.getDuration() || 0; } catch (_) {}
        var pct = tot > 0 ? Math.min(100, (cur / tot) * 100) : 0;
        var fillEl = document.getElementById('mu-full-progress-fill');
        if (fillEl) fillEl.style.width = pct + '%';
        var c1 = document.getElementById('mu-full-time-cur'); if (c1) c1.textContent = fmt(cur);
        var t1 = document.getElementById('mu-full-time-tot'); if (t1) t1.textContent = fmt(tot);
    }

    /* ── Fullscreen player ── */
    function openFullscreen() {
        if (CUR_IDX < 0) return;
        var el = document.getElementById('mu-full');
        el.classList.add('visible');
        el.setAttribute('aria-hidden', 'false');
    }
    function closeFullscreen() {
        var el = document.getElementById('mu-full');
        el.classList.remove('visible');
        el.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('mu-full-close').addEventListener('click', closeFullscreen);
    document.getElementById('mu-full-menu').addEventListener('click', closeFullscreen);
    document.getElementById('mu-full-prev').addEventListener('click', prev);
    document.getElementById('mu-full-next').addEventListener('click', next);
    document.getElementById('mu-full-toggle').addEventListener('click', togglePlay);

    /* ── Lock screen ── */
    function openLock() {
        if (CUR_IDX < 0) return;
        var lock = document.getElementById('mu-lock');
        var hint = document.getElementById('mu-lock-hint');
        if (hint) { hint.style.transform = ''; hint.style.opacity = ''; }
        lock.classList.add('visible');
        lock.setAttribute('aria-hidden', 'false');
        muRequestFs();
    }
    function closeLock() {
        var lock = document.getElementById('mu-lock');
        var hint = document.getElementById('mu-lock-hint');
        lock.classList.remove('visible');
        lock.setAttribute('aria-hidden', 'true');
        setTimeout(function(){ if (hint) { hint.style.transform = ''; hint.style.opacity = ''; } }, 1200);
        muExitFs();
    }
    function muRequestFs() {
        var el = document.documentElement;
        var fn = el.requestFullscreen || el.webkitRequestFullscreen;
        if (fn) try { fn.call(el).catch(function(){}); } catch (_) {}
    }
    function muExitFs() {
        var fn = document.exitFullscreen || document.webkitExitFullscreen;
        if (fn && (document.fullscreenElement || document.webkitFullscreenElement)) {
            try { fn.call(document).catch(function(){}); } catch (_) {}
        }
    }
    document.getElementById('mu-full-lock').addEventListener('click', openLock);

    /* Shuffle: toggle visual + comportamiento. */
    document.getElementById('mu-full-shuffle').addEventListener('click', function(){
        SHUFFLE_ON = !SHUFFLE_ON;
        this.classList.toggle('is-on', SHUFFLE_ON);
        this.setAttribute('aria-pressed', SHUFFLE_ON ? 'true' : 'false');
    });

    /* Añadir a playlist: delega a la app de Música. Cierra fullscreen
       y abre el iframe de música para que el usuario interactúe. */
    document.getElementById('mu-full-add').addEventListener('click', function(){
        closeFullscreen();
        var link = document.querySelector('.shell-launch[data-app-name="Música"]');
        if (link) link.click();
    });

    /* Swipe-up para desbloquear (solo el hint se mueve). */
    (function attachLockSwipe(){
        var lock = document.getElementById('mu-lock');
        var hint = document.getElementById('mu-lock-hint');
        var sy = null;
        function thr(){ return window.innerHeight / 3; }
        lock.addEventListener('touchstart', function(e){
            if (!e.touches || e.touches.length !== 1) return;
            sy = e.touches[0].clientY;
            hint.style.transition = 'none';
        }, { passive: true });
        lock.addEventListener('touchmove', function(e){
            if (sy === null || !e.touches[0]) return;
            var dy = sy - e.touches[0].clientY;
            if (dy > 0) {
                hint.style.transform = 'translateX(-50%) translateY(' + (-dy * 1.8) + 'px)';
                hint.style.opacity = String(0.8 - Math.min(1, dy/thr()) * 0.6);
            }
        }, { passive: true });
        function end(e){
            if (sy === null) return;
            var t = e.changedTouches && e.changedTouches[0];
            var dy = t ? (sy - t.clientY) : 0;
            sy = null;
            hint.style.transition = '';
            if (dy >= thr()) closeLock();
            else { hint.style.transform = ''; hint.style.opacity = ''; }
        }
        lock.addEventListener('touchend',    end);
        lock.addEventListener('touchcancel', end);
    })();

    /* ── Widget flotante (drag con long-press) ── */
    (function attachWidget(){
        var widget = document.getElementById('mu-widget');
        var sx, sy, ox, oy;
        var dragging = false, didMove = false, tapValid = false;
        var lpTimer = null;
        var LP_MS = 400, MOVE_TH = 10;

        function pp(e){
            return e.touches && e.touches[0]
                ? { x: e.touches[0].clientX, y: e.touches[0].clientY }
                : { x: e.clientX, y: e.clientY };
        }
        function start(e){
            var p = pp(e);
            sx = p.x; sy = p.y;
            didMove = false; tapValid = true; dragging = false;
            lpTimer = setTimeout(function(){
                lpTimer = null;
                dragging = true;
                widget.classList.add('dragging');
                var r = widget.getBoundingClientRect();
                ox = r.left; oy = r.top;
                widget.style.right  = 'auto';
                widget.style.bottom = 'auto';
                widget.style.left = ox + 'px';
                widget.style.top  = oy + 'px';
                try { navigator.vibrate && navigator.vibrate(30); } catch (_) {}
            }, LP_MS);
        }
        function move(e){
            if (sx === undefined) return;
            var p = pp(e);
            if (!dragging) {
                if (Math.abs(p.x - sx) > MOVE_TH || Math.abs(p.y - sy) > MOVE_TH) {
                    if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; }
                    tapValid = false;
                }
                return;
            }
            if (e.cancelable) e.preventDefault();
            didMove = true;
            var dx = p.x - sx, dy = p.y - sy;
            var nx = Math.max(0, Math.min(window.innerWidth  - widget.offsetWidth,  ox + dx));
            var ny = Math.max(0, Math.min(window.innerHeight - widget.offsetHeight, oy + dy));
            widget.style.left = nx + 'px';
            widget.style.top  = ny + 'px';
        }
        function end(){
            if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; }
            sx = undefined;
            widget.classList.remove('dragging');
            if (dragging) {
                dragging = false;
                try {
                    localStorage.setItem('mu-widget-pos', JSON.stringify({
                        left: widget.style.left, top: widget.style.top
                    }));
                } catch (_) {}
            } else if (tapValid) {
                openFullscreen();
            }
            tapValid = false; didMove = false;
        }
        widget.addEventListener('touchstart', start, { passive: true });
        widget.addEventListener('touchmove',  move,  { passive: false });
        widget.addEventListener('touchend',   end);
        widget.addEventListener('touchcancel', end);
        widget.addEventListener('mousedown', start);
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup',   end);

        /* Restaura posición guardada. */
        try {
            var s = JSON.parse(localStorage.getItem('mu-widget-pos') || 'null');
            if (s && s.left && s.top) {
                widget.style.right  = 'auto';
                widget.style.bottom = 'auto';
                widget.style.left = s.left;
                widget.style.top  = s.top;
            }
        } catch (_) {}
    })();

    /* ── API pública / postMessage bridge ── */
    function loadQueue(tracks, startIdx, plName) {
        if (!tracks || !tracks.length) return;
        QUEUE = tracks.slice();
        CUR_IDX = Math.max(0, Math.min(startIdx|0, QUEUE.length - 1));
        CUR_PL_NAME = plName || '';
        playCurrent();
    }
    function getState() {
        var tr = (CUR_IDX >= 0 && QUEUE[CUR_IDX]) ? QUEUE[CUR_IDX] : null;
        return {
            queue: QUEUE, idx: CUR_IDX, track: tr,
            playing: !!(YT_PLAYER && YT_PLAYER.getPlayerState && YT_PLAYER.getPlayerState() === 1)
        };
    }
    window.addEventListener('message', function(ev){
        var d = ev.data || {};
        if (d.type === 'mushell:load')          { loadQueue(d.queue, d.idx); subscribers.push(ev.source); }
        else if (d.type === 'mushell:next')     next();
        else if (d.type === 'mushell:prev')     prev();
        else if (d.type === 'mushell:toggle')   togglePlay();
        else if (d.type === 'mushell:open-full') openFullscreen();
        else if (d.type === 'mushell:open-lock') openLock();
        else if (d.type === 'mushell:state-request') {
            try { ev.source.postMessage({ type: 'mushell:state-snapshot', state: getState() }, '*'); } catch (_) {}
        }
    });
    function broadcast(msg) {
        subscribers = subscribers.filter(function(w){ return w && !w.closed; });
        subscribers.forEach(function(w){ try { w.postMessage(msg, '*'); } catch (_) {} });
    }

    return {
        loadQueue: loadQueue, next: next, prev: prev, toggle: togglePlay,
        openFullscreen: openFullscreen, openLock: openLock, getState: getState
    };
})();
</script>

</body>
</html>
