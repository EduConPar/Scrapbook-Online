# 08 — PWA, móvil y notificaciones push

La Melon Hub funciona como Progressive Web App instalable en móvil, con notificaciones push del SO, modo standalone sin barra del navegador, y un layout completamente distinto del escritorio (feature-phone).

## Detección de móvil/tablet

`assets/mobile-detect.php`:

```php
function isMobileDevice(): bool {
    if (isset($_GET['desktop'])) return false;
    if (!empty($_COOKIE['force_desktop']) && $_COOKIE['force_desktop'] === '1') return false;
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') return false;
    if (preg_match('/iPad|Tablet/i', $ua)) return false; // tablets entran al desktop
    return (bool)preg_match('/Mobi|Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
}

function isTabletDevice(): bool {
    if (isset($_GET['tablet'])) return $_GET['tablet'] === '1';
    if (!empty($_COOKIE['force_tablet']) && $_COOKIE['force_tablet'] === '1') return true;
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (preg_match('/iPad|Tablet|Kindle|Silk|PlayBook/i', $ua)) return true;
    if (preg_match('/Android/i', $ua) && !preg_match('/Mobile/i', $ua)) return true;
    return false;
}
```

Overrides: `?desktop=1`, `?tablet=1`, cookies `force_desktop=1`, `force_tablet=1`. Útil para debug.

## Mobile-landing

`mobile-landing.php`: pantalla que aparece cuando un móvil entra a la URL principal SIN tener la PWA instalada (no es `display-mode: standalone`).

- Muestra animación con instrucciones tipo "Comparte → Añadir a pantalla inicio".
- iOS y Android tienen flows distintos; se detecta el UA y se muestra el correcto.
- Botón "Ya la tengo instalada" → redirige a `mobile.php`.
- Si detecta que YA es standalone, redirige automáticamente.

## Shell móvil (`mobile.php`)

Feature-phone con:

- Header: avatar del user + saludo + hora.
- Grid/lista de apps lanzables (cada `apps/mobile/<app>-mobile.php`).
- Status bar inferior con botón "Ajustes".
- Polling de notifs (mensajes, recordatorios) que dispara sonido + actualiza badges.

### SPA shell con iframe

```html
<div id="shell-app" hidden>
    <iframe id="app-frame" allow="autoplay; encrypted-media; clipboard-write; web-share"></iframe>
</div>
```

Tap en una app → `openApp(url, name)` muestra `#shell-app` con el iframe cargado. Cuando la app quiere volver al menú, manda `postMessage({type: 'shell:back'})` al parent.

Allow attributes: `autoplay` y `encrypted-media` para YouTube, `clipboard-write` para copiar, `web-share` para Web Share API del wrapped.

### Long-press menu

`assets/js/longpress.js` añade soporte de "mantener pulsado" en iconos → abre un menú contextual (similar al click derecho del desktop).

### Notificaciones del shell

- Polling `loadUnreadChats` cada 10 s → suma total + dispara sonido al subir.
- Polling de recordatorios cada 5 min → muestra toast Win98 inferior + sonido.
- Pequeño badge rojo en el icono "Chat" del menú con el contador total.

## Apps móviles standalone (`apps/mobile/*-mobile.php`)

Cada app del desktop tiene su versión móvil simplificada. Algunas comparten endpoints con su contrapartida desktop (perfil, chat, calendario, temas, tienda).

Convenciones:

- Mismo HTML class (`mh-body`, `mh-window`, etc.) → estilos en `assets/css/mobile-theme.css`.
- Páginas autónomas con `<base href="../../">` para resolver URLs relativas.
- Cargan `assets/js/notif-sound.js` para reproducir el sonido al recibir notifs.
- `--mh-vh` variable CSS para evitar el bug del 100vh móvil (la barra del navegador descuadra el viewport).

```javascript
function setVh() {
    document.documentElement.style.setProperty('--mh-vh', window.innerHeight + 'px');
}
setVh();
window.addEventListener('resize', setVh);
window.addEventListener('orientationchange', setVh);
window.addEventListener('pageshow', setVh);
```

## PWA Manifest

`manifest.php` genera dinámicamente el manifest con tema/colores del user activo:

```json
{
  "name": "Melon Hub",
  "short_name": "Melon Hub",
  "start_url": "...",
  "scope": "./",
  "id": "./mobile.php",
  "display": "standalone",
  "display_override": ["standalone", "minimal-ui"],
  "orientation": "portrait",
  "background_color": "#<theme color>",
  "theme_color": "#<theme color>",
  "icons": [
    { "src": "assets/img/mobile/icon-192.png", "sizes": "192x192", "type": "image/png", "purpose": "any" },
    { "src": "assets/img/mobile/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }
  ]
}
```

Los iconos se generan a partir de `icon.png` con `assets/push/resize-icon.php`. Las dimensiones DEBEN coincidir con los archivos o Chrome rechaza la instalación.

## Service Worker (`service-worker.js`)

Tres responsabilidades:

1. **install/activate**: `skipWaiting()` + `clients.claim()` para tomar control inmediato. Borra caches viejos.
2. **push**: recibe el payload del backend, muestra notificación nativa.
3. **notificationclick**: enfoca o abre la PWA en la URL del payload.

```javascript
const SW_VERSION = 'v13';

self.addEventListener('push', function(event) {
    event.waitUntil((async function() {
        const data = event.data ? event.data.json() : {};
        const opts = {
            body:  data.body || '',
            icon:  data.icon || '/scrapbookOnline/assets/img/mobile/icon-192.png',
            badge: '/scrapbookOnline/assets/img/mobile/notification-badge.png',
            tag:   data.tag || ('chat:' + data.from),
            renotify: true,
            data: { url: data.url || '/scrapbookOnline/mobile.php' }
        };
        await self.registration.showNotification(data.title || 'Melon Hub', opts);
    })());
});
```

`SW_VERSION` se bumpea cada vez que cambia el archivo. El navegador compara byte-a-byte; si cambia, registra como SW nuevo, `skipWaiting` → activate. La página padre detecta el cambio vía `controllerchange` y se recarga sola.

### Cache control para el SW

`.htaccess` impide cachear el SW para que los cambios se vean al instante:

```apache
<Files "service-worker.js">
    <IfModule mod_headers.c>
        Header set Cache-Control "no-cache, no-store, must-revalidate"
        Header set Pragma "no-cache"
        Header set Expires "0"
    </IfModule>
</Files>
```

### Supresión de notificaciones de chat activas

Si el usuario ya está leyendo un chat en la PWA, no queremos mostrar la notif del SO. El SW pregunta a todos los clientes abiertos via `MessageChannel` si están "viendo" el chat de quien manda el mensaje. Si alguno responde true en <300 ms, suprime la notif.

## Push subscriptions

Tabla `push_subscriptions`:

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | |
| `endpoint` | VARCHAR(500) | URL del Push Service (cambia por browser). |
| `p256dh` | VARCHAR(255) | Clave pública. |
| `auth` | VARCHAR(255) | Auth secret. |
| `device_token` | VARCHAR(80) | Para multi-dispositivo. |
| `created_at` | TIMESTAMP | |

`assets/push/subscribe.php` crea o actualiza (idempotente por `endpoint`). `assets/push/send.php` envía un push a todos los endpoints de un user.

### Generar VAPID keys

```bash
# Una vez al instalar el proyecto:
php -r "require 'vendor/autoload.php'; \$keys = \\Minishlink\\WebPush\\VAPID::createVapidKeys(); print_r(\$keys);"
```

Mete las dos en `.env`:

```env
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
VAPID_SUBJECT=mailto:tu@email.com
```

Si no tienes `web-push` PHP library, instálala via Composer: `composer require minishlink/web-push`.

Si no quieres push real, deja las keys vacías. El SW solo emitirá las notifs del propio chat dentro de la app (no las del SO con app cerrada).

## Tablets

Las tablets entran al MISMO `desktop-base.php` que el PC, pero con:

- `<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">`
- `<body class="is-tablet">` + `<html data-tablet="1">`
- `assets/css/tablet.css` cargado solo si is_tablet → taskbar 44px, start-btn 36px, ventanas con `clamp()`/`svh`, hit-targets ≥44px.

Ver [00-arquitectura.md](00-arquitectura.md) para más detalle.

## Diagnóstico

| Síntoma | Causa probable |
|---|---|
| PWA no se instala en iOS | Falta `<meta name="apple-mobile-web-app-capable" content="yes">`. Comprueba HTTPS válido. |
| Service Worker no recibe push | VAPID keys mal o el backend no enviando bien al endpoint. `web-push` lib requiere openssl. |
| Iconos del manifest se rechazan | Dimensiones declaradas != tamaño real del archivo. Chrome es estricto. |
| Web Share del wrapped no aparece | El iframe no tiene `allow="web-share"`. |
| Clipboard write bloqueado en iframe | Necesita `allow="clipboard-write"`. |
| Audio no reproduce en iframe | `allow="autoplay"`. Y necesita gesture del user antes (no se puede autoplay puro). |
