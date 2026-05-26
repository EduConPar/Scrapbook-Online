<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/assets/config.php';
@require_once __DIR__ . '/db.php';

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

/* 3) Borrar avatar (cualquier extensión, label exacto + lowercase) */
$safeLabel = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
    @unlink(__DIR__ . '/assets/img/' . $label . '.' . $ext);
    @unlink(__DIR__ . '/assets/img/' . $safeLabel . '.' . $ext);
}

/* 3b) Borrar wallpaper del usuario */
foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
    @unlink(__DIR__ . '/assets/img/wallpapers/' . $labelLower . '-wallpaper.' . $ext);
    @unlink(__DIR__ . '/assets/img/wallpapers/' . strtolower($safeLabel) . '-wallpaper.' . $ext);
}

/* 3b-bis) Borrar icono del botón inicio del usuario */
foreach (['png', 'svg', 'webp', 'jpg', 'jpeg', 'gif'] as $ext) {
    @unlink(__DIR__ . '/assets/img/start-icons/' . $labelLower . '-start-icon.' . $ext);
    @unlink(__DIR__ . '/assets/img/start-icons/' . strtolower($safeLabel) . '-start-icon.' . $ext);
}

/* 3c) Borrar temas: JSON + todos los CSS generados para este usuario.
       Primero leemos el JSON para conocer los nombres EXACTOS de los temas
       y borrar sus CSS por path explícito; después un barrido por regex
       como red de seguridad para huérfanos con la convención
       <Theme>-<Label>.css. */
$themesDir      = __DIR__ . '/assets/themes/';
$userThemesJson = $themesDir . $userKey . '-themes.json';

if (file_exists($userThemesJson)) {
    $themesData = json_decode(file_get_contents($userThemesJson), true);
    if (is_array($themesData) && isset($themesData['themes']) && is_array($themesData['themes'])) {
        foreach (array_keys($themesData['themes']) as $themeName) {
            $safeTheme = preg_replace('/[^A-Za-z0-9_-]/', '', trim((string)$themeName));
            if ($safeTheme === '' || $safeLabel === '') continue;
            @unlink($themesDir . $safeTheme . '-' . $safeLabel . '.css');
        }
    }
    @unlink($userThemesJson);
}

/* Barrido de seguridad por la convención <Theme>-<Label>.css.
   Sólo si $safeLabel no es vacío (si lo fuera, el regex coincidiría
   con cualquier *.css huérfano de otros usuarios). */
if (is_dir($themesDir) && $safeLabel !== '') {
    foreach (scandir($themesDir) as $f) {
        if (substr($f, -4) !== '.css') continue;
        if (preg_match('/-' . preg_quote($safeLabel, '/') . '\.css$/', $f)) {
            @unlink($themesDir . $f);
        }
    }
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
    'assets/couple/'  . $userKey . '-partner-invites.json',
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

    /* Item-invites del otro usuario que vinieran de este (fromUser) */
    $otherItemInvFile = __DIR__ . '/assets/profile/' . $otherKey . '-item-invites.json';
    if (file_exists($otherItemInvFile)) {
        $invs = json_decode(file_get_contents($otherItemInvFile), true);
        if (is_array($invs)) {
            $filtered = array_values(array_filter($invs, function($n) use ($userKey) {
                return !(isset($n['fromUser']) && $n['fromUser'] === $userKey);
            }));
            if (count($filtered) !== count($invs)) {
                @file_put_contents($otherItemInvFile, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /* Music invites */
    $otherMusicInvFile = __DIR__ . '/assets/music/' . $otherKey . '-invites.json';
    if (file_exists($otherMusicInvFile)) {
        $invs = json_decode(file_get_contents($otherMusicInvFile), true);
        if (is_array($invs)) {
            $filtered = array_values(array_filter($invs, function($n) use ($userKey) {
                return !(isset($n['fromUser']) && $n['fromUser'] === $userKey);
            }));
            if (count($filtered) !== count($invs)) {
                @file_put_contents($otherMusicInvFile, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /* Partner-invites de la app de calendario */
    $otherPartnerInvFile = __DIR__ . '/assets/couple/' . $otherKey . '-partner-invites.json';
    if (file_exists($otherPartnerInvFile)) {
        $invs = json_decode(file_get_contents($otherPartnerInvFile), true);
        if (is_array($invs)) {
            $filtered = array_values(array_filter($invs, function($n) use ($userKey) {
                return !(isset($n['fromUser']) && $n['fromUser'] === $userKey);
            }));
            if (count($filtered) !== count($invs)) {
                @file_put_contents($otherPartnerInvFile, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
}

/* 6b) Limpieza en BD del calendario (parejas, momentos, recordatorios, usuarios)
       — junto con las fotos de momentos en /uploads/momentos/. */
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([strtolower($label)]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userRow && isset($userRow['id'])) {
            $uid = (int)$userRow['id'];

            /* Recoger fotos de momentos del usuario para borrarlas del disco */
            $stmt = $pdo->prepare("SELECT foto FROM momentos WHERE usuario_id = ? AND foto IS NOT NULL AND foto <> ''");
            $stmt->execute([$uid]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $foto = $row['foto'];
                if ($foto) @unlink(__DIR__ . '/uploads/momentos/' . $foto);
            }

            /* Borrar momentos y recordatorios del usuario */
            $pdo->prepare("DELETE FROM momentos WHERE usuario_id = ?")->execute([$uid]);
            $pdo->prepare("DELETE FROM recordatorios WHERE usuario_id = ?")->execute([$uid]);

            /* Pareja: además de borrarla, hay que romper la pareja en BD
               (momentos/recordatorios de la pareja que sean del otro miembro
               se conservan pero pareja_id se desvincula). */
            $stmt = $pdo->prepare("SELECT id FROM parejas WHERE usuario1_id = ? OR usuario2_id = ?");
            $stmt->execute([$uid, $uid]);
            $parejas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($parejas as $p) {
                $pid = (int)$p['id'];
                /* Romper referencia pareja_id en momentos/recordatorios del otro miembro */
                $pdo->prepare("UPDATE momentos      SET pareja_id = 0 WHERE pareja_id = ?")->execute([$pid]);
                $pdo->prepare("UPDATE recordatorios SET pareja_id = 0 WHERE pareja_id = ?")->execute([$pid]);
            }
            $pdo->prepare("DELETE FROM parejas WHERE usuario1_id = ? OR usuario2_id = ?")->execute([$uid, $uid]);

            /* Y por último, borrar el usuario de la tabla */
            $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$uid]);
        }
    } catch (Exception $e) {
        /* No bloquea el borrado del resto. Si falla la BD, los archivos ya están limpios. */
    }
}

/* 7) Si la sesión actual era de este usuario, cerrarla */
if (isset($_SESSION['user']) && $_SESSION['user'] === $userKey) {
    session_unset();
    session_destroy();
}

echo json_encode(['ok' => true]);
