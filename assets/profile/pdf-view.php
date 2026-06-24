<?php
/* ─────────────────────────────────────────────────────────────────────
   pdf-view.php — visor de PDF con PDF.js (render a canvas).
   Se embebe en un <iframe>. Renderiza el PDF servido por pdf-proxy.php
   (mismo origen). Funciona embebido también en móvil (Chrome/Safari no
   tienen visor inline para iframes con PDFs externos).
   ───────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
requireAuth();
$rawUrl = (string)($_GET['url'] ?? '');
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=6, user-scalable=yes">
<title>PDF</title>
<style>
    html, body { margin: 0; height: 100%; background: #525659; }
    #pv-scroll { height: 100%; overflow: auto; -webkit-overflow-scrolling: touch; box-sizing: border-box; padding: 10px 8px 24px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
    #pv-scroll canvas { max-width: 100%; height: auto; background: #fff; box-shadow: 0 1px 6px rgba(0,0,0,0.5); }
    #pv-msg { color: #eee; font: 14px/1.4 system-ui, sans-serif; text-align: center; padding: 28px 16px; }
    #pv-msg a { color: #9cd1ff; }
</style>
</head>
<body>
<div id="pv-scroll"><div id="pv-msg">Cargando PDF…</div></div>
<script type="module">
    import * as pdfjsLib from '../vendor/pdfjs/pdf.min.mjs';
    pdfjsLib.GlobalWorkerOptions.workerSrc = '../vendor/pdfjs/pdf.worker.min.mjs';

    var RAW = <?php echo json_encode($rawUrl, JSON_UNESCAPED_SLASHES); ?>;
    var SRC = 'pdf-proxy.php?url=' + encodeURIComponent(RAW);
    var scroll = document.getElementById('pv-scroll');
    var msg    = document.getElementById('pv-msg');

    function fail(extra) {
        msg.innerHTML = 'No se pudo cargar el PDF.' +
            (extra ? '<br><small>' + extra + '</small>' : '') +
            '<br><br><a href="' + RAW.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener">Abrir en una pestaña nueva</a>';
    }

    (async function () {
        try {
            var pdf = await pdfjsLib.getDocument({ url: SRC, withCredentials: true }).promise;
            if (msg.parentNode) msg.parentNode.removeChild(msg);
            /* Escala adaptada al ancho disponible (nítido en pantallas HiDPI). */
            var dpr = Math.min(2, window.devicePixelRatio || 1);
            var targetW = Math.min(scroll.clientWidth - 16, 1100);
            for (var p = 1; p <= pdf.numPages; p++) {
                var page = await pdf.getPage(p);
                var base = page.getViewport({ scale: 1 });
                var scale = (targetW / base.width) * dpr;
                var vp = page.getViewport({ scale: scale });
                var canvas = document.createElement('canvas');
                canvas.width = Math.floor(vp.width);
                canvas.height = Math.floor(vp.height);
                canvas.style.width = (vp.width / dpr) + 'px';
                scroll.appendChild(canvas);
                await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
            }
        } catch (e) {
            fail(e && e.message ? e.message : '');
        }
    })();
</script>
</body>
</html>
