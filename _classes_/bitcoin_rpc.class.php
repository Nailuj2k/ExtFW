<?php
// bitcoin_rpc.class.php
// Cliente JSON-RPC para Bitcoin Core + fallback a mempool.space. Genericos, sin
// dependencia de modulos: cualquier modulo que necesite hablar con el nodo o
// con mempool.space puede llamar a BitcoinRpc::call() / MempoolApi::get() sin
// acoplarse a otro modulo (p.ej. timextamping mantiene su propio helper).
//
// Configuracion: invocar BitcoinRpc::init($url, $user, $pass) una vez por
// request (tipicamente en after_init.php del modulo) leyendo CFG::$vars.
// Si MempoolApi necesita una base distinta (mainnet/testnet/instancia propia),
// usar MempoolApi::setBase('https://mempool.space/api').

class BitcoinRpc {
    private static $url  = '';
    private static $user = '';
    private static $pass = '';
    private static $timeout = 30;

    static function init($url, $user = '', $pass = '', $timeout = 30) {
        self::$url     = (string)$url;
        self::$user    = (string)$user;
        self::$pass    = (string)$pass;
        self::$timeout = max(1, (int)$timeout);
    }

    static function isConfigured() {
        return self::$url !== '';
    }

    // Realiza una llamada JSON-RPC al nodo. Devuelve ['error' => null|mixed, 'result' => mixed].
    static function call($method, $params = []) {
        if (!self::isConfigured()) {
            return ['error' => 'rpc_not_configured', 'result' => null];
        }
        $request = [
            'jsonrpc' => '2.0',
            'id'      => time(),
            'method'  => $method,
            'params'  => $params,
        ];
        $ch = curl_init(self::$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        if (self::$user !== '') {
            curl_setopt($ch, CURLOPT_USERPWD, self::$user . ':' . self::$pass);
        }
        $response   = curl_exec($ch);
        $curlError  = curl_error($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return ['error' => $curlError, 'result' => null, 'http_status' => $httpStatus];
        }
        $decoded = json_decode($response, true);
        if ($decoded === null) {
            return ['error' => 'invalid_json', 'result' => null, 'http_status' => $httpStatus, 'raw' => $response];
        }
        if (isset($decoded['error']) && $decoded['error'] !== null) {
            return ['error' => $decoded['error'], 'result' => null, 'http_status' => $httpStatus];
        }
        return ['error' => null, 'result' => $decoded['result'] ?? null, 'http_status' => $httpStatus];
    }
}

class MempoolApi {
    private static $base = 'https://mempool.space/api';
    private static $timeout = 10;

    static function setBase($url) {
        $url = rtrim((string)$url, '/');
        if ($url !== '') self::$base = $url;
    }

    static function setTimeout($seconds) {
        self::$timeout = max(1, (int)$seconds);
    }

    static function base() {
        return self::$base;
    }

    // GET a un endpoint relativo de la API. Devuelve [http_code, body_string, decoded_json_or_null].
    // No lanza excepciones: la decision de tratar errores la deja al llamador.
    static function get($path) {
        $url = self::$base . '/' . ltrim((string)$path, '/');
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $body   = curl_exec($ch);
        $code   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr   = curl_error($ch);
        curl_close($ch);

        if ($cerr) return ['code' => 0, 'body' => '', 'json' => null, 'error' => $cerr];

        $json = null;
        if ($body !== '' && $body !== false) {
            $decoded = json_decode($body, true);
            $json = is_array($decoded) ? $decoded : null;
        }
        return ['code' => $code, 'body' => (string)$body, 'json' => $json, 'error' => null];
    }

    // POST a un endpoint relativo. Para mempool.space /tx el body es el raw tx hex (text/plain) y la
    // respuesta es el txid (200) o un mensaje de error (400). Devuelve [code, body_string, error].
    static function post($path, $body, $contentType = 'text/plain') {
        $url = self::$base . '/' . ltrim((string)$path, '/');
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, (string)$body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: ' . $contentType]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($cerr) return ['code' => 0, 'body' => '', 'error' => $cerr];
        return ['code' => $code, 'body' => (string)$resp, 'error' => null];
    }
}
