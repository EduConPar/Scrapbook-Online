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

/* TV LAN URL — para Smart TVs que no soportan TLS 1.3/SNI moderno del
   túnel cloudflared (Tizen <2018, webOS <4, Android TV 5). La TV está
   en la misma WiFi → HTTP plano por IP local evita el handshake TLS y
   carga instantáneo. Detección: override de .env > socket UDP > host. */
$tvLanUrl = trim((string) env('LAN_URL', ''));
if ($tvLanUrl === '') {
    $lanIp = '';
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        if (@socket_connect($sock, '8.8.8.8', 53)) {
            @socket_getsockname($sock, $lanIp);
        }
        @socket_close($sock);
    }
    if (!$lanIp || $lanIp === '127.0.0.1') {
        $alt = @gethostbyname(@gethostname());
        if ($alt && $alt !== '127.0.0.1' && filter_var($alt, FILTER_VALIDATE_IP)) $lanIp = $alt;
    }
    if ($lanIp && $lanIp !== '127.0.0.1') {
        $tvLanUrl = 'http://' . $lanIp . $projectBaseUrl . 'tv.php';
    }
}

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
    ['name' => 'Calendario',   'url' => 'apps/calendario.php',                                                  'emoji' => '📅', 'icon' => 'assets/img/appIcons/calendarioIcon.png',   'external' => false, 'wip' => false],
    ['name' => 'Companion',    'url' => 'apps/mobile/companion-mobile.php',                                     'emoji' => '💀', 'icon' => 'assets/img/appIcons/companionIcon.png',    'external' => false, 'wip' => false],
    ['name' => 'D&D',          'url' => 'apps/mobile/dnd-mobile.php',                                           'emoji' => '⚔',  'icon' => 'assets/img/appIcons/dndIcon.png',          'external' => false, 'wip' => false],
    ['name' => 'Dibujo',       'url' => 'apps/mobile/dibujo-mobile.php',                                        'emoji' => '✏️', 'icon' => 'assets/img/appIcons/drawingIcon.png',      'external' => false, 'wip' => false],
    ['name' => 'Galería',      'url' => 'apps/mobile/galeria-mobile.php',                                       'emoji' => '🖼', 'icon' => 'assets/img/appIcons/galeriaIcon.png',     'external' => false, 'wip' => false],
    ['name' => 'MelonArchive', 'url' => 'apps/mobile/melonarchive-mobile.php',                                  'emoji' => '📼', 'icon' => 'assets/img/appIcons/melonArchiveIcon.png', 'external' => false, 'wip' => false],
    ['name' => 'Música',       'url' => 'apps/mobile/musica-mobile.php',                                        'emoji' => '🎵', 'icon' => 'assets/img/appIcons/musicaIcon.png',       'external' => false, 'wip' => false],
    ['name' => 'Perfil',       'url' => 'apps/mobile/perfil-mobile.php',                                        'emoji' => '👤', 'icon' => 'assets/img/appIcons/profileIcon.png',      'external' => false, 'wip' => false],
    ['name' => 'Temas',        'url' => 'apps/mobile/temas-mobile.php',                                         'emoji' => '🎨', 'icon' => 'assets/img/appIcons/temasIcon.png',        'external' => false, 'wip' => false],
    ['name' => 'Tienda',       'url' => 'apps/mobile/tienda-mobile.php',                                        'emoji' => '🛒', 'icon' => 'assets/img/appIcons/tiendaIcon.png',       'external' => false, 'wip' => false],
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
    <link rel="icon" href="assets/img/mobile/icon.png" type="image/png">
    <link rel="manifest" href="manifest.php<?= $tokenForManifest !== '' ? '?token=' . htmlspecialchars($tokenForManifest) : '' ?>">
    <link rel="apple-touch-icon" href="assets/img/mobile/icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Melon Hub">
    <!-- Mismo stack visual que el escritorio Win98 + tema del usuario -->
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css?v=<?= filemtime(__DIR__ . '/assets/css/base.css') ?>">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
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

        /* ── PANEL DE AJUSTES (slide-up sheet) ──
           Backdrop fixed + ventana Win98 centrada que aparece al pulsar
           "⚙ Ajustes" del statusbar. Cada opción es un botón Win98
           grande. Al tap fuera o en X se cierra. */
        #mh-settings-backdrop {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        #mh-settings-backdrop.is-open { display: flex; }
        #mh-settings-panel {
            width: 100%;
            max-width: 340px;
            display: flex;
            flex-direction: column;
        }
        #mh-settings-panel .window-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        #mh-settings-panel .mh-set-btn {
            display: flex; align-items: center; gap: 10px;
            min-height: 48px;
            font-size: 13px;
            text-align: left;
            padding: 8px 12px;
            text-decoration: none;
            color: var(--text, #000);
            background: var(--btn-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            cursor: pointer;
            border: 0;
            box-sizing: border-box;
            width: 100%;
            font-family: inherit;
        }
        #mh-settings-panel .mh-set-btn:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
            padding: 9px 11px 7px 13px;
        }
        #mh-settings-panel .mh-set-btn.danger { color: var(--error-text, #800000); }
        #mh-settings-panel .mh-set-emoji { font-size: 18px; line-height: 1; }
        #mh-settings-panel .mh-set-text { flex: 1; min-width: 0; }
        #mh-settings-panel .mh-set-text small {
            display: block;
            font-size: 10px;
            color: var(--text-muted, #666);
            margin-top: 2px;
        }

        /* ── MODAL CAMBIAR CONTRASEÑA ── (igual estilo Win98) */
        #mh-cp-backdrop {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 90;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        #mh-cp-backdrop.is-open { display: flex; }
        #mh-cp-window { width: 100%; max-width: 320px; display: flex; flex-direction: column; }
        #mh-cp-window .window-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        #mh-cp-window label { font-size: 11px; }
        #mh-cp-window input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            min-height: 26px;
            font-size: 13px;
            margin-top: 3px;
        }
        #mh-cp-status { font-size: 11px; min-height: 14px; margin: 4px 0 0; }
        #mh-cp-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 6px;
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

        /* ── MODAL "CONECTAR TV" ── */
        .mu-tv-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 310;
            display: flex; align-items: center; justify-content: center;
            padding: 16px;
        }
        .mu-tv-backdrop[hidden] { display: none !important; }
        .mu-tv-window {
            width: 100%; max-width: 360px;
            max-height: calc(100vh - 32px);
            display: flex; flex-direction: column;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }
        .mu-tv-window > .window-body {
            flex: 1;
            overflow-y: auto;
            padding: 12px 14px;
        }
        .mu-tv-intro {
            font-size: 11px;
            color: var(--text, #000);
            line-height: 1.45;
            margin: 0 0 12px;
        }
        .mu-tv-code-wrap {
            background: var(--input-bg, #fff);
            padding: 16px 12px;
            margin: 0 0 12px;
            text-align: center;
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #808080),
                inset -1px -1px var(--bezel-light-1, #fff);
        }
        .mu-tv-code {
            font-family: 'VT323', Consolas, Monaco, monospace;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: var(--accent, #000080);
            line-height: 1;
        }
        .mu-tv-code-meta {
            margin-top: 6px;
            font-size: 10px;
            color: var(--text-faint, #666);
        }
        #mu-tv-url-inline { font-family: 'VT323', monospace; font-size: 12px; }
        .mu-tv-url-row {
            display: flex; gap: 6px;
            margin-bottom: 10px;
        }
        .mu-tv-url-row input {
            flex: 1; min-width: 0;
            font-family: 'VT323', monospace;
            font-size: 13px;
            padding: 3px 6px;
            color: var(--text, #000);
            background: var(--input-bg, #fff);
        }
        .mu-tv-url-row .button {
            min-height: 24px;
            font-size: 11px;
            padding: 0 10px;
        }
        .mu-tv-steps {
            font-size: 11px;
            color: var(--text, #000);
            margin: 0; padding-left: 18px;
            line-height: 1.55;
        }
        .mu-tv-steps li { margin-bottom: 4px; }

        /* ── SHELL MODALS (action menu vinilo + playlist picker + alert) ──
           Modales bottom-sheet Win98 que viven sobre el shell. Aparecen
           encima del fullscreen (z-index 300 > .mu-full 200). */
        .shell-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 300;
            display: flex; align-items: center; justify-content: center;
            padding: 16px; box-sizing: border-box;
        }
        .shell-modal {
            width: 100%; max-width: 380px;
            max-height: calc(100vh - 32px);
            display: flex; flex-direction: column;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }
        .shell-modal > .title-bar { flex-shrink: 0; min-height: 22px; }
        .shell-modal > .window-body {
            flex: 1; overflow-y: auto; padding: 12px 14px;
            background: var(--win-body-bg, var(--win-bg, silver));
            color: var(--text, #000);
        }
        .shell-modal-list { display: flex; flex-direction: column; gap: 6px; }
        .shell-modal-item {
            display: flex; align-items: center; gap: 12px;
            justify-content: flex-start; text-align: left;
            padding: 0 14px; font-size: 13px;
            min-height: 44px; width: 100%;
        }
        .shell-modal-icon {
            font-size: 16px; width: 20px; text-align: center;
            flex-shrink: 0; color: var(--accent, #000080);
        }
        .shell-modal-item-pl {
            flex-direction: column; align-items: flex-start;
            gap: 2px; min-height: 50px;
            padding: 8px 12px;
        }
        .shell-modal-item-pl-name { font-weight: bold; font-size: 13px; }
        .shell-modal-item-pl-meta {
            font-size: 11px; color: var(--text-faint, #666);
        }
        .shell-modal-msg {
            font-size: 12px; line-height: 1.5;
            margin: 0 0 12px; color: var(--text, #000);
        }
        .shell-modal-actions {
            display: flex; justify-content: flex-end; gap: 6px;
            margin-top: 12px; padding-top: 8px;
            border-top: 1px solid var(--bezel-dark-2, grey);
        }
        .shell-modal-actions .button { min-height: 28px; min-width: 70px; }

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

        <!-- Status bar Win98 con un único acceso: Ajustes (que abre el
             panel inferior con las acciones de cuenta y enlaces). -->
        <div class="mh-statusbar">
            <a href="#" id="mh-settings-link">⚙ Ajustes</a>
        </div>

    </div>
</div>

<!-- PANEL DE AJUSTES — abierto desde "⚙ Ajustes" del statusbar. -->
<div id="mh-settings-backdrop">
    <div class="window" id="mh-settings-panel">
        <div class="title-bar">
            <div class="title-bar-text">⚙ Ajustes</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="mh-settings-close"></button>
            </div>
        </div>
        <div class="window-body">
            <button class="mh-set-btn" type="button" id="mh-set-change-password">
                <span class="mh-set-emoji">🔑</span>
                <span class="mh-set-text">Cambiar contraseña
                    <small>Actualiza la contraseña de tu cuenta</small>
                </span>
            </button>
            <a class="mh-set-btn danger" href="logout.php?to=manual">
                <span class="mh-set-emoji">🚪</span>
                <span class="mh-set-text">Cerrar sesión
                    <small>Sale y vuelve a la pantalla de login</small>
                </span>
            </a>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR CONTRASEÑA — mismo endpoint que el escritorio. -->
<div id="mh-cp-backdrop">
    <div class="window" id="mh-cp-window">
        <div class="title-bar">
            <div class="title-bar-text">🔑 Cambiar contraseña</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="mh-cp-close"></button>
            </div>
        </div>
        <div class="window-body">
            <label>Contraseña actual:
                <input type="password" id="mh-cp-current" autocomplete="current-password">
            </label>
            <label>Nueva contraseña:
                <input type="password" id="mh-cp-new" autocomplete="new-password" minlength="6">
            </label>
            <label>Repetir nueva:
                <input type="password" id="mh-cp-confirm" autocomplete="new-password" minlength="6">
            </label>
            <p id="mh-cp-status"></p>
            <div id="mh-cp-actions">
                <button id="mh-cp-cancel">Cancelar</button>
                <button id="mh-cp-ok" class="default">Aceptar</button>
            </div>
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

<!-- Listen-Together: modal de invitar usuarios + toast inferior. -->
<style>
    @keyframes ltLivePulseM {
        0%, 100% { opacity: 1;   transform: scale(1); }
        50%      { opacity: 0.3; transform: scale(0.75); }
    }
    #mu-lt-modal {
        display:none; position:fixed; left:50%; top:50%;
        transform:translate(-50%, -50%);
        width: min(320px, 90vw); max-height: 80vh; overflow-y:auto;
        z-index: 9100;
    }
    #mu-lt-modal .window-body { padding: 8px 10px; }
    #mu-lt-modal .lt-section-title {
        font-size: 11px; font-weight: bold; margin: 4px 0 6px; opacity: 0.85;
    }
    #mu-lt-user-list {
        max-height: 50vh; overflow-y: auto;
        background: var(--input-bg, #fff); color: var(--input-text, #000);
        padding: 2px; margin-bottom: 8px;
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff);
    }
    .mu-lt-user-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 8px; font-size: 12px; cursor: default;
    }
    .mu-lt-user-row + .mu-lt-user-row {
        border-top: 1px dotted rgba(0,0,0,0.15);
    }
    .mu-lt-user-row button {
        font-size: 11px; padding: 4px 10px;
        font-family: inherit;
        background: var(--btn-bg, silver); color: var(--text, #000);
        border: 0; cursor: pointer;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    .mu-lt-user-row button.invited { opacity: 0.6; }
    #mu-lt-status { font-size: 11px; opacity: 0.75; padding: 4px 0; }
    /* Toast Win98 inferior */
    .mu-lt-toast {
        position: fixed; left: 50%; bottom: 12vh;
        transform: translateX(-50%);
        background: var(--win-bg, silver);
        color: var(--text, #000);
        padding: 8px 14px; max-width: 92vw;
        font-size: 12px;
        z-index: 9300;
        animation: muLtToastIn 0.28s ease;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            3px 3px 8px rgba(0,0,0,0.35);
    }
    .mu-lt-toast .toast-title { font-weight: bold; margin-bottom: 2px; }
    .mu-lt-toast .toast-msg   { opacity: 0.85; font-size: 11px; }
    .mu-lt-toast .toast-actions { margin-top: 8px; display: flex; gap: 6px; justify-content: flex-end; }
    .mu-lt-toast .toast-actions button {
        background: var(--btn-bg, silver); color: var(--text, #000);
        border: 0; padding: 3px 12px; font-size: 11px; font-family: inherit;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    @keyframes muLtToastIn {
        from { opacity: 0; transform: translate(-50%, 16px); }
        to   { opacity: 1; transform: translate(-50%,  0); }
    }
</style>

<div class="window" id="mu-lt-modal">
    <div class="title-bar">
        <div class="title-bar-text">🎧 Escuchar juntos</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="mu-lt-modal-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="lt-section-title">Estado</div>
        <div id="mu-lt-status">No estás en ninguna sesión.</div>
        <div class="lt-section-title">Invitar a:</div>
        <div id="mu-lt-user-list">
            <div style="font-size:11px;text-align:center;padding:8px;">Cargando…</div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:4px;">
            <button class="button" id="mu-lt-end-btn" style="display:none;">Cerrar sesión</button>
        </div>
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

            <div class="mu-full-display" style="position:relative;">
                <div class="mu-vinyl-wrap">
                    <div class="mu-vinyl" id="mu-vinyl">
                        <div class="mu-vinyl-label empty" id="mu-vinyl-label">🎵</div>
                        <div class="mu-vinyl-hole"></div>
                    </div>
                </div>
                <!-- Indicador LIVE — pulsa cuando estás en sesión de escucha conjunta. -->
                <span id="lt-live-dot-m" title="" style="display:none;position:absolute;top:10px;right:10px;width:12px;height:12px;border-radius:50%;background:var(--accent, #1db954);box-shadow:0 0 7px var(--accent, #1db954),0 0 1px rgba(0,0,0,0.5);animation:ltLivePulseM 1.2s ease-in-out infinite;z-index:5;"></span>
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
                <button class="button mu-full-extra" id="mu-full-lock" type="button" aria-label="Bloquear pantalla">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="11" width="16" height="10" rx="1.5"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                </button>
                <button class="button mu-full-extra" id="mu-full-tv" type="button" aria-label="Enviar a TV">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="5" width="20" height="13" rx="1.5"/>
                        <line x1="8" y1="22" x2="16" y2="22"/>
                        <line x1="12" y1="18" x2="12" y2="22"/>
                    </svg>
                </button>
                <button class="button mu-full-extra" id="mu-full-lyrics" type="button" aria-label="Letra">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="9" y="2" width="6" height="12" rx="3"/>
                        <path d="M5 10v2a7 7 0 0 0 14 0v-2"/>
                        <line x1="12" y1="19" x2="12" y2="22"/>
                        <line x1="8" y1="22" x2="16" y2="22"/>
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

<!-- Modal "Conectar TV" — se abre desde el botón 📺 del fullscreen.
     Win98 window con QR + URL del companion tv.php. -->
<div class="mu-tv-backdrop" id="mu-tv-backdrop"
     data-lan-url="<?= htmlspecialchars($tvLanUrl, ENT_QUOTES) ?>" hidden>
    <div class="window mu-tv-window">
        <div class="title-bar">
            <div class="title-bar-text">📺 Conectar a TV</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="mu-tv-close" type="button"></button>
            </div>
        </div>
        <div class="window-body">
            <p class="mu-tv-intro">
                En el navegador de tu TV abre <strong id="mu-tv-url-inline"></strong>
                e introduce el código de abajo.
            </p>
            <div class="mu-tv-code-wrap">
                <div class="mu-tv-code" id="mu-tv-code">— — — — — —</div>
                <div class="mu-tv-code-meta" id="mu-tv-code-meta">Generando…</div>
            </div>
            <ol class="mu-tv-steps">
                <li>Abre el navegador de tu Smart TV.</li>
                <li>Ve a la URL de arriba.</li>
                <li>Teclea el código de 6 dígitos con el mando.</li>
                <li>Listo — la TV queda emparejada 30 días.</li>
            </ol>
            <div class="mu-tv-url-row">
                <input type="text" id="mu-tv-url" readonly>
                <button class="button" id="mu-tv-copy" type="button">Copiar</button>
            </div>
        </div>
    </div>
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

    /* Helpers de presentación — manipulan SOLO el DOM, sin tocar el
       history. Quien llame a openApp() empuja un estado; popstate
       (back del SO) restaura el estado anterior y reusa estos helpers.

       OJO con history pollution: asignar `iframe.src` empuja una
       entrada al history del TOP window. Esto rompe la navegación
       cuando se abre, se cierra y se vuelve a abrir una app (la pila
       queda llena de entradas fantasma y el back se desincroniza).
       Usamos contentWindow.location.replace() para cargar sin empujar.
       Tampoco reseteamos a 'about:blank' al volver al menú — el
       iframe se queda oculto con la última URL hasta el próximo open. */
    function showApp(url) {
        appShell.hidden = false;
        if (launcherEl) launcherEl.style.visibility = 'hidden';
        if (!url) return;
        try { appFrame.contentWindow.location.replace(url); }
        catch (_) { appFrame.src = url; }
    }
    function showMenu() {
        if (appShell.hidden) return;
        appShell.hidden = true;
        if (launcherEl) launcherEl.style.visibility = '';
    }

    /* Abrir una app: empuja un estado al history para que el back del
       teléfono nos devuelva al estado anterior (sea otra app abierta o
       el menú). */
    function openApp(url, name) {
        if (!url) return;
        showApp(url);
        try { history.pushState({ app: url, name: name }, '', '#app=' + encodeURIComponent(name || url)); }
        catch (_) {}
    }

    /* Apps que piden volver vía postMessage (botón "cerrar" interno).
       Disparamos history.back() para que pase por el mismo flujo que el
       back físico: popstate decide si hay app anterior o si toca menú. */
    function goBack() {
        try { history.back(); }
        catch (_) { showMenu(); }
    }

    /* Tap en un icono de app interna → cargar en iframe. */
    document.querySelectorAll('.shell-launch').forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            openApp(a.dataset.appUrl, a.dataset.appName);
        });
    });

    /* Apps embebidas → postMessage para volver atrás o lanzar otra. */
    window.addEventListener('message', function(ev){
        var d = ev.data || {};
        if (d.type === 'shell:back')      goBack();
        else if (d.type === 'shell:open' && d.url) openApp(d.url, d.name);
        else if (d.type === 'theme-activated') {
            /* La app de Temas (iframe) activó un tema nuevo. Refrescamos:
                 1) Reemplazamos el <link id="active-theme-link"> del shell
                    con el CSS del tema nuevo (cache-bust con ?t=).
                 2) Sustituimos la clase del tema en <body> conservando
                    el resto (mh-body, etc.). El patrón es "-{userLabel}".
               Resultado: el menú principal del shell adopta el tema sin
               recargar la PWA y sin perder la app abierta encima. */
            var newClass = d.className || '';
            var basePath = d.cssBasePath || '';
            var existing = document.getElementById('active-theme-link');
            if (basePath) {
                var href = basePath + (basePath.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
                if (existing) existing.href = href;
                else {
                    var link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.id  = 'active-theme-link';
                    link.href = href;
                    document.head.appendChild(link);
                }
            } else if (existing) existing.remove();

            var userLabelSlug = <?= json_encode(preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel)) ?>;
            var themeClassRe  = new RegExp('-' + userLabelSlug + '$');
            var keep = (document.body.className || '').split(/\s+/).filter(function(c){
                return c && !themeClassRe.test(c);
            });
            if (newClass) keep.push(newClass);
            document.body.className = keep.join(' ');
        }
    });

    /* Back del SO (botón físico Android, gesto swipe iOS, back del
       browser). Restauramos el estado anterior:
         - Si había otra app abierta antes → la mostramos.
         - Si no → cae al menú principal. */
    window.addEventListener('popstate', function(e){
        var s = e.state;
        if (s && s.app) {
            showApp(s.app);
        } else {
            showMenu();
        }
    });

    /* Deep-link desde notificación push: #chat=USERKEY
       Carga el perfil con el mismo hash → perfil-mobile lo lee y abre
       el modal de chat directamente con esa persona. Limpia el hash del
       shell para que no se vuelva a disparar tras navegar.
       Re-evaluado en cada hashchange: el SW llama Client.navigate(url)
       cuando ya hay una pestaña abierta, lo que cambia el hash sin
       recargar; sin el listener, el deep-link solo funcionaba en
       pestaña nueva. */
    function dispatchChatHash(){
        var m = /#chat=([a-z0-9_-]+)/i.exec(location.hash);
        if (!m) return;
        var k = encodeURIComponent(m[1]);
        try { history.replaceState(null, '', location.pathname); } catch (_) {}
        openApp('apps/mobile/perfil-mobile.php#chat=' + k, 'Perfil');
    }
    dispatchChatHash();
    window.addEventListener('hashchange', dispatchChatHash);
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
    /* Label del usuario actual — usado al añadir tracks a playlists
       para el campo addedBy. */
    var ME_LABEL = <?= json_encode($userLabel ?? '') ?>;

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
        publishNowPlaying(playing, true);
    }

    /* Publica el track actual al endpoint save-now-playing para que
       la TV (tv.php) lo lea via polling. Best-effort, errores callados.
       Throttled a 1 call/s mediante __NP_LAST. */
    var __NP_LAST = 0;
    function publishNowPlaying(isPlaying, force) {
        if (CUR_IDX < 0 || !QUEUE[CUR_IDX]) return;
        var now = Date.now();
        /* Throttle de 500ms para el tick periódico (antes 800), pero los
           eventos "importantes" (cambio de pista, play/pause) lo saltan
           para que la TV reaccione al instante. Bajamos el throttle
           para permitir el ritmo de 2s + cubrir ráfagas (cambio de
           track + start play). */
        if (!force && now - __NP_LAST < 500) return;
        __NP_LAST = now;
        var tr = QUEUE[CUR_IDX];
        var pos = 0, dur = 0;
        try { pos = YT_PLAYER && YT_PLAYER.getCurrentTime ? YT_PLAYER.getCurrentTime() : 0; } catch (_) {}
        try { dur = YT_PLAYER && YT_PLAYER.getDuration    ? YT_PLAYER.getDuration()    : 0; } catch (_) {}
        try {
            fetch('assets/music/api.php?action=save-now-playing', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    videoId:   tr.videoId,
                    title:     tr.title  || '',
                    artist:    tr.artist || '',
                    plName:    CUR_PL_NAME,
                    position:  pos,
                    duration:  dur,
                    isPlaying: !!isPlaying,
                }),
                keepalive: true
            });
        } catch (_) {}
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

    /* ── WRAPPED tracking — logea cada cambio de track con el tiempo
       real escuchado para que las escuchas DESDE EL MÓVIL cuenten en
       las stats anuales del usuario. Replica el patrón de
       reproductor.php. */
    var _wrappedLastTrack      = null;
    var _wrappedLastPlaylistId = null;
    var WRAPPED_URL = 'assets/music/wrapped-api.php?action=log';

    function sendWrappedLog(track, listenedS, playlistId) {
        if (!track || !track.videoId || !track.title) return;
        listenedS = Math.max(0, Math.floor(listenedS || 0));
        if (listenedS < 3) return;
        var body = JSON.stringify({
            videoId:    track.videoId,
            title:      track.title,
            artist:     track.artist || '',
            playlistId: playlistId,
            durationS:  listenedS,
        });
        try {
            if (navigator.sendBeacon) {
                var blob = new Blob([body], { type: 'application/json' });
                if (navigator.sendBeacon(WRAPPED_URL, blob)) return;
            }
        } catch (_) {}
        try {
            fetch(WRAPPED_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body,
                keepalive: true,
            }).catch(function(){});
        } catch (_) {}
    }
    (function setupWrappedFlush() {
        function flush() {
            if (!_wrappedLastTrack) return;
            var listened = 0;
            try {
                if (YT_PLAYER && typeof YT_PLAYER.getCurrentTime === 'function') {
                    listened = YT_PLAYER.getCurrentTime() || 0;
                }
            } catch (_) {}
            sendWrappedLog(_wrappedLastTrack, listened, _wrappedLastPlaylistId);
        }
        window.addEventListener('pagehide', flush);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') flush();
        });
    })();

    /* ════════════════════════════════════════════════════════════════
       LISTEN TOGETHER — móvil. Mismo modelo que reproductor.php desktop:
       polling cada 2s para guest, broadcast en cada track change para
       host. Backend compartido en assets/listen/api.php.
       ════════════════════════════════════════════════════════════════ */
    var LT_URL = 'assets/listen/api.php';
    var LT_ROLE = null;
    var LT_SESSION_ID = null;
    var LT_GUEST_POLL_T = null;
    var LT_HOST_BCAST_T = null;
    var LT_INVITES_POLL_T = null;
    var LT_HOST_BCAST_INTERVAL = null;
    var LT_KNOWN_PARTICIPANTS = {};
    var LT_SHOWN_INVITES = {};

    function ltFetch(action, opts) {
        opts = opts || {};
        var isGet = !opts.body;
        return fetch(LT_URL + '?action=' + encodeURIComponent(action), {
            method: isGet ? 'GET' : 'POST',
            credentials: 'same-origin',
            headers: isGet ? {} : { 'Content-Type': 'application/json' },
            body:    opts.body ? JSON.stringify(opts.body) : undefined,
        }).then(function(r){ return r.json(); }).catch(function(){ return { ok: false }; });
    }

    function ltToast(opts) {
        /* Toast Win98 inferior. opts: {title, message, actions: [{label, fn}], duration} */
        var existing = document.getElementById('mu-lt-toast-' + (opts.id || ''));
        if (existing) existing.remove();
        var t = document.createElement('div');
        t.id = 'mu-lt-toast-' + (opts.id || Date.now());
        t.className = 'mu-lt-toast';
        var html = '';
        if (opts.title)   html += '<div class="toast-title">' + opts.title + '</div>';
        if (opts.message) html += '<div class="toast-msg">' + opts.message + '</div>';
        if (opts.actions && opts.actions.length) {
            html += '<div class="toast-actions">';
            opts.actions.forEach(function(a, i){
                html += '<button data-act-i="' + i + '">' + a.label + '</button>';
            });
            html += '</div>';
        }
        t.innerHTML = html;
        document.body.appendChild(t);
        if (opts.actions && opts.actions.length) {
            t.querySelectorAll('button[data-act-i]').forEach(function(b){
                b.addEventListener('click', function(){
                    var i = +b.getAttribute('data-act-i');
                    if (opts.actions[i] && opts.actions[i].fn) opts.actions[i].fn();
                    t.remove();
                });
            });
        }
        if (!opts.actions || !opts.actions.length) {
            setTimeout(function(){ if (t.parentNode) t.remove(); }, opts.duration || 4500);
        }
    }

    function ltUpdateLiveDot(mode, hostLabel) {
        var el = document.getElementById('lt-live-dot-m');
        if (!el) return;
        if (!mode) { el.style.display = 'none'; el.title = ''; return; }
        el.style.display = 'block';
        el.title = mode === 'host'
            ? 'En directo · estás hosteando una sesión'
            : 'En directo · escuchando con ' + (hostLabel || 'el host');
    }

    /* ── HOST: broadcast del estado ──────────────────────── */
    async function ltHostBroadcast() {
        if (LT_ROLE !== 'host') return;
        /* Recoge estado del player solo si hay track cargado. Si no, sigue
           mandando heartbeat (update-state vacío) para que el backend
           refresque last_seen_at del host. Sin esto el wrapped contaría
           cero minutos si la sesión está creada pero todavía no se ha
           pulsado play. */
        var tr        = null;
        var ct        = 0;
        var dur       = 0;
        var isPlaying = false;
        if (YT_PLAYER && CUR_IDX >= 0 && CUR_IDX < QUEUE.length) {
            tr = QUEUE[CUR_IDX] || null;
            if (tr) {
                try {
                    ct  = Math.floor(YT_PLAYER.getCurrentTime() || 0);
                    dur = Math.floor(YT_PLAYER.getDuration()    || 0);
                    isPlaying = YT_PLAYER.getPlayerState && YT_PLAYER.getPlayerState() === 1;
                } catch (_) {}
            }
        }
        var r = await ltFetch('update-state', { body: {
            videoId:      tr ? tr.videoId : '',
            title:        tr ? (tr.title  || '') : '',
            artist:       tr ? (tr.artist || '') : '',
            coverUrl:     tr ? ('https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg') : '',
            currentTimeS: ct,
            durationS:    dur,
            isPlaying:    isPlaying,
        }});
        if (r && r.ok && r.participants_list) {
            r.participants_list.forEach(function(p){
                if (p.user_key && !LT_KNOWN_PARTICIPANTS[p.user_key]) {
                    LT_KNOWN_PARTICIPANTS[p.user_key] = true;
                    var name = p.label || '?';
                    ltToast({
                        id:      'joiner-' + p.user_key,
                        title:   '🎧 ' + name + ' se unió',
                        message: name + ' está escuchando contigo en tiempo real.',
                        duration: 4500,
                    });
                }
            });
        }
    }
    function ltHostBroadcastDebounced() {
        if (LT_HOST_BCAST_T) clearTimeout(LT_HOST_BCAST_T);
        LT_HOST_BCAST_T = setTimeout(ltHostBroadcast, 600);
    }

    /* ── GUEST: poll + sync ──────────────────────────────── */
    async function ltGuestPoll() {
        if (LT_ROLE !== 'guest') return;
        var r = await ltFetch('poll');
        if (!r || !r.ok) return;
        if (r.closed || !r.session) {
            ltLeaveLocal('El host cerró la sesión.');
            return;
        }
        var s = r.session;
        if (!YT_PLAYER || !s.video_id) return;
        var curVid = '';
        try {
            var d = YT_PLAYER.getVideoData && YT_PLAYER.getVideoData();
            if (d) curVid = d.video_id || '';
        } catch (_) {}
        if (curVid !== s.video_id) {
            try { YT_PLAYER.loadVideoById(s.video_id, parseInt(s.current_time_s, 10) || 0); } catch (_) {}
            /* Actualiza UI del widget: label + título visible cuando se abre fullscreen. */
            try {
                var fa = document.getElementById('mu-full-title');
                if (fa) fa.textContent = s.track_title || '';
                var ar = document.getElementById('mu-full-artist');
                if (ar) ar.textContent = s.track_artist || '';
            } catch (_) {}
            return;
        }
        var myT = 0;
        try { myT = YT_PLAYER.getCurrentTime() || 0; } catch (_) {}
        var hostT = parseInt(s.current_time_s, 10) || 0;
        if (Math.abs(myT - hostT) > 3) {
            try { YT_PLAYER.seekTo(hostT, true); } catch (_) {}
        }
        try {
            var ps = YT_PLAYER.getPlayerState();
            if (parseInt(s.is_playing, 10) === 1 && ps !== 1) YT_PLAYER.playVideo();
            if (parseInt(s.is_playing, 10) === 0 && ps === 1) YT_PLAYER.pauseVideo();
        } catch (_) {}
    }

    /* ── Entrada/salida de sesión ────────────────────────── */
    async function ltStartAsHost() {
        var r = await ltFetch('create', { body: {} });
        if (!r || !r.ok) {
            ltToast({ title: 'Error', message: 'No se pudo crear sesión.', duration: 3500 });
            return null;
        }
        LT_ROLE = 'host';
        LT_SESSION_ID = r.session_id;
        ltHostBroadcast();
        if (LT_HOST_BCAST_INTERVAL) clearInterval(LT_HOST_BCAST_INTERVAL);
        LT_HOST_BCAST_INTERVAL = setInterval(ltHostBroadcast, 5000);
        ltUpdateLiveDot('host');
        return r;
    }

    async function ltJoinAsGuest(inviteId) {
        var r = await ltFetch('accept', { body: { inviteId: inviteId } });
        if (!r || !r.ok) {
            ltToast({ title: 'Error', message: r.error || 'No se pudo unir.', duration: 3500 });
            return;
        }
        LT_ROLE = 'guest';
        LT_SESSION_ID = r.session_id;
        if (LT_GUEST_POLL_T) clearInterval(LT_GUEST_POLL_T);
        ltGuestPoll();
        LT_GUEST_POLL_T = setInterval(ltGuestPoll, 2000);
        ltUpdateLiveDot('guest', r.host_label);
        var name = r.host_label || 'el host';
        ltToast({
            id: 'joined-' + r.session_id,
            title: '🎧 Te has unido a la sesión de ' + name,
            message: 'Ahora escuchas en tiempo real con ' + name + '.',
            duration: 4500,
        });
    }

    async function ltLeave() {
        await ltFetch('leave', { body: {} });
        ltLeaveLocal();
    }
    function ltLeaveLocal(msg) {
        LT_ROLE = null;
        LT_SESSION_ID = null;
        if (LT_GUEST_POLL_T)       { clearInterval(LT_GUEST_POLL_T);       LT_GUEST_POLL_T = null; }
        if (LT_HOST_BCAST_INTERVAL){ clearInterval(LT_HOST_BCAST_INTERVAL); LT_HOST_BCAST_INTERVAL = null; }
        if (LT_HOST_BCAST_T)       { clearTimeout(LT_HOST_BCAST_T); LT_HOST_BCAST_T = null; }
        LT_KNOWN_PARTICIPANTS = {};
        ltUpdateLiveDot(null);
        if (msg) ltToast({ title: 'Sesión cerrada', message: msg, duration: 3500 });
        ltRefreshModalStatus();
    }

    /* ── Modal de invitar ────────────────────────────────── */
    async function ltOpenModal() {
        var modal = document.getElementById('mu-lt-modal');
        if (!modal) return;
        modal.style.display = 'block';
        ltRefreshModalStatus();
        var list = document.getElementById('mu-lt-user-list');
        var r = await ltFetch('users');
        if (!r || !r.ok || !r.users || !r.users.length) {
            list.innerHTML = '<div style="font-size:12px;text-align:center;padding:10px;line-height:1.45;">Aún no tienes amigos que invitar.<br><span style="opacity:0.75;font-size:11px;">Seguíos entre vosotros para haceros amigos.</span></div>';
            return;
        }
        list.innerHTML = r.users.map(function(u){
            return '<div class="mu-lt-user-row">' +
                '<span>' + (u.label || u.user_key) + '</span>' +
                '<button data-userkey="' + u.user_key + '">Invitar</button>' +
                '</div>';
        }).join('');
        list.querySelectorAll('button[data-userkey]').forEach(function(b){
            b.addEventListener('click', async function(){
                if (LT_ROLE !== 'host') {
                    var res = await ltStartAsHost();
                    if (!res) return;
                }
                var k = b.getAttribute('data-userkey');
                var inv = await ltFetch('invite', { body: { toUser: k } });
                if (inv && inv.ok) {
                    b.textContent = '✓ Invitado';
                    b.classList.add('invited');
                    b.disabled = true;
                    ltRefreshModalStatus();
                } else {
                    ltToast({ title: 'Error', message: inv.error || 'No se pudo invitar.', duration: 3500 });
                }
            });
        });
    }
    function ltCloseModal() {
        var modal = document.getElementById('mu-lt-modal');
        if (modal) modal.style.display = 'none';
    }
    function ltRefreshModalStatus() {
        var s = document.getElementById('mu-lt-status');
        var endBtn = document.getElementById('mu-lt-end-btn');
        if (!s) return;
        if (LT_ROLE === 'host') {
            s.innerHTML = '🎤 <strong>Hosteando</strong> una sesión. Invita usuarios para que se unan.';
            if (endBtn) endBtn.style.display = '';
        } else if (LT_ROLE === 'guest') {
            s.innerHTML = '🎧 Estás escuchando como invitado.';
            if (endBtn) endBtn.style.display = '';
        } else {
            s.innerHTML = 'No estás en ninguna sesión. Invita a alguien para empezar.';
            if (endBtn) endBtn.style.display = 'none';
        }
    }

    /* ── Polling de invites de listen-together ──────────────
       Ahora delegado al mNotifPollAll() unificado (definido más abajo,
       fuera de este IIFE). Mantenemos la función como stub porque
       ltBootstrap la referencia, pero ya no hace nada — la unificación
       cubre los invites de listen junto con partner/playlist/item. */
    async function ltPollInvites() { /* no-op: ahora mNotifPollAll */ }
    /* Exposición global para que mNotifPollAll pueda aceptar invites de
       listen-together delegando a ltJoinAsGuest (que vive en este scope). */
    window.ltJoinAsGuestFromNotif = ltJoinAsGuest;
    window.ltFetchFromNotif       = ltFetch;

    /* Wire UI buttons — el botón mu-full-lt ya no existe; la entrada
       a Listen Together se hace desde el menú long-press del vinilo. */
    document.addEventListener('DOMContentLoaded', function(){
        var close = document.getElementById('mu-lt-modal-close');
        if (close) close.addEventListener('click', ltCloseModal);
        var end = document.getElementById('mu-lt-end-btn');
        if (end) end.addEventListener('click', function(){ ltLeave(); ltCloseModal(); });
    });
    /* Exposición global para que openVinylMenu (en otro scope) pueda
       abrir el modal de Listen Together. */
    window.ltOpenModal = ltOpenModal;

    /* Bootstrap: recuperar sesión existente al cargar. */
    (async function ltBootstrap(){
        var r = await ltFetch('current');
        if (!r || !r.ok || !r.role) {
            LT_INVITES_POLL_T = setInterval(ltPollInvites, 5000);
            return;
        }
        LT_ROLE = r.role;
        LT_SESSION_ID = r.session ? r.session.id : null;
        if (LT_ROLE === 'guest') {
            if (LT_GUEST_POLL_T) clearInterval(LT_GUEST_POLL_T);
            LT_GUEST_POLL_T = setInterval(ltGuestPoll, 2000);
            ltGuestPoll();
            ltUpdateLiveDot('guest', r.host_label);
        } else if (LT_ROLE === 'host') {
            if (LT_HOST_BCAST_INTERVAL) clearInterval(LT_HOST_BCAST_INTERVAL);
            LT_HOST_BCAST_INTERVAL = setInterval(ltHostBroadcast, 5000);
            ltUpdateLiveDot('host');
        }
    })();

    /* ── Playback control ── */
    function playCurrent() {
        if (CUR_IDX < 0 || CUR_IDX >= QUEUE.length) return;
        var tr = QUEUE[CUR_IDX];
        if (!tr || !tr.videoId) { next(); return; }

        /* WRAPPED tracking: logea el track ANTERIOR antes de cargar el
           nuevo, con el tiempo escuchado real. Cubre next/prev/end. */
        if (_wrappedLastTrack && _wrappedLastTrack.videoId !== tr.videoId) {
            var listenedPrev = 0;
            try {
                if (YT_PLAYER && typeof YT_PLAYER.getCurrentTime === 'function') {
                    listenedPrev = YT_PLAYER.getCurrentTime() || 0;
                }
            } catch (_) {}
            sendWrappedLog(_wrappedLastTrack, listenedPrev, _wrappedLastPlaylistId);
        }
        _wrappedLastTrack      = tr;
        _wrappedLastPlaylistId = (typeof CUR_PL_ID !== 'undefined' && CUR_PL_ID) ? CUR_PL_ID : null;

        renderTrack(tr);
        document.getElementById('mu-widget').classList.add('visible');
        updateMediaSession(tr);
        startProgressTimer();
        if (!YT_READY || !YT_PLAYER) { pendingLoadId = tr.videoId; return; }
        YT_PLAYER.loadVideoById(tr.videoId);
        broadcast({ type: 'mushell:track', idx: CUR_IDX, total: QUEUE.length, track: tr, plName: CUR_PL_NAME });
        publishNowPlaying(true, true);
        /* Listen-Together: si soy host, broadcast estado al backend (debounced). */
        if (LT_ROLE === 'host') ltHostBroadcastDebounced();
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
    var __NP_TICK = 0;
    function tickProgress() {
        if (!YT_READY || !YT_PLAYER) return;
        var cur = 0, tot = 0;
        try { cur = YT_PLAYER.getCurrentTime() || 0; tot = YT_PLAYER.getDuration() || 0; } catch (_) {}
        var pct = tot > 0 ? Math.min(100, (cur / tot) * 100) : 0;
        var fillEl = document.getElementById('mu-full-progress-fill');
        if (fillEl) fillEl.style.width = pct + '%';
        var c1 = document.getElementById('mu-full-time-cur'); if (c1) c1.textContent = fmt(cur);
        var t1 = document.getElementById('mu-full-time-tot'); if (t1) t1.textContent = fmt(tot);
        /* Sync periódico de posición con la TV cada ~2s para corregir
           drift. Antes era 5s → la TV podía acumular hasta 5s+lag de
           desfase antes de tener data fresca. 2s acerca la "última
           posición conocida" del móvil y, combinado con `age` que
           devuelve el servidor, mantiene a la TV dentro de ~1-2s del
           móvil. publishNowPlaying tiene su propio throttle. */
        if (Date.now() - __NP_TICK > 2000) {
            __NP_TICK = Date.now();
            var playing = false;
            try { playing = YT_PLAYER.getPlayerState() === 1; } catch (_) {}
            publishNowPlaying(playing);
        }
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

    /* Letra: 1 tap → abre panel sobre el player; doble-tap rápido (<400ms)
       → cierra. Esto se delega a window.mLyricsHandleTap expuesto por
       el módulo mLyricsModule más abajo. */
    var lyricsBtn = document.getElementById('mu-full-lyrics');
    if (lyricsBtn) lyricsBtn.addEventListener('click', function(){
        if (typeof window.mLyricsHandleTap === 'function') window.mLyricsHandleTap();
    });

    /* (mu-full-add eliminado — la opción "Añadir a playlist" ahora vive
       solo en el menú long-press del vinilo, evitando duplicidad.) */

    /* "Conectar TV": modal con URL del companion + QR para que el
       usuario lo abra en el navegador del Smart TV. Una vez la TV está
       en /tv.php, hereda automáticamente lo que sueña el móvil. */
    (function attachTvCast(){
        var bd        = document.getElementById('mu-tv-backdrop');
        var closeBtn  = document.getElementById('mu-tv-close');
        var openBtn   = document.getElementById('mu-full-tv');
        var urlInput  = document.getElementById('mu-tv-url');
        var copyBtn   = document.getElementById('mu-tv-copy');
        var urlInline = document.getElementById('mu-tv-url-inline');
        var codeBox   = document.getElementById('mu-tv-code');
        var codeMeta  = document.getElementById('mu-tv-code-meta');

        /* Smart TVs viejas suelen NO terminar el handshake TLS 1.3 del
           túnel cloudflared y quedan en blanco indefinidamente. Si el
           servidor pudo detectar una IP LAN, la usamos como URL de la
           TV: HTTP plano en la WiFi de casa evita todo el problema TLS.
           Fallback: la URL del origin actual (cloudflared u hosting). */
        var lanUrl    = bd.getAttribute('data-lan-url') || '';
        var publicUrl = location.origin + location.pathname.replace(/\/[^\/]*$/, '/') + 'tv.php';
        var tvUrl     = lanUrl || publicUrl;
        /* Mostramos el "http://" completo para que el usuario lo teclee
           tal cual en la TV. Si solo enseñamos el host, muchos browsers
           de Smart TV añaden automáticamente "https://" y rompen el
           polling por cert self-signed. */
        var tvHostUrl = tvUrl;

        var countdown = null;
        function stopCountdown(){ if (countdown) { clearInterval(countdown); countdown = null; } }

        function fmt(code){
            if (!code) return '— — — — — —';
            return code.replace(/(\d{3})(\d{3})/, '$1 $2');
        }

        function loadCode(){
            codeBox.textContent = '— — — — — —';
            codeMeta.textContent = 'Generando…';
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'assets/music/api.php?action=get-tv-code', true);
            xhr.onreadystatechange = function(){
                if (xhr.readyState !== 4) return;
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r && r.code) {
                        codeBox.textContent = fmt(r.code);
                        var secs = r.expiresIn || 300;
                        codeMeta.textContent = 'Expira en ' + Math.floor(secs/60) + ':' + ('0'+(secs%60)).slice(-2);
                        stopCountdown();
                        countdown = setInterval(function(){
                            secs--;
                            if (secs <= 0) {
                                stopCountdown();
                                codeBox.textContent = '— — — — — —';
                                codeMeta.textContent = 'Caducado — pulsa "Regenerar"';
                                return;
                            }
                            codeMeta.textContent = 'Expira en ' + Math.floor(secs/60) + ':' + ('0'+(secs%60)).slice(-2);
                        }, 1000);
                    } else {
                        codeMeta.textContent = r && r.error ? r.error : 'Error generando código';
                    }
                } catch (_) {
                    codeMeta.textContent = 'Error de red';
                }
            };
            xhr.send();
        }

        function open(){
            urlInput.value = tvUrl;
            urlInline.textContent = tvHostUrl;
            bd.hidden = false;
            loadCode();
        }
        function close(){ bd.hidden = true; stopCountdown(); }

        openBtn.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        bd.addEventListener('click', function(e){ if (e.target === bd) close(); });

        codeBox.addEventListener('click', loadCode);

        copyBtn.addEventListener('click', function(){
            urlInput.select();
            try {
                if (navigator.clipboard) navigator.clipboard.writeText(tvUrl);
                else document.execCommand('copy');
                copyBtn.textContent = '✓ Copiado';
                setTimeout(function(){ copyBtn.textContent = 'Copiar'; }, 1500);
            } catch (_) {}
        });
    })();

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

    /* ── Auto-lock al volver del background ──
       Si la app estaba con una pista cargada cuando la pantalla se
       apagó (o el usuario salió a otra app), al volver mostramos el
       lock screen para que tenga la info de "now playing" delante.
       Skip si el lock ya está abierto o si no hay pista. */
    (function attachAutoLock(){
        var wasActiveOnHide = false;
        document.addEventListener('visibilitychange', function(){
            if (document.visibilityState === 'hidden') {
                wasActiveOnHide = (CUR_IDX >= 0);
            } else if (document.visibilityState === 'visible') {
                if (wasActiveOnHide && CUR_IDX >= 0) {
                    var lock = document.getElementById('mu-lock');
                    if (!lock.classList.contains('visible')) openLock();
                }
                wasActiveOnHide = false;
            }
        });
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

    /* ── Modales del shell (action menu + playlist picker + alerts) ──
       Bottom-sheets Win98 reutilizables. El long-press sobre el vinilo
       y el botón "+" del fullscreen usan estos para sus acciones. */

    function getCurrentTrack(){
        return (CUR_IDX >= 0 && QUEUE[CUR_IDX]) ? QUEUE[CUR_IDX] : null;
    }

    function shellOpenModal(opts){
        var bd = document.createElement('div');
        bd.className = 'shell-modal-backdrop';
        bd.innerHTML =
            '<div class="window shell-modal">' +
                '<div class="title-bar">' +
                    '<div class="title-bar-text">' + esc(opts.title || '') + '</div>' +
                    '<div class="title-bar-controls">' +
                        '<button aria-label="Close" type="button"></button>' +
                    '</div>' +
                '</div>' +
                '<div class="window-body"></div>' +
            '</div>';
        document.body.appendChild(bd);
        var body = bd.querySelector('.window-body');
        if (typeof opts.body === 'string') body.innerHTML = opts.body;
        function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
        bd.querySelector('.title-bar-controls button').addEventListener('click', close);
        bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
        return { body: body, close: close, root: bd };
    }
    function shellAlert(msg, title){
        var m = shellOpenModal({
            title: title || 'Aviso',
            body:
                '<div class="shell-modal-msg">' + esc(msg) + '</div>' +
                '<div class="shell-modal-actions">' +
                    '<button class="button default" data-act="ok" type="button">OK</button>' +
                '</div>'
        });
        m.body.querySelector('[data-act="ok"]').addEventListener('click', m.close);
    }
    function shellConfirm(msg, onOk){
        var m = shellOpenModal({
            title: 'Confirmar',
            body:
                '<div class="shell-modal-msg">' + esc(msg) + '</div>' +
                '<div class="shell-modal-actions">' +
                    '<button class="button" data-act="no" type="button">Cancelar</button>' +
                    '<button class="button default" data-act="ok" type="button">OK</button>' +
                '</div>'
        });
        m.body.querySelector('[data-act="no"]').addEventListener('click', m.close);
        m.body.querySelector('[data-act="ok"]').addEventListener('click', function(){
            m.close(); if (typeof onOk === 'function') onOk();
        });
    }

    /* Menú vinilo: 2 acciones (perfil + playlist). */
    function openVinylMenu(){
        var tr = getCurrentTrack(); if (!tr) return;
        /* Etiqueta de Listen Together según rol actual. */
        var ltLabel = 'Escuchar juntos…';
        if (typeof LT_ROLE !== 'undefined') {
            if (LT_ROLE === 'host')  ltLabel = 'Invitar a más usuarios…';
            if (LT_ROLE === 'guest') ltLabel = 'Ver sesión actual';
        }
        var m = shellOpenModal({
            title: tr.title || tr.videoId || 'Canción',
            body:
                '<div class="shell-modal-list">' +
                    '<button class="button shell-modal-item" data-act="profile" type="button">' +
                        '<span class="shell-modal-icon">➕</span>' +
                        '<span>Añadir a mi perfil</span>' +
                    '</button>' +
                    '<button class="button shell-modal-item" data-act="playlist" type="button">' +
                        '<span class="shell-modal-icon">📋</span>' +
                        '<span>Añadir a una playlist</span>' +
                    '</button>' +
                    '<button class="button shell-modal-item" data-act="listen-together" type="button">' +
                        '<span class="shell-modal-icon">🎧</span>' +
                        '<span>' + ltLabel + '</span>' +
                    '</button>' +
                '</div>'
        });
        m.body.querySelector('[data-act="profile"]').addEventListener('click', function(){
            m.close(); addCurrentToProfile(tr);
        });
        m.body.querySelector('[data-act="playlist"]').addEventListener('click', function(){
            m.close(); openShellPlaylistPicker(tr);
        });
        m.body.querySelector('[data-act="listen-together"]').addEventListener('click', function(){
            m.close();
            if (typeof window.ltOpenModal === 'function') window.ltOpenModal();
        });
    }

    /* Picker: fetch playlists del usuario y permite elegir destino. */
    function openShellPlaylistPicker(tr){
        if (!tr) tr = getCurrentTrack();
        if (!tr) return;
        var m = shellOpenModal({
            title: 'Añadir a playlist',
            body: '<div class="shell-modal-msg" style="text-align:center;color:var(--text-faint,#666);">Cargando playlists…</div>'
        });
        fetch('assets/music/api.php?action=get-playlists', { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                var playlists = Array.isArray(d) ? d : [];
                if (!playlists.length) {
                    m.body.innerHTML =
                        '<div class="shell-modal-msg">No tienes playlists todavía.</div>' +
                        '<div class="shell-modal-actions">' +
                            '<button class="button default" data-act="close" type="button">OK</button>' +
                        '</div>';
                    m.body.querySelector('[data-act="close"]').addEventListener('click', m.close);
                    return;
                }
                var html =
                    '<div class="shell-modal-msg">Añadir "' + esc(tr.title || tr.videoId || 'canción') + '" a:</div>' +
                    '<div class="shell-modal-list">';
                playlists.forEach(function(pl, pi){
                    var n = (pl.tracks || []).length;
                    var sharedTag = pl.sharedLabel
                        ? '<span style="font-size:10px;color:var(--text-faint,#666);margin-left:6px;">de ' + esc(pl.sharedLabel) + '</span>'
                        : '';
                    html +=
                        '<button class="button shell-modal-item shell-modal-item-pl" data-pi="' + pi + '" type="button">' +
                            '<div class="shell-modal-item-pl-name">' + esc(pl.name) + sharedTag + '</div>' +
                            '<div class="shell-modal-item-pl-meta">' + n + ' canción' + (n === 1 ? '' : 'es') + '</div>' +
                        '</button>';
                });
                html += '</div>' +
                    '<div class="shell-modal-actions">' +
                        '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                    '</div>';
                m.body.innerHTML = html;
                m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
                m.body.querySelectorAll('[data-pi]').forEach(function(el){
                    el.addEventListener('click', function(){
                        var pi = parseInt(el.dataset.pi, 10);
                        var pl = playlists[pi]; if (!pl) return;
                        m.close(); addTrackToPlaylistShell(pl, tr);
                    });
                });
            })
            .catch(function(){
                m.body.innerHTML =
                    '<div class="shell-modal-msg" style="color:var(--error-text,#c00);">Error cargando playlists.</div>' +
                    '<div class="shell-modal-actions"><button class="button default" data-act="close" type="button">OK</button></div>';
                m.body.querySelector('[data-act="close"]').addEventListener('click', m.close);
            });
    }

    /* POST save-playlist-item con el track añadido. Si hay duplicado por
       videoId, confirmamos antes. */
    function addTrackToPlaylistShell(pl, tr){
        var dup = tr.videoId && (pl.tracks || []).some(function(t){ return t && t.videoId === tr.videoId; });
        function doSave(){
            var newTracks = (pl.tracks || []).slice();
            var trCopy = {};
            for (var k in tr) if (tr.hasOwnProperty(k)) trCopy[k] = tr[k];
            trCopy.addedBy = ME_LABEL;
            newTracks.push(trCopy);
            var payload = { id: pl.id, name: pl.name, tracks: newTracks };
            if (pl.sharedFrom) payload.sharedFrom = pl.sharedFrom;
            fetch('assets/music/api.php?action=save-playlist-item', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d && d.error) { shellAlert(d.error); return; }
                shellAlert('"' + (tr.title || 'Canción') + '" añadida a "' + pl.name + '"', 'Añadida');
            })
            .catch(function(){ shellAlert('Error al guardar.'); });
        }
        if (dup) shellConfirm('"' + (tr.title || 'Canción') + '" ya está en "' + pl.name + '". ¿Añadirla otra vez?', doSave);
        else doSave();
    }

    /* Añade el track actual a la lista de música del perfil del usuario.
       Replica el flow de musica-mobile (GET-merge-POST) para no perder
       reseñas/featured de otros items. Tras guardar, ofrece reseñar. */
    function addCurrentToProfile(tr){
        if (!tr) tr = getCurrentTrack();
        if (!tr) return;
        fetch('assets/profile/api.php?action=get-lists', { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                var music = (Array.isArray(d.music) ? d.music : [])
                    .filter(function(m){ return m && !m.sharedFrom; });
                if (tr.videoId && music.some(function(m){ return m && m.ytId === tr.videoId; })) {
                    shellAlert('"' + (tr.title || tr.videoId) + '" ya está en tu perfil');
                    return;
                }
                var newItem = {
                    id: 'item_' + Date.now(),
                    type: 'song',
                    title: tr.title || tr.videoId || 'Sin título',
                    artist: tr.artist || '',
                    ytId: tr.videoId || '',
                    image: tr.videoId ? 'https://img.youtube.com/vi/' + tr.videoId + '/mqdefault.jpg' : '',
                    featured: false,
                    addedAt: Math.floor(Date.now() / 1000)
                };
                music.push(newItem);
                fetch('assets/profile/api.php?action=save-lists', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ category: 'music', items: music })
                })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (d && d.error) { shellAlert(d.error); return; }
                    /* Localiza el item recién insertado por ytId (único) →
                       el server le asignó un id canónico que necesitamos para
                       guardar la reseña después. */
                    var fresh = (d && Array.isArray(d.items)) ? d.items : music;
                    var saved = tr.videoId
                        ? fresh.find(function(m){ return m && m.ytId === tr.videoId; })
                        : null;
                    if (!saved) {
                        shellAlert('"' + (tr.title || 'Canción') + '" añadida a tu perfil', 'Añadida');
                        return;
                    }
                    /* Prompt: ¿quieres dejar una reseña? */
                    shellReviewPrompt(
                        '"' + (tr.title || 'Canción') + '" añadida. ¿Quieres dejar una reseña?',
                        function(){ shellOpenReviewEditor(saved.id, saved.title || tr.title || ''); }
                    );
                })
                .catch(function(){ shellAlert('Error al guardar.'); });
            })
            .catch(function(){ shellAlert('Error obteniendo el perfil.'); });
    }

    /* Prompt "¿Reseñar?" con dos botones — variante del shellConfirm con
       texto adaptado y botón positivo destacado. */
    function shellReviewPrompt(message, onYes){
        var m = shellOpenModal({
            title: '¿Añadir reseña?',
            body:
                '<div class="shell-modal-msg">' + esc(message) + '</div>' +
                '<div class="shell-modal-actions">' +
                    '<button class="button" data-act="no" type="button">No, gracias</button>' +
                    '<button class="button default" data-act="yes" type="button">★ Sí</button>' +
                '</div>'
        });
        m.body.querySelector('[data-act="no"]').addEventListener('click', m.close);
        m.body.querySelector('[data-act="yes"]').addEventListener('click', function(){
            m.close(); if (typeof onYes === 'function') onYes();
        });
    }

    /* Editor de reseña en el shell. Estrellas con half-star detection
       (tap en mitad izquierda = .5). Save → GET-merge-POST save-lists
       para no pisar otros items del perfil. */
    function shellOpenReviewEditor(musicId, title){
        var sel = 0;
        var bodyHtml =
            '<div class="shell-modal-msg" style="text-align:center;font-weight:bold;">' + esc(title || 'Canción') + '</div>' +
            '<div style="text-align:center;margin:6px 0 12px;">' +
                '<div id="sh-re-stars" style="font-size:32px;line-height:1;color:var(--accent,#000080);user-select:none;"></div>' +
                '<div id="sh-re-num" style="font-size:14px;margin-top:6px;font-weight:bold;min-height:18px;color:var(--accent,#000080);"></div>' +
            '</div>' +
            '<label for="sh-re-comment" style="font-size:11px;display:block;margin-bottom:4px;">Comentario (opcional)</label>' +
            '<textarea id="sh-re-comment" rows="3" maxlength="500" style="width:100%;box-sizing:border-box;resize:vertical;font-family:inherit;font-size:13px;padding:6px 8px;" placeholder="Tu opinión..."></textarea>' +
            '<div class="shell-modal-actions">' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                '<button class="button default" data-act="save" type="button">Guardar</button>' +
            '</div>';
        var m = shellOpenModal({ title: '★ Reseña', body: bodyHtml });
        var starsEl   = m.body.querySelector('#sh-re-stars');
        var numEl     = m.body.querySelector('#sh-re-num');
        var commentEl = m.body.querySelector('#sh-re-comment');

        function paintStar(span, val, pos){
            if (val >= pos)            { span.innerHTML = '★'; span.style.clipPath = ''; }
            else if (val >= pos - 0.5) { span.innerHTML = '★'; span.style.clipPath = 'inset(0 50% 0 0)'; }
            else                       { span.innerHTML = '☆'; span.style.clipPath = ''; }
        }
        function drawStars(){
            starsEl.innerHTML = '';
            for (var i = 1; i <= 5; i++) {
                var s = document.createElement('span');
                s.setAttribute('data-star', String(i));
                s.style.cssText = 'display:inline-block;position:relative;width:1.1em;cursor:pointer;';
                paintStar(s, sel, i);
                starsEl.appendChild(s);
            }
            numEl.textContent = sel > 0 ? String(sel) : '';
        }
        drawStars();
        starsEl.addEventListener('click', function(e){
            var target = e.target.closest('[data-star]');
            if (!target) return;
            var rect = target.getBoundingClientRect();
            var pos  = parseInt(target.getAttribute('data-star'), 10);
            var half = (e.clientX - rect.left) < rect.width / 2;
            sel = half ? pos - 0.5 : pos;
            drawStars();
        });

        m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
        m.body.querySelector('[data-act="save"]').addEventListener('click', function(){
            if (!sel) { shellAlert('Selecciona una puntuación'); return; }
            var review = {
                stars:      sel,
                comment:    (commentEl.value || '').trim(),
                reviewedAt: Math.floor(Date.now() / 1000)
            };
            /* GET-merge-POST: traemos la lista fresca para no pisar
               cambios concurrentes en otros items. */
            fetch('assets/profile/api.php?action=get-lists', { credentials: 'same-origin' })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    var music = (Array.isArray(d.music) ? d.music : [])
                        .filter(function(it){ return it && !it.sharedFrom; });
                    var idx = music.findIndex(function(it){ return it && it.id === musicId; });
                    if (idx === -1) { shellAlert('No se encontró la canción en tu perfil'); m.close(); return; }
                    music[idx].review = review;
                    fetch('assets/profile/api.php?action=save-lists', {
                        method: 'POST', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ category: 'music', items: music })
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(d){
                        if (d && d.error) { shellAlert(d.error); return; }
                        m.close();
                        /* Notify a followers — mismo flow que el editor del perfil. */
                        fetch('assets/profile/api.php?action=notify-review', {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ category: 'music', title: music[idx].title || '', itemType: music[idx].type || 'song' })
                        }).catch(function(){});
                    })
                    .catch(function(){ shellAlert('Error guardando la reseña.'); });
                })
                .catch(function(){ shellAlert('Error obteniendo el perfil.'); });
        });
    }

    /* Long-press sobre el vinilo del fullscreen → openVinylMenu. */
    (function attachVinylLongPress(){
        var vinyl = document.getElementById('mu-vinyl');
        if (!vinyl) return;
        var sx = 0, sy = 0, lpTimer = null, fired = false;
        var LP_MS_V = 500, LP_MOVE_V = 12;
        function start(e){
            var t = (e.touches && e.touches[0]) || e;
            sx = t.clientX || 0; sy = t.clientY || 0;
            fired = false;
            if (lpTimer) clearTimeout(lpTimer);
            lpTimer = setTimeout(function(){
                lpTimer = null; fired = true;
                try { navigator.vibrate && navigator.vibrate(30); } catch (_) {}
                openVinylMenu();
            }, LP_MS_V);
        }
        function move(e){
            if (!lpTimer) return;
            var t = (e.touches && e.touches[0]) || e;
            if (Math.abs((t.clientX||0) - sx) > LP_MOVE_V || Math.abs((t.clientY||0) - sy) > LP_MOVE_V) {
                clearTimeout(lpTimer); lpTimer = null;
            }
        }
        function end(e){
            if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; }
            if (fired && e && e.cancelable) e.preventDefault();
        }
        vinyl.addEventListener('touchstart', start, { passive: true });
        vinyl.addEventListener('touchmove',  move,  { passive: true });
        vinyl.addEventListener('touchend',   end);
        vinyl.addEventListener('touchcancel', end);
        vinyl.addEventListener('mousedown', start);
        document.addEventListener('mouseup', end);
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
            queue: QUEUE, idx: CUR_IDX, track: tr, plName: CUR_PL_NAME,
            playing: !!(YT_PLAYER && YT_PLAYER.getPlayerState && YT_PLAYER.getPlayerState() === 1)
        };
    }
    window.addEventListener('message', function(ev){
        var d = ev.data || {};
        if (d.type === 'mushell:load')          { loadQueue(d.queue, d.idx); subscribers.push(ev.source); }
        else if (d.type === 'mushell:subscribe') { subscribers.push(ev.source); }
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

    /* Acceso a tiempo de reproducción del player — usado por el módulo
       de letras para sincronizar el highlight con el audio. Devuelve 0
       si el player aún no está listo. */
    function getCurrentTime() {
        try { return (YT_PLAYER && YT_PLAYER.getCurrentTime) ? (YT_PLAYER.getCurrentTime() || 0) : 0; }
        catch(_) { return 0; }
    }
    function getDuration() {
        try { return (YT_PLAYER && YT_PLAYER.getDuration) ? (YT_PLAYER.getDuration() || 0) : 0; }
        catch(_) { return 0; }
    }

    return {
        loadQueue: loadQueue, next: next, prev: prev, toggle: togglePlay,
        openFullscreen: openFullscreen, openLock: openLock, getState: getState,
        getCurrentTime: getCurrentTime, getDuration: getDuration,
    };
})();

/* ─── Web Push setup (shell-level) ────────────────────────────────
   Vive aquí, no en perfil-mobile, para que el prompt de permisos
   aparezca al entrar a la PWA, no escondido detrás de un tab.
   - Registra el SW (/service-worker.js, ya servido desde la raíz).
   - Si Notification.permission === 'default' pide permiso una vez.
   - Con permiso concedido, pide la VAPID public, suscribe y POSTea
     la sub al server. Errores silenciosos para no contaminar el shell. */
(function setupPush(){
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    /* Auto-reload del shell cuando un SW NUEVO sustituye a uno viejo.
       Importante saltarlo en el PRIMER install (de null → SW recién
       creado), porque si no la primera apertura se recarga sola y se
       come el banner de permiso antes de que el usuario lo vea. */
    var reloading = false;
    var hadController = !!navigator.serviceWorker.controller;
    navigator.serviceWorker.addEventListener('controllerchange', function(){
        if (reloading || !hadController) return;
        reloading = true;
        location.reload();
    });

    /* Registro del SW como promise reutilizable. El banner del primer
       install puede mostrarse ANTES de que esto termine; si el usuario
       acepta, el subscribe espera al `then`. */
    var regPromise = navigator.serviceWorker.register('service-worker.js');

    /* Si la pestaña empieza SIN controller (primer install), mostramos el
       banner ya — no esperamos a que SW.register termine para no perder
       la primera oportunidad de pedir permiso. */
    if (!hadController && Notification.permission === 'default') {
        showPushBanner(function(){
            Notification.requestPermission().then(function(perm){
                if (perm !== 'granted') return;
                regPromise.then(subscribeToPush).catch(function(){});
            });
        });
    }

    regPromise.then(function(reg){
        /* Fuerza al browser a comprobar si hay una versión nueva del SW
           en cada carga del shell. Si la hay, se descarga, instala y
           activa inmediatamente (skipWaiting), disparando controllerchange. */
        reg.update().catch(function(){});
        if (Notification.permission === 'denied') return;
        /* Si ya está concedido (la PWA se recarga, o reentras), suscribimos
           de inmediato — no hace falta volver a pedir permiso. */
        if (Notification.permission === 'granted') {
            subscribeToPush(reg);
            return;
        }
        /* Chrome/Safari/Firefox bloquean Notification.requestPermission()
           sin gesto del usuario. Inyectamos un banner en mobile.php que el
           usuario tiene que tocar conscientemente — el tap del botón cuenta
           como gesto y Chrome abre el prompt nativo. */
        showPushBanner(function(){
            Notification.requestPermission().then(function(perm){
                if (perm === 'granted') subscribeToPush(reg);
            });
        });
    }).catch(function(){});

    function showPushBanner(onAccept) {
        if (document.getElementById('push-perm-banner')) return;
        var bd = document.createElement('div');
        bd.id = 'push-perm-banner';
        bd.style.cssText = [
            'position:fixed', 'left:0', 'right:0',
            'top:env(safe-area-inset-top,0)',
            'z-index:9500',
            'padding:10px 12px',
            'background:var(--accent,#000080)',
            'color:var(--accent-text,#fff)',
            'display:flex', 'align-items:center', 'gap:10px',
            'font-size:12px',
            'box-shadow:0 2px 6px rgba(0,0,0,0.4)'
        ].join(';');
        bd.innerHTML =
            '<span style="flex:1;"><img src="assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Activa las notificaciones para no perderte mensajes.</span>' +
            '<button class="button" id="push-perm-ok"    type="button" style="min-height:26px;font-size:11px;">Activar</button>' +
            '<button class="button" id="push-perm-skip"  type="button" style="min-height:26px;font-size:11px;">Luego</button>';
        document.body.appendChild(bd);
        function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
        document.getElementById('push-perm-ok').addEventListener('click', function(){
            close(); onAccept();
        });
        document.getElementById('push-perm-skip').addEventListener('click', close);
    }

    function subscribeToPush(reg) {
        fetch('assets/profile/api.php?action=get-vapid-public-key', { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d || !d.ok || !d.publicKey) return;
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(d.publicKey)
                });
            })
            .then(function(sub){
                if (!sub) return;
                var json = sub.toJSON();
                fetch('assets/profile/api.php?action=save-push-subscription', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        endpoint: json.endpoint,
                        p256dh:   json.keys && json.keys.p256dh,
                        auth:     json.keys && json.keys.auth
                    })
                });
            })
            .catch(function(){});
    }

    function urlBase64ToUint8Array(b64) {
        var padding = '='.repeat((4 - b64.length % 4) % 4);
        var base64 = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = atob(base64);
        var out = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
        return out;
    }
})();
</script>

<script>
/* ════════════════════════════════════════
   PANEL DE AJUSTES + CAMBIAR CONTRASEÑA
   ────────────────────────────────────────
   - Panel: tap en "⚙ Ajustes" del statusbar → muestra backdrop con el
     panel Win98 centrado. Cierra con X, tap fuera, o Esc.
   - Cambiar contraseña: dentro del panel → abre modal con 3 inputs.
     Validación cliente + POST a assets/auth/change-password.php (el
     mismo endpoint que usa el escritorio). Status en línea con
     resultado del backend.
════════════════════════════════════════ */
(function(){
    /* ── panel ajustes ── */
    var settingsLink = document.getElementById('mh-settings-link');
    var settingsBp   = document.getElementById('mh-settings-backdrop');
    var settingsClose = document.getElementById('mh-settings-close');

    function openSettings(e){
        if (e) e.preventDefault();
        settingsBp.classList.add('is-open');
    }
    function closeSettings(){ settingsBp.classList.remove('is-open'); }

    if (settingsLink)  settingsLink.addEventListener('click', openSettings);
    if (settingsClose) settingsClose.addEventListener('click', closeSettings);
    /* Tap en el backdrop (no en la ventana) cierra. */
    if (settingsBp) settingsBp.addEventListener('click', function(e){
        if (e.target === settingsBp) closeSettings();
    });

    /* ── modal cambiar contraseña ── */
    var cpOpen   = document.getElementById('mh-set-change-password');
    var cpBp     = document.getElementById('mh-cp-backdrop');
    var cpClose  = document.getElementById('mh-cp-close');
    var cpCancel = document.getElementById('mh-cp-cancel');
    var cpOk     = document.getElementById('mh-cp-ok');
    var cpCurrent = document.getElementById('mh-cp-current');
    var cpNew     = document.getElementById('mh-cp-new');
    var cpConfirm = document.getElementById('mh-cp-confirm');
    var cpStatus  = document.getElementById('mh-cp-status');

    function cpReset(){
        cpCurrent.value = ''; cpNew.value = ''; cpConfirm.value = '';
        cpStatus.textContent = ''; cpStatus.style.color = '';
        cpOk.disabled = false;
    }
    function cpOpenModal(){
        cpReset();
        closeSettings();
        cpBp.classList.add('is-open');
        setTimeout(function(){ cpCurrent.focus(); }, 30);
    }
    function cpCloseModal(){ cpBp.classList.remove('is-open'); cpReset(); }

    if (cpOpen)   cpOpen.addEventListener('click', cpOpenModal);
    if (cpClose)  cpClose.addEventListener('click', cpCloseModal);
    if (cpCancel) cpCancel.addEventListener('click', cpCloseModal);
    if (cpBp) cpBp.addEventListener('click', function(e){
        if (e.target === cpBp) cpCloseModal();
    });

    async function cpSubmit(){
        var c = cpCurrent.value, n = cpNew.value, r = cpConfirm.value;
        function err(msg){ cpStatus.style.color = 'var(--error-text,#c00)'; cpStatus.textContent = msg; }
        if (!c || !n || !r)        return err('Rellena todos los campos.');
        if (n.length < 6)          return err('La nueva debe tener al menos 6 caracteres.');
        if (n !== r)               return err('La nueva contraseña y la repetición no coinciden.');
        if (c === n)               return err('La nueva tiene que ser distinta de la actual.');

        cpOk.disabled = true;
        cpStatus.style.color = 'var(--text-muted,#666)';
        cpStatus.textContent = 'Guardando…';
        try {
            var resp = await fetch('assets/auth/change-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current: c, new: n })
            });
            var data = await resp.json().catch(function(){ return { error: 'Respuesta inválida' }; });
            if (!resp.ok || data.error){
                err('✗ ' + (data.error || ('HTTP ' + resp.status)));
                cpOk.disabled = false;
                return;
            }
            cpStatus.style.color = 'var(--accent-deep,#080)';
            cpStatus.textContent = '✔ Contraseña actualizada.';
            setTimeout(cpCloseModal, 900);
        } catch (e) {
            err('✗ Error de red: ' + e.message);
            cpOk.disabled = false;
        }
    }
    if (cpOk) cpOk.addEventListener('click', cpSubmit);
    [cpCurrent, cpNew, cpConfirm].forEach(function(el){
        if (!el) return;
        el.addEventListener('keydown', function(ev){
            if (ev.key === 'Enter'){ ev.preventDefault(); cpSubmit(); }
            else if (ev.key === 'Escape'){ ev.preventDefault(); cpCloseModal(); }
        });
    });

    /* Esc global cierra cualquier panel abierto. */
    document.addEventListener('keydown', function(ev){
        if (ev.key !== 'Escape') return;
        if (cpBp && cpBp.classList.contains('is-open')) cpCloseModal();
        else if (settingsBp && settingsBp.classList.contains('is-open')) closeSettings();
    });
})();

/* ════════════════════════════════════════════════════════════════
   NOTIFICACIONES UNIFICADAS — sondea cada 15s assets/notifications/
   pending.php y muestra como toast los invites pendientes de las 4
   fuentes (listen, partner, playlist, item).
   - Al cargar la app: fetch inmediato → si hay pending, los muestra.
   - Deep-link #notif=<source> (viene de tap en push): el primer invite
     de esa fuente se abre como SHEET full-overlay (más visible) en vez
     de toast pequeño.
   ════════════════════════════════════════════════════════════════ */
(function mNotifSystem(){
    var SHOWN = {};   /* {source: {id: true}} → evita spam */
    var POLL_T = null;
    var POLL_URL = 'assets/notifications/pending.php';

    /* Toast reusable (idéntico estilo a ltToast). */
    function mToast(opts) {
        var existing = document.getElementById('m-toast-' + (opts.id || ''));
        if (existing) existing.remove();
        var t = document.createElement('div');
        t.id = 'm-toast-' + (opts.id || Date.now());
        t.className = 'mu-lt-toast';
        if (opts.sheet) t.classList.add('mu-lt-toast-sheet');
        var html = '';
        if (opts.title)   html += '<div class="toast-title">'   + opts.title   + '</div>';
        if (opts.message) html += '<div class="toast-msg">'     + opts.message + '</div>';
        if (opts.actions && opts.actions.length) {
            html += '<div class="toast-actions">';
            opts.actions.forEach(function(a, i){
                html += '<button data-act-i="' + i + '">' + a.label + '</button>';
            });
            html += '</div>';
        }
        t.innerHTML = html;
        document.body.appendChild(t);
        if (opts.actions && opts.actions.length) {
            t.querySelectorAll('button[data-act-i]').forEach(function(b){
                b.addEventListener('click', function(){
                    var i = +b.getAttribute('data-act-i');
                    if (opts.actions[i] && opts.actions[i].fn) opts.actions[i].fn();
                    t.remove();
                });
            });
        }
        if (!opts.actions || !opts.actions.length) {
            setTimeout(function(){ if (t.parentNode) t.remove(); }, opts.duration || 4500);
        }
    }

    function fetchJSON(url, opts) {
        opts = opts || {};
        var init = { credentials: 'same-origin', method: opts.method || 'GET' };
        if (opts.body) {
            init.headers = { 'Content-Type': 'application/json' };
            init.body    = JSON.stringify(opts.body);
        }
        return fetch(url, init).then(function(r){ return r.json(); })
                               .catch(function(){ return { ok: false }; });
    }

    /* Handlers por fuente. Cada uno define icono, mensaje y acciones. */
    var HANDLERS = {
        listen: function(inv, asSheet){
            mToast({
                id:    'listen-' + inv.id,
                sheet: asSheet,
                title: '🎧 ' + (inv.fromLabel || '?') + ' te invita',
                message: inv.trackTitle
                    ? 'Escuchar juntos: ' + inv.trackTitle
                    : 'Escuchar juntos en directo',
                actions: [
                    { label: 'Rechazar', fn: function(){
                        if (window.ltFetchFromNotif) {
                            window.ltFetchFromNotif('decline', { body: { inviteId: inv.id } });
                        }
                    }},
                    { label: 'Aceptar', fn: function(){
                        if (window.ltJoinAsGuestFromNotif) {
                            window.ltJoinAsGuestFromNotif(inv.id);
                        }
                    }},
                ],
            });
        },
        partner: function(inv, asSheet){
            mToast({
                id:    'partner-' + inv.id,
                sheet: asSheet,
                title: '💌 ' + (inv.fromLabel || '?') + ' quiere ser tu pareja',
                message: 'Acepta para vincular vuestros calendarios.',
                actions: [
                    { label: 'Rechazar', fn: function(){
                        fetchJSON('assets/couple/api.php?action=respond-partner-invite',
                            { method: 'POST', body: { inviteId: inv.id, response: 'reject' } });
                    }},
                    { label: 'Aceptar', fn: function(){
                        fetchJSON('assets/couple/api.php?action=respond-partner-invite',
                            { method: 'POST', body: { inviteId: inv.id, response: 'accept' } });
                    }},
                ],
            });
        },
        playlist: function(inv, asSheet){
            mToast({
                id:    'playlist-' + inv.id,
                sheet: asSheet,
                title: '📋 ' + (inv.fromLabel || '?') + ' te invita',
                message: 'Colaborar en "' + (inv.playlistName || 'playlist') + '"',
                actions: [
                    { label: 'Rechazar', fn: function(){
                        fetchJSON('assets/music/api.php?action=respond-invite',
                            { method: 'POST', body: { inviteId: inv.id, action: 'reject' } });
                    }},
                    { label: 'Aceptar', fn: function(){
                        fetchJSON('assets/music/api.php?action=respond-invite',
                            { method: 'POST', body: { inviteId: inv.id, action: 'accept' } });
                    }},
                ],
            });
        },
        item: function(inv, asSheet){
            mToast({
                id:    'item-' + inv.id,
                sheet: asSheet,
                title: '➕ ' + (inv.fromLabel || '?') + ' te invita',
                message: 'Colaborar en "' + (inv.itemTitle || 'item') + '"',
                actions: [
                    { label: 'Rechazar', fn: function(){
                        fetchJSON('assets/profile/api.php?action=respond-item-invite',
                            { method: 'POST', body: { inviteId: inv.id, action: 'reject' } });
                    }},
                    { label: 'Aceptar', fn: function(){
                        fetchJSON('assets/profile/api.php?action=respond-item-invite',
                            { method: 'POST', body: { inviteId: inv.id, action: 'accept' } });
                    }},
                ],
            });
        },
    };

    /* Sondeo: descarga pending → muestra los nuevos. Si la URL tiene un
       deep-link de push (#notif=<source>), el primer invite de esa
       fuente sale como SHEET y bypasea el SHOWN-tracking para que
       siempre vuelva a aparecer al tap (incluso si el toast se
       descartó antes). */
    function poll(checkDeepLink) {
        return fetchJSON(POLL_URL).then(function(d){
            if (!d || !d.ok || !Array.isArray(d.invites)) return;
            var deepSource = null;
            if (checkDeepLink) {
                var m = /#notif=([a-z]+)/i.exec(location.hash);
                if (m) {
                    deepSource = m[1].toLowerCase();
                    try { history.replaceState(null, '', location.pathname); } catch (_) {}
                }
            }
            var deepShown = false;
            d.invites.forEach(function(inv){
                var src = inv.source;
                if (!HANDLERS[src]) return;
                SHOWN[src] = SHOWN[src] || {};
                var isDeep = (deepSource === src) && !deepShown;
                if (SHOWN[src][inv.id] && !isDeep) return;
                SHOWN[src][inv.id] = true;
                if (isDeep) deepShown = true;
                HANDLERS[src](inv, isDeep);
            });
        });
    }

    /* Estilo extra para "sheet" — toast ampliado al centro de la pantalla
       cuando viene de un deep-link de push. */
    var st = document.createElement('style');
    st.textContent = '.mu-lt-toast-sheet { bottom: auto !important; top: 50% !important; '
        + 'transform: translate(-50%, -50%) !important; min-width: 280px; max-width: 92vw; '
        + 'padding: 14px 18px !important; }';
    document.head.appendChild(st);

    /* Arranque: poll inmediato + cada 15s.
       hashchange: si llega un #notif=<source> mientras la app está
       abierta (lo dispara el SW al navegar tras tap en push), re-poll
       con detección de deep-link para sacar la sheet. */
    poll(true);
    POLL_T = setInterval(function(){ poll(false); }, 15000);
    window.addEventListener('hashchange', function(){
        if (/#notif=/i.test(location.hash)) poll(true);
    });
})();

/* Heartbeat de presencia — móvil, mismo endpoint que desktop. */
(function mPresenceHeartbeat(){
    function ping(){
        fetch('assets/profile/api.php?action=heartbeat', {
            method: 'POST', credentials: 'same-origin'
        }).catch(function(){});
    }
    ping();
    setInterval(ping, 30000);
})();

/* ════════════════════════════════════════════════════════════════
   LETRAS (LRCLIB) — overlay full-screen sobre el reproductor móvil.
   ════════════════════════════════════════════════════════════════ */
(function mLyricsModule() {
    /* Panel de letras montado DENTRO del fullscreen player (#mu-full).
       No es overlay full-screen — solo aparece sobre el contenido del
       player y oscurece ligeramente lo que hay detrás. */
    var muFull = document.getElementById('mu-full');
    var muFullWin = muFull ? muFull.querySelector('.mu-full-window') : null;
    var overlay = document.createElement('div');
    overlay.id = 'm-lyrics-overlay';
    overlay.innerHTML =
        '<div id="m-lyr-scroll">' +
            '<div id="m-lyr-empty">Cargando…</div>' +
            '<div id="m-lyr-lines"></div>' +
        '</div>';
    /* Lo inyectamos dentro del .mu-full-window para que respete sus
       bordes y bezels Win98. Posicionamiento absoluto via CSS. */
    if (muFullWin) muFullWin.appendChild(overlay);
    else document.body.appendChild(overlay);

    /* CSS del overlay + dim del player subyacente. */
    var st = document.createElement('style');
    st.textContent =
        /* Overlay extra transparente — apenas un tinte oscuro para que
           el contenido del reproductor siga siendo claramente visible
           detrás, con un blur suave que da el efecto vidrio.
           USAMOS visibility + opacity en lugar de display:none para
           que las transiciones de los HIJOS (letra) funcionen — con
           display:none los hijos no están en el render tree y CSS no
           dispara su transición al cambiar el display. */
        '#m-lyrics-overlay { position: absolute; inset: 0; ' +
            'background: rgba(0,0,0,0.12); ' +
            'backdrop-filter: blur(4px) saturate(1.05); ' +
            '-webkit-backdrop-filter: blur(4px) saturate(1.05); ' +
            'color: #fff; display: flex; flex-direction: column; z-index: 20; ' +
            'opacity: 0; visibility: hidden; pointer-events: none; ' +
            'transition: opacity 2.4s ease, backdrop-filter 2.4s ease, visibility 0s linear 2.4s; }' +
        '.mu-full.lyrics-active #m-lyrics-overlay { opacity: 1; visibility: visible; pointer-events: auto; ' +
            'transition: opacity 2.4s ease, backdrop-filter 2.4s ease, visibility 0s linear 0s; }' +
        /* Dim suave del contenido subyacente — sigue siendo claramente
           visible (45% opacidad, blur mínimo) para que se vea el
           reproductor original a través de las letras. */
        '.mu-full .title-bar, .mu-full-display, .mu-full-info, .mu-full-progress-row, .mu-full-controls, .mu-full-extras { ' +
            'transition: opacity 2.4s ease, filter 2.4s ease; }' +
        '.mu-full.lyrics-active .title-bar, ' +
        '.mu-full.lyrics-active .mu-full-display, ' +
        '.mu-full.lyrics-active .mu-full-info, ' +
        '.mu-full.lyrics-active .mu-full-progress-row, ' +
        '.mu-full.lyrics-active .mu-full-controls, ' +
        '.mu-full.lyrics-active .mu-full-extras { opacity: 0.45; filter: blur(1px); }' +
        '#m-lyr-scroll { flex: 1; overflow-y: auto; padding: 26px 22px; font-size: 17px; line-height: 1.8; text-align: center; -webkit-overflow-scrolling: touch; ' +
            /* Scrollbar invisible (Firefox + Chrome/Safari/Edge) — scroll
               sigue funcionando con touch/wheel pero no se ve la barra. */
            'scrollbar-width: none; -ms-overflow-style: none; ' +
            /* Entrada: empieza translucida y desplazada un poco abajo;
               cierra → vuelve a ese estado. Transición 2s. */
            'opacity: 0; transform: translateY(24px); ' +
            'transition: opacity 2s ease, transform 2s ease; }' +
        '#m-lyr-scroll::-webkit-scrollbar { display: none; width: 0; height: 0; }' +
        /* Cuando el overlay está activo, la letra entra con un pequeño
           DELAY (0.5s) para que el efecto se sienta "después" del fondo. */
        '.mu-full.lyrics-active #m-lyr-scroll { opacity: 1; transform: translateY(0); ' +
            'transition: opacity 2s ease 0.5s, transform 2s ease 0.5s; }' +
        '#m-lyr-empty { opacity: 0.65; padding-top: 35%; font-size: 15px; }' +
        '.m-lyr-line { padding: 8px 0; color: rgba(255,255,255,0.5); transition: color 0.25s, transform 0.25s, opacity 0.25s, font-weight 0.25s; opacity: 0.7; }' +
        '.m-lyr-line.active { color: var(--accent, #1db954); font-weight: bold; opacity: 1; transform: scale(1.1); text-shadow: 0 0 10px color-mix(in srgb, var(--accent) 50%, transparent); }' +
        '.m-lyr-line.past { opacity: 0.3; }';
    document.head.appendChild(st);

    /* Double-tap detection: cuando el overlay está activo, dos taps en
       <400ms en cualquier parte → cierra. El scroll de las letras no
       dispara click (touchmove cancela el click), así que esto NO
       interfiere con el scrolling. */
    var __overlayLastTap = 0;
    overlay.addEventListener('click', function() {
        if (!LYR_OPEN) return;
        var now = Date.now();
        if (now - __overlayLastTap < 400) {
            closeLyr();
            __overlayLastTap = 0;
        } else {
            __overlayLastTap = now;
        }
    });

    var LYR_VID = null, LYR_LINES = null, LYR_PLAIN = null;
    var LYR_LAST = -1, LYR_OPEN = false, LYR_TIMER = null;

    var linesEl  = overlay.querySelector('#m-lyr-lines');
    var emptyEl  = overlay.querySelector('#m-lyr-empty');
    var scrollEl = overlay.querySelector('#m-lyr-scroll');
    /* Stubs no-op para el código de fetch que antes actualizaba header
       (header eliminado per UX request — solo lyrics centradas). */
    var trackEl  = { textContent: '' };
    var statusEl = { textContent: '' };
    /* Sin botón close — el cierre es exclusivo por doble-tap sobre el
       overlay (handler en línea más arriba). */

    function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function parseLRC(lrc) {
        var lines = [];
        if (!lrc) return lines;
        lrc.split(/\r?\n/).forEach(function(raw){
            var stamps = []; var rest = raw;
            var re = /^\s*\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\]/;
            var m;
            while ((m = re.exec(rest))) {
                var min = +m[1], sec = +m[2], frac = m[3] ? +m[3] / Math.pow(10, m[3].length) : 0;
                stamps.push(min * 60 + sec + frac);
                rest = rest.slice(m[0].length);
            }
            var text = rest.trim();
            if (stamps.length && text) stamps.forEach(function(t){ lines.push({ time:t, text:text }); });
        });
        lines.sort(function(a,b){ return a.time - b.time; });
        return lines;
    }

    function curTrack() {
        /* QUEUE/CUR_IDX viven dentro del IIFE de MuShell y no son visibles
           aquí. Usamos su API pública. */
        try {
            if (window.MuShell && typeof window.MuShell.getState === 'function') {
                return window.MuShell.getState().track || null;
            }
        } catch(_){}
        return null;
    }
    function curIdx() {
        try {
            if (window.MuShell && typeof window.MuShell.getState === 'function') {
                var s = window.MuShell.getState();
                return (typeof s.idx === 'number') ? s.idx : -1;
            }
        } catch(_){}
        return -1;
    }

    function openLyr() {
        if (muFull) muFull.classList.add('lyrics-active');
        LYR_OPEN = true;
        fetchForCurrent();
        if (LYR_TIMER) clearInterval(LYR_TIMER);
        LYR_TIMER = setInterval(tick, 250);
    }
    function closeLyr() {
        if (muFull) muFull.classList.remove('lyrics-active');
        LYR_OPEN = false;
        if (LYR_TIMER) { clearInterval(LYR_TIMER); LYR_TIMER = null; }
    }
    window.mLyricsOpen = openLyr;
    /* Botón del micrófono: solo ABRE. El cierre vive en el overlay
       (doble-tap sobre el panel oscuro). Si el panel ya está abierto
       y se vuelve a tocar el botón → no hace nada. */
    window.mLyricsHandleTap = function() {
        if (!LYR_OPEN) openLyr();
    };

    function setEmpty(msg) {
        emptyEl.style.display = 'block';
        emptyEl.textContent = msg;
        linesEl.innerHTML = '';
    }

    function fetchForCurrent() {
        var tr = curTrack();
        if (!tr || !tr.videoId) { setEmpty('No hay canción en reproducción.'); return; }
        if (tr.videoId === LYR_VID && (LYR_LINES || LYR_PLAIN)) return;
        LYR_VID = tr.videoId; LYR_LINES = null; LYR_PLAIN = null; LYR_LAST = -1;
        trackEl.textContent = (tr.title || '') + (tr.artist ? ' — ' + tr.artist : '');
        setEmpty('Buscando letra…');
        statusEl.textContent = '';
        var dur = 0;
        try { dur = Math.floor((window.MuShell ? window.MuShell.getDuration() : 0) || 0); } catch(_){}
        var qs = new URLSearchParams({
            title: tr.title || '', artist: tr.artist || '', duration: String(dur),
        });
        fetch('assets/music/api.php?action=get-lyrics&' + qs.toString(), { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d || !d.ok || !d.found) { setEmpty('🥲 No se encontró letra para esta canción.'); return; }
                if (LYR_VID !== tr.videoId) return;   // cambió de track mid-fetch
                if (d.synced) {
                    LYR_LINES = parseLRC(d.synced);
                    if (LYR_LINES.length) {
                        emptyEl.style.display = 'none';
                        linesEl.innerHTML = LYR_LINES.map(function(ln, i){
                            return '<div class="m-lyr-line" data-i="' + i + '">' + escHtml(ln.text) + '</div>';
                        }).join('');
                        statusEl.textContent = '⏱ sincronizada';
                        return;
                    }
                    LYR_PLAIN = d.plain;
                }
                if (d.plain || LYR_PLAIN) {
                    LYR_PLAIN = LYR_PLAIN || d.plain;
                    emptyEl.style.display = 'none';
                    linesEl.innerHTML = '<div style="text-align:center;">' +
                        escHtml(LYR_PLAIN).replace(/\n/g, '<br>') + '</div>';
                    statusEl.textContent = 'sin sync';
                    return;
                }
                setEmpty('🥲 No se encontró letra para esta canción.');
            })
            .catch(function(){ setEmpty('Error de red al buscar la letra.'); });
    }

    function tick() {
        if (!LYR_OPEN || !LYR_LINES || !LYR_LINES.length) return;
        var t = 0;
        try { t = (window.MuShell ? window.MuShell.getCurrentTime() : 0) || 0; } catch(_){}
        var lo = 0, hi = LYR_LINES.length - 1, idx = -1;
        while (lo <= hi) {
            var mid = (lo + hi) >> 1;
            if (LYR_LINES[mid].time <= t) { idx = mid; lo = mid + 1; } else { hi = mid - 1; }
        }
        if (idx === LYR_LAST) return;
        LYR_LAST = idx;
        var all = linesEl.querySelectorAll('.m-lyr-line');
        for (var i = 0; i < all.length; i++) {
            all[i].classList.remove('active', 'past');
            if (i < idx) all[i].classList.add('past');
            if (i === idx) all[i].classList.add('active');
        }
        var activeEl = all[idx];
        if (activeEl) {
            var top = activeEl.offsetTop - scrollEl.clientHeight / 2 + activeEl.clientHeight / 2;
            scrollEl.scrollTo({ top: top, behavior: 'smooth' });
        }
    }

    /* Watcher de cambio de track: cada 500ms revisa si el idx cambió.
       Usamos curIdx() porque CUR_IDX está dentro del IIFE de MuShell. */
    var __lastCurIdx = -1;
    setInterval(function(){
        if (!LYR_OPEN) return;
        var idx = curIdx();
        if (idx !== __lastCurIdx) {
            __lastCurIdx = idx;
            fetchForCurrent();
        }
    }, 500);
})();

/* Polling de recordatorios próximos (7/2/1 días). Mismo endpoint que
   desktop. Aquí no tenemos notifSystem; reusamos el estilo de toast
   Win98 inferior creando una instancia local con la misma CSS class
   .mu-lt-toast que ya está definida en este archivo. */
(function mRemindersPoll(){
    function whenLabel(t){
        if (t === 1) return 'mañana';
        if (t === 2) return 'en 2 días';
        if (t === 7) return 'en 1 semana';
        return 'en ' + t + ' días';
    }
    function showReminderToast(rm) {
        var existing = document.getElementById('reminder-toast-' + rm.id + '-' + rm.threshold);
        if (existing) existing.remove();
        var t = document.createElement('div');
        t.id = 'reminder-toast-' + rm.id + '-' + rm.threshold;
        t.className = 'mu-lt-toast';
        t.innerHTML =
            '<div class="toast-title">🔔 Recordatorio</div>' +
            '<div class="toast-msg">' + (rm.titulo || '') + ' ' + whenLabel(rm.threshold) + '</div>';
        document.body.appendChild(t);
        setTimeout(function(){ if (t.parentNode) t.remove(); }, 8000);
    }
    function poll(){
        fetch('assets/couple/api.php?action=upcoming-reminders', { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d || !d.ok || !Array.isArray(d.reminders)) return;
                d.reminders.forEach(showReminderToast);
            })
            .catch(function(){});
    }
    setTimeout(poll, 5000);
    setInterval(poll, 5 * 60 * 1000);
})();
</script>

</body>
</html>
