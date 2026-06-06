<?php
/* No imprimir warnings/notices en la salida: en algunos XAMPP (Windows)
   display_errors viene On por defecto y un aviso de PHP se colaría dentro
   del HTML/JS, rompiendo el script (p.ej. "taskbarManager is not defined").
   Los errores se siguen registrando en el log del servidor. */
@ini_set('display_errors', '0');
error_reporting(E_ALL);

if (!isset($desktopLabel)) { header('Location: index.php'); exit; }

/* Móviles SIEMPRE entran por la landing (mobile-landing.php) — el
   escritorio Win98 no es accesible desde móvil. La landing decide si
   pintar el pitch de instalación o rebotar al home (cuando ya está
   dentro de la PWA). Override con ?desktop=1 o cookie force_desktop. */
require_once __DIR__ . '/assets/mobile-detect.php';
if (isMobileDevice()) { header('Location: mobile-landing.php'); exit; }

setLongSessionCookie();
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

// Tras validar sesión: averiguar la pareja del usuario (0 si no tiene).
require_once __DIR__ . '/db.php';
$stmtP = $pdo->prepare("
    SELECT p.id FROM parejas p
    JOIN usuarios u1 ON p.usuario1_id = u1.id
    JOIN usuarios u2 ON p.usuario2_id = u2.id
    WHERE u1.username = ? OR u2.username = ?
");
$stmtP->execute([strtolower($desktopLabel), strtolower($desktopLabel)]);
$rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
$parejaId = $rowP ? (int)$rowP['id'] : 0;

$projectBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

$hasPlayer = true;

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

$wallpaper = getUserWallpaper($desktopLabel);
$startIcon = getUserStartIcon($desktopLabel);

/* Haro activo del usuario para el sistema de notificaciones — se emite
   como global JS más abajo. Si user_settings no tiene preferencia, cae
   al haro 'green' (base que todos los usuarios tienen). */
require_once __DIR__ . '/assets/personalize/haro-paths.php';
$haroUid       = userIdByKey($desktopUserKey);
$haroSlug      = $haroUid ? activeHaroSlug($pdo, $haroUid) : 'green';
$haroAssets    = haroPaths($haroSlug);
if ($activeTheme !== '') {
    $uid = userIdByKey($desktopUserKey);
    $tWp = ''; $tSi = '';
    if ($uid) {
        $st = themesPdo()->prepare("SELECT wallpaper, start_icon FROM themes WHERE user_id = ? AND name = ?");
        $st->execute([$uid, $activeTheme]);
        $arow = $st->fetch(PDO::FETCH_ASSOC);
        if ($arow) { $tWp = (string)$arow['wallpaper']; $tSi = (string)$arow['start_icon']; }
    }
    /* Si el tema tiene asset propio y existe → úsalo; si no, mantén el
       wallpaper/icono global del usuario calculado arriba (no se cae al
       default salvo que el propio global tampoco exista). */
    if ($tWp !== '' && file_exists(__DIR__ . '/' . $tWp)) $wallpaper = $tWp;
    if ($tSi !== '' && file_exists(__DIR__ . '/' . $tSi)) $startIcon = $tSi;
}

$bodyStyles = [];
if ($wallpaper) $bodyStyles[] = "background-image:url('{$wallpaper}')";
if ($startIcon) $bodyStyles[] = "--start-icon-url:url('{$projectBaseUrl}{$startIcon}')";
$wallpaperStyle = $bodyStyles ? implode(';', $bodyStyles) : '';

$_iconTheme = 'default';
function desktopIcon(string $name, string $emoji) {
    global $_iconTheme;
    foreach ([$_iconTheme, 'default'] as $dir) {
        foreach (['png', 'jpg', 'gif'] as $ext) {
            $rel  = "assets/img/icons/{$dir}/{$name}.{$ext}";
            if (file_exists(__DIR__ . '/' . $rel)) {
                return '<img src="' . $rel . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;">';
            }
        }
    }
    return $emoji;
}

/* Helper para el title-bar de las ventanas: usa el PNG de
   assets/img/appIcons/{pngName}.png si existe (16×16) o cae al emoji.
   Pensado para que cada ventana de app muestre el MISMO icono que aparece
   en el escritorio, no un emoji distinto. */
function appTitleIcon(string $pngName, string $emoji): string {
    $rel = "assets/img/appIcons/{$pngName}.png";
    if (file_exists(__DIR__ . '/' . $rel)) {
        return '<img src="' . $rel . '" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;" alt="">';
    }
    return $emoji . ' ';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Tablets: layout fijo a 1280 → el navegador lo escala para encajar
         en el ancho del dispositivo. Pinch-zoom queda habilitado para
         que el usuario amplíe los elementos pequeños de Win98 si los
         dedos no caben. -->
    <meta name="viewport" content="width=1280, user-scalable=yes">
    <title><?php echo htmlspecialchars($desktopLabel); ?> - Escritorio</title>
    <link rel="icon" href="assets/img/mobile/icon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
    <link rel="stylesheet" href="assets/css/reproductor.css">
    <link rel="stylesheet" href="assets/css/perfil.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <script>
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
    $bodyClasses = [];
    if ($activeThemeClass) $bodyClasses[] = $activeThemeClass;
    if ($startIcon) $bodyClasses[] = 'has-start-icon';
    echo htmlspecialchars(implode(' ', $bodyClasses));
?>"<?php echo $wallpaperStyle ? " style=\"{$wallpaperStyle}\"" : ''; ?>>

<div id="page-enter"></div>

<script>
    window.DesktopParejaId = <?php echo (int)$parejaId; ?>;
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

<!-- ICON CONTEXT MENU (táctil: long-press sobre un icono) -->
<ul id="desktop-icon-ctx-menu" class="desk-ctx">
    <li data-action="move">📦 Mover</li>
</ul>

<!-- FOLDER WINDOW TEMPLATE -->
<div class="window" id="folder-window-template" data-no-auto-z style="display:none; position:fixed; width:460px; height:340px; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text"><?php echo appTitleIcon('folderIcon', '📁'); ?>Carpeta</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close"></button>
        </div>
    </div>
    <div class="window-body folder-content"></div>
</div>

<!-- MODAL "Nueva carpeta" -->
<div class="window" id="folder-create-modal" data-no-auto-z style="display:none; flex-direction:column; position:fixed;">
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

<!-- MODAL cambiar contraseña -->
<div class="window" id="change-password-modal" data-no-auto-z style="display:none; flex-direction:column; position:fixed; width:340px;">
    <div class="title-bar">
        <div class="title-bar-text">🔑 Cambiar contraseña</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="change-password-x"></button>
        </div>
    </div>
    <div class="window-body" style="padding:14px;">
        <div style="display:flex;flex-direction:column;gap:8px;">
            <label style="font-size:11px;">Contraseña actual:
                <input id="cp-current" type="password" autocomplete="current-password" style="width:100%;display:block;margin-top:3px;">
            </label>
            <label style="font-size:11px;">Nueva contraseña:
                <input id="cp-new" type="password" autocomplete="new-password" minlength="6" style="width:100%;display:block;margin-top:3px;">
            </label>
            <label style="font-size:11px;">Repetir nueva:
                <input id="cp-confirm" type="password" autocomplete="new-password" minlength="6" style="width:100%;display:block;margin-top:3px;">
            </label>
        </div>
        <p id="cp-status" style="margin:10px 0 0;font-size:11px;min-height:14px;"></p>
        <div style="display:flex;justify-content:flex-end;gap:6px;margin-top:12px;">
            <button id="cp-ok" class="default">Aceptar</button>
            <button id="cp-cancel">Cancelar</button>
        </div>
    </div>
</div>

<!-- MODAL confirmar borrar carpeta -->
<div class="window" id="folder-delete-modal" data-no-auto-z style="display:none; flex-direction:column; position:fixed;">
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
        <div class="desktop-icon-img"><?php
            $_archiveIcon = 'assets/img/appIcons/melonArchiveIcon.png';
            echo file_exists(__DIR__ . '/' . $_archiveIcon)
                ? '<img src="' . $_archiveIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : desktopIcon('archive', '📼');
        ?></div>
        <span>MelonArchive</span>
    </div>
    <div class="desktop-icon" id="calendar-icon">
        <div class="desktop-icon-img"><?php
            $_calendarIcon = 'assets/img/appIcons/calendarioIcon.png';
            echo file_exists(__DIR__ . '/' . $_calendarIcon)
                ? '<img src="' . $_calendarIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;">'
                : desktopIcon('calendar', '📅');
        ?></div>
        <span>Calendario</span>
    </div>
    <div class="desktop-icon" id="profile-icon">
        <div class="desktop-icon-img"><?php
            $_profileIcon = 'assets/img/appIcons/profileIcon.png';
            echo file_exists(__DIR__ . '/' . $_profileIcon)
                ? '<img src="' . $_profileIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : desktopIcon('profile', '👤');
        ?></div>
        <span>Perfil</span>
    </div>
    <div class="desktop-icon" id="temas-icon">
        <div class="desktop-icon-img"><?php
            $_temasIcon = 'assets/img/appIcons/temasIcon.png';
            echo file_exists(__DIR__ . '/' . $_temasIcon)
                ? '<img src="' . $_temasIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : desktopIcon('temas', '🎨');
        ?></div>
        <span>Temas</span>
    </div>
    <div class="desktop-icon" id="companion-icon">
        <div class="desktop-icon-img"><?php
            $_companionIcon = 'assets/img/appIcons/companionIcon.png';
            echo file_exists(__DIR__ . '/' . $_companionIcon)
                ? '<img src="' . $_companionIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : desktopIcon('companion', '💀');
        ?></div>
        <span>Companion</span>
    </div>
    <div class="desktop-icon" id="dnd-icon">
        <div class="desktop-icon-img"><?php
            $_dndIcon = 'assets/img/appIcons/dndIcon.png';
            echo file_exists(__DIR__ . '/' . $_dndIcon)
                ? '<img src="' . $_dndIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;">'
                : desktopIcon('dnd', '⚔');
        ?></div>
        <span>Fichas D&amp;D</span>
    </div>
    <div class="desktop-icon" id="galeria-icon">
        <div class="desktop-icon-img"><?php
            $_galeriaIcon = 'assets/img/appIcons/galeriaIcon.png';
            echo file_exists(__DIR__ . '/' . $_galeriaIcon)
                ? '<img src="' . $_galeriaIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : desktopIcon('galeria', '🖼');
        ?></div>
        <span>Galería</span>
    </div>
    <!-- ★ NUEVO: icono Dibujo -->
    <div class="desktop-icon" id="dibujo-icon">
        <div class="desktop-icon-img"><?php
            $_drawingIcon = 'assets/img/appIcons/drawingIcon.png';
            echo file_exists(__DIR__ . '/' . $_drawingIcon)
                ? '<img src="' . $_drawingIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;">'
                : desktopIcon('dibujo', '✏️');
        ?></div>
        <span>Dibujo</span>
    </div>
    <div class="desktop-icon" id="tienda-icon">
        <div class="desktop-icon-img"><?php
            $_tiendaIcon = 'assets/img/appIcons/tiendaIcon.png';
            echo file_exists(__DIR__ . '/' . $_tiendaIcon)
                ? '<img src="' . $_tiendaIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : desktopIcon('tienda', '🛒');
        ?></div>
        <span>Tienda</span>
    </div>
    <!-- App MASCOTA: doble-click → spawnea la mascota (o el huevo si no
         está eclosionada) en pantalla via MascotaEngine.spawn(). -->
    <div class="desktop-icon" id="mascota-icon">
        <div class="desktop-icon-img"><?php
            $_mascotaIcon = 'assets/img/appIcons/mascotaIcon.png';
            echo file_exists(__DIR__ . '/' . $_mascotaIcon)
                ? '<img src="' . $_mascotaIcon . '" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt="">'
                : '<div style="font-size:48px;line-height:1;">🐾</div>';
        ?></div>
        <span>Mascota</span>
    </div>
</div>

<!-- CALENDAR WINDOW -->
<div class="window" id="calendar-window" style="display:none; position:fixed; left:5vw; top:4vh; width:90vw; height:88vh; z-index:500; flex-direction:column;">
    <div class="title-bar" id="calendar-titlebar">
        <div class="title-bar-text"><?php echo appTitleIcon('calendarioIcon', '📅'); ?>Calendario</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="calendar-close"></button>
        </div>
    </div>
    <iframe id="calendar-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- MASCOTA WINDOW -->
<div class="window" id="mascota-window"
     style="display:none; position:fixed; left:15vw; top:8vh; width:380px; height:480px; z-index:550; flex-direction:column;">
    <div class="title-bar" id="mascota-titlebar">
        <div class="title-bar-text"><?php echo appTitleIcon('mascotaIcon', '🐾'); ?>Mascota</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="mascota-close"></button>
        </div>
    </div>
    <iframe id="mascota-frame" src="" frameborder="0"
            style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- VENTANA "Wrapped" — Spotify Wrapped del usuario.
     Embed del slide deck en iframe full-screen. Se abre por dos vías:
       1. Notificación automática cada 22 de diciembre.
       2. Botón DEV (configurable). -->
<div class="window" id="wrapped-window"
     style="display:none; position:fixed; left:5vw; top:4vh; width:90vw; height:88vh; z-index:9500; flex-direction:column;">
    <div class="title-bar" id="wrapped-titlebar">
        <div class="title-bar-text">🎁 Wrapped</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="wrapped-close-window"></button>
        </div>
    </div>
    <iframe id="wrapped-frame" src="" frameborder="0"
            style="flex:1; width:100%; border:none; display:block; background:#000;"></iframe>
</div>

<!-- BOTÓN DEV — abre el wrapped con el flag ?dev=1 (todas las plays
     sin filtro de año). Posición fija arriba-derecha del escritorio
     para acceso fácil mientras se desarrolla. -->
<button id="wrapped-dev-btn" type="button"
        style="position:fixed;top:46px;right:8px;z-index:9001;background:linear-gradient(135deg,#1db954,#191414);color:#fff;border:1px solid #0d0d0d;padding:4px 10px;font-size:11px;font-weight:bold;cursor:pointer;border-radius:0;box-shadow:2px 2px 0 #0a0a0a;font-family:inherit;"
        title="Spotify Wrapped (modo DEV — todas las plays)">
    🎁 Wrapped (DEV)
</button>

<!-- VENTANA "Alimentar" — picker de comidas. Se monta con el catálogo
     de assets/mascota/foods.php (no se duplica). Al click → POST feed
     con el slug → cierra ventana → muestra reacción. -->
<?php require_once __DIR__ . '/assets/mascota/foods.php'; ?>
<div class="window" id="alimentar-window"
     style="display:none; position:fixed; left:30vw; top:18vh; width:320px; z-index:560; flex-direction:column;">
    <div class="title-bar" id="alimentar-titlebar">
        <div class="title-bar-text">🍴 Alimentar</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="alimentar-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:8px;">
        <p style="margin:0 0 8px;font-size:11px;">¿Qué le das?</p>
        <div id="alimentar-grid"
             style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
            <?php foreach (MASCOTA_FOODS as $slug => $meta): ?>
            <button type="button" class="alimentar-btn" data-slug="<?= htmlspecialchars($slug) ?>"
                    title="<?= htmlspecialchars($meta['nombre']) ?>"
                    style="min-width:0;min-height:60px;padding:4px 2px;display:flex;flex-direction:column;align-items:center;gap:2px;cursor:pointer;">
                <span style="font-size:24px;line-height:1;"><?= $meta['emoji'] ?></span>
                <span style="font-size:9px;line-height:1.1;text-align:center;color:transparent;text-shadow:0 0 var(--text,#000);"><?= htmlspecialchars($meta['nombre']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ARCHIVE WINDOW -->
<div class="window" id="archive-window">
    <div class="title-bar" id="archive-titlebar">
        <div class="title-bar-text"><?php echo appTitleIcon('melonArchiveIcon', '📼'); ?>MelonArchive</div>
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
        <div class="title-bar-text"><?php echo appTitleIcon('temasIcon', '🎨'); ?>Temas</div>
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
        <div class="title-bar-text"><?php echo appTitleIcon('companionIcon', '💀'); ?>Helldivers Companion</div>
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
        <div class="title-bar-text"><?php echo appTitleIcon('dndIcon', '⚔'); ?>Fichas D&amp;D</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="dnd-close"></button>
        </div>
    </div>
    <iframe id="dnd-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- GALERÍA WINDOW -->
<div class="window" id="galeria-window" style="display:none; position:fixed; left:5vw; top:4vh; width:90vw; height:88vh; z-index:500; flex-direction:column;">
    <div class="title-bar" id="galeria-titlebar">
        <div class="title-bar-text"><?php echo appTitleIcon('galeriaIcon', '🖼'); ?>Galería</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="galeria-close"></button>
        </div>
    </div>
    <iframe id="galeria-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- ★ NUEVO: DIBUJO WINDOW -->
<div class="window" id="dibujo-window" style="display:none; position:fixed; left:5vw; top:4vh; width:90vw; height:88vh; z-index:500; flex-direction:column;">
    <div class="title-bar" id="dibujo-titlebar">
        <div class="title-bar-text"><?php echo appTitleIcon('drawingIcon', '✏️'); ?>Dibujo</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="dibujo-close"></button>
        </div>
    </div>
    <iframe id="dibujo-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- KO-FI WINDOW (lo abre la app de Tienda vía postMessage; sin icono propio) -->
<div class="window" id="kofi-window" style="display:none; position:fixed; left:32vw; top:8vh; width:36vw; height:80vh; min-width:380px; z-index:560; flex-direction:column;">
    <div class="title-bar" id="kofi-titlebar">
        <div class="title-bar-text">☕ Donar (Ko-fi)</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="kofi-close"></button>
        </div>
    </div>
    <iframe id="kofi-iframe" src="" frameborder="0" referrerpolicy="no-referrer" style="flex:1; width:100%; border:none; display:block; background:#fff;"></iframe>
</div>

<!-- TIENDA WINDOW -->
<div class="window" id="tienda-window" style="display:none; position:fixed; left:8vw; top:6vh; width:84vw; height:84vh; z-index:500; flex-direction:column;">
    <div class="title-bar" id="tienda-titlebar">
        <div class="title-bar-text"><?php echo appTitleIcon('tiendaIcon', '🛒'); ?>Tienda</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="tienda-close"></button>
        </div>
    </div>
    <iframe id="tienda-iframe" src="" frameborder="0" style="flex:1; width:100%; border:none; display:block;"></iframe>
</div>

<!-- NOTIFICATION STACK -->
<div id="notif-container"></div>

<!-- START MENU -->
<div id="start-menu">
    <div id="start-menu-inner">
        <div id="start-sidebar">MelonOS 98</div>
        <div id="start-menu-items">
            <div class="menu-sep"></div>
            <a class="menu-item" href="#" id="menu-change-password">Cambiar contraseña...</a>
            <a class="menu-item" href="logout.php">Cerrar sesión...</a>
            <div class="menu-sep"></div>
        </div>
    </div>
</div>

<!-- MASCOTA -->
 <div id="mascota-root"></div>

 <!-- MASCOTA MENU BTN — botón flotante cuadrado con icono ☰ (tres barras).
      Esquina inferior derecha encima del taskbar. Se mantiene OCULTO
      hasta que el engine dispara `spawn()` (al pulsar el icono de la app
      en el escritorio). -->
<button id="mascota-menu-btn" title="Mascota" aria-label="Menú mascota" style="display:none;">☰</button>

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
function updateClock() {
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
    /* Conserva solo clases estructurales; capi/angie ya no se usan. */
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
        ['calendar-iframe', 'archive-frame', 'temas-frame', 'companion-frame', 'dnd-iframe', 'galeria-iframe'].forEach(function(id) {
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
    } else if (e.data.type === 'haro-changed') {
        if (typeof window.applyHaroSlug === 'function') {
            window.applyHaroSlug(e.data.slug || 'green');
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
        ['calendar-iframe', 'archive-frame', 'temas-frame', 'companion-frame', 'dnd-iframe', 'galeria-iframe'].forEach(function(id) {
            var fr = document.getElementById(id);
            if (fr && fr.contentDocument) refreshImgs(fr.contentDocument);
        });
    } else if (e.data.type === 'open-profile') {
        if (typeof window.openProfileAtUser === 'function') {
            window.openProfileAtUser(e.data.userKey);
        }
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
   CAMBIAR CONTRASEÑA
   ========================
   Abre un modal Win98 con 3 inputs (actual / nueva / repetir).
   Valida en cliente (mismas reglas que en backend) y hace POST al
   endpoint assets/auth/change-password.php. En éxito muestra un toast
   verde y cierra; en error pinta el motivo en #cp-status. */
(function(){
    var link = document.getElementById('menu-change-password');
    if (!link) return;
    var modal   = document.getElementById('change-password-modal');
    var current = document.getElementById('cp-current');
    var newPwd  = document.getElementById('cp-new');
    var confirm = document.getElementById('cp-confirm');
    var status  = document.getElementById('cp-status');
    var okBtn   = document.getElementById('cp-ok');

    function reset(){
        current.value = ''; newPwd.value = ''; confirm.value = '';
        status.textContent = ''; status.style.color = '';
        okBtn.disabled = false;
    }
    function open(e){
        if (e) e.preventDefault();
        reset();
        modal.style.display = 'flex';
        if (window.centerModal) window.centerModal(modal);
        if (window.windowZ) windowZ.bringToFront('change-password-modal');
        setTimeout(function(){ current.focus(); }, 30);
    }
    function close(){ modal.style.display = 'none'; reset(); }

    link.addEventListener('click', open);
    document.getElementById('change-password-x').addEventListener('click', close);
    document.getElementById('cp-cancel').addEventListener('click', close);

    async function submit(){
        var c = current.value, n = newPwd.value, r = confirm.value;
        if (!c || !n || !r){ status.style.color = 'var(--error-text,#c00)'; status.textContent = 'Rellena todos los campos.'; return; }
        if (n.length < 6){ status.style.color = 'var(--error-text,#c00)'; status.textContent = 'La nueva debe tener al menos 6 caracteres.'; return; }
        if (n !== r){ status.style.color = 'var(--error-text,#c00)'; status.textContent = 'La nueva contraseña y la repetición no coinciden.'; return; }
        if (c === n){ status.style.color = 'var(--error-text,#c00)'; status.textContent = 'La nueva tiene que ser distinta de la actual.'; return; }

        okBtn.disabled = true;
        status.style.color = 'var(--text-muted,#666)';
        status.textContent = 'Guardando…';
        try {
            var resp = await fetch('assets/auth/change-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current: c, new: n })
            });
            var data = await resp.json().catch(function(){ return { error: 'Respuesta inválida' }; });
            if (!resp.ok || data.error){
                status.style.color = 'var(--error-text,#c00)';
                status.textContent = '✗ ' + (data.error || ('HTTP ' + resp.status));
                okBtn.disabled = false;
                return;
            }
            status.style.color = 'var(--accent-deep,#080)';
            status.textContent = '✔ Contraseña actualizada.';
            setTimeout(close, 900);
        } catch (err) {
            status.style.color = 'var(--error-text,#c00)';
            status.textContent = '✗ Error de red: ' + err.message;
            okBtn.disabled = false;
        }
    }
    okBtn.addEventListener('click', submit);
    /* Enter en cualquiera de los inputs envía. */
    [current, newPwd, confirm].forEach(function(el){
        el.addEventListener('keydown', function(ev){
            if (ev.key === 'Enter'){ ev.preventDefault(); submit(); }
            else if (ev.key === 'Escape'){ ev.preventDefault(); close(); }
        });
    });
})();

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
window.windowZ = (function() {
    var topZ = 600;
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
            if (w.hasAttribute('data-no-auto-z')) return;
            if (w.style.display === 'none' || getComputedStyle(w).display === 'none') return;
            var z = parseInt(w.style.zIndex || getComputedStyle(w).zIndex || '0', 10) || 0;
            if (z > maxZ) maxZ = z;
        });
        return myZ >= maxZ;
    }

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
        if (el.hasAttribute('data-no-auto-z')) return;
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
        /* `icon` puede ser un emoji o HTML (<img>). Para soportar ambos
           usamos innerHTML y escapamos solo el label (controlado por
           callers internos, pero por defensa). */
        function _esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
        btn.innerHTML = (icon ? icon + ' ' : '') + _esc(label);
        btn.addEventListener('click', function() { toggle(id); });
        tasksEl.appendChild(btn);
        registry[id] = { btn: btn, displayMode: displayMode || 'block' };
        win.style.display = displayMode || 'block';
        windowZ.bringToFront(id);
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
        windowZ.bringToFront(id);
    }

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

/* Haro activo del usuario — emitido por PHP. El sistema de
   notificaciones lo usa como fuente de verdad para sus URLs y se puede
   reasignar en runtime vía window.applyHaroSlug(slug) sin recargar. */
window.ACTIVE_HARO = <?= json_encode($haroAssets, JSON_UNESCAPED_SLASHES) ?>;

/* =========================
   SISTEMA UNIFICADO DE NOTIFICACIONES
========================= */
window.notifSystem = (function() {
    var container = document.getElementById('notif-container');
    var MAX_VISIBLE  = 3;
    var shownIds     = {};
    var dismissedIds = {};
    var pendingQueue = [];

    /* Animación del Haro: 29 frames @ 10 fps = 2900 ms.
       - HARO_REVEAL_MS: la tarjeta hace pop mientras el Haro aún está
         dando los últimos frames de su rodada, no esperando al final
         exacto. Da sensación de que la entrega es continua.
       - HARO_DURATION_MS: al cumplirse, swap del gif por el PNG estático
         del último frame (extraído con ffmpeg). El "freeze" vía canvas
         no funciona — drawImage(gif) captura el primer frame en casi
         todos los browsers. Por eso usamos un PNG estático.

       Las URLs vienen de window.ACTIVE_HARO (lo emite PHP según el haro
       activo del usuario). Helpers para leerlas dinámicamente — así un
       cambio de haro en personalización surte efecto al instante. */
    function haroGifUrl()   { return (window.ACTIVE_HARO && window.ACTIVE_HARO.gif)   || 'assets/vids/greenHaro.gif'; }
    function haroLastUrl()  { return (window.ACTIVE_HARO && window.ACTIVE_HARO.last)  || 'assets/vids/greenHaro-last.png'; }
    function haroAudioUrl() { return (window.ACTIVE_HARO && window.ACTIVE_HARO.audio) || 'assets/sound/haro.mp3'; }
    var HARO_REVEAL_MS   = 2200;
    var HARO_DURATION_MS = 2900;

    /* Precarga el PNG estático una vez para que el swap del gif al
       último frame sea instantáneo (sin flash). */
    (function preloadLastFrame() {
        var p = new Image();
        p.src = haroLastUrl();
    })();

    /* Audio que se reproduce al irse el Haro. Preload=auto para que el
       primer play no espere a la red. Lo reusamos en cada dismiss
       reseteando currentTime.

       Warm-up: muchos browsers bloquean Audio.play() si no hay un
       "gesture" reciente del usuario; un dismiss disparado desde un
       setTimeout o desde un animationend NO cuenta como gesto. Para
       desbloquear, hacemos un play+pause silencioso en el primer
       click/keydown que llegue a la página. A partir de ahí, la
       política de autoplay considera el elemento "iniciado por usuario"
       y nos deja reproducirlo más tarde sin gesto activo. */
    var haroAudio = new Audio(haroAudioUrl());
    haroAudio.preload = 'auto';
    (function primeOnFirstGesture() {
        var primed = false;
        function prime() {
            if (primed) return;
            primed = true;
            try {
                haroAudio.muted = true;
                var p = haroAudio.play();
                if (p && typeof p.then === 'function') {
                    p.then(function() {
                        haroAudio.pause();
                        haroAudio.currentTime = 0;
                        haroAudio.muted = false;
                    }).catch(function() { haroAudio.muted = false; });
                } else {
                    haroAudio.pause();
                    haroAudio.currentTime = 0;
                    haroAudio.muted = false;
                }
            } catch (_) { haroAudio.muted = false; }
            window.removeEventListener('click',   prime, true);
            window.removeEventListener('keydown', prime, true);
            window.removeEventListener('touchstart', prime, true);
        }
        window.addEventListener('click',      prime, true);
        window.addEventListener('keydown',    prime, true);
        window.addEventListener('touchstart', prime, true);
    })();

    /* El Haro vive a nivel del contenedor, no de un slot. Así sigue
       en pantalla aunque todas las notificaciones de su "tanda" se
       cierren — solo se va cuando el contenedor de slots se vacía.
       Una nueva notif tras el dismiss del Haro arranca otra tanda. */
    var activeHaro   = null;
    var haroPending  = false;

    /* Reproduce el sonido del Haro. Lo dispara cualquier "aparición" de
       notificación: tanto la revelación en lote tras el giro del Haro
       como cualquier notif que entre después (sin Haro). */
    function playHaroSound() {
        try {
            haroAudio.currentTime = 0;
            var pPlay = haroAudio.play();
            if (pPlay && typeof pPlay.then === 'function') {
                pPlay.catch(function(err) {
                    console.warn('[notifSystem] haroAudio play bloqueado:', err);
                });
            }
        } catch (e) {
            console.warn('[notifSystem] haroAudio threw:', e);
        }
    }

    function createHaroEl() {
        if (activeHaro) return activeHaro;
        var h = document.createElement('img');
        h.className = 'notif-haro';
        h.alt = '';
        h.src = haroGifUrl() + '?t=' + Date.now();
        container.appendChild(h);
        activeHaro  = h;
        haroPending = true;

        /* REVEAL_MS: revela todos los slots pendientes mientras la
           bola sigue dando sus últimos frames. Suena el audio en el
           mismo instante en que las tarjetas aparecen. */
        setTimeout(function() {
            if (activeHaro !== h) return;
            haroPending = false;
            var pending = container.querySelectorAll('.notif-slot.pending');
            for (var i = 0; i < pending.length; i++) {
                var p = pending[i];
                p.classList.remove('pending');
                p.classList.add('revealed');
                if (p._startDismiss) {
                    var fn = p._startDismiss;
                    p._startDismiss = null;
                    fn();
                }
            }
            if (pending.length) playHaroSound();
        }, HARO_REVEAL_MS);

        /* DURATION_MS: swap del gif por el PNG estático. */
        setTimeout(function() {
            if (activeHaro !== h) return;
            h.src = haroLastUrl();
            h.classList.add('landed');
        }, HARO_DURATION_MS);

        return h;
    }

    function dismissHaroEl() {
        if (!activeHaro) return;
        var h = activeHaro;
        activeHaro  = null;
        haroPending = false;
        h.classList.add('exiting');
        h.addEventListener('animationend', function() {
            if (h.parentNode) h.remove();
        }, { once: true });
    }

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

    function removeSlot(slot) {
        dismissedIds[slot.dataset.id] = true;
        delete shownIds[slot.dataset.id];
        slot.classList.add('notif-card-exiting');
        slot.addEventListener('animationend', function() {
            slot.remove();
            flushQueue();
            /* El Haro solo se va cuando no quedan slots NI cola
               pendiente. Si flushQueue creó nuevos slots, hay con qué
               seguir y el Haro sigue donde está. */
            if (!container.querySelector('.notif-slot') && pendingQueue.length === 0) {
                dismissHaroEl();
            }
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

        /* Política del Haro (a nivel del contenedor, no del slot):
           - Si no hay Haro activo, esta notif inaugura una nueva tanda
             y lo crea. Las que lleguen mientras la bola gire empezarán
             pending y se revelarán todas juntas en HARO_REVEAL_MS.
           - El Haro persiste mientras quede AL MENOS un slot; cuando
             el último se va, dismissHaroEl() lo retira. */
        if (!activeHaro) createHaroEl();
        var startsPending = haroPending;

        var slot = document.createElement('div');
        slot.className = 'notif-slot';
        if (startsPending) slot.classList.add('pending');
        slot.dataset.id = opts.id;

        var card = document.createElement('div');
        card.className = 'window notif-card ' + (isAction ? 'notif-card-action' : 'notif-card-info');

        var tb = document.createElement('div'); tb.className = 'title-bar';
        var tbText = document.createElement('div'); tbText.className = 'title-bar-text';
        tbText.textContent = opts.title || 'Notificación';
        tb.appendChild(tbText);
        card.appendChild(tb);

        var body = document.createElement('div'); body.className = 'window-body';
        body.style.padding = '5px 8px 6px';

        if (isAction && opts.senderImage) {
            var topRow = document.createElement('div');
            topRow.style.cssText = 'display:flex;align-items:center;gap:6px;margin-bottom:4px;';
            var avWrap = document.createElement('div'); avWrap.className = 'collab-avatar-wrap';
            var avImg = document.createElement('img'); avImg.className = 'collab-avatar-img';
            avImg.src = opts.senderImage;
            avWrap.appendChild(avImg); topRow.appendChild(avWrap);
            var msgSpan = document.createElement('span'); msgSpan.style.cssText = 'font-size:11px;';
            msgSpan.textContent = opts.message || '';
            topRow.appendChild(msgSpan); body.appendChild(topRow);
        } else {
            var msgEl = document.createElement('p'); msgEl.style.cssText = 'margin:0 0 3px;font-size:11px;';
            msgEl.textContent = opts.message || ''; body.appendChild(msgEl);
        }

        var timeEl = document.createElement('span'); timeEl.className = 'notif-time';
        if (opts.sentAt) timeEl.dataset.sent = opts.sentAt;
        timeEl.textContent = relTime(opts.sentAt || 0);
        body.appendChild(timeEl);

        if (isAction) {
            var row = document.createElement('div'); row.className = 'field-row';
            row.style.cssText = 'justify-content:flex-end;gap:4px;margin-top:5px;';
            var rejectBtn = document.createElement('button'); rejectBtn.className = 'button'; rejectBtn.textContent = 'Rechazar';
            rejectBtn.addEventListener('click', function() { if (typeof opts.onReject === 'function') opts.onReject(); removeSlot(slot); });
            var acceptBtn = document.createElement('button'); acceptBtn.className = 'button'; acceptBtn.textContent = 'Aceptar';
            acceptBtn.addEventListener('click', function() { if (typeof opts.onAccept === 'function') opts.onAccept(); removeSlot(slot); });
            row.appendChild(rejectBtn); row.appendChild(acceptBtn); body.appendChild(row);
        }

        card.appendChild(body);
        slot.appendChild(card);
        /* Insertamos al principio de la lista de slots (la más nueva
           arriba). El <img> del Haro vive aparte como hijo del
           contenedor (position:absolute, no afecta al flex layout). */
        var firstSlot = container.querySelector('.notif-slot');
        if (firstSlot) {
            container.insertBefore(slot, firstSlot);
        } else {
            container.insertBefore(slot, container.firstChild);
        }
        shownIds[opts.id] = slot;

        /* Si esta tarjeta entra visible YA (no espera a la revelación
           del Haro), suena el audio en su aparición. Las pending suenan
           todas juntas en el setTimeout REVEAL_MS de createHaroEl. */
        if (!startsPending) playHaroSound();

        /* Auto-dismiss: si el slot arranca pending, dejamos la función
           guardada para dispararla al revelarse. Si no, arranca ya. */
        var startDismiss = null;
        if (!isAction) {
            var delay = (typeof opts.autoDismissAfter === 'number') ? opts.autoDismissAfter : 5000;
            startDismiss = function() {
                setTimeout(function() {
                    if (slot.parentNode) {
                        if (typeof opts.onAutoDismiss === 'function') opts.onAutoDismiss();
                        removeSlot(slot);
                    }
                }, delay);
            };
            if (startsPending) {
                slot._startDismiss = startDismiss;
            } else {
                startDismiss();
            }
        }

        /* Los setTimeout de revelación y freeze viven en createHaroEl
           — están ligados al Haro, no a un slot concreto. */
    }

    setInterval(updateTimes, 30000);

    /* Cambio de haro en runtime (lo dispara apps/temas.php al activar
       uno en personalización). Actualiza window.ACTIVE_HARO, reinstancia
       el <Audio> con la URL nueva y precarga el PNG del nuevo. */
    window.applyHaroSlug = function(slug) {
        slug = String(slug || 'green').toLowerCase().replace(/[^a-z0-9_-]/g, '');
        if (!slug) slug = 'green';
        var specificAudio = 'assets/sound/' + slug + 'Haro.mp3';
        var fallbackAudio = 'assets/sound/haro.mp3';
        /* Asignamos primero todo lo "no auditivo" — el audio se fija
           sincronamente al fallback y se intenta upgradear de forma
           async si existe la versión específica. Si lo asignáramos
           directamente al específico, cualquier `playHaroSound` que
           ocurriera ANTES de la confirmación del 404 fallaría. */
        window.ACTIVE_HARO = {
            slug:  slug,
            gif:   'assets/vids/' + slug + 'Haro.gif',
            last:  'assets/vids/' + slug + 'Haro-last.png',
            audio: fallbackAudio
        };
        try { haroAudio = new Audio(fallbackAudio); haroAudio.preload = 'auto'; } catch (_) {}
        /* HEAD probe: si {slug}Haro.mp3 SÍ existe en el servidor, hacemos
           upgrade silencioso al audio específico. Si no existe (404 o
           bloqueo CORS), nos quedamos con `haro.mp3` ya cargado. */
        try {
            fetch(specificAudio, { method: 'HEAD' }).then(function (r) {
                if (!r.ok) return;
                if (window.ACTIVE_HARO) window.ACTIVE_HARO.audio = specificAudio;
                try { haroAudio = new Audio(specificAudio); haroAudio.preload = 'auto'; } catch (_) {}
            }).catch(function () { /* sin red / CORS → fallback */ });
        } catch (_) {}
        try { (new Image()).src = haroLastUrl(); } catch (_) {}
        /* Si hay un Haro vivo en pantalla, swap inmediato a su nuevo gif. */
        if (activeHaro) {
            try { activeHaro.src = haroGifUrl() + '?t=' + Date.now(); } catch (_) {}
        }
    };

    return {
        show:        createCard,
        dismiss:     function(id) { var s = shownIds[id]; if (s) removeSlot(s); else dismissedIds[id] = true; },
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

/* =========================
   GALERÍA
========================= */
(function() {
    var galIframe = document.getElementById('galeria-iframe');
    var galLoaded = false;

    document.getElementById('galeria-icon').addEventListener('dblclick', function() {
        if (!galLoaded) { galIframe.src = 'apps/galeria.php'; galLoaded = true; }
        if (taskbarManager.isRegistered('galeria-window')) {
            taskbarManager.restore('galeria-window');
        } else {
            taskbarManager.register('galeria-window', 'Galería', '<img src="assets/img/appIcons/galeriaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', 'flex');
        }
    });

    document.getElementById('galeria-close').addEventListener('click', function() {
        taskbarManager.unregister('galeria-window');
        galIframe.src = '';
        galLoaded = false;
    });
})();

/* =========================
   DIBUJO (Excalidraw colaborativo)
========================= */
(function() {
    var dibujoIframe = document.getElementById('dibujo-iframe');
    var dibujoLoaded = false;

    document.getElementById('dibujo-icon').addEventListener('dblclick', function() {
        if (!dibujoLoaded) {
            dibujoIframe.src = 'https://excalidraw.com/#room=scrapbook-melon,clave-secreta-fija';
            dibujoLoaded = true;
        }
        if (taskbarManager.isRegistered('dibujo-window')) {
            taskbarManager.restore('dibujo-window');
        } else {
            taskbarManager.register('dibujo-window', 'Dibujo', '✏️', 'flex');
        }
    });

    document.getElementById('dibujo-close').addEventListener('click', function() {
        taskbarManager.unregister('dibujo-window');
    });
})();

/* =========================
   TIENDA
   Iframe con la app de tienda (apps/tienda.php). Internamente tiene una
   pestaña "Donaciones" que carga Ko-fi cuando se pulsa.
========================= */
(function() {
    var tiendaIframe = document.getElementById('tienda-iframe');
    var tiendaLoaded = false;

    document.getElementById('tienda-icon').addEventListener('dblclick', function() {
        if (!tiendaLoaded) {
            tiendaIframe.src = 'apps/tienda.php';
            tiendaLoaded = true;
        } else {
            /* Iframe ya estaba cargado: pedimos al iframe que refresque su
               estado (balance, items, compras) para que el wallet de la
               sidebar no se quede congelado tras minutos cerrada. */
            try { tiendaIframe.contentWindow.postMessage({ type: 'tienda-refresh' }, '*'); } catch (e) {}
        }
        if (taskbarManager.isRegistered('tienda-window')) {
            taskbarManager.restore('tienda-window');
        } else {
            taskbarManager.register('tienda-window', 'Tienda', '🛒', 'flex');
        }
    });

    document.getElementById('tienda-close').addEventListener('click', function() {
        taskbarManager.unregister('tienda-window');
    });
})();

/* =========================
   KO-FI WINDOW (la abre la app de Tienda vía postMessage)
   Sin icono propio: solo se muestra cuando llega un mensaje
   {type:'open-kofi'} desde el iframe de la Tienda.
========================= */
(function() {
    var kofiIframe = document.getElementById('kofi-iframe');
    var kofiCurrent = null;     /* URL actualmente cargada en el iframe */
    var titleText = document.querySelector('#kofi-titlebar .title-bar-text');
    var DEFAULT_KOFI_URL = 'https://ko-fi.com/melonhub/?hidefeed=true&widget=true&embed=true&preview=true';

    function openKofi(opts) {
        opts = opts || {};
        var url = opts.url || DEFAULT_KOFI_URL;
        var title = opts.title || 'Donar (Ko-fi)';
        /* Solo recarga el iframe si la URL es DISTINTA — abrir-cerrar-abrir
           la misma URL no resetea el progreso del usuario en el checkout. */
        if (url !== kofiCurrent) {
            kofiIframe.src = url;
            kofiCurrent = url;
        }
        if (titleText) titleText.textContent = '☕ ' + title;
        if (taskbarManager.isRegistered('kofi-window')) {
            taskbarManager.restore('kofi-window');
        } else {
            taskbarManager.register('kofi-window', title, '☕', 'flex');
        }
    }

    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'open-kofi') openKofi(e.data);
    });

    document.getElementById('kofi-close').addEventListener('click', function() {
        taskbarManager.unregister('kofi-window');
    });
})();

/* ──── Player right-click context menu ──── */
(function() {
    var playerMain = document.getElementById('player-main');
    if (!playerMain) return;

    var ctxMenu = document.createElement('div');
    ctxMenu.className = 'window';
    ctxMenu.setAttribute('data-no-auto-z', '');
    ctxMenu.style.cssText = 'display:none;position:fixed;z-index:9999;padding:2px 0;min-width:160px;';
    document.body.appendChild(ctxMenu);

    var picker = document.createElement('div');
    picker.className = 'window';
    picker.setAttribute('data-no-auto-z', '');
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
                var targets = playlists.filter(function(pl) { return pl.id !== currentPlaylistId; });
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
                            var already = (pl.tracks || []).some(function(t) { return t.videoId && t.videoId === track.videoId; });
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
            showPicker(parseInt(ctxMenu.style.left) + ctxMenu.offsetWidth, parseInt(ctxMenu.style.top), track);
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

    /* Helper: coordenadas (x,y) tanto para eventos de ratón como táctiles.
       Para `touchend` los datos vienen en `changedTouches`, no en `touches`. */
    function ptXY(e) {
        if (e.touches && e.touches.length)               return { x: e.touches[0].clientX,        y: e.touches[0].clientY        };
        if (e.changedTouches && e.changedTouches.length) return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
        return { x: e.clientX, y: e.clientY };
    }

    function onPointerMove(e) {
        if (!active) return;
        var el = active.el;
        var p = ptXY(e);
        var PAD = 40;
        var VW = window.innerWidth, VH = window.innerHeight;

        if (active.mode === 'drag') {
            var newL = p.x - active.ox;
            var newT = p.y - active.oy;
            var w = el.offsetWidth, h = el.offsetHeight;
            newL = Math.max(PAD - w, Math.min(VW - PAD, newL));
            newT = Math.max(0,       Math.min(VH - PAD, newT));
            el.style.left = newL + 'px';
            el.style.top  = newT + 'px';
        } else {
            var dx = p.x - active.sx, dy = p.y - active.sy;
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
        /* Evita scroll/zoom del navegador mientras se arrastra/resize en táctil. */
        if (e.cancelable) e.preventDefault();
    }

    function onPointerUp() {
        if (active) { blocker.style.display = 'none'; active = null; }
    }

    document.addEventListener('mousemove', onPointerMove);
    document.addEventListener('mouseup',   onPointerUp);
    /* `passive:false` en touchmove → necesario para poder llamar a
       preventDefault() y bloquear el scroll mientras se arrastra. */
    document.addEventListener('touchmove', onPointerMove, { passive: false });
    document.addEventListener('touchend',  onPointerUp);
    document.addEventListener('touchcancel', onPointerUp);

    function setup(id, dragOnly) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.add('win-draggable');
        if (!dragOnly) el.classList.add('win-managed');

        var titleBar = el.querySelector('.title-bar');
        if (titleBar) {
            function startDrag(e) {
                /* En mouse, ignora botones secundarios; en táctil no aplica. */
                if (e.type === 'mousedown' && e.button !== 0) return;
                if (e.target.closest('.title-bar-controls')) return;
                e.preventDefault();
                fixPos(el);
                var r = el.getBoundingClientRect();
                var p = ptXY(e);
                blocker.style.display = 'block';
                active = { el: el, mode: 'drag', ox: p.x - r.left, oy: p.y - r.top };
            }
            titleBar.addEventListener('mousedown', startDrag);
            titleBar.addEventListener('touchstart', startDrag, { passive: false });
        }

        if (dragOnly) return;

        ['n','s','e','w','ne','nw','se','sw'].forEach(function(d) {
            var h = document.createElement('div');
            h.className = 'win-handle win-handle-' + d;
            function startResize(e) {
                if (e.type === 'mousedown' && e.button !== 0) return;
                e.preventDefault();
                e.stopPropagation();
                fixPos(el);
                var r = el.getBoundingClientRect();
                var p = ptXY(e);
                blocker.style.display = 'block';
                active = { el: el, mode: 'resize', dir: d,
                           sx: p.x, sy: p.y,
                           sw: r.width,  sh: r.height,
                           sl: r.left,   st: r.top };
            }
            h.addEventListener('mousedown', startResize);
            h.addEventListener('touchstart', startResize, { passive: false });
            el.appendChild(h);
        });
    }

    window.WindowManager = { setup: setup };

    /* Helper global: centra un elemento `position:fixed` en el viewport.
       Tiene que llamarse DESPUÉS de hacer display:flex (o lo que sea
       distinto de none) para que offsetWidth/Height ya esté calculado.
       Limita al borde superior/izquierdo a 8px mínimo para que en
       pantallas pequeñas no quede pegado fuera de vista. */
    window.centerModal = function(el){
        if (!el) return;
        var w = el.offsetWidth  || 320;
        var h = el.offsetHeight || 200;
        el.style.left = Math.max(8, (window.innerWidth  - w) / 2) + 'px';
        el.style.top  = Math.max(8, (window.innerHeight - h) / 2) + 'px';
        el.style.right  = 'auto';
        el.style.bottom = 'auto';
    };

    /* ★ dibujo-window, tienda-window, kofi-window añadidos aquí */
    [
        'calendar-window','archive-window','temas-window','dnd-window',
        'galeria-window','companion-window','dibujo-window',
        'tienda-window','kofi-window','mascota-window',
        'playlist-editor','create-playlist-dialog','profile-window',
        'add-track-dialog','import-playlist-dialog',
        'collab-dialog','spotify-import-dialog','tidal-import-dialog','confirm-dialog',
        'profile-add-dialog','profile-review-prompt','profile-review-window',
        'profile-review-view','profile-invite-dialog','profile-info-edit-dialog',
        'music-add-dialog','profile-notifs-window','profile-melon-details-window',
        'profile-chat-window'
    ].forEach(function(id) { setup(id, false); });
    setup('music-player', true);
    /* Modales pequeños — arrastrables por su title-bar pero sin resize. */
    setup('change-password-modal', true);
    setup('folder-create-modal',   true);
    setup('folder-delete-modal',   true);
})();

/* ──── Window minimize / maximize ──── */
(function() {
    /* ★ dibujo-window, tienda-window, kofi-window añadidos aquí */
    var ids = [
        'calendar-window', 'archive-window', 'temas-window', 'dnd-window',
        'galeria-window', 'create-playlist-dialog', 'profile-window',
        'companion-window', 'dibujo-window',
        'tienda-window', 'kofi-window', 'mascota-window'
    ];
    ids.forEach(function(id) {
        var win = document.getElementById(id);
        if (!win) return;
        var minBtn = win.querySelector('[aria-label="Minimize"]');
        var maxBtn = win.querySelector('[aria-label="Maximize"]');

        if (minBtn) {
            minBtn.addEventListener('click', function() { taskbarManager.minimize(id); });
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
========================================================= */
(function(){
    var HOLD_MS = 350;
    var MOVE_TOLERANCE = 6;
    var GRID_W = 96;
    var GRID_H = 96;

    window.DesktopState = window.DesktopState || { icons: {}, folders: [], player: null, loaded: false, _readyCbs: [] };
    if (!window.DesktopState.whenReady) {
        window.DesktopState.whenReady = function(cb){
            if (this.loaded) cb();
            else this._readyCbs.push(cb);
        };
    }

    function snap(v, step){ return Math.round(v / step) * step; }
    function snapPos(x, y){ return { left: snap(x, GRID_W), top: snap(y, GRID_H) }; }

    /* ¿Está la celda (left, top) — coordenadas YA snapeadas — ocupada
       por algún OTRO icono del escritorio? `excludeId` evita que un
       icono se "auto-bloquee" su propia posición durante el drag.
       Comparamos las posiciones de cada icono snapeadas a la cuadrícula
       por si alguno está un píxel descolocado por animaciones previas. */
    function isCellOccupied(left, top, excludeId){
        var icons = document.querySelectorAll('.desktop-icon');
        for (var i = 0; i < icons.length; i++){
            var ic = icons[i];
            if (ic.id === excludeId) continue;
            /* Solo iconos que tienen position absoluta/fija y left/top
               numéricos. Ignora los que vivan en un folder window. */
            var l = parseFloat(ic.style.left);
            var t = parseFloat(ic.style.top);
            if (isNaN(l) || isNaN(t)) continue;
            if (snap(l, GRID_W) === left && snap(t, GRID_H) === top) return true;
        }
        return false;
    }

    /* Busca la celda LIBRE más cercana a (targetLeft, targetTop) en
       distancia Manhattan/Chebyshev sobre la cuadrícula. La búsqueda
       avanza en anillos concéntricos: ring=1 cubre las 8 celdas
       contiguas, ring=2 las 16 siguientes, etc. Dentro de cada anillo
       elegimos la celda con menor distancia EUCLÍDEA al objetivo, así
       que el resultado se "siente" como la celda más cercana real.
       Solo consideramos celdas DENTRO del escritorio. */
    function findNearestEmptyCell(targetLeft, targetTop, excludeId, deskW, deskH){
        if (!isCellOccupied(targetLeft, targetTop, excludeId)) {
            return { left: targetLeft, top: targetTop };
        }
        var maxX = Math.max(0, deskW - GRID_W);
        var maxY = Math.max(0, deskH - GRID_H);
        var maxRing = Math.max(
            Math.ceil(deskW / GRID_W),
            Math.ceil(deskH / GRID_H)
        );
        for (var ring = 1; ring <= maxRing; ring++){
            var best = null, bestDist = Infinity;
            for (var dy = -ring; dy <= ring; dy++){
                for (var dx = -ring; dx <= ring; dx++){
                    /* Solo el borde del anillo — los interiores ya
                       se probaron en iteraciones previas. */
                    if (Math.abs(dx) !== ring && Math.abs(dy) !== ring) continue;
                    var cl = targetLeft + dx * GRID_W;
                    var ct = targetTop  + dy * GRID_H;
                    if (cl < 0 || ct < 0)        continue;
                    if (cl > maxX || ct > maxY)  continue;
                    if (isCellOccupied(cl, ct, excludeId)) continue;
                    var d = dx * dx + dy * dy;
                    if (d < bestDist){ bestDist = d; best = { left: cl, top: ct }; }
                }
            }
            if (best) return best;
        }
        /* Escritorio totalmente ocupado — fallback al destino original
           (se solapará pero al menos no perdemos el icono). */
        return { left: targetLeft, top: targetTop };
    }

    function loadPositions(){ return window.DesktopState.icons || {}; }
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

        var natural = {};
        icons.forEach(function(icon){
            var r = icon.getBoundingClientRect();
            var dr = desk.getBoundingClientRect();
            natural[icon.id] = { left: r.left - dr.left, top: r.top - dr.top };
        });

        icons.forEach(function(icon){
            var raw = saved[icon.id] || natural[icon.id];
            var pos = snapPos(raw.left, raw.top);
            icon.style.position = 'absolute';
            icon.style.margin   = '0';
            icon.style.left = pos.left + 'px';
            icon.style.top  = pos.top  + 'px';
            attachDrag(icon, desk);
        });
        if(window.DesktopFolders) DesktopFolders.init(desk);
    }

    window.DesktopIcons = {
        attachDrag: function(icon){ var desk = document.getElementById('desktop'); attachDrag(icon, desk); },
        snapPos: snapPos,
        isCellOccupied: isCellOccupied,
        findNearestEmptyCell: findNearestEmptyCell,
        savePosition: savePosition,
        loadPositions: loadPositions,
        GRID_W: GRID_W,
        GRID_H: GRID_H
    };

    /* ─── Menú contextual táctil para iconos ─────────────────────────────
       En tablet, mantener pulsado un icono ya no entra directamente en
       modo arrastrar — abre este menú con la opción "📦 Mover". Tras
       pulsar Mover, el icono queda en estado .move-pending (anim wobble)
       y la siguiente pulsación sobre él arranca el drag sin esperar los
       350 ms del long-press. Cualquier toque fuera cancela el estado. */
    var _ctxMenuEl = null;
    var _ctxIcon   = null;

    function ensureIconCtxMenu(){
        if (_ctxMenuEl) return _ctxMenuEl;
        _ctxMenuEl = document.getElementById('desktop-icon-ctx-menu');
        if (!_ctxMenuEl) return null;
        _ctxMenuEl.addEventListener('click', function(e){
            var li = e.target.closest('li[data-action]');
            if (!li) return;
            if (li.dataset.action === 'move' && _ctxIcon) {
                _ctxIcon.classList.add('move-pending');
            }
            hideIconCtxMenu();
        });
        /* Listener global (captura) para cerrar el menú y/o cancelar el
           modo mover cuando el usuario toca fuera. Vive aquí para no
           duplicarlo por cada icono. */
        document.addEventListener('touchstart', function(e){
            var menuShown        = _ctxMenuEl.classList.contains('show');
            var tappedMenu       = !!e.target.closest('#desktop-icon-ctx-menu');
            var tappedPendingIco = !!e.target.closest('.desktop-icon.move-pending');
            if (menuShown && !tappedMenu) hideIconCtxMenu();
            if (!tappedPendingIco && !tappedMenu) {
                document.querySelectorAll('.desktop-icon.move-pending').forEach(function(i){
                    i.classList.remove('move-pending');
                });
            }
        }, true);
        return _ctxMenuEl;
    }

    function showIconCtxMenu(icon){
        var menu = ensureIconCtxMenu();
        if (!menu) return;
        _ctxIcon = icon;
        /* Lo posicionamos JUNTO al icono, no bajo el dedo del usuario.
           Si lo metiéramos donde está el dedo, al levantarlo el touchend
           dispararía click sintético sobre la opción "Mover" sin haberla
           tocado intencionalmente. */
        menu.classList.add('show');
        menu.style.left = '0px';
        menu.style.top  = '0px';
        var w = menu.offsetWidth, h = menu.offsetHeight;
        var VW = window.innerWidth, VH = window.innerHeight;
        var r = icon.getBoundingClientRect();
        /* Por defecto: a la derecha del icono, alineado por arriba. */
        var px = r.right + 8;
        var py = r.top;
        if (px + w > VW - 4) px = r.left - w - 8;   /* no cabe → izquierda */
        if (px < 4)           px = Math.max(4, Math.min(r.left, VW - w - 4));
        if (py + h > VH - 4)  py = VH - h - 4;
        if (py < 4)           py = 4;
        menu.style.left = px + 'px';
        menu.style.top  = py + 'px';
    }

    function hideIconCtxMenu(){
        if (_ctxMenuEl) _ctxMenuEl.classList.remove('show');
        _ctxIcon = null;
    }

    function attachDrag(icon, desk){
        var holdTimer = null;
        var dragging  = false;
        var armed     = false;
        var startX = 0, startY = 0;
        var originX = 0, originY = 0;
        var moved   = false;
        var wasInBody = false;

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
            var isTouch = (e.type === 'touchstart');
            var p = pointer(e);
            startX = p.clientX; startY = p.clientY;
            originX = parseFloat(icon.style.left) || 0;
            originY = parseFloat(icon.style.top)  || 0;
            moved = false;

            /* Táctil con el icono en "modo mover" (ya se confirmó desde el
               menú): saltamos el long-press y armamos el drag al instante. */
            if (isTouch && icon.classList.contains('move-pending')) {
                icon.classList.remove('move-pending');
                armed = true;
                icon.classList.add('dragging');
            } else {
                icon.classList.add('long-pressing');
                holdTimer = setTimeout(function(){
                    icon.classList.remove('long-pressing');
                    if (isTouch) {
                        /* En táctil → mostramos menú "Mover" en vez de
                           empezar a arrastrar de golpe. */
                        showIconCtxMenu(icon);
                    } else {
                        armed = true;
                        icon.classList.add('dragging');
                    }
                }, HOLD_MS);
            }

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
                if(Math.abs(dx) > MOVE_TOLERANCE || Math.abs(dy) > MOVE_TOLERANCE){
                    clearTimeout(holdTimer);
                    icon.classList.remove('long-pressing');
                    cleanupListeners();
                }
                return;
            }
            if(e.cancelable) e.preventDefault();
            if(!dragging && !wasInBody){
                var br = icon.getBoundingClientRect();
                document.body.appendChild(icon);
                icon.style.position = 'fixed';
                icon.style.left = br.left + 'px';
                icon.style.top  = br.top  + 'px';
                wasInBody = true;
            }
            dragging = true; moved = true;
            var w = icon.offsetWidth, h = icon.offsetHeight;
            var dw = desk.clientWidth, dh = desk.clientHeight;
            var nx = Math.max(0, Math.min(dw - w, originX + dx));
            var ny = Math.max(0, Math.min(dh - h, originY + dy));
            icon.style.left = nx + 'px';
            icon.style.top  = ny + 'px';
            if(window.DesktopFolders){
                var rect = icon.getBoundingClientRect();
                DesktopFolders.updateHover(icon.id, rect.left + rect.width/2, rect.top + rect.height/2);
            }
        }

        function onUp(){
            clearTimeout(holdTimer);
            icon.classList.remove('long-pressing');
            icon.classList.remove('dragging');
            if(dragging){
                /* Drag completado → salimos del modo "move-pending" si lo
                   teníamos puesto (entró por flujo táctil del menú). */
                icon.classList.remove('move-pending');
                var droppedInFolder = false;
                if(window.DesktopFolders){
                    var rect = icon.getBoundingClientRect();
                    droppedInFolder = DesktopFolders.tryDrop(icon.id, rect.left + rect.width/2, rect.top + rect.height/2);
                    DesktopFolders.clearHover();
                }
                if(wasInBody){
                    desk.appendChild(icon);
                    icon.style.position = 'absolute';
                    wasInBody = false;
                }
                if(!droppedInFolder){
                    var finalPos = snapPos(parseFloat(icon.style.left)||0, parseFloat(icon.style.top)||0);
                    /* Si la celda destino ya está ocupada, buscamos la
                       celda LIBRE más cercana (anillos concéntricos
                       sobre la cuadrícula). Así el icono no rebota a
                       origen ni se solapa — siempre acaba en la celda
                       más próxima posible al punto donde lo soltaste. */
                    finalPos = findNearestEmptyCell(
                        finalPos.left, finalPos.top, icon.id,
                        desk.clientWidth, desk.clientHeight
                    );
                    icon.classList.add('snapping');
                    icon.style.left = finalPos.left + 'px';
                    icon.style.top  = finalPos.top  + 'px';
                    setTimeout(function(){ icon.classList.remove('snapping'); }, 200);
                    savePosition(icon.id, finalPos);
                }
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
        fetchState(function(){ requestAnimationFrame(init); });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();

/* =========================================================
   DOUBLE-TAP → DBLCLICK (táctil)
   ─────────────────────────────────────────────────────────
   En táctil, los navegadores no siempre sintetizan dblclick
   con dos pulsaciones rápidas. Lo emulamos manualmente para
   .desktop-icon (incluye iconos del escritorio y carpetas):
   dos taps sobre el mismo elemento en < 400 ms → dispatch
   sintético de dblclick. attachDrag() ya bloquea esto si
   hubo arrastre real (la clase .dragging se mantiene).
========================================================= */
(function(){
    var GAP = 400;
    var last = { id: null, t: 0 };
    document.addEventListener('touchend', function(e){
        if (!e.changedTouches || e.changedTouches.length !== 1) return;
        var icon = e.target.closest('.desktop-icon');
        if (!icon || !icon.id) return;
        if (icon.classList.contains('dragging'))     return;   /* drag activo */
        if (icon.classList.contains('move-pending')) return;   /* esperando mover */
        /* Si el menú "Mover" está abierto, este touchend cierra el
           long-press, no es un primer tap del doble. */
        var menu = document.getElementById('desktop-icon-ctx-menu');
        if (menu && menu.classList.contains('show')) return;
        var now = (new Date()).getTime();
        if (last.id === icon.id && now - last.t < GAP) {
            last = { id: null, t: 0 };
            icon.dispatchEvent(new MouseEvent('dblclick', { bubbles: true, cancelable: true }));
        } else {
            last = { id: icon.id, t: now };
        }
    });
})();

/* =========================================================
   CARPETAS DEL ESCRITORIO
========================================================= */
(function(){
    var folders = {};
    var openFolderWindows = {};
    var hoverFolderEl = null;

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
    var _saveTimers = {};
    function saveFolder(id){
        var f = folders[id];
        if(!f){
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
                body: JSON.stringify({ id: f.id, name: f.name, pos: f.pos, children: f.children || [] })
            }).catch(function(){});
        }, 150);
    }
    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function init(desk){
        load();
        Object.keys(folders).forEach(function(id){ renderFolderIcon(folders[id], desk); });
        var hiddenChildren = new Set();
        Object.values(folders).forEach(function(f){ (f.children||[]).forEach(function(cid){ hiddenChildren.add(cid); }); });
        hiddenChildren.forEach(function(cid){ var el = document.getElementById(cid); if(el) el.style.display = 'none'; });
    }

    function renderFolderIcon(folder, desk){
        if(!desk) desk = document.getElementById('desktop');
        var div = document.createElement('div');
        div.className = 'desktop-icon';
        div.id = folder.id;
        div.dataset.folder = '1';
        div.innerHTML = '<div class="desktop-icon-img"><img src="assets/img/appIcons/folderIcon.png" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;" alt=""></div><span>' + esc(folder.name) + '</span>';
        div.style.position = 'absolute';
        div.style.margin = '0';
        var savedIconPos = window.DesktopIcons ? window.DesktopIcons.loadPositions()[folder.id] : null;
        var basePos = savedIconPos || folder.pos;
        var pos = window.DesktopIcons ? window.DesktopIcons.snapPos(basePos.left, basePos.top) : basePos;
        div.style.left = pos.left + 'px';
        div.style.top  = pos.top  + 'px';
        div.addEventListener('dblclick', function(){ openFolderWindow(folder.id); });
        div.addEventListener('contextmenu', function(e){ e.preventDefault(); e.stopPropagation(); deleteFolder(folder.id); });
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
            /* Si la celda de creación ya está ocupada (p.ej. clicaste muy
               cerca de un icono existente), reubicamos la carpeta al
               hueco libre más cercano antes de persistir. */
            if (window.DesktopIcons && window.DesktopIcons.findNearestEmptyCell) {
                var desk = document.getElementById('desktop');
                pos = window.DesktopIcons.findNearestEmptyCell(
                    pos.left, pos.top, id,
                    desk.clientWidth, desk.clientHeight
                );
            }
            folders[id] = { id: id, name: name, pos: pos, children: [] };
            saveFolder(id);
            renderFolderIcon(folders[id]);
            if(window.DesktopIcons) window.DesktopIcons.savePosition(id, pos);
        });
    }

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
            saveFolder(id); saveFolder(parentId);
            renderFolderIcon(folders[id]);
            var iconEl = document.getElementById(id);
            if(iconEl) iconEl.style.display = 'none';
            if(openFolderWindows[parentId]) renderFolderContent(parentId);
        });
    }

    function deleteFolder(id){
        var f = folders[id]; if(!f) return;
        showDeleteModal(f.name, function(){
            (f.children||[]).forEach(function(cid){ var el = document.getElementById(cid); if(el) el.style.display = ''; });
            var parentsToRefresh = [];
            Object.keys(folders).forEach(function(fid){
                if(fid === id) return;
                var pf = folders[fid];
                var idx = (pf.children||[]).indexOf(id);
                if(idx !== -1){ pf.children.splice(idx, 1); parentsToRefresh.push(fid); }
            });
            delete folders[id];
            saveFolder(id);
            parentsToRefresh.forEach(saveFolder);
            var el = document.getElementById(id); if(el) el.remove();
            if(openFolderWindows[id]) closeFolderWindowById(id);
            parentsToRefresh.forEach(function(pid){ if(openFolderWindows[pid]) renderFolderContent(pid); });
        });
    }

    function showCreateModal(onAccept){
        var modal = document.getElementById('folder-create-modal');
        var input = document.getElementById('folder-create-input');
        input.value = 'Nueva carpeta';
        modal.style.display = 'flex';
        if(window.centerModal) window.centerModal(modal);
        if(window.windowZ) windowZ.bringToFront('folder-create-modal');
        setTimeout(function(){ input.focus(); input.select(); }, 30);
        function ok(){ cleanup(); onAccept(input.value); }
        function cancel(){ cleanup(); }
        function keyHandler(ev){ if(ev.key === 'Enter'){ ev.preventDefault(); ok(); } else if(ev.key === 'Escape'){ ev.preventDefault(); cancel(); } }
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
            '¿Borrar la carpeta <strong>' + esc(folderName) + '</strong>?<br><small>Los iconos volverán al escritorio.</small>';
        modal.style.display = 'flex';
        if(window.centerModal) window.centerModal(modal);
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
        if(fEl && folders[iconId] && isDescendant(fEl.id, iconId)) fEl = null;
        if(hoverFolderEl && hoverFolderEl !== fEl) hoverFolderEl.classList.remove('folder-receive');
        if(fEl) fEl.classList.add('folder-receive');
        hoverFolderEl = fEl;
        Object.keys(openFolderWindows).forEach(function(fid){ openFolderWindows[fid].el.classList.remove('folder-window-receive'); });
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
        Object.keys(openFolderWindows).forEach(function(fid){ openFolderWindows[fid].el.classList.remove('folder-window-receive'); });
    }

    function isDescendant(descId, ancestorId){
        if(descId === ancestorId) return true;
        var f = folders[ancestorId]; if(!f) return false;
        var children = f.children || [];
        for(var i=0; i<children.length; i++){ if(isDescendant(descId, children[i])) return true; }
        return false;
    }

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
        var fEl = findFolderAt(iconId, x, y);
        if(fEl){
            var folderId = fEl.id;
            if(folderId === iconId) return false;
            if(folders[iconId] && isDescendant(folderId, iconId)) return false;
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
            var startX = e.clientX, startY = e.clientY;
            var ghost = null;
            function makeGhost(){
                ghost = item.cloneNode(true);
                ghost.style.position = 'fixed'; ghost.style.pointerEvents = 'none';
                ghost.style.opacity = '0.78'; ghost.style.zIndex = '99999';
                ghost.style.transform = 'scale(1.05)'; ghost.style.boxShadow = '0 4px 14px rgba(0,0,0,0.4)';
                document.body.appendChild(ghost); moveGhost(startX, startY);
            }
            function moveGhost(x, y){ if(!ghost) return; ghost.style.left = (x - 40) + 'px'; ghost.style.top = (y - 24) + 'px'; }
            function highlightTargets(x, y){
                Object.keys(openFolderWindows).forEach(function(fid){ openFolderWindows[fid].el.classList.remove('folder-window-receive'); });
                if(hoverFolderEl) hoverFolderEl.classList.remove('folder-receive');
                hoverFolderEl = null;
                var overFid = getOpenFolderWindowAt(x, y);
                if(overFid && overFid !== folderId){ openFolderWindows[overFid].el.classList.add('folder-window-receive'); return; }
                var fEl = findFolderAt(iconId, x, y);
                if(fEl && fEl.id !== folderId){ fEl.classList.add('folder-receive'); hoverFolderEl = fEl; }
            }
            function clearTargets(){
                Object.keys(openFolderWindows).forEach(function(fid){ openFolderWindows[fid].el.classList.remove('folder-window-receive'); });
                if(hoverFolderEl) hoverFolderEl.classList.remove('folder-receive');
                hoverFolderEl = null;
            }
            function onMove(ev){
                var dx = ev.clientX - startX, dy = ev.clientY - startY;
                if(!ghost && (Math.abs(dx) > 5 || Math.abs(dy) > 5)) makeGhost();
                if(ghost){ ev.preventDefault(); moveGhost(ev.clientX, ev.clientY); highlightTargets(ev.clientX, ev.clientY); }
            }
            function onUp(ev){
                document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp);
                if(!ghost) return;
                ghost.remove(); clearTargets();
                var overFid = getOpenFolderWindowAt(ev.clientX, ev.clientY);
                if(overFid && overFid !== folderId){ moveIconBetweenFolders(folderId, overFid, iconId); return; }
                if(overFid === folderId) return;
                var fEl = findFolderAt(iconId, ev.clientX, ev.clientY);
                if(fEl && fEl.id !== folderId && folders[fEl.id]){ moveIconBetweenFolders(folderId, fEl.id, iconId); return; }
                removeFromFolderAt(folderId, iconId, ev.clientX, ev.clientY);
            }
            document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp);
        });
    }

    function moveIconBetweenFolders(fromId, toId, iconId){
        var from = folders[fromId], to = folders[toId];
        if(!from || !to || fromId === toId) return;
        from.children = (from.children||[]).filter(function(c){ return c !== iconId; });
        if((to.children = to.children || []).indexOf(iconId) === -1) to.children.push(iconId);
        saveFolder(fromId); saveFolder(toId);
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
            var pos = window.DesktopIcons.snapPos(screenX - dr.left, screenY - dr.top);
            var w = el.offsetWidth || 76, h = el.offsetHeight || 76;
            pos.left = Math.max(0, Math.min(desk.clientWidth - w, pos.left));
            pos.top  = Math.max(0, Math.min(desk.clientHeight - h, pos.top));
            /* Si la celda destino ya tiene otro icono, buscamos la libre
               más cercana — mismo comportamiento que el drop manual del
               drag-en-escritorio (sin esto, sacar de carpeta apilaba
               iconos en la misma cuadrícula). */
            if (window.DesktopIcons.findNearestEmptyCell) {
                pos = window.DesktopIcons.findNearestEmptyCell(
                    pos.left, pos.top, iconId,
                    desk.clientWidth, desk.clientHeight
                );
            }
            el.style.left = pos.left + 'px'; el.style.top = pos.top + 'px';
            window.DesktopIcons.savePosition(iconId, pos);
        } else if(el) { el.style.display = ''; }
        if(openFolderWindows[folderId]) renderFolderContent(folderId);
    }

    function openFolderWindow(id){
        var f = folders[id]; if(!f) return;
        if(openFolderWindows[id]){
            var existing = openFolderWindows[id].el;
            if(window.taskbarManager && taskbarManager.isRegistered(openFolderWindows[id].wid)){
                taskbarManager.restore(openFolderWindows[id].wid);
            } else { existing.style.display = 'flex'; }
            if(window.windowZ) windowZ.bringToFront(existing);
            return;
        }
        var tpl = document.getElementById('folder-window-template'); if(!tpl) return;
        var w = tpl.cloneNode(true);
        var wid = 'folder-window-' + id;
        w.id = wid;
        var count = Object.keys(openFolderWindows).length;
        var baseL = window.innerWidth  * 0.18, baseT = window.innerHeight * 0.14;
        w.style.left = (baseL + count * 28) + 'px'; w.style.top = (baseT + count * 28) + 'px';
        w.style.zIndex = ''; w.style.display = 'flex';
        var titleEl = w.querySelector('.title-bar-text');
        if(titleEl) {
            /* Render PNG folderIcon + nombre escapado (textContent solo
               admite texto, así que innerHTML con escape para evitar XSS
               si f.name viniese con < > etc). */
            titleEl.innerHTML = '<img src="assets/img/appIcons/folderIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">' + esc(f.name);
        }
        var closeBtn = w.querySelector('[aria-label="Close"]');
        var minBtn   = w.querySelector('[aria-label="Minimize"]');
        var maxBtn   = w.querySelector('[aria-label="Maximize"]');
        if(closeBtn) closeBtn.onclick = function(){ closeFolderWindowById(id); };
        if(minBtn)   minBtn.onclick   = function(){ if(window.taskbarManager) taskbarManager.minimize(wid); };
        if(maxBtn)   maxBtn.onclick   = function(){
            if(w.classList.contains('win-maximized')){ w.classList.remove('win-maximized'); maxBtn.setAttribute('aria-label', 'Maximize'); }
            else { w.classList.add('win-maximized'); maxBtn.setAttribute('aria-label', 'Restore'); }
        };
        document.body.appendChild(w);
        openFolderWindows[id] = { el: w, wid: wid };
        var bodyEl = w.querySelector('.folder-content');
        if(bodyEl){
            bodyEl.addEventListener('contextmenu', function(e){
                if(e.target.closest('.folder-item')) return;
                e.preventDefault(); e.stopPropagation();
                showCtxMenu(e.clientX, e.clientY, id);
            });
        }
        if(window.WindowManager) window.WindowManager.setup(wid, false);
        if(window.taskbarManager) taskbarManager.register(wid, f.name, '📁', 'flex');
        if(window.windowZ) windowZ.bringToFront(w);
        renderFolderContent(id);
    }

    function closeFolderWindowById(id){
        var entry = openFolderWindows[id]; if(!entry) return;
        if(window.taskbarManager && taskbarManager.isRegistered(entry.wid)) taskbarManager.unregister(entry.wid);
        entry.el.remove();
        delete openFolderWindows[id];
    }

    function renderFolderContent(id){
        var f = folders[id]; if(!f) return;
        var entry = openFolderWindows[id]; if(!entry) return;
        var box = entry.el.querySelector('.folder-content'); if(!box) return;
        box.innerHTML = '';
        if(!f.children || !f.children.length){
            var e = document.createElement('div'); e.className = 'folder-empty';
            e.textContent = 'Carpeta vacía. Arrastra iconos aquí desde el escritorio.';
            box.appendChild(e); return;
        }
        f.children.forEach(function(cid){
            var orig = document.getElementById(cid); if(!orig) return;
            var imgHtml = (orig.querySelector('.desktop-icon-img') || {}).innerHTML || '';
            var label   = (orig.querySelector('span') || {}).textContent || cid;
            var item = document.createElement('div');
            item.className = 'folder-item'; item.dataset.iconId = cid;
            item.innerHTML = '<div class="fi-img">' + imgHtml + '</div><div class="fi-label">' + esc(label) + '</div>';
            item.addEventListener('dblclick', function(){ var ev = new MouseEvent('dblclick', { bubbles: true, cancelable: true }); orig.dispatchEvent(ev); });
            if(folders[cid]){ item.addEventListener('contextmenu', function(e){ e.preventDefault(); e.stopPropagation(); deleteFolder(cid); }); }
            attachFolderItemDrag(item, id, cid);
            box.appendChild(item);
        });
    }

    var lastCtxX = 0, lastCtxY = 0;
    var ctxTargetFolderId = null;
    function showCtxMenu(x, y, targetFolderId){
        var menu = document.getElementById('desktop-ctx-menu');
        lastCtxX = x; lastCtxY = y; ctxTargetFolderId = targetFolderId || null;
        menu.style.left = x + 'px'; menu.style.top = y + 'px';
        menu.classList.add('show');
    }
    function hideCtxMenu(){ document.getElementById('desktop-ctx-menu').classList.remove('show'); }

    function bindMenu(){
        var desk = document.getElementById('desktop'); if(!desk) return;
        desk.addEventListener('contextmenu', function(e){
            if(e.target.closest('.desktop-icon')) return;
            e.preventDefault(); showCtxMenu(e.clientX, e.clientY);
        });
        /* Long-press en zona vacía del escritorio ≈ click derecho.
           600 ms para no chocar con los 350 ms del drag de iconos. */
        var lpTimer = null, lpX = 0, lpY = 0;
        desk.addEventListener('touchstart', function(e){
            if (e.target.closest('.desktop-icon')) return;
            if (!e.touches || e.touches.length !== 1) return;
            lpX = e.touches[0].clientX;
            lpY = e.touches[0].clientY;
            lpTimer = setTimeout(function(){
                lpTimer = null;
                showCtxMenu(lpX, lpY);
            }, 600);
        }, { passive: true });
        desk.addEventListener('touchmove', function(e){
            if (!lpTimer || !e.touches[0]) return;
            if (Math.abs(e.touches[0].clientX - lpX) > 10 ||
                Math.abs(e.touches[0].clientY - lpY) > 10) {
                clearTimeout(lpTimer); lpTimer = null;
            }
        }, { passive: true });
        desk.addEventListener('touchend',    function(){ if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; } });
        desk.addEventListener('touchcancel', function(){ if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; } });
        document.addEventListener('click', function(){ hideCtxMenu(); });
        document.addEventListener('contextmenu', function(e){ if(!e.target.closest('#desktop')) hideCtxMenu(); });
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') hideCtxMenu(); });
        var menu = document.getElementById('desktop-ctx-menu');
        menu.addEventListener('click', function(e){
            var li = e.target.closest('li[data-action]'); if(!li) return;
            var target = ctxTargetFolderId; hideCtxMenu();
            if(li.dataset.action === 'new-folder'){
                if(target) createFolderInside(target);
                else       createFolder(lastCtxX, lastCtxY);
            }
        });
        var fc = document.getElementById('folder-close');
        if(fc) fc.addEventListener('click', closeFolderWindow);
    }

    if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', bindMenu); }
    else { bindMenu(); }

    window.DesktopFolders = {
        init: init, updateHover: updateHover, clearHover: clearHover,
        tryDrop: tryDrop, openFolder: openFolderWindow, closeFolder: closeFolderWindowById
    };
})();

/* =========================
   MASCOTA — botón ⋯ y ventana
========================= */
(function () {
    var mascotaFrame  = document.getElementById('mascota-frame');
    var mascotaLoaded = false;
    var menuBtn       = document.getElementById('mascota-menu-btn');

    /* ─── DRAG del botón flotante ────────────────────────────────────
       El botón arranca arriba-derecha (CSS). Si el usuario lo arrastra,
       cambiamos left/top inline y persistimos en localStorage. Para no
       confundir drag con click: solo entramos en modo drag tras
       desplazarse > DRAG_THRESHOLD px. Si no hubo drag, el click se
       deja pasar normal y abre el menú. */
    (function setupDrag(){
        if (!menuBtn) return;
        var DRAG_THRESHOLD = 4;
        var STORAGE_KEY    = 'mascota-menu-btn-pos';
        var startX = 0, startY = 0, baseLeft = 0, baseTop = 0;
        var dragging = false, dragged = false, pointerId = null;

        /* Restaurar última posición guardada (si existe). */
        try {
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                var p = JSON.parse(saved);
                if (typeof p.left === 'number' && typeof p.top === 'number') {
                    /* Clamp al viewport por si la pantalla ahora es más pequeña. */
                    var w = window.innerWidth, h = window.innerHeight;
                    menuBtn.style.left  = Math.max(0, Math.min(w - 36, p.left)) + 'px';
                    menuBtn.style.top   = Math.max(0, Math.min(h - 36, p.top))  + 'px';
                    menuBtn.style.right = 'auto';
                }
            }
        } catch (_) {}

        function ptXY(e){
            if (e.touches && e.touches[0])         return { x: e.touches[0].clientX,        y: e.touches[0].clientY        };
            if (e.changedTouches && e.changedTouches[0]) return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
            return { x: e.clientX, y: e.clientY };
        }

        function onDown(e){
            if (e.type === 'mousedown' && e.button !== 0) return;
            var p = ptXY(e);
            startX = p.x; startY = p.y;
            var r = menuBtn.getBoundingClientRect();
            baseLeft = r.left; baseTop = r.top;
            dragging = true; dragged = false;
            if (e.type === 'pointerdown' && menuBtn.setPointerCapture) {
                try { pointerId = e.pointerId; menuBtn.setPointerCapture(pointerId); } catch(_){}
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend',  onUp);
            document.addEventListener('touchcancel', onUp);
        }
        function onMove(e){
            if (!dragging) return;
            var p = ptXY(e);
            var dx = p.x - startX, dy = p.y - startY;
            if (!dragged) {
                if (Math.abs(dx) < DRAG_THRESHOLD && Math.abs(dy) < DRAG_THRESHOLD) return;
                dragged = true;
                menuBtn.classList.add('dragging');
                /* Una vez en drag, convertimos a coordenadas left/top y
                   eliminamos right para que el botón se ancore a la
                   esquina superior izquierda. */
                menuBtn.style.right = 'auto';
                menuBtn.style.left  = baseLeft + 'px';
                menuBtn.style.top   = baseTop  + 'px';
            }
            if (e.cancelable) e.preventDefault();
            var nx = baseLeft + dx, ny = baseTop + dy;
            /* Clamp al viewport. */
            nx = Math.max(0, Math.min(window.innerWidth  - menuBtn.offsetWidth,  nx));
            ny = Math.max(0, Math.min(window.innerHeight - menuBtn.offsetHeight, ny));
            menuBtn.style.left = nx + 'px';
            menuBtn.style.top  = ny + 'px';
        }
        function onUp(e){
            if (!dragging) return;
            dragging = false;
            menuBtn.classList.remove('dragging');
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup',   onUp);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend',  onUp);
            document.removeEventListener('touchcancel', onUp);
            if (dragged) {
                /* Guardar nueva posición. */
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify({
                        left: parseFloat(menuBtn.style.left) || 0,
                        top:  parseFloat(menuBtn.style.top)  || 0,
                    }));
                } catch(_){}
                /* Si fue un drag real, "comernos" el siguiente click
                   sintético para que no abra el menú al soltar. */
                var swallow = function(ev){
                    ev.stopPropagation(); ev.preventDefault();
                    menuBtn.removeEventListener('click', swallow, true);
                };
                menuBtn.addEventListener('click', swallow, true);
            }
        }
        menuBtn.addEventListener('mousedown',  onDown);
        menuBtn.addEventListener('touchstart', onDown, { passive: false });
    })();

    /* Abre la ventana de gestión */
    function openMascotaWindow() {
        if (!mascotaLoaded) {
            mascotaFrame.src = 'apps/mascota.php';
            mascotaLoaded = true;
        }
        if (taskbarManager.isRegistered('mascota-window')) {
            taskbarManager.restore('mascota-window');
        } else {
            taskbarManager.register('mascota-window', 'Mascota', '<img src="assets/img/appIcons/mascotaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', 'flex');
        }
    }

    /** Invalida el iframe de la ventana de gestión para que la
     *  próxima apertura cargue datos frescos. Si la ventana está
     *  abierta ahora mismo, recarga al vuelo. Útil tras delete,
     *  hatch dev, break-egg — cualquier cambio que invalide el SSR. */
    function refreshMascotaWindow() {
        mascotaLoaded = false;
        var win = document.getElementById('mascota-window');
        if (win && win.style.display && win.style.display !== 'none') {
            mascotaFrame.src = 'apps/mascota.php?_=' + Date.now();
            mascotaLoaded = true;
        }
    }
    /* Exponer global para que la pueda llamar el propio iframe (tras
       acciones internas como hatch dev) y el resto del desktop. */
    window.refreshMascotaWindow = refreshMascotaWindow;

    document.getElementById('mascota-close').addEventListener('click', function () {
        taskbarManager.unregister('mascota-window');
    });

    /* ─── Picker "Alimentar" ────────────────────────────────────── */
    /* Hacer arrastrable la ventana del picker (solo drag, sin
       redimensionar — la ventana tiene tamaño fijo por su grid). */
    if (window.WindowManager) window.WindowManager.setup('alimentar-window', true);

    function openAlimentarWindow() {
        if (taskbarManager.isRegistered('alimentar-window')) {
            taskbarManager.restore('alimentar-window');
        } else {
            taskbarManager.register('alimentar-window', 'Alimentar', '🍴', 'flex');
        }
    }
    document.getElementById('alimentar-close').addEventListener('click', function () {
        taskbarManager.unregister('alimentar-window');
    });

    /* ─── Wrapped (Spotify Wrapped clone) ──────────────────────── */
    /* Drag-only setup para la ventana Wrapped (sin resize — la slide
       deck no se redimensiona). */
    if (window.WindowManager) window.WindowManager.setup('wrapped-window', true);

    var wrappedFrame   = document.getElementById('wrapped-frame');
    var wrappedDevBtn  = document.getElementById('wrapped-dev-btn');
    var wrappedCloseEl = document.getElementById('wrapped-close-window');

    /** Abre la ventana del Wrapped cargando el iframe.
     *  @param {boolean} dev - si true, carga con ?dev=1 (todas las plays). */
    function openWrappedWindow(dev) {
        wrappedFrame.src = 'apps/wrapped.php?_=' + Date.now() + (dev ? '&dev=1' : '');
        if (taskbarManager.isRegistered('wrapped-window')) {
            taskbarManager.restore('wrapped-window');
        } else {
            taskbarManager.register('wrapped-window', 'Wrapped', '🎁', 'flex');
        }
    }
    function closeWrappedWindow() {
        taskbarManager.unregister('wrapped-window');
        wrappedFrame.src = '';
    }

    wrappedCloseEl.addEventListener('click', closeWrappedWindow);
    if (wrappedDevBtn) {
        wrappedDevBtn.addEventListener('click', function () {
            openWrappedWindow(true);
        });
    }
    /* El iframe del wrapped puede pedir cerrarse vía postMessage. */
    window.addEventListener('message', function (ev) {
        if (ev && ev.data && ev.data.type === 'wrapped-close') {
            closeWrappedWindow();
        }
    });

    /* Notificación del 22 de diciembre — solo se muestra una vez por
       año (flag en localStorage). Click → abre wrapped (NO dev). */
    (function autoNotifyWrapped() {
        var now = new Date();
        var month = now.getMonth(); /* 0-11 */
        var day   = now.getDate();
        var year  = now.getFullYear();
        if (month !== 11 || day !== 22) return; /* Solo 22 de diciembre */
        var key = 'wrapped-notified-' + year;
        try { if (localStorage.getItem(key)) return; } catch (_) {}
        if (!window.notifSystem) return;
        setTimeout(function () {
            window.notifSystem.show({
                id:      'wrapped-' + year,
                type:    'info',
                title:   '🎁 Tu Wrapped ' + year + ' está aquí',
                message: 'Repasa tu año en música. Tap para abrir.',
                autoDismissAfter: 0,
                onClick: function () {
                    openWrappedWindow(false);
                    try { localStorage.setItem(key, '1'); } catch (_) {}
                },
            });
        }, 2000);
    })();

    /* Click sobre cualquier botón de alimento → POST feed con su slug. */
    document.querySelectorAll('.alimentar-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var slug = btn.dataset.slug;
            fetch('assets/mascota/api.php?action=feed', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ alimento: slug }),
            })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d || !d.ok) {
                        if (window.notifSystem) {
                            window.notifSystem.show({
                                id: 'feed-err-' + Date.now(),
                                type: 'error',
                                title: '🍴 Error',
                                message: (d && d.error) || 'No se pudo alimentar.',
                                autoDismissAfter: 3500,
                            });
                        }
                        return;
                    }
                    /* Sync stats locales del engine + animación de comer. */
                    if (window.MascotaEngine && window.MascotaEngine.getState) {
                        var st = window.MascotaEngine.getState();
                        if (st && st.mascota) {
                            st.mascota.hambre    = d.hambre;
                            st.mascota.felicidad = d.felicidad;
                        }
                        if (window.MascotaEngine.feed) window.MascotaEngine.feed();
                    }
                    /* Burbuja de reacción según gusto. */
                    var msgs = {
                        love:    [d.emoji + ' ¡Adoro ' + d.nombre + '! 😍', '¡Mi favorito! ❤'],
                        like:    ['¡Mmm, gracias! 😋',     '¡' + d.nombre + ' está rica!'],
                        neutral: ['Gracias.',              '...mh, vale.'],
                        dislike: ['Esto no me gusta 🙁',   '¿Otra vez ' + d.nombre + '?'],
                        hate:    ['¡Puaj! 🤢 odio ' + d.nombre, '¡Eso es asqueroso!'],
                    };
                    var arr = msgs[d.reaccion] || msgs.neutral;
                    var msg = arr[Math.floor(Math.random() * arr.length)];
                    if (window.MascotaEngine && window.MascotaEngine.showBubble) {
                        window.MascotaEngine.showBubble(msg, 3000);
                    }
                    /* Cerrar picker tras alimentar. */
                    taskbarManager.unregister('alimentar-window');
                    /* Refrescar ventana de gestión si está abierta. */
                    if (window.refreshMascotaWindow) window.refreshMascotaWindow();
                })
                .catch(function (e) { console.error('feed:', e); });
        });
    });

    /* Menú contextual del botón ⋯ */
    menuBtn.addEventListener('click', function (e) {
        e.stopPropagation();

        /* Si ya existe, cerrarlo */
        var existing = document.getElementById('mascota-ctx-inline');
        if (existing) { existing.remove(); return; }

        var menu = document.createElement('ul');
        menu.id        = 'mascota-ctx-inline';
        /* OJO: NO añadimos `desk-ctx` para que use SOLO los estilos de
           #mascota-ctx-inline (Win98 + tokens del tema). La clase
           `show` tampoco hace falta — el display es por id. */

        /* Las opciones del menú cambian según el estado actual:
           - HUEVO  → "Ver mascota" + "Dar calor" + eliminar
           - MASCOTA → "Ver mascota" + Alimentar/Jugar/Estado + eliminar
           Esto evita acciones inválidas (alimentar un huevo etc). */
        var isEgg = window.MascotaEngine && window.MascotaEngine.isEgg && window.MascotaEngine.isEgg();
        var mascotaImg = '<img src="assets/img/appIcons/mascotaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">';
        var items;
        if (isEgg) {
            items = [
                { icon: mascotaImg, label: 'Ver huevo',     action: 'open' },
                { icon: '🔥',        label: 'Dar calor',     action: 'warm' },
                { sep: true },
                { icon: '🐣',        label: 'Eclosionar (DEV)', action: 'force-hatch' },
                { icon: '🗑',         label: 'Eliminar (testeo)', action: 'delete', danger: true },
            ];
        } else {
            items = [
                { icon: mascotaImg, label: 'Ver mascota',   action: 'open'   },
                { icon: '🍕',        label: 'Alimentar',      action: 'feed'   },
                { icon: '⚽',        label: 'Jugar',           action: 'play'   },
                { icon: '💬',        label: '¿Cómo estás?',   action: 'status' },
                { sep: true },
                { icon: '🗑',         label: 'Eliminar (testeo)', action: 'delete', danger: true },
            ];
        }

        items.forEach(function (item) {
            var li = document.createElement('li');
            if (item.sep) {
                li.className = 'sep';
            } else {
                /* innerHTML porque algunos `item.icon` son <img> inline
                   (PNG appIcon) en vez de un emoji string. El label es
                   data nuestra (no input usuario) → seguro. */
                li.innerHTML = item.icon + ' ' + item.label;
                if (item.danger) li.className = 'danger';
                li.addEventListener('click', function () {
                    menu.remove();
                    handleMascotaMenuAction(item.action);
                });
            }
            menu.appendChild(li);
        });

        /* Posicionar el menú cerca del botón flotante (puede haberse
           movido por el usuario). Ajustamos top/left tras montar — si
           se saldría por la derecha lo flipeamos a la izquierda del
           botón; si se saldría por abajo, hacia arriba. */
        document.body.appendChild(menu);
        var btnRect = menuBtn.getBoundingClientRect();
        var menuRect = menu.getBoundingClientRect();
        var top  = btnRect.bottom + 4;
        var left = btnRect.left;
        if (top + menuRect.height > window.innerHeight) {
            top = btnRect.top - menuRect.height - 4;
        }
        if (left + menuRect.width > window.innerWidth) {
            left = btnRect.right - menuRect.width;
        }
        menu.style.top  = Math.max(4, top) + 'px';
        menu.style.left = Math.max(4, left) + 'px';
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';

        /* Cerrar al hacer click fuera */
        setTimeout(function () {
            document.addEventListener('click', function closeMascotaMenu() {
                var m = document.getElementById('mascota-ctx-inline');
                if (m) m.remove();
                document.removeEventListener('click', closeMascotaMenu);
            });
        }, 0);
    });

    function handleMascotaMenuAction(action) {
        switch (action) {
            case 'open':
                openMascotaWindow();
                break;

            case 'feed':
                /* Abrir ventana picker — la elección concreta del
                   alimento se delega al usuario. El handler de los
                   botones llama a la API y notifica al engine. */
                openAlimentarWindow();
                break;

            case 'play':
                if (typeof window.MascotaEngine !== 'undefined') {
                    window.MascotaEngine.play();
                } else {
                    openMascotaWindow();
                }
                break;

            case 'warm':
                /* Acción exclusiva del estado HUEVO. */
                if (typeof window.MascotaEngine !== 'undefined' && window.MascotaEngine.warm) {
                    window.MascotaEngine.warm();
                } else {
                    openMascotaWindow();
                }
                break;

            case 'force-hatch':
                /* DEV: dispara el prompt de nombre + eclosión inmediata. */
                if (typeof window.MascotaEngine !== 'undefined'
                    && window.MascotaEngine.forceHatch) {
                    window.MascotaEngine.forceHatch();
                }
                break;

            case 'status':
                if (typeof window.MascotaEngine !== 'undefined') {
                    var s = window.MascotaEngine.getState();
                    if (s && s.mascota) {
                        var h = s.mascota.hambre;
                        var f = s.mascota.felicidad;
                        var msg;
                        if (!s.mascota.viva)         msg = '💀 Ha muerto... ábrela para revivirla.';
                        else if (h > 70 && f > 70)   msg = '😄 ¡Está genial!';
                        else if (h < 25)              msg = '😢 Tiene mucha hambre...';
                        else if (f < 30)              msg = '😔 Se siente sola.';
                        else                          msg = '🙂 Bien (🍖' + h + ' ♥' + f + ')';
                        window.notifSystem.show({
                            id:    'mascota-status-' + Date.now(),
                            type:  'info',
                            title: '🐾 Tu mascota',
                            message: msg,
                            autoDismissAfter: 4000,
                        });
                    } else {
                        openMascotaWindow();
                    }
                } else {
                    openMascotaWindow();
                }
                break;

            case 'delete':
                /* TESTEO: borra mascota + memoria con confirmación.
                   Tras éxito, despawnea (limpia el DOM, oculta el
                   botón, para los loops). Próximo doble-click en el
                   icono creará un huevo nuevo. */
                var ok = window.win98Confirm
                    ? null  // wait for callback
                    : window.confirm('¿Eliminar la mascota PARA SIEMPRE?\nSe borrarán todos sus datos. Solo para testeo.');
                var doDelete = function () {
                    fetch('assets/mascota/api.php?action=delete', { method: 'POST' })
                        .then(function (r) { return r.json(); })
                        .then(function (d) {
                            if (d && d.ok) {
                                if (window.MascotaEngine && window.MascotaEngine.despawn) {
                                    window.MascotaEngine.despawn();
                                }
                                /* La iframe de la ventana de gestión
                                   tenía SSR con la mascota anterior →
                                   invalidar para que la próxima
                                   apertura (o el reload inmediato si
                                   está abierta) traiga el estado nuevo
                                   (huevo recién creado). */
                                refreshMascotaWindow();
                                if (window.notifSystem) {
                                    window.notifSystem.show({
                                        id: 'mascota-deleted-' + Date.now(),
                                        type: 'info',
                                        title: '🗑 Mascota eliminada',
                                        message: 'Toda su data ha sido borrada. Doble-click en el icono para crear una nueva.',
                                        autoDismissAfter: 4000,
                                    });
                                }
                            } else {
                                alert('Error al eliminar: ' + (d && d.error || 'desconocido'));
                            }
                        })
                        .catch(function (e) { alert('Error de red: ' + e.message); });
                };
                if (window.win98Confirm) {
                    window.win98Confirm(
                        '¿Eliminar la mascota PARA SIEMPRE?\nSe borrarán todos sus datos.\n(Esta opción es solo para testeo.)',
                        'Eliminar mascota',
                        doDelete
                    );
                } else if (ok) {
                    doDelete();
                }
                break;
        }
    }

    /* Exponer para que el engine pueda llamar a openMascotaWindow */
    window.openMascotaWindow = openMascotaWindow;
})();

/* =========================
   MASCOTA — engine (carga dinámica)
   ─────────────────────────────────────────────
   Antes el engine se inicializaba Y spawneaba la mascota en cada carga
   del escritorio. Ahora solo se INICIALIZA (config + listo) — el
   `spawn()` se dispara al hacer doble-click en el icono "Mascota" del
   escritorio. Esto convierte la mascota en una "app" más en lugar de
   un widget global. El botón flotante ☰ aparece junto con el spawn.
========================= */
(function () {
    /* Wire del icono ANTES de que el script async termine — el HTML del
       icono ya está en el DOM (lo hemos puesto en la sección de iconos
       más arriba en este mismo archivo PHP). Si el engine aún no se ha
       cargado en el momento del doble-click, mostramos un fallback. */
    var icon = document.getElementById('mascota-icon');
    if (icon) {
        icon.addEventListener('dblclick', function () {
            if (window.MascotaEngine && window.MascotaEngine.spawn) {
                window.MascotaEngine.spawn();
            } else {
                console.warn('[Mascota] engine aún no cargado, reintentando…');
                /* Reintento tardío: si el script estaba aún cargando,
                   esperamos 500ms y volvemos a intentar. */
                setTimeout(function () {
                    if (window.MascotaEngine && window.MascotaEngine.spawn) {
                        window.MascotaEngine.spawn();
                    }
                }, 500);
            }
        });
    }

    var script  = document.createElement('script');
    script.src  = 'assets/mascota/engine.js';
    script.onload = function () {
        if (typeof window.MascotaEngine === 'undefined') return;
        /* init() es ligero — solo guarda config. Lo llamamos ya, sin
           esperar a DesktopState. */
        window.MascotaEngine.init({
            userId:   <?php echo (int)userIdByKey($desktopUserKey); ?>,
            parejaId: <?php echo (int)$parejaId; ?>,
            label:    <?php echo json_encode($desktopLabel); ?>
        });
    };
    script.onerror = function () {
        console.error('[Mascota] No se pudo cargar engine.js');
    };
    document.head.appendChild(script);
})();

</script>

</body>
</html>