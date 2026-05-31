/* ──────────────────────────────────────────────────────────────────────
   SCRAPBOOK MELON — DISCORD BOT
   ──────────────────────────────────────────────────────────────────────
   Otorga puntos de Autismo a usuarios vinculados (usuarios.discord_user_id)
   por enviar mensajes y por tiempo en canales de voz.
   - +N puntos por mensaje, con cooldown anti-spam.
   - +N puntos por minuto completo en voz, contados al SALIR del canal.
   Si el usuario no está vinculado en la tienda, la UPDATE no afecta a nada
   (0 rows) y simplemente se ignora.
   ────────────────────────────────────────────────────────────────────── */
import 'dotenv/config';                /* lee `.env` y mete las vars en process.env */
import { Client, GatewayIntentBits, Events, Partials } from 'discord.js';
import mysql from 'mysql2/promise';

const cfg = {
    token            : process.env.DISCORD_BOT_TOKEN,
    guildId          : process.env.DISCORD_GUILD_ID,
    msgPoints        : Number(process.env.POINTS_PER_MESSAGE      || 1),
    voicePoints      : Number(process.env.POINTS_PER_VOICE_MINUTE || 1),
    reactionPoints   : Number(process.env.POINTS_PER_REACTION     || 10),
    msgCooldownSec   : Number(process.env.MESSAGE_COOLDOWN_SECONDS || 60),
    excludedChannels : (process.env.EXCLUDED_CHANNEL_IDS || '').split(',').map(s => s.trim()).filter(Boolean),
    melonHubChannel  : (process.env.MELON_HUB_CHANNEL_ID || '').trim(),
    db: {
        host     : process.env.DB_HOST,
        user     : process.env.DB_USER,
        password : process.env.DB_PASS || process.env.DB_PASSWORD || '',
        database : process.env.DB_NAME,
        port     : Number(process.env.DB_PORT || 3306),
        ssl      : process.env.DB_SSL === 'true' ? { rejectUnauthorized: false } : undefined,
        connectionLimit : 4,
        waitForConnections : true,
    },
};

if (!cfg.token || !cfg.db.host) {
    console.error('Faltan variables de entorno obligatorias (DISCORD_BOT_TOKEN, DB_HOST, …)');
    process.exit(1);
}

const pool = mysql.createPool(cfg.db);

async function awardPoints(discordUserId, points, reason) {
    if (points <= 0) return;
    try {
        const [r] = await pool.execute(
            'UPDATE usuarios SET autismo = autismo + ? WHERE discord_user_id = ?',
            [points, discordUserId]
        );
        if (r.affectedRows > 0) {
            console.log(`+${points}🧠 → ${discordUserId} (${reason})`);
        }
    } catch (e) {
        console.error('award error:', e.message);
    }
}

const client = new Client({
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildMessages,
        GatewayIntentBits.GuildVoiceStates,
        GatewayIntentBits.GuildMessageReactions,
    ],
    /* Partials para que dispare `messageReactionAdd` también en mensajes
       antiguos que el bot no tiene cacheados (≠ no caben los últimos 50). */
    partials: [Partials.Message, Partials.Channel, Partials.Reaction, Partials.User],
});

/* SOLO el corazón rojo estándar (❤️) cuenta — el mismo con el que el bot
   añade la reacción inicial. Aceptamos las dos formas unicode porque los
   clientes de Discord a veces lo mandan con la variation selector U+FE0F
   y a veces sin ella, pero ambas se renderizan visualmente igual. Cualquier
   otra variante (💛 💚 💙 💜 🖤 etc) y los custom emoji NO cuentan. */
const HEART_EMOJIS = new Set(['❤️', '❤']);
function isHeart(emoji) {
    if (!emoji || !emoji.name) return false;
    if (emoji.id) return false;            // custom emoji → no cuenta
    return HEART_EMOJIS.has(emoji.name);
}

const lastMsgAt = new Map();     // discordUserId → ms

client.on(Events.MessageCreate, async (msg) => {
    if (msg.author?.bot || !msg.guild) return;
    if (cfg.guildId && msg.guild.id !== cfg.guildId) return;
    if (cfg.excludedChannels.includes(msg.channelId)) return;

    const now = Date.now();
    const prev = lastMsgAt.get(msg.author.id) || 0;
    if (now - prev < cfg.msgCooldownSec * 1000) return;
    lastMsgAt.set(msg.author.id, now);
    await awardPoints(msg.author.id, cfg.msgPoints, 'mensaje');
});

/* Track de entradas a voz: discordUserId → timestamp_ms entrada. */
const voiceJoins = new Map();

client.on(Events.VoiceStateUpdate, async (oldS, newS) => {
    const uid = newS.id;
    if (newS.member?.user?.bot) return;
    if (cfg.guildId && newS.guild?.id !== cfg.guildId) return;

    const wasIn  = !!oldS.channelId;
    const isIn   = !!newS.channelId;

    if (!wasIn && isIn) {
        voiceJoins.set(uid, Date.now());
        return;
    }
    if (wasIn && !isIn) {
        const start = voiceJoins.get(uid);
        voiceJoins.delete(uid);
        if (!start) return;
        const minutes = Math.floor((Date.now() - start) / 60_000);
        if (minutes >= 1) {
            await awardPoints(uid, minutes * cfg.voicePoints, `${minutes} min en voz`);
        }
        return;
    }
    /* Cambio de canal → mantenemos timestamp; no premiamos ni penalizamos. */
});

/* ── REACCIONES DE CORAZÓN sobre mensajes publicados vía Melon Hub ──
   Cada reacción única de corazón en un mensaje del canal #artes que esté
   en `webhook_posts` otorga `POINTS_PER_REACTION` puntos al autor original.
   Reglas:
   - Solo corazones unicode (custom emojis no cuentan).
   - Solo en el canal configurado MELON_HUB_CHANNEL_ID.
   - Solo mensajes registrados en webhook_posts (los publicados desde la
     galería; los antiguos no cuentan).
   - El autor del post no puede premiarse a sí mismo.
   - Una persona = una reacción premiable por mensaje. Si añade un segundo
     corazón distinto, o quita y vuelve a poner, NO se duplica.
*/
client.on(Events.MessageReactionAdd, async (reaction, user) => {
    try {
        if (user.bot) return;
        /* Cuando viene de un partial, hay que hacer fetch() para tener los
           datos completos (emoji.name, message.channelId, etc.). */
        if (reaction.partial) await reaction.fetch();
        if (reaction.message.partial) await reaction.message.fetch();

        if (!isHeart(reaction.emoji)) return;
        if (cfg.melonHubChannel && reaction.message.channelId !== cfg.melonHubChannel) return;

        /* ¿Este mensaje está registrado como post vía webhook de la app? */
        const [postRows] = await pool.execute(
            'SELECT user_id FROM webhook_posts WHERE message_id = ? AND kind = ?',
            [reaction.message.id, 'discord']
        );
        if (!postRows.length) return;
        const authorUserId = postRows[0].user_id;

        /* Anti-autopremio: si el que reacciona es el autor, fuera. */
        const [authorRows] = await pool.execute(
            'SELECT discord_user_id FROM usuarios WHERE id = ?',
            [authorUserId]
        );
        const authorDiscordId = authorRows[0]?.discord_user_id || null;
        if (authorDiscordId && authorDiscordId === user.id) return;

        /* Dedupe (message_id, reactor) — si ya está, INSERT IGNORE no hace
           nada y no entra en el IF. Una sola transacción cubre el doble
           click rápido. */
        const conn = await pool.getConnection();
        try {
            await conn.beginTransaction();
            const [ins] = await conn.execute(
                'INSERT IGNORE INTO webhook_reactions (message_id, reactor_discord_id) VALUES (?, ?)',
                [reaction.message.id, user.id]
            );
            if (ins.affectedRows > 0) {
                await conn.execute(
                    'UPDATE usuarios SET autismo = autismo + ? WHERE id = ?',
                    [cfg.reactionPoints, authorUserId]
                );
                console.log(`+${cfg.reactionPoints}🧠 → user_id=${authorUserId} (❤ de ${user.tag || user.id} en msg ${reaction.message.id})`);
            }
            await conn.commit();
        } catch (e) {
            await conn.rollback();
            throw e;
        } finally {
            conn.release();
        }
    } catch (e) {
        console.error('reaction error:', e.message);
    }
});

client.once(Events.ClientReady, (c) => {
    console.log(`▶ Bot conectado como ${c.user.tag} — escuchando ${client.guilds.cache.size} guild(s).`);
});

/* Cierre limpio: si el proceso se mata (Fly.io reinicio, deploy), liquida
   las sesiones de voz abiertas para no perder minutos. */
async function shutdown(signal) {
    console.log(`\n${signal} recibido — cerrando…`);
    const now = Date.now();
    for (const [uid, start] of voiceJoins) {
        const minutes = Math.floor((now - start) / 60_000);
        if (minutes >= 1) await awardPoints(uid, minutes * cfg.voicePoints, 'shutdown');
    }
    await pool.end().catch(()=>{});
    client.destroy();
    process.exit(0);
}
process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT',  () => shutdown('SIGINT'));

client.login(cfg.token);
