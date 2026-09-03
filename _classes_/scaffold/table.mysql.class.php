<?php 

  define('CLASS_TABLE_MYSQL_LOADED',true);

  class TableMysql extends Table{

    use MysqlConnection;   

    public $driver = 'mysql';
    // public $order;
    
    public function __construct($tablename=false,$external=false) {
        parent::__construct($tablename);
        if ($external===true)  
            self::connect_external();
        else 
            self::connect();
    }

    public function sql_query($sql){
        return self::sqlQuery($sql);
    }

    public function sql_exec($sql){
        return self::sqlExec($sql);
    }
    /*
    public function last_error(){
        return self::lastError();
    }

    public function get_error(){
        return self::getError();
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
        return self::getFieldValue($sql);
    }

    public function nextInsertId($table=false){ 
      if(!$table) $table = $this->tablename;
      $sql = "SHOW TABLE STATUS FROM ".CFG::$vars['db']['name']." LIKE '$table'";       //  SHOW TABLE STATUS FROM extralab_tienda LIKE 'CLI_INVOICES'
      $row = self::sqlQuery($sql,false);
      return $row[0]['Auto_increment'];
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

    public function asArray($sql,$col=false,$key=false){
      $query = $this->sql_query($sql);
      $ret = array();
      if($query) foreach($query as $row) { 
          if ($key!==false)  $ret[$row[$key]] = $col ? $row[$col] : $row;   
          else               $ret[]           = $col ? $row[$col] : $row; 
      }
      return $ret; 
    }

    public function mysqlOrderBy(){
      $ob = explode(' ',$this->orderby);
      if ( $this->colByName( $ob[0] )->type=='date' ) {
         $r = "DATE_FORMAT({$ob[0]}, '".DATE_FORMAT_MYSQL."') ";
         if (count($ob)>1) $r.= $ob[1];
         return $r;
      }else  return $this->orderby;
    }

    public function sql_currentdate(){
    //gmdate("Y-m-d H:i:s",time());
      return 'NOW()';          //    return 'CURDATE()';
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
        case 'ccc':
        case 'bool':
        case 'unixtime':
          return $col->fieldname; 
          break;
        case 'datetime':
          if ($col->fuzzydate)
             return "UNIX_TIMESTAMP({$col->fieldname}) AS {$col->fieldname}"; //DATE_FORMAT({$col->fieldname},'".DATETIME_FORMAT_MYSQL."') AS {$col->fieldname}";
          else
             return "DATE_FORMAT({$col->fieldname},'".DATETIME_FORMAT_MYSQL."') AS {$col->fieldname}";
        case 'date':
          return "DATE_FORMAT({$col->fieldname},'".DATE_FORMAT_MYSQL."') AS {$col->fieldname}";
        default:
          return $col->fieldname; 
      }
    }

    public function str_select($_page_start){
      $sql   = 'SELECT '.$this->str_SqlFields();
      $sql  .= ' FROM '.$this->tablename;
      if($this->filter)    $sql  .= ' WHERE '.$this->filter;
      if     ($this->output=='group') $sql  .= ' ORDER BY '.($this->orderby?$this->mysqlOrderBy():$this->field_group_order->fieldname);//.', NAME';
      else if($this->orderby)         $sql  .= ' ORDER BY '.$this->mysqlOrderBy();
     // $_SESSION['_CACHE'][$this->tablename]['orderby'] = $this->mysqlOrderBy();
      $sql .= " LIMIT  $_page_start,{$this->page_num_items}";
      return $sql;
    }

    public function format_value($col,$value){
      if($col->type=='date') return (!$value || $value=='00-00-0000' ?  'NULL' : "STR_TO_DATE('{$value}', '".DATE_FORMAT_MYSQL."')" );
      //IF SQLITE else if($col->type=='textarea') return Str::escape($value);
                        else return $value;
    }
 
    private function str_sql_len($len,$deflen){ return ($len) ? $len : $deflen; }
    
    private function str_new_field($field){
      $af = explode(  '(',  str_replace(array(')',' ','unsigned'), '', $field['Type'])  );
      $field_var_name = strtolower($field['Field']);
      $type=$af[0];
      $len=$af[1];
      if($type == 'text')      $type = 'textarea';
      if($type == 'mediumtext')$type = 'textarea';
      if($type == 'smallint')  $type = 'int';
      if($type == 'hidden')    $type = 'int';
      if($type == 'datetime')  $type = 'datetime';
      if($type == 'timestamp') $type = 'timestamp';
      if($type == 'tinyint')   $type = 'int';
      if($type == 'enum')      {
        $len = '1';
        $type = 'int';
      }
      if($type == 'mediumtext')  $len = "'medium'";
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
      if (isset($field['props'])){
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
        $contenido .= '$tabla = new TableMysql(\''.$this->tablename.'\');'."\n";
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
      echo 'Tabla: '.$this->tablename.'<br />';

      if($this->soft_delete){
         if($this->colByName('DELETED')===false){
            $this->addDeletedCol();
         }
      }

      $res = $this->sql_query("DESCRIBE {$this->tablename}");
      if ($res){
        foreach($res as $fila){  
          echo 'Columna: '.$fila['Field'].' '.$fila['Type'];
          if ($fila['Null']=='NO') echo' NOT NULL';
          if ($fila['Extra']) echo ' '.$fila['Extra'];
          
          foreach( $this->cols as $col) {
            // if($this->pk->fieldname==$col->fieldname)$col->attribute ='unsigned';
            if($col->fieldname==$fila['Field'] && !$col->calculated){
              //if($this->pk->fieldname==$col->fieldname) $col->attribute ='unsigned NOT NULL auto_increment';
              $col->attribute = '';
              if($col->unsigned || $this->pk->fieldname==$col->fieldname)  $col->attribute .= ' unsigned';
              if($col->not_null || $this->pk->fieldname==$col->fieldname)  $col->attribute .= ' NOT NULL';
              if($this->pk->fieldname==$col->fieldname)                    $col->attribute .= ' auto_increment';
              //if(($col->type=='varchar'||$col->type=='int')&&$col->len>0){
                if     ($col->type=='select')  $t1 = $col->len>5 ? 'varchar' : 'int' ;
                else if($col->type=='datetime')$t1 = 'datetime' ;
                else if($col->type=='hidden')  $t1 = 'int' ;
                else if($col->type=='progress')$t1 = 'int('.($col->len?$col->len:'4').')' ;
                else if($col->type=='textarea' && $col->len=='medium')$t1 = 'mediumtext' ;
                else if($col->type=='textarea')$t1 = 'text' ;
                else if($col->type=='bool')    $t1 = 'int(1)' ;
                else if($col->type=='unixtime')$t1 = 'int(15)' ;
                else if($col->type=='ccc')     $t1 = 'varchar(23)' ;
                else if($col->type=='file')    $t1 = 'varchar(200)' ;
                else if($col->type=='color')   $t1 = 'varchar(12)' ;
                else if($col->type=='decimal') {
                  $ta = explode(',',$col->len);
                  $t1 = "decimal({$ta[0]},{$this->str_sql_len($col->precission,2)})" ;

                }else                           $t1 = "{$col->type}" ;
                 if($col->type=='varchar'||$col->type=='int'||$col->type=='select'||$col->type=='hidden') {
	                if (!$col->len) $col->len=5;
	                $t1 .= "({$col->len})" ;
                }
                if($col->attribute) $t1.=($col->attribute);
                if(isset($col->default_value)&&$col->type!='textarea') {
                  if($fila['Default'] != $col->default_value) $t1.= " DEFAULT {$col->default_value}";  //20131017
                }//$r .= " DEFAULT '{$col->default_value}'"; // timestamp  CURRENT_TIMESTAMP
                $t2 = $fila['Type'];
                if ($fila['Null']=='NO') $t2.= ' NOT NULL';
                if ($fila['Extra']) $t2.= ' '.$fila['Extra'];
              $m1 = md5(implode(str_split(trim($t1))));
              $m2 = md5(implode(str_split(trim($t2))));
              //echo '<span class="label label-info" style="font-size:.9em;font-weight:normal">['.similar_text($t1,$t2).']['.strlen(trim($t1)).'-'.strlen(trim($t2)).']</span>';  
                if ( trim($m1) == trim($m2) ){ //  similar_text($t1,$t2) == strlen($t2)){
              //   echo '<span class="label label-info" style="font-size:.9em;font-weight:normal">OK: '.$t1.' == '.$t2.'</span>';  
                }else{
                  echo '<span class="label label-warning" style="font-size:.9em;font-weight:normal">FAIL: ('.$t1.') != ('.$t2.')</span>';  
                  echo "<br />Update: <span class=\"label\">ALTER TABLE {$this->tablename} CHANGE {$col->fieldname} ".$this->create_column($col).';</span>';
                  $this->sql_exec("ALTER TABLE {$this->tablename} CHANGE {$col->fieldname} ".$this->create_column($col).";");
                }
              //}
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
          }
        }
      }else{
        echo "<br />Create table: <span class=\"label\">".$this->create_table().'</span><br />';
        $this->sql_exec($this->create_table());
        if($this->events && method_exists($this->events, 'OnAfterCreate')){
          $this->events->OnAfterCreate($this);
        } 
      }
      echo '</pre>';
    }
    
    public function create_column($col){
      $r = $col->fieldname;
      if      ($col->type=='varchar')  $r .=  " VARCHAR({$this->str_sql_len($col->len,15)})";
      else if ($col->type=='select' )  $r .=  $col->len>5
                                           ?  " VARCHAR({$this->str_sql_len($col->len,15)})"
                                           :  " INT({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='int'    )  $r .=  " INT({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='hidden' )  $r .=  " INT({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='textarea' && $col->len=='medium') $r .=  ' MEDIUMTEXT'; 
      else if ($col->type=='textarea') $r .=  ' TEXT'; 
      else if ($col->type=='timestamp')$r .=  ' timestamp'; // NOT NULL DEFAULT CURRENT_TIMESTAMP'; 
      else if ($col->type=='datetime') $r .=  ' datetime'; // NOT NULL DEFAULT CURRENT_TIMESTAMP'; 
      else if ($col->type=='date')     $r .=  ' DATE'; 
      else if ($col->type=='decimal')  $r .=  " decimal({$this->str_sql_len($col->len,8)},{$this->str_sql_len($col->precission,2)})"; 
      else if ($col->type=='file')     $r .=  ' varchar(200)'; 
      else if ($col->type=='ccc')      $r .=  ' varchar(23)'; 
      else if ($col->type=='color')    $r .=  ' varchar(12)'; 
      else if ($col->type=='time')     $r .=  ' TIME'; 
      else if ($col->type=='progress') $r .=  ' int(4)'; 
      else if ($col->type=='bool')     $r .=  ' int(1)'; 
      else if ($col->type=='unixtime') $r .=  ' int(15) DEFAULT UNIX_TIMESTAMP()'; 
      //if($col->attribute)    $r .= " {$col->attribute}";               // 'UNSIGNED' 
      if($col->unsigned || $this->pk->fieldname==$col->fieldname)     $r .= ' unsigned ';
      if($col->not_null || $this->pk->fieldname==$col->fieldname)     $r .= ' NOT NULL';
      if($this->pk->fieldname==$col->fieldname) $r .=  ' auto_increment';
      if(isset($col->default_value)){
          if     ($col->type=='datetime') $r .= " DEFAULT {$col->default_value}"; // timestamp  CURRENT_TIMESTAMP
          else if($col->type!='textarea') $r .= " DEFAULT '{$col->default_value}'"; // timestamp  CURRENT_TIMESTAMP
      }
      return $r;
    }

    public function create_table(){
      //$r  = "\nDROP TABLE IF EXISTS {$this->tablename};\n";
      $r = "CREATE TABLE {$this->tablename}(\n";
      foreach( $this->cols as $col) { 
        if(!$col->calculated) $r .= $this->create_column($col).",\n"; 
      }
      $r .= "PRIMARY KEY  (`".$this->pk->fieldname."`)\n";
      $r .= ")ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";
      return $r;
    }
    
  }
