<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/assets/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']); exit;
}

$userKey  = isset($_POST['user'])     ? trim($_POST['user'])     : '';
$password = isset($_POST['password']) ? $_POST['password']       : '';

if (!array_key_exists($userKey, $loginUsers)) {
    echo json_encode(['error' => 'Usuario inválido']); exit;
}
/* No permitir borrar los usuarios fijos */
if (in_array($userKey, ['user1', 'user2'], true)) {
    echo json_encode(['error' => 'Este usuario no se puede eliminar']); exit;
}
if ($loginUsers[$userKey]['password'] !== $password) {
    echo json_encode(['error' => 'Contraseña incorrecta']); exit;
}

$label      = $loginUsers[$userKey]['label'];
$labelLower = strtolower($label);

/* 1) Quitar del JSON de extras */
$jsonFile = __DIR__ . '/assets/login-users.json';
if (file_exists($jsonFile)) {
    $extras = json_decode(file_get_contents($jsonFile), true);
    if (is_array($extras) && isset($extras[$userKey])) {
        unset($extras[$userKey]);
        @file_put_contents($jsonFile, json_encode($extras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

/* 2) Borrar stub de escritorio */
@unlink(__DIR__ . '/' . $labelLower . '-desktop.php');

/* 3) Borrar avatar (cualquier extensión) */
foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
    @unlink(__DIR__ . '/assets/img/' . $label . '.' . $ext);
}

/* 4) Borrar ficheros de datos del usuario */
$dataFiles = [
    'assets/profile/' . $userKey . '-profile.json',
    'assets/profile/' . $userKey . '-lists.json',
    'assets/profile/' . $userKey . '-item-invites.json',
    'assets/profile/' . $userKey . '-profile-notifs.json',
    'assets/music/'   . $userKey . '-playlists.json',
    'assets/music/'   . $userKey . '-invites.json',
    'assets/music/'   . $userKey . '-extra.json',
    'assets/music/'   . $userKey . '-custom.json',
];
foreach ($dataFiles as $rel) {
    @unlink(__DIR__ . '/' . $rel);
}

/* 5) Borrar todos los ficheros de chat con este usuario */
$chatDir = __DIR__ . '/assets/profile/';
if (is_dir($chatDir)) {
    foreach (scandir($chatDir) as $f) {
        if (strpos($f, 'chat-') === 0 && strpos($f, '.json') !== false) {
            /* nombre: chat-userA-userB.json */
            $name = substr($f, 5, -5); /* sin "chat-" ni ".json" */
            $parts = explode('-', $name);
            if (in_array($userKey, $parts, true)) {
                @unlink($chatDir . $f);
            }
        }
    }
}

/* 6) Limpiar referencias en otros usuarios (following, likes de posts, notifs) */
foreach ($loginUsers as $otherKey => $other) {
    if ($otherKey === $userKey) continue;

    /* Profile (following + likes en posts) */
    $otherProfileFile = __DIR__ . '/assets/profile/' . $otherKey . '-profile.json';
    if (file_exists($otherProfileFile)) {
        $data = json_decode(file_get_contents($otherProfileFile), true);
        if (is_array($data)) {
            $modified = false;
            if (isset($data['following']) && is_array($data['following'])) {
                $idx = array_search($userKey, $data['following'], true);
                if ($idx !== false) {
                    array_splice($data['following'], $idx, 1);
                    $modified = true;
                }
            }
            if (isset($data['posts']) && is_array($data['posts'])) {
                foreach ($data['posts'] as &$post) {
                    if (isset($post['likes']) && is_array($post['likes'])) {
                        $idx = array_search($userKey, $post['likes'], true);
                        if ($idx !== false) {
                            array_splice($post['likes'], $idx, 1);
                            $modified = true;
                        }
                    }
                }
                unset($post);
            }
            if ($modified) @file_put_contents($otherProfileFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /* Notifs en perfil (fromUser === userKey) */
    $notifFile = __DIR__ . '/assets/profile/' . $otherKey . '-profile-notifs.json';
    if (file_exists($notifFile)) {
        $notifs = json_decode(file_get_contents($notifFile), true);
        if (is_array($notifs)) {
            $filtered = array_values(array_filter($notifs, function($n) use ($userKey) {
                return !(isset($n['fromUser']) && $n['fromUser'] === $userKey);
            }));
            if (count($filtered) !== count($notifs)) {
                @file_put_contents($notifFile, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /* Quitar colaboradores/sharedFrom de las listas del otro usuario */
    $otherListsFile = __DIR__ . '/assets/profile/' . $otherKey . '-lists.json';
    if (file_exists($otherListsFile)) {
        $lists = json_decode(file_get_contents($otherListsFile), true);
        if (is_array($lists)) {
            $modified = false;
            foreach (['movies','books','games','music'] as $cat) {
                if (!isset($lists[$cat]) || !is_array($lists[$cat])) continue;
                foreach ($lists[$cat] as &$item) {
                    if (isset($item['collaborators']) && is_array($item['collaborators'])) {
                        $idx = array_search($userKey, $item['collaborators'], true);
                        if ($idx !== false) {
                            array_splice($item['collaborators'], $idx, 1);
                            $modified = true;
                        }
                    }
                    if (isset($item['sharedFrom']) && $item['sharedFrom'] === $userKey) {
                        unset($item['sharedFrom']);
                        $modified = true;
                    }
                }
                unset($item);
            }
            if ($modified) @file_put_contents($otherListsFile, json_encode($lists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

/* 7) Si la sesión actual era de este usuario, cerrarla */
if (isset($_SESSION['user']) && $_SESSION['user'] === $userKey) {
    session_unset();
    session_destroy();
}

echo json_encode(['ok' => true]);
