<?php
// perfil.php - Profile window + all profile-related dialogs
// Included from desktop-base.php; expects $desktopLabel already set.
?>
<!-- PROFILE WINDOW -->
<div class="window" id="profile-window">
    <div class="title-bar">
        <div class="title-bar-text">👤 <?php echo htmlspecialchars($desktopLabel); ?></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="profile-close"></button>
        </div>
    </div>
    <div class="window-body" id="profile-body">
        <!-- SIDEBAR IZQUIERDO -->
        <div id="profile-sidebar">
            <div class="profile-sidebar-heading">Mis Listas</div>
            <div class="profile-nav-item" data-cat="movies">
                <span class="profile-nav-icon">🎬</span>
                <span class="profile-nav-label">Películas</span>
                <span class="profile-nav-count" id="profile-count-movies">—</span>
            </div>
            <div class="profile-nav-item" data-cat="books">
                <span class="profile-nav-icon">📚</span>
                <span class="profile-nav-label">Libros</span>
                <span class="profile-nav-count" id="profile-count-books">—</span>
            </div>
            <div class="profile-nav-item" data-cat="games">
                <span class="profile-nav-icon">🎮</span>
                <span class="profile-nav-label">Videojuegos</span>
                <span class="profile-nav-count" id="profile-count-games">—</span>
            </div>
            <div class="profile-nav-item" data-cat="music">
                <span class="profile-nav-icon">🎵</span>
                <span class="profile-nav-label">Música</span>
                <span class="profile-nav-count" id="profile-count-music">—</span>
            </div>
            <div id="profile-sidebar-back">
                <button class="button" id="profile-catview-back">← Volver</button>
            </div>
        </div>
        <!-- CONTENIDO PRINCIPAL -->
        <div id="profile-main">
            <!-- VISTA POR DEFECTO -->
            <div id="profile-view-default">
                <div id="profile-top">
                    <div id="profile-avatar-col">
                        <div class="profile-avatar-frame">
                            <?php $profileImg = getUserImage($desktopLabel); ?>
                            <?php if ($profileImg): ?>
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="" class="profile-avatar-img">
                            <?php else: ?>
                            <div class="profile-avatar-placeholder">👤</div>
                            <?php endif; ?>
                        </div>
                        <div id="profile-username"><?php echo htmlspecialchars($desktopLabel); ?></div>
                    </div>
                    <div id="profile-info-col">
                        <div id="profile-info-bio" class="pinfo-bio"></div>
                        <div id="profile-info-meta"></div>
                        <div id="profile-info-links"></div>
                        <button class="button" id="profile-info-edit-btn" style="font-size:9px;align-self:flex-start;margin-top:5px;">✏ Editar perfil</button>
                    </div>
                </div>
                <div id="profile-posts-area">
                    <div id="profile-posts-header">Posts</div>
                    <div id="profile-new-post">
                        <textarea id="profile-post-input" placeholder="Escribe algo..."></textarea>
                        <button class="button" id="profile-post-btn">Publicar</button>
                    </div>
                    <div id="profile-posts-list"></div>
                </div>
            </div>
            <!-- VISTA DE CATEGORÍA -->
            <div id="profile-view-cat">
                <div id="profile-catview-topbar">
                    <div id="profile-catview-avatar-wrap">
                        <div class="profile-avatar-frame">
                            <?php if (isset($profileImg) && $profileImg): ?>
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="" class="profile-avatar-img">
                            <?php else: ?>
                            <div class="profile-avatar-placeholder">👤</div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-catview-username"><?php echo htmlspecialchars($desktopLabel); ?></div>
                    </div>
                    <div id="profile-catview-encurso">
                        <div class="profile-encurso-heading">▶ En curso</div>
                        <div id="profile-catview-encurso-slots"></div>
                    </div>
                </div>
                <div class="profile-cat-toolbar">
                    <span id="profile-catview-title">🎬 Películas</span>
                    <button class="button" id="profile-catview-add-btn">+ Añadir</button>
                </div>
                <div id="profile-catview-sections">
                    <div class="profile-catview-section">
                        <div class="profile-catview-section-head">Pendientes</div>
                        <div class="profile-gallery" id="profile-catview-pending"></div>
                    </div>
                    <div class="profile-catview-section">
                        <div class="profile-catview-section-head" id="profile-catview-done-head">Vistas</div>
                        <div class="profile-gallery" id="profile-catview-done"></div>
                    </div>
                </div>
            </div>
            <!-- MUSIC VIEW -->
            <div id="profile-view-music">
                <div id="music-catview-topbar">
                    <div id="music-catview-avatar-wrap">
                        <div class="profile-avatar-frame">
                            <?php if (isset($profileImg) && $profileImg): ?>
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="" class="profile-avatar-img">
                            <?php else: ?>
                            <div class="profile-avatar-placeholder">👤</div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-catview-username"><?php echo htmlspecialchars($desktopLabel); ?></div>
                    </div>
                    <div id="music-catview-destacados">
                        <div class="profile-encurso-heading">★ Destacados</div>
                        <div id="music-catview-destacados-slots"></div>
                    </div>
                </div>
                <div class="profile-cat-toolbar">
                    <span id="music-catview-title">🎵 Música</span>
                    <button class="button" id="music-catview-add-btn">+ Añadir</button>
                </div>
                <div id="music-tab-bar">
                    <button class="button music-tab" data-tab="albums">Álbumes</button>
                    <button class="button music-tab" data-tab="songs">Canciones</button>
                </div>
                <div id="music-list-wrap">
                    <div id="music-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PROFILE ADD DIALOG -->
<div class="window" id="profile-add-dialog">
    <div class="title-bar">
        <div class="title-bar-text" id="profile-add-dialog-title">+ Añadir</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-add-dialog-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label for="profile-add-name">Nombre</label>
            <input type="text" id="profile-add-name" placeholder="Nombre...">
        </div>
        <div class="field-row-stacked" style="margin-top:8px;">
            <label for="profile-add-image">Imagen (URL)</label>
            <input type="text" id="profile-add-image" placeholder="https://...">
        </div>
        <p id="profile-add-error" style="color:#c00;font-size:10px;margin:6px 0 0;min-height:14px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:4px;">
            <button class="button" id="profile-add-dialog-cancel">Cancelar</button>
            <button class="button" id="profile-add-dialog-submit">Añadir</button>
        </div>
    </div>
</div>

<!-- REVIEW VIEW WINDOW -->
<div class="window" id="profile-review-view" style="display:none;position:fixed;z-index:10002;width:300px;">
    <div class="title-bar">
        <div class="title-bar-text">★ Review</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-review-view-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:0;">
        <div style="padding:16px 18px 14px;">
            <div id="profile-review-view-comment"></div>
            <div id="profile-review-view-header"></div>
        </div>
    </div>
</div>

<!-- REVIEW PROMPT -->
<div class="window" id="profile-review-prompt" style="display:none;position:fixed;z-index:10002;width:270px;">
    <div class="title-bar">
        <div class="title-bar-text">¿Añadir una review?</div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <p id="profile-review-prompt-msg" style="margin:0 0 12px;font-size:11px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;">
            <button class="button" id="profile-review-prompt-no">No, gracias</button>
            <button class="button" id="profile-review-prompt-yes">Sí</button>
        </div>
    </div>
</div>

<!-- REVIEW WINDOW -->
<div class="window" id="profile-review-window" style="display:none;position:fixed;z-index:10002;width:290px;">
    <div class="title-bar">
        <div class="title-bar-text" id="profile-review-window-title">Review</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-review-window-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <div style="display:flex;align-items:center;margin-bottom:10px;">
            <div id="profile-review-stars" style="font-size:26px;letter-spacing:4px;"></div>
            <span id="profile-review-stars-num" style="font-size:14px;margin-left:10px;min-width:2em;font-weight:bold;"></span>
        </div>
        <div class="field-row-stacked" style="margin-bottom:10px;">
            <label for="profile-review-comment" style="font-size:11px;margin-bottom:3px;">Comentario (opcional)</label>
            <textarea id="profile-review-comment" rows="4" style="resize:vertical;width:100%;box-sizing:border-box;" placeholder="Escribe tu opinión..."></textarea>
        </div>
        <div class="field-row" style="justify-content:flex-end;gap:4px;">
            <button class="button" id="profile-review-window-cancel">Cancelar</button>
            <button class="button" id="profile-review-window-submit">Guardar</button>
        </div>
    </div>
</div>

<!-- PROFILE INVITE DIALOG -->
<div class="window" id="profile-invite-dialog" style="display:none;position:fixed;z-index:10002;width:260px;">
    <div class="title-bar">
        <div class="title-bar-text">👥 Colaboradores</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-invite-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p id="profile-invite-msg" style="margin:0 0 6px;font-size:11px;"></p>
        <div id="profile-invite-users"></div>
        <p id="profile-invite-status"></p>
        <div class="field-row" style="justify-content:flex-end;margin-top:6px;">
            <button class="button" id="profile-invite-cancel">Cerrar</button>
        </div>
    </div>
</div>


<!-- PROFILE INFO EDIT DIALOG -->
<div class="window" id="profile-info-edit-dialog" style="display:none;position:fixed;z-index:10002;width:300px;">
    <div class="title-bar">
        <div class="title-bar-text">✏ Editar perfil</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-info-edit-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <div class="field-row-stacked">
            <label for="pinfo-bio" style="font-size:10px;">Bio</label>
            <textarea id="pinfo-bio" rows="2" maxlength="200" placeholder="Cuéntanos algo sobre ti..." style="resize:none;width:100%;box-sizing:border-box;font-size:10px;"></textarea>
        </div>
        <div class="field-row" style="gap:6px;margin-top:6px;">
            <div class="field-row-stacked" style="flex:1;min-width:0;">
                <label for="pinfo-pronouns" style="font-size:10px;">Pronombres</label>
                <input type="text" id="pinfo-pronouns" maxlength="30" placeholder="él/ella/elle..." style="width:100%;box-sizing:border-box;">
            </div>
            <div class="field-row-stacked" style="flex:1;min-width:0;">
                <label for="pinfo-age" style="font-size:10px;">Edad</label>
                <input type="number" id="pinfo-age" min="1" max="120" placeholder="..." style="width:100%;box-sizing:border-box;">
            </div>
        </div>
        <div class="field-row-stacked" style="margin-top:6px;">
            <label for="pinfo-country" style="font-size:10px;">País</label>
            <input type="text" id="pinfo-country" maxlength="50" placeholder="España..." style="width:100%;box-sizing:border-box;">
        </div>
        <div style="margin-top:8px;font-size:10px;font-weight:bold;border-bottom:1px solid #808080;padding-bottom:2px;margin-bottom:5px;">Redes sociales</div>
        <div class="field-row-stacked" style="margin-top:4px;">
            <label for="pinfo-steam" style="font-size:10px;">🎮 Steam (URL o usuario)</label>
            <input type="text" id="pinfo-steam" maxlength="200" placeholder="https://steamcommunity.com/id/..." style="width:100%;box-sizing:border-box;">
        </div>
        <div class="field-row-stacked" style="margin-top:4px;">
            <label for="pinfo-discord" style="font-size:10px;">💬 Discord (usuario)</label>
            <input type="text" id="pinfo-discord" maxlength="100" placeholder="usuario o usuario#1234" style="width:100%;box-sizing:border-box;">
        </div>
        <div class="field-row-stacked" style="margin-top:4px;">
            <label for="pinfo-twitter" style="font-size:10px;">🐦 Twitter / X</label>
            <input type="text" id="pinfo-twitter" maxlength="100" placeholder="@usuario" style="width:100%;box-sizing:border-box;">
        </div>
        <div class="field-row-stacked" style="margin-top:4px;">
            <label for="pinfo-instagram" style="font-size:10px;">📷 Instagram</label>
            <input type="text" id="pinfo-instagram" maxlength="100" placeholder="@usuario" style="width:100%;box-sizing:border-box;">
        </div>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:10px;">
            <button class="button" id="profile-info-edit-cancel">Cancelar</button>
            <button class="button" id="profile-info-edit-save">Guardar</button>
        </div>
    </div>
</div>

<!-- MUSIC ADD DIALOG -->
<div class="window" id="music-add-dialog" style="display:none;position:fixed;z-index:10002;width:320px;">
    <div class="title-bar">
        <div class="title-bar-text" id="music-add-dialog-title">+ Añadir música</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="music-add-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <div id="music-add-step1">
            <p style="margin:0 0 10px;font-size:11px;">¿Qué quieres añadir?</p>
            <div class="field-row" style="gap:6px;">
                <button class="button" id="music-add-type-song" style="flex:1;">🎵 Canción</button>
                <button class="button" id="music-add-type-album" style="flex:1;">💿 Álbum / Playlist</button>
            </div>
        </div>
        <div id="music-add-step2" style="display:none;">
            <div class="field-row-stacked">
                <label id="music-add-url-label" for="music-add-url">Enlace de YouTube o Spotify</label>
                <input type="text" id="music-add-url" placeholder="https://..." style="width:100%;box-sizing:border-box;">
            </div>
            <div id="music-add-preview" style="min-height:18px;font-size:10px;margin:4px 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></div>
            <div class="field-row-stacked" style="margin-top:4px;">
                <label for="music-add-artist">Artista</label>
                <input type="text" id="music-add-artist" placeholder="Artista..." style="width:100%;box-sizing:border-box;">
            </div>
            <p id="music-add-error" style="color:#c00;font-size:10px;margin:3px 0 0;min-height:14px;"></p>
            <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
                <button class="button" id="music-add-back">← Atrás</button>
                <button class="button" id="music-add-cancel">Cancelar</button>
                <button class="button" id="music-add-submit">Siguiente →</button>
            </div>
        </div>
        <div id="music-add-step3" style="display:none;">
            <div id="music-add-review-prompt">
                <p style="margin:0 0 10px;font-size:11px;">¿Quieres añadir una reseña?</p>
                <div class="field-row" style="justify-content:flex-end;gap:4px;">
                    <button class="button" id="music-add-review-no">No, guardar</button>
                    <button class="button" id="music-add-review-yes">★ Sí</button>
                </div>
            </div>
            <div id="music-add-review-form" style="display:none;">
                <div style="display:flex;align-items:center;margin-bottom:8px;">
                    <div id="music-add-review-stars" style="font-size:24px;letter-spacing:3px;cursor:pointer;user-select:none;-webkit-user-select:none;"></div>
                    <span id="music-add-review-stars-num" style="font-size:13px;margin-left:10px;min-width:2em;font-weight:bold;"></span>
                </div>
                <div class="field-row-stacked" style="margin-bottom:4px;">
                    <label for="music-add-review-comment" style="font-size:11px;margin-bottom:3px;">Comentario (opcional)</label>
                    <textarea id="music-add-review-comment" rows="3" style="resize:vertical;width:100%;box-sizing:border-box;" placeholder="Tu opinión..."></textarea>
                </div>
                <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:8px;">
                    <button class="button" id="music-add-review-back2">← Atrás</button>
                    <button class="button" id="music-add-review-save">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* =========================
   PROFILE
========================= */
var PROFILE_USERS = <?php
    $udata = [];
    foreach ($loginUsers as $k => $u) {
        $udata[$k] = ['label' => $u['label'], 'image' => getUserImage($u['label'])];
    }
    echo json_encode($udata);
?>;

(function() {
    var profileWin = document.getElementById('profile-window');
    if (!profileWin) return;

    var lists        = { movies: [], books: [], games: [], music: [] };
    var loaded       = false;
    var addDialogCat = null;
    var currentCat   = null;
    var currentMusicTab = 'albums';

    var CATS = {
        movies: { label: 'Películas',   icon: '🎬' },
        books:  { label: 'Libros',      icon: '📚' },
        games:  { label: 'Videojuegos', icon: '🎮' },
        music:  { label: 'Música',      icon: '🎵' }
    };

    var DONE_LABELS = { movies: 'Vistas', books: 'Leídas', games: 'Jugadas' };
    var CAT_VERBS   = { movies: 'ver', books: 'leer', games: 'jugar', music: 'escuchar' };

    var STATUS_CYCLE  = ['pending', 'in-progress', 'completed'];
    var STATUS_LABELS = {
        'pending':     '○ Pendiente',
        'in-progress': '◑ En curso',
        'completed':   '● Completado'
    };

    var confirmFn = window.win98Confirm || function(msg, title, cb) { if (confirm(msg)) cb(); };

    /* ──── Data ──── */
    function loadLists(cb) {
        fetch('assets/profile/get-lists.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && !data.error) {
                    ['movies', 'books', 'games', 'music'].forEach(function(k) {
                        lists[k] = Array.isArray(data[k]) ? data[k] : [];
                    });
                }
                if (cb) cb();
            })
            .catch(function() { if (cb) cb(); });
    }

    function saveCategory(cat) {
        fetch('assets/profile/save-lists.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: lists[cat] })
        }).catch(function() {});
    }

    function updateCounts() {
        ['movies', 'books', 'games'].forEach(function(cat) {
            var el = document.getElementById('profile-count-' + cat);
            if (el) el.textContent = lists[cat].length;
        });
        var musicEl = document.getElementById('profile-count-music');
        if (musicEl) musicEl.textContent = lists.music.length;
    }

    /* ──── Context menu ──── */
    var _ctxMenu = null;

    function showCtxMenu(x, y, options) {
        hideCtxMenu();
        var menu = document.createElement('div');
        menu.className = 'profile-ctx-menu';
        menu.style.left = x + 'px';
        menu.style.top  = y + 'px';
        options.forEach(function(opt) {
            var el = document.createElement('div');
            el.className = 'profile-ctx-option' + (opt.disabled ? ' disabled' : '');
            el.textContent = opt.label;
            if (!opt.disabled) {
                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    hideCtxMenu();
                    opt.action();
                });
            }
            menu.appendChild(el);
        });
        document.body.appendChild(menu);
        _ctxMenu = menu;
    }

    function hideCtxMenu() {
        if (_ctxMenu) { _ctxMenu.remove(); _ctxMenu = null; }
    }

    document.addEventListener('mousedown', function(e) {
        if (_ctxMenu && !_ctxMenu.contains(e.target)) hideCtxMenu();
    });

    /* ──── En curso: 3 slots ──── */
    function renderCatEncurso(cat) {
        var slotsEl = document.getElementById('profile-catview-encurso-slots');
        if (!slotsEl) return;
        slotsEl.innerHTML = '';
        var inProgress = (lists[cat] || []).filter(function(i) { return i.status === 'in-progress'; });
        for (var s = 0; s < 3; s++) {
            var item = inProgress[s] || null;
            var slot = document.createElement('div');
            slot.className = 'profile-encurso-slot' + (item ? ' filled' : '');

            var tb = document.createElement('div');
            tb.className = 'profile-encurso-slot-tb';
            tb.textContent = item ? item.title : '—';
            slot.appendChild(tb);

            var body = document.createElement('div');
            body.className = 'profile-encurso-slot-body';
            body.style.position = 'relative';
            if (item && item.image) {
                var img = document.createElement('img');
                img.src = item.image;
                img.alt = item.title;
                (function(b) {
                    img.onerror = function() { b.textContent = '🖼'; };
                })(body);
                body.appendChild(img);
            } else if (item) {
                body.textContent = '🖼';
            }
            if (item && item.collaborators && item.collaborators.length) {
                var strip = document.createElement('div');
                strip.className = 'profile-gallery-collabs';
                item.collaborators.forEach(function(uKey) {
                    var uInfo = PROFILE_USERS[uKey];
                    if (!uInfo) return;
                    var avFrame = document.createElement('div');
                    avFrame.className = 'profile-avatar-frame';
                    avFrame.title = uInfo.label;
                    if (uInfo.image) {
                        var avImg = document.createElement('img');
                        avImg.src = uInfo.image;
                        avImg.alt = uInfo.label;
                        avFrame.appendChild(avImg);
                    }
                    strip.appendChild(avFrame);
                });
                body.appendChild(strip);
            } else if (item && item.sharedFrom && PROFILE_USERS[item.sharedFrom]) {
                var strip = document.createElement('div');
                strip.className = 'profile-gallery-collabs';
                var hostInfo = PROFILE_USERS[item.sharedFrom];
                var avFrame = document.createElement('div');
                avFrame.className = 'profile-avatar-frame';
                avFrame.title = hostInfo.label;
                if (hostInfo.image) {
                    var avImg = document.createElement('img');
                    avImg.src = hostInfo.image;
                    avImg.alt = hostInfo.label;
                    avFrame.appendChild(avImg);
                }
                strip.appendChild(avFrame);
                body.appendChild(strip);
            }
            slot.appendChild(body);

            if (item) {
                (function(it) {
                    slot.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                        showCtxMenu(e.clientX, e.clientY, [
                            { label: '✓ Completar', action: function() {
                                var idx = lists[cat].indexOf(it);
                                if (idx !== -1) {
                                    lists[cat][idx].status = 'completed';
                                    saveCategory(cat);
                                    renderCatView(cat);
                                    renderCatEncurso(cat);
                                    showReviewPrompt(cat, idx);
                                }
                            }},
                            { label: '✕ Quitar de en curso', action: function() {
                                var idx = lists[cat].indexOf(it);
                                if (idx !== -1) {
                                    lists[cat][idx].status = 'pending';
                                    saveCategory(cat);
                                    renderCatView(cat);
                                    renderCatEncurso(cat);
                                }
                            }},
                            { label: '👥 Colaboradores', action: function() {
                                showCollabDialog(cat, it);
                            }}
                        ]);
                    });
                })(item);
            }

            slotsEl.appendChild(slot);
        }
    }

    /* ──── Review ──── */
    function showReviewPrompt(cat, itemIdx) {
        var prompt = document.getElementById('profile-review-prompt');
        var item = lists[cat][itemIdx];
        document.getElementById('profile-review-prompt-msg').textContent =
            '¿Quieres añadir una review para "' + item.title + '"?';
        prompt.style.display = 'block';
        prompt.style.left = Math.round((window.innerWidth  - prompt.offsetWidth)  / 2) + 'px';
        prompt.style.top  = Math.round((window.innerHeight - prompt.offsetHeight) / 2) + 'px';

        var yesBtn = document.getElementById('profile-review-prompt-yes');
        var noBtn  = document.getElementById('profile-review-prompt-no');
        var newYes = yesBtn.cloneNode(true); yesBtn.parentNode.replaceChild(newYes, yesBtn);
        var newNo  = noBtn.cloneNode(true);  noBtn.parentNode.replaceChild(newNo,  noBtn);

        newNo.addEventListener('click', function() { prompt.style.display = 'none'; });
        newYes.addEventListener('click', function() {
            prompt.style.display = 'none';
            showReviewWindow(cat, itemIdx);
        });
    }

    /* ──── Invite system (usa notifSystem unificado) ──── */
    var itemNotifEs        = null;
    var currentSessionUser = <?php echo json_encode($desktopUserKey); ?>;

    function postItemAction(id, action) {
        return fetch('assets/profile/respond-item-invite.php', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ inviteId: id, action: action })
        });
    }

    var ITEM_TITLES = {
        'invite':         'Invitación',
        'item-accepted':  'Aceptado',
        'item-rejected':  'Rechazado',
        'collab-left':    'Colaboración',
        'collab-removed': 'Eliminado'
    };

    function pushItemNotif(notif) {
        if (!window.notifSystem || window.notifSystem.isShown(notif.id) || window.notifSystem.isDismissed(notif.id)) return;
        var isAction = notif.type === 'invite';
        var verb = CAT_VERBS[notif.category] || 'ver';
        var msg;
        if (notif.type === 'invite')              msg = notif.fromLabel + ' te ha invitado a ' + verb + ' "' + notif.itemTitle + '"';
        else if (notif.type === 'item-accepted')  msg = notif.fromLabel + ' ha aceptado tu invitación a ' + verb + ' "' + notif.itemTitle + '"';
        else if (notif.type === 'item-rejected')  msg = notif.fromLabel + ' ha rechazado tu invitación a ' + verb + ' "' + notif.itemTitle + '"';
        else if (notif.type === 'collab-left')    msg = notif.fromLabel + ' ha abandonado "' + notif.itemTitle + '"';
        else if (notif.type === 'collab-removed') msg = 'Has sido eliminado de "' + notif.itemTitle + '"';
        else msg = '';

        if (isAction) {
            var senderImg = (notif.fromUser && PROFILE_USERS[notif.fromUser]) ? PROFILE_USERS[notif.fromUser].image : null;
            window.notifSystem.show({
                id:          notif.id,
                type:        'action',
                title:       ITEM_TITLES[notif.type] || 'Notificación',
                message:     msg,
                senderImage: senderImg,
                sentAt:      notif.sentAt,
                onAccept: function() {
                    postItemAction(notif.id, 'accept')
                        .then(function(r) { return r.json(); })
                        .then(function(d) { if (d && d.ok) loadLists(function() { updateCounts(); reloadCurrentView(); }); })
                        .catch(function(){});
                },
                onReject: function() { postItemAction(notif.id, 'reject').catch(function(){}); }
            });
        } else {
            /* Info: dismiss en servidor de inmediato y refrescar listas si aplica */
            postItemAction(notif.id, 'dismiss').catch(function(){});
            if (notif.type === 'item-accepted' || notif.type === 'collab-left' || notif.type === 'collab-removed') {
                loadLists(function() { updateCounts(); reloadCurrentView(); });
            }
            window.notifSystem.show({
                id:      notif.id,
                type:    'info',
                title:   ITEM_TITLES[notif.type] || 'Notificación',
                message: msg,
                sentAt:  notif.sentAt
            });
        }
    }

    function startItemNotifStream() {
        if (itemNotifEs) return;
        var es = new EventSource('assets/profile/item-notifications-stream.php');
        itemNotifEs = es;
        es.onmessage = function(e) {
            var items = [];
            try { items = JSON.parse(e.data); } catch(err) { return; }
            if (!Array.isArray(items)) return;
            items.forEach(pushItemNotif);
        };
        es.onerror = function() { es.close(); itemNotifEs = null; setTimeout(startItemNotifStream, 5000); };
    }

    function makeAvRow(uKey, uInfo, btnLabel, btnSetup) {
        var row = document.createElement('div');
        row.className = 'collab-user-row';
        var avWrap = document.createElement('div');
        avWrap.className = 'collab-avatar-wrap';
        if (uInfo.image) {
            var avImg = document.createElement('img');
            avImg.className = 'collab-avatar-img';
            avImg.src = uInfo.image;
            avImg.alt = uInfo.label;
            avWrap.appendChild(avImg);
        }
        row.appendChild(avWrap);
        var lbl = document.createElement('span');
        lbl.style.cssText = 'flex:1;';
        lbl.textContent = uInfo.label;
        row.appendChild(lbl);
        if (btnLabel && btnSetup) {
            var btn = document.createElement('button');
            btn.className = 'button collab-invite-btn';
            btn.textContent = btnLabel;
            btnSetup(btn);
            row.appendChild(btn);
        }
        return row;
    }

    function collabSectionTitle(text) {
        var el = document.createElement('div');
        el.style.cssText = 'font-size:10px;color:#666;padding:4px 0 2px;';
        el.textContent = text;
        return el;
    }

    function showCollabDialog(cat, item) {
        var dlg      = document.getElementById('profile-invite-dialog');
        var msg      = document.getElementById('profile-invite-msg');
        var list     = document.getElementById('profile-invite-users');
        var statusEl = document.getElementById('profile-invite-status');
        var isCollab = !!item.sharedFrom;
        msg.textContent   = '"' + item.title + '"';
        statusEl.textContent = '';
        list.innerHTML = '';

        if (isCollab) {
            var ownerKey  = item.sharedFrom;
            var ownerInfo = PROFILE_USERS[ownerKey];
            if (ownerInfo) {
                list.appendChild(collabSectionTitle('Host'));
                list.appendChild(makeAvRow(ownerKey, ownerInfo, null, null));
            }
            var othersSec = document.createElement('div');
            list.appendChild(othersSec);
            fetch('assets/profile/get-item-collabs.php?category=' + encodeURIComponent(cat) + '&itemId=' + encodeURIComponent(item.id))
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    var others = (d.collaborators || []).filter(function(k) { return k !== currentSessionUser; });
                    if (!others.length) return;
                    othersSec.appendChild(collabSectionTitle('Otros colaboradores'));
                    others.forEach(function(uKey) {
                        var uInfo = PROFILE_USERS[uKey];
                        if (uInfo) othersSec.appendChild(makeAvRow(uKey, uInfo, null, null));
                    });
                })
                .catch(function() {});
        } else {
            var existingCollabs = item.collaborators || [];
            if (existingCollabs.length) {
                list.appendChild(collabSectionTitle('Colaboradores'));
                existingCollabs.forEach(function(uKey) {
                    var uInfo = PROFILE_USERS[uKey];
                    if (!uInfo) return;
                    list.appendChild(makeAvRow(uKey, uInfo, 'Eliminar', (function(cKey, cLabel) {
                        return function(btn) {
                            btn.addEventListener('click', function() {
                                confirmFn('¿Eliminar a ' + cLabel + ' como colaborador?', 'Eliminar colaborador', function() {
                                    statusEl.textContent = 'Eliminando…';
                                    fetch('assets/profile/leave-collab.php', {
                                        method: 'POST', headers: {'Content-Type':'application/json'},
                                        body: JSON.stringify({ action: 'remove', category: cat, itemId: item.id, collaboratorUser: cKey })
                                    }).then(function(r) { return r.json(); }).then(function(d) {
                                        if (d.ok) {
                                            loadLists(function() {
                                                updateCounts();
                                                if (cat === 'music') { renderMusicView(currentMusicTab); renderMusicDestacados(); }
                                                else { renderCatView(cat); renderCatEncurso(cat); }
                                                var updated = null;
                                                (lists[cat] || []).forEach(function(i) { if (i.id === item.id) updated = i; });
                                                if (updated) showCollabDialog(cat, updated);
                                                else dlg.style.display = 'none';
                                            });
                                        }
                                    }).catch(function(){ statusEl.textContent = 'Error al eliminar.'; });
                                });
                            });
                        };
                    })(uKey, uInfo.label)));
                });
            }

            var invitable = Object.keys(PROFILE_USERS).filter(function(k) {
                return k !== currentSessionUser && existingCollabs.indexOf(k) === -1;
            });
            if (invitable.length) {
                if (existingCollabs.length) {
                    var sep = document.createElement('hr');
                    sep.style.cssText = 'margin:4px 0;border:none;border-top:1px solid #dfdfdf;';
                    list.appendChild(sep);
                }
                list.appendChild(collabSectionTitle(existingCollabs.length ? 'Invitar más' : 'Invitar'));
                invitable.forEach(function(uKey) {
                    var uInfo = PROFILE_USERS[uKey];
                    list.appendChild(makeAvRow(uKey, uInfo, 'Invitar', (function(key) {
                        return function(btn) {
                            btn.addEventListener('click', function() {
                                btn.disabled = true; btn.textContent = '…';
                                statusEl.textContent = 'Enviando…';
                                fetch('assets/profile/send-item-invite.php', {
                                    method: 'POST', headers: {'Content-Type':'application/json'},
                                    body: JSON.stringify({ toUser: key, category: cat, itemId: item.id })
                                }).then(function(r) { return r.json(); }).then(function(d) {
                                    if (d.ok) {
                                        var sent = document.createElement('span');
                                        sent.style.cssText = 'font-size:10px;color:#008000;font-weight:bold;';
                                        sent.textContent = '✓ Invitado';
                                        btn.parentNode.replaceChild(sent, btn);
                                        statusEl.textContent = 'Invitación enviada.';
                                    } else {
                                        btn.disabled = false; btn.textContent = 'Invitar';
                                        statusEl.textContent = d.error || 'Error al enviar.';
                                    }
                                }).catch(function() { btn.disabled = false; btn.textContent = 'Invitar'; statusEl.textContent = ''; });
                            });
                        };
                    })(uKey)));
                });
            } else if (!existingCollabs.length) {
                list.appendChild(collabSectionTitle('No hay otros usuarios disponibles'));
            }
        }

        dlg.style.display = 'block';
        dlg.style.left = Math.round((window.innerWidth  - dlg.offsetWidth)  / 2) + 'px';
        dlg.style.top  = Math.round((window.innerHeight - dlg.offsetHeight) / 2) + 'px';
        var closeBtn = document.getElementById('profile-invite-close');
        var newClose = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newClose, closeBtn);
        newClose.addEventListener('click', function() { dlg.style.display = 'none'; });
        var cancelBtn = document.getElementById('profile-invite-cancel');
        var newCancel = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);
        newCancel.addEventListener('click', function() { dlg.style.display = 'none'; });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function makeStarsHtml(val, total) {
        var h = '';
        for (var i = 1; i <= total; i++) {
            if (val >= i) {
                h += '<span>★</span>';
            } else if (val >= i - 0.5) {
                h += '<span style="display:inline-block;clip-path:inset(0 50% 0 0);">★</span>';
            } else {
                h += '<span>☆</span>';
            }
        }
        return h;
    }

    function showReviewView(review) {
        var win = document.getElementById('profile-review-view');
        var username = (document.getElementById('profile-username') || {}).textContent || 'Usuario';
        document.getElementById('profile-review-view-comment').textContent = review.comment ? '" ' + review.comment + ' "' : '';
        document.getElementById('profile-review-view-header').innerHTML = '— ' + escHtml(username) + '  —  ' + makeStarsHtml(review.stars, 5) + '<span style="font-size:11px;margin-left:4px;vertical-align:middle;">' + review.stars + '</span>';
        win.style.display = 'block';
        win.style.left = Math.round((window.innerWidth  - win.offsetWidth)  / 2) + 'px';
        win.style.top  = Math.round((window.innerHeight - win.offsetHeight) / 2) + 'px';
        var closeBtn = document.getElementById('profile-review-view-close');
        var newClose = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newClose, closeBtn);
        newClose.addEventListener('click', function() { win.style.display = 'none'; });
    }

    function showReviewWindow(cat, itemIdx) {
        var win     = document.getElementById('profile-review-window');
        var starsEl = document.getElementById('profile-review-stars');
        var commentEl = document.getElementById('profile-review-comment');
        var item = lists[cat][itemIdx];
        document.getElementById('profile-review-window-title').textContent = '⭐ ' + item.title;
        commentEl.value = (item.review && item.review.comment) ? item.review.comment : '';
        var sel = (item.review && item.review.stars) ? item.review.stars : 0;

        var numEl = document.getElementById('profile-review-stars-num');
        function setStarDisp(el, val, pos) {
            if (val >= pos) { el.innerHTML = '★'; el.style.clipPath = ''; el.style.opacity = ''; }
            else if (val >= pos - 0.5) { el.innerHTML = '★'; el.style.clipPath = 'inset(0 50% 0 0)'; el.style.opacity = ''; }
            else { el.innerHTML = '☆'; el.style.clipPath = ''; el.style.opacity = ''; }
        }
        function drawStars() {
            starsEl.innerHTML = '';
            numEl.textContent = sel > 0 ? sel : '';
            for (var n = 1; n <= 5; n++) {
                (function(star) {
                    var s = document.createElement('span');
                    s.setAttribute('data-star', star);
                    s.style.cssText = 'display:inline-block;position:relative;width:1.1em;cursor:pointer;';
                    setStarDisp(s, sel, star);
                    s.addEventListener('mousemove', function(e) {
                        var isHalf = e.offsetX < this.offsetWidth / 2;
                        var hover = isHalf ? star - 0.5 : star;
                        starsEl.querySelectorAll('[data-star]').forEach(function(sp) {
                            setStarDisp(sp, hover, parseFloat(sp.getAttribute('data-star')));
                        });
                        numEl.textContent = hover;
                    });
                    s.addEventListener('mouseout', function() {
                        starsEl.querySelectorAll('[data-star]').forEach(function(sp) {
                            setStarDisp(sp, sel, parseFloat(sp.getAttribute('data-star')));
                        });
                        numEl.textContent = sel > 0 ? sel : '';
                    });
                    s.addEventListener('click', function(e) {
                        var isHalf = e.offsetX < this.offsetWidth / 2;
                        sel = isHalf ? star - 0.5 : star;
                        drawStars();
                    });
                    starsEl.appendChild(s);
                })(n);
            }
        }
        drawStars();
        win.style.display = 'block';
        win.style.left = Math.round((window.innerWidth  - win.offsetWidth)  / 2) + 'px';
        win.style.top  = Math.round((window.innerHeight - win.offsetHeight) / 2) + 'px';

        var closeBtn  = document.getElementById('profile-review-window-close');
        var cancelBtn = document.getElementById('profile-review-window-cancel');
        var submitBtn = document.getElementById('profile-review-window-submit');
        var newClose  = closeBtn.cloneNode(true);  closeBtn.parentNode.replaceChild(newClose,  closeBtn);
        var newCancel = cancelBtn.cloneNode(true); cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);
        var newSubmit = submitBtn.cloneNode(true); submitBtn.parentNode.replaceChild(newSubmit, submitBtn);

        function closeWin() { win.style.display = 'none'; }
        newClose.addEventListener('click', closeWin);
        newCancel.addEventListener('click', closeWin);
        newSubmit.addEventListener('click', function() {
            if (!sel) return;
            lists[cat][itemIdx].review = { stars: sel, comment: commentEl.value.trim() };
            saveCategory(cat);
            renderCatView(cat);
            closeWin();
        });
    }

    /* ──── Category view ──── */
    function showCatView(cat) {
        currentCat = cat;
        document.getElementById('profile-view-default').style.display = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'none';
        document.getElementById('profile-sidebar-back').style.display = 'block';
        var catView = document.getElementById('profile-view-cat');
        catView.style.display = 'flex';
        var titleEl  = document.getElementById('profile-catview-title');
        if (titleEl) titleEl.textContent = CATS[cat].icon + ' ' + CATS[cat].label;
        var doneHead = document.getElementById('profile-catview-done-head');
        if (doneHead) doneHead.textContent = DONE_LABELS[cat];
        renderCatView(cat);
        renderCatEncurso(cat);
    }

    function showDefaultView() {
        currentCat = null;
        document.getElementById('profile-view-cat').style.display = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'none';
        document.getElementById('profile-sidebar-back').style.display = 'none';
        document.getElementById('profile-view-default').style.display = 'flex';
    }

    function renderCatView(cat) {
        var pendingGallery = document.getElementById('profile-catview-pending');
        var doneGallery    = document.getElementById('profile-catview-done');
        if (!pendingGallery || !doneGallery) return;
        var items   = lists[cat] || [];
        var pending = items.filter(function(i) { return i.status === 'pending'; });
        var done    = items.filter(function(i) { return i.status === 'completed'; });
        renderGallery(cat, pending, pendingGallery, true, false);
        renderGallery(cat, done, doneGallery, true, false);
    }

    function renderGallery(cat, items, gallery, withCtx, showFooter) {
        gallery.innerHTML = '';
        if (!items.length) {
            var empty = document.createElement('div');
            empty.className = 'profile-gallery-empty';
            empty.textContent = 'Sin elementos';
            gallery.appendChild(empty);
            return;
        }
        items.forEach(function(item) {
            var idx  = lists[cat].indexOf(item);
            var card = document.createElement('div');
            card.className = 'profile-gallery-card';

            var tb = document.createElement('div');
            tb.className = 'profile-gallery-tb';
            var tbTitle = document.createElement('span');
            tbTitle.className = 'profile-gallery-tb-title';
            tbTitle.textContent = item.title;
            tbTitle.title = item.title;
            tb.appendChild(tbTitle);
            if (item.review && item.review.stars) {
                var starsSpan = document.createElement('span');
                starsSpan.className = 'profile-gallery-tb-stars';
                starsSpan.innerHTML = makeStarsHtml(item.review.stars, 5) + '<span class="profile-star-num" style="font-size:9px;margin-left:2px;vertical-align:middle;">' + item.review.stars + '</span>';
                tb.appendChild(starsSpan);
            }
            if (item.review && item.review.comment) {
                var bubbleBtn = document.createElement('span');
                bubbleBtn.className = 'profile-gallery-tb-bubble';
                bubbleBtn.textContent = '💬';
                (function(r) {
                    bubbleBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        showReviewView(r);
                    });
                })(item.review);
                tb.appendChild(bubbleBtn);
            }
            var imgWrap = document.createElement('div');
            imgWrap.className = showFooter ? 'profile-gallery-img-wrap' : 'profile-gallery-img-wrap profile-gallery-img-slot';
            imgWrap.style.position = 'relative';
            if (item.image) {
                var img = document.createElement('img');
                img.className = 'profile-gallery-img';
                img.src = item.image;
                img.alt = item.title;
                img.onerror = function() {
                    imgWrap.innerHTML = '<div class="profile-gallery-placeholder">🖼</div>';
                };
                imgWrap.appendChild(img);
            } else {
                imgWrap.innerHTML = '<div class="profile-gallery-placeholder">🖼</div>';
            }
            if (item.collaborators && item.collaborators.length) {
                var collabStrip = document.createElement('div');
                collabStrip.className = 'profile-gallery-collabs';
                item.collaborators.forEach(function(uKey) {
                    var uInfo = PROFILE_USERS[uKey];
                    if (!uInfo) return;
                    var avFrame = document.createElement('div');
                    avFrame.className = 'profile-avatar-frame';
                    avFrame.title = uInfo.label;
                    if (uInfo.image) {
                        var avImg = document.createElement('img');
                        avImg.src = uInfo.image;
                        avImg.alt = uInfo.label;
                        avFrame.appendChild(avImg);
                    }
                    collabStrip.appendChild(avFrame);
                });
                imgWrap.appendChild(collabStrip);
            } else if (item.sharedFrom && PROFILE_USERS[item.sharedFrom]) {
                var hostStrip = document.createElement('div');
                hostStrip.className = 'profile-gallery-collabs';
                var hostInfo = PROFILE_USERS[item.sharedFrom];
                var avFrame = document.createElement('div');
                avFrame.className = 'profile-avatar-frame';
                avFrame.title = hostInfo.label;
                if (hostInfo.image) {
                    var avImg = document.createElement('img');
                    avImg.src = hostInfo.image;
                    avImg.alt = hostInfo.label;
                    avFrame.appendChild(avImg);
                }
                hostStrip.appendChild(avFrame);
                imgWrap.appendChild(hostStrip);
            }

            var footer = document.createElement('div');
            footer.className = 'profile-gallery-footer';
            footer.dataset.status = item.status || 'pending';
            footer.textContent = STATUS_LABELS[item.status || 'pending'];
            (function(i, el) {
                el.addEventListener('click', function() {
                    var cur  = el.dataset.status;
                    var next = STATUS_CYCLE[(STATUS_CYCLE.indexOf(cur) + 1) % STATUS_CYCLE.length];
                    el.dataset.status    = next;
                    el.textContent       = STATUS_LABELS[next];
                    lists[cat][i].status = next;
                    saveCategory(cat);
                    renderCatView(cat);
                    renderCatEncurso(cat);
                });
            })(idx, footer);

            card.appendChild(tb);
            card.appendChild(imgWrap);
            if (showFooter) card.appendChild(footer);

            if (withCtx) {
                (function(it) {
                    card.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                        var menuItems = [];
                        var isCollab = !!it.sharedFrom;
                        if (it.status === 'completed') {
                            var reviewLabel = (it.review && it.review.stars) ? '✏ Editar reseña' : '✏ Añadir reseña';
                            menuItems.push({ label: reviewLabel, action: function() {
                                var i = lists[cat].indexOf(it);
                                if (i !== -1) showReviewWindow(cat, i);
                            }});
                        } else {
                            var slots = (lists[cat] || []).filter(function(i) { return i.status === 'in-progress'; }).length;
                            menuItems.push({ label: '▶ Poner en curso', disabled: slots >= 3, action: function() {
                                var i = lists[cat].indexOf(it);
                                if (i !== -1) {
                                    lists[cat][i].status = 'in-progress';
                                    saveCategory(cat);
                                    renderCatView(cat);
                                    renderCatEncurso(cat);
                                }
                            }});
                            menuItems.push({ label: '👥 Colaboradores', action: function() {
                                showCollabDialog(cat, it);
                            }});
                        }
                        if (isCollab) {
                            menuItems.push({ label: '🚪 Abandonar actividad', action: function() {
                                confirmFn('¿Abandonar "' + it.title + '"?', 'Abandonar', function() {
                                    fetch('assets/profile/leave-collab.php', {
                                        method: 'POST', headers: {'Content-Type':'application/json'},
                                        body: JSON.stringify({ action: 'leave', category: cat, itemId: it.id })
                                    }).then(function(r) { return r.json(); }).then(function(d) {
                                        if (d.ok) { loadLists(function() { updateCounts(); renderCatView(cat); renderCatEncurso(cat); }); }
                                    }).catch(function(){});
                                });
                            }});
                        } else {
                            menuItems.push({ label: '✕ Eliminar', action: function() {
                                confirmFn('¿Eliminar "' + it.title + '"?', 'Eliminar', function() {
                                    var i = lists[cat].indexOf(it);
                                    if (i !== -1) {
                                        lists[cat].splice(i, 1);
                                        saveCategory(cat);
                                        updateCounts();
                                        renderCatView(cat);
                                        renderCatEncurso(cat);
                                    }
                                });
                            }});
                        }
                        showCtxMenu(e.clientX, e.clientY, menuItems);
                    });
                })(item);
            }

            gallery.appendChild(card);
        });
    }

    /* ──── Add dialog ──── */
    var addDlg       = document.getElementById('profile-add-dialog');
    var addDlgTitle  = document.getElementById('profile-add-dialog-title');
    var addNameInput = document.getElementById('profile-add-name');
    var addImgInput  = document.getElementById('profile-add-image');

    function openAddDialog(cat) {
        addDialogCat = cat;
        addDlgTitle.textContent = '+ Añadir · ' + CATS[cat].icon;
        addNameInput.value = '';
        addImgInput.value  = '';
        document.getElementById('profile-add-error').textContent = '';
        addDlg.style.display = 'block';
        setTimeout(function() { addNameInput.focus(); }, 0);
    }

    function closeAddDialog() {
        addDlg.style.display = 'none';
        addDialogCat = null;
    }

    function submitAdd() {
        var title = addNameInput.value.trim();
        if (!title || !addDialogCat) return;
        var lower = title.toLowerCase();
        var errEl = document.getElementById('profile-add-error');
        if (lists[addDialogCat].some(function(it) { return it.title.toLowerCase() === lower; })) {
            errEl.textContent = '⚠ Ya tienes "' + title + '" en tu lista.';
            return;
        }
        errEl.textContent = '';
        lists[addDialogCat].push({
            id:     'item_' + Date.now(),
            title:  title,
            image:  addImgInput.value.trim(),
            status: 'pending'
        });
        saveCategory(addDialogCat);
        updateCounts();
        if (currentCat) { renderCatView(currentCat); renderCatEncurso(currentCat); }
        closeAddDialog();
    }

    document.getElementById('profile-add-dialog-submit').addEventListener('click', submitAdd);
    document.getElementById('profile-add-dialog-cancel').addEventListener('click', closeAddDialog);
    document.getElementById('profile-add-dialog-close').addEventListener('click', closeAddDialog);
    [addNameInput, addImgInput].forEach(function(inp) {
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter')  submitAdd();
            if (e.key === 'Escape') closeAddDialog();
        });
    });

    /* ──── Category view buttons ──── */
    var catviewAddBtn  = document.getElementById('profile-catview-add-btn');
    var catviewBackBtn = document.getElementById('profile-catview-back');
    if (catviewAddBtn) catviewAddBtn.addEventListener('click', function() {
        if (currentCat) openAddDialog(currentCat);
    });
    if (catviewBackBtn) catviewBackBtn.addEventListener('click', showDefaultView);

    /* ──── Posts ──── */
    function relTime(ts) {
        var diff = Math.floor(Date.now() / 1000) - ts;
        if (diff < 60)   return 'ahora';
        if (diff < 3600) return 'hace ' + Math.floor(diff / 60) + 'm';
        if (diff < 86400) return 'hace ' + Math.floor(diff / 3600) + 'h';
        return 'hace ' + Math.floor(diff / 86400) + 'd';
    }

    function loadProfile(cb) {
        fetch('assets/profile/get-profile.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && !data.error) {
                    renderPosts(data.posts || []);
                    renderProfileInfo(data);
                }
                if (cb) cb();
            })
            .catch(function() { if (cb) cb(); });
    }

    function renderProfileInfo(data) {
        var bioEl   = document.getElementById('profile-info-bio');
        var metaEl  = document.getElementById('profile-info-meta');
        var linksEl = document.getElementById('profile-info-links');
        if (!bioEl) return;

        bioEl.textContent    = data.bio || '';
        bioEl.style.display  = data.bio ? '' : 'none';

        metaEl.innerHTML = '';
        function chip(txt) { var s = document.createElement('span'); s.className = 'pinfo-chip'; s.textContent = txt; return s; }
        if (data.pronouns) metaEl.appendChild(chip(data.pronouns));
        if (data.age)      metaEl.appendChild(chip(data.age + ' años'));
        if (data.country)  metaEl.appendChild(chip('📍 ' + data.country));

        linksEl.innerHTML = '';
        var socials = [
            { key: 'steam',     icon: '🎮', label: 'Steam',
              url: function(v) { return /^https?:\/\//.test(v) ? v : 'https://steamcommunity.com/id/' + encodeURIComponent(v); } },
            { key: 'discord',   icon: '💬', label: 'Discord',   url: null },
            { key: 'twitter',   icon: '🐦', label: 'Twitter',
              url: function(v) { return /^https?:\/\//.test(v) ? v : 'https://x.com/' + encodeURIComponent(v.replace(/^@/, '')); } },
            { key: 'instagram', icon: '📷', label: 'Instagram',
              url: function(v) { return /^https?:\/\//.test(v) ? v : 'https://instagram.com/' + encodeURIComponent(v.replace(/^@/, '')); } },
        ];
        socials.forEach(function(s) {
            if (!data[s.key]) return;
            var a = document.createElement('a');
            a.className = 'pinfo-social';
            a.textContent = s.icon + ' ' + s.label;
            if (s.url) {
                a.href = s.url(data[s.key]);
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
            } else {
                a.href = '#';
                a.title = 'Copiar: ' + data[s.key];
                (function(val) {
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (navigator.clipboard) navigator.clipboard.writeText(val).catch(function(){});
                    });
                })(data[s.key]);
            }
            linksEl.appendChild(a);
        });
    }

    /* Exposed globally so external code can open the profile on the music tab */
    window.profileOpenMusic = function() {
        if (taskbarManager.isRegistered('profile-window')) {
            taskbarManager.restore('profile-window');
        } else {
            profileWin.style.height = Math.max(380, window.innerHeight - 80) + 'px';
            taskbarManager.register('profile-window', 'Perfil', '👤', 'flex');
            startItemNotifStream();
        }
        if (!loaded) {
            loaded = true;
            loadLists(function() { updateCounts(); showMusicView(); });
            loadProfile();
        } else {
            showMusicView();
        }
    };

    /* Exposed globally so the player right-click menu can add the current track */
    window.profileAddTrackAndReview = function(track) {
        if (!track || !track.videoId) return;
        if (taskbarManager.isRegistered('profile-window')) {
            taskbarManager.restore('profile-window');
        } else {
            profileWin.style.height = Math.max(380, window.innerHeight - 80) + 'px';
            taskbarManager.register('profile-window', 'Perfil', '👤', 'flex');
            startItemNotifStream();
        }
        function doAdd() {
            var existIdx = -1;
            var trackTitle = (track.title || '').toLowerCase().trim();
            for (var i = 0; i < lists.music.length; i++) {
                if (lists.music[i].ytId === track.videoId || (trackTitle && lists.music[i].title.toLowerCase().trim() === trackTitle)) {
                    existIdx = i; break;
                }
            }
            var targetIdx;
            if (existIdx !== -1) {
                targetIdx = existIdx;
            } else {
                var entry = {
                    id:       'music_' + Date.now(),
                    type:     'song',
                    title:    track.title  || 'Sin título',
                    artist:   track.artist || '',
                    image:    'https://img.youtube.com/vi/' + track.videoId + '/mqdefault.jpg',
                    featured: false,
                    ytId:     track.videoId
                };
                lists.music.push(entry);
                saveCategory('music');
                updateCounts();
                renderMusicView(currentMusicTab);
                renderMusicDestacados();
                targetIdx = lists.music.length - 1;
            }
            var prompt = document.getElementById('profile-review-prompt');
            document.getElementById('profile-review-prompt-msg').textContent = existIdx !== -1
                ? '"' + (track.title || 'Sin título') + '" ya está en tu lista. ¿Editar la reseña?'
                : '¿Añadir una reseña para "' + (track.title || 'Sin título') + '"?';
            prompt.style.display = 'block';
            prompt.style.left = Math.round((window.innerWidth  - prompt.offsetWidth)  / 2) + 'px';
            prompt.style.top  = Math.round((window.innerHeight - prompt.offsetHeight) / 2) + 'px';
            var yesBtn = document.getElementById('profile-review-prompt-yes');
            var noBtn  = document.getElementById('profile-review-prompt-no');
            var newYes = yesBtn.cloneNode(true); yesBtn.parentNode.replaceChild(newYes, yesBtn);
            var newNo  = noBtn.cloneNode(true);  noBtn.parentNode.replaceChild(newNo,  noBtn);
            (function(idx) {
                newYes.addEventListener('click', function() { prompt.style.display = 'none'; showMusicReviewWindow(idx); });
                newNo.addEventListener('click',  function() { prompt.style.display = 'none'; });
            })(targetIdx);
        }
        if (!loaded) { loaded = true; loadLists(function() { updateCounts(); showMusicView(); doAdd(); }); loadProfile(); }
        else doAdd();
    };

    function renderPosts(posts) {
        var list = document.getElementById('profile-posts-list');
        if (!list) return;
        list.innerHTML = '';
        if (!posts.length) {
            var empty = document.createElement('div');
            empty.className = 'profile-post-empty';
            empty.textContent = 'Sin posts aún';
            list.appendChild(empty);
            return;
        }
        posts.forEach(function(post) {
            var box = document.createElement('div');
            box.className = 'profile-post-box';
            var hdr = document.createElement('div');
            hdr.className = 'profile-post-hdr';
            var timeEl = document.createElement('span');
            timeEl.className = 'profile-post-time';
            timeEl.textContent = relTime(post.createdAt || 0);
            var delBtn = document.createElement('button');
            delBtn.className = 'button profile-post-del';
            delBtn.textContent = '×';
            delBtn.addEventListener('click', function() {
                confirmFn('¿Eliminar este post?', 'Eliminar', function() {
                    fetch('assets/profile/delete-post.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: post.id })
                    }).then(function() { loadProfile(); }).catch(function() {});
                });
            });
            hdr.appendChild(timeEl);
            hdr.appendChild(delBtn);
            var txt = document.createElement('div');
            txt.className = 'profile-post-text';
            txt.textContent = post.text;
            box.appendChild(hdr);
            box.appendChild(txt);
            list.appendChild(box);
        });
    }

    var postInput = document.getElementById('profile-post-input');
    var postBtn   = document.getElementById('profile-post-btn');
    if (postBtn) {
        postBtn.addEventListener('click', function() {
            var text = postInput ? postInput.value.trim() : '';
            if (!text) return;
            fetch('assets/profile/add-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) { if (postInput) postInput.value = ''; loadProfile(); }
            })
            .catch(function() {});
        });
    }
    if (postInput) {
        postInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (postBtn) postBtn.click(); }
        });
    }

    /* ──── reloadCurrentView helper ──── */
    function reloadCurrentView() {
        if (currentCat === 'music') {
            renderMusicView(currentMusicTab);
            renderMusicDestacados();
        } else if (currentCat) {
            renderCatView(currentCat);
            renderCatEncurso(currentCat);
        }
    }

    /* ──── Music view ──── */
    function showMusicView() {
        currentCat = 'music';
        document.getElementById('profile-view-default').style.display = 'none';
        document.getElementById('profile-view-cat').style.display = 'none';
        document.getElementById('profile-sidebar-back').style.display = 'block';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'flex';
        renderMusicDestacados();
        renderMusicView(currentMusicTab);
    }

    function renderMusicDestacados() {
        var slotsEl = document.getElementById('music-catview-destacados-slots');
        if (!slotsEl) return;
        slotsEl.innerHTML = '';
        var featured = lists.music.filter(function(i) { return i.featured; });
        for (var s = 0; s < 3; s++) {
            var item = featured[s] || null;
            var slot = document.createElement('div');
            slot.className = 'profile-encurso-slot' + (item ? ' filled' : '');

            var tb = document.createElement('div');
            tb.className = 'profile-encurso-slot-tb music-slot-tb';
            if (item) {
                var tbTitle = document.createElement('div');
                tbTitle.className = 'music-slot-tb-title';
                tbTitle.textContent = item.title;
                tb.appendChild(tbTitle);
                if (item.artist) {
                    var tbArtist = document.createElement('div');
                    tbArtist.className = 'music-slot-tb-artist';
                    tbArtist.textContent = item.artist;
                    tb.appendChild(tbArtist);
                }
                if (item.review && item.review.stars) {
                    var tbStars = document.createElement('div');
                    tbStars.className = 'music-slot-tb-stars';
                    tbStars.innerHTML = makeStarsHtml(item.review.stars, 5) +
                        '<span class="profile-star-num" style="font-size:8px;margin-left:2px;vertical-align:middle;">' + item.review.stars + '</span>';
                    if (item.review.comment) {
                        var tbBubble = document.createElement('span');
                        tbBubble.textContent = '💬';
                        tbBubble.style.cssText = 'font-size:9px;margin-left:3px;cursor:pointer;';
                        (function(r) { tbBubble.addEventListener('click', function(e) { e.stopPropagation(); showReviewView(r); }); })(item.review);
                        tbStars.appendChild(tbBubble);
                    }
                    tb.appendChild(tbStars);
                }
            } else {
                tb.textContent = '—';
            }
            slot.appendChild(tb);

            var body = document.createElement('div');
            body.className = 'profile-encurso-slot-body';
            body.style.position = 'relative';
            if (item && item.image) {
                var img = document.createElement('img');
                img.src = item.image; img.alt = item.title;
                (function(b, it) { img.onerror = function() { b.textContent = it.type === 'album' ? '💿' : '🎵'; }; })(body, item);
                body.appendChild(img);
            } else if (item) {
                body.textContent = item.type === 'album' ? '💿' : '🎵';
            }
            if (item && item.collaborators && item.collaborators.length) {
                var strip = document.createElement('div');
                strip.className = 'profile-gallery-collabs';
                item.collaborators.forEach(function(uKey) {
                    var uInfo = PROFILE_USERS[uKey]; if (!uInfo) return;
                    var avFrame = document.createElement('div'); avFrame.className = 'profile-avatar-frame'; avFrame.title = uInfo.label;
                    if (uInfo.image) { var avImg = document.createElement('img'); avImg.src = uInfo.image; avImg.alt = uInfo.label; avFrame.appendChild(avImg); }
                    strip.appendChild(avFrame);
                });
                body.appendChild(strip);
            } else if (item && item.sharedFrom && PROFILE_USERS[item.sharedFrom]) {
                var strip = document.createElement('div');
                strip.className = 'profile-gallery-collabs';
                var hostInfo = PROFILE_USERS[item.sharedFrom];
                var avFrame = document.createElement('div'); avFrame.className = 'profile-avatar-frame'; avFrame.title = hostInfo.label;
                if (hostInfo.image) { var avImg = document.createElement('img'); avImg.src = hostInfo.image; avImg.alt = hostInfo.label; avFrame.appendChild(avImg); }
                strip.appendChild(avFrame);
                body.appendChild(strip);
            }
            slot.appendChild(body);
            if (item) {
                (function(it) {
                    slot.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                        showCtxMenu(e.clientX, e.clientY, [
                            { label: '▶ Reproducir', action: function() { playMusicItem(it); } },
                            { label: '★ Quitar de destacados', action: function() {
                                var idx = lists.music.indexOf(it);
                                if (idx !== -1) { lists.music[idx].featured = false; saveCategory('music'); renderMusicDestacados(); renderMusicView(currentMusicTab); }
                            }},
                            { label: '👥 Colaboradores', action: function() { showCollabDialog('music', it); }}
                        ]);
                    });
                })(item);
            }
            slotsEl.appendChild(slot);
        }
    }

    function playMusicItem(item) {
        updatePlayerTitle('⏳ Cargando…');
        fetch('assets/profile/play-music-item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                itemType:       item.type,
                title:          item.title          || '',
                artist:         item.artist         || '',
                ytId:           item.ytId           || '',
                spotifyId:      item.spotifyId      || '',
                ytPlaylistId:   item.ytPlaylistId   || '',
                spotifyAlbumId: item.spotifyAlbumId || '',
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error || !data.tracks || !data.tracks.length) {
                updatePlayerTitle(item.title || '—');
                return;
            }
            currentPlaylistId = null;
            currentPlaylistHasCollabs = false;
            playlist.length = 0;
            data.tracks.forEach(function(t) { playlist.push(t); });
            currentTrack = 0;
            updateTrackUI(0);
            updatePlayerTitle(item.title + (item.artist ? ' – ' + item.artist : ''));
            if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                ytPlayer.loadVideoById(playlist[0].videoId);
            }
        })
        .catch(function() { updatePlayerTitle(item.title || '—'); });
    }

    function renderMusicView(tab) {
        currentMusicTab = tab;
        document.querySelectorAll('.music-tab').forEach(function(t) {
            t.classList.toggle('active', t.dataset.tab === tab);
        });
        var listEl = document.getElementById('music-list');
        if (!listEl) return;
        listEl.innerHTML = '';
        var items = lists.music.filter(function(i) { return i.type === (tab === 'songs' ? 'song' : 'album'); });
        if (!items.length) {
            var empty = document.createElement('div');
            empty.className = 'music-list-empty';
            empty.textContent = 'Sin elementos';
            listEl.appendChild(empty);
            return;
        }
        items.forEach(function(item) {
            var idx = lists.music.indexOf(item);
            var row = document.createElement('div');
            row.className = 'music-list-row' + (item.featured ? ' music-list-featured' : '');

            var cover = document.createElement('div');
            cover.className = 'music-list-cover';
            if (item.image) {
                var img = document.createElement('img');
                img.src = item.image; img.alt = item.title;
                (function(c, it) { img.onerror = function() { c.innerHTML = '<div class="music-list-cover-ph">' + (it.type === 'album' ? '💿' : '🎵') + '</div>'; }; })(cover, item);
                cover.appendChild(img);
            } else {
                cover.innerHTML = '<div class="music-list-cover-ph">' + (item.type === 'album' ? '💿' : '🎵') + '</div>';
            }
            row.appendChild(cover);

            var info = document.createElement('div');
            info.className = 'music-list-info';
            var titleEl = document.createElement('div');
            titleEl.className = 'music-list-title';
            titleEl.textContent = item.title; titleEl.title = item.title;
            info.appendChild(titleEl);
            var artistEl = document.createElement('div');
            artistEl.className = 'music-list-artist';
            artistEl.textContent = item.artist || '—';
            info.appendChild(artistEl);
            if (item.review && item.review.stars) {
                var starsEl = document.createElement('div');
                starsEl.className = 'music-list-stars profile-gallery-tb-stars';
                starsEl.innerHTML = makeStarsHtml(item.review.stars, 5) + '<span class="profile-star-num" style="font-size:9px;margin-left:2px;vertical-align:middle;">' + item.review.stars + '</span>';
                info.appendChild(starsEl);
            }
            row.appendChild(info);

            var right = document.createElement('div');
            right.className = 'music-list-right';
            if (item.review && item.review.comment) {
                var bubble = document.createElement('span');
                bubble.className = 'profile-gallery-tb-bubble';
                bubble.textContent = '💬'; bubble.style.fontSize = '13px';
                (function(r) { bubble.addEventListener('click', function(e) { e.stopPropagation(); showReviewView(r); }); })(item.review);
                right.appendChild(bubble);
            }
            if (item.featured) {
                var featBadge = document.createElement('span');
                featBadge.className = 'music-featured-badge'; featBadge.textContent = '★';
                right.appendChild(featBadge);
            }
            if (item.collaborators && item.collaborators.length) {
                var strip = document.createElement('div'); strip.className = 'music-list-collabs';
                item.collaborators.forEach(function(uKey) {
                    var uInfo = PROFILE_USERS[uKey]; if (!uInfo) return;
                    var av = document.createElement('div'); av.className = 'profile-avatar-frame music-list-av'; av.title = uInfo.label;
                    if (uInfo.image) { var avImg = document.createElement('img'); avImg.src = uInfo.image; avImg.alt = uInfo.label; av.appendChild(avImg); }
                    strip.appendChild(av);
                });
                right.appendChild(strip);
            } else if (item.sharedFrom && PROFILE_USERS[item.sharedFrom]) {
                var strip = document.createElement('div'); strip.className = 'music-list-collabs';
                var hostInfo = PROFILE_USERS[item.sharedFrom];
                var av = document.createElement('div'); av.className = 'profile-avatar-frame music-list-av'; av.title = hostInfo.label;
                if (hostInfo.image) { var avImg = document.createElement('img'); avImg.src = hostInfo.image; avImg.alt = hostInfo.label; av.appendChild(avImg); }
                strip.appendChild(av);
                right.appendChild(strip);
            }
            row.appendChild(right);

            (function(it, i) {
                row.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    var isCollab = !!it.sharedFrom;
                    var menuItems = [];
                    menuItems.push({ label: '▶ Reproducir', action: function() { playMusicItem(it); } });
                    var featuredCount = lists.music.filter(function(x) { return x.featured; }).length;
                    if (it.featured) {
                        menuItems.push({ label: '★ Quitar de destacados', action: function() {
                            lists.music[i].featured = false; saveCategory('music'); renderMusicDestacados(); renderMusicView(currentMusicTab);
                        }});
                    } else {
                        menuItems.push({ label: '★ Destacar', disabled: featuredCount >= 3, action: function() {
                            lists.music[i].featured = true; saveCategory('music'); renderMusicDestacados(); renderMusicView(currentMusicTab);
                        }});
                    }
                    var reviewLabel = (it.review && it.review.stars) ? '✏ Editar reseña' : '✏ Añadir reseña';
                    menuItems.push({ label: reviewLabel, action: function() { showMusicReviewWindow(i); }});
                    menuItems.push({ label: '👥 Colaboradores', action: function() { showCollabDialog('music', it); }});
                    if (isCollab) {
                        menuItems.push({ label: '🚪 Abandonar actividad', action: function() {
                            confirmFn('¿Abandonar "' + it.title + '"?', 'Abandonar', function() {
                                fetch('assets/profile/leave-collab.php', {
                                    method: 'POST', headers: {'Content-Type':'application/json'},
                                    body: JSON.stringify({ action: 'leave', category: 'music', itemId: it.id })
                                }).then(function(r) { return r.json(); }).then(function(d) {
                                    if (d.ok) { loadLists(function() { updateCounts(); renderMusicView(currentMusicTab); renderMusicDestacados(); }); }
                                }).catch(function(){});
                            });
                        }});
                    } else {
                        menuItems.push({ label: '✕ Eliminar', action: function() {
                            confirmFn('¿Eliminar "' + it.title + '"?', 'Eliminar', function() {
                                lists.music.splice(i, 1); saveCategory('music'); updateCounts(); renderMusicView(currentMusicTab); renderMusicDestacados();
                            });
                        }});
                    }
                    showCtxMenu(e.clientX, e.clientY, menuItems);
                });
            })(item, idx);

            listEl.appendChild(row);
        });
    }

    function showMusicReviewWindow(itemIdx) {
        var win = document.getElementById('profile-review-window');
        var starsEl = document.getElementById('profile-review-stars');
        var commentEl = document.getElementById('profile-review-comment');
        var item = lists.music[itemIdx];
        document.getElementById('profile-review-window-title').textContent = '⭐ ' + item.title;
        commentEl.value = (item.review && item.review.comment) ? item.review.comment : '';
        var sel = (item.review && item.review.stars) ? item.review.stars : 0;
        var numEl = document.getElementById('profile-review-stars-num');
        numEl.textContent = sel > 0 ? sel : '';
        function setStarDisp(el, val, pos) {
            if (val >= pos) { el.innerHTML = '★'; el.style.clipPath = ''; el.style.opacity = ''; }
            else if (val >= pos - 0.5) { el.innerHTML = '★'; el.style.clipPath = 'inset(0 50% 0 0)'; el.style.opacity = ''; }
            else { el.innerHTML = '☆'; el.style.clipPath = ''; el.style.opacity = ''; }
        }
        starsEl.innerHTML = '';
        for (var i = 1; i <= 5; i++) {
            var s = document.createElement('span'); s.style.cssText = 'display:inline-block;position:relative;width:1.1em;cursor:pointer;';
            setStarDisp(s, sel, i);
            (function(pos) {
                s.addEventListener('mousemove', function(e) {
                    var half = e.offsetX < s.offsetWidth / 2, v = half ? pos - 0.5 : pos;
                    for (var j = 0; j < starsEl.children.length; j++) setStarDisp(starsEl.children[j], v, j + 1);
                    numEl.textContent = v;
                });
                s.addEventListener('click', function(e) {
                    sel = (e.offsetX < s.offsetWidth / 2) ? pos - 0.5 : pos;
                    for (var j = 0; j < starsEl.children.length; j++) setStarDisp(starsEl.children[j], sel, j + 1);
                    numEl.textContent = sel;
                });
                s.addEventListener('mouseleave', function() {
                    for (var j = 0; j < starsEl.children.length; j++) setStarDisp(starsEl.children[j], sel, j + 1);
                    numEl.textContent = sel > 0 ? sel : '';
                });
            })(i);
            starsEl.appendChild(s);
        }
        win.style.display = 'block';
        win.style.left = Math.round((window.innerWidth  - win.offsetWidth)  / 2) + 'px';
        win.style.top  = Math.round((window.innerHeight - win.offsetHeight) / 2) + 'px';
        function closeWin() { win.style.display = 'none'; }
        var closeBtn = document.getElementById('profile-review-window-close');
        var newClose = closeBtn.cloneNode(true); closeBtn.parentNode.replaceChild(newClose, closeBtn);
        newClose.addEventListener('click', closeWin);
        var cancelBtn = document.getElementById('profile-review-window-cancel');
        var newCancel = cancelBtn.cloneNode(true); cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);
        newCancel.addEventListener('click', closeWin);
        var submitBtn = document.getElementById('profile-review-window-submit');
        var newSubmit = submitBtn.cloneNode(true); submitBtn.parentNode.replaceChild(newSubmit, submitBtn);
        newSubmit.addEventListener('click', function() {
            if (!sel) return;
            lists.music[itemIdx].review = { stars: sel, comment: commentEl.value.trim() };
            saveCategory('music'); renderMusicView(currentMusicTab); renderMusicDestacados(); closeWin();
        });
    }

    /* ──── Music add dialog ──── */
    (function() {
        var dlg           = document.getElementById('music-add-dialog');
        var step1         = document.getElementById('music-add-step1');
        var step2         = document.getElementById('music-add-step2');
        var step3         = document.getElementById('music-add-step3');
        var reviewPromptEl= document.getElementById('music-add-review-prompt');
        var reviewFormEl  = document.getElementById('music-add-review-form');
        var reviewStarsEl = document.getElementById('music-add-review-stars');
        var reviewCommentEl = document.getElementById('music-add-review-comment');
        var urlInput      = document.getElementById('music-add-url');
        var artistInput   = document.getElementById('music-add-artist');
        var preview       = document.getElementById('music-add-preview');
        var errEl         = document.getElementById('music-add-error');
        var titleEl       = document.getElementById('music-add-dialog-title');
        var currentType   = null;
        var fetchedMeta   = null;
        var reviewRating  = 0;
        var fetchTimer  = null;

        function openDialog() {
            step1.style.display = 'block'; step2.style.display = 'none'; step3.style.display = 'none';
            currentType = null; fetchedMeta = null; reviewRating = 0;
            titleEl.textContent = '+ Añadir música';
            dlg.style.display = 'block';
            dlg.style.left = Math.round((window.innerWidth  - dlg.offsetWidth)  / 2) + 'px';
            dlg.style.top  = Math.round((window.innerHeight - dlg.offsetHeight) / 2) + 'px';
        }
        function closeDialog() {
            dlg.style.display = 'none'; currentType = null; fetchedMeta = null; reviewRating = 0; clearTimeout(fetchTimer);
        }
        function goToStep3() {
            step2.style.display = 'none'; step3.style.display = 'block';
            reviewPromptEl.style.display = 'block'; reviewFormEl.style.display = 'none';
            reviewRating = 0; reviewCommentEl.value = '';
            titleEl.textContent = '★ Reseña';
        }
        function showReviewFormInline() {
            reviewPromptEl.style.display = 'none'; reviewFormEl.style.display = 'block';
            reviewStarsEl.innerHTML = '';
            var reviewNumEl = document.getElementById('music-add-review-stars-num');
            function setStarDisp(el, val, pos) {
                if (val >= pos) { el.innerHTML = '★'; el.style.clipPath = ''; el.style.opacity = ''; }
                else if (val >= pos - 0.5) { el.innerHTML = '★'; el.style.clipPath = 'inset(0 50% 0 0)'; el.style.opacity = ''; }
                else { el.innerHTML = '☆'; el.style.clipPath = ''; el.style.opacity = ''; }
            }
            function drawReviewStars() {
                reviewStarsEl.innerHTML = '';
                reviewNumEl.textContent = reviewRating > 0 ? reviewRating : '';
                for (var n = 1; n <= 5; n++) {
                    (function(star) {
                        var s = document.createElement('span');
                        s.setAttribute('data-star', star);
                        s.style.cssText = 'display:inline-block;position:relative;width:1.1em;cursor:pointer;';
                        setStarDisp(s, reviewRating, star);
                        s.addEventListener('mousemove', function(e) {
                            var isHalf = e.offsetX < this.offsetWidth / 2;
                            var hover = isHalf ? star - 0.5 : star;
                            reviewStarsEl.querySelectorAll('[data-star]').forEach(function(sp) {
                                setStarDisp(sp, hover, parseFloat(sp.getAttribute('data-star')));
                            });
                            reviewNumEl.textContent = hover;
                        });
                        s.addEventListener('mouseout', function() {
                            reviewStarsEl.querySelectorAll('[data-star]').forEach(function(sp) {
                                setStarDisp(sp, reviewRating, parseFloat(sp.getAttribute('data-star')));
                            });
                            reviewNumEl.textContent = reviewRating > 0 ? reviewRating : '';
                        });
                        s.addEventListener('click', function(e) {
                            var isHalf = e.offsetX < this.offsetWidth / 2;
                            reviewRating = isHalf ? star - 0.5 : star;
                            drawReviewStars();
                        });
                        reviewStarsEl.appendChild(s);
                    })(n);
                }
            }
            drawReviewStars();
        }
        function buildAndSave(withReview) {
            var entry = {
                id:       'music_' + Date.now(),
                type:     currentType,
                title:    fetchedMeta.title,
                artist:   artistInput.value.trim(),
                image:    fetchedMeta.image || '',
                featured: false
            };
            if (fetchedMeta.ytId)           entry.ytId           = fetchedMeta.ytId;
            if (fetchedMeta.spotifyId)      entry.spotifyId      = fetchedMeta.spotifyId;
            if (fetchedMeta.ytPlaylistId)   entry.ytPlaylistId   = fetchedMeta.ytPlaylistId;
            if (fetchedMeta.spotifyAlbumId) entry.spotifyAlbumId = fetchedMeta.spotifyAlbumId;
            if (withReview && reviewRating > 0) {
                entry.review = { stars: reviewRating, comment: reviewCommentEl.value.trim() };
            }
            lists.music.push(entry);
            saveCategory('music');
            updateCounts();
            renderMusicView(currentMusicTab);
            renderMusicDestacados();
            closeDialog();
        }
        function goToStep2(type) {
            currentType = type; fetchedMeta = null;
            urlInput.value = ''; artistInput.value = '';
            preview.textContent = ''; preview.style.color = ''; errEl.textContent = '';
            titleEl.textContent = type === 'song' ? '🎵 Añadir canción' : '💿 Añadir álbum';
            step1.style.display = 'none'; step2.style.display = 'block';
            setTimeout(function() { urlInput.focus(); }, 0);
        }

        document.getElementById('music-add-type-song').addEventListener('click',  function() { goToStep2('song');  });
        document.getElementById('music-add-type-album').addEventListener('click', function() { goToStep2('album'); });
        document.getElementById('music-add-back').addEventListener('click', function() {
            step2.style.display = 'none'; step1.style.display = 'block';
            currentType = null; fetchedMeta = null; clearTimeout(fetchTimer);
            titleEl.textContent = '+ Añadir música';
        });
        document.getElementById('music-add-close').addEventListener('click',  closeDialog);
        document.getElementById('music-add-cancel').addEventListener('click', closeDialog);

        urlInput.addEventListener('input', function() {
            clearTimeout(fetchTimer); fetchedMeta = null;
            preview.style.color = '';
            var raw = urlInput.value.trim();
            if (!raw) { preview.textContent = ''; return; }
            preview.textContent = 'Buscando...';
            fetchTimer = setTimeout(function() {
                fetch('assets/profile/resolve-music-item.php?url=' + encodeURIComponent(raw) + '&itemType=' + currentType)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) { preview.textContent = data.error; preview.style.color = '#c00'; return; }
                    fetchedMeta = data;
                    preview.textContent = (currentType === 'album' ? '💿 ' : '♪ ') + data.title;
                    if (data.artist && !artistInput.value.trim()) artistInput.value = data.artist;
                })
                .catch(function() { preview.textContent = 'Error de conexión'; preview.style.color = '#c00'; });
            }, 500);
        });

        document.getElementById('music-add-submit').addEventListener('click', function() {
            if (!fetchedMeta) { errEl.textContent = 'Espera a que se cargue el enlace.'; return; }
            var lower = fetchedMeta.title.toLowerCase().trim();
            if (lists.music.some(function(it) { return it.title.toLowerCase().trim() === lower; })) {
                errEl.textContent = '⚠ Ya tienes "' + fetchedMeta.title + '" en tu lista.';
                return;
            }
            errEl.textContent = '';
            goToStep3();
        });
        document.getElementById('music-add-review-no').addEventListener('click', function() {
            buildAndSave(false);
        });
        document.getElementById('music-add-review-yes').addEventListener('click', function() {
            showReviewFormInline();
        });
        document.getElementById('music-add-review-back2').addEventListener('click', function() {
            reviewFormEl.style.display = 'none'; reviewPromptEl.style.display = 'block';
            reviewRating = 0;
        });
        document.getElementById('music-add-review-save').addEventListener('click', function() {
            buildAndSave(true);
        });
        urlInput.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDialog(); });

        var addBtn = document.getElementById('music-catview-add-btn');
        if (addBtn) addBtn.addEventListener('click', openDialog);

        document.querySelectorAll('.music-tab').forEach(function(btn) {
            btn.addEventListener('click', function() { renderMusicView(btn.dataset.tab); });
        });
    })();

    /* ──── Profile info edit dialog ──── */
    (function() {
        var dlg       = document.getElementById('profile-info-edit-dialog');
        var editBtn   = document.getElementById('profile-info-edit-btn');
        var closeBtn  = document.getElementById('profile-info-edit-close');
        var cancelBtn = document.getElementById('profile-info-edit-cancel');
        var saveBtn   = document.getElementById('profile-info-edit-save');
        var FIELDS    = ['bio', 'pronouns', 'age', 'country', 'steam', 'discord', 'twitter', 'instagram'];

        function openDialog() {
            fetch('assets/profile/get-profile.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    FIELDS.forEach(function(f) {
                        var el = document.getElementById('pinfo-' + f);
                        if (el) el.value = data[f] || '';
                    });
                }).catch(function() {});
            dlg.style.display = 'block';
            dlg.style.left = Math.round((window.innerWidth  - dlg.offsetWidth)  / 2) + 'px';
            dlg.style.top  = Math.round((window.innerHeight - dlg.offsetHeight) / 2) + 'px';
        }
        function closeDialog() { dlg.style.display = 'none'; }

        if (editBtn)   editBtn.addEventListener('click',   openDialog);
        if (closeBtn)  closeBtn.addEventListener('click',  closeDialog);
        if (cancelBtn) cancelBtn.addEventListener('click', closeDialog);
        if (saveBtn) saveBtn.addEventListener('click', function() {
            var payload = {};
            FIELDS.forEach(function(f) { var el = document.getElementById('pinfo-' + f); payload[f] = el ? el.value.trim() : ''; });
            fetch('assets/profile/save-info.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(d) { if (d.ok) { renderProfileInfo(payload); closeDialog(); } })
            .catch(function() {});
        });
    })();

    /* ──── Nav sidebar items ──── */
    profileWin.querySelectorAll('.profile-nav-item').forEach(function(navItem) {
        navItem.addEventListener('click', function() {
            var cat = navItem.dataset.cat;
            if (!loaded) {
                loaded = true;
                loadLists(function() {
                    updateCounts();
                    if (cat === 'music') showMusicView(); else showCatView(cat);
                });
            } else {
                if (cat === 'music') showMusicView(); else showCatView(cat);
            }
        });
    });

    /* ──── Profile icon ──── */
    document.getElementById('profile-icon').addEventListener('dblclick', function() {
        if (taskbarManager.isRegistered('profile-window')) {
            taskbarManager.restore('profile-window');
        } else {
            profileWin.style.height = Math.max(380, window.innerHeight - 80) + 'px';
            taskbarManager.register('profile-window', 'Perfil', '👤', 'flex');
            if (!loaded) {
                loaded = true;
                loadLists(updateCounts);
                loadProfile();
            }
            startItemNotifStream();
        }
    });

    document.getElementById('profile-close').addEventListener('click', function() {
        taskbarManager.unregister('profile-window');
    });

    // Arrancar stream aunque el perfil no se abra (para recibir notificaciones en segundo plano)
    startItemNotifStream();
})();
</script>
