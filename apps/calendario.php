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
    <button class="button" onclick="history.back()">◄ Volver</button>
    <span style="font-size: 13px; color: var(--text);">Hola, <?php echo htmlspecialchars($userLabel); ?></span>
    <?php if (!$pareja): ?>
    <button class="button" id="btn-invitar">💌 Invitar a mi pareja</button>
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
            <div class="title-bar"><div class="title-bar-text">💕 Juntos</div></div>
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
                <div class="field-row-stacked" style="margin-bottom: 6px;">
                    <label style="font-size: 11px;">Emoción</label>
                    <select id="momento-emocion" style="width: 100%;">
                        <option value="😊">😊 Feliz</option>
                        <option value="😍">😍 Enamorado/a</option>
                        <option value="😂">😂 Divertido</option>
                        <option value="😢">😢 Nostálgico</option>
                        <option value="😌">😌 En paz</option>
                    </select>
                </div>
                <div class="field-row-stacked" style="margin-bottom: 8px;">
                    <label style="font-size: 11px;">Foto (opcional)</label>
                    <div class="field-row" style="gap:4px;">
                        <input type="text" id="momento-foto-nombre" readonly placeholder="Sin archivo" style="flex:1;min-width:0;cursor:default;font-size:11px;height:21px;">
                        <button class="button" id="momento-foto-browse" style="min-width:70px;flex-shrink:0;height:21px;min-height:21px;">Examinar...</button>
                    </div>
                    <input type="file" id="momento-foto" accept="image/*" style="display:none;">
                </div>
                <button class="button" id="btn-guardar-momento" style="width: 100%;">Guardar</button>
                <p id="momento-status" style="font-size: 11px; margin-top: 6px;"></p>
            </div>
        </div>

        <!-- AÑADIR RECORDATORIO -->
        <div class="window" style="overflow: auto;">
            <div class="title-bar"><div class="title-bar-text">🔔 Añadir recordatorio</div></div>
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
                    <label style="font-size: 11px;">Tipo</label>
                    <select id="rec-tipo" style="width: 100%;">
                        <option value="cita">🏥 Cita médica</option>
                        <option value="examen">📝 Examen</option>
                        <option value="aniversario">💑 Aniversario</option>
                        <option value="otro">📌 Otro</option>
                    </select>
                </div>
                <div class="field-row-stacked" style="margin-bottom: 8px;">
                    <label style="font-size: 11px;">Descripción</label>
                    <textarea id="rec-desc" style="width: 100%; height: 40px;"></textarea>
                </div>
                <button class="button" id="btn-guardar-rec" style="width: 100%;">Guardar</button>
                <p id="rec-status" style="font-size: 11px; margin-top: 6px;"></p>
            </div>
        </div>

    </div>

    <!-- CENTRO: Calendario -->
    <div class="window" style="display: flex; flex-direction: column; overflow: hidden;">
        <div class="title-bar"><div class="title-bar-text">📅 Calendario</div></div>
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

    <!-- DERECHA: Recordatorios + Momentos -->
    <div style="display: flex; flex-direction: column; gap: 12px; overflow: hidden;">

        <div class="window" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
            <div class="title-bar"><div class="title-bar-text">🔔 Recordatorios</div></div>
            <div class="window-body" style="padding: 10px; flex: 1; overflow-y: auto;">
                <div id="recordatorios-lista"><p style="font-size:11px;color:#808080;">Cargando...</p></div>
            </div>
        </div>

        <div class="window" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
            <div class="title-bar"><div class="title-bar-text">💗 Momentos</div></div>
            <div class="window-body" style="padding: 10px; flex: 1; overflow-y: auto;">
                <div id="momentos-lista"><p style="font-size:11px;color:#808080;">Cargando...</p></div>
            </div>
        </div>

    </div>

</div>

<script>
// Base URL dinámica — funciona en localhost y en producción sin cambios
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

        recordatoriosPorFecha = {};
        recordatorios.forEach(r => {
            if (!recordatoriosPorFecha[r.fecha]) recordatoriosPorFecha[r.fecha] = [];
            recordatoriosPorFecha[r.fecha].push(r);
        });

        renderCalendario();
        renderRecordatorios(recordatorios);
        renderMomentos(momentos);
    }).catch(() => renderCalendario());
}

function renderRecordatorios(lista) {
    const div = document.getElementById('recordatorios-lista');
    if (!lista.length) { div.innerHTML = '<p style="font-size:11px;color:#808080;">No hay recordatorios.</p>'; return; }

    const tiposIcono = { cita: '🏥', examen: '📝', aniversario: '💑', otro: '📌' };
    const hoyStr = hoy.toISOString().split('T')[0];

    lista.sort((a,b) => a.fecha.localeCompare(b.fecha));
    div.innerHTML = '';
    lista.forEach(r => {
        const pasado = r.fecha < hoyStr;
        const item = document.createElement('div');
        item.style.cssText = 'border: 1px solid #4a90d9; padding: 6px; margin-bottom: 6px; font-size: 11px; border-radius: 2px; display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;' + (pasado ? 'opacity:0.5;' : '');
        const diasRestantes = Math.ceil((new Date(r.fecha) - hoy) / (1000*60*60*24));
        const cuandoStr = pasado ? 'Pasado' : diasRestantes === 0 ? '¡Hoy!' : 'En ' + diasRestantes + ' días';
        const texto = document.createElement('div');
        texto.style.cssText = 'flex: 1;';
        texto.innerHTML = '<strong>' + (tiposIcono[r.tipo] || '📌') + ' ' + r.titulo + '</strong><br>' +
            '<span style="color:#808080;">' + r.fecha + ' · ' + cuandoStr + (r.autor ? ' · ' + r.autor : '') + '</span>' +
            (r.descripcion ? '<br>' + r.descripcion : '');
        const btnDel = document.createElement('button');
        btnDel.className = 'button';
        btnDel.textContent = '✕';
        btnDel.style.cssText = 'font-size: 10px; padding: 1px 4px; flex-shrink: 0;';
        btnDel.addEventListener('click', () => eliminarRecordatorio(r.id));
        item.appendChild(texto);
        item.appendChild(btnDel);
        div.appendChild(item);
    });
}

function renderMomentos(lista) {
    const div = document.getElementById('momentos-lista');
    if (!lista.length) { div.innerHTML = '<p style="font-size:11px;color:#808080;">No hay momentos.</p>'; return; }

    const ordenados = [...lista].sort((a, b) => b.fecha.localeCompare(a.fecha));
    div.innerHTML = '';
    ordenados.forEach(m => {
        const item = document.createElement('div');
        item.style.cssText = 'border: 1px solid #ff69b4; padding: 6px; margin-bottom: 6px; font-size: 11px; border-radius: 2px; display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;';
        const texto = document.createElement('div');
        texto.style.cssText = 'flex: 1; cursor: pointer;';
        texto.innerHTML = '<strong>' + m.emocion + ' ' + m.titulo + '</strong>' +
            '<br><span style="color:#808080;">' + m.fecha + (m.autor ? ' · ' + m.autor : '') + '</span>' +
            (m.descripcion ? '<br>' + m.descripcion : '');
        texto.addEventListener('click', () => abrirPopupDia(m.fecha));
        const btnDel = document.createElement('button');
        btnDel.className = 'button';
        btnDel.textContent = '✕';
        btnDel.style.cssText = 'font-size: 10px; padding: 1px 4px; flex-shrink: 0;';
        btnDel.addEventListener('click', () => eliminarMomento(m.id));
        item.appendChild(texto);
        item.appendChild(btnDel);
        div.appendChild(item);
    });
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
            html += '<div><strong>' + m.emocion + ' ' + m.titulo + '</strong>';
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
        const tiposIcono = { cita: '🏥', examen: '📝', aniversario: '💑', otro: '📌' };
        const h = document.createElement('p');
        h.style.cssText = 'font-size:11px; font-weight:bold; color:#4a90d9; margin-bottom:6px; margin-top:8px;';
        h.textContent = '❓ Recordatorios';
        contenido.appendChild(h);
        recordatorios.forEach(r => {
            const div = document.createElement('div');
            div.style.cssText = 'border: 1px solid #4a90d9; padding: 6px; margin-bottom: 6px; font-size: 11px; border-radius: 2px; display:flex; justify-content:space-between; align-items:flex-start;';
            const texto = document.createElement('div');
            texto.innerHTML = '<strong>' + (tiposIcono[r.tipo] || '📌') + ' ' + r.titulo + '</strong>' +
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
    const fecha = document.getElementById('momento-fecha').value;
    const desc = document.getElementById('momento-desc').value.trim();
    const emocion = document.getElementById('momento-emocion').value;
    const fotoInput = document.getElementById('momento-foto');
    const status = document.getElementById('momento-status');

    if (!titulo || !fecha) { status.style.color = 'red'; status.textContent = 'Título y fecha son obligatorios.'; return; }

    status.style.color = '#808080';
    status.textContent = 'Guardando...';

    fetch(API_BASE + '?action=save-momento', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pareja_id: parejaId, titulo, fecha, descripcion: desc, emocion })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { status.style.color = 'red'; status.textContent = data.error; return; }

        const momentoId = data.id;

        if (fotoInput.files.length > 0) {
            const formData = new FormData();
            formData.append('foto', fotoInput.files[0]);
            formData.append('momento_id', momentoId);

            return fetch(API_BASE + '?action=upload-foto', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(fotoData => {
                if (fotoData.error) { status.style.color = 'orange'; status.textContent = '⚠️ Momento guardado pero error con la foto: ' + fotoData.error; }
                else { status.style.color = 'green'; status.textContent = '✅ Guardado con foto'; }
                document.getElementById('momento-titulo').value = '';
                document.getElementById('momento-desc').value = '';
                fotoInput.value = '';
                document.getElementById('momento-foto-nombre').value = '';
                cargarTodo();
            });
        }

        status.style.color = 'green'; status.textContent = '✅ Guardado';
        document.getElementById('momento-titulo').value = '';
        document.getElementById('momento-desc').value = '';
        cargarTodo();
    });
});

document.getElementById('btn-guardar-rec').addEventListener('click', function() {
    const titulo = document.getElementById('rec-titulo').value.trim();
    const fecha = document.getElementById('rec-fecha').value;
    const tipo = document.getElementById('rec-tipo').value;
    const desc = document.getElementById('rec-desc').value.trim();
    const status = document.getElementById('rec-status');

    if (!titulo || !fecha) { status.style.color = 'red'; status.textContent = 'Título y fecha son obligatorios.'; return; }

    fetch(API_BASE + '?action=save-recordatorio', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pareja_id: parejaId, titulo, fecha, tipo, descripcion: desc })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { status.style.color = 'red'; status.textContent = data.error; return; }
        status.style.color = 'green'; status.textContent = '✅ Guardado';
        document.getElementById('rec-titulo').value = '';
        document.getElementById('rec-desc').value = '';
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
        if (!users.length) { list.innerHTML = '<p style="font-size:11px;">No hay otros usuarios.</p>'; return; }
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

cargarTodo();

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
<div class="window" id="cal-confirm-modal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); min-width:340px; max-width:460px; z-index:8500; flex-direction:column;">
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