<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

/* ── Auth ── */
$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}
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

/* Lista FIJA de campos «stats» que el frontend manda y la BD acepta.
   Si añades uno aquí, recuerda añadirlo también en los INSERT/UPDATE. */
$OC_STAT_FIELDS = [
    'edad','altura','genero','ojos','cabello','zodiaco','especie',
    'alias','orientacion','pronombres','relacion','etnia','enneagrama',
    'mbti','estatus','residencia','alineamiento','caracter',
    'fecha_nacimiento','ocupacion','peso',
];

/* ── Helpers ── */
function getBody(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}
function sanitize(string $s, int $max = 255): string {
    return mb_substr(trim(strip_tags($s)), 0, $max);
}
function readStats(array $b, array $fields): array {
    $raw = (isset($b['stats']) && is_array($b['stats'])) ? $b['stats'] : [];
    $out = [];
    foreach ($fields as $k) {
        $out[$k] = sanitize(isset($raw[$k]) ? (string)$raw[$k] : '', 100);
    }
    return $out;
}
/* Devuelve solo los category IDs PROPIOS del usuario que se pueden
   asignar al OC (igual que las etiquetas de la galería: nada global). */
function allowedCategoryIds(PDO $pdo, int $userId, $raw): array {
    if (!is_array($raw) || !$raw) return [];
    $ids = [];
    foreach ($raw as $v) {
        $n = (int)$v;
        if ($n > 0) $ids[$n] = true;
    }
    if (!$ids) return [];
    $ids = array_keys($ids);
    $place = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id FROM oc_categorias
                           WHERE id IN ($place) AND user_id = ?");
    $stmt->execute(array_merge($ids, [$userId]));
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/* Resuelve nombres (los que entra el usuario como #tag en el formulario)
   a IDs de categoría. Reutiliza las propias del usuario o las globales si
   coincide el nombre (case-insensitive). Las que NO existan se crean al
   vuelo con color por defecto. Pensado para el flujo tipo «#tags» de la
   galería. */
function resolveCategoryNames(PDO $pdo, int $userId, $raw): array {
    if (!is_array($raw) || !$raw) return [];
    $names = [];
    foreach ($raw as $n) {
        if (!is_string($n)) continue;
        $clean = trim(mb_strtolower($n));
        if ($clean === '' || mb_strlen($clean) > 60) continue;
        $names[$clean] = $n;            /* guardamos también la original casing */
    }
    if (!$names) return [];
    /* Buscar las que ya existen del usuario (sin globales). */
    $lookupKeys = array_keys($names);
    $place = implode(',', array_fill(0, count($lookupKeys), '?'));
    $stmt = $pdo->prepare("SELECT id, nombre FROM oc_categorias
                           WHERE LOWER(nombre) IN ($place) AND user_id = ?");
    $stmt->execute(array_merge($lookupKeys, [$userId]));
    $found = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $found[mb_strtolower($r['nombre'])] = (int)$r['id'];
    }
    $out = array_values($found);
    /* Crear las que faltan, con color por defecto */
    $ins = $pdo->prepare('INSERT INTO oc_categorias (user_id, nombre, color) VALUES (?, ?, ?)');
    foreach ($names as $lower => $original) {
        if (isset($found[$lower])) continue;
        $ins->execute([$userId, trim($original), '#888888']);
        $out[] = (int)$pdo->lastInsertId();
    }
    return $out;
}
function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
}
function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

/* ══════════════════════════════════════════════
   ROUTER
   ══════════════════════════════════════════════ */
switch ($action) {

/* ── OCs ── */
case 'list':
    $rows = $pdo->prepare('
        SELECT o.*,
               GROUP_CONCAT(DISTINCT CONCAT(c.id, ":", c.nombre, ":", c.color)
                            ORDER BY c.nombre SEPARATOR "|") AS cats
        FROM ocs o
        LEFT JOIN oc_categoria_rel r ON r.oc_id = o.id
        LEFT JOIN oc_categorias c    ON c.id    = r.categoria_id
        WHERE o.user_id = ?
        GROUP BY o.id
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

case 'get':
    $id = (int)($_GET['id'] ?? 0);
    /* Lectura abierta: cualquier usuario autenticado puede ver la ficha
       de cualquier OC (la app ya muestra la biblioteca cruzada via
       `list_all`). Las acciones de escritura (update/delete) sí siguen
       exigiendo `user_id = userId`. */
    $stmt = $pdo->prepare('SELECT * FROM ocs WHERE id = ?');
    $stmt->execute([$id]);
    $oc = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$oc) jsonError('No encontrado', 404);
    unset($oc['user_id']);

    $cs = $pdo->prepare('
        SELECT c.id, c.nombre, c.color FROM oc_categorias c
        JOIN oc_categoria_rel r ON r.categoria_id = c.id
        WHERE r.oc_id = ?
    ');
    $cs->execute([$id]);
    $oc['categorias'] = $cs->fetchAll(PDO::FETCH_ASSOC);

    $gs = $pdo->prepare('SELECT id, drive_id, descripcion, orden FROM oc_galeria WHERE oc_id = ? ORDER BY orden ASC, id ASC');
    $gs->execute([$id]);
    $oc['galeria'] = $gs->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'oc' => $oc]);
    exit;

case 'create':
    requirePost();
    $b = getBody();
    $nombre      = sanitize((string)($b['nombre']      ?? ''), 100);
    $foto_id     = sanitize((string)($b['foto_id']     ?? ''), 100);
    $descripcion = sanitize((string)($b['descripcion'] ?? ''), 5000);
    if ($nombre === '') jsonError('Nombre requerido');
    $stats = readStats($b, $OC_STAT_FIELDS);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('
            INSERT INTO ocs (user_id, nombre, foto_id, descripcion,
                edad, altura, genero, ojos, cabello, zodiaco, especie,
                alias, orientacion, pronombres, relacion, etnia, enneagrama, mbti,
                estatus, residencia, alineamiento, caracter,
                fecha_nacimiento, ocupacion, peso)
            VALUES (?, ?, ?, ?,  ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?, ?,  ?, ?, ?)
        ');
        $stmt->execute([
            $userId, $nombre, $foto_id, $descripcion,
            $stats['edad'], $stats['altura'], $stats['genero'],
            $stats['ojos'], $stats['cabello'], $stats['zodiaco'], $stats['especie'],
            $stats['alias'], $stats['orientacion'], $stats['pronombres'], $stats['relacion'],
            $stats['etnia'], $stats['enneagrama'], $stats['mbti'],
            $stats['estatus'], $stats['residencia'], $stats['alineamiento'], $stats['caracter'],
            $stats['fecha_nacimiento'], $stats['ocupacion'], $stats['peso'],
        ]);
        $newId = (int)$pdo->lastInsertId();

        $catIds = array_unique(array_merge(
            allowedCategoryIds  ($pdo, $userId, $b['categorias']      ?? []),
            resolveCategoryNames($pdo, $userId, $b['categoriasNames'] ?? [])
        ));
        if ($catIds) {
            $ins = $pdo->prepare('INSERT IGNORE INTO oc_categoria_rel (oc_id, categoria_id) VALUES (?, ?)');
            foreach ($catIds as $cid) $ins->execute([$newId, $cid]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        jsonError('Error al crear: ' . $e->getMessage(), 500);
    }
    echo json_encode(['ok' => true, 'id' => $newId]);
    exit;

case 'update':
    requirePost();
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT id FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) jsonError('No encontrado', 404);

    $nombre      = sanitize((string)($b['nombre']      ?? ''), 100);
    $foto_id     = sanitize((string)($b['foto_id']     ?? ''), 100);
    $descripcion = sanitize((string)($b['descripcion'] ?? ''), 5000);
    if ($nombre === '') jsonError('Nombre requerido');
    $stats = readStats($b, $OC_STAT_FIELDS);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('
            UPDATE ocs SET nombre = ?, foto_id = ?, descripcion = ?,
                edad = ?, altura = ?, genero = ?, ojos = ?, cabello = ?, zodiaco = ?, especie = ?,
                alias = ?, orientacion = ?, pronombres = ?, relacion = ?, etnia = ?, enneagrama = ?,
                mbti = ?, estatus = ?, residencia = ?, alineamiento = ?, caracter = ?,
                fecha_nacimiento = ?, ocupacion = ?, peso = ?
            WHERE id = ? AND user_id = ?
        ')->execute([
            $nombre, $foto_id, $descripcion,
            $stats['edad'], $stats['altura'], $stats['genero'],
            $stats['ojos'], $stats['cabello'], $stats['zodiaco'], $stats['especie'],
            $stats['alias'], $stats['orientacion'], $stats['pronombres'], $stats['relacion'],
            $stats['etnia'], $stats['enneagrama'], $stats['mbti'],
            $stats['estatus'], $stats['residencia'], $stats['alineamiento'], $stats['caracter'],
            $stats['fecha_nacimiento'], $stats['ocupacion'], $stats['peso'],
            $id, $userId,
        ]);

        $pdo->prepare('DELETE FROM oc_categoria_rel WHERE oc_id = ?')->execute([$id]);
        $catIds = array_unique(array_merge(
            allowedCategoryIds  ($pdo, $userId, $b['categorias']      ?? []),
            resolveCategoryNames($pdo, $userId, $b['categoriasNames'] ?? [])
        ));
        if ($catIds) {
            $ins = $pdo->prepare('INSERT IGNORE INTO oc_categoria_rel (oc_id, categoria_id) VALUES (?, ?)');
            foreach ($catIds as $cid) $ins->execute([$id, $cid]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        jsonError('Error al actualizar: ' . $e->getMessage(), 500);
    }
    echo json_encode(['ok' => true]);
    exit;

case 'delete':
    requirePost();
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) jsonError('No encontrado', 404);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM oc_galeria       WHERE oc_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM oc_categoria_rel WHERE oc_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM ocs              WHERE id    = ? AND user_id = ?')->execute([$id, $userId]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        jsonError('Error al borrar: ' . $e->getMessage(), 500);
    }
    echo json_encode(['ok' => true]);
    exit;

case 'reorder':
    requirePost();
    $b   = getBody();
    $ids = $b['ids'] ?? [];
    if (!is_array($ids)) jsonError('ids debe ser array');
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare('UPDATE ocs SET orden = ? WHERE id = ? AND user_id = ?');
        foreach ($ids as $pos => $oid) {
            $upd->execute([(int)$pos, (int)$oid, $userId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        jsonError('Error al reordenar: ' . $e->getMessage(), 500);
    }
    echo json_encode(['ok' => true]);
    exit;

case 'list_all':
    /* OCs de TODOS los usuarios, agrupados por autor. La biblioteca es
       compartida intencionalmente (el frontend ofrece un selector de usuario).
       Incluimos también las categorías PROPIAS de cada usuario para que la
       sidebar pueda renderizar las del usuario que estás viendo, no las tuyas. */
    $rows = $pdo->prepare('
        SELECT o.*, u.username, u.label,
               GROUP_CONCAT(DISTINCT CONCAT(c.id, ":", c.nombre, ":", c.color)
                            ORDER BY c.nombre SEPARATOR "|") AS cats
        FROM ocs o
        JOIN usuarios u            ON u.id    = o.user_id
        LEFT JOIN oc_categoria_rel r ON r.oc_id = o.id
        LEFT JOIN oc_categorias    c ON c.id   = r.categoria_id
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
        $uid = (int)$row['user_id'];
        if (!isset($byUser[$uid])) {
            $byUser[$uid] = [
                'id'         => $uid,
                'username'   => $row['username'],
                'label'      => $row['label'],
                'ocs'        => [],
                'categorias' => [],   /* se rellena después */
            ];
        }
        unset($row['cats'], $row['user_id'], $row['username'], $row['label']);
        $byUser[$uid]['ocs'][] = $row;
    }
    /* Categorías propias de cada usuario (no usadas obligatoriamente por
       sus OCs — algunas pueden estar vacías), para alimentar la sidebar. */
    $catsStmt = $pdo->query('
        SELECT c.user_id, c.id, c.nombre, c.color
        FROM oc_categorias c
        WHERE c.user_id IN (' . (empty($byUser) ? '0' : implode(',', array_map('intval', array_keys($byUser)))) . ')
        ORDER BY c.user_id ASC, c.nombre ASC
    ');
    foreach ($catsStmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $uid = (int)$c['user_id'];
        if (!isset($byUser[$uid])) continue;
        $byUser[$uid]['categorias'][] = [
            'id'     => (int)$c['id'],
            'nombre' => $c['nombre'],
            'color'  => $c['color'],
        ];
    }
    echo json_encode(['ok' => true, 'users' => array_values($byUser)]);
    exit;

/* ── Categorías ── */
case 'categorias_list':
    $stmt = $pdo->prepare('
        SELECT c.id, c.nombre, c.color, COUNT(r.oc_id) AS total
        FROM oc_categorias c
        LEFT JOIN oc_categoria_rel r ON r.categoria_id = c.id
        WHERE c.user_id = ?
        GROUP BY c.id
        ORDER BY c.nombre ASC
    ');
    $stmt->execute([$userId]);
    echo json_encode(['ok' => true, 'categorias' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;

case 'categoria_create':
    requirePost();
    $b      = getBody();
    $nombre = sanitize((string)($b['nombre'] ?? ''), 60);
    $color  = preg_match('/^#[0-9a-fA-F]{6}$/', (string)($b['color'] ?? '')) ? $b['color'] : '#888888';
    if ($nombre === '') jsonError('Nombre requerido');
    $stmt = $pdo->prepare('INSERT INTO oc_categorias (user_id, nombre, color) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $nombre, $color]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId(), 'nombre' => $nombre, 'color' => $color]);
    exit;

case 'categoria_delete':
    requirePost();
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);
    /* Solo se pueden borrar las propias; las globales (user_id=0) están protegidas. */
    $stmt = $pdo->prepare('SELECT id FROM oc_categorias WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) jsonError('No encontrado', 404);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM oc_categoria_rel WHERE categoria_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM oc_categorias    WHERE id          = ?')->execute([$id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        jsonError('Error al borrar: ' . $e->getMessage(), 500);
    }
    echo json_encode(['ok' => true]);
    exit;

/* ── Galería del OC ── */
case 'galeria_add':
    requirePost();
    $b        = getBody();
    $oc_id    = (int)($b['oc_id'] ?? 0);
    $drive_id = sanitize((string)($b['drive_id']    ?? ''), 100);
    $desc     = sanitize((string)($b['descripcion'] ?? ''), 255);
    if ($drive_id === '') jsonError('drive_id requerido');

    $stmt = $pdo->prepare('SELECT id FROM ocs WHERE id = ? AND user_id = ?');
    $stmt->execute([$oc_id, $userId]);
    if (!$stmt->fetch()) jsonError('OC no encontrado', 404);

    /* foto_url es un legacy NOT NULL sin DEFAULT — guardar '' para no romper. */
    $stmt = $pdo->prepare('INSERT INTO oc_galeria (oc_id, drive_id, descripcion, foto_url) VALUES (?, ?, ?, "")');
    $stmt->execute([$oc_id, $drive_id, $desc]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    exit;

case 'galeria_delete':
    requirePost();
    $b  = getBody();
    $id = (int)($b['id'] ?? 0);
    $stmt = $pdo->prepare('
        SELECT g.id FROM oc_galeria g
        JOIN ocs o ON o.id = g.oc_id
        WHERE g.id = ? AND o.user_id = ?
    ');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) jsonError('No encontrado', 404);
    $pdo->prepare('DELETE FROM oc_galeria WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true]);
    exit;

default:
    http_response_code(400);
    echo json_encode(['error' => 'Acción desconocida: ' . htmlspecialchars((string)$action)]);
}
