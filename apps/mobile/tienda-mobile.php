<?php
/* ──────────────────────────────────────────────────────────────────────
   TIENDA — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Versión móvil de apps/tienda.php. Reusa los MISMOS IDs del HTML del
   desktop y el MISMO JS — los endpoints (assets/tienda/api.php) son
   los mismos. Solo cambia el layout (un único flujo vertical con tabs
   horizontales scrollable y la status-bar al pie) y los paths
   relativos (../../ en vez de ../).

   ⚠ Si tocas el JS del desktop, ten en cuenta que el bloque de JS de
      este archivo es una copia adaptada de paths. Mantenlo en sync.
   ────────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__, 2) . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';

if (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    header('Location: ../../index.php');
    exit;
}
$userKey   = $_SESSION['user'];
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="../../assets/js/pwa-guard.js"></script>
    <script>
    /* --mh-vh: viewport height en px sincronizado con window.innerHeight.
       Sin esto, .mh-body cae a 100dvh (stale tras bfcache) o 100vh
       (incluye la barra de URL → la ventana se desborda por debajo del
       área visible). Hay que setearlo ANTES de que el CSS lo lea. */
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
    <title>Tienda</title>
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
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
        /* ──────────────────────────────────────────────────────────────
           Look Win98 + tema del usuario:
           - El reset / fuente / fondo lo aporta mobile-theme.css
             (.mh-body + .mh-window + .title-bar + .window-body).
           - El tema activo se inyecta vía <link id="active-theme-link">
             y sobreescribe las CSS vars (--win-bg, --accent, etc).
           - Aquí solo definimos tweaks específicos de esta app. */

        /* Mini-header con wallet (balance) + Discord status. Replica el
           sidebar del desktop pero condensado a una franja horizontal. */
        .ti-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            padding: 8px;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            flex-shrink: 0;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }
        .ti-wallet {
            display: flex; flex-direction: column;
            min-width: 0;
        }
        .ti-wallet-label {
            font-size: 9px; letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-muted, var(--text-faint, #666));
        }
        .ti-wallet-amount {
            display: flex; align-items: center; gap: 6px;
            font-size: 20px; font-weight: bold;
            color: var(--text, #000);
            line-height: 1;
            padding: 2px 0;
        }
        .ti-wallet-amount .ic { font-size: 18px; }
        .ti-wallet-unit {
            font-size: 9px;
            color: var(--text-muted, var(--text-faint, #666));
            white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .ti-discord {
            display: flex; flex-direction: column; align-items: flex-end;
            gap: 3px;
            min-width: 0;
        }
        .ti-discord-label {
            font-size: 9px; letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-muted, var(--text-faint, #666));
        }
        #tienda-discord-name {
            font-size: 11px;
            font-weight: bold;
            color: var(--accent, #000080);
            max-width: 110px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        /* Botones del header (Donar | Vincular/Desvincular Discord)
           agrupados en una row a la derecha. Donar a la IZQUIERDA del
           botón Discord — alineado con cómo el desktop separa los dos. */
        .ti-actions {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        .ti-actions .button,
        #tienda-discord-btn,
        #tienda-donar-btn {
            min-height: 26px;
            font-size: 10px;
            padding: 4px 10px;
            line-height: 1;
            cursor: pointer;
            /* flex centering para que el texto quede vertical-aligned
               aunque el contenido sea solo 1 línea de 10px. */
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        #tienda-donar-btn.is-active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }

        /* Tabs Win98 horizontales con scroll. Usamos las clases del JS
           desktop (.tienda-tab + #tienda-donar-btn con .is-active) para
           que el handler de tabs del JS principal funcione sin cambios. */
        .ti-tabs {
            display: flex; gap: 4px;
            padding: 6px 4px 4px;
            background: var(--win-bg, silver);
            overflow-x: auto;
            flex-shrink: 0;
            -webkit-overflow-scrolling: touch;
        }
        .ti-tabs .tienda-tab {
            min-height: 30px;
            padding: 4px 12px;
            font-size: 11px;
            line-height: 1;
            white-space: nowrap;
            flex-shrink: 0;
            cursor: pointer;
            /* flex centering: con min-height + line-height:1 el texto
               queda anclado al baseline (sube). Centramos vertical y
               horizontalmente con inline-flex. */
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .ti-tabs .tienda-tab.active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }

        /* Paneles ocupan flex:1 y scrollean. Donaciones empieza oculta;
           el JS pone display:none al principal y añade .is-active a
           donaciones para mostrarla (mismo patrón que desktop). */
        .ti-pane {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 8px;
            padding-bottom: max(16px, env(safe-area-inset-bottom));
        }
        /* Donaciones empieza oculta; el JS añade .is-active al pulsar
           el botón Donar (ver el handler [data-view] del JS principal). */
        #tienda-view-donaciones { display: none; }
        #tienda-view-donaciones.is-active {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .tienda-empty {
            text-align: center;
            color: var(--text-muted, var(--text-faint, #808080));
            font-size: 11px;
            padding: 28px 12px;
        }

        /* Grid de items — 2 columnas en móvil (auto-fit min 140px).
           - align-content: start → las rows se apilan al inicio sin
             estirarse para llenar el contenedor si sobran items.
           - align-items: start → cada card mantiene su altura natural
             en lugar de estirarse a la altura de la row más alta.
           Sin esto, un haro solo se inflaba hasta el final del .ti-pane
           y dos cards en la misma row se igualaban verticalmente. */
        #tienda-view-principal {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 8px;
            align-content: start;
            align-items: start;
        }
        .tienda-card {
            background: var(--win-bg, silver);
            color: var(--text, #000);
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }
        .tienda-card-icon {
            position: relative;
            width: 100%; aspect-ratio: 1 / 1;
            background: var(--inset-bg, #808080);
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
            display: flex; align-items: center; justify-content: center;
            font-size: 38px;
            overflow: hidden;
        }
        .tienda-card-icon-img {
            max-width: 80%; max-height: 80%;
            object-fit: contain;
            image-rendering: -webkit-optimize-contrast;
        }
        .tienda-card-price {
            position: absolute;
            top: 3px; right: 3px;
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            font-size: 10px;
            padding: 1px 5px;
            letter-spacing: 0.5px;
        }
        .tienda-card.is-owned { opacity: 0.7; }
        .tienda-card.is-owned .tienda-card-icon::after {
            content: '✓';
            position: absolute;
            left: 4px; top: 4px;
            font-size: 14px; font-weight: bold;
            color: var(--accent, #000080);
            background: var(--win-bg, silver);
            padding: 1px 5px;
        }
        .tienda-card-name {
            font-size: 11px; font-weight: bold;
            color: var(--text, #000);
            text-align: center;
            line-height: 1.2;
            word-break: break-word;
        }
        .tienda-card-desc {
            font-size: 10px;
            color: var(--text-muted, var(--text-faint, #666));
            text-align: center;
            line-height: 1.3;
            min-height: 26px;
        }
        .tienda-card .button {
            width: 100%;
            min-height: 26px;
            font-size: 11px;
        }
        .tienda-card .button:disabled { opacity: 0.55; cursor: not-allowed; }

        /* Vista Donaciones — info + encargos + lista de donantes. El
           display/flex se aplica solo cuando .is-active está presente
           (regla de arriba). */
        .donar-intro,
        .donar-encargos,
        .donar-donors-section {
            background: var(--win-bg, silver);
            color: var(--text, #000);
            padding: 10px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }
        .donar-intro-main { text-align: center; }
        .donar-intro-emoji { font-size: 36px; line-height: 1; }
        .donar-intro-title {
            font-size: 14px; font-weight: bold;
            margin-top: 6px;
        }
        .donar-intro-text {
            font-size: 11px; color: var(--text, #000);
            line-height: 1.5;
            margin: 6px 0 8px;
        }
        #donar-go-btn { min-height: 32px; font-size: 12px; padding: 0 18px; }
        .donar-intro-hint {
            font-size: 10px;
            color: var(--text-muted, var(--text-faint, #888));
            margin-top: 6px;
        }
        .donar-encargos-title {
            font-size: 11px; font-weight: bold;
            letter-spacing: 1px; text-transform: uppercase;
            background: var(--titlebar-start, #000080);
            color: var(--titlebar-text, #fff);
            padding: 4px 8px;
            margin: -10px -10px 8px;
        }
        .donar-encargo-btn {
            display: flex; align-items: center; gap: 8px;
            width: 100%; min-height: 36px;
            margin-bottom: 6px;
            padding: 0 10px;
            text-decoration: none;
            color: var(--text, #000);
            font-size: 12px;
        }
        .donar-encargo-btn .ic { font-size: 18px; flex-shrink: 0; }
        .donar-encargo-btn .label { flex: 1; text-align: left; }
        .donar-encargo-btn .price {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            font-size: 10px;
            padding: 2px 6px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .donar-encargos-info {
            font-size: 10px;
            color: var(--text-muted, var(--text-faint, #888));
            line-height: 1.4;
            margin: 6px 0 0;
        }

        .donar-donors-title {
            font-size: 11px; font-weight: bold;
            letter-spacing: 1px; text-transform: uppercase;
            background: var(--titlebar-start, #000080);
            color: var(--titlebar-text, #fff);
            padding: 4px 8px;
            margin: -10px -10px 8px;
        }
        /* Grid de donantes — flex wrap centrado, mismas mini-cards
           Win98 que el desktop pero compactas para móvil. */
        .donar-donors-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
        }
        .donar-donor {
            width: 130px;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            box-sizing: border-box;
        }
        .donar-donor-avatar {
            width: 56px; height: 56px;
            flex-shrink: 0;
            background: var(--inset-bg, #808080);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            overflow: hidden;
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
            margin: 2px;
        }
        .donar-donor-avatar img {
            width: 56px; height: 56px;
            object-fit: cover;
            display: block;
            image-rendering: auto;
        }
        .donar-donor-name {
            font-size: 11px; font-weight: bold;
            text-align: center;
            color: var(--text, #000);
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        /* Etiqueta del tipo de aportación. Mismos colores que desktop. */
        .donar-donor-tipo {
            font-size: 9px; font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 2px 6px;
            color: #fff;
            display: inline-flex; align-items: center; gap: 3px;
            line-height: 1;
        }
        .donar-donor-tipo.donacion    { background: var(--accent, #000080); color: var(--accent-text, #fff); }
        .donar-donor-tipo.suscripcion { background: #7c4dff; }
        .donar-donor-tipo.encargo     { background: #e67e22; }
        .donar-donor-msg {
            font-size: 10px;
            color: var(--text, #000);
            text-align: center;
            line-height: 1.35;
            padding: 4px 6px;
            width: 100%;
            box-sizing: border-box;
            background: var(--inset-bg, #c0c0c0);
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -1px -1px var(--bezel-light-1, #fff);
            font-style: italic;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .donar-donor-msg:empty { display: none; }

        /* Status footer chip. */
        #tienda-status {
            font-size: 10px;
            padding: 4px 8px;
            color: var(--text-muted, var(--text-faint, #666));
            text-align: center;
            background: var(--input-bg, #fff);
            min-height: 18px;
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
        }
        #tienda-status:empty { display: none; }
        #tienda-status.is-error { color: var(--error-text, #c00); font-weight: bold; }
        #tienda-status.is-ok    { color: var(--accent, #000080); font-weight: bold; }
    </style>
</head>
<body class="mh-body <?= htmlspecialchars($activeThemeClass) ?>">
<div class="window mh-window">
    <div class="title-bar">
        <div class="title-bar-text"><img src="../../assets/img/appIcons/tiendaIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Tienda</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="ti-back"></button>
        </div>
    </div>
    <div class="window-body">

<!-- Cabecera con wallet (balance) + estado Discord. Mismos IDs que
     desktop para que el JS los rellene sin tocar nada. -->
<div class="ti-header">
    <div class="ti-wallet">
        <div class="ti-wallet-label">Tu balance</div>
        <div class="ti-wallet-amount">
            <span class="ic"><img src="../../assets/img/appIcons/puntosAutismo.png" alt="" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
            <span id="tienda-balance-v">—</span>
        </div>
        <div class="ti-wallet-unit">puntos de Autismo</div>
    </div>
    <div class="ti-discord">
        <div class="ti-discord-label">Discord</div>
        <div id="tienda-discord-name"></div>
        <!-- Donaciones vive en el header (no en las tabs), a la
             izquierda del botón Vincular/Desvincular Discord. Así queda
             separada de las categorías de items. -->
        <div class="ti-actions">
            <button type="button" class="button" id="tienda-donar-btn" data-view="donaciones">☕ Donar</button>
            <button type="button" class="button" id="tienda-discord-btn">…</button>
        </div>
    </div>
</div>

<!-- Tabs horizontales: 4 categorías de items. Donaciones ya no vive
     aquí — se accede desde el botón del header. -->
<div class="ti-tabs">
    <div class="button tienda-tab active" data-view="principal" data-cat="discord">💬 Discord</div>
    <div class="button tienda-tab"        data-view="principal" data-cat="interfaces"><img src="../../assets/img/appIcons/interfaceIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Interfaces</div>
    <div class="button tienda-tab"        data-view="principal" data-cat="mascotas"><img src="../../assets/img/appIcons/mascotaIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Mascotas</div>
    <div class="button tienda-tab"        data-view="principal" data-cat="haros"><img src="../../assets/img/appIcons/haroIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Haros</div>
</div>

<!-- Vista Principal — grid de items de la categoría seleccionada. -->
<div class="ti-pane" id="tienda-view-principal">
    <div class="tienda-empty">Cargando…</div>
</div>

<!-- Vista Donaciones — explicación, encargos Ko-fi y donantes. -->
<div class="ti-pane" id="tienda-view-donaciones">
    <div class="donar-intro">
        <div class="donar-intro-main">
            <div class="donar-intro-emoji">🍉</div>
            <div class="donar-intro-title">Apoya el desarrollo</div>
            <div class="donar-intro-text">
                Scrapbook Melon es un proyecto personal y gratuito. Cualquier
                aportación ayuda a mantener el servidor encendido, pagar el
                dominio y seguir añadiendo funciones nuevas. Gracias 💛
            </div>
            <button type="button" class="button default" id="donar-go-btn">☕ Donar</button>
            <div class="donar-intro-hint">Pago seguro vía Stripe / PayPal en Ko-fi.</div>
        </div>
    </div>

    <div class="donar-encargos">
        <div class="donar-encargos-title">🎨 Encargos</div>
        <a class="button donar-encargo-btn" href="https://ko-fi.com/c/064181251c" target="_blank" rel="noopener"
           data-kofi-title="Haro personalizado">
            <span class="ic">⚪</span><span class="label">Haro personalizado</span><span class="price">2 €</span>
        </a>
        <a class="button donar-encargo-btn" href="https://ko-fi.com/c/4de28dd45e" target="_blank" rel="noopener"
           data-kofi-title="Tema personalizado">
            <span class="ic">🎨</span><span class="label">Tema personalizado</span><span class="price">5 €</span>
        </a>
        <a class="button donar-encargo-btn" href="https://ko-fi.com/c/16c92f9fdf" target="_blank" rel="noopener"
           data-kofi-title="Mascota personalizada">
            <span class="ic"><img src="../../assets/img/appIcons/mascotaIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span><span class="label">Mascota personalizada</span><span class="price">10 €</span>
        </a>
        <p class="donar-encargos-info">
            Si no te apetece donar también puedes hacer un encargo para tener
            algo personalizado en tu perfil y no limitarte a las opciones de
            la tienda.
        </p>
    </div>

    <div class="donar-donors-section">
        <div class="donar-donors-title">Quienes han apoyado</div>
        <div class="donar-donors-grid" id="donar-donors-grid">
            <div style="text-align:center;color:var(--text-muted);font-size:11px;padding:18px;grid-column:1/-1;">Cargando…</div>
        </div>
    </div>
</div>

<!-- Status footer (mensajes de "Cargando…", "Error: …", etc.). -->
<div id="tienda-status"></div>

        <!-- Status bar Win98 al pie con "Volver al menú". -->
        <div class="mh-statusbar">
            <a href="#" id="ti-menu-link">‹ Menú</a>
        </div>

    </div><!-- /.window-body -->
</div><!-- /.window.mh-window -->

<script>
/* Botones "‹ Menú": X de la title-bar Win98 + enlace de la status-bar
   abajo. Ambos vuelven al shell de mobile.php vía postMessage (en
   iframe) o history.back si la app está standalone. */
(function(){
    function goMenu(e){
        if (e) e.preventDefault();
        if (window.parent && window.parent !== window) {
            try { window.parent.postMessage({ type: 'shell:back' }, '*'); return; } catch (_) {}
        }
        try { history.back(); } catch (_) { location.href = '../../mobile.php'; }
    }
    var topBtn = document.getElementById('ti-back');
    if (topBtn) topBtn.addEventListener('click', goMenu);
    var bottomLink = document.getElementById('ti-menu-link');
    if (bottomLink) bottomLink.addEventListener('click', goMenu);
})();

/* El handler de tabs vive en el JS principal del desktop (que se
   anexa abajo). Reconoce las clases .tienda-tab + #tienda-donar-btn
   y los atributos data-view / data-cat — mismas que usamos aquí. */

/* Override del botón "Donar": el JS desktop envía postMessage
   {type:'open-kofi'} al parent esperando que desktop-base.php abra
   una ventana iframe con Ko-fi. En el shell de mobile.php no existe
   ese listener, así que el botón se queda sin efecto. Registramos
   nuestro handler ANTES del JS principal y abrimos Ko-fi en una
   pestaña nueva. SIN fallback a location.href: si window.open
   fallara, redirigir la propia pestaña de la PWA rompe el history
   (al volver con el back-button del SO la app queda en mal estado). */
(function(){
    var KOFI_USER = 'melonhub';
    var btn = document.getElementById('donar-go-btn');
    if (!btn) return;
    btn.addEventListener('click', function(){
        window.open('https://ko-fi.com/' + KOFI_USER, '_blank', 'noopener');
    });
})();
</script>

<!-- ====================================================================
     JS PRINCIPAL — copiado de apps/tienda.php con paths ajustados.
     Mantener en sync con el original si se modifica la lógica.
     =================================================================== -->
<script>
(function(){
'use strict';
var API = '../../assets/tienda/api.php';
var KOFI_USER = 'melonhub';

function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
function setStatus(msg, kind){
    var el = document.getElementById('tienda-status');
    el.textContent = msg || '';
    el.className = 'tienda-status' + (kind === 'error' ? ' is-error' : kind === 'ok' ? ' is-ok' : '');
}
async function api(action, body){
    var opts = { headers:{'Content-Type':'application/json'} };
    if (body) { opts.method='POST'; opts.body=JSON.stringify(body); }
    var r = await fetch(API+'?action='+action, opts);
    return r.json();
}

var _balance = 0, _items = [], _owned = {}, _activeCat = 'discord';

async function loadState(){
    setStatus('Cargando…');
    try {
        var r = await api('state');
        if (r.error) throw new Error(r.error);
        _balance = r.autismo|0;
        _items   = r.items || [];
        _owned   = {};
        (r.owned || []).forEach(function(id){ _owned[id|0] = true; });
        renderBalance();
        renderItems();
        setStatus('');
    } catch (e) {
        setStatus('Error: ' + e.message, 'error');
    }
}

function renderBalance(){
    document.getElementById('tienda-balance-v').textContent = _balance;
    /* Botones se rehabilitan/deshabilitan según balance Y posesión. */
    document.querySelectorAll('[data-buy-id]').forEach(function(btn){
        var id = btn.dataset.buyId | 0;
        if (_owned[id]) { btn.disabled = true; btn.textContent = '✓ Ya lo tienes'; return; }
        var p = parseInt(btn.dataset.price, 10);
        btn.disabled = p > _balance;
    });
}
function renderItems(){
    var view = document.getElementById('tienda-view-principal');
    var items = _items.filter(function(it){ return (it.categoria || 'discord') === _activeCat; });
    if (!items.length) {
        view.innerHTML = '<div class="tienda-empty">No hay items en esta categoría todavía.</div>';
        return;
    }
    view.innerHTML = items.map(function(it){
        /* El nombre visible es el del rol de Discord (si lo lleva); si no,
           cae al nombre interno del item. Coloreamos el texto del nombre
           con el color del rol cuando exista, así se mantiene la identidad
           visual sin necesidad de un badge aparte. */
        var displayName = it.discord_role_name || it.nombre;
        var nameStyle = '';
        if (it.discord_role_name) {
            var c = it.discord_role_color | 0;
            if (c > 0) {
                var hex = '#' + ('000000' + c.toString(16)).slice(-6);
                nameStyle = ' style="color:' + hex + ';"';
            }
        }
        /* Descripción dinámica: si hay rol, genera la frase con el nombre
           real; si no, cae al campo `descripcion` de la BD como fallback. */
        var desc = it.discord_role_name
            ? 'Adquiere el rol ' + it.discord_role_name + ' en :melonduagua: 3.0'
            : (it.descripcion || '');
        var owned = !!_owned[it.id|0];
        var btnLabel = owned ? '✓ Ya lo tienes' : 'Comprar';
        /* Para los haros el icono usa la convención
           assets/img/haro/{slug}Haro-preview.png (PNG curado).
           Si no existe, cae al último frame del gif vía onerror. */
        var iconHtml;
        if (it.categoria === 'haros' && it.slug) {
            iconHtml = '<img class="tienda-card-icon-img"'
                + ' src="../../assets/img/haro/' + esc(it.slug) + 'Haro-preview.png"'
                + ' onerror="this.onerror=null;this.src=\'../../assets/vids/' + esc(it.slug) + 'Haro-last.png\';"'
                + ' alt="">';
        } else {
            iconHtml = esc(it.icono || '🎁');
        }
        return '<div class="tienda-card' + (owned ? ' is-owned' : '') + '">' +
            '<div class="tienda-card-icon">' +
                iconHtml +
                '<span class="tienda-card-price">' + it.precio + ' <img src="../../assets/img/appIcons/puntosAutismo.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>' +
            '</div>' +
            '<div class="tienda-card-name"' + nameStyle + '>' + esc(displayName) + '</div>' +
            '<div class="tienda-card-desc">' + esc(desc) + '</div>' +
            '<button type="button" class="button" data-buy-id="' + it.id + '" data-price="' + it.precio + '">' + btnLabel + '</button>' +
        '</div>';
    }).join('');
    view.querySelectorAll('[data-buy-id]').forEach(function(btn){
        btn.addEventListener('click', function(){ buy(parseInt(btn.dataset.buyId, 10), btn); });
    });
    renderBalance();
}

async function buy(itemId, btn){
    btn.disabled = true;
    setStatus('Comprando…');
    try {
        var r = await api('buy', { item_id: itemId });
        if (r.error) throw new Error(r.error);
        _balance = r.autismo|0;
        _owned[itemId|0] = true;     /* marcar como propio inmediatamente */
        renderItems();               /* repinta para que la card pase a is-owned */
        var msg = 'Compra realizada: ' + r.item.nombre + ' (-' + r.item.precio + ' puntos)';
        if (r.discord && r.discord.attempted) {
            msg += r.discord.ok
                ? ' · 🎉 rol de Discord asignado'
                : ' · ⚠ rol no asignado (' + (r.discord.error || 'error') + ')';
        }
        setStatus(msg, 'ok');
    } catch (e) {
        setStatus('Error: ' + e.message, 'error');
        btn.disabled = false;
    }
}

/* ── Cambio de vista ──
   Cubre tanto los .tienda-tab (Principal) como el botón #tienda-donar-btn
   (Donaciones). La tab usa la clase .active (background acento), el botón
   usa .is-active (bezel pulsado) — visualmente coherente con su rol. */
document.querySelectorAll('[data-view]').forEach(function(el){
    el.addEventListener('click', function(){
        var view = el.dataset.view;
        document.querySelectorAll('.tienda-tab').forEach(function(t){ t.classList.remove('active'); });
        var donar = document.getElementById('tienda-donar-btn');
        donar.classList.remove('is-active');
        if (el.classList.contains('tienda-tab')) el.classList.add('active');
        else el === donar && donar.classList.add('is-active');
        var isDon = view === 'donaciones';
        document.getElementById('tienda-view-principal').style.display = isDon ? 'none' : '';
        document.getElementById('tienda-view-donaciones').classList.toggle('is-active', isDon);
        /* Si la tab pulsada lleva categoría, repintamos el grid filtrando. */
        if (!isDon && el.dataset.cat) {
            _activeCat = el.dataset.cat;
            renderItems();
        }
    });
});

/* ═══ DONACIONES ═══ */
(function(){
    var grid = document.getElementById('donar-donors-grid');
    var pollHandle = null;
    var lastJSON = null;

    function renderDonors(donors){
        if (!donors.length) {
            grid.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:11px;padding:20px;width:100%;">Aún no hay donantes. ¡Podrías ser el primero!</div>';
            return;
        }
        var TIPOS = {
            donacion:    { ic: '💛', label: 'Donante'     },
            suscripcion: { ic: '🔁', label: 'Suscriptor'  },
            encargo:     { ic: '🎨', label: 'Encargo'     },
        };
        grid.innerHTML = donors.map(function(d){
            var av = d.avatar_url
                ? '<img src="' + esc(d.avatar_url) + '" alt="" referrerpolicy="no-referrer">'
                : '👤';
            var tipo = TIPOS[d.tipo] || TIPOS.donacion;
            var tipoEl = '<div class="donar-donor-tipo ' + esc(d.tipo || 'donacion') + '">' +
                         '<span>' + tipo.ic + '</span><span>' + tipo.label + '</span></div>';
            var msg = d.mensaje ? '<div class="donar-donor-msg">' + esc(d.mensaje) + '</div>' : '';
            return '<div class="donar-donor">' +
                '<div class="donar-donor-avatar">' + av + '</div>' +
                '<div class="donar-donor-name">' + esc(d.nombre) + '</div>' +
                tipoEl +
                msg +
            '</div>';
        }).join('');
    }

    async function loadDonors(){
        try {
            var r = await api('donors');
            if (r.error) throw new Error(r.error);
            /* Solo re-renderizamos si la respuesta cambió — evita parpadeos
               inútiles del DOM en cada poll. */
            var json = JSON.stringify(r.donors || []);
            if (json !== lastJSON) {
                lastJSON = json;
                renderDonors(r.donors || []);
            }
        } catch (e) {
            grid.innerHTML = '<div style="text-align:center;color:var(--error-text);font-size:11px;padding:20px;width:100%;">No se pudo cargar la lista: ' + esc(e.message) + '</div>';
        }
    }

    /* Polling cada 30 s mientras la pestaña Donaciones está visible. El
       webhook de Ko-fi guarda en BD y la siguiente vuelta del poll lo
       muestra sin necesidad de recargar la app. */
    function startPolling(){
        if (pollHandle) return;
        pollHandle = setInterval(loadDonors, 30000);
    }
    function stopPolling(){
        if (pollHandle) { clearInterval(pollHandle); pollHandle = null; }
    }

    /* Pide al desktop que abra la ventana de Ko-fi (iframe aparte).
       El handler vive en desktop-base.php → listener 'message'. */
    document.getElementById('donar-go-btn').addEventListener('click', function(){
        try {
            window.parent.postMessage({ type: 'open-kofi' }, '*');
        } catch (e) {}
        /* Refresco optimista cuando el usuario vuelva a esta pestaña. */
        setTimeout(loadDonors, 500);
    });

    /* Botones de encargos: las páginas /c/xxx de Ko-fi mandan
       X-Frame-Options:SAMEORIGIN que prohíbe embeberlas en iframe (al
       contrario que la página de donaciones, que sí permite ?embed=true).
       Por eso aquí abrimos popup centrada en lugar de la ventana iframe.
       El `target="_blank"` se conserva como fallback (click derecho →
       "Abrir en pestaña nueva" sigue funcionando si el navegador bloquea
       la popup). */
    document.querySelectorAll('.donar-encargo-btn').forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            var w = 480, h = 740;
            var l = (window.screen.width  - w) / 2;
            var t = (window.screen.height - h) / 2;
            var win = window.open(a.href, 'kofi-commission',
                'width=' + w + ',height=' + h + ',left=' + l + ',top=' + t +
                ',menubar=no,toolbar=no,location=no,status=no'
            );
            /* Si el navegador bloqueó la popup, caemos a pestaña nueva. */
            if (!win) window.open(a.href, '_blank', 'noopener');
            setTimeout(loadDonors, 500);
        });
    });

    /* Las pestañas de la sidebar disparan el poll cuando entras en
       Donaciones y lo paran cuando sales (ahorra requests). */
    document.querySelectorAll('[data-view]').forEach(function(el){
        el.addEventListener('click', function(){
            if (el.dataset.view === 'donaciones') { loadDonors(); startPolling(); }
            else { stopPolling(); }
        });
    });

    loadDonors();
})();

/* ═══ DISCORD ═══ */
(function(){
    var nameEl = document.getElementById('tienda-discord-name');
    var btn    = document.getElementById('tienda-discord-btn');
    var linked = false;
    var DISCORD_BASE = '../../assets/discord-oauth';

    function render(){
        if (linked) {
            btn.textContent = 'Desvincular';
        } else {
            nameEl.textContent = '';
            btn.textContent    = '🔗 Conectar Discord';
        }
    }

    async function refresh(){
        try {
            var r = await fetch(DISCORD_BASE + '/status.php', { credentials: 'same-origin' }).then(function(x){ return x.json(); });
            if (r.error) throw new Error(r.error);
            linked = !!r.linked;
            nameEl.textContent = linked && r.username ? '@' + r.username : '';
            render();
        } catch (e) {
            nameEl.textContent = '';
            btn.textContent    = '🔗 Conectar Discord';
        }
    }

    function openOAuth(){
        var url = DISCORD_BASE + '/start.php';
        var w = 520, h = 700;
        var l = (window.screen.width  - w) / 2;
        var t = (window.screen.height - h) / 2;
        /* Como esta página vive en un iframe, abrimos la popup desde el
           opener-top para que window.opener apunte a algo cerrable. */
        window.open(url, 'discord-oauth',
            'width=' + w + ',height=' + h + ',left=' + l + ',top=' + t +
            ',menubar=no,toolbar=no,location=no,status=no'
        );
    }

    async function disconnect(){
        if (!confirm('¿Desvincular tu cuenta de Discord?')) return;
        try {
            var r = await fetch(DISCORD_BASE + '/disconnect.php', {
                method: 'POST', credentials: 'same-origin'
            }).then(function(x){ return x.json(); });
            if (r.error) throw new Error(r.error);
            linked = false;
            refresh();
        } catch (e) {
            alert('Error: ' + e.message);
        }
    }

    btn.addEventListener('click', function(){
        if (linked) disconnect();
        else openOAuth();
    });

    /* La popup de OAuth nos avisa al terminar. */
    window.addEventListener('message', function(e){
        if (e.data && e.data.type === 'discord-linked') refresh();
    });

    refresh();
})();

/* Polling del balance cada 15 s para que el contador de autismo se vea en
   vivo (puntos por mensaje/voz/reacciones del bot, recompensas de admin,
   etc). Endpoint ligero `balance` que solo devuelve `{autismo}`.
   Usa fetch directo con `cache:'no-store'` + cache-buster por timestamp
   para sortear cualquier caché del navegador o de un proxy en medio. */
(function(){
    async function pollBalance(){
        if (document.hidden) return;
        try {
            var r = await fetch(API + '?action=balance&t=' + Date.now(), {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Content-Type': 'application/json' }
            }).then(function(x){ return x.json(); });
            if (r.error || typeof r.autismo !== 'number') return;
            console.log('[tienda] poll balance:', r.autismo, '(local:', _balance, ')');
            if (r.autismo === _balance) return;
            _balance = r.autismo;
            renderBalance();
        } catch (e) { console.warn('[tienda] poll error:', e.message); }
    }
    /* Primera llamada a los 3 s para arrancar rápido, luego cada 15 s. */
    setTimeout(pollBalance, 3000);
    setInterval(pollBalance, 15000);

    /* El escritorio nos avisa cuando el usuario reabre la ventana de la
       tienda — refrescamos balance + items + compras al instante en lugar
       de esperar al próximo ciclo del polling. */
    window.addEventListener('message', function(e){
        if (e.data && e.data.type === 'tienda-refresh') {
            pollBalance();           /* update inmediato del wallet */
            loadState();             /* refresca items + compras */
        }
    });
})();

loadState();
})();
</script>
</body>
</html>
