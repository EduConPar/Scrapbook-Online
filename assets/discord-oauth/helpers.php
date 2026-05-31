<?php
/* ──────────────────────────────────────────────────────────────────────
   DISCORD HELPERS
   ──────────────────────────────────────────────────────────────────────
   - discordRedirectUri(): reconstruye la URL del callback.
   - discordAuthUrl(state): URL de autorización OAuth2 con scope identify.
   - discordExchangeCode($code): canjea code → access_token.
   - discordGetMe($accessToken): pide /users/@me, devuelve id + username.
   - discordAssignRole($discordUserId, $roleId): PUT al guild → asigna rol.
   ────────────────────────────────────────────────────────────────────── */

function discordRedirectUri(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    /* Derivamos de SCRIPT_NAME el directorio base del proyecto. SCRIPT_NAME
       será /scrapbookOnline/assets/discord-oauth/<archivo>.php; subimos 3
       niveles para quedarnos con /scrapbookOnline. */
    $base = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')));
    if ($base === '/' || $base === '\\' || $base === '.') $base = '';
    return $scheme . '://' . $host . $base . '/assets/discord-oauth/callback.php';
}

function discordAuthUrl(string $state): string {
    $params = [
        'client_id'     => env('DISCORD_CLIENT_ID', ''),
        'redirect_uri'  => discordRedirectUri(),
        'response_type' => 'code',
        'scope'         => 'identify',
        'state'         => $state,
        'prompt'        => 'consent',
    ];
    return 'https://discord.com/api/oauth2/authorize?' . http_build_query($params);
}

function discordPost(string $url, array $fields): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => (int)$code, 'json' => json_decode((string)$body, true) ?: []];
}

function discordExchangeCode(string $code): ?string {
    $r = discordPost('https://discord.com/api/oauth2/token', [
        'client_id'     => env('DISCORD_CLIENT_ID', ''),
        'client_secret' => env('DISCORD_CLIENT_SECRET', ''),
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => discordRedirectUri(),
    ]);
    return $r['code'] === 200 ? ($r['json']['access_token'] ?? null) : null;
}

function discordGetMe(string $accessToken): ?array {
    $ch = curl_init('https://discord.com/api/users/@me');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    $d = json_decode((string)$body, true);
    if (!is_array($d) || empty($d['id'])) return null;
    /* `username` es el nuevo handle único de Discord (sin discriminador). */
    return [
        'id'       => (string)$d['id'],
        'username' => (string)($d['global_name'] ?? $d['username'] ?? ''),
    ];
}

/* Lista los roles del guild configurado. Cachea 1h en `app_cache` para no
   pegar a la API en cada carga (los nombres cambian raramente). Devuelve
   un mapa `[role_id => ['name' => ..., 'color' => 0xRRGGBB]]`. */
function discordGetRoles(PDO $pdo, int $ttlSec = 3600): array {
    $guild = env('DISCORD_GUILD_ID', '');
    $token = env('DISCORD_BOT_TOKEN', '');
    if ($guild === '' || $token === '') return [];

    $cacheKey = 'discord_roles:' . $guild;
    $st = $pdo->prepare('SELECT cache_value FROM app_cache WHERE cache_key = ? AND (expires_at IS NULL OR expires_at > ?)');
    $st->execute([$cacheKey, time()]);
    $cached = $st->fetchColumn();
    if ($cached !== false) {
        $d = json_decode((string)$cached, true);
        if (is_array($d)) return $d;
    }

    /* Miss → pedir a Discord. */
    $ch = curl_init("https://discord.com/api/v10/guilds/$guild/roles");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bot ' . $token],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($status !== 200) return [];
    $raw = json_decode((string)$body, true);
    if (!is_array($raw)) return [];

    $map = [];
    foreach ($raw as $r) {
        if (empty($r['id'])) continue;
        $map[(string)$r['id']] = [
            'name'  => (string)($r['name']  ?? ''),
            'color' => (int)   ($r['color'] ?? 0),
        ];
    }

    /* Cachear. ON DUPLICATE para refrescar. */
    $ins = $pdo->prepare('INSERT INTO app_cache (cache_key, cache_value, expires_at) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value), expires_at = VALUES(expires_at)');
    $ins->execute([$cacheKey, json_encode($map, JSON_UNESCAPED_UNICODE), time() + $ttlSec]);
    return $map;
}

/* Asigna `$roleId` al miembro `$discordUserId` del guild configurado.
   Devuelve array {ok, status, error}. Discord responde 204 No Content si va
   bien; cualquier 4xx típicamente significa permisos o jerarquía de rol. */
function discordAssignRole(string $discordUserId, string $roleId): array {
    $guild  = env('DISCORD_GUILD_ID', '');
    $token  = env('DISCORD_BOT_TOKEN', '');
    if ($guild === '' || $token === '' || $discordUserId === '' || $roleId === '') {
        return ['ok' => false, 'status' => 0, 'error' => 'missing-config'];
    }
    $url = "https://discord.com/api/v10/guilds/$guild/members/$discordUserId/roles/$roleId";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bot ' . $token,
            'Content-Length: 0',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($status >= 200 && $status < 300) return ['ok' => true, 'status' => $status, 'error' => null];
    $err = json_decode((string)$body, true);
    return [
        'ok'     => false,
        'status' => $status,
        'error'  => $err['message'] ?? ('http-' . $status),
    ];
}
