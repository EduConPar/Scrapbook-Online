<?php
// perfil.php - Profile window + all profile-related dialogs
// Included from desktop-base.php; expects $desktopLabel already set.
require_once dirname(__DIR__) . '/assets/config.php';
?>
<!-- PROFILE WINDOW -->
<div class="window" id="profile-window">
    <div class="title-bar">
        <div class="title-bar-text"><?php echo appTitleIcon('profileIcon', '👤'); ?><?php echo htmlspecialchars($desktopLabel); ?></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="profile-close"></button>
        </div>
    </div>
    <div class="window-body" id="profile-body">
        <!-- SIDEBAR IZQUIERDO -->
        <div id="profile-sidebar">
            <div class="profile-sidebar-heading" id="profile-listas-heading"><span id="profile-listas-heading-text">Mis Listas</span></div>
            <div id="profile-listas-nav">
                <div class="profile-nav-item" data-cat="movies">
                    <span class="profile-nav-icon"><img src="assets/img/appIcons/pelisIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                    <span class="profile-nav-label">Películas</span>
                    <span class="profile-nav-count" id="profile-count-movies">—</span>
                </div>
                <div class="profile-nav-item" data-cat="series">
                    <span class="profile-nav-icon"><img src="assets/img/appIcons/melonArchiveIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                    <span class="profile-nav-label">Series</span>
                    <span class="profile-nav-count" id="profile-count-series">—</span>
                </div>
                <div class="profile-nav-item" data-cat="books">
                    <span class="profile-nav-icon"><img src="assets/img/appIcons/booksIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                    <span class="profile-nav-label">Libros</span>
                    <span class="profile-nav-count" id="profile-count-books">—</span>
                </div>
                <div class="profile-nav-item" data-cat="games">
                    <span class="profile-nav-icon"><img src="assets/img/appIcons/juegosIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                    <span class="profile-nav-label">Videojuegos</span>
                    <span class="profile-nav-count" id="profile-count-games">—</span>
                </div>
                <div class="profile-nav-item" data-cat="music">
                    <span class="profile-nav-icon"><img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                    <span class="profile-nav-label">Música</span>
                    <span class="profile-nav-count" id="profile-count-music">—</span>
                </div>
            </div>
            <div id="profile-melon-section">
                <div class="profile-sidebar-heading"><span>Melon reviews</span></div>
                <div id="profile-melon-nav">
                    <div class="profile-nav-item" data-melon="year">
                        <span class="profile-nav-icon">🏆</span>
                        <span class="profile-nav-label">Mejor del año</span>
                    </div>
                    <div class="profile-nav-item" data-melon="recent">
                        <span class="profile-nav-icon"><img src="assets/img/appIcons/newsIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                        <span class="profile-nav-label">Reciente</span>
                    </div>
                    <div class="profile-nav-item" data-melon="alltime">
                        <span class="profile-nav-icon"><img src="assets/img/appIcons/calendarioIcon.png" alt="" style="width:16px;height:16px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;"></span>
                        <span class="profile-nav-label">Todo el tiempo</span>
                    </div>
                </div>
            </div>
            <div id="profile-social-section">
                <div class="profile-sidebar-heading">Social</div>
                <div id="profile-followed-nav"></div>
                <div class="profile-nav-item" id="profile-nav-social" data-cat="social">
                    <span class="profile-nav-icon">+</span>
                    <span class="profile-nav-label">Explorar</span>
                </div>
            </div>
            <div id="profile-sidebar-footer">
                <div id="profile-sidebar-back">
                    <button class="button" id="profile-catview-back">← Volver</button>
                </div>
                <button class="button" id="profile-info-edit-btn">✏ Editar perfil</button>
                <button class="button" id="profile-info-edit-save-btn" style="display:none;">💾 Guardar</button>
                <button class="button" id="profile-info-edit-cancel-btn" style="display:none;">Cancelar</button>
                <input type="file" id="profile-photo-input" accept="image/*" style="display:none;">
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
                        <button class="button" id="profile-follow-btn" style="display:none;font-size:9px;margin-top:5px;">+ Seguir</button>
                    </div>
                    <div id="profile-info-col">
                        <div id="profile-info-row">
                            <div id="profile-info-meta"></div>
                            <div id="profile-info-bio" class="pinfo-bio"></div>
                            <div id="profile-info-links"></div>
                        </div>
                    </div>
                </div>
                <div id="profile-posts-area">
                    <div id="profile-posts-header">
                        <span>Posts</span>
                        <button type="button" id="profile-notif-btn" title="Notificaciones">
                            <span class="profile-notif-icon"><img src="assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;display:block;margin:auto;"></span>
                            <span class="profile-notif-badge" id="profile-notif-badge" style="display:none;">0</span>
                        </button>
                    </div>
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
                        <div class="profile-encurso-heading">
                            <span>▶ En curso</span>
                            <div class="profile-catview-pager" id="profile-encurso-pager"></div>
                        </div>
                        <div id="profile-catview-encurso-slots"></div>
                    </div>
                </div>
                <div class="profile-cat-toolbar">
                    <span id="profile-catview-title"><img src="assets/img/appIcons/pelisIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Películas</span>
                    <button class="button" id="profile-catview-add-btn">+ Añadir</button>
                </div>
                <div id="profile-catview-sections">
                    <div class="profile-catview-section">
                        <div class="profile-catview-section-head">
                            <span class="profile-catview-section-head-text">Pendientes</span>
                            <div class="profile-catview-pager" id="profile-catview-pending-pager"></div>
                        </div>
                        <div class="profile-gallery" id="profile-catview-pending"></div>
                    </div>
                    <div class="profile-catview-section">
                        <div class="profile-catview-section-head">
                            <span class="profile-catview-section-head-text" id="profile-catview-done-head">Vistas</span>
                            <div class="profile-catview-pager" id="profile-catview-done-pager"></div>
                        </div>
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
                    <span id="music-catview-title"><img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Música</span>
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
            <!-- SOCIAL VIEW -->
            <div id="profile-view-social" style="display:none;">
                <div class="profile-social-head">👥 Explorar</div>
                <div id="profile-social-body">
                    <div id="profile-social-explore" class="profile-social-list"></div>
                </div>
            </div>
            <!-- MELON REVIEWS VIEW -->
            <div id="profile-view-melon" style="display:none;">
                <div class="profile-social-head" id="profile-melon-title">⭐ Melon reviews</div>
                <div id="profile-melon-cats">
                    <button class="button melon-cat-btn" data-mcat="movies"><img src="assets/img/appIcons/pelisIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Películas</button>
                    <button class="button melon-cat-btn" data-mcat="series"><img src="assets/img/appIcons/melonArchiveIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Series</button>
                    <button class="button melon-cat-btn" data-mcat="books"><img src="assets/img/appIcons/booksIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Libros</button>
                    <button class="button melon-cat-btn" data-mcat="games"><img src="assets/img/appIcons/juegosIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Videojuegos</button>
                    <button class="button melon-cat-btn" data-mcat="music" data-mtype="album"><img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Álbumes</button>
                    <button class="button melon-cat-btn" data-mcat="music" data-mtype="song"><img src="assets/img/appIcons/songIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Canciones</button>
                </div>
                <div id="profile-melon-body">
                    <div id="profile-melon-status" style="padding:14px;text-align:center;font-size:11px;color:#808080;">Selecciona una categoría</div>
                    <div id="profile-melon-list"></div>
                    <div id="profile-melon-pager"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHAT WINDOW -->
<div class="window" id="profile-chat-window" style="display:none;position:fixed;z-index:10002;width:340px;height:420px;">
    <div class="title-bar">
        <div class="title-bar-text" id="profile-chat-title" style="display:flex;align-items:center;gap:6px;">
            <span id="profile-chat-title-av" style="width:18px;height:18px;display:inline-block;background:var(--inset-bg,#fff);overflow:hidden;box-shadow:-1px -1px 0 var(--bezel-dark-1,#0a0a0a), 1px 1px 0 var(--bezel-light-1,#fff);flex-shrink:0;"></span>
            <span id="profile-chat-title-text">Chat</span>
        </div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-chat-close"></button>
        </div>
    </div>
    <div class="window-body" id="profile-chat-body">
        <div id="profile-chat-messages"></div>
        <!-- Panel emoji (oculto por defecto). Se posiciona absoluto sobre
             el input-row al togglear. -->
        <div id="profile-chat-emoji-panel" style="display:none;position:absolute;bottom:48px;left:8px;right:8px;background:var(--win-bg,silver);padding:6px;z-index:5;box-shadow:inset -1px -1px var(--bezel-dark-1,#0a0a0a),inset 1px 1px var(--bezel-light-1,#fff),inset -2px -2px var(--bezel-dark-2,grey),inset 2px 2px var(--bezel-light-2,#dfdfdf);max-height:140px;overflow-y:auto;"></div>
        <div id="profile-chat-input-row">
            <input type="text" id="profile-chat-input" maxlength="2000" placeholder="Escribe un mensaje…">
            <button class="button" id="profile-chat-emoji-btn" type="button" title="Emotes" style="padding:3px 8px;">😀</button>
            <button class="button" id="profile-chat-send">Enviar</button>
        </div>
    </div>
</div>

<!-- MELON REVIEWS DETAILS WINDOW -->
<div class="window" id="profile-melon-details-window" style="display:none;position:fixed;z-index:10002;width:360px;max-height:65vh;">
    <div class="title-bar">
        <div class="title-bar-text" id="profile-melon-details-title">Reseñas</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-melon-details-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:6px;max-height:55vh;overflow-y:auto;">
        <div id="profile-melon-details-list"></div>
    </div>
</div>

<!-- PROFILE NOTIFICATIONS WINDOW -->
<div class="window" id="profile-notifs-window" style="display:none;position:fixed;z-index:10002;width:320px;max-height:60vh;">
    <div class="title-bar">
        <div class="title-bar-text" style="display:inline-flex;align-items:center;gap:4px;"><img src="assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;">Notificaciones</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-notifs-close"></button>
        </div>
    </div>
    <div class="window-body" id="profile-notifs-body" style="padding:6px;max-height:50vh;overflow-y:auto;">
        <div id="profile-notifs-list"></div>
        <div id="profile-notifs-empty" style="display:none;padding:14px;text-align:center;font-size:11px;color:#808080;">No tienes notificaciones</div>
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
            <button class="button" id="profile-review-window-delete" style="margin-right:auto;display:none;"><img src="assets/img/appIcons/trashIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">Eliminar</button>
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


<!-- PROFILE PHOTO CROP DIALOG -->
<div class="window" id="profile-photo-crop-dialog" style="display:none;position:fixed;z-index:10003;width:420px;">
    <div class="title-bar">
        <div class="title-bar-text">✂ Recortar foto (1:1)</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-photo-crop-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <p style="font-size:10px;margin:0 0 6px;color:var(--text-muted);">Arrastra el recuadro para mover, las esquinas para redimensionar.</p>
        <div id="profile-photo-crop-stage">
            <img id="profile-photo-crop-img" alt="">
            <div id="profile-photo-crop-region">
                <div class="photo-crop-handle" data-corner="nw"></div>
                <div class="photo-crop-handle" data-corner="ne"></div>
                <div class="photo-crop-handle" data-corner="sw"></div>
                <div class="photo-crop-handle" data-corner="se"></div>
            </div>
        </div>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:10px;">
            <button class="button" id="profile-photo-crop-cancel">Cancelar</button>
            <button class="button" id="profile-photo-crop-confirm">Recortar y aplicar</button>
        </div>
    </div>
</div>

<!-- PROFILE SOCIAL LINK ADD DIALOG (edit mode) -->
<div class="window" id="profile-social-add-dialog" style="display:none;position:fixed;z-index:10002;width:280px;">
    <div class="title-bar">
        <div class="title-bar-text">+ Añadir conexión</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="profile-social-add-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <div class="field-row-stacked">
            <label for="profile-social-add-platform" style="font-size:10px;">Plataforma</label>
            <select id="profile-social-add-platform" style="width:100%;box-sizing:border-box;">
                <option value="steam">🎮 Steam</option>
                <option value="discord">💬 Discord</option>
                <option value="twitter">🐦 Twitter / X</option>
                <option value="instagram">📷 Instagram</option>
            </select>
        </div>
        <div class="field-row-stacked" style="margin-top:6px;">
            <label for="profile-social-add-value" style="font-size:10px;">Usuario o URL</label>
            <input type="text" id="profile-social-add-value" maxlength="200" placeholder="@usuario o https://..." style="width:100%;box-sizing:border-box;">
        </div>
        <div class="field-row" style="justify-content:flex-end;gap:4px;margin-top:10px;">
            <button class="button" id="profile-social-add-cancel">Cancelar</button>
            <button class="button" id="profile-social-add-confirm">Añadir</button>
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
                <button class="button" id="music-add-type-song" style="flex:1;"><img src="assets/img/appIcons/songIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Canción</button>
                <button class="button" id="music-add-type-album" style="flex:1;"><img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Álbum / Playlist</button>
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

<!-- Lightbox de imágenes de posts (click en preview → pantalla completa) -->
<div id="profile-post-lightbox" role="dialog" aria-hidden="true">
    <button type="button" id="profile-post-lightbox-close" title="Cerrar (Esc)">×</button>
    <img id="profile-post-lightbox-img" src="" alt="" referrerpolicy="no-referrer">
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

    var lists        = { movies: [], series: [], books: [], games: [], music: [] };
    var loaded       = false;
    var addDialogCat = null;
    var currentCat   = null;
    var currentMusicTab = 'albums';

    var CATS = {
        movies: { label: 'Películas',   icon: '<img src="assets/img/appIcons/pelisIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">' },
        series: { label: 'Series',      icon: '📺' },
        books:  { label: 'Libros',      icon: '<img src="assets/img/appIcons/booksIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">' },
        games:  { label: 'Videojuegos', icon: '<img src="assets/img/appIcons/juegosIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">' },
        music:  { label: 'Música',      icon: '🎵' }
    };

    var DONE_LABELS = { movies: 'Vistas', series: 'Vistas', books: 'Leídas', games: 'Jugadas' };
    var CAT_VERBS   = { movies: 'ver', series: 'ver', books: 'leer', games: 'jugar', music: 'escuchar' };

    var STATUS_CYCLE  = ['pending', 'in-progress', 'completed'];
    var STATUS_LABELS = {
        'pending':     '○ Pendiente',
        'in-progress': '◑ En curso',
        'completed':   '● Completado'
    };

    var confirmFn = window.win98Confirm || function(msg, title, cb) { if (confirm(msg)) cb(); };

    /* ──── Data ──── */
    function loadLists(cb) {
        fetch('assets/profile/api.php?action=get-lists')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && !data.error) {
                    ['movies', 'series', 'books', 'games', 'music'].forEach(function(k) {
                        lists[k] = Array.isArray(data[k]) ? data[k] : [];
                    });
                }
                if (cb) cb();
            })
            .catch(function() { if (cb) cb(); });
    }

    function saveCategory(cat) {
        var prevIds = (lists[cat] || []).map(function(x){ return x.id; }).join(',');
        fetch('assets/profile/api.php?action=save-lists', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: lists[cat] })
        }).then(function(r) { return r.json(); })
          .then(function(d) {
              /* El backend asigna IDs definitivos a los items nuevos
                 (los que llevaban id "item_...") y devuelve el snapshot
                 canónico. Reemplazar la lista local para que el próximo
                 save no duplique. */
              if (d && Array.isArray(d.items)) {
                  lists[cat] = d.items;
                  /* Si los IDs cambiaron (item nuevo recibió su id real),
                     re-renderizar la vista activa para que los closures del
                     DOM apunten a los nuevos objetos. Sin esto, un ítem recién
                     creado no respondería a clicks hasta recargar. */
                  var newIds = lists[cat].map(function(x){ return x.id; }).join(',');
                  if (newIds !== prevIds) {
                      if (cat === 'music' && currentCat === 'music') {
                          renderMusicView(currentMusicTab); renderMusicDestacados();
                      } else if (currentCat === cat) {
                          renderCatView(cat); renderCatEncurso(cat);
                      }
                  }
              }
          }).catch(function() {});
    }

    function updateCounts() {
        ['movies', 'series', 'books', 'games'].forEach(function(cat) {
            var el = document.getElementById('profile-count-' + cat);
            if (el) el.textContent = lists[cat].length;
        });
        var musicEl = document.getElementById('profile-count-music');
        if (musicEl) musicEl.textContent = lists.music.length;
    }

    /* ──── Context menu ──── */
    var _ctxMenu = null;

    function showCtxMenu(x, y, options) {
        if (!options || !options.length) return;
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

    /* ──── En curso: 3 slots por página, paginación si hay más ──── */
    var ENCURSO_PER_PAGE = 3;
    var _encursoPage = {};   /* { cat: pageIndex } */
    function renderCatEncurso(cat) {
        var slotsEl = document.getElementById('profile-catview-encurso-slots');
        if (!slotsEl) return;
        slotsEl.innerHTML = '';
        var inProgress = (lists[cat] || []).filter(function(i) { return i.status === 'in-progress'; });
        var totalPages = Math.max(1, Math.ceil(inProgress.length / ENCURSO_PER_PAGE));
        if (_encursoPage[cat] === undefined) _encursoPage[cat] = 0;
        if (_encursoPage[cat] >= totalPages) _encursoPage[cat] = totalPages - 1;
        if (_encursoPage[cat] < 0) _encursoPage[cat] = 0;
        var pageStart = _encursoPage[cat] * ENCURSO_PER_PAGE;
        var pageItems = inProgress.slice(pageStart, pageStart + ENCURSO_PER_PAGE);
        for (var s = 0; s < ENCURSO_PER_PAGE; s++) {
            var item = pageItems[s] || null;
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
                        if (viewingUser) {
                            showCtxMenu(e.clientX, e.clientY, [
                                { label: '+ Añadir a mi perfil', action: function() { addItemToOwnProfile(cat, it); }}
                            ]);
                            return;
                        }
                        showCtxMenu(e.clientX, e.clientY, [
    { label: '✓ Completar', action: function() {
                                var idx = lists[cat].findIndex(function(x){ return x.id === it.id; });
                                if (idx !== -1) {
                                    lists[cat][idx].status = 'completed';
                                    saveCategory(cat);
                                    renderCatView(cat);
                                    renderCatEncurso(cat);
                                    showReviewPrompt(cat, idx);
                                    crearMomentoDesdeItem(cat, lists[cat][idx]);
                                }
                            }},
                            { label: '✕ Quitar de en curso', action: function() {
                                var idx = lists[cat].findIndex(function(x){ return x.id === it.id; });
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
        /* Paginación en el header (mismo patrón que Pendientes/Vistas):
           contenedor #profile-encurso-pager dentro de .profile-encurso-heading.
           No ocupa espacio del área de slots → no los encoge. */
        var pagerEl = document.getElementById('profile-encurso-pager');
        if (pagerEl) {
            pagerEl.innerHTML = '';
            if (inProgress.length > ENCURSO_PER_PAGE) {
                var prev = document.createElement('button');
                prev.className = 'button'; prev.textContent = '◄';
                prev.disabled = (_encursoPage[cat] <= 0);
                prev.addEventListener('click', function(){
                    _encursoPage[cat] = Math.max(0, _encursoPage[cat] - 1);
                    renderCatEncurso(cat);
                });
                pagerEl.appendChild(prev);
                var info = document.createElement('span');
                info.className = 'profile-catview-pager-info';
                info.textContent = (_encursoPage[cat] + 1) + ' / ' + totalPages;
                pagerEl.appendChild(info);
                var next = document.createElement('button');
                next.className = 'button'; next.textContent = '►';
                next.disabled = (_encursoPage[cat] >= totalPages - 1);
                next.addEventListener('click', function(){
                    _encursoPage[cat] = Math.min(totalPages - 1, _encursoPage[cat] + 1);
                    renderCatEncurso(cat);
                });
                pagerEl.appendChild(next);
            }
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
        return fetch('assets/profile/api.php?action=respond-item-invite', {
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
            fetch('assets/profile/api.php?action=get-item-collabs&category=' + encodeURIComponent(cat) + '&itemId=' + encodeURIComponent(item.id))
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
                                    fetch('assets/profile/api.php?action=leave-collab', {
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
                return k !== currentSessionUser
                    && existingCollabs.indexOf(k) === -1
                    && isMutual(k);   /* solo seguidores mutuos */
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
                                fetch('assets/profile/api.php?action=send-item-invite', {
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
                var empty = document.createElement('div');
                empty.style.cssText = 'text-align:center;padding:10px 6px;font-size:12px;line-height:1.45;';
                empty.innerHTML = 'Aún no tienes amigos que invitar.<br><span style="opacity:0.75;font-size:11px;">Seguíos entre vosotros para haceros amigos.</span>';
                list.appendChild(empty);
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
        if (!item) return;
        var itemId = item.id;   /* re-resolver por id en submit/delete */
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
        var deleteBtn = document.getElementById('profile-review-window-delete');
        var newClose  = closeBtn.cloneNode(true);  closeBtn.parentNode.replaceChild(newClose,  closeBtn);
        var newCancel = cancelBtn.cloneNode(true); cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);
        var newSubmit = submitBtn.cloneNode(true); submitBtn.parentNode.replaceChild(newSubmit, submitBtn);
        var newDelete = deleteBtn.cloneNode(true); deleteBtn.parentNode.replaceChild(newDelete, deleteBtn);
        /* Botón Eliminar solo si ya hay una reseña guardada */
        newDelete.style.display = (item.review && item.review.stars) ? '' : 'none';

        function closeWin() { win.style.display = 'none'; }
        newClose.addEventListener('click', closeWin);
        newCancel.addEventListener('click', closeWin);
        newSubmit.addEventListener('click', function() {
            if (!sel) return;
            var i = lists[cat].findIndex(function(x){ return x.id === itemId; });
            if (i === -1) { closeWin(); return; }
            lists[cat][i].review = { stars: sel, comment: commentEl.value.trim(), reviewedAt: Math.floor(Date.now() / 1000) };
            saveCategory(cat);
            notifyReviewToFollowers(cat, lists[cat][i].title, lists[cat][i].type);
            renderCatView(cat);
            closeWin();
        });
        newDelete.addEventListener('click', function() {
            var i = lists[cat].findIndex(function(x){ return x.id === itemId; });
            if (i !== -1) delete lists[cat][i].review;
            saveCategory(cat);
            renderCatView(cat);
            closeWin();
        });
    }

    function notifyReviewToFollowers(cat, itemTitle, mtype) {
        if (!itemTitle) return;
        if (viewingUser) return; /* sólo notifica reseñas hechas en MI perfil */
        fetch('assets/profile/api.php?action=notify-review', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, itemTitle: itemTitle, mtype: mtype || '' })
        }).catch(function() {});
    }

    /* ──── Category view ──── */
    var CATVIEW_PAGE_SIZE = 4;
    var catPage = { pending: 1, done: 1 };

    function showCatView(cat) {
        document.dispatchEvent(new Event('profile-edit-cancel'));
        currentCat = cat;
        catPage = { pending: 1, done: 1 };
        document.getElementById('profile-view-default').style.display = 'none';
        document.getElementById('profile-view-social').style.display  = 'none';
        var melonV = document.getElementById('profile-view-melon');
        if (melonV) melonV.style.display = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'none';
        document.getElementById('profile-sidebar-back').style.display = 'block';
        document.getElementById('profile-info-edit-btn').style.display = 'none';
        var addBtn = document.getElementById('profile-catview-add-btn');
        if (addBtn) addBtn.style.display = viewingUser ? 'none' : '';
        var catView = document.getElementById('profile-view-cat');
        catView.style.display = 'flex';
        var titleEl  = document.getElementById('profile-catview-title');
        applyTopbarUser('profile-catview-avatar-wrap', titleEl, CATS[cat].icon, CATS[cat].label);
        /* Sustituir emoji por PNG en las categorías que tienen icono
           dedicado (series → melonArchive, music → musica). El resto
           (movies, books, games) se queda con su emoji. */
        if (titleEl) {
            var CAT_PNG = {
                series: 'assets/img/appIcons/melonArchiveIcon.png',
                music:  'assets/img/appIcons/musicaIcon.png',
                movies: 'assets/img/appIcons/pelisIcon.png'
            };
            if (CAT_PNG[cat]) {
                titleEl.innerHTML = '<img src="' + CAT_PNG[cat] + '" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">' + CATS[cat].label;
            }
        }
        var doneHead = document.getElementById('profile-catview-done-head');
        if (doneHead) doneHead.textContent = DONE_LABELS[cat];
        renderCatView(cat);
        renderCatEncurso(cat);
    }

    function showDefaultView() {
        /* Si vamos al perfil de OTRO usuario, sal del modo edición */
        if (viewingUser) document.dispatchEvent(new Event('profile-edit-cancel'));
        currentCat = null;
        document.getElementById('profile-view-cat').style.display = 'none';
        document.getElementById('profile-view-social').style.display = 'none';
        var melonV = document.getElementById('profile-view-melon');
        if (melonV) melonV.style.display = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'none';
        document.getElementById('profile-sidebar-back').style.display = 'none';
        document.getElementById('profile-info-edit-btn').style.display = viewingUser ? 'none' : '';
        document.getElementById('profile-view-default').style.display = 'flex';
    }

    function renderCatView(cat) {
        var pendingGallery = document.getElementById('profile-catview-pending');
        var doneGallery    = document.getElementById('profile-catview-done');
        if (!pendingGallery || !doneGallery) return;
        var items   = lists[cat] || [];
        var pending = items.filter(function(i) { return i.status === 'pending'; });
        var done    = items.filter(function(i) { return i.status === 'completed'; });
        renderPaginatedGallery(cat, pending, pendingGallery, 'pending', 'profile-catview-pending-pager');
        renderPaginatedGallery(cat, done,    doneGallery,    'done',    'profile-catview-done-pager');
    }

    function renderPaginatedGallery(cat, items, gallery, key, pagerId) {
        var totalPages = Math.max(1, Math.ceil(items.length / CATVIEW_PAGE_SIZE));
        if (catPage[key] > totalPages) catPage[key] = totalPages;
        if (catPage[key] < 1)          catPage[key] = 1;
        var start = (catPage[key] - 1) * CATVIEW_PAGE_SIZE;
        var pageItems = items.slice(start, start + CATVIEW_PAGE_SIZE);
        renderGallery(cat, pageItems, gallery, true, false);
        renderPager(totalPages, key, pagerId);
    }

    function renderPager(totalPages, key, pagerId) {
        var pager = document.getElementById(pagerId);
        if (!pager) return;
        pager.innerHTML = '';
        if (totalPages <= 1) return;
        var prevBtn = document.createElement('button');
        prevBtn.className = 'button';
        prevBtn.textContent = '◄';
        prevBtn.disabled = catPage[key] <= 1;
        prevBtn.addEventListener('click', function() {
            if (catPage[key] > 1) { catPage[key]--; if (currentCat) renderCatView(currentCat); }
        });
        pager.appendChild(prevBtn);
        var info = document.createElement('span');
        info.className = 'profile-catview-pager-info';
        info.textContent = catPage[key] + ' / ' + totalPages;
        pager.appendChild(info);
        var nextBtn = document.createElement('button');
        nextBtn.className = 'button';
        nextBtn.textContent = '►';
        nextBtn.disabled = catPage[key] >= totalPages;
        nextBtn.addEventListener('click', function() {
            if (catPage[key] < totalPages) { catPage[key]++; if (currentCat) renderCatView(currentCat); }
        });
        pager.appendChild(nextBtn);
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
            /* idx ya no se captura por posición: lists[cat] puede haber sido
               reemplazado por la respuesta canónica de saveCategory, lo que
               invalidaría el índice fijo. Resolvemos por id en cada click. */
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
            (function(it, el) {
                el.addEventListener('click', function() {
                    if (viewingUser) return;
                    var i = lists[cat].findIndex(function(x){ return x.id === it.id; });
                    if (i === -1) return;
                    var cur  = el.dataset.status;
                    var next = STATUS_CYCLE[(STATUS_CYCLE.indexOf(cur) + 1) % STATUS_CYCLE.length];
                    el.dataset.status    = next;
                    el.textContent       = STATUS_LABELS[next];
                    lists[cat][i].status = next;
                    saveCategory(cat);
                    renderCatView(cat);
                    renderCatEncurso(cat);
                    if (next === 'completed') crearMomentoDesdeItem(cat, lists[cat][i]);
                });
            })(item, footer);

            card.appendChild(tb);
            card.appendChild(imgWrap);
            if (showFooter) card.appendChild(footer);

            if (withCtx) {
                (function(it) {
                    card.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                        var menuItems = [];
                        if (viewingUser) {
                            menuItems.push({ label: '+ Añadir a mi perfil', action: function() {
                                addItemToOwnProfile(cat, it);
                            }});
                            showCtxMenu(e.clientX, e.clientY, menuItems);
                            return;
                        }
                        var isCollab = !!it.sharedFrom;
                        if (it.status === 'completed') {
                            var reviewLabel = (it.review && it.review.stars) ? '✏ Editar reseña' : '✏ Añadir reseña';
                            menuItems.push({ label: reviewLabel, action: function() {
                                var i = lists[cat].findIndex(function(x){ return x.id === it.id; });
                                if (i !== -1) showReviewWindow(cat, i);
                            }});
                        } else {
                            menuItems.push({ label: '▶ Poner en curso', action: function() {
                                var i = lists[cat].findIndex(function(x){ return x.id === it.id; });
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
                                    fetch('assets/profile/api.php?action=leave-collab', {
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
                                    var i = lists[cat].findIndex(function(x){ return x.id === it.id; });
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
        /* Series y music tienen PNG dedicado — los renderizamos como
           innerHTML; el resto usa textContent con el emoji del dict. */
        var ADD_PNG = {
            series: 'assets/img/appIcons/melonArchiveIcon.png',
            music:  'assets/img/appIcons/musicaIcon.png'
        };
        if (ADD_PNG[cat]) {
            addDlgTitle.innerHTML = '+ Añadir · <img src="' + ADD_PNG[cat] + '" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">';
        } else {
            addDlgTitle.innerHTML = '+ Añadir · ' + CATS[cat].icon;
        }
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
        if (viewingUser) return;
        if (currentCat) openAddDialog(currentCat);
    });
    if (catviewBackBtn) catviewBackBtn.addEventListener('click', function() {
        /* Volver SIEMPRE devuelve al perfil propio */
        if (viewingUser) exitViewingUser();
        showDefaultView();
    });

    /* ──── Posts ──── */
    function relTime(ts) {
        var diff = Math.floor(Date.now() / 1000) - ts;
        if (diff < 60)   return 'ahora';
        if (diff < 3600) return 'hace ' + Math.floor(diff / 60) + 'm';
        if (diff < 86400) return 'hace ' + Math.floor(diff / 3600) + 'h';
        return 'hace ' + Math.floor(diff / 86400) + 'd';
    }

    function loadProfile(cb) {
        fetch('assets/profile/api.php?action=get-profile')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && !data.error) {
                    renderPosts(data.posts || []);
                    renderProfileInfo(data);
                    ownFollowing = Array.isArray(data.following) ? data.following : [];
                    loadMyFollowers(function() { renderFollowedNav(); });
                }
                if (cb) cb();
            })
            .catch(function() { if (cb) cb(); });
    }

    function fitBioText(bioEl) {
        var textEl = bioEl && bioEl.querySelector('.pinfo-bio-text');
        if (!textEl) return;
        if (bioEl.offsetWidth === 0 || bioEl.offsetHeight === 0) return;
        var availW = bioEl.clientWidth  - 8;
        var availH = bioEl.clientHeight - 8;
        if (availW <= 0 || availH <= 0) return;
        var lo = 9, hi = 16, best = lo;
        while (lo <= hi) {
            var mid = (lo + hi) >> 1;
            textEl.style.fontSize = mid + 'px';
            if (textEl.scrollWidth <= availW && textEl.scrollHeight <= availH) {
                best = mid;
                lo = mid + 1;
            } else {
                hi = mid - 1;
            }
        }
        textEl.style.fontSize = best + 'px';
    }

    function renderProfileInfo(data) {
        var bioEl   = document.getElementById('profile-info-bio');
        var metaEl  = document.getElementById('profile-info-meta');
        var linksEl = document.getElementById('profile-info-links');
        if (!bioEl) return;

        if (data.bio) {
            bioEl.innerHTML = '<span class="pinfo-bio-text"></span>';
            bioEl.querySelector('.pinfo-bio-text').textContent = '" ' + data.bio + ' "';
            bioEl.style.display = '';
            requestAnimationFrame(function() { fitBioText(bioEl); });
        } else {
            bioEl.innerHTML = '';
            bioEl.style.display = 'none';
        }

        metaEl.innerHTML = '';
        function line(txt) { var s = document.createElement('div'); s.className = 'pinfo-line'; s.textContent = txt; return s; }
        if (data.pronouns) metaEl.appendChild(line(data.pronouns));
        if (data.age)      metaEl.appendChild(line(data.age + ' años'));
        if (data.country)  metaEl.appendChild(line('📍 ' + data.country));

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
            a.textContent = s.icon;
            a.title = s.label + ': ' + data[s.key];
            if (s.url) {
                a.href = s.url(data[s.key]);
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
            } else {
                a.href = '#';
                a.title = s.label + ' (clic para copiar): ' + data[s.key];
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

    (function setupBioResize() {
        var bioEl = document.getElementById('profile-info-bio');
        if (!bioEl) return;
        if (window.ResizeObserver) {
            var ro = new ResizeObserver(function() { fitBioText(bioEl); });
            ro.observe(bioEl);
        } else {
            window.addEventListener('resize', function() { fitBioText(bioEl); });
        }
    })();

    /* ──── Collapsible sections ──── */
    function makeCollapsible(headerEl, bodyEl) {
        /* Si se pasa bodyEl, pliega sólo ese elemento. Si no, todos los siguientes hermanos. */
        if (!headerEl || headerEl.dataset.collapsibleReady === '1') return;
        var arrow = document.createElement('button');
        arrow.type = 'button';
        arrow.className = 'profile-collapse-arrow';
        arrow.title = 'Plegar/desplegar';
        arrow.textContent = '▼';
        headerEl.appendChild(arrow);
        headerEl.dataset.collapsibleReady = '1';

        function toggleEl(el, collapsed) {
            if (collapsed) {
                if (el.style.display !== 'none') el.dataset._prevDisp = el.style.display || '';
                el.style.display = 'none';
            } else {
                el.style.display = (el.dataset._prevDisp !== undefined) ? el.dataset._prevDisp : '';
                delete el.dataset._prevDisp;
            }
        }

        arrow.addEventListener('click', function(e) {
            e.stopPropagation();
            var collapsed = headerEl.classList.toggle('section-collapsed');
            if (bodyEl) {
                toggleEl(bodyEl, collapsed);
            } else {
                var sib = headerEl.nextElementSibling;
                while (sib) { toggleEl(sib, collapsed); sib = sib.nextElementSibling; }
            }
            arrow.textContent = collapsed ? '▶' : '▼';
        });
    }

    (function setupCollapsibles() {
        /* Pendientes / Vistas / En curso / Destacados NO son plegables (sin flecha ▼). */
        /* Mis Listas pliega sólo su contenedor de nav (NO el resto del sidebar) */
        var listasHd  = document.getElementById('profile-listas-heading');
        var listasNav = document.getElementById('profile-listas-nav');
        if (listasHd && listasNav) makeCollapsible(listasHd, listasNav);
        /* Melon reviews: pliega sólo su nav (NO el resto del sidebar) */
        var melonHd  = document.querySelector('#profile-melon-section > .profile-sidebar-heading');
        var melonNav = document.getElementById('profile-melon-nav');
        if (melonHd && melonNav) makeCollapsible(melonHd, melonNav);
        /* Social: la cabecera plega sus hermanos dentro de #profile-social-section */
        var socialHd = document.querySelector('#profile-social-section > .profile-sidebar-heading');
        if (socialHd) makeCollapsible(socialHd);
    })();

    /* ──── Profile notifications ──── */
    var profileNotifs = [];

    function loadProfileNotifs(cb) {
        fetch('assets/profile/api.php?action=get-profile-notifs')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.ok) {
                    profileNotifs = Array.isArray(data.notifs) ? data.notifs : [];
                    updateNotifBadge(data.unread || 0);
                }
                if (cb) cb();
            })
            .catch(function() { if (cb) cb(); });
    }

    function updateNotifBadge(count) {
        var badge = document.getElementById('profile-notif-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : String(count);
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }
        /* Campana en el botón del taskbar */
        var tbBtn = (window.taskbarManager && typeof taskbarManager.getButton === 'function')
            ? taskbarManager.getButton('profile-window') : null;
        if (tbBtn) {
            var bell = tbBtn.querySelector('.taskbar-notif-bell');
            if (count > 0) {
                if (!bell) {
                    bell = document.createElement('span');
                    bell.className = 'taskbar-notif-bell';
                    bell.innerHTML = '<img src="assets/img/appIcons/bellIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;display:block;margin:auto;">';
                    tbBtn.appendChild(bell);
                }
            } else if (bell) {
                bell.remove();
            }
        }
    }

    function renderProfileNotifs() {
        var listEl  = document.getElementById('profile-notifs-list');
        var emptyEl = document.getElementById('profile-notifs-empty');
        if (!listEl) return;
        listEl.innerHTML = '';
        if (!profileNotifs.length) {
            if (emptyEl) emptyEl.style.display = '';
            return;
        }
        if (emptyEl) emptyEl.style.display = 'none';
        profileNotifs.forEach(function(n) {
            var u = PROFILE_USERS[n.fromUser];
            if (!u) return;
            var row = document.createElement('div');
            row.className = 'profile-notif-row' + (n.read ? '' : ' unread');
            var avFrame = document.createElement('div');
            avFrame.className = 'profile-avatar-frame profile-notif-av';
            if (u.image) {
                var img = document.createElement('img');
                img.src = u.image; img.alt = u.label;
                avFrame.appendChild(img);
            } else {
                avFrame.innerHTML = '<div class="profile-avatar-placeholder">👤</div>';
            }
            row.appendChild(avFrame);
            var info = document.createElement('div');
            info.className = 'profile-notif-info';
            var msg = document.createElement('div');
            msg.className = 'profile-notif-msg';
            if (n.type === 'follow') {
                msg.innerHTML = '<strong>' + escHtml(u.label) + '</strong> te ha seguido';
            } else if (n.type === 'like') {
                var snippet = n.postText ? n.postText : '';
                if (snippet.length > 60) snippet = snippet.substring(0, 60) + '…';
                msg.innerHTML = '<strong>' + escHtml(u.label) + '</strong> le ha dado ❤ a tu publicación'
                    + (snippet ? ': <em>"' + escHtml(snippet) + '"</em>' : '');
            } else if (n.type === 'review') {
                var noun;
                if (n.category === 'movies')      noun = 'la película';
                else if (n.category === 'series') noun = 'la serie';
                else if (n.category === 'books')  noun = 'el libro';
                else if (n.category === 'games')  noun = 'el videojuego';
                else if (n.category === 'music')  noun = (n.mtype === 'album') ? 'el álbum' : 'la canción';
                else                              noun = '';
                var itemT = n.itemTitle ? n.itemTitle : '';
                msg.innerHTML = '<strong>' + escHtml(u.label) + '</strong> ha reseñado '
                    + (noun ? noun + ' ' : '')
                    + '<em>"' + escHtml(itemT) + '"</em>';
            } else {
                msg.textContent = u.label;
            }
            info.appendChild(msg);
            var t = document.createElement('div');
            t.className = 'profile-notif-time';
            t.textContent = relTime(n.createdAt || 0);
            info.appendChild(t);
            row.appendChild(info);

            /* Botón "Seguir" para notifs de tipo follow */
            if (n.type === 'follow') {
                var fbBtn = document.createElement('button');
                fbBtn.className = 'button profile-notif-follow-btn';
                var nowFollowing = ownFollowing.indexOf(n.fromUser) !== -1;
                fbBtn.textContent = nowFollowing ? '✓ Siguiendo' : '+ Seguir';
                if (nowFollowing) fbBtn.classList.add('following');
                (function(uk, b) {
                    b.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (b.dataset.busy === '1') return;
                        b.dataset.busy = '1';
                        fetch('assets/profile/api.php?action=toggle-follow', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ targetUser: uk })
                        }).then(function(r) { return r.json(); })
                          .then(function(d) {
                              b.dataset.busy = '';
                              if (!d || d.error) { if (d && d.error) alert(d.error); return; }
                              ownFollowing = Array.isArray(d.list) ? d.list : ownFollowing;
                              b.textContent = d.following ? '✓ Siguiendo' : '+ Seguir';
                              b.classList.toggle('following', !!d.following);
                              renderFollowedNav();
                              /* Si el perfil que se está viendo es este usuario, sincroniza también el botón principal */
                              if (viewingUser === uk) updateFollowButton(!!d.following);
                          }).catch(function() { b.dataset.busy = ''; });
                    });
                })(n.fromUser, fbBtn);
                row.appendChild(fbBtn);
            }

            (function(uk) {
                row.addEventListener('click', function() {
                    closeNotifsWindow();
                    viewOtherUser(uk);
                });
            })(n.fromUser);
            listEl.appendChild(row);
        });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function openNotifsWindow() {
        var win = document.getElementById('profile-notifs-window');
        if (!win) return;
        renderProfileNotifs();
        win.style.display = 'block';
        win.style.left = Math.round((window.innerWidth  - win.offsetWidth)  / 2) + 'px';
        win.style.top  = Math.round((window.innerHeight - win.offsetHeight) / 2) + 'px';
        /* Marca todas como leídas en servidor + UI */
        if (profileNotifs.some(function(n) { return !n.read; })) {
            fetch('assets/profile/api.php?action=mark-notifs-read', { method: 'POST' }).catch(function(){});
            profileNotifs.forEach(function(n) { n.read = true; });
            updateNotifBadge(0);
        }
    }
    function closeNotifsWindow() {
        var win = document.getElementById('profile-notifs-window');
        if (win) win.style.display = 'none';
    }

    (function setupNotifBtn() {
        var btn  = document.getElementById('profile-notif-btn');
        var clo  = document.getElementById('profile-notifs-close');
        if (btn) btn.addEventListener('click', openNotifsWindow);
        if (clo) clo.addEventListener('click', closeNotifsWindow);
    })();

    (function setupFollowBtn() {
        var btn = document.getElementById('profile-follow-btn');
        if (!btn) return;
        btn.addEventListener('click', function() {
            if (!viewingUser || btn.dataset.busy === '1') return;
            btn.dataset.busy = '1';
            fetch('assets/profile/api.php?action=toggle-follow', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ targetUser: viewingUser })
            }).then(function(r) { return r.json(); })
              .then(function(d) {
                  btn.dataset.busy = '';
                  if (!d || d.error) { if (d && d.error) alert(d.error); return; }
                  ownFollowing = Array.isArray(d.list) ? d.list : ownFollowing;
                  updateFollowButton(!!d.following);
                  loadMyFollowers(function() { renderFollowedNav(); });
              }).catch(function() { btn.dataset.busy = ''; });
        });
    })();

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
        var isViewing = !!viewingUser;
        posts.forEach(function(post) {
            var box = document.createElement('div');
            box.className = 'profile-post-box';

            var hdr = document.createElement('div');
            hdr.className = 'profile-post-hdr';
            var timeEl = document.createElement('span');
            timeEl.className = 'profile-post-time';
            timeEl.textContent = relTime(post.createdAt || 0);
            hdr.appendChild(timeEl);

            /* Like (corazón) en la esquina derecha cuando se ve otro perfil */
            if (isViewing) {
                var likes = Array.isArray(post.likes) ? post.likes : [];
                var likedByMe = likes.indexOf(currentSessionUser) !== -1;
                var likeWrap = document.createElement('span');
                likeWrap.className = 'profile-post-like' + (likedByMe ? ' liked' : '');
                var countEl = document.createElement('span');
                countEl.className = 'profile-post-like-count';
                countEl.textContent = likes.length;
                var heart = document.createElement('span');
                heart.className = 'profile-post-like-icon';
                heart.textContent = likedByMe ? '❤' : '♡';
                likeWrap.appendChild(countEl);
                likeWrap.appendChild(heart);
                (function(p, wrap, c, h) {
                    wrap.addEventListener('click', function() {
                        if (wrap.dataset.busy === '1') return;
                        wrap.dataset.busy = '1';
                        fetch('assets/profile/api.php?action=toggle-post-like', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ targetUser: viewingUser, postId: p.id })
                        }).then(function(r) { return r.json(); })
                          .then(function(d) {
                              wrap.dataset.busy = '';
                              if (!d || d.error) return;
                              c.textContent = d.count;
                              h.textContent = d.liked ? '❤' : '♡';
                              wrap.classList.toggle('liked', !!d.liked);
                              /* Actualiza el array local para que el estado persista en re-render */
                              if (!Array.isArray(p.likes)) p.likes = [];
                              var idx = p.likes.indexOf(currentSessionUser);
                              if (d.liked && idx === -1)      p.likes.push(currentSessionUser);
                              else if (!d.liked && idx !== -1) p.likes.splice(idx, 1);
                          }).catch(function() { wrap.dataset.busy = ''; });
                    });
                })(post, likeWrap, countEl, heart);
                hdr.appendChild(likeWrap);
            } else {
                var rightCol = document.createElement('div');
                rightCol.className = 'profile-post-right';
                var delBtn = document.createElement('button');
                delBtn.className = 'button profile-post-del';
                delBtn.textContent = '×';
                delBtn.addEventListener('click', function() {
                    confirmFn('¿Eliminar este post?', 'Eliminar', function() {
                        fetch('assets/profile/api.php?action=delete-post', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: post.id })
                        }).then(function() { loadProfile(); }).catch(function() {});
                    });
                });
                rightCol.appendChild(delBtn);
                var ownLikes = Array.isArray(post.likes) ? post.likes.length : 0;
                var ownLikeEl = document.createElement('span');
                ownLikeEl.className = 'profile-post-like-display';
                ownLikeEl.innerHTML = '<span class="profile-post-like-count">' + ownLikes + '</span><span class="profile-post-like-icon">❤</span>';
                rightCol.appendChild(ownLikeEl);
                hdr.appendChild(rightCol);
            }

            box.appendChild(hdr);
            /* Texto primero (como un caption arriba), luego la imagen */
            if (post.text) {
                var txt = document.createElement('div');
                txt.className = 'profile-post-text';
                txt.textContent = post.text;
                box.appendChild(txt);
            }
            if (post.imageUrl) {
                var imgWrap = document.createElement('div');
                imgWrap.className = 'profile-post-img';
                var imgEl = document.createElement('img');
                /* no-referrer evita que la Enhanced Tracking Protection de
                   Firefox bloquee la petición a drive.google.com como si
                   fuese un tracker de Google. */
                imgEl.referrerPolicy = 'no-referrer';
                imgEl.loading = 'lazy';
                imgEl.alt = '';
                imgEl.onerror = function() {
                    imgWrap.innerHTML = '<div class="profile-post-img-err">' +
                        '⚠ No se pudo cargar la imagen ' +
                        '<small>(el fichero ha sido borrado de Drive o ya no es público)</small>' +
                        '</div>';
                };
                imgEl.src = post.imageUrl;
                imgWrap.appendChild(imgEl);
                box.appendChild(imgWrap);
            }
            /* Comentarios */
            var commentsWrap = document.createElement('div');
            commentsWrap.className = 'profile-post-comments';
            renderCommentsInto(commentsWrap, post);
            box.appendChild(commentsWrap);
            list.appendChild(box);
        });
    }

    /* Pinta la lista de comentarios + input para añadir uno nuevo dentro de
       `wrap`. Por defecto colapsado: solo el toggle "💬 N comentarios". Al
       expandir aparecen lista y form. El estado expandido se preserva entre
       re-renders (al añadir/borrar) porque vive en wrap.classList. */
    var COMMENTS_PER_PAGE = 10;
    function renderCommentsInto(wrap, post) {
        var wasExpanded = wrap.classList.contains('is-expanded');
        wrap.innerHTML = '';
        var comments  = Array.isArray(post.comments) ? post.comments : [];
        var ownProfile = !viewingUser;

        /* Paginación: estado en wrap.dataset.page (1-indexed) para
           preservar la página entre re-renders (al añadir/borrar). */
        var totalPages  = Math.max(1, Math.ceil(comments.length / COMMENTS_PER_PAGE));
        var currentPage = parseInt(wrap.dataset.page || '1', 10) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;
        wrap.dataset.page = String(currentPage);
        var pageStart = (currentPage - 1) * COMMENTS_PER_PAGE;
        var pageSlice = comments.slice(pageStart, pageStart + COMMENTS_PER_PAGE);

        /* Toggle visible siempre */
        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'profile-post-comments-toggle';
        var label = comments.length === 0
            ? '💬 Comentar'
            : '💬 ' + comments.length + ' ' + (comments.length === 1 ? 'comentario' : 'comentarios');
        toggle.innerHTML = '<span>' + label + '</span><span class="profile-post-comments-chev">▾</span>';
        wrap.appendChild(toggle);

        var inner = document.createElement('div');
        inner.className = 'profile-post-comments-inner';
        inner.style.display = wasExpanded ? '' : 'none';
        wrap.appendChild(inner);

        toggle.addEventListener('click', function() {
            wrap.classList.toggle('is-expanded');
            var nowExp = wrap.classList.contains('is-expanded');
            inner.style.display = nowExp ? '' : 'none';
            toggle.querySelector('.profile-post-comments-chev').textContent = nowExp ? '▴' : '▾';
        });
        /* Restaurar chevron tras re-render si estaba abierto */
        if (wasExpanded) toggle.querySelector('.profile-post-comments-chev').textContent = '▴';

        var listEl = document.createElement('div');
        listEl.className = 'profile-post-comments-list';
        pageSlice.forEach(function(c) {
            var row = document.createElement('div');
            row.className = 'profile-post-comment';
            var avSrc = (PROFILE_USERS[c.authorKey] && PROFILE_USERS[c.authorKey].image) || '';
            var avHtml = avSrc
                ? '<img class="profile-post-comment-av" src="' + escAttr(avSrc) + '" alt="">'
                : '<span class="profile-post-comment-av-ph">👤</span>';
            row.innerHTML = avHtml +
                '<div class="profile-post-comment-body">' +
                    '<div class="profile-post-comment-head">' +
                        '<span class="profile-post-comment-author">' + escHtml(c.authorLabel || c.authorKey) + '</span>' +
                        '<span class="profile-post-comment-time">' + relTime(c.createdAt || 0) + '</span>' +
                    '</div>' +
                    '<div class="profile-post-comment-text"></div>' +
                '</div>';
            row.querySelector('.profile-post-comment-text').textContent = c.text;

            /* Borrar: si soy el autor del comentario O dueño del post */
            var canDelete = (c.authorKey === currentSessionUser) || ownProfile;
            if (canDelete) {
                var x = document.createElement('button');
                x.type = 'button';
                x.className = 'profile-post-comment-del';
                x.textContent = '×';
                x.title = 'Borrar comentario';
                (function(cid) {
                    x.addEventListener('click', function(e) {
                        e.stopPropagation();
                        confirmFn('¿Eliminar este comentario?', 'Eliminar', function() {
                            fetch('assets/profile/api.php?action=delete-comment', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: cid })
                            }).then(function(r) { return r.json(); })
                              .then(function(d) {
                                  if (!d || d.error) return;
                                  post.comments = post.comments.filter(function(x) { return x.id !== cid; });
                                  renderCommentsInto(wrap, post);
                              });
                        });
                    });
                })(c.id);
                row.appendChild(x);
            }
            listEl.appendChild(row);
        });
        inner.appendChild(listEl);

        /* Controles de paginación (solo si hay más de una página) */
        if (totalPages > 1) {
            var pag = document.createElement('div');
            pag.className = 'profile-post-comments-pag';
            var prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'button profile-post-comments-pag-btn';
            prev.textContent = '«';
            prev.title = 'Página anterior';
            prev.disabled = currentPage <= 1;
            prev.addEventListener('click', function() {
                wrap.dataset.page = String(currentPage - 1);
                renderCommentsInto(wrap, post);
            });
            var info = document.createElement('span');
            info.className = 'profile-post-comments-pag-info';
            info.textContent = currentPage + ' / ' + totalPages;
            var next = document.createElement('button');
            next.type = 'button';
            next.className = 'button profile-post-comments-pag-btn';
            next.textContent = '»';
            next.title = 'Página siguiente';
            next.disabled = currentPage >= totalPages;
            next.addEventListener('click', function() {
                wrap.dataset.page = String(currentPage + 1);
                renderCommentsInto(wrap, post);
            });
            pag.appendChild(prev);
            pag.appendChild(info);
            pag.appendChild(next);
            inner.appendChild(pag);
        }

        /* Caja de input para nuevo comentario */
        var form = document.createElement('div');
        form.className = 'profile-post-comment-form';
        var input = document.createElement('input');
        input.type = 'text';
        input.maxLength = 500;
        input.placeholder = 'Comentar…';
        input.className = 'profile-post-comment-input';
        var sendBtn = document.createElement('button');
        sendBtn.type = 'button';
        sendBtn.className = 'button profile-post-comment-send';
        sendBtn.textContent = 'Enviar';
        function send() {
            var t = input.value.trim();
            if (!t) return;
            sendBtn.disabled = true;
            fetch('assets/profile/api.php?action=add-comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ postId: post.id, text: t })
            }).then(function(r) { return r.json(); })
              .then(function(d) {
                  sendBtn.disabled = false;
                  if (!d || d.error) return;
                  if (!Array.isArray(post.comments)) post.comments = [];
                  post.comments.push(d.comment);
                  input.value = '';
                  /* Saltar a la última página para que el comentario recién
                     enviado sea visible (los comments van ASC por fecha). */
                  wrap.dataset.page = String(Math.ceil(post.comments.length / COMMENTS_PER_PAGE));
                  renderCommentsInto(wrap, post);
              })
              .catch(function() { sendBtn.disabled = false; });
        }
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); send(); }
        });
        sendBtn.addEventListener('click', send);
        form.appendChild(input);
        form.appendChild(sendBtn);
        inner.appendChild(form);
    }
    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) {
            return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;' })[c];
        });
    }
    function escAttr(s) { return escHtml(s); }

    var postInput = document.getElementById('profile-post-input');
    var postBtn   = document.getElementById('profile-post-btn');
    if (postBtn) {
        postBtn.addEventListener('click', function() {
            var text = postInput ? postInput.value.trim() : '';
            if (!text) return;
            fetch('assets/profile/api.php?action=add-post', {
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

    /* ──── Social: list of other users + read-only view of their profile ──── */
    var viewingUser  = null;     /* userKey si estamos viendo otro perfil, null si no */
    var ownLists     = null;     /* backup de las listas propias mientras se ve a otro */
    var ownFollowing = [];       /* lista (array de userKey) de la gente que sigo */
    var myFollowers  = [];       /* lista (array de userKey) de la gente que me sigue */
    var OWN_AVATAR_IMG = <?php echo json_encode(isset($profileImg) ? $profileImg : ''); ?>;
    var OWN_LABEL      = <?php echo json_encode($desktopLabel); ?>;

    function applyTopbarUser(wrapId, titleEl, icon, ownLabel) {
        /* Cambia avatar/nombre del topbar y el título según viewingUser */
        var wrap = document.getElementById(wrapId);
        if (!wrap) return;
        var frame = wrap.querySelector('.profile-avatar-frame');
        var nameEl = wrap.querySelector('.profile-catview-username');
        var img, label;
        if (viewingUser && PROFILE_USERS[viewingUser]) {
            var u = PROFILE_USERS[viewingUser];
            img   = u.image || '';
            label = u.label;
        } else {
            img   = OWN_AVATAR_IMG;
            label = OWN_LABEL;
        }
        if (frame) {
            frame.innerHTML = img
                ? '<img src="' + img + '" alt="" class="profile-avatar-img">'
                : '<div class="profile-avatar-placeholder">👤</div>';
            /* Dot de presencia para el usuario al que pertenece la topbar.
               Si no estoy viewing-other, es mi propio user — el dot
               siempre se pintará como online (mi heartbeat está activo). */
            if (window.__attachPresenceDot) {
                window.__attachPresenceDot(frame, viewingUser || currentSessionUser);
            }
        }
        if (nameEl) nameEl.textContent = label;
        if (titleEl) titleEl.innerHTML = icon + ' ' + ownLabel;
    }

    function showSocialView() {
        document.dispatchEvent(new Event('profile-edit-cancel'));
        currentCat = null;
        document.getElementById('profile-view-default').style.display = 'none';
        document.getElementById('profile-view-cat').style.display     = 'none';
        var melonV = document.getElementById('profile-view-melon');
        if (melonV) melonV.style.display = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'none';
        document.getElementById('profile-view-social').style.display  = 'flex';
        document.getElementById('profile-sidebar-back').style.display = 'block';
        document.getElementById('profile-info-edit-btn').style.display = 'none';
        renderSocialList();
        loadProfile(function() { renderSocialList(); });
    }

    /* ──── Melon reviews ──── */
    var MELON_LABELS = { year: 'Mejor del año', recent: 'Reciente', alltime: 'Todo el tiempo' };
    var MELON_ICONS  = { movies: '<img src="assets/img/appIcons/pelisIcon.png" alt="" style="width:24px;height:24px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', series: '📺', books: '<img src="assets/img/appIcons/booksIcon.png" alt="" style="width:24px;height:24px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', games: '<img src="assets/img/appIcons/juegosIcon.png" alt="" style="width:24px;height:24px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', music: '🎵' };
    var MELON_MUST_VERBS = { movies: 'watch', series: 'watch', books: 'read', games: 'play', music: 'listen' };
    var melonPeriod = null;
    var melonCat    = null;
    var melonType   = null;
    var melonItemsCache = [];
    var melonPage = 1;
    var MELON_PAGE_SIZE = 20;

    function addMelonItemToProfile(cat, item) {
        if (!cat || !item || !item.title) return;
        var titleLower = item.title.toLowerCase().trim();
        if (!titleLower) return;
        var ownLs = ownLists || lists;
        var arr = ownLs[cat] || [];
        var dup = arr.some(function(it) { return (it.title || '').toLowerCase().trim() === titleLower; });
        if (dup) {
            if (window.notifSystem) {
                window.notifSystem.show({
                    id:      'dup_' + Date.now(),
                    type:    'info',
                    title:   'Ya en tu perfil',
                    message: '"' + item.title + '" ya está en tu lista.'
                });
            }
            return;
        }
        var entry;
        if (cat === 'music') {
            entry = {
                id:       'music_' + Date.now(),
                type:     item.mtype || 'song',
                title:    item.title,
                artist:   item.artist || '',
                image:    item.image  || '',
                featured: false
            };
            if (item.ytId)           entry.ytId           = item.ytId;
            if (item.spotifyId)      entry.spotifyId      = item.spotifyId;
            if (item.ytPlaylistId)   entry.ytPlaylistId   = item.ytPlaylistId;
            if (item.spotifyAlbumId) entry.spotifyAlbumId = item.spotifyAlbumId;
        } else {
            entry = {
                id:     'item_' + Date.now(),
                title:  item.title,
                image:  item.image || '',
                status: 'pending'
            };
        }
        if (!ownLs[cat]) ownLs[cat] = [];
        ownLs[cat].push(entry);
        var newIdx = ownLs[cat].length - 1;
        fetch('assets/profile/api.php?action=save-lists', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: ownLs[cat] })
        }).then(function(r) { return r.json(); })
          .then(function(d) {
              if (d && d.error) { alert(d.error); return; }
              if (window.notifSystem) {
                  window.notifSystem.show({
                      id:      'added_' + Date.now(),
                      type:    'info',
                      title:   cat === 'music' ? 'Música añadida' : 'Añadido a tu perfil',
                      message: cat === 'music'
                          ? '"' + item.title + '" añadido a tu lista.'
                          : '"' + item.title + '" añadido como pendiente.'
                  });
              }
              if (!viewingUser) updateCounts();
              if (cat === 'music') showAddedMusicReviewPrompt(newIdx, entry);
          }).catch(function() { alert('Error al guardar'); });
    }

    function showMelonView(period) {
        document.dispatchEvent(new Event('profile-edit-cancel'));
        if (!MELON_LABELS[period]) return;
        melonPeriod = period;
        currentCat  = null;
        document.getElementById('profile-view-default').style.display = 'none';
        document.getElementById('profile-view-cat').style.display     = 'none';
        document.getElementById('profile-view-social').style.display  = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'none';
        document.getElementById('profile-view-melon').style.display   = 'flex';
        document.getElementById('profile-sidebar-back').style.display = 'block';
        document.getElementById('profile-info-edit-btn').style.display = 'none';
        document.getElementById('profile-melon-title').textContent     = '⭐ ' + MELON_LABELS[period];
        /* Cargar películas por defecto al entrar */
        melonCat  = 'movies';
        melonType = null;
        document.querySelectorAll('.melon-cat-btn').forEach(function(b) {
            b.classList.toggle('active', b.dataset.mcat === 'movies' && !b.dataset.mtype);
        });
        var listEl = document.getElementById('profile-melon-list');
        if (listEl) listEl.innerHTML = '';
        loadMelonItems();
    }

    function loadMelonItems() {
        if (!melonPeriod || !melonCat) return;
        var statusEl = document.getElementById('profile-melon-status');
        var listEl   = document.getElementById('profile-melon-list');
        if (statusEl) { statusEl.style.display = ''; statusEl.textContent = 'Cargando...'; }
        if (listEl) listEl.innerHTML = '';
        var url = 'assets/profile/api.php?action=melon-reviews&period=' + encodeURIComponent(melonPeriod) + '&cat=' + encodeURIComponent(melonCat);
        if (melonType) url += '&type=' + encodeURIComponent(melonType);
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data || !data.ok) {
                    if (statusEl) { statusEl.style.display = ''; statusEl.textContent = (data && data.error) ? data.error : 'Error'; }
                    return;
                }
                melonItemsCache = data.items || [];
                melonPage = 1;
                renderMelonItems();
            }).catch(function() {
                if (statusEl) { statusEl.style.display = ''; statusEl.textContent = 'Error de red'; }
            });
    }

    function renderMelonItems() {
        var statusEl = document.getElementById('profile-melon-status');
        var listEl   = document.getElementById('profile-melon-list');
        var pagerEl  = document.getElementById('profile-melon-pager');
        if (!listEl) return;
        listEl.innerHTML = '';
        if (pagerEl) pagerEl.innerHTML = '';
        if (!melonItemsCache.length) {
            if (statusEl) { statusEl.style.display = ''; statusEl.textContent = 'No hay reseñas en este período.'; }
            return;
        }
        if (statusEl) statusEl.style.display = 'none';

        var totalPages = Math.max(1, Math.ceil(melonItemsCache.length / MELON_PAGE_SIZE));
        if (melonPage > totalPages) melonPage = totalPages;
        if (melonPage < 1) melonPage = 1;
        var start = (melonPage - 1) * MELON_PAGE_SIZE;
        var pageItems = melonItemsCache.slice(start, start + MELON_PAGE_SIZE);

        var isMusic = (melonCat === 'music');
        pageItems.forEach(function(item) {
            var isMust = item.avg > 4.4;
            var slot = document.createElement('div');
            slot.className = 'profile-encurso-slot filled melon-slot'
                + (isMusic ? ' melon-slot-music' : '')
                + (isMust ? ' melon-must' : '');

            if (isMust) {
                var badge = document.createElement('div');
                badge.className = 'melon-must-badge';
                badge.textContent = '★ Melon must ' + (MELON_MUST_VERBS[melonCat] || 'see');
                slot.appendChild(badge);
            }

            /* Title bar */
            var tb = document.createElement('div');
            tb.className = 'profile-encurso-slot-tb' + (isMusic ? ' music-slot-tb' : '');
            if (isMusic) {
                var tbTitle = document.createElement('div');
                tbTitle.className = 'music-slot-tb-title';
                tbTitle.textContent = item.title; tbTitle.title = item.title;
                tb.appendChild(tbTitle);
                if (item.artist) {
                    var tbArtist = document.createElement('div');
                    tbArtist.className = 'music-slot-tb-artist';
                    tbArtist.textContent = item.artist;
                    tb.appendChild(tbArtist);
                }
                var tbStars = document.createElement('div');
                tbStars.className = 'music-slot-tb-stars';
                tbStars.innerHTML = makeStarsHtml(item.avg, 5)
                    + '<span class="profile-star-num" style="font-size:8px;margin-left:2px;vertical-align:middle;">' + item.avg.toFixed(1) + '</span>'
                    + '<span style="font-size:8px;margin-left:3px;color:#c0c0c0;">(' + item.count + ')</span>';
                tb.appendChild(tbStars);
            } else {
                tb.textContent = item.title;
                tb.title = item.title;
            }
            slot.appendChild(tb);

            /* Body */
            var body = document.createElement('div');
            body.className = 'profile-encurso-slot-body';
            var fallback = isMusic ? (item.mtype === 'album' ? '💿' : '🎵') : (MELON_ICONS[melonCat] || '🖼');
            if (item.image) {
                var img = document.createElement('img');
                img.src = item.image; img.alt = item.title;
                (function(b, fb) { img.onerror = function() { b.innerHTML = fb; }; })(body, fallback);
                body.appendChild(img);
            } else {
                body.innerHTML = fallback;
            }
            slot.appendChild(body);

            /* For non-music slots, add rating below the image */
            if (!isMusic) {
                var rating = document.createElement('div');
                rating.className = 'melon-slot-rating';
                rating.innerHTML = makeStarsHtml(item.avg, 5)
                    + '<span class="melon-slot-rating-avg">' + item.avg.toFixed(1) + '</span>'
                    + '<span class="melon-slot-rating-count">(' + item.count + ')</span>';
                slot.appendChild(rating);
            }

            /* Click → ver reseñas */
            slot.style.cursor = 'pointer';
            (function(it) { slot.addEventListener('click', function() { showMelonDetails(it); }); })(item);

            /* Click derecho → menú contextual */
            (function(it, cat) {
                slot.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    var menuItems = [];
                    if (isMusic) {
                        menuItems.push({ label: '▶ Reproducir', action: function() { playMusicItem(it); } });
                    }
                    menuItems.push({ label: '+ Añadir a mi perfil', action: function() { addMelonItemToProfile(cat, it); } });
                    showCtxMenu(e.clientX, e.clientY, menuItems);
                });
            })(item, melonCat);

            listEl.appendChild(slot);
        });

        /* Pagination */
        if (pagerEl && totalPages > 1) {
            var prev = document.createElement('button');
            prev.className = 'button';
            prev.textContent = '◄';
            prev.disabled = melonPage <= 1;
            prev.addEventListener('click', function() { if (melonPage > 1) { melonPage--; renderMelonItems(); } });
            pagerEl.appendChild(prev);
            var info = document.createElement('span');
            info.className = 'melon-pager-info';
            info.textContent = melonPage + ' / ' + totalPages + '  ·  ' + melonItemsCache.length + ' items';
            pagerEl.appendChild(info);
            var next = document.createElement('button');
            next.className = 'button';
            next.textContent = '►';
            next.disabled = melonPage >= totalPages;
            next.addEventListener('click', function() { if (melonPage < totalPages) { melonPage++; renderMelonItems(); } });
            pagerEl.appendChild(next);
        }
    }

    function showMelonDetails(item) {
        var win = document.getElementById('profile-melon-details-window');
        if (!win) return;
        document.getElementById('profile-melon-details-title').textContent = '⭐ ' + item.title;
        var list = document.getElementById('profile-melon-details-list');
        list.innerHTML = '';
        (item.reviews || []).forEach(function(rev) {
            var row = document.createElement('div');
            row.className = 'melon-detail-row';
            var avFrame = document.createElement('div');
            avFrame.className = 'profile-avatar-frame melon-detail-av';
            if (rev.userImg) {
                var img = document.createElement('img');
                img.src = rev.userImg; img.alt = rev.userLabel;
                avFrame.appendChild(img);
            } else {
                avFrame.innerHTML = '<div class="profile-avatar-placeholder">👤</div>';
            }
            row.appendChild(avFrame);
            var body = document.createElement('div');
            body.className = 'melon-detail-body';
            var hdr = document.createElement('div');
            hdr.className = 'melon-detail-hdr';
            hdr.innerHTML = '<strong>' + escHtml(rev.userLabel) + '</strong> '
                + makeStarsHtml(rev.stars, 5)
                + '<span class="melon-item-avg">' + rev.stars + '</span>';
            body.appendChild(hdr);
            if (rev.comment) {
                var cmt = document.createElement('div');
                cmt.className = 'melon-detail-comment';
                cmt.textContent = '" ' + rev.comment + ' "';
                body.appendChild(cmt);
            }
            var t = document.createElement('div');
            t.className = 'melon-detail-time';
            t.textContent = rev.reviewedAt ? relTime(rev.reviewedAt) : '';
            body.appendChild(t);
            row.appendChild(body);
            list.appendChild(row);
        });
        win.style.display = 'block';
        win.style.left = Math.round((window.innerWidth  - win.offsetWidth)  / 2) + 'px';
        win.style.top  = Math.round((window.innerHeight - win.offsetHeight) / 2) + 'px';
    }

    (function setupMelon() {
        document.querySelectorAll('.melon-cat-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                melonCat  = btn.dataset.mcat;
                melonType = btn.dataset.mtype || null;
                document.querySelectorAll('.melon-cat-btn').forEach(function(b) { b.classList.toggle('active', b === btn); });
                loadMelonItems();
            });
        });
        var closeBtn = document.getElementById('profile-melon-details-close');
        if (closeBtn) closeBtn.addEventListener('click', function() {
            document.getElementById('profile-melon-details-window').style.display = 'none';
        });
    })();

    function buildSocialCard(userKey) {
        var u = PROFILE_USERS[userKey];
        if (!u) return null;
        var card = document.createElement('div');
        card.className = 'profile-social-card';
        var avWrap = document.createElement('div');
        avWrap.className = 'profile-avatar-frame';
        if (u.image) {
            var img = document.createElement('img');
            img.className = 'profile-avatar-img';
            img.src = u.image;
            img.alt = u.label;
            avWrap.appendChild(img);
        } else {
            avWrap.innerHTML = '<div class="profile-avatar-placeholder">👤</div>';
        }
        /* Punto de presencia — empieza offline, el poller lo actualizará.
           Necesita has-presence-dot para anular el overflow:hidden del
           frame que clipearía el dot que está en bottom:-2px right:-2px. */
        avWrap.classList.add('has-presence-dot');
        var dot = document.createElement('span');
        dot.className = 'pf-presence-dot';
        dot.setAttribute('data-userkey', userKey);
        avWrap.appendChild(dot);
        card.appendChild(avWrap);
        var nameEl = document.createElement('div');
        nameEl.className = 'profile-social-name';
        nameEl.textContent = u.label;
        card.appendChild(nameEl);
        card.addEventListener('click', function() { viewOtherUser(userKey); });
        return card;
    }

    function renderSocialList() {
        var exploreEl = document.getElementById('profile-social-explore');
        if (!exploreEl) return;
        exploreEl.innerHTML = '';

        var followingSet = {};
        ownFollowing.forEach(function(k) { followingSet[k] = true; });
        var exploreKeys = Object.keys(PROFILE_USERS)
            .filter(function(k) { return k !== currentSessionUser && !followingSet[k]; });
        exploreKeys.sort(function(a, b) {
            var la = (PROFILE_USERS[a].label || '').toLowerCase();
            var lb = (PROFILE_USERS[b].label || '').toLowerCase();
            return la < lb ? -1 : la > lb ? 1 : 0;
        });

        exploreKeys.forEach(function(k) {
            var card = buildSocialCard(k);
            if (card) exploreEl.appendChild(card);
        });
        /* Re-aplica el estado de presencia conocido sin esperar al fetch. */
        if (window.__applyPresence) window.__applyPresence();
        if (!exploreKeys.length) {
            exploreEl.innerHTML = '<div class="profile-social-empty">No hay más usuarios.</div>';
        }
    }

    function renderFollowedNav() {
        var nav = document.getElementById('profile-followed-nav');
        if (!nav) return;
        nav.innerHTML = '';
        var keys = ownFollowing.filter(function(k) { return PROFILE_USERS[k]; });
        keys.sort(function(a, b) {
            var la = (PROFILE_USERS[a].label || '').toLowerCase();
            var lb = (PROFILE_USERS[b].label || '').toLowerCase();
            return la < lb ? -1 : la > lb ? 1 : 0;
        });
        keys.forEach(function(k) {
            var u = PROFILE_USERS[k];
            var item = document.createElement('div');
            item.className = 'profile-nav-item profile-nav-followed';
            item.dataset.user = k;
            var iconWrap = document.createElement('span');
            iconWrap.className = 'profile-nav-icon profile-nav-icon-followed';
            if (u.image) {
                var img = document.createElement('img');
                img.className = 'profile-nav-avatar';
                img.src = u.image; img.alt = u.label;
                iconWrap.appendChild(img);
            } else {
                iconWrap.textContent = '👤';
            }
            /* Punto de presencia para los followed — más pequeño que en
               las social cards porque el avatar de la sidebar es 22×22. */
            var navDot = document.createElement('span');
            navDot.className = 'pf-presence-dot pf-presence-dot-small';
            navDot.setAttribute('data-userkey', k);
            iconWrap.appendChild(navDot);
            item.appendChild(iconWrap);
            var label = document.createElement('span');
            label.className = 'profile-nav-label';
            label.textContent = u.label;
            item.appendChild(label);
            (function(uk) {
                item.addEventListener('click', function() { viewOtherUser(uk); });
            })(k);
            if (isMutual(k)) {
                var chatBtn = document.createElement('span');
                chatBtn.className = 'profile-nav-chat';
                chatBtn.title = 'Chat con ' + u.label;
                var ico = document.createElement('span');
                ico.className = 'profile-nav-chat-ico';
                ico.textContent = '💬';
                chatBtn.appendChild(ico);
                var unread = unreadChats[k] || 0;
                if (unread > 0) {
                    var badge = document.createElement('span');
                    badge.className = 'profile-nav-chat-badge';
                    badge.textContent = unread > 9 ? '+9' : String(unread);
                    chatBtn.appendChild(badge);
                }
                (function(uk) {
                    chatBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        openChatWith(uk);
                    });
                })(k);
                item.appendChild(chatBtn);
            }
            nav.appendChild(item);
        });
        /* Re-aplica el estado de presencia conocido. */
        if (window.__applyPresence) window.__applyPresence();
    }

    function viewOtherUser(userKey) {
        /* Al ver el perfil de otra persona, salimos de modo edición */
        document.dispatchEvent(new Event('profile-edit-cancel'));
        fetch('assets/profile/api.php?action=view-user&user=' + encodeURIComponent(userKey))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data || data.error) { alert(data && data.error ? data.error : 'Error'); return; }
                if (!ownLists) ownLists = lists;
                lists = data.lists;
                viewingUser = data.userKey;
                /* Pinta cabecera con datos del otro usuario */
                var u = PROFILE_USERS[data.userKey];
                var avFrame = document.querySelector('#profile-avatar-col .profile-avatar-frame');
                if (avFrame) {
                    avFrame.innerHTML = '';
                    if (u && u.image) {
                        var img = document.createElement('img');
                        img.className = 'profile-avatar-img';
                        img.src = u.image; img.alt = u.label;
                        avFrame.appendChild(img);
                    } else {
                        avFrame.innerHTML = '<div class="profile-avatar-placeholder">👤</div>';
                    }
                    if (window.__attachPresenceDot) window.__attachPresenceDot(avFrame, data.userKey);
                }
                var nameEl = document.getElementById('profile-username');
                if (nameEl) nameEl.textContent = data.label;
                renderProfileInfo(data.profile);
                renderPosts(data.profile.posts || []);
                updateCounts();
                document.getElementById('profile-view-social').style.display = 'none';
                document.getElementById('profile-view-cat').style.display     = 'none';
                var melonV = document.getElementById('profile-view-melon');
                if (melonV) melonV.style.display = 'none';
                var mv = document.getElementById('profile-view-music');
                if (mv) mv.style.display = 'none';
                document.getElementById('profile-view-default').style.display = 'flex';
                document.getElementById('profile-sidebar-back').style.display = 'block';
                document.getElementById('profile-info-edit-btn').style.display = 'none';
                var newPostEl = document.getElementById('profile-new-post');
                if (newPostEl) newPostEl.style.display = 'none';
                updateFollowButton(!!data.isFollowing);
                var listasHdText = document.getElementById('profile-listas-heading-text');
                if (listasHdText) listasHdText.textContent = 'Listas de ' + data.label;
                var socialSec = document.getElementById('profile-social-section');
                if (socialSec) socialSec.style.display = 'none';
                var melonSec  = document.getElementById('profile-melon-section');
                if (melonSec)  melonSec.style.display  = 'none';
                var notifBtn = document.getElementById('profile-notif-btn');
                if (notifBtn) notifBtn.style.display = 'none';
            })
            .catch(function() { alert('No se pudo cargar el perfil'); });
    }

    function updateFollowButton(isFollowing) {
        var btn = document.getElementById('profile-follow-btn');
        if (!btn) return;
        if (!viewingUser) { btn.style.display = 'none'; return; }
        btn.style.display = '';
        btn.textContent = isFollowing ? '✓ Siguiendo' : '+ Seguir';
        btn.classList.toggle('following', !!isFollowing);
    }

    function isMutual(userKey) {
        return ownFollowing.indexOf(userKey) !== -1 && myFollowers.indexOf(userKey) !== -1;
    }

    /* Presencia: cada 20s pide la lista de online y refresca los puntos.
       Cacheamos el último set para que renderSocialList/renderFollowedNav
       puedan re-aplicar el estado sin esperar al siguiente fetch. */
    var lastOnlineSet = {};
    function applyPresence() {
        document.querySelectorAll('.pf-presence-dot[data-userkey]').forEach(function(dot) {
            var k = dot.getAttribute('data-userkey');
            dot.classList.toggle('online', !!lastOnlineSet[k]);
        });
    }
    /* Helper: marca un .profile-avatar-frame con su dot de presencia.
       Cualquier dot previo se elimina. userKey '' o falsy → sin dot. */
    function attachPresenceDot(frame, userKey) {
        if (!frame) return;
        var existing = frame.querySelector('.pf-presence-dot');
        if (existing) existing.remove();
        if (!userKey) {
            frame.classList.remove('has-presence-dot');
            return;
        }
        frame.classList.add('has-presence-dot');
        var dot = document.createElement('span');
        dot.className = 'pf-presence-dot';
        dot.setAttribute('data-userkey', userKey);
        frame.appendChild(dot);
        if (lastOnlineSet[userKey]) dot.classList.add('online');
    }
    window.__attachPresenceDot = attachPresenceDot;
    function refreshPresenceDots() {
        fetch('assets/profile/api.php?action=presence', { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d || !d.ok || !Array.isArray(d.online)) return;
                lastOnlineSet = {};
                d.online.forEach(function(k) { lastOnlineSet[k] = true; });
                applyPresence();
            })
            .catch(function() {});
    }
    /* Expone applyPresence para que las funciones de render puedan
       re-aplicar el estado al recrear los dots. */
    window.__applyPresence = applyPresence;
    refreshPresenceDots();
    setInterval(refreshPresenceDots, 20000);

    /* Inicializa el dot en los 3 avatares hard-coded del HTML
       (default profile view, cat view, music view). Apuntan al usuario
       propio cuando se carga, ya que no estamos viewing-other. */
    [
        '#profile-avatar-col .profile-avatar-frame',
        '#profile-catview-avatar-wrap .profile-avatar-frame',
        '#music-catview-avatar-wrap .profile-avatar-frame',
    ].forEach(function(sel) {
        var fr = document.querySelector(sel);
        if (fr) attachPresenceDot(fr, currentSessionUser);
    });

    function loadMyFollowers(cb) {
        fetch('assets/profile/api.php?action=get-followers')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d && d.ok && Array.isArray(d.followers)) myFollowers = d.followers;
                if (cb) cb();
            })
            .catch(function() { if (cb) cb(); });
    }

    /* ──── Chat ──── */
    var chatWithUser   = null;
    var chatLastSeenId = null;
    var chatPollTimer  = null;
    var unreadChats    = {};  /* { userKey: count } — mensajes sin leer por chat */
    var unreadPollTimer = null;

    function openChatWith(userKey) {
        if (!userKey || !PROFILE_USERS[userKey]) return;
        chatWithUser = userKey;
        chatLastSeenId = null;
        var u = PROFILE_USERS[userKey];
        /* Avatar del usuario en lugar del 💬. Si no hay imagen, fallback a la inicial. */
        var avEl = document.getElementById('profile-chat-title-av');
        if (avEl) {
            if (u.image) {
                avEl.innerHTML = '<img src="' + escHtml(u.image) + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">';
            } else {
                avEl.innerHTML = '<div style="width:100%;height:100%;background:var(--accent);color:var(--accent-text,#fff);font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;">' + escHtml((u.label || '?').charAt(0).toUpperCase()) + '</div>';
            }
            /* `position:relative` para anclar el dot absoluto a la esquina
               del span; `overflow:visible` porque el dot se posiciona
               fuera del rect (bottom/right negativos). */
            avEl.style.position = 'relative';
            avEl.style.overflow = 'visible';
            /* Dot de presencia del user con el que chateo. */
            var prevDot = avEl.querySelector('.pf-presence-dot');
            if (prevDot) prevDot.remove();
            var chatDot = document.createElement('span');
            chatDot.className = 'pf-presence-dot pf-presence-dot-small';
            chatDot.setAttribute('data-userkey', userKey);
            if (lastOnlineSet[userKey]) chatDot.classList.add('online');
            avEl.appendChild(chatDot);
        }
        document.getElementById('profile-chat-title-text').textContent = u.label;
        var win = document.getElementById('profile-chat-window');
        win.style.display = 'flex';
        win.style.left = Math.round((window.innerWidth  - win.offsetWidth)  / 2) + 'px';
        win.style.top  = Math.round((window.innerHeight - win.offsetHeight) / 2) + 'px';
        loadChatMessages();
        if (chatPollTimer) clearInterval(chatPollTimer);
        chatPollTimer = setInterval(loadChatMessages, 1500);
        /* Quita el contador local al abrir el chat */
        if (unreadChats[userKey]) { delete unreadChats[userKey]; renderFollowedNav(); }
        setTimeout(function() {
            var input = document.getElementById('profile-chat-input');
            if (input) input.focus();
        }, 50);
    }

    function loadUnreadChats() {
        fetch('assets/profile/api.php?action=get-unread-chats')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d || !d.ok) return;
                unreadChats = d.counts || {};
                /* No mostrar contador para el chat actualmente abierto */
                if (chatWithUser && unreadChats[chatWithUser]) delete unreadChats[chatWithUser];
                renderFollowedNav();
            }).catch(function() {});
    }

    function closeChat() {
        var win = document.getElementById('profile-chat-window');
        if (win) win.style.display = 'none';
        chatWithUser = null;
        if (chatPollTimer) { clearInterval(chatPollTimer); chatPollTimer = null; }
    }

    function loadChatMessages() {
        if (!chatWithUser) return;
        fetch('assets/profile/api.php?action=get-messages&with=' + encodeURIComponent(chatWithUser))
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d || !d.ok) return;
                renderChatMessages(d.messages || []);
            }).catch(function() {});
    }

    function renderChatMessages(messages) {
        var listEl = document.getElementById('profile-chat-messages');
        if (!listEl) return;
        var lastId = messages.length ? messages[messages.length - 1].id : null;
        if (lastId === chatLastSeenId) return; /* sin cambios */
        chatLastSeenId = lastId;
        var atBottom = (listEl.scrollHeight - listEl.scrollTop - listEl.clientHeight) < 40;
        listEl.innerHTML = '';
        messages.forEach(function(m) {
            var mine = m.from === currentSessionUser;
            var row = document.createElement('div');
            row.className = 'chat-msg' + (mine ? ' chat-msg-mine' : ' chat-msg-theirs');
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble';
            bubble.textContent = m.text;
            row.appendChild(bubble);
            var time = document.createElement('div');
            time.className = 'chat-time';
            time.textContent = relTime(m.sentAt || 0);
            row.appendChild(time);
            listEl.appendChild(row);
        });
        if (atBottom) listEl.scrollTop = listEl.scrollHeight;
    }

    function sendChatMessage() {
        if (!chatWithUser) return;
        var input = document.getElementById('profile-chat-input');
        if (!input) return;
        var text = input.value.trim();
        if (!text) return;
        input.disabled = true;
        fetch('assets/profile/api.php?action=send-message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ to: chatWithUser, text: text })
        }).then(function(r) { return r.json(); })
          .then(function(d) {
              input.disabled = false;
              if (d && d.error) { alert(d.error); return; }
              input.value = '';
              loadChatMessages();
              input.focus();
          }).catch(function() { input.disabled = false; });
    }

    (function setupChat() {
        var closeBtn = document.getElementById('profile-chat-close');
        if (closeBtn) closeBtn.addEventListener('click', closeChat);
        var sendBtn = document.getElementById('profile-chat-send');
        if (sendBtn) sendBtn.addEventListener('click', sendChatMessage);
        var input = document.getElementById('profile-chat-input');
        if (input) input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChatMessage(); }
        });
        /* Emoji picker — paleta de emotes comunes. Click en uno → append
           al input. Click fuera del panel o re-toggle del botón → cierra. */
        var EMOTES = ['😀','😂','🥲','😅','😍','😘','🤩','🤔','🙄','😎',
                      '😭','😡','🥺','😴','🤤','🤯','🤗','🫶','🫡','👀',
                      '🥳','😏','😉','🙃','😬','😱','🤣','😋','😇','🤧',
                      '👍','👎','🙌','👏','💪','🙏','🤝','✌️','🤞','👌',
                      '❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','💖',
                      '🔥','✨','💯','⭐','🎉','🎊','🎵','🎶','🎁','☕',
                      '🍕','🍔','🍿','🍻','🍰','🌹','🌸','🌈','☀️','🌙'];
        var panel  = document.getElementById('profile-chat-emoji-panel');
        var btn    = document.getElementById('profile-chat-emoji-btn');
        if (panel && btn && input) {
            /* Renderiza el grid de emotes una vez. */
            panel.innerHTML = EMOTES.map(function(e){
                return '<button type="button" class="pf-emote" style="background:transparent;border:0;font-size:18px;padding:2px 4px;cursor:pointer;font-family:inherit;" data-em="' + e + '">' + e + '</button>';
            }).join('');
            panel.style.display = 'none';
            btn.addEventListener('click', function(e){
                e.stopPropagation();
                panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            });
            panel.addEventListener('click', function(e){
                var b = e.target.closest('button[data-em]');
                if (!b) return;
                var em = b.getAttribute('data-em');
                /* Inserta en la posición del cursor. */
                var start = input.selectionStart || input.value.length;
                var end   = input.selectionEnd   || input.value.length;
                input.value = input.value.slice(0, start) + em + input.value.slice(end);
                input.focus();
                input.setSelectionRange(start + em.length, start + em.length);
            });
            /* Click fuera del panel cierra. */
            document.addEventListener('click', function(e){
                if (panel.style.display === 'none') return;
                if (!panel.contains(e.target) && e.target !== btn) panel.style.display = 'none';
            });
        }
    })();

    function addItemToOwnProfile(cat, item) {
        if (!viewingUser || !ownLists) return;
        var copy = JSON.parse(JSON.stringify(item));
        delete copy.collaborators;
        delete copy.sharedFrom;
        delete copy.review;
        if (cat === 'music') {
            copy.id = 'music_' + Date.now();
            copy.featured = false;
            delete copy.status;
        } else {
            copy.id = 'item_' + Date.now();
            copy.status = 'pending';
        }
        var titleLower = (copy.title || '').toLowerCase().trim();
        if (!titleLower) return;
        var dup = (ownLists[cat] || []).some(function(it) {
            return (it.title || '').toLowerCase().trim() === titleLower;
        });
        if (dup) {
            if (window.notifSystem) {
                window.notifSystem.show({
                    id:      'dup_' + Date.now(),
                    type:    'info',
                    title:   'Ya en tu perfil',
                    message: '"' + copy.title + '" ya está en tu lista.'
                });
            }
            return;
        }
        if (!ownLists[cat]) ownLists[cat] = [];
        ownLists[cat].push(copy);
        var newIdx = ownLists[cat].length - 1;
        fetch('assets/profile/api.php?action=save-lists', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: cat, items: ownLists[cat] })
        }).then(function(r) { return r.json(); })
          .then(function(d) {
              if (d && d.error) { alert(d.error); return; }
              if (window.notifSystem) {
                  window.notifSystem.show({
                      id:      'added_' + Date.now(),
                      type:    'info',
                      title:   cat === 'music' ? 'Música añadida' : 'Añadido a tu perfil',
                      message: cat === 'music'
                          ? '"' + copy.title + '" añadido a tu lista.'
                          : '"' + copy.title + '" añadido como pendiente.'
                  });
              }
              if (cat === 'music') {
                  showAddedMusicReviewPrompt(newIdx, copy);
              }
          }).catch(function() { alert('Error al guardar'); });
    }

    function showAddedMusicReviewPrompt(newIdx, item) {
        var prompt = document.getElementById('profile-review-prompt');
        if (!prompt) return;
        document.getElementById('profile-review-prompt-msg').textContent =
            '¿Añadir una reseña para "' + item.title + '"?';
        prompt.style.display = 'block';
        prompt.style.left = Math.round((window.innerWidth  - prompt.offsetWidth)  / 2) + 'px';
        prompt.style.top  = Math.round((window.innerHeight - prompt.offsetHeight) / 2) + 'px';
        var yesBtn = document.getElementById('profile-review-prompt-yes');
        var noBtn  = document.getElementById('profile-review-prompt-no');
        var newYes = yesBtn.cloneNode(true); yesBtn.parentNode.replaceChild(newYes, yesBtn);
        var newNo  = noBtn.cloneNode(true);  noBtn.parentNode.replaceChild(newNo, noBtn);
        newYes.addEventListener('click', function() {
            prompt.style.display = 'none';
            if (viewingUser) exitViewingUser();
            showMusicView();
            showMusicReviewWindow(newIdx);
        });
        newNo.addEventListener('click', function() {
            prompt.style.display = 'none';
        });
    }

    function exitViewingUser() {
        viewingUser = null;
        if (ownLists) { lists = ownLists; ownLists = null; }
        /* Restaura cabecera al usuario propio */
        var avFrame = document.querySelector('#profile-avatar-col .profile-avatar-frame');
        if (avFrame) {
            avFrame.innerHTML = <?php
                $ownAv = isset($profileImg) ? $profileImg : '';
                if ($ownAv) {
                    echo json_encode('<img src="' . htmlspecialchars($ownAv) . '" alt="" class="profile-avatar-img">');
                } else {
                    echo json_encode('<div class="profile-avatar-placeholder">👤</div>');
                }
            ?>;
            if (window.__attachPresenceDot) window.__attachPresenceDot(avFrame, currentSessionUser);
        }
        var nameEl = document.getElementById('profile-username');
        if (nameEl) nameEl.textContent = <?php echo json_encode($desktopLabel); ?>;
        var newPostEl = document.getElementById('profile-new-post');
        if (newPostEl) newPostEl.style.display = '';
        var followBtn = document.getElementById('profile-follow-btn');
        if (followBtn) followBtn.style.display = 'none';
        var listasHdText = document.getElementById('profile-listas-heading-text');
        if (listasHdText) listasHdText.textContent = 'Mis Listas';
        var socialSec = document.getElementById('profile-social-section');
        if (socialSec) socialSec.style.display = '';
        var melonSec  = document.getElementById('profile-melon-section');
        if (melonSec)  melonSec.style.display  = '';
        var notifBtn = document.getElementById('profile-notif-btn');
        if (notifBtn) notifBtn.style.display = '';
        updateCounts();
        loadProfile();
    }

    /* ──── Music view ──── */
    function showMusicView() {
        document.dispatchEvent(new Event('profile-edit-cancel'));
        currentCat = 'music';
        document.getElementById('profile-view-default').style.display = 'none';
        document.getElementById('profile-view-cat').style.display = 'none';
        document.getElementById('profile-view-social').style.display = 'none';
        var melonV = document.getElementById('profile-view-melon');
        if (melonV) melonV.style.display = 'none';
        document.getElementById('profile-sidebar-back').style.display = 'block';
        document.getElementById('profile-info-edit-btn').style.display = 'none';
        var mv = document.getElementById('profile-view-music');
        if (mv) mv.style.display = 'flex';
        var musicAddBtn = document.getElementById('music-catview-add-btn');
        if (musicAddBtn) musicAddBtn.style.display = viewingUser ? 'none' : '';
        var musicTitleEl = document.getElementById('music-catview-title');
        applyTopbarUser('music-catview-avatar-wrap', musicTitleEl, '🎵', 'Música');
        /* applyTopbarUser ha hecho textContent='🎵 Música' — sobreescribimos
           con innerHTML para meter la imagen PNG en lugar del emoji. */
        if (musicTitleEl) musicTitleEl.innerHTML = '<img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Música';
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
                        if (viewingUser) {
                            showCtxMenu(e.clientX, e.clientY, [
                                { label: '▶ Reproducir',         action: function() { playMusicItem(it); } },
                                { label: '+ Añadir a mi perfil', action: function() { addItemToOwnProfile('music', it); } }
                            ]);
                            return;
                        }
                        showCtxMenu(e.clientX, e.clientY, [
                            { label: '▶ Reproducir', action: function() { playMusicItem(it); } },
                            { label: '★ Quitar de destacados', action: function() {
                                var idx = lists.music.findIndex(function(x){ return x.id === it.id; });
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
        /* WRAPPED tracking: si es un ÁLBUM, lo registramos como evento
           de álbum reproducido. Fire-and-forget; los tracks
           individuales también se loguean via el reproductor más
           abajo, esto solo afecta al ranking de álbumes en Wrapped. */
        if (item && item.type === 'album') {
            try {
                fetch('assets/music/wrapped-api.php?action=log-album', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        albumTitle:     item.title          || '',
                        artist:         item.artist         || '',
                        actionType:     'play',
                        ytPlaylistId:   item.ytPlaylistId   || '',
                        spotifyAlbumId: item.spotifyAlbumId || '',
                        coverUrl:       item.image          || '',
                    }),
                }).catch(function(){});
            } catch (_) {}
        }
        fetch('assets/profile/api.php?action=play-music-item', {
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
            /* idx no se captura: lists.music puede haber sido reemplazado
               por la respuesta de saveCategory. Resolvemos por id en cada click. */
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

            (function(it) {
                /* Resolver el índice por id en cada click — `lists.music`
                   puede haber sido reemplazado por la respuesta canónica de
                   saveCategory tras un save anterior. */
                function curIdx(){
                    return lists.music.findIndex(function(x){ return x.id === it.id; });
                }
                row.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    if (viewingUser) {
                        showCtxMenu(e.clientX, e.clientY, [
                            { label: '▶ Reproducir',          action: function() { playMusicItem(it); } },
                            { label: '+ Añadir a mi perfil',  action: function() { addItemToOwnProfile('music', it); } }
                        ]);
                        return;
                    }
                    var isCollab = !!it.sharedFrom;
                    var menuItems = [];
                    menuItems.push({ label: '▶ Reproducir', action: function() { playMusicItem(it); } });
                    var featuredCount = lists.music.filter(function(x) { return x.featured; }).length;
                    if (it.featured) {
                        menuItems.push({ label: '★ Quitar de destacados', action: function() {
                            var i = curIdx(); if (i === -1) return;
                            lists.music[i].featured = false; saveCategory('music'); renderMusicDestacados(); renderMusicView(currentMusicTab);
                        }});
                    } else {
                        menuItems.push({ label: '★ Destacar', disabled: featuredCount >= 3, action: function() {
                            var i = curIdx(); if (i === -1) return;
                            lists.music[i].featured = true; saveCategory('music'); renderMusicDestacados(); renderMusicView(currentMusicTab);
                        }});
                    }
                    var reviewLabel = (it.review && it.review.stars) ? '✏ Editar reseña' : '✏ Añadir reseña';
                    menuItems.push({ label: reviewLabel, action: function() {
                        var i = curIdx(); if (i !== -1) showMusicReviewWindow(i);
                    }});
                    menuItems.push({ label: '👥 Colaboradores', action: function() { showCollabDialog('music', it); }});
                    if (isCollab) {
                        menuItems.push({ label: '🚪 Abandonar actividad', action: function() {
                            confirmFn('¿Abandonar "' + it.title + '"?', 'Abandonar', function() {
                                fetch('assets/profile/api.php?action=leave-collab', {
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
                                var i = curIdx(); if (i === -1) return;
                                lists.music.splice(i, 1); saveCategory('music'); updateCounts(); renderMusicView(currentMusicTab); renderMusicDestacados();
                            });
                        }});
                    }
                    showCtxMenu(e.clientX, e.clientY, menuItems);
                });
            })(item);

            listEl.appendChild(row);
        });
    }

    function showMusicReviewWindow(itemIdx) {
        var win = document.getElementById('profile-review-window');
        var starsEl = document.getElementById('profile-review-stars');
        var commentEl = document.getElementById('profile-review-comment');
        var item = lists.music[itemIdx];
        if (!item) return;
        /* id estable para re-resolver el ítem en submit/delete, por si
           lists.music fue reemplazado por un saveCategory intermedio. */
        var itemId = item.id;
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
            var mi = lists.music.findIndex(function(x){ return x.id === itemId; });
            if (mi === -1) { closeWin(); return; }
            lists.music[mi].review = { stars: sel, comment: commentEl.value.trim(), reviewedAt: Math.floor(Date.now() / 1000) };
            saveCategory('music');
            notifyReviewToFollowers('music', lists.music[mi].title, lists.music[mi].type);
            renderMusicView(currentMusicTab); renderMusicDestacados(); closeWin();
        });
        var deleteBtn = document.getElementById('profile-review-window-delete');
        var newDelete = deleteBtn.cloneNode(true); deleteBtn.parentNode.replaceChild(newDelete, deleteBtn);
        newDelete.style.display = (item.review && item.review.stars) ? '' : 'none';
        newDelete.addEventListener('click', function() {
            var mi = lists.music.findIndex(function(x){ return x.id === itemId; });
            if (mi !== -1) delete lists.music[mi].review;
            saveCategory('music');
            renderMusicView(currentMusicTab); renderMusicDestacados(); closeWin();
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
                entry.review = { stars: reviewRating, comment: reviewCommentEl.value.trim(), reviewedAt: Math.floor(Date.now() / 1000) };
            }
            lists.music.push(entry);
            saveCategory('music');
            if (withReview && reviewRating > 0) notifyReviewToFollowers('music', entry.title, entry.type);
            /* WRAPPED tracking: si el item añadido es un ÁLBUM, lo
               registramos como evento 'import' (lo importó a su
               colección/playlist). Suma puntos en el ranking del
               wrapped, distinto de 'play' que viene de Reproducir. */
            if (currentType === 'album') {
                try {
                    fetch('assets/music/wrapped-api.php?action=log-album', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            albumTitle:     entry.title,
                            artist:         entry.artist,
                            actionType:     'import',
                            ytPlaylistId:   entry.ytPlaylistId   || '',
                            spotifyAlbumId: entry.spotifyAlbumId || '',
                            coverUrl:       entry.image          || '',
                        }),
                    }).catch(function(){});
                } catch (_) {}
            }
            updateCounts();
            renderMusicView(currentMusicTab);
            renderMusicDestacados();
            closeDialog();
        }
        function goToStep2(type) {
            currentType = type; fetchedMeta = null;
            urlInput.value = ''; artistInput.value = '';
            preview.textContent = ''; preview.style.color = ''; errEl.textContent = '';
            /* Song usa PNG musicaIcon; album mantiene su emoji 💿. */
            if (type === 'song') {
                titleEl.innerHTML = '<img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;margin-right:3px;">Añadir canción';
            } else {
                titleEl.textContent = '💿 Añadir álbum';
            }
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
                fetch('assets/profile/api.php?action=resolve-music-item&url=' + encodeURIComponent(raw) + '&itemType=' + currentType)
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

    /* ──── Profile info INLINE edit mode ──── */
    (function() {
        var editBtn   = document.getElementById('profile-info-edit-btn');
        var saveBtn   = document.getElementById('profile-info-edit-save-btn');
        var cancelBtn = document.getElementById('profile-info-edit-cancel-btn');
        var photoIn   = document.getElementById('profile-photo-input');
        var avatarCol = document.getElementById('profile-avatar-col');

        /* Diálogo flotante para añadir una conexión social */
        var addDlg    = document.getElementById('profile-social-add-dialog');
        var addClose  = document.getElementById('profile-social-add-close');
        var addCancel = document.getElementById('profile-social-add-cancel');
        var addConfirm= document.getElementById('profile-social-add-confirm');
        var addPlat   = document.getElementById('profile-social-add-platform');
        var addVal    = document.getElementById('profile-social-add-value');

        var FIELDS = ['bio', 'pronouns', 'age', 'country'];
        var SOCIALS = ['steam', 'discord', 'twitter', 'instagram'];
        var SOCIAL_ICONS = { steam: '🎮', discord: '💬', twitter: '🐦', instagram: '📷' };
        var SOCIAL_LABELS = { steam: 'Steam', discord: 'Discord', twitter: 'Twitter', instagram: 'Instagram' };
        var SOCIAL_PLACEHOLDERS = {
            steam:     'https://steamcommunity.com/id/...',
            discord:   'usuario o usuario#1234',
            twitter:   '@usuario',
            instagram: '@usuario'
        };

        /* Estado de edición */
        var editing = false;
        var draft   = null;     /* copia mutable de los datos en edición */
        var newPhotoFile = null;/* archivo de foto pendiente de subir (null = sin cambios) */

        function buildMetaInputs(d) {
            var metaEl = document.getElementById('profile-info-meta');
            if (!metaEl) return;
            metaEl.innerHTML = '';
            metaEl.style.flexDirection = 'column';
            metaEl.style.gap = '3px';
            function row(key, ph, type, maxlen) {
                var inp = document.createElement('input');
                inp.type = type || 'text';
                if (maxlen) inp.maxLength = maxlen;
                inp.placeholder = ph;
                inp.value = d[key] || '';
                inp.style.cssText = 'width:100%;box-sizing:border-box;font-size:10px;';
                inp.dataset.field = key;
                inp.addEventListener('input', function() { draft[key] = this.value; });
                metaEl.appendChild(inp);
                return inp;
            }
            row('pronouns', 'pronombres (él/ella/elle...)', 'text',   30);
            row('age',      'edad',                          'number', 3);
            row('country',  '📍 país',                        'text',   50);
        }

        function buildBioTextarea(d) {
            var bioEl = document.getElementById('profile-info-bio');
            if (!bioEl) return;
            bioEl.innerHTML = '';
            bioEl.style.display = '';
            var ta = document.createElement('textarea');
            ta.maxLength = 200;
            ta.placeholder = 'Cuéntanos algo sobre ti...';
            ta.rows = 3;
            ta.style.cssText = 'width:100%;height:100%;box-sizing:border-box;resize:none;font-size:11px;font-style:italic;background:transparent;';
            ta.value = d.bio || '';
            ta.addEventListener('input', function() { draft.bio = this.value; });
            bioEl.appendChild(ta);
        }

        var MAX_SOCIALS = 4;

        function countSocials(d) {
            var n = 0;
            SOCIALS.forEach(function(k) { if (d[k]) n++; });
            return n;
        }

        function buildSocialEditable(d) {
            var linksEl = document.getElementById('profile-info-links');
            if (!linksEl) return;
            linksEl.innerHTML = '';
            SOCIALS.forEach(function(k) {
                if (!d[k]) return;
                var wrap = document.createElement('div');
                wrap.style.cssText = 'position:relative;display:inline-flex;';
                var span = document.createElement('span');
                span.className = 'pinfo-social';
                span.textContent = SOCIAL_ICONS[k] || '🔗';
                span.title = (SOCIAL_LABELS[k] || k) + ': ' + d[k];
                wrap.appendChild(span);
                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'profile-social-remove-btn';
                del.title = 'Quitar';
                del.innerHTML = '×';
                del.addEventListener('click', function(e) {
                    e.preventDefault(); e.stopPropagation();
                    delete draft[k];
                    buildSocialEditable(draft);
                });
                wrap.appendChild(del);
                linksEl.appendChild(wrap);
            });
            /* Botón + para añadir conexión — solo si quedan huecos (max 4) */
            if (countSocials(d) < MAX_SOCIALS) {
                var addBtn = document.createElement('button');
                addBtn.type = 'button';
                addBtn.className = 'pinfo-social-add';
                addBtn.title = 'Añadir conexión';
                addBtn.textContent = '+';
                addBtn.addEventListener('click', openAddSocial);
                linksEl.appendChild(addBtn);
            }
        }

        function openAddSocial() {
            /* Por defecto, primera plataforma disponible (no ocupada) */
            var first = SOCIALS.find(function(s) { return !draft[s]; }) || 'steam';
            addPlat.value = first;
            addVal.value = '';
            addVal.placeholder = SOCIAL_PLACEHOLDERS[first] || '';
            addDlg.style.display = 'block';
            addDlg.style.left = Math.round((window.innerWidth  - addDlg.offsetWidth)  / 2) + 'px';
            addDlg.style.top  = Math.round((window.innerHeight - addDlg.offsetHeight) / 2) + 'px';
            setTimeout(function() { addVal.focus(); }, 50);
        }
        function closeAddSocial() { addDlg.style.display = 'none'; }
        addPlat.addEventListener('change', function() {
            addVal.placeholder = SOCIAL_PLACEHOLDERS[addPlat.value] || '';
        });
        addClose.addEventListener('click', closeAddSocial);
        addCancel.addEventListener('click', closeAddSocial);
        addConfirm.addEventListener('click', function() {
            var plat = addPlat.value;
            var val  = (addVal.value || '').trim();
            if (!plat || !val) return;
            draft[plat] = val;
            buildSocialEditable(draft);
            closeAddSocial();
        });
        addVal.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addConfirm.click(); }
        });

        function enterEditMode() {
            fetch('assets/profile/api.php?action=get-profile')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    draft = Object.assign({}, data || {});
                    newPhotoFile = null;
                    editing = true;
                    editBtn.style.display    = 'none';
                    saveBtn.style.display    = '';
                    cancelBtn.style.display  = '';
                    avatarCol.classList.add('profile-edit-avatar');
                    buildMetaInputs(draft);
                    buildBioTextarea(draft);
                    buildSocialEditable(draft);
                })
                .catch(function() {});
        }

        function exitEditMode(saved) {
            editing = false;
            editBtn.style.display    = '';
            saveBtn.style.display    = 'none';
            cancelBtn.style.display  = 'none';
            avatarCol.classList.remove('profile-edit-avatar');
            /* Restaurar layout del meta */
            var metaEl = document.getElementById('profile-info-meta');
            if (metaEl) { metaEl.style.flexDirection = ''; metaEl.style.gap = ''; }
            /* Re-renderizar con datos finales (draft si guardado, recarga si cancelado) */
            if (saved) {
                renderProfileInfo(draft);
            } else {
                loadProfile();
            }
        }

        function saveAll() {
            saveBtn.classList.add('btn-busy');
            /* Compone payload con todos los campos editables */
            var payload = {};
            FIELDS.forEach(function(f) { payload[f] = (draft[f] || '').toString().trim(); });
            SOCIALS.forEach(function(f) { payload[f] = (draft[f] || '').toString().trim(); });

            var infoPromise = fetch('assets/profile/api.php?action=save-info', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); });

            var photoPromise = Promise.resolve(null);
            if (newPhotoFile) {
                var fd = new FormData();
                fd.append('photo', newPhotoFile);
                photoPromise = fetch('assets/img/save-profile-photo.php', {
                    method: 'POST', body: fd
                }).then(function(r) { return r.json(); });
            }

            Promise.all([infoPromise, photoPromise]).then(function(res) {
                saveBtn.classList.remove('btn-busy');
                var infoRes  = res[0] || {};
                var photoRes = res[1];
                if (infoRes.error) { alert(infoRes.error); return; }
                if (photoRes && photoRes.error) { alert(photoRes.error); }
                /* Refresca avatares del escritorio */
                if (photoRes && photoRes.ok && window.parent && window.parent !== window) {
                    window.parent.postMessage({ type: 'profile-photo-changed', photo: photoRes.photo }, '*');
                }
                /* Actualiza los <img> del propio iframe de perfil */
                if (photoRes && photoRes.ok) {
                    var nm = photoRes.photo.match(/\/([^/]+)\.[^/]+$/);
                    var bn = nm ? nm[1].toLowerCase() : '';
                    document.querySelectorAll('img').forEach(function(img) {
                        var src = img.getAttribute('src') || '';
                        var sm = src.match(/\/([^/?]+)\.[a-zA-Z0-9]+(?:\?|$)/);
                        if (sm && sm[1].toLowerCase() === bn) {
                            img.src = photoRes.photo + '?t=' + Date.now();
                        }
                    });
                }
                exitEditMode(true);
            }).catch(function() {
                saveBtn.classList.remove('btn-busy');
                alert('Error de red al guardar.');
            });
        }

        /* Click en el avatar (en modo edición) → abre file picker */
        avatarCol.addEventListener('click', function(e) {
            if (!editing) return;
            /* Solo si clickaste en el marco o la imagen, no en el username/botón */
            var t = e.target;
            if (t === avatarCol) photoIn.click();
            else if (t.classList && (t.classList.contains('profile-avatar-frame') || t.classList.contains('profile-avatar-img') || t.classList.contains('profile-avatar-placeholder'))) {
                photoIn.click();
            } else if (t.closest && t.closest('.profile-avatar-frame')) {
                photoIn.click();
            }
        });
        photoIn.addEventListener('change', function() {
            if (!editing || !this.files.length) return;
            openCropDialog(this.files[0]);
            this.value = ''; /* permite reabrir con el mismo archivo */
        });

        /* ──── Crop dialog ──── */
        var cropDlg     = document.getElementById('profile-photo-crop-dialog');
        var cropStage   = document.getElementById('profile-photo-crop-stage');
        var cropImg     = document.getElementById('profile-photo-crop-img');
        var cropRegion  = document.getElementById('profile-photo-crop-region');
        var cropClose   = document.getElementById('profile-photo-crop-close');
        var cropCancel  = document.getElementById('profile-photo-crop-cancel');
        var cropConfirm = document.getElementById('profile-photo-crop-confirm');

        /* Estado del cropper */
        var cropSrcFile = null;     /* File original elegido */
        var cropImgNat  = { w: 0, h: 0 };    /* tamaño natural de la imagen */
        var cropImgDisp = { w: 0, h: 0, x: 0, y: 0 };  /* tamaño/posición renderizada en el stage */
        var cropRect    = { x: 0, y: 0, size: 0 };     /* en coords del stage */

        function openCropDialog(file) {
            cropSrcFile = file;
            var url = URL.createObjectURL(file);
            cropImg.onload = function() {
                cropImgNat.w = cropImg.naturalWidth;
                cropImgNat.h = cropImg.naturalHeight;
                layoutImage();
                /* Crop inicial: cuadrado centrado, 80% del lado menor del área visible */
                var minSide = Math.min(cropImgDisp.w, cropImgDisp.h);
                cropRect.size = Math.floor(minSide * 0.8);
                cropRect.x = cropImgDisp.x + Math.floor((cropImgDisp.w - cropRect.size) / 2);
                cropRect.y = cropImgDisp.y + Math.floor((cropImgDisp.h - cropRect.size) / 2);
                drawRegion();
            };
            cropImg.src = url;
            cropDlg.style.display = 'block';
            cropDlg.style.left = Math.round((window.innerWidth  - cropDlg.offsetWidth)  / 2) + 'px';
            cropDlg.style.top  = Math.round((window.innerHeight - cropDlg.offsetHeight) / 2) + 'px';
        }

        function closeCropDialog() {
            cropDlg.style.display = 'none';
            if (cropImg.src) URL.revokeObjectURL(cropImg.src);
            cropImg.src = '';
            cropSrcFile = null;
        }

        /* Ajusta la imagen al stage manteniendo aspecto (contain) */
        function layoutImage() {
            var sw = cropStage.clientWidth, sh = cropStage.clientHeight;
            var ratio = cropImgNat.w / cropImgNat.h;
            var w, h;
            if (sw / sh > ratio) { h = sh; w = h * ratio; }
            else                 { w = sw; h = w / ratio; }
            cropImgDisp.w = Math.floor(w);
            cropImgDisp.h = Math.floor(h);
            cropImgDisp.x = Math.floor((sw - w) / 2);
            cropImgDisp.y = Math.floor((sh - h) / 2);
            cropImg.style.left   = cropImgDisp.x + 'px';
            cropImg.style.top    = cropImgDisp.y + 'px';
            cropImg.style.width  = cropImgDisp.w + 'px';
            cropImg.style.height = cropImgDisp.h + 'px';
        }

        function drawRegion() {
            cropRegion.style.left   = cropRect.x + 'px';
            cropRegion.style.top    = cropRect.y + 'px';
            cropRegion.style.width  = cropRect.size + 'px';
            cropRegion.style.height = cropRect.size + 'px';
        }

        /* Mantiene cropRect dentro de la imagen visible */
        function clampRect() {
            var maxSize = Math.min(cropImgDisp.w, cropImgDisp.h);
            if (cropRect.size > maxSize) cropRect.size = maxSize;
            if (cropRect.size < 32)      cropRect.size = 32;
            if (cropRect.x < cropImgDisp.x) cropRect.x = cropImgDisp.x;
            if (cropRect.y < cropImgDisp.y) cropRect.y = cropImgDisp.y;
            var maxX = cropImgDisp.x + cropImgDisp.w - cropRect.size;
            var maxY = cropImgDisp.y + cropImgDisp.h - cropRect.size;
            if (cropRect.x > maxX) cropRect.x = maxX;
            if (cropRect.y > maxY) cropRect.y = maxY;
        }

        /* Drag del recuadro completo */
        cropRegion.addEventListener('pointerdown', function(e) {
            if (e.target !== cropRegion) return; /* ignora si pinchas en un handle */
            e.preventDefault();
            cropRegion.setPointerCapture(e.pointerId);
            var sx = e.clientX, sy = e.clientY;
            var origX = cropRect.x, origY = cropRect.y;
            function onMove(ev) {
                cropRect.x = origX + (ev.clientX - sx);
                cropRect.y = origY + (ev.clientY - sy);
                clampRect();
                drawRegion();
            }
            function onUp(ev) {
                cropRegion.removeEventListener('pointermove', onMove);
                cropRegion.removeEventListener('pointerup', onUp);
                cropRegion.releasePointerCapture(ev.pointerId);
            }
            cropRegion.addEventListener('pointermove', onMove);
            cropRegion.addEventListener('pointerup', onUp);
        });

        /* Resize desde cualquier esquina (1:1) */
        cropRegion.querySelectorAll('.photo-crop-handle').forEach(function(h) {
            h.addEventListener('pointerdown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                h.setPointerCapture(e.pointerId);
                var corner = h.dataset.corner;
                var sx = e.clientX, sy = e.clientY;
                var orig = { x: cropRect.x, y: cropRect.y, size: cropRect.size };
                function onMove(ev) {
                    var dx = ev.clientX - sx;
                    var dy = ev.clientY - sy;
                    /* Convierte el delta en cambio de tamaño según esquina */
                    var sizeDelta;
                    if (corner === 'se')      sizeDelta =  Math.max(dx, dy);
                    else if (corner === 'nw') sizeDelta =  Math.max(-dx, -dy);
                    else if (corner === 'ne') sizeDelta =  Math.max(dx, -dy);
                    else                      sizeDelta =  Math.max(-dx, dy); /* sw */
                    var newSize = orig.size + sizeDelta;
                    /* Aplica límites antes de mover el origen */
                    var maxSize = Math.min(cropImgDisp.w, cropImgDisp.h);
                    if (newSize > maxSize) newSize = maxSize;
                    if (newSize < 32)      newSize = 32;
                    var newX = orig.x, newY = orig.y;
                    if (corner === 'nw' || corner === 'sw') newX = orig.x + (orig.size - newSize);
                    if (corner === 'nw' || corner === 'ne') newY = orig.y + (orig.size - newSize);
                    cropRect.x = newX; cropRect.y = newY; cropRect.size = newSize;
                    clampRect();
                    drawRegion();
                }
                function onUp(ev) {
                    h.removeEventListener('pointermove', onMove);
                    h.removeEventListener('pointerup', onUp);
                    h.releasePointerCapture(ev.pointerId);
                }
                h.addEventListener('pointermove', onMove);
                h.addEventListener('pointerup', onUp);
            });
        });

        function applyCrop() {
            if (!cropSrcFile) return;
            /* Pasar de coords del stage a coords originales de la imagen */
            var scale = cropImgNat.w / cropImgDisp.w; /* mismo en x e y por contain */
            var srcX = Math.max(0, Math.round((cropRect.x - cropImgDisp.x) * scale));
            var srcY = Math.max(0, Math.round((cropRect.y - cropImgDisp.y) * scale));
            var srcS = Math.round(cropRect.size * scale);
            /* Salida cuadrada — tope 1024 para no generar archivos enormes */
            var outSide = Math.min(1024, srcS);
            var canvas = document.createElement('canvas');
            canvas.width  = outSide;
            canvas.height = outSide;
            var ctx = canvas.getContext('2d');
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(cropImg, srcX, srcY, srcS, srcS, 0, 0, outSide, outSide);
            /* Conservamos el tipo MIME original si es png/webp; si no, jpeg */
            var ext = (cropSrcFile.name.split('.').pop() || 'jpg').toLowerCase();
            var mime = (ext === 'png') ? 'image/png' : (ext === 'webp' ? 'image/webp' : 'image/jpeg');
            canvas.toBlob(function(blob) {
                if (!blob) return;
                /* Convertir Blob en File para mantener el nombre con extensión */
                var fileName = cropSrcFile.name.replace(/\.[^.]+$/, '') + '-crop.' + (mime === 'image/png' ? 'png' : (mime === 'image/webp' ? 'webp' : 'jpg'));
                try { newPhotoFile = new File([blob], fileName, { type: mime }); }
                catch (err) { newPhotoFile = blob; newPhotoFile.name = fileName; }
                /* Preview local en el avatar */
                var previewUrl = URL.createObjectURL(blob);
                avatarCol.querySelectorAll('.profile-avatar-img').forEach(function(img) { img.src = previewUrl; });
                var ph = avatarCol.querySelector('.profile-avatar-placeholder');
                if (ph) {
                    var imgNew = document.createElement('img');
                    imgNew.className = 'profile-avatar-img';
                    imgNew.src = previewUrl;
                    ph.replaceWith(imgNew);
                }
                closeCropDialog();
            }, mime, 0.92);
        }

        cropClose.addEventListener('click', closeCropDialog);
        cropCancel.addEventListener('click', closeCropDialog);
        cropConfirm.addEventListener('click', applyCrop);

        if (editBtn)   editBtn.addEventListener('click', enterEditMode);
        if (cancelBtn) cancelBtn.addEventListener('click', function() { exitEditMode(false); });
        if (saveBtn)   saveBtn.addEventListener('click', saveAll);

        /* Salir de edición cuando alguien externo lo pide (cerrar perfil,
           cambiar de sección, ver perfil de otro usuario). */
        document.addEventListener('profile-edit-cancel', function() {
            if (editing) exitEditMode(false);
        });
    })();

    /* ──── Nav sidebar items ──── */
    profileWin.querySelectorAll('.profile-nav-item').forEach(function(navItem) {
        navItem.addEventListener('click', function() {
            var cat   = navItem.dataset.cat;
            var melon = navItem.dataset.melon;
            function navigate() {
                if (melon)              showMelonView(melon);
                else if (cat === 'social') showSocialView();
                else if (cat === 'music')  showMusicView();
                else if (cat)              showCatView(cat);
            }
            if (!loaded) {
                loaded = true;
                loadLists(function() { updateCounts(); navigate(); });
            } else {
                navigate();
            }
        });
    });

    /* ──── Profile icon ──── */
    function openProfileWindow() {
        if (taskbarManager.isRegistered('profile-window')) {
            taskbarManager.restore('profile-window');
        } else {
            profileWin.style.height = Math.max(380, window.innerHeight - 80) + 'px';
            taskbarManager.register('profile-window', 'Perfil', '👤', 'flex');
            if (!loaded) {
                loaded = true;
                loadLists(updateCounts);
                loadProfile();
                loadProfileNotifs();
            }
            startItemNotifStream();
        }
    }
    document.getElementById('profile-icon').addEventListener('dblclick', openProfileWindow);

    /* Abrir el perfil de otro usuario desde otra app (ej. biblioteca de temas).
       Abre la ventana de perfil y navega al perfil indicado. */
    window.openProfileAtUser = function(userKey) {
        if (!userKey) return;
        openProfileWindow();
        if (window.windowZ) windowZ.bringToFront('profile-window');
        /* pequeño retraso para asegurar que el perfil propio cargó primero */
        setTimeout(function() { viewOtherUser(userKey); }, loaded ? 60 : 300);
    };

    /* Cargar notificaciones de perfil + polling cada 30s */
    loadProfileNotifs();
    setInterval(loadProfileNotifs, 30000);

    /* Cargar contador de mensajes sin leer + polling cada 4s */
    loadUnreadChats();
    unreadPollTimer = setInterval(loadUnreadChats, 4000);

    document.getElementById('profile-close').addEventListener('click', function() {
        document.dispatchEvent(new Event('profile-edit-cancel'));
        taskbarManager.unregister('profile-window');
    });

    // Arrancar stream aunque el perfil no se abra (para recibir notificaciones en segundo plano)
    startItemNotifStream();

    /* Escuchar postMessage de iframes (galería) cuando publican un post.
       Solo refrescamos si estoy viendo MI propio perfil (no interrumpe la
       vista si estaba ojeando el de otro usuario). */
    window.addEventListener('message', function(e) {
        if (!e.data || e.data.type !== 'profile-post-added') return;
        if (viewingUser) return;
        loadProfile();
    });
})();

/* ─── Lightbox de imágenes de posts ───────────────────────
   Delegación de click sobre cualquier <img> dentro de un
   .profile-post-img → abrir overlay con la imagen a tamaño
   completo. Cerrar con click fuera, × o Esc. */
(function() {
    var lb    = document.getElementById('profile-post-lightbox');
    var lbImg = document.getElementById('profile-post-lightbox-img');
    var lbX   = document.getElementById('profile-post-lightbox-close');
    if (!lb) return;
    function open(src) {
        lbImg.src = src;
        lb.classList.add('is-open');
        lb.setAttribute('aria-hidden', 'false');
    }
    function close() {
        lb.classList.remove('is-open');
        lb.setAttribute('aria-hidden', 'true');
        lbImg.src = '';
    }
    /* Click delegado sobre cualquier preview de post */
    document.addEventListener('click', function(e) {
        var t = e.target;
        if (t && t.tagName === 'IMG' && t.closest && t.closest('.profile-post-img')) {
            open(t.src);
        }
    });
    /* Click en el overlay (no en la imagen) → cerrar */
    lb.addEventListener('click', function(e) {
        if (e.target === lb) close();
    });
    lbX.addEventListener('click', close);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lb.classList.contains('is-open')) close();
    });

    /* ──── Auto-momento al completar ──── */
    window.crearMomentoDesdeItem = function(cat, item) {
    console.log('item.image:', item.image);
    console.log('item completo:', JSON.stringify(item));
    var parejaId = window.DesktopParejaId || 0;
    var catEmojis = { movies: '🎬', series: '📺', books: '📚', games: '🎮', music: '🎵' };
    var catVerbs  = { movies: 'Vista', series: 'Vista', books: 'Leída', games: 'Jugado', music: 'Escuchado' };
    var emocion   = catEmojis[cat] || '😊';
    var titulo    = (catVerbs[cat] || 'Completado') + ': ' + item.title;
    var hoy       = new Date();
    var fecha     = hoy.getFullYear() + '-'
        + String(hoy.getMonth() + 1).padStart(2, '0') + '-'
        + String(hoy.getDate()).padStart(2, '0');
    var desc = '';
    if (item.collaborators && item.collaborators.length) {
        var collabLabels = item.collaborators.map(function(k) {
            return PROFILE_USERS[k] ? PROFILE_USERS[k].label : k;
        });
        desc = 'Con ' + collabLabels.join(', ');
    } else if (item.sharedFrom && PROFILE_USERS[item.sharedFrom]) {
        desc = 'Con ' + PROFILE_USERS[item.sharedFrom].label;
    }
    console.log('enviando save-momento:', JSON.stringify({
    pareja_id:   parejaId,
    titulo:      titulo,
    fecha:       fecha,
    descripcion: desc,
    emocion:     emocion
}));

    fetch('assets/couple/api.php?action=save-momento', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            pareja_id:   parejaId,
            titulo:      titulo,
            fecha:       fecha,
            descripcion: desc,
            emocion:     emocion
        })
    }).then(function(r) { return r.json(); })
      .then(function(data) {
          console.log('save-momento respuesta:', JSON.stringify(data));
          if (!data.ok || !data.id || !item.image) return;
          fetch('assets/couple/api.php?action=save-momento-foto-url', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ momento_id: data.id, foto_url: item.image })
          }).catch(function() {});
      })
      .catch(function(err) {
          console.log('ERROR save-momento:', err);
      });

    if (window.notifSystem) {
        window.notifSystem.show({
            id:      'momento_' + Date.now(),
            type:    'info',
            title:   '📅 Añadido al calendario',
            message: titulo + ' guardado como momento.',
            autoDismissAfter: 4000
        });
    }
}

})();
</script>
