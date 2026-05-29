<?php
// reproductor.php - All player HTML + PHP setup
// Included from desktop-base.php; expects $desktopUserKey and $hasPlayer already set.
require_once dirname(__DIR__) . '/assets/config.php';
require_once dirname(__DIR__) . '/db.php';

/* Pool global de pistas que el reproductor muestra al inicio:
   1) Listado estático por defecto para user1/user2 (semilla histórica).
   2) Pistas añadidas vía add-track → tabla music_extras. */
$youtubePlaylist = [];
if ($desktopUserKey === 'user1') {
    require_once dirname(__DIR__) . '/assets/music/playlist.php';
} elseif ($desktopUserKey === 'user2') {
    require_once dirname(__DIR__) . '/assets/music/angie-playlist.php';
}
$stmt = $pdo->prepare("SELECT me.video_id AS videoId, me.title, me.artist
                       FROM music_extras me
                       JOIN usuarios u ON me.user_id = u.id
                       WHERE u.user_key = ?
                       ORDER BY me.id ASC");
$stmt->execute([$desktopUserKey]);
$youtubePlaylist = array_merge($youtubePlaylist, $stmt->fetchAll(PDO::FETCH_ASSOC));

?>

<?php if ($hasPlayer): ?>
<div id="yt-player"></div>
<!-- REPRODUCTOR -->
<div class="window" id="music-player">
    <div class="title-bar" id="player-titlebar">
        <div class="title-bar-text" id="player-tb-text"><span id="player-pl-name">♪ Reproductor</span></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize" id="player-minimize"></button>
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
        <button class="button" id="btn-shuffle" title="Reproducción aleatoria">⇄</button>
        <button class="button" id="btn-edit-playlist" title="Ver playlist">☰</button>
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
                        <div class="pl-menu-item" id="pl-more-tidal">Importar de Tidal</div>
                        <div class="pl-menu-item" id="pl-more-collab">Colaboradores</div>
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
            <label for="add-yt-url">Enlace de YouTube, Spotify o Tidal</label>
            <input type="text" id="add-yt-url" placeholder="youtube.com/watch?v=… · open.spotify.com/track/… · tidal.com/track/…">
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
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
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
        <div class="title-bar-text">Colaboradores</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="collab-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p style="margin:0 0 6px;font-size:11px;">Playlist: "<span id="collab-pl-name"></span>"</p>
        <div id="collab-user-list"></div>
        <p id="collab-status"></p>
        <div class="field-row" style="justify-content:flex-end;margin-top:6px;">
            <button class="button" id="collab-cancel">Cerrar</button>
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
        <p id="spotify-import-hint" style="margin:0 0 6px;">
            Exporta tu playlist desde exportify.net y sube el CSV.
        </p>
        <div class="spotify-warn">
            ⚠️ La búsqueda en YouTube puede no coincidir exactamente con algunas canciones. Si alguna falla o es incorrecta, añádela tú mismo con el botón <strong>+ Añadir</strong> del editor de playlist.
        </div>
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

<!-- TIDAL IMPORT DIALOG -->
<div class="window" id="tidal-import-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Importar de Tidal</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="tidal-import-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p style="margin:0 0 6px;">
            Pega el enlace de una <strong>canción</strong> o <strong>playlist</strong> de Tidal.
        </p>
        <div class="spotify-warn">
            ⚠️ La búsqueda en YouTube puede no coincidir exactamente con algunas canciones. Si alguna falla o es incorrecta, añádela tú mismo con el botón <strong>+ Añadir</strong> del editor de playlist.
        </div>
        <div class="field-row-stacked">
            <label for="tidal-import-url">Enlace de Tidal</label>
            <input type="text" id="tidal-import-url" placeholder="https://tidal.com/browse/playlist/... o .../track/...">
        </div>
        <div id="tidal-progress-wrap" style="display:none;margin:6px 0 0;">
            <div class="progress-indicator segmented" style="height:18px;padding:2px;">
                <span id="tidal-progress-fill" class="progress-indicator-bar" style="width:0%;"></span>
            </div>
            <p id="tidal-progress-text" style="font-size:10px;margin:3px 0 0;text-align:center;"></p>
        </div>
        <p id="tidal-import-status"></p>
        <div class="field-row" style="justify-content: flex-end; gap: 4px; margin-top: 10px;">
            <button class="button" id="tidal-import-cancel">Cancelar</button>
            <button class="button" id="tidal-import-submit">Importar</button>
        </div>
    </div>
</div>


<!-- CONFIRM DIALOG -->
<div class="window" id="confirm-dialog" style="display:none;position:fixed;z-index:10003;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;">
    <div class="title-bar">
        <div class="title-bar-text" id="confirm-dialog-title">Confirmar</div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <p id="confirm-dialog-msg" style="margin:0 0 12px;font-size:11px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;">
            <button class="button" id="confirm-dialog-cancel">Cancelar</button>
            <button class="button" id="confirm-dialog-ok">Aceptar</button>
        </div>
    </div>
</div>

<script>
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

/* Estado del reproductor persistido en SQL (user_settings.key='player').
   Reemplaza la antigua copia en localStorage['melonOS_player']. La lectura
   inicial usa window.DesktopState (precargado por desktop-base) y los
   writes se hacen optimistas + POST debounced al servidor. */
var MelonPlayerState = (function(){
    var state = null;
    function get(){
        if (state) return state;
        if (window.DesktopState && window.DesktopState.player) state = window.DesktopState.player;
        return state;
    }
    var _t = null;
    function push(){
        clearTimeout(_t);
        _t = setTimeout(function(){
            fetch('assets/desktop/api.php?action=save-player', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(state || {})
            }).catch(function(){});
        }, 200);
    }
    function setPlaylist(playlistId, trackIndex){
        state = { playlistId: playlistId, trackIndex: trackIndex|0 };
        if (window.DesktopState) window.DesktopState.player = state;
        push();
    }
    function setTrack(trackIndex){
        if (!state) state = { playlistId: null, trackIndex: 0 };
        state.trackIndex = trackIndex|0;
        if (window.DesktopState) window.DesktopState.player = state;
        push();
    }
    function setVolume(v){
        if (!state) state = { playlistId: null, trackIndex: 0 };
        state.volume = Math.max(0, Math.min(100, v|0));
        if (window.DesktopState) window.DesktopState.player = state;
        push();
    }
    return { get: get, setPlaylist: setPlaylist, setTrack: setTrack, setVolume: setVolume };
})();

let currentTrack      = 0;
let currentPlaylistId = null;
var currentPlaylistHasCollabs = false;
let ytPlayer = null;
let progressInterval  = null;
let autoplayRandom    = false;

/* win98Confirm vive ahora en assets/js/win98-dialogs.js (compartido).
   La firma (message, title, onOk) se mantiene compatible con los callers. */
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

    MelonPlayerState.setTrack(index);

    if (typeof window.updatePlaylistPlayingHighlight === 'function') {
        window.updatePlaylistPlayingHighlight(currentPlaylistId, index);
    }
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
                /* onReady puede dispararse antes de que fetchState haya
                   resuelto. whenReady aplica el volumen y la playlist
                   guardada en cuanto ambos extremos estén listos. */
                window.DesktopState.whenReady(function(){
                    var saved = MelonPlayerState.get();
                    if (saved && typeof saved.volume === 'number') {
                        ytPlayer.setVolume(saved.volume);
                    } else {
                        ytPlayer.setVolume(parseInt(volSlider.value));
                    }
                    restorePlaylist(saved);
                });

                function restoreDefault() {
                    if (playlist.length) { updateTrackUI(0); ytPlayer.cueVideoById(playlist[0].videoId); }
                }

                function restorePlaylist(saved) {
                if (saved && saved.playlistId) {
                    fetch('assets/music/api.php?action=get-playlists')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!Array.isArray(data)) { restoreDefault(); return; }
                        var found = null;
                        for (var i = 0; i < data.length; i++) {
                            if (data[i].id === saved.playlistId) { found = data[i]; break; }
                        }
                        if (!found || !found.tracks.length) { restoreDefault(); return; }
                        currentPlaylistId = found.id;
                        currentPlaylistHasCollabs = !!(found.sharedFrom || (found.collaborators && found.collaborators.length > 0));
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
                    if (autoplayRandom && playlist.length > 1) {
                        let next;
                        do { next = Math.floor(Math.random() * playlist.length); } while (next === currentTrack);
                        currentTrack = next;
                    } else {
                        currentTrack = (currentTrack + 1) % playlist.length;
                    }
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
    if (autoplayRandom && playlist.length > 1) {
        let next;
        do { next = Math.floor(Math.random() * playlist.length); } while (next === currentTrack);
        switchTrack(next, playing);
    } else {
        switchTrack((currentTrack + 1) % playlist.length, playing);
    }
});

document.getElementById('btn-shuffle').addEventListener('click', () => {
    autoplayRandom = !autoplayRandom;
    document.getElementById('btn-shuffle').classList.toggle('btn-active', autoplayRandom);
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
    MelonPlayerState.setVolume(parseInt(this.value));
});

/* Restore volume before player API loads.
   El estado vive en window.DesktopState, que se rellena de forma async
   por desktop-base.fetchState(). whenReady garantiza que aplicamos el
   volumen guardado AUNQUE el fetch aún no haya resuelto al evaluar este
   script — y también si el YT player ya está listo, le aplicamos el volumen. */
window.DesktopState.whenReady(function(){
    var s = MelonPlayerState.get() || {};
    if (typeof s.volume !== 'number') return;
    volSlider.value = s.volume;
    var icon = document.getElementById('volume-icon');
    if (s.volume === 0) icon.textContent = '◄✕';
    else if (s.volume < 50) icon.textContent = '◄)';
    else icon.textContent = '◄))';
    if (ytPlayer && ytPlayer.setVolume) ytPlayer.setVolume(s.volume);
});


document.getElementById('player-close').addEventListener('click', () => {
    if (ytPlayer) ytPlayer.pauseVideo();
    btnPlay.textContent = '►';
    clearInterval(progressInterval);
    taskbarManager.unregister('music-player');
});

document.getElementById('player-minimize').addEventListener('click', function() {
    playerWindow.style.display = 'none';
});

document.getElementById('tray-player-btn').addEventListener('click', function() {
    playerWindow.style.display = playerWindow.style.display === 'none' ? 'block' : 'none';
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

    /* El artista se autocompleta desde los metadatos (Spotify/Tidal/YouTube)
       mientras el usuario no lo haya escrito a mano. */
    var artistTouched = false;
    artInput.addEventListener('input', function() { artistTouched = true; });
    function setArtist(name) {
        if (artistTouched) return;          // respeta lo que escribió el usuario
        artInput.value = name || '';
    }
    function cleanYtAuthor(name) {
        if (!name) return '';
        return name.replace(/\s*-\s*topic$/i, '').replace(/\s*VEVO$/i, '').trim();
    }

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
        artistTouched = false;
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
                fetch('assets/music/api.php?action=spotify-track&url=' + encodeURIComponent(raw))
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
                    setArtist(data.artist);
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

        if (/tidal\.com\/(?:browse\/)?track\/\d+/i.test(raw)) {
            titlePreview.textContent = 'Buscando en Tidal...';
            fetchTimer = setTimeout(function() {
                fetch('assets/music/api.php?action=tidal-track&url=' + encodeURIComponent(raw))
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
                    setArtist(data.artist);
                    titlePreview.textContent = '♪ ' + data.title + ' [Tidal]';
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
            fetch('assets/music/api.php?action=yt-title&id=' + videoId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.title) {
                    fetchedTitle = data.title;
                    setArtist(cleanYtAuthor(data.author));
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

            fetch('assets/music/api.php?action=yt-duration&id=' + videoId)
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

        fetch('assets/music/api.php?action=add-track', {
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
        if (taskbarManager.isRegistered('playlist-editor')) {
            taskbarManager.restore('playlist-editor');
        } else {
            taskbarManager.register('playlist-editor', 'Playlists', '♪', 'flex');
        }
        showHome();
        loadPlaylists();
    }
    function closeEditor() {
        taskbarManager.unregister('playlist-editor');
    }

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
        plTitleText.textContent    = '▶ Ver playlist';
        renderList();
    }

    function loadPlaylists() {
        plHomeList.innerHTML = '<div class="pl-home-msg">Cargando...</div>';
        fetch('assets/music/api.php?action=get-playlists')
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
                MelonPlayerState.setPlaylist(pl.id, 0);
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

            var editBtn = makeBtn('☰', 'pl-action-btn', function() { showEditorView(idx); });
            editBtn.title = 'Ver playlist';

            var delBtn;
            if (pl.sharedFrom) {
                delBtn = makeBtn('⊗', 'pl-action-btn', function() {
                    win98Confirm('¿Abandonar la playlist "' + pl.name + '"?', 'Abandonar playlist', function() {
                        fetch('assets/music/api.php?action=leave-playlist', {
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
                });
                delBtn.title = 'Abandonar';
            } else {
                delBtn = makeBtn('✕', 'pl-action-btn', function() {
                    win98Confirm('¿Eliminar la playlist "' + pl.name + '"?', 'Eliminar playlist', function() {
                        fetch('assets/music/api.php?action=delete-playlist', {
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

    window.updatePlaylistPlayingHighlight = function(playlistId, trackIndex) {
        if (!allPlaylists[editingPlIdx] || allPlaylists[editingPlIdx].id !== playlistId) return;
        document.querySelectorAll('#pl-list .pl-item').forEach(function(row, i) {
            row.classList.toggle('pl-item--playing', i === trackIndex);
        });
    };

    function renderList() {
        plList.innerHTML = '';
        var isCurrentPl = allPlaylists[editingPlIdx] && allPlaylists[editingPlIdx].id === currentPlaylistId;
        editList.forEach(function(track, i) {
            var row = document.createElement('div');
            row.className = 'pl-item' + (isCurrentPl && i === currentTrack ? ' pl-item--playing' : '');
            row.draggable = true;
            row.dataset.index = i;

            var handle = document.createElement('div');
            handle.className = 'pl-drag-handle';
            handle.textContent = '⠿';

            var thumbWrap = document.createElement('div');
            thumbWrap.className = 'pl-item-thumb-wrap';
            var thumbImg = document.createElement('img');
            thumbImg.src = track.videoId ? 'https://img.youtube.com/vi/' + track.videoId + '/mqdefault.jpg' : '';
            thumbImg.alt = '';
            thumbImg.width = 44;
            thumbImg.height = 44;
            thumbWrap.appendChild(thumbImg);

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
            (function(trackIndex) {
                var playTrackBtn = makeBtn('▶', 'pl-action-btn', function() {
                    var pl = allPlaylists[editingPlIdx];
                    if (!pl || !pl.tracks.length) return;
                    currentPlaylistId = pl.id;
                    currentPlaylistHasCollabs = !!(pl.sharedFrom || (pl.collaborators && pl.collaborators.length > 0));
                    MelonPlayerState.setPlaylist(pl.id, trackIndex);
                    playlist.length = 0;
                    pl.tracks.forEach(function(t) { playlist.push(t); });
                    currentTrack = trackIndex;
                    updateTrackUI(trackIndex);
                    updatePlayerTitle(pl.name);
                    if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                        ytPlayer.loadVideoById(playlist[trackIndex].videoId);
                    }
                });
                playTrackBtn.title = 'Reproducir desde aquí';
                btnsDiv.appendChild(playTrackBtn);
            })(i);
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

            row.addEventListener('contextmenu', function(e) { showTrackCtxMenu(e, track); });

            row.appendChild(handle);
            row.appendChild(thumbWrap);
            row.appendChild(infoDiv);
            row.appendChild(btnsDiv);
            plList.appendChild(row);
        });
    }

    /* ──── Context menu: añadir a otra playlist ──── */
    var ctxMenu = document.createElement('div');
    ctxMenu.className = 'window';
    ctxMenu.setAttribute('data-no-auto-z', '');   // popup: z fijo, siempre encima
    ctxMenu.style.cssText = 'display:none;position:fixed;z-index:9999;padding:2px 0;min-width:160px;';
    document.body.appendChild(ctxMenu);

    var addToPicker = document.createElement('div');
    addToPicker.className = 'window';
    addToPicker.setAttribute('data-no-auto-z', '');
    addToPicker.style.cssText = 'display:none;position:fixed;z-index:10000;min-width:190px;';
    addToPicker.innerHTML =
        '<div class="title-bar"><div class="title-bar-text">Añadir a playlist</div>' +
        '<div class="title-bar-controls"><button aria-label="Close" id="add-to-picker-close"></button></div></div>' +
        '<div class="window-body" style="padding:4px;max-height:200px;overflow-y:auto;" id="add-to-picker-list"></div>';
    document.body.appendChild(addToPicker);

    document.getElementById('add-to-picker-close').addEventListener('click', function() {
        addToPicker.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest || (!e.target.closest('#pl-ctx-menu-el') && !e.target.closest('#add-to-picker-close'))) {
            ctxMenu.style.display = 'none';
        }
    });
    ctxMenu.id = 'pl-ctx-menu-el';

    function showAddToPicker(anchorX, anchorY, track) {
        var pickerList = document.getElementById('add-to-picker-list');
        pickerList.innerHTML = '';
        var currentId = editingPlIdx >= 0 && allPlaylists[editingPlIdx] ? allPlaylists[editingPlIdx].id : null;
        var targets = allPlaylists.filter(function(pl) { return pl.id !== currentId; });

        if (!targets.length) {
            pickerList.innerHTML = '<div style="font-size:11px;padding:4px;color:#808080;">No hay otras playlists propias.</div>';
        } else {
            targets.forEach(function(pl) {
                var item = document.createElement('div');
                item.className = 'pl-menu-item';
                item.textContent = pl.name;
                item.addEventListener('click', function() {
                    addToPicker.style.display = 'none';
                    var already = pl.tracks.some(function(t) { return t.videoId === track.videoId; });
                    if (!already) pl.tracks.push(Object.assign({}, track));
                    fetch('assets/music/api.php?action=save-playlist-item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: pl.id, name: pl.name, tracks: pl.tracks })
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        if (data.error) alert(data.error);
                    }).catch(function() {});
                });
                pickerList.appendChild(item);
            });
        }

        addToPicker.style.display = 'block';
        var pw = addToPicker.offsetWidth, ph = addToPicker.offsetHeight;
        addToPicker.style.left = Math.min(anchorX, window.innerWidth  - pw - 8) + 'px';
        addToPicker.style.top  = Math.min(anchorY, window.innerHeight - ph - 8) + 'px';
    }

    function showTrackCtxMenu(e, track) {
        e.preventDefault();
        ctxMenu.innerHTML = '';
        var ex = e.clientX, ey = e.clientY;

        var addPl = document.createElement('div');
        addPl.className = 'pl-menu-item';
        addPl.textContent = '➕ Añadir a otra playlist';
        addPl.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            showAddToPicker(ex, ey, track);
        });
        ctxMenu.appendChild(addPl);

        var addProfile = document.createElement('div');
        addProfile.className = 'pl-menu-item';
        addProfile.textContent = '🎵 Añadir a mi perfil';
        addProfile.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            if (typeof window.profileAddTrackAndReview === 'function') {
                window.profileAddTrackAndReview(track);
            }
        });
        ctxMenu.appendChild(addProfile);

        ctxMenu.style.display = 'block';
        var cw = ctxMenu.offsetWidth, ch = ctxMenu.offsetHeight;
        ctxMenu.style.left = Math.min(ex, window.innerWidth  - cw - 8) + 'px';
        ctxMenu.style.top  = Math.min(ey, window.innerHeight - ch - 8) + 'px';
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
        fetch('assets/music/api.php?action=save-playlist-item', {
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
            var newPl = { id: 'pl_' + Date.now(), name: name, tracks: [], collaborators: [] };
            fetch('assets/music/api.php?action=save-playlist-item', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newPl)
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                if (data.error) { alert(data.error); return; }
                /* El backend asigna el ID definitivo (BIGINT) → reemplazar
                   el placeholder pl_<timestamp> antes de meterlo en memoria
                   para que los próximos save/delete usen el ID real. */
                if (data.id) newPl.id = data.id;
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
            importBtn.style.pointerEvents = ''; importBtn.classList.remove('btn-busy');
            importDlg.style.display = 'block';
        });

        function closeImport() { importDlg.style.display = 'none'; }
        document.getElementById('import-pl-close').addEventListener('click', closeImport);
        document.getElementById('import-pl-cancel').addEventListener('click', closeImport);

        importBtn.addEventListener('click', function() {
            var url = importUrl.value.trim();
            if (!url) return;
            importStat.textContent = 'Importando...';
            importBtn.style.pointerEvents = 'none'; importBtn.classList.add('btn-busy');
            fetch('assets/music/api.php?action=import-playlist', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url })
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                importBtn.style.pointerEvents = ''; importBtn.classList.remove('btn-busy');
                if (data.error) { importStat.textContent = 'Error: ' + data.error; return; }
                var importer = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
                data.tracks.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                renderList();
                importStat.textContent = '';
                closeImport();
            })
            .catch(function(e) {
                importBtn.style.pointerEvents = ''; importBtn.classList.remove('btn-busy');
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
                    return fetch('assets/music/api.php?action=yt-search-batch', {
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

        /* ─── Importar de Tidal (API oficial → resolver a YouTube) ─── */
        var tidalDlg  = document.getElementById('tidal-import-dialog');
        var tidalUrl  = document.getElementById('tidal-import-url');
        var tidalStat = document.getElementById('tidal-import-status');
        var tidalBtn  = document.getElementById('tidal-import-submit');
        var tidalWrap = document.getElementById('tidal-progress-wrap');
        var tidalFill = document.getElementById('tidal-progress-fill');
        var tidalText = document.getElementById('tidal-progress-text');
        var tidalAbort = null;

        function tidalBusy(on) {
            tidalBtn.style.pointerEvents = on ? 'none' : '';
            tidalBtn.classList.toggle('btn-busy', on);
        }
        function openTidalDialog() {
            moreMenu.style.display = 'none';
            tidalUrl.value = '';
            tidalStat.textContent = ''; tidalStat.style.color = '';
            tidalWrap.style.display = 'none';
            tidalFill.style.width = '0%';
            tidalBusy(false);
            tidalDlg.style.display = 'block';
        }
        document.getElementById('pl-more-tidal').addEventListener('click', openTidalDialog);

        function closeTidalImport() {
            if (tidalAbort) { tidalAbort.abort(); tidalAbort = null; }
            tidalBusy(false);
            tidalWrap.style.display = 'none';
            tidalDlg.style.display = 'none';
        }
        document.getElementById('tidal-import-close').addEventListener('click', closeTidalImport);
        document.getElementById('tidal-import-cancel').addEventListener('click', closeTidalImport);

        /* Resuelve [{title,artist,duration}] a YouTube por lotes con progreso. */
        function tidalResolveBatches(tracks, signal) {
            var BATCH = 10, total = tracks.length, found = 0, results = [];
            tidalWrap.style.display = 'block';
            tidalFill.style.width = '0%';
            tidalText.textContent = '0 encontradas · ' + total + ' restantes';
            function nextBatch(offset) {
                if (signal.aborted || offset >= total) return Promise.resolve();
                var chunk = tracks.slice(offset, offset + BATCH);
                return fetch('assets/music/api.php?action=yt-search-batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tracks: chunk }),
                    signal: signal
                })
                .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(function(data) {
                    if (data.tracks) { results = results.concat(data.tracks); found += data.tracks.length; }
                    var done = Math.min(offset + BATCH, total);
                    tidalFill.style.width = Math.round(done / total * 100) + '%';
                    tidalText.textContent = found + ' encontradas · ' + (total - done) + ' restantes';
                    return nextBatch(offset + BATCH);
                });
            }
            return nextBatch(0).then(function() { return results; });
        }

        document.getElementById('tidal-import-submit').addEventListener('click', function() {
            var url = tidalUrl.value.trim();
            if (!url) { tidalStat.textContent = 'Pega un enlace de Tidal.'; return; }
            tidalStat.style.color = '';
            var importer   = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
            var isTrack    = /tidal\.com\/(?:browse\/)?track\/\d+/i.test(url);
            var isPlaylist = /tidal\.com\/(?:browse\/)?playlist\/[0-9a-f-]{8,}/i.test(url);

            /* Canción suelta */
            if (isTrack && !isPlaylist) {
                tidalBusy(true);
                tidalStat.textContent = 'Buscando en Tidal…';
                fetch('assets/music/api.php?action=tidal-track&url=' + encodeURIComponent(url))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    tidalBusy(false);
                    if (data.error) { tidalStat.textContent = data.error; tidalStat.style.color = '#c00'; return; }
                    editList.push({ videoId: data.videoId, title: data.title, artist: data.artist, duration: data.duration, addedBy: importer });
                    renderList();
                    closeTidalImport();
                })
                .catch(function() { tidalBusy(false); tidalStat.textContent = 'Error de conexión'; tidalStat.style.color = '#c00'; });
                return;
            }

            if (!isPlaylist) { tidalStat.textContent = 'Enlace no reconocido (canción o playlist de Tidal).'; tidalStat.style.color = '#c00'; return; }

            /* Playlist: leer de Tidal y resolver a YouTube por lotes */
            tidalBusy(true);
            tidalStat.textContent = 'Leyendo playlist de Tidal…';
            tidalAbort = new AbortController();
            var signal = tidalAbort.signal;
            fetch('assets/music/api.php?action=tidal-playlist', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url }),
                signal: signal
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                if (data.error) throw new Error(data.error);
                var plName = data.name || '';
                var tracks = data.tracks || [];
                if (!tracks.length) throw new Error('La playlist no tiene canciones.');
                tidalStat.textContent = '';
                return tidalResolveBatches(tracks, signal).then(function(results) {
                    if (signal.aborted) return;
                    tidalAbort = null;
                    tidalBusy(false);
                    tidalWrap.style.display = 'none';
                    if (!results.length) { tidalStat.textContent = 'No se encontraron vídeos en YouTube.'; tidalStat.style.color = '#c00'; return; }
                    var nameInput = document.getElementById('pl-name-input');
                    if (nameInput && !nameInput.value.trim() && plName) nameInput.value = plName;
                    results.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                    renderList();
                    closeTidalImport();
                });
            })
            .catch(function(e) {
                if (e.name === 'AbortError') return;
                tidalAbort = null;
                tidalBusy(false);
                tidalWrap.style.display = 'none';
                tidalStat.textContent = 'Error: ' + e.message;
                tidalStat.style.color = '#c00';
            });
        });

        /* Colaborador */
        var collabDlg      = document.getElementById('collab-dialog');
        var collabPlName   = document.getElementById('collab-pl-name');
        var collabUserList = document.getElementById('collab-user-list');
        var collabStatus   = document.getElementById('collab-status');

        function openCollabDialog() {
            moreMenu.style.display = 'none';
            if (editingPlIdx < 0) return;
            var pl = allPlaylists[editingPlIdx];
            if (pl.sharedFrom) { alert('Solo el propietario puede gestionar colaboradores.'); return; }
            collabPlName.textContent = pl.name;
            collabStatus.textContent = '';
            collabUserList.innerHTML = '<div style="font-size:11px;color:#808080;">Cargando...</div>';
            collabDlg.style.display = 'block';
            fetch('assets/music/api.php?action=get-users')
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

                    if (already) {
                        var removeBtn = makeBtn('Eliminar', 'collab-invite-btn', function() {
                            collabStatus.textContent = 'Eliminando...';
                            removeBtn.disabled = true;
                            fetch('assets/music/api.php?action=remove-collaborator', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ playlistId: pl.id, collaborator: u.key })
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.error) { collabStatus.textContent = data.error; removeBtn.disabled = false; return; }
                                if (pl.collaborators) {
                                    pl.collaborators = pl.collaborators.filter(function(c) { return c !== u.key; });
                                }
                                collabStatus.textContent = u.label + ' eliminado de la playlist.';
                                row.remove();
                            })
                            .catch(function(e) { collabStatus.textContent = 'Error: ' + e.message; removeBtn.disabled = false; });
                        });
                        row.appendChild(avatarWrap);
                        row.appendChild(nameSpan);
                        row.appendChild(removeBtn);
                    } else {
                        var invBtn = makeBtn('Invitar', 'collab-invite-btn', function() {
                            collabStatus.textContent = 'Enviando...';
                            invBtn.disabled = true;
                            fetch('assets/music/api.php?action=invite-collaborator', {
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
                        row.appendChild(avatarWrap);
                        row.appendChild(nameSpan);
                        row.appendChild(invBtn);
                    }
                    collabUserList.appendChild(row);
                });
            })
            .catch(function(e) {
                collabUserList.innerHTML = '<div style="font-size:11px;color:#c00;">Error: ' + e.message + '</div>';
            });
        }

        document.getElementById('pl-more-collab').addEventListener('click', openCollabDialog);

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
        fetch('assets/music/api.php?action=get-playlists')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!Array.isArray(data) || data.error) return;
            allPlaylists = data;
            if (plHome.style.display !== 'none') renderHome();
        })
        .catch(function() {});
    }, 15000);
})();

function relTime(sentAt) {
    if (!sentAt) return '';
    var diff = Math.floor(Date.now() / 1000) - sentAt;
    if (diff < 60)   return 'ahora mismo';
    if (diff < 3600) return 'hace ' + Math.floor(diff / 60) + ' min';
    return 'hace ' + Math.floor(diff / 3600) + 'h';
}

/* Notificaciones del reproductor (playlist invites / collab) */
(function() {
    function serverPost(id, action) {
        return fetch('assets/music/api.php?action=respond-invite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inviteId: id, action: action })
        });
    }

    var TITLES = {
        'removed':          'Eliminado de playlist',
        'collab-accepted':  'Colaboración aceptada',
        'collab-rejected':  'Colaboración rechazada',
        'collab-left':      'Colaborador ha salido'
    };

    function messageFor(inv) {
        switch (inv.type) {
            case 'removed':         return inv.fromLabel + ' te ha eliminado de "' + inv.playlistName + '"';
            case 'collab-accepted': return inv.fromLabel + ' ha aceptado tu solicitud de colaboración en "' + inv.playlistName + '"';
            case 'collab-rejected': return inv.fromLabel + ' ha rechazado tu solicitud de colaboración en "' + inv.playlistName + '"';
            case 'collab-left':     return inv.fromLabel + ' ha abandonado tu playlist "' + inv.playlistName + '"';
            default:                return inv.fromLabel + ' te invita a colaborar en "' + inv.playlistName + '"';
        }
    }

    function pushInvite(inv) {
        if (!window.notifSystem || window.notifSystem.isShown(inv.id) || window.notifSystem.isDismissed(inv.id)) return;
        var INFO_TYPES = { 'removed': true, 'collab-accepted': true, 'collab-rejected': true, 'collab-left': true };
        var isAction = !INFO_TYPES[inv.type];
        var senderImg = (typeof usersInfo !== 'undefined' && inv.fromUser && usersInfo[inv.fromUser]) ? usersInfo[inv.fromUser].img : null;

        if (isAction) {
            window.notifSystem.show({
                id:       inv.id,
                type:     'action',
                title:    TITLES[inv.type] || 'Invitación de playlist',
                message:  messageFor(inv),
                senderImage: senderImg,
                sentAt:   inv.sentAt,
                onAccept: function() {
                    serverPost(inv.id, 'accept')
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d && d.error) { alert(d.error); return; }
                            if (typeof refreshPlaylists === 'function') refreshPlaylists();
                        })
                        .catch(function() {});
                },
                onReject: function() { serverPost(inv.id, 'reject').catch(function(){}); }
            });
        } else {
            window.notifSystem.show({
                id:      inv.id,
                type:    'info',
                title:   TITLES[inv.type] || 'Notificación',
                message: messageFor(inv),
                sentAt:  inv.sentAt,
                onAutoDismiss: function() { serverPost(inv.id, 'dismiss').catch(function(){}); }
            });
        }
    }

    function connectSSE() {
        var es = new EventSource('assets/music/notifications-stream.php');
        es.onmessage = function(e) {
            try {
                var data = JSON.parse(e.data);
                if (!Array.isArray(data)) return;
                data.sort(function(a, b) { return (a.sentAt || 0) - (b.sentAt || 0); });
                data.forEach(pushInvite);
            } catch(err) {}
        };
        es.onerror = function() { es.close(); setTimeout(connectSSE, 3000); };
    }

    connectSSE();
})();
<?php endif; ?>
</script>
