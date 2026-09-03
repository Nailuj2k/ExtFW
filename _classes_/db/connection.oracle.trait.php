<?php

    trait OracleConnection {  
      
        private static $connection;
        private static $error;
        public static $info = array();


    private $resource;

    public function getResource(){ return $this->resource; }

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
               Messages::error('Parece que Oracle esta apagado.'); 
           // throw new OracleConnectionException($e->getMessage(), $e->getCode());
        }
    }

    public function close() {
        $this->resource->close();
    }


        
        // Ejcuta una sentencia sql. 
        // Debe usarse para CREATE,DROP,INSERT,UPDATE,DELETE,etc.
        // http://docs.php.net/manual/es/pdo.error-handling.php
        public static function sqlExec($sql){

            $handle = oci_parse($this->resource, $sql);
            $r = oci_execute($handle);
            if ($r){
                //echo $sql.'<br /><br />';
                if ($handle){
                    return true;                
                } 
            }else {
                $e = oci_error($handle);  // Para errores de oci_execute, pase el gestor de sentencia
                $error = 'ERROR: '.$e['code'].': '.htmlentities($e['message'])
                       . "\n<pre>\n"
                       . htmlentities($e['sqltext'])
                       . sprintf("\n%".($e['offset']+1)."s", "^")
                       . "\n</pre>\n";
                return $error;
            }

        }

        public static function lastError(){
                $err = self::$connection->errorInfo();
                if ($err[0] === '00000' || $err[0] === '01000') return false;
                else return self::error2str(self::$connection->errorInfo()); 
        }
        
        // Devuelve un recurso sql
        public static function sqlQuery($sql,$all=true){
            /**
            try{
                $query = self::$connection->query($sql);
                return $query;
            } catch (Exception $e) {
	            Messages::error('ERROR: '.$e->getMessage() );  
            }
            **/
            if(!self::$connection) return false;
            try{
                $query = self::$connection->prepare($sql);
                if ($query){
                    if ($query->execute()==0){
                        Messages::error( self::error2str($query->errorInfo())/*.'<br>'.$sql*/ );
                        return false; 
                    }else{
                        Messages::warning(  $query->errorInfo() );
                        return $all ? $query->fetchAll(PDO::FETCH_ASSOC) : $query;   
                    }
                }else{
                    // Messages::warning('No query: '.$sql);
                    return false; 
                }
            }catch (PDOException $e) {
                Messages::error( $e->getMessage() );
            }

        }

        public static function getFieldsValues($sql){
            if(!self::$connection) return false;
            $row = self::sqlQuery($sql);
            if (!empty($row)) return $row[0];
            return false;
        }

        public static function getFieldValue($sql){
            $row = self::getFieldsValues($sql);
            return ($row) ? current($row) : false;
        }

        /**/
        public static function asArrayValues($sql,$key,$val){
            if(!self::$connection) return false;
            $rows = self::sqlQuery($sql);
            $ret =array();
            if ($rows && !empty($rows)) {
               foreach($rows as $row){
                  $ret[$row[$key]]=$val?$row[$val]:$row;
               }
            }
            return $ret;
        }

        public static function getOptionsValues($args){
          if(!self::$connection) return false;
          $rows = self::sqlQuery(urldecode($args['sql']));
          $r = '';
          if($args['nullvalue']){ 
              $nullkey = $args['nullkey'] ? $args['nullkey'] : '0';
              $r .= '<option value="'.$nullkey.'">'.$args['nullvalue'].'</option>'; 
          }
          foreach($rows as $row) { 
             $r .= '<option value="'.$row['id'].'"';
             if($args['selected']==$row['id']) $r .= ' SELECTED';
             $r .= '>'.$row['name'].'</option>';
          }
          echo count($rows)>0 ? $r : 0;
        }
      
        public static function error2str($errorInfo){
            $array = $errorInfo;
            //0	SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
            //1	Driver-specific error code.
            //2	Driver-specific error message.
            return $array[0].':'.'Error '.$array[1].'<br />'.$array[2];
        }
  
        public static function nextInsertId($table)  {
          return 0;
        }
 
        public static function lastInsertId()   {
         // return   self::$connection->lastInsertId();
          $result = self::$connection->query('SELECT last_insert_rowid()')->fetch();
          return $result[0];
        }
        
        /*****
        public static function lastInsertId($table)   {
          //$sql = "SELECT LAST_INSERT_ID() AS ID";
          $sql = "SELECT user_id AS ID FROM {$table} ORDER BY user_id DESC LIMIT 1";
          $row = self::getFieldsValues($sql);
          return $row['ID'];
          //return self::$connection->lastInsertId();
        }
        ***/

}