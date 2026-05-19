<?php
define('SPOTIFY_CLIENT_ID',     '75674a20b87c464d81d9fc35d8815114');
define('SPOTIFY_CLIENT_SECRET', 'c1d259fd020e49b5a657d224c81f0ff7');

$loginUsers = [
    'user1' => ['label' => 'Capi', 'password' => '1234'],
    'user2' => ['label' => 'Angie', 'password' => 'abcd'],
];

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
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            if (file_exists(__DIR__ . "/img/{$name}-wallpaper.{$ext}")) {
                return "assets/img/{$name}-wallpaper.{$ext}";
            }
        }
    }
    foreach (['png', 'jpg', 'jpeg'] as $ext) {
        if (file_exists(__DIR__ . "/img/base-wallpaper.{$ext}")) {
            return "assets/img/base-wallpaper.{$ext}";
        }
    }
    return '';
}
