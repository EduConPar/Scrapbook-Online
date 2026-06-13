<?php
/* ──────────────────────────────────────────────────────────────────────
   COMPANION — versión móvil (wrapper Win98 de Helldivers Companion)
   ──────────────────────────────────────────────────────────────────────
   Embebe helldiverscompanion.com dentro del marco estándar `.mh-window`
   (title-bar + window-body + statusbar) para que se integre con el resto
   del launcher móvil. El estado del site vive en su propio origen (no
   tenemos visibilidad del DOM por la same-origin policy).
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

$companionUrl = 'https://helldiverscompanion.com';
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
    <title>Companion</title>
    <link rel="icon" href="../../assets/img/mobile/icon.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/98.css">
    <link rel="stylesheet" href="../../assets/css/tokens.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
    <script src="../../assets/js/icon-pack.js"></script>
    <?php require_once dirname(__DIR__, 2) . "/assets/php/active-interface.php"; emitInterfaceCss("../../"); ?>
    <script src="../../assets/js/interface-loader.js?v=fs1"></script>
    <link rel="stylesheet" href="../../assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="../../<?= htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../../assets/css/mobile-theme.css?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/mobile-theme.css') ?>">
    <style>
        /* El window-body es contenedor puro del iframe. */
        .mh-window > .window-body {
            gap: 0 !important;
            padding: 0 !important;
            padding-bottom: max(0px, env(safe-area-inset-bottom)) !important;
        }
        /* Sunken panel Win98 alrededor del iframe. */
        #companion-frame-wrap {
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
        #companion-frame {
            flex: 1;
            width: 100%;
            height: 100%;
            border: 0;
            background: #000;
            display: block;
        }
        /* Overlay de carga mientras el sitio externo arranca. */
        #companion-loading {
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
        #companion-loading.hidden { opacity: 0; }
        #companion-loading-spinner {
            font-size: 24px;
            animation: companion-spin 1.2s linear infinite;
        }
        @keyframes companion-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="mh-body <?php echo htmlspecialchars($activeThemeClass); ?>">

<div class="window mh-window" id="companionWindow">
    <div class="title-bar">
        <div class="title-bar-text"><img src="../../assets/img/appIcons/companionIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Companion</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" id="companion-back"></button>
        </div>
    </div>
    <div class="window-body">
        <div id="companion-frame-wrap" style="position:relative;">
            <iframe
                id="companion-frame"
                src="<?= htmlspecialchars($companionUrl) ?>"
                allow="fullscreen; clipboard-read; clipboard-write"
                title="Helldivers Companion"
                referrerpolicy="no-referrer"
            ></iframe>
            <div id="companion-loading">
                <div id="companion-loading-spinner">⏳</div>
                <div>Conectando con Helldivers Companion…</div>
            </div>
        </div>

        <!-- Status bar Win98 al pie -->
        <div class="mh-statusbar">
            <a href="#" id="companion-menu-link">‹ Menú</a>
        </div>

    </div><!-- /.window-body -->
</div><!-- /.window.mh-window -->

<script>
(function(){
    /* Vuelta al shell: postMessage si está embebido en un iframe,
       history.back como fallback, location.replace al menú como último
       recurso. */
    function goMenu(e){
        if (e) e.preventDefault();
        if (window.parent && window.parent !== window) {
            try { window.parent.postMessage({ type: 'shell:back' }, '*'); return; } catch (_) {}
        }
        try { history.back(); } catch (_) { location.href = '../../mobile.php'; }
    }
    var btn = document.getElementById('companion-back');
    if (btn) btn.addEventListener('click', goMenu);
    var lnk = document.getElementById('companion-menu-link');
    if (lnk) lnk.addEventListener('click', goMenu);

    /* Site externo (otro origen) → no podemos esperar nada del DOM
       interno. Ocultamos el overlay cuando el iframe dispara 'load' o
       tras 1.5s como heurística. */
    var loading = document.getElementById('companion-loading');
    var frame   = document.getElementById('companion-frame');
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
