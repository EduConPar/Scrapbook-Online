cat > /mnt/user-data/outputs/engine.js << 'JSEOF'
/**
 * assets/mascota/engine.js  — v2 (Shimeji edition)
 *
 * Usa frames individuales en vez de spritesheets:
 *   assets/mascota/skins/{skin}/shime1.png … shimeN.png
 *
 * Animaciones mapeadas desde actions.xml:
 *   idle    → [1]
 *   walk    → [1,2,1,3]
 *   sit     → [11]
 *   sad     → [21]          (sprawl, hambre baja)
 *   fall    → [4,5,6,7]
 *   bounce  → [8,9,10]
 *   drag    → [34,35,36,37]
 *   wall    → [13]
 *   ceiling → [23]
 *   eat     → [26,15,27,16,28,17,29,11]  (SitAndSpinHead → comer)
 *   play    → [42,43,44,45,46]           (Divide1 anim, aspecto de acción)
 *   dead    → [21]          (igual que sad pero permanente)
 */

(function (global) {
    'use strict';

    /* ═══════════════════════════════════════════════════════════════
       CONFIGURACIÓN
    ═══════════════════════════════════════════════════════════════ */
    var CFG = {
        /* Tamaño real de cada PNG del shimeji (128×128 px originales) */
        IMG_W:          128,
        IMG_H:          128,
        SCALE:          1,          /* 1 = tamaño original; 1.5 o 2 si se quiere más grande */
        API:            'assets/mascota/api.php',
        HEARTBEAT_MS:   5 * 60 * 1000,
        WALK_SPEED:     2,
        ANIM_TICK_MS:   80,          /* ms por frame (≈12 fps) */
        BUBBLE_MS:      4000,
        IDLE_TIMEOUT_MS:6000,
        WINDOW_SCAN_MS: 2000,

        /* Definición de animaciones: array de números de frame */
        ANIMS: {
            idle:    { frames: [1],                         loop: true  },
            walk:    { frames: [1, 2, 1, 3],                loop: true  },
            sit:     { frames: [11],                        loop: true  },
            sad:     { frames: [21],                        loop: true  },
            dead:    { frames: [21],                        loop: true  },
            fall:    { frames: [4, 5, 6, 7],                loop: true  },
            bounce:  { frames: [8, 9, 10],                  loop: false },
            drag:    { frames: [34, 35, 36, 37],            loop: true  },
            wall:    { frames: [13],                        loop: true  },
            ceiling: { frames: [23],                        loop: true  },
            eat:     { frames: [26,15,27,16,28,17,29,11],   loop: false },
            play:    { frames: [42,43,44,45,46],            loop: false },
        },
    };

    var SIZE_W = CFG.IMG_W * CFG.SCALE;
    var SIZE_H = CFG.IMG_H * CFG.SCALE;

    /* ═══════════════════════════════════════════════════════════════
       ESTADO INTERNO
    ═══════════════════════════════════════════════════════════════ */
    var state = {
        userId:    0,
        parejaId:  0,
        label:     '',
        mascota:   null,
        memoria:   {},
        skin:      'meloncio',

        x: 100, y: 0,
        targetX: 0,
        moving: false,
        facingRight: true,
        dragging: false,
        dragOffX: 0, dragOffY: 0,

        currentAnim: 'idle',
        frameIdx: 0,          /* índice dentro del array de frames de la anim actual */
        lastFrameTime: 0,
        animDone: false,      /* true cuando una anim non-loop termina */

        idleTimer: 0,
        windowScanTimer: 0,
        heartbeatTimer: 0,
        pendingPreguntas: [],
        bubbleActive: false,
        bubbleTimer: null,
        lastOpenApp: null,

        el: null,
        imgEl: null,          /* <img> que muestra el frame actual */
        bubbleEl: null,
        hudEl: null,

        /* Cache de imágenes: { 'skin/1': HTMLImageElement, … } */
        imgCache: {},
        skinBase: '',
    };

    /* ═══════════════════════════════════════════════════════════════
       API HELPERS
    ═══════════════════════════════════════════════════════════════ */
    function apiFetch(action, data, cb) {
        var url = CFG.API + '?action=' + encodeURIComponent(action);
        var opts = { method: 'GET' };
        if (data) {
            opts.method  = 'POST';
            opts.headers = { 'Content-Type': 'application/json' };
            opts.body    = JSON.stringify(data);
        }
        fetch(url, opts)
            .then(function (r) { return r.json(); })
            .then(function (d) { if (cb) cb(null, d); })
            .catch(function (e) { if (cb) cb(e, null); });
    }

    /* ═══════════════════════════════════════════════════════════════
       SPRITES — precarga de los frames que se van a usar
    ═══════════════════════════════════════════════════════════════ */
    function skinBase(skin) {
        return 'assets/mascota/skins/' + skin + '/';
    }

    function frameUrl(frameNum) {
        return state.skinBase + 'shime' + frameNum + '.png';
    }

    /** Devuelve la imagen del frame (del caché o la crea). */
    function getImg(frameNum) {
        var key = state.skinBase + frameNum;
        if (state.imgCache[key]) return state.imgCache[key];
        var img = new Image();
        img.src = frameUrl(frameNum);
        state.imgCache[key] = img;
        return img;
    }

    /** Precarga todos los frames únicos de todas las animaciones. */
    function preloadFrames(skin, onDone) {
        state.skinBase = skinBase(skin);
        var allNums = new Set();
        Object.values(CFG.ANIMS).forEach(function (a) {
            a.frames.forEach(function (n) { allNums.add(n); });
        });
        var total   = allNums.size;
        var loaded  = 0;
        allNums.forEach(function (n) {
            var img = new Image();
            img.onload = img.onerror = function () {
                state.imgCache[state.skinBase + n] = img;
                if (++loaded === total && onDone) onDone();
            };
            img.src = frameUrl(n);
        });
        if (total === 0 && onDone) onDone();
    }

    /* ═══════════════════════════════════════════════════════════════
       DOM
    ═══════════════════════════════════════════════════════════════ */
    function buildDOM() {
        var root = document.getElementById('mascota-root');
        if (!root) {
            root = document.createElement('div');
            root.id = 'mascota-root';
            document.body.appendChild(root);
        }
        root.innerHTML = '';
        root.style.cssText = [
            'position:fixed',
            'bottom:0',
            'left:0',
            'width:100vw',
            'height:calc(100vh - 42px)',
            'pointer-events:none',
            'z-index:8000',
            'overflow:hidden',
        ].join(';');

        /* Contenedor de la entidad */
        var el = document.createElement('div');
        el.id = 'mascota-entity';
        el.style.cssText = [
            'position:absolute',
            'width:'  + SIZE_W + 'px',
            'height:' + SIZE_H + 'px',
            'cursor:pointer',
            'pointer-events:all',
            'user-select:none',
            'image-rendering:pixelated',
            '-ms-interpolation-mode:nearest-neighbor',
        ].join(';');
        root.appendChild(el);

        /* Imagen del frame actual */
        var img = document.createElement('img');
        img.id    = 'mascota-sprite';
        img.style.cssText = [
            'width:'  + SIZE_W + 'px',
            'height:' + SIZE_H + 'px',
            'display:block',
            'image-rendering:pixelated',
            '-ms-interpolation-mode:nearest-neighbor',
            'transform:scaleX(1)',
            'transition:transform 0.1s',
        ].join(';');
        img.draggable = false;
        el.appendChild(img);

        /* Burbuja de diálogo */
        var bubble = document.createElement('div');
        bubble.id = 'mascota-bubble';
        bubble.style.cssText = [
            'position:absolute',
            'bottom:' + (SIZE_H + 4) + 'px',
            'left:50%',
            'transform:translateX(-50%)',
            'background:#fff',
            'border:2px solid #000',
            'padding:6px 10px',
            'font-size:11px',
            'font-family:var(--font-ui,"MS Sans Serif",sans-serif)',
            'white-space:normal',
            'max-width:220px',
            'text-align:center',
            'line-height:1.35',
            'pointer-events:none',
            'opacity:0',
            'transition:opacity 0.2s',
            'z-index:1',
            'box-shadow:2px 2px 0 #888',
        ].join(';');
        el.appendChild(bubble);

        /* HUD barras */
        var hud = document.createElement('div');
        hud.id = 'mascota-hud';
        hud.style.cssText = [
            'position:absolute',
            'top:' + (SIZE_H + 6) + 'px',
            'left:50%',
            'transform:translateX(-50%)',
            'display:flex',
            'flex-direction:column',
            'gap:2px',
            'pointer-events:none',
            'opacity:0',
            'transition:opacity 0.2s',
            'z-index:1',
        ].join(';');
        hud.innerHTML = [
            '<div style="display:flex;align-items:center;gap:4px;">',
                '<span style="font-size:9px;width:10px;">🍖</span>',
                '<div style="width:60px;height:8px;background:#ccc;border:1px solid #888;">',
                    '<div id="mascota-bar-hambre-fill" style="height:100%;background:#0a0;width:80%;transition:width 0.4s;"></div>',
                '</div>',
            '</div>',
            '<div style="display:flex;align-items:center;gap:4px;">',
                '<span style="font-size:9px;width:10px;">♥</span>',
                '<div style="width:60px;height:8px;background:#ccc;border:1px solid #888;">',
                    '<div id="mascota-bar-happy-fill" style="height:100%;background:#e05;width:80%;transition:width 0.4s;"></div>',
                '</div>',
            '</div>',
        ].join('');
        el.appendChild(hud);

        state.el       = el;
        state.imgEl    = img;
        state.bubbleEl = bubble;
        state.hudEl    = hud;

        el.addEventListener('mousedown',  onEntityMouseDown);
        el.addEventListener('touchstart', onEntityTouchStart, { passive: false });
        el.addEventListener('click',      onEntityClick);
        el.addEventListener('mouseenter', function () { state.hudEl.style.opacity = '1'; });
        el.addEventListener('mouseleave', function () { if (!state.dragging) state.hudEl.style.opacity = '0'; });
    }

    /* ═══════════════════════════════════════════════════════════════
       RENDER — actualiza el <img> al frame correcto
    ═══════════════════════════════════════════════════════════════ */
    function renderFrame() {
        var anim   = CFG.ANIMS[state.currentAnim] || CFG.ANIMS.idle;
        var frameNum = anim.frames[state.frameIdx] || anim.frames[0];
        var img    = getImg(frameNum);
        if (state.imgEl && state.imgEl.src !== img.src) {
            state.imgEl.src = img.src;
        }
        /* Espejo horizontal al caminar hacia la derecha.
           El shimeji original camina hacia la IZQUIERDA (Velocity=-2,0),
           así que mirando a la derecha hay que flipear. */
        if (state.imgEl) {
            state.imgEl.style.transform = state.facingRight ? 'scaleX(-1)' : 'scaleX(1)';
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       ANIMACIÓN — tick
    ═══════════════════════════════════════════════════════════════ */
    function animTick(ts) {
        if (!state.lastFrameTime) state.lastFrameTime = ts;
        var elapsed = ts - state.lastFrameTime;

        if (elapsed >= CFG.ANIM_TICK_MS) {
            state.lastFrameTime = ts;
            var anim = CFG.ANIMS[state.currentAnim] || CFG.ANIMS.idle;
            var next = state.frameIdx + 1;
            if (next >= anim.frames.length) {
                if (anim.loop) {
                    state.frameIdx = 0;
                } else {
                    state.frameIdx  = anim.frames.length - 1;
                    state.animDone  = true;
                }
            } else {
                state.frameIdx = next;
            }
        }
        renderFrame();
        requestAnimationFrame(animTick);
    }

    /* ═══════════════════════════════════════════════════════════════
       MOVIMIENTO Y COMPORTAMIENTO
    ═══════════════════════════════════════════════════════════════ */
    function setAnim(anim) {
        if (state.currentAnim === anim) return;
        state.currentAnim = anim;
        state.frameIdx    = 0;
        state.animDone    = false;
    }

    function applyPosition() {
        if (!state.el) return;
        state.el.style.left = state.x + 'px';
        state.el.style.top  = state.y + 'px';
    }

    function groundY() {
        return (window.innerHeight - 42) - SIZE_H;
    }

    function pickRandomTarget() {
        var margin = 10;
        var maxX   = window.innerWidth - SIZE_W - margin;
        state.targetX = margin + Math.random() * maxX;

        /* 25% de probabilidad de ir a sentarse encima de una ventana */
        if (Math.random() < 0.25) {
            var wins = getOpenWindowRects();
            if (wins.length) {
                var win = wins[Math.floor(Math.random() * wins.length)];
                state.targetX = win.left + Math.random() * Math.max(0, win.width - SIZE_W);
                state.y = Math.max(0, win.top - SIZE_H + 4);
                applyPosition();
                return;
            }
        }
        state.y = groundY();
        applyPosition();
    }

    function moveTick() {
        if (!state.mascota || !state.mascota.viva) return;
        if (state.dragging) return;

        var hambre = state.mascota.hambre;

        if (!state.mascota.viva)    { setAnim('dead');  return; }
        if (hambre < 25)            { setAnim('sad');   return; }

        if (state.moving) {
            var dx   = state.targetX - state.x;
            var dist = Math.abs(dx);

            if (dist < 3) {
                state.moving    = false;
                state.idleTimer = 0;
                /* 50% sentarse, 50% idle */
                setAnim(Math.random() < 0.5 ? 'sit' : 'idle');
                return;
            }

            state.facingRight = dx > 0;
            var step = Math.min(CFG.WALK_SPEED, dist);
            state.x += state.facingRight ? step : -step;
            setAnim('walk');
            applyPosition();
        } else {
            state.idleTimer += 16;
            if (state.idleTimer > CFG.IDLE_TIMEOUT_MS) {
                state.idleTimer = 0;
                if (Math.random() < 0.6) {
                    pickRandomTarget();
                    state.moving = true;
                } else {
                    /* Quedarse sentado o idle un rato */
                    setAnim(Math.random() < 0.5 ? 'sit' : 'idle');
                }
            }
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       INTERACCIÓN CON VENTANAS
    ═══════════════════════════════════════════════════════════════ */
    function getOpenWindowRects() {
        var rects = [];
        document.querySelectorAll('.window').forEach(function (w) {
            if (w.hasAttribute('data-no-auto-z')) return;
            if (!w.style.display || w.style.display === 'none') return;
            var r = w.getBoundingClientRect();
            if (r.width > 0 && r.height > 0) rects.push(r);
        });
        return rects;
    }

    function scanWindows() {
        if (!state.mascota || !state.mascota.viva) return;
        var apps = [
            { id: 'temas-window',    app: 'temas'     },
            { id: 'calendar-window', app: 'calendario' },
            { id: 'archive-window',  app: 'archivo'   },
            { id: 'galeria-window',  app: 'galeria'   },
            { id: 'tienda-window',   app: 'tienda'    },
        ];
        var openApps = [];
        apps.forEach(function (e) {
            var el = document.getElementById(e.id);
            if (el && el.style.display && el.style.display !== 'none') openApps.push(e.app);
        });

        if (openApps.length && Math.random() < 0.15) {
            var app = openApps[Math.floor(Math.random() * openApps.length)];
            var mem = state.memoria;
            var msg = null;
            if (app === 'temas'     && mem.color_favorito)  msg = '¿Por qué no haces un tema ' + mem.color_favorito + '? 🎨';
            else if (app === 'calendario' && mem.juego_favorito) msg = '¡Anota cuando salga algo de ' + mem.juego_favorito + '!';
            else if (app === 'galeria')  msg = '¡Ooh, fotos! 📷';
            else if (app === 'tienda')   msg = '¡No gastes todo! 💰';

            if (msg && app !== state.lastOpenApp) {
                state.lastOpenApp = app;
                showBubble(msg);
            }
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       BURBUJA
    ═══════════════════════════════════════════════════════════════ */
    function showBubble(text, duration) {
        if (!state.bubbleEl) return;
        clearTimeout(state.bubbleTimer);
        state.bubbleEl.textContent = text;
        state.bubbleEl.style.opacity = '1';
        state.bubbleActive = true;
        var ms = (duration === 0) ? 0 : (duration || CFG.BUBBLE_MS);
        if (ms > 0) {
            state.bubbleTimer = setTimeout(function () {
                state.bubbleEl.style.opacity = '0';
                state.bubbleActive = false;
            }, ms);
        }
    }

    function hideBubble() {
        clearTimeout(state.bubbleTimer);
        if (state.bubbleEl) state.bubbleEl.style.opacity = '0';
        state.bubbleActive = false;
    }

    /* ═══════════════════════════════════════════════════════════════
       PREGUNTAS PERSONALES
    ═══════════════════════════════════════════════════════════════ */
    var PREGUNTAS_MAP = {
        comida_favorita:  '¿Cuál es tu comida favorita? 🍕',
        color_favorito:   '¿Tienes un color favorito? 🎨',
        cancion_favorita: '¿Qué canción no puedes sacarte de la cabeza? 🎵',
        juego_favorito:   '¿Cuál es tu juego favorito? 🎮',
        mascota_favorita: '¿Qué tipo de mascota te gusta? 🐾',
    };

    function askNextPregunta() {
        if (!state.pendingPreguntas.length) return;
        var clave = state.pendingPreguntas[0];
        var texto = PREGUNTAS_MAP[clave] || '¿Me cuentas algo de ti? 🤔';
        showBubble(texto, 0);
        if (typeof window.win98Prompt === 'function') {
            window.win98Prompt(
                texto, '',
                function (val) {
                    val = (val || '').trim();
                    if (!val) { hideBubble(); return; }
                    apiFetch('save-memoria', { clave: clave, valor: val }, function (err, d) {
                        if (!err && d && d.ok) {
                            state.memoria[clave] = val;
                            state.pendingPreguntas.shift();
                            showBubble('¡Anotado! Lo recordaré 😊');
                        }
                    });
                },
                function () { hideBubble(); },
                'Una pregunta de ' + (state.mascota ? state.mascota.nombre : 'tu mascota')
            );
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       EVENTOS — drag y click
    ═══════════════════════════════════════════════════════════════ */
    function onEntityMouseDown(e) {
        if (e.button !== 0) return;
        e.preventDefault();
        startDrag(e.clientX, e.clientY);
    }

    function onEntityTouchStart(e) {
        if (e.touches.length !== 1) return;
        e.preventDefault();
        startDrag(e.touches[0].clientX, e.touches[0].clientY);
    }

    function startDrag(cx, cy) {
        state.dragging = true;
        state.moving   = false;
        setAnim('drag');
        var r = state.el.getBoundingClientRect();
        state.dragOffX = cx - r.left;
        state.dragOffY = cy - r.top;

        function onMove(e) {
            var px = e.clientX !== undefined ? e.clientX : (e.touches && e.touches[0].clientX);
            var py = e.clientY !== undefined ? e.clientY : (e.touches && e.touches[0].clientY);
            if (px === undefined) return;
            state.x = Math.max(0, Math.min(window.innerWidth  - SIZE_W, px - state.dragOffX));
            state.y = Math.max(0, Math.min(window.innerHeight - 42 - SIZE_H, py - state.dragOffY));
            applyPosition();
        }
        function onUp() {
            state.dragging = false;
            setAnim('fall');
            /* Pequeño bounce al soltar */
            setTimeout(function () {
                state.y = groundY();
                applyPosition();
                setAnim('bounce');
                setTimeout(function () { setAnim('idle'); }, 400);
            }, 150);
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup',   onUp);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend',  onUp);
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup',   onUp);
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('touchend',  onUp);
    }

    function onEntityClick() {
        if (state.dragging) return;
        if (!state.mascota) return;

        if (!state.mascota.viva) {
            if (typeof window.win98Confirm === 'function') {
                window.win98Confirm(
                    state.mascota.nombre + ' ha muerto de hambre...\n¿Quieres revivirla?',
                    'Oh no...',
                    function () {
                        apiFetch('reset-death', {}, function (err, d) {
                            if (!err && d && d.ok) {
                                state.mascota.viva      = true;
                                state.mascota.hambre    = 30;
                                state.mascota.felicidad = 20;
                                updateHUD();
                                setAnim('sad');
                                showBubble('...estoy viva. Tengo hambre 😢');
                            }
                        });
                    }
                );
            }
            return;
        }
        showPetMenu();
    }

    /* ═══════════════════════════════════════════════════════════════
       MENÚ CONTEXTUAL
    ═══════════════════════════════════════════════════════════════ */
    function showPetMenu() {
        var existing = document.getElementById('mascota-ctx-menu');
        if (existing) existing.remove();

        var menu = document.createElement('ul');
        menu.id = 'mascota-ctx-menu';
        menu.className = 'desk-ctx show';
        menu.style.cssText = 'position:fixed;z-index:99999;min-width:140px;';

        [
            { icon: '🍕', label: 'Alimentar',    action: 'feed'   },
            { icon: '⚽', label: 'Jugar',         action: 'play'   },
            { icon: '❓', label: 'Preguntar algo', action: 'ask'    },
            { icon: '💬', label: '¿Cómo estás?',  action: 'status' },
        ].forEach(function (item) {
            var li = document.createElement('li');
            li.textContent = item.icon + ' ' + item.label;
            li.style.cursor = 'pointer';
            li.addEventListener('click', function () { menu.remove(); handleMenuAction(item.action); });
            menu.appendChild(li);
        });

        var r  = state.el.getBoundingClientRect();
        var mx = Math.min(r.right + 4, window.innerWidth  - 160);
        var my = Math.min(r.top,       window.innerHeight - 180);
        menu.style.left = mx + 'px';
        menu.style.top  = my + 'px';
        document.body.appendChild(menu);

        setTimeout(function () {
            document.addEventListener('click', function close() {
                var m = document.getElementById('mascota-ctx-menu');
                if (m) m.remove();
                document.removeEventListener('click', close);
            });
        }, 0);
    }

    function handleMenuAction(action) {
        switch (action) {
            case 'feed':
                if (typeof window.win98Prompt === 'function') {
                    window.win98Prompt(
                        '¿Qué le das de comer a ' + state.mascota.nombre + '?',
                        state.memoria.comida_favorita || '',
                        function (val) {
                            val = (val || '').trim() || 'algo rico';
                            apiFetch('feed', { comida: val }, function (err, d) {
                                if (!err && d && d.ok) {
                                    state.mascota.hambre    = d.hambre;
                                    state.mascota.felicidad = d.felicidad;
                                    updateHUD();
                                    setAnim('eat');
                                    var msg = d.bonus_fav ? '¡' + val + '! ¡Mi favorita! 😍' : '¡Mmm, gracias! 😋';
                                    setTimeout(function () { showBubble(msg); }, 300);
                                    /* Volver a idle cuando termine la anim eat */
                                    waitAnimDone(function () { setAnim('idle'); });
                                }
                            });
                        }
                    );
                }
                break;

            case 'play':
                apiFetch('play', {}, function (err, d) {
                    if (!err && d && d.ok) {
                        state.mascota.hambre    = d.hambre;
                        state.mascota.felicidad = d.felicidad;
                        updateHUD();
                        setAnim('play');
                        showBubble('¡Yuhu! ⚽');
                        waitAnimDone(function () { setAnim('idle'); });
                    } else if (!err && d && d.error) {
                        showBubble(d.error);
                    }
                });
                break;

            case 'ask':
                if (state.pendingPreguntas.length) {
                    askNextPregunta();
                } else {
                    showBubble('Ya sé todo lo que necesito 😊');
                }
                break;

            case 'status':
                var h = state.mascota.hambre;
                var f = state.mascota.felicidad;
                var msg;
                if (h > 70 && f > 70)  msg = '¡Estoy genial! 😄';
                else if (h < 25)        msg = 'Tengo muuucha hambre... 😢';
                else if (f < 30)        msg = 'Me siento sola últimamente 😔';
                else                    msg = 'Estoy bien 🙂 (hambre:' + h + ' ánimo:' + f + ')';
                showBubble(msg, 6000);
                break;
        }
    }

    /** Espera a que la animación actual (non-loop) termine y llama cb. */
    function waitAnimDone(cb) {
        var anim = CFG.ANIMS[state.currentAnim];
        if (!anim || anim.loop) { setTimeout(cb, 1000); return; }
        var totalMs = anim.frames.length * CFG.ANIM_TICK_MS + 100;
        setTimeout(cb, totalMs);
    }

    /* ═══════════════════════════════════════════════════════════════
       HUD
    ═══════════════════════════════════════════════════════════════ */
    function updateHUD() {
        if (!state.mascota) return;
        var hFill = document.getElementById('mascota-bar-hambre-fill');
        var fFill = document.getElementById('mascota-bar-happy-fill');
        if (hFill) {
            hFill.style.width = state.mascota.hambre + '%';
            var h = state.mascota.hambre;
            hFill.style.background = h > 60 ? '#0a0' : h > 30 ? '#f90' : '#e05';
        }
        if (fFill) fFill.style.width = state.mascota.felicidad + '%';
    }

    /* ═══════════════════════════════════════════════════════════════
       HEARTBEAT
    ═══════════════════════════════════════════════════════════════ */
    function heartbeat() {
        apiFetch('heartbeat', null, function (err, d) {
            if (!err && d && d.ok && d.mascota) {
                state.mascota = d.mascota;
                updateHUD();
                if (!d.mascota.viva && state.currentAnim !== 'dead') {
                    setAnim('dead');
                    showBubble('...');
                }
            }
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       INIT
    ═══════════════════════════════════════════════════════════════ */
    function init(opts) {
        state.userId   = opts.userId   || 0;
        state.parejaId = opts.parejaId || 0;
        state.label    = opts.label    || '';

        apiFetch('get', null, function (err, d) {
            if (err || !d || !d.ok) return;

            state.mascota          = d.mascota;
            state.pendingPreguntas = d.pending_preguntas || [];
            state.skin             = d.mascota.skin || 'meloncio';

            buildDOM();

            state.x = Math.floor(window.innerWidth / 2) - SIZE_W / 2;
            state.y = groundY();
            applyPosition();

            preloadFrames(state.skin, function () {
                renderFrame();
                requestAnimationFrame(animTick);
            });

            apiFetch('get-memoria', null, function (e2, m) {
                if (!e2 && m && m.ok) state.memoria = m.memoria || {};
            });

            updateHUD();
            setInterval(moveTick,    16);
            setInterval(scanWindows, CFG.WINDOW_SCAN_MS);
            setInterval(heartbeat,   CFG.HEARTBEAT_MS);

            setTimeout(function () {
                if (!state.mascota.viva) {
                    setAnim('dead'); showBubble('...'); return;
                }
                if (state.mascota.hambre < 25) {
                    showBubble('¡Tengo hambre! 😢');
                } else {
                    var saludos = ['¡Hola! 👋', '¡Estás de vuelta! 😊', '¡Yay! 🎉'];
                    showBubble(saludos[Math.floor(Math.random() * saludos.length)]);
                }
                if (state.pendingPreguntas.length) {
                    setTimeout(askNextPregunta, 8000);
                }
            }, 1200);
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       API PÚBLICA
    ═══════════════════════════════════════════════════════════════ */
    global.MascotaEngine = {
        init:       init,
        showBubble: showBubble,
        hideBubble: hideBubble,
        feed:       function () { handleMenuAction('feed'); },
        play:       function () { handleMenuAction('play'); },
        getState:   function () { return state; },
    };

})(window);
JSEOF