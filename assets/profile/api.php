<?php
/* ───────────────────────────────────────────────────────────
   PROFILE API — router único de la app Perfil (SQL)
   ───────────────────────────────────────────────────────────
   Acciones (vía ?action= o $_POST['action']):

   GET   ?action=get-profile
   GET   ?action=get-lists
   GET   ?action=get-profile-notifs
   GET   ?action=get-followers
   GET   ?action=get-item-collabs&category=...&itemId=...
   GET   ?action=get-unread-chats
   GET   ?action=get-messages&with=USERKEY
   GET   ?action=view-user&user=USERKEY
   GET   ?action=melon-reviews&period=year|recent|alltime&cat=...&type=album|song
   GET   ?action=resolve-music-item&url=...&itemType=song|album

   POST  ?action=add-post              { text }
   POST  ?action=delete-post           { id }
   POST  ?action=save-quote            { quote }
   POST  ?action=save-info             { bio, pronouns, age, country, steam, ... }
   POST  ?action=save-lists            { category, items[] }
   POST  ?action=toggle-follow         { targetUser }
   POST  ?action=toggle-post-like      { targetUser, postId }
   POST  ?action=mark-notifs-read      {}
   POST  ?action=send-message          { to, text }
   POST  ?action=send-item-invite      { toUser, category, itemId }
   POST  ?action=respond-item-invite   { inviteId, action: 'accept'|'reject'|'dismiss' }
   POST  ?action=leave-collab          { action: 'leave'|'remove', category, itemId, collaboratorUser? }
   POST  ?action=notify-review         { category, itemTitle, mtype? }
   POST  ?action=play-music-item       { ... }

   ALMACENAMIENTO: 100% SQL.
     - profile / posts / post_likes / follows / notifications
     - list_items / list_item_collaborators / item_invites
     - chats / messages

   El SSE item-notifications-stream.php lee directamente de item_invites.
   ─────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/db.php';

$u       = requireAuth();
$userKey = $u['key'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

/* ── user_key → usuarios.id (cacheado por petición) ──────── */
function pf_uid(PDO $pdo, string $userKey): ?int {
    static $cache = [];
    if (array_key_exists($userKey, $cache)) return $cache[$userKey];
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE user_key = ?");
    $stmt->execute([$userKey]);
    $id = $stmt->fetchColumn();
    return $cache[$userKey] = $id ? (int)$id : null;
}

/* Plantillas para mantener compat con el contrato JSON original */
function pf_emptyProfile(): array {
    return ['quote'=>'','posts'=>[],'bio'=>'','pronouns'=>'','age'=>'','country'=>'',
            'steam'=>'','discord'=>'','twitter'=>'','instagram'=>'','following'=>[]];
}
function pf_emptyLists(): array { return ['movies'=>[],'series'=>[],'books'=>[],'games'=>[],'music'=>[]]; }

/* Lee la fila de profile o devuelve plantilla vacía. */
function pf_loadProfileRow(PDO $pdo, int $uid): array {
    $stmt = $pdo->prepare("SELECT quote, bio, pronouns, age, country,
                                   steam, discord, twitter, instagram
                           FROM profile WHERE user_id = ?");
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [];
    /* Normaliza a string (los varchar pueden venir NULL) */
    foreach ($row as $k => $v) if ($v === null) $row[$k] = '';
    return $row;
}

/* Carga los posts del usuario con sus likes (lista de user_keys). */
function pf_loadPostsWithLikes(PDO $pdo, int $uid): array {
    $stmt = $pdo->prepare("SELECT id, text, UNIX_TIMESTAMP(created_at) AS createdAt
                           FROM posts WHERE user_id = ? ORDER BY created_at DESC, id DESC");
    $stmt->execute([$uid]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$posts) return [];
    $ids = array_column($posts, 'id');
    $place = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT pl.post_id, u.user_key
                           FROM post_likes pl JOIN usuarios u ON pl.user_id = u.id
                           WHERE pl.post_id IN ($place)");
    $stmt->execute($ids);
    $likesByPost = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $likesByPost[(int)$r['post_id']][] = $r['user_key'];
    }
    foreach ($posts as &$p) {
        $p['id']        = (int)$p['id'];
        $p['createdAt'] = (int)$p['createdAt'];
        $p['likes']     = $likesByPost[(int)$p['id']] ?? [];
    }
    return $posts;
}

/* Carga la lista de user_keys a los que el usuario sigue. */
function pf_loadFollowing(PDO $pdo, int $uid): array {
    $stmt = $pdo->prepare("SELECT u.user_key FROM follows f
                           JOIN usuarios u ON f.followee_id = u.id
                           WHERE f.follower_id = ? ORDER BY f.created_at ASC");
    $stmt->execute([$uid]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/* Devuelve el perfil completo del usuario en el formato JSON antiguo. */
function pf_buildFullProfile(PDO $pdo, int $uid): array {
    $p = array_merge(pf_emptyProfile(), pf_loadProfileRow($pdo, $uid));
    $p['posts']     = pf_loadPostsWithLikes($pdo, $uid);
    $p['following'] = pf_loadFollowing($pdo, $uid);
    /* age viene como int en BD, el frontend lo trataba como string */
    $p['age'] = ($p['age'] === '' || $p['age'] === null) ? '' : (string)$p['age'];
    return $p;
}

/* Devuelve un item de lista (sin id, sin owner) en formato API.
   Los campos opcionales solo se incluyen si tienen valor. */
function pf_rowToItem(array $r, ?string $sharedFromUserKey, array $collabs): array {
    $item = [
        'id'    => (int)$r['id'],
        'title' => $r['title'],
        'image' => $r['image'] ?? '',
    ];
    if ($r['category'] === 'music') {
        $item['type']     = $r['music_type'] ?: 'song';
        $item['artist']   = $r['artist']     ?? '';
        $item['featured'] = (bool)($r['featured'] ?? 0);
        foreach (['yt_id'=>'ytId','spotify_id'=>'spotifyId',
                  'yt_playlist_id'=>'ytPlaylistId','spotify_album_id'=>'spotifyAlbumId'] as $sc => $jc) {
            if (!empty($r[$sc])) $item[$jc] = $r[$sc];
        }
    } else {
        $item['status'] = $r['status'] ?: 'pending';
    }
    if ($r['review_stars'] !== null) {
        $item['review'] = [
            'stars'   => (float)$r['review_stars'],
            'comment' => (string)($r['review_comment'] ?? ''),
        ];
        if (!empty($r['reviewed_at_ts'])) {
            $item['review']['reviewedAt'] = (int)$r['reviewed_at_ts'];
        }
    }
    if (!empty($collabs)) $item['collaborators'] = $collabs;
    if ($sharedFromUserKey)  $item['sharedFrom']    = $sharedFromUserKey;
    return $item;
}

/* Carga TODAS las listas del usuario (propias + las compartidas con él
   por otros). Devuelve la misma estructura que el JSON antiguo. */
function pf_loadAllLists(PDO $pdo, int $uid): array {
    $lists = pf_emptyLists();
    /* Propias */
    $stmt = $pdo->prepare("SELECT i.*, UNIX_TIMESTAMP(i.reviewed_at) AS reviewed_at_ts, u.user_key AS shared_from_key
                           FROM list_items i
                           LEFT JOIN usuarios u ON i.shared_from = u.id
                           WHERE i.owner_id = ?
                           ORDER BY i.id ASC");
    $stmt->execute([$uid]);
    $ownRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Las que comparten conmigo: estoy como colaborador → la entrada
       aparece en mi lista con sharedFrom apuntando al owner. */
    $stmt = $pdo->prepare("SELECT i.*, UNIX_TIMESTAMP(i.reviewed_at) AS reviewed_at_ts,
                                  ou.user_key AS owner_key
                           FROM list_item_collaborators c
                           JOIN list_items i ON c.item_id = i.id
                           JOIN usuarios ou ON i.owner_id = ou.id
                           WHERE c.user_id = ?
                           ORDER BY i.id ASC");
    $stmt->execute([$uid]);
    $sharedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Cargar colaboradores de las propias en bulk */
    $collabsByItem = [];
    if (!empty($ownRows)) {
        $ids = array_column($ownRows, 'id');
        $place = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT c.item_id, u.user_key
                               FROM list_item_collaborators c
                               JOIN usuarios u ON c.user_id = u.id
                               WHERE c.item_id IN ($place)");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $collabsByItem[(int)$r['item_id']][] = $r['user_key'];
        }
    }

    foreach ($ownRows as $r) {
        $cat = $r['category'];
        $lists[$cat][] = pf_rowToItem($r, null, $collabsByItem[(int)$r['id']] ?? []);
    }
    foreach ($sharedRows as $r) {
        $cat = $r['category'];
        $lists[$cat][] = pf_rowToItem($r, $r['owner_key'], []);
    }
    return $lists;
}

/* Inserta una notificación en `notifications`. fromUser puede ser null. */
function pf_addNotif(PDO $pdo, int $toUid, string $type, ?int $fromUid, array $extra = []): void {
    if ($fromUid !== null && $fromUid === $toUid) return; /* no auto-notifs */
    $pdo->prepare("INSERT INTO notifications (user_id, type, from_user_id, payload, is_read)
                   VALUES (?, ?, ?, ?, 0)")
        ->execute([$toUid, $type, $fromUid, json_encode($extra ?: new stdClass(), JSON_UNESCAPED_UNICODE)]);
}

/* Borra notifs que matcheen un tipo + payload parcial. Útil para deshacer
   un follow/like cuando el usuario revierte la acción. */
function pf_deleteNotifsMatching(PDO $pdo, int $toUid, string $type, ?int $fromUid, array $payloadEq = []): void {
    $sql = "SELECT id, payload FROM notifications WHERE user_id = ? AND type = ?";
    $params = [$toUid, $type];
    if ($fromUid !== null) { $sql .= " AND from_user_id = ?"; $params[] = $fromUid; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $toDelete = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if (empty($payloadEq)) { $toDelete[] = $r['id']; continue; }
        $p = json_decode($r['payload'] ?: '{}', true) ?: [];
        $match = true;
        foreach ($payloadEq as $k => $v) {
            if (($p[$k] ?? null) != $v) { $match = false; break; }
        }
        if ($match) $toDelete[] = $r['id'];
    }
    if (!empty($toDelete)) {
        $place = implode(',', array_fill(0, count($toDelete), '?'));
        $pdo->prepare("DELETE FROM notifications WHERE id IN ($place)")->execute($toDelete);
    }
}

/* Inserta un item_invites de tipo "status" (item-accepted, collab-left,
   collab-removed, item-rejected) para que el SSE lo entregue al cliente. */
function pf_pushItemStatus(PDO $pdo, int $toUid, int $fromUid, ?int $itemId, string $type, array $itemMeta): void {
    if ($toUid === $fromUid) return;
    $pdo->prepare("INSERT INTO item_invites
        (to_user_id, from_user_id, type, item_id, category, item_title, item_image, item_music_type, item_artist)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            $toUid, $fromUid, $type, $itemId,
            $itemMeta['category'],
            $itemMeta['title']  ?? '',
            $itemMeta['image']  ?? null,
            $itemMeta['music_type'] ?? null,
            $itemMeta['artist'] ?? null,
        ]);
}

/* Resuelve/crea el chat entre dos usuarios. Canoniza pair_id por orden. */
function pf_chatId(PDO $pdo, int $a, int $b): int {
    if ($a > $b) { $tmp = $a; $a = $b; $b = $tmp; }
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE user_a = ? AND user_b = ?");
    $stmt->execute([$a, $b]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $pdo->prepare("INSERT INTO chats (user_a, user_b) VALUES (?, ?)")->execute([$a, $b]);
    return (int)$pdo->lastInsertId();
}

switch ($action) {

/* ─── Perfil básico ─────────────────────────────────────── */

case 'get-profile': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonResponse(pf_emptyProfile());
    jsonResponse(pf_buildFullProfile($pdo, $uid));
}

case 'add-post': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $text = mb_substr(trim(jsonBody()['text'] ?? ''), 0, 1000);
    if (!$text) jsonError('Texto vacío');
    $pdo->prepare("INSERT INTO posts (user_id, text) VALUES (?, ?)")->execute([$uid, $text]);
    $id = (int)$pdo->lastInsertId();
    jsonResponse(['ok' => true, 'post' => [
        'id'        => $id,
        'text'      => $text,
        'createdAt' => time(),
        'likes'     => [],
    ]]);
}

case 'delete-post': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $id  = (int)(jsonBody()['id'] ?? 0);
    if (!$id) jsonError('ID inválido');
    $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?")->execute([$id, $uid]);
    jsonResponse(['ok' => true]);
}

case 'save-quote': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $quote = mb_substr(trim(jsonBody()['quote'] ?? ''), 0, 500);
    $pdo->prepare("INSERT INTO profile (user_id, quote) VALUES (?, ?)
                   ON DUPLICATE KEY UPDATE quote = VALUES(quote)")
        ->execute([$uid, $quote]);
    jsonResponse(['ok' => true]);
}

case 'save-info': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b = jsonBody();
    if (!$b) jsonError('Datos inválidos');
    $bio       = mb_substr(trim($b['bio']       ?? ''), 0, 200);
    $pronouns  = mb_substr(trim($b['pronouns']  ?? ''), 0, 30);
    $ageStr    = mb_substr(preg_replace('/[^0-9]/', '', $b['age'] ?? ''), 0, 3);
    $age       = $ageStr === '' ? null : (int)$ageStr;
    $country   = mb_substr(trim($b['country']   ?? ''), 0, 50);
    $steam     = mb_substr(trim($b['steam']     ?? ''), 0, 200);
    $discord   = mb_substr(trim($b['discord']   ?? ''), 0, 100);
    $twitter   = mb_substr(trim($b['twitter']   ?? ''), 0, 100);
    $instagram = mb_substr(trim($b['instagram'] ?? ''), 0, 100);
    $pdo->prepare("INSERT INTO profile
        (user_id, bio, pronouns, age, country, steam, discord, twitter, instagram)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            bio=VALUES(bio), pronouns=VALUES(pronouns), age=VALUES(age),
            country=VALUES(country), steam=VALUES(steam), discord=VALUES(discord),
            twitter=VALUES(twitter), instagram=VALUES(instagram)")
        ->execute([$uid, $bio, $pronouns, $age, $country, $steam, $discord, $twitter, $instagram]);
    jsonResponse(['ok' => true]);
}

/* ─── Listas ─────────────────────────────────────────────── */

case 'get-lists': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonResponse(pf_emptyLists());
    jsonResponse(pf_loadAllLists($pdo, $uid));
}

case 'save-lists': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);

    $b        = jsonBody();
    $category = $b['category'] ?? '';
    $items    = $b['items']    ?? null;
    if (!in_array($category, ['movies','series','books','games','music'], true) || !is_array($items)) jsonError('Datos inválidos');
    $isMusic = ($category === 'music');

    /* Cargar el estado actual de la categoría: items propios + ids
       compartidos conmigo (solo para reconocerlos y NO tocarlos). */
    $stmt = $pdo->prepare("SELECT id FROM list_items WHERE owner_id = ? AND category = ?");
    $stmt->execute([$uid, $category]);
    $myOwnIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    $stmt = $pdo->prepare("SELECT i.id, i.owner_id FROM list_item_collaborators c
                           JOIN list_items i ON c.item_id = i.id
                           WHERE c.user_id = ? AND i.category = ?");
    $stmt->execute([$uid, $category]);
    $sharedWithMe = []; /* item_id => owner_id */
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $sharedWithMe[(int)$r['id']] = (int)$r['owner_id'];
    }

    /* Snapshot de los items propios CON collaborators para detectar bajas */
    $oldItems = [];
    if (!empty($myOwnIds)) {
        $place = implode(',', array_fill(0, count($myOwnIds), '?'));
        $stmt = $pdo->prepare("SELECT id, title FROM list_items WHERE id IN ($place)");
        $stmt->execute($myOwnIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $oldItems[(int)$r['id']] = ['title' => $r['title'], 'collabs' => []];
        }
        $stmt = $pdo->prepare("SELECT c.item_id, c.user_id FROM list_item_collaborators c
                               WHERE c.item_id IN ($place)");
        $stmt->execute($myOwnIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $oldItems[(int)$r['item_id']]['collabs'][] = (int)$r['user_id'];
        }
    }

    /* Iterar los items entrantes y separar:
        - update de propios (id numérico que coincide con mi propiedad)
        - update de compartido conmigo (id en sharedWithMe → solo permitido
          editar review/status/featured; tracks de listas se ignoran)
        - nuevos (id no numérico o desconocido → INSERT) */
    $touchedOwnIds   = [];
    $touchedShared   = []; /* item_id => owner_id, para devolver al cliente */
    $insertedIds     = []; /* mapping: client_id (string) → new int id */

    $pdo->beginTransaction();
    try {
        $upsertCols = [
            'title','image','status','music_type','artist','featured',
            'yt_id','spotify_id','yt_playlist_id','spotify_album_id',
            'review_stars','review_comment','reviewed_at',
        ];

        $fnSanitize = function(array $item) use ($isMusic) {
            $clean = [
                'title' => mb_substr(trim($item['title'] ?? ''), 0, 200),
                'image' => mb_substr(trim($item['image'] ?? ''), 0, 2000),
                'status'        => null,
                'music_type'    => null,
                'artist'        => null,
                'featured'      => 0,
                'yt_id'         => null,
                'spotify_id'    => null,
                'yt_playlist_id'=> null,
                'spotify_album_id' => null,
                'review_stars'   => null,
                'review_comment' => null,
                'reviewed_at'    => null,
            ];
            if ($isMusic) {
                $clean['music_type'] = in_array($item['type'] ?? '', ['song','album'], true) ? $item['type'] : 'song';
                $clean['artist']     = mb_substr(trim($item['artist'] ?? ''), 0, 200);
                $clean['featured']   = !empty($item['featured']) ? 1 : 0;
                foreach (['ytId'=>'yt_id','spotifyId'=>'spotify_id','ytPlaylistId'=>'yt_playlist_id','spotifyAlbumId'=>'spotify_album_id'] as $jc => $sc) {
                    if (!empty($item[$jc])) $clean[$sc] = preg_replace('/[^A-Za-z0-9_-]/', '', $item[$jc]);
                }
            } else {
                $clean['status'] = in_array($item['status'] ?? '', ['pending','in-progress','completed'], true)
                                    ? $item['status'] : 'pending';
            }
            if (isset($item['review']) && is_array($item['review'])) {
                $stars = isset($item['review']['stars']) ? round((float)$item['review']['stars'] * 2) / 2 : 0;
                $clean['review_stars']   = max(0, min(5, $stars));
                $clean['review_comment'] = mb_substr(trim($item['review']['comment'] ?? ''), 0, 1000);
                /* reviewed_at: usa el enviado por el cliente si lo trae, o
                   conserva el que ya existía si solo cambió el comentario, o
                   asigna ahora si es la primera reseña. Se decide a nivel
                   de query (UPSERT) → aquí solo marcamos "review presente". */
                $clean['reviewed_at'] = isset($item['review']['reviewedAt']) && is_numeric($item['review']['reviewedAt'])
                    ? (int)$item['review']['reviewedAt']
                    : time();
            }
            return $clean;
        };

        foreach ($items as $item) {
            $title = trim($item['title'] ?? '');
            if ($title === '') continue;
            $rawId = $item['id'] ?? null;
            $isNumeric = is_int($rawId) || (is_string($rawId) && ctype_digit($rawId));
            $clean = $fnSanitize($item);

            if ($isNumeric && in_array((int)$rawId, $myOwnIds, true)) {
                /* UPDATE de item propio */
                $id = (int)$rawId;
                $touchedOwnIds[] = $id;
                $sql = "UPDATE list_items SET
                            title = ?, image = ?, status = ?, music_type = ?, artist = ?,
                            featured = ?, yt_id = ?, spotify_id = ?, yt_playlist_id = ?,
                            spotify_album_id = ?, review_stars = ?, review_comment = ?,
                            reviewed_at = " . ($clean['reviewed_at'] === null ? "NULL" : "FROM_UNIXTIME(?)") . "
                        WHERE id = ? AND owner_id = ?";
                $params = [
                    $clean['title'], $clean['image'], $clean['status'], $clean['music_type'], $clean['artist'],
                    $clean['featured'], $clean['yt_id'], $clean['spotify_id'], $clean['yt_playlist_id'],
                    $clean['spotify_album_id'], $clean['review_stars'], $clean['review_comment'],
                ];
                if ($clean['reviewed_at'] !== null) $params[] = $clean['reviewed_at'];
                $params[] = $id; $params[] = $uid;
                $pdo->prepare($sql)->execute($params);
            } elseif ($isNumeric && isset($sharedWithMe[(int)$rawId])) {
                /* UPDATE de item compartido: el dueño es otro, solo puedo
                   editar mis preferencias visibles (status/featured) y la
                   review compartida del item. */
                $id = (int)$rawId;
                $touchedShared[$id] = $sharedWithMe[$id];
                $sql = "UPDATE list_items SET
                            status = ?, featured = ?, review_stars = ?, review_comment = ?,
                            reviewed_at = " . ($clean['reviewed_at'] === null ? "NULL" : "FROM_UNIXTIME(?)") . "
                        WHERE id = ?";
                $params = [
                    $clean['status'], $clean['featured'],
                    $clean['review_stars'], $clean['review_comment'],
                ];
                if ($clean['reviewed_at'] !== null) $params[] = $clean['reviewed_at'];
                $params[] = $id;
                $pdo->prepare($sql)->execute($params);
            } else {
                /* INSERT (item nuevo) */
                $sql = "INSERT INTO list_items
                        (owner_id, category, title, image, status, music_type, artist, featured,
                         yt_id, spotify_id, yt_playlist_id, spotify_album_id,
                         review_stars, review_comment, reviewed_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " .
                        ($clean['reviewed_at'] === null ? "NULL" : "FROM_UNIXTIME(?)") . ")";
                $params = [
                    $uid, $category, $clean['title'], $clean['image'], $clean['status'],
                    $clean['music_type'], $clean['artist'], $clean['featured'],
                    $clean['yt_id'], $clean['spotify_id'], $clean['yt_playlist_id'], $clean['spotify_album_id'],
                    $clean['review_stars'], $clean['review_comment'],
                ];
                if ($clean['reviewed_at'] !== null) $params[] = $clean['reviewed_at'];
                $pdo->prepare($sql)->execute($params);
                $newId = (int)$pdo->lastInsertId();
                $touchedOwnIds[] = $newId;
                if ($rawId !== null) $insertedIds[(string)$rawId] = $newId;
            }
        }

        /* Detectar items propios eliminados (estaban en oldItems y no en touchedOwnIds).
           Si tenían colaboradores, propagamos notif collab-removed antes del DELETE
           para incluir el item_id en la notif. */
        $deletedOwnIds = array_values(array_diff($myOwnIds, $touchedOwnIds));
        foreach ($deletedOwnIds as $itemId) {
            $meta = $oldItems[$itemId] ?? null;
            if (!$meta) continue;
            foreach ($meta['collabs'] as $cUid) {
                pf_pushItemStatus($pdo, $cUid, $uid, $itemId, 'collab-removed', [
                    'category' => $category,
                    'title'    => $meta['title'],
                ]);
            }
        }
        if (!empty($deletedOwnIds)) {
            $place = implode(',', array_fill(0, count($deletedOwnIds), '?'));
            $pdo->prepare("DELETE FROM list_items WHERE id IN ($place) AND owner_id = ?")
                ->execute(array_merge($deletedOwnIds, [$uid]));
        }

        /* Detectar items compartidos eliminados (estaban en sharedWithMe y no
           en touchedShared). Eso significa "abandonar la colaboración". */
        $leftShared = array_diff(array_keys($sharedWithMe), array_keys($touchedShared));
        foreach ($leftShared as $itemId) {
            $ownerUid = $sharedWithMe[$itemId];
            $stmt = $pdo->prepare("SELECT title FROM list_items WHERE id = ?");
            $stmt->execute([$itemId]);
            $title = $stmt->fetchColumn() ?: '';
            $pdo->prepare("DELETE FROM list_item_collaborators WHERE item_id = ? AND user_id = ?")
                ->execute([$itemId, $uid]);
            pf_pushItemStatus($pdo, $ownerUid, $uid, $itemId, 'collab-left', [
                'category' => $category, 'title' => $title,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }

    /* Devolver el snapshot canónico (incluye IDs nuevos asignados al INSERT) */
    jsonResponse([
        'ok'    => true,
        'items' => pf_loadAllLists($pdo, $uid)[$category] ?? [],
    ]);
}

case 'get-item-collabs': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $category = $_GET['category'] ?? '';
    $itemId   = (int)($_GET['itemId'] ?? 0);
    if (!in_array($category, ['movies','series','books','games','music'], true) || !$itemId) jsonError('Datos inválidos');

    $stmt = $pdo->prepare("SELECT i.owner_id, ou.user_key AS owner_key
                           FROM list_items i JOIN usuarios ou ON i.owner_id = ou.id
                           WHERE i.id = ? AND i.category = ?");
    $stmt->execute([$itemId, $category]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonError('Item no encontrado', 404);

    $stmt = $pdo->prepare("SELECT u.user_key FROM list_item_collaborators c
                           JOIN usuarios u ON c.user_id = u.id
                           WHERE c.item_id = ?");
    $stmt->execute([$itemId]);
    jsonResponse([
        'ownerKey'      => $row['owner_key'],
        'collaborators' => $stmt->fetchAll(PDO::FETCH_COLUMN),
    ]);
}

case 'send-item-invite': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b        = jsonBody();
    $toUser   = preg_replace('/[^A-Za-z0-9_-]/', '', $b['toUser'] ?? '');
    $category = $b['category'] ?? '';
    $itemId   = (int)($b['itemId'] ?? 0);
    if (!$toUser || !$itemId || !in_array($category, ['movies','series','books','games','music'], true)) jsonError('Datos inválidos');
    if (!isset($loginUsers[$toUser])) jsonError('Usuario destino inválido');
    if ($toUser === $userKey)          jsonError('No puedes invitarte a ti mismo');
    $toUid = pf_uid($pdo, $toUser);
    if (!$toUid) jsonError('Usuario destino inválido');

    $stmt = $pdo->prepare("SELECT title, image, music_type, artist
                           FROM list_items
                           WHERE id = ? AND owner_id = ? AND category = ?");
    $stmt->execute([$itemId, $uid, $category]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) jsonError('Elemento no encontrado', 404);

    /* ¿Ya es colaborador? */
    $stmt = $pdo->prepare("SELECT 1 FROM list_item_collaborators WHERE item_id = ? AND user_id = ?");
    $stmt->execute([$itemId, $toUid]);
    if ($stmt->fetch()) jsonError(($loginUsers[$toUser]['label'] ?? $toUser) . ' ya es colaborador');

    /* ¿Ya hay invite pendiente de mí hacia este usuario para este item? */
    $stmt = $pdo->prepare("SELECT 1 FROM item_invites
                           WHERE to_user_id = ? AND from_user_id = ? AND item_id = ? AND type = 'invite'");
    $stmt->execute([$toUid, $uid, $itemId]);
    if ($stmt->fetch()) jsonError('Ya existe una invitación pendiente');

    $pdo->prepare("INSERT INTO item_invites
        (to_user_id, from_user_id, type, item_id, category, item_title, item_image, item_music_type, item_artist)
        VALUES (?, ?, 'invite', ?, ?, ?, ?, ?, ?)")
        ->execute([
            $toUid, $uid, $itemId, $category,
            $item['title'], $item['image'],
            $category === 'music' ? ($item['music_type'] ?: 'song') : null,
            $category === 'music' ? ($item['artist'] ?? '') : null,
        ]);
    jsonResponse(['ok' => true]);
}

case 'respond-item-invite': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b        = jsonBody();
    $inviteId = (int)($b['inviteId'] ?? 0);
    $act      = $b['action']   ?? '';
    if (!$inviteId || !in_array($act, ['accept','reject','dismiss'], true)) jsonError('Datos inválidos');

    $stmt = $pdo->prepare("SELECT id, from_user_id, type, item_id, category, item_title, item_image, item_music_type, item_artist
                           FROM item_invites
                           WHERE id = ? AND to_user_id = ?");
    $stmt->execute([$inviteId, $uid]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inv) jsonError('Invitación no encontrada', 404);

    /* Consumir la fila siempre */
    $pdo->prepare("DELETE FROM item_invites WHERE id = ?")->execute([$inviteId]);

    /* Solo procesar accept/reject si era un invite real (no un status) */
    if ($inv['type'] === 'invite' && $act === 'accept' && $inv['item_id']) {
        /* Validar que el item sigue existiendo */
        $stmt = $pdo->prepare("SELECT 1 FROM list_items WHERE id = ?");
        $stmt->execute([(int)$inv['item_id']]);
        if ($stmt->fetch()) {
            $pdo->prepare("INSERT IGNORE INTO list_item_collaborators (item_id, user_id) VALUES (?, ?)")
                ->execute([(int)$inv['item_id'], $uid]);
            pf_pushItemStatus($pdo, (int)$inv['from_user_id'], $uid, (int)$inv['item_id'], 'item-accepted', [
                'category'   => $inv['category'],
                'title'      => $inv['item_title'],
                'image'      => $inv['item_image'],
                'music_type' => $inv['item_music_type'],
                'artist'     => $inv['item_artist'],
            ]);
        }
    } elseif ($inv['type'] === 'invite' && $act === 'reject') {
        pf_pushItemStatus($pdo, (int)$inv['from_user_id'], $uid, $inv['item_id'] ? (int)$inv['item_id'] : null, 'item-rejected', [
            'category'   => $inv['category'],
            'title'      => $inv['item_title'],
            'image'      => $inv['item_image'],
            'music_type' => $inv['item_music_type'],
            'artist'     => $inv['item_artist'],
        ]);
    }
    /* dismiss: nada extra */
    jsonResponse(['ok' => true]);
}

case 'leave-collab': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b        = jsonBody();
    $act      = $b['action']   ?? '';
    $category = $b['category'] ?? '';
    $itemId   = (int)($b['itemId'] ?? 0);
    if (!in_array($act, ['leave','remove'], true) || !in_array($category, ['movies','series','books','games','music'], true) || !$itemId) {
        jsonError('Datos inválidos');
    }

    if ($act === 'leave') {
        /* Yo soy collab → me salgo y aviso al owner */
        $stmt = $pdo->prepare("SELECT i.id, i.title, i.owner_id
                               FROM list_item_collaborators c
                               JOIN list_items i ON c.item_id = i.id
                               WHERE c.user_id = ? AND i.id = ? AND i.category = ?");
        $stmt->execute([$uid, $itemId, $category]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonError('No eres colaborador de este elemento');

        $pdo->prepare("DELETE FROM list_item_collaborators WHERE item_id = ? AND user_id = ?")
            ->execute([$itemId, $uid]);
        pf_pushItemStatus($pdo, (int)$row['owner_id'], $uid, $itemId, 'collab-left', [
            'category' => $category, 'title' => $row['title'],
        ]);
    } else {
        /* Yo soy owner → quito a un colaborador y le aviso */
        $collab = preg_replace('/[^A-Za-z0-9_-]/', '', $b['collaboratorUser'] ?? '');
        if (!$collab || !isset($loginUsers[$collab])) jsonError('Usuario colaborador inválido');
        $collabUid = pf_uid($pdo, $collab);
        if (!$collabUid) jsonError('Usuario colaborador inválido');

        $stmt = $pdo->prepare("SELECT i.title FROM list_items i
                               JOIN list_item_collaborators c ON c.item_id = i.id
                               WHERE i.id = ? AND i.owner_id = ? AND i.category = ? AND c.user_id = ?");
        $stmt->execute([$itemId, $uid, $category, $collabUid]);
        $title = $stmt->fetchColumn();
        if ($title === false) jsonError('El usuario no es colaborador');

        $pdo->prepare("DELETE FROM list_item_collaborators WHERE item_id = ? AND user_id = ?")
            ->execute([$itemId, $collabUid]);
        pf_pushItemStatus($pdo, $collabUid, $uid, $itemId, 'collab-removed', [
            'category' => $category, 'title' => $title,
        ]);
    }
    jsonResponse(['ok' => true]);
}

/* ─── Seguimiento / likes / notifs ───────────────────────── */

case 'toggle-follow': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $target = preg_replace('/[^a-z0-9_]/i', '', jsonBody()['targetUser'] ?? '');
    if (!$target || !isset($loginUsers[$target])) jsonError('Usuario no encontrado');
    if ($target === $userKey)                     jsonError('No puedes seguirte');
    $tid = pf_uid($pdo, $target);
    if (!$tid) jsonError('Usuario no encontrado');

    $stmt = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followee_id = ?");
    $stmt->execute([$uid, $tid]);
    $alreadyFollowing = (bool)$stmt->fetch();

    if ($alreadyFollowing) {
        $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followee_id = ?")
            ->execute([$uid, $tid]);
        pf_deleteNotifsMatching($pdo, $tid, 'follow', $uid);
        $following = false;
    } else {
        $pdo->prepare("INSERT INTO follows (follower_id, followee_id) VALUES (?, ?)")
            ->execute([$uid, $tid]);
        pf_addNotif($pdo, $tid, 'follow', $uid);
        $following = true;
    }

    jsonResponse([
        'ok' => true, 'following' => $following,
        'list' => pf_loadFollowing($pdo, $uid),
    ]);
}

case 'toggle-post-like': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b      = jsonBody();
    $target = preg_replace('/[^a-z0-9_]/i', '', $b['targetUser'] ?? '');
    $postId = (int)($b['postId'] ?? 0);
    if (!$target || !isset($loginUsers[$target])) jsonError('Usuario no encontrado');
    if (!$postId)                                  jsonError('Post inválido');

    $stmt = $pdo->prepare("SELECT p.id, p.text, p.user_id FROM posts p
                           JOIN usuarios u ON p.user_id = u.id
                           WHERE p.id = ? AND u.user_key = ?");
    $stmt->execute([$postId, $target]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) jsonError('Post no encontrado', 404);

    $stmt = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $uid]);
    $alreadyLiked = (bool)$stmt->fetch();

    if ($alreadyLiked) {
        $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?")
            ->execute([$postId, $uid]);
        pf_deleteNotifsMatching($pdo, (int)$post['user_id'], 'like', $uid, ['postId' => $postId]);
        $liked = false;
    } else {
        $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)")
            ->execute([$postId, $uid]);
        pf_addNotif($pdo, (int)$post['user_id'], 'like', $uid, [
            'postId'   => $postId,
            'postText' => mb_substr($post['text'], 0, 80),
        ]);
        $liked = true;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ?");
    $stmt->execute([$postId]);
    jsonResponse(['ok' => true, 'liked' => $liked, 'count' => (int)$stmt->fetchColumn()]);
}

case 'get-profile-notifs': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonResponse(['ok' => true, 'notifs' => [], 'unread' => 0]);
    $stmt = $pdo->prepare("SELECT n.id, n.type, fu.user_key AS fromUser, n.payload,
                                  n.is_read AS isRead, UNIX_TIMESTAMP(n.created_at) AS createdAt
                           FROM notifications n
                           LEFT JOIN usuarios fu ON n.from_user_id = fu.id
                           WHERE n.user_id = ?
                           ORDER BY n.created_at DESC, n.id DESC
                           LIMIT 100");
    $stmt->execute([$uid]);
    $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $list   = [];
    $unread = 0;
    foreach ($rows as $r) {
        $payload = $r['payload'] ? (json_decode($r['payload'], true) ?: []) : [];
        $entry = array_merge([
            'id'        => (int)$r['id'],
            'type'      => $r['type'],
            'fromUser'  => $r['fromUser'],
            'createdAt' => (int)$r['createdAt'],
            'read'      => (bool)$r['isRead'],
        ], $payload);
        if (!$entry['read']) $unread++;
        $list[] = $entry;
    }
    jsonResponse(['ok' => true, 'notifs' => $list, 'unread' => $unread]);
}

case 'mark-notifs-read': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
        ->execute([$uid]);
    jsonResponse(['ok' => true]);
}

case 'get-followers': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonResponse(['ok' => true, 'followers' => []]);
    $stmt = $pdo->prepare("SELECT u.user_key FROM follows f
                           JOIN usuarios u ON f.follower_id = u.id
                           WHERE f.followee_id = ? ORDER BY f.created_at ASC");
    $stmt->execute([$uid]);
    jsonResponse(['ok' => true, 'followers' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
}

case 'notify-review': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b        = jsonBody();
    $category = preg_replace('/[^a-z]/i', '', $b['category']  ?? '');
    $title    = mb_substr(trim($b['itemTitle'] ?? ''), 0, 120);
    $mtype    = preg_replace('/[^a-z]/i', '', $b['mtype']     ?? '');
    if (!$category || !$title) jsonError('Faltan datos');
    if (!in_array($category, ['movies','series','books','games','music'], true)) jsonError('Categoría inválida');
    if ($mtype !== '' && !in_array($mtype, ['album','song'], true)) $mtype = '';

    /* Notificar a todos mis followers (los que me siguen) */
    $stmt = $pdo->prepare("SELECT follower_id FROM follows WHERE followee_id = ?");
    $stmt->execute([$uid]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $fuid) {
        /* Dedup: borra una notif previa de review del mismo título */
        pf_deleteNotifsMatching($pdo, (int)$fuid, 'review', $uid, [
            'category' => $category, 'itemTitle' => $title,
        ]);
        pf_addNotif($pdo, (int)$fuid, 'review', $uid, [
            'category' => $category, 'itemTitle' => $title, 'mtype' => $mtype,
        ]);
    }
    jsonResponse(['ok' => true]);
}

/* ─── Chat ──────────────────────────────────────────────── */

case 'get-unread-chats': {
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonResponse(['ok' => true, 'counts' => []]);
    /* Por cada chat en el que participo: contar mensajes de la contraparte
       más nuevos que mi last_seen. Devuelve {otherUserKey: unreadCount}. */
    $stmt = $pdo->prepare("
        SELECT
            CASE WHEN c.user_a = :uid THEN bu.user_key ELSE au.user_key END AS other,
            (SELECT COUNT(*) FROM messages m
              WHERE m.chat_id = c.id
                AND m.from_user_id != :uid
                AND m.sent_at > COALESCE(
                    CASE WHEN c.user_a = :uid THEN c.last_seen_a ELSE c.last_seen_b END,
                    '1970-01-01'
                )) AS unread
        FROM chats c
        JOIN usuarios au ON c.user_a = au.id
        JOIN usuarios bu ON c.user_b = bu.id
        WHERE c.user_a = :uid OR c.user_b = :uid");
    $stmt->execute(['uid' => $uid]);
    $counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if ((int)$r['unread'] > 0) $counts[$r['other']] = (int)$r['unread'];
    }
    jsonResponse(['ok' => true, 'counts' => $counts]);
}

case 'get-messages': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $with = preg_replace('/[^a-z0-9_]/i', '', $_GET['with'] ?? '');
    if (!$with || !isset($loginUsers[$with])) jsonError('Usuario inválido');
    if ($with === $userKey)                    jsonError('Mismo usuario');
    $withUid = pf_uid($pdo, $with);
    if (!$withUid) jsonError('Usuario inválido');

    $chatId = pf_chatId($pdo, $uid, $withUid);

    /* Actualizar mi last_seen */
    $col = ($uid < $withUid) ? 'last_seen_a' : 'last_seen_b';
    $pdo->prepare("UPDATE chats SET $col = NOW() WHERE id = ?")->execute([$chatId]);

    $stmt = $pdo->prepare("SELECT m.id, fu.user_key AS `from`, m.text,
                                  UNIX_TIMESTAMP(m.sent_at) AS sentAt
                           FROM messages m
                           JOIN usuarios fu ON m.from_user_id = fu.id
                           WHERE m.chat_id = ?
                           ORDER BY m.sent_at ASC, m.id ASC");
    $stmt->execute([$chatId]);
    $msgs = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $m) {
        $m['id']     = (int)$m['id'];
        $m['sentAt'] = (int)$m['sentAt'];
        $msgs[] = $m;
    }
    jsonResponse(['ok' => true, 'messages' => $msgs]);
}

case 'send-message': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $b    = jsonBody();
    $to   = preg_replace('/[^a-z0-9_]/i', '', $b['to'] ?? '');
    $text = mb_substr(trim($b['text'] ?? ''), 0, 2000);
    if (!$to || !isset($loginUsers[$to])) jsonError('Destinatario inválido');
    if ($to === $userKey)                  jsonError('No puedes hablarte a ti mismo');
    if ($text === '')                      jsonError('Mensaje vacío');
    $toUid = pf_uid($pdo, $to);
    if (!$toUid) jsonError('Destinatario inválido');

    /* Solo se puede chatear si hay seguimiento mutuo */
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows
                           WHERE (follower_id = ? AND followee_id = ?)
                              OR (follower_id = ? AND followee_id = ?)");
    $stmt->execute([$uid, $toUid, $toUid, $uid]);
    if ((int)$stmt->fetchColumn() < 2) jsonError('Necesitas seguirse mutuamente para hablar', 403);

    $chatId = pf_chatId($pdo, $uid, $toUid);
    $pdo->prepare("INSERT INTO messages (chat_id, from_user_id, text) VALUES (?, ?, ?)")
        ->execute([$chatId, $uid, $text]);
    $msgId = (int)$pdo->lastInsertId();
    /* Mi last_seen también se actualiza al mandar */
    $col = ($uid < $toUid) ? 'last_seen_a' : 'last_seen_b';
    $pdo->prepare("UPDATE chats SET $col = NOW() WHERE id = ?")->execute([$chatId]);

    jsonResponse(['ok' => true, 'message' => [
        'id'     => $msgId,
        'from'   => $userKey,
        'text'   => $text,
        'sentAt' => time(),
    ]]);
}

/* ─── Ver perfil ajeno ──────────────────────────────────── */

case 'view-user': {
    global $loginUsers;
    $uid = pf_uid($pdo, $userKey);
    if (!$uid) jsonError('Usuario no encontrado', 500);
    $target = preg_replace('/[^a-z0-9_]/i', '', $_GET['user'] ?? '');
    if (!$target || !isset($loginUsers[$target])) jsonError('Usuario no encontrado', 404);
    $tid = pf_uid($pdo, $target);
    if (!$tid) jsonError('Usuario no encontrado', 404);

    $profile = pf_buildFullProfile($pdo, $tid);
    unset($profile['following']);
    $lists = pf_loadAllLists($pdo, $tid);

    $stmt = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followee_id = ?");
    $stmt->execute([$uid, $tid]);
    $isFollowing = (bool)$stmt->fetch();

    jsonResponse([
        'userKey'     => $target,
        'label'       => $loginUsers[$target]['label'],
        'profile'     => $profile,
        'lists'       => $lists,
        'isFollowing' => $isFollowing,
    ]);
}

/* ─── Reseñas globales (MelonArchive) ───────────────────── */

case 'melon-reviews': {
    global $loginUsers;
    $period = $_GET['period'] ?? 'alltime';
    $cat    = $_GET['cat']    ?? 'movies';
    $type   = $_GET['type']   ?? '';
    if (!in_array($cat, ['movies','series','books','games','music'], true)) jsonError('Categoría inválida');
    if (!in_array($period, ['year','recent','alltime'], true))     jsonError('Período inválido');
    if ($type !== '' && !in_array($type, ['album','song'], true))  jsonError('Tipo inválido');
    $yearAgo = time() - 365 * 24 * 60 * 60;

    $sql = "SELECT i.title, i.image, i.artist, i.music_type AS mtype, i.yt_id AS ytId,
                   i.spotify_id AS spotifyId, i.yt_playlist_id AS ytPlaylistId,
                   i.spotify_album_id AS spotifyAlbumId,
                   i.review_stars AS stars, i.review_comment AS comment,
                   UNIX_TIMESTAMP(COALESCE(i.reviewed_at, i.created_at)) AS reviewedAt,
                   u.user_key AS userKey, u.label AS userLabel
            FROM list_items i
            JOIN usuarios u ON i.owner_id = u.id
            WHERE i.category = ? AND i.review_stars IS NOT NULL AND i.review_stars > 0";
    $params = [$cat];
    if ($cat === 'music' && $type !== '') { $sql .= " AND i.music_type = ?"; $params[] = $type; }
    if ($period === 'year') { $sql .= " AND COALESCE(i.reviewed_at, i.created_at) >= FROM_UNIXTIME(?)"; $params[] = $yearAgo; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groups = [];
    foreach ($rows as $r) {
        $title = trim((string)$r['title']);
        if ($title === '') continue;
        $stars = (float)$r['stars'];
        $rAt   = (int)$r['reviewedAt'];
        $key   = mb_strtolower($title);
        if (!isset($groups[$key])) {
            $groups[$key] = [
                'title'          => $title,
                'image'          => $r['image']  ?? '',
                'artist'         => $r['artist'] ?? '',
                'mtype'          => $r['mtype']  ?? '',
                'ytId'           => $r['ytId']           ?? '',
                'spotifyId'      => $r['spotifyId']      ?? '',
                'ytPlaylistId'   => $r['ytPlaylistId']   ?? '',
                'spotifyAlbumId' => $r['spotifyAlbumId'] ?? '',
                'totalStars'     => 0.0, 'totalReviews' => 0, 'latestAt' => 0, 'reviews' => [],
            ];
        }
        foreach (['image','artist','mtype','ytId','spotifyId','ytPlaylistId','spotifyAlbumId'] as $f) {
            if (empty($groups[$key][$f]) && !empty($r[$f])) $groups[$key][$f] = $r[$f];
        }
        $groups[$key]['totalStars'] += $stars;
        $groups[$key]['totalReviews']++;
        if ($rAt > $groups[$key]['latestAt']) $groups[$key]['latestAt'] = $rAt;
        $userLabel = $r['userLabel'];
        $groups[$key]['reviews'][] = [
            'user'       => $r['userKey'],
            'userLabel'  => $userLabel,
            'userImg'    => function_exists('getUserImage') ? getUserImage($userLabel) : '',
            'stars'      => $stars,
            'comment'    => (string)($r['comment'] ?? ''),
            'reviewedAt' => $rAt,
        ];
    }

    $items = [];
    foreach ($groups as $g) {
        usort($g['reviews'], fn($a, $b) => $b['reviewedAt'] - $a['reviewedAt']);
        $e = [
            'title' => $g['title'], 'image' => $g['image'], 'artist' => $g['artist'], 'mtype' => $g['mtype'],
            'avg'   => round($g['totalStars'] / $g['totalReviews'], 2),
            'count' => $g['totalReviews'], 'latestAt' => $g['latestAt'], 'reviews' => $g['reviews'],
        ];
        foreach (['ytId','spotifyId','ytPlaylistId','spotifyAlbumId'] as $f) if (!empty($g[$f])) $e[$f] = $g[$f];
        if (!empty($g['mtype'])) $e['type'] = $g['mtype'];
        $items[] = $e;
    }
    if ($period === 'recent') {
        usort($items, fn($a, $b) => $b['latestAt'] - $a['latestAt']);
    } else {
        usort($items, function($a, $b) {
            if ($b['avg'] !== $a['avg']) return ($b['avg'] > $a['avg']) ? 1 : -1;
            return $b['count'] - $a['count'];
        });
    }
    jsonResponse(['ok' => true, 'items' => $items]);
}

/* ─── Música: resolución / playback (sin almacenamiento) ── */

case 'resolve-music-item': {
    require_once dirname(__DIR__) . '/music/spotify-helpers.php';
    $url      = trim($_GET['url']      ?? '');
    $itemType = trim($_GET['itemType'] ?? 'song');
    if (!$url)                                  jsonError('URL requerida');
    if (!in_array($itemType, ['song','album'], true)) jsonError('Tipo invalido');

    $parseYtV  = fn($u) => (preg_match('/youtu\.be\/([A-Za-z0-9_-]{11})/', $u, $m) || preg_match('/[?&]v=([A-Za-z0-9_-]{11})/', $u, $m) || preg_match('/\/embed\/([A-Za-z0-9_-]{11})/', $u, $m) || preg_match('/\/shorts\/([A-Za-z0-9_-]{11})/', $u, $m) || preg_match('/^([A-Za-z0-9_-]{11})$/', trim($u), $m)) ? $m[1] : null;
    $parseYtP  = fn($u) => preg_match('/[?&]list=([A-Za-z0-9_-]+)/', $u, $m) ? $m[1] : null;
    $parseSpot = fn($u, $type) => (preg_match('/spotify\.com\/(?:[a-z][a-z0-9-]*\/)?' . $type . '\/([A-Za-z0-9]+)/', $u, $m) || preg_match('/spotify:' . $type . ':([A-Za-z0-9]+)/', $u, $m)) ? $m[1] : null;
    $ctx8 = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);

    $result = null;
    if ($itemType === 'song') {
        $trackId = $parseSpot($url, 'track');
        if ($trackId && ($token = getSpotifyToken())) {
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true, 'header' => 'Authorization: Bearer ' . $token]]);
            $raw = @file_get_contents('https://api.spotify.com/v1/tracks/' . $trackId, false, $ctx);
            if ($raw) {
                $t = json_decode($raw, true);
                if (isset($t['name'])) {
                    $imgs = $t['album']['images'] ?? [];
                    $result = ['title' => $t['name'], 'artist' => $t['artists'][0]['name'] ?? '',
                        'image' => count($imgs) > 1 ? $imgs[1]['url'] : ($imgs[0]['url'] ?? ''), 'spotifyId' => $trackId];
                }
            }
        }
        if (!$result && ($vid = $parseYtV($url))) {
            $raw = @file_get_contents('https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/watch?v=' . $vid) . '&format=json', false, $ctx8);
            if ($raw) {
                $d = json_decode($raw, true);
                if (isset($d['title'])) $result = ['title' => $d['title'], 'artist' => '', 'image' => 'https://img.youtube.com/vi/' . $vid . '/mqdefault.jpg', 'ytId' => $vid];
            }
        }
    } else {
        if (($albumId = $parseSpot($url, 'album')) && ($token = getSpotifyToken())) {
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true, 'header' => 'Authorization: Bearer ' . $token]]);
            $raw = @file_get_contents('https://api.spotify.com/v1/albums/' . $albumId, false, $ctx);
            if ($raw) {
                $a = json_decode($raw, true);
                if (isset($a['name'])) {
                    $imgs = $a['images'] ?? [];
                    $result = ['title' => $a['name'], 'artist' => $a['artists'][0]['name'] ?? '',
                        'image' => count($imgs) > 1 ? $imgs[1]['url'] : ($imgs[0]['url'] ?? ''), 'spotifyAlbumId' => $albumId];
                }
            }
        }
        if (!$result && ($plId = $parseSpot($url, 'playlist')) && ($token = getSpotifyToken())) {
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true, 'header' => 'Authorization: Bearer ' . $token]]);
            $raw = @file_get_contents('https://api.spotify.com/v1/playlists/' . $plId . '?fields=name,images,owner', false, $ctx);
            if ($raw) {
                $p = json_decode($raw, true);
                if (isset($p['name'])) $result = ['title' => $p['name'], 'artist' => $p['owner']['display_name'] ?? '', 'image' => $p['images'][0]['url'] ?? '', 'spotifyAlbumId' => $plId];
            }
        }
        if (!$result && ($plId = $parseYtP($url))) {
            $raw = @file_get_contents('https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/playlist?list=' . $plId) . '&format=json', false, $ctx8);
            if ($raw) {
                $d = json_decode($raw, true);
                if (isset($d['title'])) $result = ['title' => $d['title'], 'artist' => '', 'image' => $d['thumbnail_url'] ?? '', 'ytPlaylistId' => $plId];
            }
        }
    }
    if (!$result) jsonError('No se pudo obtener informacion del enlace', 404);
    jsonResponse($result);
}

case 'play-music-item': {
    set_time_limit(60);
    require_once dirname(__DIR__) . '/music/spotify-helpers.php';
    $b = jsonBody();
    if (!$b) jsonError('Datos inválidos');
    $itemType       = $b['itemType']       ?? '';
    $title          = mb_substr(trim($b['title']  ?? ''), 0, 200);
    $artist         = mb_substr(trim($b['artist'] ?? ''), 0, 200);
    $ytId           = preg_replace('/[^A-Za-z0-9_-]/', '', $b['ytId']           ?? '');
    $ytPlaylistId   = preg_replace('/[^A-Za-z0-9_-]/', '', $b['ytPlaylistId']   ?? '');
    $spotifyAlbumId = preg_replace('/[^A-Za-z0-9]/',   '', $b['spotifyAlbumId'] ?? '');
    $spotifyId      = preg_replace('/[^A-Za-z0-9]/',   '', $b['spotifyId']      ?? '');
    if (!in_array($itemType, ['song','album'], true)) jsonError('Tipo inválido');

    $searchYTParallel = function(array $queries): array {
        if (empty($queries)) return [];
        $headers = ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36', 'Accept-Language: en-US,en;q=0.9'];
        $mh = curl_multi_init(); $handles = [];
        foreach ($queries as $i => $q) {
            $ch = curl_init('https://www.youtube.com/results?search_query=' . urlencode($q));
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FOLLOWLOCATION => true]);
            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }
        $active = null;
        do { $status = curl_multi_exec($mh, $active); if ($active) curl_multi_select($mh, 1.0); } while ($active && $status == CURLM_OK);
        $results = [];
        foreach ($handles as $i => $ch) {
            $html = curl_multi_getcontent($ch);
            $vid = ($html && preg_match('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m)) ? $m[1] : null;
            $results[$i] = $vid;
            curl_multi_remove_handle($mh, $ch); curl_close($ch);
        }
        curl_multi_close($mh);
        return $results;
    };

    if ($itemType === 'song') {
        if ($ytId) jsonResponse(['tracks' => [['videoId' => $ytId, 'title' => $title, 'artist' => $artist]]]);
        $vids = $searchYTParallel([trim($title . ' ' . $artist . ' audio')]);
        if (empty($vids[0])) jsonError('No se encontró el vídeo en YouTube', 502);
        jsonResponse(['tracks' => [['videoId' => $vids[0], 'title' => $title, 'artist' => $artist]]]);
    }

    /* Album */
    if ($ytPlaylistId) {
        $headers = ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36', 'Accept-Language: en-US,en;q=0.9'];
        $ch = curl_init('https://www.youtube.com/playlist?list=' . $ytPlaylistId);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FOLLOWLOCATION => true]);
        $html = curl_exec($ch); curl_close($ch);
        if (!$html) jsonError('No se pudo cargar la playlist de YouTube', 502);
        $pairs = [];
        preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"[^}]{0,400}?"title"\s*:\s*\{\s*"runs"\s*:\s*\[\s*\{\s*"text"\s*:\s*"([^"]+)"/', $html, $pairs);
        $videoIds = $pairs[1] ?? [];
        $titles   = $pairs[2] ?? [];
        $seen = []; $tracks = [];
        foreach ($videoIds as $i => $vid) {
            if (isset($seen[$vid])) continue;
            $seen[$vid] = true;
            $tracks[] = ['videoId' => $vid, 'title' => ($titles[$i] ?? '') ?: $title, 'artist' => $artist];
            if (count($tracks) >= 50) break;
        }
        if (empty($tracks)) {
            $m = [];
            preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m);
            foreach (($m[1] ?? []) as $vid) {
                if (isset($seen[$vid])) continue;
                $seen[$vid] = true;
                $tracks[] = ['videoId' => $vid, 'title' => $title, 'artist' => $artist];
                if (count($tracks) >= 50) break;
            }
        }
        if (!$tracks) jsonError('No se encontraron vídeos en la playlist', 502);
        jsonResponse(['tracks' => $tracks]);
    }

    if ($spotifyAlbumId) {
        $token = getSpotifyToken();
        if (!$token) jsonError('No se pudo conectar con Spotify', 502);
        $headers = ['Authorization: Bearer ' . $token];
        $spTracks = null;
        foreach (['albums/' . $spotifyAlbumId . '/tracks', 'playlists/' . $spotifyAlbumId . '/tracks'] as $ep) {
            $ch = curl_init('https://api.spotify.com/v1/' . $ep . '?limit=50');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => false]);
            $raw = curl_exec($ch); curl_close($ch);
            if (!$raw) continue;
            $data = json_decode($raw, true); if (!isset($data['items'])) continue;
            $items = $data['items'];
            if (!empty($items[0]['track'])) $items = array_values(array_filter(array_map(fn($i) => $i['track'] ?? null, $items)));
            if (!empty($items)) { $spTracks = $items; break; }
        }
        if (!$spTracks) jsonError('No se encontraron canciones en el álbum', 502);
        $queries = []; $meta = [];
        foreach ($spTracks as $t) {
            if (empty($t['name'])) continue;
            $tt = $t['name']; $ta = $t['artists'][0]['name'] ?? $artist;
            $queries[] = $tt . ' ' . $ta . ' audio';
            $meta[]    = ['title' => $tt, 'artist' => $ta];
        }
        if (!$queries) jsonError('Sin pistas válidas', 404);
        $vids = $searchYTParallel($queries);
        $tracks = [];
        foreach ($vids as $i => $v) {
            if (!$v) continue;
            $tracks[] = ['videoId' => $v, 'title' => $meta[$i]['title'], 'artist' => $meta[$i]['artist']];
        }
        if (!$tracks) jsonError('No se encontraron vídeos en YouTube', 502);
        jsonResponse(['tracks' => $tracks]);
    }

    jsonError('Sin datos de reproducción');
}

default:
    jsonError('Acción no válida: ' . $action, 400);
}
