<?php
/* ──────────────────────────────────────────────────────────────────────
   cover.php?h=<sha1> — sirve la copia local de una foto de lista
   ──────────────────────────────────────────────────────────────────────
   Primero mira la caché en disco (uploads/covers/) para no abrir una
   conexión a BD por cada imagen; si no está (p. ej. tras un despliegue
   que vació uploads/), la saca de cover_cache y la vuelve a escribir.
   Sin sesión: son carátulas públicas. Ver cover-helpers.php.
   ────────────────────────────────────────────────────────────────────── */
$hash = (string)($_GET['h'] ?? '');
if (!preg_match('/^[a-f0-9]{40}$/', $hash)) { http_response_code(400); exit; }

function cover_send(string $mime, string $data, string $hash): void {
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($data));
    /* Una copia no cambia nunca para un mismo hash → caché "para siempre". */
    header('Cache-Control: public, max-age=31536000, immutable');
    header('ETag: "' . $hash . '"');
    header('X-Content-Type-Options: nosniff');
    echo $data;
    exit;
}

if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === '"' . $hash . '"') {
    http_response_code(304);
    exit;
}

$dir = dirname(__DIR__, 2) . '/uploads/covers';
foreach (['webp' => 'image/webp', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'] as $ext => $mime) {
    $file = $dir . '/' . $hash . '.' . $ext;
    if (is_file($file)) cover_send($mime, (string)file_get_contents($file), $hash);
}

require_once dirname(__DIR__, 2) . '/db.php';
require_once __DIR__ . '/cover-helpers.php';
try {
    $st = $pdo->prepare("SELECT mime, data FROM cover_cache WHERE hash = ?");
    $st->execute([$hash]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $row = null;   /* tabla aún sin crear */
}
if (!$row) { http_response_code(404); exit; }
cv_writeDisk($hash, $row['mime'], $row['data']);
cover_send($row['mime'], $row['data'], $hash);
