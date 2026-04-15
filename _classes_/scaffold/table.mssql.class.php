<?php  

  @include('../../configuration.mssql.php');





  define('CLASS_TABLE_MSSQL_LOADED',true); //'wysihtml5');


  class TableMsSql extends Table{
    
    protected $db_instance;
    private $resource;
    public $error;
    public $driver = 'mssql';
    private $errors = array();
    private $sqls = array();

    function __construct($tablename=''){
      parent::__construct($tablename);
      $this->db_instance=mssql_connect(CFG::$vars['mssql']['host'].':'.CFG::$vars['mssql']['port'],
                                       CFG::$vars['mssql']['user'],
                                       CFG::$vars['mssql']['password']) or die("Connect failed");
    }

    function __destruct(){
    }

    public function getFieldValue($fieldName,$table='',$where=''){
      if(strpos($fieldName,'SELECT')===0)
        $sql = $fieldName;  
      else
        $sql = "SELECT {$fieldName} RESULT FROM {$table} $where";
      //echo($sql);
      $res = $this->sql_query($sql);
      if ($res){
        $row = $this->sql_fetch($res);
        if($row){
         //echo ' -> '.$row['RESULT'];
          return current($row); //$row['RESULT']; 
        }
      } 
      return false;
    }

    public function sql_query($sql){
        $q = mssql_query($sql, $this->db_instance);
        if (mssql_num_rows($q)) {
             $this->error = false;
             $this->sqls[] = $sql;
             return $q;
        }else{
             $this->errors[] = 'Algo no va bien';
             return false;
        }
    }

    public function sql_test(){
      echo 'test';
    }

    public function sql_fetch($query){
        return mssql_fetch_assoc($query);
    }

    public function recordCount($where='',$field=false,$table=false)   {
      $total=0;
      if(strpos($where,'SELECT')===0){
        $sql = $where;  
      }else{
        if(!$table) $table = $this->tablename;
        if(!$field) $field = $this->pk->fieldname;
        $sql = "SELECT count('{$field}') TOTAL FROM {$table} $where";  
      }
      //$this->debug(__LINE__."SELECT count('{$field}') total FROM {$table} $where",true); //,true);
      $res = $this->sql_query($sql);
      if ($res){
        $row = $this->sql_fetch($res);
        //$this->debug($row);
        if($row){
          //$this->debug( 'Total '.$row['TOTAL'] );       
          return $row['TOTAL']; 
        }//else 
          //$this->debug( __LINE__.' Total 0' );       
      } 
      //else 
        //$this->debug( __LINE__.' Total 0' );       
    }


    public function lastInsertId(){ 
   
    }

    public function nextInsertId($table=false){ 
      
    }
    

    
    public function toarray($arrayname,$tablename,$fieldkey='',$fieldname='',$where='',$force=true){
      if(!$arrayname) $arrayname=$tablename;
      if ($force) $_SESSION['_CACHE']['values'][$arrayname] = false;
      if (is_array($_SESSION['_CACHE']['values'][$arrayname])) return $_SESSION['_CACHE']['values'][$arrayname]; 
      if(strpos(trim($tablename),'SELECT')===0) $query = $this->sql_query($tablename);  
                                    else  $query = $this->sql_query('SELECT '.$fieldkey.' AS ID,'.$fieldname.' AS NAME FROM '.$tablename.' '.$where);
      $_SESSION['_CACHE']['values'][$arrayname] = array();
      while ($row = $this->sql_fetch($query)) { $_SESSION['_CACHE']['values'][$arrayname][$row['ID']] = $row['NAME']; }
      return $_SESSION['_CACHE']['values'][$arrayname];
    }


    public function asArray($arrayname,$sql,$force=true){
      if(!$arrayname) $arrayname=md5($tablename);
      //if ($force) 
        $_SESSION['_CACHE']['values'][$arrayname] = false;
      if (is_array($_SESSION['_CACHE']['values'][$arrayname])) return $_SESSION['_CACHE']['values'][$arrayname]; 
      $query = $this->sql_query($sql);
      $_SESSION['_CACHE']['values'][$arrayname] = array();
      if($query) while ($row = $this->sql_fetch($query)) { $_SESSION['_CACHE']['values'][$arrayname][$row['ID']] = $row['NAME']; }
      return $_SESSION['_CACHE']['values'][$arrayname];
    }

    public function sql_currentdate(){
      return 'SYSDATE';
    } 

    public function str_select($_page_start){
        if($_page_start<1) $_page_start = 1;
        if($_page_start>1) $_page_start = $_page_start+1;  //??????????
        $_page_num_items = $this->page_num_items -1;

        if     ($this->output=='group') $this->orderby = $this->field_group_order->fieldname;
        else if(!$this->orderby)        $this->orderby = $this->pk->fieldname;

        $sql = ';WITH My_CTE AS '
              . '('
              . '     SELECT '.$this->str_SqlFields().', ROW_NUMBER() OVER(ORDER BY '.$this->orderby.') AS row_number'
              . '     FROM '.$this->tablename;

        if($this->field_key) { 
                $sql  .= ' WHERE '.$this->field_key.'='.$this->field_value;
                if($this->filter) $sql  .= ' AND '.$this->filter;
        }else{
                if($this->filter) $sql  .= ' WHERE '.$this->filter;
        }
        if($this->groupby)  $sql  .= ' GROUP BY '.$this->groupby;

        $sql .= ') '
             . 'SELECT '.$this->str_SqlFields() . ' FROM  My_CTE '
             . 'WHERE row_number BETWEEN '.$_page_start.'  AND '.($_page_start+$_page_num_items)
        ;
        return $sql;
    }

    public function format_value($col,$value){
//      if($col->type=='date') return "{$value}";
                        //else 
                          return $value;
    }

    public function formatFieldname($col) {
      
      if($col->cast) 
        return 'cast ('.$col->fieldname.' as nvarchar('.($col->len?$col->len:'150').')) as '.$col->fieldname;
      if($col->binary) 
        return 'CAST(CAST('.$col->fieldname.' AS VARBINARY(MAX)) AS VARCHAR(MAX)) as '.$col->fieldname; 

      switch($col->type) {
        case 'date':
        case 'float':
        case 'enum':
        case 'color':
        case 'textarea':
        case 'file':
        case 'datetime':
        case 'time':
        case 'varchar':
        case 'select':
        case 'int':
        case 'bool':
        case 'decimal':
          return $col->fieldname; 
          break;
        default:
          return $col->fieldname; 
      }
    }


    private function str_sql_len($len,$deflen){
    }

    private function str_new_field($field){
      $type=strtolower($field['DATA_TYPE']);
      $len=$field['CHARACTER_MAXIMUM_LENGTH'] ? $field['CHARACTER_MAXIMUM_LENGTH'] : $field['NUMERIC_PRECISION'];
      $field_var_name = strtolower($field['COLUMN_NAME']);
      if($type == 'uniqueidentifier') $type = 'varchar';
      if($type == 'nvarchar') $type = 'varchar';
      if($type == 'int')      $type = 'int';
      if($type == 'float')    $type = 'varchar';
      if($type == 'datetime') {
        $type = 'varchar';
        $len = 20;
      }
      if($type == 'bit') {
        $type = 'varchar';
        $len = 1;
      }
      $r  = '';
      $r .= '$'.$field_var_name.' = new Field();'."\n";
      $r .= '$'.$field_var_name.'->type      = \''.$type.'\';'."\n";
      if($len) $r .= '$'.$field_var_name.'->len       = '.$len.';'."\n";
      $r .= '$'.$field_var_name.'->fieldname = \''.$field['COLUMN_NAME'].'\';'."\n";
      $r .= '$'.$field_var_name.'->label     = \''.str_replace(array('_id','_lv','_'),array('','',' '),ucwords($field_var_name)).'\';'."\n";  
      if ($field['COLUMN_ID']>1){
        $r .= '$'.$field_var_name.'->editable  = true ;'."\n";
        $r .= '$'.$field_var_name.'->sortable  = true;'."\n";
      }
      if($type == 'text') $r .= '$'.$field_var_name.'->hide  = true;'."\n";
      $r .= '$tabla->addCol($'.$field_var_name.');'."\n\n";
      //$r = print_r($field,true);
      return $r."\n\n";
    }

    public function create($filename){

      echo '<style>code, code span{font-size:9px !important;line-height:.9em;font-family:Monaco;height:110px;overflow:auto;}</style><pre style="">';
      $cols  =  $this->getFieldValue("SELECT COUNT(COLUMN_NAME) TOTAL FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->tablename}'");
      //echo "SELECT COUNT(COLUMN_NAME) TOTAL FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '{$this->tablename}'\n";
                                                 //ALL_TAB_COLUMNS
      if ($cols){
        $num = $this->recordCount("SELECT COUNT(COLUMN_NAME) TOTAL FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->tablename}'"); 
        $contenido  = '/* Auto created */'."\n";
        $contenido .= "\n";
        $contenido .= '$tabla = new TableMsSql(\''.$this->tablename.'\');'."\n";
        $contenido .= "\n";
        
        $res = $this->sql_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->tablename}'");

        while ($fila = $this->sql_fetch($res)){ //  print_r($fila);
          $field_var_name = strtolower($fila['COLUMN_NAME']);
          if($field_var_name==CREATED_BY||$field_var_name==CREATION_DATE||$field_var_name==LAST_UPDATE_DATE||$field_var_name==LAST_UPDATED_BY){
            $whocols = true;
          }else if($field_var_name==ACTIVE){
            $activecol = true;
          }else{
            $contenido .= $this->str_new_field($fila);
          }
        }
        
        $contenido .= '$tabla->name = \''.$this->tablename.'\';'."\n";
        $contenido .= '$tabla->title = \''.str_replace('_','',ucwords($this->tablename)).'\';'."\n";
        $contenido .= '$tabla->verbose=false;'."\n";
        $contenido .= '$tabla->output=\'table\';'."\n";
        $contenido .= '$tabla->page = $page;'."\n";
        $contenido .= '$tabla->page_num_items = 10;'."\n";
        $contenido .= '$tabla->show_empty_rows = true;'."\n";
        $contenido .= '$tabla->show_inputsearch =false;'."\n";
        if($whocols)$contenido .= '$tabla->addWhoColumns();'."\n";
        if($activecol)$contenido .= '$tabla->addActiveCol();'."\n";
        $contenido .= "\n";
        $contenido .= '$tabla->perms[\'delete\'] = Administrador();'."\n";
        $contenido .= '$tabla->perms[\'edit\']   = Administrador();'."\n";
        $contenido .= '$tabla->perms[\'add\']    = Administrador();'."\n";
        $contenido .= '$tabla->perms[\'setup\']  = false;'."\n";
        $contenido .= '$tabla->perms[\'reload\'] = true;'."\n";
        $contenido .= '$tabla->perms[\'filter\'] = true;'."\n";
        $contenido .= '$tabla->perms[\'view\']   = true;'."\n";
        $contenido .= "\n";
        $event_error_code = '0'; //($this->recordCount()<1) ? '0' : '5';
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
        $contenido .= "}\n";
        $contenido .= "\$tabla->events = New {$this->tablename}Events();\n";
        $contenido .= "\n";
        $contenido .= "\n";
        echo 'Archivo creado: '.$filename; //.'<pre style="font-size:10px;font-family:Monaco;">';
        highlight_string('<'."?php \n".$contenido.'?'.'>');
        create_php_file($filename,$contenido);
        if ($_SESSION['message_error']) echo $_SESSION['message_error'];
        $_SESSION['message_error'] = false;
      }else{
        echo "no cols\n";
      }
      echo '</pre>';





    }

    public function check_table(){

    }

    public function showInfo(){

    }

    public function create_column($col){
    }

    public function create_table(){

    }
    
  }
