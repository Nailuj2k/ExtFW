<?php

Class Errors{

        public static function print_vars(){
            return '';
            /**
            return '<pre>'
                  .     '         SCRIPT_DIR: '.SCRIPT_DIR."\n"
                  .     '             MODULE: '.MODULE."\n"
                  .     ' SCRIPT_DIR_MODULES: '.SCRIPT_DIR_MODULES."\n"
                  .     ' SCRIPT_DIR_CLASSES: '.SCRIPT_DIR_CLASSES."\n"
                  .     'SCRIPT_DIR_INCLUDES: '.SCRIPT_DIR_INCLUDES."\n"
                  .     '  SCRIPT_DIR_MODULE: '.SCRIPT_DIR_MODULE."\n"
                  .     '  SCRIPT_DIR_THEMES: '.SCRIPT_DIR_THEMES."\n"
                  .     '              THEME: '.THEME."\n"
                  .     '             $_ARGS: '.print_r($_ARGS,true)."\n"
                  .     '          $_SESSION: '.print_r($_SESSION,true)."\n"
                //.     '          $_REQUEST: '.print_r($_REQUEST,true)
                  .'</pre>';  
            */
        }

        public static function HandleError($errno, $errstr, $errfile, $errline) {
          //$_SESSION['message_error'] = $errno.','.$errstr.','.$errfile.','.$errline;
          switch ($errno) {
            case E_USER_ERROR:
              Messages::error(  'USER_ERROR<br>'.$errno.','.$errstr.','.$errfile.','.$errline );  
              break;
            case E_WARNING:
              if(CFG::get('production')==true){
                //echo 'error';
              }else{
                //Messages::warning( 'WARNING<br>'.$errno.','.$errstr.','.$errfile.','.$errline, false, 414000);  //.' '.print_vars() 
              }
              break;
            case E_USER_WARNING:
              if(CFG::get('production')==true){
                //echo 'error';
              }else{
                 Messages::warning( 'USER_WARNING<br>'.$errno.','.$errstr.','.$errfile.','.$errline);  
              }
              break;
            case E_NOTICE:
            case E_USER_NOTICE:
              if(CFG::get('production')==true){
                //echo 'error';
              }else{
                // Messages::alert( 'NOTICE<br>'.$errno.','.$errstr.','.$errfile.','.$errline); //.' '.print_vars() );  
              }
              break;
            case 8192: //deprecated
              break;
            default:
              Messages::danger(   'DEFAULT<br>'.   $errno.','.$errstr.','.$errfile.','.$errline );  
              break;
          }
        }  // function HandleError($errno, $errstr, $errfile, $errline)
        
        public static function SetErrorLevel($level){
          if( $level==0){
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('error_reporting', 0);
          }else{
            error_reporting($level);
            ini_set('display_errors', 1);
            ini_set('error_reporting', $level); // ^ E_NOTICE ); // 1 E_ALL);
            //error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Mostrar todos los errores excepto warnings y notices
            //ini_set('display_errors', 1); // Mostrar errores en pantalla
            //ini_set('error_reporting', E_ALL & ~E_WARNING & ~E_NOTICE); // Aplicar configuración de error_reporting             
          }
          return true;
        }
      
}  // Class

Errors::SetErrorLevel(ERROR_LEVEL);
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
$old_error_handler = set_error_handler(array('Errors','HandleError'));

register_shutdown_function('errorHandler');

function errorHandler() { 
    $error = error_get_last();
    $type = $error['type'];
    $message = $error['message'];
    if ($type == 64 && !empty($message)) {
        echo '
            <div style="margin-top:25px;color:red;font-weight:500;">
              Fatal error captured:
            </div>
        ';
        echo "<pre>";
        Vars::debug_var($error);
        echo "</pre>";
    }
}














  
  /**
  if (ini_get("display_errors")) {
    // print error
  }elseif (ini_get('log_errors')) {
    //error_log('detail error message');
  } else {
    // not display nor log
  }
  return true;
  **/

  /*
  if($show){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL ^ E_NOTICE ); // 1 E_ALL);
  }else{
    error_reporting(0);
    ini_set('display_errors', 0);
  }
  */




/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
//	define('ENVIRONMENT', $cfg['production']===true ? 'production' : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
/****
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
		ini_set('display_errors', 0);
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}
*/