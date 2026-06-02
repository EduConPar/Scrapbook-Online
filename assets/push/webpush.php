<?php
/* ──────────────────────────────────────────────────────────────────────
   WEB PUSH (RFC 8291 + RFC 8188 aes128gcm) — implementación PHP-pura.
   ──────────────────────────────────────────────────────────────────────
   Sin composer. Usa openssl_pkey_*, openssl_pkey_derive (PHP 7.3+),
   hash_hkdf (PHP 7.1+), openssl_encrypt('aes-128-gcm') (PHP 7.1+).

   API pública:
     $wp = new WebPush();
     $wp->send($subscription, $payload, $ttl = 60);
       $subscription = ['endpoint'=>'...', 'p256dh'=>'b64url', 'auth'=>'b64url']
       $payload      = string (JSON o lo que sea, <=4078 bytes)
       Devuelve [statusCode, responseBody]. 410 → la subscripción ya no vale.

   El constructor carga vapid-keys.php. Si no existe lanza Exception →
   ejecuta primero generate-vapid.php.
   ────────────────────────────────────────────────────────────────────── */

class WebPush {
    private $publicB64;
    private $privatePem;
    private $publicBin;   /* 65 bytes uncompressed */
    private $subject;

    public function __construct() {
        $f = __DIR__ . '/vapid-keys.php';
        if (!file_exists($f)) {
            throw new Exception('VAPID keys missing — corre generate-vapid.php');
        }
        $k = require $f;
        $this->publicB64  = $k['public_b64url'];
        $this->privatePem = $k['private_pem'];
        $this->publicBin  = self::b64urlDecode($this->publicB64);
        $this->subject    = $k['subject'] ?? 'mailto:admin@example.com';
    }

    public function getPublicKeyB64() { return $this->publicB64; }

    /* ─── Envío de notificación ───────────────────────────────────── */

    public function send(array $sub, string $payload, int $ttl = 60): array {
        $endpoint = $sub['endpoint'] ?? '';
        $p256dhB  = $sub['p256dh']   ?? '';
        $authB    = $sub['auth']     ?? '';
        if (!$endpoint || !$p256dhB || !$authB) {
            throw new Exception('Subscription incompleta');
        }
        $uaPubBin = self::b64urlDecode($p256dhB);
        $authBin  = self::b64urlDecode($authB);
        if (strlen($uaPubBin) !== 65 || strlen($authBin) !== 16) {
            throw new Exception('p256dh/auth con longitud inesperada');
        }

        /* Encrypted record body. */
        list($body, $asPubBin) = $this->encryptPayload($payload, $uaPubBin, $authBin);

        /* JWT VAPID. */
        $jwt = $this->buildVapidJwt($endpoint);

        $headers = [
            'Content-Type: application/octet-stream',
            'Content-Encoding: aes128gcm',
            'TTL: ' . $ttl,
            'Authorization: vapid t=' . $jwt . ', k=' . $this->publicB64,
            'Content-Length: ' . strlen($body),
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return [0, 'curl: ' . $err];
        }
        curl_close($ch);
        return [$code, (string)$resp];
    }

    /* ─── Cifrado payload (RFC 8291 + RFC 8188 aes128gcm) ─────────── */

    private function encryptPayload(string $plaintext, string $uaPub, string $auth): array {
        /* 1) Ephemeral keypair del servidor (uno por push). */
        $ephemeral = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if (!$ephemeral) throw new Exception('No se pudo generar keypair ephemeral');
        $details = openssl_pkey_get_details($ephemeral);
        $ec = $details['ec'];
        $asPubBin = "\x04" . str_pad($ec['x'], 32, "\x00", STR_PAD_LEFT)
                          . str_pad($ec['y'], 32, "\x00", STR_PAD_LEFT);

        /* 2) ECDH(ephemeral_priv, ua_pub) — necesitamos PEM del peer. */
        $peerPem = self::ecPubBinToPem($uaPub);
        $ecdhSecret = openssl_pkey_derive($peerPem, $ephemeral);
        if (!$ecdhSecret) throw new Exception('openssl_pkey_derive falló');

        /* 3) IKM = HKDF(auth_secret, ecdh_secret, key_info, 32). */
        $keyInfo = "WebPush: info\x00" . $uaPub . $asPubBin;
        $ikm     = hash_hkdf('sha256', $ecdhSecret, 32, $keyInfo, $auth);

        /* 4) Salt aleatoria (16 bytes). */
        $salt = random_bytes(16);

        /* 5) CEK (16 bytes) y NONCE (12 bytes) — RFC 8188. */
        $cek   = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00",     $salt);

        /* 6) Plaintext + delimiter 0x02 (RFC 8188 single-record). */
        $plainWithDelim = $plaintext . "\x02";

        /* 7) AES-128-GCM. La auth tag (16 bytes) se concatena al final. */
        $tag = '';
        $cipher = openssl_encrypt(
            $plainWithDelim, 'aes-128-gcm', $cek,
            OPENSSL_RAW_DATA, $nonce, $tag, '', 16
        );
        if ($cipher === false) throw new Exception('AES-128-GCM falló');

        /* 8) Header: salt(16) || rs(4 BE) || idlen(1) || keyid(idlen=65). */
        $rs     = 4096;
        $header = $salt . pack('N', $rs) . chr(65) . $asPubBin;
        $body   = $header . $cipher . $tag;

        return [$body, $asPubBin];
    }

    /* ─── VAPID JWT (RFC 8292) ────────────────────────────────────── */

    private function buildVapidJwt(string $endpoint): string {
        $parts = parse_url($endpoint);
        $aud = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) $aud .= ':' . $parts['port'];

        $headerJson  = json_encode(['typ' => 'JWT', 'alg' => 'ES256']);
        $payloadJson = json_encode([
            'aud' => $aud,
            'exp' => time() + 12 * 3600,   /* 12h vida del JWT */
            'sub' => $this->subject,
        ]);
        $headerB  = self::b64urlEncode($headerJson);
        $payloadB = self::b64urlEncode($payloadJson);
        $signingInput = $headerB . '.' . $payloadB;

        $derSig = '';
        if (!openssl_sign($signingInput, $derSig, $this->privatePem, OPENSSL_ALGO_SHA256)) {
            throw new Exception('JWT sign falló');
        }
        /* DER → R||S de 64 bytes. */
        $rawSig = self::derToRawEcdsa($derSig);
        return $signingInput . '.' . self::b64urlEncode($rawSig);
    }

    /* ─── Helpers ─────────────────────────────────────────────────── */

    public static function b64urlEncode(string $bin): string {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }
    public static function b64urlDecode(string $s): string {
        $s = strtr($s, '-_', '+/');
        $pad = strlen($s) % 4;
        if ($pad) $s .= str_repeat('=', 4 - $pad);
        return base64_decode($s);
    }

    /* Construye un PEM SubjectPublicKeyInfo para una clave P-256 uncompressed.
       Necesario porque openssl_pkey_derive solo acepta keys con resource/PEM. */
    private static function ecPubBinToPem(string $uncompressed65): string {
        /* SPKI prefix para id-ecPublicKey + prime256v1 + 0x03 0x42 0x00 + key. */
        $prefix = hex2bin(
            '3059' .       /* SEQUENCE 89 bytes */
            '3013' .       /* SEQUENCE 19 bytes — AlgorithmIdentifier */
            '0607' . '2a8648ce3d0201' .  /* OID 1.2.840.10045.2.1 (id-ecPublicKey) */
            '0608' . '2a8648ce3d030107' .  /* OID 1.2.840.10045.3.1.7 (prime256v1) */
            '0342' . '00'  /* BIT STRING 66 bytes (1 unused + 65 key) */
        );
        $der = $prefix . $uncompressed65;
        return "-----BEGIN PUBLIC KEY-----\n" .
               chunk_split(base64_encode($der), 64, "\n") .
               "-----END PUBLIC KEY-----\n";
    }

    /* DER ECDSA-Sig-Value (SEQUENCE of two INTEGERs) → 32+32 raw. */
    private static function derToRawEcdsa(string $der): string {
        $off = 0;
        if (ord($der[$off++]) !== 0x30) throw new Exception('DER: no SEQUENCE');
        $seqLen = ord($der[$off++]);
        if ($seqLen & 0x80) {
            $n = $seqLen & 0x7F;
            $off += $n;  /* long-form length, lo saltamos */
        }
        $r = self::derReadInt($der, $off);
        $s = self::derReadInt($der, $off);
        return str_pad($r, 32, "\x00", STR_PAD_LEFT) . str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }
    private static function derReadInt(string $der, int &$off): string {
        if (ord($der[$off++]) !== 0x02) throw new Exception('DER: no INTEGER');
        $len = ord($der[$off++]);
        $val = substr($der, $off, $len);
        $off += $len;
        /* Strip leading 0x00 si lo metió por signedness. */
        if (strlen($val) === 33 && $val[0] === "\x00") $val = substr($val, 1);
        return $val;
    }
}
