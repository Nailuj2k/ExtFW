<?php  

if(!defined('CLASS_TABLE_ORACLE_LOADED')) {
  //@include('../../configuration.oracle.php');
//  include_once(SCRIPT_DIR_CLASSES.'/exceptions/IException.php');
//  include_once(SCRIPT_DIR_CLASSES.'/exceptions/CustomException.php');
 // include_once(SCRIPT_DIR_CLASSES.'/exceptions/OracleConnectionException.php');
  include_once(SCRIPT_DIR_CLASSES.'/db/db.oracle.php');   

  define('CLASS_TABLE_ORACLE_LOADED',true); //'wysihtml5');
}


  class TableOracle extends Table{
    
    protected $db_instance;
    private $resource;
    public $error;
    public $prefix = '';
    public $driver = 'oracle';
    private $errors = array();
    private $_sqls = array();

    function __construct($tablename=false,$external=false){
      parent::__construct($tablename);
      
      $db_instance = OracleConnection::singleton();
      $db_instance->connect($external);  //external is an array oracle configuration, or false by default
      $this->resource = $db_instance->getResource();
      
    }

    function __destruct(){
      oci_close($this->resource);
    }
/*
    public function getFieldValue($fieldName,$table='',$where=''){
      if(strpos($fieldName,'SELECT')===0)
        $sql = $fieldName;  
      else
        $sql = "SELECT {$fieldName} RESULT FROM {$this->prefix}{$table} $where";
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
    public function getRow($sql){
      //echo($sql);
      $res = $this->sql_query($sql);
      if ($res){
        $row = $this->sql_fetch($res);
        if($row){
          //echo ' -> '.$row['RESULT'];
          // return $row['RESULT']; 
          return $row; 
        }
      } 
      return false;
    }
    */
    public function sql_query($sql){
      
      //$this->debug( $sql , true);
      $handle = oci_parse($this->resource, $sql);
      $r = oci_execute($handle);
      if ($r){
        $this->error = false;
        $this->_sqls[] = $sql;
       
        return $handle;

      }else {
        $e = oci_error($handle);  // Para errores de oci_execute, pase el gestor de sentencia
        $this->error = 'ERROR: '.$e['code'].': '.htmlentities($e['message'])
                     . "\n<pre>\n"
                     . htmlentities($e['sqltext'])
                     . sprintf("\n%".($e['offset']+1)."s", "^")
                     . "\n</pre>\n";
        $this->errors[] = $this->error;
        return false;
      }
      
    }

    public function sql_test(){
      echo 'test';
    }

    public function sql_fetch($query){
      return oci_fetch_array($query, OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS);
      //return oci_fetch_assoc($query); //, OCI_ASSOC+OCI_RETURN_NULLS);  //CHECK this
    }

    public function recordCount($where='',$field=false,$table=false)   {
      $total=0;
      if(strpos($where,'SELECT')===0){
        $sql = $where;  
      }else{
        if(!$table) $table = $this->tablename;
        if(!$field) $field = $this->pk->fieldname;
        $sql = "SELECT count('{$field}') TOTAL FROM {$this->prefix}{$table} $where";  
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

    /*
    public function lastInsertId(){ 
     // return false; 
      return $this->getFieldValue("SELECT MAX({$this->pk->fieldname}) FROM {$this->prefix}{$this->tablename}");

    }

    public function nextInsertId($table=false){ 
      return $this->getFieldValue("SELECT {$this->prefix}{$this->tablename}_SEQ.NEXTVAL AS NEXT  FROM dual");
    }
    */
      
    public function toarray($arrayname,$tablename,$fieldkey='',$fieldname='',$where='',$force=true){
      //if(!$arrayname) $arrayname=$tablename;
      //if ($force) $_SESSION['_CACHE']['values'][$arrayname] = false;
      //if (is_array($_SESSION['_CACHE']['values'][$arrayname])) return $_SESSION['_CACHE']['values'][$arrayname]; 
      if(strpos(trim($tablename),'SELECT')===0) $query = $this->sql_query($tablename);  
                                    else  $query = $this->sql_query('SELECT '.$fieldkey.' AS ID,'.$fieldname.' AS NAME FROM '.$this->prefix.$tablename.' '.$where);
      /*$_SESSION['_CACHE']['values'][$arrayname]*/ $ret = array();
      while ($row = $this->sql_fetch($query)) { /*$_SESSION['_CACHE']['values'][$arrayname][$row['ID']]*/ $ret[$row['ID']] = $row['NAME']; }
      return $ret;  //$_SESSION['_CACHE']['values'][$arrayname];
    }


    public function asArray($sql,$col=false,$key=false){
      $query = $this->sql_query($sql);
      $ret = array();
      if($query) 
      while ($row = $this->sql_fetch($query)) { 
          if ($key!==false)  $ret[$row[$key]] = $col ? $row[$col] : $row;   
          else               $ret[]           = $col ? $row[$col] : $row; 
      }
      return $ret; 
    }


    public function sql_currentdate(){
        //            return "to_char(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') "; //                        "'date('DD-MM-YYYY HH24:MI:SS')'";
        //      return "'".date(DATE_FORMAT_ORACLE)."'";
       //    return "'".date('d-mm-Y')."'";
      return 'SYSDATE';
    } 

    public function str_select($_page_start){
      if($_page_start<1) $_page_start = 1;
      if($_page_start>1) $_page_start = $_page_start+1;  //??????????
      $_page_num_items = $this->page_num_items -1;

      if     ($this->output=='group') $this->orderby = $this->field_group_order->fieldname;
      else if(!$this->orderby)        $this->orderby = $this->pk->fieldname;


      $sql   = 'SELECT * FROM ( ';      
      //FIX no repeat orderby  
      $sql  .= ' SELECT  '.$this->str_SqlFields().', row_number() OVER(ORDER BY '.$this->orderby.') rnk ';
      //OK      $sql  .= ' SELECT  '.$this->str_SqlFields().', row_number() OVER(ORDER BY '.$this->orderby.','.$this->pk->fieldname.') rnk ';
      $sql  .= ' FROM '.$this->prefix.$this->tablename;

      
      if($this->field_key) { 
      
        $sql  .= ' WHERE '.$this->field_key.'='.$this->field_value;
        
        if($this->filter) 
          $sql  .= ' AND '.$this->filter;
        
      }else{
        
        if($this->filter) 
          $sql  .= ' WHERE '.$this->filter;

      }
     
      if($this->groupby) 
        $sql  .= ' GROUP BY '.$this->groupby;
     
      $sql  .= ') WHERE rnk BETWEEN '.$_page_start.' AND '.($_page_start+$_page_num_items);

      return $sql;
    
    }

    public function format_value($col,$value){
       //      if($col->type=='date') return "{$value}";
                        //else 
                          return $value;
    }

    public function formatFieldname($col) {
      switch($col->type) {
        case 'date':
        //return "to_char({$col->fieldname}, 'DD-MM-YYYY HH24:MI:SS') as {$col->fieldname}";        
          return "to_char({$col->fieldname}, 'DD-MM-RRRR') as {$col->fieldname}";        
          break;
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

    /*
        SELECT * FROM 
          ( SELECT ISSUE_ACTION_ID,ISSUE_ID,USER_ID_ASSIGNED,ACTION_DATE,NOTES,
                    CREATED_BY,CREATION_DATE,LAST_UPDATED_BY,LAST_UPDATE_DATE, 
                    row_number() OVER(ORDER BY ISSUE_ID) rnk  FROM ISS_ISSUES_ACTIONS
          ) 
          WHERE ISSUE_ID=111 AND rnk BETWEEN 0 AND 5
    */
    /**
      SELECT * FROM (
      SELECT  ID, GFH, TELEFONO, USUARIO_MOD, FECHA_MOD, dense_rank() OVER(ORDER BY telefono,id) rnk 
      FROM AD_MAP_GFH_TFNO)
      WHERE rnk BETWEEN 10 AND 20

      SELECT * FROM (
      SELECT  ID, GFH, TELEFONO, USUARIO_MOD, FECHA_MOD, row_number() OVER(ORDER BY telefono) rnk 
      FROM AD_MAP_GFH_TFNO)
      WHERE rnk BETWEEN 10 AND 20


      SELECT * FROM (  
      SELECT  tag_id,name,description,color, row_number() OVER(ORDER BY tag_id) rnk  
      FROM ISS_TAGS) 
      WHERE rnk BETWEEN 0 AND 12

    **/
    private function str_sql_len($len,$deflen){ return ($len) ? $len : $deflen; }

    private function str_new_field($field){
      $type=strtolower($field['DATA_TYPE']);
      $len=$field['DATA_LENGTH'];
      $field_var_name = strtolower($field['COLUMN_NAME']);
      if($type == 'text')     $type = 'textarea';
      if($type == 'smallint') $type = 'int';
      if($type == 'hidden')   $type = 'int';
      if($type == 'date')     $type = 'datetime';
      if($type == 'timestamp')$type = 'timestamp';
      if($type == 'tinyint')  $type = 'int';
      if($type == 'enum')     {
        $len = '1';
        $type = 'int';
      }
      if($type == 'number')   $len = "'{$len}'";
      if($type == 'timestamp')$len = false;
      if($type == 'datetime') $len = false;
      if($type == 'date')     $len = false;
      if($type == 'varchar2') $type = 'varchar';
      if($type == 'number')   $type = 'int';
      
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
     // $res = $this->sql_query("SHOW FIELDS FROM {$this->tablename}");
      echo '<style>code, code span{font-size:9px !important;line-height:.9em;font-family:Monaco;height:110px;overflow:auto;}</style><pre style="">';
      $cols  =  $this->getFieldValue("SELECT COUNT(COLUMN_NAME) TOTAL FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'");
      //echo "SELECT COUNT(COLUMN_NAME) TOTAL FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '{$this->tablename}'\n";
                                                 //ALL_TAB_COLUMNS
      if ($cols){
        $num = $this->recordCount("SELECT COUNT(COLUMN_NAME) TOTAL FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'"); 
        $contenido  = '/* Auto created */'."\n";
        $contenido .= "\n";
        $contenido .= '$tabla = new TableOracle(\''.$this->tablename.'\');'."\n";
        $contenido .= "\n";
        
        $res = $this->sql_query("SELECT * FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'");

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
      //      $this->showInfo();
       //      return false;
      echo '<pre style="font-size:.8em;">';
     //// echo 'Tabla: '.$this->tablename;
      //$res = $this->sql_query("SELECT * FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '{$this->tablename}'");
      //if (!$res){       //OLD $res!==false){
      $cols  = $this->getFieldValue("SELECT COUNT(COLUMN_NAME) TOTAL FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'");
      //echo '<br />count: ['.$cols.']<br />';

      if ($cols){
        $res = $this->sql_query("SELECT * FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'");
        echo "DESCRIBE {$this->tablename} ({$cols})\n";
        $num = $this->recordCount("SELECT COUNT(COLUMN_NAME) TOTAL FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'"); 
        while ($fila = $this->sql_fetch($res)){ //  print_r($fila);
          echo $fila['COLUMN_NAME'].' ['.$fila['DATA_TYPE'].']  ['.$fila['DATA_LENGTH'].']';
          foreach( $this->cols as $col) {
            if($col->fieldname==$fila['COLUMN_NAME']){
             //if ($col->type=='date') $col->len=19;
             if(($col->type=='number'||$col->type=='varchar'||/*$col->type=='date'||*/$col->type=='int') &&$col->len>0 && !$col->calculated ){
              //echo '{'.$col->type.'}';
               $t1 = $this->create_column($col); //"{$col->type}({$col->len})" ;
                if($col->attribute)$t1.=' '.($col->attribute);
                if($col->type=='number') $t2 =  $fila['COLUMN_NAME'].' '.$fila['DATA_TYPE'].'('.$fila['DATA_PRECISION'].')';
                if($col->type=='int')    $t2 =  $fila['COLUMN_NAME'].' '.$fila['DATA_TYPE']; //.'('.$fila['DATA_LENGTH'].')';
                                    else $t2 =  $fila['COLUMN_NAME'].' '.$fila['DATA_TYPE'].'('.$fila['DATA_LENGTH'].')';
                
                //echo ' {'.$t1.'}{'.$t2.'}';
                if($t1 != $t2){
                  echo '<span class="label label-warning" style="font-size:.9em;font-weight:normal">FAIL: '.$t1.' != '.$t2.'</span>';  
                  echo "<br />Update: <span class=\"label\">ALTER TABLE {$this->prefix}{$this->tablename} MODIFY ".$this->create_column($col).';</span>';
                  $this->sql_query("ALTER TABLE {$this->prefix}{$this->tablename} MODIFY ".$this->create_column($col));
                }else{
                  //echo '<span class="label label-info" style="font-size:.9em;font-weight:normal">OK: '.$t1.' == '.$t2.'</span>';  
                }
              }
              $col->check = true;
              break;
            }
          }    
          echo ' '.($col->check) 
                   ? '<span class="label label-success" style="font-size:.9em;font-weight:normal">OK</span>' 
                   : '<span class="label label-important"style="font-size:.9em;font-weight:normal">NO</span>';
          echo '<br />';
        }
        foreach( $this->cols as $col) {
          if(!$col->check  && !$col->calculated){
            echo "<br />New column: <span class=\"label\">ALTER TABLE {$this->prefix}{$this->tablename} ADD ( ".$this->create_column($col).')</span>';
            $this->sql_query("ALTER TABLE {$this->prefix}{$this->tablename} ADD (".$this->create_column($col).')');
          }
        }
      }else{
        $echo = '<br />'.$this->create_table().'<br />';
        //        echo "<br />Create table: <span class=\"label\">".$this->create_table().'</span><br />';
        $this->sql_query($this->create_table());

        $this->sql_query('CREATE UNIQUE INDEX "'.$this->tablename.'_PK" ON "'.$this->prefix.$this->tablename.'" ("'.$this->pk->fieldname.'")');
        $this->sql_query('ALTER TABLE "'.$this->prefix.$this->tablename.'" ADD CONSTRAINT "'.$this->tablename.'_PK" PRIMARY KEY ("'.$this->pk->fieldname.'") ENABLE');
        $this->sql_query('ALTER TABLE "'.$this->prefix.$this->tablename.'" MODIFY ("'.$this->pk->fieldname.'" NOT NULL ENABLE)');
        $this->sql_query('CREATE SEQUENCE  "'.$this->tablename.'_SEQ" MINVALUE 1 MAXVALUE 999999999999999999999999999 INCREMENT BY 1 START WITH 1 NOCACHE  NOORDER  NOCYCLE');
        $this->sql_query('CREATE OR REPLACE TRIGGER "'.$this->tablename.'_BI" '
                        .'BEFORE INSERT ON '.$this->prefix.$this->tablename.' '
                        .'FOR EACH ROW '
                        .'BEGIN IF :NEW.'.$this->pk->fieldname.' IS NULL THEN SELECT '.$this->tablename.'_SEQ.NEXTVAL INTO :NEW.'.$this->pk->fieldname.' FROM dual; END IF; '
                        .'END;');
        $this->sql_query('ALTER TRIGGER "'.$this->tablename.'_BI" ENABLE');
        $this->sql_query('COMMIT');


        echo implode("\n", ($this->errors ? $this->errors : $this->_sqls) );

      }
      echo $echo.'</pre>';
    }


    public function showInfo(){
      $res = $this->sql_query("SELECT * FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->prefix}{$this->tablename}'");
      $num = $this->recordCount("SELECT COUNT(COLUMN_NAME) TOTAL FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = '{$this->tablename}'"); 
      if ($num > 0) { 
        ?><pre class="fix-code"><?php 
        $h=array();
        $h[0]='Column name'; //Field[string]                 
        $h[1]='Type'; //Type[blob]          
        $h[2]='Lenght'; //Null[string]
        //$h[3]='Null'; //Key[string]
        //$h[4]='Default'; //Default[blob]
        //$h[5]='Extra'; //Extra[string]
        $l = array();
        $l[0]=30;
        $l[1]=18;
        $l[2]=10;
        $l[3]=10;
        //$l[4]=10;
        //$l[5]=14;
        $alter='';
        $fn = array();
        $fn[0]='COLUMN_NAME';
        $fn[1]='DATA_TYPE';
        $fn[2]='DATA_LENGTH';
        $fn[3]='NULLABLE';
        /*
        $n[3]='';
        $n[4]='';
        $n[5]='';
        */
        $alter='';
        print('<B>'.t('filas').'</B>:'.$num."\n");
        $campos = count($h); 
        for ($n=0; $n<$campos; $n++){ echo str_pad($h[$n], $l[$n]); }   
        echo "\n";
        for ($n=0; $n<$campos; $n++){ echo str_pad('-', $l[$n],'-');  }   
        echo "\n";
        while ($fila = $this->sql_fetch($res)){ 
          //print_r($fila);

          for ($n=0; $n<$campos; $n++) { echo str_pad($fila[$fn[$n]], $l[$n],' ',($n==2)?STR_PAD_LEFT:STR_PAD_RIGHT); }
          echo "\n";  
        }
        ?></pre><?php 
      } 
    }


    /*
      DESC USER_TAB_COLUMNS
      Nombre                         Nulo     Tipo                                                            
      ------------------------------ -------- ---------------------------
      TABLE_NAME                     NOT NULL VARCHAR2(30)               
      COLUMN_NAME                    NOT NULL VARCHAR2(30)               
      DATA_TYPE                               VARCHAR2(106)              
      DATA_TYPE_MOD                           VARCHAR2(3)                
      DATA_TYPE_OWNER                         VARCHAR2(30)               
      DATA_LENGTH                    NOT NULL NUMBER                     
      DATA_PRECISION                          NUMBER                     
      DATA_SCALE                              NUMBER                     
      NULLABLE                                VARCHAR2(1)                
      COLUMN_ID                               NUMBER                     
      DEFAULT_LENGTH                          NUMBER                     
      DATA_DEFAULT                            LONG()                     
      NUM_DISTINCT                            NUMBER                     
      LOW_VALUE                               RAW(32 BYTE)               
      HIGH_VALUE                              RAW(32 BYTE)               
      DENSITY                                 NUMBER                     
      NUM_NULLS                               NUMBER                     
      NUM_BUCKETS                             NUMBER                     
      LAST_ANALYZED                           DATE                       
      SAMPLE_SIZE                             NUMBER                     
      CHARACTER_SET_NAME                      VARCHAR2(44)               
      CHAR_COL_DECL_LENGTH                    NUMBER                     
      GLOBAL_STATS                            VARCHAR2(3)                
      USER_STATS                              VARCHAR2(3)                
      AVG_COL_LEN                             NUMBER                     
      CHAR_LENGTH                             NUMBER                     
      CHAR_USED                               VARCHAR2(1)                
      V80_FMT_IMAGE                           VARCHAR2(3)                
      DATA_UPGRADED                           VARCHAR2(3)                
      HISTOGRAM                               VARCHAR2(15)               
    */
    public function create_column($col){
      $r = $col->fieldname;
      if      ($col->type=='varchar')  $r .=  " VARCHAR2({$this->str_sql_len($col->len,15)})";
      else if ($col->type=='select' )  $r .=  " NUMBER({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='int'    )  $r .=  " NUMBER";//({$this->str_sql_len($col->len,5)})";
      else if ($col->type=='decimal')  $r .=  " NUMBER({$this->str_sql_len($col->len,8)},{$this->str_sql_len($col->precission,2)})"; 
      else if ($col->type=='textarea') $r .=  ' VARCHAR2(4000)'; 
      else if ($col->type=='datetime') $r .=  ' DATE'; 
      else if ($col->type=='date')     $r .=  ' DATE'; //VARCHAR2(19)'; 
      else if ($col->type=='time')     $r .=  ' VARCHAR2(5)'; 
      else if ($col->type=='color')    $r .=  ' VARCHAR2(12)';
      else if ($col->type=='file')     $r .=  ' VARCHAR2(100)'; 
      else if ($col->type=='bool')     $r .=  ' NUMBER(1)'; 
      if($col->attribute)    $r .= " {$col->attribute}";               // 'UNSIGNED' 
      if($col->not_null)     $r .= " NOT NULL";
    // if($col->default_value&&$col->type!='textarea') $r .= " DEFAULT '{$col->default_value}'"; // timestamp  CURRENT_TIMESTAMP
    //if($this->pk->fieldname==$col->fieldname) $r .=  ' unsigned NOT NULL auto_increment';
      return $r;
    }

    public function create_table(){
      //$r  = "\nDROP TABLE IF EXISTS {$this->tablename};\n";
      $names = array();
      $r = "CREATE TABLE {$this->prefix}{$this->tablename}(\n";
      foreach( $this->cols as $col) { 
        if(!$col->calculated)
        $names[] =$this->create_column($col); 
      }
      //$r .= "PRIMARY KEY  (`".$this->pk->fieldname."`)\n";
      $r .= implode(",\n",$names);
      $r .= ")\n";
 
      if($this->events && method_exists($this->events, 'OnAfterCreate')){
        $this->events->OnAfterCreate($this);
      } 

      return $r;
    }
    
  }
