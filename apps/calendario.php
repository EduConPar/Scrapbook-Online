<?php
session_start();
require_once dirname(__DIR__) . '/assets/config.php';
require_once dirname(__DIR__) . '/db.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../index.php');
    exit;
}

$userLabel = $loginUsers[$userKey]['label'];

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->execute([strtolower($userLabel)]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT p.id, p.fecha_inicio, u1.username as user1, u2.username as user2
    FROM parejas p
    JOIN usuarios u1 ON p.usuario1_id = u1.id
    JOIN usuarios u2 ON p.usuario2_id = u2.id
    WHERE u1.username = ? OR u2.username = ?
");
$stmt->execute([strtolower($userLabel), strtolower($userLabel)]);
$pareja = $stmt->fetch(PDO::FETCH_ASSOC);
$parejaId = $pareja ? $pareja['id'] : 0;
$fechaInicio = $pareja ? $pareja['fecha_inicio'] : null;

/* Tema activo del usuario */
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes = loadUserThemes($userKey);
$activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = '../' . themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(dirname(__DIR__) . '/' . themeCssRelPath($activeTheme, $userLabel))) $activeThemeCss = '';
}

/* Base URL dinámica para JS — sube de /apps a la raíz del proyecto */
$projectBaseUrl = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="../assets/js/pwa-guard.js"></script>
    <title>Nuestro espacio</title>
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/calendario.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
</head>
<body class="<?php
    $bc = [];
    if ($activeThemeClass) $bc[] = $activeThemeClass;
    echo htmlspecialchars(implode(' ', $bc));
?>">

<!-- BARRA SUPERIOR -->
<div style="padding: 8px 16px; display: flex; align-items: center; gap: 8px;">
    <span style="font-size: 13px; color: var(--text);">Hola, <?php echo htmlspecialchars($userLabel); ?></span>
    <?php if (!$pareja): ?>
    <button class="button" id="btn-invitar">💌 Invitar</button>
    <?php endif; ?>
</div>

<!-- VENTANA DE INVITACIÓN -->
<div class="window" id="invite-window" style="display:none; width: 280px; position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1000;">
    <div class="title-bar">
        <div class="title-bar-text">💌 Invitar pareja</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="invite-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding: 12px;">
        <p style="font-size: 11px; margin-bottom: 8px;">Selecciona a tu pareja:</p>
        <div id="user-list" style="margin-bottom: 10px;"></div>
        <p id="invite-status" style="font-size: 11px; color: green;"></p>
    </div>
</div>

<!-- NOTIFICACIÓN DE INVITACIÓN RECIBIDA -->
<div id="partner-notif" style="display:none; position: fixed; bottom: 60px; right: 16px; z-index: 5000;">
    <div class="window" style="width: 260px;">
        <div class="title-bar">
            <div class="title-bar-text">💑 Invitación de pareja</div>
        </div>
        <div class="window-body" style="padding: 10px;">
            <p id="partner-notif-msg" style="font-size: 11px; margin-bottom: 8px;"></p>
            <div class="field-row-stacked" style="margin-bottom: 8px;">
                <label style="font-size: 11px;">Fecha en que empezasteis:</label>
                <input type="date" id="partner-fecha" style="width: 100%;">
            </div>
            <div class="field-row" style="justify-content: flex-end; gap: 4px;">
                <button class="button" id="partner-reject">Rechazar</button>
                <button class="button" id="partner-accept">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA COUNTDOWN — épica, cuenta atrás con música en bucle -->
<div class="window" id="countdown-window"
     style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); width:min(1100px,96vw); z-index:9600; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text" id="countdown-title-text">⏳ Cuenta atrás</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="countdown-close"></button>
        </div>
    </div>
    <div class="window-body cd-body">
        <div class="cd-content">
            <div class="cd-title-row">
                <h1 class="cd-title" id="countdown-titulo-big">Recordatorio</h1>
            </div>
            <div class="cd-status" id="countdown-passed">QUEDAN</div>

            <div class="cd-grid">
                <div class="cd-cell">
                    <div class="cd-flap-wrap">
                        <span class="cd-flap-ghost">88</span>
                        <span class="cd-flap" id="cd-dias">00</span>
                    </div>
                    <div class="cd-lbl">DÍAS</div>
                </div>
                <div class="cd-sep">:</div>
                <div class="cd-cell">
                    <div class="cd-flap-wrap">
                        <span class="cd-flap-ghost">88</span>
                        <span class="cd-flap" id="cd-horas">00</span>
                    </div>
                    <div class="cd-lbl">HORAS</div>
                </div>
                <div class="cd-sep">:</div>
                <div class="cd-cell">
                    <div class="cd-flap-wrap">
                        <span class="cd-flap-ghost">88</span>
                        <span class="cd-flap" id="cd-mins">00</span>
                    </div>
                    <div class="cd-lbl">MIN</div>
                </div>
                <div class="cd-sep">:</div>
                <div class="cd-cell">
                    <div class="cd-flap-wrap">
                        <span class="cd-flap-ghost">88</span>
                        <span class="cd-flap" id="cd-segs">00</span>
                    </div>
                    <div class="cd-lbl">SEG</div>
                </div>
            </div>
        </div>

        <!-- Container del player YT (oculto fuera de pantalla — solo audio). -->
        <div id="countdown-yt-wrap" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;">
            <div id="countdown-yt"></div>
        </div>
    </div>
</div>
<style>
    /* ════════════════════════════════════════════════════════════
       ESTÉTICA LCD ÉPICA con colores del tema del usuario.
       - Panel negro profundo (LCD apagado).
       - Dígitos en var(--accent) con glow pulsante.
       - "Segmentos fantasma" del mismo accent al 12% (la convención
         de un LCD real: los 8 apagados se ven muy tenues detrás).
       - Bordes neon + vignette + scanlines para look retro-futurista.
       ════════════════════════════════════════════════════════════ */
    .cd-body {
        padding: 0 !important;
        position: relative;
        overflow: hidden;
        height: min(620px, 82vh);
        background:
            radial-gradient(ellipse at 50% 0%, color-mix(in srgb, var(--accent) 10%, #000) 0%, #050505 70%, #000 100%);
    }
    /* Scanlines sutiles tipo CRT antiguo. */
    .cd-body::before {
        content: '';
        position: absolute; inset: 0;
        background: repeating-linear-gradient(180deg,
            transparent 0, transparent 3px,
            rgba(0,0,0,0.18) 3px, rgba(0,0,0,0.18) 4px);
        pointer-events: none;
        z-index: 2;
        mix-blend-mode: multiply;
    }
    /* Vignette — oscurece las esquinas para concentrar la mirada. */
    .cd-body::after {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse at center,
            transparent 40%, rgba(0,0,0,0.55) 100%);
        pointer-events: none;
        z-index: 3;
    }
    .cd-content {
        position: relative;
        z-index: 5;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px;
        text-align: center;
    }
    .cd-title-row { margin-bottom: 8px; }
    .cd-title {
        margin: 0;
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: clamp(22px, 3.5vw, 36px);
        font-weight: 900;
        letter-spacing: 10px;
        text-transform: uppercase;
        color: var(--accent, #00ffaa);
        text-shadow:
            0 0 6px var(--accent, #00ffaa),
            0 0 18px var(--accent, #00ffaa),
            0 0 36px color-mix(in srgb, var(--accent) 60%, transparent);
        animation: cdTitlePulse 2.4s ease-in-out infinite alternate;
    }
    @keyframes cdTitlePulse {
        from { filter: brightness(0.95); }
        to   { filter: brightness(1.25); }
    }
    .cd-status {
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: 13px;
        letter-spacing: 14px;
        color: var(--accent, #00ffaa);
        opacity: 0.55;
        text-shadow: 0 0 6px var(--accent, #00ffaa);
        margin-bottom: 30px;
    }
    .cd-grid {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-bottom: 18px;
        padding: 24px 30px;
        background: rgba(0,0,0,0.55);
        border: 2px solid color-mix(in srgb, var(--accent) 40%, transparent);
        border-radius: 12px;
        box-shadow:
            inset 0 0 30px rgba(0,0,0,0.9),
            0 0 14px color-mix(in srgb, var(--accent) 50%, transparent),
            0 0 42px color-mix(in srgb, var(--accent) 25%, transparent),
            0 0 80px color-mix(in srgb, var(--accent) 15%, transparent);
        animation: cdPanelPulse 3s ease-in-out infinite alternate;
    }
    @keyframes cdPanelPulse {
        from {
            box-shadow:
                inset 0 0 30px rgba(0,0,0,0.9),
                0 0 10px color-mix(in srgb, var(--accent) 40%, transparent),
                0 0 30px color-mix(in srgb, var(--accent) 20%, transparent);
        }
        to {
            box-shadow:
                inset 0 0 30px rgba(0,0,0,0.9),
                0 0 20px color-mix(in srgb, var(--accent) 70%, transparent),
                0 0 60px color-mix(in srgb, var(--accent) 35%, transparent),
                0 0 100px color-mix(in srgb, var(--accent) 20%, transparent);
        }
    }
    .cd-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .cd-flap-wrap {
        position: relative;
        line-height: 1;
        /* Ancho mínimo que acomoda DOS dígitos del tamaño máximo
           (124px) sin cortar: cada "8" ~75px + letter-spacing + padding
           interno → reservamos 200-230px generosos. */
        min-width: clamp(110px, 17vw, 210px);
        padding: 0 14px;
    }
    .cd-flap-ghost,
    .cd-flap {
        font-family: 'Courier New', 'Consolas', monospace;
        font-weight: 900;
        font-size: clamp(64px, 11vw, 124px);
        line-height: 1;
        letter-spacing: 3px;
        display: block;
        white-space: nowrap;
    }
    .cd-flap-ghost {
        /* Segmentos apagados — accent al 12%. Convención LCD. */
        color: color-mix(in srgb, var(--accent) 12%, transparent);
        user-select: none;
        text-shadow: none;
    }
    .cd-flap {
        position: absolute;
        inset: 0;
        padding: inherit;
        color: var(--accent, #00ffaa);
        text-shadow:
            0 0 6px var(--accent, #00ffaa),
            0 0 18px var(--accent, #00ffaa),
            0 0 36px color-mix(in srgb, var(--accent) 70%, transparent),
            0 0 64px color-mix(in srgb, var(--accent) 40%, transparent);
        animation: cdDigitGlow 2.6s ease-in-out infinite alternate;
    }
    @keyframes cdDigitGlow {
        from {
            text-shadow:
                0 0 4px var(--accent, #00ffaa),
                0 0 12px var(--accent, #00ffaa),
                0 0 24px color-mix(in srgb, var(--accent) 60%, transparent);
            filter: brightness(0.95);
        }
        to {
            text-shadow:
                0 0 8px var(--accent, #00ffaa),
                0 0 22px var(--accent, #00ffaa),
                0 0 48px color-mix(in srgb, var(--accent) 80%, transparent),
                0 0 90px color-mix(in srgb, var(--accent) 50%, transparent);
            filter: brightness(1.25);
        }
    }
    .cd-flap.tick {
        /* "Tick" cuando el valor cambia — flash de brillo + escala. */
        animation: cdTickLcd 0.45s ease-out;
    }
    @keyframes cdTickLcd {
        0%   { filter: brightness(1);   transform: scale(1); }
        30%  { filter: brightness(2);   transform: scale(1.05); }
        60%  { filter: brightness(1.5); transform: scale(1.02); }
        100% { filter: brightness(1);   transform: scale(1); }
    }
    .cd-lbl {
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: 11px;
        letter-spacing: 5px;
        color: var(--accent, #00ffaa);
        opacity: 0.7;
        margin-top: 18px;
        font-weight: bold;
        text-shadow: 0 0 6px var(--accent, #00ffaa);
    }
    .cd-sep {
        font-family: 'Courier New', 'Consolas', monospace;
        font-size: clamp(52px, 10vw, 100px);
        font-weight: 900;
        color: var(--accent, #00ffaa);
        animation: cdColonBlinkLcd 1s steps(2, start) infinite;
        align-self: flex-start;
        padding-top: 10px;
        text-shadow:
            0 0 8px var(--accent, #00ffaa),
            0 0 18px var(--accent, #00ffaa);
    }
    @keyframes cdColonBlinkLcd {
        0%, 100% { opacity: 1; }
        50%      { opacity: 0.15; }
    }
</style>

<!-- POPUP DÍA -->
<div class="popup-overlay" id="popup-dia">
    <div class="window" style="width: 380px; max-height: 85vh; display: flex; flex-direction: column;">
        <div class="title-bar">
            <div class="title-bar-text" id="popup-titulo">📅 Día</div>
            <div class="title-bar-controls">
                <button aria-label="Close" id="popup-close"></button>
            </div>
        </div>
        <div class="window-body" style="padding: 12px; overflow-y: auto; flex: 1;">
            <div id="popup-contenido"></div>
        </div>
    </div>
</div>

<!-- LAYOUT PRINCIPAL -->
<div style="display: grid; grid-template-columns: 180px 1fr 240px; gap: 12px; padding: 0 16px 16px 16px; height: calc(100vh - 50px); box-sizing: border-box;">

    <!-- IZQUIERDA -->
    <div style="display: flex; flex-direction: column; gap: 12px; overflow: hidden;">

        <?php if ($pareja): ?>
        <div class="window">
            <div class="title-bar"><div class="title-bar-text">💕 Tiempo juntos</div></div>
            <div class="window-body" style="padding: 12px; text-align: center;">
                <div id="dias-contador" style="font-size: 28px; font-weight: bold;"></div>
                <div style="font-size: 11px; margin-top: 4px;">días juntos</div>
                <div style="font-size: 10px; color: #808080; margin-top: 6px;">Desde <?php echo $pareja['fecha_inicio']; ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- AÑADIR MOMENTO -->
        <div class="window" style="overflow: auto;">
            <div class="title-bar"><div class="title-bar-text">📸 Añadir momento</div></div>
            <div class="window-body" style="padding: 10px;">
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Título</label>
                    <input type="text" id="momento-titulo" style="width: 100%;">
                </div>
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Fecha</label>
                    <input type="date" id="momento-fecha" style="width: 100%;">
                </div>
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Descripción</label>
                    <textarea id="momento-desc" style="width: 100%; height: 50px;"></textarea>
                </div>
                <div class="field-row-stacked" style="margin-bottom: 8px;">
                    <label style="font-size: 11px;">Foto URL (opcional)</label>
                    <div class="field-row" style="gap:4px;">
                        <input type="text" id="momento-foto-url" placeholder="https://..." style="flex:1;min-width:0;cursor:default;font-size:11px;height:21px;">
                    </div>
                    <input type="file" id="momento-foto" accept="image/*" style="display:none;">
                </div>
                <button class="button" id="btn-guardar-momento" style="width: 100%;">Guardar</button>
                <p id="momento-status" style="font-size: 11px; margin-top: 6px;"></p>
            </div>
        </div>

        <!-- AÑADIR RECORDATORIO -->
        <div class="window" style="overflow: auto;">
            <div class="title-bar"><div class="title-bar-text"><img src="../assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Añadir recordatorio</div></div>
            <div class="window-body" style="padding: 10px;">
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Título</label>
                    <input type="text" id="rec-titulo" style="width: 100%;">
                </div>
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Fecha</label>
                    <input type="date" id="rec-fecha" style="width: 100%;">
                </div>
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Descripción</label>
                    <textarea id="rec-desc" style="width: 100%; height: 40px;"></textarea>
                </div>
                <div class="field-row-stacked" style="margin-bottom: 8px;">
                    <label style="font-size: 11px;">Periodicidad</label>
                    <select id="rec-periodicidad" style="width: 100%;">
                        <option value="ninguna">— Sin repetición —</option>
                        <option value="anual">📅 Anual (cada año)</option>
                        <option value="mensual">🗓️ Mensual (cada mes)</option>
                        <option value="semanal">📆 Semanal (cada semana)</option>
                    </select>
                </div>
                <button class="button" id="btn-guardar-rec" style="width: 100%;">Guardar</button>
                <p id="rec-status" style="font-size: 11px; margin-top: 6px;"></p>
            </div>
        </div>

    </div>

    <!-- CENTRO: Calendario -->
    <div class="window" style="display: flex; flex-direction: column; overflow: hidden;">
        <div class="title-bar"><div class="title-bar-text"> Calendario</div></div>
        <div class="window-body" style="padding: 16px; flex: 1; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <button class="button" id="prev-month">◄</button>
                <span id="month-label" style="font-size: 18px; font-weight: bold;"></span>
                <button class="button" id="next-month">►</button>
            </div>
            <div style="font-size: 10px; margin-bottom: 8px; display: flex; gap: 12px;">
                <span><span style="color:#ff69b4;">●</span> Momentos</span>
                <span><span style="color:#4a90d9;">●</span> Recordatorios</span>
            </div>
            <div id="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; font-size: 13px; flex: 1;"></div>
        </div>
    </div>

    <!-- DERECHA: Recordatorios (próximos 14 días) -->
    <div style="display: flex; flex-direction: column; gap: 12px; overflow: hidden;">

        <div class="window" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
            <div class="title-bar"><div class="title-bar-text"><img src="../assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Recordatorios</div></div>
            <div class="window-body" style="padding: 10px; flex: 1; overflow-y: auto;">
                <div id="recordatorios-lista"><p style="font-size:11px;color:#808080;">Cargando...</p></div>
            </div>
        </div>

    </div>

</div>

<script>
const API_BASE = '<?php echo htmlspecialchars($projectBaseUrl); ?>/assets/couple/api.php';

const parejaId = <?php echo $parejaId; ?>;
const fechaInicio = '<?php echo $fechaInicio; ?>';
const hoy = new Date();

<?php if ($pareja): ?>
const inicio = new Date(fechaInicio);
const dias = Math.floor((hoy - inicio) / (1000 * 60 * 60 * 24));
document.getElementById('dias-contador').textContent = dias;
<?php endif; ?>

let currentYear = hoy.getFullYear();
let currentMonth = hoy.getMonth();
let momentosPorFecha = {};
let recordatoriosPorFecha = {};
let todosMomentos = [];
const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const diasSemana = ['Lu','Ma','Mi','Ju','Vi','Sá','Do'];

function expandirRecordatorios(lista) {
    const expandidos = [];
    const desde = new Date(hoy); desde.setFullYear(desde.getFullYear() - 1);
    const hasta = new Date(hoy); hasta.setFullYear(hasta.getFullYear() + 2);

    lista.forEach(r => {
        const p = r.periodicidad || 'ninguna';
        if (p === 'ninguna') {
            expandidos.push(r);
            return;
        }
        const base = new Date(r.fecha);
        let cursor = new Date(base);

        while (cursor > desde) {
            if (p === 'anual')        cursor.setFullYear(cursor.getFullYear() - 1);
            else if (p === 'mensual') cursor.setMonth(cursor.getMonth() - 1);
            else if (p === 'semanal') cursor.setDate(cursor.getDate() - 7);
        }

        while (cursor <= hasta) {
            const fechaOcurrencia = cursor.toISOString().split('T')[0];
            expandidos.push({ ...r, fecha: fechaOcurrencia, _periodico: true });
            if (p === 'anual')        cursor.setFullYear(cursor.getFullYear() + 1);
            else if (p === 'mensual') cursor.setMonth(cursor.getMonth() + 1);
            else if (p === 'semanal') cursor.setDate(cursor.getDate() + 7);
        }
    });
    return expandidos;
}

function cargarTodo() {
    Promise.all([
        fetch(API_BASE + '?action=get-momentos&pareja_id=' + parejaId).then(r => r.json()),
        fetch(API_BASE + '?action=get-recordatorios&pareja_id=' + parejaId).then(r => r.json())
    ]).then(([momentos, recordatorios]) => {
        todosMomentos = momentos;
        momentosPorFecha = {};
        momentos.forEach(m => {
            if (!momentosPorFecha[m.fecha]) momentosPorFecha[m.fecha] = [];
            momentosPorFecha[m.fecha].push(m);
        });

        const recordatoriosExpandidos = expandirRecordatorios(recordatorios);
        recordatoriosPorFecha = {};
        recordatoriosExpandidos.forEach(r => {
            if (!recordatoriosPorFecha[r.fecha]) recordatoriosPorFecha[r.fecha] = [];
            recordatoriosPorFecha[r.fecha].push(r);
        });

        renderCalendario();
        renderRecordatorios(recordatoriosExpandidos);
        /* Sidebar de "Momentos" eliminado — la sidebar derecha solo
           muestra recordatorios próximos. */
    }).catch(() => renderCalendario());
}

function renderRecordatorios(lista) {
    const div = document.getElementById('recordatorios-lista');

    /* Solo mostrar los que sean dentro de los próximos 14 días
       (hoy incluido). Cualquier fecha pasada o más allá de 2 semanas
       queda fuera de la sidebar — siguen viéndose en el grid del mes. */
    const hoyStr = hoy.toISOString().split('T')[0];
    const limite = new Date(hoy);
    limite.setDate(limite.getDate() + 14);
    const limiteStr = limite.toISOString().split('T')[0];
    lista = lista.filter(r => r.fecha >= hoyStr && r.fecha <= limiteStr);

    if (!lista.length) {
        div.innerHTML = '<p style="font-size:11px;color:#808080;">Sin recordatorios en las próximas 2 semanas.</p>';
        return;
    }

    lista.sort((a,b) => a.fecha.localeCompare(b.fecha));
    div.innerHTML = '';
    lista.forEach(r => {
        /* La lista ya viene filtrada al rango hoy..hoy+14, así que no
           hay "pasado" — todo es 0..14 días. */
        const item = document.createElement('div');
        item.style.cssText = 'border: 1px solid #4a90d9; padding: 6px; margin-bottom: 6px; font-size: 11px; border-radius: 2px; display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;';
        const diasRestantes = Math.ceil((new Date(r.fecha) - hoy) / (1000*60*60*24));
        const cuandoStr = diasRestantes === 0 ? '¡Hoy!' : 'En ' + diasRestantes + ' día' + (diasRestantes === 1 ? '' : 's');
        const periodicoLabel = r.periodicidad && r.periodicidad !== 'ninguna' ? ' · 🔁 ' + r.periodicidad : '';
        const texto = document.createElement('div');
        texto.style.cssText = 'flex: 1;';
        texto.innerHTML = '<strong>' + r.titulo + '</strong><br>' +
            '<span style="color:#808080;">' + r.fecha + ' · ' + cuandoStr + periodicoLabel + (r.autor ? ' · ' + r.autor : '') + '</span>' +
            (r.descripcion ? '<br>' + r.descripcion : '');
        const btns = document.createElement('div');
        btns.style.cssText = 'display:flex;flex-direction:column;gap:3px;flex-shrink:0;';
        const btnVer = document.createElement('button');
        btnVer.className = 'button';
        btnVer.textContent = '👁';
        btnVer.title = 'Ver cuenta atrás';
        btnVer.style.cssText = 'font-size: 10px; padding: 1px 4px;';
        btnVer.addEventListener('click', () => abrirCountdown(r));
        const btnDel = document.createElement('button');
        btnDel.className = 'button';
        btnDel.textContent = '✕';
        btnDel.style.cssText = 'font-size: 10px; padding: 1px 4px;';
        btnDel.addEventListener('click', () => eliminarRecordatorio(r.id));
        btns.appendChild(btnVer);
        btns.appendChild(btnDel);
        item.appendChild(texto);
        item.appendChild(btns);
        div.appendChild(item);
    });
}

/* ════════════════════════════════════════════════════════════════
   CUENTA ATRÁS — ventana modal con D/H/M/S restantes hasta el
   recordatorio. Música de fondo: YouTube tvoh8bVTLUQ a partir del
   minuto 2:09 en bucle hasta cerrar.
   ════════════════════════════════════════════════════════════════ */
const COUNTDOWN_VIDEO_ID = 'tvoh8bVTLUQ';
const COUNTDOWN_START_SEC = 129;  /* 2 min 09 s */
let countdownTimer = null;
let countdownYtPlayer = null;
let countdownYtReady = false;
let countdownTargetMs = 0;
let countdownTitulo = '';

/* Carga la YT IFrame API una sola vez. */
(function loadCountdownYtApi() {
    if (window.YT && window.YT.Player) { countdownYtReady = true; return; }
    var s = document.createElement('script');
    s.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(s);
    /* Callback global de YouTube API. Si ya existía otro, lo encadena. */
    var prev = window.onYouTubeIframeAPIReady;
    window.onYouTubeIframeAPIReady = function() {
        countdownYtReady = true;
        if (prev) try { prev(); } catch(_) {}
    };
})();

function abrirCountdown(rec) {
    countdownTargetMs = new Date(rec.fecha + 'T00:00:00').getTime();
    countdownTitulo = rec.titulo || 'Recordatorio';
    document.getElementById('countdown-title-text').textContent = '⏳ ' + countdownTitulo;
    const bigTitle = document.getElementById('countdown-titulo-big');
    if (bigTitle) bigTitle.textContent = countdownTitulo;
    actualizarCountdown(true);
    if (countdownTimer) clearInterval(countdownTimer);
    countdownTimer = setInterval(actualizarCountdown, 1000);
    const win = document.getElementById('countdown-window');
    win.style.display = 'flex';
    if (!win.dataset.dragWired) {
        if (window._calMakeDraggable) window._calMakeDraggable(win);
    }
    iniciarMusicaCountdown();
}

function cerrarCountdown() {
    document.getElementById('countdown-window').style.display = 'none';
    if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null; }
    pararMusicaCountdown();
}

function actualizarCountdown(forceAll) {
    const ahora = Date.now();
    let restante = countdownTargetMs - ahora;
    const pasado = restante < 0;
    if (pasado) restante = -restante;
    const dias  = Math.floor(restante / (1000 * 60 * 60 * 24));
    const horas = Math.floor((restante / (1000 * 60 * 60)) % 24);
    const mins  = Math.floor((restante / (1000 * 60)) % 60);
    const segs  = Math.floor((restante / 1000) % 60);
    /* `tick` solo cuando el valor cambia → escala+brillo pulsado solo
       en la celda que se mueve, no en todas a la vez. */
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (!el) return;
        const str = String(val).padStart(2, '0');
        if (forceAll || el.textContent !== str) {
            el.textContent = str;
            el.classList.remove('tick');
            /* Force reflow para reiniciar la animación. */
            void el.offsetWidth;
            el.classList.add('tick');
        }
    };
    set('cd-dias', dias);
    set('cd-horas', horas);
    set('cd-mins', mins);
    set('cd-segs', segs);
    document.getElementById('countdown-passed').textContent = pasado ? 'PASARON' : 'QUEDAN';
}

function iniciarMusicaCountdown() {
    /* Si la API aún no terminó de cargarse, reintenta cada 200ms. */
    if (!countdownYtReady || !window.YT || !window.YT.Player) {
        setTimeout(iniciarMusicaCountdown, 200);
        return;
    }
    /* Player nuevo en cada apertura: garantiza arrancar en 2:09. */
    if (countdownYtPlayer) {
        try { countdownYtPlayer.destroy(); } catch(_) {}
        countdownYtPlayer = null;
    }
    try {
        countdownYtPlayer = new YT.Player('countdown-yt', {
            videoId: COUNTDOWN_VIDEO_ID,
            playerVars: {
                autoplay: 1,
                start:    COUNTDOWN_START_SEC,
                controls: 0,
                modestbranding: 1,
                rel:      0,
                playsinline: 1,
                loop:     0,   /* loop manual con onStateChange — más fiable */
            },
            events: {
                onReady: function(e) {
                    try { e.target.playVideo(); } catch(_) {}
                    try { e.target.setVolume(70); } catch(_) {}
                },
                onStateChange: function(e) {
                    /* 0 = ENDED. Al terminar volvemos a 2:09. */
                    if (e.data === 0) {
                        try { e.target.seekTo(COUNTDOWN_START_SEC, true); } catch(_) {}
                        try { e.target.playVideo(); } catch(_) {}
                    }
                }
            }
        });
    } catch(_) {}
}

function pararMusicaCountdown() {
    if (!countdownYtPlayer) return;
    try { countdownYtPlayer.stopVideo(); } catch(_) {}
    try { countdownYtPlayer.destroy(); } catch(_) {}
    countdownYtPlayer = null;
}

function renderCalendario() {
    document.getElementById('month-label').textContent = meses[currentMonth] + ' ' + currentYear;
    const grid = document.getElementById('calendar-grid');
    grid.innerHTML = '';

    diasSemana.forEach(d => {
        const cell = document.createElement('div');
        cell.textContent = d;
        cell.style.cssText = 'font-weight:bold; padding: 8px 4px; color: #808080; border-bottom: 1px solid #c0c0c0; text-align:center;';
        grid.appendChild(cell);
    });

    const primerDia = new Date(currentYear, currentMonth, 1).getDay();
    const offset = primerDia === 0 ? 6 : primerDia - 1;
    const totalDias = new Date(currentYear, currentMonth + 1, 0).getDate();

    for (let i = 0; i < offset; i++) {
        const empty = document.createElement('div');
        empty.className = 'cal-cell';
        empty.style.cursor = 'default';
        empty.style.borderColor = 'transparent';
        grid.appendChild(empty);
    }

    for (let d = 1; d <= totalDias; d++) {
        const cell = document.createElement('div');
        const fechaStr = currentYear + '-' + String(currentMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const momentosDelDia = momentosPorFecha[fechaStr] || [];
        const tieneRecordatorios = recordatoriosPorFecha[fechaStr] && recordatoriosPorFecha[fechaStr].length > 0;
        const esHoy = d === hoy.getDate() && currentMonth === hoy.getMonth() && currentYear === hoy.getFullYear();
        const momentoConFoto = momentosDelDia.find(m => m.foto);

        cell.className = 'cal-cell';

        if (momentoConFoto) {
            cell.style.backgroundImage = 'url(' + (momentoConFoto.foto.startsWith('http') ? momentoConFoto.foto : '../uploads/momentos/' + momentoConFoto.foto) + ')';
            cell.classList.add('cal-cell-foto');
            const num = document.createElement('div');
            num.className = 'cal-cell-num';
            num.textContent = d;
            cell.appendChild(num);
        } else if (esHoy) {
            cell.style.borderColor = 'transparent';
            const barra = document.createElement('div');
            barra.className = 'cal-cell-today';
            barra.textContent = d;
            cell.appendChild(barra);
        } else {
            const num = document.createElement('div');
            num.className = 'cal-cell-num';
            num.textContent = d;
            cell.appendChild(num);
        }

        if (momentosDelDia.length > 0 || tieneRecordatorios) {
            const dots = document.createElement('div');
            dots.style.cssText = 'display:flex; justify-content:center; gap:2px; padding-bottom:3px;';
            if (momentosDelDia.length > 0) {
                const dot = document.createElement('div');
                dot.style.cssText = 'width:5px;height:5px;background:#ff69b4;border-radius:50%;';
                dots.appendChild(dot);
            }
            if (tieneRecordatorios) {
                const dot = document.createElement('div');
                dot.style.cssText = 'width:5px;height:5px;background:#4a90d9;border-radius:50%;';
                dots.appendChild(dot);
            }
            cell.appendChild(dots);
        }

        cell.addEventListener('click', () => abrirPopupDia(fechaStr));
        grid.appendChild(cell);
    }
}

function abrirPopupDia(fecha) {
    const momentos = momentosPorFecha[fecha] || [];
    const recordatorios = recordatoriosPorFecha[fecha] || [];

    document.getElementById('popup-titulo').textContent = '📅 ' + fecha;
    const contenido = document.getElementById('popup-contenido');
    contenido.innerHTML = '';

    if (!momentos.length && !recordatorios.length) {
        contenido.innerHTML = '<p style="font-size:11px;color:#808080;">No hay nada este día.</p>';
    }

    if (momentos.length) {
        const h = document.createElement('p');
        h.style.cssText = 'font-size:11px; font-weight:bold; color:#ff69b4; margin-bottom:6px;';
        h.textContent = '💗 Momentos';
        contenido.appendChild(h);
        momentos.forEach(m => {
            const div = document.createElement('div');
            div.style.cssText = 'border: 1px solid #ff69b4; padding: 8px; margin-bottom: 8px; font-size: 11px; border-radius: 2px;';
            let html = '';
            if (m.foto) {
               html += '<img src="' + (m.foto.startsWith('http') ? m.foto : '../uploads/momentos/' + m.foto) + '" style="width:100%; max-height:180px; object-fit:cover; border-radius:2px; margin-bottom:6px; display:block;">';
            }
            html += '<div style="display:flex; justify-content:space-between; align-items:flex-start;">';
            html += '<div><strong>' + m.titulo + '</strong>';
            if (m.autor) html += ' <span style="color:#808080;">(' + m.autor + ')</span>';
            if (m.descripcion) html += '<br>' + m.descripcion;
            html += '</div>';
            html += '<button class="button" onclick="eliminarMomento(' + m.id + ')" style="font-size:10px;padding:1px 4px;flex-shrink:0;margin-left:6px;">✕</button>';
            html += '</div>';
            div.innerHTML = html;
            contenido.appendChild(div);
        });
    }

    if (recordatorios.length) {
        const h = document.createElement('p');
        h.style.cssText = 'font-size:11px; font-weight:bold; color:#4a90d9; margin-bottom:6px; margin-top:8px;';
        h.textContent = '🔔 Recordatorios';
        contenido.appendChild(h);
        recordatorios.forEach(r => {
            const div = document.createElement('div');
            div.style.cssText = 'border: 1px solid #4a90d9; padding: 6px; margin-bottom: 6px; font-size: 11px; border-radius: 2px; display:flex; justify-content:space-between; align-items:flex-start;';
            const periodicoLabel = r.periodicidad && r.periodicidad !== 'ninguna' ? ' <span style="color:#808080;">· 🔁 ' + r.periodicidad + '</span>' : '';
            const texto = document.createElement('div');
            texto.innerHTML = '<strong>' + r.titulo + '</strong>' + periodicoLabel +
                (r.descripcion ? '<br>' + r.descripcion : '');
            const btnDel = document.createElement('button');
            btnDel.className = 'button';
            btnDel.textContent = '✕';
            btnDel.style.cssText = 'font-size:10px;padding:1px 4px;flex-shrink:0;margin-left:6px;';
            btnDel.addEventListener('click', () => eliminarRecordatorio(r.id));
            div.appendChild(texto);
            div.appendChild(btnDel);
            contenido.appendChild(div);
        });
    }

    document.getElementById('popup-dia').classList.add('active');
    document.getElementById('momento-fecha').value = fecha;
    document.getElementById('rec-fecha').value = fecha;
}

document.getElementById('popup-close').addEventListener('click', () => {
    document.getElementById('popup-dia').classList.remove('active');
});
document.getElementById('popup-dia').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});

document.getElementById('prev-month').addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    renderCalendario();
});

document.getElementById('next-month').addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    renderCalendario();
});

document.getElementById('btn-guardar-momento').addEventListener('click', function() {
    const titulo = document.getElementById('momento-titulo').value.trim();
    const fecha  = document.getElementById('momento-fecha').value;
    const desc   = document.getElementById('momento-desc').value.trim();
    const foto   = document.getElementById('momento-foto-url').value.trim();
    const status = document.getElementById('momento-status');

    if (!titulo || !fecha) {
        status.style.color = 'red';
        status.textContent = 'Título y fecha son obligatorios.';
        return;
    }

    status.style.color = '#808080';
    status.textContent = 'Guardando...';

    fetch(API_BASE + '?action=save-momento', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pareja_id: parejaId, titulo, fecha, descripcion: desc, foto })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { status.style.color = 'red'; status.textContent = data.error; return; }
        status.style.color = 'green';
        status.textContent = '✅ Guardado';
        document.getElementById('momento-titulo').value = '';
        document.getElementById('momento-desc').value  = '';
        document.getElementById('momento-foto-url').value = '';
        cargarTodo();
    });
});

document.getElementById('btn-guardar-rec').addEventListener('click', function() {
    const titulo = document.getElementById('rec-titulo').value.trim();
    const fecha = document.getElementById('rec-fecha').value;
    const desc = document.getElementById('rec-desc').value.trim();
    const periodicidad = document.getElementById('rec-periodicidad').value;
    const status = document.getElementById('rec-status');

    if (!titulo || !fecha) { status.style.color = 'red'; status.textContent = 'Título y fecha son obligatorios.'; return; }

    fetch(API_BASE + '?action=save-recordatorio', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pareja_id: parejaId, titulo, fecha, descripcion: desc, periodicidad })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { status.style.color = 'red'; status.textContent = data.error; return; }
        status.style.color = 'green'; status.textContent = '✅ Guardado';
        document.getElementById('rec-titulo').value = '';
        document.getElementById('rec-desc').value = '';
        document.getElementById('rec-periodicidad').value = 'ninguna';
        cargarTodo();
    });
});

function eliminarMomento(id) {
    window._calConfirm('¿Eliminar este <strong>momento</strong>?', function(){
        fetch(API_BASE + '?action=delete-momento', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            document.getElementById('popup-dia').classList.remove('active');
            cargarTodo();
        });
    });
}

function eliminarRecordatorio(id) {
    window._calConfirm('¿Eliminar este <strong>recordatorio</strong>?', function(){
        _doEliminarRecordatorio(id);
    });
}
function _doEliminarRecordatorio(id) {
    fetch(API_BASE + '?action=delete-recordatorio', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        document.getElementById('popup-dia').classList.remove('active');
        cargarTodo();
    });
}

<?php if (!$pareja): ?>
document.getElementById('btn-invitar').addEventListener('click', function() {
    document.getElementById('invite-window').style.display = 'block';
    fetch(API_BASE + '?action=get-users')
    .then(r => r.json())
    .then(users => {
        const list = document.getElementById('user-list');
        list.innerHTML = '';
        if (!users.length) {
            list.innerHTML = '<div style="text-align:center;padding:12px 6px;font-size:12px;line-height:1.45;">Aún no tienes amigos que invitar.<br><span style="opacity:0.75;font-size:11px;">Seguíos entre vosotros para haceros amigos.</span></div>';
            return;
        }
        users.forEach(u => {
            const btn = document.createElement('button');
            btn.className = 'button';
            btn.textContent = u.label;
            btn.style.cssText = 'width:100%;margin-bottom:4px;';
            btn.addEventListener('click', function() {
                btn.disabled = true;
                fetch(API_BASE + '?action=invite-partner', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ toUser: u.key })
                })
                .then(r => r.json())
                .then(data => {
                    const status = document.getElementById('invite-status');
                    if (data.error) { status.style.color = 'red'; status.textContent = data.error; btn.disabled = false; return; }
                    status.style.color = 'green';
                    status.textContent = '✅ Invitación enviada a ' + u.label;
                });
            });
            list.appendChild(btn);
        });
    });
});

document.getElementById('invite-close').addEventListener('click', function() {
    document.getElementById('invite-window').style.display = 'none';
});

let currentPartnerInvite = null;

function checkPartnerInvites() {
    fetch(API_BASE + '?action=get-partner-invites')
    .then(r => r.json())
    .then(data => {
        if (!Array.isArray(data) || !data.length) return;
        const inv = data[0];
        if (currentPartnerInvite && currentPartnerInvite.id === inv.id) return;
        currentPartnerInvite = inv;
        document.getElementById('partner-notif-msg').textContent = inv.fromLabel + ' quiere ser tu pareja 💕';
        document.getElementById('partner-notif').style.display = 'block';
    })
    .catch(() => {});
}

function respondInvite(action) {
    if (!currentPartnerInvite) return;
    const fecha = document.getElementById('partner-fecha').value;
    if (action === 'accept' && !fecha) { alert('Por favor introduce la fecha en que empezasteis.'); return; }
    fetch(API_BASE + '?action=respond-partner-invite', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ inviteId: currentPartnerInvite.id, action: action, fecha: fecha })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        document.getElementById('partner-notif').style.display = 'none';
        if (action === 'accept') location.reload();
    });
}

document.getElementById('partner-accept').addEventListener('click', () => respondInvite('accept'));
document.getElementById('partner-reject').addEventListener('click', () => respondInvite('reject'));

checkPartnerInvites();
setInterval(checkPartnerInvites, 5000);
<?php endif; ?>

document.getElementById('momento-foto-browse').addEventListener('click', function() {
    document.getElementById('momento-foto').click();
});
document.getElementById('momento-foto').addEventListener('change', function() {
    document.getElementById('momento-foto-nombre').value = this.files.length ? this.files[0].name : '';
});

/* Purgar recordatorios no periódicos ya pasados antes de cargar */
fetch(API_BASE + '?action=purge-recordatorios&pareja_id=' + parejaId, { method: 'POST' })
    .finally(() => cargarTodo());

/* Refresca cuando otro contexto (ej. perfil que crea un momento al
   "completar" un item) avisa vía postMessage. El parent es la ventana
   del desktop o del shell móvil. */
window.addEventListener('message', function(e) {
    if (!e.data || typeof e.data !== 'object') return;
    if (e.data.type === 'momento-saved' || e.data.type === 'recordatorio-saved') {
        cargarTodo();
    }
});

/* ════════════════════════════════════════════════════════════════
   DRAGGABLE — todas las ventanas popup (invite, día, partner-notif,
   confirmar). El primer mousedown sobre la title-bar convierte la
   posición CSS (que puede usar transform:translate(-50%,-50%)) a
   left/top absolutos en px y elimina el transform → a partir de ahí
   left/top son la fuente de verdad. Compatible con .window de 98.css.
   ════════════════════════════════════════════════════════════════ */
(function setupCalendarDrag() {
    function makeDraggable(winEl) {
        if (!winEl || winEl.dataset.dragWired === '1') return;
        var titleBar = winEl.querySelector('.title-bar');
        if (!titleBar) return;
        winEl.dataset.dragWired = '1';
        titleBar.style.cursor = 'move';
        titleBar.style.userSelect = 'none';
        var dragging = false;
        var startX = 0, startY = 0;
        var winX = 0, winY = 0;
        function onMouseDown(e) {
            /* Ignorar clicks en botones de la title-bar (close/min/max). */
            if (e.target.closest('.title-bar-controls')) return;
            if (e.button !== undefined && e.button !== 0) return;
            /* Si todavía está centrado vía transform, convertir a left/top
               absolutos antes del primer drag. */
            var r = winEl.getBoundingClientRect();
            winEl.style.transform = 'none';
            winEl.style.left = r.left + 'px';
            winEl.style.top  = r.top  + 'px';
            winEl.style.margin = '0';
            dragging = true;
            startX = e.clientX; startY = e.clientY;
            winX = r.left; winY = r.top;
            e.preventDefault();
        }
        function onMouseMove(e) {
            if (!dragging) return;
            var dx = e.clientX - startX, dy = e.clientY - startY;
            var nx = winX + dx, ny = winY + dy;
            /* Limita al viewport (deja al menos 20px visible). */
            var vw = window.innerWidth, vh = window.innerHeight;
            var w = winEl.offsetWidth, h = winEl.offsetHeight;
            nx = Math.max(20 - w, Math.min(vw - 20, nx));
            ny = Math.max(0,      Math.min(vh - 20, ny));
            winEl.style.left = nx + 'px';
            winEl.style.top  = ny + 'px';
        }
        function onMouseUp() { dragging = false; }
        titleBar.addEventListener('mousedown', onMouseDown);
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup',   onMouseUp);
        /* Soporte táctil básico. */
        titleBar.addEventListener('touchstart', function(e) {
            if (e.touches.length !== 1) return;
            var t = e.touches[0];
            onMouseDown({ clientX: t.clientX, clientY: t.clientY, button: 0, target: t.target, preventDefault: function(){ e.preventDefault(); } });
        }, { passive: false });
        document.addEventListener('touchmove', function(e) {
            if (!dragging || e.touches.length !== 1) return;
            var t = e.touches[0];
            onMouseMove({ clientX: t.clientX, clientY: t.clientY });
            e.preventDefault();
        }, { passive: false });
        document.addEventListener('touchend', onMouseUp);
    }
    window._calMakeDraggable = makeDraggable;

    /* Inicializa los popups que ya existen en el DOM. El popup-dia
       contiene la .window dentro de un .popup-overlay → drag aplica
       a la .window interna. */
    [
        document.getElementById('invite-window'),
        document.querySelector('#partner-notif > .window'),
        document.querySelector('#popup-dia > .window'),
        document.getElementById('cal-confirm-modal'),
        document.getElementById('countdown-window'),
    ].forEach(function(w) { if (w) makeDraggable(w); });
})();

/* Cierre de la ventana countdown — para la música y limpia el timer. */
document.getElementById('countdown-close').addEventListener('click', cerrarCountdown);

/* ─── Modal Win98 de confirmación ─── */
(function(){
    function open(text, onOk){
        var modal = document.getElementById('cal-confirm-modal');
        document.getElementById('cal-confirm-text').innerHTML = text;
        modal.style.display = 'flex';
        function cleanup(){
            modal.style.display = 'none';
            document.getElementById('cal-confirm-ok').onclick = null;
            document.getElementById('cal-confirm-cancel').onclick = null;
            document.getElementById('cal-confirm-x').onclick = null;
            document.removeEventListener('keydown', keyHandler);
        }
        function ok(){ cleanup(); onOk(); }
        function cancel(){ cleanup(); }
        function keyHandler(ev){
            if(ev.key === 'Enter'){ ev.preventDefault(); ok(); }
            else if(ev.key === 'Escape'){ ev.preventDefault(); cancel(); }
        }
        document.getElementById('cal-confirm-ok').onclick = ok;
        document.getElementById('cal-confirm-cancel').onclick = cancel;
        document.getElementById('cal-confirm-x').onclick = cancel;
        document.addEventListener('keydown', keyHandler);
    }
    window._calConfirm = open;
})();
</script>

<!-- Modal Win98 compartido para confirmar eliminaciones -->
<div class="window" id="cal-confirm-modal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); min-width:340px; max-width:460px; z-index:9500; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text" id="cal-confirm-title">Confirmar eliminación</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="cal-confirm-x"></button>
        </div>
    </div>
    <div class="window-body w98-confirm-body">
        <div class="w98-confirm-row">
            <div class="w98-icon-question"></div>
            <div class="w98-confirm-text" id="cal-confirm-text">¿Eliminar?</div>
        </div>
        <div class="w98-confirm-btns">
            <button id="cal-confirm-ok" class="default">Sí</button>
            <button id="cal-confirm-cancel">No</button>
        </div>
    </div>
</div>

</body>
</html>