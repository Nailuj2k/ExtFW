<?php
/**
 * TelegramStore - Data persistence for Telegram module
 * Tables: TGRAM_CHATS, TGRAM_TOKENS, TGRAM_LOG
 * Compatible with MySQL and SQLite via DbConnection
 */
class TelegramStore extends DbConnection {

    private static function isSQLite() {
        return CFG::$vars['db']['type'] === 'sqlite';
    }

    // -------------------------------------------------------------------------
    // Table creation
    // -------------------------------------------------------------------------

    static function ensureTables() {
        $v = $_SESSION['telegram_tables_v'] ?? 0;
        if ($v >= 2) return;

        if (self::isSQLite()) {

            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_CHATS (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL DEFAULT 0,
                chat_id TEXT NOT NULL,
                username TEXT DEFAULT '',
                first_name TEXT DEFAULT '',
                last_name TEXT DEFAULT '',
                active INTEGER DEFAULT 1,
                linked_at INTEGER DEFAULT 0,
                created_at INTEGER DEFAULT 0,
                updated_at INTEGER DEFAULT 0,
                UNIQUE(chat_id)
            )");

            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_TOKENS (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT NOT NULL,
                created_at INTEGER DEFAULT 0,
                used_at INTEGER DEFAULT NULL,
                UNIQUE(token)
            )");

            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_LOG (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chat_id TEXT DEFAULT '',
                user_id INTEGER DEFAULT 0,
                direction TEXT DEFAULT 'out',
                message_text TEXT DEFAULT '',
                ok INTEGER DEFAULT 1,
                created_at INTEGER DEFAULT 0
            )");

            // ── FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────────
            // Tabla de respuestas automáticas por palabras clave.
            // El bot comprueba esta tabla ANTES de llamar a la IA.
            // Campos:
            //   keywords  — palabras clave separadas por coma (ej: "hola,buenas,hey")
            //               el bot responderá si el mensaje contiene CUALQUIERA de ellas
            //   response  — texto que enviará el bot cuando se detecte una keyword
            //   priority  — mayor número = se comprueba antes (útil para ordenar reglas)
            //   active    — 0 = desactivada sin borrarla
            // ────────────────────────────────────────────────────────────────────────
            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_TEXTS (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                keywords TEXT NOT NULL DEFAULT '',
                response TEXT NOT NULL DEFAULT '',
                priority INTEGER DEFAULT 0,
                active INTEGER DEFAULT 1,
                created_at INTEGER DEFAULT 0
            )");
            // ── END FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────

        } else {

            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_CHATS (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL DEFAULT 0,
                chat_id VARCHAR(32) NOT NULL,
                username VARCHAR(128) DEFAULT '',
                first_name VARCHAR(128) DEFAULT '',
                last_name VARCHAR(128) DEFAULT '',
                active TINYINT(1) DEFAULT 1,
                linked_at INT DEFAULT 0,
                created_at INT DEFAULT 0,
                updated_at INT DEFAULT 0,
                UNIQUE KEY uq_chat_id (chat_id),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_TOKENS (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL,
                created_at INT DEFAULT 0,
                used_at INT DEFAULT NULL,
                UNIQUE KEY uq_token (token),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_LOG (
                id INT AUTO_INCREMENT PRIMARY KEY,
                chat_id VARCHAR(32) DEFAULT '',
                user_id INT DEFAULT 0,
                direction VARCHAR(3) DEFAULT 'out',
                message_text TEXT,
                ok TINYINT(1) DEFAULT 1,
                created_at INT DEFAULT 0,
                KEY idx_chat_id (chat_id),
                KEY idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // ── FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────────
            // Ver descripción en el bloque SQLite de arriba.
            // ────────────────────────────────────────────────────────────────────────
            self::sqlExec("CREATE TABLE IF NOT EXISTS TGRAM_TEXTS (
                id INT AUTO_INCREMENT PRIMARY KEY,
                keywords VARCHAR(500) NOT NULL DEFAULT '',
                response TEXT NOT NULL,
                priority INT DEFAULT 0,
                active TINYINT(1) DEFAULT 1,
                created_at INT DEFAULT 0,
                KEY idx_priority (priority),
                KEY idx_active (active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // ── END FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────

        }

        $_SESSION['telegram_tables_v'] = 2;
    }

    // -------------------------------------------------------------------------
    // TGRAM_CHATS
    // -------------------------------------------------------------------------

    static function findChatByUserId(int $userId): ?array {
        $rows = self::sqlQueryPrepared(
            'SELECT * FROM TGRAM_CHATS WHERE user_id = ? AND active = 1 LIMIT 1',
            [$userId]
        );
        return !empty($rows[0]) ? $rows[0] : null;
    }

    static function findChatByChatId(string $chatId): ?array {
        $rows = self::sqlQueryPrepared(
            'SELECT * FROM TGRAM_CHATS WHERE chat_id = ? LIMIT 1',
            [$chatId]
        );
        return !empty($rows[0]) ? $rows[0] : null;
    }

    static function getChatIdForUser(int $userId): ?string {
        $row = self::findChatByUserId($userId);
        return $row ? (string)$row['chat_id'] : null;
    }

    static function saveChat(string $chatId, int $userId, string $username = '', string $firstName = '', string $lastName = ''): bool {
        $now = time();
        if (self::isSQLite()) {
            return (bool)self::sqlQueryPrepared(
                "INSERT INTO TGRAM_CHATS (chat_id, user_id, username, first_name, last_name, active, linked_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)
                 ON CONFLICT(chat_id) DO UPDATE SET
                   user_id=excluded.user_id, username=excluded.username,
                   first_name=excluded.first_name, last_name=excluded.last_name,
                   active=1, linked_at=excluded.linked_at, updated_at=excluded.updated_at",
                [$chatId, $userId, $username, $firstName, $lastName, $now, $now, $now]
            );
        }
        return (bool)self::sqlQueryPrepared(
            "INSERT INTO TGRAM_CHATS (chat_id, user_id, username, first_name, last_name, active, linked_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               user_id=VALUES(user_id), username=VALUES(username),
               first_name=VALUES(first_name), last_name=VALUES(last_name),
               active=1, linked_at=VALUES(linked_at), updated_at=VALUES(updated_at)",
            [$chatId, $userId, $username, $firstName, $lastName, $now, $now, $now]
        );
    }

    static function unlinkChat(int $userId): bool {
        return (bool)self::sqlQueryPrepared(
            'UPDATE TGRAM_CHATS SET active = 0, updated_at = ? WHERE user_id = ?',
            [time(), $userId]
        );
    }

    // -------------------------------------------------------------------------
    // TGRAM_TOKENS  (one-time registration tokens)
    // -------------------------------------------------------------------------

    static function createToken(int $userId): string {
        // Delete ALL existing tokens for this user so there is only ever one active token
        self::sqlQueryPrepared(
            'DELETE FROM TGRAM_TOKENS WHERE user_id = ?',
            [$userId]
        );

        $token = strtoupper(bin2hex(random_bytes(4))); // 8-char hex, easy to type
        $now   = time();
        self::sqlQueryPrepared(
            'INSERT INTO TGRAM_TOKENS (user_id, token, created_at, used_at) VALUES (?, ?, ?, NULL)',
            [$userId, $token, $now]
        );
        return $token;
    }

    /**
     * Returns user_id if token is valid and unused, null otherwise.
     * Marks the token as used.
     * On error returns an array with a 'debug' key (so callers can distinguish DB errors from invalid tokens).
     */
    static function consumeToken(string $token): mixed {
        $token = strtoupper(trim($token));

        // Look up the token without constraints first (for diagnostics)
        $debug = self::sqlQueryPrepared('SELECT * FROM TGRAM_TOKENS WHERE token = ? LIMIT 1', [$token]);

        // Build a diagnostic payload for logging and error reporting
        $diag = [
            'token'   => $token,
            'found'   => is_array($debug) && !empty($debug[0]),
            'row'     => is_array($debug) ? ($debug[0] ?? null) : null,
            'db_err'  => ($debug === false) ? 'sqlQueryPrepared returned false' : null,
            'now'     => time(),
        ];

        // Write to log file if the log dir is writable
        $logDir = defined('SCRIPT_DIR_LOG') ? SCRIPT_DIR_LOG : 'media/log';
        if (is_dir($logDir) && is_writable($logDir)) {
            file_put_contents(
                $logDir . '/' . date('Ymd_Hi') . '_tg_consume_debug.txt',
                json_encode($diag) . "\n",
                FILE_APPEND
            );
        }

        // If DB returned false (connection/table error), surface it
        if ($debug === false) {
            return ['debug' => $diag, 'error' => 'db_error'];
        }

        // Token not found at all
        if (empty($debug[0])) {
            return ['debug' => $diag, 'error' => 'not_found'];
        }

        $row = $debug[0];

        // Token already used (used_at is set and non-zero)
        if (!empty($row['used_at'])) {
            $diag['reason'] = 'already_used';
            return ['debug' => $diag, 'error' => 'already_used'];
        }

        // Token expired (older than 10 minutes)
        if ((int)$row['created_at'] < time() - 600) {
            $diag['reason'] = 'expired';
            return ['debug' => $diag, 'error' => 'expired'];
        }

        // Valid — mark as used and return user_id
        $userId = (int)$row['user_id'];
        self::sqlQueryPrepared(
            'UPDATE TGRAM_TOKENS SET used_at = ? WHERE token = ?',
            [time(), $token]
        );
        return $userId;
    }

    // -------------------------------------------------------------------------
    // TGRAM_LOG
    // -------------------------------------------------------------------------

    static function logMessage(string $chatId, int $userId, string $direction, string $text, bool $ok = true): void {
        self::sqlQueryPrepared(
            'INSERT INTO TGRAM_LOG (chat_id, user_id, direction, message_text, ok, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$chatId, $userId, $direction, $text, $ok ? 1 : 0, time()]
        );
    }

    // -------------------------------------------------------------------------
    // AI config helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the currently configured AI service name (e.g. 'ollama', 'claude').
     */
    static function getAiService(): string {
        return self::getCfgValue('modules.telegram.ai_service', 'ollama');
    }

    /**
     * Persists the AI service selection.
     */
    static function setAiService(string $service): void {
        self::setCfgValue('modules.telegram.ai_service', $service, 'AI service used by the Telegram bot');
    }

    /**
     * Returns true if the AI feature is enabled (not set to 'off').
     */
    static function isAiEnabled(): bool {
        return strtolower(self::getAiService()) !== 'off';
    }

    // -------------------------------------------------------------------------
    // Permissions — classic ACL role check (works without HTTP session)
    // -------------------------------------------------------------------------

    /**
     * Returns true if the Telegram chat is linked to a user who has the
     * 'Administradores' role in the classic ACL.
     *
     * Queries ACL_USER_ROLES + ACL_ROLES directly so it works inside the
     * webhook context (no $_SESSION needed).
     */
    static function isLinkedUserAdmin(string $chatId): bool {
        $chat = self::findChatByChatId($chatId);
        if (!$chat || !(int)($chat['user_id'] ?? 0)) return false;

        $userId = (int)$chat['user_id'];

        // Step 1: get role_id for 'Administradores'
        $roleRows = self::sqlQueryPrepared(
            'SELECT role_id FROM ' . TB_ACL_ROLES . ' WHERE role_name = ? LIMIT 1',
            ['Administradores']
        );
        if (empty($roleRows[0]['role_id'])) return false;
        $roleId = (int)$roleRows[0]['role_id'];

        // Step 2: check if user has that role
        $userRows = self::sqlQueryPrepared(
            'SELECT id_role FROM ' . TB_ACL_USER_ROLES . ' WHERE id_user = ? AND id_role = ? LIMIT 1',
            [$userId, $roleId]
        );

        return !empty($userRows);
    }

    // ── FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────────
    // Métodos de acceso a la tabla de respuestas por palabras clave.
    //
    // Para añadir una nueva feature similar:
    //   1. Crea la tabla en ensureTables() (SQLite + MySQL)
    //   2. Añade aquí los métodos de acceso a datos
    //   3. Úsalos desde telegram.class.php en el handler correspondiente
    //   4. Crea TABLE_TGRAM_*.php para el panel de admin (scaffold)
    //   5. Añade el tab en admin.php
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Busca en TGRAM_TEXTS si el mensaje contiene alguna keyword activa.
     * Las filas se comprueban en orden de prioridad descendente.
     * Devuelve el texto de respuesta de la primera coincidencia, o null si ninguna.
     *
     * Matching: case-insensitive, el mensaje debe CONTENER la keyword.
     * Múltiples keywords por fila separadas por coma (OR implícito).
     */
    static function findTextResponse(string $message): ?string {
        $rows = self::sqlQueryPrepared(
            'SELECT keywords, response FROM TGRAM_TEXTS WHERE active = 1 ORDER BY priority DESC',
            []
        );
        if (!is_array($rows) || empty($rows)) return null;

        $msgLower = mb_strtolower(trim($message));

        foreach ($rows as $row) {
            $keywords = array_map('trim', explode(',', $row['keywords']));
            foreach ($keywords as $kw) {
                if ($kw === '') continue;
                if (mb_strpos($msgLower, mb_strtolower($kw)) !== false) {
                    return $row['response'];
                }
            }
        }
        return null;
    }
    // ── END FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────

    // -------------------------------------------------------------------------
    // Debug helpers
    // -------------------------------------------------------------------------

    static function debugListTokens(): array {
        return self::sqlQueryPrepared(
            'SELECT id, user_id, token, created_at, used_at FROM TGRAM_TOKENS ORDER BY id DESC LIMIT 20',
            []
        ) ?: [];
    }

    static function debugFindToken(string $token): mixed {
        $rows = self::sqlQueryPrepared(
            'SELECT id, user_id, token, created_at, used_at FROM TGRAM_TOKENS WHERE token = ? LIMIT 1',
            [strtoupper($token)]
        );
        return $rows[0] ?? null;
    }

    // -------------------------------------------------------------------------
    // CFG helpers  (same pattern as NoxtrStore)
    // -------------------------------------------------------------------------

    static function getCfgValue(string $key, string $default = ''): string {
        // Accepts both 'modules.telegram.foo' and short 'foo' (auto-prefixed)
        if (strpos($key, '.') === false) {
            $key = 'modules.telegram.' . $key;
        }
        $rows = self::sqlQueryPrepared('SELECT V FROM CFG_CFG WHERE K = ? LIMIT 1', [$key]);
        if (!empty($rows[0]['V'])) return (string)$rows[0]['V'];
        return $default;
    }

    static function setCfgValue(string $key, string $value, string $description = '', int $active = 1): void {
        $existing = self::sqlQueryPrepared('SELECT ID FROM CFG_CFG WHERE K = ? LIMIT 1', [$key]);
        if (!empty($existing)) {
            self::sqlQueryPrepared('UPDATE CFG_CFG SET V = ?, DESCRIPTION = ? WHERE K = ?', [$value, $description, $key]);
        } else {
            self::sqlQueryPrepared('INSERT INTO CFG_CFG (K, V, DESCRIPTION, ACTIVE) VALUES (?, ?, ?, ?)', [$key, $value, $description, $active]);
        }
    }
}
