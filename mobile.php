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
    ['name' => 'Tienda',       'url' => 'apps/tienda.php',                                                      'emoji' => '🛒', 'icon' => null,                                       'external' => false, 'wip' => false],
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
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="theme-color" content="#0c2b54">
    <title>Scrapbook Melon</title>
    <link rel="icon" href="data:,">
    <link rel="manifest" href="manifest.php<?= $tokenForManifest !== '' ? '?token=' . htmlspecialchars($tokenForManifest) : '' ?>">
    <link rel="apple-touch-icon" href="assets/img/start-icons/capi-start-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Melon Hub">
    <link rel="stylesheet" href="assets/css/mobile.css">
</head>
<body>
<div id="screen">

    <!-- Barra de estado tipo móvil antiguo -->
    <div id="status-bar">
        <div class="status-left">
            <span class="signal">●●●●</span>
            <span class="provider">MELON</span>
        </div>
        <div class="status-right">
            <span id="status-clock">--:--</span>
            <span class="battery">▮▮▮</span>
        </div>
    </div>

    <!-- Cabecera con saludo y fecha -->
    <header id="mob-header">
        <?php if ($userImg): ?>
            <img class="mob-avatar" src="<?= htmlspecialchars($userImg) ?>" alt="">
        <?php endif; ?>
        <div class="mob-title">
            <div class="mob-title-name">Scrapbook Melon</div>
            <div class="mob-title-user">Hola, <?= htmlspecialchars($userLabel) ?></div>
        </div>
        <div id="mob-date">—</div>
    </header>

    <!-- Lista de apps (orden alfabético, se calcula en PHP arriba) -->
    <main id="app-list" role="list">
        <?php foreach ($apps as $app):
            $hasIcon = $app['icon'] && file_exists(__DIR__ . '/' . $app['icon']);
            $target  = $app['external'] ? ' target="_blank" rel="noopener"' : '';
            $chev    = $app['external'] ? '↗' : '›';
        ?>
            <a class="app-row<?= $app['wip'] ? ' wip' : '' ?>" role="listitem" href="<?= htmlspecialchars($app['url']) ?>"<?= $target ?>>
                <div class="app-icon">
                    <?php if ($hasIcon): ?>
                        <img src="<?= htmlspecialchars($app['icon']) ?>" alt="">
                    <?php else: ?>
                        <span class="app-icon-emoji"><?= htmlspecialchars($app['emoji']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="app-name"><?= htmlspecialchars($app['name']) ?></div>
                <div class="app-chevron"><?= htmlspecialchars($chev) ?></div>
            </a>
        <?php endforeach; ?>
    </main>

    <!-- Soft keys del pie estilo Nokia/Symbian -->
    <footer id="soft-keys">
        <a class="soft-key" href="<?= strtolower(htmlspecialchars($userLabel)) ?>-desktop.php?desktop=1">Versión PC</a>
        <a class="soft-key danger" href="logout.php">Cerrar sesión</a>
    </footer>

</div>

<!-- Banner de instalación PWA (Android / Chrome / Edge) -->
<div id="install-banner" hidden>
    <div class="install-msg">
        <strong>Instala Melon</strong>
        <span>Añade Scrapbook Melon a tu pantalla de inicio para abrirlo como una app.</span>
    </div>
    <div class="install-actions">
        <button id="install-dismiss" type="button">Más tarde</button>
        <button id="install-btn" class="primary" type="button">Instalar</button>
    </div>
</div>

<!-- Instrucciones manuales para iOS (Safari no expone beforeinstallprompt) -->
<div id="install-ios" hidden>
    <div class="ios-arrow">↓</div>
    <p>Pulsa el botón <strong>Compartir</strong> y luego <strong>Añadir a pantalla de inicio</strong>.</p>
    <button id="install-ios-close" type="button">Entendido</button>
</div>

<script>
/* ─── Reloj y fecha en la barra de estado ─────────────────────────── */
(function() {
    var clockEl = document.getElementById('status-clock');
    var dateEl  = document.getElementById('mob-date');
    var DAYS    = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    var MONTHS  = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
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

/* ─── Service worker (necesario para que Chrome ofrezca instalar) ── */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js', { scope: '<?= $projectBaseUrl ?>' })
        .catch(function(err){ console.warn('[mobile] sw register fail', err); });
}

/* ─── Banner de instalación PWA ─────────────────────────────────── */
(function() {
    var standalone = window.matchMedia('(display-mode: standalone)').matches
                  || window.navigator.standalone === true;
    if (standalone) return;   /* ya estamos dentro de la PWA */

    var isIOS  = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    var banner = document.getElementById('install-banner');
    var iosTip = document.getElementById('install-ios');
    var btn    = document.getElementById('install-btn');
    var btnX   = document.getElementById('install-dismiss');
    var iosX   = document.getElementById('install-ios-close');

    /* Si el usuario descartó el banner hace menos de 7 días, no insistir. */
    var DISMISS_KEY = 'melon-install-dismissed';
    var DAY = 24 * 60 * 60 * 1000;
    var dismissedAt = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
    if (dismissedAt && (Date.now() - dismissedAt) < 7 * DAY) return;

    function rememberDismiss() {
        localStorage.setItem(DISMISS_KEY, String(Date.now()));
    }

    if (isIOS) {
        /* Safari móvil no soporta install programático: mostramos el
           tip con la flecha que apunta al botón Compartir. */
        iosTip.hidden = false;
        iosX.addEventListener('click', function(){ iosTip.hidden = true; rememberDismiss(); });
        btnX.addEventListener('click', function(){ banner.hidden = true; rememberDismiss(); });
        return;
    }

    /* Android / Chromium: guardamos el evento beforeinstallprompt y lo
       disparamos al pulsar el botón. */
    var deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        banner.hidden = false;
    });
    btn.addEventListener('click', function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function(){
            deferredPrompt = null;
            banner.hidden = true;
        });
    });
    btnX.addEventListener('click', function(){ banner.hidden = true; rememberDismiss(); });
    window.addEventListener('appinstalled', function() {
        banner.hidden = true;
        rememberDismiss();
    });
})();
</script>

</body>
</html>
