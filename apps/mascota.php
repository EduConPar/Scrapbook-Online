<?php
/**
 * apps/mascota.php
 * Ventana de gestión de la mascota virtual.
 * Se carga dentro del iframe #mascota-window en desktop-base.php.
 */

@ini_set('display_errors', '0');
session_start();
require_once __DIR__ . '/../assets/config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../assets/themes/theme-helpers.php';

/* ── Auth ─────────────────────────────────────────────────────── */
if (empty($_SESSION['user']) || !isset($loginUsers[$_SESSION['user']])) {
    http_response_code(403); echo 'No autorizado'; exit;
}
$userKey    = $_SESSION['user'];
$userLabel  = $loginUsers[$userKey]['label'];
$userId     = userIdByKey($userKey);

/* ── Tema activo ──────────────────────────────────────────────── */
refreshActiveThemeCss($userKey, $userLabel);
$_ut         = loadUserThemes($userKey);
$activeTheme = !empty($_ut['active']) ? sanitizeThemeName($_ut['active']) : '';
$themeCssRel = '';
$themeClass  = '';
if ($activeTheme !== '' && isset(((array)$_ut['themes'])[$activeTheme])) {
    $themeClass  = themeCssClassName($activeTheme, $userLabel);
    $themeCssRel = '../' . themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(__DIR__ . '/' . $themeCssRel)) $themeCssRel = '';
}

/* ── Datos mascota (SSR para carga inicial sin flicker) ────────── */
function getMascotaSSR(PDO $pdo, int $uid): array {
    $stmt = $pdo->prepare("SELECT * FROM mascotas WHERE user_id = ?");
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [];

    $now       = time();
    $diffSecs  = max(0, $now - strtotime($row['ultima_vez']));
    $diffMins  = $diffSecs / 60;
    $isEgg     = !((int)($row['eclosionado'] ?? 1));

    if ($row['viva'] && $diffMins > 0) {
        if ($isEgg) {
            /* Huevo: temperatura -1/h. Si llega a 0 muere de frío. */
            $row['temperatura'] = max(0, (int)($row['temperatura'] ?? 80) - (int)floor($diffMins / 60));
            if ($row['temperatura'] <= 0) $row['viva'] = 0;
        } else {
            /* Mascota: decay clásico de hambre + felicidad. */
            $row['hambre']    = max(0, (int)$row['hambre']    - (int)floor($diffMins / 30));
            $row['felicidad'] = max(0, (int)$row['felicidad'] - (int)floor($diffMins / 60));
            if ($row['hambre'] === 0 && $diffSecs >= 86400) $row['viva'] = 0;
        }
    }
    $row['viva']        = (bool)$row['viva'];
    $row['hambre']      = (int)$row['hambre'];
    $row['felicidad']   = (int)$row['felicidad'];
    $row['temperatura'] = (int)($row['temperatura'] ?? 80);
    $row['edad']        = (int)$row['edad'];
    $row['eclosionado'] = (bool)($row['eclosionado'] ?? 1);
    /* Segundos restantes para que el huevo eclosione (UI cuenta atrás). */
    if (!$row['eclosionado'] && !empty($row['eclosion_at'])) {
        $row['segundos_para_eclosion'] = max(0, strtotime($row['eclosion_at']) - $now);
    } else {
        $row['segundos_para_eclosion'] = null;
    }
    return $row;
}
$mascota = getMascotaSSR($pdo, $userId);
$hasMascota = !empty($mascota);

/* Memoria */
$memoria = [];
if ($hasMascota) {
    $stmtM = $pdo->prepare("SELECT clave, valor FROM mascota_memoria WHERE user_id = ?");
    $stmtM->execute([$userId]);
    $memoria = $stmtM->fetchAll(PDO::FETCH_KEY_PAIR);
}

/* Gustos: catálogo + valores del usuario, ordenados de mayor a menor. */
require_once dirname(__DIR__) . '/assets/mascota/foods.php';
$gustos = [];
if ($hasMascota && $mascota['eclosionado']) {
    $stmtG = $pdo->prepare("SELECT alimento, valor, revelado FROM mascota_gustos WHERE user_id = ?");
    $stmtG->execute([$userId]);
    $byAlim = [];
    foreach ($stmtG->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $byAlim[$r['alimento']] = [
            'valor'    => (int)$r['valor'],
            'revelado' => (bool)$r['revelado'],
        ];
    }
    foreach (MASCOTA_FOODS as $slug => $meta) {
        $g = $byAlim[$slug] ?? ['valor' => 50, 'revelado' => false];
        $gustos[] = [
            'slug'     => $slug,
            'nombre'   => $meta['nombre'],
            'emoji'    => $meta['emoji'],
            'valor'    => $g['valor'],
            'revelado' => $g['revelado'],
        ];
    }
    usort($gustos, fn($a,$b) => $b['valor'] <=> $a['valor']);
}

/* La skin ACTUAL de la mascota se lee directamente de la fila. Queda
   fijada en el INSERT y no cambia aunque el usuario elija otra skin en
   Temas → Personalización. Para "cambiar" hay que eliminar la mascota
   (botón en la pestaña Estado o en el menú flotante) y la próxima
   se creará con la skin preferida actual. */
$currentSkin = $hasMascota ? ($mascota['skin'] ?? 'gabriel') : 'gabriel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, user-scalable=yes">
    <title>Mascota</title>
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <?php if ($themeCssRel): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?= htmlspecialchars($themeCssRel) ?>">
    <?php endif; ?>
    <style>
        /* ── Layout base ─────────────────────────────────────────
           Toda la ventana adopta el look Win98 con los tokens del
           tema activo del usuario. Los nombres correctos son
           --bezel-light-1/-2 y --bezel-dark-1/-2 (con guion). */
        html, body {
            margin: 0; padding: 0;
            height: 100%; overflow: hidden;
            background: var(--win-bg, silver);
            color: var(--text, #000);
            font-size: 11px;
            font-family: 'Pixelated MS Sans Serif', 'ms_sans_serif', Tahoma, sans-serif;
        }
        #mascota-app {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* ── Tabs Win98 (tipo carpeta) ───────────────────────────
           Pestañas raised con bezel inset de 4 capas; la activa
           se solapa visualmente con el panel y baja 1px para dar
           sensación de continuidad. */
        .tab-bar {
            display: flex;
            gap: 0;
            padding: 4px 4px 0;
            background: var(--win-bg, silver);
            border-bottom: 1px solid var(--bezel-dark-1, #0a0a0a);
            flex-shrink: 0;
        }
        .tab-btn {
            min-height: 24px;
            padding: 4px 14px;
            margin: 0 -1px 0 0;
            font-size: 11px;
            font-family: inherit;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            cursor: pointer;
            border: 0;
            border-radius: 0;
            position: relative;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }
        .tab-btn.active {
            font-weight: bold;
            z-index: 2;
            top: 1px;
            padding-bottom: 6px;
            box-shadow:
                inset  1px  1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-light-2, #dfdfdf),
                inset -1px 0   var(--bezel-dark-1, #0a0a0a),
                inset -2px 0   var(--bezel-dark-2, grey);
        }
        .tab-panel {
            display: none; flex: 1;
            overflow-y: auto;
            padding: 12px;
            background: var(--win-bg, silver);
        }
        .tab-panel.active { display: block; }

        /* ── Stat bars (hambre/felicidad) ────────────────────────
           Pista hundida Win98 (sunken inset 4-capas) + fill que
           usa --accent del tema. */
        .stat-row {
            display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
        }
        .stat-label { width: 72px; flex-shrink: 0; font-size: 11px; }
        .stat-track {
            flex: 1; height: 16px;
            background: var(--input-bg, #fff);
            position: relative;
            overflow: hidden;
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
        }
        .stat-fill {
            height: 100%; transition: width .4s ease;
            background: var(--accent, #000080);
        }
        .stat-val {
            width: 36px; text-align: right; flex-shrink: 0;
            font-size: 11px; font-variant-numeric: tabular-nums;
        }

        /* ── Skin selector ─────────────────────────────────────── */
        .skin-grid {
            display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;
        }
        .skin-card {
            padding: 10px 16px; cursor: pointer; text-align: center;
            background: var(--btn-bg, silver);
            color: var(--text, #000);
            border: 0;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }
        .skin-card.selected {
            background: var(--accent, #000080);
            color: var(--accent-text, #fff);
            box-shadow:
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  1px  1px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf),
                inset  2px  2px var(--bezel-dark-2, grey);
        }
        .skin-card .skin-emoji { font-size: 24px; display: block; }
        .skin-card .skin-name  { font-size: 10px; margin-top: 3px; }

        /* ── Memoria grid ─────────────────────────────────────── */
        .mem-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 6px; }
        .mem-card {
            padding: 6px 8px;
            background: var(--input-bg, #fff);
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
        }
        .mem-card .mem-key  { color: var(--text-muted, #666); font-size: 10px; margin-bottom: 2px; }
        .mem-card .mem-val  { color: var(--text, #000); font-size: 11px; word-break: break-word; }
        .mem-card .mem-edit { float: right; font-size: 10px; cursor: pointer; color: var(--link-text,#00f); }
        .mem-empty { color: var(--text-muted,#666); font-style: italic; font-size: 11px; }

        /* ── Sprite preview ─────────────────────────────────────
           Panel hundido para el frame de la mascota. */
        .sprite-preview {
            width: 128px; height: 128px;
            image-rendering: pixelated;
            background: var(--input-bg, #fff);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
        }
        .sprite-preview img {
            width: 128px; height: 128px;
            image-rendering: pixelated;
        }

        /* ── Estado general ─────────────────────────────────────── */
        .estado-header {
            display: flex; gap: 12px; align-items: flex-start;
            margin-bottom: 10px;
        }
        .estado-info { flex: 1; }
        .estado-name {
            font-size: 14px; font-weight: bold;
            color: var(--text,#000); margin-bottom: 4px;
        }
        .estado-sub  { color: var(--text-muted,#666); font-size: 10px; }
        .dead-banner {
            background: var(--error-text, #800);
            color: #fff;
            padding: 8px 10px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, rgba(255,255,255,0.5)),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, rgba(255,255,255,0.3));
        }
        .action-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }

        /* ── Botón "eliminar (testeo)" — panel danger ──────────── */
        .delete-zone {
            margin-top: 16px;
            padding: 10px;
            background: var(--win-bg, silver);
            box-shadow:
                inset  1px  1px var(--bezel-dark-2, grey),
                inset -1px -1px var(--bezel-light-1, #fff),
                inset  2px  2px var(--bezel-dark-1, #0a0a0a),
                inset -2px -2px var(--bezel-light-2, #dfdfdf);
        }
        .delete-zone h4 {
            margin: 0 0 4px;
            color: var(--error-text, #c00);
            font-size: 11px;
        }
        .delete-zone p {
            margin: 0 0 8px;
            font-size: 10px;
            color: var(--text-muted, #666);
        }
        .btn-danger {
            color: var(--error-text, #c00);
            font-weight: bold;
        }

        /* ── Rename input ────────────────────────────────────────── */
        .rename-row { display: flex; gap: 6px; align-items: center; margin-top: 8px; }
        .rename-row input { flex: 1; }

        /* ── Scrollbar Win98 con tokens ─────────────────────────── */
        ::-webkit-scrollbar       { width: 16px; height: 16px; }
        ::-webkit-scrollbar-track {
            background: var(--win-bg, silver);
            background-image:
                repeating-linear-gradient(
                    45deg,
                    var(--bezel-light-2, #dfdfdf) 0 1px,
                    transparent 1px 2px
                );
        }
        ::-webkit-scrollbar-thumb {
            background: var(--btn-bg, silver);
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }

        /* ── Notifications inline (usan tokens del tema) ────────── */
        .inline-msg {
            padding: 6px 10px; margin-top: 8px;
            font-size: 11px;
            display: none;
            box-shadow:
                inset -1px -1px var(--bezel-dark-1, #0a0a0a),
                inset  1px  1px var(--bezel-light-1, #fff),
                inset -2px -2px var(--bezel-dark-2, grey),
                inset  2px  2px var(--bezel-light-2, #dfdfdf);
        }
        .inline-msg.ok    { background: var(--accent-deep, #060); color: #fff; }
        .inline-msg.error { background: var(--error-text, #c00); color: #fff; }
    </style>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">
<div id="mascota-app">

    <!-- ── Tab bar ─────────────────────────────────────────────── -->
    <div class="tab-bar">
        <button class="tab-btn active" data-tab="estado">🐾 Estado</button>
        <?php if ($hasMascota && $mascota['eclosionado']): ?>
        <button class="tab-btn"        data-tab="gustos">💕 Gustos</button>
        <?php endif; ?>
        <button class="tab-btn"        data-tab="memoria">💬 Memoria</button>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         TAB: ESTADO
    ═══════════════════════════════════════════════════════════ -->
    <div id="tab-estado" class="tab-panel active">

        <?php if (!$hasMascota): ?>
        <!-- Primera vez: crear mascota -->
        <p style="margin:0 0 10px;">¡Aún no tienes mascota! Ponle nombre y empieza.</p>
        <div class="field-row-stacked">
            <label for="new-name">Nombre</label>
            <input id="new-name" type="text" maxlength="40" value="Meloncio" style="width:200px;">
        </div>
        <div style="margin-top:8px;">
            <button class="button default" id="btn-crear">Crear mascota 🐾</button>
        </div>
        <div class="inline-msg" id="msg-crear"></div>

        <?php else: ?>

        <?php
        $isEgg = !$mascota['eclosionado'];
        /* El huevo no tiene nombre — usar "Huevo" como display fallback
           para que los mensajes no queden truncados ni feos. */
        $displayName = trim((string)($mascota['nombre'] ?? '')) !== '' ? $mascota['nombre'] : 'Huevo';
        if (!$mascota['viva']):
            $deathMsg = $isEgg ? 'El huevo se enfrió y murió...'
                               : htmlspecialchars($displayName).' ha muerto de hambre...';
        ?>
        <div class="dead-banner">💀 <?= $deathMsg ?></div>
        <?php endif; ?>

        <div class="estado-header">
            <!-- Preview: huevo si no ha eclosionado; sprite si sí -->
            <div class="sprite-preview" id="sprite-preview">
                <?php if ($isEgg): ?>
                <span id="egg-preview" style="font-size:80px;line-height:1;">🥚</span>
                <?php else: ?>
                <img id="sprite-img"
                     src="../assets/mascota/skins/<?= htmlspecialchars($currentSkin) ?>/shime1.png"
                     alt="sprite"
                     onerror="this.style.display='none';this.parentNode.innerHTML='<span style=\'font-size:40px\'>🐾</span>'">
                <?php endif; ?>
            </div>

            <div class="estado-info">
                <div class="estado-name" id="display-name">
                    <?= htmlspecialchars($displayName) ?>
                    <?php if ($isEgg): ?><span style="font-size:10px;color:var(--text-muted,#666);">(sin nombre — se elige al eclosionar)</span><?php endif; ?>
                </div>
                <?php if ($isEgg): ?>
                <div class="estado-sub">
                    <?php
                    $secs = (int)$mascota['segundos_para_eclosion'];
                    $days  = (int)floor($secs / 86400);
                    $hours = (int)floor(($secs % 86400) / 3600);
                    $mins  = (int)floor(($secs % 3600) / 60);
                    if ($secs <= 0) {
                        echo '<strong style="color:var(--accent,#080);">¡A punto de eclosionar!</strong>';
                    } else {
                        echo 'Eclosiona en: <strong>';
                        if ($days > 0)  echo $days  . 'd ';
                        if ($hours > 0) echo $hours . 'h ';
                        echo $mins . 'm</strong>';
                    }
                    ?>
                </div>

                <!-- Temperatura: ÚNICA barra del huevo -->
                <div class="stat-row" style="margin-top:10px;">
                    <span class="stat-label">🔥 Temperatura</span>
                    <div class="stat-track">
                        <div class="stat-fill" id="fill-temperatura"
                             style="width:<?= $mascota['temperatura'] ?>%;background:<?= $mascota['temperatura'] < 20 ? '#06f' : ($mascota['temperatura'] < 50 ? '#f93' : '#f60') ?>;">
                        </div>
                    </div>
                    <span class="stat-val" id="val-temperatura"><?= $mascota['temperatura'] ?></span>
                </div>
                <p style="font-size:10px;color:var(--text-muted,#666);margin:6px 0 0;">
                    Si la temperatura llega a 0 el huevo morirá. Dale calor regularmente.
                </p>
                <?php else: ?>
                <div class="estado-sub">
                    Edad: <strong id="display-edad"><?= (int)$mascota['edad'] ?></strong> día<?= $mascota['edad'] !== 1 ? 's' : '' ?>
                </div>

                <!-- Hambre -->
                <div class="stat-row" style="margin-top:10px;">
                    <span class="stat-label">🍖 Hambre</span>
                    <div class="stat-track">
                        <div class="stat-fill" id="fill-hambre"
                             style="width:<?= $mascota['hambre'] ?>%;background:<?= $mascota['hambre'] > 60 ? '#0a0' : ($mascota['hambre'] > 30 ? '#f90' : '#c00') ?>;">
                        </div>
                    </div>
                    <span class="stat-val" id="val-hambre"><?= $mascota['hambre'] ?></span>
                </div>

                <!-- Felicidad -->
                <div class="stat-row">
                    <span class="stat-label">♥ Ánimo</span>
                    <div class="stat-track">
                        <div class="stat-fill" id="fill-felicidad"
                             style="width:<?= $mascota['felicidad'] ?>%;background:#e05;">
                        </div>
                    </div>
                    <span class="stat-val" id="val-felicidad"><?= $mascota['felicidad'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$isEgg): ?>
        <!-- Renombrar (solo cuando ya hay mascota) -->
        <div class="rename-row">
            <label>Nombre:</label>
            <input type="text" id="input-nombre" maxlength="40" value="<?= htmlspecialchars($mascota['nombre']) ?>">
            <button class="button" id="btn-renombrar">Guardar</button>
        </div>
        <?php endif; ?>

        <!-- Acciones: cambian según huevo o mascota -->
        <div class="action-row">
            <?php if (!$mascota['viva']): ?>
            <button class="button default" id="btn-revivir">💊 Revivir</button>
            <?php elseif ($isEgg): ?>
            <button class="button default" id="btn-warm">🔥 Dar calor</button>
            <?php else: ?>
            <button class="button default" id="btn-play"
                <?= $mascota['hambre'] < 15 ? 'disabled title="Demasiada hambre para jugar"' : '' ?>>
                ⚽ Jugar
            </button>
            <?php endif; ?>
        </div>
        <div class="inline-msg" id="msg-accion"></div>

        <!-- ZONA DE PELIGRO / DEV ─────── -->
        <div class="delete-zone">
            <h4>⚠ Zona de testeo</h4>
            <?php if ($isEgg && $mascota['viva']): ?>
            <p>Forzar la eclosión saltándose los 3 días de espera. Te pedirá el nombre.</p>
            <button class="button" id="btn-force-hatch" style="margin-bottom:8px;">🐣 Eclosionar (DEV)</button>
            <?php endif; ?>
            <p>Esta opción borra TODA la mascota y su memoria de forma permanente. Solo úsala para reiniciar pruebas.</p>
            <button class="button btn-danger" id="btn-eliminar">🗑 Eliminar mascota</button>
            <div class="inline-msg" id="msg-eliminar"></div>
        </div>

        <?php endif; /* hasMascota */ ?>
    </div><!-- /tab-estado -->

    <!-- TAB Skins eliminada: la selección de skin de la mascota se
         hace ahora desde la app de Temas → Personalización → Mascotas.
         La skin de la mascota actual queda FIJA en el momento de su
         creación; cambiar la preferencia solo afecta a la próxima
         mascota (tras "Eliminar mascota"). -->

    <!-- ══════════════════════════════════════════════════════════
         TAB: GUSTOS
    ═══════════════════════════════════════════════════════════ -->
    <?php if ($hasMascota && $mascota['eclosionado']):
        /* 3 SLOTS POR LISTA — fijos, siempre visibles, con placeholder
           "???" cuando aún no se han descubierto. Al alimentar, los
           slots se rellenan dinámicamente:
           - Solo se consideran alimentos REVELADOS.
           - Umbral fijo: valor >= 40 → favoritos; valor < 40 → odiados.
           - Favoritos = top 3 con MAYOR valor.
           - Odiados   = top 3 con MENOR valor (los más detestados).
           - Ambos se muestran ordenados DESC (mejor dentro de su grupo
             primero — para odiados eso = menos malo primero). */
        $UMBRAL_FAVORITO = 40;
        $SLOTS           = 3;
        $revelados = array_filter($gustos, fn($g) => !empty($g['revelado']));
        $favoritos = array_filter($revelados, fn($g) => $g['valor'] >= $UMBRAL_FAVORITO);
        $odiados   = array_filter($revelados, fn($g) => $g['valor'] <  $UMBRAL_FAVORITO);
        usort($favoritos, fn($a,$b) => $b['valor'] <=> $a['valor']);
        usort($odiados,   fn($a,$b) => $b['valor'] <=> $a['valor']);
        /* Limitar a 3 slots: favoritos top 3 MAYORES, odiados top 3 MENORES. */
        $favoritos = array_slice(array_values($favoritos), 0, $SLOTS);
        $odiados   = array_slice(array_values($odiados),   -$SLOTS);

        $renderSlot = function(?array $g, int $rank): void {
            $filled = $g !== null;
            ?>
            <div class="mem-card" style="display:flex;align-items:center;gap:10px;<?= $filled ? '' : 'opacity:0.55;' ?>">
                <span style="width:24px;text-align:center;font-weight:bold;color:var(--text-muted,#666);">#<?= $rank ?></span>
                <span style="font-size:28px;line-height:1;<?= $filled ? '' : 'filter:grayscale(1);' ?>"><?= $filled ? $g['emoji'] : '❓' ?></span>
                <span style="flex:1;font-weight:bold;font-size:12px;<?= $filled ? '' : 'color:var(--text-muted,#666);' ?>"><?= $filled ? htmlspecialchars($g['nombre']) : '???' ?></span>
            </div>
            <?php
        };
    ?>
    <div id="tab-gustos" class="tab-panel">
        <p style="margin:0 0 12px;color:var(--text-muted,#666);font-size:11px;">
            Descubre los gustos de tu mascota dándole de comer. Cada
            alimento que pruebe rellenará uno de los slots. Si pruebas
            algo mejor/peor, los slots se reorganizan automáticamente.
        </p>

        <h4 style="margin:0 0 6px;color:var(--accent,#080);font-size:11px;">★ Lo que más le ha gustado</h4>
        <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
            <?php for ($i = 0; $i < $SLOTS; $i++) $renderSlot($favoritos[$i] ?? null, $i + 1); ?>
        </div>

        <h4 style="margin:0 0 6px;color:var(--error-text,#c00);font-size:11px;">✗ Lo que peor le sienta</h4>
        <div style="display:flex;flex-direction:column;gap:6px;">
            <?php for ($i = 0; $i < $SLOTS; $i++) $renderSlot($odiados[$i] ?? null, $i + 1); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════
         TAB: MEMORIA
    ═══════════════════════════════════════════════════════════ -->
    <div id="tab-memoria" class="tab-panel">
        <p style="margin:0 0 8px;color:var(--text-muted,#666);">
            Lo que tu mascota sabe de ti:
        </p>
        <div class="mem-grid" id="mem-grid">
            <?php
            $claveLabels = [
                'comida_favorita'  => '🍕 Comida favorita',
                'color_favorito'   => '🎨 Color favorito',
                'cancion_favorita' => '🎵 Canción favorita',
                'juego_favorito'   => '🎮 Juego favorito',
                'mascota_favorita' => '🐾 Mascota favorita',
            ];
            foreach ($claveLabels as $clave => $etiqueta):
                $val = $memoria[$clave] ?? null;
            ?>
            <div class="mem-card" id="memcard-<?= $clave ?>">
                <div class="mem-key">
                    <?= htmlspecialchars($etiqueta) ?>
                    <span class="mem-edit" data-clave="<?= $clave ?>" title="Editar">✏️</span>
                </div>
                <div class="mem-val" id="memval-<?= $clave ?>">
                    <?php if ($val): ?>
                        <?= htmlspecialchars($val) ?>
                    <?php else: ?>
                        <span class="mem-empty">Sin respuesta</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="inline-msg" id="msg-memoria"></div>
    </div>

</div><!-- /mascota-app -->

<script>
(function () {
    'use strict';

    var API    = '../assets/mascota/api.php';
    var hasMascota = <?= $hasMascota ? 'true' : 'false' ?>;
    var currentSkin = <?= json_encode($currentSkin) ?>;

    /* ── Helpers ──────────────────────────────────────────────── */
    function apiFetch(action, data, cb) {
        var url  = API + '?action=' + encodeURIComponent(action);
        var opts = data
            ? { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) }
            : { method:'GET' };
        fetch(url, opts)
            .then(function(r){ return r.json(); })
            .then(function(d){ cb(null, d); })
            .catch(function(e){ cb(e, null); });
    }

    function showMsg(id, text, type) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = text;
        el.className   = 'inline-msg ' + (type || 'ok');
        el.style.display = 'block';
        setTimeout(function(){ el.style.display = 'none'; }, 3000);
    }

    function setBarValue(fillId, valId, value) {
        var fill = document.getElementById(fillId);
        var val  = document.getElementById(valId);
        if (fill) {
            fill.style.width = value + '%';
            if (fillId === 'fill-hambre') {
                fill.style.background = value > 60 ? '#0a0' : value > 30 ? '#f90' : '#c00';
            }
        }
        if (val) val.textContent = value;
    }

    /* ── Tabs ─────────────────────────────────────────────────── */
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
            btn.classList.add('active');
            var panel = document.getElementById('tab-' + btn.dataset.tab);
            if (panel) panel.classList.add('active');
        });
    });

    /* ══════════════════════════════════════════════════════════
       TAB ESTADO
    ═══════════════════════════════════════════════════════════ */

    /* Crear mascota (primera vez) */
    var btnCrear = document.getElementById('btn-crear');
    if (btnCrear) {
        btnCrear.addEventListener('click', function() {
            var nombre = (document.getElementById('new-name').value || '').trim() || 'Meloncio';
            btnCrear.disabled = true;
            apiFetch('get', null, function(err, d) {
                /* get ya crea la mascota si no existe */
                if (!err && d && d.ok) {
                    /* Si el nombre difiere del default, renombramos */
                    if (nombre !== 'Meloncio') {
                        apiFetch('rename', { nombre: nombre }, function(){});
                    }
                    window.location.reload();
                } else {
                    showMsg('msg-crear', 'Error al crear la mascota', 'error');
                    btnCrear.disabled = false;
                }
            });
        });
    }

    /* Renombrar */
    var btnRenombrar = document.getElementById('btn-renombrar');
    if (btnRenombrar) {
        btnRenombrar.addEventListener('click', function() {
            var nombre = (document.getElementById('input-nombre').value || '').trim();
            if (!nombre) return;
            apiFetch('rename', { nombre: nombre }, function(err, d) {
                if (!err && d && d.ok) {
                    var dn = document.getElementById('display-name');
                    if (dn) dn.textContent = nombre;
                    showMsg('msg-accion', 'Nombre actualizado ✓', 'ok');
                } else {
                    showMsg('msg-accion', 'Error al renombrar', 'error');
                }
            });
        });
    }

    /* DEV: forzar eclosión saltándose los 3 días. Pide nombre y, tras
       OK, llama a hatch con dev=true. Recarga el iframe en éxito para
       que la UI cambie de huevo a mascota. */
    var btnForceHatch = document.getElementById('btn-force-hatch');
    if (btnForceHatch) {
        btnForceHatch.addEventListener('click', function() {
            var nombre = (prompt('¿Qué nombre le pones a tu mascota?') || '').trim();
            if (!nombre) return;
            apiFetch('hatch', { nombre: nombre, dev: true }, function(err, d) {
                if (!err && d && d.ok) {
                    /* Si el escritorio padre tiene el huevo en pantalla,
                       hacer la transición visual ahí también. */
                    if (window.parent && window.parent.MascotaEngine
                        && window.parent.MascotaEngine.getState) {
                        var st = window.parent.MascotaEngine.getState();
                        if (st && st.mascota) {
                            st.mascota.eclosionado = true;
                            st.mascota.nombre = nombre;
                            st.mascota.hambre = 80;
                            st.mascota.felicidad = 80;
                        }
                    }
                    showMsg('msg-eliminar', '¡' + nombre + ' ha nacido! Recargando…', 'ok');
                    setTimeout(function() { window.location.reload(); }, 700);
                } else {
                    showMsg('msg-eliminar', (d && d.error) ? d.error : 'Error', 'error');
                }
            });
        });
    }

    /* Dar calor al huevo. Solo aparece cuando $isEgg en SSR. */
    var btnWarm = document.getElementById('btn-warm');
    if (btnWarm) {
        btnWarm.addEventListener('click', function() {
            apiFetch('warm', {}, function(err, d) {
                if (!err && d && d.ok) {
                    setBarValue('fill-temperatura', 'val-temperatura', d.temperatura);
                    var msg = d.temperatura > 90 ? '¡Cálido y feliz! 🥰'
                            : d.temperatura > 60 ? '¡Calor entregado! 🔥'
                            :                       '¡Necesita más calor!';
                    showMsg('msg-accion', msg, 'ok');
                    if (window.parent && window.parent.MascotaEngine) {
                        var s = window.parent.MascotaEngine.getState();
                        if (s && s.mascota) {
                            s.mascota.temperatura = d.temperatura;
                            /* Forzar refresh del HUD del engine. */
                            if (typeof window.parent.MascotaEngine.showBubble === 'function') {
                                window.parent.MascotaEngine.showBubble('🔥');
                            }
                        }
                    }
                } else {
                    showMsg('msg-accion', (d && d.error) ? d.error : 'Error', 'error');
                }
            });
        });
    }

    /* Alimentar: el botón se eliminó de esta ventana — la alimentación
       se hace desde el picker del escritorio (☰ → Alimentar). */

    /* Jugar */
    var btnPlay = document.getElementById('btn-play');
    if (btnPlay) {
        btnPlay.addEventListener('click', function() {
            apiFetch('play', {}, function(err, d) {
                if (!err && d && d.ok) {
                    setBarValue('fill-hambre',    'val-hambre',    d.hambre);
                    setBarValue('fill-felicidad', 'val-felicidad', d.felicidad);
                    showMsg('msg-accion', '¡Jugaste con tu mascota! ⚽', 'ok');
                    if (window.parent && window.parent.MascotaEngine) {
                        window.parent.MascotaEngine.showBubble('¡Yuhu! ⚽');
                    }
                    /* Deshabilitar si hambre baja de 15 */
                    if (d.hambre < 15) btnPlay.disabled = true;
                } else {
                    showMsg('msg-accion', (d && d.error) ? d.error : 'Error', 'error');
                }
            });
        });
    }

    /* Revivir */
    var btnRevivir = document.getElementById('btn-revivir');
    if (btnRevivir) {
        btnRevivir.addEventListener('click', function() {
            if (!confirm('¿Revivir a tu mascota? Volverá con hambre y ánimo bajos.')) return;
            apiFetch('reset-death', {}, function(err, d) {
                if (!err && d && d.ok) {
                    window.location.reload();
                } else {
                    showMsg('msg-accion', 'No se pudo revivir', 'error');
                }
            });
        });
    }

    /* Eliminar (testeo) — borra mascota + memoria de BD. Tras éxito
       despawnea el sprite en el escritorio padre y recarga el iframe
       para volver al estado "primera vez" (creador de mascota). */
    var btnEliminar = document.getElementById('btn-eliminar');
    if (btnEliminar) {
        btnEliminar.addEventListener('click', function() {
            if (!confirm('¿Eliminar la mascota PARA SIEMPRE?\n\nSe borrará TODO: hambre, felicidad, edad, memoria, etc.\n\nEsta opción es solo para testeo.')) return;
            apiFetch('delete', {}, function(err, d) {
                if (!err && d && d.ok) {
                    showMsg('msg-eliminar', 'Mascota eliminada. Recargando…', 'ok');
                    /* Si el escritorio padre tiene la mascota spawneada,
                       la quitamos antes de recargar la ventana. */
                    if (window.parent && window.parent.MascotaEngine
                        && window.parent.MascotaEngine.despawn) {
                        try { window.parent.MascotaEngine.despawn(); } catch(_){}
                    }
                    setTimeout(function() { window.location.reload(); }, 700);
                } else {
                    showMsg('msg-eliminar', (d && d.error) ? d.error : 'Error al eliminar', 'error');
                }
            });
        });
    }

    /* ══════════════════════════════════════════════════════════
       TAB SKINS — REMOVIDO.
       La selección de skin vive ahora en Temas → Personalización →
       Mascotas (assets/personalize/api.php?action=set-active-mascot).
       Aquí solo dejamos esta nota; el endpoint `set-skin` queda en
       api.php pero el frontend ya no lo llama.
    ═══════════════════════════════════════════════════════════ */

    /* ══════════════════════════════════════════════════════════
       TAB MEMORIA
    ═══════════════════════════════════════════════════════════ */
    var claveLabels = {
        comida_favorita:  '🍕 Comida favorita',
        color_favorito:   '🎨 Color favorito',
        cancion_favorita: '🎵 Canción favorita',
        juego_favorito:   '🎮 Juego favorito',
        mascota_favorita: '🐾 Mascota favorita',
    };

    document.querySelectorAll('.mem-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var clave  = btn.dataset.clave;
            var valEl  = document.getElementById('memval-' + clave);
            var actual = valEl ? valEl.textContent.trim() : '';
            if (actual === 'Sin respuesta') actual = '';

            var nuevo = prompt(claveLabels[clave] || clave, actual);
            if (nuevo === null) return;
            nuevo = nuevo.trim();
            if (!nuevo) return;

            apiFetch('save-memoria', { clave: clave, valor: nuevo }, function(err, d) {
                if (!err && d && d.ok) {
                    if (valEl) valEl.innerHTML = escapeHtml(nuevo);
                    showMsg('msg-memoria', 'Guardado ✓', 'ok');
                } else {
                    showMsg('msg-memoria', 'Error al guardar', 'error');
                }
            });
        });
    });

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    /* ── Sincronización con el engine cada 30 s ─────────────────── */
    if (hasMascota) {
        setInterval(function() {
            apiFetch('get', null, function(err, d) {
                if (!err && d && d.ok && d.mascota) {
                    var m = d.mascota;
                    setBarValue('fill-hambre',    'val-hambre',    m.hambre);
                    setBarValue('fill-felicidad', 'val-felicidad', m.felicidad);
                    var edadEl = document.getElementById('display-edad');
                    if (edadEl) edadEl.textContent = m.edad;
                }
            });
        }, 30000);
    }

})();
</script>
</body>
</html>