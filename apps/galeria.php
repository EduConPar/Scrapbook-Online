<?php
/* No cachear este iframe: la app evoluciona rápido y el iframe del desktop
   solo se inicializa una vez por sesión. Sin estos headers, los cambios de
   JS se quedan invisibles hasta un hard refresh manual. */
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();
require_once dirname(__DIR__) . '/assets/config.php';
require_once dirname(__DIR__) . '/assets/themes/theme-helpers.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: ../index.php');
    exit;
}
$userLabel = $loginUsers[$userKey]['label'];

/* Tema activo del usuario (igual que en temas.php) */
refreshActiveThemeCss($userKey, $userLabel);
$_userThemes      = loadUserThemes($userKey);
$activeTheme      = !empty($_userThemes['active']) ? sanitizeThemeName($_userThemes['active']) : '';
$activeThemeClass = '';
$activeThemeCss   = '';
if ($activeTheme !== '' && isset(((array)$_userThemes['themes'])[$activeTheme])) {
    $activeThemeClass = themeCssClassName($activeTheme, $userLabel);
    $activeThemeCss   = '../' . themeCssRelPath($activeTheme, $userLabel);
    if (!file_exists(dirname(__DIR__) . '/' . themeCssRelPath($activeTheme, $userLabel))) $activeThemeCss = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería</title>
    <link rel="stylesheet" href="../assets/css/98.css">
    <link rel="stylesheet" href="../assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/galeria.css?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/galeria.css'); ?>">
    <?php if ($activeThemeCss): ?>
    <link rel="stylesheet" id="active-theme-link" href="<?php echo htmlspecialchars($activeThemeCss); ?>">
    <?php endif; ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="<?php echo htmlspecialchars($activeThemeClass); ?>">

<!-- Vista DESCONECTADA -->
<div id="gal-connect-view">
    <div class="gal-connect-box window">
        <div class="title-bar"><div class="title-bar-text">🖼 Galería</div></div>
        <div class="window-body" style="text-align:center;padding:24px;">
            <div style="font-size:48px;line-height:1;">🖼️</div>
            <h2 style="margin:10px 0 4px;">Galería</h2>
            <p style="margin:0 0 14px;font-size:11px;color:var(--text-muted);">
                Conecta tu Google Drive para guardar y ver tus imágenes en la nube.
            </p>
            <button class="button default" id="gal-connect-btn">☁ Conectar con Google Drive</button>
            <p id="gal-connect-status" style="margin:10px 0 0;font-size:10px;color:var(--text-faint);min-height:13px;"></p>
        </div>
    </div>
</div>

<!-- Vista CONECTADA -->
<div id="gal-main" style="display:none;">
    <!-- Sidebar -->
    <aside id="gal-sidebar">
        <div class="gal-side-head">🔍 Buscar</div>
        <div class="gal-side-pad">
            <input type="text" id="gal-search" placeholder="Nombre de archivo...">
        </div>

        <div class="gal-side-head">🏷 Etiquetas</div>
        <div id="gal-tag-list">
            <div class="gal-empty" id="gal-tag-empty">Sin etiquetas todavía.</div>
        </div>

        <div id="gal-sidebar-footer">
            <button class="button" id="gal-clear-filters">Limpiar filtros</button>
            <button class="button" id="gal-discord-settings" title="Webhook de Discord">⚙ Discord</button>
            <button class="button" id="gal-disconnect" title="Cerrar sesión de Drive">🔌 Desconectar</button>
        </div>
    </aside>

    <!-- Área principal -->
    <main id="gal-main-area">
        <div id="gal-toolbar">
            <button class="button" id="gal-upload">⬆ Subir imagen</button>
            <span id="gal-status"></span>
            <span id="gal-count"></span>
            <button class="button" id="gal-refresh" title="Recargar">↻</button>
        </div>
        <div id="gal-grid"></div>
        <div id="gal-grid-empty" style="display:none;">
            <p>No hay imágenes en la galería todavía.</p>
            <p><small>Pulsa <strong>⬆ Subir imagen</strong> para empezar.</small></p>
        </div>
    </main>
</div>

<!-- Diálogo: SUBIR imagen -->
<div class="window gal-dialog" id="gal-upload-dialog" style="display:none;">
    <div class="title-bar">
        <div class="title-bar-text">⬆ Subir imagen</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="gal-up-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label>Archivo</label>
            <div class="field-row" style="gap:4px;">
                <input type="text" id="gal-up-filechosen" readonly placeholder="Sin archivo seleccionado" style="flex:1;min-width:0;cursor:default;">
                <button class="button" id="gal-up-browse" style="min-width:70px;flex-shrink:0;">Examinar...</button>
            </div>
        </div>
        <div class="field-row-stacked">
            <label for="gal-up-name">Nombre <small style="color:var(--text-muted);">(sin extensión)</small></label>
            <input type="text" id="gal-up-name" maxlength="120" placeholder="MiFoto">
        </div>
        <div class="field-row-stacked">
            <label for="gal-up-tags">Etiquetas <small style="color:var(--text-muted);">(con #, separadas por espacios)</small></label>
            <input type="text" id="gal-up-tags" placeholder="#vacaciones #playa #2025">
            <div id="gal-up-tag-chips"></div>
        </div>
        <p id="gal-up-status" style="font-size:11px;margin:6px 0 0;min-height:14px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
            <button class="button" id="gal-up-cancel">Cancelar</button>
            <button class="button default" id="gal-up-submit">Subir</button>
        </div>
    </div>
</div>

<!-- Diálogo: CONFIRMAR borrado -->
<div class="window gal-dialog" id="gal-confirm-dialog" style="display:none;">
    <div class="title-bar">
        <div class="title-bar-text">Confirmar</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="gal-cf-x"></button>
        </div>
    </div>
    <div class="window-body">
        <div style="display:flex;gap:10px;align-items:flex-start;">
            <div class="w98-icon-question"></div>
            <div id="gal-cf-text" style="font-size:11px;line-height:1.4;">¿Continuar?</div>
        </div>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:10px;">
            <button class="button" id="gal-cf-no">No</button>
            <button class="button default" id="gal-cf-yes">Sí</button>
        </div>
    </div>
</div>

<!-- Diálogo: EDITAR (renombrar + etiquetas) -->
<div class="window gal-dialog" id="gal-edit-dialog" style="display:none;">
    <div class="title-bar">
        <div class="title-bar-text">✏ Editar</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="gal-ed-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label for="gal-ed-name">Nombre <small style="color:var(--text-muted);">(sin extensión)</small></label>
            <input type="text" id="gal-ed-name" maxlength="120">
        </div>
        <div class="field-row-stacked">
            <label for="gal-ed-tags">Etiquetas <small style="color:var(--text-muted);">(con #, separadas por espacios)</small></label>
            <input type="text" id="gal-ed-tags" placeholder="#vacaciones #playa">
            <div id="gal-ed-tag-chips"></div>
        </div>
        <p id="gal-ed-status" style="font-size:11px;margin:6px 0 0;min-height:14px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
            <button class="button" id="gal-ed-cancel">Cancelar</button>
            <button class="button default" id="gal-ed-submit">Guardar</button>
        </div>
    </div>
</div>

<!-- Diálogo: PUBLICAR en perfil -->
<div class="window gal-dialog" id="gal-publish-dialog" style="display:none;">
    <div class="title-bar">
        <div class="title-bar-text">📤 Publicar en perfil</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="gal-pub-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div id="gal-pub-preview"></div>
        <div class="field-row-stacked" style="margin-top:6px;">
            <label for="gal-pub-text">Texto adjunto <small style="color:var(--text-muted);">(opcional, máx 1000)</small></label>
            <textarea id="gal-pub-text" maxlength="1000" rows="3" placeholder="Escribe algo sobre la imagen…" style="resize:vertical;min-height:60px;"></textarea>
        </div>
        <p id="gal-pub-status" style="font-size:11px;margin:6px 0 0;min-height:14px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
            <button class="button" id="gal-pub-cancel">Cancelar</button>
            <button class="button default" id="gal-pub-submit">Publicar</button>
        </div>
    </div>
</div>

<!-- Diálogo: PUBLICAR en Discord -->
<div class="window gal-dialog" id="gal-discord-dialog" style="display:none;">
    <div class="title-bar">
        <div class="title-bar-text">🟣 Publicar en Discord</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="gal-disc-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div id="gal-disc-preview"></div>
        <div class="field-row-stacked" style="margin-top:6px;">
            <label for="gal-disc-text">Mensaje <small style="color:var(--text-muted);">(opcional, máx 1900)</small></label>
            <textarea id="gal-disc-text" maxlength="1900" rows="3" placeholder="Escribe algo (opcional)…" style="resize:vertical;min-height:60px;"></textarea>
        </div>
        <p id="gal-disc-status" style="font-size:11px;margin:6px 0 0;min-height:14px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
            <button class="button" id="gal-disc-cancel">Cancelar</button>
            <button class="button default" id="gal-disc-submit">Publicar</button>
        </div>
    </div>
</div>

<!-- Diálogo: AJUSTES de Discord (webhook URL) -->
<div class="window gal-dialog" id="gal-discord-settings-dialog" style="display:none;">
    <div class="title-bar">
        <div class="title-bar-text">⚙ Webhook de Discord</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="gal-ds-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p style="font-size:11px;margin:0 0 8px;line-height:1.4;color:var(--text-muted);">
            En tu servidor de Discord: <strong>Configuración del canal → Integraciones → Webhooks → Crear</strong>.<br>
            Copia la URL completa y pégala aquí. Déjalo vacío para desconectar.
        </p>
        <div class="field-row-stacked">
            <label for="gal-ds-url">URL del webhook</label>
            <input type="text" id="gal-ds-url" maxlength="500" placeholder="https://discord.com/api/webhooks/...">
        </div>
        <p id="gal-ds-status" style="font-size:11px;margin:6px 0 0;min-height:14px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
            <button class="button" id="gal-ds-cancel">Cancelar</button>
            <button class="button default" id="gal-ds-submit">Guardar</button>
        </div>
    </div>
</div>

<!-- Menú contextual -->
<div id="gal-ctx-menu" data-no-auto-z="" style="display:none;">
    <div class="gal-ctx-opt" data-act="preview">🖼 Ver</div>
    <div class="gal-ctx-opt" data-act="download">📥 Descargar</div>
    <div class="gal-ctx-opt" data-act="publish">📤 Publicar en perfil</div>
    <div class="gal-ctx-opt" data-act="discord">🟣 Publicar en Discord</div>
    <div class="gal-ctx-opt" data-act="edit">✏ Renombrar / etiquetas</div>
    <div class="gal-ctx-opt gal-ctx-danger" data-act="delete">🗑 Eliminar</div>
</div>

<!-- Preview a pantalla completa -->
<div id="gal-preview" style="display:none;">
    <div id="gal-preview-inner">
        <button id="gal-preview-close" title="Cerrar (Esc)">×</button>
        <img id="gal-preview-img" src="" alt="">
        <div id="gal-preview-meta">
            <div id="gal-preview-name"></div>
            <div id="gal-preview-tags"></div>
        </div>
    </div>
</div>

<input type="file" id="gal-file-input" accept="image/*,.csp,.psd,.kra" style="display:none;">

<script>
/* ─────────────────────────────────────────────────────────
   GALERÍA — Google Drive client-side (mirror del flujo de dnd.php)
   - Tokens SOLO en el navegador (localStorage).
   - Etiquetas guardadas en appProperties.tags (CSV) del fichero en Drive.
   ───────────────────────────────────────────────────────── */
var GOOGLE_CLIENT_ID = <?php echo json_encode(GOOGLE_CLIENT_ID); ?>;
var GDRIVE_SCOPE     = 'https://www.googleapis.com/auth/drive.file';
var GDRIVE_FOLDER    = 'Scrapbook Melon - Galería';

var DRIVE_TOKEN_KEY     = 'galeria_drive_token';
var DRIVE_EVER_AUTH_KEY = 'galeria_drive_ever_auth';

var _tokenClient = null;
var _driveToken  = null;
var _silentRefreshTimer    = null;
var _silentRefreshInFlight = false;
var _pendingDriveCb        = null;

/* Restaurar token de localStorage */
(function tryRestoreToken() {
    try {
        var s = localStorage.getItem(DRIVE_TOKEN_KEY);
        if (!s) return;
        var t = JSON.parse(s);
        if (t && t.expires_at > Date.now() + 30000) _driveToken = t;
    } catch (e) {}
})();

function _initTokenClient() {
    if (_tokenClient) return _tokenClient;
    if (typeof google === 'undefined' || !google.accounts || !google.accounts.oauth2) return null;
    _tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: GOOGLE_CLIENT_ID,
        scope:     GDRIVE_SCOPE,
        callback: function(resp) {
            if (resp.error) {
                if (!_silentRefreshInFlight) setStatus('✗ Drive: ' + resp.error, true);
                _silentRefreshInFlight = false;
                _pendingDriveCb = null;
                return;
            }
            _driveToken = {
                access_token: resp.access_token,
                expires_at:   Date.now() + (resp.expires_in * 1000) - 60000
            };
            localStorage.setItem(DRIVE_TOKEN_KEY, JSON.stringify(_driveToken));
            localStorage.setItem(DRIVE_EVER_AUTH_KEY, '1');
            scheduleSilentRefresh();
            _silentRefreshInFlight = false;
            if (_pendingDriveCb) { var fn = _pendingDriveCb; _pendingDriveCb = null; fn(); }
            showConnected();
        }
    });
    return _tokenClient;
}

function scheduleSilentRefresh() {
    clearTimeout(_silentRefreshTimer);
    if (!_driveToken) return;
    var ms = _driveToken.expires_at - Date.now() - 5 * 60 * 1000;
    if (ms < 1000) ms = 1000;
    _silentRefreshTimer = setTimeout(silentRefresh, ms);
}

function silentRefresh() {
    var c = _initTokenClient();
    if (!c) return;
    _silentRefreshInFlight = true;
    try { c.requestAccessToken({ prompt: '' }); }
    catch (e) { _silentRefreshInFlight = false; }
}

function tryAutoConnectDrive() {
    if (!localStorage.getItem(DRIVE_EVER_AUTH_KEY)) return;
    if (_driveToken && _driveToken.expires_at > Date.now() + 30000) {
        scheduleSilentRefresh();
        showConnected();
        return;
    }
    var attempts = 0;
    var iv = setInterval(function() {
        attempts++;
        if (typeof google !== 'undefined' && google.accounts && google.accounts.oauth2) {
            clearInterval(iv);
            silentRefresh();
        } else if (attempts > 60) {
            clearInterval(iv);
        }
    }, 250);
}

function ensureDriveAuth(fn) {
    if (_driveToken && _driveToken.expires_at > Date.now() + 30000) { fn(); return; }
    _pendingDriveCb = fn;
    var c = _initTokenClient();
    if (!c) {
        var attempts = 0;
        var iv = setInterval(function() {
            attempts++;
            c = _initTokenClient();
            if (c) { clearInterval(iv); c.requestAccessToken({ prompt: '' }); }
            else if (attempts > 40) { clearInterval(iv); _pendingDriveCb = null; setStatus('No se pudo cargar Google Identity.', true); }
        }, 250);
        return;
    }
    c.requestAccessToken({ prompt: '' });
}

function disconnectDrive() {
    if (_driveToken && typeof google !== 'undefined' && google.accounts) {
        try { google.accounts.oauth2.revoke(_driveToken.access_token, function() {}); } catch (e) {}
    }
    _driveToken = null;
    clearTimeout(_silentRefreshTimer);
    localStorage.removeItem(DRIVE_TOKEN_KEY);
    localStorage.removeItem(DRIVE_EVER_AUTH_KEY);
    _driveFolderId = null;
    _files = [];
    _selectedTag = null;
    revokeAllThumbs();
    showDisconnected();
}

async function driveFetch(url, opts, retried) {
    opts = opts || {};
    opts.headers = Object.assign({}, opts.headers || {}, {
        Authorization: 'Bearer ' + _driveToken.access_token
    });
    var r = await fetch(url, opts);
    if (r.status === 401 && !retried) {
        _driveToken = null;
        localStorage.removeItem(DRIVE_TOKEN_KEY);
        return new Promise(function(resolve, reject) {
            ensureDriveAuth(function() {
                driveFetch(url, opts, true).then(resolve, reject);
            });
        });
    }
    if (!r.ok) {
        var t = ''; try { t = await r.text(); } catch (e) {}
        throw new Error('Drive ' + r.status + ': ' + t.slice(0, 160));
    }
    return r;
}

/* ─── Carpeta + ficheros ────────────────────────────────── */
var _driveFolderId = null;

async function ensureDriveFolder() {
    if (_driveFolderId) return _driveFolderId;
    var q = "mimeType='application/vnd.google-apps.folder' and name='" +
            GDRIVE_FOLDER.replace(/'/g, "\\'") + "' and trashed=false";
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                             '&fields=files(id,name)&spaces=drive');
    var d = await r.json();
    if (d.files && d.files.length) { _driveFolderId = d.files[0].id; return _driveFolderId; }
    var cr = await driveFetch('https://www.googleapis.com/drive/v3/files', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: GDRIVE_FOLDER, mimeType: 'application/vnd.google-apps.folder' })
    });
    var c = await cr.json();
    _driveFolderId = c.id;
    return _driveFolderId;
}

/* Extensiones admitidas (no-imagen → tratadas como WIP). */
var WIP_EXTS    = ['csp', 'psd', 'kra'];
var IMAGE_EXTS  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif', 'tif', 'tiff'];
/* MIME por extensión (para subida; Drive lo necesita en la metadata). */
var EXT_TO_MIME = {
    jpg:  'image/jpeg', jpeg: 'image/jpeg', png: 'image/png', gif: 'image/gif',
    webp: 'image/webp', bmp:  'image/bmp',  svg: 'image/svg+xml',
    avif: 'image/avif', tif:  'image/tiff', tiff: 'image/tiff',
    psd:  'image/vnd.adobe.photoshop',
    csp:  'application/octet-stream',
    kra:  'application/x-krita'
};

function extOf(name) {
    var m = String(name || '').toLowerCase().match(/\.([a-z0-9]+)$/);
    return m ? m[1] : '';
}
function isWip(file)   { return WIP_EXTS.indexOf(extOf(file.name)) !== -1; }
function isImage(file) {
    if (file.mimeType && file.mimeType.indexOf('image/') === 0) return true;
    return IMAGE_EXTS.indexOf(extOf(file.name)) !== -1;
}
function isAccepted(file) { return isImage(file) || isWip(file); }

async function listDriveFiles() {
    var folderId = await ensureDriveFolder();
    /* Sin filtro de MIME en el query: Drive no soporta OR fácilmente para
       mezclar image/* + extensiones tipo .csp/.psd/.kra. Lo filtramos en
       cliente con isAccepted().
       Drive autogenera `thumbnailLink` para muchos formatos (PSD incluido);
       lo usamos en las tarjetas WIP cuando exista. */
    var q = "'" + folderId + "' in parents and trashed=false";
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files?q=' + encodeURIComponent(q) +
                             '&fields=files(id,name,mimeType,modifiedTime,size,appProperties,thumbnailLink,hasThumbnail)' +
                             '&orderBy=modifiedTime desc&pageSize=1000');
    var d = await r.json();
    return (d.files || []).filter(isAccepted);
}

async function uploadDriveImage(name, tagsCsv, blob) {
    var folderId = await ensureDriveFolder();
    var boundary = '-------gal_' + Math.random().toString(36).slice(2);
    var delim = '\r\n--' + boundary + '\r\n';
    var close = '\r\n--' + boundary + '--';
    /* Para .csp/.kra el navegador deja blob.type vacío → deducimos del nombre. */
    var mime = blob.type || EXT_TO_MIME[extOf(name)] || 'application/octet-stream';
    var metadata = {
        name: name,
        parents: [folderId],
        mimeType: mime,
        appProperties: { tags: tagsCsv }
    };
    var head = delim + 'Content-Type: application/json; charset=UTF-8\r\n\r\n' + JSON.stringify(metadata) +
               delim + 'Content-Type: ' + metadata.mimeType + '\r\n\r\n';
    var headBytes = new TextEncoder().encode(head);
    var tailBytes = new TextEncoder().encode(close);
    var bodyBytes = new Uint8Array(await blob.arrayBuffer());
    var body = new Uint8Array(headBytes.length + bodyBytes.length + tailBytes.length);
    body.set(headBytes, 0);
    body.set(bodyBytes, headBytes.length);
    body.set(tailBytes, headBytes.length + bodyBytes.length);
    var r = await driveFetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart' +
                             '&fields=id,name,mimeType,modifiedTime,size,appProperties,thumbnailLink,hasThumbnail', {
        method: 'POST',
        headers: { 'Content-Type': 'multipart/related; boundary=' + boundary },
        body: body
    });
    return await r.json();
}

async function deleteDriveFile(fileId) {
    await driveFetch('https://www.googleapis.com/drive/v3/files/' + fileId, { method: 'DELETE' });
}

async function downloadDriveBlob(fileId, mimeType) {
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files/' + fileId + '?alt=media');
    var buf = await r.arrayBuffer();
    return new Blob([buf], { type: mimeType || 'application/octet-stream' });
}

/* PATCH para renombrar y/o actualizar appProperties.tags. */
async function patchDriveFile(fileId, patch) {
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files/' + fileId +
                             '?fields=id,name,mimeType,modifiedTime,size,appProperties', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(patch)
    });
    return await r.json();
}

/* ─────────────────────────────────────────────────────────
   EXTRACCIÓN DE PREVIEW DE FICHEROS .kra
   ─────────────────────────────────────────────────────────
   Los .kra son ZIPs estándar con `preview.png` (256×256 aprox.)
   y/o `mergedimage.png` (composite final) en la raíz. Para no
   bajarnos el .kra entero, usamos HTTP Range requests sobre
   la API de Drive:
     1) Pedimos los últimos ~64KB → contienen EOCD + central dir.
     2) Localizamos la entrada del fichero objetivo.
     3) Pedimos solo el rango del local header + datos.
     4) Descomprimimos con DecompressionStream si es DEFLATE.
   Para un .kra de 100MB con un preview de 30KB: ~100KB descargados
   en vez de 100MB. ───────────────────────────────────────── */

async function _driveRangeBytes(fileId, start, endInclusive) {
    var r = await driveFetch('https://www.googleapis.com/drive/v3/files/' + fileId + '?alt=media', {
        headers: { 'Range': 'bytes=' + start + '-' + endInclusive }
    });
    return new Uint8Array(await r.arrayBuffer());
}

async function extractFromDriveZip(file, filename) {
    var size = parseInt(file.size, 10);
    if (!size || size < 22) return null;

    /* 1) Cola del fichero (max EOCD + comentario = 22 + 65535) */
    var tailLen = Math.min(65557, size);
    var tailStart = size - tailLen;
    var tail = await _driveRangeBytes(file.id, tailStart, size - 1);
    var view = new DataView(tail.buffer, tail.byteOffset, tail.byteLength);

    /* 2) Buscar EOCD (PK\x05\x06 = 0x06054b50) desde el final */
    var eocdOff = -1;
    for (var i = tail.length - 22; i >= 0; i--) {
        if (view.getUint32(i, true) === 0x06054b50) { eocdOff = i; break; }
    }
    if (eocdOff < 0) return null;

    var cdSize   = view.getUint32(eocdOff + 12, true);
    var cdGlobal = view.getUint32(eocdOff + 16, true);

    /* 3) Comprobar que el directorio central entra en el chunk descargado */
    if (cdGlobal < tailStart || cdGlobal + cdSize > size) return null;
    var cdLocal    = cdGlobal - tailStart;
    var cdEndLocal = cdLocal + cdSize;
    if (cdEndLocal > tail.length) return null;

    /* 4) Recorrer el directorio central buscando filename */
    var off = cdLocal;
    var entry = null;
    while (off < cdEndLocal && off + 46 <= tail.length) {
        if (view.getUint32(off, true) !== 0x02014b50) break;
        var method     = view.getUint16(off + 10, true);
        var compSize   = view.getUint32(off + 20, true);
        var nameLen    = view.getUint16(off + 28, true);
        var extraLen   = view.getUint16(off + 30, true);
        var commentLen = view.getUint16(off + 32, true);
        var localOff   = view.getUint32(off + 42, true);
        var name = new TextDecoder().decode(tail.subarray(off + 46, off + 46 + nameLen));
        if (name === filename) {
            entry = { method: method, compSize: compSize, localOff: localOff };
            break;
        }
        off += 46 + nameLen + extraLen + commentLen;
    }
    if (!entry) return null;

    /* 5) Rango: local header + datos. Margen 2KB para name+extra (raro que crezca). */
    var grabEnd = Math.min(size - 1, entry.localOff + 30 + 2048 + entry.compSize);
    var local = await _driveRangeBytes(file.id, entry.localOff, grabEnd);
    var lview = new DataView(local.buffer, local.byteOffset, local.byteLength);
    if (lview.getUint32(0, true) !== 0x04034b50) return null;
    var lhNameLen  = lview.getUint16(26, true);
    var lhExtraLen = lview.getUint16(28, true);
    var dataOff = 30 + lhNameLen + lhExtraLen;
    var dataEnd = dataOff + entry.compSize;
    if (dataEnd > local.length) return null;       // extra inesperadamente grande
    var raw = local.subarray(dataOff, dataEnd);

    /* 6) Descomprimir según método (STORED=0, DEFLATE=8) */
    if (entry.method === 0) return raw;
    if (entry.method === 8) {
        if (typeof DecompressionStream === 'undefined') {
            throw new Error('Navegador sin DecompressionStream');
        }
        var stream = new Response(raw).body.pipeThrough(new DecompressionStream('deflate-raw'));
        return new Uint8Array(await new Response(stream).arrayBuffer());
    }
    throw new Error('Compresión no soportada: ' + entry.method);
}

/* Intenta extraer preview.png o mergedimage.png de un .kra. Devuelve un
   blob: URL listo para <img src>, o null si no hay preview embebida. */
async function extractKraPreviewBlob(file) {
    var bytes = null;
    try { bytes = await extractFromDriveZip(file, 'preview.png'); } catch (e) { /* sigue */ }
    if (!bytes) {
        try { bytes = await extractFromDriveZip(file, 'mergedimage.png'); } catch (e) {}
    }
    if (!bytes) return null;
    return URL.createObjectURL(new Blob([bytes], { type: 'image/png' }));
}

/* ─────────────────────────────────────────────────────────
   EXTRACCIÓN DE PREVIEW EMBEBIDA EN .psd
   ─────────────────────────────────────────────────────────
   Estructura PSD (big-endian):
     [26B]  File header: '8BPS' + version=1 + reservado + channels + h + w + depth + colorMode
     [4B]   Color Mode Data length (cmLen) + [cmLen]B data
     [4B]   Image Resources length (irLen) + [irLen]B resources
     [4B]   Layer & Mask length + ...
     [...]  Image data
   Los recursos son secuencias:
     '8BIM' (4B) + ID (2B) + nombre Pascal (1B+chars, padded a par) + len (4B) + data (padded a par)
   ID 0x040C = miniatura "moderna" (PS5+); 0x0409 = vieja (PS4). Ambas con
   un sub-header de 28B y luego los bytes JPEG (si format=1) o RGB crudo (0).
   Usamos Range requests para no descargar el PSD entero. ──────────────── */
async function extractPsdThumbnailBlob(file) {
    var size = parseInt(file.size, 10);
    if (!size || size < 30) return null;

    /* 1) Cabecera completa + cmDataLen (30 bytes) */
    var head = await _driveRangeBytes(file.id, 0, 29);
    var hv = new DataView(head.buffer, head.byteOffset);
    if (hv.getUint32(0, false) !== 0x38425053) return null;
    if (hv.getUint16(4, false) !== 1) return null;
    var cmDataLen = hv.getUint32(26, false);

    /* 2) Tras color mode data: 4 bytes con la longitud de Image Resources */
    var irLenOff = 30 + cmDataLen;
    if (irLenOff + 4 > size) return null;
    var lenBuf = await _driveRangeBytes(file.id, irLenOff, irLenOff + 3);
    var irLen = new DataView(lenBuf.buffer, lenBuf.byteOffset).getUint32(0, false);
    if (!irLen) return null;

    /* 3) Pedimos la sección entera o hasta 2MB (la miniatura suele estar
          al principio; este margen cubre PSDs típicos sin descargar el archivo). */
    var irStart = irLenOff + 4;
    /* Hasta 1GB de Image Resources. La miniatura suele estar al principio,
       pero PSDs muy complejos (smart objects, color profiles enormes, layer
       comps) la pueden empujar lejos. Math.min frente a irLen para no
       pedir más bytes de los que dice la cabecera, y como margen contra
       valores corruptos. */
    var fetchLen = Math.min(irLen, 1024 * 1024 * 1024);
    var ir = await _driveRangeBytes(file.id, irStart, irStart + fetchLen - 1);
    var iv = new DataView(ir.buffer, ir.byteOffset);

    /* 4) Recorrer los recursos buscando ID 0x040C o 0x0409 */
    var off = 0;
    while (off + 12 <= ir.length) {
        if (iv.getUint32(off, false) !== 0x3842494D) break;   // '8BIM'
        var resId   = iv.getUint16(off + 4, false);
        var nameLen = iv.getUint8(off + 6);
        var nameTotal = 1 + nameLen;
        if (nameTotal % 2) nameTotal++;                       // padding a par
        var sizeOff = off + 6 + nameTotal;
        if (sizeOff + 4 > ir.length) break;
        var resLen = iv.getUint32(sizeOff, false);
        var dataStart = sizeOff + 4;
        var dataEnd   = dataStart + resLen;
        var paddedEnd = dataEnd + (dataEnd % 2);              // padding a par

        if ((resId === 0x040C || resId === 0x0409) && dataEnd <= ir.length) {
            /* Sub-header de la miniatura: 28 bytes; después vienen los bytes
               JPEG (format=1) o RGB crudo (format=0). */
            var format = iv.getUint32(dataStart, false);
            if (format === 1 && resLen > 28) {
                var jpegStart = dataStart + 28;
                var jpegLen   = resLen - 28;
                var jpegBytes = ir.subarray(jpegStart, jpegStart + jpegLen);
                return URL.createObjectURL(new Blob([jpegBytes], { type: 'image/jpeg' }));
            }
            if (format === 0) return await _psdRawRgbToBlobUrl(ir, dataStart, resLen);
            return null;
        }
        off = paddedEnd;
    }
    return null;
}

/* ─────────────────────────────────────────────────────────
   EXTRACTORES "LOCALES" (sobre un Blob/File en memoria)
   ─────────────────────────────────────────────────────────
   Cuando el usuario acaba de subir un fichero, sus bytes están en
   memoria como _selectedFile. Si extraemos el preview AHÍ (sin pedirle
   a Drive Range requests), la miniatura aparece al instante y no
   depende de la indexación que pueda hacer Drive después. ─────────── */

async function extractKraPreviewFromBlob(blob) {
    var bytes = new Uint8Array(await blob.arrayBuffer());
    var view  = new DataView(bytes.buffer);
    var n     = bytes.length;
    if (n < 22) return null;

    /* 1) Buscar EOCD desde el final */
    var eocdOff = -1;
    var searchStart = Math.max(0, n - 65557);
    for (var i = n - 22; i >= searchStart; i--) {
        if (view.getUint32(i, true) === 0x06054b50) { eocdOff = i; break; }
    }
    if (eocdOff < 0) return null;

    var cdSize = view.getUint32(eocdOff + 12, true);
    var cdOff  = view.getUint32(eocdOff + 16, true);
    var cdEnd  = cdOff + cdSize;
    if (cdEnd > n) return null;

    /* 2) Helper: buscar una entrada por nombre en el directorio central */
    function findEntry(filename) {
        var off = cdOff;
        while (off < cdEnd && off + 46 <= n) {
            if (view.getUint32(off, true) !== 0x02014b50) break;
            var method     = view.getUint16(off + 10, true);
            var compSize   = view.getUint32(off + 20, true);
            var nameLen    = view.getUint16(off + 28, true);
            var extraLen   = view.getUint16(off + 30, true);
            var commentLen = view.getUint16(off + 32, true);
            var localOff   = view.getUint32(off + 42, true);
            var name = new TextDecoder().decode(bytes.subarray(off + 46, off + 46 + nameLen));
            if (name === filename) return { method: method, compSize: compSize, localOff: localOff };
            off += 46 + nameLen + extraLen + commentLen;
        }
        return null;
    }

    /* 3) Helper: extraer (y descomprimir si DEFLATE) los datos de una entrada */
    async function readEntry(entry) {
        var lh = entry.localOff;
        if (view.getUint32(lh, true) !== 0x04034b50) return null;
        var lhNameLen  = view.getUint16(lh + 26, true);
        var lhExtraLen = view.getUint16(lh + 28, true);
        var dataOff = lh + 30 + lhNameLen + lhExtraLen;
        if (dataOff + entry.compSize > n) return null;
        var raw = bytes.subarray(dataOff, dataOff + entry.compSize);
        if (entry.method === 0) return raw;
        if (entry.method === 8) {
            if (typeof DecompressionStream === 'undefined') return null;
            var stream = new Response(raw).body.pipeThrough(new DecompressionStream('deflate-raw'));
            return new Uint8Array(await new Response(stream).arrayBuffer());
        }
        return null;
    }

    /* 4) Intentar preview.png, luego mergedimage.png como fallback */
    var pngBytes = null;
    var e1 = findEntry('preview.png');
    if (e1) { try { pngBytes = await readEntry(e1); } catch (e) {} }
    if (!pngBytes) {
        var e2 = findEntry('mergedimage.png');
        if (e2) { try { pngBytes = await readEntry(e2); } catch (e) {} }
    }
    if (!pngBytes) return null;
    return URL.createObjectURL(new Blob([pngBytes], { type: 'image/png' }));
}

/* Renderiza un thumbnail PSD en formato raw RGB (format=0) a un blob PNG
   usando canvas. Útil para PSDs viejos / generados por herramientas que
   no escriben JPEG. Devuelve URL del blob o null. */
async function _psdRawRgbToBlobUrl(bytes, dataStart, resLen) {
    if (resLen < 28) return null;
    var view = new DataView(bytes.buffer, bytes.byteOffset || 0, bytes.byteLength);
    var width      = view.getUint32(dataStart + 4,  false);
    var height     = view.getUint32(dataStart + 8,  false);
    var widthBytes = view.getUint32(dataStart + 12, false);
    var bits       = view.getUint16(dataStart + 24, false);
    var planes     = view.getUint16(dataStart + 26, false);
    if (!width || !height || bits !== 24 || (planes !== 1 && planes !== 3)) return null;
    var pixOff = dataStart + 28;
    var needed = (planes === 1) ? widthBytes * height : 3 * width * height;
    if (pixOff + needed > bytes.length) return null;
    var canvas = document.createElement('canvas');
    canvas.width = width;  canvas.height = height;
    var ctx = canvas.getContext('2d');
    var imgData = ctx.createImageData(width, height);
    var dst = imgData.data;
    if (planes === 1) {
        /* Interleaved RGB, con padding por fila a `widthBytes` */
        for (var y = 0; y < height; y++) {
            var srcRow = pixOff + y * widthBytes;
            for (var x = 0; x < width; x++) {
                var s = srcRow + x * 3, d = (y * width + x) * 4;
                dst[d] = bytes[s]; dst[d+1] = bytes[s+1]; dst[d+2] = bytes[s+2]; dst[d+3] = 255;
            }
        }
    } else {
        /* Planar: R[pix], G[pix], B[pix] uno detrás de otro */
        var pixCount = width * height;
        for (var i = 0; i < pixCount; i++) {
            var di = i * 4;
            dst[di]   = bytes[pixOff + i];
            dst[di+1] = bytes[pixOff + pixCount + i];
            dst[di+2] = bytes[pixOff + 2 * pixCount + i];
            dst[di+3] = 255;
        }
    }
    ctx.putImageData(imgData, 0, 0);
    return await new Promise(function(resolve) {
        canvas.toBlob(function(b) { resolve(b ? URL.createObjectURL(b) : null); }, 'image/png');
    });
}

async function extractPsdThumbnailFromBlob(blob) {
    var bytes = new Uint8Array(await blob.arrayBuffer());
    var view  = new DataView(bytes.buffer);
    var n     = bytes.length;
    if (n < 30) return null;
    if (view.getUint32(0, false) !== 0x38425053) return null;
    if (view.getUint16(4, false) !== 1) return null;
    var cmDataLen = view.getUint32(26, false);
    var irLenOff = 30 + cmDataLen;
    if (irLenOff + 4 > n) return null;
    var irLen = view.getUint32(irLenOff, false);
    if (!irLen) return null;
    var irStart = irLenOff + 4;
    var irEnd = Math.min(irStart + irLen, n);

    var off = irStart;
    while (off + 12 <= irEnd) {
        if (view.getUint32(off, false) !== 0x3842494D) break;   // '8BIM'
        var resId   = view.getUint16(off + 4, false);
        var nameLen = view.getUint8(off + 6);
        var nameTotal = 1 + nameLen;
        if (nameTotal % 2) nameTotal++;
        var sizeOff = off + 6 + nameTotal;
        if (sizeOff + 4 > irEnd) break;
        var resLen = view.getUint32(sizeOff, false);
        var dataStart = sizeOff + 4;
        var dataEnd   = dataStart + resLen;
        var paddedEnd = dataEnd + (dataEnd % 2);
        if ((resId === 0x040C || resId === 0x0409) && dataEnd <= irEnd) {
            var format = view.getUint32(dataStart, false);
            if (format === 1 && resLen > 28) {
                var jpegStart = dataStart + 28;
                var jpegLen   = resLen - 28;
                var jpegBytes = bytes.subarray(jpegStart, jpegStart + jpegLen);
                return URL.createObjectURL(new Blob([jpegBytes], { type: 'image/jpeg' }));
            }
            if (format === 0) return await _psdRawRgbToBlobUrl(bytes, dataStart, resLen);
            return null;
        }
        off = paddedEnd;
    }
    return null;
}

/* Indicador visual en la placeholder de la card: cambia el texto de la
   etiqueta de extensión (".psd" → ".psd · esperando preview…", etc.). */
function _setCardExtLabel(fileId, suffix) {
    var card = document.querySelector('.gal-card[data-id="' + fileId + '"]');
    if (!card) return;
    var lbl = card.querySelector('.gal-wip-ext');
    if (!lbl) return;
    if (!lbl.dataset.origText) lbl.dataset.origText = lbl.textContent;
    lbl.textContent = lbl.dataset.origText + (suffix ? ' · ' + suffix : '');
}

/* Polling tras una subida: Drive a veces tarda 5-60s en generar el
   thumbnailLink de un PSD. Cuando aparezca, actualizamos el _files y
   reset-eamos data-loaded para que loadThumb lo pinte sin recarga.
   Si agota intentos, dejamos el placeholder con un texto explicativo. */
async function _pollDriveThumbnail(fileId) {
    var attempts  = 0;
    var maxTries  = 15;
    var delays    = [1500, 2000, 3000, 4000, 5000, 5000, 6000, 7000, 8000, 10000, 12000, 15000, 18000, 20000, 25000]; // ~140s
    _setCardExtLabel(fileId, 'esperando preview…');

    while (attempts < maxTries) {
        await new Promise(function(res) { setTimeout(res, delays[attempts] || 25000); });
        attempts++;
        try {
            var r = await driveFetch('https://www.googleapis.com/drive/v3/files/' + fileId +
                '?fields=id,name,mimeType,modifiedTime,size,appProperties,thumbnailLink,hasThumbnail');
            var refreshed = await r.json();
            if (!refreshed || !refreshed.thumbnailLink) continue;
            var idx = _files.findIndex(function(x) { return x.id === fileId; });
            if (idx >= 0) {
                _files[idx] = Object.assign({}, _files[idx], refreshed);
                var card = document.querySelector('.gal-card[data-id="' + fileId + '"]');
                if (card) {
                    delete card.dataset.loaded;
                    card._loading = false;        // permitir re-ejecución
                    loadThumb(card);
                }
            }
            return;
        } catch (e) { /* sigue intentando */ }
    }
    /* Drive no ha generado la miniatura en ~140s. Probablemente el PSD se
       guardó sin "Maximize Compatibility" Y Drive tampoco puede renderizarlo. */
    _setCardExtLabel(fileId, 'sin preview');
}

/* Forzar la descarga al disco del usuario con el nombre del fichero. */
async function downloadToUser(file) {
    setStatus('Descargando…');
    try {
        var blob = await downloadDriveBlob(file.id, file.mimeType);
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url;
        a.download = file.name;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function() { URL.revokeObjectURL(url); }, 2000);
        setStatus('');
    } catch (e) {
        setStatus('Error al descargar: ' + e.message, true);
    }
}

/* ─── Estado de la UI ───────────────────────────────────── */
var _files = [];               // [{id,name,mimeType,modifiedTime,size,appProperties:{tags}}]
var _selectedTag = null;   // selección única: nombre de la etiqueta o null
var _thumbCache = new Map();   // fileId → objectURL
var _searchTerm = '';

function setStatus(msg, isErr) {
    var s = document.getElementById('gal-status');
    var cs = document.getElementById('gal-connect-status');
    if (s) { s.textContent = msg || ''; s.style.color = isErr ? 'var(--error-text)' : ''; }
    if (cs) { cs.textContent = msg || ''; cs.style.color = isErr ? 'var(--error-text)' : ''; }
}

function showConnected() {
    document.getElementById('gal-connect-view').style.display = 'none';
    document.getElementById('gal-main').style.display = '';   // → flex (CSS)
    setStatus('');
    reload();
}
function showDisconnected() {
    document.getElementById('gal-connect-view').style.display = '';
    document.getElementById('gal-main').style.display = 'none';
    setStatus('');
}

function revokeAllThumbs() {
    _thumbCache.forEach(function(url) { try { URL.revokeObjectURL(url); } catch (e) {} });
    _thumbCache.clear();
}

function parseTags(raw) {
    if (!raw) return [];
    /* Extrae #tags (letras, números, _ , -) admitiendo acentos */
    var out = [], seen = {};
    var rx = /#([\p{L}0-9_-]+)/gu;
    var m;
    while ((m = rx.exec(raw)) !== null) {
        var t = m[1].toLowerCase();
        if (!seen[t]) { seen[t] = 1; out.push(t); }
    }
    /* También admitimos separación por comas/espacios sin # */
    if (!out.length) {
        raw.split(/[,\s]+/).forEach(function(tok) {
            tok = tok.replace(/^#/, '').toLowerCase().replace(/[^\p{L}0-9_-]/gu, '');
            if (tok && !seen[tok]) { seen[tok] = 1; out.push(tok); }
        });
    }
    return out;
}

/* Tag especial: NO se muestra en el sidebar ni como chip en la tarjeta;
   activa la badge "WIP" en la card y el overlay sobre la miniatura. */
var WIP_TAG = 'wip';

function tagsOf(file) {
    var csv = (file.appProperties && file.appProperties.tags) || '';
    if (!csv) return [];
    return csv.split(',').map(function(s) { return s.trim().toLowerCase(); }).filter(Boolean);
}
/* Tags que el usuario debe ver (chip en card, sidebar) — excluye #wip. */
function visibleTagsOf(file) {
    return tagsOf(file).filter(function(t) { return t !== WIP_TAG; });
}
function hasWipTag(file) {
    return tagsOf(file).indexOf(WIP_TAG) !== -1;
}

function allTags() {
    var c = {};
    _files.forEach(function(f) {
        visibleTagsOf(f).forEach(function(t) { c[t] = (c[t] || 0) + 1; });
    });
    return Object.keys(c).sort().map(function(t) { return { tag: t, count: c[t] }; });
}

function filteredFiles() {
    var q = _searchTerm.toLowerCase().trim();
    return _files.filter(function(f) {
        if (q && f.name.toLowerCase().indexOf(q) === -1) return false;
        if (_selectedTag && tagsOf(f).indexOf(_selectedTag) === -1) return false;
        return true;
    });
}

function renderTagSidebar() {
    var list = document.getElementById('gal-tag-list');
    var tags = allTags();
    list.innerHTML = '';
    if (!tags.length) {
        list.innerHTML = '<div class="gal-empty" id="gal-tag-empty">Sin etiquetas todavía.</div>';
        return;
    }
    tags.forEach(function(t) {
        var el = document.createElement('div');
        el.className = 'gal-tag-item' + (_selectedTag === t.tag ? ' active' : '');
        el.innerHTML = '<span class="gal-tag-name">#' + escapeHtml(t.tag) + '</span>' +
                       '<span class="gal-tag-count">' + t.count + '</span>';
        el.addEventListener('click', function() {
            /* Selección única: si clicas la misma → deselecciona; si clicas otra → reemplaza */
            _selectedTag = (_selectedTag === t.tag) ? null : t.tag;
            renderTagSidebar();
            renderGrid();
        });
        list.appendChild(el);
    });
}

function renderGrid() {
    var grid = document.getElementById('gal-grid');
    var emptyEl = document.getElementById('gal-grid-empty');
    var files = filteredFiles();
    document.getElementById('gal-count').textContent =
        files.length + (files.length === 1 ? ' archivo' : ' archivos') +
        (files.length !== _files.length ? ' / ' + _files.length : '');
    grid.innerHTML = '';
    if (!files.length) {
        emptyEl.style.display = '';   // → flex (CSS)
        emptyEl.innerHTML = _files.length
            ? '<p>Sin resultados con los filtros aplicados.</p>'
            : '<p>La galería está vacía.</p><p><small>Pulsa <strong>⬆ Subir imagen</strong> para empezar.</small></p>';
        return;
    }
    emptyEl.style.display = 'none';
    files.forEach(function(f) {
        var wipExt    = isWip(f);          // extensión no renderizable (.psd/.csp/.kra)
        var wipTagged = hasWipTag(f);      // tiene el tag especial #wip → badge visible
        var ext = extOf(f.name).toUpperCase();
        var card = document.createElement('div');
        card.className = 'gal-card' + (wipTagged ? ' gal-wip' : '');
        card.dataset.id = f.id;
        card.dataset.kind = wipExt ? 'wip' : 'image';
        var ts = visibleTagsOf(f);         // chip list nunca muestra #wip
        var thumbInner = wipExt
            ? '<div class="gal-thumb-wip">' +
                  '<div class="gal-wip-ext">.' + escapeHtml(ext.toLowerCase()) + '</div>' +
              '</div>'
            : '<div class="gal-thumb-ph">…</div>';
        card.innerHTML =
            '<div class="gal-thumb">' + thumbInner + _wipBadgeOverlay(f) + '</div>' +
            '<div class="gal-name" title="' + escapeAttr(f.name) + '">' + escapeHtml(f.name) + '</div>' +
            (ts.length ? '<div class="gal-card-tags">' +
                ts.map(function(t) { return '<span class="gal-chip">#' + escapeHtml(t) + '</span>'; }).join('') +
            '</div>' : '');
        /* Click izquierdo: extensión no-renderizable → descarga; resto → preview */
        card.addEventListener('click', function() {
            if (wipExt) downloadToUser(f);
            else openPreview(f);
        });
        card.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            openCtxMenu(e.clientX, e.clientY, f);
        });
        grid.appendChild(card);
    });
    /* Lazy-load solo para tarjetas de imagen (WIP no tienen thumbnail) */
    observeThumbs();
}

/* IntersectionObserver para cargar thumbs solo cuando entran en viewport */
var _thumbObserver = null;
function observeThumbs() {
    if (!_thumbObserver) {
        _thumbObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(ent) {
                if (!ent.isIntersecting) return;
                _thumbObserver.unobserve(ent.target);
                loadThumb(ent.target);
            });
        }, { rootMargin: '300px' });
    }
    document.querySelectorAll('.gal-card').forEach(function(card) {
        if (card.dataset.loaded) return;
        _thumbObserver.observe(card);
    });
}

/* Overlay sobre la miniatura. Componible:
   - badge "WIP" si el fichero tiene el tag #wip.
   - etiqueta de extensión si la extensión no es de imagen (.psd/.csp/.kra). */
function _wipOverlayHtml(file, ext) {
    var parts = [];
    if (hasWipTag(file)) parts.push('<span class="gal-wip-badge">WIP</span>');
    if (isWip(file))     parts.push('<span class="gal-wip-ext-small">.' + escapeHtml(ext) + '</span>');
    if (!parts.length) return '';
    return '<div class="gal-wip-overlay">' + parts.join('') + '</div>';
}
/* Wrapper sin extensión: solo el badge WIP (para imágenes etiquetadas y
   también placeholders donde la extensión la pinta el thumbInner). */
function _wipBadgeOverlay(file) {
    return hasWipTag(file)
        ? '<div class="gal-wip-overlay"><span class="gal-wip-badge">WIP</span></div>'
        : '';
}
function _setWipExtLabel(thumb, txt) {
    var ph = thumb.querySelector('.gal-wip-ext');
    if (ph) ph.textContent = txt;
}

async function loadThumb(card) {
    /* Evita doble ejecución concurrente. El observer y _pollDriveThumbnail
       pueden llamar a loadThumb sobre la misma card en paralelo; sin este
       guard, ambas crean Image() probes que la red deduplica → Firefox
       reporta NS_BINDING_ABORTED en uno de los dos. */
    if (card._loading) return;
    card._loading = true;
    try {
        await _loadThumbInner(card);
    } finally {
        card._loading = false;
    }
}
async function _loadThumbInner(card) {
    var fileId = card.dataset.id;
    var f = _files.find(function(x) { return x.id === fileId; });
    if (!f) return;
    var thumb = card.querySelector('.gal-thumb');
    var ext   = extOf(f.name);

    if (isWip(f)) {
        /* Vía A — Drive ya generó thumbnailLink (típico de PSD): la usamos
           directamente. Si falla la carga, CAEMOS a extracción.
           Insertamos el MISMO Image que usamos para probar — así hay un
           solo fetch al URL de Drive (evita "NS_BINDING_ABORTED" por
           petición duplicada en Firefox).
           referrerpolicy=no-referrer evita que la Enhanced Tracking
           Protection de Firefox marque la petición como tracking de Google
           y la cancele antes de tiempo. */
        if (f.thumbnailLink) {
            var imgEl = new Image();
            imgEl.alt = f.name;
            imgEl.referrerPolicy = 'no-referrer';
            var loadedFromLink = await new Promise(function(resolve) {
                imgEl.onload  = function() { resolve(true); };
                imgEl.onerror = function() { resolve(false); };
                imgEl.src = f.thumbnailLink;
            });
            if (loadedFromLink) {
                thumb.innerHTML = '';
                thumb.appendChild(imgEl);
                thumb.insertAdjacentHTML('beforeend', _wipOverlayHtml(f, ext));
                card.dataset.loaded = '1';
                return;
            }
        }

        /* Vía B — .kra / .psd: extraer el preview embebido del propio archivo
           usando Range requests sobre la API. */
        if (ext === 'kra' || ext === 'psd') {
            if (_thumbCache.has(fileId)) {
                thumb.innerHTML =
                    '<img src="' + _thumbCache.get(fileId) + '" alt="' + escapeAttr(f.name) + '">' +
                    _wipOverlayHtml(f, ext);
                card.dataset.loaded = '1';
                return;
            }
            _setWipExtLabel(thumb, '.' + ext + ' · extrayendo…');
            try {
                var url = ext === 'kra'
                    ? await extractKraPreviewBlob(f)
                    : await extractPsdThumbnailBlob(f);
                if (!url) { _setWipExtLabel(thumb, '.' + ext); return; }
                _thumbCache.set(fileId, url);
                thumb.innerHTML =
                    '<img src="' + url + '" alt="' + escapeAttr(f.name) + '">' +
                    _wipOverlayHtml(f, ext);
                card.dataset.loaded = '1';
            } catch (e) {
                _setWipExtLabel(thumb, '.' + ext);
            }
            return;
        }
        /* .csp y otros WIP sin thumbnailLink → placeholder se queda como está */
        return;
    }
    /* Imagen normal: descargar blob completo y cachear como objectURL */
    try {
        var url;
        if (_thumbCache.has(fileId)) {
            url = _thumbCache.get(fileId);
        } else {
            var blob = await downloadDriveBlob(fileId, f.mimeType);
            url = URL.createObjectURL(blob);
            _thumbCache.set(fileId, url);
        }
        thumb.innerHTML = '<img src="' + url + '" alt="' + escapeAttr(f.name) + '">' + _wipBadgeOverlay(f);
        card.dataset.loaded = '1';
    } catch (e) {
        thumb.innerHTML = '<div class="gal-thumb-ph err">✗</div>';
    }
}

/* ─── Preview ───────────────────────────────────────────── */
function openPreview(f) {
    var p = document.getElementById('gal-preview');
    var img = document.getElementById('gal-preview-img');
    var nameEl = document.getElementById('gal-preview-name');
    var tagsEl = document.getElementById('gal-preview-tags');
    nameEl.textContent = f.name + (isWip(f) ? '  (preview de Drive)' : '');
    var ts = visibleTagsOf(f);   // ocultar #wip de los chips
    tagsEl.innerHTML = ts.length
        ? ts.map(function(t) { return '<span class="gal-chip">#' + escapeHtml(t) + '</span>'; }).join('')
        : '<small style="color:var(--text-faint);">Sin etiquetas</small>';
    p.style.display = '';   // → flex (CSS)
    if (isWip(f)) {
        /* WIP: prioridad → thumbnailLink (PSD) > cache (kra ya extraído)
           > extracción on-demand del .kra > mensaje "sin preview". */
        if (f.thumbnailLink) {
            img.src = f.thumbnailLink;
            img.onerror = function() {
                tagsEl.innerHTML += '<br><small style="color:var(--error-text);">No se pudo cargar la preview.</small>';
            };
            return;
        }
        if (_thumbCache.has(f.id)) { img.src = _thumbCache.get(f.id); return; }
        var _ext = extOf(f.name);
        if (_ext === 'kra' || _ext === 'psd') {
            img.src = '';
            var note = document.createElement('small');
            note.style.color = 'var(--text-muted)';
            note.innerHTML = '<br>Extrayendo preview del .' + _ext + '…';
            tagsEl.appendChild(note);
            var extractor = (_ext === 'kra') ? extractKraPreviewBlob : extractPsdThumbnailBlob;
            extractor(f).then(function(url) {
                if (note.parentNode) note.parentNode.removeChild(note);
                if (!url) {
                    tagsEl.innerHTML += '<br><small style="color:var(--text-muted);">El .' + _ext + ' no contiene preview embebida.</small>';
                    return;
                }
                _thumbCache.set(f.id, url);
                img.src = url;
            }).catch(function(e) {
                if (note.parentNode) note.parentNode.removeChild(note);
                tagsEl.innerHTML += '<br><small style="color:var(--error-text);">Error: ' + escapeHtml(e.message) + '</small>';
            });
            return;
        }
        img.src = '';
        tagsEl.innerHTML += '<br><small style="color:var(--text-muted);">Drive no generó preview para este formato. Descárgalo para abrirlo.</small>';
        return;
    }
    /* Imagen normal: usar el blob ya descargado o pedirlo ahora */
    img.src = _thumbCache.get(f.id) || '';
    if (!img.src) {
        downloadDriveBlob(f.id, f.mimeType).then(function(blob) {
            var url = URL.createObjectURL(blob);
            _thumbCache.set(f.id, url);
            img.src = url;
        }).catch(function(e) { tagsEl.innerHTML += '<br><small style="color:var(--error-text);">' + escapeHtml(e.message) + '</small>'; });
    }
}
function closePreview() { document.getElementById('gal-preview').style.display = 'none'; }

/* ─── Recarga global ────────────────────────────────────── */
async function reload() {
    setStatus('Cargando…');
    try {
        _files = await listDriveFiles();
        setStatus('');
        renderTagSidebar();
        renderGrid();
    } catch (e) {
        setStatus('Error: ' + e.message, true);
    }
}

/* ─── Menú contextual ───────────────────────────────────── */
var _ctxFile = null;
function openCtxMenu(x, y, file) {
    _ctxFile = file;
    var menu = document.getElementById('gal-ctx-menu');
    if (!menu) return;
    /* "Ver" solo para imágenes — los WIP no tienen una preview garantizada
       (depende de si Drive autogeneró thumbnailLink o si el archivo trae
       miniatura embebida). Para no ofrecer una acción que puede fallar
       silenciosamente, la opción se oculta en WIPs. */
    var prevOpt = menu.querySelector('[data-act="preview"]');
    if (prevOpt) prevOpt.style.display = isWip(file) ? 'none' : '';
    /* "Publicar" solo para imágenes: para los WIP no podemos garantizar
       que la URL pública de Drive renderice (es un .psd/.kra). */
    var pubOpt = menu.querySelector('[data-act="publish"]');
    if (pubOpt) pubOpt.style.display = isWip(file) ? 'none' : '';
    var discOpt = menu.querySelector('[data-act="discord"]');
    if (discOpt) discOpt.style.display = isWip(file) ? 'none' : '';
    /* Forzar visibilidad Y estética inline: blindado contra cache del CSS. */
    menu.style.display    = 'block';
    menu.style.position   = 'fixed';
    menu.style.zIndex     = '99999';
    menu.style.visibility = 'visible';
    menu.style.opacity    = '1';
    menu.style.minWidth   = '180px';
    menu.style.padding    = '2px 0';
    menu.style.fontSize   = '11px';
    menu.style.background = 'var(--btn-bg, #c0c0c0)';
    menu.style.color      = 'var(--text, #000)';
    menu.style.borderTop    = '2px solid var(--bezel-light-1, #fff)';
    menu.style.borderLeft   = '2px solid var(--bezel-light-1, #fff)';
    menu.style.borderRight  = '2px solid var(--bezel-dark-2, #808080)';
    menu.style.borderBottom = '2px solid var(--bezel-dark-2, #808080)';
    menu.style.boxShadow  = '2px 2px 5px rgba(0,0,0,0.35)';
    /* Medir tras hacerlo visible para evitar que se salga del viewport */
    var mw = menu.offsetWidth, mh = menu.offsetHeight;
    menu.style.left = Math.min(x, window.innerWidth  - mw - 4) + 'px';
    menu.style.top  = Math.min(y, window.innerHeight - mh - 4) + 'px';
}
function closeCtxMenu() {
    var menu = document.getElementById('gal-ctx-menu');
    if (menu) menu.style.display = 'none';
    _ctxFile = null;
}

/* ─── Subir imagen ──────────────────────────────────────── */
var _selectedFile = null;
function openUploadDialog() {
    document.getElementById('gal-up-filechosen').value = '';
    document.getElementById('gal-up-name').value = '';
    document.getElementById('gal-up-tags').value = '';
    document.getElementById('gal-up-tag-chips').innerHTML = '';
    document.getElementById('gal-up-status').textContent = '';
    document.getElementById('gal-up-status').style.color = '';
    _selectedFile = null;
    var dlg = document.getElementById('gal-upload-dialog');
    dlg.style.display = 'block';
    centerDialog(dlg);
}
function closeUploadDialog() { document.getElementById('gal-upload-dialog').style.display = 'none'; }

function updateUploadTagChips() {
    var ts = parseTags(document.getElementById('gal-up-tags').value);
    var c = document.getElementById('gal-up-tag-chips');
    c.innerHTML = ts.map(function(t) { return '<span class="gal-chip">#' + escapeHtml(t) + '</span>'; }).join('');
}

async function submitUpload() {
    var btn = document.getElementById('gal-up-submit');
    var stEl = document.getElementById('gal-up-status');
    if (!_selectedFile) { stEl.textContent = 'Elige un archivo.'; stEl.style.color = 'var(--error-text)'; return; }
    var rawName = document.getElementById('gal-up-name').value.trim();
    if (!rawName) { stEl.textContent = 'Pon un nombre.'; stEl.style.color = 'var(--error-text)'; return; }
    /* Sanitizar: quitar caracteres no válidos para Drive */
    rawName = rawName.replace(/[\\/:*?"<>|]/g, '_').slice(0, 120);
    /* Conservar extensión del archivo original */
    var ext = '';
    var dot = _selectedFile.name.lastIndexOf('.');
    if (dot >= 0) ext = _selectedFile.name.slice(dot).toLowerCase();
    /* Si el usuario YA escribió una extensión, no duplicar */
    var finalName = /\.\w{2,5}$/i.test(rawName) ? rawName : rawName + ext;
    var tags = parseTags(document.getElementById('gal-up-tags').value);
    /* Auto-añadir #wip si el archivo es de extensión no-renderizable
       (.psd/.csp/.kra). El usuario puede quitarlo después editando. */
    if (WIP_EXTS.indexOf(extOf(_selectedFile.name)) !== -1 && tags.indexOf(WIP_TAG) === -1) {
        tags.push(WIP_TAG);
    }
    var tagsCsv = tags.join(',');

    btn.classList.add('btn-busy'); btn.disabled = true;
    stEl.style.color = ''; stEl.textContent = 'Subiendo…';
    try {
        /* Capturamos el Blob ANTES de la subida porque uploadDriveImage
           consume el stream; quedárnoslo nos permite extraer la preview
           localmente sin re-bajar de Drive. */
        var localBlob = _selectedFile;
        var meta = await uploadDriveImage(finalName, tagsCsv, _selectedFile);

        /* Pre-extraer la miniatura del fichero LOCAL para que la card la
           muestre al instante (sin esperar a que Drive procese, ni a una
           recarga). Si falla (PSD sin miniatura embebida), polleamos a
           Drive hasta que genere su thumbnailLink. */
        var newExt = extOf(meta.name);
        var localExtractionOk = false;
        if (isWip(meta) && (newExt === 'kra' || newExt === 'psd')) {
            try {
                var localUrl = newExt === 'kra'
                    ? await extractKraPreviewFromBlob(localBlob)
                    : await extractPsdThumbnailFromBlob(localBlob);
                if (localUrl) {
                    _thumbCache.set(meta.id, localUrl);
                    localExtractionOk = true;
                }
            } catch (e) { /* extracción local opcional — silenciar */ }
        }

        /* Insertar en cabeza del listado */
        _files.unshift(meta);
        renderTagSidebar();
        renderGrid();
        closeUploadDialog();

        /* Si no conseguimos miniatura local y Drive aún no ha generado la
           suya, pollemos su metadata hasta que aparezca thumbnailLink.
           Drive suele tardar 2-15s en procesar un PSD. */
        if (isWip(meta) && !localExtractionOk && !meta.thumbnailLink) {
            _pollDriveThumbnail(meta.id);
        }
    } catch (e) {
        stEl.textContent = 'Error: ' + e.message;
        stEl.style.color = 'var(--error-text)';
    } finally {
        btn.classList.remove('btn-busy'); btn.disabled = false;
    }
}

/* ─── Editar (renombrar + etiquetas) ────────────────────── */
var _editingFile = null;
function openEditDialog(f) {
    _editingFile = f;
    var dlg = document.getElementById('gal-edit-dialog');
    /* Nombre sin extensión, para que el usuario edite solo la parte legible */
    var name = f.name, dot = name.lastIndexOf('.');
    var base = dot > 0 ? name.slice(0, dot) : name;
    document.getElementById('gal-ed-name').value = base;
    /* Mostrar tags actuales como #t1 #t2 */
    var ts = tagsOf(f);
    document.getElementById('gal-ed-tags').value = ts.map(function(t) { return '#' + t; }).join(' ');
    updateEditTagChips();
    document.getElementById('gal-ed-status').textContent = '';
    document.getElementById('gal-ed-status').style.color = '';
    dlg.style.display = 'block';
    centerDialog(dlg);
}
function closeEditDialog() {
    document.getElementById('gal-edit-dialog').style.display = 'none';
    _editingFile = null;
}
function updateEditTagChips() {
    var ts = parseTags(document.getElementById('gal-ed-tags').value);
    document.getElementById('gal-ed-tag-chips').innerHTML =
        ts.map(function(t) { return '<span class="gal-chip">#' + escapeHtml(t) + '</span>'; }).join('');
}
async function submitEdit() {
    if (!_editingFile) return;
    var f = _editingFile;
    var btn = document.getElementById('gal-ed-submit');
    var stEl = document.getElementById('gal-ed-status');
    var rawName = document.getElementById('gal-ed-name').value.trim();
    if (!rawName) { stEl.textContent = 'Pon un nombre.'; stEl.style.color = 'var(--error-text)'; return; }
    rawName = rawName.replace(/[\\/:*?"<>|]/g, '_').slice(0, 120);
    /* Preservar la extensión original si el usuario no la escribe */
    var origExt = '';
    var dot = f.name.lastIndexOf('.');
    if (dot >= 0) origExt = f.name.slice(dot);
    var finalName = /\.\w{2,5}$/i.test(rawName) ? rawName : rawName + origExt;
    var tags = parseTags(document.getElementById('gal-ed-tags').value);
    var tagsCsv = tags.join(',');

    btn.classList.add('btn-busy'); btn.disabled = true;
    stEl.style.color = ''; stEl.textContent = 'Guardando…';
    try {
        var updated = await patchDriveFile(f.id, {
            name: finalName,
            appProperties: { tags: tagsCsv }
        });
        /* Actualizar el fichero en cache local */
        var idx = _files.findIndex(function(x) { return x.id === f.id; });
        if (idx >= 0) _files[idx] = Object.assign({}, _files[idx], updated);
        renderTagSidebar();
        renderGrid();
        closeEditDialog();
    } catch (e) {
        stEl.textContent = 'Error: ' + e.message;
        stEl.style.color = 'var(--error-text)';
    } finally {
        btn.classList.remove('btn-busy'); btn.disabled = false;
    }
}

/* ─── Publicar en perfil ────────────────────────────────────
   El fichero NO se sube a nuestro servidor: se hace público en Drive
   (permiso "anyone reader") y se guarda solo la URL pública en posts. */
var _publishingFile = null;
function openPublishDialog(f) {
    if (isWip(f)) return;                                // por seguridad
    _publishingFile = f;
    var dlg = document.getElementById('gal-publish-dialog');
    var prev = document.getElementById('gal-pub-preview');
    var src = _thumbCache.get(f.id) || f.thumbnailLink || '';
    prev.innerHTML = src
        ? '<img src="' + escapeAttr(src) + '" alt="" style="max-width:100%;max-height:200px;object-fit:contain;display:block;margin:0 auto;">'
        : '<div style="text-align:center;padding:20px;color:var(--text-muted);">Cargando preview…</div>';
    document.getElementById('gal-pub-text').value = '';
    var st = document.getElementById('gal-pub-status');
    st.textContent = ''; st.style.color = '';
    dlg.style.display = 'block';
    centerDialog(dlg);
}
function closePublishDialog() {
    document.getElementById('gal-publish-dialog').style.display = 'none';
    _publishingFile = null;
}

/* Hace el fichero "anyone reader" si no lo es ya. Tolera errores tipo
   "duplicate" / "already exists" porque significa que ya está público. */
async function _ensureDrivePublic(fileId) {
    try {
        await driveFetch('https://www.googleapis.com/drive/v3/files/' + fileId + '/permissions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: 'reader', type: 'anyone' })
        });
    } catch (e) {
        if (!/duplicate|alreadyExists|exists|already.+public/i.test(e.message)) throw e;
    }
}

async function submitPublish() {
    if (!_publishingFile) return;
    var f = _publishingFile;
    var btn = document.getElementById('gal-pub-submit');
    var st  = document.getElementById('gal-pub-status');
    var text = document.getElementById('gal-pub-text').value.trim();
    btn.classList.add('btn-busy'); btn.disabled = true;
    st.style.color = ''; st.textContent = 'Configurando permisos…';
    try {
        await _ensureDrivePublic(f.id);
        /* URL pública de Drive: el endpoint /thumbnail funciona sin
           autenticación para ficheros con permiso "anyone". `sz=w1920`
           limita el ancho. Es más estable que el formato lh3/d/{id}=. */
        var publicUrl = 'https://drive.google.com/thumbnail?id=' + encodeURIComponent(f.id) + '&sz=w1920';
        st.textContent = 'Publicando…';
        var r = await fetch('../assets/profile/api.php?action=add-post', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: text, image_url: publicUrl })
        });
        var data = await r.json();
        if (data.error) throw new Error(data.error);
        st.style.color = '';
        st.textContent = '✔ Publicado en tu perfil.';
        /* Avisar al desktop (donde vive perfil.php) para que recargue los
           posts sin esperar a un refresh manual. */
        try { window.parent.postMessage({ type: 'profile-post-added' }, '*'); } catch (e) {}
        setTimeout(closePublishDialog, 900);
    } catch (e) {
        st.textContent = 'Error: ' + e.message;
        st.style.color = 'var(--error-text)';
    } finally {
        btn.classList.remove('btn-busy'); btn.disabled = false;
    }
}

/* ─── Publicar en Discord ───────────────────────────────────
   Igual que el flujo de publicar en perfil:
   1) `_ensureDrivePublic(file.id)` → permiso "anyone reader" en Drive.
   2) URL pública de Drive (drive.google.com/thumbnail).
   3) POST a nuestro endpoint que reenvía al webhook del usuario.
   El webhook URL no sale del servidor (lo lee el backend de la BD). */
var _discordFile = null;
function openDiscordDialog(f) {
    if (isWip(f)) return;
    _discordFile = f;
    var dlg = document.getElementById('gal-discord-dialog');
    var prev = document.getElementById('gal-disc-preview');
    var src = _thumbCache.get(f.id) || f.thumbnailLink || '';
    prev.innerHTML = src
        ? '<img src="' + escapeAttr(src) + '" referrerpolicy="no-referrer" alt="" style="max-width:100%;max-height:200px;object-fit:contain;display:block;margin:0 auto;">'
        : '<div style="text-align:center;padding:20px;color:var(--text-muted);">Cargando preview…</div>';
    document.getElementById('gal-disc-text').value = '';
    var st = document.getElementById('gal-disc-status');
    st.textContent = ''; st.style.color = '';
    dlg.style.display = 'block';
    centerDialog(dlg);
}
function closeDiscordDialog() {
    document.getElementById('gal-discord-dialog').style.display = 'none';
    _discordFile = null;
}
async function submitDiscord() {
    if (!_discordFile) return;
    var f = _discordFile;
    var btn = document.getElementById('gal-disc-submit');
    var st  = document.getElementById('gal-disc-status');
    var cap = document.getElementById('gal-disc-text').value.trim();
    btn.classList.add('btn-busy'); btn.disabled = true;
    st.style.color = ''; st.textContent = 'Configurando permisos…';
    try {
        await _ensureDrivePublic(f.id);
        var publicUrl = 'https://drive.google.com/thumbnail?id=' + encodeURIComponent(f.id) + '&sz=w1920';
        st.textContent = 'Enviando a Discord…';
        var r = await fetch('../assets/profile/api.php?action=discord-publish', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_url: publicUrl, caption: cap })
        });
        var data = await r.json();
        if (data.error) throw new Error(data.error);
        st.style.color = ''; st.textContent = '✔ Publicado en Discord.';
        setTimeout(closeDiscordDialog, 900);
    } catch (e) {
        st.textContent = 'Error: ' + e.message;
        st.style.color = 'var(--error-text)';
    } finally {
        btn.classList.remove('btn-busy'); btn.disabled = false;
    }
}

/* ─── Ajustes de webhook de Discord ─────────────────────────── */
function openDiscordSettings() {
    var dlg = document.getElementById('gal-discord-settings-dialog');
    var input = document.getElementById('gal-ds-url');
    var st = document.getElementById('gal-ds-status');
    st.textContent = 'Cargando…'; st.style.color = '';
    input.value = '';
    dlg.style.display = 'block';
    centerDialog(dlg);
    fetch('../assets/profile/api.php?action=get-discord-webhook')
      .then(function(r) { return r.json(); })
      .then(function(d) {
          if (d && d.webhook) input.value = d.webhook;
          st.textContent = '';
      })
      .catch(function() { st.textContent = 'No se pudo cargar.'; st.style.color = 'var(--error-text)'; });
}
function closeDiscordSettings() {
    document.getElementById('gal-discord-settings-dialog').style.display = 'none';
}
async function submitDiscordSettings() {
    var btn = document.getElementById('gal-ds-submit');
    var st = document.getElementById('gal-ds-status');
    var url = document.getElementById('gal-ds-url').value.trim();
    btn.classList.add('btn-busy'); btn.disabled = true;
    st.style.color = ''; st.textContent = 'Guardando…';
    try {
        var r = await fetch('../assets/profile/api.php?action=save-discord-webhook', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ webhook: url })
        });
        var d = await r.json();
        if (d.error) throw new Error(d.error);
        st.textContent = '✔ Guardado.';
        setTimeout(closeDiscordSettings, 700);
    } catch (e) {
        st.textContent = 'Error: ' + e.message;
        st.style.color = 'var(--error-text)';
    } finally {
        btn.classList.remove('btn-busy'); btn.disabled = false;
    }
}

/* ─── Confirmar borrado ─────────────────────────────────── */
function confirmDelete(f) {
    var dlg = document.getElementById('gal-confirm-dialog');
    document.getElementById('gal-cf-text').innerHTML =
        '¿Eliminar <strong>' + escapeHtml(f.name) + '</strong> de tu Drive?<br>' +
        '<small style="color:var(--text-muted);">Esta acción no se puede deshacer.</small>';
    dlg.style.display = 'block';
    centerDialog(dlg);
    var yes = document.getElementById('gal-cf-yes');
    var no  = document.getElementById('gal-cf-no');
    var x   = document.getElementById('gal-cf-x');
    function cleanup() { yes.onclick = no.onclick = x.onclick = null; dlg.style.display = 'none'; }
    yes.onclick = async function() {
        cleanup();
        setStatus('Eliminando…');
        try {
            await deleteDriveFile(f.id);
            _files = _files.filter(function(x) { return x.id !== f.id; });
            if (_thumbCache.has(f.id)) { try { URL.revokeObjectURL(_thumbCache.get(f.id)); } catch (e) {} _thumbCache.delete(f.id); }
            renderTagSidebar();
            renderGrid();
            setStatus('');
        } catch (e) {
            setStatus('Error al eliminar: ' + e.message, true);
        }
    };
    no.onclick = cleanup;
    x.onclick = cleanup;
}

/* ─── Utilidades varias ─────────────────────────────────── */
function escapeHtml(s) {
    return String(s).replace(/[&<>"]/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
}
function escapeAttr(s) { return escapeHtml(s); }

function centerDialog(el) {
    el.style.position = 'fixed';
    el.style.left = '50%';
    el.style.top = '50%';
    el.style.transform = 'translate(-50%, -50%)';
    el.style.zIndex = '3000';
}

/* ─── Wiring inicial ────────────────────────────────────── */
document.getElementById('gal-connect-btn').addEventListener('click', function() {
    setStatus('Conectando…');
    ensureDriveAuth(function() { /* callback dispara showConnected vía token cb */ });
});
document.getElementById('gal-disconnect').addEventListener('click', disconnectDrive);
document.getElementById('gal-refresh').addEventListener('click', reload);
document.getElementById('gal-clear-filters').addEventListener('click', function() {
    _selectedTag = null;
    _searchTerm = '';
    document.getElementById('gal-search').value = '';
    renderTagSidebar();
    renderGrid();
});
document.getElementById('gal-search').addEventListener('input', function() {
    _searchTerm = this.value;
    renderGrid();
});

document.getElementById('gal-upload').addEventListener('click', openUploadDialog);
document.getElementById('gal-up-browse').addEventListener('click', function() {
    document.getElementById('gal-file-input').click();
});
document.getElementById('gal-file-input').addEventListener('change', function() {
    var f = this.files && this.files[0];
    if (!f) return;
    _selectedFile = f;
    document.getElementById('gal-up-filechosen').value = f.name;
    /* Sugerir nombre = nombre original sin extensión */
    var n = f.name;
    var dot = n.lastIndexOf('.');
    if (dot > 0) n = n.slice(0, dot);
    var nameInput = document.getElementById('gal-up-name');
    if (!nameInput.value.trim()) nameInput.value = n;
    /* Si es un fichero "WIP" (.psd/.csp/.kra) pre-rellenamos #wip
       visualmente en la caja de tags (el usuario puede quitarlo). */
    if (WIP_EXTS.indexOf(extOf(f.name)) !== -1) {
        var tagsInput = document.getElementById('gal-up-tags');
        var existing = parseTags(tagsInput.value);
        if (existing.indexOf(WIP_TAG) === -1) {
            tagsInput.value = ('#' + WIP_TAG + ' ' + tagsInput.value).trim();
            updateUploadTagChips();
        }
    }
});
document.getElementById('gal-up-tags').addEventListener('input', updateUploadTagChips);
document.getElementById('gal-up-close').addEventListener('click', closeUploadDialog);
document.getElementById('gal-up-cancel').addEventListener('click', closeUploadDialog);
document.getElementById('gal-up-submit').addEventListener('click', submitUpload);

/* Diálogo de edición */
document.getElementById('gal-ed-close').addEventListener('click', closeEditDialog);
document.getElementById('gal-ed-cancel').addEventListener('click', closeEditDialog);
document.getElementById('gal-ed-submit').addEventListener('click', submitEdit);

/* Diálogo de publicación */
document.getElementById('gal-pub-close').addEventListener('click', closePublishDialog);
document.getElementById('gal-pub-cancel').addEventListener('click', closePublishDialog);
document.getElementById('gal-pub-submit').addEventListener('click', submitPublish);

/* Diálogo de Discord */
document.getElementById('gal-disc-close').addEventListener('click', closeDiscordDialog);
document.getElementById('gal-disc-cancel').addEventListener('click', closeDiscordDialog);
document.getElementById('gal-disc-submit').addEventListener('click', submitDiscord);

/* Ajustes de Discord (webhook) */
document.getElementById('gal-discord-settings').addEventListener('click', openDiscordSettings);
document.getElementById('gal-ds-close').addEventListener('click', closeDiscordSettings);
document.getElementById('gal-ds-cancel').addEventListener('click', closeDiscordSettings);
document.getElementById('gal-ds-submit').addEventListener('click', submitDiscordSettings);
document.getElementById('gal-ed-tags').addEventListener('input', updateEditTagChips);

/* Menú contextual: acciones por opción */
document.getElementById('gal-ctx-menu').addEventListener('click', function(e) {
    var opt = e.target.closest('.gal-ctx-opt');
    if (!opt || !_ctxFile) return;
    var act = opt.dataset.act;
    var f = _ctxFile;
    closeCtxMenu();
    if      (act === 'preview')  openPreview(f);
    else if (act === 'download') downloadToUser(f);
    else if (act === 'publish')  openPublishDialog(f);
    else if (act === 'discord')  openDiscordDialog(f);
    else if (act === 'edit')     openEditDialog(f);
    else if (act === 'delete')   confirmDelete(f);
});
/* Cerrar el menú con clic fuera o Esc */
document.addEventListener('click', function(e) {
    if (!e.target.closest('#gal-ctx-menu')) closeCtxMenu();
});
document.addEventListener('contextmenu', function(e) {
    if (!e.target.closest('.gal-card') && !e.target.closest('#gal-ctx-menu')) closeCtxMenu();
});

document.getElementById('gal-preview-close').addEventListener('click', closePreview);
document.getElementById('gal-preview').addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    if (document.getElementById('gal-ctx-menu').style.display !== 'none')     { closeCtxMenu(); return; }
    if (document.getElementById('gal-publish-dialog').style.display !== 'none') { closePublishDialog(); return; }
    if (document.getElementById('gal-discord-dialog').style.display !== 'none') { closeDiscordDialog(); return; }
    if (document.getElementById('gal-discord-settings-dialog').style.display !== 'none') { closeDiscordSettings(); return; }
    if (document.getElementById('gal-edit-dialog').style.display !== 'none')  { closeEditDialog(); return; }
    if (document.getElementById('gal-preview').style.display !== 'none')      { closePreview(); return; }
});

/* Cuando GIS termina de cargar intenta auto-conectar (si ya hubo consentimiento) */
window.addEventListener('load', tryAutoConnectDrive);
</script>

</body>
</html>
