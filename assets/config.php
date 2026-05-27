<?php
/* ─────────────────────────────────────────────────────────
   CONFIG — cargador de .env + claves de APIs + usuarios + helpers
   ─────────────────────────────────────────────────────────
   - .env (project root) contiene DB_*, SPOTIFY_*, GOOGLE_*
   - env(KEY, DEFAULT) lo expone al resto de la app
   - Las constantes y $loginUsers se definen aquí
   ───────────────────────────────────────────────────────── */
if (!function_exists('env')) {
    function _envLoad(): void {
        if (isset($GLOBALS['_env'])) return;
        $GLOBALS['_env'] = [];
        $file = dirname(__DIR__) . '/.env';
        if (!is_readable($file)) return;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            $eq = strpos($line, '=');
            if ($eq === false) continue;
            $key = trim(substr($line, 0, $eq));
            $val = trim(substr($line, $eq + 1));
            /* Quitar comentario inline (#) si NO está dentro de comillas */
            if ($val !== '' && $val[0] !== '"' && $val[0] !== "'") {
                $hash = strpos($val, '#');
                if ($hash !== false) $val = rtrim(substr($val, 0, $hash));
            }
            /* Quitar comillas envolventes */
            $len = strlen($val);
            if ($len >= 2 && (($val[0] === '"' && $val[$len-1] === '"') || ($val[0] === "'" && $val[$len-1] === "'"))) {
                $val = substr($val, 1, -1);
            }
            $GLOBALS['_env'][$key] = $val;
        }
    }
    function env(string $key, $default = null) {
        _envLoad();
        return array_key_exists($key, $GLOBALS['_env']) ? $GLOBALS['_env'][$key] : $default;
    }
}

/* ─── Claves de APIs externas ─── */
define('SPOTIFY_CLIENT_ID',     env('SPOTIFY_CLIENT_ID',     ''));
define('SPOTIFY_CLIENT_SECRET', env('SPOTIFY_CLIENT_SECRET', ''));
define('GOOGLE_CLIENT_ID',      env('GOOGLE_CLIENT_ID',      ''));

/* ─── Conexión a BD (la usa db.php) ─── */
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'scrapbook_melon'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

/* ─── $loginUsers viene de la tabla `usuarios` (incluye hash bcrypt) ───
   La contraseña no se valida con ===; usar password_verify($plain, $hash).
   Carga perezosa: si algo necesita $loginUsers, ensureLoginUsers() lo
   rellena la primera vez abriendo su propia conexión PDO (config.php se
   incluye antes que db.php en muchos sitios, así que no podemos depender
   de $GLOBALS['pdo'] aquí). */
$loginUsers = [];
function ensureLoginUsers(): void {
    global $loginUsers;
    if (!empty($loginUsers)) return;
    try {
        $pdo = isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO
            ? $GLOBALS['pdo']
            : new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                       DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT user_key, label, password FROM usuarios ORDER BY id ASC");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (!$r['user_key']) continue;
            $loginUsers[$r['user_key']] = [
                'label'    => $r['label'] ?: $r['user_key'],
                'password' => $r['password'],
            ];
        }
    } catch (Throwable $e) { /* DB caída → array vacío, el caller lo gestiona */ }
}
ensureLoginUsers();

function getUserImage($label)
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
        if (file_exists(__DIR__ . "/img/{$safe}.{$ext}")) {
            return "assets/img/{$safe}.{$ext}";
        }
    }
    return '';
}

function getUserWallpaper($label)
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    foreach ([$safe, strtolower($safe)] as $name) {
        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
            if (file_exists(__DIR__ . "/img/wallpapers/{$name}-wallpaper.{$ext}")) {
                return "assets/img/wallpapers/{$name}-wallpaper.{$ext}";
            }
        }
    }
    foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
        if (file_exists(__DIR__ . "/img/wallpapers/base-wallpaper.{$ext}")) {
            return "assets/img/wallpapers/base-wallpaper.{$ext}";
        }
    }
    return '';
}

function getUserStartIcon($label)
{
    $safe = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label));
    foreach (['png', 'svg', 'webp', 'jpg', 'jpeg', 'gif'] as $ext) {
        if (file_exists(__DIR__ . "/img/start-icons/{$safe}-start-icon.{$ext}")) {
            return "assets/img/start-icons/{$safe}-start-icon.{$ext}";
        }
    }
    return '';
}
