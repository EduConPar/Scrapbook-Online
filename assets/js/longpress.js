/* ─────────────────────────────────────────────────────────────────
   LONG-PRESS → CONTEXTMENU
   Util compartido para tablets. Sin esto, el menú contextual de
   los iconos del escritorio, mascota, etc. solo aparece con
   click derecho de ratón → invisible en tablet.

   Uso:
     window.longPressMenu.attach(elemento, {
         delay:   500,        // ms para considerar long-press
         move:    8,          // px de tolerancia antes de cancelar
         enabled: () => true, // gate opcional (p.ej. body.is-tablet)
     });

   El target recibe un evento `contextmenu` sintético en la posición
   del touch al pasar el umbral. El `pointerup` posterior NO dispara
   click (lo cancelamos con preventDefault en el siguiente click).
   ───────────────────────────────────────────────────────────────── */
(function () {
    'use strict';

    function attach(el, opts) {
        if (!el) return;
        opts = opts || {};
        var delay   = opts.delay   != null ? opts.delay   : 500;
        var moveTol = opts.move    != null ? opts.move    : 8;
        var enabled = opts.enabled || function () { return true; };

        var timer = null;
        var startX = 0, startY = 0;
        var pid = -1;
        var fired = false;

        function reset() {
            if (timer) { clearTimeout(timer); timer = null; }
            pid   = -1;
            fired = false;
        }

        el.addEventListener('pointerdown', function (e) {
            /* Solo touch / pen — el ratón ya tiene `contextmenu` nativo. */
            if (e.pointerType !== 'touch' && e.pointerType !== 'pen') return;
            if (!enabled()) return;
            reset();
            pid    = e.pointerId;
            startX = e.clientX;
            startY = e.clientY;
            timer  = setTimeout(function () {
                fired = true;
                /* contextmenu sintético en la posición del touch — los
                   listeners de contextmenu existentes se disparan tal
                   cual. */
                var ev = new MouseEvent('contextmenu', {
                    bubbles:    true,
                    cancelable: true,
                    clientX:    startX,
                    clientY:    startY,
                    button:     2,
                });
                el.dispatchEvent(ev);
            }, delay);
        });

        el.addEventListener('pointermove', function (e) {
            if (e.pointerId !== pid) return;
            var dx = Math.abs(e.clientX - startX);
            var dy = Math.abs(e.clientY - startY);
            if (dx > moveTol || dy > moveTol) reset();
        });

        function cancel(e) {
            if (e && e.pointerId !== pid) return;
            reset();
        }
        el.addEventListener('pointerup',     cancel);
        el.addEventListener('pointercancel', cancel);
        el.addEventListener('pointerleave',  cancel);

        /* Si disparamos contextmenu, el click subsiguiente debe morir
           para que el icono no abra la app cuando solo queríamos su
           menú contextual. */
        el.addEventListener('click', function (e) {
            if (fired) {
                fired = false;
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    }

    window.longPressMenu = { attach: attach };
})();
