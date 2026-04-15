<?php

    define('NL',"\n"); //"\n");

    // ============================================
    // Limpieza de tracking params (Google, Facebook, etc.)
    // ============================================
    // Esto se hace ANTES de parsear la URL para evitar que
    // ensucien el sistema de routing

    $tracking_params = [
        'gclid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign',
        'utm_term', 'utm_content', 'fb_comment_id', '_ga', 'mc_cid', 'mc_eid'
    ];

    $_REQUEST_URI = $_SERVER['REQUEST_URI']; 

    // Ejemplo de cómo manejar .well-known
    if (strpos( $_REQUEST_URI, '/.well-known/') === 0) {

         $_REQUEST_URI = str_replace(['.well-known/nostr.json?name=',
                                      '.well-known/lnurlp/',
                                      '&','?'],
                                     ['noxtr/raw/wellknown/action=nostr.json/name=',
                                      'noxtr/raw/wellknown/action=lnurlp/name=',
                                      '/','/'],
                                     $_REQUEST_URI);

        // /.well-known/lnurlp/jts27n?amount=1000000&nostr=XXXX&comment=YYYY
        // amount — cantidad en millisatoshis (1000000 = 1000 sats)
        // nostr — JSON URL-encoded de un evento NIP-57 "zap request" (kind 9734). Ejemplo codificado: %7B%22kind%22%3A9734%2C%22pubkey%22%3A%22abc...%22%2C...%7D
        // comment — texto libre opcional (máx 255 chars según tu config)

        // .well-known/lnurlp/jts27n?amount=1000000&nostr=XXXX&comment=YYYY
        // se convierte en
        // $_ARGS[0] => noxtr
        // $_ARGS[1] => raw
        // $_ARGS[2] => wellknown
        // $_ARGS[output] => raw
        // $_ARGS[action] => lnurlp
        // $_ARGS[name] => jts27n
        // $_ARGS[amount] => 1000000
        // $_ARGS[nostr] => XXXX
        // $_ARGS[comment] => YYYY

        // hemos convertido url como
        // miweb.com/.well-known/nostr.json?name=pepe   en
        // miweb.com/noxtr/raw/wellknown/action=nostr.json/name=pepe
        // y url como
        // miweb.com/.well-known/lnurlp/pepe  en
        // miweb.com/noxtr/raw/wellknown/action=lnurlp/name=pepe
        // de esta forma, el módulo noxtr puede manejar estas urls especiales sin necesidad
        // de configurar el servidor para redirigirlas a un handler específico.
        // y además serán procesadas por el output raw, que no carga el html del theme y
        // devuelve solo el contenido generado por el módulo, ideal para este tipo de endpoints de API.
        // NOTA: Si el módulo noxtr no estuviera activo, esta URL no harían nda de nada, o sí: un 404
    }


    if (strpos( $_REQUEST_URI, '?') !== false) {
        $parts = explode('?',  $_REQUEST_URI, 2);
        $path = $parts[0];
        parse_str($parts[1] ?? '', $query_params);

        $cleaned = false;
        foreach ($tracking_params as $param) {
            if (isset($query_params[$param])) {
                unset($query_params[$param]);
                $cleaned = true;
            }
        }

        // Si se limpió algo, redirigir a URL limpia
        if ($cleaned) {
            $new_query = http_build_query($query_params);
            $new_uri = $path . ($new_query ? '?' . $new_query : '');
            header('Location: ' . $new_uri, true, 301);
            exit;
        }
    }

    // ============================================
    // Parseo de URL en argumentos
    // ============================================

    if(SCRIPT_DIR){
        $_ARGS = explode(  '/' , str_replace(SCRIPT_DIR.'/', '',  $_REQUEST_URI) );
    }else{
        $_ARGS = explode(  '/' ,   $_REQUEST_URI );
        for ($i = 0; $i < count($_ARGS); $i++) { $_ARGS[$i] = $_ARGS[($i+1)]??'';}
    }

    // $host = $_SERVER['HTTP_HOST'];
    // $subdomain = explode('.', $host)[0];
 
    foreach($_ARGS as $k => $v){

      //if (is_string($v) && strpos($v, '=') !== false) {
        if (strpos($v??'','=')) {
            $_ARGS[ substr($v, 0, strpos($v, '='))] = substr($v, strpos($v, '=') + 1); // make associative args element if format is 'name=value'
            unset($_ARGS[$k]);  //remove numeric key
        }

        if(count($cfg['langs'])>1){       //FIX
            if ($v=='lang' && (in_array($_ARGS[$k+1],CFG::$vars['langs'])) ){ 
                $_ARGS['lang'] = $_ARGS[$k+1]; 
            }else if (in_array($v,CFG::$vars['langs'])) {
                $_ARGS['lang'] = $v;
            }
            $private_url = false;
        }    

        if (CFG::$vars['enable_themes']){ 
            if ($v=='theme' &&  $_ARGS[$k+1]=='reset')$_ARGS[$k+1]=CFG::$vars['default_theme'];
            if ($v=='theme' /*&& (count(CFG::$vars['themes'])<2 || (in_array($_ARGS[$k+1],CFG::$vars['themes'])))*/) { 
                $_ARGS['theme'] = $_ARGS[$k+1]; 
                $private_url = false;
            //}else if (in_array($v,CFG::$vars['themes'])) {
              //  $_ARGS['theme'] = $v;
            }
        }else $_ARGS['theme']=CFG::$vars['default_theme'];

        if ($v=='   de 17 a output' && (in_array($_ARGS[$k+1],CFG::$vars['outputs'])) ){ 
            $_ARGS['output'] = $_ARGS[$k+1]; 
        }else if (in_array($v,CFG::$vars['outputs'])) {
            $_ARGS['output'] = $v;
        }
    
        if ($v=='debug') $_ARGS['debug'] = true;
    
        if(empty($v)) unset($_ARGS[$k]);

    }

   // $_ARGS = array_merge( $_ARGS, $_REQUEST );
    $_ARGS = array_merge( $_ARGS, $_POST );        // add POST data to $_ARGS

    // unset _GET && _POST here to avoid conflicts?
    // unset($_GET);
    // unset($_POST

    session_name('session_jux_'.CFG::get('prefix'));
    ini_set('session.gc_maxlifetime', 3*24*60*60);
    /*
    $cookie_secure = true; // if you only want to receive the cookie over HTTPS
    $cookie_httponly = true; // prevent JavaScript access to session cookie
    $cookie_samesite = 'Lax'; //'lax';
    $cookie_maxlifetime = 3*24*60*60;

    if(PHP_VERSION_ID < 70300) {
        session_set_cookie_params($cookie_maxlifetime, '/; samesite='.$cookie_samesite, $_SERVER['HTTP_HOST'], $cookie_secure, $cookie_httponly);
    } else {
        session_set_cookie_params([
            'lifetime' => $cookie_maxlifetime,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $cookie_secure,
            'httponly' => $cookie_httponly,
            'samesite' => $cookie_samesite
        ]);
    }
    */

    session_start();
    
    //$_SESSION['translate']=false;
    Vars::setDefaultSessionVar('translate'  ,false );
    Vars::setDefaultSessionVar('valid_user'  ,false );
    Vars::setDefaultSessionVar('userid'  ,false );
    Vars::setDefaultSessionVar('username'  ,false );
    Vars::setDefaultSessionVar('user_score'  , 0 );
    Vars::setDefaultSessionVar('auth_provider' , false );
    Vars::setDefaultSessionVar('theme'  ,CFG::$vars['default_theme'] );
    Vars::setDefaultSessionVar('module' ,CFG::$vars['default_module']);
    $nav_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    Vars::setDefaultSessionVar('lang'   ,in_array($nav_lang,CFG::$vars['langs']) ? $nav_lang: CFG::$vars['default_lang']  ); 
    if(!isset($_ARGS['lang'])&&$_SESSION['lang']!==CFG::$vars['default_lang']) {
     //if(news || page ...  //translatable item that must have a suffix
     //lang     if (in_array( MODULE, ['products','news','page']))
     //lang     $_ARGS['lang']=CFG::$vars['default_lang']; //CFG::$vars['default_lang']
     //unset($_ARGS['lang']);
    }
    
    if (isset($_ARGS['lang']))
        Vars::setSessionVar('lang', $_ARGS['lang'] ?? CFG::$vars['default_lang']  ); //CHECK

    // ============================================
    // Generación de token CSRF (mejorado)
    // ============================================
    // Se genera una vez por sesión con random_bytes (más seguro que md5)
    // Se mantiene compatibilidad con código existente que usa 'token'

    // Si el token es viejo (32 chars = MD5), regenerarlo
    if(!isset($_SESSION['csrf_token']) || strlen($_SESSION['csrf_token']) < 64){
        // Token criptográficamente seguro
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64 caracteres hex
    }

    // Mantener compatibilidad con código que usa $_SESSION['token']
    // Siempre sincronizar con csrf_token
    $_SESSION['token'] = $_SESSION['csrf_token'];

    // ============================================
    // Validación CSRF centralizada
    // ============================================
    // IMPORTANTE: Se ejecuta DESPUÉS de session_start() y generación del token
    // Se ejecuta automáticamente para TODOS los POST
    // wQuery.js añade el token automáticamente

    // SSE ajax usa autenticación por dominio (tabla SSE_DOMAINS), no por sesión/CSRF
    $_skip_csrf = strpos($_REQUEST_URI, '/sse/ajax') !== false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST) && !$_skip_csrf) {
        // Obtener token recibido (soporta ambos nombres para compatibilidad)
        $token_received = $_POST['csrf_token'] ?? $_POST['token'] ?? '';

        // Validar token
        if (!SecurityValidator::validateCsrfToken($token_received)) {
            SecurityValidator::logAndBlock('csrf_validation_failed', [
                'uri' =>  $_REQUEST_URI,
                'post_keys' => array_keys($_POST)
            ]);
        }


    }

    if(in_array($_ARGS[0]??[],array_merge(['theme','lang','output'/*,'debug'*/],CFG::$vars['outputs']))) unset($_ARGS[0]); //=false;

    if (isset($_ARGS['theme']))  Vars::setSessionVar('theme', $_ARGS['theme']);

    $arg_0 = Vars::getArrayVar($_ARGS, 0);
    if($arg_0){   
        if (!file_exists(SCRIPT_DIR_MODULES.'/'.$arg_0)) {   // Is not MODULE
            if (in_array($arg_0,array_merge(['theme','lang'],CFG::$vars['outputs']))) {    
                // All is ok
            }else if (  in_array(CFG::$vars['default_module'],array('page','blog','news','products','tienda','shop'))  ) { //FIX  check for 'page','blog', or any entries based module
                //if( is_array($items_translated[$_SESSION['lang']]) && count($items_translated[$_SESSION['lang']])>0 && in_array( $arg_0,array_keys($items_translated[$_SESSION['lang']]))){
                //    $arg_0=$items_translated[$_SESSION['lang']][$arg_0];
                //    $_ARGS[0] = $arg_0;
                //}else{
                    $_ARGS[0] = CFG::$vars['default_module'];    // Si no existe el primer argumento ponemos default_module como primero
                    if(isset($_ARGS[1])) $_ARGS[2] = $_ARGS[1];  // offset if exists 
                    $_ARGS[1] = $arg_0;                          // así, si el default module fuera, por ejemplo, blog, podriamos resolver 
                //}                                              // url's como server.com/nombredeunaentrada al convertir esta en
            }                                                    //
        }
    }else{
       $_ARGS[0]=CFG::$vars['default_module']; 
       $_ARGS[1]=CFG::$vars['default_page']??false; // false; 
    }

    define('MODULE'            , Vars::getArrayVar($_ARGS, 0, CFG::$vars['default_module']) );
    define('SCRIPT_DIR_MODULE' , SCRIPT_DIR_MODULES.'/'.MODULE );  
    define('OUTPUT'            , $_ARGS['output'] ??  false             );
    define('THEME'             , $_ARGS['theme']  ??  $_SESSION['theme']);
    define('SCRIPT_DIR_THEME'  , SCRIPT_DIR_THEMES.'/'.THEME );  
    define("MODULE_SYSTEM"     , 1 );
    define("MODULE_MODULE"     , 2 );
    define("MODULE_HTML"       , 3 );
    define("MODULE_URL"        , 4 );
    define("MODULE_IFRAME"     , 5 );
    define("MODULE_SEPARATOR"  , 6 );

    $documents = false;
    $files = false;
    $gallery = false;
    $form_css = false;

    if($cfg['db']['cache']){

        if(CFG::$vars['db']['log']){
            $log_id = date('Ymd_h').'_'.$_SESSION['token'];
            $log = new Persistent(CACHE_DIR.'/log_'.$log_id.'.log');
            $log->open();
            if(!$log->data) ($log->data = array());
        }
    }

    include(SCRIPT_DIR_I18N.'/'.$_SESSION['lang'].'.php');
    if (!function_exists('t')) { function t($s){ return $s;} }
    include(SCRIPT_DIR_INCLUDES.'/auth.php');
    Hook::do_action('after_auth');          // usuario autenticado, sesión lista

    //include(SCRIPT_DIR_CLASSES.'/inflect.'.$_SESSION['lang'].'.class.php');
    include(SCRIPT_DIR_CLASSES.'/images.class.php' );

    APP::setArgs($_ARGS);
    APP::$plugins->load_plugins();
    Hook::do_action('plugins_loaded');      // todos los plugins cargados
    
    Karma::$enabled = true;
    Karma::$maxStars = 5; 

    $_ACL = new ACL();
    $juxACL = $_ACL;  // backwards-compat — eliminar tras migración completa
    Hook::do_action('acl_ready');           // ACL inicializado, roles y permisos disponibles
    //  if(isset($_SESSION['valid_user'])) $_ACL->buildACL();
    
    $db_engine = 'crud';
    define('DB_ENGINE',$db_engine);
                
    if(!OUTPUT){
        include( SCRIPT_DIR_THEME   . '/init.php' );
        Hook::do_action('after_theme_init');    // theme cargado, variables de layout disponibles
    }

    include( SCRIPT_DIR_MODULE   . '/init.php' );
    include( SCRIPT_DIR_CLASSES  . '/' . $db_engine . '/init.php');
    Hook::do_action('after_module_init');       // módulo inicializado, $db_engine listo
   
    define('VERSION', CFG::$vars['script']['version']);  
    
    ///CFG::$vars['captcha']['enabled']=false;
    if(isset($after_init) && $after_init) include( SCRIPT_DIR_MODULE  . '/after_init.php' );  //FIX move down?
    
    include( SCRIPT_DIR_INCLUDES . '/security_headers.php');
  
    $private_url = in_array( $_ARGS[0], array('control_panel','lang','theme','login') ) ? true : $private_url;

    define('ITEM_NAME', $_ARGS[0] == CFG::$vars['default_module']?$_ARGS[1]:$_ARGS[0] );
    define('USE_CDN',CFG::$vars['options']['use_cdn']??true);
    define('CDN_URL',CFG::$vars['options']['cdn_url']??false);

    if(!OUTPUT){

        if (!is_dir(SCRIPT_DIR_MEDIA)/* && !@mkdir(SCRIPT_DIR_MEDIA, 0777, true)*/)
            Messages::error('The directory '.SCRIPT_DIR_MEDIA.' is invalid.',false,30000);

        if (!is_writable(SCRIPT_DIR_MEDIA))
            Messages::error('The directory '.SCRIPT_DIR_MEDIA.' is not writable',false,30000);

    }

    Hook::do_action('before_output');           // justo antes de renderizar (theme o output handler)
    include( (OUTPUT?SCRIPT_DIR_OUTPUTS:SCRIPT_DIR_THEMES) . '/' . (OUTPUT?OUTPUT:THEME) . '/index.php' );
    Hook::do_action('after_output');            // justo después de renderizar

    if(CFG::$vars['db']['log']) $log->save(); 
    
    if(CFG::$vars['log'])       LOG::write(); 

    $_SESSION['message_error']=false;
    $_SESSION['message_info']=false;
