<?php

class LOG {
    
    public static $logfile_prefix = 'log_';
    public static $logfile_extension = '.txt';

    public static $messages = [];

    private static function check_log_dir(){

        $log_dir = SCRIPT_DIR_LOG;
        if (!is_dir($log_dir)) {
            if (!SYS::mkdirr($log_dir, 0775, true) && !is_dir($log_dir)) {
                $err = error_get_last();
                Messages::error(
                    "Could not create log directory {$log_dir}"
                    . ($err && isset($err['message']) ? " ({$err['message']})" : "")
                );
                return false;
            }
        }
        return true;

    }

    private static function logLineBrowser(){

        $browser = Browser::get();
            
        $log_line = '1 '.
                    date('H:i:s') . ' ' . 
                    get_ip() . ' ' . 
                     ($_SESSION['valid_user'] ? $_SESSION['username'] : '-') . ' ' .
                    $_SERVER['REQUEST_URI'] . ' ' .
                    $browser['name'] .  ' ' .
                    $browser['platform'];

        return $log_line;
    }

    public static function write($log_line = null) {
       
        if(CFG::$vars['log']!==true) return;

        //if(MODULE=='debug') return;
        
        $no_log_substrings = [   'op=render_comments'
                               , 'op=render_rating'
                               , '_/dropzone/dropzone'
                               , 'drawing/'
                               , '/ajax/log'
                               , 'debug/'];

        $log_line_extra = self::logLineBrowser() ;

        if ($log_line_extra){
            foreach($no_log_substrings as $substr){
                if(strpos($_SERVER['REQUEST_URI'], $substr)!==false){
                    return;
                }
            }
        }else {
            return;# code...
        }

        if(!self::check_log_dir()) return;

        $log_filename = SCRIPT_DIR_LOG.'/'.self::$logfile_prefix . date('Ymd') . self::$logfile_extension;
       // $log_filename_ext = SCRIPT_DIR_LOG.'/'.self::$logfile_prefix . date('Ymd') . '_ext' . self::$logfile_extension;

        if($log_line === null)
            $log_line = self::logLineBrowser();
        /*
        file_put_contents(
            $log_filename,
            "\n" .  $log_line  ,
            FILE_APPEND
        );    
        */
        //unlink($log_filename_ext);// delete lines $log_filename_ext

        //if(OUTPUT=='ajax' || $_POST){

            // $log_line_extra = self::logLineBrowser() ;
            
            if(self::$messages) {
                $_messages = '';
                foreach(self::$messages as $k => $v) { $_messages .=   '['.$k.'] => '.$v."\n"; }
                $log_line_extra .= "\n-------------- MESSAGES ------------------\n" . $_messages . "------------------------------------------\n" ;
            }

            if($_POST) {                
                $_post = '';
                foreach($_POST as $k => $v) { $_post .=   '['.$k.'] => '.$v."\n"; }
                $log_line_extra .= "\n-------------- POST ----------------------\n" . $_post     .  "------------------------------------------\n" ;
            }

            $log_line_extra .= "\n" ;

            file_put_contents(
                $log_filename,
                $log_line_extra  ,
                FILE_APPEND
            );

        //}

    
    }

    /**
     * Log security events (attacks, violations, etc.)
     *
     * @param string $type Type of security event
     * @param array|string $data Additional data about the event
     */
    public static function security(string $type, $data = null) {

        if(!self::check_log_dir()) return;

        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'user' => $_SESSION['username'] ?? 'anonymous',
            'data' => $data,
        ];

        $log_filename = SCRIPT_DIR_LOG . '/security_' . date('Ymd') . '.log';

        file_put_contents(
            $log_filename,
            json_encode($log_entry, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }


   /*

    // Lista de IPs o rangos a bloquear
    $ips_bloqueadas = array(
        '123.123.0.0/16',  // Rango que empieza con 123.123
        '203.0.113.42',    // IP individual
        '198.51.100.0/24'  // Otro rango
    );

    // Obtener la IP del visitante
    function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    // Función para verificar si una IP está en un rango CIDR
    function ip_in_cidr($ip, $cidr) {
        list($subnet, $mask) = explode('/', $cidr);
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet);
    }

    $ip_visitante = get_client_ip();

    // Recorrer la lista para verificar si la IP está bloqueada
    foreach ($ips_bloqueadas as $ip_a_bloquear) {
        if (strpos($ip_a_bloquear, '/') !== false) {
            // Es un rango CIDR
            if (ip_in_cidr($ip_visitante, $ip_a_bloquear)) {
                header('HTTP/1.0 403 Forbidden');
                echo 'Acceso denegado.';
                exit();
            }
        } else {
            // Es una IP individual
            if ($ip_visitante == $ip_a_bloquear) {
                header('HTTP/1.0 403 Forbidden');
                echo 'Acceso denegado.';
                exit();
            }
        }
    }
    */


}