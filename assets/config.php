<?php
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
    return '';
}
