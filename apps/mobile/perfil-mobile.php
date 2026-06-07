<?php
/* ──────────────────────────────────────────────────────────────────────
   PERFIL — versión móvil standalone
   ──────────────────────────────────────────────────────────────────────
   Entrada independiente del escritorio: NO se incluye desde
   desktop-base.php y no depende de $desktopLabel / appTitleIcon().
   Lee toda la información vía /assets/profile/api.php:
     - get-profile  → cabecera (descripción, quote)
     - get-lists    → 5 categorías (movies/series/books/games/music)
   UI: cabecera + pestañas horizontales scroll + lista vertical.
   ────────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__, 2) . '/assets/mobile-detect.php';
setLongSessionCookie();
session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

if (!isset($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    header('Location: ../../index.php');
    exit;
}
$userKey   = $_SESSION['user'];
$userLabel = $loginUsers[$userKey]['label'];

/* ── pareja_id ── Mismo patrón que calendario.php — la query une la
   tabla parejas con usuarios por username (lowercased). Si el usuario
   no está en ninguna pareja, parejaId queda en 0 y crearMomento envía
   null (save-momento lo acepta). */
$parejaId = 0;
try {
    $stmt = $pdo->prepare("
        SELECT p.id FROM parejas p
        JOIN usuarios u1 ON p.usuario1_id = u1.id
        JOIN usuarios u2 ON p.usuario2_id = u2.id
        WHERE u1.username = ? OR u2.username = ?
    ");
    $stmt->execute([strtolower($userLabel), strtolower($userLabel)]);
    $pareja = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pareja) $parejaId = (int)$pareja['id'];
} catch (Throwable $e) { /* sin pareja → momento sin asociar */ }

/* Avatar del usuario logueado */
$userImg = '';
$safe = preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel);
foreach (['png','jpg','jpeg','gif'] as $ext) {
    if (file_exists(dirname(__DIR__, 2) . "/assets/img/{$safe}.{$ext}")) {
        $userImg = "../../assets/img/{$safe}.{$ext}";
        break;
    }
}

/* ── TEMA ACTIVO DEL USUARIO ──
   Misma estrategia que mobile.php: el look hereda 98.css + tokens.css
   + tema del usuario, y el inline CSS solo refina lo específico de
   esta pantalla con variables del tema. */
require_once dirname(__DIR__, 2) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = themeCssRelPath($activeTheme, $userLabel);
    if ($activeThemeCss !== '' && !file_exists(dirname(__DIR__, 2) . '/' . $activeThemeCss)) {
        $activeThemeCss = '';
    }
}
$themeBgColor = '#000000';
if ($activeTheme !== '' && isset($_userThemes['themes'][$activeTheme]['colors']['desktopBg'])) {
    $candidate = (string)$_userThemes['themes'][$activeTheme]['colors']['desktopBg'];
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $candidate)) {
        $themeBgColor = $candidate;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- PWA GUARD: fuera de standalone → fuera de la app. -->
    <script>
    (function(){
        var sa = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
              || window.navigator.standalone === true;
        if (!sa) window.location.replace('../../mobile-landing.php');
    })();
    /* --mh-vh sincronizado con window.innerHeight (sobrevive al bfcache). */
    (function(){
        function setVh(){
            document.documentElement.style.setProperty('--mh-vh', window.innerHeight + 'px');
        }
        setVh();
        window.addEventListener('resize', setVh);
        window.addEventListener('orientationchange', setVh);
        window.addEventListener('pageshow', setVh);
        window.addEventListener('visibilitychange', setVh);
    })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="<?= htmlspecialchars($themeBgColor) ?>">
    <title>Perfil — Scrapbook Melon</title>
    <link rel="icon" href="../../assets/img/mobile/icon.png" type="image/png">
    <!-- Win98 + tema del usuario para consistencia con mobile.php -->
    <link rel="stylesheet" href="../../assets/css/98.css">
    <link rel="stylesheet" href="../../assets/css/tokens.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
    <link rel="stylesheet" href="../../assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="../../<?= htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../../assets/css/mobile-theme.css?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/mobile-theme.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <style>
        /* Tweaks específicos del perfil — el grueso del look viene de
           mobile-theme.css. Aquí solo añadimos lo propio de la pantalla
           (tabs de categorías, listado de items con poster). */

        /* ── Datos del perfil dentro del userbar: bio + meta + conexiones ──
           Replica `profile-info-bio`, `profile-info-meta`, `profile-info-links`
           del escritorio, pero embebidos en la cabecera para que no roben
           espacio adicional. */
        .pf-userbar { align-items: flex-start; padding: 8px; }
        .pf-userbar .mh-userbar-text { min-width: 0; }
        /* Columna izquierda: avatar + cuadrado de conexiones debajo.
           Flex column para alinear ambos a la misma anchura visual. */
        .pf-userbar-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        /* Bio = "descripción". Cursiva, color de acento. El placeholder
           "Sin descripción" usa otra clase para grisarlo. */
        .pf-about-bio {
            font-size: 11px;
            font-style: italic;
            line-height: 1.4;
            color: var(--accent, #000080);
            margin: 4px 0 3px;
            word-break: break-word;
        }
        .pf-about-bio.is-empty {
            color: var(--text-faint, #888);
            opacity: 0.85;
        }
        /* Línea de meta: pronombres · edad años · 📍 país. */
        .pf-about-meta {
            font-size: 10px;
            color: var(--text-faint, #666);
            margin: 0 0 4px;
            line-height: 1.3;
        }
        .pf-about-meta:empty { display: none; }
        .pf-about-meta .pf-meta-sep {
            margin: 0 4px;
            color: var(--text-faint, #aaa);
        }
        /* Cuadrado de conexiones — panel hundido Win98 con rejilla 2×2
           de iconos. Pensado para alinear visualmente con la anchura
           del avatar (~52px) que tiene encima. */
        .pf-about-socials {
            display: grid;
            grid-template-columns: repeat(2, 22px);
            grid-auto-rows: 22px;
            gap: 2px;
            padding: 3px;
            min-height: 46px;       /* fuerza forma cuadrada aunque haya 1-2 socials */
            background: var(--input-bg, #fff);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a);
            box-sizing: content-box;
        }
        .pf-about-socials:empty { display: none; }
        .pf-about-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            font-size: 13px;
            text-decoration: none;
            color: var(--text, #000);
            background: var(--btn-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff);
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
        }
        .pf-about-social:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a);
        }

        /* ── Sub-tabs de música: álbumes vs canciones ──
           Solo visible en la pestaña 'música'. Replica el patrón del
           desktop (#music-tab-bar en perfil.php). */
        .pf-subtabs {
            display: flex;
            gap: 2px;
            padding: 4px 6px;
            background: var(--btn-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-2, grey);
            flex-shrink: 0;
        }
        .pf-subtabs[hidden] { display: none !important; }
        .pf-subtab {
            flex: 1;
            padding: 6px 10px;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            font-size: 11px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-family: inherit;
            min-height: 30px;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf);
        }
        .pf-subtab.active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }
        .pf-subtab:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }

        /* ── Destacados ──
           Mismo concepto que `#music-catview-destacados` del escritorio:
           hasta 3 items con `featured: true`. Aquí los pintamos como una
           rejilla 3-col compacta encima de la lista. Tap en un slot con
           reseña abre el modal de reseña (mismo click delegado). */
        .pf-destacados {
            padding: 6px 8px;
            background: var(--input-bg, #fff);
            color: var(--input-text, var(--text, #000));
            border-bottom: 1px dotted var(--text-faint, #aaa);
            flex-shrink: 0;
        }
        .pf-destacados[hidden] { display: none !important; }
        .pf-destacados-title {
            font-size: 10px;
            color: var(--text-muted, var(--text-faint, #666));
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .pf-destacados-slots {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }
        .pf-dest-slot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            padding: 6px 4px;
            min-height: 92px;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            text-align: center;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-2, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-1, #dfdfdf);
            overflow: hidden;
        }
        .pf-dest-slot.empty {
            color: var(--text-faint, #888);
            justify-content: center;
            align-items: center;
            font-size: 18px;
        }
        .pf-dest-slot.has-review {
            cursor: pointer;
            -webkit-user-select: none;
            user-select: none;
            touch-action: manipulation;
        }
        .pf-dest-slot.has-review:active {
            box-shadow:
                inset -1px -1px var(--bezel-light-2, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-1, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }
        .pf-dest-cover {
            width: 44px;
            height: 44px;
            object-fit: cover;
            image-rendering: pixelated;
            background: var(--inset-bg, #ddd);
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .pf-dest-cover-ph {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--accent, #000080);
        }
        .pf-dest-title {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            width: 100%;
            color: inherit;
        }
        .pf-dest-artist {
            font-size: 9px;
            color: var(--text-faint, #666);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }
        .pf-dest-stars {
            font-size: 10px;
            color: var(--star-color, #f4c130);
            display: inline-flex;
            align-items: center;
            gap: 1px;
            line-height: 1;
        }
        .pf-dest-stars > .pf-stars > span { display: inline-block; }
        .pf-dest-stars-num {
            font-size: 9px;
            color: var(--text-faint, #666);
            font-weight: bold;
            margin-left: 2px;
        }

        /* ── Tabs horizontales (categorías) ── */
        .pf-tabs {
            display: flex;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: var(--btn-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-2, grey);
            scrollbar-width: none;
            flex-shrink: 0;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset 1px 1px var(--bezel-light-2, #fff);
        }
        .pf-tabs::-webkit-scrollbar { display: none; }
        .pf-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;          /* nunca colapsa contenido por flex sibling */
            padding: 8px 10px;
            color: var(--text, #000);
            background: transparent;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
            line-height: 1;          /* evita que el badge desborde verticalmente */
            letter-spacing: 0.3px;
            border: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-family: inherit;
            min-height: 36px;
        }
        .pf-tab.active {
            color: var(--accent, #000080);
            border-bottom-color: var(--accent, #000080);
            background: var(--input-bg, #fff);
        }
        .pf-tab .pf-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 16px;
            height: 16px;
            padding: 0 5px;
            box-sizing: border-box;
            font-size: 10px;
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            font-weight: bold;
            line-height: 1;
        }

        /* ── Lista de items dentro de un mh-panel ── */
        .pf-list { padding: 0; }
        .pf-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-top: 1px dotted var(--text-faint, #aaa);
            min-height: 56px;
            color: var(--input-text, var(--text, #000));
        }
        .pf-item:first-child { border-top: none; }
        /* Solo el item con reseña ofrece feedback `:active` — los
           que no son clicables se quedan sin highlight para no
           sugerir falsa interactividad. */
        .pf-item.has-review:active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .pf-item-poster {
            width: 40px;
            height: 56px;
            object-fit: cover;
            background: var(--inset-bg, #ddd);
            flex-shrink: 0;
            margin-left: 2px;          /* aire al shadow exterior */
            /* Marco de foto Win98 (idéntico al del escritorio). */
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1, #0a0a0a),
                 1px  1px 0 var(--bezel-light-1, #fff),
                -2px -2px 0 var(--bezel-dark-2, grey),
                 2px  2px 0 var(--bezel-light-2, #dfdfdf);
        }
        .pf-item.music .pf-item-poster { width: 44px; height: 44px; }
        .pf-item-poster.placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--accent, #000080);
            background: var(--inset-bg, #ddd);
        }
        .pf-item-info { flex: 1; min-width: 0; }
        .pf-item-title {
            font-size: 13px;
            font-weight: bold;
            line-height: 1.3;
            color: inherit;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .pf-item-sub {
            font-size: 11px;
            color: var(--text-faint, #666);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pf-item.has-review:active .pf-item-sub { color: var(--accent-text, #fff); }
        /* Estrellas con medias estrellas — replica el patrón de
           perfil.css del escritorio (makeStarsHtml). Cada estrella es
           un <span> y para la media usamos clip-path inline.
           El contenedor con .pf-review-trigger es táctil y abre la
           reseña al pulsar. */
        .pf-stars {
            color: var(--star-color, #f4c130);
            font-size: 13px;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 1px;
            line-height: 1;
        }
        .pf-stars > span {
            display: inline-block;
            line-height: 1;
        }
        .pf-stars-num {
            color: var(--text-faint, #666);
            font-size: 10px;
            margin-left: 4px;
            font-weight: bold;
        }
        .pf-item.has-review:active .pf-stars-num { color: var(--accent-text, #fff); }
        /* Item con reseña: todo el row es clicable. Cursor pointer y
           feedback `:active` reforzado para indicar que la fila completa
           abre la reseña al pulsar. */
        .pf-item.has-review {
            cursor: pointer;
            touch-action: manipulation;
            -webkit-user-select: none;
            user-select: none;
        }
        .pf-stars-wrap {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        /* ── Modal de reseña — centrado, con tema del usuario ──
           Centrado vertical (no bottom-sheet) para que se sienta como
           una ventana modal Win98 clásica. Mount como hijo de <body>
           (no de <html>) para heredar las variables del tema activo
           que viven en `body.theme-X { --win-bg: ...; ... }`. */
        .pf-modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.55);
            z-index: 10000;
            display: flex;
            align-items: center;            /* ← centrado vertical */
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        .pf-modal {
            width: 100%;
            max-width: 380px;
            max-height: calc(100vh - 32px);
            display: flex;
            flex-direction: column;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }
        .pf-modal > .title-bar {
            flex-shrink: 0;
            /* Refuerzo: el title-bar de 98.css usa --titlebar-* tokens
               que ya inyecta el tema. Aquí solo aseguramos que respete
               la altura sin colapsar dentro del flex. */
            min-height: 22px;
        }
        .pf-modal > .window-body {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 14px 16px;
            background: var(--win-body-bg, var(--win-bg, silver));
            color: var(--text, #000);
        }
        .pf-modal .pf-review-title {
            font-size: 14px;
            font-weight: bold;
            color: var(--text, #000);
            margin: 0 0 10px;
            word-break: break-word;
        }
        /* Bloque comentario — match al #profile-review-view-comment del escritorio. */
        .pf-modal .pf-review-comment {
            font-size: 12px;
            font-style: italic;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.65;
            color: var(--accent, #000080);
            margin: 0 0 12px;
            max-height: 220px;
            overflow-y: auto;
        }
        .pf-modal .pf-review-comment-empty {
            font-size: 12px;
            font-style: italic;
            color: var(--text-faint, #888);
            margin: 0 0 12px;
        }
        /* Header con firma del usuario — match al #profile-review-view-header. */
        .pf-modal .pf-review-header {
            font-size: 12px;
            font-weight: bold;
            color: var(--accent, #000080);
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pf-modal .pf-review-header .pf-stars { font-size: 16px; }
        .pf-modal .pf-review-header .pf-stars-num {
            font-size: 12px;
            color: var(--accent, #000080);
        }
        .pf-modal .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid var(--bezel-dark-2, grey);
        }
        .pf-modal .modal-actions .button { min-height: 28px; min-width: 70px; }

        /* Context menu (long-press en música): lista vertical de
           botones Win98 con icono a la izquierda. Comparte el chasis
           de .pf-modal — solo añadimos el layout interno. */
        .pf-modal.pf-ctx-menu > .window-body { padding: 10px 12px; }
        .pf-ctx-list { display: flex; flex-direction: column; gap: 6px; }
        .pf-ctx-item {
            display: flex; align-items: center; gap: 12px;
            justify-content: flex-start; text-align: left;
            padding: 0 14px; font-size: 13px;
            min-height: 44px; width: 100%;
        }
        .pf-ctx-item:disabled { opacity: 0.45; cursor: not-allowed; }
        .pf-ctx-icon {
            font-size: 16px; width: 20px; text-align: center;
            flex-shrink: 0; color: var(--accent, #000080);
        }
        .pf-ctx-label { flex: 1; }

        /* Review editor: stars row + textarea. */
        .pf-rev-edit-stars {
            display: flex; align-items: center; gap: 4px;
            margin: 4px 0 12px;
        }
        .pf-rev-edit-star {
            font-size: 28px; line-height: 1; cursor: pointer;
            color: var(--accent, #000080); user-select: none;
            min-width: 32px; text-align: center;
        }
        .pf-rev-edit-star.empty { color: var(--text-faint, #888); }
        .pf-rev-edit-num {
            margin-left: 8px; font-size: 14px; font-weight: bold;
            color: var(--accent, #000080); min-width: 18px;
        }
        .pf-rev-edit-comment {
            width: 100%; min-height: 80px; box-sizing: border-box;
            font-family: inherit; font-size: 12px; padding: 6px 8px;
            resize: vertical;
        }
        .pf-rev-edit-label {
            font-size: 11px; margin: 8px 0 4px; color: var(--text, #000);
        }
        .pf-modal .modal-actions .button.danger {
            margin-right: auto;
        }

        /* Collab dialog: lista de avatares + botones acción. */
        .pf-collab-section-title {
            font-size: 10px; color: var(--text-faint, #666);
            padding: 4px 0 2px; text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pf-collab-row {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 0; min-height: 32px;
        }
        .pf-collab-av {
            width: 28px; height: 28px; flex-shrink: 0;
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #808080),
                inset -1px -1px var(--bezel-light-1, #fff);
            display: flex; align-items: center; justify-content: center;
        }
        .pf-collab-av img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .pf-collab-av-ph {
            font-size: 14px; color: var(--text-faint, #888);
        }
        .pf-collab-label {
            flex: 1; font-size: 12px; color: var(--text, #000);
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .pf-collab-action { min-height: 26px; min-width: 64px; font-size: 11px; }
        .pf-collab-status {
            font-size: 10px; color: var(--text-faint, #666);
            margin-top: 6px; min-height: 12px; font-style: italic;
        }
        .pf-collab-sep {
            border: none; border-top: 1px solid var(--bezel-dark-1, #808080);
            margin: 6px 0;
        }

        /* Item "+" al final de cada lista — clickable, look discreto.
           Pintado por renderActiveList al final de la lista. */
        .pf-item.pf-add-item {
            justify-content: center;
            min-height: 50px;
            cursor: pointer;
            background: transparent;
            border-top: 1px dashed var(--bezel-dark-1, #808080);
            color: var(--text-faint, #666);
            gap: 8px;
            opacity: 0.85;
        }
        .pf-item.pf-add-item:active { opacity: 1; }
        .pf-item.pf-add-item .pf-add-icon {
            font-size: 18px; font-weight: bold;
            color: var(--accent, #000080);
            min-width: 24px; text-align: center;
        }
        .pf-item.pf-add-item .pf-add-label {
            font-size: 12px;
            color: var(--text, #000);
        }

        /* Formularios de modales (Edit profile + Add item). */
        .pf-form-row {
            display: flex; flex-direction: column; gap: 3px;
            margin-bottom: 8px;
        }
        .pf-form-row label {
            font-size: 11px; font-weight: bold;
            color: var(--text, #000);
        }
        .pf-form-row input,
        .pf-form-row textarea,
        .pf-form-row select {
            width: 100%; box-sizing: border-box;
            font-family: inherit; font-size: 12px;
            padding: 4px 6px;
        }
        .pf-form-row textarea { resize: vertical; min-height: 60px; }
        .pf-form-hint {
            font-size: 10px; color: var(--text-faint, #666);
            font-style: italic; margin-top: 2px;
        }
        .pf-form-error {
            font-size: 11px; color: var(--error-text, #c00);
            margin: 4px 0 0; min-height: 14px;
        }
        /* Botones tipo "tile" para selección de tipo (música song/album). */
        .pf-type-row {
            display: flex; gap: 8px; margin: 6px 0 4px;
        }
        .pf-type-btn {
            flex: 1; min-height: 56px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 2px; font-size: 11px;
        }
        .pf-type-btn .pf-type-emoji { font-size: 22px; }
        .pf-shared-from {
            color: var(--accent, #000080);
            font-size: 10px;
            background: var(--input-bg, #fff);
            padding: 1px 6px;
            font-weight: bold;
        }
        .pf-item.has-review:active .pf-shared-from {
            background: var(--accent-text, #fff);
            color: var(--accent, #000080);
        }

        /* ── Hamburguesa + sidebar drawer ── */
        .pf-userbar { position: relative; }
        .pf-hamburger {
            position: absolute; top: 6px; right: 6px;
            width: 28px; height: 28px; min-width: 28px;
            padding: 0; display: flex; align-items: center;
            justify-content: center; font-size: 16px;
            line-height: 1; cursor: pointer; z-index: 2;
        }
        .pf-drawer-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 9000; opacity: 0;
            visibility: hidden; transition: opacity 0.2s ease, visibility 0s linear 0.2s;
        }
        .pf-drawer-backdrop.open {
            opacity: 1; visibility: visible;
            transition: opacity 0.2s ease;
        }
        .pf-drawer {
            position: fixed; top: 0; right: 0;
            width: 80%; max-width: 280px; height: 100%;
            background: var(--win-bg, silver);
            box-shadow: -4px 0 12px rgba(0,0,0,0.4);
            z-index: 9001; display: flex; flex-direction: column;
            transform: translateX(100%); transition: transform 0.2s ease;
            color: var(--text, #000);
        }
        .pf-drawer.open { transform: translateX(0); }
        .pf-drawer-head {
            padding: 8px 10px; font-size: 12px; font-weight: bold;
            background: var(--accent, #000080); color: var(--accent-text, #fff);
            display: flex; align-items: center; justify-content: space-between;
        }
        .pf-drawer-close {
            min-width: 22px; height: 22px; padding: 0;
            font-size: 14px;
        }
        .pf-drawer-body { flex: 1; overflow-y: auto; padding: 8px; }
        .pf-drawer-section { display: flex; flex-direction: column; gap: 4px; }
        .pf-drawer-section + .pf-drawer-section {
            margin-top: 12px; border-top: 1px solid var(--bezel-dark-1, #808080);
            padding-top: 10px;
        }
        .pf-drawer-section-title {
            font-size: 10px; color: var(--text-faint, #666);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin: 0 0 4px 4px;
        }
        .pf-drawer-item {
            display: flex; align-items: center; gap: 10px;
            justify-content: flex-start; text-align: left;
            padding: 0 10px; min-height: 36px;
            font-size: 12px; width: 100%;
        }
        .pf-drawer-item.active {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            font-weight: bold;
        }
        .pf-drawer-icon {
            font-size: 14px; width: 18px; text-align: center; flex-shrink: 0;
        }

        /* ── Vistas ── */
        .pf-view { display: flex; flex-direction: column; flex: 1; min-height: 0; }
        .pf-view[hidden] { display: none !important; }

        /* ── Vista Perfil (edit + posts) ── */
        .pf-profile-actions {
            display: flex; gap: 6px; padding: 8px 0;
            border-bottom: 1px solid var(--bezel-dark-1, #808080);
            margin-bottom: 8px;
        }
        .pf-profile-actions .button { min-height: 28px; font-size: 11px; flex: 1; }
        .pf-posts-list { display: flex; flex-direction: column; gap: 8px; padding: 0 0 8px; }
        .pf-post {
            background: var(--input-bg, #fff);
            padding: 8px 10px;
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #808080),
                inset -1px -1px var(--bezel-light-1, #fff);
        }
        .pf-post-head {
            display: flex; align-items: center; gap: 6px;
            font-size: 11px; margin-bottom: 6px;
        }
        .pf-post-author { font-weight: bold; color: var(--accent, #000080); }
        .pf-post-time { font-size: 10px; color: var(--text-faint, #666); margin-left: auto; }
        .pf-post-text {
            font-size: 12px; line-height: 1.4;
            white-space: pre-wrap; word-break: break-word;
            color: var(--text, #000);
        }
        .pf-post-img {
            display: block; max-width: 100%; margin-top: 6px;
            border: 1px solid var(--bezel-dark-1, #808080);
        }
        .pf-post-foot {
            display: flex; align-items: center; gap: 4px;
            margin-top: 6px; padding-top: 6px;
            border-top: 1px solid var(--bezel-dark-1, #808080);
        }
        .pf-post-btn {
            min-height: 22px; min-width: 36px;
            font-size: 11px; padding: 0 6px;
        }
        .pf-post-btn.is-liked { color: var(--accent, #000080); font-weight: bold; }
        .pf-post-btn.danger { color: var(--error-text, #c00); margin-left: auto; }
        .pf-post-comments {
            margin-top: 6px; padding-top: 6px;
            border-top: 1px solid var(--bezel-dark-1, #808080);
        }
        .pf-post-comments[hidden] { display: none; }
        .pf-comment {
            display: flex; gap: 6px;
            padding: 4px 0; font-size: 11px;
        }
        .pf-comment-author { font-weight: bold; color: var(--accent, #000080); }
        .pf-comment-text   { flex: 1; word-break: break-word; }
        .pf-comment-del {
            min-width: 18px; min-height: 18px; padding: 0;
            font-size: 10px; color: var(--error-text, #c00);
        }
        .pf-comment-form {
            display: flex; gap: 4px; margin-top: 4px;
        }
        .pf-comment-form input {
            flex: 1; font-size: 11px; padding: 3px 6px;
        }
        .pf-comment-form button { font-size: 11px; min-height: 22px; }
        .pf-new-post {
            display: flex; flex-direction: column; gap: 4px;
            padding: 8px; margin-bottom: 10px;
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #808080),
                inset -1px -1px var(--bezel-light-1, #fff);
        }
        /* El atributo HTML `hidden` lo pisaría `display:flex` de arriba —
           lo forzamos para que toggleProfileActions(true) realmente oculte
           el form cuando estoy viendo a otro usuario. */
        .pf-new-post[hidden] { display: none !important; }
        #pf-edit-profile-btn[hidden],
        #pf-follow-btn[hidden],
        #pf-chat-btn[hidden] { display: none !important; }
        .pf-new-post textarea {
            width: 100%; box-sizing: border-box;
            font-family: inherit; font-size: 12px;
            resize: vertical; min-height: 50px;
        }
        .pf-new-post input { font-size: 11px; padding: 3px 6px; }
        .pf-new-post-actions {
            display: flex; justify-content: flex-end; gap: 4px;
        }

        /* ── Vista Social ── */
        .pf-social-section-title {
            font-size: 11px; font-weight: bold;
            color: var(--text, #000); margin: 4px 0 6px;
            padding-bottom: 4px; border-bottom: 1px solid var(--bezel-dark-1, #808080);
        }
        .pf-friends-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 8px; padding: 0 0 8px;
        }
        .pf-friend-card {
            display: flex; flex-direction: column;
            align-items: center; gap: 4px;
            padding: 8px 4px; cursor: pointer;
            background: var(--win-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #808080),
                inset  1px  1px var(--bezel-light-1, #fff);
        }
        .pf-friend-card:active {
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #808080),
                inset -1px -1px var(--bezel-light-1, #fff);
        }
        .pf-friend-card.is-explore {
            border: 1px dashed var(--bezel-dark-1, #808080);
            color: var(--text-faint, #666); background: transparent;
            box-shadow: none;
        }
        .pf-friend-av {
            width: 48px; height: 48px; flex-shrink: 0;
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-1, #808080),
                inset -1px -1px var(--bezel-light-1, #fff);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .pf-friend-av img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .pf-friend-av-plus {
            font-size: 32px; color: var(--accent, #000080); line-height: 1;
        }
        .pf-friend-label {
            font-size: 10px; text-align: center;
            line-height: 1.2; word-break: break-word;
            max-width: 100%; overflow: hidden;
        }
        /* Badge de chat — esquina inferior derecha del avatar. Solo
           aparece para amigos con seguimiento mutuo. */
        .pf-friend-av { position: relative; }
        .pf-friend-chat {
            position: absolute;
            bottom: -4px; right: -4px;
            width: 22px; height: 22px; min-width: 22px; min-height: 22px;
            padding: 0; font-size: 11px; line-height: 1;
            border-radius: 50%;
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.4);
            z-index: 1;
        }
        .pf-friend-chat:active { transform: scale(0.92); }

        /* .pf-friend-av tiene overflow:hidden para clipear la imagen
           interna. El dot vive FUERA (bottom/right -2px), así que el
           contenedor con dot necesita overflow:visible. */
        .pf-friend-av.has-presence-dot { overflow: visible !important; }

        /* Punto de presencia online/offline — esquina inferior derecha.
           Cuando hay chat-badge en la misma esquina, lo movemos arriba
           izquierda para no colisionar. */
        .pf-presence-dot {
            position: absolute;
            bottom: -2px; right: -2px;
            width: 12px; height: 12px;
            border-radius: 50%;
            background: #888;
            box-shadow: 0 0 0 2px var(--win-bg, silver),
                        inset -1px -1px 0 rgba(0,0,0,0.35);
            z-index: 2;
        }
        .pf-friend-av .pf-friend-chat + .pf-presence-dot {
            bottom: auto; right: auto;
            top: -2px; left: -2px;
        }
        .pf-presence-dot.online {
            background: #2ecc71;
            /* alternate elimina el "stop" en el rebote 50%→100% del patrón
               keyframes-en-3-puntos: con un único par from→to y direction
               alternate el ciclo es continuo en valor y en velocidad. */
            animation: pfPresencePulse 1.2s ease-in-out infinite alternate;
        }
        @keyframes pfPresencePulse {
            from {
                box-shadow: 0 0 0 2px var(--win-bg, silver),
                            0 0 3px rgba(46,204,113,0.5);
                opacity: 0.85;
            }
            to {
                box-shadow: 0 0 0 2px var(--win-bg, silver),
                            0 0 12px rgba(46,204,113,1);
                opacity: 1;
            }
        }

        /* ── Badges de mensajes sin leer ──
           Pintamos un círculo rojo con el contador (1-9, o "9+" si excede).
           Vive sobre el avatar del amigo en Social (esquina superior derecha)
           y sobre el botón hamburguesa (esquina superior derecha) cuando hay
           cualquier chat sin leer. */
        .pf-unread-badge {
            position: absolute; top: -4px; right: -4px;
            min-width: 18px; height: 18px; padding: 0 4px;
            border-radius: 9px;
            background: #c0392b; color: #fff;
            font-size: 10px; font-weight: bold;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.4);
            z-index: 2;
        }
        /* En la hamburguesa lo desplazamos un poco más fuera para que
           no se confunda con el icono ☰. La regla base .pf-hamburger
           ya tiene position:absolute, no hace falta repetirla. */
        .pf-hamburger .pf-unread-badge { top: -6px; right: -6px; }

        /* ── Chat modal (bottom-sheet de pantalla casi completa) ── */
        .pf-chat-modal {
            width: 100%; max-width: 460px;
            height: calc(100vh - 32px);
            max-height: calc(100vh - 32px);
            display: flex; flex-direction: column;
        }
        .pf-chat-body {
            flex: 1; display: flex; flex-direction: column;
            min-height: 0; padding: 0;
            background: var(--input-bg, #fff);
        }
        .pf-chat-messages {
            flex: 1; overflow-y: auto;
            padding: 10px; display: flex; flex-direction: column;
            gap: 6px; -webkit-overflow-scrolling: touch;
        }
        .pf-chat-empty {
            text-align: center; color: var(--text-faint, #888);
            font-size: 11px; padding: 20px 10px; font-style: italic;
        }
        .pf-chat-msg { display: flex; flex-direction: column; max-width: 75%; }
        .pf-chat-msg.mine   { align-self: flex-end;   align-items: flex-end; }
        .pf-chat-msg.theirs { align-self: flex-start; align-items: flex-start; }
        .pf-chat-bubble {
            padding: 6px 10px; font-size: 12px;
            line-height: 1.35; word-break: break-word;
            border: 1px solid var(--bezel-dark-1, #808080);
        }
        .pf-chat-msg.mine .pf-chat-bubble {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
        }
        .pf-chat-msg.theirs .pf-chat-bubble {
            background: var(--win-bg, silver);
            color: var(--text, #000);
        }
        .pf-chat-time {
            font-size: 9px; color: var(--text-faint, #888);
            margin-top: 1px;
        }
        .pf-chat-input-row {
            display: flex; gap: 4px; padding: 6px 8px;
            border-top: 1px solid var(--bezel-dark-1, #808080);
            background: var(--win-bg, silver);
        }
        .pf-chat-input-row input {
            flex: 1; font-family: inherit; font-size: 12px;
            padding: 4px 6px;
        }
        .pf-chat-input-row button { min-height: 26px; font-size: 11px; }

        /* ── Viewing-user banner ── */
        .pf-viewing-banner {
            display: none; padding: 6px 8px;
            background: var(--accent, #000080); color: var(--accent-text, #fff);
            font-size: 11px;
            align-items: center; justify-content: space-between; gap: 8px;
        }
        .pf-viewing-banner.is-active { display: flex; }
        .pf-viewing-back {
            min-height: 22px; padding: 0 8px;
            font-size: 10px;
        }
    </style>
</head>
<body class="mh-body <?= htmlspecialchars($activeThemeClass) ?>">

<div class="window mh-window" id="perfilWindow">
    <div class="title-bar">
        <div class="title-bar-text"><img src="../../assets/img/appIcons/profileIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:4px;">Perfil - <?= htmlspecialchars($userLabel) ?></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize" disabled></button>
            <button aria-label="Close" onclick="window.location.href='../../mobile.php';"></button>
        </div>
    </div>
    <div class="window-body">

        <!-- Banner cuando estoy viendo el perfil de otro usuario. -->
        <div class="pf-viewing-banner" id="pf-viewing-banner">
            <span id="pf-viewing-text">Viendo perfil de …</span>
            <button class="button pf-viewing-back" id="pf-viewing-back" type="button">← Mi perfil</button>
        </div>

        <!-- Header: izquierda avatar + cuadro de conexiones, derecha texto -->
        <div class="mh-userbar pf-userbar">
            <button class="button pf-hamburger" id="pf-hamburger" type="button" aria-label="Menú">☰</button>
            <div class="pf-userbar-left">
                <div class="mh-userbar-avatar" id="pf-userbar-avatar">
                    <?php if ($userImg): ?>
                        <img src="<?= htmlspecialchars($userImg) ?>" alt="">
                    <?php else: ?>
                        <span>👤</span>
                    <?php endif; ?>
                </div>
                <!-- Cuadrado de conexiones: rejilla 2×2 de iconos sociales,
                     panel hundido Win98 para que destaque bajo el avatar. -->
                <div class="pf-about-socials" id="pf-about-socials"></div>
            </div>
            <div class="mh-userbar-text">
                <div class="mh-userbar-greeting" id="pf-userbar-greeting">Perfil de</div>
                <div class="mh-userbar-name" id="pf-userbar-name"><?= htmlspecialchars($userLabel) ?></div>
                <!-- Bio = "descripción" del escritorio. Si está vacía mostramos
                     "Sin descripción" como placeholder en gris. -->
                <div class="pf-about-bio" id="pf-about-bio">Sin descripción</div>
                <div class="pf-about-meta" id="pf-about-meta"></div>
            </div>
        </div>

        <!-- VISTA: Perfil (edit + posts) -->
        <div class="pf-view" id="pf-view-profile">
            <div class="pf-profile-actions" id="pf-profile-actions">
                <button class="button" id="pf-edit-profile-btn" type="button">✏ Editar perfil</button>
                <button class="button" id="pf-follow-btn" type="button" hidden>+ Seguir</button>
                <button class="button" id="pf-chat-btn" type="button" hidden>💬 Chat</button>
            </div>
            <div class="pf-new-post" id="pf-new-post">
                <textarea id="pf-post-text" maxlength="1000" placeholder="Escribe algo..."></textarea>
                <input type="text" id="pf-post-img" maxlength="2000" placeholder="URL de imagen (opcional)">
                <div class="pf-new-post-actions">
                    <button class="button default" id="pf-post-publish" type="button">Publicar</button>
                </div>
            </div>
            <div class="pf-posts-list" id="pf-posts-list"></div>
        </div>

        <!-- VISTA: Mis listas (tabs + sub-tabs + destacados + lista) -->
        <div class="pf-view" id="pf-view-lists" hidden>
            <!-- Pestañas categoría (scroll horizontal) -->
            <nav class="pf-tabs" id="pf-tabs">
                <button class="pf-tab active" data-cat="movies"><img src="../../assets/img/appIcons/pelisIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Películas <span class="pf-tab-count" id="cnt-movies">·</span></button>
                <button class="pf-tab" data-cat="series"><img src="../../assets/img/appIcons/melonArchiveIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Series <span class="pf-tab-count" id="cnt-series">·</span></button>
                <button class="pf-tab" data-cat="books">📖 Libros <span class="pf-tab-count" id="cnt-books">·</span></button>
                <button class="pf-tab" data-cat="games"><img src="../../assets/img/appIcons/juegosIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Juegos <span class="pf-tab-count" id="cnt-games">·</span></button>
                <button class="pf-tab" data-cat="music"><img src="../../assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Música <span class="pf-tab-count" id="cnt-music">·</span></button>
            </nav>

            <!-- Sub-toggle de música: álbumes vs canciones. Solo visible
                 cuando la pestaña activa es 'music'. -->
            <nav class="pf-subtabs" id="pf-music-subtabs" hidden>
                <button class="pf-subtab active" data-mtab="albums" type="button">💿 Álbumes</button>
                <button class="pf-subtab" data-mtab="songs" type="button"><img src="../../assets/img/appIcons/songIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Canciones</button>
            </nav>

            <!-- Sub-toggle de estado: pendientes / en curso / completadas.
                 Solo visible cuando la pestaña activa NO es 'music'. -->
            <nav class="pf-subtabs" id="pf-status-subtabs" hidden>
                <button class="pf-subtab active" data-st="pending"     type="button">○ Pendientes</button>
                <button class="pf-subtab"        data-st="in-progress" type="button">◑ En curso</button>
                <button class="pf-subtab"        data-st="completed"   type="button">● Completadas</button>
            </nav>

            <!-- Destacados: hasta 3 items con `featured`. Solo música. -->
            <div class="pf-destacados" id="pf-destacados" hidden>
                <div class="pf-destacados-title">★ Destacados</div>
                <div class="pf-destacados-slots" id="pf-destacados-slots"></div>
            </div>

            <!-- Lista de items dentro de un panel hundido -->
            <div class="mh-panel pf-list" id="pf-list">
                <div class="mh-empty"><span class="mh-empty-icon">⏳</span>Cargando…</div>
            </div>
        </div>

        <!-- VISTA: Social (amigos + explorar) -->
        <div class="pf-view" id="pf-view-social" hidden>
            <div class="pf-social-section-title">👥 Amigos</div>
            <div class="pf-friends-grid" id="pf-friends-grid"></div>
        </div>

        <!-- Sidebar drawer -->
        <div class="pf-drawer-backdrop" id="pf-drawer-backdrop"></div>
        <aside class="pf-drawer" id="pf-drawer">
            <div class="pf-drawer-head">
                <span id="pf-drawer-title">Menú</span>
                <button class="button pf-drawer-close" id="pf-drawer-close" type="button">×</button>
            </div>
            <div class="pf-drawer-body" id="pf-drawer-body">
                <!-- secciones inyectadas por JS según contexto -->
            </div>
        </aside>

        <!-- Status bar Win98 al pie -->
        <div class="mh-statusbar">
            <a href="../../mobile.php">‹ Menú</a>
        </div>

    </div>
</div>

<script>
/* ─── Estado y carga de datos ──────────────────────────────────── */
var API = '../../assets/profile/api.php';
var COUPLE_API = '../../assets/couple/api.php';
var STATE = {
    lists: null, profile: null,
    current: 'movies', musicView: 'albums', statusView: 'pending',
    /* view: 'profile' | 'lists' | 'social' — qué pintamos abajo del userbar.
       viewingUser: null cuando soy yo; un userKey cuando estoy en perfil ajeno. */
    view: 'profile',
    viewingUser: null,
    viewingLabel: null,
    myFollowing: [],       /* lista de userKeys a los que sigo (para Social) */
    myFollowers: [],       /* lista de userKeys que me siguen (para chat mutuo) */
    unreadChats: {}        /* { userKey: unreadCount } — actualizado por polling */
};
var USER_LABEL = <?= json_encode($userLabel) ?>;
var PAREJA_ID  = <?= (int)$parejaId ?>;
var USER_KEY   = <?= json_encode($userKey) ?>;
/* Mapa { userKey: { label, image } } igual que el desktop, para el
   diálogo de colaboradores (avatares + listado de invitables). El
   `../../` viene de que perfil-mobile.php vive en /apps/ y getUserImage()
   devuelve la ruta relativa a la raíz del proyecto. */
var PROFILE_USERS = <?php
    $udata = [];
    foreach ($loginUsers as $k => $u) {
        $img = getUserImage($u['label']);
        $udata[$k] = [
            'label' => $u['label'],
            'image' => $img !== '' ? '../../' . $img : ''
        ];
    }
    echo json_encode($udata);
?>;

function fetchProfile() {
    return fetch(API + '?action=get-profile', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            STATE.profile = d || {};
            /* get-profile devuelve following → cache para vista Social. */
            STATE.myFollowing = (STATE.profile.following || []).slice();
            renderAbout(STATE.profile);
            /* Si estamos en vista Perfil (mía), re-render de posts. */
            if (!STATE.viewingUser && STATE.view === 'profile') {
                renderPosts(STATE.profile.posts || []);
            }
        })
        .catch(function(){
            var bioEl = document.getElementById('pf-about-bio');
            if (bioEl) { bioEl.textContent = '(error cargando perfil)'; bioEl.classList.add('is-empty'); }
        });
}

/* Carga mis followers (los que me siguen). Necesario para isMutual y
   para decidir si pintar el botón Chat. */
function fetchFollowers() {
    return fetch(API + '?action=get-followers', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && d.ok && Array.isArray(d.followers)) {
                STATE.myFollowers = d.followers.slice();
            }
        })
        .catch(function(){});
}

/* Pinta la sección "About" con bio, meta y conexiones — mismo
   conjunto de campos que `renderProfileInfo` del escritorio. */
function renderAbout(d) {
    var bioEl     = document.getElementById('pf-about-bio');
    var metaEl    = document.getElementById('pf-about-meta');
    var socialsEl = document.getElementById('pf-about-socials');
    if (!bioEl || !metaEl || !socialsEl) return;

    /* Bio = "descripción". Si está vacía deja "Sin descripción" como
       placeholder gris (idéntico al estado anterior de pf-cover-quote). */
    if (d && d.bio) {
        bioEl.textContent = '" ' + d.bio + ' "';
        bioEl.classList.remove('is-empty');
    } else {
        bioEl.textContent = 'Sin descripción';
        bioEl.classList.add('is-empty');
    }

    /* Meta: pronombres · edad · país */
    var parts = [];
    if (d && d.pronouns) parts.push(esc(d.pronouns));
    if (d && d.age)      parts.push(esc(d.age) + ' años');
    if (d && d.country)  parts.push('📍 ' + esc(d.country));
    metaEl.innerHTML = parts.length
        ? parts.join('<span class="pf-meta-sep">·</span>')
        : '';

    /* Conexiones — replica el array `socials` del escritorio
       (perfil.php:1551). Si el valor no es URL completa, construye una.
       Discord no tiene URL pública → tap copia el nombre al portapapeles. */
    var socials = [
        { key: 'steam',     icon: '🎮', label: 'Steam',
          url: function(v){ return /^https?:\/\//.test(v) ? v : 'https://steamcommunity.com/id/' + encodeURIComponent(v); } },
        { key: 'discord',   icon: '💬', label: 'Discord',   url: null },
        { key: 'twitter',   icon: '🐦', label: 'Twitter',
          url: function(v){ return /^https?:\/\//.test(v) ? v : 'https://x.com/' + encodeURIComponent(v.replace(/^@/, '')); } },
        { key: 'instagram', icon: '📷', label: 'Instagram',
          url: function(v){ return /^https?:\/\//.test(v) ? v : 'https://instagram.com/' + encodeURIComponent(v.replace(/^@/, '')); } }
    ];
    socialsEl.innerHTML = '';
    socials.forEach(function(s){
        var v = d && d[s.key];
        if (!v) return;
        var a = document.createElement('a');
        a.className = 'pf-about-social';
        a.textContent = s.icon;
        if (s.url) {
            a.href   = s.url(v);
            a.target = '_blank';
            a.rel    = 'noopener noreferrer';
            a.title  = s.label + ': ' + v;
        } else {
            a.href  = '#';
            a.title = s.label + ' (tap para copiar): ' + v;
            a.addEventListener('click', function(e){
                e.preventDefault();
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(v).then(function(){
                        a.style.outline = '1px dotted var(--accent, #000080)';
                        setTimeout(function(){ a.style.outline = ''; }, 700);
                    }).catch(function(){});
                }
            });
        }
        socialsEl.appendChild(a);
    });
}

function fetchLists() {
    return fetch(API + '?action=get-lists', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            STATE.lists = d || {};
            ['movies','series','books','games','music'].forEach(function(cat){
                var arr = STATE.lists[cat] || [];
                document.getElementById('cnt-' + cat).textContent = arr.length;
            });
            renderActiveList();
        })
        .catch(function(){
            document.getElementById('pf-list').innerHTML =
                '<div class="mh-empty"><span class="mh-empty-icon">⚠️</span>Error cargando listas</div>';
        });
}

/* ─── Render ───────────────────────────────────────────────────── */
/* Normaliza status a uno de los 3 canónicos del filtro: pending /
   in-progress / completed. Los valores legacy del DB (doing/done) y
   cualquier otro (dropped, '') caen en pending por defecto excepto los
   mapeos directos. */
function normalizeStatus(s) {
    if (s === 'doing' || s === 'in-progress') return 'in-progress';
    if (s === 'done'  || s === 'completed')   return 'completed';
    return 'pending';
}
/* makeStarsHtml: replica el render exacto del escritorio (perfil.php) —
   estrellas llenas, medias estrellas con clip-path:inset(0 50% 0 0) y
   estrellas vacías. Devuelve HTML para insertar dentro de .pf-stars. */
function makeStarsHtml(val, total) {
    total = total || 5;
    var h = '';
    for (var i = 1; i <= total; i++) {
        if (val >= i) {
            h += '<span>★</span>';
        } else if (val >= i - 0.5) {
            h += '<span style="clip-path:inset(0 50% 0 0);">★</span>';
        } else {
            h += '<span>☆</span>';
        }
    }
    return h;
}
function esc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderActiveList() {
    var listEl = document.getElementById('pf-list');
    var musicSubtabsEl  = document.getElementById('pf-music-subtabs');
    var statusSubtabsEl = document.getElementById('pf-status-subtabs');
    var isMusicCat = (STATE.current === 'music');
    var viewingOther = !!STATE.viewingUser;
    /* Cuando estoy viendo a otro usuario, los sub-tabs de status no
       aplican (mostramos solo reseñas). Los de música sí — el usuario
       quiere ver sus álbumes vs canciones reseñadas. */
    if (musicSubtabsEl)  musicSubtabsEl.hidden  = !isMusicCat;
    if (statusSubtabsEl) statusSubtabsEl.hidden = isMusicCat || viewingOther;
    /* Destacados también solo en música. */
    renderDestacados();

    var rawArr = (STATE.lists && STATE.lists[STATE.current]) || [];
    /* Mantenemos pares {item, origIdx} para que el filtro de música no
       rompa el lookup del modal de reseña (que indexa STATE.lists[cat]
       por la posición original). */
    var entries = rawArr.map(function(it, i){ return { it: it, origIdx: i }; });
    if (isMusicCat) {
        var wantType = STATE.musicView === 'songs' ? 'song' : 'album';
        entries = entries.filter(function(e){ return e.it && e.it.type === wantType; });
        if (viewingOther) {
            entries = entries.filter(function(e){ return e.it.review && e.it.review.stars; });
        }
    } else if (viewingOther) {
        /* En perfil ajeno mostramos solo las reseñas (no aplica status). */
        entries = entries.filter(function(e){ return e.it && e.it.review && e.it.review.stars; });
    } else {
        /* Filtro de estado para no-música. Normalizamos legacy
           (doing/done) → canónico (in-progress/completed). dropped
           se cuela en 'pending' para no esconderlo del usuario. */
        var wantSt = STATE.statusView;
        entries = entries.filter(function(e){ return e.it && normalizeStatus(e.it.status) === wantSt; });
    }
    var html = '';
    if (!entries.length) {
        var emoji = STATE.current === 'movies' ? '<img src="../../assets/img/appIcons/pelisIcon.png" alt="" style="width:32px;height:32px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">'
                  : STATE.current === 'series' ? '📺'
                  : STATE.current === 'books'  ? '📖'
                  : STATE.current === 'games'  ? '🎮'
                  : (STATE.musicView === 'songs' ? '🎵' : '💿');
        var emptyMsg;
        if (viewingOther) {
            emptyMsg = 'Sin reseñas';
        } else if (isMusicCat) {
            emptyMsg = STATE.musicView === 'songs' ? 'Sin canciones' : 'Sin álbumes';
        } else {
            var stWord = STATE.statusView === 'in-progress' ? 'en curso'
                      :  STATE.statusView === 'completed'   ? 'completados'
                      :  'pendientes';
            var catWord = STATE.current === 'movies' ? 'películas'
                       :  STATE.current === 'series' ? 'series'
                       :  STATE.current === 'books'  ? 'libros'
                       :  'juegos';
            emptyMsg = 'Sin ' + catWord + ' ' + stWord;
        }
        html = '<div class="mh-empty"><span class="mh-empty-icon">' + emoji + '</span>' + emptyMsg + '</div>';
    }
    entries.forEach(function(entry){
        var it  = entry.it;
        var idx = entry.origIdx;       /* índice en STATE.lists[cat] sin filtrar */
        var isMusic = (STATE.current === 'music');
        var posterHtml = it.image
            ? '<img class="pf-item-poster" src="' + esc(it.image) + '" alt="" loading="lazy">'
            : '<div class="pf-item-poster placeholder">' +
              (isMusic ? '🎵' : STATE.current === 'movies' ? '<img src="../../assets/img/appIcons/pelisIcon.png" alt="" style="width:32px;height:32px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">'
                : STATE.current === 'series' ? '📺'
                : STATE.current === 'books'  ? '📖' : '🎮') +
              '</div>';
        var subParts = [];
        if (isMusic && it.artist) subParts.push('<span>' + esc(it.artist) + '</span>');
        /* La etiqueta de estado por item se eliminó — los sub-tabs de
           estado ya filtran por pendiente/en curso/completado, así que
           el badge era redundante. */
        var hasReview = !!(it.review && it.review.stars);
        if (hasReview) {
            subParts.push(
                '<span class="pf-stars-wrap">' +
                    '<span class="pf-stars">' + makeStarsHtml(it.review.stars) + '</span>' +
                    '<span class="pf-stars-num">' + esc(it.review.stars) + '</span>' +
                '</span>'
            );
        }
        if (it.sharedFrom) {
            subParts.push('<span class="pf-shared-from">compartido</span>');
        }
        /* Toda la fila es el target táctil cuando hay reseña — el click
           delegado lee data-cat/data-idx para abrir el modal. En música
           emitimos data-cat/data-idx SIEMPRE (con o sin reseña) para que
           el long-press pueda resolver la fila a un item sin contar
           posiciones DOM. */
        var classes = 'pf-item' + (isMusic ? ' music' : '') + (hasReview ? ' has-review' : '');
        /* data-cat/data-idx SIEMPRE en items reales (no en el row "+")
           para que el long-press y el click de reseña los resuelvan. */
        var dataAttrs = ' data-cat="' + esc(STATE.current) + '" data-idx="' + idx + '"';
        if (hasReview) dataAttrs += ' role="button" tabindex="0"';
        html += '<div class="' + classes + '"' + dataAttrs + '>' +
                  posterHtml +
                  '<div class="pf-item-info">' +
                    '<div class="pf-item-title">' + esc(it.title) + '</div>' +
                    (subParts.length ? '<div class="pf-item-sub">' + subParts.join('') + '</div>' : '') +
                  '</div>' +
                '</div>';
    });
    /* Row "+" final — solo en mi propio perfil. Cuando veo a otro
       usuario no tiene sentido añadir items a su lista. */
    if (!viewingOther) {
        var addLabel = isMusicCat
            ? (STATE.musicView === 'songs' ? 'Añadir canción' : 'Añadir álbum')
            : (STATE.current === 'movies' ? 'Añadir película'
             : STATE.current === 'series' ? 'Añadir serie'
             : STATE.current === 'books'  ? 'Añadir libro'
             : 'Añadir juego');
        html += '<div class="pf-item pf-add-item" data-act="add-item" role="button" tabindex="0">' +
                  '<span class="pf-add-icon">+</span>' +
                  '<span class="pf-add-label">' + esc(addLabel) + '</span>' +
                '</div>';
    }
    listEl.innerHTML = html;
}

/* Render de la sección Destacados (música). Hasta 3 slots; los vacíos
   son `—`. Mismo concepto que renderMusicDestacados del escritorio. */
function renderDestacados() {
    var box     = document.getElementById('pf-destacados');
    var slotsEl = document.getElementById('pf-destacados-slots');
    if (!box || !slotsEl) return;
    var isMusicCat = (STATE.current === 'music');
    box.hidden = !isMusicCat;
    if (!isMusicCat) return;

    var music = (STATE.lists && STATE.lists.music) || [];
    /* Preservamos los índices originales en STATE.lists.music para que
       el lookup del modal de reseña funcione. */
    var featured = [];
    music.forEach(function(it, origIdx){
        if (it && it.featured) featured.push({ it: it, origIdx: origIdx });
    });

    var html = '';
    for (var s = 0; s < 3; s++) {
        var entry = featured[s] || null;
        if (!entry) {
            html += '<div class="pf-dest-slot empty">—</div>';
            continue;
        }
        var it = entry.it;
        var imgHtml = it.image
            ? '<img class="pf-dest-cover" src="' + esc(it.image) + '" alt="" loading="lazy">'
            : '<div class="pf-dest-cover pf-dest-cover-ph">' + (it.type === 'album' ? '💿' : '🎵') + '</div>';
        var hasReview = !!(it.review && it.review.stars);
        var starsHtml = hasReview
            ? '<div class="pf-dest-stars">' +
                '<span class="pf-stars">' + makeStarsHtml(it.review.stars) + '</span>' +
                '<span class="pf-dest-stars-num">' + esc(it.review.stars) + '</span>' +
              '</div>'
            : '';
        /* data-cat/data-idx SIEMPRE en slots no vacíos: lo necesita
           tanto el click de reseña (cuando hay) como el long-press. */
        var classes = 'pf-dest-slot' + (hasReview ? ' has-review' : '');
        var dataAttrs = ' data-cat="music" data-idx="' + entry.origIdx + '"';
        if (hasReview) dataAttrs += ' role="button" tabindex="0" aria-label="Ver reseña"';
        html += '<div class="' + classes + '"' + dataAttrs + '>' +
                  imgHtml +
                  '<div class="pf-dest-title">' + esc(it.title) + '</div>' +
                  (it.artist ? '<div class="pf-dest-artist">' + esc(it.artist) + '</div>' : '') +
                  starsHtml +
                '</div>';
    }
    slotsEl.innerHTML = html;
}

/* ─── Tab switching ────────────────────────────────────────────── */
document.getElementById('pf-tabs').addEventListener('click', function(e){
    var btn = e.target.closest('.pf-tab');
    if (!btn) return;
    var cat = btn.dataset.cat;
    if (cat === STATE.current) return;
    STATE.current = cat;
    document.querySelectorAll('.pf-tab').forEach(function(b){
        b.classList.toggle('active', b.dataset.cat === cat);
    });
    renderActiveList();
});

/* Sub-tabs de música — álbumes vs canciones. Scope al contenedor para
   no interferir con los sub-tabs de estado. */
document.getElementById('pf-music-subtabs').addEventListener('click', function(e){
    var btn = e.target.closest('.pf-subtab');
    if (!btn) return;
    var mtab = btn.dataset.mtab;
    if (mtab === STATE.musicView) return;
    STATE.musicView = mtab;
    this.querySelectorAll('.pf-subtab').forEach(function(b){
        b.classList.toggle('active', b.dataset.mtab === mtab);
    });
    renderActiveList();
});

/* Sub-tabs de estado — pendientes / en curso / completadas.
   Solo aplica a categorías no-música. */
document.getElementById('pf-status-subtabs').addEventListener('click', function(e){
    var btn = e.target.closest('.pf-subtab');
    if (!btn) return;
    var st = btn.dataset.st;
    if (st === STATE.statusView) return;
    STATE.statusView = st;
    this.querySelectorAll('.pf-subtab').forEach(function(b){
        b.classList.toggle('active', b.dataset.st === st);
    });
    renderActiveList();
});

/* ─── Review modal — replica showReviewView del escritorio ──────
   Bottom-sheet con look Win98 que muestra el comentario en cursiva,
   las estrellas exactas (con media estrella) y la firma del usuario.
   No edita: es solo lectura, igual que en el escritorio. */
function openReviewView(item) {
    var review = item.review || {};
    var commentHtml = (review.comment && review.comment.trim())
        ? '<div class="pf-review-comment">" ' + esc(review.comment) + ' "</div>'
        : '<div class="pf-review-comment-empty">(Sin comentario)</div>';
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">★ Reseña</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-review-title">' + esc(item.title || '') + '</div>' +
                commentHtml +
                '<div class="pf-review-header">' +
                    '— ' + esc(USER_LABEL) + ' &nbsp;—&nbsp; ' +
                    '<span class="pf-stars">' + makeStarsHtml(review.stars) + '</span>' +
                    '<span class="pf-stars-num">' + esc(review.stars) + '</span>' +
                '</div>' +
                '<div class="modal-actions">' +
                    '<button class="button default" data-act="close" type="button">Cerrar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    /* Mount en <body> (NO en documentElement): el tema del usuario
       vive como `body.theme-X { --win-bg: ...; --accent: ...; }`, así
       que cualquier elemento debe ser descendiente de <body> para
       heredar las variables. */
    document.body.appendChild(bd);
    function close() { if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('[data-act="close"]').addEventListener('click', close);
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
}

/* ─── Long-press menu (música) ───────────────────────────────────
   Mantener pulsada una canción / álbum / slot destacado abre un menú
   bottom-sheet con dos acciones: Reproducir y Destacar / Quitar de
   destacados. El estado del long-press se publica en `lpFired` para
   que el click delegado de reseña pueda suprimirse durante una ventana
   corta y no se abra el modal en el mismo gesto. */

var LP_MS_PF   = 500;    /* duración para que se considere long-press */
var LP_MOVE_PF = 12;     /* px máx. antes de cancelarlo */
var lpFired    = false;  /* flag durante la ventana de "ya disparó" */

/* Generic helper para abrir un bottom-sheet de acciones con N items.
   Cada item: { act, icon, label, danger?, disabled? }. onPick(act) se
   llama tras cerrar el modal cuando el usuario tapea uno. */
function openCtxMenu(title, items, onPick) {
    var listHtml = items.map(function(it){
        return '<button class="button pf-ctx-item" data-act="' + esc(it.act) + '" type="button"' +
                (it.disabled ? ' disabled' : '') + '>' +
                  '<span class="pf-ctx-icon">' + (it.icon || '') + '</span>' +
                  '<span class="pf-ctx-label">' + esc(it.label) + '</span>' +
                '</button>';
    }).join('');
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal pf-ctx-menu">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">' + esc(title) + '</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-ctx-list">' + listHtml + '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
    items.forEach(function(it){
        if (it.disabled) return;
        var btn = bd.querySelector('[data-act="' + it.act + '"]');
        if (!btn) return;
        btn.addEventListener('click', function(){ close(); onPick(it.act); });
    });
}

function showActionMenu(item, origIdx) {
    var cat = STATE.current;
    var isCollab = !!item.sharedFrom;
    var title = item.title || '—';

    /* Viendo a otro usuario → menú restringido (no puedo modificar sus
       items). Solo ofrezco copiarlo a mi perfil; en música además play. */
    if (STATE.viewingUser) {
        var items = [];
        if (cat === 'music') items.push({ act: 'play', icon: '▶', label: 'Reproducir' });
        items.push({ act: 'copy', icon: '➕', label: 'Añadir a mi perfil' });
        openCtxMenu(title, items, function(act){
            if (act === 'play') playMusicItem(item);
            if (act === 'copy') addItemToOwnProfile(cat, item);
        });
        return;
    }

    if (cat === 'music') {
        showMusicActionMenu(item, origIdx, title, isCollab);
        return;
    }

    /* Categorías con status (movies/series/books/games): menú según
       el estado normalizado del item. */
    var status = normalizeStatus(item.status);
    var lastLabel = isCollab ? 'Abandonar actividad' : 'Eliminar';
    var lastIcon  = isCollab ? '🚪' : '✕';
    var lastAct   = isCollab ? 'leave' : 'delete';
    var items = [];

    if (status === 'pending') {
        items.push({ act: 'inprogress', icon: '▶', label: 'Poner en curso' });
        items.push({ act: 'collab',     icon: '👥', label: 'Colaboradores' });
        items.push({ act: lastAct,      icon: lastIcon, label: lastLabel });
    } else if (status === 'in-progress') {
        items.push({ act: 'complete', icon: '✓', label: 'Completar' });
        items.push({ act: 'unstart',  icon: '✕', label: 'Quitar de en curso' });
        items.push({ act: 'collab',   icon: '👥', label: 'Colaboradores' });
    } else { /* completed */
        var hasReview = !!(item.review && item.review.stars);
        items.push({ act: 'review',  icon: '✏', label: hasReview ? 'Editar reseña' : 'Añadir reseña' });
        items.push({ act: lastAct,   icon: lastIcon, label: lastLabel });
    }

    openCtxMenu(title, items, function(act){
        if (act === 'inprogress') setItemStatus(cat, origIdx, 'in-progress');
        else if (act === 'complete') completeItem(cat, origIdx);
        else if (act === 'unstart')  setItemStatus(cat, origIdx, 'pending');
        else if (act === 'collab')   openCollabDialog(item, origIdx, cat);
        else if (act === 'review')   openReviewEditor(item, origIdx, cat);
        else if (act === 'delete') {
            pfConfirm('¿Eliminar "' + title + '" de tu perfil?', 'Eliminar', function(){
                deleteItem(origIdx, cat);
            });
        } else if (act === 'leave') {
            pfConfirm('¿Abandonar "' + title + '"?', 'Abandonar', function(){
                leaveCollabItem(item, cat);
            });
        }
    });
}

/* Menú largo de música — mantengo la lógica original (no status,
   sino destacados + reseña + colabs + delete). */
function showMusicActionMenu(item, origIdx, title, isCollab) {
    var music = (STATE.lists && STATE.lists.music) || [];
    var featuredCount = 0;
    for (var i = 0; i < music.length; i++) if (music[i] && music[i].featured) featuredCount++;
    var isFeatured = !!item.featured;
    var canFeature = isFeatured || featuredCount < 3;
    var featLabel  = isFeatured ? 'Quitar de destacados' : 'Destacar';
    var hasReview  = !!(item.review && item.review.stars);
    var revLabel   = hasReview ? 'Editar reseña' : 'Añadir reseña';
    var lastLabel  = isCollab ? 'Abandonar actividad' : 'Eliminar';
    var lastIcon   = isCollab ? '🚪' : '✕';
    var lastAct    = isCollab ? 'leave' : 'delete';

    openCtxMenu(title, [
        { act: 'play',     icon: '▶', label: 'Reproducir' },
        { act: 'featured', icon: '★', label: featLabel, disabled: !canFeature },
        { act: 'review',   icon: '✏', label: revLabel },
        { act: 'collab',   icon: '👥', label: 'Colaboradores' },
        { act: lastAct,    icon: lastIcon, label: lastLabel }
    ], function(act){
        if (act === 'play')          playMusicItem(item);
        else if (act === 'featured') toggleFeatured(origIdx);
        else if (act === 'review')   openReviewEditor(item, origIdx, 'music');
        else if (act === 'collab')   openCollabDialog(item, origIdx, 'music');
        else if (act === 'delete') {
            pfConfirm('¿Eliminar "' + title + '" de tu perfil?', 'Eliminar', function(){
                deleteItem(origIdx, 'music');
            });
        } else if (act === 'leave') {
            pfConfirm('¿Abandonar "' + title + '"?', 'Abandonar', function(){
                leaveCollabItem(item, 'music');
            });
        }
    });
}

/* Cambia el status de un item y guarda. Optimistic update. */
function setItemStatus(cat, origIdx, newStatus) {
    var list = STATE.lists && STATE.lists[cat];
    if (!list || !list[origIdx]) return;
    var prev = list[origIdx].status;
    list[origIdx].status = newStatus;
    renderActiveList();
    fetch(API + '?action=save-lists', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category: cat, items: list })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && Array.isArray(d.items)) { STATE.lists[cat] = d.items; renderActiveList(); }
    })
    .catch(function(){
        list[origIdx].status = prev;
        renderActiveList();
    });
}

/* Flow de "Completar": status=completed → prompt reseña → crear momento.
   El momento se crea ANTES de la reseña para que se vea en el calendario
   aunque el usuario cancele la reseña. */
function completeItem(cat, origIdx) {
    var list = STATE.lists && STATE.lists[cat];
    if (!list || !list[origIdx]) return;
    var snapshot = {};
    var src = list[origIdx];
    for (var k in src) if (src.hasOwnProperty(k)) snapshot[k] = src[k];
    setItemStatus(cat, origIdx, 'completed');
    crearMomentoFromItem(cat, snapshot);
    pfReviewPrompt(
        '"' + (snapshot.title || 'Item') + '" completada. ¿Quieres reseñarla?',
        function(){
            /* Re-resolvemos el item por si los IDs cambiaron con el save. */
            var fresh = STATE.lists[cat][origIdx] || snapshot;
            openReviewEditor(fresh, origIdx, cat);
        }
    );
}

/* Prompt "¿Reseñar?" — variante del confirm con copia adaptada. */
function pfReviewPrompt(message, onYes) {
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">¿Añadir reseña?</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-review-comment">' + esc(message) + '</div>' +
                '<div class="modal-actions">' +
                    '<button class="button" data-act="no" type="button">No, gracias</button>' +
                    '<button class="button default" data-act="yes" type="button">★ Sí</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="no"]').addEventListener('click', close);
    bd.querySelector('[data-act="yes"]').addEventListener('click', function(){
        close(); if (typeof onYes === 'function') onYes();
    });
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
}

/* Crea un momento (evento de calendario) para hoy con la info del item.
   Replica `crearMomentoDesdeItem` del escritorio: emoji por categoría,
   título "Verb: Item", descripción con colabs si las hay, y foto opcional. */
function crearMomentoFromItem(cat, item) {
    var emojis = { movies: '🎬', series: '📺', books: '📚', games: '🎮' };
    var verbs  = { movies: 'Vista', series: 'Vista', books: 'Leído', games: 'Jugado' };
    var titulo = (verbs[cat] || 'Completado') + ': ' + (item.title || '');
    var d = new Date();
    var fecha = d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
    var desc = '';
    if (item.collaborators && item.collaborators.length) {
        var labels = item.collaborators.map(function(k){
            return (PROFILE_USERS[k] && PROFILE_USERS[k].label) || k;
        });
        desc = 'Con ' + labels.join(', ');
    } else if (item.sharedFrom && PROFILE_USERS[item.sharedFrom]) {
        desc = 'Con ' + PROFILE_USERS[item.sharedFrom].label;
    }
    fetch(COUPLE_API + '?action=save-momento', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            pareja_id:   PAREJA_ID,
            titulo:      titulo,
            fecha:       fecha,
            descripcion: desc,
            emocion:     emojis[cat] || '😊'
        })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (!d || !d.ok || !d.id || !item.image) return;
        /* Foto del momento — endpoint específico para URLs externas. */
        fetch(COUPLE_API + '?action=save-momento-foto-url', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ momento_id: d.id, foto_url: item.image })
        }).catch(function(){});
    })
    .catch(function(){});
}

/* Confirm modal Win98 (reutilizable). Llama onOk si el usuario acepta. */
function pfConfirm(message, okLabel, onOk) {
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">Confirmar</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-review-comment">' + esc(message) + '</div>' +
                '<div class="modal-actions">' +
                    '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                    '<button class="button default" data-act="ok" type="button">' + esc(okLabel || 'OK') + '</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="cancel"]').addEventListener('click', close);
    bd.querySelector('[data-act="ok"]').addEventListener('click', function(){
        close(); if (typeof onOk === 'function') onOk();
    });
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
}

/* Editor de reseña: 5 estrellas (full-star, sin medios para móvil) +
   textarea. Save → actualiza item.review + POST save-lists +
   notify-review. Delete → quita review + save-lists. */
function openReviewEditor(item, origIdx, cat) {
    cat = cat || 'music';
    var cur = (item.review && item.review.stars) ? Math.round(item.review.stars) : 0;
    var curComment = (item.review && item.review.comment) ? item.review.comment : '';
    var hadReview = cur > 0;

    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">★ ' + esc(item.title || '—') + '</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-rev-edit-label">Puntuación</div>' +
                '<div class="pf-rev-edit-stars" id="pf-rev-edit-stars"></div>' +
                '<div class="pf-rev-edit-label">Comentario</div>' +
                '<textarea class="pf-rev-edit-comment" id="pf-rev-edit-comment" maxlength="500" placeholder="Opcional"></textarea>' +
                '<div class="modal-actions">' +
                    (hadReview ? '<button class="button danger" data-act="delete" type="button"><img src="../../assets/img/appIcons/trashIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Borrar</button>' : '') +
                    '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                    '<button class="button default" data-act="save" type="button">Guardar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);

    var starsBox = bd.querySelector('#pf-rev-edit-stars');
    var commentEl = bd.querySelector('#pf-rev-edit-comment');
    commentEl.value = curComment;
    var sel = cur;

    function renderStars() {
        var html = '';
        for (var i = 1; i <= 5; i++) {
            html += '<span class="pf-rev-edit-star' + (i > sel ? ' empty' : '') +
                    '" data-v="' + i + '">' + (i > sel ? '☆' : '★') + '</span>';
        }
        html += '<span class="pf-rev-edit-num">' + (sel || '') + '</span>';
        starsBox.innerHTML = html;
        Array.prototype.forEach.call(starsBox.querySelectorAll('.pf-rev-edit-star'), function(el){
            el.addEventListener('click', function(){
                var v = parseInt(el.dataset.v, 10);
                /* Tap en la estrella seleccionada → desactiva (queda en v-1). */
                sel = (v === sel) ? (v - 1) : v;
                renderStars();
            });
        });
    }
    renderStars();

    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="cancel"]').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });

    bd.querySelector('[data-act="save"]').addEventListener('click', function(){
        if (!sel) return;  /* Necesita al menos 1 estrella */
        var list = STATE.lists && STATE.lists[cat];
        if (!list || !list[origIdx]) { close(); return; }
        list[origIdx].review = {
            stars: sel,
            comment: commentEl.value.trim(),
            reviewedAt: Math.floor(Date.now() / 1000)
        };
        renderActiveList();
        fetch(API + '?action=save-lists', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: list })
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && Array.isArray(d.items)) { STATE.lists[cat] = d.items; renderActiveList(); }
            /* Notify a followers — mismo flow que el escritorio. */
            fetch(API + '?action=notify-review', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ category: cat, itemTitle: item.title || '', mtype: item.type || '' })
            }).catch(function(){});
        })
        .catch(function(){});
        close();
    });

    var delBtn = bd.querySelector('[data-act="delete"]');
    if (delBtn) delBtn.addEventListener('click', function(){
        var list = STATE.lists && STATE.lists[cat];
        if (!list || !list[origIdx]) { close(); return; }
        delete list[origIdx].review;
        renderActiveList();
        fetch(API + '?action=save-lists', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: list })
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && Array.isArray(d.items)) { STATE.lists[cat] = d.items; renderActiveList(); }
        })
        .catch(function(){});
        close();
    });
}

/* Diálogo de colaboradores. Si el item es shared (isCollab) muestra
   solo el host + otros (read-only). Si soy el dueño, muestra colabs
   actuales con botón Eliminar + lista de invitables con botón Invitar.
   `cat` es la categoría del item (music/movies/series/books/games). */
function openCollabDialog(item, origIdx, cat) {
    cat = cat || 'music';
    var isCollab = !!item.sharedFrom;
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">👥 ' + esc(item.title || '—') + '</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div id="pf-collab-list"></div>' +
                '<div class="pf-collab-status" id="pf-collab-status"></div>' +
                '<div class="modal-actions">' +
                    '<button class="button default" data-act="close" type="button">Cerrar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    var listEl   = bd.querySelector('#pf-collab-list');
    var statusEl = bd.querySelector('#pf-collab-status');

    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="close"]').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });

    function sectionTitle(text) {
        var el = document.createElement('div');
        el.className = 'pf-collab-section-title';
        el.textContent = text;
        return el;
    }
    function avRow(uKey, uInfo, btnLabel, onClick) {
        var row = document.createElement('div');
        row.className = 'pf-collab-row';
        var av = document.createElement('div');
        av.className = 'pf-collab-av';
        if (uInfo.image) {
            var im = document.createElement('img');
            im.src = uInfo.image; im.alt = uInfo.label;
            av.appendChild(im);
        } else {
            var ph = document.createElement('div');
            ph.className = 'pf-collab-av-ph';
            ph.textContent = '👤';
            av.appendChild(ph);
        }
        row.appendChild(av);
        var lbl = document.createElement('span');
        lbl.className = 'pf-collab-label';
        lbl.textContent = uInfo.label;
        row.appendChild(lbl);
        if (btnLabel && typeof onClick === 'function') {
            var btn = document.createElement('button');
            btn.className = 'button pf-collab-action';
            btn.textContent = btnLabel;
            btn.addEventListener('click', function(){ onClick(btn); });
            row.appendChild(btn);
        }
        return row;
    }

    if (isCollab) {
        /* Soy colaborador → muestro host + otros (read-only). */
        var ownerKey = item.sharedFrom;
        var ownerInfo = PROFILE_USERS[ownerKey];
        if (ownerInfo) {
            listEl.appendChild(sectionTitle('Host'));
            listEl.appendChild(avRow(ownerKey, ownerInfo));
        }
        fetch(API + '?action=get-item-collabs&category=' + encodeURIComponent(cat) + '&itemId=' + encodeURIComponent(item.id), { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                var others = (d.collaborators || []).filter(function(k){ return k !== USER_KEY; });
                if (!others.length) return;
                listEl.appendChild(sectionTitle('Otros colaboradores'));
                others.forEach(function(uKey){
                    var uInfo = PROFILE_USERS[uKey];
                    if (uInfo) listEl.appendChild(avRow(uKey, uInfo));
                });
            })
            .catch(function(){});
        return;
    }

    /* Soy el dueño → render colabs actuales + invitables. */
    function render() {
        listEl.innerHTML = '';
        var list = STATE.lists && STATE.lists[cat];
        var cur = (list && list[origIdx] && list[origIdx].collaborators) || [];

        if (cur.length) {
            listEl.appendChild(sectionTitle('Colaboradores'));
            cur.forEach(function(uKey){
                var uInfo = PROFILE_USERS[uKey];
                if (!uInfo) return;
                listEl.appendChild(avRow(uKey, uInfo, 'Eliminar', function(btn){
                    pfConfirm('¿Eliminar a ' + uInfo.label + ' como colaborador?', 'Eliminar', function(){
                        btn.disabled = true;
                        statusEl.textContent = 'Eliminando…';
                        fetch(API + '?action=leave-collab', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'remove', category: cat, itemId: item.id, collaboratorUser: uKey })
                        })
                        .then(function(r){ return r.json(); })
                        .then(function(d){
                            if (!d || !d.ok) { statusEl.textContent = 'Error.'; btn.disabled = false; return; }
                            statusEl.textContent = '';
                            fetchLists().then(render);
                        })
                        .catch(function(){ statusEl.textContent = 'Error.'; btn.disabled = false; });
                    });
                }));
            });
        }

        var invitable = Object.keys(PROFILE_USERS).filter(function(k){
            return k !== USER_KEY
                && cur.indexOf(k) === -1
                && isMutual(k);   /* solo seguidores mutuos */
        });
        if (!invitable.length && !cur.length) {
            var empty = document.createElement('div');
            empty.style.cssText = 'text-align:center;padding:12px 8px;font-size:12px;line-height:1.45;';
            empty.innerHTML = 'Aún no tienes amigos que invitar.<br><span style="opacity:0.75;font-size:11px;">Seguíos entre vosotros para haceros amigos.</span>';
            listEl.appendChild(empty);
        }
        if (invitable.length) {
            if (cur.length) {
                var sep = document.createElement('hr');
                sep.className = 'pf-collab-sep';
                listEl.appendChild(sep);
            }
            listEl.appendChild(sectionTitle(cur.length ? 'Invitar más' : 'Invitar'));
            invitable.forEach(function(uKey){
                var uInfo = PROFILE_USERS[uKey];
                if (!uInfo) return;
                listEl.appendChild(avRow(uKey, uInfo, 'Invitar', function(btn){
                    btn.disabled = true; btn.textContent = '…';
                    statusEl.textContent = 'Enviando…';
                    fetch(API + '?action=send-item-invite', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ toUser: uKey, category: cat, itemId: item.id })
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(d){
                        if (!d || !d.ok) {
                            statusEl.textContent = (d && d.error) || 'Error.';
                            btn.disabled = false; btn.textContent = 'Invitar';
                            return;
                        }
                        statusEl.textContent = '';
                        btn.textContent = 'Enviado';
                    })
                    .catch(function(){
                        statusEl.textContent = 'Error.';
                        btn.disabled = false; btn.textContent = 'Invitar';
                    });
                }));
            });
        }
    }
    render();
}

/* Eliminar item del perfil (yo soy el dueño). */
function deleteItem(origIdx, cat) {
    cat = cat || 'music';
    var list = STATE.lists && STATE.lists[cat];
    if (!list || !list[origIdx]) return;
    list.splice(origIdx, 1);
    var cntEl = document.getElementById('cnt-' + cat);
    if (cntEl) cntEl.textContent = list.length;
    renderActiveList();
    fetch(API + '?action=save-lists', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category: cat, items: list })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && Array.isArray(d.items)) {
            STATE.lists[cat] = d.items;
            if (cntEl) cntEl.textContent = d.items.length;
            renderActiveList();
        }
    })
    .catch(function(){
        /* No revertimos el splice — el render queda como el optimistic. */
    });
}

/* Abandonar actividad colaborativa (yo no soy el dueño). */
function leaveCollabItem(item, cat) {
    cat = cat || 'music';
    fetch(API + '?action=leave-collab', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'leave', category: cat, itemId: item.id })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){ if (d && d.ok) fetchLists().then(renderActiveList); })
    .catch(function(){});
}

/* Pide al backend la tracklist resuelta (igual que el escritorio) y se
   la pasa al MuShell del shell padre, que es quien posee el iframe de
   YouTube. Si no está embebida (acceso directo), no hay reproductor
   sobre el que actuar, así que no hacemos nada. */
function playMusicItem(item) {
    var SHELL = null;
    try { SHELL = window.parent.MuShell; } catch (_) {}
    fetch(API + '?action=play-music-item', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            itemType:       item.type           || '',
            title:          item.title          || '',
            artist:         item.artist         || '',
            ytId:           item.ytId           || '',
            spotifyId:      item.spotifyId      || '',
            ytPlaylistId:   item.ytPlaylistId   || '',
            spotifyAlbumId: item.spotifyAlbumId || ''
        })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (!d || !d.tracks || !d.tracks.length) return;
        if (SHELL && typeof SHELL.loadQueue === 'function') {
            SHELL.loadQueue(d.tracks, 0, item.title || '');
        }
    })
    .catch(function(){});
}

/* Toggle de featured con optimistic update + revert si falla el POST.
   Después del save canónico, sincronizamos STATE.lists.music con la
   respuesta del backend (puede haber re-id de items nuevos). */
function toggleFeatured(origIdx) {
    var music = STATE.lists && STATE.lists.music;
    if (!music || !music[origIdx]) return;
    music[origIdx].featured = !music[origIdx].featured;
    renderActiveList();
    fetch(API + '?action=save-lists', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category: 'music', items: music })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && Array.isArray(d.items)) { STATE.lists.music = d.items; renderActiveList(); }
    })
    .catch(function(){
        music[origIdx].featured = !music[origIdx].featured;
        renderActiveList();
    });
}

/* Long-press delegado a document. Resuelve target → {item, origIdx}
   leyendo data-cat/data-idx (renderActiveList y renderDestacados ya los
   emiten siempre en música). */
(function attachLongPress(){
    var lpTimer = null, sx = 0, sy = 0;
    function pickInfo(target){
        if (!target || !target.closest) return null;
        /* Excluye explícitamente el row "+" para que el long-press no
           dispare en él. Sí queremos pf-item + pf-dest-slot con data-cat. */
        var el = target.closest('.pf-item[data-cat]:not(.pf-add-item)') ||
                 target.closest('.pf-dest-slot[data-cat]');
        if (!el) return null;
        var cat = el.dataset.cat;
        var idx = parseInt(el.dataset.idx, 10);
        var it  = STATE.lists && STATE.lists[cat] && STATE.lists[cat][idx];
        return it ? { item: it, origIdx: idx } : null;
    }
    document.addEventListener('touchstart', function(e){
        var t = e.touches && e.touches[0]; if (!t) return;
        var info = pickInfo(e.target); if (!info) return;
        sx = t.clientX; sy = t.clientY;
        if (lpTimer) clearTimeout(lpTimer);
        lpTimer = setTimeout(function(){
            lpTimer = null; lpFired = true;
            try { navigator.vibrate && navigator.vibrate(30); } catch (_) {}
            showActionMenu(info.item, info.origIdx);
        }, LP_MS_PF);
    }, { passive: true });
    document.addEventListener('touchmove', function(e){
        if (!lpTimer) return;
        var t = e.touches && e.touches[0]; if (!t) return;
        if (Math.abs(t.clientX - sx) > LP_MOVE_PF || Math.abs(t.clientY - sy) > LP_MOVE_PF) {
            clearTimeout(lpTimer); lpTimer = null;
        }
    }, { passive: true });
    function endLp(){
        if (lpTimer) { clearTimeout(lpTimer); lpTimer = null; }
        if (lpFired) setTimeout(function(){ lpFired = false; }, 400);
    }
    document.addEventListener('touchend',    endLp);
    document.addEventListener('touchcancel', endLp);
})();

/* ─── Editar perfil ──────────────────────────────────────────────
   Modal con form para bio, pronouns, age, country + 4 conexiones.
   Guarda vía save-info y refresca la sección About con renderAbout. */
function openEditProfileDialog() {
    var p = STATE.profile || {};
    function v(k){ return (p && p[k] != null) ? String(p[k]) : ''; }
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">✏ Editar perfil</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-form-row">' +
                    '<label for="ep-bio">Descripción</label>' +
                    '<textarea id="ep-bio" maxlength="200" placeholder="Cuéntanos algo sobre ti...">' + esc(v('bio')) + '</textarea>' +
                    '<div class="pf-form-hint">Máx. 200 caracteres.</div>' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-pronouns">Pronombres</label>' +
                    '<input type="text" id="ep-pronouns" maxlength="30" value="' + esc(v('pronouns')) + '" placeholder="él/ella/elle...">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-age">Edad</label>' +
                    '<input type="number" id="ep-age" min="0" max="999" value="' + esc(v('age')) + '" placeholder="25">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-country">País</label>' +
                    '<input type="text" id="ep-country" maxlength="50" value="' + esc(v('country')) + '" placeholder="📍 España">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-steam">🎮 Steam</label>' +
                    '<input type="text" id="ep-steam" maxlength="200" value="' + esc(v('steam')) + '" placeholder="usuario o URL">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-discord">💬 Discord</label>' +
                    '<input type="text" id="ep-discord" maxlength="100" value="' + esc(v('discord')) + '" placeholder="usuario#1234">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-twitter">🐦 Twitter</label>' +
                    '<input type="text" id="ep-twitter" maxlength="100" value="' + esc(v('twitter')) + '" placeholder="@usuario">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ep-instagram">📷 Instagram</label>' +
                    '<input type="text" id="ep-instagram" maxlength="100" value="' + esc(v('instagram')) + '" placeholder="@usuario">' +
                '</div>' +
                '<div class="pf-form-error" id="ep-error"></div>' +
                '<div class="modal-actions">' +
                    '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                    '<button class="button default" data-act="save" type="button">Guardar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="cancel"]').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
    bd.querySelector('[data-act="save"]').addEventListener('click', function(){
        var payload = {
            bio:       bd.querySelector('#ep-bio').value,
            pronouns:  bd.querySelector('#ep-pronouns').value,
            age:       bd.querySelector('#ep-age').value,
            country:   bd.querySelector('#ep-country').value,
            steam:     bd.querySelector('#ep-steam').value,
            discord:   bd.querySelector('#ep-discord').value,
            twitter:   bd.querySelector('#ep-twitter').value,
            instagram: bd.querySelector('#ep-instagram').value
        };
        var errEl = bd.querySelector('#ep-error');
        errEl.textContent = 'Guardando…';
        fetch(API + '?action=save-info', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && d.error) { errEl.textContent = d.error; return; }
            /* Recargamos el perfil server-side para reflejar saneamientos
               (trim, mb_substr) y actualizar el render. */
            fetchProfile().then(close);
        })
        .catch(function(){ errEl.textContent = 'Error de conexión.'; });
    });
}

/* ─── Añadir item NO-música (movies/series/books/games) ──────────
   Modal con title + image opcional + status. Reusa save-lists. */
function openAddItemDialog(cat) {
    var labels = {
        movies: { title: '<img src="../../assets/img/appIcons/pelisIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Añadir película', titleField: 'Título' },
        series: { title: '<img src="../../assets/img/appIcons/melonArchiveIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Añadir serie',    titleField: 'Título' },
        books:  { title: '📖 Añadir libro',    titleField: 'Título' },
        games:  { title: '🎮 Añadir juego',    titleField: 'Título' }
    };
    var cfg = labels[cat]; if (!cfg) return;
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">' + cfg.title + '</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body">' +
                '<div class="pf-form-row">' +
                    '<label for="ai-title">' + cfg.titleField + '</label>' +
                    '<input type="text" id="ai-title" maxlength="200" autofocus>' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ai-image">Imagen (URL, opcional)</label>' +
                    '<input type="text" id="ai-image" maxlength="500" placeholder="https://...">' +
                '</div>' +
                '<div class="pf-form-row">' +
                    '<label for="ai-status">Estado</label>' +
                    '<select id="ai-status">' +
                        '<option value="pending">Pendiente</option>' +
                        '<option value="in-progress">En curso</option>' +
                        '<option value="completed">Completado</option>' +
                    '</select>' +
                '</div>' +
                '<div class="pf-form-error" id="ai-error"></div>' +
                '<div class="modal-actions">' +
                    '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                    '<button class="button default" data-act="save" type="button">Añadir</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="cancel"]').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
    bd.querySelector('[data-act="save"]').addEventListener('click', function(){
        var title  = bd.querySelector('#ai-title').value.trim();
        var image  = bd.querySelector('#ai-image').value.trim();
        var status = bd.querySelector('#ai-status').value;
        var errEl  = bd.querySelector('#ai-error');
        if (!title) { errEl.textContent = 'Ponle un título.'; return; }
        var list = (STATE.lists && STATE.lists[cat]) || [];
        var lower = title.toLowerCase();
        if (list.some(function(x){ return x && x.title && x.title.toLowerCase() === lower; })) {
            errEl.textContent = '⚠ Ya tienes "' + title + '" en tu lista.';
            return;
        }
        var item = {
            id:     'item_' + Date.now(),
            title:  title,
            image:  image,
            status: status
        };
        var newList = list.slice(); newList.push(item);
        errEl.textContent = 'Guardando…';
        fetch(API + '?action=save-lists', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: newList })
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && d.error) { errEl.textContent = d.error; return; }
            if (d && Array.isArray(d.items)) STATE.lists[cat] = d.items;
            else STATE.lists[cat] = newList;
            document.getElementById('cnt-' + cat).textContent = STATE.lists[cat].length;
            renderActiveList();
            close();
        })
        .catch(function(){ errEl.textContent = 'Error de conexión.'; });
    });
}

/* ─── Añadir música ──────────────────────────────────────────────
   Modal multi-paso: type → URL+artist → reseña opcional → save.
   Usa resolve-music-item para extraer metadata (ytId/spotifyId/etc),
   save-lists para insertar, y opcionalmente notify-review. */
function openMusicAddDialog() {
    var defaultType = STATE.musicView === 'songs' ? 'song' : 'album';
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text" id="ma-title">+ Añadir música</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body" id="ma-body"></div>' +
        '</div>';
    document.body.appendChild(bd);

    var titleEl = bd.querySelector('#ma-title');
    var bodyEl  = bd.querySelector('#ma-body');
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });

    var state = { type: null, meta: null, artist: '', reviewStars: 0, reviewComment: '' };

    function renderStep1() {
        titleEl.textContent = '+ Añadir música';
        bodyEl.innerHTML =
            '<p style="font-size:12px;margin:0 0 10px;">¿Qué quieres añadir?</p>' +
            '<div class="pf-type-row">' +
                '<button class="button pf-type-btn" data-t="song" type="button">' +
                    '<span class="pf-type-emoji"><img src="../../assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>Canción</button>' +
                '<button class="button pf-type-btn" data-t="album" type="button">' +
                    '<span class="pf-type-emoji">💿</span>Álbum / Playlist</button>' +
            '</div>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
            '</div>';
        bodyEl.querySelector('[data-act="cancel"]').addEventListener('click', close);
        bodyEl.querySelectorAll('[data-t]').forEach(function(btn){
            btn.addEventListener('click', function(){
                state.type = btn.dataset.t;
                renderStep2();
            });
        });
    }

    function renderStep2() {
        /* Song usa PNG musicaIcon vía innerHTML; album mantiene 💿. */
        if (state.type === 'song') {
            titleEl.innerHTML = '<img src="../../assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Añadir canción';
        } else {
            titleEl.textContent = '💿 Añadir álbum';
        }
        bodyEl.innerHTML =
            '<div class="pf-form-row">' +
                '<label for="ma-url">Enlace de YouTube o Spotify</label>' +
                '<input type="text" id="ma-url" placeholder="https://..." autofocus>' +
                '<div class="pf-form-hint" id="ma-preview" style="font-style:normal;color:var(--text-faint,#666);">Pega un enlace para buscar.</div>' +
            '</div>' +
            '<div class="pf-form-row">' +
                '<label for="ma-artist">Artista</label>' +
                '<input type="text" id="ma-artist" maxlength="200" placeholder="Artista..." value="' + esc(state.artist) + '">' +
            '</div>' +
            '<div class="pf-form-error" id="ma-error"></div>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="back" type="button">← Atrás</button>' +
                '<button class="button" data-act="cancel" type="button">Cancelar</button>' +
                '<button class="button default" data-act="next" type="button">Siguiente →</button>' +
            '</div>';

        var urlEl     = bodyEl.querySelector('#ma-url');
        var artistEl  = bodyEl.querySelector('#ma-artist');
        var previewEl = bodyEl.querySelector('#ma-preview');
        var errEl     = bodyEl.querySelector('#ma-error');
        var fetchTimer = null;

        bodyEl.querySelector('[data-act="back"]').addEventListener('click', renderStep1);
        bodyEl.querySelector('[data-act="cancel"]').addEventListener('click', close);
        urlEl.addEventListener('input', function(){
            clearTimeout(fetchTimer); state.meta = null;
            previewEl.style.color = 'var(--text-faint, #666)';
            var raw = urlEl.value.trim();
            if (!raw) { previewEl.textContent = 'Pega un enlace para buscar.'; return; }
            previewEl.textContent = 'Buscando…';
            fetchTimer = setTimeout(function(){
                fetch(API + '?action=resolve-music-item&url=' + encodeURIComponent(raw) + '&itemType=' + state.type, { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(d){
                        if (!d || d.error) {
                            previewEl.style.color = 'var(--error-text, #c00)';
                            previewEl.textContent = (d && d.error) || 'No se pudo resolver.';
                            return;
                        }
                        state.meta = d;
                        previewEl.style.color = 'var(--text, #000)';
                        previewEl.textContent = (state.type === 'album' ? '💿 ' : '♪ ') + (d.title || '');
                        if (d.artist && !artistEl.value.trim()) artistEl.value = d.artist;
                    })
                    .catch(function(){
                        previewEl.style.color = 'var(--error-text, #c00)';
                        previewEl.textContent = 'Error de conexión.';
                    });
            }, 500);
        });
        bodyEl.querySelector('[data-act="next"]').addEventListener('click', function(){
            state.artist = artistEl.value.trim();
            if (!state.meta) { errEl.textContent = 'Espera a que se cargue el enlace.'; return; }
            var music = (STATE.lists && STATE.lists.music) || [];
            var lower = state.meta.title.toLowerCase().trim();
            if (music.some(function(x){ return x && x.title && x.title.toLowerCase().trim() === lower; })) {
                errEl.textContent = '⚠ Ya tienes "' + state.meta.title + '" en tu lista.';
                return;
            }
            errEl.textContent = '';
            renderStep3();
        });
    }

    function renderStep3() {
        titleEl.textContent = '★ ¿Reseñar?';
        bodyEl.innerHTML =
            '<p style="font-size:12px;margin:0 0 10px;">¿Quieres añadir una reseña a "' + esc(state.meta.title) + '"?</p>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="back" type="button">← Atrás</button>' +
                '<button class="button" data-act="skip" type="button">No, guardar</button>' +
                '<button class="button default" data-act="yes" type="button">★ Sí</button>' +
            '</div>';
        bodyEl.querySelector('[data-act="back"]').addEventListener('click', renderStep2);
        bodyEl.querySelector('[data-act="skip"]').addEventListener('click', function(){ saveAll(false); });
        bodyEl.querySelector('[data-act="yes"]').addEventListener('click', renderStep4);
    }

    function renderStep4() {
        titleEl.textContent = '★ Reseña';
        bodyEl.innerHTML =
            '<div class="pf-rev-edit-label">Puntuación</div>' +
            '<div class="pf-rev-edit-stars" id="ma-stars"></div>' +
            '<div class="pf-rev-edit-label">Comentario</div>' +
            '<textarea class="pf-rev-edit-comment" id="ma-comment" maxlength="500" placeholder="Opcional"></textarea>' +
            '<div class="pf-form-error" id="ma-rev-error"></div>' +
            '<div class="modal-actions">' +
                '<button class="button" data-act="back" type="button">← Atrás</button>' +
                '<button class="button default" data-act="save" type="button">Guardar</button>' +
            '</div>';
        var starsEl   = bodyEl.querySelector('#ma-stars');
        var commentEl = bodyEl.querySelector('#ma-comment');
        commentEl.value = state.reviewComment;
        var sel = state.reviewStars || 0;
        function paint(){
            var html = '';
            for (var i = 1; i <= 5; i++) {
                html += '<span class="pf-rev-edit-star' + (i > sel ? ' empty' : '') +
                        '" data-v="' + i + '">' + (i > sel ? '☆' : '★') + '</span>';
            }
            html += '<span class="pf-rev-edit-num">' + (sel || '') + '</span>';
            starsEl.innerHTML = html;
            Array.prototype.forEach.call(starsEl.querySelectorAll('.pf-rev-edit-star'), function(el){
                el.addEventListener('click', function(){
                    var v = parseInt(el.dataset.v, 10);
                    sel = (v === sel) ? (v - 1) : v;
                    paint();
                });
            });
        }
        paint();
        bodyEl.querySelector('[data-act="back"]').addEventListener('click', renderStep3);
        bodyEl.querySelector('[data-act="save"]').addEventListener('click', function(){
            var errEl = bodyEl.querySelector('#ma-rev-error');
            if (!sel) { errEl.textContent = 'Selecciona una puntuación.'; return; }
            state.reviewStars   = sel;
            state.reviewComment = commentEl.value.trim();
            saveAll(true);
        });
    }

    function saveAll(withReview) {
        var meta = state.meta;
        var entry = {
            id:       'music_' + Date.now(),
            type:     state.type,
            title:    meta.title,
            artist:   state.artist || meta.artist || '',
            image:    meta.image || '',
            featured: false
        };
        if (meta.ytId)           entry.ytId           = meta.ytId;
        if (meta.spotifyId)      entry.spotifyId      = meta.spotifyId;
        if (meta.ytPlaylistId)   entry.ytPlaylistId   = meta.ytPlaylistId;
        if (meta.spotifyAlbumId) entry.spotifyAlbumId = meta.spotifyAlbumId;
        if (withReview && state.reviewStars > 0) {
            entry.review = {
                stars:      state.reviewStars,
                comment:    state.reviewComment,
                reviewedAt: Math.floor(Date.now() / 1000)
            };
        }
        var music = ((STATE.lists && STATE.lists.music) || []).slice();
        music.push(entry);
        fetch(API + '?action=save-lists', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: 'music', items: music })
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && d.error) { /* mostrar error en step actual */
                var e = bodyEl.querySelector('.pf-form-error');
                if (e) e.textContent = d.error;
                return;
            }
            if (d && Array.isArray(d.items)) STATE.lists.music = d.items;
            else STATE.lists.music = music;
            document.getElementById('cnt-music').textContent = STATE.lists.music.length;
            renderActiveList();
            if (withReview && state.reviewStars > 0) {
                fetch(API + '?action=notify-review', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ category: 'music', itemTitle: meta.title, mtype: state.type })
                }).catch(function(){});
            }
            close();
        })
        .catch(function(){
            var e = bodyEl.querySelector('.pf-form-error');
            if (e) e.textContent = 'Error de conexión.';
        });
    }

    /* Arrancamos en step1 (chooser) si no hay sub-tab activo claro,
       o saltamos a step2 con el tipo derivado de musicView. */
    if (defaultType) { state.type = defaultType; renderStep2(); }
    else renderStep1();
}

/* Click delegado a nivel `document`: cualquier tap sobre una fila o
   slot con `.has-review` abre el modal de reseña. Delego en document
   por si un ancestro con overflow se traga el evento.
   El row "+" (data-act="add-item") abre el dialog de añadir item. */
document.addEventListener('click', function(e){
    if (lpFired) { e.preventDefault(); e.stopPropagation(); return; }
    if (!e.target || !e.target.closest) return;
    var addRow = e.target.closest('.pf-item.pf-add-item');
    if (addRow) {
        e.preventDefault(); e.stopPropagation();
        if (STATE.current === 'music') openMusicAddDialog();
        else openAddItemDialog(STATE.current);
        return;
    }
    var row = e.target.closest('.pf-item.has-review') ||
              e.target.closest('.pf-dest-slot.has-review');
    if (!row) return;
    e.preventDefault();
    e.stopPropagation();
    var cat = row.dataset.cat;
    var idx = parseInt(row.dataset.idx, 10);
    var item = (STATE.lists && STATE.lists[cat] && STATE.lists[cat][idx]) || null;
    if (item && item.review) openReviewView(item);
});

/* Botón "Editar perfil" — vive ahora en la vista Perfil. */
document.getElementById('pf-edit-profile-btn').addEventListener('click', openEditProfileDialog);

/* ─── Sidebar drawer ──────────────────────────────────────────────
   Botón ☰ abre el panel lateral. El backdrop y la × lo cierran.
   El contenido del body cambia según si estoy en mi perfil o
   viendo el de otro usuario. */
var DRAWER     = document.getElementById('pf-drawer');
var DRAWER_BD  = document.getElementById('pf-drawer-backdrop');
var DRAWER_BD2 = document.getElementById('pf-drawer-body');

function openDrawer() {
    renderDrawerBody();
    DRAWER.classList.add('open');
    DRAWER_BD.classList.add('open');
}
function closeDrawer() {
    DRAWER.classList.remove('open');
    DRAWER_BD.classList.remove('open');
}
document.getElementById('pf-hamburger').addEventListener('click', openDrawer);
document.getElementById('pf-drawer-close').addEventListener('click', closeDrawer);
DRAWER_BD.addEventListener('click', closeDrawer);

function renderDrawerBody() {
    var viewing = !!STATE.viewingUser;
    var sections = [];

    if (!viewing) {
        sections.push({
            title: null,
            items: [
                { view: 'profile', icon: '👤', label: 'Perfil' },
                { view: 'lists',   icon: '📋', label: 'Mis listas' },
                { view: 'social',  icon: '👥', label: 'Social' }
            ]
        });
    } else {
        sections.push({
            title: null,
            items: [
                { view: 'profile', icon: '👤', label: 'Perfil' },
                { view: 'lists',   icon: '📋', label: 'Listas de ' + (STATE.viewingLabel || '?') }
            ]
        });
    }

    var html = '';
    sections.forEach(function(sec){
        html += '<div class="pf-drawer-section">';
        if (sec.title) html += '<div class="pf-drawer-section-title">' + esc(sec.title) + '</div>';
        sec.items.forEach(function(it){
            var active = STATE.view === it.view ? ' active' : '';
            html += '<button class="button pf-drawer-item' + active + '" data-view="' + it.view + '" type="button">' +
                      '<span class="pf-drawer-icon">' + it.icon + '</span>' +
                      '<span>' + esc(it.label) + '</span>' +
                    '</button>';
        });
        html += '</div>';
    });
    DRAWER_BD2.innerHTML = html;
    DRAWER_BD2.querySelectorAll('[data-view]').forEach(function(btn){
        btn.addEventListener('click', function(){
            showView(btn.dataset.view);
            closeDrawer();
        });
    });
}

/* ─── View switching ────────────────────────────────────────────── */
function showView(name) {
    STATE.view = name;
    var V = { profile: 'pf-view-profile', lists: 'pf-view-lists', social: 'pf-view-social' };
    Object.keys(V).forEach(function(k){
        var el = document.getElementById(V[k]);
        if (el) el.hidden = (k !== name);
    });
    /* Acciones específicas por vista. */
    if (name === 'profile') {
        /* Posts ya cargados con fetchProfile; render si tenemos datos. */
        if (STATE.profile && STATE.profile.posts) renderPosts(STATE.profile.posts);
        /* Si estoy viendo otro usuario, oculto edit + new-post y enseño follow. */
        toggleProfileActions();
    } else if (name === 'social') {
        renderSocial();
    }
}

/* Edit profile / nuevo post solo en mi perfil. Botón Seguir solo cuando
   estoy viendo a otro. Chat solo si hay seguimiento mutuo. */
function toggleProfileActions() {
    var viewing = !!STATE.viewingUser;
    var editBtn = document.getElementById('pf-edit-profile-btn');
    var followBtn = document.getElementById('pf-follow-btn');
    var chatBtn = document.getElementById('pf-chat-btn');
    var newPost = document.getElementById('pf-new-post');
    editBtn.hidden   = viewing;
    followBtn.hidden = !viewing;
    chatBtn.hidden   = !(viewing && isMutual(STATE.viewingUser));
    newPost.hidden   = viewing;
}

/* Mutualidad: sigo a X Y X me sigue. Necesario para el chat (el server
   también lo verifica con 403 si falta). */
function isMutual(uKey) {
    if (!uKey) return false;
    return STATE.myFollowing.indexOf(uKey) !== -1 &&
           STATE.myFollowers.indexOf(uKey) !== -1;
}

/* Presencia: refresca los puntos online/offline en los avatares de
   Social. Llamado al cargar y cada 20s. Cachea el último set para que
   los re-renders apliquen el estado sin esperar al fetch. */
var __pfLastOnline = {};
function applyPresence() {
    document.querySelectorAll('.pf-presence-dot[data-userkey]').forEach(function(dot) {
        var k = dot.getAttribute('data-userkey');
        dot.classList.toggle('online', !!__pfLastOnline[k]);
    });
}
/* Helper: attach/replace dot en cualquier wrapper de avatar. userKey
   '' o falsy → elimina el dot. */
function attachPresenceDot(wrap, userKey, small) {
    if (!wrap) return;
    var existing = wrap.querySelector('.pf-presence-dot');
    if (existing) existing.remove();
    if (!userKey) {
        wrap.classList.remove('has-presence-dot');
        return;
    }
    wrap.classList.add('has-presence-dot');
    var dot = document.createElement('span');
    dot.className = 'pf-presence-dot' + (small ? ' pf-presence-dot-small' : '');
    dot.setAttribute('data-userkey', userKey);
    if (__pfLastOnline[userKey]) dot.classList.add('online');
    wrap.appendChild(dot);
}
window.__pfAttachPresenceDot = attachPresenceDot;
function refreshPresenceDots() {
    fetch(API + '?action=presence', { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d || !d.ok || !Array.isArray(d.online)) return;
            __pfLastOnline = {};
            d.online.forEach(function(k) { __pfLastOnline[k] = true; });
            applyPresence();
        })
        .catch(function() {});
}
window.__pfApplyPresence = applyPresence;
refreshPresenceDots();
setInterval(refreshPresenceDots, 20000);

/* ─── Posts ─────────────────────────────────────────────────────── */
function renderPosts(posts) {
    var listEl = document.getElementById('pf-posts-list');
    if (!listEl) return;
    if (!posts || !posts.length) {
        listEl.innerHTML = '<div class="mh-empty"><span class="mh-empty-icon">📝</span>Sin posts todavía</div>';
        return;
    }
    var iAmOwner = !STATE.viewingUser;
    var authorKey = STATE.viewingUser || USER_KEY;
    var authorLabel = STATE.viewingLabel || USER_LABEL;
    var html = '';
    posts.forEach(function(p){
        var imgHtml = p.imageUrl ? '<img class="pf-post-img" src="' + esc(p.imageUrl) + '" alt="" loading="lazy">' : '';
        var liked = (p.likes || []).indexOf(USER_KEY) !== -1;
        var likeCount = (p.likes || []).length;
        var commentCount = (p.comments || []).length;
        var canDel = iAmOwner;
        /* En mis propios posts no puedo darme like — render del botón
           como disabled para que se vea el contador pero no responda. */
        var likeBtnAttr = iAmOwner ? ' disabled' : '';
        html +=
            '<div class="pf-post" data-post-id="' + p.id + '">' +
                '<div class="pf-post-head">' +
                    '<span class="pf-post-author">' + esc(authorLabel) + '</span>' +
                    '<span class="pf-post-time">' + relTime(p.createdAt) + '</span>' +
                '</div>' +
                (p.text ? '<div class="pf-post-text">' + esc(p.text) + '</div>' : '') +
                imgHtml +
                '<div class="pf-post-foot">' +
                    '<button class="button pf-post-btn' + (liked ? ' is-liked' : '') + '" data-act="like" type="button"' + likeBtnAttr + '>' +
                        (liked ? '❤' : '♡') + ' ' + likeCount +
                    '</button>' +
                    '<button class="button pf-post-btn" data-act="comments" type="button">💬 ' + commentCount + '</button>' +
                    (canDel ? '<button class="button pf-post-btn danger" data-act="delete" type="button"><img src="../../assets/img/appIcons/trashIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;"></button>' : '') +
                '</div>' +
                '<div class="pf-post-comments" data-comments-for="' + p.id + '" hidden></div>' +
            '</div>';
    });
    listEl.innerHTML = html;

    listEl.querySelectorAll('.pf-post').forEach(function(postEl){
        var pid = parseInt(postEl.dataset.postId, 10);
        var post = posts.find(function(x){ return x.id === pid; });
        if (!post) return;
        var likeBtn = postEl.querySelector('[data-act="like"]');
        /* No bindeamos handler si está disabled (posts propios). */
        if (likeBtn && !likeBtn.disabled) {
            likeBtn.addEventListener('click', function(){ togglePostLike(post, postEl); });
        }
        var cmtBtn  = postEl.querySelector('[data-act="comments"]');
        if (cmtBtn)  cmtBtn.addEventListener('click', function(){ toggleComments(post, postEl); });
        var delBtn  = postEl.querySelector('[data-act="delete"]');
        if (delBtn)  delBtn.addEventListener('click', function(){
            pfConfirm('¿Eliminar el post?', 'Eliminar', function(){ deletePost(post.id); });
        });
    });
}

/* Helper de fecha relativa (ahora / hace Nm / hace Nh / hace Nd). */
function relTime(ts) {
    var diff = Math.floor(Date.now() / 1000) - (ts || 0);
    if (diff < 60)    return 'ahora';
    if (diff < 3600)  return 'hace ' + Math.floor(diff / 60) + 'm';
    if (diff < 86400) return 'hace ' + Math.floor(diff / 3600) + 'h';
    return 'hace ' + Math.floor(diff / 86400) + 'd';
}

function togglePostLike(post, postEl) {
    var targetUser = STATE.viewingUser || USER_KEY;
    fetch(API + '?action=toggle-post-like', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ targetUser: targetUser, postId: post.id })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && d.ok && Array.isArray(d.likes)) {
            post.likes = d.likes;
            var btn = postEl.querySelector('[data-act="like"]');
            var liked = d.likes.indexOf(USER_KEY) !== -1;
            btn.classList.toggle('is-liked', liked);
            btn.textContent = (liked ? '❤' : '♡') + ' ' + d.likes.length;
        }
    })
    .catch(function(){});
}

function toggleComments(post, postEl) {
    var box = postEl.querySelector('[data-comments-for="' + post.id + '"]');
    if (!box) return;
    if (!box.hidden) { box.hidden = true; return; }
    renderComments(post, box);
    box.hidden = false;
}

function renderComments(post, box) {
    var comments = post.comments || [];
    var html = '';
    comments.forEach(function(c){
        var canDel = (c.authorKey === USER_KEY) || (!STATE.viewingUser);
        html +=
            '<div class="pf-comment" data-comment-id="' + c.id + '">' +
                '<span class="pf-comment-author">' + esc(c.authorLabel || c.authorKey) + ':</span>' +
                '<span class="pf-comment-text">' + esc(c.text) + '</span>' +
                (canDel ? '<button class="button pf-comment-del" data-act="del-comment" type="button">×</button>' : '') +
            '</div>';
    });
    /* Form solo si soy yo o si estoy viendo otro (puedo comentar en posts ajenos). */
    html +=
        '<div class="pf-comment-form">' +
            '<input type="text" maxlength="500" placeholder="Comentar...">' +
            '<button class="button" type="button">Enviar</button>' +
        '</div>';
    box.innerHTML = html;
    box.querySelectorAll('[data-act="del-comment"]').forEach(function(btn){
        var row = btn.closest('[data-comment-id]');
        var cid = parseInt(row.dataset.commentId, 10);
        btn.addEventListener('click', function(){ deleteComment(post, cid, box); });
    });
    var input = box.querySelector('input');
    var sendBtn = box.querySelector('.pf-comment-form button');
    function send(){
        var txt = input.value.trim();
        if (!txt) return;
        addComment(post, txt, box);
        input.value = '';
    }
    sendBtn.addEventListener('click', send);
    input.addEventListener('keydown', function(e){ if (e.key === 'Enter') send(); });
}

function addComment(post, text, box) {
    fetch(API + '?action=add-comment', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ postId: post.id, text: text })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && d.ok && d.comment) {
            post.comments = post.comments || [];
            post.comments.push(d.comment);
            renderComments(post, box);
            /* Actualiza el contador del botón. */
            var postEl = box.closest('.pf-post');
            var btn = postEl && postEl.querySelector('[data-act="comments"]');
            if (btn) btn.textContent = '💬 ' + post.comments.length;
        }
    })
    .catch(function(){});
}

function deleteComment(post, cid, box) {
    fetch(API + '?action=delete-comment', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: cid })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && d.ok) {
            post.comments = (post.comments || []).filter(function(c){ return c.id !== cid; });
            renderComments(post, box);
            var postEl = box.closest('.pf-post');
            var btn = postEl && postEl.querySelector('[data-act="comments"]');
            if (btn) btn.textContent = '💬 ' + post.comments.length;
        }
    })
    .catch(function(){});
}

function deletePost(postId) {
    fetch(API + '?action=delete-post', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: postId })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && d.ok && STATE.profile && STATE.profile.posts) {
            STATE.profile.posts = STATE.profile.posts.filter(function(p){ return p.id !== postId; });
            renderPosts(STATE.profile.posts);
        }
    })
    .catch(function(){});
}

document.getElementById('pf-post-publish').addEventListener('click', function(){
    var textEl = document.getElementById('pf-post-text');
    var imgEl  = document.getElementById('pf-post-img');
    var text = (textEl.value || '').trim();
    var img  = (imgEl.value || '').trim();
    if (!text && !img) return;
    fetch(API + '?action=add-post', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ text: text, image_url: img })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d && d.ok && d.post) {
            STATE.profile = STATE.profile || {};
            STATE.profile.posts = STATE.profile.posts || [];
            STATE.profile.posts.unshift(d.post);
            renderPosts(STATE.profile.posts);
            textEl.value = ''; imgEl.value = '';
        } else if (d && d.error) {
            pfAlert(d.error);
        }
    })
    .catch(function(){});
});

/* Botón "Seguir" — solo aparece viendo otro perfil. Toggle. */
document.getElementById('pf-follow-btn').addEventListener('click', function(){
    if (!STATE.viewingUser) return;
    var btn = this;
    btn.disabled = true;
    fetch(API + '?action=toggle-follow', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ targetUser: STATE.viewingUser })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        btn.disabled = false;
        if (d && typeof d.following === 'boolean') {
            updateFollowBtn(d.following);
            /* El server devuelve la lista canónica → la usamos. */
            if (Array.isArray(d.list)) STATE.myFollowing = d.list.slice();
        }
    })
    .catch(function(){ btn.disabled = false; });
});
function updateFollowBtn(isFollowing) {
    var btn = document.getElementById('pf-follow-btn');
    btn.textContent = isFollowing ? '✓ Siguiendo' : '+ Seguir';
}

/* Helper alert para JS sin window.alert. */
function pfAlert(msg) {
    pfConfirm(msg, 'OK', function(){});  /* reuso del confirm como info simple */
}

/* ─── Social view ──────────────────────────────────────────────── */
function renderSocial() {
    var gridEl = document.getElementById('pf-friends-grid');
    if (!gridEl) return;
    var friends = STATE.myFollowing || [];
    var html = '';
    friends.forEach(function(uKey){
        var uInfo = PROFILE_USERS[uKey];
        if (!uInfo) return;
        var avHtml = uInfo.image
            ? '<img src="' + esc(uInfo.image) + '" alt="">'
            : '<span>👤</span>';
        /* Badge de chat solo para mutuos (el server enforza la mutualidad
           con 403; ocultarlo aquí evita un tap que falla). */
        var chatBadge = isMutual(uKey)
            ? '<button class="pf-friend-chat" data-act="chat" type="button" aria-label="Chat">💬</button>'
            : '';
        html +=
            '<div class="pf-friend-card" data-user="' + esc(uKey) + '">' +
                '<div class="pf-friend-av has-presence-dot">' + avHtml + chatBadge + '<span class="pf-presence-dot" data-userkey="' + esc(uKey) + '"></span></div>' +
                '<div class="pf-friend-label">' + esc(uInfo.label) + '</div>' +
            '</div>';
    });
    /* "+ Explorar" tile al final. */
    html +=
        '<div class="pf-friend-card is-explore" data-act="explore">' +
            '<div class="pf-friend-av"><span class="pf-friend-av-plus">+</span></div>' +
            '<div class="pf-friend-label">Explorar</div>' +
        '</div>';
    gridEl.innerHTML = html;
    gridEl.querySelectorAll('[data-user]').forEach(function(card){
        card.addEventListener('click', function(){ viewOtherUser(card.dataset.user); });
    });
    /* Re-aplica el estado de presencia conocido sin esperar al fetch. */
    if (window.__pfApplyPresence) window.__pfApplyPresence();
    /* Badge 💬: stopPropagation para que no dispare también viewOtherUser. */
    gridEl.querySelectorAll('[data-act="chat"]').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var card = btn.closest('[data-user]');
            if (card) openChat(card.dataset.user);
        });
    });
    var exploreBtn = gridEl.querySelector('[data-act="explore"]');
    if (exploreBtn) exploreBtn.addEventListener('click', openExploreDialog);
    /* Re-aplica los badges al render — el grid se acaba de regenerar y
       perdió cualquier badge previo del estado actual de unreadChats. */
    renderUnreadBadges();
}

/* Modal de explorar — lista de usuarios que NO sigo. */
function openExploreDialog() {
    var others = Object.keys(PROFILE_USERS).filter(function(k){
        return k !== USER_KEY && STATE.myFollowing.indexOf(k) === -1;
    });
    var listHtml = '';
    if (!others.length) {
        listHtml = '<p class="pf-form-hint" style="text-align:center;padding:10px;">Ya sigues a todo el mundo 🎉</p>';
    } else {
        listHtml = '<div class="pf-friends-grid">';
        others.forEach(function(uKey){
            var uInfo = PROFILE_USERS[uKey];
            if (!uInfo) return;
            var avHtml = uInfo.image
                ? '<img src="' + esc(uInfo.image) + '" alt="">'
                : '<span>👤</span>';
            listHtml +=
                '<div class="pf-friend-card" data-user="' + esc(uKey) + '">' +
                    '<div class="pf-friend-av has-presence-dot">' + avHtml + '<span class="pf-presence-dot" data-userkey="' + esc(uKey) + '"></span></div>' +
                    '<div class="pf-friend-label">' + esc(uInfo.label) + '</div>' +
                '</div>';
        });
        listHtml += '</div>';
    }
    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.innerHTML =
        '<div class="window pf-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text">🌐 Explorar</div>' +
                '<div class="title-bar-controls"><button aria-label="Close" type="button"></button></div>' +
            '</div>' +
            '<div class="window-body">' + listHtml +
                '<div class="modal-actions"><button class="button default" data-act="close" type="button">Cerrar</button></div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);
    function close(){ if (bd.parentNode) bd.parentNode.removeChild(bd); }
    bd.querySelector('.title-bar-controls button').addEventListener('click', close);
    bd.querySelector('[data-act="close"]').addEventListener('click', close);
    bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
    bd.querySelectorAll('[data-user]').forEach(function(card){
        card.addEventListener('click', function(){
            close();
            viewOtherUser(card.dataset.user);
        });
    });
}

/* ─── View other user ──────────────────────────────────────────── */
function viewOtherUser(userKey) {
    if (!userKey || userKey === USER_KEY) return;
    fetch(API + '?action=view-user&user=' + encodeURIComponent(userKey), { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || d.error) { pfAlert((d && d.error) || 'No se pudo cargar el perfil'); return; }
            STATE.viewingUser  = d.userKey;
            STATE.viewingLabel = d.label;
            STATE.profile      = d.profile;
            STATE.lists        = d.lists;
            updateHeaderForViewing();
            updateFollowBtn(!!d.isFollowing);
            /* Render: dirige al usuario directo a la sección de posts. */
            renderAbout(STATE.profile);
            ['movies','series','books','games','music'].forEach(function(cat){
                var arr = (STATE.lists && STATE.lists[cat]) || [];
                var el = document.getElementById('cnt-' + cat);
                if (el) el.textContent = arr.length;
            });
            renderActiveList();
            showView('profile');
        })
        .catch(function(){ pfAlert('Error de conexión'); });
}

function exitViewingUser() {
    STATE.viewingUser  = null;
    STATE.viewingLabel = null;
    updateHeaderForViewing();
    /* Recarga mis datos. */
    fetchProfile();
    fetchLists();
    showView('profile');
}

function updateHeaderForViewing() {
    var viewing = !!STATE.viewingUser;
    var banner  = document.getElementById('pf-viewing-banner');
    banner.classList.toggle('is-active', viewing);
    if (viewing) {
        document.getElementById('pf-viewing-text').textContent =
            'Viendo perfil de ' + (STATE.viewingLabel || '?');
    }
    /* Cambia el nombre y avatar del userbar al perfil que estoy viendo. */
    var nameEl    = document.getElementById('pf-userbar-name');
    var greetEl   = document.getElementById('pf-userbar-greeting');
    var avatarEl  = document.getElementById('pf-userbar-avatar');
    if (viewing) {
        var info = PROFILE_USERS[STATE.viewingUser];
        nameEl.textContent  = STATE.viewingLabel || (info && info.label) || '?';
        greetEl.textContent = 'Perfil de';
        if (info && info.image) {
            avatarEl.innerHTML = '<img src="' + esc(info.image) + '" alt="">';
        } else {
            avatarEl.innerHTML = '<span>👤</span>';
        }
        attachPresenceDot(avatarEl, STATE.viewingUser);
    } else {
        nameEl.textContent  = USER_LABEL;
        greetEl.textContent = 'Perfil de';
        var meInfo = PROFILE_USERS[USER_KEY];
        if (meInfo && meInfo.image) {
            avatarEl.innerHTML = '<img src="' + esc(meInfo.image) + '" alt="">';
        } else {
            avatarEl.innerHTML = '<span>👤</span>';
        }
        attachPresenceDot(avatarEl, USER_KEY);
    }
    toggleProfileActions();
}

document.getElementById('pf-viewing-back').addEventListener('click', exitViewingUser);

/* ─── Chat ──────────────────────────────────────────────────────
   Modal de pantalla casi completa con polling cada 1.5s mientras está
   abierto. Solo accesible desde perfil ajeno con mutual follow — el
   server reenforza el 403 si no lo es. */

var CHAT = { withUser: null, pollTimer: null, lastId: null };

function openChat(uKey) {
    if (!uKey || !isMutual(uKey)) return;
    var uInfo = PROFILE_USERS[uKey];
    if (!uInfo) return;
    CHAT.withUser = uKey;
    CHAT.lastId   = null;
    /* Limpia el badge inmediatamente al abrir el chat — el server
       actualizará last_seen en la primera llamada a get-messages. */
    if (STATE.unreadChats[uKey]) {
        delete STATE.unreadChats[uKey];
        renderUnreadBadges();
    }

    var bd = document.createElement('div');
    bd.className = 'pf-modal-backdrop';
    bd.id = 'pf-chat-backdrop';
    /* Avatar del usuario en la title-bar. Si tiene image → img, si no → inicial sobre accent. */
    var avHtml = uInfo.image
        ? '<img src="' + esc(uInfo.image) + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">'
        : '<div style="width:100%;height:100%;background:var(--accent);color:var(--accent-text,#fff);font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;">' + esc((uInfo.label || '?').charAt(0).toUpperCase()) + '</div>';
    bd.innerHTML =
        '<div class="window pf-modal pf-chat-modal">' +
            '<div class="title-bar">' +
                '<div class="title-bar-text" style="display:flex;align-items:center;gap:6px;">' +
                    '<span id="pf-chat-title-av" style="position:relative;width:18px;height:18px;display:inline-block;background:var(--inset-bg,#fff);box-shadow:-1px -1px 0 var(--bezel-dark-1,#0a0a0a), 1px 1px 0 var(--bezel-light-1,#fff);flex-shrink:0;">' + avHtml + '</span>' +
                    '<span>' + esc(uInfo.label) + '</span>' +
                '</div>' +
                '<div class="title-bar-controls">' +
                    '<button aria-label="Close" type="button"></button>' +
                '</div>' +
            '</div>' +
            '<div class="window-body pf-chat-body" style="position:relative;">' +
                '<div class="pf-chat-messages" id="pf-chat-messages">' +
                    '<div class="pf-chat-empty">Cargando…</div>' +
                '</div>' +
                '<div id="pf-chat-emoji-panel" style="display:none;position:absolute;bottom:48px;left:8px;right:8px;background:var(--win-bg,silver);padding:6px;z-index:5;box-shadow:inset -1px -1px var(--bezel-dark-1,#0a0a0a),inset 1px 1px var(--bezel-light-1,#fff),inset -2px -2px var(--bezel-dark-2,grey),inset 2px 2px var(--bezel-light-2,#dfdfdf);max-height:160px;overflow-y:auto;"></div>' +
                '<div class="pf-chat-input-row">' +
                    '<input type="text" id="pf-chat-input" maxlength="2000" placeholder="Escribe un mensaje…">' +
                    '<button class="button" id="pf-chat-emoji-btn" type="button" title="Emotes" style="padding:3px 8px;">😀</button>' +
                    '<button class="button" id="pf-chat-send" type="button">Enviar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(bd);

    bd.querySelector('.title-bar-controls button').addEventListener('click', closeChat);
    bd.addEventListener('click', function(e){ if (e.target === bd) closeChat(); });

    /* Dot de presencia del user con el que estoy chateando — pequeño,
       en la esquina inferior derecha del avatar de la title-bar. */
    var chatAv = bd.querySelector('#pf-chat-title-av');
    if (chatAv && window.__pfAttachPresenceDot) {
        window.__pfAttachPresenceDot(chatAv, uKey, true);
    }

    var input = bd.querySelector('#pf-chat-input');
    var sendBtn = bd.querySelector('#pf-chat-send');
    sendBtn.addEventListener('click', sendChatMessage);
    input.addEventListener('keydown', function(e){ if (e.key === 'Enter') sendChatMessage(); });

    /* Emoji picker — paleta de emotes. Mismo set y comportamiento que desktop. */
    var EMOTES = ['😀','😂','🥲','😅','😍','😘','🤩','🤔','🙄','😎',
                  '😭','😡','🥺','😴','🤤','🤯','🤗','🫶','🫡','👀',
                  '🥳','😏','😉','🙃','😬','😱','🤣','😋','😇','🤧',
                  '👍','👎','🙌','👏','💪','🙏','🤝','✌️','🤞','👌',
                  '❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','💖',
                  '🔥','✨','💯','⭐','🎉','🎊','🎵','🎶','🎁','☕',
                  '🍕','🍔','🍿','🍻','🍰','🌹','🌸','🌈','☀️','🌙'];
    var panel  = bd.querySelector('#pf-chat-emoji-panel');
    var emBtn  = bd.querySelector('#pf-chat-emoji-btn');
    if (panel && emBtn) {
        panel.innerHTML = EMOTES.map(function(e){
            return '<button type="button" style="background:transparent;border:0;font-size:20px;padding:4px 6px;cursor:pointer;font-family:inherit;min-height:32px;" data-em="' + e + '">' + e + '</button>';
        }).join('');
        emBtn.addEventListener('click', function(e){
            e.stopPropagation();
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });
        panel.addEventListener('click', function(e){
            var b = e.target.closest('button[data-em]');
            if (!b) return;
            var em = b.getAttribute('data-em');
            var start = input.selectionStart || input.value.length;
            var end   = input.selectionEnd   || input.value.length;
            input.value = input.value.slice(0, start) + em + input.value.slice(end);
            input.focus();
            input.setSelectionRange(start + em.length, start + em.length);
        });
        bd.addEventListener('click', function(e){
            if (panel.style.display === 'none') return;
            if (!panel.contains(e.target) && e.target !== emBtn) panel.style.display = 'none';
        });
    }

    loadChatMessages();
    CHAT.pollTimer = setInterval(loadChatMessages, 1500);
}

function closeChat() {
    if (CHAT.pollTimer) { clearInterval(CHAT.pollTimer); CHAT.pollTimer = null; }
    CHAT.withUser = null;
    CHAT.lastId   = null;
    var bd = document.getElementById('pf-chat-backdrop');
    if (bd && bd.parentNode) bd.parentNode.removeChild(bd);
}

function loadChatMessages() {
    if (!CHAT.withUser) return;
    fetch(API + '?action=get-messages&with=' + encodeURIComponent(CHAT.withUser), { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) return;
            renderChatMessages(d.messages || []);
        })
        .catch(function(){});
}

function renderChatMessages(msgs) {
    var listEl = document.getElementById('pf-chat-messages');
    if (!listEl) return;
    var lastId = msgs.length ? msgs[msgs.length - 1].id : null;
    /* Sin cambios → no repintamos para no romper scroll del usuario. */
    if (lastId === CHAT.lastId) return;
    CHAT.lastId = lastId;
    var atBottom = (listEl.scrollHeight - listEl.scrollTop - listEl.clientHeight) < 40;
    if (!msgs.length) {
        listEl.innerHTML = '<div class="pf-chat-empty">Sin mensajes todavía. Di hola 👋</div>';
        return;
    }
    var html = '';
    msgs.forEach(function(m){
        var mine = m.from === USER_KEY;
        html +=
            '<div class="pf-chat-msg ' + (mine ? 'mine' : 'theirs') + '">' +
                '<div class="pf-chat-bubble">' + esc(m.text) + '</div>' +
                '<div class="pf-chat-time">' + relTime(m.sentAt || 0) + '</div>' +
            '</div>';
    });
    listEl.innerHTML = html;
    if (atBottom) listEl.scrollTop = listEl.scrollHeight;
}

function sendChatMessage() {
    if (!CHAT.withUser) return;
    var input   = document.getElementById('pf-chat-input');
    var sendBtn = document.getElementById('pf-chat-send');
    if (!input) return;
    var text = (input.value || '').trim();
    if (!text) return;
    input.disabled = true; sendBtn.disabled = true;
    fetch(API + '?action=send-message', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ to: CHAT.withUser, text: text })
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        input.disabled = false; sendBtn.disabled = false;
        if (d && d.error) { pfAlert(d.error); return; }
        input.value = '';
        loadChatMessages();
        input.focus();
    })
    .catch(function(){
        input.disabled = false; sendBtn.disabled = false;
    });
}

document.getElementById('pf-chat-btn').addEventListener('click', function(){
    if (STATE.viewingUser) openChat(STATE.viewingUser);
});

/* ─── Notificaciones de chats sin leer ─────────────────────────
   Polling cada 10s a get-unread-chats. El server devuelve
   { userKey: unreadCount } solo para chats con mensajes posteriores
   a mi last_seen. Pintamos badge rojo en la hamburguesa (total) y en
   cada avatar de amigo en Social (per-user). "9+" si supera 9. */

var UNREAD_POLL_MS = 10000;

function loadUnreadChats() {
    fetch(API + '?action=get-unread-chats', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) return;
            var counts = d.counts || {};
            /* No muestro contador para el chat que tengo abierto ahora —
               el server lo limpiará en el siguiente tick al actualizar
               last_seen, pero localmente lo elimino ya para feedback inmediato. */
            if (CHAT.withUser && counts[CHAT.withUser]) delete counts[CHAT.withUser];
            STATE.unreadChats = counts;
            renderUnreadBadges();
        })
        .catch(function(){});
}

function fmtUnread(n) { return n > 9 ? '9+' : String(n); }

function renderUnreadBadges() {
    /* Hamburguesa: badge con la suma total de no-leídos. */
    var total = 0;
    Object.keys(STATE.unreadChats).forEach(function(k){
        total += STATE.unreadChats[k] || 0;
    });
    var hamb = document.getElementById('pf-hamburger');
    if (hamb) setBadge(hamb, total);

    /* Social view: badge por cada tarjeta de amigo. Solo si el grid
       está montado (vista activa o pre-renderizada). */
    var gridEl = document.getElementById('pf-friends-grid');
    if (gridEl) {
        gridEl.querySelectorAll('.pf-friend-card[data-user]').forEach(function(card){
            var av = card.querySelector('.pf-friend-av');
            if (!av) return;
            var n = STATE.unreadChats[card.dataset.user] || 0;
            setBadge(av, n);
        });
    }
}

function setBadge(container, n) {
    var existing = container.querySelector(':scope > .pf-unread-badge');
    if (n > 0) {
        var text = fmtUnread(n);
        if (existing) {
            existing.textContent = text;
        } else {
            var b = document.createElement('span');
            b.className = 'pf-unread-badge';
            b.textContent = text;
            container.appendChild(b);
        }
    } else if (existing) {
        existing.parentNode.removeChild(existing);
    }
}

/* Copia un item de la lista de otro usuario a mi propio perfil. Como
   STATE.lists ahora apunta a las listas del que veo, hacemos GET fresco
   de las mías, dedup por título, y POST save-lists. */
function addItemToOwnProfile(cat, item) {
    fetch(API + '?action=get-lists', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var myList = (Array.isArray(d[cat]) ? d[cat] : [])
                .filter(function(x){ return x && !x.sharedFrom; });
            var lower = (item.title || '').toLowerCase().trim();
            if (myList.some(function(x){ return (x.title || '').toLowerCase().trim() === lower; })) {
                pfAlert('"' + (item.title || 'Item') + '" ya está en tu perfil');
                return;
            }
            var copy = JSON.parse(JSON.stringify(item));
            delete copy.collaborators;
            delete copy.sharedFrom;
            delete copy.review;
            if (cat === 'music') {
                copy.id = 'music_' + Date.now();
                copy.featured = false;
                delete copy.status;
            } else {
                copy.id = 'item_' + Date.now();
                copy.status = 'pending';
            }
            myList.push(copy);
            fetch(API + '?action=save-lists', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ category: cat, items: myList })
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d && d.error) { pfAlert(d.error); return; }
                pfAlert('"' + (item.title || 'Item') + '" añadida a tu perfil');
            })
            .catch(function(){ pfAlert('Error al guardar.'); });
        })
        .catch(function(){ pfAlert('Error obteniendo el perfil.'); });
}

/* ─── Bootstrap ─────────────────────────────────────────────────── */
/* Carga inicial: fetchProfile rellena perfil + posts + following; fetchLists
   las 5 categorías. La vista por defecto es "Mis listas". */
fetchProfile();
fetchFollowers();
fetchLists();
showView('profile');
/* Polling de chats sin leer — primera carga + intervalo. */
loadUnreadChats();
setInterval(loadUnreadChats, UNREAD_POLL_MS);

/* Deep-link desde notificación push: #chat=USERKEY
   La notif del SO abrió la PWA con este hash. Esperamos a que
   fetchFollowers termine (para que isMutual funcione) y abrimos el
   chat directamente. Limpiamos el hash para no re-disparar en navs.
   También en hashchange por si el shell cambia el src del iframe a
   #chat=KEY sin recargar (mismo path, solo hash distinto). */
function handleChatHash(){
    var m = /#chat=([a-z0-9_-]+)/i.exec(location.hash);
    if (!m) return;
    var uKey = m[1];
    try { history.replaceState(null, '', location.pathname + location.search); } catch (_) {}
    /* Si ya hay un chat abierto con la misma persona, no re-abrimos. */
    if (CHAT && CHAT.withUser === uKey) return;
    fetchFollowers().then(function(){
        if (isMutual(uKey)) openChat(uKey);
    });
}
handleChatHash();
window.addEventListener('hashchange', handleChatHash);

/* ─── Notificaciones (foreground + push) ──────────────────────
   Foreground: cuando el polling detecta un cambio en unreadChats y la
   pestaña NO está activa, disparamos new Notification para que el usuario
   se entere aunque tenga otra pestaña delante.

   Background: registramos el Service Worker y nos suscribimos al
   PushManager. La VAPID public key viene del server. Si el navegador
   no soporta push (iOS < 16.4, etc.) hacemos no-op silencioso. */

var LAST_UNREAD = {};
var ORIGINAL_LOAD_UNREAD = loadUnreadChats;
loadUnreadChats = function() {
    return fetch(API + '?action=get-unread-chats', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) return;
            var counts = d.counts || {};
            if (CHAT.withUser && counts[CHAT.withUser]) delete counts[CHAT.withUser];
            /* Detect deltas — solo nuevos mensajes desde el último poll. */
            Object.keys(counts).forEach(function(k){
                var prev = LAST_UNREAD[k] || 0;
                var curr = counts[k] || 0;
                if (curr > prev && document.visibilityState !== 'visible') {
                    showForegroundNotification(k, curr - prev);
                }
            });
            LAST_UNREAD = Object.assign({}, counts);
            STATE.unreadChats = counts;
            renderUnreadBadges();
        })
        .catch(function(){});
};

function showForegroundNotification(fromKey, deltaCount) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    var uInfo = PROFILE_USERS[fromKey];
    var title = '💬 ' + ((uInfo && uInfo.label) || fromKey);
    var body  = deltaCount === 1 ? 'Nuevo mensaje' : deltaCount + ' nuevos mensajes';
    try {
        new Notification(title, {
            body: body,
            icon: (uInfo && uInfo.image) || undefined,
            tag:  'chat:' + fromKey
        });
    } catch (_) {}
}

/* La subscripción al push y el registro del SW viven en el shell
   (mobile.php) para que el permiso de notificaciones se pida al entrar
   a la PWA, no cuando entras al perfil específicamente. */

/* Embebido en el shell SPA → "‹ Menú" no navega, envía postMessage. */
(function(){
    var embedded = false;
    try { embedded = window.parent !== window; } catch (_) {}
    if (!embedded) return;
    document.addEventListener('click', function(e){
        var link = e.target && e.target.closest && e.target.closest('.mh-statusbar a[href*="../../mobile.php"]');
        if (!link) return;
        e.preventDefault();
        try { window.parent.postMessage({ type: 'shell:back' }, '*'); } catch (_) {}
    }, true);
})();
</script>

</body>
</html>
