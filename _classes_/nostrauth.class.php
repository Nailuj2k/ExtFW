<?php
/**
 * NostrAuth - Autenticación con Nostr (NIP-07)
 * Verificación de firmas Schnorr (BIP-340) sobre secp256k1
 * Requiere extensión PHP GMP
 */

class NostrAuth extends DbConnection {

    // Parámetros de la curva secp256k1
    private static $p;
    private static $n;
    private static $Gx;
    private static $Gy;

    private static function init() {
        if (self::$p === null) {
            self::$p  = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16);
            self::$n  = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
            self::$Gx = gmp_init('79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', 16);
            self::$Gy = gmp_init('483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 16);
        }
    }

    /**
     * Genera un challenge para login
     */
    public static function generateChallenge(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Valida un evento Nostr firmado
     */
    public static function verifyEvent($event, ?string $expectedChallenge = null): array {
        if (is_array($event)) {
            $event = (object) $event;
        }

        // Verificar estructura
        if (!isset($event->id, $event->pubkey, $event->created_at, $event->kind, $event->tags, $event->content, $event->sig)) {
            return ['valid' => false, 'error' => 'invalid_structure', 'pubkey' => null];
        }

        // Verificar ID = hash del evento
        $serialized = json_encode([
            0,
            $event->pubkey,
            $event->created_at,
            $event->kind,
            $event->tags,
            $event->content
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        if (hash('sha256', $serialized) !== $event->id) {
            return ['valid' => false, 'error' => 'invalid_id', 'pubkey' => null];
        }

        // Verificar challenge
        if ($expectedChallenge !== null) {
            $found = false;
            foreach ($event->tags as $tag) {
                if ($tag[0] === 'challenge' && $tag[1] === $expectedChallenge) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return ['valid' => false, 'error' => 'challenge_mismatch', 'pubkey' => null];
            }
        }

        // Verificar timestamp (10 min de tolerancia para diferencias de reloj)
        $timeDiff = abs(time() - $event->created_at);
        if ($timeDiff > 600) {
            error_log("Nostr auth expired: server=" . time() . " event=" . $event->created_at . " diff=" . $timeDiff);
            return ['valid' => false, 'error' => 'expired', 'pubkey' => null];
        }

        // Verificar firma Schnorr
        if (!self::verifySchnorr($event->sig, $event->id, $event->pubkey)) {
            return ['valid' => false, 'error' => 'invalid_signature', 'pubkey' => null];
        }

        return ['valid' => true, 'pubkey' => $event->pubkey, 'error' => null];
    }

    /**
     * Verificación Schnorr BIP-340
     */
    public static function verifySchnorr(string $sig, string $msg, string $pubkey): bool {
        if (!extension_loaded('gmp')) {
            error_log('NostrAuth: GMP required');
            return false;
        }

        self::init();

        if (strlen($sig) !== 128 || strlen($msg) !== 64 || strlen($pubkey) !== 64) {
            return false;
        }

        try {
            $r = gmp_init(substr($sig, 0, 64), 16);
            $s = gmp_init(substr($sig, 64, 64), 16);
            $px = gmp_init($pubkey, 16);

            if (gmp_cmp($r, self::$p) >= 0 || gmp_cmp($s, self::$n) >= 0) {
                return false;
            }

            $P = self::liftX($px);
            if ($P === null) return false;

            // e = tagged_hash("BIP0340/challenge", r || P || m) mod n
            $rBytes = str_pad(hex2bin(gmp_strval($r, 16)), 32, "\x00", STR_PAD_LEFT);
            $pBytes = str_pad(hex2bin($pubkey), 32, "\x00", STR_PAD_LEFT);
            $mBytes = str_pad(hex2bin($msg), 32, "\x00", STR_PAD_LEFT);
            
            $e = self::taggedHash('BIP0340/challenge', $rBytes . $pBytes . $mBytes);
            $e = gmp_mod(gmp_init(bin2hex($e), 16), self::$n);

            // R = s*G - e*P
            $sG = self::pointMul([self::$Gx, self::$Gy], $s);
            $eP = self::pointMul($P, $e);
            $ePneg = [$eP[0], gmp_mod(gmp_neg($eP[1]), self::$p)];
            $R = self::pointAdd($sG, $ePneg);

            if ($R === null) return false;

            // R.y par y R.x == r
            return !gmp_testbit($R[1], 0) && gmp_cmp($R[0], $r) === 0;

        } catch (Exception $e) {
            error_log('NostrAuth error: ' . $e->getMessage());
            return false;
        }
    }

    private static function taggedHash(string $tag, string $data): string {
        $tagHash = hash('sha256', $tag, true);
        return hash('sha256', $tagHash . $tagHash . $data, true);
    }

    private static function liftX($x): ?array {
        $x3 = gmp_powm($x, 3, self::$p);
        $y2 = gmp_mod(gmp_add($x3, 7), self::$p);
        $exp = gmp_div(gmp_add(self::$p, 1), 4);
        $y = gmp_powm($y2, $exp, self::$p);
        
        if (gmp_cmp(gmp_powm($y, 2, self::$p), $y2) !== 0) return null;
        if (gmp_testbit($y, 0)) $y = gmp_sub(self::$p, $y);
        
        return [$x, $y];
    }

    private static function pointAdd(?array $P1, ?array $P2): ?array {
        if ($P1 === null) return $P2;
        if ($P2 === null) return $P1;

        list($x1, $y1) = $P1;
        list($x2, $y2) = $P2;

        if (gmp_cmp($x1, $x2) === 0) {
            if (gmp_cmp($y1, $y2) !== 0) return null;
            $num = gmp_mod(gmp_mul(3, gmp_pow($x1, 2)), self::$p);
            $den = gmp_mod(gmp_mul(2, $y1), self::$p);
        } else {
            $num = gmp_mod(gmp_sub($y2, $y1), self::$p);
            $den = gmp_mod(gmp_sub($x2, $x1), self::$p);
        }

        $lambda = gmp_mod(gmp_mul($num, gmp_invert($den, self::$p)), self::$p);
        $x3 = gmp_mod(gmp_sub(gmp_sub(gmp_pow($lambda, 2), $x1), $x2), self::$p);
        $y3 = gmp_mod(gmp_sub(gmp_mul($lambda, gmp_sub($x1, $x3)), $y1), self::$p);

        return [$x3, $y3];
    }

    private static function pointMul(array $P, $k): ?array {
        $result = null;
        $addend = $P;

        while (gmp_cmp($k, 0) > 0) {
            if (gmp_testbit($k, 0)) {
                $result = self::pointAdd($result, $addend);
            }
            $addend = self::pointAdd($addend, $addend);
            $k = gmp_div($k, 2);
        }

        return $result;
    }

    /**
     * Hex a npub (bech32)
     */
    public static function hexToNpub(string $hex): string {
        return self::bech32Encode('npub', self::convertBits(hex2bin($hex), 8, 5));
    }

    /**
     * npub a hex
     */
    public static function npubToHex(string $npub): ?string {
        $decoded = self::bech32Decode($npub);
        if (!$decoded || $decoded[0] !== 'npub') return null;
        return bin2hex(pack('C*', ...self::convertBits($decoded[1], 5, 8, false)));
    }

    private static function bech32Encode(string $hrp, array $data): string {
        $charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
        $checksum = self::bech32Checksum($hrp, $data);
        $result = $hrp . '1';
        foreach (array_merge($data, $checksum) as $i) {
            $result .= $charset[$i];
        }
        return $result;
    }

    private static function bech32Decode(string $str): ?array {
        $charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
        $str = strtolower($str);
        $pos = strrpos($str, '1');
        if ($pos === false || $pos < 1) return null;
        
        $hrp = substr($str, 0, $pos);
        $data = [];
        for ($i = $pos + 1; $i < strlen($str); $i++) {
            $d = strpos($charset, $str[$i]);
            if ($d === false) return null;
            $data[] = $d;
        }
        return [$hrp, array_slice($data, 0, -6)];
    }

    private static function bech32Checksum(string $hrp, array $data): array {
        $values = array_merge(self::hrpExpand($hrp), $data, [0,0,0,0,0,0]);
        $polymod = self::bech32Polymod($values) ^ 1;
        $result = [];
        for ($i = 0; $i < 6; $i++) {
            $result[] = ($polymod >> (5 * (5 - $i))) & 31;
        }
        return $result;
    }

    private static function hrpExpand(string $hrp): array {
        $result = [];
        for ($i = 0; $i < strlen($hrp); $i++) {
            $result[] = ord($hrp[$i]) >> 5;
        }
        $result[] = 0;
        for ($i = 0; $i < strlen($hrp); $i++) {
            $result[] = ord($hrp[$i]) & 31;
        }
        return $result;
    }

    private static function bech32Polymod(array $values): int {
        $gen = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        $chk = 1;
        foreach ($values as $v) {
            $top = $chk >> 25;
            $chk = (($chk & 0x1ffffff) << 5) ^ $v;
            for ($i = 0; $i < 5; $i++) {
                if (($top >> $i) & 1) $chk ^= $gen[$i];
            }
        }
        return $chk;
    }

    private static function convertBits($data, int $from, int $to, bool $pad = true): array {
        $acc = 0;
        $bits = 0;
        $result = [];
        $maxv = (1 << $to) - 1;
        
        if (is_string($data)) $data = array_values(unpack('C*', $data));
        
        foreach ($data as $value) {
            $acc = ($acc << $from) | $value;
            $bits += $from;
            while ($bits >= $to) {
                $bits -= $to;
                $result[] = ($acc >> $bits) & $maxv;
            }
        }
        
        if ($pad && $bits > 0) {
            $result[] = ($acc << ($to - $bits)) & $maxv;
        }
        
        return $result;
    }

    /**
     * Busca usuario por pubkey
     */
    public static function findUserByPubkey(string $pubkey): ?array {
        if (strpos($pubkey, 'npub') === 0) {
            $pubkey = self::npubToHex($pubkey);
        }
        
        $sql = "SELECT user_id, username, user_fullname, user_email, user_level, nostr_pubkey, user_url_avatar
                FROM " . TB_USER . "
                WHERE nostr_pubkey = ?
                LIMIT 1";
        
        $result = self::sqlQueryPrepared($sql, [$pubkey]);
        return $result[0] ?? null;
    }

    /**
     * Crea o actualiza usuario Nostr
     */
    public static function createOrUpdateUser(string $pubkey, string $customUsername = ''): ?array {
        $existing = self::findUserByPubkey($pubkey);

        if ($existing) {
            Login::updateLastLogin($existing['user_id']);
            return [
                'user_id' => $existing['user_id'],
                'is_new' => false,
                'user' => $existing
            ];
        }

        // Crear usuario nuevo
        $npub = self::hexToNpub($pubkey);

        // Username: personalizado si lo proporcionó el usuario, o autogenerado
        if (!empty($customUsername)) {
            $username = preg_replace('/[^a-z0-9_]/', '', strtolower($customUsername));
            if (strlen($username) < 5) {
                $username = 'n_' . substr(hash('sha256', $pubkey), 0, 8);
            }
        } else {
            $username = 'n_' . substr(hash('sha256', $pubkey), 0, 8);
        }

        // Fullname: username personalizado o npub truncado
        $fullname = !empty($customUsername) ? $username : substr($npub, 0, 12) . '...' . substr($npub, -6);
        // Email con dominio del sitio (sirve como LN address)
        $domain = $_SERVER['HTTP_HOST'] ?? 'nostr.local';
        $email = $username . '@' . $domain;
        
        $ahora = time();
        $ip = get_ip();
        
        $sql = "INSERT INTO " . TB_USER . " 
                (username, user_email, user_fullname, user_level, user_active, user_verify, nostr_pubkey, user_ip, user_last_login, user_date_created)
                VALUES (?, ?, ?, 100, 1, 1, ?, ?, ?, ?)";
        
        self::sqlQueryPrepared($sql, [$username, $email, $fullname, $pubkey, $ip, $ahora, $ahora]);
        
        $result = self::sqlQueryPrepared(
            "SELECT user_id, username, user_fullname, user_email, user_level,user_url_avatar,user_score FROM " . TB_USER . " WHERE nostr_pubkey = ? ORDER BY user_id DESC LIMIT 1", 
            [$pubkey]
        );
        
        $user = $result[0] ?? null;
        
        if (!$user) {
            return null;
        }
        
        return [
            'user_id' => $user['user_id'],
            'is_new' => true,
            'user' => $user
        ];
    }

    /**
     * Vincula npub a usuario existente
     */
    public static function linkPubkeyToUser(int $userId, string $pubkey): bool {
        if (strpos($pubkey, 'npub') === 0) {
            $pubkey = self::npubToHex($pubkey);
        }
        
        // Verificar que no esté ya vinculada a otro usuario
        $existing = self::findUserByPubkey($pubkey);
        if ($existing && $existing['user_id'] != $userId) {
            return false; // Ya vinculada a otro
        }
        
        $sql = "UPDATE " . TB_USER . " SET nostr_pubkey = ? WHERE user_id = ?";
        return self::sqlQueryPrepared($sql, [$pubkey, $userId]) !== false;
    }
}
