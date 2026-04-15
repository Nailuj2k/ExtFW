<?php

//// http://www.juntadeandalucia.es/servicios/madeja/contenido/recurso/257

class Demo_PDO{
    
    private static $instance = null;
 
    public function __construct(){
    }
 
    public static function singleton(){
        if( self::$instance == null ){
            self::$instance = new self();
        }
        return self::$instance;
    }


}
