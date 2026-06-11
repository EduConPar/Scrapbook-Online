<?php
// reproductor.php - All player HTML + PHP setup
// Included from desktop-base.php; expects $desktopUserKey and $hasPlayer already set.
require_once dirname(__DIR__) . '/assets/config.php';
require_once dirname(__DIR__) . '/db.php';

/* Pool global de pistas que el reproductor muestra al inicio:
   1) Listado estático por defecto para user1/user2 (semilla histórica).
   2) Pistas añadidas vía add-track → tabla music_extras. */
$youtubePlaylist = [];
if ($desktopUserKey === 'user1') {
    require_once dirname(__DIR__) . '/assets/music/playlist.php';
} elseif ($desktopUserKey === 'user2') {
    require_once dirname(__DIR__) . '/assets/music/angie-playlist.php';
}
$stmt = $pdo->prepare("SELECT me.video_id AS videoId, me.title, me.artist
                       FROM music_extras me
                       JOIN usuarios u ON me.user_id = u.id
                       WHERE u.user_key = ?
                       ORDER BY me.id ASC");
$stmt->execute([$desktopUserKey]);
$youtubePlaylist = array_merge($youtubePlaylist, $stmt->fetchAll(PDO::FETCH_ASSOC));

?>

<?php if ($hasPlayer): ?>
<div id="yt-player"></div>
<!-- REPRODUCTOR -->
<div class="window" id="music-player">
    <div class="title-bar" id="player-titlebar">
        <div class="title-bar-text" id="player-tb-text"><?php echo appTitleIcon('musicaIcon', '♪'); ?><span id="player-pl-name">Reproductor</span></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize" id="player-minimize"></button>
            <button aria-label="Maximize" id="player-maximize"></button>
            <button aria-label="Close" id="player-close"></button>
        </div>
    </div>
    <div class="window-body" id="player-body">
        <div id="player-content">
            <div id="player-main">
                <div id="player-cover-wrap" style="position:relative;">
                    <img id="player-cover" src="" alt="">
                    <!-- Indicador LIVE (host/guest). Oculto por defecto. -->
                    <span id="lt-live-dot" title="" style="display:none;position:absolute;top:3px;right:3px;width:9px;height:9px;border-radius:50%;background:var(--accent, #1db954);box-shadow:0 0 5px var(--accent, #1db954),0 0 1px rgba(0,0,0,0.5);animation:ltLivePulse 1.2s ease-in-out infinite;z-index:5;"></span>
                </div>
                <div id="player-info">
                    <p id="player-title">Sin título</p>
                    <p id="player-artist">—</p>
                    <div id="player-addedby"></div>
                </div>
            </div>
            <input type="range" id="player-progress" min="0" max="100" value="0" step="0.1">
            <div id="player-time">
                <span id="player-current">0:00</span>
                <span id="player-duration">0:00</span>
            </div>
            <div id="player-controls">
                <button class="button" id="btn-prev">◄◄</button>
                <button class="button" id="btn-play">►</button>
                <button class="button" id="btn-next">►►</button>
            </div>
        </div>
        <button class="button" id="btn-shuffle" title="Reproducción aleatoria">⇄</button>
        <button class="button" id="btn-edit-playlist" title="Ver playlist">☰</button>
        <button class="button" id="btn-lyrics" title="Letra"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:block;margin:auto;"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v2a7 7 0 0 0 14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg></button>
        <div id="player-volume-wrap">
            <div id="volume-track-outer">
                <input type="range" id="player-volume" min="0" max="100" value="100" step="1" orient="vertical">
            </div>
            <span id="volume-icon">◄))</span>
        </div>
    </div>
</div>

<!-- FULLSCREEN PLAYER — versión "maximizada" del reproductor, con
     vinilo girando + controles grandes (clon visual del fullscreen
     player del móvil). El estado de reproducción se comparte con el
     reproductor normal (mismos ytPlayer + playlist + currentTrack);
     este overlay solo es una vista alternativa. -->
<div id="player-full" aria-hidden="true">
    <div class="window pf-window">
        <div class="title-bar pf-titlebar">
            <div class="title-bar-text"><?php echo appTitleIcon('songIcon', '♪'); ?>Melon Player — <span id="pf-pl-name">Reproductor</span></div>
            <div class="title-bar-controls">
                <button aria-label="Restore" id="pf-restore"></button>
                <button aria-label="Close" id="pf-close-x"></button>
            </div>
        </div>
        <div class="window-body pf-body">
            <!-- Cover difuminado de fondo — añade color de ambiente
                 sin ocultar el chrome Win98. -->
            <div class="pf-bg-cover" id="pf-bg-cover" aria-hidden="true"></div>

            <!-- ═══ VINILO FLOTANTE — encima del hifi, fuera del componente ═══ -->
            <div class="pf-vinyl-floating">
                <div class="pf-vinyl-glow"></div>
                <div class="pf-vinyl-wrap">
                    <div class="pf-vinyl" id="pf-vinyl">
                        <div class="pf-vinyl-label empty" id="pf-vinyl-label">♪</div>
                        <div class="pf-vinyl-hole"></div>
                    </div>
                </div>
            </div>

            <!-- ═══ HIFI COMPONENT — solo título, progreso y controles ═══ -->
            <div class="pf-unit">
                <!-- LCD: título / artista + clock digital. -->
                <div class="pf-lcd">
                    <div class="pf-lcd-left">
                        <div class="pf-lcd-marker">▶</div>
                        <div class="pf-lcd-info">
                            <div class="pf-title-wrap"><span class="pf-title" id="pf-title">—</span></div>
                            <div class="pf-artist-wrap"><span class="pf-artist" id="pf-artist">—</span></div>
                        </div>
                    </div>
                    <div class="pf-lcd-clock">
                        <span class="pf-clock-cur" id="pf-time-cur">0:00</span>
                        <span class="pf-clock-sep">/</span>
                        <span class="pf-clock-tot" id="pf-time-tot">0:00</span>
                    </div>
                </div>

                <!-- PROGRESS: cassette tape con dos carretes a los lados. -->
                <div class="pf-tape">
                    <div class="pf-reel pf-reel-l" id="pf-reel-l">
                        <span class="pf-reel-spoke" style="--rot: 0deg"></span>
                        <span class="pf-reel-spoke" style="--rot: 60deg"></span>
                        <span class="pf-reel-spoke" style="--rot: 120deg"></span>
                    </div>
                    <div class="pf-progress" id="pf-progress-track">
                        <div class="pf-progress-fill" id="pf-progress-fill"></div>
                    </div>
                    <div class="pf-reel pf-reel-r" id="pf-reel-r">
                        <span class="pf-reel-spoke" style="--rot: 0deg"></span>
                        <span class="pf-reel-spoke" style="--rot: 60deg"></span>
                        <span class="pf-reel-spoke" style="--rot: 120deg"></span>
                    </div>
                </div>

                <!-- TRANSPORT BAR Win98 con divider. -->
                <div class="pf-transport">
                    <!-- IZQUIERDA: shuffle + lyrics -->
                    <div class="pf-extras">
                        <button class="button pf-extra" id="pf-shuffle" type="button" aria-label="Aleatorio" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="16 3 21 3 21 8"/>
                                <line x1="4" y1="20" x2="21" y2="3"/>
                                <polyline points="21 16 21 21 16 21"/>
                                <line x1="15" y1="15" x2="21" y2="21"/>
                                <line x1="4" y1="4" x2="9" y2="9"/>
                            </svg>
                        </button>
                        <button class="button pf-extra" id="pf-lyrics" type="button" aria-label="Letra">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="9" y="2" width="6" height="12" rx="3"/>
                                <path d="M5 10v2a7 7 0 0 0 14 0v-2"/>
                                <line x1="12" y1="19" x2="12" y2="22"/>
                                <line x1="8" y1="22" x2="16" y2="22"/>
                            </svg>
                        </button>
                    </div>

                    <!-- CENTRO: prev / play / next -->
                    <div class="pf-controls">
                        <button class="button pf-btn" id="pf-prev" type="button" aria-label="Anterior">⏮</button>
                        <button class="button pf-btn pf-primary" id="pf-toggle" type="button" aria-label="Play/Pausa">
                            <span class="pf-icon-play">►</span>
                            <span class="pf-icon-pause">⏸</span>
                        </button>
                        <button class="button pf-btn" id="pf-next" type="button" aria-label="Siguiente">⏭</button>
                    </div>

                    <!-- DERECHA: Volumen -->
                    <div class="pf-volume-wrap">
                        <span class="pf-vol-icon" id="pf-vol-icon" aria-hidden="true">◄))</span>
                        <input type="range" id="pf-volume" class="pf-volume" min="0" max="100" value="100" step="1" aria-label="Volumen">
                    </div>
                </div>
            </div>

            <!-- Lyric video — solo la línea actualmente sonando flota
                 sobre el reproductor con tremble por carácter +
                 slide/fade al cambiar. Sin fondo, sin blur, sin scroll. -->
            <div id="pf-lyrics-overlay" aria-hidden="true">
                <div class="pf-lyr-stage" id="pf-lyr-stage"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- LYRICS WINDOW — panel con letra plana o sincronizada (LRCLIB). -->
<div class="window" id="lyrics-window" data-no-auto-z
     style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); width:min(420px,92vw); height:min(560px,86vh); z-index:10004; flex-direction:column;">
    <div class="title-bar" id="lyrics-titlebar">
        <div class="title-bar-text" style="display:inline-flex;align-items:center;gap:6px;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v2a7 7 0 0 0 14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg><span id="lyrics-title-text">Letra</span></div>
        <div class="title-bar-controls">
            <button aria-label="Minimize" id="lyrics-min"></button>
            <button aria-label="Maximize" id="lyrics-max"></button>
            <button aria-label="Close" id="lyrics-close"></button>
        </div>
    </div>
    <div class="window-body" id="lyrics-body" style="flex:1;display:flex;flex-direction:column;overflow:hidden;padding:0;">
        <div id="lyrics-meta" style="padding:8px 10px;border-bottom:1px solid var(--bezel-dark-2,#808080);font-size:11px;display:flex;justify-content:space-between;align-items:center;gap:6px;">
            <span id="lyrics-track" style="font-weight:bold;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">—</span>
            <span id="lyrics-status" style="font-size:10px;color:var(--text-muted,#888);flex-shrink:0;"></span>
        </div>
        <div id="lyrics-scroll" style="flex:1;overflow-y:auto;padding:14px 16px;font-size:13px;line-height:1.6;text-align:center;white-space:pre-wrap;">
            <div id="lyrics-empty" style="opacity:0.6;padding-top:30%;">Cargando…</div>
            <div id="lyrics-lines"></div>
        </div>
    </div>
</div>
<style>
    .lyrics-line {
        padding: 4px 0;
        color: var(--text-muted, rgba(0,0,0,0.55));
        transition: color 0.25s, transform 0.25s, opacity 0.25s, font-weight 0.25s;
        opacity: 0.7;
    }
    .lyrics-line.active {
        color: var(--accent, #1db954);
        font-weight: bold;
        opacity: 1;
        transform: scale(1.06);
        text-shadow: 0 0 8px color-mix(in srgb, var(--accent) 30%, transparent);
    }
    .lyrics-line.past {
        opacity: 0.4;
    }
</style>

<!-- PLAYLIST EDITOR -->
<div class="window" id="playlist-editor">
    <div class="title-bar" id="pl-titlebar">
        <div class="title-bar-text" id="pl-title-text">♪ Playlists</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="pl-close"></button>
        </div>
    </div>
    <div class="window-body" id="pl-body">
        <!-- HOME VIEW -->
        <div id="pl-home">
            <div id="pl-home-list"></div>
            <div id="pl-home-footer">
                <button class="button" id="pl-create">+ Crear playlist</button>
            </div>
        </div>
        <!-- EDITOR VIEW -->
        <div id="pl-editor-view">
            <div id="pl-name-row">
                <label for="pl-name-input">Nombre</label>
                <input type="text" id="pl-name-input" placeholder="Nombre de la playlist">
            </div>
            <div id="pl-list"></div>
            <div id="pl-footer">
                <button class="button" id="pl-add">+ Añadir</button>
                <div id="pl-more-wrap">
                    <button class="button" id="pl-more">. . .</button>
                    <div id="pl-more-menu" class="window">
                        <div class="pl-menu-item" id="pl-more-import">Importar de YouTube</div>
                        <div class="pl-menu-item" id="pl-more-spotify">Importar de Spotify</div>
                        <div class="pl-menu-item" id="pl-more-tidal">Importar de Tidal</div>
                        <div class="pl-menu-item" id="pl-more-collab">Colaboradores</div>
                    </div>
                </div>
                <span style="flex:1"></span>
                <button class="button" id="pl-back">← Atrás</button>
                <button class="button" id="pl-save">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ADD TRACK DIALOG -->
<div class="window" id="add-track-dialog">
    <div class="title-bar">
        <div class="title-bar-text">+ Añadir canción</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="add-track-close"></button>
        </div>
    </div>
    <div class="window-body" id="add-track-body">
        <div class="field-row-stacked">
            <label for="add-yt-url">Enlace de YouTube, Spotify o Tidal</label>
            <input type="text" id="add-yt-url" placeholder="youtube.com/watch?v=… · open.spotify.com/track/… · tidal.com/track/…">
            <div id="add-title-preview-wrap"><span id="add-title-preview"></span></div>
        </div>
        <div class="field-row-stacked">
            <label for="add-artist">Artista</label>
            <input type="text" id="add-artist" placeholder="Nombre del artista">
        </div>
        <p id="add-track-error"></p>
        <div class="field-row" id="add-track-actions">
            <button class="button" id="add-track-cancel">Cancelar</button>
            <button class="button" id="add-track-submit">Añadir</button>
        </div>
    </div>
</div>

<!-- CREATE PLAYLIST DIALOG -->
<div class="window" id="create-playlist-dialog">
    <div class="title-bar">
        <div class="title-bar-text">+ Nueva playlist</div>
        <div class="title-bar-controls">
            <button aria-label="Minimize"></button>
            <button aria-label="Maximize"></button>
            <button aria-label="Close" id="create-pl-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label for="create-pl-name">Nombre de la playlist</label>
            <input type="text" id="create-pl-name" placeholder="Mi playlist">
        </div>
        <div class="field-row" style="justify-content: flex-end; gap: 4px; margin-top: 8px;">
            <button class="button" id="create-pl-cancel">Cancelar</button>
            <button class="button" id="create-pl-submit">Crear</button>
        </div>
    </div>
</div>

<!-- IMPORT PLAYLIST DIALOG -->
<div class="window" id="import-playlist-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Importar playlist de YouTube</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="import-pl-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="field-row-stacked">
            <label for="import-pl-url">Enlace de playlist de YouTube</label>
            <input type="text" id="import-pl-url" placeholder="https://youtube.com/playlist?list=...">
        </div>
        <p id="import-pl-status"></p>
        <div class="field-row" style="justify-content: flex-end; gap: 4px;">
            <button class="button" id="import-pl-cancel">Cancelar</button>
            <button class="button" id="import-pl-submit">Importar</button>
        </div>
    </div>
</div>

<!-- COLLABORATOR DIALOG -->
<div class="window" id="collab-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Colaboradores</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="collab-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p style="margin:0 0 6px;font-size:11px;">Playlist: "<span id="collab-pl-name"></span>"</p>
        <div id="collab-user-list"></div>
        <p id="collab-status"></p>
        <div class="field-row" style="justify-content:flex-end;margin-top:6px;">
            <button class="button" id="collab-cancel">Cerrar</button>
        </div>
    </div>
</div>

<!-- SPOTIFY IMPORT DIALOG -->
<div class="window" id="spotify-import-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Importar canciones</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="spotify-import-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p id="spotify-import-hint" style="margin:0 0 6px;">
            Exporta tu playlist desde exportify.net y sube el CSV.
        </p>
        <div class="spotify-warn">
            ⚠️ La búsqueda en YouTube puede no coincidir exactamente con algunas canciones. Si alguna falla o es incorrecta, añádela tú mismo con el botón <strong>+ Añadir</strong> del editor de playlist.
        </div>
        <div class="field-row-stacked">
            <label for="spotify-file-name">Archivo CSV</label>
            <div class="field-row" style="gap:4px;">
                <input type="text" id="spotify-file-name" readonly placeholder="Sin archivo seleccionado" style="flex:1;min-width:0;cursor:default;">
                <button class="button" id="spotify-file-browse" style="min-width:70px;flex-shrink:0;margin-bottom:3px;">Examinar...</button>
            </div>
            <input type="file" id="spotify-import-file" accept=".csv,text/csv" style="display:none;">
        </div>
        <div id="spotify-progress-wrap" style="display:none;margin:6px 0 0;">
            <div class="progress-indicator segmented" style="height:18px;padding:2px;">
                <span id="spotify-progress-fill" class="progress-indicator-bar" style="width:0%;"></span>
            </div>
            <p id="spotify-progress-text" style="font-size:10px;margin:3px 0 0;text-align:center;"></p>
        </div>
        <p id="spotify-import-status"></p>
        <div class="field-row" style="justify-content: flex-end; gap: 4px; margin-top: 10px;">
            <button class="button" id="spotify-import-cancel">Cancelar</button>
            <button class="button" id="spotify-import-submit">Importar</button>
        </div>
    </div>
</div>

<!-- TIDAL IMPORT DIALOG -->
<div class="window" id="tidal-import-dialog">
    <div class="title-bar">
        <div class="title-bar-text">Importar de Tidal</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="tidal-import-close"></button>
        </div>
    </div>
    <div class="window-body">
        <p style="margin:0 0 6px;">
            Pega el enlace de una <strong>canción</strong> o <strong>playlist</strong> de Tidal.
        </p>
        <div class="spotify-warn">
            ⚠️ La búsqueda en YouTube puede no coincidir exactamente con algunas canciones. Si alguna falla o es incorrecta, añádela tú mismo con el botón <strong>+ Añadir</strong> del editor de playlist.
        </div>
        <div class="field-row-stacked">
            <label for="tidal-import-url">Enlace de Tidal</label>
            <input type="text" id="tidal-import-url" placeholder="https://tidal.com/browse/playlist/... o .../track/...">
        </div>
        <div id="tidal-progress-wrap" style="display:none;margin:6px 0 0;">
            <div class="progress-indicator segmented" style="height:18px;padding:2px;">
                <span id="tidal-progress-fill" class="progress-indicator-bar" style="width:0%;"></span>
            </div>
            <p id="tidal-progress-text" style="font-size:10px;margin:3px 0 0;text-align:center;"></p>
        </div>
        <p id="tidal-import-status"></p>
        <div class="field-row" style="justify-content: flex-end; gap: 4px; margin-top: 10px;">
            <button class="button" id="tidal-import-cancel">Cancelar</button>
            <button class="button" id="tidal-import-submit">Importar</button>
        </div>
    </div>
</div>


<!-- CONFIRM DIALOG -->
<div class="window" id="confirm-dialog" style="display:none;position:fixed;z-index:10003;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;">
    <div class="title-bar">
        <div class="title-bar-text" id="confirm-dialog-title">Confirmar</div>
    </div>
    <div class="window-body" style="padding:10px 12px 12px;">
        <p id="confirm-dialog-msg" style="margin:0 0 12px;font-size:11px;"></p>
        <div class="field-row" style="justify-content:flex-end;gap:4px;">
            <button class="button" id="confirm-dialog-cancel">Cancelar</button>
            <button class="button" id="confirm-dialog-ok">Aceptar</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     LISTEN TOGETHER — banner de estado + modal de invitar.
     ═══════════════════════════════════════════════════════════════ -->
<style>
    /* Indicador LIVE — punto pulsante con el accent del tema. Aparece
       sobre el cover cuando estás hosteando o eres guest. */
    @keyframes ltLivePulse {
        0%, 100% { opacity: 1;   transform: scale(1); }
        50%      { opacity: 0.3; transform: scale(0.75); }
    }
    #lt-banner {
        position: fixed;
        top: 4px; left: 50%;
        transform: translateX(-50%);
        background: var(--win-bg, silver);
        color: var(--text, #000);
        padding: 4px 10px;
        font-size: 11px;
        display: none;
        align-items: center;
        gap: 8px;
        z-index: 9000;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
        max-width: 90vw;
    }
    #lt-banner.visible { display: flex; }
    #lt-banner .lt-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--accent, #1db954);
        animation: ltBlink 1.4s ease-in-out infinite;
        flex-shrink: 0;
    }
    @keyframes ltBlink { 0%,100% { opacity: 0.4; } 50% { opacity: 1; } }
    /* Reset minimal — solo quita outlines/borders al click sin tocar
       altura ni line-height (esos vienen del CSS original y deben
       ser idénticos para los 3 items). */
    .pl-menu-item,
    .pl-menu-item:active,
    .pl-menu-item:focus,
    .pl-menu-item:focus-visible {
        outline: none !important;
        border: none !important;
        margin: 0 !important;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
    }
    #lt-banner .lt-leave {
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        border: 0; cursor: pointer;
        padding: 2px 8px; font-size: 11px;
        font-family: inherit;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    #lt-modal { width: 320px; max-width: 92vw; }
    #lt-modal .window-body { padding: 8px 10px; }
    #lt-modal .lt-section-title {
        font-size: 11px;
        font-weight: bold;
        margin: 4px 0 6px;
        opacity: 0.85;
    }
    #lt-user-list {
        max-height: 200px;
        overflow-y: auto;
        background: var(--input-bg, #fff);
        color: var(--input-text, #000);
        padding: 2px;
        box-shadow:
            inset  1px  1px var(--bezel-dark-2, grey),
            inset -1px -1px var(--bezel-light-1, #fff);
        margin-bottom: 8px;
    }
    .lt-user-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 4px 6px;
        font-size: 12px;
        cursor: default;
    }
    .lt-user-row + .lt-user-row {
        border-top: 1px dotted rgba(0,0,0,0.15);
    }
    .lt-user-row button {
        font-size: 11px; padding: 2px 8px;
        font-family: inherit;
        background: var(--btn-bg, silver);
        color: var(--text, #000);
        border: 0; cursor: pointer;
        box-shadow:
            inset -1px -1px var(--bezel-dark-1, #0a0a0a),
            inset  1px  1px var(--bezel-light-1, #fff),
            inset -2px -2px var(--bezel-dark-2, grey),
            inset  2px  2px var(--bezel-light-2, #dfdfdf);
    }
    .lt-user-row button.invited { opacity: 0.6; }
    .lt-status {
        font-size: 11px; opacity: 0.75; padding: 4px 0;
    }
</style>

<div id="lt-banner">
    <span class="lt-dot"></span>
    <span id="lt-banner-text"></span>
    <button class="lt-leave" id="lt-leave-btn">Salir</button>
</div>


<div class="window" id="lt-modal" data-no-auto-z style="display:none;position:fixed;top:60px;left:50%;transform:translateX(-50%);z-index:9995;">
    <div class="title-bar">
        <div class="title-bar-text">🎧 Escuchar juntos</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="lt-modal-close"></button>
        </div>
    </div>
    <div class="window-body">
        <div class="lt-section-title">Estado</div>
        <div class="lt-status" id="lt-status-text">No estás en ninguna sesión.</div>

        <div class="lt-section-title">Invitar a:</div>
        <div id="lt-user-list">
            <div class="lt-status" style="text-align:center;">Cargando usuarios…</div>
        </div>

        <div class="field-row" style="justify-content:flex-end;gap:4px;">
            <button class="button" id="lt-end-btn" style="display:none;">Cerrar sesión</button>
        </div>
    </div>
</div>

<script>
<?php if ($hasPlayer): ?>
const playlist = <?php echo json_encode($youtubePlaylist); ?>;
var currentUserKey = '<?php echo $desktopUserKey; ?>';
var usersInfo = <?php
$_usersInfoArr = [];
foreach ($loginUsers as $_k => $_u) {
    $_usersInfoArr[$_k] = ['key' => $_k, 'label' => $_u['label'], 'img' => getUserImage($_u['label'])];
}
echo json_encode($_usersInfoArr);
?>;
var spotifyClientId   = '<?php echo SPOTIFY_CLIENT_ID; ?>';

/* Estado del reproductor persistido en SQL (user_settings.key='player').
   Reemplaza la antigua copia en localStorage['melonOS_player']. La lectura
   inicial usa window.DesktopState (precargado por desktop-base) y los
   writes se hacen optimistas + POST debounced al servidor. */
var MelonPlayerState = (function(){
    var state = null;
    function get(){
        if (state) return state;
        if (window.DesktopState && window.DesktopState.player) state = window.DesktopState.player;
        return state;
    }
    var _t = null;
    function push(){
        clearTimeout(_t);
        _t = setTimeout(function(){
            fetch('assets/desktop/api.php?action=save-player', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(state || {})
            }).catch(function(){});
        }, 200);
    }
    function setPlaylist(playlistId, trackIndex){
        state = { playlistId: playlistId, trackIndex: trackIndex|0 };
        if (window.DesktopState) window.DesktopState.player = state;
        push();
    }
    function setTrack(trackIndex){
        if (!state) state = { playlistId: null, trackIndex: 0 };
        state.trackIndex = trackIndex|0;
        if (window.DesktopState) window.DesktopState.player = state;
        push();
    }
    function setVolume(v){
        if (!state) state = { playlistId: null, trackIndex: 0 };
        state.volume = Math.max(0, Math.min(100, v|0));
        if (window.DesktopState) window.DesktopState.player = state;
        push();
    }
    return { get: get, setPlaylist: setPlaylist, setTrack: setTrack, setVolume: setVolume };
})();

let currentTrack      = 0;
let currentPlaylistId = null;
var currentPlaylistHasCollabs = false;
let ytPlayer = null;
let progressInterval  = null;
let autoplayRandom    = false;

/* Estado del WRAPPED tracking: guardamos referencia a la última canción
   que se EMPEZÓ a reproducir. Cuando cambia (skip / natural end / cierre
   de la ventana) la logueamos con el tiempo ESCUCHADO real (la
   `currentTime` del player en el momento de cambio) en lugar de su
   duración total. Si el usuario salta una canción en el segundo 30, se
   loguean 30s, no los 4 minutos completos. */
let _wrappedLastTrack       = null;
let _wrappedLastPlaylistId  = null;

function sendWrappedLog(track, listenedS, playlistId) {
    if (!track || !track.videoId || !track.title) return;
    listenedS = Math.max(0, Math.floor(listenedS || 0));
    /* Si escuchó menos de 3s no lo contamos como play — evita inflar
       counts por taps accidentales que cambian de track al instante. */
    if (listenedS < 3) return;
    var body = JSON.stringify({
        videoId:    track.videoId,
        title:      track.title,
        artist:     track.artist || '',
        playlistId: playlistId,
        durationS:  listenedS,
    });
    /* sendBeacon: garantiza que la petición se envíe incluso si la
       página se está cerrando (pagehide). Si no está disponible o
       falla, fallback a fetch. */
    try {
        if (navigator.sendBeacon) {
            var blob = new Blob([body], { type: 'application/json' });
            if (navigator.sendBeacon('assets/music/wrapped-api.php?action=log', blob)) return;
        }
    } catch (_) {}
    try {
        fetch('assets/music/wrapped-api.php?action=log', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: body,
            /* keepalive: lo mismo que sendBeacon, sobrevive al cierre. */
            keepalive: true,
        }).catch(function(){});
    } catch (_) {}
}

/* Al cerrar el escritorio (o cambiar a otra pestaña sin volver), logueamos
   la última canción con el tiempo escuchado. Sin esto, la PRIMERA canción
   nunca quedaría registrada porque solo se loguea al CAMBIAR de track. */
(function setupWrappedFlush() {
    function flush() {
        if (!_wrappedLastTrack) return;
        var listened = 0;
        try {
            if (ytPlayer && typeof ytPlayer.getCurrentTime === 'function') {
                listened = ytPlayer.getCurrentTime() || 0;
            }
        } catch (_) {}
        sendWrappedLog(_wrappedLastTrack, listened, _wrappedLastPlaylistId);
    }
    /* `pagehide` es más fiable que beforeunload (también dispara en BFCache). */
    window.addEventListener('pagehide', flush);
    /* `visibilitychange` para cuando el usuario cambia de tab o
       minimiza — guardamos lo escuchado HASTA AHORA por si no vuelve. */
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') flush();
    });
})();

/* ════════════════════════════════════════════════════════════════
   LISTEN TOGETHER — sesiones de escucha conjunta.

   Modelo: el HOST tiene el player real; postea estado a la API cada
   vez que cambia (track switch, play/pause, drift > 5s) — debounced
   ~600ms. El GUEST poll-ea cada 2s y aplica: loadVideoById si cambia
   videoId, seekTo si su currentTime difiere > 3s, play/pause para
   coincidir. Controles deshabilitados como guest (avisa que va a
   salir si los pulsa).

   Endpoints: assets/listen/api.php?action=<...>
═════════════════════════════════════════════════════════════════════ */
const LT_URL = 'assets/listen/api.php';
let LT_ROLE         = null;     /* 'host' | 'guest' | null */
let LT_SESSION_ID   = null;
let LT_GUEST_POLL_T = null;
let LT_HOST_BCAST_T = null;
let LT_INVITES_POLL_T = null;
let LT_LAST_HOST_VID = null;    /* host: último videoId enviado */
let LT_SHOWN_INVITES = new Set(); /* IDs de invites ya mostrados como notif */

function ltFetch(action, opts) {
    opts = opts || {};
    const isGet = !opts.body;
    const url = LT_URL + '?action=' + encodeURIComponent(action);
    return fetch(url, {
        method: isGet ? 'GET' : 'POST',
        credentials: 'same-origin',
        headers: isGet ? {} : { 'Content-Type': 'application/json' },
        body:    opts.body ? JSON.stringify(opts.body) : undefined,
    }).then(function(r){ return r.json(); }).catch(function(){ return { ok: false }; });
}

function ltBanner(text) {
    const b = document.getElementById('lt-banner');
    const t = document.getElementById('lt-banner-text');
    if (!b || !t) return;
    if (!text) { b.classList.remove('visible'); return; }
    t.textContent = text;
    b.classList.add('visible');
}

/* Indicador LIVE — muestra/oculta el punto pulsante sobre el cover del
   player. `mode`: 'host' / 'guest' / null (oculta). */
function ltUpdateLiveDot(mode, hostLabel) {
    const el = document.getElementById('lt-live-dot');
    if (!el) return;
    if (!mode) {
        el.style.display = 'none';
        el.title = '';
        return;
    }
    el.style.display = 'block';
    if (mode === 'host') {
        el.title = 'En directo · estás hosteando una sesión';
    } else if (mode === 'guest') {
        el.title = 'En directo · escuchando con ' + (hostLabel || 'el host');
    }
}

/* ── HOST: broadcast del estado ──────────────────────────────────── */
/* Set de user_keys de participantes que el host ya ha notificado, para
   evitar mostrar el toast "X se unió" repetidamente en cada poll. */
let LT_KNOWN_PARTICIPANTS = new Set();

async function ltHostBroadcast() {
    if (LT_ROLE !== 'host' || !ytPlayer) return;
    const track = playlist[currentTrack];
    if (!track) return;
    let ct = 0, dur = 0;
    try {
        ct  = Math.floor(ytPlayer.getCurrentTime() || 0);
        dur = Math.floor(ytPlayer.getDuration()    || 0);
    } catch (_) {}
    let isPlaying = false;
    try { isPlaying = ytPlayer.getPlayerState && ytPlayer.getPlayerState() === 1; } catch (_) {}
    const r = await ltFetch('update-state', { body: {
        videoId:      track.videoId,
        title:        track.title  || '',
        artist:       track.artist || '',
        coverUrl:     'https://i.ytimg.com/vi/' + track.videoId + '/mqdefault.jpg',
        currentTimeS: ct,
        durationS:    dur,
        isPlaying:    isPlaying,
    }});
    /* Detección de joiners nuevos. */
    if (r && r.ok && Array.isArray(r.participants_list)) {
        r.participants_list.forEach(p => {
            if (p.user_key && !LT_KNOWN_PARTICIPANTS.has(p.user_key)) {
                LT_KNOWN_PARTICIPANTS.add(p.user_key);
                if (window.notifSystem) {
                    const joinerName = p.label || '?';
                    window.notifSystem.show({
                        id:    'lt-joiner-' + p.user_key,
                        type:  'info',
                        title: joinerName + ' se unió',
                        message: joinerName + ' está escuchando contigo en tiempo real.',
                        autoDismissAfter: 4500,
                    });
                }
            }
        });
    }
}
function ltHostBroadcastDebounced() {
    if (LT_HOST_BCAST_T) clearTimeout(LT_HOST_BCAST_T);
    LT_HOST_BCAST_T = setTimeout(ltHostBroadcast, 600);
}

/* ── GUEST: poll + sync ──────────────────────────────────────────── */
async function ltGuestPoll() {
    if (LT_ROLE !== 'guest') return;
    const r = await ltFetch('poll');
    if (!r || !r.ok) return;
    if (r.closed || !r.session) {
        /* Host cerró → salimos. */
        ltLeaveLocal('El host cerró la sesión.');
        return;
    }
    const s = r.session;
    /* Banner del guest eliminado — solo notif al unirse. */
    /* Aplicar estado al player. */
    if (!ytPlayer || !s.video_id) return;
    let curVid = '';
    try {
        const d = ytPlayer.getVideoData && ytPlayer.getVideoData();
        if (d) curVid = d.video_id || '';
    } catch (_) {}
    if (curVid !== s.video_id) {
        try { ytPlayer.loadVideoById(s.video_id, parseInt(s.current_time_s, 10) || 0); } catch (_) {}
        /* Actualizamos el mini-player UI manualmente (cover + texto). */
        try {
            const coverEl = document.getElementById('player-cover');
            if (coverEl) {
                /* Usamos el cover_url del host si lo tiene, si no fallback
                   al thumbnail genérico de YouTube. */
                coverEl.src = s.cover_url
                    || 'https://i.ytimg.com/vi/' + s.video_id + '/mqdefault.jpg';
            }
            document.getElementById('player-info').innerHTML =
                '<div id="player-title">' + (s.track_title || '') + '</div>' +
                '<div id="player-artist">' + (s.track_artist || '') + '</div>' +
                '<div id="player-addedby">🎧 Sesión conjunta</div>';
        } catch (_) {}
        return;
    }
    /* Drift correction. */
    let myT = 0;
    try { myT = ytPlayer.getCurrentTime() || 0; } catch (_) {}
    const hostT = parseInt(s.current_time_s, 10) || 0;
    if (Math.abs(myT - hostT) > 3) {
        try { ytPlayer.seekTo(hostT, true); } catch (_) {}
    }
    /* Play/pause sync. */
    try {
        const ps = ytPlayer.getPlayerState();
        if (parseInt(s.is_playing, 10) === 1 && ps !== 1) ytPlayer.playVideo();
        if (parseInt(s.is_playing, 10) === 0 && ps === 1) ytPlayer.pauseVideo();
    } catch (_) {}
}

/* ── INICIAR como HOST ───────────────────────────────────────────── */
async function ltStartAsHost() {
    const r = await ltFetch('create', { body: {} });
    if (!r || !r.ok) {
        if (window.notifSystem) window.notifSystem.show({ type: 'error', title: 'Error', message: 'No se pudo crear sesión.' });
        return;
    }
    LT_ROLE       = 'host';
    LT_SESSION_ID = r.session_id;
    ltHostBroadcast();   /* primer push inmediato */
    ltUpdateLiveDot('host');
    ltRefreshModalState();
}

/* ── INICIAR como GUEST (al aceptar invite) ──────────────────────── */
async function ltJoinAsGuest(inviteId) {
    const r = await ltFetch('accept', { body: { inviteId: inviteId } });
    if (!r || !r.ok) {
        if (window.notifSystem) window.notifSystem.show({ type: 'error', title: 'Error', message: r.error || 'No se pudo unir.' });
        return;
    }
    LT_ROLE       = 'guest';
    LT_SESSION_ID = r.session_id;
    if (LT_GUEST_POLL_T) clearInterval(LT_GUEST_POLL_T);
    ltGuestPoll();
    LT_GUEST_POLL_T = setInterval(ltGuestPoll, 2000);
    ltUpdateLiveDot('guest', r.host_label);
    ltRefreshModalState();
    /* Notif de confirmación al guest — incluye el nombre del host. */
    if (window.notifSystem) {
        const hostName = r.host_label || 'el host';
        window.notifSystem.show({
            id:    'lt-joined-' + r.session_id,
            type:  'info',
            title: 'Te has unido a la sesión de ' + hostName,
            message: 'Ahora escuchas en tiempo real con ' + hostName + '.',
            autoDismissAfter: 4500,
        });
    }
}

/* ── SALIR de la sesión ──────────────────────────────────────────── */
async function ltLeave() {
    await ltFetch('leave', { body: {} });
    ltLeaveLocal();
}
function ltLeaveLocal(msg) {
    LT_ROLE       = null;
    LT_SESSION_ID = null;
    if (LT_GUEST_POLL_T) { clearInterval(LT_GUEST_POLL_T); LT_GUEST_POLL_T = null; }
    if (LT_HOST_BCAST_T) { clearTimeout(LT_HOST_BCAST_T); LT_HOST_BCAST_T = null; }
    ltBanner(null);
    ltUpdateLiveDot(null);   /* oculta el punto LIVE */
    LT_KNOWN_PARTICIPANTS = new Set();
    ltRefreshModalState();
    if (msg && window.notifSystem) window.notifSystem.show({ type: 'info', title: 'Sesión cerrada', message: msg });
}

/* ── MODAL: poblar y refrescar ───────────────────────────────────── */
async function ltOpenModal() {
    document.getElementById('lt-modal').style.display = 'block';
    ltRefreshModalState();
    /* Cargar lista de usuarios. */
    const r = await ltFetch('users');
    const list = document.getElementById('lt-user-list');
    if (!r || !r.ok || !r.users || r.users.length === 0) {
        list.innerHTML = '<div class="lt-status" style="text-align:center;line-height:1.45;">Aún no tienes amigos que invitar.<br><span style="opacity:0.75;font-size:11px;">Seguíos entre vosotros para haceros amigos.</span></div>';
        return;
    }
    list.innerHTML = r.users.map(function(u){
        return '<div class="lt-user-row">' +
            '<span>' + (u.label || u.user_key) + '</span>' +
            '<button data-userkey="' + u.user_key + '">Invitar</button>' +
            '</div>';
    }).join('');
    list.querySelectorAll('button[data-userkey]').forEach(function(b){
        b.addEventListener('click', async function(){
            /* Si no soy host aún, crearme como host primero. */
            if (LT_ROLE !== 'host') await ltStartAsHost();
            const k = b.getAttribute('data-userkey');
            const inv = await ltFetch('invite', { body: { toUser: k } });
            if (inv && inv.ok) {
                b.textContent = '✓ Invitado';
                b.classList.add('invited');
                b.disabled = true;
            } else {
                if (window.notifSystem) window.notifSystem.show({ type: 'error', title: 'Error', message: inv.error || 'No se pudo invitar.' });
            }
        });
    });
}
function ltCloseModal() {
    document.getElementById('lt-modal').style.display = 'none';
}
function ltRefreshModalState() {
    const t = document.getElementById('lt-status-text');
    const endBtn = document.getElementById('lt-end-btn');
    if (!t) return;
    if (LT_ROLE === 'host') {
        t.innerHTML = '🎤 <strong>Hosteando</strong> una sesión. Invita usuarios para que se unan.';
        endBtn.style.display = '';
    } else if (LT_ROLE === 'guest') {
        t.innerHTML = '🎧 Estás escuchando como invitado.';
        endBtn.style.display = '';
    } else {
        t.innerHTML = 'No estás en ninguna sesión. Invita a alguien para empezar una.';
        endBtn.style.display = 'none';
    }
}

/* ── POLL de invites para guests potenciales ─────────────────────── */
async function ltPollInvites() {
    if (LT_ROLE) return; /* ya en sesión, no procesa nuevos invites */
    const r = await ltFetch('invites');
    if (!r || !r.ok || !r.invites) return;
    r.invites.forEach(function(inv){
        if (LT_SHOWN_INVITES.has(inv.id)) return;
        LT_SHOWN_INVITES.add(inv.id);
        if (!window.notifSystem) return;
        /* type: 'action' añade botones Aceptar/Rechazar y NO auto-dismissa
           — el usuario decide. */
        window.notifSystem.show({
            id:      'lt-invite-' + inv.id,
            type:    'action',
            title:   (inv.from_label || '?') + ' te invita',
            message: 'Escuchar juntos: ' + (inv.track_title || 'una canción'),
            onAccept: function(){ ltJoinAsGuest(inv.id); },
            onReject: function(){
                /* Marcar el invite como declinado en el backend. */
                ltFetch('decline', { body: { inviteId: inv.id } });
            },
        });
    });
}

/* ── HOOKS al player ─────────────────────────────────────────────── */
/* Cada cambio de track del HOST → broadcast. updateTrackUI es donde el
   desktop ya rastrea para wrapped; hooks el broadcast ahí también. */
(function(){
    const origUpdateTrackUI = (typeof updateTrackUI === 'function') ? updateTrackUI : null;
    if (origUpdateTrackUI) {
        window.updateTrackUI = function(track) {
            origUpdateTrackUI(track);
            if (LT_ROLE === 'host') ltHostBroadcastDebounced();
        };
    }
})();
/* Periódico: si soy host, mando state cada 5s para mantener participantes. */
setInterval(function(){
    if (LT_ROLE === 'host') ltHostBroadcast();
}, 5000);

/* Listeners UI del modal Listen Together. El item del context menu
   se inyecta en showTrackCtxMenu (más abajo) junto a "Añadir a otra
   playlist" y "Añadir a mi perfil". */
document.addEventListener('DOMContentLoaded', function(){
    const close = document.getElementById('lt-modal-close');
    if (close) close.addEventListener('click', ltCloseModal);
    const leave = document.getElementById('lt-leave-btn');
    if (leave) leave.addEventListener('click', ltLeave);
    const endBtn = document.getElementById('lt-end-btn');
    if (endBtn) endBtn.addEventListener('click', function(){ ltLeave(); ltCloseModal(); });
});
/* Exposición global para que showTrackCtxMenu pueda llamarlos. */
window.ltOpenModal = ltOpenModal;
window.ltLeave     = ltLeave;

/* (Click derecho sobre el reproductor lo maneja desktop-base.php sobre
   #player-main, que ahora incluye la opción "Escuchar juntos". Aquí no
   duplicamos el listener — duplicarlo provocaba 2 menús superpuestos.) */

/* Al cargar, checkear si ya estoy en sesión (refresh de pestaña). */
(async function ltBootstrap(){
    const r = await ltFetch('current');
    if (!r || !r.ok || !r.role) {
        /* Iniciar poll de invites. */
        LT_INVITES_POLL_T = setInterval(ltPollInvites, 5000);
        return;
    }
    LT_ROLE       = r.role;
    LT_SESSION_ID = r.session ? r.session.id : null;
    if (LT_ROLE === 'guest') {
        if (LT_GUEST_POLL_T) clearInterval(LT_GUEST_POLL_T);
        LT_GUEST_POLL_T = setInterval(ltGuestPoll, 2000);
        ltGuestPoll();
        ltUpdateLiveDot('guest', r.host_label);
        /* Guest sin banner — solo notif al unirse (que ya pasó). */
    } else if (LT_ROLE === 'host') {
        ltUpdateLiveDot('host');
    }
})();

/* win98Confirm vive ahora en assets/js/win98-dialogs.js (compartido).
   La firma (message, title, onOk) se mantiene compatible con los callers. */
let stopTitleMarquee  = null;
let stopPlNameMarquee = null;
var refreshPlaylists  = null;

/* Spotify PKCE OAuth */
var spotifyPKCE = (function() {
    var REDIRECT_URI = location.origin + location.pathname;

    function rnd(n) {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var arr = crypto.getRandomValues(new Uint8Array(n));
        return Array.from(arr, function(b) { return chars[b % chars.length]; }).join('');
    }
    function b64url(buf) {
        return btoa(String.fromCharCode.apply(null, new Uint8Array(buf)))
            .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }
    function getToken() {
        try {
            var d = JSON.parse(localStorage.getItem('sp_token') || 'null');
            if (d && d.t && d.e > Date.now()) return d.t;
        } catch(e) {}
        return null;
    }
    function saveToken(data) {
        localStorage.setItem('sp_token', JSON.stringify({ t: data.access_token, e: Date.now() + data.expires_in * 1000 }));
    }

    /* Handle OAuth callback */
    var p = new URLSearchParams(location.search);
    if (p.get('state') === 'sp_import') {
        history.replaceState(null, '', location.pathname);
        if (p.get('error')) {
            window._spotifyAuthError = p.get('error');
        } else if (p.get('code')) {
            var code     = p.get('code');
            var verifier = localStorage.getItem('sp_verifier');
            if (verifier) {
                fetch('https://accounts.spotify.com/api/token', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ client_id: spotifyClientId, grant_type: 'authorization_code', code: code, redirect_uri: REDIRECT_URI, code_verifier: verifier }).toString()
                })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    localStorage.removeItem('sp_verifier');
                    if (d.access_token) {
                        saveToken(d);
                        window._spotifyPendingUrl = localStorage.getItem('sp_pending_url') || '';
                        localStorage.removeItem('sp_pending_url');
                        window._spotifyJustAuthed = true;
                    } else {
                        window._spotifyAuthError = d.error || 'token_error';
                    }
                })
                .catch(function(e) { window._spotifyAuthError = e.message; });
            }
        }
    }

    return {
        getToken: getToken,
        clearToken: function() { localStorage.removeItem('sp_token'); localStorage.removeItem('sp_verifier'); },
        authorize: function(pendingUrl) {
            var verifier = rnd(128);
            localStorage.setItem('sp_verifier', verifier);
            if (pendingUrl) localStorage.setItem('sp_pending_url', pendingUrl);
            crypto.subtle.digest('SHA-256', new TextEncoder().encode(verifier))
            .then(function(buf) {
                location.href = 'https://accounts.spotify.com/authorize?' + new URLSearchParams({
                    response_type: 'code', client_id: spotifyClientId, scope: 'playlist-read-public playlist-read-collaborative',
                    redirect_uri: REDIRECT_URI, code_challenge_method: 'S256', code_challenge: b64url(buf), state: 'sp_import'
                }).toString();
            });
        }
    };
})();

function updatePlayerTitle(name) {
    var el     = document.getElementById('player-pl-name');
    var parent = document.getElementById('player-tb-text');
    if (!el || !parent) return;
    /* El icono PNG ♪/musicaIcon vive ahora FUERA del span (en el HTML
       del title-bar), así que aquí solo escribimos el nombre del
       playlist — sin prefijo ♪ que duplicaría el icono. */
    el.textContent = name;
    if (stopPlNameMarquee) { stopPlNameMarquee(); stopPlNameMarquee = null; }
    setTimeout(function() {
        stopPlNameMarquee = marqueeScroll(el, parent, 40, 1500);
    }, 50);
}

function marqueeScroll(el, parent, speed, pauseMs) {
    el.style.transform = 'translateX(0)';
    var pos = 0, last = null, pausing = true, fromRight = false, raf = null, timer = null;
    var W = el.offsetWidth, C = parent.clientWidth;
    if (W <= C) return function(){};
    function tick(ts) {
        if (!last) last = ts;
        var dt = Math.min(ts - last, 50);
        last = ts;
        pos -= speed * dt / 1000;
        if (pos < -W) {
            pos = C + (pos + W);
            fromRight = true;
        }
        if (fromRight && pos <= 0) {
            pos = 0;
            fromRight = false;
            el.style.transform = 'translateX(0)';
            last = null; pausing = true;
            timer = setTimeout(function() { pausing = false; raf = requestAnimationFrame(tick); }, pauseMs);
            return;
        }
        el.style.transform = 'translateX(' + pos + 'px)';
        raf = requestAnimationFrame(tick);
    }
    timer = setTimeout(function() { pausing = false; raf = requestAnimationFrame(tick); }, pauseMs);
    return function() {
        clearTimeout(timer);
        if (raf) cancelAnimationFrame(raf);
        el.style.transform = '';
    };
}

const playerWindow  = document.getElementById('music-player');
const playerCover   = document.getElementById('player-cover');
const playerTitle   = document.getElementById('player-title');
const playerArtist  = document.getElementById('player-artist');
const playerProg    = document.getElementById('player-progress');
const playerCurrent = document.getElementById('player-current');
const playerDur     = document.getElementById('player-duration');
const btnPlay       = document.getElementById('btn-play');
const volSlider     = document.getElementById('player-volume');

function pixelateImg(img, factor) {
    if (!img.naturalWidth || img.src.startsWith('data:')) return;
    const w = Math.max(1, Math.round(img.naturalWidth  * factor));
    const h = Math.max(1, Math.round(img.naturalHeight * factor));
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d');
    ctx.imageSmoothingEnabled = false;
    try {
        ctx.drawImage(img, 0, 0, w, h);
        img.src = c.toDataURL('image/png');
    } catch (e) {}
}

playerCover.addEventListener('load', function() { pixelateImg(playerCover, 0.25); });

function formatTime(s)
{
    const m = Math.floor(s / 60);
    return m + ':' + String(Math.floor(s % 60)).padStart(2, '0');
}

function updateTrackUI(index)
{
    if (!playlist.length) return;
    const track = playlist[index];
    playerTitle.textContent  = track.title;
    playerArtist.textContent = track.artist || '—';
    playerCover.crossOrigin = 'anonymous';
    playerCover.src = `https://img.youtube.com/vi/${track.videoId}/mqdefault.jpg`;

    var addedByEl = document.getElementById('player-addedby');
    addedByEl.innerHTML = '';
    if (currentPlaylistHasCollabs && track.addedBy) {
        var adderInfo = null;
        Object.keys(usersInfo).forEach(function(k) {
            if (usersInfo[k].label === track.addedBy) adderInfo = usersInfo[k];
        });
        if (adderInfo && adderInfo.img) {
            var wrap = document.createElement('div');
            wrap.className = 'player-addedby-wrap';
            wrap.title = adderInfo.label;
            var img = document.createElement('img');
            img.className = 'player-addedby-img';
            img.src = adderInfo.img;
            img.alt = adderInfo.label;
            wrap.appendChild(img);
            addedByEl.appendChild(wrap);
            addedByEl.style.display = 'flex';
        } else {
            addedByEl.style.display = 'none';
        }
    } else {
        addedByEl.style.display = 'none';
    }
    playerProg.value = 0;
    playerCurrent.textContent = '0:00';
    playerDur.textContent = '0:00';
    if (stopTitleMarquee) { stopTitleMarquee(); stopTitleMarquee = null; }
    setTimeout(function() {
        stopTitleMarquee = marqueeScroll(playerTitle, playerTitle.parentElement, 50, 1000);
    }, 50);

    MelonPlayerState.setTrack(index);

    if (typeof window.updatePlaylistPlayingHighlight === 'function') {
        window.updatePlaylistPlayingHighlight(currentPlaylistId, index);
    }

    /* WRAPPED tracking: ANTES de cambiar el track activo, logueamos
       el ANTERIOR con el tiempo que el usuario realmente escuchó
       (ytPlayer.getCurrentTime() en el momento del cambio).
       Se llama tanto en skip (switchTrack → updateTrackUI) como en
       fin natural (ENDED → updateTrackUI), así que cubre ambos casos. */
    if (_wrappedLastTrack && _wrappedLastTrack.videoId !== track.videoId) {
        var listened = 0;
        try {
            if (ytPlayer && typeof ytPlayer.getCurrentTime === 'function') {
                listened = ytPlayer.getCurrentTime() || 0;
            }
        } catch (_) {}
        sendWrappedLog(_wrappedLastTrack, listened, _wrappedLastPlaylistId);
    }
    _wrappedLastTrack      = track;
    _wrappedLastPlaylistId = currentPlaylistId;
}

function startProgress()
{
    clearInterval(progressInterval);
    progressInterval = setInterval(() => {
        if (!ytPlayer || !ytPlayer.getDuration) return;
        const dur = ytPlayer.getDuration();
        const cur = ytPlayer.getCurrentTime();
        if (dur) {
            playerProg.value = (cur / dur) * 100;
            playerCurrent.textContent = formatTime(cur);
            playerDur.textContent = formatTime(dur);
        }
    }, 500);
}

function onYouTubeIframeAPIReady()
{
    ytPlayer = new YT.Player('yt-player', {
        width: 1, height: 1,
        playerVars: { controls: 0, rel: 0, modestbranding: 1 },
        events: {
            onReady: function() {
                /* onReady puede dispararse antes de que fetchState haya
                   resuelto. whenReady aplica el volumen y la playlist
                   guardada en cuanto ambos extremos estén listos. */
                window.DesktopState.whenReady(function(){
                    var saved = MelonPlayerState.get();
                    if (saved && typeof saved.volume === 'number') {
                        ytPlayer.setVolume(saved.volume);
                    } else {
                        ytPlayer.setVolume(parseInt(volSlider.value));
                    }
                    restorePlaylist(saved);
                });

                function restoreDefault() {
                    if (playlist.length) { updateTrackUI(0); ytPlayer.cueVideoById(playlist[0].videoId); }
                }

                function restorePlaylist(saved) {
                if (saved && saved.playlistId) {
                    fetch('assets/music/api.php?action=get-playlists')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!Array.isArray(data)) { restoreDefault(); return; }
                        var found = null;
                        for (var i = 0; i < data.length; i++) {
                            if (data[i].id === saved.playlistId) { found = data[i]; break; }
                        }
                        if (!found || !found.tracks.length) { restoreDefault(); return; }
                        currentPlaylistId = found.id;
                        currentPlaylistHasCollabs = !!(found.sharedFrom || (found.collaborators && found.collaborators.length > 0));
                        playlist.length = 0;
                        found.tracks.forEach(function(t) { playlist.push(t); });
                        var idx = (saved.trackIndex >= 0 && saved.trackIndex < playlist.length) ? saved.trackIndex : 0;
                        currentTrack = idx;
                        updateTrackUI(idx);
                        updatePlayerTitle(found.name);
                        ytPlayer.cueVideoById(playlist[idx].videoId);
                    })
                    .catch(restoreDefault);
                } else {
                    restoreDefault();
                }
                }
            },
            onStateChange: (e) => {
                if (e.data === YT.PlayerState.PLAYING) {
                    btnPlay.textContent = '⏸';
                    startProgress();
                } else if (e.data === YT.PlayerState.PAUSED || e.data === YT.PlayerState.ENDED) {
                    btnPlay.textContent = '►';
                    clearInterval(progressInterval);
                }
                if (e.data === YT.PlayerState.ENDED) {
                    if (autoplayRandom && playlist.length > 1) {
                        let next;
                        do { next = Math.floor(Math.random() * playlist.length); } while (next === currentTrack);
                        currentTrack = next;
                    } else {
                        currentTrack = (currentTrack + 1) % playlist.length;
                    }
                    updateTrackUI(currentTrack);
                    ytPlayer.loadVideoById(playlist[currentTrack].videoId);
                }
            }
        }
    });
}

function switchTrack(index, keepPlaying)
{
    currentTrack = index;
    updateTrackUI(index);
    if (!ytPlayer) return;
    if (keepPlaying) {
        ytPlayer.loadVideoById(playlist[index].videoId);
    } else {
        ytPlayer.cueVideoById(playlist[index].videoId);
    }
}

btnPlay.addEventListener('click', () => {
    if (!ytPlayer || !playlist.length) return;
    const state = ytPlayer.getPlayerState();
    if (state === YT.PlayerState.PLAYING) {
        ytPlayer.pauseVideo();
    } else {
        ytPlayer.playVideo();
    }
});

document.getElementById('btn-prev').addEventListener('click', () => {
    if (!playlist.length) return;
    const playing = ytPlayer && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING;
    switchTrack((currentTrack - 1 + playlist.length) % playlist.length, playing);
});

document.getElementById('btn-next').addEventListener('click', () => {
    if (!playlist.length) return;
    const playing = ytPlayer && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING;
    if (autoplayRandom && playlist.length > 1) {
        let next;
        do { next = Math.floor(Math.random() * playlist.length); } while (next === currentTrack);
        switchTrack(next, playing);
    } else {
        switchTrack((currentTrack + 1) % playlist.length, playing);
    }
});

document.getElementById('btn-shuffle').addEventListener('click', () => {
    autoplayRandom = !autoplayRandom;
    document.getElementById('btn-shuffle').classList.toggle('btn-active', autoplayRandom);
});

playerProg.addEventListener('input', function() {
    if (ytPlayer && ytPlayer.getDuration) {
        ytPlayer.seekTo((this.value / 100) * ytPlayer.getDuration(), true);
    }
});

volSlider.addEventListener('input', function() {
    if (ytPlayer && ytPlayer.setVolume) {
        ytPlayer.setVolume(parseInt(this.value));
    }
    const icon = document.getElementById('volume-icon');
    if (parseInt(this.value) === 0) icon.textContent = '◄✕';
    else if (parseInt(this.value) < 50) icon.textContent = '◄)';
    else icon.textContent = '◄))';
    MelonPlayerState.setVolume(parseInt(this.value));
});

/* Restore volume before player API loads.
   El estado vive en window.DesktopState, que se rellena de forma async
   por desktop-base.fetchState(). whenReady garantiza que aplicamos el
   volumen guardado AUNQUE el fetch aún no haya resuelto al evaluar este
   script — y también si el YT player ya está listo, le aplicamos el volumen. */
window.DesktopState.whenReady(function(){
    var s = MelonPlayerState.get() || {};
    if (typeof s.volume !== 'number') return;
    volSlider.value = s.volume;
    var icon = document.getElementById('volume-icon');
    if (s.volume === 0) icon.textContent = '◄✕';
    else if (s.volume < 50) icon.textContent = '◄)';
    else icon.textContent = '◄))';
    if (ytPlayer && ytPlayer.setVolume) ytPlayer.setVolume(s.volume);
});


document.getElementById('player-close').addEventListener('click', () => {
    if (ytPlayer) ytPlayer.pauseVideo();
    btnPlay.textContent = '►';
    clearInterval(progressInterval);
    taskbarManager.unregister('music-player');
});

document.getElementById('player-minimize').addEventListener('click', function() {
    playerWindow.style.display = 'none';
});

document.getElementById('tray-player-btn').addEventListener('click', function() {
    playerWindow.style.display = playerWindow.style.display === 'none' ? 'block' : 'none';
});

/* Cargar YouTube IFrame API */
const ytScript = document.createElement('script');
ytScript.src = 'https://www.youtube.com/iframe_api';
document.head.appendChild(ytScript);

/* Arrastrar ventana */
(function() {
    const titlebar = document.getElementById('player-titlebar');
    let dragging = false, ox, oy;
    titlebar.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'BUTTON') return;
        dragging = true;
        const rect = playerWindow.getBoundingClientRect();
        playerWindow.style.left   = rect.left + 'px';
        playerWindow.style.top    = rect.top  + 'px';
        playerWindow.style.right  = 'auto';
        playerWindow.style.bottom = 'auto';
        ox = e.clientX - rect.left;
        oy = e.clientY - rect.top;
    });
    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        playerWindow.style.left = (e.clientX - ox) + 'px';
        playerWindow.style.top  = (e.clientY - oy) + 'px';
    });
    document.addEventListener('mouseup', () => { dragging = false; });
})();

var openAddDialog = null;
var addTrackCallback = null;

/* Añadir canción */
(function() {
    var dialog   = document.getElementById('add-track-dialog');
    var urlInput = document.getElementById('add-yt-url');
    var artInput = document.getElementById('add-artist');
    var errEl    = document.getElementById('add-track-error');
    var fetchedTitle = '';

    /* El artista se autocompleta desde los metadatos (Spotify/Tidal/YouTube)
       mientras el usuario no lo haya escrito a mano. */
    var artistTouched = false;
    artInput.addEventListener('input', function() { artistTouched = true; });
    function setArtist(name) {
        if (artistTouched) return;          // respeta lo que escribió el usuario
        artInput.value = name || '';
    }
    function cleanYtAuthor(name) {
        if (!name) return '';
        return name.replace(/\s*-\s*topic$/i, '').replace(/\s*VEVO$/i, '').trim();
    }

    function parseYouTubeId(raw) {
        var s = raw.trim();
        var m;
        m = s.match(/youtu\.be\/([A-Za-z0-9_-]{11})/);
        if (m) return m[1];
        m = s.match(/[?&]v=([A-Za-z0-9_-]{11})/);
        if (m) return m[1];
        m = s.match(/\/embed\/([A-Za-z0-9_-]{11})/);
        if (m) return m[1];
        if (/^[A-Za-z0-9_-]{11}$/.test(s)) return s;
        return null;
    }

    function openDialog() {
        urlInput.value = ''; artInput.value = '';
        artistTouched = false;
        errEl.textContent = '';
        resetTitlePreview();
        dialog.style.display = 'block';
        urlInput.focus();
    }
    function closeDialog() {
        dialog.style.display = 'none';
        if (!addTrackCallback) addTrackCallback = null;
    }
    openAddDialog = openDialog;

    var titlePreview = document.getElementById('add-title-preview');
    var fetchTimer   = null;
    var fetchedDuration = 0;
    var fetchedVideoId  = null;

    var stopPreviewMarquee = null;

    function resetTitlePreview() {
        fetchedTitle    = '';
        fetchedDuration = 0;
        fetchedVideoId  = null;
        titlePreview.textContent = '';
        titlePreview.style.color = '';
        if (stopPreviewMarquee) { stopPreviewMarquee(); stopPreviewMarquee = null; }
    }

    function applyScrollIfNeeded() {
        if (stopPreviewMarquee) { stopPreviewMarquee(); stopPreviewMarquee = null; }
        setTimeout(function() {
            stopPreviewMarquee = marqueeScroll(titlePreview, titlePreview.parentElement, 50, 1000);
        }, 50);
    }

    urlInput.addEventListener('input', function() {
        clearTimeout(fetchTimer);
        resetTitlePreview();
        var raw = urlInput.value.trim();

        if (/open\.spotify\.com\/.+\/track\/|spotify:track:/.test(raw)) {
            titlePreview.textContent = 'Buscando en Spotify...';
            fetchTimer = setTimeout(function() {
                fetch('assets/music/api.php?action=spotify-track&url=' + encodeURIComponent(raw))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) {
                        titlePreview.textContent = data.error;
                        titlePreview.style.color = '#c00';
                        return;
                    }
                    fetchedTitle    = data.title;
                    fetchedVideoId  = data.videoId;
                    fetchedDuration = data.duration;
                    setArtist(data.artist);
                    titlePreview.textContent = '♪ ' + data.title + ' [Spotify]';
                    titlePreview.style.color = '';
                    applyScrollIfNeeded();
                })
                .catch(function() {
                    titlePreview.textContent = 'Error de conexión';
                    titlePreview.style.color = '#c00';
                });
            }, 400);
            return;
        }

        if (/tidal\.com\/(?:browse\/)?track\/\d+/i.test(raw)) {
            titlePreview.textContent = 'Buscando en Tidal...';
            fetchTimer = setTimeout(function() {
                fetch('assets/music/api.php?action=tidal-track&url=' + encodeURIComponent(raw))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) {
                        titlePreview.textContent = data.error;
                        titlePreview.style.color = '#c00';
                        return;
                    }
                    fetchedTitle    = data.title;
                    fetchedVideoId  = data.videoId;
                    fetchedDuration = data.duration;
                    setArtist(data.artist);
                    titlePreview.textContent = '♪ ' + data.title + ' [Tidal]';
                    titlePreview.style.color = '';
                    applyScrollIfNeeded();
                })
                .catch(function() {
                    titlePreview.textContent = 'Error de conexión';
                    titlePreview.style.color = '#c00';
                });
            }, 400);
            return;
        }

        var videoId = parseYouTubeId(urlInput.value);
        if (!videoId) return;
        titlePreview.textContent = 'Obteniendo título...';
        fetchTimer = setTimeout(function() {
            fetch('assets/music/api.php?action=yt-title&id=' + videoId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.title) {
                    fetchedTitle = data.title;
                    setArtist(cleanYtAuthor(data.author));
                    titlePreview.textContent = '♪ ' + data.title;
                    titlePreview.style.color = '';
                    applyScrollIfNeeded();
                } else {
                    titlePreview.textContent = data.error || 'No se pudo obtener el título';
                    titlePreview.style.color = '#c00';
                }
            })
            .catch(function() {
                titlePreview.textContent = 'Error de conexión';
                titlePreview.style.color = '#c00';
            });

            fetch('assets/music/api.php?action=yt-duration&id=' + videoId)
            .then(function(r) { return r.json(); })
            .then(function(data) { if (data.duration) fetchedDuration = data.duration; })
            .catch(function() {});
        }, 400);
    });

    document.getElementById('add-track-close').addEventListener('click', function() { addTrackCallback = null; closeDialog(); });
    document.getElementById('add-track-cancel').addEventListener('click', function() { addTrackCallback = null; closeDialog(); });

    document.getElementById('add-track-submit').addEventListener('click', function() {
        var videoId = fetchedVideoId || parseYouTubeId(urlInput.value);
        var title   = fetchedTitle;
        var artist  = artInput.value.trim();

        if (!videoId) { errEl.textContent = 'No se pudo extraer el ID del enlace.'; return; }
        if (!title)   { errEl.textContent = 'No se pudo obtener el título. Espera un momento.'; return; }
        errEl.textContent = '';

        var newTrack = { videoId: videoId, title: title, artist: artist, duration: fetchedDuration, addedBy: (usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '') };

        if (addTrackCallback) {
            addTrackCallback(newTrack);
            addTrackCallback = null;
            closeDialog();
            return;
        }

        fetch('assets/music/api.php?action=add-track', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newTrack)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) { errEl.textContent = data.error; return; }
            playlist.push(newTrack);
            if (playlist.length === 1) {
                updateTrackUI(0);
                ytPlayer.cueVideoById(playlist[0].videoId);
            }
            closeDialog();
        })
        .catch(function(e) { errEl.textContent = 'Error: ' + e.message; });
    });
})();

/* Editor de playlist */
(function() {
    var editor       = document.getElementById('playlist-editor');
    var plList       = document.getElementById('pl-list');
    var plHome       = document.getElementById('pl-home');
    var plEditorView = document.getElementById('pl-editor-view');
    var plTitleText  = document.getElementById('pl-title-text');
    var plHomeList   = document.getElementById('pl-home-list');
    var plNameInput  = document.getElementById('pl-name-input');
    var editList     = [];
    var allPlaylists = [];
    var editingPlIdx = -1;
    var dragSrc      = null;

    function openEditor() {
        if (taskbarManager.isRegistered('playlist-editor')) {
            taskbarManager.restore('playlist-editor');
        } else {
            taskbarManager.register('playlist-editor', 'Playlists', '<img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', 'flex');
        }
        showHome();
        loadPlaylists();
    }
    function closeEditor() {
        taskbarManager.unregister('playlist-editor');
    }

    function showHome() {
        plHome.style.display       = 'flex';
        plEditorView.style.display = 'none';
        plTitleText.textContent    = '♪ Playlists';
        if (allPlaylists.length > 0) renderHome();
    }

    function showEditorView(idx) {
        var pl = allPlaylists[idx];
        editingPlIdx = idx;
        plNameInput.value    = pl.name;
        plNameInput.disabled = !!pl.sharedFrom;
        editList = pl.tracks.map(function(t) {
            return { title: t.title, artist: t.artist || '', videoId: t.videoId, duration: t.duration || 0, addedBy: t.addedBy || '' };
        });
        plHome.style.display       = 'none';
        plEditorView.style.display = 'flex';
        plTitleText.textContent    = '▶ Ver playlist';
        renderList();
    }

    function loadPlaylists() {
        plHomeList.innerHTML = '<div class="pl-home-msg">Cargando...</div>';
        fetch('assets/music/api.php?action=get-playlists')
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(data) {
            if (data.error) { plHomeList.innerHTML = '<div class="pl-home-msg">Error: ' + data.error + '</div>'; return; }
            allPlaylists = data;
            renderHome();
        })
        .catch(function(e) {
            plHomeList.innerHTML = '<div class="pl-home-msg">Error: ' + e.message + '</div>';
        });
    }

    function renderHome() {
        plHomeList.innerHTML = '';
        if (allPlaylists.length === 0) {
            plHomeList.innerHTML = '<div class="pl-home-msg">Sin playlists. Crea una nueva.</div>';
            return;
        }
        allPlaylists.forEach(function(pl, idx) {
            var row      = document.createElement('div');
            row.className = 'pl-home-row';

            var totalSec = 0, hasDur = false;
            pl.tracks.forEach(function(t) { if (t.duration) { totalSec += t.duration; hasDur = true; } });
            var durStr = '';
            if (hasDur) {
                var h = Math.floor(totalSec / 3600), m = Math.floor((totalSec % 3600) / 60);
                durStr = ' · ' + (h > 0 ? h + 'h ' : '') + m + 'm';
            }

            var infoEl = document.createElement('div');
            infoEl.className = 'pl-home-info';

            var nameEl = document.createElement('div');
            nameEl.className   = 'pl-home-name';
            nameEl.textContent = (pl.sharedFrom ? '[+] ' : '') + pl.name;

            var metaEl = document.createElement('div');
            metaEl.className = 'pl-home-meta';
            var metaStr = pl.tracks.length + ' Canciones' + durStr;
            metaEl.textContent = metaStr;

            infoEl.appendChild(nameEl);
            infoEl.appendChild(metaEl);

            var btns = document.createElement('div');
            btns.className = 'pl-home-btns';

            var playBtn = makeBtn('▶', 'pl-action-btn', function() {
                if (!pl.tracks.length) return;
                currentPlaylistId = pl.id;
                currentPlaylistHasCollabs = !!(pl.sharedFrom || (pl.collaborators && pl.collaborators.length > 0));
                MelonPlayerState.setPlaylist(pl.id, 0);
                playlist.length = 0;
                pl.tracks.forEach(function(t) { playlist.push(t); });
                currentTrack = 0;
                updateTrackUI(0);
                updatePlayerTitle(pl.name);
                if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                    ytPlayer.loadVideoById(playlist[0].videoId);
                }
                closeEditor();
            });
            playBtn.title = 'Reproducir';

            var editBtn = makeBtn('☰', 'pl-action-btn', function() { showEditorView(idx); });
            editBtn.title = 'Ver playlist';

            var delBtn;
            if (pl.sharedFrom) {
                delBtn = makeBtn('⊗', 'pl-action-btn', function() {
                    win98Confirm('¿Abandonar la playlist "' + pl.name + '"?', 'Abandonar playlist', function() {
                        fetch('assets/music/api.php?action=leave-playlist', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: pl.id, sharedFrom: pl.sharedFrom })
                        })
                        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                        .then(function(data) {
                            if (data.error) { alert(data.error); return; }
                            allPlaylists.splice(idx, 1);
                            renderHome();
                        })
                        .catch(function(e) { alert('Error: ' + e.message); });
                    });
                });
                delBtn.title = 'Abandonar';
            } else {
                delBtn = makeBtn('✕', 'pl-action-btn', function() {
                    win98Confirm('¿Eliminar la playlist "' + pl.name + '"?', 'Eliminar playlist', function() {
                        fetch('assets/music/api.php?action=delete-playlist', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: pl.id })
                        })
                        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                        .then(function(data) {
                            if (data.error) { alert(data.error); return; }
                            allPlaylists.splice(idx, 1);
                            renderHome();
                        })
                        .catch(function(e) { alert('Error: ' + e.message); });
                    });
                });
                delBtn.title = 'Eliminar';
            }

            btns.appendChild(playBtn);
            btns.appendChild(editBtn);
            btns.appendChild(delBtn);
            var collabsEl = document.createElement('div');
            collabsEl.className = 'pl-home-collabs';
            var collabKeys = [];
            if (pl.sharedFrom) {
                var participants = [pl.sharedFrom].concat(pl.collaborators || []);
                collabKeys = participants.filter(function(k) { return k !== currentUserKey; });
            } else if (pl.collaborators && pl.collaborators.length) {
                collabKeys = pl.collaborators;
            }
            collabKeys.slice(0, 4).forEach(function(cKey) {
                var u = usersInfo[cKey];
                if (!u) return;
                var wrap = document.createElement('div');
                wrap.className = 'collab-avatar-wrap';
                wrap.title = u.label;
                if (u.img) {
                    var img = document.createElement('img');
                    img.className = 'collab-avatar-img';
                    img.alt = u.label;
                    img.src = u.img;
                    wrap.appendChild(img);
                }
                collabsEl.appendChild(wrap);
            });

            row.appendChild(infoEl);
            if (collabKeys.length) row.appendChild(collabsEl);
            row.appendChild(btns);
            plHomeList.appendChild(row);
        });
    }

    window.updatePlaylistPlayingHighlight = function(playlistId, trackIndex) {
        if (!allPlaylists[editingPlIdx] || allPlaylists[editingPlIdx].id !== playlistId) return;
        document.querySelectorAll('#pl-list .pl-item').forEach(function(row, i) {
            row.classList.toggle('pl-item--playing', i === trackIndex);
        });
    };

    function renderList() {
        plList.innerHTML = '';
        var isCurrentPl = allPlaylists[editingPlIdx] && allPlaylists[editingPlIdx].id === currentPlaylistId;
        editList.forEach(function(track, i) {
            var row = document.createElement('div');
            row.className = 'pl-item' + (isCurrentPl && i === currentTrack ? ' pl-item--playing' : '');
            row.draggable = true;
            row.dataset.index = i;

            var handle = document.createElement('div');
            handle.className = 'pl-drag-handle';
            handle.textContent = '⠿';

            var thumbWrap = document.createElement('div');
            thumbWrap.className = 'pl-item-thumb-wrap';
            var thumbImg = document.createElement('img');
            thumbImg.src = track.videoId ? 'https://img.youtube.com/vi/' + track.videoId + '/mqdefault.jpg' : '';
            thumbImg.alt = '';
            thumbImg.width = 44;
            thumbImg.height = 44;
            thumbWrap.appendChild(thumbImg);

            var infoDiv = document.createElement('div');
            infoDiv.className = 'pl-item-info';
            var t1 = document.createElement('div');
            t1.className = 'pl-item-title-text';
            t1.textContent = track.title;
            var t2 = document.createElement('div');
            t2.className = 'pl-item-artist-row';
            var artistSpan = document.createElement('span');
            artistSpan.className = 'pl-item-artist-text';
            artistSpan.textContent = track.artist || '—';
            t2.appendChild(artistSpan);
            if (track.addedBy) {
                var addedBySpan = document.createElement('span');
                addedBySpan.className = 'pl-item-addedby';
                addedBySpan.textContent = track.addedBy;
                t2.appendChild(addedBySpan);
            }
            infoDiv.appendChild(t1);
            infoDiv.appendChild(t2);

            var btnsDiv = document.createElement('div');
            btnsDiv.className = 'pl-item-btns';
            (function(trackIndex) {
                var playTrackBtn = makeBtn('▶', 'pl-action-btn', function() {
                    var pl = allPlaylists[editingPlIdx];
                    if (!pl || !pl.tracks.length) return;
                    currentPlaylistId = pl.id;
                    currentPlaylistHasCollabs = !!(pl.sharedFrom || (pl.collaborators && pl.collaborators.length > 0));
                    MelonPlayerState.setPlaylist(pl.id, trackIndex);
                    playlist.length = 0;
                    pl.tracks.forEach(function(t) { playlist.push(t); });
                    currentTrack = trackIndex;
                    updateTrackUI(trackIndex);
                    updatePlayerTitle(pl.name);
                    if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                        ytPlayer.loadVideoById(playlist[trackIndex].videoId);
                    }
                });
                playTrackBtn.title = 'Reproducir desde aquí';
                btnsDiv.appendChild(playTrackBtn);
            })(i);
            btnsDiv.appendChild(makeBtn('✎', 'pl-action-btn', function() { startEdit(row, infoDiv, btnsDiv, i); }));
            btnsDiv.appendChild(makeBtn('✕', 'pl-action-btn', function() { editList.splice(i, 1); renderList(); }));

            row.addEventListener('dragstart', function(e) {
                dragSrc = i;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(function() { row.classList.add('dragging'); }, 0);
            });
            row.addEventListener('dragend', function() {
                row.classList.remove('dragging');
                plList.querySelectorAll('.pl-item').forEach(function(r) { r.classList.remove('drag-over'); });
            });
            row.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                plList.querySelectorAll('.pl-item').forEach(function(r) { r.classList.remove('drag-over'); });
                row.classList.add('drag-over');
            });
            row.addEventListener('drop', function(e) {
                e.preventDefault();
                if (dragSrc !== null && dragSrc !== i) {
                    var moved = editList.splice(dragSrc, 1)[0];
                    editList.splice(i, 0, moved);
                    renderList();
                }
            });

            row.addEventListener('contextmenu', function(e) { showTrackCtxMenu(e, track); });

            row.appendChild(handle);
            row.appendChild(thumbWrap);
            row.appendChild(infoDiv);
            row.appendChild(btnsDiv);
            plList.appendChild(row);
        });
    }

    /* ──── Context menu: añadir a otra playlist ──── */
    var ctxMenu = document.createElement('div');
    ctxMenu.className = 'window';
    ctxMenu.setAttribute('data-no-auto-z', '');   // popup: z fijo, siempre encima
    ctxMenu.style.cssText = 'display:none;position:fixed;z-index:9999;padding:2px 0;min-width:160px;';
    document.body.appendChild(ctxMenu);

    var addToPicker = document.createElement('div');
    addToPicker.className = 'window';
    addToPicker.setAttribute('data-no-auto-z', '');
    addToPicker.style.cssText = 'display:none;position:fixed;z-index:10000;min-width:190px;';
    addToPicker.innerHTML =
        '<div class="title-bar"><div class="title-bar-text">Añadir a playlist</div>' +
        '<div class="title-bar-controls"><button aria-label="Close" id="add-to-picker-close"></button></div></div>' +
        '<div class="window-body" style="padding:4px;max-height:200px;overflow-y:auto;" id="add-to-picker-list"></div>';
    document.body.appendChild(addToPicker);

    document.getElementById('add-to-picker-close').addEventListener('click', function() {
        addToPicker.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest || (!e.target.closest('#pl-ctx-menu-el') && !e.target.closest('#add-to-picker-close'))) {
            ctxMenu.style.display = 'none';
        }
    });
    ctxMenu.id = 'pl-ctx-menu-el';

    function showAddToPicker(anchorX, anchorY, track) {
        var pickerList = document.getElementById('add-to-picker-list');
        /* Lazy-load de playlists: el editor (loadPlaylists) solo se
           llama cuando el usuario abre el panel de playlists. Si nunca
           lo abrió, allPlaylists está vacío y "Añadir a otra playlist"
           mostraba "no hay otras". Hacemos un fetch en frío aquí y
           rellenamos allPlaylists; las próximas veces ya está caliente. */
        if (!allPlaylists || allPlaylists.length === 0) {
            pickerList.innerHTML = '<div style="font-size:11px;padding:4px;color:#808080;">Cargando…</div>';
            addToPicker.style.display = 'block';
            addToPicker.style.left = Math.min(anchorX, window.innerWidth  - 200) + 'px';
            addToPicker.style.top  = Math.min(anchorY, window.innerHeight - 100) + 'px';
            fetch('assets/music/api.php?action=get-playlists')
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (!data || data.error) {
                        pickerList.innerHTML = '<div style="font-size:11px;padding:4px;color:#808080;">Error cargando playlists.</div>';
                        return;
                    }
                    allPlaylists = data;
                    /* Re-pinta con datos frescos. */
                    renderAddToPicker(track, pickerList);
                })
                .catch(function(){
                    pickerList.innerHTML = '<div style="font-size:11px;padding:4px;color:#808080;">Error de red.</div>';
                });
            return;
        }
        renderAddToPicker(track, pickerList);
        addToPicker.style.display = 'block';
        var pw = addToPicker.offsetWidth, ph = addToPicker.offsetHeight;
        addToPicker.style.left = Math.min(anchorX, window.innerWidth  - pw - 8) + 'px';
        addToPicker.style.top  = Math.min(anchorY, window.innerHeight - ph - 8) + 'px';
    }
    /* Helper extraído — pinta las playlists destino en el picker. Reusable
       tras un lazy-fetch o si ya estaban cargadas. */
    function renderAddToPicker(track, pickerList) {
        pickerList.innerHTML = '';
        var currentId = editingPlIdx >= 0 && allPlaylists[editingPlIdx] ? allPlaylists[editingPlIdx].id : null;
        var targets = allPlaylists.filter(function(pl) { return pl.id !== currentId; });

        if (!targets.length) {
            pickerList.innerHTML = '<div style="font-size:11px;padding:4px;color:#808080;">No hay otras playlists propias.</div>';
        } else {
            targets.forEach(function(pl) {
                var item = document.createElement('div');
                item.className = 'pl-menu-item';
                item.textContent = pl.name;
                item.addEventListener('click', function() {
                    addToPicker.style.display = 'none';
                    var already = pl.tracks.some(function(t) { return t.videoId === track.videoId; });
                    if (!already) pl.tracks.push(Object.assign({}, track));
                    fetch('assets/music/api.php?action=save-playlist-item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: pl.id, name: pl.name, tracks: pl.tracks })
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        if (data.error) alert(data.error);
                    }).catch(function() {});
                });
                pickerList.appendChild(item);
            });
        }
        /* No reposicionamos aquí — el caller (showAddToPicker o el
           callback del fetch) ya dejó el picker visible y anclado. */
    }

    function showTrackCtxMenu(e, track) {
        e.preventDefault();
        ctxMenu.innerHTML = '';
        var ex = e.clientX, ey = e.clientY;

        var addPl = document.createElement('div');
        addPl.className = 'pl-menu-item';
        addPl.textContent = '➕ Añadir a otra playlist';
        addPl.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            showAddToPicker(ex, ey, track);
        });
        ctxMenu.appendChild(addPl);

        var addProfile = document.createElement('div');
        addProfile.className = 'pl-menu-item';
        addProfile.textContent = '🎵 Añadir a mi perfil';
        addProfile.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            /* Cierra el fullscreen llamando a la función real (expuesta
               como window.__closeFullscreenPlayer) en vez de simular
               click() en el botón restore. El click() simulado a veces
               no se propagaba bien, dejando el flag isOpen en true →
               el siguiente click en maximize hacía return temprano y
               necesitabas dos clicks. */
            if (typeof window.__closeFullscreenPlayer === 'function') {
                window.__closeFullscreenPlayer();
            }
            if (typeof window.profileAddTrackAndReview === 'function') {
                window.profileAddTrackAndReview(track);
            }
        });
        ctxMenu.appendChild(addProfile);

        /* Item Listen Together — abre el modal de invitar (o gestiona
           la sesión actual si ya estás en una). */
        var ltItem = document.createElement('div');
        ltItem.className = 'pl-menu-item';
        if (typeof LT_ROLE !== 'undefined' && LT_ROLE === 'host') {
            ltItem.textContent = '🎧 Invitar a más usuarios…';
        } else if (typeof LT_ROLE !== 'undefined' && LT_ROLE === 'guest') {
            ltItem.textContent = '🎧 Ver sesión actual';
        } else {
            ltItem.textContent = '🎧 Escuchar juntos…';
        }
        ltItem.addEventListener('click', function() {
            ctxMenu.style.display = 'none';
            if (typeof ltOpenModal === 'function') ltOpenModal();
        });
        ctxMenu.appendChild(ltItem);

        /* Item de salir, solo si estás en una sesión. */
        if (typeof LT_ROLE !== 'undefined' && LT_ROLE) {
            var ltLeave = document.createElement('div');
            ltLeave.className = 'pl-menu-item';
            ltLeave.textContent = '✕ Salir de la sesión';
            ltLeave.addEventListener('click', function() {
                ctxMenu.style.display = 'none';
                if (typeof window.ltLeave === 'function') window.ltLeave();
            });
            ctxMenu.appendChild(ltLeave);
        }

        ctxMenu.style.display = 'block';
        var cw = ctxMenu.offsetWidth, ch = ctxMenu.offsetHeight;
        ctxMenu.style.left = Math.min(ex, window.innerWidth  - cw - 8) + 'px';
        ctxMenu.style.top  = Math.min(ey, window.innerHeight - ch - 8) + 'px';
    }
    /* Exposición global → permite triggear el mismo menú desde el
       mini-player (no solo desde los rows de la lista). */
    window.openTrackCtxMenu = showTrackCtxMenu;

    function startEdit(row, infoDiv, btnsDiv, i) {
        var track = editList[i];
        var wrap  = document.createElement('div');
        wrap.className = 'pl-item-edit-inputs';
        var ti = document.createElement('input');
        ti.type = 'text'; ti.value = track.title;
        var ai = document.createElement('input');
        ai.type = 'text'; ai.value = track.artist || '';
        wrap.appendChild(ti); wrap.appendChild(ai);
        row.replaceChild(wrap, infoDiv);

        btnsDiv.innerHTML = '';
        btnsDiv.appendChild(makeBtn('✓', 'pl-action-btn', function() {
            editList[i].title  = ti.value.trim() || editList[i].title;
            editList[i].artist = ai.value.trim();
            renderList();
        }));
        btnsDiv.appendChild(makeBtn('✕', 'pl-action-btn', renderList));
        ti.focus(); ti.select();
    }

    function makeBtn(label, cls, fn) {
        var b = document.createElement('button');
        b.className = 'button ' + cls;
        b.textContent = label;
        b.addEventListener('click', fn);
        return b;
    }

    function saveCurrentPlaylist(onSuccess) {
        if (editingPlIdx < 0) return;
        var pl = allPlaylists[editingPlIdx];
        var newName = plNameInput.value.trim();
        if (newName) pl.name = newName;
        pl.tracks = editList.slice();
        var savePayload = { id: pl.id, name: pl.name, tracks: pl.tracks };
        if (pl.sharedFrom) savePayload.sharedFrom = pl.sharedFrom;
        fetch('assets/music/api.php?action=save-playlist-item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(savePayload)
        })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(data) {
            if (data.error) { alert(data.error); return; }
            if (onSuccess) onSuccess();
        })
        .catch(function(e) { alert('Error al guardar: ' + e.message); });
    }

    document.getElementById('btn-edit-playlist').addEventListener('click', openEditor);
    document.getElementById('pl-close').addEventListener('click', closeEditor);
    document.getElementById('pl-back').addEventListener('click', showHome);

    document.getElementById('pl-add').addEventListener('click', function() {
        addTrackCallback = function(track) { editList.push(track); renderList(); };
        openAddDialog();
    });

    document.getElementById('pl-save').addEventListener('click', function() {
        saveCurrentPlaylist(function() {
            var pl = allPlaylists[editingPlIdx];
            if (pl.id === currentPlaylistId && pl.tracks.length > 0) {
                playlist.length = 0;
                pl.tracks.forEach(function(t) { playlist.push(t); });
                currentTrack = 0;
                updateTrackUI(0);
                updatePlayerTitle(pl.name);
                if (ytPlayer && typeof ytPlayer.loadVideoById === 'function') {
                    ytPlayer.loadVideoById(playlist[0].videoId);
                }
            }
            showHome();
        });
    });

    /* Create playlist dialog */
    (function() {
        var createDlg    = document.getElementById('create-playlist-dialog');
        var createInput  = document.getElementById('create-pl-name');
        var createSubmit = document.getElementById('create-pl-submit');

        function openCreateDlg() {
            createInput.value = '';
            createDlg.style.display = 'block';
            setTimeout(function() { createInput.focus(); createInput.select(); }, 50);
        }
        function closeCreateDlg() { createDlg.style.display = 'none'; }

        document.getElementById('pl-create').addEventListener('click', openCreateDlg);
        document.getElementById('create-pl-close').addEventListener('click', closeCreateDlg);
        document.getElementById('create-pl-cancel').addEventListener('click', closeCreateDlg);

        function doCreate() {
            var name = createInput.value.trim();
            if (!name) return;
            closeCreateDlg();
            var newPl = { id: 'pl_' + Date.now(), name: name, tracks: [], collaborators: [] };
            fetch('assets/music/api.php?action=save-playlist-item', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newPl)
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                if (data.error) { alert(data.error); return; }
                /* El backend asigna el ID definitivo (BIGINT) → reemplazar
                   el placeholder pl_<timestamp> antes de meterlo en memoria
                   para que los próximos save/delete usen el ID real. */
                if (data.id) newPl.id = data.id;
                allPlaylists.push(newPl);
                showEditorView(allPlaylists.length - 1);
            })
            .catch(function(e) { alert('Error: ' + e.message); });
        }

        createSubmit.addEventListener('click', doCreate);
        createInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') doCreate(); });
    })();

    /* ... menu + import playlist */
    (function() {
        var moreBtn    = document.getElementById('pl-more');
        var moreMenu   = document.getElementById('pl-more-menu');
        var importDlg  = document.getElementById('import-playlist-dialog');
        var importUrl  = document.getElementById('import-pl-url');
        var importStat = document.getElementById('import-pl-status');
        var importBtn  = document.getElementById('import-pl-submit');

        moreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            moreMenu.style.display = moreMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function() { moreMenu.style.display = 'none'; });

        document.getElementById('pl-more-import').addEventListener('click', function() {
            moreMenu.style.display = 'none';
            importUrl.value = '';
            importStat.textContent = '';
            importBtn.style.pointerEvents = ''; importBtn.classList.remove('btn-busy');
            importDlg.style.display = 'block';
        });

        function closeImport() { importDlg.style.display = 'none'; }
        document.getElementById('import-pl-close').addEventListener('click', closeImport);
        document.getElementById('import-pl-cancel').addEventListener('click', closeImport);

        importBtn.addEventListener('click', function() {
            var url = importUrl.value.trim();
            if (!url) return;
            importStat.textContent = 'Importando...';
            importBtn.style.pointerEvents = 'none'; importBtn.classList.add('btn-busy');
            fetch('assets/music/api.php?action=import-playlist', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url })
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                importBtn.style.pointerEvents = ''; importBtn.classList.remove('btn-busy');
                if (data.error) { importStat.textContent = 'Error: ' + data.error; return; }
                var importer = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
                data.tracks.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                renderList();
                importStat.textContent = '';
                closeImport();
            })
            .catch(function(e) {
                importBtn.style.pointerEvents = ''; importBtn.classList.remove('btn-busy');
                importStat.textContent = 'Error: ' + e.message;
            });
        });

        /* Spotify CSV import */
        var spotifyDlg  = document.getElementById('spotify-import-dialog');
        var spotifyFile = document.getElementById('spotify-import-file');
        var spotifyStat = document.getElementById('spotify-import-status');
        var spotifyBtn  = document.getElementById('spotify-import-submit');

        function openSpotifyDialog() {
            moreMenu.style.display = 'none';
            spotifyFile.value = '';
            spotifyFileName.value = '';
            spotifyStat.textContent = '';
            spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
            spotifyProgressWrap.style.display = 'none';
            spotifyProgressFill.style.width = '0%';
            spotifyDlg.style.display = 'block';
        }

        document.getElementById('pl-more-spotify').addEventListener('click', openSpotifyDialog);

        var spotifyFileName = document.getElementById('spotify-file-name');
        document.getElementById('spotify-file-browse').addEventListener('click', function() { spotifyFile.click(); });
        spotifyFile.addEventListener('change', function() {
            spotifyFileName.value = spotifyFile.files[0] ? spotifyFile.files[0].name : '';
        });

        var spotifyAbortCtrl = null;

        function closeSpotifyImport() {
            if (spotifyAbortCtrl) { spotifyAbortCtrl.abort(); spotifyAbortCtrl = null; }
            spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
            spotifyProgressWrap.style.display = 'none';
            spotifyDlg.style.display = 'none';
        }
        document.getElementById('spotify-import-close').addEventListener('click', closeSpotifyImport);
        document.getElementById('spotify-import-cancel').addEventListener('click', closeSpotifyImport);

        function parseExportifyCSV(raw) {
            /* Parse RFC-4180-ish CSV properly (handles quoted fields with commas) */
            function parseLine(line) {
                var cols = [], cur = '', inQ = false;
                for (var i = 0; i < line.length; i++) {
                    var c = line[i];
                    if (c === '"') { inQ = !inQ; }
                    else if (c === ',' && !inQ) { cols.push(cur); cur = ''; }
                    else { cur += c; }
                }
                cols.push(cur);
                return cols;
            }
            var lines  = raw.split('\n');
            var header = parseLine(lines[0]);
            var tiIdx  = header.indexOf('Track Name');
            var arIdx  = header.indexOf('Artist Name(s)');
            if (tiIdx === -1) return [];
            var tracks = [];
            for (var i = 1; i < lines.length; i++) {
                if (!lines[i].trim()) continue;
                var cols   = parseLine(lines[i]);
                var title  = (cols[tiIdx] || '').trim();
                var artist = arIdx !== -1 ? (cols[arIdx] || '').trim() : '';
                if (title) tracks.push({ title: title, artist: artist, duration: 0 });
            }
            return tracks;
        }

        var spotifyProgressWrap = document.getElementById('spotify-progress-wrap');
        var spotifyProgressFill = document.getElementById('spotify-progress-fill');
        var spotifyProgressText = document.getElementById('spotify-progress-text');

        spotifyBtn.addEventListener('click', function() {
            var file = spotifyFile.files[0];
            if (!file) { spotifyStat.textContent = 'Selecciona un archivo CSV.'; return; }
            var reader = new FileReader();
            reader.onload = function(e) {
                var tracks = parseExportifyCSV(e.target.result);
                reader.onload = null;
                if (!tracks.length) { spotifyStat.textContent = 'No se encontraron canciones en el archivo.'; return; }

                spotifyAbortCtrl = new AbortController();
                var signal = spotifyAbortCtrl.signal;

                spotifyBtn.style.pointerEvents = 'none'; spotifyBtn.classList.add('btn-busy');
                spotifyStat.textContent = '';
                spotifyProgressWrap.style.display = 'block';
                spotifyProgressFill.style.width = '0%';
                spotifyProgressText.textContent = '0 encontradas · ' + tracks.length + ' restantes';

                var BATCH   = 10;
                var total   = tracks.length;
                var found   = 0;
                var done    = 0;
                var results = [];

                function nextBatch(offset) {
                    if (signal.aborted || offset >= total) return Promise.resolve();
                    var chunk = tracks.slice(offset, offset + BATCH);
                    return fetch('assets/music/api.php?action=yt-search-batch', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body:    JSON.stringify({ tracks: chunk }),
                        signal:  signal
                    })
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function(data) {
                        if (data.tracks) {
                            results = results.concat(data.tracks);
                            found  += data.tracks.length;
                        }
                        done = Math.min(offset + BATCH, total);
                        var pct = Math.round(done / total * 100);
                        spotifyProgressFill.style.width = pct + '%';
                        spotifyProgressText.textContent = found + ' encontradas · ' + (total - done) + ' restantes';
                        return nextBatch(offset + BATCH);
                    });
                }

                nextBatch(0)
                .then(function() {
                    if (signal.aborted) return;
                    spotifyAbortCtrl = null;
                    spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
                    spotifyProgressWrap.style.display = 'none';
                    if (!results.length) { spotifyStat.textContent = 'No se encontraron vídeos en YouTube.'; return; }
                    var importer = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
                    results.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                    renderList();
                    closeSpotifyImport();
                })
                .catch(function(e) {
                    if (e.name === 'AbortError') return;
                    spotifyAbortCtrl = null;
                    spotifyBtn.style.pointerEvents = ''; spotifyBtn.classList.remove('btn-busy');
                    spotifyProgressWrap.style.display = 'none';
                    spotifyStat.textContent = 'Error: ' + e.message;
                });
            };
            reader.readAsText(file);
        });

        /* ─── Importar de Tidal (API oficial → resolver a YouTube) ─── */
        var tidalDlg  = document.getElementById('tidal-import-dialog');
        var tidalUrl  = document.getElementById('tidal-import-url');
        var tidalStat = document.getElementById('tidal-import-status');
        var tidalBtn  = document.getElementById('tidal-import-submit');
        var tidalWrap = document.getElementById('tidal-progress-wrap');
        var tidalFill = document.getElementById('tidal-progress-fill');
        var tidalText = document.getElementById('tidal-progress-text');
        var tidalAbort = null;

        function tidalBusy(on) {
            tidalBtn.style.pointerEvents = on ? 'none' : '';
            tidalBtn.classList.toggle('btn-busy', on);
        }
        function openTidalDialog() {
            moreMenu.style.display = 'none';
            tidalUrl.value = '';
            tidalStat.textContent = ''; tidalStat.style.color = '';
            tidalWrap.style.display = 'none';
            tidalFill.style.width = '0%';
            tidalBusy(false);
            tidalDlg.style.display = 'block';
        }
        document.getElementById('pl-more-tidal').addEventListener('click', openTidalDialog);

        function closeTidalImport() {
            if (tidalAbort) { tidalAbort.abort(); tidalAbort = null; }
            tidalBusy(false);
            tidalWrap.style.display = 'none';
            tidalDlg.style.display = 'none';
        }
        document.getElementById('tidal-import-close').addEventListener('click', closeTidalImport);
        document.getElementById('tidal-import-cancel').addEventListener('click', closeTidalImport);

        /* Resuelve [{title,artist,duration}] a YouTube por lotes con progreso. */
        function tidalResolveBatches(tracks, signal) {
            var BATCH = 10, total = tracks.length, found = 0, results = [];
            tidalWrap.style.display = 'block';
            tidalFill.style.width = '0%';
            tidalText.textContent = '0 encontradas · ' + total + ' restantes';
            function nextBatch(offset) {
                if (signal.aborted || offset >= total) return Promise.resolve();
                var chunk = tracks.slice(offset, offset + BATCH);
                return fetch('assets/music/api.php?action=yt-search-batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tracks: chunk }),
                    signal: signal
                })
                .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(function(data) {
                    if (data.tracks) { results = results.concat(data.tracks); found += data.tracks.length; }
                    var done = Math.min(offset + BATCH, total);
                    tidalFill.style.width = Math.round(done / total * 100) + '%';
                    tidalText.textContent = found + ' encontradas · ' + (total - done) + ' restantes';
                    return nextBatch(offset + BATCH);
                });
            }
            return nextBatch(0).then(function() { return results; });
        }

        document.getElementById('tidal-import-submit').addEventListener('click', function() {
            var url = tidalUrl.value.trim();
            if (!url) { tidalStat.textContent = 'Pega un enlace de Tidal.'; return; }
            tidalStat.style.color = '';
            var importer   = usersInfo[currentUserKey] ? usersInfo[currentUserKey].label : '';
            var isTrack    = /tidal\.com\/(?:browse\/)?track\/\d+/i.test(url);
            var isPlaylist = /tidal\.com\/(?:browse\/)?playlist\/[0-9a-f-]{8,}/i.test(url);

            /* Canción suelta */
            if (isTrack && !isPlaylist) {
                tidalBusy(true);
                tidalStat.textContent = 'Buscando en Tidal…';
                fetch('assets/music/api.php?action=tidal-track&url=' + encodeURIComponent(url))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    tidalBusy(false);
                    if (data.error) { tidalStat.textContent = data.error; tidalStat.style.color = '#c00'; return; }
                    editList.push({ videoId: data.videoId, title: data.title, artist: data.artist, duration: data.duration, addedBy: importer });
                    renderList();
                    closeTidalImport();
                })
                .catch(function() { tidalBusy(false); tidalStat.textContent = 'Error de conexión'; tidalStat.style.color = '#c00'; });
                return;
            }

            if (!isPlaylist) { tidalStat.textContent = 'Enlace no reconocido (canción o playlist de Tidal).'; tidalStat.style.color = '#c00'; return; }

            /* Playlist: leer de Tidal y resolver a YouTube por lotes */
            tidalBusy(true);
            tidalStat.textContent = 'Leyendo playlist de Tidal…';
            tidalAbort = new AbortController();
            var signal = tidalAbort.signal;
            fetch('assets/music/api.php?action=tidal-playlist', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ url: url }),
                signal: signal
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                if (data.error) throw new Error(data.error);
                var plName = data.name || '';
                var tracks = data.tracks || [];
                if (!tracks.length) throw new Error('La playlist no tiene canciones.');
                tidalStat.textContent = '';
                return tidalResolveBatches(tracks, signal).then(function(results) {
                    if (signal.aborted) return;
                    tidalAbort = null;
                    tidalBusy(false);
                    tidalWrap.style.display = 'none';
                    if (!results.length) { tidalStat.textContent = 'No se encontraron vídeos en YouTube.'; tidalStat.style.color = '#c00'; return; }
                    var nameInput = document.getElementById('pl-name-input');
                    if (nameInput && !nameInput.value.trim() && plName) nameInput.value = plName;
                    results.forEach(function(t) { editList.push(Object.assign({}, t, { addedBy: importer })); });
                    renderList();
                    closeTidalImport();
                });
            })
            .catch(function(e) {
                if (e.name === 'AbortError') return;
                tidalAbort = null;
                tidalBusy(false);
                tidalWrap.style.display = 'none';
                tidalStat.textContent = 'Error: ' + e.message;
                tidalStat.style.color = '#c00';
            });
        });

        /* Colaborador */
        var collabDlg      = document.getElementById('collab-dialog');
        var collabPlName   = document.getElementById('collab-pl-name');
        var collabUserList = document.getElementById('collab-user-list');
        var collabStatus   = document.getElementById('collab-status');

        function openCollabDialog() {
            moreMenu.style.display = 'none';
            if (editingPlIdx < 0) return;
            var pl = allPlaylists[editingPlIdx];
            if (pl.sharedFrom) { alert('Solo el propietario puede gestionar colaboradores.'); return; }
            collabPlName.textContent = pl.name;
            collabStatus.textContent = '';
            collabUserList.innerHTML = '<div style="font-size:11px;color:#808080;">Cargando...</div>';
            collabDlg.style.display = 'block';
            fetch('assets/music/api.php?action=get-users')
            .then(function(r) { return r.json(); })
            .then(function(users) {
                collabUserList.innerHTML = '';
                if (!Array.isArray(users) || !users.length) {
                    collabUserList.innerHTML = '<div class="pl-home-msg" style="text-align:center;line-height:1.45;">Aún no tienes amigos que invitar.<br><span style="opacity:0.75;font-size:11px;">Seguíos entre vosotros para haceros amigos.</span></div>';
                    return;
                }
                users.forEach(function(u) {
                    var already = Array.isArray(pl.collaborators) && pl.collaborators.indexOf(u.key) >= 0;
                    var row = document.createElement('div');
                    row.className = 'collab-user-row';
                    var avatarWrap = document.createElement('div');
                    avatarWrap.className = 'collab-avatar-wrap';
                    var uInfo = usersInfo[u.key];
                    if (uInfo && uInfo.img) {
                        var avatarImg = document.createElement('img');
                        avatarImg.className = 'collab-avatar-img';
                        avatarImg.src = uInfo.img;
                        avatarImg.alt = u.label;
                        avatarWrap.appendChild(avatarImg);
                    }
                    var nameSpan = document.createElement('span');
                    nameSpan.textContent = u.label;

                    if (already) {
                        var removeBtn = makeBtn('Eliminar', 'collab-invite-btn', function() {
                            collabStatus.textContent = 'Eliminando...';
                            removeBtn.disabled = true;
                            fetch('assets/music/api.php?action=remove-collaborator', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ playlistId: pl.id, collaborator: u.key })
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.error) { collabStatus.textContent = data.error; removeBtn.disabled = false; return; }
                                if (pl.collaborators) {
                                    pl.collaborators = pl.collaborators.filter(function(c) { return c !== u.key; });
                                }
                                collabStatus.textContent = u.label + ' eliminado de la playlist.';
                                row.remove();
                            })
                            .catch(function(e) { collabStatus.textContent = 'Error: ' + e.message; removeBtn.disabled = false; });
                        });
                        row.appendChild(avatarWrap);
                        row.appendChild(nameSpan);
                        row.appendChild(removeBtn);
                    } else {
                        var invBtn = makeBtn('Invitar', 'collab-invite-btn', function() {
                            collabStatus.textContent = 'Enviando...';
                            invBtn.disabled = true;
                            fetch('assets/music/api.php?action=invite-collaborator', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ playlistId: pl.id, toUser: u.key })
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.error) { collabStatus.textContent = data.error; invBtn.disabled = false; return; }
                                invBtn.textContent = 'Invitado';
                                invBtn.disabled = true;
                                collabStatus.textContent = 'Invitación enviada a ' + u.label;
                            })
                            .catch(function(e) { collabStatus.textContent = 'Error: ' + e.message; invBtn.disabled = false; });
                        });
                        row.appendChild(avatarWrap);
                        row.appendChild(nameSpan);
                        row.appendChild(invBtn);
                    }
                    collabUserList.appendChild(row);
                });
            })
            .catch(function(e) {
                collabUserList.innerHTML = '<div style="font-size:11px;color:#c00;">Error: ' + e.message + '</div>';
            });
        }

        document.getElementById('pl-more-collab').addEventListener('click', openCollabDialog);

        document.getElementById('collab-close').addEventListener('click', function() { collabDlg.style.display = 'none'; });
        document.getElementById('collab-cancel').addEventListener('click', function() { collabDlg.style.display = 'none'; });
    })();

    /* Drag window */
    (function() {
        var tb = document.getElementById('pl-titlebar');
        var dragging = false, ox, oy;
        tb.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'BUTTON') return;
            dragging = true;
            var r = editor.getBoundingClientRect();
            editor.style.left = r.left + 'px'; editor.style.top = r.top + 'px';
            editor.style.transform = 'none';
            ox = e.clientX - r.left; oy = e.clientY - r.top;
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            editor.style.left = (e.clientX - ox) + 'px';
            editor.style.top  = (e.clientY - oy) + 'px';
        });
        document.addEventListener('mouseup', function() { dragging = false; });
    })();

    refreshPlaylists = loadPlaylists;

    setInterval(function() {
        if (editor.style.display !== 'block') return;
        fetch('assets/music/api.php?action=get-playlists')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!Array.isArray(data) || data.error) return;
            allPlaylists = data;
            if (plHome.style.display !== 'none') renderHome();
        })
        .catch(function() {});
    }, 15000);
})();

function relTime(sentAt) {
    if (!sentAt) return '';
    var diff = Math.floor(Date.now() / 1000) - sentAt;
    if (diff < 60)   return 'ahora mismo';
    if (diff < 3600) return 'hace ' + Math.floor(diff / 60) + ' min';
    return 'hace ' + Math.floor(diff / 3600) + 'h';
}

/* Notificaciones del reproductor (playlist invites / collab) */
(function() {
    function serverPost(id, action) {
        return fetch('assets/music/api.php?action=respond-invite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inviteId: id, action: action })
        });
    }

    var TITLES = {
        'removed':          'Eliminado de playlist',
        'collab-accepted':  'Colaboración aceptada',
        'collab-rejected':  'Colaboración rechazada',
        'collab-left':      'Colaborador ha salido'
    };

    function messageFor(inv) {
        switch (inv.type) {
            case 'removed':         return inv.fromLabel + ' te ha eliminado de "' + inv.playlistName + '"';
            case 'collab-accepted': return inv.fromLabel + ' ha aceptado tu solicitud de colaboración en "' + inv.playlistName + '"';
            case 'collab-rejected': return inv.fromLabel + ' ha rechazado tu solicitud de colaboración en "' + inv.playlistName + '"';
            case 'collab-left':     return inv.fromLabel + ' ha abandonado tu playlist "' + inv.playlistName + '"';
            default:                return inv.fromLabel + ' te invita a colaborar en "' + inv.playlistName + '"';
        }
    }

    function pushInvite(inv) {
        if (!window.notifSystem || window.notifSystem.isShown(inv.id) || window.notifSystem.isDismissed(inv.id)) return;
        var INFO_TYPES = { 'removed': true, 'collab-accepted': true, 'collab-rejected': true, 'collab-left': true };
        var isAction = !INFO_TYPES[inv.type];
        var senderImg = (typeof usersInfo !== 'undefined' && inv.fromUser && usersInfo[inv.fromUser]) ? usersInfo[inv.fromUser].img : null;

        if (isAction) {
            window.notifSystem.show({
                id:       inv.id,
                type:     'action',
                title:    TITLES[inv.type] || 'Invitación de playlist',
                message:  messageFor(inv),
                senderImage: senderImg,
                sentAt:   inv.sentAt,
                onAccept: function() {
                    serverPost(inv.id, 'accept')
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d && d.error) { alert(d.error); return; }
                            if (typeof refreshPlaylists === 'function') refreshPlaylists();
                        })
                        .catch(function() {});
                },
                onReject: function() { serverPost(inv.id, 'reject').catch(function(){}); }
            });
        } else {
            window.notifSystem.show({
                id:      inv.id,
                type:    'info',
                title:   TITLES[inv.type] || 'Notificación',
                message: messageFor(inv),
                sentAt:  inv.sentAt,
                onAutoDismiss: function() { serverPost(inv.id, 'dismiss').catch(function(){}); }
            });
        }
    }

    function connectSSE() {
        var es = new EventSource('assets/music/notifications-stream.php');
        es.onmessage = function(e) {
            try {
                var data = JSON.parse(e.data);
                if (!Array.isArray(data)) return;
                data.sort(function(a, b) { return (a.sentAt || 0) - (b.sentAt || 0); });
                data.forEach(pushInvite);
            } catch(err) {}
        };
        es.onerror = function() { es.close(); setTimeout(connectSSE, 3000); };
    }

    connectSSE();
})();

/* ════════════════════════════════════════════════════════════════
   LETRAS (LRCLIB) — botón + panel + sincronización con ytPlayer.
   Estado:
     LYR_CURRENT_VID  → videoId de la canción cuya letra está cargada.
     LYR_LINES        → array { time:number, text:string } si synced.
     LYR_PLAIN        → string plana si no hay synced.
     LYR_LAST_ACTIVE  → índice del último .lyrics-line activo (evita
                        re-querys cada tick si no cambió).
     LYR_OPEN         → true cuando la ventana está visible.
     LYR_SYNC_TIMER   → setInterval que actualiza el highlight.
   ════════════════════════════════════════════════════════════════ */
(function lyricsModule() {
    let LYR_CURRENT_VID = null;
    let LYR_LINES       = null;
    let LYR_PLAIN       = null;
    let LYR_LAST_ACTIVE = -1;
    let LYR_OPEN        = false;
    let LYR_SYNC_TIMER  = null;

    const win   = document.getElementById('lyrics-window');
    const btn   = document.getElementById('btn-lyrics');
    const close = document.getElementById('lyrics-close');
    const linesEl  = document.getElementById('lyrics-lines');
    const emptyEl  = document.getElementById('lyrics-empty');
    const trackEl  = document.getElementById('lyrics-track');
    const statusEl = document.getElementById('lyrics-status');
    const scrollEl = document.getElementById('lyrics-scroll');

    /* Drag + resize vía WindowManager del desktop. Si aún no existe
       (reproductor partial cargado antes que WindowManager) reintenta
       hasta que esté listo. setup(id, false) → drag por title-bar +
       resize por las 8 esquinas/lados. */
    var __lyricsWmTries = 0;
    function setupLyricsWindowManager() {
        if (window.WindowManager && typeof window.WindowManager.setup === 'function') {
            window.WindowManager.setup('lyrics-window', false);
            return;
        }
        if (__lyricsWmTries++ < 30) setTimeout(setupLyricsWindowManager, 200);
    }
    setupLyricsWindowManager();

    /* Minimize / Maximize / Close: wire UI controls.
         - Minimize → taskbarManager.minimize (oculta + queda en la taskbar).
         - Maximize → toggle .win-maximized (full-viewport CSS de base.css).
         - Close   → ocultar + unregister + parar sync. */
    var minBtn = document.getElementById('lyrics-min');
    var maxBtn = document.getElementById('lyrics-max');
    if (minBtn) minBtn.addEventListener('click', function() {
        if (window.taskbarManager) window.taskbarManager.minimize('lyrics-window');
    });
    if (maxBtn) maxBtn.addEventListener('click', function() {
        if (win.classList.contains('win-maximized')) {
            win.classList.remove('win-maximized');
            maxBtn.setAttribute('aria-label', 'Maximize');
        } else {
            win.classList.add('win-maximized');
            maxBtn.setAttribute('aria-label', 'Restore');
        }
    });

    function open() {
        win.style.display = 'flex';
        LYR_OPEN = true;
        /* Registra en la taskbar (o restaura si estaba minimizada). */
        if (window.taskbarManager) {
            if (window.taskbarManager.isRegistered('lyrics-window')) {
                window.taskbarManager.restore('lyrics-window');
            } else {
                window.taskbarManager.register('lyrics-window', 'Letra', '🎤', 'flex');
            }
        }
        fetchForCurrent();
        startSync();
    }
    function close_() {
        win.style.display = 'none';
        LYR_OPEN = false;
        if (window.taskbarManager && window.taskbarManager.isRegistered('lyrics-window')) {
            window.taskbarManager.unregister('lyrics-window');
        }
        stopSync();
    }
    if (btn)   btn.addEventListener('click', open);
    if (close) close.addEventListener('click', close_);

    /* Parser LRC: cada línea "[mm:ss.xx]texto". Soporta multi-timestamp
       por línea ("[01:00.50][02:30.20]coro"). */
    function parseLRC(lrc) {
        if (!lrc) return [];
        const lines = [];
        lrc.split(/\r?\n/).forEach(raw => {
            const stamps = [];
            let rest = raw;
            const re = /^\s*\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\]/;
            let m;
            while ((m = re.exec(rest))) {
                const min = +m[1], sec = +m[2], ms = +(m[3] || 0);
                stamps.push(min * 60 + sec + ms / Math.pow(10, (m[3] || '').length));
                rest = rest.slice(m[0].length);
            }
            const text = rest.trim();
            if (stamps.length && text) {
                stamps.forEach(t => lines.push({ time: t, text }));
            }
        });
        lines.sort((a, b) => a.time - b.time);
        return lines;
    }

    function getCurrentTrack() {
        if (typeof playlist !== 'undefined' && playlist.length && typeof currentTrack === 'number') {
            return playlist[currentTrack];
        }
        return null;
    }

    function setStatus(s) { if (statusEl) statusEl.textContent = s || ''; }

    /* Controller del fetch activo del reproductor pequeño — se aborta
       al cambiar de canción para no sobreescribir el estado nuevo con
       respuestas viejas. */
    let LYR_SMALL_FETCH_CTRL = null;

    async function fetchForCurrent() {
        const tr = getCurrentTrack();
        if (!tr || !tr.videoId) {
            renderEmpty('No hay canción en reproducción.');
            return;
        }
        /* Si ya tenemos la letra de esta canción, no re-fetchear. */
        if (tr.videoId === LYR_CURRENT_VID && (LYR_LINES || LYR_PLAIN)) return;
        /* Cancela el fetch previo si sigue en vuelo — su respuesta
           sería para otra canción y no debe pisar el estado nuevo. */
        if (LYR_SMALL_FETCH_CTRL) {
            try { LYR_SMALL_FETCH_CTRL.abort(); } catch (_) {}
        }
        const ctrl = new AbortController();
        LYR_SMALL_FETCH_CTRL = ctrl;
        LYR_CURRENT_VID = tr.videoId;
        LYR_LINES = null;
        LYR_PLAIN = null;
        LYR_LAST_ACTIVE = -1;
        trackEl.textContent = (tr.title || '') + (tr.artist ? ' — ' + tr.artist : '');
        renderEmpty('Buscando letra…');
        setStatus('');
        let dur = 0;
        try { dur = (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getDuration) ? Math.floor(ytPlayer.getDuration() || 0) : 0; } catch (_) {}
        const qs = new URLSearchParams({
            title: tr.title || '',
            artist: tr.artist || '',
            duration: String(dur),
        });
        try {
            const r = await fetch('assets/music/api.php?action=get-lyrics&' + qs.toString(), {
                credentials: 'same-origin',
                signal: ctrl.signal,
            });
            const d = await r.json();
            /* GUARD STALE — antes de renderizar NADA. Si el track cambió
               mientras esperábamos, este response es para una canción
               vieja: no debe pisar el "Buscando letra…" del track nuevo
               con un "🥲 No se encontró". */
            if (ctrl.signal.aborted) return;
            if (LYR_CURRENT_VID !== tr.videoId) return;
            if (!d || !d.ok || !d.found) {
                renderEmpty('🥲 No se encontró letra para esta canción.');
                return;
            }
            if (d.synced) {
                LYR_LINES = parseLRC(d.synced);
                if (LYR_LINES.length) renderSynced();
                else { LYR_PLAIN = d.plain; renderPlain(); }
            } else if (d.plain) {
                LYR_PLAIN = d.plain;
                renderPlain();
            } else {
                renderEmpty('🥲 No se encontró letra para esta canción.');
            }
            if (d.matched) {
                setStatus(LYR_LINES ? '⏱ sincronizada' : 'sin sync');
            }
        } catch (e) {
            /* Abort no es error, es señal de track-change. */
            if (e && e.name === 'AbortError') return;
            /* Solo render error si seguimos en el track que pidió. */
            if (LYR_CURRENT_VID === tr.videoId) renderEmpty('Error de red al buscar la letra.');
        } finally {
            if (LYR_SMALL_FETCH_CTRL === ctrl) LYR_SMALL_FETCH_CTRL = null;
        }
    }

    function renderEmpty(msg) {
        emptyEl.style.display = 'block';
        emptyEl.textContent = msg;
        linesEl.innerHTML = '';
    }
    function renderSynced() {
        emptyEl.style.display = 'none';
        linesEl.innerHTML = LYR_LINES.map((ln, i) =>
            `<div class="lyrics-line" data-i="${i}">${escapeHtmlSimple(ln.text)}</div>`
        ).join('');
    }
    function renderPlain() {
        emptyEl.style.display = 'none';
        linesEl.innerHTML = '<div style="text-align:center;">' +
            escapeHtmlSimple(LYR_PLAIN).replace(/\n/g, '<br>') + '</div>';
    }
    function escapeHtmlSimple(s) {
        return String(s || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    /* SYNC: cada 250ms busca la línea actual según ytPlayer.currentTime
       y aplica .active. Auto-scroll para mantenerla a la vista. */
    function tick() {
        if (!LYR_OPEN || !LYR_LINES || !LYR_LINES.length) return;
        let t = 0;
        try {
            if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getCurrentTime) {
                t = ytPlayer.getCurrentTime() || 0;
            }
        } catch (_) {}
        /* Búsqueda binaria de la última línea cuyo time <= t. */
        let lo = 0, hi = LYR_LINES.length - 1, idx = -1;
        while (lo <= hi) {
            const mid = (lo + hi) >> 1;
            if (LYR_LINES[mid].time <= t) { idx = mid; lo = mid + 1; }
            else hi = mid - 1;
        }
        if (idx === LYR_LAST_ACTIVE) return;
        LYR_LAST_ACTIVE = idx;
        const all = linesEl.querySelectorAll('.lyrics-line');
        all.forEach((el, i) => {
            el.classList.remove('active', 'past');
            if (i < idx) el.classList.add('past');
            if (i === idx) el.classList.add('active');
        });
        const activeEl = all[idx];
        if (activeEl) {
            const top = activeEl.offsetTop - scrollEl.clientHeight / 2 + activeEl.clientHeight / 2;
            scrollEl.scrollTo({ top, behavior: 'smooth' });
        }
    }
    function startSync() { stopSync(); LYR_SYNC_TIMER = setInterval(tick, 250); }
    function stopSync()  { if (LYR_SYNC_TIMER) { clearInterval(LYR_SYNC_TIMER); LYR_SYNC_TIMER = null; } }

    /* Hook al cambio de track: si el panel está abierto, re-fetch. */
    const _origUpdateTrackUI = (typeof updateTrackUI === 'function') ? updateTrackUI : null;
    if (_origUpdateTrackUI) {
        window.updateTrackUI = function(idx) {
            _origUpdateTrackUI(idx);
            if (LYR_OPEN) fetchForCurrent();
        };
    }

    /* Expone close para que el módulo del fullscreen pueda cerrar la
       ventana de letras al maximizar (requisito UX del usuario). */
    window.__lyricsClose = close_;
})();

/* ════════════════════════════════════════════════════════════════
   FULLSCREEN PLAYER — minimize / maximize / sync / lyrics overlay.
   ════════════════════════════════════════════════════════════════ */
(function fullscreenPlayerModule() {
    const root       = document.getElementById('player-full');
    const win        = document.getElementById('music-player');
    if (!root || !win) return;

    const btnMin     = document.getElementById('player-minimize');
    const btnMax     = document.getElementById('player-maximize');
    const btnRestore = document.getElementById('pf-restore');
    const btnCloseX  = document.getElementById('pf-close-x');

    /* Refs UI fullscreen. */
    const vinyl    = document.getElementById('pf-vinyl');
    const vinylLbl = document.getElementById('pf-vinyl-label');
    const titleEl  = document.getElementById('pf-title');
    const artistEl = document.getElementById('pf-artist');
    const plNameEl = document.getElementById('pf-pl-name');
    const progFill = document.getElementById('pf-progress-fill');
    const progBar  = document.getElementById('pf-progress-track');
    const tCurEl   = document.getElementById('pf-time-cur');
    const tTotEl   = document.getElementById('pf-time-tot');
    const btnPrev2 = document.getElementById('pf-prev');
    const btnNext2 = document.getElementById('pf-next');
    const btnTgl2  = document.getElementById('pf-toggle');
    const btnSh2   = document.getElementById('pf-shuffle');
    const btnLyr   = document.getElementById('pf-lyrics');

    /* Helpers de formato y sync. */
    function fmtTime(s) {
        if (!s || isNaN(s)) return '0:00';
        s = Math.floor(s);
        const m = Math.floor(s / 60);
        const r = s % 60;
        return m + ':' + (r < 10 ? '0' + r : r);
    }
    /* Refs adicionales del nuevo layout. */
    const bgCover = document.getElementById('pf-bg-cover');
    const reelL   = document.getElementById('pf-reel-l');
    const reelR   = document.getElementById('pf-reel-r');

    function syncFromOriginalPlayer() {
        try {
            if (typeof playlist !== 'undefined' && playlist.length && typeof currentTrack === 'number') {
                const tr = playlist[currentTrack];
                if (tr) {
                    titleEl.textContent  = tr.title  || '—';
                    artistEl.textContent = tr.artist || '—';
                    if (tr.videoId) {
                        const cover = 'https://img.youtube.com/vi/' + tr.videoId + '/mqdefault.jpg';
                        vinylLbl.style.backgroundImage = 'url("' + cover + '")';
                        vinylLbl.classList.remove('empty');
                        vinylLbl.textContent = '';
                        /* Fondo difuminado con el cover de la canción. */
                        if (bgCover) bgCover.style.backgroundImage = 'url("' + cover + '")';
                    } else {
                        vinylLbl.classList.add('empty');
                        vinylLbl.textContent = '♪';
                        vinylLbl.style.backgroundImage = '';
                        if (bgCover) bgCover.style.backgroundImage = '';
                    }
                }
            }
            const plName = document.getElementById('player-pl-name');
            if (plName) plNameEl.textContent = plName.textContent;

            if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getDuration) {
                const dur = ytPlayer.getDuration() || 0;
                const cur = ytPlayer.getCurrentTime() || 0;
                if (dur > 0) progFill.style.width = ((cur / dur) * 100) + '%';
                tCurEl.textContent = fmtTime(cur);
                tTotEl.textContent = fmtTime(dur);
                const playing = ytPlayer.getPlayerState && ytPlayer.getPlayerState() === YT.PlayerState.PLAYING;
                btnTgl2.classList.toggle('pf-playing', !!playing);
                vinyl.classList.toggle('pf-spinning', !!playing);
                /* Reels girando en sync con el play state. */
                if (reelL) reelL.style.animationPlayState = playing ? 'running' : 'paused';
                if (reelR) reelR.style.animationPlayState = playing ? 'running' : 'paused';
            }
            /* Sync shuffle visual desde el botón original. */
            if (typeof autoplayRandom !== 'undefined') {
                btnSh2.classList.toggle('is-on', !!autoplayRandom);
                btnSh2.setAttribute('aria-pressed', autoplayRandom ? 'true' : 'false');
            }
            /* Sync volumen (el slider y el icono) desde el slider
               original — atrapa cambios externos. */
            if (typeof window.__pfSyncVolume === 'function') window.__pfSyncVolume();
        } catch(_){}
    }

    let syncTimer = null;
    let isOpen = false;

    function openFullscreen() {
        if (isOpen) return;
        /* Cierra la ventana de letras flotante si estaba abierta —
           en el fullscreen las letras se muestran como overlay. */
        if (typeof window.__lyricsClose === 'function') {
            try { window.__lyricsClose(); } catch(_){}
        }
        root.classList.add('pf-active');
        isOpen = true;
        syncFromOriginalPlayer();
        if (syncTimer) clearInterval(syncTimer);
        syncTimer = setInterval(syncFromOriginalPlayer, 500);
    }
    function closeFullscreen() {
        if (!isOpen) return;
        /* Apaga letras COMPLETAMENTE antes de salir: pfCloseLyrics se
           encarga de cancelar el fetch en vuelo, limpiar el DOM del
           lyrStage, resetear todos los flags de estado (counters,
           repeatGroup, multiState…) y — clave — quitar la clase .is-on
           del botón micrófono para matar su glow pulsante.
           pfCloseLyrics() en sí ya chequea !pfLyricsOpen, pero el
           guard aquí evita trabajo innecesario. */
        if (pfLyricsOpen) pfCloseLyrics();
        root.classList.remove('pf-active');
        isOpen = false;
        if (syncTimer) { clearInterval(syncTimer); syncTimer = null; }
    }
    /* Exposed globally para que el ctxMenu pueda cerrarlo desde otro
       IIFE (showTrackCtxMenu vive en otro scope). Usar la función real
       en vez de simular click() en el botón restore evita un timing
       weird donde el listener no se dispara y el siguiente openFullscreen
       cree que sigue abierto (necesitando 2 clicks en max). */
    window.__closeFullscreenPlayer = closeFullscreen;

    /* Minimize: usa taskbarManager para llevar el reproductor a la
       taskbar — igual que cualquier otra ventana del desktop. */
    if (btnMin) btnMin.addEventListener('click', function() {
        if (window.taskbarManager) {
            if (!window.taskbarManager.isRegistered('music-player')) {
                window.taskbarManager.register('music-player', 'Reproductor', '<img src="assets/img/appIcons/musicaIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:middle;">', 'block');
            }
            window.taskbarManager.minimize('music-player');
        } else {
            win.style.display = 'none';
        }
    });

    /* Maximize: abre fullscreen overlay. */
    if (btnMax) btnMax.addEventListener('click', openFullscreen);
    if (btnRestore) btnRestore.addEventListener('click', closeFullscreen);
    if (btnCloseX)  btnCloseX.addEventListener('click', closeFullscreen);

    /* Forward de controles al reproductor original — reusa toda la
       lógica de play/pause/prev/next/shuffle sin duplicar. */
    if (btnPrev2) btnPrev2.addEventListener('click', () => document.getElementById('btn-prev').click());
    if (btnNext2) btnNext2.addEventListener('click', () => document.getElementById('btn-next').click());
    if (btnTgl2)  btnTgl2.addEventListener('click',  () => document.getElementById('btn-play').click());
    if (btnSh2)   btnSh2.addEventListener('click',   () => document.getElementById('btn-shuffle').click());

    /* Seek en la progress bar. */
    if (progBar) progBar.addEventListener('click', function(e) {
        if (typeof ytPlayer === 'undefined' || !ytPlayer || !ytPlayer.getDuration) return;
        const rect = progBar.getBoundingClientRect();
        const pct = (e.clientX - rect.left) / rect.width;
        ytPlayer.seekTo(pct * ytPlayer.getDuration(), true);
        syncFromOriginalPlayer();
    });

    /* ── Volumen — proxy al slider del player original. ──
       Forward del input → setea el value del #player-volume y dispara
       su event 'input' para reusar toda la lógica de persistencia +
       icono altavoz + setVolume del ytPlayer. */
    const volSlider2 = document.getElementById('pf-volume');
    const volIcon2   = document.getElementById('pf-vol-icon');
    function updateVolIcon(v) {
        if (!volIcon2) return;
        if (v === 0) volIcon2.textContent = '◄✕';
        else if (v < 50) volIcon2.textContent = '◄)';
        else volIcon2.textContent = '◄))';
    }
    if (volSlider2) {
        volSlider2.addEventListener('input', function() {
            const v = parseInt(this.value, 10);
            const orig = document.getElementById('player-volume');
            if (orig) {
                orig.value = v;
                orig.dispatchEvent(new Event('input', { bubbles: true }));
            } else if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.setVolume) {
                ytPlayer.setVolume(v);
            }
            updateVolIcon(v);
        });
    }
    /* Sync inicial + cada vez que se abra el fullscreen: lee el value
       del slider original y refleja en el del fullscreen + icono.
       Expuesto en window.__pfSyncVolume → syncFromOriginalPlayer lo
       llama dentro de su tick para mantener consistencia con cambios
       externos (otra pestaña, restore desde state). */
    window.__pfSyncVolume = function() {
        const orig = document.getElementById('player-volume');
        if (orig && volSlider2) {
            volSlider2.value = orig.value;
            updateVolIcon(parseInt(orig.value, 10));
        }
    };
    window.__pfSyncVolume();

    /* Logger del módulo: envía cada paso a logs/lyrics-debug.log
       en el servidor + console.log local. NO bloquea (fire-and-forget). */
    function pfLog(msg) {
        try { console.log('[pf-lyrics]', msg); } catch(_){}
        try {
            fetch('assets/music/api.php?action=client-log', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ msg: '[pf-lyrics] ' + msg }),
                credentials: 'same-origin',
                keepalive: true,
            });
        } catch(_){}
    }

    /* ════════════════════════════════════════════════════════════
       LYRIC VIDEO — solo la línea ACTUAL flota sobre el reproductor.
       - Sin fondo, sin blur (integración directa con el player).
       - Cada char tiene su propio tremble (delay + duration random).
       - Al cambiar de línea: la anterior sube + fade-out, la nueva
         entra desde abajo + fade-in.
       - Si la canción está en gap instrumental (>7s entre líneas y
         ya pasaron 3.5s de la línea actual): no se muestra nada.
       - Solo letras SINCRONIZADAS (LRC). Si solo hay plain, no se
         muestra nada (no sabemos qué está sonando).
       ════════════════════════════════════════════════════════════ */
    let pfLyrLines = null;
    let pfLyrVid   = null;
    let pfLyrCurIdx = -1;     /* índice de línea actualmente mostrada (-1 = ninguna) */
    let pfLyricsOpen = false;
    let pfLyrTimer = null;
    const overlay  = document.getElementById('pf-lyrics-overlay');
    const lyrStage = document.getElementById('pf-lyr-stage');

    function escH(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function pfParseLRC(lrc) {
        const lines = [];
        if (!lrc) return lines;
        lrc.split(/\r?\n/).forEach(raw => {
            const stamps = []; let rest = raw;
            const re = /^\s*\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\]/;
            let m;
            while ((m = re.exec(rest))) {
                const min = +m[1], sec = +m[2], frac = m[3] ? +m[3] / Math.pow(10, m[3].length) : 0;
                stamps.push(min * 60 + sec + frac);
                rest = rest.slice(m[0].length);
            }
            const text = rest.trim();
            if (stamps.length && text) stamps.forEach(t => lines.push({ time: t, text }));
        });
        lines.sort((a, b) => a.time - b.time);
        return lines;
    }
    function pfCurTrack() {
        if (typeof playlist !== 'undefined' && playlist.length && typeof currentTrack === 'number') {
            return playlist[currentTrack] || null;
        }
        return null;
    }

    /* Construye el HTML de una línea agrupando los chars POR PALABRA.
       Cada palabra es un <span class="pf-lyr-word"> con white-space:
       nowrap → la palabra es indivisible: o cabe entera en la línea
       actual, o salta entera a la siguiente. Dentro de cada palabra,
       cada char es un <span class="pf-lyr-char"> con tremble propio. */
    function pfRenderLine(text) {
        if (!text) return '';
        const out = ['<span class="pf-lyr-line-content">'];
        /* Split por whitespace pero MANTENIENDO los espacios como tokens
           (para preservar separaciones múltiples). */
        const tokens = text.split(/(\s+)/);
        for (const tok of tokens) {
            if (tok === '') continue;
            if (/^\s+$/.test(tok)) {
                /* Solo whitespace → &nbsp; por cada espacio para que no
                   se colapsen. */
                out.push('&nbsp;');
                continue;
            }
            /* Palabra → wrappear todo el bloque para que sea indivisible. */
            out.push('<span class="pf-lyr-word">');
            for (let i = 0; i < tok.length; i++) {
                const c = tok[i];
                const delay = (Math.random() * 1.8).toFixed(2);
                const dur   = (1.4 + Math.random() * 1.4).toFixed(2);
                out.push('<span class="pf-lyr-char" style="--delay:' + delay + 's;--dur:' + dur + 's">' + escH(c) + '</span>');
            }
            out.push('</span>');
        }
        out.push('</span>');
        return out.join('');
    }

    /* Muestra una línea nueva o limpia el stage si text es null.
       Aprovecha CSS animations forwards: insertar el elemento dispara
       pfLyrSlideIn automáticamente; añadir .leaving dispara
       pfLyrSlideOut. Sin RAF, sin transitions class-swap-fragile. */
    /* Modos experimentales single-line + multi-line. */
    const PF_EXP_MODES = [
        'vert-left', 'tilt-r', 'corner-tl', 'top', 'mega',
        'diagonal', 'vert-right', 'tilt-l', 'corner-br', 'bottom',
    ];
    /* Multi-modes con totals reducidos para que la animación no se
       eternice. Cada one tiene un cap absoluto de tiempo (PF_MULTI_MAX_MS)
       que fuerza el final si los versos son lentos. */
    const PF_MULTI_MODES = [
        { mode: 'fill-vert',  total: 5, holdMs: 1100 },
        { mode: 'fill-stack', total: 4, holdMs: 900  },
    ];
    /* Cap absoluto de tiempo total para multi-modes y fast-stack. Si
       los versos son lentos y no completamos los slots a tiempo,
       cerramos el grupo aquí (5.5s + hold ≈ 6.5s total). */
    const PF_MULTI_MAX_MS      = 5500;
    const PF_FAST_STACK_MAX_MS = 5500;
    const PF_FAST_LINE_THRESHOLD = 2.0;
    const PF_FAST_STACK_TOTAL = 7;

    let pfLineCounter = 0;
    let pfMultiState = null;        /* {mode, total, slot, holdMs, startedAt} */
    let pfFastStackCounter = 0;
    let pfFastStackStartedAt = 0;   /* ms timestamp del primer fast-line en curso */
    /* Cache de estilo por línea — key = texto normalizado, value =
       descriptor del estilo elegido en la primera aparición. Si la
       misma línea ("Everybody, everybody, everybody living now")
       reaparece más adelante en la canción, reusamos su estilo para
       que el coro tenga consistencia visual.
       Tipos guardados:
         { kind: 'repeat-inline', layout: 'h'|'v' }   → fast-repeat inline
         { kind: 'repeat-scatter' }                   → scatter normal
         { kind: 'normal', mode: '<modeName>' }       → modo single-line
       Multi-mode (fill-vert/fill-stack/fast-stack) NO se cachean —
       dependen del estado de grupo y romperían el flow. */
    let pfLineStyleCache = {};
    function pfLineKey(text) {
        return text ? text.toLowerCase().replace(/\s+/g, ' ').trim() : '';
    }

    /* Devuelve la duración de la línea actual en segundos (tiempo
       hasta el siguiente verso). Si es la última, devuelve un valor
       alto (treated as long). */
    function pfGetCurLineDuration() {
        if (!pfLyrLines || pfLyrCurIdx < 0 || pfLyrCurIdx >= pfLyrLines.length) return 999;
        const cur = pfLyrLines[pfLyrCurIdx];
        const next = pfLyrLines[pfLyrCurIdx + 1];
        if (!next) return 999;
        return next.time - cur.time;
    }

    function pfPickMode(opts) {
        opts = opts || {};
        const avoidStack = !!opts.avoidStacking;
        const avoidVert  = !!opts.avoidVertical;
        let progress = 0;
        try {
            if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getDuration) {
                const d = ytPlayer.getDuration() || 0;
                const t = ytPlayer.getCurrentTime() || 0;
                if (d > 0) progress = t / d;
            }
        } catch(_){}
        /* Intro/outro → center + blur. Reset estados acumulativos. */
        if (progress < 0.25 || progress > 0.92) {
            pfMultiState = null;
            pfFastStackCounter = 0;
            pfFastStackStartedAt = 0;
            return { mode: 'center', blur: true };
        }
        /* Si esta línea no debe apilarse pero hay un multi-mode en
           curso → lo abortamos (la siguiente line will be normal). */
        if (avoidStack && pfMultiState) {
            pfMultiState = null;
        }
        const now = Date.now();
        /* Si hay un multi-mode en curso: continúa hasta completar o
           hasta el cap absoluto de tiempo (lo que ocurra primero). */
        if (pfMultiState) {
            const slot = pfMultiState.slot;
            const total = pfMultiState.total;
            const elapsed = now - pfMultiState.startedAt;
            const isLast = (slot + 1) >= total || elapsed >= PF_MULTI_MAX_MS;
            const result = {
                mode: pfMultiState.mode,
                blur: false,
                slot: slot,
                total: total,
                isLast: isLast,
                holdMs: pfMultiState.holdMs,
            };
            if (isLast) pfMultiState = null;
            else pfMultiState.slot++;
            return result;
        }
        /* ── Detección de línea rápida ──
           Si la línea actual dura menos del threshold → fast-stack.
           El stack tiene un cap de tiempo: si lleva >5.5s acumulando,
           lo forzamos a cerrar (treat as long line) para que no se
           eternice durante un verso de rap largo. */
        const lineDur = pfGetCurLineDuration();
        if (lineDur < PF_FAST_LINE_THRESHOLD && !avoidStack) {
            if (pfFastStackStartedAt === 0) pfFastStackStartedAt = now;
            const elapsedFast = now - pfFastStackStartedAt;
            if (elapsedFast < PF_FAST_STACK_MAX_MS) {
                const slot = pfFastStackCounter % PF_FAST_STACK_TOTAL;
                pfFastStackCounter++;
                return {
                    mode: 'fast-stack',
                    blur: false,
                    slot: slot,
                    total: PF_FAST_STACK_TOTAL,
                    isFastStack: true,
                };
            }
            /* Cap superado → forzar reset, esta línea va por modo normal. */
            pfFastStackCounter = 0;
            pfFastStackStartedAt = 0;
        } else {
            /* Línea larga o avoidStack → reset del fast-stack. */
            pfFastStackCounter = 0;
            pfFastStackStartedAt = 0;
        }
        /* Dado random: 22% chance de arrancar un multi-mode después
           del 4º verso. avoidStack excluye TODOS los multi-modes;
           avoidVert excluye fill-vert (también es apilado vertical
           de caracteres como vert-left/right). */
        if (pfLineCounter > 3 && Math.random() < 0.22 && !avoidStack) {
            let candidates = PF_MULTI_MODES;
            if (avoidVert) {
                candidates = candidates.filter(c => c.mode !== 'fill-vert');
            }
            if (candidates.length > 0) {
                const cfg = candidates[Math.floor(Math.random() * candidates.length)];
                pfMultiState = {
                    mode: cfg.mode, total: cfg.total, slot: 1,
                    holdMs: cfg.holdMs, startedAt: now,
                };
                return {
                    mode: cfg.mode,
                    blur: false,
                    slot: 0,
                    total: cfg.total,
                    isLast: false,
                    holdMs: cfg.holdMs,
                };
            }
        }
        /* Single-line: rotamos modos experimentales. Si avoidVert
           filtramos los modos verticales (vert-left/right) que no
           caben bien para verses largos. */
        let modes = PF_EXP_MODES;
        if (avoidVert) {
            modes = modes.filter(m => m !== 'vert-left' && m !== 'vert-right');
        }
        const idx = pfLineCounter % modes.length;
        return { mode: modes[idx], blur: false };
    }

    /* Extrae las partes entre paréntesis del verso. Soporta paréntesis
       ASCII y CJK fullwidth (）). Devuelve {main, asides}:
         - main: texto SIN las partes paréntesis, trim.
         - asides: array de strings con cada contenido de paréntesis.
       Si todo el verso son paréntesis → main será '' y asides tendrá
       el contenido. */
    function pfExtractParens(text) {
        if (!text) return { main: '', asides: [] };
        const re = /[(（]([^)）]+)[)）]/g;
        const asides = [];
        let m;
        while ((m = re.exec(text)) !== null) {
            const t = m[1].trim();
            if (t) asides.push(t);
        }
        const main = text.replace(re, '').replace(/\s+/g, ' ').trim();
        return { main, asides };
    }

    /* Contador de asides (paréntesis) para rotar la esquina en la que
       se muestran (tl → tr → bl → br). Reset al cerrar lyrics. */
    const PF_ASIDE_CORNERS = ['tl', 'tr', 'bl', 'br'];
    let pfAsideCounter = 0;

    /* State activo de un grupo de líneas consecutivas idénticas mostradas
       como scatter. {startIdx, endIdx, text, count}. Mientras pfLyrCurIdx
       esté en este rango, NO re-renderizamos — el scatter ya está visible. */
    let pfRepeatLineGroup = null;

    /* Detecta si la línea ACTUAL (pfLyrCurIdx) inicia un grupo de
       líneas consecutivas idénticas. Devuelve {startIdx, endIdx, text,
       count} si hay 3+ repeticiones consecutivas, o null. */
    function pfDetectMultiLineRepeat() {
        if (!pfLyrLines || pfLyrCurIdx < 0 || pfLyrCurIdx >= pfLyrLines.length) return null;
        const target = _pfNorm(pfLyrLines[pfLyrCurIdx].text);
        if (!target) return null;
        let endIdx = pfLyrCurIdx;
        while (endIdx + 1 < pfLyrLines.length && _pfNorm(pfLyrLines[endIdx + 1].text) === target) {
            endIdx++;
        }
        const count = endIdx - pfLyrCurIdx + 1;
        if (count >= 3) {
            return { startIdx: pfLyrCurIdx, endIdx, text: pfLyrLines[pfLyrCurIdx].text, count };
        }
        return null;
    }
    /* Cuánto tiempo permanece visible un aside antes de auto-cerrarse. */
    const PF_ASIDE_HOLD_MS = 4500;

    /* Renderiza una palabra/frase entre paréntesis como overlay pequeño
       en una esquina. NO limpia las líneas anteriores. Auto-fade tras
       PF_ASIDE_HOLD_MS para no quedar fantasma indefinidamente. */
    function pfRenderAsideCorner(text) {
        if (!lyrStage || !text) return;
        const corner = PF_ASIDE_CORNERS[pfAsideCounter++ % PF_ASIDE_CORNERS.length];
        const wrap = document.createElement('div');
        wrap.className = 'pf-lyr-line-wrap pf-lyr-mode-aside pf-lyr-aside-' + corner;
        wrap.innerHTML = pfRenderLine(text);
        lyrStage.appendChild(wrap);
        setTimeout(() => {
            if (!wrap.parentNode || wrap.classList.contains('leaving')) return;
            wrap.style.animationDelay = '0s';
            wrap.classList.add('leaving');
            setTimeout(() => { if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }, 700);
        }, PF_ASIDE_HOLD_MS);
    }

    /* Helper de normalización para comparaciones case/punctuation-insensitive. */
    function _pfNorm(s) {
        return (s || '').trim().toLowerCase().replace(/[.,!?;:¡¿]+$/, '');
    }

    /* Detecta si una línea consiste en UNA ÚNICA palabra enfática:
         - Palabra de 2+ chars seguida de 1-3 signos de exclamación
           (FIRE!, GO!, wow!!!).
         - O ALL CAPS de 4+ chars (FIRE, BURN, WHATEVER).
       Solo aplica cuando la línea trim+limpia-puntuación es UN solo
       token (sin espacios ni más palabras). Devuelve la palabra
       enfática o null. */
    function pfDetectSingleEmphasis(text) {
        if (!text) return null;
        /* Trim outer punctuation (excepto !) y whitespace. */
        const trimmed = text.trim().replace(/^[.,;:¡¿\-—\s]+|[.,;:\-—\s]+$/g, '').trim();
        if (!trimmed) return null;
        /* Debe ser UNA sola palabra (sin espacios). */
        if (/\s/.test(trimmed)) return null;
        /* Palabra + 1-3 exclamaciones. */
        const exclMatch = trimmed.match(/^([\p{L}\d]+)(!{1,3})$/u);
        if (exclMatch && exclMatch[1].length >= 2) return trimmed;
        /* ALL CAPS de 4+ chars (sin exclamación). */
        if (/^[\p{Lu}][\p{Lu}\d]{3,}$/u.test(trimmed)) return trimmed;
        return null;
    }

    /* Renderiza una palabra enfática como overlay GIGANTE centrado.
       Auto-fade tras 2.5s. Múltiples emph pueden coexistir si el line
       tiene varias (cada una con su delay distinto). */
    function pfRenderEmphasis(word) {
        if (!lyrStage || !word) return;
        const wrap = document.createElement('div');
        wrap.className = 'pf-lyr-line-wrap pf-lyr-mode-emph';
        wrap.innerHTML = pfRenderLine(word);
        lyrStage.appendChild(wrap);
        setTimeout(() => {
            if (!wrap.parentNode || wrap.classList.contains('leaving')) return;
            wrap.style.animationDelay = '0s';
            wrap.classList.add('leaving');
            setTimeout(() => { if (wrap.parentNode) wrap.parentNode.removeChild(wrap); }, 600);
        }, 2500);
    }

    /* Render INLINE para repeticiones DEMASIADO RÁPIDAS para scatter.
       REUSAMOS los modos multi-line ya existentes — mismo color, fondo,
       animaciones y tipografía que el resto:
         layout='v' → cada frase es un wrap fill-stack (fila horizontal
                      en su slot vertical) → apiladas verticalmente.
         layout='h' → cada frase es un wrap fill-vert (columna vertical
                      en su slot horizontal) → alineadas lado a lado.
       delaysSec: array de delays absolutos por slot — cada wrap recibe
       su animationDelay para entrar escalonadamente al ritmo de la
       música, no todas a la vez. */
    function pfRenderInlineRepeat(texts, layout, delaysSec) {
        if (!lyrStage || !texts || !texts.length) return;
        const mode = layout === 'v' ? 'fill-stack' : 'fill-vert';
        const total = texts.length;
        texts.forEach((text, slot) => {
            const wrap = document.createElement('div');
            wrap.className = 'pf-lyr-line-wrap pf-lyr-mode-' + mode;
            wrap.style.setProperty('--slot', String(slot));
            wrap.style.setProperty('--total', String(total));
            wrap.setAttribute('data-pf-slot', String(slot));
            const delay = (delaysSec && delaysSec[slot] !== undefined) ? delaysSec[slot] : 0;
            if (delay > 0) {
                /* Pre-oculta el wrap mientras espera su delay: las reglas
                   base de fill-stack/fill-vert no traen opacity:0, así
                   que durante el animation-delay el wrap es VISIBLE a
                   opacidad normal y solo se aplica la animación cuando
                   arranca → todas aparecían "ya puestas" antes de
                   animarse. Inline opacity:0 + animation forwards arregla
                   esto: invisible durante el delay, la animación llega y
                   anima 0→1, al terminar el frame 100% sostiene opacity:1. */
                wrap.style.opacity = '0';
                wrap.style.animationDelay = delay.toFixed(2) + 's';
            }
            wrap.innerHTML = pfRenderLine(text);
            lyrStage.appendChild(wrap);
        });
    }

    /* Detecta repeticiones de palabra o FRASE dentro de una línea:
         - "Sí, sí, sí, sí" → palabra "Sí" repetida.
         - "everybody it's all up in my, everybody it's all up in my, everybody it's all in my"
           → frase "everybody it's all up in my" repetida (con la 3ª ligeramente diferente, igualmente cuenta).
       Devuelve {word, count} o null. La detección es tolerante: si la
       frase MÁS COMÚN aparece 2+ veces en una línea con 3+ frases,
       se considera repetición. */
    function pfDetectRepeatedWord(text) {
        if (!text) return null;

        /* Paso 1: palabra única repetida. Todas iguales → outliers vacíos. */
        const words = text.split(/[^\p{L}\p{N}]+/u).filter(p => p.length > 0);
        if (words.length >= 3) {
            const first = words[0].toLowerCase();
            if (words.every(p => p.toLowerCase() === first)) {
                return { word: words[0], count: words.length, triggerCount: words.length, outliers: [] };
            }
        }

        /* Paso 2: frase repetida — split por comas/puntos/punto-coma. */
        const phrases = text.split(/[,;.!?¡¿]+\s*/).map(s => s.trim()).filter(s => s.length > 0);
        if (phrases.length >= 3) {
            const counts = {};
            for (const p of phrases) {
                const k = _pfNorm(p);
                if (k) counts[k] = (counts[k] || 0) + 1;
            }
            let bestKey = null, bestCount = 0;
            for (const k in counts) {
                if (counts[k] > bestCount) { bestKey = k; bestCount = counts[k]; }
            }
            if (bestCount >= 2) {
                const phrase = phrases.find(p => _pfNorm(p) === bestKey) || phrases[0];
                const outliers = phrases.filter(p => _pfNorm(p) !== bestKey);
                const orderedPhrases = phrases.map(p => ({
                    text: p,
                    isOutlier: _pfNorm(p) !== bestKey,
                    length: p.length,
                }));
                return {
                    word: phrase,
                    count: bestCount,
                    triggerCount: phrases.length,
                    outliers,
                    orderedPhrases,
                };
            }
        }

        /* Paso 3: TOKEN GROUP repetido (sin comas). Cubre versos como
           "i love you i love you i love you" — separados por espacios
           pero sin puntuación. Busca el grupo más pequeño que cubra
           todos los tokens repitiendo 3+ veces. */
        const tokens = text.trim().split(/\s+/).filter(t => t.length > 0);
        if (tokens.length >= 3) {
            for (let groupSize = 1; groupSize <= Math.floor(tokens.length / 3); groupSize++) {
                const candidate = tokens.slice(0, groupSize).join(' ');
                const candLower = candidate.toLowerCase();
                let matchCount = 0;
                for (let i = 0; i + groupSize <= tokens.length; i += groupSize) {
                    const slice = tokens.slice(i, i + groupSize).join(' ').toLowerCase();
                    if (slice === candLower) matchCount++;
                    else break;
                }
                if (matchCount * groupSize === tokens.length && matchCount >= 3) {
                    const op = [];
                    for (let k = 0; k < matchCount; k++) {
                        op.push({ text: candidate, isOutlier: false, length: candidate.length });
                    }
                    return {
                        word: candidate, count: matchCount,
                        triggerCount: matchCount, outliers: [],
                        orderedPhrases: op,
                    };
                }
            }
        }

        /* Paso 4: CHAR-LEVEL substring repetido (sin separadores ni
           espacios). Cubre "くるくるくるくる" (Japonés), "lalala",
           "OhOhOhOh", etc. Encuentra el sub-patrón más corto que
           cubre el texto entero repitiendo 3+ veces. */
        const charTrimmed = text.trim();
        if (charTrimmed.length >= 4) {
            for (let L = 1; L <= Math.floor(charTrimmed.length / 3); L++) {
                const candidate = charTrimmed.substring(0, L);
                if (!candidate.trim()) continue;
                const candLower = candidate.toLowerCase();
                let cnt = 0, i = 0;
                while (i + L <= charTrimmed.length
                    && charTrimmed.substring(i, i + L).toLowerCase() === candLower) {
                    cnt++;
                    i += L;
                }
                /* Cobertura total Y 3+ repeticiones. */
                if (i === charTrimmed.length && cnt >= 3) {
                    const op = [];
                    for (let k = 0; k < cnt; k++) {
                        op.push({ text: candidate, isOutlier: false, length: candidate.length });
                    }
                    return {
                        word: candidate, count: cnt,
                        triggerCount: cnt, outliers: [],
                        orderedPhrases: op,
                    };
                }
            }
        }

        return null;
    }

    /* Construye y suelta N copias de la misma palabra esparcidas en
       posiciones de un GRID virtual. Stepwise entry — el delay entre
       cada copia se calcula a partir de la duración del verso ÷ total
       de frases (incluyendo outliers). Sincroniza con la música:
         - "sí, sí, sí" (línea 1s / 3 frases) → step ~0.33s (rápido)
         - "im i bad × 4" (línea 4s / 4 frases) → step ~1s (lento)
       opts.lineDurMs: duración del verso (defecto 2s).
       opts.totalCount: total de frases incluyendo outliers (defecto count).
       Devuelve el stepDelay calculado (s) para que el caller sincronice
       los outliers también con el mismo ritmo. */
    function pfRenderScatter(word, count, opts) {
        opts = opts || {};
        /* delaysSec: array de delays absolutos en segundos para cada
           copia (en orden de aparición temporal). Si se proporciona,
           tiene prioridad. Si no, se cae al cálculo legacy basado en
           lineDurMs/totalCount. */
        const delaysSec = opts.delaysSec || null;
        const lineDurMs = opts.lineDurMs || 2000;
        const totalCount = opts.totalCount || count;
        const max = Math.min(count, 8);
        const cols = Math.ceil(Math.sqrt(max));
        const rows = Math.ceil(max / cols);
        const padX = 12, padY = 18;
        const usableW = 100 - padX * 2;
        const usableH = 100 - padY * 2;
        const cellW = usableW / cols;
        const cellH = usableH / rows;
        /* Si NO viene delaysSec, calculamos step uniforme basado en
           lineDurMs (modo legacy). */
        const stepRaw = (lineDurMs / 1000) / Math.max(totalCount, 1);
        const fallbackStep = Math.max(0.18, Math.min(stepRaw, 1.2));
        const order = [];
        for (let i = 0; i < max; i++) order.push(i);
        for (let i = order.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            const tmp = order[i]; order[i] = order[j]; order[j] = tmp;
        }
        order.forEach((cellIdx, displayIdx) => {
            const col = cellIdx % cols;
            const row = Math.floor(cellIdx / cols);
            const cx = padX + (col + 0.5) * cellW;
            const cy = padY + (row + 0.5) * cellH;
            const jx = (Math.random() - 0.5) * cellW * 0.4;
            const jy = (Math.random() - 0.5) * cellH * 0.4;
            const wrap = document.createElement('div');
            wrap.className = 'pf-lyr-line-wrap pf-lyr-mode-scatter';
            wrap.style.setProperty('--x', (cx + jx).toFixed(2));
            wrap.style.setProperty('--y', (cy + jy).toFixed(2));
            wrap.style.setProperty('--r', String(-14 + Math.random() * 28));
            wrap.style.setProperty('--s', String(0.75 + Math.random() * 0.45));
            /* displayIdx es el orden VISUAL (post-shuffle). Para el
               TIEMPO usamos directamente displayIdx también — i.e., la
               copia visualmente N-ésima aparece en el delay N-ésimo.
               El shuffle de cells es solo para no leer en grid. */
            const delay = (delaysSec && delaysSec[displayIdx] !== undefined)
                ? delaysSec[displayIdx]
                : displayIdx * fallbackStep;
            wrap.style.animationDelay = delay.toFixed(2) + 's';
            wrap.innerHTML = pfRenderLine(word);
            lyrStage.appendChild(wrap);
        });
        return delaysSec ? delaysSec[delaysSec.length - 1] || 0 : (max - 1) * fallbackStep;
    }

    /* Helper para marcar como leaving los wraps existentes. Limpia el
       animation-delay inline para que TODAS las salidas arranquen a la
       vez — sin esto, las copias del scatter heredaban el delay
       escalonado de entrada (0.4s × idx) y salían también escalonadas. */
    function pfFadeOutWraps(selector) {
        const els = lyrStage ? lyrStage.querySelectorAll(selector || '.pf-lyr-line-wrap') : [];
        Array.from(els).forEach(el => {
            if (el.classList.contains('leaving')) return;
            el.style.animationDelay = '0s';
            el.classList.add('leaving');
            const toRemove = el;
            setTimeout(() => { if (toRemove.parentNode) toRemove.parentNode.removeChild(toRemove); }, 800);
        });
    }

    function pfShowLine(text) {
        if (!lyrStage) { pfLog('showLine: lyrStage is null'); return; }
        pfLog('showLine called, text=' + JSON.stringify(text ? text.substring(0, 80) : null));
        if (text) {
            const status = lyrStage.querySelector('.pf-lyr-status');
            if (status) status.remove();
        }
        if (!text) {
            /* Silencio → quita las líneas principales. Asides se mantienen
               hasta su auto-fade (4.5s tras aparecer). */
            pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
            pfRepeatLineGroup = null;
            return;
        }
        /* ── Multi-line repeat ──
           Si estamos DENTRO de un grupo ya activo (idx en rango), no
           renderizamos nada nuevo — el scatter previo sigue en pantalla. */
        if (pfRepeatLineGroup && pfLyrCurIdx >= pfRepeatLineGroup.startIdx && pfLyrCurIdx <= pfRepeatLineGroup.endIdx) {
            pfLog('  skip: still in active multi-line repeat group');
            return;
        }
        /* Si salimos del grupo, lo limpiamos y seguimos al check de
           nuevo grupo. */
        pfRepeatLineGroup = null;
        /* Detecta si la línea ACTUAL inicia un grupo de N líneas
           consecutivas idénticas → scatter del verso. */
        const multiRepeat = pfDetectMultiLineRepeat();
        if (multiRepeat) {
            /* Para multi-line TENEMOS timestamps reales de cada línea
               — los usamos directamente para sincronizar las copias del
               scatter con el ritmo del canto. Cada copia aparece en el
               momento EXACTO en que su línea del LRC tocaría. */
            const startTime = pfLyrLines[multiRepeat.startIdx]?.time || 0;
            const delaysSec = [];
            for (let i = 0; i < multiRepeat.count; i++) {
                const t = pfLyrLines[multiRepeat.startIdx + i]?.time;
                delaysSec.push(t !== undefined ? Math.max(0, t - startTime) : i * 0.5);
            }
            pfLog('  multi-line repeat: idx ' + multiRepeat.startIdx + '-' + multiRepeat.endIdx
                + ' count=' + multiRepeat.count
                + ' delaysSec=[' + delaysSec.map(d => d.toFixed(2)).join(',') + '] "'
                + multiRepeat.text.substring(0, 40) + '"');
            pfRepeatLineGroup = multiRepeat;
            pfLineCounter++;
            root.classList.remove('pf-lyr-blurred');
            pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
            pfRenderScatter(multiRepeat.text, multiRepeat.count, {
                delaysSec,
            });
            return;
        }

        /* ── Detección de paréntesis ──
           Si el verso tiene paréntesis, separamos el main (lo de fuera)
           del aside (lo de dentro). Aside se renderiza en esquina
           pequeña SIN borrar la línea anterior. */
        const parens = pfExtractParens(text);
        if (parens.asides.length > 0 && !parens.main) {
            /* Verso TOTALMENTE entre paréntesis → no borra la previa,
               solo añade el aside en esquina. */
            pfLog('  aside-only: "' + parens.asides.join('|') + '"');
            parens.asides.forEach(a => pfRenderAsideCorner(a));
            return;
        }
        /* El texto principal a renderizar es lo de fuera de los
           paréntesis. Si no había paréntesis, parens.main === text. */
        const mainText = parens.main || text;

        /* Si la línea entera es UNA sola palabra enfática (FIRE!, GO!,
           BURN, etc.) → render como overlay grande explosivo en lugar
           del modo normal. NO emph para versos con texto regular. */
        const singleEmph = pfDetectSingleEmphasis(mainText);
        if (singleEmph) {
            pfLog('  single-word emph: "' + singleEmph + '"');
            pfLineCounter++;
            root.classList.remove('pf-lyr-blurred');
            pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
            pfRenderEmphasis(singleEmph);
            /* Asides del verso (paréntesis) también se muestran. */
            parens.asides.forEach(a => pfRenderAsideCorner(a));
            return;
        }

        /* Caso especial: palabra/frase repetida ("Sí, sí, sí, sí" o
           "im i bad, im i bad, im i bad, im i really that bad") → scatter
           + emphasis para outliers. triggerCount >= 3 gates. */
        const repeated = pfDetectRepeatedWord(mainText);
        if (repeated && repeated.triggerCount >= 3) {
            const outliers = repeated.outliers || [];
            const ordered = repeated.orderedPhrases || [];
            const lineKey = pfLineKey(mainText);
            const cached = pfLineStyleCache[lineKey];
            /* lineDurMs = duración del verso actual en ms (gap hasta el
               siguiente verso del LRC). Usado para decidir si hay tiempo
               para el scatter o si hay que fallback a inline. */
            const lineDurMs = Math.max(500, pfGetCurLineDuration() * 1000);
            /* ── Sincronización por LONGITUD DE FRASE ──
               El tiempo de cada frase se estima como (chars × SEC_PER_CHAR).
               Tomamos longitud antes que duración del verso porque a veces
               el verso es largo en tiempo pero las frases cortas (silencio
               final, como "oh, oh, right"). */
            const SEC_PER_CHAR = 0.1;
            /* Calcula span total estimado del scatter (cumChars × SEC_PER_CHAR).
               Si la línea no llega a cubrir ese span con buffer suficiente
               para la animación de entrada (0.7s), el scatter se buguea:
               las últimas copias no terminan de entrar antes de que llegue
               la siguiente línea → solo se ven sus fades de salida fantasma.
               En esos casos, render INLINE (horizontal o vertical según
               cantidad), sin destacar outliers — todo en línea. */
            let totalChars = 0;
            ordered.forEach(p => { totalChars += Math.max(2, p.length); });
            const lineDurSec = lineDurMs / 1000;
            const estimatedSpan = totalChars * SEC_PER_CHAR;
            const ENTRY_BUFFER_SEC = 0.7;
            /* Decisión cacheable: si la línea YA apareció antes con un
               estilo, lo reusamos para consistencia visual del coro.
               Si no, decidimos por isTooFast y guardamos. */
            let useInline;
            if (cached && cached.kind === 'repeat-inline') {
                useInline = true;
            } else if (cached && cached.kind === 'repeat-scatter') {
                useInline = false;
            } else {
                useInline = estimatedSpan + ENTRY_BUFFER_SEC > lineDurSec;
            }
            if (useInline) {
                /* INLINE MODE — todas las frases en wraps fill-stack o
                   fill-vert con delays escalonados → aparecen al ritmo
                   de la música, una tras otra, no todas a la vez. Sin
                   emphasis para outliers — todo al mismo nivel. */
                const allTexts = ordered.map(p => p.text);
                const layout = allTexts.length >= 4 ? 'v' : 'h';
                /* Cachea decisión para próximas apariciones del mismo verso. */
                if (!cached) pfLineStyleCache[lineKey] = { kind: 'repeat-inline', layout };
                /* Delays por longitud de frase. Mismo principio que el
                   scatter (chars × SEC_PER_CHAR) pero con compresión
                   para que el último entre antes del final de la línea.
                   Buffer 0.4s: la entrada del fill-* dura 0.85s con
                   opacity 1 al 60% = 0.51s — el último necesita ese
                   tiempo desde su delay hasta el final del verso. */
                let cumChars2 = 0;
                const rawDelays = ordered.map(p => {
                    const d = cumChars2 * SEC_PER_CHAR;
                    cumChars2 += Math.max(2, p.length);
                    return d;
                });
                const INLINE_BUFFER = 0.4;
                const maxSpan2 = Math.max(0.15, lineDurSec - INLINE_BUFFER);
                const lastRaw = rawDelays[rawDelays.length - 1] || 0;
                const inlineDelays = (lastRaw > maxSpan2 && lastRaw > 0)
                    ? rawDelays.map(d => d * (maxSpan2 / lastRaw))
                    : rawDelays;
                pfLog('  fast-repeat (inline-' + layout + '): ' + allTexts.length
                    + ' phrases, lineDur=' + lineDurSec.toFixed(2)
                    + 's delays=[' + inlineDelays.map(d => d.toFixed(2)).join(',') + ']');
                pfLineCounter++;
                root.classList.remove('pf-lyr-blurred');
                pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
                pfRenderInlineRepeat(allTexts, layout, inlineDelays);
                parens.asides.forEach(a => pfRenderAsideCorner(a));
                return;
            }
            /* Modo scatter normal — la línea tiene tiempo suficiente. */
            if (!cached) pfLineStyleCache[lineKey] = { kind: 'repeat-scatter' };
            let cumChars = 0;
            const scatterDelays = [];
            const outlierTimings = [];
            ordered.forEach(p => {
                const startTimeSec = cumChars * SEC_PER_CHAR;
                if (p.isOutlier) {
                    outlierTimings.push({ text: p.text, delaySec: startTimeSec });
                } else {
                    scatterDelays.push(startTimeSec);
                }
                cumChars += Math.max(2, p.length);
            });
            pfLog('  scatter: word="' + repeated.word + '" copies=' + repeated.count
                + ' outliers=' + outliers.length
                + ' scatterDelays=[' + scatterDelays.map(d => d.toFixed(2)).join(',') + ']');
            pfLineCounter++;
            root.classList.remove('pf-lyr-blurred');
            pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
            pfRenderScatter(repeated.word, repeated.count, {
                delaysSec: scatterDelays,
            });
            const expectedIdx = pfLyrCurIdx;
            outlierTimings.forEach(({ text, delaySec }) => {
                setTimeout(() => {
                    if (!pfLyricsOpen) return;
                    if (pfLyrCurIdx !== expectedIdx) return;
                    pfRenderEmphasis(text);
                }, delaySec * 1000);
            });
            parens.asides.forEach(a => pfRenderAsideCorner(a));
            return;
        }
        /* ── Detección de línea larga ──
           Thresholds independientes:
             - avoidVertical: para vert-left/right (1 char por fila).
               20 chars máx (más allá no cabe verticalmente en 90vh).
             - avoidStacking: para multi-modes (fill-vert/stack, fast).
               40 chars máx — el font es más chico, caben más. */
        const lineDur = pfGetCurLineDuration();
        let choice;
        const normalKey = pfLineKey(mainText);
        const normalCached = pfLineStyleCache[normalKey];
        if (normalCached && normalCached.kind === 'normal') {
            /* Repetición de un verso ya visto en modo single-line →
               reusamos su mode exacto para que el coro siempre se vea
               igual. Forzamos blur=false para no interferir con el
               intro/outro auto-toggle del picker. */
            choice = { mode: normalCached.mode, blur: false };
        } else {
            choice = pfPickMode({
                avoidVertical: mainText.length > 20 || lineDur > 6,
                avoidStacking: mainText.length > 40 || lineDur > 6,
            });
            /* Solo cacheamos modos SINGLE-LINE (los multi y fast-stack
               dependen del estado de grupo — no son seguros de reusar
               por línea aislada). choice.slot está sólo en multi/fast. */
            if (choice.slot === undefined) {
                pfLineStyleCache[normalKey] = { kind: 'normal', mode: choice.mode };
            }
        }
        pfLineCounter++;
        const isMulti     = (choice.slot !== undefined) && !choice.isFastStack;
        const isFastStack = !!choice.isFastStack;
        pfLog('  mode=' + choice.mode + ' blur=' + choice.blur + ' lineCounter=' + pfLineCounter
            + (isMulti     ? (' slot=' + choice.slot + '/' + choice.total + (choice.isLast?' (LAST)':'')) : '')
            + (isFastStack ? (' fast-slot=' + choice.slot + '/' + choice.total) : ''));
        if (choice.blur) root.classList.add('pf-lyr-blurred');
        else root.classList.remove('pf-lyr-blurred');

        /* Los asides (paréntesis en esquinas) NUNCA se limpian por el
           flujo principal — se autoextinguen tras PF_ASIDE_HOLD_MS.
           Por eso todos los selectores de limpieza excluyen .pf-lyr-mode-aside. */
        if (isFastStack) {
            pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-fast-stack):not(.pf-lyr-mode-aside)');
            pfFadeOutWraps('.pf-lyr-line-wrap.pf-lyr-mode-fast-stack[data-pf-slot="' + choice.slot + '"]');
        } else if (isMulti) {
            if (choice.slot === 0) {
                pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
            }
        } else {
            pfFadeOutWraps('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
        }

        const wrap = document.createElement('div');
        wrap.className = 'pf-lyr-line-wrap pf-lyr-mode-' + choice.mode;
        if (isMulti || isFastStack) {
            wrap.style.setProperty('--slot', String(choice.slot));
            wrap.style.setProperty('--total', String(choice.total));
            wrap.setAttribute('data-pf-slot', String(choice.slot));
        }
        wrap.innerHTML = pfRenderLine(mainText);
        lyrStage.appendChild(wrap);

        /* Schedule fade-out grupal del multi-mode al completar. */
        if (isMulti && choice.isLast) {
            const groupSelector = '.pf-lyr-line-wrap.pf-lyr-mode-' + choice.mode;
            setTimeout(() => pfFadeOutWraps(groupSelector), choice.holdMs);
        }

        /* Después de renderizar el main, añadimos los asides (si los hay)
           en esquinas — no reemplazan el main, son overlay adicional. */
        parens.asides.forEach(a => pfRenderAsideCorner(a));
    }

    /* Controller del fetch activo — al iniciar un nuevo fetch (track
       change) se aborta el anterior para no malgastar recursos ni
       sobreescribir el estado del track nuevo con la respuesta vieja. */
    let pfFetchController = null;

    async function pfFetchForCurrent() {
        /* Aborta el fetch anterior si existe — al cambiar de canción,
           la request vieja se cancela inmediatamente. */
        if (pfFetchController) {
            try { pfFetchController.abort(); } catch(_){}
        }
        const controller = new AbortController();
        pfFetchController = controller;

        const tr = pfCurTrack();
        if (!tr || !tr.videoId) {
            pfLog('fetch: no track or videoId');
            pfLyrLines = null; pfLyrVid = null; pfLyrCurIdx = -1;
            pfShowLine(null);
            return;
        }
        if (tr.videoId === pfLyrVid && pfLyrLines) {
            pfLog('fetch: cache hit, lines=' + pfLyrLines.length);
            pfLyrCurIdx = -2;
            if (pfLyricsOpen) pfTick();
            return;
        }
        pfLyrVid = tr.videoId; pfLyrLines = null; pfLyrCurIdx = -1;

        /* Espera hasta 3s a que el YT player reporte una duración real
           (>0). Sin esto, fetches inmediatos tras cambiar de track usan
           dur=0 y caen en cache miss (server-side cache key no incluye
           dur ya, pero LRCLIB hace una búsqueda menos precisa). */
        let dur = 0;
        const waitStart = Date.now();
        while (Date.now() - waitStart < 3000) {
            try {
                if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getDuration) {
                    dur = Math.floor(ytPlayer.getDuration() || 0);
                }
            } catch(_){}
            if (dur > 0) break;
            await new Promise(r => setTimeout(r, 200));
            /* Salidas tempranas — si nos abortaron o el track cambió. */
            if (controller.signal.aborted) { pfLog('fetch: aborted during dur-wait'); return; }
            const curT = pfCurTrack();
            if (!curT || curT.videoId !== tr.videoId) { pfLog('fetch: track changed during dur-wait'); return; }
        }

        const qs = new URLSearchParams({ title: tr.title || '', artist: tr.artist || '', duration: String(dur) });
        pfLog('fetch: GET title=' + (tr.title||'') + ' artist=' + (tr.artist||'') + ' dur=' + dur);
        try {
            const r = await fetch('assets/music/api.php?action=get-lyrics&' + qs.toString(), {
                credentials: 'same-origin',
                signal: controller.signal,
            });
            pfLog('fetch: response status=' + r.status);
            const d = await r.json();
            pfLog('fetch: response ok=' + (d&&d.ok) + ' found=' + (d&&d.found) + ' hasSynced=' + !!(d&&d.synced) + ' syncedLen=' + ((d&&d.synced||'').length));
            if (controller.signal.aborted) { pfLog('fetch: aborted post-response'); return; }
            if (pfLyrVid !== tr.videoId) { pfLog('fetch: track changed mid-fetch, ignoring'); return; }
            if (!d || !d.ok || !d.found || !d.synced) { pfLog('fetch: no synced data, returning'); return; }
            const parsed = pfParseLRC(d.synced);
            pfLog('fetch: parsed ' + parsed.length + ' synced lines');
            if (parsed.length > 0) {
                pfLog('fetch: first line @' + parsed[0].time + 's "' + parsed[0].text.substring(0, 40) + '"');
                pfLog('fetch: last  line @' + parsed[parsed.length-1].time + 's');
            }
            if (parsed.length) {
                pfLyrLines = parsed;
                pfLyrCurIdx = -2;
                if (pfLyricsOpen) pfTick();
            }
        } catch(e) {
            if (e && e.name === 'AbortError') {
                pfLog('fetch: aborted (track changed)');
                return;
            }
            pfLog('fetch: exception: ' + (e && e.message ? e.message : String(e)));
        } finally {
            /* Si este controller sigue siendo el activo, lo limpiamos.
               Si ya fue reemplazado por otro fetch, no lo tocamos. */
            if (pfFetchController === controller) pfFetchController = null;
        }
    }

    /* Tick: calcula qué línea (si alguna) debería estar visible AHORA.
       Detección de gap → durante huecos instrumentales largos no
       mostramos nada, pero más permisiva que antes (15s threshold).
       Heartbeat cada 5 ticks (1s) loguea estado del player + tiempo
       para diagnosticar si el sync funciona. */
    let pfTickCount = 0;
    function pfTick() {
        if (!pfLyricsOpen) return;
        if (!pfLyrLines || !pfLyrLines.length) {
            if (pfLyrCurIdx !== -1) { pfLyrCurIdx = -1; pfShowLine(null); }
            return;
        }
        let t = 0;
        try { if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getCurrentTime) t = ytPlayer.getCurrentTime() || 0; } catch(_){}
        /* Heartbeat — cada 5 ticks (1s) loguea estado actual. Solo
           durante los primeros 30s tras open para no llenar el log. */
        pfTickCount++;
        if (pfTickCount % 5 === 0 && pfTickCount <= 150) {
            let st = 'unknown';
            try {
                if (typeof ytPlayer !== 'undefined' && ytPlayer && ytPlayer.getPlayerState) {
                    const s = ytPlayer.getPlayerState();
                    st = ({'-1':'UNSTARTED','0':'ENDED','1':'PLAYING','2':'PAUSED','3':'BUFFERING','5':'CUED'})[String(s)] || ('STATE_' + s);
                }
            } catch(_){}
            pfLog('heartbeat: tick#' + pfTickCount + ' t=' + t.toFixed(2) + ' state=' + st + ' firstLine@=' + (pfLyrLines && pfLyrLines[0] ? pfLyrLines[0].time : '?'));
        }
        /* Binary search: última línea con time <= t. */
        let lo = 0, hi = pfLyrLines.length - 1, idx = -1;
        while (lo <= hi) {
            const mid = (lo + hi) >> 1;
            if (pfLyrLines[mid].time <= t) { idx = mid; lo = mid + 1; } else { hi = mid - 1; }
        }
        /* Detección de gap — relajada: solo ocultar si el gap a la
           siguiente es >15s Y ya pasaron 8s desde la actual. Antes era
           7s/3.5s lo que ocultaba demasiado durante canciones con
           pausas naturales entre versos. */
        let showIdx = idx;
        if (showIdx >= 0) {
            const cur  = pfLyrLines[showIdx];
            const next = pfLyrLines[showIdx + 1];
            const elapsed = t - cur.time;
            if (next) {
                const gap = next.time - cur.time;
                if (gap > 15 && elapsed > 8) showIdx = -1;
            } else {
                /* Última línea: ocultar después de 20s (outro largo). */
                if (elapsed > 20) showIdx = -1;
            }
        }
        if (showIdx !== pfLyrCurIdx) {
            pfLog('tick: t=' + t.toFixed(2) + ' rawIdx=' + idx + ' showIdx=' + showIdx + ' prevIdx=' + pfLyrCurIdx
                + (showIdx >= 0 ? ' line="' + pfLyrLines[showIdx].text.substring(0, 50) + '"' : ' (no line)'));
            pfLyrCurIdx = showIdx;
            pfShowLine(showIdx >= 0 ? pfLyrLines[showIdx].text : null);
        }
    }

    /* Toast con un solo texto (para "Sin letra…", etc). */
    function pfFlashStatus(msg, ms) {
        if (!lyrStage) return;
        const old = lyrStage.querySelector('.pf-lyr-status');
        if (old) old.remove();
        const el = document.createElement('div');
        el.className = 'pf-lyr-status';
        el.textContent = msg;
        lyrStage.appendChild(el);
        setTimeout(() => { el.classList.add('fade'); setTimeout(() => el.remove(), 500); }, ms || 1600);
    }
    /* Loader minimalista — solo 3 dots bounceando, sin texto.
       (El parámetro label se ignora; se mantiene por compatibilidad). */
    function pfFlashLoading(label, ms) {
        if (!lyrStage) return;
        const old = lyrStage.querySelector('.pf-lyr-status');
        if (old) old.remove();
        const el = document.createElement('div');
        el.className = 'pf-lyr-status pf-lyr-status-loading';
        const dotsWrap = document.createElement('span');
        dotsWrap.className = 'pf-loading-dots';
        for (let i = 0; i < 3; i++) {
            const d = document.createElement('span');
            d.className = 'pf-loading-dot';
            d.textContent = '.';
            dotsWrap.appendChild(d);
        }
        el.appendChild(dotsWrap);
        lyrStage.appendChild(el);
        setTimeout(() => { el.classList.add('fade'); setTimeout(() => el.remove(), 500); }, ms || 60000);
    }
    /* Toast estilo "Now Playing" — título grande + artista debajo.
       Se muestra al cargar lyrics correctamente. */
    function pfFlashTrack(title, artist, ms) {
        if (!lyrStage) return;
        const old = lyrStage.querySelector('.pf-lyr-status');
        if (old) old.remove();
        const el = document.createElement('div');
        el.className = 'pf-lyr-status pf-lyr-status-track';
        const tEl = document.createElement('div');
        tEl.className = 'pf-lyr-status-title';
        tEl.textContent = title || '—';
        const aEl = document.createElement('div');
        aEl.className = 'pf-lyr-status-artist';
        aEl.textContent = artist || '';
        el.appendChild(tEl);
        if (artist) el.appendChild(aEl);
        lyrStage.appendChild(el);
        setTimeout(() => { el.classList.add('fade'); setTimeout(() => el.remove(), 500); }, ms || 2600);
    }

    function pfUpdateBtnState() {
        if (!btnLyr) return;
        btnLyr.classList.toggle('is-on', pfLyricsOpen);
        btnLyr.setAttribute('aria-pressed', pfLyricsOpen ? 'true' : 'false');
    }

    function pfOpenLyrics() {
        root.classList.add('pf-lyrics-active');
        pfLyricsOpen = true;
        pfUpdateBtnState();
        pfLyrCurIdx = -2;
        const tr = pfCurTrack();
        /* Captura videoId al abrir — el .then() compara contra esto para
           descartar callbacks de fetches abortados de canciones previas
           (que devuelven igual via Promise resolution). */
        const openedFor = tr && tr.videoId;
        try { console.log('[pf-lyrics] open. track=', tr && tr.title, 'videoId=', tr && tr.videoId, 'cached=', tr && tr.videoId === pfLyrVid && !!pfLyrLines); } catch(_){}
        if (!pfLyrLines || !tr || tr.videoId !== pfLyrVid) {
            pfFlashLoading('🎤 Buscando letra', 60000);   /* persiste hasta cambio */
        }
        pfFetchForCurrent().then(() => {
            if (!pfLyricsOpen) return;
            /* Guard contra stale callback: si el track cambió mientras
               el fetch estaba en vuelo, este callback es de la canción
               vieja — el reload de la nueva ya está corriendo. */
            const tNow = pfCurTrack();
            if (!tNow || tNow.videoId !== openedFor) {
                try { console.log('[pf-lyrics] open.then: stale (track changed)'); } catch(_){}
                return;
            }
            try { console.log('[pf-lyrics] fetch done. lines=', (pfLyrLines||[]).length); } catch(_){}
            if (!pfLyrLines || !pfLyrLines.length) {
                pfFlashStatus('🥲 Sin letra sincronizada para esta canción', 8000);
                return;
            }
            /* Si al activar las letras ya hay una línea visible (porque
               la canción está en mitad de un verso y pfTick disparó
               showLine en cache hit), NO mostramos el toast "Now
               Playing" — taparía el verso ya visible. Solo cleanup del
               loading toast. */
            const lineVisible = lyrStage && lyrStage.querySelector('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
            if (lineVisible) {
                const status = lyrStage.querySelector('.pf-lyr-status');
                if (status) status.remove();
                return;
            }
            const t = pfCurTrack();
            pfFlashTrack(t && t.title || 'Canción', t && t.artist || '', 2800);
        });
        if (pfLyrTimer) clearInterval(pfLyrTimer);
        pfLyrTimer = setInterval(pfTick, 200);
    }
    function pfCloseLyrics() {
        root.classList.remove('pf-lyrics-active');
        root.classList.remove('pf-lyr-blurred');
        pfLyricsOpen = false;
        pfUpdateBtnState();
        if (pfLyrTimer) { clearInterval(pfLyrTimer); pfLyrTimer = null; }
        pfLyrCurIdx = -1;
        pfLineCounter = 0;
        pfMultiState = null;
        pfFastStackCounter = 0;
        pfFastStackStartedAt = 0;
        pfAsideCounter = 0;
        pfRepeatLineGroup = null;
        pfLineStyleCache = {};  /* cache vacío por canción */
        /* Cancela cualquier fetch en vuelo al cerrar. */
        if (pfFetchController) { try { pfFetchController.abort(); } catch(_){} pfFetchController = null; }
        /* Cancela debounce de reload pendiente. */
        if (pfReloadDebounceTimer) { clearTimeout(pfReloadDebounceTimer); pfReloadDebounceTimer = null; }
        if (lyrStage) lyrStage.innerHTML = '';
    }

    /* Botón micrófono: toggle on/off + visual feedback (.is-on). */
    if (btnLyr) btnLyr.addEventListener('click', function() {
        if (pfLyricsOpen) pfCloseLyrics();
        else pfOpenLyrics();
    });
    /* Debounce del reload — si el usuario salta rápido entre canciones,
       no queremos disparar un fetch + render por cada skip. Solo cuando
       la canción se "asienta" 1.5s arrancamos la carga real. */
    let pfReloadDebounceTimer = null;
    const PF_RELOAD_DEBOUNCE_MS = 1500;

    /* Limpieza ligera inmediata al cambiar de track — quita los wraps
       visibles SIN animación (mucho más rápido que pfFadeOutWraps que
       crea 50+ animations CSS por wrap). Asides se preservan. */
    function pfQuickClearStage() {
        if (!lyrStage) return;
        const els = lyrStage.querySelectorAll('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
        els.forEach(el => el.remove());
        const status = lyrStage.querySelector('.pf-lyr-status');
        if (status) status.remove();
    }

    /* Cuando cambia el track con las letras abiertas:
       1. INMEDIATO: cancela fetch en vuelo + clear DOM + loading dots
          + reset de TODO el estado de letra previa.
       2. DEBOUNCED: tras 1.5s sin más cambios, arranca el fetch real
          de la nueva canción.
       Si el usuario salta otra vez antes de 1.5s, el timer se cancela
       y se reinicia → solo trabajo de carga REAL cuando el usuario
       "se queda" en una canción. */
    function pfReloadForTrack() {
        if (!pfLyricsOpen) return;

        /* === FASE 1 inmediata: cleanup ligero === */

        /* Cancela el debounce pendiente si existía. */
        if (pfReloadDebounceTimer) {
            clearTimeout(pfReloadDebounceTimer);
            pfReloadDebounceTimer = null;
        }
        /* Cancela cualquier fetch en vuelo. */
        if (pfFetchController) {
            try { pfFetchController.abort(); } catch(_){}
            pfFetchController = null;
        }
        /* Reset completo del estado de letra previa. */
        pfLyrLines = null;
        pfLyrVid = null;
        pfLyrCurIdx = -2;
        pfLineCounter = 0;
        pfMultiState = null;
        pfFastStackCounter = 0;
        pfFastStackStartedAt = 0;
        pfRepeatLineGroup = null;
        pfLineStyleCache = {};  /* cada canción tiene su propio cache */
        root.classList.remove('pf-lyr-blurred');
        /* DOM cleanup INSTANTÁNEO (sin animación). */
        pfQuickClearStage();
        /* Loading dots como feedback visual mientras se debouncea. */
        pfFlashLoading('', 60000);

        /* === FASE 2 debounced: fetch real tras 1.5s === */

        pfReloadDebounceTimer = setTimeout(() => {
            pfReloadDebounceTimer = null;
            if (!pfLyricsOpen) return;
            const tr = pfCurTrack();
            pfLog('reloadForTrack (debounced): ' + (tr ? (tr.title + ' / ' + tr.videoId) : 'no-track'));
            if (!tr || !tr.videoId) {
                /* Sin track activo → solo quita el loading. */
                const st = lyrStage && lyrStage.querySelector('.pf-lyr-status');
                if (st) st.remove();
                return;
            }
            /* Captura videoId — el .then() compara contra esto para
               descartar callbacks stale si el usuario salta otra vez
               durante el fetch. */
            const reloadFor = tr.videoId;
            pfFetchForCurrent().then(() => {
                if (!pfLyricsOpen) return;
                /* Guard stale callback: si saltaron a otra canción mientras
                   el fetch estaba en vuelo, este callback es de la vieja —
                   un reload nuevo está en camino, no spameamos el toast. */
                const tNow = pfCurTrack();
                if (!tNow || tNow.videoId !== reloadFor) {
                    pfLog('reloadForTrack.then: stale (track changed)');
                    return;
                }
                if (!pfLyrLines || !pfLyrLines.length) {
                    pfFlashStatus('🥲 Sin letra sincronizada para esta canción', 8000);
                } else {
                    /* No mostrar el toast "Now Playing" si ya hay una
                       línea visible (cache hit con el reproductor en
                       mitad de un verso). */
                    const lineVisible = lyrStage && lyrStage.querySelector('.pf-lyr-line-wrap:not(.pf-lyr-mode-aside)');
                    if (!lineVisible) {
                        pfFlashTrack(tNow.title || 'Canción', tNow.artist || '', 2800);
                    } else {
                        const st = lyrStage.querySelector('.pf-lyr-status');
                        if (st) st.remove();
                    }
                }
            });
        }, PF_RELOAD_DEBOUNCE_MS);
    }

    /* Hook a updateTrackUI: al cambiar de track con lyrics abiertas,
       dispara el reload completo. */
    const __origUpdateTrackUI2 = (typeof window.updateTrackUI === 'function') ? window.updateTrackUI : null;
    if (__origUpdateTrackUI2) {
        window.updateTrackUI = function(idx) {
            __origUpdateTrackUI2(idx);
            if (pfLyricsOpen) pfReloadForTrack();
        };
    }

    /* ── Right-click menu ──
       Mismo menú contextual que la playlist (Añadir a playlist /
       Añadir a perfil / Escuchar juntos), accesible con right-click
       en cualquier parte del player pequeño Y del fullscreen. */
    function getCurTrackForCtx() {
        if (typeof playlist !== 'undefined' && playlist.length && typeof currentTrack === 'number') {
            return playlist[currentTrack];
        }
        return null;
    }
    function attachCtxMenu(el) {
        if (!el) return;
        el.addEventListener('contextmenu', function(e) {
            const tr = getCurTrackForCtx();
            if (!tr) return;
            if (typeof window.openTrackCtxMenu === 'function') {
                window.openTrackCtxMenu(e, tr);
            }
        });
    }
    /* Player pequeño: cualquier zona excepto los botones interactivos. */
    attachCtxMenu(document.getElementById('player-titlebar'));
    attachCtxMenu(document.getElementById('player-content'));
    attachCtxMenu(document.getElementById('player-cover-wrap'));
    /* Fullscreen: title-bar + body (que cubre el contenido entero del
       overlay). Los selectors antiguos .pf-display y .pf-info ya no
       existían en el HTML actualizado, por eso el menú no aparecía. */
    attachCtxMenu(document.querySelector('#player-full .pf-titlebar'));
    attachCtxMenu(document.querySelector('#player-full .pf-body'));
    attachCtxMenu(document.querySelector('#player-full .pf-lcd'));
    attachCtxMenu(document.querySelector('#player-full .pf-vinyl-floating'));
})();
<?php endif; ?>
</script>
