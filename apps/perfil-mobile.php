<?php
/* ──────────────────────────────────────────────────────────────────────
   PERFIL — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Entrada independiente del escritorio: NO se incluye desde
   desktop-base.php y no depende de $desktopLabel / appTitleIcon().
   Lee toda la información vía /assets/profile/api.php:
     - get-profile  → cabecera (descripción, quote)
     - get-lists    → 5 categorías (movies/series/books/games/music)
   UI: cabecera + pestañas horizontales scroll + lista vertical.
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

/* Avatar del usuario logueado */
$userImg = '';
$safe = preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel);
foreach (['png','jpg','jpeg','gif'] as $ext) {
    if (file_exists(dirname(__DIR__) . "/assets/img/{$safe}.{$ext}")) {
        $userImg = "../assets/img/{$safe}.{$ext}";
        break;
    }
}
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
    <title>Perfil — Scrapbook Melon</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/css/mobile.css">
    <style>
        /* ── Cabecera Perfil ── */
        .pf-cover {
            background: linear-gradient(180deg, #1a3a6e 0%, #0c2b54 100%);
            padding: 18px 16px 14px;
            display: flex;
            gap: 14px;
            align-items: center;
            border-bottom: 1px dashed rgba(255,255,255,0.18);
        }
        .pf-cover .pf-avatar {
            width: 72px; height: 72px;
            border-radius: 12px;
            border: 2px solid #5bd4ff;
            object-fit: cover;
            image-rendering: pixelated;
            flex-shrink: 0;
            background: rgba(255,255,255,0.08);
        }
        .pf-cover-info { flex: 1; min-width: 0; }
        .pf-cover-name {
            font-size: 22px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 0.4px;
        }
        .pf-cover-quote {
            font-size: 12px;
            color: #9bc1ed;
            font-style: italic;
            margin-top: 4px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        /* ── Pestañas categoría (scroll horizontal) ── */
        .pf-tabs {
            display: flex;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: rgba(0,0,0,0.4);
            border-bottom: 1px solid rgba(255,255,255,0.10);
            scrollbar-width: none;
            flex-shrink: 0;
        }
        .pf-tabs::-webkit-scrollbar { display: none; }
        .pf-tab {
            padding: 12px 16px;
            color: #9bc1ed;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        .pf-tab.active {
            color: #5bd4ff;
            border-bottom-color: #5bd4ff;
        }
        .pf-tab .pf-tab-count {
            font-size: 10px;
            background: rgba(91,212,255,0.18);
            color: #5bd4ff;
            padding: 1px 6px;
            border-radius: 8px;
            margin-left: 6px;
            font-weight: bold;
        }
        /* ── Lista de items ── */
        .pf-list {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 4px 0 16px;
        }
        .pf-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px dotted rgba(255,255,255,0.10);
            min-height: 60px;
        }
        .pf-item-poster {
            width: 44px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            background: rgba(255,255,255,0.08);
            flex-shrink: 0;
        }
        .pf-item.music .pf-item-poster {
            width: 48px;
            height: 48px;
            border-radius: 6px;
        }
        .pf-item-poster.placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #5bd4ff;
        }
        .pf-item-info { flex: 1; min-width: 0; }
        .pf-item-title {
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .pf-item-sub {
            color: #9bc1ed;
            font-size: 12px;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pf-status {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .pf-status.done    { background: #2da34c; color: #fff; }
        .pf-status.doing   { background: #4d9aff; color: #fff; }
        .pf-status.pending { background: #6b7a8a; color: #fff; }
        .pf-status.dropped { background: #c0392b; color: #fff; }
        .pf-stars {
            color: #f4c130;
            font-size: 12px;
            letter-spacing: 1px;
        }
        .pf-shared-from {
            color: #5bd4ff;
            font-size: 10px;
            background: rgba(91,212,255,0.12);
            padding: 1px 6px;
            border-radius: 6px;
        }
        /* Empty state */
        .pf-empty {
            text-align: center;
            color: #6b8db5;
            padding: 60px 24px 40px;
            font-size: 13px;
        }
        .pf-empty .big { font-size: 48px; opacity: 0.4; margin-bottom: 12px; }
        .pf-loading {
            text-align: center;
            color: #5bd4ff;
            padding: 40px 24px;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div id="screen">

    <!-- Status bar -->
    <div id="status-bar">
        <div class="status-left">
            <a href="../mobile.php" style="color:#5bd4ff;text-decoration:none;font-weight:bold;">‹ Menú</a>
        </div>
        <div class="status-right">
            <span id="status-clock">--:--</span>
            <span class="battery">▮▮▮</span>
        </div>
    </div>

    <!-- Cabecera con avatar + nombre + quote -->
    <header class="pf-cover">
        <?php if ($userImg): ?>
            <img class="pf-avatar" src="<?= htmlspecialchars($userImg) ?>" alt="">
        <?php else: ?>
            <div class="pf-avatar pf-item-poster placeholder">👤</div>
        <?php endif; ?>
        <div class="pf-cover-info">
            <div class="pf-cover-name"><?= htmlspecialchars($userLabel) ?></div>
            <div class="pf-cover-quote" id="pf-cover-quote">—</div>
        </div>
    </header>

    <!-- Pestañas categoría -->
    <nav class="pf-tabs" id="pf-tabs">
        <button class="pf-tab active" data-cat="movies">🎬 Películas <span class="pf-tab-count" id="cnt-movies">·</span></button>
        <button class="pf-tab" data-cat="series">📺 Series <span class="pf-tab-count" id="cnt-series">·</span></button>
        <button class="pf-tab" data-cat="books">📖 Libros <span class="pf-tab-count" id="cnt-books">·</span></button>
        <button class="pf-tab" data-cat="games">🎮 Juegos <span class="pf-tab-count" id="cnt-games">·</span></button>
        <button class="pf-tab" data-cat="music">🎵 Música <span class="pf-tab-count" id="cnt-music">·</span></button>
    </nav>

    <!-- Lista que reacciona al tab activo -->
    <main class="pf-list" id="pf-list">
        <div class="pf-loading">Cargando…</div>
    </main>

</div>

<script>
/* ─── Reloj de la status-bar ────────────────────────────────────── */
(function() {
    var clockEl = document.getElementById('status-clock');
    function pad(n){ return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var d = new Date();
        clockEl.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    tick(); setInterval(tick, 15000);
})();

/* ─── Estado y carga de datos ──────────────────────────────────── */
var API = '../assets/profile/api.php';
var STATE = { lists: null, profile: null, current: 'movies' };

function fetchProfile() {
    return fetch(API + '?action=get-profile', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            STATE.profile = d || {};
            var q = (d && d.quote) ? d.quote : '';
            var c = document.getElementById('pf-cover-quote');
            c.textContent = q || 'Sin descripción';
            c.style.fontStyle = q ? 'italic' : 'normal';
        })
        .catch(function(){
            document.getElementById('pf-cover-quote').textContent = '(error cargando perfil)';
        });
}

function fetchLists() {
    return fetch(API + '?action=get-lists', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            STATE.lists = d || {};
            ['movies','series','books','games','music'].forEach(function(cat){
                var arr = STATE.lists[cat] || [];
                document.getElementById('cnt-' + cat).textContent = arr.length;
            });
            renderActiveList();
        })
        .catch(function(){
            document.getElementById('pf-list').innerHTML =
                '<div class="pf-empty"><div class="big">⚠️</div>Error cargando listas</div>';
        });
}

/* ─── Render ───────────────────────────────────────────────────── */
function statusLabel(s) {
    return { done: 'Terminado', doing: 'En curso', pending: 'Pendiente', dropped: 'Abandonado' }[s] || s;
}
function statusClass(s) {
    return ['done','doing','pending','dropped'].indexOf(s) >= 0 ? s : 'pending';
}
function stars(n) {
    var full = Math.round(n);
    return '★★★★★☆☆☆☆☆'.substr(5 - full, 5);
}
function esc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderActiveList() {
    var listEl = document.getElementById('pf-list');
    var arr = (STATE.lists && STATE.lists[STATE.current]) || [];
    if (!arr.length) {
        var emoji = STATE.current === 'movies' ? '🎬'
                  : STATE.current === 'series' ? '📺'
                  : STATE.current === 'books'  ? '📖'
                  : STATE.current === 'games'  ? '🎮'
                  : '🎵';
        listEl.innerHTML = '<div class="pf-empty"><div class="big">' + emoji + '</div>Sin elementos en esta lista</div>';
        return;
    }
    var html = '';
    arr.forEach(function(it){
        var isMusic = (STATE.current === 'music');
        var posterHtml = it.image
            ? '<img class="pf-item-poster" src="' + esc(it.image) + '" alt="" loading="lazy">'
            : '<div class="pf-item-poster placeholder">' +
              (isMusic ? '🎵' : STATE.current === 'movies' ? '🎬'
                : STATE.current === 'series' ? '📺'
                : STATE.current === 'books'  ? '📖' : '🎮') +
              '</div>';
        var subParts = [];
        if (isMusic && it.artist) subParts.push('<span>' + esc(it.artist) + '</span>');
        if (!isMusic && it.status) {
            subParts.push('<span class="pf-status ' + statusClass(it.status) + '">' +
                          esc(statusLabel(it.status)) + '</span>');
        }
        if (it.review && it.review.stars) {
            subParts.push('<span class="pf-stars">' + stars(it.review.stars) + '</span>');
        }
        if (it.sharedFrom) {
            subParts.push('<span class="pf-shared-from">compartido</span>');
        }
        html += '<div class="pf-item' + (isMusic ? ' music' : '') + '">' +
                  posterHtml +
                  '<div class="pf-item-info">' +
                    '<div class="pf-item-title">' + esc(it.title) + '</div>' +
                    (subParts.length ? '<div class="pf-item-sub">' + subParts.join('') + '</div>' : '') +
                  '</div>' +
                '</div>';
    });
    listEl.innerHTML = html;
}

/* ─── Tab switching ────────────────────────────────────────────── */
document.getElementById('pf-tabs').addEventListener('click', function(e){
    var btn = e.target.closest('.pf-tab');
    if (!btn) return;
    var cat = btn.dataset.cat;
    if (cat === STATE.current) return;
    STATE.current = cat;
    document.querySelectorAll('.pf-tab').forEach(function(b){
        b.classList.toggle('active', b.dataset.cat === cat);
    });
    renderActiveList();
});

/* ─── Bootstrap ─────────────────────────────────────────────────── */
fetchProfile();
fetchLists();
</script>

</body>
</html>
