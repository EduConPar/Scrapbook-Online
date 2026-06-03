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

/* Helper para resolver el avatar de un usuario por label. */
$resolveAvatar = function($label) {
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$label);
    foreach (['png','jpg','jpeg','gif'] as $ext) {
        if (file_exists(dirname(__DIR__, 2) . "/assets/img/{$safe}.{$ext}")) {
            return "../../assets/img/{$safe}.{$ext}";
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
require_once dirname(__DIR__, 2) . '/assets/themes/theme-helpers.php';
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
    if ($activeThemeCss !== '' && !file_exists(dirname(__DIR__, 2) . '/' . $activeThemeCss)) {
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
        if (!sa) window.location.replace('../../mobile-landing.php');
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
    <link rel="icon" href="../../assets/img/mobile/icon.png" type="image/png">
    <!-- Mismo stack que mobile.php para Win98 + tema del usuario -->
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

        /* Botón de play en el header de cada playlist. Inicia la
           reproducción desde el primer track al pulsarlo. Si la
           playlist está vacía queda en disabled. */
        .mu-pl-play {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            min-width: 36px;
            min-height: 36px;
            padding: 0;
            margin: 0 4px 0 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text, #000);
            font-family: inherit;
        }
        .mu-pl-play svg {
            width: 16px;
            height: 16px;
            display: block;
        }
        .mu-pl-play:disabled {
            color: var(--text-faint, #888);
            cursor: not-allowed;
        }
        .mu-pl-head:active .mu-pl-play { color: var(--accent-text, #fff); }

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
            /* Bottom = statusbar_top exacto, sin gap ni overlap.
               statusbar_height (22px) + window-body padding-bottom
               (max 8px o env safe-area). */
            bottom: calc(22px + max(8px, env(safe-area-inset-bottom, 0px)));
            background: var(--btn-bg, silver);
            border-top: 2px solid var(--accent, #000080);
            padding: 8px 12px;
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
        /* Visualmente sugerir que el mini-player es tappable. */
        .mu-player-info, .mu-player-thumb {
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
        }

        /* ════════════════════════════════════════════════════════════
           NOW PLAYING — vista fullscreen con vinilo girando
           Estética Win98 con la paleta del tema del usuario. El splash
           del thumbnail blureado sigue presente pero atenuado, para
           que pinte sobre el fondo sin tapar la silver del tema.
           ════════════════════════════════════════════════════════════ */
        .mu-full {
            /* ── Solo el efecto LCD/glow del texto vive aquí ──
               Los FONDOS los heredamos del tema activo (var(--win-bg),
               var(--input-bg), etc.) como cualquier otra app. La
               "vibe LCD" sale del glow del acento sobre el chrome
               Win98 normal + scanlines globales sutiles. */
            --lcd:      var(--accent, #00ff88);
            --lcd-soft: color-mix(in srgb, var(--accent, #00ff88) 65%, transparent);
            --lcd-dim:  color-mix(in srgb, var(--accent, #00ff88) 35%, transparent);
            --lcd-veil: color-mix(in srgb, var(--accent, #00ff88) 5%, transparent);

            position: fixed;
            inset: 0;
            z-index: 300;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            overflow: hidden;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .mu-full.visible { transform: translateY(0); }
        /* Scanlines globales sobre toda la ventana. mix-blend-mode da
           ese tinte CRT auténtico sin tapar el texto. */
        .mu-full::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                to bottom,
                var(--lcd-veil) 0,
                var(--lcd-veil) 1px,
                transparent 1px,
                transparent 3px
            );
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 999;
        }
        /* Splash desactivado: el fondo del fullscreen usa solo el tema
           del usuario (var(--win-bg) en .mu-full). El elemento se queda
           en el DOM por si en el futuro quieres reactivarlo. */
        .mu-full-splash { display: none; }
        /* La ventana Win98 ocupa todo el viewport y vive por encima del
           splash. Body translúcido para que la paleta cale a través. */
        .mu-full-window {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            background: transparent;
            box-shadow: none;
            padding: 3px;
        }
        .mu-full-window > .title-bar { flex-shrink: 0; }
        .mu-full-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            margin: 0;
            padding: 10px 12px;
            background: transparent;
            overflow: hidden;
            gap: 10px;
        }

        /* Display sunken: el vinilo vive dentro de un panel hundido
           Win98 (como la pantalla de un reproductor). Le da volumen y
           "marco" sin tener que tocar el propio disco. */
        .mu-full-display {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            min-height: 0;
            background: var(--input-bg, #000);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a);
            /* Brillo radial sutil que sugiere "pantalla de tubo". */
            background-image:
                radial-gradient(ellipse at center, rgba(0,0,0,0) 40%, rgba(0,0,0,0.35) 100%);
            position: relative;
            overflow: hidden;
        }
        /* Scanlines retro encima del display. */
        .mu-full-display::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(
                    to bottom,
                    rgba(255,255,255,0.025) 0,
                    rgba(255,255,255,0.025) 1px,
                    transparent 1px,
                    transparent 3px
                );
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
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background:
                repeating-radial-gradient(circle at center,
                    rgba(255,255,255,0.04) 0,
                    rgba(255,255,255,0.04) 1px,
                    transparent 1px,
                    transparent 5px),
                radial-gradient(circle at center,
                    #1f1f1f 0%,
                    #0a0a0a 70%,
                    #050505 100%);
            animation: mu-spin 6s linear infinite;
        }
        .mu-vinyl.paused { animation-play-state: paused; }
        @keyframes mu-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        .mu-vinyl::after {
            content: '';
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            box-shadow:
                inset 12px -20px 30px rgba(255,255,255,0.04),
                inset -6px 8px 18px rgba(0,0,0,0.6);
            pointer-events: none;
        }
        /* Etiqueta central — círculo con el thumbnail (mqdefault, 16:9
           sin letterbox). 36% del diámetro: tamaño clásico de etiqueta
           de vinilo, el cover del thumbnail rellena sin bandas porque
           la fuente ya viene 16:9. */
        .mu-vinyl-label {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 36%;
            height: 36%;
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
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: var(--accent-text, #fff);
        }
        .mu-vinyl-hole {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 3.2%;
            height: 3.2%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: #050505;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.18);
            z-index: 2;
        }

        /* Info — display LCD/holograma sobre fondo negro. Misma estética
           que antes (glow + scanlines + blink) pero el color de luz lo
           dicta `var(--accent)` del tema activo en vez del verde fijo.
           Las dos custom properties internas (--lcd y --lcd-soft) se
           recalculan con color-mix si el tema cambia. */
        .mu-full-info {
            --lcd:      var(--accent, #00ff88);
            --lcd-soft: color-mix(in srgb, var(--accent, #00ff88) 65%, transparent);
            --lcd-veil: color-mix(in srgb, var(--accent, #00ff88) 5%, transparent);
            display: flex;
            align-items: center;
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
        /* Scanlines retro tintadas con el acento del tema. */
        .mu-full-info::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                to bottom,
                var(--lcd-veil) 0,
                var(--lcd-veil) 1px,
                transparent 1px,
                transparent 3px
            );
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
            flex: 1;
            min-width: 0;
            text-align: left;
        }
        /* Wrappers con altura fija + clipping horizontal. La altura es
           1 línea exacta (line-height × font-size). El texto vive en
           un <span> hijo inline-block que puede desbordar y se anima
           horizontalmente si excede el ancho del wrap. */
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
        /* Track: contenedor que se anima horizontalmente. En reposo es
           inline-block con un único hijo (el span del texto). Cuando
           el JS detecta overflow añade .marquee y entonces pasa a
           inline-flex con un gap entre las dos copias del texto. */
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

        /* Marquee continuo tipo ticker: el track recorre una distancia
           igual a (ancho del texto + gap), llevando el clon a la
           posición donde estaba el original → loop sin "vuelta atrás",
           hay un espacio en blanco entre cada pasada del título. */
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

        /* Progress bar tipo cassette deck: barra hundida con relleno
           accent + tiempos VT323 a los lados. */
        .mu-full-progress-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 4px;
            flex-shrink: 0;
        }
        .mu-full-time {
            font-family: 'VT323', monospace;
            font-size: 14px;
            color: var(--text, #000);
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
            background:
                linear-gradient(
                    90deg,
                    var(--accent, #000080) 0%,
                    var(--accent-deep, var(--accent, #1084d0)) 100%
                );
            transition: width 0.25s linear;
        }

        /* Controles LCD: dark + glow accent.
           98.css mete colores hardcoded en .button, hay que override-arlos
           con !important. El text-shadow `0 0 #222` lo borra con un glow
           del color del tema. */
        .mu-full-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            padding: 4px 0 2px;
        }
        /* Botones Win98 normales del tema — sin overrides de fondo.
           Solo controlamos tamaño y centrado del icono SVG/CSS. */
        .mu-full-btn {
            min-width: 60px;
            min-height: 52px;
            padding: 0 12px;
            font-size: 20px;
            font-family: inherit;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text, #000);
        }
        .mu-full-btn.primary {
            min-width: 88px;
            min-height: 60px;
            font-size: 26px;
        }

        /* ── Iconos play/pausa dibujados con CSS ──
           Evita la lotería de renderizado de ⏸/▶ como emoji con fondo
           en algunos dispositivos. Forma siempre centrada y un solo
           color (currentColor) que sigue al `color` del botón. */
        .mu-icon-play,
        .mu-icon-pause {
            display: inline-block;
            color: inherit;
            line-height: 0;
        }
        .mu-icon-pause { display: none; }
        .is-playing > .mu-icon-play   { display: none; }
        .is-playing > .mu-icon-pause  { display: inline-flex; }

        /* Triángulo play: CSS borders. */
        #mu-toggle .mu-icon-play {
            width: 0; height: 0;
            border-style: solid;
            border-width: 6px 0 6px 10px;
            border-color: transparent transparent transparent currentColor;
            margin-left: 2px;            /* compensación óptica del triángulo */
        }
        #mu-full-toggle .mu-icon-play {
            width: 0; height: 0;
            border-style: solid;
            border-width: 11px 0 11px 18px;
            border-color: transparent transparent transparent currentColor;
            margin-left: 4px;            /* compensación óptica */
        }

        /* Dos barras de pausa: pseudo-elementos. */
        .mu-icon-pause {
            align-items: center;
            justify-content: center;
        }
        .mu-icon-pause::before,
        .mu-icon-pause::after {
            content: '';
            display: block;
            background: currentColor;
        }
        #mu-toggle .mu-icon-pause::before,
        #mu-toggle .mu-icon-pause::after {
            width: 3px;
            height: 11px;
            margin: 0 2px;
        }
        #mu-full-toggle .mu-icon-pause::before,
        #mu-full-toggle .mu-icon-pause::after {
            width: 6px;
            height: 22px;
            margin: 0 3px;
        }

        /* Tiempo del progreso con glow LCD (sigue siendo "pantalla"). */
        .mu-full-time {
            color: var(--lcd) !important;
            text-shadow: 0 0 5px var(--lcd-soft);
        }
        /* Fila de acciones secundarias: shuffle + añadir. Icon-only. */
        .mu-full-extras {
            display: flex;
            justify-content: center;
            align-items: stretch;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .mu-full-extra svg {
            width: 22px;
            height: 22px;
            display: block;
        }
        /* Estado "pulsado" del shuffle — bezel invertido como botón Win98
           apretado, para marcar que el modo aleatorio está activo. */
        .mu-full-extra.is-on {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey) !important;
            color: var(--accent, #000080);
        }

        /* Footer: chevron-down ancho completo que minimiza el fullscreen. */
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text, #000);
            font-family: inherit;
        }
        .mu-full-footer-btn svg {
            width: 22px;
            height: 22px;
            display: block;
        }

        /* ── MODALES WIN98 reutilizables ──
           Overlay + ventana centrada con title-bar + window-body. Pensado
           como bottom-sheet en móvil pero usando estructura Win98. */
        /* ════════════════════════════════════════════════════════════
           LOCK SCREEN — modo reposo del reproductor.
           Estructura: overlay full-viewport con vinilo centrado,
           scanlines LCD y hint deslizable. El opening "mezcla" el
           reproductor con el negro vía transición de opacity sobre
           todo el overlay (su contenido aparece al mismo tiempo que
           el fondo negro tapa el fullscreen subyacente).
           ════════════════════════════════════════════════════════════ */
        .mu-lock {
            position: fixed;
            inset: 0;
            z-index: 350;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            /* Paleta tipo terminal — fondo negro + verde fosforescente
               fijo. NO usa el acento del tema porque el modo reposo
               siempre tiene look CRT-monocromo. Los tokens LCD locales
               sobrescriben los del fullscreen (que sí siguen al tema). */
            --lock-bg:        #0a0a0a;
            --lock-surface:   #050505;
            --lock-bezel-hi:  #2a2a2a;     /* gris neutro para los bezels claros */
            --lock-bezel-lo:  #1a1a1a;
            --lock-bezel-dk:  #000;
            /* Override LCD tokens: verde terminal más apagado. Menos
               saturación que el #00ff88 anterior — se sigue leyendo
               como fósforo CRT pero descansa más la vista. */
            --lcd:      #22cc66;
            --lcd-soft: rgba(34, 204, 102, 0.65);
            --lcd-dim:  rgba(34, 204, 102, 0.35);
            --lcd-veil: rgba(34, 204, 102, 0.06);
            --accent:      #22cc66;
            --accent-deep: #117740;
            --accent-text: #0d1f12;
            background: var(--lock-bg);
            color: var(--lcd);
            transition: opacity 1.2s ease, visibility 0s linear 1.2s;
            -webkit-tap-highlight-color: transparent;
        }
        .mu-lock.visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transition: opacity 1.2s ease, visibility 0s linear 0s;
        }

        /* Filtro LCD del lock: solo CRT scanlines en blanco grisáceo,
           sin tinte verde sobre el fondo. El verde queda RESERVADO
           para el texto/HUD; el background se mantiene gris-negro
           neutro. La viñeta también en gris para reforzar la
           sensación de "tubo de rayos catódicos" sin colorear. */
        .mu-lock::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center,
                rgba(0, 0, 0, 0) 50%,
                rgba(0, 0, 0, 0.35) 100%);
            pointer-events: none;
            z-index: 1;
        }
        .mu-lock::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(to bottom,
                rgba(255, 255, 255, 0.04) 0,
                rgba(255, 255, 255, 0.04) 1px,
                transparent 1px,
                transparent 3px);
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 1;
        }

        /* Ahorro batería: cuando el lock está visible, las animaciones
           del fullscreen subyacente se pausan. Como el lock es opaco y
           lo tapa por completo, no se ve nada → es absurdo gastar GPU
           rotando un vinilo invisible. */
        .mu-full.behind-lock,
        .mu-full.behind-lock *,
        .mu-full.behind-lock *::before,
        .mu-full.behind-lock *::after {
            animation-play-state: paused !important;
        }

        /* Layout EXACTO al .mu-full-window: title-bar arriba, body
           con padding y gap idénticos, footer abajo. La diferencia
           es que aquí los huecos del header, controles, extras y
           footer son divs vacíos que reservan el espacio para que
           vinilo/info/progress queden en la misma posición vertical
           que en el fullscreen. */
        .mu-lock {
            display: flex;
            flex-direction: column;
            /* Replica el padding interno del .mu-full-window (3px en
               cada lado) para que el title-bar/body/footer empiecen
               en las MISMAS coordenadas que en el fullscreen. Sin
               este padding la HUD quedaba 3px más arriba. */
            padding: 3px;
            box-sizing: border-box;
        }

        /* Hueco del title-bar: misma altura mínima (22px). Fondo
           transparente para que el filtro LCD (::before/::after) se
           vea a través — si fuera opaco quedaría como una franja
           negra sin tintar mientras el resto sí tiene el tinte. */
        .mu-lock-titlebar {
            flex-shrink: 0;
            min-height: 22px;
            background: transparent;
            position: relative;
            z-index: 2;
        }

        /* Body con misma estructura interna que .mu-full-body. */
        .mu-lock-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            padding: 10px 12px;
            gap: 10px;
            background: transparent;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }
        /* Huecos vacíos donde estaban los controles y los extras.
           min-height EXACTO del fullscreen: controls primary 60px +
           padding (4+2); extras button 36px + padding (2+2). Sin
           padding propio en el hole → el min-height define la altura. */
        .mu-lock-controls-hole {
            flex-shrink: 0;
            min-height: 66px;
        }
        .mu-lock-extras-hole {
            flex-shrink: 0;
            min-height: 40px;
        }
        /* Hueco del footer: misma altura que .mu-full-footer-btn
           (min-height 32px) + safe-area + margen del .mu-full-footer.
           Transparente para que el LCD también tinte aquí. */
        .mu-lock-footer-hole {
            flex-shrink: 0;
            min-height: 32px;
            margin: 0 1px 1px;
            padding-bottom: env(safe-area-inset-bottom);
            background: transparent;
            position: relative;
            z-index: 2;
        }

        /* Display sunken con vinilo — replica EXACTA de .mu-full-display
           pero con la paleta dark. Incluye el radial gradient CRT y
           las scanlines. */
        .mu-lock-display {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            min-height: 0;
            background: var(--lock-surface);
            background-image:
                radial-gradient(ellipse at center, rgba(0,0,0,0) 40%, rgba(0,0,0,0.35) 100%);
            box-shadow:
                inset -1px -1px var(--lock-bezel-hi),
                inset  1px  1px var(--lock-bezel-dk),
                inset -2px -2px var(--lock-bezel-lo),
                inset  2px  2px var(--lock-bezel-dk);
            position: relative;
            overflow: hidden;
        }
        .mu-lock-display::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(to bottom,
                rgba(255,255,255,0.025) 0,
                rgba(255,255,255,0.025) 1px,
                transparent 1px,
                transparent 3px);
            pointer-events: none;
        }
        /* Vinilo wrap: spec IDÉNTICO al .mu-vinyl-wrap del fullscreen. */
        .mu-lock-vinyl-wrap {
            width: min(72vw, 300px);
            max-height: 100%;
            aspect-ratio: 1;
            position: relative;
            filter: drop-shadow(0 10px 18px rgba(0,0,0,0.65));
        }

        /* Info LCD — clon del .mu-full-info, mismos glow + scanlines
           pero sobre tokens dark. */
        .mu-lock-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--lock-surface);
            color: var(--lcd);
            box-shadow:
                inset -1px -1px var(--lock-bezel-hi),
                inset  1px  1px var(--lock-bezel-dk),
                inset -2px -2px var(--lock-bezel-lo),
                inset  2px  2px var(--lock-bezel-dk);
            box-sizing: border-box;
            flex-shrink: 0;
            min-height: 50px;
            position: relative;
            overflow: hidden;
        }
        .mu-lock-info-marker {
            font-family: 'VT323', monospace;
            font-size: 24px;
            color: var(--lcd);
            text-shadow: 0 0 8px var(--lcd-soft);
            line-height: 1;
            flex-shrink: 0;
            animation: mu-blink 1s steps(2) infinite;
        }
        .mu-lock-info-text {
            flex: 1;
            min-width: 0;
            text-align: left;
        }

        /* Title/artist con misma estructura wrap+track+span para
           reusar el sistema de marquee. */
        .mu-lock-title-wrap {
            font-size: clamp(18px, 5.5vw, 24px);
            line-height: 1.15;
            height: 1.15em;
            overflow: hidden;
            white-space: nowrap;
            margin: 0 0 2px;
        }
        .mu-lock-artist-wrap {
            font-size: clamp(13px, 3.8vw, 16px);
            line-height: 1.3;
            height: 1.3em;
            overflow: hidden;
            white-space: nowrap;
        }
        .mu-lock-title-track,
        .mu-lock-artist-track {
            display: inline-block;
            will-change: transform;
        }
        .mu-lock-title,
        .mu-lock-artist {
            display: inline-block;
            font-family: 'VT323', monospace;
            letter-spacing: 1px;
            text-shadow: 0 0 6px var(--lcd-soft);
            line-height: inherit;
        }
        .mu-lock-title  { color: var(--lcd); }
        .mu-lock-artist { color: var(--lcd-soft); }
        .mu-lock-title-wrap.marquee .mu-lock-title-track,
        .mu-lock-artist-wrap.marquee .mu-lock-artist-track {
            display: inline-flex;
            gap: 3em;
            animation: mu-marquee var(--mu-marquee-duration, 12s) linear infinite;
        }

        /* Progress + tiempos — réplica del fullscreen con tokens dark. */
        .mu-lock-progress-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 4px;
            flex-shrink: 0;
        }
        .mu-lock-time {
            font-family: 'VT323', monospace;
            font-size: 14px;
            color: var(--lcd);
            text-shadow: 0 0 5px var(--lcd-soft);
            font-variant-numeric: tabular-nums;
            min-width: 38px;
            text-align: center;
        }
        .mu-lock-progress {
            flex: 1;
            height: 14px;
            background: var(--lock-surface);
            box-shadow:
                inset -1px -1px var(--lock-bezel-hi),
                inset  1px  1px var(--lock-bezel-dk),
                inset -2px -2px var(--lock-bezel-lo),
                inset  2px  2px var(--lock-bezel-dk);
            position: relative;
            overflow: hidden;
            padding: 2px;
            box-sizing: border-box;
        }
        .mu-lock-progress-fill {
            height: 100%;
            width: 0;
            /* Gradiente verde terminal apagado para casar con --lcd. */
            background: linear-gradient(90deg, #0d5530 0%, #22cc66 100%);
            box-shadow: 0 0 8px rgba(34, 204, 102, 0.4);
            transition: width 0.25s linear;
        }

        /* Hint deslizable abajo — el ÚNICO elemento que se mueve con
           el dedo durante el swipe. */
        .mu-lock-hint {
            position: absolute;
            left: 50%;
            bottom: max(28px, env(safe-area-inset-bottom) + 28px);
            transform: translateX(-50%);
            font-family: 'VT323', monospace;
            font-size: 14px;
            letter-spacing: 1.5px;
            color: var(--lcd);
            text-shadow: 0 0 6px var(--lcd-soft);
            opacity: 0.8;
            animation: mu-blink 2s ease-in-out infinite;
            pointer-events: none;
            white-space: nowrap;
            z-index: 2;
            transition: transform 0.3s ease, opacity 0.3s ease;
            will-change: transform, opacity;
        }

        .mu-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            /* z-index 400 → por encima del fullscreen (z 300) para que
               cualquier modal abierto desde el reproductor se vea sobre
               él en lugar de quedar tapado. */
            z-index: 400;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 0;
        }
        /* Icono SVG opcional en el title-bar del modal — hereda el color
           del titlebar-text y se alinea verticalmente con el texto. */
        .mu-modal .title-bar-text {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .mu-modal .title-bar-text svg {
            width: 14px;
            height: 14px;
            display: block;
            color: var(--titlebar-text, #fff);
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
            <button aria-label="Close" onclick="window.location.href='../../mobile.php';"></button>
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
            <a href="../../mobile.php">‹ Menú</a>
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
        <button class="button default mu-btn primary" id="mu-toggle" type="button" aria-label="Play/Pausa">
            <span class="mu-icon-play"  aria-hidden="true"></span>
            <span class="mu-icon-pause" aria-hidden="true"></span>
        </button>
        <button class="button mu-btn" id="mu-next" type="button" aria-label="Siguiente">⏭</button>
    </div>
</div>

<!-- ─── NOW PLAYING fullscreen ────────────────────────────────────
     Ventana Win98 a pantalla completa con vinilo en panel hundido,
     progress bar tipo cassette deck y status bar al pie. -->
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

            <!-- Display sunken con el vinilo -->
            <div class="mu-full-display">
                <div class="mu-vinyl-wrap">
                    <div class="mu-vinyl" id="mu-vinyl">
                        <div class="mu-vinyl-label empty" id="mu-vinyl-label">🎵</div>
                        <div class="mu-vinyl-hole"></div>
                    </div>
                </div>
            </div>

            <!-- Panel de info con LCD verde "tipo CRT".
                 Estructura para marquee continuo tipo ticker:
                 wrap (clipping fijo) → track (animable) → span (texto).
                 Si hay overflow, el JS clona el span dentro del track
                 y la animación scrollea el track una distancia
                 (texto + gap), creando un loop visual sin "vuelta atrás". -->
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

            <!-- Progress bar tipo deck con tiempo a los lados -->
            <div class="mu-full-progress-row">
                <span class="mu-full-time" id="mu-full-time-cur">0:00</span>
                <div class="mu-full-progress" id="mu-full-progress">
                    <div class="mu-full-progress-fill" id="mu-full-progress-fill"></div>
                </div>
                <span class="mu-full-time" id="mu-full-time-tot">0:00</span>
            </div>

            <!-- Controles grandes -->
            <div class="mu-full-controls">
                <button class="button mu-full-btn" id="mu-full-prev" type="button" aria-label="Anterior">⏮</button>
                <button class="button default mu-full-btn primary" id="mu-full-toggle" type="button" aria-label="Play/Pausa">
                    <span class="mu-icon-play"  aria-hidden="true"></span>
                    <span class="mu-icon-pause" aria-hidden="true"></span>
                </button>
                <button class="button mu-full-btn" id="mu-full-next" type="button" aria-label="Siguiente">⏭</button>
            </div>
            <!-- Acciones secundarias: shuffle + añadir canción a otra playlist.
                 SVG inline con stroke=currentColor → monocromo, sin pastilla
                 de emoji, hereda el color del tema. -->
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
        <!-- Footer: chevron-down que minimiza el fullscreen y vuelve a la
             vista de playlists. NO navega fuera del reproductor. -->
        <div class="mu-full-footer">
            <button class="button mu-full-footer-btn" id="mu-full-menu" type="button" aria-label="Cerrar reproductor">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- ─── LOCK SCREEN ────────────────────────────────────────────────
     MISMA distribución vertical que el .mu-full: title-bar arriba,
     body con display/info/progress/controls/extras, footer abajo.
     Diferencia: los huecos del title-bar, controls, extras y footer
     son DIVS VACÍOS — reservan el espacio pero no muestran botones.
     Los colores se sustituyen por la paleta dark/LCD. -->
<div class="mu-lock" id="mu-lock" aria-hidden="true">
    <!-- Hueco del title-bar (vacío, misma altura). -->
    <div class="mu-lock-titlebar"></div>

    <div class="mu-lock-body">
        <!-- Display sunken con vinilo (flex: 1, igual que .mu-full-display). -->
        <div class="mu-lock-display">
            <div class="mu-lock-vinyl-wrap">
                <div class="mu-vinyl mu-lock-vinyl" id="mu-lock-vinyl">
                    <div class="mu-vinyl-label empty" id="mu-lock-vinyl-label">🎵</div>
                    <div class="mu-vinyl-hole"></div>
                </div>
            </div>
        </div>

        <!-- Info LCD: marker + title/artist con marquee. -->
        <div class="mu-lock-info">
            <div class="mu-lock-info-marker">♪</div>
            <div class="mu-lock-info-text">
                <div class="mu-lock-title-wrap" id="mu-lock-title-wrap">
                    <span class="mu-lock-title-track" id="mu-lock-title-track">
                        <span class="mu-lock-title" id="mu-lock-title">—</span>
                    </span>
                </div>
                <div class="mu-lock-artist-wrap" id="mu-lock-artist-wrap">
                    <span class="mu-lock-artist-track" id="mu-lock-artist-track">
                        <span class="mu-lock-artist" id="mu-lock-artist">—</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress + tiempos. -->
        <div class="mu-lock-progress-row">
            <span class="mu-lock-time" id="mu-lock-time-cur">0:00</span>
            <div class="mu-lock-progress">
                <div class="mu-lock-progress-fill" id="mu-lock-progress-fill"></div>
            </div>
            <span class="mu-lock-time" id="mu-lock-time-tot">0:00</span>
        </div>

        <!-- Hueco de los controles (vacío, misma altura que prev/play/next). -->
        <div class="mu-lock-controls-hole"></div>
        <!-- Hueco de la fila extras (vacío). -->
        <div class="mu-lock-extras-hole"></div>
    </div>

    <!-- Hueco del footer (vacío, misma altura que el chevron de cerrar). -->
    <div class="mu-lock-footer-hole"></div>

    <!-- Hint flotante para el swipe (no rompe el layout). -->
    <div class="mu-lock-hint" id="mu-lock-hint">↑  Desliza para desbloquear</div>
</div>

<!-- Host de modales (se rellena dinámicamente desde JS) -->
<div id="mu-modal-host"></div>

<!-- Iframe oculto donde vive el player de YouTube -->
<div id="yt-host"><div id="yt-iframe"></div></div>


<script>
/* ─── Modo embebido ──────────────────────────────────────────────
   Cuando esta página vive dentro del shell SPA (mobile.php),
   delegamos toda la reproducción al motor del padre — el iframe de
   YouTube y el mini-player del shell controlan el audio, aquí solo
   gestionamos la UI de playlists. Standalone (acceso directo) sigue
   funcionando con su propio motor por compatibilidad. */
var EMBEDDED = (function(){ try { return window.parent !== window; } catch (_) { return false; } })();
var SHELL    = EMBEDDED ? window.parent.MuShell : null;

/* Ocultamos el mini-player local + el iframe YT local cuando estamos
   embebidos: el shell ya muestra su propio mini-player y mantiene la
   reproducción. */
if (EMBEDDED) {
    var _localPlayer = document.getElementById('mu-player');
    if (_localPlayer) _localPlayer.style.display = 'none';
    var _localYt = document.getElementById('yt-host');
    if (_localYt) _localYt.style.display = 'none';
    /* La vista grande del reproductor y la pantalla de bloqueo viven
       en musica-mobile, pero como no tenemos YT_PLAYER local quedan
       inservibles. Las ocultamos por completo. */
    ['mu-full', 'mu-lock'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    /* Y la status-bar del fullscreen "‹ Menú" envía postMessage en
       lugar de navegar para volver al launcher. */
    document.addEventListener('click', function(e){
        var menuLink = e.target && e.target.closest && e.target.closest('.mh-statusbar a[href*="../../mobile.php"]');
        if (!menuLink) return;
        e.preventDefault();
        try { window.parent.postMessage({ type: 'shell:back' }, '*'); } catch (_) {}
    }, true);

    /* Nos suscribimos al shell para recibir 'mushell:track' cada vez
       que cambia el track activo (next/prev/auto-advance) → reaplicamos
       el highlight visual en la lista. */
    try { window.parent.postMessage({ type: 'mushell:subscribe' }, '*'); } catch (_) {}
    window.addEventListener('message', function(ev){
        var d = ev.data || {};
        if (d.type !== 'mushell:track' || !d.track || !d.track.videoId) return;
        findAndHighlight(d.track.videoId, d.plName);
    });
}

/* Localiza el par (pi, ti) que mejor describe el track actual del shell.
   Prefiere la playlist cuyo nombre coincide con plName si viene; cae al
   primer match por videoId si no. */
function findAndHighlight(videoId, plName) {
    if (!videoId) return;
    function search(filterByName){
        for (var pi = 0; pi < PLAYLISTS.length; pi++) {
            var pl = PLAYLISTS[pi];
            if (filterByName && plName && pl.name !== plName) continue;
            var tracks = (pl && pl.tracks) || [];
            for (var ti = 0; ti < tracks.length; ti++) {
                if (tracks[ti] && tracks[ti].videoId === videoId) {
                    CUR_PL_IDX = pi; CUR_IDX = ti; QUEUE = tracks.slice();
                    applyPlayingHighlight(pi, ti);
                    return true;
                }
            }
        }
        return false;
    }
    if (plName && search(true)) return;
    search(false);
}

/* ─── Estado ────────────────────────────────────────────────────── */
var API_MUSIC = '../../assets/music/api.php';
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
var SHUFFLE_ON = false;    /* modo aleatorio: next/prev eligen track al azar */
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
            /* Embebida → restaurar highlight desde el estado del shell.
               Localizamos el track actual buscando su videoId en las
               PLAYLISTS recién cargadas. */
            restoreHighlightFromShell();
        })
        .catch(function(){
            document.getElementById('mu-list').innerHTML =
                '<div class="mh-empty"><span class="mh-empty-icon">⚠️</span>Error cargando playlists</div>';
        });
}

/* Cuando estamos embebidos pedimos el track actual al shell y
   buscamos su par (pl-idx, tr-idx) en PLAYLISTS para resaltar.
   Si el shell no está, o no hay track sonando, no hace nada. */
function restoreHighlightFromShell() {
    if (!SHELL || typeof SHELL.getState !== 'function') return;
    var st = null;
    try { st = SHELL.getState(); } catch (_) {}
    if (!st || !st.track || !st.track.videoId) return;
    findAndHighlight(st.track.videoId, st.plName);
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

        var canPlay   = (pl.tracks || []).length > 0;
        var playBtnHtml = '<button class="button mu-pl-play" type="button"' +
                            ' data-pl-idx="' + i + '"' +
                            (canPlay ? '' : ' disabled') +
                            ' aria-label="Reproducir playlist">' +
                            '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' +
                                '<polygon points="6 4 20 12 6 20"/>' +
                            '</svg>' +
                          '</button>';
        html += '<div class="mu-playlist" data-pl-idx="' + i + '">' +
                  '<div class="mu-pl-head">' +
                    '<div class="mu-pl-info">' +
                      '<div class="mu-pl-name">' + esc(pl.name) + '</div>' +
                      '<div class="mu-pl-meta">' + meta + '</div>' +
                    '</div>' +
                    avatarsHtml +
                    playBtnHtml +
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
    /* Botón ▶ del header — antes que el toggle del head para que el
       tap NO abra/cierre la playlist accidentalmente. */
    var playBtn = e.target.closest('.mu-pl-play');
    if (playBtn) {
        e.preventDefault();
        e.stopPropagation();
        if (playBtn.disabled) return;
        var pi = parseInt(playBtn.dataset.plIdx, 10);
        if (PLAYLISTS[pi] && (PLAYLISTS[pi].tracks || []).length) {
            playFromPlaylist(pi, 0);     /* arranca desde el primer track */
        }
        return;
    }
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
    if (e.data === 1)      { btn.classList.add('is-playing');    setMediaPlaybackState('playing'); }
    else if (e.data === 2) { btn.classList.remove('is-playing'); setMediaPlaybackState('paused');  }
    else if (e.data === 0) nextTrack();
    /* Espeja el estado en el toggle grande del fullscreen + pausa
       la rotación del vinilo cuando no se está reproduciendo. */
    muFullSyncPlay(e.data === 1);
}

/* ─── NOW PLAYING fullscreen + LOCK ─────────────────────────────
   Timer único de progreso (500ms) que actualiza el progreso de
   ambos paneles (fullscreen + lock) — solo corre cuando al menos
   uno está visible para ahorrar batería. */
var MU_PROGRESS_T = null;

function muFullOpen() {
    if (CUR_IDX < 0) return;                     /* nada sonando: no abre */
    var el = document.getElementById('mu-full');
    el.classList.add('visible');
    el.setAttribute('aria-hidden', 'false');
    muFullRefresh();
    muProgressTimerSync();
}
function muFullClose() {
    var el = document.getElementById('mu-full');
    el.classList.remove('visible');
    el.setAttribute('aria-hidden', 'true');
    muProgressTimerSync();
}
function muProgressTimerSync() {
    var fullVis = document.getElementById('mu-full').classList.contains('visible');
    var lockVis = document.getElementById('mu-lock').classList.contains('visible');
    if (fullVis || lockVis) muStartProgressTimer();
    else                    muStopProgressTimer();
}
function muStartProgressTimer() {
    if (MU_PROGRESS_T) return;
    MU_PROGRESS_T = setInterval(muTickProgress, 500);
    muTickProgress();
}
function muStopProgressTimer() {
    if (MU_PROGRESS_T) { clearInterval(MU_PROGRESS_T); MU_PROGRESS_T = null; }
}
function muFmt(sec) {
    sec = Math.max(0, Math.floor(sec || 0));
    var m = Math.floor(sec / 60);
    var s = sec % 60;
    return m + ':' + (s < 10 ? '0' + s : s);
}
function muTickProgress() {
    if (!YT_READY || !YT_PLAYER) return;
    var cur = 0, tot = 0;
    try {
        cur = YT_PLAYER.getCurrentTime() || 0;
        tot = YT_PLAYER.getDuration()    || 0;
    } catch (_) {}
    var pct = tot > 0 ? Math.min(100, (cur / tot) * 100) : 0;
    var curStr = muFmt(cur), totStr = muFmt(tot);
    /* Fullscreen */
    var f1 = document.getElementById('mu-full-progress-fill');
    var f2 = document.getElementById('mu-full-time-cur');
    var f3 = document.getElementById('mu-full-time-tot');
    if (f1) f1.style.width = pct + '%';
    if (f2) f2.textContent = curStr;
    if (f3) f3.textContent = totStr;
    /* Lock screen */
    var l1 = document.getElementById('mu-lock-progress-fill');
    var l2 = document.getElementById('mu-lock-time-cur');
    var l3 = document.getElementById('mu-lock-time-tot');
    if (l1) l1.style.width = pct + '%';
    if (l2) l2.textContent = curStr;
    if (l3) l3.textContent = totStr;
}
/* Alias retro-compat: el resto del código aún llama Start/Stop. */
function muFullStartProgressTimer() { muStartProgressTimer(); }
function muFullStopProgressTimer()  { muStopProgressTimer();  }
/* Marquee tipo ticker: si el texto no cabe en su wrap, duplica el
   span dentro del track y anima el track una distancia igual a
   (ancho del texto + gap). El clon llega exactamente a la posición
   inicial del original → loop continuo sin saltos ni vuelta atrás.
   Velocidad fija ~50 px/s → frases largas tardan más en cíclar. */
function muSetupMarquee(wrapId, trackId, textId) {
    var wrap  = document.getElementById(wrapId);
    var track = document.getElementById(trackId);
    var text  = document.getElementById(textId);
    if (!wrap || !track || !text) return;
    /* Reset: quita marquee, elimina cualquier clon previo, limpia props. */
    wrap.classList.remove('marquee');
    while (track.children.length > 1) track.removeChild(track.lastChild);
    track.style.removeProperty('--mu-marquee-cycle');
    track.style.removeProperty('--mu-marquee-duration');
    /* requestAnimationFrame: esperamos al layout antes de medir. */
    requestAnimationFrame(function(){
        var textW = text.offsetWidth;
        if (textW <= wrap.clientWidth + 4) return;  /* cabe — sin marquee */
        /* Clon del span con aria-hidden para lectores de pantalla. */
        var dup = text.cloneNode(true);
        dup.removeAttribute('id');
        dup.setAttribute('aria-hidden', 'true');
        track.appendChild(dup);
        /* Gap CSS = 3em → en píxeles. */
        var em  = parseFloat(getComputedStyle(text).fontSize) || 16;
        var gap = em * 3;
        var cycle = textW + gap;                    /* distancia a recorrer */
        var duration = Math.max(8, cycle / 50).toFixed(1) + 's';  /* ~50 px/s */
        track.style.setProperty('--mu-marquee-cycle', cycle + 'px');
        track.style.setProperty('--mu-marquee-duration', duration);
        wrap.classList.add('marquee');
    });
}

function muFullRefresh() {
    if (CUR_IDX < 0 || !QUEUE[CUR_IDX]) return;
    var tr = QUEUE[CUR_IDX];
    document.getElementById('mu-full-title').textContent  = tr.title  || tr.videoId || '—';
    document.getElementById('mu-full-artist').textContent = tr.artist || '';
    /* Re-evalúa marquee con el texto nuevo. */
    muSetupMarquee('mu-full-title-wrap',  'mu-full-title-track',  'mu-full-title');
    muSetupMarquee('mu-full-artist-wrap', 'mu-full-artist-track', 'mu-full-artist');
    /* mqdefault: 320×180 16:9, sin letterbox. */
    var thumbUrl = tr.videoId ? 'https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg' : '';
    var label  = document.getElementById('mu-vinyl-label');
    var splash = document.getElementById('mu-full-splash');
    if (thumbUrl) {
        label.style.backgroundImage  = "url('" + thumbUrl + "')";
        splash.style.backgroundImage = "url('" + thumbUrl + "')";
        label.classList.remove('empty');
        label.textContent = '';
    } else {
        label.style.backgroundImage  = '';
        splash.style.backgroundImage = '';
        label.classList.add('empty');
        label.textContent = '🎵';
    }
    /* Title-bar: nombre de la playlist activa. */
    var tbPl = document.getElementById('mu-full-tb-pl');
    if (tbPl) {
        var pl = (CUR_PL_IDX >= 0 && PLAYLISTS[CUR_PL_IDX]) ? PLAYLISTS[CUR_PL_IDX] : null;
        tbPl.textContent = pl ? pl.name : 'Reproduciendo';
    }
    /* Replica los mismos datos en el lock (vinilo + info + marquee). */
    muLockSync();
    /* Sincroniza play state. */
    var playing = !!(YT_PLAYER && YT_PLAYER.getPlayerState && YT_PLAYER.getPlayerState() === 1);
    muFullSyncPlay(playing);
}
function muFullSyncPlay(isPlaying) {
    var btn       = document.getElementById('mu-full-toggle');
    var vinyl     = document.getElementById('mu-vinyl');
    var lockVinyl = document.getElementById('mu-lock-vinyl');
    if (btn)       btn.classList.toggle('is-playing', !!isPlaying);
    if (vinyl)     vinyl.classList.toggle('paused', !isPlaying);
    if (lockVinyl) lockVinyl.classList.toggle('paused', !isPlaying);
}

/* Tap en el mini-player (excepto en los botones) → abre fullscreen. */
document.getElementById('mu-player').addEventListener('click', function(e){
    if (e.target.closest('button')) return;      /* clicks de control siguen su flujo */
    muFullOpen();
});
document.getElementById('mu-full-close').addEventListener('click', muFullClose);
/* Chevron-down al pie → minimiza el fullscreen. Mismo comportamiento
   que la X del title-bar, pero accesible con el pulgar. */
document.getElementById('mu-full-menu').addEventListener('click', muFullClose);

/* ─── LOCK SCREEN ──────────────────────────────────────────────
   Click en el padlock → muLockOpen() añade .visible y la transición
   CSS funde el reproductor con el fondo negro. Para los pollers que
   gastan batería (progress timer). Swipe-up para desbloquear. */
/* Theme-color del SO mientras el lock está activo: negro puro para
   que la barra de estado/notch y la nav-bar se fundan con el modo
   reposo y los bordes superior/inferior del dispositivo se vean
   negros, dando sensación de pantalla apagada. */
var THEME_COLOR_DEFAULT = '<?= htmlspecialchars($themeBgColor) ?>';
function muSetThemeColor(color) {
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', color);
}

function muLockOpen() {
    if (CUR_IDX < 0) return;                 /* nada sonando: nada que bloquear */
    var lock = document.getElementById('mu-lock');
    var hint = document.getElementById('mu-lock-hint');
    var full = document.getElementById('mu-full');
    if (hint) { hint.style.transform = ''; hint.style.opacity = ''; }
    muLockSync();                            /* pinta vinilo/info/progress antes del fade */
    lock.classList.add('visible');
    lock.setAttribute('aria-hidden', 'false');
    if (full) full.classList.add('behind-lock');
    muSetThemeColor('#000000');
    /* Fullscreen API: oculta la barra de navegación del SO (los
       3 botones inferiores en Android, gesture bar en iOS) y la
       status bar. Solo funciona dentro de un gesto del usuario
       — el click en el padlock cuenta como tal. iOS Safari tiene
       soporte limitado; en PWA standalone con display:fullscreen
       en el manifest funciona mejor. */
    muRequestFullscreen();
    muProgressTimerSync();
}
function muLockClose() {
    var lock = document.getElementById('mu-lock');
    var hint = document.getElementById('mu-lock-hint');
    var full = document.getElementById('mu-full');
    lock.classList.remove('visible');
    lock.setAttribute('aria-hidden', 'true');
    if (full) full.classList.remove('behind-lock');
    muSetThemeColor(THEME_COLOR_DEFAULT);
    muExitFullscreen();
    setTimeout(function(){
        if (hint) { hint.style.transform = ''; hint.style.opacity = ''; }
    }, 1200);
    muProgressTimerSync();
}
/* Helpers Fullscreen API con prefijos webkit por compatibilidad. */
function muRequestFullscreen() {
    var el = document.documentElement;
    var fn = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
    if (fn) {
        try { fn.call(el).catch(function(){}); } catch (_) {}
    }
}
function muExitFullscreen() {
    var fn = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
    if (fn && (document.fullscreenElement || document.webkitFullscreenElement)) {
        try { fn.call(document).catch(function(){}); } catch (_) {}
    }
}

/* Sincroniza el lock con el track actual: vinilo (label + paused),
   título, artista, marquee. El progreso lo refresca el timer común. */
function muLockSync() {
    if (CUR_IDX < 0 || !QUEUE[CUR_IDX]) return;
    var tr = QUEUE[CUR_IDX];
    var thumbUrl = tr.videoId ? 'https://i.ytimg.com/vi/' + tr.videoId + '/mqdefault.jpg' : '';
    var label = document.getElementById('mu-lock-vinyl-label');
    var vinyl = document.getElementById('mu-lock-vinyl');
    var titleEl  = document.getElementById('mu-lock-title');
    var artistEl = document.getElementById('mu-lock-artist');
    if (label) {
        if (thumbUrl) {
            label.style.backgroundImage = "url('" + thumbUrl + "')";
            label.classList.remove('empty');
            label.textContent = '';
        } else {
            label.style.backgroundImage = '';
            label.classList.add('empty');
            label.textContent = '🎵';
        }
    }
    if (titleEl)  titleEl.textContent  = tr.title  || tr.videoId || '—';
    if (artistEl) artistEl.textContent = tr.artist || '';
    muSetupMarquee('mu-lock-title-wrap',  'mu-lock-title-track',  'mu-lock-title');
    muSetupMarquee('mu-lock-artist-wrap', 'mu-lock-artist-track', 'mu-lock-artist');
    if (vinyl) {
        var playing = !!(YT_PLAYER && YT_PLAYER.getPlayerState && YT_PLAYER.getPlayerState() === 1);
        vinyl.classList.toggle('paused', !playing);
    }
}

document.getElementById('mu-full-lock').addEventListener('click', muLockOpen);

/* Swipe up para desbloquear. SOLO se mueve el hint (.mu-lock-hint);
   ni el fondo negro ni el vinilo se tocan. Threshold = mitad del
   viewport (~400px en móvil estándar). Al alcanzarlo se dispara el
   fade-out lento (1.2s) del lock screen entero. */
(function attachLockSwipe() {
    var lock = document.getElementById('mu-lock');
    var hint = document.getElementById('mu-lock-hint');
    if (!lock || !hint) return;
    var startY = null;
    var dragging = false;
    function getThreshold() { return window.innerHeight / 3; }   /* ~1/3 de la pantalla */

    lock.addEventListener('touchstart', function(e){
        if (!e.touches || e.touches.length !== 1) return;
        startY = e.touches[0].clientY;
        dragging = true;
        /* Sin transición durante el drag para que el hint siga al
           dedo sin retraso. */
        hint.style.transition = 'none';
    }, { passive: true });
    lock.addEventListener('touchmove', function(e){
        if (!dragging || startY === null || !e.touches[0]) return;
        var dy = startY - e.touches[0].clientY;
        if (dy > 0) {
            /* Amplificación visual x1.8 — el hint "vuela" con el
               dedo. translateX(-50%) lo conserva centrado horizontal. */
            hint.style.transform = 'translateX(-50%) translateY(' + (-dy * 1.8) + 'px)';
            /* Fade gradual del hint conforme se acerca al threshold. */
            var pct = Math.min(1, dy / getThreshold());
            hint.style.opacity = String(0.8 - pct * 0.6);
        }
    }, { passive: true });
    function endDrag(e) {
        if (!dragging || startY === null) return;
        dragging = false;
        var t = e.changedTouches && e.changedTouches[0];
        var dy = t ? (startY - t.clientY) : 0;
        startY = null;
        hint.style.transition = '';   /* restaura snap-back fluido */
        if (dy >= getThreshold()) {
            muLockClose();
        } else {
            /* Snap back: el hint vuelve a su sitio. */
            hint.style.transform = '';
            hint.style.opacity = '';
        }
    }
    lock.addEventListener('touchend',    endDrag);
    lock.addEventListener('touchcancel', endDrag);

    /* Soporte mouse para testing en escritorio. */
    var msDown = false;
    lock.addEventListener('mousedown', function(e){
        if (e.button !== 0) return;
        startY = e.clientY;
        msDown = true;
        dragging = true;
        hint.style.transition = 'none';
    });
    lock.addEventListener('mousemove', function(e){
        if (!msDown || startY === null) return;
        var dy = startY - e.clientY;
        if (dy > 0) {
            hint.style.transform = 'translateX(-50%) translateY(' + (-dy * 1.8) + 'px)';
            var pct = Math.min(1, dy / getThreshold());
            hint.style.opacity = String(0.8 - pct * 0.6);
        }
    });
    function endMouseDrag(e) {
        if (!msDown) return;
        msDown = false;
        var dy = startY !== null ? (startY - e.clientY) : 0;
        startY = null;
        hint.style.transition = '';
        if (dy >= getThreshold()) muLockClose();
        else {
            hint.style.transform = '';
            hint.style.opacity = '';
        }
    }
    lock.addEventListener('mouseup',    endMouseDrag);
    lock.addEventListener('mouseleave', endMouseDrag);
})();
document.getElementById('mu-full-prev').addEventListener('click', prevTrack);
document.getElementById('mu-full-next').addEventListener('click', nextTrack);
document.getElementById('mu-full-toggle').addEventListener('click', function(){
    if (!YT_READY || !YT_PLAYER || CUR_IDX < 0) return;
    var st = YT_PLAYER.getPlayerState();
    if (st === 1) YT_PLAYER.pauseVideo();
    else          YT_PLAYER.playVideo();
});

/* Shuffle: toggle visual + de comportamiento. El estado lo lleva
   SHUFFLE_ON; next/prev miran esa flag para decidir secuencial o
   random. */
document.getElementById('mu-full-shuffle').addEventListener('click', function(){
    SHUFFLE_ON = !SHUFFLE_ON;
    this.classList.toggle('is-on', SHUFFLE_ON);
    this.setAttribute('aria-pressed', SHUFFLE_ON ? 'true' : 'false');
});

/* Añadir canción actual a otra playlist. Lista todas las playlists,
   chequea duplicados por videoId y pide confirmación si ya existe. */
document.getElementById('mu-full-add').addEventListener('click', function(){
    if (CUR_IDX < 0 || !QUEUE[CUR_IDX]) return;
    muOpenAddCurrentToPlaylist(QUEUE[CUR_IDX]);
});

function muOpenAddCurrentToPlaylist(tr) {
    if (!PLAYLISTS.length) { muAlert('No tienes playlists todavía'); return; }
    var bodyHtml = '<p class="modal-msg" style="margin:0 0 6px;">' +
                     'Añadir "' + esc(tr.title || tr.videoId || 'canción') + '" a:' +
                   '</p>';
    bodyHtml += '<div class="mu-modal-list">';
    PLAYLISTS.forEach(function(pl, pi){
        var nTracks = (pl.tracks || []).length;
        var sharedTag = pl.sharedLabel
            ? '<span style="font-size:10px;color:var(--text-faint, #666);margin-left:6px;">de ' + esc(pl.sharedLabel) + '</span>'
            : '';
        bodyHtml += '<div class="mu-modal-list-item" data-pl-idx="' + pi + '">' +
                      '<div class="item-main">' +
                        '<div style="font-weight:bold;">' + esc(pl.name) + sharedTag + '</div>' +
                        '<div style="font-size:11px;color:var(--text-faint, #666);">' +
                            nTracks + ' canción' + (nTracks === 1 ? '' : 'es') +
                        '</div>' +
                      '</div>' +
                    '</div>';
    });
    bodyHtml += '</div>';
    bodyHtml += '<div class="modal-actions"><button class="button" data-act="cancel" type="button">Cancelar</button></div>';

    /* Mismo SVG que el botón → coherencia visual entre trigger y modal. */
    var addIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">' +
                    '<line x1="12" y1="5"  x2="12" y2="19"/>' +
                    '<line x1="5"  y1="12" x2="19" y2="12"/>' +
                  '</svg>';
    var m = muOpenModal({ titleIcon: addIcon, title: 'Añadir a playlist', body: bodyHtml });
    m.body.querySelector('[data-act="cancel"]').addEventListener('click', m.close);
    m.body.querySelectorAll('.mu-modal-list-item').forEach(function(el){
        el.addEventListener('click', function(){
            var pi = parseInt(el.dataset.plIdx, 10);
            var pl = PLAYLISTS[pi];
            if (!pl) return;
            m.close();
            muAddCurrentToPlaylistConfirmed(pl, tr);
        });
    });
}

function muAddCurrentToPlaylistConfirmed(pl, tr) {
    var dup = tr.videoId && (pl.tracks || []).some(function(t){
        return t && t.videoId && t.videoId === tr.videoId;
    });
    var doAdd = function(){
        var newTracks = (pl.tracks || []).slice();
        /* Copia plana de tr + addedBy (no mutamos el objeto original
           que vive en QUEUE/PLAYLISTS). */
        var trCopy = {};
        for (var k in tr) if (tr.hasOwnProperty(k)) trCopy[k] = tr[k];
        trCopy.addedBy = ME_LABEL;
        newTracks.push(trCopy);
        var payload = { id: pl.id, name: pl.name, tracks: newTracks };
        if (pl.sharedFrom) payload.sharedFrom = pl.sharedFrom;
        apiPost('save-playlist-item', payload).then(function(res){
            if (!res.ok) { muAlert((res.data && res.data.error) || 'Error al añadir'); return; }
            loadPlaylists();
            muAlert('"' + (tr.title || 'Canción') + '" añadida a "' + pl.name + '"', 'Añadida');
        });
    };
    if (dup) {
        muConfirm(
            '"' + (tr.title || 'Esta canción') + '" ya está en "' + pl.name + '". ¿Añadirla de nuevo?',
            doAdd
        );
    } else {
        doAdd();
    }
}

/* ─── Reproductor YouTube ──────────────────────────────────────── */
/* Carga la IFrame API una vez. El callback global onYouTubeIframeAPIReady
   es el contrato standard de la API. */
window.onYouTubeIframeAPIReady = function() {
    if (EMBEDDED) return;             /* shell maneja el player */
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
    if (EMBEDDED) return;             /* shell ya tiene su propio iframe API */
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
    CUR_PL_IDX = pi;
    /* Highlight visual del track activo — también cuando estamos
       embebidos en el shell (antes solo lo hacía playCurrent que no
       corre en modo embed). Selector con pl-idx + tr-idx para no
       pintar tracks homónimos de otras playlists. */
    applyPlayingHighlight(pi, ti);
    /* Embebida → delega al shell, pasando el nombre de la playlist
       para el title-bar del fullscreen. */
    if (SHELL) { SHELL.loadQueue(QUEUE, ti, pl.name); return; }
    playCurrent();
}

/* Pone .playing solo en el track del par (pi, ti). Limpia el resto. */
function applyPlayingHighlight(pi, ti) {
    document.querySelectorAll('.mu-track.playing').forEach(function(el){
        el.classList.remove('playing');
    });
    if (pi == null || ti == null || pi < 0 || ti < 0) return;
    var sel = document.querySelector(
        '.mu-track[data-pl-idx="' + pi + '"][data-tr-idx="' + ti + '"]'
    );
    if (sel) sel.classList.add('playing');
}

function playCurrent() {
    if (CUR_IDX < 0 || CUR_IDX >= QUEUE.length) return;
    var tr = QUEUE[CUR_IDX];
    if (!tr || !tr.videoId) { nextTrack(); return; }
    /* Marca visualmente el track activo en la lista. Usa pl-idx + tr-idx
       para no pintar tracks homónimos de otras playlists. */
    applyPlayingHighlight(CUR_PL_IDX, CUR_IDX);
    /* Info en el mini-player. */
    document.getElementById('mu-now-title').textContent  = tr.title  || tr.videoId;
    document.getElementById('mu-now-artist').textContent = tr.artist || '';
    document.getElementById('mu-thumb').innerHTML =
        '<img src="https://i.ytimg.com/vi/' + esc(tr.videoId) + '/mqdefault.jpg" alt="">';
    document.getElementById('mu-player').classList.add('visible');
    /* Refresca también la vista fullscreen si está abierta — esto
       actualiza el thumbnail del vinilo y el splash con cada cambio
       de track. */
    muFullRefresh();
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

/* Devuelve un índice aleatorio de QUEUE distinto al actual. Se usa
   tanto para prev como next cuando SHUFFLE_ON: si solo hay un track
   se queda donde está. */
function muPickRandomIdx() {
    if (QUEUE.length <= 1) return CUR_IDX;
    var r;
    do { r = Math.floor(Math.random() * QUEUE.length); } while (r === CUR_IDX);
    return r;
}
function nextTrack() {
    if (CUR_IDX < 0) return;
    if (SHUFFLE_ON) { CUR_IDX = muPickRandomIdx(); }
    else {
        CUR_IDX++;
        if (CUR_IDX >= QUEUE.length) CUR_IDX = 0;     /* loop circular */
    }
    playCurrent();
}
function prevTrack() {
    if (CUR_IDX < 0) return;
    if (SHUFFLE_ON) { CUR_IDX = muPickRandomIdx(); }
    else {
        CUR_IDX--;
        if (CUR_IDX < 0) CUR_IDX = QUEUE.length - 1;  /* loop circular */
    }
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
    /* opts.titleIcon: HTML/SVG crudo que se inyecta antes del título.
       Se confía la entrada — no la llaman desde input de usuario. */
    var titleHtml = (opts.titleIcon ? opts.titleIcon : '') + esc(opts.title || '');
    bd.innerHTML =
        '<div class="window mu-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">' + titleHtml + '</div>' +
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
        { act: 'addPl',      label: '📋 Añadir a otra playlist' },
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
            if (act === 'addPl')      muOpenAddCurrentToPlaylist(tr);
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
var API_PROFILE = '../../assets/profile/api.php';
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
