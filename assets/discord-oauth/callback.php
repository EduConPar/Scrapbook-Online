<?php
/* Callback OAuth2 de Discord. Discord redirige aquí con ?code=...&state=...
   Validamos state, canjeamos code → access_token, leemos /users/@me y
   guardamos el id+username en la columna de `usuarios`. Cerramos la popup
   y notificamos al opener vía postMessage. */
session_start();
require_once dirname(__DIR__, 2) . '/assets/config.php';
require_once dirname(__DIR__, 2) . '/db.php';
require_once __DIR__ . '/helpers.php';

function finish(string $status, string $detail = ''): void {
    /* Devolvemos una mini-página HTML que avisa al opener y se cierra. */
    $safeStatus = htmlspecialchars($status, ENT_QUOTES);
    $safeDetail = htmlspecialchars($detail, ENT_QUOTES);
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
<!DOCTYPE html><html><head><title>Discord</title>
<style>body{font-family:system-ui,sans-serif;background:#0e1116;color:#dcdde0;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;box-sizing:border-box}h2{margin:0 0 6px;font-size:18px}p{margin:0;font-size:13px;color:#9aa0a6}</style></head>
<body><div><h2 id="t">…</h2><p id="d">$safeDetail</p></div>
<script>
var status = "$safeStatus";
document.getElementById('t').textContent = status === 'ok' ? '✓ Discord vinculado' : '✗ Error vinculando Discord';
try { if (window.opener && !window.opener.closed) window.opener.postMessage({type:'discord-linked', status:status, detail:"$safeDetail"}, '*'); } catch(e){}
setTimeout(function(){ try { window.close(); } catch(e){} }, status === 'ok' ? 800 : 2500);
</script>
</body></html>
HTML;
    exit;
}

$userKey       = $_SESSION['user']                  ?? null;
$expectedState = $_SESSION['discord_oauth_state']   ?? null;
$expectedUser  = $_SESSION['discord_oauth_user']    ?? null;
unset($_SESSION['discord_oauth_state'], $_SESSION['discord_oauth_user']);

if (!$userKey || !isset($loginUsers[$userKey]))    finish('error', 'Sesión expirada');
if ($expectedUser && $expectedUser !== $userKey)   finish('error', 'Usuario distinto');
$state = $_GET['state'] ?? '';
$code  = $_GET['code']  ?? '';
if (!$state || !$expectedState || !hash_equals($expectedState, (string)$state)) finish('error', 'CSRF mismatch');
if (!$code) finish('error', 'Sin code');

$accessToken = discordExchangeCode((string)$code);
if (!$accessToken) finish('error', 'No pude canjear el code');

$me = discordGetMe($accessToken);
if (!$me) finish('error', 'No pude leer /users/@me');

try {
    $st = $pdo->prepare('UPDATE usuarios SET discord_user_id = ?, discord_username = ? WHERE user_key = ?');
    $st->execute([$me['id'], $me['username'], $userKey]);
} catch (Throwable $e) {
    finish('error', 'BD: ' . $e->getMessage());
}

finish('ok', '@' . $me['username']);
