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
    if ($row['viva'] && $diffMins > 0) {
        $row['hambre']    = max(0, (int)$row['hambre']    - (int)floor($diffMins / 30));
        $row['felicidad'] = max(0, (int)$row['felicidad'] - (int)floor($diffMins / 60));
        if ($row['hambre'] === 0 && $diffSecs >= 86400) $row['viva'] = 0;
    }
    $row['viva']      = (bool)$row['viva'];
    $row['hambre']    = (int)$row['hambre'];
    $row['felicidad'] = (int)$row['felicidad'];
    $row['edad']      = (int)$row['edad'];
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

/* Skins disponibles */
$skins = [
    'meloncio'  => ['label' => 'Meloncio',  'emoji' => '🍈'],
    'helldiver' => ['label' => 'Helldiver', 'emoji' => '🪖'],
    'v1'        => ['label' => 'V1',        'emoji' => '🤖'],
];
$currentSkin = $hasMascota ? ($mascota['skin'] ?? 'meloncio') : 'meloncio';
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
        /* ── Layout ─────────────────────────────────────────────── */
        html, body {
            margin: 0; padding: 0;
            height: 100%; overflow: hidden;
            background: var(--win-body-bg, #c0c0c0);
            font-size: 11px;
        }
        #mascota-app {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* ── Tabs ───────────────────────────────────────────────── */
        .tab-bar {
            display: flex;
            gap: 2px;
            padding: 4px 4px 0;
            background: var(--win-bg, #c0c0c0);
            border-bottom: 2px solid var(--border-strong, #808080);
            flex-shrink: 0;
        }
        .tab-btn {
            padding: 3px 12px 4px;
            font-size: 11px;
            border: 2px solid;
            border-color: var(--bezel-light1,#fff) var(--bezel-dark2,#404040) var(--bezel-dark2,#404040) var(--bezel-light1,#fff);
            background: var(--win-bg, #c0c0c0);
            cursor: pointer;
            position: relative;
            bottom: -2px;
            color: var(--text, #000);
        }
        .tab-btn.active {
            border-bottom-color: var(--win-body-bg, #c0c0c0);
            background: var(--win-body-bg, #c0c0c0);
            font-weight: bold;
            z-index: 1;
        }
        .tab-panel { display: none; flex: 1; overflow-y: auto; padding: 10px; }
        .tab-panel.active { display: block; }

        /* ── Stat bars ──────────────────────────────────────────── */
        .stat-row {
            display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
        }
        .stat-label { width: 72px; flex-shrink: 0; }
        .stat-track {
            flex: 1; height: 14px;
            background: var(--inset-bg, #808080);
            border: 2px inset var(--border, #808080);
            position: relative; overflow: hidden;
        }
        .stat-fill {
            height: 100%; transition: width .4s ease;
        }
        .stat-val { width: 32px; text-align: right; flex-shrink: 0; }

        /* ── Skin selector ──────────────────────────────────────── */
        .skin-grid {
            display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;
        }
        .skin-card {
            border: 2px solid var(--border, #808080);
            padding: 8px 14px; cursor: pointer; text-align: center;
            background: var(--win-bg, #c0c0c0);
            transition: border-color .15s;
        }
        .skin-card:hover { border-color: var(--accent, #000080); }
        .skin-card.selected {
            border-color: var(--accent, #000080);
            background: var(--selection-bg, #000080);
            color: var(--selection-text, #fff);
        }
        .skin-card .skin-emoji { font-size: 24px; display: block; }
        .skin-card .skin-name  { font-size: 10px; margin-top: 3px; }

        /* ── Memoria grid ───────────────────────────────────────── */
        .mem-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 6px; }
        .mem-card {
            border: 2px inset var(--border, #808080);
            background: var(--inset-bg, #fff);
            padding: 6px 8px;
        }
        .mem-card .mem-key  { color: var(--text-muted, #666); font-size: 10px; margin-bottom: 2px; }
        .mem-card .mem-val  { color: var(--text, #000); font-size: 11px; word-break: break-word; }
        .mem-card .mem-edit { float: right; font-size: 10px; cursor: pointer; color: var(--link-text,#00f); }
        .mem-empty { color: var(--text-muted,#666); font-style: italic; font-size: 11px; }

        /* ── Sprite preview ─────────────────────────────────────── */
        .sprite-preview {
            width: 128px; height: 128px;
            image-rendering: pixelated;
            border: 2px inset var(--border,#808080);
            background: var(--inset-bg, #c0c0c0);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
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
            background: #800; color: #fff; padding: 6px 10px;
            border: 2px solid #400; margin-bottom: 8px; text-align: center;
        }
        .action-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }

        /* ── Rename input ────────────────────────────────────────── */
        .rename-row { display: flex; gap: 6px; align-items: center; margin-top: 8px; }
        .rename-row input { flex: 1; }

        /* ── Scrollbar win98 ─────────────────────────────────────── */
        ::-webkit-scrollbar       { width: 16px; }
        ::-webkit-scrollbar-track { background: var(--win-bg,#c0c0c0); }
        ::-webkit-scrollbar-thumb {
            background: var(--win-bg,#c0c0c0);
            border: 2px solid;
            border-color: var(--bezel-light1,#fff) var(--bezel-dark2,#404040) var(--bezel-dark2,#404040) var(--bezel-light1,#fff);
        }

        /* ── Notifications inline ───────────────────────────────── */
        .inline-msg {
            padding: 4px 8px; margin-top: 6px; font-size: 11px;
            border: 1px solid; display: none;
        }
        .inline-msg.ok    { border-color: #080; background: #dfd; color: #040; }
        .inline-msg.error { border-color: #800; background: #fdd; color: #400; }
    </style>
</head>
<body class="<?= htmlspecialchars($themeClass) ?>">
<div id="mascota-app">

    <!-- ── Tab bar ─────────────────────────────────────────────── -->
    <div class="tab-bar">
        <button class="tab-btn active" data-tab="estado">🐾 Estado</button>
        <button class="tab-btn"        data-tab="skins">🎨 Skins</button>
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

        <?php if (!$mascota['viva']): ?>
        <div class="dead-banner">💀 <?= htmlspecialchars($mascota['nombre']) ?> ha muerto de hambre...</div>
        <?php endif; ?>

        <div class="estado-header">
            <!-- Sprite preview: frame idle de la skin actual -->
            <div class="sprite-preview" id="sprite-preview">
                <img id="sprite-img"
                     src="../assets/mascota/skins/<?= htmlspecialchars($currentSkin) ?>/shime1.png"
                     alt="sprite"
                     onerror="this.style.display='none';this.parentNode.innerHTML='<span style=\'font-size:40px\'>🐾</span>'">
            </div>

            <div class="estado-info">
                <div class="estado-name" id="display-name"><?= htmlspecialchars($mascota['nombre']) ?></div>
                <div class="estado-sub">
                    Skin: <strong><?= htmlspecialchars($skins[$currentSkin]['label'] ?? $currentSkin) ?></strong>
                    &nbsp;·&nbsp; Edad: <strong id="display-edad"><?= (int)$mascota['edad'] ?></strong> día<?= $mascota['edad'] !== 1 ? 's' : '' ?>
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
            </div>
        </div>

        <!-- Renombrar -->
        <div class="rename-row">
            <label>Nombre:</label>
            <input type="text" id="input-nombre" maxlength="40" value="<?= htmlspecialchars($mascota['nombre']) ?>">
            <button class="button" id="btn-renombrar">Guardar</button>
        </div>

        <!-- Acciones -->
        <div class="action-row">
            <?php if ($mascota['viva']): ?>
            <button class="button default" id="btn-feed">🍕 Alimentar</button>
            <button class="button" id="btn-play"
                <?= $mascota['hambre'] < 15 ? 'disabled title="Demasiada hambre para jugar"' : '' ?>>
                ⚽ Jugar
            </button>
            <?php else: ?>
            <button class="button default" id="btn-revivir">💊 Revivir</button>
            <?php endif; ?>
        </div>
        <div class="inline-msg" id="msg-accion"></div>

        <?php endif; /* hasMascota */ ?>
    </div><!-- /tab-estado -->

    <!-- ══════════════════════════════════════════════════════════
         TAB: SKINS
    ═══════════════════════════════════════════════════════════ -->
    <div id="tab-skins" class="tab-panel">
        <p style="margin:0 0 8px;color:var(--text-muted,#666);">Elige el aspecto de tu mascota:</p>
        <div class="skin-grid" id="skin-grid">
            <?php foreach ($skins as $key => $data): ?>
            <div class="skin-card <?= $key === $currentSkin ? 'selected' : '' ?>"
                 data-skin="<?= $key ?>">
                <span class="skin-emoji"><?= $data['emoji'] ?></span>
                <div class="skin-name"><?= htmlspecialchars($data['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="inline-msg" id="msg-skin"></div>
        <p style="margin:12px 0 0;font-size:10px;color:var(--text-muted,#666);">
            Más skins se añadirán en futuras actualizaciones.
        </p>
    </div>

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

    /* Alimentar */
    var btnFeed = document.getElementById('btn-feed');
    if (btnFeed) {
        btnFeed.addEventListener('click', function() {
            var comida = (prompt('¿Qué le das de comer?') || '').trim() || 'comida';
            apiFetch('feed', { comida: comida }, function(err, d) {
                if (!err && d && d.ok) {
                    setBarValue('fill-hambre',    'val-hambre',    d.hambre);
                    setBarValue('fill-felicidad', 'val-felicidad', d.felicidad);
                    var msg = d.bonus_fav ? '¡Le encantó! +bonus ★' : '¡Comida entregada!';
                    showMsg('msg-accion', msg, 'ok');
                    /* Notificar al engine.js del escritorio si está activo */
                    if (window.parent && window.parent.MascotaEngine) {
                        window.parent.MascotaEngine.showBubble('¡Mmm! 😋');
                    }
                } else {
                    showMsg('msg-accion', (d && d.error) ? d.error : 'Error', 'error');
                }
            });
        });
    }

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

    /* ══════════════════════════════════════════════════════════
       TAB SKINS
    ═══════════════════════════════════════════════════════════ */
    document.querySelectorAll('.skin-card').forEach(function(card) {
        card.addEventListener('click', function() {
            var skin = card.dataset.skin;
            if (skin === currentSkin) return;

            apiFetch('set-skin', { skin: skin }, function(err, d) {
                if (!err && d && d.ok) {
                    currentSkin = skin;
                    document.querySelectorAll('.skin-card').forEach(function(c){
                        c.classList.toggle('selected', c.dataset.skin === skin);
                    });
                    /* Actualizar sprite preview */
                    var img = document.getElementById('sprite-img');
                    if (img) {
                        img.style.display = '';
                        img.src = '../assets/mascota/skins/' + skin + '/shime1.png';
                    }
                    showMsg('msg-skin', 'Skin cambiada a ' + skin + ' ✓', 'ok');
                    /* Notificar al engine */
                    if (window.parent && window.parent.MascotaEngine) {
                        var s = window.parent.MascotaEngine.getState();
                        if (s) {
                            s.skin = skin;
                            /* Forzar recarga de frames en el engine */
                            if (typeof window.parent.MascotaEngine.reloadSkin === 'function') {
                                window.parent.MascotaEngine.reloadSkin(skin);
                            }
                        }
                    }
                } else {
                    showMsg('msg-skin', 'Error al cambiar skin', 'error');
                }
            });
        });
    });

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