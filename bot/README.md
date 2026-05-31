# Scrapbook Melon — Discord Bot

Otorga puntos de Autismo a usuarios vinculados (`usuarios.discord_user_id`)
por enviar mensajes y por tiempo en canales de voz.

## Resumen del flujo

1. Usuario escribe un mensaje → `+POINTS_PER_MESSAGE` puntos (cooldown anti-spam).
2. Usuario entra a un canal de voz → se anota el timestamp.
3. Usuario sale del canal de voz → `+POINTS_PER_VOICE_MINUTE × minutos completos` puntos.
4. Si el usuario aún no ha pulsado "🔗 Conectar Discord" en la tienda, la UPDATE no afecta a ninguna fila y simplemente se ignora.

## Variables de entorno (.env)

Discloud lee automáticamente el `.env` que metas en el ZIP. Copia `.env.example` → `.env` y rellena.

| Variable | Por defecto | Descripción |
|---|---|---|
| `DISCORD_BOT_TOKEN` | — (obligatoria) | Bot Token de discord.com/developers/applications |
| `DISCORD_GUILD_ID` | — (recomendada) | Solo cuenta actividad en este servidor |
| `POINTS_PER_MESSAGE` | `1` | Puntos por mensaje |
| `POINTS_PER_VOICE_MINUTE` | `1` | Puntos por minuto completo en voz |
| `MESSAGE_COOLDOWN_SECONDS` | `60` | Anti-spam: ignora mensajes seguidos del mismo usuario |
| `EXCLUDED_CHANNEL_IDS` | — | IDs separados por coma; mensajes en esos canales no dan puntos |
| `DB_HOST` | — (obligatoria) | Host del MySQL del sitio (la IP/dominio externo) |
| `DB_PORT` | `3306` | Puerto MySQL |
| `DB_USER` | — | Usuario MySQL |
| `DB_PASS` | `""` | Password MySQL |
| `DB_NAME` | `scrapbook_melon` | Nombre de la base |
| `DB_SSL` | — | `true` si el hosting exige TLS |

## Deploy en Discloud (gratis, 5 min)

### 1) Rellenar el `.env`

```bash
cd bot
cp .env.example .env
nano .env       # o el editor que prefieras
```

### 2) Comprimir todo en un ZIP

El ZIP debe contener **exactamente** estos archivos en la raíz (NO dentro de una carpeta):

```
bot.js
package.json
discloud.config
.env
```

**Importante**: NO incluyas `node_modules/`, Discloud lo instala solo a partir de `package.json`. Tampoco incluyas `README.md` ni `.env.example` (no estorban, pero pesan).

Desde la terminal:
```bash
cd bot
zip -r scrapbook-bot.zip bot.js package.json discloud.config .env
```

O desde el explorador de archivos: selecciona los 4 archivos → click derecho → "Comprimir → ZIP".

### 3) Entrar al servidor de Discloud

Únete a [discord.gg/discloud](https://discord.gg/discloud) si no estás ya.

### 4) Subir el ZIP

En cualquier canal donde tengas permiso (o en DM al bot):

```
/up
```

→ El bot te pedirá que arrastres el ZIP. Lo arrastras y le das a Enter.

Si todo está bien, te responde con el ID de la app y un enlace a su dashboard.

### 5) Comprobar logs

En el mismo servidor de Discloud, en DM al bot:

```
/logs
```

Te muestra los últimos logs. Deberías ver `▶ Bot conectado como Scrapbook Melon#1234 — escuchando 1 guild(s).`

### Comandos útiles del bot de Discloud

```
/up        — subir nuevo ZIP (reemplaza si ya existe)
/restart   — reiniciar la app
/logs      — ver logs recientes
/status    — uso de RAM/CPU + uptime
/stop      — pararla
/start     — arrancarla
/apps      — listar tus apps
/info      — info detallada de una app
/commit    — subir solo archivos cambiados (más rápido que /up entero)
```

## Free tier y renovación

Discloud free tier **se apaga automáticamente cada 7 días** si no la renuevas. Para renovar:

- En el server de Discloud hay un canal `#renovar` o similar donde reaccionas a un mensaje semanal con un emoji.
- Si miras al menos cada 7 días, es 100% gratis para siempre.
- Si te olvidas, la app se para pero el código no se borra — puedes hacer `/start` cuando vuelvas.

Si quieres saltarte la renovación, el plan más barato (~$1-2/mes según promo) la elimina y añade más RAM.

## MySQL accesible desde fuera

Como el bot vive en Discloud (Brasil) y tu BD en el hosting, el hosting tiene que permitir conexiones MySQL externas:

- **Hostinger / IONOS / Banahosting / similar**: panel → **MySQL** → **"Remote MySQL"** → añadir host. Como Discloud no te da una IP fija pública, lo más simple es permitir `%` (cualquier IP). Si te preocupa la seguridad, en Discloud Premium puedes pedir IP fija.
- **Si tu hosting NO permite MySQL remoto**: o cambias de hosting, o montas un proxy/túnel SSH en un VPS pequeño (3-4€/mes).

## Verificar que tira (checklist)

- `/status` → state `RUNNING`, RAM 50-100 MB.
- `/logs` → primer mensaje `▶ Bot conectado como ...`.
- Manda un mensaje en tu server desde una cuenta con Discord ya vinculada en la tienda → en `/logs` aparece `+1🧠 → 12345... (mensaje)`.
- Mira en BD: `SELECT label, autismo FROM usuarios;` — el contador debería haber subido en 1.

## Troubleshooting

| Síntoma | Causa probable | Solución |
|---|---|---|
| `Used disallowed intents` en logs | Faltan intents en Discord Developer Portal | Activa **Server Members Intent** (no privilegiado en uso del bot, pero a veces lo piden) |
| `award error: connect ECONNREFUSED` | El hosting MySQL no acepta conexiones externas | Activa Remote MySQL y abre el host `%` |
| `await error: Access denied` | Usuario/password MySQL incorrectos | Revisa `.env`, recuerda hacer `/restart` después |
| El bot conecta pero los puntos no suben | Tu usuario no ha pulsado "🔗 Conectar Discord" en la tienda | Conecta tu cuenta primero |
| Voz no cuenta minutos | `GUILD_VOICE_STATES` activado pero no detecta cambios | Asegúrate de que el bot tiene permiso de "Ver canales de voz" en el server |

## Variables que NO necesita el bot

- `KOFI_*` — solo lo usa el webhook PHP del sitio.
- `SPOTIFY_*`, `TIDAL_*`, `GOOGLE_*` — clientes de la web, no del bot.
- `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET` — solo OAuth de usuario; el bot usa `DISCORD_BOT_TOKEN`.
