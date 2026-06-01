#!/bin/bash
# ─────────────────────────────────────────────────────────────────────
# setup-https.sh — Genera un certificado autofirmado válido para
# localhost + tu IP LAN y lo instala en XAMPP. Tras correrlo, tu PWA
# podrá instalarse "como app de verdad" (display:standalone) desde el
# móvil cuando entres por https://.
#
# Tras ejecutar:
#   1) Reinicia XAMPP (sudo /opt/lampp/lampp restart)
#   2) En el móvil entra por https://<tu-ip>/scrapbookOnline/
#   3) Acepta el aviso de "certificado no fiable" (una vez)
#   4) La PWA se instala correctamente y abre SIN barras de navegador.
# ─────────────────────────────────────────────────────────────────────
set -e

if [ "$EUID" -ne 0 ]; then
    echo "Este script tiene que correr con sudo (toca archivos en /opt/lampp/etc/)."
    echo "Reintenta:  sudo bash $0"
    exit 1
fi

SSL_DIR="/opt/lampp/etc"
CRT="$SSL_DIR/ssl.crt/server.crt"
KEY="$SSL_DIR/ssl.key/server.key"
BACKUP_SUFFIX=".bak.$(date +%Y%m%d%H%M%S)"

# Detectar las IPs LAN del propio host. Cogemos las que pertenezcan al
# rango privado típico (192.168/16, 10/8, 172.16-31/12).
LAN_IPS=$(awk '
    /inet / && $2 !~ /^127\./ && $2 !~ /^::1/ {
        split($2, a, "/")
        if (a[1] ~ /^(192\.168|10\.|172\.(1[6-9]|2[0-9]|3[01]))\./) print a[1]
    }
' <(ip -4 addr show 2>/dev/null || hostname -I 2>/dev/null | tr " " "\n" | sed "s/^/inet /"))

if [ -z "$LAN_IPS" ]; then
    # Fallback vía /proc/net/fib_trie por si ip/hostname no están.
    LAN_IPS=$(awk '/^\s+\|--\s+(192\.168|10\.|172\.(1[6-9]|2[0-9]|3[01]))\./ { print $2 }' /proc/net/fib_trie 2>/dev/null | grep -v '\.0$\|\.255$' | sort -u)
fi

if [ -z "$LAN_IPS" ]; then
    echo "[!] No detecté ninguna IP LAN privada. Introdúcela manualmente:"
    read -r IP
    LAN_IPS="$IP"
fi

echo "[+] IPs LAN detectadas:"
echo "$LAN_IPS" | sed 's/^/    /'

# Construir las entradas SAN para el cert. Subject Alternative Name es
# lo que los navegadores validan ahora — el Common Name está deprecado.
SAN_LINES="DNS:localhost\nDNS:nobara-pc.local\nIP:127.0.0.1\nIP:::1"
while IFS= read -r ip; do
    [ -n "$ip" ] && SAN_LINES="${SAN_LINES}\nIP:${ip}"
done <<< "$LAN_IPS"

echo "[+] SAN del certificado:"
echo -e "$SAN_LINES" | sed 's/^/    /'

# Backup de los certs viejos.
if [ -f "$CRT" ]; then cp "$CRT" "${CRT}${BACKUP_SUFFIX}"; echo "[+] Backup cert antiguo: ${CRT}${BACKUP_SUFFIX}"; fi
if [ -f "$KEY" ]; then cp "$KEY" "${KEY}${BACKUP_SUFFIX}"; echo "[+] Backup key  antigua: ${KEY}${BACKUP_SUFFIX}"; fi

# Config temporal de OpenSSL con SAN.
CFG=$(mktemp)
cat > "$CFG" <<EOF
[req]
distinguished_name = req
prompt             = no
x509_extensions    = v3_ca

[req]
CN = Melon Hub Local

[v3_ca]
subjectAltName = @alt_names
basicConstraints = critical, CA:TRUE
keyUsage = critical, digitalSignature, keyCertSign
extendedKeyUsage = serverAuth

[alt_names]
EOF

# Volcar SAN_LINES en el bloque [alt_names] como entradas numeradas.
i=1
while IFS= read -r line; do
    [ -z "$line" ] && continue
    case "$line" in
        DNS:*) echo "DNS.$i = ${line#DNS:}" >> "$CFG" ;;
        IP:*)  echo "IP.$i  = ${line#IP:}"  >> "$CFG" ;;
    esac
    i=$((i+1))
done <<< "$(echo -e "$SAN_LINES")"

# El bloque [req] estaba duplicado intencionalmente para incluir CN —
# rehacemos el archivo limpio.
cat > "$CFG" <<EOF
[req]
default_bits       = 2048
prompt             = no
distinguished_name = req_dn
x509_extensions    = v3_ext

[req_dn]
CN = Melon Hub Local

[v3_ext]
subjectAltName       = @alt_names
basicConstraints     = critical, CA:TRUE
keyUsage             = critical, digitalSignature, keyCertSign
extendedKeyUsage     = serverAuth

[alt_names]
$(
    i=1
    while IFS= read -r line; do
        [ -z "$line" ] && continue
        case "$line" in
            DNS:*) echo "DNS.$i = ${line#DNS:}" ;;
            IP:*)  echo "IP.$i = ${line#IP:}"  ;;
        esac
        i=$((i+1))
    done <<< "$(echo -e "$SAN_LINES")"
)
EOF

echo "[+] Generando cert autofirmado con validez 5 años…"
openssl req -x509 -newkey rsa:2048 -sha256 -days 1825 -nodes \
    -keyout "$KEY" -out "$CRT" -config "$CFG" 2>/dev/null

chmod 600 "$KEY"
chmod 644 "$CRT"
rm -f "$CFG"

echo
echo "[✓] Listo. Cert nuevo en $CRT"
echo
echo "─── PASOS SIGUIENTES ─────────────────────────────────"
echo "  1) Reinicia XAMPP:   sudo /opt/lampp/lampp restart"
echo "  2) Abre puerto 443:  sudo firewall-cmd --add-service=https"
echo "  3) En el móvil:      https://<tu-ip>/scrapbookOnline/mobile.php"
echo "  4) Acepta el aviso de cert. (Chrome: pulsa Avanzado → Continuar.)"
echo "  5) Sigue la guía de instalación → ahora SÍ habrá icono PWA."
echo "──────────────────────────────────────────────────────"
