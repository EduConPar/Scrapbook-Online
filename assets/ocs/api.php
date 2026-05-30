<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/assets/themes/theme-helpers.php';

/* ── Auth ── */
$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

/* Obtener user_id numérico */
$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE user_key = ?');
$stmt->execute([$userKey]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$userRow) {
    http_response_code(403);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}
$userId = (int)$userRow['id'];

$action = $_GET['action'] ?? '';

/* ── Leer body JSON ── */
function getBody(): array {
    $raw = file_get_contents('php://input');
    return $raw ? (json_decode($raw, true) ?? []) : [];
}

function sanitize(string $s, int $max = 255): string {
    return mb_substr(trim(strip_tags($s)), 0, $max);
}

/* ══════════════════════════════════════════════
   OCs
══════════════════════════════════════════════ */

/* list — todos los OCs del usuario con sus categorías */
if ($action === 'list') {
    $rows = $pdo->prepare('
        SELECT o.*,
               GROUP_CONCAT(DISTINCT CONCAT(c.id, ":", c.nombre, ":", c.color) ORDER BY c.nombre SEPARATOR "|") AS cats
        FROM ocs o
        LEFT JOIN oc_categoria_rel r ON r.oc_id = o.id
        LEFT JOIN oc_categorias c ON c.id = r.categoria_id
        WHERE o.user_id = ?
        ORDER BY o.orden ASC, o.id DESC
    ');
    $rows->execute([$userId]);
    $ocs = [];
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cats = [];
        if ($row['cats']) {
            foreach (explode('|', $row['cats']) as $part) {
                [$cid, $cnom, $ccol] = explode(':', $part, 3);
                $cats[] = ['id' => (int)$cid, 'nombre' => $cnom, 'color' => $ccol];
            }
        }
        $row['categorias'] = $cats;
        unset($row['cats'], $row['user_id']);
        $ocs[] = $row;
    }
    echo json_encode(['ok' => true, 'ocs' => $ocs]);
    exit;
}

/* get — un OC con galería */
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $oc = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$oc) { echo json_encode(['error' => 'No encontrado']); exit; }
    unset($oc['user_id']);

    /* Categorías */
    $cs = $pdo->prepare('
        SELECT c.id, c.nombre, c.color FROM oc_categorias c
        JOIN oc_categoria_rel r ON r.categoria_id = c.id
        WHERE r.oc_id = ?
    ');
    $cs->execute([$id]);
    $oc['categorias'] = $cs->fetchAll(PDO::FETCH_ASSOC);

    /* Galería extra */
    $gs = $pdo->prepare('SELECT id, drive_id, descripcion, orden FROM oc_galeria WHERE oc_id = ? ORDER BY orden ASC');
    $gs->execute([$id]);
    $oc['galeria'] = $gs->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'oc' => $oc]);
    exit;
}

/* create */
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b = getBody();
    $nombre   = sanitize($b['nombre'] ?? '', 100);
    $foto_id  = sanitize($b['foto_id'] ?? '', 100);
    $descripcion = sanitize($b['descripcion'] ?? '', 5000);
    if (!$nombre) { echo json_encode(['error' => 'Nombre requerido']); exit; }

    $stats = [];
    foreach (['edad','altura','genero','ojos','cabello','zodiaco','especie',
          'alias','orientacion','pronombres','relacion','etnia','enneagrama',
          'mbti','estatus','residencia','alineamiento','caracter',
          'fecha_nacimiento','ocupacion','peso'] as $k) {
    $stats[$k] = sanitize($b['stats'][$k] ?? '', 100);
}
    }

    $stmt = $pdo->prepare('
    INSERT INTO ocs (user_id, nombre, foto_id, descripcion, edad, altura, genero, ojos, cabello, zodiaco, especie,
        alias, orientacion, pronombres, relacion, etnia, enneagrama, mbti, estatus, residencia, alineamiento, caracter, fecha_nacimiento, ocupacion, peso)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$stmt->execute([
    $userId, $nombre, $foto_id, $descripcion,
    $stats['edad'], $stats['altura'], $stats['genero'],
    $stats['ojos'], $stats['cabello'], $stats['zodiaco'], $stats['especie'],
    $stats['alias'], $stats['orientacion'], $stats['pronombres'], $stats['relacion'],
    $stats['etnia'], $stats['enneagrama'], $stats['mbti'], $stats['estatus'],
    $stats['residencia'], $stats['alineamiento'], $stats['caracter'],
    $stats['fecha_nacimiento'], $stats['ocupacion'], $stats['peso']
]);
    $newId = (int)$pdo->lastInsertId();

    /* Categorías */
    if (!empty($b['categorias']) && is_array($b['categorias'])) {
        $ins = $pdo->prepare('INSERT IGNORE INTO oc_categoria_rel (oc_id, categoria_id) VALUES (?, ?)');
        foreach ($b['categorias'] as $cid) {
            $ins->execute([$newId, (int)$cid]);
        }
    }

    echo json_encode(['ok' => true, 'id' => $newId]);
    exit;
}

/* update */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT id FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'No encontrado']); exit; }

    $nombre      = sanitize($b['nombre'] ?? '', 100);
    $foto_id     = sanitize($b['foto_id'] ?? '', 100);
    $descripcion = sanitize($b['descripcion'] ?? '', 5000);
    if (!$nombre) { echo json_encode(['error' => 'Nombre requerido']); exit; }

    $stats = [];
   foreach (['edad','altura','genero','ojos','cabello','zodiaco','especie',
          'alias','orientacion','pronombres','relacion','etnia','enneagrama',
          'mbti','estatus','residencia','alineamiento','caracter',
          'fecha_nacimiento','ocupacion','peso'] as $k) {
    $stats[$k] = sanitize($b['stats'][$k] ?? '', 100);
}

    $pdo->prepare('
    UPDATE ocs SET nombre=?, foto_id=?, descripcion=?,
        edad=?, altura=?, genero=?, ojos=?, cabello=?, zodiaco=?, especie=?,
        alias=?, orientacion=?, pronombres=?, relacion=?, etnia=?, enneagrama=?,
        mbti=?, estatus=?, residencia=?, alineamiento=?, caracter=?,
        fecha_nacimiento=?, ocupacion=?, peso=?
    WHERE id = ?
')->execute([
    $nombre, $foto_id, $descripcion,
    $stats['edad'], $stats['altura'], $stats['genero'],
    $stats['ojos'], $stats['cabello'], $stats['zodiaco'], $stats['especie'],
    $stats['alias'], $stats['orientacion'], $stats['pronombres'], $stats['relacion'],
    $stats['etnia'], $stats['enneagrama'], $stats['mbti'], $stats['estatus'],
    $stats['residencia'], $stats['alineamiento'], $stats['caracter'],
    $stats['fecha_nacimiento'], $stats['ocupacion'], $stats['peso'],
    $id
]);

    /* Reconstruir categorías */
    $pdo->prepare('DELETE FROM oc_categoria_rel WHERE oc_id = ?')->execute([$id]);
    if (!empty($b['categorias']) && is_array($b['categorias'])) {
        $ins = $pdo->prepare('INSERT IGNORE INTO oc_categoria_rel (oc_id, categoria_id) VALUES (?, ?)');
        foreach ($b['categorias'] as $cid) {
            $ins->execute([$id, (int)$cid]);
        }
    }

    echo json_encode(['ok' => true]);
    exit;
}

/* delete */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'No encontrado']); exit; }
    $pdo->prepare('DELETE FROM ocs WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

/* reorder — recibe array de ids en el nuevo orden */
if ($action === 'reorder' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b   = getBody();
    $ids = $b['ids'] ?? [];
    if (!is_array($ids)) { echo json_encode(['error' => 'ids debe ser array']); exit; }
    $upd = $pdo->prepare('UPDATE ocs SET orden = ? WHERE id = ? AND user_id = ?');
    foreach ($ids as $pos => $oid) {
        $upd->execute([$pos, (int)$oid, $userId]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

/* list_all — OCs de todos los usuarios agrupados */
if ($action === 'list_all') {
    $rows = $pdo->prepare('
        SELECT o.*, u.username, u.label,
               GROUP_CONCAT(DISTINCT CONCAT(c.id, ":", c.nombre, ":", c.color) ORDER BY c.nombre SEPARATOR "|") AS cats
        FROM ocs o
        JOIN usuarios u ON u.id = o.user_id
        LEFT JOIN oc_categoria_rel r ON r.oc_id = o.id
        LEFT JOIN oc_categorias c ON c.id = r.categoria_id
        GROUP BY o.id
        ORDER BY u.id ASC, o.orden ASC, o.id DESC
    ');
    $rows->execute();
    $byUser = [];
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cats = [];
        if ($row['cats']) {
            foreach (explode('|', $row['cats']) as $part) {
                [$cid, $cnom, $ccol] = explode(':', $part, 3);
                $cats[] = ['id' => (int)$cid, 'nombre' => $cnom, 'color' => $ccol];
            }
        }
        $row['categorias'] = $cats;
        unset($row['cats']);
        $uid = (int)$row['user_id'];
        if (!isset($byUser[$uid])) {
            $byUser[$uid] = ['id' => $uid, 'username' => $row['username'], 'label' => $row['label'], 'ocs' => []];
        }
        unset($row['username'], $row['label']);
        $byUser[$uid]['ocs'][] = $row;
    }
    echo json_encode(['ok' => true, 'users' => array_values($byUser)]);
    exit;
}

/* ══════════════════════════════════════════════
   CATEGORÍAS
══════════════════════════════════════════════ */

if ($action === 'categorias_list') {
    $stmt = $pdo->prepare('
        SELECT c.*, COUNT(r.oc_id) AS total
        FROM oc_categorias c
        LEFT JOIN oc_categoria_rel r ON r.categoria_id = c.id
        WHERE c.user_id = ?
        GROUP BY c.id
        ORDER BY c.nombre ASC
    ');
    $stmt->execute([$userId]);
    echo json_encode(['ok' => true, 'categorias' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($action === 'categoria_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b      = getBody();
    $nombre = sanitize($b['nombre'] ?? '', 60);
    $color  = preg_match('/^#[0-9a-fA-F]{6}$/', $b['color'] ?? '') ? $b['color'] : '#888888';
    if (!$nombre) { echo json_encode(['error' => 'Nombre requerido']); exit; }
    $stmt = $pdo->prepare('INSERT INTO oc_categorias (user_id, nombre, color) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $nombre, $color]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId(), 'nombre' => $nombre, 'color' => $color]);
    exit;
}

if ($action === 'categoria_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id FROM oc_categorias WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'No encontrado']); exit; }
    $pdo->prepare('DELETE FROM oc_categorias WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

/* ══════════════════════════════════════════════
   GALERÍA DEL OC
══════════════════════════════════════════════ */

if ($action === 'galeria_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b        = getBody();
    $oc_id    = (int)($b['oc_id'] ?? 0);
    $drive_id = sanitize($b['drive_id'] ?? '', 100);
    $desc     = sanitize($b['descripcion'] ?? '', 255);

    $stmt = $pdo->prepare('SELECT id FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$oc_id, $userId]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'OC no encontrado']); exit; }

    $stmt = $pdo->prepare('INSERT INTO oc_galeria (oc_id, drive_id, descripcion) VALUES (?, ?, ?)');
    $stmt->execute([$oc_id, $drive_id, $desc]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;
}

if ($action === 'galeria_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);
    /* Verificar que el OC pertenece al usuario */
    $stmt = $pdo->prepare('
        SELECT g.id FROM oc_galeria g
        JOIN ocs o ON o.id = g.oc_id
        WHERE g.id = ? AND o.user_id = ?
    ');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'No encontrado']); exit; }
    $pdo->prepare('DELETE FROM oc_galeria WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción desconocida: ' . htmlspecialchars($action)]);