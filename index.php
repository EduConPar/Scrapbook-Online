<?php
session_start();
require_once __DIR__ . '/assets/config.php';

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Scrapbook Melon</title>
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
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
    </style>
    <?php if ($selectedWallpaper): ?>
    <style id="wallpaper-style">body::before{ background-image:url('<?php echo htmlspecialchars($selectedWallpaper); ?>'); }</style>
    <?php endif; ?>
</head>

<body class="<?php echo $bodyClass; ?>">

<!-- INTRO -->
<?php if (!$skipIntro): ?>
<div id="intro-overlay">
    <video id="intro-video" autoplay muted playsinline>
        <source src="assets/vids/inicio.mp4" type="video/mp4">
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
            <div class="title-bar-text">Seleccionar usuario</div>
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
    introOverlay.addEventListener('click', function(){
        introVideo.muted = false;
    }, { once: true });

    introVideo.addEventListener('ended', function(){
        introOverlay.classList.add('oculto');
        setTimeout(function(){
            introOverlay.style.display = 'none';
        }, 800);
    });

    introVideo.addEventListener('error', function(){
        introOverlay.style.display = 'none';
    });
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
    setTimeout(() => {
        selectWindow.classList.remove('hidden');
    }, 350);
});
</script>

</body>
</html>
