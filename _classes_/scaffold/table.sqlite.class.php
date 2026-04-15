<?php 

  define('CLASS_TABLE_SQLITE_LOADED',true);

  class TableSqlite extends Table{

    use SqliteConnection;   

    public $driver = 'sqlite';

    public function __construct($tablename=false,$external=false) {
        parent::__construct($tablename);
        if ($external)  
            self::connect_external();
        else 
            self::connect();
    }

    public function sql_query($sql){
        return self::sqlQuery($sql,false);
    }

    public function sql_exec($sql){
        return self::sqlExec($sql);
    }
    /*
    public function last_error(){
        return self::lastError();
    }
   */
    /*
    public function sql_fetch($query){
        return $query?$query->fetch(PDO::FETCH_ASSOC):false;
    }

    public function getFieldValue($fieldName,$table='',$where=''){
        if(strpos($fieldName,'SELECT')===0)  $sql = $fieldName;  
                                       else  $sql = "SELECT {$fieldName} AS RESULT FROM {$table} $where";  
        $row = $this->getFieldsValues($sql);
        return ($row) ? current($row) : false;
    }
    */
    public function recordCount($where='',$field=false,$table=false)   {
        $total=0;
        if(strpos($where,'SELECT')===0){
            $sql = $where;  
        }else{
            if(!$table) $table = $this->tablename;
            if(!$field) $field = $this->pk->fieldname;
            $sql = "SELECT count('{$field}') AS total FROM {$table} $where";  
        }
        return $this->getFieldValue($sql);
    }
    
    public function nextInsertId($table=false){ 
      if(!$table) $table = $this->tablename;
      $sql = "select seq from sqlite_sequence WHERE name = '$table'" ;
      $row = self::sqlQuery($sql);
      return ($row[0]['Auto_increment'])+1;
      //return self::nextInsertId($table);
    }
    /*
    public function lastInsertId(){ 
      return  self::$connection->lastInsertId();
    }
    */
    public function numRows($sql){ 
 	  $r =  $this->sql_query($sql);
      return $r->rowCount();
    }    
    
    public function toarray($arrayname,$tablename,$fieldkey='',$fieldname='',$where='',$force=true){
      //if(!$arrayname) $arrayname=$tablename;
      //if ($force) $_SESSION['_CACHE']['values'][$arrayname] = false;
      //if (is_array($_SESSION['_CACHE']['values'][$arrayname])) return $_SESSION['_CACHE']['values'][$arrayname]; 
      if(strpos(trim($tablename),'SELECT')===0) $query = $this->sql_query($tablename);  
                                          else  $query = $this->sql_query('SELECT '.$fieldkey.' AS ID,'.$fieldname.' AS NAME FROM '.$tablename.' '.$where);
      //$_SESSION['_CACHE']['values'][$arrayname] = array();
      $result = array();
      if($query) 
      foreach($query as $row)  { /*$_SESSION['_CACHE']['values'][$arrayname]*/$result[$row['ID']] = $row['NAME']; }
      return $result; //$_SESSION['_CACHE']['values'][$arrayname];
    }

    public function asArray($arrayname,$sql,$force=true,$complete=false){
      //if ($force) $_SESSION['_CACHE']['values'][$arrayname] = false;
      //if (is_array($_SESSION['_CACHE']['values'][$arrayname])) return $_SESSION['_CACHE']['values'][$arrayname]; 
      $query = $this->sql_query($sql);
      // $_SESSION['_CACHE']['values'][$arrayname] = array();
      $result = array();
      if($query) foreach($query as $row)  { /*$_SESSION['_CACHE']['values'][$arrayname]*/$result[$row['ID']] = $complete ? $row : $row['NAME'];}
      return $result;  //$_SESSION['_CACHE']['values'][$arrayname];
    }

    public function Orderby(){
      $ob = explode(' ',$this->orderby);
      if ( $this->colByName( $ob[0] )->type=='date' ) {      //   $r = "DATE_FORMAT({$ob[0]}, '".DATE_FORMAT_MYSQL."') ";
         $r = "strftime('%Y-%m-%d',{$ob[0]} ) ";
         if (count($ob)>1) $r.= $ob[1];
         return $r;
      }else  return $this->orderby;
    }

    public function sql_currentdate(){
      return gmdate("YmdHis",time());
      //return 'NOW()';          //    return 'CURDATE()';
    } 

    public function formatFieldname($col) {
      switch($col->type) {
        case 'float':
        case 'decimal':
        case 'enum':
        case 'color':
        case 'textarea':
        case 'file':
        case 'timestamp':
        case 'time':
        case 'varchar':
        case 'select':
        case 'progress':
        case 'hidden':
        case 'int':
        case 'date':
        case 'datetime':
        case 'time':
        case 'bool':
        case 'unixtime':
          return $col->fieldname; 
          break;
        default:
          return $col->fieldname; 
      }
    }

    public function str_select($_page_start){
      $sql   = 'SELECT '.$this->str_SqlFields();
      $sql  .= ' FROM '.$this->tablename;
      if($this->filter)    $sql  .= ' WHERE '.$this->filter;
      if     ($this->output=='group') $sql  .= ' ORDER BY '.$this->field_group_order->fieldname;
      else if($this->orderby)         $sql  .= ' ORDER BY '.$this->Orderby();
      $sql .= " LIMIT  $_page_start,{$this->page_num_items}";
      return $sql;
    }

    public function format_value($col,$value){
      
      if     ($col->type=='datetime')   return /*$value ? $value :*/ gmdate("Y-m-d H:i:s",time());
      //DATE else if($col->type=='date')       return /*$value ? $value :*/ gmdate("Y-m-d",time());
      else if($col->type=='time')       return $value ? $value : gmdate("H:i:s",time());
      else                              return $value;
      /*
      if($col->type=='date')      return (!$value || $value=='00-00-0000' ?  'NULL' : "STR_TO_DATE('{$value}', 'Ymd')" );
      if($col->type=='datetiime') return (!$value || $value=='00-00-0000' ?  'NULL' : "STR_TO_DATE('{$value}', 'YmdHis')" );
      //else if($col->type=='textarea') return Str::escape($value);
                        else 
                        */

    }
 
    private function str_sql_len($len,$deflen){ return ($len) ? $len : $deflen; }
    
    private function str_new_field($field){
      $af = explode(  '(',  str_replace(array(')',' ','unsigned'), '', $field['Type'])  );
      $field_var_name = strtolower($field['Field']);
      $type=$af[0];
      $len=$af[1];
      if($type == 'text')     $type = 'textarea';
      if($type == 'smallint') $type = 'int';
      if($type == 'hidden')   $type = 'int';
      if($type == 'datetime') $type = 'datetime';
      if($type == 'timestamp')$type = 'timestamp';
      if($type == 'tinyint')  $type = 'int';
      if($type == 'enum')     {
        $len = '1';
        $type = 'int';
      }
      if($type == 'decimal')  $len = "'{$len}'";
      if($type == 'timestamp')$len = false;
      if($type == 'datetime') $len = false;
      if($type == 'date')     $len = false;
      $r  = '$'.$field_var_name.' = new Field();'."\n";
      $r .= '$'.$field_var_name.'->type      = \''.$type.'\';'."\n";
      if($len) $r .= '$'.$field_var_name.'->len       = '.$len.';'."\n";
      $r .= '$'.$field_var_name.'->fieldname = \''.$field['Field'].'\';'."\n";
      $r .= '$'.$field_var_name.'->label     = \''.str_replace(array('_id','_lv','_'),array('','',' '),ucwords($field_var_name)).'\';'."\n";  
      
      if($type == 'select') {  
        $r .= '$'.$field_var_name.'->values= $tabla->toarray(\'TRA_'.$field_var_name.'\' , "SELECT '.$field['lookup_key'].' AS ID, '.$field['lookup_display'].' AS NAME FROM '.$field['lookup_table'].' ",true);'."\n"; 
        $r .= '$'.$field_var_name.'->values_all= $tabla->toarray(\'TRA_'.$field_var_name.'_all\' , "SELECT '.$field['lookup_key'].' AS ID, '.$field['lookup_display'].' AS NAME FROM '.$field['lookup_table'].' WHERE ACTIVE = 1",true);'."\n"; 
      }
      if (isset($field['Field']['PK']) && $field['Field']['PK']===true){
        $r .= '$'.$field_var_name.'->pk  = true ;'."\n";
      }else{
        $r .= '$'.$field_var_name.'->editable  = false ;'."\n";
        $r .= '$'.$field_var_name.'->sortable  = true;'."\n";
        $r .= '$'.$field_var_name.'->searchable  = true;'."\n";
      }
      if (isset($ield['props'])){
	      foreach ($field['props'] as $k=>$v){
              $r .= "{${$field_var_name}}->{$k} = $v;\n";
	      } 
      }
      if($type == 'text') $r .= '$'.$field_var_name.'->hide  = true;'."\n";
      return $r;
    }
    
    public function create($filename){
      global $def;
      echo '<style>code, code span{font-size:9px !important;line-height:.9em;font-family:Monaco;height:110px;overflow:auto;}</style><pre style="max-height:110px;">';
    
      $pk = false;
      $res = $this->sql_query("SHOW FIELDS FROM {$this->tablename}");
      if ($res){
        $contenido  = '/* Auto created */'."\n";
        $contenido .= "\n";
        $contenido .= '$tabla = new TableSqlite(\''.$this->tablename.'\');'."\n";
        $contenido .= "\n";
        foreach($res as $fila){
          $fila['PK']=false;
          if (/*!isset($def['tables'][$this->tablename]['PK']) */ !$pk){
	         if ($fila['Key']=='PRI' || $fila['Type']=='int') {
              $fila['PK']=true;
              $pk=true;
           }
          }
          if($fila['Field']==CREATED_BY||$fila['Field']==CREATION_DATE||$fila['Field']==LAST_UPDATE_DATE||$fila['Field']==LAST_UPDATED_BY){
            $whocols = true;
          }else if($fila['Field']==ACTIVE){
            $activecol = true;
          }else{
            if (isset($def['masters'][$this->tablename][$fila['Field']])){
	          $fila['Type']='select';
	          $fila['lookup_key']     = $def['masters'][$this->tablename][$fila['Field']][0];
	          $fila['lookup_display'] = $def['masters'][$this->tablename][$fila['Field']][1];
	          $fila['lookup_table']   = $def['masters'][$this->tablename][$fila['Field']][2];
            }
            if (isset($def['tables'][$this->tablename][$fila['Field']]['props'])){
	           $fila['props'] = $def['tables'][$this->tablename][$fila['Field']]['props'];
            }
            $contenido .= $this->str_new_field($fila);
            $contenido .= '$tabla->addCol($'.strtolower($fila['Field']).');'."\n";
            $contenido .= "\n";
          }
        }
        $contenido .= '$tabla->name = \''.$this->tablename.'\';'."\n";
        $contenido .= '$tabla->title = \''.str_replace('_','',ucwords($this->tablename)).'\';'."\n";
        $contenido .= '$tabla->verbose=false;'."\n";
        $contenido .= '$tabla->output=\'table\';'."\n";
        $contenido .= '$tabla->page = $page;'."\n";
        $contenido .= '$tabla->page_num_items = 10;'."\n";
        $contenido .= '$tabla->show_empty_rows = true;'."\n";
        $contenido .= '$tabla->show_inputsearch =true;'."\n";
        if($whocols)$contenido .= '$tabla->addWhoColumns();'."\n";
        if($activecol)$contenido .= '$tabla->addActiveCol();'."\n";
        $contenido .= "\n";
        $contenido .= '$tabla->perms[\'delete\'] = Administrador();'."\n";
        $contenido .= '$tabla->perms[\'edit\']   = Administrador();'."\n";
        $contenido .= '$tabla->perms[\'add\']    = Administrador();'."\n";
        $contenido .= '$tabla->perms[\'setup\']  = Root();'."\n";
        $contenido .= '$tabla->perms[\'reload\'] = true;'."\n";
        $contenido .= '$tabla->perms[\'filter\'] = true;'."\n";
        $contenido .= '$tabla->perms[\'view\']   = true;'."\n";
        $contenido .= "\n";
        if (isset($def['details'][$this->tablename])){
           $contenido .= '$tabla->detail_tables=array(\''.implode( ',' , $def['details'][$this->tablename]).'\');'."\n";
        //   $contenido .= '$tabla->detail_tables=array(\''.               $def['details'][$this->tablename] .'\');'."\n";
        }     
        if (isset($def['tables'][$this->tablename]['master_key'])){
           $contenido .= '$tabla->setParent(\''.$def['tables'][$this->tablename]['master_key'].'\',$parent);'."\n";
        }
        $contenido .= "\n";
        $event_error_code = ($this->recordCount()<1) ? '0' : '5';
        $contenido .= "class {$this->tablename}Events extends defaultTableEvents implements iEvents{\n";
        $contenido .= "  function OnInsert(\$owner,&\$result,&\$post) { \n";
        $contenido .= "      \$result['error'] = {$event_error_code};\n";
        $contenido .= "      \$result['msg'] = '¡Esto es el evento OnInsert!';\n";
        $contenido .= "  }\n";
        $contenido .= "  function OnUpdate(\$owner,&\$result,&\$post) { \n";
        $contenido .= "      \$result['error'] ={$event_error_code};\n";
        $contenido .= "      \$result['msg'] = '¡Esto es el evento OnUpdate! ';\n";
        $contenido .= "  }\n";
        $contenido .= "  function OnDelete(\$owner,&\$result,\$id)    { \n";
        $contenido .= "      \$result['error'] =5;\n";
        $contenido .= "      \$result['msg'] = '¡Esto es el evento OnDelete!';\n";
        $contenido .= "  }\n";
        if (file_exists(SCRIPT_DIR_MODULE.'/EVENTS_'.$this->tablename.'.php')){
           $contenido .=  file_get_contents(SCRIPT_DIR_MODULE.'/EVENTS_'.$this->tablename.'.php');
        }
        $contenido .= "}\n";
        $contenido .= "\$tabla->events = New {$this->tablename}Events();\n";
        $contenido .= "\n";
        $contenido .= "\n";
        //echo 'Archivo creado: '.$filename.'<pre style="font-size:10px;font-family:Monaco;">';
        highlight_string('<'."?php \n".$contenido.'?'.'>');
        create_php_file($filename,$contenido);
        if ($_SESSION['message_error']) echo $_SESSION['message_error'];
        $_SESSION['message_error'] = false;
      }else{
        echo "no cols\n";
	    //  $this->sql_query($def['tables'][$this->tablename]['create']);
      }
      echo '</pre>';
    }
    
    public function check_table(){
      echo '<pre style="font-size:.8em;text-align:left;">';
      echo 'Tabla: '.$this->tablename.' (sqlite)<br />';
      
      $recreate = false;

      if($this->soft_delete){
         if($this->colByName('DELETED')===false){
            $this->addDeletedCol();
         }
      }

      $res = $this->sql_query("PRAGMA table_info( {$this->tablename} )");

      if ($res){

        foreach($res as $fila){  
          echo 'Columna: '.$fila['name'].' '.$fila['type'];
          if($fila['pk']=='1') echo " PRIMARY KEY AUTOINCREMENT" ;
          if($fila['dflt_value'])   echo " DEFAULT {$fila['dflt_value']}";  //20131017

          if ($fila['notnull']=='1') echo' NOT NULL';
          
          foreach( $this->cols as $col) {
            $t1='';
            $t2='';
            if($col->fieldname==$fila['name'] && !$col->calculated){
              $col->attribute = '';
                $t2 = strtoupper($fila['type']);
                if     ($col->type=='select')  $t1 = $col->len>5 ? 'VARCHAR('.$col->len.')' : 'INTEGER' ;
                else if($col->type=='datetime')$t1 = 'DATETIME' ;
                else if($col->type=='date')    $t1 = 'VARCHAR(10)' ;
                else if($col->type=='int')     $t1 = $t2=='INT(16)' ? $t2 : 'INTEGER' ;
                else if($col->type=='hidden')  $t1 = 'INTEGER' ;
                else if($col->type=='progress')$t1 = 'INTEGER'; //('.($col->len?$col->len:'4').')' ;
                else if($col->type=='varchar') $t1 = 'VARCHAR' ;
                else if($col->type=='textarea')$t1 = 'TEXT' ;
                else if($col->type=='bool')    $t1 = 'INTEGER'; //(1)' ;
                else if($col->type=='unixtime')$t1 = 'INTEGER';
                else if($col->type=='ccc')     $t1 = 'VARCHAR(23)' ;
                else if($col->type=='file')    $t1 = 'VARCHAR(200)' ;
                else if($col->type=='color')   $t1 = 'VARCHAR(12)' ;
                else if($col->type=='decimal') {
                  $ta = explode(',',$col->len);
                  $t1 = "decimal({$ta[0]},{$this->str_sql_len($col->precission,2)})" ;
                }else                           $t1 = "{$col->type}" ;

                      if($t2=='INT(1)'){ $t1='INT(1)';
                }else if($t2=='INT(2)'){ $t1='INT(2)';
                }else if($t2=='INT(3)'){ $t1='INT(3)';
                }else if($t2=='INT(4)'){ $t1='INT(4)';
                }else if($t2=='INT(5)'){ $t1='INT(5)';
                }else if($t2=='INT(6)'){ $t1='INT(6)';
                }else if($t2=='INT(7)'){ $t1='INT(7)';
                }else if($t2=='INT(8)'){ $t1='INT(8)';
                }else if($t2=='INT(16)'){
                }else if($col->type=='int'||$col->type=='select'||$col->type=='hidden'){
                }else if($col->type=='varchar') {
	                if (!$col->len) $col->len=5;
	                $t1 .= "({$col->len})" ;
                }

                if($fila['pk']=='1') {
                    $t1 .= " PRIMARY KEY AUTOINCREMENT" ;
                    $t2 .= " PRIMARY KEY AUTOINCREMENT" ;
                }
                if ($fila['notnull']=='1') {
                    $t1.= ' NOT NULL';
                    $t2.= ' NOT NULL';
                }
                if($col->attribute) $t1.=($col->attribute);
                $m1 = md5(implode(str_split(trim($t1))));
                $m2 = md5(implode(str_split(trim($t2))));
                if ( trim($m1) == trim($m2) ){ 

                }else{

                  echo ' <span style="color:var(--blue);font-size:.9em;font-weight:normal">&nbsp; <span class="label label-warning" style="color:var(--red);">FAIL: ['.$t1.'] != ['.$t2.']</span>';  
                  echo "<br />Update: <span class=\"label\">ALTER TABLE {$this->tablename} CHANGE {$col->fieldname} ".$this->create_column($col).';</span></span>';

                  $this->sql_exec("ALTER TABLE {$this->tablename} CHANGE {$col->fieldname} ".$this->create_column($col).";");
                  $recreate = true;
                }
              $col->check = true;
              break;
            }
          }    
          echo ' '.($col->check) ? '<span class="label label-success"><i class="fa fa-check"></i></span>':' <span class="label label-error"><i class="fa fa-close"></i></span>';
          echo '<br />';
        }

        foreach( $this->cols as $col) {
          if(!$col->check  && !$col->calculated){
            echo "<br />New column: <span class=\"label\">ALTER TABLE {$this->tablename} ADD COLUMN ".$this->create_column($col).';</span>';
            $this->sql_exec("ALTER TABLE {$this->tablename} ADD COLUMN ".$this->create_column($col).';');
            $recreate = true;
          }
        }



      }else{
        echo "<br />Create table: <span class=\"label\">".$this->create_table().'</span><br />';
        Table::sqlExec($this->create_table());
        if($this->events && method_exists($this->events, 'OnAfterCreate')){
          $this->events->OnAfterCreate($this);
        } 
      }

      if($recreate){

        echo $this->create_table();

      }

      echo '</pre>';
    }
    
    public function create_column($col){
      $r = $col->fieldname;
      if      ($col->type=='varchar')  $r .=  " VARCHAR({$this->str_sql_len($col->len,15)})";
      else if ($col->type=='select' )  $r .=  $col->len>5
                                           ?  " VARCHAR({$this->str_sql_len($col->len,15)})"
                                           :  ' INTEGER'; //" INT({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='int'    )  $r .=  ' INTEGER'; //" INT({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='hidden' )  $r .=  ' INTEGER'; //" INT({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='textarea') $r .=  ' TEXT'; 
      else if ($col->type=='timestamp')$r .=  ' TIMESTAMP'; // NOT NULL DEFAULT CURRENT_TIMESTAMP'; 
      else if ($col->type=='datetime') $r .=  ' DATETIME'; // NOT NULL DEFAULT CURRENT_TIMESTAMP'; 
      else if ($col->type=='date')     $r .=  ' VARCHAR(10)'; 
      else if ($col->type=='decimal')  $r .=  " DECIMAL({$this->str_sql_len($col->len,8)},{$this->str_sql_len($col->precission,2)})"; 
      else if ($col->type=='file')     $r .=  ' VARCHAR(200)'; 
      else if ($col->type=='ccc')      $r .=  ' VACHAR(23)'; 
      else if ($col->type=='color')    $r .=  ' VARCHAR(12)'; 
      else if ($col->type=='time')     $r .=  ' VARCHAR(8)'; 
      else if ($col->type=='progress') $r .=  ' INTEGER'; 
      else if ($col->type=='bool')     $r .=  ' INTEGER'; 
      else if ($col->type=='unixtime') $r .=  ' INTEGER DEFAULT (unixepoch())'; 
      if($this->pk->fieldname==$col->fieldname) 
                                      $r .=  ' PRIMARY KEY AUTOINCREMENT';
      else {
        //if($col->attribute)    $r .= " {$col->attribute}";               // 'UNSIGNED' 
       // if($col->unsigned || $this->pk->fieldname==$col->fieldname)     $r .= ' UNSIGNED ';
        if($col->not_null || $this->pk->fieldname==$col->fieldname)     $r .= ' NOT NULL';

        if(isset($col->default_value)){
            if     ($col->default_value=='current_timestamp()') $r .= " DEFAULT CURRENT_TIMESTAMP";
            else if($col->type=='datetime') $r .= " DEFAULT {$col->default_value}"; // timestamp  CURRENT_TIMESTAMP
            else if($col->type!='textarea') $r .= " DEFAULT '{$col->default_value}'"; // timestamp  CURRENT_TIMESTAMP
        }
      }
      return $r;
    }

/*

//Change type column datetime to unix timestamp (INTEGER) in sqlite

-- 1. Crear tabla temporal de respaldo
CREATE TABLE CLI_USER_KEYS_BAK AS SELECT * FROM CLI_USER_KEYS;

-- 2. Borrar tabla original
DROP TABLE CLI_USER_KEYS;

-- 3. Crear tabla original con la estructura correcta
CREATE TABLE CLI_USER_KEYS (
    id_user INTEGER PRIMARY KEY, 
    device_id VARCHAR(64) NOT NULL, 
    device_name VARCHAR(255) NOT NULL,                  
    sign_public_key VARCHAR(512) NOT NULL, 
    enc_public_key VARCHAR(512) NOT NULL, 
    user_agent VARCHAR(512) NOT NULL, 
    last_used_at INTEGER DEFAULT (unixepoch()), 
    ACTIVE INTEGER NOT NULL
);

-- 4. Copiar datos convirtiendo datetime a timestamp Unix
INSERT INTO CLI_USER_KEYS (
    id_user, 
    device_id, 
    device_name, 
    sign_public_key, 
    enc_public_key, 
    user_agent, 
    last_used_at, 
    ACTIVE
)
SELECT 
    id_user,
    device_id,
    device_name,
    sign_public_key,
    enc_public_key,
    user_agent,
    last_used_at,
    ACTIVE
FROM CLI_USER_KEYS_BAK;


CREATE TABLE NSTR_BOOKMARKS_BAK AS SELECT * FROM NSTR_BOOKMARKS;
INSERT INTO NSTR_BOOKMARKS (
    id, 
    user_id, 
    event_id, 
    event_pubkey, 
    event_content, 
    event_created_at, 
    created_at
)
SELECT 
    id, 
    user_id, 
    event_id, 
    event_pubkey, 
    event_content, 
    event_created_at, 
    created_at
FROM NSTR_BOOKMARKS_BAK
*/


    public function create_table(){
      //$r  = "\nDROP TABLE IF EXISTS {$this->tablename};\n";
      $r = "CREATE TABLE {$this->tablename}(\n";
      $cols = [];
      foreach( $this->cols as $col) { 
        if(!$col->calculated) 
        $cols[] = $this->create_column($col);
      }
      
      $r .= implode(",\n",$cols);
      $r .= ")\n";
            return $r;
    }
    
  }
