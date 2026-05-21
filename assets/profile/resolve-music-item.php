<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/music/spotify-helpers.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
if (!array_key_exists($_SESSION['user'], $loginUsers)) { echo json_encode(['error' => 'Usuario invalido']); exit; }

$url      = isset($_GET['url'])      ? trim($_GET['url'])      : '';
$itemType = isset($_GET['itemType']) ? trim($_GET['itemType']) : 'song';
if (!$url)                                          { echo json_encode(['error' => 'URL requerida']); exit; }
if (!in_array($itemType, ['song', 'album']))        { echo json_encode(['error' => 'Tipo invalido']);  exit; }

function parseYtVideoId($url) {
    if (preg_match('/youtu\.be\/([A-Za-z0-9_-]{11})/', $url, $m)) return $m[1];
    if (preg_match('/[?&]v=([A-Za-z0-9_-]{11})/',     $url, $m)) return $m[1];
    if (preg_match('/\/embed\/([A-Za-z0-9_-]{11})/',   $url, $m)) return $m[1];
    if (preg_match('/\/shorts\/([A-Za-z0-9_-]{11})/',  $url, $m)) return $m[1];
    if (preg_match('/^([A-Za-z0-9_-]{11})$/', trim($url), $m))    return $m[1];
    return null;
}
function parseYtPlaylistId($url) {
    if (preg_match('/[?&]list=([A-Za-z0-9_-]+)/', $url, $m)) return $m[1];
    return null;
}
function parseSpotifyId($url, $type) {
    if (preg_match('/spotify\.com\/(?:[a-z][a-z0-9-]*\/)?' . $type . '\/([A-Za-z0-9]+)/', $url, $m)) return $m[1];
    if (preg_match('/spotify:' . $type . ':([A-Za-z0-9]+)/', $url, $m)) return $m[1];
    return null;
}

$result = null;
$ctx8   = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);

if ($itemType === 'song') {
    $trackId = parseSpotifyId($url, 'track');
    if ($trackId) {
        $token = getSpotifyToken();
        if ($token) {
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true,
                'header' => 'Authorization: Bearer ' . $token]]);
            $raw = @file_get_contents('https://api.spotify.com/v1/tracks/' . $trackId, false, $ctx);
            if ($raw) {
                $track = json_decode($raw, true);
                if (isset($track['name'])) {
                    $imgs  = $track['album']['images'] ?? [];
                    $image = count($imgs) > 1 ? $imgs[1]['url'] : ($imgs[0]['url'] ?? '');
                    $result = [
                        'title'     => $track['name'],
                        'artist'    => $track['artists'][0]['name'] ?? '',
                        'image'     => $image,
                        'spotifyId' => $trackId,
                    ];
                }
            }
        }
    }
    if (!$result) {
        $videoId = parseYtVideoId($url);
        if ($videoId) {
            $raw = @file_get_contents(
                'https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/watch?v=' . $videoId) . '&format=json',
                false, $ctx8);
            if ($raw) {
                $data = json_decode($raw, true);
                if (isset($data['title'])) {
                    $result = [
                        'title'  => $data['title'],
                        'artist' => '',
                        'image'  => 'https://img.youtube.com/vi/' . $videoId . '/mqdefault.jpg',
                        'ytId'   => $videoId,
                    ];
                }
            }
        }
    }
} else {
    // Spotify album
    $albumId = parseSpotifyId($url, 'album');
    if ($albumId) {
        $token = getSpotifyToken();
        if ($token) {
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true,
                'header' => 'Authorization: Bearer ' . $token]]);
            $raw = @file_get_contents('https://api.spotify.com/v1/albums/' . $albumId, false, $ctx);
            if ($raw) {
                $album = json_decode($raw, true);
                if (isset($album['name'])) {
                    $imgs  = $album['images'] ?? [];
                    $image = count($imgs) > 1 ? $imgs[1]['url'] : ($imgs[0]['url'] ?? '');
                    $result = [
                        'title'          => $album['name'],
                        'artist'         => $album['artists'][0]['name'] ?? '',
                        'image'          => $image,
                        'spotifyAlbumId' => $albumId,
                    ];
                }
            }
        }
    }
    // Spotify playlist
    if (!$result) {
        $plId = parseSpotifyId($url, 'playlist');
        if ($plId) {
            $token = getSpotifyToken();
            if ($token) {
                $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true,
                    'header' => 'Authorization: Bearer ' . $token]]);
                $raw = @file_get_contents(
                    'https://api.spotify.com/v1/playlists/' . $plId . '?fields=name,images,owner',
                    false, $ctx);
                if ($raw) {
                    $pl = json_decode($raw, true);
                    if (isset($pl['name'])) {
                        $image = $pl['images'][0]['url'] ?? '';
                        $result = [
                            'title'          => $pl['name'],
                            'artist'         => $pl['owner']['display_name'] ?? '',
                            'image'          => $image,
                            'spotifyAlbumId' => $plId,
                        ];
                    }
                }
            }
        }
    }
    // YouTube playlist
    if (!$result) {
        $plId = parseYtPlaylistId($url);
        if ($plId) {
            $raw = @file_get_contents(
                'https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/playlist?list=' . $plId) . '&format=json',
                false, $ctx8);
            if ($raw) {
                $data = json_decode($raw, true);
                if (isset($data['title'])) {
                    $result = [
                        'title'        => $data['title'],
                        'artist'       => '',
                        'image'        => $data['thumbnail_url'] ?? '',
                        'ytPlaylistId' => $plId,
                    ];
                }
            }
        }
    }
}

if (!$result) { echo json_encode(['error' => 'No se pudo obtener informacion del enlace']); exit; }
echo json_encode($result);
