/* ──────────────────────────────────────────────────────────────────────
   interface-loader.js
   ──────────────────────────────────────────────────────────────────────
   API mínima del lado cliente para el sistema de interfaces.

   El PHP (assets/php/active-interface.php) ya emite el <link> correcto
   server-side leyendo la cookie `activeInterface`. Este script solo
   expone helpers para CAMBIAR de interfaz desde la app de Temas:

     window.setActiveInterface(name)
       - Guarda en cookie + localStorage
       - Recarga el shell completo (window.top.location.reload) para
         que el nuevo CSS se aplique en todas las ventanas y iframes.

     window.getActiveInterface()
       - Lee la cookie / localStorage actual.
   ──────────────────────────────────────────────────────────────────── */
(function() {
    'use strict';

    function readCookie(name) {
        var parts = (document.cookie || '').split(';');
        for (var i = 0; i < parts.length; i++) {
            var kv = parts[i].split('=');
            var k = (kv[0] || '').trim();
            if (k === name) return decodeURIComponent((kv[1] || '').trim());
        }
        return '';
    }
    function writeCookie(name, value) {
        var oneYear = 60 * 60 * 24 * 365;
        document.cookie = name + '=' + encodeURIComponent(value) +
                          '; path=/; max-age=' + oneYear + '; SameSite=Lax';
    }

    window.getActiveInterface = function() {
        var c = readCookie('activeInterface');
        if (c) return c;
        try { return localStorage.getItem('activeInterface') || 'win98'; }
        catch (_) { return 'win98'; }
    };

    /* NOTA: usamos `applyInterfacePack` (no `setActiveInterface`) para
       evitar colisión con la función local del mismo nombre que vive
       en `apps/temas.php` — esa hace POST al endpoint de inventario
       antiguo y nos pisaría la global causando 400 Bad Request. */
    window.applyInterfacePack = function(name) {
        if (!name) return;
        /* Sanitiza: solo letras/números/_/- — el PHP también valida pero
           es buena práctica filtrar en el origen. */
        name = String(name).replace(/[^A-Za-z0-9_-]/g, '');
        if (!name) return;
        writeCookie('activeInterface', name);
        try { localStorage.setItem('activeInterface', name); } catch (_) {}
        /* Recarga TODO el shell para que el PHP re-emita el <link> y
           todos los iframes hijos también re-renderen con el nuevo CSS. */
        try { window.top.location.reload(); }
        catch (_) { location.reload(); }
    };

    /* ── Inyección de font-scale.js ──
       Todas las apps cargan interface-loader.js; aprovechamos para
       cargar también font-scale.js (el escalado global de fuentes
       según el tema). En iframes leemos el delta del padre (mismo
       origen) antes de inyectar el script — así el primer paint del
       iframe ya tiene el escalado correcto y no hay flash. */
    (function loadFontScale() {
        /* Heredar delta del padre si vivimos en un iframe. */
        if (typeof window.__fontScaleDelta !== 'number') {
            try {
                var parentDelta = window.parent && window.parent.__fontScaleDelta;
                if (typeof parentDelta === 'number') {
                    window.__fontScaleDelta = parentDelta;
                }
            } catch (_) { /* cross-origin → seguimos con 0 */ }
        }
        /* Path absoluto al asset, derivado del propio script para no
           depender de paths relativos al documento. */
        var thisScript = document.currentScript;
        var src = '/assets/js/font-scale.js';
        if (thisScript && thisScript.src) {
            try { src = new URL('font-scale.js', thisScript.src).href; }
            catch (_) {}
        }
        var s = document.createElement('script');
        s.src = src;
        s.async = false;
        document.head.appendChild(s);
    })();
})();
