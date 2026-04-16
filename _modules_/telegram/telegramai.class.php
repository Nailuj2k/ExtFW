<?php

/**
 * TelegramAI — AI service adapter for the Telegram bot
 *
 * Wraps the AI services from _modules_/edit/ai/ for use in the webhook context:
 *   - Conversation history keyed by chat_id (not session_id)
 *   - No extractCode() — this is a chat, not a code editor
 *   - Configurable system prompt
 *   - Service selected from CFG_CFG (modules.telegram.ai_service)
 *
 * Usage:
 *   $reply = TelegramAI::ask($chatId, $userText);
 *   TelegramAI::clearHistory($chatId);
 */
class TelegramAI {

    const SERVICES = ['ollama', 'claude', 'openai', 'gemini', 'grok', 'kimi', 'deepseek'];

    /** Last error detail — set by curlPost/callX on failure, readable by callers. */
    public static $lastError = '';

    const DEFAULT_SYSTEM_PROMPT = 'Eres un asistente simpático integrado en un bot de Telegram. '
        . 'Responde siempre en el mismo idioma en que te escriba el usuario. '
        . 'Sé conciso: las respuestas en Telegram deben ser cortas y directas. '
        . 'No uses bloques de código a menos que el usuario pida explícitamente código.';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Send a message to the configured AI and return the reply.
     * Returns null on error (caller should handle gracefully).
     */
    static function ask(string $chatId, string $userText): ?string {
        $service = TelegramStore::getCfgValue('modules.telegram.ai_service', 'ollama');
        $method  = 'call' . ucfirst(strtolower($service));

        if (!method_exists(self::class, $method)) {
            self::$lastError = "unknown_service={$service}";
            return null;
        }

        $history = self::loadHistory($chatId);
        // Use call_user_func — self::$method() accesses a property, not a method
        $reply   = call_user_func([self::class, $method], $userText, $history);

        if ($reply !== null) {
            $history[] = ['role' => 'user',     'content' => $userText];
            $history[] = ['role' => 'assistant', 'content' => $reply];
            self::saveHistory($chatId, $history);
        }

        return $reply;
    }

    /**
     * Delete the conversation history for a chat.
     */
    static function clearHistory(string $chatId): void {
        $path = self::historyPath($chatId);
        if (file_exists($path)) unlink($path);
    }

    /**
     * Returns true if the configured AI service appears usable
     * (key defined or Ollama reachable).
     */
    static function isAvailable(): bool {
        $service = TelegramStore::getCfgValue('modules.telegram.ai_service', 'ollama');
        return in_array(strtolower($service), self::SERVICES, true);
    }

    // -------------------------------------------------------------------------
    // Service implementations
    // -------------------------------------------------------------------------

    private static function callOllama(string $text, array $history): ?string {
        // Read same config as noxtr/ajax.php translate action
        $apiKey = CFG::$vars['ai']['ollama']['api_key'] ?? (defined('OLLAMA_API_KEY') ? OLLAMA_API_KEY : '');
        $model  = CFG::$vars['ai']['ollama']['model']   ?? (defined('OLLAMA_MODEL')   ? OLLAMA_MODEL  : 'gpt-oss:20b-cloud');
        $host   = CFG::$vars['ai']['ollama']['host']    ?? (defined('OLLAMA_HOST')    ? OLLAMA_HOST   : 'http://localhost:11434');

        // Cloud if key present, local otherwise
        if ($apiKey) {
            $url = 'https://ollama.com/api/chat';
        } else {
            $url = rtrim($host, '/') . '/api/chat';
        }

        $messages = self::buildMessages($history, $text);
        $payload  = ['model' => $model, 'messages' => $messages, 'stream' => false];

        $headers = [];
        if ($apiKey) $headers[] = 'Authorization: Bearer ' . $apiKey;

        self::$lastError = "url={$url} model={$model} key=" . ($apiKey ? substr($apiKey,0,8).'…' : 'none');

        $response = self::curlPost($url, $payload, $headers);

        if (!isset($response['message']['content'])) {
            self::$lastError .= ' | response=' . json_encode($response);
            return null;
        }

        return $response['message']['content'];
    }

    private static function callClaude(string $text, array $history): ?string {
        $key = CFG::$vars['ai']['claude']['api_key'] ?? (defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : '');
        if (!$key) return null;

        $url      = 'https://api.anthropic.com/v1/messages';
        $messages = self::buildMessages($history, $text, false); // Claude: no system in messages
        $payload  = [
            'model'      => 'claude-haiku-4-5-20251001', // fast + cheap for chat
            'max_tokens' => 1024,
            'system'     => self::getSystemPrompt(),
            'messages'   => $messages,
        ];
        $headers = [
            'x-api-key: ' . $key,
            'anthropic-version: 2023-06-01',
        ];

        $response = self::curlPost($url, $payload, $headers);
        return $response['content'][0]['text'] ?? null;
    }

    private static function callOpenai(string $text, array $history): ?string {
        $key = CFG::$vars['ai']['openai']['api_key'] ?? (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
        if (!$key) return null;

        $url      = 'https://api.openai.com/v1/chat/completions';
        $messages = self::buildMessages($history, $text);
        $payload  = ['model' => 'gpt-4o-mini', 'messages' => $messages, 'max_tokens' => 1024];
        $headers  = ['Authorization: Bearer ' . $key];

        $response = self::curlPost($url, $payload, $headers);
        return $response['choices'][0]['message']['content'] ?? null;
    }

    private static function callGemini(string $text, array $history): ?string {
        $key = CFG::$vars['ai']['gemini']['api_key'] ?? (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');
        if (!$key) return null;

        $model = 'gemini-2.0-flash';
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        // Gemini uses 'parts' and 'model'/'user' roles (no 'assistant')
        $contents = [];
        foreach ($history as $msg) {
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $text]]];

        $payload = [
            'system_instruction' => ['parts' => [['text' => self::getSystemPrompt()]]],
            'contents'           => $contents,
        ];

        $response = self::curlPost($url, $payload, []);
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    private static function callGrok(string $text, array $history): ?string {
        $key = CFG::$vars['ai']['grok']['api_key'] ?? (defined('GROK_API_KEY') ? GROK_API_KEY : '');
        if (!$key) return null;

        // Grok uses OpenAI-compatible API
        $url      = 'https://api.x.ai/v1/chat/completions';
        $messages = self::buildMessages($history, $text);
        $payload  = ['model' => 'grok-3-mini', 'messages' => $messages, 'max_tokens' => 1024];
        $headers  = ['Authorization: Bearer ' . $key];

        $response = self::curlPost($url, $payload, $headers);
        return $response['choices'][0]['message']['content'] ?? null;
    }

    private static function callKimi(string $text, array $history): ?string {
        $key = CFG::$vars['ai']['kimi']['api_key'] ?? (defined('KIMI_API_KEY') ? KIMI_API_KEY : '');
        if (!$key) return null;

        $url      = 'https://api.moonshot.cn/v1/chat/completions';
        $messages = self::buildMessages($history, $text);
        $payload  = ['model' => 'moonshot-v1-8k', 'messages' => $messages, 'max_tokens' => 1024];
        $headers  = ['Authorization: Bearer ' . $key];

        $response = self::curlPost($url, $payload, $headers);
        return $response['choices'][0]['message']['content'] ?? null;
    }

    private static function callDeepseek(string $text, array $history): ?string {
        $key = CFG::$vars['ai']['deepseek']['api_key'] ?? (defined('DEEPSEEK_API_KEY') ? DEEPSEEK_API_KEY : '');
        if (!$key) return null;

        $url      = 'https://api.deepseek.com/v1/chat/completions';
        $messages = self::buildMessages($history, $text);
        $payload  = ['model' => 'deepseek-chat', 'messages' => $messages, 'max_tokens' => 1024];
        $headers  = ['Authorization: Bearer ' . $key];

        $response = self::curlPost($url, $payload, $headers);
        return $response['choices'][0]['message']['content'] ?? null;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a messages array with optional system prompt prepended.
     * @param bool $includeSystem  Set false for APIs that take system separately (Claude)
     */
    private static function buildMessages(array $history, string $newText, bool $includeSystem = true): array {
        $messages = [];
        if ($includeSystem) {
            $messages[] = ['role' => 'system', 'content' => self::getSystemPrompt()];
        }
        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $newText];
        return $messages;
    }

    private static function getSystemPrompt(): string {
        $custom = TelegramStore::getCfgValue('modules.telegram.ai_system_prompt', '');
        return $custom ?: self::DEFAULT_SYSTEM_PROMPT;
    }

    private static function historyPath(string $chatId): string {
        $dir = defined('SCRIPT_DIR_MEDIA') ? SCRIPT_DIR_MEDIA : 'media';
        return $dir . '/tg_history_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $chatId) . '.json';
    }

    private static function loadHistory(string $chatId): array {
        $path = self::historyPath($chatId);
        if (!file_exists($path)) return [];
        $data = json_decode(file_get_contents($path), true);
        // Keep last 20 exchanges (40 messages) to avoid token bloat
        return is_array($data) ? array_slice($data, -40) : [];
    }

    private static function saveHistory(string $chatId, array $history): void {
        $path = self::historyPath($chatId);
        file_put_contents($path, json_encode($history, JSON_UNESCAPED_UNICODE));
    }

    private static function curlPost(string $url, array $payload, array $extraHeaders): array {
        $headers = array_merge(['Content-Type: application/json'], $extraHeaders);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_errno($ch) ? curl_error($ch) : '';
        curl_close($ch);

        if ($curlErr) {
            self::$lastError .= " | curl_err={$curlErr}";
            return [];
        }
        if (!is_string($response) || $response === '') {
            self::$lastError .= " | http={$httpCode} empty_response";
            return [];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            self::$lastError .= " | http={$httpCode} invalid_json=" . substr($response, 0, 120);
            return [];
        }

        if ($httpCode !== 200) {
            self::$lastError .= " | http={$httpCode} body=" . substr($response, 0, 200);
            return $decoded; // return anyway so callers can inspect error fields
        }

        return $decoded;
    }
}
