<?php
if (!isset($desktopLabel)) { header('Location: index.php'); exit; }
header('Content-Type: text/html; charset=UTF-8');

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

$hasPlayer = in_array($desktopUserKey, ['user1', 'user2']);

$_iconTheme = $desktopUserKey === 'user1' ? 'capi' : 'angie';
function desktopIcon($name, $emoji) {
    global $_iconTheme;
    foreach ([$_iconTheme, 'default'] as $dir) {
        foreach (['png', 'jpg', 'gif'] as $ext) {
            $rel  = "assets/img/icons/{$dir}/{$name}.{$ext}";
            if (file_exists(__DIR__ . '/' . $rel)) {
                return '<img src="' . $rel . '" style="width:32px;height:32px;object-fit:contain;image-rendering:pixelated;">';
            }
        }
    }
    return $emoji;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($desktopLabel); ?> - Escritorio</title>
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/reproductor.css">
    <link rel="stylesheet" href="assets/css/perfil.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>

<body class="<?php echo $desktopUserKey === 'user1' ? 'capi' : 'angie'; ?>"<?php echo $wallpaperStyle ? " style=\"{$wallpaperStyle}\"" : ''; ?>>

<div id="page-enter"></div>
<div id="desktop">
    <div class="desktop-icon" id="archive-icon">
        <div class="desktop-icon-img"><?php echo desktopIcon('archive', '📼'); ?></div>
        <span>MelonArchive</span>
    </div>
    <div class="desktop-icon" id="calendar-icon">
        <div class="desktop-icon-img"><?php echo desktopIcon('calendar', '📅'); ?></div>
        <span>Calendario</span>
    </div>
    <div class="desktop-icon" id="profile-icon">
        <div class="desktop-icon-img"><?php echo desktopIcon('profile', '👤'); ?></div>
        <span>Perfil</span>
    </div>
</div>

<!-- CALENDAR WINDOW -->
<div class="window" id="calendar-window" style="display:none; position:fixed; left:5vw; top:4vh; width:90vw; height:88vh; z-index:500; flex-direction:column;">
    <div class="title-bar" id="calendar-titlebar">
        <div class="title-bar-text">📅 Calendario</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="calendar-close"></button>
        </div>
    </div>
    <iframe id="calendar-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- ARCHIVE WINDOW -->
<div class="window" id="archive-window">
    <div class="title-bar" id="archive-titlebar">
        <div class="title-bar-text">📼 MelonArchive</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="archive-close"></button>
        </div>
    </div>
    <iframe id="archive-frame" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- NOTIFICATION STACK -->
<div id="notif-container"></div>


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
    <button class="button" id="tray-player-btn" title="Reproductor">♪▶</button>
    <div id="system-tray">
        <span id="tray-clock">00:00</span>
    </div>
</div>

<?php include __DIR__ . '/apps/reproductor.php'; ?>

<?php include __DIR__ . '/apps/perfil.php'; ?>

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
   SISTEMA UNIFICADO DE NOTIFICACIONES
========================= */
window.notifSystem = (function() {
    var container = document.getElementById('notif-container');
    var MAX_VISIBLE  = 3;
    var shownIds     = {};
    var dismissedIds = {};
    var pendingQueue = [];

    function relTime(sentAt) {
        if (!sentAt) return '';
        var diff = Math.floor(Date.now() / 1000) - sentAt;
        if (diff < 60)   return 'ahora mismo';
        if (diff < 3600) return 'hace ' + Math.floor(diff / 60) + ' min';
        return 'hace ' + Math.floor(diff / 3600) + 'h';
    }

    function updateTimes() {
        container.querySelectorAll('.notif-time[data-sent]').forEach(function(el) {
            el.textContent = relTime(parseInt(el.dataset.sent, 10));
        });
    }

    function removeCard(card) {
        dismissedIds[card.dataset.id] = true;
        delete shownIds[card.dataset.id];
        card.classList.add('notif-card-exiting');
        card.addEventListener('animationend', function() {
            card.remove();
            flushQueue();
        }, { once: true });
    }

    function flushQueue() {
        while (pendingQueue.length && Object.keys(shownIds).length < MAX_VISIBLE) {
            createCard(pendingQueue.shift());
        }
    }

    function createCard(opts) {
        if (!opts || !opts.id) return;
        if (shownIds[opts.id] || dismissedIds[opts.id]) return;
        if (Object.keys(shownIds).length >= MAX_VISIBLE) {
            if (!pendingQueue.some(function(q) { return q.id === opts.id; })) pendingQueue.push(opts);
            return;
        }

        var isAction = opts.type === 'action';

        var card = document.createElement('div');
        card.className = 'window notif-card ' + (isAction ? 'notif-card-action' : 'notif-card-info');
        card.dataset.id = opts.id;

        var tb = document.createElement('div');
        tb.className = 'title-bar';
        var tbText = document.createElement('div');
        tbText.className = 'title-bar-text';
        tbText.textContent = opts.title || 'Notificación';
        tb.appendChild(tbText);
        card.appendChild(tb);

        var body = document.createElement('div');
        body.className = 'window-body';
        body.style.padding = '5px 8px 6px';

        if (isAction && opts.senderImage) {
            var topRow = document.createElement('div');
            topRow.style.cssText = 'display:flex;align-items:center;gap:6px;margin-bottom:4px;';
            var avWrap = document.createElement('div');
            avWrap.className = 'collab-avatar-wrap';
            var avImg = document.createElement('img');
            avImg.className = 'collab-avatar-img';
            avImg.src = opts.senderImage;
            avWrap.appendChild(avImg);
            topRow.appendChild(avWrap);
            var msgSpan = document.createElement('span');
            msgSpan.style.cssText = 'font-size:11px;';
            msgSpan.textContent = opts.message || '';
            topRow.appendChild(msgSpan);
            body.appendChild(topRow);
        } else {
            var msgEl = document.createElement('p');
            msgEl.style.cssText = 'margin:0 0 3px;font-size:11px;';
            msgEl.textContent = opts.message || '';
            body.appendChild(msgEl);
        }

        var timeEl = document.createElement('span');
        timeEl.className = 'notif-time';
        if (opts.sentAt) timeEl.dataset.sent = opts.sentAt;
        timeEl.textContent = relTime(opts.sentAt || 0);
        body.appendChild(timeEl);

        if (isAction) {
            var row = document.createElement('div');
            row.className = 'field-row';
            row.style.cssText = 'justify-content:flex-end;gap:4px;margin-top:5px;';
            var rejectBtn = document.createElement('button');
            rejectBtn.className = 'button'; rejectBtn.textContent = 'Rechazar';
            rejectBtn.addEventListener('click', function() {
                if (typeof opts.onReject === 'function') opts.onReject();
                removeCard(card);
            });
            var acceptBtn = document.createElement('button');
            acceptBtn.className = 'button'; acceptBtn.textContent = 'Aceptar';
            acceptBtn.addEventListener('click', function() {
                if (typeof opts.onAccept === 'function') opts.onAccept();
                removeCard(card);
            });
            row.appendChild(rejectBtn);
            row.appendChild(acceptBtn);
            body.appendChild(row);
        }

        card.appendChild(body);
        container.insertBefore(card, container.firstChild);
        shownIds[opts.id] = card;

        if (!isAction) {
            var delay = (typeof opts.autoDismissAfter === 'number') ? opts.autoDismissAfter : 5000;
            setTimeout(function() {
                if (card.parentNode) {
                    if (typeof opts.onAutoDismiss === 'function') opts.onAutoDismiss();
                    removeCard(card);
                }
            }, delay);
        }
    }

    setInterval(updateTimes, 30000);

    return {
        show:        createCard,
        dismiss:     function(id) { var c = shownIds[id]; if (c) removeCard(c); else dismissedIds[id] = true; },
        isShown:     function(id) { return !!shownIds[id]; },
        isDismissed: function(id) { return !!dismissedIds[id]; }
    };
})();

/* =========================
   ARCHIVE
========================= */
(function() {
    var archFrame  = document.getElementById('archive-frame');
    var archLoaded = false;

    document.getElementById('archive-icon').addEventListener('dblclick', function() {
        if (!archLoaded) { archFrame.src = 'apps/melonarchive.php'; archLoaded = true; }
        if (taskbarManager.isRegistered('archive-window')) {
            taskbarManager.restore('archive-window');
        } else {
            taskbarManager.register('archive-window', 'MelonArchive', '📼', 'flex');
        }
    });

    document.getElementById('archive-close').addEventListener('click', function() {
        taskbarManager.unregister('archive-window');
        try { archFrame.contentWindow.postMessage({ type: 'archive-stop' }, '*'); } catch(e) {}
    });
})();

(function() {
    var calWin    = document.getElementById('calendar-window');
    var calIframe = document.getElementById('calendar-iframe');
    var calLoaded = false;

    document.getElementById('calendar-icon').addEventListener('dblclick', function() {
        if (!calLoaded) { calIframe.src = 'apps/calendario.php'; calLoaded = true; }
        if (taskbarManager.isRegistered('calendar-window')) {
            taskbarManager.restore('calendar-window');
        } else {
            taskbarManager.register('calendar-window', 'Calendario', '📅', 'flex');
        }
    });

    document.getElementById('calendar-close').addEventListener('click', function() {
        taskbarManager.unregister('calendar-window');
    });
})();

/* ──── Player right-click context menu ──── */
(function() {
    var playerMain = document.getElementById('player-main');
    if (!playerMain) return;

    /* Context menu window */
    var ctxMenu = document.createElement('div');
    ctxMenu.className = 'window';
    ctxMenu.style.cssText = 'display:none;position:fixed;z-index:9999;padding:2px 0;min-width:160px;';
    document.body.appendChild(ctxMenu);

    /* Playlist picker window */
    var picker = document.createElement('div');
    picker.className = 'window';
    picker.style.cssText = 'display:none;position:fixed;z-index:10000;min-width:190px;';
    picker.innerHTML =
        '<div class="title-bar" style="cursor:default;">' +
            '<div class="title-bar-text">Añadir a playlist</div>' +
            '<div class="title-bar-controls"><button aria-label="Close" id="player-ctx-picker-close"></button></div>' +
        '</div>' +
        '<div class="window-body" style="padding:4px 0;" id="player-ctx-picker-list"></div>';
    document.body.appendChild(picker);

    document.getElementById('player-ctx-picker-close').addEventListener('click', function() {
        picker.style.display = 'none';
    });

    function closeAll() {
        ctxMenu.style.display = 'none';
        picker.style.display  = 'none';
    }

    function showPicker(ax, ay, track) {
        var listEl = document.getElementById('player-ctx-picker-list');
        listEl.innerHTML = '';
        fetch('assets/music/get-playlists.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var playlists = Array.isArray(data) ? data : [];
                var targets = playlists.filter(function(pl) {
                    return pl.id !== currentPlaylistId;
                });
                if (!targets.length) {
                    listEl.innerHTML = '<div style="padding:4px 8px;font-size:11px;">Sin otras playlists</div>';
                } else {
                    targets.forEach(function(pl) {
                        var item = document.createElement('div');
                        item.className = 'pl-menu-item';
                        item.textContent = pl.name;
                        item.addEventListener('click', function() {
                            picker.style.display = 'none';
                            if (!track) return;
                            var already = (pl.tracks || []).some(function(t) {
                                return t.videoId && t.videoId === track.videoId;
                            });
                            if (already) { alert('Esta canción ya está en "' + pl.name + '"'); return; }
                            var newTrack = { title: track.title, artist: track.artist, videoId: track.videoId, duration: track.duration };
                            pl.tracks = (pl.tracks || []).concat([newTrack]);
                            fetch('assets/music/save-playlist-item.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ playlistId: pl.id, tracks: pl.tracks })
                            });
                        });
                        listEl.appendChild(item);
                    });
                }
                var px = Math.min(ax, window.innerWidth  - 200);
                var py = Math.min(ay, window.innerHeight - picker.offsetHeight - 10);
                picker.style.left    = px + 'px';
                picker.style.top     = py + 'px';
                picker.style.display = 'block';
            })
            .catch(function() {
                listEl.innerHTML = '<div style="padding:4px 8px;font-size:11px;">Error al cargar</div>';
                picker.style.display = 'block';
            });
    }

    playerMain.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        closeAll();
        var track = (typeof playlist !== 'undefined' && typeof currentTrack !== 'undefined')
            ? playlist[currentTrack] : null;
        if (!track) return;

        ctxMenu.innerHTML = '';
        var item = document.createElement('div');
        item.className = 'pl-menu-item';
        item.textContent = '➕ Añadir a otra playlist';
        item.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            showPicker(
                parseInt(ctxMenu.style.left) + ctxMenu.offsetWidth,
                parseInt(ctxMenu.style.top),
                track
            );
        });
        ctxMenu.appendChild(item);

        var listItem = document.createElement('div');
        listItem.className = 'pl-menu-item';
        listItem.textContent = '🎵 Añadir a mi perfil';
        listItem.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            if (typeof window.profileAddTrackAndReview === 'function') window.profileAddTrackAndReview(track);
        });
        ctxMenu.appendChild(listItem);

        var mx = Math.min(e.clientX, window.innerWidth  - 170);
        var my = Math.min(e.clientY, window.innerHeight - 60);
        ctxMenu.style.left    = mx + 'px';
        ctxMenu.style.top     = my + 'px';
        ctxMenu.style.display = 'block';
    });

    document.addEventListener('mousedown', function(e) {
        if (!ctxMenu.contains(e.target) && !picker.contains(e.target)) closeAll();
    });
})();

/* ──── Generic drag + resize for all windows ──── */
(function() {
    var MIN_W = 180, MIN_H = 100;
    var active = null;

    /* Transparent overlay prevents iframes from stealing mouse events */
    var blocker = document.createElement('div');
    blocker.style.cssText = 'position:fixed;inset:0;z-index:99998;display:none;';
    document.body.appendChild(blocker);

    function fixPos(el) {
        var r = el.getBoundingClientRect();
        el.style.position  = 'fixed';
        el.style.left      = r.left + 'px';
        el.style.top       = r.top  + 'px';
        el.style.right     = 'auto';
        el.style.bottom    = 'auto';
        el.style.transform = 'none';
    }

    document.addEventListener('mousemove', function(e) {
        if (!active) return;
        var el = active.el;
        var PAD = 40; /* min pixels visible from each screen edge */
        var VW = window.innerWidth, VH = window.innerHeight;

        if (active.mode === 'drag') {
            var newL = e.clientX - active.ox;
            var newT = e.clientY - active.oy;
            var w = el.offsetWidth, h = el.offsetHeight;
            newL = Math.max(PAD - w, Math.min(VW - PAD, newL));
            newT = Math.max(0,       Math.min(VH - PAD, newT));
            el.style.left = newL + 'px';
            el.style.top  = newT + 'px';
        } else {
            var dx = e.clientX - active.sx, dy = e.clientY - active.sy;
            var d = active.dir;
            var MAX_W = VW - PAD * 2, MAX_H = VH - PAD * 2;
            if (d.indexOf('e') !== -1) el.style.width  = Math.min(MAX_W, Math.max(MIN_W, active.sw + dx)) + 'px';
            if (d.indexOf('s') !== -1) el.style.height = Math.min(MAX_H, Math.max(MIN_H, active.sh + dy)) + 'px';
            if (d.indexOf('w') !== -1) {
                var nw = Math.min(MAX_W, Math.max(MIN_W, active.sw - dx));
                el.style.width = nw + 'px';
                el.style.left  = (active.sl + active.sw - nw) + 'px';
            }
            if (d.indexOf('n') !== -1) {
                var nh = Math.min(MAX_H, Math.max(MIN_H, active.sh - dy));
                el.style.height = nh + 'px';
                el.style.top    = (active.st + active.sh - nh) + 'px';
            }
        }
    });

    document.addEventListener('mouseup', function() {
        if (active) { blocker.style.display = 'none'; active = null; }
    });

    function setup(id, dragOnly) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.add('win-draggable');
        if (!dragOnly) el.classList.add('win-managed');

        /* Drag via title bar */
        var titleBar = el.querySelector('.title-bar');
        if (titleBar) {
            titleBar.addEventListener('mousedown', function(e) {
                if (e.button !== 0 || e.target.closest('.title-bar-controls')) return;
                e.preventDefault();
                fixPos(el);
                var r = el.getBoundingClientRect();
                blocker.style.display = 'block';
                active = { el: el, mode: 'drag', ox: e.clientX - r.left, oy: e.clientY - r.top };
            });
        }

        if (dragOnly) return;

        /* Resize handles injected around the border */
        ['n','s','e','w','ne','nw','se','sw'].forEach(function(d) {
            var h = document.createElement('div');
            h.className = 'win-handle win-handle-' + d;
            h.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return;
                e.preventDefault();
                e.stopPropagation();
                fixPos(el);
                var r = el.getBoundingClientRect();
                blocker.style.display = 'block';
                active = { el: el, mode: 'resize', dir: d,
                           sx: e.clientX, sy: e.clientY,
                           sw: r.width,   sh: r.height,
                           sl: r.left,    st: r.top };
            });
            el.appendChild(h);
        });
    }

    ['calendar-window','archive-window','playlist-editor',
     'create-playlist-dialog','profile-window'].forEach(function(id) { setup(id, false); });
    ['music-player','add-track-dialog','import-playlist-dialog',
     'collab-dialog','spotify-import-dialog','confirm-dialog',
     'profile-add-dialog','profile-review-prompt','profile-review-window',
     'profile-review-view','profile-invite-dialog','profile-info-edit-dialog',
     'music-add-dialog','profile-notifs-window','profile-melon-details-window','profile-chat-window'].forEach(function(id) { setup(id, true); });
})();

/* ──── Window minimize / maximize ──── */
(function() {
    var ids = [
        'calendar-window', 'archive-window',
        'create-playlist-dialog', 'profile-window'
    ];
    ids.forEach(function(id) {
        var win = document.getElementById(id);
        if (!win) return;
        var minBtn = win.querySelector('[aria-label="Minimize"]');
        var maxBtn = win.querySelector('[aria-label="Maximize"]');

        if (minBtn) {
            minBtn.addEventListener('click', function() {
                taskbarManager.minimize(id);
            });
        }

        if (maxBtn) {
            maxBtn.addEventListener('click', function() {
                if (win.classList.contains('win-maximized')) {
                    win.classList.remove('win-maximized');
                    maxBtn.setAttribute('aria-label', 'Maximize');
                } else {
                    win.classList.add('win-maximized');
                    maxBtn.setAttribute('aria-label', 'Restore');
                }
            });
        }
    });
})();
</script>

</body>
</html>
