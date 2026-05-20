<?php
if (!isset($desktopLabel)) { header('Location: index.php'); exit; }

session_start();
require_once __DIR__ . '/assets/config.php';

$desktopUserKey = null;
foreach ($loginUsers as $key => $user) {
    if ($user['label'] === $desktopLabel) { $desktopUserKey = $key; break; }
}

if (!isset($_SESSION['user']) || $_SESSION['user'] !== $desktopUserKey) {
    header('Location: index.php');
    exit;
}

$wallpaper = getUserWallpaper($desktopLabel);
$wallpaperStyle = $wallpaper ? "background-image:url('{$wallpaper}')" : '';

$youtubePlaylist = [];
$customFile = __DIR__ . '/assets/music/' . $desktopUserKey . '-custom.json';
if (file_exists($customFile)) {
    $custom = json_decode(file_get_contents($customFile), true);
    if (is_array($custom)) $youtubePlaylist = $custom;
} else {
    if ($desktopUserKey === 'user1') {
        require_once __DIR__ . '/assets/music/playlist.php';
    } elseif ($desktopUserKey === 'user2') {
        require_once __DIR__ . '/assets/music/angie-playlist.php';
    }
    $extraFile = __DIR__ . '/assets/music/' . $desktopUserKey . '-extra.json';
    if (file_exists($extraFile)) {
        $extra = json_decode(file_get_contents($extraFile), true);
        if (is_array($extra)) $youtubePlaylist = array_merge($youtubePlaylist, $extra);
    }
}
$hasPlayer = in_array($desktopUserKey, ['user1', 'user2']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($desktopLabel); ?> - Escritorio</title>
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/desktop.css">
    <style>#player-cover { image-rendering: pixelated; }</style>
</head>

<body class="<?php echo $desktopUserKey === 'user1' ? 'dark' : 'angie'; ?>"<?php echo $wallpaperStyle ? " style=\"{$wallpaperStyle}\"" : ''; ?>>

<div id="page-enter"></div>
<div id="desktop">
    <div class="desktop-icon" id="archive-icon">
        <div class="desktop-icon-img">📼</div>
        <span>MelonArchive</span>
    </div>
    <div class="desktop-icon" id="calendar-icon">
        <div class="desktop-icon-img">📅</div>
        <span>Calendario</span>
    </div>
</div>

<!-- ARCHIVE WINDOW -->
<div class="window" id="archive-window">
    <div class="ar-handle" data-dir="n"></div>
    <div class="ar-handle" data-dir="s"></div>
    <div class="ar-handle" data-dir="e"></div>
    <div class="ar-handle" data-dir="w"></div>
    <div class="ar-handle" data-dir="ne"></div>
    <div class="ar-handle" data-dir="nw"></div>
    <div class="ar-handle" data-dir="se"></div>
    <div class="ar-handle" data-dir="sw"></div>
    <div class="title-bar" id="archive-titlebar">
        <div class="title-bar-text">📼 MelonArchive</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="archive-close"></button>
        </div>
    </div>
    <div id="archive-toolbar">
        <button class="button" id="archive-back" disabled>◄ Atrás</button>
        <span id="archive-breadcrumb">Playlists</span>
    </div>
    <div class="window-body" id="archive-body">
        <div id="archive-content">
            <div id="archive-status"></div>
            <div id="archive-grid"></div>
        </div>
        <div id="archive-player">
            <iframe id="archive-iframe" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    </div>
</div>

<?php if ($hasPlayer): ?>
<div id="yt-player"></div>
<!-- REPRODUCTOR -->
<div class="window" id="music-player">
    <div class="title-bar" id="player-titlebar">
        <div class="title-bar-text" id="player-tb-text"><span id="player-pl-name">♪ Reproductor</span></div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="player-close"></button>
        </div>
    </div>
    <div class="window-body" id="player-body">
        <div id="player-content">
            <div id="player-main">
                <div id="player-cover-wrap">
                    <img id="player-cover" src="" alt="">
                </div>
                <div id="player-info">
                    <p id="player-title">Sin título</p>
                    <p id="player-artist">—</p>
                    <div id="player-addedby"></div>
                </div>
            </div>
            <input type="range" id="player-progress" min="0" max="100" value="0" step="0.1">
            <div id="player-time">
                <span id="player-current">0:00</span>
                <span id="player-duration">0:00</span>
            </div>
            <div id="player-controls">
                <button class="button" id="btn-prev">◄◄</button>
                <button class="button" id="btn-play">►</button>
                <button class="button" id="btn-next">►►</button>
            </div>
        </div>
        <button class="button" id="btn-edit-playlist" title="Editar playlist">✎</button>
        <div id="player-volume-wrap">
            <div id="volume-track-outer">
                <input type="range" id="player-volume" min="0" max="100" value="100" step="1" orient="vertical">
            </div>
            <span id="volume-icon">◄))</span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- PLAYLIST EDITOR -->
<div class="window" id="playlist-editor">
    <div class="title-bar" id="pl-titlebar">
        <div class="title-bar-text" id="pl-title-text">♪ Playlists</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="pl-close"></button>
        </div>
    </div>
    <div class="window-body" id="pl-body">
        <!-- HOME VIEW -->
        <div id="pl-home">
            <div id="pl-home-list"></div>
            <div id="pl-home-footer">
                <button class="button" id="pl-create">+ Crear playlist</button>
            </div>
        </div>
        <!-- EDITOR VIEW -->
        <div id="pl-editor-view">
            <div id="pl-name-row">
                <label for="pl-name-input">Nombre</label>
                <input type="text" id="pl-name-input" placeholder="Nombre de la playlist">
            </div>
            <div id="pl-list"></div>
            <div id="pl-footer">
                <button class="button" id="pl-add">+ Añadir</button>
                <div id="pl-more-wrap">
                    <button class="button" id="pl-more">. . .</button>
                    <div id="pl-more-menu" class="window">
                        <div class="pl-menu-item" id="pl-more-import">Importar de YouTube</div>
                        <div class="pl-menu-item" id="pl-more-spotify">Importar de Spotify</div>
                        <div class="pl-menu-item" id="pl-more-collab">Añadir colaborador</div>
                    </div>
                </div>
                <span style="flex:1"></span>
                <button class="button" id="pl-back">← Atrás</button>
                <button class="button" id="pl-save">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ADD TRACK DIALOG -->
<div class="window" id="add-track-dialog">
    <div class="title-bar">
        <div class="title-bar-text">+ Añadir canción</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="add-track-close"></button>
        </div>
    </div>
    <div class="window-body" id="add-track-body">
        <div class="field-row-stacked">
            <label for="add-yt-url">Enlace de YouTube o Spotify</label>
            <input type="text" id="add-yt-url" placeholder="https://youtube.com/watch?v=... o https://open.spotify.com/track/...">
            <div id="add-title-preview-wrap"><span id="add-title-preview"></span></div>
        </div>
        <div class="field-row-stacked">
            <label for="add-artist">Artista</label>
            <input type="text" id="add-artist" placeholder="Nombre del artista">
        </div>
        <p id="add-track-error"></p>
        <div class="field-row" id="add-track-actions">
            <button class="button" id="add-track-cancel">Cancelar</button>
            <button class="button" id="add-track-submit">Añadir</button>
        </div>
    </div>
</div>

<!-- CREATE PLAYLIST DIALOG -->
<div class="window" id="create-playlist-dialog">
    <div class="title-bar">
        <div class="title-bar-text">+ Nueva playlist</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="create-pl-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label for="create-pl-name">Nombre de la playlist</label>
            <input type="text" id="create-pl-name" placeholder="Mi playlist">
        </div>
        <div class="field-row" style="justify-content: flex-end; gap: 4px; margin-top: 8px;">
            <button class="button" id="create-pl-cancel">Cancelar</button>
            <button class="button" id="create-pl-submit">Crear</button>
        </div>
    </div>
</div>

<!-- IMPORT PLAYLIST DIALOG -->
<div class="window" id="import-playlist-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Importar playlist de YouTube</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="import-pl-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label for="import-pl-url">Enlace de playlist de YouTube</label>
            <input type="text" id="import-pl-url" placeholder="https://youtube.com/playlist?list=...">
        </div>
        <p id="import-pl-status"></p>
        <div class="field-row" style="justify-content: flex-end; gap: 4px;">
            <button class="button" id="import-pl-cancel">Cancelar</button>
            <button class="button" id="import-pl-submit">Importar</button>
        </div>
    </div>
</div>

<!-- COLLABORATOR DIALOG -->
<div class="window" id="collab-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Añadir colaborador</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="collab-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p style="margin:0 0 6px;font-size:11px;">Invitar a colaborar en "<span id="collab-pl-name"></span>":</p>
        <div id="collab-user-list"></div>
        <p id="collab-status"></p>
        <div class="field-row" style="justify-content:flex-end;margin-top:6px;">
            <button class="button" id="collab-cancel">Cerrar</button>
        </div>
    </div>
</div>

<!-- INVITE NOTIFICATION -->
<div id="invite-notification">
    <div class="window" style="margin:0;">
        <div class="title-bar">
            <div class="title-bar-text">Invitacion de playlist</div>
        </div>
        <div class="window-body" style="padding:6px 8px;">
            <p id="invite-msg"></p>
            <div class="field-row" style="justify-content:flex-end;gap:4px;">
                <button class="button" id="invite-reject">Rechazar</button>
                <button class="button" id="invite-accept">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- SPOTIFY IMPORT DIALOG -->
<div class="window" id="spotify-import-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Importar canciones</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="spotify-import-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p id="spotify-import-hint" style="margin:0 0 2px;">
            Exporta tu playlist desde exportify.net y sube el CSV.
        </p>
        <div class="field-row-stacked">
            <label for="spotify-file-name">Archivo CSV</label>
            <div class="field-row" style="gap:4px;">
                <input type="text" id="spotify-file-name" readonly placeholder="Sin archivo seleccionado" style="flex:1;min-width:0;cursor:default;">
                <button class="button" id="spotify-file-browse" style="min-width:70px;flex-shrink:0;margin-bottom:3px;">Examinar...</button>
            </div>
            <input type="file" id="spotify-import-file" accept=".csv,text/csv" style="display:none;">
        </div>
        <div id="spotify-progress-wrap" style="display:none;margin:6px 0 0;">
            <div class="progress-indicator segmented" style="height:18px;padding:2px;">
                <span id="spotify-progress-fill" class="progress-indicator-bar" style="width:0%;"></span>
            </div>
            <p id="spotify-progress-text" style="font-size:10px;margin:3px 0 0;text-align:center;"></p>
        </div>
        <p id="spotify-import-status"></p>
        <div class="field-row" style="justify-content: flex-end; gap: 4px; margin-top: 10px;">
            <button class="button" id="spotify-import-cancel">Cancelar</button>
            <button class="button" id="spotify-import-submit">Importar</button>
        </div>
    </div>
</div>

<!-- START MENU -->
<div id="start-menu">
    <div id="start-menu-inner">
        <div id="start-sidebar">MelonOS 98</div>
        <div id="start-menu-items">
            <div class="menu-sep"></div>
            <a class="menu-item" href="logout.php">Cerrar sesión...</a>
            <div class="menu-sep"></div>
        </div>
    </div>
</div>

<!-- TASKBAR -->
<div id="taskbar">
    <button id="start-btn" class="button">
        <span class="win-logo">
            <span style="background:#ff2020"></span>
            <span style="background:#20c820"></span>
            <span style="background:#2020f0"></span>
            <span style="background:#f0c800"></span>
        </span>
        Inicio
    </button>
    <div class="taskbar-sep"></div>
    <div id="taskbar-tasks"></div>
    <div id="system-tray">
        <span id="tray-clock">00:00</span>
    </div>
</div>

<script>
/* =========================
   ANIMACION ENTRADA
========================= */

const pageEnter = document.getElementById('page-enter');
pageEnter.addEventListener('animationend', () => pageEnter.remove());

/* =========================
   RELOJ
========================= */

function updateClock()
{
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('tray-clock').textContent = h + ':' + m;
}

updateClock();
setInterval(updateClock, 1000);

/* =========================
   START MENU
========================= */

const startBtn = document.getElementById('start-btn');
const startMenu = document.getElementById('start-menu');

startBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    const visible = startMenu.style.display === 'block';
    startMenu.style.display = visible ? 'none' : 'block';
    startBtn.classList.toggle('active', !visible);
});

document.addEventListener('click', function() {
    startMenu.style.display = 'none';
    startBtn.classList.remove('active');
});

/* =========================
   ANIMACION SALIDA
========================= */

document.querySelectorAll('a[href="logout.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.href;
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:#000;opacity:0;transition:opacity 0.4s ease;z-index:9999;pointer-events:none;';
        document.body.appendChild(overlay);
        setTimeout(() => { overlay.style.opacity = '1'; }, 10);
        setTimeout(() => { window.location.href = href; }, 450);
    });
});

/* =========================
   REPRODUCTOR
========================= */

<?php if ($hasPlayer): ?>
const playlist = <?php echo json_encode($youtubePlaylist); ?>;
var currentUserKey = '<?php echo $desktopUserKey; ?>';
var usersInfo = <?php
$_usersInfoArr = [];
foreach ($loginUsers as $_k => $_u) {
    $_usersInfoArr[$_k] = ['key' => $_k, 'label' => $_u['label'], 'img' => getUserImage($_u['label'])];
}
echo json_encode($_usersInfoArr);
?>;
var spotifyClientId   = '<?php echo SPOTIFY_CLIENT_ID; ?>';
let currentTrack      = 0;
let currentPlaylistId = null;
var currentPlaylistHasCollabs = false;
let ytPlayer = null;
let progressInterval  = null;
let stopTitleMarquee  = null;
let stopPlNameMarquee = null;
var refreshPlaylists  = null;

/* Spotify PKCE OAuth */
var spotifyPKCE = (function() {
    var REDIRECT_URI = location.origin + location.pathname;

    function rnd(n) {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var arr = crypto.getRandomValues(new Uint8Array(n));
        return Array.from(arr, function(b) { return chars[b % chars.length]; }).join('');
    }
    function b64url(buf) {
        return btoa(String.fromCharCode.apply(null, new Uint8Array(buf)))
            .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }
    function getToken() {
        try {
            var d = JSON.parse(localStorage.getItem('sp_token') || 'null');
            if (d && d.t && d.e > Date.now()) return d.t;
        } catch(e) {}
        return null;
    }
    function saveToken(data) {
        localStorage.setItem('sp_token', JSON.stringify({ t: data.access_token, e: Date.now() + data.expires_in * 1000 }));
    }

    /* Handle OAuth callback */
    var p = new URLSearchParams(location.search);
    if (p.get('state') === 'sp_import') {
        history.replaceState(null, '', location.pathname);
        if (p.get('error')) {
            window._spotifyAuthError = p.get('error');
        } else if (p.get('code')) {
            var code     = p.get('code');
            var verifier = localStorage.getItem('sp_verifier');
            if (verifier) {
                fetch('https://accounts.spotify.com/api/token', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ client_id: spotifyClientId, grant_type: 'authorization_code', code: code, redirect_uri: REDIRECT_URI, code_verifier: verifier }).toString()
                })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    localStorage.removeItem('sp_verifier');
                    if (d.access_token) {
                        saveToken(d);
                        window._spotifyPendingUrl = localStorage.getItem('sp_pending_url') || '';
                        localStorage.removeItem('sp_pending_url');
                        window._spotifyJustAuthed = true;
                    } else {
                        window._spotifyAuthError = d.error || 'token_error';
                    }
                })
                .catch(function(e) { window._spotifyAuthError = e.message; });
            }
        }
    }

    return {
        getToken: getToken,
        clearToken: function() { localStorage.removeItem('sp_token'); localStorage.removeItem('sp_verifier'); },
        authorize: function(pendingUrl) {
            var verifier = rnd(128);
            localStorage.setItem('sp_verifier', verifier);
            if (pendingUrl) localStorage.setItem('sp_pending_url', pendingUrl);
            crypto.subtle.digest('SHA-256', new TextEncoder().encode(verifier))
            .then(function(buf) {
                location.href = 'https://accounts.spotify.com/authorize?' + new URLSearchParams({
                    response_type: 'code', client_id: spotifyClientId, scope: 'playlist-read-public playlist-read-collaborative',
                    redirect_uri: REDIRECT_URI, code_challenge_method: 'S256', code_challenge: b64url(buf), state: 'sp_import'
                }).toString();
            });
        }
    };
})();

function updatePlayerTitle(name) {
    var el     = document.getElementById('player-pl-name');
    var parent = document.getElementById('player-tb-text');
    if (!el || !parent) return;
    el.textContent = '♪ ' + name;
    if (stopPlNameMarquee) { stopPlNameMarquee(); stopPlNameMarquee = null; }
    setTimeout(function() {
        stopPlNameMarquee = marqueeScroll(el, parent, 40, 1500);
    }, 50);
}

function marqueeScroll(el, parent, speed, pauseMs) {
    el.style.transform = 'translateX(0)';
    var pos = 0, last = null, pausing = true, fromRight = false, raf = null, timer = null;
    var W = el.offsetWidth, C = parent.clientWidth;
    if (W <= C) return function(){};
    function tick(ts) {
        if (!last) last = ts;
        var dt = Math.min(ts - last, 50);
        last = ts;
        pos -= speed * dt / 1000;
        if (pos < -W) {
            pos = C + (pos + W);
            fromRight = true;
        }
        if (fromRight && pos <= 0) {
            pos = 0;
            fromRight = false;
            el.style.transform = 'translateX(0)';
            last = null; pausing = true;
            timer = setTimeout(function() { pausing = false; raf = requestAnimationFrame(tick); }, pauseMs);
            return;
        }
        el.style.transform = 'translateX(' + pos + 'px)';
        raf = requestAnimationFrame(tick);
    }
    timer = setTimeout(function() { pausing = false; raf = requestAnimationFrame(tick); }, pauseMs);
    return function() {
        clearTimeout(timer);
        if (raf) cancelAnimationFrame(raf);
        el.style.transform = '';
    };
}

const playerWindow  = document.getElementById('music-player');
const playerCover   = document.getElementById('player-cover');
const playerTitle   = document.getElementById('player-title');
const playerArtist  = document.getElementById('player-artist');
const playerProg    = document.getElementById('player-progress');
const playerCurrent = document.getElementById('player-current');
const playerDur     = document.getElementById('player-duration');
const btnPlay       = document.getElementById('btn-play');
const volSlider     = document.getElementById('player-volume');

function pixelateImg(img, factor) {
    if (!img.naturalWidth || img.src.startsWith('data:')) return;
    const w = Math.max(1, Math.round(img.naturalWidth  * factor));
    const h = Math.max(1, Math.round(img.naturalHeight * factor));
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d');
    ctx.imageSmoothingEnabled = false;
    try {
        ctx.drawImage(img, 0, 0, w, h);
        img.src = c.toDataURL('image/png');
    } catch (e) {}
}

playerCover.addEventListener('load', function() { pixelateImg(playerCover, 0.25); });

function formatTime(s)
{
    const m = Math.floor(s / 60);
    return m + ':' + String(Math.floor(s % 60)).padStart(2, '0');
}

function updateTrackUI(index)
{
    if (!playlist.length) return;
    const track = playlist[index];
    playerTitle.textContent  = track.title;
    playerArtist.textContent = track.artist || '—';
    playerCover.crossOrigin = 'anonymous';
    playerCover.src = `https://img.youtube.com/vi/${track.videoId}/mqdefault.jpg`;

    var addedByEl = document.getElementById('player-addedby');
    addedByEl.innerHTML = '';
    if (currentPlaylistHasCollabs && track.addedBy) {
        var adderInfo = null;
        Object.keys(usersInfo).forEach(function(k) {
            if (usersInfo[k].label === track.addedBy) adderInfo = usersInfo[k];
        });
        if (adderInfo && adderInfo.img) {
            var wrap = document.createElement('div');
            wrap.className = 'player-addedby-wrap';
            wrap.title = adderInfo.label;
            var img = document.createElement('img');
            img.className = 'player-addedby-img';
            img.src = adderInfo.img;
            img.alt = adderInfo.label;
            wrap.appendChild(img);
            addedByEl.appendChild(wrap);
            addedByEl.style.display = 'flex';
        } else {
            addedByEl.style.display = 'none';
        }
    } else {
        addedByEl.style.display = 'none';
    }
    playerProg.value = 0;
    playerCurrent.textContent = '0:00';
    playerDur.textContent = '0:00';
    if (stopTitleMarquee) { stopTitleMarquee(); stopTitleMarquee = null; }
    setTimeout(function() {
        stopTitleMarquee = marqueeScroll(playerTitle, playerTitle.parentElement, 50, 1000);
    }, 50);

    try {
        var s = JSON.parse(localStorage.getItem('melonOS_player') || '{}');
        s.trackIndex = index;
        localStorage.setItem('melonOS_player', JSON.stringify(s));
    } catch(e) {}
}

function startProgress()
{
    clearInterval(progressInterval);
    progressInterval = setInterval(() => {
        if (!ytPlayer || !ytPlayer.getDuration) return;
        const dur = ytPlayer.getDuration();
        const cur = ytPlayer.getCurrentTime();
        if (dur) {
            playerProg.value = (cur / dur) * 100;
            playerCurrent.textContent = formatTime(cur);
            playerDur.textContent = formatTime(dur);
        }
    }, 500);
}

function onYouTubeIframeAPIReady()
{
    ytPlayer = new YT.Player('yt-player', {
        width: 1, height: 1,
        playerVars: { controls: 0, rel: 0, modestbranding: 1 },
        events: {
            onReady: function() {
                ytPlayer.setVolume(parseInt(volSlider.value));

                var saved = null;
                try { saved = JSON.parse(localStorage.getItem('melonOS_player')); } catch(e) {}

                function restoreDefault() {
                    if (playlist.length) { updateTrackUI(0); ytPlayer.cueVideoById(playlist[0].videoId); }
                }

                if (saved && saved.playlistId) {
                    fetch('assets/music/get-playlists.php')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!Array.isArray(data)) { restoreDefault(); return; }
                        var found = null;
                        for (var i = 0; i < data.length; i++) {
                            if (data[i].id === saved.playlistId) { found = data[i]; break; }
                        }
                        if (!found || !found.tracks.length) { restoreDefault(); return; }
                        currentPlaylistId = found.id;
                        playlist.length = 0;
                        found.tracks.forEach(function(t) { playlist.push(t); });
                        var idx = (saved.trackIndex >= 0 && saved.trackIndex < playlist.length) ? saved.trackIndex : 0;
                        currentTrack = idx;
                        updateTrackUI(idx);
                        updatePlayerTitle(found.name);
                        ytPlayer.cueVideoById(playlist[idx].videoId);
                    })
                    .catch(restoreDefault);
                } else {
                    restoreDefault();
                }
            },
            onStateChange: (e) => {
                if (e.data === YT.PlayerState.PLAYING) {
                    btnPlay.textContent = '⏸';
                    startProgress();
                } else if (e.data === YT.PlayerState.PAUSED || e.data === YT.PlayerState.ENDED) {
                    btnPlay.textContent = '►';
                    clearInterval(progressInterval);
                }
                if (e.data === YT.PlayerState.ENDED) {
                    currentTrack = (currentTrack + 1) % playlist.length;
                    updateTrackUI(currentTrack);
                    ytPlayer.loadVideoById(playlist[currentTrack].videoId);
                }
            }
        }
    });
}

function switchTrack(index, keepPlaying)
{
    currentTrack = index;
    updateTrackUI(index);
    if (!ytPlayer) return;
    if (keepPlaying) {
        ytPlayer.loadVideoById(playlist[index].videoId);
    } else {
        ytPlayer.cueVideoById(playlist[index].videoId);
    }
}

btnPlay.addEventListener('click', () => {
    if (!ytPlayer || !playlist.length) return;
    const state = ytPlayer.getPlayerState();
    if (state === YT.PlayerState.PLAYING) {
        ytPlayer.pauseVideo();
    } else {
        ytPlayer.playVideo();
    }
});

document.getElementById('btn-prev').addEventListener('click', () => {
    if (!playlist.length) return;
    const playing = ytPlayer && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING;
    switchTrack((currentTrack - 1 + playlist.length) % playlist.length, playing);
});

document.getElementById('btn-next').addEventListener('click', () => {
    if (!playlist.length) return;
    const playing = ytPlayer && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING;
    switchTrack((currentTrack + 1) % playlist.length, playing);
});

playerProg.addEventListener('input', function() {
    if (ytPlayer && ytPlayer.getDuration) {
        ytPlayer.seekTo((this.value / 100) * ytPlayer.getDuration(), true);
    }
});

volSlider.addEventListener('input', function() {
    if (ytPlayer && ytPlayer.setVolume) {
        ytPlayer.setVolume(parseInt(this.value));
    }
    const icon = document.getElementById('volume-icon');
    if (parseInt(this.value) === 0) icon.textContent = '◄✕';
    else if (parseInt(this.value) < 50) icon.textContent = '◄)';
    else icon.textContent = '◄))';
    try {
        var s = JSON.parse(localStorage.getItem('melonOS_player') || '{}');
        s.volume = parseInt(this.value);
        localStorage.setItem('melonOS_player', JSON.stringify(s));
    } catch(e) {}
});

/* Restore volume before player API loads */
(function() {
    try {
        var s = JSON.parse(localStorage.getItem('melonOS_player') || '{}');
        if (typeof s.volume === 'number') {
            volSlider.value = s.volume;
            var icon = document.getElementById('volume-icon');
            if (s.volume === 0) icon.textContent = '◄✕';
            else if (s.volume < 50) icon.textContent = '◄)';
            else icon.textContent = '◄))';
        }
    } catch(e) {}
})();


document.getElementById('player-close').addEventListener('click', () => {
    if (ytPlayer) ytPlayer.pauseVideo();
    btnPlay.textContent = '►';
    clearInterval(progressInterval);
    playerWindow.style.display = 'none';
});

/* Cargar YouTube IFrame API */
const ytScript = document.createElement('script');
ytScript.src = 'https://www.youtube.com/iframe_api';
document.head.appendChild(ytScript);

/* Arrastrar ventana */
(function() {
    const titlebar = document.getElementById('player-titlebar');
    let dragging = false, ox, oy;
    titlebar.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'BUTTON') return;
        dragging = true;
        const rect = playerWindow.getBoundingClientRect();
        playerWindow.style.left   = rect.left + 'px';
        playerWindow.style.top    = rect.top  + 'px';
        playerWindow.style.right  = 'auto';
        playerWindow.style.bottom = 'auto';
        ox = e.clientX - rect.left;
        oy = e.clientY - rect.top;
    });
    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        playerWindow.style.left = (e.clientX - ox) + 'px';
        playerWindow.style.top  = (e.clientY - oy) + 'px';
    });
    document.addEventListener('mouseup', () => { dragging = false; });
})();

var openAddDialog = null;
var addTrackCallback = null;

/* Añadir canción */
(function() {
    var dialog   = document.getElementById('add-track-dialog');
    var urlInput = document.getElementById('add-yt-url');
    var artInput = document.getElementById('add-artist');
    var errEl    = document.getElementById('add-track-error');
    var fetchedTitle = '';

    function parseYouTubeId(raw) {
        var s = raw.trim();
        var m;
        m = s.match(/youtu\.be\/([A-Za-z0-9_-]{11})/);
        if (m) return m[1];
        m = s.match(/[?&]v=([A-Za-z0-9_-]{11})/);
        if (m) return m[1];
        m = s.match(/\/embed\/([A-Za-z0-9_-]{11})/);
        if (m) return m[1];
        if (/^[A-Za-z0-9_-]{11}$/.test(s)) return s;
        return null;
    }

    function openDialog() {
        urlInput.value = ''; artInput.value = '';
        errEl.textContent = '';
        resetTitlePreview();
        dialog.style.display = 'block';
        urlInput.focus();
    }
    function closeDialog() {
        dialog.style.display = 'none';
        if (!addTrackCallback) addTrackCallback = null;
    }
    openAddDialog = openDialog;

    var titlePreview = document.getElementById('add-title-preview');
    var fetchTimer   = null;
    var fetchedDuration = 0;
    var fetchedVideoId  = null;

    var stopPreviewMarquee = null;

    function resetTitlePreview() {
        fetchedTitle    = '';
        fetchedDuration = 0;
        fetchedVideoId  = null;
        titlePreview.textContent = '';
        titlePreview.style.color = '';
        if (stopPreviewMarquee) { stopPreviewMarquee(); stopPreviewMarquee = null; }
    }

    function applyScrollIfNeeded() {
        if (stopPreviewMarquee) { stopPreviewMarquee(); stopPreviewMarquee = null; }
        setTimeout(function() {
            stopPreviewMarquee = marqueeScroll(titlePreview, titlePreview.parentElement, 50, 1000);
        }, 50);
    }

    urlInput.addEventListener('input', function() {
        clearTimeout(fetchTimer);
        resetTitlePreview();
        var raw = urlInput.value.trim();

        if (/open\.spotify\.com\/.+\/track\/|spotify:track:/.test(raw)) {
            titlePreview.textContent = 'Buscando en Spotify...';
            fetchTimer = setTimeout(function() {
                fetch('assets/music/spotify-track.php?url=' + encodeURIComponent(raw))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) {
                        titlePreview.textContent = data.error;
                        titlePreview.style.color = '#c00';
                        return;
                    }
                    fetchedTitle    = data.title;
                    fetchedVideoId  = data.videoId;
                    fetchedDuration = data.duration;
                    if (!artInput.value.trim()) artInput.value = data.artist;
                    titlePreview.textContent = '♪ ' + data.title + ' [Spotify]';
                    titlePreview.style.color = '';
                    applyScrollIfNeeded();
                })
                .catch(function() {
                    titlePreview.textContent = 'Error de conexión';
                    titlePreview.style.color = '#c00';
                });
            }, 400);
            return;
        }

        var videoId = parseYouTubeId(urlInput.value);
        if (!videoId) return;
        titlePreview.textContent = 'Obteniendo título...';
        fetchTimer = setTimeout(function() {
            fetch('assets/music/yt-title.php?id=' + videoId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.title) {
                    fetchedTitle = data.title;
                    titlePreview.textContent = '♪ ' + data.title;
                    titlePreview.style.color = '';
                    applyScrollIfNeeded();
                } else {
                    titlePreview.textContent = data.error || 'No se pudo obtener el título';
                    titlePreview.style.color = '#c00';
                }
            })
            .catch(function() {
                titlePreview.textContent = 'Error de conexión';
                titlePreview.style.color = '#c00';
            });

            fetch('assets/music/yt-duration.php?id=' + videoId)
            .then(function(r) { return r.json(); })
            .then(function(data) { if (data.duration) fetchedDuration = data.duration; })
            .catch(function() {});
        }, 400);
    });

    document.getElementById('add-track-close').addEventListener('click', function() { addTrackCallback = null; closeDialog(); });
    document.getElementById('add-track-cancel').addEventListener('click', function() { addTrackCallback = null; closeDialog(); });

    document.getElementById('add-track-submit').addEventListener('click', function() {
        var videoId = fetchedVideoId || parseYouTubeId(urlInput.value);
        var title   = fetchedTitle;
        var artist  = artInput.value.trim();

        if (!videoId) { errEl.textContent = 'No se pudo extraer el ID del enlace.'; return; }
        if (!title)   { errEl.textContent = 'No se pudo obtener el título. Espera un momento.'; return; }
        errEl.textContent = '';

        var newTrack = { videoId: videoId, title: title, artist: artist, duration: fetchedDuration, addedBy: (usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '') };

        if (addTrackCallback) {
            addTrackCallback(newTrack);
            addTrackCallback = null;
            closeDialog();
            return;
        }

        fetch('assets/music/add-track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newTrack)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) { errEl.textContent = data.error; return; }
            playlist.push(newTrack);
            if (playlist.length === 1) {
                updateTrackUI(0);
                ytPlayer.cueVideoById(playlist[0].videoId);
            }
            closeDialog();
        })
        .catch(function(e) { errEl.textContent = 'Error: ' + e.message; });
    });
})();

/* Editor de playlist */
(function() {
    var editor       = document.getElementById('playlist-editor');
    var plList       = document.getElementById('pl-list');
    var plHome       = document.getElementById('pl-home');
    var plEditorView = document.getElementById('pl-editor-view');
    var plTitleText  = document.getElementById('pl-title-text');
    var plHomeList   = document.getElementById('pl-home-list');
    var plNameInput  = document.getElementById('pl-name-input');
    var editList     = [];
    var allPlaylists = [];
    var editingPlIdx = -1;
    var dragSrc      = null;

    function openEditor() {
        editor.style.display = 'block';
        showHome();
        loadPlaylists();
    }
    function closeEditor() { editor.style.display = 'none'; }

    function showHome() {
        plHome.style.display       = 'flex';
        plEditorView.style.display = 'none';
        plTitleText.textContent    = '♪ Playlists';
        if (allPlaylists.length > 0) renderHome();
    }

    function showEditorView(idx) {
        var pl = allPlaylists[idx];
        editingPlIdx = idx;
        plNameInput.value    = pl.name;
        plNameInput.disabled = !!pl.sharedFrom;
        editList = pl.tracks.map(function(t) {
            return { title: t.title, artist: t.artist || '', videoId: t.videoId, duration: t.duration || 0, addedBy: t.addedBy || '' };
        });
        plHome.style.display       = 'none';
        plEditorView.style.display = 'flex';
        plTitleText.textContent    = '✎ Editar playlist';
        renderList();
    }

    function loadPlaylists() {
        plHomeList.innerHTML = '<div class="pl-home-msg">Cargando...</div>';
        fetch('assets/music/get-playlists.php')
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(data) {
            if (data.error) { plHomeList.innerHTML = '<div class="pl-home-msg">Error: ' + data.error + '</div>'; return; }
            allPlaylists = data;
            renderHome();
        })
        .catch(function(e) {
            plHomeList.innerHTML = '<div class="pl-home-msg">Error: ' + e.message + '</div>';
        });
    }

    function renderHome() {
        plHomeList.innerHTML = '';
        if (allPlaylists.length === 0) {
            plHomeList.innerHTML = '<div class="pl-home-msg">Sin playlists. Crea una nueva.</div>';
            return;
        }
        allPlaylists.forEach(function(pl, idx) {
            var row      = document.createElement('div');
            row.className = 'pl-home-row';

            var totalSec = 0, hasDur = false;
            pl.tracks.forEach(function(t) { if (t.duration) { totalSec += t.duration; hasDur = true; } });
            var durStr = '';
            if (hasDur) {
                var h = Math.floor(totalSec / 3600), m = Math.floor((totalSec % 3600) / 60);
                durStr = ' · ' + (h > 0 ? h + 'h ' : '') + m + 'm';
            }

            var infoEl = document.createElement('div');
            infoEl.className = 'pl-home-info';

            var nameEl = document.createElement('div');
            nameEl.className   = 'pl-home-name';
            nameEl.textContent = (pl.sharedFrom ? '[+] ' : '') + pl.name;

            var metaEl = document.createElement('div');
            metaEl.className = 'pl-home-meta';
            var metaStr = pl.tracks.length + ' Canciones' + durStr;
            metaEl.textContent = metaStr;

            infoEl.appendChild(nameEl);
            infoEl.appendChild(metaEl);

            var btns = document.createElement('div');
            btns.className = 'pl-home-btns';

            var playBtn = makeBtn('▶', 'pl-action-btn', function() {
                if (!pl.tracks.length) return;
                currentPlaylistId = pl.id;
                currentPlaylistHasCollabs = !!(pl.sharedFrom || (pl.collaborators && pl.collaborators.length > 0));
                try { localStorage.setItem('melonOS_player', JSON.stringify({ playlistId: pl.id, trackIndex: 0 })); } catch(e) {}
                playlist.length = 0;
                pl.tracks.forEach(function(t) { playlist.push(t); });
                currentTrack = 0;
                updateTrackUI(0);
                updatePlayerTitle(pl.name);
                if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                    ytPlayer.loadVideoById(playlist[0].videoId);
                }
                closeEditor();
            });
            playBtn.title = 'Reproducir';

            var editBtn = makeBtn('✎', 'pl-action-btn', function() { showEditorView(idx); });
            editBtn.title = 'Editar';

            var delBtn;
            if (pl.sharedFrom) {
                delBtn = makeBtn('⊗', 'pl-action-btn', function() {
                    if (!confirm('¿Abandonar la playlist "' + pl.name + '"?')) return;
                    fetch('assets/music/leave-playlist.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: pl.id, sharedFrom: pl.sharedFrom })
                    })
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function(data) {
                        if (data.error) { alert(data.error); return; }
                        allPlaylists.splice(idx, 1);
                        renderHome();
                    })
                    .catch(function(e) { alert('Error: ' + e.message); });
                });
                delBtn.title = 'Abandonar';
            } else {
                delBtn = makeBtn('✕', 'pl-action-btn', function() {
                    if (!confirm('¿Eliminar la playlist "' + pl.name + '"?')) return;
                    fetch('assets/music/delete-playlist.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: pl.id })
                    })
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function(data) {
                        if (data.error) { alert(data.error); return; }
                        allPlaylists.splice(idx, 1);
                        renderHome();
                    })
                    .catch(function(e) { alert('Error: ' + e.message); });
                });
                delBtn.title = 'Eliminar';
            }

            btns.appendChild(playBtn);
            btns.appendChild(editBtn);
            btns.appendChild(delBtn);
            var collabsEl = document.createElement('div');
            collabsEl.className = 'pl-home-collabs';
            var collabKeys = [];
            if (pl.sharedFrom) {
                var participants = [pl.sharedFrom].concat(pl.collaborators || []);
                collabKeys = participants.filter(function(k) { return k !== currentUserKey; });
            } else if (pl.collaborators && pl.collaborators.length) {
                collabKeys = pl.collaborators;
            }
            collabKeys.slice(0, 4).forEach(function(cKey) {
                var u = usersInfo[cKey];
                if (!u) return;
                var wrap = document.createElement('div');
                wrap.className = 'collab-avatar-wrap';
                wrap.title = u.label;
                if (u.img) {
                    var img = document.createElement('img');
                    img.className = 'collab-avatar-img';
                    img.alt = u.label;
                    img.src = u.img;
                    wrap.appendChild(img);
                }
                collabsEl.appendChild(wrap);
            });

            row.appendChild(infoEl);
            if (collabKeys.length) row.appendChild(collabsEl);
            row.appendChild(btns);
            plHomeList.appendChild(row);
        });
    }

    function renderList() {
        plList.innerHTML = '';
        editList.forEach(function(track, i) {
            var row = document.createElement('div');
            row.className = 'pl-item';
            row.draggable = true;
            row.dataset.index = i;

            var handle = document.createElement('div');
            handle.className = 'pl-drag-handle';
            handle.textContent = '⠿';

            var infoDiv = document.createElement('div');
            infoDiv.className = 'pl-item-info';
            var t1 = document.createElement('div');
            t1.className = 'pl-item-title-text';
            t1.textContent = track.title;
            var t2 = document.createElement('div');
            t2.className = 'pl-item-artist-row';
            var artistSpan = document.createElement('span');
            artistSpan.className = 'pl-item-artist-text';
            artistSpan.textContent = track.artist || '—';
            t2.appendChild(artistSpan);
            if (track.addedBy) {
                var addedBySpan = document.createElement('span');
                addedBySpan.className = 'pl-item-addedby';
                addedBySpan.textContent = track.addedBy;
                t2.appendChild(addedBySpan);
            }
            infoDiv.appendChild(t1);
            infoDiv.appendChild(t2);

            var btnsDiv = document.createElement('div');
            btnsDiv.className = 'pl-item-btns';
            btnsDiv.appendChild(makeBtn('✎', 'pl-action-btn', function() { startEdit(row, infoDiv, btnsDiv, i); }));
            btnsDiv.appendChild(makeBtn('✕', 'pl-action-btn', function() { editList.splice(i, 1); renderList(); }));

            row.addEventListener('dragstart', function(e) {
                dragSrc = i;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(function() { row.classList.add('dragging'); }, 0);
            });
            row.addEventListener('dragend', function() {
                row.classList.remove('dragging');
                plList.querySelectorAll('.pl-item').forEach(function(r) { r.classList.remove('drag-over'); });
            });
            row.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                plList.querySelectorAll('.pl-item').forEach(function(r) { r.classList.remove('drag-over'); });
                row.classList.add('drag-over');
            });
            row.addEventListener('drop', function(e) {
                e.preventDefault();
                if (dragSrc !== null && dragSrc !== i) {
                    var moved = editList.splice(dragSrc, 1)[0];
                    editList.splice(i, 0, moved);
                    renderList();
                }
            });

            row.appendChild(handle);
            row.appendChild(infoDiv);
            row.appendChild(btnsDiv);
            plList.appendChild(row);
        });
    }

    function startEdit(row, infoDiv, btnsDiv, i) {
        var track = editList[i];
        var wrap  = document.createElement('div');
        wrap.className = 'pl-item-edit-inputs';
        var ti = document.createElement('input');
        ti.type = 'text'; ti.value = track.title;
        var ai = document.createElement('input');
        ai.type = 'text'; ai.value = track.artist || '';
        wrap.appendChild(ti); wrap.appendChild(ai);
        row.replaceChild(wrap, infoDiv);

        btnsDiv.innerHTML = '';
        btnsDiv.appendChild(makeBtn('✓', 'pl-action-btn', function() {
            editList[i].title  = ti.value.trim() || editList[i].title;
            editList[i].artist = ai.value.trim();
            renderList();
        }));
        btnsDiv.appendChild(makeBtn('✕', 'pl-action-btn', renderList));
        ti.focus(); ti.select();
    }

    function makeBtn(label, cls, fn) {
        var b = document.createElement('button');
        b.className = 'button ' + cls;
        b.textContent = label;
        b.addEventListener('click', fn);
        return b;
    }

    function saveCurrentPlaylist(onSuccess) {
        if (editingPlIdx < 0) return;
        var pl = allPlaylists[editingPlIdx];
        var newName = plNameInput.value.trim();
        if (newName) pl.name = newName;
        pl.tracks = editList.slice();
        var savePayload = { id: pl.id, name: pl.name, tracks: pl.tracks };
        if (pl.sharedFrom) savePayload.sharedFrom = pl.sharedFrom;
        fetch('assets/music/save-playlist-item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(savePayload)
        })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(data) {
            if (data.error) { alert(data.error); return; }
            if (onSuccess) onSuccess();
        })
        .catch(function(e) { alert('Error al guardar: ' + e.message); });
    }

    document.getElementById('btn-edit-playlist').addEventListener('click', openEditor);
    document.getElementById('pl-close').addEventListener('click', closeEditor);
    document.getElementById('pl-back').addEventListener('click', showHome);

    document.getElementById('pl-add').addEventListener('click', function() {
        addTrackCallback = function(track) { editList.push(track); renderList(); };
        openAddDialog();
    });

    document.getElementById('pl-save').addEventListener('click', function() {
        saveCurrentPlaylist(function() {
            var pl = allPlaylists[editingPlIdx];
            if (pl.id === currentPlaylistId && pl.tracks.length > 0) {
                playlist.length = 0;
                pl.tracks.forEach(function(t) { playlist.push(t); });
                currentTrack = 0;
                updateTrackUI(0);
                updatePlayerTitle(pl.name);
                if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                    ytPlayer.loadVideoById(playlist[0].videoId);
                }
            }
            showHome();
        });
    });

    /* Create playlist dialog */
    (function() {
        var createDlg    = document.getElementById('create-playlist-dialog');
        var createInput  = document.getElementById('create-pl-name');
        var createSubmit = document.getElementById('create-pl-submit');

        function openCreateDlg() {
            createInput.value = '';
            createDlg.style.display = 'block';
            setTimeout(function() { createInput.focus(); createInput.select(); }, 50);
        }
        function closeCreateDlg() { createDlg.style.display = 'none'; }

        document.getElementById('pl-create').addEventListener('click', openCreateDlg);
        document.getElementById('create-pl-close').addEventListener('click', closeCreateDlg);
        document.getElementById('create-pl-cancel').addEventListener('click', closeCreateDlg);

        function doCreate() {
            var name = createInput.value.trim();
            if (!name) return;
            closeCreateDlg();
            var newPl = { id: 'pl_' + Date.now(), name: name, tracks: [] };
            fetch('assets/music/save-playlist-item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newPl)
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                if (data.error) { alert(data.error); return; }
                allPlaylists.push(newPl);
                showEditorView(allPlaylists.length - 1);
            })
            .catch(function(e) { alert('Error: ' + e.message); });
        }

        createSubmit.addEventListener('click', doCreate);
        createInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') doCreate(); });
    })();

    /* ... menu + import playlist */
    (function() {
        var moreBtn    = document.getElementById('pl-more');
        var moreMenu   = document.getElementById('pl-more-menu');
        var importDlg  = document.getElementById('import-playlist-dialog');
        var importUrl  = document.getElementById('import-pl-url');
        var importStat = document.getElementById('import-pl-status');
        var importBtn  = document.getElementById('import-pl-submit');

        moreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            moreMenu.style.display = moreMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function() { moreMenu.style.display = 'none'; });

        document.getElementById('pl-more-import').addEventListener('click', function() {
            moreMenu.style.display = 'none';
            importUrl.value = '';
            importStat.textContent = '';
            importBtn.disabled = false;
            importDlg.style.display = 'block';
        });

        function closeImport() { importDlg.style.display = 'none'; }
        document.getElementById('import-pl-close').addEventListener('click', closeImport);
        document.getElementById('import-pl-cancel').addEventListener('click', closeImport);

        importBtn.addEventListener('click', function() {
            var url = importUrl.value.trim();
            if (!url) return;
            importStat.textContent = 'Importando...';
            importBtn.disabled = true;
            fetch('assets/music/import-playlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url })
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                importBtn.disabled = false;
                if (data.error) { importStat.textContent = 'Error: ' + data.error; return; }
                var importer = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
                data.tracks.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                renderList();
                importStat.textContent = '';
                closeImport();
            })
            .catch(function(e) {
                importBtn.disabled = false;
                importStat.textContent = 'Error: ' + e.message;
            });
        });

        /* Spotify CSV import */
        var spotifyDlg  = document.getElementById('spotify-import-dialog');
        var spotifyFile = document.getElementById('spotify-import-file');
        var spotifyStat = document.getElementById('spotify-import-status');
        var spotifyBtn  = document.getElementById('spotify-import-submit');

        function openSpotifyDialog() {
            moreMenu.style.display = 'none';
            spotifyFile.value = '';
            spotifyFileName.value = '';
            spotifyStat.textContent = '';
            spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
            spotifyProgressWrap.style.display = 'none';
            spotifyProgressFill.style.width = '0%';
            spotifyDlg.style.display = 'block';
        }

        document.getElementById('pl-more-spotify').addEventListener('click', openSpotifyDialog);

        var spotifyFileName = document.getElementById('spotify-file-name');
        document.getElementById('spotify-file-browse').addEventListener('click', function() { spotifyFile.click(); });
        spotifyFile.addEventListener('change', function() {
            spotifyFileName.value = spotifyFile.files[0] ? spotifyFile.files[0].name : '';
        });

        var spotifyAbortCtrl = null;

        function closeSpotifyImport() {
            if (spotifyAbortCtrl) { spotifyAbortCtrl.abort(); spotifyAbortCtrl = null; }
            spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
            spotifyProgressWrap.style.display = 'none';
            spotifyDlg.style.display = 'none';
        }
        document.getElementById('spotify-import-close').addEventListener('click', closeSpotifyImport);
        document.getElementById('spotify-import-cancel').addEventListener('click', closeSpotifyImport);

        function parseExportifyCSV(raw) {
            /* Parse RFC-4180-ish CSV properly (handles quoted fields with commas) */
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

        var spotifyProgressWrap = document.getElementById('spotify-progress-wrap');
        var spotifyProgressFill = document.getElementById('spotify-progress-fill');
        var spotifyProgressText = document.getElementById('spotify-progress-text');

        spotifyBtn.addEventListener('click', function() {
            var file = spotifyFile.files[0];
            if (!file) { spotifyStat.textContent = 'Selecciona un archivo CSV.'; return; }
            var reader = new FileReader();
            reader.onload = function(e) {
                var tracks = parseExportifyCSV(e.target.result);
                reader.onload = null;
                if (!tracks.length) { spotifyStat.textContent = 'No se encontraron canciones en el archivo.'; return; }

                spotifyAbortCtrl = new AbortController();
                var signal = spotifyAbortCtrl.signal;

                spotifyBtn.style.pointerEvents = 'none'; spotifyBtn.classList.add('btn-busy');
                spotifyStat.textContent = '';
                spotifyProgressWrap.style.display = 'block';
                spotifyProgressFill.style.width = '0%';
                spotifyProgressText.textContent = '0 encontradas · ' + tracks.length + ' restantes';

                var BATCH   = 10;
                var total   = tracks.length;
                var found   = 0;
                var done    = 0;
                var results = [];

                function nextBatch(offset) {
                    if (signal.aborted || offset >= total) return Promise.resolve();
                    var chunk = tracks.slice(offset, offset + BATCH);
                    return fetch('assets/music/yt-search-batch.php', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body:    JSON.stringify({ tracks: chunk }),
                        signal:  signal
                    })
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function(data) {
                        if (data.tracks) {
                            results = results.concat(data.tracks);
                            found  += data.tracks.length;
                        }
                        done = Math.min(offset + BATCH, total);
                        var pct = Math.round(done / total * 100);
                        spotifyProgressFill.style.width = pct + '%';
                        spotifyProgressText.textContent = found + ' encontradas · ' + (total - done) + ' restantes';
                        return nextBatch(offset + BATCH);
                    });
                }

                nextBatch(0)
                .then(function() {
                    if (signal.aborted) return;
                    spotifyAbortCtrl = null;
                    spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
                    spotifyProgressWrap.style.display = 'none';
                    if (!results.length) { spotifyStat.textContent = 'No se encontraron vídeos en YouTube.'; return; }
                    var importer = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
                    results.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                    renderList();
                    closeSpotifyImport();
                })
                .catch(function(e) {
                    if (e.name === 'AbortError') return;
                    spotifyAbortCtrl = null;
                    spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
                    spotifyProgressWrap.style.display = 'none';
                    spotifyStat.textContent = 'Error: ' + e.message;
                });
            };
            reader.readAsText(file);
        });

        /* Colaborador */
        var collabDlg      = document.getElementById('collab-dialog');
        var collabPlName   = document.getElementById('collab-pl-name');
        var collabUserList = document.getElementById('collab-user-list');
        var collabStatus   = document.getElementById('collab-status');

        document.getElementById('pl-more-collab').addEventListener('click', function() {
            moreMenu.style.display = 'none';
            if (editingPlIdx < 0) return;
            var pl = allPlaylists[editingPlIdx];
            if (pl.sharedFrom) { alert('Solo el propietario puede añadir colaboradores.'); return; }
            collabPlName.textContent = pl.name;
            collabStatus.textContent = '';
            collabUserList.innerHTML = '<div style="font-size:11px;color:#808080;">Cargando...</div>';
            collabDlg.style.display = 'block';
            fetch('assets/music/get-users.php')
            .then(function(r) { return r.json(); })
            .then(function(users) {
                collabUserList.innerHTML = '';
                if (!Array.isArray(users) || !users.length) {
                    collabUserList.innerHTML = '<div class="pl-home-msg">No hay otros usuarios.</div>';
                    return;
                }
                users.forEach(function(u) {
                    var already = Array.isArray(pl.collaborators) && pl.collaborators.indexOf(u.key) >= 0;
                    var row = document.createElement('div');
                    row.className = 'collab-user-row';
                    var avatarWrap = document.createElement('div');
                    avatarWrap.className = 'collab-avatar-wrap';
                    var uInfo = usersInfo[u.key];
                    if (uInfo && uInfo.img) {
                        var avatarImg = document.createElement('img');
                        avatarImg.className = 'collab-avatar-img';
                        avatarImg.src = uInfo.img;
                        avatarImg.alt = u.label;
                        avatarWrap.appendChild(avatarImg);
                    }
                    var nameSpan = document.createElement('span');
                    nameSpan.textContent = u.label;
                    var invBtn = makeBtn(already ? 'Ya colabora' : 'Invitar', 'collab-invite-btn', function() {
                        if (already) return;
                        collabStatus.textContent = 'Enviando...';
                        invBtn.disabled = true;
                        fetch('assets/music/invite-collaborator.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ playlistId: pl.id, toUser: u.key })
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.error) { collabStatus.textContent = data.error; invBtn.disabled = false; return; }
                            invBtn.textContent = 'Invitado';
                            invBtn.disabled = true;
                            collabStatus.textContent = 'Invitación enviada a ' + u.label;
                        })
                        .catch(function(e) { collabStatus.textContent = 'Error: ' + e.message; invBtn.disabled = false; });
                    });
                    invBtn.disabled = already;
                    row.appendChild(avatarWrap);
                    row.appendChild(nameSpan);
                    row.appendChild(invBtn);
                    collabUserList.appendChild(row);
                });
            })
            .catch(function(e) {
                collabUserList.innerHTML = '<div style="font-size:11px;color:#c00;">Error: ' + e.message + '</div>';
            });
        });

        document.getElementById('collab-close').addEventListener('click', function() { collabDlg.style.display = 'none'; });
        document.getElementById('collab-cancel').addEventListener('click', function() { collabDlg.style.display = 'none'; });
    })();

    /* Drag window */
    (function() {
        var tb = document.getElementById('pl-titlebar');
        var dragging = false, ox, oy;
        tb.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'BUTTON') return;
            dragging = true;
            var r = editor.getBoundingClientRect();
            editor.style.left = r.left + 'px'; editor.style.top = r.top + 'px';
            editor.style.transform = 'none';
            ox = e.clientX - r.left; oy = e.clientY - r.top;
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            editor.style.left = (e.clientX - ox) + 'px';
            editor.style.top  = (e.clientY - oy) + 'px';
        });
        document.addEventListener('mouseup', function() { dragging = false; });
    })();

    refreshPlaylists = loadPlaylists;

    setInterval(function() {
        if (editor.style.display !== 'block') return;
        fetch('assets/music/get-playlists.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!Array.isArray(data) || data.error) return;
            allPlaylists = data;
            if (plHome.style.display !== 'none') renderHome();
        })
        .catch(function() {});
    }, 15000);
})();

/* Notificaciones de invitacion */
(function() {
    var notif         = document.getElementById('invite-notification');
    var inviteMsg     = document.getElementById('invite-msg');
    var acceptBtn     = document.getElementById('invite-accept');
    var rejectBtn     = document.getElementById('invite-reject');
    var currentInvite = null;
    var shownId       = null;

    function checkInvites() {
        fetch('assets/music/get-invites.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!Array.isArray(data) || !data.length) {
                if (shownId) { notif.style.display = 'none'; shownId = null; currentInvite = null; }
                return;
            }
            var inv = data[0];
            if (inv.id === shownId) return;
            currentInvite = inv;
            shownId       = inv.id;
            inviteMsg.textContent = inv.fromLabel + ' te invita a colaborar en "' + inv.playlistName + '"';
            notif.style.display = 'block';
        })
        .catch(function() {});
    }

    function respond(action) {
        if (!currentInvite) return;
        var inv   = currentInvite;
        currentInvite = null;
        shownId       = null;
        notif.style.display = 'none';
        fetch('assets/music/respond-invite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inviteId: inv.id, action: action })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) { alert(data.error); return; }
            if (action === 'accept' && refreshPlaylists) refreshPlaylists();
        })
        .catch(function() {});
    }

    acceptBtn.addEventListener('click', function() { respond('accept'); });
    rejectBtn.addEventListener('click', function() { respond('reject'); });
    checkInvites();
    setInterval(checkInvites, 5000);
})();
<?php endif; ?>

/* =========================
   ARCHIVE
========================= */

const archiveWindow   = document.getElementById('archive-window');
const archiveBack     = document.getElementById('archive-back');
const archiveBreadcrumb = document.getElementById('archive-breadcrumb');
const archiveGrid     = document.getElementById('archive-grid');
const archiveStatus   = document.getElementById('archive-status');
const archiveIframe   = document.getElementById('archive-iframe');
const archiveContent  = document.getElementById('archive-content');
const archivePlayer   = document.getElementById('archive-player');
let archiveView = 'playlists';

document.getElementById('archive-icon').addEventListener('dblclick', function() {
    archiveWindow.style.display = 'flex';
    if (archiveGrid.children.length === 0) loadPlaylists();
});

document.getElementById('archive-close').addEventListener('click', function() {
    archiveWindow.style.display = 'none';
    archiveIframe.src = '';
});

archiveBack.addEventListener('click', function() {
    if (archiveView === 'video') {
        archiveIframe.src = '';
        archivePlayer.style.display = 'none';
        archiveContent.style.display = 'flex';
        archiveView = 'videos';
    } else {
        loadPlaylists();
    }
});

function archiveSetStatus(msg) {
    archiveStatus.textContent = msg;
    archiveGrid.innerHTML = '';
}

async function loadPlaylists() {
    archiveView = 'playlists';
    archiveBack.disabled = true;
    archiveBreadcrumb.textContent = 'Playlists';
    archivePlayer.style.display = 'none';
    archiveContent.style.display = 'flex';
    archiveSetStatus('Cargando playlists...');
    try {
        const res  = await fetch('assets/yt-archive.php?action=playlists');
        const data = await res.json();
        archiveStatus.textContent = '';
        if (data.error) { archiveStatus.textContent = data.error; return; }
        renderPlaylists(data.playlists || []);
    } catch(e) { archiveStatus.textContent = 'Error: ' + e.message; }
}

async function loadVideos(id, title) {
    archiveView = 'videos';
    archiveBack.disabled = false;
    archiveBreadcrumb.textContent = title;
    archiveSetStatus('Cargando vídeos...');
    try {
        const res  = await fetch('assets/yt-archive.php?action=videos&list=' + id);
        const data = await res.json();
        archiveStatus.textContent = '';
        if (data.error) { archiveStatus.textContent = data.error; return; }
        renderVideos(data.videos || []);
    } catch(e) { archiveStatus.textContent = 'Error: ' + e.message; }
}

function renderPlaylists(list) {
    archiveGrid.innerHTML = '';
    if (!list.length) { archiveStatus.textContent = 'No se encontraron playlists.'; return; }
    list.forEach(pl => {
        const el = document.createElement('div');
        el.className = 'archive-item';
        el.innerHTML = `<img src="${pl.thumb}" alt="" loading="lazy">
            <div class="archive-item-title">${pl.title}</div>
            <div class="archive-item-meta">${pl.count}</div>`;
        el.addEventListener('click', () => loadVideos(pl.id, pl.title));
        archiveGrid.appendChild(el);
    });
}

function renderVideos(list) {
    archiveGrid.innerHTML = '';
    if (!list.length) { archiveStatus.textContent = 'No se encontraron vídeos.'; return; }
    list.forEach(v => {
        const el = document.createElement('div');
        el.className = 'archive-item';
        el.innerHTML = `<img src="${v.thumb}" alt="" loading="lazy">
            <div class="archive-item-title">${v.title}</div>
            <div class="archive-item-meta">${v.duration}</div>`;
        el.addEventListener('click', () => playVideo(v.id));
        archiveGrid.appendChild(el);
    });
}

function playVideo(id) {
    archiveView = 'video';
    archiveBack.disabled = false;
    archiveContent.style.display = 'none';
    archivePlayer.style.display = 'flex';
    archiveIframe.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1&rel=0';
}

(function() {
    const bar = document.getElementById('archive-titlebar');
    let dragging = false, ox, oy;
    bar.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'BUTTON') return;
        dragging = true;
        ox = e.clientX - archiveWindow.offsetLeft;
        oy = e.clientY - archiveWindow.offsetTop;
    });
    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        archiveWindow.style.left = (e.clientX - ox) + 'px';
        archiveWindow.style.top  = (e.clientY - oy) + 'px';
    });
    document.addEventListener('mouseup', () => { dragging = false; });
})();

(function() {
    const handles = archiveWindow.querySelectorAll('.ar-handle');
    var resizing = false, dir = '', sx, sy, sw, sh, sl, st;
    var MIN_W = 360, MIN_H = 280;

    handles.forEach(function(h) {
        h.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resizing = true;
            dir = h.dataset.dir;
            sx = e.clientX; sy = e.clientY;
            var r = archiveWindow.getBoundingClientRect();
            sw = r.width; sh = r.height; sl = r.left; st = r.top;
            archiveWindow.style.left   = sl + 'px';
            archiveWindow.style.top    = st + 'px';
            archiveWindow.style.right  = 'auto';
            archiveWindow.style.bottom = 'auto';
        });
    });

    document.addEventListener('mousemove', function(e) {
        if (!resizing) return;
        var dx = e.clientX - sx, dy = e.clientY - sy;
        if (dir.indexOf('e') !== -1) archiveWindow.style.width  = Math.max(MIN_W, sw + dx) + 'px';
        if (dir.indexOf('s') !== -1) archiveWindow.style.height = Math.max(MIN_H, sh + dy) + 'px';
        if (dir.indexOf('w') !== -1) {
            var nw = Math.max(MIN_W, sw - dx);
            archiveWindow.style.width = nw + 'px';
            archiveWindow.style.left  = (sl + sw - nw) + 'px';
        }
        if (dir.indexOf('n') !== -1) {
            var nh = Math.max(MIN_H, sh - dy);
            archiveWindow.style.height = nh + 'px';
            archiveWindow.style.top    = (st + sh - nh) + 'px';
        }
    });

    document.addEventListener('mouseup', function() { resizing = false; });
})();
document.getElementById('calendar-icon').addEventListener('dblclick', function() {
    window.location.href = 'calendario.php';
});
</script>

</body>
</html>
