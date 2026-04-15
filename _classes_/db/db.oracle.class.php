<?php

//// http://www.juntadeandalucia.es/servicios/madeja/contenido/recurso/257

class Oracle_OCI{
    
    public static $info = array();

    private $resource;
    private static $db_instance;

    //private function __construct(){}

    public function getResource(){ return $this->resource; }

    public static function singleton(){
        if(!self::$instance){   self::$instance = new self(); }
        return self::$instance;
    }

    public function connect(){
        global $cfg;
        try{
            $this->resource = oci_connect( CFG::$vars['db']['user'],  CFG::$vars['db']['password'],  CFG::$vars['db']['host'],  CFG::$vars['db']['charset']);
  
            if(!$this->resource){
                $error = oci_error();
                //throw new OracleConnectionException($error['message'], $error['code']);
               echo '<div class="error" style="margin:20px;"><h4>Error</h4><p>'.$error['message'].'<br />'.$error['code'].'</p></div>';
               exit();
            }
            $s = oci_parse($this->resource,  "ALTER SESSION SET ". CFG::$vars['db']['config']);
            //$s = oci_parse($this->resource,  "ALTER SESSION SET ". CFG::$vars['db']['config']);
            $s = oci_parse($this->resource,  "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '. '");
            $r = oci_execute($s);

        }catch (Exception $e){
          throw new OracleConnectionException($e->getMessage(), $e->getCode());
        }
    }

    public function close() {
        $this->resource->close();
    }

    public function __clone() {
        trigger_error('Cloning <em>OracleConnection</em> is forbidden.', E_USER_ERROR);
    }

    private static function information(){
        self::$info["SERVER_SOFTWARE"] = 'Oracle';
    }

}

/******************
class Oracle_OCI_External {

    public static $info = array();

    private $resource;
    private static $db_instance;

    //private function __construct(){}

    public function getResource(){ return $this->resource; }

    public static function singleton(){
        if(!self::$instance){   self::$instance = new self(); }
        return self::$instance;
    }

    public function connect(){
        global $cfg;
        try{
            $this->resource = oci_connect( CFG::$vars['db'][MODULE]['user'],  CFG::$vars['db'][MODULE]['password'],  CFG::$vars['db'][MODULE]['host'],  CFG::$vars['db'][MODULE]['charset']);
  
            if(!$this->resource){
                $error = oci_error();
                //throw new OracleConnectionException($error['message'], $error['code']);
               echo '<div class="error" style="margin:20px;"><h4>Error</h4><p>'.$error['message'].'<br />'.$error['code'].'</p></div>';
               exit();
            }

            $s = oci_parse($this->resource,  "ALTER SESSION SET ". CFG::$vars['db'][MODULE]['config']);
            //$s = oci_parse($this->resource,  "ALTER SESSION SET ". CFG::$vars['db'][MODULE]['config']);
            $s = oci_parse($this->resource,  "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '. '");
            $r = oci_execute($s);
            Messages::success('Oracle Connected OK.');

        }catch (Exception $e){
          throw new OracleConnectionException($e->getMessage(), $e->getCode());
        }
    }

    public function close() {
        $this->resource->close();
    }
    public function __clone() {
        trigger_error('Cloning <em>OracleConnection</em> is forbidden.', E_USER_ERROR);
    }

    private static function information(){
        self::$info["SERVER_SOFTWARE"] = 'Oracle';
    }

}

**/
