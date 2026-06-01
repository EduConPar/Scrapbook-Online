<?php
/* ──────────────────────────────────────────────────────────────────────
   MÚSICA — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Reproductor YouTube embebido + listado de playlists del usuario.
   - GET /assets/music/api.php?action=get-playlists → playlists con tracks
   - YouTube IFrame API para reproducir por videoId
   - Mini-player sticky abajo (track actual + play/pause + prev/next)
   Sin editor de playlists, sin import Spotify/Tidal — esas son features
   complejas que viven en el reproductor de escritorio.
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

/* Helper para resolver el avatar de un usuario por label. */
$resolveAvatar = function($label) {
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$label);
    foreach (['png','jpg','jpeg','gif'] as $ext) {
        if (file_exists(dirname(__DIR__) . "/assets/img/{$safe}.{$ext}")) {
            return "../assets/img/{$safe}.{$ext}";
        }
    }
    return '';
};
$userImg = $resolveAvatar($userLabel);

/* Lista de TODOS los usuarios con sus avatares resueltos — la usa el
   frontend para pintar avatares de colaboradores y de "added by".
   Lo pasamos al JS como JSON inline para no tener que pedirlo por
   fetch en cada arranque. */
$usersForJs = [];
foreach ($loginUsers as $k => $u) {
    $usersForJs[] = [
        'key'   => $k,
        'label' => $u['label'],
        'img'   => $resolveAvatar($u['label']),
    ];
}

/* ── TEMA ACTIVO DEL USUARIO ──
   Replica el setup de mobile.php para mantener consistencia visual:
   misma paleta de colores en todas las pantallas móviles. */
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = themeCssRelPath($activeTheme, $userLabel);
    /* refreshActiveThemeCss da una ruta relativa al root del proyecto;
       desde apps/ subimos un nivel para resolver el href. */
    if ($activeThemeCss !== '' && !file_exists(dirname(__DIR__) . '/' . $activeThemeCss)) {
        $activeThemeCss = '';
    }
}

/* desktopBg del tema para el meta theme-color. */
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
    <title>Música — Scrapbook Melon</title>
    <link rel="icon" href="data:,">
    <!-- Mismo stack que mobile.php para Win98 + tema del usuario -->
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
        /* Tweaks específicos de Música — todo lo común (window, userbar,
           panel, statusbar) viene de mobile-theme.css. */

        /* Reservamos espacio abajo para el mini-player sticky. */
        .mu-list { padding-bottom: 88px; }

        /* Cada playlist es un bloque con head colapsable + tracks. */
        .mu-playlist + .mu-playlist {
            border-top: 1px dotted var(--text-faint, #aaa);
        }
        .mu-pl-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            cursor: pointer;
            background: var(--btn-bg, transparent);
            color: var(--text, #000);
            min-height: 44px;
        }
        .mu-pl-head:active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .mu-pl-info { flex: 1; min-width: 0; }
        .mu-pl-name {
            font-size: 14px;
            font-weight: bold;
            color: inherit;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mu-pl-meta {
            font-size: 11px;
            color: var(--text-faint, #666);
            margin-top: 1px;
        }
        /* Avatares de colaboradores: posición lateral (donde estaba el ⋮),
           apilados horizontalmente con leve overlap estilo Slack/GitHub
           para que ocupen poco ancho aunque haya hasta 4. */
        .mu-pl-collabs {
            display: flex;
            flex-direction: row-reverse;  /* el primero queda encima del resto */
            justify-content: flex-end;
            align-items: center;
            flex-shrink: 0;
            padding: 0 8px 0 10px;
        }
        .mu-pl-collab-av {
            width: 32px; height: 32px;
            object-fit: cover;
            image-rendering: pixelated;
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            margin-left: -10px;            /* overlap apilado */
            flex-shrink: 0;
            /* Marco de foto estilo Win98 (perfil.css .profile-avatar-frame). */
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .mu-pl-collab-av:last-child { margin-left: 0; }
        .mu-pl-collab-more {
            background: var(--btn-bg, silver);
            color: var(--text, #000);
        }
        /* Long-press en la cabecera abre el menú de opciones.
           user-select: none → evita que el navegador inicie la selección
           de texto al mantener el dedo pulsado. */
        .mu-pl-head {
            -webkit-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        /* Feedback visual mientras se está aguantando el long-press
           (clase que se añade en touchstart y se quita en touchend). */
        .mu-pl-head.long-pressing,
        .mu-track.long-pressing {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            transition: background 0.15s ease;
        }
        .mu-pl-head.long-pressing .mu-pl-meta,
        .mu-pl-head.long-pressing .mu-pl-arrow,
        .mu-track.long-pressing .mu-track-num,
        .mu-track.long-pressing .mu-track-artist,
        .mu-track.long-pressing .mu-track-dur {
            color: var(--accent-text, #fff);
        }
        /* Evita que la selección de texto se inicie al mantener pulsado. */
        .mu-track {
            -webkit-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }

        /* "Añadido por" en cada track — avatar circular junto a la duración. */
        .mu-track-addedby {
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        .mu-track-addedby-av {
            width: 22px; height: 22px;
            object-fit: cover;
            image-rendering: pixelated;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            margin: 0 2px;             /* aire al shadow exterior */
            /* Marco de foto Win98. */
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .mu-pl-head:active .mu-pl-meta { color: var(--accent-text, #fff); }
        .mu-pl-arrow {
            font-size: 14px;
            color: var(--text-faint, #444);
            transition: transform 0.2s;
            flex-shrink: 0;
        }
        .mu-pl-head:active .mu-pl-arrow { color: var(--accent-text, #fff); }
        .mu-playlist.open .mu-pl-arrow { transform: rotate(90deg); }

        /* Lista de tracks dentro de una playlist abierta. */
        .mu-tracks {
            display: none;
            background: var(--inset-bg, rgba(0,0,0,0.05));
        }
        .mu-playlist.open .mu-tracks { display: block; }
        .mu-track {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            cursor: pointer;
            border-top: 1px dotted var(--text-faint, #aaa);
            color: var(--text, #000);
        }
        .mu-track:active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .mu-track.playing {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .mu-track-num {
            color: var(--text-faint, #888);
            font-size: 11px;
            width: 18px;
            text-align: right;
            flex-shrink: 0;
        }
        .mu-track:active .mu-track-num,
        .mu-track.playing .mu-track-num { color: var(--accent-text, #fff); }

        /* Thumbnail YouTube 16:9 escalado a cuadrado con object-fit: cover
           (recorta el centro, no añade bandas). Marco de foto Win98. */
        .mu-track-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            flex-shrink: 0;
            margin-left: 2px;          /* compensa el shadow exterior */
            background: var(--inset-bg, #ddd);
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .mu-track-thumb-ph {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--accent, #000080);
        }

        .mu-track-info { flex: 1; min-width: 0; }
        .mu-track-title {
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: inherit;
        }
        .mu-track-artist {
            color: var(--text-faint, #666);
            font-size: 11px;
            margin-top: 1px;
        }
        .mu-track:active .mu-track-artist,
        .mu-track.playing .mu-track-artist { color: var(--accent-text, #fff); }
        .mu-track-dur {
            color: var(--text-faint, #888);
            font-size: 11px;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }

        /* Editor de reseña — fila de estrellas grandes (tap para puntuar,
           tap en la mitad izquierda = media estrella). */
        #re-stars {
            font-size: 32px;
            letter-spacing: 6px;
            color: var(--accent, #000080);
            user-select: none;
            -webkit-user-select: none;
            line-height: 1;
        }
        #re-stars span {
            display: inline-block;
            position: relative;
            width: 1.1em;
            cursor: pointer;
        }

        /* ── Mini reproductor sticky abajo ── */
        .mu-player {
            position: fixed;
            left: 0; right: 0;
            bottom: 0;
            background: var(--btn-bg, silver);
            border-top: 2px solid var(--accent, #000080);
            padding: 8px 12px calc(8px + env(safe-area-inset-bottom));
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow:
                inset 0 1px var(--bezel-light-1, #fff),
                0 -4px 12px rgba(0,0,0,0.4);
            z-index: 50;
            transform: translateY(110%);
            transition: transform 0.25s ease;
            color: var(--text, #000);
        }
        .mu-player.visible { transform: translateY(0); }
        .mu-player-thumb {
            width: 40px;
            height: 40px;
            margin-left: 2px;          /* compensa el shadow exterior */
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            overflow: hidden;
            /* Marco de foto Win98 (idéntico al del escritorio). */
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .mu-player-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .mu-player-info { flex: 1; min-width: 0; }
        .mu-player-title {
            font-family: 'VT323', monospace;
            font-size: 18px;
            line-height: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text, #000);
            letter-spacing: 0.5px;
        }
        .mu-player-artist {
            font-family: 'VT323', monospace;
            font-size: 14px;
            line-height: 1;
            margin-top: 2px;
            color: var(--text-faint, #666);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            letter-spacing: 0.5px;
        }
        .mu-player-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        /* Botones de control reusan .button Win98 — solo damos forma circular
           a los aux con clase .mu-btn. */
        .mu-btn {
            min-width: 32px;
            min-height: 32px;
            padding: 0;
            font-size: 14px;
        }
        .mu-btn.primary {
            min-width: 40px;
            min-height: 40px;
            font-size: 16px;
        }

        /* ── MODALES WIN98 reutilizables ──
           Overlay + ventana centrada con title-bar + window-body. Pensado
           como bottom-sheet en móvil pero usando estructura Win98. */
        .mu-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 200;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 0;
        }
        .mu-modal-backdrop[hidden] { display: none !important; }
        .mu-modal {
            width: 100%;
            max-width: 460px;
            max-height: 90vh;
            margin-bottom: max(8px, env(safe-area-inset-bottom));
            display: flex;
            flex-direction: column;
        }
        .mu-modal > .title-bar { flex-shrink: 0; }
        .mu-modal > .window-body {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px 12px;
            font-size: 12px;
            background: var(--win-body-bg, var(--win-bg, silver));
            color: var(--text, #000);
        }
        .mu-modal .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--bezel-dark-2, grey);
        }
        .mu-modal .modal-actions .button { min-height: 28px; min-width: 70px; }
        .mu-modal label {
            display: block;
            font-size: 11px;
            color: var(--text, #000);
            margin: 8px 0 3px;
        }
        .mu-modal input[type="text"],
        .mu-modal input[type="url"],
        .mu-modal input[type="search"] {
            width: 100%;
            box-sizing: border-box;
            font-size: 16px;       /* evita auto-zoom iOS */
            font-family: inherit;
        }
        .mu-modal .modal-msg {
            margin: 0 0 8px;
            font-size: 12px;
            line-height: 1.4;
        }
        .mu-modal .modal-err {
            color: var(--error-text, #c00);
            background: var(--warning-bg, #ffeeee);
            border: 1px solid var(--error-text, #c00);
            padding: 4px 6px;
            font-size: 11px;
            margin: 6px 0;
        }
        .mu-modal .modal-loading {
            text-align: center;
            padding: 16px;
            color: var(--text-faint, #666);
            font-size: 12px;
        }
        /* Lista de items dentro de modal (para tracks, usuarios, etc.) */
        .mu-modal-list {
            border: 2px inset var(--bezel-dark-2, grey);
            background: var(--input-bg, #fff);
            padding: 2px;
            margin-top: 4px;
            max-height: 40vh;
            overflow-y: auto;
        }
        .mu-modal-list-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            font-size: 12px;
            border-bottom: 1px dotted var(--text-faint, #aaa);
            color: var(--input-text, var(--text, #000));
        }
        .mu-modal-list-item:last-child { border-bottom: none; }
        .mu-modal-list-item:active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .mu-modal-list-item .item-icon {
            width: 24px; height: 24px;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            object-fit: cover;
            image-rendering: pixelated;
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            font-size: 13px;
            font-weight: bold;
        }
        .mu-modal-list-item .item-main {
            flex: 1; min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mu-modal-list-item .item-action {
            flex-shrink: 0;
            font-size: 11px;
            min-height: 24px;
        }

        /* Botón "+ Nueva playlist" en la cabecera de la lista. */
        .mu-newpl-bar {
            display: flex;
            gap: 6px;
            padding: 4px 6px;
            background: var(--btn-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-2, grey);
            flex-shrink: 0;
        }
        .mu-newpl-bar .button {
            flex: 1;
            min-height: 26px;
            font-size: 11px;
        }

        /* iframe del reproductor YouTube — solo necesitamos el audio. */
        #yt-host {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            opacity: 0;
        }
    </style>
</head>
<body class="mh-body <?= htmlspecialchars($activeThemeClass) ?>">

<div class="window mh-window" id="musicaWindow">
    <div class="title-bar">
        <div class="title-bar-text">🎵 Música - <?= htmlspecialchars($userLabel) ?></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" onclick="window.location.href='../mobile.php';"></button>
        </div>
    </div>
    <div class="window-body">

        <!-- Header: icono música + título + contador de playlists -->
        <div class="mh-userbar">
            <div class="mh-userbar-avatar">
                <?php if ($userImg): ?>
                    <img src="<?= htmlspecialchars($userImg) ?>" alt="">
                <?php else: ?>
                    <span>👤</span>
                <?php endif; ?>
            </div>
            <div class="mh-userbar-text">
                <div class="mh-userbar-greeting">Tu música</div>
                <div class="mh-userbar-name"><?= htmlspecialchars($userLabel) ?></div>
            </div>
            <div>
                <div class="mh-userbar-clock" id="mu-pl-count">—</div>
                <div class="mh-userbar-date">playlists</div>
            </div>
        </div>

        <!-- Botonera arriba para acciones globales -->
        <div class="mu-newpl-bar">
            <button class="button" id="mu-btn-new-pl" type="button">+ Nueva playlist</button>
            <button class="button" id="mu-btn-import" type="button">⤓ Importar</button>
        </div>

        <!-- Lista de playlists colapsables -->
        <div class="mh-panel mu-list" id="mu-list">
            <div class="mh-empty"><span class="mh-empty-icon">⏳</span>Cargando…</div>
        </div>

        <!-- Status bar Win98 al pie -->
        <div class="mh-statusbar">
            <a href="../mobile.php">‹ Menú</a>
        </div>

    </div>
</div>

<!-- Mini reproductor sticky -->
<div class="mu-player" id="mu-player">
    <div class="mu-player-thumb" id="mu-thumb">🎵</div>
    <div class="mu-player-info">
        <div class="mu-player-title" id="mu-now-title">—</div>
        <div class="mu-player-artist" id="mu-now-artist">—</div>
    </div>
    <div class="mu-player-controls">
        <button class="button mu-btn" id="mu-prev" type="button" aria-label="Anterior">⏮</button>
        <button class="button default mu-btn primary" id="mu-toggle" type="button" aria-label="Play/Pausa">▶</button>
        <button class="button mu-btn" id="mu-next" type="button" aria-label="Siguiente">⏭</button>
    </div>
</div>

<!-- Host de modales (se rellena dinámicamente desde JS) -->
<div id="mu-modal-host"></div>

<!-- Iframe oculto donde vive el player de YouTube -->
<div id="yt-host"><div id="yt-iframe"></div></div>

<script>
/* ─── Estado ────────────────────────────────────────────────────── */
var API_MUSIC = '../assets/music/api.php';
var ME_KEY    = <?= json_encode($userKey) ?>;
var ME_LABEL  = <?= json_encode($userLabel) ?>;
var PLAYLISTS = [];        /* respuesta del API */
var USERS     = <?= json_encode($usersForJs, JSON_UNESCAPED_SLASHES) ?>;
                            /* todos los usuarios con sus avatares resueltos
                               (server-side, no fetch) para pintar collabs y
                               "added by" al instante. */
var CUR_PL_IDX = -1;       /* índice de la playlist actualmente reproduciéndose */
var QUEUE     = [];        /* tracks de la playlist en reproducción */
var CUR_IDX   = -1;        /* índice del track actual en QUEUE */
var YT_PLAYER = null;      /* instancia YT.Player (no llamarla YT — choca con window.YT) */
var YT_READY  = false;
/* Música del perfil — la cargamos en paralelo a las playlists para
   poder mostrar la estrella de reseña en cualquier track cuyo videoId
   ya tengas reseñado en tu lista de música. Solo items PROPIOS. */
var MY_PROFILE_MUSIC   = [];
var MY_REVIEWS_BY_YTID = {}; /* ytId → { musicId, title, stars, comment, reviewedAt } */

function esc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function fmtDur(sec) {
    sec = parseInt(sec, 10) || 0;
    var m = Math.floor(sec / 60);
    var s = sec % 60;
    return m + ':' + (s < 10 ? '0' + s : s);
}
/* Duración total formateada como "1h 23m" o "23m" o "" si la playlist
   no tiene durations cargadas. */
function fmtDurTotal(tracks) {
    var total = 0, has = false;
    (tracks || []).forEach(function(t){ if (t.duration) { total += +t.duration; has = true; } });
    if (!has) return '';
    var h = Math.floor(total / 3600);
    var m = Math.floor((total % 3600) / 60);
    return (h > 0 ? h + 'h ' : '') + m + 'm';
}

/* Lookup de avatar de usuario por LABEL (que es lo que viene en
   track.addedBy y pl.sharedLabel). Devuelve la url del avatar o null. */
function userAvatarByLabel(label) {
    if (!label) return null;
    for (var i = 0; i < USERS.length; i++) {
        if (USERS[i].label === label) return USERS[i].img || null;
    }
    return null;
}
function userAvatarByKey(key) {
    if (!key) return null;
    for (var i = 0; i < USERS.length; i++) {
        if (USERS[i].key === key) return USERS[i].img || null;
    }
    return null;
}
function userLabelByKey(key) {
    if (!key) return '';
    for (var i = 0; i < USERS.length; i++) {
        if (USERS[i].key === key) return USERS[i].label;
    }
    return key;
}

/* ─── Carga playlists ───────────────────────────────────────────── */
function loadPlaylists() {
    return fetch(API_MUSIC + '?action=get-playlists', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            PLAYLISTS = Array.isArray(d) ? d : [];
            document.getElementById('mu-pl-count').textContent =
                PLAYLISTS.length + ' playlist' + (PLAYLISTS.length === 1 ? '' : 's');
            renderPlaylists();
        })
        .catch(function(){
            document.getElementById('mu-list').innerHTML =
                '<div class="mh-empty"><span class="mh-empty-icon">⚠️</span>Error cargando playlists</div>';
        });
}

function renderPlaylists() {
    var listEl = document.getElementById('mu-list');
    if (!PLAYLISTS.length) {
        listEl.innerHTML = '<div class="mh-empty"><span class="mh-empty-icon">🎵</span>No tienes playlists todavía</div>';
        return;
    }
    var html = '';
    PLAYLISTS.forEach(function(pl, i){
        var tracks = pl.tracks || [];
        var collabs = pl.collaborators || [];
        var isOwn = !pl.sharedFrom;
        /* Meta: nº canciones · duración total · "compartido por X" */
        var metaParts = [tracks.length + ' canción' + (tracks.length === 1 ? '' : 'es')];
        var dur = fmtDurTotal(tracks);
        if (dur) metaParts.push(dur);
        if (pl.sharedLabel) metaParts.push('compartido por ' + esc(pl.sharedLabel));
        var meta = metaParts.join(' · ');

        /* Avatares de colaboradores. Cuando es mi playlist con collabs:
           muestro los avatares de los demás. Cuando es compartida conmigo:
           muestro el avatar del owner + el resto de collabs (sin yo). */
        var avatarsHtml = '';
        var avatarsToShow = [];
        if (isOwn) {
            collabs.forEach(function(ck){
                if (ck !== ME_KEY) avatarsToShow.push(ck);
            });
        } else {
            /* owner del compartido — pl.sharedFrom es su user_key */
            if (pl.sharedFrom) avatarsToShow.push(pl.sharedFrom);
            collabs.forEach(function(ck){
                if (ck !== ME_KEY && ck !== pl.sharedFrom) avatarsToShow.push(ck);
            });
        }
        if (avatarsToShow.length) {
            avatarsHtml = '<div class="mu-pl-collabs">';
            avatarsToShow.slice(0, 4).forEach(function(ck){
                var img = userAvatarByKey(ck);
                var lbl = userLabelByKey(ck);
                avatarsHtml += img
                    ? '<img class="mu-pl-collab-av" src="' + esc(img) + '" alt="' + esc(lbl) + '" title="' + esc(lbl) + '">'
                    : '<div class="mu-pl-collab-av mu-pl-collab-av-ph" title="' + esc(lbl) + '">' + esc(lbl.substring(0, 1)) + '</div>';
            });
            if (avatarsToShow.length > 4) {
                avatarsHtml += '<div class="mu-pl-collab-av mu-pl-collab-more">+' + (avatarsToShow.length - 4) + '</div>';
            }
            avatarsHtml += '</div>';
        }

        html += '<div class="mu-playlist" data-pl-idx="' + i + '">' +
                  '<div class="mu-pl-head">' +
                    '<div class="mu-pl-info">' +
                      '<div class="mu-pl-name">' + esc(pl.name) + '</div>' +
                      '<div class="mu-pl-meta">' + meta + '</div>' +
                    '</div>' +
                    avatarsHtml +
                    '<div class="mu-pl-arrow">›</div>' +
                  '</div>' +
                  '<div class="mu-tracks">';

        /* Si la playlist tiene collabs, mostramos "added by" en cada track. */
        var showAddedBy = avatarsToShow.length > 0 || isOwn === false;
        tracks.forEach(function(tr, ti){
            var thumbUrl = tr.videoId ? 'https://i.ytimg.com/vi/' + esc(tr.videoId) + '/mqdefault.jpg' : '';
            var addedByHtml = '';
            if (showAddedBy && tr.addedBy) {
                var addImg = userAvatarByLabel(tr.addedBy);
                addedByHtml = '<div class="mu-track-addedby" title="Añadido por ' + esc(tr.addedBy) + '">' +
                              (addImg
                                ? '<img class="mu-track-addedby-av" src="' + esc(addImg) + '" alt="">'
                                : '<span class="mu-track-addedby-av mu-track-addedby-av-ph">' + esc(tr.addedBy.substring(0,1)) + '</span>') +
                              '</div>';
            }
            /* Las estrellas de reseña NO se muestran en la app de música
               (la calificación vive en el perfil). Sigue el editor de
               reseña al añadir al perfil, pero los tracks no las pintan. */
            html += '<div class="mu-track" data-pl-idx="' + i + '" data-tr-idx="' + ti + '">' +
                      '<div class="mu-track-num">' + (ti + 1) + '</div>' +
                      (thumbUrl
                        ? '<img class="mu-track-thumb" src="' + thumbUrl + '" alt="" loading="lazy">'
                        : '<div class="mu-track-thumb mu-track-thumb-ph">🎵</div>') +
                      '<div class="mu-track-info">' +
                        '<div class="mu-track-title">' + esc(tr.title || tr.videoId) + '</div>' +
                        (tr.artist ? '<div class="mu-track-artist">' + esc(tr.artist) + '</div>' : '') +
                      '</div>' +
                      addedByHtml +
                      '<div class="mu-track-dur">' + (tr.duration ? fmtDur(tr.duration) : '—') + '</div>' +
                    '</div>';
        });
        html += '</div></div>';
    });
    listEl.innerHTML = html;
}

/* ─── Long-press handler (delegado) ───────────────────────────────
   Mantener pulsado abre un menú contextual:
   - sobre `.mu-pl-head` → muOpenPlaylistMenu (editar, colaboradores…)
   - sobre `.mu-track`   → muOpenTrackMenu    (añadir a perfil, quitar…)
   La flag `_lpFired` bloquea el click sintético posterior para que no
   se haga toggle de la playlist ni se inicie la reproducción. */
var _lpTimer = null, _lpStartX = 0, _lpStartY = 0, _lpFired = false, _lpEl = null;
var _LP_MS = 500, _LP_TOLERANCE = 12;

function _lpStart(el, x, y, onLongPress) {
    _lpEl = el;
    _lpStartX = x;
    _lpStartY = y;
    _lpFired = false;
    el.classList.add('long-pressing');
    _lpTimer = setTimeout(function(){
        _lpTimer = null;
        _lpFired = true;
        el.classList.remove('long-pressing');
        try { navigator.vibrate && navigator.vibrate(40); } catch (_) {}
        onLongPress();
    }, _LP_MS);
}
function _lpCancel() {
    if (_lpTimer) { clearTimeout(_lpTimer); _lpTimer = null; }
    if (_lpEl)    { _lpEl.classList.remove('long-pressing'); _lpEl = null; }
}
/* Resuelve el elemento bajo el dedo/cursor y devuelve la callback que
   debe disparar el long-press. Centralizado para no duplicar la lógica
   entre touchstart y mousedown. */
function _lpResolve(target) {
    var headEl = target.closest('.mu-pl-head');
    if (headEl) {
        var pl = headEl.parentElement;
        var idx = parseInt(pl.dataset.plIdx, 10);
        return { el: headEl, cb: function(){ muOpenPlaylistMenu(idx); } };
    }
    var trackEl = target.closest('.mu-track');
    if (trackEl) {
        var pi = parseInt(trackEl.dataset.plIdx, 10);
        var ti = parseInt(trackEl.dataset.trIdx, 10);
        return { el: trackEl, cb: function(){ muOpenTrackMenu(pi, ti); } };
    }
    return null;
}

var listEl = document.getElementById('mu-list');
listEl.addEventListener('touchstart', function(e){
    if (!e.touches || e.touches.length !== 1) return;
    var r = _lpResolve(e.target);
    if (!r) return;
    _lpStart(r.el, e.touches[0].clientX, e.touches[0].clientY, r.cb);
}, { passive: true });
listEl.addEventListener('touchmove', function(e){
    if (!_lpTimer || !e.touches[0]) return;
    if (Math.abs(e.touches[0].clientX - _lpStartX) > _LP_TOLERANCE ||
        Math.abs(e.touches[0].clientY - _lpStartY) > _LP_TOLERANCE) {
        _lpCancel();
    }
}, { passive: true });
listEl.addEventListener('touchend',    _lpCancel);
listEl.addEventListener('touchcancel', _lpCancel);

/* Soporte mouse (testing en escritorio): mismo gesto con mousedown. */
listEl.addEventListener('mousedown', function(e){
    if (e.button !== 0) return;
    var r = _lpResolve(e.target);
    if (!r) return;
    _lpStart(r.el, e.clientX, e.clientY, r.cb);
});
listEl.addEventListener('mousemove', function(e){
    if (!_lpTimer) return;
    if (Math.abs(e.clientX - _lpStartX) > _LP_TOLERANCE ||
        Math.abs(e.clientY - _lpStartY) > _LP_TOLERANCE) {
        _lpCancel();
    }
});
listEl.addEventListener('mouseup',    _lpCancel);
listEl.addEventListener('mouseleave', _lpCancel);

/* ─── Click handlers (delegados) ───────────────────────────────── */
listEl.addEventListener('click', function(e){
    /* Si acabamos de disparar long-press, ignora el click sintético
       que viene con el touchend para no togglear la playlist. */
    if (_lpFired) { _lpFired = false; e.preventDefault(); e.stopPropagation(); return; }
    var headEl = e.target.closest('.mu-pl-head');
    if (headEl) {
        var pl = headEl.parentElement;
        pl.classList.toggle('open');
        return;
    }
    var trackEl = e.target.closest('.mu-track');
    if (trackEl) {
        var pi = parseInt(trackEl.dataset.plIdx, 10);
        var ti = parseInt(trackEl.dataset.trIdx, 10);
        playFromPlaylist(pi, ti);
    }
});

function onYTState(e) {
    var btn = document.getElementById('mu-toggle');
    if (e.data === 1) { btn.textContent = '▌▌'; setMediaPlaybackState('playing'); }
    else if (e.data === 2) { btn.textContent = '▶'; setMediaPlaybackState('paused'); }
    else if (e.data === 0) nextTrack();
}

/* ─── Reproductor YouTube ──────────────────────────────────────── */
/* Carga la IFrame API una vez. El callback global onYouTubeIframeAPIReady
   es el contrato standard de la API. */
window.onYouTubeIframeAPIReady = function() {
    YT_PLAYER = new window.YT.Player('yt-iframe', {
        height: '1', width: '1',
        playerVars: { playsinline: 1, autoplay: 0 },
        events: {
            'onReady':       function(){ YT_READY = true; },
            'onStateChange': onYTState,
            'onError':       function(){ nextTrack(); }
        }
    });
};
(function loadYT(){
    var s = document.createElement('script');
    s.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(s);
})();

/* YT.PlayerState: -1 unstarted, 0 ended, 1 playing, 2 paused, 3 buffering, 5 cued */

/* ─── Media Session API ────────────────────────────────────────────
   Le dice al SO qué se está reproduciendo (título, artista, artwork)
   y cómo responder a los botones de control (play/pause/prev/next).
   - Android: aparece la track en la pantalla de bloqueo + en el panel
     de notificaciones, y los botones físicos (auriculares, bluetooth)
     funcionan. Combined con la PWA standalone, esto permite que el
     audio siga sonando aunque el móvil se bloquee.
   - iOS: aparece en el control center y en pantalla de bloqueo.
     Background audio en PWA es limitado pero MediaSession ayuda. */
function updateMediaSession(tr) {
    if (!('mediaSession' in navigator)) return;
    if (!tr) { navigator.mediaSession.metadata = null; return; }
    try {
        navigator.mediaSession.metadata = new MediaMetadata({
            title:  tr.title  || 'Track',
            artist: tr.artist || 'Melon Hub',
            album:  'Melon Hub',
            artwork: tr.videoId ? [
                { src: 'https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg',  sizes: '320x180', type: 'image/jpeg' },
                { src: 'https://i.ytimg.com/vi/' + tr.videoId + '/hqdefault.jpg',  sizes: '480x360', type: 'image/jpeg' },
                { src: 'https://i.ytimg.com/vi/' + tr.videoId + '/maxresdefault.jpg', sizes: '1280x720', type: 'image/jpeg' }
            ] : []
        });
    } catch (_) { /* algunos navegadores antiguos */ }
}
function setMediaPlaybackState(state) {
    if (!('mediaSession' in navigator)) return;
    try { navigator.mediaSession.playbackState = state; } catch (_) {}
}
if ('mediaSession' in navigator) {
    /* Action handlers — el SO los invoca cuando el usuario toca los
       botones de la lock screen / notificación / auriculares. */
    try {
        navigator.mediaSession.setActionHandler('play',           function(){ if (YT_PLAYER) YT_PLAYER.playVideo();  });
        navigator.mediaSession.setActionHandler('pause',          function(){ if (YT_PLAYER) YT_PLAYER.pauseVideo(); });
        navigator.mediaSession.setActionHandler('previoustrack',  prevTrack);
        navigator.mediaSession.setActionHandler('nexttrack',      nextTrack);
        navigator.mediaSession.setActionHandler('stop',           function(){ if (YT_PLAYER) YT_PLAYER.stopVideo();  });
    } catch (_) {}
}

function playFromPlaylist(pi, ti) {
    var pl = PLAYLISTS[pi];
    if (!pl) return;
    QUEUE = (pl.tracks || []).slice();
    CUR_IDX = ti;
    playCurrent();
}

function playCurrent() {
    if (CUR_IDX < 0 || CUR_IDX >= QUEUE.length) return;
    var tr = QUEUE[CUR_IDX];
    if (!tr || !tr.videoId) { nextTrack(); return; }
    /* Marca visualmente el track activo en la lista. */
    document.querySelectorAll('.mu-track.playing').forEach(function(el){ el.classList.remove('playing'); });
    var sel = document.querySelector('.mu-track[data-tr-idx="' + CUR_IDX + '"]');
    if (sel) sel.classList.add('playing');
    /* Info en el mini-player. */
    document.getElementById('mu-now-title').textContent  = tr.title  || tr.videoId;
    document.getElementById('mu-now-artist').textContent = tr.artist || '';
    document.getElementById('mu-thumb').innerHTML =
        '<img src="https://i.ytimg.com/vi/' + esc(tr.videoId) + '/mqdefault.jpg" alt="">';
    document.getElementById('mu-player').classList.add('visible');
    /* Registra metadata para la lock screen / auriculares / Bluetooth. */
    updateMediaSession(tr);
    /* Si el SDK aún no está, esperamos un tick. */
    if (!YT_READY || !YT_PLAYER) {
        var waitT = setInterval(function(){
            if (YT_READY && YT_PLAYER) { clearInterval(waitT); YT_PLAYER.loadVideoById(tr.videoId); }
        }, 150);
        return;
    }
    YT_PLAYER.loadVideoById(tr.videoId);
}

function nextTrack() {
    if (CUR_IDX < 0) return;
    CUR_IDX++;
    if (CUR_IDX >= QUEUE.length) CUR_IDX = 0;     /* loop circular */
    playCurrent();
}
function prevTrack() {
    if (CUR_IDX < 0) return;
    CUR_IDX--;
    if (CUR_IDX < 0) CUR_IDX = QUEUE.length - 1;  /* loop circular */
    playCurrent();
}

document.getElementById('mu-toggle').addEventListener('click', function(){
    if (!YT_READY || !YT_PLAYER || CUR_IDX < 0) return;
    var st = YT_PLAYER.getPlayerState();
    if (st === 1) YT_PLAYER.pauseVideo();
    else          YT_PLAYER.playVideo();
});
document.getElementById('mu-next').addEventListener('click', nextTrack);
document.getElementById('mu-prev').addEventListener('click', prevTrack);

/* ════════════════════════════════════════════════════════════════════
   MODALES + EDICIÓN DE PLAYLISTS
   ════════════════════════════════════════════════════════════════════ */

/* ─── Helper modal genérico ────────────────────────────────────────
   Crea un overlay con una ventana Win98 dentro y devuelve la API para
   manipular su contenido y cerrarla. */
function muOpenModal(opts) {
    var host = document.getElementById('mu-modal-host');
    var bd = document.createElement('div');
    bd.className = 'mu-modal-backdrop';
    bd.innerHTML =
        '<div class="window mu-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">' + esc(opts.title || '') + '</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body"></div>' +
        '</div>';
    var modal = bd.querySelector('.mu-modal');
    var body = bd.querySelector('.window-body');
    var closeBtn = bd.querySelector('.title-bar-controls button');
    body.innerHTML = opts.body || '';
    host.appendChild(bd);

    var api = {
        el: bd,
        body: body,
        modal: modal,
        close: function(){ if (bd.parentNode) bd.parentNode.removeChild(bd); },
        setBody: function(html){ body.innerHTML = html; }
    };
    closeBtn.addEventListener('click', api.close);
    /* Click en backdrop (fuera de la ventana) cierra. */
    bd.addEventListener('click', function(e){
        if (e.target === bd) api.close();
    });
    if (opts.onOpen) opts.onOpen(api);
    return api;
}

/* Confirmación tipo win98 */
function muConfirm(message, onYes) {
    var m = muOpenModal({
        title: 'Confirmar',
        body:
            '<p class="modal-msg">' + esc(message) + '</p>' +
            '<div class="modal-actions">' +
                '<button class="button" type="button" data-act="no">Cancelar</button>' +
                '<button class="button default" type="button" data-act="yes">Sí</button>' +
            '</div>'
    });
    m.body.querySelector('[data-act="no"]').addEventListener('click', m.close);
    m.body.querySelector('[data-act="yes"]').addEventListener('click', function(){
        m.close();
        onYes && onYes();
    });
}

/* Aviso simple */
function muAlert(message, title) {
    var m = muOpenModal({
        title: title || 'Aviso',
        body:
            '<p class="modal-msg">' + esc(message) + '</p>' +
            '<div class="modal-actions">' +
                '<button class="button default" type="button" data-act="ok">OK</button>' +
            '</div>'
    });
    m.body.querySelector('[data-act="ok"]').addEventListener('click', m.close);
}

/* ─── API helpers ──────────────────────────────────────────────── */
function apiPost(action, body) {
    return fetch(API_MUSIC + '?action=' + action, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body || {})
    }).then(function(r){ return r.json().then(function(d){ return { ok: r.ok, status: r.status, data: d }; }); });
}
function apiGet(action, params) {
    var qs = '';
    if (params) {
        var p = [];
        Object.keys(params).forEach(function(k){ p.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k])); });
        if (p.length) qs = '&' + p.join('&');
    }
    return fetch(API_MUSIC + '?action=' + action + qs, { credentials: 'same-origin' })
        .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, status: r.status, data: d }; }); });
}

/* ─── NUEVA PLAYLIST ───────────────────────────────────────────── */
document.getElementById('mu-btn-new-pl').addEventListener('click', function(){
    var m = muOpenModal({
        title: '+ Nueva playlist',
        body:
            '<label for="np-name">Nombre</label>' +
            '<input type="text" id="np-name" maxlength="100" autofocus>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                '<button class="button default" data-act="ok" type="button">Crear</button>' +
            '</div>'
    });
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    m.body.querySelector('[data-act="ok"]').addEventListener('click', function(){
        var name = (m.body.querySelector('#np-name').value || '').trim();
        if (!name) return;
        var payload = { id: 'pl_' + Date.now(), name: name, tracks: [] };
        apiPost('save-playlist-item', payload).then(function(res){
            m.close();
            if (!res.ok) { muAlert((res.data && res.data.error) || 'Error al crear'); return; }
            /* La nueva playlist no viene en la respuesta — recargamos. */
            loadPlaylists();
        });
    });
});

/* ─── MENÚ CONTEXTUAL DE PLAYLIST (⋮) ────────────────────────── */
function muOpenPlaylistMenu(idx) {
    var pl = PLAYLISTS[idx];
    if (!pl) return;
    var isOwn = !pl.sharedFrom;
    var items = [
        { act: 'add',       label: '➕ Añadir canción' },
        { act: 'edit',      label: '✏️ Renombrar' }
    ];
    if (isOwn) {
        items.push({ act: 'collab', label: '👥 Colaboradores' });
        items.push({ act: 'delete', label: '🗑 Eliminar playlist', danger: true });
    } else {
        items.push({ act: 'leave',  label: '🚪 Abandonar (dejar de colaborar)', danger: true });
    }
    var bodyHtml = '<div class="mu-modal-list">';
    items.forEach(function(it){
        bodyHtml += '<div class="mu-modal-list-item" data-act="' + it.act + '">' +
                      '<div class="item-main" style="' + (it.danger ? 'color:var(--error-text, #c00);font-weight:bold;' : '') + '">' + it.label + '</div>' +
                    '</div>';
    });
    bodyHtml += '</div>';
    bodyHtml += '<div class="modal-actions"><button class="button" data-act="cancel" type="button">Cerrar</button></div>';

    var m = muOpenModal({ title: pl.name, body: bodyHtml });
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    m.body.querySelectorAll('.mu-modal-list-item').forEach(function(el){
        el.addEventListener('click', function(){
            var act = el.dataset.act;
            m.close();
            if (act === 'add')    muOpenAddTrack(idx);
            if (act === 'edit')   muOpenEditPlaylist(idx);
            if (act === 'collab') muOpenCollabs(idx);
            if (act === 'delete') muConfirm('¿Eliminar "' + pl.name + '"? Esta acción no se puede deshacer.', function(){ muDeletePlaylist(idx); });
            if (act === 'leave')  muConfirm('¿Dejar de colaborar en "' + pl.name + '"?', function(){ muLeavePlaylist(idx); });
        });
    });
}

/* ─── RENOMBRAR PLAYLIST ────────────────────────────────────────
   Solo cambia el nombre. La eliminación de tracks vive en el menú
   contextual del track (long-press sobre la canción). */
function muOpenEditPlaylist(idx) {
    var pl = PLAYLISTS[idx];
    if (!pl) return;
    var bodyHtml =
        '<label for="ep-name">Nombre</label>' +
        '<input type="text" id="ep-name" maxlength="100" value="' + esc(pl.name) + '" autofocus>' +
        '<div class="modal-actions">' +
            '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
            '<button class="button default" data-act="save" type="button">Guardar</button>' +
        '</div>';
    var m = muOpenModal({ title: 'Renombrar playlist', body: bodyHtml });
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    m.body.querySelector('[data-act="save"]').addEventListener('click', function(){
        var newName = (m.body.querySelector('#ep-name').value || '').trim();
        if (!newName) { muAlert('El nombre no puede estar vacío'); return; }
        if (newName === pl.name) { m.close(); return; }
        var payload = { id: pl.id, name: newName, tracks: pl.tracks || [] };
        if (pl.sharedFrom) payload.sharedFrom = pl.sharedFrom;
        apiPost('save-playlist-item', payload).then(function(res){
            m.close();
            if (!res.ok) { muAlert((res.data && res.data.error) || 'Error al guardar'); return; }
            loadPlaylists();
        });
    });
}

/* ─── MENÚ CONTEXTUAL DE TRACK (long-press) ────────────────────
   Opciones: añadir la canción a la lista de música del perfil del
   usuario, o quitarla de la playlist actual. */
function muOpenTrackMenu(pi, ti) {
    var pl = PLAYLISTS[pi];
    if (!pl) return;
    var tr = (pl.tracks || [])[ti];
    if (!tr) return;
    var items = [
        { act: 'addProfile', label: '➕ Añadir a mi perfil' },
        { act: 'remove',     label: '🗑 Quitar de la playlist', danger: true }
    ];
    var bodyHtml = '<p class="modal-msg" style="margin:0 0 6px;color:var(--text-faint, #666);">' +
                     esc(tr.title || tr.videoId || 'Canción') +
                     (tr.artist ? ' — ' + esc(tr.artist) : '') +
                   '</p>';
    bodyHtml += '<div class="mu-modal-list">';
    items.forEach(function(it){
        bodyHtml += '<div class="mu-modal-list-item" data-act="' + it.act + '">' +
                      '<div class="item-main" style="' + (it.danger ? 'color:var(--error-text, #c00);font-weight:bold;' : '') + '">' + it.label + '</div>' +
                    '</div>';
    });
    bodyHtml += '</div>';
    bodyHtml += '<div class="modal-actions"><button class="button" data-act="cancel" type="button">Cerrar</button></div>';

    var m = muOpenModal({ title: 'Opciones', body: bodyHtml });
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    m.body.querySelectorAll('.mu-modal-list-item').forEach(function(el){
        el.addEventListener('click', function(){
            var act = el.dataset.act;
            m.close();
            if (act === 'addProfile') addTrackToProfile(pi, ti);
            if (act === 'remove') {
                muConfirm('¿Quitar "' + (tr.title || 'esta canción') + '" de la playlist?', function(){
                    removeTrackFromPlaylist(pi, ti);
                });
            }
        });
    });
}

/* Quita la canción `ti` de la playlist `pi` y guarda con save-playlist-item.
   Si la playlist es compartida se preserva sharedFrom para que el server
   actualice la copia del owner correcta. */
function removeTrackFromPlaylist(pi, ti) {
    var pl = PLAYLISTS[pi];
    if (!pl) return;
    var newTracks = (pl.tracks || []).slice();
    if (ti < 0 || ti >= newTracks.length) return;
    newTracks.splice(ti, 1);
    var payload = { id: pl.id, name: pl.name, tracks: newTracks };
    if (pl.sharedFrom) payload.sharedFrom = pl.sharedFrom;
    apiPost('save-playlist-item', payload).then(function(res){
        if (!res.ok) { muAlert((res.data && res.data.error) || 'Error al quitar'); return; }
        /* Si la canción quitada está sonando, paramos el visual highlight
           del track (los índices habrán cambiado). */
        loadPlaylists();
    });
}

/* Añade la canción al apartado "música" del perfil del usuario.
   El API de perfil hace UPSERT + DELETE de los items NO presentes en la
   petición — por eso primero hacemos GET para reenviarlos todos. */
var API_PROFILE = '../assets/profile/api.php';
function profileApiGet(action) {
    return fetch(API_PROFILE + '?action=' + action, { credentials: 'same-origin' })
        .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, status: r.status, data: d }; }); });
}
function profileApiPost(action, body) {
    return fetch(API_PROFILE + '?action=' + action, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body || {})
    }).then(function(r){ return r.json().then(function(d){ return { ok: r.ok, status: r.status, data: d }; }); });
}
/* Reconstruye el lookup ytId → review desde MY_PROFILE_MUSIC. Lo llaman
   loadProfileMusic + saveProfileMusic tras cada cambio. */
function buildReviewsMap() {
    MY_REVIEWS_BY_YTID = {};
    MY_PROFILE_MUSIC.forEach(function(it){
        if (it && it.ytId && it.review && it.review.stars) {
            MY_REVIEWS_BY_YTID[it.ytId] = {
                musicId:    it.id,
                title:      it.title,
                stars:      it.review.stars,
                comment:    it.review.comment || '',
                reviewedAt: it.review.reviewedAt || null
            };
        }
    });
}
/* Carga la música del perfil al arrancar — corre en paralelo a las
   playlists y dispara un re-render si éstas ya están pintadas. */
function loadProfileMusic() {
    return profileApiGet('get-lists').then(function(res){
        if (!res.ok) return;
        var lists = res.data || {};
        MY_PROFILE_MUSIC = (Array.isArray(lists.music) ? lists.music : [])
            .filter(function(m){ return m && !m.sharedFrom; });
        buildReviewsMap();
        if (PLAYLISTS.length) renderPlaylists();
    });
}
/* Guarda la lista de música y refresca el cache local con la respuesta
   canónica del server (incluye los IDs nuevos asignados al INSERT). */
function saveProfileMusic(music, onSuccess) {
    return profileApiPost('save-lists', { category: 'music', items: music }).then(function(r){
        if (!r.ok) { muAlert((r.data && r.data.error) || 'Error guardando en tu perfil'); return; }
        var fresh = (r.data && Array.isArray(r.data.items)) ? r.data.items : music;
        MY_PROFILE_MUSIC = fresh.filter(function(m){ return m && !m.sharedFrom; });
        buildReviewsMap();
        if (PLAYLISTS.length) renderPlaylists();
        onSuccess && onSuccess(MY_PROFILE_MUSIC);
    });
}
/* Aplica una reseña a la canción del perfil con id `musicId`. Si
   newReview es null borra la reseña existente. */
function saveReviewToProfile(musicId, newReview, onSuccess) {
    profileApiGet('get-lists').then(function(res){
        if (!res.ok) { muAlert((res.data && res.data.error) || 'Error obteniendo el perfil'); return; }
        var lists = res.data || {};
        var music = (Array.isArray(lists.music) ? lists.music : [])
            .filter(function(m){ return m && !m.sharedFrom; });
        var idx = music.findIndex(function(m){ return m && m.id === musicId; });
        if (idx === -1) { muAlert('No se encontró la canción en tu perfil'); return; }
        if (newReview) music[idx].review = newReview;
        else           delete music[idx].review;
        saveProfileMusic(music, onSuccess);
    });
}

function addTrackToProfile(pi, ti) {
    var pl = PLAYLISTS[pi];
    if (!pl) return;
    var tr = (pl.tracks || [])[ti];
    if (!tr) return;
    profileApiGet('get-lists').then(function(res){
        if (!res.ok) { muAlert((res.data && res.data.error) || 'Error obteniendo el perfil'); return; }
        var lists = res.data || {};
        /* get-lists devuelve items propios + compartidos conmigo (con
           sharedFrom). Replico el patrón de perfil.php: opero solo sobre
           los propios — los compartidos viven en la tabla de collab y
           el server los respeta. */
        var music = (Array.isArray(lists.music) ? lists.music : [])
            .filter(function(m){ return m && !m.sharedFrom; });
        /* Evita duplicar la canción si ya está en el perfil (por ytId). */
        if (tr.videoId && music.some(function(m){ return m && m.ytId && m.ytId === tr.videoId; })) {
            muAlert('"' + (tr.title || tr.videoId) + '" ya está en tu perfil');
            return;
        }
        music.push({
            id:       'music_' + Date.now(),
            type:     'song',
            title:    tr.title  || tr.videoId || 'Canción',
            artist:   tr.artist || '',
            image:    tr.videoId ? ('https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg') : '',
            featured: false,
            ytId:     tr.videoId || ''
        });
        saveProfileMusic(music, function(saved){
            /* Encuentra el item recién insertado (por ytId — único) para
               obtener el id numérico asignado por el server. */
            var savedItem = tr.videoId
                ? saved.find(function(m){ return m && m.ytId === tr.videoId; })
                : null;
            if (!savedItem) {
                muAlert('"' + (tr.title || 'Canción') + '" añadida a tu perfil', 'Añadido');
                return;
            }
            muReviewPrompt('"' + (tr.title || 'Canción') + '" añadida. ¿Quieres dejar una reseña?', function(){
                muOpenReviewEditor(savedItem.id, savedItem.title, savedItem.ytId);
            });
        });
    });
}

/* Prompt Sí/No para preguntar por la reseña tras añadir al perfil
   (replica el flujo del escritorio: showAddedMusicReviewPrompt). */
function muReviewPrompt(message, onYes) {
    var m = muOpenModal({
        title: '¿Añadir reseña?',
        body:
            '<p class="modal-msg">' + esc(message) + '</p>' +
            '<div class="modal-actions">' +
                '<button class="button" type="button" data-act="no">No, gracias</button>' +
                '<button class="button default" type="button" data-act="yes">★ Sí</button>' +
            '</div>'
    });
    m.body.querySelector('[data-act="no"]').addEventListener('click', m.close);
    m.body.querySelector('[data-act="yes"]').addEventListener('click', function(){
        m.close();
        onYes && onYes();
    });
}

/* Editor de reseña: estrellas táctiles (tap en mitad izquierda = media
   estrella), comentario, guardar / eliminar. Re-resuelve el item por
   musicId al guardar por si los IDs cambiaron entre tanto. */
function muOpenReviewEditor(musicId, title, ytId) {
    var current = ytId ? MY_REVIEWS_BY_YTID[ytId] : null;
    var sel = current ? current.stars : 0;
    var bodyHtml =
        '<p class="modal-msg" style="text-align:center;color:var(--text, #000);margin:0 0 4px;">' + esc(title || 'Canción') + '</p>' +
        '<div style="text-align:center;margin:10px 0 14px;">' +
            '<div id="re-stars"></div>' +
            '<div id="re-stars-num" style="font-size:14px;margin-top:6px;color:var(--text, #000);min-height:18px;font-weight:bold;"></div>' +
        '</div>' +
        '<label for="re-comment">Comentario (opcional)</label>' +
        '<textarea id="re-comment" rows="3" style="width:100%;box-sizing:border-box;resize:vertical;font-family:inherit;font-size:13px;" placeholder="Tu opinión..."></textarea>' +
        '<div class="modal-actions">' +
            (current ? '<button class="button" data-act="delete" type="button" style="margin-right:auto;color:var(--error-text, #c00);">🗑 Eliminar</button>' : '') +
            '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
            '<button class="button default" data-act="save" type="button">Guardar</button>' +
        '</div>';
    var m = muOpenModal({ title: '★ Reseña', body: bodyHtml });
    var starsEl   = m.body.querySelector('#re-stars');
    var numEl     = m.body.querySelector('#re-stars-num');
    var commentEl = m.body.querySelector('#re-comment');
    if (current && current.comment) commentEl.value = current.comment;

    function paintStar(span, val, pos) {
        if (val >= pos)        { span.innerHTML = '★'; span.style.clipPath = ''; }
        else if (val >= pos - 0.5) { span.innerHTML = '★'; span.style.clipPath = 'inset(0 50% 0 0)'; }
        else                   { span.innerHTML = '☆'; span.style.clipPath = ''; }
    }
    function drawStars() {
        starsEl.innerHTML = '';
        for (var i = 1; i <= 5; i++) {
            var s = document.createElement('span');
            s.setAttribute('data-star', String(i));
            paintStar(s, sel, i);
            starsEl.appendChild(s);
        }
        numEl.textContent = sel > 0 ? String(sel) : '';
    }
    drawStars();
    /* Tap → setea estrellas. Half-star detection por posición relativa
       al span tocado (usamos el bounding rect para que funcione igual
       en touch synthesized clicks y en clicks normales). */
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
        if (!sel) { muAlert('Selecciona una puntuación'); return; }
        var review = {
            stars:      sel,
            comment:    (commentEl.value || '').trim(),
            reviewedAt: Math.floor(Date.now() / 1000)
        };
        saveReviewToProfile(musicId, review, m.close);
    });
    if (current) {
        m.body.querySelector('[data-act="delete"]').addEventListener('click', function(){
            muConfirm('¿Eliminar la reseña?', function(){
                saveReviewToProfile(musicId, null, m.close);
            });
        });
    }
}

/* ─── AÑADIR CANCIÓN ─────────────────────────────────────────── */
function detectMusicSource(url) {
    if (/youtu\.?be(?:\.com)?\//.test(url) || /^[A-Za-z0-9_-]{11}$/.test(url.trim())) return 'youtube';
    if (/spotify\.com|spotify:track:/.test(url)) return 'spotify';
    if (/tidal\.com/.test(url)) return 'tidal';
    return '';
}
function extractYouTubeId(url) {
    if (/^[A-Za-z0-9_-]{11}$/.test(url.trim())) return url.trim();
    var m = url.match(/(?:v=|youtu\.be\/|embed\/|shorts\/)([A-Za-z0-9_-]{11})/);
    return m ? m[1] : '';
}

function muOpenAddTrack(idx) {
    var pl = PLAYLISTS[idx];
    if (!pl) return;
    var m = muOpenModal({
        title: 'Añadir canción a ' + (pl.name || ''),
        body:
            '<label for="at-url">URL de YouTube, Spotify o Tidal</label>' +
            '<input type="url" id="at-url" placeholder="https://..." autofocus>' +
            '<div id="at-preview"></div>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                '<button class="button" data-act="fetch" type="button">Buscar</button>' +
                '<button class="button default" data-act="add" type="button" disabled>Añadir</button>' +
            '</div>'
    });
    var inputEl = m.body.querySelector('#at-url');
    var previewEl = m.body.querySelector('#at-preview');
    var addBtn = m.body.querySelector('[data-act="add"]');
    var fetchBtn = m.body.querySelector('[data-act="fetch"]');
    var track = null;

    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    fetchBtn.addEventListener('click', function(){
        var url = (inputEl.value || '').trim();
        if (!url) return;
        var src = detectMusicSource(url);
        previewEl.innerHTML = '<div class="modal-loading">Buscando…</div>';
        addBtn.disabled = true;
        track = null;

        function done(t){
            if (!t || !t.videoId) {
                previewEl.innerHTML = '<div class="modal-err">No se pudo obtener la canción</div>';
                return;
            }
            track = t;
            previewEl.innerHTML =
                '<div class="mu-modal-list" style="margin-top:8px;">' +
                  '<div class="mu-modal-list-item">' +
                    '<img class="item-icon" src="https://i.ytimg.com/vi/' + esc(t.videoId) + '/mqdefault.jpg" alt="">' +
                    '<div class="item-main"><strong>' + esc(t.title) + '</strong>' +
                        (t.artist ? '<br><span style="color:var(--text-faint, #666);font-size:11px;">' + esc(t.artist) + '</span>' : '') +
                    '</div>' +
                  '</div>' +
                '</div>';
            addBtn.disabled = false;
        }
        function fail(err){
            previewEl.innerHTML = '<div class="modal-err">' + esc(err || 'Error obteniendo metadata') + '</div>';
        }

        if (src === 'youtube') {
            var vid = extractYouTubeId(url);
            if (!vid) return fail('URL de YouTube no reconocida');
            Promise.all([
                apiGet('yt-title',    { id: vid }),
                apiGet('yt-duration', { id: vid })
            ]).then(function(rs){
                if (!rs[0].ok) return fail(rs[0].data.error || 'No se pudo obtener título');
                done({
                    videoId:  vid,
                    title:    rs[0].data.title || '',
                    artist:   rs[0].data.author || '',
                    duration: rs[1].ok ? (rs[1].data.duration || 0) : 0
                });
            });
        } else if (src === 'spotify') {
            apiGet('spotify-track', { url: url }).then(function(r){
                if (!r.ok) return fail(r.data.error || 'Error con Spotify');
                done(r.data);
            });
        } else if (src === 'tidal') {
            apiGet('tidal-track', { url: url }).then(function(r){
                if (!r.ok) return fail(r.data.error || 'Error con Tidal');
                done(r.data);
            });
        } else {
            fail('Origen no reconocido (YouTube/Spotify/Tidal)');
        }
    });

    addBtn.addEventListener('click', function(){
        if (!track) return;
        track.addedBy = ME_LABEL;
        var newTracks = (pl.tracks || []).slice();
        newTracks.push(track);
        var payload = { id: pl.id, name: pl.name, tracks: newTracks };
        if (pl.sharedFrom) payload.sharedFrom = pl.sharedFrom;
        apiPost('save-playlist-item', payload).then(function(res){
            m.close();
            if (!res.ok) { muAlert((res.data && res.data.error) || 'Error al añadir'); return; }
            loadPlaylists();
        });
    });
}

/* ─── IMPORTAR PLAYLIST ───────────────────────────────────────────
   Primero un selector: Spotify (CSV de exportify.net) o Tidal/YouTube
   (URL). Cada opción abre su modal específico — Spotify no expone
   playlists públicas vía URL, así que su única vía es CSV. */
document.getElementById('mu-btn-import').addEventListener('click', function(){
    var items = [
        { act: 'spotify', icon: '🟢', label: 'Spotify',          sub: 'Sube un CSV de exportify.net' },
        { act: 'url',     icon: '🔗', label: 'Tidal / YouTube',  sub: 'Pega la URL de la playlist' }
    ];
    var bodyHtml = '<p class="modal-msg">¿De dónde quieres importar?</p>';
    bodyHtml += '<div class="mu-modal-list">';
    items.forEach(function(it){
        bodyHtml += '<div class="mu-modal-list-item" data-act="' + it.act + '" style="padding:10px 8px;">' +
                      '<div class="item-icon" style="background:transparent;color:inherit;font-size:18px;">' + it.icon + '</div>' +
                      '<div class="item-main">' +
                        '<div style="font-weight:bold;font-size:13px;">' + esc(it.label) + '</div>' +
                        '<div style="font-size:11px;color:var(--text-faint, #666);">' + esc(it.sub) + '</div>' +
                      '</div>' +
                    '</div>';
    });
    bodyHtml += '</div>';
    bodyHtml += '<div class="modal-actions"><button class="button" data-act="cancel" type="button">Cerrar</button></div>';

    var m = muOpenModal({ title: '⤓ Importar playlist', body: bodyHtml });
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    m.body.querySelectorAll('.mu-modal-list-item').forEach(function(el){
        el.addEventListener('click', function(){
            var act = el.dataset.act;
            m.close();
            if (act === 'spotify') muOpenImportSpotify();
            if (act === 'url')     muOpenImportUrl();
        });
    });
});

/* Crea una playlist nueva con los tracks ya resueltos. Helper compartido
   entre los dos flujos de import. */
function muCreateImportedPlaylist(name, tracks, onDone) {
    tracks.forEach(function(t){ t.addedBy = ME_LABEL; });
    apiPost('save-playlist-item', { id: 'pl_' + Date.now(), name: name, tracks: tracks }).then(function(res){
        if (!res.ok) { muAlert((res.data && res.data.error) || 'Error al guardar'); onDone && onDone(false); return; }
        loadPlaylists();
        onDone && onDone(true);
    });
}

/* Importar desde Tidal o YouTube por URL. */
function muOpenImportUrl() {
    var m = muOpenModal({
        title: 'Importar de Tidal / YouTube',
        body:
            '<p class="modal-msg">Pega la URL de una playlist de YouTube o Tidal.</p>' +
            '<label for="im-url">URL</label>' +
            '<input type="url" id="im-url" placeholder="https://..." autofocus>' +
            '<div id="im-status"></div>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                '<button class="button default" data-act="ok" type="button">Importar</button>' +
            '</div>'
    });
    var inputEl  = m.body.querySelector('#im-url');
    var statusEl = m.body.querySelector('#im-status');
    var btnOk    = m.body.querySelector('[data-act="ok"]');
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);

    btnOk.addEventListener('click', function(){
        var url = (inputEl.value || '').trim();
        if (!url) return;
        btnOk.disabled = true;
        var src = /tidal\.com/.test(url) ? 'tidal' : 'youtube';
        statusEl.innerHTML = '<div class="modal-loading">Importando…</div>';

        function done(success) {
            if (success) m.close();
            else btnOk.disabled = false;
        }
        function resolveBatch(tracks, plName) {
            statusEl.innerHTML = '<div class="modal-loading">Buscando ' + tracks.length + ' canciones en YouTube…</div>';
            apiPost('yt-search-batch', { tracks: tracks }).then(function(r){
                if (!r.ok) { statusEl.innerHTML = '<div class="modal-err">' + esc(r.data.error || 'Error en búsqueda') + '</div>'; btnOk.disabled = false; return; }
                muCreateImportedPlaylist(plName, r.data.tracks || [], done);
            });
        }
        if (src === 'tidal') {
            apiPost('tidal-playlist', { url: url }).then(function(r){
                if (!r.ok) { statusEl.innerHTML = '<div class="modal-err">' + esc(r.data.error || 'Error con Tidal') + '</div>'; btnOk.disabled = false; return; }
                resolveBatch(r.data.tracks || [], r.data.name || 'Importada de Tidal');
            });
        } else {
            apiPost('import-playlist', { url: url }).then(function(r){
                if (!r.ok) { statusEl.innerHTML = '<div class="modal-err">' + esc(r.data.error || 'Error con YouTube') + '</div>'; btnOk.disabled = false; return; }
                /* import-playlist ya devuelve videoIds resueltos. */
                var tracks = (r.data.tracks || []).map(function(t){ t.addedBy = ME_LABEL; return t; });
                muCreateImportedPlaylist('Importada de YouTube', tracks, done);
            });
        }
    });
}

/* Importar desde Spotify vía CSV de exportify.net. Replica el flujo del
   escritorio: el CSV trae (title, artist) y resolvemos a videoIds de
   YouTube con yt-search-batch (por lotes de 10 con barra de progreso). */
function muOpenImportSpotify() {
    var m = muOpenModal({
        title: 'Importar de Spotify',
        body:
            '<p class="modal-msg">Exporta tu playlist desde <strong>exportify.net</strong> y selecciona el archivo CSV.</p>' +
            '<label for="im-sp-name">Nombre de la playlist</label>' +
            '<input type="text" id="im-sp-name" maxlength="100" placeholder="Importada de Spotify">' +
            '<label for="im-sp-file" style="margin-top:8px;">Archivo CSV</label>' +
            '<input type="file" id="im-sp-file" accept=".csv,text/csv">' +
            '<div id="im-sp-status" style="font-size:11px;color:var(--text-faint, #666);margin-top:6px;"></div>' +
            '<div id="im-sp-progress" style="display:none;margin-top:8px;">' +
                '<div style="background:var(--input-bg, #fff);border:1px inset var(--bezel-dark-2, grey);height:14px;position:relative;">' +
                    '<div id="im-sp-progress-fill" style="background:var(--accent, #000080);height:100%;width:0;transition:width 0.2s;"></div>' +
                '</div>' +
                '<div id="im-sp-progress-text" style="font-size:11px;margin-top:3px;color:var(--text, #000);"></div>' +
            '</div>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                '<button class="button default" data-act="ok" type="button">Importar</button>' +
            '</div>'
    });
    var nameEl    = m.body.querySelector('#im-sp-name');
    var fileEl    = m.body.querySelector('#im-sp-file');
    var statusEl  = m.body.querySelector('#im-sp-status');
    var progEl    = m.body.querySelector('#im-sp-progress');
    var progFill  = m.body.querySelector('#im-sp-progress-fill');
    var progText  = m.body.querySelector('#im-sp-progress-text');
    var btnOk     = m.body.querySelector('[data-act="ok"]');
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);

    /* Parse RFC-4180-ish de la cabecera + filas de exportify (mismo
       algoritmo que reproductor.php). Solo necesitamos 'Track Name' y
       'Artist Name(s)'. */
    function parseExportifyCSV(raw) {
        function parseLine(line) {
            var cols = [], cur = '', inQ = false;
            for (var i = 0; i < line.length; i++) {
                var c = line[i];
                if (c === '"') { inQ = !inQ; }
                else if (c === ',' && !inQ) { cols.push(cur); cur = ''; }
                else { cur += c; }
            }
            cols.push(cur);
            return cols;
        }
        var lines  = raw.split('\n');
        if (!lines.length) return [];
        var header = parseLine(lines[0]);
        var tiIdx  = header.indexOf('Track Name');
        var arIdx  = header.indexOf('Artist Name(s)');
        if (tiIdx === -1) return [];
        var tracks = [];
        for (var i = 1; i < lines.length; i++) {
            if (!lines[i].trim()) continue;
            var cols   = parseLine(lines[i]);
            var title  = (cols[tiIdx] || '').trim();
            var artist = arIdx !== -1 ? (cols[arIdx] || '').trim() : '';
            if (title) tracks.push({ title: title, artist: artist, duration: 0 });
        }
        return tracks;
    }

    btnOk.addEventListener('click', function(){
        var file = fileEl.files && fileEl.files[0];
        if (!file) { statusEl.textContent = 'Selecciona un archivo CSV.'; return; }
        var plName = (nameEl.value || '').trim() || 'Importada de Spotify';
        btnOk.disabled = true;
        statusEl.textContent = 'Leyendo archivo…';
        var reader = new FileReader();
        reader.onload = function(e){
            var tracks = parseExportifyCSV(e.target.result);
            if (!tracks.length) {
                statusEl.textContent = 'No se encontraron canciones (¿es un CSV de exportify.net?).';
                btnOk.disabled = false;
                return;
            }
            statusEl.textContent = '';
            progEl.style.display = 'block';
            progFill.style.width = '0%';
            progText.textContent = '0 / ' + tracks.length;

            /* Resuelve en lotes de 10. Los lotes se concatenan en
               resolved[] y al terminar guardamos la playlist. */
            var BATCH = 10;
            var resolved = [];
            var idx = 0;
            function nextBatch() {
                if (idx >= tracks.length) {
                    progText.textContent = 'Guardando playlist…';
                    muCreateImportedPlaylist(plName, resolved, function(ok){
                        if (ok) m.close();
                        else btnOk.disabled = false;
                    });
                    return;
                }
                var slice = tracks.slice(idx, idx + BATCH);
                apiPost('yt-search-batch', { tracks: slice }).then(function(r){
                    if (!r.ok) {
                        statusEl.textContent = r.data && r.data.error ? r.data.error : 'Error en la búsqueda';
                        btnOk.disabled = false;
                        progEl.style.display = 'none';
                        return;
                    }
                    var found = r.data.tracks || [];
                    found.forEach(function(t){ if (t && t.videoId) resolved.push(t); });
                    idx += slice.length;
                    var pct = Math.round((idx / tracks.length) * 100);
                    progFill.style.width = pct + '%';
                    progText.textContent = idx + ' / ' + tracks.length + ' · ' + resolved.length + ' encontradas';
                    nextBatch();
                });
            }
            nextBatch();
        };
        reader.onerror = function(){
            statusEl.textContent = 'No se pudo leer el archivo';
            btnOk.disabled = false;
        };
        reader.readAsText(file);
    });
}

/* ─── COLABORADORES ─────────────────────────────────────────────── */
function muOpenCollabs(idx) {
    var pl = PLAYLISTS[idx];
    if (!pl) return;
    var collabs = pl.collaborators || [];
    var others = USERS.filter(function(u){ return u.key !== ME_KEY; });

    var bodyHtml = '<p class="modal-msg">Gestiona quién puede editar "' + esc(pl.name) + '".</p>';
    bodyHtml += '<div class="mu-modal-list">';
    if (!others.length) bodyHtml += '<div class="modal-msg" style="padding:8px;">(no hay otros usuarios)</div>';
    others.forEach(function(u){
        var isCollab = collabs.indexOf(u.key) >= 0;
        var img = u.img;
        bodyHtml += '<div class="mu-modal-list-item">' +
                      (img
                        ? '<img class="item-icon" src="' + esc(img) + '" alt="">'
                        : '<div class="item-icon">' + esc(u.label.substring(0,1)) + '</div>') +
                      '<div class="item-main">' + esc(u.label) + '</div>' +
                      (isCollab
                        ? '<button class="button item-action" data-act="remove" data-key="' + esc(u.key) + '" type="button">Quitar</button>'
                        : '<button class="button item-action" data-act="invite" data-key="' + esc(u.key) + '" type="button">Invitar</button>') +
                    '</div>';
    });
    bodyHtml += '</div>';
    bodyHtml += '<div class="modal-actions"><button class="button" data-act="close" type="button">Cerrar</button></div>';

    var m = muOpenModal({ title: 'Colaboradores', body: bodyHtml });
    m.body.querySelector('[data-act="close"]').addEventListener('click', m.close);
    m.body.querySelectorAll('[data-act="invite"]').forEach(function(b){
        b.addEventListener('click', function(){
            apiPost('invite-collaborator', { playlistId: pl.id, toUser: b.dataset.key }).then(function(r){
                if (!r.ok) { muAlert((r.data && r.data.error) || 'Error al invitar'); return; }
                m.close();
                muAlert('Invitación enviada a ' + userLabelByKey(b.dataset.key));
            });
        });
    });
    m.body.querySelectorAll('[data-act="remove"]').forEach(function(b){
        b.addEventListener('click', function(){
            muConfirm('¿Quitar a ' + userLabelByKey(b.dataset.key) + ' de colaboradores?', function(){
                apiPost('remove-collaborator', { playlistId: pl.id, collaborator: b.dataset.key }).then(function(r){
                    if (!r.ok) { muAlert((r.data && r.data.error) || 'Error al quitar'); return; }
                    m.close();
                    loadPlaylists();
                });
            });
        });
    });
}

/* ─── ELIMINAR / ABANDONAR ─────────────────────────────────────── */
function muDeletePlaylist(idx) {
    var pl = PLAYLISTS[idx];
    if (!pl) return;
    apiPost('delete-playlist', { id: pl.id }).then(function(r){
        if (!r.ok) { muAlert((r.data && r.data.error) || 'Error al eliminar'); return; }
        loadPlaylists();
    });
}
function muLeavePlaylist(idx) {
    var pl = PLAYLISTS[idx];
    if (!pl || !pl.sharedFrom) return;
    apiPost('leave-playlist', { id: pl.id, sharedFrom: pl.sharedFrom }).then(function(r){
        if (!r.ok) { muAlert((r.data && r.data.error) || 'Error al abandonar'); return; }
        loadPlaylists();
    });
}

/* ─── Bootstrap ─────────────────────────────────────────────────── */
loadPlaylists();
/* En paralelo: música del perfil para tener el mapa de reseñas listo
   y mostrar las estrellas en los tracks reseñados (re-renderiza solo
   si las playlists ya terminaron de cargar). */
loadProfileMusic();
</script>

</body>
</html>
