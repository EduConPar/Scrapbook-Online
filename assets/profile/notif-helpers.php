<?php
/**
 * Helpers para notificaciones de perfil (seguir, like en post, etc).
 * Cada usuario tiene <user>-profile-notifs.json con un array.
 * Cada notif: { id, type, fromUser, postId?, createdAt, read }
 */

function profileNotifsFile($userKey) {
    return __DIR__ . '/' . $userKey . '-profile-notifs.json';
}

function readProfileNotifs($userKey) {
    $file = profileNotifsFile($userKey);
    if (!file_exists($file)) return [];
    $raw = json_decode(file_get_contents($file), true);
    return is_array($raw) ? $raw : [];
}

function writeProfileNotifs($userKey, $list) {
    /* Tope de 100 más recientes para evitar crecimiento ilimitado */
    if (count($list) > 100) $list = array_slice($list, 0, 100);
    file_put_contents(profileNotifsFile($userKey), json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function addProfileNotif($targetUser, $type, $fromUser, $extra = []) {
    if ($targetUser === $fromUser) return; /* no auto-notifs */
    $list = readProfileNotifs($targetUser);
    $notif = array_merge([
        'id'        => 'notif_' . time() . '_' . rand(100, 9999),
        'type'      => $type,
        'fromUser'  => $fromUser,
        'createdAt' => time(),
        'read'      => false
    ], $extra);
    array_unshift($list, $notif);
    writeProfileNotifs($targetUser, $list);
}

function removeProfileNotifsMatching($targetUser, $callback) {
    $list = readProfileNotifs($targetUser);
    $filtered = array_values(array_filter($list, function($n) use ($callback) {
        return !$callback($n);
    }));
    if (count($filtered) !== count($list)) writeProfileNotifs($targetUser, $filtered);
}
