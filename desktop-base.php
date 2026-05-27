<?php
/* No imprimir warnings/notices en la salida: en algunos XAMPP (Windows)
   display_errors viene On por defecto y un aviso de PHP se colaría dentro
   del HTML/JS, rompiendo el script (p.ej. "taskbarManager is not defined").
   Los errores se siguen registrando en el log del servidor. */
@ini_set('display_errors', '0');
error_reporting(E_ALL);

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
$startIcon = getUserStartIcon($desktopLabel);

/* URL base del proyecto — necesaria porque las url() dentro de custom
   properties pueden resolverse relativas al CSS que las consume (no al
   documento) en algunos navegadores. */
$projectBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$bodyStyles = [];
if ($wallpaper) $bodyStyles[] = "background-image:url('{$wallpaper}')";
if ($startIcon) $bodyStyles[] = "--start-icon-url:url('{$projectBaseUrl}{$startIcon}')";
$wallpaperStyle = $bodyStyles ? implode(';', $bodyStyles) : '';

$hasPlayer = true; /* Todos los usuarios tienen reproductor */

/* Tema activo del usuario (si lo tiene) */
require_once __DIR__ . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($desktopUserKey, $desktopLabel);
$_userThemes = loadUserThemes($desktopUserKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $desktopLabel);
    $activeThemeCss   = themeCssRelPath($activeTheme, $desktopLabel);
    if (!file_exists(__DIR__ . '/' . $activeThemeCss)) $activeThemeCss = '';
}

/* Iconos del escritorio: set único 'default' para todos los usuarios.
   Antes user1/user2 cargaban iconos custom (capi/, angie/). */
$_iconTheme = 'default';
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
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/reproductor.css">
    <link rel="stylesheet" href="assets/css/perfil.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <script>
    /* Win98 dialogs — inline (idem a la versión en index.php) para
       evitar cualquier 404 sobre un fichero JS recién creado. */
    (function(){
        if (window.__win98DialogsLoaded) return;
        window.__win98DialogsLoaded = true;
        var Z = 999999;
        function build(opts){
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.25);z-index:' + (Z++) + ';display:flex;align-items:center;justify-content:center;';
            var win = document.createElement('div');
            win.className = 'window';
            win.style.cssText = 'min-width:300px;max-width:480px;z-index:' + (Z++) + ';';
            var bar = document.createElement('div'); bar.className = 'title-bar';
            var barText = document.createElement('div'); barText.className = 'title-bar-text';
            barText.textContent = opts.title || (opts.type === 'alert' ? 'Aviso' : opts.type === 'confirm' ? 'Confirmar' : 'Introduce un valor');
            var barCtl = document.createElement('div'); barCtl.className = 'title-bar-controls';
            var closeBtn = document.createElement('button'); closeBtn.setAttribute('aria-label','Close');
            barCtl.appendChild(closeBtn); bar.appendChild(barText); bar.appendChild(barCtl);
            var body = document.createElement('div'); body.className = 'window-body'; body.style.cssText = 'padding:12px 14px;';
            var row = document.createElement('div'); row.style.cssText = 'display:flex;gap:12px;align-items:flex-start;';
            var icon = document.createElement('div');
            icon.textContent = opts.type === 'confirm' ? '❓' : opts.type === 'prompt' ? '✏️' : 'ℹ️';
            icon.style.cssText = 'font-size:24px;line-height:1;flex:0 0 28px;';
            var msg = document.createElement('div');
            msg.style.cssText = 'flex:1;font-size:11px;white-space:pre-wrap;line-height:1.4;';
            msg.textContent = opts.message || '';
            row.appendChild(icon); row.appendChild(msg); body.appendChild(row);
            var input = null;
            if (opts.type === 'prompt') {
                input = document.createElement('input'); input.type = 'text';
                input.value = (opts.defaultVal != null ? String(opts.defaultVal) : '');
                input.style.cssText = 'width:100%;margin-top:10px;box-sizing:border-box;';
                body.appendChild(input);
            }
            var btns = document.createElement('div');
            btns.style.cssText = 'display:flex;justify-content:flex-end;gap:6px;margin-top:14px;';
            var okBtn = document.createElement('button'); okBtn.className = 'default'; okBtn.textContent = opts.okLabel || 'Aceptar';
            var cancelBtn = null;
            if (opts.type !== 'alert') {
                cancelBtn = document.createElement('button'); cancelBtn.textContent = opts.cancelLabel || 'Cancelar';
                btns.appendChild(cancelBtn);
            }
            btns.appendChild(okBtn); body.appendChild(btns);
            win.appendChild(bar); win.appendChild(body);
            overlay.appendChild(win); document.body.appendChild(overlay);
            if (input) setTimeout(function(){ input.focus(); input.select(); }, 30);
            else       setTimeout(function(){ okBtn.focus(); }, 30);
            function close(){ document.removeEventListener('keydown', onKey, true); overlay.remove(); }
            function doOk(){ close(); if (opts.onOk) opts.onOk(input ? input.value : true); }
            function doCancel(){ close(); if (opts.onCancel) opts.onCancel(); }
            function onKey(e){
                if (e.key === 'Enter')      { e.preventDefault(); doOk(); }
                else if (e.key === 'Escape'){ e.preventDefault(); doCancel(); }
            }
            document.addEventListener('keydown', onKey, true);
            okBtn.addEventListener('click', doOk);
            if (cancelBtn) cancelBtn.addEventListener('click', doCancel);
            closeBtn.addEventListener('click', doCancel);
        }
        window.win98Alert = function(message, title, onOk){
            if (typeof title === 'function') { onOk = title; title = undefined; }
            build({ type:'alert', message:String(message), title:title, onOk:onOk });
        };
        window.win98Confirm = function(message, title, onOk, onCancel){
            if (typeof title === 'function') { onCancel = onOk; onOk = title; title = undefined; }
            build({ type:'confirm', message:String(message), title:title, onOk:onOk, onCancel:onCancel });
        };
        window.win98Prompt = function(message, defaultVal, onOk, onCancel, title){
            build({ type:'prompt', message:String(message), defaultVal:defaultVal, onOk:onOk, onCancel:onCancel, title:title });
        };
        window.alert = function(message){ window.win98Alert(message); };
    })();
    </script>
</head>

<body class="<?php
    /* Tema por defecto = Win98 (tokens.css) para TODOS los usuarios.
       Sin clases 'capi'/'angie' ni 'userN' que disparen paletas especiales
       en themes.css. Si el usuario tiene un tema custom activo, $activeThemeClass
       lo aplica como antes. */
    $bodyClasses = [];
    if ($activeThemeClass) $bodyClasses[] = $activeThemeClass;
    if ($startIcon) $bodyClasses[] = 'has-start-icon';
    echo htmlspecialchars(implode(' ', $bodyClasses));
?>"<?php echo $wallpaperStyle ? " style=\"{$wallpaperStyle}\"" : ''; ?>>

<div id="page-enter"></div>

<script>
/* Hoist mínimo de DesktopState: las apps (reproductor.php se incluye más
   abajo) lo usan inline antes de que se ejecute el módulo grande de
   desktop-base.php. Aquí solo creamos el stub con whenReady; fetchState
   lo rellena después y dispara la cola. */
window.DesktopState = { icons: {}, folders: [], player: null, loaded: false, _readyCbs: [] };
window.DesktopState.whenReady = function(cb){
    if (this.loaded) cb();
    else this._readyCbs.push(cb);
};
</script>

<!-- DESKTOP CONTEXT MENU (right-click) -->
<ul id="desktop-ctx-menu" class="desk-ctx">
    <li data-action="new-folder">📁 Nueva carpeta</li>
</ul>

<!-- FOLDER WINDOW TEMPLATE — clonado para cada carpeta abierta (varias en paralelo) -->
<div class="window" id="folder-window-template" style="display:none; position:fixed; width:460px; height:340px; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text">📁 Carpeta</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close"></button>
        </div>
    </div>
    <div class="window-body folder-content"></div>
</div>

<!-- MODAL "Nueva carpeta" (estilo Win98/XP) -->
<div class="window" id="folder-create-modal" style="display:none; position:fixed; left:50%; top:35%; transform:translate(-50%,-50%); width:340px; z-index:8500; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text">📁 Nueva carpeta</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="folder-create-x"></button>
        </div>
    </div>
    <div class="window-body" style="padding:14px;">
        <p style="margin-bottom:10px;font-size:11px;">Nombre de la carpeta:</p>
        <input id="folder-create-input" type="text" maxlength="40" style="width:100%;" value="Nueva carpeta">
        <div style="display:flex;justify-content:flex-end;gap:6px;margin-top:14px;">
            <button id="folder-create-ok" class="default">Aceptar</button>
            <button id="folder-create-cancel">Cancelar</button>
        </div>
    </div>
</div>

<!-- MODAL confirmar borrar carpeta (Windows 98 clásico) -->
<div class="window" id="folder-delete-modal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); min-width:340px; max-width:460px; z-index:8500; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text">Confirmar eliminación de carpeta</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="folder-delete-x"></button>
        </div>
    </div>
    <div class="window-body w98-confirm-body">
        <div class="w98-confirm-row">
            <div class="w98-icon-question"></div>
            <div class="w98-confirm-text" id="folder-delete-text">¿Borrar la carpeta?</div>
        </div>
        <div class="w98-confirm-btns">
            <button id="folder-delete-ok" class="default">Sí</button>
            <button id="folder-delete-cancel">No</button>
        </div>
    </div>
</div>

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
    <div class="desktop-icon" id="temas-icon">
        <div class="desktop-icon-img"><?php echo desktopIcon('temas', '🎨'); ?></div>
        <span>Temas</span>
    </div>
    <div class="desktop-icon" id="companion-icon">
        <div class="desktop-icon-img"><?php echo desktopIcon('companion', '💀'); ?></div>
        <span>Companion</span>
    </div>
    <div class="desktop-icon" id="dnd-icon">
        <div class="desktop-icon-img"><?php echo desktopIcon('dnd', '⚔'); ?></div>
        <span>Fichas D&amp;D</span>
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

<!-- TEMAS WINDOW -->
<div class="window" id="temas-window" style="display:none; position:fixed; left:10vw; top:6vh; width:80vw; height:80vh; z-index:550; flex-direction:column;">
    <div class="title-bar" id="temas-titlebar">
        <div class="title-bar-text">🎨 Temas</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="temas-close"></button>
        </div>
    </div>
    <iframe id="temas-frame" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- COMPANION WINDOW -->
<div class="window" id="companion-window" style="display:none; position:fixed; left:10vw; top:6vh; width:80vw; height:80vh; z-index:550; flex-direction:column;">
    <div class="title-bar" id="companion-titlebar">
        <div class="title-bar-text">💀 Helldivers Companion</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="companion-close"></button>
        </div>
    </div>
    <iframe id="companion-frame" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- DND WINDOW -->
<div class="window" id="dnd-window" style="display:none; position:fixed; left:5vw; top:4vh; width:90vw; height:88vh; z-index:500; flex-direction:column;">
    <div class="title-bar" id="dnd-titlebar">
        <div class="title-bar-text">⚔ Fichas D&amp;D</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="dnd-close"></button>
        </div>
    </div>
    <iframe id="dnd-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
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
   TEMA EN VIVO (hot-swap)
========================= */
window.applyThemeToDocument = function(doc, className, cssHref) {
    if (!doc || !doc.body) return;
    var body = doc.body;
<<<<<<< HEAD
=======
    /* Conserva solo clases estructurales; capi/angie ya no se usan. */
>>>>>>> f90d0df325639ab0f1fd958b8cb649527d24e11e
    var keep = (body.className || '').split(/\s+/).filter(function(c) {
        return c === 'has-start-icon';
    });
    if (className) keep.push(className);
    body.className = keep.join(' ');

    var existing = doc.getElementById('active-theme-link');
    if (cssHref) {
        var bust = (cssHref.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
        if (existing) {
            existing.href = cssHref + bust;
        } else {
            var link = doc.createElement('link');
            link.rel  = 'stylesheet';
            link.id   = 'active-theme-link';
            link.href = cssHref + bust;
            doc.head.appendChild(link);
        }
    } else if (existing) {
        existing.remove();
    }
};

window.addEventListener('message', function(e) {
    if (!e.data) return;
    if (e.data.type === 'theme-activated') {
        var className = e.data.className || '';
        var basePath  = e.data.cssBasePath || '';
        applyThemeToDocument(document, className, basePath);
        ['calendar-iframe', 'archive-frame', 'temas-frame', 'companion-frame', 'dnd-iframe'].forEach(function(id) {
            var fr = document.getElementById(id);
            if (!fr || !fr.contentDocument || !fr.contentDocument.body) return;
            var childHref = basePath ? '../' + basePath : '';
            applyThemeToDocument(fr.contentDocument, className, childHref);
        });
    } else if (e.data.type === 'wallpaper-changed') {
        var wp = e.data.wallpaper || '';
        if (wp) {
            document.body.style.backgroundImage = "url('" + wp + "?t=" + Date.now() + "')";
        } else {
            document.body.style.backgroundImage = '';
        }
    } else if (e.data.type === 'start-icon-changed') {
        var si = e.data.icon || '';
        if (si) {
            var abs = new URL(si, location.href).href;
            document.body.style.setProperty('--start-icon-url', "url('" + abs + "?t=" + Date.now() + "')");
            document.body.classList.add('has-start-icon');
        } else {
            document.body.style.removeProperty('--start-icon-url');
            document.body.classList.remove('has-start-icon');
        }
    } else if (e.data.type === 'profile-photo-changed') {
        var newPath = e.data.photo || '';
        if (!newPath) return;
        var m = newPath.match(/\/([^/]+)\.[^/]+$/);
        if (!m) return;
        var basename = m[1].toLowerCase();
        function refreshImgs(doc) {
            if (!doc) return;
            var imgs = doc.querySelectorAll('img');
            for (var i = 0; i < imgs.length; i++) {
                var src = imgs[i].getAttribute('src') || '';
                var sm = src.match(/\/([^/?]+)\.[a-zA-Z0-9]+(?:\?|$)/);
                if (sm && sm[1].toLowerCase() === basename) {
                    imgs[i].src = newPath + '?t=' + Date.now();
                }
            }
        }
        refreshImgs(document);
        ['calendar-iframe', 'archive-frame', 'temas-frame', 'companion-frame', 'dnd-iframe'].forEach(function(id) {
            var fr = document.getElementById(id);
            if (fr && fr.contentDocument) refreshImgs(fr.contentDocument);
        });
    }
});

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
   TASKBAR TASK MANAGER
========================= */
/* ──── Z-ORDER manager: ventana abierta siempre encima ────
   Cualquier .window cuyo display pase de "none" a visible se eleva
   automáticamente con bringToFront vía MutationObserver. Así las review
   modales / dialogs nunca quedan tapadas, sin tener que parchear cada
   sitio que abre una ventana. */
window.windowZ = (function() {
    var topZ = 600;  // por encima de los z-index iniciales (500-560)
    function bringToFront(id) {
        var win = (typeof id === 'string') ? document.getElementById(id) : id;
        if (!win) return;
        topZ++;
        win.style.zIndex = topZ;
    }
    function isFrontmost(id) {
        var win = document.getElementById(id);
        if (!win) return false;
        var myZ = parseInt(win.style.zIndex || getComputedStyle(win).zIndex || '0', 10) || 0;
        var maxZ = 0;
        document.querySelectorAll('.window').forEach(function(w){
            if (w === win) return;
            if (w.style.display === 'none' || getComputedStyle(w).display === 'none') return;
            var z = parseInt(w.style.zIndex || getComputedStyle(w).zIndex || '0', 10) || 0;
            if (z > maxZ) maxZ = z;
        });
        return myZ >= maxZ;
    }

    /* Auto-elevar cuando una .window pasa de oculta a visible. Sirve para
       cualquier dialog/modal/popup que cambie display o clase "hidden",
       no solo para los que pasan por taskbarManager. */
    function isVisible(el) {
        if (!el || !el.classList || !el.classList.contains('window')) return false;
        if (el.classList.contains('hidden')) return false;
        var s = el.style.display;
        if (s === 'none') return false;
        if (!s && getComputedStyle(el).display === 'none') return false;
        return true;
    }
    var lastSeen = new WeakMap();
    function check(el) {
        if (!el || !el.classList || !el.classList.contains('window')) return;
        var nowVis = isVisible(el);
        var prev   = lastSeen.get(el) === true;
        if (nowVis && !prev) bringToFront(el);
        lastSeen.set(el, nowVis);
    }
    new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.type === 'attributes') {
                check(m.target);
            } else if (m.type === 'childList') {
                m.addedNodes.forEach(function(n) {
                    if (n.nodeType !== 1) return;
                    /* el propio nodo o cualquier .window dentro de él */
                    check(n);
                    if (n.querySelectorAll) n.querySelectorAll('.window').forEach(check);
                });
            }
        });
    }).observe(document.body, {
        attributes: true,
        attributeFilter: ['style', 'class'],
        childList: true,
        subtree: true,
    });

    return { bringToFront: bringToFront, isFrontmost: isFrontmost };
})();

window.taskbarManager = (function() {
    var tasksEl  = document.getElementById('taskbar-tasks');
    var registry = {};

    function register(id, label, icon, displayMode) {
        if (registry[id]) { restore(id); return; }
        var win = document.getElementById(id);
        if (!win) return;
        var btn = document.createElement('button');
        btn.className = 'button taskbar-task-btn';
        btn.title = label;
        btn.textContent = (icon ? icon + ' ' : '') + label;
        btn.addEventListener('click', function() { toggle(id); });
        tasksEl.appendChild(btn);
        registry[id] = { btn: btn, displayMode: displayMode || 'block' };
        win.style.display = displayMode || 'block';
        windowZ.bringToFront(id);   // ventana recién abierta → al frente
    }

    function unregister(id) {
        if (!registry[id]) return;
        var win = document.getElementById(id);
        if (win) win.style.display = 'none';
        registry[id].btn.remove();
        delete registry[id];
    }

    function minimize(id) {
        var entry = registry[id];
        if (!entry) return;
        var win = document.getElementById(id);
        if (!win || win.style.display === 'none') return;
        entry.displayMode = win.style.display;
        win.style.display = 'none';
        win.classList.remove('win-maximized');
        entry.btn.classList.add('taskbar-task-minimized');
    }

    function restore(id) {
        var entry = registry[id];
        if (!entry) return;
        var win = document.getElementById(id);
        if (!win) return;
        win.style.display = entry.displayMode || 'block';
        entry.btn.classList.remove('taskbar-task-minimized');
        windowZ.bringToFront(id);   // al restaurar/abrir, al frente
    }

    /* Click en barra de tareas:
       - Minimizada  → restaurar + al frente
       - Visible y NO frontmost → traer al frente (sin minimizar)
       - Visible y frontmost → minimizar */
    function toggle(id) {
        var entry = registry[id];
        if (!entry) return;
        var win = document.getElementById(id);
        if (!win) return;
        if (win.style.display === 'none') {
            restore(id);
        } else if (!windowZ.isFrontmost(id)) {
            windowZ.bringToFront(id);
        } else {
            minimize(id);
        }
    }

    function isRegistered(id) { return !!registry[id]; }
    function getButton(id)    { return registry[id] ? registry[id].btn : null; }

    return { register: register, unregister: unregister, minimize: minimize, restore: restore, toggle: toggle, isRegistered: isRegistered, getButton: getButton };
})();

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

/* =========================
   CALENDARIO
========================= */
(function() {
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

/* =========================
   TEMAS
========================= */
(function() {
    var temasFrame  = document.getElementById('temas-frame');
    var temasLoaded = false;
    document.getElementById('temas-icon').addEventListener('dblclick', function() {
        if (!temasLoaded) { temasFrame.src = 'apps/temas.php'; temasLoaded = true; }
        if (taskbarManager.isRegistered('temas-window')) {
            taskbarManager.restore('temas-window');
        } else {
            taskbarManager.register('temas-window', 'Temas', '🎨', 'flex');
        }
    });
    document.getElementById('temas-close').addEventListener('click', function() {
        taskbarManager.unregister('temas-window');
    });
})();

/* =========================
   COMPANION
========================= */
(function() {
    var companionFrame  = document.getElementById('companion-frame');
    var companionLoaded = false;
    document.getElementById('companion-icon').addEventListener('dblclick', function() {
        if (!companionLoaded) { companionFrame.src = 'https://helldiverscompanion.com'; companionLoaded = true; }
        if (taskbarManager.isRegistered('companion-window')) {
            taskbarManager.restore('companion-window');
        } else {
            taskbarManager.register('companion-window', 'Companion', '💀', 'flex');
        }
    });
    document.getElementById('companion-close').addEventListener('click', function() {
        taskbarManager.unregister('companion-window');
    });
})();

/* =========================
   D&D FICHAS
========================= */
(function() {
    var dndIframe = document.getElementById('dnd-iframe');
    var dndLoaded = false;

    document.getElementById('dnd-icon').addEventListener('dblclick', function() {
        if (!dndLoaded) { dndIframe.src = 'apps/dnd.php'; dndLoaded = true; }
        if (taskbarManager.isRegistered('dnd-window')) {
            taskbarManager.restore('dnd-window');
        } else {
            taskbarManager.register('dnd-window', 'Fichas D&D', '⚔', 'flex');
        }
    });

    document.getElementById('dnd-close').addEventListener('click', function() {
        taskbarManager.unregister('dnd-window');
    });
})();

/* ──── Player right-click context menu ──── */
(function() {
    var playerMain = document.getElementById('player-main');
    if (!playerMain) return;

    var ctxMenu = document.createElement('div');
    ctxMenu.className = 'window';
    ctxMenu.style.cssText = 'display:none;position:fixed;z-index:9999;padding:2px 0;min-width:160px;';
    document.body.appendChild(ctxMenu);

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
        fetch('assets/music/api.php?action=get-playlists')
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
                            fetch('assets/music/api.php?action=save-playlist-item', {
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
        var PAD = 40;
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


    /* Expone setup() para que módulos externos (ej. carpetas dinámicas) puedan
       enrolar nuevas ventanas en el sistema de drag+resize. */
    window.WindowManager = { setup: setup };

    /* Todas las ventanas: drageables Y resizables (handles en bordes y esquinas) */
    [
        'calendar-window','archive-window','temas-window','dnd-window',
        'companion-window',
        'playlist-editor','create-playlist-dialog','profile-window',
        'add-track-dialog','import-playlist-dialog',
        'collab-dialog','spotify-import-dialog','confirm-dialog',
        'profile-add-dialog','profile-review-prompt','profile-review-window',
        'profile-review-view','profile-invite-dialog','profile-info-edit-dialog',
        'music-add-dialog','profile-notifs-window','profile-melon-details-window',
        'profile-chat-window'
    ].forEach(function(id) { setup(id, false); });
    /* music-player: solo drag, NUNCA resize (tamaño fijo) */
    setup('music-player', true);
})();

/* ──── Window minimize / maximize ──── */
(function() {
    var ids = [
        'calendar-window', 'archive-window', 'temas-window', 'dnd-window',
        'create-playlist-dialog', 'profile-window', 'companion-window'
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

/* =========================================================
   ICONOS DEL ESCRITORIO: long-press para mover
   - Mantener pulsado ~400 ms inicia el modo arrastrar
   - Estado persistido en SQL vía assets/desktop/api.php
   - El doble click sigue abriendo la app (no entra en conflicto)
========================================================= */
(function(){
    var HOLD_MS = 350;
    var MOVE_TOLERANCE = 6;        // px de margen antes de cancelar un click normal
    var GRID_W = 96;               // cuadrícula horizontal (px)
    var GRID_H = 96;               // cuadrícula vertical (px)

    /* Estado del escritorio cacheado: lo rellena la primera carga.
       window.DesktopState también lo expone para que DesktopFolders lo lea.
       `whenReady(cb)` resuelve en cuanto fetchState termina (o inmediatamente
       si ya estaba cargado). Útil para código que evalúa antes del fetch
       (reproductor.php carga volumen + playlist saved → necesita esperar). */
    window.DesktopState = window.DesktopState || { icons: {}, folders: [], player: null, loaded: false, _readyCbs: [] };
    if (!window.DesktopState.whenReady) {
        window.DesktopState.whenReady = function(cb){
            if (this.loaded) cb();
            else this._readyCbs.push(cb);
        };
    }

    function snap(v, step){ return Math.round(v / step) * step; }
    function snapPos(x, y){ return { left: snap(x, GRID_W), top: snap(y, GRID_H) }; }

    function loadPositions(){ return window.DesktopState.icons || {}; }
    /* Debounce por-icono para no martillear al servidor mientras se arrastra */
    var _saveTimers = {};
    function savePosition(id, pos){
        window.DesktopState.icons[id] = pos;
        clearTimeout(_saveTimers[id]);
        _saveTimers[id] = setTimeout(function(){
            fetch('assets/desktop/api.php?action=save-icon', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, left: pos.left, top: pos.top })
            }).catch(function(){});
        }, 120);
    }

    function fetchState(cb){
        function done(){
            window.DesktopState.loaded = true;
            /* Vaciar la cola de whenReady() (reproductor, etc.) */
            var queue = window.DesktopState._readyCbs || [];
            window.DesktopState._readyCbs = [];
            queue.forEach(function(fn){ try { fn(); } catch(e){} });
            cb();
        }
        fetch('assets/desktop/api.php?action=get-all')
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d && d.ok){
                    window.DesktopState.icons   = d.icons   || {};
                    window.DesktopState.folders = d.folders || [];
                    window.DesktopState.player  = d.player  || null;
                }
                done();
            })
            .catch(done);
    }

    function init(){
        var icons = document.querySelectorAll('.desktop-icon');
        if(!icons.length) return;
        var desk  = document.getElementById('desktop');
        var saved = loadPositions();

        // Capturar la posición natural (flow) antes de pasarlos a absolute
        var natural = {};
        icons.forEach(function(icon){
            var r = icon.getBoundingClientRect();
            var dr = desk.getBoundingClientRect();
            natural[icon.id] = { left: r.left - dr.left, top: r.top - dr.top };
        });

        icons.forEach(function(icon){
            var raw = saved[icon.id] || natural[icon.id];
            var pos = snapPos(raw.left, raw.top);   // siempre alineado a cuadrícula
            icon.style.position = 'absolute';
            icon.style.margin   = '0';
            icon.style.left = pos.left + 'px';
            icon.style.top  = pos.top  + 'px';
            attachDrag(icon, desk);
        });
        if(window.DesktopFolders) DesktopFolders.init(desk);
    }

    // Exponer helpers para que DesktopFolders pueda usarlos en iconos creados al vuelo
    window.DesktopIcons = {
        attachDrag: function(icon){
            var desk = document.getElementById('desktop');
            attachDrag(icon, desk);
        },
        snapPos: snapPos,
        savePosition: savePosition,
        loadPositions: loadPositions,
        GRID_W: GRID_W,
        GRID_H: GRID_H
    };

    function attachDrag(icon, desk){
        var holdTimer = null;
        var dragging  = false;
        var armed     = false;        // pasó el long-press, listo para arrastrar
        var startX = 0, startY = 0;
        var originX = 0, originY = 0;
        var moved   = false;
        var wasInBody = false;        // si se reubicó al body para escapar stacking context

        function pointer(e){
            if(e.touches && e.touches[0]) return e.touches[0];
            if(e.changedTouches && e.changedTouches[0]) return e.changedTouches[0];
            return e;
        }

        function cleanupListeners(){
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onUp);
            document.removeEventListener('touchcancel', onUp);
        }

        function onDown(e){
            if(e.type === 'mousedown' && e.button !== 0) return;
            var p = pointer(e);
            startX = p.clientX; startY = p.clientY;
            originX = parseFloat(icon.style.left) || 0;
            originY = parseFloat(icon.style.top)  || 0;
            moved = false;

            icon.classList.add('long-pressing');
            holdTimer = setTimeout(function(){
                armed = true;
                icon.classList.remove('long-pressing');
                icon.classList.add('dragging');
            }, HOLD_MS);

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend',  onUp);
            document.addEventListener('touchcancel', onUp);
        }

        function onMove(e){
            var p = pointer(e);
            var dx = p.clientX - startX;
            var dy = p.clientY - startY;
            if(!armed){
                // Cancelar el hold si el usuario se mueve antes del umbral
                if(Math.abs(dx) > MOVE_TOLERANCE || Math.abs(dy) > MOVE_TOLERANCE){
                    clearTimeout(holdTimer);
                    icon.classList.remove('long-pressing');
                    cleanupListeners();
                }
                return;
            }
            // En drag: bloquear scroll en touch
            if(e.cancelable) e.preventDefault();
            // En la primera frame de movimiento: reubicar al body para escapar
            // del stacking context de #desktop (si no, el icono queda por debajo
            // de las ventanas/carpetas abiertas que son hermanas de #desktop).
            if(!dragging && !wasInBody){
                var br = icon.getBoundingClientRect();
                document.body.appendChild(icon);
                icon.style.position = 'fixed';
                icon.style.left = br.left + 'px';
                icon.style.top  = br.top  + 'px';
                wasInBody = true;
            }
            dragging = true; moved = true;
            // Movimiento libre 1:1 con el cursor, clamp a los bordes del escritorio.
            // Coords ya son viewport (position:fixed). El snap a cuadrícula ocurre al soltar.
            var w = icon.offsetWidth, h = icon.offsetHeight;
            var dw = desk.clientWidth, dh = desk.clientHeight;
            var nx = Math.max(0, Math.min(dw - w, originX + dx));
            var ny = Math.max(0, Math.min(dh - h, originY + dy));
            icon.style.left = nx + 'px';
            icon.style.top  = ny + 'px';
            // Resaltar la carpeta sobre la que el icono se encuentra
            if(window.DesktopFolders){
                var rect = icon.getBoundingClientRect();
                DesktopFolders.updateHover(icon.id, rect.left + rect.width/2, rect.top + rect.height/2);
            }
        }

        function onUp(){
            clearTimeout(holdTimer);
            icon.classList.remove('long-pressing');
            // SIEMPRE limpiar la clase 'dragging': el long-press la añade aunque
            // el usuario no llegue a mover, y si no se quita el CSS pointer-events:none
            // deja el icono bloqueado para futuros clicks/dblclicks.
            icon.classList.remove('dragging');
            if(dragging){
                // ¿Se soltó sobre una carpeta?
                var droppedInFolder = false;
                if(window.DesktopFolders){
                    var rect = icon.getBoundingClientRect();
                    droppedInFolder = DesktopFolders.tryDrop(icon.id, rect.left + rect.width/2, rect.top + rect.height/2);
                    DesktopFolders.clearHover();
                }
                // Devolver el icono a #desktop (las coords viewport siguen siendo válidas
                // porque #desktop está en 0,0 del viewport)
                if(wasInBody){
                    desk.appendChild(icon);
                    icon.style.position = 'absolute';
                    wasInBody = false;
                }
                if(!droppedInFolder){
                    var finalPos = snapPos(parseFloat(icon.style.left)||0, parseFloat(icon.style.top)||0);
                    icon.classList.add('snapping');
                    icon.style.left = finalPos.left + 'px';
                    icon.style.top  = finalPos.top  + 'px';
                    setTimeout(function(){ icon.classList.remove('snapping'); }, 200);
                    savePosition(icon.id, finalPos);
                }
                // Bloquear el siguiente click para que no abra la app sin querer
                if(moved){
                    var swallow = function(ev){ ev.stopPropagation(); ev.preventDefault();
                        document.removeEventListener('click', swallow, true); };
                    document.addEventListener('click', swallow, true);
                }
            }
            dragging = false; armed = false;
            cleanupListeners();
        }

        icon.addEventListener('mousedown',  onDown);
        icon.addEventListener('touchstart', onDown, { passive: true });
    }

    function bootstrap(){
        /* Cargar estado desde SQL antes de pintar nada. Si falla, init usará
           posiciones por defecto. */
        fetchState(function(){ requestAnimationFrame(init); });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();

/* =========================================================
   CARPETAS DEL ESCRITORIO
   - Right-click sobre el fondo: "📁 Nueva carpeta"
   - Arrastra un icono sobre una carpeta → se añade dentro
   - Doble-click sobre la carpeta → ventana con los iconos hijos
   - Click en un icono hijo → ejecuta dblclick sobre el original (abre la app)
   - Botón ✕ junto al icono hijo → lo saca de la carpeta
========================================================= */
(function(){
    var folders = {};            // {folderId: {id,name,pos:{l,t},children:[...]}}
    var openFolderWindows = {};  // {folderId: { el: HTMLElement, wid: 'folder-window-...' }}
    var hoverFolderEl = null;

    /* Lee del estado precargado por el módulo de iconos. Espera al evento
       readystate completo: cuando se llama a init, DesktopState ya está
       resuelto (bootstrap del módulo anterior). */
    function load(){
        folders = {};
        var arr = (window.DesktopState && window.DesktopState.folders) || [];
        arr.forEach(function(f){
            folders[f.id] = {
                id: f.id,
                name: f.name,
                pos: { left: (f.pos && f.pos.left)|0, top: (f.pos && f.pos.top)|0 },
                children: Array.isArray(f.children) ? f.children.slice() : [],
            };
        });
    }
    /* Debounce por-carpeta: durante un drag rápido evitamos hacer N POSTs */
    var _saveTimers = {};
    function saveFolder(id){
        var f = folders[id];
        if(!f){
            /* Si el folder ya no existe en memoria, dispara delete */
            clearTimeout(_saveTimers[id]);
            fetch('assets/desktop/api.php?action=delete-folder', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            }).catch(function(){});
            return;
        }
        clearTimeout(_saveTimers[id]);
        _saveTimers[id] = setTimeout(function(){
            fetch('assets/desktop/api.php?action=save-folder', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: f.id, name: f.name, pos: f.pos, children: f.children || [],
                })
            }).catch(function(){});
        }, 150);
    }
    /* save() global: persiste todas las carpetas presentes (útil tras un
       cambio que afecta varias en cascada, ej. mover icono entre carpetas). */
    function save(){
        Object.keys(folders).forEach(saveFolder);
    }
    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function init(desk){
        load();
        // Renderizar cada carpeta como icono
        Object.keys(folders).forEach(function(id){ renderFolderIcon(folders[id], desk); });
        // Ocultar iconos que ya están dentro de alguna carpeta
        var hiddenChildren = new Set();
        Object.values(folders).forEach(function(f){
            (f.children||[]).forEach(function(cid){ hiddenChildren.add(cid); });
        });
        hiddenChildren.forEach(function(cid){
            var el = document.getElementById(cid); if(el) el.style.display = 'none';
        });
    }

    function renderFolderIcon(folder, desk){
        if(!desk) desk = document.getElementById('desktop');
        var div = document.createElement('div');
        div.className = 'desktop-icon';
        div.id = folder.id;
        div.dataset.folder = '1';
        div.innerHTML =
            '<div class="desktop-icon-img">📁</div>' +
            '<span>' + esc(folder.name) + '</span>';
        div.style.position = 'absolute';
        div.style.margin = '0';
        /* La posición autoritativa para todo lo que pinta en el escritorio
           es desktop_icons (savePosition). desktop_folders.pos es solo la
           posición inicial; cuando el usuario arrastra el icono de carpeta
           solo se actualiza desktop_icons. */
        var savedIconPos = window.DesktopIcons ? window.DesktopIcons.loadPositions()[folder.id] : null;
        var basePos = savedIconPos || folder.pos;
        var pos = window.DesktopIcons ? window.DesktopIcons.snapPos(basePos.left, basePos.top) : basePos;
        div.style.left = pos.left + 'px';
        div.style.top  = pos.top  + 'px';
        div.addEventListener('dblclick', function(){ openFolderWindow(folder.id); });
        // contextmenu sobre el icono de la carpeta → dispara directamente el
        // modal Win98 de confirmar borrado (sin diálogo nativo del navegador)
        div.addEventListener('contextmenu', function(e){
            e.preventDefault(); e.stopPropagation();
            deleteFolder(folder.id);
        });
        desk.appendChild(div);
        if(window.DesktopIcons) window.DesktopIcons.attachDrag(div);
    }

    function createFolder(x, y){
        showCreateModal(function(name){
            if(!name) return;
            name = name.trim().slice(0, 40);
            if(!name) return;
            var id = 'fld-' + Date.now().toString(36) + Math.random().toString(36).slice(2,5);
            var pos = window.DesktopIcons ? window.DesktopIcons.snapPos(x, y) : { left: x, top: y };
            folders[id] = { id: id, name: name, pos: pos, children: [] };
            saveFolder(id);
            renderFolderIcon(folders[id]);
            if(window.DesktopIcons) window.DesktopIcons.savePosition(id, pos);
        });
    }

    /* Crear una nueva carpeta como hija de otra carpeta (vía right-click dentro) */
    function createFolderInside(parentId){
        if(!folders[parentId]) return;
        showCreateModal(function(name){
            if(!name) return;
            name = name.trim().slice(0, 40);
            if(!name) return;
            var id = 'fld-' + Date.now().toString(36) + Math.random().toString(36).slice(2,5);
            var pos = window.DesktopIcons ? window.DesktopIcons.snapPos(0, 0) : { left: 0, top: 0 };
            folders[id] = { id: id, name: name, pos: pos, children: [] };
            folders[parentId].children.push(id);
            saveFolder(id);
            saveFolder(parentId);
            // Renderiza el icono en el escritorio pero queda oculto (vive dentro del padre)
            renderFolderIcon(folders[id]);
            var iconEl = document.getElementById(id);
            if(iconEl) iconEl.style.display = 'none';
            // Refresca la ventana padre si está abierta
            if(openFolderWindows[parentId]) renderFolderContent(parentId);
        });
    }

    function deleteFolder(id){
        var f = folders[id]; if(!f) return;
        showDeleteModal(f.name, function(){
            // Restaurar iconos hijos al escritorio (mostrarlos de nuevo)
            (f.children||[]).forEach(function(cid){
                var el = document.getElementById(cid); if(el) el.style.display = '';
            });
            // Si esta carpeta vive dentro de otras, sacarla de sus padres
            var parentsToRefresh = [];
            Object.keys(folders).forEach(function(fid){
                if(fid === id) return;
                var pf = folders[fid];
                var idx = (pf.children||[]).indexOf(id);
                if(idx !== -1){
                    pf.children.splice(idx, 1);
                    parentsToRefresh.push(fid);
                }
            });
            delete folders[id];
            /* Borrado real en SQL (CASCADE limpia desktop_folder_items) */
            saveFolder(id);
            /* Persistir los padres que la tenían dentro */
            parentsToRefresh.forEach(saveFolder);
            var el = document.getElementById(id); if(el) el.remove();
            if(openFolderWindows[id]) closeFolderWindowById(id);
            // Refrescar ventanas padre abiertas para que ya no muestren la carpeta
            parentsToRefresh.forEach(function(pid){
                if(openFolderWindows[pid]) renderFolderContent(pid);
            });
        });
    }

    /* Modales en estilo Win98/XP ─ reemplazan a prompt() y confirm() */
    function showCreateModal(onAccept){
        var modal = document.getElementById('folder-create-modal');
        var input = document.getElementById('folder-create-input');
        input.value = 'Nueva carpeta';
        modal.style.display = 'flex';
        if(window.windowZ) windowZ.bringToFront('folder-create-modal');
        setTimeout(function(){ input.focus(); input.select(); }, 30);
        function ok(){ cleanup(); onAccept(input.value); }
        function cancel(){ cleanup(); }
        function keyHandler(ev){
            if(ev.key === 'Enter'){ ev.preventDefault(); ok(); }
            else if(ev.key === 'Escape'){ ev.preventDefault(); cancel(); }
        }
        function cleanup(){
            modal.style.display = 'none';
            document.getElementById('folder-create-ok').onclick = null;
            document.getElementById('folder-create-cancel').onclick = null;
            document.getElementById('folder-create-x').onclick = null;
            input.removeEventListener('keydown', keyHandler);
        }
        document.getElementById('folder-create-ok').onclick = ok;
        document.getElementById('folder-create-cancel').onclick = cancel;
        document.getElementById('folder-create-x').onclick = cancel;
        input.addEventListener('keydown', keyHandler);
    }
    function showDeleteModal(folderName, onAccept){
        var modal = document.getElementById('folder-delete-modal');
        document.getElementById('folder-delete-text').innerHTML =
            '¿Borrar la carpeta <strong>' + esc(folderName) + '</strong>?<br>' +
            '<small>Los iconos volverán al escritorio.</small>';
        modal.style.display = 'flex';
        if(window.windowZ) windowZ.bringToFront('folder-delete-modal');
        function ok(){ cleanup(); onAccept(); }
        function cancel(){ cleanup(); }
        function cleanup(){
            modal.style.display = 'none';
            document.getElementById('folder-delete-ok').onclick = null;
            document.getElementById('folder-delete-cancel').onclick = null;
            document.getElementById('folder-delete-x').onclick = null;
        }
        document.getElementById('folder-delete-ok').onclick = ok;
        document.getElementById('folder-delete-cancel').onclick = cancel;
        document.getElementById('folder-delete-x').onclick = cancel;
    }

    function findFolderAt(iconId, x, y){
        var els = document.querySelectorAll('.desktop-icon[data-folder]');
        for(var i=0; i<els.length; i++){
            if(els[i].id === iconId) continue;
            var r = els[i].getBoundingClientRect();
            if(x >= r.left && x <= r.right && y >= r.top && y <= r.bottom) return els[i];
        }
        return null;
    }

    /* Devuelve folderId si (x,y) cae dentro de alguna ventana de carpeta abierta, sino null */
    function getOpenFolderWindowAt(x, y){
        for(var fid in openFolderWindows){
            var fw = openFolderWindows[fid].el;
            if(!fw || fw.style.display === 'none') continue;
            var r = fw.getBoundingClientRect();
            if(x >= r.left && x <= r.right && y >= r.top && y <= r.bottom) return fid;
        }
        return null;
    }

    function updateHover(iconId, x, y){
        var fEl = findFolderAt(iconId, x, y);
        // Drop inválido (ciclo) → no iluminar
        if(fEl && folders[iconId] && isDescendant(fEl.id, iconId)) fEl = null;
        if(hoverFolderEl && hoverFolderEl !== fEl) hoverFolderEl.classList.remove('folder-receive');
        if(fEl) fEl.classList.add('folder-receive');
        hoverFolderEl = fEl;
        // Quitar highlight de todas las ventanas, poner sólo en la que está bajo el cursor
        Object.keys(openFolderWindows).forEach(function(fid){
            var fw = openFolderWindows[fid].el;
            fw.classList.remove('folder-window-receive');
        });
        var overFid = getOpenFolderWindowAt(x, y);
        if(overFid && folders[overFid] && overFid !== iconId &&
           !(folders[iconId] && isDescendant(overFid, iconId)) &&
           (folders[overFid].children||[]).indexOf(iconId) === -1){
            openFolderWindows[overFid].el.classList.add('folder-window-receive');
        }
    }
    function clearHover(){
        if(hoverFolderEl) hoverFolderEl.classList.remove('folder-receive');
        hoverFolderEl = null;
        Object.keys(openFolderWindows).forEach(function(fid){
            openFolderWindows[fid].el.classList.remove('folder-window-receive');
        });
    }

    /* Comprueba si `descId` está dentro del árbol de hijos de `ancestorId`
       (incluye el propio ancestor). Útil para evitar ciclos al anidar. */
    function isDescendant(descId, ancestorId){
        if(descId === ancestorId) return true;
        var f = folders[ancestorId];
        if(!f) return false;
        var children = f.children || [];
        for(var i=0; i<children.length; i++){
            if(isDescendant(descId, children[i])) return true;
        }
        return false;
    }

    /* Quita iconId de toda carpeta donde esté y devuelve la lista de
       carpetas afectadas, para que el caller las persista con saveFolder(id). */
    function unlinkAndCollect(iconId){
        var touched = [];
        Object.keys(folders).forEach(function(fid){
            var f = folders[fid];
            var idx = (f.children||[]).indexOf(iconId);
            if(idx !== -1){ f.children.splice(idx, 1); touched.push(fid); }
        });
        return touched;
    }

    function tryDrop(iconId, x, y){
        // 1) ¿Soltado sobre un icono de carpeta en el escritorio?
        var fEl = findFolderAt(iconId, x, y);
        if(fEl){
            var folderId = fEl.id;
            if(folderId === iconId) return false;                           // ella misma
            if(folders[iconId] && isDescendant(folderId, iconId)) return false; // ciclo
            if(folders[folderId] && folders[folderId].children.indexOf(iconId) === -1){
                var touched = unlinkAndCollect(iconId);
                folders[folderId].children.push(iconId);
                touched.push(folderId);
                touched.forEach(saveFolder);
            }
            var icon = document.getElementById(iconId); if(icon) icon.style.display = 'none';
            if(openFolderWindows[folderId]) renderFolderContent(folderId);
            return true;
        }

        // 2) ¿Soltado dentro de alguna ventana de carpeta abierta?
        var overFid = getOpenFolderWindowAt(x, y);
        if(overFid && folders[overFid]){
            if(overFid === iconId) return false;
            if(folders[iconId] && isDescendant(overFid, iconId)) return false;
            var f = folders[overFid];
            if(f.children.indexOf(iconId) === -1){
                var touched2 = unlinkAndCollect(iconId);
                f.children.push(iconId);
                touched2.push(overFid);
                touched2.forEach(saveFolder);
            }
            var ic = document.getElementById(iconId); if(ic) ic.style.display = 'none';
            renderFolderContent(overFid);
            return true;
        }

        return false;
    }

    function attachFolderItemDrag(item, folderId, iconId){
        item.addEventListener('mousedown', function(e){
            if(e.button !== 0) return;
            // Sólo iniciar drag si el usuario se mueve >5px (para no romper el dblclick)
            var startX = e.clientX, startY = e.clientY;
            var ghost = null;
            function makeGhost(){
                ghost = item.cloneNode(true);
                ghost.style.position = 'fixed';
                ghost.style.pointerEvents = 'none';
                ghost.style.opacity = '0.78';
                ghost.style.zIndex = '99999';
                ghost.style.transform = 'scale(1.05)';
                ghost.style.boxShadow = '0 4px 14px rgba(0,0,0,0.4)';
                document.body.appendChild(ghost);
                moveGhost(startX, startY);
            }
            function moveGhost(x, y){
                if(!ghost) return;
                ghost.style.left = (x - 40) + 'px';
                ghost.style.top  = (y - 24) + 'px';
            }
            function highlightTargets(x, y){
                // Resaltar carpeta destino: otra ventana o icono de carpeta del escritorio
                Object.keys(openFolderWindows).forEach(function(fid){
                    openFolderWindows[fid].el.classList.remove('folder-window-receive');
                });
                if(hoverFolderEl) hoverFolderEl.classList.remove('folder-receive');
                hoverFolderEl = null;

                var overFid = getOpenFolderWindowAt(x, y);
                if(overFid && overFid !== folderId){
                    openFolderWindows[overFid].el.classList.add('folder-window-receive');
                    return;
                }
                var fEl = findFolderAt(iconId, x, y);
                if(fEl && fEl.id !== folderId){
                    fEl.classList.add('folder-receive');
                    hoverFolderEl = fEl;
                }
            }
            function clearTargets(){
                Object.keys(openFolderWindows).forEach(function(fid){
                    openFolderWindows[fid].el.classList.remove('folder-window-receive');
                });
                if(hoverFolderEl) hoverFolderEl.classList.remove('folder-receive');
                hoverFolderEl = null;
            }
            function onMove(ev){
                var dx = ev.clientX - startX, dy = ev.clientY - startY;
                if(!ghost && (Math.abs(dx) > 5 || Math.abs(dy) > 5)) makeGhost();
                if(ghost){
                    ev.preventDefault();
                    moveGhost(ev.clientX, ev.clientY);
                    highlightTargets(ev.clientX, ev.clientY);
                }
            }
            function onUp(ev){
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                if(!ghost) return;       // click normal, no drag
                ghost.remove();
                clearTargets();

                // 1) ¿Soltó sobre OTRA ventana de carpeta? → mover entre carpetas
                var overFid = getOpenFolderWindowAt(ev.clientX, ev.clientY);
                if(overFid && overFid !== folderId){
                    moveIconBetweenFolders(folderId, overFid, iconId);
                    return;
                }
                // Si soltó sobre la MISMA ventana, no hacemos nada (no-op)
                if(overFid === folderId) return;

                // 2) ¿Soltó sobre un icono de carpeta del escritorio? → mover allí
                var fEl = findFolderAt(iconId, ev.clientX, ev.clientY);
                if(fEl && fEl.id !== folderId && folders[fEl.id]){
                    moveIconBetweenFolders(folderId, fEl.id, iconId);
                    return;
                }

                // 3) Fuera de todo → vuelve al escritorio en la posición del puntero
                removeFromFolderAt(folderId, iconId, ev.clientX, ev.clientY);
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    }

    function moveIconBetweenFolders(fromId, toId, iconId){
        var from = folders[fromId], to = folders[toId];
        if(!from || !to || fromId === toId) return;
        from.children = (from.children||[]).filter(function(c){ return c !== iconId; });
        if((to.children = to.children || []).indexOf(iconId) === -1) to.children.push(iconId);
        saveFolder(fromId);
        saveFolder(toId);
        if(openFolderWindows[fromId]) renderFolderContent(fromId);
        if(openFolderWindows[toId])   renderFolderContent(toId);
    }

    function removeFromFolderAt(folderId, iconId, screenX, screenY){
        var f = folders[folderId]; if(!f) return;
        f.children = (f.children||[]).filter(function(c){ return c !== iconId; });
        saveFolder(folderId);
        var el = document.getElementById(iconId);
        if(el && window.DesktopIcons){
            el.style.display = '';
            var desk = document.getElementById('desktop');
            var dr = desk.getBoundingClientRect();
            var localX = screenX - dr.left;
            var localY = screenY - dr.top;
            var pos = window.DesktopIcons.snapPos(localX, localY);
            // Clamp para no salirse del desktop
            var w = el.offsetWidth || 76, h = el.offsetHeight || 76;
            pos.left = Math.max(0, Math.min(desk.clientWidth - w, pos.left));
            pos.top  = Math.max(0, Math.min(desk.clientHeight - h, pos.top));
            el.style.left = pos.left + 'px';
            el.style.top  = pos.top  + 'px';
            window.DesktopIcons.savePosition(iconId, pos);
        } else if(el) {
            el.style.display = '';
        }
        if(openFolderWindows[folderId]) renderFolderContent(folderId);
    }

    function removeFromFolder(folderId, iconId){
        var f = folders[folderId]; if(!f) return;
        f.children = (f.children||[]).filter(function(c){ return c !== iconId; });
        saveFolder(folderId);
        var el = document.getElementById(iconId);
        if(el){
            el.style.display = '';
            // Reposicionar al lado de la carpeta para que sea visible
            var folderEl = document.getElementById(folderId);
            if(folderEl && window.DesktopIcons){
                var fp = { left: parseFloat(folderEl.style.left)||0, top: parseFloat(folderEl.style.top)||0 };
                var newPos = window.DesktopIcons.snapPos(fp.left + window.DesktopIcons.GRID_W, fp.top);
                el.style.left = newPos.left + 'px';
                el.style.top  = newPos.top + 'px';
                window.DesktopIcons.savePosition(iconId, newPos);
            }
        }
        if(openFolderWindows[folderId]) renderFolderContent(folderId);
    }

    function openFolderWindow(id){
        var f = folders[id]; if(!f) return;
        // Si ya está abierta: sólo bring-to-front (sin duplicar)
        if(openFolderWindows[id]){
            var existing = openFolderWindows[id].el;
            if(window.taskbarManager && taskbarManager.isRegistered(openFolderWindows[id].wid)){
                taskbarManager.restore(openFolderWindows[id].wid);
            } else {
                existing.style.display = 'flex';
            }
            if(window.windowZ) windowZ.bringToFront(existing);
            return;
        }

        // Clonar el template y darle IDs únicos
        var tpl = document.getElementById('folder-window-template');
        if(!tpl) return;
        var w = tpl.cloneNode(true);
        var wid = 'folder-window-' + id;
        w.id = wid;

        // Posición en cascada: cada nueva ventana se desplaza 28 px de la anterior
        var count = Object.keys(openFolderWindows).length;
        var baseL = window.innerWidth  * 0.18;
        var baseT = window.innerHeight * 0.14;
        w.style.left = (baseL + count * 28) + 'px';
        w.style.top  = (baseT + count * 28) + 'px';
        w.style.zIndex = '';   // lo asignará windowZ
        w.style.display = 'flex';

        var titleEl = w.querySelector('.title-bar-text');
        if(titleEl) titleEl.textContent = '📁 ' + f.name;

        var closeBtn = w.querySelector('[aria-label="Close"]');
        var minBtn   = w.querySelector('[aria-label="Minimize"]');
        var maxBtn   = w.querySelector('[aria-label="Maximize"]');
        if(closeBtn) closeBtn.onclick = function(){ closeFolderWindowById(id); };
        if(minBtn)   minBtn.onclick   = function(){
            if(window.taskbarManager) taskbarManager.minimize(wid);
        };
        if(maxBtn)   maxBtn.onclick   = function(){
            if(w.classList.contains('win-maximized')){
                w.classList.remove('win-maximized');
                maxBtn.setAttribute('aria-label', 'Maximize');
            } else {
                w.classList.add('win-maximized');
                maxBtn.setAttribute('aria-label', 'Restore');
            }
        };

        document.body.appendChild(w);
        openFolderWindows[id] = { el: w, wid: wid };

        // Right-click sobre el FONDO de la carpeta (no sobre un item) → "Nueva carpeta dentro"
        var bodyEl = w.querySelector('.folder-content');
        if(bodyEl){
            bodyEl.addEventListener('contextmenu', function(e){
                if(e.target.closest('.folder-item')) return;
                e.preventDefault();
                e.stopPropagation();
                showCtxMenu(e.clientX, e.clientY, id);
            });
        }

        // Drag + resize handles
        if(window.WindowManager) window.WindowManager.setup(wid, false);

        // Taskbar + bring-to-front
        if(window.taskbarManager) taskbarManager.register(wid, f.name, '📁', 'flex');
        if(window.windowZ) windowZ.bringToFront(w);

        renderFolderContent(id);
    }

    function closeFolderWindowById(id){
        var entry = openFolderWindows[id]; if(!entry) return;
        if(window.taskbarManager && taskbarManager.isRegistered(entry.wid)){
            taskbarManager.unregister(entry.wid);
        }
        entry.el.remove();
        delete openFolderWindows[id];
    }

    function renderFolderContent(id){
        var f = folders[id]; if(!f) return;
        var entry = openFolderWindows[id]; if(!entry) return;
        var box = entry.el.querySelector('.folder-content');
        if(!box) return;
        box.innerHTML = '';
        if(!f.children || !f.children.length){
            var e = document.createElement('div'); e.className = 'folder-empty';
            e.textContent = 'Carpeta vacía. Arrastra iconos aquí desde el escritorio.';
            box.appendChild(e); return;
        }
        f.children.forEach(function(cid){
            var orig = document.getElementById(cid);
            if(!orig) return;
            var imgHtml = (orig.querySelector('.desktop-icon-img') || {}).innerHTML || '';
            var label   = (orig.querySelector('span') || {}).textContent || cid;
            var item = document.createElement('div');
            item.className = 'folder-item';
            item.dataset.iconId = cid;
            item.innerHTML =
                '<div class="fi-img">' + imgHtml + '</div>' +
                '<div class="fi-label">' + esc(label) + '</div>';
            item.addEventListener('dblclick', function(){
                // Disparar el dblclick original que abre la app
                var ev = new MouseEvent('dblclick', { bubbles: true, cancelable: true });
                orig.dispatchEvent(ev);
            });
            // Si el hijo ES una carpeta, click derecho la borra (igual que en escritorio)
            if(folders[cid]){
                item.addEventListener('contextmenu', function(e){
                    e.preventDefault(); e.stopPropagation();
                    deleteFolder(cid);
                });
            }
            // Drag-out: arrastra el icono fuera de la ventana → vuelve al escritorio
            attachFolderItemDrag(item, id, cid);
            box.appendChild(item);
        });
    }

    /* ── Right-click sobre el escritorio O sobre el fondo de una carpeta ── */
    var lastCtxX = 0, lastCtxY = 0;
    var ctxTargetFolderId = null;   // null = crear en el escritorio; id = crear dentro de esa carpeta
    function showCtxMenu(x, y, targetFolderId){
        var menu = document.getElementById('desktop-ctx-menu');
        lastCtxX = x; lastCtxY = y;
        ctxTargetFolderId = targetFolderId || null;
        menu.style.left = x + 'px';
        menu.style.top  = y + 'px';
        menu.classList.add('show');
    }
    function hideCtxMenu(){ document.getElementById('desktop-ctx-menu').classList.remove('show'); }

    function bindMenu(){
        var desk = document.getElementById('desktop');
        if(!desk) return;
        desk.addEventListener('contextmenu', function(e){
            // sólo si se clica en el fondo (no en un icono)
            if(e.target.closest('.desktop-icon')) return;
            e.preventDefault();
            showCtxMenu(e.clientX, e.clientY);
        });
        document.addEventListener('click',       function(){ hideCtxMenu(); });
        document.addEventListener('contextmenu', function(e){
            if(!e.target.closest('#desktop')) hideCtxMenu();
        });
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape') hideCtxMenu();
        });
        var menu = document.getElementById('desktop-ctx-menu');
        menu.addEventListener('click', function(e){
            var li = e.target.closest('li[data-action]');
            if(!li) return;
            var target = ctxTargetFolderId;
            hideCtxMenu();
            if(li.dataset.action === 'new-folder'){
                if(target) createFolderInside(target);
                else       createFolder(lastCtxX, lastCtxY);
            }
        });
        // Ventana de carpeta
        var fc = document.getElementById('folder-close');
        if(fc) fc.addEventListener('click', closeFolderWindow);
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', bindMenu);
    } else {
        bindMenu();
    }

    window.DesktopFolders = {
        init: init,
        updateHover: updateHover,
        clearHover: clearHover,
        tryDrop: tryDrop,
        openFolder: openFolderWindow,
        closeFolder: closeFolderWindowById
    };
})();
</script>

</body>
</html>