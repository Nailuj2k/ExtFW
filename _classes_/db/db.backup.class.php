<?php

    class DB_Backup{

        public static function str2type($str_type){
            $af = explode(  '(',  str_replace(array(')',' ','unsigned'), '', $str_type)  );
            $type=$af[0];
            return ($type == 'int' || $type == 'smallint' || $type == 'hidden' || $type == 'tinyint') ? 'int' : $str_type;
        }

        public static function backup($tables=false,$external=false,$filename=false){  
            $messages = array();
            $messages['error'] =  0;
            $r='';

            $tb = new TableMysql(false,$external);

            if(!$tables){
                $tables = array();  
                $tablas = $tb->sql_query('SHOW TABLES');  
                foreach($tablas as $k => $v) $tables[] = $v[0];      
            }

            $return  =  '<?php'."\n\n";
            $return .= "// -- Create tables for database ".($external?CFG::$vars['db'][MODULE]['name']:CFG::$vars['db']['name'])."\n"; 
         // Vars::debug_var($tables);
         // return true;
            $primary_keys=true; // in_array(TABLENAMES)
            foreach($tables as $table) {  

                $return .=  "\n\n// -- Create table $table\n";

                $x = $tb->sql_query('SHOW CREATE TABLE '.$table);
                
                $str_fields = '';
                $comma ='';

                $fields=array();
                $fields_res = $tb->sql_query("DESCRIBE {$table}");
                if ($fields_res){
                    $return .= "\n".  '$sqls[] = "'. 'DROP TABLE IF EXISTS `'.$table.'`";'."\n";
                    $return .= "\n".  '$sqls[] = "'. 'CREATE TABLE `'.$table.'` ('  ."\n";
                    $str_uni ='';
                    $str_key='';
                    foreach($fields_res as $fields_row){ //  print_r($fila);
                        $fields[]=$fields_row;    
                        if($primary_keys || $fields_row['Extra']!='auto_increment') {
                            $str_fields .= $comma.$fields_row['Field'];
                            $comma=',';
                        }
                        $return .= '`'.$fields_row['Field'].'` '.$fields_row['Type']
                                . ($fields_row['Null'] =='NO'   ? ' NOT NULL':'')
                                . ($fields_row['Default'] ? ' DEFAULT \''.$fields_row['Default'].'\'':'')
                                . ($fields_row['Extra']   ? ' '.$fields_row['Extra']:'')
                                .",\n";
                        if($fields_row['Extra']=='auto_increment') $str_key  = 'PRIMARY KEY (`'.$fields_row['Field'].'`)';
                        if($fields_row['Key']=='UNI')              $str_uni .= ', UNIQUE KEY (`'.$fields_row['Field'].'`)';
                    }
                    $return .= $str_key .$str_uni . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci".'";'."\n";  
                }

                $return .=  "\n// -- Rows for table $table\n";

                $num_fields=count($fields);
                $result = $tb->sql_query('SELECT * FROM '.$table); //.' LIMIT 20');
                
              //  $row_count = $result->rowCount();  //SQLite ??????????
                $row_count = $tb->recordCount($table);
                if($row_count>0){
                    $max = 9000;
                    $n=0;
                    $return .= "\n".  '$sqls[] =  "INSERT INTO '.$table." (".$str_fields.") VALUES";  
                    foreach ($result as $row){
                              $tmp_str = '';
                              if($n==0) 
                                  $tmp_str .=  ""; 
                              else
                                  $tmp_str .= ", ";
                              $tmp_str .= "\n(";  
                              $values = array();
                              foreach($fields as $col)   {  
                                  if(!$primary_keys) if($col['Extra']=='auto_increment') continue;
                                  $fieldname = $col['Field'];
                                  if (is_null ($row[$fieldname] ))  
                                      $values[$fieldname] ='NULL';
                                  else if ( self::str2type($col['Type']) == 'int' )
                                      $values[$fieldname] = $row[$fieldname];  
                                  else
                                      $values[$fieldname] = "'".Str::escape($row[$fieldname])."'";  
                              }  
                              $tmp_str .=  implode(', ',$values);
                              $tmp_str .=  ")";  
                              
                              //$n++;
                              $n = $n + strlen($tmp_str);

                              if ($n>=$max){
                                  $tmp_str .=  '";' . "\n" . '$sqls[] =  "INSERT INTO '.$table." (".$str_fields.") VALUES ";  
                                  $n=0;
                              }
                              $return .= $tmp_str;
                    }  
                    $return .=  '";'."\n";  
                }else{
                    $return .=  "// -- No hay filas en la tabla $table\n";
                }
                
            }  
            // echo '<pre id="div_install" style="background-color:white;display:block;margin:15px auto;width:100%;height:350px;overflow:auto;border:1px solid #DFDFC4;font-family:Monaco;font-size:9px;">'.str_replace(['<','>'],['&lt;','&gt;'],$return).'</pre>';
              
            $backup_file_name = $filename?$filename:'_modules_/'.MODULE.'/create_tables.php'; 

            $handle = fopen($backup_file_name,'w+');  
            fwrite($handle,$return);  
            fclose($handle);  
            $messages['msg'][] =  " Downloading $backup_file_name <br>";     
            return $messages;
            
        }  

        public static function id2username(){ //$table,$to,$from){  
            $messages = array();
            $messages['error'] =  0;
            $r='';

            $tb = new TableMysql(false,true);
            // $t = new TableMysql(false);

            $result = $tb->sql_query('SELECT user_id,user_name FROM hulamm_user WHERE user_id IN (SELECT DISTINCT(id_user) FROM hulamm_guardias)'); //.' LIMIT 20');

            $return  =  '<?php'."\n\n";

            foreach ($result as $row){

                $return .=  '$sqls[] =  "UPDATE hulamm_guardias             SET  id_user = \''.$row['user_name'].'\' WHERE id_user = \''.$row['user_id'].'\'"'.";\n"
                        .   '$sqls[] =  "UPDATE hulamm_guardias_incidencias SET  id_user = \''.$row['user_name'].'\' WHERE id_user = \''.$row['user_id'].'\'"'.";\n";  
                //$t->sql_query('UPDATE hulamm_g SET id_user = \''.$row['user_name'].'\' WHERE id_user = \''.$row['user_id'].'\'');
            }

            $backup_file_name = '_modules_/'.MODULE.'/sql.php'; 

            $handle = fopen($backup_file_name,'w+');  
            fwrite($handle,$return);  
            fclose($handle);  
            return $messages;
       }

        public static function update_id2username(){ //$table,$to,$from){  

            if(Root()) {

                include(SCRIPT_DIR_MODULE.'/sql.php');

                function runsql($sql){
                    echo 'Ejecutando <span style="color:#3399CC;">'.mb_strimwidth($sql, 0, 300, "...").' ....</span><br />';
                    $res = Table::sqlExec($sql);
                    echo $res ? '  <span style="color:green"><b>OK</b></span><br />' : '  <span style="color:red;"><b>ERROR '.Table::lastError().'</b></span><br>';  
                    //if($res>0) return Table::lastInsertId();
                }
                ?>

                <pre id="div_install" style="background-color:white;display:block;margin:15px auto;width:100%;height:350px;overflow:auto;border:1px solid #DFDFC4;font-family:Monaco;font-size:9px;">

                <?php 
                    foreach ($sqls as $sql){  
                        runsql($sql); 
                    }
                    // unlink(SCRIPT_DIR_MODULE.'/sql_dump.php');
                ?>

                </pre>

                <div style="text-align:center;margin:25px auto;">
                    <p style="font-size:1.3em;text-align:left;" class="info">La actualización se ha completado.</p>
                </div>

                <script type="text/javascript">
                    document.getElementById('div_install').scrollTop = 60000;
                </script>

                <?php 

            } else {

                echo 'Acceso denegado';

            }
       }
    }