<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
set_time_limit(600);
require_once __DIR__ . '/spotify-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$url  = isset($body['url']) ? trim($body['url']) : '';
if (!$url) { echo json_encode(['error' => 'URL requerida']); exit; }

$playlistId = null;
$albumId    = null;

if (preg_match('/spotify\.com\/(?:[a-z][a-z0-9-]*\/)?playlist\/([A-Za-z0-9]+)/', $url, $m))      $playlistId = $m[1];
elseif (preg_match('/spotify:playlist:([A-Za-z0-9]+)/', $url, $m))                                $playlistId = $m[1];
elseif (preg_match('/spotify\.com\/(?:[a-z][a-z0-9-]*\/)?album\/([A-Za-z0-9]+)/', $url, $m))     $albumId    = $m[1];
elseif (preg_match('/spotify:album:([A-Za-z0-9]+)/', $url, $m))                                   $albumId    = $m[1];

if (!$playlistId && !$albumId) {
    echo json_encode(['error' => 'URL de playlist o álbum de Spotify inválida']); exit;
}

/* Resolve hostname via DNS-over-HTTPS to bypass ISP DNS blocks */
function resolveViaDoH($host) {
    $dohServers = [
        'https://cloudflare-dns.com/dns-query?name=' . urlencode($host) . '&type=A',
        'https://dns.google/resolve?name='           . urlencode($host) . '&type=A',
    ];
    foreach ($dohServers as $dohUrl) {
        $ch = curl_init($dohUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => ['Accept: application/dns-json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        if (!$raw) continue;
        $data = json_decode($raw, true);
        if (empty($data['Answer'])) continue;
        foreach ($data['Answer'] as $ans) {
            if (isset($ans['type']) && $ans['type'] == 1 && !empty($ans['data'])) {
                return $ans['data'];
            }
        }
    }
    return null;
}

function curlFetchSpotifydown($apiUrl, $headers) {
    static $resolvedIp = null;
    if ($resolvedIp === null) {
        $resolvedIp = resolveViaDoH('api.spotifydown.com') ?: '';
    }
    $ch = curl_init($apiUrl);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => $headers,
    ];
    if ($resolvedIp) {
        $opts[CURLOPT_RESOLVE] = ['api.spotifydown.com:443:' . $resolvedIp];
    }
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    curl_close($ch);
    return $raw ?: null;
}

$curlHeaders = [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Origin: https://spotifydown.com',
    'Referer: https://spotifydown.com/',
];

$rawTracks = [];

if ($playlistId) {
    $offset = 0;
    do {
        $apiUrl = 'https://api.spotifydown.com/trackList/playlist/' . $playlistId . '?offset=' . $offset;
        $raw  = curlFetchSpotifydown($apiUrl, $curlHeaders);
        if (!$raw) break;
        $data = json_decode($raw, true);
        if (empty($data['success']) || !isset($data['trackList'])) break;
        foreach ($data['trackList'] as $t) {
            if (!empty($t['title'])) $rawTracks[] = $t;
        }
        $offset = isset($data['nextOffset']) ? (int)$data['nextOffset'] : null;
    } while ($offset !== null && count($rawTracks) < 800);
} else {
    $apiUrl = 'https://api.spotifydown.com/trackList/album/' . $albumId;
    $raw = curlFetchSpotifydown($apiUrl, $curlHeaders);
    if ($raw) {
        $data = json_decode($raw, true);
        if (!empty($data['success']) && isset($data['trackList'])) {
            $rawTracks = $data['trackList'];
        }
    }
}

if (empty($rawTracks)) {
    echo json_encode(['error' => 'No se pudieron obtener las canciones de Spotify']); exit;
}

$results = [];
foreach ($rawTracks as $track) {
    $title  = isset($track['title'])  ? $track['title']  : '';
    $artist = isset($track['artist']) ? $track['artist'] : '';
    if (!$title) continue;
    $videoId = searchYouTubeVideoId($title . ' ' . $artist . ' audio');
    if (!$videoId) continue;
    $results[] = ['videoId' => $videoId, 'title' => $title, 'artist' => $artist, 'duration' => 0];
    usleep(150000);
}

echo json_encode(['tracks' => $results]);
