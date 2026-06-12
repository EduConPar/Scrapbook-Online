# 05 — Perfil, social, chat y notificaciones

El sistema social cubre: ficha del usuario con datos personales, listas de cosas que está viendo/jugando/leyendo (con colaboradores), posts/likes/comments públicos, follows, chat 1-a-1, notificaciones internas y push.

## Tabla `profile`

Datos extendidos del usuario más allá de `usuarios`. Estructura key-value parcial:

| Columna | Tipo | Notas |
|---|---|---|
| `user_id` | INT PK FK | 1-a-1 con `usuarios`. |
| `bio` | TEXT | Bio libre. |
| `location` | VARCHAR(80) | Ciudad/región. |
| `pronouns` | VARCHAR(40) | Pronombres. |
| `birthday` | DATE NULL | |
| `mood` | VARCHAR(40) | Estado de ánimo actual. |
| `mood_emoji` | VARCHAR(8) | |
| `socials` | TEXT JSON | Mapa de plataforma → handle (twitter, instagram, twitch, steam...). |
| `theme_choices` | TEXT JSON | Preferencias visuales propias del perfil. |
| `featured_items` | TEXT JSON | IDs de `list_items` destacados. |
| `last_seen_at` | TIMESTAMP | Para presencia. |

## Listas (`list_items`)

Cosas que el usuario está consumiendo o quiere consumir. Polimorfismo por categoría.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `owner_id` | INT FK | Dueño. |
| `category` | ENUM('series', 'pelis', 'juegos', 'libros', 'musica', 'animes', 'mangas', 'comics', 'podcasts', ...) | |
| `title` | VARCHAR(255) | |
| `subtitle` | VARCHAR(255) NULL | Autor, año, plataforma. |
| `image_url` | VARCHAR(500) NULL | URL pública o subida local. |
| `status` | ENUM('pending', 'in-progress', 'completed', 'dropped') | |
| `rating` | INT NULL | 1-5 estrellas. |
| `review` | TEXT NULL | Comentario al completar. |
| `shared_from` | INT FK NULL | Si lo importó de otro user. |
| `progress` | VARCHAR(80) NULL | "Capítulo 4", "Temporada 2 ep 8". |
| `created_at`, `updated_at`, `completed_at` | TIMESTAMPS | |

### Colaboradores

`list_item_collaborators (item_id, user_id, joined_at, status)`. Status: `pending`/`active`/`rejected`/`left`/`removed`. Invitaciones: tabla `item_invites`.

### En curso (carousel)

El perfil muestra hasta 3 items `in-progress` por categoría como cards principales con paginación. Datos derivados de la query, sin tabla aparte.

## Tabla `posts`

Posts que el usuario publica en su muro.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | |
| `text` | TEXT | Hasta 500 chars. |
| `image_url` | VARCHAR(500) NULL | |
| `created_at` | TIMESTAMP | |

Indexada por `(user_id, created_at)` para timeline del propio user. Para el feed combinado de los users que sigo, se hace JOIN con `follows`.

### Likes

`post_likes (post_id, user_id)` con UNIQUE compuesto. CASCADE on user delete.

### Comments

`post_comments (id, post_id, user_id, text, created_at)` con FK CASCADE.

### Publicar a Discord

Desde la galería puedes publicar un post (con imagen) al canal Discord configurado en `MELON_HUB_WEBHOOK_URL`. Cuando lo haces:

1. POST al webhook con título + imagen + mensaje.
2. Discord devuelve el `message_id` del mensaje creado.
3. INSERT en `webhook_posts (message_id, user_id, post_id, kind, created_at)`. Este mapping queda disponible por si algún sistema externo necesita resolver qué post de la web corresponde a un mensaje concreto del canal de Discord.

## Follows

`follows (follower_id, followee_id, created_at)` con FK CASCADE. Idempotente vía UNIQUE compuesto.

Pares mutuos (yo te sigo, tú me sigues) → habilita chat 1-a-1 entre los dos. Sin mutual follow, no se puede chatear.

## Chat

### Tabla `chats`

`(id, user_a, user_b, created_at, last_msg_at)`. Pareja única `LEAST(a,b), GREATEST(a,b)` mantenida por el cliente al crear.

### Tabla `messages`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `chat_id` | INT FK | |
| `from_user_id` | INT FK | |
| `body` | TEXT | |
| `image_url` | VARCHAR(500) NULL | |
| `sent_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | Soft delete. |

### Presencia y last-seen

Tabla `user_presence (user_id, last_at, focused_chat_with)`. Heartbeat cada 30 s actualiza `last_at`. `focused_chat_with` lo set el cliente cuando abre un chat para que la otra parte vea el indicador "está leyendo".

### Unread counts

`assets/profile/api.php?action=get-unread-chats` calcula los mensajes posteriores al `last_seen_at` del usuario receptor por cada chat activo, devuelve `{counts: {userKey1: 3, userKey2: 1}}`.

### API

`assets/profile/api.php` con muchas acciones:

- `get-profile`, `save-info` — datos del perfil.
- `save-list-item`, `delete-list-item`, `update-status` — listas.
- `get-posts`, `save-post`, `delete-post` — posts.
- `toggle-post-like` — devuelve `{ok, liked, count}` (NO un array `likes`).
- `get-post-comments`, `add-post-comment` — comments.
- `follow`, `unfollow`, `get-follows` — social.
- `get-messages`, `send-message` — chat.
- `get-unread-chats`, `heartbeat`, `presence`, `set-focused-chat` — presencia.
- `get-profile-notifs`, `notify-review`, `notify-dismiss` — notifs (bell).

## Notificaciones internas

Tabla `notifications`:

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | Destinatario. |
| `from_user_id` | INT FK NULL | ON DELETE SET NULL. |
| `type` | ENUM('like', 'comment', 'follow', 'item-invite', 'collab-accepted', 'review-request', ...) | |
| `payload` | TEXT JSON | Datos arbitrarios según el tipo. |
| `is_read` | TINYINT(1) | |
| `created_at` | TIMESTAMP | |

Helper `pf_addNotif($pdo, $userId, $type, $fromUserId, $payload)` y `pf_deleteNotifsMatching(...)` en `assets/profile/api.php` centralizan creación/limpieza.

### Item notifications via SSE

Algunas notifs (invitaciones a colaborar en items) llegan en tiempo real vía `assets/profile/item-notifications-stream.php`. Es un SSE que pollea internamente la BD y emite eventos a cualquier cliente conectado. El cliente:

```javascript
const es = new EventSource('assets/profile/item-notifications-stream.php');
es.onmessage = ev => {
    const items = JSON.parse(ev.data);
    items.forEach(pushItemNotif);  // pinta cards en el shell
};
```

### Sonido de notificación

`assets/sound/notificacion.wav` + helper `assets/js/notif-sound.js`. Suena en:

- Desktop: cuando aumenta el contador unread del polling, o cuando llega un item-notif por SSE.
- Móvil: cuando aumenta total unread, cuando llega un reminder toast (calendario), cuando aumenta el badge de chat.

El helper expone `window.playNotifSound()` con throttle de 1.5 s para evitar pitidos solapados.

## Push notifications (PWA)

Cuando la PWA está cerrada (móvil con app no abierta), las notifs nativas del SO llegan vía Push API:

1. Usuario activa "Recibir avisos" en Ajustes → consenso del browser → `Notification.requestPermission()`.
2. `assets/push/subscribe.php` crea/actualiza una subscripción VAPID, la guarda en `push_subscriptions (user_id, endpoint, keys, created_at)`.
3. El backend cuando crea una notif crítica (DM, item invite) hace POST al endpoint VAPID del browser.
4. El Service Worker (`service-worker.js`) recibe el push, parsea `{title, body, url, icon}`, muestra `showNotification()`.

Ver [07-pwa-movil.md](07-pwa-movil.md) para configurar VAPID keys y el SW.

## Momentos (Calendario)

Aunque viven en la app Calendario, son parte del modelo social. Tabla `momentos`:

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `pareja_id` | INT NULL | NULL si es solo del usuario. |
| `usuario_id` | INT NOT NULL | Autor. |
| `titulo` | VARCHAR(100) | |
| `descripcion` | TEXT NULL | |
| `foto` | VARCHAR(255) NULL | Archivo en `uploads/momentos/momento_<id>_<ts>.<ext>`. |
| `emoji` | VARCHAR(10) NULL | |
| `emocion` | VARCHAR(10) NULL | |
| `fecha` | DATE | |
| `creado_en` | TIMESTAMP | |

API: `assets/couple/api.php` (compartida con recordatorios). Foto se sube vía `?action=upload-foto` (multipart).

## Diagnóstico de problemas comunes

| Síntoma | Causa probable |
|---|---|
| Botón like no actualiza UI en móvil pero sí cuenta en BD | Bug histórico: el frontend móvil esperaba `data.likes` array pero la API devuelve `{ok, liked, count}`. Verifica que `togglePostLike` lee `d.liked` y `d.count`, no `d.likes`. |
| El chat no marca como leído | `set-focused-chat` no se está llamando al abrir o el heartbeat falla por sesión expirada. |
| No llegan push cuando la app está cerrada | VAPID public/private mal configurada en `.env`, o `push_subscriptions` vacío (no se hizo subscribe). |
| Notifs internas duplicadas | Faltan calls a `pf_deleteNotifsMatching` cuando se revierte una acción (ej. quitar like). |
