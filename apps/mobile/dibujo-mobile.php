<?php
/* ──────────────────────────────────────────────────────────────────────
   DIBUJO — versión móvil (wrapper Win98 alrededor de Excalidraw)
   ──────────────────────────────────────────────────────────────────────
   Carga Excalidraw embebido en un iframe dentro del marco estándar
   `.mh-window` (title-bar + window-body + statusbar). El shell de
   mobile.php sigue siendo el contenedor; este archivo SOLO añade la
   estética Win98 alrededor del lienzo de Excalidraw.

   Excalidraw guarda el dibujo en localStorage del PROPIO iframe (origin
   excalidraw.com), no en nuestro backend — esto NO es persistencia
   nuestra. El parámetro #room=… sincroniza la sala compartida entre
   usuarios (igual que la versión escritorio).
   ────────────────────────────────────────────────────────────────────── */
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once dirname(__DIR__, 2) . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../../index.php');
    exit;
}
$userLabel = $loginUsers[$userKey]['label'];

/* Tema activo del usuario — mismo flow que el resto de apps móviles. */
require_once dirname(__DIR__, 2) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes      = loadUserThemes($userKey);
$activeTheme      = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = themeCssRelPath($activeTheme, $userLabel);
    if ($activeThemeCss !== '' && !file_exists(dirname(__DIR__, 2) . '/' . $activeThemeCss)) {
        $activeThemeCss = '';
    }
}
$themeBgColor = '#000000';
if ($activeTheme !== '' && isset($_userThemes['themes'][$activeTheme]['colors']['desktopBg'])) {
    $candidate = (string)$_userThemes['themes'][$activeTheme]['colors']['desktopBg'];
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $candidate)) {
        $themeBgColor = $candidate;
    }
}

/* URL de Excalidraw — la misma sala que la versión escritorio. */
$excalidrawUrl = 'https://excalidraw.com/#room=scrapbook-melon,clave-secreta-fija';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="../../assets/js/pwa-guard.js"></script>
    <script>
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
    <meta name="theme-color" content="<?= htmlspecialchars($themeBgColor) ?>">
    <title>Dibujo</title>
    <link rel="icon" href="../../assets/img/mobile/icon.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/98.css">
    <link rel="stylesheet" href="../../assets/css/tokens.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
    <link rel="stylesheet" href="../../assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="../../<?= htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../../assets/css/mobile-theme.css?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/mobile-theme.css') ?>">
    <style>
        /* El window-body se convierte en el contenedor del iframe.
           Quito gap y padding para que el lienzo de Excalidraw ocupe
           cada píxel disponible — solo dejo padding-bottom para la
           safe-area inferior (gesture bar del SO). */
        .mh-window > .window-body {
            gap: 0 !important;
            padding: 0 !important;
            padding-bottom: max(0px, env(safe-area-inset-bottom)) !important;
        }
        /* El iframe vive dentro de un "sunken panel" Win98 para que
           se vea hundido respecto al marco de la ventana. */
        #dibujo-frame-wrap {
            flex: 1; min-height: 0;
            display: flex;
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
            margin: 2px;
        }
        #dibujo-frame {
            flex: 1;
            width: 100%;
            height: 100%;
            border: 0;
            background: #fff;
            display: block;
        }
        /* Overlay de carga mientras Excalidraw arranca. */
        #dibujo-loading {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            font-size: 11px;
            gap: 8px;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        #dibujo-loading.hidden { opacity: 0; }
        #dibujo-loading-spinner {
            font-size: 24px;
            animation: dibujo-spin 1.2s linear infinite;
        }
        @keyframes dibujo-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="mh-body <?php echo htmlspecialchars($activeThemeClass); ?>">

<!-- Ventana Win98 envolvente: title-bar arriba, iframe en el medio,
     statusbar al pie. -->
<div class="window mh-window" id="dibujoWindow">
    <div class="title-bar">
        <div class="title-bar-text"><img src="../../assets/img/appIcons/drawingIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Dibujo</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" id="dibujo-back"></button>
        </div>
    </div>
    <div class="window-body">
        <div id="dibujo-frame-wrap" style="position:relative;">
            <iframe
                id="dibujo-frame"
                src="<?= htmlspecialchars($excalidrawUrl) ?>"
                allow="clipboard-read; clipboard-write; fullscreen"
                title="Excalidraw"
                referrerpolicy="no-referrer"
            ></iframe>
            <div id="dibujo-loading">
                <div id="dibujo-loading-spinner">⏳</div>
                <div>Cargando lienzo…</div>
            </div>
        </div>

        <!-- Status bar Win98 al pie -->
        <div class="mh-statusbar">
            <a href="#" id="dibujo-menu-link">‹ Menú</a>
        </div>

    </div><!-- /.window-body -->
</div><!-- /.window.mh-window -->

<script>
(function(){
    /* Vuelta al shell — postMessage si estamos embebidos, history.back
       como fallback, location.replace al menú como último recurso. */
    function goMenu(e){
        if (e) e.preventDefault();
        if (window.parent && window.parent !== window) {
            try { window.parent.postMessage({ type: 'shell:back' }, '*'); return; } catch (_) {}
        }
        try { history.back(); } catch (_) { location.href = '../../mobile.php'; }
    }
    var btn = document.getElementById('dibujo-back');
    if (btn) btn.addEventListener('click', goMenu);
    var lnk = document.getElementById('dibujo-menu-link');
    if (lnk) lnk.addEventListener('click', goMenu);

    /* Excalidraw es de otro origen → no podemos detectar fiablemente
       "load completado" desde aquí. Como heurística cómoda: ocultamos
       el overlay tras 1.5s (suficiente en redes razonables) o cuando
       el iframe dispara 'load'. */
    var loading = document.getElementById('dibujo-loading');
    var frame   = document.getElementById('dibujo-frame');
    function hideLoading(){
        if (!loading) return;
        loading.classList.add('hidden');
        setTimeout(function(){ if (loading && loading.parentNode) loading.parentNode.removeChild(loading); }, 400);
    }
    if (frame) frame.addEventListener('load', hideLoading);
    setTimeout(hideLoading, 1500);
})();
</script>

</body>
</html>
