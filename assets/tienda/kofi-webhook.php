<?php
/* ──────────────────────────────────────────────────────────────────────
   KO-FI WEBHOOK RECEIVER
   ──────────────────────────────────────────────────────────────────────
   Ko-fi envía un POST a esta URL cada vez que alguien dona desde su sitio.
   Para configurarlo:
     1) En ko-fi.com → Settings → API → Webhook URL → pegar la URL pública
        de este archivo (https://tu-dominio/scrapbookOnline/assets/tienda/kofi-webhook.php).
     2) En la misma página, copiar el "Verification Token" y pegarlo en
        .env como KOFI_WEBHOOK_TOKEN=...
   Formato del payload: Ko-fi manda `application/x-www-form-urlencoded`
   con un único campo `data` que contiene JSON.  Verificamos que el
   `verification_token` coincida con el nuestro antes de tocar la BD.
   Dedupe por `kofi_transaction_id` (UNIQUE en la tabla).
   ────────────────────────────────────────────────────────────────────── */
header('Content-Type: text/plain; charset=utf-8');

/* ── DEBUG TEMPORAL ────────────────────────────────────────────────────
   Loguea CADA petición que reciba este endpoint en `kofi-debug.log`
   (misma carpeta). Útil cuando Hostinger no expone logs PHP por panel.
   Cuando confirmes que funciona, borra todo este bloque hasta la línea
   `── FIN DEBUG`. */
$DEBUG_LOG = __DIR__ . '/kofi-debug.log';
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log',      $DEBUG_LOG);
error_reporting(E_ALL);

function kofiDbg($msg) {
    global $DEBUG_LOG;
    @file_put_contents($DEBUG_LOG, '[' . date('c') . '] ' . $msg . "\n", FILE_APPEND);
}

kofiDbg('REQUEST ' . ($_SERVER['REQUEST_METHOD'] ?? '?')
    . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
$rawBody = file_get_contents('php://input');
kofiDbg('raw_body_len=' . strlen($rawBody) . ' content_type=' . ($_SERVER['CONTENT_TYPE'] ?? '?'));
kofiDbg('raw_body=' . substr($rawBody, 0, 2000));
kofiDbg('POST keys=' . implode(',', array_keys($_POST)));
if (isset($_POST['data'])) {
    kofiDbg('POST.data=' . substr((string)$_POST['data'], 0, 2000));
}
/* ── FIN DEBUG ────────────────────────────────────────────────────── */

require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kofiDbg('rejected: method not POST');
    http_response_code(405); echo 'POST only'; exit;
}

$raw = $_POST['data'] ?? null;
if (!$raw) {
    kofiDbg('rejected: missing data field');
    http_response_code(400); echo 'missing data'; exit;
}

$payload = json_decode((string)$raw, true);
if (!is_array($payload)) {
    kofiDbg('rejected: json_decode failed, last_error=' . json_last_error_msg());
    http_response_code(400); echo 'bad json'; exit;
}

$token   = env('KOFI_WEBHOOK_TOKEN', '');
$sent    = (string)($payload['verification_token'] ?? '');
kofiDbg('token_check env_len=' . strlen($token) . ' payload_len=' . strlen($sent)
    . ' env_head=' . substr($token, 0, 4) . ' payload_head=' . substr($sent, 0, 4));
if ($token === '' || !hash_equals($token, $sent)) {
    kofiDbg('rejected: verification_token mismatch');
    http_response_code(401);
    echo 'unauthorized';
    error_log('[kofi-webhook] verification_token mismatch');
    exit;
}
kofiDbg('token OK, type=' . ($payload['type'] ?? 'NONE'));

/* Tipos aceptados: donaciones libres, suscripciones mensuales y compras
   de comisiones (Commission). Todos contribuyen a sostener el proyecto,
   así que todos entran en el mismo tablón de donantes. Shop Order
   también pasa por aquí si tuvieras shop items configurados — lo
   dejamos fuera por ahora porque no aplica. */
$type = $payload['type'] ?? '';
$typeMap = [
    'Donation'     => 'donacion',
    'Subscription' => 'suscripcion',
    'Commission'   => 'encargo',
];
if (!isset($typeMap[$type])) {
    kofiDbg('skip: type "' . $type . '" not in map (Donation/Subscription/Commission)');
    http_response_code(204);
    exit;
}
$tipo = $typeMap[$type];

/* Nombre humano de cada tipo de encargo a partir del direct_link_code
   que Ko-fi mete en el payload. La parte que va después de /c/ en la URL
   pública del encargo es esa misma clave. Si en el futuro añades más
   tipos de comisión, sumas su código aquí. */
$COMMISSION_NAMES = [
    '064181251c' => 'Haro personalizado',
    '4de28dd45e' => 'Tema personalizado',
    '16c92f9fdf' => 'Mascota personalizada',
];

$txId = (string)($payload['kofi_transaction_id'] ?? '');
if ($txId === '') { http_response_code(400); echo 'missing tx id'; exit; }

/* Respetar `is_public`: si el donante eligió privado, lo guardamos como
   anónimo sin mensaje. Eso es lo que muestra la propia pestaña pública
   de Ko-fi. */
$isPublic = !empty($payload['is_public']);
$nombre   = $isPublic ? trim((string)($payload['from_name'] ?? 'Anónimo')) : 'Anónimo';
if ($nombre === '') $nombre = 'Anónimo';
$nombre   = mb_substr($nombre, 0, 80);

/* Para donaciones/suscripciones: el campo `message` del checkout es un
   textarea libre = mensaje público del donante. Para encargos: ese mismo
   campo son las Order Notes (descripción privada del pedido para el
   artista), NO un mensaje de cara al público. Por eso en encargos
   ignoramos `message` y mostramos "Encargó: {producto}" derivado del
   shop_item, así la card del tablón es informativa sin filtrar la
   descripción privada. */
if ($type === 'Commission') {
    $code = '';
    if (isset($payload['shop_items'][0]['direct_link_code'])) {
        $code = (string)$payload['shop_items'][0]['direct_link_code'];
    }
    $productName = $COMMISSION_NAMES[$code]
        ?? (isset($payload['shop_items'][0]['variation_name'])
            ? trim((string)$payload['shop_items'][0]['variation_name'])
            : 'algo personalizado');
    $mensaje = 'Encargó: ' . $productName;
    if (mb_strlen($mensaje) > 200) $mensaje = mb_substr($mensaje, 0, 200);
} else {
    $mensaje = $isPublic ? trim((string)($payload['message'] ?? '')) : '';
    $mensaje = $mensaje !== '' ? mb_substr($mensaje, 0, 200) : null;
}

$importe  = (float)($payload['amount'] ?? 0);
if ($importe <= 0) $importe = null;

/* Ko-fi no envía la URL del avatar en el webhook; queda NULL y el frontend
   usa el placeholder 👤. Si en el futuro Ko-fi añade ese campo, basta con
   asignarlo aquí. */
$avatar = null;

try {
    kofiDbg('insert tipo=' . $tipo . ' tx=' . $txId . ' nombre=' . $nombre . ' importe=' . ($importe ?? 'null'));
    $stmt = $pdo->prepare('
        INSERT INTO donaciones (nombre, avatar_url, mensaje, importe, tipo, kofi_transaction_id)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            nombre  = VALUES(nombre),
            mensaje = VALUES(mensaje),
            importe = VALUES(importe),
            tipo    = VALUES(tipo)
    ');
    $stmt->execute([$nombre, $avatar, $mensaje, $importe, $tipo, $txId]);
    kofiDbg('insert OK, rows_affected=' . $stmt->rowCount());
    http_response_code(200);
    echo 'ok';
} catch (Throwable $e) {
    kofiDbg('insert FAIL: ' . $e->getMessage());
    http_response_code(500);
    error_log('[kofi-webhook] ' . $e->getMessage());
    echo 'error';
}
