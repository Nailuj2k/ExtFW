<?php


$writeWebhookLog = function (string $prefix, string $content = '') { //use ($logDir) {
    
    $timestamp = date('Y-m-d H:i:s');
    
    $log_content      = "\n===============================================================\n"
                      .    $prefix . ' ' . $timestamp  . ' ' . $_SERVER['REQUEST_URI'];
    if ($content!=='')           
        $log_content .= "\n---------------------------------------------------------------\n" 
                      .    $content ;
    $log_content     .= "\n===============================================================\n";

    file_put_contents( SCRIPT_DIR_LOG.'/'. date('Ymd_Hi') . '.txt', $log_content ,  FILE_APPEND );  

};

$btcpay_webhook   = $_ARGS[1]==='btcpay_webhook';
$webhook_callback = $_ARGS[1]==='checkout' && $_ARGS[2]==='bitcoin' && $_ARGS[3]==='callback';
$valid_callabck   = $btcpay_webhook || $webhook_callback; 

if (!$valid_callabck) {
    http_response_code(400);
    $contenido = "Invalid Path\n\n". "Received _ARGS: " . print_r($_ARGS, true) . "\n";
    $writeWebhookLog('webhook_PATH_INVALID', $contenido);
    exit;
}else{
    $writeWebhookLog('webhook_BEGIN');
}

$payload = file_get_contents("php://input");


if (!is_string($payload) || trim($payload) === '') {
    http_response_code(400);
    $writeWebhookLog('webhook_PAYLOAD_EMPTY', "Empty payload\n\nReceived _ARGS: " . print_r($_ARGS, true));
    exit;
}

$writeWebhookLog('webhook_RAW_payload', $payload);

$cfg_btc = Table::asArrayValues("SELECT V,K from CFG_CFG WHERE K LIKE 'btcpay.%'",'K','V');  
$secret  = $cfg_btc['btcpay.secret'] ?? '';
$headers = function_exists('getallheaders') ? getallheaders() : [];

$writeWebhookLog('webhook_RAW_headers', print_r($headers, true));


$btcpaySig = '';
$forwardSig = '';
foreach ($headers as $k => $v) {
    $key = strtolower($k);
    if ($key === 'btcpay-sig') { $btcpaySig = $v; }
    if ($key === 'x-forward-sig') { $forwardSig = $v; }
}

if ($btcpay_webhook) {
    $signatureHeader = $btcpaySig;
    $sigName = 'BTCPay-Sig';
} else {
    $signatureHeader = $forwardSig;
    $sigName = 'X-Forward-Sig';
}

if (trim($signatureHeader) === '') {
    http_response_code(400);
    $writeWebhookLog('webhook_SIG_MISSING', "Signature missing ($sigName)\n\nHeaders: " . print_r($headers, true) . "\n\n" . $payload);
    exit;
}

$receivedSig = (strpos($signatureHeader, 'sha256=') === 0) ? substr($signatureHeader, 7) : $signatureHeader;
$expectedSig = hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expectedSig, $receivedSig)) {
    http_response_code(400);
    $writeWebhookLog('webhook_SIG_INVALID', "Invalid signature ($sigName)\n\nReceived: $signatureHeader\nExpected: sha256=$expectedSig\n\n$payload");
    exit;
}



$data = json_decode($payload, true);

if (!is_array($data)) {
    http_response_code(400);
    $writeWebhookLog('webhook_JSON_INVALID', "Invalid JSON\n\n" . $payload);
    exit;
//}else{
    //$writeWebhookLog('webhook_JSON_VALID', "Valid JSON\n\n" . print_r($data, true));
}

// ============================================================
// Funciones para pago a Lightning Address
// ============================================================

$getLnurlInvoice = function($lnAddress, $amountSats) use ($writeWebhookLog) {
    $parts = explode('@', $lnAddress);
    if (count($parts) !== 2) return null;
    
    [$user, $domain] = $parts;
    $lnurlEndpoint = "https://$domain/.well-known/lnurlp/$user";
    
    $ch = curl_init($lnurlEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $writeWebhookLog('lnurl_RESOLVE_FAIL', "HTTP $httpCode para $lnurlEndpoint\n\n$response");
        return null;
    }
    
    $lnurlData = json_decode($response, true);
    if (!$lnurlData || !isset($lnurlData['callback'])) return null;
    
    $minSats = ($lnurlData['minSendable'] ?? 1000) / 1000;
    $maxSats = ($lnurlData['maxSendable'] ?? 100000000) / 1000;
    
    if ($amountSats < $minSats || $amountSats > $maxSats) {
        $writeWebhookLog('lnurl_AMOUNT_OUT_OF_RANGE', "Amount: $amountSats, Min: $minSats, Max: $maxSats");
        return null;
    }
    
    $callback = $lnurlData['callback'];
    $separator = strpos($callback, '?') !== false ? '&' : '?';
    $invoiceUrl = $callback . $separator . 'amount=' . ($amountSats * 1000);
    
    $ch = curl_init($invoiceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $writeWebhookLog('lnurl_INVOICE_FAIL', "HTTP $httpCode to $invoiceUrl\n\n$response");
        return null;
    }
    
    $invoiceData = json_decode($response, true);
    return $invoiceData['pr'] ?? null;
};

$payLnInvoice = function($bolt11) use ($writeWebhookLog) {

    $url =  CFG::$vars['btcpay']['url'].'/api/v1/stores/'.CFG::$vars['btcpay']['store_id'].'/lightning/BTC/invoices/pay';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["BOLT11" => $bolt11]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: token ' . CFG::$vars['btcpay']['api_key']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $writeWebhookLog('lnpay_RESPONSE', "HTTP $httpCode\n\n$response");
    
    return $httpCode >= 200 && $httpCode < 300;
};

// ============================================================
// Procesar webhook
// ============================================================

if($btcpay_webhook){

    $webhook_to_redirect = $data['metadata']['webhook'] ?? null;

    if ($webhook_to_redirect) {
        $ch = curl_init($webhook_to_redirect);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Forward-Sig: sha256=' . $expectedSig
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $writeWebhookLog('webhook_FORWARD', "Forwarded to: $webhook_to_redirect\nHTTP Code: $httpCode\nResponse: $response");
    }

}else if($webhook_callback){

    
    $eventType = $data['type'] ?? '';
    $invoiceId = $data['invoiceId'] ?? '';
    $metadata  = $data['metadata'] ?? [];

    $writeWebhookLog('webhook_CALLBACK_RECEIVED', "invoiceId: $invoiceId, eventType: $eventType");

    if ($eventType === 'InvoiceSettled') {
        $authorId  = (int)($metadata['authorId'] ?? 0);
        $payerId   = (int)($metadata['userId'] ?? 0);
        $articleId = (int)($metadata['articleId'] ?? 0);
        $moduleId  = (int)($metadata['moduleId'] ?? 0);
        $amountSats = (int)($metadata['amountSats'] ?? 0);

        if ($amountSats <= 0 && isset($data['payments']) && is_array($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                if (($payment['cryptoCode'] ?? '') === 'BTC') {
                    $amountSats += (int) round(((float)($payment['amount'] ?? 0)) * 100000000);
                }
            }
        }
        
        $writeWebhookLog('webhook_CALLBACK_DATA',  "   authorId: $authorId\n" 
                                                  ."    payerId: $payerId\n"
                                                  ." amountSats: $amountSats\n"
                                                  ."   moduleId: $moduleId\n"
                                                  ."  articleId: $articleId\n"
                                                  ."  invoiceId: $invoiceId\n"
                                                  );

        // ---- Noxtr Zaps (moduleId=5): external Nostr user, no authorId ----
        if ($moduleId == 5 && !$authorId && $amountSats > 0) {
            $lnAddress = $metadata['lnAddress'] ?? '';
            if ($lnAddress) {
                $commissionSats = (int) floor($amountSats * 5 / 100);
                $authorAmountSats = $amountSats - $commissionSats;
                $paidToAuthor = false;

                $writeWebhookLog('noxtr_ZAP_ATTEMPT', "Paying $authorAmountSats sats to $lnAddress (commission: $commissionSats)");

                $bolt11 = $getLnurlInvoice($lnAddress, $authorAmountSats);
                if ($bolt11) {
                    $paidToAuthor = $payLnInvoice($bolt11);
                    if ($paidToAuthor) {
                        $writeWebhookLog('noxtr_ZAP_SUCCESS', "Paid $authorAmountSats sats to $lnAddress");
                    } else {
                        $writeWebhookLog('noxtr_ZAP_FAIL', "Failed to pay $lnAddress");
                    }
                } else {
                    $writeWebhookLog('noxtr_ZAP_NO_INVOICE', "Could not get bolt11 from $lnAddress");
                }

                // Record transaction (authorId=0 for external Nostr users)
                $sql = "INSERT INTO CLI_USER_TRANSACTIONS (from_user, to_user, transaction_type, amount_sats, commission_sats, invoice_id, module_id, article_id, payment_method, direct_payment, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                // module_id=5 (Noxtr): registrar como "Zap enviado" en lugar de retiro genérico.
                $params = [$payerId, 0, 5, $authorAmountSats, $commissionSats, $invoiceId, 5, 0, 'lightning', $paidToAuthor ? 1 : 0, time()];
                $writeWebhookLog('noxtr_ZAP_TRANSACTION', $sql . "\n" . print_r($params, true));
                Table::sqlQueryPrepared($sql, $params);
            } else {
                $writeWebhookLog('noxtr_ZAP_ERROR', "No lnAddress in metadata");
            }
        }

        if ($authorId && $amountSats > 0) {
            
            // Obtener lightning_address del autor
            $authorRow = Table::sqlQueryPrepared(
                "SELECT lightning_address FROM CLI_USER WHERE user_id = ? LIMIT 1", 
                [$authorId]
            )[0] ?? null;
            $authorLnAddress = $authorRow['lightning_address'] ?? null;
            
            // Calcular comisión (5%)
            if($moduleId == 4){ // No commission for timextamping recharges
                $commissionPercent = 0;
                $authorAmountSats = $amountSats;
            }else   {
            $commissionPercent = 5;  //FIX set in CFG
            $commissionSats = (int) floor($amountSats * $commissionPercent / 100);
            $authorAmountSats = $amountSats - $commissionSats;
            }
            
            $paidToAuthor = false;
            $paymentMethod = 'balance'; // 'balance' o 'lightning'
            $directPayment = 0;

            // Intentar pago directo si tiene lightning_address
            if (!empty($authorLnAddress) && $authorAmountSats > 0) {
                $writeWebhookLog('lnpay_ATTEMPT', "Attempting to pay $authorAmountSats sats to $authorLnAddress");
                
                $bolt11 = $getLnurlInvoice($authorLnAddress, $authorAmountSats);
                
                if ($bolt11) {
                    
                    $paidToAuthor = $payLnInvoice($bolt11);
                    
                    if ($paidToAuthor) {
                        $paymentMethod = 'lightning';
                        $writeWebhookLog('lnpay_SUCCESS', "Paid $authorAmountSats sats to $authorLnAddress");
                        $directPayment = 1; //$authorAmountSats;
                    } else {
                        $writeWebhookLog('lnpay_FAIL', "Failed to pay $authorLnAddress, using balance as fallback");
                    }
                }
            }
            
            // If not paid via Lightning, accumulate in balance
            if (!$paidToAuthor) {
                $sql = "UPDATE CLI_USER SET balance_sats = COALESCE(balance_sats, 0) + ? WHERE user_id = ?";
                $params = [$authorAmountSats, $authorId];
                $writeWebhookLog('webhook_SQL_UPDATE_USER_BALANCE', $sql."\n".print_r($params,true));
                Table::sqlQueryPrepared( $sql, $params );
            }
            
            // Register transaction
            // transaction_type: 1 = balance, 2 = direct lightning, 4 = timextamping recharge, 5 = zap enviado, 6 = zap recibido
            $transactionType =  $moduleId == 4
                ? 4
                : (
                    $moduleId == 5
                        ? ((int)$payerId > 0 ? 5 : 6)
                        : ($paymentMethod === 'lightning' ? 2 : 1)
                );

            $sql = "INSERT INTO CLI_USER_TRANSACTIONS (from_user, to_user, transaction_type, amount_sats, commission_sats, invoice_id, module_id, article_id, payment_method, direct_payment, created_at) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$payerId, $authorId, $transactionType, $authorAmountSats, $commissionSats, $invoiceId, $moduleId, $articleId, $paymentMethod, $directPayment, time()];
            $writeWebhookLog('webhook_SQL_INSERT_TRANSACTION', $sql."\n".print_r($params,true));
            Table::sqlQueryPrepared( $sql, $params );

            /***OLD
            // Register transaction
            // transaction_type: 1 = balance, 2 = direct lightning, 4 = timextamping recharge
            $transactionType =  $moduleId == 4  ? 4 : ( $paymentMethod === 'lightning' ? 2 : 1 );
            $sql = "INSERT INTO CLI_USER_TRANSACTIONS (from_user, to_user, transaction_type, amount_sats, commission_sats, invoice_id, module_id, article_id, payment_method, direct_payment, created_at) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$payerId, $authorId, $transactionType, $authorAmountSats, $commissionSats, $invoiceId, $moduleId, $articleId, $paymentMethod, $directPayment, time()];
            $writeWebhookLog('webhook_SQL_INSERT_TRANSACTION', $sql."\n".print_r($params,true));
            Table::sqlQueryPrepared( $sql, $params );
            ******/


            // Anti-farming: if a previous transaction from the same sender exists for this module+article, do not award karma.
            $shouldRewardKarma = true;
            if ($moduleId && $articleId && $payerId) {
                $sql = "SELECT COUNT(*) AS c FROM CLI_USER_TRANSACTIONS WHERE module_id = ? AND article_id = ? AND from_user = ? LIMIT 1";
                $params = [$moduleId, $articleId, $payerId];
                $writeWebhookLog('webhook_SQL_ANTI_FARMING', $sql."\n".print_r($params,true));
                $prior = Table::sqlQueryPrepared( $sql, $params );
                $shouldRewardKarma = (($prior[0]['c'] ?? 0) == 0);
            }

            if ($shouldRewardKarma) {
                if($payerId && $payerId !== $authorId)
                    Karma::rewardTipGiven($payerId, $authorAmountSats);

                Karma::rewardTipReceived($authorId, $authorAmountSats);

                $writeWebhookLog('webhook_KARMA', "Rewarded karma for tip of $authorAmountSats sats to authorId $authorId from payerId $payerId");

            }

        } else {
            $data['_processed_error'] = [
                'reason' => 'Missing authorId or amountSats',
                'authorId' => $authorId,
                'payerId' => $payerId,
                'amountSats' => $amountSats,
                'articleId' => $articleId,
                'moduleId' => $moduleId,
            ];
            $writeWebhookLog('webhook_ERROR', print_r($data,true));

        }
    }
    
}

$logContent = isset($data) ? json_encode($data, JSON_PRETTY_PRINT) : $payload;
$writeWebhookLog('webhook_END', $logContent);

http_response_code(200);
echo "Webhook received\n";
