<?php

/**
 * Security Layer - ExtFW 3.0
 *
 * Multi-layer security validation system
 * All validations are executed (not using else-if chains)
 *
 * @version 2.0
 * @date 2026-01-21
 */

declare(strict_types=1);

// ============================================
// LAYER 1: HTTPS Enforcement
// ============================================
// Nota: Si está configurado en Apache/Nginx esto es redundante,
// pero sirve como red de seguridad por si hay mala configuración
if (PHP_SAPI !== 'cli' && $cfg['ssl'] && empty($_SERVER['HTTPS'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// ============================================
// LAYER 2: Request URI Validation
// ============================================
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// Bloquear .php en URLs (excepto index.php que es interno)
// Esto previene acceso directo a archivos PHP
if (strpos($request_uri, '.php') !== false) {
    SecurityValidator::logAndBlock('php_in_url', $request_uri);
}

// Bloquear path traversal (../)
if (strpos($request_uri, '../') !== false || strpos($request_uri, '..\\') !== false) {
    SecurityValidator::logAndBlock('path_traversal', $request_uri);
}

// Bloquear URLs con patrones sospechosos específicos
$suspicious_patterns = [
    '?url=https' => 'open_redirect',
    '/gl/' => 'suspicious_path',
];

foreach ($suspicious_patterns as $pattern => $type) {
    if (strpos($request_uri, $pattern) !== false) {
        SecurityValidator::logAndBlock($type, $request_uri);
    }
}

// Detectar SQL injection básico en URL
$sql_keywords = ['ORDER BY', 'UNION SELECT', 'INSERT INTO', 'DELETE FROM', 'DROP TABLE', 'UPDATE SET'];
foreach ($sql_keywords as $keyword) {
    if (stripos($request_uri, $keyword) !== false) {
        SecurityValidator::logAndBlock('sql_injection_in_url', $request_uri);
    }
}

// Detectar XSS básico en URL
$xss_patterns = ['<script', 'javascript:', 'onerror=', 'onload=', 'eval('];
foreach ($xss_patterns as $pattern) {
    if (stripos($request_uri, $pattern) !== false) {
        SecurityValidator::logAndBlock('xss_in_url', $request_uri);
    }
}

// ============================================
// LAYER 3: POST Data Validation
// ============================================
if ($_POST && isset($_POST['url'])) {
    $url_value = $_POST['url'];

    // SQL injection en POST data
    foreach ($sql_keywords as $keyword) {
        if (stripos($url_value, $keyword) !== false) {
            SecurityValidator::logAndBlock('sql_injection_in_post', $url_value);
        }
    }

    // Detectar patrones sospechosos adicionales
    $attacks = SecurityValidator::detectSuspiciousPatterns($url_value);
    if (!empty($attacks)) {
        SecurityValidator::logAndBlock('suspicious_post_data', [
            'field' => 'url',
            'value' => $url_value,
            'attacks' => $attacks
        ]);
    }
}

// ============================================
// LAYER 4: Protección de Checkout (solo POST)
// ============================================
$checkout_patterns = [
    'products/checkout/order',
    'tienda/checkout/order',
    'shop/checkout/order',
];

foreach ($checkout_patterns as $pattern) {
    if (strpos($request_uri, $pattern) !== false && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        SecurityValidator::logAndBlock('checkout_without_post', $request_uri);
    }
}

// ============================================
// LAYER 5: PHPIDS (opcional, configurable)
// ============================================
if (CFG::$vars['module_security'] === 'phpids') {
    // Solo cargar en módulos específicos si está configurado
    $phpids_modules = CFG::$vars['security']['phpids_modules'] ?? [];

    // Si no hay lista de módulos, o si el módulo actual está en la lista
    $current_module = explode('/', $request_uri)[1] ?? '';

    if (empty($phpids_modules) || in_array($current_module, $phpids_modules)) {
        include(SCRIPT_DIR_LIB . '/phpids/index.php');
    }
}



/* =============================================
 * CÓDIGO ANTIGUO (COMENTADO PARA REFERENCIA)
 * ============================================
 *
 * Problemas del código anterior:
 * 1. Usaba else-if en cadena: solo se ejecutaba UNA validación
 * 2. SQL injection detection case-sensitive y fácil de bypassear
 * 3. Redirecciones extrañas a 127.0.0.1
 * 4. Variables $message_warning y $error sin usar
 * 5. Sin logging de ataques
 *
 * ============================================

if ($cfg['ssl'] && empty($_SERVER['HTTPS']) ) {
    header('Location: https://'.$_SERVER['SERVER_NAME']);
    exit;
}

// Sometimes sites like bing, google, etc., called site adding parameters
// to url. These parameter could not be complaint with site url policies
// and can produce mistakes, pages not found, etc. Here we detects these
// url's and clean or sanitize it. We not works with google.
// If you, webmaster loves this big brother bussines can freely modify
// this lines to suck google ass.

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (strpos($referer,'google.')) {           // https://accounts.google.com/
    $referer = '';
}else if (strpos($_SERVER['REQUEST_URI'],'?')){   // ?gclid  ?fb_comment_id
    $aUrl = explode('?',$_SERVER['REQUEST_URI']);
    Header('Location: '.$aUrl[0]);
}else if ($_POST && isset($_POST['url'])){   //
    $p = $_POST['url'];
    if (strpos($p,'ORDER BY')||strpos($p,'UNION')||strpos($p,'SELECT')||strpos($p,'/gl/')) {   //
        Header('Location: https://127.0.0.1');
    }
}else if (strpos($_SERVER['REQUEST_URI'],'?url=https')){   //
    Header('Location: https://127.0.0.1');
}else if (strpos($_SERVER['REQUEST_URI'],'gl/')){   //
    Header('Location: https://127.0.0.1');
}else if ( (strpos($_SERVER['REQUEST_URI'],'&')) || (strpos($_SERVER['REQUEST_URI'],'.php')) )  {
    $message_warning = 'Url con errores';
    $error = true;
}else if (strpos($_SERVER['REQUEST_URI'],'&')){
    Header('Location: /');
}else if (strpos($_SERVER['REQUEST_URI'],'products/checkout/order')&&(!$_POST)){
    $message_warning = 'Url not post';
    Header('Location: /');
}else if (strpos($_SERVER['REQUEST_URI'],'tienda/checkout/order')&&(!$_POST)){
    $message_warning = 'Url not post';
    Header('Location: /');
}else if (strpos($_SERVER['REQUEST_URI'],'shop/checkout/order')&&(!$_POST)){
    $message_warning = 'Url not post';
    Header('Location: /');
}


// si $prefix == false es que no tenemos configurado el sistema
// así que no usaremos phpids, kses, o lo que hubiera en module_security
// mientras dure el proceso de configuración.

//if ($cfg['prefix'] ){
//    if ($_SESSION['userlevel']>500){
//
//    }else{
        if(CFG::$vars['module_security']=='phpids')
            include(  SCRIPT_DIR_LIB.'/phpids/index.php' );
//    }
//}

 * ============================================
 * FIN DEL CÓDIGO ANTIGUO
 * ============================================ */
