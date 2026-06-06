<?php
/* ──────────────────────────────────────────────────────────────────────
   D&D — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Centrada en los dos casos de uso útiles en móvil:
     1. 🎲 Lanzador de dados Three.js (mismo motor que el escritorio).
     2. 📁 Mis Fichas — lista de PDFs guardados en Google Drive con
        visor (PDF.js, página a página, pinch-zoom nativo).
   La EDICIÓN de campos del PDF queda fuera — es inviable en pantalla
   pequeña. Para editar el usuario sigue usando la versión escritorio.
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
    <title>D&D</title>
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
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        /* Fuente Allison para la ficha melon — misma que escritorio. */
        @font-face {
            font-family: 'Allison';
            src: url('../../assets/fonts/Allison-Regular.ttf') format('truetype');
            font-display: swap;
        }

        /* ── Layout base ── window-body sin padding/gap, cada tab gestiona
           su propio espacio. */
        .mh-window > .window-body {
            gap: 0 !important;
            padding: 0 !important;
            padding-bottom: max(0px, env(safe-area-inset-bottom)) !important;
        }

        /* ── Tabs Win98 ── pestañas tipo carpeta. */
        #dnd-tabs {
            display: flex; gap: 0;
            padding: 4px 4px 0;
            background: var(--win-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-1, #0a0a0a);
            flex-shrink: 0;
        }
        #dnd-tabs .dnd-tab {
            min-height: 28px;
            padding: 4px 10px;
            margin: 0 -1px 0 0;
            font-size: 11px;
            line-height: 1;
            white-space: nowrap;
            flex: 1; min-width: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            border: 0;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            position: relative;
            cursor: pointer;
        }
        #dnd-tabs .dnd-tab.active {
            background: var(--win-bg, silver);
            color: var(--text, #000);
            box-shadow:
                inset  1px  1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-light-2, #dfdfdf),
                inset -1px 0 var(--bezel-dark-1, #0a0a0a),
                inset -2px 0 var(--bezel-dark-2, grey);
            font-weight: bold;
            z-index: 2;
            top: 1px;
            padding-bottom: 6px;
        }

        /* ── Paneles de cada tab ── */
        .dnd-panel {
            flex: 1; min-height: 0;
            display: none;
            flex-direction: column;
            overflow: hidden;
            background: var(--win-bg, silver);
        }
        .dnd-panel.is-active { display: flex; }

        /* ── DADOS ── */
        #dnd-dados {
            padding: 8px;
            gap: 8px;
            color: var(--text, #000);
        }
        /* Fila de dados: scroll horizontal Win98. Los botones .button de
           98.css tienen min-width: 75px, así que en móvil estrecho
           7×75 + gaps no caben en la ventana del overlay (max 480px).
           Antes usábamos grid 1fr lo que forzaba overflow invisible.
           Ahora: flex horizontal con overflow-x: auto → scrollbar visible. */
        #dice-buttons-row {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 2px;
        }
        .dice-btn-m {
            min-height: 40px;
            min-width: 56px;
            flex-shrink: 0;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            display: inline-flex; align-items: center; justify-content: center;
        }
        /* Stage del canvas con bezel hundido. */
        #dice-stage-m {
            flex: 1; min-height: 200px;
            position: relative;
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
            overflow: hidden;
        }
        #dice-canvas-m {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            display: block;
        }
        #dice-overlays-m {
            position: absolute; inset: 0;
            pointer-events: none;
        }
        .dice-val {
            position: absolute;
            transform: translate(-50%, -50%);
            font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', monospace;
            font-size: 18px;
            font-weight: bold;
            color: var(--accent-text, #fff);
            text-shadow: 1px 1px 0 #000, -1px 1px 0 #000, 1px -1px 0 #000, -1px -1px 0 #000;
            pointer-events: none;
        }
        #dice-hint-m {
            position: absolute; inset: auto 8px 8px;
            font-size: 10px;
            color: var(--text-muted, #666);
            text-align: center;
            pointer-events: none;
        }
        #dice-actions-row {
            display: flex; gap: 4px;
            flex-shrink: 0;
        }
        #dice-actions-row .button { flex: 1; min-height: 30px; font-size: 11px; }
        #dice-mod-btn-m {
            min-width: 50px;
            flex: 0 0 auto;
        }
        #dice-result-m {
            background: var(--win-bg, silver);
            padding: 6px 10px;
            text-align: center;
            font-size: 14px;
            min-height: 30px;
            display: flex; align-items: center; justify-content: center;
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff);
            flex-shrink: 0;
        }
        .dice-crit { color: var(--accent-deep, #c8a000); font-weight: bold; }
        .dice-fail { color: var(--error-text, #c00); font-weight: bold; }

        /* ── MIS FICHAS ── */
        #dnd-fichas {
            padding: 0;
        }
        #mf-toolbar-m {
            display: flex; gap: 4px; align-items: center;
            padding: 6px;
            background: var(--win-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            flex-shrink: 0;
        }
        #mf-search-m {
            flex: 1; min-width: 0;
            font-size: 12px;
            min-height: 26px;
        }
        #mf-toolbar-m .button {
            min-height: 26px; min-width: 32px; font-size: 11px;
            padding: 3px 8px;
        }
        #mf-list-m {
            flex: 1; min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 8px;
            padding-bottom: max(16px, env(safe-area-inset-bottom));
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .ficha-card-m {
            background: var(--win-bg, silver);
            padding: 8px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 4px 8px;
            align-items: center;
        }
        .fc-tipo-m { font-size: 10px; color: var(--text-muted, #666); grid-column: 1; }
        .fc-nombre-m { font-size: 13px; font-weight: bold; grid-column: 1; word-break: break-word; }
        .fc-fecha-m { font-size: 10px; color: var(--text-muted, #666); grid-column: 1; }
        .fc-actions-m { grid-column: 2; grid-row: 1 / span 3; display: flex; flex-direction: column; gap: 4px; align-self: stretch; }
        .fc-actions-m .button { min-height: 28px; font-size: 11px; padding: 3px 8px; white-space: nowrap; }
        .fc-actions-m .danger { color: var(--error-text, #c00); }
        #mf-empty-m {
            text-align: center;
            font-size: 11px;
            color: var(--text-muted, #666);
            padding: 24px 16px;
        }
        #mf-empty-m strong { color: var(--text, #000); }
        #drive-status-m {
            font-size: 10px;
            color: var(--text-muted, #666);
            margin-left: 4px;
        }

        /* ── VISOR PDF (modal fullscreen) Win98 ──
           Marco silver con bezel raised completo + barra de título
           azul. Mismo look que cualquier window de Win98 nativo. */
        #pdf-viewer {
            position: fixed; inset: 0;
            background: var(--win-bg, silver);
            z-index: 9500;
            display: none;
            flex-direction: column;
            padding: 3px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf);
            box-sizing: border-box;
        }
        #pdf-viewer.is-open { display: flex; }
        #pdf-viewer-titlebar {
            display: flex;
            background: linear-gradient(to right, var(--titlebar-start, #000080), var(--titlebar-end, #1084d0));
            color: var(--titlebar-text, #fff);
            align-items: center;
            padding: 3px 4px 3px 6px;
            font-size: 11px; font-weight: bold;
            flex-shrink: 0;
        }
        #pdf-viewer-title { flex: 1; min-width: 0;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ── Toolbar Win98 con zoom + acciones (encima del canvas). ── */
        #pdf-viewer-toolbar {
            display: flex; align-items: center; gap: 4px;
            padding: 4px 6px;
            background: var(--win-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            flex-shrink: 0;
        }
        #pdf-viewer-toolbar .button {
            min-height: 26px; min-width: 32px;
            font-size: 11px; padding: 3px 6px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        #pdf-zoom-label {
            font-size: 10px;
            color: var(--text, #000);
            background: var(--win-bg, silver);
            padding: 3px 6px;
            min-width: 42px;
            text-align: center;
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey);
        }
        .tb-sep {
            width: 2px;
            align-self: stretch;
            margin: 0 2px;
            background: var(--bezel-dark-2, grey);
            box-shadow: 1px 0 0 var(--bezel-light-1, #fff);
        }

        /* ── Área de visualización Win98 hundida ──
           Fondo silver con bezel inset (sunken panel). La hoja se
           centra horizontal y vertical cuando es menor que el área
           (zoom out); con zoom in aparecen scrollbars. */
        #pdf-viewer-canvas-wrap {
            flex: 1; min-height: 0;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            background: var(--win-bg, silver);
            padding: 12px;
            display: flex;
            /* `safe` evita el bug clásico del flex centrado con overflow:
               cuando el contenido es MAYOR que el contenedor, justify-content:
               center coloca el inicio del contenido en una posición negativa
               INACCESIBLE para el scroll (scrollLeft no puede ser < 0).
               Con `safe center`, si hay overflow se cae a `start` y puedes
               scrollear a TODO el ancho de la hoja, no solo a la mitad
               derecha. Mismo razonamiento para align vertical. */
            align-items: safe flex-start;
            justify-content: safe center;
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
            /* Permitir scroll de un dedo y dejar el pinch-zoom para nuestro
               handler JS (que rerendea el PDF a la nueva resolución, no
               solo escala bitmap). pinch-zoom nativo NO se incluye aquí. */
            touch-action: pan-x pan-y;
        }
        /* Contenedor relativo del canvas + capa de formularios para que
           los inputs se posicionen sobre los widgets del PDF. Bezel
           raised Win98 alrededor de la hoja. Flex-shrink:0 + margin:auto
           mantienen la hoja centrada cuando zoom < 100% y permiten
           scroll cuando zoom > 100%. */
        #pdf-page-wrap {
            position: relative;
            flex-shrink: 0;
            background: #fff;
            line-height: 0;
            padding: 2px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf),
                3px 3px 10px rgba(0,0,0,0.35);
            /* SIN `margin: auto` aquí — provocaba el mismo bug que el
               justify-content:center pre-`safe`: márgenes negativos
               inaccesibles al scroll. El centrado lo gestiona el padre
               con `safe center`. */
        }
        #pdf-viewer-canvas {
            display: block;
            background: #fff;
        }
        /* Capa de inputs encima del canvas. Cada input/textarea/etc.
           se posiciona en absoluto con coordenadas calculadas por PDF.js
           desde el viewport del canvas.
           IMPORTANTE: `inset: 2px` para que la capa ARRANQUE en la
           misma esquina sup-izq que el canvas, que está desplazado 2px
           hacia adentro por el `padding: 2px` del #pdf-page-wrap (ese
           padding es necesario para que el bezel Win98 no tape el PDF).
           Sin esta corrección los inputs aparecen 2px arriba/izquierda
           respecto al PDF impreso — drift apenas visible a zoom alto
           pero notorio al bajar el zoom (las casillas "suben"). */
        #pdf-form-layer {
            position: absolute;
            inset: 2px;
            pointer-events: none;
        }
        /* Inputs editables sobre el PDF — reglas base para POSICIÓN y
           reset visual. `appearance: none` SOLO se aplica a text, textarea
           y select (más abajo) — los checkboxes y radios mantienen su
           apariencia nativa para ser visibles. */
        #pdf-form-layer > * {
            position: absolute;
            box-sizing: border-box;
            margin: 0;
            line-height: 1;
            color: rgba(0, 0, 0, 0.92);
            pointer-events: auto;
            font-family: Helvetica, Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        /* Text/textarea/select: fondo amarillo + reset visual completo. */
        #pdf-form-layer input[type="text"],
        #pdf-form-layer textarea,
        #pdf-form-layer select {
            background: rgba(255, 255, 0, 0.10);
            border: 0 !important;
            border-radius: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            padding: 0 2px;
            -webkit-appearance: none !important;
            appearance: none !important;
        }
        #pdf-form-layer input[type="text"]:focus,
        #pdf-form-layer textarea:focus,
        #pdf-form-layer select:focus {
            background: rgba(180, 200, 255, 0.30);
        }
        #pdf-form-layer textarea {
            resize: none;
            overflow: hidden;
        }
        /* Checkboxes y radios — mismo estilo visual que los inputs de
           texto: fondo amarillo translúcido, sin bezel Win98. Al estar
           marcados aparece un check/punto negro sutil dentro.

           Hay que neutralizar los overrides agresivos de 98.css
           (opacity:0; position:fixed; etc.) con !important en cada
           propiedad que la regla global toca, igual que con los text
           inputs. */
        #pdf-form-layer input[type="checkbox"],
        #pdf-form-layer input[type="radio"] {
            -webkit-appearance: none !important;
            appearance: none !important;
            opacity: 1 !important;
            position: absolute !important;
            background-color: rgba(255, 255, 0, 0.10) !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
            background-size: 80% 80% !important;
            border: 0 !important;
            box-shadow: none !important;
            cursor: pointer;
            padding: 0 !important;
            margin: 0 !important;
            /* width/height/posición vienen del JS (inline) — escalan con
               el zoom como el resto de campos para mantener proporción
               respecto al PDF impreso. */
        }
        #pdf-form-layer input[type="checkbox"]:focus,
        #pdf-form-layer input[type="radio"]:focus {
            background-color: rgba(180, 200, 255, 0.30) !important;
        }
        /* Marcado: tick SVG negro fino centrado, alpha ~80% para que
           encaje con la suavidad del resto de la UI. */
        #pdf-form-layer input[type="checkbox"]:checked {
            background-image:
                url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2016%2016%27%3E%3Cpath%20d%3D%27M3%208%20L7%2012%20L13%204%27%20fill%3D%27none%27%20stroke%3D%27%23000%27%20stroke-opacity%3D%270.8%27%20stroke-width%3D%272%27%20stroke-linecap%3D%27round%27%20stroke-linejoin%3D%27round%27%2F%3E%3C%2Fsvg%3E") !important;
        }
        #pdf-form-layer input[type="radio"] {
            border-radius: 50% !important;
        }
        #pdf-form-layer input[type="radio"]:checked {
            background-image:
                radial-gradient(circle, rgba(0,0,0,0.8) 0 30%, transparent 31% 100%) !important;
        }
        #pdf-viewer-pagebar {
            display: flex; gap: 4px; align-items: center;
            padding: 6px 8px;
            background: var(--win-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
            flex-shrink: 0;
        }
        #pdf-viewer-pagebar .button {
            min-height: 26px; min-width: 36px; font-size: 11px;
        }
        #pdf-viewer-pageinfo {
            flex: 1;
            text-align: center;
            font-size: 11px;
            color: var(--text, #000);
            background: var(--win-bg, silver);
            padding: 3px 6px;
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey);
        }
        #pdf-viewer-loading {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 12px;
            pointer-events: none;
        }

        /* ── DICE OVERLAY dentro del visor PDF ──
           Ventana Win98 flotante posicionada en la esquina inferior
           del visor. Tap fuera no la cierra (la X o el botón 🎲 sí).
           El layout interno es el mismo que tenía el tab Dados original. */
        #dice-overlay-m {
            position: absolute;
            left: 8px; right: 8px;
            bottom: 8px;
            max-width: 480px;
            margin: 0 auto;
            max-height: 70%;
            display: none;
            flex-direction: column;
            z-index: 9550;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf),
                0 6px 18px rgba(0,0,0,0.5);
        }
        #dice-overlay-m.is-open { display: flex; }

        /* ── Modal "Nueva ficha" ── */
        #new-sheet-modal {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 9650;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        #new-sheet-modal.is-open { display: flex; }
        #new-sheet-box {
            width: 100%;
            max-width: 320px;
            display: flex;
            flex-direction: column;
        }

        /* ── Modal modificador (igual estilo Win98 dialog) ── */
        #dice-mod-modal-m {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 9600;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        #dice-mod-modal-m.is-open { display: flex; }
        #dice-mod-box-m {
            width: 100%;
            max-width: 280px;
        }
        #dice-mod-box-m input[type="number"] {
            width: 100%; box-sizing: border-box;
            font-size: 18px;
            text-align: center;
            min-height: 36px;
        }

        /* ── Toast ── */
        #toast-m {
            position: fixed;
            bottom: calc(60px + env(safe-area-inset-bottom));
            left: 50%;
            transform: translateX(-50%);
            background: var(--win-bg, silver);
            color: var(--text, #000);
            padding: 6px 12px;
            font-size: 11px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf),
                0 4px 12px rgba(0,0,0,0.4);
            z-index: 9800;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            max-width: 80vw;
            text-align: center;
        }
        #toast-m.show { opacity: 1; }
    </style>
</head>
<body class="mh-body <?php echo htmlspecialchars($activeThemeClass); ?>">

<div class="window mh-window" id="dndWindow">
    <div class="title-bar">
        <div class="title-bar-text"><img src="../../assets/img/appIcons/dndIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Fichas D&amp;D</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" id="dnd-back"></button>
        </div>
    </div>
    <div class="window-body">

        <!-- ────── MIS FICHAS (vista única) ────── -->
        <div id="dnd-fichas" class="dnd-panel is-active">
            <div id="mf-toolbar-m">
                <input type="text" id="mf-search-m" placeholder="Buscar ficha…">
                <button class="button" id="mf-new-m" title="Nueva ficha">＋ Nueva</button>
                <button class="button" id="mf-refresh-m" title="Recargar">↻</button>
                <button class="button" id="mf-disconnect-m" title="Desconectar Drive">🔌</button>
                <span id="drive-status-m"></span>
            </div>
            <div id="mf-list-m">
                <div id="mf-empty-m">☁ Conecta tu Google Drive para ver tus fichas.</div>
            </div>
        </div>

        <!-- Status bar Win98 -->
        <div class="mh-statusbar">
            <a href="#" id="dnd-menu-link">‹ Menú</a>
        </div>

    </div><!-- /.window-body -->
</div><!-- /.window.mh-window -->

<!-- Visor PDF a pantalla completa (editable) -->
<div id="pdf-viewer">
    <div id="pdf-viewer-titlebar">
        <span id="pdf-viewer-title">PDF</span>
        <div class="title-bar-controls">
            <button aria-label="Close" id="pdf-viewer-close"></button>
        </div>
    </div>

    <!-- Toolbar Win98: zoom + dados + guardar -->
    <div id="pdf-viewer-toolbar">
        <button class="button" id="pdf-zoom-out" title="Alejar">−</button>
        <span id="pdf-zoom-label">100%</span>
        <button class="button" id="pdf-zoom-in" title="Acercar">+</button>
        <button class="button" id="pdf-zoom-reset" title="Ajustar">⛶</button>
        <div class="tb-sep"></div>
        <button class="button" id="pdf-dice-toggle" title="Lanzar dados"><img src="../../assets/img/appIcons/dndIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></button>
        <button class="button" id="pdf-save" title="Guardar en Drive">💾</button>
        <button class="button" id="pdf-back-menu" title="Volver al menú principal" style="margin-left:auto;">‹ Menú</button>
    </div>

    <div id="pdf-viewer-canvas-wrap" style="position:relative;">
        <div id="pdf-page-wrap">
            <canvas id="pdf-viewer-canvas"></canvas>
            <div id="pdf-form-layer"></div>
        </div>
        <div id="pdf-viewer-loading" style="display:none;">⏳ Cargando…</div>

        <!-- ────── DICE OVERLAY ────── ventana Win98 flotante con
             lanzador de dados Three.js. Sólo se monta cuando se abre
             (lazy load de three.js). Posicionada fija dentro del visor. -->
        <div id="dice-overlay-m" class="window">
            <div class="title-bar">
                <div class="title-bar-text"><img src="../../assets/img/appIcons/dndIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Lanzar dados</div>
                <div class="title-bar-controls">
                    <button aria-label="Close" id="dice-overlay-close"></button>
                </div>
            </div>
            <div class="window-body" style="margin:0;padding:6px;display:flex;flex-direction:column;gap:6px;flex:1;min-height:0;">
                <div id="dice-buttons-row">
                    <button class="button dice-btn-m" data-sides="4">d4</button>
                    <button class="button dice-btn-m" data-sides="6">d6</button>
                    <button class="button dice-btn-m" data-sides="8">d8</button>
                    <button class="button dice-btn-m" data-sides="10">d10</button>
                    <button class="button dice-btn-m" data-sides="12">d12</button>
                    <button class="button dice-btn-m" data-sides="20">d20</button>
                    <button class="button dice-btn-m" data-sides="100">d100</button>
                </div>
                <div id="dice-stage-m">
                    <canvas id="dice-canvas-m"></canvas>
                    <div id="dice-overlays-m"></div>
                    <div id="dice-hint-m">Pulsa un dado para añadirlo · pulsa el dado para quitarlo</div>
                </div>
                <div id="dice-actions-row">
                    <button class="button" id="dice-reroll-m"><img src="../../assets/img/appIcons/dndIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Tirar</button>
                    <button class="button" id="dice-clear-m"><img src="../../assets/img/appIcons/trashIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Limpiar</button>
                    <button class="button" id="dice-mod-btn-m" title="Modificador">＋/−</button>
                </div>
                <div id="dice-result-m">Elige un dado</div>
            </div>
        </div>
    </div>
    <div id="pdf-viewer-pagebar">
        <button class="button" id="pdf-prev">‹</button>
        <div id="pdf-viewer-pageinfo">– / –</div>
        <button class="button" id="pdf-next">›</button>
    </div>
</div>

<!-- Modal "Nueva ficha" — pregunta qué tipo de hoja crear. -->
<div id="new-sheet-modal">
    <div class="window" id="new-sheet-box">
        <div class="title-bar">
            <div class="title-bar-text">＋ Nueva ficha</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="new-sheet-close"></button>
            </div>
        </div>
        <div class="window-body">
            <p style="font-size:11px;margin:0 0 12px;">¿Qué tipo de hoja quieres crear?</p>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button class="button" id="new-sheet-oficial" style="min-height:40px;font-size:13px;text-align:left;padding:6px 12px;">
                    📜 <strong>Oficial</strong>
                    <span style="display:block;font-size:10px;color:var(--text-muted,#666);margin-top:2px;">Ficha estándar D&amp;D 5e (5 páginas)</span>
                </button>
                <button class="button" id="new-sheet-melon" style="min-height:40px;font-size:13px;text-align:left;padding:6px 12px;">
                    🎨 <strong>Melon</strong>
                    <span style="display:block;font-size:10px;color:var(--text-muted,#666);margin-top:2px;">Versión alternativa con fuente Allison</span>
                </button>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                <button class="button" id="new-sheet-cancel">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal modificador de dados -->
<div id="dice-mod-modal-m">
    <div class="window gal-dialog" id="dice-mod-box-m" style="display:flex;flex-direction:column;position:relative;">
        <div class="title-bar">
            <div class="title-bar-text">＋ Modificador</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="dice-mod-close-m"></button>
            </div>
        </div>
        <div class="window-body">
            <p style="font-size:11px;margin:0 0 8px;">Suma fija al total de la tirada.</p>
            <input type="number" id="dice-mod-input-m" value="0" step="1">
            <div style="display:flex;gap:6px;justify-content:flex-end;margin-top:12px;">
                <button class="button" id="dice-mod-cancel-m">Cancelar</button>
                <button class="button default" id="dice-mod-apply-m">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast-m"></div>

<!-- PDF.js para el visor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
/* ════════════════════════════════════════
   CONFIG GENERAL
════════════════════════════════════════ */
var GOOGLE_CLIENT_ID = <?php echo json_encode(GOOGLE_CLIENT_ID); ?>;
var GDRIVE_SCOPE     = 'https://www.googleapis.com/auth/drive.file';
var GDRIVE_FOLDER    = 'D&D Fichas';

var DRIVE_TOKEN_KEY     = 'dnd_drive_token';
var DRIVE_EVER_AUTH_KEY = 'dnd_drive_ever_auth';

/* Plantillas de ficha en blanco para "Nueva ficha". Mismas URLs que el
   escritorio + defaultFont del tipo (Helvetica para la oficial, Allison
   para la melon). El defaultFont sirve de fallback si el campo del PDF
   no especifica fuente propia en defaultAppearanceData. */
var SHEETS = {
    oficial: {
        pdfUrl:      '../../assets/pdf/Hoja_de_personaje_Editable.pdf',
        defaultFont: 'Helvetica, Arial, sans-serif',
        label:       '📜 Oficial'
    },
    melon: {
        pdfUrl:      '../../assets/pdf/Hoja DND.pdf',
        defaultFont: "'Allison', cursive",
        label:       '🎨 Melon'
    }
};

/* Mapea fontName del AcroForm (defaultAppearanceData.fontName) a una
   familia CSS razonable. Helv/HeBo → Helvetica, Tibo/Times → Times, etc.
   Si el campo no tiene fuente, cae al defaultFont del sheet (Allison para
   melon, Helvetica para oficial). */
function mapPdfFont(name, defaultFont){
    var fallback = defaultFont || 'Helvetica, Arial, sans-serif';
    if (!name) return fallback;
    var n = String(name).toLowerCase();
    if (n.indexOf('helv') !== -1 || n.indexOf('hebo') !== -1 ||
        n.indexOf('arial') !== -1) return fallback;
    if (n.indexOf('time') !== -1 || n.indexOf('roman') !== -1 ||
        n.indexOf('tibo') !== -1) return "'Times New Roman', Times, serif";
    if (n.indexOf('cour') !== -1 || n.indexOf('mono') !== -1) return "'Courier New', Courier, monospace";
    if (n.indexOf('allison') !== -1) return "'Allison', cursive";
    return fallback;
}

/* ════════════════════════════════════════
   CONSTANTES DE CÁLCULO — mismos campos que escritorio (apps/dnd.php).
   El AcroForm de cada hoja tiene scripts /C internos que replicamos
   aquí en JS para que los campos auto-calculados se actualicen al
   editar los valores base (puntuaciones, ProfBonus, checkboxes).
════════════════════════════════════════ */
var ABILITIES = ['STR','DEX','CON','INT','WIS','CHA'];

/* Hoja OFICIAL: skill → {stat de base, checkbox de competencia}. */
var SKILL_MAP = {
    Acrobatics:    { stat:'DEX', prof:'acroPROF'    },
    AnHan:         { stat:'WIS', prof:'anhanPROF'   },
    Arcana:        { stat:'INT', prof:'arcanaPROF'  },
    Athletics:     { stat:'STR', prof:'athPROF'     },
    Deception:     { stat:'CHA', prof:'decepPROF'   },
    History:       { stat:'INT', prof:'histPROF'    },
    Insight:       { stat:'WIS', prof:'insightPROF' },
    Intimidation:  { stat:'CHA', prof:'intimPROF'   },
    Investigation: { stat:'INT', prof:'investPROF'  },
    Medicine:      { stat:'WIS', prof:'medPROF'     },
    Nature:        { stat:'INT', prof:'naturePROF'  },
    Perception:    { stat:'WIS', prof:'perPROF'     },
    Performance:   { stat:'CHA', prof:'perfPROF'    },
    Persuasion:    { stat:'CHA', prof:'persPROF'    },
    Religion:      { stat:'INT', prof:'religPROF'   },
    SleightofHand: { stat:'DEX', prof:'sohPROF'     },
    Stealth:       { stat:'DEX', prof:'stealthPROF' },
    Survival:      { stat:'WIS', prof:'survPROF'    }
};
var CALC_FIELDS = [
    'STRbonus','DEXbonus','CONbonus','INTbonus','WISbonus','CHAbonus',
    'STRsave','DEXsave','CONsave','INTsave','WISsave','CHAsave',
    'Acrobatics','AnHan','Arcana','Athletics','Deception','History','Insight',
    'Intimidation','Investigation','Medicine','Nature','Perception',
    'Performance','Persuasion','Religion','SleightofHand','Stealth','Survival',
    'PWP','SpellSaveDC','SAB'
];
function isCalcField(name){ return CALC_FIELDS.indexOf(name) !== -1; }

/* Hoja MELON: los nombres de campo son opacos (doc_0_doc_0_Text_NN);
   mapeamos cada uno a su semántica STR/DEX/etc. */
var MELON_PROF_FIELD    = 'doc_0_doc_0_Text_25';
var MELON_SPELL_ABILITY = 'Text_1';   // página 3 (root, sin prefijo)
var MELON_SPELL_DC      = 'Text_2';
var MELON_SPELL_ATK     = 'Text_3';
var MELON_MAP = {
    STR: { score: 'doc_0_doc_0_Text_58', bonus: 'doc_0_doc_0_Text_52', save: 'doc_0_doc_0_Text_29', savePROF: 'doc_0_doc_0_Checkbox_1' },
    DEX: { score: 'doc_0_doc_0_Text_59', bonus: 'doc_0_doc_0_Text_53', save: 'doc_0_doc_0_Text_30', savePROF: 'doc_0_doc_0_Checkbox_2' },
    CON: { score: 'doc_0_doc_0_Text_60', bonus: 'doc_0_doc_0_Text_54', save: 'doc_0_doc_0_Text_31', savePROF: 'doc_0_doc_0_Checkbox_3' },
    INT: { score: 'doc_0_doc_0_Text_61', bonus: 'doc_0_doc_0_Text_55', save: 'doc_0_doc_0_Text_32', savePROF: 'doc_0_doc_0_Checkbox_4' },
    WIS: { score: 'doc_0_doc_0_Text_62', bonus: 'doc_0_doc_0_Text_56', save: 'doc_0_doc_0_Text_33', savePROF: 'doc_0_doc_0_Checkbox_5' },
    CHA: { score: 'doc_0_doc_0_Text_63', bonus: 'doc_0_doc_0_Text_57', save: 'doc_0_doc_0_Text_34', savePROF: 'doc_0_doc_0_Checkbox_6' }
};
var MELON_SKILL_MAP = [
    { name: 'Acrobacias',  stat: 'DEX', field: 'doc_0_doc_0_Text_35', prof: 'doc_0_doc_0_Checkbox_7'  },
    { name: 'Aguante',     stat: 'CON', field: 'doc_0_doc_0_Text_36', prof: 'doc_0_doc_0_Checkbox_8'  },
    { name: 'Atletismo',   stat: 'STR', field: 'doc_0_doc_0_Text_37', prof: 'doc_0_doc_0_Checkbox_9'  },
    { name: 'JuegoManos',  stat: 'DEX', field: 'doc_0_doc_0_Text_38', prof: 'doc_0_doc_0_Checkbox_10' },
    { name: 'Sigilo',      stat: 'DEX', field: 'doc_0_doc_0_Text_39', prof: 'doc_0_doc_0_Checkbox_11' },
    { name: 'TAnimales',   stat: 'WIS', field: 'doc_0_doc_0_Text_40', prof: 'doc_0_doc_0_Checkbox_12' },
    { name: 'Percepcion',  stat: 'WIS', field: 'doc_0_doc_0_Text_41', prof: 'doc_0_doc_0_Checkbox_13', passive: true },
    { name: 'Perspicacia', stat: 'CHA', field: 'doc_0_doc_0_Text_42', prof: 'doc_0_doc_0_Checkbox_14', passive: true },
    { name: 'CArcano',     stat: 'INT', field: 'doc_0_doc_0_Text_43', prof: 'doc_0_doc_0_Checkbox_15', passive: true }
];
var MELON_CALC_FIELDS = (function(){
    var f = [];
    Object.keys(MELON_MAP).forEach(function(k){ f.push(MELON_MAP[k].bonus, MELON_MAP[k].save); });
    MELON_SKILL_MAP.forEach(function(s){ f.push(s.field); });
    f.push(MELON_SPELL_DC, MELON_SPELL_ATK);
    return f;
})();
function isMelonCalcField(name){ return MELON_CALC_FIELDS.indexOf(name) !== -1; }

/* ════════════════════════════════════════
   HELPERS de acceso a campos — CRUZAN páginas usando _pdfFormData.
   En móvil solo renderizamos UNA página a la vez, así que muchos
   campos referenciados por los cálculos no están en el DOM. El
   patrón es: leer del DOM si existe (input visible), si no caer en
   _pdfFormData. Escribir SIEMPRE en _pdfFormData; actualizar el DOM
   también si está visible. Así los cálculos persisten entre páginas.
════════════════════════════════════════ */
function _qField(name){
    return document.querySelector('#pdf-form-layer [data-field-name="'+ name.replace(/"/g,'\\"') +'"]');
}
function _fldVal(name){
    var el = _qField(name);
    if (el) return el.value;
    return _pdfFormData[name] != null ? _pdfFormData[name] : '';
}
function _fldNum(name){
    var v = _fldVal(name);
    v = (v||'').toString().replace(/^\+/,'').trim();
    if (v === '') return null;
    var n = parseFloat(v); return isNaN(n) ? null : n;
}
function _fldChk(name){
    var el = _qField(name);
    if (el) return !!el.checked;
    return !!_pdfFormData[name];
}
function _fldSet(name, val){
    var v = (val === null || val === undefined) ? '' : String(val);
    _pdfFormData[name] = v;
    var el = _qField(name);
    if (el && el.value !== v) el.value = v;
}
function _fmtMod(n){ return n > 0 ? '+'+n : String(n); }

/* ── Despachador de cálculos según tipo de ficha ── */
var _runCalcsScheduled = false;
function runCalcs(){
    /* Pequeño debounce con rAF para que cambios consecutivos (typing,
       toggling) no disparen N pasadas. */
    if (_runCalcsScheduled) return;
    _runCalcsScheduled = true;
    requestAnimationFrame(function(){
        _runCalcsScheduled = false;
        if (_pdfSheetType === 'melon') runCalcsMelon();
        else if (_pdfSheetType === 'oficial') runCalcsOficial();
    });
}

function runCalcsOficial(){
    var prof = _fldNum('ProfBonus');
    var bonuses = {};
    ABILITIES.forEach(function(ab){
        var score = _fldNum(ab+'score');
        if (score === null){
            bonuses[ab] = null;
            _fldSet(ab+'bonus', '');
            _fldSet(ab+'save',  '');
            return;
        }
        var bonus = Math.floor((score - 10) / 2);
        bonuses[ab] = bonus;
        _fldSet(ab+'bonus', _fmtMod(bonus));
        var sv = bonus + (_fldChk(ab+'savePROF') && prof !== null ? prof : 0);
        _fldSet(ab+'save', _fmtMod(sv));
    });
    Object.keys(SKILL_MAP).forEach(function(skill){
        var m = SKILL_MAP[skill]; var bn = bonuses[m.stat];
        if (bn === null){ _fldSet(skill, ''); return; }
        var v = bn + (_fldChk(m.prof) && prof !== null ? prof : 0);
        _fldSet(skill, _fmtMod(v));
    });
    if (bonuses.WIS === null) _fldSet('PWP', '');
    else _fldSet('PWP', String(10 + bonuses.WIS + (_fldChk('perPROF') && prof !== null ? prof : 0)));
    var saMap = { '1':'INT', '2':'WIS', '3':'CHA' };
    var sk = saMap[_fldVal('SpellAbility')];
    if (sk && bonuses[sk] !== null && prof !== null){
        _fldSet('SpellSaveDC', String(8 + prof + bonuses[sk]));
        _fldSet('SAB',         _fmtMod(prof + bonuses[sk]));
    } else {
        _fldSet('SpellSaveDC', '');
        _fldSet('SAB',         '');
    }
}

function runCalcsMelon(){
    var prof = _fldNum(MELON_PROF_FIELD);
    var bonuses = {};
    ABILITIES.forEach(function(ab){
        var m = MELON_MAP[ab]; if (!m) return;
        var score = _fldNum(m.score);
        if (score === null){
            bonuses[ab] = null;
            _fldSet(m.bonus, '');
            _fldSet(m.save,  '');
            return;
        }
        var bonus = Math.floor((score - 10) / 2);
        bonuses[ab] = bonus;
        _fldSet(m.bonus, _fmtMod(bonus));
        var sv = bonus + (_fldChk(m.savePROF) && prof !== null ? prof : 0);
        _fldSet(m.save, _fmtMod(sv));
    });
    MELON_SKILL_MAP.forEach(function(s){
        var b = bonuses[s.stat];
        if (b === null || b === undefined){ _fldSet(s.field, ''); return; }
        var extra = (_fldChk(s.prof) && prof !== null) ? prof : 0;
        var v = b + extra;
        _fldSet(s.field, s.passive ? String(10 + v) : _fmtMod(v));
    });
    var stat = _fldVal(MELON_SPELL_ABILITY);
    var bn = bonuses[stat];
    if (stat && bn !== null && bn !== undefined && prof !== null){
        _fldSet(MELON_SPELL_DC,  String(8 + prof + bn));
        _fldSet(MELON_SPELL_ATK, _fmtMod(prof + bn));
    } else {
        _fldSet(MELON_SPELL_DC,  '');
        _fldSet(MELON_SPELL_ATK, '');
    }
}

var _tokenClient   = null;
var _driveToken    = null;
var _driveFolderId = null;
var _pendingDriveCb = null;
var _silentRefreshTimer    = null;
var _silentRefreshInFlight = false;
var _driveFichasCache = [];

if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
}

/* ════════════════════════════════════════
   UTILIDADES
════════════════════════════════════════ */
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function loadScript(src){
    return new Promise(function(res, rej){
        var s = document.createElement('script');
        s.src = src; s.onload = res; s.onerror = rej;
        document.head.appendChild(s);
    });
}
var _toastTimer = null;
function showToast(msg){
    var t = document.getElementById('toast-m');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(function(){ t.classList.remove('show'); }, 2200);
}

/* ════════════════════════════════════════
   GOOGLE DRIVE (mismo flujo que escritorio)
════════════════════════════════════════ */
(function tryRestoreToken(){
    try {
        var s = localStorage.getItem(DRIVE_TOKEN_KEY);
        if (!s) return;
        var t = JSON.parse(s);
        if (t && t.expires_at > Date.now() + 30000) _driveToken = t;
    } catch(e){}
})();

function _initTokenClient(){
    if (_tokenClient) return _tokenClient;
    if (typeof google === 'undefined' || !google.accounts || !google.accounts.oauth2) return null;
    _tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: GOOGLE_CLIENT_ID,
        scope:     GDRIVE_SCOPE,
        callback: function(resp){
            if (resp.error){
                if (!_silentRefreshInFlight) showToast('✗ Drive: '+resp.error);
                _silentRefreshInFlight = false;
                _pendingDriveCb = null;
                return;
            }
            _driveToken = {
                access_token: resp.access_token,
                expires_at:   Date.now() + (resp.expires_in * 1000) - 60000
            };
            localStorage.setItem(DRIVE_TOKEN_KEY, JSON.stringify(_driveToken));
            localStorage.setItem(DRIVE_EVER_AUTH_KEY, '1');
            updateDriveStatus();
            scheduleSilentRefresh();
            if (!_silentRefreshInFlight) showToast('☁ Drive conectado');
            _silentRefreshInFlight = false;
            if (_pendingDriveCb){ var fn = _pendingDriveCb; _pendingDriveCb = null; fn(); }
            /* Si el silent refresh completa AHORA y el usuario está en
               la pestaña Mis Fichas sin lista, dispara el fetch para
               que vea sus fichas sin tener que tocar nada. */
            else {
                var fichasPanel = document.getElementById('dnd-fichas');
                if (fichasPanel && fichasPanel.classList.contains('is-active') && !_driveFichasCache.length){
                    fetchAndRenderFichas();
                }
            }
        }
    });
    return _tokenClient;
}

function scheduleSilentRefresh(){
    clearTimeout(_silentRefreshTimer);
    if (!_driveToken) return;
    var ms = _driveToken.expires_at - Date.now() - 5*60*1000;
    if (ms < 1000) ms = 1000;
    _silentRefreshTimer = setTimeout(silentRefresh, ms);
}
function silentRefresh(){
    var c = _initTokenClient();
    if (!c) return;
    _silentRefreshInFlight = true;
    try { c.requestAccessToken({ prompt: '' }); }
    catch(e){ _silentRefreshInFlight = false; }
}
function tryAutoConnectDrive(){
    if (!localStorage.getItem(DRIVE_EVER_AUTH_KEY)) return;
    if (_driveToken && _driveToken.expires_at > Date.now() + 30000){
        scheduleSilentRefresh();
        updateDriveStatus();
        return;
    }
    var attempts = 0;
    var iv = setInterval(function(){
        attempts++;
        if (typeof google !== 'undefined' && google.accounts && google.accounts.oauth2){
            clearInterval(iv);
            silentRefresh();
        } else if (attempts > 60){
            clearInterval(iv);
        }
    }, 250);
}
function ensureDriveAuth(fn){
    if (_driveToken && _driveToken.expires_at > Date.now() + 30000){ fn(); return; }
    _pendingDriveCb = fn;
    var c = _initTokenClient();
    if (!c){
        var attempts = 0;
        var iv = setInterval(function(){
            attempts++;
            c = _initTokenClient();
            if (c){ clearInterval(iv); c.requestAccessToken({ prompt: '' }); }
            else if (attempts > 40){
                clearInterval(iv);
                showToast('✗ No se pudo cargar Google Identity Services');
                _pendingDriveCb = null;
            }
        }, 250);
        return;
    }
    c.requestAccessToken({ prompt: '' });
}
function disconnectDrive(){
    if (_driveToken && typeof google !== 'undefined' && google.accounts){
        try { google.accounts.oauth2.revoke(_driveToken.access_token, function(){}); } catch(e){}
    }
    _driveToken = null;
    clearTimeout(_silentRefreshTimer);
    localStorage.removeItem(DRIVE_TOKEN_KEY);
    localStorage.removeItem(DRIVE_EVER_AUTH_KEY);
    _driveFichasCache = [];
    _driveFolderId = null;
    updateDriveStatus();
    renderFichasList();
    showToast('Drive desconectado');
}
function updateDriveStatus(){
    var s = document.getElementById('drive-status-m');
    if (!s) return;
    var connected = !!(_driveToken && _driveToken.expires_at > Date.now());
    s.textContent = connected ? '● Conectado' : '';
}

async function driveFetch(url, opts, retried){
    opts = opts || {};
    opts.headers = Object.assign({}, opts.headers || {}, {
        Authorization: 'Bearer ' + _driveToken.access_token
    });
    var r = await fetch(url, opts);
    if (r.status === 401 && !retried){
        _driveToken = null;
        localStorage.removeItem(DRIVE_TOKEN_KEY);
        return new Promise(function(resolve, reject){
            ensureDriveAuth(function(){ driveFetch(url, opts, true).then(resolve, reject); });
        });
    }
    if (!r.ok){
        var t = ''; try { t = await r.text(); } catch(e){}
        throw new Error('Drive '+r.status+': '+t.slice(0,160));
    }
    return r;
}

async function ensureDriveFolder(){
    if (_driveFolderId) return _driveFolderId;
    var q = "mimeType='application/vnd.google-apps.folder' and name='" + GDRIVE_FOLDER.replace(/'/g,"\\'") + "' and trashed=false";
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) + '&fields=files(id,name)&spaces=drive');
    var d = await r.json();
    if (d.files && d.files.length){ _driveFolderId = d.files[0].id; return _driveFolderId; }
    var cr = await driveFetch('https://www.googleapis.com/drive/v3/files', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: GDRIVE_FOLDER, mimeType: 'application/vnd.google-apps.folder' })
    });
    var c = await cr.json();
    _driveFolderId = c.id;
    return _driveFolderId;
}

/* ════════════════════════════════════════
   MIS FICHAS — listado y carga
════════════════════════════════════════ */
function fetchAndRenderFichas(){
    var list = document.getElementById('mf-list-m');
    list.innerHTML = '<div id="mf-empty-m">☁ Cargando desde Drive…</div>';
    ensureDriveAuth(async function(){
        try {
            var folderId = await ensureDriveFolder();
            var q = "'" + folderId + "' in parents and trashed=false and mimeType='application/pdf'";
            var r = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                                     '&fields=files(id,name,modifiedTime,size)&orderBy=modifiedTime desc');
            var d = await r.json();
            _driveFichasCache = d.files || [];
            renderFichasList();
        } catch(e){
            list.innerHTML = '<div id="mf-empty-m">✗ Error de Drive: ' + esc(e.message) + '</div>';
        }
    });
}

function renderFichasList(){
    var busqEl = document.getElementById('mf-search-m');
    var busq = (busqEl ? busqEl.value : '').toLowerCase();
    var fichas = _driveFichasCache.filter(function(f){
        return !busq || f.name.toLowerCase().indexOf(busq) !== -1;
    });
    var list = document.getElementById('mf-list-m'); list.innerHTML = '';
    if (!fichas.length){
        var connected = !!(_driveToken && _driveToken.expires_at > Date.now());
        var e = document.createElement('div'); e.id = 'mf-empty-m';
        if (!connected) {
            e.innerHTML = '☁ Conecta tu Google Drive para ver tus fichas.<br><br>'+
                '<button class="button default" id="mf-connect-btn">☁ Conectar con Drive</button>';
        } else if (busq) {
            e.innerHTML = '🔍 Sin resultados para "<strong>'+esc(busq)+'</strong>".';
        } else {
            e.innerHTML = '☁ No hay fichas en tu Drive.<br><small>Guarda fichas desde la versión escritorio.</small>';
        }
        list.appendChild(e);
        var btn = document.getElementById('mf-connect-btn');
        if (btn) btn.addEventListener('click', fetchAndRenderFichas);
        return;
    }
    fichas.forEach(function(f){
        var tipo = /_melon\.pdf$/i.test(f.name) ? 'melon'
                 : /_oficial\.pdf$/i.test(f.name) ? 'oficial'
                 : 'otro';
        var nombreLimpio = f.name.replace(/_(oficial|melon)\.pdf$/i,'').replace(/\.pdf$/i,'').replace(/_/g,' ');
        var fecha = new Date(f.modifiedTime).toLocaleDateString('es-ES', {day:'2-digit',month:'2-digit',year:'numeric'});
        var tipoLbl = tipo==='oficial'?'📜 Oficial':tipo==='melon'?'🎨 Melon':'❓ Otro';
        var card = document.createElement('div'); card.className = 'ficha-card-m';
        card.innerHTML =
            '<div class="fc-tipo-m">'+tipoLbl+'</div>'+
            '<div class="fc-nombre-m">'+esc(nombreLimpio)+'</div>'+
            '<div class="fc-fecha-m">📅 '+esc(fecha)+'</div>'+
            '<div class="fc-actions-m">'+
                '<button class="button" data-a="ver">👁 Ver</button>'+
                '<button class="button danger" data-a="del">✕</button>'+
            '</div>';
        card.querySelector('[data-a="ver"]').addEventListener('click', function(){ openPdfViewer(f); });
        card.querySelector('[data-a="del"]').addEventListener('click', function(){
            if (confirm('¿Eliminar "'+nombreLimpio+'" de Drive?')) deleteDriveFile(f);
        });
        list.appendChild(card);
    });
}

async function deleteDriveFile(f){
    try {
        await driveFetch('https://www.googleapis.com/drive/v3/files/' + f.id, { method: 'DELETE' });
        _driveFichasCache = _driveFichasCache.filter(function(x){ return x.id !== f.id; });
        renderFichasList();
        showToast('Eliminada de Drive');
    } catch(e){ showToast('✗ '+e.message); }
}

/* ════════════════════════════════════════
   VISOR PDF EDITABLE (PDF.js + pdf-lib)

   Flujo:
     1. openPdfViewer baja los bytes del PDF de Drive y los guarda en
        _pdfOriginalBytes (necesarios para escribir de vuelta con pdf-lib).
     2. Detecta el tipo de ficha desde el nombre (_oficial.pdf / _melon.pdf
        / otro) para elegir el defaultFont.
     3. renderPdfPage pinta la página en canvas + crea overlays editables
        (input/textarea/checkbox/select) sobre cada widget AcroForm,
        cogiendo posición + fuente + tamaño desde PDF.js.
     4. Los valores editados se guardan en _pdfFormData (clave fieldName).
     5. savePdfToDrive usa pdf-lib para inyectar los valores en el PDF
        original y subirlo de vuelta a Drive (PATCH si ya existía).
════════════════════════════════════════ */
var _pdfDoc            = null;
var _pdfPage           = 1;
var _pdfNumPages       = 0;
var _pdfRendering      = false;
var _pdfOriginalBytes  = null;   // bytes ORIGINALES del PDF (para escribir back)
var _pdfFile           = null;   // metadatos del file en Drive {id, name}
var _pdfSheetType      = 'otro'; // 'oficial' | 'melon' | 'otro' — afecta a defaultFont
var _pdfFormData       = {};     // fieldName → valor actual

/* Multiplicador de zoom del usuario sobre la escala "fit-to-width".
   1.0 = la hoja ocupa el ancho disponible. >1 hace scroll, <1 centra. */
var _pdfUserZoom       = 1.0;
var _pdfZoomRebuildTimer = null;

/* Detecta el tipo de ficha a partir del nombre del fichero. */
function _detectSheetType(filename){
    if (/_oficial\.pdf$/i.test(filename)) return 'oficial';
    if (/_melon\.pdf$/i.test(filename))   return 'melon';
    return 'otro';
}

async function openPdfViewer(f){
    var v = document.getElementById('pdf-viewer');
    v.classList.add('is-open');
    document.getElementById('pdf-viewer-title').textContent = (
        _detectSheetType(f.name) === 'melon' ? '🎨 ' :
        _detectSheetType(f.name) === 'oficial' ? '📜 ' : '📄 '
    ) + f.name;
    document.getElementById('pdf-viewer-loading').style.display = 'flex';
    document.getElementById('pdf-viewer-pageinfo').textContent = '– / –';
    document.getElementById('pdf-form-layer').innerHTML = '';

    _pdfFile = f;
    _pdfSheetType = _detectSheetType(f.name);
    _pdfFormData = {};

    try {
        var r = await driveFetch('https://www.googleapis.com/drive/v3/files/' + f.id + '?alt=media');
        _pdfOriginalBytes = await r.arrayBuffer();
        /* Copia para PDF.js (consume el buffer). pdf-lib usará el original. */
        var copy = _pdfOriginalBytes.slice(0);
        _pdfDoc = await pdfjsLib.getDocument({ data: copy }).promise;
        _pdfNumPages = _pdfDoc.numPages;
        _pdfPage = 1;
        await renderPdfPage();
    } catch(e){
        document.getElementById('pdf-viewer-loading').style.display = 'none';
        showToast('✗ '+e.message);
    }
}

/* Abre el visor desde una plantilla en blanco (Nueva ficha) sin pasar
   por Drive — el guardado lo subirá nuevo (POST en lugar de PATCH). */
async function openBlankSheet(sheetKey){
    var sheet = SHEETS[sheetKey]; if (!sheet) return;
    var v = document.getElementById('pdf-viewer');
    v.classList.add('is-open');
    document.getElementById('pdf-viewer-title').textContent = sheet.label + ' — nueva ficha';
    document.getElementById('pdf-viewer-loading').style.display = 'flex';
    document.getElementById('pdf-viewer-pageinfo').textContent = '– / –';
    document.getElementById('pdf-form-layer').innerHTML = '';

    _pdfFile = null;             // sin file en Drive todavía → POST nuevo
    _pdfSheetType = sheetKey;
    _pdfFormData = {};

    try {
        var r = await fetch(sheet.pdfUrl);
        _pdfOriginalBytes = await r.arrayBuffer();
        var copy = _pdfOriginalBytes.slice(0);
        _pdfDoc = await pdfjsLib.getDocument({ data: copy }).promise;
        _pdfNumPages = _pdfDoc.numPages;
        _pdfPage = 1;
        await renderPdfPage();
    } catch(e){
        document.getElementById('pdf-viewer-loading').style.display = 'none';
        showToast('✗ '+e.message);
    }
}

async function renderPdfPage(){
    if (!_pdfDoc || _pdfRendering) return;
    _pdfRendering = true;
    document.getElementById('pdf-viewer-loading').style.display = 'flex';
    document.getElementById('pdf-form-layer').innerHTML = '';
    try {
        var page = await _pdfDoc.getPage(_pdfPage);
        var canvas = document.getElementById('pdf-viewer-canvas');
        var ctx = canvas.getContext('2d');
        var wrap = document.getElementById('pdf-viewer-canvas-wrap');
        /* availW: ancho útil del área de visualización (descontamos
           padding + 4px por las sombras del page-wrap). El fit-scale
           base hace que la hoja quepa exactamente en ese ancho con
           zoom 100%. _pdfUserZoom multiplica por encima de eso. */
        var availW = wrap.clientWidth - 28;
        var viewport0 = page.getViewport({ scale: 1 });
        var fitScale = availW / viewport0.width;
        var scale = Math.max(0.1, fitScale * _pdfUserZoom);
        var dpr = window.devicePixelRatio || 1;
        var renderViewport = page.getViewport({ scale: scale * dpr });
        var cssViewport    = page.getViewport({ scale: scale });

        canvas.width  = renderViewport.width;
        canvas.height = renderViewport.height;
        canvas.style.width  = cssViewport.width  + 'px';
        canvas.style.height = cssViewport.height + 'px';

        /* annotationMode: DISABLE → no pinta los widgets AcroForm dentro
           del canvas (que dejaba el texto original "fantasma" visible
           detrás de los inputs editables — sobre todo notable en la
           hoja melon con la fuente Allison cursiva). Los widgets siguen
           leyéndose vía page.getAnnotations() para crear la capa
           editable; aquí solo evitamos que se IMPRIMAN. */
        var ANNOT_DISABLE = (pdfjsLib.AnnotationMode && pdfjsLib.AnnotationMode.DISABLE) || 0;
        await page.render({
            canvasContext: ctx,
            viewport: renderViewport,
            annotationMode: ANNOT_DISABLE
        }).promise;

        /* Construir la capa de inputs editables usando CSS-viewport
           (sin el factor DPR) para que las coordenadas coincidan con
           los píxeles CSS del canvas. */
        var pageWrap = document.getElementById('pdf-page-wrap');
        pageWrap.style.width  = cssViewport.width  + 'px';
        pageWrap.style.height = cssViewport.height + 'px';
        var layer = document.getElementById('pdf-form-layer');
        var annotations = await page.getAnnotations();
        annotations.forEach(function(annot){
            if (annot.subtype !== 'Widget') return;
            if (!annot.fieldName) return;
            createFormField(layer, annot, cssViewport);
        });

        document.getElementById('pdf-viewer-pageinfo').textContent = _pdfPage + ' / ' + _pdfNumPages;

        /* Recalcular para que los campos auto-calc de esta página
           muestren los valores correctos en cuanto se rendericen
           (los inputs base pueden estar en otra página y vivir en
           _pdfFormData). */
        runCalcs();
    } finally {
        _pdfRendering = false;
        document.getElementById('pdf-viewer-loading').style.display = 'none';
    }
}

/* Crea un input/textarea/checkbox/select editable sobre el widget AcroForm. */
function createFormField(layer, annot, viewport){
    var r = pdfjsLib.Util.normalizeRect(annot.rect);
    var tl = viewport.convertToViewportPoint(r[0], r[3]);
    var br = viewport.convertToViewportPoint(r[2], r[1]);
    var left   = Math.min(tl[0], br[0]);
    var top    = Math.min(tl[1], br[1]);
    var width  = Math.abs(br[0] - tl[0]);
    var height = Math.abs(br[1] - tl[1]);

    var el = null;
    var ft = annot.fieldType;
    var spellAbilityOverride = false;

    /* OVERRIDE: en la hoja melon el campo Aptitud Mágica se presenta
       SIEMPRE como dropdown CHA/SAB/INT, aunque el PDF original lo
       defina como text field. La opción "—" representa "sin aptitud".
       (Replica el comportamiento del escritorio en apps/dnd.php.) */
    if (_pdfSheetType === 'melon' && annot.fieldName === MELON_SPELL_ABILITY){
        el = document.createElement('select');
        el.className = 'spell-ability';
        [['','—'],['CHA','Carisma'],['WIS','Sabiduría'],['INT','Inteligencia']].forEach(function(o){
            var opt = document.createElement('option');
            opt.value = o[0]; opt.textContent = o[1];
            el.appendChild(opt);
        });
        spellAbilityOverride = true;
    }
    else if (ft === 'Tx'){
        if (annot.multiLine){ el = document.createElement('textarea'); }
        else { el = document.createElement('input'); el.type = 'text'; }
        if (annot.maxLen) el.maxLength = annot.maxLen;
    } else if (ft === 'Btn'){
        if (annot.checkBox){
            el = document.createElement('input'); el.type = 'checkbox';
        } else if (annot.radioButton){
            el = document.createElement('input'); el.type = 'radio';
            el.name = annot.fieldName;
            el.value = annot.buttonValue || 'On';
        } else { return; }
    } else if (ft === 'Ch'){
        el = document.createElement('select');
        var blank = document.createElement('option');
        blank.value = ''; blank.textContent = '—';
        el.appendChild(blank);
        (annot.options || []).forEach(function(o){
            var opt = document.createElement('option');
            opt.value = o.exportValue != null ? o.exportValue : o.displayValue;
            opt.textContent = o.displayValue;
            el.appendChild(opt);
        });
    } else { return; }

    el.dataset.fieldName = annot.fieldName;

    /* Recuperar el valor actual: primero del estado en memoria
       (_pdfFormData), si no del PDF original (annot.fieldValue). */
    var stored = _pdfFormData[annot.fieldName];
    var v = (stored !== undefined) ? stored : annot.fieldValue;
    if (el.type === 'checkbox'){
        el.checked = stored !== undefined ? !!stored : (!!v && v !== 'Off');
    } else if (el.type === 'radio'){
        el.checked = stored !== undefined ? (stored === el.value) : (v === el.value);
    } else if (spellAbilityOverride){
        /* Solo aceptar valores válidos del dropdown CHA/WIS/INT. */
        if (typeof v === 'string' && ['CHA','WIS','INT'].indexOf(v) !== -1) el.value = v;
    } else if (el.tagName === 'SELECT'){
        var setVal = (v == null) ? '' : String(v);
        for (var i = 0; i < el.options.length; i++){
            if (el.options[i].value === setVal){ el.value = setVal; break; }
        }
    } else {
        el.value = (v == null) ? '' : (Array.isArray(v) ? v.join('\n') : String(v));
    }

    /* Posición + tamaño escalan con el viewport del PDF para todos los
       campos por igual — mantienen proporción a cualquier zoom.
       Los checkboxes/radios además se AMPLIFICAN visualmente (×1.8)
       sobre el rect original del PDF: los rects del AcroForm para
       casillas suelen ser muy pequeños (~12px en zoom 100%) y quedan
       poco visibles. Multiplicamos por 1.8 manteniendo el centro
       alineado con el widget original — así crecen hacia fuera por
       igual en cada lado. */
    if (el.type === 'checkbox' || el.type === 'radio'){
        var BOX_SCALE = 2;
        var newW = width  * BOX_SCALE;
        var newH = height * BOX_SCALE;
        el.style.left   = (left - (newW - width)  / 2) + 'px';
        el.style.top    = (top  - (newH - height) / 2) + 'px';
        el.style.width  = newW + 'px';
        el.style.height = newH + 'px';
    } else {
        el.style.left   = left   + 'px';
        el.style.top    = top    + 'px';
        el.style.width  = width  + 'px';
        el.style.height = height + 'px';
    }

    /* Fuente: la del campo del PDF si existe, si no la del sheet. */
    var sheetCfg = SHEETS[_pdfSheetType] || {};
    var da = annot.defaultAppearanceData || {};
    var fs;
    if (da.fontSize && da.fontSize > 0){
        fs = da.fontSize * viewport.scale;
    } else {
        fs = Math.min(14 * viewport.scale, Math.max(8, height * 0.65));
    }
    el.style.fontFamily = mapPdfFont(da.fontName, sheetCfg.defaultFont);
    if (da.fontName && /bo|bold/i.test(da.fontName)) el.style.fontWeight = 'bold';
    if (da.fontName && /(it|italic|ob|oblique)$/i.test(da.fontName)) el.style.fontStyle = 'italic';
    if (el.tagName !== 'INPUT' || (el.type !== 'checkbox' && el.type !== 'radio')){
        /* El dropdown Aptitud Mágica original tiene cuadro enorme — lo
           reducimos al 45% para que no quede una caja gigante con 3 letras. */
        if (spellAbilityOverride){
            el.style.fontSize = (fs * 0.45) + 'px';
            el.style.height = Math.max(14, height * 0.45) + 'px';
            el.style.top = (top + (height - parseFloat(el.style.height)) / 2) + 'px';
        } else {
            el.style.fontSize = fs + 'px';
        }
    }
    if (annot.textAlignment === 1) el.style.textAlign = 'center';
    if (annot.textAlignment === 2) el.style.textAlign = 'right';
    if (annot.readOnly){ el.readOnly = true; el.tabIndex = -1; el.style.opacity = 0.85; }

    /* Campo auto-calculado → bloquear edición (no alterar colores).
       Solo aplica a text/textarea; checkbox/select/radio nunca son calc. */
    var isCalc = (_pdfSheetType === 'oficial' && isCalcField(annot.fieldName)) ||
                 (_pdfSheetType === 'melon'   && isMelonCalcField(annot.fieldName));
    if (isCalc && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') &&
        el.type !== 'checkbox' && el.type !== 'radio'){
        el.readOnly = true;
        el.tabIndex = -1;
        el.title = 'Campo auto-calculado';
    }

    /* Persistir cambios en _pdfFormData y disparar recalc en cada
       interacción. runCalcs() usa rAF debounce — varios cambios
       seguidos solo provocan UNA pasada. */
    var changeEvt = (el.tagName === 'SELECT' || el.type === 'checkbox' || el.type === 'radio') ? 'change' : 'input';
    el.addEventListener(changeEvt, function(){
        if (el.type === 'checkbox') _pdfFormData[annot.fieldName] = el.checked;
        else if (el.type === 'radio'){ if (el.checked) _pdfFormData[annot.fieldName] = el.value; }
        else _pdfFormData[annot.fieldName] = el.value;
        runCalcs();
    });

    layer.appendChild(el);
}

function closePdfViewer(){
    document.getElementById('pdf-viewer').classList.remove('is-open');
    /* Cerrar también el overlay de dados si está abierto, para que al
       reabrir el visor empiece limpio. */
    var dovr = document.getElementById('dice-overlay-m');
    if (dovr) dovr.classList.remove('is-open');
    _pdfDoc = null; _pdfPage = 1; _pdfNumPages = 0;
    _pdfOriginalBytes = null; _pdfFile = null; _pdfFormData = {};
    _pdfUserZoom = 1.0;
    updateZoomLabel();
    document.getElementById('pdf-form-layer').innerHTML = '';
}

/* ── Control de zoom del visor ── */
function updateZoomLabel(){
    var lbl = document.getElementById('pdf-zoom-label');
    if (lbl) lbl.textContent = Math.round(_pdfUserZoom * 100) + '%';
}
function setPdfZoom(z){
    /* Clamp a un rango razonable (30% – 300%). Re-render diferido para
       no machacar a PDF.js si el usuario pulsa varias veces seguidas. */
    _pdfUserZoom = Math.max(0.3, Math.min(3.0, z));
    updateZoomLabel();
    clearTimeout(_pdfZoomRebuildTimer);
    _pdfZoomRebuildTimer = setTimeout(function(){
        if (_pdfDoc) renderPdfPage();
    }, 100);
}
function pdfZoomStep(delta){ setPdfZoom(_pdfUserZoom + delta); }
function pdfZoomReset(){ setPdfZoom(1.0); }

/* ════════════════════════════════════════
   GUARDAR PDF EDITADO → Drive
   Usa pdf-lib para inyectar _pdfFormData en el PDF original y subirlo.
════════════════════════════════════════ */
async function buildFilledPdfBytes(){
    if (!window.PDFLib) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
    var PDFDocument = window.PDFLib.PDFDocument;
    /* IMPORTANTE: clonamos _pdfOriginalBytes — PDFDocument.load consume
       el ArrayBuffer y futuros saves fallarían. */
    var bytes = _pdfOriginalBytes.slice(0);
    var doc = await PDFDocument.load(bytes);
    var form = doc.getForm();
    var byName = {};
    form.getFields().forEach(function(f){ byName[f.getName()] = f; });
    Object.keys(_pdfFormData).forEach(function(name){
        var fld = byName[name]; if (!fld) return;
        var v = _pdfFormData[name];
        try {
            if (typeof fld.isChecked === 'function'){
                if (v) fld.check(); else fld.uncheck();
            } else if (typeof fld.setText === 'function'){
                fld.setText(v == null ? '' : String(v));
            } else if (typeof fld.select === 'function'){
                if (v) fld.select(String(v));
            }
        } catch(e){}
    });
    return doc.save({ updateFieldAppearances: true });
}

async function savePdfToDrive(){
    if (!_pdfOriginalBytes) { showToast('Nada que guardar'); return; }
    ensureDriveAuth(async function(){
        var btn = document.getElementById('pdf-save');
        var prevTxt = btn.textContent;
        btn.disabled = true; btn.textContent = '⏳';
        try {
            var folderId = await ensureDriveFolder();
            var bytes = await buildFilledPdfBytes();

            var fileName;
            if (_pdfFile){
                /* Conserva el nombre original para PATCH. */
                fileName = _pdfFile.name;
            } else {
                /* Ficha nueva: deriva el nombre del campo "Nombre" (oficial:
                   CharacterName, melon: doc_0_doc_0_Text_1) o "personaje". */
                var nameField = _pdfSheetType === 'oficial' ? 'CharacterName' :
                                _pdfSheetType === 'melon'   ? 'doc_0_doc_0_Text_1' : null;
                var rawName = (nameField && _pdfFormData[nameField]) || 'personaje';
                fileName = String(rawName).replace(/[\\/:*?"<>|]/g,'_').replace(/\s+/g,'_') + '_' + _pdfSheetType + '.pdf';
            }

            var existingId = _pdfFile && _pdfFile.id;
            if (!existingId){
                /* Por si ya hay un fichero con ese nombre, hacer PATCH. */
                var q = "name='" + fileName.replace(/'/g,"\\'") + "' and '" + folderId + "' in parents and trashed=false";
                var fr = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                                          '&fields=files(id)&spaces=drive');
                var fd = await fr.json();
                existingId = fd.files && fd.files[0] && fd.files[0].id;
            }

            if (existingId){
                await driveFetch('https://www.googleapis.com/upload/drive/v3/files/' + existingId + '?uploadType=media', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/pdf' },
                    body: bytes
                });
                /* Actualizar el file de referencia para próximos saves. */
                if (!_pdfFile) _pdfFile = { id: existingId, name: fileName };
            } else {
                var boundary = '-------dnd_' + Math.random().toString(36).slice(2);
                var delim = '\r\n--' + boundary + '\r\n';
                var close = '\r\n--' + boundary + '--';
                var metadata = { name: fileName, parents: [folderId], mimeType: 'application/pdf' };
                var head = delim + 'Content-Type: application/json; charset=UTF-8\r\n\r\n' + JSON.stringify(metadata) +
                           delim + 'Content-Type: application/pdf\r\n\r\n';
                var headBytes = new TextEncoder().encode(head);
                var tailBytes = new TextEncoder().encode(close);
                var body = new Uint8Array(headBytes.length + bytes.byteLength + tailBytes.length);
                body.set(headBytes, 0);
                body.set(new Uint8Array(bytes), headBytes.length);
                body.set(tailBytes, headBytes.length + bytes.byteLength);
                var cr = await driveFetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', {
                    method: 'POST',
                    headers: { 'Content-Type': 'multipart/related; boundary=' + boundary },
                    body: body
                });
                var cd = await cr.json();
                if (cd.id) _pdfFile = { id: cd.id, name: fileName };
            }
            /* Refrescar la lista de fichas en el panel detrás del visor. */
            _driveFichasCache = [];
            showToast('☁ Guardado: ' + fileName);
        } catch(e){
            showToast('✗ '+e.message);
        } finally {
            btn.disabled = false; btn.textContent = prevTxt;
        }
    });
}

/* ════════════════════════════════════════
   DICE ROLLER (mismo motor Three.js que escritorio)
════════════════════════════════════════ */
var _diceRolling = false;
var _diceScene = null;
var dicePool = [];
var diceModifier = 0;

function _themeHex(varName, fallback){
    var v = getComputedStyle(document.body).getPropertyValue(varName).trim();
    return v || fallback;
}

function _makeD10Geometry(THREE){
    var n = 5, apexY = 1.2;
    var c = Math.cos(Math.PI / n);
    var ringY = ((1 - c) / (1 + c)) * apexY;
    var ringR = 1.2;
    var v = [];
    v.push(0, apexY, 0); v.push(0, -apexY, 0);
    for (var i = 0; i < n; i++){
        var a = (i / n) * Math.PI * 2;
        v.push(ringR * Math.cos(a), ringY, ringR * Math.sin(a));
    }
    for (var j = 0; j < n; j++){
        var b = (j / n) * Math.PI * 2 + Math.PI / n;
        v.push(ringR * Math.cos(b), -ringY, ringR * Math.sin(b));
    }
    var U = function(i){ return 2 + (i % n); };
    var L = function(i){ return 7 + (i % n); };
    var idx = [];
    for (var k = 0; k < n; k++){
        idx.push(0, U(k+1), L(k)); idx.push(0, L(k), U(k));
        idx.push(1, L(k), U(k+1)); idx.push(1, U(k+1), L(k+1));
    }
    var g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
    g.setIndex(idx);
    g.computeVertexNormals();
    return g;
}

function _makeGeometry(THREE, sides){
    switch(sides){
        case 4:  return new THREE.TetrahedronGeometry(1.25);
        case 6:  return new THREE.BoxGeometry(1.5, 1.5, 1.5);
        case 8:  return new THREE.OctahedronGeometry(1.3);
        case 10: return _makeD10Geometry(THREE);
        case 12: return new THREE.DodecahedronGeometry(1.2);
        case 20: return new THREE.IcosahedronGeometry(1.25);
        default: return new THREE.BoxGeometry(1.5, 1.5, 1.5);
    }
}

function _initDiceScene(){
    if (_diceScene) return _diceScene;
    var canvas = document.getElementById('dice-canvas-m');
    var stage = document.getElementById('dice-stage-m');
    var w = stage.clientWidth || 300, h = stage.clientHeight || 240;

    var renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(window.devicePixelRatio || 1);
    renderer.setSize(w, h, false);

    var scene  = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(40, w / h, 0.1, 100);
    camera.position.set(0, 0, 6);
    scene.add(new THREE.AmbientLight(0xffffff, 0.65));
    var key = new THREE.DirectionalLight(0xffffff, 0.9);
    key.position.set(3, 5, 4); scene.add(key);
    var rim = new THREE.DirectionalLight(0xffffff, 0.35);
    rim.position.set(-4, -2, -3); scene.add(rim);

    _diceScene = { renderer: renderer, scene: scene, camera: camera, raf: 0, w: w, h: h,
                   raycaster: new THREE.Raycaster(), running: false };

    /* Tap en un dado → quitarlo. */
    canvas.addEventListener('click', function(e){
        if (typeof THREE === 'undefined' || !_diceScene) return;
        var rect = canvas.getBoundingClientRect();
        var mx = ((e.clientX - rect.left) / rect.width)  * 2 - 1;
        var my = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        _diceScene.raycaster.setFromCamera({ x: mx, y: my }, camera);
        var meshes = dicePool.map(function(d){ return d.mesh; });
        var hits = _diceScene.raycaster.intersectObjects(meshes, true);
        if (!hits.length) return;
        var obj = hits[0].object;
        while(obj && meshes.indexOf(obj) === -1) obj = obj.parent;
        if (!obj) return;
        var i = meshes.indexOf(obj);
        if (i !== -1) removeDieAt(i);
    });

    /* Recalcular tamaño en resize. */
    var ro = new ResizeObserver(function(){
        var nw = stage.clientWidth, nh = stage.clientHeight;
        if (!nw || !nh) return;
        renderer.setSize(nw, nh, false);
        camera.aspect = nw / nh;
        camera.updateProjectionMatrix();
        _diceScene.w = nw; _diceScene.h = nh;
        layoutDice();
        _renderDice();
        _positionOverlays();
    });
    ro.observe(stage);

    return _diceScene;
}

function _makeDieMesh(sides){
    var accent = _themeHex('--accent', '#EDC001');
    var deep   = _themeHex('--accent-deep', '#c8a000');
    var geo = _makeGeometry(THREE, sides);
    var mat = new THREE.MeshStandardMaterial({
        color: new THREE.Color(accent), metalness: 0.25, roughness: 0.45, flatShading: true
    });
    var mesh = new THREE.Mesh(geo, mat);
    mesh.add(new THREE.LineSegments(
        new THREE.EdgesGeometry(geo, 1),
        new THREE.LineBasicMaterial({ color: new THREE.Color(deep) })
    ));
    return mesh;
}

function _recolorDice(){
    if (!_diceScene || typeof THREE === 'undefined') return;
    var accent = _themeHex('--accent', '#EDC001');
    var deep   = _themeHex('--accent-deep', '#c8a000');
    dicePool.forEach(function(d){
        if (d.mesh && d.mesh.material) d.mesh.material.color.set(accent);
        if (d.mesh) d.mesh.children.forEach(function(ch){
            if (ch.material && ch.material.color) ch.material.color.set(deep);
        });
    });
    _renderDice();
}

function layoutDice(){
    var S = _diceScene, n = dicePool.length;
    if (!S || !n) return;
    var aspect = S.w / S.h;
    var cols = Math.max(1, Math.min(n, Math.round(Math.sqrt(n * aspect))));
    var rows = Math.ceil(n / cols);
    var sp = 2.6;
    var spanW = (cols - 1) * sp, spanH = (rows - 1) * sp;
    var scale = Math.max(0.42, Math.min(0.9, 2.6 / Math.max(cols, rows)));
    dicePool.forEach(function(d, i){
        var r = Math.floor(i / cols), c = i % cols;
        var inRow = (r === rows - 1) ? (n - cols * (rows - 1)) : cols;
        var rowSpanW = (inRow - 1) * sp;
        d.mesh.position.x = -rowSpanW / 2 + c * sp;
        d.mesh.position.y =  spanH / 2 - r * sp;
        d.mesh.scale.setScalar(scale);
    });
    var fov = 40 * Math.PI / 180, half = Math.tan(fov / 2);
    var needW = spanW * 1 + 2.4, needH = spanH * 1 + 2.4;
    var zW = needW / (2 * half * aspect);
    var zH = needH / (2 * half);
    S.camera.position.z = Math.max(6, zW, zH);
}

function _positionOverlays(){
    var S = _diceScene; if (!S) return;
    var cw = S.renderer.domElement.clientWidth, ch = S.renderer.domElement.clientHeight;
    dicePool.forEach(function(d){
        var v = d.mesh.position.clone().project(S.camera);
        d.el.style.left = ((v.x * 0.5 + 0.5) * cw) + 'px';
        d.el.style.top  = ((-v.y * 0.5 + 0.5) * ch) + 'px';
    });
}
function _renderDice(){ if (_diceScene) _diceScene.renderer.render(_diceScene.scene, _diceScene.camera); }

function _animateLoop(){
    var S = _diceScene; if (!S) return;
    var now = performance.now(), anyRolling = false;
    dicePool.forEach(function(d){
        if (!d.rolling) return;
        var t = Math.min(1, (now - d.t0) / d.dur);
        var decay = 1 - t * t;
        d.mesh.rotation.x += d.vx * decay;
        d.mesh.rotation.y += d.vy * decay;
        d.mesh.rotation.z += d.vz * decay;
        d._scr = (d._scr || 0) + 16;
        if (t < 0.75 && d._scr > 60){ d._scr = 0; d.el.textContent = _dieLabel(d, _randomFaceValue(d)); }
        if (t >= 1){
            d.rolling = false;
            d.el.textContent = _dieLabel(d, d.value);
            d.el.animate(
                [{ transform:'translate(-50%,-50%) scale(1.6)', opacity:.4 },
                 { transform:'translate(-50%,-50%) scale(1)',   opacity:1 }],
                { duration: 240, easing:'cubic-bezier(.2,1.4,.4,1)' });
            updateDiceTotal();
        } else { anyRolling = true; }
    });
    _renderDice();
    _positionOverlays();
    if (anyRolling){ S.raf = requestAnimationFrame(_animateLoop); }
    else { S.running = false; }
}
function _ensureLoop(){
    if (_diceScene && !_diceScene.running){ _diceScene.running = true; _diceScene.raf = requestAnimationFrame(_animateLoop); }
}

function _randomFaceValue(d){
    if (d.kind === 'd10tens') return Math.floor(Math.random() * 10) * 10;
    return Math.floor(Math.random() * d.sides) + 1;
}
function _dieLabel(d, value){
    if (d.kind === 'd10tens') return ('00' + value).slice(-2);
    return value;
}
function _spinDie(d){
    d.value = _randomFaceValue(d);
    d.vx = (0.22 + Math.random() * 0.22) * (Math.random() < .5 ? -1 : 1);
    d.vy =  0.28 + Math.random() * 0.26;
    d.vz = (0.10 + Math.random() * 0.14) * (Math.random() < .5 ? -1 : 1);
    d.dur = 1300 + Math.random() * 400;
    d.t0 = performance.now();
    d._scr = 0;
    d.rolling = true;
}

function updateDiceTotal(){
    var resEl = document.getElementById('dice-result-m');
    if (!dicePool.length && !diceModifier){ resEl.textContent = 'Elige un dado'; return; }
    var anyRolling = dicePool.some(function(d){ return d.rolling; });
    if (anyRolling){ resEl.textContent = 'Tirando…'; return; }
    var total = diceModifier;
    dicePool.forEach(function(d){ total += d.value; });
    var extra = '';
    if (dicePool.length === 1 && !diceModifier && dicePool[0].sides === 20){
        if (dicePool[0].value === 20) extra = ' <span class="dice-crit">¡CRÍTICO!</span>';
        else if (dicePool[0].value === 1) extra = ' <span class="dice-fail">PIFIA</span>';
    }
    var modTxt = diceModifier ? ' <span style="color:var(--text-muted)">(' + (diceModifier > 0 ? '+' : '') + diceModifier + ')</span>' : '';
    resEl.innerHTML = 'Total <strong>' + total + '</strong>' + modTxt + extra;
}

function _pushDie(sides, kind){
    var mesh = _makeDieMesh(sides);
    _diceScene.scene.add(mesh);
    var el = document.createElement('div');
    el.className = 'dice-val';
    document.getElementById('dice-overlays-m').appendChild(el);
    var d = { sides: sides, kind: kind, value: 1, mesh: mesh, el: el };
    dicePool.push(d);
    _spinDie(d);
}

function addDie(sides){
    if (typeof THREE === 'undefined' || !_diceScene) return;
    if (sides === 100){ _pushDie(10, 'd10tens'); _pushDie(10, 'normal'); }
    else { _pushDie(sides, 'normal'); }
    layoutDice();
    updateDiceTotal();
    _ensureLoop();
}

function rerollAll(){
    if (typeof THREE === 'undefined' || !_diceScene || !dicePool.length) return;
    _recolorDice();
    dicePool.forEach(_spinDie);
    updateDiceTotal();
    _ensureLoop();
}

function removeDieAt(i){
    var d = dicePool[i]; if (!d) return;
    _diceScene.scene.remove(d.mesh);
    if (d.mesh.geometry) d.mesh.geometry.dispose();
    if (d.el && d.el.parentNode) d.el.parentNode.removeChild(d.el);
    dicePool.splice(i, 1);
    layoutDice();
    _renderDice();
    _positionOverlays();
    updateDiceTotal();
}

function clearDice(){
    while(dicePool.length) removeDieAt(0);
}

/* Carga lazy de Three.js la primera vez que se abre el overlay de
   dados dentro del visor PDF (antes era un tab separado). */
var _threeLoaded = false;
function ensureDiceEngine(){
    if (_threeLoaded) return Promise.resolve();
    _threeLoaded = true;
    return loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js').then(function(){
        _initDiceScene();
        _renderDice();
    }).catch(function(){
        _threeLoaded = false;
        document.getElementById('dice-result-m').textContent = '✗ No se pudo cargar el motor 3D';
    });
}

/* ════════════════════════════════════════
   WIRING
════════════════════════════════════════ */
(function(){
    /* Botón Close del title-bar + ‹ Menú del statusbar. */
    function goMenu(e){
        if (e) e.preventDefault();
        if (window.parent && window.parent !== window){
            try { window.parent.postMessage({ type: 'shell:back' }, '*'); return; } catch(_){}
        }
        try { history.back(); } catch(_){ location.href = '../../mobile.php'; }
    }
    var topBtn = document.getElementById('dnd-back');
    if (topBtn) topBtn.addEventListener('click', goMenu);
    var lnk = document.getElementById('dnd-menu-link');
    if (lnk) lnk.addEventListener('click', goMenu);
    /* Botón "‹ Menú" en el toolbar del visor PDF — atajo para volver
       al menú principal sin cerrar primero el visor y luego ‹ Menú del
       statusbar. El postMessage shell:back lo recibe mobile.php y hace
       history.back() — el iframe entero se reemplaza, así que no hace
       falta cerrar el visor manualmente. */
    var pdfBackMenu = document.getElementById('pdf-back-menu');
    if (pdfBackMenu) pdfBackMenu.addEventListener('click', goMenu);

    /* Toggle del overlay de dados dentro del visor PDF. La carga del
       motor 3D es lazy: se descarga Three.js sólo la primera vez que
       se abre el overlay. */
    var diceOverlay = document.getElementById('dice-overlay-m');
    var diceToggleBtn = document.getElementById('pdf-dice-toggle');
    function openDiceOverlay(){
        diceOverlay.classList.add('is-open');
        ensureDiceEngine().then(function(){
            /* Forzar layout/positioning ahora que el canvas tiene tamaño. */
            if (_diceScene){ layoutDice(); _renderDice(); _positionOverlays(); }
        });
    }
    function closeDiceOverlay(){ diceOverlay.classList.remove('is-open'); }
    if (diceToggleBtn) diceToggleBtn.addEventListener('click', function(){
        if (diceOverlay.classList.contains('is-open')) closeDiceOverlay();
        else openDiceOverlay();
    });
    var diceOverlayClose = document.getElementById('dice-overlay-close');
    if (diceOverlayClose) diceOverlayClose.addEventListener('click', closeDiceOverlay);

    /* Dados — botones y acciones. */
    document.querySelectorAll('.dice-btn-m').forEach(function(b){
        b.addEventListener('click', function(){
            var sides = parseInt(b.dataset.sides, 10);
            addDie(sides);
        });
    });
    document.getElementById('dice-reroll-m').addEventListener('click', rerollAll);
    document.getElementById('dice-clear-m').addEventListener('click', clearDice);
    document.getElementById('dice-mod-btn-m').addEventListener('click', function(){
        var modal = document.getElementById('dice-mod-modal-m');
        var inp = document.getElementById('dice-mod-input-m');
        inp.value = diceModifier;
        modal.classList.add('is-open');
        setTimeout(function(){ inp.focus(); inp.select(); }, 50);
    });
    document.getElementById('dice-mod-close-m').addEventListener('click', function(){
        document.getElementById('dice-mod-modal-m').classList.remove('is-open');
    });
    document.getElementById('dice-mod-cancel-m').addEventListener('click', function(){
        document.getElementById('dice-mod-modal-m').classList.remove('is-open');
    });
    document.getElementById('dice-mod-apply-m').addEventListener('click', function(){
        var v = parseInt(document.getElementById('dice-mod-input-m').value, 10);
        diceModifier = isNaN(v) ? 0 : v;
        document.getElementById('dice-mod-modal-m').classList.remove('is-open');
        updateDiceTotal();
    });

    /* Mis Fichas — buscador, refresh, disconnect. */
    document.getElementById('mf-search-m').addEventListener('input', renderFichasList);
    document.getElementById('mf-refresh-m').addEventListener('click', fetchAndRenderFichas);
    document.getElementById('mf-disconnect-m').addEventListener('click', disconnectDrive);

    /* Modal "Nueva ficha" — el botón ＋ Nueva abre la ventana de
       confirmación; el usuario elige tipo de hoja, y entonces
       arrancamos el visor con la plantilla en blanco correspondiente. */
    var newSheetModal = document.getElementById('new-sheet-modal');
    function openNewSheetModal(){ newSheetModal.classList.add('is-open'); }
    function closeNewSheetModal(){ newSheetModal.classList.remove('is-open'); }
    document.getElementById('mf-new-m').addEventListener('click', openNewSheetModal);
    document.getElementById('new-sheet-close').addEventListener('click', closeNewSheetModal);
    document.getElementById('new-sheet-cancel').addEventListener('click', closeNewSheetModal);
    document.getElementById('new-sheet-oficial').addEventListener('click', function(){
        closeNewSheetModal();
        openBlankSheet('oficial');
    });
    document.getElementById('new-sheet-melon').addEventListener('click', function(){
        closeNewSheetModal();
        openBlankSheet('melon');
    });
    /* Tap fuera del cuadro (sobre el backdrop) cierra el modal. */
    newSheetModal.addEventListener('click', function(e){
        if (e.target === newSheetModal) closeNewSheetModal();
    });

    /* Visor PDF — navegación y guardado. */
    document.getElementById('pdf-viewer-close').addEventListener('click', function(){
        closePdfViewer();
        /* Al cerrar, recargar lista por si guardaste algo nuevo. */
        if (_driveToken && _driveToken.expires_at > Date.now()){
            fetchAndRenderFichas();
        }
    });
    document.getElementById('pdf-prev').addEventListener('click', function(){
        if (_pdfPage > 1){ _pdfPage--; renderPdfPage(); }
    });
    document.getElementById('pdf-next').addEventListener('click', function(){
        if (_pdfPage < _pdfNumPages){ _pdfPage++; renderPdfPage(); }
    });
    document.getElementById('pdf-save').addEventListener('click', savePdfToDrive);

    /* Zoom: botones − / + / ⛶ (ajustar al ancho = 100%). Pasos de 20%. */
    document.getElementById('pdf-zoom-out').addEventListener('click', function(){ pdfZoomStep(-0.2); });
    document.getElementById('pdf-zoom-in').addEventListener('click',  function(){ pdfZoomStep(+0.2); });
    document.getElementById('pdf-zoom-reset').addEventListener('click', pdfZoomReset);

    /* ── PINCH-TO-ZOOM ──
       Detectamos dos dedos sobre el área del visor, medimos la distancia
       inicial entre ellos y al moverlos aplicamos un CSS transform: scale()
       INSTANTÁNEO sobre #pdf-page-wrap para feedback fluido. En touchend
       fijamos el zoom REAL en _pdfUserZoom y disparamos renderPdfPage()
       una sola vez para que el canvas se rasterice a la nueva resolución
       (texto crispo) y los inputs de la capa editable se reposicionen.
       Durante el pinch los inputs escalan visualmente con el wrap. */
    (function(){
        var wrap = document.getElementById('pdf-viewer-canvas-wrap');
        var pageWrap = document.getElementById('pdf-page-wrap');
        var pinch = { active: false, startDist: 0, startZoom: 1, currentZoom: 1 };

        function touchDist(touches){
            var dx = touches[0].clientX - touches[1].clientX;
            var dy = touches[0].clientY - touches[1].clientY;
            return Math.sqrt(dx*dx + dy*dy);
        }
        function setLabelText(z){
            var lbl = document.getElementById('pdf-zoom-label');
            if (lbl) lbl.textContent = Math.round(z * 100) + '%';
        }

        wrap.addEventListener('touchstart', function(e){
            if (e.touches.length !== 2) return;
            pinch.active = true;
            pinch.startDist = touchDist(e.touches);
            pinch.startZoom = _pdfUserZoom;
            pinch.currentZoom = _pdfUserZoom;
            clearTimeout(_pdfZoomRebuildTimer);
            /* OCULTAR la capa de inputs durante el pinch. Si la dejamos
               visible, el transform: scale() del page-wrap escala TODOS
               los hijos visualmente, incluidos los checkboxes/radios —
               se ven temporalmente agrandados. Al ocultarla, solo el
               canvas se ve escalando suavemente. Tras el endPinch,
               renderPdfPage la reconstruye al tamaño correcto y con los
               checkboxes a 16×16 fijos. */
            var fl = document.getElementById('pdf-form-layer');
            if (fl) fl.style.visibility = 'hidden';
            e.preventDefault();
        }, { passive: false });

        wrap.addEventListener('touchmove', function(e){
            if (!pinch.active || e.touches.length !== 2) return;
            e.preventDefault();
            var dist = touchDist(e.touches);
            if (pinch.startDist === 0) return;
            var ratio = dist / pinch.startDist;
            var newZoom = Math.max(0.3, Math.min(3.0, pinch.startZoom * ratio));
            pinch.currentZoom = newZoom;
            var delta = newZoom / pinch.startZoom;
            pageWrap.style.transformOrigin = 'center top';
            pageWrap.style.transform = 'scale(' + delta + ')';
            setLabelText(newZoom);
        }, { passive: false });

        function endPinch(){
            if (!pinch.active) return;
            pinch.active = false;
            pageWrap.style.transform = '';
            pageWrap.style.transformOrigin = '';
            var fl = document.getElementById('pdf-form-layer');
            if (fl) fl.style.visibility = '';
            if (Math.abs(pinch.currentZoom - _pdfUserZoom) > 0.01){
                _pdfUserZoom = pinch.currentZoom;
                updateZoomLabel();
                if (_pdfDoc) renderPdfPage();
            } else {
                updateZoomLabel();
            }
        }
        wrap.addEventListener('touchend',    endPinch);
        wrap.addEventListener('touchcancel', endPinch);
    })();

    /* Recolorear dados si el tema del usuario cambia en caliente. */
    var deb = null;
    new MutationObserver(function(){
        clearTimeout(deb);
        deb = setTimeout(function(){ _recolorDice(); }, 120);
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });

    /* Estado inicial. Si hay token válido cacheado en localStorage,
       cargamos la lista de inmediato. Si NO hay token, llamamos a
       renderFichasList() para que pinte el empty-state con el botón
       "☁ Conectar con Drive" (el HTML estático solo lleva el texto,
       no el botón — antes esto dejaba al usuario sin manera de iniciar
       el flujo OAuth). */
    updateDriveStatus();
    tryAutoConnectDrive();
    if (_driveToken && _driveToken.expires_at > Date.now()){
        fetchAndRenderFichas();
    } else {
        renderFichasList();
    }
})();
</script>

</body>
</html>
