<?php
    

    if ( !$_ACL->hasPermission('site_edit') ) {

        http_response_code(403);
        echo "Acceso no autorizado.";

    }else{


        $_encrypted_text = $_ARGS['sql'];
        $_decrypted_text = Crypt::crypt2str($_encrypted_text,$_SESSION['one_time_token']);

        if((!$_decrypted_text || $_decrypted_text=='' ) && strlen($_encrypted_text)>3){

            $sql = NULL;

        }else{
            $sql = $_decrypted_text;
        }

       // $sql =  Crypt::crypt2str($_ARGS['sql'],$_SESSION['one_time_token'] ); //$_ARGS['key']);


       $dump   =  (str_starts_with($sql,'DUMP'));
       $select =  (str_starts_with($sql,'SELECT')  || 
                    str_starts_with($sql,'PRAGMA')|| 
                    str_starts_with($sql,'DESCRIBE')|| 
                    str_starts_with($sql,'SHOW'));

        // sleep(1);  // No hay prisa :)

        if($sql==NULL){

            http_response_code(403);
            echo "No se han podido decodificar los datos. Recarge la página, a ver que tal.";

        }else if ($dump===true){

            $result = array();  
            $result['error'] = 0;
            $result['msg']   = 'Opción no implementada.';      
            echo json_encode($result);

        }else{

            $result = array();  
            $result['error'] = 0;
            $result['msg']   = 'datos recibidos: '.print_r($_ARGS,true);      
            $result['sql']   = $sql;
            $result['type']  = $sql=='SHOW TABLES' ? 'tables' : ($select ? 'select' : 'exec' );


            $_DBNAME = CFG::$vars['db'][MODULE]['name']?CFG::$vars['db'][MODULE]['name']:CFG::$vars['db']['name'];


            if ($cfg['db']['type']  == 'mysql'){              
                $TB = new TableMysql(false,true);

                //////  SHOW VARIABLES LIKE 'version%';
                /////////////////  SELECT VERSION();
            }else if ($cfg['db']['type']  == 'sqlite'){           
                $TB= new TableSqlite(false); 

                 /////////////////  SELECT sqlite_version();           
            }


            if($sql=='SHOW TABLES'){
               // $rows = Table::sqlQuery('SHOW TABLES');
               // $rows = Table::sqlQuery('SELECT TABLE_NAME AS `Name`, TABLE_ROWS AS `Rows`, CONCAT(  (FORMAT((DATA_LENGTH + INDEX_LENGTH) / POWER(1024,2),2)) , \' Mb\')  AS `Size`,  TABLE_COLLATION AS `Collation`  FROM information_schema.TABLES WHERE TABLE_SCHEMA = \'extralab_tienda\'');
               if ($cfg['db']['type']  == 'mysql'){              
                   //$TB = new TableMysql(false,true);
                   $rows = $TB::sqlQuery('SELECT TABLE_NAME AS `Name`, TABLE_ROWS AS `Rows`, (DATA_LENGTH + INDEX_LENGTH)  AS `Size`,  TABLE_COLLATION AS `Collation`  FROM information_schema.TABLES WHERE TABLE_SCHEMA = \''.$_DBNAME.'\' ORDER BY `Name` ASC',false);


                    //SHOW CREATE TABLE CLI_USER
                    //SHOW CREATE VIEW CLI_USER
                    //DESCRIBE information_schema.TABLES
                    //SELECT *  FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'extralab_tienda'



               }else if ($cfg['db']['type']  == 'sqlite'){           
                   // type	name	tbl_name	rootpage	sql
                   $rows = Table::sqlQuery("SELECT name AS Name,'0' AS Rows FROM sqlite_master WHERE type='table'",false);
                   // $rows = Table::sqlQuery("SELECT name AS Name,(SELECT COUNT(1) FROM [name] ) AS Rows FROM sqlite_master WHERE type='table'",true);
                   //foreach ($rows as $k => $v) 
                   //    $v['Rows'] = Table::sqlQuery("SELECT COUNT(1) AS Rows FROM {$v['Name']}")[0]['Rows'];









               }
               foreach ($rows as $k => $v){ 

                   if ($cfg['db']['type']  == 'sqlite'){     
                       $v['Rows'] = Table::sqlQuery("SELECT COUNT(1) AS Rows FROM {$v['Name']}",false)[0]['Rows'];
                   }

                   $result['rows'][] = ['Tabla'=>$v['Name'] , 'Filas'=>$v['Rows']]; 

               }  // 'Filas'=>Str::formatBytes($v['Rows'])]; }

            }else if($select) {

                if ($cfg['db']['type']  == 'sqlite' && str_starts_with($sql,'DESCRIBE')){
                    $sql = str_replace('DESCRIBE', 'PRAGMA table_info(',$sql).' )';
                }

                /*
                if ($cfg['db']['type']  == 'sqlite' && str_starts_with($sql,'DESCRIBE')){
                    $_tbname = str_replace('DESCRIBE ','',$sql);
                    $sql = "SELECT '<pre>'||sql||'</pre>' AS sql,(SELECT COUNT(1) FROM ".$_tbname.") AS Filas FROM sqlite_master WHERE type='table'  AND name = '".$_tbname."'";
                    //$sql = "SELECT sql FROM sqlite_master WHERE type='table' AND name = '".$_tbname."'" ;
                }
                */

                $ret = $TB::sqlQuery($sql,false);
                if (!$ret) {
                    $result['error']= Table::lastError();
                }

                $result['rows'] = $ret; //Table::sqlQuery($sql,false);

                function str_contains_html($str){
                    return preg_match('/<[^>]+>/', $str);
                }               
                
                if ($cfg['db']['type']  == 'sqlite' && str_contains($sql,'sqlite_master')){
                    
                    $result['rows'][0]['sql']='<pre><code class="theme-atom-one-dark language-sql">'.$result['rows'][0]['sql'].'</code></pre>';

                }else  if ($cfg['db']['type']  == 'mysql' &&   str_contains($sql,'CREATE TABLE')){
                    
                    $result['rows'][0]['Create Table']='<pre><code class="theme-atom-one-dark language-sql">'.$result['rows'][0]['Create Table'].'</code></pre>';
                    unset($result['rows'][0]['Table']);  

                }else{
                    foreach ($result['rows'] as $k => $v){ 
                        foreach ($v as $k1 => $v1){ 
                            //if ($v1==='blondie johnson') $result['rows'][$k][$k1]='JAJAJA';
                            //if (str_contains_html($v1)) 
                           //OKI     $result['rows'][$k][$k1] = Str::truncate(strip_tags($v1)    , 100, '...', false); 
                                $result['rows'][$k][$k1] = strip_tags($v1) ; 
                        }
                    }

                }

                
            }else{
                $affected = $TB::sqlExec($sql);

                if ($affected === false) {
                    $result['error'] = $TB::lastError() ?: Table::lastError() ?: 'Error ejecutando la sentencia SQL.';
                    $result['affected'] = 0;
                }else{
                    $result['affected'] = $affected < 1 || $affected=='true'  ? 0 : $affected;
                }
            }
            $result['sql'] = htmlspecialchars($sql);
            echo json_encode($result);

        }  

    }    // if ( !$_ACL->hasPermission('site_edit') ) 




/*

SELECT AAP.ID AS AAP_ID, 
        AP.ID AS AP_ID, 
        AP.NAME 
FROM CFG_AREAS_APPS_PERMS AAP, 
     CFG_APPS_PERMS AP 
WHERE AAP.ID_APP_PERM = AP.ID 
   AND AAP.ID_AREA_APP = (
          SELECT ID_AREA_APP FROM CFG_AREAS_APPS_USERS WHERE ID =1 
)


CFG_AREAS_APPS_PERMS

*/
