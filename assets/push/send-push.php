<?php
/* ════════════════════════════════════════════════════════════════
   send-push.php — helper genérico para mandar Web Push notifications
   a un usuario destinatario. Best-effort: errores se silencian.

   sendPushToUser(PDO $pdo, int $toUid, array $payload): void
   - $payload es un array con keys (al menos):
       title  → string visible del notificación
       body   → string subtítulo
       url    → URL de deep-link a abrir en tap (relativa al dominio)
       icon   → URL opcional del icono
       tag    → opcional, agrupa notificaciones del mismo tema
   - Limpia subscripciones muertas (HTTP 404/410).

   Notas:
   - VAPID keys deben existir en assets/push/vapid-keys.php.
   - WebPush lib debe estar en assets/push/webpush.php.
═══════════════════════════════════════════════════════════════════ */

if (!function_exists('sendPushToUser')) {
    function sendPushToUser(PDO $pdo, int $toUid, array $payload): void {
        $libPath = __DIR__ . '/webpush.php';
        if (!file_exists($libPath)) return;
        $keysPath = __DIR__ . '/vapid-keys.php';
        if (!file_exists($keysPath)) return;

        $stmt = $pdo->prepare("SELECT id, endpoint, p256dh, auth FROM push_subscriptions WHERE user_id = ?");
        $stmt->execute([$toUid]);
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$subs) return;

        require_once $libPath;
        try { $wp = new WebPush(); } catch (Throwable $e) { return; }

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        foreach ($subs as $s) {
            try {
                list($code, $_resp) = $wp->send([
                    'endpoint' => $s['endpoint'],
                    'p256dh'   => $s['p256dh'],
                    'auth'     => $s['auth'],
                ], $payloadJson, 60);
                if ($code === 404 || $code === 410) {
                    $pdo->prepare("DELETE FROM push_subscriptions WHERE id = ?")->execute([$s['id']]);
                }
            } catch (Throwable $e) { /* silencio */ }
        }
    }
}

/* Helper de conveniencia: construye un payload de tipo 'invite' con los
   campos comunes y la URL de deep-link al shell móvil (?pwa=1#...). */
if (!function_exists('buildInvitePushPayload')) {
    function buildInvitePushPayload(string $source, string $title, string $body, ?string $iconUrl = null): array {
        return [
            'type'  => 'invite',
            'title' => $title,
            'body'  => $body,
            'icon'  => $iconUrl,
            'tag'   => 'invite-' . $source,
            /* Deep-link: el shell móvil parsea #notif=<source> al cargar y
               abre el sheet de invites pendientes. */
            'url'   => '/scrapbookOnline/mobile.php?pwa=1#notif=' . $source,
        ];
    }
}
