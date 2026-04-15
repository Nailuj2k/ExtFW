<?php

    trait SQLiteConnection {  
      
        private static $connection;
        private static $error;
        public static $info = array();

        public static function connect(){
           try{
               self::$connection = SQLite_PDO::singleton();
               self::$info = (self::$connection)::$info;
             //Messages::success('SQLite Connected OK.');
           } catch (Exception $e) {
	           Messages::error('Parece que SQLite no está disponible.'.$e->getMessage() );  
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

        private static function fixReplaceInto($sql) {
           // 1. Cláusula de guarda: si no hay un " SET ", no perdemos tiempo
            if (stripos($sql, ' SET ') === false) {
                return $sql;
            }
            if(str_starts_with($sql,'INSERT INTO')) return $sql;
            // 2. Regex flexible: 
            // ^(INSERT.*?INTO|REPLACE\s+INTO) -> Acepta "INSERT... INTO" o "REPLACE INTO"
            // \s+([\w_.]+) -> Captura el nombre de la tabla (incluyendo posibles prefijos tipo bd.tabla)
            $pattern = '/^(INSERT.*?INTO|REPLACE\s+INTO)\s+([\w_.]+)\s+SET\s+(.*)$/i';

            return preg_replace_callback($pattern, function($matches) {
                $verb = $matches[1]; // "REPLACE INTO" o "INSERT INTO"
                $table = $matches[2]; // "ACL_ROLE_PERMS"
                $assignments = $matches[3]; // "id_role = 3, id_permission = 11..."

                $cols = [];
                $vals = [];

                // 3. Separar los pares columna=valor respetando comillas
                $pairs = str_getcsv($assignments, ',', "'");

                foreach ($pairs as $pair) {
                    $parts = explode('=', $pair, 2);
                    if (count($parts) === 2) {
                        $cols[] = trim($parts[0]);
                        $vals[] = trim($parts[1]);
                    }
                }

                // 4. Retornar el formato estándar: REPLACE INTO tabla (cols) VALUES (vals)
                return sprintf(
                    "%s %s (%s) VALUES (%s)",
                    $verb,
                    $table,
                    implode(', ', $cols),
                    implode(', ', $vals)
                );
            }, $sql) ?? $sql;
        }

        private static function fixConcat($sql){

            if (stripos($sql, 'CONCAT(') === false) {
                return $sql;
            }            

            return preg_replace_callback(
                    '/CONCAT\s*\((.*)\)/iU', // 'U' para que sea no-codicioso
                    function ($matches) {
                        $inner = $matches[1];
                        $args = [];
                        $current = '';
                        $depth = 0;

                        // Recorremos el contenido caracter por caracter
                        for ($i = 0; $i < strlen($inner); $i++) {
                            $char = $inner[$i];

                            if ($char === '(') $depth++;
                            if ($char === ')') $depth--;

                            // Si encontramos una coma y no estamos dentro de un paréntesis anidado
                            if ($char === ',' && $depth === 0) {
                                $args[] = trim($current);
                                $current = '';
                            } else {
                                $current .= $char;
                            }
                        }
                        $args[] = trim($current); // Añadir el último argumento

                        // Unimos con el operador de tuberías de SQLite
                        return implode(' || ', $args);
                    },
                    $sql
                );
        }

        
        // Ejcuta una sentencia sql. 
        // Debe usarse para CREATE,DROP,INSERT,UPDATE,DELETE,etc.
        // http://docs.php.net/manual/es/pdo.error-handling.php
        public static function sqlExec($sql){

            if(!self::$connection) return false;

            //$sql = preg_replace('/^REPLACE INTO/i', 'INSERT OR REPLACE INTO', $sql);
            $sql = self::fixConcat($sql);
            $sql = self::fixReplaceInto($sql);

            if ($cache && self::$cache) {
                     (self::$cache)::updateOnExec($sql);
            }

            //return self::$connection->exec($sql);
            /**/
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
                }
                return $affected;
            }catch (PDOException $e) {
                self::$error = $e->getMessage(); //['ERROR'=>$e->getMessage()]];
                Messages::error( $e->getMessage() );
                return false;
            }     
            /**/
        }

        public static function lastError(){
                $err = self::$connection->errorInfo();
                if ($err[0] === '00000' || $err[0] === '01000') return false;
                else return self::error2str(self::$connection->errorInfo()); 
        }
        
        // Devuelve un recurso sql
        public static function sqlQuery($sql,$cache=true){
            if(!self::$connection) return false;

            //$sql = preg_replace('/^REPLACE INTO/i', 'INSERT OR REPLACE INTO', $sql);
            $sql = self::fixConcat($sql);
            $sql = self::fixReplaceInto($sql);


                try{
                    $query = self::$connection->prepare($sql);
                    if ($query){

                        if ($query->execute()===false){

                            if ($key && self::$cache)   self::$cache::set($key, array() );
                            if(CFG::$vars['db']['log']) self::log( '<span class="FAIL">  QUERY FAILED: '.date('Ymd_hms').' ['.$key.'] :: '.$sql.'</span>');
                            return false; 

                        }else{

                            $data =  $query->fetchAll(PDO::FETCH_ASSOC);
                            return $data;

                        }
                    }else{
                        Messages::warning('No query: '.$sql);
                        return false; 
                    }
                }catch (PDOException $e) {
                    self::$error = $e->getMessage(); //['ERROR'=>$e->getMessage()]];
                    Messages::error( $e->getMessage() );
                    return false;
                }

        }
   


        /**
         * Ejecuta una consulta SQL preparada de forma segura
         *
         * @param string $sql Consulta SQL con placeholders
         * @param array $params Parámetros para la consulta
         * @return array Resultado de la consulta
         */
        public static function sqlQueryPrepared($sql, $params = [], $cache = true) {
            if (!self::$connection) return false;

            //$sql = preg_replace('/^REPLACE INTO/i', 'INSERT OR REPLACE INTO', $sql);
            $sql = self::fixConcat($sql);
            $sql = self::fixReplaceInto($sql);

            try {
                $stmt = self::$connection->prepare($sql);
                if (!$stmt) return false;

                foreach ($params as $i => $param) {
                    $pos = $i + 1;
                    if (is_null($param)) {
                        $stmt->bindValue($pos, null, PDO::PARAM_NULL);
                    } elseif (is_int($param)) {
                        $stmt->bindValue($pos, $param, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($pos, $param, PDO::PARAM_STR);
                    }
                }

                $ok = $stmt->execute();
                if ($ok === false) return false;

                $isWrite = preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|CREATE|ALTER|DROP|TRUNCATE)/i', $sql) === 1;
                if ($isWrite) {
                    if (class_exists('MyCache')) MyCache::updateOnExec($sql);
                    return $stmt->rowCount() ?: true;
                }
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                self::$error = $e->getMessage();
                Messages::error($e->getMessage());
                return false;
            }
        }
        



        /** *OLD 
        public static function sqlQueryPrepared($sql, $params = [],$cache=true) {
            // Implementación básica basada en sustitución + delegación a sqlQuery/sqlExec
            foreach ($params as $i => $param) {
                if (is_string($param)) {
                    $params[$i] = "'" . self::escapeString($param) . "'";
                } elseif (is_null($param)) {
                    $params[$i] = 'NULL';
                }
            }

            $finalSql = $sql;
            foreach ($params as $param) {
                // Use str_replace to avoid backreference interpretation in preg_replace
                // This is important for values containing $1, $2, etc. (like bcrypt hashes)
                $pos = strpos($finalSql, '?');
                if ($pos !== false) {
                    $finalSql = substr_replace($finalSql, $param, $pos, 1);
                }
            }

            $isWrite = preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|CREATE|ALTER|DROP|TRUNCATE)/i', $sql) === 1;
            if ($isWrite) {
                if (class_exists('MyCache')) MyCache::updateOnExec($finalSql);
                return self::sqlExec($finalSql);
            }
            return self::sqlQuery($finalSql,$cache);
        }

        **/




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

        public static function OLDsqlQuery($sql,$all=true){

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
            $result = self::$connection->query("select seq from sqlite_sequence WHERE name = '$table'" )->fetch();
            return $result[0] + 1;
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
