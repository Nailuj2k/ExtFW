<?php

class SQLite_PDO extends PDO{
    
    private static $instance = null;
    public static $info = array();

    public function __construct(){
        parent::__construct(  
           'sqlite:'.(CFG::$vars['db']['name']?CFG::$vars['db']['name']:'demo').'.sqlite'
        );
    }
 
    public static function singleton(){
        if( self::$instance == null ){
            self::$instance = new self();
            self::information();
        }
        return self::$instance;
    }

    private static function information(){
        $attributes = array("AUTOCOMMIT", "ERRMODE", "CASE", "CLIENT_VERSION", "CONNECTION_STATUS", "PERSISTENT", "PREFETCH", "SERVER_INFO", "SERVER_VERSION","TIMEOUT");
        
        self::$info["SERVER_SOFTWARE"] = 'SQLite';
        /*
        foreach ($attributes as $val) {
            self::$info["$val"] = self::$instance->getAttribute(constant("PDO::ATTR_$val"));
        }
        */
    }


    
}
