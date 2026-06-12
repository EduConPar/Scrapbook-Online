# 06 — Mascota

Sistema de mascota virtual tipo Tamagotchi/Shimeji con sprite animado. Cada usuario tiene 0 o 1 mascota activa. Las mascotas tienen:

- Nombre y "skin" (variante visual).
- Estadísticas (hambre, ánimo, energía).
- Gustos personalizados (comidas favoritas, actividades).
- Memoria de eventos recientes.
- Inventario de objetos.

## Tabla `mascotas`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK UNIQUE | 1-a-1. |
| `nombre` | VARCHAR(80) | |
| `skin` | VARCHAR(40) | Slug del pack visual (`gabriel`, ...). |
| `hambre` | INT | 0-100. Baja con tiempo. |
| `animo` | INT | 0-100. |
| `energia` | INT | 0-100. |
| `nivel_amistad` | INT | Sube con interacciones. |
| `last_fed_at` | TIMESTAMP NULL | Para descomponer hambre. |
| `last_played_at` | TIMESTAMP NULL | |
| `created_at` | TIMESTAMP | |

## Tabla `mascota_gustos`

Preferencias del usuario para los menús de su mascota.

| Columna | Tipo | Notas |
|---|---|---|
| `user_id` | INT FK | |
| `category` | VARCHAR(40) | `comida`, `actividad`, `juguete`. |
| `key_name` | VARCHAR(60) | Slug del item. |
| `weight` | INT | Cuánto le gusta (1-10). |

## Tabla `mascota_memoria`

Eventos recientes que la mascota "recuerda" y usa para reaccionar.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | |
| `event_type` | VARCHAR(40) | `fed`, `played`, `ignored`, `gifted`, `scolded`. |
| `payload` | TEXT JSON | Detalles del evento. |
| `created_at` | TIMESTAMP | |

Capped a las últimas N entradas por user (cron o purga manual).

## Tabla `mascota_objetos`

Inventario de items que el user ha conseguido para su mascota.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | |
| `item_slug` | VARCHAR(60) | |
| `qty` | INT | |
| `acquired_at` | TIMESTAMP | |

## API: `assets/mascota/api.php`

Acciones principales:

- `state` — devuelve mascota + estadísticas + gustos + objetos + skin actual.
- `create` — crea la mascota (nombre, skin). Una sola por user.
- `feed` — alimenta. Sube hambre, ajusta ánimo según gustos.
- `play` — juega. Baja energía, sube ánimo.
- `gift` — usar un objeto del inventario.
- `delete` — elimina la mascota (con confirmación).
- `update-gustos` — guarda preferencias del usuario.

## Sprite y animaciones

`assets/mascota/engine.js` (cargado por la app) maneja el render del sprite-sheet. Cada skin tiene su propia carpeta `assets/mascota/skins/<slug>/` con:

- `frames/idle/*.png` — animación idle.
- `frames/happy/*.png`, `frames/sad/*.png`, etc.
- `meta.json` con tamaños, framerate, transiciones.

El engine carga el meta, precarga frames, y renderiza en un `<canvas>` o serie de `<img>` rotativos. Gabriel (el skin base) tiene 46 frames distribuidos en varias animaciones.

## Compra de skins

Las skins son items en `tienda_items` con `categoria='mascotas'`. La skin base (`gabriel`) tiene precio 0. Las demás son de pago en puntos de Autismo. Comprar añade fila a `tienda_compras` y desbloquea el skin para crear la siguiente mascota (la actual mantiene su skin original).

`assets/personalize/api.php?action=inventory` devuelve todos los mascotas/haros/interfaces que el user posee (base + comprados), separados por categoría.

## Long-press / contextmenu en móvil

En tablet/táctil, mantener pulsada la mascota abre el menú contextual (alimentar, jugar, regalar, info). Usa `assets/js/longpress.js` (util genérico).

## Iframe de la mascota en el escritorio

`apps/mascota.php` se carga en un iframe en `desktop-base.php`. El iframe queda persistente entre minimizaciones — la mascota sigue animándose en segundo plano.

## Diagnóstico

| Síntoma | Causa probable |
|---|---|
| Sprite no se ve, error 404 en frames | Skin con archivos faltantes. Revisa `assets/mascota/skins/<slug>/frames/`. |
| Estadísticas no bajan con el tiempo | Falta cron de "tick" que decremente hambre/energía cada N min. La app móvil lo recalcula al abrir. |
| No puedo crear segunda mascota | UNIQUE en `mascotas.user_id`. Solo 1 por user. Bórrala primero. |
