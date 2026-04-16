<?php

    if (!isset($_SESSION['valid_user']) || !$_SESSION['valid_user']) {
        echo json_encode(['error' => 1, 'msg' => 'Not logged in']);
        exit;
    }


    $action = $_ARGS['action'] ?? '';

    // Con action = API custom del modulo
    if ($action) {

        $userId = (int)$_SESSION['userid'];
        $result = ['error' => 0];


        switch ($action) {

            // Generate a one-time registration token for the current user
            case 'get_token':
                $token       = TelegramStore::createToken($userId);
                $botUsername = TelegramBot::getBotUsername();
                $result['token']       = $token;
                $result['bot_username'] = $botUsername;
                $result['deep_link']   = $botUsername ? "https://t.me/{$botUsername}?start={$token}" : '';
                // debug: verify the token was actually saved
                $result['_db_check']   = TelegramStore::debugFindToken($token);
                break;

            // Check link status
            case 'status':
                $chat = TelegramStore::findChatByUserId($userId);
                $result['linked']     = !empty($chat);
                $result['username']   = $chat['username'] ?? '';
                $result['first_name'] = $chat['first_name'] ?? '';
                $result['chat_id']    = $chat ? $chat['chat_id'] : '';
                break;

            // Unlink Telegram from this account
            case 'unlink':
                TelegramStore::unlinkChat($userId);
                $result['msg'] = 'Telegram desvinculado';
                break;

            // Send a test message to the linked chat
            case 'test_message':
                $chat = TelegramStore::findChatByUserId($userId);
                if (!$chat) {
                    $result['error'] = 1;
                    $result['msg']   = 'No hay chat vinculado';
                    break;
                }
                $ok = TelegramBot::sendMessage($chat['chat_id'], 'Mensaje de prueba desde ' . (SCRIPT_HOST ?? 'noxtr') . '. Las notificaciones están funcionando correctamente.');
                $result['ok']  = $ok;
                $result['msg'] = $ok ? 'Mensaje enviado' : 'Error al enviar';
                break;

            // Admin: register webhook
            case 'set_webhook':
                if (!TelegramAdministrador()) { $result['error'] = 1; $result['msg'] = 'Forbidden'; break; }
                $url    = TelegramBot::getWebhookUrl();
                $apiResult = TelegramBot::setWebhook($url);
                $result['api'] = $apiResult;
                $result['url'] = $url;
                break;

            // Admin: get webhook info
            case 'webhook_info':
                if (!TelegramAdministrador()) { $result['error'] = 1; $result['msg'] = 'Forbidden'; break; }
                $result['api'] = TelegramBot::getWebhookInfo();
                break;

            // Admin: delete webhook
            case 'delete_webhook':
                if (!TelegramAdministrador()) { $result['error'] = 1; $result['msg'] = 'Forbidden'; break; }
                $result['api'] = TelegramBot::deleteWebhook();
                break;

            // Admin: bot info
            case 'get_me':
                if (!TelegramAdministrador()) { $result['error'] = 1; $result['msg'] = 'Forbidden'; break; }
                $result['api'] = TelegramBot::getMe();
                break;

            // Admin: list active tokens (debug)
            case 'list_tokens':
                if (!TelegramAdministrador()) { $result['error'] = 1; $result['msg'] = 'Forbidden'; break; }
                $result['tokens'] = TelegramStore::debugListTokens();
                $result['now'] = time();
                break;

            // Admin: send test message to hardcoded chat_id (debug)
            case 'send_test':
                if (!TelegramAdministrador()) { $result['error'] = 1; $result['msg'] = 'Forbidden'; break; }
                $result['api'] = TelegramBot::sendMessageRaw('4980379', 'Prueba desde el admin de ' . (SCRIPT_HOST ?? 'noxtr'));
                break;

            // Admin: test AI service directly (no bot, no webhook needed)
            case 'test_ai':

                
                //error_reporting(E_ALL /*& ~E_NOTICE & ~E_WARNING*/); // Mostrar todos los errores
                //ini_set('display_errors', 1); // Mostrar errores en pantalla
                //ini_set('error_reporting', E_ALL /*& ~E_WARNING & ~E_NOTICE*/);
                
                try{
                    $prompt  = trim($_ARGS['prompt'] ?? 'Hola, ¿cómo estás?');
                    $service = TelegramStore::getAiService();
                    $chatId  = 'admin_test';
                    TelegramAI::clearHistory($chatId);
                    $reply   = TelegramAI::ask($chatId, $prompt);
                    TelegramAI::clearHistory($chatId);
                    $result['service']    = $service;
                    $result['prompt']     = $prompt;
                    $result['reply']      = $reply;
                    $result['last_error'] = TelegramAI::$lastError;
                    $result['ok']         = $reply !== null;
                } catch (Throwable $e) {
                    $result['error']     = 1;
                    $result['exception'] = get_class($e) . ': ' . $e->getMessage();
                    $result['file']      = $e->getFile() . ':' . $e->getLine();
                }
                break;

            default:
                $result['error'] = 1;
                $result['msg']   = 'Unknown action';
        }

        echo json_encode($result);

    
    } else {
        // Sin action = Scaffold AJAX (operaciones CRUD de TABLE_*)
        include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
    }
