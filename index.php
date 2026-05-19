<?php
session_start();
require_once __DIR__ . '/assets/config.php';

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

if ($selectedUser && isset($users[$selectedUser])) {
    $selectedLabel = $users[$selectedUser]['label'];
    $selectedImage = getUserImage($selectedLabel);
    $selectedWallpaper = getUserWallpaper($selectedLabel);
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
$skipIntro = isset($_GET['nointro']);

$baseWallpaper = '';
foreach (['png','jpg','jpeg'] as $ext) {
    if (file_exists(__DIR__ . "/assets/img/base-wallpaper.{$ext}")) {
        $baseWallpaper = "assets/img/base-wallpaper.{$ext}";
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
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
    #intro-start {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        padding: 14px 18px 14px 60px;
        background: #0d1117;
        z-index: 1;
        cursor: pointer;
        overflow: hidden;
    }
    #intro-start::after {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 3px,
            rgba(0,0,0,0.22) 3px,
            rgba(0,0,0,0.22) 4px
        );
        pointer-events: none;
    }
    #intro-start > span {
        font-family: 'VT323', monospace;
        font-size: 2rem;
        color: #c8ffe0;
        text-shadow: none;
        letter-spacing: 4px;
        text-transform: uppercase;
        user-select: none;
        position: relative;
        z-index: 1;
    }
    @keyframes termBlink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0; }
    }
    #intro-cursor {
        animation: termBlink 1s step-end infinite;
    }
    #intro-overlay {
        position: fixed;
        inset: 0;
        background: #000;
        z-index: 9999;
    }
    #intro-overlay video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #intro-overlay.oculto {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.8s ease;
    }
    .error-text {
        color: #c00;
        font-size: 11px;
        margin-top: 4px;
    }
    @keyframes adFromTL {
        from { opacity:0; transform:translate(-20px,-20px); }
        to   { opacity:1; transform:translate(0,0); }
    }
    @keyframes adFromTR {
        from { opacity:0; transform:translate(20px,-20px); }
        to   { opacity:1; transform:translate(0,0); }
    }
    @keyframes adFromBL {
        from { opacity:0; transform:translate(-20px,20px); }
        to   { opacity:1; transform:translate(0,0); }
    }
    @keyframes adFromBR {
        from { opacity:0; transform:translate(20px,20px); }
        to   { opacity:1; transform:translate(0,0); }
    }
    #ad-popup {
        position: fixed;
        z-index: 5000;
        display: none;
        cursor: pointer;
    }
    #ad-titlebar {
        cursor: move;
    }
    #ad-image {
        display: block;
        width: 10vw;
        height: auto;
        image-rendering: pixelated;
    }
    #previewImage {
        image-rendering: pixelated;
    }
    #selectWindow {
        min-width: 200px;
        background: #120808;
        border-color: #3a0000 #0a0000 #0a0000 #3a0000;
        box-shadow:
            1px 1px 0 #000,
            -1px -1px 0 #5a1010,
            0 8px 32px rgba(0,0,0,0.8),
            0 0 12px rgba(180,0,0,0.15);
    }
    #selectWindow .title-bar {
        background: linear-gradient(to right, #6a0000, #b02020);
        text-shadow: 0 1px 2px rgba(0,0,0,0.6);
        border-bottom: 1px solid #3a0000;
    }
    #selectWindow .window-body {
        background: #120808;
        padding: 8px;
    }
    .user-list {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .user-button {
        width: 100%;
        text-align: left;
        padding: 5px 12px;
        font-size: 12px;
        background: #1e0c0c !important;
        color: #d4a0a0 !important;
        border: 1px solid #3a1515 !important;
        border-radius: 1px;
        box-shadow:
            inset 0 1px 0 rgba(255,100,100,0.08),
            0 1px 3px rgba(0,0,0,0.5) !important;
        transition: background 0.1s, color 0.1s;
    }
    .user-button:hover {
        background: #5a0000 !important;
        color: #fff !important;
        border-color: #a03030 !important;
        box-shadow:
            inset 0 1px 0 rgba(255,120,120,0.15),
            0 2px 6px rgba(120,0,0,0.4) !important;
    }
    .user-button:active {
        background: #3a0000 !important;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.5) !important;
    }
    #error-icon {
        display: inline-flex;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        color: #cc0000;
        font-size: 10px;
        font-weight: bold;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        margin-right: 4px;
        line-height: 1;
        flex-shrink: 0;
    }
    .user-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .user-button {
        width: 100%;
        text-align: left;
        padding: 4px 12px;
        font-size: 12px;
    }
    .error-popup {
        position: fixed;
        z-index: 4500;
        width: 270px;
        opacity: 0;
        transform: scale(0.88);
        transition: opacity 0.18s ease, transform 0.18s ease;
        background: #120808;
        border-color: #3a0000 #0a0000 #0a0000 #3a0000;
        box-shadow: 1px 1px 0 #000, -1px -1px 0 #5a1010,
                    0 10px 36px rgba(0,0,0,0.85), 0 0 14px rgba(180,0,0,0.2);
    }
    .error-popup.ep-visible {
        opacity: 1;
        transform: scale(1);
    }
    .error-popup .title-bar {
        background: linear-gradient(to right, #6a0000, #b02020);
        border-bottom: 1px solid #3a0000;
    }
    .ep-title-icon {
        display: inline-flex;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #fff;
        color: #cc0000;
        font-size: 9px;
        font-weight: bold;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        margin-right: 3px;
        line-height: 1;
    }
    .error-popup .window-body {
        background: #120808;
        padding: 12px 14px;
    }
    .ep-body {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }
    .ep-big-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #cc0000;
        color: #fff;
        font-size: 24px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #800000;
        box-shadow: inset 0 1px 0 rgba(255,100,100,0.3);
        line-height: 1;
    }
    .ep-msg {
        color: #d4a0a0;
        font-size: 11px;
        margin: 2px 0 0;
        line-height: 1.5;
    }
    .ep-actions {
        display: flex;
        justify-content: center;
    }
    .ep-ok-btn {
        min-width: 72px;
        background: #1e0c0c !important;
        color: #d4a0a0 !important;
        border: 1px solid #3a1515 !important;
        box-shadow: inset 0 1px 0 rgba(255,80,80,0.07), 0 1px 3px rgba(0,0,0,0.5) !important;
    }
    .ep-ok-btn:hover {
        background: #5a0000 !important;
        color: #fff !important;
        border-color: #a03030 !important;
    }
    #ad-btn {
        width: 100%;
        margin-top: 8px;
        font-weight: bold;
        font-size: 12px;
        letter-spacing: 1px;
    }
    </style>
    <?php if ($baseWallpaper): ?>
    <style>body::before{ background-image:url('<?php echo htmlspecialchars($baseWallpaper); ?>'); opacity:1; }</style>
    <?php endif; ?>
    <?php if ($selectedWallpaper): ?>
    <style id="wallpaper-style">body::before{ background-image:url('<?php echo htmlspecialchars($selectedWallpaper); ?>'); }</style>
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
        <source src="assets/vids/inicio.mp4?v=<?php echo filemtime(__DIR__.'/assets/vids/inicio.mp4'); ?>" type="video/mp4">
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
                <img id="previewImage" src="<?php echo htmlspecialchars($selectedImage); ?>" alt="">
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
                <?php foreach ($users as $key => $user): ?>
                    <button
                        class="button user-button select-user"
                        data-user="<?php echo $key; ?>"
                        data-label="<?php echo htmlspecialchars($user['label']); ?>"
                        data-img="<?php echo htmlspecialchars(getUserImage($user['label'])); ?>"
                        data-wallpaper="<?php echo htmlspecialchars(getUserWallpaper($user['label'])); ?>"
                        type="button"
                    ><?php echo htmlspecialchars($user['label']); ?></button>
                <?php endforeach; ?>
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
                    <button class="button default" type="submit">Ingresar</button>
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
const introVideo = document.getElementById('intro-video');
const introOverlay = document.getElementById('intro-overlay');

/* =========================
   INTRO
========================= */

if (introVideo && introOverlay) {
    if (sessionStorage.getItem('introSeen')) {
        introOverlay.style.display = 'none';
    } else {
        const introStart = document.getElementById('intro-start');

        introStart.addEventListener('click', function() {
            introStart.style.display = 'none';
            introVideo.muted = false;
            introVideo.play();
        }, { once: true });

        introVideo.addEventListener('ended', function(){
            sessionStorage.setItem('introSeen', '1');
            introOverlay.classList.add('oculto');
            setTimeout(function(){
                introOverlay.style.display = 'none';
            }, 800);
        });

        introVideo.addEventListener('error', function(){
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

function setUser(userKey, label, imgSrc, wallpaperSrc)
{
    selectedUserInput.value = userKey;
    selectedLabel.textContent = label;
    setWallpaper(wallpaperSrc);
    body.classList.remove(...userKeys);
    body.classList.add('user-selected', userKey);
    if (imgSrc) previewImage.src = imgSrc;
    selectWindow.classList.add('hidden');
    loginWindow.classList.add('visible');
    userPreview.classList.add('visible');
}

/* =========================
   BOTONES USUARIO
========================= */

document.querySelectorAll('.select-user').forEach(button => {
    button.addEventListener('click', function(){
        setUser(this.dataset.user, this.dataset.label, this.dataset.img, this.dataset.wallpaper);
    });
});

/* =========================
   CAMBIAR USUARIO
========================= */

document.getElementById('changeUser').addEventListener('click', function(){
    loginWindow.classList.remove('visible');
    userPreview.classList.remove('visible');
    body.classList.remove('user-selected', ...userKeys);
    setWallpaper('');
    if (adPopup) adPopup.style.display = 'none';
    setTimeout(() => {
        selectWindow.classList.remove('hidden');
    }, 350);
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
    setUser = function(userKey, label, imgSrc, wallpaperSrc) {
        _origSetUser(userKey, label, imgSrc, wallpaperSrc);
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
setUser = function(u, l, i, w) { _origSetUser2(u, l, i, w); stopErrorPopups(); };

document.getElementById('changeUser').addEventListener('click', function() {
    setTimeout(startErrorPopups, 400);
}, true);
</script>

</body>
</html>
