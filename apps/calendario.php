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
    <script src="../assets/js/icon-pack.js"></script>
    <?php require_once dirname(__DIR__) . "/assets/php/active-interface.php"; emitInterfaceCss("../"); ?>
    <script src="../assets/js/interface-loader.js?v=fs1"></script>
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
    <button class="button" id="btn-invitar">Invitar</button>
    <?php endif; ?>
    <button class="button" id="btn-eventos">Eventos</button>
</div>

<!-- VENTANA EVENTOS (lista de eventos disponibles) -->
<div class="window" id="events-window"
     style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%);
            width:min(560px,94vw); height:min(560px,86vh); z-index:9500; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text">Eventos</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="events-close"></button>
        </div>
    </div>
    <div class="window-body" style="flex:1; min-height:0; display:flex; flex-direction:column; padding:8px 10px;">
        <div style="display:flex; gap:6px; margin-bottom:8px;">
            <button class="button" id="events-tab-list">Lista</button>
            <button class="button" id="events-tab-create">Crear evento</button>
            <span style="flex:1;"></span>
            <button class="button" id="events-refresh" title="Refrescar lista">↻</button>
        </div>
        <!-- TAB LISTA -->
        <div id="events-pane-list" style="flex:1; min-height:0; overflow:auto; border:1px inset; padding:6px; background:var(--win-body-bg, var(--win-bg));">
            <div id="events-list-empty" style="display:none; text-align:center; font-size:11px; padding:20px; color:var(--text-faint,#666);">
                No hay eventos disponibles. ¡Crea el primero!
            </div>
            <div id="events-list-items"></div>
        </div>
        <!-- TAB CREAR -->
        <div id="events-pane-create" style="display:none; flex:1; min-height:0; overflow:auto; padding:4px;">
            <div class="field-row-stacked" style="margin-bottom:8px;">
                <label for="ev-create-title" style="font-size:11px;">Título *</label>
                <input type="text" id="ev-create-title" maxlength="120" style="width:100%;">
            </div>
            <div class="field-row-stacked" style="margin-bottom:8px;">
                <label for="ev-create-desc" style="font-size:11px;">Descripción</label>
                <textarea id="ev-create-desc" rows="3" maxlength="2000" style="width:100%; resize:vertical;"></textarea>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                <div class="field-row-stacked" style="flex:1; min-width:140px;">
                    <label for="ev-create-date" style="font-size:11px;">Fecha y hora *</label>
                    <input type="datetime-local" id="ev-create-date" style="width:100%;">
                </div>
                <div class="field-row-stacked" style="flex:1; min-width:120px;">
                    <label for="ev-create-duration" style="font-size:11px;">Duración (min) *</label>
                    <input type="number" id="ev-create-duration" value="60" min="15" max="10080" style="width:100%;">
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                <div class="field-row-stacked" style="flex:1; min-width:120px;">
                    <label for="ev-create-min" style="font-size:11px;">Mínimo participantes</label>
                    <input type="number" id="ev-create-min" value="1" min="1" style="width:100%;">
                </div>
                <div class="field-row-stacked" style="flex:1; min-width:120px;">
                    <label for="ev-create-max" style="font-size:11px;">Máximo (0 = sin límite)</label>
                    <input type="number" id="ev-create-max" value="0" min="0" style="width:100%;">
                </div>
            </div>
            <div class="field-row-stacked" style="margin-bottom:8px;">
                <label style="font-size:11px;">Visibilidad</label>
                <div style="display:flex; gap:10px; margin-top:2px;">
                    <label style="font-size:11px; display:flex; align-items:center; gap:4px;">
                        <input type="radio" name="ev-visibility" value="public" checked> Público (cualquiera puede unirse)
                    </label>
                    <label style="font-size:11px; display:flex; align-items:center; gap:4px;">
                        <input type="radio" name="ev-visibility" value="private"> Privado (solo invitados)
                    </label>
                </div>
            </div>
            <div class="field-row-stacked" style="margin-bottom:8px;">
                <label style="font-size:11px;">Invitar amigos (opcional)</label>
                <div id="ev-create-friends" style="max-height:130px; overflow:auto; border:1px inset; padding:4px; background:var(--win-body-bg, var(--win-bg));">
                    <p style="font-size:10px; color:var(--text-faint, #666); margin:4px;">Cargando amigos…</p>
                </div>
            </div>
            <div class="field-row" style="justify-content:flex-end; gap:4px; margin-top:10px;">
                <button class="button" id="ev-create-cancel">Cancelar</button>
                <button class="button default" id="ev-create-submit">Crear</button>
            </div>
            <p id="ev-create-status" style="font-size:11px; margin:6px 0 0; color:var(--text-faint, #666);"></p>
        </div>
    </div>
</div>

<!-- DETALLE DE UN EVENTO (overlay encima del modal de eventos) -->
<div class="window" id="event-detail-window"
     style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%);
            width:min(480px,92vw); max-height:86vh; z-index:9550; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text" id="event-detail-title">Detalle del evento</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="event-detail-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px; overflow:auto;">
        <div id="event-detail-body"></div>
    </div>
</div>

<!-- VENTANA DE INVITACIÓN -->
<div class="window" id="invite-window" style="display:none; width: 280px; position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1000;">
    <div class="title-bar">
        <div class="title-bar-text">Invitar a compartir calendario</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="invite-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding: 12px;">
        <p style="font-size: 11px; margin-bottom: 8px;">Selecciona a quién invitar:</p>
        <div id="user-list" style="margin-bottom: 10px;"></div>
        <p id="invite-status" style="font-size: 11px; color: green;"></p>
    </div>
</div>

<!-- MODAL DE FECHA — se abre tras aceptar la notif del sistema general
     para pedir cuándo empezó la relación antes de confirmar la unión. -->
<div class="window" id="partner-fecha-modal" style="display:none;position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:280px;z-index:9700;">
    <div class="title-bar">
        <div class="title-bar-text">Invitación al calendario</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="partner-fecha-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px;">
        <p id="partner-fecha-msg" style="font-size:11px;margin:0 0 8px;"></p>
        <div class="field-row-stacked" style="margin-bottom:10px;">
            <label for="partner-fecha" style="font-size:11px;">Fecha en que empezasteis:</label>
            <input type="date" id="partner-fecha" style="width:100%;">
        </div>
        <div class="field-row" style="justify-content:flex-end;gap:4px;">
            <button class="button" id="partner-fecha-cancel">Cancelar</button>
            <button class="button" id="partner-fecha-confirm">Confirmar</button>
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

    /* ════════════════════════════════════════════════════════════════
       WIDGETS WIN98 — date picker + custom select
       Reemplazan inputs nativos para tener look retro coherente con
       el resto del desktop. El input/select original se convierte en
       hidden y sirve de "modelo": el JS sincroniza un display visible.
       ════════════════════════════════════════════════════════════════ */
    .w98-date-wrap, .w98-select-wrap {
        position: relative;
        display: inline-flex;
        width: 100%;
        box-sizing: border-box;
    }
    .w98-date-display, .w98-select-btn {
        flex: 1;
        height: 22px;
        padding: 2px 6px;
        font-size: 11px;
        font-family: inherit;
        background: var(--input-bg, #fff);
        color: var(--text);
        border: 1px solid var(--bezel-dark-1, #0a0a0a);
        box-shadow:
            inset -1px -1px 0 var(--bezel-light-2, #dfdfdf),
            inset  1px  1px 0 var(--bezel-dark-2, grey);
        cursor: pointer;
        text-align: left;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        line-height: 1;
    }
    .w98-date-display { cursor: pointer; }
    .w98-select-arrow {
        margin-left: 6px;
        font-size: 8px;
        opacity: 0.8;
        flex-shrink: 0;
    }
    .w98-date-label { flex: 1; }

    /* Popup del calendario */
    .w98-cal-popup {
        position: absolute;
        top: 24px;
        left: 0;
        z-index: 10000;
        background: var(--win-bg, #c3c3c3);
        border: 1px solid;
        border-color:
            var(--bezel-light-1, #fff)
            var(--bezel-dark-1, #0a0a0a)
            var(--bezel-dark-1, #0a0a0a)
            var(--bezel-light-1, #fff);
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-2, grey),
            inset  1px  1px 0 var(--bezel-light-2, #dfdfdf),
            2px 2px 6px rgba(0, 0, 0, 0.35);
        padding: 4px;
        font-size: 11px;
        width: 220px;
        box-sizing: border-box;
        font-family: inherit;
    }
    .w98-cal-popup[hidden] { display: none; }
    .w98-cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 3px 4px;
        background: linear-gradient(to right,
            var(--titlebar-start, #000080),
            var(--titlebar-end, #1084d0));
        color: var(--titlebar-text, #fff);
        margin-bottom: 4px;
        font-weight: bold;
        font-size: 11px;
        height: 22px;       /* altura fija evita que el title wrappeado descoloque buttons */
        box-sizing: border-box;
        overflow: hidden;
        gap: 4px;
    }
    .w98-cal-title {
        flex: 1 1 auto;
        min-width: 0;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1;
    }
    .w98-cal-nav-btn {
        /* Botón propio sin heredar de .button para evitar min-width
           que hacía que sobresalieran del header. */
        width: 22px;
        height: 18px;
        padding: 0;
        margin: 0;
        font-size: 12px;
        font-family: inherit;
        line-height: 1;
        cursor: pointer;
        flex: 0 0 22px;            /* no crece, no se encoge — siempre 22px */
        box-sizing: border-box;
        background: var(--win-bg, #c3c3c3);
        color: var(--text);
        border: 1px solid var(--bezel-dark-1, #0a0a0a);
        box-shadow:
            inset -1px -1px 0 var(--bezel-dark-2, grey),
            inset  1px  1px 0 var(--bezel-light-2, #dfdfdf);
        appearance: none;          /* sin estilo nativo del browser */
        -webkit-appearance: none;
    }
    .w98-cal-nav-btn:active {
        box-shadow:
            inset  1px  1px 0 var(--bezel-dark-2, grey),
            inset -1px -1px 0 var(--bezel-light-2, #dfdfdf);
    }
    .w98-cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: var(--bezel-dark-2, grey);
        padding: 1px;
    }
    .w98-cal-dow {
        text-align: center;
        font-weight: bold;
        font-size: 9px;
        padding: 2px 0;
        background: var(--win-bg, #c3c3c3);
        color: var(--text-faint, #555);
    }
    .w98-cal-day {
        text-align: center;
        padding: 3px 0;
        cursor: pointer;
        background: var(--input-bg, #fff);
        color: var(--text);
        border: 1px solid transparent;
        font-size: 10px;
        user-select: none;
        line-height: 1.2;
    }
    .w98-cal-day:hover {
        background: var(--accent, #1084d0);
        color: var(--accent-text, #fff);
    }
    .w98-cal-day.other-month {
        color: var(--text-faint, #999);
        opacity: 0.45;
    }
    .w98-cal-day.today {
        outline: 1px dashed var(--accent, #1084d0);
        outline-offset: -2px;
        font-weight: bold;
    }
    .w98-cal-day.selected {
        background: var(--accent, #1084d0);
        color: var(--accent-text, #fff);
        font-weight: bold;
    }

    /* Popup del select custom */
    .w98-select-popup {
        position: absolute;
        top: 22px;
        left: 0;
        right: 0;
        z-index: 10000;
        background: var(--input-bg, #fff);
        border: 1px solid var(--bezel-dark-1, #0a0a0a);
        box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        max-height: 180px;
        overflow-y: auto;
        font-family: inherit;
    }
    .w98-select-popup[hidden] { display: none; }
    .w98-select-opt {
        padding: 4px 8px;
        cursor: pointer;
        font-size: 11px;
        color: var(--text);
        user-select: none;
        line-height: 1.2;
    }
    .w98-select-opt:hover,
    .w98-select-opt.active {
        background: var(--accent, #1084d0);
        color: var(--accent-text, #fff);
    }
</style>

<!-- POPUP DÍA -->
<div class="popup-overlay" id="popup-dia">
    <div class="window" style="width: 380px; max-height: 85vh; display: flex; flex-direction: column;">
        <div class="title-bar">
            <div class="title-bar-text" id="popup-titulo"><img src="../assets/img/appIcons/calendarioIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Día</div>
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
            <div class="title-bar"><div class="title-bar-text">Tiempo juntos</div></div>
            <div class="window-body" style="padding: 12px; text-align: center;">
                <div id="dias-contador" style="font-size: 28px; font-weight: bold;"></div>
                <div style="font-size: 11px; margin-top: 4px;">días juntos</div>
                <div style="font-size: 10px; color: #808080; margin-top: 6px;">Desde <?php echo $pareja['fecha_inicio']; ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- AÑADIR MOMENTO -->
        <div class="window" style="overflow: auto;">
            <div class="title-bar"><div class="title-bar-text"><img src="../assets/img/appIcons/instagramIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Añadir momento</div></div>
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
                        <option value="anual">Anual (cada año)</option>
                        <option value="mensual">Mensual (cada mes)</option>
                        <option value="semanal">Semanal (cada semana)</option>
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

/* ── Constantes de configuración — antes magic numbers dispersos. */
const CFG = {
    sidebarDays:      14,        /* sidebar derecha → próximos N días */
    expandPadMonths:  1,         /* recordatorios expandidos = mes ±1 */
    invitePollMinMs:  5000,      /* polling de invitaciones — inicial */
    invitePollMaxMs:  120000,    /* ... techo (backoff exponencial) */
    countdownTickMs:  1000,      /* tick del countdown */
    ytRetryMs:        200,       /* reintento si YT API no está lista */
    ytMaxRetries:     50,        /* máx reintentos antes de abortar */
    confirmEscape:    'Escape',  /* tecla para cerrar modales */
};

/* ── Helpers de fecha — SIEMPRE LOCAL, NUNCA UTC ──
   Bug histórico: `new Date('YYYY-MM-DD')` se interpreta como UTC
   midnight, no como medianoche local. En zonas horarias negativas
   (Américas) sale el día anterior → "días restantes" off-by-one,
   contador de días juntos incorrecto, etc. */
const ISO_DATE_RE = /^\d{4}-\d{2}-\d{2}$/;
function parseISODate(s) {
    if (!s || typeof s !== 'string' || !ISO_DATE_RE.test(s)) return null;
    const parts = s.split('-');
    /* Date(y, mIdx, d) → midnight LOCAL. */
    const d = new Date(+parts[0], +parts[1] - 1, +parts[2]);
    return isNaN(d.getTime()) ? null : d;
}
function toISODate(d) {
    if (!d || isNaN(d.getTime())) return '';
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}
function startOfDay(d) {
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}
/* Helper de escape HTML — antes se metían valores del usuario crudos
   en `innerHTML` (XSS vulnerable). */
function escHTML(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

const hoyMidnight = startOfDay(hoy);

<?php if ($pareja): ?>
const inicio = parseISODate(fechaInicio);
if (inicio) {
    const dias = Math.floor((hoyMidnight - inicio) / (1000 * 60 * 60 * 24));
    document.getElementById('dias-contador').textContent = dias;
}
<?php endif; ?>

let currentYear = hoy.getFullYear();
let currentMonth = hoy.getMonth();
let momentosPorFecha = {};
let recordatoriosPorFecha = {};
let todosMomentos = [];
const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const diasSemana = ['Lu','Ma','Mi','Ju','Vi','Sá','Do'];

function expandirRecordatorios(lista) {
    /* Acotamos a ventana ±CFG.expandPadMonths alrededor del mes visible
       + CFG.sidebarDays hacia delante (sidebar). Antes expandía 3 años
       por recordatorio → MBs de memoria. Re-llamar tras navegar mes. */
    const expandidos = [];
    const desde = new Date(currentYear, currentMonth - CFG.expandPadMonths, 1);
    const hasta = new Date(currentYear, currentMonth + CFG.expandPadMonths + 1, 0);
    const sidebarLimit = new Date(hoyMidnight);
    sidebarLimit.setDate(sidebarLimit.getDate() + CFG.sidebarDays);
    if (sidebarLimit > hasta) hasta.setTime(sidebarLimit.getTime());

    lista.forEach(r => {
        const p = (r.periodicidad || 'ninguna');
        const base = parseISODate(r.fecha);
        if (!base) return;       /* fecha inválida → descarta silencioso */
        if (p === 'ninguna') {
            expandidos.push(r);
            return;
        }
        /* Calcular DIRECTAMENTE la primera ocurrencia >= effDesde sin
           loop lento. effDesde = max(desde, base) → la periodicidad solo
           aplica del día base en adelante. Si navegas a meses anteriores
           al día del recordatorio, no se muestran ocurrencias ficticias.
           (Antes: si base=Julio 2026 y navegabas a 2024, expandía hacia
           atrás como si el evento existiera siempre.) */
        const effDesde = desde > base ? desde : base;
        let cursor;
        if (p === 'anual') {
            /* Mismo mes/día, año = effDesde.year. Si pasó → siguiente año. */
            cursor = new Date(effDesde.getFullYear(), base.getMonth(), base.getDate());
            if (cursor < effDesde) cursor.setFullYear(cursor.getFullYear() + 1);
        } else if (p === 'mensual') {
            /* Mismo día del mes. Con clamp para meses cortos
               (día 31 en febrero → último día de febrero). */
            const dayBase = base.getDate();
            cursor = new Date(effDesde.getFullYear(), effDesde.getMonth(), 1);
            while (cursor < effDesde) cursor.setMonth(cursor.getMonth() + 1);
            /* Asegurar que el día efectivo refleja el ORIGINAL clampeado. */
            const lastDayOfMonth = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0).getDate();
            cursor.setDate(Math.min(dayBase, lastDayOfMonth));
            if (cursor < effDesde) cursor.setMonth(cursor.getMonth() + 1);
        } else if (p === 'semanal') {
            /* Mismo día de la semana que base, primer >= effDesde. */
            cursor = new Date(effDesde);
            const dowBase = base.getDay();
            const dowDesde = cursor.getDay();
            let diff = (dowBase - dowDesde + 7) % 7;
            cursor.setDate(cursor.getDate() + diff);
        } else {
            return;
        }

        /* Iterar emitiendo hasta `hasta`. */
        while (cursor <= hasta) {
            expandidos.push(Object.assign({}, r, { fecha: toISODate(cursor), _periodico: true }));
            if (p === 'anual')        cursor.setFullYear(cursor.getFullYear() + 1);
            else if (p === 'mensual') {
                /* Re-clampear: para mensual día 31, después de enero=31,
                   febrero debe ser 28/29, no marzo 3. */
                const targetDay = base.getDate();
                cursor.setDate(1);
                cursor.setMonth(cursor.getMonth() + 1);
                const lastDay = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0).getDate();
                cursor.setDate(Math.min(targetDay, lastDay));
            }
            else if (p === 'semanal') cursor.setDate(cursor.getDate() + 7);
            else break;
        }
    });
    return expandidos;
}

/* Wrapper centralizado de fetch JSON — verifica response.ok + JSON
   parse, devuelve {ok, data} para que el caller decida. Antes cada
   fetch sin .catch dejaba la UI colgada en "Guardando..." si fallaba
   la red o el servidor devolvía HTML de error. */
async function apiFetch(url, opts) {
    try {
        const r = await fetch(url, opts);
        if (!r.ok) return { ok: false, error: 'HTTP ' + r.status };
        const ct = r.headers.get('content-type') || '';
        if (!ct.includes('json')) return { ok: false, error: 'Non-JSON response' };
        return { ok: true, data: await r.json() };
    } catch (e) {
        return { ok: false, error: e && e.message ? e.message : 'Network error' };
    }
}

async function cargarTodo() {
    const [mResp, rResp] = await Promise.all([
        apiFetch(API_BASE + '?action=get-momentos&pareja_id=' + parejaId),
        apiFetch(API_BASE + '?action=get-recordatorios&pareja_id=' + parejaId),
    ]);
    if (!mResp.ok || !rResp.ok) {
        /* Renderizar el grid aunque haya fallo — vista útil con la última data. */
        renderCalendario();
        return;
    }
    const momentos = Array.isArray(mResp.data) ? mResp.data : [];
    const recordatorios = Array.isArray(rResp.data) ? rResp.data : [];
    todosMomentos = momentos;
    momentosPorFecha = {};
    momentos.forEach(m => {
        if (!m || !m.fecha) return;
        if (!momentosPorFecha[m.fecha]) momentosPorFecha[m.fecha] = [];
        momentosPorFecha[m.fecha].push(m);
    });
    const recordatoriosExpandidos = expandirRecordatorios(recordatorios);
    recordatoriosPorFecha = {};
    recordatoriosExpandidos.forEach(r => {
        if (!r || !r.fecha) return;
        if (!recordatoriosPorFecha[r.fecha]) recordatoriosPorFecha[r.fecha] = [];
        recordatoriosPorFecha[r.fecha].push(r);
    });
    renderCalendario();
    renderRecordatorios(recordatoriosExpandidos);
}

function renderRecordatorios(lista) {
    const div = document.getElementById('recordatorios-lista');
    if (!div) return;

    /* Solo recordatorios dentro de [hoy, hoy+CFG.sidebarDays]. Pasados
       y lejanos siguen siendo visibles como puntos en el grid del mes. */
    const hoyStr = toISODate(hoyMidnight);
    const limite = new Date(hoyMidnight);
    limite.setDate(limite.getDate() + CFG.sidebarDays);
    const limiteStr = toISODate(limite);
    lista = lista.filter(r => r && r.fecha >= hoyStr && r.fecha <= limiteStr);

    if (!lista.length) {
        div.innerHTML = '<p style="font-size:11px;color:#808080;">Sin recordatorios en las próximas 2 semanas.</p>';
        return;
    }

    lista.sort((a,b) => a.fecha.localeCompare(b.fecha));
    div.innerHTML = '';
    lista.forEach(r => {
        const recDate = parseISODate(r.fecha);
        if (!recDate) return;
        const item = document.createElement('div');
        item.className = 'sidebar-rec';
        const diasRestantes = Math.round((recDate - hoyMidnight) / 86400000);
        const cuandoStr = diasRestantes === 0 ? '¡Hoy!' : 'En ' + diasRestantes + ' día' + (diasRestantes === 1 ? '' : 's');
        const periodicoLabel = r.periodicidad && r.periodicidad !== 'ninguna' ? ' · 🔁 ' + escHTML(r.periodicidad) : '';
        const texto = document.createElement('div');
        texto.className = 'sidebar-rec-texto';
        texto.innerHTML = '<strong>' + escHTML(r.titulo) + '</strong><br>' +
            '<span class="muted">' + escHTML(r.fecha) + ' · ' + cuandoStr + periodicoLabel +
            (r.autor ? ' · ' + escHTML(r.autor) : '') + '</span>' +
            (r.descripcion ? '<br>' + escHTML(r.descripcion) : '');
        const btns = document.createElement('div');
        btns.className = 'sidebar-rec-btns';
        const btnVer = document.createElement('button');
        btnVer.className = 'button btn-icon-sm';
        btnVer.textContent = '👁';
        btnVer.title = 'Ver cuenta atrás';
        btnVer.addEventListener('click', () => abrirCountdown(r));
        const btnDel = document.createElement('button');
        btnDel.className = 'button btn-icon-sm';
        btnDel.textContent = '✕';
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

/* Carga la YT IFrame API una sola vez. A10: guard contra dobles
   inyecciones del script (recarga del iframe, navegación con shell
   de desktop, etc) usando data-attribute en el <head>. */
(function loadCountdownYtApi() {
    if (window.YT && window.YT.Player) { countdownYtReady = true; return; }
    /* Marca canónica que persiste aunque este script se re-ejecute. */
    var alreadyInjected = !!document.querySelector('script[data-yt-iframe-api]');
    if (!alreadyInjected) {
        var s = document.createElement('script');
        s.src = 'https://www.youtube.com/iframe_api';
        s.setAttribute('data-yt-iframe-api', '1');
        document.head.appendChild(s);
    }
    /* Callback global de YouTube API. Si ya existía otro, lo encadena. */
    var prev = window.onYouTubeIframeAPIReady;
    window.onYouTubeIframeAPIReady = function() {
        countdownYtReady = true;
        if (typeof prev === 'function') try { prev(); } catch(_) {}
    };
})();

function abrirCountdown(rec) {
    /* Parse local-safe — antes `new Date('...T00:00:00')` aún caía en UTC
       en algunos motores antiguos. parseISODate fuerza Date(y,m,d). */
    const target = parseISODate(rec.fecha);
    countdownTargetMs = target ? target.getTime() : NaN;
    if (isNaN(countdownTargetMs)) {
        alert('Fecha del recordatorio inválida.');
        return;
    }
    countdownTitulo = rec.titulo || 'Recordatorio';
    document.getElementById('countdown-title-text').textContent = '⏳ ' + countdownTitulo;
    const bigTitle = document.getElementById('countdown-titulo-big');
    if (bigTitle) bigTitle.textContent = countdownTitulo;
    actualizarCountdown(true);
    if (countdownTimer) clearInterval(countdownTimer);
    countdownTimer = setInterval(actualizarCountdown, CFG.countdownTickMs);
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

/* Cache de celdas DOM por fecha — el grid SOLO se reconstruye cuando
   cambia mes/año. Las recargas de datos (cargarTodo tras un guardado/
   borrado) actualizan únicamente los dots/foto de las celdas existentes.
   Antes se re-creaban 42 nodos por cada cambio. */
let __gridCellsByDate = {};
let __gridCurrentKey = null;

function _safeFotoCellSrc(foto) {
    if (!foto || typeof foto !== 'string') return '';
    if (/^https?:\/\//i.test(foto)) return foto;
    if (!/^[\w.\-]+$/.test(foto)) return '';
    return '../uploads/momentos/' + foto;
}

function _updateCellContent(cell, fechaStr, d) {
    /* Borra contenido previo (num, foto, dots), recoloca según data
       actual. No re-añade el listener — el cell ya lo tiene del
       buildGridShell. */
    while (cell.firstChild) cell.removeChild(cell.firstChild);
    cell.classList.remove('cal-cell-foto');
    cell.style.backgroundImage = '';
    cell.style.borderColor = '';

    const momentosDelDia = momentosPorFecha[fechaStr] || [];
    const tieneRecordatorios = recordatoriosPorFecha[fechaStr] && recordatoriosPorFecha[fechaStr].length > 0;
    const esHoy = d === hoy.getDate() && currentMonth === hoy.getMonth() && currentYear === hoy.getFullYear();
    const momentoConFoto = momentosDelDia.find(m => m && m.foto);

    if (momentoConFoto) {
        const src = _safeFotoCellSrc(momentoConFoto.foto);
        if (src) {
            cell.style.backgroundImage = 'url("' + src.replace(/"/g, '%22') + '")';
            cell.classList.add('cal-cell-foto');
        }
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
}

function _rebuildGridShell() {
    const grid = document.getElementById('calendar-grid');
    grid.innerHTML = '';
    __gridCellsByDate = {};

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
        cell.className = 'cal-cell';
        const fechaStr = currentYear + '-' + String(currentMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
        cell.addEventListener('click', () => abrirPopupDia(fechaStr));
        grid.appendChild(cell);
        __gridCellsByDate[fechaStr] = { cell, d };
    }
}

function renderCalendario() {
    document.getElementById('month-label').textContent = meses[currentMonth] + ' ' + currentYear;
    const key = currentYear + '-' + currentMonth;
    if (key !== __gridCurrentKey) {
        _rebuildGridShell();
        __gridCurrentKey = key;
    }
    /* Diff update: solo refresca contenido interno de cada celda según
       los datos actuales. Click listeners se mantienen porque las
       celdas son las mismas. */
    Object.keys(__gridCellsByDate).forEach(fechaStr => {
        const { cell, d } = __gridCellsByDate[fechaStr];
        _updateCellContent(cell, fechaStr, d);
    });
}

function abrirPopupDia(fecha) {
    const momentos = momentosPorFecha[fecha] || [];
    const recordatorios = recordatoriosPorFecha[fecha] || [];

    document.getElementById('popup-titulo').innerHTML = '<img src="../assets/img/appIcons/calendarioIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">' + fecha;
    const contenido = document.getElementById('popup-contenido');
    contenido.innerHTML = '';

    if (!momentos.length && !recordatorios.length) {
        const empty = document.createElement('p');
        empty.className = 'popup-empty';
        empty.textContent = 'No hay nada este día.';
        contenido.appendChild(empty);
    }

    if (momentos.length) {
        contenido.appendChild(_buildSectionHeader(
            '<img src="../assets/img/appIcons/galeriaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Momentos',
            'popup-section-momentos'
        ));
        momentos.forEach(m => contenido.appendChild(_buildMomentoCard(m)));
    }

    if (recordatorios.length) {
        contenido.appendChild(_buildSectionHeader(
            '<img src="../assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Recordatorios',
            'popup-section-recs'
        ));
        recordatorios.forEach(r => contenido.appendChild(_buildRecordatorioRow(r)));
    }

    document.getElementById('popup-dia').classList.add('active');
    document.getElementById('momento-fecha').value = fecha;
    document.getElementById('rec-fecha').value = fecha;
}

/* ── Helpers de render del popup-dia ───────────────────────────────
   Extraídos de abrirPopupDia para que sean ~10 líneas cada uno.
   Estilos en CSS (.momento-card, .rec-row, etc.). */

function _buildSectionHeader(title, className) {
    const h = document.createElement('p');
    h.className = 'popup-section-title ' + className;
    /* innerHTML para permitir <img> inline (e.g. bellIcon.png en la
       sección de recordatorios). Los callers pasan strings de confianza,
       no input del usuario, así que no hay riesgo XSS. */
    h.innerHTML = title;
    return h;
}

/* Valida `foto` para evitar atributos `src` con protocolos peligrosos
   (javascript:, data:) o saltos de atributo. */
function _safeFotoSrc(foto) {
    if (!foto || typeof foto !== 'string') return '';
    if (/^https?:\/\//i.test(foto)) return foto;
    if (!/^[\w.\-]+$/.test(foto)) return '';
    return '../uploads/momentos/' + foto;
}

function _buildMomentoCard(m) {
    const div = document.createElement('div');
    div.className = 'momento-card';

    const fotoSrc = _safeFotoSrc(m.foto);
    if (fotoSrc) {
        const img = document.createElement('img');
        img.src = fotoSrc;     /* atributo, no inline HTML → sin XSS */
        img.alt = '';
        img.className = 'momento-card-img';
        div.appendChild(img);
    }

    const row = document.createElement('div');
    row.className = 'momento-card-row';

    const texto = document.createElement('div');
    let html = '<strong>' + escHTML(m.titulo) + '</strong>';
    if (m.autor)       html += ' <span class="muted">(' + escHTML(m.autor) + ')</span>';
    if (m.descripcion) html += '<br>' + escHTML(m.descripcion);
    texto.innerHTML = html;
    row.appendChild(texto);

    const btnDel = document.createElement('button');
    btnDel.className = 'button btn-del-row';
    btnDel.textContent = '✕';
    btnDel.addEventListener('click', () => eliminarMomento(m.id));
    row.appendChild(btnDel);

    div.appendChild(row);
    return div;
}

function _buildRecordatorioRow(r) {
    const div = document.createElement('div');
    div.className = 'rec-row';

    const periodicoLabel = (r.periodicidad && r.periodicidad !== 'ninguna')
        ? ' <span class="muted">· 🔁 ' + escHTML(r.periodicidad) + '</span>'
        : '';
    const texto = document.createElement('div');
    texto.innerHTML = '<strong>' + escHTML(r.titulo) + '</strong>' + periodicoLabel +
        (r.descripcion ? '<br>' + escHTML(r.descripcion) : '');

    /* Botón "ver contador" — abre la ventana fullscreen de cuenta atrás
       épica. Mismo icono (👁) que en el sidebar para consistencia visual.
       Cierra el popup-día antes de abrir para que la overlay nueva no
       quede tapada por el popup-día. */
    const btnVer = document.createElement('button');
    btnVer.className = 'button btn-del-row';
    btnVer.textContent = '👁';
    btnVer.title = 'Ver cuenta atrás';
    btnVer.addEventListener('click', () => {
        document.getElementById('popup-dia').classList.remove('active');
        abrirCountdown(r);
    });

    const btnDel = document.createElement('button');
    btnDel.className = 'button btn-del-row';
    btnDel.textContent = '✕';
    btnDel.title = 'Eliminar recordatorio';
    btnDel.addEventListener('click', () => eliminarRecordatorio(r.id));

    /* Wrap de los dos botones — la .rec-row usa justify-content:
       space-between que separaría los 3 hijos en columnas. Con el
       wrap, son 2 hijos: texto a la izquierda, grupo de botones a la
       derecha, los dos botones uno al lado del otro. */
    const btnsWrap = document.createElement('div');
    btnsWrap.className = 'rec-row-btns';
    btnsWrap.appendChild(btnVer);
    btnsWrap.appendChild(btnDel);

    div.appendChild(texto);
    div.appendChild(btnsWrap);
    return div;
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
    /* expandirRecordatorios acota al mes visible — re-cargar para refrescar
       la ventana de expansión. */
    cargarTodo();
});

document.getElementById('next-month').addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    cargarTodo();
});

document.getElementById('btn-guardar-momento').addEventListener('click', async function() {
    const titulo = document.getElementById('momento-titulo').value.trim();
    const fecha  = document.getElementById('momento-fecha').value;
    const desc   = document.getElementById('momento-desc').value.trim();
    const fotoEl = document.getElementById('momento-foto-url');
    const foto   = fotoEl ? fotoEl.value.trim() : '';
    const status = document.getElementById('momento-status');
    const btn    = this;

    if (!titulo || !fecha) {
        status.style.color = 'red';
        status.textContent = 'Título y fecha son obligatorios.';
        return;
    }

    btn.disabled = true;
    status.style.color = '#808080';
    status.textContent = 'Guardando...';

    const resp = await apiFetch(API_BASE + '?action=save-momento', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pareja_id: parejaId, titulo, fecha, descripcion: desc, foto }),
    });
    btn.disabled = false;
    if (!resp.ok) { status.style.color = 'red'; status.textContent = 'Error de red. Inténtalo de nuevo.'; return; }
    if (resp.data && resp.data.error) { status.style.color = 'red'; status.textContent = resp.data.error; return; }
    status.style.color = 'green';
    status.textContent = '✅ Guardado';
    document.getElementById('momento-titulo').value = '';
    document.getElementById('momento-desc').value  = '';
    if (fotoEl) fotoEl.value = '';
    cargarTodo();
});

document.getElementById('btn-guardar-rec').addEventListener('click', async function() {
    const titulo = document.getElementById('rec-titulo').value.trim();
    const fecha = document.getElementById('rec-fecha').value;
    const desc = document.getElementById('rec-desc').value.trim();
    const periodicidad = document.getElementById('rec-periodicidad').value;
    const status = document.getElementById('rec-status');
    const btn = this;

    if (!titulo || !fecha) { status.style.color = 'red'; status.textContent = 'Título y fecha son obligatorios.'; return; }

    btn.disabled = true;
    status.style.color = '#808080';
    status.textContent = 'Guardando...';
    const resp = await apiFetch(API_BASE + '?action=save-recordatorio', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pareja_id: parejaId, titulo, fecha, descripcion: desc, periodicidad }),
    });
    btn.disabled = false;
    if (!resp.ok) { status.style.color = 'red'; status.textContent = 'Error de red. Inténtalo de nuevo.'; return; }
    if (resp.data && resp.data.error) { status.style.color = 'red'; status.textContent = resp.data.error; return; }
    status.style.color = 'green';
    status.textContent = '✅ Guardado';
    document.getElementById('rec-titulo').value = '';
    document.getElementById('rec-desc').value = '';
    document.getElementById('rec-periodicidad').value = 'ninguna';
    cargarTodo();
});

function eliminarMomento(id) {
    window._calConfirm('¿Eliminar este <strong>momento</strong>?', async function() {
        const resp = await apiFetch(API_BASE + '?action=delete-momento', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id }),
        });
        if (!resp.ok) { alert('Error de red. Inténtalo de nuevo.'); return; }
        if (resp.data && resp.data.error) { alert(resp.data.error); return; }
        document.getElementById('popup-dia').classList.remove('active');
        cargarTodo();
    });
}

function eliminarRecordatorio(id) {
    window._calConfirm('¿Eliminar este <strong>recordatorio</strong>?', async function() {
        const resp = await apiFetch(API_BASE + '?action=delete-recordatorio', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id }),
        });
        if (!resp.ok) { alert('Error de red. Inténtalo de nuevo.'); return; }
        if (resp.data && resp.data.error) { alert(resp.data.error); return; }
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

/* M2: backoff exponencial — empieza polleando rápido (5s) y si no hay
   novedad va espaciando hasta CFG.invitePollMaxMs (2min). Reset a
   intervalo mínimo en cuanto aparece una invitación nueva. Ahorra
   ~80-90% de requests cuando no hay actividad. */
var __invitePollDelay  = CFG.invitePollMinMs;
var __invitePollTimer  = null;
var __invitePollEmpty  = 0;     // requests seguidos sin novedad

/* notifSystem vive en el shell padre. El calendario corre dentro de un
   iframe, así que accedemos a window.parent.notifSystem (mismo origen,
   no hay restricción). Helper que centraliza el fallback por si el shell
   no estuviera disponible (standalone, tests). */
function _parentNotifSystem() {
    try {
        if (window.parent && window.parent !== window && window.parent.notifSystem) {
            return window.parent.notifSystem;
        }
    } catch (_) {}
    return null;
}

async function checkPartnerInvites() {
    const r = await apiFetch(API_BASE + '?action=get-partner-invites');
    if (!r.ok) {
        /* Error de red — espacia agresivamente para no DDoSearnos a nosotros mismos. */
        __invitePollEmpty++;
        return;
    }
    const data = r.data;
    if (!Array.isArray(data) || !data.length) {
        __invitePollEmpty++;
        return;
    }
    const inv = data[0];
    if (currentPartnerInvite && currentPartnerInvite.id === inv.id) {
        __invitePollEmpty++;
        return;
    }
    var notif = _parentNotifSystem();
    var invId = 'partner-invite-' + inv.id;
    if (notif && (notif.isShown(invId) || notif.isDismissed(invId))) {
        /* El shell ya está mostrando la tarjeta de esta invitación —
           no la dupliquemos en cada tick. */
        __invitePollEmpty++;
        return;
    }
    /* Novedad encontrada — reset agresivo. */
    __invitePollEmpty = 0;
    __invitePollDelay = CFG.invitePollMinMs;
    currentPartnerInvite = inv;
    if (notif) {
        notif.show({
            id:      invId,
            type:    'action',
            title:   'Invitación al calendario',
            message: inv.fromLabel + ' te ha invitado a compartir calendario',
            sentAt:  inv.sentAt,
            onAccept: function() { openPartnerFechaModal(inv); },
            onReject: function() { respondInvite(inv, 'reject', ''); }
        });
    } else {
        /* Sin shell padre — fallback al modal de fecha directo. */
        openPartnerFechaModal(inv);
    }
}

function _schedulePartnerPoll() {
    /* Sin novedad → duplicamos el intervalo hasta el techo. */
    if (__invitePollEmpty > 2) {
        __invitePollDelay = Math.min(__invitePollDelay * 2, CFG.invitePollMaxMs);
        __invitePollEmpty = 0;
    }
    __invitePollTimer = setTimeout(async function tick() {
        if (document.visibilityState === 'visible') {
            await checkPartnerInvites();
        }
        _schedulePartnerPoll();
    }, __invitePollDelay);
}

function openPartnerFechaModal(inv) {
    var modal = document.getElementById('partner-fecha-modal');
    document.getElementById('partner-fecha-msg').textContent =
        inv.fromLabel + ' te ha invitado a compartir calendario. Elige la fecha en que empezasteis para confirmar.';
    document.getElementById('partner-fecha').value = '';
    modal.style.display = 'block';
}

function closePartnerFechaModal() {
    document.getElementById('partner-fecha-modal').style.display = 'none';
}

async function respondInvite(inv, action, fecha) {
    if (!inv) return;
    if (action === 'accept' && !fecha) {
        alert('Por favor introduce la fecha en que empezasteis.');
        return;
    }
    const r = await apiFetch(API_BASE + '?action=respond-partner-invite', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ inviteId: inv.id, action: action, fecha: fecha }),
    });
    if (!r.ok) { alert('Error de red. Inténtalo de nuevo.'); return; }
    if (r.data && r.data.error) { alert(r.data.error); return; }
    closePartnerFechaModal();
    currentPartnerInvite = null;
    if (action === 'accept') location.reload();
}

document.getElementById('partner-fecha-confirm').addEventListener('click', function() {
    var fecha = document.getElementById('partner-fecha').value;
    respondInvite(currentPartnerInvite, 'accept', fecha);
});
document.getElementById('partner-fecha-cancel').addEventListener('click', closePartnerFechaModal);
document.getElementById('partner-fecha-close').addEventListener('click', closePartnerFechaModal);

checkPartnerInvites();
_schedulePartnerPoll();
/* Para el cleanup unificado — referencia al setTimeout activo. */
var __invitePollId = { clear: function() { if (__invitePollTimer) clearTimeout(__invitePollTimer); } };
window.addEventListener('beforeunload', function() {
    /* __invitePollId ahora es {clear}: el polling usa setTimeout
       recursivo en lugar de setInterval (M2 backoff). */
    if (typeof __invitePollId !== 'undefined' && __invitePollId && __invitePollId.clear) __invitePollId.clear();
    /* Para evitar que el countdown timer + el player YT sigan corriendo
       si el iframe se descarga (recarga, navegación). */
    try { cerrarCountdown(); } catch(_){}
});

/* Escape global cierra la ventana visible de mayor z-index — UX
   estándar Win98 ausente antes. Orden: confirm modal → countdown →
   popup-dia → invite-window. */
document.addEventListener('keydown', function(e) {
    if (e.key !== CFG.confirmEscape) return;
    const confirmEl  = document.getElementById('cal-confirm-modal');
    if (confirmEl && confirmEl.classList.contains('active')) {
        confirmEl.classList.remove('active');
        return;
    }
    const cdEl = document.getElementById('countdown-window');
    if (cdEl && cdEl.style.display !== 'none' && cdEl.style.display !== '') {
        cerrarCountdown();
        return;
    }
    const popupEl = document.getElementById('popup-dia');
    if (popupEl && popupEl.classList.contains('active')) {
        popupEl.classList.remove('active');
        return;
    }
    const inviteEl = document.getElementById('invite-window');
    if (inviteEl && inviteEl.style.display === 'block') {
        inviteEl.style.display = 'none';
        return;
    }
});
<?php endif; ?>

/* Listeners guardados — los inputs `momento-foto-browse` y
   `momento-foto-nombre` se eliminaron en un refactor previo; los
   referencia rota provocaba TypeError silencioso que rompía TODO el
   script posterior (drag, postMessage listener, confirm modal). */
var __momFotoBrowse = document.getElementById('momento-foto-browse');
var __momFotoInput  = document.getElementById('momento-foto');
var __momFotoNombre = document.getElementById('momento-foto-nombre');
if (__momFotoBrowse && __momFotoInput) {
    __momFotoBrowse.addEventListener('click', function() { __momFotoInput.click(); });
}
if (__momFotoInput && __momFotoNombre) {
    __momFotoInput.addEventListener('change', function() {
        __momFotoNombre.value = this.files.length ? this.files[0].name : '';
    });
}

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
   DRAGGABLE — todas las ventanas popup (invite, día, partner-fecha-modal,
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
        function onMouseUp() { dragging = false; pid = -1; }
        /* Unificamos mouse + touch via Pointer Events: una sola ruta,
           setPointerCapture mantiene los moves aunque el dedo salga del
           título. touch-action:none evita que iOS Safari haga pan/zoom
           durante el drag. */
        var pid = -1;
        titleBar.style.touchAction = 'none';
        titleBar.addEventListener('pointerdown', function(e) {
            /* Bail-out ANTES de capturar el puntero: si capturamos sobre la
               title-bar mientras el usuario está pulsando un botón de
               .title-bar-controls (close/min/max), el pointerup se queda en
               la title-bar y el browser nunca dispara `click` en el botón
               → la X "no cierra". Mismo gate también para botón secundario
               del ratón. */
            if (e.target.closest('.title-bar-controls')) return;
            if (e.button !== undefined && e.button !== 0) return;
            pid = e.pointerId;
            try { titleBar.setPointerCapture(pid); } catch (_) {}
            onMouseDown(e);
        });
        titleBar.addEventListener('pointermove', function(e) {
            if (e.pointerId !== pid) return;
            onMouseMove(e);
        });
        titleBar.addEventListener('pointerup', function(e) {
            if (e.pointerId !== pid) return;
            onMouseUp();
        });
        titleBar.addEventListener('pointercancel', onMouseUp);
    }
    window._calMakeDraggable = makeDraggable;

    /* Inicializa los popups que ya existen en el DOM. El popup-dia
       contiene la .window dentro de un .popup-overlay → drag aplica
       a la .window interna. */
    [
        document.getElementById('invite-window'),
        document.getElementById('partner-fecha-modal'),
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

<!-- ════════════════════════════════════════════════════════════════
     WIN98 WIDGETS — date picker + custom select
     Reemplaza nativamente todos los <input type="date"> y <select> del
     calendario por widgets que respetan el theme Win98. Mantiene los
     mismos IDs en hidden inputs → el código existente sigue accediendo
     a .value como antes. Un monkey-patch al setter de value sincroniza
     el display al hacer `input.value = X` desde otros sitios.
     ════════════════════════════════════════════════════════════════ -->
<script>
(function() {
    var MONTHS = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    /* Días de semana empezando en LUNES (estilo europeo). */
    var DOWS = ['L','M','X','J','V','S','D'];

    function pad(n) { return n < 10 ? '0' + n : String(n); }
    function ymd(date) {
        return date.getFullYear() + '-' + pad(date.getMonth()+1) + '-' + pad(date.getDate());
    }
    function parseYmd(s) {
        if (!s) return null;
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s);
        if (!m) return null;
        var d = new Date(+m[1], +m[2]-1, +m[3]);
        return isNaN(d.getTime()) ? null : d;
    }
    function fmtDisplay(s) {
        var d = parseYmd(s);
        if (!d) return '';
        /* dd/mm/yyyy es más legible que ISO en interfaces ES. */
        return pad(d.getDate()) + '/' + pad(d.getMonth()+1) + '/' + d.getFullYear();
    }

    /* Intercepta `input.value = X` para mantener sincronizado el display
       sin que el código existente cambie. Solo para los inputs que
       gestionamos — no afecta a otros inputs del documento. */
    function interceptValueSetter(input, onSet) {
        var proto = Object.getPrototypeOf(input);
        var nativeDesc = Object.getOwnPropertyDescriptor(proto, 'value')
                      || Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        if (!nativeDesc || !nativeDesc.set) return;
        Object.defineProperty(input, 'value', {
            configurable: true,
            get: function() { return nativeDesc.get.call(this); },
            set: function(v) {
                nativeDesc.set.call(this, v);
                try { onSet(v); } catch(_) {}
            }
        });
    }

    /* ── Date picker ─────────────────────────────────────────────── */
    function buildDatePicker(origInput) {
        /* Capturamos el value antes de cambiar el type — input[type=date]
           tiene su propio formato YYYY-MM-DD, lo conservamos. */
        var initialValue = origInput.value || '';
        var origStyle    = origInput.getAttribute('style') || '';
        var origId       = origInput.id;
        var origName     = origInput.name || '';

        /* Convertimos a hidden y le añadimos width:100% que tenía. */
        origInput.type = 'hidden';
        origInput.removeAttribute('style');

        var wrap = document.createElement('div');
        wrap.className = 'w98-date-wrap';
        if (origStyle) wrap.setAttribute('style', origStyle);

        var display = document.createElement('div');
        display.className = 'w98-date-display';
        display.tabIndex = 0;
        display.innerHTML = '<span class="w98-date-label"></span><span class="w98-select-arrow">▼</span>';

        var popup = document.createElement('div');
        popup.className = 'w98-cal-popup';
        popup.hidden = true;

        /* Insertamos wrap antes del original y movemos el hidden DENTRO
           del wrap para mantenerlos juntos. */
        origInput.parentNode.insertBefore(wrap, origInput);
        wrap.appendChild(display);
        wrap.appendChild(popup);
        wrap.appendChild(origInput);

        var labelEl = display.querySelector('.w98-date-label');
        var currentView = new Date();   /* mes/año mostrados en el grid */

        function updateDisplay(v) {
            labelEl.textContent = fmtDisplay(v) || 'dd/mm/aaaa';
        }
        updateDisplay(initialValue);
        /* Restauramos el value tras setear type=hidden (algunos browsers
           lo limpian al cambiar de tipo). */
        if (initialValue) {
            var nativeDesc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
            nativeDesc.set.call(origInput, initialValue);
        }

        interceptValueSetter(origInput, updateDisplay);

        function renderGrid() {
            var sel = parseYmd(origInput.value);
            var today = new Date();
            var year = currentView.getFullYear();
            var month = currentView.getMonth();
            var firstDay = new Date(year, month, 1);
            var lastDay  = new Date(year, month + 1, 0);
            /* Lunes = 0; nativo de JS getDay() es Domingo = 0. */
            var startDow = (firstDay.getDay() + 6) % 7;
            var monthDays = lastDay.getDate();
            /* 5 semanas × 7 = 35 celdas. Si el mes natural necesita 6
               semanas (cellsNeeded > 35), desplazamos el grid +7 para
               que los últimos días caigan dentro: las primeras 1-3
               filas del mes se pintan como "other-month" overflow al
               principio o se omiten — preferimos perder los primeros
               días en favor de mostrar el mes ENTERO al final (que es
               donde casi siempre cae el "hoy"). */
            var totalCells = 35;
            var cellsNeeded = startDow + monthDays;
            if (cellsNeeded > totalCells) startDow -= 7;

            var html = '<div class="w98-cal-header">' +
                '<button type="button" class="w98-cal-nav-btn" data-nav="prev" title="Mes anterior">‹</button>' +
                '<div class="w98-cal-title">' + MONTHS[month] + ' ' + year + '</div>' +
                '<button type="button" class="w98-cal-nav-btn" data-nav="next" title="Mes siguiente">›</button>' +
            '</div><div class="w98-cal-grid">';
            DOWS.forEach(function(d){ html += '<div class="w98-cal-dow">' + d + '</div>'; });
            for (var i = 0; i < totalCells; i++) {
                var dayNum = i - startDow + 1;
                var date = new Date(year, month, dayNum);
                var isOther = dayNum < 1 || dayNum > monthDays;
                var isToday = date.toDateString() === today.toDateString();
                var isSel = sel && date.toDateString() === sel.toDateString();
                var cls = 'w98-cal-day';
                if (isOther) cls += ' other-month';
                if (isToday) cls += ' today';
                if (isSel)   cls += ' selected';
                html += '<div class="' + cls + '" data-date="' + ymd(date) + '">' + date.getDate() + '</div>';
            }
            html += '</div>';
            popup.innerHTML = html;

            popup.querySelectorAll('[data-nav]').forEach(function(btn){
                btn.addEventListener('click', function(e){
                    e.stopPropagation();
                    var dir = btn.dataset.nav === 'next' ? 1 : -1;
                    currentView = new Date(year, month + dir, 1);
                    renderGrid();
                });
            });
            popup.querySelectorAll('.w98-cal-day').forEach(function(cell){
                cell.addEventListener('click', function(e){
                    e.stopPropagation();
                    origInput.value = cell.dataset.date;   /* dispara monkey-patch → updateDisplay */
                    closePop();
                });
            });
        }

        /* Movemos el popup al body para escapar overflow:hidden / clip
           del modal padre. Con position:fixed lo anclamos al viewport
           y lo posicionamos según el bounding del display. Si no cabe
           debajo, lo volteamos arriba; mismo para derecha. */
        document.body.appendChild(popup);
        popup.style.position = 'fixed';

        function positionPop() {
            var r = display.getBoundingClientRect();
            popup.style.left = r.left + 'px';
            popup.style.top  = (r.bottom + 2) + 'px';
            /* Forzar reflow antes de medir popup. */
            var pr = popup.getBoundingClientRect();
            if (pr.bottom > window.innerHeight - 8) {
                /* Voltea arriba si no cabe abajo. */
                popup.style.top = Math.max(8, r.top - pr.height - 2) + 'px';
            }
            if (pr.right > window.innerWidth - 8) {
                popup.style.left = Math.max(8, window.innerWidth - pr.width - 8) + 'px';
            }
        }

        function openPop() {
            var v = parseYmd(origInput.value);
            currentView = v ? new Date(v.getFullYear(), v.getMonth(), 1) : new Date();
            renderGrid();
            popup.hidden = false;
            positionPop();
        }
        function closePop() { popup.hidden = true; }

        display.addEventListener('click', function(e){
            e.stopPropagation();
            if (popup.hidden) openPop();
            else closePop();
        });
        display.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (popup.hidden) openPop();
                else closePop();
            } else if (e.key === 'Escape') {
                closePop();
            }
        });
        /* Guard incluye popup (vive en body, fuera de wrap) — sin esto
           cualquier click DENTRO del popup cerraría el calendario. */
        document.addEventListener('click', function(e){
            if (!wrap.contains(e.target) && !popup.contains(e.target)) closePop();
        });
        /* Re-posicionar si el viewport cambia mientras está abierto. */
        window.addEventListener('resize', function(){ if (!popup.hidden) positionPop(); });
        window.addEventListener('scroll', function(){ if (!popup.hidden) positionPop(); }, true);
    }

    /* ── Custom select ───────────────────────────────────────────── */
    function buildSelect(origSelect) {
        var options = Array.from(origSelect.options).map(function(opt){
            return { value: opt.value, label: opt.textContent.trim() };
        });
        if (!options.length) return;
        var origId    = origSelect.id;
        var origName  = origSelect.name || '';
        var origValue = origSelect.value;
        var origStyle = origSelect.getAttribute('style') || '';

        var wrap = document.createElement('div');
        wrap.className = 'w98-select-wrap';
        if (origStyle) wrap.setAttribute('style', origStyle);

        var btn = document.createElement('div');
        btn.className = 'w98-select-btn';
        btn.tabIndex = 0;
        btn.innerHTML = '<span class="w98-date-label"></span><span class="w98-select-arrow">▼</span>';

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        if (origId)   hidden.id   = origId;
        if (origName) hidden.name = origName;
        hidden.value = origValue;

        var popup = document.createElement('div');
        popup.className = 'w98-select-popup';
        popup.hidden = true;

        var labelEl = btn.querySelector('.w98-date-label');

        function findOpt(v) {
            return options.find(function(o){ return o.value === v; });
        }
        function updateLabel(v) {
            var o = findOpt(v);
            labelEl.textContent = o ? o.label : '';
        }
        updateLabel(origValue);

        options.forEach(function(o) {
            var el = document.createElement('div');
            el.className = 'w98-select-opt';
            el.textContent = o.label;
            el.dataset.value = o.value;
            if (o.value === origValue) el.classList.add('active');
            el.addEventListener('click', function(e){
                e.stopPropagation();
                hidden.value = o.value;   /* dispara monkey-patch → updateLabel */
                popup.querySelectorAll('.w98-select-opt').forEach(function(x){ x.classList.remove('active'); });
                el.classList.add('active');
                popup.hidden = true;
                btn.focus();
            });
            popup.appendChild(el);
        });

        wrap.appendChild(btn);
        wrap.appendChild(hidden);
        origSelect.parentNode.replaceChild(wrap, origSelect);
        /* Popup al body para escapar overflow:hidden del modal padre. */
        document.body.appendChild(popup);
        popup.style.position = 'fixed';

        interceptValueSetter(hidden, updateLabel);

        function positionSelectPop() {
            var r = btn.getBoundingClientRect();
            /* Mantenemos el mismo ancho que el botón para que el dropdown
               se alinee visualmente. */
            popup.style.left  = r.left + 'px';
            popup.style.width = r.width + 'px';
            popup.style.top   = (r.bottom + 1) + 'px';
            var pr = popup.getBoundingClientRect();
            if (pr.bottom > window.innerHeight - 8) {
                popup.style.top = Math.max(8, r.top - pr.height - 1) + 'px';
            }
            if (pr.right > window.innerWidth - 8) {
                popup.style.left = Math.max(8, window.innerWidth - pr.width - 8) + 'px';
            }
        }
        function openSelectPop() {
            popup.hidden = false;
            positionSelectPop();
        }
        function closeSelectPop() { popup.hidden = true; }

        btn.addEventListener('click', function(e){
            e.stopPropagation();
            if (popup.hidden) openSelectPop();
            else closeSelectPop();
        });
        btn.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (popup.hidden) openSelectPop();
                else closeSelectPop();
            } else if (e.key === 'Escape') {
                closeSelectPop();
            }
        });
        document.addEventListener('click', function(e){
            /* Guard incluye popup (vive en body, fuera de wrap). */
            if (!wrap.contains(e.target) && !popup.contains(e.target)) popup.hidden = true;
        });
        /* Re-posicionar al cambiar viewport mientras abierto. */
        window.addEventListener('resize', function(){ if (!popup.hidden) positionSelectPop(); });
        window.addEventListener('scroll', function(){ if (!popup.hidden) positionSelectPop(); }, true);
    }

    /* ── Init: aplica a TODOS los date/select del calendario ────── */
    function init() {
        document.querySelectorAll('input[type="date"]').forEach(buildDatePicker);
        document.querySelectorAll('select').forEach(buildSelect);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

<!-- ══════════════════════════════════════════════════════════════════
     EVENTOS — lista, creación, detalle, join/leave, invitaciones haro.
     ══════════════════════════════════════════════════════════════════ -->
<script>
(function eventsModule(){
    'use strict';
    var API = '../assets/events/api.php';

    /* ── Estado UI ── */
    var winEl    = document.getElementById('events-window');
    var detailEl = document.getElementById('event-detail-window');
    var btnOpen  = document.getElementById('btn-eventos');
    if (!winEl || !btnOpen) return;

    var listPane    = document.getElementById('events-pane-list');
    var createPane  = document.getElementById('events-pane-create');
    var listItemsEl = document.getElementById('events-list-items');
    var listEmptyEl = document.getElementById('events-list-empty');

    var STATE = {
        events: [],
        friends: [],
        selectedInvitees: {},   // userKey → true
        currentDetailId: null,
    };

    function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    }); }

    function fmtDate(iso) {
        if (!iso) return '';
        var d = new Date(iso.replace(' ', 'T'));
        if (isNaN(d.getTime())) return iso;
        var days = ['dom','lun','mar','mié','jue','vie','sáb'];
        var pad = function(n){ return String(n).padStart(2,'0'); };
        return days[d.getDay()] + ' ' + pad(d.getDate()) + '/' + pad(d.getMonth()+1) + '/' + d.getFullYear() +
               ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function api(action, body, method) {
        var opts = { credentials: 'same-origin' };
        if (method === 'POST') {
            opts.method = 'POST';
            opts.headers = { 'Content-Type': 'application/json' };
            opts.body = JSON.stringify(body || {});
        }
        return fetch(API + '?action=' + encodeURIComponent(action), opts).then(function(r){ return r.json(); });
    }

    /* ── Abrir/cerrar ventana ── */
    function openWindow() {
        winEl.style.display = '';
        showTab('list');
        loadEvents();
        loadFriends();
    }
    function closeWindow() {
        winEl.style.display = 'none';
        closeDetail();
    }
    btnOpen.addEventListener('click', openWindow);
    document.getElementById('events-close').addEventListener('click', closeWindow);

    function showTab(name) {
        listPane.style.display   = (name === 'list')   ? '' : 'none';
        createPane.style.display = (name === 'create') ? '' : 'none';
    }
    document.getElementById('events-tab-list').addEventListener('click', function(){ showTab('list'); });
    document.getElementById('events-tab-create').addEventListener('click', function(){
        showTab('create');
        renderFriendsCheckboxes();
    });
    document.getElementById('events-refresh').addEventListener('click', loadEvents);

    /* ── LISTA ── */
    function loadEvents() {
        listItemsEl.innerHTML = '<p style="font-size:11px; color:var(--text-faint, #666); padding:8px;">Cargando…</p>';
        api('list-events').then(function(d){
            if (!d || !d.ok) {
                listItemsEl.innerHTML = '<p style="font-size:11px; color:#a33; padding:8px;">Error al cargar eventos.</p>';
                return;
            }
            STATE.events = d.events || [];
            renderList();
        }).catch(function(){
            listItemsEl.innerHTML = '<p style="font-size:11px; color:#a33; padding:8px;">Error de red.</p>';
        });
    }

    function renderList() {
        if (!STATE.events.length) {
            listItemsEl.innerHTML = '';
            listEmptyEl.style.display = '';
            return;
        }
        listEmptyEl.style.display = 'none';
        var html = STATE.events.map(function(ev){
            var badge = '';
            if (ev.myStatus === 'joined')   badge = '<span style="background:#2e8b57;color:#fff;padding:1px 5px;font-size:10px;border-radius:2px;">UNIDO</span>';
            else if (ev.myStatus === 'waitlist') badge = '<span style="background:#d4a017;color:#000;padding:1px 5px;font-size:10px;border-radius:2px;">ESPERA</span>';
            else if (ev.myStatus === 'invited') badge = '<span style="background:#4a90d9;color:#fff;padding:1px 5px;font-size:10px;border-radius:2px;">INVITADO</span>';
            var visBadge = ev.visibility === 'private'
                ? '<span style="background:#888;color:#fff;padding:1px 5px;font-size:10px;border-radius:2px;">PRIVADO</span>'
                : '';
            var capLabel = ev.maxParticipants > 0
                ? (ev.joinedCount + '/' + ev.maxParticipants + (ev.waitlistCount ? ' (+' + ev.waitlistCount + ' espera)' : ''))
                : (ev.joinedCount + ' unidos');
            return '<div class="ev-card" data-event-id="' + ev.id + '" style="' +
                'padding:6px 8px; margin-bottom:5px; background:var(--win-bg); border:1px outset; cursor:pointer;">' +
                '<div style="display:flex; gap:6px; align-items:baseline; margin-bottom:2px;">' +
                    '<strong style="font-size:12px; flex:1;">' + esc(ev.title) + '</strong>' +
                    badge + ' ' + visBadge +
                '</div>' +
                '<div style="font-size:10px; color:var(--text-faint, #666);">' +
                    '📅 ' + esc(fmtDate(ev.eventDate)) + ' · ⏱ ' + ev.durationMin + ' min · 👥 ' + esc(capLabel) +
                '</div>' +
            '</div>';
        }).join('');
        listItemsEl.innerHTML = html;
        listItemsEl.querySelectorAll('.ev-card').forEach(function(card){
            card.addEventListener('click', function(){
                openDetail(parseInt(card.dataset.eventId, 10));
            });
        });
    }

    /* ── AMIGOS para invitar ── */
    function loadFriends() {
        api('list-mutual-friends').then(function(d){
            if (d && d.ok) STATE.friends = d.friends || [];
            renderFriendsCheckboxes();
        }).catch(function(){});
    }
    function renderFriendsCheckboxes() {
        var box = document.getElementById('ev-create-friends');
        if (!box) return;
        if (!STATE.friends.length) {
            box.innerHTML = '<p style="font-size:10px; color:var(--text-faint, #666); margin:4px;">No tienes amigos mutuos para invitar.</p>';
            return;
        }
        box.innerHTML = STATE.friends.map(function(f){
            var checked = STATE.selectedInvitees[f.key] ? 'checked' : '';
            return '<label style="display:block; font-size:11px; padding:2px 4px;">' +
                '<input type="checkbox" class="ev-inv-cb" value="' + esc(f.key) + '" ' + checked + '> ' +
                esc(f.label) + '</label>';
        }).join('');
        box.querySelectorAll('.ev-inv-cb').forEach(function(cb){
            cb.addEventListener('change', function(){
                if (cb.checked) STATE.selectedInvitees[cb.value] = true;
                else delete STATE.selectedInvitees[cb.value];
            });
        });
    }

    /* ── CREAR ── */
    document.getElementById('ev-create-cancel').addEventListener('click', function(){
        showTab('list');
    });
    document.getElementById('ev-create-submit').addEventListener('click', function(){
        var statusEl = document.getElementById('ev-create-status');
        statusEl.style.color = 'var(--text-faint, #666)';
        statusEl.textContent = 'Creando…';

        var title = document.getElementById('ev-create-title').value.trim();
        var desc  = document.getElementById('ev-create-desc').value.trim();
        var dateRaw = document.getElementById('ev-create-date').value;  // "YYYY-MM-DDTHH:MM"
        var durMin = parseInt(document.getElementById('ev-create-duration').value, 10) || 60;
        var minP   = parseInt(document.getElementById('ev-create-min').value, 10) || 1;
        var maxP   = parseInt(document.getElementById('ev-create-max').value, 10) || 0;
        var vis = document.querySelector('input[name="ev-visibility"]:checked');
        vis = vis ? vis.value : 'public';
        var invitees = Object.keys(STATE.selectedInvitees);

        if (!title)   { statusEl.style.color = '#a33'; statusEl.textContent = 'El título es obligatorio.'; return; }
        if (!dateRaw) { statusEl.style.color = '#a33'; statusEl.textContent = 'La fecha es obligatoria.'; return; }
        if (maxP > 0 && maxP < minP) { statusEl.style.color = '#a33'; statusEl.textContent = 'Máximo no puede ser menor que mínimo.'; return; }

        var dateStr = dateRaw.replace('T', ' ') + ':00';  // "YYYY-MM-DD HH:MM:SS"

        api('create-event', {
            title: title,
            description: desc,
            eventDate: dateStr,
            durationMin: durMin,
            minParticipants: minP,
            maxParticipants: maxP,
            visibility: vis,
            invitees: invitees,
        }, 'POST').then(function(d){
            if (!d || !d.ok) {
                statusEl.style.color = '#a33';
                statusEl.textContent = (d && d.error) ? d.error : 'Error al crear.';
                return;
            }
            statusEl.style.color = 'green';
            statusEl.textContent = '✓ Evento creado.';
            /* Reset form */
            document.getElementById('ev-create-title').value = '';
            document.getElementById('ev-create-desc').value = '';
            document.getElementById('ev-create-date').value = '';
            document.getElementById('ev-create-duration').value = '60';
            document.getElementById('ev-create-min').value = '1';
            document.getElementById('ev-create-max').value = '0';
            STATE.selectedInvitees = {};
            renderFriendsCheckboxes();
            /* Refrescar lista + recordatorios del calendario. */
            loadEvents();
            if (typeof window.cargarTodo === 'function') window.cargarTodo();
            setTimeout(function(){ showTab('list'); }, 700);
        }).catch(function(){
            statusEl.style.color = '#a33';
            statusEl.textContent = 'Error de red.';
        });
    });

    /* ── DETALLE ── */
    function openDetail(eventId) {
        STATE.currentDetailId = eventId;
        var bodyEl = document.getElementById('event-detail-body');
        bodyEl.innerHTML = '<p style="font-size:11px; color:var(--text-faint, #666);">Cargando…</p>';
        detailEl.style.display = '';
        api('get-event&id=' + eventId).then(function(d){
            if (!d || !d.ok) {
                bodyEl.innerHTML = '<p style="font-size:11px; color:#a33;">' + esc((d && d.error) || 'Error') + '</p>';
                return;
            }
            renderDetail(d.event);
        }).catch(function(){
            bodyEl.innerHTML = '<p style="font-size:11px; color:#a33;">Error de red.</p>';
        });
    }
    function closeDetail() {
        detailEl.style.display = 'none';
        STATE.currentDetailId = null;
    }
    document.getElementById('event-detail-close').addEventListener('click', closeDetail);

    function renderDetail(ev) {
        document.getElementById('event-detail-title').textContent = ev.title;
        var bodyEl = document.getElementById('event-detail-body');
        var capInfo = ev.maxParticipants > 0
            ? (ev.joinedCount + ' / ' + ev.maxParticipants)
            : (ev.joinedCount + ' (sin límite)');
        var waitInfo = ev.waitlistCount > 0
            ? '<div style="font-size:11px; margin-top:2px;">En lista de espera: <strong>' + ev.waitlistCount + '</strong></div>'
            : '';
        var partList = (ev.participants || []).map(function(p){
            var sLabel = p.status === 'waitlist' ? ' <span style="color:#d4a017;">(espera)</span>' : '';
            return '<li style="font-size:11px;">' + esc(p.label) + sLabel + '</li>';
        }).join('');
        var actions = '';
        if (ev.isFinished) {
            actions = '<p style="font-size:11px; color:var(--text-faint, #666);">Este evento ya ha finalizado.</p>';
        } else if (ev.myStatus === 'joined' || ev.myStatus === 'waitlist') {
            actions = '<button class="button" id="ev-detail-leave">Salir del evento</button>';
        } else if (ev.myStatus === 'invited') {
            actions = '<button class="button" id="ev-detail-decline">Rechazar</button> ' +
                      '<button class="button default" id="ev-detail-accept">Aceptar invitación</button>';
        } else {
            var joinLabel = (ev.maxParticipants > 0 && ev.joinedCount >= ev.maxParticipants)
                ? 'Unirme (lista de espera)'
                : 'Unirme';
            actions = '<button class="button default" id="ev-detail-join">' + joinLabel + '</button>';
        }
        var deleteBtn = ev.isCreator
            ? '<button class="button" id="ev-detail-delete" style="margin-left:auto; color:#a33;">Eliminar evento</button>'
            : '';
        var inviteBtn = (!ev.isFinished && (ev.isCreator || (ev.visibility === 'public' && ev.myStatus === 'joined')))
            ? '<button class="button" id="ev-detail-invite">Invitar amigos…</button>'
            : '';

        bodyEl.innerHTML =
            '<div style="margin-bottom:8px;">' +
                '<div style="font-size:11px;"><strong>Fecha:</strong> ' + esc(fmtDate(ev.eventDate)) + '</div>' +
                '<div style="font-size:11px;"><strong>Duración:</strong> ' + ev.durationMin + ' min</div>' +
                '<div style="font-size:11px;"><strong>Visibilidad:</strong> ' + (ev.visibility === 'private' ? 'Privado' : 'Público') + '</div>' +
                '<div style="font-size:11px;"><strong>Mín / Máx:</strong> ' + ev.minParticipants + ' / ' + (ev.maxParticipants || '∞') + '</div>' +
                '<div style="font-size:11px;"><strong>Participantes:</strong> ' + esc(capInfo) + '</div>' +
                waitInfo +
            '</div>' +
            (ev.description ? '<div style="font-size:11px; white-space:pre-wrap; padding:6px; background:var(--win-body-bg, var(--win-bg)); border:1px inset; margin-bottom:8px;">' + esc(ev.description) + '</div>' : '') +
            '<div style="margin-bottom:8px;"><strong style="font-size:11px;">Participantes:</strong>' +
                (partList ? '<ul style="margin:4px 0 0 18px; padding:0;">' + partList + '</ul>' : '<p style="font-size:11px; color:var(--text-faint,#666);">Nadie todavía.</p>') +
            '</div>' +
            '<div class="field-row" style="gap:4px; align-items:center; margin-top:8px;">' +
                actions + ' ' + inviteBtn + ' ' + deleteBtn +
            '</div>' +
            '<div id="ev-detail-invite-panel" style="display:none; margin-top:8px; border:1px inset; padding:6px; background:var(--win-body-bg, var(--win-bg));">' +
                '<p style="font-size:11px; margin:0 0 4px;"><strong>Invitar a amigos mutuos:</strong></p>' +
                '<div id="ev-detail-invite-list" style="max-height:140px; overflow:auto;"></div>' +
                '<p id="ev-detail-invite-status" style="font-size:10px; margin:4px 0 0; color:var(--text-faint, #666);"></p>' +
            '</div>';

        wireDetailActions(ev);
    }

    function wireDetailActions(ev) {
        var join    = document.getElementById('ev-detail-join');
        var leave   = document.getElementById('ev-detail-leave');
        var accept  = document.getElementById('ev-detail-accept');
        var decline = document.getElementById('ev-detail-decline');
        var del     = document.getElementById('ev-detail-delete');
        var invite  = document.getElementById('ev-detail-invite');

        if (join) join.addEventListener('click', function(){
            api('join-event', { eventId: ev.id }, 'POST').then(function(d){
                if (d && d.ok) {
                    openDetail(ev.id);
                    loadEvents();
                    if (typeof window.cargarTodo === 'function') window.cargarTodo();
                }
            });
        });
        if (leave) leave.addEventListener('click', function(){
            api('leave-event', { eventId: ev.id }, 'POST').then(function(d){
                if (d && d.ok) {
                    openDetail(ev.id);
                    loadEvents();
                    if (typeof window.cargarTodo === 'function') window.cargarTodo();
                }
            });
        });
        if (accept) accept.addEventListener('click', function(){
            api('respond-event-invite', { inviteId: ev.myInviteId, action: 'accept' }, 'POST').then(function(d){
                if (d && d.ok) {
                    openDetail(ev.id);
                    loadEvents();
                    if (typeof window.cargarTodo === 'function') window.cargarTodo();
                }
            });
        });
        if (decline) decline.addEventListener('click', function(){
            api('respond-event-invite', { inviteId: ev.myInviteId, action: 'decline' }, 'POST').then(function(d){
                if (d && d.ok) {
                    closeDetail();
                    loadEvents();
                }
            });
        });
        if (del) del.addEventListener('click', function(){
            if (!confirm('¿Eliminar este evento? Se borrará también de los calendarios de todos los participantes.')) return;
            api('delete-event', { eventId: ev.id }, 'POST').then(function(d){
                if (d && d.ok) {
                    closeDetail();
                    loadEvents();
                    if (typeof window.cargarTodo === 'function') window.cargarTodo();
                }
            });
        });
        if (invite) invite.addEventListener('click', function(){
            var panel = document.getElementById('ev-detail-invite-panel');
            var list  = document.getElementById('ev-detail-invite-list');
            panel.style.display = '';
            api('list-mutual-friends').then(function(d){
                if (!d || !d.ok) { list.innerHTML = '<p style="font-size:10px;">Error.</p>'; return; }
                var already = {};
                (ev.participants || []).forEach(function(p){ already[p.key] = true; });
                var avail = (d.friends || []).filter(function(f){ return !already[f.key]; });
                if (!avail.length) { list.innerHTML = '<p style="font-size:10px;">Sin amigos disponibles para invitar.</p>'; return; }
                list.innerHTML = avail.map(function(f){
                    return '<div style="display:flex; align-items:center; gap:4px; padding:2px 0;">' +
                        '<span style="flex:1; font-size:11px;">' + esc(f.label) + '</span>' +
                        '<button class="button ev-inv-send" data-key="' + esc(f.key) + '" style="font-size:10px; padding:1px 6px;">Invitar</button>' +
                    '</div>';
                }).join('');
                list.querySelectorAll('.ev-inv-send').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        var key = btn.dataset.key;
                        btn.disabled = true; btn.textContent = '…';
                        api('invite-to-event', { eventId: ev.id, userKey: key }, 'POST').then(function(r){
                            if (r && r.ok) { btn.textContent = '✓ Enviado'; }
                            else { btn.disabled = false; btn.textContent = 'Invitar'; document.getElementById('ev-detail-invite-status').textContent = (r && r.error) || 'Error'; }
                        });
                    });
                });
            });
        });
    }

    /* ── HARO POLLING para invitaciones pendientes ──
       Cada 30s comprobamos invites pending NO notificados todavía.
       Por cada uno, disparamos notifSystem.show con type:'action' y
       callbacks onAccept/onReject que POSTean al endpoint. Tras
       mostrar, marcamos como notified para no spamear cada poll. */
    function getNotifSystem() {
        try {
            if (window.parent && window.parent !== window && window.parent.notifSystem) {
                return window.parent.notifSystem;
            }
        } catch (_) {}
        return (typeof window.notifSystem === 'object' && window.notifSystem) ? window.notifSystem : null;
    }
    function pollInvites() {
        var notifSys = getNotifSystem();
        if (!notifSys || !notifSys.show) return;
        api('get-pending-invites').then(function(d){
            if (!d || !d.ok || !Array.isArray(d.invites)) return;
            var toMark = [];
            d.invites.forEach(function(inv){
                if (inv.notified) return;  /* ya se mostró antes */
                toMark.push(inv.id);
                var notifId = 'event-invite-' + inv.id;
                notifSys.show({
                    id: notifId,
                    type: 'action',
                    title: '📅 Invitación a evento',
                    message: esc(inv.inviterLabel) + ' te ha invitado a "' + esc(inv.eventTitle) + '" — ' + esc(fmtDate(inv.eventDate)),
                    sentAt: (inv.sentAt || 0) * 1000,
                    onAccept: function(){
                        api('respond-event-invite', { inviteId: inv.id, action: 'accept' }, 'POST').then(function(r){
                            if (r && r.ok && typeof window.cargarTodo === 'function') window.cargarTodo();
                        });
                    },
                    onReject: function(){
                        api('respond-event-invite', { inviteId: inv.id, action: 'decline' }, 'POST');
                    },
                });
            });
            if (toMark.length) api('mark-invites-notified', { inviteIds: toMark }, 'POST');
        }).catch(function(){});
    }
    /* Llamada inicial tras un pequeño delay (para que notifSystem se
       haya inicializado en el shell padre) y polling cada 30s. */
    setTimeout(pollInvites, 2500);
    setInterval(pollInvites, 30000);
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