<?php


class APP{
    
    public static $shortcodes;
    public static $plugins;
    private static $args;
    private static $instance;
    
    function __construct(){
    }
    
    public static function load(){
        self::$shortcodes = new Shortcodes();
        self::$plugins    = new Plugins();
    }
      
    public static function singleton(){
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public static function setArgs($args){
        self::$args = $args;
    }

    public static function getArgs(){
        return self::$args;
    }

    public static function arg($key){
        return self::$args[$key] ?? null;
    }
    
}

APP::load();

