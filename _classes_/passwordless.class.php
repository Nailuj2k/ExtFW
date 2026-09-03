<?php
/**
 * PasswordlessAuth - Autenticación sin contraseña usando firma digital ECDSA
 *
 * Flujo:
 * 1. Usuario solicita login con su email/username
 * 2. Servidor genera challenge aleatorio y lo guarda en BD
 * 3. Usuario firma el challenge con su clave privada (en navegador)
 * 4. Servidor verifica la firma con la clave pública del usuario
 * 5. Si es válida, crea la sesión
 */

// Definir constante para la tabla de claves (multi-dispositivo)
if (!defined('TB_USER_KEYS')) {
    define('TB_USER_KEYS', 'CLI_USER_KEYS');
}

class PasswordlessAuth extends DbConnection {
    
    private $challengeExpiry = 300; // 5 minutos
    
    /**
     * Buscar usuario por email o username
     * Devuelve datos del usuario (sin claves, ya que están en CLI_USER_KEYS)
     */
    public function findUser(string $identifier): ?array {
        $sql = "SELECT user_id, username, user_fullname, user_email, user_level, user_url_avatar, user_score
                FROM " . TB_USER . "
                WHERE (user_email = ? OR username = ?)
                AND user_active = 1
                LIMIT 1";

        $result = self::sqlQueryPrepared($sql, [$identifier, $identifier]);

        return $result[0] ?? null;
    }

    /**
     * Obtener todas las claves activas de un usuario
     * @return array Array de dispositivos con sus claves públicas
     */
    public function getUserKeys(int $userId): array {
        $sql = "SELECT id, device_id, sign_public_key, enc_public_key, device_name, user_agent, last_used_at
                FROM " . TB_USER_KEYS . "
                WHERE id_user = ?
                AND ACTIVE = 1
                ORDER BY last_used_at DESC";

        return self::sqlQueryPrepared($sql, [$userId]) ?: [];
    }

    /**
     * Obtener clave pública de un dispositivo específico
     */
    public function getDeviceKey(int $userId, string $deviceId): ?array {
        $sql = "SELECT id, device_id, sign_public_key, enc_public_key, device_name
                FROM " . TB_USER_KEYS . "
                WHERE id_user = ?
                AND device_id = ?
                AND ACTIVE = 1
                LIMIT 1";

        $result = self::sqlQueryPrepared($sql, [$userId, $deviceId]);
        return $result[0] ?? null;
    }
    
    /**
     * Generar challenge para un usuario
     */
    public function generateChallenge(int $userId, string $ip = ''): array {
        // Limpiar challenges expirados
        $sql = "DELETE FROM CLI_AUTH_CHALLENGES 
                WHERE user_id = ? OR expires_at < ?";
        self::sqlQueryPrepared($sql, [$userId, time()]);
        
        // Generar challenge único
        $challenge = $this->generateUUID() . '_' . bin2hex(random_bytes(16));
        $expiresAt = time() + $this->challengeExpiry;
        
        // Guardar en BD
        $sql = "INSERT INTO CLI_AUTH_CHALLENGES
                (user_id, challenge, ip_address, expires_at, created_at, used)
                VALUES (?, ?, ?, ?, ?, 0)";
        self::sqlQueryPrepared($sql, [$userId, $challenge, $ip, $expiresAt, time()]);
        
        return [
            'challenge' => $challenge,
            'expires_in' => $this->challengeExpiry
        ];
    }
    
    /**
     * Verificar challenge y firma
     */
    public function verifyChallenge(string $challenge, string $signatureB64, string $publicKeyB64, string $ip = ''): array {
        // Buscar challenge válido
        $sql = "SELECT id, user_id, used
                FROM CLI_AUTH_CHALLENGES
                WHERE challenge = ?
                AND expires_at > ?
                AND (used = 0 OR used IS NULL)
                LIMIT 1";
        $_time = time();
        $result = self::sqlQueryPrepared($sql, [$challenge, $_time]);

        // Debug logs (descomentarlos si es necesario):
        // LOG::$messages[__FILE__.':'.__LINE__] = "Challenge query - Result: ".print_r($result, true);
        // LOG::$messages[__FILE__.':'.__LINE__] = "Challenge query - Count: ".(is_array($result) ? count($result) : 'N/A');

        $row = $result[0] ?? null;

        if ($row == null || !isset($row['id']) || !isset($row['user_id'])) {
            return ['valid' => false, 'error' => 'challenge_invalid'];
        }

        // Marcar como usado inmediatamente (anti-replay)
        $sql = "UPDATE CLI_AUTH_CHALLENGES SET used = 1 WHERE id = ?";
        self::sqlQueryPrepared($sql, [$row['id']]);

        // Verificar firma
        $valid = $this->verifySignature($challenge, $signatureB64, $publicKeyB64);

        // Debug log (descomentar si es necesario):
        // LOG::$messages[__FILE__.':'.__LINE__] = "Signature valid: ".($valid ? 'YES' : 'NO');

        if (!$valid) {
            return [
                'valid' => false,
                'error' => 'signature_invalid',
                'msg' => 'Las claves no coinciden. Si has iniciado sesión desde otro dispositivo, necesitas eliminar y regenerar tus claves.'
            ];
        }
        
        return ['valid' => true, 'user_id' => $row['user_id']];
    }
    
    /**
     * Verificar firma ECDSA
     * La firma viene en formato raw (64 bytes: r+s) desde Web Crypto
     */
    public function verifySignature(string $data, string $signatureB64, string $publicKeyB64): bool {
        $signature = base64_decode($signatureB64);
        $publicKeyDer = base64_decode($publicKeyB64);

        // DEBUG LOGS
        error_log("=== verifySignature DEBUG ===");
        error_log("data (challenge): " . substr($data, 0, 50) . "...");
        error_log("signatureB64 length: " . strlen($signatureB64));
        error_log("signature raw length: " . strlen($signature));
        error_log("publicKeyB64 (first 50 chars): " . substr($publicKeyB64, 0, 50));
        error_log("publicKeyDer length: " . strlen($publicKeyDer));

        // Convertir clave pública DER a PEM
        $publicKeyPem = $this->derToPem($publicKeyDer, 'PUBLIC');
        error_log("publicKeyPem:\n" . $publicKeyPem);

        $pubKey = openssl_pkey_get_public($publicKeyPem);

        if ($pubKey === false) {
            error_log('PasswordlessAuth: Error importando clave pública: ' . openssl_error_string());
            return false;
        }

        // Web Crypto genera firma en formato raw (r+s concatenados, 64 bytes)
        // OpenSSL espera formato DER, hay que convertir
        $originalSignatureLen = strlen($signature);
        if (strlen($signature) === 64) {
            $signature = $this->rawToDer($signature);
            error_log("Signature converted from raw (64 bytes) to DER (" . strlen($signature) . " bytes)");
        } else {
            error_log("Signature NOT converted, length: " . $originalSignatureLen);
        }

        $result = openssl_verify($data, $signature, $pubKey, OPENSSL_ALGO_SHA256);
        error_log("openssl_verify result: " . $result . " (1=valid, 0=invalid, -1=error)");
        if ($result === -1) {
            error_log("OpenSSL error: " . openssl_error_string());
        }
        error_log("=== END verifySignature ===");

        return $result === 1;
    }
    
    /**
     * Registrar nuevo dispositivo con sus claves públicas
     * Si el dispositivo ya existe (mismo device_id), lo reactiva y actualiza las claves
     * @param string $deviceId UUID único del dispositivo
     * @param string $deviceName Nombre del dispositivo (ej: "Chrome en MacBook")
     * @param string $userAgent User agent del navegador
     */
    public function addUserKeys(int $userId, string $deviceId, string $signPubKey, string $encPubKey, string $deviceName = '', string $userAgent = ''): int {
        // Usar timestamp Unix (compatible con INT en ambas BD)
        $now = time();

        // DEBUG
        error_log("PasswordlessAuth::addUserKeys - START");
        error_log("PasswordlessAuth::addUserKeys - userId: $userId, deviceId: $deviceId, now: $now");

        // Verificar si ya existe una fila con este device_id para este usuario
        $sql = "SELECT id FROM " . TB_USER_KEYS . "
                WHERE id_user = ? AND device_id = ?
                LIMIT 1";
        $existing = self::sqlQueryPrepared($sql, [$userId, $deviceId]);

        error_log("PasswordlessAuth::addUserKeys - existing check result: " . var_export($existing, true));

        if (!empty($existing)) {
            // Ya existe: actualizar (reactivar) la fila existente
            // Nota: El sistema devuelve nombres de columnas en MAYÚSCULAS
            $keyId = (int)($existing[0]['ID'] ?? $existing[0]['id'] ?? 0);
            error_log("PasswordlessAuth::addUserKeys - UPDATE existing device, keyId: $keyId");

            $sql = "UPDATE " . TB_USER_KEYS . "
                    SET sign_public_key = ?,
                        enc_public_key = ?,
                        device_name = ?,
                        user_agent = ?,
                        last_used_at = ?,
                        ACTIVE = 1
                    WHERE id = ?";

            $result = self::sqlQueryPrepared($sql, [$signPubKey, $encPubKey, $deviceName, $userAgent, $now, $keyId]);

            error_log("PasswordlessAuth::addUserKeys - UPDATE result: " . var_export($result, true));

            // Si el UPDATE fue exitoso, devolver el ID de la fila actualizada
            if ($result !== false) {
                return $keyId;
            }
            error_log("PasswordlessAuth::addUserKeys - UPDATE failed, returning 0");
            return 0;
        }

        // No existe: insertar nueva fila
        error_log("PasswordlessAuth::addUserKeys - INSERT new device");
        $sql = "INSERT INTO " . TB_USER_KEYS . "
                (id_user, device_id, device_name, sign_public_key, enc_public_key, user_agent, last_used_at, ACTIVE)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";

        $result = self::sqlQueryPrepared($sql, [$userId, $deviceId, $deviceName, $signPubKey, $encPubKey, $userAgent, $now]);

        error_log("PasswordlessAuth::addUserKeys - INSERT result: " . var_export($result, true));

        if ($result !== false) {
            $lastId = self::lastInsertId();
            error_log("PasswordlessAuth::addUserKeys - lastInsertId: " . var_export($lastId, true));
            return $lastId;
        }

        error_log("PasswordlessAuth::addUserKeys - INSERT failed, returning 0");
        return 0;
    }

    /**
     * Actualizar timestamp de último uso de un dispositivo
     */
    public function updateLastUsed(int $keyId): bool {
        // Usar timestamp Unix (compatible con INT en ambas BD)
        $now = time();
        $sql = "UPDATE " . TB_USER_KEYS . "
                SET last_used_at = ?
                WHERE id = ?";

        return self::sqlQueryPrepared($sql, [$now, $keyId]) !== false;
    }

    /**
     * Revocar (desactivar) un dispositivo por ID
     */
    public function revokeDevice(int $keyId, int $userId): bool {
        $sql = "UPDATE " . TB_USER_KEYS . "
                SET ACTIVE = 0
                WHERE id = ?
                AND id_user = ?";

        return self::sqlQueryPrepared($sql, [$keyId, $userId]) !== false;
    }

    /**
     * Revocar (desactivar) un dispositivo por device_id
     * Desactiva el dispositivo y borra las claves públicas por seguridad
     */
    public function revokeDeviceByUUID(string $deviceId, int $userId): bool {
        $sql = "UPDATE " . TB_USER_KEYS . "
                SET ACTIVE = 0,
                    sign_public_key = '',
                    enc_public_key = ''
                WHERE device_id = ?
                AND id_user = ?";

        return self::sqlQueryPrepared($sql, [$deviceId, $userId]) !== false;
    }

    /**
     * Verificar si usuario tiene claves configuradas
     */
    public function userHasKeys(int $userId): bool {
        $sql = "SELECT id
                FROM " . TB_USER_KEYS . "
                WHERE id_user = ?
                AND ACTIVE = 1
                LIMIT 1";
        $result = self::sqlQueryPrepared($sql, [$userId]);

        return !empty($result);
    }
    
    /**
     * Generar UUID v4
     */
    private function generateUUID(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Convertir DER a PEM
     */
    private function derToPem(string $der, string $type): string {
        $b64 = chunk_split(base64_encode($der), 64);
        return "-----BEGIN $type KEY-----\n$b64-----END $type KEY-----\n";
    }
    
    /**
     * Convertir firma raw (r+s) a formato DER para OpenSSL
     */
    private function rawToDer(string $raw): string {
        if (strlen($raw) !== 64) {
            return $raw;
        }
        
        $r = substr($raw, 0, 32);
        $s = substr($raw, 32, 32);
        
        $r = ltrim($r, "\x00") ?: "\x00";
        $s = ltrim($s, "\x00") ?: "\x00";
        
        if (ord($r[0]) & 0x80) $r = "\x00" . $r;
        if (ord($s[0]) & 0x80) $s = "\x00" . $s;
        
        $rDer = "\x02" . chr(strlen($r)) . $r;
        $sDer = "\x02" . chr(strlen($s)) . $s;
        $seq = $rDer . $sDer;
        
        return "\x30" . chr(strlen($seq)) . $seq;
    }
}
