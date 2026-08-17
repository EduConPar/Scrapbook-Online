<?php
/* SSE de invites + status notifs de items (perfil).
   Lee de item_invites cada segundo y emite cuando cambia. */
session_start();
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__, 2) . '/db.php';

if (!isset($_SESSION['user'])) { http_response_code(401); exit; }
$userKey = $_SESSION['user'];
session_write_close();

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
$stmt->execute([$userKey]);
$uid = (int)$stmt->fetchColumn();
if (!$uid) { http_response_code(401); exit; }

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

$query = $pdo->prepare(
    "SELECT i.id, i.type, i.category, i.item_id AS itemId,
            i.item_title AS itemTitle, i.item_image AS itemImage,
            i.item_music_type AS itemMusicType, i.item_artist AS itemArtist,
            fu.user_key AS fromUser, fu.label AS fromLabel,
            UNIX_TIMESTAMP(i.created_at) AS sentAt
     FROM item_invites i
     JOIN usuarios fu ON i.from_user_id = fu.id
     WHERE i.to_user_id = ?
     ORDER BY i.created_at ASC"
);

$lastHash = '';
$start    = time();
while (true) {
    if (time() - $start > 25) break;
    if (connection_aborted()) break;

    $query->execute([$uid]);
    $invites = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($invites as &$row) {
        $row['id']     = (int)$row['id'];
        $row['itemId'] = $row['itemId'] !== null ? (int)$row['itemId'] : null;
        $row['sentAt'] = (int)$row['sentAt'];
    }
    unset($row);

    $hash = md5(json_encode($invites));
    if ($hash !== $lastHash) {
        $lastHash = $hash;
        echo 'data: ' . json_encode($invites) . "\n\n";
    } else {
        /* Heartbeat: comentario SSE (línea que empieza por ':'), que
           EventSource ignora. Es imprescindible para detectar el cierre
           del cliente: PHP solo se entera de que el socket murió cuando
           INTENTA escribir. Sin esto, un stream cuyo contenido no cambia
           no escribía nunca, connection_aborted() no saltaba jamás y el
           proceso seguía vivo —reteniendo su conexión MySQL— los 25s
           completos aunque la pestaña ya estuviera cerrada.
           Al cambiar de interfaz se recarga todo de golpe, así que esos
           zombis se solapaban con las conexiones de la página nueva y
           agotaban el tope de la cuenta. */
        echo ": ping\n\n";
    }
    if (ob_get_level()) ob_flush();
    flush();
    sleep(1);
}

echo "data: []\n\n";
if (ob_get_level()) ob_flush();
flush();
