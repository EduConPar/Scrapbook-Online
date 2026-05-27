<?php
session_start();
require_once dirname(__DIR__) . '/assets/config.php';
header('Content-Type: text/html; charset=UTF-8');

$userKey   = $_SESSION['user'] ?? null;
$userLabel = ($userKey && isset($loginUsers[$userKey])) ? $loginUsers[$userKey]['label'] : '';

/* Tema activo del usuario (mismo patrón que calendario.php) */
$activeThemeClass = '';
$activeThemeCss   = '';
$themeHelpers = dirname(__DIR__) . '/assets/themes/theme-helpers.php';
if ($userKey && file_exists($themeHelpers)) {
    require_once $themeHelpers;
    refreshActiveThemeCss($userKey, $userLabel);
    $_userThemes = loadUserThemes($userKey);
    $activeTheme = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
    if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
        $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
        $rel = themeCssRelPath($activeTheme, $userLabel);
        if (file_exists(dirname(__DIR__) . '/' . $rel)) $activeThemeCss = '../' . $rel;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>D&D Fichas</title>
<link rel="stylesheet" href="../assets/css/tokens.css">
<link rel="stylesheet" href="../assets/css/themes.css">
<?php if ($activeThemeCss): ?>
<link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
<?php endif; ?>
<style>
@font-face { font-family:'Allison'; src:url('../assets/fonts/Allison-Regular.ttf') format('truetype'); }
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

body{
    font-family:'ms_sans_serif','Microsoft Sans Serif',sans-serif;
    font-size:11px;
    background:var(--win-body-bg);
    color:var(--text);
    height:100vh;overflow:hidden;display:flex;flex-direction:column;
}

/* ── TOOLBAR ── */
#dnd-toolbar{
    background:var(--btn-bg);
    border-bottom:2px solid var(--bezel-dark-2);
    padding:4px 6px;display:flex;gap:4px;align-items:center;flex-shrink:0;flex-wrap:wrap;
    position:relative;z-index:20;   /* tooltips encima de page-tabs / sheet-area */
}
.tb-btn{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    padding:2px 10px;cursor:pointer;
    background:var(--btn-bg);color:var(--btn-text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    white-space:nowrap;user-select:none;
}
.tb-btn:active,.tb-btn.active{
    border-top:2px solid var(--bezel-dark-2);border-left:2px solid var(--bezel-dark-2);
    border-right:2px solid var(--bezel-light-1);border-bottom:2px solid var(--bezel-light-1);
    background:var(--btn-bg);filter:brightness(0.93);
}
.tb-btn:hover:not(.active):not(:active){filter:brightness(1.06);}

/* Tabs de selección de ficha activas → color de acento del SO */
#tab-oficial.active, #tab-melon.active, #tab-misfichas.active{
    background:var(--accent);
    color:var(--accent-text);
    filter:none;
    font-weight:bold;
}
.tb-sep{width:1px;background:var(--bezel-dark-2);height:20px;margin:0 2px;}
.tb-spacer{flex:1;}
#status-msg{font-size:10px;color:var(--link-text);min-width:180px;padding:0 4px;transition:opacity .3s;}
#status-msg.fade{opacity:0;}

/* Zoom controls */
#zoom-controls{display:flex;align-items:center;gap:4px;}
#zoom-controls label{font-size:10px;color:var(--text-muted);}
#zoom-controls .tb-btn{padding:1px 6px;min-width:18px;font-weight:bold;}
#zoom-slider{width:110px;cursor:pointer;accent-color:var(--accent);}
#zoom-val{
    font-size:10px;width:40px;text-align:center;
    background:var(--input-bg);color:var(--input-text);
    border:1px inset var(--bezel-dark-2);padding:1px 2px;
}

/* Tooltip global */
.has-tip{position:relative;}
.has-tip:hover::after{
    content:attr(data-tip);position:absolute;
    bottom:calc(100% + 4px);left:50%;transform:translateX(-50%);
    background:var(--warning-bg);color:var(--warning-text);
    font-size:10px;padding:3px 7px;border-radius:3px;
    white-space:nowrap;z-index:9999;pointer-events:none;
    border:1px solid var(--bezel-dark-2);
}
/* Botones pegados al borde superior del iframe → tooltip BAJO el botón
   (si quedan arriba se recortan fuera de la ventana) */
#dnd-toolbar .has-tip:hover::after,
#mf-toolbar  .has-tip:hover::after{
    bottom:auto;
    top:calc(100% + 4px);
    /* permitir que tooltips largos hagan wrap en lugar de extenderse a los lados */
    white-space:normal;
    max-width:260px;
    text-align:center;
}
/* Tooltips de los primeros botones de la izquierda → anclar al borde izquierdo
   del botón (en vez de centrar) para que no se corten por la ventana */
#tab-oficial:hover::after,
#tab-melon:hover::after,
#tab-misfichas:hover::after{
    left:0;
    transform:none;
}

/* ── TABS (estilo Win98 ─ borde elevado, tab activa hundida en la página) ── */
#page-tabs{
    background:var(--btn-bg);
    border-top:2px solid var(--bezel-light-1);
    border-bottom:2px solid var(--bezel-dark-2);
    padding:4px 6px 0;display:flex;gap:2px;flex-shrink:0;min-height:28px;
}
.pg-tab{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    padding:3px 16px 4px;cursor:pointer;
    background:var(--btn-bg);color:var(--btn-text);
    /* Tab elevada al estilo Win98 */
    border-top:2px solid var(--bezel-light-1);
    border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);
    border-bottom:none;
    border-radius:4px 4px 0 0;
    position:relative;top:2px;user-select:none;
}
.pg-tab:hover:not(.active){filter:brightness(1.06);}
.pg-tab.active{
    background:var(--btn-bg);color:var(--btn-text);font-weight:bold;z-index:1;
    padding-top:5px;padding-bottom:5px;top:0;     /* tab activa "sale" hacia arriba */
}

/* ── SHEET AREA: fondo liso del tema, borde hundido Win98 ── */
#sheet-area{
    flex:1;overflow:auto;
    background:var(--win-body-bg);
    border-top:2px solid var(--bezel-dark-2);
    border-left:2px solid var(--bezel-dark-2);
    border-right:2px solid var(--bezel-light-1);
    border-bottom:2px solid var(--bezel-light-1);
    padding:10px;display:flex;justify-content:center;align-items:flex-start;
}
.sheet-page{display:none;}
.sheet-page.visible{display:block;}

/* ── PDF PAGE ── */
.pdf-page-container{
    position:relative;display:inline-block;
    box-shadow:3px 3px 12px rgba(0,0,0,.6);
    background:#fff;
}
.pdf-canvas{display:block;user-select:none;}
.pdf-form-layer{
    position:absolute;inset:0;
    pointer-events:none; /* los hijos sí reciben eventos */
}
.pdf-form-layer input,
.pdf-form-layer textarea,
.pdf-form-layer select{
    position:absolute;
    pointer-events:auto;
    font-family:Helvetica,'Helvetica Neue',Arial,sans-serif;
    padding:0 1px;
    line-height:1.15;
    box-sizing:border-box;
    outline:none;
    margin:0;
}
.pdf-form-layer input[type="text"],
.pdf-form-layer input[type="number"],
.pdf-form-layer input[type="password"]{
    line-height:1;        /* single-line: el browser centra verticalmente */
}
/* Inputs del PDF: NO siguen el tema. Fondo y borde semi-transparentes para
   marcar dónde están las cajas sin tapar el PDF. */
.pdf-form-layer input[type="text"],
.pdf-form-layer input[type="number"],
.pdf-form-layer input[type="password"],
.pdf-form-layer textarea{
    background:rgba(255,255,255,0.25) !important;
    color:#000 !important;
    border:1px solid rgba(0,0,0,0.18) !important;
    border-radius:0 !important;
    box-shadow:0 1px 2px rgba(0,0,0,0.08) !important;
}
.pdf-form-layer input[type="text"]:hover:not(:focus),
.pdf-form-layer input[type="number"]:hover:not(:focus),
.pdf-form-layer textarea:hover:not(:focus){
    background:rgba(180,210,255,0.30) !important;
    border-color:rgba(0,0,0,0.28) !important;
}
.pdf-form-layer input[type="text"]:focus,
.pdf-form-layer input[type="number"]:focus,
.pdf-form-layer textarea:focus{
    background:rgba(255,235,160,0.50) !important;
    border-color:rgba(0,0,0,0.45) !important;
    box-shadow:0 1px 3px rgba(0,0,0,0.15) !important;
}
.pdf-form-layer textarea{resize:none;overflow:auto;}
/* Checkboxes/radios: default del navegador, sin accent del tema */
.pdf-form-layer input[type="checkbox"],
.pdf-form-layer input[type="radio"]{
    cursor:pointer;
}
/* Select: sin borde, sólo caja blanca legible y flechita */
.pdf-form-layer select{
    appearance:none;-webkit-appearance:none;
    cursor:pointer;
    padding-right:14px;
    background-color:#fff !important;
    color:#000 !important;
    border:none !important;
    box-shadow:none !important;
    background-image:
        linear-gradient(45deg, transparent 50%, #000 50%),
        linear-gradient(135deg, #000 50%, transparent 50%) !important;
    background-position:
        calc(100% - 8px) 50%,
        calc(100% - 4px) 50%;
    background-size:4px 4px, 4px 4px;
    background-repeat:no-repeat;
}
/* Aptitud mágica (melon): mismo look que un input + compacto.
   El font-size se asigna en JS al ~70% de la fuente normal del campo para
   que escale con el zoom como el resto y nunca quede gigante. */
.pdf-form-layer select.spell-ability{
    background-color:rgba(255,255,255,0.25) !important;
    border:1px solid rgba(0,0,0,0.18) !important;
    border-radius:0 !important;
    box-shadow:0 1px 2px rgba(0,0,0,0.08) !important;
    padding:0 14px 0 2px;
    line-height:1.1;
}
.pdf-form-layer select.spell-ability:hover:not(:focus){
    background-color:rgba(180,210,255,0.30) !important;
    border-color:rgba(0,0,0,0.28) !important;
}
.pdf-form-layer select.spell-ability:focus{
    background-color:rgba(255,235,160,0.50) !important;
    border-color:rgba(0,0,0,0.45) !important;
    box-shadow:0 1px 3px rgba(0,0,0,0.15) !important;
}
/* Readonly: tono apagado neutro (anula override de themes.css) */
.pdf-form-layer input[readonly],
.pdf-form-layer textarea[readonly]{
    background:transparent !important;
    color:#333 !important;
}

/* ── MIS FICHAS ── */
#mis-fichas-panel{display:none;flex:1;flex-direction:column;overflow:hidden;background:var(--btn-bg);}
#mis-fichas-panel.visible{display:flex;}
#mf-toolbar{
    background:var(--btn-bg);border-bottom:1px solid var(--bezel-dark-2);
    padding:5px 8px;display:flex;gap:6px;align-items:center;flex-shrink:0;flex-wrap:wrap;
    position:relative;z-index:20;   /* tooltip queda por encima de #mf-list */
}
#mf-toolbar .tb-btn{flex-shrink:0;}
#mf-toolbar strong{color:var(--btn-text);}
#mf-toolbar input{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    border:1px inset var(--bezel-dark-2);background:var(--input-bg);color:var(--input-text);
    padding:2px 4px;flex:1;max-width:220px;
}
#mf-list{flex:1;overflow:auto;padding:10px;display:flex;flex-wrap:wrap;gap:10px;align-content:flex-start;}
.ficha-card{
    background:var(--btn-bg);color:var(--text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    width:260px;padding:14px 16px;position:relative;cursor:default;transition:filter .1s;
}
.ficha-card:hover{filter:brightness(1.05);}
.fc-tipo{font-size:11px;color:var(--text);margin-bottom:6px;font-weight:bold;}
.fc-nombre{
    font-size:20px;font-family:'ms_sans_serif',sans-serif;font-weight:bold;
    color:var(--text);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:6px;
}
.fc-info{font-size:11px;color:var(--text);line-height:1.5;}
.fc-fecha{font-size:11px;color:var(--text);margin-top:6px;}
.fc-btns{margin-top:10px;display:flex;gap:6px;}
.fc-btns button,.fc-del{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;padding:3px 10px;cursor:pointer;
    background:var(--btn-bg);color:var(--text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
}
.fc-btns button:hover,.fc-del:hover{filter:brightness(1.06);}
.fc-del{position:absolute;top:6px;right:6px;padding:2px 7px;font-size:11px;color:var(--text);}
#mf-empty{width:100%;text-align:center;color:var(--text-muted);padding:40px 20px;line-height:2;}

/* ── BOTTOM BAR ── */
#bottom-bar{
    background:var(--btn-bg);border-top:2px solid var(--bezel-dark-2);
    padding:4px 8px;display:flex;gap:6px;align-items:center;flex-shrink:0;
}
#bottom-bar .info{font-size:10px;color:var(--text-muted);flex:1;}
#autosave-dot{
    width:8px;height:8px;border-radius:50%;
    background:var(--text-muted);display:inline-block;margin-right:4px;transition:background .3s;
}
#autosave-dot.saved{background:var(--link-text);}
#autosave-dot.saving{background:var(--warning-bg);}

/* ── MODALES ── */
#modal-confirm,#modal-import{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.45);z-index:8000;
    align-items:center;justify-content:center;
}
#modal-confirm.show,#modal-import.show{display:flex;}
#import-box{
    background:var(--btn-bg);color:var(--text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    min-width:320px;max-width:420px;
}
#import-body{padding:14px 18px;}
#modal-confirm{z-index:8500;}
#modal-box,#confirm-box{
    background:var(--btn-bg);color:var(--btn-text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    min-width:300px;
}
#confirm-box{min-width:260px;}
.modal-titlebar{
    background:var(--titlebar-start);color:var(--titlebar-text);
    font-weight:bold;font-size:11px;padding:3px 6px;
    display:flex;justify-content:space-between;align-items:center;user-select:none;
}
.modal-titlebar button{
    background:var(--btn-bg);color:var(--btn-text);
    border-top:1px solid var(--bezel-light-1);border-left:1px solid var(--bezel-light-1);
    border-right:1px solid var(--bezel-dark-2);border-bottom:1px solid var(--bezel-dark-2);
    font-size:9px;width:16px;height:14px;cursor:pointer;
}
#modal-body{padding:12px 16px;}
#modal-body label{display:block;margin-bottom:4px;font-size:11px;color:var(--btn-text);}
#modal-body input{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    border:1px inset var(--bezel-dark-2);background:var(--input-bg);color:var(--input-text);
    padding:2px 4px;width:100%;margin-bottom:10px;
}
.modal-btns{display:flex;gap:6px;justify-content:flex-end;}
#confirm-body{padding:14px 18px;font-size:11px;line-height:1.6;color:var(--btn-text);}
#confirm-btns{padding:0 18px 12px;display:flex;gap:6px;justify-content:flex-end;}
#confirm-ok{color:var(--link-text);}

/* ── PROGRESS ── */
#progress-overlay{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.5);z-index:9999;
    align-items:center;justify-content:center;
}
#progress-overlay.show{display:flex;}
#progress-box{
    background:var(--btn-bg);color:var(--btn-text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    padding:16px 24px;text-align:center;min-width:260px;
}
#progress-box p{margin-bottom:10px;font-size:11px;color:var(--btn-text);}
#pb-wrap{width:100%;height:16px;background:var(--input-bg);border:1px inset var(--bezel-dark-2);}
#pb{height:100%;background:var(--accent);width:0%;transition:width .25s;}

/* ── TOAST ── */
#toast{
    position:fixed;bottom:50px;left:50%;transform:translateX(-50%) translateY(20px);
    background:var(--btn-bg);color:var(--btn-text);
    border:1px solid var(--bezel-dark-2);
    font-size:11px;padding:6px 16px;border-radius:3px;
    opacity:0;transition:opacity .25s, transform .25s;
    pointer-events:none;z-index:9990;
    box-shadow:1px 1px 4px rgba(0,0,0,.3);
}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);}

/* ── DICE ROLLER ── */
#dice-panel{
    position:fixed;top:90px;right:30px;z-index:9995;
    width:260px;
    background:var(--win-bg);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    box-shadow:3px 3px 10px rgba(0,0,0,.4);
    user-select:none;
}
#dice-titlebar{
    background:linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
    color:var(--titlebar-text);
    font-weight:bold;font-size:11px;
    padding:3px 6px;display:flex;align-items:center;justify-content:space-between;
    cursor:move;
}
#dice-close{
    background:var(--btn-bg);color:var(--btn-text);
    border-top:1px solid var(--bezel-light-1);border-left:1px solid var(--bezel-light-1);
    border-right:1px solid var(--bezel-dark-2);border-bottom:1px solid var(--bezel-dark-2);
    width:16px;height:14px;line-height:1;font-size:9px;cursor:pointer;padding:0;
}
#dice-close:active{filter:brightness(0.9);}
#dice-body{padding:10px;}
#dice-buttons{display:flex;flex-wrap:wrap;gap:4px;justify-content:center;margin-bottom:10px;}
.dice-btn{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;font-weight:bold;
    padding:3px 8px;cursor:pointer;min-width:38px;
    background:var(--btn-bg);color:var(--btn-text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
}
.dice-btn:hover{filter:brightness(1.08);}
.dice-btn:active{
    border-top:2px solid var(--bezel-dark-2);border-left:2px solid var(--bezel-dark-2);
    border-right:2px solid var(--bezel-light-1);border-bottom:2px solid var(--bezel-light-1);
}
#dice-stage{
    height:150px;background:var(--inset-bg);
    border:2px solid var(--bezel-dark-2);
    box-shadow:inset 1px 1px 3px rgba(0,0,0,.4);
    margin-bottom:8px;overflow:hidden;position:relative;
}
#dice-canvas{ width:100%;height:100%;display:block;cursor:pointer; }
/* Capa de números: un badge por dado, posicionado proyectando su 3D → 2D */
#dice-overlays{ position:absolute;inset:0;pointer-events:none;z-index:2; }
.dice-val{
    position:absolute;transform:translate(-50%,-50%);
    font-family:'ms_sans_serif','Microsoft Sans Serif',sans-serif;
    font-weight:bold;font-size:18px;color:var(--accent-text);
    text-shadow:0 1px 2px rgba(0,0,0,.6), 0 0 3px rgba(0,0,0,.5);
    line-height:1;white-space:nowrap;
}
#dice-hint{
    position:absolute;bottom:3px;left:0;right:0;text-align:center;
    font-size:8px;color:var(--text-muted);pointer-events:none;z-index:2;
    opacity:.8;
}
#dice-actions{ display:flex;gap:6px;justify-content:center;align-items:stretch;margin-bottom:8px; }
#dice-actions .tb-btn{ padding:3px 10px;font-weight:bold; }
/* Botón modificador: + arriba, − abajo */
#dice-mod-btn{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    width:26px;line-height:1;cursor:pointer;padding:0;
    background:var(--btn-bg);color:var(--btn-text);
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
}
#dice-mod-btn:hover{ filter:brightness(1.08); }
#dice-mod-btn:active{
    border-top:2px solid var(--bezel-dark-2);border-left:2px solid var(--bezel-dark-2);
    border-right:2px solid var(--bezel-light-1);border-bottom:2px solid var(--bezel-light-1);
}
.dmod-plus{ font-size:11px;font-weight:bold; }
.dmod-minus{ font-size:13px;font-weight:bold;margin-top:-2px; }
#dice-result{
    text-align:center;font-size:12px;color:var(--text);min-height:16px;
}
/* Modal "Añadir modificador" (dentro del panel de dados) */
#dice-mod-modal{
    position:absolute;inset:0;z-index:10;
    background:rgba(0,0,0,.35);
    display:flex;align-items:center;justify-content:center;
}
#dice-mod-box{
    background:var(--win-bg);width:180px;
    border-top:2px solid var(--bezel-light-1);border-left:2px solid var(--bezel-light-1);
    border-right:2px solid var(--bezel-dark-2);border-bottom:2px solid var(--bezel-dark-2);
    box-shadow:2px 2px 8px rgba(0,0,0,.4);
}
.dice-mod-title{
    background:linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
    color:var(--titlebar-text);font-weight:bold;font-size:11px;padding:3px 6px;
}
.dice-mod-body{ padding:12px;text-align:center; }
#dice-mod-input{ width:100%;text-align:center;font-size:16px;padding:3px;box-sizing:border-box; }
.dice-mod-btns{ display:flex;justify-content:flex-end;gap:4px;padding:0 8px 10px; }
#dice-result .dice-crit{ color:var(--link-text);font-weight:bold; }
#dice-result .dice-fail{ color:var(--error-text);font-weight:bold; }
</style>
</head>
<body class="<?php
    $bc = [];
    if ($activeThemeClass) $bc[] = $activeThemeClass;
    echo htmlspecialchars(implode(' ', $bc));
?>">

<!-- TOOLBAR -->
<div id="dnd-toolbar">
    <button class="tb-btn active has-tip" id="tab-oficial"   onclick="switchSheet('oficial')"   data-tip="Ficha oficial D&D 5e (PDF editable, 5 páginas)">📜 Oficial</button>
    <button class="tb-btn has-tip"        id="tab-melon" onclick="switchSheet('melon')" data-tip="Ficha melon alternativa">🎨 Melon</button>
    <button class="tb-btn has-tip"        id="tab-misfichas" onclick="switchToMisFichas()"      data-tip="Ver todas tus fichas guardadas">📁 Mis Fichas</button>
    <div class="tb-sep"></div>
    <button class="tb-btn has-tip" id="btn-dados" onclick="toggleDicePanel()" data-tip="Tirar dados (d4–d100)">🎲 Dados</button>
    <div class="tb-sep"></div>
    <div id="zoom-controls">
        <label>Zoom:</label>
        <button class="tb-btn" onclick="zoomStep(-10)" title="Alejar">−</button>
        <input type="range" id="zoom-slider" min="40" max="300" value="100" step="10" oninput="setZoom(this.value)">
        <button class="tb-btn" onclick="zoomStep(10)" title="Acercar">+</button>
        <span id="zoom-val">100%</span>
        <button class="tb-btn" onclick="resetZoom()" title="Ajustar al alto">⛶</button>
    </div>
    <div class="tb-sep"></div>
    <span id="sheet-tools" style="display:flex;gap:4px;align-items:center;">
        <button class="tb-btn has-tip" onclick="undoLast()" id="btn-undo" data-tip="Deshacer último cambio (Ctrl+Z)">↩ Deshacer</button>
        <button class="tb-btn has-tip" onclick="clearAll()" data-tip="Borrar todos los campos de la ficha actual">🗑 Limpiar</button>
    </span>
    <div class="tb-spacer"></div>
    <span id="status-msg"></span>
</div>

<!-- PAGE TABS -->
<div id="page-tabs"></div>

<!-- SHEET AREA -->
<div id="sheet-area"></div>

<!-- MIS FICHAS -->
<div id="mis-fichas-panel">
    <div id="mf-toolbar">
        <strong>📁 Mis Fichas <span style="font-weight:normal;color:var(--text-muted);">(Google Drive)</span></strong>
        <input type="text" id="mf-search" placeholder="Buscar por nombre..." oninput="renderFichasList()">
        <span id="mf-count" style="font-size:10px;color:var(--text-muted);"></span>
        <button class="tb-btn has-tip" onclick="triggerImport()" data-tip="Importar un PDF con datos">⬆ Importar</button>
        <input type="file" id="import-file" accept="application/pdf,.pdf" style="display:none" onchange="onImportFile(event)">
        <button class="tb-btn has-tip" onclick="fetchAndRenderFichas()" data-tip="Recargar lista">↻</button>
        <button class="tb-btn has-tip" onclick="disconnectDrive()" data-tip="Cerrar sesión de Drive">🔌</button>
    </div>
    <div id="mf-list"></div>
</div>

<!-- BOTTOM BAR -->
<div id="bottom-bar">
    <button class="tb-btn has-tip" onclick="saveToDrive()" id="btn-drive-save" data-tip="Guardar el PDF rellenado en tu Google Drive">☁ Guardar Drive</button>
    <button class="tb-btn has-tip" onclick="exportPDF()" data-tip="Descargar el PDF al disco local">⬇ PDF</button>
    <span id="drive-status" style="font-size:10px;color:var(--text-muted);margin-left:4px;"></span>
    <div class="info">
        <span id="autosave-dot"></span>Autosave local
    </div>
</div>

<!-- DICE ROLLER (ventana flotante draggable) -->
<div id="dice-panel" style="display:none;">
    <div id="dice-titlebar">
        <span>🎲 Lanzar dados</span>
        <button id="dice-close" onclick="toggleDicePanel(false)" title="Cerrar">✕</button>
    </div>
    <div id="dice-body">
        <div id="dice-buttons">
            <button class="dice-btn" data-sides="4"   onclick="addDie(4)">d4</button>
            <button class="dice-btn" data-sides="6"   onclick="addDie(6)">d6</button>
            <button class="dice-btn" data-sides="8"   onclick="addDie(8)">d8</button>
            <button class="dice-btn" data-sides="10"  onclick="addDie(10)">d10</button>
            <button class="dice-btn" data-sides="12"  onclick="addDie(12)">d12</button>
            <button class="dice-btn" data-sides="20"  onclick="addDie(20)">d20</button>
            <button class="dice-btn" data-sides="100" onclick="addDie(100)">d100</button>
        </div>
        <div id="dice-stage">
            <canvas id="dice-canvas"></canvas>
            <div id="dice-overlays"></div>
            <div id="dice-hint">Pulsa un dado para añadirlo · clic en un dado para quitarlo</div>
        </div>
        <div id="dice-actions">
            <button class="tb-btn" id="dice-reroll" onclick="rerollAll()">🎲 Tirar dados</button>
            <button class="tb-btn" id="dice-clear" onclick="clearDice()">🗑 Limpiar</button>
            <button id="dice-mod-btn" onclick="openModifierDialog()" title="Añadir modificador">
                <span class="dmod-plus">＋</span><span class="dmod-minus">−</span>
            </button>
        </div>
        <div id="dice-result">Elige un dado</div>
    </div>
    <!-- Sub-ventana: añadir modificador -->
    <div id="dice-mod-modal" style="display:none;">
        <div id="dice-mod-box">
            <div class="dice-mod-title">Añadir modificador</div>
            <div class="dice-mod-body">
                <input type="number" id="dice-mod-input" value="0" step="1">
            </div>
            <div class="dice-mod-btns">
                <button class="tb-btn" onclick="closeModifierDialog()">Cancelar</button>
                <button class="tb-btn" onclick="applyModifier()">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELEGIR HOJA AL IMPORTAR -->
<div id="modal-import">
    <div id="import-box">
        <div class="modal-titlebar"><span>⬆ Importar PDF</span><button onclick="closeImportModal()">✕</button></div>
        <div id="import-body">
            <p style="margin-bottom:14px;font-size:11px;color:var(--text);">
                ¿A qué hoja pertenece <strong id="import-file-name"></strong>?
            </p>
            <div class="modal-btns">
                <button class="tb-btn" onclick="doImport('oficial')">📜 Oficial</button>
                <button class="tb-btn" onclick="doImport('melon')">🎨 Melon</button>
                <button class="tb-btn" onclick="closeImportModal()">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR BORRAR -->
<div id="modal-confirm">
    <div id="confirm-box">
        <div class="modal-titlebar"><span>⚠ Confirmar</span></div>
        <div id="confirm-body"></div>
        <div id="confirm-btns">
            <button class="tb-btn" id="confirm-cancel">Cancelar</button>
            <button class="tb-btn" id="confirm-ok" style="color:#800000;">✕ Eliminar</button>
        </div>
    </div>
</div>

<!-- PROGRESS -->
<div id="progress-overlay">
    <div id="progress-box"><p id="pb-text">Generando PDF...</p><div id="pb-wrap"><div id="pb"></div></div></div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<!-- PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Google Identity Services (OAuth para Drive) -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
/* ════════════════════════════════════════
   CONFIG
════════════════════════════════════════ */
pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

var SHEETS = {
    oficial: {
        pdfUrl:     '../assets/pdf/Hoja_de_personaje_Editable.pdf',
        storageKey: 'dnd_pdf_oficial',
        nameField:  'CharacterName',
        infoFields: ['ClassLevel','Race','Background'],
        defaultFont:'Helvetica, Arial, sans-serif'
    },
    melon: {
        pdfUrl:     '../assets/pdf/Hoja DND.pdf',
        storageKey: 'dnd_pdf_melon',
        nameField:  'doc_0_doc_0_Text_1',   /* primer textbox arriba-izquierda = Nombre */
        infoFields: [],
        defaultFont:"'Allison', cursive"
    }
};

var currentSheet = 'oficial';
var currentPage  = 0;
var inMisFichas  = false;
var fitScale     = 1.0;                 // escala que hace que la página ocupe todo el alto
var userZoom     = 1.0;                 // multiplicador del usuario (slider). 1.0 = ajuste-al-alto
var currentZoom  = 1.5;                 // = fitScale * userZoom (usado por PDF.js)
var pdfDocs      = {};                  // cache de PDFDocumentProxy por sheet
var sheetState   = {};                  // sheet → { pageCount, fieldNames:Set }

/* ════════════════════════════════════════
   CARGA Y RENDER
════════════════════════════════════════ */
async function buildSheet(key){
    currentSheet = key; currentPage = 0; inMisFichas = false;
    undoStack = [];

    var area = document.getElementById('sheet-area');
    var tabs = document.getElementById('page-tabs');
    area.innerHTML = ''; tabs.innerHTML = '';
    area.style.display = 'flex';
    document.getElementById('bottom-bar').style.display = 'flex';
    document.getElementById('mis-fichas-panel').classList.remove('visible');

    var sheet = SHEETS[key];
    setStatus('Cargando PDF…');
    try {
        var pdf = pdfDocs[key];
        if(!pdf){
            pdf = await pdfjsLib.getDocument(sheet.pdfUrl).promise;
            pdfDocs[key] = pdf;
        }
        var state = { pageCount: pdf.numPages, fieldNames: new Set() };
        sheetState[key] = state;

        // Auto-fit vertical sobre 1ª página + multiplicador del usuario
        fitScale    = await computeFitScale(pdf);
        currentZoom = Math.max(0.2, fitScale * userZoom);

        for(var pi=0; pi<pdf.numPages; pi++){
            var tab = document.createElement('button');
            tab.className = 'pg-tab'+(pi===0?' active':'');
            tab.textContent = 'Pág.'+(pi+1);
            tab.dataset.pi = pi;
            (function(idx){ tab.onclick = function(){ showPage(idx); }; })(pi);
            tabs.appendChild(tab);

            var wrap = document.createElement('div');
            wrap.className = 'sheet-page'+(pi===0?' visible':'');
            wrap.id = key+'-pg-'+pi;
            area.appendChild(wrap);

            await renderPage(pdf, pi, wrap, state);
        }

        // Aplicar datos guardados
        var stored = loadStored(sheet.storageKey);
        if(stored) applyData(stored);
        runCalcs();
        attachChangeListeners();
        _lastSavedState = JSON.stringify(collectData());
        setStatus('✔ Cargado ('+pdf.numPages+' páginas)');
    } catch(e){
        console.error(e);
        setStatus('✗ Error: '+e.message);
        showToast('✗ No se pudo cargar el PDF');
    }
}

async function renderPage(pdf, pageIndex, wrap, state){
    var page = await pdf.getPage(pageIndex+1);
    var viewport = page.getViewport({ scale: currentZoom });

    var container = document.createElement('div');
    container.className = 'pdf-page-container';
    container.style.width  = viewport.width+'px';
    container.style.height = viewport.height+'px';
    wrap.appendChild(container);

    var canvas = document.createElement('canvas');
    canvas.className = 'pdf-canvas';
    canvas.width  = viewport.width;
    canvas.height = viewport.height;
    container.appendChild(canvas);

    await page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;

    var formLayer = document.createElement('div');
    formLayer.className = 'pdf-form-layer';
    container.appendChild(formLayer);

    var annotations = await page.getAnnotations();
    annotations.forEach(function(annot){
        if(annot.subtype !== 'Widget') return;
        if(!annot.fieldName) return;
        createFormField(formLayer, annot, viewport, pageIndex, state);
    });
}

/* Crea un input/textarea/checkbox/select para una anotación AcroForm */
function createFormField(layer, annot, viewport, pageIndex, state){
    // Coordenadas: rect = [x1,y1,x2,y2] en puntos PDF (origen abajo-izquierda)
    var r = pdfjsLib.Util.normalizeRect(annot.rect);
    var tl = viewport.convertToViewportPoint(r[0], r[3]); // top-left
    var br = viewport.convertToViewportPoint(r[2], r[1]); // bottom-right
    var left   = Math.min(tl[0], br[0]);
    var top    = Math.min(tl[1], br[1]);
    var width  = Math.abs(br[0] - tl[0]);
    var height = Math.abs(br[1] - tl[1]);

    var el = null;
    var ft = annot.fieldType;

    /* Override universal: en la hoja melon el campo de aptitud mágica
       siempre se presenta como <select> CHA/SAB/INT, sin importar si en el
       PDF original es Tx (text) o Ch (choice) con otras opciones. */
    if(currentSheet === 'melon' && annot.fieldName === MELON_SPELL_ABILITY){
        el = document.createElement('select');
        el.className = 'spell-ability';
        [['','—'],['CHA','Carisma'],['WIS','Sabiduría'],['INT','Inteligencia']].forEach(function(o){
            var opt = document.createElement('option');
            opt.value = o[0]; opt.textContent = o[1];
            el.appendChild(opt);
        });
        var fv0 = annot.fieldValue;
        if(typeof fv0 === 'string' && ['CHA','WIS','INT'].indexOf(fv0) !== -1) el.value = fv0;
        ft = 'Tx'; /* que el resto del flujo lo trate como editable normal */
    }
    else if(ft === 'Tx'){
        if(annot.multiLine){
            el = document.createElement('textarea');
        } else {
            el = document.createElement('input');
            el.type = annot.password ? 'password' : 'text';
        }
        if(annot.maxLen) el.maxLength = annot.maxLen;
        var v = annot.fieldValue;
        if(v !== undefined && v !== null) el.value = Array.isArray(v) ? v.join('\n') : String(v);
    }
    else if(ft === 'Btn'){
        if(annot.checkBox){
            el = document.createElement('input');
            el.type = 'checkbox';
            el.checked = !!annot.fieldValue && annot.fieldValue !== 'Off';
        } else if(annot.radioButton){
            el = document.createElement('input');
            el.type = 'radio';
            el.name = annot.fieldName;
            el.value = annot.buttonValue || 'On';
            el.checked = annot.fieldValue === el.value;
        } else {
            return; // pushButton: no es campo de datos
        }
    }
    else if(ft === 'Ch'){
        el = document.createElement('select');
        var blank = document.createElement('option');
        blank.value = ''; blank.textContent = '—';
        el.appendChild(blank);
        (annot.options||[]).forEach(function(o){
            var opt = document.createElement('option');
            opt.value = o.exportValue != null ? o.exportValue : o.displayValue;
            opt.textContent = o.displayValue;
            el.appendChild(opt);
        });
        if(annot.fieldValue) el.value = annot.fieldValue;
    }
    else {
        return; // Sig u otros: omitir
    }

    el.dataset.fieldName = annot.fieldName;
    el.dataset.pageIndex = pageIndex;
    state.fieldNames.add(annot.fieldName);

    el.style.left   = left+'px';
    el.style.top    = top+'px';
    el.style.width  = width+'px';
    el.style.height = height+'px';

    // Font: la fuente del PDF (Helv/HeBo). Si el sheet define defaultFont propio
    // (p.ej. melon → Allison), se usa ese como fallback.
    var sheetCfg = SHEETS[currentSheet] || {};
    var da = annot.defaultAppearanceData || {};
    var fs;
    if(da.fontSize && da.fontSize > 0){
        fs = da.fontSize * viewport.scale;             // tamaño fijo PDF → px
    } else {
        // auto-fit: ~65 % del alto pero capado a 14pt (lo que hace Acrobat para
        // que un nombre largo no se desborde y no aparezca texto enorme).
        fs = Math.min(14 * viewport.scale, Math.max(8, height * 0.65));
    }
    el.style.fontFamily = mapPdfFont(da.fontName, sheetCfg.defaultFont);
    // Detectar variantes bold (HeBo, Helvetica-Bold, Times-Bold, etc.)
    if(da.fontName && /bo|bold/i.test(da.fontName)) el.style.fontWeight = 'bold';
    if(da.fontName && /(it|italic|ob|oblique)$/i.test(da.fontName)) el.style.fontStyle = 'italic';
    if(el.tagName !== 'INPUT' || (el.type !== 'checkbox' && el.type !== 'radio')){
        /* El dropdown de aptitud mágica va al ~45% para no quedar enorme:
           es muy corto (3 letras) y el cuadro original del PDF es grande. */
        var isSpell = el.classList && el.classList.contains('spell-ability');
        el.style.fontSize = (isSpell ? fs * 0.45 : fs) + 'px';
        if(isSpell){
            /* Bajar también el alto para que no quede una caja gigante */
            el.style.height = Math.max(14, height * 0.45) + 'px';
            /* Recolocar verticalmente para mantenerlo centrado en el hueco */
            el.style.top = (top + (height - parseFloat(el.style.height)) / 2) + 'px';
        }
    }
    if(annot.textAlignment === 1) el.style.textAlign = 'center';
    if(annot.textAlignment === 2) el.style.textAlign = 'right';
    if(annot.readOnly){ el.readOnly = true; el.tabIndex = -1; el.style.opacity = 0.85; }
    // Campos auto-calculados: bloquear edición (sin alterar colores del PDF)
    if(ft === 'Tx' && (
        (currentSheet === 'oficial' && isCalcField(annot.fieldName)) ||
        (currentSheet === 'melon'   && isMelonCalcField(annot.fieldName))
    )){
        el.readOnly = true; el.tabIndex = -1;
        el.title = 'Campo auto-calculado';
    }

    layer.appendChild(el);
}

function mapPdfFont(name, defaultFont){
    var fallback = defaultFont || 'Helvetica, Arial, sans-serif';
    if(!name) return fallback;
    var n = String(name).toLowerCase();
    // Helvetica family (Helv, HeBo = Helvetica-Bold) → fallback del sheet (Helvetica)
    if(n.indexOf('helv') !== -1 || n.indexOf('hebo') !== -1 ||
       n.indexOf('arial') !== -1) return fallback;
    if(n.indexOf('time') !== -1 || n.indexOf('roman') !== -1 ||
       n.indexOf('tibo') !== -1) return "'Times New Roman', Times, serif";
    if(n.indexOf('cour') !== -1 || n.indexOf('mono') !== -1) return "'Courier New', Courier, monospace";
    if(n.indexOf('allison') !== -1) return "'Allison', cursive";
    return fallback;
}

/* ════════════════════════════════════════
   AUTO-FIT VERTICAL + ZOOM
════════════════════════════════════════ */
async function computeFitScale(pdf){
    var page = await pdf.getPage(1);
    var base = page.getViewport({ scale: 1 });
    var area = document.getElementById('sheet-area');
    // Esperar a que el layout esté hecho (clientHeight puede ser 0 al principio)
    if(area.clientHeight < 200){
        await new Promise(function(r){ requestAnimationFrame(function(){ requestAnimationFrame(r); }); });
    }
    var availH = Math.max(300, area.clientHeight - 20);
    // Sin suelo: PDFs grandes (melon = 2480x3508) necesitan escalas <0.4
    return availH / base.height;
}

function rerenderPreservingData(){
    if(!pdfDocs[currentSheet]) return;
    var data = collectData();
    var pageBefore = currentPage;
    /* Cancelar autosave pendiente: durante el rebuild el DOM se vacía y el
       timer dispararía collectData() sobre 0 inputs, clobbering localStorage.
       Volcamos AHORA el snapshot bueno a disco para que tampoco se pierda si
       el rebuild fallara a medias. */
    clearTimeout(_autosaveTimer);
    clearTimeout(_pushUndoTimer);
    try {
        localStorage.setItem(SHEETS[currentSheet].storageKey, JSON.stringify(data));
        _lastSavedState = JSON.stringify(data);
    } catch(e){}
    buildSheet(currentSheet).then(function(){
        applyData(data);
        runCalcs();
        _lastSavedState = JSON.stringify(collectData());
        /* Restaurar la página que estaba viendo el usuario; buildSheet
           reinicia currentPage=0 siempre. */
        if(pageBefore > 0 && typeof showPage === 'function') showPage(pageBefore);
    });
}

var _zoomRebuildTimer = null;
function setZoom(val){
    userZoom = parseInt(val) / 100;
    document.getElementById('zoom-val').textContent = val + '%';
    document.getElementById('zoom-slider').value = val;
    clearTimeout(_zoomRebuildTimer);
    _zoomRebuildTimer = setTimeout(rerenderPreservingData, 220);
}
function zoomStep(delta){
    var s = document.getElementById('zoom-slider');
    var v = Math.max(parseInt(s.min), Math.min(parseInt(s.max), parseInt(s.value) + delta));
    setZoom(v);
}
function resetZoom(){ setZoom(100); }

var _resizeTimer = null;
window.addEventListener('resize', function(){
    if(inMisFichas) return;
    clearTimeout(_resizeTimer);
    _resizeTimer = setTimeout(rerenderPreservingData, 220);
});

/* Ctrl + rueda del ratón = zoom in/out sobre la ficha */
document.getElementById('sheet-area').addEventListener('wheel', function(e){
    if(!(e.ctrlKey || e.metaKey)) return;   // sin ctrl/cmd: scroll normal
    e.preventDefault();
    zoomStep(e.deltaY < 0 ? 10 : -10);
}, { passive: false });

/* ════════════════════════════════════════
   DATOS (basados en fieldName del AcroForm)
════════════════════════════════════════ */
function collectData(){
    var data = {};
    document.querySelectorAll('.pdf-form-layer input, .pdf-form-layer textarea, .pdf-form-layer select').forEach(function(el){
        var name = el.dataset.fieldName; if(!name) return;
        if(el.type === 'checkbox'){
            data[name] = el.checked;
        } else if(el.type === 'radio'){
            if(el.checked) data[name] = el.value;
        } else {
            data[name] = el.value;
        }
    });
    return data;
}
function applyData(data){
    if(!data) return;
    document.querySelectorAll('.pdf-form-layer input, .pdf-form-layer textarea, .pdf-form-layer select').forEach(function(el){
        var name = el.dataset.fieldName; if(!name) return;
        if(!(name in data)) return;
        var v = data[name];
        if(el.type === 'checkbox') el.checked = !!v;
        else if(el.type === 'radio') el.checked = (el.value === v);
        else el.value = (v == null ? '' : v);
    });
}
function loadStored(key){ try{ var r=localStorage.getItem(key); return r?JSON.parse(r):null; }catch(e){ return null; } }

/* ════════════════════════════════════════
   CÁLCULOS AUTOMÁTICOS DEL PDF OFICIAL
   Replican los scripts /C (Calculate) del AcroForm.
════════════════════════════════════════ */
var ABILITIES = ['STR','DEX','CON','INT','WIS','CHA'];
var SKILL_MAP = {
    Acrobatics:    { stat:'DEX', prof:'acroPROF'    },
    AnHan:         { stat:'WIS', prof:'anhanPROF'   },
    Arcana:        { stat:'INT', prof:'arcanaPROF'  },
    Athletics:     { stat:'STR', prof:'athPROF'     },
    Deception:     { stat:'CHA', prof:'decepPROF'   },
    History:       { stat:'INT', prof:'histPROF'    },
    Insight:       { stat:'WIS', prof:'insightPROF' },
    Intimidation:  { stat:'CHA', prof:'intimPROF'   },
    Investigation: { stat:'INT', prof:'investPROF'  },
    Medicine:      { stat:'WIS', prof:'medPROF'     },
    Nature:        { stat:'INT', prof:'naturePROF'  },
    Perception:    { stat:'WIS', prof:'perPROF'     },
    Performance:   { stat:'CHA', prof:'perfPROF'    },
    Persuasion:    { stat:'CHA', prof:'persPROF'    },
    Religion:      { stat:'INT', prof:'religPROF'   },
    SleightofHand: { stat:'DEX', prof:'sohPROF'     },
    Stealth:       { stat:'DEX', prof:'stealthPROF' },
    Survival:      { stat:'WIS', prof:'survPROF'    }
};
var CALC_FIELDS = [
    'STRbonus','DEXbonus','CONbonus','INTbonus','WISbonus','CHAbonus',
    'STRsave','DEXsave','CONsave','INTsave','WISsave','CHAsave',
    'Acrobatics','AnHan','Arcana','Athletics','Deception','History','Insight',
    'Intimidation','Investigation','Medicine','Nature','Perception',
    'Performance','Persuasion','Religion','SleightofHand','Stealth','Survival',
    'PWP','SpellSaveDC','SAB'
];
function isCalcField(name){ return CALC_FIELDS.indexOf(name) !== -1; }

/* Mapeo de los campos opacos del PDF melon a la semántica STR/DEX/etc.
   - `score`: lo escribe el usuario
   - `bonus`: auto-calc floor((score-10)/2)
   - `save`:  auto-calc bonus + (savePROF? ProfBonus : 0)
   - `savePROF`: checkbox de competencia en salvación
   Los `bonus` y `save` se marcan readOnly al renderizar la página. */
var MELON_PROF_FIELD  = 'doc_0_doc_0_Text_25';
/* Campos de aptitud mágica (página 3 del melon): el primero se convierte
   en dropdown CHA/SAB/INT; los otros dos se auto-calculan.
   Estos sí van sin prefijo doc_0_doc_X_ (la página 3 los registra al root). */
var MELON_SPELL_ABILITY = 'Text_1';
var MELON_SPELL_DC      = 'Text_2';
var MELON_SPELL_ATK     = 'Text_3';
var MELON_MAP = {
    STR: { score: 'doc_0_doc_0_Text_58', bonus: 'doc_0_doc_0_Text_52', save: 'doc_0_doc_0_Text_29', savePROF: 'doc_0_doc_0_Checkbox_1' },
    DEX: { score: 'doc_0_doc_0_Text_59', bonus: 'doc_0_doc_0_Text_53', save: 'doc_0_doc_0_Text_30', savePROF: 'doc_0_doc_0_Checkbox_2' },
    CON: { score: 'doc_0_doc_0_Text_60', bonus: 'doc_0_doc_0_Text_54', save: 'doc_0_doc_0_Text_31', savePROF: 'doc_0_doc_0_Checkbox_3' },
    INT: { score: 'doc_0_doc_0_Text_61', bonus: 'doc_0_doc_0_Text_55', save: 'doc_0_doc_0_Text_32', savePROF: 'doc_0_doc_0_Checkbox_4' },
    WIS: { score: 'doc_0_doc_0_Text_62', bonus: 'doc_0_doc_0_Text_56', save: 'doc_0_doc_0_Text_33', savePROF: 'doc_0_doc_0_Checkbox_5' },
    CHA: { score: 'doc_0_doc_0_Text_63', bonus: 'doc_0_doc_0_Text_57', save: 'doc_0_doc_0_Text_34', savePROF: 'doc_0_doc_0_Checkbox_6' }
};
/* Skills (activas) y pasivas. Cada entrada: stat=ability base, field=campo
   donde se escribe el resultado, prof=checkbox de competencia, passive=
   true para las "pasivas" (10 + bonus + competencia). */
var MELON_SKILL_MAP = [
    /* ACTIVAS */
    { name: 'Acrobacias',  stat: 'DEX', field: 'doc_0_doc_0_Text_35', prof: 'doc_0_doc_0_Checkbox_7'  },
    { name: 'Aguante',     stat: 'CON', field: 'doc_0_doc_0_Text_36', prof: 'doc_0_doc_0_Checkbox_8'  },
    { name: 'Atletismo',   stat: 'STR', field: 'doc_0_doc_0_Text_37', prof: 'doc_0_doc_0_Checkbox_9'  },
    { name: 'JuegoManos',  stat: 'DEX', field: 'doc_0_doc_0_Text_38', prof: 'doc_0_doc_0_Checkbox_10' },
    { name: 'Sigilo',      stat: 'DEX', field: 'doc_0_doc_0_Text_39', prof: 'doc_0_doc_0_Checkbox_11' },
    { name: 'TAnimales',   stat: 'WIS', field: 'doc_0_doc_0_Text_40', prof: 'doc_0_doc_0_Checkbox_12' },
    /* PASIVAS (10 + bonus + competencia) */
    { name: 'Percepcion',  stat: 'WIS', field: 'doc_0_doc_0_Text_41', prof: 'doc_0_doc_0_Checkbox_13', passive: true },
    { name: 'Perspicacia', stat: 'CHA', field: 'doc_0_doc_0_Text_42', prof: 'doc_0_doc_0_Checkbox_14', passive: true },
    { name: 'CArcano',     stat: 'INT', field: 'doc_0_doc_0_Text_43', prof: 'doc_0_doc_0_Checkbox_15', passive: true }
];
var MELON_CALC_FIELDS = (function(){
    var f = [];
    Object.keys(MELON_MAP).forEach(function(k){
        f.push(MELON_MAP[k].bonus, MELON_MAP[k].save);
    });
    MELON_SKILL_MAP.forEach(function(s){ f.push(s.field); });
    f.push(MELON_SPELL_DC, MELON_SPELL_ATK);
    return f;
})();
function isMelonCalcField(name){ return MELON_CALC_FIELDS.indexOf(name) !== -1; }

function _qField(name){
    return document.querySelector('.pdf-form-layer [data-field-name="'+ name.replace(/"/g,'\\"') +'"]');
}
function _fldNum(name){
    var el = _qField(name); if(!el) return null;
    var v = (el.value||'').toString().replace(/^\+/,'').trim();
    if(v === '') return null;
    var n = parseFloat(v); return isNaN(n) ? null : n;
}
function _fldChk(name){ var el = _qField(name); return !!(el && el.checked); }
function _fldSet(name, val){ var el = _qField(name); if(el) el.value = (val === null || val === undefined) ? '' : val; }
function _fmtMod(n){ return n > 0 ? '+'+n : String(n); }

function runCalcs(){
    if(currentSheet === 'melon'){ runCalcsMelon(); return; }
    if(currentSheet !== 'oficial') return;
    /* ProfBonus es editable por el usuario; _fldNum acepta tanto "2" como
       "+2", así que no hace falta reformatearlo en cada keystroke. */
    var prof = _fldNum('ProfBonus');

    var bonuses = {};
    ABILITIES.forEach(function(ab){
        var score = _fldNum(ab+'score');
        var bonus;
        if(score === null){ bonus = null; _fldSet(ab+'bonus',''); }
        else { bonus = Math.floor((score - 10) / 2); _fldSet(ab+'bonus', _fmtMod(bonus)); }
        bonuses[ab] = bonus;
        if(score === null){ _fldSet(ab+'save',''); }
        else {
            var sv = bonus + (_fldChk(ab+'savePROF') && prof !== null ? prof : 0);
            _fldSet(ab+'save', _fmtMod(sv));
        }
    });

    Object.keys(SKILL_MAP).forEach(function(skill){
        var m = SKILL_MAP[skill]; var bn = bonuses[m.stat];
        if(bn === null){ _fldSet(skill, ''); return; }
        var v = bn + (_fldChk(m.prof) && prof !== null ? prof : 0);
        _fldSet(skill, _fmtMod(v));
    });

    // Percepción pasiva: 10 + WISbonus + (perPROF ? ProfBonus : 0)
    if(bonuses.WIS === null) _fldSet('PWP','');
    else _fldSet('PWP', 10 + bonuses.WIS + (_fldChk('perPROF') && prof !== null ? prof : 0));

    // SpellSaveDC y SAB: dependen del SpellAbility (1=INT, 2=WIS, 3=CHA)
    var sa = _qField('SpellAbility');
    var saVal = sa ? sa.value : '';
    var saMap = { '1':'INT', '2':'WIS', '3':'CHA' };
    var sk = saMap[saVal];
    if(sk && bonuses[sk] !== null && prof !== null){
        _fldSet('SpellSaveDC', 8 + prof + bonuses[sk]);
        _fldSet('SAB',             prof + bonuses[sk]);
    } else {
        _fldSet('SpellSaveDC',''); _fldSet('SAB','');
    }
}

/* Cálculos de la hoja melon:
   - Modificador: floor((score-10)/2). 15 → +2.
   - Salvación: modificador + (checkbox PROF marcada ? ProfBonus : 0).
   - Skill activa:  modificador + (PROF ? ProfBonus : 0).
   - Skill pasiva:  10 + modificador + (PROF ? ProfBonus : 0). */
function runCalcsMelon(){
    var prof = _fldNum(MELON_PROF_FIELD);
    var bonuses = {};
    ABILITIES.forEach(function(ab){
        var m = MELON_MAP[ab]; if(!m) return;
        var score = _fldNum(m.score);
        if(score === null){
            bonuses[ab] = null;
            _fldSet(m.bonus, '');
            _fldSet(m.save,  '');
            return;
        }
        var bonus = Math.floor((score - 10) / 2);
        bonuses[ab] = bonus;
        _fldSet(m.bonus, _fmtMod(bonus));
        var sv = bonus + (_fldChk(m.savePROF) && prof !== null ? prof : 0);
        _fldSet(m.save, _fmtMod(sv));
    });
    MELON_SKILL_MAP.forEach(function(s){
        var b = bonuses[s.stat];
        if(b === null || b === undefined){ _fldSet(s.field, ''); return; }
        var extra = (_fldChk(s.prof) && prof !== null) ? prof : 0;
        var v = b + extra;
        _fldSet(s.field, s.passive ? String(10 + v) : _fmtMod(v));
    });
    /* Aptitud mágica: CD = 8 + prof + mod, BAC = prof + mod (mod del stat
       elegido en el dropdown). Vacío si no hay aptitud, prof o score. */
    var sa  = _qField(MELON_SPELL_ABILITY);
    var stat = sa ? sa.value : '';
    var bn   = bonuses[stat];
    if(stat && bn !== null && bn !== undefined && prof !== null){
        _fldSet(MELON_SPELL_DC,  String(8 + prof + bn));
        _fldSet(MELON_SPELL_ATK, _fmtMod(prof + bn));
    } else {
        _fldSet(MELON_SPELL_DC, '');
        _fldSet(MELON_SPELL_ATK, '');
    }
}

/* ════════════════════════════════════════
   AUTOSAVE / UNDO / LISTENERS
════════════════════════════════════════ */
var undoStack = [];
var MAX_UNDO  = 30;
function pushUndo(){
    undoStack.push(JSON.stringify(collectData()));
    if(undoStack.length > MAX_UNDO) undoStack.shift();
}
function undoLast(){
    if(!undoStack.length){ showToast('Sin cambios para deshacer'); return; }
    var prev = JSON.parse(undoStack.pop());
    applyData(prev);
    runCalcs();
    saveLocal(true);
    showToast('↩ Deshecho');
}

var _autosaveTimer  = null;
var _lastSavedState = null;
function markDirty(){
    setAutosaveDot('saving');
    clearTimeout(_autosaveTimer);
    _autosaveTimer = setTimeout(function(){
        var cur = JSON.stringify(collectData());
        if(cur !== _lastSavedState){
            _lastSavedState = cur;
            saveLocal(true);
            setAutosaveDot('saved');
            setTimeout(function(){ setAutosaveDot(''); },2000);
        }
    }, 1200);
}
function setAutosaveDot(state){
    var dot = document.getElementById('autosave-dot');
    dot.className = state === 'saving' ? 'saving' : (state ? 'saved' : '');
}

var _pushUndoTimer = null;
function fieldChanged(){
    clearTimeout(_pushUndoTimer);
    _pushUndoTimer = setTimeout(pushUndo, 500);
    markDirty();
    runCalcs();
    markDirtyDrive();
}
function attachChangeListeners(){
    document.querySelectorAll('.pdf-form-layer input, .pdf-form-layer textarea, .pdf-form-layer select').forEach(function(el){
        el.removeEventListener('input',  fieldChanged);
        el.removeEventListener('change', fieldChanged);
        el.addEventListener('input',  fieldChanged);
        el.addEventListener('change', fieldChanged);
    });
}

/* ════════════════════════════════════════
   NAVEGACIÓN
════════════════════════════════════════ */
function switchSheet(key){
    saveLocal(true);
    currentSheet = key;
    document.getElementById('tab-oficial').classList.toggle('active', key==='oficial');
    document.getElementById('tab-melon').classList.toggle('active', key==='melon');
    document.getElementById('tab-misfichas').classList.remove('active');
    // Reset zoom al ajuste vertical por defecto al cambiar de hoja
    userZoom = 1.0;
    var s = document.getElementById('zoom-slider'); if(s) s.value = 100;
    var v = document.getElementById('zoom-val');    if(v) v.textContent = '100%';
    document.getElementById('zoom-controls').style.display = 'flex'; // visible en hojas
    document.getElementById('sheet-tools').style.display = 'flex';
    return buildSheet(key);   // devolver promise para que callers puedan await
}
function switchToMisFichas(){
    saveLocal(true); inMisFichas = true;
    document.getElementById('tab-oficial').classList.remove('active');
    document.getElementById('tab-melon').classList.remove('active');
    document.getElementById('tab-misfichas').classList.add('active');
    document.getElementById('sheet-area').style.display = 'none';
    document.getElementById('bottom-bar').style.display = 'none';
    document.getElementById('page-tabs').innerHTML = '';
    document.getElementById('mis-fichas-panel').classList.add('visible');
    // Zoom y herramientas de hoja no aplican en Mis Fichas
    document.getElementById('zoom-controls').style.display = 'none';
    document.getElementById('sheet-tools').style.display = 'none';
    fetchAndRenderFichas();
}
function showPage(pi){
    currentPage = pi;
    document.querySelectorAll('.pg-tab').forEach(function(t){ t.classList.toggle('active', parseInt(t.dataset.pi)===pi); });
    document.querySelectorAll('.sheet-page').forEach(function(p,i){ p.classList.toggle('visible', i===pi); });
    document.getElementById('sheet-area').scrollTop = 0;
}

/* ════════════════════════════════════════
   STORAGE LOCAL (solo autosave silencioso)
════════════════════════════════════════ */
function saveLocal(silent){
    try{
        var data = collectData();
        localStorage.setItem(SHEETS[currentSheet].storageKey, JSON.stringify(data));
        _lastSavedState = JSON.stringify(data);
        if(!silent){ setStatus('✔ Guardado'); setAutosaveDot('saved'); setTimeout(function(){ setAutosaveDot(''); },2000); }
    }catch(e){ if(!silent){ setStatus('✗ Error al guardar'); showToast('✗ Error: '+e.message); } }
}
function clearAll(){
    showConfirm('¿Borrar todos los campos de la ficha actual?<br><small style="color:#666">Esta acción es reversible con Deshacer.</small>', function(){
        pushUndo();
        document.querySelectorAll('.pdf-form-layer input, .pdf-form-layer textarea, .pdf-form-layer select').forEach(function(el){
            if(el.readOnly) return; // no tocar calc fields, runCalcs los limpiará
            if(el.type === 'checkbox' || el.type === 'radio') el.checked = false;
            else el.value = '';
        });
        runCalcs();
        saveLocal(true);
        showToast('🗑 Ficha limpiada');
    });
}
window.addEventListener('beforeunload', function(){ saveLocal(true); });

/* ════════════════════════════════════════
   MIS FICHAS (listado desde Google Drive)
════════════════════════════════════════ */
var _driveFichasCache = [];

function getCurrentName(){
    var sheet = SHEETS[currentSheet];
    if(sheet.nameField){
        var el = document.querySelector('.pdf-form-layer [data-field-name="'+cssAttr(sheet.nameField)+'"]');
        if(el && el.value) return el.value;
    }
    var first = document.querySelector('.pdf-form-layer input[type="text"]');
    return (first && first.value) ? first.value : '';
}

function fetchAndRenderFichas(){
    var list = document.getElementById('mf-list');
    list.innerHTML = '<div id="mf-empty">☁ Cargando desde Drive…</div>';
    document.getElementById('mf-count').textContent = '';
    ensureDriveAuth(async function(){
        try {
            var folderId = await ensureDriveFolder();
            var q = "'" + folderId + "' in parents and trashed=false and mimeType='application/pdf'";
            var r = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                                     '&fields=files(id,name,modifiedTime,size)&orderBy=modifiedTime desc');
            var d = await r.json();
            _driveFichasCache = d.files || [];
            renderFichasList();
        } catch(e){
            list.innerHTML = '';
            var x = document.createElement('div'); x.id = 'mf-empty';
            x.innerHTML = '✗ Error de Drive: ' + esc(e.message);
            list.appendChild(x);
        }
    });
}

function renderFichasList(){
    var busq = (document.getElementById('mf-search').value||'').toLowerCase();
    var fichas = _driveFichasCache.filter(function(f){
        return !busq || f.name.toLowerCase().indexOf(busq) !== -1;
    });
    document.getElementById('mf-count').textContent = fichas.length+' ficha'+(fichas.length!==1?'s':'');
    var list = document.getElementById('mf-list'); list.innerHTML = '';
    if(!fichas.length){
        var e = document.createElement('div'); e.id = 'mf-empty';
        e.innerHTML = busq ? '🔍 Sin resultados para "'+esc(busq)+'".'
                           : '☁ No hay fichas en tu Drive todavía.<br>Pulsa <strong>☁ Guardar Drive</strong> desde una hoja.';
        list.appendChild(e); return;
    }
    fichas.forEach(function(f){
        var tipo = /_melon\.pdf$/i.test(f.name) ? 'melon'
                 : /_oficial\.pdf$/i.test(f.name)   ? 'oficial'
                 : 'desconocida';
        var nombreLimpio = f.name.replace(/_(oficial|melon)\.pdf$/i,'').replace(/\.pdf$/i,'').replace(/_/g,' ');
        var fecha = new Date(f.modifiedTime).toLocaleDateString('es-ES', {day:'2-digit',month:'2-digit',year:'numeric'});
        var tipoLbl = tipo==='oficial'?'📜 Oficial':tipo==='melon'?'🎨 Melon':'❓ Otro';
        var card = document.createElement('div'); card.className = 'ficha-card';
        card.innerHTML =
            '<button class="fc-del" title="Eliminar de Drive">✕</button>'+
            '<div class="fc-tipo">'+tipoLbl+'</div>'+
            '<div class="fc-nombre">'+esc(nombreLimpio)+'</div>'+
            '<div class="fc-fecha">📅 '+esc(fecha)+'</div>'+
            '<div class="fc-btns"><button data-a="cargar">📂 Cargar</button></div>';
        card.querySelector('.fc-del').onclick = function(e){
            e.stopPropagation();
            showConfirm('¿Eliminar <strong>'+esc(nombreLimpio)+'</strong> de Drive?', function(){
                deleteDriveFile(f);
            });
        };
        card.querySelector('[data-a="cargar"]').onclick = function(e){
            e.stopPropagation();
            loadDriveFile(f);
        };
        list.appendChild(card);
    });
}

async function deleteDriveFile(f){
    try {
        await driveFetch('https://www.googleapis.com/drive/v3/files/' + f.id, { method: 'DELETE' });
        _driveFichasCache = _driveFichasCache.filter(function(x){ return x.id !== f.id; });
        renderFichasList();
        showToast('Eliminada de Drive');
    } catch(e){ showToast('✗ '+e.message); }
}

function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function cssAttr(s){ return String(s).replace(/"/g,'\\"'); }

/* ────── Importar PDF local ────── */
var _pendingImportBytes = null;
var _pendingImportName  = '';

function triggerImport(){
    document.getElementById('import-file').click();
}
function onImportFile(ev){
    var file = ev.target.files && ev.target.files[0];
    ev.target.value = '';
    if(!file) return;
    var rd = new FileReader();
    rd.onload = function(){
        _pendingImportBytes = rd.result;
        _pendingImportName  = file.name;
        document.getElementById('import-file-name').textContent = file.name;
        document.getElementById('modal-import').classList.add('show');
    };
    rd.onerror = function(){ showToast('✗ No se pudo leer el archivo'); };
    rd.readAsArrayBuffer(file);
}
function closeImportModal(){
    document.getElementById('modal-import').classList.remove('show');
    _pendingImportBytes = null;
    _pendingImportName  = '';
}
async function doImport(sheetKey){
    if(!_pendingImportBytes){ closeImportModal(); return; }
    var bytes = _pendingImportBytes;
    var fname = _pendingImportName;
    _pendingImportBytes = null; _pendingImportName = '';
    document.getElementById('modal-import').classList.remove('show');
    showProg(true); setProgress(20, 'Leyendo AcroForm…');
    try {
        if(!window.PDFLib) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
        var doc = await window.PDFLib.PDFDocument.load(bytes);
        var form = doc.getForm();
        var data = {};
        form.getFields().forEach(function(f){
            var name = f.getName();
            try {
                if(typeof f.isChecked === 'function') data[name] = f.isChecked();
                else if(typeof f.getText === 'function') data[name] = f.getText() || '';
                else if(typeof f.getSelected === 'function'){
                    var s = f.getSelected();
                    data[name] = Array.isArray(s) ? (s[0]||'') : (s||'');
                }
            } catch(e){}
        });
        try { localStorage.setItem(SHEETS[sheetKey].storageKey, JSON.stringify(data)); } catch(e){}
        setProgress(70, 'Renderizando hoja…');
        await switchSheet(sheetKey);
        applyData(data);
        runCalcs();
        showProg(false);
        showToast('⬆ Importado: '+fname);
        // Forzar autosave a Drive (sin esperar al debounce) si conectado
        if(_driveToken && _driveToken.expires_at > Date.now()) markDirtyDrive();
    } catch(e){
        showProg(false);
        showToast('✗ Error: '+e.message);
        console.error(e);
    }
}

document.getElementById('modal-import').addEventListener('click', function(e){
    if(e.target === this) closeImportModal();
});

/* ════════════════════════════════════════
   EXPORT PDF (rellena el AcroForm con pdf-lib)
════════════════════════════════════════ */
async function exportPDF(){
    saveLocal(true);
    var sheet = SHEETS[currentSheet];
    var stored = loadStored(sheet.storageKey) || {};
    var nombre = (sheet.nameField && stored[sheet.nameField]) || getCurrentName() || 'personaje';
    await exportPDFFromData(currentSheet, stored, nombre);
}
async function exportPDFFromData(sheetKey, stored, nombre){
    setProgress(0, 'Cargando pdf-lib…'); showProg(true);
    try{
        if(!window.PDFLib){
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
        }
        var PDFDocument = window.PDFLib.PDFDocument;
        var sheet = SHEETS[sheetKey];

        setProgress(20, 'Leyendo PDF…');
        var pdfBytes = await fetch(sheet.pdfUrl).then(function(r){ return r.arrayBuffer(); });
        var doc = await PDFDocument.load(pdfBytes);
        var form = doc.getForm();

        setProgress(50, 'Rellenando campos…');
        var fields = form.getFields();
        var byName = {}; fields.forEach(function(f){ byName[f.getName()] = f; });

        Object.keys(stored).forEach(function(name){
            var fld = byName[name]; if(!fld) return;
            var val = stored[name];
            try {
                if(typeof fld.isChecked === 'function'){
                    if(val) fld.check(); else fld.uncheck();
                } else if(typeof fld.setText === 'function'){
                    fld.setText(val == null ? '' : String(val));
                } else if(typeof fld.select === 'function'){
                    if(val) fld.select(String(val));
                }
            } catch(e){ /* ignorar campo problemático */ }
        });

        setProgress(85, 'Guardando…');
        var bytes = await doc.save({ updateFieldAppearances: true });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([bytes], {type:'application/pdf'}));
        a.download = String(nombre||'personaje').replace(/\s+/g,'_') + '_' + sheetKey + '.pdf';
        a.click(); URL.revokeObjectURL(a.href);

        setProgress(100, '¡Listo!');
        setTimeout(function(){ showProg(false); showToast('✔ PDF descargado'); }, 600);
    } catch(e){
        showProg(false);
        showToast('✗ Error: '+e.message);
        console.error(e);
    }
}
function loadScript(src){
    return new Promise(function(res, rej){
        var s = document.createElement('script');
        s.src = src; s.onload = res; s.onerror = rej;
        document.head.appendChild(s);
    });
}
function showProg(v){ document.getElementById('progress-overlay').classList.toggle('show', v); }
function setProgress(pct, msg){
    document.getElementById('pb').style.width = pct+'%';
    document.getElementById('pb-text').textContent = msg;
}

/* ════════════════════════════════════════
   UI HELPERS
════════════════════════════════════════ */
var _statusTimer = null;
function setStatus(msg){
    var el = document.getElementById('status-msg');
    el.textContent = msg; el.classList.remove('fade');
    clearTimeout(_statusTimer);
    _statusTimer = setTimeout(function(){
        el.classList.add('fade');
        setTimeout(function(){ el.textContent = ''; el.classList.remove('fade'); }, 300);
    }, 3000);
}
var _toastTimer = null;
function showToast(msg){
    var t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(function(){ t.classList.remove('show'); }, 2000);
}
function showConfirm(html, onOk){
    document.getElementById('confirm-body').innerHTML = html;
    document.getElementById('modal-confirm').classList.add('show');
    var ok = document.getElementById('confirm-ok');
    var cancel = document.getElementById('confirm-cancel');
    function cleanup(){ document.getElementById('modal-confirm').classList.remove('show'); ok.onclick = null; cancel.onclick = null; }
    ok.onclick = function(){ cleanup(); onOk(); };
    cancel.onclick = cleanup;
}

document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        document.getElementById('modal-confirm').classList.remove('show');
    }
    if((e.ctrlKey || e.metaKey) && e.key === 'z'){ e.preventDefault(); undoLast(); }
    if((e.ctrlKey || e.metaKey) && e.key === 's'){ e.preventDefault(); saveToDrive(); }
});

/* ════════════════════════════════════════
   GOOGLE DRIVE INTEGRATION
   - OAuth con Google Identity Services (token client)
   - Sube/baja el PDF rellenado a una carpeta "D&D Fichas" en el Drive del usuario
   - El AcroForm guarda el estado dentro del propio PDF → al recargar se lee
     con pdf-lib y se repuebla la UI
════════════════════════════════════════ */
var GOOGLE_CLIENT_ID  = <?php echo json_encode(GOOGLE_CLIENT_ID); ?>;
var GDRIVE_SCOPE      = 'https://www.googleapis.com/auth/drive.file';
var GDRIVE_FOLDER     = 'D&D Fichas';
var _tokenClient      = null;
var _driveToken       = null;       // { access_token, expires_at }
var _driveFolderId    = null;
var _pendingDriveCb   = null;

var DRIVE_TOKEN_KEY     = 'dnd_drive_token';
var DRIVE_EVER_AUTH_KEY = 'dnd_drive_ever_auth';

(function tryRestoreToken(){
    try {
        var s = localStorage.getItem(DRIVE_TOKEN_KEY);
        if(!s) return;
        var t = JSON.parse(s);
        if(t && t.expires_at > Date.now() + 30000) _driveToken = t;
    } catch(e){}
})();

function _initTokenClient(){
    if(_tokenClient) return _tokenClient;
    if(typeof google === 'undefined' || !google.accounts || !google.accounts.oauth2){
        return null;
    }
    _tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: GOOGLE_CLIENT_ID,
        scope:     GDRIVE_SCOPE,
        callback: function(resp){
            if(resp.error){
                // Errores de silent refresh no se reportan al usuario
                if(!_silentRefreshInFlight) showToast('✗ Drive: '+resp.error);
                _silentRefreshInFlight = false;
                _pendingDriveCb = null;
                return;
            }
            _driveToken = {
                access_token: resp.access_token,
                expires_at:   Date.now() + (resp.expires_in * 1000) - 60000
            };
            localStorage.setItem(DRIVE_TOKEN_KEY, JSON.stringify(_driveToken));
            localStorage.setItem(DRIVE_EVER_AUTH_KEY, '1');
            updateDriveStatus();
            scheduleSilentRefresh();
            if(!_silentRefreshInFlight) showToast('☁ Drive conectado');
            _silentRefreshInFlight = false;
            if(_pendingDriveCb){ var fn = _pendingDriveCb; _pendingDriveCb = null; fn(); }
        }
    });
    return _tokenClient;
}

/* Renovación silenciosa del token antes de que caduque (1 h) */
var _silentRefreshTimer    = null;
var _silentRefreshInFlight = false;

function scheduleSilentRefresh(){
    clearTimeout(_silentRefreshTimer);
    if(!_driveToken) return;
    var ms = _driveToken.expires_at - Date.now() - 5*60*1000; // 5 min antes
    if(ms < 1000) ms = 1000;
    _silentRefreshTimer = setTimeout(silentRefresh, ms);
}

function silentRefresh(){
    var c = _initTokenClient();
    if(!c) return;
    _silentRefreshInFlight = true;
    try { c.requestAccessToken({ prompt: '' }); }
    catch(e){ _silentRefreshInFlight = false; }
}

/* Al cargar: si el usuario consintió previamente, intenta restaurar sesión
   sin UI. Si el token cacheado sigue válido, no se llama a Google. */
function tryAutoConnectDrive(){
    if(!localStorage.getItem(DRIVE_EVER_AUTH_KEY)) return;
    if(_driveToken && _driveToken.expires_at > Date.now() + 30000){
        scheduleSilentRefresh();
        updateDriveStatus();
        return;
    }
    // Token caducado o ausente → pedir uno nuevo en silencio
    var attempts = 0;
    var iv = setInterval(function(){
        attempts++;
        if(typeof google !== 'undefined' && google.accounts && google.accounts.oauth2){
            clearInterval(iv);
            silentRefresh();
        } else if(attempts > 60){   // 15 s
            clearInterval(iv);
        }
    }, 250);
}

function ensureDriveAuth(fn){
    if(_driveToken && _driveToken.expires_at > Date.now() + 30000){ fn(); return; }
    _pendingDriveCb = fn;
    var c = _initTokenClient();
    if(!c){
        // GIS aún no cargó; esperar
        var attempts = 0;
        var iv = setInterval(function(){
            attempts++;
            c = _initTokenClient();
            if(c){
                clearInterval(iv);
                c.requestAccessToken({ prompt: '' });
            } else if(attempts > 40){
                clearInterval(iv);
                showToast('✗ No se pudo cargar Google Identity Services');
                _pendingDriveCb = null;
            }
        }, 250);
        return;
    }
    c.requestAccessToken({ prompt: '' });
}

function disconnectDrive(){
    if(_driveToken && typeof google !== 'undefined' && google.accounts){
        try { google.accounts.oauth2.revoke(_driveToken.access_token, function(){}); } catch(e){}
    }
    _driveToken = null;
    clearTimeout(_silentRefreshTimer);
    localStorage.removeItem(DRIVE_TOKEN_KEY);
    localStorage.removeItem(DRIVE_EVER_AUTH_KEY);
    updateDriveStatus();
    showToast('Drive desconectado');
    if(inMisFichas){
        _driveFichasCache = [];
        renderFichasList();
    }
}

function updateDriveStatus(state){
    var s = document.getElementById('drive-status');
    if(!s) return;
    var connected = !!(_driveToken && _driveToken.expires_at > Date.now());
    if(!connected){ s.textContent = ''; return; }
    if(state === 'saving')      s.textContent = '☁ subiendo…';
    else if(state === 'saved')  s.textContent = '☁ guardado';
    else if(state === 'error')  s.textContent = '☁ ✗ error';
    else                        s.textContent = '● conectado';
}

/* ────── Autosave a Drive con debounce ────── */
var _driveAutoTimer = null;
var _driveSaving    = false;
var DRIVE_AUTOSAVE_MS = 6000;     // 6 s tras la última edición

function markDirtyDrive(){
    if(!_driveToken || _driveToken.expires_at < Date.now()) return; // no conectado
    clearTimeout(_driveAutoTimer);
    _driveAutoTimer = setTimeout(autoSaveToDrive, DRIVE_AUTOSAVE_MS);
}

async function autoSaveToDrive(){
    if(_driveSaving) {                       // re-encolar si hay otro en vuelo
        clearTimeout(_driveAutoTimer);
        _driveAutoTimer = setTimeout(autoSaveToDrive, 2000);
        return;
    }
    if(!_driveToken || _driveToken.expires_at < Date.now()) return;
    _driveSaving = true;
    updateDriveStatus('saving');
    try {
        var sheet = SHEETS[currentSheet];
        var folderId = await ensureDriveFolder();
        saveLocal(true);
        var stored = collectData();
        var bytes = await buildFilledPdfBytes(currentSheet, stored);
        var rawName = (sheet.nameField && stored[sheet.nameField]) || getCurrentName() || 'personaje';
        var fileName = String(rawName).replace(/[\\/:*?"<>|]/g,'_').replace(/\s+/g,'_') + '_' + currentSheet + '.pdf';

        var q = "name='" + fileName.replace(/'/g,"\\'") + "' and '" + folderId + "' in parents and trashed=false";
        var fr = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                                  '&fields=files(id)&spaces=drive');
        var fd = await fr.json();
        var existing = fd.files && fd.files[0] && fd.files[0].id;

        if(existing){
            await driveFetch('https://www.googleapis.com/upload/drive/v3/files/' + existing + '?uploadType=media', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/pdf' },
                body: bytes
            });
        } else {
            var boundary = '-------dnd_' + Math.random().toString(36).slice(2);
            var delim = '\r\n--' + boundary + '\r\n';
            var close = '\r\n--' + boundary + '--';
            var metadata = { name: fileName, parents: [folderId], mimeType: 'application/pdf' };
            var head = delim + 'Content-Type: application/json; charset=UTF-8\r\n\r\n' + JSON.stringify(metadata) +
                       delim + 'Content-Type: application/pdf\r\n\r\n';
            var headBytes = new TextEncoder().encode(head);
            var tailBytes = new TextEncoder().encode(close);
            var body = new Uint8Array(headBytes.length + bytes.byteLength + tailBytes.length);
            body.set(headBytes, 0);
            body.set(new Uint8Array(bytes), headBytes.length);
            body.set(tailBytes, headBytes.length + bytes.byteLength);
            await driveFetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', {
                method: 'POST',
                headers: { 'Content-Type': 'multipart/related; boundary=' + boundary },
                body: body
            });
        }
        updateDriveStatus('saved');
        setTimeout(function(){ updateDriveStatus(); }, 2000);
    } catch(e){
        console.error('Drive autosave fallido:', e);
        updateDriveStatus('error');
        setTimeout(function(){ updateDriveStatus(); }, 3000);
    } finally {
        _driveSaving = false;
    }
}

/* Wrapper de fetch que añade el Authorization. Reintenta una vez si 401. */
async function driveFetch(url, opts, retried){
    opts = opts || {};
    opts.headers = Object.assign({}, opts.headers || {}, {
        Authorization: 'Bearer ' + _driveToken.access_token
    });
    var r = await fetch(url, opts);
    if(r.status === 401 && !retried){
        // Token rechazado: limpiar y pedir uno nuevo (silencioso si hay consentimiento previo)
        _driveToken = null;
        localStorage.removeItem(DRIVE_TOKEN_KEY);
        return new Promise(function(resolve, reject){
            ensureDriveAuth(function(){
                driveFetch(url, opts, true).then(resolve, reject);
            });
        });
    }
    if(!r.ok){
        var t = ''; try { t = await r.text(); } catch(e){}
        throw new Error('Drive '+r.status+': '+t.slice(0,160));
    }
    return r;
}

async function ensureDriveFolder(){
    if(_driveFolderId) return _driveFolderId;
    var q = "mimeType='application/vnd.google-apps.folder' and name='" + GDRIVE_FOLDER.replace(/'/g,"\\'") + "' and trashed=false";
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                             '&fields=files(id,name)&spaces=drive');
    var d = await r.json();
    if(d.files && d.files.length){
        _driveFolderId = d.files[0].id;
        return _driveFolderId;
    }
    var cr = await driveFetch('https://www.googleapis.com/drive/v3/files', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: GDRIVE_FOLDER, mimeType: 'application/vnd.google-apps.folder' })
    });
    var c = await cr.json();
    _driveFolderId = c.id;
    return _driveFolderId;
}

/* Genera bytes del PDF rellenado (misma lógica que exportPDFFromData pero devolviendo bytes) */
async function buildFilledPdfBytes(sheetKey, stored){
    if(!window.PDFLib) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
    var PDFDocument = window.PDFLib.PDFDocument;
    var sheet = SHEETS[sheetKey];
    var pdfBytes = await fetch(sheet.pdfUrl).then(function(r){ return r.arrayBuffer(); });
    var doc = await PDFDocument.load(pdfBytes);
    var form = doc.getForm();
    var byName = {}; form.getFields().forEach(function(f){ byName[f.getName()] = f; });
    Object.keys(stored).forEach(function(name){
        var fld = byName[name]; if(!fld) return;
        var v = stored[name];
        try {
            if(typeof fld.isChecked === 'function'){
                if(v) fld.check(); else fld.uncheck();
            } else if(typeof fld.setText === 'function'){
                fld.setText(v == null ? '' : String(v));
            } else if(typeof fld.select === 'function'){
                if(v) fld.select(String(v));
            }
        } catch(e){}
    });
    return doc.save({ updateFieldAppearances: true });
}

async function saveToDrive(){
    ensureDriveAuth(async function(){
        showProg(true); setProgress(10, 'Carpeta Drive…');
        try {
            var folderId = await ensureDriveFolder();
            saveLocal(true);
            setProgress(30, 'Rellenando PDF…');
            var stored = collectData();
            var sheet = SHEETS[currentSheet];
            var bytes = await buildFilledPdfBytes(currentSheet, stored);
            var rawName = (sheet.nameField && stored[sheet.nameField]) || getCurrentName() || 'personaje';
            var fileName = String(rawName).replace(/[\\/:*?"<>|]/g,'_').replace(/\s+/g,'_') + '_' + currentSheet + '.pdf';
            setProgress(60, 'Subiendo a Drive…');

            // ¿Ya existe? → update; si no → create multipart
            var q = "name='" + fileName.replace(/'/g,"\\'") + "' and '" + folderId + "' in parents and trashed=false";
            var fr = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                                      '&fields=files(id)&spaces=drive');
            var fd = await fr.json();
            var existing = fd.files && fd.files[0] && fd.files[0].id;

            if(existing){
                await driveFetch('https://www.googleapis.com/upload/drive/v3/files/' + existing + '?uploadType=media', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/pdf' },
                    body: bytes
                });
            } else {
                var boundary = '-------dnd_' + Math.random().toString(36).slice(2);
                var delim = '\r\n--' + boundary + '\r\n';
                var close = '\r\n--' + boundary + '--';
                var metadata = { name: fileName, parents: [folderId], mimeType: 'application/pdf' };
                var head = delim + 'Content-Type: application/json; charset=UTF-8\r\n\r\n' + JSON.stringify(metadata) +
                           delim + 'Content-Type: application/pdf\r\n\r\n';
                var tail = close;
                var headBytes = new TextEncoder().encode(head);
                var tailBytes = new TextEncoder().encode(tail);
                var body = new Uint8Array(headBytes.length + bytes.byteLength + tailBytes.length);
                body.set(headBytes, 0);
                body.set(new Uint8Array(bytes), headBytes.length);
                body.set(tailBytes, headBytes.length + bytes.byteLength);
                await driveFetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', {
                    method: 'POST',
                    headers: { 'Content-Type': 'multipart/related; boundary=' + boundary },
                    body: body
                });
            }

            setProgress(100, 'Listo'); setTimeout(function(){ showProg(false); }, 500);
            showToast('☁ Guardado: ' + fileName);
        } catch(e){
            showProg(false); showToast('✗ Drive: ' + e.message); console.error(e);
        }
    });
}

async function loadDriveFile(file){
    showProg(true); setProgress(20, 'Descargando '+file.name+'…');
    try {
        var r = await driveFetch('https://www.googleapis.com/drive/v3/files/' + file.id + '?alt=media');
        var bytes = await r.arrayBuffer();
        setProgress(60, 'Leyendo AcroForm…');
        if(!window.PDFLib) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
        var doc = await window.PDFLib.PDFDocument.load(bytes);
        var form = doc.getForm();
        var data = {};
        form.getFields().forEach(function(f){
            var name = f.getName();
            try {
                if(typeof f.isChecked === 'function'){
                    data[name] = f.isChecked();
                } else if(typeof f.getText === 'function'){
                    data[name] = f.getText() || '';
                } else if(typeof f.getSelected === 'function'){
                    var s = f.getSelected();
                    data[name] = Array.isArray(s) ? (s[0]||'') : (s||'');
                }
            } catch(e){}
        });
        var nonEmpty = Object.keys(data).filter(function(k){
            var v = data[k]; return v !== '' && v !== false && v != null;
        }).length;
        console.log('[Drive load] '+Object.keys(data).length+' campos leídos, '+nonEmpty+' con valor');

        // Detectar a qué hoja pertenece: sufijo en el nombre
        var targetSheet = currentSheet;
        if(/_melon\.pdf$/i.test(file.name)) targetSheet = 'melon';
        else if(/_oficial\.pdf$/i.test(file.name)) targetSheet = 'oficial';

        // Persistimos los datos y AWAIT al render antes de aplicar
        try { localStorage.setItem(SHEETS[targetSheet].storageKey, JSON.stringify(data)); } catch(e){}
        setProgress(80, 'Renderizando hoja…');
        await switchSheet(targetSheet);   // ahora devuelve la promise de buildSheet
        applyData(data);
        runCalcs();
        showProg(false);
        showToast('☁ Cargada: '+file.name);
    } catch(e){
        showProg(false); showToast('✗ Drive: '+e.message); console.error(e);
    }
}

/* Status inicial + intento de reconexión silenciosa */
updateDriveStatus();
tryAutoConnectDrive();

/* ════════════════════════════════════════
   LANZADOR DE DADOS — modelos 3D reales (Three.js)
   Cada dado usa su poliedro: d4 tetraedro, d6 cubo, d8 octaedro,
   d10/d100 trapezoedro pentagonal, d12 dodecaedro, d20 icosaedro.
   Gira con velocidad angular aleatoria y desacelera hasta parar.
════════════════════════════════════════ */
var _diceRolling = false;
var _diceScene = null;   // { renderer, scene, camera, mesh, raf, ... }

/* Color del tema → THREE.Color (hex de las CSS vars) */
function _themeHex(varName, fallback){
    var v = getComputedStyle(document.body).getPropertyValue(varName).trim();
    return v || fallback;
}

/* Geometría del trapezoedro pentagonal (d10) — no viene en Three.js */
function _makeD10Geometry(THREE){
    var n = 5, top = 1.0, bot = -1.0, ry = 0.30, lo = Math.PI / n;
    var v = [];
    v.push(0, top, 0);            // 0 ápice superior
    v.push(0, bot, 0);            // 1 ápice inferior
    for(var i = 0; i < n; i++){   // 2..6 anillo superior
        var a = (i / n) * Math.PI * 2;
        v.push(Math.cos(a), ry, Math.sin(a));
    }
    for(var j = 0; j < n; j++){   // 7..11 anillo inferior (desfasado)
        var b = (j / n) * Math.PI * 2 + lo;
        v.push(Math.cos(b), -ry, Math.sin(b));
    }
    var U = function(i){ return 2 + (i % n); };
    var L = function(i){ return 7 + (i % n); };
    var idx = [];
    for(var k = 0; k < n; k++){
        // cometa superior: ápice, U[k], L[k], U[k+1]
        idx.push(0, U(k), L(k));   idx.push(0, L(k), U(k+1));
        // cometa inferior: ápice inf, L[k], U[k+1], L[k+1]
        idx.push(1, L(k), U(k+1)); idx.push(1, U(k+1), L(k+1));
    }
    var g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
    g.setIndex(idx);
    g.computeVertexNormals();
    g.scale(0.95, 1.15, 0.95);
    return g;
}

function _makeGeometry(THREE, sides){
    switch(sides){
        case 4:   return new THREE.TetrahedronGeometry(1.25);
        case 6:   return new THREE.BoxGeometry(1.5, 1.5, 1.5);
        case 8:   return new THREE.OctahedronGeometry(1.3);
        case 10:  return _makeD10Geometry(THREE);
        case 12:  return new THREE.DodecahedronGeometry(1.2);
        case 20:  return new THREE.IcosahedronGeometry(1.25);
        case 100: return _makeD10Geometry(THREE);
        default:  return new THREE.BoxGeometry(1.5, 1.5, 1.5);
    }
}

/* Pool de dados en pantalla: cada entrada {sides, value, mesh, el, rolling, vx,vy,vz, t0} */
var dicePool = [];
var diceModifier = 0;   /* modificador fijo que se suma al total */

function _initDiceScene(){
    if(_diceScene) return _diceScene;
    var canvas = document.getElementById('dice-canvas');
    var w = canvas.clientWidth || 236, h = canvas.clientHeight || 146;

    var renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(window.devicePixelRatio || 1);
    renderer.setSize(w, h, false);

    var scene  = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(40, w / h, 0.1, 100);
    camera.position.set(0, 0, 6);

    scene.add(new THREE.AmbientLight(0xffffff, 0.65));
    var key = new THREE.DirectionalLight(0xffffff, 0.9);
    key.position.set(3, 5, 4); scene.add(key);
    var rim = new THREE.DirectionalLight(0xffffff, 0.35);
    rim.position.set(-4, -2, -3); scene.add(rim);

    _diceScene = { renderer: renderer, scene: scene, camera: camera, raf: 0, w: w, h: h,
                   raycaster: new THREE.Raycaster(), running: false };

    /* Clic en un dado → quitarlo */
    canvas.addEventListener('click', function(e){
        if(typeof THREE === 'undefined' || !_diceScene) return;
        var rect = canvas.getBoundingClientRect();
        var mx = ((e.clientX - rect.left) / rect.width)  * 2 - 1;
        var my = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        _diceScene.raycaster.setFromCamera({ x: mx, y: my }, camera);
        var meshes = dicePool.map(function(d){ return d.mesh; });
        var hits = _diceScene.raycaster.intersectObjects(meshes, true);
        if(!hits.length) return;
        /* subir al mesh raíz del pool */
        var obj = hits[0].object;
        while(obj && meshes.indexOf(obj) === -1) obj = obj.parent;
        if(!obj) return;
        var i = meshes.indexOf(obj);
        if(i !== -1) removeDieAt(i);
    });
    return _diceScene;
}

function _makeDieMesh(sides){
    var accent = _themeHex('--accent', '#EDC001');
    var deep   = _themeHex('--accent-deep', '#c8a000');
    var geo = _makeGeometry(THREE, sides);
    var mat = new THREE.MeshStandardMaterial({
        color: new THREE.Color(accent), metalness: 0.25, roughness: 0.45, flatShading: true
    });
    var mesh = new THREE.Mesh(geo, mat);
    mesh.add(new THREE.LineSegments(
        new THREE.EdgesGeometry(geo, 1),
        new THREE.LineBasicMaterial({ color: new THREE.Color(deep) })
    ));
    return mesh;
}

/* Recolorea todos los dados del pool con el tema activo actual (para que
   reflejen el tema del usuario incluso si cambió tras crearlos). */
function _recolorDice(){
    if(!_diceScene || typeof THREE === 'undefined') return;
    var accent = _themeHex('--accent', '#EDC001');
    var deep   = _themeHex('--accent-deep', '#c8a000');
    dicePool.forEach(function(d){
        if(d.mesh && d.mesh.material) d.mesh.material.color.set(accent);
        if(d.mesh) d.mesh.children.forEach(function(ch){
            if(ch.material && ch.material.color) ch.material.color.set(deep);
        });
    });
    _renderDice();
}

/* Coloca los dados en una rejilla centrada y ajusta la cámara para que
   TODOS quepan siempre en pantalla (filas/columnas según cantidad). */
function layoutDice(){
    var S = _diceScene, n = dicePool.length;
    if(!S || !n) return;
    var aspect = S.w / S.h;
    /* nº de columnas proporcional al aspecto (más anchas que altas) */
    var cols = Math.max(1, Math.min(n, Math.round(Math.sqrt(n * aspect))));
    var rows = Math.ceil(n / cols);
    var sp = 2.6;                                   /* separación entre centros */
    var spanW = (cols - 1) * sp, spanH = (rows - 1) * sp;

    /* escala de cada dado: más pequeña cuanto más grande la rejilla */
    var scale = Math.max(0.42, Math.min(0.9, 2.6 / Math.max(cols, rows)));

    dicePool.forEach(function(d, i){
        var r = Math.floor(i / cols), c = i % cols;
        var inRow = (r === rows - 1) ? (n - cols * (rows - 1)) : cols;  /* dados en ESTA fila */
        var rowSpanW = (inRow - 1) * sp;
        d.mesh.position.x = -rowSpanW / 2 + c * sp;
        d.mesh.position.y =  spanH / 2 - r * sp;
        d.mesh.scale.setScalar(scale);
    });

    /* cámara: alejar hasta que entren ancho y alto (con margen) */
    var fov = 40 * Math.PI / 180, half = Math.tan(fov / 2);
    var needW = spanW * 1 + 2.4, needH = spanH * 1 + 2.4;
    var zW = needW / (2 * half * aspect);
    var zH = needH / (2 * half);
    S.camera.position.z = Math.max(6, zW, zH);
}

/* Proyecta la posición 3D de cada dado a px y coloca su número */
function _positionOverlays(){
    var S = _diceScene; if(!S) return;
    var cw = S.renderer.domElement.clientWidth, ch = S.renderer.domElement.clientHeight;
    dicePool.forEach(function(d){
        var v = d.mesh.position.clone().project(S.camera);
        d.el.style.left = ((v.x * 0.5 + 0.5) * cw) + 'px';
        d.el.style.top  = ((-v.y * 0.5 + 0.5) * ch) + 'px';
    });
}

function _renderDice(){ if(_diceScene) _diceScene.renderer.render(_diceScene.scene, _diceScene.camera); }

/* Bucle único: anima los dados marcados como rolling y va parando */
function _animateLoop(){
    var S = _diceScene; if(!S) return;
    var now = performance.now(), anyRolling = false;
    dicePool.forEach(function(d){
        if(!d.rolling) return;
        var t = Math.min(1, (now - d.t0) / d.dur);
        var decay = 1 - t * t;
        d.mesh.rotation.x += d.vx * decay;
        d.mesh.rotation.y += d.vy * decay;
        d.mesh.rotation.z += d.vz * decay;
        d._scr = (d._scr || 0) + 16;
        if(t < 0.75 && d._scr > 60){ d._scr = 0; d.el.textContent = Math.floor(Math.random() * d.sides) + 1; }
        if(t >= 1){
            d.rolling = false;
            d.el.textContent = d.value;
            d.el.animate(
                [{ transform:'translate(-50%,-50%) scale(1.6)', opacity:.4 },
                 { transform:'translate(-50%,-50%) scale(1)',   opacity:1 }],
                { duration: 240, easing:'cubic-bezier(.2,1.4,.4,1)' });
            updateDiceTotal();
        } else { anyRolling = true; }
    });
    _renderDice();
    _positionOverlays();
    if(anyRolling){ S.raf = requestAnimationFrame(_animateLoop); }
    else { S.running = false; }
}
function _ensureLoop(){
    if(_diceScene && !_diceScene.running){ _diceScene.running = true; _diceScene.raf = requestAnimationFrame(_animateLoop); }
}

/* Lanza (o relanza) un dado del pool */
function _spinDie(d){
    d.value = Math.floor(Math.random() * d.sides) + 1;
    d.vx = (0.22 + Math.random() * 0.22) * (Math.random() < .5 ? -1 : 1);
    d.vy =  0.28 + Math.random() * 0.26;
    d.vz = (0.10 + Math.random() * 0.14) * (Math.random() < .5 ? -1 : 1);
    d.dur = 1300 + Math.random() * 400;
    d.t0 = performance.now();
    d._scr = 0;
    d.rolling = true;
}

function updateDiceTotal(){
    var resEl = document.getElementById('dice-result');
    if(!dicePool.length && !diceModifier){ resEl.textContent = 'Elige un dado'; return; }
    var anyRolling = dicePool.some(function(d){ return d.rolling; });
    if(anyRolling){ resEl.textContent = 'Tirando…'; return; }
    var total = diceModifier;
    dicePool.forEach(function(d){ total += d.value; });
    var extra = '';
    if(dicePool.length === 1 && !diceModifier && dicePool[0].sides === 20){
        if(dicePool[0].value === 20) extra = ' <span class="dice-crit">¡CRÍTICO!</span>';
        else if(dicePool[0].value === 1) extra = ' <span class="dice-fail">PIFIA</span>';
    }
    var modTxt = diceModifier ? ' <span style="color:var(--text-muted)">(' + (diceModifier > 0 ? '+' : '') + diceModifier + ')</span>' : '';
    resEl.innerHTML = 'Total <strong>' + total + '</strong>' + modTxt + extra;
}

/* ── Modificador fijo ── */
function openModifierDialog(){
    var inp = document.getElementById('dice-mod-input');
    inp.value = diceModifier;
    document.getElementById('dice-mod-modal').style.display = 'flex';
    setTimeout(function(){ inp.focus(); inp.select(); }, 30);
}
function closeModifierDialog(){
    document.getElementById('dice-mod-modal').style.display = 'none';
}
function applyModifier(){
    var v = parseInt(document.getElementById('dice-mod-input').value, 10);
    diceModifier = isNaN(v) ? 0 : v;
    closeModifierDialog();
    updateDiceTotal();
}

/* Añadir un dado al pool y tirarlo */
function addDie(sides){
    if(typeof THREE === 'undefined' || !_diceScene) return;
    var mesh = _makeDieMesh(sides);
    _diceScene.scene.add(mesh);
    var el = document.createElement('div');
    el.className = 'dice-val';
    document.getElementById('dice-overlays').appendChild(el);
    var d = { sides: sides, value: 1, mesh: mesh, el: el };
    dicePool.push(d);
    layoutDice();
    _spinDie(d);
    updateDiceTotal();
    _ensureLoop();
}

/* Relanzar TODOS los dados en pantalla */
function rerollAll(){
    if(typeof THREE === 'undefined' || !_diceScene || !dicePool.length) return;
    _recolorDice();           /* por si el tema cambió desde la última tirada */
    dicePool.forEach(_spinDie);
    updateDiceTotal();
    _ensureLoop();
}

/* Recolorear los dados en vivo cuando el tema del usuario cambia (el padre
   actualiza la clase de tema en el <body> de esta iframe). */
(function(){
    var deb = null;
    new MutationObserver(function(){
        clearTimeout(deb);
        deb = setTimeout(function(){ _recolorDice(); }, 120);
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
})();

/* Quitar un dado (clic sobre él) */
function removeDieAt(i){
    var d = dicePool[i]; if(!d) return;
    _diceScene.scene.remove(d.mesh);
    if(d.mesh.geometry) d.mesh.geometry.dispose();
    if(d.el && d.el.parentNode) d.el.parentNode.removeChild(d.el);
    dicePool.splice(i, 1);
    layoutDice();
    _renderDice();
    _positionOverlays();
    updateDiceTotal();
}

function clearDice(){
    while(dicePool.length) removeDieAt(0);
}

function toggleDicePanel(force){
    var p = document.getElementById('dice-panel');
    var show = (typeof force === 'boolean') ? force : (p.style.display === 'none' || !p.style.display);
    p.style.display = show ? 'block' : 'none';
    document.getElementById('btn-dados').classList.toggle('active', show);
    if(show && !p.dataset.init){
        p.dataset.init = '1';
        loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js').then(function(){
            _initDiceScene();
            _renderDice();
        }).catch(function(){
            document.getElementById('dice-result').textContent = '✗ No se pudo cargar el motor 3D';
        });
    }
}

/* Arrastrar la ventana de dados por su barra de título */
(function(){
    var panel = document.getElementById('dice-panel');
    var bar   = document.getElementById('dice-titlebar');
    if(!panel || !bar) return;
    var dragging = false, ox = 0, oy = 0;
    bar.addEventListener('mousedown', function(e){
        if(e.target.id === 'dice-close') return;
        dragging = true;
        var r = panel.getBoundingClientRect();
        ox = e.clientX - r.left; oy = e.clientY - r.top;
        panel.style.right = 'auto';
        panel.style.left = r.left + 'px';
        panel.style.top  = r.top + 'px';
        e.preventDefault();
    });
    document.addEventListener('mousemove', function(e){
        if(!dragging) return;
        var nx = Math.max(0, Math.min(window.innerWidth  - 60, e.clientX - ox));
        var ny = Math.max(0, Math.min(window.innerHeight - 30, e.clientY - oy));
        panel.style.left = nx + 'px';
        panel.style.top  = ny + 'px';
    });
    document.addEventListener('mouseup', function(){ dragging = false; });
})();

/* ── INIT ── */
window.addEventListener('load', function(){ buildSheet('oficial'); });
</script>
</body>
</html>
