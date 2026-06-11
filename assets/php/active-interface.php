<?php
/* ──────────────────────────────────────────────────────────────────────
   active-interface.php
   ──────────────────────────────────────────────────────────────────────
   Helpers para el sistema de INTERFACES (looks completos de la app).

   - Cada interfaz vive en `assets/interfaces/<name>/` con:
       · style.css   — CSS overrides (vacío para la base Win98)
       · meta.json   — { name, label, description, tokens: { ... } }
       · preview.png — opcional, icono mostrado en Temas

   - La interfaz activa se persiste en una COOKIE `activeInterface` que
     el JS frontend (interface-loader.js) actualiza al cambiar y el PHP
     lee aquí para emitir el `<link>` correcto. Sin cookie → default
     'win98'.

   - SEGURIDAD: el nombre se sanitiza con preg_replace para evitar
     path-traversal. Cualquier valor no listado en la carpeta se
     ignora.
   ────────────────────────────────────────────────────────────────────── */

/* Resuelve la ruta absoluta a la carpeta de interfaces. */
function _interfacesDir(): string {
    /* dirname(__DIR__, 2) = raíz del proyecto (sube assets/php/ → /). */
    return dirname(__DIR__, 2) . '/assets/interfaces';
}

/* Lista todas las interfaces disponibles como
   [ [name, label, description, isDefault, previewRel, tokens], ... ]
   Lee meta.json de cada carpeta. */
function listInterfaces(): array {
    $base = _interfacesDir();
    if (!is_dir($base)) return [];
    $out = [];
    foreach (scandir($base) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $dir = $base . '/' . $entry;
        if (!is_dir($dir)) continue;
        $metaFile = $dir . '/meta.json';
        $meta = [];
        if (is_file($metaFile)) {
            $raw = file_get_contents($metaFile);
            $j = json_decode($raw, true);
            if (is_array($j)) $meta = $j;
        }
        /* Detectar preview opcional. */
        $previewRel = '';
        foreach (['preview.png', 'preview.jpg', 'preview.gif'] as $p) {
            if (is_file($dir . '/' . $p)) {
                $previewRel = 'assets/interfaces/' . $entry . '/' . $p;
                break;
            }
        }
        $out[] = [
            'name'        => $entry,
            'label'       => $meta['label']       ?? $entry,
            'description' => $meta['description'] ?? '',
            'isDefault'   => !empty($meta['isDefault']),
            'preview'     => $previewRel,
            'tokens'      => $meta['tokens']      ?? new stdClass(),
        ];
    }
    usort($out, fn($a, $b) => strcasecmp($a['label'], $b['label']));
    return $out;
}

/* Devuelve el nombre de la interfaz activa (de la cookie o el default).
   Valida que la carpeta exista en disco; si no, cae a 'win98'. */
function getActiveInterface(): string {
    $name = $_COOKIE['activeInterface'] ?? '';
    $name = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$name);
    if ($name !== '' && is_dir(_interfacesDir() . '/' . $name) && is_file(_interfacesDir() . '/' . $name . '/style.css')) {
        return $name;
    }
    return 'win98';
}

/* Emite el <link> del CSS de la interfaz activa + el <script> init.js
   opcional si la interfaz lo trae. El path es relativo a la raíz del
   proyecto y debe ajustarse con un prefijo según desde dónde se llame
   (desktop-base.php → "" ; apps/X.php → "../" ;
   apps/mobile/X.php → "../../").

   init.js (opcional): si existe en la carpeta de la interfaz, se emite
   detrás del <link>. Lo usa Win7 para añadir `.active` a todas las
   `.window` y `.title-bar` que 7.css necesita para activar sus hovers
   Aero. Otras interfaces pueden añadirlo si necesitan init JS. */
function emitInterfaceCss(string $relPrefix = ''): void {
    $name   = getActiveInterface();
    $rel    = $relPrefix . 'assets/interfaces/' . $name . '/style.css';
    $cssAbs = dirname(__DIR__, 2) . '/assets/interfaces/' . $name . '/style.css';
    $cssV   = is_file($cssAbs) ? filemtime($cssAbs) : 0;
    echo '<link rel="stylesheet" id="active-interface-link" href="'
        . htmlspecialchars($rel) . '?v=' . $cssV . '">';

    /* init.js opcional. */
    $jsAbs = dirname(__DIR__, 2) . '/assets/interfaces/' . $name . '/init.js';
    if (is_file($jsAbs)) {
        $jsRel = $relPrefix . 'assets/interfaces/' . $name . '/init.js';
        $jsV   = filemtime($jsAbs);
        echo "\n    " . '<script src="' . htmlspecialchars($jsRel) . '?v=' . $jsV . '"></script>';
    }
}
