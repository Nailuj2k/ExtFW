<?php

/**
 * Estado del backend Lightning configurado en una tienda BTCPay Server.
 *
 * `btcpay.lightning_active=false` actua como interruptor manual autoritativo.
 * Si no esta desactivado, se consulta el nodo mediante la API Greenfield. El
 * resultado se cachea para que cada peticion LNURL publica no consulte BTCPay.
 *
 * Un 401/403 se considera `unknown` y conserva el comportamiento anterior.
 * Un 404/5xx o un error de conexion se considera indisponibilidad real.
 */
final class BtcpayLightning
{
    public static function status(array $config, int $ttl = 60): array
    {
        $manual = $config['lightning_active'] ?? null;
        if (self::isExplicitlyDisabled($manual)) {
            return self::result(false, 'disabled', 'manual');
        }

        $baseUrl = rtrim(trim((string)($config['url'] ?? '')), '/');
        $storeId = trim((string)($config['store_id'] ?? ''));
        $apiKey = trim((string)($config['api_key'] ?? ''));

        // Cada consumidor ya tiene su tratamiento para configuracion ausente.
        if ($baseUrl === '' || $storeId === '' || $apiKey === '') {
            return self::result(true, 'unknown', 'not_configured');
        }

        $ttl = max(10, min(300, $ttl));
        $cacheFile = self::cacheFile($baseUrl, $storeId);
        $cached = self::readCache($cacheFile, $ttl);
        if ($cached !== null) {
            return $cached;
        }

        $result = self::probe($baseUrl, $storeId, $apiKey);
        self::writeCache($cacheFile, $result);
        return $result;
    }

    public static function configFromGlobals(): array
    {
        return [
            'url' => CFG::$vars['btcpay']['url'] ?? '',
            'store_id' => CFG::$vars['btcpay']['store_id'] ?? '',
            'api_key' => CFG::$vars['btcpay']['api_key'] ?? '',
            // null/ausente = compatibilidad hacia atras
            'lightning_active' => CFG::$vars['btcpay']['lightning_active'] ?? null,
        ];
    }

    public static function unavailableMessage(array $status): string
    {
        if (($status['state'] ?? '') === 'disabled') {
            return 'Lightning esta temporalmente desactivado por mantenimiento';
        }
        return 'Lightning esta temporalmente no disponible';
    }

    private static function isExplicitlyDisabled($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        if (is_bool($value)) {
            return !$value;
        }
        return in_array(strtolower(trim((string)$value)), ['0', 'false', 'off', 'no', 'disabled'], true);
    }

    private static function probe(string $baseUrl, string $storeId, string $apiKey): array
    {
        if (!function_exists('curl_init')) {
            return self::result(true, 'unknown', 'curl_unavailable');
        }

        $url = $baseUrl . '/api/v1/stores/' . rawurlencode($storeId) . '/lightning/BTC/info';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: token ' . $apiKey,
                'Accept: application/json',
            ],
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($ch);
        $curlError = curl_errno($ch);
        $httpStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($curlError !== 0 || $httpStatus === 0) {
            return self::result(false, 'down', 'connection_error');
        }
        if ($httpStatus >= 200 && $httpStatus < 300) {
            return self::result(true, 'up', 'api_ok');
        }
        if (in_array($httpStatus, [401, 403], true)) {
            // No confundir falta de permisos de la API key con un nodo caido.
            return self::result(true, 'unknown', 'permission_denied');
        }
        if ($httpStatus === 404) {
            return self::result(false, 'down', 'not_configured');
        }
        if ($httpStatus >= 500) {
            return self::result(false, 'down', 'api_unavailable');
        }

        // Una respuesta inesperada no debe romper instalaciones con otras
        // versiones de BTCPay al desplegar esta funcionalidad.
        return self::result(true, 'unknown', 'http_' . $httpStatus);
    }

    private static function result(bool $available, string $state, string $reason): array
    {
        return [
            'available' => $available,
            'state' => $state,
            'reason' => $reason,
            'checked_at' => time(),
        ];
    }

    private static function cacheFile(string $baseUrl, string $storeId): string
    {
        $dir = defined('SCRIPT_DIR_LOG') && is_dir(SCRIPT_DIR_LOG)
            ? SCRIPT_DIR_LOG
            : sys_get_temp_dir();
        return rtrim($dir, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'btcpay_lightning_health_' . hash('sha256', $baseUrl . '|' . $storeId) . '.json';
    }

    private static function readCache(string $file, int $ttl): ?array
    {
        if (!is_file($file) || (time() - (int)@filemtime($file)) >= $ttl) {
            return null;
        }
        $data = json_decode((string)@file_get_contents($file), true);
        if (!is_array($data) || !isset($data['available'], $data['state'], $data['reason'])) {
            return null;
        }
        $data['available'] = (bool)$data['available'];
        return $data;
    }

    private static function writeCache(string $file, array $result): void
    {
        @file_put_contents($file, json_encode($result), LOCK_EX);
    }
}
