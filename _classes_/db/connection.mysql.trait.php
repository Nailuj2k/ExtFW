<?php

    trait MysqlConnection {  
      
        private static $connection;
        private static $transactions = false;
        public static $info = array();
        public static $cache;
        private static $error;
        
        public static function connect(){
           try{
               self::$connection = MySql_PDO::singleton();
               if(CFG::$vars['db']['cache']) self::$cache =  CACHE_DIR ? MyCache::singleton() : false;
               self::$info = (self::$connection)::$info;
            // Messages::success('MySQL Connected OK.');
           } catch (Exception $e) {
               Messages::error('Parece que MySQL esta apagado.'); 
           }
        }

        public static function beginTransaction(){
            self::$connection->beginTransaction();
        }
        
        public static function commit(){
            self::$connection->commit();
        }
        
        public static function rollBack(){
            self::$connection->rollBack();
        }

        public static function connect_external(){
           try{
               self::$connection = MySql_PDO_External::singleton();
               self::$cache = false;
            // Messages::success('MySQL Connected OK.');
           } catch (Exception $e) {
               Messages::error('Parece que MySQL esta apagado.'); 
           }
        }

        //public static function escape(){
        //    return "'".addslashes($string)."'"; //"'".mysqli_real_escape_string(self::$connection, $string)."'";
        //}

        /*
        public static prepare($sql){
            return self::$connection->prepare($sql);
        }    

        public static function execute($query){
            return $query->execute();
        }

        public static function fetch($query){
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        */
        // Ejcuta una sentencia sql. 
        // Debe usarse para CREATE,DROP,INSERT,UPDATE,DELETE,etc.
        // http://docs.php.net/manual/es/pdo.error-handling.php
        public static function sqlExec($sql,$cache=true){
            if(!self::$connection) return false;

            /////self::log( $sql);
            /*
            $type = '8';
            $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'sql\',\''.Str::escape($sql).'\')';
            Table::sqlExec($log_sql);
            */
            //return self::$connection->exec($sql);
            /**/
                    if ($cache && self::$cache) {
                        (self::$cache)::updateOnExec($sql);
                    }
            try{
                $affected = self::$connection->exec($sql);
                if ($affected === false) {
                    $err = self::$connection->errorInfo();
                    if ($err[0] === '00000' || $err[0] === '01000') {
                        return true;
                    }
                    return false;
                }else if ($affected === 0) {
                    return true; //'0';  //FIX: = _> no changes!!
                }else{
                }

            /***********
            $ok = Table::sqlExec($sql);
            if     ($ok==='0') $result['msg'] = t('NO_CHANGES_MADE');
            else if($ok)       $result['msg'] = t('PAGE_SAVED_SUCCESSFULLY'); //.print_r($_ARGS['id'],true);
                          else $result['msg'] = t('ERROR_SAVING_PAGE_TEXT').': '.Table::lastError();
            ****/
               //if($affected!=='0' && $affected!==false){
                //}
                //Messages::info( 'updateOnExec: '.$sql );
                return $affected;
                /**/
            }catch (PDOException $e) {
                Messages::error( $e->getMessage() );
                return false;
            }
        }

        public static function log($s){
             if(CFG::$vars['db']['log']){ //if(CACHE_LOG){
                global $log;
                $log->data[] = $s;
            }
        }

        public static function lastError(){
                $err = self::$connection->errorInfo();
                if ($err[0] === '00000' || $err[0] === '01000') return false;
                else return self::error2str(self::$connection->errorInfo()); 
        }
        
        // Ejecuta una consulta sql, y devuelve / *el recurso, o* / todo
        public static function sqlQuery($sql,$cache=true){
            if(!self::$connection) return false;

            $key = $cache && self::$cache ? (self::$cache)::keyFromSqlSelect($sql) : false;

            if($key && CFG::$vars['db']['log'])  self::log( '<span class="READ">READ: ['.$key.'] :: '.$sql.'</span>');

            $get_result =  $key /*&& $all===true*/ ? (self::$cache)::get($key) : false;

            if ($get_result) {  //if $key  && 


                //if($debug) echo '<span class="debug">Datos le&iacute;dos de la cach&eacute</span>';
                //if(MyCache::debug()) echo '<span class="debug">Datos le&iacute;dos de la cach&eacute</span>';

                return $get_result;
              /**
            }else if ($get_result!==false) {

                 if(CFG::$vars['db']['log'])  self::log( '<span class="READ">READ: ['.$key.'] :: '.$sql.'</span>');

                //if($debug) echo '<span class="debug">Datos le&iacute;dos de la cach&eacute</span>';
                //if(MyCache::debug()) echo '<span class="debug">Datos le&iacute;dos de la cach&eacute</span>';

                return false;
              **/
            }else{

                try{
                    //if (self::$transactions) self::$connection->beginTransaction();
                    $query = self::$connection->prepare($sql);
                    if ($query){

                        if ($query->execute()==0){

                            //Messages::error( self::error2str($query->errorInfo()).'<br>'.$sql);
                            //if (self::$transactions) self::$connection->rollBack();
                            if ($key && self::$cache)   self::$cache::set($key, array() );
                            if(CFG::$vars['db']['log']) self::log( '<span class="FAIL">  EMPTY: '.date('Ymd_hms').' ['.$key.'] :: '.$sql.'</span>');                                    
                            return false; 

                        }else{

                            $data =  $query->fetchAll(PDO::FETCH_ASSOC);
                            //Messages::warning(  $query->errorInfo() );
                            //if (self::$transactions) self::$connection->commit(); 
                            if ($key && self::$cache)  {
                             
                                if($data){
                                    self::$cache::set($key, $data );
                                    if(CFG::$vars['db']['log']) self::log( '<span class="WRITE">  WRITE: '.date('Ymd_hms').' ['.$key.'] :: '.$sql.'</span>');                                    
                                //}else{
                                //    if(CFG::$vars['db']['log']) self::log( '<span class="FAIL">  FAIL: '.date('Ymd_hms').' ['.$key.'] :: '.$sql.'</span>');                                    
                                }   
                                /*
                                if ( self::$cache::set($key, $data) > 0  ) {
                                    if(CFG::$vars['db']['log']) self::log( '<span class="WRITE">  WRITE: '.date('Ymd_hms').' ['.$key.'] :: '.$sql.'</span>');                                    
                                }else{
                                    if(CFG::$vars['db']['log']) self::log( '<span class="FAIL">  FAIL: '.date('Ymd_hms').' ['.$key.'] :: '.$sql.'</span>');                                    
                                }
                                */

                            }

                            return $data;

                        }
                    }else{
                        Messages::warning('No query: '.$sql);
                        return false; 
                    }
                }catch (PDOException $e) {
                    Messages::error( $e->getMessage() ); //.'<br>'.$sql );
                    self::$error = $e->getMessage(); //['ERROR'=>$e->getMessage()]];
                    return false; //[0=>['ERROR'=>$e->getMessage()]];
                }

            } // if cache

        }
   

        /**
         * Ejecuta una consulta SQL preparada de forma segura
         * 
         * @param string $sql Consulta SQL con placeholders
         * @param array $params Parámetros para la consulta
         * @return array Resultado de la consulta
         */
        public static function sqlQueryPrepared($sql, $params = [], $cache = true) {

            if (!self::$connection) {
                // Optionally log or use Messages::error here if appropriate
                return false;
            }

            // Detectar si es escritura (no SELECT)
            $isWrite = preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|CREATE|ALTER|DROP|TRUNCATE)/i', $sql) === 1;

            if ($isWrite) {
                // Invalidar caché por tablas afectadas
                if (self::$cache) {
                    (self::$cache)::updateOnExec($sql);
                } elseif (class_exists('MyCache')) {
                    MyCache::updateOnExec($sql);
                }

                try {
                    $stmt = self::$connection->prepare($sql);
                    if ($stmt) {
                        if ($stmt->execute($params)) {
                            // Devolver filas afectadas o true
                            $affected = $stmt->rowCount();
                            return $affected === 0 ? true : $affected;
                        } else {
                            $errorInfo = $stmt->errorInfo();
                            Messages::error('SQL Prepared Statement Execution Error: ' . self::error2str($errorInfo) . '<br>Query: ' . htmlentities($sql) . '<br>Params: ' . htmlentities(print_r($params, true)));
                            return false;
                        }
                    } else {
                        $errorInfo = self::$connection->errorInfo();
                        Messages::error('SQL Prepare Statement Error: ' . self::error2str($errorInfo) . '<br>Query: ' . htmlentities($sql));
                        return false;
                    }
                } catch (PDOException $e) {
                    Messages::error('PDOException in sqlQueryPrepared (write): ' . $e->getMessage() . '<br>Query: ' . htmlentities($sql));
                    self::$error = $e->getMessage();
                    return false;
                }
            }

            // SELECT con caché por clave + parámetros
            $key = false;
            if ($cache && self::$cache) {
                $key = (self::$cache)::keyFromSqlSelect($sql) . '-' . hash('adler32', serialize($params));
                if ($key) {
                    $get_result = (self::$cache)::get($key);
                    if ($get_result) return $get_result;
                }
            }

            try {
                $stmt = self::$connection->prepare($sql);
                if ($stmt) {
                    if ($stmt->execute($params)) {
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($key && self::$cache) {
                            (self::$cache)::set($key, $data);
                        }
                        return $data;
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        Messages::error('SQL Prepared Statement Execution Error: ' . self::error2str($errorInfo) . '<br>Query: ' . htmlentities($sql) . '<br>Params: ' . htmlentities(print_r($params, true)));
                        return false;
                    }
                } else {
                    $errorInfo = self::$connection->errorInfo();
                    Messages::error('SQL Prepare Statement Error: ' . self::error2str($errorInfo) . '<br>Query: ' . htmlentities($sql));
                    return false;
                }
            } catch (PDOException $e) {
                Messages::error('PDOException in sqlQueryPrepared: ' . $e->getMessage() . '<br>Query: ' . htmlentities($sql) . '<br>Params: ' . htmlentities(print_r($params, true)));
                self::$error = $e->getMessage();
                return false;
            }
        }
        
        /**
         * Escapa una cadena para usar en SQL (implementación básica)
         * 
         * @param string $string Cadena a escapar
         * @return string Cadena escapada
         */
        private static function escapeString($string) {
            // Implementación básica - en producción usar funciones específicas del SGBD
            return str_replace(["'", '"', '\\'], ["''", '""', '\\\\'], $string);
        }

        public static function getError(){
            return self::$error ;
        }

        /****/
        public static function __sqlQuery($sql,$cache=true){
            if(!self::$connection) return false;
            try {
                $query = self::$connection->prepare($sql);
                if($query->execute()){
                    $data =  $query->fetchAll(PDO::FETCH_ASSOC);
                    return $data;   
                }else{
                    Messages::warning(  $query->errorInfo() );
                    $_SESSION['message_error'] = 'EEERROR: '.$sql;
                      //Messages::error( 'ERROR: '.$sql );               
                }
            }catch (PDOException $e) {
                Messages::error( $e->getMessage() );
                return [0=>['ERROR'=>$e->getMessage()]];
                $_SESSION['message_error'] = 'EEERROR: '.$e->getMessage();
            }
        }
        /*****/

        public static function getFieldsValues($sql){
            if(!self::$connection) return false;
            $row = self::sqlQuery($sql);
            if ($row && !empty($row)) return $row[0];
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
        /**/
        /*
        public static function asArrayValues($sql,$key){
            if(!self::$connection) return false;
            $rows = self::sqlQuery($sql);
            $ret =array();
            if ($rows && !empty($rows)) {
               foreach($rows as $row){
                  $ret[]=$row[$key];
               }
            }
            return $ret;
        }
        */

        public static function getOptionsValues($args){
          if(!self::$connection) return false;
          $rows = self::sqlQuery(urldecode($args['sql']));   //FIX sql inject risk
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
            return '['.$array[0].':'.$array[1].':'.$array[2].']';
        }

        public static function nextInsertId($table)   {
          $sql = "SHOW TABLE STATUS FROM ".CFG::$vars['db']['name']." LIKE '$table'";
          $row = self::sqlQuery($sql);
          return $row[0]['Auto_increment'];
        }

        public static function lastInsertId()   {
            return  self::$connection->lastInsertId();
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
        /********
        public static function function runsql($sql){
            //global $simulation;
            echo "Ejecutando <font color='#3399CC'>".stripslashes($sql)." ....</font>";
            //if ($simulation) echo "<font color=\"green\"><i>OK</i></font><br>\n";
            //else {
            $res=self::sqlExec($sql);
            if($res) echo "  <font color=\"green\"><b>OK</b></font><br>\n";  
                else echo "  <font color=\"red\"><b>ERROR</b></font><br>\n";  
            //}
            return self::lastInsertId();
        }

        function execSQLs( $querys ){
            $sqls = explode( ';', $querys );
            foreach( $sqls as $sql )	{
               if(trim($sql)) self::runsql($sql);
            }
        }
        ****/
        /*
        public static function info(){
            self::$info;
        }
        */


        /**
         * 
         * // Busca algo como esto y cambia utf8 por utf8mb4:
// $pdo->exec("SET NAMES utf8");
$pdo->exec("SET NAMES utf8mb4");

// O si usas DSN:
// 'mysql:host=...;dbname=...;charset=utf8'
'mysql:host=...;dbname=...;charset=utf8mb4'

         * 
         */

}
