<?php

include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');

class Install{

    //public static $zip = true;
    public static $backup_file_name = 'backup.php'; 
    public static $zip_file_name = false; // 'backup-database-'.Str::sanitizeName($_SERVER['SERVER_NAME'],true).'-'.date('Ymd-his',time()).'.zip'; 


    public static function unzip($zip_file){
      
        $zip = new ZipArchive;
        if ($zip->open($zip_file) === TRUE) {
            $zip->extractTo('./');
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    public static function createZipFile($verbose=false){
        $zipfilehandle = new zipfile();
        if ($verbose) echo "zipfile created OK<br />";
        return $zipfilehandle;
    }

    public static function addToZip($zipfilehandle,$ad_dir,$verbose=false,$compress=false){
         // $verbose=true;
         if (!is_dir($ad_dir)) {
           //if ($verbose) echo  "Adding FILE $ad_dir to zip<br />";
           $f_tmp = @fopen( $ad_dir , 'r');
           $ext = Str::get_file_extension($ad_dir);
           //if ($ext=='jpg') {
             if($f_tmp) {
               if ($compress && $ext=='css') {
                   if ($verbose) echo  "minify CSS $ad_dir <br />";
                   //$dump_buffer=fread( $f_tmp, filesize($ad_dir));
                   //$minifier = new Minify\CSS($dump_buffer);
                   //$dump_buffer = $minifier->minify();
                   //          $dump_buffer = PHPWee\Minify::css(file_get_contents($ad_dir));
               }else if ($compress && $ext=='js') {
                   if ($verbose) echo  "minify JS $ad_dir <br />";
                   //$dump_buffer=fread( $f_tmp, filesize($ad_dir));
                   //$dump_buffer = \JSMin\JSMin::minify($dump_buffer);
                   //          $dump_buffer = PHPWee\Minify::js(file_get_contents($ad_dir));
               }else if (/*1==2 && */$compress && $ext=='php') {
                   if ($verbose) echo  "minify PHP $ad_dir <br />";
                   $dump_buffer = preg_replace('/^<\?php/', '<?php ' . '/* (c) ExtFW 3.0.1 */', php_strip_whitespace($ad_dir));
               }else{
       
                   $_l_ = filesize($ad_dir);
                   if($_l_ < 1){
                       $_l_=1;
                       $dump_buffer = ' ';
                   }
       
                   //if ($ext=='jpg') 
       
                   //print_r($ad_dir.'<br>');
                   $dump_buffer=fread( $f_tmp, $_l_);
               }
       
               //echo 'FILE '.$ad_dir .'<br>';
               $zipfilehandle -> addFile($dump_buffer, $ad_dir);
               fclose( $f_tmp );
             }
           //}  // if ($ext=='jpg') {
         }else{
           if ($handle = opendir($ad_dir)) {
             while (false !== ($file = readdir($handle)))  {
               if ($file == "." || $file == ".." || $file == "log" ) {
                 //if ($verbose) echo  "ignoring $ad_dir/$file <br />";
               } else if (is_dir($ad_dir. '/' .$file)) {
                 $messages['msg'][] =  "$ad_dir/$file is a directory<br />";
                 self::addToZip($zipfilehandle,$ad_dir. '/' . $file,$verbose,$compress);
               
               } else if (strpos($file,'.0')!==false||strpos($file,'.test')!==false||/*strpos($file,'.ok')!==false||*/strpos($file,'.bak')!==false||strpos($file,'.zip')!==false) {
       
               }else  {
                 $messages['msg'][] =  "Adding file $ad_dir/$file ..";
                 $f_tmp = @fopen( $ad_dir . '/' . $file, 'r');
                 if($f_tmp) {
                     $ext = Str::get_file_extension($file);
       
                     //if ($ext=='jpg') {
       
                       if (filesize($ad_dir . '/' . $file)) {
                           if ($compress && $ext=='css') {
                               if ($verbose) echo  "minify CSS $ad_dir/$file <br />";
                               //$dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                               //$minifier = new Minify\CSS($dump_buffer);
                               //$dump_buffer = $minifier->minify();
                               //                      $dump_buffer = PHPWee\Minify::css(file_get_contents($ad_dir . '/' . $file));
                           }else if ($compress && $ext=='js') {
                               if ($verbose) echo  "minify JS $ad_dir/$file <br />";
                               //$dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                               //$dump_buffer = \JSMin\JSMin::minify($dump_buffer);
                               //                      $dump_buffer = PHPWee\Minify::js(file_get_contents($ad_dir . '/' . $file));
                           }else if (/*1==2 &&*/ $compress && $ext=='php') {
                               //  https://php-minify.com/php-obfuscator/
                               // https://www.php.net/manual/en/function.php-strip-whitespace.php
                               if ($verbose) echo  "minify PHP $ad_dir/$file <br />";
                               //if ($file=='index.php'||$file=='index.header.php'||$file=='footer.php'||$file=='run.php') {
                               //    $dump_buffer = compress_php_code(php_strip_whitespace($ad_dir . '/' . $file)) ;      
                               //  //$dump_buffer=str_replace(['  ',"\n"],[' ',' '],$dump_buffer);
                               //    $dump_buffer=str_replace('  ',' ',$dump_buffer);
                               //}else{
                                   $dump_buffer = preg_replace('/^<\?php/', '<?php ' . '/* (c) ExtFW 3.0.1 */', php_strip_whitespace($ad_dir . '/' . $file) );
                               //}
       
                           }else{
                               $dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                           }
                       }else {
                           $dump_buffer='';
                       }
                       //echo 'FILE '.$ad_dir . '/' . $file.'<br>';
                       $zipfilehandle -> addFile($dump_buffer, $ad_dir . '/' . $file);
                       fclose( $f_tmp );
                     //}  // if ($ext=='jpg') {
       
                 }
                 //if ($verbose) echo ". OK<br />";
                 }
                }  
            }
        }  
    }
       
       
    public static function saveZipFile($zipfilehandle,$zipfilename,$verbose=false){
        $dump_buffer = $zipfilehandle -> file();
        // write the file to disk:
        $file_pointer = fopen($zipfilename, 'w');
        if($file_pointer){
        fwrite( $file_pointer, $dump_buffer, strlen($dump_buffer) );
        fclose( $file_pointer );
        if ($verbose) echo "$zipfilename saved OK<br />";
        } else {
        if ($verbose) echo "ERROR: $zipfilename not saved<br />";
        }
    }
       
    public static function runsql($sql){
        /**/
        if (str_starts_with(trim($sql),'CREATE')){
            if (CFG::$vars['db']['type']=='sqlite') {
                $sql = str_replace( array('UNSIGNED',
                                        'INT(1)','INT(2)','INT(3)','INT(4)','INT(5)','INT(6)','INT(7)','INT(8)','INT(9)','INT(10)','INT(11)','INT(12)','INT(13)','INT(14)','INT(15)','INT(16)',
                                        ',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci',
                                       // 'PRIMARY KEY',
                                        'UNIQUE KEY',
                                        'ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci',
                                        'NOT NULL AUTO_INCREMENT',
                                        'current_timestamp()'
                                    ),
                                    array('',
                                        'INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER','INTEGER',
                                        ')',
                                       // '-- PRIMARY KEY',
                                        'UNIQUE',
                                        '',
                                        'PRIMARY KEY AUTOINCREMENT',
                                        "(datetime('now','localtime'))"
                                    ),$sql);
            }
        }

        $sql_clean = '';
        $lines = explode("\n",$sql);
        foreach ($lines as $line){
            if($line!='' && str_starts_with(trim($line),'--')===false) $sql_clean .= $line."\n";
        }
        echo "\n";
        $sql = $sql_clean;
        
        $sql = str_replace(['[SITE_EMAIL],[SITE_NAME]'],[CFG::$vars['email'],CFG::$vars['sitename']],$sql);


        //echo 'Ejecutando <span style="color:#3399CC;font-size:0.8em;">'.$sql.'</span><br />';
        echo 'Ejecutando <span style="color:#3399CC;font-size:0.9em;">'.mb_strimwidth($sql, 0, 300, "...").' ....</span><br />';
        $res=Table::sqlExec($sql);
        echo $res ? '  <span style="color:green"><b>OK</b></span><br />' 
                : '  <span style="color:red;"><b>ERROR '.Table::lastError().'</b></span><br>';  
        //if($res>0) return Table::lastInsertId();
        echo Table::lastError();
        
        //echo '<span style="color:#3399CC;font-size:0.9em;">'.$sql.' ....</span><br />';

    }
    /*
    private static function escape($value) {                                      //ADD 20140423
        //  if($this->driver=='mysql') return mysql_real_escape_string($value);   //ADD 20140423
        //                        else return $value;                             //ADD 20140423
          $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
          $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
          return str_replace($search, $replace, $value);
    } 
    */   
    private static function escape($value) {
        if (is_null($value)) {
            return 'NULL';
        }
        
        //$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        //$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a", '$');
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z", '\\$');

        return str_replace($search, $replace, $value);
    }

    private static function str2type($str_type){
        $af = explode(  '(',  str_replace(array(')',' ','unsigned'), '', $str_type)  );
        $type=$af[0];
        return ($type == 'int' || $type == 'smallint' || $type == 'hidden' || $type == 'tinyint') ? 'int' : $str_type;
    }
    
    public static function backup($tables = false, $external = false, $zip=true){  
        if (CFG::$vars['db']['type']=='mysql') {
            return self::backup_mysql($tables, $external, $zip);
        }else if (CFG::$vars['db']['type']=='sqlite') {
            return self::backup_sqlite($tables, $external, $zip);
        }
    }

    public static function backup_sqlite($tables = false, $external = false, $zip=true){  
        
        $messages = array();
        $messages['error'] =  0;
        $r='';
        

        if($zip){
            if (!self::$zip_file_name) 
                self::$zip_file_name = 'backup-database-'.Str::sanitizeName($_SERVER['SERVER_NAME'],true).'-'.date('Ymd-his',time()).'.zip'; 
            $hzip = self::createZipFile();
        }

        if($tables){
       
            $t = new TableSqlite(false);
         
            
            $return  =  '<?php'."\n\n";
            $return .= "// -- Create tables for database ".CFG::$vars['db']['name']."\n";
            //$primary_keys=true; // in_array(TABLENAMES)
            foreach($tables as $table) {  
                
                if (strpos($table,'LOCALID')>0) continue;
                if (strpos($table,'CALENDAR')>0) continue;
                if (strpos($table,'_LOG')>0) continue;             //FIX list of exclude_from_backup tables in config or theme
                if (strpos($table,'ONT_')>0) continue;
                if ($table=='sqlite_sequence') continue;
                
                $return .=  "\n\n// -- Create table $table\n";            
                
                $str_fields = '';
                $comma ='';
                
                $fields=array();
                $fields_res = $t->sql_query("PRAGMA table_info({$table})");
                if ($fields_res){
                    $return .= "\n".  '$sqls[] = "'. 'DROP TABLE IF EXISTS `'.$table.'`";'."\n";
                    $return .= "\n".  '$sqls[] = "'. 'CREATE TABLE `'.$table.'` ('  ."\n";
                    $str_uni ='';
                    $str_key ='';
                    $_NL_    ='';
                    $comma ='';
                    foreach($fields_res as $fields_row){

                        if ($fields_row['dflt_value']){
                            if      ($fields_row['dflt_value']=='CURRENT_TIMESTAMP')   $fields_row['dflt_value'] = 'CURRENT_TIMESTAMP';
                            else if ($fields_row['dflt_value']=='unixepoch()'   )        $fields_row['dflt_value'] = 'unixepoch()';
                            //else                                                         $fields_row['dflt_value'] = '\''.$fields_row['dflt_value'].'\'';
                        }
                        $fields[]=$fields_row;    
                        $str_fields .= $comma.$fields_row['name'];

                        $return .= $comma.$_NL_;

                        $return .= '    `'.$fields_row['name'].'` '.$fields_row['type']
                        . ($fields_row['pk'] =='1'   ? ' AUTO_INCREMENT PRIMARY KEY':'')
                        . ($fields_row['notnull'] =='1'   ? ' NOT NULL':'')
                        . ($fields_row['dflt_value'] ? ' DEFAULT '.$fields_row['dflt_value'].'':'')
                        ; 
                        $comma=',';
                        $_NL_ = "\n";

                    }
                    $return .=  "\n) ".'";'."\n";  
                }
                
                $return .=  "\n// -- Rows for table $table\n";          
                
                $num_fields=count($fields);
                $result = $t->sql_query('SELECT * FROM '.$table);
                
                //  $row_count = $result->rowCount();  //SQLite ??????????

                if($result){
                    $max = 9000;
                    $n = 0;
                    $i = 0;
                    $return .= "\n".  '$sqls[] =  "INSERT INTO '.$table." (".$str_fields.") VALUES";  
                    foreach($result as $row){
                        $i++;
                        $tmp_str = '';
                        if($n==0) 
                            $tmp_str .=  ""; 
                        else
                            $tmp_str .= ", ";
                        $tmp_str .= "\n    (";  
                        $values = array();
                        foreach($fields as $col)   {  
                            $fieldname = $col['name'];
                            /*
                            if($fieldname=='CREATED_BY')       continue;
                            if($fieldname=='CREATION_DATE')    continue;
                            if($fieldname=='LAST_UPDATED_BY')  continue;
                            if($fieldname=='LAST_UPDATE_DATE') continue;
                            */
                            if (is_null ($row[$fieldname] ))  
                                $values[$fieldname] ='NULL';
                            else if ( self::str2type($col['type']) == 'INTEGER' )
                                $values[$fieldname] = $row[$fieldname];  
                            else
                                $values[$fieldname] = "'".str_replace('$', '\\$', addslashes($row[$fieldname]))."'";

                        }  
                        $tmp_str .=  implode(', ',$values);
                        $tmp_str .=  ")";  
                    
                        //$n++;
                        $n = $n + strlen($tmp_str);

                        if ($n>=$max && $i<$row_count){
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
            
            $handle = fopen(self::$backup_file_name,'w+');  
            fwrite($handle,$return);  
            fclose($handle);  
        
            self::addToZip($hzip,self::$backup_file_name);



        }else{
             
            /*
            $tables = array();  
            $tablas = Table::sqlQuery("SELECT name AS Name FROM sqlite_master WHERE type='table'",false);
            foreach($tablas as $k => $v){
                $tables[] = $v['Name'];
            }
            */
            self::addToZip($hzip,CFG::$vars['db']['name'].'.sqlite');
    
        }


        if($zip){          
            self::saveZipFile($hzip,self::$zip_file_name);
            $messages['url'][] = self::$zip_file_name;
            sleep(2);
            unlink(self::$backup_file_name);
        }else{
            $messages['url'][] =self::$backup_file_name;
        }
    


        return $messages;

    } 



    public static function backup_mysql($tables = false, $external = false, $zip=true){  
        
        $messages = array();
        $messages['error'] =  0;
        $r='';
        
        if (CFG::$vars['db']['type']=='mysql') {


            // EXTERNAL
            if ($external)
                $t = new TableMysql(false,true);
            else
                $t = new TableMysql(false);

            if($tables){
            
            }else{
                        
                $tables = array();  
                $tablas = $t->sql_query('SHOW TABLES');  
                foreach($tablas as $k => $v){
                    //  print_r($v);
                    $tables[] = $v['Tables_in_'.CFG::$vars['db']['name']];
                }
    
            }
            
            
            $return  =  '<?php'."\n\n";
            $return .= "// -- Create tables for database ".CFG::$vars['db']['name']."\n";
            $primary_keys=true; // in_array(TABLENAMES)
            foreach($tables as $table) {  
                
                if (strpos($table,'LOCALID')>0) continue;
                if (strpos($table,'CALENDAR')>0) continue;
                if (strpos($table,'_LOG')>0) continue;             //FIX list of exclude_from_backup tables in config or theme
                if (strpos($table,'ONT_')>0) continue;
                //if (strpos($table,'MUNICIP')>0) continue;
                
                $return .=  "\n\n// -- Create table $table\n";            
                
                $str_fields = '';
                $comma ='';
                
                $fields=array();
                $fields_res = $t->sql_query("DESCRIBE {$table}");
                if ($fields_res){
                    $return .= "\n".  '$sqls[] = "'. 'DROP TABLE IF EXISTS `'.$table.'`";'."\n";
                    $return .= "\n".  '$sqls[] = "'. 'CREATE TABLE `'.$table.'` ('  ."\n";
                    $str_uni ='';
                    $str_key ='';
                    $_NL_    ='';
                    foreach($fields_res as $fields_row){

                        if ($fields_row['Default']){
                            if      ($fields_row['Default']=='current_timestamp()')   $fields_row['Default'] = 'current_timestamp()';
                            else if ($fields_row['Default']=='unix_timestamp()'   )   $fields_row['Default'] = 'unix_timestamp()';
                            else                                                      $fields_row['Default'] = '\''.$fields_row['Default'].'\'';
                        }

                        /*
                        if($fields_row['Field']=='CREATED_BY')      continue;
                        if($fields_row['Field']=='CREATION_DATE')   continue;
                        if($fields_row['Field']=='LAST_UPDATED_BY') continue;
                        if($fields_row['Field']=='LAST_UPDATE_DATE') continue;
                        */
                        $fields[]=$fields_row;    
                        if($primary_keys || $fields_row['Extra']!='auto_increment') {
                            $str_fields .= $comma.$fields_row['Field'];
                            $comma=',';
                        }
                        $return .= $_NL_;
                        $return .= '    `'.$fields_row['Field'].'` '.$fields_row['Type']
                        . ($fields_row['Null'] =='NO'   ? ' NOT NULL':'')
                        . ($fields_row['Default'] ? ' DEFAULT '.$fields_row['Default'].'':'')
                        . ($fields_row['Extra']   ? ' '.$fields_row['Extra']:'')
                        ; //.",\n";
                        if($fields_row['Extra']=='auto_increment') $str_key  = ",\n".'    PRIMARY KEY (`'.$fields_row['Field'].'`)';
                        if($fields_row['Key']=='UNI')              $str_uni .= ",\n".'    UNIQUE KEY (`'.$fields_row['Field'].'`)';
                        if($_NL_ =='') {
                        $_NL_  = ",\n"; 
                        }
                    }
                    $return .= $str_key .$str_uni . "\n) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci".'";'."\n";  
                }
                
                $return .=  "\n// -- Rows for table $table\n";          
                
                $num_fields=count($fields);
                $result = $t->sql_query('SELECT * FROM '.$table);
                
                //  $row_count = $result->rowCount();  //SQLite ??????????
                $row_count = $t->recordCount($table);
                if($row_count>0){
                    $max = 9000;
                    $n = 0;
                    $i = 0;
                    $return .= "\n".  '$sqls[] =  "INSERT INTO '.$table." (".$str_fields.") VALUES";  
                    foreach($result as $row){
                        $i++;
                        $tmp_str = '';
                        if($n==0) 
                            $tmp_str .=  ""; 
                        else
                            $tmp_str .= ", ";
                        $tmp_str .= "\n    (";  
                        $values = array();
                        foreach($fields as $col)   {  
                            if(!$primary_keys) if($col['Extra']=='auto_increment') continue;
                            $fieldname = $col['Field'];
                            /*
                            if($fieldname=='CREATED_BY')       continue;
                            if($fieldname=='CREATION_DATE')    continue;
                            if($fieldname=='LAST_UPDATED_BY')  continue;
                            if($fieldname=='LAST_UPDATE_DATE') continue;
                            */
                            if (is_null ($row[$fieldname] ))  
                                $values[$fieldname] ='NULL';
                            else if ( self::str2type($col['Type']) == 'int' )
                                $values[$fieldname] = $row[$fieldname];  
                            else
                                $values[$fieldname] = "'".str_replace('$', '\\$', addslashes($row[$fieldname]))."'";

                        }  
                        $tmp_str .=  implode(', ',$values);
                        $tmp_str .=  ")";  
                    
                        //$n++;
                        $n = $n + strlen($tmp_str);

                        if ($n>=$max && $i<$row_count){
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
            
            $handle = fopen(self::$backup_file_name,'w+');  
            fwrite($handle,$return);  
            fclose($handle);  
        
        }  // else sqlite

        if($zip){
            if (!self::$zip_file_name) 
                self::$zip_file_name = 'backup-database-'.Str::sanitizeName($_SERVER['SERVER_NAME'],true).'-'.date('Ymd-his',time()).'.zip'; 
            
            $hzip = self::createZipFile();

            if (CFG::$vars['db']['type']=='mysql') {
                
                self::addToZip($hzip,self::$backup_file_name);
                
                
            }else{  //sqlite
                
                self::addToZip($hzip,CFG::$vars['db']['name'].'.sqlite');
                
            }
            
            self::saveZipFile($hzip,self::$zip_file_name);
            $messages['url'][] = self::$zip_file_name;
            sleep(2);
            unlink(self::$backup_file_name);
        }else{
            $messages['url'][] =self::$backup_file_name;
        }
    
        //$messages['msg'][] =  " Downloading $file_source <br>";     
        return $messages;

    } 
   
    public static function restore($backup_filename='backup.php'){

        if (file_exists($backup_filename.'.zip')){
            
            echo '<br>Exists '.$backup_filename.'.zip<br>';
            self::unzip($backup_filename.'.zip');
            include($backup_filename); 
            foreach ($sqls as $sql){  self::runsql($sql);    }
            rename($backup_filename.'.zip', $backup_filename. '.' . date('YmdHis') . '.zip');
            unlink($backup_filename);


        }else if (file_exists($backup_filename)){

            echo '<br>Exists '.$backup_filename.'<br>';
            include($backup_filename); 
            foreach ($sqls as $sql){  self::runsql($sql);    }
            unlink($backup_filename);

        } else {
  
            echo '<br>NOT exists '.$backup_filename.'<br>';

            $result = array();
            $result['error']=1;
            $result['msg'] = '<b style="font-weight:900;">No existe un backup para restaurar.</b><br />Debe subir un archivo '.$backup_filename.' o .sqlite al public_html o equivalente.<br />
                                El archivo '.$backup_filename.' puede crearse con la opcion \'Backup database\' en este mismo módulo.<br>
                                <span style="color:yellow;font-size:11px;">En una próxima versión añadiremos aquí la opción de subir el backup.</span>';
        }
    
    }
    
}