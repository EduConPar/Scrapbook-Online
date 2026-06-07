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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Bungee&family=Bowlby+One+SC&display=swap');

    html, body {
        margin: 0; padding: 0;
        width: 100%; height: 100%;
        overflow: hidden;
        font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif;
        color: var(--text, #fff);
        background: #000;
        user-select: none;
    }

    /* ── SLIDE LAYER ─────────────────────────────────────────────── */
    .slide {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        opacity: 0;
        pointer-events: none;
        padding: 6vh 4vw;
        box-sizing: border-box;
        transform: scale(0.92);
        transition: opacity 0.55s ease, transform 0.55s cubic-bezier(0.34, 1.4, 0.64, 1);
    }
    .slide.active {
        opacity: 1;
        pointer-events: auto;
        transform: scale(1);
        z-index: 2;
    }
    .slide.exiting {
        opacity: 0;
        z-index: 1;
    }
    /* Transiciones direccionales más interesantes: la salida hace un
       leve giro 3D y zoom-out al lateral; la entrada viene desde el
       lado opuesto también con tilt 3D. Da sensación de "carrusel de
       ventanas Win98". */
    .slide.exiting.exit-left  { transform: translateX(-25%) scale(0.85) rotateY(15deg); }
    .slide.exiting.exit-right { transform: translateX( 25%) scale(0.85) rotateY(-15deg); }
    .slide.entering.enter-left  { transform: translateX(-25%) scale(0.85) rotateY(15deg); }
    .slide.entering.enter-right { transform: translateX( 25%) scale(0.85) rotateY(-15deg); }

    /* DRIFT del gradient base. */
    .slide.active {
        background-size: 250% 250% !important;
        animation: bgDrift 16s ease-in-out infinite alternate;
    }
    @keyframes bgDrift {
        0%   { background-position:   0%   0%; }
        100% { background-position: 100% 100%; }
    }

    /* PINSTRIPE animado: capa de líneas diagonales semi-transparentes
       que se desplazan lentamente en todas las slides. Da textura
       Win98 sin tapar el contenido. */
    .slide::before {
        content: '';
        position: absolute; inset: 0;
        background-image: repeating-linear-gradient(
            45deg,
            rgba(255,255,255,0.06) 0px,
            rgba(255,255,255,0.06) 2px,
            transparent 2px,
            transparent 16px
        );
        pointer-events: none;
        animation: stripeScroll 24s linear infinite;
        z-index: 0;
    }
    @keyframes stripeScroll {
        from { background-position:   0   0; }
        to   { background-position: 256px 256px; }
    }

    /* GRID retro: puntos blancos diminutos en cuadrícula. */
    .slide::after {
        content: '';
        position: absolute; inset: 0;
        background-image: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.15) 1px, transparent 1.5px);
        background-size: 28px 28px;
        pointer-events: none;
        animation: gridFloat 40s linear infinite;
        opacity: 0.5;
        z-index: 0;
    }
    @keyframes gridFloat {
        from { background-position:   0   0; }
        to   { background-position: 28px 56px; }
    }

    /* BLOBS flotantes — efecto "lava lamp" en el fondo. 3 blobs con
       trayectorias y tamaños distintos, blur intenso, mix-blend para
       fundirse con el gradient base. */
    .bg-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(70px);
        opacity: 0.45;
    }
    .bg-blob-1 {
        width: 55vw; height: 55vw;
        background: var(--accent, #1db954);
        top: -15vw; left: -15vw;
        animation: blobMove1 28s ease-in-out infinite;
    }
    .bg-blob-2 {
        width: 40vw; height: 40vw;
        background: rgba(255,255,255,0.6);
        bottom: -15vw; right: -15vw;
        animation: blobMove2 22s ease-in-out infinite;
    }
    .bg-blob-3 {
        width: 35vw; height: 35vw;
        background: var(--accent, #1db954);
        top: 25vh; right: 20vw;
        animation: blobMove3 19s ease-in-out infinite alternate;
    }
    @keyframes blobMove1 {
        0%   { transform: translate(0, 0) scale(1); }
        33%  { transform: translate(30vw, 25vh) scale(1.3); }
        66%  { transform: translate(15vw, 50vh) scale(0.85); }
        100% { transform: translate(0, 0) scale(1); }
    }
    @keyframes blobMove2 {
        0%   { transform: translate(0, 0) scale(1); }
        50%  { transform: translate(-25vw, -25vh) scale(1.4); }
        100% { transform: translate(0, 0) scale(1); }
    }
    @keyframes blobMove3 {
        0%   { transform: translate(0, 0) scale(0.7); }
        100% { transform: translate(-40vw, -25vh) scale(1.5); }
    }

    /* ICONOS Win98 flotantes — caracteres unicode que suben lentamente
       desde abajo, con delays escalonados. */
    .bg-icons {
        position: absolute; inset: 0;
        pointer-events: none;
        z-index: 0;
        overflow: hidden;
    }
    .bg-icons span {
        position: absolute;
        bottom: -50px;
        font-size: clamp(20px, 3vw, 36px);
        color: rgba(255,255,255,0.35);
        text-shadow: 1px 1px 0 rgba(0,0,0,0.25);
        animation: iconFloat 18s linear infinite;
        animation-delay: calc(var(--bg-i, 0) * -2.2s);
        left: calc(var(--bg-i, 0) * 12.5% + 2vw);
    }
    @keyframes iconFloat {
        0%   { transform: translateY(0) rotate(0deg); opacity: 0; }
        10%  { opacity: 0.5; }
        50%  { transform: translateY(-50vh) rotate(180deg); opacity: 0.35; }
        90%  { opacity: 0.2; }
        100% { transform: translateY(-110vh) rotate(360deg); opacity: 0; }
    }

    /* === TRANSITION VARIANTS — random por cada slide change === */
    /* tx-slide: el default (slide + rotate Y leve), ya está. */

    /* tx-flip: voltea la slide saliente en 3D y la entrante desde
       el lado opuesto. */
    .slide.tx-flip.exiting.exit-left  { transform: perspective(1200px) rotateY(-80deg) translateX(-15%); transform-origin: left center; }
    .slide.tx-flip.exiting.exit-right { transform: perspective(1200px) rotateY( 80deg) translateX( 15%); transform-origin: right center; }
    .slide.tx-flip.entering.enter-left  { transform: perspective(1200px) rotateY( 80deg) translateX(-15%); transform-origin: left center; }
    .slide.tx-flip.entering.enter-right { transform: perspective(1200px) rotateY(-80deg) translateX( 15%); transform-origin: right center; }

    /* tx-zoom: la saliente "explota" hacia fuera, la entrante viene
       desde un punto pequeño. */
    .slide.tx-zoom.exiting  { transform: scale(2.4); opacity: 0; }
    .slide.tx-zoom.entering { transform: scale(0.4); opacity: 0; }

    /* tx-iris: clip-path circular que se cierra/abre desde el centro. */
    .slide.tx-iris.exiting {
        clip-path: circle(0% at 50% 50%);
        transition: clip-path 0.55s cubic-bezier(0.4, 0, 0.6, 1), opacity 0.5s;
    }
    .slide.tx-iris.entering {
        clip-path: circle(0% at 50% 50%);
    }
    .slide.tx-iris.active {
        clip-path: circle(150% at 50% 50%);
        transition: clip-path 0.55s cubic-bezier(0.4, 0, 0.6, 1) 0.05s, opacity 0.5s, transform 0.55s cubic-bezier(0.34, 1.4, 0.64, 1);
    }

    /* ═══════════════════════════════════════════════════════════════
       TRANSICIONES SUTILES — mecánicas evocativas de la era 90s con
       movimientos contenidos. Las exageraciones (rotaciones 900°,
       translates 110vw, blur 20px) están fuera. Cada transición se
       SUGIERE más que se grita.
       ═══════════════════════════════════════════════════════════════ */

    /* tx-vinyl: media vuelta con leve blur — sugiere el spin. */
    .slide.tx-vinyl.exiting {
        animation: vinylOut 0.5s cubic-bezier(0.5, 0, 0.7, 1) forwards;
    }
    @keyframes vinylOut {
        0%   { transform: rotate(0) scale(1); opacity: 1; filter: none; }
        100% { transform: rotate(-180deg) scale(0.85); opacity: 0; filter: blur(2px); }
    }
    .slide.tx-vinyl.entering,
    .slide.tx-vinyl.active {
        animation: vinylIn 0.6s cubic-bezier(0.34, 1.2, 0.64, 1) forwards;
    }
    @keyframes vinylIn {
        0%   { transform: rotate(180deg) scale(0.85); opacity: 0; filter: blur(2px); }
        100% { transform: rotate(0) scale(1); opacity: 1; filter: none; }
    }

    /* tx-crt-on: scaleY rápido y un toque de brillo. */
    .slide.tx-crt-on.exiting {
        animation: crtPowerOff 0.4s cubic-bezier(0.6, 0, 0.4, 1) forwards;
    }
    @keyframes crtPowerOff {
        0%   { transform: scale(1); filter: brightness(1); opacity: 1; }
        70%  { transform: scaleY(0.05) scaleX(1.05); filter: brightness(1.8); }
        100% { transform: scaleY(0.05) scaleX(0.9); filter: brightness(2); opacity: 0; }
    }
    .slide.tx-crt-on.entering,
    .slide.tx-crt-on.active {
        animation: crtPowerOn 0.55s cubic-bezier(0.34, 1.2, 0.64, 1) forwards;
    }
    @keyframes crtPowerOn {
        0%   { transform: scaleY(0.05) scaleX(0.9); filter: brightness(2); opacity: 0; }
        25%  { transform: scaleY(0.05) scaleX(1.05); filter: brightness(1.8); opacity: 1; }
        100% { transform: scale(1); filter: none; opacity: 1; }
    }

    /* tx-vhs-tracking: skew sutil con flicker breve. */
    .slide.tx-vhs-tracking.entering,
    .slide.tx-vhs-tracking.active {
        animation: vhsTrackingIn 0.55s steps(10) forwards;
    }
    @keyframes vhsTrackingIn {
        0%   { transform: skewY(2deg) translateY(-8px); filter: brightness(1.2) contrast(1.2); opacity: 0; }
        20%  { transform: skewY(-1.5deg) translateY(6px); filter: brightness(1.1) contrast(1.15); opacity: 0.8; }
        40%  { transform: skewY(1deg) translateY(-3px); filter: brightness(1.05); }
        60%  { transform: skewY(-0.5deg) translateY(1px); }
        100% { transform: none; filter: none; opacity: 1; }
    }
    .slide.tx-vhs-tracking.exiting {
        animation: vhsTrackingOut 0.35s steps(7) forwards;
    }
    @keyframes vhsTrackingOut {
        0%   { transform: none; filter: none; opacity: 1; }
        40%  { transform: skewY(-1deg) translateY(4px); filter: brightness(1.1); opacity: 0.7; }
        100% { transform: skewY(2.5deg) translateY(10px); filter: brightness(1.4); opacity: 0; }
    }

    /* tx-floppy: vertical leve con tilt 3D mínimo. */
    .slide.tx-floppy.exiting {
        animation: floppyEject 0.45s cubic-bezier(0.5, 0, 0.85, 0.4) forwards;
    }
    @keyframes floppyEject {
        0%   { transform: translateY(0) rotateX(0); opacity: 1; }
        100% { transform: translateY(-12vh) rotateX(-10deg); opacity: 0; }
    }
    .slide.tx-floppy.entering,
    .slide.tx-floppy.active {
        animation: floppyInsert 0.55s cubic-bezier(0.34, 1.3, 0.6, 1) forwards;
    }
    @keyframes floppyInsert {
        0%   { transform: translateY(12vh) rotateX(10deg); opacity: 0; }
        70%  { transform: translateY(-1vh) rotateX(-2deg); opacity: 1; }
        100% { transform: translateY(0) rotateX(0); opacity: 1; }
    }

    /* tx-defrag: clip-path con bloques menos extremos + skew sutil. */
    .slide.tx-defrag.entering,
    .slide.tx-defrag.active {
        animation: defragIn 0.6s steps(5) forwards;
    }
    @keyframes defragIn {
        0%   { clip-path: polygon(0 0, 60% 0, 55% 100%, 0 100%); transform: skewX(2deg); opacity: 0; }
        30%  { clip-path: polygon(40% 0, 100% 0, 95% 100%, 45% 100%); transform: skewX(-1.5deg); opacity: 0.7; }
        60%  { clip-path: polygon(0 20%, 100% 15%, 100% 85%, 0 90%); transform: skewX(0.5deg); opacity: 0.9; }
        100% { clip-path: inset(0); transform: none; opacity: 1; }
    }
    .slide.tx-defrag.exiting {
        animation: defragOut 0.4s steps(4) forwards;
    }
    @keyframes defragOut {
        0%   { clip-path: inset(0); transform: none; opacity: 1; }
        50%  { clip-path: polygon(10% 5%, 90% 0%, 95% 95%, 5% 100%); transform: skewX(-1deg); opacity: 0.7; }
        100% { clip-path: polygon(0 20%, 50% 15%, 45% 80%, 0 85%); transform: skewX(2deg) translateX(-6vw); opacity: 0; }
    }

    /* tx-page-tear: rasgado suave por el centro. */
    .slide.tx-page-tear.exiting {
        animation: pageTearOut 0.5s cubic-bezier(0.6, 0, 0.85, 0.3) forwards;
    }
    @keyframes pageTearOut {
        0%   { clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%); transform: none; opacity: 1; }
        100% { clip-path: polygon(0 0, 40% 0, 35% 50%, 45% 100%, 0 100%); transform: translateX(-12vw) rotate(-3deg); opacity: 0; }
    }
    .slide.tx-page-tear.entering,
    .slide.tx-page-tear.active {
        animation: pageTearIn 0.6s cubic-bezier(0.34, 1.2, 0.64, 1) forwards;
    }
    @keyframes pageTearIn {
        0%   { clip-path: polygon(60% 0, 100% 0, 100% 100%, 55% 100%, 65% 50%); transform: translateX(12vw) rotate(3deg); opacity: 0; }
        100% { clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%); transform: none; opacity: 1; }
    }

    /* tx-cascade: deriva diagonal mínima — sugiere apilar ventanas. */
    .slide.tx-cascade.exiting {
        animation: cascadeOut 0.4s cubic-bezier(0.6, 0, 0.85, 0.3) forwards;
    }
    @keyframes cascadeOut {
        0%   { transform: none; opacity: 1; }
        100% { transform: translate(6vw, 4vh) scale(0.85); opacity: 0; }
    }
    .slide.tx-cascade.entering,
    .slide.tx-cascade.active {
        animation: cascadeIn 0.5s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
    }
    @keyframes cascadeIn {
        0%   { transform: translate(-6vw, -4vh) scale(0.85); opacity: 0; }
        100% { transform: none; opacity: 1; }
    }

    /* tx-rewind: leve compresión horizontal con blur mínimo. */
    .slide.tx-rewind.exiting {
        animation: rewindOut 0.4s steps(5) forwards;
    }
    @keyframes rewindOut {
        0%   { transform: none; filter: none; opacity: 1; }
        100% { transform: scaleX(0.5) translateX(15vw); filter: blur(3px); opacity: 0; }
    }
    .slide.tx-rewind.entering,
    .slide.tx-rewind.active {
        animation: rewindIn 0.5s cubic-bezier(0.34, 1.2, 0.5, 1) forwards;
    }
    @keyframes rewindIn {
        0%   { transform: scaleX(0.5) translateX(-15vw); filter: blur(3px); opacity: 0; }
        100% { transform: none; filter: none; opacity: 1; }
    }

    /* ═══════════════════════════════════════════════════════════════
       tx-FINAL-GLITCH — SOLO para la transición a la última slide.
       Combina chromatic aberration EXAGERADA (RGB split rojo/cyan vía
       drop-shadow), saltos VHS de gran amplitud, hue-rotate intenso,
       motion blur y flash. Es el "drop" del wrapped — efecto memorable.
       ═══════════════════════════════════════════════════════════════ */
    .slide.tx-final-glitch.exiting {
        animation: finalGlitchOut 0.9s steps(14) forwards;
    }
    @keyframes finalGlitchOut {
        0%   { transform: none; filter: none; opacity: 1; }
        10%  { transform: translate(-25px, 3px); filter: hue-rotate(60deg) saturate(2)
                drop-shadow(-12px 0 0 #ff0080) drop-shadow(12px 0 0 #00ffff); }
        20%  { transform: translate(25px, -5px); filter: hue-rotate(-120deg) saturate(3)
                drop-shadow(18px 0 0 #00ffff) drop-shadow(-18px 0 0 #ff0080); }
        30%  { transform: translate(-15px, 4px); filter: hue-rotate(180deg)
                drop-shadow(-22px 0 0 #ff0080) drop-shadow(22px 0 0 #00ffff); }
        45%  { transform: translate(10px, 0); filter: hue-rotate(-90deg)
                drop-shadow(10px 0 0 #00ffff) drop-shadow(-10px 0 0 #ff0080); }
        60%  { transform: scale(1.1); filter: blur(4px) hue-rotate(45deg)
                drop-shadow(6px 0 0 #ff0080); opacity: 0.6; }
        80%  { transform: scale(1.4); filter: blur(18px) brightness(2); opacity: 0.25; }
        100% { transform: scale(1.9); filter: blur(50px); opacity: 0; }
    }
    .slide.tx-final-glitch.entering,
    .slide.tx-final-glitch.active {
        animation: finalGlitchIn 1.6s steps(24) forwards;
    }
    @keyframes finalGlitchIn {
        0%   {
            transform: scale(0.4) translate(0);
            filter: hue-rotate(0) saturate(1) blur(20px);
            opacity: 0;
        }
        4%   {
            transform: translate(-60px, 15px) scale(0.55);
            filter: hue-rotate(240deg) saturate(5) blur(6px)
                drop-shadow(25px 0 0 #ff0080) drop-shadow(-25px 0 0 #00ffff);
            opacity: 0.35;
        }
        8%   {
            transform: translate(45px, -12px) scale(1.18);
            filter: hue-rotate(-180deg) saturate(6) blur(2px)
                drop-shadow(-30px 0 0 #ff0080) drop-shadow(30px 0 0 #00ffff);
            opacity: 0.65;
        }
        12%  {
            transform: translate(-30px, 6px) scale(0.9);
            filter: hue-rotate(150deg) saturate(4)
                drop-shadow(20px 0 0 #ff0080) drop-shadow(-20px 0 0 #00ffff);
            opacity: 0.8;
        }
        16%  {
            transform: translate(22px, -4px) scale(1.06);
            filter: hue-rotate(-90deg) saturate(3)
                drop-shadow(-15px 0 0 #ff0080) drop-shadow(15px 0 0 #00ffff);
        }
        20%  {
            transform: translate(-12px, 3px);
            filter: hue-rotate(60deg) saturate(2)
                drop-shadow(10px 0 0 #ff0080) drop-shadow(-10px 0 0 #00ffff);
        }
        25%  {
            transform: translate(8px, -1px);
            filter: hue-rotate(-45deg)
                drop-shadow(-7px 0 0 #ff0080) drop-shadow(7px 0 0 #00ffff);
        }
        30%  {
            transform: translate(-4px, 1px);
            filter: drop-shadow(-4px 0 0 #ff0080) drop-shadow(4px 0 0 #00ffff);
        }
        40%  {
            transform: translate(0);
            filter: drop-shadow(-2px 0 0 #ff0080) drop-shadow(2px 0 0 #00ffff);
        }
        60%  {
            transform: none;
            filter: drop-shadow(-1px 0 0 #ff0080) drop-shadow(1px 0 0 #00ffff);
        }
        80%  {
            transform: none;
            filter: none;
        }
        100% {
            transform: none;
            filter: none;
            opacity: 1;
        }
    }

    /* tx-flash: pantalla blanca brevísima al cambiar. Overlay global. */
    #tx-flash-overlay {
        position: fixed; inset: 0;
        background: var(--accent, #fff);
        opacity: 0;
        pointer-events: none;
        z-index: 9999;
        mix-blend-mode: screen;
    }
    #tx-flash-overlay.fire {
        animation: flashFire 0.5s ease;
    }
    @keyframes flashFire {
        0%   { opacity: 0; }
        30%  { opacity: 0.6; }
        100% { opacity: 0; }
    }
    /* Flash múltiple e intenso para el tx-final-glitch — pulsa varias
       veces durante la entrada al closure. */
    #tx-flash-overlay.fire-final {
        animation: flashFireFinal 1.4s ease;
    }
    @keyframes flashFireFinal {
        0%   { opacity: 0; background: #fff; }
        5%   { opacity: 0.95; }
        12%  { opacity: 0; }
        18%  { opacity: 0.7; background: #ff0080; }
        25%  { opacity: 0; }
        32%  { opacity: 0.6; background: #00ffff; }
        40%  { opacity: 0; }
        50%  { opacity: 0.3; background: #fff; }
        100% { opacity: 0; }
    }

    /* ── PANEL Win98 (con title bar tipo wrapped.exe) ──────────────── */
    .slide-panel {
        position: relative;
        z-index: 1;
        background: var(--win-bg, silver);
        color: var(--text, #000);
        padding: 3px;
        max-width: min(720px, 92vw);
        max-height: 86vh;
        display: flex; flex-direction: column;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            6px 6px 0 rgba(0,0,0,0.45),
            0 0 60px rgba(0,0,0,0.35);
        opacity: 0;
        transform: translateY(40px) scale(0.92);
    }
    .slide.active .slide-panel {
        animation: panelIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    @keyframes panelIn {
        from { opacity: 0; transform: translateY(40px) scale(0.88); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Title bar del panel — idéntico al wc-titlebar de la summary card */
    .slide-panel-titlebar {
        background: linear-gradient(90deg,
            var(--titlebar-start, #000080) 0%,
            var(--titlebar-end,   #1084d0) 100%);
        color: var(--titlebar-text, #fff);
        padding: 3px 4px 3px 6px;
        font-size: 12px;
        font-weight: bold;
        font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        flex-shrink: 0;
    }
    .slide-panel-title-text {
        display: flex; align-items: center; gap: 6px;
    }
    .slide-panel-icon {
        display: inline-block; width: 14px; height: 14px;
        background: var(--titlebar-icon-bg, var(--accent, #1db954));
        color: var(--titlebar-icon-color, #fff);
        font-size: 10px;
        text-align: center; line-height: 14px;
        box-shadow:
            inset -1px -1px var(--titlebar-icon-bezel-dark, rgba(0,0,0,0.5)),
            inset  1px  1px var(--titlebar-icon-bezel-light, rgba(255,255,255,0.5));
    }
    .slide-panel-ctrls {
        display: flex; gap: 2px;
    }
    .slide-panel-ctrl {
        display: inline-block;
        width: 16px; height: 14px;
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        font-size: 10px;
        text-align: center; line-height: 12px;
        font-weight: bold;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }

    /* Cuerpo del panel — donde va el contenido real de la slide. */
    .slide-panel-body {
        padding: 28px 24px;
        flex: 1; min-height: 0;
        overflow-y: auto;
        display: flex; flex-direction: column;
        align-items: center;
        gap: 14px;
    }

    /* Hijos del body: animación staggered. */
    .slide.active .slide-panel-body > * {
        opacity: 0;
        animation: childUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        animation-delay: calc(0.35s + var(--i, 0) * 0.08s);
    }
    @keyframes childUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Status bar al fondo del panel — un pixel de bezel con texto
       de estado tipo "Ready" o el progress de la slide actual. */
    .slide-panel-statusbar {
        padding: 3px 8px;
        font-size: 10px;
        color: var(--text-muted, var(--text, #444));
        background: var(--win-bg, silver);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff);
    }
    .slide-panel-statusbar span:first-child::before {
        content: '●';
        color: var(--accent, #1db954);
        margin-right: 4px;
        animation: statusBlink 1.6s ease-in-out infinite;
    }
    @keyframes statusBlink {
        0%, 100% { opacity: 0.4; }
        50%      { opacity: 1; }
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

    /* Slide title — ahora estilo "header chip" Win98 con bg accent +
       texto blanco y bezel (replica los headers de la summary card). */
    .slide-title {
        font-size: clamp(20px, 3.6vw, 38px);
        font-weight: bold;
        margin: 0 0 8px;
        text-align: center;
        line-height: 1.1;
        color: var(--accent-text, #fff);
        background: var(--accent, #1db954);
        padding: 6px 18px;
        letter-spacing: 0.5px;
        box-shadow:
            inset -1px -1px rgba(0,0,0,0.3),
            inset  1px  1px rgba(255,255,255,0.4);
    }
    .slide-subtitle {
        font-size: clamp(13px, 1.6vw, 18px);
        font-weight: normal;
        margin: 0;
        text-align: center;
        color: var(--text-muted, var(--text, #444));
    }
    /* Big number en panel sunken Win98 — efecto LCD/display digital. */
    .big-number {
        font-size: clamp(80px, 14vw, 180px);
        font-weight: bold;
        line-height: 1;
        color: var(--accent, var(--text, #000));
        text-align: center;
        background: var(--input-bg, #fff);
        padding: 12px 28px 8px;
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff),
            inset  2px  2px var(--bezel-dark-1, #0a0a0a),
            inset -2px -2px var(--bezel-light-2, #dfdfdf);
        font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', monospace;
        letter-spacing: 2px;
        max-width: min(560px, 88vw);
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

    /* Hero card para "top 1" — ahora envuelto en marco Win98 con
       pinstripes encima y debajo, igual que la summary card. */
    .hero {
        display: flex; flex-direction: column;
        align-items: center;
        gap: 14px;
        position: relative;
    }
    /* Pinstripe arriba/abajo del cover (decorativo). */
    .hero::before, .hero::after {
        content: '';
        width: clamp(140px, 26vw, 240px);
        height: 6px;
        background: repeating-linear-gradient(
            45deg,
            var(--accent, #1db954) 0px,
            var(--accent, #1db954) 4px,
            var(--text, #000) 4px,
            var(--text, #000) 8px
        );
    }
    .hero-cover {
        width: clamp(120px, 24vw, 220px);
        height: clamp(120px, 24vw, 220px);
        object-fit: cover;
        background: var(--inset-bg, var(--input-bg, #fff));
        /* OUTSET bezel — mismo que la profile-avatar-frame. */
        box-shadow:
            -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
             1px  1px 0 var(--bezel-light-1, #fff),
            -2px -2px 0 var(--bezel-dark-2, grey),
             2px  2px 0 var(--bezel-light-2, #dfdfdf),
             6px 6px 0 rgba(0,0,0,0.4);
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
            -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
             1px  1px 0 var(--bezel-light-1, #fff),
            -2px -2px 0 var(--bezel-dark-2, grey),
             2px  2px 0 var(--bezel-light-2, #dfdfdf),
             6px 6px 0 rgba(0,0,0,0.4);
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

    /* Zonas tap invisibles que ocupan media ventana cada una. Cubren la
       altura útil bajo el progress bar (6px). z-index 50: por debajo del
       now-playing / glitch (100+) pero encima del slide content (1-10),
       de modo que un tap en cualquier sitio del slide avanza o retrocede.
       Los enlaces/botones interactivos dentro del slide deben tener
       z-index > 50 y `pointer-events:auto` para seguir funcionando. */
    .wrapped-nav-zone {
        position: fixed;
        top: 6px;            /* deja libre la progress bar */
        bottom: 0;
        width: 50vw;
        z-index: 50;
        cursor: pointer;
        background: transparent;
    }
    #wrapped-nav-left  { left: 0;  }
    #wrapped-nav-right { right: 0; }

    /* Progress segments — barras de mini-Win98. Ahora ocupan todo el
       ancho porque el botón close se ha movido al header de la window
       padre. */
    #wrapped-progress {
        position: fixed;
        top: 12px; left: 12px; right: 12px;
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
        animation: fillProgress 9s linear forwards;
    }
    @keyframes fillProgress { to { width: 100%; } }

    /* ── TARJETA DE RESUMEN (última slide) ─────────────────────────
       Layout estilo Win98 dialog: title bar + body con foto del top
       artist enmarcada en bezel + tira decorativa de checker + dos
       columnas (top artists / top songs) + stats grandes + footer.
       Exportable a PNG vía html2canvas. */
    .wrapped-card-wrapper {
        display: flex; flex-direction: column;
        align-items: center;
        gap: 14px;
        /* z-index alto + position: relative para que la card resumen
           NUNCA quede debajo de los blobs/iconos animados del fondo
           (que están en z-index: 0). */
        position: relative;
        z-index: 10;
    }
    .wrapped-card {
        background: var(--win-bg, silver);
        color: var(--text, #000);
        /* Logical width fijo a 380 (un poco más pequeño que antes para
           que NO se salga de la ventana al escalar). Todos los offsets
           internos siguen calibrados a este tamaño base. El crecimiento
           real lo hace transform:scale() controlado por --wc-scale que
           actualiza el JS en cada resize. */
        width: 380px;
        padding: 3px;
        transform: scale(var(--wc-scale, 1));
        transform-origin: top center;
        /* Reserva el alto extra que ocupa el card escalado en el flujo
           del flex container (transform no afecta layout). Card natural
           height ≈ 720px; el card escalado ocupa 720 * scale. */
        margin-bottom: calc(720px * (var(--wc-scale, 1) - 1));
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf),
            6px 6px 0 rgba(0,0,0,0.45);
    }

    /* ── Title bar Win98 ─────────────────────────────────────────── */
    .wc-titlebar {
        background: linear-gradient(90deg,
            var(--titlebar-start, #000080) 0%,
            var(--titlebar-end,   #1084d0) 100%);
        color: var(--titlebar-text, #fff);
        padding: 3px 4px 3px 6px;
        font-size: 12px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }
    .wc-titlebar-text {
        display: flex; align-items: center; gap: 6px;
    }
    .wc-titlebar-text .wc-titlebar-icon {
        display: inline-block; width: 14px; height: 14px;
        background: var(--titlebar-icon-bg, var(--accent, #1db954));
        color: var(--titlebar-icon-color, #fff);
        font-size: 10px;
        text-align: center; line-height: 14px;
        box-shadow:
            inset -1px -1px var(--titlebar-icon-bezel-dark, rgba(0,0,0,0.5)),
            inset  1px  1px var(--titlebar-icon-bezel-light, rgba(255,255,255,0.5));
    }
    .wc-titlebar-controls {
        display: flex; gap: 2px;
    }
    .wc-titlebar-ctrl {
        display: inline-block;
        width: 16px; height: 14px;
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        font-size: 10px;
        text-align: center; line-height: 12px;
        font-weight: bold;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }

    /* ── Hero photo ──────────────────────────────────────────────── */
    /* Marco del mismo estilo que la foto de perfil del usuario:
       OUTSET 4-layer bezel → la foto se ve "metida" dentro del bezel. */
    .wc-hero-frame {
        margin: 14px 10px 10px;
        position: relative;
        background: var(--inset-bg, var(--input-bg, #fff));
        box-shadow:
            -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
             1px  1px 0 var(--bezel-light-1, #fff),
            -2px -2px 0 var(--bezel-dark-2, grey),
             2px  2px 0 var(--bezel-light-2, #dfdfdf);
        overflow: visible;
    }
    /* Tira decorativa pinstripe — ahora como <div> reales (no pseudo)
       para que html2canvas las capture sin problemas. */
    .wc-hero-stripe {
        position: absolute;
        left: -2px; right: -2px;
        height: 6px;
        background: repeating-linear-gradient(
            45deg,
            var(--accent, #1db954) 0px,
            var(--accent, #1db954) 4px,
            var(--text, #000) 4px,
            var(--text, #000) 8px
        );
        z-index: 4;
        pointer-events: none;
    }
    .wc-hero-stripe-top    { top: -8px; }
    .wc-hero-stripe-bottom { bottom: -8px; }

    /* Marco cuadrado 1:1 — usamos padding-bottom: 100% para forzar
       aspect-ratio sin depender de aspect-ratio CSS (más fiable en
       html2canvas v1.4.1). El hijo va en absolute :inset 0. */
    .wc-hero {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 100%;
        overflow: hidden;
        background: var(--inset-bg, #fff);
    }
    .wc-hero-img,
    .wc-hero-fallback {
        position: absolute;
        inset: 0;
        width: 100%; height: 100%;
        display: block;
    }
    .wc-hero-img {
        object-fit: cover;
    }
    .wc-hero-fallback {
        display: flex; align-items: center; justify-content: center;
        font-size: 80px;
        background: var(--accent, #1db954);
        color: var(--accent-text, #fff);
    }

    /* "2026" como BADGE plano en esquina superior izquierda — sin
       rotaciones raras. Cuadrado con bezel raised, sombras y un acento.
       Reemplaza el rotate(-90deg) buggy que mostraba digits stacked. */
    /* Badge del año — estilo title bar Win98 (gradient del tema +
       texto blanco con shadow sutil) + 4-layer raised bezel auténtico.
       Tipografía Inter Black (chunky pero MÁS LEGIBLE que Bungee
       cuando va con outline). */
    .wc-hero-badge {
        position: absolute;
        top: 10px; left: 10px;
        z-index: 3;
        background: linear-gradient(180deg,
            var(--titlebar-start, #000080) 0%,
            var(--titlebar-end,   #1084d0) 100%);
        color: var(--titlebar-text, #fff);
        font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif;
        font-size: 22px;
        font-weight: bold;
        padding: 4px 12px 3px;
        letter-spacing: 3px;
        line-height: 1;
        transform: rotate(-3deg);
        transform-origin: top left;
        text-shadow: 1px 1px 0 rgba(0,0,0,0.55);
        /* 4-layer raised bezel Win98 + drop shadow */
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px 0 var(--bezel-light-1, #fff),
            inset -2px -2px 0 var(--bezel-dark-2, grey),
            inset  2px  2px 0 var(--bezel-light-2, #dfdfdf),
            3px 3px 0 var(--bezel-dark-1, #0a0a0a);
    }
    /* Highlight stripe del badge — ahora <div> real (no ::before). */
    .wc-badge-highlight {
        position: absolute;
        top: 3px; left: 6px; right: 6px;
        height: 2px;
        background: repeating-linear-gradient(
            90deg,
            rgba(255,255,255,0.7) 0px,
            rgba(255,255,255,0.7) 3px,
            transparent 3px,
            transparent 6px
        );
        pointer-events: none;
    }
    /* Estrella decorativa — pequeña y dorada estilo "premio Win98". */
    .wc-badge-star {
        position: absolute;
        top: -8px; right: -10px;
        font-size: 18px;
        line-height: 1;
        color: var(--accent, #1db954);
        text-shadow:
            -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
             1px -1px 0 var(--bezel-dark-1, #0a0a0a),
            -1px  1px 0 var(--bezel-dark-1, #0a0a0a),
             1px  1px 0 var(--bezel-dark-1, #0a0a0a);
        transform: rotate(15deg);
        pointer-events: none;
        z-index: 10;
    }

    /* ── Body / contenido ────────────────────────────────────────── */
    .wc-body {
        padding: 14px 14px 12px;
    }
    /* Flexbox en vez de grid — html2canvas v1.4.1 tiene bugs con
       grid-template-columns que producen colores/dimensiones distintas
       en cada columna al capturar. Flex es 100% fiable. */
    .wc-cols {
        display: flex;
        gap: 14px;
        margin-bottom: 10px;
    }
    .wc-col {
        flex: 1 1 0;
        min-width: 0;
    }
    .wc-col h4 {
        font-size: 10px;
        font-weight: bold;
        margin: 0 0 6px;
        padding: 2px 4px;
        background: var(--accent, #1db954);
        color: var(--accent-text, #fff);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow:
            inset -1px -1px rgba(0,0,0,0.3),
            inset  1px  1px rgba(255,255,255,0.4);
    }
    .wc-col ol {
        margin: 0; padding: 0;
        list-style: none;
        font-size: 12px;
        line-height: 1.3;
    }
    .wc-col ol li {
        display: flex;
        align-items: flex-start;
        gap: 5px;
        padding: 2px 3px;
        margin-bottom: 1px;
        overflow: hidden;
        word-break: break-word;
    }
    .wc-col ol li:nth-child(odd) {
        background: rgba(0,0,0,0.06);
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
        text-align: right;
    }

    /* ── Etched separator (Win98 groove de 2px) ──────────────────── */
    .wc-sep {
        height: 2px;
        margin: 10px 0;
        border-top: 1px solid var(--bezel-dark-2, grey);
        border-bottom: 1px solid var(--bezel-light-1, #fff);
    }

    /* ── Stats ──────────────────────────────────────────────────── */
    /* Flex en vez de grid — mismo motivo que .wc-cols. */
    .wc-stats {
        display: flex;
        gap: 12px;
    }
    .wc-stat {
        flex: 1 1 0;
        min-width: 0;
        padding: 6px 8px;
        background: var(--input-bg, #fff);
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff),
            inset  2px  2px var(--bezel-dark-1, #0a0a0a),
            inset -2px -2px var(--bezel-light-2, #dfdfdf);
    }
    .wc-stat-label {
        font-size: 9px;
        color: var(--text-muted, var(--text, #555));
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
        font-weight: bold;
    }
    .wc-stat-val {
        font-size: 24px;
        font-weight: bold;
        color: var(--accent, var(--text, #000));
        line-height: 1;
        text-shadow: 1px 1px 0 rgba(0,0,0,0.12);
    }
    .wc-stat-val.smaller { font-size: 18px; }

    /* ── Footer ─────────────────────────────────────────────────── */
    .wc-footer {
        margin-top: 10px;
        padding: 5px 6px;
        background: linear-gradient(90deg,
            var(--titlebar-start, #000080) 0%,
            var(--titlebar-end,   #1084d0) 100%);
        color: var(--titlebar-text, #fff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .wc-footer .wc-logo {
        font-size: 13px;
        color: var(--titlebar-icon-bg, var(--accent, #1db954));
        margin-right: 4px;
    }
    .wc-footer .wc-footer-right {
        opacity: 0.85;
        font-size: 8px;
    }

    /* ── Share buttons (FUERA de la card capturada) ─────────────── */
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
        padding: 5px 16px;
        min-height: 30px;
        border: 0;
        border-radius: 0;
        cursor: pointer;
        font-weight: bold;
        position: relative;
        z-index: 60;  /* > .wrapped-nav-zone (50) para que reciba el click. */
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
        position: relative;
    }
    .month-bar {
        width: 100%;
        background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.55));
        border-radius: 6px 6px 0 0;
        min-height: 2px;
        position: relative;
        transition: height 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        z-index: 2;
    }
    .month-bar.top-month {
        background: linear-gradient(180deg, #fff, #ffd166);
        box-shadow: 0 0 30px rgba(255,209,102,0.6);
    }
    .month-bar.current-month {
        background: linear-gradient(180deg, var(--accent, #1db954), var(--accent-deep, var(--accent, #1db954)));
        box-shadow: 0 0 20px var(--accent, #1db954);
    }
    /* Proyección: barra "fantasma" detrás de la real, con borde
       discontinuo y patrón rayado diagonal. Sólo visible para el
       MES EN CURSO si la proyección > actual. */
    .month-bar-proj {
        position: absolute;
        bottom: 0;
        width: 100%;
        background:
            repeating-linear-gradient(
                45deg,
                rgba(255,255,255,0.2) 0px,
                rgba(255,255,255,0.2) 3px,
                transparent 3px,
                transparent 6px
            ),
            rgba(255,255,255,0.1);
        border: 1.5px dashed rgba(255,255,255,0.7);
        border-bottom: none;
        border-radius: 6px 6px 0 0;
        min-height: 2px;
        transition: height 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.15s;
        z-index: 1;
        pointer-events: none;
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

<!-- Zonas tap invisibles: mitad izquierda = anterior, mitad derecha = siguiente.
     Cubren toda la altura útil debajo del progress bar (top:6px) y se sitúan
     debajo del contenido del slide (pointer-events: auto pero z-index bajo
     respecto a botones interactivos). -->
<div id="wrapped-nav-left"  class="wrapped-nav-zone" aria-label="Slide anterior"></div>
<div id="wrapped-nav-right" class="wrapped-nav-zone" aria-label="Siguiente slide"></div>

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

<!-- Controles inferiores eliminados: avanzar/retroceder con tap en las
     mitades izquierda/derecha de la ventana (zonas .wrapped-nav-zone). -->


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
        /* Buddy top — tono cálido medio-claro (parejita en directo). */
        buddyTop:    dx(30,   5, 52, 28),
        /* Buddy list — algo más sobrio que el HERO. */
        buddyList:   dx(25,   0, 42, 20),
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

    /* Buddies (listen-together top): canción random "considerable" que
       NO haya salido en grupos anteriores. Fallback al pool de songs. */
    const buddyConsPool = (data.considerable_songs || data.songs || [])
        .filter(s => s.video_id);
    songForGroup.buddyTop = pickUnique(buddyConsPool);
    songForGroup.buddyList = pickUnique(buddyConsPool);
    if (!songForGroup.buddyTop || !songForGroup.buddyList) {
        const fallback = (data.songs || []).filter(s => s.video_id && !usedVids.has(s.video_id));
        if (!songForGroup.buddyTop && fallback.length) {
            const s = fallback.shift();
            songForGroup.buddyTop = { video_id: s.video_id, title: s.title, artist: s.artist };
            usedVids.add(s.video_id);
        }
        if (!songForGroup.buddyList && fallback.length) {
            const s = fallback.shift();
            songForGroup.buddyList = { video_id: s.video_id, title: s.title, artist: s.artist };
            usedVids.add(s.video_id);
        }
    }

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
    function makeSlide(bgKey, inner, extraAttrs, opts) {
        const attrs = extraAttrs ? ' ' + extraAttrs : '';
        opts = opts || {};
        const slideIdx = i++;
        /* Background blobs — divs flotantes con animaciones independientes.
           Se inyectan en cada slide para tener layers extras de fondo. */
        const bgFx = `
            <div class="bg-blob bg-blob-1"></div>
            <div class="bg-blob bg-blob-2"></div>
            <div class="bg-blob bg-blob-3"></div>
            <div class="bg-icons">
                <span style="--bg-i:0">♪</span>
                <span style="--bg-i:1">▮</span>
                <span style="--bg-i:2">★</span>
                <span style="--bg-i:3">▣</span>
                <span style="--bg-i:4">♫</span>
                <span style="--bg-i:5">◆</span>
                <span style="--bg-i:6">♥</span>
                <span style="--bg-i:7">▲</span>
            </div>`;
        if (opts.bare) {
            return `<div class="slide" id="slide-${slideIdx}" style="background:${SLIDE_BG[bgKey]};"${attrs}>${bgFx}${inner}</div>`;
        }
        const status = opts.status || 'Ready';
        return `<div class="slide" id="slide-${slideIdx}" style="background:${SLIDE_BG[bgKey]};"${attrs}>
            ${bgFx}
            <div class="slide-panel">
                <div class="slide-panel-titlebar">
                    <span class="slide-panel-title-text">
                        <span class="slide-panel-icon">♪</span>
                        wrapped.exe
                    </span>
                    <span class="slide-panel-ctrls">
                        <span class="slide-panel-ctrl">_</span>
                        <span class="slide-panel-ctrl">□</span>
                        <span class="slide-panel-ctrl">×</span>
                    </span>
                </div>
                <div class="slide-panel-body">${inner}</div>
                <div class="slide-panel-statusbar">
                    <span>${escapeHtml(status)}</span>
                    <span>${slideIdx + 1} / __TOTAL__</span>
                </div>
            </div>
        </div>`;
    }

    /* Welcome — grupo "opening" (4 primeras slides comparten canción). */
    slides.push(makeSlide('welcome', `
        <div class="slide-subtitle" style="opacity:0.85;letter-spacing:4px;">MELON HUB</div>
        <h1 class="slide-title" style="font-size:clamp(48px,10vw,140px);">Wrapped<br><span style="font-weight:400;">${YEAR}</span></h1>
        <p class="slide-subtitle">Tu año en música</p>
    `, groupSong('opening'), { title: 'wrapped.exe', status: 'Loading...' }));

    /* Total minutos — count-up animado. data-countup → el handler de
       showSlide lo dispara con animateCountUp al activar la slide. */
    const totalMin   = Number(data.total_min)   || 0;
    const totalPlays = Number(data.total_plays) || 0;
    slides.push(makeSlide('minutes', `
        <p class="slide-subtitle">Escuchaste</p>
        <div class="big-number" data-countup="${totalMin}">0</div>
        <p class="slide-subtitle">minutos de música<br>(${totalPlays.toLocaleString('es-ES')} reproducciones)</p>
        ${totalPlays === 0 ? '<p class="empty-state" style="margin-top:20px;">Empieza a reproducir música para acumular minutos.</p>' : ''}
    `, groupSong('opening'), { title: 'minutos.exe', status: 'Computing total...' }));

    /* Mes con MÁS escuchas. Va justo después del total para construir
       narrativa "escuchaste X minutos, y el mes más fuerte fue...". */
    const topMonth = data.top_month;
    if (topMonth && topMonth.minutes > 0) {
        slides.push(makeSlide('topMonth', `
            <p class="slide-subtitle">Tu mes top fue</p>
            <div class="big-number" style="color:var(--accent);">${escapeHtml(topMonth.name)}</div>
            <p class="slide-subtitle">Escuchaste <strong>${topMonth.minutes.toLocaleString('es-ES')}</strong> minutos<br>(${topMonth.plays.toLocaleString('es-ES')} reproducciones)</p>
        `, groupSong('opening'), { title: 'calendario.exe', status: 'Analyzing month...' }));
    }

    /* Gráfica anual: 12 barras (Ene-Dic). La barra del top month
       destaca en dorado. Las alturas se calculan en % del máximo
       para que la columna mayor llegue al 100% del contenedor. */
    const months = data.months_breakdown || [];
    /* PROYECCIÓN: para el mes en curso (si estamos viendo el año
       actual o all=1), calculamos cuánto escucharía el usuario si
       mantuviera el ritmo actual hasta fin de mes. La proyección se
       muestra como una extensión "fantasma" arriba de la barra real. */
    const __now = new Date();
    const __curYear  = __now.getFullYear();
    const __curMonth = __now.getMonth() + 1;
    const __curDay   = __now.getDate();
    const __daysInCurMonth = new Date(__curYear, __curMonth, 0).getDate();
    const __isCurYear = data.all || data.year === __curYear;

    months.forEach(m => {
        m.projection = 0;
        if (__isCurYear && m.m === __curMonth && m.minutes > 0 && __curDay > 0) {
            /* projected = actual / (currentDay / daysInMonth). */
            m.projection = Math.round(m.minutes * __daysInCurMonth / __curDay);
        }
    });

    /* Max para normalizar: incluye proyección si es mayor que actual.
       Así la barra TOP del chart siempre llega al 100% del contenedor,
       independiente de si el usuario tiene 100 o 1000 min. */
    const maxMin = months.reduce(
        (mx, m) => Math.max(mx, m.minutes, m.projection || 0),
        0
    );
    if (maxMin > 0) {
        const topMonthNum = topMonth ? topMonth.month_num : -1;
        slides.push(makeSlide('monthsChart', `
            <h1 class="slide-title" style="font-size:clamp(24px,4vw,44px);">Tu año mes a mes</h1>
            <p class="slide-subtitle" style="margin-top:-8px;">Minutos escuchados</p>
            <div class="months-chart">
                ${months.map(m => {
                    const pct  = (m.minutes / maxMin) * 100;
                    const projPct = m.projection ? (m.projection / maxMin) * 100 : 0;
                    const isTop = m.m === topMonthNum;
                    const isCur = __isCurYear && m.m === __curMonth;
                    return `
                        <div class="month-col">
                            <div class="month-bar-wrap">
                                ${projPct > pct ? `<div class="month-bar-proj" data-h="${projPct}" style="height:0%;" title="Proyección: ${m.projection.toLocaleString('es-ES')} min"></div>` : ''}
                                <div class="month-bar ${isTop ? 'top-month' : ''} ${isCur ? 'current-month' : ''}" data-h="${pct}" style="height:0%;">
                                    ${m.minutes > 0 ? `<span class="month-bar-val">${m.minutes.toLocaleString('es-ES')}</span>` : ''}
                                </div>
                            </div>
                            <div class="month-col-label">${escapeHtml(m.short)}</div>
                        </div>
                    `;
                }).join('')}
            </div>
            ${__isCurYear ? `<p class="slide-subtitle" style="font-size:11px;opacity:0.75;margin-top:4px;">Línea punteada = proyección a fin de mes</p>` : ''}
        `, groupSong('opening'), { title: 'chart.exe', status: 'Rendering chart...' }));
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
        `, groupSong('topSong'), { title: 'cancion.exe', status: 'Now playing...' }));
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
        `, groupSong('topSong'), { title: 'top-tracks.exe', status: 'Sorting tracks...' }));
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
        `, groupSong('artist'), { title: 'artista.exe', status: 'Loading artist...' }));
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
        `, groupSong('artist'), { title: 'top-artists.exe', status: 'Ranking artists...' }));
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
        `, groupSong('album'), { title: 'album.exe', status: 'Loading album...' }));
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
        `, groupSong('album'), { title: 'top-albums.exe', status: 'Sorting albums...' }));
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
        `, groupSong('genre'), { title: 'genero.exe', status: 'Analyzing tags...' }));
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
        `, groupSong('genre'), { title: 'top-genres.exe', status: 'Ranking genres...' }));
    }

    /* ── BUDDIES — Listen-Together ─────────────────────────────────
       Top buddy (1 slide HERO) + top 3 buddies (1 slide ranking). Solo
       si hay datos. Cada slide arranca con su propia canción "considerable"
       distinta a las usadas en grupos anteriores. */
    const buddies = (data.buddies || []).filter(b => b.user_key);
    /* Marco Win98 4-layer raised (idéntico al .profile-avatar-frame de
       perfil.css). Cuadrado, NO redondo. */
    const FRAME_SHADOW = '-1px -1px 0 var(--bezel-dark-1, #0a0a0a), 1px 1px 0 var(--bezel-light-1, #fff), -2px -2px 0 var(--bezel-dark-2, grey), 2px 2px 0 var(--bezel-light-2, #dfdfdf)';
    /* Versión más notoria del marco — usada en el HERO (top 1). Escala
       los layers a 3-6px en lugar de 1-2px + drop-shadow exterior. */
    const FRAME_SHADOW_BIG = '-3px -3px 0 var(--bezel-dark-1, #0a0a0a), 3px 3px 0 var(--bezel-light-1, #fff), -6px -6px 0 var(--bezel-dark-2, grey), 6px 6px 0 var(--bezel-light-2, #dfdfdf), 10px 10px 0 rgba(0,0,0,0.5)';

    /* Helper: avatar grande cuadrado para slide HERO. */
    const buddyAvatar = (b, size) => {
        const initial = escapeHtml((b.label || '?').charAt(0).toUpperCase());
        const sizeCss = size || 'clamp(120px,28vw,200px)';
        const wrap = `margin:28px auto;width:${sizeCss};height:${sizeCss};background:var(--inset-bg,var(--input-bg,#fff));overflow:hidden;box-shadow:${FRAME_SHADOW_BIG};`;
        if (b.image_url) {
            return `<div style="${wrap}">
                <img src="${escapeHtml(b.image_url)}" alt="" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="this.outerHTML='<div style=\\'width:100%;height:100%;background:var(--accent);color:var(--accent-text,#fff);display:flex;align-items:center;justify-content:center;font-size:clamp(56px,12vw,96px);font-weight:bold;\\'>${initial}</div>'">
            </div>`;
        }
        return `<div style="${wrap}background:var(--accent);display:flex;align-items:center;justify-content:center;">
            <span style="font-size:clamp(56px,12vw,96px);color:var(--accent-text,#fff);font-weight:bold;">${initial}</span>
        </div>`;
    };
    /* Avatar pequeño inline cuadrado para top-3 list. */
    const buddyAvatarInline = (b) => {
        const initial = escapeHtml((b.label || '?').charAt(0).toUpperCase());
        const wrap = `width:32px;height:32px;background:var(--inset-bg,var(--input-bg,#fff));overflow:hidden;flex-shrink:0;box-shadow:${FRAME_SHADOW};`;
        if (b.image_url) {
            return `<div style="${wrap}">
                <img src="${escapeHtml(b.image_url)}" alt="" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="this.outerHTML='<div style=\\'width:100%;height:100%;background:var(--accent);color:var(--accent-text,#fff);display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;\\'>${initial}</div>'">
            </div>`;
        }
        return `<div style="${wrap}background:var(--accent);display:flex;align-items:center;justify-content:center;">
            <span style="color:var(--accent-text,#fff);font-weight:bold;font-size:14px;">${initial}</span>
        </div>`;
    };

    if (buddies.length > 0) {
        const topBuddy = buddies[0];
        slides.push(makeSlide('buddyTop', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">
                Con quien más has escuchado música
            </h1>
            ${buddyAvatar(topBuddy)}
            <div class="slide-subtitle" style="font-size:clamp(20px,3.5vw,36px);margin-top:8px;">
                ${escapeHtml(topBuddy.label || topBuddy.user_key)}
            </div>
            <div class="slide-subtitle" style="opacity:0.8;font-size:clamp(13px,1.8vw,18px);">
                ${(topBuddy.minutes || 0).toLocaleString('es-ES')} minutos escuchando juntos
            </div>
        `, groupSong('buddyTop'), { title: 'buddy.exe', status: 'Loading partner stats...' }));

        slides.push(makeSlide('buddyList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus compañeros de escucha</h1>
            <div class="top-list">
                ${buddies.slice(0, 3).map((b, idx) => `
                    <div class="top-item" style="--i:${idx}">
                        <div class="top-rank">${idx + 1}</div>
                        ${buddyAvatarInline(b)}
                        <div class="top-meta">
                            <div class="top-meta-title">${escapeHtml(b.label || b.user_key)}</div>
                            <div class="top-meta-sub">${(b.minutes || 0).toLocaleString('es-ES')} min</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `, groupSong('buddyList'), { title: 'top-buddies.exe', status: 'Ranking buddies...' }));
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
                <div class="wc-titlebar">
                    <span class="wc-titlebar-text">
                        <span class="wc-titlebar-icon">♪</span>
                        wrapped.exe
                    </span>
                    <span class="wc-titlebar-controls">
                        <span class="wc-titlebar-ctrl">_</span>
                        <span class="wc-titlebar-ctrl">□</span>
                        <span class="wc-titlebar-ctrl">×</span>
                    </span>
                </div>
                <div class="wc-hero-frame">
                    <div class="wc-hero-stripe wc-hero-stripe-top"></div>
                    <div class="wc-hero">
                        ${heroSrc
                            ? `<img class="wc-hero-img" crossorigin="anonymous" src="${escapeHtml(heroSrc)}" alt="" onerror="this.outerHTML='<div class=\\'wc-hero-fallback\\'>🎤</div>'">`
                            : `<div class="wc-hero-fallback">🎤</div>`}
                    </div>
                    <div class="wc-hero-stripe wc-hero-stripe-bottom"></div>
                    <div class="wc-hero-badge">
                        ${YEAR}<div class="wc-badge-star">★</div>
                    </div>
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
                    <div class="wc-sep"></div>
                    <div class="wc-stats">
                        <div class="wc-stat">
                            <div class="wc-stat-label">Minutes Listened</div>
                            <div class="wc-stat-val">${totalMin.toLocaleString('es-ES')}</div>
                        </div>
                        <div class="wc-stat">
                            <div class="wc-stat-label">Top Genre</div>
                            <div class="wc-stat-val smaller">${escapeHtml(sumGenre)}</div>
                        </div>
                    </div>
                    <div class="wc-footer">
                        <span><span class="wc-logo">♪</span> MELON HUB / WRAPPED ${YEAR}</span>
                        <span class="wc-footer-right">www.melonhub.com</span>
                    </div>
                </div>
            </div>
            <div class="wc-share-row">
                <button type="button" class="wc-share-btn" id="wc-btn-share">📱 Compartir</button>
                <button type="button" class="wc-share-btn" id="wc-btn-download">💾 Descargar</button>
                <button type="button" class="wc-share-btn" id="wc-btn-copy">📋 Copiar</button>
            </div>
        </div>
    `, groupSong('genre'), { bare: true }));

    /* Reemplazar __TOTAL__ por el número final de slides en el status bar. */
    root.innerHTML = slides.join('').replace(/__TOTAL__/g, String(slides.length));

    /* Progress bar segmentos. */
    const progress = document.getElementById('wrapped-progress');
    progress.innerHTML = slides.map(_ => '<div class="progress-segment"></div>').join('');

    showSlide(0);

    /* Pre-renderizar el patrón de pinstripes de la tarjeta resumen en
       un canvas → data URL. html2canvas no captura bien repeating-
       linear-gradient pero SÍ captura PNG data-URLs perfectamente. */
    applyStripePatterns();

    /* Init del player AHORA — DOM ya tiene el slide activo, así
       initWrappedPlayer puede leer su data-song-id y arrancar con la
       canción correcta (la de "opening", no la top song). */
    if (window.YT && window.YT.Player && !ytWrappedPlayer) {
        initWrappedPlayer();
    }
}

/** Reemplaza el background CSS de los `.wc-hero-stripe` por un PNG
 *  data-URL generado con canvas — usando los colores del tema
 *  resueltos en runtime. html2canvas v1.4.1 NO renderiza correctamente
 *  `repeating-linear-gradient`, por eso convertimos el patrón a una
 *  imagen tile-able real antes de capturar. */
function applyStripePatterns() {
    const stripes = document.querySelectorAll('.wc-hero-stripe');
    if (!stripes.length) return;
    const cs = getComputedStyle(document.body);
    const accent = (cs.getPropertyValue('--accent') || '#EDC001').trim();
    const text   = (cs.getPropertyValue('--text')   || '#000000').trim();

    /* Canvas tile 16×6 con pinstripes diagonales. Se repetirá X. */
    const c = document.createElement('canvas');
    c.width = 16; c.height = 6;
    const ctx = c.getContext('2d');
    ctx.fillStyle = text;
    ctx.fillRect(0, 0, 16, 6);
    /* Dos barras diagonales accent. */
    ctx.fillStyle = accent;
    /* Primera barra. */
    ctx.beginPath();
    ctx.moveTo(0, 6); ctx.lineTo(4, 0); ctx.lineTo(8, 0); ctx.lineTo(4, 6);
    ctx.closePath(); ctx.fill();
    /* Segunda barra. */
    ctx.beginPath();
    ctx.moveTo(8, 6); ctx.lineTo(12, 0); ctx.lineTo(16, 0); ctx.lineTo(12, 6);
    ctx.closePath(); ctx.fill();

    const url = c.toDataURL('image/png');
    stripes.forEach(s => {
        s.style.setProperty('background-image', `url(${url})`, 'important');
        s.style.setProperty('background-repeat', 'repeat', 'important');
        s.style.setProperty('background-size', '16px 6px', 'important');
        s.style.setProperty('background-color', text, 'important');
    });
}

let currentSlide = 0;
let autoTimer    = null;

/* Pool de transiciones REGULARES — todas creativas Win98, ninguna con
   chromatic aberration. El chromatic aberration se reserva para la
   transición especial a la última slide. */
const TX_TYPES = ['tx-vinyl', 'tx-crt-on', 'tx-vhs-tracking', 'tx-floppy', 'tx-defrag', 'tx-page-tear', 'tx-cascade', 'tx-rewind'];
const TX_FINAL = 'tx-final-glitch';
let __lastTx = '';

function pickWrappedTransition(targetIndex, total) {
    /* Entrando a la ÚLTIMA slide (closure) → tx exagerada con
       chromatic aberration + glitch + flash múltiple. */
    if (targetIndex === total - 1) return TX_FINAL;
    let tx, attempts = 0;
    do {
        tx = TX_TYPES[Math.floor(Math.random() * TX_TYPES.length)];
        attempts++;
    } while (tx === __lastTx && attempts < 6);
    __lastTx = tx;
    return tx;
}

/* Overlay para el flash entre slides. `intense` → multi-color final. */
function fireFlashOverlay(intense) {
    let ov = document.getElementById('tx-flash-overlay');
    if (!ov) {
        ov = document.createElement('div');
        ov.id = 'tx-flash-overlay';
        document.body.appendChild(ov);
    }
    ov.classList.remove('fire', 'fire-final');
    void ov.offsetWidth;
    ov.classList.add(intense ? 'fire-final' : 'fire');
}

function showSlide(i) {
    const all = document.querySelectorAll('.slide');
    if (i < 0 || i >= all.length) return;
    const prev = document.querySelector('.slide.active');
    const goingForward = i > currentSlide;
    const tx = pickWrappedTransition(i, all.length);
    const isFinal = tx === TX_FINAL;
    /* tx-final-glitch tarda más → tenemos que esperar más. */
    const txDuration = isFinal ? 1600 : 700;

    all.forEach(s => {
        s.classList.remove('active', 'exiting', 'entering', 'exit-left', 'exit-right', 'enter-left', 'enter-right');
        TX_TYPES.forEach(t => s.classList.remove(t));
        s.classList.remove(TX_FINAL);
    });
    if (prev && prev !== all[i]) {
        prev.classList.add('exiting', tx);
        prev.classList.add(goingForward ? 'exit-left' : 'exit-right');
        setTimeout(() => {
            prev.classList.remove('exiting', 'exit-left', 'exit-right', tx);
        }, txDuration);
    }
    /* Flash:
       - tx-zoom: flash blanco corto.
       - tx-final-glitch: flash MULTI-COLOR (blanco → magenta → cyan)
         largo, sincronizado con el glitch. */
    if (tx === 'tx-zoom') {
        fireFlashOverlay(false);
    } else if (isFinal) {
        fireFlashOverlay(true);
    }
    all[i].classList.add('entering', tx, goingForward ? 'enter-right' : 'enter-left');
    void all[i].offsetWidth;
    requestAnimationFrame(() => {
        all[i].classList.remove('enter-right', 'enter-left');
        all[i].classList.add('active');
        setTimeout(() => {
            all[i].classList.remove('entering');
        }, txDuration);
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
        /* Bars de la gráfica de meses (real + proyección) — animadas
           con stagger por mes. */
        all[i].querySelectorAll('.month-bar[data-h], .month-bar-proj[data-h]').forEach((bar, idx) => {
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
        autoTimer = setTimeout(() => showSlide(i + 1), 9000);
    }
}

/* Navegación: tap en mitad izquierda = anterior, mitad derecha =
   siguiente. Misma lógica para keyboard (←/→/Space). Escape cierra
   el iframe via postMessage al padre. */
function goNext() {
    const total = document.querySelectorAll('.slide').length;
    if (currentSlide < total - 1) showSlide(currentSlide + 1);
}
function goPrev() {
    if (currentSlide > 0) showSlide(currentSlide - 1);
}
function closeWrapped() {
    if (window.parent && window.parent !== window) {
        try { window.parent.postMessage({ type: 'wrapped-close' }, '*'); } catch(_) {}
    } else {
        window.location.href = '../desktop-base.php';
    }
}
document.getElementById('wrapped-nav-right').addEventListener('click', goNext);
document.getElementById('wrapped-nav-left').addEventListener('click',  goPrev);
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight' || e.key === ' ') goNext();
    if (e.key === 'ArrowLeft')                    goPrev();
    if (e.key === 'Escape')                       closeWrapped();
});

/* ════════════════════════════════════════════════════════════════
   --wc-scale dinámico — escala el card resumen (slide final) en
   proporción al tamaño del iframe. CSS calc no soporta dividir
   longitudes con distintas unidades, así que el cálculo va aquí.
   - byHeight: cuánto cabe verticalmente (88vh / 600px de alto natural).
   - byWidth:  cuánto cabe horizontalmente (92vw / 420px de ancho).
   - clamp(1, ..., 2.2): nunca encoge por debajo de 1, max 2.2x.
   ════════════════════════════════════════════════════════════════ */
function updateWcScale() {
    const W = window.innerWidth;
    const H = window.innerHeight;
    /* El card mantiene SIEMPRE el mismo ratio respecto al iframe:
       - Card natural: 380w × 720h (la altura aprox del último slide
         con título, hero 1:1, top artists/songs, stats y footer).
       - byHeight: el card ocupa el 87% del alto del iframe.
       - byWidth:  nunca excede el 90% del ancho del iframe.
       Se permite encoger hasta 0.5 (ventanas muy pequeñas) y crecer
       hasta 3.0 (sin cap teórico, sólo seguridad). El más restrictivo
       de byHeight/byWidth gana → el card cabe siempre. */
    const byHeight = (H * 0.90) / 720;
    const byWidth  = (W * 0.95) / 380;
    const scale = Math.max(0.5, Math.min(byHeight, byWidth, 3));
    document.documentElement.style.setProperty('--wc-scale', scale.toFixed(3));
}
window.addEventListener('resize', updateWcScale);
updateWcScale();

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
/** html2canvas v1.4.1 no resuelve siempre bien CSS variables (var(...))
 *  en background-image/box-shadow. Para evitar que la PNG salga con
 *  colores fallback (azul Win98 en lugar del oro del tema) hacemos
 *  un walk del DOM y resolvemos los var() a valores computados antes
 *  de capturar. Tras la captura, revertimos los estilos inline. */
function inlineComputedColors(root) {
    const original = new WeakMap();
    const props = [
        'background-image', 'background-color',
        'color', 'box-shadow',
        'border-color', 'border-top-color', 'border-right-color',
        'border-bottom-color', 'border-left-color',
    ];
    const all = [root, ...root.querySelectorAll('*')];
    all.forEach(el => {
        const cs = getComputedStyle(el);
        const saved = {};
        props.forEach(p => {
            const v = cs.getPropertyValue(p);
            if (v && v !== 'none' && v !== 'rgba(0, 0, 0, 0)') {
                saved[p] = el.style.getPropertyValue(p);
                el.style.setProperty(p, v, 'important');
            }
        });
        original.set(el, saved);
    });
    return () => {
        all.forEach(el => {
            const saved = original.get(el);
            if (!saved) return;
            Object.keys(saved).forEach(p => {
                if (saved[p]) el.style.setProperty(p, saved[p]);
                else          el.style.removeProperty(p);
            });
        });
    };
}

async function captureWrappedCard() {
    const card = document.getElementById('wrapped-summary-card');
    if (!card) return null;

    if (document.fonts && document.fonts.ready) {
        try { await document.fonts.ready; } catch (_) {}
    }

    /* PRIMARIO: render manual del card con Canvas API. Es el único
       camino 100% fiable — html2canvas v1.4.1 producía un split de
       colores a la mitad de la imagen que era imposible de eliminar
       (incluso con foreignObjectRendering, inlineComputedColors,
       freeze de animaciones, etc.). Dibujamos cada elemento con
       fillRect/fillText/drawImage usando los colores resueltos del
       tema activo. */
    try {
        const blob = await renderCardManually(card);
        if (blob) return blob;
    } catch (e) {
        console.warn('[wrapped] manual render failed, falling back to html2canvas', e);
    }

    /* FALLBACK: html2canvas básico. */
    if (typeof html2canvas !== 'function') return null;
    const htmlEl = document.documentElement;
    const hadLcdOn  = htmlEl.classList.contains('lcd-filter-on');
    const hadLcdTop = htmlEl.classList.contains('lcd-filter-top');
    htmlEl.classList.remove('lcd-filter-on', 'lcd-filter-top');
    const winBg = (getComputedStyle(document.body).getPropertyValue('--win-bg') || '#c0c0c0').trim();
    try {
        const canvas = await html2canvas(card, {
            useCORS: true,
            backgroundColor: winBg,
            scale: 2,
            logging: false,
            onclone: (clonedDoc) => {
                try { clonedDoc.body.className = document.body.className; } catch (_) {}
            },
        });
        return new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
    } finally {
        if (hadLcdOn)  htmlEl.classList.add('lcd-filter-on');
        if (hadLcdTop) htmlEl.classList.add('lcd-filter-top');
    }
}

/** Lee la paleta del tema activo + medidas + datos del card y los
 *  dibuja a mano en un canvas. Esto evita TODOS los bugs de
 *  html2canvas. */
async function renderCardManually(card) {
    const cs = getComputedStyle(document.body);
    const COLORS = {
        winBg:         (cs.getPropertyValue('--win-bg')         || '#c0c0c0').trim(),
        text:          (cs.getPropertyValue('--text')           || '#000000').trim(),
        textMuted:     (cs.getPropertyValue('--text-muted')     || '#555555').trim(),
        accent:        (cs.getPropertyValue('--accent')         || '#1db954').trim(),
        accentText:    (cs.getPropertyValue('--accent-text')    || '#ffffff').trim(),
        insetBg:       (cs.getPropertyValue('--inset-bg')       || '#ffffff').trim(),
        inputBg:       (cs.getPropertyValue('--input-bg')       || '#ffffff').trim(),
        bezelLight1:   (cs.getPropertyValue('--bezel-light-1')  || '#ffffff').trim(),
        bezelLight2:   (cs.getPropertyValue('--bezel-light-2')  || '#dfdfdf').trim(),
        bezelDark1:    (cs.getPropertyValue('--bezel-dark-1')   || '#0a0a0a').trim(),
        bezelDark2:    (cs.getPropertyValue('--bezel-dark-2')   || '#808080').trim(),
        titlebarStart: (cs.getPropertyValue('--titlebar-start') || '#000080').trim(),
        titlebarEnd:   (cs.getPropertyValue('--titlebar-end')   || '#1084d0').trim(),
        titlebarText:  (cs.getPropertyValue('--titlebar-text')  || '#ffffff').trim(),
        btnBg:         (cs.getPropertyValue('--btn-bg')         || '#c0c0c0').trim(),
    };

    /* Datos del card (textos + img src). */
    const getText = sel => (card.querySelector(sel)?.textContent || '').trim();
    const data = {
        year: getText('.wc-hero-badge')?.replace(/[★]/g, '').trim() || '2026',
        artists: [...card.querySelectorAll('.wc-cols .wc-col:nth-child(1) ol li')].map(li => ({
            rank: (li.querySelector('.rank')?.textContent || '').trim(),
            name: (li.querySelector('span:not(.rank)')?.textContent || '').trim(),
        })),
        songs: [...card.querySelectorAll('.wc-cols .wc-col:nth-child(2) ol li')].map(li => ({
            rank: (li.querySelector('.rank')?.textContent || '').trim(),
            name: (li.querySelector('span:not(.rank)')?.textContent || '').trim(),
        })),
        minutes: getText('.wc-stats .wc-stat:nth-child(1) .wc-stat-val'),
        genre:   getText('.wc-stats .wc-stat:nth-child(2) .wc-stat-val'),
        footerL: getText('.wc-footer span:nth-child(1)'),
        footerR: getText('.wc-footer .wc-footer-right'),
        heroImgEl: card.querySelector('.wc-hero-img'),
    };

    /* Layout — coordinadas en CSS pixels, luego escalamos.
       W = 380 (mismo valor que la CSS .wrapped-card width).
       outputScale escala el BITMAP final según el ancho real (con
       transform:scale aplicado) que tiene el card en pantalla. */
    const W = 380;
    const PAD = 14;
    const HERO_SIZE = W - 20; /* margin 10px cada lado */
    const displayedW = Math.max(280, card.getBoundingClientRect().width || W);
    const outputScale = displayedW / W;
    let y = 0;

    /* Pre-cargar la imagen del hero antes de empezar a dibujar. */
    let heroImg = null;
    if (data.heroImgEl?.src) {
        try {
            heroImg = await loadImage(data.heroImgEl.src);
        } catch (_) { heroImg = null; }
    }

    /* Calcular altura total dinámicamente. */
    const titleH = 22;
    const heroFrameTop = titleH + 14;
    const heroFrameH = HERO_SIZE + 8;
    const bodyTop = heroFrameTop + heroFrameH + 10;
    /* Header (16) + 5 items @ 18 + sep (10) + stat (50) + footer (24) + paddings */
    const colsH = 16 + 5 * 18 + 6;
    const sepH = 10;
    const statsH = 50;
    const footerH = 22;
    const bodyH = 14 + colsH + sepH + statsH + 14 + footerH;
    const H = bodyTop + bodyH;

    /* scale = 2 → super-sampling para texto nítido.
       outputScale → multiplicador para que el bitmap resultante
       coincida con el ancho del card en pantalla. */
    const scale = 2 * outputScale;
    const cv = document.createElement('canvas');
    cv.width = Math.round(W * scale); cv.height = Math.round(H * scale);
    const ctx = cv.getContext('2d');
    ctx.scale(scale, scale);
    ctx.textBaseline = 'top';
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';

    /* Helpers. */
    const fillRect = (color, x, yy, w, h) => { ctx.fillStyle = color; ctx.fillRect(x, yy, w, h); };
    const drawBezelOutset = (x, yy, w, h) => {
        /* 4-layer outset bezel: dark1 -1/-1, light1 1/1, dark2 -2/-2, light2 2/2 */
        fillRect(COLORS.bezelDark1, x-1, yy-1, w+1, 1);
        fillRect(COLORS.bezelDark1, x-1, yy-1, 1, h+1);
        fillRect(COLORS.bezelLight1, x, yy+h, w+1, 1);
        fillRect(COLORS.bezelLight1, x+w, yy, 1, h+1);
        fillRect(COLORS.bezelDark2, x-2, yy-2, w+2, 1);
        fillRect(COLORS.bezelDark2, x-2, yy-2, 1, h+2);
        fillRect(COLORS.bezelLight2, x-1, yy+h+1, w+3, 1);
        fillRect(COLORS.bezelLight2, x+w+1, yy-1, 1, h+2);
    };
    const drawBezelRaised = (x, yy, w, h) => {
        /* Win98 raised: light top-left, dark bottom-right. 4 capas inset. */
        fillRect(COLORS.bezelLight1, x, yy, w, 1);
        fillRect(COLORS.bezelLight1, x, yy, 1, h);
        fillRect(COLORS.bezelDark1, x, yy+h-1, w, 1);
        fillRect(COLORS.bezelDark1, x+w-1, yy, 1, h);
        fillRect(COLORS.bezelLight2, x+1, yy+1, w-2, 1);
        fillRect(COLORS.bezelLight2, x+1, yy+1, 1, h-2);
        fillRect(COLORS.bezelDark2, x+1, yy+h-2, w-2, 1);
        fillRect(COLORS.bezelDark2, x+w-2, yy+1, 1, h-2);
    };
    const drawBezelSunken = (x, yy, w, h) => {
        /* Inverso del raised. */
        fillRect(COLORS.bezelDark2, x, yy, w, 1);
        fillRect(COLORS.bezelDark2, x, yy, 1, h);
        fillRect(COLORS.bezelLight1, x, yy+h-1, w, 1);
        fillRect(COLORS.bezelLight1, x+w-1, yy, 1, h);
        fillRect(COLORS.bezelDark1, x+1, yy+1, w-2, 1);
        fillRect(COLORS.bezelDark1, x+1, yy+1, 1, h-2);
        fillRect(COLORS.bezelLight2, x+1, yy+h-2, w-2, 1);
        fillRect(COLORS.bezelLight2, x+w-2, yy+1, 1, h-2);
    };
    const drawText = (text, x, yy, opts = {}) => {
        ctx.font = (opts.weight || 'normal') + ' ' + (opts.size || 12) + 'px ' + (opts.family || "'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif");
        ctx.fillStyle = opts.color || COLORS.text;
        if (opts.shadow) {
            ctx.fillStyle = opts.shadow;
            ctx.fillText(text, x + 1, yy + 1);
            ctx.fillStyle = opts.color || COLORS.text;
        }
        ctx.fillText(text, x, yy);
    };
    const drawTextEllipsis = (text, x, yy, maxW, opts) => {
        ctx.font = (opts.weight || 'normal') + ' ' + (opts.size || 12) + 'px ' + (opts.family || "'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif");
        if (ctx.measureText(text).width <= maxW) return drawText(text, x, yy, opts);
        let truncated = text;
        while (truncated.length > 1 && ctx.measureText(truncated + '…').width > maxW) {
            truncated = truncated.slice(0, -1);
        }
        drawText(truncated + '…', x, yy, opts);
    };

    /* === 1. Fondo del card === */
    fillRect(COLORS.winBg, 0, 0, W, H);
    drawBezelRaised(0, 0, W, H);

    /* === 2. Title bar con gradient horizontal === */
    const tbY = 3;
    const tbH = titleH - 3;
    const tbGrad = ctx.createLinearGradient(0, 0, W, 0);
    tbGrad.addColorStop(0, COLORS.titlebarStart);
    tbGrad.addColorStop(1, COLORS.titlebarEnd);
    ctx.fillStyle = tbGrad;
    ctx.fillRect(3, tbY, W - 6, tbH);
    /* Icono ♪ */
    fillRect(COLORS.accent, 6, tbY + 3, 14, 14);
    drawBezelRaised(6, tbY + 3, 14, 14);
    drawText('♪', 9, tbY + 4, { color: COLORS.accentText, size: 10, weight: 'bold' });
    /* "wrapped.exe" */
    drawText('wrapped.exe', 24, tbY + 4, { color: COLORS.titlebarText, size: 12, weight: 'bold' });
    /* Botones _ □ × */
    for (let i = 0; i < 3; i++) {
        const bx = W - 6 - (16 + 2) * (3 - i);
        fillRect(COLORS.btnBg, bx, tbY + 3, 16, 14);
        drawBezelRaised(bx, tbY + 3, 16, 14);
        const labels = ['_', '□', '×'];
        drawText(labels[i], bx + 5, tbY + 4, { color: COLORS.text, size: 10, weight: 'bold' });
    }

    /* === 3. Hero frame con bezel outset === */
    const hfX = 10, hfY = heroFrameTop;
    const hfW = HERO_SIZE, hfH = heroFrameH;
    fillRect(COLORS.insetBg, hfX, hfY, hfW, hfH);
    drawBezelOutset(hfX, hfY, hfW, hfH);

    /* === 4. Pinstripe top (diagonal yellow/black) === */
    const stripeH = 6;
    drawPinstripe(ctx, hfX - 2, hfY - 8, hfW + 4, stripeH, COLORS.accent, COLORS.text);

    /* === 5. Hero photo === */
    const phX = hfX + 4, phY = hfY + 4;
    const phSize = hfW - 8;
    fillRect(COLORS.insetBg, phX, phY, phSize, phSize);
    if (heroImg) {
        ctx.drawImage(heroImg, phX, phY, phSize, phSize);
    } else {
        fillRect(COLORS.accent, phX, phY, phSize, phSize);
        drawText('🎤', phX + phSize/2 - 30, phY + phSize/2 - 30, { color: COLORS.accentText, size: 60 });
    }

    /* === 6. Pinstripe bottom === */
    drawPinstripe(ctx, hfX - 2, hfY + hfH + 2, hfW + 4, stripeH, COLORS.accent, COLORS.text);

    /* === 7. Badge 2026 con rotación === */
    ctx.save();
    ctx.translate(hfX + 10, hfY + 10);
    ctx.rotate(-3 * Math.PI / 180);
    const badgeW = 70, badgeH = 26;
    const bgrad = ctx.createLinearGradient(0, 0, 0, badgeH);
    bgrad.addColorStop(0, COLORS.titlebarStart);
    bgrad.addColorStop(1, COLORS.titlebarEnd);
    /* Drop shadow */
    fillRect(COLORS.bezelDark1, 3, 3, badgeW, badgeH);
    ctx.fillStyle = bgrad;
    ctx.fillRect(0, 0, badgeW, badgeH);
    drawBezelRaised(0, 0, badgeW, badgeH);
    drawText(data.year, 11, 5, { color: COLORS.titlebarText, size: 18, weight: 'bold', shadow: 'rgba(0,0,0,0.55)' });
    ctx.restore();
    /* Estrella ★ */
    drawText('★', hfX + 78, hfY + 8, { color: COLORS.accent, size: 16, weight: 'bold', shadow: COLORS.bezelDark1 });

    /* === 8. Body — Top Artists + Top Songs columns === */
    y = bodyTop + 14;
    const bodyX = PAD;
    const bodyW = W - PAD * 2;
    const gap = 14;
    const colW = (bodyW - gap) / 2;

    /* Headers */
    drawListHeader(ctx, bodyX, y, colW, 'TOP ARTISTS', COLORS, drawBezelRaised, drawText);
    drawListHeader(ctx, bodyX + colW + gap, y, colW, 'TOP SONGS', COLORS, drawBezelRaised, drawText);
    y += 18;

    /* Items */
    const itemH = 18;
    for (let i = 0; i < 5; i++) {
        const a = data.artists[i] || { rank: String(i+1), name: '—' };
        const s = data.songs[i] || { rank: String(i+1), name: '—' };
        /* Alternating bg */
        if (i % 2 === 0) {
            fillRect('rgba(0,0,0,0.06)', bodyX, y, colW, itemH);
            fillRect('rgba(0,0,0,0.06)', bodyX + colW + gap, y, colW, itemH);
        }
        drawText(a.rank, bodyX + 4, y + 3, { color: COLORS.accent, size: 12, weight: 'bold' });
        drawTextEllipsis(a.name, bodyX + 22, y + 3, colW - 26, { color: COLORS.text, size: 12 });
        drawText(s.rank, bodyX + colW + gap + 4, y + 3, { color: COLORS.accent, size: 12, weight: 'bold' });
        drawTextEllipsis(s.name, bodyX + colW + gap + 22, y + 3, colW - 26, { color: COLORS.text, size: 12 });
        y += itemH;
    }

    /* === 9. Etched separator === */
    y += 6;
    fillRect(COLORS.bezelDark2, bodyX, y, bodyW, 1);
    fillRect(COLORS.bezelLight1, bodyX, y + 1, bodyW, 1);
    y += 8;

    /* === 10. Stats sunken boxes === */
    const statH = 44;
    drawStatBox(ctx, bodyX, y, colW, statH, 'MINUTES LISTENED', data.minutes, COLORS, drawBezelSunken, drawText);
    drawStatBox(ctx, bodyX + colW + gap, y, colW, statH, 'TOP GENRE', data.genre, COLORS, drawBezelSunken, drawText);
    y += statH + 10;

    /* === 11. Footer === */
    const fgrad = ctx.createLinearGradient(0, 0, W, 0);
    fgrad.addColorStop(0, COLORS.titlebarStart);
    fgrad.addColorStop(1, COLORS.titlebarEnd);
    ctx.fillStyle = fgrad;
    ctx.fillRect(bodyX, y, bodyW, footerH);
    drawText(data.footerL || '♪ MELON HUB / WRAPPED 2026', bodyX + 5, y + 6, { color: COLORS.titlebarText, size: 9, weight: 'bold' });
    const rightW = ctx.measureText(data.footerR || 'www.melonhub.com').width;
    drawText(data.footerR || 'www.melonhub.com', bodyX + bodyW - rightW - 5, y + 6, { color: COLORS.titlebarText, size: 9, weight: 'bold' });

    /* === 12. Filtro LCD (scanlines + vignette) — replica
       body::before/::after del tema. Sólo si el usuario lo tiene
       activado (html.lcd-filter-on). */
    if (document.documentElement.classList.contains('lcd-filter-on')) {
        applyLcdFilter(ctx, W, H);
    }

    return new Promise(resolve => cv.toBlob(resolve, 'image/png'));
}

/** Reproduce el filtro LCD VHS sobre el canvas del card:
 *  - Scanlines: barras horizontales de 1px alternando oscuras y
 *    claras con offsets de 1px (cada 3px se repite).
 *  - Vignette: radial gradient desde el centro hacia las esquinas. */
function applyLcdFilter(ctx, W, H) {
    /* SCANLINES — patrón cada 3px:
       y%3==0 → oscura, y%3==1 → clara, y%3==2 → transparente. */
    ctx.fillStyle = 'rgba(0, 0, 0, 0.10)';
    for (let yy = 0; yy < H; yy += 3) {
        ctx.fillRect(0, yy, W, 1);
    }
    ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
    for (let yy = 1; yy < H; yy += 3) {
        ctx.fillRect(0, yy, W, 1);
    }

    /* VIGNETTE radial: claro en el centro, oscuro en esquinas. */
    const cx = W / 2, cy = H / 2;
    const inner = Math.min(W, H) * 0.30;
    const outer = Math.max(W, H) * 0.70;
    const vg = ctx.createRadialGradient(cx, cy, inner, cx, cy, outer);
    vg.addColorStop(0,    'rgba(0, 0, 0, 0)');
    vg.addColorStop(0.6,  'rgba(0, 0, 0, 0)');
    vg.addColorStop(0.85, 'rgba(0, 0, 0, 0.10)');
    vg.addColorStop(1,    'rgba(0, 0, 0, 0.20)');
    ctx.fillStyle = vg;
    ctx.fillRect(0, 0, W, H);
}

function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

function drawPinstripe(ctx, x, y, w, h, accent, bg) {
    ctx.fillStyle = bg;
    ctx.fillRect(x, y, w, h);
    ctx.fillStyle = accent;
    const tile = 8;
    for (let i = 0; i < Math.ceil(w / tile); i++) {
        ctx.beginPath();
        const tx = x + i * tile;
        ctx.moveTo(tx, y + h);
        ctx.lineTo(tx + h, y);
        ctx.lineTo(tx + h + 4, y);
        ctx.lineTo(tx + 4, y + h);
        ctx.closePath();
        ctx.fill();
    }
}

function drawListHeader(ctx, x, y, w, text, C, raisedFn, drawTextFn) {
    ctx.fillStyle = C.accent;
    ctx.fillRect(x, y, w, 14);
    raisedFn(x, y, w, 14);
    drawTextFn(text, x + 4, y + 2, { color: C.accentText, size: 10, weight: 'bold' });
}

function drawStatBox(ctx, x, y, w, h, label, val, C, sunkenFn, drawTextFn) {
    ctx.fillStyle = C.inputBg;
    ctx.fillRect(x, y, w, h);
    sunkenFn(x, y, w, h);
    drawTextFn(label, x + 6, y + 4, { color: C.textMuted, size: 9, weight: 'bold' });
    const isLong = (val || '').length > 6;
    drawTextFn(val || '—', x + 6, y + 16, { color: C.accent, size: isLong ? 18 : 22, weight: 'bold' });
}

function wrappedFilename() {
    const year = (new Date()).getFullYear();
    return 'melonhub-wrapped-' + year + '.png';
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
                await navigator.share({ files: [file], title: 'Mi Melon Hub Wrapped' });
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
