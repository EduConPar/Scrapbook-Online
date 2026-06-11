<?php
/* ──────────────────────────────────────────────────────────────────────
   LOGIN MANUAL — username + password en TEXTO
   ──────────────────────────────────────────────────────────────────────
   Variante de index.php pensada para "cambiar de cuenta" desde el menú
   de ajustes del móvil. El usuario escribe su nombre y contraseña en
   inputs de texto (sin picker de avatares).

   Compatible con el resto del sistema: misma tabla `usuarios`, mismo
   bcrypt, misma sesión PHP. Resuelve el `user_key` buscando por `label`
   (case-insensitive).

   El look replica index.php: 98.css + login.css + tokens.css + themes
   + filtro LCD/VHS inline + wallpaper de fondo. Misma fuente VT323.
   ────────────────────────────────────────────────────────────────────── */
require_once __DIR__ . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once __DIR__ . '/assets/config.php';

$projectBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
if ($projectBaseUrl === '/') $projectBaseUrl = '/';

$enteredUsername = '';
$error           = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $enteredUsername = $username;

    if ($username === '' || $password === '') {
        $error = true;
    } else {
        $matchedKey = null;
        foreach ($loginUsers as $key => $u) {
            if (strcasecmp($u['label'], $username) === 0) {
                $matchedKey = $key; break;
            }
        }
        if ($matchedKey !== null && password_verify($password, $loginUsers[$matchedKey]['password'])) {
            $_SESSION['user'] = $matchedKey;
            $target = isMobileDevice()
                ? 'mobile.php'
                : 'desktops/' . strtolower($loginUsers[$matchedKey]['label']) . '-desktop.php';
            header('Location: ' . $target);
            exit;
        }
        $error = true;
    }
}

$baseWallpaper = '';
foreach (['png','jpg','jpeg','webp','gif'] as $ext) {
    if (file_exists(__DIR__ . "/assets/img/wallpapers/base-wallpaper.{$ext}")) {
        $baseWallpaper = 'assets/img/wallpapers/base-wallpaper.' . $ext;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Iniciar sesión — Scrapbook Melon</title>
    <link rel="icon" href="assets/img/mobile/icon.png" type="image/png">
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <!-- Filtro LCD/VHS: scanlines + viñeta (idéntico a index.php) -->
    <style>
    html::before {
        content: '';
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 2147483645;
        background-image: repeating-linear-gradient(
            180deg,
            rgba(0, 0, 0, 0.10) 0,
            rgba(0, 0, 0, 0.10) 1px,
            rgba(255, 255, 255, 0.05) 1px,
            rgba(255, 255, 255, 0.05) 2px,
            transparent 2px,
            transparent 3px
        );
    }
    html::after {
        content: '';
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 2147483646;
        background: radial-gradient(
            ellipse 75% 60% at center,
            transparent 60%,
            rgba(0, 0, 0, 0.10) 85%,
            rgba(0, 0, 0, 0.24) 100%
        );
    }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <?php if ($baseWallpaper): ?>
    <style>body::before{ background-image:url('<?php echo htmlspecialchars($baseWallpaper); ?>'); opacity:1; }</style>
    <?php endif; ?>
</head>

<body>

<div class="login-window">

    <!-- LOGIN MANUAL — username + password -->
    <div class="window visible" id="loginWindow">
        <div class="title-bar">
            <div class="title-bar-text">Iniciar sesión</div>
        </div>
        <div class="window-body">
            <form method="POST" autocomplete="on">
                <div class="field-row-stacked">
                    <label>Usuario</label>
                    <input type="text" name="username" autocomplete="username"
                           value="<?php echo htmlspecialchars($enteredUsername); ?>" autofocus required>
                </div>
                <div class="field-row-stacked">
                    <label>Contraseña</label>
                    <input type="password" name="password" autocomplete="current-password" required>
                    <?php if ($error): ?>
                    <p class="error-text">Usuario o contraseña incorrectos.</p>
                    <?php endif; ?>
                </div>
                <div class="login-actions">
                    <button class="button default" type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

</div>

</body>
</html>
