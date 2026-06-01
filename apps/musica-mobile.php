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
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c2b54">
    <title>Música — Scrapbook Melon</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/css/mobile.css">
    <style>
        /* ── Lista de playlists ── */
        .mu-list {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            /* Reserva espacio para el mini-player sticky abajo. */
            padding: 4px 0 96px;
        }
        .mu-playlist {
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .mu-pl-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            cursor: pointer;
            background: rgba(255,255,255,0.03);
        }
        .mu-pl-head:active { background: #1c4a82; }
        .mu-pl-icon {
            width: 40px; height: 40px;
            background: rgba(91,212,255,0.18);
            color: #5bd4ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .mu-pl-info { flex: 1; min-width: 0; }
        .mu-pl-name {
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mu-pl-meta {
            color: #9bc1ed;
            font-size: 11px;
            margin-top: 2px;
        }
        .mu-pl-arrow {
            font-size: 14px;
            color: #5bd4ff;
            transition: transform 0.2s;
        }
        .mu-playlist.open .mu-pl-arrow { transform: rotate(90deg); }

        /* ── Tracks colapsables ── */
        .mu-tracks {
            display: none;
            background: rgba(0,0,0,0.18);
        }
        .mu-playlist.open .mu-tracks { display: block; }
        .mu-track {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px 10px 32px;
            cursor: pointer;
            border-bottom: 1px dotted rgba(255,255,255,0.06);
        }
        .mu-track:active   { background: #1c4a82; }
        .mu-track.playing  { background: rgba(91,212,255,0.10); }
        .mu-track.playing .mu-track-title { color: #5bd4ff; }
        .mu-track-num {
            color: #6b8db5;
            font-size: 11px;
            width: 18px;
            text-align: right;
            flex-shrink: 0;
        }
        .mu-track-info { flex: 1; min-width: 0; }
        .mu-track-title {
            color: #fff;
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mu-track-artist {
            color: #9bc1ed;
            font-size: 11px;
            margin-top: 1px;
        }
        .mu-track-dur {
            color: #6b8db5;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }

        /* ── Mini reproductor sticky ── */
        .mu-player {
            position: fixed;
            left: 0; right: 0;
            bottom: 0;
            background: #0c2b54;
            border-top: 2px solid #5bd4ff;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.6);
            z-index: 50;
            transform: translateY(110%);
            transition: transform 0.25s ease;
        }
        .mu-player.visible { transform: translateY(0); }
        .mu-player-thumb {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            background: #1a3a6e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5bd4ff;
            font-size: 22px;
            flex-shrink: 0;
            overflow: hidden;
        }
        .mu-player-thumb img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .mu-player-info { flex: 1; min-width: 0; }
        .mu-player-title {
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mu-player-artist {
            color: #9bc1ed;
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mu-player-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mu-btn {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            border: none;
            border-radius: 50%;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .mu-btn:active { transform: scale(0.92); }
        .mu-btn.primary {
            background: #5bd4ff;
            color: #061629;
            width: 44px; height: 44px;
            font-size: 18px;
        }

        /* iframe del reproductor YouTube (oculto: solo necesitamos el audio) */
        #yt-host {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            opacity: 0;
        }
    </style>
</head>
<body>
<div id="screen">

    <!-- Status bar con back -->
    <div id="status-bar">
        <div class="status-left">
            <a href="../mobile.php" style="color:#5bd4ff;text-decoration:none;font-weight:bold;">‹ Menú</a>
        </div>
        <div class="status-right">
            <span id="status-clock">--:--</span>
            <span class="battery">▮▮▮</span>
        </div>
    </div>

    <!-- Header -->
    <header id="mob-header">
        <div class="app-icon" style="width:48px;height:48px;background:rgba(91,212,255,0.18);">
            <span class="app-icon-emoji">🎵</span>
        </div>
        <div class="mob-title">
            <div class="mob-title-name">Música</div>
            <div class="mob-title-user"><?= htmlspecialchars($userLabel) ?> · <span id="mu-pl-count">—</span></div>
        </div>
    </header>

    <!-- Lista de playlists -->
    <main class="mu-list" id="mu-list">
        <div class="pf-loading" style="text-align:center;color:#5bd4ff;padding:40px;font-size:13px;">Cargando…</div>
    </main>

</div>

<!-- Mini reproductor sticky -->
<div class="mu-player" id="mu-player">
    <div class="mu-player-thumb" id="mu-thumb">🎵</div>
    <div class="mu-player-info">
        <div class="mu-player-title" id="mu-now-title">—</div>
        <div class="mu-player-artist" id="mu-now-artist">—</div>
    </div>
    <div class="mu-player-controls">
        <button class="mu-btn" id="mu-prev" type="button" aria-label="Anterior">⏮</button>
        <button class="mu-btn primary" id="mu-toggle" type="button" aria-label="Play/Pausa">▶</button>
        <button class="mu-btn" id="mu-next" type="button" aria-label="Siguiente">⏭</button>
    </div>
</div>

<!-- Iframe oculto donde vive el player de YouTube -->
<div id="yt-host"><div id="yt-iframe"></div></div>

<script>
/* ─── Reloj ─────────────────────────────────────────────────────── */
(function() {
    var clockEl = document.getElementById('status-clock');
    function pad(n){ return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var d = new Date();
        clockEl.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    tick(); setInterval(tick, 15000);
})();

/* ─── Estado ────────────────────────────────────────────────────── */
var API_MUSIC = '../assets/music/api.php';
var PLAYLISTS = [];        /* respuesta del API */
var QUEUE     = [];        /* tracks de la playlist en reproducción */
var CUR_IDX   = -1;        /* índice del track actual en QUEUE */
var YT_PLAYER = null;      /* instancia YT.Player (no llamarla YT — choca con window.YT) */
var YT_READY  = false;

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
                '<div class="pf-empty" style="text-align:center;color:#6b8db5;padding:60px 24px;">' +
                '<div class="big" style="font-size:48px;opacity:0.4;margin-bottom:12px;">⚠️</div>' +
                'Error cargando playlists</div>';
        });
}

function renderPlaylists() {
    var listEl = document.getElementById('mu-list');
    if (!PLAYLISTS.length) {
        listEl.innerHTML =
            '<div style="text-align:center;color:#6b8db5;padding:60px 24px;">' +
            '<div style="font-size:48px;opacity:0.4;margin-bottom:12px;">🎵</div>' +
            'No tienes playlists todavía</div>';
        return;
    }
    var html = '';
    PLAYLISTS.forEach(function(pl, i){
        var tracks = pl.tracks || [];
        var meta = tracks.length + ' canción' + (tracks.length === 1 ? '' : 'es');
        if (pl.sharedLabel) meta += ' · compartido por ' + esc(pl.sharedLabel);
        html += '<div class="mu-playlist" data-pl-idx="' + i + '">' +
                  '<div class="mu-pl-head">' +
                    '<div class="mu-pl-icon">🎵</div>' +
                    '<div class="mu-pl-info">' +
                      '<div class="mu-pl-name">' + esc(pl.name) + '</div>' +
                      '<div class="mu-pl-meta">' + meta + '</div>' +
                    '</div>' +
                    '<div class="mu-pl-arrow">›</div>' +
                  '</div>' +
                  '<div class="mu-tracks">';
        tracks.forEach(function(tr, ti){
            html += '<div class="mu-track" data-pl-idx="' + i + '" data-tr-idx="' + ti + '">' +
                      '<div class="mu-track-num">' + (ti + 1) + '</div>' +
                      '<div class="mu-track-info">' +
                        '<div class="mu-track-title">' + esc(tr.title || tr.videoId) + '</div>' +
                        (tr.artist ? '<div class="mu-track-artist">' + esc(tr.artist) + '</div>' : '') +
                      '</div>' +
                      '<div class="mu-track-dur">' + (tr.duration ? fmtDur(tr.duration) : '—') + '</div>' +
                    '</div>';
        });
        html += '</div></div>';
    });
    listEl.innerHTML = html;
}

/* ─── Click handlers (delegados) ───────────────────────────────── */
document.getElementById('mu-list').addEventListener('click', function(e){
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
function onYTState(e) {
    var btn = document.getElementById('mu-toggle');
    if (e.data === 1) btn.textContent = '⏸';
    else if (e.data === 2) btn.textContent = '▶';
    else if (e.data === 0) nextTrack();
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
    /* Muestra info en el mini-player. */
    document.getElementById('mu-now-title').textContent  = tr.title  || tr.videoId;
    document.getElementById('mu-now-artist').textContent = tr.artist || '';
    var thumb = document.getElementById('mu-thumb');
    thumb.innerHTML = '<img src="https://i.ytimg.com/vi/' + esc(tr.videoId) + '/default.jpg" alt="">';
    document.getElementById('mu-player').classList.add('visible');
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

/* ─── Bootstrap ─────────────────────────────────────────────────── */
loadPlaylists();
</script>

</body>
</html>
