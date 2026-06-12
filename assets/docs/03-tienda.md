# 03 — Tienda

Sistema de cosméticos con dos economías:

1. **Puntos de Autismo** (moneda interna) — se ganan participando en el Discord. Se gastan en items dentro de la web.
2. **Euros vía Ko-fi** (moneda real) — donaciones y encargos personalizados. NO entran en el sistema de Autismo: son donaciones puras + comisiones artísticas.

## Categorías de items

`tienda_items.categoria`:

| Categoría | Qué es | Premia con |
|---|---|---|
| `discord` | Roles del servidor Discord (Tulon, Mentalidad de Asperger, Speedrunner, Forma Final). Se asignan automáticamente al comprar. | Discord role + entry en `tienda_compras`. |
| `interfaces` | Packs visuales que reskinean la web (MelonOS Overdose). | Acceso a la interfaz desde Temas. |
| `mascotas` | Skins de mascota virtual. | Skin disponible al crear nueva mascota. |
| `haros` | Variantes de Haro (mascota notificadora). | Haro como selección activa. |

## Tabla `tienda_items`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `nombre` | VARCHAR(80) | Nombre visible. |
| `slug` | VARCHAR(60) | Identificador semántico (`green`, `yellow`, `kawaii`, `gabriel`). |
| `descripcion` | VARCHAR(300) | Texto descriptivo (puede ser auto-generado para roles Discord). |
| `precio` | INT | En puntos de Autismo. 0 = item base (gratis). |
| `icono` | VARCHAR(8) | Emoji o caracter. Para haros se usa PNG curado en `assets/img/haro/<slug>Haro-preview.png`. |
| `activo` | TINYINT(1) | Soft-delete sin perder compras. |
| `orden` | INT | Para ordenar dentro de la categoría. |
| `categoria` | VARCHAR(20) | Ver tabla arriba. |
| `discord_role_id` | VARCHAR(40) NULL | Solo para items de Discord. |

## Tabla `tienda_compras`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | |
| `item_id` | INT FK | |
| `precio` | INT | Snapshot del precio pagado (por si cambia después). |
| `creado_en` | TIMESTAMP | |

`UNIQUE (user_id, item_id)` previene compra doble.

## API: `assets/tienda/api.php`

- `state` — devuelve balance del usuario, items disponibles (con `discord_role_name` resuelto vía discord-oauth/helpers), compras del usuario, estado de Discord linkage.
- `balance` — endpoint ligero para polling. Devuelve solo `{autismo}`.
- `buy` — atomic. Valida, descuenta, registra compra, opcionalmente asigna rol Discord.
- `donors` — lista los donantes para la pestaña Donaciones (lee `donaciones`, devuelve nombre + avatar + mensaje + tipo).

### Flujo de compra (`?action=buy`)

```
POST { item_id: 8 }
```

1. Valida `item_id` y que el item esté activo.
2. **Anti-doble compra**: `SELECT 1 FROM tienda_compras WHERE user_id = ? AND item_id = ?`. Si existe → 409 "Ya tienes este item".
3. **Discord linkage check**: si el item tiene `discord_role_id` y `usuarios.discord_user_id` está vacío → 403 "Vincula tu cuenta de Discord antes de comprar este item." Sin esta validación, el usuario perdería puntos sin recibir el rol.
4. **Balance check**: `SELECT autismo FROM usuarios WHERE id = ? FOR UPDATE`. Si < precio → 402 "Autismo insuficiente".
5. Dentro de una transacción:
   - `UPDATE usuarios SET autismo = autismo - precio`
   - `INSERT INTO tienda_compras`
   - Si tiene `discord_role_id`: `discordAssignRole($discordUserId, $roleId)` (en `assets/discord-oauth/helpers.php`). Si Discord rechaza por jerarquía/permisos → rollback completo + 502.
6. COMMIT y devuelve `{ok, autismo: newBalance, item, discord: {attempted, ok, status, error}}`.

## Tabla `donaciones` (Ko-fi)

Receptor: `assets/tienda/kofi-webhook.php`. Configurado en ko-fi.com/Settings/API con `KOFI_WEBHOOK_TOKEN` para autenticar.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `nombre` | VARCHAR(80) | `from_name` del payload. `Anónimo` si is_public=false. |
| `avatar_url` | VARCHAR(500) NULL | Ko-fi no manda avatar en el webhook; queda null y el frontend pone placeholder. |
| `mensaje` | VARCHAR(200) NULL | Mensaje público del donante. Para encargos se sustituye por "Encargó: <producto>" derivado del `direct_link_code`. |
| `importe` | DECIMAL(10,2) NULL | |
| `tipo` | ENUM('donacion', 'suscripcion', 'encargo') | Mapeado del `type` de Ko-fi (Donation/Subscription/Commission). |
| `kofi_transaction_id` | VARCHAR(80) UNIQUE | Dedupe natural. |

Webhook:

1. Verifica `data.verification_token` con `KOFI_WEBHOOK_TOKEN` (constant-time compare via `hash_equals`).
2. Mapea `type` a `tipo` interno.
3. Para `Commission`: lee `shop_items[0].direct_link_code` y busca en `$COMMISSION_NAMES` (Haro personalizado, Tema personalizado, Mascota personalizada) — esto evita filtrar las "Order Notes" privadas que el cliente escribió.
4. INSERT con `ON DUPLICATE KEY UPDATE` para que re-envíos sean idempotentes.

## Pestaña Donaciones en la tienda

`tienda.php` y `tienda-mobile.php` tienen una vista "Donaciones" (botón abajo de la sidebar) con:

- Texto explicativo + botón Ko-fi.
- Lista de encargos personalizados (Haro 5 €, Tema 10 €, Mascota 15 €) que abren en pestaña nueva.
- Grid de donantes (ordenados por `creado_en DESC`).

## Ayuda para ganar puntos

Botón "?" circular en el card del wallet ("Tu balance"). Abre una ventana con las reglas de la economía:

- Mensajes en cualquier canal: +1 punto. Cooldown anti-spam de 2 s.
- Tiempo en canales de voz: +1 punto cada 2 min.
- Reacciones de corazón en posts publicados a Discord: +10 al autor del post. Una persona = un corazón por post.
- Descargas de tus temas en la biblioteca: +50 al autor por cada usuario distinto que lo descargue.

Los puntos por mensajes, voz y reacciones los reparte un bot de Discord externo que vive en su propio proyecto fuera de este repo. Las reacciones a posts publicados usan las tablas `webhook_posts` (mapping post → message_id) y `webhook_reactions` (dedupe). Los premios por descarga de tema sí los maneja directamente este proyecto vía la tabla `theme_download_rewards` (ver [02-interfaces-temas.md](02-interfaces-temas.md)).

## UI gating de items Discord

El frontend lee `window.__DISCORD_LINKED__` (sincronizado al cargar y tras conectar/desconectar Discord). Para items con `discord_role_name`:

- Si NO linked y no comprado: el botón "Comprar" pasa a "Vincula Discord" y `disabled`.
- Si linked: botón normal.
- Si comprado: "Ya lo tienes".

El server también rechaza estas compras (403) para que parchear el HTML no salte el control.

## Precios actuales (a fecha de escritura)

Items en `tienda_items`:

```
discord:
  Tulon                       100  rol 858965703708639262
  Mentalidad de Asperger      250  rol 873153444528132107
  Mentalidad de Speedrunner   500  rol 873152655269195826
  La Forma Final             1000  rol 1233425443244347492

haros:
  Verde       0  (base, todos lo tienen)
  Amarillo  100

mascotas:
  Gabriel     0  (base)

interfaces:
  MelonOS Overdose  500
```

Encargos por Ko-fi (no entran en BD, son links externos):

```
Haro personalizado       5 €
Tema personalizado       10 €
Mascota personalizada    15 €
```

## Refresco entre clientes

Cuando un usuario hace una compra:

1. Frontend muestra confirmación inmediatamente (optimistic update del balance).
2. `pollBalance()` cada 15 s pide `?action=balance` para sincronizar con el server (por si gastó algo desde otra pestaña).
3. El widget de Donaciones polls cada 30 s a `?action=donors` para refrescar el grid sin recargar.
