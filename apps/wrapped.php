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

/* No-cache: en desarrollo (y siempre, realmente) queremos asegurar
   que cualquier cambio al UI/JS se vea al instante sin tener que
   limpiar cache del navegador. */
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$isDev = !empty($_GET['dev']);
$year  = (int)($_GET['year'] ?? date('Y'));
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Wrapped <?= $year ?></title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');

    html, body {
        margin: 0; padding: 0;
        width: 100%; height: 100%;
        overflow: hidden;
        font-family: 'Inter', -apple-system, sans-serif;
        color: #fff;
        background: #000;
        user-select: none;
    }

    /* Cada slide es un layer full-screen con su propio fondo y contenido. */
    .slide {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.5s ease;
        padding: 8vh 6vw;
        box-sizing: border-box;
    }
    .slide.active {
        opacity: 1;
        pointer-events: auto;
        animation: slideIn 0.6s ease;
    }
    @keyframes slideIn {
        from { transform: scale(0.95); opacity: 0; }
        to   { transform: scale(1);    opacity: 1; }
    }

    /* Backgrounds vibrantes únicos por slide. */
    #slide-0 { background: linear-gradient(135deg, #1db954 0%, #191414 100%); }
    #slide-1 { background: linear-gradient(135deg, #ff6b6b 0%, #4ecdc4 100%); }
    #slide-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    #slide-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    #slide-4 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    #slide-5 { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    #slide-6 { background: linear-gradient(135deg, #1db954 0%, #191414 100%); }

    .slide-title {
        font-size: clamp(28px, 5vw, 64px);
        font-weight: 900;
        margin: 0 0 24px;
        text-align: center;
        line-height: 1.1;
        text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .slide-subtitle {
        font-size: clamp(16px, 2.5vw, 28px);
        font-weight: 400;
        opacity: 0.9;
        margin: 0 0 20px;
        text-align: center;
    }
    .big-number {
        font-size: clamp(96px, 18vw, 220px);
        font-weight: 900;
        line-height: 1;
        text-shadow: 0 8px 40px rgba(0,0,0,0.4);
    }

    /* Lista de top items. */
    .top-list {
        display: flex; flex-direction: column;
        gap: 14px;
        width: min(580px, 90vw);
        margin: 30px 0 0;
    }
    .top-item {
        display: flex; align-items: center;
        gap: 16px;
        padding: 12px 16px;
        background: rgba(0,0,0,0.25);
        border-radius: 12px;
        backdrop-filter: blur(8px);
    }
    .top-rank {
        font-size: 36px; font-weight: 900;
        width: 50px; text-align: center;
        opacity: 0.95;
    }
    .top-cover {
        width: 56px; height: 56px;
        border-radius: 6px;
        object-fit: cover;
        flex-shrink: 0;
        background: rgba(255,255,255,0.1);
    }
    .top-meta {
        flex: 1; min-width: 0;
    }
    .top-meta-title {
        font-weight: 700;
        font-size: 16px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .top-meta-sub {
        font-size: 13px;
        opacity: 0.75;
        margin-top: 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .top-plays {
        font-size: 13px;
        opacity: 0.9;
        white-space: nowrap;
    }

    /* Hero card para "top 1" — más grande y centrada. */
    .hero {
        display: flex; flex-direction: column;
        align-items: center;
        gap: 20px;
    }
    .hero-cover {
        width: clamp(150px, 30vw, 280px);
        height: clamp(150px, 30vw, 280px);
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .hero-circle {
        width: clamp(150px, 30vw, 280px);
        height: clamp(150px, 30vw, 280px);
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex; align-items: center; justify-content: center;
        font-size: clamp(60px, 12vw, 120px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .hero-title {
        font-size: clamp(24px, 4.5vw, 56px);
        font-weight: 900;
        text-align: center;
        line-height: 1.1;
        max-width: min(700px, 90vw);
    }
    .hero-sub {
        font-size: clamp(14px, 2vw, 20px);
        opacity: 0.8;
    }

    /* Controles */
    #wrapped-controls {
        position: fixed;
        bottom: 24px; right: 24px;
        display: flex; gap: 8px;
        z-index: 100;
    }
    .ctrl-btn {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 30px;
        cursor: pointer;
        font-family: inherit;
    }
    .ctrl-btn:hover { background: rgba(255,255,255,0.25); }

    #wrapped-progress {
        position: fixed;
        top: 24px; left: 24px; right: 24px;
        display: flex; gap: 4px;
        z-index: 100;
    }
    .progress-segment {
        flex: 1;
        height: 3px;
        background: rgba(255,255,255,0.3);
        border-radius: 2px;
        overflow: hidden;
    }
    .progress-segment.done { background: rgba(255,255,255,0.95); }
    .progress-segment.active::after {
        content: ''; display: block;
        height: 100%; width: 0;
        background: #fff;
        animation: fillProgress 6s linear forwards;
    }
    @keyframes fillProgress { to { width: 100%; } }

    #wrapped-close {
        position: fixed;
        top: 50px; right: 24px;
        background: rgba(0,0,0,0.3);
        border: none;
        color: #fff;
        font-size: 20px;
        width: 36px; height: 36px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 101;
    }

    .empty-state {
        font-size: clamp(14px, 2vw, 18px);
        opacity: 0.7;
        font-style: italic;
        margin-top: 14px;
    }
</style>
</head>
<body>

<div id="wrapped-progress"></div>
<button id="wrapped-close" title="Cerrar">✕</button>

<div id="wrapped-root"></div>

<div id="wrapped-controls">
    <button class="ctrl-btn" id="wrapped-prev">‹ Atrás</button>
    <button class="ctrl-btn" id="wrapped-next">Siguiente ›</button>
</div>

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

/* Catálogo de fondos por slide. Cada slide recibe su gradient
   directamente como inline style — así NO depende de tener un ID
   coincidente con CSS (antes había IDs duplicados que causaban
   conflictos). */
const SLIDE_BG = {
    welcome:     'linear-gradient(135deg,#1db954 0%,#191414 100%)',
    minutes:     'linear-gradient(135deg,#ff6b6b 0%,#4ecdc4 100%)',
    topSong:     'linear-gradient(135deg,#f093fb 0%,#f5576c 100%)',
    songsList:   'linear-gradient(135deg,#4facfe 0%,#00f2fe 100%)',
    topArtist:   'linear-gradient(135deg,#fa709a 0%,#fee140 100%)',
    artistsList: 'linear-gradient(135deg,#f78ca0 0%,#f9748f 100%)',
    topAlbum:    'linear-gradient(135deg,#667eea 0%,#764ba2 100%)',
    albumsList:  'linear-gradient(135deg,#0f2027 0%,#203a43 50%,#2c5364 100%)',
    playlists:   'linear-gradient(135deg,#30cfd0 0%,#330867 100%)',
    closure:     'linear-gradient(135deg,#1db954 0%,#191414 100%)',
};

function buildSlides(data) {
    if (!data || !data.ok) {
        document.getElementById('wrapped-root').innerHTML =
            `<div class="slide active" style="background:#222"><h1 class="slide-title">Sin datos</h1><p class="slide-subtitle">${(data && data.error) || 'No hay reproducciones registradas todavía.'}</p></div>`;
        return;
    }

    const root = document.getElementById('wrapped-root');
    const slides = [];
    let i = 0;
    function makeSlide(bgKey, inner) {
        return `<div class="slide" id="slide-${i++}" style="background:${SLIDE_BG[bgKey]};">${inner}</div>`;
    }

    /* Welcome */
    slides.push(makeSlide('welcome', `
        <div class="slide-subtitle" style="opacity:0.85;letter-spacing:4px;">SCRAPBOOK</div>
        <h1 class="slide-title" style="font-size:clamp(48px,10vw,140px);">Wrapped<br><span style="font-weight:400;">${IS_DEV ? '(DEV)' : YEAR}</span></h1>
        <p class="slide-subtitle">Tu año en música</p>
    `));

    /* Total minutos — siempre se muestra, incluso si 0. */
    const totalMin   = Number(data.total_min)   || 0;
    const totalPlays = Number(data.total_plays) || 0;
    slides.push(makeSlide('minutes', `
        <p class="slide-subtitle">Escuchaste</p>
        <div class="big-number">${totalMin.toLocaleString('es-ES')}</div>
        <p class="slide-subtitle">minutos de música<br>(${totalPlays.toLocaleString('es-ES')} reproducciones)</p>
        ${totalPlays === 0 ? '<p class="empty-state" style="margin-top:20px;">Empieza a reproducir música para acumular minutos.</p>' : ''}
    `));

    /* Top song HERO */
    const topSong = (data.songs || [])[0];
    if (topSong) {
        slides.push(makeSlide('topSong', `
            <p class="slide-subtitle">Tu canción del año</p>
            <div class="hero">
                <img class="hero-cover" src="${escapeHtml(topSong.cover_url)}" alt="cover" onerror="this.style.display='none'">
                <div>
                    <div class="hero-title">${escapeHtml(topSong.title)}</div>
                    <div class="hero-sub" style="text-align:center;margin-top:8px;">${escapeHtml(topSong.artist || 'Artista desconocido')} · ${topSong.plays} plays</div>
                </div>
            </div>
        `));
    }

    /* Top 5 canciones */
    if ((data.songs || []).length > 0) {
        slides.push(makeSlide('songsList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Top canciones</h1>
            <div class="top-list">
                ${data.songs.map((s, idx) => `
                    <div class="top-item">
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
        `));
    }

    /* Top artist HERO */
    const topArtist = (data.artists || [])[0];
    if (topArtist) {
        slides.push(makeSlide('topArtist', `
            <p class="slide-subtitle">Tu artista del año</p>
            <div class="hero">
                <div class="hero-circle">🎤</div>
                <div>
                    <div class="hero-title">${escapeHtml(topArtist.artist)}</div>
                    <div class="hero-sub" style="text-align:center;margin-top:8px;">${topArtist.plays} reproducciones</div>
                </div>
            </div>
        `));
    }

    /* Top 5 artistas */
    if ((data.artists || []).length > 0) {
        slides.push(makeSlide('artistsList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus artistas top</h1>
            <div class="top-list">
                ${data.artists.map((a, idx) => `
                    <div class="top-item">
                        <div class="top-rank">${idx+1}</div>
                        <div class="top-cover" style="display:flex;align-items:center;justify-content:center;font-size:28px;background:rgba(255,255,255,0.15);">🎤</div>
                        <div class="top-meta">
                            <div class="top-meta-title">${escapeHtml(a.artist)}</div>
                        </div>
                        <div class="top-plays">${a.plays}×</div>
                    </div>
                `).join('')}
            </div>
        `));
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
                    <div class="hero-title">${escapeHtml(topAlbum.title)}</div>
                    <div class="hero-sub" style="text-align:center;margin-top:8px;">${escapeHtml(topAlbum.artist || 'Artista desconocido')} · ${topAlbum.plays} interacciones</div>
                </div>
            </div>
        `));
    }

    /* Top 5 álbumes */
    if ((data.albums || []).length > 0) {
        slides.push(makeSlide('albumsList', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus álbumes top</h1>
            <div class="top-list">
                ${data.albums.map((al, idx) => `
                    <div class="top-item">
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
        `));
    }

    /* Top playlists */
    if ((data.playlists || []).length > 0) {
        slides.push(makeSlide('playlists', `
            <h1 class="slide-title" style="font-size:clamp(28px,4.5vw,56px);">Tus playlists top</h1>
            <div class="top-list">
                ${data.playlists.map((p, idx) => `
                    <div class="top-item">
                        <div class="top-rank">${idx+1}</div>
                        <div class="top-cover" style="display:flex;align-items:center;justify-content:center;font-size:28px;">📀</div>
                        <div class="top-meta">
                            <div class="top-meta-title">${escapeHtml(p.name)}</div>
                            <div class="top-meta-sub">${p.plays} plays</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `));
    }

    /* Cierre */
    slides.push(makeSlide('closure', `
        <h1 class="slide-title">Gracias por<br>escuchar conmigo 💚</h1>
        <p class="slide-subtitle">— Scrapbook Online</p>
    `));

    root.innerHTML = slides.join('');

    /* Progress bar segmentos. */
    const progress = document.getElementById('wrapped-progress');
    progress.innerHTML = slides.map(_ => '<div class="progress-segment"></div>').join('');

    showSlide(0);
}

let currentSlide = 0;
let autoTimer    = null;

function showSlide(i) {
    const all = document.querySelectorAll('.slide');
    if (i < 0 || i >= all.length) return;
    all.forEach(s => s.classList.remove('active'));
    all[i].classList.add('active');
    currentSlide = i;

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
        autoTimer = setTimeout(() => showSlide(i + 1), 6000);
    }
}

document.getElementById('wrapped-next').addEventListener('click', () => {
    const total = document.querySelectorAll('.slide').length;
    if (currentSlide < total - 1) showSlide(currentSlide + 1);
});
document.getElementById('wrapped-prev').addEventListener('click', () => {
    if (currentSlide > 0) showSlide(currentSlide - 1);
});
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight' || e.key === ' ') document.getElementById('wrapped-next').click();
    if (e.key === 'ArrowLeft') document.getElementById('wrapped-prev').click();
    if (e.key === 'Escape')    document.getElementById('wrapped-close').click();
});
document.getElementById('wrapped-close').addEventListener('click', () => {
    /* Si está dentro de un iframe, cierra cerrando la ventana padre.
       Si no, vuelve al desktop. */
    if (window.parent && window.parent !== window) {
        try { window.parent.postMessage({ type: 'wrapped-close' }, '*'); } catch(_) {}
    } else {
        window.location.href = '../desktop-base.php';
    }
});

/* Click anywhere para avanzar. */
document.addEventListener('click', e => {
    if (e.target.closest('.ctrl-btn') || e.target.closest('#wrapped-close')) return;
    document.getElementById('wrapped-next').click();
});
</script>
</body>
</html>
