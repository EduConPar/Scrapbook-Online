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
/* Tidal (API oficial, credenciales de developer.tidal.com). Client
   credentials → solo catálogo público y playlists públicas. */
define('TIDAL_CLIENT_ID',     env('TIDAL_CLIENT_ID',     ''));
define('TIDAL_CLIENT_SECRET', env('TIDAL_CLIENT_SECRET', ''));
define('TIDAL_COUNTRY',       env('TIDAL_COUNTRY',       'US'));
/* Last.fm — necesario para extraer géneros A NIVEL DE CANCIÓN (tags
   crowdsourced). Si no se configura, el wrapped cae al fallback de
   géneros por artista vía Spotify. Crear key gratis en
   https://www.last.fm/api/account/create */
define('LASTFM_API_KEY',      env('LASTFM_API_KEY',      ''));

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

/* Fondo por defecto de la app (base-wallpaper). Se usa cuando un tema activo
   no define un fondo propio. Devuelve ruta relativa o ''. */
function defaultWallpaper()
{
    foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
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

/* Self-heal del stub `desktops/<label>-desktop.php`. Si por cualquier
   motivo (rollback erróneo de register-user.php en versiones antiguas,
   File Manager borrado, etc.) el archivo no existe pero el usuario sí
   está en BD, lo regeneramos al vuelo justo antes del redirect. Sin
   esto el login redirige a un 404 y el usuario queda sin escritorio
   utilizable hasta que un admin lo cree a mano. Idempotente. */
function ensureDesktopStub(string $label): void {
    $safe = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', $label));
    if ($safe === '') return;
    $path = dirname(__DIR__) . '/desktops/' . $safe . '-desktop.php';
    if (file_exists($path)) return;
    $stub = "<?php \$desktopLabel = " . var_export($label, true)
          . "; require __DIR__ . '/../desktop-base.php';\n";
    @file_put_contents($path, $stub);
}
