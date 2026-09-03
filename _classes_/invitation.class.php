<?php

class Invitation extends DbConnection {

    const TABLE = 'CLI_INVITATIONS';
    const KARMA_COST = 1000;

    private static $tableReady = false;

    private static function isSQLite() {
        return CFG::$vars['db']['type'] === 'sqlite';
    }

    public static function isRequired() {
        return !empty(CFG::$vars['login']['invitation']['required']);
    }

    public static function ensureTable() {
        if (self::$tableReady) return;
        if (!empty($_SESSION['_inv_table_ok'])) {
            self::$tableReady = true;
            return;
        }

        if (self::isSQLite()) {
            self::sqlExec("CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                user_id_from INTEGER NOT NULL,
                user_id_to INTEGER DEFAULT NULL,
                used INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NOT NULL DEFAULT 0,
                used_at INTEGER DEFAULT NULL
            )");
            self::sqlExec("CREATE INDEX IF NOT EXISTS idx_inv_code ON " . self::TABLE . "(code)");
            self::sqlExec("CREATE INDEX IF NOT EXISTS idx_inv_user_from ON " . self::TABLE . "(user_id_from)");
        } else {
            self::sqlExec("CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(32) NOT NULL,
                user_id_from INT NOT NULL,
                user_id_to INT DEFAULT NULL,
                used TINYINT(1) NOT NULL DEFAULT 0,
                created_at INT NOT NULL DEFAULT 0,
                used_at INT DEFAULT NULL,
                UNIQUE KEY uq_code (code),
                KEY idx_user_from (user_id_from)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");
        }

        $_SESSION['_inv_table_ok'] = true;
        self::$tableReady = true;
    }

    public static function generateCode() {
        return bin2hex(random_bytes(8));
    }

    public static function create($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return ['error' => 1, 'msg' => 'Usuario no válido'];
        }

        if (!Karma::canSpend($userId, self::KARMA_COST)) {
            return ['error' => 1, 'msg' => 'Karma insuficiente. Necesitas ' . self::KARMA_COST . ' puntos.'];
        }

        if (!Karma::spendPoints($userId, self::KARMA_COST)) {
            return ['error' => 1, 'msg' => 'Error al descontar karma.'];
        }

        $code = self::generateCode();

        self::sqlQueryPrepared(
            "INSERT INTO " . self::TABLE . " (code, user_id_from, used, created_at) VALUES (?, ?, 0, ?)",
            [$code, $userId, time()]
        );

        return [
            'error' => 0,
            'code'  => $code,
            'msg'   => 'Invitación creada.',
            'score' => Karma::getUserScore($userId)
        ];
    }

    public static function validate($code) {
        if (empty($code)) return false;
        $row = self::sqlQueryPrepared(
            "SELECT id, used FROM " . self::TABLE . " WHERE code = ? LIMIT 1",
            [trim($code)]
        );
        if (!$row || empty($row[0])) return false;
        return (int)$row[0]['used'] === 0;
    }

    public static function markUsed($code, $newUserId) {
        return self::sqlQueryPrepared(
            "UPDATE " . self::TABLE . " SET used = 1, user_id_to = ?, used_at = ? WHERE code = ? AND used = 0",
            [(int)$newUserId, time(), trim($code)]
        );
    }

    public static function getByUser($userId, $limit = 50) {
        return self::sqlQueryPrepared(
            "SELECT id, code, user_id_to, used, created_at, used_at FROM " . self::TABLE . " WHERE user_id_from = ? ORDER BY created_at DESC LIMIT " . (int)$limit,
            [(int)$userId]
        ) ?: [];
    }

    public static function countAvailable($userId) {
        $row = self::sqlQueryPrepared(
            "SELECT COUNT(*) AS cnt FROM " . self::TABLE . " WHERE user_id_from = ? AND used = 0",
            [(int)$userId]
        );
        return ($row && isset($row[0]['cnt'])) ? (int)$row[0]['cnt'] : 0;
    }

    public static function countUsed($userId) {
        $row = self::sqlQueryPrepared(
            "SELECT COUNT(*) AS cnt FROM " . self::TABLE . " WHERE user_id_from = ? AND used = 1",
            [(int)$userId]
        );
        return ($row && isset($row[0]['cnt'])) ? (int)$row[0]['cnt'] : 0;
    }
}
