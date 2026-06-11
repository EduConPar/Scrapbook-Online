/* ──────────────────────────────────────────────────────────────────────
   CHAT MÓVIL — JS
   Reusa endpoints de /assets/profile/api.php:
     get-recent-chats   — un solo endpoint con todo: amigos mutuos +
                         último mensaje + unread + online + mute +
                         nickname
     get-messages       — historial de un chat (incluye read/delivered
                         por mensaje cuando es mío)
     send-message       — postear
     set-chat-mute      — silenciar 1h/8h/24h/1w/forever/off
     set-chat-nickname  — asignar apodo (vacío = limpiar)
   ────────────────────────────────────────────────────────────────────── */
(function() {
'use strict';

const CFG = window.__CH_CFG || {};
const API = CFG.API || '/assets/profile/api.php';
const USER_KEY = CFG.USER_KEY || '';
const USERS = CFG.USERS || {};

const POLL_MESSAGES_MS  = 2500;
const POLL_RECENTS_MS   = 8000;
const POLL_HEARTBEAT_MS = 30000;
const LONG_PRESS_MS     = 500;

/* Set de emojis usable en el composer. Pixel-friendly + comunes. */
const EMOJI_SET = [
    '😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰',
    '😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳',
    '😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤',
    '😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫',
    '🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵',
    '🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','👍','👎','👌','✌️','🤞','🤟',
    '🤘','🤙','👋','🤚','✋','🖖','👏','🙌','👐','🤲','🙏','💪','❤️','🧡','💛','💚',
    '💙','💜','🖤','🤍','💔','💕','💖','💗','💘','💝','✨','⭐','🌟','💫','🔥','💯',
    '🎉','🎊','🎁','🎂','🍰','🍕','🍔','🍟','🍿','☕','🍻','🍺','🥂','🍷'
];

const STATE = {
    chats: [],           /* array de {userKey, lastText, lastAt, lastFromMe,
                            unread, online, mutedUntil, nickname} */
    view: 'list',        /* 'list' | 'chat' */
    chatWith: null,
    chatLastSig: '',     /* signature de los msgs renderizados → evita re-paint */
    chatPollTimer: null,
    recentPollTimer: null,
    /* Acceso rápido al meta del chat actual. */
    currentMeta: null,
};

function esc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Win98 Alert / Confirm modales ──
   Reemplazos de alert()/confirm() nativos. Usan el mismo bezel Win98
   que el resto de modales del chat. chAlert resuelve cuando el usuario
   cierra el modal. chConfirm devuelve Promise<boolean>. */
function chDialog(opts) {
    return new Promise(function(resolve) {
        var bd = document.createElement('div');
        bd.className = 'ch-lp-backdrop is-open';
        bd.style.zIndex = '3100';
        var msgHtml = String(opts.message || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;')
            .replace(/\n/g, '<br>');
        var btns = (opts.showCancel !== false)
            ? '<div class="ch-lp-row" style="margin-top:10px;">' +
                  '<button class="button" data-act="cancel" type="button">' + (opts.cancelLabel || 'Cancelar') + '</button>' +
                  '<button class="button" data-act="ok" type="button">' + (opts.okLabel || 'Aceptar') + '</button>' +
              '</div>'
            : '<div class="ch-lp-row" style="margin-top:10px;">' +
                  '<button class="button" data-act="ok" type="button" style="flex:1;">' + (opts.okLabel || 'Aceptar') + '</button>' +
              '</div>';
        bd.innerHTML =
            '<div class="ch-lp-sheet">' +
                '<div class="ch-lp-title">' +
                    '<span>' + String(opts.title || 'Aviso').replace(/</g,'&lt;') + '</span>' +
                    '<button class="ch-lp-close" data-act="close" type="button">✕</button>' +
                '</div>' +
                '<div class="ch-lp-body" style="font-size:13px;line-height:1.4;">' +
                    msgHtml +
                    btns +
                '</div>' +
            '</div>';
        document.body.appendChild(bd);
        function done(val) {
            if (bd.parentNode) bd.parentNode.removeChild(bd);
            resolve(val);
        }
        bd.querySelector('[data-act="close"]').addEventListener('click', function() { done(false); });
        bd.querySelector('[data-act="ok"]').addEventListener('click', function() { done(true); });
        var cancelBtn = bd.querySelector('[data-act="cancel"]');
        if (cancelBtn) cancelBtn.addEventListener('click', function() { done(false); });
        bd.addEventListener('click', function(e) { if (e.target === bd) done(false); });
    });
}
function chAlert(message, title) {
    return chDialog({ message: message, title: title || 'Aviso', showCancel: false, okLabel: 'Aceptar' });
}
function chConfirm(message, title, okLabel) {
    return chDialog({ message: message, title: title || 'Confirmar', okLabel: okLabel || 'Aceptar' });
}

/* ── API helpers ── */
function apiGet(action) {
    return fetch(API + '?action=' + action, { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : { error: 'http' })
        .catch(() => ({ error: 'net' }));
}
function apiPost(action, body) {
    return fetch(API + '?action=' + action, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body || {})
    })
        .then(r => r.ok ? r.json() : { error: 'http' })
        .catch(() => ({ error: 'net' }));
}

/* Devuelve el meta del chat con uKey desde STATE.chats; null si no existe. */
function getMeta(uKey) {
    return STATE.chats.find(c => c.userKey === uKey) || null;
}
/* Nombre a mostrar: nickname si existe, label real si no. */
function displayName(uKey, meta) {
    const m = meta || getMeta(uKey);
    if (m && m.nickname) return m.nickname;
    const u = USERS[uKey];
    return (u && u.label) || uKey;
}

/* ── Carga lista (get-recent-chats) ── */
async function loadRecents() {
    const d = await apiGet('get-recent-chats');
    if (d && Array.isArray(d.chats)) {
        STATE.chats = d.chats;
    }
    if (STATE.view === 'list') renderList();
    /* Si estoy en chat, refrescar la cabecera (online/nickname). */
    if (STATE.view === 'chat' && STATE.chatWith) refreshChatHeader();
}

/* ── Render lista ── */
function renderList() {
    const el = document.getElementById('ch-list-content');
    if (!el) return;
    if (!STATE.chats.length) {
        el.innerHTML = '<div class="ch-list-empty">Aún no tienes amigos para chatear.<br>Sigue a otros usuarios desde tu perfil — cuando os sigáis mutuamente aparecerán aquí.</div>';
        return;
    }
    el.innerHTML = STATE.chats.map(c => {
        const u = USERS[c.userKey] || { label: c.userKey, image: '' };
        const name = c.nickname || u.label;
        const av = u.image
            ? '<img src="' + esc(u.image) + '" alt="">'
            : '<img class="ch-friend-av-fallback" src="../../assets/img/appIcons/profileIcon.png" alt="">';
        const presence = '<span class="ch-presence-dot' + (c.online ? ' is-online' : '') + '"></span>';
        /* Sub-text: último mensaje o "Tocar para chatear" si no hay. */
        let sub;
        if (c.lastText) {
            const prefix = c.lastFromMe ? 'Tú: ' : '';
            sub = prefix + c.lastText;
        } else {
            sub = 'Tocar para chatear';
        }
        const muteIcon = (c.mutedUntil > 0)
            ? '<img class="ch-friend-mute-icon" src="../../assets/img/appIcons/bellIcon.png" alt="" style="filter:grayscale(1);opacity:0.5;">'
            : '';
        const time = c.lastAt ? formatRelTime(c.lastAt) : '';
        const badge = c.unread > 0
            ? '<div class="ch-friend-badge">' + c.unread + '</div>'
            : '';
        return '<div class="ch-friend" data-user="' + esc(c.userKey) + '">' +
            '<div class="ch-friend-av">' + av + presence + '</div>' +
            '<div class="ch-friend-info">' +
                '<div class="ch-friend-name">' + esc(name) + muteIcon + '</div>' +
                '<div class="ch-friend-sub' + (c.unread > 0 ? ' has-unread' : '') + '">' + esc(sub) + '</div>' +
            '</div>' +
            '<div class="ch-friend-right">' +
                (time ? '<div class="ch-friend-time">' + esc(time) + '</div>' : '') +
                badge +
            '</div>' +
        '</div>';
    }).join('');
    /* Tap + long-press handlers. */
    el.querySelectorAll('[data-user]').forEach(card => attachCardHandlers(card));
}

function formatRelTime(ts) {
    const d = new Date(ts * 1000);
    if (isNaN(d.getTime())) return '';
    const now = new Date();
    const sameDay = d.toDateString() === now.toDateString();
    if (sameDay) {
        return String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
    }
    const diff = (now - d) / 86400000;
    if (diff < 7) {
        const dows = ['dom','lun','mar','mié','jue','vie','sáb'];
        return dows[d.getDay()];
    }
    return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0');
}

/* ── Long-press detection sobre las cards ── */
function attachCardHandlers(card) {
    let pressTimer = null;
    let longPressed = false;
    const uKey = card.dataset.user;

    function start() {
        longPressed = false;
        pressTimer = setTimeout(() => {
            longPressed = true;
            openLongPress(uKey);
        }, LONG_PRESS_MS);
    }
    function cancel() {
        if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; }
    }
    card.addEventListener('touchstart', start, { passive: true });
    card.addEventListener('touchend',   () => { cancel(); });
    card.addEventListener('touchmove',  () => { cancel(); });
    card.addEventListener('touchcancel', cancel);
    /* Mouse fallback para desktop. */
    card.addEventListener('mousedown',  start);
    card.addEventListener('mouseup',    cancel);
    card.addEventListener('mouseleave', cancel);
    /* Click solo se ejecuta si NO fue long-press. */
    card.addEventListener('click', (e) => {
        if (longPressed) { e.preventDefault(); e.stopPropagation(); return; }
        openChat(uKey);
    });
    /* Bloquea el menú contextual del browser en long-press. */
    card.addEventListener('contextmenu', e => e.preventDefault());
}

/* ── Vista chat individual ── */
function openChat(uKey) {
    const meta = getMeta(uKey);
    if (!meta) return;
    STATE.chatWith    = uKey;
    STATE.chatLastSig = '';
    STATE.view        = 'chat';
    STATE.currentMeta = meta;

    refreshChatHeader();

    document.getElementById('ch-list').classList.add('is-hidden');
    document.getElementById('ch-chat').classList.add('is-active');
    closeEmojiPanel();

    /* Limpia notif push del SO. Helper vive en parent (mobile.php). */
    try {
        const fn = (window.parent && window.parent !== window)
            ? window.parent.mhClearNotifications
            : window.mhClearNotifications;
        if (typeof fn === 'function') fn({ tag: 'chat:' + uKey });
    } catch (_) {}

    /* Poll de mensajes. */
    pollMessages(true);
    if (STATE.chatPollTimer) clearInterval(STATE.chatPollTimer);
    STATE.chatPollTimer = setInterval(() => pollMessages(false), POLL_MESSAGES_MS);

    setTimeout(() => {
        const inp = document.getElementById('ch-input');
        if (inp) inp.focus();
    }, 100);
}

function refreshChatHeader() {
    const uKey = STATE.chatWith;
    if (!uKey) return;
    const meta = getMeta(uKey) || STATE.currentMeta;
    const u = USERS[uKey] || { label: uKey, image: '' };
    const name = (meta && meta.nickname) || u.label;
    const avEl = document.getElementById('ch-chat-av');
    const nmEl = document.getElementById('ch-chat-name');
    const stEl = document.getElementById('ch-chat-status');
    const av = u.image
        ? '<img src="' + esc(u.image) + '" alt="">'
        : '<img class="ch-friend-av-fallback" src="../../assets/img/appIcons/profileIcon.png" alt="" style="width:60%;height:60%;margin:20%;">';
    const presence = '<span class="ch-presence-dot' + (meta && meta.online ? ' is-online' : '') + '"></span>';
    avEl.innerHTML = av + presence;
    nmEl.textContent = name;
    /* Status: "En línea" si online, "Última vez HH:MM" si offline y
       tenemos timestamp de presencia, vacío si nunca pingueó. */
    if (stEl) {
        if (meta && meta.online) {
            stEl.textContent = 'En línea';
            stEl.classList.add('is-online');
        } else if (meta && meta.lastSeenAt > 0) {
            stEl.textContent = 'Última vez ' + formatLastSeen(meta.lastSeenAt);
            stEl.classList.remove('is-online');
        } else {
            stEl.textContent = '';
            stEl.classList.remove('is-online');
        }
    }
}

/* "Última vez" — formato compacto. Hoy → "hoy HH:MM", ayer → "ayer HH:MM",
   esta semana → "lun HH:MM", más viejo → "DD/MM HH:MM". */
function formatLastSeen(ts) {
    const d = new Date(ts * 1000);
    if (isNaN(d.getTime())) return '';
    const now = new Date();
    const hh = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
    const sameDay = d.toDateString() === now.toDateString();
    if (sameDay) return 'hoy ' + hh;
    const y = new Date(now.getTime() - 86400000);
    if (d.toDateString() === y.toDateString()) return 'ayer ' + hh;
    const diffDays = (now - d) / 86400000;
    if (diffDays < 7) {
        const dows = ['dom','lun','mar','mié','jue','vie','sáb'];
        return dows[d.getDay()] + ' ' + hh;
    }
    return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + ' ' + hh;
}

function closeChat() {
    STATE.chatWith    = null;
    STATE.chatLastSig = '';
    STATE.view        = 'list';
    STATE.currentMeta = null;
    if (STATE.chatPollTimer) { clearInterval(STATE.chatPollTimer); STATE.chatPollTimer = null; }
    closeEmojiPanel();
    document.getElementById('ch-chat').classList.remove('is-active');
    document.getElementById('ch-list').classList.remove('is-hidden');
    loadRecents();
}

async function pollMessages(initial) {
    if (!STATE.chatWith) return;
    const target = STATE.chatWith;
    const d = await apiGet('get-messages&with=' + encodeURIComponent(target));
    if (STATE.chatWith !== target) return;
    if (!d || !Array.isArray(d.messages)) return;
    /* Signature incluye max id + read/delivered de los míos → re-render
       si cambia cualquier estado de ticks (no solo si llega msg nuevo). */
    const sig = d.messages.map(m => {
        const flags = (m.edited ? 'E' : '') + (m.deleted ? 'X' : '');
        if (m.from === USER_KEY) {
            return m.id + ':' + (m.read ? 'R' : (m.delivered ? 'D' : 'S')) + flags;
        }
        return m.id + flags;
    }).join(',');
    if (!initial && sig === STATE.chatLastSig) return;
    STATE.chatLastSig = sig;
    renderFeed(d.messages);
}

/* HTML helper para el placeholder de mensajes eliminados — replazado el
   emoji 🗑 por trashIcon.png para coherencia visual con el tema. */
const TRASH_ICON_HTML = '<img src="../../assets/img/appIcons/trashIcon.png" alt="" style="width:14px;height:14px;object-fit:contain;image-rendering:pixelated;vertical-align:-2px;margin-right:4px;">';

/* ── Renderizar el feed de mensajes ── */
function renderFeed(messages) {
    const feed = document.getElementById('ch-feed');
    if (!messages.length) {
        feed.innerHTML = '<div class="ch-feed-empty">No hay mensajes todavía. Escribe el primero ↓</div>';
        return;
    }
    const wasAtBottom = (feed.scrollHeight - feed.scrollTop - feed.clientHeight) < 60;
    feed.innerHTML = messages.map(m => {
        const isMine = (m.from === USER_KEY);
        const isDel  = !!m.deleted;
        let cls = 'ch-msg ' + (isMine ? 'is-mine' : 'is-them');
        if (isDel) cls += ' is-deleted';
        const time = formatTime(m.sentAt);
        const body = isDel
            ? '<span class="ch-msg-text">' + TRASH_ICON_HTML + 'Mensaje eliminado</span>'
            : '<span class="ch-msg-text">' + esc(m.text) + '</span>';
        const editedTag = (m.edited && !isDel)
            ? '<span class="ch-msg-edited">(editado)</span>'
            : '';
        let ticks = '';
        if (isMine && !isDel) {
            if (m.read) {
                ticks = '<span class="ch-ticks is-read" title="Leído">✓✓</span>';
            } else if (m.delivered) {
                ticks = '<span class="ch-ticks is-delivered" title="Entregado">✓✓</span>';
            } else {
                ticks = '<span class="ch-ticks is-sent" title="Enviado">✓</span>';
            }
        }
        return '<div class="' + cls + '" data-msg-id="' + m.id +
                '" data-mine="' + (isMine ? '1' : '0') +
                '" data-deleted="' + (isDel ? '1' : '0') + '">' +
            body +
            '<span class="ch-msg-meta">' + ticks + editedTag + '<span>' + time + '</span></span>' +
        '</div>';
    }).join('');
    /* Long-press en MIS mensajes no eliminados → menú edit/delete. */
    feed.querySelectorAll('.ch-msg.is-mine:not(.is-deleted)').forEach(el => attachMsgLongPress(el));
    if (wasAtBottom) feed.scrollTop = feed.scrollHeight;
}

/* ── Long-press en mensaje → menu edit/delete ── */
function attachMsgLongPress(el) {
    let pressTimer = null;
    let longPressed = false;
    function start() {
        longPressed = false;
        pressTimer = setTimeout(() => {
            longPressed = true;
            openMsgMenu(el);
        }, 500);
    }
    function cancel() { if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; } }
    el.addEventListener('touchstart',  start, { passive: true });
    el.addEventListener('touchend',    cancel);
    el.addEventListener('touchmove',   cancel);
    el.addEventListener('touchcancel', cancel);
    el.addEventListener('mousedown',   start);
    el.addEventListener('mouseup',     cancel);
    el.addEventListener('mouseleave',  cancel);
    el.addEventListener('contextmenu', e => {
        e.preventDefault();
        openMsgMenu(el);
    });
}

function openMsgMenu(msgEl) {
    const msgId = +msgEl.dataset.msgId;
    const text  = (msgEl.querySelector('.ch-msg-text')?.textContent) || '';
    const bd = document.getElementById('ch-msg-backdrop');
    bd.dataset.msgId = msgId;
    bd.dataset.text  = text;
    bd.classList.add('is-open');
}
function closeMsgMenu() { document.getElementById('ch-msg-backdrop').classList.remove('is-open'); }

/* Wire ONCE — defer al final del módulo. */

function formatTime(sentAt) {
    if (!sentAt) return '';
    const d = new Date(sentAt * 1000);
    if (isNaN(d.getTime())) return '';
    return String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
}

async function sendMessage() {
    const inp = document.getElementById('ch-input');
    const text = (inp.value || '').trim();
    if (!text || !STATE.chatWith) return;
    inp.value = '';
    inp.style.height = '';
    const target = STATE.chatWith;
    await apiPost('send-message', { to: target, text });
    if (STATE.chatWith === target) pollMessages(true);
}

/* ─────────────────────────────────────────────────────────
   LONG-PRESS MODAL: nickname + mute
   ───────────────────────────────────────────────────────── */
function openLongPress(uKey) {
    const meta = getMeta(uKey);
    if (!meta) return;
    const u = USERS[uKey] || { label: uKey };
    document.getElementById('ch-lp-title-text').textContent = (meta.nickname || u.label);
    document.getElementById('ch-lp-nick-input').value = meta.nickname || '';
    /* Marca la opción de mute activa visualmente. */
    refreshMuteStatus(uKey);
    const backdrop = document.getElementById('ch-lp-backdrop');
    backdrop.dataset.user = uKey;
    backdrop.classList.add('is-open');
}
function closeLongPress() {
    document.getElementById('ch-lp-backdrop').classList.remove('is-open');
}
function refreshMuteStatus(uKey) {
    const meta = getMeta(uKey);
    const muted = meta && meta.mutedUntil > 0;
    const status = document.getElementById('ch-lp-mute-status');
    if (!muted) {
        status.textContent = 'Notificaciones activadas.';
    } else {
        const until = new Date(meta.mutedUntil * 1000);
        const now = new Date();
        const diffDays = (until - now) / 86400000;
        if (diffDays > 365) {
            status.textContent = 'Silenciado indefinidamente.';
        } else {
            const fmt = until.toLocaleDateString('es-ES', { day:'2-digit', month:'short' }) +
                        ' ' + String(until.getHours()).padStart(2,'0') + ':' +
                        String(until.getMinutes()).padStart(2,'0');
            status.textContent = 'Silenciado hasta ' + fmt + '.';
        }
    }
    /* Resaltar opción activa. */
    const opts = document.querySelectorAll('.ch-lp-opt[data-mute]');
    opts.forEach(o => o.classList.remove('is-selected'));
    if (!muted) {
        const off = document.querySelector('.ch-lp-opt[data-mute="off"]');
        if (off) off.classList.add('is-selected');
    }
}

/* Handlers del modal. */
document.getElementById('ch-lp-close').addEventListener('click', closeLongPress);
document.getElementById('ch-lp-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeLongPress();
});

/* Mute options. */
document.querySelectorAll('.ch-lp-opt[data-mute]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const uKey = document.getElementById('ch-lp-backdrop').dataset.user;
        if (!uKey) return;
        const duration = btn.dataset.mute;
        btn.disabled = true;
        const r = await apiPost('set-chat-mute', { with: uKey, duration });
        btn.disabled = false;
        if (r && r.ok) {
            const meta = getMeta(uKey);
            if (meta) meta.mutedUntil = r.mutedUntil || 0;
            refreshMuteStatus(uKey);
            renderList();
        }
    });
});

/* Nickname save. */
document.getElementById('ch-lp-nick-save').addEventListener('click', async () => {
    const uKey = document.getElementById('ch-lp-backdrop').dataset.user;
    if (!uKey) return;
    const nick = document.getElementById('ch-lp-nick-input').value.trim();
    const r = await apiPost('set-chat-nickname', { with: uKey, nickname: nick });
    if (r && r.ok) {
        const meta = getMeta(uKey);
        if (meta) meta.nickname = r.nickname || '';
        document.getElementById('ch-lp-title-text').textContent = (r.nickname || (USERS[uKey] && USERS[uKey].label) || uKey);
        renderList();
        if (STATE.view === 'chat' && STATE.chatWith === uKey) refreshChatHeader();
        closeLongPress();
    }
});
document.getElementById('ch-lp-nick-clear').addEventListener('click', async () => {
    const uKey = document.getElementById('ch-lp-backdrop').dataset.user;
    if (!uKey) return;
    document.getElementById('ch-lp-nick-input').value = '';
    const r = await apiPost('set-chat-nickname', { with: uKey, nickname: '' });
    if (r && r.ok) {
        const meta = getMeta(uKey);
        if (meta) meta.nickname = '';
        document.getElementById('ch-lp-title-text').textContent = (USERS[uKey] && USERS[uKey].label) || uKey;
        renderList();
        if (STATE.view === 'chat' && STATE.chatWith === uKey) refreshChatHeader();
    }
});

/* ─────────────────────────────────────────────────────────
   EMOJI PICKER
   ───────────────────────────────────────────────────────── */
const emojiPanel = document.getElementById('ch-emoji-panel');
emojiPanel.innerHTML = EMOJI_SET.map(e =>
    '<button class="ch-emoji" type="button" tabindex="-1">' + e + '</button>'
).join('');
emojiPanel.addEventListener('click', (e) => {
    const btn = e.target.closest('.ch-emoji');
    if (!btn) return;
    const inp = document.getElementById('ch-input');
    const txt = btn.textContent;
    const start = inp.selectionStart || inp.value.length;
    const end = inp.selectionEnd || inp.value.length;
    inp.value = inp.value.slice(0, start) + txt + inp.value.slice(end);
    inp.focus();
    inp.selectionStart = inp.selectionEnd = start + txt.length;
});
function toggleEmojiPanel() { emojiPanel.classList.toggle('is-open'); }
function closeEmojiPanel()  { emojiPanel.classList.remove('is-open'); }
document.getElementById('ch-emoji-btn').addEventListener('click', toggleEmojiPanel);

/* ── Modal mensaje (edit/delete) wiring ── */
document.getElementById('ch-msg-close').addEventListener('click', closeMsgMenu);
document.getElementById('ch-msg-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeMsgMenu();
});
document.getElementById('ch-msg-edit').addEventListener('click', () => {
    const bd = document.getElementById('ch-msg-backdrop');
    const msgId = +bd.dataset.msgId;
    const text  = bd.dataset.text || '';
    closeMsgMenu();
    openEditModal(msgId, text);
});
document.getElementById('ch-msg-delete').addEventListener('click', async () => {
    const bd = document.getElementById('ch-msg-backdrop');
    const msgId = +bd.dataset.msgId;
    closeMsgMenu();
    const ok = await chConfirm(
        '¿Eliminar este mensaje?\n\nQuien lo haya visto verá "Mensaje eliminado".',
        'Eliminar mensaje',
        'Eliminar'
    );
    if (!ok) return;
    const r = await apiPost('delete-message', { messageId: msgId });
    if (r && r.ok) {
        STATE.chatLastSig = '';   /* fuerza re-render */
        pollMessages(true);
    }
});

/* ── Modal de edición ── */
function openEditModal(msgId, currentText) {
    const bd  = document.getElementById('ch-edit-backdrop');
    const inp = document.getElementById('ch-edit-input');
    bd.dataset.msgId = msgId;
    bd.dataset.original = currentText;
    inp.value = currentText;
    bd.classList.add('is-open');
    setTimeout(() => { inp.focus(); inp.select(); }, 50);
}
function closeEditModal() { document.getElementById('ch-edit-backdrop').classList.remove('is-open'); }
document.getElementById('ch-edit-close').addEventListener('click', closeEditModal);
document.getElementById('ch-edit-cancel').addEventListener('click', closeEditModal);
document.getElementById('ch-edit-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
document.getElementById('ch-edit-save').addEventListener('click', async () => {
    const bd  = document.getElementById('ch-edit-backdrop');
    const msgId = +bd.dataset.msgId;
    const orig  = bd.dataset.original || '';
    const newText = document.getElementById('ch-edit-input').value.trim();
    if (!newText || newText === orig) { closeEditModal(); return; }
    const r = await apiPost('edit-message', { messageId: msgId, text: newText });
    closeEditModal();
    if (r && r.ok) {
        STATE.chatLastSig = '';
        pollMessages(true);
    }
});

/* ── Event listeners ── */
document.getElementById('ch-back-list').addEventListener('click', closeChat);
/* preventDefault en pointerdown del botón Enviar evita que el botón
   robe el foco al textarea — el SO móvil mantiene el teclado abierto.
   Click normal sigue funcionando (pointerdown no cancela el click,
   solo el cambio de foco). Mismo truco aplica al botón de emojis. */
document.getElementById('ch-send').addEventListener('pointerdown', (e) => e.preventDefault());
document.getElementById('ch-send').addEventListener('click', sendMessage);
document.getElementById('ch-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});
document.getElementById('ch-input').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(90, this.scrollHeight) + 'px';
});

/* Botones que vuelven al menú: status-bar, X title-bar, botón nuevo arriba. */
function chGoMenu(e) {
    if (e) e.preventDefault();
    if (window.parent && window.parent !== window) {
        try { window.parent.postMessage({ type: 'shell:back' }, '*'); return; } catch (_) {}
    }
    try { history.back(); } catch (_) { location.href = '../../mobile.php'; }
}
document.getElementById('ch-menu-link').addEventListener('click', chGoMenu);
document.getElementById('ch-titlebar-close').addEventListener('click', chGoMenu);

/* ── Listener para que el SW suprima la notif si estamos en este chat ── */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function(e) {
        const d = e.data || {};
        if (d.type !== 'sw:is-chat-focused') return;
        const port = e.ports && e.ports[0];
        if (!port) return;
        const focused = (STATE.chatWith === d.fromKey)
                     && (document.visibilityState === 'visible')
                     && (STATE.view === 'chat');
        try { port.postMessage({ focused }); } catch (_) {}
    });
}

/* ── Polling de recents mientras estamos en la lista. ── */
STATE.recentPollTimer = setInterval(() => {
    if (STATE.view === 'list' && document.visibilityState === 'visible') {
        loadRecents();
    }
}, POLL_RECENTS_MS);

/* ── Heartbeat de presencia ──
   Sin esto la app de Chat no actualiza user_presence.last_at → los otros
   usuarios ven al user como "offline" / "Última vez …" aunque esté
   leyendo activamente. Ping cada 30s solo si la pestaña está visible. */
function chHeartbeat() {
    if (document.visibilityState !== 'visible') return;
    apiGet('heartbeat');
}
chHeartbeat();
setInterval(chHeartbeat, POLL_HEARTBEAT_MS);
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') chHeartbeat();
});

/* ── Deep-link #chat=USERKEY (push) ── */
function handleHash() {
    const m = /#chat=([a-z0-9_-]+)/i.exec(location.hash || '');
    if (!m) return;
    const uKey = m[1];
    try { history.replaceState(null, '', location.pathname); } catch (_) {}
    /* Solo abrir si está en la lista de mutuos (ya cargada). Si aún no
       cargó, marcamos para procesarlo después. */
    if (getMeta(uKey)) {
        openChat(uKey);
    } else {
        STATE._pendingOpenKey = uKey;
    }
}
window.addEventListener('hashchange', handleHash);

/* Init. */
(async function init() {
    await loadRecents();
    handleHash();
    if (STATE._pendingOpenKey && getMeta(STATE._pendingOpenKey)) {
        openChat(STATE._pendingOpenKey);
        STATE._pendingOpenKey = null;
    }
})();

})();
