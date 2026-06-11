/* ─────────────────────────────────────────────────────────────────
   notif-sound.js
   Helper compartido para reproducir assets/sound/notificacion.wav
   con throttle, autoplay-safe. Lo usan el shell mobile y el perfil
   mobile/desktop para que tanto chats nuevos como notificaciones
   suenen igual.

   Uso:
       window.playNotifSound();   // se ignora si han pasado <1.5s

   Notas:
   - Una sola instancia de Audio (lazy) → reutilizable.
   - `play()` rechazado por autoplay-policy se traga en .catch.
   - Si el archivo no carga (red, 404), .play() resuelve igual; el
     siguiente intento creará un Audio nuevo solo si el actual quedó
     en estado 'error' (readyState=0 + networkState=NO_SOURCE).
   ───────────────────────────────────────────────────────────────── */
(function () {
    'use strict';
    if (window.playNotifSound) return; /* ya cargado */

    var _audio = null;
    var _lastAt = 0;
    var THROTTLE_MS = 1500;
    /* Resolver contra document.baseURI para que respete <base href>
       de páginas que viven en subcarpetas (p.ej. /apps/mobile/X.php
       o /desktops/X-desktop.php). */
    var SRC = new URL('assets/sound/notificacion.wav', document.baseURI).href;

    function ensureAudio() {
        if (_audio && _audio.networkState !== 3 /* NO_SOURCE */) return _audio;
        try {
            _audio = new Audio(SRC);
            _audio.preload = 'auto';
        } catch (_) { _audio = null; }
        return _audio;
    }

    window.playNotifSound = function () {
        var now = Date.now();
        if (now - _lastAt < THROTTLE_MS) return;
        _lastAt = now;
        var a = ensureAudio();
        if (!a) return;
        try {
            a.currentTime = 0;
            var p = a.play();
            if (p && typeof p.catch === 'function') p.catch(function () {});
        } catch (_) {}
    };
})();
