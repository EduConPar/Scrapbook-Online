<?php
require_once __DIR__ . '/db.php';

$usuarios = [
    ['username' => 'capi',  'password' => '1234', 'avatar' => 'Capi'],
    ['username' => 'angie', 'password' => 'abcd', 'avatar' => 'Angie'],
];

foreach ($usuarios as $u) {
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios (username, password, avatar) VALUES (?, ?, ?)");
    $stmt->execute([$u['username'], $hash, $u['avatar']]);
}

echo '✅ Usuarios creados correctamente';