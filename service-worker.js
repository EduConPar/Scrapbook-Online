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
const SW_VERSION = 'v14';

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
    event.waitUntil((async function() {
        var data = {};
        try { data = event.data ? event.data.json() : {}; }
        catch (_) { data = { title: 'Melon Hub', body: event.data ? event.data.text() : 'Nuevo mensaje' }; }

        var title   = data.title || 'Melon Hub';
        var fromKey = data.from || '';

        /* SUPRESIÓN: si la notificación es un chat y algún cliente abierto
           tiene ese chat focused + visible, no la mostramos. El usuario ya
           está leyendo los mensajes en vivo (poll de chat).
           Solo aplica si hay `from` — sin remitente no hay nada que matchear. */
        if (fromKey && await shouldSuppressChat(fromKey)) return;

        var opts = {
            body:  data.body  || '',
            icon:  data.icon  || '/scrapbookOnline/assets/img/mobile/icon-192.png',
            badge: '/scrapbookOnline/assets/img/mobile/notification-badge.png?v=' + SW_VERSION,
            tag:   data.tag   || ('chat:' + fromKey),
            renotify: true,
            vibrate: [200, 100, 200],
            silent:  false,
            data: { url: data.url || '/scrapbookOnline/mobile.php' }
        };
        try {
            await self.registration.showNotification(title, opts);
        } catch (_) {
            await self.registration.showNotification(title, { body: opts.body, tag: opts.tag });
        }
    })());
});

/* ── Supresión de notificaciones de chat activas ──
   El SW envía un postMessage a CADA cliente abierto (parent shell +
   iframes mobile/) preguntándoles vía MessageChannel si están viendo
   actualmente el chat con `fromKey`. Si cualquiera responde true en
   menos de 300 ms, se considera "leído en vivo" y no se muestra la
   notificación del SO.
   Si ningún cliente responde (PWA cerrada, app en background…) se
   muestra siempre — los timeouts cuentan como "no focused". */
async function shouldSuppressChat(fromKey) {
    try {
        var clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        if (!clients.length) return false;
        var votes = await Promise.all(clients.map(function(c) { return askClient(c, fromKey); }));
        return votes.some(Boolean);
    } catch (_) {
        return false;
    }
}
function askClient(client, fromKey) {
    return new Promise(function(resolve) {
        var ch;
        try { ch = new MessageChannel(); } catch (_) { resolve(false); return; }
        var done = false;
        var t = setTimeout(function() {
            if (done) return;
            done = true;
            resolve(false);
        }, 300);
        ch.port1.onmessage = function(e) {
            if (done) return;
            done = true;
            clearTimeout(t);
            resolve(!!(e.data && e.data.focused));
        };
        try {
            client.postMessage({ type: 'sw:is-chat-focused', fromKey: fromKey }, [ch.port2]);
        } catch (_) {
            clearTimeout(t);
            done = true;
            resolve(false);
        }
    });
}

/* ── Mensajes del cliente para LIMPIAR notificaciones ──
   Las apps (perfil-mobile chat, calendario-mobile invite, etc.) envían
   un postMessage al SW cuando el usuario "responde" a la notificación
   abriendo el chat o aceptando/rechazando una invitación. El SW busca
   notificaciones activas que matcheen y las cierra → desaparecen de la
   barra del SO.

   Forma de los mensajes:
     { type: 'clear-notifications', tag: 'chat:user1' }       → cierra solo ese tag
     { type: 'clear-notifications', tagPrefix: 'chat:' }      → cierra todos los chat:*
     { type: 'clear-notifications', urlContains: '/calendario' } → cierra las que apuntan a calendario
     { type: 'clear-notifications', all: true }               → cierra TODAS */
self.addEventListener('message', function(event) {
    var d = (event.data || {});
    if (d.type !== 'clear-notifications') return;
    event.waitUntil(
        self.registration.getNotifications().then(function(notifs) {
            notifs.forEach(function(n) {
                if (d.all) { n.close(); return; }
                if (d.tag && n.tag === d.tag) { n.close(); return; }
                if (d.tagPrefix && n.tag && n.tag.indexOf(d.tagPrefix) === 0) { n.close(); return; }
                if (d.urlContains && n.data && n.data.url && n.data.url.indexOf(d.urlContains) !== -1) {
                    n.close();
                }
            });
        })
    );
});

/* ── Notification click ──
   Reglas:
     1. Si hay un cliente que YA está en el shell (mobile.php) y la URL
        de la notif solo cambia el hash, mandamos postMessage para que
        el shell procese el deep-link in-place (más fiable que
        Client.navigate() — algunos browsers no disparan hashchange tras
        navigate() cuando la URL antigua y la nueva difieren solo en el
        fragmento, y otros lo simulan inconsistentemente).
     2. Si hay shell pero el path/query difieren, usamos navigate() para
        ir a la URL completa.
     3. Si no hay shell pero hay otros clientes scrapbookOnline (iframe
        u otra app), preferimos enfocar y navigate.
     4. Sin clientes vivos → openWindow.
   Siempre intentamos focus() al final.

   Adicional: incluso si navigate() funciona, también enviamos un
   postMessage con el deep-link. El shell tiene un listener que re-dispara
   el handler del hash aunque sea el mismo (evita el caso "notif del
   mismo chat 2 veces seguidas → no abre la segunda"). */
function urlsDifferOnlyByHash(a, b) {
    try {
        var ua = new URL(a, self.location.origin);
        var ub = new URL(b, self.location.origin);
        return ua.origin === ub.origin
            && ua.pathname === ub.pathname
            && ua.search   === ub.search
            && ua.hash     !== ub.hash;
    } catch (_) { return false; }
}
function isShellUrl(u) {
    /* El shell del móvil acaba en /mobile.php (con o sin query/hash). */
    try { return new URL(u, self.location.origin).pathname.endsWith('/mobile.php'); }
    catch (_) { return false; }
}
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil((async function() {
        var list = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        var scrapClients = list.filter(function(c){ return c.url.indexOf('scrapbookOnline') !== -1; });
        if (!scrapClients.length) {
            return self.clients.openWindow(url);
        }
        /* Preferir el shell sobre iframes embebidos / otras apps. */
        var shell = scrapClients.find(function(c){ return isShellUrl(c.url); });
        var target = shell || scrapClients[0];
        try {
            if (shell && urlsDifferOnlyByHash(shell.url, url)) {
                /* Solo cambia el hash: postMessage es más fiable.
                   También intentamos navigate() por si el shell no
                   procesa el postMessage (paranoia defensiva). */
                try { shell.postMessage({ type: 'sw:deep-link', url: url }); } catch (_) {}
                try { if ('navigate' in shell) shell.navigate(url).catch(function(){}); } catch (_) {}
            } else if ('navigate' in target) {
                await target.navigate(url).catch(function(){});
                try { target.postMessage({ type: 'sw:deep-link', url: url }); } catch (_) {}
            }
        } catch (_) {}
        try { await target.focus(); } catch (_) {}
        return target;
    })());
});
