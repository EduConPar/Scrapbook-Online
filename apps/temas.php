<?php
session_start();
require_once dirname(__DIR__) . '/assets/config.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../index.php');
    exit;
}
$userLabel = $loginUsers[$userKey]['label'];

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Temas</title>
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; background: var(--win-bg); color: var(--text); }
        #temas-app { display: flex; height: 100vh; }
        #temas-sidebar {
            width: 180px;
            background: var(--win-bg);
            color: var(--text);
            border-right: 1px solid var(--border);
            box-shadow: 1px 0 0 var(--bezel-light-1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .temas-side-head {
            padding: 4px 8px;
            background: linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
            color: var(--titlebar-text);
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        #temas-list {
            flex: 1;
            overflow-y: auto;
            padding: 4px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .temas-item {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 5px 6px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            font-size: 11px;
            color: var(--text);
        }
        .temas-item:hover {
            background: var(--accent);
            color: var(--accent-text);
        }
        .temas-item.active {
            background: var(--accent);
            color: var(--accent-text);
        }
        .temas-item-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .temas-item-badge {
            font-size: 9px;
            color: #008000;
            font-weight: bold;
        }
        .temas-item.active .temas-item-badge,
        .temas-item:hover .temas-item-badge { color: var(--accent-text); }
        #temas-sidebar-footer {
            padding: 6px 8px;
            border-top: 1px solid var(--border);
            box-shadow: 0 -1px 0 var(--bezel-light-1);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        #temas-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--win-bg);
            color: var(--text);
        }
        #temas-wallpaper-area {
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            border-top: 1px solid var(--border);
            box-shadow: 0 -1px 0 var(--bezel-light-1);
        }
        #temas-wallpaper-preview {
            width: 100%;
            aspect-ratio: 16/10;
            background-color: var(--inset-bg);
            background-size: cover;
            background-position: center;
            box-shadow: inset 1px 1px var(--bezel-dark-1),
                        inset -1px -1px var(--bezel-light-1),
                        inset 2px 2px var(--bezel-dark-2),
                        inset -2px -2px var(--bezel-light-2);
        }
        #temas-toolbar {
            background: var(--win-bg);
            color: var(--text);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 0 var(--bezel-light-1);
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #temas-toolbar .field-row-stacked { margin: 0; flex: 1; }
        #temas-toolbar label { font-size: 10px; color: var(--text); }
        #temas-toolbar input { font-size: 11px; padding: 2px 4px; }
        #temas-editor {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            background: var(--win-bg);
            color: var(--text);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
            align-content: start;
        }
        .color-group-head {
            grid-column: 1 / -1;
            font-size: 11px;
            font-weight: bold;
            color: var(--accent);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 0 var(--bezel-light-1);
            padding: 4px 2px 2px;
            margin-top: 6px;
            letter-spacing: 0.4px;
        }
        .color-group-head:first-child { margin-top: 0; }
        .color-field {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .color-field-label { font-size: 11px; font-weight: bold; color: var(--text); }
        .color-field-row { display: flex; gap: 4px; align-items: center; }
        .color-field-row input[type="color"] {
            width: 36px;
            height: 24px;
            padding: 0;
            border: 1px solid var(--border);
            cursor: pointer;
        }
        .color-field-row input[type="text"] {
            width: 80px;
            font-size: 11px;
            padding: 2px 4px;
        }
        #temas-status {
            font-size: 11px;
            padding: 4px 10px;
            background: var(--win-bg);
            color: var(--text);
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
    </style>
</head>
<body class="<?php
    $bc = [];
    if ($userKey === 'user1') $bc[] = 'capi';
    elseif ($userKey === 'user2') $bc[] = 'angie';
    if ($activeThemeClass) $bc[] = $activeThemeClass;
    echo htmlspecialchars(implode(' ', $bc));
?>">

<div id="temas-app">
    <div id="temas-sidebar">
        <div class="temas-side-head">🎨 Mis temas</div>
        <div id="temas-list"></div>
        <div id="temas-sidebar-footer">
            <button class="button" id="temas-new">+ Nuevo</button>
            <button class="button" id="temas-deactivate">Usar default</button>
        </div>

        <div class="temas-side-head" style="border-top:1px solid var(--border);">🖼 Fondo</div>
        <div id="temas-wallpaper-area">
            <div id="temas-wallpaper-preview"></div>
            <div class="field-row" style="gap:4px;">
                <input type="text" id="temas-wallpaper-name" readonly placeholder="Sin archivo" style="flex:1;min-width:0;cursor:default;font-size:11px;height:21px;">
                <button class="button" id="temas-wallpaper-browse" style="min-width:70px;flex-shrink:0;height:21px;min-height:21px;">Examinar...</button>
            </div>
            <input type="file" id="temas-wallpaper-file" accept="image/*" style="display:none;">
            <button class="button" id="temas-wallpaper-save" style="width:100%;margin-top:4px;">Subir y aplicar</button>
            <p id="temas-wallpaper-status" style="font-size:10px;margin:3px 0 0;color:var(--text-faint);min-height:13px;"></p>
        </div>
    </div>

    <div id="temas-main">
        <div id="temas-toolbar">
            <div class="field-row-stacked">
                <label for="theme-name-input">Nombre del tema</label>
                <input type="text" id="theme-name-input" maxlength="30" placeholder="MiTema">
            </div>
            <button class="button" id="theme-save">Guardar</button>
            <button class="button" id="theme-activate">Activar</button>
            <button class="button" id="theme-delete">Eliminar</button>
        </div>
        <div id="temas-editor">
            <!-- color fields injected by JS -->
        </div>
        <div id="temas-status">Crea un tema nuevo o selecciona uno existente para editarlo.</div>
    </div>
</div>

<script>
const COLOR_DEFS = [
    /* — Superficies — */
    { key: 'winBg',         label: 'Fondo de ventana',          def: '#c0c0c0', group: 'Superficies' },
    { key: 'winBodyBg',     label: 'Fondo del cuerpo',          def: '#c0c0c0', group: 'Superficies' },
    { key: 'surfaceDeep',   label: 'Taskbar / paneles',         def: '#c0c0c0', group: 'Superficies' },
    { key: 'insetBg',       label: 'Fondo hundido (placeholder)', def: '#808080', group: 'Superficies' },
    /* — Inputs — */
    { key: 'inputBg',       label: 'Fondo del input',           def: '#ffffff', group: 'Inputs' },
    { key: 'inputText',     label: 'Texto del input',           def: '#000000', group: 'Inputs' },
    /* — Botones — */
    { key: 'btnBg',         label: 'Fondo del botón',           def: '#c0c0c0', group: 'Botones' },
    { key: 'btnText',       label: 'Texto del botón',           def: '#000000', group: 'Botones' },
    /* — Texto — */
    { key: 'text',          label: 'Texto principal',           def: '#000000', group: 'Texto' },
    { key: 'textMuted',     label: 'Texto secundario',          def: '#666666', group: 'Texto' },
    { key: 'textFaint',     label: 'Texto terciario',           def: '#808080', group: 'Texto' },
    { key: 'textInset',     label: 'Texto sobre hundido',       def: '#808080', group: 'Texto' },
    /* — Acento / barra de título — */
    { key: 'titlebarStart', label: 'Título — gradiente inicio', def: '#000080', group: 'Acento' },
    { key: 'titlebarEnd',   label: 'Título — gradiente final',  def: '#1084d0', group: 'Acento' },
    { key: 'titlebarText',  label: 'Título — texto',            def: '#ffffff', group: 'Acento' },
    { key: 'accent',        label: 'Acento (hover/activo)',     def: '#000080', group: 'Acento' },
    { key: 'accentText',    label: 'Texto sobre acento',        def: '#ffffff', group: 'Acento' },
    { key: 'accentDeep',    label: 'Acento profundo (hover²)',  def: '#00004a', group: 'Acento' },
    /* — Iconos de ventana (close / minimize / maximize) — */
    { key: 'titlebarIconColor',      label: 'Color del dibujo del icono', def: '#000000', group: 'Iconos de ventana' },
    { key: 'titlebarIconBg',         label: 'Fondo del botón del icono',  def: '#c0c0c0', group: 'Iconos de ventana' },
    { key: 'titlebarIconBezelLight', label: 'Borde 3D claro (top-left)',  def: '#ffffff', group: 'Iconos de ventana' },
    { key: 'titlebarIconBezelDark',  label: 'Borde 3D oscuro (bot-right)', def: '#0a0a0a', group: 'Iconos de ventana' },
    /* — Bordes — */
    { key: 'border',        label: 'Borde general',             def: '#808080', group: 'Bordes' },
    { key: 'borderStrong',  label: 'Borde fuerte',              def: '#404040', group: 'Bordes' },
    /* — Bezels Win98 (4 capas) — */
    { key: 'bezelLight1',   label: 'Bezel claro (1px)',         def: '#ffffff', group: 'Bezels' },
    { key: 'bezelLight2',   label: 'Bezel claro (2px)',         def: '#dfdfdf', group: 'Bezels' },
    { key: 'bezelDark1',    label: 'Bezel oscuro (1px)',        def: '#0a0a0a', group: 'Bezels' },
    { key: 'bezelDark2',    label: 'Bezel oscuro (2px)',        def: '#808080', group: 'Bezels' },
    /* — Escritorio — */
    { key: 'desktopBg',     label: 'Fondo del escritorio',      def: '#008080', group: 'Escritorio' },
    /* — Estados — */
    { key: 'linkText',      label: 'Hipervínculos',             def: '#0000ff', group: 'Estados' },
    { key: 'errorText',     label: 'Texto de error',            def: '#c00000', group: 'Estados' },
    { key: 'warningBg',     label: 'Fondo de aviso',            def: '#fffbe6', group: 'Estados' },
    { key: 'warningText',   label: 'Texto de aviso',            def: '#444444', group: 'Estados' },
    /* — Selección / badges — */
    { key: 'selectionBg',   label: 'Fondo de selección',        def: '#000080', group: 'Selección' },
    { key: 'selectionText', label: 'Texto en selección',        def: '#ffffff', group: 'Selección' },
    { key: 'badgeBg',       label: 'Fondo del badge',           def: '#d72638', group: 'Selección' },
    { key: 'badgeText',     label: 'Texto del badge',           def: '#ffffff', group: 'Selección' },
    /* — Decorativos — */
    { key: 'starColor',     label: 'Estrellas (rating)',        def: '#ffd700', group: 'Decorativos' }
];

/* Mapeo de claves legacy → nuevas (para temas guardados antes del refactor) */
const LEGACY_KEY_MAP = {
    'bg':            'winBg',
    'taskbarBg':     'surfaceDeep',
    'windowBg':      'winBg',
    'windowText':    'text',
    'titleBarStart': 'titlebarStart',
    'titleBarEnd':   'titlebarEnd',
    'titleBarText':  'titlebarText',
    'windowShadow':  'bezelDark1'
};

function migrateLegacyColors(colors) {
    if (!colors) return {};
    var out = {};
    Object.keys(colors).forEach(function(k) {
        var newKey = LEGACY_KEY_MAP[k] || k;
        if (out[newKey] === undefined) out[newKey] = colors[k];
    });
    return out;
}

var editorEl = document.getElementById('temas-editor');
var nameInput = document.getElementById('theme-name-input');
var listEl = document.getElementById('temas-list');
var statusEl = document.getElementById('temas-status');
var savedThemes = {};
var activeName = '';

function buildEditor() {
    editorEl.innerHTML = '';
    /* Agrupar por def.group */
    var groups = {};
    var groupOrder = [];
    COLOR_DEFS.forEach(function(def) {
        var g = def.group || 'Otros';
        if (!groups[g]) { groups[g] = []; groupOrder.push(g); }
        groups[g].push(def);
    });
    groupOrder.forEach(function(g) {
        var head = document.createElement('div');
        head.className = 'color-group-head';
        head.textContent = g;
        editorEl.appendChild(head);
        groups[g].forEach(function(def) {
            var wrap = document.createElement('div');
            wrap.className = 'color-field';
            var l = document.createElement('div');
            l.className = 'color-field-label';
            l.textContent = def.label;
            wrap.appendChild(l);
            var row = document.createElement('div');
            row.className = 'color-field-row';
            var picker = document.createElement('input');
            picker.type = 'color';
            picker.value = def.def;
            picker.dataset.key = def.key;
            picker.id = 'col-' + def.key;
            var hex = document.createElement('input');
            hex.type = 'text';
            hex.value = def.def;
            hex.maxLength = 9;
            hex.dataset.key = def.key;
            hex.id = 'hex-' + def.key;
            picker.addEventListener('input', function() { hex.value = picker.value; });
            hex.addEventListener('input', function() {
                if (/^#[0-9a-f]{6}$/i.test(hex.value)) picker.value = hex.value;
            });
            row.appendChild(picker);
            row.appendChild(hex);
            wrap.appendChild(row);
            editorEl.appendChild(wrap);
        });
    });
}

function getEditorColors() {
    var c = {};
    COLOR_DEFS.forEach(function(def) {
        c[def.key] = document.getElementById('hex-' + def.key).value;
    });
    return c;
}

function setEditorColors(colors) {
    COLOR_DEFS.forEach(function(def) {
        var v = (colors && colors[def.key]) ? colors[def.key] : def.def;
        document.getElementById('hex-' + def.key).value = v;
        if (/^#[0-9a-f]{6}$/i.test(v)) document.getElementById('col-' + def.key).value = v;
    });
}

function resetEditor() {
    nameInput.value = '';
    var defaults = {};
    COLOR_DEFS.forEach(function(d) { defaults[d.key] = d.def; });
    setEditorColors(defaults);
    setActiveItem(null);
    statusEl.textContent = 'Nuevo tema. Pon un nombre y guarda.';
}

function setActiveItem(name) {
    document.querySelectorAll('.temas-item').forEach(function(el) {
        el.classList.toggle('active', el.dataset.name === name);
    });
}

function renderList() {
    listEl.innerHTML = '';
    var names = Object.keys(savedThemes).sort();
    if (!names.length) {
        var empty = document.createElement('div');
        empty.style.cssText = 'padding:10px;font-size:11px;color:#808080;text-align:center;';
        empty.textContent = 'No tienes temas';
        listEl.appendChild(empty);
        return;
    }
    names.forEach(function(name) {
        var item = document.createElement('div');
        item.className = 'temas-item';
        item.dataset.name = name;
        var n = document.createElement('span');
        n.className = 'temas-item-name';
        n.textContent = name;
        item.appendChild(n);
        if (name === activeName) {
            var badge = document.createElement('span');
            badge.className = 'temas-item-badge';
            badge.textContent = '✓ activo';
            item.appendChild(badge);
        }
        item.addEventListener('click', function() {
            nameInput.value = name;
            setEditorColors(migrateLegacyColors(savedThemes[name].colors || {}));
            setActiveItem(name);
            statusEl.textContent = 'Editando "' + name + '"';
        });
        listEl.appendChild(item);
    });
}

function loadThemes() {
    fetch('../assets/themes/get-themes.php')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d || !d.ok) return;
            savedThemes = d.themes || {};
            activeName  = d.active || '';
            renderList();
            /* Mostrar siempre el tema activo del usuario al entrar */
            if (activeName && savedThemes[activeName]) {
                nameInput.value = activeName;
                setEditorColors(migrateLegacyColors(savedThemes[activeName].colors || {}));
                setActiveItem(activeName);
                statusEl.textContent = 'Editando "' + activeName + '" (activo)';
            }
        });
}

document.getElementById('temas-new').addEventListener('click', resetEditor);
document.getElementById('temas-deactivate').addEventListener('click', function() {
    fetch('../assets/themes/set-active.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: '' })
    }).then(function(r) { return r.json(); })
      .then(function(d) {
          if (d && d.ok) {
              activeName = '';
              renderList();
              applyLiveTheme('', '');
              statusEl.textContent = 'Tema desactivado.';
          }
      });
});

document.getElementById('theme-save').addEventListener('click', function() {
    var name = nameInput.value.trim();
    if (!name) { statusEl.textContent = 'Falta el nombre.'; return; }
    var colors = getEditorColors();
    fetch('../assets/themes/save-theme.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, colors: colors })
    }).then(function(r) { return r.json(); })
      .then(function(d) {
          if (!d || d.error) { statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
          statusEl.textContent = 'Tema "' + name + '" guardado.';
          loadThemes();
      });
});

document.getElementById('theme-activate').addEventListener('click', function() {
    var name = nameInput.value.trim();
    if (!name) { statusEl.textContent = 'Selecciona un tema primero.'; return; }
    if (!savedThemes[name]) { statusEl.textContent = 'Guarda el tema antes de activarlo.'; return; }
    fetch('../assets/themes/set-active.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name })
    }).then(function(r) { return r.json(); })
      .then(function(d) {
          if (!d || d.error) { statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
          activeName = name;
          renderList();
          var className = name + '-' + <?php echo json_encode(preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel)); ?>;
          var basePath  = 'assets/themes/' + className + '.css';
          applyLiveTheme(className, basePath);
          statusEl.textContent = 'Tema "' + name + '" activado.';
      });
});

/* También aplicar en vivo si se guarda un tema que ya está activo */
document.getElementById('theme-save').addEventListener('click', function() {
    /* Hook adicional: si el tema guardado es el activo, re-aplicar para refrescar la cache. */
    setTimeout(function() {
        var name = nameInput.value.trim();
        if (name && name === activeName) {
            var className = name + '-' + <?php echo json_encode(preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel)); ?>;
            var basePath  = 'assets/themes/' + className + '.css';
            applyLiveTheme(className, basePath);
        }
    }, 200);
});

function applyLiveTheme(className, basePath) {
    /* Local (la propia iframe de Temas): rutas con "../" */
    var localHref = basePath ? '../' + basePath : '';
    applyToDocLocal(document, className, localHref);
    /* Padre: que se encargue de su body + propagar a sus iframes */
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({ type: 'theme-activated', className: className, cssBasePath: basePath }, '*');
    }
}

function applyToDocLocal(doc, className, cssHref) {
    if (!doc || !doc.body) return;
    var body = doc.body;
    var keep = (body.className || '').split(/\s+/).filter(function(c) {
        return c === 'capi' || c === 'angie';
    });
    if (className) keep.push(className);
    body.className = keep.join(' ');
    var existing = doc.getElementById('active-theme-link');
    if (cssHref) {
        var bust = (cssHref.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
        if (existing) {
            existing.href = cssHref + bust;
        } else {
            var link = doc.createElement('link');
            link.rel = 'stylesheet';
            link.id  = 'active-theme-link';
            link.href = cssHref + bust;
            doc.head.appendChild(link);
        }
    } else if (existing) {
        existing.remove();
    }
}

document.getElementById('theme-delete').addEventListener('click', function() {
    var name = nameInput.value.trim();
    if (!name || !savedThemes[name]) { statusEl.textContent = 'Selecciona un tema guardado.'; return; }
    if (!confirm('¿Eliminar el tema "' + name + '"?')) return;
    fetch('../assets/themes/delete-theme.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name })
    }).then(function(r) { return r.json(); })
      .then(function(d) {
          if (!d || d.error) { statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
          statusEl.textContent = 'Tema "' + name + '" eliminado.';
          resetEditor();
          loadThemes();
      });
});

buildEditor();
resetEditor();
loadThemes();

/* =========================================================
   FONDO DE PANTALLA
========================================================= */
(function() {
    var wpFile    = document.getElementById('temas-wallpaper-file');
    var wpName    = document.getElementById('temas-wallpaper-name');
    var wpBrowse  = document.getElementById('temas-wallpaper-browse');
    var wpSave    = document.getElementById('temas-wallpaper-save');
    var wpPreview = document.getElementById('temas-wallpaper-preview');
    var wpStatus  = document.getElementById('temas-wallpaper-status');

    /* Wallpaper actual del usuario (calculado en PHP) */
    var currentWp = <?php echo json_encode(getUserWallpaper($userLabel)); ?>;

    function setPreview(rel) {
        if (rel) {
            /* Cache-bust por si el navegador tenía la versión vieja */
            wpPreview.style.backgroundImage = 'url("../' + rel + '?t=' + Date.now() + '")';
        } else {
            wpPreview.style.backgroundImage = '';
        }
    }
    setPreview(currentWp);

    wpBrowse.addEventListener('click', function() { wpFile.click(); });
    wpFile.addEventListener('change', function() {
        wpName.value = this.files.length ? this.files[0].name : '';
        wpStatus.textContent = '';
    });

    wpSave.addEventListener('click', function() {
        if (!wpFile.files.length) { wpStatus.textContent = 'Elige una imagen primero.'; return; }
        var fd = new FormData();
        fd.append('wallpaper', wpFile.files[0]);
        wpStatus.textContent = 'Subiendo…';
        wpSave.classList.add('btn-busy');
        fetch('../assets/img/wallpapers/save-wallpaper.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                wpSave.classList.remove('btn-busy');
                if (!d || d.error) { wpStatus.textContent = (d && d.error) ? d.error : 'Error'; return; }
                wpStatus.textContent = 'Fondo actualizado.';
                currentWp = d.wallpaper;
                setPreview(currentWp);
                /* Pide al escritorio padre que aplique el wallpaper nuevo */
                if (window.parent && window.parent !== window) {
                    window.parent.postMessage({ type: 'wallpaper-changed', wallpaper: currentWp }, '*');
                }
                wpFile.value = '';
                wpName.value = '';
            })
            .catch(function() { wpSave.classList.remove('btn-busy'); wpStatus.textContent = 'Error de red.'; });
    });
})();
</script>

</body>
</html>
