<?php
/**
 * assets/mascota/api.php
 * Endpoint AJAX para el sistema de mascotas virtuales.
 * Acciones: get, feed, set-skin, save-memoria, get-memoria, heartbeat, reset-death
 */

header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', '0');

require_once __DIR__ . '/../../assets/config.php';
require_once __DIR__ . '/../../db.php';

/* ── Sesión y autenticación ─────────────────────────────────────── */
session_start();
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$userKey = $_SESSION['user'];
if (!isset($loginUsers[$userKey])) {
    http_response_code(403);
    echo json_encode(['error' => 'Usuario inválido']);
    exit;
}

$userId = userIdByKey($userKey);
if (!$userId) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo obtener user_id']);
    exit;
}

/* ── Router ─────────────────────────────────────────────────────── */
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Parsear JSON body si aplica
$body = [];
$raw = file_get_contents('php://input');
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $body = $decoded;
}

switch ($action) {
    case 'get':          actionGet();        break;
    case 'feed':         actionFeed();       break;
    case 'play':         actionPlay();       break;
    case 'set-skin':     actionSetSkin();    break;
    case 'save-memoria': actionSaveMemoria();break;
    case 'get-memoria':  actionGetMemoria(); break;
    case 'heartbeat':    actionHeartbeat();  break;
    case 'reset-death':  actionResetDeath(); break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción desconocida: ' . htmlspecialchars($action)]);
}

/* ════════════════════════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════════════════════ */

/**
 * Crea la mascota con valores por defecto si no existe.
 * Devuelve el array con todos sus campos, con hambre/felicidad
 * recalculados según el tiempo transcurrido desde ultima_vez.
 */
function getMascota(): array {
    global $pdo, $userId;

    $stmt = $pdo->prepare("SELECT * FROM mascotas WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        /* Primera vez: insertar con defaults */
        $pdo->prepare("
            INSERT INTO mascotas (user_id, nombre, skin, hambre, felicidad, edad, viva)
            VALUES (?, 'Meloncio', 'meloncio', 80, 80, 0, 1)
        ")->execute([$userId]);
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ── Calcular decay por tiempo offline ──────────────────────── */
    $now        = time();
    $ultimaVez  = strtotime($row['ultima_vez']);
    $diffSecs   = max(0, $now - $ultimaVez);
    $diffMins   = $diffSecs / 60;

    if ($row['viva'] && $diffMins > 0) {
        /* Hambre: -2 por hora offline (=1/30 por minuto) */
        $hungerDecay    = (int)floor($diffMins / 30);
        /* Felicidad: -1 por hora offline si no hubo interacción */
        $happyDecay     = (int)floor($diffMins / 60);

        $newHambre    = max(0, (int)$row['hambre']    - $hungerDecay);
        $newFelicidad = max(0, (int)$row['felicidad'] - $happyDecay);

        /* ── Edad: +1 día por cada 24 h de sesión acumulada ──────── */
        $daysPassed = (int)floor($diffSecs / 86400);
        $newEdad    = (int)$row['edad'] + $daysPassed;

        /* ── Muerte: si hambre=0 durante más de 24 h ─────────────── */
        $newViva = (int)$row['viva'];
        if ($newHambre === 0 && $diffSecs >= 86400) {
            $newViva = 0;
        }

        if ($newHambre    !== (int)$row['hambre']    ||
            $newFelicidad !== (int)$row['felicidad']  ||
            $newEdad      !== (int)$row['edad']       ||
            $newViva      !== (int)$row['viva']) {

            $pdo->prepare("
                UPDATE mascotas
                SET hambre=?, felicidad=?, edad=?, viva=?, ultima_vez=NOW()
                WHERE user_id=?
            ")->execute([$newHambre, $newFelicidad, $newEdad, $newViva, $userId]);

            $row['hambre']    = $newHambre;
            $row['felicidad'] = $newFelicidad;
            $row['edad']      = $newEdad;
            $row['viva']      = $newViva;
        }
    }

    /* Sanitizar tipos */
    $row['user_id']   = (int)$row['user_id'];
    $row['hambre']    = (int)$row['hambre'];
    $row['felicidad'] = (int)$row['felicidad'];
    $row['edad']      = (int)$row['edad'];
    $row['viva']      = (bool)$row['viva'];

    return $row;
}

/* ════════════════════════════════════════════════════════════════════
   ACCIONES
═══════════════════════════════════════════════════════════════════ */

/** GET — estado completo de la mascota + memoria pendiente */
function actionGet(): void {
    global $pdo, $userId;

    $mascota = getMascota();

    /* ── Preguntas sin responder ─────────────────────────────────── */
    $allKeys = ['comida_favorita','color_favorito','cancion_favorita','juego_favorito','mascota_favorita'];

    $stmt = $pdo->prepare("SELECT clave FROM mascota_memoria WHERE user_id = ?");
    $stmt->execute([$userId]);
    $answered = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'clave');

    $pending = array_values(array_diff($allKeys, $answered));

    /* ── Última app abierta (para comentarios contextuales) ──────── */
    // Se puede ampliar con más contexto en el futuro

    echo json_encode([
        'ok'              => true,
        'mascota'         => $mascota,
        'pending_preguntas' => $pending,
    ]);
}

/** FEED — alimentar a la mascota (+20 hambre, +5 felicidad) */
function actionFeed(): void {
    global $pdo, $userId, $body;

    $mascota = getMascota();
    if (!$mascota['viva']) {
        echo json_encode(['ok' => false, 'error' => 'Tu mascota ha muerto.']);
        return;
    }

    $comida = htmlspecialchars(trim($body['comida'] ?? 'comida'), ENT_QUOTES);

    /* Comidas favoritas dan +5 extra de felicidad */
    $stmt = $pdo->prepare("SELECT valor FROM mascota_memoria WHERE user_id=? AND clave='comida_favorita'");
    $stmt->execute([$userId]);
    $fav = $stmt->fetchColumn();
    $bonus = ($fav && mb_stripos($comida, $fav) !== false) ? 5 : 0;

    $newHambre    = min(100, $mascota['hambre']    + 20);
    $newFelicidad = min(100, $mascota['felicidad'] + 5 + $bonus);

    $pdo->prepare("
        UPDATE mascotas SET hambre=?, felicidad=?, ultima_vez=NOW() WHERE user_id=?
    ")->execute([$newHambre, $newFelicidad, $userId]);

    echo json_encode([
        'ok'        => true,
        'hambre'    => $newHambre,
        'felicidad' => $newFelicidad,
        'bonus_fav' => $bonus > 0,
    ]);
}

/** PLAY — jugar con la mascota (+10 felicidad, -5 hambre por el esfuerzo) */
function actionPlay(): void {
    global $pdo, $userId;

    $mascota = getMascota();
    if (!$mascota['viva']) {
        echo json_encode(['ok' => false, 'error' => 'Tu mascota ha muerto.']);
        return;
    }
    if ($mascota['hambre'] < 15) {
        echo json_encode(['ok' => false, 'error' => 'La mascota tiene demasiada hambre para jugar.']);
        return;
    }

    $newFelicidad = min(100, $mascota['felicidad'] + 10);
    $newHambre    = max(0,   $mascota['hambre']    - 5);

    $pdo->prepare("
        UPDATE mascotas SET felicidad=?, hambre=?, ultima_vez=NOW() WHERE user_id=?
    ")->execute([$newFelicidad, $newHambre, $userId]);

    echo json_encode([
        'ok'        => true,
        'hambre'    => $newHambre,
        'felicidad' => $newFelicidad,
    ]);
}

/** SET-SKIN — cambiar la skin de la mascota */
function actionSetSkin(): void {
    global $pdo, $userId, $body;

    $allowed = ['meloncio', 'helldiver', 'v1'];
    $skin = $body['skin'] ?? '';
    if (!in_array($skin, $allowed, true)) {
        echo json_encode(['ok' => false, 'error' => 'Skin no válida']);
        return;
    }

    $pdo->prepare("UPDATE mascotas SET skin=? WHERE user_id=?")->execute([$skin, $userId]);
    echo json_encode(['ok' => true, 'skin' => $skin]);
}

/** SAVE-MEMORIA — guardar una respuesta personal de la mascota */
function actionSaveMemoria(): void {
    global $pdo, $userId, $body;

    $clave = trim($body['clave'] ?? '');
    $valor = trim($body['valor'] ?? '');

    $allowedClaves = ['comida_favorita','color_favorito','cancion_favorita','juego_favorito','mascota_favorita'];
    if (!in_array($clave, $allowedClaves, true) || $valor === '') {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
        return;
    }

    $valor = mb_substr($valor, 0, 200);

    $pdo->prepare("
        INSERT INTO mascota_memoria (user_id, clave, valor)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE valor=VALUES(valor), guardado_en=NOW()
    ")->execute([$userId, $clave, $valor]);

    echo json_encode(['ok' => true, 'clave' => $clave, 'valor' => $valor]);
}

/** GET-MEMORIA — obtener toda la memoria del usuario */
function actionGetMemoria(): void {
    global $pdo, $userId;

    $stmt = $pdo->prepare("SELECT clave, valor FROM mascota_memoria WHERE user_id=?");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // clave => valor

    echo json_encode(['ok' => true, 'memoria' => $rows]);
}

/**
 * HEARTBEAT — ping cada ~5 min desde el engine para:
 *  1. Bajar hambre (-1 cada 30 min de sesión activa)
 *  2. Actualizar ultima_vez
 *  3. Devolver estado fresco para sincronizar el HUD
 */
function actionHeartbeat(): void {
    global $pdo, $userId;

    $mascota = getMascota();
    if (!$mascota['viva']) {
        echo json_encode(['ok' => true, 'mascota' => $mascota]);
        return;
    }

    /* Sesión activa: -1 hambre cada 30 min = heartbeat cada 5 min → -1/6 por tick.
       Acumulamos con un contador float en PHP usando última_vez como referencia.
       Simplificado: cada heartbeat (5 min) descuenta 1 punto directamente. */
    $newHambre = max(0, $mascota['hambre'] - 1);
    $newViva   = ($newHambre === 0 && $mascota['hambre'] === 0) ? 0 : (int)$mascota['viva'];

    $pdo->prepare("
        UPDATE mascotas SET hambre=?, viva=?, ultima_vez=NOW() WHERE user_id=?
    ")->execute([$newHambre, $newViva, $userId]);

    $mascota['hambre'] = $newHambre;
    $mascota['viva']   = (bool)$newViva;

    echo json_encode(['ok' => true, 'mascota' => $mascota]);
}

/** RESET-DEATH — revivir la mascota (solo si viva=0, con coste) */
function actionResetDeath(): void {
    global $pdo, $userId;

    $mascota = getMascota();
    if ($mascota['viva']) {
        echo json_encode(['ok' => false, 'error' => 'La mascota no está muerta.']);
        return;
    }

    $pdo->prepare("
        UPDATE mascotas
        SET viva=1, hambre=30, felicidad=20, ultima_vez=NOW()
        WHERE user_id=?
    ")->execute([$userId]);

    echo json_encode(['ok' => true, 'mensaje' => 'Mascota revivida con hambre baja.']);
    function actionRename(): void {
    global $pdo, $userId, $body;
 
    $nombre = trim($body['nombre'] ?? '');
    if ($nombre === '') {
        echo json_encode(['ok' => false, 'error' => 'Nombre vacío']);
        return;
    }
    $nombre = mb_substr($nombre, 0, 60);
 
    $pdo->prepare("UPDATE mascotas SET nombre=? WHERE user_id=?")
        ->execute([$nombre, $userId]);
 
    echo json_encode(['ok' => true, 'nombre' => $nombre]);

}