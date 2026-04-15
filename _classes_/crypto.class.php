<?php
// crypto.class.php - Criptografía EC P-256 (ECDH + ECDSA) compatible con Web Crypto API

class CryptoLib {
    private $encPrivDer;
    private $signPrivDer;
    private $curve = 'prime256v1'; // Por defecto el más compatible

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Restaurar claves privadas de sesión (solo para demo)
        $this->encPrivDer = $_SESSION['php_enc_priv_der'] ?? null;
        $this->signPrivDer = $_SESSION['php_sign_priv_der'] ?? null;
    }

    private function error(string $msg) {
        http_response_code(500);
        echo json_encode(['error' => $msg]);
        exit;
    }

    private function clearOpenSSLErrors() {
        while (openssl_error_string() !== false);
    }

    private function getErrors(): string {
        $errors = [];
        while ($e = openssl_error_string()) {
            $errors[] = $e;
        }
        return implode(' | ', $errors);
    }

    private function createECKey(): OpenSSLAsymmetricKey {
        $candidates = ['prime256v1', 'secp256r1'];
        foreach ($candidates as $curve) {
            $this->clearOpenSSLErrors();
            $config = [
                'curve_name' => $curve,
                'private_key_type' => OPENSSL_KEYTYPE_EC
            ];
            $key = @openssl_pkey_new($config);
            if ($key !== false) {
                $this->curve = $curve; // Recordamos cuál funcionó
                return $key;
            }
        }
        $this->error('No se pudo generar clave EC P-256. Errores: ' . $this->getErrors());
    }

    public function generateKeys(): array {
        $this->clearOpenSSLErrors();

        $encKey = $this->createECKey();
        $signKey = $this->createECKey();

        // Exportar privadas en formato DER
        if (!openssl_pkey_export($encKey, $encPrivPem)) {
            $this->error('Export enc private failed: ' . $this->getErrors());
        }
        $this->encPrivDer = $this->pemToDer($encPrivPem);

        if (!openssl_pkey_export($signKey, $signPrivPem)) {
            $this->error('Export sign private failed: ' . $this->getErrors());
        }
        $this->signPrivDer = $this->pemToDer($signPrivPem);

        // Guardar en sesión para descifrar/firmar después
        $_SESSION['php_enc_priv_der'] = $this->encPrivDer;
        $_SESSION['php_sign_priv_der'] = $this->signPrivDer;

        // Públicas en SPKI DER → base64 (compatible con Web Crypto)
        $encDetails = openssl_pkey_get_details($encKey);
        $signDetails = openssl_pkey_get_details($signKey);

        // $encDetails['key'] es el PEM de la pública, lo convertimos a DER
        return [
            'encPub'  => base64_encode($this->pemToDer($encDetails['key'])),
            'signPub' => base64_encode($this->pemToDer($signDetails['key']))
        ];
    }

    private function pemToDer(string $pem): string {
        $pem = preg_replace('/-----BEGIN.*?KEY-----/', '', $pem);
        $pem = preg_replace('/-----END.*?KEY-----/', '', $pem);
        $pem = str_replace(["\r", "\n", " "], '', $pem);
        return base64_decode($pem);
    }

    private function derToPem(string $der, string $type): string {
        $b64 = chunk_split(base64_encode($der), 64);
        return "-----BEGIN $type KEY-----\n$b64-----END $type KEY-----\n";
    }

    private function importPublic(string $base64): OpenSSLAsymmetricKey {
        $der = base64_decode($base64);
        $pem = $this->derToPem($der, 'PUBLIC');
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            $this->error('Import public key failed: ' . $this->getErrors());
        }
        return $key;
    }

    private function importPrivate(string $der): OpenSSLAsymmetricKey {
        $pem = $this->derToPem($der, 'PRIVATE');
        $key = openssl_pkey_get_private($pem);
        if ($key === false) {
            $this->error('Import private key failed: ' . $this->getErrors());
        }
        return $key;
    }

    public function encrypt(string $text, string $recipientPubBase64): array {
        $recipientPub = $this->importPublic($recipientPubBase64);

        // Clave efímera con la misma curva que funcionó
        $config = ['curve_name' => $this->curve, 'private_key_type' => OPENSSL_KEYTYPE_EC];
        $ephKey = openssl_pkey_new($config);
        if ($ephKey === false) {
            $this->error('Ephemeral key failed: ' . $this->getErrors());
        }

        $ephDetails = openssl_pkey_get_details($ephKey);
        // Exportar en SPKI DER (compatible con Web Crypto importKey 'spki')
        $ephPubBase64 = base64_encode($this->pemToDer($ephDetails['key']));

        // Derivar secreto compartido
        $shared = openssl_pkey_derive($recipientPub, $ephKey);
        if ($shared === false) {
            $this->error('Derive failed: ' . $this->getErrors());
        }

        // HKDF: salt vacío, info = 'AES-256-GCM key' (debe coincidir con JS)
        $aesKey = hash_hkdf('sha256', $shared, 32, 'AES-256-GCM key', '');

        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($text, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            $this->error('Encryption failed: ' . $this->getErrors());
        }

        return [
            'ephPub'      => $ephPubBase64,
            'iv'          => base64_encode($iv),
            'ciphertext'  => base64_encode($ciphertext),
            'tag'         => base64_encode($tag)
        ];
    }

    public function decrypt(array $encData): string {
        if (!$this->encPrivDer) {
            $this->error('Genere claves PHP primero');
        }

        $ephPub = $this->importPublic($encData['ephPub']);
        $myPriv = $this->importPrivate($this->encPrivDer);

        $shared = openssl_pkey_derive($ephPub, $myPriv);
        if ($shared === false) {
            $this->error('Derive decrypt failed: ' . $this->getErrors());
        }

        // HKDF: hash_hkdf(algo, ikm, length, info, salt)
        // Debe coincidir con JS: salt vacío, info = 'AES-256-GCM key'
        $aesKey = hash_hkdf('sha256', $shared, 32, 'AES-256-GCM key', '');

        $iv = base64_decode($encData['iv']);
        $ciphertext = base64_decode($encData['ciphertext']);
        $tag = base64_decode($encData['tag']);

        $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            $this->error('Decryption failed: ' . $this->getErrors());
        }

        return $decrypted;
    }

    public function sign(string $text): string {
        if (!$this->signPrivDer) {
            $this->error('Genere claves PHP primero');
        }
        $priv = $this->importPrivate($this->signPrivDer);
        openssl_sign($text, $sigDer, $priv, OPENSSL_ALGO_SHA256);
        $sigRaw = $this->derToRaw($sigDer);
        return base64_encode($sigRaw);
    }

    public function verify(string $text, string $sigBase64, string $signerPubBase64): bool {
        $pub = $this->importPublic($signerPubBase64);
        $sigRaw = base64_decode($sigBase64);
        $sigDer = $this->rawToDer($sigRaw);
        $result = openssl_verify($text, $sigDer, $pub, OPENSSL_ALGO_SHA256);
        return $result === 1;
    }

    private function derToRaw(string $der): string {
        // Parse SEQUENCE
        if ($der[0] !== "\x30") return '';
        $len = ord($der[1]);
        if (($len & 0x80) !== 0) return ''; // Long form no soportado aquí
        $content = substr($der, 2, $len);

        // Parse r
        if ($content[0] !== "\x02") return '';
        $rLen = ord($content[1]);
        $r = substr($content, 2, $rLen);
        $offset = 2 + $rLen;

        // Parse s
        if ($content[$offset] !== "\x02") return '';
        $sLen = ord($content[$offset + 1]);
        $s = substr($content, $offset + 2, $sLen);

        // DER añade 0x00 cuando el bit alto está activo - hay que quitarlo
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        
        // Luego padear a exactamente 32 bytes cada uno
        $r = str_pad($r, 32, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    private function rawToDer(string $raw): string {
        if (strlen($raw) !== 64) return '';
        $r = ltrim(substr($raw, 0, 32), "\x00");
        $s = ltrim(substr($raw, 32), "\x00");
        if (ord($r[0] ?? '') & 0x80) $r = "\x00" . $r;
        if (ord($s[0] ?? '') & 0x80) $s = "\x00" . $s;
        $body = "\x02" . chr(strlen($r)) . $r . "\x02" . chr(strlen($s)) . $s;
        return "\x30" . chr(strlen($body)) . $body;
    }

    // ========== FILE DECRYPTION ==========
    
    public function decryptFile(array $encData): string {
        if (!$this->encPrivDer) {
            $this->error('Genere claves PHP primero para descifrar archivos');
        }

        $ephPub = $this->importPublic($encData['ephPub']);
        $myPriv = $this->importPrivate($this->encPrivDer);

        $shared = openssl_pkey_derive($ephPub, $myPriv);
        if ($shared === false) {
            $this->error('Derive file decrypt failed: ' . $this->getErrors());
        }

        // HKDF igual que en decrypt()
        $aesKey = hash_hkdf('sha256', $shared, 32, 'AES-256-GCM key', '');

        $iv = base64_decode($encData['iv']);
        $ciphertext = base64_decode($encData['ciphertext']);
        $tag = base64_decode($encData['tag']);

        $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            $this->error('File decryption failed: ' . $this->getErrors());
        }

        return $decrypted;
    }
}

