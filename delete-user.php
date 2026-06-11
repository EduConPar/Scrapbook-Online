<?php
/* Borra un usuario y todos sus datos.
   Tras la migración a SQL, casi todos los datos viven en `scrapbook_melon` con
   FKs ON DELETE CASCADE → un único DELETE FROM usuarios lo limpia casi todo.
   Las únicas tablas legacy SIN cascade son parejas/momentos/recordatorios
   (estructura previa al refactor), que se limpian explícitamente. */
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/assets/config.php';
require_once __DIR__ . '/db.php';

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
if (!password_verify($password, $loginUsers[$userKey]['password'])) {
    echo json_encode(['error' => 'Contraseña incorrecta']); exit;
}

$label      = $loginUsers[$userKey]['label'];
$labelLower = strtolower($label);
$safeLabel  = preg_replace('/[^A-Za-z0-9_-]/', '', $label);

/* Numeración de secciones (1..5) — coherente con el flujo original */
/* 1) Borrar stub de escritorio */
@unlink(__DIR__ . '/desktops/' . $labelLower . '-desktop.php');

/* 2) Borrar avatar, wallpaper e icono de inicio */
foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
    @unlink(__DIR__ . '/assets/img/' . $label . '.' . $ext);
    @unlink(__DIR__ . '/assets/img/' . $safeLabel . '.' . $ext);
    @unlink(__DIR__ . '/assets/img/wallpapers/' . $labelLower . '-wallpaper.' . $ext);
    @unlink(__DIR__ . '/assets/img/wallpapers/' . strtolower($safeLabel) . '-wallpaper.' . $ext);
}
foreach (['png', 'svg', 'webp', 'jpg', 'jpeg', 'gif'] as $ext) {
    @unlink(__DIR__ . '/assets/img/start-icons/' . $labelLower . '-start-icon.' . $ext);
    @unlink(__DIR__ . '/assets/img/start-icons/' . strtolower($safeLabel) . '-start-icon.' . $ext);
}

/* 3) Borrar los CSS generados de temas para este label.
      Sólo si $safeLabel no es vacío (si lo fuera, el regex coincidiría
      con cualquier *.css huérfano de otros usuarios). */
$themesDir = __DIR__ . '/assets/themes/';
if (is_dir($themesDir) && $safeLabel !== '') {
    foreach (scandir($themesDir) as $f) {
        if (substr($f, -4) !== '.css') continue;
        if (preg_match('/-' . preg_quote($safeLabel, '/') . '\.css$/', $f)) {
            @unlink($themesDir . $f);
        }
    }
}

/* 4) Limpieza en BD.
   - Recoge fotos de momentos para borrarlas del disco
   - Borra momentos/recordatorios/parejas (FKs sin CASCADE en estas tablas)
   - DELETE FROM usuarios → CASCADE elimina el resto (profile, posts,
     post_likes, follows, list_items, list_item_collaborators, item_invites,
     chats, messages, themes, desktop_icons, desktop_folders y sus items,
     music_extras, playlists, playlist_invites, playlist_collaborators,
     partner_invites, notifications, user_settings). */
try {
    /* Buscar el uid: primero por user_key (columna nueva), si no existe
       por username (lower(label)) como fallback. */
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ? OR username = ? LIMIT 1");
    $stmt->execute([$userKey, $labelLower]);
    $uid = (int)$stmt->fetchColumn();
    if ($uid) {
        /* Fotos de momentos a borrar del disco */
        $stmt = $pdo->prepare("SELECT foto FROM momentos WHERE usuario_id = ? AND foto IS NOT NULL AND foto <> ''");
        $stmt->execute([$uid]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            @unlink(__DIR__ . '/uploads/momentos/' . $row['foto']);
        }

        /* Calendario: legacy sin CASCADE en parejas/momentos/recordatorios */
        $pdo->prepare("DELETE FROM momentos      WHERE usuario_id = ?")->execute([$uid]);
        $pdo->prepare("DELETE FROM recordatorios WHERE usuario_id = ?")->execute([$uid]);
        /* Antes de borrar la pareja, desvincular momentos/recordatorios del
           OTRO miembro (su pareja_id apuntaba a una pareja que va a desaparecer). */
        $stmt = $pdo->prepare("SELECT id FROM parejas WHERE usuario1_id = ? OR usuario2_id = ?");
        $stmt->execute([$uid, $uid]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $pid) {
            $pdo->prepare("UPDATE momentos      SET pareja_id = 0 WHERE pareja_id = ?")->execute([(int)$pid]);
            $pdo->prepare("UPDATE recordatorios SET pareja_id = 0 WHERE pareja_id = ?")->execute([(int)$pid]);
        }
        $pdo->prepare("DELETE FROM parejas WHERE usuario1_id = ? OR usuario2_id = ?")->execute([$uid, $uid]);

        /* DELETE final → CASCADE limpia todo lo demás */
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$uid]);
    }
} catch (Exception $e) {
    /* No bloquea el borrado: los ficheros del usuario ya están limpios. */
}

/* 5) Si la sesión actual era de este usuario, cerrarla */
if (isset($_SESSION['user']) && $_SESSION['user'] === $userKey) {
    session_unset();
    session_destroy();
}

echo json_encode(['ok' => true]);
