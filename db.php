<?php
require_once __DIR__ . '/assets/config.php';

/* Reintento corto con backoff. En hosting compartido (LiteSpeed +
   CloudLinux/LVE) el connect puede fallar con
   SQLSTATE[HY000] [2002] Operation not permitted
   cuando la cuenta roza su tope de procesos/conexiones — un EPERM en el
   connect(), no una credencial mala. Es transitorio: reintentar unos
   cientos de ms lo salva sin que el usuario llegue a ver el error.
   Los SSE (notifications-stream) mantienen una conexión viva 25s cada
   uno, así que basta con varias ventanas abiertas para rozar el tope.

   Esperas entre intentos en microsegundos: ~1.85s en el peor caso.
   Con 450ms no bastaba — la ráfaga de un cambio de interfaz (recarga el
   shell y todas las apps a la vez) dura más que eso. */
$esperas     = [100000, 250000, 500000, 1000000];
$pdo         = null;
$dbLastError = null;
for ($intento = 0; $intento <= count($esperas); $intento++) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        break;
    } catch (PDOException $e) {
        $pdo         = null;
        $dbLastError = $e->getMessage();
        if ($intento < count($esperas)) usleep($esperas[$intento]);
    }
}

if (!$pdo) {
    /* El detalle va al log del servidor, NUNCA al navegador: el die()
       anterior escupía host, usuario y ruta del socket a cualquiera
       capaz de provocar el fallo. */
    error_log('[db] conexión fallida tras ' . (count($esperas) + 1) . ' intentos: ' . $dbLastError);

    /* Antes esto era un die() de texto plano CON HTTP 200. Los endpoints
       JSON devolvían entonces un cuerpo imparseable, el JSON.parse del
       cliente petaba y el frontend solo podía enseñar un error genérico
       sin causa. Respondemos en el formato que espera el llamante y con
       un status que refleje de verdad lo que pasa. */
    $mensaje = 'La base de datos no está disponible ahora mismo. Inténtalo de nuevo en unos segundos.';
    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 5');
        $quiereJson = isset($_GET['action']) || isset($_POST['action'])
            || basename($_SERVER['SCRIPT_NAME'] ?? '') === 'api.php'
            || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        if ($quiereJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $mensaje], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $mensaje;
    exit;
}
