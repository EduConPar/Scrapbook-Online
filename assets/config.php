<?php
define('SPOTIFY_CLIENT_ID',     '75674a20b87c464d81d9fc35d8815114');
define('SPOTIFY_CLIENT_SECRET', 'c1d259fd020e49b5a657d224c81f0ff7');

$loginUsers = [
    'user1' => ['label' => 'Capi', 'password' => '1234'],
    'user2' => ['label' => 'Angie', 'password' => 'abcd'],
];

/* Usuarios registrados dinámicamente desde la pantalla de login */
$_extraUsersFile = __DIR__ . '/login-users.json';
if (file_exists($_extraUsersFile)) {
    $_extras = json_decode(file_get_contents($_extraUsersFile), true);
    if (is_array($_extras)) {
        $loginUsers = array_merge($loginUsers, $_extras);
    }
}

function getUserImage($label)
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
        if (file_exists(__DIR__ . "/img/{$safe}.{$ext}")) {
            return "assets/img/{$safe}.{$ext}";
        }
    }
    return '';
}

function getUserWallpaper($label)
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
    foreach ([$safe, strtolower($safe)] as $name) {
        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
            if (file_exists(__DIR__ . "/img/wallpapers/{$name}-wallpaper.{$ext}")) {
                return "assets/img/wallpapers/{$name}-wallpaper.{$ext}";
            }
        }
    }
    foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
        if (file_exists(__DIR__ . "/img/wallpapers/base-wallpaper.{$ext}")) {
            return "assets/img/wallpapers/base-wallpaper.{$ext}";
        }
    }
    return '';
}
