<?php
/* ──────────────────────────────────────────────────────────────────
   Helpers de relación social compartidos por los APIs.

   isMutualFollow($pdo, $aId, $bId) → bool
   - true  si A sigue a B Y B sigue a A (seguimiento mutuo).
   - true  si A === B (uno mismo).
   - false si falta cualquier lado o IDs inválidos.

   Usado para restringir interacciones (colab playlists, colab items,
   ver OCs ajenos) a usuarios que se siguen mutuamente.
   ────────────────────────────────────────────────────────────────── */

if (!function_exists('isMutualFollow')) {
    function isMutualFollow(PDO $pdo, int $aId, int $bId): bool {
        if ($aId <= 0 || $bId <= 0) return false;
        if ($aId === $bId)          return true;
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM follows
            WHERE (follower_id = ? AND followee_id = ?)
               OR (follower_id = ? AND followee_id = ?)
        ");
        $stmt->execute([$aId, $bId, $bId, $aId]);
        return ((int)$stmt->fetchColumn()) >= 2;
    }
}

/* Devuelve los ids de los usuarios que siguen mutuamente al usuario dado.
   Útil para filtrar listados (list_all OCs) a solo seguidores mutuos. */
if (!function_exists('mutualFollowerIds')) {
    function mutualFollowerIds(PDO $pdo, int $uid): array {
        if ($uid <= 0) return [];
        $stmt = $pdo->prepare("
            SELECT f1.followee_id
              FROM follows f1
              JOIN follows f2
                ON f2.follower_id = f1.followee_id
               AND f2.followee_id = f1.follower_id
             WHERE f1.follower_id = ?
        ");
        $stmt->execute([$uid]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
