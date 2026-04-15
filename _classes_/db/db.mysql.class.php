<?php

//// http://www.juntadeandalucia.es/servicios/madeja/contenido/recurso/257

class MySql_PDO extends PDO{
    
    private static $instance = null;
    public static $info = array();

    public function __construct(){
        parent::__construct(  
             CFG::$vars['db']['type'] . ':host=' . CFG::$vars['db']['host'] . ';dbname=' . CFG::$vars['db']['name'] . ';charset=utf8mb4' , 
             CFG::$vars['db']['user'] , 
             CFG::$vars['db']['pass'] 
        );
    }
 
    public static function singleton(){
        if( self::$instance == null ){
            self::$instance = new self();
            //self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //Messages::success('MySQL Connected OK.');
            self::information();
        }
        return self::$instance;
    }

    private static function information(){
        $attributes = array(  "CLIENT_VERSION", "SERVER_INFO", "SERVER_VERSION"); //"PERSISTENT", "ORACLE_NULLS", "CONNECTION_STATUS","AUTOCOMMIT", "ERRMODE", "CASE", Fail in php8: "TIMEOUT","PREFETCH"
        self::$info["SERVER_SOFTWARE"] = 'MySQL';
        foreach ($attributes as $val) {
            self::$info["$val"] = self::$instance->getAttribute(constant("PDO::ATTR_$val"));
        }
    }
   
}


class MySql_PDO_External extends PDO{
    
    private static $instance = null;
    private static $string = '';
    private static $db;
    private static $last_string = '';
 
  
    public function __construct(){
        parent::__construct(  
             CFG::$vars['db'][MODULE]['type'] . ':host=' . CFG::$vars['db'][MODULE]['host'] . ';dbname=' . CFG::$vars['db'][MODULE]['name'] . ';charset=utf8mb4' , 
             CFG::$vars['db'][MODULE]['user'] , 
             CFG::$vars['db'][MODULE]['pass'] 
        );
    }

    public static function singleton(){
        if( self::$instance == null ){
            self::$instance = new self();
            // Messages::success('MySQL Connected OK. '.MODULE);
        }
        return self::$instance;
    }
   
}



    /*
    PDO::ATTR_AUTOCOMMIT:: 1
    PDO::ATTR_ERRMODE:: 0
    PDO::ATTR_CASE:: 0
    PDO::ATTR_CLIENT_VERSION:: mysqlnd 5.0.12-dev - 20150407 - $Id: 3591daad22de08524295e1bd073aceeff11e6579 $
    PDO::ATTR_CONNECTION_STATUS:: Localhost via UNIX socket
    PDO::ATTR_ORACLE_NULLS:: 0
    PDO::ATTR_PERSISTENT:: 
    PDO::ATTR_PREFETCH:: 
    PDO::ATTR_SERVER_INFO:: Uptime: 12809722  Threads: 15  Questions: 58348583  Slow queries: 0  Opens: 1147409  Flush tables: 1  Open tables: 4000  Queries per second avg: 4.555
    PDO::ATTR_SERVER_VERSION:: 5.5.5-10.2.27-MariaDB
    PDO::ATTR_TIMEOUT:: 
    */
