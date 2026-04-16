# Telegram — Módulo ExtFW

Módulo de integración con Telegram Bot API. Proporciona notificaciones por Telegram y registro de usuarios vía webhook.

## Uso desde otros módulos

```php
// Enviar mensaje a un usuario por su user_id de ExtFW
TelegramBot::sendToUser($userId, "Monitor Mostro: han tomado tu orden #a1b2c3");

// Enviar mensaje directo a un chat_id
TelegramBot::sendMessage($chatId, "Texto", 'HTML');

// Comprobar si el usuario tiene Telegram vinculado
TelegramBot::userIsLinked($userId);
```

## File Map

| Archivo | Propósito |
|---|---|
| `telegram.class.php` | Bot API client: sendMessage, webhook, handleUpdate, comandos |
| `telegramstore.class.php` | Data layer: TGRAM_CHATS, TGRAM_TOKENS, TGRAM_LOG |
| `init.php` | Init del módulo: ensureTables, helpers de rol |
| `index.php` | Router: ajax / raw / admin / install / run |
| `run.php` | UI de usuario: vincular/desvincular Telegram |
| `ajax.php` | Acciones AJAX: get_token, status, unlink, test_message, set_webhook |
| `raw.php` | Webhook endpoint: recibe POSTs de Telegram |
| `head.php` | Carga CSS y JS |
| `footer.php` | Inicializa Telegram.init() con config PHP |
| `script.js` | Lógica cliente: vinculación, estado, prueba |
| `style.css` | Estilos mínimos |
| `admin.php` | Panel admin: tabs Chats / Log / Setup |
| `TABLE_TGRAM_CHATS.php` | Definición tabla para la clase Table |
| `TABLE_TGRAM_LOG.php` | Definición tabla para la clase Table |
| `install.php` | Inserta valores iniciales en CFG_CFG |

## Tablas

### TGRAM_CHATS
Vinculación usuario ExtFW ↔ chat_id de Telegram.

| Campo | Descripción |
|---|---|
| `chat_id` | ID del chat de Telegram (unique) |
| `user_id` | ID del usuario en ExtFW |
| `username` | @username de Telegram |
| `first_name` / `last_name` | Nombre del usuario |
| `active` | 0 = desvinculado |
| `linked_at` | Timestamp de vinculación |

### TGRAM_TOKENS
Tokens de un solo uso para el flujo de vinculación.

| Campo | Descripción |
|---|---|
| `token` | 8 chars hex, mayúsculas |
| `user_id` | Usuario que generó el token |
| `created_at` | Timestamp de creación |
| `used_at` | NULL = no usado aún. Caducan a los 10 min. Solo hay un token activo por usuario a la vez (el anterior se borra al generar uno nuevo). |

### TGRAM_LOG
Log de mensajes enviados y recibidos.

| Campo | Descripción |
|---|---|
| `direction` | `in` (del usuario al bot) / `out` (del bot al usuario) |
| `ok` | 1 = enviado correctamente |

## Configuración (CFG_CFG)

| Clave | Descripción |
|---|---|
| `telegram.bot_token` | Token del bot de @BotFather |
| `telegram.webhook_secret` | Secret aleatorio. Se envía como `X-Telegram-Bot-Api-Secret-Token` para validar que el POST viene de Telegram |

## Webhook URL

```
https://[host]/telegram/raw/webhook
```

Registrar/actualizar con el botón en `telegram/admin/tab=setup` o llamando a `TelegramBot::setWebhook($url)`.

## Flujo de vinculación de usuario

1. Usuario va a `/telegram` → pulsa "Vincular Telegram"
2. AJAX `get_token` → genera token de 8 chars en `TGRAM_TOKENS`
3. UI muestra deep link: `https://t.me/BotUsername?start=TOKEN`
4. Usuario abre el link → Telegram abre el bot con `/start TOKEN`
5. Webhook recibe el update → `TelegramBot::handleUpdate()` → `handleStart()`
6. `TelegramStore::consumeToken(TOKEN)` valida y devuelve `user_id`
7. `TelegramStore::saveChat()` guarda la vinculación en `TGRAM_CHATS`
8. Bot responde confirmación al usuario

## Comandos del bot

| Comando | Descripción |
|---|---|
| `/start [TOKEN]` | Vincula la cuenta con el token generado en la web |
| `/unlink` | Desvincula el chat |
| `/status` | Muestra si el chat está vinculado |
| `/help` | Ayuda |

## Rules

- Todas las llamadas a la Bot API son server-side (PHP curl). Nunca desde el navegador.
- El webhook debe responder siempre 200 OK, aunque haya errores internos (si no, Telegram reintenta).
- Los tokens caducan a los 10 minutos. Al generar uno nuevo para el mismo user, el anterior se elimina de forma inmediata (solo un token activo por usuario).
- `TelegramStore::consumeToken()` devuelve `int` (user_id) en éxito, o `array ['error' => string, 'debug' => array]` en fallo (not_found / already_used / expired / db_error).
- `TelegramStore` extiende `DbConnection` — mismo patrón que `NoxtrStore`.
