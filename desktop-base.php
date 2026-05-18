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
if ($desktopUserKey === 'user1') {
    require_once __DIR__ . '/assets/music/playlist.php';
} elseif ($desktopUserKey === 'user2') {
    require_once __DIR__ . '/assets/music/angie-playlist.php';
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
<div id="desktop"></div>

<?php if ($hasPlayer): ?>
<div id="yt-player"></div>
<!-- REPRODUCTOR -->
<div class="window" id="music-player">
    <div class="title-bar" id="player-titlebar">
        <div class="title-bar-text">♪ Reproductor</div>
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
        <div id="player-volume-wrap">
            <div id="volume-track-outer">
                <input type="range" id="player-volume" min="0" max="100" value="100" step="1" orient="vertical">
            </div>
            <span id="volume-icon">◄))</span>
        </div>
    </div>
</div>
<?php endif; ?>

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
let currentTrack = 0;
let ytPlayer = null;
let progressInterval = null;

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
    playerProg.value = 0;
    playerCurrent.textContent = '0:00';
    playerDur.textContent = '0:00';
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
            onReady: () => {
                if (playlist.length) {
                    updateTrackUI(0);
                    ytPlayer.cueVideoById(playlist[0].videoId);
                }
                ytPlayer.setVolume(parseInt(volSlider.value));
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
});


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
        ox = e.clientX - playerWindow.offsetLeft;
        oy = e.clientY - playerWindow.offsetTop;
    });
    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        playerWindow.style.left = (e.clientX - ox) + 'px';
        playerWindow.style.top  = (e.clientY - oy) + 'px';
    });
    document.addEventListener('mouseup', () => { dragging = false; });
})();
<?php endif; ?>
</script>

</body>
</html>
