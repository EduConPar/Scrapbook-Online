<?php
/* ──────────────────────────────────────────────────────────────────────
   PLACEHOLDER MÓVIL — para apps que en el escritorio son parciales
   (Perfil, Música) y todavía no tienen entrada standalone para móvil.
   ──────────────────────────────────────────────────────────────────────
   En el escritorio, perfil.php y reproductor.php son `require`s desde
   desktop-base.php — esperan $desktopLabel, appTitleIcon() y el resto
   del contexto que monta el escritorio. No se pueden cargar tal cual
   como página standalone. Cuando estén adaptadas a móvil, basta con
   cambiar la URL del array $apps en mobile.php para que apunten a la
   nueva entrada.
   ────────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once dirname(__DIR__) . '/assets/config.php';

if (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    header('Location: ../index.php');
    exit;
}

$appName = isset($_GET['app']) ? trim((string)$_GET['app']) : 'Esta app';
$appName = mb_substr($appName, 0, 40);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c2b54">
    <title><?= htmlspecialchars($appName) ?> — Próximamente</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/css/mobile.css">
</head>
<body>
<div id="screen">
    <div id="status-bar">
        <div class="status-left">
            <span class="signal">●●●●</span>
            <span class="provider">MELON</span>
        </div>
        <div class="status-right">
            <span id="status-clock">--:--</span>
            <span class="battery">▮▮▮</span>
        </div>
    </div>

    <main class="wip-page">
        <div class="big-icon">🚧</div>
        <h1><?= htmlspecialchars($appName) ?></h1>
        <p>Esta app aún no tiene versión adaptada a móvil. Estamos trabajando en ello — vuelve pronto.</p>
        <a class="back-btn" href="../mobile.php">← Volver al menú</a>
    </main>
</div>

<script>
(function() {
    var clockEl = document.getElementById('status-clock');
    function pad(n){ return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var d = new Date();
        clockEl.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    tick(); setInterval(tick, 15000);
})();
</script>
</body>
</html>
