<?php
/**
 * Telegram webhook endpoint
 *
 * URL: /telegram/raw/webhook
 * $_ARGS[0] = 'telegram'
 * $_ARGS[1] = 'raw'
 * $_ARGS[2] = 'webhook'
 *
 * Telegram hace POST aquí cada vez que alguien interactúa con el bot.
 * Validamos el secret header y procesamos el update.
 */

header('Content-Type: application/json');

// Read body once and reuse — php://input can only be read once on some servers
$body = file_get_contents('php://input');

// Debug: log incoming request
file_put_contents(
    SCRIPT_DIR_LOG . '/' . date('Ymd_Hi') . 'tg_webhook_debug.txt',
    json_encode([
        'time'   => date('Y-m-d H:i:s'),
        'args'   => $_ARGS ?? [],
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'body'   => $body,
    ]) . "\n",
    FILE_APPEND
);

$subaction = $_ARGS[2] ?? '';

if ($subaction !== 'webhook') {
    http_response_code(404);
    echo json_encode(['ok' => false, 'description' => 'Not found', 'args' => $_ARGS ?? []]);
    exit;
}

// Validate webhook secret (X-Telegram-Bot-Api-Secret-Token header)
if (!TelegramBot::validateWebhookSecret()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'description' => 'Forbidden']);
    exit;
}

if (!$body) {
    echo json_encode(['ok' => true]);
    exit;
}

$update = json_decode($body, true);
if (!is_array($update)) {
    echo json_encode(['ok' => true]);
    exit;
}

// Process the update
TelegramBot::handleUpdate($update);

// Always respond 200 OK to Telegram (even on errors — otherwise it retries)
echo json_encode(['ok' => true]);
exit;
