<?php
/* ──────────────────────────────────────────────────────────────────────
   TV COMPANION VIEW — now playing screen para Smart TVs.
   ──────────────────────────────────────────────────────────────────────
   Versión con CSS/JS legacy para Smart TVs viejos (Tizen 2017+, webOS
   3+, Android TV con Chrome 50+). Sin aspect-ratio, sin inset, sin
   clamp(), sin min(). Solo features con 8+ años de soporte estable.
   ────────────────────────────────────────────────────────────────────── */
/* Fuerza HTTP plano cuando se accede por IP LAN.
   Si la TV abre https://192.168.1.18/... acepta el cert self-signed
   de XAMPP para la página, pero el iframe interno se rompe porque cada
   nuevo request al endpoint también valida el cert (y muchos browsers
   de TV no extienden la excepción a subresources). Forzando HTTP
   evitamos todo TLS. Solo aplica si HOST es una IP privada — no rompe
   producción en dominios reales con cert válido. */
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $hostNoPort = preg_replace('/:\d+$/', '', $host);
    $isPrivateIp = (bool) preg_match(
        '/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.|127\.|169\.254\.)/',
        $hostNoPort
    );
    if ($isPrivateIp) {
        /* Limpia cualquier HSTS para que el redirect a HTTP se obedezca. */
        header('Strict-Transport-Security: max-age=0');
        header('Location: http://' . $host . $_SERVER['REQUEST_URI']);
        exit;
    }
}

session_start();
require_once __DIR__ . '/assets/config.php';
require_once __DIR__ . '/db.php';

/* Cierre de sesión de la TV: GET ?logout=1 (o el botón "Desconectar"
   abajo). Borra cookie persistente y limpia la sesión. */
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
    header('Location: tv.php');
    exit;
}

/* ── Redención de código de emparejamiento ──
   Si la TV postea un código de 6 dígitos válido, creamos sesión y
   redirigimos a la propia tv.php (ya logueada). El código se invalida
   tras un solo uso. */
$linkError = '';
if (isset($_POST['code'])) {
    $code = preg_replace('/[^0-9]/', '', (string)$_POST['code']);
    if (strlen($code) === 6) {
        try {
            $stmt = $pdo->prepare(
                "SELECT user_key FROM tv_link_codes
                 WHERE code = ? AND created_at > NOW() - INTERVAL 5 MINUTE"
            );
            $stmt->execute([$code]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($loginUsers[$row['user_key']])) {
                $_SESSION['user'] = $row['user_key'];
                $pdo->prepare("DELETE FROM tv_link_codes WHERE code = ?")->execute([$code]);
                /* Cookie de sesión persistente 30 días — la TV no
                   tendrá que volver a emparejarse en un mes. */
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), [
                    'expires'  => time() + 30 * 86400,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                header('Location: tv.php');
                exit;
            }
        } catch (Throwable $e) { /* tabla no existe aún o error → mensaje genérico */ }
        $linkError = 'Código inválido o caducado';
    } else {
        $linkError = 'El código debe tener 6 dígitos';
    }
}

/* Sin sesión → form de emparejamiento, no rebote al login. */
$loggedIn = isset($_SESSION['user']) && isset($loginUsers[$_SESSION['user']]);
$userLabel = $loggedIn ? $loginUsers[$_SESSION['user']]['label'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $loggedIn ? '♪ TV — ' . htmlspecialchars($userLabel) : 'Conectar TV' ?></title>
    <link rel="icon" href="assets/img/mobile/icon.png" type="image/png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            width: 100%;
            height: 100%;
            background: #050505;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
            overflow: hidden;
        }

        /* IFrame de YouTube oculto detrás del fondo. La TV reproduce el
           audio aquí. El video se ve si quitas el .tv-bg, pero por
           defecto queda tapado por el fondo difuminado y el overlay. */
        .tv-player-wrap {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            overflow: hidden;
            background: #050505;
        }
        .tv-player-wrap iframe,
        .tv-player-wrap #tv-player {
            position: absolute;
            top: -10%; left: -10%;
            width: 120%; height: 120%;
            border: 0;
        }

        /* Fondo difuminado con el cover del track. */
        .tv-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #050505;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            -webkit-filter: blur(60px) saturate(1.5) brightness(0.4);
            filter: blur(60px) saturate(1.5) brightness(0.4);
            z-index: 1;
        }
        .tv-bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.45);
            z-index: 2;
        }

        /* Layout principal. */
        .tv-stage {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 3;
            display: none;
            text-align: center;
            padding: 5%;
        }
        .tv-stage.active { display: block; }

        /* Cover circular del track. Fijo a 40vmin (40% del lado más corto)
           para que funcione en cualquier ratio sin aspect-ratio. */
        .tv-cover {
            display: inline-block;
            width: 40vmin;
            height: 40vmin;
            background-color: #111;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 50%;
            margin: 5vh auto 4vh;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7);
            position: relative;
            -webkit-animation: tv-spin 18s linear infinite;
                    animation: tv-spin 18s linear infinite;
            -webkit-animation-play-state: paused;
                    animation-play-state: paused;
        }
        .tv-cover.playing {
            -webkit-animation-play-state: running;
                    animation-play-state: running;
        }
        @-webkit-keyframes tv-spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }
        @keyframes tv-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Agujero del vinilo (centro). */
        .tv-cover-hole {
            position: absolute;
            top: 43%; left: 43%;
            width: 14%; height: 14%;
            background: #000;
            border-radius: 50%;
            box-shadow: 0 0 0 5px rgba(0,0,0,0.7);
        }

        /* Textos. */
        .tv-now {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 14px;
        }
        .tv-title {
            font-size: 36px;
            font-weight: bold;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 14px;
            padding: 0 5%;
        }
        .tv-artist {
            font-size: 22px;
            color: rgba(255,255,255,0.78);
            margin-bottom: 10px;
        }
        .tv-playlist {
            font-size: 15px;
            color: rgba(255,255,255,0.55);
            margin-bottom: 24px;
        }

        /* Barra de progreso. */
        .tv-progress {
            width: 70%;
            margin: 0 auto;
        }
        .tv-progress-bar {
            width: 100%;
            height: 5px;
            background: rgba(255,255,255,0.18);
            border-radius: 3px;
            overflow: hidden;
        }
        .tv-progress-fill {
            height: 100%;
            background: #fff;
            width: 0%;
        }
        .tv-progress-times {
            margin-top: 6px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
        }
        .tv-progress-times .right { float: right; }

        /* Estado idle (esperando). */
        .tv-idle {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #000;
            z-index: 4;
            text-align: center;
        }
        .tv-idle.hidden { display: none; }
        .tv-idle-inner {
            position: absolute;
            top: 50%; left: 50%;
            -webkit-transform: translate(-50%, -50%);
                    transform: translate(-50%, -50%);
            width: 80%;
        }
        .tv-idle-icon {
            font-size: 100px;
            color: rgba(255,255,255,0.25);
            margin-bottom: 20px;
            -webkit-animation: tv-pulse 3s ease-in-out infinite;
                    animation: tv-pulse 3s ease-in-out infinite;
        }
        .tv-idle-title {
            font-size: 24px;
            color: rgba(255,255,255,0.7);
            font-weight: normal;
            margin-bottom: 12px;
        }
        .tv-idle-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.4);
        }
        @-webkit-keyframes tv-pulse {
            0%, 100% { opacity: 0.25; }
            50% { opacity: 0.5; }
        }
        @keyframes tv-pulse {
            0%, 100% { opacity: 0.25; }
            50% { opacity: 0.5; }
        }

        /* Badge del usuario en esquina. */
        .tv-user {
            position: fixed;
            top: 18px; right: 24px;
            z-index: 5;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .tv-user-dot {
            display: inline-block;
            width: 9px; height: 9px;
            background: #ff3b30;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
        .tv-user.connected .tv-user-dot { background: #34c759; }

        .tv-logout {
            position: fixed;
            top: 18px; left: 22px;
            z-index: 5;
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.72);
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.18);
            padding: 9px 18px 9px 14px;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.35);
            -webkit-transition: color .2s, background .2s, border-color .2s, -webkit-transform .2s;
                    transition: color .2s, background .2s, border-color .2s, transform .2s;
        }
        .tv-logout-icon {
            display: inline-block;
            width: 14px; height: 14px;
            margin-right: 8px;
            vertical-align: -2px;
            border: 1.5px solid currentColor;
            border-radius: 50%;
            position: relative;
        }
        .tv-logout-icon::after {
            content: '';
            position: absolute;
            top: -3px; left: 50%;
            width: 1.5px; height: 7px;
            margin-left: -0.75px;
            background: currentColor;
        }
        .tv-logout:hover, .tv-logout:focus {
            color: #fff;
            background: rgba(255,68,68,0.22);
            border-color: rgba(255,68,68,0.7);
            outline: none;
            -webkit-transform: translateY(-1px);
                    transform: translateY(-1px);
        }

        /* Pantallas pequeñas (móvil/tablet vertical): textos un poco más
           pequeños. */
        @media (max-width: 600px) {
            .tv-title { font-size: 22px; }
            .tv-artist { font-size: 16px; }
            .tv-cover { width: 60vmin; height: 60vmin; }
        }

        /* ── Form de pairing (cuando no hay sesión) ── */
        .tv-pair {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #0a0a0a;
            display: block;
            text-align: center;
        }
        .tv-pair-inner {
            position: absolute;
            top: 50%; left: 50%;
            -webkit-transform: translate(-50%, -50%);
                    transform: translate(-50%, -50%);
            width: 90%;
            max-width: 540px;
        }
        .tv-pair-icon {
            font-size: 80px;
            color: rgba(255,255,255,0.4);
            margin-bottom: 18px;
        }
        .tv-pair-title {
            font-size: 32px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 12px;
        }
        .tv-pair-sub {
            font-size: 16px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 32px;
            line-height: 1.5;
        }
        .tv-pair-input {
            width: 100%;
            max-width: 360px;
            padding: 22px 16px;
            font-size: 48px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 18px;
            background: #1c1c1c;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            font-family: Consolas, Monaco, monospace;
        }
        .tv-pair-input:focus {
            outline: none;
            border-color: #34c759;
        }
        .tv-pair-btn {
            display: block;
            margin: 24px auto 0;
            padding: 16px 48px;
            font-size: 18px;
            font-weight: bold;
            background: #34c759;
            color: #fff;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .tv-pair-btn:hover, .tv-pair-btn:focus { background: #2ea84e; outline: none; }
        .tv-pair-err {
            color: #ff5546;
            font-size: 16px;
            margin-top: 14px;
            min-height: 22px;
        }
        .tv-pair-hint {
            font-size: 13px;
            color: rgba(255,255,255,0.4);
            margin-top: 26px;
            line-height: 1.55;
        }

        /* Aviso cuando YouTube bloquea la reproducción en embed (errores
           101/150). El audio se queda en el móvil, la TV sigue mostrando
           carátula y título pero indica que no puede reproducirlo. */
        .tv-err-banner {
            display: none;
            margin: 0 auto 14px;
            max-width: 80%;
            background: rgba(255,68,68,0.22);
            border: 1px solid rgba(255,68,68,0.7);
            color: #ff8080;
            font-size: 14px;
            padding: 10px 16px;
            border-radius: 6px;
            letter-spacing: 1px;
        }
        .tv-err-banner.visible { display: block; }
    </style>
</head>
<body>

<?php if (!$loggedIn): ?>
<!-- Form de emparejamiento: la TV recibe el código generado en el móvil. -->
<div class="tv-pair">
    <form class="tv-pair-inner" method="POST" autocomplete="off">
        <div class="tv-pair-icon">📺</div>
        <div class="tv-pair-title">Conecta tu TV</div>
        <div class="tv-pair-sub">
            En el reproductor del móvil → botón 📺<br>
            Introduce abajo el código de 6 dígitos:
        </div>
        <input class="tv-pair-input" type="tel" name="code"
               inputmode="numeric" pattern="[0-9]*" maxlength="6"
               autofocus required>
        <div class="tv-pair-err"><?= htmlspecialchars($linkError) ?></div>
        <button class="tv-pair-btn" type="submit">Conectar</button>
        <div class="tv-pair-hint">
            El código caduca en 5 minutos. Una vez conectado, esta TV
            queda recordada durante 30 días.
        </div>
    </form>
</div>
<?php else: ?>

<!-- IFrame de YouTube — emite el audio por los altavoces de la TV.
     Detrás del fondo difuminado (z-index 0). YT.Player lo reemplaza
     por un iframe real cuando la API termina de cargar. -->
<div class="tv-player-wrap"><div id="tv-player"></div></div>

<div class="tv-bg" id="tv-bg"></div>
<div class="tv-bg-overlay"></div>
<div class="tv-user" id="tv-user"><span class="tv-user-dot"></span><?= htmlspecialchars($userLabel) ?></div>
<a class="tv-logout" href="tv.php?logout=1"><span class="tv-logout-icon"></span>Desconectar</a>

<!-- Vista idle. -->
<div class="tv-idle" id="tv-idle">
    <div class="tv-idle-inner">
        <div class="tv-idle-icon">♪</div>
        <div class="tv-idle-title">Esperando música</div>
        <div class="tv-idle-sub">Abre el reproductor en el móvil</div>
    </div>
</div>

<!-- Vista activa. -->
<div class="tv-stage" id="tv-stage">
    <div class="tv-cover" id="tv-cover">
        <div class="tv-cover-hole"></div>
    </div>
    <div class="tv-err-banner" id="tv-err-banner"></div>
    <div class="tv-now">Reproduciendo</div>
    <div class="tv-title"   id="tv-title">—</div>
    <div class="tv-artist"  id="tv-artist">—</div>
    <div class="tv-playlist" id="tv-playlist"></div>
    <div class="tv-progress">
        <div class="tv-progress-bar"><div class="tv-progress-fill" id="tv-progress-fill"></div></div>
        <div class="tv-progress-times">
            <span id="tv-time-cur">0:00</span>
            <span class="right" id="tv-time-tot">0:00</span>
        </div>
    </div>
</div>

<script>
/* Estado local. La posición se INTERPOLA entre polls para que la barra
   se mueva suave aunque polleamos cada segundo. Compatible IE11+. */
var STATE = {
    track:     null,
    isPlaying: false,
    serverPos: 0,
    serverTs:  0,
    duration:  0
};

var bgEl       = document.getElementById('tv-bg');
var coverEl    = document.getElementById('tv-cover');
var titleEl    = document.getElementById('tv-title');
var artistEl   = document.getElementById('tv-artist');
var plEl       = document.getElementById('tv-playlist');
var fillEl     = document.getElementById('tv-progress-fill');
var curEl      = document.getElementById('tv-time-cur');
var totEl      = document.getElementById('tv-time-tot');
var idleEl     = document.getElementById('tv-idle');
var stageEl    = document.getElementById('tv-stage');
var userEl     = document.getElementById('tv-user');
var errBanner  = document.getElementById('tv-err-banner');

function showPlaybackError(code) {
    if (!errBanner) return;
    var msg;
    switch (code) {
        case 101: case 150: msg = '🎵 El audio sigue en el móvil — esta canción no se puede reproducir en TV'; break;
        case 100:           msg = 'Video no encontrado o privado'; break;
        case 2:             msg = 'ID de video inválido'; break;
        case 5:             msg = 'Error del reproductor HTML5'; break;
        default:            msg = 'Error de reproducción (código ' + code + ')';
    }
    errBanner.innerHTML = msg;
    errBanner.className = 'tv-err-banner visible';
}
function hidePlaybackError() {
    if (errBanner) errBanner.className = 'tv-err-banner';
}

function fmt(sec) {
    sec = Math.max(0, Math.floor(sec || 0));
    var m = Math.floor(sec / 60), s = sec % 60;
    return m + ':' + (s < 10 ? '0' : '') + s;
}

function thumbUrl(videoId) {
    return videoId ? 'https://i.ytimg.com/vi/' + videoId + '/maxresdefault.jpg' : '';
}

function render() {
    var tr = STATE.track;
    if (!tr || !tr.videoId) {
        idleEl.className = 'tv-idle';
        stageEl.className = 'tv-stage';
        userEl.className  = 'tv-user';
        return;
    }
    idleEl.className  = 'tv-idle hidden';
    stageEl.className = 'tv-stage active';
    userEl.className  = 'tv-user connected';

    var url = thumbUrl(tr.videoId);
    var bgVal = 'url("' + url + '")';
    if (coverEl.style.backgroundImage !== bgVal) {
        coverEl.style.backgroundImage = bgVal;
        bgEl.style.backgroundImage    = bgVal;
    }
    titleEl.innerHTML  = escapeHtml(tr.title  || '—');
    artistEl.innerHTML = escapeHtml(tr.artist || '');
    plEl.innerHTML     = tr.plName ? ('📋 ' + escapeHtml(tr.plName)) : '';

    if (STATE.isPlaying) {
        coverEl.className = 'tv-cover playing';
    } else {
        coverEl.className = 'tv-cover';
    }
}

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function tickProgress() {
    if (!STATE.track || !STATE.duration) {
        fillEl.style.width = '0%';
        curEl.innerHTML = '0:00';
        totEl.innerHTML = '0:00';
        return;
    }
    /* Fuente de verdad para la barra:
       - Embed bloqueado → interpolamos desde la posición del móvil (el
         player de la TV está parado, su getCurrentTime daría 0).
       - Caso normal  → leemos del iframe propio de la TV (es el audio
         que el usuario realmente está oyendo). Elimina los saltos que
         se producían al refrescar serverPos cada 5s.
       - Fallback     → interpolación si el player aún no cargó o falla. */
    var pos = -1;
    if (!EMBED_BLOCKED && ytPlayer && ytPlayer.getCurrentTime) {
        try {
            var t = ytPlayer.getCurrentTime();
            if (typeof t === 'number' && isFinite(t) && t >= 0) pos = t;
        } catch (_) {}
    }
    if (pos < 0) {
        var elapsed = (now() - STATE.serverTs) / 1000;
        pos = STATE.isPlaying ? STATE.serverPos + elapsed : STATE.serverPos;
    }
    if (pos > STATE.duration) pos = STATE.duration;
    var pct = STATE.duration > 0 ? (pos / STATE.duration) * 100 : 0;
    fillEl.style.width = pct + '%';
    curEl.innerHTML = fmt(pos);
    totEl.innerHTML = fmt(STATE.duration);
}

function now() {
    /* Date.now() existe desde IE9; algunos browsers antiguos de TV
       pueden no tenerlo → fallback. */
    return Date.now ? Date.now() : (new Date()).getTime();
}

/* XMLHttpRequest en vez de fetch() — fetch puede no estar en Tizen/
   webOS de antes de 2018. Cache-buster (?_=ts) porque algunos browsers
   antiguos de Smart TV ignoran Cache-Control y sirven la primera
   respuesta para siempre. Sin el buster, el polling parece "congelado"
   tras el primer hit. */
/* Polling vía <iframe> oculto reusable — XHR y <script src> dinámicos
   están rotos en este navegador de TV tras el primer hit. El truco
   universal: un solo <iframe> que cambia de src en cada poll. El
   endpoint responde HTML con el JSON en <pre id="d">. En el padre,
   iframe.onload lee el texto del <pre>, lo parsea y aplica el estado.
   Sin scripts dentro del iframe (no corren en navegaciones dinámicas
   en algunos browsers). Polling SERIAL: el siguiente poll se programa
   al terminar el actual. */
var POLL_BUSY   = false;
var POLL_DELAY  = 750;
var POLL_FRAME  = null;
var POLL_TIMER  = null;
var POLL_DONE   = false;

function ensurePollFrame() {
    if (POLL_FRAME) return POLL_FRAME;
    POLL_FRAME = document.createElement('iframe');
    POLL_FRAME.style.cssText = 'position:absolute;width:1px;height:1px;border:0;left:-9999px;top:-9999px;visibility:hidden';
    POLL_FRAME.setAttribute('aria-hidden', 'true');
    /* onload se dispara en CADA cambio de src, también en navegaciones
       sucesivas. Leemos el JSON desde el <pre id="d"> del documento
       cargado y se lo pasamos a applyNowPlaying. */
    POLL_FRAME.onload = function() {
        if (POLL_DONE) return;
        try {
            var doc = POLL_FRAME.contentDocument
                   || (POLL_FRAME.contentWindow && POLL_FRAME.contentWindow.document);
            if (!doc) { POLL_DONE = true; POLL_BUSY = false; schedulePoll(); return; }
            var pre = doc.getElementById ? doc.getElementById('d') : null;
            var raw = pre
                ? (pre.textContent || pre.innerText || '')
                : (doc.body ? (doc.body.textContent || doc.body.innerText || '') : '');
            raw = (raw || '').replace(/^\s+|\s+$/g, '');
            if (!raw) { POLL_DONE = true; POLL_BUSY = false; schedulePoll(); return; }
            applyNowPlaying(JSON.parse(raw));
        } catch (e) {
            POLL_DONE = true; POLL_BUSY = false; schedulePoll();
        }
    };
    document.body.appendChild(POLL_FRAME);
    return POLL_FRAME;
}

function applyNowPlaying(d) {
    if (POLL_DONE) return;
    POLL_DONE = true;
    if (POLL_TIMER) { clearTimeout(POLL_TIMER); POLL_TIMER = null; }
    POLL_BUSY = false;

    if (!d || !d.ok) { schedulePoll(); return; }

    STATE.track     = d.track || null;
    STATE.isPlaying = !!d.isPlaying;
    if (d.track) {
        var basePos = (typeof d.track.position === 'number') ? d.track.position : 0;
        /* `age` = segundos transcurridos en el servidor desde el último
           save-now-playing. Si el móvil está reproduciendo, su posición
           REAL ahora es position + age (más la latencia red→TV, no
           estimable sin clock sync). Sin este ajuste el TV interpolaría
           desde una snapshot atrasada → drift estable de ~2-3s + cualquier
           lag adicional acumulado. */
        var age = (typeof d.age === 'number') ? d.age : 0;
        STATE.serverPos = STATE.isPlaying ? (basePos + age) : basePos;
        STATE.duration  = (typeof d.track.duration === 'number') ? d.track.duration : 0;
        STATE.serverTs  = now();
    }
    render();
    schedulePoll();
}

function fetchNowPlaying() {
    if (POLL_BUSY) { schedulePoll(); return; }
    POLL_BUSY  = true;
    POLL_DONE  = false;

    var f = ensurePollFrame();
    /* Timeout de respaldo: si el iframe nunca dispara onload. */
    POLL_TIMER = setTimeout(function(){
        if (POLL_DONE) return;
        POLL_DONE = true;
        POLL_BUSY = false;
        schedulePoll();
    }, 4000);

    try {
        f.src = 'assets/music/api.php?action=get-now-playing&frame=1&_=' + now();
    } catch (e) {
        POLL_BUSY = false;
        schedulePoll();
    }
}

/* ── YouTube IFrame Player ──
   La TV reproduce el audio. Se crea cuando llega el primer track y se
   resincroniza con el servidor: cambia videoId, play/pause según
   isPlaying, seek si difiere >3s del tiempo esperado. */
var ytReady = false;
var ytPlayer = null;
var currentVideoId = null;
var YT_UNSTARTED = -1, YT_ENDED = 0, YT_PLAYING = 1, YT_PAUSED = 2, YT_BUFFERING = 3, YT_CUED = 5;
/* Throttle del seek: cada seek provoca un buffer + corte de audio. Solo
   re-sincronizamos cuando ha pasado >5s desde el último seek/load y la
   diferencia con el servidor es muy grande (>8s). Drift pequeño se
   tolera para no cortar el audio constantemente. */
var LAST_SEEK_TS = 0;
/* Detector de "atascado": cuando el servidor pide isPlaying pero el
   player no entra en PLAYING. STUCK_SINCE es el primer instante en
   que detectamos el atasco — si persiste, escalamos a forzar play
   → mute-trick → recargar video → recrear player. */
var STUCK_SINCE = 0;
/* Tracking de buffering. Cada entrada en BUFFERING marca el inicio;
   al salir contamos el tiempo total perdido. Después de un buffer
   largo (>2s) forzamos un resync agresivo porque sabemos que el TV
   ha caído atrás. */
var BUFFER_STARTED = 0;
var POST_BUFFER_RESYNC = false;
/* Embed bloqueado por el autor (errores 101/150). En este caso la TV
   se queda como "now-playing visual": portada, título, vinilo girando
   y barra de progreso desde la posición del móvil. Sin reintentos. */
var EMBED_BLOCKED = false;

/* Workaround de la autoplay policy del navegador de TV: los browsers
   bloquean playVideo() programático sin gesto de usuario, pero
   permiten autoplay si el audio está muteado. Truco: mute → play →
   unmute. La transición es imperceptible (~100ms muteado). */
function forcePlayWithMuteTrick() {
    if (!ytPlayer) return;
    try {
        ytPlayer.mute();
        ytPlayer.playVideo();
        setTimeout(function(){
            if (!ytPlayer) return;
            try { ytPlayer.unMute(); } catch (_) {}
            try { ytPlayer.setVolume(100); } catch (_) {}
        }, 200);
    } catch (_) {}
}

window.onYouTubeIframeAPIReady = function() {
    ytReady = true;
    syncPlayer();
};

function createPlayer() {
    if (ytPlayer || !STATE.track || !STATE.track.videoId) return;
    currentVideoId = STATE.track.videoId;
    var startSec = Math.max(0, Math.floor(STATE.serverPos || 0));
    LAST_SEEK_TS = now();
    try {
        /* Config minimalista igual que mobile.php. Algunos videos
           bloquean específicamente el "embed con autoplay=1" pero
           aceptan embed manual (autoplay=0 + playVideo()). Quitamos
           también controls/rel/etc. — ninguno afecta a la validación
           del embed, pero menos params = menos diferencias respecto a
           la configuración que SÍ funciona en el móvil. */
        ytPlayer = new YT.Player('tv-player', {
            videoId: currentVideoId,
            playerVars: {
                playsinline: 1,
                autoplay:    0,
                start:       startSec
            },
            events: {
                onReady: function(e) {
                    if (STATE.isPlaying) { try { e.target.playVideo(); } catch (_) {} }
                    else { try { e.target.pauseVideo(); } catch (_) {} }
                },
                /* El autoplay tras loadVideoById a veces se queda en
                   CUED o UNSTARTED en vez de pasar a PLAYING. Forzamos
                   play cuando el servidor dice isPlaying. */
                onStateChange: function(e) {
                    if (e.data === YT_PLAYING || e.data === YT_BUFFERING) {
                        STUCK_SINCE = 0; /* salió del atasco */
                    }
                    /* Marca inicio/fin de buffering — buffer largo →
                       resync agresivo al volver a PLAYING. */
                    if (e.data === YT_BUFFERING) {
                        if (!BUFFER_STARTED) BUFFER_STARTED = now();
                    } else if (e.data === YT_PLAYING && BUFFER_STARTED) {
                        var buffered = now() - BUFFER_STARTED;
                        BUFFER_STARTED = 0;
                        /* Resync agresivo SOLO si el buffer fue largo y NO
                           lo provocó nuestro propio seek reciente. Los
                           buffers tras un seek son esperados; tratarlos
                           como "atraso" disparaba otro seek → buffer →
                           seek… = micro-cortes en bucle. */
                        if (buffered > 1500 && (now() - LAST_SEEK_TS) > 2500) {
                            POST_BUFFER_RESYNC = true;
                        }
                    }
                    if (!STATE.isPlaying) return;
                    if (e.data === YT_CUED || e.data === YT_UNSTARTED) {
                        setTimeout(function(){
                            try { e.target.playVideo(); } catch (_) {}
                        }, 120);
                    }
                },
                /* Códigos: 2 = videoId inválido, 5 = HTML5 error,
                   100 = no encontrado/privado, 101/150 = embed bloqueado.
                   Para embed bloqueado mostramos un banner — no hay
                   reintento posible. Para el resto reintentamos load. */
                onError: function(e) {
                    var code = e && e.data;
                    showPlaybackError(code);
                    if (code === 101 || code === 150) {
                        /* Embed bloqueado por el autor → modo visual:
                           paramos el player, dejamos portada+vinilo+
                           barra (interpolada desde el móvil). */
                        EMBED_BLOCKED = true;
                        try { ytPlayer.stopVideo(); } catch (_) {}
                        return;
                    }
                    if (code === 100 || code === 2) {
                        return; /* otros errores no recuperables */
                    }
                    setTimeout(function(){
                        if (!ytPlayer || !STATE.track || !STATE.track.videoId) return;
                        try {
                            ytPlayer.cueVideoById({
                                videoId: STATE.track.videoId,
                                startSeconds: Math.max(0, Math.floor(STATE.serverPos || 0))
                            });
                            setTimeout(function(){
                                if (ytPlayer) { try { ytPlayer.playVideo(); } catch (_) {} }
                            }, 300);
                        } catch (_) {}
                    }, 500);
                }
            }
        });
    } catch (_) {}
}

function syncPlayer() {
    if (!ytReady) return;
    var tr = STATE.track;
    if (!tr || !tr.videoId) {
        if (ytPlayer && currentVideoId) {
            try { ytPlayer.stopVideo(); } catch (_) {}
            currentVideoId = null;
        }
        return;
    }
    if (!ytPlayer) { createPlayer(); return; }

    /* Cambió la pista → cargar la nueva.
       Usamos cueVideoById + playVideo() en vez de loadVideoById. El
       móvil opera así y muchos videos rechazan loadVideoById (que
       implica autoplay) pero aceptan cue+play manual. */
    if (tr.videoId !== currentVideoId) {
        currentVideoId = tr.videoId;
        hidePlaybackError();
        EMBED_BLOCKED = false;
        var startSec = Math.max(0, Math.floor(STATE.serverPos || 0));
        try {
            ytPlayer.cueVideoById({ videoId: currentVideoId, startSeconds: startSec });
        } catch (_) {}
        LAST_SEEK_TS = now();
        if (STATE.isPlaying) {
            /* playVideo() a los 300ms — dar tiempo a que cueVideoById
               registre el videoId en el player. */
            setTimeout(function(){
                if (!ytPlayer || currentVideoId !== tr.videoId) return;
                try { ytPlayer.playVideo(); } catch (_) {}
            }, 300);
        }
        return;
    }

    /* Si la pista actual tiene embed bloqueado, no insistimos —
       seguimos en modo visual hasta que el móvil pase a la siguiente. */
    if (EMBED_BLOCKED) return;

    var ps = -1;
    try { ps = ytPlayer.getPlayerState(); } catch (_) {}

    /* Posición esperada del móvil AHORA = última snapshot + tiempo
       transcurrido. */
    var dur      = (typeof STATE.duration === 'number' && STATE.duration > 0) ? STATE.duration : 0;
    var elapsed  = (now() - STATE.serverTs) / 1000;
    var expected = STATE.serverPos + elapsed;

    /* CLAVE anti-bucle: si `expected` supera la duración, la canción ya
       debería haber terminado. Cuando el móvil deja de emitir (pantalla
       bloqueada, app en 2º plano → los timers JS se estrangulan) la
       snapshot se CONGELA y `expected` se dispara sin fin. Antes, al
       terminar el vídeo, la detección de atasco forzaba playVideo() que
       REBOBINA desde 0 → se reproducía un tramo en bucle y nunca se
       cambiaba de pista. Ahora, si se pasó del final: dejamos el vídeo
       quieto (pausado) y esperamos a que llegue la siguiente pista
       (cambio de videoId en el poll). Nada de replays ni seeks. */
    if (dur > 0 && expected >= dur - 0.5) {
        if (ps === YT_PLAYING) { try { ytPlayer.pauseVideo(); } catch (_) {} }
        STUCK_SINCE = 0;
        return;
    }

    /* Misma pista: play/pause son baratos, los hacemos siempre. */
    if (STATE.isPlaying && ps !== YT_PLAYING && ps !== YT_BUFFERING) {
        try { ytPlayer.playVideo(); } catch (_) {}
    } else if (!STATE.isPlaying && ps === YT_PLAYING) {
        try { ytPlayer.pauseVideo(); } catch (_) {}
    }

    /* Detección de atasco: el servidor pide play pero el player no
       arranca. Escalamos cada vez que pasamos por syncPlayer:
         <1s   → solo intentamos playVideo (lo de arriba).
         1-4s  → mute-trick (bypass del autoplay policy).
         4-8s  → recargamos el video desde su posición.
         >8s   → recreamos el player desde cero (último recurso).  */
    if (STATE.isPlaying && ps !== YT_PLAYING && ps !== YT_BUFFERING) {
        if (!STUCK_SINCE) STUCK_SINCE = now();
        var stuckFor = now() - STUCK_SINCE;
        if (stuckFor > 8000) {
            try { ytPlayer.destroy(); } catch (_) {}
            ytPlayer = null;
            currentVideoId = null;
            STUCK_SINCE = 0;
            LAST_SEEK_TS = 0;
            createPlayer();
            return;
        }
        if (stuckFor > 4000 && now() - LAST_SEEK_TS > 1500) {
            try {
                ytPlayer.cueVideoById({
                    videoId: currentVideoId,
                    startSeconds: Math.max(0, Math.floor(expected))
                });
                setTimeout(function(){
                    if (ytPlayer) { try { ytPlayer.playVideo(); } catch (_) {} }
                }, 300);
            } catch (_) {}
            LAST_SEEK_TS = now();
        } else if (stuckFor > 1000) {
            forcePlayWithMuteTrick();
        }
    } else if (ps === YT_PLAYING || ps === YT_BUFFERING) {
        STUCK_SINCE = 0;
    }

    /* Resync de posición SOLO si:
       1. Está reproduciendo (no tiene sentido seekear en pausa).
       2. El estado del player ya no está buffering (seekear en mitad
          de un buffer empeora el corte).
       3. Pasaron >3s desde el último seek/load (deja al buffer estabilizarse).
       4. La diferencia con el servidor supera el umbral (banda muerta de
          2s → ignora desfases pequeños y evita micro-cortes).
       Después de un buffer >1.5s (no provocado por nuestro propio seek),
       el TV se sabe atrasado: corregimos con umbral 1s. El target se
       limita a la duración para no seekear más allá del final. */
    if (!STATE.isPlaying) return;
    if (ps === YT_BUFFERING) return;

    var target = dur > 0 ? Math.min(expected, dur - 0.5) : expected;
    var actual = 0;
    try { actual = ytPlayer.getCurrentTime() || 0; } catch (_) {}
    var diff = Math.abs(actual - target);

    var threshold;
    if (POST_BUFFER_RESYNC) {
        threshold = 1;
        POST_BUFFER_RESYNC = false;
    } else {
        if (now() - LAST_SEEK_TS < 3000) return;
        threshold = 2;
    }

    if (diff > threshold) {
        try { ytPlayer.seekTo(target, true); } catch (_) {}
        LAST_SEEK_TS = now();
    }
}

/* Engancha syncPlayer al render solo para cambios DISCRETOS (pista
   distinta o cambio de estado). Para los seeks de drift confiamos en
   el setInterval(syncPlayer, 1500) — llamarlo cada poll provocaba
   demasiados seekTo, que cortan el audio. */
var _origRender = render;
var _lastSyncedVid     = null;
var _lastSyncedPlaying = null;
render = function() {
    _origRender();
    var tr = STATE.track;
    var vid = tr && tr.videoId ? tr.videoId : null;
    var pl  = !!STATE.isPlaying;
    if (vid !== _lastSyncedVid || pl !== _lastSyncedPlaying) {
        _lastSyncedVid     = vid;
        _lastSyncedPlaying = pl;
        syncPlayer();
    }
};

/* Carga la API de YouTube. El callback global onYouTubeIframeAPIReady
   se dispara cuando el script esté listo. */
(function loadYtApi() {
    var s = document.createElement('script');
    s.src = 'https://www.youtube.com/iframe_api';
    document.getElementsByTagName('head')[0].appendChild(s);
})();

/* Polling serial — el siguiente poll se programa al terminar el actual
   (ver schedulePoll), no con setInterval. Esto evita acumulación de
   scripts pendientes cuando el browser de la TV no permite más de N
   conexiones simultáneas. */
function schedulePoll() {
    setTimeout(fetchNowPlaying, POLL_DELAY);
}
fetchNowPlaying();
setInterval(tickProgress,  300);
setInterval(syncPlayer,    900);   /* antes 1500 — chequeo de drift más frecuente */
</script>

<?php endif; ?>

</body>
</html>
