<?php
/* ──────────────────────────────────────────────────────────
   DESKTOP API — iconos, carpetas y user_settings (player...)
   ──────────────────────────────────────────────────────────
   GET  ?action=get-all            → estado completo del escritorio
       → { icons: {iconId: {left,top}},
           folders: [{id,name,pos:{left,top},children:[iconId,...]}],
           player: {playlistId, trackIndex}|null }

   POST ?action=save-icon          { id, left, top }
   POST ?action=save-folder        { id, name, pos:{left,top}, children:[...] }
   POST ?action=delete-folder      { id }
   POST ?action=save-player        { playlistId, trackIndex }
   ────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';

$u       = requireAuth();
$userKey = $u['key'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

function dsk_uid(PDO $pdo, string $userKey): ?int {
    static $cache = [];
    if (array_key_exists($userKey, $cache)) return $cache[$userKey];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $cache[$userKey] = $id ? (int)$id : null;
}

function dsk_sanId(string $id): string {
    return substr(preg_replace('/[^A-Za-z0-9_-]/', '', $id), 0, 60);
}

$uid = dsk_uid($pdo, $userKey);
if (!$uid) jsonError('Usuario no encontrado', 500);

switch ($action) {

case 'get-all': {
    /* Iconos */
    $stmt = $pdo->prepare("SELECT icon_id, pos_left, pos_top FROM desktop_icons WHERE user_id = ?");
    $stmt->execute([$uid]);
    $icons = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $icons[$r['icon_id']] = ['left' => (int)$r['pos_left'], 'top' => (int)$r['pos_top']];
    }
    /* Carpetas + sus children en bulk */
    $stmt = $pdo->prepare("SELECT id, name, pos_left, pos_top FROM desktop_folders
                           WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$uid]);
    $folders = [];
    $idx = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $folders[] = [
            'id'       => $r['id'],
            'name'     => $r['name'],
            'pos'      => ['left' => (int)$r['pos_left'], 'top' => (int)$r['pos_top']],
            'children' => [],
        ];
        $idx[$r['id']] = count($folders) - 1;
    }
    if (!empty($folders)) {
        $stmt = $pdo->prepare("SELECT fi.folder_id, fi.icon_id
                               FROM desktop_folder_items fi
                               JOIN desktop_folders f ON fi.folder_id = f.id
                               WHERE f.user_id = ?
                               ORDER BY fi.position ASC");
        $stmt->execute([$uid]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($idx[$r['folder_id']])) {
                $folders[$idx[$r['folder_id']]]['children'][] = $r['icon_id'];
            }
        }
    }
    /* user_settings: player */
    $stmt = $pdo->prepare("SELECT value FROM user_settings WHERE user_id = ? AND key_name = 'player'");
    $stmt->execute([$uid]);
    $playerRaw = $stmt->fetchColumn();
    $player = $playerRaw ? json_decode($playerRaw, true) : null;

    jsonResponse([
        'ok'      => true,
        'icons'   => $icons ?: new stdClass(),
        'folders' => $folders,
        'player'  => $player,
    ]);
}

case 'save-icon': {
    $b   = jsonBody();
    $id  = dsk_sanId($b['id'] ?? '');
    if ($id === '') jsonError('ID inválido');
    $l   = (int)($b['left'] ?? 0);
    $t   = (int)($b['top']  ?? 0);
    $pdo->prepare("INSERT INTO desktop_icons (user_id, icon_id, pos_left, pos_top)
                   VALUES (?, ?, ?, ?)
                   ON DUPLICATE KEY UPDATE pos_left = VALUES(pos_left), pos_top = VALUES(pos_top)")
        ->execute([$uid, $id, $l, $t]);
    jsonResponse(['ok' => true]);
}

case 'save-folder': {
    $b   = jsonBody();
    $id  = dsk_sanId($b['id'] ?? '');
    if ($id === '') jsonError('ID inválido');
    $name = mb_substr(trim($b['name'] ?? ''), 0, 40);
    if ($name === '') jsonError('Nombre inválido');
    $pos = $b['pos'] ?? ['left' => 0, 'top' => 0];
    $l   = (int)($pos['left'] ?? 0);
    $t   = (int)($pos['top']  ?? 0);
    $childrenRaw = $b['children'] ?? [];
    if (!is_array($childrenRaw)) $childrenRaw = [];
    $children = [];
    foreach ($childrenRaw as $cid) {
        $c = dsk_sanId((string)$cid);
        if ($c !== '') $children[] = $c;
    }

    $pdo->beginTransaction();
    try {
        /* UPSERT carpeta. Validamos la propiedad: si ya existe y user_id no
           coincide, abortamos. */
        $stmt = $pdo->prepare("SELECT user_id FROM desktop_folders WHERE id = ?");
        $stmt->execute([$id]);
        $owner = $stmt->fetchColumn();
        if ($owner !== false && (int)$owner !== $uid) jsonError('No autorizado', 403);

        $pdo->prepare("INSERT INTO desktop_folders (id, user_id, name, pos_left, pos_top)
                       VALUES (?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE name = VALUES(name),
                           pos_left = VALUES(pos_left), pos_top = VALUES(pos_top)")
            ->execute([$id, $uid, $name, $l, $t]);

        /* Reemplazar children: DELETE + INSERT preservando posición */
        $pdo->prepare("DELETE FROM desktop_folder_items WHERE folder_id = ?")->execute([$id]);
        if (!empty($children)) {
            $stmt = $pdo->prepare("INSERT INTO desktop_folder_items (folder_id, icon_id, position) VALUES (?, ?, ?)");
            foreach ($children as $pos => $cid) {
                $stmt->execute([$id, $cid, $pos]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    jsonResponse(['ok' => true]);
}

case 'delete-folder': {
    $id = dsk_sanId(jsonBody()['id'] ?? '');
    if ($id === '') jsonError('ID inválido');
    /* CASCADE borra los desktop_folder_items asociados */
    $stmt = $pdo->prepare("DELETE FROM desktop_folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $uid]);
    /* También quitar la entrada del propio fld-xxx si estaba dentro de otra
       carpeta (icon_id apunta a ella desde su padre) */
    $pdo->prepare("DELETE FROM desktop_folder_items WHERE icon_id = ?
                   AND folder_id IN (SELECT id FROM desktop_folders WHERE user_id = ?)")
        ->execute([$id, $uid]);
    /* Y cualquier posición de icono guardada con ese ID */
    $pdo->prepare("DELETE FROM desktop_icons WHERE user_id = ? AND icon_id = ?")
        ->execute([$uid, $id]);
    jsonResponse(['ok' => true]);
}

case 'save-player': {
    $b = jsonBody();
    /* Persistir solo los campos conocidos del estado del player. Cualquier
       otra clave que mande el cliente se ignora. */
    $payload = [
        'playlistId' => $b['playlistId'] ?? null,
        'trackIndex' => isset($b['trackIndex']) ? (int)$b['trackIndex'] : 0,
    ];
    if (isset($b['volume'])) $payload['volume'] = max(0, min(100, (int)$b['volume']));
    $value = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $pdo->prepare("INSERT INTO user_settings (user_id, key_name, value)
                   VALUES (?, 'player', ?)
                   ON DUPLICATE KEY UPDATE value = VALUES(value)")
        ->execute([$uid, $value]);
    jsonResponse(['ok' => true]);
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
