<?php


define('BTCPAY_URL',              CFG::$vars['btcpay']['url']); // Sin barra final
define('BTCPAY_API_KEY',         '14482ef8972a89d3c370480333c9280e066c63a4'); // CFG::$vars['btcpay']['api_key']);
define('BTCPAY_STORE_ID',         CFG::$vars['btcpay']['store_id']);

// Configuración de formato de moneda fiat
define('WALLET_FIAT_LOCALE',      CFG::$vars['btcpay']['fiat_locale'] ?? 'es-ES');
define('WALLET_FIAT_CURRENCY',    CFG::$vars['btcpay']['fiat_currency'] ?? 'EUR');

// Bitcoin Core RPC Configuration (via nginx proxy)
// IMPORTANTE: La URL incluye /wallet/timestamping para usar el wallet correcto
define('BITCOIN_RPC_URL',         CFG::$vars['btcpay']['rpc_url']);
define('BITCOIN_RPC_USER',        CFG::$vars['btcpay']['rpc_user']);
define('BITCOIN_RPC_PASSWORD',    CFG::$vars['btcpay']['rpc_password']);












// Función auxiliar para conectar con la API
function btcpay_request($endpoint, $method = 'GET', $data = null) {
    $url = BTCPAY_URL . '/api/v1/' . $endpoint;
    $headers = [
        'Authorization: token ' . BTCPAY_API_KEY,
        'Content-Type: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Si hubo un error de cURL
    if ($curl_error) {
        LOG::$messages['after_init_'.__LINE__] = "BTCPay cURL Error: " . $curl_error;
        return ['status' => 0, 'data' => null, 'error' => $curl_error];
    }

    // Decodificar respuesta JSON
    $decoded = json_decode($response, true);

    // Si no se pudo decodificar el JSON
    if ($decoded === null && $response !== 'null') {
        LOG::$messages['after_init_'.__LINE__] = "BTCPay JSON Decode Error. Raw response: " . $response;
        return ['status' => $status, 'data' => null, 'error' => 'Invalid JSON response', 'raw' => $response];
    }

    return ['status' => $status, 'data' => $decoded, 'error' => null];
}


// Función para hacer llamadas RPC a Bitcoin Core
function bitcoin_rpc($method, $params = []) {
    $request = [
        'jsonrpc' => '2.0',
        'id' => time(),
        'method' => $method,
        'params' => $params
    ];

    $ch = curl_init(BITCOIN_RPC_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
    curl_setopt($ch, CURLOPT_USERPWD, BITCOIN_RPC_USER . ':' . BITCOIN_RPC_PASSWORD);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        LOG::$messages['after_init_'.__LINE__] = "Bitcoin RPC Error: " . $curl_error;
        return ['error' => $curl_error, 'result' => null];
    }

    $decoded = json_decode($response, true);

    if (isset($decoded['error']) && $decoded['error'] !== null) {
        LOG::$messages['after_init_'.__LINE__] = "Bitcoin RPC returned error: " . json_encode($decoded['error']);
        return ['error' => $decoded['error'], 'result' => null];
    }

    return ['error' => null, 'result' => $decoded['result'] ?? null];
}
