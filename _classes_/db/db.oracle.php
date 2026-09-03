<<?php 
  

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
      $cfg = array();
      if($external) {
        $cfg['oracle'] = CFG::$vars['db'][MODULE];
      } else if (isset(CFG::$vars['db'][MODULE]['oracle'])) {
        $cfg['oracle'] = CFG::$vars['db'][MODULE]['oracle'];
      }

      if (empty($cfg['oracle'])) {
        throw new RuntimeException('No existe configuración Oracle para el módulo '.MODULE.'.');
      }

      //Vars::debug_var($cfg,'cfg oracle');

      $this->resource = oci_connect($cfg['oracle']['user'], $cfg['oracle']['password'], $cfg['oracle']['host'], $cfg['oracle']['charset']);

      if(!$this->resource){
        $error = oci_error();
        //throw new OracleConnectionException($error['message'], $error['code']);
        $message = isset($error['message']) ? trim($error['message']) : 'Error desconocido de Oracle';
        $code = isset($error['code']) ? $error['code'] : 'sin código';
        throw new RuntimeException('Oracle no está disponible ('.$code.'): '.$message);
      }else{
          
        $s = oci_parse($this->resource,  "ALTER SESSION SET ".$cfg['oracle']['config']);
        //$s = oci_parse($this->resource,  "ALTER SESSION SET ".$cfg['oracle']['config']);
        $s = oci_parse($this->resource,  "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '. '");
        $r = oci_execute($s);
      }
    }catch (Exception $e){
      $this->resource = null;
      error_log('OracleConnection: '.$e->getMessage().' ('.$e->getCode().')');
      throw $e;
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
