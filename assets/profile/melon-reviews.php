<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'No autorizado']); exit; }
$requester = $_SESSION['user'];
if (!array_key_exists($requester, $loginUsers)) { echo json_encode(['error' => 'Usuario inválido']); exit; }

$period = isset($_GET['period']) ? $_GET['period'] : 'alltime';
$cat    = isset($_GET['cat'])    ? $_GET['cat']    : 'movies';
$type   = isset($_GET['type'])   ? $_GET['type']   : '';

if (!in_array($cat, ['movies', 'books', 'games', 'music'], true)) {
    echo json_encode(['error' => 'Categoría inválida']); exit;
}
if (!in_array($period, ['year', 'recent', 'alltime'], true)) {
    echo json_encode(['error' => 'Período inválido']); exit;
}
if ($type !== '' && !in_array($type, ['album', 'song'], true)) {
    echo json_encode(['error' => 'Tipo inválido']); exit;
}

$yearAgo = time() - 365 * 24 * 60 * 60;

/** Saca el timestamp de la reseña: usa reviewedAt si existe, si no
 * cae al timestamp incrustado en el id (item_TIMESTAMP_X o music_TIMESTAMP). */
function reviewTimestamp($item) {
    if (isset($item['review']['reviewedAt']) && is_numeric($item['review']['reviewedAt'])) {
        return (int)$item['review']['reviewedAt'];
    }
    if (isset($item['id']) && preg_match('/_(\d{10,})/', $item['id'], $m)) {
        return (int)$m[1];
    }
    return 0;
}

$groups = []; /* title_lower => { title, image, totalStars, totalReviews, latestAt, reviews[] } */

foreach ($loginUsers as $userKey => $userData) {
    $file = __DIR__ . '/' . $userKey . '-lists.json';
    if (!file_exists($file)) continue;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data) || !isset($data[$cat]) || !is_array($data[$cat])) continue;

    foreach ($data[$cat] as $item) {
        if (!isset($item['review']) || !is_array($item['review'])) continue;
        if (!isset($item['review']['stars']) || !is_numeric($item['review']['stars'])) continue;
        $stars = (float)$item['review']['stars'];
        if ($stars <= 0) continue;
        /* Para música, filtrar por type (album|song) si se especifica */
        if ($cat === 'music' && $type !== '') {
            if (!isset($item['type']) || $item['type'] !== $type) continue;
        }

        $reviewedAt = reviewTimestamp($item);
        if ($period === 'year' && $reviewedAt < $yearAgo) continue;

        $title = isset($item['title']) ? trim($item['title']) : '';
        if ($title === '') continue;
        $key = mb_strtolower($title);

        if (!isset($groups[$key])) {
            $groups[$key] = [
                'title'           => $title,
                'image'           => isset($item['image']) ? $item['image'] : '',
                'artist'          => isset($item['artist']) ? $item['artist'] : '',
                'mtype'           => isset($item['type']) ? $item['type'] : '',
                'ytId'            => isset($item['ytId']) ? $item['ytId'] : '',
                'spotifyId'       => isset($item['spotifyId']) ? $item['spotifyId'] : '',
                'ytPlaylistId'    => isset($item['ytPlaylistId']) ? $item['ytPlaylistId'] : '',
                'spotifyAlbumId'  => isset($item['spotifyAlbumId']) ? $item['spotifyAlbumId'] : '',
                'totalStars'      => 0.0,
                'totalReviews'    => 0,
                'latestAt'        => 0,
                'reviews'         => []
            ];
        }
        if (empty($groups[$key]['image']) && !empty($item['image'])) {
            $groups[$key]['image'] = $item['image'];
        }
        if (empty($groups[$key]['artist']) && !empty($item['artist'])) {
            $groups[$key]['artist'] = $item['artist'];
        }
        if (empty($groups[$key]['mtype']) && !empty($item['type'])) {
            $groups[$key]['mtype'] = $item['type'];
        }
        foreach (['ytId', 'spotifyId', 'ytPlaylistId', 'spotifyAlbumId'] as $idField) {
            if (empty($groups[$key][$idField]) && !empty($item[$idField])) {
                $groups[$key][$idField] = $item[$idField];
            }
        }
        $groups[$key]['totalStars']   += $stars;
        $groups[$key]['totalReviews']++;
        if ($reviewedAt > $groups[$key]['latestAt']) $groups[$key]['latestAt'] = $reviewedAt;
        $groups[$key]['reviews'][] = [
            'user'       => $userKey,
            'userLabel'  => $userData['label'],
            'userImg'    => function_exists('getUserImage') ? getUserImage($userData['label']) : '',
            'stars'      => $stars,
            'comment'    => isset($item['review']['comment']) ? $item['review']['comment'] : '',
            'reviewedAt' => $reviewedAt
        ];
    }
}

$items = [];
foreach ($groups as $g) {
    /* Ordena reseñas de cada item por más recientes primero */
    usort($g['reviews'], function($a, $b) { return $b['reviewedAt'] - $a['reviewedAt']; });
    $entry = [
        'title'    => $g['title'],
        'image'    => $g['image'],
        'artist'   => $g['artist'],
        'mtype'    => $g['mtype'],
        'avg'      => round($g['totalStars'] / $g['totalReviews'], 2),
        'count'    => $g['totalReviews'],
        'latestAt' => $g['latestAt'],
        'reviews'  => $g['reviews']
    ];
    if (!empty($g['ytId']))           $entry['ytId']           = $g['ytId'];
    if (!empty($g['spotifyId']))      $entry['spotifyId']      = $g['spotifyId'];
    if (!empty($g['ytPlaylistId']))   $entry['ytPlaylistId']   = $g['ytPlaylistId'];
    if (!empty($g['spotifyAlbumId'])) $entry['spotifyAlbumId'] = $g['spotifyAlbumId'];
    if (!empty($g['mtype']))          $entry['type']           = $g['mtype'];
    $items[] = $entry;
}

if ($period === 'recent') {
    usort($items, function($a, $b) { return $b['latestAt'] - $a['latestAt']; });
} else {
    /* year y alltime: por media descendente; desempate por nº de reseñas */
    usort($items, function($a, $b) {
        if ($b['avg'] !== $a['avg']) return ($b['avg'] > $a['avg']) ? 1 : -1;
        return $b['count'] - $a['count'];
    });
}

echo json_encode(['ok' => true, 'items' => $items]);
