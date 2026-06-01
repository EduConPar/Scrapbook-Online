<?php
/* ──────────────────────────────────────────────────────────────────────
   MANIFEST DINÁMICO PWA
   ──────────────────────────────────────────────────────────────────────
   Si recibe ?token=XXX (token válido formato hex 64), lo embebe en el
   start_url para auto-login en standalone. Sin token → start_url base.

   Iconos: SVG vía pwa-icon.php (escalan limpiamente a cualquier
   resolución) + variante maskable (Android la recorta a círculo en el
   launcher → la safe-zone evita que se recorte la sandía).
   ────────────────────────────────────────────────────────────────────── */
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, private');

require_once __DIR__ . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once __DIR__ . '/assets/config.php';

$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) $token = '';

/* `pwa=1` es el marcador que el servidor lee para identificar que la
   petición viene del icono de la PWA (no del navegador). El token, si
   existe, sigue al lado para hacer autologin. */
$qs = ['pwa' => '1'];
if ($token !== '') $qs['t'] = $token;
$startUrl = './mobile.php?' . http_build_query($qs);

/* COLOR DE FONDO DEL SPLASH SCREEN
   Si el usuario está logueado al instalar la PWA, el SO usará el
   `desktopBg` de su tema como color del splash. Si no hay sesión
   (o el tema no define el color), caemos a un negro grisáceo neutro
   — mejor que el azul del default antiguo. Una vez instalada la
   PWA, este color queda CACHEADO por el SO y no cambia hasta reinstalar. */
$bgColor = '#1a1a1a';        /* negro grisáceo neutro */
if (isset($_SESSION['user']) && isset($loginUsers[$_SESSION['user']])) {
    require_once __DIR__ . '/assets/themes/theme-helpers.php';
    $userKey = $_SESSION['user'];
    $userLabel = $loginUsers[$userKey]['label'];
    $_userThemes = loadUserThemes($userKey);
    $activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
    if ($activeTheme !== '' && isset($_userThemes['themes'][$activeTheme]['colors']['desktopBg'])) {
        $candidate = (string)$_userThemes['themes'][$activeTheme]['colors']['desktopBg'];
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $candidate)) {
            $bgColor = $candidate;
        }
    }
}

$manifest = [
    'name'              => 'Melon Hub',
    'short_name'        => 'Melon Hub',
    'description'       => 'Tu escritorio personal Scrapbook Melon, versión móvil.',
    'start_url'         => $startUrl,
    'scope'             => './',
    'id'                => './mobile.php',
    'display'           => 'standalone',
    'display_override'  => ['standalone', 'minimal-ui'],
    'orientation'       => 'portrait',
    'background_color'  => $bgColor,
    'theme_color'       => $bgColor,
    'lang'              => 'es',
    'categories'        => ['lifestyle', 'social', 'utilities'],
    'icons'             => [
        /* Chrome Android exige PNG (no acepta SVG para el check de
           instalabilidad PWA) en tamaños 192 Y 512. Apuntamos al mismo
           archivo en ambos tamaños — el contenido es 376x391, Chrome
           acepta la declaración aunque las dimensiones reales sean
           distintas. Mantenemos también la versión SVG como secundaria
           por si algún navegador la prefiere. */
        [
            'src'     => 'assets/img/start-icons/capi-start-icon.png',
            'sizes'   => '192x192',
            'type'    => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src'     => 'assets/img/start-icons/capi-start-icon.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src'     => 'assets/img/start-icons/capi-start-icon.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'maskable',
        ],
        /* SVG como extra (Chrome desktop, Edge, Firefox lo aceptan). */
        [
            'src'     => 'assets/pwa-icon.php?size=512',
            'sizes'   => 'any',
            'type'    => 'image/svg+xml',
            'purpose' => 'any',
        ],
    ],
];
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
