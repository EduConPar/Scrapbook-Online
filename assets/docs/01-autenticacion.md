# 01 — Autenticación y usuarios

## Tabla `usuarios`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK auto | FK target de medio repo. |
| `user_key` | VARCHAR(50) UNIQUE | `user1`, `user2`, … Generado al registrarse incrementando del máximo existente. |
| `username` | VARCHAR(50) UNIQUE | Lowercase del label. |
| `label` | VARCHAR(50) | Nombre visible. |
| `password` | VARCHAR(255) | bcrypt (`password_hash($pw, PASSWORD_BCRYPT)`). |
| `autismo` | INT NOT NULL DEFAULT 10 | Balance de puntos (economía). |
| `discord_user_id` | VARCHAR(40) NULL | Snowflake del usuario tras OAuth. |
| `created_at` | TIMESTAMP | Default current. |

## Flujo de login

### Vía selector visual

1. `index.php` carga `assets/config.php` → trae todos los users con `SELECT user_key, label, password FROM usuarios`.
2. Renderiza tarjetas con avatar (lee `assets/img/<Label>.<ext>` o emoji fallback).
3. Click → cookie `selectedUser` se setea y se carga la ventana de password con la interfaz/tema del usuario seleccionado (para previsualizar).
4. Submit password → `password_verify()` contra el hash en BD. Si OK:
   - Si móvil: `header('Location: mobile.php')`
   - Si desktop/tablet: `ensureDesktopStub($selectedLabel)` (autoregenera el stub si falta) + `header('Location: desktops/<label>-desktop.php')`

### Vía login manual

`login-manual.php` permite escribir username + password directo. Útil si el selector se rompe o si el user no aparece (registrados nuevos). Misma lógica de verify + redirect.

## Registro de nuevo usuario

`register-user.php` (endpoint POST llamado vía fetch desde el selector):

1. Valida username (3-30 chars, alfanumerico + guiones).
2. Valida password (mín 6 chars).
3. Opcional: foto de perfil multipart (1-5 MB, jpg/png/gif/webp). La guarda como `assets/img/<username>.<ext>` borrando versiones anteriores con otra extensión.
4. Calcula el siguiente `user_key`: max(`user<N>`) + 1.
5. **Crea el stub de escritorio ANTES del INSERT**: `desktops/<username-lower>-desktop.php` con contenido `<?php $desktopLabel = '<Username>'; require __DIR__ . '/../desktop-base.php';`. Usa un flag `$createdStub` para saber si fue ESTA invocación la que lo creó (importante para el rollback).
6. `password_hash($password, PASSWORD_BCRYPT)`.
7. INSERT en `usuarios`. Si falla (típicamente UNIQUE violation por username repetido), `@unlink($desktopStub)` SOLO si `$createdStub` es true. **Sin ese flag, se borraba el stub del usuario legítimo con mismo nombre.**

```php
// register-user.php (resumen)
$desktopStub = __DIR__ . '/desktops/' . strtolower($username) . '-desktop.php';
$createdStub = false;
if (!file_exists($desktopStub)) {
    file_put_contents($desktopStub, "<?php \$desktopLabel = " . var_export($username, true) . "; require __DIR__ . '/../desktop-base.php';\n");
    $createdStub = true;
}
try {
    $pdo->prepare("INSERT INTO usuarios (...) VALUES (?, ?, ?, ?)")
        ->execute([$newKey, $lowerNew, $username, $hash]);
} catch (Throwable $e) {
    if ($createdStub) @unlink($desktopStub);
    jsonError('...');
}
```

## Auto-regeneración del stub

`assets/config.php` define `ensureDesktopStub($label)`. Si el `.php` del escritorio fue borrado por accidente (deploys destructivos, File Manager, etc.), se recrea al vuelo en el siguiente login. Llamado desde:

- `index.php` antes del redirect a `desktops/...`
- `login-manual.php` antes del redirect

Esto evita 404s post-deploy.

## Eliminación de usuario

`delete-user.php` (POST con `current_password` para confirmar):

1. Verifica password actual.
2. Borra archivos en disco:
   - `desktops/<label-lower>-desktop.php`
   - `assets/img/<Label>.<ext>` y variantes
   - `assets/img/wallpapers/<label-lower>-wallpaper.*`
   - `assets/img/start-icons/<label-lower>-start-icon.*`
   - CSS de temas en `assets/themes/`
3. `DELETE FROM usuarios WHERE id = ?` (cascade en la mayoría de tablas vía FK).
4. Limpia tablas que no tienen FK con CASCADE explícitamente: `parejas`, `recordatorios`, `momentos`, `tienda_compras`, etc.

## Sesiones

PHP nativo. Cookie configurada vía `setLongSessionCookie()` (helper en `assets/mobile-detect.php`):

```php
function setLongSessionCookie(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30, // 30 días
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 30));
}
```

DEBE llamarse ANTES de `session_start()`. Todos los endpoints PHP lo siguen.

**Datos en sesión**:

- `$_SESSION['user']` — el `user_key` (string, ej. `user1`).
- `$_SESSION['device_token']` — para móviles que se manejan como dispositivos individuales.
- `$_SESSION['is_pwa']` — flag opcional para distinguir PWA instalada.

## Helper `requireAuth()`

`assets/auth.php`:

```php
function requireAuth(): array {
    session_start();
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    require_once __DIR__ . '/config.php';
    $key = $_SESSION['user'];
    if (!isset($loginUsers[$key])) {
        http_response_code(403);
        echo json_encode(['error' => 'Usuario inválido']);
        exit;
    }
    return ['key' => $key, 'label' => $loginUsers[$key]['label']];
}
```

Llamado al inicio de cada `assets/*/api.php` para garantizar sesión.

## Cambio de contraseña

`assets/auth/change-password.php` (POST JSON):

```json
{ "current": "actual", "new": "nueva" }
```

Verifica `current`, hashea `new` con bcrypt, UPDATE. Reusada por el desktop (start-menu → cambiar contraseña) y por el móvil (Ajustes).

## Notas de seguridad

- Las contraseñas en BD son bcrypt, no recuperables. Para reset administrativo: `UPDATE usuarios SET password = '<hash>' WHERE username = '...';` con un hash generado vía `php -r "echo password_hash('NUEVA', PASSWORD_BCRYPT);"`.
- El `.env` NO debe versionarse — está en `.gitignore`. Si se filtra el `DB_PASS` rota contraseña en hPanel y actualiza.
- Discord bot token es especialmente sensible: si llega a GitHub, Discord lo invalida automáticamente.
- Las APIs no tienen CSRF tokens; confían en `SameSite=Lax` + sesión + same-origin. Acepta que cualquier XSS dentro del dominio rompería todo.
