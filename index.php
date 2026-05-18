<?php
require_once __DIR__ . '/assets/config.php';

$users = $loginUsers;

function getUserImage($label)
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $label);

    $extensions = ['jpg', 'jpeg', 'png', 'gif'];

    foreach ($extensions as $ext) {

        $path = __DIR__ . "/assets/img/{$safe}.{$ext}";

        if (file_exists($path)) {
            return "assets/img/{$safe}.{$ext}";
        }
    }

    return '';
}

$selectedUser = '';
$selectedLabel = '';
$selectedImage = '';
$showLogin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selectedUser = $_POST['user'] ?? '';

    if (isset($users[$selectedUser])) {

        $selectedLabel = $users[$selectedUser]['label'];

        $selectedImage = getUserImage($selectedLabel);

        $showLogin = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <title>Login 98.css</title>

    <link rel="stylesheet" href="https://unpkg.com/98.css">

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
    </style>

</head>

<body class="<?php echo htmlspecialchars($selectedUser); ?>">

<!-- INTRO -->
<div id="intro-overlay">
    <video id="intro-video" autoplay muted playsinline>
        <source src="assets/Vids/Inicio.mp4" type="video/mp4">
    </video>
</div>

<div class="login-window">

    <!-- FOTO -->

    <div
        class="user-preview <?php echo $showLogin ? 'visible' : ''; ?>"
        id="userPreview"
    >

        <div class="window">

            <div class="title-bar">

                <div class="title-bar-text">!ERROR</div>

            </div>

            <div class="window-body">

                <img
                    id="previewImage"
                    src="<?php echo htmlspecialchars($selectedImage); ?>"
                    alt=""
                >

            </div>

        </div>

    </div>

    <!-- SELECCION -->

    <div
        class="window <?php echo $showLogin ? 'hidden' : ''; ?>"
        id="selectWindow"
    >

        <div class="title-bar">

            <div class="title-bar-text">
                Seleccionar usuario
            </div>

        </div>

        <div class="window-body">

            <div class="user-list">

                <?php foreach ($users as $key => $user): ?>

                    <button
                        class="button user-button select-user"
                        data-user="<?php echo $key; ?>"
                        data-label="<?php echo htmlspecialchars($user['label']); ?>"
                        type="button"
                    >
                        <?php echo htmlspecialchars($user['label']); ?>

                    </button>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

    <!-- LOGIN -->

    <div
        class="window <?php echo $showLogin ? 'visible' : 'hidden'; ?>"
        id="loginWindow"
    >

        <div class="title-bar">

            <div class="title-bar-text">
                Iniciar sesión
            </div>

        </div>

        <div class="window-body">

            <form method="POST">
                <input
                    type="hidden" name="user" id="selectedUser" value="<?php echo htmlspecialchars($selectedUser); ?>">

                <p class="login-text">
                    Accediendo como <strong id="selectedLabel"></strong>
                </p>

                <div class="field-row-stacked">
                    <label>Contraseña</label>
                    <input type="password" name="password">

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

/* =========================
   INTRO
========================= */

var introVideo = document.getElementById('intro-video');
var introOverlay = document.getElementById('intro-overlay');

introVideo.addEventListener('ended', function(){
    introOverlay.classList.add('oculto');
    setTimeout(function(){
        introOverlay.style.display = 'none';
    }, 800);
});

// por si el video no carga, quitar intro igualmente
introVideo.addEventListener('error', function(){
    introOverlay.style.display = 'none';
});

/* =========================
   CAMBIO USUARIO
========================= */

function setUser(userKey, label)
{
    selectedUserInput.value = userKey;

    selectedLabel.textContent = label;

    body.classList.remove('user1', 'user2');

    body.classList.add(userKey);

    const extensions = ['jpg','jpeg','png','gif'];

    extensions.forEach(ext => {

        const path = `assets/img/${label}.${ext}`;

        const img = new Image();

        img.onload = function(){

            previewImage.src = path;

        };

        img.src = path;

    });

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
            this.dataset.label
        );
    });
});

/* =========================
   CAMBIAR USUARIO
========================= */

document.getElementById('changeUser').addEventListener('click', function(){
    loginWindow.classList.remove('visible');
    userPreview.classList.remove('visible');

    setTimeout(() => {
        selectWindow.classList.remove('hidden');
    }, 350);

});

/* =========================
   RECUPERAR POST
========================= */

<?php if($showLogin): ?>
loginWindow.classList.add('visible');
userPreview.classList.add('visible');
body.classList.add('<?php echo $selectedUser; ?>');
<?php endif; ?>

</script>

</body>
</html>
