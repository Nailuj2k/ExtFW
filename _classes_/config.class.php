<?php


class CFG{
    
    public static $vars = array();
    private static $instance;
   
    function __construct($array = false){
    }

    public static function get($name, $default = false) {
        return self::$vars[$name] ?? $default;
    }

    public static function set($name,$value){
        $old = self::get($name);
        self::$vars[$name] = $value;
        return $old;
    }
    
    public static function load($array){
        foreach ($array as $k=>$v) self::$vars[$k]=$v;
    }

    public static function printvars(){
        //Vars::debug_var(self::$vars);
    }
      
    public static function singleton(){
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }
    
    
}

CFG::load($cfg);

