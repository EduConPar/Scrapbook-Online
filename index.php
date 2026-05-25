<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
require_once __DIR__ . '/assets/config.php';
require_once __DIR__ . '/assets/themes/theme-helpers.php';

/* Base URL del proyecto, derivada del script actual. Asegura que las
   rutas a CSS de temas funcionen sea cual sea la URL de acceso
   (localhost/scrapbookOnline/, localhost/, virtual host, etc.). */
$projectBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
if ($projectBaseUrl === '/') $projectBaseUrl = '/';

/* Para cada usuario disponible, calcula su tema activo (si existe).
   Devuelve ['class' => '<className>', 'css' => '/scrapbookOnline/assets/themes/...css']
   o null si el usuario no tiene tema activo. */
function getUserActiveTheme($userKey, $label) {
    global $projectBaseUrl;
    $data = loadUserThemes($userKey);
    $active = !empty($data['active']) ? sanitizeThemeName($data['active']) : '';

    /* Fallback: si user1/user2 no tienen tema activo, usar el tema por
       defecto (Capi/Angie) para que SIEMPRE veas tu paleta al loguearte. */
    if ($active === '') {
        $def = defaultThemeForUser($userKey);
        if (!$def) return null;
        $active = sanitizeThemeName($def['name']);
    }

    $cssRel = themeCssRelPath($active, $label);
    if (!file_exists(__DIR__ . '/' . $cssRel)) {
        /* Asegura que el CSS del tema (default o activo) existe en disco */
        $themes = (array)($data['themes'] ?? []);
        $colors = isset($themes[$active]['colors']) ? $themes[$active]['colors'] : null;
        if (!$colors) {
            $def = defaultThemeForUser($userKey);
            if ($def && sanitizeThemeName($def['name']) === $active) $colors = $def['colors'];
        }
        if ($colors) {
            $css = generateThemeCss(themeCssClassName($active, $label), $colors);
            @file_put_contents(__DIR__ . '/' . $cssRel, $css);
        }
        if (!file_exists(__DIR__ . '/' . $cssRel)) return null;
    }
    return [
        'class' => themeCssClassName($active, $label),
        'css'   => $projectBaseUrl . $cssRel,
    ];
}

$adImages = [];
$adDir = __DIR__ . '/assets/img/archiveAd';
if (is_dir($adDir)) {
    foreach (scandir($adDir) as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $adImages[] = 'assets/img/archiveAd/' . $f;
        }
    }
}
$adImage = $adImages ? $adImages[array_rand($adImages)] : null;

$users = $loginUsers;

$selectedUser = $_POST['user'] ?? '';
$selectedLabel = '';
$selectedImage = '';
$selectedWallpaper = '';
$showLogin = false;
$loginError = false;

$selectedTheme = null;  /* ['class'=>..., 'css'=>...] del usuario seleccionado */
if ($selectedUser && isset($users[$selectedUser])) {
    $selectedLabel = $users[$selectedUser]['label'];
    $selectedImage = getUserImage($selectedLabel);
    $selectedWallpaper = getUserWallpaper($selectedLabel);
    $selectedTheme = getUserActiveTheme($selectedUser, $selectedLabel);
    $showLogin = true;

    if (isset($_POST['password'])) {
        if ($_POST['password'] === $users[$selectedUser]['password']) {
            $_SESSION['user'] = $selectedUser;
            header('Location: ' . strtolower($selectedLabel) . '-desktop.php');
            exit;
        }
        $loginError = true;
    }
}

$bodyClass = $showLogin ? "user-selected {$selectedUser}" : '';
if ($selectedTheme) $bodyClass .= ' ' . $selectedTheme['class'];
$skipIntro = isset($_GET['nointro']);

$baseWallpaper = '';
foreach (['png','jpg','jpeg','webp','gif'] as $ext) {
    if (file_exists(__DIR__ . "/assets/img/wallpapers/base-wallpaper.{$ext}")) {
        $baseWallpaper = "assets/img/wallpapers/base-wallpaper.{$ext}";
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Scrapbook Melon</title>
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <?php if ($baseWallpaper): ?>
    <style>body::before{ background-image:url('<?php echo htmlspecialchars($baseWallpaper); ?>'); opacity:1; }</style>
    <?php endif; ?>
    <?php if ($selectedWallpaper): ?>
    <style id="wallpaper-style">body::before{ background-image:url('<?php echo htmlspecialchars($selectedWallpaper); ?>'); }</style>
    <?php endif; ?>
    <?php if ($selectedTheme): ?>
    <link rel="stylesheet" id="user-theme-link" href="<?php echo htmlspecialchars($selectedTheme['css']); ?>">
    <?php endif; ?>
</head>

<body class="<?php echo $bodyClass; ?>">

<!-- INTRO -->
<?php if (!$skipIntro): ?>
<div id="intro-overlay">
    <div id="intro-start">
        <span>Click to start...<span id="intro-cursor">_</span></span>
    </div>
    <video id="intro-video" muted playsinline>
        <source src="https://raw.githubusercontent.com/EduConPar/Scrapbook-Online/dev/assets/vids/inicio.mp4" type="video/mp4">
    </video>
</div>
<?php endif; ?>

<div class="login-window">

    <!-- FOTO -->
    <div class="user-preview <?php echo $showLogin ? 'visible' : ''; ?>" id="userPreview">
        <div class="window">
            <div class="title-bar">
                <div class="title-bar-text">!ERROR</div>
            </div>
            <div class="window-body">
                <img id="previewImage" src="<?php echo htmlspecialchars($selectedImage); ?>" alt=""<?php if (!$selectedImage) echo ' style="display:none;"'; ?>>
                <div id="previewPlaceholder" class="login-avatar-placeholder"<?php if ($selectedImage) echo ' style="display:none;"'; ?>>👤</div>
            </div>
        </div>
    </div>

    <!-- SELECCION -->
    <div class="window <?php echo $showLogin ? 'hidden' : ''; ?>" id="selectWindow">
        <div class="title-bar">
            <div class="title-bar-text"><span id="error-icon">✕</span> !ERROR</div>
        </div>
        <div class="window-body">
            <div class="user-list">
                <?php foreach ($users as $key => $user):
                    $_theme = getUserActiveTheme($key, $user['label']);
                ?>
                    <button
                        class="button user-button select-user"
                        data-user="<?php echo $key; ?>"
                        data-label="<?php echo htmlspecialchars($user['label']); ?>"
                        data-img="<?php echo htmlspecialchars(getUserImage($user['label'])); ?>"
                        data-wallpaper="<?php echo htmlspecialchars(getUserWallpaper($user['label'])); ?>"
                        data-theme-class="<?php echo htmlspecialchars($_theme['class'] ?? ''); ?>"
                        data-theme-css="<?php echo htmlspecialchars($_theme['css'] ?? ''); ?>"
                        type="button"
                    ><?php echo htmlspecialchars($user['label']); ?></button>
                <?php endforeach; ?>
                <button class="button user-button" id="openRegister" type="button">+ Crear usuario</button>
            </div>
        </div>
    </div>

    <!-- LOGIN -->
    <div class="window <?php echo $showLogin ? 'visible' : 'hidden'; ?>" id="loginWindow">
        <div class="title-bar">
            <div class="title-bar-text">Iniciar sesión</div>
        </div>
        <div class="window-body">
            <form method="POST">
                <input type="hidden" name="user" id="selectedUser" value="<?php echo htmlspecialchars($selectedUser); ?>">
                <p class="login-text">Accediendo como <strong id="selectedLabel"></strong></p>
                <div class="field-row-stacked">
                    <label>Contraseña</label>
                    <input type="password" name="password" autofocus>
                    <?php if ($loginError): ?>
                    <p class="error-text">Contraseña incorrecta.</p>
                    <?php endif; ?>
                </div>
                <div class="login-actions">
                    <button class="button" type="button" id="changeUser">Cambiar usuario</button>
                    <?php $_showDel = $showLogin && $selectedUser !== '' && $selectedUser !== 'user1' && $selectedUser !== 'user2'; ?>
                    <button class="button" type="button" id="deleteUser" style="display:<?php echo $_showDel ? 'inline-block' : 'none'; ?>;">🗑 Eliminar</button>
                    <button class="button default" type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- REGISTER -->
    <div class="window hidden" id="registerWindow">
        <div class="title-bar">
            <div class="title-bar-text">+ Crear usuario</div>
        </div>
        <div class="window-body">
            <form id="registerForm" enctype="multipart/form-data">
                <div class="field-row-stacked">
                    <label>Foto de perfil</label>
                    <div class="register-file-row">
                        <input type="text" id="registerPhotoName" readonly placeholder="Sin archivo seleccionado">
                        <button class="button" type="button" id="registerPhotoBrowse">Examinar...</button>
                    </div>
                    <input type="file" id="registerPhoto" name="photo" accept="image/*" style="display:none;">
                </div>
                <div class="field-row-stacked">
                    <label>Nombre de usuario</label>
                    <input type="text" id="registerUsername" name="username" maxlength="30" required>
                </div>
                <div class="field-row-stacked">
                    <label>Contraseña</label>
                    <input type="password" id="registerPassword" name="password" required>
                </div>
                <p class="error-text" id="registerStatus" style="display:none;"></p>
                <div class="login-actions">
                    <button class="button" type="button" id="registerCancel">Cancelar</button>
                    <button class="button default" type="submit" id="registerSubmit">Crear</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php if ($adImage): ?>
<!-- POPUP AD -->
<div id="ad-popup" class="window">
    <div class="title-bar" id="ad-titlebar">
        <div class="title-bar-text">&#128276; MelonArchive</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="ad-close"></button>
        </div>
    </div>
    <div class="window-body" id="ad-body">
        <img id="ad-image" src="<?php echo htmlspecialchars($adImage); ?>" alt="MelonArchive">
        <button class="button" id="ad-btn">!VISIT NOW</button>
    </div>
</div>
<?php endif; ?>

<script>
const body = document.body;
const selectWindow = document.getElementById('selectWindow');
const loginWindow = document.getElementById('loginWindow');
const selectedUserInput = document.getElementById('selectedUser');
const selectedLabel = document.getElementById('selectedLabel');
const userPreview = document.getElementById('userPreview');
const previewImage = document.getElementById('previewImage');
const previewPlaceholder = document.getElementById('previewPlaceholder');
const introOverlay = document.getElementById('intro-overlay');

/* =========================
   INTRO
========================= */

if (introOverlay) {
    if (sessionStorage.getItem('introSeen')) {
        introOverlay.style.display = 'none';
    } else {
        const introStart = document.getElementById('intro-start');

        const introVideo = document.getElementById('intro-video');

        introStart.addEventListener('click', function() {
            introStart.style.display = 'none';
            introVideo.muted = false;
            introVideo.play();
        }, { once: true });

        introVideo.addEventListener('ended', function() {
            sessionStorage.setItem('introSeen', '1');
            introOverlay.classList.add('oculto');
            setTimeout(function() { introOverlay.style.display = 'none'; }, 800);
        });

        introVideo.addEventListener('error', function() {
            sessionStorage.setItem('introSeen', '1');
            introOverlay.style.display = 'none';
        });
    }
}

/* =========================
   CAMBIO USUARIO
========================= */

function setWallpaper(src)
{
    let style = document.getElementById('wallpaper-style');
    if (!style) {
        style = document.createElement('style');
        style.id = 'wallpaper-style';
        document.head.appendChild(style);
    }
    style.textContent = src ? `body::before{ background-image:url('${src}'); }` : '';
}

/* Aplica/quita el CSS del tema activo del usuario seleccionado.
   Se inyecta/actualiza un <link id="user-theme-link"> en <head>. */
let currentUserThemeClass = <?php echo json_encode($selectedTheme['class'] ?? ''); ?>;
function applyUserTheme(themeClass, themeCss)
{
    /* Quitar clase del tema anterior */
    if (currentUserThemeClass) body.classList.remove(currentUserThemeClass);
    currentUserThemeClass = themeClass || '';
    /* Si no hay tema → quitar el <link> entero (evita cargar página como CSS) */
    let link = document.getElementById('user-theme-link');
    if (!themeClass || !themeCss) {
        if (link) link.remove();
        return;
    }
    if (!link) {
        link = document.createElement('link');
        link.rel = 'stylesheet';
        link.id  = 'user-theme-link';
        document.head.appendChild(link);
    }
    body.classList.add(themeClass);
    /* Cache-buster por si el navegador tenía el CSS cacheado */
    link.href = themeCss + (themeCss.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
}

function pixelateImg(img, factor) {
    if (!img.naturalWidth || img.src.startsWith('data:')) return;
    const w = Math.max(1, Math.round(img.naturalWidth  * factor));
    const h = Math.max(1, Math.round(img.naturalHeight * factor));
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d');
    ctx.imageSmoothingEnabled = false;
    ctx.drawImage(img, 0, 0, w, h);
    img.src = c.toDataURL('image/png');
}

previewImage.addEventListener('load', function() { pixelateImg(previewImage, 0.1); });
if (previewImage.complete && previewImage.naturalWidth) pixelateImg(previewImage, 0.1);

const userKeys = [...document.querySelectorAll('.select-user')].map(b => b.dataset.user);

function setUser(userKey, label, imgSrc, wallpaperSrc, themeClass, themeCss)
{
    selectedUserInput.value = userKey;
    selectedLabel.textContent = label;
    setWallpaper(wallpaperSrc);
    body.classList.remove(...userKeys);
    body.classList.add('user-selected', userKey);
    applyUserTheme(themeClass, themeCss);
    if (imgSrc) {
        previewImage.src = imgSrc;
        previewImage.style.display = '';
        if (previewPlaceholder) previewPlaceholder.style.display = 'none';
    } else {
        previewImage.style.display = 'none';
        if (previewPlaceholder) previewPlaceholder.style.display = '';
    }
    /* Botón "Eliminar" sólo para usuarios que no sean Capi (user1) ni Angie (user2) */
    var delBtn = document.getElementById('deleteUser');
    if (delBtn) delBtn.style.display = (userKey === 'user1' || userKey === 'user2') ? 'none' : '';
    selectWindow.classList.add('hidden');
    loginWindow.classList.add('visible');
    userPreview.classList.add('visible');
}

/* =========================
   BOTONES USUARIO
========================= */

document.querySelectorAll('.select-user').forEach(button => {
    button.addEventListener('click', function(){
        setUser(
            this.dataset.user,
            this.dataset.label,
            this.dataset.img,
            this.dataset.wallpaper,
            this.dataset.themeClass,
            this.dataset.themeCss
        );
    });
});

/* =========================
   CAMBIAR USUARIO
========================= */

document.getElementById('changeUser').addEventListener('click', function(){
    loginWindow.classList.remove('visible');
    userPreview.classList.remove('visible');
    body.classList.remove('user-selected', ...userKeys);
    applyUserTheme('', '');
    setWallpaper('');
    if (adPopup) adPopup.style.display = 'none';
    setTimeout(() => {
        selectWindow.classList.remove('hidden');
    }, 350);
});

/* =========================
   BORRAR USUARIO
========================= */
document.getElementById('deleteUser').addEventListener('click', function() {
    var userKey = selectedUserInput.value;
    var label   = selectedLabel.textContent;
    if (!userKey || userKey === 'user1' || userKey === 'user2') return;
    var pwdInput = document.querySelector('input[name="password"]');
    var pwd = pwdInput ? pwdInput.value : '';
    if (!pwd) { alert('Introduce la contraseña para eliminar la cuenta.'); if (pwdInput) pwdInput.focus(); return; }
    if (!confirm('¿Eliminar la cuenta de "' + label + '"?\nTodos sus datos (perfil, listas, mensajes) se borrarán de forma permanente.')) return;

    var fd = new FormData();
    fd.append('user', userKey);
    fd.append('password', pwd);
    fetch('delete-user.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d || d.error) { alert((d && d.error) ? d.error : 'Error'); return; }
            window.location.href = 'index.php?nointro=1';
        })
        .catch(function() { alert('Error de red'); });
});

/* =========================
   REGISTRO DE USUARIO
========================= */

const registerWindow = document.getElementById('registerWindow');
const registerForm   = document.getElementById('registerForm');
const registerStatus = document.getElementById('registerStatus');
const registerSubmit = document.getElementById('registerSubmit');

document.getElementById('openRegister').addEventListener('click', function() {
    selectWindow.classList.add('hidden');
    registerWindow.classList.remove('hidden');
    registerWindow.classList.add('visible');
    registerStatus.style.display = 'none';
    registerStatus.textContent = '';
});

document.getElementById('registerPhotoBrowse').addEventListener('click', function() {
    document.getElementById('registerPhoto').click();
});
document.getElementById('registerPhoto').addEventListener('change', function() {
    document.getElementById('registerPhotoName').value = this.files.length ? this.files[0].name : '';
});

document.getElementById('registerCancel').addEventListener('click', function() {
    registerWindow.classList.remove('visible');
    registerWindow.classList.add('hidden');
    selectWindow.classList.remove('hidden');
    registerForm.reset();
});

registerForm.addEventListener('submit', function(e) {
    e.preventDefault();
    registerStatus.style.display = 'none';
    registerSubmit.disabled = true;
    var fd = new FormData(registerForm);
    fetch('register-user.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            registerSubmit.disabled = false;
            if (!d || d.error) {
                registerStatus.textContent = (d && d.error) ? d.error : 'Error';
                registerStatus.style.display = '';
                return;
            }
            /* Recargar para que la lista de usuarios incluya al nuevo */
            window.location.href = 'index.php?nointro=1';
        })
        .catch(function() {
            registerSubmit.disabled = false;
            registerStatus.textContent = 'Error de red';
            registerStatus.style.display = '';
        });
});

/* =========================
   POPUP AD
========================= */

const adPopup = document.getElementById('ad-popup');

if (adPopup) {
    const adClose = document.getElementById('ad-close');
    const adBody  = document.getElementById('ad-body');
    const adUrl   = 'https://www.youtube.com/@melondeaguaarchive/playlists';

    const adImg      = document.getElementById('ad-image');
    const adAllImages = <?php echo json_encode(array_values($adImages)); ?>;
    const corners = [
        { top: true,  left: true,  anim: 'adFromTL' },
        { top: true,  left: false, anim: 'adFromTR' },
        { top: false, left: true,  anim: 'adFromBL' },
        { top: false, left: false, anim: 'adFromBR' },
    ];

    function placeAd() {
        const c   = corners[Math.floor(Math.random() * 4)];
        const gap = 80 + Math.floor(Math.random() * 80);
        const taskbar = 50;

        adPopup.style.top    = 'auto';
        adPopup.style.left   = 'auto';
        adPopup.style.right  = 'auto';
        adPopup.style.bottom = 'auto';

        if (c.top)  adPopup.style.top    = gap + 'px';
        else        adPopup.style.bottom = (gap + taskbar) + 'px';
        if (c.left) adPopup.style.left   = gap + 'px';
        else        adPopup.style.right  = gap + 'px';

        adPopup.style.animation = 'none';
        adPopup.offsetHeight; /* reflow para reiniciar */
        adPopup.style.animation = c.anim + ' 0.35s ease-out forwards';
    }

    function pixelate(img) {
        if (img.dataset.px) return;
        img.dataset.px = '1';
        const factor = 0.15;
        const w = Math.max(1, Math.round(img.naturalWidth  * factor));
        const h = Math.max(1, Math.round(img.naturalHeight * factor));
        const c = document.createElement('canvas');
        c.width = w; c.height = h;
        const ctx = c.getContext('2d');
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(img, 0, 0, w, h);
        img.src = c.toDataURL('image/png');
    }

    adImg.addEventListener('load', function() { pixelate(adImg); });
    if (adImg.complete && adImg.naturalWidth) pixelate(adImg);

    function showAd() {
        if (adAllImages.length) {
            const newSrc = adAllImages[Math.floor(Math.random() * adAllImages.length)];
            delete adImg.dataset.px;
            adImg.src = newSrc;
        }
        placeAd();
        adPopup.style.display = 'block';
    }

    adClose.addEventListener('click', function(e) {
        e.stopPropagation();
        adPopup.style.display = 'none';
    });

    adBody.addEventListener('click', function() {
        window.open(adUrl, '_blank');
    });

    /* Arrastrar */
    (function() {
        const bar = document.getElementById('ad-titlebar');
        let dragging = false, ox, oy;
        bar.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'BUTTON') return;
            dragging = true;
            const rect = adPopup.getBoundingClientRect();
            adPopup.style.animation = 'none';
            adPopup.style.top    = rect.top  + 'px';
            adPopup.style.left   = rect.left + 'px';
            adPopup.style.bottom = 'auto';
            adPopup.style.right  = 'auto';
            ox = e.clientX - rect.left;
            oy = e.clientY - rect.top;
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            adPopup.style.left = (e.clientX - ox) + 'px';
            adPopup.style.top  = (e.clientY - oy) + 'px';
        });
        document.addEventListener('mouseup', () => { dragging = false; });
    })();

    /* Mostrar al seleccionar usuario */
    const _origSetUser = setUser;
    setUser = function(...args) {
        _origSetUser.apply(this, args);
        setTimeout(showAd, 400);
    };

    <?php if ($showLogin): ?>
    showAd();
    <?php endif; ?>
}

/* ========================
   FATAL ERROR POPUPS
======================== */
(function() {
    var msgs = [
        'SOUL.EXE stopped working.\nPlease select a user to continue.',
        'User not responding.\nIs your body still yours?',
        'Biometric data confusing. \nTry to remember who you are.',
        'Integrated GPS failure.\nInfinity cannot be measured.',
        'Unauthorised presence.\nLog in as fast as possible.',
        'Connection to reality lost.\nPlease select a user to reconnect.',
        'Consciousness leaking.\nEncapsulate yourself.',
        'Questioning existence.\n Think to stabilize.',
        '"I" is not a valid user.\nPlease select yourself to proceed.',
        'Hostile environment detected.\nPlease flee.',
        'Kernel panic - not fitting: Attempted to kill init!\n Task failed.',
        'const self = null;\nError: Cannot set property "self" of null at line 42.',
        'booting failed: no operating system found.\nPlease select a user to load your world.',
        'Module human.exe failed to initialize.\n Check the list to proceed.',
    ];

    var active = false;
    var timer  = null;

    function createPopup() {
        var msg = msgs[Math.floor(Math.random() * msgs.length)];
        var el  = document.createElement('div');
        el.className = 'error-popup window';
        el.innerHTML =
            '<div class="title-bar">' +
                '<div class="title-bar-text"><span class="ep-title-icon">✕</span> !FATAL ERROR</div>' +
                '<div class="title-bar-controls"><button aria-label="Close" class="ep-close-btn"></button></div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="ep-body">' +
                    '<div class="ep-big-icon">✕</div>' +
                    '<p class="ep-msg">' + msg.replace('\n', '<br>') + '</p>' +
                '</div>' +
                '<div class="ep-actions"><button class="button ep-ok-btn">OK</button></div>' +
            '</div>';

        var maxX = Math.max(50, window.innerWidth  - 290);
        var maxY = Math.max(50, window.innerHeight - 180);
        el.style.left = (20 + Math.floor(Math.random() * maxX)) + 'px';
        el.style.top  = (20 + Math.floor(Math.random() * maxY)) + 'px';

        document.body.appendChild(el);
        setTimeout(function() { el.classList.add('ep-visible'); }, 10);

        function close() { el.classList.remove('ep-visible'); setTimeout(function() { el.remove(); }, 180); }
        el.querySelector('.ep-close-btn').addEventListener('click', close);
        el.querySelector('.ep-ok-btn').addEventListener('click', close);
    }

    function schedule() {
        if (!active) return;
        var delay = timer === null ? 8000 : (6000 + Math.random() * 5000);
        timer = setTimeout(function() { if (!active) return; createPopup(); schedule(); }, delay);
    }

    window.startErrorPopups = function() {
        if (active) return;
        active = true;
        timer = null;
        schedule();
    };

    window.stopErrorPopups = function() {
        active = false;
        clearTimeout(timer);
        timer = null;
        document.querySelectorAll('.error-popup').forEach(function(p) { p.remove(); });
    };
})();

<?php if (!$showLogin): ?>startErrorPopups();<?php endif; ?>

const _origSetUser2 = setUser;
setUser = function(...args) { _origSetUser2.apply(this, args); stopErrorPopups(); };

document.getElementById('changeUser').addEventListener('click', function() {
    setTimeout(startErrorPopups, 400);
}, true);
</script>

</body>
</html>
