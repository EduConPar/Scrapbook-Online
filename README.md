# Melon Hub

Una web-app personal estilo escritorio (y en móvil) que combina varios "mini-OS" con sus propias aplicaciones: perfil/social, chat, calendario, reproductor de música multi-fuente, archivo de YouTube, tienda con economía de puntos vinculada a Discord, mascota virtual, generador de fichas de D&D, dibujo colaborativo, galería de momentos, sistema de temas e interfaces personalizables y un Wrapped anual.

Hecha en PHP 8 + MariaDB + JavaScript vanilla, sin frameworks pesados. Frontend modular cargado por iframes desde un shell central. PWA instalable.

## Vistas principales

- **Escritorio**: Con ventanas arrastrables, taskbar, start menu, iconos del escritorio. Cada usuario logueado entra a su propio `desktops/<label>-desktop.php`.
- **Móvil**: feature-phone con menú de apps lanzables en iframe. Tabletas usan el escritorio con layout adaptado.

## Apps incluidas

| App | Qué hace |
|---|---|
| Perfil | Datos personales, listas de cosas que estás viendo/jugando/leyendo, social (follows, posts, likes, comments), chat en vivo. |
| Calendario | Compartido: recordatorios, cuentas atrás épicas, momentos con foto. |
| MelonArchive | Buscador de playlists públicas de un canal de YouTube vía Innertube. |
| Música | Reproductor con vinilo girando. Importa playlists de YouTube/Spotify/Tidal. Listen-Together por sala. |
| D&D | Lector de PDFs de fichas + tirador 3D de dados (three.js). |
| Galería | Sube imágenes, las publica en Discord vía webhook. |
| Tienda | Compra cosméticos (interfaces, haros, roles Discord) con puntos. |
| Temas | Editor de paletas + biblioteca compartida (descarga lo que publiquen otros usuarios). |
| Dibujo | Colaborativo, Krita/Drawpile-style: capas, presión, brushes, undo. |

---

## Requisitos para correr en local

- PHP 8.0+ con extensiones `pdo_mysql`, `curl`, `gd`, `mbstring`, `openssl`, `fileinfo`.
- MariaDB 10.4+ o MySQL 8+.
- Apache con `mod_rewrite` (XAMPP empaqueta los tres).
- OpenSSL (para generar certificado HTTPS local).
- ~500 MB de espacio en disco.

## Instalación paso a paso (Linux con XAMPP)

### 1. Clonar el repo

```bash
cd /opt/lampp/htdocs
sudo git clone https://github.com/TU_USUARIO/melonhub.git scrapbookOnline
sudo chown -R $USER:$USER scrapbookOnline
cd scrapbookOnline
```

En Windows con XAMPP, clónalo en `C:\xampp\htdocs\scrapbookOnline` y ajusta rutas en consecuencia. El proyecto está pensado para Linux pero funciona en Windows con limitaciones (el script `setup-https.sh` solo corre en bash; usa el cert default de XAMPP).

### 2. Arrancar XAMPP

```bash
sudo /opt/lampp/lampp start
```

Verifica que Apache y MySQL están up en [http://localhost/](http://localhost/).

### 3. Crear la base de datos

Abre phpMyAdmin en [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/) y crea una BD vacía llamada `scrapbook_melon` (charset `utf8mb4`, collation `utf8mb4_general_ci`).

Importa el schema:

```bash
/opt/lampp/bin/mysql -u root scrapbook_melon < u546786567_melonhub_schema.sql
```

Si quieres datos de ejemplo (Capi y Angie como users), importa el dump completo:

```bash
/opt/lampp/bin/mysql -u root scrapbook_melon < scrapbook_melon.sql
```

### 4. Configurar el `.env`

Copia el ejemplo y rellena:

```bash
cp .env.example .env  # si existe, o créalo desde cero
nano .env
```

Contenido mínimo:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=scrapbook_melon

# OAuth y APIs externas (déjalas vacías si no las usas todavía)
SPOTIFY_CLIENT_ID=
SPOTIFY_CLIENT_SECRET=
GOOGLE_CLIENT_ID=
TIDAL_CLIENT_ID=
TIDAL_CLIENT_SECRET=
TIDAL_COUNTRY=ES
KOFI_WEBHOOK_TOKEN=
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_BOT_TOKEN=
DISCORD_GUILD_ID=
MELON_HUB_WEBHOOK_URL=
MELON_HUB_CHANNEL_ID=
POINTS_PER_REACTION=10
LASTFM_API_KEY=
```

### 5. (Opcional pero recomendado) Generar HTTPS para PWA

Las funciones PWA (instalable en móvil, notificaciones push, Web Share) requieren HTTPS, incluso en local. Lanza el script:

```bash
sudo bash setup-https.sh
```

Detecta tu IP LAN automáticamente y genera un cert auto-firmado válido 5 años para `localhost` + tu IP. Reinicia XAMPP después:

```bash
sudo /opt/lampp/lampp restart
```

### 6. Abrir la web

- Desktop: [http://localhost/scrapbookOnline/](http://localhost/scrapbookOnline/)
- Móvil PWA: `https://<tu-ip-lan>/scrapbookOnline/mobile.php` (acepta el aviso de cert no fiable la primera vez)

Verás el selector de usuarios. Con el dump completo, Capi y Angie ya están listos. Si importaste solo el schema, regístrate desde el botón de registro.

### 7. (Opcional) Configurar Discord OAuth para vincular cuentas

Sin esto los usuarios no pueden comprar items de Discord en la tienda. Crea una aplicación en [Discord Developer Portal](https://discord.com/developers/applications):

1. OAuth2 → Redirects: añade `http://localhost/scrapbookOnline/assets/discord-oauth/callback.php`.
2. Copia Client ID y Client Secret a tu `.env`.

### 8. (Opcional) Configurar Ko-fi webhook

Para que las donaciones aparezcan en la pestaña Donaciones:

1. ko-fi.com → Settings → API → pega como Webhook URL: `https://TU-DOMINIO/assets/tienda/kofi-webhook.php` (necesita HTTPS válido).
2. Copia el Verification Token a `KOFI_WEBHOOK_TOKEN` en `.env`.

## Documentación detallada

Hay docs por subsistema en [`assets/docs/`](assets/docs/):

- [00-arquitectura.md](assets/docs/00-arquitectura.md) — visión general, stack, estructura de directorios, flujo de petición.
- [01-autenticacion.md](assets/docs/01-autenticacion.md) — login, registro, sesiones, hash de contraseñas, autocreación de stubs.
- [02-interfaces-temas.md](assets/docs/02-interfaces-temas.md) — sistema de packs visuales (win98, kawaii) y editor de temas.
- [03-tienda.md](assets/docs/03-tienda.md) — `tienda_items`, economía de Autismo, Ko-fi, roles Discord, premio por descarga.
- [04-perfil-social.md](assets/docs/04-perfil-social.md) — perfil, listas, posts, likes, comments, follows, chat, notificaciones.
- [05-mascota.md](assets/docs/05-mascota.md) — sistema de mascotas tipo tamagotchi/shimeji.
- [06-musica.md](assets/docs/06-musica.md) — reproductor multi-fuente, playlists, Listen-Together.
- [07-pwa-movil.md](assets/docs/07-pwa-movil.md) — Service Worker, manifest, mobile-landing, mobile-detect, push.
- [08-base-datos.md](assets/docs/08-base-datos.md) — esquema, tablas clave, dumps, convenciones de FK.

## Convenciones del repo

- PHP se sirve directamente desde Apache, sin pre-compilación. Vainilla. Solo `require_once` para librerías propias.
- JS se carga inline o como ficheros `.js` en `assets/js/`. Sin build step. ESM no, IIFE+jQuery-style.
- CSS vainilla. Variables CSS para todos los tokens visuales (centralizadas en `assets/css/tokens.css`). Las interfaces solo redefinen tokens.
- BD: PDO con prepared statements. Helpers en `assets/themes/theme-helpers.php` para queries comunes.
- Sesión: cookies HTTP-only de 30 días. Helper `setLongSessionCookie()` antes de cada `session_start()`.

## Comandos útiles

```bash
# Refrescar dump SQL después de cambios en BD
/opt/lampp/bin/mysqldump -u root --databases scrapbook_melon \
    --single-transaction --quick --triggers --default-character-set=utf8mb4 \
    > scrapbook_melon.sql

# Lint PHP
php -l <archivo.php>

# Generar dump solo schema para producción
/opt/lampp/bin/mysqldump -u root --databases scrapbook_melon \
    --no-data --triggers --default-character-set=utf8mb4 \
    | sed 's/`scrapbook_melon`/`u546786567_melonhub`/g' \
    > u546786567_melonhub_schema.sql
```

## Créditos

Hecho a partir de [98.css](https://github.com/jdan/98.css) y muchas tardes. Los iconos de aplicaciones son obra propia o de creative commons.

## Licencia

Proyecto personal sin licencia formal. Si quieres reutilizarlo, contacta antes.
