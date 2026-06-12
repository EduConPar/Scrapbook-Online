<?php
/* ──────────────────────────────────────────────
   HARO PATHS — mapeo slug → URLs de los assets
   ──────────────────────────────────────────────
   Convención de nombres:
     gif:   assets/vids/{slug}Haro.gif
     last:  assets/vids/{slug}Haro-last.png  (extraído con ffmpeg)
     audio: assets/sound/{slug}Haro.mp3      (fallback a haro.mp3)

   Devuelve URLs relativas desde la raíz del proyecto (sin "/").
   Si los archivos del slug no existen, cae al haro 'green' que es
   la base que todos los usuarios tienen. */
function haroPaths(string $slug): array {
    $slug    = preg_replace('/[^a-z0-9_-]/', '', strtolower($slug));
    $default = 'green';
    if ($slug === '') $slug = $default;

    $root = dirname(__DIR__, 2);
    $gif  = "assets/vids/{$slug}Haro.gif";
    $last = "assets/vids/{$slug}Haro-last.png";
    $aud  = "assets/sound/{$slug}Haro.mp3";

    /* Fallbacks si los archivos del slug no existen. */
    if (!file_exists("$root/$gif"))  $gif  = "assets/vids/{$default}Haro.gif";
    if (!file_exists("$root/$last")) $last = "assets/vids/{$default}Haro-last.png";
    if (!file_exists("$root/$aud"))  $aud  = "assets/sound/haro.mp3";

    return [
        'slug'  => $slug,
        'gif'   => $gif,
        'last'  => $last,
        'audio' => $aud,
    ];
}

/* Lee el slug activo del usuario desde user_settings + tienda_items.
   Devuelve 'green' por defecto (haro base de todos). */
function activeHaroSlug(PDO $pdo, int $uid): string {
    try {
        $stmt = $pdo->prepare("
            SELECT i.slug
            FROM user_settings s
            JOIN tienda_items i ON i.id = CAST(s.value AS UNSIGNED)
            WHERE s.user_id = ? AND s.key_name = 'active_haro' AND i.categoria = 'haros'
            LIMIT 1
        ");
        $stmt->execute([$uid]);
        $v = (string) $stmt->fetchColumn();
        if ($v !== '') return $v;
    } catch (Throwable $e) { /* tabla tienda_items.slug puede no existir aún */ }
    return 'green';
}
