<?php
/* ─────────────────────────────────────────────────────────
   CONFIG — cargador de .env + claves de APIs + usuarios + helpers
   ─────────────────────────────────────────────────────────
   - .env (project root) contiene DB_*, SPOTIFY_*, GOOGLE_*
   - env(KEY, DEFAULT) lo expone al resto de la app
   - Las constantes y $loginUsers se definen aquí
   ───────────────────────────────────────────────────────── */

/* NUNCA imprimir warnings/notices en la salida: en XAMPP de Windows
   display_errors viene On por defecto y un aviso de PHP se colaría dentro
   del HTML/JS/JSON (rompiendo el script o la respuesta de las APIs).
   Como config.php lo incluye TODO punto de entrada, esto cubre la app entera.
   Los errores se siguen registrando en el log del servidor. */
@ini_set('display_errors', '0');
error_reporting(E_ALL);
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
            /* Tolerar comillas/espacios sueltos: hostnames y credenciales no
               contienen comillas legítimas — si quedaron por copy-paste,
               las saneamos para evitar errores de DNS y de conexión a BD. */
            $val = trim($val, " \t\"'");
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
$GLOBALS['_loginUsersError'] = null;
function ensureLoginUsers(): void {
    global $loginUsers;
    if (!empty($loginUsers)) return;
    try {
        $pdo = isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO
            ? $GLOBALS['pdo']
            : new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                       DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT id, user_key, label, password FROM usuarios ORDER BY id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* Auto-backfill: si hay filas sin user_key (legacy o import parcial),
           generamos uno con 'user{id}' y lo persistimos. Una sola vez. */
        $needsBackfill = false;
        foreach ($rows as $r) {
            if (empty($r['user_key'])) { $needsBackfill = true; break; }
        }
        if ($needsBackfill) {
            try {
                $upd = $pdo->prepare("UPDATE usuarios SET user_key = ? WHERE id = ?");
                foreach ($rows as &$r) {
                    if (empty($r['user_key'])) {
                        $r['user_key'] = 'user' . (int)$r['id'];
                        $upd->execute([$r['user_key'], (int)$r['id']]);
                    }
                }
                unset($r);
            } catch (Throwable $e) { /* sin permiso UPDATE → seguimos con fallback en memoria */ }
        }

        foreach ($rows as $r) {
            $key = $r['user_key'] ?: ('user' . (int)$r['id']);
            $loginUsers[$key] = [
                'label'    => $r['label'] ?: $key,
                'password' => $r['password'],
            ];
        }
        if (empty($loginUsers)) {
            $GLOBALS['_loginUsersError'] = 'La tabla `usuarios` está vacía. Importa el SQL.';
        }
    } catch (Throwable $e) {
        $GLOBALS['_loginUsersError'] = 'BD: ' . $e->getMessage();
    }
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
