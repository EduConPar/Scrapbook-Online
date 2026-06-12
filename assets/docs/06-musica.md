# 07 — Música

Reproductor con disco de vinilo girando, playlists multi-fuente y modo "Listen-Together" para escuchar a la vez con otros usuarios.

## Fuentes soportadas

| Fuente | Cómo se reproduce | Cómo se importa |
|---|---|---|
| YouTube | IFrame API embedida. | URL, link de playlist, ID. |
| Spotify | Vista previa (30 s) vía Web API. No reproducción full por restricciones. | Importar playlist pública via API client credentials. |
| Tidal | Similar a Spotify, preview de 30 s + metadata. | Importar via API oficial. |
| Local/upload | No soportado (intencional, sin storage propio). | — |

YouTube es la fuente "real" — todo lo demás es para construir listas y luego buscar la versión en YT. Esto evita problemas de copyright y licencias.

## Tabla `playlists`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `owner_id` | INT FK | |
| `name` | VARCHAR(120) | |
| `is_public` | TINYINT(1) | |
| `created_at`, `updated_at` | TIMESTAMPS | |

## Tabla `playlist_items`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `playlist_id` | INT FK | |
| `youtube_id` | VARCHAR(40) NULL | ID de YouTube si lo conocemos. |
| `spotify_id` | VARCHAR(40) NULL | |
| `tidal_id` | VARCHAR(40) NULL | |
| `title` | VARCHAR(255) | |
| `artist` | VARCHAR(255) | |
| `album` | VARCHAR(255) NULL | |
| `image_url` | VARCHAR(500) NULL | |
| `duration_sec` | INT NULL | |
| `position` | INT | Orden dentro de la playlist. |
| `added_by` | INT FK NULL | Para playlists colaborativas. |
| `added_at` | TIMESTAMP | |

## Tabla `playlist_collaborators`

Para playlists colaborativas (un grupo de amigos editando la misma). `(playlist_id, user_id, role, joined_at)`. Roles: `owner`, `editor`, `viewer`.

`playlist_invites` maneja invitaciones pendientes (similar a `item_invites` del perfil).

## API: `assets/music/api.php`

Acciones:

- `get-playlists` — propias + en las que colaboro.
- `create-playlist`, `delete-playlist`, `rename-playlist`.
- `save-playlist-item`, `delete-playlist-item`, `reorder-playlist-items`.
- `import-playlist` — auto-detección YouTube/Spotify/Tidal y bulk-insert.
- `search-youtube`, `search-spotify`, `search-tidal` — proxy a las APIs con cache en BD (`app_cache`).
- `get-extras` — info ampliada (letras vía Last.fm, artist bio, etc.).
- `last-fm-scrobble` — opcional, registra scrobbles en Last.fm si el user tiene API key.
- `track-play` — para stats de Wrapped (qué se ha escuchado más).

## Tabla `music_plays`

Registro de reproducciones para Wrapped y stats:

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | |
| `youtube_id` | VARCHAR(40) NULL | |
| `title`, `artist` | VARCHAR(255) | Snapshot al momento. |
| `duration_played_sec` | INT | Cuánto realmente se escuchó. |
| `played_at` | TIMESTAMP | |

Indexada por `(user_id, played_at)`. Wrapped agrupa por mes/artista/canción.

## Reproductor (`apps/reproductor.php`)

UI estilo Win98 con:

- Header de ventana con info de la canción.
- Disco de vinilo central (CSS animation con rotación; pausada si está en pause).
- Cover incrustado en el centro del disco.
- Controles (play/pause/next/prev/seek/volume).
- Right panel: editor de playlists.
- Modo fullscreen ("flip" a vista grande con letras de la canción si hay).

El YouTube iframe vive oculto fuera del viewport — solo se usa por su API. El audio sale de él pero el "frontend" es nuestro.

### Estados

- `state.playerWindow` — la ventana visible.
- `state.editor` — editor de playlist (la "playlist actual" abierta).
- `state.ytPlayer` — el `YT.Player` instanciado.
- `state.currentTrack` — track ahora sonando.

## Listen-Together

Modo "escuchar a la vez". Un user crea una sala, invita amigos vía `listening_invites`. Los participantes sincronizan posición vía polling.

### Tablas

| Tabla | Para qué |
|---|---|
| `listening_sessions` | `(id, host_user_id, playlist_id, current_track_idx, started_at, last_sync_at)` |
| `listening_participants` | `(session_id, user_id, joined_at, left_at)` |
| `listening_invites` | `(session_id, from_user_id, to_user_id, accepted, created_at)` |

### Sincronización

El host es la fuente de verdad. Cada N segundos:

1. Host envía `last-sync` con `current_track_idx` y `position_sec`.
2. Participantes pollean cada 5 s y ajustan su YouTube player si desfasan >1.5 s.
3. Si el host se desconecta, la sesión termina (último ping > 30 s).

## Mini-player flotante en móvil

`mobile.php` tiene un widget de disco de vinilo arrastrable que sigue al usuario por las apps. Tap → abre el reproductor fullscreen. Drag con long-press. El audio sigue sonando aunque navegues por otras apps (el shell mantiene el YT iframe).

## Wrapped (resumen anual)

`apps/wrapped.php` consulta `music_plays` (y otras tablas: posts, follows, items completados) y construye un "Wrapped" tipo Spotify:

- Tu canción más escuchada
- Artista del año
- Géneros
- Stats sociales: posts publicados, likes recibidos, momentos creados
- Etc.

Render con html2canvas → PNG descargable + share vía Web Share API. Ver [README.md](../../README.md) para detalle.

## APIs externas — config

### Spotify

```env
SPOTIFY_CLIENT_ID=...
SPOTIFY_CLIENT_SECRET=...
```

Client Credentials flow (no user OAuth). Suficiente para playlists públicas y metadata. Tokens cacheados en `app_cache` por 50 min.

### Tidal

```env
TIDAL_CLIENT_ID=...
TIDAL_CLIENT_SECRET=...
TIDAL_COUNTRY=ES
```

Mismo flujo.

### Last.fm

```env
LASTFM_API_KEY=...
```

Solo para letras y artist info. Si está vacío, el reproductor cae a metadata limitada.

### YouTube

No requiere API key — usa Innertube (reverse-engineered, ver `assets/yt-archive.php`). Cuidado: estructura inestable, YouTube cambia los renderers cada cierto tiempo. El parser tiene fallbacks para `playlistVideoRenderer`, `gridVideoRenderer`, `lockupViewModel`, etc.

## Diagnóstico

| Síntoma | Causa probable |
|---|---|
| YouTube no carga (negro) | Bloqueado por ad-blocker o iframe sandboxing. Comprueba consola del browser. |
| Spotify devuelve 401 | Token expirado, cache stale. Borra entradas `sp_` de `app_cache` o espera 50 min. |
| Listen-Together desincroniza | Probablemente latency entre clients. El threshold de sync es 1.5 s. Considera bajarlo. |
| Wrapped no muestra canciones | `music_plays` vacío. La app `track-play` debe llamarse al final de cada reproducción para registrar. |
