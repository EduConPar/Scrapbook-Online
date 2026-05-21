<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { http_response_code(401); exit; }
$userKey = $_SESSION['user'];
session_write_close();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

$inviteFile = __DIR__ . '/' . $userKey . '-item-invites.json';
$lastHash   = '';
$start      = time();

while (true) {
    if (time() - $start > 25) break;
    if (connection_aborted()) break;

    $invites = [];
    if (file_exists($inviteFile)) {
        $raw     = file_get_contents($inviteFile);
        $invites = json_decode($raw, true);
        if (!is_array($invites)) $invites = [];
    }

    $hash = md5(json_encode($invites));
    if ($hash !== $lastHash) {
        $lastHash = $hash;
        echo 'data: ' . json_encode($invites) . "\n\n";
        if (ob_get_level()) ob_flush();
        flush();
    }

    sleep(1);
}

echo "data: []\n\n";
if (ob_get_level()) ob_flush();
flush();
