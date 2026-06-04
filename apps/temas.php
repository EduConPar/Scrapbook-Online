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
    <script src="../assets/js/pwa-guard.js"></script>
    <title>Temas</title>
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <script>try{if(localStorage.getItem('lcd-filter')!=='0'){var c=document.documentElement.classList;c.add('lcd-filter-on');if(window.top===window)c.add('lcd-filter-top');}}catch(e){}</script>
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
        .temas-item-pub { font-size: 10px; flex-shrink: 0; }
        .temas-item-dl  { font-size: 10px; flex-shrink: 0; }
        /* Menú contextual de tema (publicar/quitar) */
        .temas-ctx-menu {
            position: fixed; z-index: 99999;
            background: var(--btn-bg); color: var(--text);
            border-top: 2px solid var(--bezel-light-1); border-left: 2px solid var(--bezel-light-1);
            border-right: 2px solid var(--bezel-dark-2); border-bottom: 2px solid var(--bezel-dark-2);
            box-shadow: 2px 2px 5px rgba(0,0,0,.35); font-size: 11px; padding: 2px; min-width: 170px;
        }
        .temas-ctx-opt { padding: 5px 12px; cursor: pointer; white-space: nowrap; }
        .temas-ctx-opt:hover { background: var(--accent); color: var(--accent-text); }
        .temas-ctx-opt.danger { color: var(--error-text); }
        .temas-ctx-opt.danger:hover { background: var(--error-text); color: var(--win-bg); }
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
        #temas-tabs {
            display: flex;
            gap: 2px;
            padding: 4px 4px 0 4px;
            flex-shrink: 0;
        }
        .temas-tab-btn {
            flex: 1 1 0;
            min-width: 0;                 /* permite encoger por debajo del contenido */
            font-size: 10px;
            padding: 3px 4px;
            min-height: 22px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }
        .temas-tab-btn.active {
            background: var(--accent);
            color: var(--accent-text);
        }
        .temas-tab-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            min-height: 0;
        }
        .temas-tab-pane[hidden] { display: none; }

        /* ── Biblioteca de temas ── */
        #temas-library { flex:1; overflow-y:auto; padding:12px; }
        #temas-library-grid {
            display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
            gap:14px;
        }
        .lib-card {
            border-top:2px solid var(--bezel-light-1); border-left:2px solid var(--bezel-light-1);
            border-right:2px solid var(--bezel-dark-2); border-bottom:2px solid var(--bezel-dark-2);
            background:var(--win-bg); display:flex; flex-direction:column;
        }
        .lib-preview { cursor:pointer; padding:8px; }
        .lib-preview:hover { filter:brightness(1.04); }
        /* mini maqueta de ventana win98 con los colores del tema */
        .lib-win { border:1px solid #000; border-radius:2px; overflow:hidden; box-shadow:2px 2px 0 rgba(0,0,0,.25); }
        .lib-win-tb { display:flex; align-items:center; gap:4px; padding:3px 5px; font-size:9px; font-weight:bold; }
        .lib-win-tb-dots { margin-left:auto; display:flex; gap:2px; }
        .lib-win-tb-dots i { width:7px; height:7px; display:block; border:1px solid rgba(0,0,0,.4); }
        .lib-win-body { padding:8px; min-height:54px; }
        .lib-win-row { height:9px; border-radius:1px; margin-bottom:5px; }
        .lib-win-btns { display:flex; gap:4px; margin-top:6px; }
        .lib-win-btn { font-size:8px; padding:2px 6px; border-radius:1px; }
        .lib-name { font-size:11px; font-weight:bold; text-align:center; padding:2px 4px; color:var(--text);
                    overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .lib-author {
            display:flex; align-items:center; gap:6px; padding:6px 8px; cursor:pointer;
            border-top:1px solid var(--border);
        }
        .lib-author:hover { background:var(--accent); color:var(--accent-text); }
        /* Marco cuadrado tipo Win98 (igual que el avatar de la pantalla de login) */
        .lib-author-img {
            width:24px; height:24px; object-fit:cover; flex-shrink:0; display:block;
            image-rendering:pixelated;
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1),
                 1px  1px 0 var(--bezel-light-1),
                -2px -2px 0 var(--bezel-dark-2),
                 2px  2px 0 var(--bezel-light-2);
            margin:2px;
        }
        .lib-author-ph {
            width:24px; height:24px; flex-shrink:0; display:flex;
            align-items:center; justify-content:center; font-size:14px;
            background:var(--inset-bg); color:var(--text-inset);
            box-shadow:
                -1px -1px 0 var(--bezel-dark-1),
                 1px  1px 0 var(--bezel-light-1),
                -2px -2px 0 var(--bezel-dark-2),
                 2px  2px 0 var(--bezel-light-2);
            margin:2px;
        }
        .lib-author-name { font-size:11px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        #temas-library-empty { text-align:center; color:var(--text-faint); font-size:11px; padding:30px 10px; }

        /* ── Vista de Personalización (Temas / Haros / Mascotas) ── */
        #temas-personalize { flex:1; overflow-y:auto; padding:12px; background:var(--win-bg); color:var(--text); }
        .pers-section { margin-bottom:18px; }
        .pers-section:last-child { margin-bottom:0; }
        .pers-section-title {
            font-size:11px;
            font-weight:bold;
            margin:0 0 6px;
            padding:3px 6px;
            background:var(--titlebar-start, #000080);
            color:var(--titlebar-text, #fff);
            letter-spacing:1px;
        }
        .pers-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(110px, 1fr));
            gap:8px;
            padding:6px;
            box-shadow:
                inset  1px  1px var(--bezel-dark-1),
                inset -1px -1px var(--bezel-light-1),
                inset  2px  2px var(--bezel-dark-2),
                inset -2px -2px var(--bezel-light-2);
            background:var(--inset-bg);
            min-height:80px;
        }
        .pers-item {
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:4px;
            padding:8px 4px 6px;
            background:var(--win-bg);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1),
                inset  1px  1px var(--bezel-light-1);
            cursor:pointer;
            font-size:10px;
            text-align:center;
            user-select:none;
            position:relative;
        }
        .pers-item:hover { background:var(--btn-bg, #d4d0c8); }
        .pers-item:active,
        .pers-item.active {
            box-shadow:
                inset  1px  1px var(--bezel-dark-1),
                inset -1px -1px var(--bezel-light-1);
        }
        .pers-item.active { background:var(--accent, #000080); color:var(--accent-text, #fff); }
        .pers-item-icon {
            width:48px; height:48px;
            display:flex; align-items:center; justify-content:center;
            font-size:32px; line-height:1;
        }
        .pers-item-icon img { max-width:100%; max-height:100%; display:block; }
        .pers-item-name { line-height:1.2; word-break:break-word; }
        .pers-item-badge {
            position:absolute;
            top:3px; right:3px;
            font-size:8px;
            padding:1px 4px;
            background:rgba(0,0,0,0.4);
            color:#fff;
            border-radius:6px;
            letter-spacing:1px;
        }
        .pers-item.active .pers-item-badge { background:rgba(255,255,255,0.25); }
        #temas-starticon-area {
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        #temas-starticon-preview {
            width: 64px;
            height: 64px;
            align-self: center;
            background-color: var(--inset-bg);
            background-size: contain;
            background-repeat: no-repeat;
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
    /* Tema default = Win98 (tokens.css) para todos. Sin clase capi/angie. */
    $bc = [];
    if ($activeThemeClass) $bc[] = $activeThemeClass;
    echo htmlspecialchars(implode(' ', $bc));
?>">

<div id="temas-app">
    <div id="temas-sidebar">
        <div id="temas-tabs">
            <button class="button temas-tab-btn active" data-tab="themes">🎨 Mis temas</button>
            <button class="button temas-tab-btn" data-tab="personalize">🖌 Personalización</button>
            <button class="button temas-tab-btn" data-tab="library">🌐 Biblioteca</button>
        </div>

        <!-- Tab: Mis temas -->
        <div class="temas-tab-pane" id="temas-pane-themes">
            <div id="temas-list"></div>
            <div id="temas-sidebar-footer">
                <button class="button" id="temas-new">+ Nuevo</button>
                <button class="button" id="temas-deactivate">Usar default</button>
            </div>
        </div>

        <!-- Tab: Personalización -->
        <div class="temas-tab-pane" id="temas-pane-personalize" hidden>
            <div class="temas-side-head">🖼 Fondo</div>
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

            <div class="temas-side-head" style="border-top:1px solid var(--border);">▶ Icono de inicio</div>
            <div id="temas-starticon-area">
                <div id="temas-starticon-preview"></div>
                <div class="field-row" style="gap:4px;">
                    <input type="text" id="temas-starticon-name" readonly placeholder="Sin archivo" style="flex:1;min-width:0;cursor:default;font-size:11px;height:21px;">
                    <button class="button" id="temas-starticon-browse" style="min-width:70px;flex-shrink:0;height:21px;min-height:21px;">Examinar...</button>
                </div>
                <input type="file" id="temas-starticon-file" accept="image/*,image/svg+xml" style="display:none;">
                <button class="button" id="temas-starticon-save" style="width:100%;margin-top:4px;">Subir y aplicar</button>
                <p id="temas-starticon-status" style="font-size:10px;margin:3px 0 0;color:var(--text-faint);min-height:13px;"></p>
            </div>

            <div class="temas-side-head" style="border-top:1px solid var(--border);">📺 Efectos visuales</div>
            <div id="temas-effects-area" style="padding:6px 4px;">
                <div class="field-row">
                    <input type="checkbox" id="lcd-filter-toggle">
                    <label for="lcd-filter-toggle" style="font-size:11px;">Filtro LCD (scanlines)</label>
                </div>
                <p style="font-size:10px;margin:4px 0 0;color:var(--text-faint);line-height:1.4;">
                    Líneas horizontales sutiles sobre toda la app. Se guarda por dispositivo.
                </p>
            </div>

        </div>

        <!-- Tab: Biblioteca -->
        <div class="temas-tab-pane" id="temas-pane-library" hidden>
            <div class="temas-side-head">🌐 Biblioteca de temas</div>
            <p style="font-size:10px;color:var(--text-faint);padding:6px 4px;line-height:1.4;">
                Temas publicados por la comunidad. Pulsa un tema para probarlo;
                pulsa el autor para visitar su perfil.
            </p>
            <button class="button" id="temas-lib-refresh" style="width:100%;">↻ Recargar</button>
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
            <button class="button" id="theme-export"  title="Descargar este tema como JSON">⬇ Exportar</button>
            <button class="button" id="theme-import"  title="Cargar un tema desde un fichero JSON">⬆ Importar</button>
            <input type="file" id="theme-import-file" accept="application/json,.json" style="display:none;">
        </div>
        <div id="temas-editor">
            <!-- color fields injected by JS -->
        </div>
        <div id="temas-status">Crea un tema nuevo o selecciona uno existente para editarlo.</div>
        <!-- Biblioteca (grid de temas publicados) -->
        <div id="temas-library" hidden>
            <div id="temas-library-grid"></div>
        </div>
        <!-- Personalización (Temas / Haros / Mascotas con cosas que tienes) -->
        <div id="temas-personalize" hidden>
            <section class="pers-section">
                <h3 class="pers-section-title">🎨 Interfaces</h3>
                <div class="pers-grid" id="pers-themes-grid"></div>
            </section>
            <section class="pers-section">
                <h3 class="pers-section-title">⚪ Haros</h3>
                <div class="pers-grid" id="pers-haros-grid"></div>
            </section>
            <section class="pers-section">
                <h3 class="pers-section-title">🐾 Mascotas</h3>
                <div class="pers-grid" id="pers-mascots-grid"></div>
            </section>
        </div>
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
    { key: 'startBtnBg',    label: 'Fondo del botón Inicio',    def: '#c0c0c0', group: 'Botones' },
    { key: 'startBtnText',  label: 'Texto del botón Inicio',    def: '#000000', group: 'Botones' },
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
/* Nombre del tema que se está editando (su nombre ORIGINAL). Si al guardar
   el usuario cambió el nombre, se renombra el original en vez de duplicar. */
var editingOriginalName = null;

/* DEFAULT de la app: se usan cuando el TEMA ACTIVO no define fondo/icono.
   - Fondo  → base-wallpaper (default de la app).
   - Icono  → '' (sin imagen → logo Win98 por defecto). */
var THEME_DEFAULT_WP = <?php echo json_encode(defaultWallpaper()); ?>;
var THEME_DEFAULT_SI = '';
/* Fondo / icono GLOBAL del usuario (su baseline cuando NO hay tema activo). */
var USER_GLOBAL_WP = <?php echo json_encode(getUserWallpaper($userLabel)); ?>;
var USER_GLOBAL_SI = <?php echo json_encode(getUserStartIcon($userLabel)); ?>;

/* Pide al escritorio (padre) aplicar el fondo y el icono del tema. */
function applyThemeAssets(wallpaper, startIcon){
    if (!window.parent || window.parent === window) return;
    window.parent.postMessage({ type: 'wallpaper-changed', wallpaper: wallpaper || '' }, '*');
    window.parent.postMessage({ type: 'start-icon-changed', icon: startIcon || '' }, '*');
}

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
    editingOriginalName = null;   /* tema nuevo: no hay original que renombrar */
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
        /* Indicador de tema descargado de la biblioteca */
        if (savedThemes[name] && savedThemes[name].downloaded) {
            var dl = document.createElement('span');
            dl.className = 'temas-item-dl';
            dl.textContent = '📥';
            dl.title = 'Descargado de la biblioteca';
            item.appendChild(dl);
        }
        /* Indicador de publicado en biblioteca */
        if (savedThemes[name] && savedThemes[name].public) {
            var pub = document.createElement('span');
            pub.className = 'temas-item-pub';
            pub.textContent = '🌐';
            pub.title = 'Publicado en la biblioteca';
            item.appendChild(pub);
        }
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
            editingOriginalName = name;   /* editando un tema existente */
            statusEl.textContent = 'Editando "' + name + '"';
        });
        /* Clic derecho → menú publicar/quitar de la biblioteca */
        item.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showThemeCtxMenu(e.clientX, e.clientY, name);
        });
        listEl.appendChild(item);
    });
}

/* Menú contextual de un tema (publicar/quitar + eliminar) */
var _themeCtxMenu = null;
function hideThemeCtxMenu(){ if(_themeCtxMenu){ _themeCtxMenu.remove(); _themeCtxMenu = null; } }
function showThemeCtxMenu(x, y, name){
    hideThemeCtxMenu();
    var t = savedThemes[name] || {};
    var isPub = !!t.public;
    var isDownloaded = !!t.downloaded;
    var menu = document.createElement('div');
    menu.className = 'temas-ctx-menu';

    var opts = [];
    /* Los temas descargados de la biblioteca NO se pueden publicar */
    if (!isDownloaded) {
        opts.push({ label: isPub ? '🌐 Quitar de la biblioteca' : '🌐 Publicar en la biblioteca',
                    action: function(){ setThemePublic(name, !isPub); } });
    }
    opts.push({ label: '📋 Crear copia', action: function(){ duplicateTheme(name); } });
    opts.push({ label: '🗑 Eliminar', danger: true, action: function(){ deleteTheme(name); } });
    opts.forEach(function(o){
        var el = document.createElement('div');
        el.className = 'temas-ctx-opt' + (o.danger ? ' danger' : '');
        el.textContent = o.label;
        el.addEventListener('click', function(){ hideThemeCtxMenu(); o.action(); });
        menu.appendChild(el);
    });

    document.body.appendChild(menu);
    var mw = menu.offsetWidth, mh = menu.offsetHeight;
    menu.style.left = Math.min(x, window.innerWidth  - mw - 4) + 'px';
    menu.style.top  = Math.min(y, window.innerHeight - mh - 4) + 'px';
    _themeCtxMenu = menu;
}
document.addEventListener('click', hideThemeCtxMenu);
document.addEventListener('contextmenu', function(e){
    if(!e.target.closest('.temas-item, .lib-card')) hideThemeCtxMenu();
});

/* Crear una copia del tema (nuevo tema ORIGINAL → se puede publicar) */
function duplicateTheme(name){
    var src = savedThemes[name];
    if(!src) return;
    var colors = migrateLegacyColors(src.colors || {});
    var copyName = (name + ' copia').slice(0, 30);
    if (savedThemes[copyName]) {
        var base = (name + ' copia').slice(0, 27), n = 2;
        while (savedThemes[copyName] && n < 99) { copyName = base + ' ' + n; n++; }
    }
    fetch('../assets/themes/api.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: copyName, colors: colors })   /* sin downloaded → original */
    }).then(function(r){ return r.json(); })
      .then(function(d){
          if(!d || d.error){ statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
          statusEl.textContent = 'Copia "' + copyName + '" creada.';
          editingOriginalName = copyName;
          loadThemes(function(){
              nameInput.value = copyName;
              setEditorColors(colors);
              setActiveItem(copyName);
          });
      });
}

/* Eliminar un tema (confirmación Win98 + borrado en servidor) */
function deleteTheme(name){
    if(!savedThemes[name]) return;
    window._w98ConfirmDeleteTheme(name, function(){
        fetch('../assets/themes/api.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name })
        }).then(function(r){ return r.json(); })
          .then(function(d){
              if(!d || d.error){ statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
              statusEl.textContent = 'Tema "' + name + '" eliminado.';
              if(nameInput.value.trim() === name) resetEditor();
              loadThemes();
          });
    });
}

function setThemePublic(name, makePublic){
    if(!savedThemes[name]) return;
    fetch('../assets/themes/api.php?action=set-public', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, public: makePublic })
    }).then(function(r){ return r.json(); })
      .then(function(d){
          if(!d || d.error){ statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
          savedThemes[name].public = d.public;
          renderList();
          statusEl.textContent = d.public
              ? 'Tema "' + name + '" publicado en la biblioteca.'
              : 'Tema "' + name + '" quitado de la biblioteca.';
      });
}

function loadThemes(cb) {
    fetch('../assets/themes/api.php?action=get')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d || !d.ok) return;
            savedThemes = d.themes || {};
            activeName  = d.active || '';
            renderList();
            /* Reflejar en Personalización el fondo/icono EFECTIVO:
               con tema → su asset o el default; sin tema → el global. */
            var act = activeName && savedThemes[activeName] ? savedThemes[activeName] : null;
            var prevWp = act ? (act.wallpaper || THEME_DEFAULT_WP) : USER_GLOBAL_WP;
            var prevSi = act ? (act.startIcon || THEME_DEFAULT_SI) : USER_GLOBAL_SI;
            if (window._setWpPreview) window._setWpPreview(prevWp);
            if (window._setSiPreview) window._setSiPreview(prevSi);
            /* Si el caller pasa callback, él decide qué mostrar en el editor.
               Si no, mostramos el tema activo del usuario (comportamiento normal). */
            if (typeof cb === 'function') { cb(); return; }
            if (activeName && savedThemes[activeName]) {
                nameInput.value = activeName;
                setEditorColors(migrateLegacyColors(savedThemes[activeName].colors || {}));
                setActiveItem(activeName);
                editingOriginalName = activeName;
                statusEl.textContent = 'Editando "' + activeName + '" (activo)';
            }
        });
}

document.getElementById('temas-new').addEventListener('click', resetEditor);
document.getElementById('temas-deactivate').addEventListener('click', function() {
    fetch('../assets/themes/api.php?action=set-active', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: '' })
    }).then(function(r) { return r.json(); })
      .then(function(d) {
          if (d && d.ok) {
              activeName = '';
              renderList();
              applyLiveTheme('', '');
              /* Sin tema → fondo/icono GLOBAL del usuario (su baseline) */
              applyThemeAssets(USER_GLOBAL_WP, USER_GLOBAL_SI);
              if (window._setWpPreview) window._setWpPreview(USER_GLOBAL_WP);
              if (window._setSiPreview) window._setSiPreview(USER_GLOBAL_SI);
              statusEl.textContent = 'Tema desactivado.';
          }
      });
});

document.getElementById('theme-save').addEventListener('click', function() {
    var name = nameInput.value.trim();
    if (!name) { statusEl.textContent = 'Falta el nombre.'; return; }
    var colors = getEditorColors();
    /* oldName: si estamos editando un tema existente y cambió el nombre,
       el backend renombra el original en vez de crear un duplicado. */
    var payload = { name: name, colors: colors };
    if (editingOriginalName) payload.oldName = editingOriginalName;
    var renamed = !!(editingOriginalName && editingOriginalName !== name);
    fetch('../assets/themes/api.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function(r) { return r.json(); })
      .then(function(d) {
          if (!d || d.error) { statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
          /* Si renombramos el tema activo, refrescar el activeName */
          if (renamed && activeName === editingOriginalName) activeName = name;
          editingOriginalName = name;
          statusEl.textContent = renamed
              ? 'Tema renombrado a "' + name + '".'
              : 'Tema "' + name + '" guardado.';
          loadThemes(function(){
              nameInput.value = name;
              if (savedThemes[name]) setEditorColors(migrateLegacyColors(savedThemes[name].colors || {}));
              setActiveItem(name === activeName ? activeName : name);
          });
      });
});

document.getElementById('theme-activate').addEventListener('click', function() {
    var name = nameInput.value.trim();
    if (!name) { statusEl.textContent = 'Selecciona un tema primero.'; return; }
    if (!savedThemes[name]) { statusEl.textContent = 'Guarda el tema antes de activarlo.'; return; }
    fetch('../assets/themes/api.php?action=set-active', {
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
          /* Aplicar el fondo y el icono vinculados al tema (o el DEFAULT si no tiene) */
          applyThemeAssets(d.wallpaper || THEME_DEFAULT_WP, d.startIcon || THEME_DEFAULT_SI);
          /* Reflejar en Personalización los assets efectivos del tema activo */
          if (savedThemes[name]) { savedThemes[name].wallpaper = d.wallpaper || ''; savedThemes[name].startIcon = d.startIcon || ''; }
          if (window._setWpPreview) window._setWpPreview(d.wallpaper || THEME_DEFAULT_WP);
          if (window._setSiPreview) window._setSiPreview(d.startIcon || THEME_DEFAULT_SI);
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
    /* capi/angie ya no se aplican como clases de body; sólo conservar
       'has-start-icon' que es estructural. */
    var keep = (body.className || '').split(/\s+/).filter(function(c) {
        return c === 'has-start-icon';
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

/* (Eliminar tema → ahora vía clic derecho en la lista: deleteTheme) */

/* =========================================================
   EXPORTAR / IMPORTAR temas (JSON)
   - Export: descarga el tema actual del editor como JSON.
   - Import: carga un JSON al editor; el usuario decide si guardar.
   El formato es self-contained y portable entre usuarios/instalaciones. */
function sanitizeFilename(s) {
    return (s || 'theme').replace(/[^A-Za-z0-9_-]+/g, '_').slice(0, 40);
}

document.getElementById('theme-export').addEventListener('click', function() {
    var name = nameInput.value.trim() || 'tema';
    var payload = {
        format:     'melon-theme',
        version:    1,
        name:       name,
        exportedAt: new Date().toISOString(),
        colors:     getEditorColors()
    };
    var blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href = url;
    a.download = sanitizeFilename(name) + '.melon-theme.json';
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function() { URL.revokeObjectURL(url); }, 1500);
    statusEl.textContent = '✔ "' + name + '" descargado.';
});

document.getElementById('theme-import').addEventListener('click', function() {
    document.getElementById('theme-import-file').click();
});
document.getElementById('theme-import-file').addEventListener('change', function(e) {
    var file = e.target.files && e.target.files[0];
    e.target.value = '';   // resetear para permitir reimportar el mismo fichero
    if (!file) return;
    if (file.size > 100 * 1024) {
        statusEl.textContent = '✗ Fichero demasiado grande (>100KB).';
        return;
    }
    var reader = new FileReader();
    reader.onload = function() {
        var data;
        try { data = JSON.parse(reader.result); }
        catch (err) { statusEl.textContent = '✗ JSON inválido.'; return; }
        if (!data || typeof data !== 'object' || !data.colors || typeof data.colors !== 'object') {
            statusEl.textContent = '✗ Formato de tema desconocido.'; return;
        }
        var colors = migrateLegacyColors(data.colors);
        /* Filtrar a las claves del editor + validar hex */
        var clean = {};
        var bad = 0;
        COLOR_DEFS.forEach(function(def) {
            var v = colors[def.key];
            if (typeof v === 'string' && /^#[0-9a-f]{3,8}$/i.test(v)) clean[def.key] = v;
            else if (v !== undefined) bad++;
        });
        /* Nombre: usa el del fichero o cae al filename sin extensión.
           Si ya existe, añadimos sufijo " (importado)" para no pisar. */
        var nameRaw = (data.name && typeof data.name === 'string')
                      ? data.name
                      : file.name.replace(/\.melon-theme\.json$|\.json$/i, '');
        var name = String(nameRaw).replace(/[^A-Za-z0-9_-]/g, '').slice(0, 30) || 'Importado';
        if (savedThemes[name]) {
            var base = name, n = 2;
            while (savedThemes[name] && name.length < 30) {
                name = (base + '_' + n).slice(0, 30); n++;
                if (n > 99) break;
            }
        }
        nameInput.value = name;
        setEditorColors(clean);
        setActiveItem(null);

        /* Auto-save + auto-activate */
        statusEl.textContent = 'Importando "' + name + '"…';
        fetch('../assets/themes/api.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, colors: clean })
        }).then(function(r) { return r.json(); })
          .then(function(d) {
              if (!d || d.error) throw new Error((d && d.error) || 'Error al guardar');
              return fetch('../assets/themes/api.php?action=set-active', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ name: name })
              }).then(function(r) { return r.json(); }).then(function(d2) {
                  if (!d2 || d2.error) throw new Error((d2 && d2.error) || 'Error al activar');
                  activeName = name;
                  loadThemes();
                  applyLiveTheme(d.className, d.cssPath);
                  statusEl.textContent = '✔ "' + name + '" importado y activado'
                      + (bad ? ' (' + bad + ' valores inválidos descartados)' : '') + '.';
              });
          })
          .catch(function(err) { statusEl.textContent = '✗ ' + err.message; });
    };
    reader.onerror = function() { statusEl.textContent = '✗ Error leyendo el fichero.'; };
    reader.readAsText(file);
});

buildEditor();
resetEditor();
loadThemes();

/* =========================================================
   TABS DE LA SIDEBAR (Mis temas / Personalización)
========================================================= */
(function() {
    var tabs  = document.querySelectorAll('.temas-tab-btn');
    var panes = {
        themes:      document.getElementById('temas-pane-themes'),
        personalize: document.getElementById('temas-pane-personalize'),
        library:     document.getElementById('temas-pane-library')
    };
    var editorEls = ['temas-toolbar', 'temas-editor', 'temas-status'];
    var libEl     = document.getElementById('temas-library');
    var persEl    = document.getElementById('temas-personalize');

    tabs.forEach(function(t) {
        t.addEventListener('click', function() {
            tabs.forEach(function(b) { b.classList.remove('active'); });
            t.classList.add('active');
            var which = t.dataset.tab;
            Object.keys(panes).forEach(function(k) {
                if (k === which) panes[k].removeAttribute('hidden');
                else             panes[k].setAttribute('hidden', '');
            });
            /* El main panel muestra:
                 - editor de colores (themes)
                 - grid de biblioteca (library)
                 - vista de personalización (personalize, NUEVO) */
            var lib = (which === 'library');
            var pers = (which === 'personalize');
            editorEls.forEach(function(id){
                var el = document.getElementById(id);
                if (el) el.style.display = (lib || pers) ? 'none' : '';
            });
            libEl.hidden  = !lib;
            persEl.hidden = !pers;
            if (lib)  loadLibrary();
            if (pers) loadPersonalize();
        });
    });
})();

/* =========================================================
   PERSONALIZACIÓN — Temas / Haros / Mascotas
   --------------------------------------------------------
   Vista que reemplaza al editor de colores cuando se entra
   en la pestaña "Personalización". Muestra 3 secciones con
   lo que el usuario tiene desbloqueado (base + comprado en
   tienda) y permite cambiar el activo con un click.
========================================================= */
function loadPersonalize() {
    /* Las 3 secciones (Interfaces / Haros / Mascotas) salen del mismo
       endpoint. "Interfaces" son skins/apariencias de la app — items
       con categoria='temas' en tienda_items. Los TEMAS DE COLORES del
       usuario (savedThemes) son otra cosa distinta y viven en el
       sidebar "Mis temas". */
    fetch('../assets/personalize/api.php?action=inventory', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) throw new Error(d && d.error || 'error');
            renderPersInventory('pers-themes-grid',  d.interfaces, d.activeInterface, 'interface');
            renderPersInventory('pers-haros-grid',   d.haros,      d.activeHaro,      'haro');
            renderPersInventory('pers-mascots-grid', d.mascots,    d.activeMascot,    'mascot');
        })
        .catch(function(){
            document.getElementById('pers-themes-grid').innerHTML  = '';
            document.getElementById('pers-haros-grid').innerHTML   = '';
            document.getElementById('pers-mascots-grid').innerHTML = '';
        });
}

function renderPersInventory(gridId, items, activeId, kind) {
    var grid = document.getElementById(gridId);
    grid.innerHTML = '';
    if (!items || !items.length) return;
    items.forEach(function(it){
        /* Para haros el icono es el PNG del último frame del gif. Para
           el resto se usa el emoji del campo `icono`. */
        var iconHtml;
        if (kind === 'haro' && it.slug) {
            /* Convención: PNG curado del haro en assets/img/haro/
               {slug}Haro-preview.png. Para haros antiguos sin curated,
               cae al último frame del gif (assets/vids/{slug}Haro-last.png). */
            iconHtml = '<img'
                + ' src="../assets/img/haro/' + it.slug + 'Haro-preview.png"'
                + ' onerror="this.onerror=null;this.src=\'../assets/vids/' + it.slug + 'Haro-last.png\';"'
                + ' alt=""'
                + ' style="max-width:100%;max-height:100%;object-fit:contain;">';
        } else {
            iconHtml = it.icono || '◽';
        }
        grid.appendChild(buildPersItem({
            id:     it.id,
            name:   it.nombre,
            icon:   iconHtml,
            base:   it.isBase,
            active: activeId === it.id,
            onClick: function(){
                if (kind === 'interface') setActiveInterface(it);
                if (kind === 'haro')      setActiveHaro(it);
                if (kind === 'mascot')    setActiveMascot(it);
            }
        }));
    });
}

function setActiveInterface(item) {
    fetch('../assets/personalize/api.php?action=set-active-interface', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemId: item.id })
    })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) throw new Error(d && d.error || 'error');
            loadPersonalize();
        })
        .catch(function(){});
}

function buildPersItem(opts) {
    var el = document.createElement('div');
    el.className = 'pers-item' + (opts.active ? ' active' : '');
    el.dataset.id = opts.id;
    var icon = document.createElement('div');
    icon.className = 'pers-item-icon';
    icon.innerHTML = opts.icon || '';
    el.appendChild(icon);
    var name = document.createElement('div');
    name.className = 'pers-item-name';
    name.textContent = opts.name || '';
    el.appendChild(name);
    if (opts.base) {
        var b = document.createElement('div');
        b.className = 'pers-item-badge';
        b.textContent = 'BASE';
        el.appendChild(b);
    }
    el.addEventListener('click', function(){
        if (typeof opts.onClick === 'function') opts.onClick();
    });
    return el;
}

function setActiveHaro(item) {
    fetch('../assets/personalize/api.php?action=set-active-haro', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemId: item.id })
    })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) throw new Error(d && d.error || 'error');
            /* Aplicación al instante en el escritorio padre: pasa el
               nuevo slug y el notifSystem cambia sus URLs sin recargar. */
            var slug = d.slug || item.slug || '';
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'haro-changed', slug: slug }, '*');
            }
            loadPersonalize();
        })
        .catch(function(){});
}

function setActiveMascot(item) {
    fetch('../assets/personalize/api.php?action=set-active-mascot', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemId: item.id })
    })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d || !d.ok) throw new Error(d && d.error || 'error');
            if (typeof window.applyMascotSlug === 'function') {
                window.applyMascotSlug(item.slug || '');
            }
            loadPersonalize();
        })
        .catch(function(){});
}

/* =========================================================
   BIBLIOTECA DE TEMAS (públicos de todos los usuarios)
========================================================= */
var OWN_USER_KEY = <?php echo json_encode($userKey); ?>;

function _libColor(colors, key, def){
    var v = colors && colors[key];
    return (typeof v === 'string' && /^#[0-9a-f]{3,8}$/i.test(v)) ? v : def;
}

/* Mini-maqueta de una ventana Win98 con los colores del tema */
function buildThemePreview(colors){
    var c = migrateLegacyColors(colors || {});
    var winBg   = _libColor(c, 'winBg', '#c0c0c0');
    var bodyBg  = _libColor(c, 'winBodyBg', winBg);
    var tbStart = _libColor(c, 'titlebarStart', '#000080');
    var tbEnd   = _libColor(c, 'titlebarEnd', '#1084d0');
    var tbText  = _libColor(c, 'titlebarText', '#ffffff');
    var accent  = _libColor(c, 'accent', '#000080');
    var accentT = _libColor(c, 'accentText', '#ffffff');
    var btnBg   = _libColor(c, 'btnBg', '#c0c0c0');
    var btnText = _libColor(c, 'btnText', '#000000');
    var text    = _libColor(c, 'text', '#000000');
    var inset   = _libColor(c, 'insetBg', '#808080');
    var border  = _libColor(c, 'border', '#808080');
    return '' +
    '<div class="lib-win" style="background:' + winBg + ';border-color:' + border + ';">' +
        '<div class="lib-win-tb" style="background:linear-gradient(to right,' + tbStart + ',' + tbEnd + ');color:' + tbText + ';">' +
            '<span>Aa</span>' +
            '<span class="lib-win-tb-dots">' +
                '<i style="background:' + btnBg + ';"></i>' +
                '<i style="background:' + btnBg + ';"></i>' +
            '</span>' +
        '</div>' +
        '<div class="lib-win-body" style="background:' + bodyBg + ';">' +
            '<div class="lib-win-row" style="background:' + inset + ';width:90%;"></div>' +
            '<div class="lib-win-row" style="background:' + inset + ';width:65%;"></div>' +
            '<div class="lib-win-btns">' +
                '<span class="lib-win-btn" style="background:' + btnBg + ';color:' + btnText + ';border:1px solid ' + border + ';">Ok</span>' +
                '<span class="lib-win-btn" style="background:' + accent + ';color:' + accentT + ';">★</span>' +
            '</div>' +
        '</div>' +
    '</div>';
}

function loadLibrary(){
    var grid = document.getElementById('temas-library-grid');
    grid.innerHTML = '<div id="temas-library-empty">Cargando…</div>';
    fetch('../assets/themes/api.php?action=library')
        .then(function(r){ return r.json(); })
        .then(function(d){
            if(!d || !d.ok || !Array.isArray(d.items) || !d.items.length){
                grid.innerHTML = '<div id="temas-library-empty">Aún no hay temas publicados.<br>Publica uno desde «Mis temas».</div>';
                return;
            }
            grid.innerHTML = '';
            d.items.forEach(function(it){
                var card = document.createElement('div');
                card.className = 'lib-card';

                var prev = document.createElement('div');
                prev.className = 'lib-preview';
                prev.title = 'Probar este tema';
                /* El fondo del preview representa el escritorio del tema:
                   color desktopBg + wallpaper encima si el tema trae uno. */
                var prevColors = migrateLegacyColors(it.colors || {});
                prev.style.backgroundColor = _libColor(prevColors, 'desktopBg', '#008080');
                if (it.wallpaper) {
                    prev.style.backgroundImage    = 'url("../' + it.wallpaper + '")';
                    prev.style.backgroundSize     = 'cover';
                    prev.style.backgroundPosition = 'center';
                }
                prev.innerHTML = buildThemePreview(it.colors);
                prev.addEventListener('click', function(){ tryLibraryTheme(it); });
                card.appendChild(prev);

                var nm = document.createElement('div');
                nm.className = 'lib-name';
                nm.textContent = it.name;
                card.appendChild(nm);

                var author = document.createElement('div');
                author.className = 'lib-author';
                author.title = 'Ver el perfil de ' + it.label;
                var imgHtml = it.image
                    ? '<img class="lib-author-img" src="../' + it.image + '" alt="">'
                    : '<span class="lib-author-ph">👤</span>';
                author.innerHTML = imgHtml + '<span class="lib-author-name">' + escapeHtml(it.label) + '</span>';
                author.addEventListener('click', function(e){
                    e.stopPropagation();
                    window.parent.postMessage({ type: 'open-profile', userKey: it.userKey }, '*');
                });
                card.appendChild(author);

                /* Clic derecho → si el tema es MÍO, opción de quitarlo de la biblioteca */
                if(it.userKey === OWN_USER_KEY){
                    card.addEventListener('contextmenu', function(e){
                        e.preventDefault();
                        showLibraryCtxMenu(e.clientX, e.clientY, it);
                    });
                }

                grid.appendChild(card);
            });
        })
        .catch(function(){
            grid.innerHTML = '<div id="temas-library-empty">✗ No se pudo cargar la biblioteca.</div>';
        });
}

/* Clic derecho sobre una tarjeta de la biblioteca que es MÍA → quitarla */
function showLibraryCtxMenu(x, y, it){
    hideThemeCtxMenu();
    var menu = document.createElement('div');
    menu.className = 'temas-ctx-menu';
    var el = document.createElement('div');
    el.className = 'temas-ctx-opt danger';
    el.textContent = '🗑 Quitar de la biblioteca';
    el.addEventListener('click', function(){
        hideThemeCtxMenu();
        fetch('../assets/themes/api.php?action=set-public', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: it.name, public: false })
        }).then(function(r){ return r.json(); })
          .then(function(d){
              if(!d || d.error){ statusEl.textContent = (d && d.error) ? d.error : 'Error'; return; }
              if(savedThemes[it.name]) savedThemes[it.name].public = false;
              statusEl.textContent = 'Tema "' + it.name + '" quitado de la biblioteca.';
              loadLibrary();
          });
    });
    menu.appendChild(el);
    document.body.appendChild(menu);
    var mw = menu.offsetWidth, mh = menu.offsetHeight;
    menu.style.left = Math.min(x, window.innerWidth  - mw - 4) + 'px';
    menu.style.top  = Math.min(y, window.innerHeight - mh - 4) + 'px';
    _themeCtxMenu = menu;
}

/* Probar un tema de la biblioteca: pide confirmación y, al aceptar,
   lo guarda en "Mis temas" del usuario. */
function tryLibraryTheme(it){
    var extras = [];
    if (it.wallpaper) extras.push('fondo');
    if (it.startIcon) extras.push('icono');
    var extraNote = extras.length
        ? '<br><small>Incluye ' + extras.join(' e ') + '.</small>'
        : '';
    window._w98Confirm({
        title: 'Descargar tema',
        html: '¿Descargar el tema <strong>' + escapeHtml(it.name) + '</strong> de '
              + escapeHtml(it.label) + '?<br><small>Se guardará en «Mis temas».</small>' + extraNote,
        okText: 'Descargar',
        onOk: function(){ downloadLibraryTheme(it); }
    });
}

/* Guarda el tema de la biblioteca en los temas del usuario (action=save).
   Si ya existe uno con ese nombre, añade un sufijo para no pisarlo. */
function downloadLibraryTheme(it){
    var colors = migrateLegacyColors(it.colors || {});
    var name = it.name;
    if (savedThemes[name]) {
        var base = name.slice(0, 26), n = 2;
        while (savedThemes[name] && n < 99) { name = base + '_' + n; n++; }
    }
    var labelSafe = <?php echo json_encode(preg_replace('/[^A-Za-z0-9_-]/', '', $userLabel)); ?>;
    fetch('../assets/themes/api.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, colors: colors, downloaded: true,
                               wallpaper: it.wallpaper || '', startIcon: it.startIcon || '' })
    }).then(function(r){ return r.json(); })
      .then(function(d){
          if (!d || d.error) { statusEl.textContent = (d && d.error) ? d.error : 'Error al guardar'; return; }
          editingOriginalName = name;
          /* Activar el tema descargado para aplicar de golpe colores + fondo + icono.
             set-active devuelve los assets ya copiados al espacio del usuario. */
          return fetch('../assets/themes/api.php?action=set-active', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ name: name })
          }).then(function(r){ return r.json(); })
            .then(function(d2){
                activeName = name;
                var className = name + '-' + labelSafe;
                applyLiveTheme(className, 'assets/themes/' + className + '.css');
                applyThemeAssets((d2 && d2.wallpaper) || THEME_DEFAULT_WP,
                                 (d2 && d2.startIcon) || THEME_DEFAULT_SI);
                /* Ir a "Mis temas", refrescar lista/previews y dejarlo seleccionado */
                document.querySelector('.temas-tab-btn[data-tab="themes"]').click();
                loadThemes(function(){
                    nameInput.value = name;
                    setEditorColors(colors);
                    setActiveItem(name);
                });
                statusEl.textContent = '✔ "' + name + '" descargado y activado.';
            });
      })
      .catch(function(){ statusEl.textContent = '✗ Error de red al guardar.'; });
}

function escapeHtml(s){
    return String(s).replace(/[&<>"]/g, function(ch){
        return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;' })[ch];
    });
}

document.getElementById('temas-lib-refresh').addEventListener('click', loadLibrary);

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
    /* Refresca el preview con el valor EFECTIVO que pasa el llamador
       (vacío = limpia el preview, mostrando el hueco hundido). */
    window._setWpPreview = function(rel){ currentWp = rel || ''; setPreview(currentWp); };

    wpBrowse.addEventListener('click', function() { wpFile.click(); });
    wpFile.addEventListener('change', function() {
        wpName.value = this.files.length ? this.files[0].name : '';
        wpStatus.textContent = '';
    });

    wpSave.addEventListener('click', function() {
        if (!wpFile.files.length) { wpStatus.textContent = 'Elige una imagen primero.'; return; }
        var fd = new FormData();
        fd.append('wallpaper', wpFile.files[0]);
        /* Vincular el fondo al tema activo (si lo hay) */
        if (activeName) fd.append('theme', activeName);
        wpStatus.textContent = 'Subiendo…';
        wpSave.classList.add('btn-busy');
        fetch('../assets/img/wallpapers/save-wallpaper.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                wpSave.classList.remove('btn-busy');
                if (!d || d.error) { wpStatus.textContent = (d && d.error) ? d.error : 'Error'; return; }
                wpStatus.textContent = activeName ? 'Fondo vinculado al tema "' + activeName + '".' : 'Fondo actualizado.';
                currentWp = d.wallpaper;
                if (activeName && savedThemes[activeName]) savedThemes[activeName].wallpaper = d.wallpaper;
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

/* =========================================================
   ICONO DEL BOTÓN DE INICIO
========================================================= */
(function() {
    var siFile    = document.getElementById('temas-starticon-file');
    var siName    = document.getElementById('temas-starticon-name');
    var siBrowse  = document.getElementById('temas-starticon-browse');
    var siSave    = document.getElementById('temas-starticon-save');
    var siPreview = document.getElementById('temas-starticon-preview');
    var siStatus  = document.getElementById('temas-starticon-status');

    var currentSi = <?php echo json_encode(getUserStartIcon($userLabel)); ?>;

    function setPreview(rel) {
        if (rel) {
            siPreview.style.backgroundImage = 'url("../' + rel + '?t=' + Date.now() + '")';
        } else {
            siPreview.style.backgroundImage = '';
        }
    }
    setPreview(currentSi);
    window._setSiPreview = function(rel){ currentSi = rel || ''; setPreview(currentSi); };

    siBrowse.addEventListener('click', function() { siFile.click(); });
    siFile.addEventListener('change', function() {
        siName.value = this.files.length ? this.files[0].name : '';
        siStatus.textContent = '';
    });

    siSave.addEventListener('click', function() {
        if (!siFile.files.length) { siStatus.textContent = 'Elige una imagen primero.'; return; }
        var fd = new FormData();
        fd.append('icon', siFile.files[0]);
        if (activeName) fd.append('theme', activeName);
        siStatus.textContent = 'Subiendo…';
        siSave.classList.add('btn-busy');
        fetch('../assets/img/start-icons/save-start-icon.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                siSave.classList.remove('btn-busy');
                if (!d || d.error) { siStatus.textContent = (d && d.error) ? d.error : 'Error'; return; }
                siStatus.textContent = activeName ? 'Icono vinculado al tema "' + activeName + '".' : 'Icono actualizado.';
                currentSi = d.icon;
                if (activeName && savedThemes[activeName]) savedThemes[activeName].startIcon = d.icon;
                setPreview(currentSi);
                if (window.parent && window.parent !== window) {
                    window.parent.postMessage({ type: 'start-icon-changed', icon: currentSi }, '*');
                }
                siFile.value = '';
                siName.value = '';
            })
            .catch(function() { siSave.classList.remove('btn-busy'); siStatus.textContent = 'Error de red.'; });
    });
})();

</script>

<!-- Modal Win98 para confirmar eliminación de tema -->
<div class="window" id="theme-delete-modal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); min-width:340px; max-width:460px; z-index:8500; flex-direction:column;">
    <div class="title-bar">
        <div class="title-bar-text">Confirmar eliminación de tema</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="theme-delete-x"></button>
        </div>
    </div>
    <div class="window-body w98-confirm-body">
        <div class="w98-confirm-row">
            <div class="w98-icon-question"></div>
            <div class="w98-confirm-text" id="theme-delete-text">¿Eliminar el tema?</div>
        </div>
        <div class="w98-confirm-btns">
            <button id="theme-delete-ok" class="default">Sí</button>
            <button id="theme-delete-cancel">No</button>
        </div>
    </div>
</div>

<script>
/* Modal Win98 de confirmación genérico (sustituye al confirm() nativo). */
(function(){
    var modal = document.getElementById('theme-delete-modal');
    var titleEl = modal.querySelector('.title-bar-text');
    function openConfirm(opts){
        /* opts: { title, html, okText, onOk } */
        if(titleEl) titleEl.textContent = opts.title || 'Confirmar';
        document.getElementById('theme-delete-text').innerHTML = opts.html || '¿Continuar?';
        document.getElementById('theme-delete-ok').textContent = opts.okText || 'Sí';
        modal.style.display = 'flex';
        function cleanup(){
            modal.style.display = 'none';
            document.getElementById('theme-delete-ok').onclick = null;
            document.getElementById('theme-delete-cancel').onclick = null;
            document.getElementById('theme-delete-x').onclick = null;
            document.removeEventListener('keydown', keyHandler);
        }
        function ok(){ cleanup(); if(opts.onOk) opts.onOk(); }
        function cancel(){ cleanup(); }
        function keyHandler(ev){
            if(ev.key === 'Enter'){ ev.preventDefault(); ok(); }
            else if(ev.key === 'Escape'){ ev.preventDefault(); cancel(); }
        }
        document.getElementById('theme-delete-ok').onclick = ok;
        document.getElementById('theme-delete-cancel').onclick = cancel;
        document.getElementById('theme-delete-x').onclick = cancel;
        document.addEventListener('keydown', keyHandler);
    }
    window._w98Confirm = openConfirm;
    /* Compat: confirmación de borrado de tema */
    window._w98ConfirmDeleteTheme = function(name, onOk){
        openConfirm({
            title: 'Confirmar eliminación de tema',
            html: '¿Eliminar el tema <strong>' + name.replace(/[&<>]/g, function(c){
                      return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c];
                  }) + '</strong>?',
            okText: 'Sí', onOk: onOk
        });
    };
})();
</script>

<!-- LCD filter toggle: sincroniza checkbox <-> localStorage <-> clase en
     <html>. Si esta página está embebida en iframe (mobile.php o
     desktop-base.php cargan temas/ vía iframe), aplicamos también la
     clase en el documento padre — mismo origen, acceso directo OK. -->
<script>
(function(){
    var KEY = 'lcd-filter';
    var cb = document.getElementById('lcd-filter-toggle');
    if (!cb) return;
    /* Default ON: filtro activo salvo que el usuario lo desactive explícitamente. */
    function get(){ try { return localStorage.getItem(KEY) !== '0'; } catch(_) { return true; } }
    function apply(on){
        document.documentElement.classList.toggle('lcd-filter-on', on);
        if (window.top !== window) {
            try { window.top.document.documentElement.classList.toggle('lcd-filter-on', on); } catch(_) {}
        }
    }
    cb.checked = get();
    apply(cb.checked);
    cb.addEventListener('change', function(){
        try { localStorage.setItem(KEY, cb.checked ? '1' : '0'); } catch(_) {}
        apply(cb.checked);
    });
})();
</script>

</body>
</html>
