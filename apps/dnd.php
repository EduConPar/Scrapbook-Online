<?php
/* apps/dnd.php */
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>D&D Fichas</title>
<style>
@font-face {
    font-family: 'Allison';
    src: url('../assets/fonts/Allison-Regular.ttf') format('truetype');
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'ms_sans_serif', 'Microsoft Sans Serif', sans-serif;
    font-size: 11px;
    background: #c0c0c0;
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* ── TOOLBAR ── */
#dnd-toolbar {
    background: #c0c0c0;
    border-bottom: 2px solid #808080;
    padding: 4px 6px;
    display: flex;
    gap: 4px;
    align-items: center;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.tb-btn {
    font-family: 'ms_sans_serif', sans-serif;
    font-size: 11px;
    padding: 2px 10px;
    cursor: pointer;
    background: #c0c0c0;
    border-top: 2px solid #fff;
    border-left: 2px solid #fff;
    border-right: 2px solid #808080;
    border-bottom: 2px solid #808080;
    white-space: nowrap;
}
.tb-btn:active, .tb-btn.active {
    border-top: 2px solid #808080;
    border-left: 2px solid #808080;
    border-right: 2px solid #fff;
    border-bottom: 2px solid #fff;
    background: #dfdfdf;
}
.tb-spacer { flex: 1; }
#status-msg { font-size: 10px; color: #006600; min-width: 160px; }

/* ── PAGE TABS ── */
#page-tabs {
    background: #dfdfdf;
    border-bottom: 1px solid #808080;
    padding: 3px 6px 0;
    display: flex;
    gap: 2px;
    flex-shrink: 0;
    min-height: 26px;
}
.pg-tab {
    font-family: 'ms_sans_serif', sans-serif;
    font-size: 10px;
    padding: 2px 12px;
    cursor: pointer;
    background: #c0c0c0;
    border: 1px solid #808080;
    border-bottom: none;
    border-radius: 3px 3px 0 0;
    position: relative;
    top: 1px;
}
.pg-tab.active {
    background: #fff;
    font-weight: bold;
    z-index: 1;
}

/* ── SHEET AREA ── */
#sheet-area {
    flex: 1;
    overflow: auto;
    background: #7a7a7a;
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* ── MIS FICHAS PANEL ── */
#mis-fichas-panel {
    display: none;
    flex: 1;
    overflow: auto;
    background: #c0c0c0;
    flex-direction: column;
}
#mis-fichas-panel.visible { display: flex; }

#mf-toolbar {
    background: #c0c0c0;
    border-bottom: 1px inset #808080;
    padding: 6px 8px;
    display: flex;
    gap: 6px;
    align-items: center;
    flex-shrink: 0;
}
#mf-toolbar input {
    font-family: 'ms_sans_serif', sans-serif;
    font-size: 11px;
    border-top: 1px solid #808080;
    border-left: 1px solid #808080;
    border-right: 1px solid #fff;
    border-bottom: 1px solid #fff;
    background: #fff;
    padding: 2px 4px;
    flex: 1;
    max-width: 220px;
}
#mf-list {
    flex: 1;
    overflow: auto;
    padding: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-content: flex-start;
}
.ficha-card {
    background: #dfdfdf;
    border-top: 2px solid #fff;
    border-left: 2px solid #fff;
    border-right: 2px solid #808080;
    border-bottom: 2px solid #808080;
    width: 180px;
    padding: 8px 10px;
    cursor: pointer;
    position: relative;
}
.ficha-card:hover { background: #ebebeb; }
.ficha-card .fc-tipo {
    font-size: 9px;
    color: #555;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.ficha-card .fc-nombre {
    font-size: 13px;
    font-family: 'Allison', cursive;
    color: #1a0800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 3px;
}
.ficha-card .fc-info {
    font-size: 9px;
    color: #444;
    line-height: 1.5;
}
.ficha-card .fc-fecha {
    font-size: 9px;
    color: #777;
    margin-top: 4px;
}
.ficha-card .fc-btns {
    margin-top: 6px;
    display: flex;
    gap: 4px;
}
.ficha-card .fc-btns button {
    font-family: 'ms_sans_serif', sans-serif;
    font-size: 9px;
    padding: 1px 6px;
    cursor: pointer;
    background: #c0c0c0;
    border-top: 1px solid #fff;
    border-left: 1px solid #fff;
    border-right: 1px solid #808080;
    border-bottom: 1px solid #808080;
}
.ficha-card .fc-btns button:active {
    border-top: 1px solid #808080;
    border-left: 1px solid #808080;
    border-right: 1px solid #fff;
    border-bottom: 1px solid #fff;
}
.ficha-card .fc-del {
    position: absolute;
    top: 4px; right: 4px;
    font-size: 9px;
    padding: 1px 4px;
    cursor: pointer;
    background: #c0c0c0;
    border-top: 1px solid #fff;
    border-left: 1px solid #fff;
    border-right: 1px solid #808080;
    border-bottom: 1px solid #808080;
    color: #800000;
}
#mf-empty {
    width: 100%;
    text-align: center;
    color: #555;
    padding: 40px 20px;
    font-size: 11px;
    line-height: 2;
}

/* ── PAGE ── */
.sheet-page { display: none; }
.sheet-page.visible { display: block; }

.page-canvas {
    position: relative;
    display: inline-block;
    line-height: 0;
    box-shadow: 3px 3px 12px rgba(0,0,0,.6);
}
.page-canvas img {
    display: block;
    width: 100%;
    user-select: none;
    pointer-events: none;
    max-width: 800px;
}

/* inputs sobre la imagen */
.page-canvas input[type="text"],
.page-canvas input[type="number"],
.page-canvas textarea {
    position: absolute;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'Allison', cursive;
    color: #1a0800;
    caret-color: #1a0800;
    padding: 0 2px;
    line-height: 1.15;
}
.page-canvas input:focus,
.page-canvas textarea:focus {
    background: rgba(255,235,160,0.35);
    border-radius: 2px;
}
.page-canvas textarea { resize: none; overflow: hidden; }
.page-canvas input[type="checkbox"] {
    position: absolute;
    cursor: pointer;
    width: 12px !important;
    height: 12px !important;
    background: transparent;
}

/* ── BOTTOM BAR ── */
#bottom-bar {
    background: #c0c0c0;
    border-top: 2px solid #808080;
    padding: 4px 8px;
    display: flex;
    gap: 6px;
    align-items: center;
    flex-shrink: 0;
    flex-wrap: wrap;
}
#bottom-bar .info { font-size: 10px; color: #555; flex: 1; }

/* ── MODAL GUARDAR FICHA ── */
#modal-guardar {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 8000;
    align-items: center;
    justify-content: center;
}
#modal-guardar.show { display: flex; }
#modal-box {
    background: #c0c0c0;
    border-top: 2px solid #fff;
    border-left: 2px solid #fff;
    border-right: 2px solid #808080;
    border-bottom: 2px solid #808080;
    min-width: 280px;
}
#modal-box .title-bar-dnd {
    background: #000080;
    color: #fff;
    font-weight: bold;
    font-size: 11px;
    padding: 3px 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#modal-box .title-bar-dnd button {
    background: #c0c0c0;
    border-top: 1px solid #fff;
    border-left: 1px solid #fff;
    border-right: 1px solid #808080;
    border-bottom: 1px solid #808080;
    font-size: 9px;
    width: 16px; height: 14px;
    cursor: pointer;
    line-height: 1;
}
#modal-body { padding: 14px 16px; }
#modal-body label { display: block; margin-bottom: 4px; font-size: 11px; }
#modal-body input {
    font-family: 'ms_sans_serif', sans-serif;
    font-size: 11px;
    border-top: 1px solid #808080;
    border-left: 1px solid #808080;
    border-right: 1px solid #fff;
    border-bottom: 1px solid #fff;
    background: #fff;
    padding: 2px 4px;
    width: 100%;
    margin-bottom: 10px;
}
#modal-body .modal-btns { display: flex; gap: 6px; justify-content: flex-end; }

/* progress */
#progress-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
#progress-overlay.show { display: flex; }
#progress-box {
    background: #c0c0c0;
    border-top: 2px solid #fff;
    border-left: 2px solid #fff;
    border-right: 2px solid #808080;
    border-bottom: 2px solid #808080;
    padding: 14px 22px;
    text-align: center;
    min-width: 240px;
}
#progress-box p { margin-bottom: 8px; }
#pb-wrap { width:100%; height:16px; background:#fff; border:1px solid #808080; }
#pb { height:100%; background:#000080; width:0%; transition:width .2s; }
</style>
</head>
<body>

<div id="dnd-toolbar">
    <button class="tb-btn active" id="tab-oficial"   onclick="switchSheet('oficial')">📜 Ficha Oficial</button>
    <button class="tb-btn"        id="tab-artistica" onclick="switchSheet('artistica')">🎨 Ficha Artística</button>
    <button class="tb-btn"        id="tab-misfichas" onclick="switchToMisFichas()">📁 Mis Fichas</button>
    <div class="tb-spacer"></div>
    <span id="status-msg"></span>
</div>

<div id="page-tabs"></div>

<div id="sheet-area"></div>
<div id="mis-fichas-panel">
    <div id="mf-toolbar">
        <strong style="font-size:11px;">📁 Mis Fichas guardadas</strong>
        <input type="text" id="mf-search" placeholder="Buscar por nombre..." oninput="renderFichasList()">
        <span id="mf-count" style="font-size:10px;color:#555;"></span>
    </div>
    <div id="mf-list"></div>
</div>

<div id="bottom-bar">
    <button class="tb-btn" onclick="saveLocal()">💾 Guardar</button>
    <button class="tb-btn" onclick="loadLocal()">📂 Cargar</button>
    <button class="tb-btn" id="btn-guardar-ficha" onclick="openModalGuardar()">⭐ Guardar en Mis Fichas</button>
    <button class="tb-btn" onclick="exportPDF()">⬇ Descargar PDF</button>
    <div class="info">Los datos se guardan automáticamente en el navegador.</div>
</div>

<!-- Modal guardar ficha -->
<div id="modal-guardar">
    <div id="modal-box">
        <div class="title-bar-dnd">
            <span>⭐ Guardar en Mis Fichas</span>
            <button onclick="closeModalGuardar()">✕</button>
        </div>
        <div id="modal-body">
            <label>Nombre de la ficha (para identificarla):</label>
            <input type="text" id="modal-nombre" placeholder="Ej: Aldric el Paladín">
            <div class="modal-btns">
                <button class="tb-btn" onclick="closeModalGuardar()">Cancelar</button>
                <button class="tb-btn" onclick="confirmarGuardarFicha()">✔ Guardar</button>
            </div>
        </div>
    </div>
</div>

<div id="progress-overlay">
    <div id="progress-box">
        <p id="pb-text">Generando PDF...</p>
        <div id="pb-wrap"><div id="pb"></div></div>
    </div>
</div>

<!-- Imágenes embebidas: no depende de rutas externas -->
<script src="../assets/img/dnd/dnd_imgs.js"></script>

<script>
/* ═══════════════════════════════════════════
   HELPERS DE DEFINICIÓN DE CAMPOS
═══════════════════════════════════════════ */
function f(id,l,t,w,fs,opts)  { return Object.assign({id,l,t,w,fs:fs||14,type:'text'},opts||{}); }
function fn(id,l,t,w,fs,opts) { return Object.assign({id,l,t,w,fs:fs||14,type:'number'},opts||{}); }
function ft(id,l,t,w,h,fs)    { return {id,l,t,w,h,fs:fs||11,type:'textarea'}; }
function fc(id,l,t)            { return {id,l,t,w:1.4,h:1.4,type:'checkbox'}; }

/* ═══════════════════════════════════════════
   DEFINICIÓN DE FICHAS
   Coordenadas en % sobre la imagen.
   ofic_0/1/2: 765×990 px  (612×792 pt PDF)
   art_0/1/2:  893×1263 px
═══════════════════════════════════════════ */
var SHEETS = {

  oficial: {
    storageKey: 'dnd_oficial',
    pdfUrl: '../assets/pdf/Hoja_de_personaje_Editable.pdf',
    pages: [
      { imgKey: 'ofic_0', fields: [
          // ── Cabecera ──
          f('o-nombre',       7.8,  7.3, 21.2, 14),
          f('o-clase',       29.8,  6.1, 18.8, 12),
          f('o-transfondo',  49.8,  6.1, 11.4, 12),
          f('o-jugador',     62.7,  6.1, 34.1, 12),
          f('o-raza',        29.8,  7.8, 14.1, 12),
          f('o-alineamiento',45.1,  7.8, 12.5, 12),
          fn('o-xp',         58.8,  7.8, 20.0, 12),

          // ── Stats principales ──
          fn('o-fue',    4.5, 11.8,  4.3, 18, {align:'center'}),
          fn('o-des',    4.5, 18.8,  4.3, 18, {align:'center'}),
          fn('o-con',    4.5, 25.8,  4.3, 18, {align:'center'}),
          fn('o-int',    4.5, 32.7,  4.3, 18, {align:'center'}),
          fn('o-sab',    4.5, 39.7,  4.3, 18, {align:'center'}),
          fn('o-car',    4.5, 46.7,  4.3, 18, {align:'center'}),

          // ── Modificadores ──
          f('o-mod-fue', 3.5, 14.2,  6.3, 12, {align:'center'}),
          f('o-mod-des', 3.5, 21.2,  6.3, 12, {align:'center'}),
          f('o-mod-con', 3.5, 28.2,  6.3, 12, {align:'center'}),
          f('o-mod-int', 3.5, 35.2,  6.3, 12, {align:'center'}),
          f('o-mod-sab', 3.5, 42.1,  6.3, 12, {align:'center'}),
          f('o-mod-car', 3.5, 49.1,  6.3, 12, {align:'center'}),

          // ── Inspiración / Competencia ──
          fn('o-insp', 14.5, 11.8, 5.5, 12, {align:'center'}),
          f('o-comp',  13.7, 13.8, 7.1, 12, {align:'center'}),

          // ── Tiradas de salvación ──
          fc('os-c-fue',13.0,16.2), f('os-v-fue',15.3,16.2, 6.0,11,{align:'center'}),
          fc('os-c-des',13.0,17.2), f('os-v-des',15.3,17.2, 6.0,11,{align:'center'}),
          fc('os-c-con',13.0,18.1), f('os-v-con',15.3,18.1, 6.0,11,{align:'center'}),
          fc('os-c-int',13.0,19.0), f('os-v-int',15.3,19.0, 6.0,11,{align:'center'}),
          fc('os-c-sab',13.0,19.9), f('os-v-sab',15.3,19.9, 6.0,11,{align:'center'}),
          fc('os-c-car',13.0,20.8), f('os-v-car',15.3,20.8, 6.0,11,{align:'center'}),

          // ── Habilidades ──
          fc('sk-c-acro',13.0,23.2), f('sk-v-acro',15.3,23.2, 6.0,11,{align:'center'}),
          fc('sk-c-atle',13.0,24.1), f('sk-v-atle',15.3,24.1, 6.0,11,{align:'center'}),
          fc('sk-c-carc',13.0,24.9), f('sk-v-carc',15.3,24.9, 6.0,11,{align:'center'}),
          fc('sk-c-enga',13.0,25.8), f('sk-v-enga',15.3,25.8, 6.0,11,{align:'center'}),
          fc('sk-c-hist',13.0,26.6), f('sk-v-hist',15.3,26.6, 6.0,11,{align:'center'}),
          fc('sk-c-inte',13.0,27.5), f('sk-v-inte',15.3,27.5, 6.0,11,{align:'center'}),
          fc('sk-c-inti',13.0,28.3), f('sk-v-inti',15.3,28.3, 6.0,11,{align:'center'}),
          fc('sk-c-inve',13.0,29.2), f('sk-v-inve',15.3,29.2, 6.0,11,{align:'center'}),
          fc('sk-c-jdm', 13.0,30.0), f('sk-v-jdm', 15.3,30.0, 6.0,11,{align:'center'}),
          fc('sk-c-medi',13.0,30.8), f('sk-v-medi',15.3,30.8, 6.0,11,{align:'center'}),
          fc('sk-c-natu',13.0,31.7), f('sk-v-natu',15.3,31.7, 6.0,11,{align:'center'}),
          fc('sk-c-perc',13.0,32.5), f('sk-v-perc',15.3,32.5, 6.0,11,{align:'center'}),
          fc('sk-c-pers',13.0,33.4), f('sk-v-pers',15.3,33.4, 6.0,11,{align:'center'}),
          fc('sk-c-pers2',13.0,34.2),f('sk-v-pers2',15.3,34.2, 6.0,11,{align:'center'}),
          fc('sk-c-reli',13.0,35.1), f('sk-v-reli',15.3,35.1, 6.0,11,{align:'center'}),
          fc('sk-c-sigi',13.0,35.9), f('sk-v-sigi',15.3,35.9, 6.0,11,{align:'center'}),
          fc('sk-c-supe',13.0,36.8), f('sk-v-supe',15.3,36.8, 6.0,11,{align:'center'}),
          fc('sk-c-tani',13.0,37.6), f('sk-v-tani',15.3,37.6, 6.0,11,{align:'center'}),

          fn('o-percpas', 10.4,50.0,10.7,12,{align:'center'}),
          ft('o-comps',    3.3,52.1,17.9,12.1,10),
          ft('o-equipo',  27.5,41.2,19.6,23.0,10),
          ft('o-rasgos',  48.5,41.2,48.4,23.0,10),

          // ── Combate ──
          fn('o-ca',   27.1,11.8, 4.3,16,{align:'center'}),
          fn('o-ini',  32.4,11.8, 4.5,16,{align:'center'}),
          f('o-vel',   37.9,11.8, 4.9,16,{align:'center'}),

          fn('o-pgmax', 27.3,15.0,19.8,12),
          fn('o-pgact', 27.3,16.7,19.8,16),
          fn('o-pgtemp',27.3,19.7,19.8,16),

          f('o-dgtot',  27.3,23.2, 6.4,11),
          f('o-dgval',  27.3,24.1, 6.4,11),

          fc('od-e1',37.5,23.2), fc('od-e2',39.5,23.2), fc('od-e3',41.5,23.2),
          fc('od-f1',37.5,24.3), fc('od-f2',39.5,24.3), fc('od-f3',41.5,24.3),

          // ── Ataques ──
          f('atk-n1',27.3,27.2,10.0,11), f('atk-b1',37.4,27.2,3.4,11), f('atk-d1',40.9,27.2,6.1,11),
          f('atk-n2',27.3,28.0,10.0,11), f('atk-b2',37.4,28.0,3.4,11), f('atk-d2',40.9,28.0,6.1,11),
          f('atk-n3',27.3,28.8,10.0,11), f('atk-b3',37.4,28.8,3.4,11), f('atk-d3',40.9,28.8,6.1,11),
          f('atk-n4',27.3,29.7,10.0,11), f('atk-b4',37.4,29.7,3.4,11), f('atk-d4',40.9,29.7,6.1,11),
          ft('o-atk-notas',27.3,30.6,19.8,3.5,9),

          // ── Monedas ──
          f('coin-pc',  27.3,33.0, 2.4,11,{align:'center'}),
          f('coin-pe',  30.2,33.0, 2.4,11,{align:'center'}),
          f('coin-ppt', 32.9,33.0, 2.4,11,{align:'center'}),
          f('coin-po',  35.7,33.0, 2.4,11,{align:'center'}),
          f('coin-pp',  38.4,33.0, 2.4,11,{align:'center'}),

          // ── Personalidad ──
          ft('o-raspers',48.5,11.8,48.4, 6.4,10),
          ft('o-ideales', 48.5,18.5,48.4, 5.8,10),
          ft('o-vinculos',48.5,24.5,48.4, 5.6,10),
          ft('o-defectos',48.5,30.5,48.4, 5.9,10),
      ]},
      { imgKey: 'ofic_1', fields: [
          // ── Cabecera ──
          f('o2-nombre',  7.8,  6.5, 21.2,14),
          f('o2-edad',   29.4,  5.8, 12.2,12),
          f('o2-altura', 43.5,  5.8, 12.2,12),
          f('o2-peso',   57.3,  5.8, 13.3,12),
          f('o2-ojos',   29.4,  7.3, 12.2,12),
          f('o2-piel',   43.5,  7.3, 12.2,12),
          f('o2-pelo',   57.3,  7.3, 13.3,12),
          // ── Secciones ──
          ft('o2-aspecto',   2.7,  9.4, 23.1,24.5,10),
          ft('o2-aliados',  27.1,  9.4, 69.8,24.5,10),
          ft('o2-aliado-nombre', 55.5, 10.0, 13.2, 4.0, 10),
          ft('o2-rasgos',    2.7, 34.8, 23.1,13.6,10),
          ft('o2-historia',  2.7, 49.7, 23.1,24.8,10),
          ft('o2-tesoro',   27.1, 49.7, 69.8,14.5,10),
          ft('o2-rasgos2',  27.1, 34.8, 69.8,13.6,10),
      ]},
      { imgKey: 'ofic_2', fields: [
          // ── Cabecera conjuros ──
          f('o3-clase',  2.7,  3.3, 21.6,12),
          f('o3-apt',   35.7,  3.3,  5.9,12,{align:'center'}),
          f('o3-cd',    46.3,  3.3,  5.5,12,{align:'center'}),
          f('o3-bon',   58.0,  3.3,  7.1,12,{align:'center'}),
          // ── Trucos (Nivel 0) ──
          ft('o3-trucos', 2.7,  7.0, 22.0,11.8,10),
          // ── Nivel 1 ──
          f('o3-e1t',    4.3, 20.6,  8.6,11,{align:'center'}),
          f('o3-e1g',   13.7, 20.6, 10.6,11,{align:'center'}),
          ft('o3-sp1',   2.7, 21.9, 22.0, 9.0,9),
          // ── Nivel 2 ──
          f('o3-e2t',    4.3, 31.9,  8.6,11,{align:'center'}),
          f('o3-e2g',   13.7, 31.9, 10.6,11,{align:'center'}),
          ft('o3-sp2',   2.7, 33.2, 22.0, 8.6,9),
          // ── Nivel 3 ──
          f('o3-e3t',   27.8,  7.0,  8.2,11,{align:'center'}),
          f('o3-e3g',   36.5,  7.0, 12.2,11,{align:'center'}),
          ft('o3-sp3',  26.3,  8.4, 22.4,11.0,9),
          // ── Nivel 4 ──
          f('o3-e4t',   27.8, 20.6,  8.2,11,{align:'center'}),
          f('o3-e4g',   36.5, 20.6, 12.2,11,{align:'center'}),
          ft('o3-sp4',  26.3, 21.9, 22.4, 9.0,9),
          // ── Nivel 5 ──
          f('o3-e5t',   27.8, 31.9,  8.2,11,{align:'center'}),
          f('o3-e5g',   36.5, 31.9, 12.2,11,{align:'center'}),
          ft('o3-sp5',  26.3, 33.2, 22.4, 6.8,9),
          // ── Nivel 6 ──
          f('o3-e6t',   52.2,  7.0,  8.2,11,{align:'center'}),
          f('o3-e6g',   60.8,  7.0, 12.9,11,{align:'center'}),
          ft('o3-sp6',  50.6,  8.4, 23.1,11.0,9),
          // ── Nivel 7 ──
          f('o3-e7t',   52.2, 20.6,  8.2,11,{align:'center'}),
          f('o3-e7g',   60.8, 20.6, 12.9,11,{align:'center'}),
          ft('o3-sp7',  50.6, 21.9, 23.1, 7.2,9),
          // ── Nivel 8 ──
          f('o3-e8t',   52.2, 29.7,  8.2,11,{align:'center'}),
          f('o3-e8g',   60.8, 29.7, 12.9,11,{align:'center'}),
          ft('o3-sp8',  50.6, 30.9, 23.1, 6.1,9),
          // ── Nivel 9 ──
          f('o3-e9t',   52.2, 37.9,  8.2,11,{align:'center'}),
          f('o3-e9g',   60.8, 37.9, 12.9,11,{align:'center'}),
          ft('o3-sp9',  50.6, 39.1, 23.1, 7.0,9),
      ]}
    ]
  },

  artistica: {
    storageKey: 'dnd_artistica',
    pdfUrl: '../assets/pdf/Hoja_DND.pdf',
    pages: [
      { imgKey: 'art_0', fields: [
          // ── Cabecera ──
          f('a-nombre',  2.8,  4.7, 35.0, 18),
          f('a-clase',  54.6,  4.5, 33.5, 13),
          f('a-raza',   54.6,  6.2, 33.5, 13),
          f('a-xp',      4.6,  6.9,  7.4, 12),
          f('a-lvl',    13.5,  6.9,  5.8, 12),

          // ── Combate cabecera ──
          fn('a-ini', 28.5,  9.0,  5.0, 16, {align:'center'}),
          fn('a-ca',  36.8,  9.0,  5.0, 16, {align:'center'}),
          f('a-vel',  45.2,  9.0,  5.5, 16, {align:'center'}),
          f('a-comp', 20.0, 12.0, 11.0, 13, {align:'center'}),

          // ── PG ──
          fn('a-pgmax',  54.0, 13.2, 22.0, 12),
          fn('a-pgact',  54.0, 14.7, 22.0, 12),
          fn('a-pgtemp', 54.0, 16.5, 22.0, 12),

          // ── Stats ──
          fn('a-fue',  2.8, 15.2,  7.0, 20, {align:'center'}),
          fn('a-des',  2.8, 19.6,  7.0, 20, {align:'center'}),
          fn('a-con',  2.8, 24.7,  7.0, 20, {align:'center'}),
          fn('a-int',  2.8, 29.3,  7.0, 20, {align:'center'}),
          fn('a-sab',  2.8, 34.1,  7.0, 20, {align:'center'}),
          fn('a-car',  2.8, 39.0,  7.0, 20, {align:'center'}),

          // ── Salvaciones ──
          f('a-sv-fue', 18.8, 15.9, 6.5, 11, {align:'center'}),
          f('a-sv-des', 18.8, 20.1, 6.5, 11, {align:'center'}),
          f('a-sv-con', 18.8, 25.1, 6.5, 11, {align:'center'}),
          f('a-sv-int', 18.8, 29.8, 6.5, 11, {align:'center'}),
          f('a-sv-sab', 18.8, 34.5, 6.5, 11, {align:'center'}),
          f('a-sv-car', 18.8, 39.4, 6.5, 11, {align:'center'}),

          // ── Habilidades activas ──
          f('a-acro',  30.5, 44.5, 8.5, 11, {align:'center'}),
          f('a-agua',  30.5, 46.4, 8.5, 11, {align:'center'}),
          f('a-atle',  30.5, 48.4, 8.5, 11, {align:'center'}),
          f('a-jdm',   30.5, 50.3, 8.5, 11, {align:'center'}),
          f('a-sigi',  30.5, 52.2, 8.5, 11, {align:'center'}),
          f('a-tani',  30.5, 54.1, 8.5, 11, {align:'center'}),

          // ── Habilidades pasivas ──
          f('a-percepc', 30.5, 58.8, 8.5, 11, {align:'center'}),
          f('a-perspic', 30.5, 60.8, 8.5, 11, {align:'center'}),
          f('a-c-arc',   30.5, 62.8, 8.5, 11, {align:'center'}),

          // ── Dados de golpe ──
          f('a-dg',     54.5, 59.8, 11.5, 12),

          // ── Salvación contra muerte ──
          fc('a-e1',69.0,63.2), fc('a-e2',72.0,63.2), fc('a-e3',75.0,63.2),
          fc('a-f1',69.0,65.7), fc('a-f2',72.0,65.7), fc('a-f3',75.0,65.7),

          // ── Armas ──
          f('a-mb-nom', 54.0, 65.0, 20.5, 12),
          f('a-mb-bon', 75.5, 65.0,  7.5, 12, {align:'center'}),
          f('a-mb-dao', 84.0, 65.0,  9.5, 12, {align:'center'}),
          f('a-mm-nom', 54.0, 69.5, 20.5, 12),
          f('a-mm-bon', 75.5, 69.5,  7.5, 12, {align:'center'}),
          f('a-mm-dao', 84.0, 69.5,  9.5, 12, {align:'center'}),
          f('a-fu-nom', 54.0, 74.3, 20.5, 12),
          f('a-fu-bon', 75.5, 74.3,  7.5, 12, {align:'center'}),
          f('a-fu-dao', 84.0, 74.3,  9.5, 12, {align:'center'}),

          // ── Extra ──
          ft('a-armas-extra', 54.0, 78.5, 40.5, 4.0, 10),
          ft('a-comps',        2.8, 64.8, 18.5,32.5, 10),
          ft('a-rasgos',      91.5, 11.0,  5.5,85.5, 10),

          // ── Heridas / Efectos ──
          ft('a-heridas',  35.0, 20.5, 15.0, 28.0, 10),
          ft('a-efectos',  52.0, 20.5, 10.0, 28.0, 10),
      ]},
      { imgKey: 'art_1', fields: [
          // ── Slots ──
          f('a2-slots-total',  2.5,  3.5, 18.0, 13),
          // ── Mochilas ──
          ft('a2-moch1',  2.5, 22.5, 44.5, 74.0, 12),
          ft('a2-moch2', 51.0, 22.5, 46.5, 74.0, 12),
      ]},
      { imgKey: 'art_2', fields: [
          // ── Cabecera conjuros ──
          f('a3-apt',  12.0,  3.5, 16.0, 13, {align:'center'}),
          f('a3-cd',   35.0,  3.5, 16.0, 13, {align:'center'}),
          f('a3-bon',  57.5,  3.5, 16.0, 13, {align:'center'}),
          // ── Nivel 0 Trucos ──
          ft('a3-trucos', 2.5, 18.0, 28.5, 12.5, 11),
          // ── Nivel 1 ──
          f('a3-e1t',  7.0, 37.5, 9.0,12,{align:'center'}),
          f('a3-e1g', 17.5, 37.5, 9.0,12,{align:'center'}),
          ft('a3-sp1', 2.5, 39.5, 28.5,17.0, 10),
          // ── Nivel 2 ──
          f('a3-e2t',  7.0, 58.5, 9.0,12,{align:'center'}),
          f('a3-e2g', 17.5, 58.5, 9.0,12,{align:'center'}),
          ft('a3-sp2', 2.5, 60.5, 28.5,13.0, 10),
          // ── Nivel 3 ──
          f('a3-e3t', 36.5, 18.5, 9.0,12,{align:'center'}),
          f('a3-e3g', 47.0, 18.5, 9.0,12,{align:'center'}),
          ft('a3-sp3',33.0, 20.5, 31.0,17.0, 10),
          // ── Nivel 4 ──
          f('a3-e4t', 36.5, 43.0, 9.0,12,{align:'center'}),
          f('a3-e4g', 47.0, 43.0, 9.0,12,{align:'center'}),
          ft('a3-sp4',33.0, 45.0, 31.0,15.0, 10),
          // ── Nivel 5 ──
          f('a3-e5t', 36.5, 63.0, 9.0,12,{align:'center'}),
          f('a3-e5g', 47.0, 63.0, 9.0,12,{align:'center'}),
          ft('a3-sp5',33.0, 65.0, 31.0,10.0, 10),
          // ── Nivel 6 ──
          f('a3-e6t', 68.5, 18.5, 9.0,12,{align:'center'}),
          f('a3-e6g', 79.0, 18.5, 9.0,12,{align:'center'}),
          ft('a3-sp6',65.0, 20.5, 33.0,17.0, 10),
          // ── Nivel 7 ──
          f('a3-e7t', 68.5, 41.5, 9.0,12,{align:'center'}),
          f('a3-e7g', 79.0, 41.5, 9.0,12,{align:'center'}),
          ft('a3-sp7',65.0, 43.5, 33.0,13.0, 10),
          // ── Nivel 8 ──
          f('a3-e8t', 68.5, 59.5, 9.0,12,{align:'center'}),
          f('a3-e8g', 79.0, 59.5, 9.0,12,{align:'center'}),
          ft('a3-sp8',65.0, 61.5, 33.0,10.0, 10),
          // ── Nivel 9 ──
          f('a3-e9t', 68.5, 74.0, 9.0,12,{align:'center'}),
          f('a3-e9g', 79.0, 74.0, 9.0,12,{align:'center'}),
          ft('a3-sp9',65.0, 76.0, 33.0,10.0, 10),
      ]}
    ]
  }
};


/* ═══════════════════════════════════════════
   ESTADO
═══════════════════════════════════════════ */
var currentSheet = 'oficial';
var currentPage  = 0;
var inMisFichas  = false;

/* ═══════════════════════════════════════════
   BUILD DOM
═══════════════════════════════════════════ */
function buildSheet(key) {
    currentPage = 0;
    inMisFichas = false;
    var sheet = SHEETS[key];
    var area  = document.getElementById('sheet-area');
    var tabs  = document.getElementById('page-tabs');
    var mfp   = document.getElementById('mis-fichas-panel');

    area.innerHTML = '';
    tabs.innerHTML = '';
    area.style.display = 'flex';
    mfp.classList.remove('visible');
    document.getElementById('bottom-bar').style.display = 'flex';

    sheet.pages.forEach(function(pg, pi) {
        // Tab
        var tab = document.createElement('button');
        tab.className = 'pg-tab' + (pi === 0 ? ' active' : '');
        tab.textContent = 'Pág. ' + (pi + 1);
        tab.dataset.pi = pi;
        tab.onclick = function() { showPage(pi); };
        tabs.appendChild(tab);

        // Wrapper página
        var wrap = document.createElement('div');
        wrap.className = 'sheet-page' + (pi === 0 ? ' visible' : '');
        wrap.id = key + '-pg-' + pi;

        var canvas = document.createElement('div');
        canvas.className = 'page-canvas';

        var img = document.createElement('img');
        img.src = (typeof DND_IMGS !== 'undefined' && DND_IMGS[pg.imgKey])
                    ? DND_IMGS[pg.imgKey]
                    : '';
        img.alt = 'Página ' + (pi + 1);
        img.style.maxWidth = key === 'oficial' ? '780px' : '800px';

        canvas.appendChild(img);

        function doPosition() {
            if (img.offsetWidth > 0) {
                placeFields(canvas, pg.fields, img);
            } else {
                setTimeout(doPosition, 60);
            }
        }
        img.onload = doPosition;
        if (img.complete && img.naturalWidth > 0) doPosition();

        wrap.appendChild(canvas);
        area.appendChild(wrap);
    });

    var stored = loadStored(sheet.storageKey);
    if (stored) setTimeout(function(){ applyData(stored); }, 200);
}

function placeFields(canvas, fields, img) {
    var W = img.offsetWidth;
    var H = img.offsetHeight;
    if (!W || !H) { setTimeout(function(){ placeFields(canvas,fields,img); }, 60); return; }

    fields.forEach(function(fd) {
        var old = canvas.querySelector('#' + CSS.escape(fd.id));
        if (old) old.remove();

        var el;
        if (fd.type === 'textarea') {
            el = document.createElement('textarea');
        } else {
            el = document.createElement('input');
            el.type = fd.type === 'checkbox' ? 'checkbox' : (fd.type === 'number' ? 'number' : 'text');
        }
        el.id = fd.id;
        el.style.left   = (fd.l / 100 * W) + 'px';
        el.style.top    = (fd.t / 100 * H) + 'px';
        el.style.width  = (fd.w / 100 * W) + 'px';
        if (fd.h)     el.style.height    = (fd.h  / 100 * H) + 'px';
        if (fd.fs)    el.style.fontSize  = fd.fs + 'px';
        if (fd.align) el.style.textAlign = fd.align;

        canvas.appendChild(el);
    });
}

/* ═══════════════════════════════════════════
   NAVEGACIÓN
═══════════════════════════════════════════ */
function switchSheet(key) {
    saveLocal(true);
    currentSheet = key;
    document.getElementById('tab-oficial').classList.toggle('active', key === 'oficial');
    document.getElementById('tab-artistica').classList.toggle('active', key === 'artistica');
    document.getElementById('tab-misfichas').classList.remove('active');
    buildSheet(key);
}

function switchToMisFichas() {
    saveLocal(true);
    inMisFichas = true;
    document.getElementById('tab-oficial').classList.remove('active');
    document.getElementById('tab-artistica').classList.remove('active');
    document.getElementById('tab-misfichas').classList.add('active');

    var area = document.getElementById('sheet-area');
    var mfp  = document.getElementById('mis-fichas-panel');
    var tabs = document.getElementById('page-tabs');
    area.style.display = 'none';
    mfp.classList.add('visible');
    tabs.innerHTML = '';
    document.getElementById('bottom-bar').style.display = 'none';
    renderFichasList();
}

function showPage(pi) {
    currentPage = pi;
    document.querySelectorAll('.pg-tab').forEach(function(t){
        t.classList.toggle('active', parseInt(t.dataset.pi) === pi);
    });
    document.querySelectorAll('.sheet-page').forEach(function(p, i){
        p.classList.toggle('visible', i === pi);
    });
    document.getElementById('sheet-area').scrollTop = 0;
}

/* resize */
var _rt;
window.addEventListener('resize', function(){
    clearTimeout(_rt);
    _rt = setTimeout(function(){
        if (inMisFichas) return;
        var sheet = SHEETS[currentSheet];
        sheet.pages.forEach(function(pg, pi){
            var wrap = document.getElementById(currentSheet+'-pg-'+pi);
            if (!wrap) return;
            var canvas = wrap.querySelector('.page-canvas');
            var img    = canvas.querySelector('img');
            if (img && img.offsetWidth) placeFields(canvas, pg.fields, img);
        });
    }, 150);
});

/* ═══════════════════════════════════════════
   DATOS (sesión actual)
═══════════════════════════════════════════ */
function collectData() {
    var data = {};
    document.querySelectorAll('.page-canvas input, .page-canvas textarea').forEach(function(el){
        if (!el.id) return;
        data[el.id] = el.type === 'checkbox' ? el.checked : el.value;
    });
    return data;
}

function applyData(data) {
    Object.keys(data).forEach(function(id){
        var el = document.getElementById(id);
        if (!el) return;
        if (el.type === 'checkbox') el.checked = data[id];
        else el.value = data[id];
    });
}

function loadStored(key) {
    try { var r = localStorage.getItem(key); return r ? JSON.parse(r) : null; }
    catch(e){ return null; }
}

function saveLocal(silent) {
    try {
        localStorage.setItem(SHEETS[currentSheet].storageKey, JSON.stringify(collectData()));
        if (!silent) setStatus('✔ Guardado');
    } catch(e){ if (!silent) setStatus('✗ Error al guardar'); }
}

function loadLocal() {
    var data = loadStored(SHEETS[currentSheet].storageKey);
    if (!data) { setStatus('Sin datos guardados'); return; }
    applyData(data);
    setStatus('✔ Cargado');
}

window.addEventListener('beforeunload', function(){ saveLocal(true); });

/* ═══════════════════════════════════════════
   MIS FICHAS — localStorage
   Clave: 'dnd_mis_fichas' → array de objetos
   { id, nombre, tipo, data, fecha }
═══════════════════════════════════════════ */
var MF_KEY = 'dnd_mis_fichas';

function getMisFichas() {
    try { var r = localStorage.getItem(MF_KEY); return r ? JSON.parse(r) : []; }
    catch(e){ return []; }
}

function setMisFichas(arr) {
    try { localStorage.setItem(MF_KEY, JSON.stringify(arr)); } catch(e){}
}

function openModalGuardar() {
    // Pre-rellenar con el nombre del personaje si existe
    var nombreCampo = document.getElementById('o-nombre') || document.getElementById('a-nombre');
    var nombreVal = nombreCampo ? (nombreCampo.value || '') : '';
    document.getElementById('modal-nombre').value = nombreVal;
    document.getElementById('modal-guardar').classList.add('show');
    document.getElementById('modal-nombre').focus();
}

function closeModalGuardar() {
    document.getElementById('modal-guardar').classList.remove('show');
}

function confirmarGuardarFicha() {
    var nombre = document.getElementById('modal-nombre').value.trim();
    if (!nombre) { alert('Por favor, escribe un nombre para la ficha.'); return; }

    var fichas = getMisFichas();
    var data   = collectData();
    var ficha  = {
        id:     Date.now().toString(36) + Math.random().toString(36).slice(2,6),
        nombre: nombre,
        tipo:   currentSheet,
        data:   data,
        fecha:  new Date().toLocaleDateString('es-ES', {day:'2-digit',month:'2-digit',year:'numeric'})
    };
    fichas.unshift(ficha);
    setMisFichas(fichas);
    closeModalGuardar();
    setStatus('✔ Ficha guardada en Mis Fichas');
}

function renderFichasList() {
    var fichas = getMisFichas();
    var busq   = (document.getElementById('mf-search').value || '').toLowerCase().trim();
    var list   = document.getElementById('mf-list');
    var count  = document.getElementById('mf-count');

    if (busq) {
        fichas = fichas.filter(function(f){ return f.nombre.toLowerCase().indexOf(busq) !== -1; });
    }

    count.textContent = fichas.length + ' ficha' + (fichas.length !== 1 ? 's' : '');
    list.innerHTML = '';

    if (!fichas.length) {
        var empty = document.createElement('div');
        empty.id = 'mf-empty';
        if (busq) {
            empty.innerHTML = '🔍 No se encontraron fichas con ese nombre.';
        } else {
            empty.innerHTML = '📂 Aún no tienes fichas guardadas.<br>Rellena una ficha y pulsa <strong>⭐ Guardar en Mis Fichas</strong>.';
        }
        list.appendChild(empty);
        return;
    }

    fichas.forEach(function(ficha) {
        var card = document.createElement('div');
        card.className = 'ficha-card';

        var tipoLabel = ficha.tipo === 'oficial' ? '📜 Oficial' : '🎨 Artística';
        // Extraer info básica de la ficha
        var data = ficha.data || {};
        var clase = data['o-clase'] || data['a-clase'] || '';
        var raza  = data['o-raza']  || data['a-raza']  || '';
        var infoTxt = [clase, raza].filter(Boolean).join(' · ');

        card.innerHTML =
            '<button class="fc-del" title="Eliminar ficha">✕</button>' +
            '<div class="fc-tipo">' + tipoLabel + '</div>' +
            '<div class="fc-nombre">' + escHtml(ficha.nombre) + '</div>' +
            (infoTxt ? '<div class="fc-info">' + escHtml(infoTxt) + '</div>' : '') +
            '<div class="fc-fecha">📅 ' + escHtml(ficha.fecha) + '</div>' +
            '<div class="fc-btns">' +
                '<button data-action="cargar">📂 Cargar</button>' +
                '<button data-action="exportar">⬇ PDF</button>' +
            '</div>';

        // Botón eliminar
        card.querySelector('.fc-del').addEventListener('click', function(e){
            e.stopPropagation();
            if (!confirm('¿Eliminar la ficha "' + ficha.nombre + '"?')) return;
            var arr = getMisFichas().filter(function(f){ return f.id !== ficha.id; });
            setMisFichas(arr);
            renderFichasList();
        });

        // Botón cargar
        card.querySelector('[data-action="cargar"]').addEventListener('click', function(e){
            e.stopPropagation();
            // Guardar en el storage de sesión del tipo correspondiente y abrir esa ficha
            try { localStorage.setItem(SHEETS[ficha.tipo].storageKey, JSON.stringify(ficha.data)); } catch(ex){}
            switchSheet(ficha.tipo);
            setTimeout(function(){
                applyData(ficha.data);
                setStatus('✔ Ficha "' + ficha.nombre + '" cargada');
            }, 400);
        });

        // Botón exportar PDF
        card.querySelector('[data-action="exportar"]').addEventListener('click', function(e){
            e.stopPropagation();
            exportPDFFromData(ficha.tipo, ficha.data, ficha.nombre);
        });

        list.appendChild(card);
    });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ═══════════════════════════════════════════
   EXPORTAR PDF  (pdf-lib)
═══════════════════════════════════════════ */
async function exportPDF() {
    saveLocal(true);
    var data   = loadStored(SHEETS[currentSheet].storageKey) || {};
    var nombre = data['o-nombre'] || data['a-nombre'] || 'personaje';
    await exportPDFFromData(currentSheet, data, nombre);
}

async function exportPDFFromData(sheetKey, stored, nombre) {
    setProgress(0, 'Cargando pdf-lib...');
    showProg(true);
    try {
        if (!window.PDFLib) {
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js');
        }
        const { PDFDocument, rgb } = window.PDFLib;
        const sheet = SHEETS[sheetKey];

        setProgress(10, 'Cargando PDF original...');
        const pdfBytes = await fetch(sheet.pdfUrl).then(r => r.arrayBuffer());
        const srcDoc   = await PDFDocument.load(pdfBytes);

        setProgress(20, 'Cargando fuente...');
        const fontBytes = await fetch('../assets/fonts/Allison-Regular.ttf').then(r => r.arrayBuffer());

        const outDoc = await PDFDocument.create();
        const font   = await outDoc.embedFont(fontBytes);
        const n = sheet.pages.length;

        for (let pi = 0; pi < n; pi++) {
            setProgress(20 + Math.round(70 * pi / n), 'Página ' + (pi+1) + '...');
            const [cp] = await outDoc.copyPages(srcDoc, [pi]);
            outDoc.addPage(cp);
            const page = outDoc.getPage(pi);
            const { width: pW, height: pH } = page.getSize();

            sheet.pages[pi].fields.forEach(function(fd) {
                if (fd.type === 'checkbox') return;
                var val = stored[fd.id];
                if (!val && val !== 0) return;
                val = String(val);
                if (!val.trim()) return;

                var xPt = (fd.l / 100) * pW;
                var yPt = pH - ((fd.t / 100) * pH) - (fd.fs * 0.85);
                var maxW = (fd.w / 100) * pW;

                try {
                    if (fd.type === 'textarea') {
                        val.split('\n').forEach(function(line, li) {
                            if (!line.trim()) return;
                            page.drawText(line, {
                                x: xPt, y: yPt - li * fd.fs * 1.35,
                                size: fd.fs, font, color: rgb(0.1,0.05,0), maxWidth: maxW
                            });
                        });
                    } else {
                        page.drawText(val, {
                            x: xPt, y: yPt,
                            size: fd.fs, font, color: rgb(0.1,0.05,0), maxWidth: maxW
                        });
                    }
                } catch(e) {}
            });
        }

        setProgress(95, 'Guardando...');
        const bytes = await outDoc.save();
        const blob  = new Blob([bytes], { type: 'application/pdf' });
        const a     = document.createElement('a');
        a.href      = URL.createObjectURL(blob);
        a.download  = (nombre || 'personaje').replace(/\s+/g,'_') + '_' + sheetKey + '.pdf';
        a.click();
        URL.revokeObjectURL(a.href);
        setProgress(100, '¡Listo!');
        setTimeout(function(){ showProg(false); setStatus('✔ PDF descargado'); }, 600);
    } catch(e) {
        showProg(false);
        setStatus('✗ Error: ' + e.message);
        console.error(e);
    }
}

function loadScript(src) {
    return new Promise(function(res,rej){
        var s = document.createElement('script');
        s.src = src; s.onload = res; s.onerror = rej;
        document.head.appendChild(s);
    });
}

function showProg(v) { document.getElementById('progress-overlay').classList.toggle('show',v); }
function setProgress(pct, msg) {
    document.getElementById('pb').style.width = pct + '%';
    document.getElementById('pb-text').textContent = msg;
}
function setStatus(msg) {
    var el = document.getElementById('status-msg');
    el.textContent = msg;
    setTimeout(function(){ el.textContent = ''; }, 3000);
}

/* ── Cerrar modal con Escape ── */
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeModalGuardar();
});
document.getElementById('modal-guardar').addEventListener('click', function(e){
    if (e.target === this) closeModalGuardar();
});
document.getElementById('modal-nombre').addEventListener('keydown', function(e){
    if (e.key === 'Enter') confirmarGuardarFicha();
});

/* ── INIT ── */
buildSheet('oficial');
</script>
</body>
</html>