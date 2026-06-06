<?php
/* ──────────────────────────────────────────────
   WRAPPED — slide presentation estilo Spotify Wrapped
   ──────────────────────────────────────────────
   Devuelve markup HTML auto-contenido (estilos inline) que se inserta
   como overlay full-viewport. La navegación entre slides la maneja
   el JS embebido. La data se obtiene vía ?action=stats al cargar.
   El query param `dev=1` muestra TODAS las plays (sin filtro de año)
   — útil para testeo. */
require_once dirname(__DIR__) . '/assets/config.php';
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';

session_start();
$userKey = $_SESSION['user'] ?? '';
if (!$userKey) { header('Location: /scrapbookOnline/index.php'); exit; }

/* No-cache. */
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

/* Tema activo del usuario — el wrapped adopta los tokens del tema
   (colores, bezels, etc.) para que las slides se sientan parte del SO. */
$userLabel = $loginUsers[$userKey]['label'] ?? '';
refreshActiveThemeCss($userKey, $userLabel);
$_ut         = loadUserThemes($userKey);
$activeTheme = !empty($_ut['active']) ? sanitizeThemeName($_ut['active']) : '';
$themeCssRel = '';
$themeClass  = '';
if ($activeTheme !== '' && isset(((array)$_ut['themes'])[$activeTheme])) {
    $themeClass  = themeCssClassName($activeTheme, $userLabel);
    $themeCssRel = '../' . themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(__DIR__ . '/' . $themeCssRel)) $themeCssRel = '';
}

$isDev = !empty($_GET['dev']);
$year  = (int)($_GET['year'] ?? date('Y'));
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Wrapped <?= $year ?></title>
<link rel="stylesheet" href="../assets/css/98.css">
<link rel="stylesheet" href="../assets/css/tokens.css">
<link rel="stylesheet" href="../assets/css/base.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<?php if ($themeCssRel): ?>
<link rel="stylesheet" id="active-theme-link" href="<?= htmlspecialchars($themeCssRel) ?>">
<?php endif; ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');

    html, body {
        margin: 0; padding: 0;
        width: 100%; height: 100%;
        overflow: hidden;
        font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif;
        color: var(--text, #fff);
        background: #000;
        user-select: none;
    }

    /* Cada slide es un layer full-screen — el backdrop colorido se
       mantiene como ambiente, pero el contenido va dentro de un
       PANEL WIN98 con tokens del tema (chrome raised + bezel inset). */
    .slide {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        opacity: 0;
        pointer-events: none;
        padding: 6vh 4vw;
        box-sizing: border-box;
        transform: scale(0.95);
        transition: opacity 0.55s ease, transform 0.55s cubic-bezier(0.34, 1.4, 0.64, 1);
    }
    .slide.active {
        opacity: 1;
        pointer-events: auto;
        transform: scale(1);
        z-index: 2;
    }
    /* Slide saliente — mientras dura la animación, se aleja con un
       leve fade y zoom-out para que la entrada de la siguiente sea
       más visible. */
    .slide.exiting {
        opacity: 0;
        transform: scale(1.06);
        z-index: 1;
    }
    /* Variantes direccionales según el sentido del slide change. */
    .slide.exiting.exit-left  { transform: translateX(-12%) scale(0.95); }
    .slide.exiting.exit-right { transform: translateX( 12%) scale(0.95); }
    .slide.entering.enter-left  { transform: translateX(-12%) scale(0.95); }
    .slide.entering.enter-right { transform: translateX( 12%) scale(0.95); }
    /* DRIFT del backdrop — el gradient se desplaza muy lentamente
       para evitar que el fondo se sienta estático. */
    .slide.active {
        background-size: 200% 200% !important;
        animation: bgDrift 12s ease-in-out infinite alternate;
    }
    @keyframes bgDrift {
        0%   { background-position:   0%   0%; }
        100% { background-position: 100% 100%; }
    }
    /* Capa de "spotlight" que orbita lentamente — copia el truco de
       Spotify Wrapped real (un círculo radial blanco semi-transparente
       que da sensación de luz pulsante). */
    .slide::before {
        content: '';
        position: absolute; inset: -50%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.18), transparent 50%);
        pointer-events: none;
        animation: spotlight 14s linear infinite;
        z-index: 0;
    }
    .slide-panel { position: relative; z-index: 1; }
    @keyframes spotlight {
        0%   { transform: rotate(0deg)   translateX(0); }
        50%  { transform: rotate(180deg) translateX(15%); }
        100% { transform: rotate(360deg) translateX(0); }
    }

    /* Panel Win98 centrado dentro del slide. Background + bezel del
       tema del usuario. */
    .slide-panel {
        background: var(--win-bg, silver);
        color: var(--text, #000);
        padding: 32px 28px;
        max-width: min(720px, 92vw);
        max-height: 86vh;
        overflow-y: auto;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            8px 8px 24px rgba(0,0,0,0.4),
            0 0 60px rgba(0,0,0,0.25);
        display: flex; flex-direction: column;
        align-items: center;
        gap: 14px;
        opacity: 0;
        transform: translateY(40px) scale(0.92);
    }
    .slide.active .slide-panel {
        animation: panelIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    @keyframes panelIn {
        from { opacity: 0; transform: translateY(40px) scale(0.92); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Hijos del panel: animación staggered (entran de uno en uno con
       delay creciente — se aplica via --i en cada elemento). */
    .slide.active .slide-panel > * {
        opacity: 0;
        animation: childUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        animation-delay: calc(0.25s + var(--i, 0) * 0.08s);
    }
    @keyframes childUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Items dentro de top-list — entran ZIG-ZAG, alternando lados.
       Even-index: desde la izquierda con rotación leve.
       Odd-index : desde la derecha. */
    .slide.active .top-item {
        opacity: 0;
        animation: itemInL 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        animation-delay: calc(0.5s + var(--i, 0) * 0.1s);
    }
    .slide.active .top-item:nth-child(even) {
        animation-name: itemInR;
    }
    @keyframes itemInL {
        from { opacity: 0; transform: translateX(-60px) rotate(-3deg); }
        50%  { opacity: 1; transform: translateX(10px)  rotate(1deg); }
        to   { opacity: 1; transform: translateX(0)     rotate(0); }
    }
    @keyframes itemInR {
        from { opacity: 0; transform: translateX(60px)  rotate(3deg); }
        50%  { opacity: 1; transform: translateX(-10px) rotate(-1deg); }
        to   { opacity: 1; transform: translateX(0)     rotate(0); }
    }

    /* BIG NUMBER — explosión: zoom desde 0 con bounce + brillo. */
    .slide.active .big-number,
    .slide.active .big-number-text {
        animation: bigPop 1.1s cubic-bezier(0.34, 1.8, 0.64, 1) 0.3s both;
    }
    @keyframes bigPop {
        0%   { opacity: 0; transform: scale(0.2) rotate(-8deg); }
        60%  { opacity: 1; transform: scale(1.18) rotate(2deg); }
        80%  {              transform: scale(0.95) rotate(-1deg); }
        100% { opacity: 1; transform: scale(1) rotate(0); }
    }

    /* Title de la slide — entra desde arriba con efecto "swing". */
    .slide.active .slide-title {
        animation: titleSwing 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.35s both;
    }
    @keyframes titleSwing {
        from { opacity: 0; transform: translateY(-30px) rotate(-2deg); }
        70%  { opacity: 1; transform: translateY(4px) rotate(0.5deg); }
        to   { opacity: 1; transform: translateY(0) rotate(0); }
    }

    /* Letras individuales del hero-title — cascading reveal estilo
       Spotify (cuando el helper letterSplit divide el texto). */
    .letter {
        display: inline-block;
        opacity: 0;
        transform: translateY(40px) rotate(8deg);
    }
    .slide.active .letter {
        animation: letterUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        animation-delay: calc(0.5s + var(--li, 0) * 0.04s);
    }
    @keyframes letterUp {
        from { opacity: 0; transform: translateY(40px) rotate(8deg); }
        to   { opacity: 1; transform: translateY(0)    rotate(0); }
    }

    .slide-title {
        font-size: clamp(24px, 4vw, 44px);
        font-weight: bold;
        margin: 0 0 8px;
        text-align: center;
        line-height: 1.1;
        color: var(--text, #000);
    }
    .slide-subtitle {
        font-size: clamp(13px, 1.6vw, 18px);
        font-weight: normal;
        margin: 0;
        text-align: center;
        color: var(--text-muted, var(--text, #444));
    }
    .big-number {
        font-size: clamp(80px, 14vw, 180px);
        font-weight: bold;
        line-height: 1;
        color: var(--accent, var(--text, #000));
        text-align: center;
    }
    .big-number-text {
        font-size: clamp(36px, 6vw, 76px);
        font-weight: bold;
        line-height: 1.05;
        color: var(--accent, var(--text, #000));
        text-align: center;
    }

    /* Lista de top items — cada item es un panel Win98 inset. */
    .top-list {
        display: flex; flex-direction: column;
        gap: 6px;
        width: 100%;
        margin: 6px 0 0;
    }
    .top-item {
        display: flex; align-items: center;
        gap: 12px;
        padding: 8px 10px;
        background: var(--input-bg, var(--win-bg, silver));
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff),
            inset  2px  2px var(--bezel-dark-1, #0a0a0a),
            inset -2px -2px var(--bezel-light-2, #dfdfdf);
    }
    .top-rank {
        font-size: 22px; font-weight: bold;
        width: 32px; text-align: center;
        color: var(--accent, var(--text, #000));
    }
    .top-cover {
        width: 44px; height: 44px;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--input-bg, #fff);
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff);
    }
    .top-meta {
        flex: 1; min-width: 0;
    }
    .top-meta-title {
        font-weight: bold;
        font-size: 12px;
        color: var(--text, #000);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .top-meta-sub {
        font-size: 11px;
        color: var(--text-muted, var(--text, #444));
        margin-top: 1px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .top-plays {
        font-size: 11px;
        font-weight: bold;
        color: var(--accent, var(--text, #000));
        white-space: nowrap;
    }

    /* Hero card para "top 1". */
    .hero {
        display: flex; flex-direction: column;
        align-items: center;
        gap: 14px;
    }
    .hero-cover {
        width: clamp(120px, 24vw, 220px);
        height: clamp(120px, 24vw, 220px);
        object-fit: cover;
        background: var(--input-bg, #fff);
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            4px 4px 12px rgba(0,0,0,0.3);
        animation: heroPop 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
    }
    @keyframes heroPop {
        0%   { opacity: 0; transform: scale(0.2) rotate(-15deg); }
        40%  { opacity: 1; transform: scale(1.15) rotate(6deg); }
        60%  { transform: scale(0.95) rotate(-3deg); }
        80%  { transform: scale(1.05) rotate(1deg); }
        100% { opacity: 1; transform: scale(1) rotate(0); }
    }
    /* Flotación suave del hero después del pop (idle). */
    .slide.active .hero-cover,
    .slide.active .hero-circle {
        animation: heroPop 1s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both,
                   heroFloat 4s ease-in-out 1.5s infinite;
    }
    @keyframes heroFloat {
        0%, 100% { transform: translateY(0) rotate(0); }
        50%      { transform: translateY(-8px) rotate(0); }
    }
    .hero-circle {
        width: clamp(120px, 24vw, 220px);
        height: clamp(120px, 24vw, 220px);
        background: var(--win-bg, silver);
        display: flex; align-items: center; justify-content: center;
        font-size: clamp(54px, 10vw, 110px);
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            4px 4px 12px rgba(0,0,0,0.3);
        animation: heroPop 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
    }
    .hero-title {
        font-size: clamp(22px, 4vw, 44px);
        font-weight: bold;
        text-align: center;
        line-height: 1.1;
        max-width: min(640px, 90vw);
        color: var(--text, #000);
    }
    .hero-sub {
        font-size: clamp(12px, 1.4vw, 16px);
        color: var(--text-muted, var(--text, #444));
        text-align: center;
    }

    /* Controles — botones Win98 fijos abajo. */
    #wrapped-controls {
        position: fixed;
        bottom: 16px; right: 16px;
        display: flex; gap: 4px;
        z-index: 100;
    }
    .ctrl-btn {
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        padding: 4px 14px;
        font-size: 12px;
        font-weight: normal;
        font-family: inherit;
        border: 0;
        border-radius: 0;
        cursor: pointer;
        min-width: 80px;
        min-height: 26px;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    .ctrl-btn:active {
        box-shadow:
            inset -1px -1px var(--bezel-light-1, #fff),
            inset  1px  1px var(--bezel-dark-1, #0a0a0a),
            inset -2px -2px var(--bezel-light-2, #dfdfdf),
            inset  2px  2px var(--bezel-dark-2, grey);
    }

    /* Progress segments — barras de mini-Win98. */
    #wrapped-progress {
        position: fixed;
        top: 12px; left: 12px; right: 60px;
        display: flex; gap: 3px;
        z-index: 100;
    }
    .progress-segment {
        flex: 1;
        height: 8px;
        background: var(--input-bg, #fff);
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff);
        overflow: hidden;
    }
    .progress-segment.done {
        background: var(--accent, #1db954);
    }
    .progress-segment.active::after {
        content: ''; display: block;
        height: 100%; width: 0;
        background: var(--accent, #1db954);
        animation: fillProgress 6s linear forwards;
    }
    @keyframes fillProgress { to { width: 100%; } }

    /* ── TARJETA DE RESUMEN (última slide) ─────────────────────────
       Layout estilo Spotify Wrapped: foto del top artist arriba, dos
       columnas con top artists/songs, minutos y género abajo. Toda la
       tarjeta tiene chrome Win98 y se exporta como PNG. */
    .wrapped-card-wrapper {
        display: flex; flex-direction: column;
        align-items: center;
        gap: 16px;
    }
    .wrapped-card {
        background: var(--win-bg, silver);
        color: var(--text, #000);
        width: min(400px, 92vw);
        padding: 0;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            6px 6px 0 var(--bezel-dark-1, #0a0a0a);
    }
    .wc-hero {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 0.95;
        overflow: hidden;
        background: var(--input-bg, #fff);
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff);
    }
    .wc-hero-img {
        width: 100%; height: 100%;
        object-fit: cover;
    }
    .wc-hero-fallback {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 80px;
        background: var(--accent, #1db954);
        color: var(--accent-text, #fff);
    }
    /* "2026" decorativo lateral. Texto con outline negro + glow para
       que sea SIEMPRE legible sobre cualquier foto de artista
       (independientemente de colores claros u oscuros del fondo). */
    .wc-hero-year {
        position: absolute;
        left: 6px; top: 50%;
        transform: translateY(-50%) rotate(-90deg);
        transform-origin: left center;
        font-size: 56px;
        font-weight: bold;
        color: #fff;
        letter-spacing: -2px;
        pointer-events: none;
        -webkit-text-stroke: 2px #000;
        text-shadow:
            -1px -1px 0 #000,
             1px -1px 0 #000,
            -1px  1px 0 #000,
             1px  1px 0 #000,
             0 0 10px rgba(0,0,0,0.65);
        z-index: 2;
    }
    /* Banda oscura semi-transparente detrás del año — refuerza
       contraste cuando la foto es muy clara o ruidosa. */
    .wc-hero::before {
        content: '';
        position: absolute;
        left: 0; top: 0;
        width: 70px; height: 100%;
        background: linear-gradient(90deg, rgba(0,0,0,0.45), rgba(0,0,0,0));
        pointer-events: none;
        z-index: 1;
    }
    .wc-body {
        padding: 16px 18px 14px;
    }
    .wc-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 14px;
    }
    .wc-col h4 {
        font-size: 10px;
        font-weight: bold;
        margin: 0 0 4px;
        color: var(--text-muted, var(--text, #555));
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wc-col ol {
        margin: 0; padding: 0;
        list-style: none;
        font-size: 12px;
        line-height: 1.35;
    }
    /* WRAP a 2 líneas (line-clamp) en vez de cortar con ellipsis a 1
       línea — ahora nombres largos como "The Smashing Pumpkins" o
       "Don't Look Back in Anger" se ven enteros. */
    .wc-col ol li {
        display: flex;
        align-items: flex-start;
        gap: 4px;
        margin-bottom: 3px;
        overflow: hidden;
        word-break: break-word;
    }
    .wc-col ol li > span:not(.rank) {
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .wc-col ol li span.rank {
        flex-shrink: 0;
        width: 14px;
        color: var(--accent, var(--text, #000));
        font-weight: bold;
    }
    .wc-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        padding-top: 10px;
        border-top: 1px solid var(--bezel-dark-2, #888);
    }
    .wc-stat-label {
        font-size: 10px;
        color: var(--text-muted, var(--text, #555));
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    .wc-stat-val {
        font-size: 26px;
        font-weight: bold;
        color: var(--accent, var(--text, #000));
        line-height: 1;
    }
    .wc-footer {
        margin-top: 14px;
        padding-top: 8px;
        border-top: 1px solid var(--bezel-dark-2, #888);
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text-muted, var(--text, #444));
    }
    .wc-footer .wc-logo {
        font-size: 14px;
        color: var(--accent, #1db954);
    }

    /* Botones de share — Win98 row. */
    .wc-share-row {
        display: flex; gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .wc-share-btn {
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        font-family: inherit;
        font-size: 11px;
        padding: 4px 14px;
        min-height: 28px;
        border: 0;
        border-radius: 0;
        cursor: pointer;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    .wc-share-btn:active {
        box-shadow:
            inset -1px -1px var(--bezel-light-1, #fff),
            inset  1px  1px var(--bezel-dark-1, #0a0a0a),
            inset -2px -2px var(--bezel-light-2, #dfdfdf),
            inset  2px  2px var(--bezel-dark-2, grey);
    }

    /* Now playing — arriba-izquierda, debajo del progress bar. */
    #wrapped-now-playing {
        position: fixed;
        top: 28px; left: 12px;
        display: none;
        align-items: center;
        gap: 8px;
        padding: 6px 10px 6px 8px;
        background: var(--win-bg, silver);
        color: var(--text, #000);
        font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif;
        font-size: 11px;
        max-width: 240px;
        z-index: 102;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    #wrapped-now-playing.visible {
        display: flex;
        animation: npSlideIn 0.4s ease;
    }
    @keyframes npSlideIn {
        from { opacity: 0; transform: translateX(-30px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    .np-icon {
        font-size: 18px;
        color: var(--accent, #1db954);
        animation: npPulse 1.4s ease-in-out infinite;
    }
    @keyframes npPulse {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50%      { opacity: 1; transform: scale(1.2); }
    }
    .np-meta { min-width: 0; flex: 1; }
    .np-title {
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .np-artist {
        color: var(--text-muted, var(--text, #444));
        font-size: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #wrapped-close {
        position: fixed;
        top: 8px; right: 8px;
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        font-size: 14px;
        font-weight: bold;
        font-family: inherit;
        width: 28px; height: 28px;
        cursor: pointer;
        border: 0;
        border-radius: 0;
        z-index: 101;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    #wrapped-close:active {
        box-shadow:
            inset -1px -1px var(--bezel-light-1, #fff),
            inset  1px  1px var(--bezel-dark-1, #0a0a0a),
            inset -2px -2px var(--bezel-light-2, #dfdfdf),
            inset  2px  2px var(--bezel-dark-2, grey);
    }

    .empty-state {
        font-size: clamp(14px, 2vw, 18px);
        opacity: 0.7;
        font-style: italic;
        margin-top: 14px;
    }

    /* Gráfica de barras por mes — 12 columnas a igual ancho. */
    .months-chart {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 6px;
        width: min(700px, 92vw);
        height: clamp(180px, 40vh, 340px);
        margin: 24px 0 12px;
    }
    .month-col {
        flex: 1;
        display: flex; flex-direction: column;
        align-items: center;
        gap: 6px;
        max-width: 56px;
    }
    .month-bar-wrap {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .month-bar {
        width: 100%;
        background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.55));
        border-radius: 6px 6px 0 0;
        min-height: 2px;
        position: relative;
        transition: height 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .month-bar.top-month {
        background: linear-gradient(180deg, #fff, #ffd166);
        box-shadow: 0 0 30px rgba(255,209,102,0.6);
    }
    .month-bar-val {
        position: absolute;
        top: -22px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        white-space: nowrap;
    }
    .month-col-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        opacity: 0.85;
    }
</style>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">

<div id="wrapped-progress"></div>
<button id="wrapped-close" title="Cerrar">✕</button>

<!-- Now playing — esquina superior izquierda, debajo del progress bar.
     Muestra título + artista de la canción que suena de fondo. -->
<div id="wrapped-now-playing">
    <span class="np-icon">♪</span>
    <div class="np-meta">
        <div class="np-title" id="np-title"></div>
        <div class="np-artist" id="np-artist"></div>
    </div>
</div>

<div id="wrapped-root"></div>

<!-- YT player oculto (1×1 px). Reproduce las top songs del usuario en
     loop como banda sonora del wrapped. -->
<div id="yt-player-holder" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;"></div>

<div id="wrapped-controls">
    <button class="ctrl-btn" id="wrapped-mute" title="Silenciar música">🔊</button>
    <button class="ctrl-btn" id="wrapped-prev">‹ Atrás</button>
    <button class="ctrl-btn" id="wrapped-next">Siguiente ›</button>
</div>

<script>
const IS_DEV = <?= $isDev ? 'true' : 'false' ?>;
const YEAR   = <?= (int)$year ?>;

/* Cargar stats y construir slides. */
fetch('../assets/music/wrapped-api.php?action=stats&year=' + YEAR + (IS_DEV ? '&all=1' : ''))
    .then(r => r.json())
    .then(buildSlides)
    .catch(e => {
        document.getElementById('wrapped-root').innerHTML =
            '<div class="slide active" id="slide-err" style="background:#222"><h1 class="slide-title">Error al cargar stats</h1><p class="slide-subtitle">' + (e.message || e) + '</p></div>';
    });

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

/** Shuffle Fisher-Yates in-place. Devuelve el mismo array shuffled. */
function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

/** Envuelve cada carácter en un <span class="letter" style="--li:N">
 *  para que la animación CSS los anime escalonadamente, generando un
 *  efecto cascading de texto. Espacios se preservan. */
function letterSplit(str) {
    return String(str).split('').map((ch, idx) =>
        ch === ' '
            ? '<span class="letter" style="--li:' + idx + '">&nbsp;</span>'
            : '<span class="letter" style="--li:' + idx + '">' + escapeHtml(ch) + '</span>'
    ).join('');
}

/** Anima un count-up de 0 hasta `target` durante `durMs` ms en el
 *  elemento. Se llama cuando la slide se activa. */
function animateCountUp(el, target, durMs) {
    target = Number(target) || 0;
    if (target <= 0) { el.textContent = '0'; return; }
    const start = performance.now();
    const fmt = n => n.toLocaleString('es-ES');
    function tick(now) {
        const t = Math.min(1, (now - start) / durMs);
        /* Easing: ease-out-cubic para que llegue suave al valor final. */
        const e = 1 - Math.pow(1 - t, 3);
        el.textContent = fmt(Math.round(target * e));
        if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

/* Paleta de slides generada a partir del COLOR ACCENT del tema del
   usuario. Cada slide recibe un gradient distinto (complementarios,
   analogous, triadic) derivado del accent base para que el wrapped
   se sienta personal y coherente con la apariencia del SO. */

/** Lee el var CSS `--accent` resuelto y devuelve un HSL. Si no se
 *  puede leer (sin tema), cae a verde Spotify. */
function readAccentHSL() {
    const probe = document.createElement('div');
    probe.style.color = 'var(--accent)';
    document.body.appendChild(probe);
    const resolved = getComputedStyle(probe).color;
    probe.remove();
    /* Parse "rgb(r, g, b)" */
    const m = resolved.match(/rgba?\(\s*(\d+)\D+(\d+)\D+(\d+)/);
    if (!m) return { h: 141, s: 73, l: 42 }; /* fallback Spotify green */
    const r = +m[1] / 255, g = +m[2] / 255, b = +m[3] / 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    const l = (max + min) / 2;
    let h, s;
    if (max === min) { h = 0; s = 0; }
    else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = ((g - b) / d + (g < b ? 6 : 0)) * 60; break;
            case g: h = ((b - r) / d + 2) * 60; break;
            case b: h = ((r - g) / d + 4) * 60; break;
        }
    }
    return { h: h | 0, s: (s * 100) | 0, l: (l * 100) | 0 };
}
function hsl(h, s, l) { return `hsl(${(h + 360) % 360},${Math.max(0, Math.min(100, s))}%,${Math.max(0, Math.min(100, l))}%)`; }
function grad(deg, ...stops) { return `linear-gradient(${deg}deg,${stops.join(',')})`; }

/* Construimos el catálogo después de leer el tema (en buildSlides).
   Estrategia: TODAS las slides usan variaciones dentro de ±25° del
   accent base, variando L y S. Así el wrapped se siente parte del
   mismo tema. Antes usaba complementarios (h+180) que salían colores
   totalmente distintos al tema → corregido. */
let SLIDE_BG = {};
function buildSlideBgs() {
    const a = readAccentHSL();
    /* Aseguramos saturación mínima para que se vean los gradients
       (si el accent es muy gris no salía nada). */
    const S = Math.max(60, a.s);
    /* Helper para variaciones cohesivas. */
    const dx = (hd, sd, l1, l2) => grad(135, hsl(a.h + hd, S + sd, l1), hsl(a.h + hd, S + sd, l2));
    SLIDE_BG = {
        /* Welcome: el propio accent oscuro → muy oscuro (intro elegante). */
        welcome:     dx(0,    0, 28, 12),
        /* Minutos: tono vivo, mid-light. */
        minutes:     dx(-10,  0, 55, 32),
        /* Top mes: pastel cálido (hue +15, L alta, S baja). */
        topMonth:    dx(15,  -15, 70, 50),
        /* Chart meses: oscuro plano para que las barras blancas
           resalten al máximo. */
        monthsChart: dx(-10,  0, 30, 12),
        /* Top song HERO: tono dorado del accent (mid + algo de hue+). */
        topSong:     dx(10,   0, 50, 28),
        /* Lista canciones: misma tonalidad, un toque más fría. */
        songsList:   dx(-15,  0, 45, 22),
        /* Top artist HERO: hue ligero positivo, más saturación. */
        topArtist:   dx(20,   5, 55, 30),
        /* Lista artistas. */
        artistsList: dx(15,   0, 42, 20),
        /* Top album HERO: tono frío (hue-), profundo. */
        topAlbum:    dx(-20,  0, 48, 25),
        /* Lista álbumes: profundo. */
        albumsList:  dx(-20,  0, 35, 14),
        /* Top género HERO: tono claro, casi neon. */
        topGenre:    dx(25,  10, 60, 38),
        /* Lista géneros. */
        genres:      dx(20,   5, 40, 18),
        /* Cierre: mismo que welcome para cerrar el ciclo. */
        closure:     dx(0,    0, 26, 10),
    };
}

function buildSlides(data) {
    if (!data || !data.ok) {
        document.getElementById('wrapped-root').innerHTML =
            `<div class="slide active" style="background:#222"><h1 class="slide-title">Sin datos</h1><p class="slide-subtitle">${(data && data.error) || 'No hay reproducciones registradas todavía.'}</p></div>`;
        return;
    }

    /* Generar paleta a partir del accent del tema antes de empezar. */
    buildSlideBgs();

    /* Cola del reproductor de fondo: las top songs del usuario. */
    window.__wrappedSongs = (data.songs || []).slice(0, 5);

    /* Helpers de data-song-* — declarados ANTES de pick/groupSong para
       evitar TDZ (groupSong llama a songAttrs). */
    const songAttrs = (vid, title, artist) =>
        vid ? `data-song-id="${escapeHtml(vid)}" data-song-title="${escapeHtml(title || '')}" data-song-artist="${escapeHtml(artist || '')}"` : '';
    const songPoolAttrs = (pool) => {
        if (!pool || !pool.length) return '';
        const json = pool.map(s => ({ v: s.video_id, t: s.title || '', a: s.artist || '' }));
        return `data-song-pool='${escapeHtml(JSON.stringify(json))}'`;
    };

    /* Pre-elegir UNA canción por GRUPO garantizando que NO se repiten
       entre grupos. Cada grupo "claim" una canción distinta:
       - opening : random "considerable" (NO en used).
       - topSong : data.songs[0] FIJA — pero claim primero para
                   que los siguientes la eviten.
       - artist  : random del top artista, NO en used.
       - album   : random del top álbum, NO en used.
       - genre   : random del top género, NO en used.
       Si un pool no tiene ninguna canción libre, se devuelve la
       primera barajeada como fallback (puede repetir solo en pools
       muy pequeños). */
    /* Pick estricto: NUNCA devuelve una canción ya usada. Si todo el
       pool está en used, devuelve null (mejor que repetir la top song
       por error como hacía antes con `shuffled[0]` de fallback). */
    const usedVids = new Set();
    const pickUnique = (pool) => {
        if (!pool || !pool.length) return null;
        const shuffled = shuffle(pool.slice());
        for (const s of shuffled) {
            if (s.video_id && !usedVids.has(s.video_id)) {
                usedVids.add(s.video_id);
                return s;
            }
        }
        return null;
    };

    const __topArtist = (data.artists || [])[0];
    const __topAlbum  = (data.albums  || [])[0];
    const __topGenre  = (data.genres  || [])[0];
    const __topSongVid = data.songs && data.songs[0] ? data.songs[0].video_id : null;

    /* Pre-claim: topSong primero (fija). */
    const songForGroup = {};
    if (data.songs && data.songs[0]) {
        songForGroup.topSong = {
            video_id: data.songs[0].video_id,
            title:    data.songs[0].title,
            artist:   data.songs[0].artist,
        };
        usedVids.add(data.songs[0].video_id);
    } else {
        songForGroup.topSong = null;
    }

    /* OPENING — filtramos la top song del pool de "considerables" para
       que NUNCA pueda coincidir con topSong. Y elegimos PRIMERO opening
       (antes de artist/album/genre) para que tenga el máximo pool
       libre — opening tiene el pool más grande (20 canciones) así que
       sacrificarlo del orden no afecta a los demás. */
    const consPool = (data.considerable_songs || data.songs || [])
        .filter(s => s.video_id && s.video_id !== __topSongVid);
    songForGroup.opening = pickUnique(consPool);
    /* Si el usuario solo tiene la top song con plays suficientes,
       opening queda null → fallback duro: data.songs[1] o cualquier
       canción de data.songs que NO sea topSong. */
    if (!songForGroup.opening) {
        const alt = (data.songs || []).find(s => s.video_id && s.video_id !== __topSongVid);
        if (alt) {
            songForGroup.opening = { video_id: alt.video_id, title: alt.title, artist: alt.artist };
            usedVids.add(alt.video_id);
        }
    }

    /* Después, artist/album/genre con sus pools propios. */
    songForGroup.artist  = __topArtist ? pickUnique(__topArtist.top_songs) : null;
    songForGroup.album   = __topAlbum  ? pickUnique(__topAlbum.top_songs)  : null;
    songForGroup.genre   = __topGenre  ? pickUnique(__topGenre.top_songs)  : null;

    const groupSong = (g) => {
        const s = songForGroup[g];
        return s ? songAttrs(s.video_id, s.title, s.artist) : '';
    };
    /* El player se inicializa AL FINAL de buildSlides (después de
       showSlide(0)) para que el slide activo ya esté en el DOM y el
       initWrappedPlayer pueda leer su data-song-id. */

    const root = document.getElementById('wrapped-root');
    const slides = [];
    let i = 0;
    function makeSlide(bgKey, inner, extraAttrs) {
        /* Envoltorio Win98: backdrop con el gradient del catálogo +
           panel central con estilo de ventana Win98 (theme tokens).
           `extraAttrs` permite adjuntar data-* (ej. data-song-id para
           sincronizar la música de fondo con la slide activa). */
        const attrs = extraAttrs ? ' ' + extraAttrs : '';
        return `<div class="slide" id="slide-${i++}" style="background:${SLIDE_BG[bgKey]};"${attrs}>
                  <div class="slide-panel">${inner}</div>
                </div>`;
    }

    /* Welcome — grupo "opening" (4 primeras slides comparten canción). */
    slides.push(makeSlide('welcome', `
        <div class="slide-subtitle" style="opacity:0.85;letter-spacing:4px;">SCRAPBOOK</div>
        <h1 class="slide-title" style="font-size:clamp(48px,10vw,140px);">Wrapped<br><span style="font-weight:400;">${YEAR}</span></h1>
        <p class="slide-subtitle">Tu año en música</p>
    `, groupSong('opening')));

    /* Total minutos — count-up animado. data-countup → el handler de
       showSlide lo dispara con animateCountUp al activar la slide. */
    const totalMin   = Number(data.total_min)   || 0;
    const totalPlays = Number(data.total_plays) || 0;
    slides.push(makeSlide('minutes', `
        <p class="slide-subtitle">Escuchaste</p>
        <div class="big-number" data-countup="${totalMin}">0</div>
        <p class="slide-subtitle">minutos de música<br>(${totalPlays.toLocaleString('es-ES')} reproducciones)</p>
        ${totalPlays === 0 ? '<p class="empty-state" style="margin-top:20px;">Empieza a reproducir música para acumular minutos.</p>' : ''}
    `, groupSong('opening')));

    /* Mes con MÁS escuchas. Va justo después del total para construir
       narrativa "escuchaste X minutos, y el mes más fuerte fue...". */
    const topMonth = data.top_month;
    if (topMonth && topMonth.minutes > 0) {
        slides.push(makeSlide('topMonth', `
            <p class="slide-subtitle">Tu mes top fue</p>
            <div class="big-number" style="color:#a83254;text-shadow:0 6px 30px rgba(168,50,84,0.3);">${escapeHtml(topMonth.name)}</div>
            <p class="slide-subtitle">Escuchaste <strong>${topMonth.minutes.toLocaleString('es-ES')}</strong> minutos<br>(${topMonth.plays.toLocaleString('es-ES')} reproducciones)</p>
        `, groupSong('opening')));
    }

    /* Gráfica anual: 12 barras (Ene-Dic). La barra del top month
       destaca en dorado. Las alturas se calculan en % del máximo
       para que la columna mayor llegue al 100% del contenedor. */
    const months = data.months_breakdown || [];
    const maxMin = months.reduce((mx, m) => Math.max(mx, m.minutes), 0);
    if (maxMin > 0) {
        const topMonthNum = topMonth ? topMonth.month_num : -1;
        slides.push(makeSlide('monthsChart', `
            <h1 class="slide-title" style="font-size:clamp(24px,4vw,44px);">Tu año mes a mes</h1>
            <p class="slide-subtitle" style="margin-top:-8px;">Minutos escuchados</p>
            <div class="months-chart">
                ${months.map(m => {
                    const pct = (m.minutes / maxMin) * 100;
                    const isTop = m.m === topMonthNum;
                    /* data-h: altura final en %. CSS arranca con 0 y el
                       handler de showSlide lo eleva escalonadamente al
                       activarse la slide. */
                    return `
                        <div class="month-col">
                            <div class="month-bar-wrap">
                                <div class="month-bar ${isTop ? 'top-month' : ''}" data-h="${pct}" style="height:0%;">
                                    ${m.minutes > 0 ? `<span class="month-bar-val">${m.minutes.toLocaleString('es-ES')}</span>` : ''}
                                </div>
                            </div>
                            <div class="month-col-label">${escapeHtml(m.short)}</div>
                        </div>
                    `;
                }).join('')}
            </div>
        `, groupSong('opening')));
    }

    /* (songAttrs y songPoolAttrs declarados arriba, antes de groupSong) */

    /* Top song HERO — siempre la #1 (grupo topSong). */
    const topSong = (data.songs || [])[0];
    if (topSong) {
        slides.push(makeSlide('topSong', `
            <p class="slide-subtitle">Tu canción del año</p>
            <div class="hero">
                <img class="hero-cover" src="${escapeHtml(topSong.cover_url)}" alt="cover" onerror="this.style.display='none'">
                <div>
                    <div class="hero-title">${letterSplit(topSong.title)}</div>
                    <div class="hero-sub" style="text-align:center;margin-top:8px;">${escapeHtml(topSong.artist || 'Artista desconocido')} · ${topSong.plays} plays</div>
                </div>
            </div>
        `, groupSong('topSong')));
    }

    /* Top 5 canciones — misma canción #1 que el HERO. */
    if ((data.songs || []).length > 0) {
        slides.push(makeSlide('songsList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Top canciones</h1>
            <div class="top-list">
                ${data.songs.map((s, idx) => `
                    <div class="top-item" style="--i:${idx}">
                        <div class="top-rank">${idx+1}</div>
                        <img class="top-cover" src="${escapeHtml(s.cover_url)}" onerror="this.style.opacity=0.3">
                        <div class="top-meta">
                            <div class="top-meta-title">${escapeHtml(s.title)}</div>
                            <div class="top-meta-sub">${escapeHtml(s.artist || '—')}</div>
                        </div>
                        <div class="top-plays">${s.plays}×</div>
                    </div>
                `).join('')}
            </div>
        `, groupSong('topSong')));
    }

    /* Top artist HERO — foto de Spotify si está disponible, fallback 🎤. */
    const topArtist = (data.artists || [])[0];
    if (topArtist) {
        const heroImg = topArtist.image_url
            ? `<img class="hero-cover" src="${escapeHtml(topArtist.image_url)}" alt="artist" style="border-radius:50%;" onerror="this.outerHTML='<div class=\\'hero-circle\\'>🎤</div>'">`
            : `<div class="hero-circle">🎤</div>`;
        slides.push(makeSlide('topArtist', `
            <p class="slide-subtitle">Tu artista del año</p>
            <div class="hero">
                ${heroImg}
                <div>
                    <div class="hero-title">${letterSplit(topArtist.artist)}</div>
                    <div class="hero-sub" style="text-align:center;margin-top:8px;">${topArtist.plays} reproducciones</div>
                </div>
            </div>
        `, groupSong('artist')));
    }

    /* Top 5 artistas — cada uno con su foto de perfil (Spotify) o
       fallback 🎤 si no se encontró. La cover redonda diferencia
       visualmente artistas de canciones/álbumes. */
    if ((data.artists || []).length > 0) {
        const a1 = data.artists[0];
        slides.push(makeSlide('artistsList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus artistas top</h1>
            <div class="top-list">
                ${data.artists.map((a, idx) => {
                    const img = a.image_url
                        ? `<img class="top-cover" style="border-radius:50%;object-fit:cover;" src="${escapeHtml(a.image_url)}" onerror="this.outerHTML='<div class=\\'top-cover\\' style=\\'display:flex;align-items:center;justify-content:center;font-size:28px;background:rgba(255,255,255,0.15);border-radius:50%;\\'>🎤</div>'">`
                        : `<div class="top-cover" style="display:flex;align-items:center;justify-content:center;font-size:28px;background:rgba(255,255,255,0.15);border-radius:50%;">🎤</div>`;
                    return `
                        <div class="top-item" style="--i:${idx}">
                            <div class="top-rank">${idx+1}</div>
                            ${img}
                            <div class="top-meta">
                                <div class="top-meta-title">${escapeHtml(a.artist)}</div>
                            </div>
                            <div class="top-plays">${a.plays}×</div>
                        </div>
                    `;
                }).join('')}
            </div>
        `, groupSong('artist')));
    }

    /* Top álbum HERO (del melon archive — reproducciones + imports) */
    const topAlbum = (data.albums || [])[0];
    if (topAlbum) {
        slides.push(makeSlide('topAlbum', `
            <p class="slide-subtitle">Tu álbum del año</p>
            <div class="hero">
                ${topAlbum.cover_url
                    ? `<img class="hero-cover" src="${escapeHtml(topAlbum.cover_url)}" alt="album" onerror="this.style.display='none'">`
                    : `<div class="hero-circle">💿</div>`}
                <div>
                    <div class="hero-title">${letterSplit(topAlbum.title)}</div>
                    <div class="hero-sub" style="text-align:center;margin-top:8px;">${escapeHtml(topAlbum.artist || 'Artista desconocido')} · ${topAlbum.plays} interacciones</div>
                </div>
            </div>
        `, groupSong('album')));
    }

    /* Top 5 álbumes */
    if ((data.albums || []).length > 0) {
        const al1 = data.albums[0];
        slides.push(makeSlide('albumsList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus álbumes top</h1>
            <div class="top-list">
                ${data.albums.map((al, idx) => `
                    <div class="top-item" style="--i:${idx}">
                        <div class="top-rank">${idx+1}</div>
                        ${al.cover_url
                            ? `<img class="top-cover" src="${escapeHtml(al.cover_url)}" onerror="this.style.background='rgba(255,255,255,0.1)';this.style.opacity=0.3">`
                            : `<div class="top-cover" style="display:flex;align-items:center;justify-content:center;font-size:24px;">💿</div>`}
                        <div class="top-meta">
                            <div class="top-meta-title">${escapeHtml(al.title)}</div>
                            <div class="top-meta-sub">${escapeHtml(al.artist || '—')}</div>
                        </div>
                        <div class="top-plays">${al.plays}×</div>
                    </div>
                `).join('')}
            </div>
        `, groupSong('album')));
    }

    /* Top género HERO — usa una canción RANDOM de las top que cumplen
       con ese género como banda sonora. */
    const topGenre = (data.genres || [])[0];
    if (topGenre) {
        /* Shuffle del pool para que cada apertura del wrapped suene
           una canción distinta del mismo género (variedad). */
        const genrePool = shuffle((topGenre.top_songs || []).slice());
        slides.push(makeSlide('topGenre', `
            <p class="slide-subtitle">Tu género del año</p>
            <div class="hero">
                <div class="hero-title" style="font-size:clamp(36px,8vw,96px);">${letterSplit(topGenre.name)}</div>
            </div>
        `, groupSong('genre')));
    }

    /* Top 5 géneros — mantiene la música del slide anterior (HERO del
       género) pero si éste no tuvo pool, suena la canción top del #1. */
    if ((data.genres || []).length > 0) {
        const genre1Pool = shuffle(((data.genres[0] || {}).top_songs || []).slice());
        slides.push(makeSlide('genres', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus géneros top</h1>
            <div class="top-list">
                ${data.genres.map((g, idx) => `
                    <div class="top-item" style="--i:${idx}">
                        <div class="top-rank">${idx+1}</div>
                        <div class="top-meta">
                            <div class="top-meta-title">${escapeHtml(g.name)}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `, groupSong('genre')));
    }

    /* Cierre */
    /* TARJETA RESUMEN — última slide. Estilo Spotify Wrapped:
       foto top artist + listas Top Artists/Top Songs + minutos + género.
       Win98 chrome + botones para compartir / descargar / copiar. */
    const sumTopSongs   = (data.songs   || []).slice(0, 5);
    const sumTopArtists = (data.artists || []).slice(0, 5);
    const sumGenre      = ((data.genres || [])[0] || {}).name || '—';
    const sumTopArtist  = (data.artists || [])[0];
    const heroSrc       = sumTopArtist && sumTopArtist.image_url ? sumTopArtist.image_url : '';
    slides.push(makeSlide('closure', `
        <div class="wrapped-card-wrapper">
            <div class="wrapped-card" id="wrapped-summary-card">
                <div class="wc-hero">
                    ${heroSrc
                        ? `<img class="wc-hero-img" crossorigin="anonymous" src="${escapeHtml(heroSrc)}" alt="" onerror="this.outerHTML='<div class=\\'wc-hero-fallback\\'>🎤</div>'">`
                        : `<div class="wc-hero-fallback">🎤</div>`}
                    <div class="wc-hero-year">${YEAR}</div>
                </div>
                <div class="wc-body">
                    <div class="wc-cols">
                        <div class="wc-col">
                            <h4>Top Artists</h4>
                            <ol>
                                ${sumTopArtists.map((a, i) => `<li><span class="rank">${i+1}</span><span>${escapeHtml(a.artist)}</span></li>`).join('') ||
                                  '<li><span>—</span></li>'}
                            </ol>
                        </div>
                        <div class="wc-col">
                            <h4>Top Songs</h4>
                            <ol>
                                ${sumTopSongs.map((s, i) => `<li><span class="rank">${i+1}</span><span>${escapeHtml(s.title)}</span></li>`).join('') ||
                                  '<li><span>—</span></li>'}
                            </ol>
                        </div>
                    </div>
                    <div class="wc-stats">
                        <div>
                            <div class="wc-stat-label">Minutes Listened</div>
                            <div class="wc-stat-val">${totalMin.toLocaleString('es-ES')}</div>
                        </div>
                        <div>
                            <div class="wc-stat-label">Top Genre</div>
                            <div class="wc-stat-val" style="font-size:18px;">${escapeHtml(sumGenre)}</div>
                        </div>
                    </div>
                    <div class="wc-footer">
                        <span class="wc-logo">♪</span>
                        <span>SCRAPBOOK / WRAPPED ${YEAR}</span>
                    </div>
                </div>
            </div>
            <div class="wc-share-row">
                <button type="button" class="wc-share-btn" id="wc-btn-share">📱 Compartir</button>
                <button type="button" class="wc-share-btn" id="wc-btn-download">💾 Descargar</button>
                <button type="button" class="wc-share-btn" id="wc-btn-copy">📋 Copiar</button>
            </div>
        </div>
    `, groupSong('genre')));

    root.innerHTML = slides.join('');

    /* Progress bar segmentos. */
    const progress = document.getElementById('wrapped-progress');
    progress.innerHTML = slides.map(_ => '<div class="progress-segment"></div>').join('');

    showSlide(0);

    /* Init del player AHORA — DOM ya tiene el slide activo, así
       initWrappedPlayer puede leer su data-song-id y arrancar con la
       canción correcta (la de "opening", no la top song). */
    if (window.YT && window.YT.Player && !ytWrappedPlayer) {
        initWrappedPlayer();
    }
}

let currentSlide = 0;
let autoTimer    = null;

function showSlide(i) {
    const all = document.querySelectorAll('.slide');
    if (i < 0 || i >= all.length) return;
    const prev = document.querySelector('.slide.active');
    const goingForward = i > currentSlide;
    all.forEach(s => s.classList.remove('active', 'exiting', 'entering', 'exit-left', 'exit-right', 'enter-left', 'enter-right'));
    /* Marcar la previa como saliente en la dirección correcta. */
    if (prev && prev !== all[i]) {
        prev.classList.add('exiting');
        prev.classList.add(goingForward ? 'exit-left' : 'exit-right');
        /* Limpiar el flag cuando termine la transición. */
        setTimeout(() => prev.classList.remove('exiting', 'exit-left', 'exit-right'), 600);
    }
    /* La nueva entra desde el lado opuesto y aterriza al centro. */
    all[i].classList.add('entering', goingForward ? 'enter-right' : 'enter-left');
    /* Force reflow para que la clase entering se aplique antes de active. */
    void all[i].offsetWidth;
    requestAnimationFrame(() => {
        all[i].classList.remove('enter-right', 'enter-left');
        all[i].classList.add('active');
        /* Quitar entering tras la transición. */
        setTimeout(() => all[i].classList.remove('entering'), 600);
    });
    currentSlide = i;

    /* Sincronizar música con la slide.
       - data-song-id: una canción concreta (Top Song HERO).
       - data-song-pool: array JSON de candidatos. El handler elige la
         PRIMERA NO USADA todavía en esta sesión del wrapped, para que
         las slides de artist/album suenen distintas aunque su artista
         coincida con el del Top Song. */
    if (!window.__wrappedUsedVids) window.__wrappedUsedVids = new Set();
    const sid    = all[i].getAttribute('data-song-id');
    const sartist= all[i].getAttribute('data-song-artist') || '';
    const poolStr= all[i].getAttribute('data-song-pool');
    if (sid) {
        const stitle = all[i].getAttribute('data-song-title') || '';
        wrappedPlayVideo(sid, stitle, sartist);
        window.__wrappedUsedVids.add(sid);
    } else if (poolStr) {
        try {
            const pool = JSON.parse(poolStr);
            /* Primer candidato NO usado aún. Cada entrada lleva su
               propio artist en `a` — lo usamos para el Now Playing. */
            const chosen = pool.find(p => !window.__wrappedUsedVids.has(p.v)) || pool[0];
            if (chosen && chosen.v) {
                wrappedPlayVideo(chosen.v, chosen.t, chosen.a || sartist);
                window.__wrappedUsedVids.add(chosen.v);
            }
        } catch (_) {}
    }

    /* Disparar count-up en los <element data-countup="N"> de la
       slide recién activada — tras un pequeño delay para sincronizar
       con la entrada del panel. */
    setTimeout(() => {
        all[i].querySelectorAll('[data-countup]').forEach(el => {
            animateCountUp(el, +el.getAttribute('data-countup'), 1800);
        });
        /* Bars de la gráfica de meses — recalculamos el height tras
           layout para que el transition CSS ya tenga 0 → valor real. */
        all[i].querySelectorAll('.month-bar[data-h]').forEach((bar, idx) => {
            const targetH = bar.getAttribute('data-h');
            setTimeout(() => { bar.style.height = targetH + '%'; }, idx * 60);
        });
    }, 300);

    /* Progress segments. */
    const segs = document.querySelectorAll('.progress-segment');
    segs.forEach((seg, idx) => {
        seg.className = 'progress-segment';
        if (idx < i) seg.classList.add('done');
        if (idx === i) seg.classList.add('active');
    });

    /* Auto-advance cada 6s (sincronizado con la animación de progreso). */
    if (autoTimer) clearTimeout(autoTimer);
    if (i < all.length - 1) {
        autoTimer = setTimeout(() => showSlide(i + 1), 6000);
    }
}

document.getElementById('wrapped-next').addEventListener('click', () => {
    const total = document.querySelectorAll('.slide').length;
    if (currentSlide < total - 1) showSlide(currentSlide + 1);
});
document.getElementById('wrapped-prev').addEventListener('click', () => {
    if (currentSlide > 0) showSlide(currentSlide - 1);
});
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight' || e.key === ' ') document.getElementById('wrapped-next').click();
    if (e.key === 'ArrowLeft') document.getElementById('wrapped-prev').click();
    if (e.key === 'Escape')    document.getElementById('wrapped-close').click();
});
document.getElementById('wrapped-close').addEventListener('click', () => {
    /* Si está dentro de un iframe, cierra cerrando la ventana padre.
       Si no, vuelve al desktop. */
    if (window.parent && window.parent !== window) {
        try { window.parent.postMessage({ type: 'wrapped-close' }, '*'); } catch(_) {}
    } else {
        window.location.href = '../desktop-base.php';
    }
});

/* Click anywhere para avanzar. */
document.addEventListener('click', e => {
    if (e.target.closest('.ctrl-btn') || e.target.closest('#wrapped-close')) return;
    document.getElementById('wrapped-next').click();
});

/* ════════════════════════════════════════════════════════════════
   MÚSICA DE FONDO — YouTube iframe API hidden player.
   Reproduce las top songs del usuario como banda sonora del wrapped.
   La cola arranca con la canción nº1; al terminar pasa a la siguiente
   y rota infinitamente. Sincronizada con las slides: al llegar al
   "Top song HERO" el player salta a esa canción si no estaba sonando.
═════════════════════════════════════════════════════════════════ */
let ytWrappedPlayer    = null;
let wrappedQueue       = [];
let wrappedQueueIdx    = 0;
let wrappedMuted       = false;
let wrappedSeekedSet   = new Set();   /* videos ya seeked en esta sesión */
let wrappedNowPlayingMeta = { videoId: null, title: '', artist: '' };

window.__wrappedSongs = []; /* lo rellenamos desde buildSlides */

/* Cargar YT API si no está. */
(function ensureYTApi() {
    if (window.YT && window.YT.Player) { return; }
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);
})();

window.onYouTubeIframeAPIReady = function () {
    /* Si todavía no se han cargado las stats, esperamos. */
    if (!window.__wrappedSongs.length) {
        const wait = setInterval(() => {
            if (window.__wrappedSongs.length) {
                clearInterval(wait);
                initWrappedPlayer();
            }
        }, 200);
        return;
    }
    initWrappedPlayer();
};

function initWrappedPlayer() {
    if (ytWrappedPlayer) return;
    wrappedQueue = window.__wrappedSongs.filter(s => s && s.video_id);
    if (!wrappedQueue.length) return;
    /* Holder div sirve de target; YT lo reemplaza por el iframe. */
    const holder = document.createElement('div');
    holder.id = 'yt-wrapped-iframe';
    document.getElementById('yt-player-holder').appendChild(holder);

    /* Determinar la canción INICIAL: si el slide actualmente activo
       tiene un data-song-id (caso normal: welcome con la canción de
       opening), arrancamos con esa. Si no, top song. Sin esto, el
       reproductor cargaba SIEMPRE la top song aunque el slide 0
       quisiera otra → las 4 primeras slides sonaban a top song. */
    const activeSlide = document.querySelector('.slide.active');
    let initVid    = activeSlide ? activeSlide.getAttribute('data-song-id')     : null;
    let initTitle  = activeSlide ? activeSlide.getAttribute('data-song-title')  : null;
    let initArtist = activeSlide ? activeSlide.getAttribute('data-song-artist') : null;
    if (!initVid) {
        const first = wrappedQueue[0];
        initVid    = first.video_id;
        initTitle  = first.title  || '';
        initArtist = first.artist || '';
    }
    wrappedNowPlayingMeta = { videoId: initVid, title: initTitle || '', artist: initArtist || '' };
    updateNowPlayingUI();
    ytWrappedPlayer = new YT.Player('yt-wrapped-iframe', {
        height: '1', width: '1',
        videoId: initVid,
        playerVars: {
            autoplay:       1,
            controls:       0,
            disablekb:      1,
            modestbranding: 1,
            playsinline:    1,
        },
        events: {
            onReady: (e) => {
                e.target.setVolume(40);
                e.target.playVideo();
            },
            onStateChange: (e) => {
                /* PLAYING: seek al ~30% si no lo hemos hecho ya para este
                   video. Es la mejor aproximación heurística a "una parte
                   más movida" — suele caer cerca del primer chorus en
                   tracks pop, y evita los típicos intros lentos. */
                if (e.data === YT.PlayerState.PLAYING) {
                    const vid = wrappedNowPlayingMeta.videoId;
                    if (vid && !wrappedSeekedSet.has(vid)) {
                        const dur = e.target.getDuration();
                        if (dur > 60) {
                            const target = Math.max(30, dur * 0.30);
                            try { e.target.seekTo(target, true); } catch (_) {}
                        }
                        wrappedSeekedSet.add(vid);
                    }
                }
                /* Al acabar una, siguiente de la cola (loop). */
                if (e.data === YT.PlayerState.ENDED) {
                    wrappedQueueIdx = (wrappedQueueIdx + 1) % wrappedQueue.length;
                    const next = wrappedQueue[wrappedQueueIdx];
                    wrappedNowPlayingMeta = { videoId: next.video_id, title: next.title || '', artist: next.artist || '' };
                    updateNowPlayingUI();
                    e.target.loadVideoById(next.video_id);
                }
            },
        },
    });
}

/** Actualiza la UI del "Now Playing" arriba-izquierda. */
function updateNowPlayingUI() {
    const wrap = document.getElementById('wrapped-now-playing');
    const t = document.getElementById('np-title');
    const a = document.getElementById('np-artist');
    if (!wrap || !t || !a) return;
    if (!wrappedNowPlayingMeta.videoId) {
        wrap.classList.remove('visible');
        return;
    }
    t.textContent = wrappedNowPlayingMeta.title || '—';
    a.textContent = wrappedNowPlayingMeta.artist || '';
    /* Re-trigger animation. */
    wrap.classList.remove('visible');
    void wrap.offsetWidth;
    wrap.classList.add('visible');
}

/** Salta a una canción específica para acompañar la slide actual.
 *  `title` y `artist` se muestran en el Now Playing. Si la canción ya
 *  está sonando, no hace nada. */
function wrappedPlayVideo(videoId, title, artist) {
    if (!videoId) return;
    if (!ytWrappedPlayer || typeof ytWrappedPlayer.loadVideoById !== 'function') {
        /* El player aún no está listo — guarda la pista para cuando lo esté. */
        wrappedNowPlayingMeta = { videoId, title: title || '', artist: artist || '' };
        return;
    }
    if (wrappedNowPlayingMeta.videoId === videoId) return;
    wrappedNowPlayingMeta = { videoId, title: title || '', artist: artist || '' };
    updateNowPlayingUI();
    const idx = wrappedQueue.findIndex(s => s.video_id === videoId);
    if (idx >= 0) wrappedQueueIdx = idx;
    try { ytWrappedPlayer.loadVideoById(videoId); } catch (_) {}
}

/* Botón mute/unmute. */
document.getElementById('wrapped-mute').addEventListener('click', () => {
    if (!ytWrappedPlayer) return;
    wrappedMuted = !wrappedMuted;
    try {
        if (wrappedMuted) ytWrappedPlayer.mute();
        else              ytWrappedPlayer.unMute();
    } catch (_) {}
    document.getElementById('wrapped-mute').textContent = wrappedMuted ? '🔇' : '🔊';
});

/* Pausar al cerrar la pestaña/ventana. */
window.addEventListener('pagehide', () => {
    try { if (ytWrappedPlayer) ytWrappedPlayer.stopVideo(); } catch (_) {}
});

/* ════════════════════════════════════════════════════════════════
   SHARE de la tarjeta-resumen (última slide).
   Usa html2canvas para renderizar el div #wrapped-summary-card a un
   canvas y exporta PNG. Tres botones:
     - Compartir: Web Share API (mobile-first, navegadores compatibles).
     - Descargar: link <a download>.
     - Copiar:    navigator.clipboard.write con ClipboardItem.
═════════════════════════════════════════════════════════════════ */
async function captureWrappedCard() {
    const card = document.getElementById('wrapped-summary-card');
    if (!card || typeof html2canvas !== 'function') return null;
    const canvas = await html2canvas(card, {
        useCORS:      true,    /* permite img.youtube.com / i.scdn.co */
        allowTaint:   false,
        backgroundColor: null,
        scale:        2,       /* render @2x para una imagen nítida */
        logging:      false,
    });
    return new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
}

function wrappedFilename() {
    const year = (new Date()).getFullYear();
    return 'scrapbook-wrapped-' + year + '.png';
}

document.addEventListener('click', async (e) => {
    const t = e.target;
    if (!t || !t.classList) return;

    if (t.id === 'wc-btn-share') {
        const blob = await captureWrappedCard();
        if (!blob) return;
        const file = new File([blob], wrappedFilename(), { type: 'image/png' });
        if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
            try {
                await navigator.share({ files: [file], title: 'Mi Scrapbook Wrapped' });
            } catch (_) {/* user canceled */}
        } else {
            /* Fallback a download si Web Share no disponible. */
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = wrappedFilename();
            a.click();
            URL.revokeObjectURL(url);
        }
    }
    else if (t.id === 'wc-btn-download') {
        const blob = await captureWrappedCard();
        if (!blob) return;
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = wrappedFilename();
        a.click();
        URL.revokeObjectURL(url);
    }
    else if (t.id === 'wc-btn-copy') {
        const blob = await captureWrappedCard();
        if (!blob) return;
        try {
            if (navigator.clipboard && window.ClipboardItem) {
                await navigator.clipboard.write([
                    new ClipboardItem({ 'image/png': blob }),
                ]);
                t.textContent = '✓ Copiado';
                setTimeout(() => { t.textContent = '📋 Copiar'; }, 1800);
            }
        } catch (_) { /* permiso denegado */ }
    }
});
</script>
</body>
</html>
