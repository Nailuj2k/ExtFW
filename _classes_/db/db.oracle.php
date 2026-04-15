<?php 
  

  class OracleConnection{

  private $resource;
  private static $db_instance;

 // private function __construct(){}
 
  public function getResource(){ return $this->resource; }

  public static function singleton(){
    if(!self::$db_instance){   self::$db_instance = new OracleConnection(); }
    return self::$db_instance;
  }

  public function connect($external = false){
    try{
 
      if($external)  
        $cfg['oracle'] =  CFG::$vars['db'][MODULE];

      //Vars::debug_var($cfg,'cfg oracle');

      $this->resource = oci_connect($cfg['oracle']['user'], $cfg['oracle']['password'], $cfg['oracle']['host'], $cfg['oracle']['charset']);

      if(!$this->resource){
        $error = oci_error();
        //throw new OracleConnectionException($error['message'], $error['code']);
        Messages::error('ERROR: '.$error['message'].'<br />'.$error['code']);
        echo '<div class="error" style="margin:20px;"><h4>Error</h4><p>'.$error['message'].'<br />'.$error['code'].'</p></div>';
        exit();
      }else{
          
        $s = oci_parse($this->resource,  "ALTER SESSION SET ".$cfg['oracle']['config']);
        //$s = oci_parse($this->resource,  "ALTER SESSION SET ".$cfg['oracle']['config']);
        $s = oci_parse($this->resource,  "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '. '");
        $r = oci_execute($s);
      }
    }catch (Exception $e){
      //throw new OracleConnectionException($e->getMessage(), $e->getCode());
      Messages::error('ERROR: '.$e->getMessage().' '.$e->getCode());
    }
  }

  public function close() {
    $this->resource->close();
  }
  public function __clone() {
    trigger_error('Cloning <em>OracleConnection</em> is forbidden.', E_USER_ERROR);
  }

 
  /**
  public function query($sql){
      //$this->debug( $sql , true);
    $handle = oci_parse($this->resource, $sql);
    if (oci_execute($handle)) return $handle;
                          else return false;
  }
  **/
}

