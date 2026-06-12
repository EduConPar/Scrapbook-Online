# 02 — Interfaces y Temas

Dos sistemas relacionados pero independientes:

- **Interfaces** (packs visuales): cambian la apariencia GLOBAL de la web (chrome de ventanas, taskbar, iconos del start menu, fuentes). `win98` (default) y `kawaii` (MelonOS Overdose). Son carpetas en `assets/interfaces/<slug>/`.
- **Temas** (paletas de colores): cambian las variables CSS sobre la interfaz activa. Cada usuario edita las suyas en la app Temas. Se publican en una biblioteca compartida.

---

## Sistema de Interfaces

### Estructura

```
assets/interfaces/
  win98/
    meta.json
    style.css
    preview.png       (opcional)
    init.js           (opcional)
  kawaii/
    meta.json
    style.css
    preview.png
    init.js           (init para activar .active en .window, etc.)
    login.css         (override scoped al #userPreview del login)
```

`meta.json`:

```json
{
  "name": "kawaii",
  "label": "MelonOS Overdose",
  "description": "Reskin kawaii con rosa y lavanda",
  "isDefault": false,
  "tokens": { /* opcional, tokens custom */ }
}
```

### Cómo se aplica una interfaz

1. `getActiveInterface()` (en `assets/php/active-interface.php`) lee la cookie `activeInterface`. Si está vacía o el slug no existe en disco, default `'win98'`.
2. `emitInterfaceCss($prefix)` emite el `<link rel="stylesheet" href="<prefix>assets/interfaces/<slug>/style.css">` con cache-bust por `filemtime`. Si la interfaz trae `init.js`, también lo emite.
3. En el login: solo se emite `assets/interfaces/<slug>/login.css` (scoped a `#userPreview`) para que solo el cuadro del password reciba el reskin sin afectar al selector.

### Sincronización con BD

Cada usuario tiene su interfaz preferida en `user_settings.active_interface_slug` (JSON string: `"kawaii"`). Al cargar el shell (`desktop-base.php` y `mobile.php`) se lee la BD y se SETEA la cookie. Sin esto, un usuario nuevo o que cambió en otro dispositivo heredaría la cookie del anterior.

```sql
SELECT value FROM user_settings WHERE user_id = ? AND key_name = 'active_interface_slug';
```

### Listado para la app Temas

`assets/php/active-interface.php` define:

- `listInterfaces(): array` — escanea `assets/interfaces/*/` y devuelve todas las disponibles. Sin filtro.
- `listInterfacesForUser(PDO $pdo, ?int $userId): array` — variante per-user: oculta interfaces "premium" (con fila en `tienda_items` categoria='interfaces' y precio>0) que el user NO haya comprado.

La app Temas usa la versión filtrada. El endpoint `assets/themes/api.php?action=library` la usa sin filtrar (para mapear name → label legible al mostrar temas públicos).

### MelonOS Overdose (kawaii) — caso especial

Es una interfaz "premium" del store. Una fila en `tienda_items`:

```
id 8 | nombre 'MelonOS Overdose' | slug 'kawaii' | precio 500 | categoria 'interfaces'
```

Hasta que el usuario la compra:

- No aparece en el grid de Temas (`listInterfacesForUser` la oculta).
- Sí aparece en la pestaña Interfaces de la Tienda como item comprable.
- `tienda_compras` registra la compra.

Tras comprar, el usuario la ve en Temas y puede activarla.

---

## Sistema de Temas

### Tabla `themes`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK | Owner. |
| `interface_name` | VARCHAR(30) | `win98`, `kawaii`. UNIQUE compuesto con `name`. |
| `name` | VARCHAR(30) | Nombre del tema. |
| `colors` | TEXT JSON | Paleta como mapa key → hex. |
| `wallpaper` | VARCHAR(500) | Ruta opcional. |
| `start_icon` | VARCHAR(500) | Ruta opcional. |
| `is_active` | TINYINT(1) | Marca el tema activo para esta (user, interface). |
| `is_public` | TINYINT(1) | Publicado en la biblioteca compartida. |
| `is_downloaded` | TINYINT(1) | Marca si es un tema descargado de otro user (no se puede re-publicar). |
| `updated_at` | TIMESTAMP | Para ordenación. |

`UNIQUE KEY (user_id, interface_name, name)` — un usuario puede tener el MISMO nombre en interfaces distintas.

### API: `assets/themes/api.php`

Acciones (?action=...):

- `get` — temas + activo de la interfaz actual del user.
- `save` — guarda/actualiza/renombra. Accepta `{name, colors, oldName?, interface?, downloaded?, wallpaper?, startIcon?}`.
- `set-active` — activa un tema para la interfaz actual. Devuelve `wallpaper` y `startIcon` para que el frontend los aplique.
- `delete` — borra un tema y su CSS generado.
- `set-public` — publica/despublica. No deja publicar temas descargados.
- `library` — lista temas públicos de la interfaz actual (de todos los usuarios).

### Generación de CSS

Cada save regenera el CSS del tema en disco: `assets/themes/<ThemeName>-<UserLabel>.css`. Lo crea `generateThemeCss(className, colors)` en `assets/themes/theme-helpers.php`. Se inyecta vía `<link rel="stylesheet" id="active-theme-link">` en el `<head>` del shell.

La clase CSS toma el formato `<ThemeName>-<UserLabel>` (ej. `Forthebrainrot-Capi`). Se aplica al `<body>` cuando el tema está activo.

### Tema "activo" — fuente de verdad

`getActiveThemeForInterface($userKey, $label, $interfaceName)` (en `theme-helpers.php`):

1. Mira la cookie `themeFor_<interface>` (la setea apps/temas.php al activar).
2. Si no hay cookie, mira `themes.is_active=1` del BD.
3. Si nada, sin tema (usa defaults de la interfaz).

Esto desacopla la cookie (rápida) de la BD (autoridad). Si la cookie se borra, en el siguiente request se recupera de la BD.

### Override de interfaz en `save`

El endpoint `save` acepta `interface` en el body. Si está presente y la carpeta existe, override del `$iface` derivado de la cookie. Esto permite que el import de temas guarde el archivo en su interfaz de origen aunque el usuario esté en otra.

```php
if (isset($body['interface'])) {
    $reqIface = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$body['interface']);
    if ($reqIface !== '' && is_dir(dirname(__DIR__) . '/interfaces/' . $reqIface)) {
        $iface = $reqIface;
    }
}
```

### Import / Export de temas

En la app Temas (Desktop y Móvil), botones "Exportar" y "Importar":

- **Export**: descarga un JSON con `{format, version: 2, name, interface, exportedAt, colors}`. La versión 2 añade `interface`.
- **Import**: lee el JSON.
  - Si trae `interface` → guarda en esa interfaz vía el override server-side.
  - Si la interfaz del JSON != actual → guarda pero NO auto-activa; pide al user que cambie de interfaz.
  - Si el nombre ya existe → añade sufijo `_2`, `_3`, etc.

### Biblioteca compartida

`apps/temas.php` tiene un tab "Biblioteca" que llama a `?action=library`. Pinta una grid con los temas públicos de la interfaz actual. Cada uno con preview del autor, su avatar, nombre del tema, "Descargar".

Al descargar:

1. POST a `?action=save` con `downloaded: true`, `sourceUserKey`, `sourceThemeName`, y los colors/wallpaper/icon del tema.
2. Server marca `is_downloaded=1` para que no se pueda re-publicar.
3. **Premio**: si `sourceUserKey` y `sourceThemeName` están presentes Y el owner != current user Y no hay fila previa en `theme_download_rewards (theme_owner_id, theme_name, interface_name, downloader_id)`, INSERT IGNORE + UPDATE +50 a `usuarios.autismo` del autor. Dedupe por PK compuesta.

```sql
CREATE TABLE theme_download_rewards (
    theme_owner_id INT,
    theme_name VARCHAR(30),
    interface_name VARCHAR(30),
    downloader_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (theme_owner_id, theme_name, interface_name, downloader_id),
    CONSTRAINT FOREIGN KEY (theme_owner_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT FOREIGN KEY (downloader_id)  REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Editor de temas (apps/temas.php)

La UI tiene:

- Grid de iconos de paquetes (interfaces disponibles).
- Lista de temas guardados del user (con activar/eliminar/publicar).
- Editor de colores: sliders/inputs hex para cada token CSS. Variables agrupadas por categoría (chrome, body, accent, etc.).
- Live preview: aplica los cambios via `document.documentElement.style.setProperty()` mientras editas.
- Botones Save / Save As / Delete / Set Active / Make Public / Export / Import.
- Sub-app "Personalización": wallpaper + start-icon por tema. Sube via `assets/img/wallpapers/save-wallpaper.php` y `assets/img/start-icons/save-start-icon.php`.

## Notas de mantenimiento

- Si añades una interfaz nueva: crea carpeta en `assets/interfaces/<slug>/`, mete `meta.json` + `style.css`, listo. Aparece automáticamente.
- Si quieres que sea premium: añade fila en `tienda_items` con `categoria='interfaces'`, `slug='<nombre>'`, `precio>0`. `listInterfacesForUser()` la oculta hasta que se compre.
- El CSS de los temas se regenera en cada `save`, así que si cambias `generateThemeCss()` y un usuario hace cualquier edición, se actualiza automáticamente. Para usuarios que no editen, el CSS antiguo se queda en disco.
- Los archivos CSS de temas viven en `assets/themes/`. Están en `.gitignore` (excepto los de Capi/Angie que se versionan como seed inicial).
