<?php
/* ──────────────────────────────────────────────
   WRAPPED API — tracking de reproducciones + stats
   ──────────────────────────────────────────────
   Endpoints (?action=):
     POST log    — registra una reproducción
                   body: { videoId, title, artist, playlistId, durationS }
     GET  stats  — devuelve top songs/artists/playlists del año actual
   ────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__, 2) . '/db.php';
require_once dirname(__DIR__) . '/themes/theme-helpers.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

$userKey = $_SESSION['user'] ?? '';
if (!$userKey) { http_response_code(401); echo json_encode(['error'=>'No autenticado']); exit; }
$userId = userIdByKey($userKey);
if (!$userId) { http_response_code(500); echo json_encode(['error'=>'user_id no resuelto']); exit; }

/* ── Auto-migración (idempotente) ───────────────────────────────── */
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `music_plays` (
            `id`          BIGINT NOT NULL AUTO_INCREMENT,
            `user_id`     INT NOT NULL,
            `video_id`    VARCHAR(11) NOT NULL,
            `title`       VARCHAR(200) NOT NULL,
            `artist`      VARCHAR(200) NOT NULL DEFAULT '',
            `playlist_id` BIGINT NULL,
            `duration_s`  INT NOT NULL DEFAULT 0,
            `played_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_year` (`user_id`, `played_at`),
            INDEX `idx_user_video` (`user_id`, `video_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    /* Tabla de "eventos de álbum" — distinta a music_plays porque
       agrupa por TÍTULO de álbum + artista (no por video_id como las
       canciones sueltas). action_type='play' al darle a reproducir;
       'import' al añadir el álbum a una playlist propia. Cada acción
       cuenta como un "punto" en el ranking de álbumes más escuchados. */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `music_album_actions` (
            `id`               BIGINT NOT NULL AUTO_INCREMENT,
            `user_id`          INT NOT NULL,
            `album_title`      VARCHAR(200) NOT NULL,
            `artist`           VARCHAR(200) NOT NULL DEFAULT '',
            `action_type`      VARCHAR(20) NOT NULL DEFAULT 'play',
            `yt_playlist_id`   VARCHAR(40) NULL,
            `spotify_album_id` VARCHAR(40) NULL,
            `cover_url`        VARCHAR(500) NULL,
            `played_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_year` (`user_id`, `played_at`),
            INDEX `idx_user_album` (`user_id`, `album_title`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Schema: '.$e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'log':       actionLog();      break;
        case 'log-album': actionLogAlbum(); break;
        case 'stats':     actionStats();    break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción desconocida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/** Registra una reproducción. Anti-spam: ignora si la misma canción
 *  se registró en los últimos 30 segundos (suele ser por re-render
 *  del player tras pausa/resume). */
function actionLog(): void {
    global $pdo, $userId;
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $videoId   = mb_substr(trim((string)($body['videoId'] ?? '')), 0, 11);
    $title     = mb_substr(trim((string)($body['title']    ?? '')), 0, 200);
    $artist    = mb_substr(trim((string)($body['artist']   ?? '')), 0, 200);
    $playlistId= isset($body['playlistId']) && $body['playlistId'] !== null
                   ? (int)$body['playlistId']
                   : null;
    $durationS = max(0, (int)($body['durationS'] ?? 0));

    if ($videoId === '' || $title === '') {
        echo json_encode(['ok'=>false, 'error'=>'videoId+title requeridos']);
        return;
    }

    /* Dedupe 30s — evita doble-log al saltar/repausar la misma. */
    $stmt = $pdo->prepare("
        SELECT 1 FROM music_plays
        WHERE user_id=? AND video_id=?
          AND played_at >= (NOW() - INTERVAL 30 SECOND)
        LIMIT 1
    ");
    $stmt->execute([$userId, $videoId]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['ok'=>true, 'dedupe'=>true]);
        return;
    }

    $pdo->prepare("
        INSERT INTO music_plays (user_id, video_id, title, artist, playlist_id, duration_s)
        VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([$userId, $videoId, $title, $artist, $playlistId, $durationS]);

    echo json_encode(['ok'=>true]);
}

/** Log de acción sobre un ÁLBUM (play / import a playlist).
 *  Llamada desde perfil.php cuando el usuario abre un álbum desde su
 *  melon archive. Anti-dedupe 60s (más laxo que songs porque los
 *  álbumes se relisten más espaciados). */
function actionLogAlbum(): void {
    global $pdo, $userId;
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $album  = mb_substr(trim((string)($body['albumTitle'] ?? '')), 0, 200);
    $artist = mb_substr(trim((string)($body['artist']     ?? '')), 0, 200);
    $type   = in_array($body['actionType'] ?? 'play', ['play','import'], true)
                ? $body['actionType'] : 'play';
    $ytPL   = mb_substr(trim((string)($body['ytPlaylistId']  ?? '')), 0, 40) ?: null;
    $spAlb  = mb_substr(trim((string)($body['spotifyAlbumId'] ?? '')), 0, 40) ?: null;
    $cover  = mb_substr(trim((string)($body['coverUrl'] ?? '')), 0, 500) ?: null;

    if ($album === '') {
        echo json_encode(['ok'=>false, 'error'=>'albumTitle requerido']);
        return;
    }

    /* Dedupe: misma combinación user+album+action en 60s ignora. */
    $stmt = $pdo->prepare("
        SELECT 1 FROM music_album_actions
        WHERE user_id=? AND album_title=? AND action_type=?
          AND played_at >= (NOW() - INTERVAL 60 SECOND)
        LIMIT 1
    ");
    $stmt->execute([$userId, $album, $type]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['ok'=>true, 'dedupe'=>true]);
        return;
    }

    $pdo->prepare("
        INSERT INTO music_album_actions
            (user_id, album_title, artist, action_type, yt_playlist_id, spotify_album_id, cover_url)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([$userId, $album, $artist, $type, $ytPL, $spAlb, $cover]);

    echo json_encode(['ok'=>true]);
}

/** Stats para Spotify Wrapped del usuario.
 *  Param `year` opcional (default año actual). Para testeo dev se
 *  pasa `all=1` para incluir todo lo registrado sin filtro de año. */
function actionStats(): void {
    global $pdo, $userId;
    $year = (int)($_GET['year'] ?? date('Y'));
    $all  = !empty($_GET['all']);

    $where  = "user_id = ?";
    $params = [$userId];
    if (!$all) {
        $where  .= " AND YEAR(played_at) = ?";
        $params[] = $year;
    }

    /* Total minutos. Cada play sin duration (caso típico de plays
       de álbum desde perfil, donde el endpoint play-music-item no
       devuelve duration en sus tracks) cuenta como 180s (3 min)
       — duración media estimada de una canción. Plays CON duración
       suman su valor real. */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS plays,
               SUM(IF(duration_s > 0, duration_s, 180)) AS secs
        FROM music_plays WHERE $where
    ");
    $stmt->execute($params);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['plays'=>0,'secs'=>0];
    $totalPlays = (int)$totals['plays'];
    $totalSecs  = (int)$totals['secs'];
    $totalMin = (int)round($totalSecs / 60);

    /* Top canciones (5) — agrupadas por video_id. */
    $stmt = $pdo->prepare("
        SELECT video_id, MAX(title) AS title, MAX(artist) AS artist, COUNT(*) AS plays
        FROM music_plays WHERE $where
        GROUP BY video_id
        ORDER BY plays DESC, MAX(played_at) DESC
        LIMIT 5
    ");
    $stmt->execute($params);
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($songs as &$s) {
        $s['plays']     = (int)$s['plays'];
        $s['cover_url'] = "https://img.youtube.com/vi/{$s['video_id']}/mqdefault.jpg";
    }
    unset($s);

    /* Top artistas (5) — saltamos artist vacío. */
    $stmt = $pdo->prepare("
        SELECT artist, COUNT(*) AS plays
        FROM music_plays WHERE $where AND artist <> ''
        GROUP BY artist
        ORDER BY plays DESC
        LIMIT 5
    ");
    $stmt->execute($params);
    $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($artists as &$a) $a['plays'] = (int)$a['plays'];
    unset($a);

    /* Top playlists (5) — joinea con playlists para el nombre. */
    $stmt = $pdo->prepare("
        SELECT p.playlist_id, pl.name, COUNT(*) AS plays
        FROM music_plays p
        LEFT JOIN playlists pl ON pl.id = p.playlist_id
        WHERE $where AND p.playlist_id IS NOT NULL
        GROUP BY p.playlist_id, pl.name
        ORDER BY plays DESC
        LIMIT 5
    ");
    $stmt->execute($params);
    $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($playlists as &$pl) {
        $pl['plays'] = (int)$pl['plays'];
        $pl['name']  = $pl['name'] ?: 'Playlist sin nombre';
    }
    unset($pl);

    /* Top álbumes — desde music_album_actions (eventos play + import).
       Agrupa por título del álbum (case-insensitive vía LOWER en SQL). */
    $whereAlb = "user_id = ?";
    $paramsAlb = [$userId];
    if (!$all) {
        $whereAlb  .= " AND YEAR(played_at) = ?";
        $paramsAlb[] = $year;
    }
    $stmt = $pdo->prepare("
        SELECT
            MAX(album_title) AS title,
            MAX(artist)      AS artist,
            MAX(cover_url)   AS cover_url,
            COUNT(*)         AS plays,
            SUM(CASE WHEN action_type='import' THEN 1 ELSE 0 END) AS imports
        FROM music_album_actions WHERE $whereAlb
        GROUP BY LOWER(album_title)
        ORDER BY plays DESC, MAX(played_at) DESC
        LIMIT 5
    ");
    $stmt->execute($paramsAlb);
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($albums as &$al) {
        $al['plays']   = (int)$al['plays'];
        $al['imports'] = (int)$al['imports'];
    }
    unset($al);

    echo json_encode([
        'ok'         => true,
        'year'       => $year,
        'all'        => $all,
        'total_plays'=> $totalPlays,
        'total_secs' => $totalSecs,
        'total_min'  => $totalMin,
        'songs'      => $songs,
        'artists'    => $artists,
        'albums'     => $albums,
        'playlists'  => $playlists,
    ]);
}
