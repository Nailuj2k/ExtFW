<?php

/**
 * Security Validator Class
 *
 * Provides security validation methods for ExtFW 3.0
 *
 * @author Julián Torres
 * @version 1.0
 */

class SecurityValidator {

    /**
     * Registra ataque y bloquea la petición
     *
     * @param string $type Tipo de ataque detectado
     * @param mixed $data Datos relacionados con el ataque
     * @return never
     */
    public static function logAndBlock(string $type, $data = null): never {

        // Log del ataque
        if (CFG::$vars['log']) {
            LOG::security($type, $data);
        }

        // Bloquear request
        http_response_code(403);

        if (CFG::$vars['debug']) {
            die('Security violation: ' . htmlspecialchars($type));
        }

        // En producción mostrar mensaje genérico o página 403
        if (file_exists(SCRIPT_DIR_MODULES . '/403/run.php')) {
            include SCRIPT_DIR_MODULES . '/403/run.php';
            exit;
        }

        die('Access denied');
    }

    /**
     * Sanitiza string para prevenir XSS
     *
     * @param string $value String a sanitizar
     * @return string String sanitizado
     */
    public static function sanitizeString(string $value): string {
        // Remover null bytes
        $value = str_replace("\0", '', $value);

        // Decodificar entidades HTML múltiples veces (ataques de doble encoding)
        $previous = '';
        $iterations = 0;
        while ($value !== $previous && $iterations < 5) {
            $previous = $value;
            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            $iterations++;
        }

        return $value;
    }

    /**
     * Valida token CSRF
     *
     * @param string $received_token Token recibido del cliente
     * @return bool True si el token es válido
     */
    public static function validateCsrfToken(string $received_token): bool {
        // Soportar ambos nombres de token para compatibilidad
        $session_token = $_SESSION['csrf_token'] ?? $_SESSION['token'] ?? '';

        if (empty($session_token) || empty($received_token)) {
            return false;
        }

        // Usar hash_equals para prevenir timing attacks
        return hash_equals($session_token, $received_token);
    }

    /**
     * Detecta patrones sospechosos en un string
     *
     * @param string $value String a analizar
     * @return array Array con tipos de ataques detectados
     */
    public static function detectSuspiciousPatterns(string $value): array {
        $detected = [];

        // SQL Injection patterns
        $sql_patterns = [
            '/(\bSELECT\b.*\bFROM\b)/i',
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bOR\s+\d+\s*=\s*\d+)/i',
            '/(--|\#|\/\*|\*\/)/i',
        ];

        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $detected[] = 'sql_injection';
                break;
            }
        }

        // XSS patterns
        $xss_patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/eval\s*\(/i',
        ];

        foreach ($xss_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $detected[] = 'xss';
                break;
            }
        }

        // Path traversal
        if (strpos($value, '../') !== false || strpos($value, '..\\') !== false) {
            $detected[] = 'path_traversal';
        }

        // Command injection
        if (preg_match('/[;&|`$()]/', $value)) {
            $detected[] = 'command_injection';
        }

        return $detected;
    }

    /**
     * Limpia parámetros de tracking de una URL
     *
     * @param string $uri URI a limpiar
     * @return string URI limpia o la misma si no había tracking params
     */
    public static function cleanTrackingParams(string $uri): string {
        $tracking_params = [
            'gclid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign',
            'utm_term', 'utm_content', 'fb_comment_id', '_ga', 'mc_cid', 'mc_eid'
        ];

        if (strpos($uri, '?') === false) {
            return $uri;
        }

        $parts = explode('?', $uri, 2);
        $path = $parts[0];
        parse_str($parts[1] ?? '', $query_params);

        $cleaned = false;
        foreach ($tracking_params as $param) {
            if (isset($query_params[$param])) {
                unset($query_params[$param]);
                $cleaned = true;
            }
        }

        if (!$cleaned) {
            return $uri;
        }

        $new_query = http_build_query($query_params);
        return $path . ($new_query ? '?' . $new_query : '');
    }
}
