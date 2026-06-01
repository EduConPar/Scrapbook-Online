<?php
/* ──────────────────────────────────────────────────────────────────────
   ICONO PWA — SVG dinámico para el manifest
   ──────────────────────────────────────────────────────────────────────
   Parámetros (vía query string):
     size=192   ancho/alto en px (mín 16, máx 2048)
     mask=1     versión maskable (safe-zone interior; Android le aplica
                máscara circular en el launcher y necesita margen)
   SVG funciona como icono PWA en Chrome, Edge y Firefox. iOS Safari no
   acepta SVG para apple-touch-icon — ese usa la PNG existente.
   ────────────────────────────────────────────────────────────────────── */

$size = (int)($_GET['size'] ?? 512);
if ($size < 16)   { $size = 16; }
if ($size > 2048) { $size = 2048; }
$mask = !empty($_GET['mask']);

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=2592000, immutable');

/* Escala del dibujo dentro del canvas 512x512:
   - any:      90% del canvas (margen mínimo, el icono ya tiene su
               propio fondo redondeado y se ve cuadrado en el launcher)
   - maskable: 70% del canvas (Android lo recortará a círculo; necesitamos
               safe-zone holgada para que no corte la sandía). */
$scale       = $mask ? 0.7 : 0.9;
$r           = 256.0 * $scale;
$radiusFull  = $r;
$radiusLight = $r * 0.90;
$radiusWhite = $r * 0.85;
$radiusRed   = $r * 0.78;
$rectRadius  = $mask ? 0 : 96;
$translateY  = 256.0 + $r * 0.05;

/* Helper: un path semicircular (la mitad superior de un círculo en
   coords de SVG donde Y crece hacia abajo). Cada capa de la sandía es
   uno de estos paths, de mayor a menor radio. */
function half_disc(float $radius, string $fill): string {
    return sprintf(
        '<path d="M %.2f %.2f A %.2f %.2f 0 0 1 %.2f %.2f Z" fill="%s"/>',
        -$radius, 0.0, $radius, $radius, $radius, 0.0, $fill
    );
}

/* Pepitas dentro de la pulpa: pares [factor_x, factor_y] relativos al
   radio. Se dibujan como elipses negras ligeramente rotadas. */
$seeds = [
    [-0.50, 0.30], [-0.30, 0.50], [-0.10, 0.55],
    [ 0.12, 0.52], [ 0.32, 0.48], [ 0.52, 0.30],
    [-0.42, 0.18], [-0.18, 0.30], [ 0.08, 0.32],
    [ 0.28, 0.28], [ 0.42, 0.15],
];
$seedsSvg = '';
foreach ($seeds as $pair) {
    $sx = $pair[0]; $sy = $pair[1];
    $x  = $sx * $r;
    $y  = $sy * $r;
    $rs = $r * 0.04;
    $seedsSvg .= sprintf(
        '<ellipse cx="%.2f" cy="%.2f" rx="%.2f" ry="%.2f" fill="#1a1a1a" transform="rotate(%d %.2f %.2f)"/>',
        $x, $y, $rs, $rs * 1.4, ($sx > 0 ? 20 : -20), $x, $y
    );
}

$svg  = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 512 512">', $size, $size);
$svg .= '<defs>';
$svg .=   '<linearGradient id="bgGrad" x1="0" y1="0" x2="0" y2="1">';
$svg .=     '<stop offset="0%" stop-color="#1a3a6e"/>';
$svg .=     '<stop offset="100%" stop-color="#061629"/>';
$svg .=   '</linearGradient>';
$svg .=   '<radialGradient id="rind" cx="50%" cy="50%" r="50%">';
$svg .=     '<stop offset="0%" stop-color="#3fb361"/>';
$svg .=     '<stop offset="100%" stop-color="#1f6d36"/>';
$svg .=   '</radialGradient>';
$svg .= '</defs>';
$svg .= sprintf('<rect width="512" height="512" rx="%d" fill="url(#bgGrad)"/>', $rectRadius);
$svg .= sprintf('<g transform="translate(256 %.2f)">', $translateY);
$svg .= half_disc($radiusFull,  'url(#rind)');
$svg .= half_disc($radiusLight, '#7ed18d');
$svg .= half_disc($radiusWhite, '#f7eed8');
$svg .= half_disc($radiusRed,   '#e74c3c');
$svg .= $seedsSvg;
$svg .= '</g></svg>';

echo $svg;
