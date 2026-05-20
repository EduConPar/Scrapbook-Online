<?php
session_start();
require_once __DIR__ . '/assets/config.php';
require_once __DIR__ . '/db.php';

$userKey = $_SESSION['user'] ?? null;
if (!$userKey || !isset($loginUsers[$userKey])) {
    header('Location: index.php');
    exit;
}

$userLabel = $loginUsers[$userKey]['label'];

// Comprobar si ya tiene pareja
$stmt = $pdo->prepare("
    SELECT p.id, p.fecha_inicio, u1.username as user1, u2.username as user2
    FROM parejas p
    JOIN usuarios u1 ON p.usuario1_id = u1.id
    JOIN usuarios u2 ON p.usuario2_id = u2.id
    WHERE u1.username = ? OR u2.username = ?
");
$stmt->execute([strtolower($userLabel), strtolower($userLabel)]);
$pareja = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuestro espacio</title>
    <link rel="stylesheet" href="assets/css/98.css">
    <link rel="stylesheet" href="assets/css/desktop.css">
</head>
<body class="<?php echo $userKey === 'user1' ? 'dark' : 'angie'; ?>">

<div style="padding: 16px;">
    <button class="button" onclick="history.back()">◄ Volver</button>
    <span style="margin-left: 12px; font-size: 13px;">Hola, <?php echo htmlspecialchars($userLabel); ?></span>
</div>

<?php if (!$pareja): ?>
<!-- SIN PAREJA: mostrar opciones -->
<div class="window" style="width: 320px; margin: 40px auto;">
    <div class="title-bar">
        <div class="title-bar-text">💑 Nuestro espacio</div>
    </div>
    <div class="window-body" style="padding: 16px; text-align: center;">
        <p style="margin-bottom: 16px; font-size: 12px;">Todavía no estás conectado con tu pareja.</p>
        <button class="button" id="btn-invitar" style="width: 100%; margin-bottom: 8px;">💌 Invitar a mi pareja</button>
    </div>
</div>

<!-- VENTANA DE INVITACIÓN -->
<div class="window" id="invite-window" style="display:none; width: 280px; margin: 0 auto;">
    <div class="title-bar">
        <div class="title-bar-text">💌 Invitar pareja</div>
        <div class="title-bar-controls">
            <button aria-label="Close" id="invite-close"></button>
        </div>
    </div>
    <div class="window-body" style="padding: 12px;">
        <p style="font-size: 11px; margin-bottom: 8px;">Selecciona a tu pareja:</p>
        <div id="user-list" style="margin-bottom: 10px;"></div>
        <p id="invite-status" style="font-size: 11px; color: green;"></p>
    </div>
</div>

<!-- NOTIFICACIÓN DE INVITACIÓN RECIBIDA -->
<div id="partner-notif" style="display:none; position: fixed; bottom: 60px; right: 16px; z-index: 5000;">
    <div class="window" style="width: 260px;">
        <div class="title-bar">
            <div class="title-bar-text">💑 Invitación de pareja</div>
        </div>
        <div class="window-body" style="padding: 10px;">
            <p id="partner-notif-msg" style="font-size: 11px; margin-bottom: 8px;"></p>
            <div class="field-row-stacked" style="margin-bottom: 8px;">
                <label style="font-size: 11px;">Fecha en que empezasteis:</label>
                <input type="date" id="partner-fecha" style="width: 100%;">
            </div>
            <div class="field-row" style="justify-content: flex-end; gap: 4px;">
                <button class="button" id="partner-reject">Rechazar</button>
                <button class="button" id="partner-accept">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
const currentUserKey = '<?php echo $userKey; ?>';

// Cargar lista de usuarios para invitar
document.getElementById('btn-invitar').addEventListener('click', function() {
    document.getElementById('invite-window').style.display = 'block';
    fetch('assets/couple/get-users.php')
    .then(r => r.json())
    .then(users => {
        const list = document.getElementById('user-list');
        list.innerHTML = '';
        if (!users.length) { list.innerHTML = '<p style="font-size:11px;">No hay otros usuarios.</p>'; return; }
        users.forEach(u => {
            const btn = document.createElement('button');
            btn.className = 'button';
            btn.textContent = u.label;
            btn.style.cssText = 'width:100%;margin-bottom:4px;';
            btn.addEventListener('click', function() {
                btn.disabled = true;
                fetch('assets/couple/invite-partner.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ toUser: u.key })
                })
                .then(r => r.json())
                .then(data => {
                    const status = document.getElementById('invite-status');
                    if (data.error) { status.style.color = 'red'; status.textContent = data.error; btn.disabled = false; return; }
                    status.style.color = 'green';
                    status.textContent = '✅ Invitación enviada a ' + u.label;
                });
            });
            list.appendChild(btn);
        });
    });
});

document.getElementById('invite-close').addEventListener('click', function() {
    document.getElementById('invite-window').style.display = 'none';
});

// Comprobar invitaciones recibidas
let currentPartnerInvite = null;

function checkPartnerInvites() {
    fetch('assets/couple/get-partner-invites.php')
    .then(r => r.json())
    .then(data => {
        if (!Array.isArray(data) || !data.length) return;
        const inv = data[0];
        if (currentPartnerInvite && currentPartnerInvite.id === inv.id) return;
        currentPartnerInvite = inv;
        document.getElementById('partner-notif-msg').textContent = inv.fromLabel + ' quiere ser tu pareja 💕';
        document.getElementById('partner-notif').style.display = 'block';
    })
    .catch(() => {});
}

function respondInvite(action) {
    if (!currentPartnerInvite) return;
    const fecha = document.getElementById('partner-fecha').value;
    if (action === 'accept' && !fecha) {
        alert('Por favor introduce la fecha en que empezasteis.');
        return;
    }
    fetch('assets/couple/respond-partner-invite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ inviteId: currentPartnerInvite.id, action: action, fecha: fecha })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        document.getElementById('partner-notif').style.display = 'none';
        if (action === 'accept') location.reload();
    })
    .catch(() => {});
}

document.getElementById('partner-accept').addEventListener('click', () => respondInvite('accept'));
document.getElementById('partner-reject').addEventListener('click', () => respondInvite('reject'));

checkPartnerInvites();
setInterval(checkPartnerInvites, 5000);
</script>

<?php else: ?>
<!-- CON PAREJA: mostrar el calendario -->
<div style="padding: 16px;">
    <p style="font-size: 12px;">✅ Conectado con tu pareja. ¡El calendario viene pronto!</p>
    <p style="font-size: 11px; color: #808080;">Juntos desde: <?php echo $pareja['fecha_inicio']; ?></p>
</div>
<?php endif; ?>

</body>
</html>