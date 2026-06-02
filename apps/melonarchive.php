<?php
session_start();
require_once dirname(__DIR__) . '/assets/config.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../index.php');
    exit;
}
$userLabel = $loginUsers[$userKey]['label'];

/* Tema activo del usuario */
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = '../' . themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(dirname(__DIR__) . '/' . themeCssRelPath($activeTheme, $userLabel))) $activeThemeCss = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="../assets/js/pwa-guard.js"></script>
    <title>MelonArchive</title>
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
    <link rel="stylesheet" href="../assets/css/melonarchive.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
</head>
<body class="<?php
    $bc = [];
    if ($activeThemeClass) $bc[] = $activeThemeClass;
    echo htmlspecialchars(implode(' ', $bc));
?>">

<div id="archive-root">
    <div id="archive-toolbar">
        <button class="button" id="archive-back" disabled>◄ Atrás</button>
        <span id="archive-breadcrumb">Playlists</span>
    </div>
    <div id="archive-body">
        <div id="archive-content">
            <div id="archive-status"></div>
            <div id="archive-grid"></div>
        </div>
        <div id="archive-player">
            <iframe id="archive-iframe" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    </div>
</div>

<script>
const archiveBack       = document.getElementById('archive-back');
const archiveBreadcrumb = document.getElementById('archive-breadcrumb');
const archiveGrid       = document.getElementById('archive-grid');
const archiveStatus     = document.getElementById('archive-status');
const archiveIframe     = document.getElementById('archive-iframe');
const archiveContent    = document.getElementById('archive-content');
const archivePlayer     = document.getElementById('archive-player');
let archiveView = 'playlists';

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
        const res  = await fetch('../assets/yt-archive.php?action=playlists');
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
        const res  = await fetch('../assets/yt-archive.php?action=videos&list=' + id);
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

/* Parent window can ask us to stop YouTube playback when archive window closes */
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'archive-stop') {
        archiveIframe.src = '';
        archivePlayer.style.display = 'none';
        archiveContent.style.display = 'flex';
        if (archiveView === 'video') archiveView = 'videos';
    }
});

loadPlaylists();
</script>

</body>
</html>
