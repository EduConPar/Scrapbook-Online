<?php
/* ─────────────────────────────────────────────────────────────────────
   pdf-proxy.php
   Descarga un PDF externo y lo sirve MISMO-ORIGEN para que PDF.js (o el
   visor nativo) pueda mostrarlo sin problemas de CORS / X-Frame-Options.
   Requiere sesión iniciada. Bloquea IPs privadas (anti-SSRF), limita el
   tamaño y valida que el contenido empiece por "%PDF".
   ───────────────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
requireAuth();   /* corta con 401 si no hay sesión */

$url = (string)($_GET['url'] ?? '');
if (!preg_match('#^https?://#i', $url)) { http_response_code(400); exit('URL inválida'); }

$host = parse_url($url, PHP_URL_HOST);
if (!$host) { http_response_code(400); exit('URL inválida'); }

/* Anti-SSRF: resolvemos el host y rechazamos rangos privados/reservados.
   (No cubre redirecciones a IPs internas, pero limitamos protocolos.) */
$ip = gethostbyname($host);
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    http_response_code(403); exit('Host no permitido');
}

$MAX = 80 * 1024 * 1024;   /* 80 MB tope */
$written = 0; $started = false; $head = '';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION  => true,
    CURLOPT_MAXREDIRS       => 5,
    CURLOPT_CONNECTTIMEOUT  => 12,
    CURLOPT_TIMEOUT         => 60,
    CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    CURLOPT_USERAGENT       => 'Mozilla/5.0 (MelonHub PDF viewer)',
    CURLOPT_HEADER          => false,
    CURLOPT_WRITEFUNCTION   => function ($ch, $chunk) use (&$written, &$started, &$head, $MAX) {
        $len = strlen($chunk);
        if (!$started) {
            $head .= $chunk;
            if (strlen($head) < 5) return $len;            /* esperar más bytes */
            if (strncmp($head, '%PDF', 4) !== 0) return 0;  /* no es PDF → abortar */
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="documento.pdf"');
            header('X-Content-Type-Options: nosniff');
            echo $head; $written = strlen($head); $head = ''; $started = true;
            return $len;
        }
        $written += $len;
        if ($written > $MAX) return 0;                      /* excede tope → abortar */
        echo $chunk;
        return $len;
    },
]);
curl_exec($ch);
$err = curl_errno($ch);
curl_close($ch);

if (!$started) {
    /* No llegó a enviarse ningún PDF válido (no era PDF, error de red, etc.). */
    http_response_code(502);
    exit('No se pudo cargar el PDF (¿enlace directo a un .pdf?).');
}
