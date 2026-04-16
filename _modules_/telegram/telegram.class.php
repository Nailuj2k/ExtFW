<?php
/**
 * TelegramBot - Telegram Bot API client
 *
 * Uso desde otros módulos:
 *   TelegramBot::sendMessage($chatId, "Texto del aviso");
 *   TelegramBot::sendToUser($userId, "Texto del aviso");
 *
 * Configuración necesaria en CFG_CFG:
 *   telegram.bot_token     → token del bot (obtenido de @BotFather)
 *   telegram.webhook_secret → secreto aleatorio para validar el webhook
 */
class TelegramBot {

    const API_BASE = 'https://api.telegram.org/bot';
    const TIMEOUT  = 10;

    // -------------------------------------------------------------------------
    // Public API — usable from other modules
    // -------------------------------------------------------------------------

    /**
     * Sends a plain text message to a Telegram chat_id.
     * Returns true on success.
     */
    static function sendMessage(string $chatId, string $text, string $parseMode = ''): bool {
        $token = self::getToken();
        if (!$token || !$chatId || !$text) return false;

        $params = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];
        if ($parseMode) {
            $params['parse_mode'] = $parseMode; // 'HTML' or 'Markdown'
        }

        $result = self::apiCall($token, 'sendMessage', $params);
        $ok = !empty($result['ok']);

        TelegramStore::logMessage($chatId, 0, 'out', $text, $ok);
        return $ok;
    }

    /**
     * Like sendMessage but returns the full API response array (for debugging).
     */
    static function sendMessageRaw(string $chatId, string $text, string $parseMode = ''): array {
        $token = self::getToken();
        if (!$token) return ['ok' => false, 'description' => 'No token'];

        $params = ['chat_id' => $chatId, 'text' => $text];
        if ($parseMode) $params['parse_mode'] = $parseMode;

        return self::apiCall($token, 'sendMessage', $params);
    }

    /**
     * Sends a message to the Telegram chat linked to an ExtFW user_id.
     * Returns false if the user has no linked chat.
     */
    static function sendToUser(int $userId, string $text, string $parseMode = ''): bool {
        $chatId = TelegramStore::getChatIdForUser($userId);
        if (!$chatId) return false;
        return self::sendMessage($chatId, $text, $parseMode);
    }

    /**
     * Returns true if the user has a linked Telegram chat.
     */
    static function userIsLinked(int $userId): bool {
        return TelegramStore::getChatIdForUser($userId) !== null;
    }

    // -------------------------------------------------------------------------
    // Webhook
    // -------------------------------------------------------------------------

    /**
     * Registers the webhook URL with Telegram.
     * Call once from install.php or admin panel.
     */
    static function setWebhook(string $url): array {
        $token = self::getToken();
        if (!$token) return ['ok' => false, 'description' => 'No bot token configured'];

        $params = [
            'url'             => $url,
            'allowed_updates' => ['message'],
        ];

        $secret = self::getWebhookSecret();
        if ($secret !== '') {
            $params['secret_token'] = $secret;
        }

        return self::apiCall($token, 'setWebhook', $params);
    }

    static function deleteWebhook(): array {
        $token = self::getToken();
        if (!$token) return ['ok' => false, 'description' => 'No bot token configured'];
        return self::apiCall($token, 'deleteWebhook', []);
    }

    static function getWebhookInfo(): array {
        $token = self::getToken();
        if (!$token) return ['ok' => false];
        return self::apiCall($token, 'getWebhookInfo', []);
    }

    /**
     * Validates that an incoming webhook request has the correct secret.
     * Telegram sends it as X-Telegram-Bot-Api-Secret-Token header.
     */
    static function validateWebhookSecret(): bool {
        $expected = self::getWebhookSecret();
        if (!$expected) return true; // No secret configured → accept all (not recommended)

        $received = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
        return hash_equals($expected, $received);
    }

    // -------------------------------------------------------------------------
    // Incoming update processing
    // -------------------------------------------------------------------------

    /**
     * Processes a decoded Telegram update array.
     * Called from raw.php when Telegram POSTs to the webhook.
     */
    static function handleUpdate(array $update): void {
        $message = $update['message'] ?? null;
        if (!is_array($message)) return;

        $chatId    = (string)($message['chat']['id'] ?? '');
        $text      = trim((string)($message['text'] ?? ''));
        $username  = (string)($message['from']['username'] ?? '');
        $firstName = (string)($message['from']['first_name'] ?? '');
        $lastName  = (string)($message['from']['last_name'] ?? '');

        if ($chatId === '' || $text === '') return;

        TelegramStore::logMessage($chatId, 0, 'in', $text);

        // /start TOKEN  — registration flow
        if (stripos($text, '/start') === 0) {
            $parts = preg_split('/\s+/', trim($text));
            $token = strtoupper(trim((string)($parts[1] ?? '')));
            self::handleStart($chatId, $token, $username, $firstName, $lastName);
            return;
        }

        // /unlink — remove link
        if (stripos($text, '/unlink') === 0) {
            self::handleUnlink($chatId);
            return;
        }

        // /status
        if (stripos($text, '/status') === 0) {
            self::handleStatus($chatId);
            return;
        }

        // /help
        if (stripos($text, '/help') === 0) {
            self::handleHelp($chatId);
            return;
        }

        // /ai  [service|off|reset]
        if (stripos($text, '/ai') === 0) {
            $parts = preg_split('/\s+/', trim($text));
            $arg   = strtolower(trim($parts[1] ?? ''));
            self::handleAi($chatId, $arg);
            return;
        }

        // Free text — TGRAM_TEXTS keywords first, AI fallback
        self::handleText($chatId, $text);
    }

    // -------------------------------------------------------------------------
    // Command handlers
    // -------------------------------------------------------------------------

    private static function handleStart(string $chatId, string $token, string $username, string $firstName, string $lastName): void {
        if ($token === '') {
            $name = $firstName ? " {$firstName}" : '';
            self::sendMessage($chatId,
                "Hola{$name}! Para vincular tu cuenta, ve a tu perfil en la web y genera un código de vinculación.\n\nEscribe /help para más información."
            );
            return;
        }

        $result = TelegramStore::consumeToken($token);

        // consumeToken returns an int on success, or an array with 'error' on failure
        if (is_array($result)) {
            $error = $result['error'] ?? 'unknown';
            $diag  = $result['debug'] ?? [];

            switch ($error) {
                case 'already_used':
                    self::sendMessage($chatId, "Este código ya fue usado. Genera uno nuevo desde tu perfil en la web.");
                    break;
                case 'expired':
                    self::sendMessage($chatId, "El código ha caducado (válido 10 min). Genera uno nuevo desde tu perfil en la web.");
                    break;
                case 'not_found':
                    self::sendMessage($chatId,
                        "Código no encontrado [{$token}]. Asegúrate de copiar el código completo o genera uno nuevo desde la web."
                    );
                    break;
                case 'db_error':
                    self::sendMessage($chatId,
                        "Error interno al validar el código. Inténtalo de nuevo o contacta con el administrador. [db_error]"
                    );
                    break;
                default:
                    self::sendMessage($chatId, "Código inválido o caducado [{$token}]. Genera uno nuevo desde tu perfil en la web.");
            }
            return;
        }

        // $result is the user_id (int)
        $userId = (int)$result;
        TelegramStore::saveChat($chatId, $userId, $username, $firstName, $lastName);
        self::sendMessage($chatId, "¡Cuenta vinculada correctamente! A partir de ahora recibirás notificaciones aquí.");
    }

    private static function handleUnlink(string $chatId): void {
        $row = TelegramStore::findChatByChatId($chatId);
        if (!$row || !$row['user_id']) {
            self::sendMessage($chatId, "No hay ninguna cuenta vinculada a este chat.");
            return;
        }

        TelegramStore::unlinkChat((int)$row['user_id']);
        self::sendMessage($chatId, "Cuenta desvinculada. Ya no recibirás notificaciones en Telegram.");
    }

    private static function handleStatus(string $chatId): void {
        $row = TelegramStore::findChatByChatId($chatId);
        if (!$row || !$row['active']) {
            self::sendMessage($chatId, "Este chat no está vinculado a ninguna cuenta.");
            return;
        }
        self::sendMessage($chatId, "Chat vinculado. Recibirás notificaciones en este chat.");
    }

    private static function handleHelp(string $chatId): void {
        $aiService = TelegramStore::getAiService();
        $aiLine    = TelegramStore::isAiEnabled()
            ? "/ai [servicio|off|reset] — IA activa: {$aiService}"
            : '/ai [servicio] — IA desactivada';

        $text = implode("\n", [
            'Comandos disponibles:',
            '/start CÓDIGO — vincula tu cuenta con el código generado en la web',
            '/unlink — desvincula este chat',
            '/status — muestra si el chat está vinculado',
            $aiLine,
            '/help — muestra esta ayuda',
            '',
            'También puedes escribirme directamente y te responderé con IA.',
        ]);
        self::sendMessage($chatId, $text);
    }

    private static function handleAi(string $chatId, string $arg): void {
        $services = TelegramAI::SERVICES;

        // No argument — show current status
        if ($arg === '') {
            $current = TelegramStore::getAiService();
            $list    = implode(', ', $services);
            self::sendMessage($chatId,
                "IA actual: {$current}\nServicios disponibles: {$list}, off\n\n"
                . "Para cambiar (requiere ser admin del área 'telegram'):\n/ai claude"
            );
            return;
        }

        // Reset conversation history
        if ($arg === 'reset') {
            TelegramAI::clearHistory($chatId);
            self::sendMessage($chatId, 'Historial de conversación borrado.');
            return;
        }

        // Change service — admin only
        if (!TelegramStore::isLinkedUserAdmin($chatId)) {
            self::sendMessage($chatId, 'Solo los administradores del área telegram pueden cambiar la IA.');
            return;
        }

        if ($arg === 'off') {
            TelegramStore::setAiService('off');
            self::sendMessage($chatId, 'IA desactivada. Solo responderé a comandos.');
            return;
        }

        if (!in_array($arg, $services, true)) {
            $list = implode(', ', $services);
            self::sendMessage($chatId, "Servicio desconocido. Disponibles: {$list}, off");
            return;
        }

        TelegramStore::setAiService($arg);
        TelegramAI::clearHistory($chatId); // Fresh start with new model
        self::sendMessage($chatId, "IA cambiada a: {$arg} ✓");
    }

    private static function handleText(string $chatId, string $text): void {

        // ── FEATURE: TGRAM_TEXTS — respuestas automáticas por palabras clave ────
        // Se comprueba ANTES que la IA. Si el mensaje contiene alguna keyword
        // definida en la tabla TGRAM_TEXTS, se responde con el texto configurado
        // y se corta el flujo (no se llama a la IA).
        //
        // Gestión desde: telegram/admin → tab "Textos"
        // Lógica de matching: TelegramStore::findTextResponse()
        //
        // Para añadir una nueva capa de procesado antes de la IA (ej: búsqueda
        // en la web, comandos ocultos, respuestas de FAQ), sigue este mismo
        // patrón: comprueba → si hay respuesta, envía y return.
        $textReply = TelegramStore::findTextResponse($text);
        if ($textReply !== null) {
            self::sendMessage($chatId, $textReply);
            return;
        }
        // ── END FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────

        // ── FEATURE: AI fallback ──────────────────────────────────────────────────
        // Si ninguna keyword coincidió, se envía el mensaje a la IA configurada.
        // Servicio activo: TelegramStore::getAiService() (se cambia con /ai)
        // Implementación: TelegramAI::ask() en telegramai.class.php
        //
        // Para desactivar la IA completamente: /ai off
        if (!TelegramStore::isAiEnabled()) {
            self::sendMessage($chatId, 'Escribe /help para ver los comandos disponibles.');
            return;
        }

        $reply = TelegramAI::ask($chatId, $text);

        if ($reply === null) {
            $service = TelegramStore::getAiService();
            $detail  = TelegramAI::$lastError;
            self::sendMessage($chatId,
                "Error contactando con la IA ({$service}).\n{$detail}"
            );
            return;
        }

        self::sendMessage($chatId, $reply);
        // ── END FEATURE: AI fallback ──────────────────────────────────────────────
    }

    // -------------------------------------------------------------------------
    // Bot info
    // -------------------------------------------------------------------------

    static function getMe(): array {
        $token = self::getToken();
        if (!$token) return ['ok' => false];
        return self::apiCall($token, 'getMe', []);
    }

    static function getBotUsername(): string {
        $result = self::getMe();
        return (string)($result['result']['username'] ?? '');
    }

    // -------------------------------------------------------------------------
    // Config helpers
    // -------------------------------------------------------------------------

    static function getToken(): string {
        return trim((string)(CFG::$vars['modules']['telegram']['bot_token'] ?? TelegramStore::getCfgValue('modules.telegram.bot_token', '')));
    }

    static function getWebhookSecret(): string {
        return trim((string)(CFG::$vars['modules']['telegram']['webhook_secret'] ?? TelegramStore::getCfgValue('modules.telegram.webhook_secret', '')));
    }

    static function getWebhookUrl(): string {
        return rtrim(SCRIPT_HOST, '/') . '/telegram/raw/webhook';
    }

    static function isConfigured(): bool {
        return self::getToken() !== '';
    }

    // -------------------------------------------------------------------------
    // HTTP
    // -------------------------------------------------------------------------

    private static function apiCall(string $token, string $method, array $params): array {
        $url = self::API_BASE . $token . '/' . $method;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($params),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!is_string($response) || $response === '') return ['ok' => false];
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['ok' => false];
    }
}
