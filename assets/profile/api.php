<?php
/* ───────────────────────────────────────────────────────────
   PROFILE API — router único de la app Perfil
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
   POST  ?action=play-music-item       { itemType, title, artist, ytId?, ytPlaylistId?, spotifyAlbumId?, spotifyId? }

   El SSE item-notifications-stream.php se mantiene aparte (formato distinto).
   ─────────────────────────────────────────────────────────── */
require_once dirname(__DIR__) . '/auth.php';
require_once __DIR__ . '/notif-helpers.php';

$u         = requireAuth();
$userKey   = $u['key'];
$userLabel = $u['label'];
$action    = $_GET['action'] ?? $_POST['action'] ?? '';

/* ── Helpers de IO ───────────────────────────────────────── */
function pf_profileFile(string $uk): string { return __DIR__ . '/' . $uk . '-profile.json'; }
function pf_listsFile(string $uk):   string { return __DIR__ . '/' . $uk . '-lists.json'; }
function pf_invitesFile(string $uk): string { return __DIR__ . '/' . $uk . '-item-invites.json'; }
function pf_chatFile(string $a, string $b): string {
    $pair = [$a, $b]; sort($pair);
    return __DIR__ . '/chat-' . $pair[0] . '-' . $pair[1] . '.json';
}
function pf_readJson(string $path, $default = []) {
    if (!file_exists($path)) return $default;
    $d = json_decode(file_get_contents($path), true);
    return is_array($d) ? $d : $default;
}
function pf_writeJson(string $path, $data): void {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function pf_emptyLists(): array { return ['movies' => [], 'books' => [], 'games' => [], 'music' => []]; }
function pf_emptyProfile(): array {
    return ['quote'=>'','posts'=>[],'bio'=>'','pronouns'=>'','age'=>'','country'=>'',
            'steam'=>'','discord'=>'','twitter'=>'','instagram'=>'','following'=>[]];
}

switch ($action) {

/* ─── Perfil básico ─────────────────────────────────────── */

case 'get-profile': {
    $d = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($userKey)));
    jsonResponse($d);
}

case 'add-post': {
    $body = jsonBody();
    $text = mb_substr(trim($body['text'] ?? ''), 0, 1000);
    if (!$text) jsonError('Texto vacío');
    $d = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($userKey)));
    $post = ['id' => 'post_' . time() . '_' . rand(100, 999), 'text' => $text, 'createdAt' => time()];
    array_unshift($d['posts'], $post);
    pf_writeJson(pf_profileFile($userKey), $d);
    jsonResponse(['ok' => true, 'post' => $post]);
}

case 'delete-post': {
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', jsonBody()['id'] ?? '');
    if (!$id) jsonError('ID inválido');
    $d = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($userKey)));
    $d['posts'] = array_values(array_filter($d['posts'], fn($p) => ($p['id'] ?? '') !== $id));
    pf_writeJson(pf_profileFile($userKey), $d);
    jsonResponse(['ok' => true]);
}

case 'save-quote': {
    $quote = mb_substr(trim(jsonBody()['quote'] ?? ''), 0, 500);
    $d = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($userKey)));
    $d['quote'] = $quote;
    pf_writeJson(pf_profileFile($userKey), $d);
    jsonResponse(['ok' => true]);
}

case 'save-info': {
    $body = jsonBody();
    if (!$body) jsonError('Datos inválidos');
    $d = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($userKey)));
    $d['bio']       = mb_substr(trim($body['bio']       ?? ''), 0, 200);
    $d['pronouns']  = mb_substr(trim($body['pronouns']  ?? ''), 0, 30);
    $d['age']       = mb_substr(preg_replace('/[^0-9]/', '', $body['age'] ?? ''), 0, 3);
    $d['country']   = mb_substr(trim($body['country']   ?? ''), 0, 50);
    $d['steam']     = mb_substr(trim($body['steam']     ?? ''), 0, 200);
    $d['discord']   = mb_substr(trim($body['discord']   ?? ''), 0, 100);
    $d['twitter']   = mb_substr(trim($body['twitter']   ?? ''), 0, 100);
    $d['instagram'] = mb_substr(trim($body['instagram'] ?? ''), 0, 100);
    pf_writeJson(pf_profileFile($userKey), $d);
    jsonResponse(['ok' => true]);
}

/* ─── Listas ─────────────────────────────────────────────── */

case 'get-lists': {
    $d = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($userKey)));
    jsonResponse($d);
}

case 'save-lists': {
    global $loginUsers;
    $body     = jsonBody();
    $category = $body['category'] ?? '';
    $items    = $body['items']    ?? null;
    if (!in_array($category, ['movies','books','games','music'], true) || !is_array($items)) jsonError('Datos inválidos');

    $lists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($userKey)));
    $isMusic = ($category === 'music');
    $clean = [];
    foreach ($items as $item) {
        if (!isset($item['title']) || !trim($item['title'])) continue;
        $entry = [
            'id'    => isset($item['id']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $item['id']) : uniqid(),
            'title' => mb_substr(trim($item['title']), 0, 200),
            'image' => mb_substr(trim($item['image'] ?? ''), 0, 2000),
        ];
        if ($isMusic) {
            $entry['type']     = in_array($item['type'] ?? '', ['song','album'], true) ? $item['type'] : 'song';
            $entry['artist']   = mb_substr(trim($item['artist'] ?? ''), 0, 200);
            $entry['featured'] = !empty($item['featured']);
            foreach (['ytId','spotifyId','ytPlaylistId','spotifyAlbumId'] as $mf) {
                if (!empty($item[$mf])) $entry[$mf] = preg_replace('/[^A-Za-z0-9_-]/', '', $item[$mf]);
            }
        } else {
            $entry['status'] = in_array($item['status'] ?? '', ['pending','in-progress','completed'], true)
                                ? $item['status'] : 'pending';
        }
        if (isset($item['review']) && is_array($item['review'])) {
            $stars = isset($item['review']['stars']) ? round((float)$item['review']['stars'] * 2) / 2 : 0;
            $entry['review'] = [
                'stars'   => max(0, min(5, $stars)),
                'comment' => mb_substr(trim($item['review']['comment'] ?? ''), 0, 1000),
            ];
        }
        if (isset($item['collaborators']) && is_array($item['collaborators'])) {
            $entry['collaborators'] = array_values(array_filter(array_map(
                fn($u2) => preg_replace('/[^A-Za-z0-9_-]/', '', $u2), $item['collaborators']
            )));
        }
        if (isset($item['sharedFrom']) && isset($loginUsers[$item['sharedFrom']])) {
            $entry['sharedFrom'] = preg_replace('/[^A-Za-z0-9_-]/', '', $item['sharedFrom']);
        }
        $clean[] = $entry;
    }

    /* Detectar items deletados con colaboradores y propagar la eliminación */
    $newIds = array_column($clean, 'id');
    foreach ($lists[$category] as $oldItem) {
        if (in_array($oldItem['id'], $newIds, true)) continue;
        if (empty($oldItem['collaborators'])) continue;
        foreach ($oldItem['collaborators'] as $ck) {
            $ck = preg_replace('/[^A-Za-z0-9_-]/', '', $ck);
            if (!isset($loginUsers[$ck])) continue;
            $cl = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($ck)));
            $cl[$category] = array_values(array_filter($cl[$category], fn($it) => $it['id'] !== $oldItem['id']));
            pf_writeJson(pf_listsFile($ck), $cl);
            $notifs = pf_readJson(pf_invitesFile($ck));
            $notifs[] = [
                'id'        => 'cd_' . time() . '_' . rand(1000, 9999),
                'type'      => 'collab-removed',
                'category'  => $category,
                'itemId'    => $oldItem['id'],
                'itemTitle' => $oldItem['title'] ?? '',
                'fromLabel' => $loginUsers[$userKey]['label'],
                'fromUser'  => $userKey,
                'sentAt'    => time(),
            ];
            pf_writeJson(pf_invitesFile($ck), $notifs);
        }
    }
    $lists[$category] = $clean;
    pf_writeJson(pf_listsFile($userKey), $lists);
    jsonResponse(['ok' => true]);
}

case 'get-item-collabs': {
    global $loginUsers;
    $category = $_GET['category'] ?? '';
    $itemId   = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['itemId'] ?? '');
    if (!in_array($category, ['movies','books','games','music'], true) || !$itemId) jsonError('Datos inválidos');
    $myLists = pf_readJson(pf_listsFile($userKey));
    $userItem = null;
    foreach ($myLists[$category] ?? [] as $it) if (($it['id'] ?? '') === $itemId) { $userItem = $it; break; }
    if (!$userItem) jsonError('Item no encontrado', 404);
    if (isset($userItem['sharedFrom'])) {
        $ownerKey = $userItem['sharedFrom'];
        if (!isset($loginUsers[$ownerKey])) jsonError('Owner inválido');
        $ownerLists = pf_readJson(pf_listsFile($ownerKey));
        foreach ($ownerLists[$category] ?? [] as $it) {
            if (($it['id'] ?? '') === $itemId) {
                jsonResponse(['ownerKey' => $ownerKey, 'collaborators' => $it['collaborators'] ?? []]);
            }
        }
        jsonResponse(['ownerKey' => $ownerKey, 'collaborators' => []]);
    }
    jsonResponse(['ownerKey' => $userKey, 'collaborators' => $userItem['collaborators'] ?? []]);
}

case 'send-item-invite': {
    global $loginUsers;
    $b        = jsonBody();
    $toUser   = preg_replace('/[^A-Za-z0-9_-]/', '', $b['toUser']   ?? '');
    $category = $b['category'] ?? '';
    $itemId   = preg_replace('/[^A-Za-z0-9_-]/', '', $b['itemId']   ?? '');
    if (!$toUser || !$itemId || !in_array($category, ['movies','books','games','music'], true)) jsonError('Datos inválidos');
    if (!isset($loginUsers[$toUser])) jsonError('Usuario destino inválido');
    if ($toUser === $userKey)          jsonError('No puedes invitarte a ti mismo');

    $lists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($userKey)));
    $item = null;
    foreach ($lists[$category] as $it) if (($it['id'] ?? '') === $itemId) { $item = $it; break; }
    if (!$item) jsonError('Elemento no encontrado', 404);
    if (in_array($toUser, $item['collaborators'] ?? [], true)) {
        jsonError($loginUsers[$toUser]['label'] . ' ya es colaborador');
    }
    $invites = pf_readJson(pf_invitesFile($toUser));
    foreach ($invites as $inv) {
        if (($inv['type'] ?? '') === 'invite' && ($inv['itemId'] ?? '') === $itemId && ($inv['fromUser'] ?? '') === $userKey) {
            jsonError('Ya existe una invitación pendiente');
        }
    }
    $notif = [
        'id'        => 'inv_' . time() . '_' . rand(1000, 9999),
        'type'      => 'invite',
        'category'  => $category,
        'itemId'    => $itemId,
        'itemTitle' => $item['title'],
        'itemImage' => $item['image'] ?? '',
        'fromUser'  => $userKey,
        'fromLabel' => $loginUsers[$userKey]['label'],
        'sentAt'    => time(),
    ];
    if ($category === 'music') {
        $notif['itemMusicType'] = $item['type']   ?? 'song';
        $notif['itemArtist']    = $item['artist'] ?? '';
    }
    $invites[] = $notif;
    pf_writeJson(pf_invitesFile($toUser), $invites);
    jsonResponse(['ok' => true]);
}

case 'respond-item-invite': {
    global $loginUsers;
    $b        = jsonBody();
    $inviteId = $b['inviteId'] ?? '';
    $act      = $b['action']   ?? '';
    if (!$inviteId || !in_array($act, ['accept','reject','dismiss'], true)) jsonError('Datos inválidos');

    $invites = pf_readJson(pf_invitesFile($userKey));
    $invite = null;
    foreach ($invites as $inv) if (($inv['id'] ?? '') === $inviteId) { $invite = $inv; break; }
    if (!$invite) jsonError('Invitación no encontrada', 404);
    $invites = array_values(array_filter($invites, fn($i) => ($i['id'] ?? '') !== $inviteId));
    pf_writeJson(pf_invitesFile($userKey), $invites);

    if ($act === 'accept' && isset($invite['fromUser'], $loginUsers[$invite['fromUser']])) {
        $fromUser = $invite['fromUser'];
        $cat      = $invite['category'];
        $iid      = $invite['itemId'];
        $myLists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($userKey)));
        $already = false;
        foreach ($myLists[$cat] as $it) if (($it['id'] ?? '') === $iid) { $already = true; break; }
        if (!$already) {
            $newItem = [
                'id'         => $iid,
                'title'      => $invite['itemTitle'],
                'image'      => $invite['itemImage'] ?? '',
                'sharedFrom' => $fromUser,
            ];
            if ($cat === 'music') {
                $newItem['type']     = $invite['itemMusicType'] ?? 'song';
                $newItem['artist']   = $invite['itemArtist']   ?? '';
                $newItem['featured'] = false;
            } else {
                $newItem['status'] = 'pending';
            }
            $myLists[$cat][] = $newItem;
            pf_writeJson(pf_listsFile($userKey), $myLists);
        }
        $ownerLists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($fromUser)));
        foreach ($ownerLists[$cat] as &$it) {
            if (($it['id'] ?? '') === $iid) {
                if (!isset($it['collaborators'])) $it['collaborators'] = [];
                if (!in_array($userKey, $it['collaborators'], true)) $it['collaborators'][] = $userKey;
                break;
            }
        }
        unset($it);
        pf_writeJson(pf_listsFile($fromUser), $ownerLists);
    }

    if (in_array($act, ['accept','reject'], true) && isset($invite['fromUser'], $loginUsers[$invite['fromUser']])) {
        $host = $invite['fromUser'];
        $hn = pf_readJson(pf_invitesFile($host));
        $hn[] = [
            'id'        => 'resp_' . time() . '_' . rand(1000, 9999),
            'type'      => $act === 'accept' ? 'item-accepted' : 'item-rejected',
            'category'  => $invite['category'],
            'itemId'    => $invite['itemId'],
            'itemTitle' => $invite['itemTitle'],
            'fromLabel' => $loginUsers[$userKey]['label'],
            'fromUser'  => $userKey,
            'sentAt'    => time(),
        ];
        pf_writeJson(pf_invitesFile($host), $hn);
    }
    jsonResponse(['ok' => true]);
}

case 'leave-collab': {
    global $loginUsers;
    $b        = jsonBody();
    $act      = $b['action']   ?? '';
    $category = $b['category'] ?? '';
    $itemId   = preg_replace('/[^A-Za-z0-9_-]/', '', $b['itemId'] ?? '');
    if (!in_array($act, ['leave','remove'], true) || !in_array($category, ['movies','books','games','music'], true) || !$itemId) {
        jsonError('Datos inválidos');
    }

    if ($act === 'leave') {
        $myLists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($userKey)));
        $ownerKey = null; $itemTitle = '';
        foreach ($myLists[$category] as $it) {
            if (($it['id'] ?? '') === $itemId && isset($it['sharedFrom'])) {
                $ownerKey = $it['sharedFrom']; $itemTitle = $it['title']; break;
            }
        }
        if (!$ownerKey || !isset($loginUsers[$ownerKey])) jsonError('No eres colaborador de este elemento');
        $myLists[$category] = array_values(array_filter($myLists[$category], fn($it) => $it['id'] !== $itemId));
        pf_writeJson(pf_listsFile($userKey), $myLists);

        $ownerLists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($ownerKey)));
        foreach ($ownerLists[$category] as &$it) {
            if (($it['id'] ?? '') === $itemId) {
                if (isset($it['collaborators'])) {
                    $it['collaborators'] = array_values(array_filter($it['collaborators'], fn($u2) => $u2 !== $userKey));
                }
                $itemTitle = $it['title']; break;
            }
        }
        unset($it);
        pf_writeJson(pf_listsFile($ownerKey), $ownerLists);

        $on = pf_readJson(pf_invitesFile($ownerKey));
        $on[] = [
            'id'        => 'cl_' . time() . '_' . rand(1000, 9999),
            'type'      => 'collab-left',
            'category'  => $category,
            'itemId'    => $itemId,
            'itemTitle' => $itemTitle,
            'fromLabel' => $loginUsers[$userKey]['label'],
            'fromUser'  => $userKey,
            'sentAt'    => time(),
        ];
        pf_writeJson(pf_invitesFile($ownerKey), $on);
    } else {
        $collab = preg_replace('/[^A-Za-z0-9_-]/', '', $b['collaboratorUser'] ?? '');
        if (!$collab || !isset($loginUsers[$collab])) jsonError('Usuario colaborador inválido');
        $myLists = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($userKey)));
        $itemTitle = '';
        foreach ($myLists[$category] as &$it) {
            if (($it['id'] ?? '') === $itemId) {
                if (!isset($it['collaborators']) || !in_array($collab, $it['collaborators'], true)) jsonError('El usuario no es colaborador');
                $it['collaborators'] = array_values(array_filter($it['collaborators'], fn($u2) => $u2 !== $collab));
                $itemTitle = $it['title']; break;
            }
        }
        unset($it);
        pf_writeJson(pf_listsFile($userKey), $myLists);

        $cl = array_merge(pf_emptyLists(), pf_readJson(pf_listsFile($collab)));
        $cl[$category] = array_values(array_filter($cl[$category], fn($it) => $it['id'] !== $itemId));
        pf_writeJson(pf_listsFile($collab), $cl);

        $cn = pf_readJson(pf_invitesFile($collab));
        $cn[] = [
            'id'        => 'cr_' . time() . '_' . rand(1000, 9999),
            'type'      => 'collab-removed',
            'category'  => $category,
            'itemId'    => $itemId,
            'itemTitle' => $itemTitle,
            'fromLabel' => $loginUsers[$userKey]['label'],
            'fromUser'  => $userKey,
            'sentAt'    => time(),
        ];
        pf_writeJson(pf_invitesFile($collab), $cn);
    }
    jsonResponse(['ok' => true]);
}

/* ─── Seguimiento / likes / notifs ───────────────────────── */

case 'toggle-follow': {
    global $loginUsers;
    $target = preg_replace('/[^a-z0-9_]/i', '', jsonBody()['targetUser'] ?? '');
    if (!$target || !isset($loginUsers[$target])) jsonError('Usuario no encontrado');
    if ($target === $userKey)                     jsonError('No puedes seguirte');
    $d = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($userKey)));
    if (!is_array($d['following'])) $d['following'] = [];
    $idx = array_search($target, $d['following'], true);
    $following = ($idx === false);
    if ($following) $d['following'][] = $target;
    else            array_splice($d['following'], $idx, 1);
    pf_writeJson(pf_profileFile($userKey), $d);

    if ($following) {
        addProfileNotif($target, 'follow', $userKey);
    } else {
        removeProfileNotifsMatching($target, fn($n) => ($n['type'] ?? '') === 'follow' && ($n['fromUser'] ?? '') === $userKey);
    }
    jsonResponse(['ok' => true, 'following' => $following, 'list' => $d['following']]);
}

case 'toggle-post-like': {
    global $loginUsers;
    $b      = jsonBody();
    $target = preg_replace('/[^a-z0-9_]/i', '', $b['targetUser'] ?? '');
    $postId = trim($b['postId'] ?? '');
    if (!$target || !isset($loginUsers[$target])) jsonError('Usuario no encontrado');
    if (!$postId)                                  jsonError('Post inválido');
    $d = pf_readJson(pf_profileFile($target));
    if (!isset($d['posts']) || !is_array($d['posts'])) jsonError('Sin posts', 404);
    $found = false; $liked = false; $count = 0; $postText = '';
    foreach ($d['posts'] as &$post) {
        if (($post['id'] ?? '') !== $postId) continue;
        $found = true;
        if (!isset($post['likes']) || !is_array($post['likes'])) $post['likes'] = [];
        $idx = array_search($userKey, $post['likes'], true);
        if ($idx === false) { $post['likes'][] = $userKey; $liked = true; }
        else                { array_splice($post['likes'], $idx, 1); $liked = false; }
        $count = count($post['likes']);
        $postText = mb_substr($post['text'] ?? '', 0, 80);
        break;
    }
    unset($post);
    if (!$found) jsonError('Post no encontrado', 404);
    pf_writeJson(pf_profileFile($target), $d);
    if ($liked) {
        addProfileNotif($target, 'like', $userKey, ['postId' => $postId, 'postText' => $postText]);
    } else {
        removeProfileNotifsMatching($target, fn($n) =>
            ($n['type'] ?? '') === 'like' && ($n['fromUser'] ?? '') === $userKey && ($n['postId'] ?? '') === $postId);
    }
    jsonResponse(['ok' => true, 'liked' => $liked, 'count' => $count]);
}

case 'get-profile-notifs': {
    $list = readProfileNotifs($userKey);
    $unread = 0;
    foreach ($list as $n) if (empty($n['read'])) $unread++;
    jsonResponse(['ok' => true, 'notifs' => $list, 'unread' => $unread]);
}

case 'mark-notifs-read': {
    $list = readProfileNotifs($userKey);
    foreach ($list as &$n) $n['read'] = true;
    unset($n);
    writeProfileNotifs($userKey, $list);
    jsonResponse(['ok' => true]);
}

case 'get-followers': {
    global $loginUsers;
    $followers = [];
    foreach ($loginUsers as $uk => $_u) {
        if ($uk === $userKey) continue;
        $d = pf_readJson(pf_profileFile($uk));
        $following = isset($d['following']) && is_array($d['following']) ? $d['following'] : [];
        if (in_array($userKey, $following, true)) $followers[] = $uk;
    }
    jsonResponse(['ok' => true, 'followers' => $followers]);
}

case 'notify-review': {
    global $loginUsers;
    $b        = jsonBody();
    $category = preg_replace('/[^a-z]/i', '', $b['category']  ?? '');
    $title    = mb_substr(trim($b['itemTitle'] ?? ''), 0, 120);
    $mtype    = preg_replace('/[^a-z]/i', '', $b['mtype']     ?? '');
    if (!$category || !$title) jsonError('Faltan datos');
    if (!in_array($category, ['movies','books','games','music'], true)) jsonError('Categoría inválida');
    if ($mtype !== '' && !in_array($mtype, ['album','song'], true)) $mtype = '';

    foreach ($loginUsers as $uk => $_u) {
        if ($uk === $userKey) continue;
        $d = pf_readJson(pf_profileFile($uk));
        $following = isset($d['following']) && is_array($d['following']) ? $d['following'] : [];
        if (!in_array($userKey, $following, true)) continue;
        removeProfileNotifsMatching($uk, fn($n) =>
            ($n['type'] ?? '') === 'review' && ($n['fromUser'] ?? '') === $userKey
            && ($n['category'] ?? '') === $category && ($n['itemTitle'] ?? '') === $title);
        addProfileNotif($uk, 'review', $userKey, [
            'category'  => $category,
            'itemTitle' => $title,
            'mtype'     => $mtype,
        ]);
    }
    jsonResponse(['ok' => true]);
}

/* ─── Chat ──────────────────────────────────────────────── */

case 'get-unread-chats': {
    global $loginUsers;
    $counts = [];
    foreach ($loginUsers as $uk => $_u) {
        if ($uk === $userKey) continue;
        $raw = pf_readJson(pf_chatFile($userKey, $uk));
        if (!$raw) continue;
        $messages = $raw['messages'] ?? (isset($raw[0]) ? $raw : []);
        $lastSeen = $raw['lastSeen'] ?? [];
        $myLast = isset($lastSeen[$userKey]) ? (int)$lastSeen[$userKey] : 0;
        $unread = 0;
        foreach ($messages as $m) {
            if (!isset($m['from']) || $m['from'] === $userKey) continue;
            if ((int)($m['sentAt'] ?? 0) > $myLast) $unread++;
        }
        if ($unread > 0) $counts[$uk] = $unread;
    }
    jsonResponse(['ok' => true, 'counts' => $counts]);
}

case 'get-messages': {
    global $loginUsers;
    $with = preg_replace('/[^a-z0-9_]/i', '', $_GET['with'] ?? '');
    if (!$with || !isset($loginUsers[$with])) jsonError('Usuario inválido');
    if ($with === $userKey)                    jsonError('Mismo usuario');
    $file = pf_chatFile($userKey, $with);
    $data = ['messages' => [], 'lastSeen' => []];
    if (file_exists($file)) {
        $raw = json_decode(file_get_contents($file), true);
        if (is_array($raw)) {
            if (isset($raw['messages'], $raw['lastSeen'])) $data = $raw;
            else $data['messages'] = $raw;
        }
    }
    $data['lastSeen'][$userKey] = time();
    pf_writeJson($file, $data);
    jsonResponse(['ok' => true, 'messages' => $data['messages']]);
}

case 'send-message': {
    global $loginUsers;
    $b    = jsonBody();
    $to   = preg_replace('/[^a-z0-9_]/i', '', $b['to'] ?? '');
    $text = mb_substr(trim($b['text'] ?? ''), 0, 2000);
    if (!$to || !isset($loginUsers[$to])) jsonError('Destinatario inválido');
    if ($to === $userKey)                  jsonError('No puedes hablarte a ti mismo');
    if ($text === '')                      jsonError('Mensaje vacío');

    $follows = function(string $a, string $b): bool {
        $d = is_file(__DIR__ . '/' . $a . '-profile.json')
            ? json_decode(file_get_contents(__DIR__ . '/' . $a . '-profile.json'), true) : null;
        return is_array($d) && in_array($b, $d['following'] ?? [], true);
    };
    if (!$follows($userKey, $to) || !$follows($to, $userKey)) {
        jsonError('Necesitas seguirse mutuamente para hablar', 403);
    }

    $file = pf_chatFile($userKey, $to);
    $data = ['messages' => [], 'lastSeen' => []];
    if (file_exists($file)) {
        $raw = json_decode(file_get_contents($file), true);
        if (is_array($raw)) {
            if (isset($raw['messages'], $raw['lastSeen'])) $data = $raw;
            else $data['messages'] = $raw;
        }
    }
    $now = time();
    $msg = ['id' => 'msg_' . $now . '_' . rand(100, 9999), 'from' => $userKey, 'text' => $text, 'sentAt' => $now];
    $data['messages'][] = $msg;
    if (count($data['messages']) > 500) $data['messages'] = array_slice($data['messages'], -500);
    $data['lastSeen'][$userKey] = $now;
    pf_writeJson($file, $data);
    jsonResponse(['ok' => true, 'message' => $msg]);
}

/* ─── Ver perfil ajeno ──────────────────────────────────── */

case 'view-user': {
    global $loginUsers;
    $target = preg_replace('/[^a-z0-9_]/i', '', $_GET['user'] ?? '');
    if (!$target || !isset($loginUsers[$target])) jsonError('Usuario no encontrado', 404);
    $profile = array_merge(pf_emptyProfile(), pf_readJson(pf_profileFile($target)));
    unset($profile['following']);
    $lists   = array_merge(pf_emptyLists(),   pf_readJson(pf_listsFile($target)));
    $reqD    = pf_readJson(pf_profileFile($userKey));
    $isFollowing = is_array($reqD) && in_array($target, $reqD['following'] ?? [], true);
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
    if (!in_array($cat, ['movies','books','games','music'], true)) jsonError('Categoría inválida');
    if (!in_array($period, ['year','recent','alltime'], true))     jsonError('Período inválido');
    if ($type !== '' && !in_array($type, ['album','song'], true))  jsonError('Tipo inválido');
    $yearAgo = time() - 365 * 24 * 60 * 60;

    $reviewTs = function($item): int {
        if (isset($item['review']['reviewedAt']) && is_numeric($item['review']['reviewedAt'])) {
            return (int)$item['review']['reviewedAt'];
        }
        if (isset($item['id']) && preg_match('/_(\d{10,})/', $item['id'], $m)) return (int)$m[1];
        return 0;
    };

    $groups = [];
    foreach ($loginUsers as $uk => $userData) {
        $d = pf_readJson(pf_listsFile($uk));
        if (!isset($d[$cat]) || !is_array($d[$cat])) continue;
        foreach ($d[$cat] as $item) {
            if (!isset($item['review']['stars']) || !is_numeric($item['review']['stars'])) continue;
            $stars = (float)$item['review']['stars'];
            if ($stars <= 0) continue;
            if ($cat === 'music' && $type !== '' && ($item['type'] ?? '') !== $type) continue;
            $rAt = $reviewTs($item);
            if ($period === 'year' && $rAt < $yearAgo) continue;
            $title = trim($item['title'] ?? ''); if (!$title) continue;
            $key = mb_strtolower($title);
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'title'          => $title,
                    'image'          => $item['image'] ?? '',
                    'artist'         => $item['artist'] ?? '',
                    'mtype'          => $item['type'] ?? '',
                    'ytId'           => $item['ytId'] ?? '',
                    'spotifyId'      => $item['spotifyId'] ?? '',
                    'ytPlaylistId'   => $item['ytPlaylistId'] ?? '',
                    'spotifyAlbumId' => $item['spotifyAlbumId'] ?? '',
                    'totalStars'     => 0.0, 'totalReviews' => 0, 'latestAt' => 0, 'reviews' => [],
                ];
            }
            foreach (['image','artist','mtype','ytId','spotifyId','ytPlaylistId','spotifyAlbumId'] as $f) {
                $src = $f === 'mtype' ? 'type' : $f;
                if (empty($groups[$key][$f]) && !empty($item[$src])) $groups[$key][$f] = $item[$src];
            }
            $groups[$key]['totalStars']   += $stars;
            $groups[$key]['totalReviews']++;
            if ($rAt > $groups[$key]['latestAt']) $groups[$key]['latestAt'] = $rAt;
            $groups[$key]['reviews'][] = [
                'user'       => $uk,
                'userLabel'  => $userData['label'],
                'userImg'    => function_exists('getUserImage') ? getUserImage($userData['label']) : '',
                'stars'      => $stars,
                'comment'    => $item['review']['comment'] ?? '',
                'reviewedAt' => $rAt,
            ];
        }
    }

    $items = [];
    foreach ($groups as $g) {
        usort($g['reviews'], fn($a, $b) => $b['reviewedAt'] - $a['reviewedAt']);
        $e = [
            'title'    => $g['title'], 'image' => $g['image'], 'artist' => $g['artist'], 'mtype' => $g['mtype'],
            'avg'      => round($g['totalStars'] / $g['totalReviews'], 2),
            'count'    => $g['totalReviews'], 'latestAt' => $g['latestAt'], 'reviews' => $g['reviews'],
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

/* ─── Música (resolución/playback de items de listas) ───── */

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
        preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"[^}]{0,400}?"title"\s*:\s*\{\s*"runs"\s*:\s*\[\s*\{\s*"text"\s*:\s*"([^"]+)"/', $html, $pairs);
        $seen = []; $tracks = [];
        for ($i = 0; $i < count($pairs[1] ?? []); $i++) {
            $vid = $pairs[1][$i]; if (isset($seen[$vid])) continue; $seen[$vid] = true;
            $tracks[] = ['videoId' => $vid, 'title' => $pairs[2][$i] ?: $title, 'artist' => $artist];
            if (count($tracks) >= 50) break;
        }
        if (empty($tracks)) {
            preg_match_all('/"videoId"\s*:\s*"([A-Za-z0-9_-]{11})"/', $html, $m);
            foreach ($m[1] as $vid) {
                if (isset($seen[$vid])) continue; $seen[$vid] = true;
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
