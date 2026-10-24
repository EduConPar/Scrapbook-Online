<?php
/* ──────────────────────────────────────────────────────────────────────
   covers-repair.php — repaso de golpe de las fotos de las listas
   ──────────────────────────────────────────────────────────────────────
   Recorre los items de pelis/series/libros/juegos que aún apuntan a una
   URL externa y, para cada foto distinta:
     - si carga      → copia local y los items pasan a usarla
     - si está caída → busca sustituta (cv_findReplacement)
     - si da un error pasajero → la deja para otra pasada
   Se lanza:
     - Navegador, con sesión iniciada: abre esta página y pulsa
       "Empezar". Va por tandas para no pasarse del tiempo máximo de PHP.
     - Terminal: php assets/profile/covers-repair.php
   Se puede repetir sin miedo: lo ya copiado no se vuelve a tocar.
   ────────────────────────────────────────────────────────────────────── */
$isCli = PHP_SAPI === 'cli';
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';
if (!$isCli) requireAuth();
require_once __DIR__ . '/cover-helpers.php';
cv_ensureTables($pdo);

/* Procesa fotos distintas a partir del item $after hasta $limit o hasta
   pasar $deadline. Devuelve [resultados, nuevo cursor, ¿terminado?]. */
function repair_batch(PDO $pdo, int $after, int $limit, float $deadline): array {
    $stmt = $pdo->prepare("
        SELECT MIN(id) AS firstId, category, MIN(title) AS title, image
        FROM list_items
        WHERE category IN ('movies','series','books','games') AND image LIKE 'http%'
        GROUP BY category, LOWER(title), image
        HAVING MIN(id) > ?
        ORDER BY firstId
        LIMIT " . (int)$limit);
    $stmt->execute([$after]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = []; $cursor = $after; $processed = 0;
    foreach ($rows as $r) {
        if (microtime(true) > $deadline) break;
        $cursor = (int)$r['firstId'];
        $processed++;
        $url = (string)$r['image'];
        if (!cv_isRemote($url)) continue;

        $local = cv_cacheRemote($pdo, $url, $status);
        if ($local) {
            $pdo->prepare("UPDATE list_items SET image = ? WHERE category = ? AND image = ?")
                ->execute([$local, $r['category'], $url]);
            $result = 'copiada';
        } elseif ($status === 'dead') {
            $rep = cv_findReplacement($pdo, $r['category'], $r['title'], $url, true);
            if ($rep['url']) {
                $pdo->prepare("UPDATE list_items SET image = ? WHERE category = ? AND LOWER(title) = ? AND image = ?")
                    ->execute([$rep['url'], $r['category'], mb_strtolower(trim($r['title'])), $url]);
                $result = 'caída → sustituida';
            } else {
                $result = 'caída, sin sustituta';
            }
        } else {
            $result = 'error pasajero, se reintentará en otra pasada';
        }
        $out[] = ['category' => $r['category'], 'title' => $r['title'], 'result' => $result];
    }
    $done = $processed === count($rows) && count($rows) < $limit;
    return [$out, $cursor, $done];
}

if ($isCli) {
    $after = 0;
    do {
        [$out, $after, $done] = repair_batch($pdo, $after, 20, microtime(true) + 300);
        foreach ($out as $o) echo "[{$o['category']}] {$o['title']}: {$o['result']}\n";
    } while (!$done);
    echo "Terminado.\n";
    exit;
}

if (isset($_GET['step'])) {
    @set_time_limit(60);
    [$out, $after, $done] = repair_batch($pdo, (int)($_GET['after'] ?? 0), 6, microtime(true) + 20);
    jsonResponse(['ok' => true, 'results' => $out, 'after' => $after, 'done' => $done]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Repaso de fotos</title>
<style>
    body { font: 14px/1.5 system-ui, sans-serif; margin: 24px; max-width: 760px; }
    #log { white-space: pre-wrap; font: 12px/1.6 ui-monospace, monospace; background: #f4f4f4; padding: 12px; min-height: 120px; }
    button { font-size: 14px; padding: 6px 16px; }
</style>
</head>
<body>
<h1>Repaso de fotos de las listas</h1>
<p>Copia en local las fotos de pelis, series, libros y juegos que aún son enlaces externos, y busca otra para las que estén caídas. Se puede repetir cuando quieras.</p>
<p><button id="go">Empezar</button> <span id="state"></span></p>
<div id="log"></div>
<script>
(function(){
    var btn = document.getElementById('go'), state = document.getElementById('state'), log = document.getElementById('log');
    var counts = {};
    function step(after) {
        fetch('covers-repair.php?step=1&after=' + after, { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d || !d.ok) {
                    state.textContent = (d && d.error) || 'Error inesperado.';
                    btn.disabled = false;
                    return;
                }
                (d.results || []).forEach(function(o){
                    counts[o.result] = (counts[o.result] || 0) + 1;
                    log.textContent += '[' + o.category + '] ' + o.title + ': ' + o.result + '\n';
                });
                if (d.done) {
                    state.textContent = 'Terminado. ' + Object.keys(counts).map(function(k){ return k + ': ' + counts[k]; }).join(' · ');
                    btn.disabled = false;
                } else {
                    step(d.after);
                }
            })
            .catch(function(){
                state.textContent = 'Error de red. Vuelve a pulsar "Empezar" para seguir.';
                btn.disabled = false;
            });
    }
    btn.addEventListener('click', function(){
        btn.disabled = true; counts = {}; log.textContent = '';
        state.textContent = 'Trabajando…';
        step(0);
    });
})();
</script>
</body>
</html>
