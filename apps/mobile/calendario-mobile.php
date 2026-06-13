<?php
/* ──────────────────────────────────────────────────────────────────────
   CALENDARIO — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Port de apps/calendario.php a single-page móvil. Reusa los mismos
   endpoints de /assets/couple/api.php — get-momentos, get-recordatorios,
   save-momento, save-recordatorio, delete-momento, delete-recordatorio,
   invite-partner, get-partner-invites, respond-partner-invite,
   purge-recordatorios + countdown YT player.

   Funcionalidades portadas (TODAS):
     - Grid mensual con fotos de día + navegación prev/next
     - Tiempo juntos contador (si hay pareja)
     - Bottom sheet del día con momentos + recordatorios
     - Añadir momento (título, fecha, descripción, foto URL/upload)
     - Añadir recordatorio (título, fecha, descripción, periodicidad)
     - Eliminar momento/recordatorio (confirm modal)
     - Recordatorios periódicos: anual/mensual/semanal (forward-only)
     - Sidebar de próximos 14 días → lista colapsable abajo
     - Countdown épico fullscreen con YT player de música
     - Invitación de pareja (enviar + recibir + aceptar/rechazar)
     - Polling de invites con backoff exponencial
     - Widgets Win98: date picker custom + select custom
   ────────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__, 2) . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

if (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    header('Location: ../../index.php');
    exit;
}
$userKey   = $_SESSION['user'];
$userLabel = $loginUsers[$userKey]['label'];

/* uid → necesario para invocaciones del countdown YT y datos del usuario. */
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->execute([strtolower($userLabel)]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'] ?? 0;

/* Pareja del usuario — misma query que el desktop. */
$stmt = $pdo->prepare("
    SELECT p.id, p.fecha_inicio, u1.username as user1, u2.username as user2
    FROM parejas p
    JOIN usuarios u1 ON p.usuario1_id = u1.id
    JOIN usuarios u2 ON p.usuario2_id = u2.id
    WHERE u1.username = ? OR u2.username = ?
");
$stmt->execute([strtolower($userLabel), strtolower($userLabel)]);
$pareja = $stmt->fetch(PDO::FETCH_ASSOC);
$parejaId    = $pareja ? (int)$pareja['id'] : 0;
$fechaInicio = $pareja ? $pareja['fecha_inicio'] : null;

/* Tema activo del usuario. */
require_once dirname(__DIR__, 2) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
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
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="theme-color" content="<?= htmlspecialchars($themeBgColor) ?>">
    <title>Calendario</title>
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
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
    /* ════════════════════════════════════════════════════════════════
       CALENDARIO MÓVIL — layout vertical full-width.
       Fuente pixelada (VT323) en TODO + colores del tema (sin hardcodes).
       ════════════════════════════════════════════════════════════════ */
    html, body {
        height: 100%; margin: 0; padding: 0; overflow: hidden;
        background: var(--desktop-bg, var(--win-bg));
        color: var(--text);
        font-family: 'VT323', monospace;
        font-size: 16px;
        letter-spacing: 0.5px;
    }
    * { box-sizing: border-box; font-family: inherit; }
    /* VT323 es proporcionalmente más pequeño que las sans-serif por
       default → subimos los tamaños base en buttons / inputs para que
       el render visual no se sienta más chico que antes. */
    button, input, textarea, select, .button {
        font-family: 'VT323', monospace !important;
        letter-spacing: 0.5px;
    }

    /* ── Body scrollable. Ya NO usa calc(100vh) — el flujo flex del
       .window-body (mobile-theme.css) lo gestiona automáticamente: el
       title-bar arriba + statusbar abajo son flex-shrink:0 y este body
       toma el resto con flex:1. ── */
    .cm-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 8px;
        padding-bottom: calc(8px + env(safe-area-inset-bottom));
    }

    /* ── Card "Tiempo juntos" ── */
    .cm-card {
        background: var(--win-bg, silver);
        padding: 10px;
        margin-bottom: 10px;
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px 0 var(--bezel-light-1, #fff),
            inset -2px -2px 0 var(--bezel-dark-2, grey),
            inset  2px  2px 0 var(--bezel-light-2, #dfdfdf);
    }
    .cm-card-title {
        font-size: 11px;
        font-weight: bold;
        background: linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
        color: var(--titlebar-text);
        padding: 3px 6px;
        margin: -10px -10px 8px;
    }
    .cm-counter {
        text-align: center;
        padding: 8px;
    }
    .cm-counter-big {
        font-size: 32px;
        font-weight: bold;
        color: var(--accent);
        line-height: 1;
    }
    .cm-counter-sub {
        font-size: 11px;
        margin-top: 4px;
        color: var(--text);
    }
    .cm-counter-from {
        font-size: 10px;
        color: var(--text-faint, #808080);
        margin-top: 6px;
    }

    /* ── Nav del mes: solo label centrado, sin botones (navegación por
       swipe horizontal sobre el grid). ── */
    .cm-month-nav {
        text-align: center;
        padding: 6px 4px 4px;
        margin-bottom: 6px;
    }
    .cm-month-label {
        font-size: 14px;
        font-weight: bold;
        color: var(--text);
        user-select: none;
    }
    /* Wrap del grid: clipping + touch-action para que el browser deje
       pasar gestos verticales al scroll pero capture el pan-x. */
    .cm-grid-wrap {
        overflow: hidden;
        width: 100%;
        touch-action: pan-y;
        position: relative;
    }
    /* Slider de 300% que contiene 3 grids (prev/curr/next) en flex.
       Transform por defecto -33.333% centra el del medio en el viewport.
       La transición se desactiva durante el dedo (no-lag) y se reactiva
       al soltar para que la animación de "asentarse" sea fluida. */
    .cm-grid-slider {
        display: flex;
        width: 300%;
        transform: translateX(-33.333%);
        transition: transform 0.25s cubic-bezier(0.22, 0.9, 0.3, 1);
        will-change: transform;
    }
    .cm-grid-slider.cm-swiping {
        transition: none;
    }
    .cm-grid-slider > .cm-grid {
        flex: 0 0 33.333%;
        min-width: 0;
    }

    /* ── Grid mes ── */
    .cm-dow {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        margin-bottom: 4px;
    }
    .cm-dow-cell {
        text-align: center;
        font-size: 10px;
        font-weight: bold;
        color: var(--text-faint, #808080);
        padding: 2px 0;
    }
    .cm-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background: var(--bezel-dark-2, grey);
        padding: 2px;
    }
    .cm-cell {
        aspect-ratio: 1;
        background: var(--input-bg, #fff);
        color: var(--text);
        font-size: 12px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background-size: cover;
        background-position: center;
        border: 1px solid transparent;
        user-select: none;
        overflow: hidden;
    }
    .cm-cell-other { color: var(--text-faint, #999); opacity: 0.5; }
    .cm-cell-today {
        background: var(--accent);
        color: var(--accent-text);
        font-weight: bold;
    }
    .cm-cell-foto {
        color: var(--accent-text, #fff) !important;
        text-shadow: 0 0 3px #000, 0 0 6px #000;   /* shadow funcional para legibilidad sobre fotos */
    }
    .cm-cell-dot {
        position: absolute;
        bottom: 2px;
        right: 3px;
        display: flex;
        gap: 2px;
    }
    .cm-cell-dot span {
        width: 5px; height: 5px;
        border-radius: 50%;
        display: block;
    }
    .cm-cell-dot .d-mom { background: var(--accent); }
    .cm-cell-dot .d-rec { background: var(--accent-deep, var(--accent)); }

    /* ── Acciones rápidas ── */
    .cm-actions {
        display: flex;
        gap: 6px;
        margin: 10px 0;
    }
    .cm-actions .button {
        flex: 1;
        min-height: 36px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .cm-actions .button img {
        width: 14px; height: 14px;
        object-fit: contain;
        image-rendering: pixelated;
    }

    /* ── Lista de próximos recordatorios ── */
    .cm-list-title {
        font-size: 11px;
        font-weight: bold;
        background: linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
        color: var(--titlebar-text);
        padding: 4px 8px;
        margin: 12px 0 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .cm-list-title img {
        width: 14px; height: 14px;
        object-fit: contain;
        image-rendering: pixelated;
    }
    .cm-rec-item {
        background: var(--win-bg);
        padding: 8px;
        margin-bottom: 6px;
        border: 1px solid var(--accent-deep, var(--accent));
        font-size: 12px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 6px;
    }
    .cm-rec-item-info {
        flex: 1;
        min-width: 0;
        overflow-wrap: break-word;
    }
    .cm-rec-item-info strong { font-size: 13px; }
    .cm-rec-item-info .when { font-size: 10px; color: var(--text-faint, #808080); margin-top: 2px; display: block; }
    .cm-rec-item-btns {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex-shrink: 0;
    }
    .cm-rec-item-btns .button {
        min-height: 28px;
        min-width: 36px;
        padding: 2px 6px;
        font-size: 12px;
    }

    /* ── Bottom sheet ── */
    .cm-sheet-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        z-index: 1000;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }
    .cm-sheet-overlay.visible { display: flex; }
    .cm-sheet {
        background: var(--win-bg);
        width: 100%;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        animation: cmSheetIn 0.22s cubic-bezier(0.2, 0.9, 0.3, 1.2);
        border-top: 2px solid var(--bezel-light-1);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.5);
    }
    @keyframes cmSheetIn {
        from { transform: translateY(100%); }
        to   { transform: translateY(0); }
    }
    .cm-sheet-bar {
        background: linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
        color: var(--titlebar-text);
        padding: 6px 10px;
        font-size: 12px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .cm-sheet-bar img {
        width: 14px; height: 14px;
        object-fit: contain;
        image-rendering: pixelated;
        vertical-align: -2px;
        margin-right: 4px;
    }
    .cm-sheet-close {
        background: var(--win-bg);
        color: var(--text);
        border: 1px solid var(--bezel-dark-1);
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-2),
            inset  1px  1px 0 var(--bezel-light-2);
        width: 24px; height: 22px;
        font-size: 12px;
        padding: 0;
        cursor: pointer;
    }
    .cm-sheet-body {
        padding: 10px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }
    .cm-section-title {
        font-size: 11px;
        font-weight: bold;
        margin: 8px 0 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .cm-section-title.momentos { color: var(--accent); }
    .cm-section-title.recs     { color: var(--accent-deep, var(--accent)); }
    .cm-section-title img {
        width: 14px; height: 14px;
        object-fit: contain;
        image-rendering: pixelated;
    }

    /* Cards de momentos en bottom sheet */
    .cm-mom {
        border: 1px solid var(--accent);
        padding: 8px;
        margin-bottom: 8px;
        font-size: 11px;
    }
    .cm-mom-img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        margin-bottom: 6px;
        display: block;
    }
    .cm-mom-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 6px;
    }
    .cm-mom-row .info { flex: 1; min-width: 0; }
    .cm-rec {
        border: 1px solid var(--accent-deep, var(--accent));
        padding: 6px;
        margin-bottom: 6px;
        font-size: 11px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 6px;
    }
    .cm-rec-info { flex: 1; min-width: 0; }
    .cm-rec-btns {
        display: flex;
        gap: 4px;
        flex-shrink: 0;
    }
    .cm-rec-btns .button {
        font-size: 11px;
        padding: 2px 6px;
        min-height: 26px;
        min-width: 28px;
    }
    .muted { color: var(--text-faint, #808080); }

    /* ── Modales de añadir (full screen) ── */
    .cm-modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 12px;
    }
    .cm-modal-overlay.visible { display: flex; }
    .cm-modal {
        background: var(--win-bg);
        width: 100%;
        max-width: 360px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-1),
            inset  1px  1px 0 var(--bezel-light-1),
            inset -2px -2px 0 var(--bezel-dark-2),
            inset  2px  2px 0 var(--bezel-light-2);
    }
    .cm-modal-bar {
        background: linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
        color: var(--titlebar-text);
        padding: 4px 8px;
        font-size: 12px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cm-modal-bar img {
        width: 14px; height: 14px;
        object-fit: contain;
        image-rendering: pixelated;
        vertical-align: -2px;
        margin-right: 4px;
    }
    .cm-modal-body {
        padding: 12px;
        overflow-y: auto;
        max-height: 75vh;
    }
    .cm-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 10px;
    }
    .cm-field label {
        font-size: 11px;
        font-weight: bold;
    }
    .cm-field input[type="text"],
    .cm-field input[type="date"],
    .cm-field textarea {
        width: 100%;
        font-size: 12px;
        min-height: 28px;
        padding: 4px 6px;
    }
    .cm-field textarea { min-height: 60px; resize: vertical; }
    .cm-modal-foot {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
        padding: 8px;
        border-top: 1px solid var(--bezel-dark-2);
        background: var(--win-bg);
    }
    .cm-modal-foot .button {
        min-height: 32px;
        padding: 4px 14px;
        font-size: 12px;
    }
    #cm-status, #cm-rec-status, #cm-invite-status, #cm-partner-status {
        font-size: 11px;
        margin-top: 4px;
        min-height: 14px;
    }

    /* ── Win98 widgets (date picker + select) — mismo CSS que desktop ── */
    .w98-date-wrap, .w98-select-wrap {
        position: relative;
        display: inline-flex;
        width: 100%;
        box-sizing: border-box;
    }
    .w98-date-display, .w98-select-btn {
        flex: 1;
        height: 28px;
        padding: 2px 8px;
        font-size: 12px;
        font-family: inherit;
        background: var(--input-bg, #fff);
        color: var(--text);
        border: 1px solid var(--bezel-dark-1);
        box-shadow:
            inset -1px -1px 0 var(--bezel-light-2),
            inset  1px  1px 0 var(--bezel-dark-2);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        line-height: 1;
    }
    .w98-select-arrow {
        margin-left: 6px;
        font-size: 8px;
        opacity: 0.8;
        flex-shrink: 0;
    }
    .w98-date-label { flex: 1; }

    .w98-cal-popup {
        position: fixed;
        z-index: 10000;
        background: var(--win-bg);
        border: 1px solid;
        border-color: var(--bezel-light-1) var(--bezel-dark-1) var(--bezel-dark-1) var(--bezel-light-1);
        box-shadow: 2px 2px 6px rgba(0,0,0,0.35);
        padding: 4px;
        font-size: 11px;
        width: 240px;
        box-sizing: border-box;
        font-family: inherit;
    }
    .w98-cal-popup[hidden] { display: none; }
    .w98-cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 3px 4px;
        background: linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
        color: var(--titlebar-text);
        margin-bottom: 4px;
        font-weight: bold;
        font-size: 11px;
        height: 24px;
        box-sizing: border-box;
        overflow: hidden;
        gap: 4px;
    }
    .w98-cal-title {
        flex: 1 1 auto;
        min-width: 0;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1;
    }
    .w98-cal-nav-btn {
        width: 24px;
        height: 20px;
        padding: 0;
        margin: 0;
        font-size: 13px;
        font-family: inherit;
        line-height: 1;
        cursor: pointer;
        flex: 0 0 24px;
        box-sizing: border-box;
        background: var(--win-bg);
        color: var(--text);
        border: 1px solid var(--bezel-dark-1);
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-2),
            inset  1px  1px 0 var(--bezel-light-2);
        appearance: none;
        -webkit-appearance: none;
    }
    .w98-cal-nav-btn:active {
        box-shadow:
            inset  1px  1px 0 var(--bezel-dark-2),
            inset -1px -1px 0 var(--bezel-light-2);
    }
    .w98-cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: var(--bezel-dark-2);
        padding: 1px;
    }
    .w98-cal-dow {
        text-align: center;
        font-weight: bold;
        font-size: 10px;
        padding: 2px 0;
        background: var(--win-bg);
        color: var(--text-faint, #555);
    }
    .w98-cal-day {
        text-align: center;
        padding: 6px 0;
        cursor: pointer;
        background: var(--input-bg, #fff);
        color: var(--text);
        border: 1px solid transparent;
        font-size: 11px;
        user-select: none;
    }
    .w98-cal-day:active { background: var(--accent); color: var(--accent-text); }
    .w98-cal-day.other-month { color: var(--text-faint, #999); opacity: 0.45; }
    .w98-cal-day.today {
        outline: 1px dashed var(--accent);
        outline-offset: -2px;
        font-weight: bold;
    }
    .w98-cal-day.selected {
        background: var(--accent);
        color: var(--accent-text);
        font-weight: bold;
    }

    .w98-select-popup {
        position: fixed;
        z-index: 10000;
        background: var(--input-bg, #fff);
        border: 1px solid var(--bezel-dark-1);
        box-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        max-height: 50vh;
        overflow-y: auto;
        font-family: inherit;
    }
    .w98-select-popup[hidden] { display: none; }
    .w98-select-opt {
        padding: 8px 10px;
        cursor: pointer;
        font-size: 12px;
        color: var(--text);
        user-select: none;
    }
    .w98-select-opt:active,
    .w98-select-opt.active {
        background: var(--accent);
        color: var(--accent-text);
    }

    /* ── Toast simple ── */
    .cm-toast {
        position: fixed;
        bottom: calc(60px + env(safe-area-inset-bottom));
        left: 50%;
        transform: translateX(-50%);
        background: var(--win-bg);
        padding: 8px 14px;
        font-size: 12px;
        z-index: 3000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        border: 1px solid var(--bezel-dark-1);
    }

    /* ── Countdown fullscreen ── */
    #cm-countdown {
        position: fixed; inset: 0;
        background: var(--surface-deep, var(--win-bg, #0a0a14));
        z-index: 5000;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        text-align: center;
        padding: 20px;
        overflow: hidden;
    }
    #cm-countdown.visible { display: flex; }
    #cm-countdown .cd-title {
        font-family: 'VT323', monospace;
        font-size: 36px;
        margin-bottom: 8px;
        text-shadow: 0 0 12px var(--accent);
        word-break: break-word;
    }
    #cm-countdown .cd-status {
        font-size: 14px;
        letter-spacing: 4px;
        margin-bottom: 12px;
        opacity: 0.8;
    }
    #cm-countdown .cd-digits {
        display: flex;
        gap: 10px;
        font-family: 'VT323', monospace;
        font-size: 64px;
        font-weight: bold;
        margin: 12px 0;
        text-shadow: 0 0 18px var(--accent, var(--accent));
    }
    #cm-countdown .cd-cell { display: flex; flex-direction: column; align-items: center; }
    #cm-countdown .cd-cell-num { line-height: 1; min-width: 50px; }
    #cm-countdown .cd-cell-lbl {
        font-family: inherit;
        font-size: 11px;
        letter-spacing: 2px;
        text-shadow: none;
        opacity: 0.6;
        margin-top: 4px;
    }
    #cm-countdown .cd-fecha {
        font-family: 'VT323', monospace;
        font-size: 20px;
        opacity: 0.7;
    }
    #cm-countdown-close {
        position: absolute;
        top: calc(12px + env(safe-area-inset-top));
        right: 12px;
        min-height: 36px;
        min-width: 36px;
        font-size: 16px;
        padding: 0;
        background: var(--win-bg);
        border: 1px solid var(--bezel-dark-1);
        color: var(--text);
        cursor: pointer;
        z-index: 5001;
    }
    </style>
</head>
<body class="mh-body <?= htmlspecialchars($activeThemeClass) ?>">
<div class="window mh-window">
    <div class="title-bar">
        <div class="title-bar-text"><img src="../../assets/img/appIcons/calendarioIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Calendario</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="cm-back-top"></button>
        </div>
    </div>
    <div class="window-body">

<?php if (!$pareja): ?>
<!-- Botón invitar — solo visible cuando no hay pareja. -->
<div style="padding:6px 8px;display:flex;justify-content:flex-end;flex-shrink:0;">
    <button class="button" id="cm-invite-btn" type="button">Invitar</button>
</div>
<?php endif; ?>

<!-- ═══════════ SCROLL BODY ═══════════ -->
<div class="cm-scroll">

<?php if ($pareja): ?>
    <!-- Tiempo juntos -->
    <div class="cm-card">
        <div class="cm-card-title">Tiempo juntos</div>
        <div class="cm-counter">
            <div class="cm-counter-big" id="cm-dias">—</div>
            <div class="cm-counter-sub">días juntos</div>
            <div class="cm-counter-from">Desde <?= htmlspecialchars($pareja['fecha_inicio']) ?></div>
        </div>
    </div>
<?php endif; ?>

    <!-- Nav del mes + grid -->
    <!-- Solo label del mes. Navegación por swipe horizontal sobre el grid. -->
    <div class="cm-month-nav">
        <div class="cm-month-label" id="cm-month-label">—</div>
    </div>
    <div class="cm-dow">
        <div class="cm-dow-cell">L</div>
        <div class="cm-dow-cell">M</div>
        <div class="cm-dow-cell">X</div>
        <div class="cm-dow-cell">J</div>
        <div class="cm-dow-cell">V</div>
        <div class="cm-dow-cell">S</div>
        <div class="cm-dow-cell">D</div>
    </div>
    <!-- Wrap clipping + slider con 3 grids (prev/current/next).
         Swipe horizontal sobre el wrap navega entre meses; los grids
         se renderizan los 3 sincronizados desde el JS. -->
    <div class="cm-grid-wrap" id="cm-grid-wrap">
        <div class="cm-grid-slider" id="cm-grid-slider">
            <div class="cm-grid"></div>
            <div class="cm-grid" id="cm-grid"></div>
            <div class="cm-grid"></div>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="cm-actions">
        <button class="button" id="cm-add-mom-btn" type="button">
            <img src="../../assets/img/appIcons/galeriaIcon.png" alt="">Momento
        </button>
        <button class="button" id="cm-add-rec-btn" type="button">
            <img src="../../assets/img/appIcons/bellIcon.png" alt="">Recordatorio
        </button>
    </div>

    <!-- Lista próximos recordatorios -->
    <div class="cm-list-title">
        <img src="../../assets/img/appIcons/bellIcon.png" alt="">Próximos recordatorios
    </div>
    <div id="cm-rec-list"><p style="font-size:11px;color:var(--text-faint);">Cargando…</p></div>
</div>

<!-- Status bar Win98 al pie con "‹ Menú" — mismo patrón que el resto
     de apps móviles (tienda, perfil…). Usa la clase compartida
     .mh-statusbar de mobile-theme.css. id="cm-back" para reutilizar el
     handler existente en el JS. -->
<div class="mh-statusbar">
    <a href="#" id="cm-back">‹ Menú</a>
</div>

    </div><!-- /.window-body -->
</div><!-- /.window.mh-window -->

<!-- ═══════════ BOTTOM SHEET DÍA ═══════════ -->
<div class="cm-sheet-overlay" id="cm-sheet-overlay">
    <div class="cm-sheet">
        <div class="cm-sheet-bar">
            <span>
                <img src="../../assets/img/appIcons/calendarioIcon.png" alt=""><span id="cm-sheet-fecha"></span>
            </span>
            <button class="cm-sheet-close" id="cm-sheet-close" type="button">✕</button>
        </div>
        <div class="cm-sheet-body" id="cm-sheet-body"></div>
    </div>
</div>

<!-- ═══════════ MODAL: AÑADIR MOMENTO ═══════════ -->
<div class="cm-modal-overlay" id="cm-mom-modal">
    <div class="cm-modal">
        <div class="cm-modal-bar">
            <span><img src="../../assets/img/appIcons/instagramIcon.png" alt="">Añadir momento</span>
            <button class="cm-sheet-close" id="cm-mom-close" type="button">✕</button>
        </div>
        <div class="cm-modal-body">
            <div class="cm-field">
                <label>Título</label>
                <input type="text" id="cm-mom-titulo" maxlength="200">
            </div>
            <div class="cm-field">
                <label>Fecha</label>
                <input type="date" id="cm-mom-fecha">
            </div>
            <div class="cm-field">
                <label>Descripción</label>
                <textarea id="cm-mom-desc" maxlength="2000"></textarea>
            </div>
            <div class="cm-field">
                <label>Foto (URL o subir)</label>
                <div style="display:flex;gap:4px;">
                    <input type="text" id="cm-mom-foto-url" placeholder="https://..." style="flex:1;">
                    <button class="button" id="cm-mom-foto-upload-btn" type="button" style="flex-shrink:0;min-height:28px;padding:0 8px;"><img src="../../assets/img/appIcons/folderIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></button>
                </div>
                <input type="file" id="cm-mom-foto" accept="image/*" style="display:none;">
            </div>
            <p id="cm-status"></p>
        </div>
        <div class="cm-modal-foot">
            <button class="button" id="cm-mom-cancel" type="button">Cancelar</button>
            <button class="button" id="cm-mom-save" type="button">Guardar</button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: AÑADIR RECORDATORIO ═══════════ -->
<div class="cm-modal-overlay" id="cm-rec-modal">
    <div class="cm-modal">
        <div class="cm-modal-bar">
            <span><img src="../../assets/img/appIcons/bellIcon.png" alt="">Añadir recordatorio</span>
            <button class="cm-sheet-close" id="cm-rec-close" type="button">✕</button>
        </div>
        <div class="cm-modal-body">
            <div class="cm-field">
                <label>Título</label>
                <input type="text" id="cm-rec-titulo" maxlength="200">
            </div>
            <div class="cm-field">
                <label>Fecha</label>
                <input type="date" id="cm-rec-fecha">
            </div>
            <div class="cm-field">
                <label>Descripción</label>
                <textarea id="cm-rec-desc" maxlength="2000"></textarea>
            </div>
            <div class="cm-field">
                <label>Periodicidad</label>
                <select id="cm-rec-periodicidad">
                    <option value="ninguna">— Sin repetición —</option>
                    <option value="anual">Anual (cada año)</option>
                    <option value="mensual">Mensual (cada mes)</option>
                    <option value="semanal">Semanal (cada semana)</option>
                </select>
            </div>
            <p id="cm-rec-status"></p>
        </div>
        <div class="cm-modal-foot">
            <button class="button" id="cm-rec-cancel" type="button">Cancelar</button>
            <button class="button" id="cm-rec-save" type="button">Guardar</button>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL: INVITAR PAREJA ═══════════ -->
<div class="cm-modal-overlay" id="cm-invite-modal">
    <div class="cm-modal">
        <div class="cm-modal-bar">
            <span>Invitar a compartir calendario</span>
            <button class="cm-sheet-close" id="cm-invite-close" type="button">✕</button>
        </div>
        <div class="cm-modal-body">
            <p style="font-size:11px;margin-bottom:8px;">Selecciona a quién invitar:</p>
            <div id="cm-user-list"></div>
            <p id="cm-invite-status" style="color:green;"></p>
        </div>
    </div>
</div>

<!-- ═══════════ NOTIFICACIÓN DE INVITACIÓN RECIBIDA ═══════════ -->
<div class="cm-modal-overlay" id="cm-partner-modal">
    <div class="cm-modal">
        <div class="cm-modal-bar">
            <span>Invitación al calendario</span>
        </div>
        <div class="cm-modal-body">
            <p id="cm-partner-msg" style="font-size:12px;margin-bottom:10px;"></p>
            <div class="cm-field">
                <label>Fecha en que empezasteis:</label>
                <input type="date" id="cm-partner-fecha">
            </div>
            <p id="cm-partner-status"></p>
        </div>
        <div class="cm-modal-foot">
            <button class="button" id="cm-partner-reject" type="button">Rechazar</button>
            <button class="button" id="cm-partner-accept" type="button">Aceptar</button>
        </div>
    </div>
</div>

<!-- ═══════════ COUNTDOWN FULLSCREEN ═══════════ -->
<div id="cm-countdown">
    <button id="cm-countdown-close" type="button">✕</button>
    <div class="cd-title" id="cm-cd-titulo">Recordatorio</div>
    <div class="cd-status" id="cm-cd-status">QUEDAN</div>
    <div class="cd-digits" id="cm-cd-digits"></div>
    <div class="cd-fecha" id="cm-cd-fecha"></div>
    <div id="cm-cd-yt-wrap" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;">
        <div id="cm-cd-yt"></div>
    </div>
</div>

<!-- ═══════════ MODAL DE CONFIRMACIÓN ═══════════ -->
<div class="cm-modal-overlay" id="cm-confirm-modal">
    <div class="cm-modal" style="max-width:300px;">
        <div class="cm-modal-bar">
            <span id="cm-confirm-title">Confirmar</span>
        </div>
        <div class="cm-modal-body">
            <p id="cm-confirm-msg" style="font-size:12px;"></p>
        </div>
        <div class="cm-modal-foot">
            <button class="button" id="cm-confirm-cancel" type="button">Cancelar</button>
            <button class="button" id="cm-confirm-ok" type="button">Aceptar</button>
        </div>
    </div>
</div>

<?php
$projectBaseUrl = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
?>
<script>
window.__CM_CFG = {
    API_BASE: <?= json_encode($projectBaseUrl . '/assets/couple/api.php') ?>,
    parejaId: <?= (int)$parejaId ?>,
    fechaInicio: <?= json_encode($fechaInicio) ?>,
    userLabel: <?= json_encode($userLabel) ?>,
    hasPareja: <?= $pareja ? 'true' : 'false' ?>
};
</script>
<script src="https://www.youtube.com/iframe_api"></script>
<script src="calendario-mobile.js?v=<?= filemtime(__DIR__ . '/calendario-mobile.js') ?>"></script>
</body>
</html>
