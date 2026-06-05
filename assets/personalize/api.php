<?php
/* ──────────────────────────────────────────────
   PERSONALIZE API — haros + mascotas activos del usuario
   ──────────────────────────────────────────────
   Endpoints (?action= o body):
     GET  ?action=inventory       → haros y mascotas del usuario + activos
     POST ?action=set-active-haro    {itemId}
     POST ?action=set-active-mascot  {itemId}

   Items "de base" = tienda_items.precio = 0 (todos los usuarios los
   tienen sin necesidad de compra).
   Items "comprados" = el usuario tiene fila en tienda_compras.

   La elección activa se guarda en user_settings con
     key_name = 'active_haro' o 'active_mascot', value = item_id.
   ────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';

$u      = requireAuth();
$userKey = $u['key'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

/* user_key (string) → usuarios.id (int) */
$uid = (function() use ($pdo, $userKey) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $id ? (int)$id : null;
})();
if (!$uid) jsonError('Usuario no encontrado en BD', 500);

/* ── Setup idempotente ──
   Añade columna `slug` a tienda_items si no existe — la usamos para
   mapear el item a sus archivos (gif/png/audio). Y siembra el primer
   haro base ("Verde") asociado al greenHaro existente. */
function ensureSchema(PDO $pdo): void {
    static $done = false;
    if ($done) return;

    /* slug column */
    try {
        $col = $pdo->query("SHOW COLUMNS FROM tienda_items LIKE 'slug'")->fetch();
        if (!$col) {
            $pdo->exec("ALTER TABLE tienda_items ADD COLUMN slug VARCHAR(60) NOT NULL DEFAULT '' AFTER nombre");
        }
    } catch (Throwable $e) { /* sin permisos ALTER → seguimos, fallback en nombre */ }

    /* Seed para una categoría arbitraria de tienda_items.
       Convención de nombres por slug:
         haros    → assets/vids/{slug}Haro.gif (+ -last.png + .mp3)
         mascotas → assets/mascota/skins/{slug}/shimeN.png */
    $seed = function(string $categoria, string $slug, string $nombre, string $desc, int $precio, int $orden) use ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM tienda_items WHERE categoria = ? AND slug = ? LIMIT 1");
            $stmt->execute([$categoria, $slug]);
            if ($stmt->fetchColumn()) return;
            $ins = $pdo->prepare("INSERT INTO tienda_items (nombre, slug, descripcion, precio, icono, activo, orden, categoria)
                                  VALUES (?, ?, ?, ?, '', 1, ?, ?)");
            $ins->execute([$nombre, $slug, $desc, $precio, $orden, $categoria]);
        } catch (Throwable $e) { /* slug column quizá no existe — ignoramos */ }
    };

    try {
        /* Migra cualquier seed anterior con slug='verde' a 'green' y
           vacía iconos emoji previos — los haros usan el PNG del slug
           como icono visible (la convención manda). */
        $pdo->exec("UPDATE tienda_items SET slug = 'green' WHERE categoria = 'haros' AND slug = 'verde'");
        $pdo->exec("UPDATE tienda_items SET icono = '' WHERE categoria = 'haros' AND icono <> ''");
    } catch (Throwable $e) {}

    /* Haros */
    $seed('haros', 'green',  'Verde',    'Haro verde clásico. Lo tienes por defecto.', 0,  1);
    $seed('haros', 'yellow', 'Amarillo', 'Haro amarillo',                              50, 2);
    /* Mascotas — la skin se aplica a la PRÓXIMA mascota que crees
       (la actual mantiene su skin original). */
    $seed('mascotas', 'gabriel', 'Gabriel', 'Mascota Gabriel — angel shimeji con 46 frames.', 0, 1);

    $done = true;
}

/* Items que el usuario posee de una categoría (base + comprados). */
function loadOwnedItems(PDO $pdo, int $uid, string $categoria): array {
    $sql = "SELECT i.id, i.nombre, i.slug, i.icono, i.precio, i.descripcion,
                   (i.precio = 0) AS is_base,
                   EXISTS (SELECT 1 FROM tienda_compras c WHERE c.user_id = ? AND c.item_id = i.id) AS owned
            FROM tienda_items i
            WHERE i.activo = 1 AND i.categoria = ?
              AND (i.precio = 0
                   OR EXISTS (SELECT 1 FROM tienda_compras c WHERE c.user_id = ? AND c.item_id = i.id))
            ORDER BY i.orden ASC, i.id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $categoria, $uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(function($r) {
        return [
            'id'          => (int)$r['id'],
            'nombre'      => $r['nombre'],
            'slug'        => $r['slug'] ?? '',
            'icono'       => $r['icono'],
            'descripcion' => $r['descripcion'],
            'isBase'      => (bool)$r['is_base'],
        ];
    }, $rows);
}

function getActiveItemId(PDO $pdo, int $uid, string $keyName): ?int {
    $stmt = $pdo->prepare("SELECT value FROM user_settings WHERE user_id = ? AND key_name = ?");
    $stmt->execute([$uid, $keyName]);
    $v = $stmt->fetchColumn();
    if ($v === false) return null;
    $v = is_numeric($v) ? (int)$v : null;
    return $v ?: null;
}

function setActiveItemId(PDO $pdo, int $uid, string $keyName, int $itemId): void {
    $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, key_name, value)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([$uid, $keyName, (string)$itemId]);
}

/* Verifica que el usuario posea el ítem (base o comprado) antes de
   permitir activarlo. */
function userOwnsItem(PDO $pdo, int $uid, int $itemId, string $categoria): bool {
    $stmt = $pdo->prepare("SELECT id, precio FROM tienda_items WHERE id = ? AND categoria = ? AND activo = 1");
    $stmt->execute([$itemId, $categoria]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;
    if ((int)$row['precio'] === 0) return true; /* base */
    $stmt = $pdo->prepare("SELECT 1 FROM tienda_compras WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$uid, $itemId]);
    return (bool) $stmt->fetchColumn();
}

ensureSchema($pdo);

switch ($action) {

case 'inventory': {
    /* "Interfaces" = items con categoria='interfaces'. Son skins /
       apariencias completas, totalmente independientes de los TEMAS
       DE COLORES del usuario (esos viven en la tabla `themes` y los
       gestiona el editor del sidebar "Mis temas"). */
    $interfaces = loadOwnedItems($pdo, $uid, 'interfaces');
    $haros      = loadOwnedItems($pdo, $uid, 'haros');
    $mascots    = loadOwnedItems($pdo, $uid, 'mascotas');

    $activeInterface = getActiveItemId($pdo, $uid, 'active_interface');
    $activeHaro      = getActiveItemId($pdo, $uid, 'active_haro');
    $activeMascot    = getActiveItemId($pdo, $uid, 'active_mascot');

    /* Si no hay activo en BD, cae al primer ítem de la lista (siempre
       es base, ya que orden los pone primero). */
    if ($activeInterface === null && $interfaces) $activeInterface = $interfaces[0]['id'];
    if ($activeHaro      === null && $haros)      $activeHaro      = $haros[0]['id'];
    if ($activeMascot    === null && $mascots)    $activeMascot    = $mascots[0]['id'];

    jsonResponse([
        'ok'              => true,
        'interfaces'      => $interfaces,
        'haros'           => $haros,
        'mascots'         => $mascots,
        'activeInterface' => $activeInterface,
        'activeHaro'      => $activeHaro,
        'activeMascot'    => $activeMascot,
    ]);
}

case 'set-active-interface': {
    $body = jsonBody();
    $itemId = (int)($body['itemId'] ?? 0);
    if (!$itemId) jsonError('itemId requerido');
    if (!userOwnsItem($pdo, $uid, $itemId, 'interfaces')) jsonError('No tienes esta interfaz', 403);
    setActiveItemId($pdo, $uid, 'active_interface', $itemId);
    $stmt = $pdo->prepare("SELECT slug FROM tienda_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $slug = (string) $stmt->fetchColumn();
    jsonResponse(['ok' => true, 'slug' => $slug]);
}

case 'set-active-haro': {
    $body = jsonBody();
    $itemId = (int)($body['itemId'] ?? 0);
    if (!$itemId) jsonError('itemId requerido');
    if (!userOwnsItem($pdo, $uid, $itemId, 'haros')) jsonError('No tienes este haro', 403);
    setActiveItemId($pdo, $uid, 'active_haro', $itemId);
    /* Devuelve el slug para que el cliente actualice ya el sistema de
       notificaciones sin recargar. */
    $stmt = $pdo->prepare("SELECT slug FROM tienda_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $slug = (string) $stmt->fetchColumn();
    jsonResponse(['ok' => true, 'slug' => $slug]);
}

case 'set-active-mascot': {
    $body = jsonBody();
    $itemId = (int)($body['itemId'] ?? 0);
    if (!$itemId) jsonError('itemId requerido');
    if (!userOwnsItem($pdo, $uid, $itemId, 'mascotas')) jsonError('No tienes esta mascota', 403);
    setActiveItemId($pdo, $uid, 'active_mascot', $itemId);
    jsonResponse(['ok' => true]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
