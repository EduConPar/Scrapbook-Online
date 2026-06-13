<?php
session_start();
require_once dirname(__DIR__) . '/assets/config.php';
require_once dirname(__DIR__) . '/assets/php/active-interface.php';
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../index.php');
    exit;
}
$userLabel = $loginUsers[$userKey]['label'];
$activeIface = getActiveInterface();
$bodyIfaceClass = 'iface-' . preg_replace('/[^a-z0-9_-]/', '', strtolower($activeIface));

/* Tema personalizado activo del usuario — regenera el CSS en disco si
   fuera necesario y carga el bloque para emitir el <link> + class del
   body. Mismo patrón que apps/temas.php. */
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes      = loadUserThemes($userKey);
$activeTheme      = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = '../' . themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(dirname(__DIR__) . '/' . themeCssRelPath($activeTheme, $userLabel))) {
        $activeThemeCss = '';
    }
}
$bodyClass = trim($bodyIfaceClass . ' ' . $activeThemeClass);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dibujo Colaborativo</title>
<link rel="stylesheet" href="../assets/css/98.css">
<link rel="stylesheet" href="../assets/css/tokens.css">
<link rel="stylesheet" href="../assets/css/base.css">
<?php emitInterfaceCss('../'); ?>
<link rel="stylesheet" href="../assets/css/themes.css">
<?php if ($activeThemeCss): ?>
<link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<!-- Google Identity Services para auth de Drive cuando guardas a Galería -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<!-- Icon-pack swap: reescribe los <img src="appIcons/X.png"> con el
     pack del usuario (Melon / Claro / Oscuro / etc). Debe ir antes del
     primer render para minimizar el flash de 404. -->
<script src="../assets/js/icon-pack.js"></script>
<style>
/* ────────────────────────────────────────────────────────────────────
   DIBUJO — colores 100% via tokens. La interfaz activa (win98/kawaii)
   redefine las variables a nivel :root, así que esta app hereda el
   look correcto sin sniff manual.
   ──────────────────────────────────────────────────────────────────── */
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{
    font:11px var(--font,'MS Sans Serif','Segoe UI',Tahoma,sans-serif);
    background:var(--win-bg);
    color:var(--text);
    display:flex;flex-direction:column;
    user-select:none;
}

/* ────────── Barras (menu / tool) ────────── */
#menubar,#toolbar{
    background:var(--win-bg);
    display:flex;align-items:center;gap:4px;padding:2px 4px;flex-shrink:0;
}
#menubar{border-bottom:1px solid var(--border)}
#toolbar{
    border-bottom:2px solid;
    border-color:var(--border) var(--bezel-light-1) var(--bezel-light-1) var(--border);
    flex-wrap:wrap;
}
.app-title{font-weight:bold;margin-right:8px;color:var(--text)}

/* ────────── Override 98.css para botones toolbar/menú ────────── */
/* 98.css aplica min-width:75px;min-height:23px;padding:0 12px a TODO
   <button>. Para los iconos cuadrados del toolbar reducimos a 28x28.
   Mantenemos los bezels que 98.css ya pinta — solo neutralizamos size,
   padding y el text-shadow transparente que 98.css usa para botones de
   formulario. */
.tb-btn,.menu-btn{
    min-width:0;min-height:0;
    padding:0 6px;
    color:var(--text);
    text-shadow:none;
    font:inherit;font-size:11px;
}
.tb-btn{
    width:28px;height:28px;padding:0;
    display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:bold;
}
.tb-btn.active{
    /* Estado activo — mismo look que :active de 98.css (bezel hundido) */
    box-shadow:
        inset -1px -1px var(--bezel-light-1),
        inset 1px 1px var(--bezel-dark-1),
        inset -2px -2px var(--bezel-light-2),
        inset 2px 2px var(--bezel-dark-2);
}
.menu-btn{
    background:transparent;
    box-shadow:none;
    min-height:20px;
}
.menu-btn:hover,.menu-btn.open{
    /* Botón de menú resaltado — usa el acento del tema (navy/pink/...) */
    background:var(--accent);
    color:var(--accent-text);
}
.tb-sep{
    width:1px;height:22px;background:var(--border);
    box-shadow:1px 0 0 var(--bezel-light-1);
    margin:0 3px;
}
.tb-label{font-size:11px;margin:0 4px 0 8px;color:var(--text)}
/* 98.css aplica width:100% a input[type=range] (especificidad 0,1,1).
   El selector compuesto sube a 0,2,1 para fijar 84px y evitar que el
   slider rellene toda la fila flex del toolbar. */
input[type=range].tb-slider{
    width:84px;
    flex:0 0 84px;
}
.tb-select{
    font:inherit;font-size:11px;height:22px;
    padding:0 24px 0 4px;cursor:pointer;
    min-width:0;
}

/* ────────── Tab bar — fila debajo del toolbar con un tab por lienzo
   abierto. Cada tab muestra el nombre + asterisco si está sucio + una
   X para cerrar. El tab activo lleva la clase .tab.active con bezel
   "subido" mientras los inactivos quedan "hundidos". ────────── */
#tab-bar{
    display:flex;align-items:flex-end;gap:1px;
    flex-shrink:0;padding:2px 4px 0;
    background:var(--win-bg);
    border-bottom:1px solid var(--border);
    overflow-x:auto;
    min-height:24px;
}
.tab{
    display:flex;align-items:center;gap:4px;
    padding:2px 4px 2px 8px;
    background:var(--win-bg);color:var(--text);
    font-size:11px;
    cursor:pointer;
    max-width:200px;
    box-shadow:
        inset 1px 1px var(--bezel-light-1),
        inset -1px -1px var(--bezel-dark-1),
        inset 2px 2px var(--bezel-light-2),
        inset -2px -2px var(--bezel-dark-2);
    border-bottom:none;
}
.tab.active{
    background:var(--win-body-bg,var(--win-bg));
    font-weight:bold;
    position:relative;
    z-index:2;
    /* "Saltada" sobre los otros — solapa con la línea del border-bottom
       del tab-bar para visualmente unirse al workspace. */
    margin-bottom:-1px;padding-bottom:3px;
}
.tab-name{
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    max-width:170px;
}
.tab-dirty{color:var(--accent);margin-left:1px}
.tab-close{
    min-width:0;min-height:0;
    width:16px;height:16px;
    padding:0;margin-left:2px;
    font-size:10px;line-height:1;
    flex-shrink:0;
    color:var(--text);
}
.tab-new{
    min-width:0;min-height:0;
    width:22px;height:20px;
    padding:0;margin-left:4px;
    font-size:14px;line-height:1;
    flex-shrink:0;
    align-self:flex-end;
}

/* ────────── Statusbar (98.css .status-bar) ────────── */
/* Color sólido fijo del tema en el footer — sin gradiente ni
   transparencia, así nunca "se ve por debajo" el fondo del body
   (que en algunos temas puede traer wallpaper o gradiente). */
#statusbar{
    flex-shrink:0;margin-top:1px;padding:1px;
    background:var(--win-bg);
}
#statusbar .status-bar-field{
    /* 98.css aplica flex-grow:1 + box-shadow grey/#dfdfdf hardcoded.
       Aquí: parar el grow (cada campo se ajusta a su contenido) y
       reemplazar el bezel hundido por los tokens del tema. */
    flex:0 0 auto;
    min-width:0;
    padding:1px 8px;
    color:var(--text);
    background:var(--win-bg);
    box-shadow:
        inset -1px -1px var(--bezel-light-2),
        inset 1px 1px var(--bezel-dark-2);
}
/* "Conectados: X" empujado al extremo derecho del statusbar. */
#users{margin-left:auto}
#conn-status.online{color:var(--accent);font-weight:bold}
#conn-status.offline{color:var(--error-text)}

/* ────────── Readouts dentro del toolbar (size, zoom) — look
   hundido Win98 sin necesidad de var(), siguiendo 98.css. ────────── */
.tb-readout{
    display:inline-block;min-width:48px;font-size:11px;text-align:center;
    padding:1px 6px;color:var(--text);background:var(--win-bg);
    box-shadow:
        inset -1px -1px var(--bezel-light-2),
        inset 1px 1px var(--bezel-dark-2);
}

/* ────────── Workspace (canvas + sidebar) ────────── */
#workspace{flex:1;display:flex;overflow:hidden;min-height:0}
#canvas-wrap{
    flex:1;position:relative;
    background:var(--inset-bg);
    /* overflow:hidden — el lienzo se mueve con transform:translate(),
       puede salirse del wrap, y queda recortado limpio (sin
       scrollbars). El pan con Espacio es la única forma de moverlo. */
    overflow:hidden;
    display:flex;
    align-items:safe center;
    justify-content:safe center;
    padding:20px;
}
#canvas-wrap.panning{cursor:grab}
#canvas-wrap.panning.pan-active{cursor:grabbing}
#zoom-container{
    position:relative;flex-shrink:0;
    /* Drop-shadow del lienzo — usa el color de texto del tema con
       30% opacidad para que el "sombreado" siga la paleta (en kawaii
       el text es lila, así que la sombra tiene un tinte lila suave). */
    box-shadow:0 0 0 1px var(--border),4px 4px 0 0 color-mix(in srgb, var(--text) 30%, transparent);
    transform-origin:0 0;
}
/* ────────── Checker pattern temático (señal de transparencia)
   2 gradientes a 45° + 2 a -45° forman el cuadriculado clásico tipo
   Krita/Photoshop. Los colores derivan del tema:
     - light cell: --input-bg
     - dark  cell: 8% del color de texto sobre --input-bg
   El navegador los dibuja como vectores, no como bitmap → escala
   limpia a cualquier zoom. ────────── */
.checker-bg{
    background:
        linear-gradient(45deg,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 25%,
            transparent 25%, transparent 75%,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 75%) 0 0,
        linear-gradient(45deg,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 25%,
            transparent 25%, transparent 75%,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 75%) 6px 6px,
        var(--input-bg);
    background-size:12px 12px;
}

#display-canvas{
    display:block;background:transparent;
    touch-action:none;cursor:crosshair;
    /* Checker debajo del canvas — cuando la capa de fondo está oculta
       o el usuario pinta con alfa, el cuadriculado del tema aparece
       en lugar del color sólido del workspace. */
    background:
        linear-gradient(45deg,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 25%,
            transparent 25%, transparent 75%,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 75%) 0 0 / 16px 16px,
        linear-gradient(45deg,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 25%,
            transparent 25%, transparent 75%,
            color-mix(in srgb, var(--text) 8%, var(--input-bg)) 75%) 8px 8px / 16px 16px,
        var(--input-bg);
}
#display-canvas.eraser{cursor:cell}
#display-canvas.eyedropper{cursor:copy}
#display-canvas.selection{cursor:cell}
#display-canvas.text{cursor:text}

/* Input flotante de la herramienta de texto. JS lo crea on-demand y
   lo posiciona en client coords. */
.text-input-floating{
    position:fixed;
    z-index:9999;
    background:color-mix(in srgb, var(--input-bg) 85%, transparent);
    border:1px dashed var(--text);
    outline:none;
    padding:1px 3px;
    font-family:Arial, sans-serif;
    line-height:1;
}
#selection-overlay{
    position:absolute;border:1.5px dashed var(--text);
    pointer-events:none;
    /* Tinte de selección usa el acento del tema. */
    background:color-mix(in srgb, var(--accent) 12%, transparent);
    display:none;box-shadow:0 0 0 1px var(--input-bg);
    transform-origin:50% 50%;
}
#selection-overlay.transforming{
    pointer-events:auto;cursor:move;
    border-style:solid;
}
/* Botón flotante "borrar selección". JS pone left/top en CSS-px del
   zoom-container y display según estado. */
#selection-actions{
    position:absolute;
    display:none;
    pointer-events:auto;
    z-index:3;
    gap:2px;
}
#selection-actions.visible{display:flex}
#sel-delete{
    min-width:0;min-height:0;
    width:24px;height:24px;
    padding:0;
    font-size:12px;line-height:1;
    flex-shrink:0;
    background:var(--win-bg);
    color:var(--text);
    box-shadow:
        inset 1px 1px var(--bezel-light-1),
        inset -1px -1px var(--bezel-dark-1),
        inset 2px 2px var(--bezel-light-2),
        inset -2px -2px var(--bezel-dark-2);
    cursor:pointer;
}
#sel-delete:active{
    box-shadow:
        inset 1px 1px var(--bezel-dark-1),
        inset -1px -1px var(--bezel-light-1),
        inset 2px 2px var(--bezel-dark-2),
        inset -2px -2px var(--bezel-light-2);
}
/* Menú contextual del Transform — popup nativo Win98, fixed para no
   quedar capturado por overflow:hidden del wrap. */
.transform-ctx-menu{
    position:fixed!important;
    z-index:1000;
}
.tx-handle{
    position:absolute;width:10px;height:10px;
    background:var(--input-bg);
    border:1px solid var(--text);
    box-sizing:border-box;
    transform:translate(-50%,-50%);
    z-index:2;
}
.tx-handle.nw,.tx-handle.se{cursor:nwse-resize}
.tx-handle.ne,.tx-handle.sw{cursor:nesw-resize}
.tx-handle.n,.tx-handle.s{cursor:ns-resize}
.tx-handle.e,.tx-handle.w{cursor:ew-resize}
.tx-handle.rot{
    width:12px;height:12px;border-radius:50%;
    background:var(--accent);border-color:var(--accent-text);
    cursor:alias;
}
.tx-handle.rot::before{
    /* Línea desde el handle hasta el borde superior de la selección. */
    content:'';position:absolute;left:50%;top:100%;
    width:1px;height:14px;background:var(--accent);
    transform:translateX(-50%);
}

/* ────────── Sidebar — los paneles usan .window de 98.css; aquí
   solo controlamos layout, gaps y márgenes. 98.css ya pinta el bezel +
   title-bar con gradient navy. ────────── */
#sidebar{
    width:240px;flex-shrink:0;
    background:var(--win-bg);
    border-left:2px solid;
    border-color:var(--bezel-light-1) var(--border) var(--border) var(--bezel-light-1);
    display:flex;flex-direction:column;gap:6px;padding:6px;overflow-y:auto;
}
#sidebar .panel{margin:0}
#sidebar .panel > .title-bar{padding:2px 4px}
#sidebar .panel > .window-body{margin:6px;padding:0}
/* Panel de Capas (2º panel) — flex:1 ocupa todo el espacio sobrante
   entre Color y Pinceles, dejando Pinceles anclado al fondo del
   sidebar. min-height:0 + display:flex en .window y .window-body
   permite que #layers-list herede el espacio y muestre scroll cuando
   se queda sin sitio. */
#sidebar > .panel:nth-of-type(2){
    flex:1 1 0;
    min-height:0;
    display:flex;
    flex-direction:column;
}
#sidebar > .panel:nth-of-type(2) > .window-body{
    flex:1 1 0;
    min-height:0;
    display:flex;
    flex-direction:column;
}

/* ────────── Panel de pinceles ────────── */
.brush-current{
    text-align:center;font-size:11px;
    margin:0 0 4px;
    padding:2px 4px;
    color:var(--text);
    background:var(--win-bg);
    box-shadow:
        inset 1px 1px var(--bezel-dark-1),
        inset -1px -1px var(--bezel-light-1);
}
.brush-grid{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    grid-auto-rows:30px;
    gap:2px;
    margin-bottom:4px;
    /* Altura fija para ~5 filas — no depende de cuántos pinceles haya;
       si hay más, aparece scrollbar. align-content:start evita que la
       grid centre verticalmente cuando hay pocos items. */
    height:160px;overflow-y:auto;
    align-content:start;
}
.brush-btn{
    min-width:0;min-height:0;padding:1px;
    background:var(--input-bg);
    box-shadow:
        inset -1px -1px var(--bezel-dark-1),
        inset 1px 1px var(--bezel-light-1),
        inset -2px -2px var(--bezel-dark-2),
        inset 2px 2px var(--bezel-light-2);
    cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    color:var(--text);
}
.brush-btn:hover{filter:brightness(1.05)}
.brush-btn.active{
    background:var(--accent);
    box-shadow:
        inset 1px 1px var(--bezel-dark-1),
        inset -1px -1px var(--bezel-light-1),
        inset 2px 2px var(--bezel-dark-2),
        inset -2px -2px var(--bezel-light-2);
}
.brush-btn canvas{display:block;width:32px;height:22px;image-rendering:auto}
.brush-opacity-row{
    display:flex;align-items:center;gap:4px;margin-top:6px;
}
.brush-opacity-label{font-size:11px;color:var(--text);flex-shrink:0}
input[type=range].brush-opacity-slider{
    flex:1;min-width:0;
}
#brush-opacity-val{min-width:42px;font-size:11px}

/* ────────── Color picker ────────── */
#color-wheel{
    width:140px;height:140px;display:block;margin:0 auto 4px;
    cursor:crosshair;background:var(--input-bg);
    box-shadow:
        inset 1px 1px var(--bezel-dark-1),
        inset -1px -1px var(--bezel-light-1),
        inset 2px 2px var(--bezel-dark-2),
        inset -2px -2px var(--bezel-light-2);
}
.color-preview{display:flex;align-items:center;gap:6px;padding:2px 0}
.color-swatch{
    width:28px;height:18px;
    border:1px solid var(--text);
}
.color-hex{
    flex:1;font-family:monospace;font-size:11px;
    padding:2px 4px;border:none;min-width:0;
}
.hsv-slider-row{
    display:flex;align-items:center;gap:4px;padding:1px 0;
}
.hsv-label{
    font-size:11px;font-weight:bold;color:var(--text);
    width:12px;text-align:center;flex-shrink:0;
}
.hsv-slider-row .tb-readout{min-width:36px;font-size:11px;padding:1px 4px}

/* Sliders HSV: anulamos el track y el thumb de 98.css y los
   redibujamos como una barra de color con un marcador finito. El
   gradiente del fondo se setea desde JS en función del color actual. */
input[type=range].hsv-slider{
    -webkit-appearance:none; appearance:none;
    flex:1; min-width:0;
    height:14px; padding:0; margin:2px 0;
    background:transparent;
    cursor:ew-resize;
}
input[type=range].hsv-slider::-webkit-slider-runnable-track{
    height:14px; width:100%;
    border:1px solid var(--text);
    box-shadow:none;
    background:inherit;  /* heredamos el gradient seteado en .style.background del input */
}
input[type=range].hsv-slider::-moz-range-track{
    height:14px; width:100%;
    border:1px solid var(--text);
    box-shadow:none;
    background:inherit;
}
input[type=range].hsv-slider::-webkit-slider-thumb{
    -webkit-appearance:none; appearance:none;
    width:6px; height:20px;
    background:var(--input-bg);
    border:1px solid var(--text);
    box-shadow:none;
    margin-top:-4px;
    transform:none;  /* cancela el translate de 98.css */
    cursor:ew-resize;
}
input[type=range].hsv-slider::-moz-range-thumb{
    width:6px; height:20px;
    background:var(--input-bg);
    border:1px solid var(--text);
    box-shadow:none;
    border-radius:0;
    cursor:ew-resize;
}

/* ────────── Capas — sunken-panel ya viene de 98.css ────────── */
.layer-actions{display:flex;gap:2px;justify-content:center;margin-bottom:4px}
.layer-actions button{
    flex:1;
    min-width:0;min-height:0;
    padding:2px 0;
    font-size:10px;
}
#layers-list{
    display:flex;flex-direction:column;gap:1px;
    padding:2px;
    /* Crece para llenar el .window-body sobrante del panel Capas.
       Con flex:1+min-height:0 el contenedor padre le da altura
       efectiva y aparece scroll cuando se queda sin sitio. */
    flex:1 1 0;
    min-height:0;
    overflow-y:auto;
    /* 98.css fija background-color:#fff y un border-image SVG con
       colores grey/0a0a0a/dfdfdf incrustados (no themeables) en
       .sunken-panel. Lo anulamos y reproducimos el bezel hundido con
       4 inset box-shadow usando tokens del tema. */
    background:var(--win-bg);
    border:none;
    border-image:none;
    box-shadow:
        inset 1px 1px var(--bezel-dark-1),
        inset -1px -1px var(--bezel-light-1),
        inset 2px 2px var(--bezel-dark-2),
        inset -2px -2px var(--bezel-light-2);
}
.layer-item{
    display:flex;align-items:center;gap:4px;padding:3px 4px;
    cursor:grab;background:var(--win-bg);color:var(--text);font-size:11px;
    user-select:none;
}
.layer-item.dragging{
    opacity:0.3;
    cursor:grabbing;
}
/* Header de grupo: barra horizontal con checkbox de máscara de
   recorte + nombre + botón eliminar. Las capas dentro del grupo
   llevan .in-group con un border-left para indicar la pertenencia. */
.group-header{
    display:flex;align-items:center;gap:4px;
    padding:3px 4px;font-size:11px;font-weight:bold;
    color:var(--text);
    background:linear-gradient(to right, var(--titlebar-start), var(--titlebar-end));
    color:var(--titlebar-text);
    user-select:none;
}
.group-header input[type=checkbox]{
    position:static;opacity:1;
    appearance:auto;-webkit-appearance:auto;-moz-appearance:auto;
    margin:0;width:14px;height:14px;flex-shrink:0;
}
.group-name{
    flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    cursor:text;
}
.group-del{
    min-width:0;min-height:0;
    width:18px;height:18px;
    padding:0;font-size:11px;line-height:1;
    flex-shrink:0;
}
/* Indicador de "drop dentro del grupo" — recuadro completo del header
   con un borde interior del color acento. */
.group-header.drop-into{
    box-shadow:inset 0 0 0 2px var(--accent);
}
/* Botón de máscara de recorte por capa (solo aparece en capas dentro
   de grupo). Cuando .active el botón se rellena con el color acento
   indicando que esa capa está recortada a la silueta de la capa de
   abajo del mismo grupo. */
.layer-clip{
    min-width:0;min-height:0;
    width:18px;height:18px;
    padding:0;
    font-size:13px;line-height:1;font-weight:bold;
    font-family:'Times New Roman', serif;  /* glifo griego más legible */
    flex-shrink:0;
    color:var(--text);
}
.layer-clip.active{
    background:var(--accent);
    color:var(--accent-text);
}
.layer-item.in-group{
    padding-left:14px;
    border-left:3px solid var(--accent);
}
/* Menú contextual de capa: position:fixed para no quedar contenido por
   el sidebar (overflow:hidden). Z-index alto para flotar encima. */
.layer-ctx-menu{
    position:fixed!important;
    z-index:1000;
}
/* Indicador de drop: una sola línea fina en el borde donde caerá la
   capa arrastrada. */
.layer-item.drop-above{
    box-shadow:inset 0 2px 0 var(--accent);
}
.layer-item.drop-below{
    box-shadow:inset 0 -2px 0 var(--accent);
}
.layer-item.active{
    background:var(--accent);
    color:var(--accent-text);
}
/* 98.css esconde los checkboxes nativos para reemplazarlos con sus
   propias imágenes. En la lista de capas queremos el control nativo
   simple — restauramos visibilidad y posición. */
.layer-item input[type=checkbox]{
    position:static;opacity:1;appearance:auto;-webkit-appearance:auto;-moz-appearance:auto;
    margin:0;width:14px;height:14px;flex-shrink:0;
}
/* Preview cuadrado de cada capa: 22x22 con bezel hundido temático.
   El checker SIEMPRE es blanco/gris-claro (no sigue el tema), como
   en Photoshop/Krita — así el preview es legible y comparable entre
   capas independientemente del tema activo del usuario. */
.layer-thumb{
    flex:0 0 22px;
    width:22px;height:22px;
    display:block;
    background:
        linear-gradient(45deg, #d0d0d0 25%, transparent 25%, transparent 75%, #d0d0d0 75%) 0 0 / 6px 6px,
        linear-gradient(45deg, #d0d0d0 25%, transparent 25%, transparent 75%, #d0d0d0 75%) 3px 3px / 6px 6px,
        #ffffff;
    box-shadow:
        inset 1px 1px var(--bezel-dark-1),
        inset -1px -1px var(--bezel-light-1);
    image-rendering:pixelated;
}
.layer-name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
/* Mismo override que .tb-slider — selector compuesto sube especificidad
   a 0,2,1 para vencer el width:100% genérico de 98.css. */
input[type=range].layer-opacity{
    width:54px;
    flex:0 0 54px;
    margin:0;
}


/* ────────── Popup menu (Archivo) ────────── */
.popup-menu{
    position:absolute;
    background:var(--win-bg);color:var(--text);
    box-shadow:
        inset -1px -1px var(--bezel-dark-1),
        inset 1px 1px var(--bezel-light-1),
        inset -2px -2px var(--bezel-dark-2),
        inset 2px 2px var(--bezel-light-2);
    padding:2px;z-index:200;display:none;min-width:180px;
}
.popup-menu.open{display:block}
.popup-item{
    padding:4px 16px;cursor:pointer;font-size:11px;
    display:flex;justify-content:space-between;align-items:center;gap:12px;
    color:var(--text);
}
.popup-item:hover{
    background:var(--accent);
    color:var(--accent-text);
}
.popup-sep{
    height:1px;background:var(--bezel-dark-2);
    box-shadow:0 1px 0 var(--bezel-light-1);
    margin:3px 0;
}

/* ────────── Dialog Nuevo Lienzo — usa .window de 98.css ────────── */
.dialog-bd{
    position:fixed;inset:0;
    /* Backdrop = color de texto del tema con 40% opacidad. En kawaii
       el text es lila → backdrop con tinte lila; en win98 → oscuro. */
    background:color-mix(in srgb, var(--text) 40%, transparent);
    display:none;align-items:center;justify-content:center;z-index:300;
}
.dialog-bd.open{display:flex}
.dialog{min-width:300px}
.dialog .window-body label{
    display:flex;align-items:center;gap:8px;font-size:11px;
}
.dialog .window-body input[type=number]{width:80px}

/* ────────────────────────────────────────────────────────────────────
   VARIANTE KAWAII — body.iface-kawaii
   La interfaz CSS ya redefine los tokens; aquí solo añadimos los
   ajustes específicos del dibujo que las variables no cubren:
   esquinas redondeadas, sombras suaves y eliminación de los bezels
   3D más agresivos del look Win98 para encajar con MelonOS Overdose.
   ──────────────────────────────────────────────────────────────────── */
body.iface-kawaii{
    background:var(--win-body-bg);
}
body.iface-kawaii .window,
body.iface-kawaii .popup-menu{
    box-shadow:0 2px 0 var(--border),0 0 0 1px var(--border);
    border-radius:8px;
    background:var(--win-body-bg);
    padding:0;
}
body.iface-kawaii .window > .title-bar{
    border-radius:6px 6px 0 0;
    background:var(--accent);
    color:var(--accent-text);
}
body.iface-kawaii #menubar,
body.iface-kawaii #toolbar{
    background:var(--win-body-bg);
    border-color:var(--border);
}
body.iface-kawaii #statusbar.status-bar .status-bar-field{
    border:1px solid var(--border);
    border-radius:6px;
    background:var(--win-bg);
    box-shadow:none;
}
body.iface-kawaii .tb-btn,
body.iface-kawaii .menu-btn,
body.iface-kawaii .layer-actions button,
body.iface-kawaii .dialog .window-body button{
    border:1px solid var(--border);
    border-radius:6px;
    box-shadow:0 1px 0 var(--border);
    background:var(--win-bg);
    color:var(--text);
    text-shadow:none;
}
/* Kawaii's style.css fuerza padding:3px 10px !important en .tb-btn, lo
   que aplasta el SVG interno (la goma se ve diminuta). Restauramos el
   padding:0 + tamaños originales. */
body.iface-kawaii .tb-btn{
    padding:0!important;
    width:28px!important;height:28px!important;
}
body.iface-kawaii .tb-btn.active,
body.iface-kawaii .tb-btn:active,
body.iface-kawaii .menu-btn.open,
body.iface-kawaii .layer-actions button:active,
body.iface-kawaii .dialog .window-body button:active{
    background:var(--accent);
    color:var(--accent-text);
    /* Sombra interior pressed = color de texto al 20% (lila en kawaii). */
    box-shadow:inset 0 1px 2px color-mix(in srgb, var(--text) 20%, transparent);
    transform:translateY(1px);
}
body.iface-kawaii .tb-select,
body.iface-kawaii #color-wheel,
body.iface-kawaii .sunken-panel,
body.iface-kawaii .color-hex,
body.iface-kawaii .dialog input[type=number]{
    border:1px solid var(--border);
    border-radius:6px;
    box-shadow:none;
}
body.iface-kawaii .layer-item.active{
    background:var(--accent);
    color:var(--accent-text);
    border-radius:4px;
}
body.iface-kawaii #canvas-wrap{
    background:var(--win-body-bg);
}
</style>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">

<div id="menubar">
    <button class="menu-btn" id="menu-archivo">Archivo</button>
    <div class="popup-menu" id="menu-archivo-popup">
        <div class="popup-item" data-act="new">Nuevo lienzo...</div>
        <div class="popup-item" data-act="open-ora">Abrir imagen / .ora / .kra...</div>
        <div class="popup-sep"></div>
        <div class="popup-item" data-act="save-gallery">Guardar <small>(Ctrl+S)</small></div>
        <div class="popup-item" data-act="download-ora">Descargar como .ora</div>
        <div class="popup-item" data-act="save-png">Exportar como PNG</div>
        <div class="popup-item" data-act="save-zip">Exportar capas (ZIP)</div>
    </div>
    <input type="file" id="ora-file-input"
           accept=".ora,.kra,.png,.jpg,.jpeg,.gif,.webp,.bmp,.avif,image/*"
           style="display:none">
</div>

<div id="toolbar">
    <button class="tb-btn active" data-tool="brush"   title="Pincel (B)">🖌️</button>
    <button class="tb-btn"        data-tool="eraser"  title="Goma (E)"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><rect x="2" y="7" width="11" height="6" rx="1" fill="#ffe066" stroke="#222" stroke-width="0.8"/><rect x="2" y="4.5" width="11" height="3" rx="1" fill="#5b9be0" stroke="#222" stroke-width="0.8"/><line x1="2" y1="10" x2="13" y2="10" stroke="#222" stroke-width="0.5" opacity="0.6"/></svg></button>
    <button class="tb-btn"        data-tool="eyedrop" title="Cuentagotas (I)">💧</button>
    <button class="tb-btn"        data-tool="select"  title="Selección rectangular (M)">⬚</button>
    <button class="tb-btn"        data-tool="text"    title="Texto (T)">T</button>
    <span class="tb-sep"></span>
    <span class="tb-label">Grosor:</span>
    <input type="range" class="tb-slider" id="size" min="1" max="200" value="12">
    <span class="tb-readout" id="size-val">12</span>
    <span class="tb-sep"></span>
    <span class="tb-label">Suavizado:</span>
    <input type="range" class="tb-slider" id="smooth" min="0" max="10" value="3">
    <span class="tb-sep"></span>
    <button class="tb-btn" id="btn-undo" title="Deshacer (Ctrl+Z)">↶</button>
    <button class="tb-btn" id="btn-redo" title="Rehacer (Ctrl+Shift+Z)">↷</button>
    <span class="tb-sep"></span>
    <button class="tb-btn" id="btn-zoom-out" title="Alejar">-</button>
    <span class="tb-readout" id="zoom-val">100%</span>
    <button class="tb-btn" id="btn-zoom-in"  title="Acercar">+</button>
    <button class="tb-btn" id="btn-zoom-1"   title="Zoom 100%">🔎</button>
    <button class="tb-btn" id="btn-zoom-fit" title="Ajustar a ventana">⛶</button>
</div>

<div id="tab-bar"></div>

<div id="workspace">
    <div id="canvas-wrap">
        <div id="zoom-container">
            <canvas id="display-canvas"></canvas>
            <div id="selection-overlay"></div>
            <!-- Acciones que flotan junto a la selección. JS las
                 posiciona en CSS-px del zoom-container, así escalan
                 con el lienzo y se ocultan según el estado. -->
            <div id="selection-actions">
                <button id="sel-delete" title="Quitar selección">
                    <img src="../assets/img/appIcons/trashIcon.png" alt=""
                         style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">
                </button>
            </div>
        </div>
    </div>
    <div id="sidebar">
        <div class="window panel">
            <div class="title-bar">
                <div class="title-bar-text">Color</div>
            </div>
            <div class="window-body">
                <canvas id="color-wheel" width="140" height="140"></canvas>
                <div class="color-preview">
                    <div class="color-swatch" id="color-swatch"></div>
                    <input type="text" class="color-hex" id="color-hex" value="#000000" maxlength="7">
                </div>
                <div class="hsv-slider-row">
                    <span class="hsv-label">H</span>
                    <input type="range" id="color-h" class="hsv-slider hsv-h" min="0" max="360" value="0">
                    <span class="tb-readout" id="color-h-val">0</span>
                </div>
                <div class="hsv-slider-row">
                    <span class="hsv-label">S</span>
                    <input type="range" id="color-s" class="hsv-slider hsv-s" min="0" max="100" value="0">
                    <span class="tb-readout" id="color-s-val">0</span>
                </div>
                <div class="hsv-slider-row">
                    <span class="hsv-label">V</span>
                    <input type="range" id="color-v" class="hsv-slider hsv-v" min="0" max="100" value="0">
                    <span class="tb-readout" id="color-v-val">0</span>
                </div>
            </div>
        </div>
        <div class="window panel">
            <div class="title-bar">
                <div class="title-bar-text">Capas</div>
            </div>
            <div class="window-body">
                <div class="layer-actions">
                    <button id="btn-layer-add" title="Nueva capa">➕</button>
                    <button id="btn-layer-del" title="Borrar capa">
                        <img src="../assets/img/appIcons/trashIcon.png" alt=""
                             style="width:12px;height:12px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">
                    </button>
                </div>
                <div class="sunken-panel" id="layers-list"></div>
            </div>
        </div>
        <div class="window panel">
            <div class="title-bar">
                <div class="title-bar-text">Pinceles</div>
            </div>
            <div class="window-body">
                <div id="brush-current" class="brush-current">Pincel suave</div>
                <div id="brush-grid" class="brush-grid"></div>
                <div class="brush-opacity-row">
                    <span class="brush-opacity-label">Opacidad:</span>
                    <input type="range" id="brush-opacity" class="brush-opacity-slider" min="0" max="100" value="100">
                    <span class="tb-readout" id="brush-opacity-val">100%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="statusbar" class="status-bar">
    <p class="status-bar-field" id="pos">x: 0, y: 0</p>
    <p class="status-bar-field" id="pressure">p: 0.00</p>
    <p class="status-bar-field" id="type">--</p>
    <p class="status-bar-field" id="conn-status">offline</p>
    <p class="status-bar-field" id="users">Conectados: 1</p>
</div>

<div class="dialog-bd" id="dlg-new">
    <div class="window dialog">
        <div class="title-bar">
            <div class="title-bar-text">Nuevo lienzo</div>
            <div class="title-bar-controls">
                <button aria-label="Close" data-close></button>
            </div>
        </div>
        <div class="window-body">
            <div class="field-row-stacked">
                <label>Ancho (px): <input type="number" id="new-w" min="64" max="4096" value="1280"></label>
                <label>Alto (px): <input type="number" id="new-h" min="64" max="4096" value="720"></label>
                <label>Color de fondo: <input type="color" id="new-bg" value="#ffffff"></label>
            </div>
            <section class="field-row" style="justify-content:flex-end;margin-top:14px;">
                <button data-close>Cancelar</button>
                <button class="default" id="new-ok">Crear</button>
            </section>
        </div>
    </div>
</div>

<script>
'use strict';

/* ════════════════════════════════════════════════════════════════════
   DIBUJO COLABORATIVO — Krita/Drawpile-style con capas, zoom, undo
   ════════════════════════════════════════════════════════════════════ */

const CFG = {
    FAKE_PRESSURE: 0.5, STABILIZER_MAX: 10, NET_FLUSH_MS: 33,
    UNDO_MAX: 30, ZOOM_MIN: 0.1, ZOOM_MAX: 16, ZOOM_STEP: 1.25,
};

const BRUSHES = {
    round:    { hardEdge:false, alphaCurve:2.0, flow:1.0,  spacing:0.10, scatter:0    },
    pencil:   { hardEdge:true,  alphaCurve:0.8, flow:1.0,  spacing:0.08, scatter:0    },
    pen:      { hardEdge:true,  alphaCurve:0.5, flow:1.0,  spacing:0.06, scatter:0    },
    marker:   { hardEdge:true,  alphaCurve:1.0, flow:0.55, spacing:0.04, scatter:0    },
    airbrush: { hardEdge:false, alphaCurve:1.5, flow:0.06, spacing:0.02, scatter:0    },
    chalk:    { hardEdge:false, alphaCurve:1.2, flow:0.65, spacing:0.18, scatter:0.45 },
    charcoal: { hardEdge:true,  alphaCurve:1.5, flow:0.45, spacing:0.22, scatter:0.65 },
    /* Difuminado: samplea el píxel composited bajo el dab y lo mezcla
       con el color "arrastrado" del dab anterior (smudge tipo
       Photoshop) más una pequeña inyección del color del usuario.
       Bordes suaves, spacing tight y dragWeight alto = blending muy
       agresivo que esparce los colores a lo largo del trazo. */
    blender:  { hardEdge:false, alphaCurve:0.6, flow:0.18, spacing:0.03, scatter:0,
                blender:true,   dragWeight:0.85 },
};

const BRUSH_LABELS = {
    round:    'Pincel suave',
    pencil:   'Lápiz',
    pen:      'Bolígrafo',
    marker:   'Marcador',
    airbrush: 'Aerógrafo',
    chalk:    'Tiza',
    charcoal: 'Carboncillo',
    blender:  'Difuminado',
};

const $ = id => document.getElementById(id);
const display = $('display-canvas');
/* willReadFrequently mejora el rendimiento del cuentagotas. */
const dctx = display.getContext('2d', { alpha: true, willReadFrequently: true });

const state = {
    width: 1280, height: 720, bg: '#ffffff',
    layers: [], activeIdx: 0, nextLayerId: 1,
    groups: [], nextGroupId: 1,
    tool: 'brush', brushName: 'round',
    color: '#000000', size: 12, smooth: 3, brushOpacity: 1.0,
    blendPrev: null,  /* color arrastrado por el blender entre dabs del mismo trazo */
    drawing: false, pid: -1, lastDab: null, stab: [], strokeId: null,
    /* Cuando la capa activa tiene clipMask y abajo está en el mismo
       grupo, los trazos no escriben directamente en la capa: se pintan
       en strokeBuffer. composite() compone layer + buffer (con
       destination-out si es eraser) y aplica la máscara de la capa de
       abajo. Al final del trazo, se masca el buffer y se vuelca en la
       capa. Esto restringe lo dibujado a la silueta de la capa-base. */
    strokeBuffer: null, strokeBufferCtx: null, strokeIsEraser: false,
    strokeBufferLayerIdx: -1, strokeBufferBelowIdx: -1,
    /* Si el dibujo viene de un archivo de la galería (Drive), guardamos
       su fileId + nombre para que al guardar se actualice ese fichero
       en lugar de crear uno nuevo. */
    currentDriveFileId: null, currentDriveFileName: null,
    zoom: 1, panKey: false, panLastX: null, panLastY: null,
    panX: 0, panY: 0,
    selection: null, selecting: false, selStart: null,
    selectionRotation: 0,
    transforming: false, transformOp: null, transformStart: null,
    transformInitial: null, transformFloat: null,
    undoStack: [], redoStack: [],
    pendingSamples: [], flushTimer: null,
    /* Etiqueta del lienzo (mostrada en su tab). */
    name: 'Sin título',
    /* Si hay cambios no guardados desde la última carga / guardado. */
    isDirty: false,
};

/* ════════════════════════════════════════════════════════════════════
   TABS — varios lienzos abiertos a la vez. Cada tab guarda un snapshot
   de los keys "per-canvas" del state (layers, groups, undo, posición,
   etc.). Los keys globales (tool, brushName, color, panKey, etc.) se
   comparten entre tabs.
   ════════════════════════════════════════════════════════════════════ */
const TAB_KEYS = [
    'width','height','bg','layers','activeIdx','nextLayerId',
    'groups','nextGroupId','selection','selectionRotation',
    'undoStack','redoStack','panX','panY','zoom',
    'currentDriveFileId','currentDriveFileName','name','isDirty',
];
const tabs = [];          /* [{id, snapshot}] — snapshot stale para el tab activo */
let activeTabIdx = -1;
let nextTabId    = 1;

function _snapshotPerCanvas() {
    const snap = {};
    for (const k of TAB_KEYS) snap[k] = state[k];
    return snap;
}
function _loadPerCanvas(snap) {
    for (const k of TAB_KEYS) state[k] = snap[k];
    /* Estados transitorios que no tienen sentido al cambiar de tab. */
    state.drawing = false; state.lastDab = null; state.stab = [];
    state.strokeId = null; state.selecting = false; state.selStart = null;
    state.blendPrev = null;
    state.transforming = false; state.transformOp = null;
    state.transformStart = null; state.transformInitial = null;
    state.transformFloat = null;
    state.strokeBuffer = null; state.strokeBufferCtx = null;
    state.strokeIsEraser = false; state.strokeBufferLayerIdx = -1;
    state.strokeBufferBelowIdx = -1;
    /* Tab switch: el cache below/above pertenece al tab anterior. */
    _invalidateStrokeCache();
    /* Display canvas debe ajustarse al tamaño del lienzo del tab. */
    display.width = state.width;
    display.height = state.height;
    /* Overlay de selección — reset visual; refreshSelectionOverlay se
       encarga de mostrarlo si state.selection existe. */
    const ov = $('selection-overlay');
    ov.classList.remove('transforming');
    ov.style.backgroundImage = '';
    ov.style.transform = '';
}

function markDirty() {
    state.isDirty = true;
    renderTabs();
}
function markClean() {
    state.isDirty = false;
    renderTabs();
}

/* Crea un tab nuevo con un lienzo vacío y se cambia a él. */
function createNewTab(opts) {
    opts = opts || {};
    const w = opts.width || 1280, h = opts.height || 720, bg = opts.bg || '#ffffff';
    const name = opts.name || 'Sin título';
    /* Guardamos el estado actual en su tab antes de cambiar. */
    if (activeTabIdx >= 0 && tabs[activeTabIdx]) {
        tabs[activeTabIdx].snapshot = _snapshotPerCanvas();
    }
    /* Reset puro: layers/groups/undo limpios, lienzo en blanco. */
    state.width = w; state.height = h; state.bg = bg;
    state.layers = [];
    state.groups = [];
    state.undoStack = []; state.redoStack = [];
    state.activeIdx = 0;
    state.nextLayerId = 1; state.nextGroupId = 1;
    state.selection = null; state.selectionRotation = 0;
    state.panX = 0; state.panY = 0; state.zoom = 1;
    state.currentDriveFileId = null; state.currentDriveFileName = null;
    state.name = name; state.isDirty = false;
    display.width = w; display.height = h;
    const bgLayer = makeLayer('Fondo');
    bgLayer.ctx.fillStyle = bg; bgLayer.ctx.fillRect(0, 0, w, h);
    const draw = makeLayer('Capa 1');
    state.layers.push(bgLayer, draw);
    state.activeIdx = 1;
    document.documentElement.style.setProperty('--canvas-aspect', w + ' / ' + h);
    /* Empuja el nuevo tab + activo. */
    const tab = { id: nextTabId++, snapshot: _snapshotPerCanvas() };
    tabs.push(tab);
    activeTabIdx = tabs.length - 1;
    applyZoom();
    composite();
    renderLayers();
    renderTabs();
}

/* Cambia al tab N. Guarda el estado activo en su snapshot y carga el
   snapshot del nuevo. */
function switchToTab(idx) {
    if (idx < 0 || idx >= tabs.length || idx === activeTabIdx) return;
    if (activeTabIdx >= 0 && tabs[activeTabIdx]) {
        tabs[activeTabIdx].snapshot = _snapshotPerCanvas();
    }
    activeTabIdx = idx;
    _loadPerCanvas(tabs[idx].snapshot);
    document.documentElement.style.setProperty('--canvas-aspect',
        state.width + ' / ' + state.height);
    applyZoom();
    composite();
    renderLayers();
    refreshSelectionOverlay();
    renderTabs();
}

/* Intenta cerrar un tab. Si tiene cambios sin guardar, pregunta al
   usuario. Devuelve true si finalmente se cerró. */
async function closeTab(idx) {
    if (idx < 0 || idx >= tabs.length) return false;
    /* Si vamos a cerrar el activo, el state vive aquí; si no, el
       snapshot guardado es el "estado real" del tab cerrando. */
    const isActive = (idx === activeTabIdx);
    const dirty = isActive ? state.isDirty : tabs[idx].snapshot.isDirty;
    if (dirty) {
        /* Si está dirty pero no es el activo, cambiamos a él primero
           para que un eventual saveToGallery use sus datos. */
        if (!isActive) switchToTab(idx);
        const tabName = state.name || 'Sin título';
        const choice = await winDialog({
            title: 'Guardar cambios',
            message: 'Tienes cambios sin guardar en "' + tabName + '". ¿Guardarlos antes de cerrar?',
            buttons: [
                { label: 'Cancelar' },
                { label: 'No' },
                { label: 'Sí', default: true },
            ],
        });
        if (choice === -1 || choice === 0) return false;  /* Cancelar */
        if (choice === 2) {
            /* Sí → guardar y, si guardó OK, cerrar. saveToGallery puede
               abrir popups (auth, nombre) — esperamos a que termine y
               solo cerramos si isDirty quedó en false. */
            await saveToGallery();
            if (state.isDirty) return false;
        }
        /* choice === 1 → No → cerramos sin guardar. */
    }
    /* Cierre real. */
    tabs.splice(idx, 1);
    if (tabs.length === 0) {
        /* Si era el último, creamos uno nuevo vacío. */
        activeTabIdx = -1;
        createNewTab();
        return true;
    }
    if (idx < activeTabIdx) {
        activeTabIdx--;
    } else if (idx === activeTabIdx) {
        /* Carga el tab anterior o el siguiente. */
        activeTabIdx = Math.min(idx, tabs.length - 1);
        _loadPerCanvas(tabs[activeTabIdx].snapshot);
        document.documentElement.style.setProperty('--canvas-aspect',
            state.width + ' / ' + state.height);
        applyZoom();
        composite();
        renderLayers();
        refreshSelectionOverlay();
    }
    renderTabs();
    return true;
}

/* Cierra todos los tabs (con prompt para los dirty). Devuelve true si
   se cerraron todos. */
/* Renderiza la barra de tabs. Llamado tras cualquier cambio
   estructural: add/close/switch, marca dirty/clean, rename. */
function renderTabs() {
    const bar = $('tab-bar');
    if (!bar) return;
    bar.innerHTML = '';
    tabs.forEach((tab, idx) => {
        const isActive = (idx === activeTabIdx);
        /* Para el tab activo, los datos "vivos" están en state. Para
           los inactivos, en su snapshot. */
        const name  = isActive ? state.name    : tab.snapshot.name;
        const dirty = isActive ? state.isDirty : tab.snapshot.isDirty;
        const el = document.createElement('div');
        el.className = 'tab' + (isActive ? ' active' : '');
        el.title = name;
        el.innerHTML =
            '<span class="tab-name">' + escapeXml(name) + '</span>' +
            (dirty ? '<span class="tab-dirty">●</span>' : '') +
            '<button class="tab-close" title="Cerrar">×</button>';
        el.addEventListener('click', e => {
            if (e.target.closest('.tab-close')) return;  /* lo maneja el btn */
            switchToTab(idx);
        });
        el.querySelector('.tab-close').addEventListener('click', async e => {
            e.stopPropagation();
            await closeTab(idx);
        });
        bar.appendChild(el);
    });
    /* Botón "+" para nuevo tab. */
    const addBtn = document.createElement('button');
    addBtn.className = 'tab-new';
    addBtn.textContent = '+';
    addBtn.title = 'Nuevo lienzo';
    addBtn.addEventListener('click', () => createNewTab());
    bar.appendChild(addBtn);
}

async function closeAllTabs() {
    /* Iteramos por copia porque closeTab modifica `tabs`. */
    while (tabs.length) {
        /* Cierra el primero. Si el usuario cancela, abortamos. */
        const ok = await closeTab(0);
        if (!ok) return false;
        /* closeTab(0) en el último crea otro vacío — paramos antes. */
        if (tabs.length === 1 && !tabs[0].snapshot.isDirty
            && tabs[0].snapshot.layers.length <= 2) {
            /* "Vacío" = solo fondo+capa 1 limpia. No molesta cerrarlo. */
            tabs.splice(0, 1);
            activeTabIdx = -1;
            break;
        }
    }
    return true;
}

/* ════════════════════════════════════════════════════════════════════
   LAYERS
   ════════════════════════════════════════════════════════════════════ */
function makeLayer(name) {
    const cv = document.createElement('canvas');
    cv.width = state.width; cv.height = state.height;
    return { id: state.nextLayerId++, name, canvas: cv, ctx: cv.getContext('2d', { alpha: true }),
             visible: true, opacity: 1, groupId: null, clipMask: false };
}

/* ════════════════════════════════════════════════════════════════════
   GRUPOS — los grupos agrupan capas contiguas (mismo groupId en runs
   adyacentes del array). Pueden tener "máscara de recorte" activada:
   en ese modo, la capa de más abajo del grupo (índice menor) actúa
   como máscara alfa para todas las demás del mismo grupo.
   ════════════════════════════════════════════════════════════════════ */
function findGroup(id) {
    return state.groups.find(g => g.id === id) || null;
}
function pruneEmptyGroups() {
    state.groups = state.groups.filter(g =>
        state.layers.some(l => l.groupId === g.id));
}
function createGroupForLayer(idx) {
    snapshotForUndo({ kind: 'layer-op' });
    const g = { id: state.nextGroupId++, name: 'Grupo ' + (state.groups.length + 1) };
    state.groups.push(g);
    state.layers[idx].groupId = g.id;
    renderLayers(); composite();
}
function addLayerToGroup(layerIdx, groupId) {
    snapshotForUndo({ kind: 'layer-op' });
    state.layers[layerIdx].groupId = groupId;
    renderLayers(); composite();
}
function removeLayerFromGroup(layerIdx) {
    snapshotForUndo({ kind: 'layer-op' });
    state.layers[layerIdx].groupId = null;
    pruneEmptyGroups();
    renderLayers(); composite();
}
function deleteGroup(groupId) {
    snapshotForUndo({ kind: 'layer-op' });
    for (const l of state.layers) if (l.groupId === groupId) l.groupId = null;
    state.groups = state.groups.filter(g => g.id !== groupId);
    renderLayers(); composite();
}
function toggleLayerClipMask(layerIdx, on) {
    if (!state.layers[layerIdx]) return;
    state.layers[layerIdx].clipMask = on;
    _invalidateStrokeCache();
    composite();
}

/* Indica si la capa de un índice debe ser recortada por la de abajo
   (mismo grupo + clipMask activado). Devuelve el índice de la base
   de la máscara o -1 si no aplica. */
function clipBaseIdxFor(layerIdx) {
    const layer = state.layers[layerIdx];
    if (!layer || !layer.clipMask || layer.groupId == null) return -1;
    const below = layerIdx > 0 ? state.layers[layerIdx - 1] : null;
    if (!below || below.groupId !== layer.groupId) return -1;
    return layerIdx - 1;
}
function activeLayer() { return state.layers[state.activeIdx]; }
function activeCtx() { return activeLayer().ctx; }

function resetCanvas(w, h, bg) {
    state.width = w; state.height = h; state.bg = bg;
    state.layers = [];
    state.groups = [];
    const bgLayer = makeLayer('Fondo');
    bgLayer.ctx.fillStyle = bg; bgLayer.ctx.fillRect(0, 0, w, h);
    const draw = makeLayer('Capa 1');
    state.layers.push(bgLayer, draw);
    state.activeIdx = 1;
    display.width = w; display.height = h;
    state.undoStack = []; state.redoStack = [];
    /* Reset pan al crear un lienzo nuevo. */
    state.panX = 0; state.panY = 0;
    /* Olvida cualquier vínculo previo con archivo de la galería. */
    state.currentDriveFileId = null;
    state.currentDriveFileName = null;
    applyZoom();
    composite();
    renderLayers();
}

/* ── Compositing optimizado ──
   Para lienzos grandes el coste por composite era inaceptable: cada
   pointermove (incluso a 120 Hz) creaba un canvas fresco del tamaño
   completo del lienzo, dibujaba TODAS las capas otra vez y dejaba el
   anterior al GC. Tres optimizaciones combinadas:

   1) rAF throttling: las llamadas a composite() se coalescen a una por
      frame. Eventos repetidos en un mismo frame no causan trabajo extra.

   2) Scratch canvas persistente: el canvas off-screen para clipping/
      buffer-blit se reusa entre llamadas. clearRect + redraw es
      órdenes de magnitud más barato que createElement+resize en
      lienzos grandes.

   3) Cache below/above durante un trazo: la única capa que cambia
      durante un stroke es la activa + strokeBuffer. Cacheamos el
      compuesto "todo lo de abajo" + "todo lo de arriba" UNA vez al
      iniciar el trazo (o al cambiar de capa activa) y solo recombinamos
      la activa con el buffer en cada frame. El stack puede tener N
      capas; el trabajo por frame es ~constante en N.

      Limitación: si una capa POR ENCIMA de la activa tiene clipMask
      con groupId == activa.groupId, depende de la activa y el cache
      de arriba sería incorrecto. En ese caso desactivamos el atajo
      y caemos al camino completo. Es raro en la práctica. */
let _scratchCanvas = null, _scratchCtx = null;
/* Si rect=null limpia el scratch entero (legacy). Si rect={x,y,w,h}
   limpia solo ese rect — usado por el sub-rect path para no pagar
   O(canvas) en lienzos grandes. El caller debe responsabilizarse de
   no leer fuera del rect que limpió. */
function _getScratchCanvas(rect) {
    if (!_scratchCanvas) {
        _scratchCanvas = document.createElement('canvas');
        _scratchCtx = _scratchCanvas.getContext('2d');
    }
    if (_scratchCanvas.width !== state.width || _scratchCanvas.height !== state.height) {
        _scratchCanvas.width = state.width;
        _scratchCanvas.height = state.height;
    }
    if (rect) {
        _scratchCtx.clearRect(rect.x, rect.y, rect.w, rect.h);
    } else {
        _scratchCtx.clearRect(0, 0, state.width, state.height);
    }
    _scratchCtx.globalCompositeOperation = 'source-over';
    _scratchCtx.globalAlpha = 1;
    return _scratchCtx;
}

/* ── Dirty rect tracking ──
   Para no repintar el lienzo entero cuando solo cambia un círculo de
   N px. applyStrokeSample añade el bbox del dab/segmento aquí;
   _compositeNow lo usa para restringir clear+drawImage. En lienzos
   grandes (4k×4k), el coste por frame baja órdenes de magnitud — un
   trazo afecta ~100×100 px, no 16M. */
let _dirty = null;
function _markDirty(x, y, w, h) {
    if (w <= 0 || h <= 0) return;
    if (!_dirty) { _dirty = { x, y, w, h }; return; }
    const x1 = Math.min(_dirty.x, x);
    const y1 = Math.min(_dirty.y, y);
    const x2 = Math.max(_dirty.x + _dirty.w, x + w);
    const y2 = Math.max(_dirty.y + _dirty.h, y + h);
    _dirty.x = x1; _dirty.y = y1;
    _dirty.w = x2 - x1; _dirty.h = y2 - y1;
}
function _clampDirtyToCanvas() {
    if (!_dirty) return null;
    const dx = Math.max(0, Math.floor(_dirty.x));
    const dy = Math.max(0, Math.floor(_dirty.y));
    const r  = Math.min(state.width,  Math.ceil(_dirty.x + _dirty.w));
    const b  = Math.min(state.height, Math.ceil(_dirty.y + _dirty.h));
    const w = r - dx, h = b - dy;
    if (w <= 0 || h <= 0) return null;
    return { x: dx, y: dy, w, h };
}
function _markDabDirty(x, y, width) {
    /* +2 px de margen por anti-alias / radial-gradient. */
    const pad = Math.max(2, width / 2 + 2);
    _markDirty(x - pad, y - pad, 2 * pad, 2 * pad);
}
function _markSegmentDirty(from, to, maxWidth) {
    const pad = Math.max(2, maxWidth / 2 + 2);
    const minX = Math.min(from.x, to.x) - pad;
    const minY = Math.min(from.y, to.y) - pad;
    const maxX = Math.max(from.x, to.x) + pad;
    const maxY = Math.max(from.y, to.y) + pad;
    _markDirty(minX, minY, maxX - minX, maxY - minY);
}

let _strokeCache = null; /* {below, above, activeIdx, fastPath} */
function _invalidateStrokeCache() {
    _strokeCache = null;
    /* Si algo más fuera del trazo afectó el stack (visibility, opacity,
       layer reorder), un sub-rect composite dejaría datos viejos en el
       resto del display. Forzamos repintado completo en el próximo
       composite reseteando el dirty rect. */
    _dirty = null;
}

function _composeRangeTo(ctx, fromIdx, toIdx) {
    /* Compone capas en [fromIdx, toIdx) sobre ctx, sin tener en cuenta
       el strokeBuffer (es el "estado estable" de cada extremo del stack
       durante el trazo). Misma lógica de clipping que el camino full. */
    for (let i = fromIdx; i < toIdx; i++) {
        const layer = state.layers[i];
        if (!layer.visible) continue;
        const below = i > 0 ? state.layers[i - 1] : null;
        const shouldClip = layer.clipMask
            && layer.groupId != null
            && below
            && below.groupId === layer.groupId;
        if (!shouldClip) {
            ctx.globalAlpha = layer.opacity;
            ctx.drawImage(layer.canvas, 0, 0);
            ctx.globalAlpha = 1;
            continue;
        }
        const oc = _getScratchCanvas();
        oc.drawImage(layer.canvas, 0, 0);
        oc.globalCompositeOperation = 'destination-in';
        oc.drawImage(below.canvas, 0, 0);
        oc.globalCompositeOperation = 'source-over';
        ctx.globalAlpha = layer.opacity;
        ctx.drawImage(_scratchCanvas, 0, 0);
        ctx.globalAlpha = 1;
    }
}

function _aboveDependsOnActive(activeIdx) {
    /* La capa activa+1 tiene clipMask y comparte grupo con la activa:
       su renderizado depende del contenido de la activa, así que un
       cache "above" pre-renderizado sería incorrecto. */
    const a = state.layers[activeIdx];
    const above = state.layers[activeIdx + 1];
    if (!above || !above.clipMask || above.groupId == null) return false;
    return above.groupId === a.groupId;
}

function _ensureStrokeCache(activeIdx) {
    if (_strokeCache && _strokeCache.activeIdx === activeIdx && _strokeCache.fastPath) return _strokeCache;
    if (_aboveDependsOnActive(activeIdx)) {
        _strokeCache = { activeIdx, fastPath: false };
        return _strokeCache;
    }
    const below = document.createElement('canvas');
    below.width = state.width; below.height = state.height;
    _composeRangeTo(below.getContext('2d'), 0, activeIdx);

    const above = document.createElement('canvas');
    above.width = state.width; above.height = state.height;
    _composeRangeTo(above.getContext('2d'), activeIdx + 1, state.layers.length);

    _strokeCache = { below, above, activeIdx, fastPath: true };
    return _strokeCache;
}

/* Versión sub-rect: igual que _compositeActiveLayerInto pero limita
   clear+drawImage al rect (dx, dy, dw, dh). Para las composite ops
   especiales (destination-in con below, destination-out con buffer)
   usamos clip path para que solo afecten al rect — sin clip, esas ops
   tocarían el scratch entero y serían O(canvas). Con clip son O(rect). */
function _compositeActiveLayerIntoRect(ctx, activeIdx, dx, dy, dw, dh) {
    const layer = state.layers[activeIdx];
    if (!layer.visible) return;
    const below = activeIdx > 0 ? state.layers[activeIdx - 1] : null;
    const shouldClip = layer.clipMask
        && layer.groupId != null
        && below
        && below.groupId === layer.groupId;
    const hasBuf = state.strokeBuffer && state.strokeBufferLayerIdx === activeIdx;
    if (!shouldClip && !hasBuf) {
        ctx.globalAlpha = layer.opacity;
        ctx.drawImage(layer.canvas, dx, dy, dw, dh, dx, dy, dw, dh);
        ctx.globalAlpha = 1;
        return;
    }
    const oc = _getScratchCanvas({ x: dx, y: dy, w: dw, h: dh });
    /* Clip al rect: las composite ops solo afectan dentro del rect. */
    oc.save();
    oc.beginPath();
    oc.rect(dx, dy, dw, dh);
    oc.clip();
    oc.drawImage(layer.canvas, dx, dy, dw, dh, dx, dy, dw, dh);
    if (hasBuf) {
        if (state.strokeIsEraser) {
            oc.globalCompositeOperation = 'destination-out';
            oc.drawImage(state.strokeBuffer, dx, dy, dw, dh, dx, dy, dw, dh);
            oc.globalCompositeOperation = 'source-over';
        } else {
            oc.drawImage(state.strokeBuffer, dx, dy, dw, dh, dx, dy, dw, dh);
        }
    }
    if (shouldClip) {
        oc.globalCompositeOperation = 'destination-in';
        oc.drawImage(below.canvas, dx, dy, dw, dh, dx, dy, dw, dh);
        oc.globalCompositeOperation = 'source-over';
    }
    oc.restore();
    ctx.globalAlpha = layer.opacity;
    ctx.drawImage(_scratchCanvas, dx, dy, dw, dh, dx, dy, dw, dh);
    ctx.globalAlpha = 1;
}

function _compositeActiveLayerInto(ctx, activeIdx) {
    const layer = state.layers[activeIdx];
    if (!layer.visible) return;
    const below = activeIdx > 0 ? state.layers[activeIdx - 1] : null;
    const shouldClip = layer.clipMask
        && layer.groupId != null
        && below
        && below.groupId === layer.groupId;
    const hasBuf = state.strokeBuffer && state.strokeBufferLayerIdx === activeIdx;
    if (!shouldClip && !hasBuf) {
        ctx.globalAlpha = layer.opacity;
        ctx.drawImage(layer.canvas, 0, 0);
        ctx.globalAlpha = 1;
        return;
    }
    const oc = _getScratchCanvas();
    oc.drawImage(layer.canvas, 0, 0);
    if (hasBuf) {
        if (state.strokeIsEraser) {
            oc.globalCompositeOperation = 'destination-out';
            oc.drawImage(state.strokeBuffer, 0, 0);
            oc.globalCompositeOperation = 'source-over';
        } else {
            oc.drawImage(state.strokeBuffer, 0, 0);
        }
    }
    if (shouldClip) {
        oc.globalCompositeOperation = 'destination-in';
        oc.drawImage(below.canvas, 0, 0);
        oc.globalCompositeOperation = 'source-over';
    }
    ctx.globalAlpha = layer.opacity;
    ctx.drawImage(_scratchCanvas, 0, 0);
    ctx.globalAlpha = 1;
}

function _compositeNow() {
    /* Atajo: durante un trazo (strokeBuffer activo) usamos los caches
       below/above y solo recompositamos la capa activa. Si tenemos
       dirty rect además, restringimos clear+drawImage a ese rect —
       el blit pasa de 16M px a unos pocos miles en lienzos grandes. */
    const hasStroke = !!state.strokeBuffer && state.strokeBufferLayerIdx >= 0;
    if (hasStroke) {
        const ai = state.strokeBufferLayerIdx;
        const cache = _ensureStrokeCache(ai);
        if (cache.fastPath) {
            const d = _clampDirtyToCanvas();
            if (d) {
                /* Restringido al dirty rect. */
                dctx.clearRect(d.x, d.y, d.w, d.h);
                dctx.drawImage(cache.below, d.x, d.y, d.w, d.h, d.x, d.y, d.w, d.h);
                _compositeActiveLayerIntoRect(dctx, ai, d.x, d.y, d.w, d.h);
                dctx.drawImage(cache.above, d.x, d.y, d.w, d.h, d.x, d.y, d.w, d.h);
            } else {
                /* Sin dirty rect (primer frame del stroke) — pintamos todo. */
                dctx.clearRect(0, 0, state.width, state.height);
                dctx.drawImage(cache.below, 0, 0);
                _compositeActiveLayerInto(dctx, ai);
                dctx.drawImage(cache.above, 0, 0);
            }
            _dirty = null;
            return;
        }
    }
    _dirty = null;
    /* Camino completo: dibuja todas las capas. */
    dctx.clearRect(0, 0, state.width, state.height);
    for (let i = 0; i < state.layers.length; i++) {
        const layer = state.layers[i];
        if (!layer.visible) continue;
        const below = i > 0 ? state.layers[i - 1] : null;
        const shouldClip = layer.clipMask
            && layer.groupId != null
            && below
            && below.groupId === layer.groupId;
        const hasActiveBuffer = state.strokeBuffer && state.strokeBufferLayerIdx === i;
        if (!shouldClip && !hasActiveBuffer) {
            dctx.globalAlpha = layer.opacity;
            dctx.drawImage(layer.canvas, 0, 0);
            dctx.globalAlpha = 1;
            continue;
        }
        const oc = _getScratchCanvas();
        oc.drawImage(layer.canvas, 0, 0);
        if (hasActiveBuffer) {
            if (state.strokeIsEraser) {
                oc.globalCompositeOperation = 'destination-out';
                oc.drawImage(state.strokeBuffer, 0, 0);
                oc.globalCompositeOperation = 'source-over';
            } else {
                oc.drawImage(state.strokeBuffer, 0, 0);
            }
        }
        if (shouldClip) {
            oc.globalCompositeOperation = 'destination-in';
            oc.drawImage(below.canvas, 0, 0);
            oc.globalCompositeOperation = 'source-over';
        }
        dctx.globalAlpha = layer.opacity;
        dctx.drawImage(_scratchCanvas, 0, 0);
        dctx.globalAlpha = 1;
    }
}

let _compositeScheduled = false;
function composite() {
    if (_compositeScheduled) return;
    _compositeScheduled = true;
    requestAnimationFrame(() => {
        _compositeScheduled = false;
        _compositeNow();
    });
}
/* Para llamadas síncronas críticas (cierre de trazo, exports). */
function compositeSync() {
    _compositeScheduled = false;
    _compositeNow();
}

/* Dibuja la miniatura de una capa dentro de un <canvas> pequeño con
   letterbox. NO rellenamos fondo opaco: el checker CSS del .layer-thumb
   se ve a través de las zonas transparentes de la capa. */
function drawLayerThumb(thumbCv, layer) {
    if (!thumbCv) return;
    const tw = thumbCv.width, th = thumbCv.height;
    const tctx = thumbCv.getContext('2d');
    tctx.clearRect(0, 0, tw, th);
    const scale = Math.min(tw / layer.canvas.width, th / layer.canvas.height);
    const dw = layer.canvas.width * scale, dh = layer.canvas.height * scale;
    const ox = (tw - dw) / 2, oy = (th - dh) / 2;
    tctx.imageSmoothingEnabled = true;
    tctx.drawImage(layer.canvas, ox, oy, dw, dh);
}

/* Refresca solo la miniatura de la capa activa (lo barato — no rebuild
   completo del DOM). Lo llamamos al final de cada trazo. */
function updateActiveLayerThumb() {
    const item = document.querySelector('.layer-item[data-layer-idx="' + state.activeIdx + '"]');
    if (!item) return;
    drawLayerThumb(item.querySelector('.layer-thumb'), activeLayer());
}

function renderLayers() {
    /* Cualquier rebuild de la lista de capas implica que el stack ha
       cambiado (add/remove/reorder/activa). El cache below/above debe
       reconstruirse en el próximo composite durante stroke. */
    _invalidateStrokeCache();
    const list = $('layers-list');
    list.innerHTML = '';
    /* Iteración en reverso para que la capa con índice más alto (la
       más nueva / encima en z-order) salga la primera en el DOM, y
       por tanto la primera arriba del panel. La capa de Fondo
       (índice 0) queda la última, abajo. */
    let prevGroupId = undefined;  /* undefined = primera iteración */
    for (let arrIdx = state.layers.length - 1; arrIdx >= 0; arrIdx--) {
        const layer = state.layers[arrIdx];
        const idx = arrIdx;
        /* Si entramos a un grupo distinto al anterior (en orden DOM) y
           es un grupo no-nulo, renderiza la cabecera del grupo. */
        if (layer.groupId !== prevGroupId && layer.groupId != null) {
            const group = findGroup(layer.groupId);
            if (group) list.appendChild(buildGroupHeader(group));
        }
        prevGroupId = layer.groupId;
        const item = document.createElement('div');
        item.className = 'layer-item' + (idx === state.activeIdx ? ' active' : '')
                       + (layer.groupId != null ? ' in-group' : '');
        item.dataset.layerIdx = idx;
        const clipBtnHtml = (layer.groupId != null)
            ? '<button class="layer-clip' + (layer.clipMask ? ' active' : '')
                + '" title="Máscara alfa: recortar a capa de abajo">α</button>'
            : '';
        item.innerHTML =
            '<input type="checkbox"' + (layer.visible ? ' checked' : '') + '>' +
            '<canvas class="layer-thumb" width="22" height="22"></canvas>' +
            clipBtnHtml +
            '<span class="layer-name">' + layer.name + '</span>' +
            '<input type="range" class="layer-opacity" min="0" max="100" value="' + Math.round(layer.opacity * 100) + '">';
        item.querySelector('input[type=checkbox]').addEventListener('change', e => {
            layer.visible = e.target.checked; _invalidateStrokeCache(); composite();
        });
        item.querySelector('.layer-opacity').addEventListener('input', e => {
            layer.opacity = +e.target.value / 100; _invalidateStrokeCache(); composite();
        });
        item.querySelector('.layer-name').addEventListener('dblclick', async () => {
            const nm = await winPrompt({
                title: 'Renombrar capa',
                message: 'Nuevo nombre:',
                defaultValue: layer.name,
            });
            if (nm) { layer.name = nm; markDirty(); renderLayers(); }
        });
        const clipBtn = item.querySelector('.layer-clip');
        if (clipBtn) {
            clipBtn.addEventListener('click', e => {
                e.stopPropagation();
                toggleLayerClipMask(idx, !layer.clipMask);
                clipBtn.classList.toggle('active', layer.clipMask);
            });
        }
        attachLayerDrag(item, idx);
        list.appendChild(item);
        drawLayerThumb(item.querySelector('.layer-thumb'), layer);
    }
}

/* Header de un grupo: nombre + botón eliminar. La máscara de recorte
   ahora se activa por capa (cada layer-item del grupo tiene su botón). */
function buildGroupHeader(group) {
    const header = document.createElement('div');
    header.className = 'group-header';
    header.dataset.groupId = group.id;
    header.innerHTML =
        '<span class="group-name">' + group.name + '</span>' +
        '<button class="group-del" title="Eliminar grupo">×</button>';
    header.querySelector('.group-del').addEventListener('click', e => {
        e.stopPropagation();
        deleteGroup(group.id);
    });
    header.querySelector('.group-name').addEventListener('dblclick', async () => {
        const nm = await winPrompt({
            title: 'Renombrar grupo',
            message: 'Nuevo nombre:',
            defaultValue: group.name,
        });
        if (nm) { group.name = nm; markDirty(); renderLayers(); }
    });
    return header;
}

/* Menú contextual de click derecho sobre un layer-item. Se construye
   on-demand con las opciones disponibles según el estado de la capa. */
function showLayerContextMenu(idx, clientX, clientY) {
    document.querySelectorAll('.layer-ctx-menu').forEach(el => el.remove());
    const menu = document.createElement('div');
    menu.className = 'popup-menu open layer-ctx-menu';
    const layer = state.layers[idx];
    const opts = [];
    if (layer.groupId == null) {
        opts.push({ label: 'Crear grupo', action: () => createGroupForLayer(idx) });
    } else {
        opts.push({ label: 'Sacar del grupo', action: () => removeLayerFromGroup(idx) });
    }
    for (const o of opts) {
        const it = document.createElement('div');
        it.className = 'popup-item';
        it.textContent = o.label;
        it.addEventListener('click', () => { menu.remove(); o.action(); });
        menu.appendChild(it);
    }
    document.body.appendChild(menu);
    menu.style.left = clientX + 'px';
    menu.style.top  = clientY + 'px';
    /* Cierra al primer click/contextmenu fuera del menú. */
    const close = ev => {
        if (!menu.contains(ev.target)) {
            menu.remove();
            document.removeEventListener('click',       close, true);
            document.removeEventListener('contextmenu', close, true);
        }
    };
    setTimeout(() => {
        document.addEventListener('click',       close, true);
        document.addEventListener('contextmenu', close, true);
    }, 0);
}

/* Click + drag unificados sobre un layer-item:
   - Si el pointer no se mueve > 5px → click → selecciona la capa.
   - Si se mueve más → entra en modo drag, marca con una línea fina
     la posición donde caerá (arriba o abajo del item bajo el cursor),
     y al soltar reordena el array. */
function attachLayerDrag(item, idx) {
    /* Click derecho → context menu. */
    item.addEventListener('contextmenu', e => {
        e.preventDefault();
        showLayerContextMenu(idx, e.clientX, e.clientY);
    });
    item.addEventListener('pointerdown', e => {
        /* Solo botón izquierdo. */
        if (e.button !== 0) return;
        /* Inputs (checkbox / opacity slider) y botones (clip alfa) —
           no nos metemos para que reciban su click nativo. */
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
        e.preventDefault();
        const startX = e.clientX, startY = e.clientY;
        let moved = false;

        /* Posibles drop targets:
           - 'layer': una capa concreta — el dragged adopta el groupId
              del target y se reordena arriba/abajo.
           - 'header': la cabecera de un grupo — el dragged se une al
              grupo y queda como capa más alta de él. */
        function dropTargetAt(clientX, clientY) {
            const tgt = document.elementFromPoint(clientX, clientY);
            if (!tgt || !tgt.closest) return null;
            const layerEl = tgt.closest('.layer-item');
            if (layerEl && layerEl !== item) {
                const r = layerEl.getBoundingClientRect();
                const side = (clientY < r.top + r.height / 2) ? 'above' : 'below';
                return { type: 'layer', el: layerEl, side };
            }
            const headerEl = tgt.closest('.group-header');
            if (headerEl) return { type: 'header', el: headerEl };
            return null;
        }
        function clearDropMarkers() {
            document.querySelectorAll('.layer-item.drop-above, .layer-item.drop-below')
                    .forEach(el => el.classList.remove('drop-above', 'drop-below'));
            document.querySelectorAll('.group-header.drop-into')
                    .forEach(el => el.classList.remove('drop-into'));
        }

        const onMove = ev => {
            if (!moved) {
                if (Math.hypot(ev.clientX - startX, ev.clientY - startY) <= 5) return;
                moved = true;
                item.classList.add('dragging');
            }
            clearDropMarkers();
            const dt = dropTargetAt(ev.clientX, ev.clientY);
            if (!dt) return;
            if (dt.type === 'layer')  dt.el.classList.add('drop-' + dt.side);
            else                       dt.el.classList.add('drop-into');
        };
        const onUp = ev => {
            document.removeEventListener('pointermove', onMove);
            document.removeEventListener('pointerup',   onUp);
            if (!moved) {
                state.activeIdx = idx;
                renderLayers();
                return;
            }
            item.classList.remove('dragging');
            const dt = dropTargetAt(ev.clientX, ev.clientY);
            clearDropMarkers();
            if (!dt) return;

            snapshotForUndo({ kind: 'layer-op' });
            const movingLayer = state.layers[idx];
            const activeLayerRef = state.layers[state.activeIdx];

            if (dt.type === 'layer') {
                const toIdx = +dt.el.dataset.layerIdx;
                if (toIdx === idx) return;
                const targetLayer = state.layers[toIdx];
                state.layers.splice(idx, 1);
                const newToIdx = state.layers.indexOf(targetLayer);
                const insertAt = (dt.side === 'above') ? newToIdx + 1 : newToIdx;
                /* El dragged adopta el grupo del target — si target es
                   suelta (groupId=null), también queda suelta. */
                movingLayer.groupId = targetLayer.groupId;
                state.layers.splice(insertAt, 0, movingLayer);
            } else {
                /* Drop sobre header de grupo → meterla al grupo, colocarla
                   como la capa más arriba (mayor idx) del grupo. */
                const gid = +dt.el.dataset.groupId;
                state.layers.splice(idx, 1);
                movingLayer.groupId = gid;
                /* Busca el último índice (más alto) ocupado por el grupo
                   tras la extracción. Insertamos justo por encima. */
                let insertAt = 0;
                for (let i = state.layers.length - 1; i >= 0; i--) {
                    if (state.layers[i].groupId === gid) { insertAt = i + 1; break; }
                }
                state.layers.splice(insertAt, 0, movingLayer);
            }
            state.activeIdx = state.layers.indexOf(activeLayerRef);
            pruneEmptyGroups();
            renderLayers();
            composite();
        };
        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup',   onUp);
    });
}

/* ════════════════════════════════════════════════════════════════════
   winDialog — modal Win98 reusable (sustituye alert/confirm nativos)
   Devuelve Promise<index> con el índice del botón pulsado, o -1 si
   se cierra con la X o clickando el backdrop.
   ════════════════════════════════════════════════════════════════════ */
/* Variante con campo de texto. Devuelve Promise<string|null> — el
   string introducido o null si se cancela. */
function winPrompt(opts) {
    return new Promise(resolve => {
        const bd = document.createElement('div');
        bd.className = 'dialog-bd open';
        bd.innerHTML =
            '<div class="window dialog">' +
                '<div class="title-bar">' +
                    '<div class="title-bar-text">' + (opts.title || '') + '</div>' +
                    '<div class="title-bar-controls"><button aria-label="Close"></button></div>' +
                '</div>' +
                '<div class="window-body">' +
                    '<p style="margin:4px 4px 10px;">' + (opts.message || '') + '</p>' +
                    '<input type="text" id="_winprompt-input" style="width:100%;box-sizing:border-box;" value="' +
                        ((opts.defaultValue || '') + '').replace(/"/g, '&quot;') + '">' +
                    '<section class="field-row" style="justify-content:flex-end;gap:6px;margin-top:14px;">' +
                        '<button data-act="cancel">Cancelar</button>' +
                        '<button class="default" data-act="ok">Aceptar</button>' +
                    '</section>' +
                '</div>' +
            '</div>';
        document.body.appendChild(bd);
        const input = bd.querySelector('#_winprompt-input');
        const close = v => { bd.remove(); resolve(v); };
        bd.querySelector('[data-act="ok"]').addEventListener('click', () => close(input.value));
        bd.querySelector('[data-act="cancel"]').addEventListener('click', () => close(null));
        bd.querySelector('button[aria-label="Close"]').addEventListener('click', () => close(null));
        bd.addEventListener('click', e => { if (e.target === bd) close(null); });
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter')  { e.preventDefault(); close(input.value); }
            if (e.key === 'Escape') { e.preventDefault(); close(null); }
        });
        input.focus();
        input.select();
    });
}

function winDialog(opts) {
    return new Promise(resolve => {
        const bd = document.createElement('div');
        bd.className = 'dialog-bd open';
        const btns = (opts.buttons || [{label:'Aceptar'}]).map((b, i) =>
            '<button data-idx="' + i + '"' + (b.default ? ' class="default"' : '') + '>'
            + b.label + '</button>'
        ).join('');
        bd.innerHTML =
            '<div class="window dialog">' +
                '<div class="title-bar">' +
                    '<div class="title-bar-text">' + (opts.title || '') + '</div>' +
                    '<div class="title-bar-controls"><button aria-label="Close"></button></div>' +
                '</div>' +
                '<div class="window-body">' +
                    '<p style="margin:4px 4px 14px;">' + (opts.message || '') + '</p>' +
                    '<section class="field-row" style="justify-content:flex-end;gap:6px;">' +
                        btns +
                    '</section>' +
                '</div>' +
            '</div>';
        document.body.appendChild(bd);
        const close = v => { bd.remove(); resolve(v); };
        bd.querySelectorAll('button[data-idx]').forEach(b =>
            b.addEventListener('click', () => close(+b.dataset.idx)));
        bd.querySelector('button[aria-label="Close"]')
          .addEventListener('click', () => close(-1));
        bd.addEventListener('click', e => { if (e.target === bd) close(-1); });
        /* Foco al botón default para Enter directo. */
        const def = bd.querySelector('button.default') || bd.querySelector('button[data-idx]');
        if (def) def.focus();
    });
}

$('btn-layer-add').addEventListener('click', () => {
    snapshotForUndo({ kind: 'layer-op' });
    const layer = makeLayer('Capa ' + state.layers.length);
    state.layers.splice(state.activeIdx + 1, 0, layer);
    state.activeIdx++;
    renderLayers(); composite();
});
$('btn-layer-del').addEventListener('click', async () => {
    if (state.layers.length <= 1) {
        await winDialog({
            title: 'No se puede borrar',
            message: 'Tiene que quedar al menos una capa.',
            buttons: [{label:'Aceptar', default:true}],
        });
        return;
    }
    const choice = await winDialog({
        title: 'Borrar capa',
        message: 'Borrar la capa "' + activeLayer().name + '"? Esta acción se puede deshacer con Ctrl+Z.',
        buttons: [
            {label:'Cancelar'},
            {label:'Borrar', default:true},
        ],
    });
    if (choice !== 1) return;
    snapshotForUndo({ kind: 'layer-op' });
    state.layers.splice(state.activeIdx, 1);
    if (state.activeIdx >= state.layers.length) state.activeIdx = state.layers.length - 1;
    pruneEmptyGroups();
    renderLayers(); composite();
});

/* ════════════════════════════════════════════════════════════════════
   UNDO / REDO
   ════════════════════════════════════════════════════════════════════ */
function snapshotLayer(layer) {
    const off = document.createElement('canvas');
    off.width = layer.canvas.width; off.height = layer.canvas.height;
    off.getContext('2d').drawImage(layer.canvas, 0, 0);
    return off;
}
function snapshotForUndo(meta) {
    state.redoStack = [];
    /* Cualquier operación que justifique un snapshot también justifica
       marcar el lienzo como modificado. */
    if (!state.isDirty) markDirty();
    if (meta && meta.kind === 'layer-op') {
        state.undoStack.push({
            kind: 'layer-op',
            layers: state.layers.map(l => ({ name: l.name, id: l.id, visible: l.visible,
                                              opacity: l.opacity, groupId: l.groupId,
                                              clipMask: l.clipMask,
                                              snap: snapshotLayer(l) })),
            activeIdx: state.activeIdx,
            groups: state.groups.map(g => ({ ...g })),
        });
    } else {
        state.undoStack.push({ kind: 'stroke', layerIdx: state.activeIdx,
                               snap: snapshotLayer(activeLayer()) });
    }
    while (state.undoStack.length > CFG.UNDO_MAX) state.undoStack.shift();
}
function applyUndoEntry(entry, toRedo) {
    if (entry.kind === 'stroke') {
        const layer = state.layers[entry.layerIdx];
        const curSnap = snapshotLayer(layer);
        layer.ctx.clearRect(0, 0, layer.canvas.width, layer.canvas.height);
        layer.ctx.drawImage(entry.snap, 0, 0);
        toRedo.push({ kind: 'stroke', layerIdx: entry.layerIdx, snap: curSnap });
    } else if (entry.kind === 'layer-op') {
        const curLayers = state.layers.map(l => ({ name: l.name, id: l.id, visible: l.visible,
                                                    opacity: l.opacity, groupId: l.groupId,
                                                    clipMask: l.clipMask,
                                                    snap: snapshotLayer(l) }));
        const curActive = state.activeIdx;
        const curGroups = state.groups.map(g => ({ ...g }));
        state.layers = entry.layers.map(L => {
            const layer = makeLayer(L.name);
            layer.id = L.id; layer.visible = L.visible; layer.opacity = L.opacity;
            layer.groupId = L.groupId;
            layer.clipMask = L.clipMask;
            layer.ctx.drawImage(L.snap, 0, 0);
            return layer;
        });
        state.activeIdx = entry.activeIdx;
        state.groups = entry.groups.map(g => ({ ...g }));
        toRedo.push({ kind: 'layer-op', layers: curLayers, activeIdx: curActive, groups: curGroups });
        renderLayers();
    }
    _invalidateStrokeCache();
    composite();
}
$('btn-undo').addEventListener('click', () => {
    const e = state.undoStack.pop(); if (!e) return;
    applyUndoEntry(e, state.redoStack);
});
$('btn-redo').addEventListener('click', () => {
    const e = state.redoStack.pop(); if (!e) return;
    applyUndoEntry(e, state.undoStack);
});

/* ════════════════════════════════════════════════════════════════════
   COORDS + BRUSH RENDERING
   ════════════════════════════════════════════════════════════════════ */
function eventToCanvas(ev) {
    const r = display.getBoundingClientRect();
    return {
        x: (ev.clientX - r.left) * (state.width  / r.width),
        y: (ev.clientY - r.top)  * (state.height / r.height),
        p: (ev.pointerType === 'mouse' && ev.pressure === 0.5)
              ? CFG.FAKE_PRESSURE : (ev.pressure || CFG.FAKE_PRESSURE),
    };
}
function pressureToWidth(p) {
    const adj = Math.pow(Math.max(0, Math.min(1, p)), 1.5);
    return state.size * (0.10 + 0.90 * adj);
}
/* ── Dab template cache ──
   Crear createRadialGradient + arc + fill por cada dab era el coste
   dominante con airbrush/chalk/round (50+ dabs por anchura). Cacheamos
   un canvas pequeño con el dab "tipo" pre-pintado por (brush.hardEdge,
   color) y lo blitteamos con drawImage redimensionando — el navegador
   acelera por HW. Para hardEdge un círculo plano; para soft un radial.
   La opacidad por dab sigue aplicándose con globalAlpha, así el efecto
   de presión y flow se conserva.

   No se aplica al blender (color dinámico por dab) ni a sample con
   selection rotation activa (necesitaría rotar también el template);
   en esos casos caemos al render directo. */
const DAB_TEMPLATE_SIZE = 128;
const _dabTemplates = new Map(); /* key → HTMLCanvasElement */
const _DAB_TEMPLATE_CAP = 16;
function _getDabTemplate(brush, color) {
    const key = (brush.hardEdge ? 'h:' : 's:') + color;
    let tpl = _dabTemplates.get(key);
    if (tpl) return tpl;
    if (_dabTemplates.size >= _DAB_TEMPLATE_CAP) {
        /* LRU naive: borra el primero (el más antiguo en orden de
           inserción de Map). */
        const firstKey = _dabTemplates.keys().next().value;
        _dabTemplates.delete(firstKey);
    }
    const s = DAB_TEMPLATE_SIZE;
    tpl = document.createElement('canvas');
    tpl.width = s; tpl.height = s;
    const tctx = tpl.getContext('2d');
    const c = s / 2;
    const r = c - 2;
    if (brush.hardEdge) {
        tctx.fillStyle = color;
        tctx.beginPath(); tctx.arc(c, c, r, 0, Math.PI * 2); tctx.fill();
    } else {
        const g = tctx.createRadialGradient(c, c, 0, c, c, r);
        g.addColorStop(0,    color);
        g.addColorStop(0.55, color);
        g.addColorStop(1,    color + '00');
        tctx.fillStyle = g;
        tctx.beginPath(); tctx.arc(c, c, r, 0, Math.PI * 2); tctx.fill();
    }
    _dabTemplates.set(key, tpl);
    return tpl;
}
function _clearDabTemplates() { _dabTemplates.clear(); }

function stampDab(ctx, x, y, pressure, color, brush, isEraser, sampleCtx) {
    const w = pressureToWidth(pressure);
    if (w < 0.5) return;
    const r = w / 2;
    if (brush.scatter > 0) {
        x += (Math.random() - 0.5) * w * brush.scatter;
        y += (Math.random() - 0.5) * w * brush.scatter;
    }
    /* Brush blender (smudge tipo Photoshop): cada dab toma el color
       "arrastrado" del dab anterior (state.blendPrev), lo mezcla con
       el color sampleado en la posición actual, y le inyecta un poco
       del color del usuario. El resultado se estampa Y se guarda como
       blendPrev → así los colores se embarran a lo largo del trazo. */
    if (brush.blender && !isEraser) {
        const src = sampleCtx || dctx;
        /* Samplea un punto del lienzo composited. */
        const ix = Math.max(0, Math.min(src.canvas.width  - 1, x | 0));
        const iy = Math.max(0, Math.min(src.canvas.height - 1, y | 0));
        const px = src.getImageData(ix, iy, 1, 1).data;
        const here = (px[3] === 0)
            ? null
            : { r: px[0], g: px[1], b: px[2] };
        /* Si no hay color en este punto ni arrastrado del anterior,
           no hay nada que blendear. */
        if (!here && !state.blendPrev) return;
        /* Mezcla arrastrado ← punto actual. dragWeight alto = casi todo
           el peso al arrastrado, así el color recorrido domina el dab. */
        const drag = brush.dragWeight || 0.85;
        let r0, g0, b0;
        if (state.blendPrev && here) {
            r0 = state.blendPrev.r * drag + here.r * (1 - drag);
            g0 = state.blendPrev.g * drag + here.g * (1 - drag);
            b0 = state.blendPrev.b * drag + here.b * (1 - drag);
        } else if (state.blendPrev) {
            r0 = state.blendPrev.r; g0 = state.blendPrev.g; b0 = state.blendPrev.b;
        } else {
            r0 = here.r; g0 = here.g; b0 = here.b;
        }
        /* Pequeña inyección del color del usuario controlada por flow
           * pressure * brushOpacity. */
        const ur = parseInt(color.slice(1, 3), 16);
        const ug = parseInt(color.slice(3, 5), 16);
        const ub = parseInt(color.slice(5, 7), 16);
        const wUser = Math.pow(pressure, brush.alphaCurve) * brush.flow * state.brushOpacity;
        const br = (ur * wUser + r0 * (1 - wUser)) | 0;
        const bg = (ug * wUser + g0 * (1 - wUser)) | 0;
        const bb = (ub * wUser + b0 * (1 - wUser)) | 0;
        /* Estampa con gradiente radial soft (hardEdge:false) para
           bordes suaves; la opacidad sigue al brushOpacity. */
        ctx.globalCompositeOperation = 'source-over';
        ctx.globalAlpha = state.brushOpacity;
        if (brush.hardEdge) {
            ctx.fillStyle = 'rgb(' + br + ',' + bg + ',' + bb + ')';
        } else {
            const rgba = 'rgba(' + br + ',' + bg + ',' + bb;
            const gx = ctx.createRadialGradient(x, y, 0, x, y, r);
            gx.addColorStop(0,    rgba + ',1)');
            gx.addColorStop(0.55, rgba + ',1)');
            gx.addColorStop(1,    rgba + ',0)');
            ctx.fillStyle = gx;
        }
        ctx.beginPath(); ctx.arc(x, y, r, 0, Math.PI * 2); ctx.fill();
        ctx.globalAlpha = 1;
        /* Guarda el color para que el próximo dab arrastre desde aquí. */
        state.blendPrev = { r: br, g: bg, b: bb };
        return;
    }
    ctx.globalCompositeOperation = isEraser ? 'destination-out' : 'source-over';
    ctx.globalAlpha = Math.pow(pressure, brush.alphaCurve) * brush.flow * state.brushOpacity;
    /* drawImage(template) en lugar de createRadialGradient+arc+fill por
       dab — el navegador lo acelera por HW y evitamos N allocations de
       gradient por trazo. Template cacheado por (hardEdge, color). */
    const tpl = _getDabTemplate(brush, color);
    ctx.drawImage(tpl, x - r, y - r, w, w);
    ctx.globalAlpha = 1;
}
function paintSegment(ctx, from, to, color, brush, isEraser, sampleCtx) {
    const dx = to.x - from.x, dy = to.y - from.y;
    const dist = Math.hypot(dx, dy);
    const wMid = pressureToWidth((from.p + to.p) / 2);
    const step = Math.max(1, wMid * brush.spacing);
    const n = Math.max(1, Math.floor(dist / step));
    for (let i = 1; i <= n; i++) {
        const t = i / n;
        stampDab(ctx, from.x + dx*t, from.y + dy*t,
                 from.p + (to.p - from.p) * t, color, brush, isEraser, sampleCtx);
    }
}
function stabilize(pt) {
    if (state.smooth === 0) return pt;
    state.stab.push(pt);
    if (state.stab.length > CFG.STABILIZER_MAX) state.stab.shift();
    const n = Math.min(state.stab.length, state.smooth);
    /* Iteramos sobre el slice in-place — evitamos crear un array nuevo
       por sample. Con muchos samples por frame el slice + GC sumaba. */
    const start = state.stab.length - n;
    let x = 0, y = 0, p = 0;
    for (let i = start; i < state.stab.length; i++) {
        const s = state.stab[i];
        x += s.x; y += s.y; p += s.p;
    }
    return { x: x / n, y: y / n, p: p / n };
}
function applyStrokeSample(rawPt, isLocal) {
    const pt = isLocal ? stabilize(rawPt) : rawPt;
    /* Si hay strokeBuffer pintamos AHÍ; sino directo en la capa.
       En el buffer SIEMPRE pintamos como "positivo" (source-over)
       aunque sea eraser — el destination-out final lo hace composite()
       y el commit en endPointer. */
    const usingBuffer = state.strokeBufferCtx != null;
    const ctx = usingBuffer ? state.strokeBufferCtx : activeCtx();
    const isEraser = state.tool === 'eraser';
    const isEraserForStamp = usingBuffer ? false : isEraser;
    const brush = BRUSHES[isEraser ? 'round' : state.brushName];
    if (state.selection) {
        ctx.save();
        const s = state.selection;
        const rot = state.selectionRotation || 0;
        if (rot !== 0) {
            const cx = s.x + s.w / 2, cy = s.y + s.h / 2;
            ctx.translate(cx, cy);
            ctx.rotate(rot);
            ctx.translate(-cx, -cy);
            ctx.beginPath();
            ctx.rect(s.x, s.y, s.w, s.h);
            ctx.clip();
            ctx.setTransform(1, 0, 0, 1, 0, 0);
        } else {
            ctx.beginPath();
            ctx.rect(s.x, s.y, s.w, s.h);
            ctx.clip();
        }
    }
    if (!state.lastDab) {
        stampDab(ctx, pt.x, pt.y, pt.p, state.color, brush, isEraserForStamp);
        _markDabDirty(pt.x, pt.y, pressureToWidth(pt.p));
    } else {
        paintSegment(ctx, state.lastDab, pt, state.color, brush, isEraserForStamp);
        const maxP = Math.max(state.lastDab.p, pt.p);
        _markSegmentDirty(state.lastDab, pt, pressureToWidth(maxP));
    }
    if (state.selection) ctx.restore();
    state.lastDab = pt;
    composite();
    if (isLocal) {
        $('pos').textContent = 'x: ' + (pt.x|0) + ', y: ' + (pt.y|0);
        $('pressure').textContent = 'p: ' + pt.p.toFixed(2);
        state.pendingSamples.push({ x: pt.x, y: pt.y, p: pt.p });
        scheduleFlush();
    }
}

/* ════════════════════════════════════════════════════════════════════
   TOOLS — pointer events sobre el display
   ════════════════════════════════════════════════════════════════════ */
function pickColorAt(x, y) {
    if (x < 0 || y < 0 || x >= state.width || y >= state.height) return null;
    /* Si hay un composite programado vía rAF, el display puede estar
       stale para el cuentagotas. Forzamos un compositeSync para leer
       los píxeles actuales sin esperar al próximo frame. */
    if (_compositeScheduled) compositeSync();
    const px = dctx.getImageData(x|0, y|0, 1, 1).data;
    if (px[3] === 0) return null;
    return '#' + [0,1,2].map(i => px[i].toString(16).padStart(2, '0')).join('');
}

display.addEventListener('pointerdown', e => {
    if (e.button !== undefined && e.button !== 0) return;
    if (state.panKey) return;
    display.setPointerCapture(e.pointerId);
    state.pid = e.pointerId;
    const pt = eventToCanvas(e);
    $('type').textContent = e.pointerType;
    if (state.tool === 'eyedrop') {
        const c = pickColorAt(pt.x, pt.y);
        if (c) setColor(c);
        return;
    }
    if (state.tool === 'select') {
        state.selecting = true;
        state.selStart = pt;
        state.selection = null;
        $('selection-overlay').style.display = 'none';
        const _sa = $('selection-actions');
        if (_sa) _sa.classList.remove('visible');
        return;
    }
    if (state.tool === 'text') {
        startTextInput(pt.x, pt.y, e.clientX, e.clientY);
        return;
    }
    snapshotForUndo();
    state.drawing = true;
    state.lastDab = null;
    state.stab = [];
    /* Reset del color arrastrado del blender — cada trazo empieza
       limpio sin "memoria" del trazo anterior. */
    state.blendPrev = null;
    state.strokeId = 's_' + Date.now().toString(36) + Math.random().toString(36).slice(2,5);
    const tool = (e.pointerType === 'eraser') ? 'eraser' : state.tool;
    /* Si la capa activa tiene clipMask con base válida, prepara el
       buffer offscreen para todo este trazo. */
    const baseIdx = clipBaseIdxFor(state.activeIdx);
    if (baseIdx >= 0) {
        const buf = document.createElement('canvas');
        buf.width = state.width; buf.height = state.height;
        state.strokeBuffer = buf;
        state.strokeBufferCtx = buf.getContext('2d', { willReadFrequently: true });
        state.strokeIsEraser = (tool === 'eraser');
        state.strokeBufferLayerIdx = state.activeIdx;
        state.strokeBufferBelowIdx = baseIdx;
    }
    netSend({ type:'stroke-begin', strokeId: state.strokeId, tool,
              color: state.color, size: state.size, brush: state.brushName,
              layerIdx: state.activeIdx });
    applyStrokeSample(pt, true);
});

display.addEventListener('pointermove', e => {
    const pt = eventToCanvas(e);
    $('pos').textContent = 'x: ' + (pt.x|0) + ', y: ' + (pt.y|0);
    $('pressure').textContent = 'p: ' + (e.pressure || 0).toFixed(2);
    if (state.selecting && e.pointerId === state.pid) {
        updateSelection(state.selStart, pt); return;
    }
    if (!state.drawing || e.pointerId !== state.pid) return;
    const samples = e.getCoalescedEvents ? e.getCoalescedEvents() : [e];
    for (const s of samples) applyStrokeSample(eventToCanvas(s), true);
});

function endPointer(e) {
    if (e && e.pointerId !== state.pid) return;
    try { display.releasePointerCapture(state.pid); } catch (_) {}
    state.pid = -1;
    if (state.selecting) {
        state.selecting = false;
        const sel = state.selection;
        if (sel && (sel.w < 4 || sel.h < 4)) {
            state.selection = null;
            $('selection-overlay').style.display = 'none';
            const _sa = $('selection-actions');
            if (_sa) _sa.classList.remove('visible');
        }
        return;
    }
    if (!state.drawing) return;
    state.drawing = false;
    flushNow();
    /* Si hubo strokeBuffer, masca con la capa base + vuelca a la capa
       (drawImage para paint, destination-out para eraser). */
    if (state.strokeBuffer) {
        const layer = state.layers[state.strokeBufferLayerIdx];
        const base  = state.layers[state.strokeBufferBelowIdx];
        if (layer && base) {
            const bctx = state.strokeBufferCtx;
            /* Recorta el buffer a la silueta de la base — solo
               sobreviven los píxeles del buffer donde la base tiene
               alfa, es decir, donde el usuario tiene "permiso" para
               pintar. */
            bctx.globalCompositeOperation = 'destination-in';
            bctx.drawImage(base.canvas, 0, 0);
            bctx.globalCompositeOperation = 'source-over';
            /* Vuelca a la capa. */
            const layerCtx = layer.ctx;
            if (state.strokeIsEraser) {
                layerCtx.globalCompositeOperation = 'destination-out';
                layerCtx.drawImage(state.strokeBuffer, 0, 0);
                layerCtx.globalCompositeOperation = 'source-over';
            } else {
                layerCtx.drawImage(state.strokeBuffer, 0, 0);
            }
        }
        state.strokeBuffer = null;
        state.strokeBufferCtx = null;
        state.strokeBufferLayerIdx = -1;
        state.strokeBufferBelowIdx = -1;
        state.strokeIsEraser = false;
    }
    netSend({ type: 'stroke-end', strokeId: state.strokeId });
    state.strokeId = null;
    state.lastDab = null;
    /* Cache obsoleto: la capa activa cambió (volcamos el buffer en
       ella). Próximo stroke reconstruye. */
    _invalidateStrokeCache();
    /* Síncrono: el thumb update lee de layer.canvas (no afecta) pero
       queremos el display ya pintado por si export sigue inmediatamente. */
    compositeSync();
    updateActiveLayerThumb();
}
display.addEventListener('pointerup',     endPointer);
display.addEventListener('pointercancel', endPointer);

/* ════════════════════════════════════════════════════════════════════
   TRANSFORM — Shift+T levanta los píxeles dentro de la selección de la
   capa activa a un buffer flotante. Se puede mover, escalar y rotar
   (con los handles del overlay) y al confirmar (Enter) se pintan de
   vuelta sobre la misma capa con la nueva geometría. Esc cancela y
   restaura el snapshot original.
   ════════════════════════════════════════════════════════════════════ */
function startTransform() {
    if (!state.selection || state.transforming) return;
    const s = state.selection;
    if (s.w < 1 || s.h < 1) return;

    /* Snapshot para undo (operación de capa). */
    snapshotForUndo();

    const layer = activeLayer();
    /* Snapshot completo de la capa para Esc/cancel. */
    const layerBackup = snapshotLayer(layer);

    /* Copia los píxeles seleccionados a un canvas flotante del mismo
       tamaño que la selección. */
    const floatCv = document.createElement('canvas');
    floatCv.width  = Math.max(1, Math.round(s.w));
    floatCv.height = Math.max(1, Math.round(s.h));
    floatCv.getContext('2d').drawImage(
        layer.canvas,
        s.x, s.y, s.w, s.h,
        0, 0, floatCv.width, floatCv.height
    );

    /* Limpia el área original en la capa — esos píxeles ahora son
       "flotantes" y se renderizan vía background-image del overlay. */
    layer.ctx.clearRect(s.x, s.y, s.w, s.h);

    state.transforming = true;
    state.transformInitial = {
        sel: { ...s },
        rot: state.selectionRotation,
        layerIdx: state.activeIdx,
        layerBackup,
    };
    state.transformFloat = floatCv;

    composite();
    updateActiveLayerThumb();
    $('selection-overlay').classList.add('transforming');
    renderTransformHandles();
    renderFloatingPreview();
}

/* Muestra el buffer flotante como background-image del overlay. Se
   estira con backgroundSize:100% 100% para que resize cambie su tamaño,
   y al CSS transform:rotate() del overlay le sigue de regalo la
   rotación visual. */
function renderFloatingPreview() {
    const ov = $('selection-overlay');
    if (!state.transformFloat) {
        ov.style.backgroundImage = '';
        return;
    }
    ov.style.backgroundImage = 'url(' + state.transformFloat.toDataURL() + ')';
    ov.style.backgroundSize = '100% 100%';
    ov.style.backgroundRepeat = 'no-repeat';
}

function commitTransform() {
    if (!state.transforming) return;
    /* Pinta el flotante de vuelta sobre la capa con la geometría actual
       (posición, tamaño escalado y rotación). */
    if (state.transformFloat && state.transformInitial) {
        const layer = state.layers[state.transformInitial.layerIdx];
        if (layer) {
            const s = state.selection;
            const rot = state.selectionRotation || 0;
            const ctx = layer.ctx;
            ctx.save();
            if (rot !== 0) {
                const cx = s.x + s.w / 2, cy = s.y + s.h / 2;
                ctx.translate(cx, cy);
                ctx.rotate(rot);
                ctx.translate(-cx, -cy);
            }
            ctx.drawImage(state.transformFloat, s.x, s.y, s.w, s.h);
            ctx.restore();
        }
    }
    cleanupTransform();
    composite();
    updateActiveLayerThumb();
    /* Al confirmar la transform, la selección desaparece. */
    clearSelection();
}

function cancelTransform() {
    if (!state.transforming) return;
    /* Restaura la capa al snapshot pre-lift. */
    if (state.transformInitial) {
        const layer = state.layers[state.transformInitial.layerIdx];
        if (layer && state.transformInitial.layerBackup) {
            layer.ctx.clearRect(0, 0, layer.canvas.width, layer.canvas.height);
            layer.ctx.drawImage(state.transformInitial.layerBackup, 0, 0);
        }
    }
    cleanupTransform();
    composite();
    updateActiveLayerThumb();
    /* Al cancelar, también desaparece la selección. */
    clearSelection();
}

function cleanupTransform() {
    state.transforming = false;
    state.transformOp = null;
    state.transformStart = null;
    state.transformInitial = null;
    state.transformFloat = null;
    const ov = $('selection-overlay');
    ov.classList.remove('transforming');
    ov.style.backgroundImage = '';
    renderTransformHandles();
}

/* Quita la selección por completo: limpia el rectángulo, la rotación
   y oculta el overlay (resetea sus estilos inline). Llamado al
   confirmar/cancelar Transform y al cambiar de herramienta. */
function clearSelection() {
    state.selection = null;
    state.selectionRotation = 0;
    const ov = $('selection-overlay');
    ov.style.display = 'none';
    ov.style.backgroundImage = '';
    ov.style.transform = '';
    const selActs = $('selection-actions');
    if (selActs) selActs.classList.remove('visible');
}

/* Pointer events sobre el overlay durante transform. */
const selOverlay = $('selection-overlay');
selOverlay.addEventListener('pointerdown', e => {
    if (!state.transforming) return;
    e.stopPropagation();
    const op = e.target.classList.contains('tx-handle')
        ? e.target.dataset.op
        : 'move';
    state.transformOp = op;
    /* Coords en lienzo internas del centro de la selección antes del op. */
    const s = state.selection;
    state.transformStart = {
        clientX: e.clientX, clientY: e.clientY,
        sel: { ...s },
        rot: state.selectionRotation,
        cx: s.x + s.w / 2,
        cy: s.y + s.h / 2,
    };
    try { selOverlay.setPointerCapture(e.pointerId); } catch (_) {}
});
selOverlay.addEventListener('pointermove', e => {
    if (!state.transforming || !state.transformOp || !state.transformStart) return;
    e.stopPropagation();
    /* Delta en CSS px del wrap → coords lienzo (÷ zoom). */
    const dxCss = e.clientX - state.transformStart.clientX;
    const dyCss = e.clientY - state.transformStart.clientY;
    const dx = dxCss / state.zoom;
    const dy = dyCss / state.zoom;
    const op = state.transformOp;
    const ini = state.transformStart;
    let s = { ...ini.sel };
    if (op === 'move') {
        s.x = ini.sel.x + dx;
        s.y = ini.sel.y + dy;
    } else if (op === 'rot') {
        /* Ángulo entre el centro y el cursor, menos el ángulo inicial. */
        const ovRect = selOverlay.getBoundingClientRect();
        const cxCss = ovRect.left + ovRect.width  / 2;
        const cyCss = ovRect.top  + ovRect.height / 2;
        const aNow = Math.atan2(e.clientY - cyCss, e.clientX - cxCss);
        const aStart = Math.atan2(ini.clientY - cyCss, ini.clientX - cxCss);
        state.selectionRotation = ini.rot + (aNow - aStart);
    } else {
        /* Resize: cada handle modifica uno o dos bordes de la selección. */
        if (op.includes('w')) { s.x = ini.sel.x + dx; s.w = ini.sel.w - dx; }
        if (op.includes('e')) { s.w = ini.sel.w + dx; }
        if (op.includes('n')) { s.y = ini.sel.y + dy; s.h = ini.sel.h - dy; }
        if (op.includes('s')) { s.h = ini.sel.h + dy; }
        /* Mínimos para evitar selección colapsada. */
        if (s.w < 4) { s.x -= (4 - s.w); s.w = 4; }
        if (s.h < 4) { s.y -= (4 - s.h); s.h = 4; }
    }
    state.selection = s;
    refreshSelectionOverlay();
});
function endTransformOp(e) {
    if (!state.transformOp) return;
    state.transformOp = null;
    state.transformStart = null;
    try { selOverlay.releasePointerCapture(e.pointerId); } catch (_) {}
}
selOverlay.addEventListener('pointerup',     endTransformOp);
selOverlay.addEventListener('pointercancel', endTransformOp);

function updateSelection(from, to) {
    const x = Math.min(from.x, to.x), y = Math.min(from.y, to.y);
    const w = Math.abs(to.x - from.x), h = Math.abs(to.y - from.y);
    state.selection = { x, y, w, h };
    state.selectionRotation = 0;
    refreshSelectionOverlay();
}

/* Posiciona el overlay según la selección + zoom + rotación actual.
   Las coords de selection están en px internas del lienzo; el overlay
   vive dentro de #zoom-container que tiene CSS px = canvas-internal *
   zoom. Por eso multiplicamos por state.zoom para obtener CSS px. */
function refreshSelectionOverlay() {
    const ov = $('selection-overlay');
    const selActs = $('selection-actions');
    if (!state.selection) {
        ov.style.display = 'none';
        if (selActs) selActs.classList.remove('visible');
        return;
    }
    const z = state.zoom;
    const s = state.selection;
    ov.style.display = 'block';
    ov.style.left   = (s.x * z) + 'px';
    ov.style.top    = (s.y * z) + 'px';
    ov.style.width  = (s.w * z) + 'px';
    ov.style.height = (s.h * z) + 'px';
    ov.style.transform = 'rotate(' + state.selectionRotation + 'rad)';

    /* Botón "borrar selección" debajo del centro de la sel. */
    if (selActs) {
        selActs.classList.add('visible');
        selActs.style.left = (s.x * z + (s.w * z) / 2 - 12) + 'px';
        selActs.style.top  = (s.y * z + s.h * z + 6) + 'px';
    }
}

/* ════════════════════════════════════════════════════════════════════
   HERRAMIENTA DE TEXTO — al hacer click con el tool 'text' se abre un
   <input> flotante en la posición clicada. Enter pinta el texto en la
   capa activa con el color y tamaño actual. Esc/blur cancela.
   ════════════════════════════════════════════════════════════════════ */
function startTextInput(canvasX, canvasY, clientX, clientY) {
    /* Tamaño tipográfico = state.size * 2 → escala con el slider de grosor. */
    const fontPx = Math.max(8, state.size * 2);
    const fontFamily = 'Arial, sans-serif';
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'text-input-floating';
    input.style.left = (clientX - 4) + 'px';
    input.style.top  = (clientY - fontPx * state.zoom / 2) + 'px';
    input.style.fontSize = (fontPx * state.zoom) + 'px';
    input.style.color = state.color;
    input.style.fontFamily = fontFamily;
    input.style.minWidth = '60px';
    document.body.appendChild(input);
    setTimeout(() => input.focus(), 0);

    let done = false;
    function commit() {
        if (done) return; done = true;
        const text = input.value;
        input.remove();
        if (!text) return;
        snapshotForUndo();
        const ctx = activeCtx();
        ctx.save();
        ctx.font = fontPx + 'px ' + fontFamily;
        ctx.fillStyle = state.color;
        ctx.textBaseline = 'middle';
        ctx.globalAlpha = state.brushOpacity;
        ctx.fillText(text, canvasX, canvasY);
        ctx.restore();
        composite();
        updateActiveLayerThumb();
        markDirty();
    }
    function cancel() {
        if (done) return; done = true;
        input.remove();
    }
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter')  { e.preventDefault(); commit(); }
        if (e.key === 'Escape') { e.preventDefault(); cancel(); }
    });
    input.addEventListener('blur', commit);
}

/* Botón "borrar selección" — elimina los píxeles dentro de la
   selección en la capa activa pero MANTIENE el rectángulo visible
   (así puedes seguir trabajando sobre la misma zona). Si había un
   transform en curso, descarta el float (los píxeles "levantados" no
   vuelven a pegarse). Respeta la rotación de la selección. */
function deleteSelectionPixels() {
    if (!state.selection) return;
    snapshotForUndo();
    const s = state.selection;
    const rot = state.selectionRotation || 0;
    const ctx = activeCtx();
    if (rot === 0) {
        ctx.clearRect(s.x, s.y, s.w, s.h);
    } else {
        /* Rotado: clearRect no soporta transform, así que usamos
           destination-out con un rect dibujado y rotado. */
        ctx.save();
        const cx = s.x + s.w / 2, cy = s.y + s.h / 2;
        ctx.translate(cx, cy);
        ctx.rotate(rot);
        ctx.translate(-cx, -cy);
        ctx.globalCompositeOperation = 'destination-out';
        ctx.fillStyle = '#000';
        ctx.fillRect(s.x, s.y, s.w, s.h);
        ctx.restore();
    }
    composite();
    updateActiveLayerThumb();
    markDirty();
}
$('sel-delete').addEventListener('click', e => {
    e.stopPropagation();
    if (state.transforming) {
        /* En transform mode los píxeles ya están "levantados" en el
           float (la capa tiene el hueco). Descartamos el float —
           cleanupTransform no pinta el float de vuelta — y la capa
           queda con el área borrada. La selección sigue visible. */
        cleanupTransform();
        composite();
        updateActiveLayerThumb();
        markDirty();
        refreshSelectionOverlay();
    } else {
        deleteSelectionPixels();
    }
});

/* Espeja el float buffer horizontalmente (flipH) o verticalmente. */
function _flipFloat(axis) {
    if (!state.transformFloat) return;
    const old = state.transformFloat;
    const flipped = document.createElement('canvas');
    flipped.width = old.width;
    flipped.height = old.height;
    const ctx = flipped.getContext('2d');
    if (axis === 'h') {
        ctx.translate(flipped.width, 0);
        ctx.scale(-1, 1);
    } else {
        ctx.translate(0, flipped.height);
        ctx.scale(1, -1);
    }
    ctx.drawImage(old, 0, 0);
    state.transformFloat = flipped;
    renderFloatingPreview();
}
/* Menú contextual del Transform — click derecho sobre la selección
   mientras está activo Shift+T abre opciones Voltear H / Voltear V.
   Fuera del transform, el right-click sobre la selección no hace nada
   especial (lo deja pasar al navegador). */
function showTransformContextMenu(clientX, clientY) {
    document.querySelectorAll('.transform-ctx-menu').forEach(el => el.remove());
    const menu = document.createElement('div');
    menu.className = 'popup-menu open transform-ctx-menu';
    const opts = [
        { label: 'Voltear horizontal ⇆', action: () => _flipFloat('h') },
        { label: 'Voltear vertical ⇅',   action: () => _flipFloat('v') },
    ];
    for (const o of opts) {
        const it = document.createElement('div');
        it.className = 'popup-item';
        it.textContent = o.label;
        it.addEventListener('click', () => { menu.remove(); o.action(); });
        menu.appendChild(it);
    }
    document.body.appendChild(menu);
    menu.style.left = clientX + 'px';
    menu.style.top  = clientY + 'px';
    const close = ev => {
        if (!menu.contains(ev.target)) {
            menu.remove();
            document.removeEventListener('click',       close, true);
            document.removeEventListener('contextmenu', close, true);
        }
    };
    setTimeout(() => {
        document.addEventListener('click',       close, true);
        document.addEventListener('contextmenu', close, true);
    }, 0);
}
$('selection-overlay').addEventListener('contextmenu', e => {
    if (!state.transforming) return;
    e.preventDefault();
    e.stopPropagation();
    showTransformContextMenu(e.clientX, e.clientY);
});

/* Dibuja los handles 8 (resize) + 1 (rotación) sobre el overlay. */
function renderTransformHandles() {
    const ov = $('selection-overlay');
    ov.querySelectorAll('.tx-handle').forEach(h => h.remove());
    if (!state.transforming) return;
    const handles = [
        ['nw',  0,   0  ], ['n',  0.5, 0  ], ['ne', 1,   0  ],
        ['e',   1,   0.5], ['se', 1,   1  ], ['s',  0.5, 1  ],
        ['sw',  0,   1  ], ['w',  0,   0.5],
        ['rot', 0.5, -0.12],
    ];
    handles.forEach(([cls, fx, fy]) => {
        const el = document.createElement('div');
        el.className = 'tx-handle ' + cls;
        el.style.left = (fx * 100) + '%';
        el.style.top  = (fy * 100) + '%';
        el.dataset.op = cls;
        ov.appendChild(el);
    });
}

/* ════════════════════════════════════════════════════════════════════
   COLOR — picker + rueda HSV (sin recursión, con cache offscreen)
   ════════════════════════════════════════════════════════════════════ */
const WHEEL_SIZE = 140, WHEEL_R = 68, WHEEL_INNER = 50, SQ_HALF = 30;
let currentH = 0, currentS = 0, currentV = 0;
const wheelCanvas = $('color-wheel');
const wctx = wheelCanvas.getContext('2d');
/* Cache offscreen: solo se reconstruye cuando cambia H. */
const wheelCache = document.createElement('canvas');
wheelCache.width = WHEEL_SIZE; wheelCache.height = WHEEL_SIZE;
const wcacheCtx = wheelCache.getContext('2d');
let cachedH = -1;

function hsvToRgb(h, s, v) {
    const i = Math.floor(h * 6);
    const f = h * 6 - i;
    const p = v * (1 - s);
    const q = v * (1 - f * s);
    const t = v * (1 - (1 - f) * s);
    let r, g, b;
    switch (i % 6) {
        case 0: r = v; g = t; b = p; break;
        case 1: r = q; g = v; b = p; break;
        case 2: r = p; g = v; b = t; break;
        case 3: r = p; g = q; b = v; break;
        case 4: r = t; g = p; b = v; break;
        default: r = v; g = p; b = q;
    }
    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}
function rgbToHex(r, g, b) {
    return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
}
function hexToHsv(hex) {
    const r = parseInt(hex.slice(1, 3), 16) / 255;
    const g = parseInt(hex.slice(3, 5), 16) / 255;
    const b = parseInt(hex.slice(5, 7), 16) / 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    const d = max - min;
    let h = 0;
    const s = max === 0 ? 0 : d / max;
    const v = max;
    if (max !== min) {
        if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
        else if (max === g) h = ((b - r) / d + 2) / 6;
        else h = ((r - g) / d + 4) / 6;
    }
    return [h, s, v];
}

function buildWheelCache(h) {
    const sz = WHEEL_SIZE, cx = sz / 2, cy = sz / 2;
    const img = wcacheCtx.createImageData(sz, sz);
    const d = img.data;
    for (let y = 0; y < sz; y++) {
        for (let x = 0; x < sz; x++) {
            const dx = x - cx, dy = y - cy;
            const dist = Math.hypot(dx, dy);
            const off = (y * sz + x) * 4;
            if (dist >= WHEEL_INNER && dist <= WHEEL_R) {
                const a = (Math.atan2(dy, dx) / (Math.PI * 2)) + 0.5;
                const [r, g, b] = hsvToRgb(a, 1, 1);
                d[off] = r; d[off+1] = g; d[off+2] = b; d[off+3] = 255;
            } else if (Math.abs(dx) <= SQ_HALF && Math.abs(dy) <= SQ_HALF) {
                const s = (dx + SQ_HALF) / (SQ_HALF * 2);
                const v = 1 - (dy + SQ_HALF) / (SQ_HALF * 2);
                const [r, g, b] = hsvToRgb(h, s, v);
                d[off] = r; d[off+1] = g; d[off+2] = b; d[off+3] = 255;
            }
        }
    }
    wcacheCtx.putImageData(img, 0, 0);
    cachedH = h;
}

function renderWheel() {
    if (Math.abs(cachedH - currentH) > 0.001) buildWheelCache(currentH);
    wctx.clearRect(0, 0, WHEEL_SIZE, WHEEL_SIZE);
    wctx.drawImage(wheelCache, 0, 0);
    const cx = WHEEL_SIZE / 2, cy = WHEEL_SIZE / 2;
    const sx = cx - SQ_HALF + currentS * SQ_HALF * 2;
    const sy = cy - SQ_HALF + (1 - currentV) * SQ_HALF * 2;
    wctx.lineWidth = 2; wctx.strokeStyle = '#000';
    wctx.beginPath(); wctx.arc(sx, sy, 5, 0, Math.PI * 2); wctx.stroke();
    wctx.lineWidth = 1; wctx.strokeStyle = '#fff';
    wctx.beginPath(); wctx.arc(sx, sy, 5, 0, Math.PI * 2); wctx.stroke();
    const a = currentH * Math.PI * 2 - Math.PI;
    const mid = (WHEEL_R + WHEEL_INNER) / 2;
    const hx = cx + Math.cos(a) * mid, hy = cy + Math.sin(a) * mid;
    wctx.lineWidth = 2; wctx.strokeStyle = '#000';
    wctx.beginPath(); wctx.arc(hx, hy, 6, 0, Math.PI * 2); wctx.stroke();
    wctx.lineWidth = 1; wctx.strokeStyle = '#fff';
    wctx.beginPath(); wctx.arc(hx, hy, 6, 0, Math.PI * 2); wctx.stroke();
}

function setColor(hex) {
    state.color = hex;
    $('color-hex').value = hex;
    $('color-swatch').style.background = hex;
    const [h, s, v] = hexToHsv(hex);
    currentH = h; currentS = s; currentV = v;
    renderWheel();
    updateHsvGradients();
    syncHsvSliders();
}

/* Sincroniza las 3 barras con el color actual: pone el thumb de cada
   slider en el valor H/S/V correspondiente. Se llama tras CUALQUIER
   cambio externo de color (rueda, eyedropper, hex input). */
function syncHsvSliders() {
    const h = Math.round(currentH * 360);
    const s = Math.round(currentS * 100);
    const v = Math.round(currentV * 100);
    $('color-h').value = h; $('color-h-val').textContent = h;
    $('color-s').value = s; $('color-s-val').textContent = s;
    $('color-v').value = v; $('color-v-val').textContent = v;
}

/* Pinta los fondos en gradiente de las 3 barras según el color actual.
   - H: arcoíris fijo (0–360 hue) — cíclico, no depende del color.
   - S: del gris al hue actual con saturación máxima.
   - V: del negro al color actual con V máxima.
   Llamado cada vez que el color cambia. */
function updateHsvGradients() {
    const h360 = (currentH * 360) | 0;
    const sPct = (currentS * 100) | 0;
    const stops = [];
    for (let i = 0; i <= 6; i++) stops.push('hsl(' + (i * 60) + ',100%,50%)');
    $('color-h').style.background = 'linear-gradient(to right,' + stops.join(',') + ')';
    $('color-s').style.background = 'linear-gradient(to right,' +
        'hsl(' + h360 + ',0%,50%),' +
        'hsl(' + h360 + ',100%,50%))';
    $('color-v').style.background = 'linear-gradient(to right,#000,' +
        'hsl(' + h360 + ',' + sPct + '%,50%))';
}

/* Sliders absolutos: el thumb representa directamente el valor H/S/V
   del color actual. Mover el thumb → setea ese canal y refresca el
   color y la rueda. La rueda y otras fuentes externas sincronizan los
   thumbs vía syncHsvSliders(). */
function bindHsvAbsolute(id, channel, max) {
    const slider = $(id);
    const readout = $(id + '-val');
    slider.addEventListener('input', e => {
        const v = +e.target.value;
        readout.textContent = v;
        if (channel === 'h')      currentH = v / max;
        else if (channel === 's') currentS = v / max;
        else                      currentV = v / max;
        const [r, g, b] = hsvToRgb(currentH, currentS, currentV);
        state.color = rgbToHex(r, g, b);
        $('color-hex').value = state.color;
        $('color-swatch').style.background = state.color;
        renderWheel();
        updateHsvGradients();
    });
}
bindHsvAbsolute('color-h', 'h', 360);
bindHsvAbsolute('color-s', 's', 100);
bindHsvAbsolute('color-v', 'v', 100);
$('color-hex').addEventListener('change', e => {
    let v = e.target.value.trim();
    if (!v.startsWith('#')) v = '#' + v;
    if (/^#[0-9a-f]{6}$/i.test(v)) setColor(v.toLowerCase());
    else $('color-hex').value = state.color;
});

let wheelDragging = null;
wheelCanvas.addEventListener('pointerdown', e => {
    wheelCanvas.setPointerCapture(e.pointerId);
    handleWheelPointer(e, true);
});
wheelCanvas.addEventListener('pointermove', e => { if (wheelDragging) handleWheelPointer(e, false); });
wheelCanvas.addEventListener('pointerup',   () => { wheelDragging = null; });
wheelCanvas.addEventListener('pointercancel', () => { wheelDragging = null; });

function handleWheelPointer(e, isDown) {
    const r = wheelCanvas.getBoundingClientRect();
    const sx = WHEEL_SIZE / r.width, sy = WHEEL_SIZE / r.height;
    const x = (e.clientX - r.left) * sx, y = (e.clientY - r.top) * sy;
    const dx = x - WHEEL_SIZE / 2, dy = y - WHEEL_SIZE / 2;
    const d = Math.hypot(dx, dy);
    if (isDown) {
        if (d >= WHEEL_INNER && d <= WHEEL_R) wheelDragging = 'hue';
        else if (Math.abs(dx) <= SQ_HALF && Math.abs(dy) <= SQ_HALF) wheelDragging = 'sv';
        else return;
    }
    if (wheelDragging === 'hue') {
        currentH = ((Math.atan2(dy, dx) / (Math.PI * 2)) + 0.5 + 1) % 1;
    } else if (wheelDragging === 'sv') {
        currentS = Math.max(0, Math.min(1, (dx + SQ_HALF) / (SQ_HALF * 2)));
        currentV = Math.max(0, Math.min(1, 1 - (dy + SQ_HALF) / (SQ_HALF * 2)));
    }
    const [r2, g2, b2] = hsvToRgb(currentH, currentS, currentV);
    /* No usamos setColor() porque hexToHsv perdería el hue al pasar por
       el RGB de un negro/blanco puro. Manualmente actualizamos color +
       DOM y refrescamos la rueda. */
    state.color = rgbToHex(r2, g2, b2);
    $('color-hex').value = state.color;
    $('color-swatch').style.background = state.color;
    renderWheel();
    updateHsvGradients();
    syncHsvSliders();
}

/* ════════════════════════════════════════════════════════════════════
   TOOLBAR + ZOOM + PAN
   ════════════════════════════════════════════════════════════════════ */
document.querySelectorAll('[data-tool]').forEach(btn => btn.addEventListener('click', () => {
    const newTool = btn.dataset.tool;
    /* Si cambiamos a una herramienta distinta, tratamos como Esc:
       cancelamos cualquier Transform activo y borramos la selección.
       Click sobre la misma herramienta no afecta a la selección. */
    if (newTool !== state.tool) {
        if (state.transforming) cancelTransform();
        else if (state.selection) clearSelection();
    }
    document.querySelectorAll('[data-tool]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    state.tool = newTool;
    display.className = state.tool === 'eraser' ? 'eraser'
                      : state.tool === 'eyedrop' ? 'eyedropper'
                      : state.tool === 'select'  ? 'selection'
                      : state.tool === 'text'    ? 'text' : '';
}));
$('size').addEventListener('input', e => {
    state.size = +e.target.value;
    $('size-val').textContent = state.size;
});
$('smooth').addEventListener('input', e => state.smooth = +e.target.value);
$('brush-opacity').addEventListener('input', e => {
    state.brushOpacity = +e.target.value / 100;
    $('brush-opacity-val').textContent = e.target.value + '%';
});

/* ════════════════════════════════════════════════════════════════════
   PANEL DE PINCELES — genera previews dinámicos pintando un trazo de
   ejemplo con cada brush sobre un canvas pequeño. Para blender se
   pinta sobre un degradado para que el efecto smudge se vea.
   ════════════════════════════════════════════════════════════════════ */
function renderBrushPreview(brushName) {
    const W = 32, H = 22;
    const cv = document.createElement('canvas');
    cv.width = W; cv.height = H;
    const pctx = cv.getContext('2d', { willReadFrequently: true });
    const brush = BRUSHES[brushName];

    /* Fondo del preview. Para blender un degradado horizontal para que
       el smudge sea visible. */
    if (brush.blender) {
        const g = pctx.createLinearGradient(0, 0, W, 0);
        g.addColorStop(0, '#cccccc');
        g.addColorStop(1, '#222222');
        pctx.fillStyle = g;
    } else {
        pctx.fillStyle = '#ffffff';
    }
    pctx.fillRect(0, 0, W, H);

    /* Curva de muestra: ondulación de izquierda a derecha con presión
       campana (0.2 → 1 → 0.2). */
    const samples = [];
    const N = 22;
    for (let i = 0; i <= N; i++) {
        const t = i / N;
        const x = 3 + t * (W - 6);
        const y = H / 2 + Math.sin(t * Math.PI * 1.6) * 4;
        const p = 0.2 + Math.sin(t * Math.PI) * 0.8;
        samples.push({ x, y, p });
    }

    const prevSize = state.size, prevOpacity = state.brushOpacity, prevBlend = state.blendPrev;
    state.size = 6;
    state.brushOpacity = 1.0;
    state.blendPrev = null;
    const color = brush.blender ? '#ff5577' : '#222222';
    for (let i = 1; i < samples.length; i++) {
        /* sampleCtx = pctx para que blender funcione sobre su propio fondo. */
        paintSegment(pctx, samples[i-1], samples[i], color, brush, false, pctx);
    }
    state.size = prevSize;
    state.brushOpacity = prevOpacity;
    state.blendPrev = prevBlend;
    return cv;
}

function renderBrushGrid() {
    const grid = $('brush-grid');
    grid.innerHTML = '';
    Object.keys(BRUSHES).forEach(name => {
        const btn = document.createElement('button');
        btn.className = 'brush-btn' + (name === state.brushName ? ' active' : '');
        btn.dataset.brush = name;
        btn.title = BRUSH_LABELS[name];
        btn.appendChild(renderBrushPreview(name));
        btn.addEventListener('click', () => setBrush(name));
        grid.appendChild(btn);
    });
    $('brush-current').textContent = BRUSH_LABELS[state.brushName] || state.brushName;
}

function setBrush(name) {
    if (!BRUSHES[name]) return;
    state.brushName = name;
    /* Si el usuario estaba con goma/cuentagotas/selección, al elegir un
       brush volvemos al modo pincel automáticamente. */
    if (state.tool !== 'brush') {
        document.querySelector('[data-tool="brush"]').click();
    }
    document.querySelectorAll('.brush-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.brush === name);
    });
    $('brush-current').textContent = BRUSH_LABELS[name] || name;
}
renderBrushGrid();

function applyZoom() {
    const zc = $('zoom-container');
    zc.style.width  = state.width  * state.zoom + 'px';
    zc.style.height = state.height * state.zoom + 'px';
    zc.style.transform = 'translate(' + state.panX + 'px,' + state.panY + 'px)';
    display.style.width  = state.width  * state.zoom + 'px';
    display.style.height = state.height * state.zoom + 'px';
    refreshSelectionOverlay();
    $('zoom-val').textContent = Math.round(state.zoom * 100) + '%';
}
function setZoom(z, anchorX, anchorY) {
    z = Math.max(CFG.ZOOM_MIN, Math.min(CFG.ZOOM_MAX, z));
    if (z === state.zoom) return;
    const wrap = $('canvas-wrap');
    /* Sin ancla explícita → ancla el centro del wrap (zoom buttons). */
    if (anchorX === undefined) {
        anchorX = wrap.clientWidth / 2;
        anchorY = wrap.clientHeight / 2;
    }
    /* Punto bajo el cursor en coords internas del lienzo ANTES del zoom. */
    const wrapRect = wrap.getBoundingClientRect();
    const dispRect = display.getBoundingClientRect();
    const clientX = wrapRect.left + anchorX;
    const clientY = wrapRect.top  + anchorY;
    const canvasX = (clientX - dispRect.left) / state.zoom;
    const canvasY = (clientY - dispRect.top)  / state.zoom;
    state.zoom = z;
    applyZoom();
    /* Tras el resize, ese mismo punto del lienzo cae en otra posición —
       compensamos con panX/panY para que quede de nuevo bajo el cursor. */
    const newRect = display.getBoundingClientRect();
    const newClientX = newRect.left + canvasX * z;
    const newClientY = newRect.top  + canvasY * z;
    state.panX += clientX - newClientX;
    state.panY += clientY - newClientY;
    applyZoom();
}
$('btn-zoom-in').addEventListener('click',  () => setZoom(state.zoom * CFG.ZOOM_STEP));
$('btn-zoom-out').addEventListener('click', () => setZoom(state.zoom / CFG.ZOOM_STEP));
$('btn-zoom-1').addEventListener('click',   () => setZoom(1));
$('btn-zoom-fit').addEventListener('click', () => {
    const wrap = $('canvas-wrap');
    setZoom(Math.min((wrap.clientWidth - 40) / state.width,
                     (wrap.clientHeight - 40) / state.height));
});
/* Rueda del ratón = zoom (sin necesidad de Ctrl). El ancla bajo el
   cursor se mantiene fija para que el punto que miras no se mueva. */
$('canvas-wrap').addEventListener('wheel', e => {
    e.preventDefault();
    const r = $('canvas-wrap').getBoundingClientRect();
    setZoom(state.zoom * (e.deltaY < 0 ? CFG.ZOOM_STEP : 1/CFG.ZOOM_STEP),
            e.clientX - r.left, e.clientY - r.top);
}, { passive: false });

document.addEventListener('keydown', e => {
    if (e.target.matches('input,textarea,select')) return;
    if (e.code === 'Space' && !state.panKey) {
        e.preventDefault();
        state.panKey = true;
        /* Cursor pasa a 'grab' (mano abierta) — todavía no estamos
           panneando, solo en modo listo. */
        $('canvas-wrap').classList.add('panning');
    }
});
document.addEventListener('keyup', e => {
    if (e.code === 'Space') {
        state.panKey = false;
        state.panning = false;
        $('canvas-wrap').classList.remove('panning', 'pan-active');
        state.panLastX = null;
        state.panLastY = null;
    }
});
/* ════════════════════════════════════════════════════════════════════
   PAN — Espacio + click izquierdo + arrastrar mueve el LIENZO.
   Implementado con transform:translate() sobre #zoom-container así
   funciona aunque no haya scroll y se aplica independientemente del
   tamaño del lienzo.
   ════════════════════════════════════════════════════════════════════ */
$('canvas-wrap').addEventListener('pointerdown', e => {
    /* Solo si Espacio está pulsado Y es click izquierdo. */
    if (!state.panKey || e.button !== 0) return;
    state.panning = true;
    state.panLastX = e.clientX;
    state.panLastY = e.clientY;
    state.panPointerId = e.pointerId;
    /* Cursor pasa de 'grab' a 'grabbing' (mano cerrada). */
    $('canvas-wrap').classList.add('pan-active');
    e.preventDefault();
});
document.addEventListener('pointermove', e => {
    if (!state.panning) return;
    if (state.panPointerId !== undefined && e.pointerId !== state.panPointerId) return;
    const dx = e.clientX - state.panLastX;
    const dy = e.clientY - state.panLastY;
    state.panLastX = e.clientX;
    state.panLastY = e.clientY;
    state.panX += dx;
    state.panY += dy;
    $('zoom-container').style.transform =
        'translate(' + state.panX + 'px,' + state.panY + 'px)';
});
function endPan(e) {
    if (!state.panning) return;
    if (state.panPointerId !== undefined && e && e.pointerId !== state.panPointerId) return;
    state.panning = false;
    state.panPointerId = undefined;
    state.panLastX = null;
    state.panLastY = null;
    /* Quita el cursor 'grabbing'. Si Espacio sigue pulsado, vuelve a
       'grab' (el keyup limpiará 'panning' después). */
    $('canvas-wrap').classList.remove('pan-active');
}
document.addEventListener('pointerup',     endPan);
document.addEventListener('pointercancel', endPan);

/* ════════════════════════════════════════════════════════════════════
   MENU ARCHIVO + DIALOGO NUEVO LIENZO + SAVE
   ════════════════════════════════════════════════════════════════════ */
$('menu-archivo').addEventListener('click', e => {
    e.stopPropagation();
    const popup = $('menu-archivo-popup');
    const r = $('menu-archivo').getBoundingClientRect();
    popup.style.left = r.left + 'px'; popup.style.top = r.bottom + 'px';
    popup.classList.toggle('open');
});
document.addEventListener('click', () => $('menu-archivo-popup').classList.remove('open'));
$('menu-archivo-popup').addEventListener('click', e => {
    const act = e.target.closest('.popup-item')?.dataset.act;
    if (act === 'new')          openNewDialog();
    if (act === 'open-ora')     $('ora-file-input').click();
    if (act === 'save-gallery') saveToGallery();
    if (act === 'download-ora') saveOra();
    if (act === 'save-png')     saveFlatPNG();
    if (act === 'save-zip')     saveLayersZIP();
});
$('ora-file-input').addEventListener('change', async e => {
    const f = e.target.files && e.target.files[0];
    e.target.value = '';  /* permite re-elegir el mismo fichero después */
    if (!f) return;
    const choice = await winDialog({
        title: 'Abrir',
        message: '¿Cargar "' + f.name + '"? Se perderá el dibujo actual.',
        buttons: [{label:'Cancelar'}, {label:'Cargar', default:true}],
    });
    if (choice !== 1) return;
    try {
        await loadAnyImage(f);
    } catch (err) {
        await winDialog({
            title: 'Error al cargar',
            message: 'No se pudo abrir el archivo: ' + (err && err.message || err),
            buttons: [{label:'Aceptar', default:true}],
        });
    }
});

function openNewDialog() {
    $('new-w').value  = state.width;
    $('new-h').value  = state.height;
    $('new-bg').value = state.bg;
    $('dlg-new').classList.add('open');
}
$('dlg-new').addEventListener('click', e => {
    if (e.target.matches('[data-close]') || e.target === $('dlg-new')) {
        $('dlg-new').classList.remove('open');
    }
});
$('new-ok').addEventListener('click', () => {
    const w = +$('new-w').value, h = +$('new-h').value, bg = $('new-bg').value;
    if (w < 64 || h < 64) return alert('Mínimo 64x64');
    if (!confirm('Crear lienzo nuevo? Se perderá lo actual.')) return;
    resetCanvas(w, h, bg);
    $('dlg-new').classList.remove('open');
});

function saveFlatPNG() {
    const off = document.createElement('canvas');
    off.width = state.width; off.height = state.height;
    const ox = off.getContext('2d');
    ox.fillStyle = state.bg; ox.fillRect(0, 0, state.width, state.height);
    for (const l of state.layers) {
        if (!l.visible) continue;
        ox.globalAlpha = l.opacity;
        ox.drawImage(l.canvas, 0, 0);
    }
    off.toBlob(blob => downloadBlob(blob, 'dibujo.png'));
}
function saveLayersZIP() {
    if (typeof JSZip === 'undefined') return alert('JSZip no cargado');
    const zip = new JSZip();
    let pending = state.layers.length;
    state.layers.forEach((l, i) => {
        l.canvas.toBlob(b => {
            const safe = l.name.replace(/[^\w\d-]+/g, '_');
            zip.file((i+1).toString().padStart(2, '0') + '_' + safe + '.png', b);
            if (--pending === 0) {
                zip.generateAsync({ type: 'blob' }).then(blob => {
                    downloadBlob(blob, 'dibujo-capas.zip');
                });
            }
        });
    });
}
/* ════════════════════════════════════════════════════════════════════
   FORMATO OPENRASTER (.ora) — guardar / cargar
   ════════════════════════════════════════════════════════════════════
   Spec: https://www.openraster.org/baseline/file-layout-spec.html
   Estructura del ZIP:
     - mimetype           (texto "image/openraster", PRIMERO, sin comprimir)
     - stack.xml          (jerarquía de capas/grupos)
     - data/layerN.png    (una por capa)
     - mergedimage.png    (composite final aplanado)
     - Thumbnails/thumbnail.png (preview 256px)
   Nuestras extensiones custom (groups + clipMask por capa) viajan en
   el atributo `mlh:clipMask` con namespace propio — otras apps lo
   ignoran, nosotros lo leemos de vuelta. */
function escapeXml(s) {
    return String(s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&apos;');
}

function buildOraStackXml(layerFilenames) {
    /* La cadena XML se construye por trozos: si pones la
       declaracion entera literal aqui, el parser PHP la interpreta
       como un open tag suyo y rompe la compilacion del template. */
    let xml = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n';
    xml += '<image w="' + state.width + '" h="' + state.height
         + '" xmlns:mlh="https://melon.hub/openraster">\n';
    xml += '  <stack>\n';
    /* ORA stack: primera <layer> = arriba del z-order. Nuestro array
       tiene idx 0 = abajo, así que iteramos en reverso. Las capas
       contiguas con mismo groupId se envuelven en un <stack name="..."/>. */
    let prevGroupId = undefined;
    let inGroup = false;
    for (let i = state.layers.length - 1; i >= 0; i--) {
        const layer = state.layers[i];
        if (layer.groupId !== prevGroupId) {
            if (inGroup) { xml += '    </stack>\n'; inGroup = false; }
            if (layer.groupId != null) {
                const g = findGroup(layer.groupId);
                xml += '    <stack name="' + escapeXml(g ? g.name : 'Grupo')
                     + '" isolation="isolate">\n';
                inGroup = true;
            }
        }
        prevGroupId = layer.groupId;
        const indent = inGroup ? '      ' : '    ';
        const opacity = layer.opacity.toFixed(3);
        const visibility = layer.visible ? 'visible' : 'hidden';
        const clipAttr = layer.clipMask ? ' mlh:clipMask="true"' : '';
        xml += indent + '<layer src="' + layerFilenames[i]
             + '" name="' + escapeXml(layer.name)
             + '" opacity="' + opacity
             + '" visibility="' + visibility + '"' + clipAttr + '/>\n';
    }
    if (inGroup) xml += '    </stack>\n';
    xml += '  </stack>\n</image>\n';
    return xml;
}

async function saveOra() {
    if (typeof JSZip === 'undefined') return alert('JSZip no cargado');
    const zip = new JSZip();
    /* mimetype debe ir PRIMERO y sin comprimir (spec OpenRaster). */
    zip.file('mimetype', 'image/openraster', { compression: 'STORE' });
    /* Cada capa → PNG en data/. */
    const filenames = [];
    for (let i = 0; i < state.layers.length; i++) {
        const layer = state.layers[i];
        const blob = await new Promise(res => layer.canvas.toBlob(res, 'image/png'));
        const name = 'data/layer' + i + '.png';
        zip.file(name, blob);
        filenames.push(name);
    }
    /* stack.xml */
    zip.file('stack.xml', buildOraStackXml(filenames));
    /* mergedimage.png + Thumbnails/thumbnail.png — flatten respetando fondo. */
    const merged = document.createElement('canvas');
    merged.width = state.width; merged.height = state.height;
    const mctx = merged.getContext('2d');
    mctx.fillStyle = state.bg; mctx.fillRect(0, 0, state.width, state.height);
    for (const l of state.layers) {
        if (!l.visible) continue;
        mctx.globalAlpha = l.opacity;
        mctx.drawImage(l.canvas, 0, 0);
    }
    mctx.globalAlpha = 1;
    const mergedBlob = await new Promise(res => merged.toBlob(res, 'image/png'));
    zip.file('mergedimage.png', mergedBlob);
    /* Thumbnail 256px máx en cualquier dimensión. */
    const tscale = 256 / Math.max(state.width, state.height);
    const thumb = document.createElement('canvas');
    thumb.width  = Math.max(1, Math.round(state.width  * tscale));
    thumb.height = Math.max(1, Math.round(state.height * tscale));
    const tctx = thumb.getContext('2d');
    tctx.drawImage(merged, 0, 0, thumb.width, thumb.height);
    const thumbBlob = await new Promise(res => thumb.toBlob(res, 'image/png'));
    zip.file('Thumbnails/thumbnail.png', thumbBlob);
    /* Genera ZIP y descarga. */
    const blob = await zip.generateAsync({ type: 'blob' });
    downloadBlob(blob, 'dibujo.ora');
}

/* Devuelve el blob del .ora completo (mismo contenido que saveOra)
   pero sin disparar la descarga. Usado por saveToGallery. */
async function buildOraBlob() {
    if (typeof JSZip === 'undefined') throw new Error('JSZip no cargado');
    const zip = new JSZip();
    zip.file('mimetype', 'image/openraster', { compression: 'STORE' });
    const filenames = [];
    for (let i = 0; i < state.layers.length; i++) {
        const blob = await new Promise(res => state.layers[i].canvas.toBlob(res, 'image/png'));
        const name = 'data/layer' + i + '.png';
        zip.file(name, blob);
        filenames.push(name);
    }
    zip.file('stack.xml', buildOraStackXml(filenames));
    const merged = document.createElement('canvas');
    merged.width = state.width; merged.height = state.height;
    const mctx = merged.getContext('2d');
    mctx.fillStyle = state.bg; mctx.fillRect(0, 0, state.width, state.height);
    for (const l of state.layers) {
        if (!l.visible) continue;
        mctx.globalAlpha = l.opacity;
        mctx.drawImage(l.canvas, 0, 0);
    }
    mctx.globalAlpha = 1;
    const mergedBlob = await new Promise(res => merged.toBlob(res, 'image/png'));
    zip.file('mergedimage.png', mergedBlob);
    const tscale = 256 / Math.max(state.width, state.height);
    const thumb = document.createElement('canvas');
    thumb.width  = Math.max(1, Math.round(state.width  * tscale));
    thumb.height = Math.max(1, Math.round(state.height * tscale));
    const tctx = thumb.getContext('2d');
    tctx.drawImage(merged, 0, 0, thumb.width, thumb.height);
    const thumbBlob = await new Promise(res => thumb.toBlob(res, 'image/png'));
    zip.file('Thumbnails/thumbnail.png', thumbBlob);
    return await zip.generateAsync({ type: 'blob' });
}

/* ════════════════════════════════════════════════════════════════════
   GUARDAR EN GALERÍA — usa el token de Drive que la galería deja en
   localStorage. No hacemos nuestra propia auth (sería duplicar la
   infraestructura OAuth). Si no hay token, pedimos al usuario que se
   conecte primero en la app de Galería.
   ════════════════════════════════════════════════════════════════════ */
const DRIVE_TOKEN_KEY     = 'galeria_drive_token';
const DRIVE_EVER_AUTH_KEY = 'galeria_drive_ever_auth';
const GDRIVE_SCOPE        = 'https://www.googleapis.com/auth/drive.file';
const GOOGLE_CLIENT_ID    = <?php echo json_encode(GOOGLE_CLIENT_ID); ?>;
const GDRIVE_FOLDER       = 'Scrapbook Melon - Galería';
let _driveFolderId = null;
let _tokenClient   = null;
let _pendingAuthCb = null;

function _driveAccessToken() {
    try {
        const s = localStorage.getItem(DRIVE_TOKEN_KEY);
        if (!s) return null;
        const t = JSON.parse(s);
        if (t && t.access_token && t.expires_at > Date.now() + 10000) return t.access_token;
    } catch (_) {}
    return null;
}

/* Inicializa el cliente de tokens de Google Identity Services. Si la
   librería aún no ha cargado (script async), reintenta cada 250ms. */
function _initTokenClient(onReady) {
    if (_tokenClient) { onReady && onReady(_tokenClient); return _tokenClient; }
    if (typeof google === 'undefined' || !google.accounts || !google.accounts.oauth2) {
        if (onReady) {
            let attempts = 0;
            const iv = setInterval(() => {
                attempts++;
                if (typeof google !== 'undefined' && google.accounts && google.accounts.oauth2) {
                    clearInterval(iv);
                    _initTokenClient(onReady);
                } else if (attempts > 40) {
                    clearInterval(iv);
                    onReady(null);
                }
            }, 250);
        }
        return null;
    }
    _tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: GOOGLE_CLIENT_ID,
        scope: GDRIVE_SCOPE,
        callback: function(resp) {
            if (resp.error) {
                /* Usuario canceló o falló. Limpiamos el pending callback
                   sin invocarlo. */
                _pendingAuthCb = null;
                return;
            }
            const token = {
                access_token: resp.access_token,
                expires_at:   Date.now() + (resp.expires_in * 1000) - 60000,
            };
            try {
                localStorage.setItem(DRIVE_TOKEN_KEY, JSON.stringify(token));
                localStorage.setItem(DRIVE_EVER_AUTH_KEY, '1');
            } catch (_) {}
            if (_pendingAuthCb) { const fn = _pendingAuthCb; _pendingAuthCb = null; fn(); }
        },
    });
    onReady && onReady(_tokenClient);
    return _tokenClient;
}

/* Garantiza un token válido. Si hay uno en localStorage, llama fn().
   Si no, abre el popup de Google y llama fn() cuando llegue el token.
   El popup SÓLO se abre desde un gesto de usuario (click) por política
   del navegador — saveToGallery se invoca desde un click del menú o
   Ctrl+S, así que es válido. */
function ensureDriveAuth(fn) {
    if (_driveAccessToken()) { fn(); return; }
    _pendingAuthCb = fn;
    _initTokenClient(client => {
        if (!client) {
            _pendingAuthCb = null;
            winDialog({
                title: 'Error',
                message: 'No se pudo cargar Google Identity Services. Revisa tu conexión.',
                buttons: [{label:'Aceptar', default:true}],
            });
            return;
        }
        try { client.requestAccessToken({ prompt: '' }); }
        catch (_) { try { client.requestAccessToken({ prompt: 'consent' }); } catch (__) {} }
    });
}
async function _driveFetch(url, opts) {
    const token = _driveAccessToken();
    if (!token) throw new Error('Sin sesión de Drive. Abre la Galería y conéctate primero.');
    opts = opts || {};
    opts.headers = Object.assign({}, opts.headers || {}, {
        Authorization: 'Bearer ' + token,
    });
    const r = await fetch(url, opts);
    if (r.status === 401) {
        try { localStorage.removeItem(DRIVE_TOKEN_KEY); } catch (_) {}
        throw new Error('Sesión de Drive expirada. Reconecta en la Galería.');
    }
    return r;
}
async function _ensureGalleryFolder() {
    if (_driveFolderId) return _driveFolderId;
    const q = "name='" + GDRIVE_FOLDER + "' and mimeType='application/vnd.google-apps.folder' and trashed=false";
    const r = await _driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                                 '&fields=files(id,name)');
    const d = await r.json();
    if (d.files && d.files.length) { _driveFolderId = d.files[0].id; return _driveFolderId; }
    const cr = await _driveFetch('https://www.googleapis.com/drive/v3/files', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: GDRIVE_FOLDER, mimeType: 'application/vnd.google-apps.folder' })
    });
    const c = await cr.json();
    _driveFolderId = c.id;
    return _driveFolderId;
}
async function _driveUploadNewOra(name, blob) {
    const folderId = await _ensureGalleryFolder();
    const boundary = '-------dib_' + Math.random().toString(36).slice(2);
    const delim = '\r\n--' + boundary + '\r\n';
    const close = '\r\n--' + boundary + '--';
    /* Auto-etiqueta #wip — coherente con cómo la galería marca los
       archivos no-renderizables al subir desde su propio diálogo. La
       galería lo lee de appProperties.tags (CSV) para mostrar el
       badge "WIP" y filtrar por la etiqueta. */
    const metadata = {
        name, parents: [folderId],
        mimeType: 'image/openraster',
        appProperties: { tags: 'wip' },
    };
    const head = delim + 'Content-Type: application/json; charset=UTF-8\r\n\r\n' + JSON.stringify(metadata) +
                 delim + 'Content-Type: image/openraster\r\n\r\n';
    const headBytes = new TextEncoder().encode(head);
    const tailBytes = new TextEncoder().encode(close);
    const bodyBytes = new Uint8Array(await blob.arrayBuffer());
    const body = new Uint8Array(headBytes.length + bodyBytes.length + tailBytes.length);
    body.set(headBytes, 0);
    body.set(bodyBytes, headBytes.length);
    body.set(tailBytes, headBytes.length + bodyBytes.length);
    const r = await _driveFetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name', {
        method: 'POST',
        headers: { 'Content-Type': 'multipart/related; boundary=' + boundary },
        body
    });
    return await r.json();
}
async function _driveUpdateOraContent(fileId, blob) {
    const r = await _driveFetch('https://www.googleapis.com/upload/drive/v3/files/' + fileId
                                + '?uploadType=media&fields=id,name', {
        method: 'PATCH',
        headers: { 'Content-Type': 'image/openraster' },
        body: blob
    });
    return await r.json();
}

async function saveToGallery() {
    /* Si no hay token, dispara el popup de Google. La función queda en
       espera; cuando el usuario completa el flow OAuth se reentra a
       saveToGallery y esta vez sí hay token. */
    if (!_driveAccessToken()) {
        ensureDriveAuth(() => saveToGallery());
        return;
    }
    try {
        const blob = await buildOraBlob();
        if (state.currentDriveFileId) {
            /* Actualiza el archivo que abriste. */
            const updated = await _driveUpdateOraContent(state.currentDriveFileId, blob);
            state.name = updated.name || state.currentDriveFileName || state.name;
            markClean();
            await winDialog({
                title: 'Guardado',
                message: 'Actualizado en Galería: ' + state.name,
                buttons: [{label:'Aceptar', default:true}],
            });
        } else {
            /* Pide nombre y sube nuevo. */
            const defaultName = (state.name && state.name !== 'Sin título')
                ? state.name.replace(/\.[^.]+$/, '') + '.ora'
                : 'dibujo-' + new Date().toISOString().slice(0,10) + '.ora';
            const name = await winPrompt({
                title: 'Guardar en Galería',
                message: 'Nombre del archivo:',
                defaultValue: defaultName,
            });
            if (!name) return;
            const finalName = /\.ora$/i.test(name) ? name : name + '.ora';
            const created = await _driveUploadNewOra(finalName, blob);
            state.currentDriveFileId = created.id;
            state.currentDriveFileName = created.name;
            state.name = created.name;
            markClean();
            await winDialog({
                title: 'Guardado',
                message: 'Subido a Galería: ' + created.name,
                buttons: [{label:'Aceptar', default:true}],
            });
        }
    } catch (err) {
        await winDialog({
            title: 'Error al guardar',
            message: (err && err.message) || String(err),
            buttons: [{label:'Aceptar', default:true}],
        });
    }
}

/* ════════════════════════════════════════════════════════════════════
   POSTMESSAGE — cuando la galería abre un WIP, nos envía
   {type:'load-file', blob, fileName, driveFileId}. Lo cargamos como
   si lo abrieras del menú, pero recordando el driveFileId para que
   al guardar se actualice ese mismo archivo.
   ════════════════════════════════════════════════════════════════════ */
/* ════════════════════════════════════════════════════════════════════
   COPY — Ctrl+C copia el área seleccionada de la capa activa al
   portapapeles del SO como PNG. Después un Ctrl+V (handler de paste de
   abajo) la pega de vuelta, automáticamente entrando en modo Transform.
   ════════════════════════════════════════════════════════════════════ */
document.addEventListener('copy', async e => {
    const t = e.target;
    if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
    if (!state.selection) return;
    e.preventDefault();
    const s = state.selection;
    const off = document.createElement('canvas');
    off.width  = Math.max(1, Math.round(s.w));
    off.height = Math.max(1, Math.round(s.h));
    /* Copiamos SOLO la capa activa — no la composición. Si quieres
       copiar varias capas, fusiónalas antes. */
    off.getContext('2d').drawImage(activeLayer().canvas, s.x, s.y, s.w, s.h, 0, 0, off.width, off.height);
    try {
        const blob = await new Promise(res => off.toBlob(res, 'image/png'));
        if (navigator.clipboard && navigator.clipboard.write && window.ClipboardItem) {
            await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
        }
    } catch (_) { /* falla silenciosa: HTTPS requerido en algunos navegadores */ }
});

/* ════════════════════════════════════════════════════════════════════
   PASTE — Ctrl+V (o paste nativo) pega imagen del portapapeles
   directamente en modo Transform (como un Shift+T): aparece flotando
   centrada con los handles, y puede mover/escalar/rotar antes de
   comprometerla a la capa con Enter (o cancelar con Esc).
   ════════════════════════════════════════════════════════════════════ */
document.addEventListener('paste', async e => {
    /* Si el foco está en un input/textarea, dejamos que ese pegue su
       texto normalmente (hex de color, prompts, etc.). */
    const t = e.target;
    if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
    const items = e.clipboardData && e.clipboardData.items;
    if (!items) return;
    let imgBlob = null;
    for (const it of items) {
        if (it.type && it.type.indexOf('image/') === 0) {
            imgBlob = it.getAsFile();
            if (imgBlob) break;
        }
    }
    if (!imgBlob) return;
    e.preventDefault();
    try {
        const img = await _blobToImage(imgBlob);
        startTransformFromImage(img);
    } catch (err) {
        /* Falla silenciosa: si el blob no se puede decodificar como
           imagen, no hay nada útil que decir al usuario. */
    }
});

/* Variante de startTransform que NO levanta píxeles de la capa: usa
   una imagen externa (clipboard, drag-and-drop, etc.) como contenido
   del buffer flotante. La capa queda intacta hasta que el usuario
   confirma con Enter. Esc cancela sin tocar nada. */
function startTransformFromImage(img) {
    /* Si ya había un transform en curso, lo confirmamos primero para
       no mezclar dos buffers en el mismo overlay. */
    if (state.transforming) commitTransform();

    const w = img.naturalWidth || img.width;
    const h = img.naturalHeight || img.height;
    if (!w || !h) return;

    /* Snapshot para Ctrl+Z (el commit modificará la capa). */
    snapshotForUndo();

    const layer = activeLayer();
    const layerBackup = snapshotLayer(layer);

    /* Buffer flotante = imagen pegada a tamaño nativo. */
    const floatCv = document.createElement('canvas');
    floatCv.width = w; floatCv.height = h;
    floatCv.getContext('2d').drawImage(img, 0, 0);

    /* Posición inicial: centrada en el lienzo. */
    const x = Math.round((state.width  - w) / 2);
    const y = Math.round((state.height - h) / 2);

    state.selection = { x, y, w, h };
    state.selectionRotation = 0;
    state.transforming = true;
    state.transformInitial = {
        sel: { ...state.selection },
        rot: 0,
        layerIdx: state.activeIdx,
        layerBackup,
    };
    state.transformFloat = floatCv;

    composite();
    refreshSelectionOverlay();
    $('selection-overlay').classList.add('transforming');
    renderTransformHandles();
    renderFloatingPreview();
}

/* El parent (desktop-base) nos pregunta "request-close" antes de
   cerrar la ventana de Dibujo. Si hay tabs sucios, paseamos al usuario
   por el flow de confirmación. Respondemos con 'close-allowed' o
   'close-cancelled'. */
window.addEventListener('message', async ev => {
    if (ev.data && ev.data.type === 'request-close') {
        let allow = true;
        for (let i = tabs.length - 1; i >= 0; i--) {
            const tab = tabs[i];
            const dirty = (i === activeTabIdx) ? state.isDirty : tab.snapshot.isDirty;
            if (!dirty) continue;
            const ok = await closeTab(i);
            if (!ok) { allow = false; break; }
        }
        try {
            ev.source.postMessage(
                { type: allow ? 'close-allowed' : 'close-cancelled' },
                ev.origin || '*');
        } catch (_) {}
    }
});

window.addEventListener('message', async ev => {
    const msg = ev.data;
    if (!msg || msg.type !== 'load-file' || !msg.blob) return;
    try {
        /* Reconstruimos un File para que loadAnyImage lea bien la
           extensión por el nombre. */
        let f;
        try { f = new File([msg.blob], msg.fileName || 'archivo', { type: msg.blob.type || '' }); }
        catch (_) { f = msg.blob; f.name = msg.fileName || 'archivo'; }
        await loadAnyImage(f);
        if (msg.driveFileId) {
            state.currentDriveFileId   = msg.driveFileId;
            state.currentDriveFileName = msg.fileName || null;
        }
    } catch (err) {
        await winDialog({
            title: 'Error al cargar',
            message: (err && err.message) || String(err),
            buttons: [{label:'Aceptar', default:true}],
        });
    }
});

function _blobToImage(blob) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(blob);
        const img = new Image();
        img.onload  = () => { URL.revokeObjectURL(url); resolve(img); };
        img.onerror = e => { URL.revokeObjectURL(url); reject(e); };
        img.src = url;
    });
}

async function loadOra(file) {
    if (typeof JSZip === 'undefined') throw new Error('JSZip no cargado');
    const zip = await JSZip.loadAsync(file);
    const mimeFile = zip.file('mimetype');
    if (!mimeFile) throw new Error('Falta mimetype — no es .ora válido');
    const mime = (await mimeFile.async('string')).trim();
    if (mime !== 'image/openraster') throw new Error('mimetype incorrecto: ' + mime);
    const xmlFile = zip.file('stack.xml');
    if (!xmlFile) throw new Error('Falta stack.xml');
    const xmlText = await xmlFile.async('string');
    const xml = new DOMParser().parseFromString(xmlText, 'text/xml');
    if (xml.querySelector('parsererror')) throw new Error('stack.xml mal formado');
    const imageEl = xml.querySelector('image');
    if (!imageEl) throw new Error('Falta <image> en stack.xml');
    const w = parseInt(imageEl.getAttribute('w'), 10);
    const h = parseInt(imageEl.getAttribute('h'), 10);
    if (!w || !h) throw new Error('Tamaño no válido en stack.xml');

    /* Reset al estado nuevo. */
    state.width = w; state.height = h;
    state.layers = [];
    state.groups = [];
    state.undoStack = []; state.redoStack = [];
    state.panX = 0; state.panY = 0;
    state.currentDriveFileId = null;
    state.currentDriveFileName = null;
    display.width = w; display.height = h;

    /* Recorre el <stack> raíz. Para cada <stack> anidada creamos un
       grupo nuevo. Para cada <layer> guardamos sus props y src.
       Construimos en orden DOM (top→bottom) y al final invertimos para
       que idx 0 sea la capa de más abajo. */
    const collected = [];
    function walk(el, groupId) {
        for (const child of Array.from(el.children)) {
            if (child.tagName.toLowerCase() === 'stack') {
                const name = child.getAttribute('name') || ('Grupo ' + (state.groups.length + 1));
                const g = { id: state.nextGroupId++, name };
                state.groups.push(g);
                walk(child, g.id);
            } else if (child.tagName.toLowerCase() === 'layer') {
                collected.push({
                    src: child.getAttribute('src'),
                    name: child.getAttribute('name') || 'Capa',
                    opacity: parseFloat(child.getAttribute('opacity') || '1'),
                    visible: child.getAttribute('visibility') !== 'hidden',
                    groupId,
                    /* clipMask: probamos atributo prefijado y namespaced. */
                    clipMask: child.getAttribute('mlh:clipMask') === 'true'
                           || child.getAttributeNS('https://melon.hub/openraster', 'clipMask') === 'true',
                });
            }
        }
    }
    const rootStack = imageEl.querySelector('stack');
    if (rootStack) walk(rootStack, null);
    collected.reverse();  /* DOM top→bottom → array idx 0 = abajo */

    /* Carga cada PNG y crea la capa. */
    for (const entry of collected) {
        const layer = makeLayer(entry.name);
        layer.opacity = entry.opacity;
        layer.visible = entry.visible;
        layer.groupId = entry.groupId;
        layer.clipMask = entry.clipMask;
        const f = entry.src ? zip.file(entry.src) : null;
        if (f) {
            const blob = await f.async('blob');
            try {
                const img = await _blobToImage(blob);
                layer.ctx.drawImage(img, 0, 0);
            } catch (_) { /* salta capas corruptas en vez de fallar todo */ }
        }
        state.layers.push(layer);
    }
    if (state.layers.length === 0) {
        /* Defensa: si por lo que sea no había capas, mete una vacía. */
        state.layers.push(makeLayer('Capa 1'));
    }
    state.activeIdx = state.layers.length - 1;
    pruneEmptyGroups();
    /* Etiqueta del lienzo = nombre del fichero (sin extensión la mostramos
       en el tab; el state.name guarda lo original con .ora). */
    state.name = (file && file.name) ? file.name : state.name;
    state.isDirty = false;
    applyZoom();
    composite();
    renderLayers();
    renderTabs();
}

/* Dispatcher por extensión: elige el loader correcto.
   - .ora       → loadOra (full fidelity: capas, grupos, máscara α).
   - .kra       → loadKraFlattened (extrae mergedimage.png — los layers
                  reales de Krita usan formato de tiles propietario, así
                  que cargamos el composite final como única capa).
   - otros      → loadImageAsCanvas (PNG/JPG/GIF/WebP/BMP/AVIF/etc.). */
async function loadAnyImage(file) {
    const ext = ((file.name || '').toLowerCase().match(/\.([a-z0-9]+)$/) || [])[1] || '';
    if (ext === 'ora') return loadOra(file);
    if (ext === 'kra') return loadKraFlattened(file);
    return loadImageAsCanvas(file);
}

/* Carga una imagen raster cualquiera (PNG/JPG/etc.) como un lienzo
   nuevo con UNA sola capa que contiene la imagen. El tamaño del
   lienzo se ajusta al de la imagen. */
async function loadImageAsCanvas(file) {
    const img = await _blobToImage(file);
    const w = img.naturalWidth || img.width;
    const h = img.naturalHeight || img.height;
    if (!w || !h) throw new Error('Imagen vacía o no soportada');
    state.width = w; state.height = h;
    state.layers = [];
    state.groups = [];
    state.undoStack = []; state.redoStack = [];
    state.panX = 0; state.panY = 0;
    state.currentDriveFileId = null;
    state.currentDriveFileName = null;
    display.width = w; display.height = h;
    /* Nombre de capa = nombre del fichero sin extensión. */
    const layerName = (file.name || 'Capa').replace(/\.[^.]+$/, '') || 'Capa';
    const layer = makeLayer(layerName);
    layer.ctx.drawImage(img, 0, 0);
    state.layers.push(layer);
    state.activeIdx = 0;
    state.name = file.name || state.name;
    state.isDirty = false;
    applyZoom();
    composite();
    renderLayers();
    renderTabs();
}

/* Carga un .kra como una sola capa aplanada. Los archivos .kra son ZIP
   con el composite final en mergedimage.png; los layers reales viven
   en formato de tiles propietario de Krita que no podemos decodificar
   en JS sin reimplementar el spec. Para preservar más info, el usuario
   debería guardar el .kra como .ora desde Krita y abrir el .ora aquí. */
async function loadKraFlattened(file) {
    if (typeof JSZip === 'undefined') throw new Error('JSZip no cargado');
    const zip = await JSZip.loadAsync(file);
    const pngFile = zip.file('mergedimage.png') || zip.file('preview.png');
    if (!pngFile) throw new Error('No se encontró mergedimage.png ni preview.png en el .kra');
    const blob = await pngFile.async('blob');
    /* Re-empaqueta como File para que loadImageAsCanvas tome el nombre. */
    const fakeName = (file.name || 'dibujo').replace(/\.kra$/i, '') + '.png';
    let fakeFile;
    try { fakeFile = new File([blob], fakeName, { type: 'image/png' }); }
    catch (_) { fakeFile = blob; fakeFile.name = fakeName; }
    return loadImageAsCanvas(fakeFile);
}

function downloadBlob(blob, name) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = name; a.rel = 'noopener';
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

/* ════════════════════════════════════════════════════════════════════
   ATAJOS
   ════════════════════════════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if (e.target.matches('input,textarea,select')) return;
    const k = e.key.toLowerCase();
    if ((e.ctrlKey || e.metaKey) && k === 'z' && !e.shiftKey) { e.preventDefault(); $('btn-undo').click(); return; }
    if ((e.ctrlKey || e.metaKey) && (k === 'y' || (k === 'z' && e.shiftKey))) { e.preventDefault(); $('btn-redo').click(); return; }
    if ((e.ctrlKey || e.metaKey) && k === 's') { e.preventDefault(); saveToGallery(); return; }
    /* Shift+T sobre selección → entra/sale del modo Transform. */
    if (e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey && k === 't') {
        e.preventDefault();
        if (state.transforming) commitTransform();
        else startTransform();
        return;
    }
    /* Enter confirma transform; Esc cancela revirtiendo al snapshot. */
    if (state.transforming && e.key === 'Enter')  { e.preventDefault(); commitTransform();  return; }
    if (state.transforming && e.key === 'Escape') { e.preventDefault(); cancelTransform(); return; }
    if (e.ctrlKey || e.metaKey || e.altKey || e.shiftKey) return;
    const map = { b: 'brush', e: 'eraser', i: 'eyedrop', m: 'select', t: 'text' };
    if (map[k]) document.querySelector('[data-tool="' + map[k] + '"]').click();
});

/* ════════════════════════════════════════════════════════════════════
   RED — placeholder. WS deshabilitado hasta que haya server.
   ════════════════════════════════════════════════════════════════════ */
const NET_URL = '';
let ws = null;
const remoteStrokes = new Map();
function connectWS() {
    if (!NET_URL) return;
    ws = new WebSocket(NET_URL);
    ws.onopen    = () => { $('conn-status').textContent = 'online';
                           $('conn-status').className = 'status-bar-field online'; };
    ws.onclose   = () => { $('conn-status').textContent = 'offline';
                           $('conn-status').className = 'status-bar-field offline';
                           setTimeout(connectWS, 3000); };
    ws.onmessage = ev => { try { handleRemote(JSON.parse(ev.data)); } catch (_) {} };
}
function netSend(msg) {
    if (!ws || ws.readyState !== 1) return;
    ws.send(JSON.stringify(msg));
}
function scheduleFlush() {
    if (state.flushTimer) return;
    state.flushTimer = setTimeout(flushNow, CFG.NET_FLUSH_MS);
}
function flushNow() {
    if (state.flushTimer) { clearTimeout(state.flushTimer); state.flushTimer = null; }
    if (!state.pendingSamples.length || !state.strokeId) return;
    netSend({ type: 'stroke-data', strokeId: state.strokeId, samples: state.pendingSamples });
    state.pendingSamples = [];
}
function handleRemote(msg) {
    if (msg.type === 'stroke-begin') {
        remoteStrokes.set(msg.strokeId, {
            tool: msg.tool, color: msg.color, size: msg.size, brush: msg.brush,
            layerIdx: msg.layerIdx, lastPt: null });
        /* Trazos remotos también reinician el carry del blender. */
        state.blendPrev = null;
    } else if (msg.type === 'stroke-data') {
        const s = remoteStrokes.get(msg.strokeId);
        if (!s) return;
        const layer = state.layers[s.layerIdx] || activeLayer();
        const brush = BRUSHES[s.brush] || BRUSHES.round;
        const isEraser = s.tool === 'eraser';
        const savedSize = state.size; state.size = s.size;
        for (const pt of msg.samples) {
            if (!s.lastPt) stampDab(layer.ctx, pt.x, pt.y, pt.p, s.color, brush, isEraser);
            else paintSegment(layer.ctx, s.lastPt, pt, s.color, brush, isEraser);
            s.lastPt = pt;
        }
        state.size = savedSize;
        composite();
    } else if (msg.type === 'stroke-end') {
        const s = remoteStrokes.get(msg.strokeId);
        if (s) {
            /* Refresca la miniatura de la capa donde estaba pintando
               el remoto (puede no ser la activa nuestra). */
            const item = document.querySelector('.layer-item[data-layer-idx="' + s.layerIdx + '"]');
            if (item) drawLayerThumb(item.querySelector('.layer-thumb'), state.layers[s.layerIdx]);
        }
        remoteStrokes.delete(msg.strokeId);
    } else if (msg.type === 'users') {
        $('users').textContent = 'Conectados: ' + msg.count;
    }
}
connectWS();

/* ════════════════════════════════════════════════════════════════════
   BOOT
   ════════════════════════════════════════════════════════════════════ */
createNewTab({ width: 1280, height: 720, bg: '#ffffff' });
setColor('#000000');

</script>
</body>
</html>
