/* ──────────────────────────────────────────────────────────────────────
   COVER FALLBACK — si la foto de un item de lista no carga, busca otra
   ──────────────────────────────────────────────────────────────────────
   Cualquier <img data-cover-cat="movies" data-cover-title="Dune"> que
   falle llega aquí ANTES que su onerror propio (listener en fase de
   captura sobre document) y se pide al servidor que la arregle
   (assets/profile/api.php?action=fix-cover):
     - Si devuelve otra foto → se pone en la misma <img>.
     - Si no → se relanza el error y corre el fallback de siempre de esa
       <img> (icono de categoría, emoji…).
   Un solo intento por <img>: si la nueva también falla, va directa al
   fallback.

   MhCover.url(src) resuelve las rutas relativas al root del proyecto
   ("assets/profile/cover.php?h=…") desde páginas sin <base href>.
   ────────────────────────────────────────────────────────────────────── */
(function(){
    if (window.MhCover) return;

    /* Root del proyecto a partir de la URL de este mismo script. */
    var me   = document.currentScript;
    var ROOT = me ? me.src.replace(/assets\/js\/cover-fallback\.js(?:[?#].*)?$/, '') : '';
    var API  = ROOT + 'assets/profile/api.php?action=fix-cover';
    var pending = {};   /* misma foto en varias <img> → una sola petición */

    /* Como mucho 2 peticiones a la vez: con muchas fotos caídas en la misma
       pantalla no queremos una ráfaga contra el servidor (hosting
       compartido con tope de procesos, ver db.php). */
    var queue = [], active = 0;
    function next() {
        while (active < 2 && queue.length) {
            active++;
            queue.shift()().then(function(){ active--; next(); });
        }
    }
    function enqueue(task) {
        return new Promise(function(resolve){
            queue.push(function(){ return task().then(resolve); });
            next();
        });
    }

    function url(src) {
        src = String(src || '');
        if (src === '' || /^(?:[a-z][a-z0-9+.-]*:|\/)/i.test(src)) return src;
        return ROOT + src.replace(/^(?:\.\.\/)+/, '');
    }

    function fix(cat, title, src) {
        var key = cat + '\n' + title.toLowerCase() + '\n' + src;
        if (!pending[key]) {
            pending[key] = enqueue(function(){
                return fetch(API, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ category: cat, title: title, image: src })
                })
                    .then(function(r){ return r.ok ? r.json() : null; })
                    .then(function(d){ return (d && d.image) || null; })
                    .catch(function(){ return null; });
            });
        }
        return pending[key];
    }

    document.addEventListener('error', function(ev){
        var img = ev.target;
        if (!img || img.tagName !== 'IMG' || img._mhCoverTried) return;
        var cat   = img.getAttribute('data-cover-cat');
        var title = img.getAttribute('data-cover-title');
        var src   = img.getAttribute('src') || '';
        if (!cat || !title || !src) return;
        img._mhCoverTried = true;
        /* Que no llegue al onerror de la <img> mientras buscamos otra. */
        ev.stopImmediatePropagation();
        fix(cat, title, src).then(function(next){
            if (next && url(next) !== src) img.src = url(next);
            else img.dispatchEvent(new Event('error'));
        });
    }, true);

    window.MhCover = { url: url };
})();
