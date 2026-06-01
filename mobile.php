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
                $target  = $app['external'] ? ' target="_blank" rel="noopener"' : '';
                $chev    = $app['external'] ? '↗' : '›';
            ?>
                <a class="mh-row" href="<?= htmlspecialchars($app['url']) ?>"<?= $target ?>>
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

/* ─── Service worker (necesario para que Chrome ofrezca instalar la
       PWA por si alguien aterrizó aquí sin instalar). El SW está
       registrado pero su scope queda contenido — la verdadera
       instalación se hace desde mobile-landing.php. */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js', { scope: '<?= $projectBaseUrl ?>' })
        .catch(function(err){ console.warn('[mobile] sw register fail', err); });
}
</script>

</body>
</html>
