<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>D&D Fichas</title>
<style>
@font-face { font-family:'Allison'; src:url('../assets/fonts/Allison-Regular.ttf') format('truetype'); }
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

/*
  ── PALETA DE SISTEMA ──────────────────────────────────────────
  Usamos únicamente CSS system color keywords para adaptarse
  automáticamente al tema del escritorio (claro, oscuro, alto contraste).

  ButtonFace       → fondo de controles (gris claro / gris oscuro)
  ButtonHighlight  → borde iluminado superior-izquierdo
  ButtonShadow     → borde sombra inferior-derecho
  ButtonText       → texto sobre controles
  Window           → fondo de área de contenido (blanco / negro)
  WindowText       → texto sobre Window
  Canvas           → fondo de página (body)
  CanvasText       → texto general
  Highlight        → color de selección / acento activo
  HighlightText    → texto sobre Highlight
  GrayText         → texto desactivado / secundario
  ActiveCaption    → barra de título de ventana activa
  ActiveBorderText → texto sobre ActiveCaption  (alias: CaptionText)
  ─────────────────────────────────────────────────────────────*/

body{
    font-family:'ms_sans_serif','Microsoft Sans Serif',sans-serif;
    font-size:11px;
    background:Canvas;
    color:CanvasText;
    height:100vh;overflow:hidden;display:flex;flex-direction:column;
}

/* ── TOOLBAR ── */
#dnd-toolbar{
    background:ButtonFace;
    border-bottom:2px solid ButtonShadow;
    padding:4px 6px;display:flex;gap:4px;align-items:center;flex-shrink:0;flex-wrap:wrap;
}
.tb-btn{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    padding:2px 10px;cursor:pointer;
    background:ButtonFace;color:ButtonText;
    border-top:2px solid ButtonHighlight;border-left:2px solid ButtonHighlight;
    border-right:2px solid ButtonShadow;border-bottom:2px solid ButtonShadow;
    white-space:nowrap;user-select:none;
}
.tb-btn:active,.tb-btn.active{
    border-top:2px solid ButtonShadow;border-left:2px solid ButtonShadow;
    border-right:2px solid ButtonHighlight;border-bottom:2px solid ButtonHighlight;
    background:ButtonFace;filter:brightness(0.93);
}
.tb-btn:hover:not(.active):not(:active){filter:brightness(1.06);}
.tb-sep{width:1px;background:ButtonShadow;height:20px;margin:0 2px;}
.tb-spacer{flex:1;}
#status-msg{font-size:10px;color:LinkText;min-width:180px;padding:0 4px;transition:opacity .3s;}
#status-msg.fade{opacity:0;}

/* Font controls */
#font-controls{display:flex;align-items:center;gap:5px;}
#font-controls label{font-size:10px;color:GrayText;}
#font-size-slider{width:70px;cursor:pointer;accent-color:Highlight;}
#font-size-val{
    font-size:10px;width:28px;text-align:center;
    background:Window;color:WindowText;
    border:1px inset ButtonShadow;padding:1px 2px;
}

/* Tooltip global */
.has-tip{position:relative;}
.has-tip:hover::after{
    content:attr(data-tip);position:absolute;
    bottom:calc(100% + 4px);left:50%;transform:translateX(-50%);
    background:InfoBackground;color:InfoText;
    font-size:10px;padding:3px 7px;border-radius:3px;
    white-space:nowrap;z-index:9999;pointer-events:none;
    border:1px solid ButtonShadow;
}

/* ── CONTEXT MENU ── */
#field-ctx-menu{
    display:none;position:fixed;z-index:9000;
    background:ButtonFace;color:ButtonText;
    border-top:2px solid ButtonHighlight;border-left:2px solid ButtonHighlight;
    border-right:2px solid ButtonShadow;border-bottom:2px solid ButtonShadow;
    padding:8px 10px;min-width:190px;
    box-shadow:2px 2px 6px rgba(0,0,0,.35);
}
#field-ctx-menu .ctx-title{
    font-size:10px;color:GrayText;
    margin-bottom:6px;border-bottom:1px solid ButtonShadow;padding-bottom:4px;
}
#field-ctx-menu .ctx-row{display:flex;align-items:center;gap:6px;}
#ctx-fs-slider{width:90px;cursor:pointer;accent-color:Highlight;}
#ctx-fs-val{font-size:11px;font-weight:bold;width:24px;text-align:center;color:ButtonText;}
#ctx-reset{
    font-family:'ms_sans_serif',sans-serif;font-size:10px;
    padding:1px 6px;cursor:pointer;margin-top:6px;width:100%;
    background:ButtonFace;color:ButtonText;
    border-top:1px solid ButtonHighlight;border-left:1px solid ButtonHighlight;
    border-right:1px solid ButtonShadow;border-bottom:1px solid ButtonShadow;
}
#ctx-reset:hover{filter:brightness(1.06);}

/* ── TABS ── */
#page-tabs{
    background:ButtonFace;
    border-bottom:1px solid ButtonShadow;
    padding:3px 6px 0;display:flex;gap:2px;flex-shrink:0;min-height:26px;
}
.pg-tab{
    font-family:'ms_sans_serif',sans-serif;font-size:10px;
    padding:2px 14px;cursor:pointer;
    background:ButtonFace;color:ButtonText;
    border:1px solid ButtonShadow;border-bottom:none;
    border-radius:3px 3px 0 0;position:relative;top:1px;user-select:none;
}
.pg-tab:hover:not(.active){filter:brightness(1.06);}
.pg-tab.active{background:Window;color:WindowText;font-weight:bold;z-index:1;}

/* ── SHEET AREA ── */
#sheet-area{
    flex:1;overflow:auto;
    background:color-mix(in srgb, Canvas 60%, ButtonShadow 40%);
    padding:10px;display:flex;justify-content:center;align-items:flex-start;
}
.sheet-page{display:none;}
.sheet-page.visible{display:block;}
.page-canvas{position:relative;display:inline-block;line-height:0;box-shadow:3px 3px 12px rgba(0,0,0,.6);}
.page-canvas img{display:block;width:100%;user-select:none;pointer-events:none;}

/* ── CAMPOS sobre la ficha ──
   El texto se escribe con la fuente manuscrita Allison en marrón oscuro
   (que contrasta bien con el papel de la ficha) → este color NO sigue el
   tema del sistema porque es texto sobre una imagen de papel impreso. */
.page-canvas input[type="text"],
.page-canvas input[type="number"],
.page-canvas textarea{
    position:absolute;background:transparent;border:none;outline:none;
    font-family:'Allison',cursive;
    color:#1a0800;        /* tinta sobre papel: no cambia con el tema */
    caret-color:#1a0800;
    padding:0 2px;line-height:1.15;
}
/* Campos calculados: azul cobalto sobre papel */
.page-canvas input.auto-calc{color:#00008b;font-weight:bold;cursor:default;}

/* Focus y hover sobre la ficha — semitransparentes para no tapar el papel */
.page-canvas input:focus:not(.auto-calc),
.page-canvas textarea:focus{background:rgba(255,235,160,0.45);border-radius:2px;}
.page-canvas input[type="text"]:hover:not(.auto-calc):not(:focus),
.page-canvas input[type="number"]:hover:not(:focus),
.page-canvas textarea:hover:not(:focus){background:rgba(180,210,255,0.22);border-radius:2px;}

.page-canvas textarea{resize:none;overflow:hidden;}
.page-canvas input[type="checkbox"]{
    position:absolute;cursor:pointer;
    width:12px!important;height:12px!important;
    background:transparent;accent-color:Highlight;
}

/* ── MIS FICHAS ── */
#mis-fichas-panel{display:none;flex:1;flex-direction:column;overflow:hidden;background:ButtonFace;}
#mis-fichas-panel.visible{display:flex;}
#mf-toolbar{
    background:ButtonFace;border-bottom:1px solid ButtonShadow;
    padding:5px 8px;display:flex;gap:6px;align-items:center;flex-shrink:0;
}
#mf-toolbar strong{color:ButtonText;}
#mf-toolbar input{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    border:1px inset ButtonShadow;background:Window;color:WindowText;
    padding:2px 4px;flex:1;max-width:220px;
}
#mf-list{flex:1;overflow:auto;padding:10px;display:flex;flex-wrap:wrap;gap:10px;align-content:flex-start;}
.ficha-card{
    background:ButtonFace;color:ButtonText;
    border-top:2px solid ButtonHighlight;border-left:2px solid ButtonHighlight;
    border-right:2px solid ButtonShadow;border-bottom:2px solid ButtonShadow;
    width:185px;padding:8px 10px;position:relative;cursor:default;transition:filter .1s;
}
.ficha-card:hover{filter:brightness(1.05);}
.fc-tipo{font-size:9px;color:GrayText;margin-bottom:2px;}
.fc-nombre{font-size:15px;font-family:'Allison',cursive;color:#1a0800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:2px;}
.fc-info{font-size:9px;color:ButtonText;line-height:1.5;}
.fc-fecha{font-size:9px;color:GrayText;margin-top:3px;}
.fc-btns{margin-top:6px;display:flex;gap:4px;}
.fc-btns button,.fc-del{
    font-family:'ms_sans_serif',sans-serif;font-size:9px;padding:1px 6px;cursor:pointer;
    background:ButtonFace;color:ButtonText;
    border-top:1px solid ButtonHighlight;border-left:1px solid ButtonHighlight;
    border-right:1px solid ButtonShadow;border-bottom:1px solid ButtonShadow;
}
.fc-btns button:hover,.fc-del:hover{filter:brightness(1.06);}
.fc-del{position:absolute;top:4px;right:4px;padding:1px 4px;color:LinkText;}
#mf-empty{width:100%;text-align:center;color:GrayText;padding:40px 20px;line-height:2;}

/* ── BOTTOM BAR ── */
#bottom-bar{
    background:ButtonFace;border-top:2px solid ButtonShadow;
    padding:4px 8px;display:flex;gap:6px;align-items:center;flex-shrink:0;
}
#bottom-bar .info{font-size:10px;color:GrayText;flex:1;}
#autosave-dot{
    width:8px;height:8px;border-radius:50%;
    background:GrayText;display:inline-block;margin-right:4px;transition:background .3s;
}
#autosave-dot.saved{background:LinkText;}
#autosave-dot.saving{background:Mark;}

/* ── MODALES ── */
#modal-guardar,#modal-confirm{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.45);z-index:8000;
    align-items:center;justify-content:center;
}
#modal-guardar.show,#modal-confirm.show{display:flex;}
#modal-confirm{z-index:8500;}
#modal-box,#confirm-box{
    background:ButtonFace;color:ButtonText;
    border-top:2px solid ButtonHighlight;border-left:2px solid ButtonHighlight;
    border-right:2px solid ButtonShadow;border-bottom:2px solid ButtonShadow;
    min-width:300px;
}
#confirm-box{min-width:260px;}
.modal-titlebar{
    background:ActiveCaption;color:CaptionText;
    font-weight:bold;font-size:11px;padding:3px 6px;
    display:flex;justify-content:space-between;align-items:center;user-select:none;
}
.modal-titlebar button{
    background:ButtonFace;color:ButtonText;
    border-top:1px solid ButtonHighlight;border-left:1px solid ButtonHighlight;
    border-right:1px solid ButtonShadow;border-bottom:1px solid ButtonShadow;
    font-size:9px;width:16px;height:14px;cursor:pointer;
}
#modal-body{padding:12px 16px;}
#modal-body label{display:block;margin-bottom:4px;font-size:11px;color:ButtonText;}
#modal-body input{
    font-family:'ms_sans_serif',sans-serif;font-size:11px;
    border:1px inset ButtonShadow;background:Window;color:WindowText;
    padding:2px 4px;width:100%;margin-bottom:10px;
}
.modal-btns{display:flex;gap:6px;justify-content:flex-end;}
#confirm-body{padding:14px 18px;font-size:11px;line-height:1.6;color:ButtonText;}
#confirm-btns{padding:0 18px 12px;display:flex;gap:6px;justify-content:flex-end;}
#confirm-ok{color:LinkText;}

/* ── PROGRESS ── */
#progress-overlay{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.5);z-index:9999;
    align-items:center;justify-content:center;
}
#progress-overlay.show{display:flex;}
#progress-box{
    background:ButtonFace;color:ButtonText;
    border-top:2px solid ButtonHighlight;border-left:2px solid ButtonHighlight;
    border-right:2px solid ButtonShadow;border-bottom:2px solid ButtonShadow;
    padding:16px 24px;text-align:center;min-width:260px;
}
#progress-box p{margin-bottom:10px;font-size:11px;color:ButtonText;}
#pb-wrap{width:100%;height:16px;background:Window;border:1px inset ButtonShadow;}
#pb{height:100%;background:Highlight;width:0%;transition:width .25s;}

/* ── TOAST ── */
#toast{
    position:fixed;bottom:50px;left:50%;transform:translateX(-50%) translateY(20px);
    background:ButtonFace;color:ButtonText;
    border:1px solid ButtonShadow;
    font-size:11px;padding:6px 16px;border-radius:3px;
    opacity:0;transition:opacity .25s, transform .25s;
    pointer-events:none;z-index:9990;
    box-shadow:1px 1px 4px rgba(0,0,0,.3);
}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);}

/* ── UNDO INDICATOR ── */
#undo-hint{font-size:10px;color:GrayText;padding:0 6px;}
</style>
</head>
<body>

<!-- TOOLBAR -->
<div id="dnd-toolbar">
    <button class="tb-btn active has-tip" id="tab-oficial"   onclick="switchSheet('oficial')"   data-tip="Ficha oficial D&D 5e (3 páginas)">📜 Oficial</button>
    <button class="tb-btn has-tip"        id="tab-artistica" onclick="switchSheet('artistica')" data-tip="Ficha artística alternativa">🎨 Artística</button>
    <button class="tb-btn has-tip"        id="tab-misfichas" onclick="switchToMisFichas()"      data-tip="Ver todas tus fichas guardadas">📁 Mis Fichas</button>
    <div class="tb-sep"></div>
    <div id="font-controls">
        <label>Letra:</label>
        <input type="range" id="font-size-slider" min="8" max="24" value="12" step="1" oninput="changeFontSize(this.value)">
        <span id="font-size-val">12</span>
    </div>
    <div class="tb-sep"></div>
    <button class="tb-btn has-tip" onclick="undoLast()" id="btn-undo" data-tip="Deshacer último cambio (Ctrl+Z)">↩ Deshacer</button>
    <button class="tb-btn has-tip" onclick="clearAll()" data-tip="Borrar todos los campos de la ficha actual">🗑 Limpiar</button>
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
        <strong>📁 Mis Fichas</strong>
        <input type="text" id="mf-search" placeholder="Buscar por nombre..." oninput="renderFichasList()">
        <span id="mf-count" style="font-size:10px;color:#555;"></span>
    </div>
    <div id="mf-list"></div>
</div>

<!-- BOTTOM BAR -->
<div id="bottom-bar">
    <button class="tb-btn has-tip" onclick="saveLocal()" data-tip="Guardar en el navegador">💾 Guardar</button>
    <button class="tb-btn has-tip" onclick="loadLocal()" data-tip="Recargar desde el navegador">📂 Cargar</button>
    <button class="tb-btn has-tip" onclick="openModalGuardar()" data-tip="Guardar una copia nombrada">⭐ Guardar como...</button>
    <button class="tb-btn has-tip" onclick="exportPDF()" data-tip="Descargar PDF rellenado">⬇ PDF</button>
    <div class="info">
        <span id="autosave-dot"></span>Guardado automático activo
    </div>
    <span id="undo-hint"></span>
</div>

<!-- MODAL GUARDAR EN MIS FICHAS -->
<div id="modal-guardar">
    <div id="modal-box">
        <div class="modal-titlebar"><span>⭐ Guardar en Mis Fichas</span><button onclick="closeModal()">✕</button></div>
        <div id="modal-body">
            <label>Nombre para identificar esta ficha:</label>
            <input type="text" id="modal-nombre" placeholder="Ej: Aldric el Paladín" autocomplete="off">
            <div class="modal-btns">
                <button class="tb-btn" onclick="closeModal()">Cancelar</button>
                <button class="tb-btn" onclick="confirmarGuardar()">✔ Guardar</button>
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

<!-- CONTEXT MENU (clic derecho en campo) -->
<div id="field-ctx-menu">
    <div class="ctx-title" id="ctx-field-name">Campo</div>
    <div class="ctx-row">
        <span style="font-size:9px;color:#555">A</span>
        <input type="range" id="ctx-fs-slider" min="6" max="30" value="12" step="1" oninput="ctxFsChange(this.value)">
        <span style="font-size:15px;color:#333">A</span>
        <span id="ctx-fs-val">12</span>px
    </div>
    <button id="ctx-reset" onclick="ctxFsReset()">↺ Restaurar tamaño por defecto</button>
</div>

<!-- TOAST -->
<div id="toast"></div>

<script src="../assets/img/dnd/dnd_imgs.js"></script>
<script>
/* ════════════════════════════════════════
   HELPERS DE CAMPO
   f  → text    fn → number    ft → textarea
   fc → checkbox   fa → auto-calc (readonly, azul)
════════════════════════════════════════ */
function f(id,l,t,w,fs,opts)  { return Object.assign({id,l,t,w,fs:fs||12,type:'text'},opts||{}); }
function fn(id,l,t,w,fs,opts) { return Object.assign({id,l,t,w,fs:fs||12,type:'number'},opts||{}); }
function ft(id,l,t,w,h,fs)    { return {id,l,t,w,h:h||4,fs:fs||11,type:'textarea'}; }
function fc(id,l,t)            { return {id,l,t,w:1.4,h:1.4,type:'checkbox'}; }
function fa(id,l,t,w,fs)      { return {id,l,t,w,fs:fs||12,type:'text',calc:true}; }

/* ════════════════════════════════════════
   DEFINICIÓN DE FICHAS
   CORRECCIÓN: en la ficha oficial, cada stat tiene:
   - caja pequeña del MODIFICADOR  → arriba  (t menor)
   - caja grande del VALOR BASE    → abajo   (t mayor)
   Se han intercambiado las coordenadas t respecto a la versión anterior.
════════════════════════════════════════ */
var SHEETS = {
  oficial: {
    storageKey:'dnd_oficial',
    pdfUrl:'../assets/pdf/Hoja_de_personaje_Editable.pdf',
    pages:[
      { imgKey:'ofic_0', fields:[
          /* ── Cabecera ── */
          f('o-nombre',        7.6,  7.6, 32.8, 18),   /* nombre: grande y prominente */
          f('o-clase',        43.9,  5.9, 15.2, 14),   /* clase/raza: campos principales */
          f('o-transfondo',   61.7,  6.1,  9.9, 13),
          f('o-jugador',      77.4,  6.1, 14.9, 13),
          f('o-raza',         43.9,  9.5, 15.9, 14),
          f('o-alineamiento', 62.2,  9.6,  9.8, 13),
          fn('o-xp',          77.5,  9.4, 15.0,  9,{align:'center'}), /* XP: pequeño */

          /* ── Stats ──
             LAYOUT REAL D&D 5e:
               [pequeño óvalo ARRIBA]  ← MODIFICADOR calculado  → fs pequeño
               [caja grande ABAJO]     ← VALOR BASE que escribe el jugador → fs grande */
          fa('o-mod-fue',      7.8, 19.5,  3.7, 11),   /* mod: pequeño arriba */
          fn('o-fue',          5.5, 23.2,  7.0, 20,{align:'center'}), /* stat: grande abajo */

          fa('o-mod-des',      7.5, 28.4,  4.2, 11),
          fn('o-des',          5.5, 32.1,  7.0, 20,{align:'center'}),

          fa('o-mod-con',      7.8, 37.1,  3.3, 11),
          fn('o-con',          5.5, 41.0,  7.0, 20,{align:'center'}),

          fa('o-mod-int',      7.8, 46.2,  2.6, 11),
          fn('o-int',          5.5, 50.1,  7.0, 20,{align:'center'}),

          fa('o-mod-sab',      7.8, 55.1,  3.3, 11),
          fn('o-sab',          5.5, 58.9,  7.0, 20,{align:'center'}),

          fa('o-mod-car',      7.8, 63.8,  3.9, 11),
          fn('o-car',          5.5, 67.8,  7.0, 20,{align:'center'}),

          /* ── Inspiración / Competencia: números compactos ── */
          fn('o-inspiracion',  15.8, 16.4,  3.4, 11,{align:'center'}),
          fn('o-competencia',  15.8, 21.1,  3.8, 11,{align:'center'}),

          /* ── Tiradas de salvación: compactas ── */
          fc('o-sv-c-fue', 17.0, 25.8), fa('o-sv-fue', 18.3, 25.8, 2.5, 10),
          fc('o-sv-c-des', 17.0, 27.5), fa('o-sv-des', 18.4, 27.5, 2.7, 10),
          fc('o-sv-c-con', 17.0, 29.1), fa('o-sv-con', 18.6, 29.1, 2.2, 10),
          fc('o-sv-c-int', 17.0, 30.6), fa('o-sv-int', 17.9, 30.6, 3.1, 10),
          fc('o-sv-c-sab', 17.0, 32.3), fa('o-sv-sab', 18.0, 32.3, 3.0, 10),
          fc('o-sv-c-car', 17.0, 34.1), fa('o-sv-car', 18.0, 34.1, 3.0, 10),

          /* ── Habilidades: compactas ── */
          fc('o-sk-c-acro', 17.0, 40.2), fa('o-sk-acro', 18.0, 40.2, 2.7, 10),
          fc('o-sk-c-atle', 17.0, 42.2), fa('o-sk-atle', 18.2, 42.2, 2.5, 10),
          fc('o-sk-c-carc', 17.0, 43.7), fa('o-sk-carc', 18.2, 43.7, 2.6, 10),
          fc('o-sk-c-enga', 17.0, 45.4), fa('o-sk-enga', 18.0, 45.4, 2.7, 10),
          fc('o-sk-c-hist', 17.0, 47.0), fa('o-sk-hist', 17.9, 47.0, 3.3, 10),
          fc('o-sk-c-inte', 17.0, 48.9), fa('o-sk-inte', 18.0, 48.9, 2.9, 10),
          fc('o-sk-c-inti', 17.0, 50.5), fa('o-sk-inti', 18.0, 50.5, 3.0, 10),
          fc('o-sk-c-inve', 17.0, 52.2), fa('o-sk-inve', 17.9, 52.2, 3.1, 10),
          fc('o-sk-c-jdm',  17.0, 54.0), fa('o-sk-jdm',  17.8, 54.0, 3.3, 10),
          fc('o-sk-c-medi', 17.0, 55.6), fa('o-sk-medi', 17.9, 55.6, 2.9, 10),
          fc('o-sk-c-natu', 17.0, 57.3), fa('o-sk-natu', 17.9, 57.3, 3.0, 10),
          fc('o-sk-c-perc', 17.0, 59.0), fa('o-sk-perc', 17.9, 59.0, 3.1, 10),
          fc('o-sk-c-pers', 17.0, 60.5), fa('o-sk-pers', 17.9, 60.5, 3.3, 10),
          fc('o-sk-c-pers2',17.0, 62.2), fa('o-sk-pers2',17.8, 62.2, 3.3, 10),
          fc('o-sk-c-reli', 17.0, 64.0), fa('o-sk-reli', 17.8, 64.0, 3.5, 10),
          fc('o-sk-c-sigi', 17.0, 65.8), fa('o-sk-sigi', 17.8, 65.8, 3.4, 10),
          fc('o-sk-c-supe', 17.0, 67.5), fa('o-sk-supe', 18.0, 67.5, 3.0, 10),
          fc('o-sk-c-tani', 17.0, 69.1), fa('o-sk-tani', 17.9, 69.1, 3.3, 10),

          /* ── Percepción pasiva calculada ── */
          fa('o-perc-pasiva', 4.6, 74.4, 5.0, 13),

          /* ── Otras competencias: textarea de rol ── */
          ft('o-competencias', 5.4, 79.3, 27.5, 14, 11),

          /* ── Combate: números de referencia rápida ── */
          fn('o-ca',         37.6, 19.6,  5.8, 17,{align:'center'}), /* CA: grande */
          fa('o-iniciativa', 46.1, 18.9,  6.8, 17),                  /* ini: grande */
          fn('o-velocidad',  55.7, 18.8,  6.9, 15),                  /* vel: grande */

          /* ── Puntos de golpe ── */
          fn('o-pg-max',     49.9, 24.8, 12.8,  9),  /* PG máx: pequeño, es referencia */
          fn('o-pg-act',     37.5, 26.7, 25.2, 18),  /* PG actual: grande, se usa mucho */
          fn('o-pg-temp',    37.0, 33.2, 26.3, 18),  /* PG temp: grande */

          /* ── Dados de golpe ── */
          f('o-dg-total',    40.3, 40.3,  7.8, 10),
          f('o-dg-tipo',     36.9, 41.6, 12.4, 12),

          /* ── Ataques: nombre grande, bono y daño pequeños ── */
          f('o-atk1-nom',  36.6, 49.8,  9.7, 13),
          f('o-atk1-bon',  48.0, 49.9,  4.7,  9),
          f('o-atk1-dao',  53.7, 49.8,  9.3, 11),
          f('o-atk2-nom',  36.2, 52.5, 10.5, 13),
          f('o-atk2-bon',  47.8, 52.3,  5.0,  9),
          f('o-atk2-dao',  53.3, 52.4, 10.1, 11),
          f('o-atk3-nom',  35.9, 54.3, 10.5, 13),
          f('o-atk3-bon',  47.3, 54.8,  5.5,  9),
          f('o-atk3-dao',  53.1, 54.6,  9.9, 11),
          ft('o-atk-notas',36.1, 57.1, 27.5, 3.0, 10),

          /* ── Monedas: pequeñas, es contabilidad ── */
          fn('o-pc',  37.1, 75.8,  5.5,  9,{align:'center'}),
          fn('o-pe',  37.0, 79.0,  5.8,  9,{align:'center'}),
          fn('o-pp',  36.9, 82.3,  5.9,  9,{align:'center'}),
          fn('o-po',  37.3, 85.3,  5.5,  9,{align:'center'}),
          fn('o-pe2', 36.6, 88.6,  6.5,  9,{align:'center'}),
          fn('o-mon-pc', 44.1, 75.7, 4.0,  9,{align:'center'}),

          /* ── Equipo: textarea cómoda ── */
          ft('o-equipo', 67.1, 48.5, 28.8, 25.0, 11),

          /* ── Personalidad / Ideales / Vínculos / Defectos: rol, legible ── */
          ft('o-personalidad', 68.4, 18.0, 24.6,  6.5, 11),
          ft('o-ideales',      68.1, 26.8, 25.8,  5.5, 11),
          ft('o-vinculos',     67.8, 33.5, 25.8,  5.5, 11),
          ft('o-defectos',     68.1, 40.7, 25.6,  5.5, 11),

          /* ── Rasgos y atributos ── */
          ft('o-int-4',  67.1, 48.5, 28.8, 20.0, 11),
          ft('o-int-12',  5.4, 79.3, 27.5, 14.0, 11),
      ]},

      { imgKey:'ofic_1', fields:[
          f('o2-nombre',   7.1,  7.7, 34.8, 18),  /* nombre grande */
          f('o2-edad',    43.3,  6.1, 11.2, 13),
          f('o2-altura',  42.7,  8.8, 12.7, 13),
          f('o2-ojos',    56.9,  6.3, 14.9, 13),
          f('o2-piel',    57.6,  9.2, 12.0, 13),
          f('o2-peso',    77.0,  6.1, 16.7, 13),
          f('o2-pelo',    77.3,  9.2, 16.9, 13),
          ft('o2-aspecto',   5.6, 16.7, 26.9, 28.0, 11),
          ft('o2-aliados',  36.3, 16.5, 30.3, 28.0, 11),
          ft('o2-ali-sym',  68.9, 19.5, 22.9, 14.0, 11),
          ft('o2-rasgos',    5.4, 48.7, 27.5, 24.0, 11),
          ft('o2-historia',  5.4, 48.7, 27.5, 24.0, 11),
          ft('o2-tesoro',   36.5, 47.5, 58.8, 24.0, 11),
          ft('o2-rasgos2',  35.9, 76.0, 59.1, 18.0, 11),
      ]},

      { imgKey:'ofic_2', fields:[
          f('o3-clase',    7.2,  7.6, 34.5, 12),
          f('o3-apt',     46.7,  6.8, 10.2, 11,{align:'center'}),
          f('o3-cd',      62.6,  7.0, 10.8, 11,{align:'center'}),
          f('o3-bon',     79.7,  6.7, 11.0, 11,{align:'center'}),
          ft('o3-sp0',     4.8, 21.7, 28.9, 15.0, 10),
          fn('o3-e1t',    39.1, 18.5,  7.2, 10,{align:'center'}),
          f('o3-e1g',     47.6, 18.5, 16.1, 10),
          ft('o3-sp1',    37.5, 22.1, 27.5, 14.0, 10),
          fn('o3-e2t',    68.6, 18.3,  8.4, 10,{align:'center'}),
          f('o3-e2g',     77.8, 18.5, 16.9, 10),
          ft('o3-sp2',    66.8, 22.1, 28.2, 14.0, 10),
          fn('o3-e3t',     7.5, 39.5,  8.1, 10,{align:'center'}),
          f('o3-e3g',     16.1, 39.5, 17.1, 10),
          ft('o3-sp3',     6.1, 43.7, 27.7, 14.0, 10),
          fn('o3-e4t',    39.1, 46.8,  7.1, 10,{align:'center'}),
          f('o3-e4g',     48.0, 46.9, 15.6, 10),
          ft('o3-sp4',    37.3, 50.7, 27.5, 14.0, 10),
          fn('o3-e5t',    68.9, 39.7,  7.7, 10,{align:'center'}),
          f('o3-e5g',     77.8, 40.1, 17.0, 10),
          ft('o3-sp5',    66.5, 43.9, 28.5, 14.0, 10),
          fn('o3-e6t',     8.4, 68.4,  6.5, 10,{align:'center'}),
          f('o3-e6g',     16.6, 68.8, 16.1, 10),
          ft('o3-sp6',     6.4, 72.3, 26.7, 14.0, 10),
          fn('o3-e7t',    39.3, 75.7,  6.8, 10,{align:'center'}),
          f('o3-e7g',     47.6, 75.9, 15.9, 10),
          ft('o3-sp7',    37.5, 79.7, 27.2, 14.0, 10),
          fn('o3-e8t',    69.4, 61.1,  7.3, 10,{align:'center'}),
          f('o3-e8g',     78.3, 61.4, 16.1, 10),
          ft('o3-sp8',    67.8, 65.1, 26.8, 14.0, 10),
          fn('o3-e9t',    69.5, 79.2,  7.2, 10,{align:'center'}),
          f('o3-e9g',     77.8, 79.3, 16.6, 10),
          ft('o3-sp9',    67.7, 82.5, 27.1, 14.0, 10),
      ]}
    ]
  },

  artistica: {
    storageKey:'dnd_artistica',
    pdfUrl:'../assets/pdf/Hoja_DND.pdf',
    pages:[
      { imgKey:'art_0', fields:[
          f('a-nombre',      12.8, 12.9, 26.7, 16),
          f('a-clase',       55.9, 13.4, 18.4, 12),
          f('a-raza',        55.9, 16.9, 10.7, 12),
          fn('a-ini',        33.1, 22.1,  5.6, 14,{align:'center'}),
          fn('a-ca',         42.0, 22.0,  5.8, 14,{align:'center'}),
          fn('a-vel',        50.7, 22.1,  6.3, 12,{align:'center'}),
          fn('a-competencia',14.5, 29.1,  6.5, 12,{align:'center'}),

          /* Stats artística (misma lógica: mod arriba, valor abajo) */
          fa('a-mod-fue',    24.9, 28.7,  4.5, 12),
          fn('a-fue',        15.3, 36.6,  5.6, 16,{align:'center'}),
          fa('a-mod-des',    32.7, 37.0,  4.5, 12),
          fn('a-des',        15.6, 44.7,  5.8, 16,{align:'center'}),
          fa('a-mod-con',    32.7, 38.9,  4.0, 12),
          fn('a-con',        15.6, 32.9,  4.9, 16,{align:'center'}),
          fa('a-mod-int',    32.6, 40.7,  4.2, 12),
          fn('a-int',        15.5, 40.5,  5.1, 16,{align:'center'}),
          fa('a-mod-sab',    33.4, 42.5,  3.1, 12),
          fn('a-sab',        15.5, 53.0,  6.3, 16,{align:'center'}),
          fa('a-mod-car',    33.3, 44.2,  3.5, 12),
          fn('a-car',        16.3, 57.0,  5.1, 16,{align:'center'}),

          /* PG */
          fn('a-pg-max',     49.1, 28.9, 10.9, 12),
          fn('a-pg-act',     49.3, 31.0, 10.8, 14),
          fn('a-pg-temp',    47.9, 34.7, 11.7, 12),

          /* Salvaciones */
          fa('a-sv-fue', 33.4, 46.5,  3.8, 11),
          fa('a-sv-des', 40.9, 41.3, 10.3, 11),
          fa('a-sv-con', 51.9, 41.2,  9.5, 11),
          fa('a-sv-int', 63.3, 24.4, 15.8, 11),
          fa('a-sv-sab', 44.9, 62.4,  4.9, 11),
          fa('a-sv-car', 15.0, 60.3,  7.1, 11),
          fc('a-sv-c-fue', 36.4, 53.0),
          fc('a-sv-c-des', 36.6, 54.3),
          fc('a-sv-c-con', 36.7, 55.9),
          fc('a-sv-c-int', 37.0, 57.1),
          fc('a-sv-c-sab', 36.8, 58.3),
          fc('a-sv-c-car', 37.1, 59.7),

          /* Habilidades */
          fc('a-sk-c-acro', 33.5, 65.8), fa('a-sk-acro', 34.8, 65.8, 1.9, 11),
          fc('a-sk-c-atle', 33.4, 68.0), fa('a-sk-atle', 34.7, 68.0, 2.0, 11),
          fc('a-sk-c-jdm',  33.2, 70.3), fa('a-sk-jdm',  34.5, 70.3, 2.3, 11),
          fc('a-sk-c-sigi', 16.2, 66.5), fa('a-sk-sigi', 16.3, 66.5, 6.2, 11),
          fc('a-sk-c-tani', 16.0, 64.8), fa('a-sk-tani', 16.3, 64.8, 5.3, 11),
          fc('a-sk-c-agua', 15.6, 68.7), fa('a-sk-agua', 16.3, 68.7, 6.3, 11),
          fa('a-sk-perc',   40.6, 70.8, 8.1, 11),
          fa('a-sk-pers',   40.3, 74.9, 8.3, 11),
          fa('a-sk-carc',   40.7, 79.2, 8.4, 11),
          fc('a-sk-c-perc', 49.8, 70.8),
          fc('a-sk-c-pers', 49.6, 74.6),
          fc('a-sk-c-carc', 49.9, 79.1),

          fa('a-ini-calc',  16.3, 72.8, 5.0, 12),
          f('a-dg',         53.2, 70.6, 7.4, 12),

          fc('a-m-e1',40.6,70.8), fc('a-m-e2',49.8,70.8), fc('a-m-e3',53.2,70.8),
          fc('a-m-f1',40.3,74.9), fc('a-m-f2',49.6,74.9), fc('a-m-f3',52.9,74.9),

          f('a-mb-nom',40.4, 83.8, 22.4, 11),
          f('a-mm-bon',54.0, 79.1,  6.6, 11),
          ft('a-comps',  8.8, 78.2, 25.8, 16.0, 10),
          ft('a-rasgos',63.3, 24.4, 15.8, 50.0, 10),
      ]},

      { imgKey:'art_1', fields:[
          f('a2-slots',  3.0,  7.2, 14.2, 12),
          ft('a2-moch1', 5.7, 20.4, 41.7, 74.0, 11),
          ft('a2-moch2',53.5, 20.3, 40.0, 74.0, 11),
      ]},

      { imgKey:'art_2', fields:[
          f('a3-apt',   26.2,  4.0, 12.7, 12,{align:'center'}),
          f('a3-cd',    42.9,  4.4, 12.2, 12,{align:'center'}),
          f('a3-bon',   59.5,  3.8, 13.0, 12,{align:'center'}),
          ft('a3-sp0',   4.9, 20.6, 28.5, 14.0, 10),
          fn('a3-e1t',  41.0, 17.1,  5.0, 10,{align:'center'}),
          f('a3-e1g',   47.8, 17.0, 15.8, 10),
          ft('a3-sp1',  37.9, 20.8, 28.5, 14.0, 10),
          fn('a3-e2t',  72.0, 17.0,  4.9, 10,{align:'center'}),
          f('a3-e2g',   78.4, 16.9, 16.4, 10),
          ft('a3-sp2',  68.7, 20.8, 27.6, 14.0, 10),
          fn('a3-e3t',  10.2, 38.6,  4.5, 10,{align:'center'}),
          f('a3-e3g',   16.5, 38.6, 16.3, 10),
          ft('a3-sp3',   6.4, 44.8, 28.5, 14.0, 10),
          fn('a3-e4t',  40.9, 45.8,  5.6, 10,{align:'center'}),
          f('a3-e4g',   47.6, 45.8, 16.9, 10),
          ft('a3-sp4',  37.8, 49.8, 27.6, 14.0, 10),
          fn('a3-e5t',  71.9, 38.7,  4.9, 10,{align:'center'}),
          f('a3-e5g',   78.4, 38.9, 16.7, 10),
          ft('a3-sp5',  68.7, 42.6, 27.8, 14.0, 10),
          fn('a3-e6t',   9.7, 68.0,  5.7, 10,{align:'center'}),
          f('a3-e6g',   16.6, 68.8, 16.1, 10),
          ft('a3-sp6',   6.6, 72.4, 28.7, 14.0, 10),
          fn('a3-e7t',  40.8, 75.2,  4.7, 10,{align:'center'}),
          f('a3-e7g',   47.2, 75.4, 16.4, 10),
          ft('a3-sp7',  37.3, 79.3, 28.3, 14.0, 10),
          fn('a3-e8t',  71.7, 60.6,  5.8, 10,{align:'center'}),
          f('a3-e8g',   78.3, 60.8, 17.0, 10),
          ft('a3-sp8',  68.3, 64.7, 28.4, 14.0, 10),
          fn('a3-e9t',  72.3, 79.3,  5.1, 10,{align:'center'}),
          f('a3-e9g',   79.3, 79.3, 15.7, 10),
          ft('a3-sp9',  68.8, 83.0, 28.3, 14.0, 10),
      ]}
    ]
  }
};

/* ════════════════════════════════════════
   CÁLCULOS D&D 5e
════════════════════════════════════════ */
var SKILL_STAT = {
  'acro':'des','atle':'fue','carc':'int','enga':'car',
  'hist':'int','inte':'car','inti':'car','inve':'int',
  'jdm':'des','medi':'sab','natu':'int','perc':'sab',
  'pers':'sab','pers2':'car','reli':'int','sigi':'des',
  'supe':'sab','tani':'sab',
  'agua':'fue'
};
function getMod(val){ return Math.floor(((parseInt(val)||10)-10)/2); }
function fmtMod(m){ return (m>=0?'+':'')+m; }

function runCalcs(prefix){
    var p = prefix+'-';
    ['fue','des','con','int','sab','car'].forEach(function(s){
        var el  = document.getElementById(p+s);
        var mel = document.getElementById(p+'mod-'+s);
        if(el && mel) mel.value = fmtMod(getMod(el.value));
    });
    var compEl  = document.getElementById(prefix==='o'?'o-competencia':'a-competencia');
    var compVal = compEl ? (parseInt(compEl.value)||2) : 2;

    ['fue','des','con','int','sab','car'].forEach(function(s){
        var mod = getMod((document.getElementById(p+s)||{value:'10'}).value);
        var chk = document.getElementById(p+'sv-c-'+s);
        var out = document.getElementById(p+'sv-'+s);
        if(out) out.value = fmtMod(mod+(chk&&chk.checked?compVal:0));
    });

    Object.keys(SKILL_STAT).forEach(function(sk){
        var mod = getMod((document.getElementById(p+SKILL_STAT[sk])||{value:'10'}).value);
        var chk = document.getElementById(p+'sk-c-'+sk);
        var out = document.getElementById(p+'sk-'+sk);
        if(out) out.value = fmtMod(mod+(chk&&chk.checked?compVal:0));
    });

    if(prefix==='o'){
        var percEl = document.getElementById('o-sk-perc');
        var ppEl   = document.getElementById('o-perc-pasiva');
        if(ppEl) ppEl.value = 10+(percEl?(parseInt(percEl.value.replace('+',''))||0):0);
    }

    var desEl = document.getElementById(p+'des');
    var iniEl = document.getElementById(p+'iniciativa')||document.getElementById(p+'ini-calc');
    if(desEl&&iniEl) iniEl.value = fmtMod(getMod(desEl.value));
}

function attachCalcListeners(prefix){
    var p = prefix+'-';
    ['fue','des','con','int','sab','car'].forEach(function(s){
        var el = document.getElementById(p+s);
        if(el) el.addEventListener('input',function(){ runCalcs(prefix); markDirty(); });
    });
    var compEl = document.getElementById(p+'competencia');
    if(compEl) compEl.addEventListener('input',function(){ runCalcs(prefix); markDirty(); });
    document.querySelectorAll('[id^="'+p+'sv-c-"],[id^="'+p+'sk-c-"]').forEach(function(el){
        el.addEventListener('change',function(){ runCalcs(prefix); markDirty(); });
    });
    runCalcs(prefix);
}

/* ════════════════════════════════════════
   TAMAÑO DE LETRA — global + por campo
════════════════════════════════════════ */
var globalFontScale = 1.0;
var fieldFontSizes  = {};
var ctxFieldId      = null;

function changeFontSize(val){
    document.getElementById('font-size-val').textContent = val;
    globalFontScale = val/12;
    reRenderFields();
}

function reRenderFields(){
    var sheet = SHEETS[currentSheet];
    sheet.pages.forEach(function(pg,pi){
        var wrap   = document.getElementById(currentSheet+'-pg-'+pi); if(!wrap) return;
        var canvas = wrap.querySelector('.page-canvas');
        var img    = canvas&&canvas.querySelector('img');
        if(img&&img.offsetWidth) placeFields(canvas,pg.fields,img);
    });
    var stored = loadStored(SHEETS[currentSheet].storageKey);
    if(stored) setTimeout(function(){ applyData(stored); runCalcs(currentSheet==='oficial'?'o':'a'); },100);
}

function openCtxMenu(e,fieldId,currentFs){
    e.preventDefault(); e.stopPropagation();
    ctxFieldId = fieldId;
    var customFs = fieldFontSizes[fieldId]||currentFs;
    document.getElementById('ctx-field-name').textContent = '✏ '+fieldId;
    document.getElementById('ctx-fs-slider').value = customFs;
    document.getElementById('ctx-fs-val').textContent = customFs;
    var menu = document.getElementById('field-ctx-menu');
    var x=e.clientX, y=e.clientY;
    if(x+210>window.innerWidth)  x=window.innerWidth-215;
    if(y+110>window.innerHeight) y=window.innerHeight-115;
    menu.style.left=x+'px'; menu.style.top=y+'px'; menu.style.display='block';
}

function ctxFsChange(val){
    document.getElementById('ctx-fs-val').textContent = val;
    if(!ctxFieldId) return;
    fieldFontSizes[ctxFieldId] = parseInt(val);
    var el = document.getElementById(ctxFieldId);
    if(el) el.style.fontSize = val+'px';
    saveFieldFontSizes();
}

function ctxFsReset(){
    if(!ctxFieldId) return;
    delete fieldFontSizes[ctxFieldId];
    saveFieldFontSizes();
    var sheet=SHEETS[currentSheet]; var fd=null;
    sheet.pages.forEach(function(pg){ pg.fields.forEach(function(f){ if(f.id===ctxFieldId) fd=f; }); });
    if(fd){
        var fs=(fd.fs||12)*globalFontScale;
        var el=document.getElementById(ctxFieldId);
        if(el) el.style.fontSize=fs+'px';
        document.getElementById('ctx-fs-slider').value=fd.fs||12;
        document.getElementById('ctx-fs-val').textContent=fd.fs||12;
    }
}

function saveFieldFontSizes(){ try{ localStorage.setItem(SHEETS[currentSheet].storageKey+'_fs',JSON.stringify(fieldFontSizes)); }catch(e){} }
function loadFieldFontSizes(){ try{ var r=localStorage.getItem(SHEETS[currentSheet].storageKey+'_fs'); fieldFontSizes=r?JSON.parse(r):{}; }catch(e){ fieldFontSizes={}; } }

document.addEventListener('click',function(e){ var m=document.getElementById('field-ctx-menu'); if(m&&!m.contains(e.target)) m.style.display='none'; });
document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){ document.getElementById('field-ctx-menu').style.display='none'; closeModal(); }
    if((e.ctrlKey||e.metaKey)&&e.key==='z'){ e.preventDefault(); undoLast(); }
    if((e.ctrlKey||e.metaKey)&&e.key==='s'){ e.preventDefault(); saveLocal(); }
});

/* ════════════════════════════════════════
   ESTADO GLOBAL
════════════════════════════════════════ */
var currentSheet = 'oficial';
var currentPage  = 0;
var inMisFichas  = false;

/* ── Historial undo (simple: guarda snapshots) ── */
var undoStack = [];
var MAX_UNDO  = 20;

function pushUndo(){
    var snap = collectData();
    undoStack.push(JSON.stringify(snap));
    if(undoStack.length>MAX_UNDO) undoStack.shift();
}
function undoLast(){
    if(!undoStack.length){ showToast('Sin cambios para deshacer'); return; }
    var prev = JSON.parse(undoStack.pop());
    applyData(prev);
    runCalcs(currentSheet==='oficial'?'o':'a');
    showToast('↩ Deshecho');
    saveLocal(true);
}

/* ── Autosave / dirty tracking ── */
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
    dot.className = state ? 'saved' : '';
    if(state==='saving') dot.className = 'saving';
}

/* Adjuntar listeners de cambio a todos los campos normales */
function attachChangeListeners(){
    document.querySelectorAll('.page-canvas input:not(.auto-calc),.page-canvas textarea').forEach(function(el){
        el.removeEventListener('input',  fieldChanged);
        el.removeEventListener('change', fieldChanged);
        el.addEventListener('input',  fieldChanged);
        el.addEventListener('change', fieldChanged);
    });
}
var _pushUndo_timer = null;
function fieldChanged(){
    clearTimeout(_pushUndo_timer);
    _pushUndo_timer = setTimeout(pushUndo, 500);
    markDirty();
}

/* ════════════════════════════════════════
   BUILD DOM
════════════════════════════════════════ */
function buildSheet(key){
    currentPage=0; inMisFichas=false;
    loadFieldFontSizes();
    var sheet=SHEETS[key];
    var area=document.getElementById('sheet-area');
    var tabs=document.getElementById('page-tabs');
    var mfp=document.getElementById('mis-fichas-panel');
    var bbar=document.getElementById('bottom-bar');
    area.innerHTML=''; tabs.innerHTML='';
    area.style.display='flex'; bbar.style.display='flex';
    mfp.classList.remove('visible');
    undoStack=[];

    sheet.pages.forEach(function(pg,pi){
        var tab=document.createElement('button');
        tab.className='pg-tab'+(pi===0?' active':'');
        tab.textContent='Pág.'+(pi+1);
        tab.dataset.pi=pi;
        tab.onclick=function(){ showPage(pi); };
        tabs.appendChild(tab);

        var wrap=document.createElement('div');
        wrap.className='sheet-page'+(pi===0?' visible':'');
        wrap.id=key+'-pg-'+pi;
        var canvas=document.createElement('div');
        canvas.className='page-canvas';
        var img=document.createElement('img');
        img.src=(typeof DND_IMGS!=='undefined'&&DND_IMGS[pg.imgKey])?DND_IMGS[pg.imgKey]:'';
        img.alt='Página '+(pi+1);
        img.style.maxWidth=key==='oficial'?'780px':'800px';
        canvas.appendChild(img);
        function doPos(){ if(img.offsetWidth>0) placeFields(canvas,pg.fields,img); else setTimeout(doPos,60); }
        img.onload=doPos;
        if(img.complete&&img.naturalWidth>0) doPos();
        wrap.appendChild(canvas); area.appendChild(wrap);
    });

    var stored=loadStored(sheet.storageKey);
    if(stored){
        setTimeout(function(){
            applyData(stored);
            setTimeout(function(){
                runCalcs(key==='oficial'?'o':'a');
                attachChangeListeners();
                _lastSavedState=JSON.stringify(collectData());
            },150);
        },200);
    } else {
        setTimeout(function(){ attachChangeListeners(); },400);
    }

    document.getElementById('font-size-slider').value=12;
    document.getElementById('font-size-val').textContent=12;
    globalFontScale=1.0;
}

function placeFields(canvas,fields,img){
    var W=img.offsetWidth, H=img.offsetHeight;
    if(!W||!H){ setTimeout(function(){ placeFields(canvas,fields,img); },60); return; }

    fields.forEach(function(fd){
        var old=canvas.querySelector('#'+CSS.escape(fd.id));
        if(old) old.remove();
        var el;
        if(fd.type==='textarea'){ el=document.createElement('textarea'); }
        else { el=document.createElement('input'); el.type=fd.type==='checkbox'?'checkbox':(fd.type==='number'?'number':'text'); }
        el.id=fd.id;
        el.style.left  =(fd.l/100*W)+'px';
        el.style.top   =(fd.t/100*H)+'px';
        el.style.width =(fd.w/100*W)+'px';
        if(fd.h) el.style.height=(fd.h/100*H)+'px';

        var basefs = fd.fs||12;
        var fs = fieldFontSizes[fd.id]!==undefined ? fieldFontSizes[fd.id] : (basefs*globalFontScale);
        el.style.fontSize = fs+'px';
        if(fd.align) el.style.textAlign=fd.align;
        if(fd.calc){ el.readOnly=true; el.classList.add('auto-calc'); el.tabIndex=-1; }

        /* Clic derecho → menú tamaño (solo en campos editables) */
        if(fd.type!=='checkbox'){
            el.addEventListener('contextmenu',function(e){
                var curfs=fieldFontSizes[fd.id]!==undefined?fieldFontSizes[fd.id]:Math.round(basefs*globalFontScale);
                openCtxMenu(e,fd.id,curfs);
            });
        }

        /* Tab order natural (salta campos de solo lectura) */
        if(fd.calc||fd.type==='checkbox') el.tabIndex=-1;

        canvas.appendChild(el);
    });

    var sheetKey=currentSheet;
    setTimeout(function(){
        attachCalcListeners(sheetKey==='oficial'?'o':'a');
        attachChangeListeners();
    },80);
}

/* ════════════════════════════════════════
   NAVEGACIÓN
════════════════════════════════════════ */
function switchSheet(key){
    saveLocal(true); currentSheet=key;
    document.getElementById('tab-oficial').classList.toggle('active',key==='oficial');
    document.getElementById('tab-artistica').classList.toggle('active',key==='artistica');
    document.getElementById('tab-misfichas').classList.remove('active');
    buildSheet(key);
}
function switchToMisFichas(){
    saveLocal(true); inMisFichas=true;
    document.getElementById('tab-oficial').classList.remove('active');
    document.getElementById('tab-artistica').classList.remove('active');
    document.getElementById('tab-misfichas').classList.add('active');
    document.getElementById('sheet-area').style.display='none';
    document.getElementById('bottom-bar').style.display='none';
    document.getElementById('page-tabs').innerHTML='';
    document.getElementById('mis-fichas-panel').classList.add('visible');
    renderFichasList();
}
function showPage(pi){
    currentPage=pi;
    document.querySelectorAll('.pg-tab').forEach(function(t){ t.classList.toggle('active',parseInt(t.dataset.pi)===pi); });
    document.querySelectorAll('.sheet-page').forEach(function(p,i){ p.classList.toggle('visible',i===pi); });
    document.getElementById('sheet-area').scrollTop=0;
}
var _rt;
window.addEventListener('resize',function(){
    clearTimeout(_rt); _rt=setTimeout(function(){
        if(inMisFichas) return;
        SHEETS[currentSheet].pages.forEach(function(pg,pi){
            var wrap=document.getElementById(currentSheet+'-pg-'+pi); if(!wrap) return;
            var canvas=wrap.querySelector('.page-canvas');
            var img=canvas&&canvas.querySelector('img');
            if(img&&img.offsetWidth) placeFields(canvas,pg.fields,img);
        });
    },150);
});

/* ════════════════════════════════════════
   DATOS
════════════════════════════════════════ */
function collectData(){
    var data={};
    document.querySelectorAll('.page-canvas input:not(.auto-calc),.page-canvas textarea').forEach(function(el){
        if(!el.id) return;
        data[el.id]=el.type==='checkbox'?el.checked:el.value;
    });
    return data;
}
function applyData(data){
    Object.keys(data).forEach(function(id){
        var el=document.getElementById(id); if(!el) return;
        if(el.type==='checkbox') el.checked=data[id]; else el.value=data[id];
    });
}
function loadStored(key){ try{ var r=localStorage.getItem(key); return r?JSON.parse(r):null; }catch(e){ return null; } }

function saveLocal(silent){
    try{
        var data=collectData();
        localStorage.setItem(SHEETS[currentSheet].storageKey,JSON.stringify(data));
        _lastSavedState=JSON.stringify(data);
        if(!silent){ setStatus('✔ Guardado'); showToast('💾 Guardado'); setAutosaveDot('saved'); setTimeout(function(){ setAutosaveDot(''); },2000); }
    }catch(e){ if(!silent){ setStatus('✗ Error al guardar'); showToast('✗ Error al guardar'); } }
}
function loadLocal(){
    var data=loadStored(SHEETS[currentSheet].storageKey);
    if(!data){ showToast('Sin datos guardados'); return; }
    pushUndo();
    applyData(data); runCalcs(currentSheet==='oficial'?'o':'a');
    showToast('✔ Datos cargados');
}

function clearAll(){
    showConfirm('¿Borrar todos los campos de la ficha actual?<br><small style="color:#666">Esta acción es reversible con Deshacer.</small>',function(){
        pushUndo();
        document.querySelectorAll('.page-canvas input:not(.auto-calc),.page-canvas textarea').forEach(function(el){
            if(el.type==='checkbox') el.checked=false; else el.value='';
        });
        runCalcs(currentSheet==='oficial'?'o':'a');
        saveLocal(true);
        showToast('🗑 Ficha limpiada');
    });
}

window.addEventListener('beforeunload',function(){ saveLocal(true); });

/* ════════════════════════════════════════
   MIS FICHAS
════════════════════════════════════════ */
var MF_KEY='dnd_mis_fichas';
function getMisFichas(){ try{ var r=localStorage.getItem(MF_KEY); return r?JSON.parse(r):[]; }catch(e){ return []; } }
function setMisFichas(arr){ try{ localStorage.setItem(MF_KEY,JSON.stringify(arr)); }catch(e){} }

function openModalGuardar(){
    var el=document.getElementById('o-nombre')||document.getElementById('a-nombre');
    document.getElementById('modal-nombre').value=el?(el.value||''):'';
    document.getElementById('modal-guardar').classList.add('show');
    setTimeout(function(){ document.getElementById('modal-nombre').focus(); document.getElementById('modal-nombre').select(); },50);
}
function closeModal(){ document.getElementById('modal-guardar').classList.remove('show'); }
function confirmarGuardar(){
    var nombre=document.getElementById('modal-nombre').value.trim();
    if(!nombre){ document.getElementById('modal-nombre').focus(); return; }
    var fichas=getMisFichas();
    fichas.unshift({
        id:Date.now().toString(36)+Math.random().toString(36).slice(2,5),
        nombre, tipo:currentSheet, data:collectData(),
        fecha:new Date().toLocaleDateString('es-ES',{day:'2-digit',month:'2-digit',year:'numeric'})
    });
    setMisFichas(fichas); closeModal();
    showToast('⭐ Guardada: "'+nombre+'"');
}

function renderFichasList(){
    var fichas=getMisFichas();
    var busq=(document.getElementById('mf-search').value||'').toLowerCase();
    if(busq) fichas=fichas.filter(function(f){ return f.nombre.toLowerCase().indexOf(busq)!==-1; });
    document.getElementById('mf-count').textContent=fichas.length+' ficha'+(fichas.length!==1?'s':'');
    var list=document.getElementById('mf-list'); list.innerHTML='';
    if(!fichas.length){
        var e=document.createElement('div'); e.id='mf-empty';
        e.innerHTML=busq?'🔍 Sin resultados para "'+esc(busq)+'".':'📂 No hay fichas guardadas.<br>Rellena una ficha y pulsa <strong>⭐ Guardar como…</strong>';
        list.appendChild(e); return;
    }
    fichas.forEach(function(ficha){
        var card=document.createElement('div'); card.className='ficha-card';
        var d=ficha.data||{};
        var info=[d['o-clase']||d['a-clase']||'',d['o-raza']||d['a-raza']||''].filter(Boolean).join(' · ');
        card.innerHTML=
            '<button class="fc-del" title="Eliminar ficha">✕</button>'+
            '<div class="fc-tipo">'+(ficha.tipo==='oficial'?'📜 Oficial':'🎨 Artística')+'</div>'+
            '<div class="fc-nombre">'+esc(ficha.nombre)+'</div>'+
            (info?'<div class="fc-info">'+esc(info)+'</div>':'')+
            '<div class="fc-fecha">📅 '+esc(ficha.fecha)+'</div>'+
            '<div class="fc-btns"><button data-a="cargar">📂 Cargar</button><button data-a="pdf">⬇ PDF</button></div>';
        card.querySelector('.fc-del').onclick=function(e){
            e.stopPropagation();
            showConfirm('¿Eliminar la ficha <strong>'+esc(ficha.nombre)+'</strong>?',function(){
                setMisFichas(getMisFichas().filter(function(f){ return f.id!==ficha.id; }));
                renderFichasList();
                showToast('Ficha eliminada');
            });
        };
        card.querySelector('[data-a="cargar"]').onclick=function(e){
            e.stopPropagation();
            try{ localStorage.setItem(SHEETS[ficha.tipo].storageKey,JSON.stringify(ficha.data)); }catch(ex){}
            switchSheet(ficha.tipo);
            setTimeout(function(){ applyData(ficha.data); runCalcs(ficha.tipo==='oficial'?'o':'a'); showToast('✔ "'+ficha.nombre+'" cargada'); },400);
        };
        card.querySelector('[data-a="pdf"]').onclick=function(e){
            e.stopPropagation(); exportPDFFromData(ficha.tipo,ficha.data,ficha.nombre);
        };
        list.appendChild(card);
    });
}
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ════════════════════════════════════════
   PDF
════════════════════════════════════════ */
async function exportPDF(){
    saveLocal(true);
    var stored=loadStored(SHEETS[currentSheet].storageKey)||{};
    var nombre=stored['o-nombre']||stored['a-nombre']||'personaje';
    await exportPDFFromData(currentSheet,stored,nombre);
}
async function exportPDFFromData(sheetKey,stored,nombre){
    setProgress(0,'Cargando pdf-lib…'); showProg(true);
    try{
        if(!window.PDFLib) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
        const{PDFDocument,rgb}=window.PDFLib;
        const sheet=SHEETS[sheetKey];
        setProgress(10,'Cargando PDF…');
        const pdfBytes=await fetch(sheet.pdfUrl).then(r=>r.arrayBuffer());
        const srcDoc=await PDFDocument.load(pdfBytes);
        setProgress(20,'Cargando fuente…');
        const fontBytes=await fetch('../assets/fonts/Allison-Regular.ttf').then(r=>r.arrayBuffer());
        const outDoc=await PDFDocument.create();
        const font=await outDoc.embedFont(fontBytes);
        for(let pi=0;pi<sheet.pages.length;pi++){
            setProgress(20+Math.round(70*pi/sheet.pages.length),'Página '+(pi+1)+'…');
            const[cp]=await outDoc.copyPages(srcDoc,[pi]);
            outDoc.addPage(cp);
            const page=outDoc.getPage(pi);
            const{width:pW,height:pH}=page.getSize();
            sheet.pages[pi].fields.forEach(function(fd){
                if(fd.type==='checkbox') return;
                var val=stored[fd.id]; if(!val&&val!==0) return;
                val=String(val); if(!val.trim()) return;
                var xPt=(fd.l/100)*pW, yPt=pH-((fd.t/100)*pH)-(fd.fs*0.85), maxW=(fd.w/100)*pW;
                try{
                    if(fd.type==='textarea'){ val.split('\n').forEach(function(line,li){ if(!line.trim()) return; page.drawText(line,{x:xPt,y:yPt-li*fd.fs*1.35,size:fd.fs,font,color:rgb(0.1,0.05,0),maxWidth:maxW}); }); }
                    else{ page.drawText(val,{x:xPt,y:yPt,size:fd.fs,font,color:rgb(0.1,0.05,0),maxWidth:maxW}); }
                }catch(e){}
            });
        }
        setProgress(95,'Guardando…');
        const bytes=await outDoc.save();
        const a=document.createElement('a');
        a.href=URL.createObjectURL(new Blob([bytes],{type:'application/pdf'}));
        a.download=(nombre||'personaje').replace(/\s+/g,'_')+'_'+sheetKey+'.pdf';
        a.click(); URL.revokeObjectURL(a.href);
        setProgress(100,'¡Listo!');
        setTimeout(function(){ showProg(false); showToast('✔ PDF descargado'); },600);
    }catch(e){ showProg(false); showToast('✗ Error: '+e.message); console.error(e); }
}
function loadScript(src){ return new Promise(function(res,rej){ var s=document.createElement('script'); s.src=src; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }
function showProg(v){ document.getElementById('progress-overlay').classList.toggle('show',v); }
function setProgress(pct,msg){ document.getElementById('pb').style.width=pct+'%'; document.getElementById('pb-text').textContent=msg; }

/* ════════════════════════════════════════
   UI HELPERS
════════════════════════════════════════ */
var _statusTimer=null;
function setStatus(msg){
    var el=document.getElementById('status-msg');
    el.textContent=msg; el.classList.remove('fade');
    clearTimeout(_statusTimer);
    _statusTimer=setTimeout(function(){ el.classList.add('fade'); setTimeout(function(){ el.textContent=''; el.classList.remove('fade'); },300); },3000);
}

var _toastTimer=null;
function showToast(msg){
    var t=document.getElementById('toast');
    t.textContent=msg; t.classList.add('show');
    clearTimeout(_toastTimer);
    _toastTimer=setTimeout(function(){ t.classList.remove('show'); },2000);
}

/* Modal de confirmación reutilizable */
function showConfirm(html, onOk){
    document.getElementById('confirm-body').innerHTML=html;
    document.getElementById('modal-confirm').classList.add('show');
    var ok=document.getElementById('confirm-ok');
    var cancel=document.getElementById('confirm-cancel');
    function cleanup(){ document.getElementById('modal-confirm').classList.remove('show'); ok.onclick=null; cancel.onclick=null; }
    ok.onclick=function(){ cleanup(); onOk(); };
    cancel.onclick=cleanup;
}

/* Teclado modal guardar */
document.addEventListener('keydown',function(e){ if(e.key==='Escape'){ closeModal(); document.getElementById('modal-confirm').classList.remove('show'); } });
document.getElementById('modal-guardar').addEventListener('click',function(e){ if(e.target===this) closeModal(); });
document.getElementById('modal-nombre').addEventListener('keydown',function(e){ if(e.key==='Enter') confirmarGuardar(); });

/* ── INIT ── */
buildSheet('oficial');
</script>
</body>
</html>