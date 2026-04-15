<?php

class Vars{

    // Devuelve el valor de una variable.
    // Comprueba si existe y en caso contrario devuelve false o su valor por omisión.
    // Parámetros: Una variable $key y un valor $default opcional
    public static function getVar($key, $default = false) {
        return $key ?: $default;
    }

    // Igual que la anterior pero con un elemetno de un array asociativo
    // Parametros: 
    // $array - Array en el que buscar la variable $key 
    // $default - Valor opcional
    public static function getArrayVar($array, $key, $default = false) {
        return $array[$key] ?? $default;
    }

    // Get var value from post or get
    public static function getRequestVar($key,$value=''){
      return $_REQUEST[$key] ?? false;
    }

    // Set session var value from post or get ONLY if new value 
    // is different to current value.
    public static function setSessionVarFromRequestVar($key, $default = '') {
      if (!$key) return false;

      if (isset($_REQUEST[$key]) && $_REQUEST[$key] !== ($_SESSION[$key] ?? null)) {
          $_SESSION[$key] = $_REQUEST[$key];
      }
  
      if ($default && empty($_SESSION[$key])) {
          $_SESSION[$key] = $default;
      }
  
      return $_SESSION[$key] ?? false;
    }

    // set session var value ONLY if new value is
    // different to current value
    public static function setSessionVar($key,$value){
      if (!$key) return false;

      if ($value && ($_SESSION[$key] ?? null) !== $value) {
          $_SESSION[$key] = $value;
      }
    }

    public static function getSessionVar($key,$default=false){
        return $_SESSION[$key] ?? $default;
    }


    public static function setDefaultSessionVar($key,$value){
      if(!$key) return false;
      if ( !isset($_SESSION[$key]) || !$_SESSION[$key])
         $_SESSION[$key] = $value;
    }
    
    private static $rec = 0;
    
    public static function debug_var($var,$varname='',$ret=false,$len=300){
        self::$rec++;
        if (self::$rec > 500) return false;
    
        $output = '<div style="font-weight:700;color:#bbb;border-bottom:1px solid #efefef;font-size:12px;">' . $varname . '</div>';
    
        if (is_array($var)) {
            $output .= '<ul style="list-style-type:none;padding:0;">';
            foreach ($var as $k => $v) {
                $output .= '<li style="font-family:Monaco,monospace;font-size:10px;">';
                $output .= '<b style="color:#bbb;">' . $k . ':</b> ' . (is_array($v) ? self::debug_var($v, '', true) : htmlspecialchars(Str::truncate($v, $len)));
                $output .= '</li>';
            }
            $output .= '</ul>';
        } elseif (is_object($var)) {
            $output .= '<span>' . htmlspecialchars(print_r($var, true)) . '</span>';
        } else {
            $output .= '<span>' . htmlspecialchars(Str::truncate($var, $len)) . '</span>';
        }
    
        if ($ret) return $output;
        echo $output;
    }

    /**     
    public static function mkUrl($p1='', $p2='', $p3='', $p4='', $p5='', $p6='', $p7=''){     
        //return  SCRIPT_DIR . ($p1?"/$p1".($p2?"/$p2".($p3?"/$p3".($p4?"/$p4".($p5?"/$p5".($p6?"/$p6".($p7?"/$p7":''):''):''):''):''):''):'');
        if ($p1) $strUrl .=  "/$p1" ;
        if ($p2) $strUrl .=  "/$p2" ;
        if ($p3) $strUrl .=  "/$p3" ;
        if ($p4) $strUrl .=  "/$p4" ;
        if ($p5) $strUrl .=  "/$p5" ;
        if ($p6) $strUrl .=  "/$p6" ;
        if ($p7) $strUrl .=  "/$p7" ;

        if(CFG::$vars['site']['langs']['suffix'])  
            if (CFG::$vars['enable_langs']||CFG::$vars['site']['langs']['enabled'])
                if(in_array(MODULE,['news','page','products','shop']))               //FIX ¿remove line?
                    if ($_SESSION['lang']!==CFG::$vars['default_lang']) //CFG::$vars['default_lang']){
                        $strUrl .= '/'.$_SESSION['lang'];

        return SCRIPT_DIR.$strUrl;
    }
    **/

    public static function mkUrl(...$params) {
      $strUrl = implode('/', array_filter($params));
  
      if (CFG::$vars['site']['langs']['suffix'] && (CFG::$vars['enable_langs'] || CFG::$vars['site']['langs']['enabled'])) {
          if (in_array(MODULE, ['news', 'page', 'products', 'shop']) && $_SESSION['lang'] !== CFG::$vars['default_lang']) {
              $strUrl .= '/' . $_SESSION['lang'];
          }
      }
  
      return SCRIPT_DIR . '/' . $strUrl;
    }

    // Devuelve 0 en caso de que el parametros recibido contenga algún
    // caracter NO comprendido entre 0 y 9, de lo contrario devuelve el número recibido.
    public static function IsNumeric($n) {
        //OLD GOOD  return(ctype_digit(strval($n)));
        return is_numeric($n);
        /*
        $p=$n;
        $l=strlen($p);
        $in = true;
        for ($t=0; $t<$l; $t++){
            $c=substr($p,$t,1);
            //    if($c==',') continue;
            //if($c=='€') continue;
            if ($c<'0' || $c>'9') { $in=false; }
        }
        return $in;
        */
    }
    
    public static function IsFloat($n) {
        $p=$n;
        $l=strlen($p);
        $in = true;
        for ($t=0; $t<$l; $t++) {
            $c=substr($p,$t,1);
            if($c==',') continue;
            if    ($c<'0' || $c>'9') { $in=false; }
        }
        return $in;
    }

    public static function revhex($hex){
        return implode('', array_reverse(str_split($hex, 2)));
    }

    public static function rekey( $input , $uppercase=true, $prefix='[', $suffix = ']') { 
        $out = array(); 
        foreach( $input as $k => $v ) { 
             $out[$prefix . ($uppercase?strtoupper($k):$k) . $suffix] = $v;
        }
        return $out;
    }

    public static function array_in_array($a1,$a2){
        foreach($a1 as $v)
            if(in_array($v,$a2))
                return true;
        return false;
    }

    public static function array_rand($array){
      return $array[array_rand($array)];
    }

}
