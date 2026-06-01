<?php
/* ──────────────────────────────────────────────────────────────────────
   PERFIL — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Entrada independiente del escritorio: NO se incluye desde
   desktop-base.php y no depende de $desktopLabel / appTitleIcon().
   Lee toda la información vía /assets/profile/api.php:
     - get-profile  → cabecera (descripción, quote)
     - get-lists    → 5 categorías (movies/series/books/games/music)
   UI: cabecera + pestañas horizontales scroll + lista vertical.
   ────────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once dirname(__DIR__) . '/assets/config.php';

if (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    header('Location: ../index.php');
    exit;
}
$userKey   = $_SESSION['user'];
$userLabel = $loginUsers[$userKey]['label'];

/* Avatar del usuario logueado */
$userImg = '';
$safe = preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel);
foreach (['png','jpg','jpeg','gif'] as $ext) {
    if (file_exists(dirname(__DIR__) . "/assets/img/{$safe}.{$ext}")) {
        $userImg = "../assets/img/{$safe}.{$ext}";
        break;
    }
}

/* ── TEMA ACTIVO DEL USUARIO ──
   Misma estrategia que mobile.php: el look hereda 98.css + tokens.css
   + tema del usuario, y el inline CSS solo refina lo específico de
   esta pantalla con variables del tema. */
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = themeCssRelPath($activeTheme, $userLabel);
    if ($activeThemeCss !== '' && !file_exists(dirname(__DIR__) . '/' . $activeThemeCss)) {
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
    <!-- PWA GUARD: fuera de standalone → fuera de la app. -->
    <script>
    (function(){
        var sa = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
              || window.navigator.standalone === true;
        if (!sa) window.location.replace('../mobile-landing.php');
    })();
    /* --mh-vh sincronizado con window.innerHeight (sobrevive al bfcache). */
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
    <title>Perfil — Scrapbook Melon</title>
    <link rel="icon" href="data:,">
    <!-- Win98 + tema del usuario para consistencia con mobile.php -->
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="../<?= htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/mobile-theme.css?v=<?= filemtime(dirname(__DIR__) . '/assets/css/mobile-theme.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
        /* Tweaks específicos del perfil — el grueso del look viene de
           mobile-theme.css. Aquí solo añadimos lo propio de la pantalla
           (tabs de categorías, listado de items con poster). */

        /* ── Datos del perfil dentro del userbar: bio + meta + conexiones ──
           Replica `profile-info-bio`, `profile-info-meta`, `profile-info-links`
           del escritorio, pero embebidos en la cabecera para que no roben
           espacio adicional. */
        .pf-userbar { align-items: flex-start; padding: 8px; }
        .pf-userbar .mh-userbar-text { min-width: 0; }
        /* Columna izquierda: avatar + cuadrado de conexiones debajo.
           Flex column para alinear ambos a la misma anchura visual. */
        .pf-userbar-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        /* Bio = "descripción". Cursiva, color de acento. El placeholder
           "Sin descripción" usa otra clase para grisarlo. */
        .pf-about-bio {
            font-size: 11px;
            font-style: italic;
            line-height: 1.4;
            color: var(--accent, #000080);
            margin: 4px 0 3px;
            word-break: break-word;
        }
        .pf-about-bio.is-empty {
            color: var(--text-faint, #888);
            opacity: 0.85;
        }
        /* Línea de meta: pronombres · edad años · 📍 país. */
        .pf-about-meta {
            font-size: 10px;
            color: var(--text-faint, #666);
            margin: 0 0 4px;
            line-height: 1.3;
        }
        .pf-about-meta:empty { display: none; }
        .pf-about-meta .pf-meta-sep {
            margin: 0 4px;
            color: var(--text-faint, #aaa);
        }
        /* Cuadrado de conexiones — panel hundido Win98 con rejilla 2×2
           de iconos. Pensado para alinear visualmente con la anchura
           del avatar (~52px) que tiene encima. */
        .pf-about-socials {
            display: grid;
            grid-template-columns: repeat(2, 22px);
            grid-auto-rows: 22px;
            gap: 2px;
            padding: 3px;
            min-height: 46px;       /* fuerza forma cuadrada aunque haya 1-2 socials */
            background: var(--input-bg, #fff);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a);
            box-sizing: content-box;
        }
        .pf-about-socials:empty { display: none; }
        .pf-about-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            font-size: 13px;
            text-decoration: none;
            color: var(--text, #000);
            background: var(--btn-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff);
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
        }
        .pf-about-social:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a);
        }

        /* ── Sub-tabs de música: álbumes vs canciones ──
           Solo visible en la pestaña 'música'. Replica el patrón del
           desktop (#music-tab-bar en perfil.php). */
        .pf-subtabs {
            display: flex;
            gap: 2px;
            padding: 4px 6px;
            background: var(--btn-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-2, grey);
            flex-shrink: 0;
        }
        .pf-subtabs[hidden] { display: none !important; }
        .pf-subtab {
            flex: 1;
            padding: 6px 10px;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            font-size: 11px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-family: inherit;
            min-height: 30px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf);
        }
        .pf-subtab.active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }
        .pf-subtab:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }

        /* ── Destacados ──
           Mismo concepto que `#music-catview-destacados` del escritorio:
           hasta 3 items con `featured: true`. Aquí los pintamos como una
           rejilla 3-col compacta encima de la lista. Tap en un slot con
           reseña abre el modal de reseña (mismo click delegado). */
        .pf-destacados {
            padding: 6px 8px;
            background: var(--input-bg, #fff);
            color: var(--input-text, var(--text, #000));
            border-bottom: 1px dotted var(--text-faint, #aaa);
            flex-shrink: 0;
        }
        .pf-destacados[hidden] { display: none !important; }
        .pf-destacados-title {
            font-size: 10px;
            color: var(--text-muted, var(--text-faint, #666));
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .pf-destacados-slots {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }
        .pf-dest-slot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            padding: 6px 4px;
            min-height: 92px;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            text-align: center;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf);
            overflow: hidden;
        }
        .pf-dest-slot.empty {
            color: var(--text-faint, #888);
            justify-content: center;
            align-items: center;
            font-size: 18px;
        }
        .pf-dest-slot.has-review {
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
            touch-action: manipulation;
        }
        .pf-dest-slot.has-review:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }
        .pf-dest-cover {
            width: 44px;
            height: 44px;
            object-fit: cover;
            image-rendering: pixelated;
            background: var(--inset-bg, #ddd);
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .pf-dest-cover-ph {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--accent, #000080);
        }
        .pf-dest-title {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            width: 100%;
            color: inherit;
        }
        .pf-dest-artist {
            font-size: 9px;
            color: var(--text-faint, #666);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }
        .pf-dest-stars {
            font-size: 10px;
            color: var(--star-color, #f4c130);
            display: inline-flex;
            align-items: center;
            gap: 1px;
            line-height: 1;
        }
        .pf-dest-stars > .pf-stars > span { display: inline-block; }
        .pf-dest-stars-num {
            font-size: 9px;
            color: var(--text-faint, #666);
            font-weight: bold;
            margin-left: 2px;
        }

        /* ── Tabs horizontales (categorías) ── */
        .pf-tabs {
            display: flex;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: var(--btn-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-2, grey);
            scrollbar-width: none;
            flex-shrink: 0;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset 1px 1px var(--bezel-light-2, #fff);
        }
        .pf-tabs::-webkit-scrollbar { display: none; }
        .pf-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;          /* nunca colapsa contenido por flex sibling */
            padding: 8px 10px;
            color: var(--text, #000);
            background: transparent;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
            line-height: 1;          /* evita que el badge desborde verticalmente */
            letter-spacing: 0.3px;
            border: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-family: inherit;
            min-height: 36px;
        }
        .pf-tab.active {
            color: var(--accent, #000080);
            border-bottom-color: var(--accent, #000080);
            background: var(--input-bg, #fff);
        }
        .pf-tab .pf-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 16px;
            height: 16px;
            padding: 0 5px;
            box-sizing: border-box;
            font-size: 10px;
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            font-weight: bold;
            line-height: 1;
        }

        /* ── Lista de items dentro de un mh-panel ── */
        .pf-list { padding: 0; }
        .pf-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-top: 1px dotted var(--text-faint, #aaa);
            min-height: 56px;
            color: var(--input-text, var(--text, #000));
        }
        .pf-item:first-child { border-top: none; }
        /* Solo el item con reseña ofrece feedback `:active` — los
           que no son clicables se quedan sin highlight para no
           sugerir falsa interactividad. */
        .pf-item.has-review:active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .pf-item-poster {
            width: 40px;
            height: 56px;
            object-fit: cover;
            background: var(--inset-bg, #ddd);
            flex-shrink: 0;
            margin-left: 2px;          /* aire al shadow exterior */
            /* Marco de foto Win98 (idéntico al del escritorio). */
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .pf-item.music .pf-item-poster { width: 44px; height: 44px; }
        .pf-item-poster.placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--accent, #000080);
            background: var(--inset-bg, #ddd);
        }
        .pf-item-info { flex: 1; min-width: 0; }
        .pf-item-title {
            font-size: 13px;
            font-weight: bold;
            line-height: 1.3;
            color: inherit;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .pf-item-sub {
            font-size: 11px;
            color: var(--text-faint, #666);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pf-item.has-review:active .pf-item-sub { color: var(--accent-text, #fff); }
        .pf-status {
            display: inline-block;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #fff;
        }
        .pf-status.done    { background: #2da34c; }
        .pf-status.doing   { background: #1e7adb; }
        .pf-status.pending { background: #555; }
        .pf-status.dropped { background: #c0392b; }
        /* Estrellas con medias estrellas — replica el patrón de
           perfil.css del escritorio (makeStarsHtml). Cada estrella es
           un <span> y para la media usamos clip-path inline.
           El contenedor con .pf-review-trigger es táctil y abre la
           reseña al pulsar. */
        .pf-stars {
            color: var(--star-color, #f4c130);
            font-size: 13px;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 1px;
            line-height: 1;
        }
        .pf-stars > span {
            display: inline-block;
            line-height: 1;
        }
        .pf-stars-num {
            color: var(--text-faint, #666);
            font-size: 10px;
            margin-left: 4px;
            font-weight: bold;
        }
        .pf-item.has-review:active .pf-stars-num { color: var(--accent-text, #fff); }
        /* Item con reseña: todo el row es clicable. Cursor pointer y
           feedback `:active` reforzado para indicar que la fila completa
           abre la reseña al pulsar. */
        .pf-item.has-review {
            cursor: pointer;
            touch-action: manipulation;
            -webkit-user-select: none;
            user-select: none;
        }
        .pf-stars-wrap {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        /* ── Modal de reseña — centrado, con tema del usuario ──
           Centrado vertical (no bottom-sheet) para que se sienta como
           una ventana modal Win98 clásica. Mount como hijo de <body>
           (no de <html>) para heredar las variables del tema activo
           que viven en `body.theme-X { --win-bg: ...; ... }`. */
        .pf-modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.55);
            z-index: 10000;
            display: flex;
            align-items: center;            /* ← centrado vertical */
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        .pf-modal {
            width: 100%;
            max-width: 380px;
            max-height: calc(100vh - 32px);
            display: flex;
            flex-direction: column;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }
        .pf-modal > .title-bar {
            flex-shrink: 0;
            /* Refuerzo: el title-bar de 98.css usa --titlebar-* tokens
               que ya inyecta el tema. Aquí solo aseguramos que respete
               la altura sin colapsar dentro del flex. */
            min-height: 22px;
        }
        .pf-modal > .window-body {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 14px 16px;
            background: var(--win-body-bg, var(--win-bg, silver));
            color: var(--text, #000);
        }
        .pf-modal .pf-review-title {
            font-size: 14px;
            font-weight: bold;
            color: var(--text, #000);
            margin: 0 0 10px;
            word-break: break-word;
        }
        /* Bloque comentario — match al #profile-review-view-comment del escritorio. */
        .pf-modal .pf-review-comment {
            font-size: 12px;
            font-style: italic;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.65;
            color: var(--accent, #000080);
            margin: 0 0 12px;
            max-height: 220px;
            overflow-y: auto;
        }
        .pf-modal .pf-review-comment-empty {
            font-size: 12px;
            font-style: italic;
            color: var(--text-faint, #888);
            margin: 0 0 12px;
        }
        /* Header con firma del usuario — match al #profile-review-view-header. */
        .pf-modal .pf-review-header {
            font-size: 12px;
            font-weight: bold;
            color: var(--accent, #000080);
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pf-modal .pf-review-header .pf-stars { font-size: 16px; }
        .pf-modal .pf-review-header .pf-stars-num {
            font-size: 12px;
            color: var(--accent, #000080);
        }
        .pf-modal .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid var(--bezel-dark-2, grey);
        }
        .pf-modal .modal-actions .button { min-height: 28px; min-width: 70px; }
        .pf-shared-from {
            color: var(--accent, #000080);
            font-size: 10px;
            background: var(--input-bg, #fff);
            padding: 1px 6px;
            font-weight: bold;
        }
        .pf-item.has-review:active .pf-shared-from {
            background: var(--accent-text, #fff);
            color: var(--accent, #000080);
        }
    </style>
</head>
<body class="mh-body <?= htmlspecialchars($activeThemeClass) ?>">

<div class="window mh-window" id="perfilWindow">
    <div class="title-bar">
        <div class="title-bar-text">👤 Perfil - <?= htmlspecialchars($userLabel) ?></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" onclick="window.location.href='../mobile.php';"></button>
        </div>
    </div>
    <div class="window-body">

        <!-- Header: izquierda avatar + cuadro de conexiones, derecha texto -->
        <div class="mh-userbar pf-userbar">
            <div class="pf-userbar-left">
                <div class="mh-userbar-avatar">
                    <?php if ($userImg): ?>
                        <img src="<?= htmlspecialchars($userImg) ?>" alt="">
                    <?php else: ?>
                        <span>👤</span>
                    <?php endif; ?>
                </div>
                <!-- Cuadrado de conexiones: rejilla 2×2 de iconos sociales,
                     panel hundido Win98 para que destaque bajo el avatar. -->
                <div class="pf-about-socials" id="pf-about-socials"></div>
            </div>
            <div class="mh-userbar-text">
                <div class="mh-userbar-greeting">Perfil de</div>
                <div class="mh-userbar-name"><?= htmlspecialchars($userLabel) ?></div>
                <!-- Bio = "descripción" del escritorio. Si está vacía mostramos
                     "Sin descripción" como placeholder en gris. -->
                <div class="pf-about-bio" id="pf-about-bio">Sin descripción</div>
                <div class="pf-about-meta" id="pf-about-meta"></div>
            </div>
        </div>

        <!-- Pestañas categoría (scroll horizontal) -->
        <nav class="pf-tabs" id="pf-tabs">
            <button class="pf-tab active" data-cat="movies">🎬 Películas <span class="pf-tab-count" id="cnt-movies">·</span></button>
            <button class="pf-tab" data-cat="series">📺 Series <span class="pf-tab-count" id="cnt-series">·</span></button>
            <button class="pf-tab" data-cat="books">📖 Libros <span class="pf-tab-count" id="cnt-books">·</span></button>
            <button class="pf-tab" data-cat="games">🎮 Juegos <span class="pf-tab-count" id="cnt-games">·</span></button>
            <button class="pf-tab" data-cat="music">🎵 Música <span class="pf-tab-count" id="cnt-music">·</span></button>
        </nav>

        <!-- Sub-toggle de música: álbumes vs canciones. Solo visible
             cuando la pestaña activa es 'music'. -->
        <nav class="pf-subtabs" id="pf-music-subtabs" hidden>
            <button class="pf-subtab active" data-mtab="albums" type="button">💿 Álbumes</button>
            <button class="pf-subtab" data-mtab="songs" type="button">🎵 Canciones</button>
        </nav>

        <!-- Destacados: hasta 3 items con `featured`. Solo música. -->
        <div class="pf-destacados" id="pf-destacados" hidden>
            <div class="pf-destacados-title">★ Destacados</div>
            <div class="pf-destacados-slots" id="pf-destacados-slots"></div>
        </div>

        <!-- Lista de items dentro de un panel hundido -->
        <div class="mh-panel pf-list" id="pf-list">
            <div class="mh-empty"><span class="mh-empty-icon">⏳</span>Cargando…</div>
        </div>

        <!-- Status bar Win98 al pie -->
        <div class="mh-statusbar">
            <a href="../mobile.php">‹ Menú</a>
        </div>

    </div>
</div>

<script>
/* ─── Estado y carga de datos ──────────────────────────────────── */
var API = '../assets/profile/api.php';
var STATE = { lists: null, profile: null, current: 'movies', musicView: 'albums' };
var USER_LABEL = <?= json_encode($userLabel) ?>;

function fetchProfile() {
    return fetch(API + '?action=get-profile', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            STATE.profile = d || {};
            renderAbout(STATE.profile);
        })
        .catch(function(){
            var bioEl = document.getElementById('pf-about-bio');
            if (bioEl) { bioEl.textContent = '(error cargando perfil)'; bioEl.classList.add('is-empty'); }
        });
}

/* Pinta la sección "About" con bio, meta y conexiones — mismo
   conjunto de campos que `renderProfileInfo` del escritorio. */
function renderAbout(d) {
    var bioEl     = document.getElementById('pf-about-bio');
    var metaEl    = document.getElementById('pf-about-meta');
    var socialsEl = document.getElementById('pf-about-socials');
    if (!bioEl || !metaEl || !socialsEl) return;

    /* Bio = "descripción". Si está vacía deja "Sin descripción" como
       placeholder gris (idéntico al estado anterior de pf-cover-quote). */
    if (d && d.bio) {
        bioEl.textContent = '" ' + d.bio + ' "';
        bioEl.classList.remove('is-empty');
    } else {
        bioEl.textContent = 'Sin descripción';
        bioEl.classList.add('is-empty');
    }

    /* Meta: pronombres · edad · país */
    var parts = [];
    if (d && d.pronouns) parts.push(esc(d.pronouns));
    if (d && d.age)      parts.push(esc(d.age) + ' años');
    if (d && d.country)  parts.push('📍 ' + esc(d.country));
    metaEl.innerHTML = parts.length
        ? parts.join('<span class="pf-meta-sep">·</span>')
        : '';

    /* Conexiones — replica el array `socials` del escritorio
       (perfil.php:1551). Si el valor no es URL completa, construye una.
       Discord no tiene URL pública → tap copia el nombre al portapapeles. */
    var socials = [
        { key: 'steam',     icon: '🎮', label: 'Steam',
          url: function(v){ return /^https?:\/\//.test(v) ? v : 'https://steamcommunity.com/id/' + encodeURIComponent(v); } },
        { key: 'discord',   icon: '💬', label: 'Discord',   url: null },
        { key: 'twitter',   icon: '🐦', label: 'Twitter',
          url: function(v){ return /^https?:\/\//.test(v) ? v : 'https://x.com/' + encodeURIComponent(v.replace(/^@/, '')); } },
        { key: 'instagram', icon: '📷', label: 'Instagram',
          url: function(v){ return /^https?:\/\//.test(v) ? v : 'https://instagram.com/' + encodeURIComponent(v.replace(/^@/, '')); } }
    ];
    socialsEl.innerHTML = '';
    socials.forEach(function(s){
        var v = d && d[s.key];
        if (!v) return;
        var a = document.createElement('a');
        a.className = 'pf-about-social';
        a.textContent = s.icon;
        if (s.url) {
            a.href   = s.url(v);
            a.target = '_blank';
            a.rel    = 'noopener noreferrer';
            a.title  = s.label + ': ' + v;
        } else {
            a.href  = '#';
            a.title = s.label + ' (tap para copiar): ' + v;
            a.addEventListener('click', function(e){
                e.preventDefault();
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(v).then(function(){
                        a.style.outline = '1px dotted var(--accent, #000080)';
                        setTimeout(function(){ a.style.outline = ''; }, 700);
                    }).catch(function(){});
                }
            });
        }
        socialsEl.appendChild(a);
    });
}

function fetchLists() {
    return fetch(API + '?action=get-lists', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            STATE.lists = d || {};
            ['movies','series','books','games','music'].forEach(function(cat){
                var arr = STATE.lists[cat] || [];
                document.getElementById('cnt-' + cat).textContent = arr.length;
            });
            renderActiveList();
        })
        .catch(function(){
            document.getElementById('pf-list').innerHTML =
                '<div class="mh-empty"><span class="mh-empty-icon">⚠️</span>Error cargando listas</div>';
        });
}

/* ─── Render ───────────────────────────────────────────────────── */
function statusLabel(s) {
    return { done: 'Terminado', doing: 'En curso', pending: 'Pendiente', dropped: 'Abandonado' }[s] || s;
}
function statusClass(s) {
    return ['done','doing','pending','dropped'].indexOf(s) >= 0 ? s : 'pending';
}
/* makeStarsHtml: replica el render exacto del escritorio (perfil.php) —
   estrellas llenas, medias estrellas con clip-path:inset(0 50% 0 0) y
   estrellas vacías. Devuelve HTML para insertar dentro de .pf-stars. */
function makeStarsHtml(val, total) {
    total = total || 5;
    var h = '';
    for (var i = 1; i <= total; i++) {
        if (val >= i) {
            h += '<span>★</span>';
        } else if (val >= i - 0.5) {
            h += '<span style="clip-path:inset(0 50% 0 0);">★</span>';
        } else {
            h += '<span>☆</span>';
        }
    }
    return h;
}
function esc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderActiveList() {
    var listEl = document.getElementById('pf-list');
    var subtabsEl = document.getElementById('pf-music-subtabs');
    var isMusicCat = (STATE.current === 'music');
    /* Sub-tabs álbum/canción solo cuando estamos viendo música. */
    if (subtabsEl) subtabsEl.hidden = !isMusicCat;
    /* Destacados también solo en música. */
    renderDestacados();

    var rawArr = (STATE.lists && STATE.lists[STATE.current]) || [];
    /* Mantenemos pares {item, origIdx} para que el filtro de música no
       rompa el lookup del modal de reseña (que indexa STATE.lists[cat]
       por la posición original). */
    var entries = rawArr.map(function(it, i){ return { it: it, origIdx: i }; });
    if (isMusicCat) {
        var wantType = STATE.musicView === 'songs' ? 'song' : 'album';
        entries = entries.filter(function(e){ return e.it && e.it.type === wantType; });
    }
    if (!entries.length) {
        var emoji = STATE.current === 'movies' ? '🎬'
                  : STATE.current === 'series' ? '📺'
                  : STATE.current === 'books'  ? '📖'
                  : STATE.current === 'games'  ? '🎮'
                  : (STATE.musicView === 'songs' ? '🎵' : '💿');
        var emptyMsg = isMusicCat
            ? (STATE.musicView === 'songs' ? 'Sin canciones' : 'Sin álbumes')
            : 'Sin elementos en esta lista';
        listEl.innerHTML = '<div class="mh-empty"><span class="mh-empty-icon">' + emoji + '</span>' + emptyMsg + '</div>';
        return;
    }
    var html = '';
    entries.forEach(function(entry){
        var it  = entry.it;
        var idx = entry.origIdx;       /* índice en STATE.lists[cat] sin filtrar */
        var isMusic = (STATE.current === 'music');
        var posterHtml = it.image
            ? '<img class="pf-item-poster" src="' + esc(it.image) + '" alt="" loading="lazy">'
            : '<div class="pf-item-poster placeholder">' +
              (isMusic ? '🎵' : STATE.current === 'movies' ? '🎬'
                : STATE.current === 'series' ? '📺'
                : STATE.current === 'books'  ? '📖' : '🎮') +
              '</div>';
        var subParts = [];
        if (isMusic && it.artist) subParts.push('<span>' + esc(it.artist) + '</span>');
        if (!isMusic && it.status) {
            subParts.push('<span class="pf-status ' + statusClass(it.status) + '">' +
                          esc(statusLabel(it.status)) + '</span>');
        }
        var hasReview = !!(it.review && it.review.stars);
        if (hasReview) {
            subParts.push(
                '<span class="pf-stars-wrap">' +
                    '<span class="pf-stars">' + makeStarsHtml(it.review.stars) + '</span>' +
                    '<span class="pf-stars-num">' + esc(it.review.stars) + '</span>' +
                '</span>'
            );
        }
        if (it.sharedFrom) {
            subParts.push('<span class="pf-shared-from">compartido</span>');
        }
        /* Toda la fila es el target táctil cuando hay reseña — el click
           delegado lee data-cat/data-idx para abrir el modal. */
        var classes = 'pf-item' + (isMusic ? ' music' : '') + (hasReview ? ' has-review' : '');
        var dataAttrs = hasReview
            ? ' data-cat="' + esc(STATE.current) + '" data-idx="' + idx + '" role="button" tabindex="0"'
            : '';
        html += '<div class="' + classes + '"' + dataAttrs + '>' +
                  posterHtml +
                  '<div class="pf-item-info">' +
                    '<div class="pf-item-title">' + esc(it.title) + '</div>' +
                    (subParts.length ? '<div class="pf-item-sub">' + subParts.join('') + '</div>' : '') +
                  '</div>' +
                '</div>';
    });
    listEl.innerHTML = html;
}

/* Render de la sección Destacados (música). Hasta 3 slots; los vacíos
   son `—`. Mismo concepto que renderMusicDestacados del escritorio. */
function renderDestacados() {
    var box     = document.getElementById('pf-destacados');
    var slotsEl = document.getElementById('pf-destacados-slots');
    if (!box || !slotsEl) return;
    var isMusicCat = (STATE.current === 'music');
    box.hidden = !isMusicCat;
    if (!isMusicCat) return;

    var music = (STATE.lists && STATE.lists.music) || [];
    /* Preservamos los índices originales en STATE.lists.music para que
       el lookup del modal de reseña funcione. */
    var featured = [];
    music.forEach(function(it, origIdx){
        if (it && it.featured) featured.push({ it: it, origIdx: origIdx });
    });

    var html = '';
    for (var s = 0; s < 3; s++) {
        var entry = featured[s] || null;
        if (!entry) {
            html += '<div class="pf-dest-slot empty">—</div>';
            continue;
        }
        var it = entry.it;
        var imgHtml = it.image
            ? '<img class="pf-dest-cover" src="' + esc(it.image) + '" alt="" loading="lazy">'
            : '<div class="pf-dest-cover pf-dest-cover-ph">' + (it.type === 'album' ? '💿' : '🎵') + '</div>';
        var hasReview = !!(it.review && it.review.stars);
        var starsHtml = hasReview
            ? '<div class="pf-dest-stars">' +
                '<span class="pf-stars">' + makeStarsHtml(it.review.stars) + '</span>' +
                '<span class="pf-dest-stars-num">' + esc(it.review.stars) + '</span>' +
              '</div>'
            : '';
        var classes = 'pf-dest-slot' + (hasReview ? ' has-review' : '');
        var dataAttrs = hasReview
            ? ' data-cat="music" data-idx="' + entry.origIdx + '" role="button" tabindex="0" aria-label="Ver reseña"'
            : '';
        html += '<div class="' + classes + '"' + dataAttrs + '>' +
                  imgHtml +
                  '<div class="pf-dest-title">' + esc(it.title) + '</div>' +
                  (it.artist ? '<div class="pf-dest-artist">' + esc(it.artist) + '</div>' : '') +
                  starsHtml +
                '</div>';
    }
    slotsEl.innerHTML = html;
}

/* ─── Tab switching ────────────────────────────────────────────── */
document.getElementById('pf-tabs').addEventListener('click', function(e){
    var btn = e.target.closest('.pf-tab');
    if (!btn) return;
    var cat = btn.dataset.cat;
    if (cat === STATE.current) return;
    STATE.current = cat;
    document.querySelectorAll('.pf-tab').forEach(function(b){
        b.classList.toggle('active', b.dataset.cat === cat);
    });
    renderActiveList();
});

/* Sub-tabs de música — álbumes vs canciones. */
document.getElementById('pf-music-subtabs').addEventListener('click', function(e){
    var btn = e.target.closest('.pf-subtab');
    if (!btn) return;
    var mtab = btn.dataset.mtab;
    if (mtab === STATE.musicView) return;
    STATE.musicView = mtab;
    document.querySelectorAll('.pf-subtab').forEach(function(b){
        b.classList.toggle('active', b.dataset.mtab === mtab);
    });
    renderActiveList();
});

/* ─── Review modal — replica showReviewView del escritorio ──────
   Bottom-sheet con look Win98 que muestra el comentario en cursiva,
   las estrellas exactas (con media estrella) y la firma del usuario.
   No edita: es solo lectura, igual que en el escritorio. */
function openReviewView(item) {
    var review = item.review || {};
    var commentHtml = (review.comment && review.comment.trim())
        ? '<div class="pf-review-comment">" ' + esc(review.comment) + ' "</div>'
        : '<div class="pf-review-comment-empty">(Sin comentario)</div>';
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">★ Reseña</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-review-title">' + esc(item.title || '') + '</div>' +
                commentHtml +
                '<div class="pf-review-header">' +
                    '— ' + esc(USER_LABEL) + ' &nbsp;—&nbsp; ' +
                    '<span class="pf-stars">' + makeStarsHtml(review.stars) + '</span>' +
                    '<span class="pf-stars-num">' + esc(review.stars) + '</span>' +
                '</div>' +
                '<div class="modal-actions">' +
                    '<button class="button default" data-act="close" type="button">Cerrar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    /* Mount en <body> (NO en documentElement): el tema del usuario
       vive como `body.theme-X { --win-bg: ...; --accent: ...; }`, así
       que cualquier elemento debe ser descendiente de <body> para
       heredar las variables. */
    document.body.appendChild(bd);
    function close() { if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('[data-act="close"]').addEventListener('click', close);
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
}

/* Click delegado a nivel `document`: cualquier tap sobre una fila o
   slot con `.has-review` abre el modal de reseña. Delego en document
   por si un ancestro con overflow se traga el evento. */
document.addEventListener('click', function(e){
    if (!e.target || !e.target.closest) return;
    var row = e.target.closest('.pf-item.has-review') ||
              e.target.closest('.pf-dest-slot.has-review');
    if (!row) return;
    e.preventDefault();
    e.stopPropagation();
    var cat = row.dataset.cat;
    var idx = parseInt(row.dataset.idx, 10);
    var item = (STATE.lists && STATE.lists[cat] && STATE.lists[cat][idx]) || null;
    if (item && item.review) openReviewView(item);
});

/* ─── Bootstrap ─────────────────────────────────────────────────── */
fetchProfile();
fetchLists();
</script>

</body>
</html>
