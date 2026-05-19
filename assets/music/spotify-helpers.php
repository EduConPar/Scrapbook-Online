<?php
require_once dirname(__DIR__) . '/config.php';

function getSpotifyToken() {
    $cacheFile = __DIR__ . '/spotify-token-cache.json';
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cache) && isset($cache['token'], $cache['expires']) && $cache['expires'] > time() + 60) {
            return $cache['token'];
        }
    }
    $creds = base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET);
    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Authorization: Basic {$creds}\r\nContent-Type: application/x-www-form-urlencoded",
        'content'       => 'grant_type=client_credentials',
        'timeout'       => 10,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents('https://accounts.spotify.com/api/token', false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!isset($data['access_token'])) return null;
    file_put_contents($cacheFile, json_encode([
        'token'   => $data['access_token'],
        'expires' => time() + (int)$data['expires_in'],
    ]));
    return $data['access_token'];
}

function getSpotifyWebToken() {
    $cacheFile = __DIR__ . '/spotify-web-token-cache.json';
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cache) && isset($cache['token'], $cache['expires']) && $cache['expires'] > time() + 60) {
            return $cache['token'];
        }
    }
    $ctx = stream_context_create(['http' => [
        'timeout'       => 10,
        'ignore_errors' => true,
        'header'        => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept: application/json',
            'Referer: https://open.spotify.com/',
        ]),
    ]]);
    $raw = @file_get_contents('https://open.spotify.com/get_access_token?reason=transport&productType=web_player', false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!isset($data['accessToken'])) return null;
    $expires = isset($data['accessTokenExpirationTimestampMs'])
        ? (int)($data['accessTokenExpirationTimestampMs'] / 1000)
        : time() + 3600;
    file_put_contents($cacheFile, json_encode(['token' => $data['accessToken'], 'expires' => $expires]));
    return $data['accessToken'];
}

function searchYouTubeVideoId($query) {
    $url = 'https://www.youtube.com/results?search_query=' . urlencode($query);
    $ctx = stream_context_create(['http' => [
        'timeout'       => 10,
        'ignore_errors' => true,
        'header'        => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
        ]),
    ]]);
    $html = @file_get_contents($url, false, $ctx);
    if (!$html) return null;
    if (preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m)) {
        return $m[1][0];
    }
    return null;
}
