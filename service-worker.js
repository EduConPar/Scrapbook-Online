/* ──────────────────────────────────────────────────────────────────────
   Scrapbook Melon — Service Worker
   ──────────────────────────────────────────────────────────────────────
   1) install/activate: skipWaiting + claim → toma control inmediato.
   2) push: muestra notificación del sistema con el payload del server.
   3) notificationclick: enfoca o abre la PWA en la URL del payload.

   VERSION bump cada vez que cambia el código de este archivo. El
   navegador compara byte-a-byte; si difiere, registra como SW nuevo,
   skipWaiting → activate. La página padre detecta el cambio vía
   `controllerchange` y se recarga sola.
   ────────────────────────────────────────────────────────────────────── */
const SW_VERSION = 'v10';

self.addEventListener('install', function() {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then(function(names) {
                return Promise.all(names.map(function(n) { return caches.delete(n); }));
            }),
            self.clients.matchAll({ type: 'window' }).then(function(list) {
                list.forEach(function(c){
                    try { c.postMessage({ type: 'sw-activated', version: SW_VERSION }); } catch (_) {}
                });
            })
        ])
    );
});

/* Fetch handler vacío: deja que el navegador haga la petición normal.
   Chrome requiere que exista uno para considerar el sitio PWA-elegible. */
self.addEventListener('fetch', function() {});

/* ── Push ──
   El payload viene como JSON { title, body, url, icon? }. Si no se puede
   parsear, fallback a texto plano. La notificación usa `tag` para que
   varios mensajes del mismo chat se colapsen en una sola entrada del SO.
   Si showNotification falla por algún motivo (icon URL inválida, etc.),
   reintentamos con un payload mínimo para garantizar que algo se vea. */
self.addEventListener('push', function(event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; }
    catch (_) { data = { title: 'Melon Hub', body: event.data ? event.data.text() : 'Nuevo mensaje' }; }

    var title = data.title || 'Melon Hub';
    var opts = {
        body:  data.body  || '',
        /* icon = avatar del remitente cuando viene en el payload; si no,
           cae al icono escalado de la app en assets/img/mobile/. */
        icon:  data.icon  || '/scrapbookOnline/assets/img/mobile/icon-192.png',
        /* badge = silueta de sandía (PNG transparente con solo el medio
           disco opaco). Android lo pinta en monocromo blanco en la barra
           de estado → reemplaza la campana por defecto de Chrome. Es el
           silhouette generado con generate-watermelon-badge.php. */
        /* Query string `?v=` para invalidar cache del PNG cuando regenero
           el badge — sin esto el browser cachea la versión vieja. */
        badge: '/scrapbookOnline/assets/img/mobile/notification-badge.png?v=' + SW_VERSION,
        tag:   data.tag   || ('chat:' + (data.from || '')),
        renotify: true,
        vibrate: [200, 100, 200],
        silent:  false,
        data: { url: data.url || '/scrapbookOnline/apps/perfil-mobile.php' }
    };
    event.waitUntil(
        self.registration.showNotification(title, opts).catch(function(){
            return self.registration.showNotification(title, { body: opts.body, tag: opts.tag });
        })
    );
});

/* ── Notification click ──
   Si ya hay una pestaña abierta de la PWA la enfocamos (y navegamos si
   se puede); si no, abrimos una nueva. */
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(list) {
            for (var i = 0; i < list.length; i++) {
                var c = list[i];
                if (c.url.indexOf('scrapbookOnline') !== -1) {
                    if ('navigate' in c) c.navigate(url).catch(function(){});
                    return c.focus();
                }
            }
            return self.clients.openWindow(url);
        })
    );
});
