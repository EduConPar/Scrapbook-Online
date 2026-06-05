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
        TASKBAR_PX:     30,          /* alto real del taskbar (28px height + 2px border-top en base.css) */
        /* Píxeles transparentes en el bottom del PNG. Medido en gabriel:
           el contenido del sprite llega hasta la última fila (y=127)
           → padding 0. Si una skin tuviera padding inferior, sube
           este valor para que los pies visibles queden alineados con
           la superficie (taskbar o techo de ventana). */
        SPRITE_FOOT_PAD: 0,
        /* Píxeles transparentes a los lados del cuerpo visible del
           shimeji. Medidos del shime13/shime14 de gabriel: el
           contenido va de la col 32 a la 120 dentro del PNG 128×.
           Si añades skins con padding distinto, ajusta aquí. */
        /* PAD a los lados del cuerpo SÓLIDO (alpha=255) del shimeji.
           Medidos del cuerpo opaco real (no de los efectos semi-
           transparentes que extienden el contenido):
             shime13: l=40, r=20
             shime14: l=51, r=5
           Usamos valores medios/agresivos para que el cuerpo (no los
           efectos) toque la pared en lugar de quedarse alejado por
           la transparencia de los crystal effects. */
        SPRITE_PAD_LEFT:  60,
        SPRITE_PAD_RIGHT: 15,
        /* Físicas de caída — ajustadas para 60fps (moveTick cada 16ms). */
        GRAVITY:        1.2,         /* px/frame² aceleración */
        MAX_FALL:       16,          /* terminal velocity */
        FALL_LAUNCH_VY: 3,           /* velocidad inicial al soltar tras drag (si no hay momentum) */
        /* MOMENTUM al soltar el drag — al lanzar, se calcula la velocidad
           del cursor en los últimos ms y se aplica al state.vx/vy. */
        MAX_THROW_VX:   30,          /* tope horizontal */
        MAX_THROW_VY:   25,          /* tope vertical (hacia abajo) */
        AIR_FRICTION:   0.985,       /* multiplicador por frame de vx en el aire */
        /* REBOTE contra paredes (viewport o ventanas) durante una caída
           con momentum horizontal. */
        BOUNCE_DAMPING:   0.55,      /* energía conservada tras rebote (0=stop, 1=elástico) */
        BOUNCE_THRESHOLD: 4,         /* |vx| mínimo para rebotar; menor → snap a la pared */
        /* REBOTE contra el SUELO al aterrizar con velocidad vertical
           alta. Con damping 0.5 y threshold 5: caída a vy=16 → bounce
           a 8 → bounce a 4 (< threshold) → para. Da ~2 rebotes
           naturales para una caída larga. */
        GROUND_BOUNCE_DAMPING:   0.5,
        GROUND_BOUNCE_THRESHOLD: 5,
        /* Fricción horizontal aplicada al contactar el suelo (cada
           rebote). vx *= esto en cada bounce — evita que la mascota
           "deslice" eternamente al aterrizar con momentum lateral. */
        GROUND_FRICTION: 0.7,
        /* ESCALADA de ventanas — al toparse con una pared lateral en
           lugar de siempre dar la vuelta, hay una probabilidad de
           empezar a escalar verticalmente hasta el techo de la ventana. */
        CLIMB_SPEED:  1.5,           /* px/frame hacia arriba (lento, sensación de esfuerzo) */
        CLIMB_CHANCE: 0.5
        ,           /* prob. de escalar al chocar (vs. girar) */
        /* Si un HUEVO cae más de esta distancia (px) antes de aterrizar,
           se rompe y la mascota muere. Soltar al suelo desde poca altura
           es seguro; lanzar desde la mitad/arriba de la pantalla lo
           destruye. */
        EGG_BREAK_FALL_PX: 250,

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
            wall:    { frames: [13, 14],                    loop: true  },
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
        skin:      'gabriel',

        x: 100, y: 0,
        targetX: 0,
        moving: false,
        facingRight: true,
        dragging: false,
        dragOffX: 0, dragOffY: 0,
        /* Físicas: cuando state.falling=true moveTick acelera state.vy
           bajo gravedad y desciende state.y hasta tocar una superficie
           (suelo o top de una ventana abierta). vx maneja el momentum
           horizontal del lanzamiento — decae con AIR_FRICTION y rebota
           contra paredes. */
        falling: false,
        vy: 0,
        vx: 0,
        /* Buffer de muestras (x, y, t) recientes durante el drag para
           calcular velocidad de lanzamiento al soltar. */
        dragSamples: [],
        /* ESCALADA: cuando la mascota choca con un muro lateral y
           decide subir en lugar de girarse. */
        climbing:        false,
        climbingWindow:  null,       /* DOMRect snapshot — { left, right, top, bottom } */
        climbingEl:      null,       /* referencia al elemento DOM — para detectar movimiento */
        climbingSide:    null,       /* 'left' o 'right' — qué lado de la ventana toca */

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
        /* Cubre TODO el viewport — sin reservar el área del taskbar y
           sin `overflow: hidden`. Antes con `bottom: 42px` +
           overflow:hidden, cualquier parte de la caja de la mascota
           que cayera dentro de la zona reservada del taskbar se
           recortaba (pies cortados, HUD/burbujas cortadas). Ahora la
           mascota puede ocupar cualquier punto de la pantalla; la
           función `groundY()` sigue siendo quien define el "suelo
           lógico" para las físicas, y el taskbar visible (z-index
           propio) se pinta encima sin necesidad de recortar nada. */
        root.style.cssText = [
            'position:fixed',
            'inset:0',
            'pointer-events:none',
            'z-index:8000',
            /* overflow:hidden eliminado a propósito — nada se corta. */
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

        /* HUD barras — DEBAJO del sprite. Ya no se cortan porque
           eliminamos `overflow: hidden` del #mascota-root y lo
           expandimos a todo el viewport. */
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
        /* HUD con TRES barras: hambre 🍖, felicidad ♥, temperatura 🔥.
           updateHUD() decide cuáles mostrar según el estado:
           - HUEVO  → solo 🔥 (temperatura)
           - MASCOTA → 🍖 + ♥ (hambre + felicidad) */
        hud.innerHTML = [
            '<div id="mascota-bar-hambre-row" style="display:flex;align-items:center;gap:4px;">',
                '<span style="font-size:9px;width:10px;">🍖</span>',
                '<div style="width:60px;height:8px;background:#ccc;border:1px solid #888;">',
                    '<div id="mascota-bar-hambre-fill" style="height:100%;background:#0a0;width:80%;transition:width 0.4s;"></div>',
                '</div>',
            '</div>',
            '<div id="mascota-bar-happy-row" style="display:flex;align-items:center;gap:4px;">',
                '<span style="font-size:9px;width:10px;">♥</span>',
                '<div style="width:60px;height:8px;background:#ccc;border:1px solid #888;">',
                    '<div id="mascota-bar-happy-fill" style="height:100%;background:#e05;width:80%;transition:width 0.4s;"></div>',
                '</div>',
            '</div>',
            '<div id="mascota-bar-temp-row" style="display:none;align-items:center;gap:4px;">',
                '<span style="font-size:9px;width:10px;">🔥</span>',
                '<div style="width:60px;height:8px;background:#ccc;border:1px solid #888;">',
                    '<div id="mascota-bar-temp-fill" style="height:100%;background:#f93;width:80%;transition:width 0.4s,background 0.4s;"></div>',
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
        /* Compensa CFG.SPRITE_FOOT_PAD para que los píxeles "pie" del
           sprite queden ALINEADOS con el top del taskbar y no flotando.
           Resultado: state.y + SIZE_H ≈ taskbarTop + SPRITE_FOOT_PAD,
           es decir, los pies visibles tocan el taskbar. */
        return (window.innerHeight - CFG.TASKBAR_PX) - SIZE_H + CFG.SPRITE_FOOT_PAD;
    }

    /* ── Detección de superficie ───────────────────────────────────
       Dado (x, y) actuales, devuelve la `y` de la superficie sólida
       más alta por debajo de la mascota. Considera:
         - El suelo (top del taskbar) como fallback.
         - El top de cada ventana abierta, SI la X del centro de la
           mascota está dentro del rango horizontal de la ventana Y
           la ventana está debajo de nuestra posición actual.
       Se elige la más ALTA (smaller y) de entre las que están por
       debajo de `y`, así no "saltamos" para arriba al detectarlas. */
    function getSurfaceBelow(x, y) {
        var groundLanding = groundY();
        var best = groundLanding;
        var centerX = x + SIZE_W / 2;
        getOpenWindowRects().forEach(function (w) {
            if (centerX < w.left || centerX > w.right) return;
            /* y donde aterriza ENCIMA del techo. Compensamos
               SPRITE_FOOT_PAD igual que el suelo — sin esto, los
               pies visibles de la mascota flotarían 14px por encima
               de la ventana al posarse. */
            var landing = w.top - SIZE_H + CFG.SPRITE_FOOT_PAD;
            if (landing >= y && landing < best) best = landing;
        });
        return best;
    }

    /* ── Colisión durante CAMINATA AUTÓNOMA ──
       Las ventanas se tratan como MUROS que extienden hacia abajo
       (más allá de su `w.bottom` real). Así la mascota caminando por
       el suelo choca con la pared lateral de cualquier ventana en su
       camino, aunque la ventana esté flotando en mitad de la pantalla
       sin tocar el suelo (comportamiento estilo Shimeji clásico).

       Excepción única: si la mascota está caminando EXACTAMENTE encima
       de la ventana (su bottom toca el top de la ventana) → no es
       colisión, está aterrizada sobre el techo. */
    function checkWindowCollision(newX, newY) {
        var petL = newX, petR = newX + SIZE_W;
        var petB = newY + SIZE_H;
        var wins = getOpenWindowRects();
        if (window.MascotaDebug) {
            console.log('[Mascota] checkCollision pet=', {l:petL,r:petR,t:newY,b:petB}, 'wins=', wins.length);
        }
        /* Threshold incluye SPRITE_FOOT_PAD: como la mascota aterriza
           sobre el techo con petB = w.top + SPRITE_FOOT_PAD (no
           directamente w.top), comparar con w.top + 4 daba falsos
           positivos de "colisión con muro" cuando en realidad estaba
           encima caminando. */
        var onTopThreshold = CFG.SPRITE_FOOT_PAD + 4;
        for (var i = 0; i < wins.length; i++) {
            var w = wins[i];
            if (petB <= w.top + onTopThreshold) continue;
            /* Si la ventana está FLOTANTE (su bottom no llega a tocar
               el cuerpo de la mascota), pasamos por debajo en lugar de
               chocar. La mascota anda al nivel `newY` con bottom en
               `petB`. Si w.bottom < newY, la ventana entera está
               por encima → no es muro para nosotros. */
            if (w.bottom < newY) continue;
            if (petR > w.left && petL < w.right) {
                if (window.MascotaDebug) console.log('[Mascota] HIT', w);
                return w;
            }
        }
        return null;
    }

    /** Intenta empezar una escalada por una pared de ventana.
     *  Llamado desde la lógica de colisión lateral durante la caminata.
     *  Solo escala si:
     *    - El techo de la ventana está ESTRICTAMENTE más alto que la
     *      posición actual (si no, no hay nada que escalar).
     *    - Suerte: pasa el roll de CLIMB_CHANCE.
     *  Devuelve true si arrancó la escalada (caller debe `return`). */
    function maybeStartClimb(w, side) {
        /* Posición final tras subir (con foot pad para no flotar). */
        var landingY = w.top - SIZE_H + CFG.SPRITE_FOOT_PAD;
        if (landingY >= state.y - 2) return false;  /* ventana NO está por encima */
        if (Math.random() > CFG.CLIMB_CHANCE) return false;
        /* Snap horizontal a la cara de la ventana.
           El sprite tiene SPRITE_SIDE_PAD px de transparencia a cada
           lado del cuerpo visible. Compensamos para que el cuerpo
           toque la pared en vez de la caja invisible. */
        if (side === 'left') {
            /* Mascota a la IZQ de la ventana; mira hacia la pared (a
               la derecha) → facingRight=true (con flip).
               Post-flip, el contenido visible está en
               [state.x + PAD_RIGHT, state.x + SIZE_W - PAD_LEFT].
               El borde derecho post-flip está en state.x + SIZE_W - PAD_LEFT.
               Para que toque w.left:
                 state.x = w.left - SIZE_W + PAD_LEFT */
            state.x = w.left - SIZE_W + CFG.SPRITE_PAD_LEFT;
            state.facingRight = true;
        } else {
            /* Mascota a la DCHA de la ventana; mira hacia la pared (a
               la izquierda) → facingRight=false (sin flip, natural).
               Contenido visible: [state.x + PAD_LEFT, state.x + SIZE_W - PAD_RIGHT].
               El borde izquierdo está en state.x + PAD_LEFT.
               Para que toque w.right:
                 state.x = w.right - PAD_LEFT */
            state.x = w.right - CFG.SPRITE_PAD_LEFT;
            state.facingRight = false;
        }
        /* Busca el ELEMENTO DOM de la ventana con la que estamos
           colisionando — necesario para detectar si se mueve durante
           la escalada y entonces caer. */
        var winEl = null;
        var nodes = document.querySelectorAll('.window');
        for (var i = 0; i < nodes.length; i++) {
            var r = nodes[i].getBoundingClientRect();
            if (Math.abs(r.left - w.left) < 2 && Math.abs(r.top - w.top) < 2
                && Math.abs(r.right - w.right) < 2 && Math.abs(r.bottom - w.bottom) < 2) {
                winEl = nodes[i];
                break;
            }
        }
        if (!winEl) return false;  /* sin DOM no podemos detectar movimiento */

        state.climbing       = true;
        state.climbingWindow = { left: w.left, right: w.right, top: w.top, bottom: w.bottom };
        state.climbingEl     = winEl;
        state.climbingSide   = side;
        state.moving         = false;
        setAnim('wall');
        applyPosition();
        return true;
    }

    /* ── Colisión LATERAL durante caída con momentum ──
       Detecta si moverse a `newX` (manteniendo Y actual) hace que la
       mascota choque con:
         a) Pared izquierda del viewport (x < 0)
         b) Pared derecha del viewport (x > innerWidth - SIZE_W)
         c) Lateral de una ventana abierta (AABB con cuerpo de ventana,
            ignorando el techo donde la mascota podría aterrizar)
       Devuelve { snapX } con la X "pegada a la pared", o null si no
       hay impacto. La dirección del rebote se decide en moveTick según
       el signo de state.vx. */
    function checkWallHit(newX, curY) {
        var SIZE_HALF = SIZE_W / 2;
        var petL = newX, petR = newX + SIZE_W;
        var petT = curY, petB = curY + SIZE_H;
        /* Viewport horizontal. */
        if (petL < 0) {
            return { snapX: 0 };
        }
        if (petR > window.innerWidth) {
            return { snapX: window.innerWidth - SIZE_W };
        }
        /* Ventanas. AABB normal pero ignoramos cuando estamos posándonos
           encima (bottom de la mascota cerca del top de la ventana,
           contando el SPRITE_FOOT_PAD). */
        var wins = getOpenWindowRects();
        var onTopThreshold = CFG.SPRITE_FOOT_PAD + 4;
        for (var i = 0; i < wins.length; i++) {
            var w = wins[i];
            if (petB <= w.top + onTopThreshold) continue;
            if (petB <= w.top || petT >= w.bottom) continue;
            if (petR > w.left && petL < w.right) {
                /* Hay solapamiento horizontal. Decidir si la pared con
                   la que se choca es la IZQUIERDA o DERECHA de la
                   ventana, según el lado por el que la mascota venía.
                   Comparamos el centro de la mascota actual vs el
                   centro de la ventana. */
                var petCenter = state.x + SIZE_HALF;
                var winCenter = (w.left + w.right) / 2;
                if (petCenter < winCenter) {
                    /* Venía por la izquierda → choca con la cara izq. */
                    return { snapX: w.left - SIZE_W };
                } else {
                    return { snapX: w.right };
                }
            }
        }
        return null;
    }

    /* ── Colisión durante ARRASTRE (AABB estricto) ──
       Distinta del muro-infinito de la caminata: aquí queremos AABB real
       para que el usuario pueda mover la mascota POR ENCIMA o POR DEBAJO
       de las ventanas flotantes, y solo se bloquee cuando trataría de
       meter la mascota DENTRO del cuerpo de la ventana.
       Ignora el margen de aterrizaje (4px del techo) — el usuario puede
       posar la mascota sobre el techo sin problema. */
    function checkWindowCollisionAABB(newX, newY) {
        /* Usar bordes del CUERPO VISIBLE (no del bounding box) para
           que el sliding del drag pegue el cuerpo del personaje
           contra la ventana.
           IMPORTANTE: pad SIMÉTRICO (no depende de facingRight).
           Antes usaba pad asimétrico según flip → la posición de
           snap cambiaba entre arrastres consecutivos según hacia
           dónde mirara la mascota. Promediando PAD_LEFT/PAD_RIGHT
           obtenemos un cuerpo "centrado" en el bounding box y el
           snap queda en el mismo sitio siempre. */
        var pad  = (CFG.SPRITE_PAD_LEFT + CFG.SPRITE_PAD_RIGHT) >> 1;  /* (60+15)/2 = 37 */
        var petL = newX + pad;
        var petR = newX + SIZE_W - pad;
        var petT = newY, petB = newY + SIZE_H;
        var wins = getOpenWindowRects();
        for (var i = 0; i < wins.length; i++) {
            var w = wins[i];
            if (petR > w.left && petL < w.right
                && petB > w.top && petT < w.bottom) {
                return w;
            }
        }
        return null;
    }

    function pickRandomTarget() {
        var margin = 10;
        var maxX   = window.innerWidth - SIZE_W - margin;

        /* Si la mascota está actualmente posada SOBRE UNA VENTANA, el
           target se elige preferentemente dentro de los X de esa misma
           ventana — así camina a lo largo de su techo sin caerse por
           el borde a la mínima. Solo un 30% del tiempo intentamos
           explorar fuera, para que la mascota no se quede pegada
           eternamente. */
        var currentSurface = getSurfaceBelow(state.x, state.y);
        var onWindow = null;
        if (Math.abs(state.y - currentSurface) < 4 && currentSurface < groundY() - 2) {
            /* Estamos sobre algún techo (no en el suelo) — localizar la
               ventana correspondiente para mantenernos en su rango X. */
            var centerX = state.x + SIZE_W / 2;
            getOpenWindowRects().forEach(function (w) {
                if (centerX >= w.left && centerX <= w.right
                    && Math.abs((w.top - SIZE_H + CFG.SPRITE_FOOT_PAD) - state.y) < 4) {
                    onWindow = w;
                }
            });
        }

        if (onWindow && Math.random() < 0.7) {
            /* Caminamos a lo largo del techo de la ventana actual. */
            var winMaxX = Math.max(onWindow.left, onWindow.right - SIZE_W);
            state.targetX = onWindow.left + Math.random() * Math.max(0, winMaxX - onWindow.left);
        } else {
            state.targetX = margin + Math.random() * maxX;
        }
        /* NO teletransportamos state.y aquí — la mascota se queda donde
           está y la física (caída, colisiones) decide lo demás durante
           moveTick. Antes había `state.y = groundY()` que provocaba
           saltos instantáneos al suelo al elegir target. */
    }

    function moveTick() {
        if (!state.mascota || !state.mascota.viva) return;
        if (state.dragging) return;

        var isEgg = !state.mascota.eclosionado;

        /* ── EYECCIÓN: si una ventana acaba de aparecer encima de la
           mascota, sale disparada. Solo aplica cuando NO está cayendo
           ni escalando — durante una caída la mascota puede atravesar
           ventanas brevemente y no queremos disparos espurios. */
        if (!state.falling && !state.climbing) {
            var intruder = windowContainingPet();
            if (intruder) {
                ejectFromWindow(intruder);
                return;
            }
        }

        /* ── FASE 0: ESCALADA por una pared de ventana ─────────────
           Sube state.y a CLIMB_SPEED hasta alcanzar el techo de la
           ventana sobre la que está. Al llegar arriba, se "engancha"
           encima de la ventana y vuelve al modo idle/caminata. */
        if (state.climbing && !isEgg) {
            var cw = state.climbingWindow;
            if (!cw) {
                /* Seguridad: estado inconsistente, cancelar. */
                state.climbing = false;
            } else {
                /* La ventana se ha MOVIDO o se ha cerrado mientras
                   escalábamos → ya no hay pared a la que agarrarse,
                   caemos. Comparamos la rect actual del elemento DOM
                   con el snapshot de cuando empezó la escalada. */
                var winEl = state.climbingEl;
                var moved = !winEl || !document.body.contains(winEl);
                if (winEl && !moved) {
                    var liveRect = winEl.getBoundingClientRect();
                    /* >2px de desplazamiento o cambio de tamaño = "se ha
                       movido". Tolerancia para fluctuaciones por
                       sub-pixel rendering / animaciones. */
                    if (Math.abs(liveRect.left   - cw.left  ) > 2
                     || Math.abs(liveRect.top    - cw.top   ) > 2
                     || Math.abs(liveRect.right  - cw.right ) > 2
                     || Math.abs(liveRect.bottom - cw.bottom) > 2) {
                        moved = true;
                    }
                }
                if (moved) {
                    state.climbing       = false;
                    state.climbingWindow = null;
                    state.climbingEl     = null;
                    state.climbingSide   = null;
                    /* Disparar caída — la fase de caída de moveTick
                       hará el resto desde el próximo tick. */
                    state.falling = true;
                    state.vy      = 0;
                    state.vx      = 0;
                    state.peakY   = state.y;
                    setAnim('fall');
                    return;
                }
                state.y -= CFG.CLIMB_SPEED;
                /* Posición final = techo de la ventana ajustado con
                   foot pad para que los pies visibles queden sobre la
                   ventana en lugar de "flotando" 14px arriba. */
                var topY = cw.top - SIZE_H + CFG.SPRITE_FOOT_PAD;
                if (state.y <= topY) {
                    /* Alcanzó el techo — se posa encima. Ajustamos X
                       para que el BORDE VISIBLE del cuerpo quede
                       alineado con el borde de la ventana (no el
                       bounding box que tiene mucho padding asimétrico).
                       Y aseguramos que centerX caiga dentro del rango
                       de la ventana para que getSurfaceBelow la
                       reconozca y no caiga de nuevo. */
                    state.y = topY;
                    if (state.climbingSide === 'left') {
                        /* facingRight=true (con flip) tras la escalada.
                           Post-flip, visible.left = state.x + PAD_RIGHT.
                           Para alinear con cw.left:
                             state.x = cw.left - PAD_RIGHT */
                        state.x = cw.left - CFG.SPRITE_PAD_RIGHT;
                    } else {
                        /* facingRight=false (sin flip). visible.right =
                           state.x + SIZE_W - PAD_RIGHT. Para alinear
                           con cw.right:
                             state.x = cw.right - SIZE_W + PAD_RIGHT */
                        state.x = cw.right - SIZE_W + CFG.SPRITE_PAD_RIGHT;
                    }
                    /* Clamp al viewport por si la ventana toca el
                       borde de la pantalla. */
                    state.x = Math.max(0, Math.min(window.innerWidth - SIZE_W, state.x));
                    state.climbing       = false;
                    state.climbingWindow = null;
                    state.climbingEl     = null;
                    state.climbingSide   = null;
                    state.moving         = false;
                    state.idleTimer      = 0;
                    setAnim('idle');
                } else {
                    setAnim('wall');
                }
                applyPosition();
            }
            return;
        }

        /* ── FASE 1: caída con gravedad + momentum horizontal ─────
           - state.vy se incrementa por gravedad cada frame.
           - state.vx (lanzamiento horizontal) se desgasta con
             AIR_FRICTION y rebota al chocar con paredes (viewport o
             cuerpos de ventana) si la velocidad es > BOUNCE_THRESHOLD.
           - Detectamos aterrizaje contra la superficie más cercana. */
        if (state.falling) {
            /* Gravedad. state.vy puede empezar NEGATIVO (lanzamiento
               hacia arriba) → gravity lo lleva a 0 (pico) → vuelve
               positivo (cae). Esta es la mecánica de PROYECTIL. */
            state.vy = Math.min(state.vy + CFG.GRAVITY, CFG.MAX_FALL);
            state.vx *= CFG.AIR_FRICTION;
            if (Math.abs(state.vx) < 0.05) state.vx = 0;

            var nextX = state.x + state.vx;
            var nextY = state.y + state.vy;

            /* Trackear el PICO (Y más alto = menor número) de la
               trayectoria. Lo usamos al aterrizar para calcular la
               distancia REAL de caída — incluida la fase de subida +
               la fase de bajada — y romper el huevo si fue brutal. */
            if (state.peakY == null || nextY < state.peakY) {
                state.peakY = nextY;
            }

            /* Colisión LATERAL durante la caída — pared del viewport o
               cuerpo de ventana. Si hay impacto y |vx| > umbral,
               REBOTA con damping; si no, simplemente se queda pegado. */
            var hit = checkWallHit(nextX, state.y);
            if (hit) {
                nextX = hit.snapX;
                if (Math.abs(state.vx) > CFG.BOUNCE_THRESHOLD) {
                    state.vx = -state.vx * CFG.BOUNCE_DAMPING;
                } else {
                    state.vx = 0;
                }
            }

            var surface = getSurfaceBelow(nextX, state.y);
            if (nextY >= surface) {
                /* IMPACTO contra superficie. */
                state.x        = nextX;
                state.y        = surface;
                var landingVy  = state.vy;       /* velocidad ANTES de procesarla */
                applyPosition();

                if (isEgg) {
                    /* Comprueba ruptura por altura de caída desde pico.
                       El huevo no rebota — el cascarón está rígido. */
                    var peak     = state.peakY != null ? state.peakY : state.y;
                    var fallDist = state.y - peak;
                    state.peakY  = null;
                    state.falling = false;
                    state.vy = 0;
                    state.vx = 0;
                    if (fallDist > CFG.EGG_BREAK_FALL_PX) {
                        breakEgg();
                    }
                    return;
                }

                /* MASCOTA: si la velocidad de impacto es alta, REBOTA
                   (vy invertido con damping). Cada rebote pierde
                   energía hasta caer bajo el umbral → para. La
                   `peakY` se resetea para que cada arco mida la
                   altura real de ese arco concreto, no del lanzamiento
                   inicial. */
                if (landingVy > CFG.GROUND_BOUNCE_THRESHOLD) {
                    state.vy = -landingVy * CFG.GROUND_BOUNCE_DAMPING;
                    state.vx *= CFG.GROUND_FRICTION;
                    if (Math.abs(state.vx) < 0.05) state.vx = 0;
                    state.peakY = state.y;       /* nuevo arco arranca aquí */
                    /* state.falling sigue true — reentramos en la fase
                       de caída en el próximo tick. */
                    setAnim('fall');
                    return;
                }

                /* Aterrizaje "suave": para definitivamente. */
                state.falling = false;
                state.vy = 0;
                state.vx = 0;
                state.peakY = null;
                setAnim('bounce');
                setTimeout(function () {
                    if (!state.falling && !state.dragging) setAnim('idle');
                }, 400);
            } else {
                state.x = nextX;
                state.y = nextY;
                if (!isEgg) setAnim('fall');
                applyPosition();
            }
            return;
        }

        /* ── HUEVO: solo física, no caminata ───────────────────────
           El huevo no anda ni decide targets. Solo "vive" mientras
           está parado en una superficie. Si la superficie desaparece
           (cierras una ventana sobre la que estaba), cae. */
        if (isEgg) {
            var surfBelow = getSurfaceBelow(state.x, state.y);
            if (state.y < surfBelow - 2) {
                state.falling = true;
                state.vy = 0;
                state.peakY = state.y;
            }
            return;
        }

        var hambre = state.mascota.hambre;
        if (!state.mascota.viva) { setAnim('dead'); return; }
        if (hambre < 25)         { setAnim('sad');  return; }

        /* ── FASE 2: detectar caída espontánea ─────────────────────
           Si la mascota está "en el aire" (la superficie de abajo se
           movió o cerró una ventana), arranca a caer. */
        var surfaceNow = getSurfaceBelow(state.x, state.y);
        if (state.y < surfaceNow - 2) {
            state.falling = true;
            state.vy = 0;
            state.moving = false;
            state.peakY = state.y;
            return;
        }

        /* ── FASE 3: movimiento horizontal hacia el target ────────── */
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
            var step  = Math.min(CFG.WALK_SPEED, dist);
            var dir   = state.facingRight ? 1 : -1;
            var prevX = state.x;            /* guardamos por si toca caer */
            var newX  = state.x + step * dir;

            /* Colisión horizontal con cuerpo de ventana.
               Al chocar tiene 2 opciones:
                 a) ESCALAR la pared (chance CLIMB_CHANCE, si la
                    ventana está por encima).
                 b) Girar y volver.
               El lado depende de la dirección actual: si voy hacia
               la derecha y choco, era la cara IZQUIERDA de la ventana. */
            var hitWin = checkWindowCollision(newX, state.y);
            if (hitWin) {
                var side = state.facingRight ? 'left' : 'right';
                if (maybeStartClimb(hitWin, side)) return;
                state.moving = false;
                state.facingRight = !state.facingRight;
                setAnim('idle');
                return;
            }
            /* Clamp a los bordes del viewport. */
            newX = Math.max(0, Math.min(window.innerWidth - SIZE_W, newX));
            state.x = newX;

            /* Si al avanzar nos hemos quedado sin superficie debajo
               (caminé hasta el borde de una ventana), caer. */
            var nextSurface = getSurfaceBelow(state.x, state.y);
            if (state.y < nextSurface - 2) {
                /* Localizamos la ventana sobre la que ESTÁBAMOS antes
                   del step (su techo == state.y±4). Hace falta porque
                   tras dar el paso, centerX puede haber salido de su
                   rango y getSurfaceBelow ya no la encuentra. */
                var leftWin = null;
                var prevCenterX = prevX + SIZE_W / 2;
                getOpenWindowRects().forEach(function (w) {
                    if (prevCenterX >= w.left && prevCenterX <= w.right
                        && Math.abs((w.top - SIZE_H + CFG.SPRITE_FOOT_PAD) - state.y) < 4) {
                        leftWin = w;
                    }
                });
                /* Snap X completamente fuera del cuerpo horizontal de
                   esa ventana antes de empezar a caer — si no, al
                   descender atravesaría la ventana visualmente. */
                if (leftWin) {
                    if (state.facingRight) {
                        state.x = Math.min(
                            window.innerWidth - SIZE_W,
                            leftWin.right            /* pet.left coincide con ventana.right */
                        );
                    } else {
                        state.x = Math.max(
                            0,
                            leftWin.left - SIZE_W    /* pet.right coincide con ventana.left */
                        );
                    }
                    applyPosition();
                }
                state.falling    = true;
                state.vy         = 0;
                state.moving     = false;
                state.peakY = state.y;
                return;
            }

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
            /* Excluimos plantillas (folder-window-template) y modales
               (folder-create-modal, change-password-modal, etc.) que
               son `.window` pero no representan ventanas reales. */
            if (w.hasAttribute('data-no-auto-z')) return;
            /* Excluimos la propia ventana de la mascota. */
            if (w.id === 'mascota-window') return;
            /* Visibilidad: solo por bounding rect.
               - `display:none` → getBoundingClientRect devuelve todo 0 → filtra.
               - NO usamos `offsetParent === null` porque las ventanas del
                 escritorio son `position: fixed` y ESO hace null su
                 offsetParent SIEMPRE (los fixed se posicionan respecto al
                 viewport, no a un padre). Antes excluía TODAS las ventanas
                 visibles → wins=0 en debug. */
            var r = w.getBoundingClientRect();
            if (r.width <= 0 || r.height <= 0) return;
            rects.push(r);
        });
        return rects;
    }

    /** ¿El centro de la mascota está dentro del CUERPO de alguna
     *  ventana? Útil para detectar que el usuario acaba de abrir una
     *  ventana ENCIMA suya y debe ser eyectada. */
    function windowContainingPet() {
        var cx = state.x + SIZE_W / 2;
        var cy = state.y + SIZE_H / 2;
        var wins = getOpenWindowRects();
        for (var i = 0; i < wins.length; i++) {
            var w = wins[i];
            if (cx > w.left && cx < w.right && cy > w.top && cy < w.bottom) {
                return w;
            }
        }
        return null;
    }

    /** Busca una posición X "segura" en el suelo donde la mascota no
     *  esté solapada con ninguna ventana. Prueba varios candidatos
     *  (esquinas, cuartos del viewport). Si ninguno queda libre,
     *  devuelve la primera (esquina izq). */
    function findSafeLandingX() {
        var SAFE_MARGIN = 40;
        var candidates = [
            SAFE_MARGIN,
            window.innerWidth - SIZE_W - SAFE_MARGIN,
            Math.floor(window.innerWidth * 0.20) - SIZE_W / 2,
            Math.floor(window.innerWidth * 0.80) - SIZE_W / 2,
            Math.floor(window.innerWidth * 0.50) - SIZE_W / 2,
        ];
        var floorY = groundY();
        var wins = getOpenWindowRects();
        for (var i = 0; i < candidates.length; i++) {
            var x = Math.max(0, Math.min(window.innerWidth - SIZE_W, candidates[i]));
            var cx = x + SIZE_W / 2;
            var cy = floorY + SIZE_H / 2;
            var ok = true;
            for (var j = 0; j < wins.length; j++) {
                var w = wins[j];
                if (cx > w.left && cx < w.right && cy > w.top && cy < w.bottom) {
                    ok = false; break;
                }
            }
            if (ok) return x;
        }
        return candidates[0];
    }

    /** Sale disparada hacia la posición segura más cercana lejos del
     *  centro de la ventana intrusa. Aplica vx + vy de impulso y deja
     *  que las físicas (gravedad, paredes, rebote) hagan el resto. */
    function ejectFromWindow(w) {
        var winCenterX = (w.left + w.right) / 2;
        var safeX      = findSafeLandingX();
        /* Dirección: hacia donde está la zona segura respecto al centro
           de la ventana invasora. */
        var dir = safeX < winCenterX ? -1 : 1;
        /* Velocidades fijas para el "disparo" — fuerte horizontal +
           pequeño impulso hacia arriba para arco. */
        state.vx = 22 * dir;
        state.vy = -6;
        state.falling    = true;
        state.peakY      = state.y;
        state.moving     = false;
        state.dragSamples = [];
        setAnim('fall');
        applyPosition();
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
        /* Cancelar cualquier caída en curso — al estar dragging las
           físicas se pausan, pero limpiamos por consistencia.
           `peakY` también: la próxima caída se mide desde el pico
           propio (parábolas), no desde un pico viejo. `dragSamples`
           se resetea para calcular el momentum desde 0. */
        state.falling    = false;
        state.vy         = 0;
        state.vx         = 0;
        state.peakY      = null;
        state.dragSamples = [{ x: state.x, y: state.y, t: Date.now() }];
        /* Cualquier escalada en curso queda cancelada al agarrar a la
           mascota. */
        state.climbing       = false;
        state.climbingWindow = null;
        state.climbingEl     = null;
        state.climbingSide   = null;
        setAnim('drag');
        var r = state.el.getBoundingClientRect();
        state.dragOffX = cx - r.left;
        state.dragOffY = cy - r.top;

        function onMove(e) {
            var px = e.clientX !== undefined ? e.clientX : (e.touches && e.touches[0].clientX);
            var py = e.clientY !== undefined ? e.clientY : (e.touches && e.touches[0].clientY);
            if (px === undefined) return;
            /* Posición candidata clampeada al viewport. */
            var nx = Math.max(0, Math.min(window.innerWidth  - SIZE_W, px - state.dragOffX));
            var ny = Math.max(0, Math.min(groundY(),                  py - state.dragOffY));

            /* SLIDE collision con SNAP-A-PARED para consistencia.
               Antes la mascota se quedaba "donde le pillara" según el
               último frame válido → el punto de impacto variaba con
               cada arrastre. Ahora, cuando el X queda bloqueado,
               snapeamos explícitamente al borde de la ventana
               correspondiente (pegando el cuerpo) → el snap es
               siempre el mismo, no depende de la velocidad/path
               del cursor. */
            var pad = (CFG.SPRITE_PAD_LEFT + CFG.SPRITE_PAD_RIGHT) >> 1;
            if (!checkWindowCollisionAABB(nx, ny)) {
                state.x = nx; state.y = ny;
            } else if (!checkWindowCollisionAABB(nx, state.y)) {
                /* Y bloqueado, X libre: aplica X normal. */
                state.x = nx;
            } else {
                /* X bloqueado: snap explícito al borde de la ventana
                   con la que se colisiona en X. */
                var blockerX = checkWindowCollisionAABB(nx, state.y);
                if (blockerX) {
                    if (nx > state.x) {
                        /* Movía a la derecha → cuerpo derecho pega a
                           blockerX.left → bbox right en w.left+pad. */
                        state.x = blockerX.left - SIZE_W + pad;
                    } else if (nx < state.x) {
                        /* Movía a la izquierda → cuerpo izquierdo
                           pega a blockerX.right. */
                        state.x = blockerX.right - pad;
                    }
                    state.x = Math.max(0, Math.min(window.innerWidth - SIZE_W, state.x));
                }
                /* Y por separado — puede estar libre aunque X no. */
                if (!checkWindowCollisionAABB(state.x, ny)) {
                    state.y = ny;
                }
            }
            applyPosition();

            /* Guardar la muestra para calcular velocidad al soltar.
               Limitamos a los últimos 100ms — más ventana = inercia
               percibida más "vaga"; menos = sensación más responsiva. */
            var now = Date.now();
            state.dragSamples.push({ x: state.x, y: state.y, t: now });
            while (state.dragSamples.length > 1
                   && now - state.dragSamples[0].t > 100) {
                state.dragSamples.shift();
            }
        }
        function onUp() {
            state.dragging = false;
            /* MOMENTUM: calcular velocidad del lanzamiento desde las
               muestras recientes. Promediamos px/frame entre la primera
               y última muestra de la ventana de 100ms.
               IMPORTANTE: NO clampeamos vy a positivo — si el usuario
               flickeó hacia arriba, vy es negativo y la mascota hace
               una PARÁBOLA: sube → frena por gravedad → cae. Pura
               física de proyectil. */
            var samples = state.dragSamples || [];
            var vx = 0, vy = 0;
            if (samples.length >= 2) {
                var last  = samples[samples.length - 1];
                var first = samples[0];
                var dtMs  = last.t - first.t;
                if (dtMs > 0) {
                    /* px/ms → px/frame (16ms ≈ 1 frame). */
                    var perFrame = 16;
                    vx = (last.x - first.x) / dtMs * perFrame;
                    vy = (last.y - first.y) / dtMs * perFrame;
                }
            }
            /* Topes simétricos — el flick hacia arriba también se capa
               para que un yeet brutal no saque la mascota de la pantalla
               permanentemente. */
            vx = Math.max(-CFG.MAX_THROW_VX, Math.min(CFG.MAX_THROW_VX, vx));
            vy = Math.max(-CFG.MAX_THROW_VY, Math.min(CFG.MAX_THROW_VY, vy));

            state.falling    = true;
            state.vx         = vx;
            state.vy         = vy;
            state.moving     = false;
            state.peakY = state.y;
            state.dragSamples = [];
            if (!(state.mascota && !state.mascota.eclosionado)) setAnim('fall');
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

        /* Mascota muerta → confirmación de revivir (mantenido — es una
           acción de "rescate", no el menú general). */
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
        /* Mascota viva: el menú (alimentar/jugar/etc.) ya NO se abre al
           tocar la mascota — se accede únicamente desde el botón flotante
           ☰. Aquí solo mostramos una pequeña reacción para que el tap
           no se sienta inerte. */
        var reacciones = ['¿Sí?', '😊', '¿Mh?', '¡Hola!', '*ronronea*'];
        showBubble(reacciones[Math.floor(Math.random() * reacciones.length)], 1800);
    }

    /* ═══════════════════════════════════════════════════════════════
       MENÚ — el menú contextual sobre la mascota se eliminó: ahora
       solo se accede desde el botón flotante ☰ del escritorio. Las
       acciones (feed/play/...) siguen disponibles vía
       window.MascotaEngine.feed() etc. que llaman a handleMenuAction.
    ═══════════════════════════════════════════════════════════════ */
    function handleMenuAction(action) {
        switch (action) {
            case 'feed':
                /* La selección concreta del alimento se hace ahora en
                   el picker (ventana #alimentar-window del escritorio).
                   Aquí solo lanzamos la animación de comer; las
                   actualizaciones de stats las hace ese handler. */
                setAnim('eat');
                waitAnimDone(function () { setAnim('idle'); });
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

            case 'warm':
                /* Dar calor al huevo. Sube temperatura +30 (cap 100).
                   Solo válido mientras estado huevo. SIN burbuja —
                   el huevo no habla; el feedback es solo la barra HUD
                   actualizándose. */
                apiFetch('warm', {}, function (err, d) {
                    if (!err && d && d.ok) {
                        state.mascota.temperatura = d.temperatura;
                        updateHUD();
                    }
                });
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
        var isEgg     = !state.mascota.eclosionado;
        var hambreRow = document.getElementById('mascota-bar-hambre-row');
        var happyRow  = document.getElementById('mascota-bar-happy-row');
        var tempRow   = document.getElementById('mascota-bar-temp-row');

        if (hambreRow) hambreRow.style.display = isEgg ? 'none' : 'flex';
        if (happyRow)  happyRow.style.display  = isEgg ? 'none' : 'flex';
        if (tempRow)   tempRow.style.display   = isEgg ? 'flex' : 'none';

        if (isEgg) {
            var t = state.mascota.temperatura || 0;
            var tFill = document.getElementById('mascota-bar-temp-fill');
            if (tFill) {
                tFill.style.width = t + '%';
                /* Color del frío→cálido: rojo (peligro) → naranja → verde. */
                tFill.style.background = t < 20 ? '#06f' : t < 50 ? '#f93' : '#f60';
                /* Si está muy frío parpadea para enfatizar el peligro. */
                tFill.style.animation = t < 20 ? 'mascota-temp-pulse 0.8s ease-in-out infinite' : 'none';
            }
            return;
        }

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
                var wasEgg     = state.mascota && !state.mascota.eclosionado;
                var nowMascota = !!d.mascota.eclosionado;
                state.mascota  = d.mascota;
                updateHUD();
                /* Si el huevo está LISTO para eclosionar (3 días pasaron
                   y temperatura > 0), pedimos nombre al usuario. */
                if (wasEgg && !nowMascota
                    && state.mascota.viva
                    && state.mascota.segundos_para_eclosion === 0
                    && !state.hatchPromptShown) {
                    promptHatchName();
                }
                /* Si por la razón que sea (otro tab, otro dispositivo)
                   ya está eclosionado en BD pero localmente lo
                   teníamos como huevo, hacemos la transición visual. */
                if (wasEgg && nowMascota) {
                    transitionEggToMascota();
                }
                if (!d.mascota.viva && state.currentAnim !== 'dead') {
                    setAnim('dead');
                }
            }
        });
    }

    /** Pide nombre al usuario y dispara la eclosión. Usado tanto al
     *  detectar fin del ciclo natural (3 días) como por el botón DEV.
     *  `opts.dev=true` salta el check del servidor de los 3 días. */
    function promptHatchName(opts) {
        if (state.hatchPromptShown) return;
        state.hatchPromptShown = true;
        opts = opts || {};
        var ask = function () {
            if (typeof window.win98Prompt === 'function') {
                window.win98Prompt(
                    '¡Tu huevo está listo para eclosionar!\n¿Qué nombre le pones a tu mascota?',
                    '', /* default vacío */
                    function (nombre) {
                        nombre = (nombre || '').trim();
                        if (!nombre) { state.hatchPromptShown = false; return; }
                        apiFetch('hatch', { nombre: nombre, dev: !!opts.dev }, function (err, d) {
                            if (!err && d && d.ok) {
                                state.mascota = d.mascota;
                                transitionEggToMascota();
                                /* La iframe de "ver mascota" tenía SSR
                                   del huevo → invalidar. */
                                try { if (window.refreshMascotaWindow) window.refreshMascotaWindow(); } catch (_) {}
                            } else {
                                state.hatchPromptShown = false;
                            }
                        });
                    },
                    function () { state.hatchPromptShown = false; },
                    'Eclosión'
                );
            } else {
                var nombre = (prompt('¿Qué nombre le pones?') || '').trim();
                if (!nombre) { state.hatchPromptShown = false; return; }
                apiFetch('hatch', { nombre: nombre, dev: !!opts.dev }, function (err, d) {
                    if (!err && d && d.ok) {
                        state.mascota = d.mascota;
                        transitionEggToMascota();
                        try { if (window.refreshMascotaWindow) window.refreshMascotaWindow(); } catch (_) {}
                    } else {
                        state.hatchPromptShown = false;
                    }
                });
            }
        };
        /* Pequeño delay para que cualquier UI previa (HUD, etc.) se
           asiente antes de mostrar el modal. */
        setTimeout(ask, 250);
    }

    /* ═══════════════════════════════════════════════════════════════
       INIT — solo guarda config. NO renderiza nada hasta que
       alguien llame a spawn(). Esto permite que el escritorio cargue
       el script sin que la mascota aparezca: la app "Mascota" del
       launcher es quien dispara spawn().
    ═══════════════════════════════════════════════════════════════ */
    function init(opts) {
        state.userId   = opts.userId   || 0;
        state.parejaId = opts.parejaId || 0;
        state.label    = opts.label    || '';
        state.spawned  = false;
    }

    /** Despierta la mascota: pide estado a la API, monta el DOM y arranca
     *  loops. Si la mascota NO está eclosionada (`eclosionado=0`),
     *  pinta un HUEVO grande clickable en lugar de la animación.
     *  Idempotente: llamar dos veces seguidas no duplica nada. */
    function spawn() {
        if (state.spawned) {
            /* Ya está en pantalla — solo refrescamos. */
            apiFetch('get', null, function (err, d) {
                if (err || !d || !d.ok) return;
                state.mascota = d.mascota;
                updateHUD();
            });
            return;
        }
        state.spawned = true;

        apiFetch('get', null, function (err, d) {
            if (err || !d || !d.ok) { state.spawned = false; return; }

            state.mascota          = d.mascota;
            state.pendingPreguntas = d.pending_preguntas || [];
            state.skin             = d.mascota.skin || 'gabriel';

            buildDOM();

            state.x = Math.floor(window.innerWidth / 2) - SIZE_W / 2;
            state.y = groundY();
            applyPosition();

            /* Mostrar botón flotante del menú (☰) y avisar al desktop. */
            var menuBtn = document.getElementById('mascota-menu-btn');
            if (menuBtn) menuBtn.style.display = '';
            try { window.dispatchEvent(new CustomEvent('mascota:spawned')); } catch (_) {}

            /* HUEVO vs MASCOTA. Si `eclosionado=false` renderizamos un
               huevo. NO se eclosiona al tocarlo — eclosiona solo tras
               3 días con temperatura > 0. Arrancamos heartbeat y
               moveTick: el moveTick aplica gravedad al huevo igual que
               a la mascota (sin la lógica de caminata), de modo que
               se puede arrastrar y soltar. */
            if (!state.mascota.eclosionado) {
                renderEgg();
                updateHUD();
                state.moveTickIv  = setInterval(moveTick,  16);
                state.heartbeatIv = setInterval(heartbeat, CFG.HEARTBEAT_MS);
                /* Si al spawnear el huevo ya está listo (3 días pasaron
                   mientras el usuario no abría la app), pedimos nombre
                   directamente sin esperar al heartbeat. */
                if (state.mascota.viva && state.mascota.segundos_para_eclosion === 0) {
                    promptHatchName();
                }
                return;
            }

            preloadFrames(state.skin, function () {
                renderFrame();
                requestAnimationFrame(animTick);
            });

            apiFetch('get-memoria', null, function (e2, m) {
                if (!e2 && m && m.ok) state.memoria = m.memoria || {};
            });

            updateHUD();
            state.moveTickIv = setInterval(moveTick,    16);
            state.scanIv     = setInterval(scanWindows, CFG.WINDOW_SCAN_MS);
            state.heartbeatIv = setInterval(heartbeat,  CFG.HEARTBEAT_MS);

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

    /** Pinta el huevo grande encima del contenedor de la mascota.
     *  El huevo NO se eclosiona al tocarlo — eclosiona automáticamente
     *  cuando han pasado 3 días desde su creación Y temperatura > 0.
     *  El usuario lo mantiene caliente con la acción "Dar calor" del
     *  menú flotante. Tocarlo solo muestra una reacción amable.
     *  El poll del heartbeat detecta cuando eclosiona y dispara la
     *  transición a modo mascota. */
    function renderEgg() {
        if (!state.imgEl) return;
        state.imgEl.style.display = 'none';
        var egg = document.getElementById('mascota-egg');
        if (egg) egg.remove();
        egg = document.createElement('div');
        egg.id = 'mascota-egg';
        egg.style.cssText = [
            'width:'  + SIZE_W + 'px',
            'height:' + SIZE_H + 'px',
            'display:flex',
            'align-items:center',
            'justify-content:center',
            'font-size:' + Math.floor(SIZE_H * 0.8) + 'px',
            'line-height:1',
            'cursor:pointer',
            'user-select:none',
            'animation:mascota-egg-wobble 2.4s ease-in-out infinite',
        ].join(';');
        egg.textContent = '🥚';
        state.el.appendChild(egg);

        /* Wobble del huevo + pulse de temperatura crítica (keyframes
           inyectados una vez). */
        if (!document.getElementById('mascota-egg-css')) {
            var st = document.createElement('style');
            st.id = 'mascota-egg-css';
            st.textContent =
                '@keyframes mascota-egg-wobble{' +
                  '0%,100%{transform:rotate(-4deg)}' +
                  '50%{transform:rotate(4deg)}' +
                '}' +
                '@keyframes mascota-temp-pulse{' +
                  '0%,100%{opacity:1}' +
                  '50%{opacity:0.4}' +
                '}';
            document.head.appendChild(st);
        }

        /* El huevo NO habla — sin burbujas de reacción ni mensajes
           iniciales. Toda la comunicación es visual (wobble + barra
           de temperatura) hasta que eclosiona. */
    }

    /** Rompe el huevo (lo mata) y se lo notifica al backend.
     *  Visual: swap del 🥚 a 💥, después 💀. El huevo no habla. */
    function breakEgg() {
        if (state.mascota) state.mascota.viva = false;
        var egg = document.getElementById('mascota-egg');
        if (egg) {
            egg.style.animation = 'none';
            egg.style.transition = 'transform 0.18s';
            egg.style.transform = 'scale(1.25)';
            egg.textContent = '💥';
            setTimeout(function () {
                var e = document.getElementById('mascota-egg');
                if (e) { e.textContent = '💀'; e.style.transform = 'scale(1)'; }
            }, 700);
        }
        /* Persistir en BD — viva=0. */
        apiFetch('break-egg', {}, function (err, d) {
            if (!err && d && d.ok && d.mascota) {
                state.mascota = d.mascota;
                updateHUD();
            }
            /* Invalidar la ventana de gestión si está cargada para
               que muestre el estado "muerto" al abrirla. */
            try { if (window.refreshMascotaWindow) window.refreshMascotaWindow(); } catch (_) {}
        });
    }

    /** Transición animada huevo → mascota cuando la API confirma la
     *  eclosión. Limpia el huevo del DOM, muestra el sprite y arranca
     *  los loops de física/animación si no estaban. */
    function transitionEggToMascota() {
        var egg = document.getElementById('mascota-egg');
        if (egg) {
            /* Animación de "crack" antes de borrarlo. */
            egg.style.animation = 'none';
            egg.style.transition = 'transform 0.4s';
            egg.style.transform = 'scale(1.15)';
            showBubble('¡Crack! 🐣');
            setTimeout(function () { if (egg.parentNode) egg.remove(); }, 400);
        }
        if (state.imgEl) state.imgEl.style.display = '';
        preloadFrames(state.skin, function () {
            renderFrame();
            if (!state.animLoopStarted) {
                state.animLoopStarted = true;
                requestAnimationFrame(animTick);
            }
        });
        if (!state.moveTickIv)  state.moveTickIv  = setInterval(moveTick,    16);
        if (!state.scanIv)      state.scanIv      = setInterval(scanWindows, CFG.WINDOW_SCAN_MS);
        if (!state.heartbeatIv) state.heartbeatIv = setInterval(heartbeat,   CFG.HEARTBEAT_MS);
        updateHUD();
        setTimeout(function () { showBubble('¡Hola! 👋'); }, 900);
    }

    /** Saca la mascota de la pantalla y para todos los timers.
     *  Útil si en el futuro hay que "ocultar" sin destruir. */
    function despawn() {
        if (!state.spawned) return;
        state.spawned = false;
        clearInterval(state.moveTickIv);
        clearInterval(state.scanIv);
        clearInterval(state.heartbeatIv);
        var root = document.getElementById('mascota-root');
        if (root) root.innerHTML = '';
        var menuBtn = document.getElementById('mascota-menu-btn');
        if (menuBtn) menuBtn.style.display = 'none';
    }

    /* ═══════════════════════════════════════════════════════════════
       API PÚBLICA
    ═══════════════════════════════════════════════════════════════ */
    global.MascotaEngine = {
        init:       init,
        spawn:      spawn,
        despawn:    despawn,
        isSpawned:  function () { return !!state.spawned; },
        isEgg:      function () { return !!(state.mascota && !state.mascota.eclosionado); },
        showBubble: showBubble,
        hideBubble: hideBubble,
        feed:       function () { handleMenuAction('feed'); },
        play:       function () { handleMenuAction('play'); },
        warm:       function () { handleMenuAction('warm'); },
        /* DEV: fuerza la eclosión saltándose el check de 3 días. Muestra
           el mismo prompt de nombre que el flujo normal. */
        forceHatch: function () { state.hatchPromptShown = false; promptHatchName({ dev: true }); },
        getState:   function () { return state; },
    };

})(window);