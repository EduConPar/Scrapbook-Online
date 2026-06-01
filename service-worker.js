/* ──────────────────────────────────────────────────────────────────────
   Scrapbook Melon — Service Worker mínimo
   ──────────────────────────────────────────────────────────────────────
   Solo existe para que Chrome cumpla el criterio "PWA tiene SW con
   fetch handler" y muestre el botón "Instalar app".  No cacheamos nada
   porque la app depende de sesión PHP — servir páginas viejas tras
   logout sería peor que cero offline support.
   ────────────────────────────────────────────────────────────────────── */

self.addEventListener('install', function() {
    /* Activa el nuevo SW inmediatamente sin esperar a que todas las
       pestañas viejas se cierren. */
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    /* Toma control de los clientes existentes en cuanto se activa, y de
       paso limpia caches viejas si las hubiera de versiones anteriores. */
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then(function(names) {
                return Promise.all(names.map(function(n) { return caches.delete(n); }));
            })
        ])
    );
});

/* Fetch handler vacío: deja que el navegador haga la petición normal.
   Chrome requiere que exista uno para considerar el sitio PWA-elegible,
   aunque no haga nada. NO usar e.respondWith() — eso fuerza al SW a
   intermediar y aquí no queremos. */
self.addEventListener('fetch', function() {});
