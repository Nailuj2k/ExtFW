<?php


    // Muestra las filas de una tabla, con enlaces para editar, eliminar, añadir y actualzar
    // Paginador todavía no


    if       (CFG::$vars['db']['type']=='mysql') {
        class BaseTable {
            use MysqlConnection;   
        }
    }else if (CFG::$vars['db']['type']=='sqlite') {
        class BaseTable {
            use SQLiteConnection;   
        }
    }

    class Table extends BaseTable { 
    
        // use MysqlConnection;

        public $tablename;
        public $pk;              // Primary key Field Object
        public $cols = array();  // Array of Field objects
        private $rows;            // Array devuelto por sqlquery
        public $fieldsets = array();    // Array of strings
        public $state = 'browse';
        public $parent_key   = false;  // Foreing Key for act as detail table - false or fieldname
        public $parent_value = false;  // Foreing Key value for act as detail table - false or field value
        public $verbose =true;
        public $show_empty_rows = true;
        public $page_num_items = 5;
        public $detail_tables = array();
        public static $css = '/'.SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/css/style.'.CRUD_CSS_STYLE.'.css?ver=1.1.4';

        public function __construct($tablename=false){
           // parent::__construct();
            $this->tablename = $tablename;
            $this->perms['edit']=true;
        }
        
        // Helper class function.
        public static function query2array($sql){
            $rows = Table::sqlQuery($sql);
            $result = array();
            foreach($rows as $row)$result[] = $row;
            return $result;
        }

        // Añade un objeto Field, es decir, una columna
        public function addCol($col) { 
         // if(/*$col->fieldname=='id' &&*/ $this->pk===false) $this->pk=$col;
         // if($col->pk && $col->type===false) $col->type='int';
            $this->cols[$col->fieldname] = $col;
            $col->owner = $this;
            if ($col->pk == true) {
                $col->required = true;
                $this->pk = $col;
            }
        }

        public function setState($state){ $oldState = $this->state; $this->state = $state; return $oldState; }

        public function addFieldset($name, $legend=false, $type='tab') { 
          if (!isset($this->fieldsets[$name])) $this->fieldsets[$name] = new fieldset($name,($legend?$legend:$name)); 
          $this->fieldsets[$name]->displaytype = $type; 
        }
        
        // Devuelve una fila
        // $id - Valor de la clave primaria cuya fila solicitamos
        private function getRow($id){
            if($id) $row = Table::sqlQuery('SELECT '.implode( ',', array_keys($this->cols)).' FROM '.$this->tablename.' WHERE '.$this->pk->fieldname.' = \''.$id.'\''); 
            if (!empty($row) ){ 
              foreach ($this->cols as $col)  $col->value      = $row[0][$col->fieldname];
              return $row[0];
            }
            return false;
        }

        private function getDisplayRow($id){
            $displayRow = array();
            $row = $this->getRow($id);
            foreach ($row as $k=>$v){
                foreach ($this->cols as $col){
                    if ($k==$col->fieldname){
                        $displayRow[$k]['fieldvalue'] = $v;
                        if ($col->type=='select')  $displayRow[$k]['displayvalue'] = $col->values[$v];
                                             else  $displayRow[$k]['displayvalue'] = $v;
                    }
                }
            }
            return $displayRow;
        }        
        
        // Imprime una fila en formato json
        public function showRow($args){
            $result = array();
            $result['error'] = 0;
            
            $row = $this->getDisplayRow($args['id']);
            if($row) {
               $result['row'] = $row; //str_replace('[CONTENT]',$row,MARKUP_DETAIL);
            }else{
               $result['error'] = 1;
               $result['msg']='No existe la fila';
            }
            echo json_encode($result, JSON_PRETTY_PRINT);  
        }
        
        public function showForm($args){
            //Vars::debug_var($args);
            $oldState = $this->setState($args['op']=='edit' ? 'update' : 'insert');
            //Vars::debug_var($args['id']);
            if ($args['op']=='edit') $row  = $this->getRow($args['id']);

            $form = new Form('form_'.$this->tablename,$this->tablename); //.'_'.$id);
            $form->setAction( Vars::mkUrl( MODULE, 'ajax', 'table='.$this->tablename) );
            $form->tablename = $this->tablename;
            $form->id = 'form_'.$this->tablename;
            $this->addFieldset('default');
  
            foreach ($this->cols as $col){
              
               if ($args['op']=='add' && $this->parent_key && $col->fieldname==$this->parent_key && $args['id']) $col->value=$args['id']; 

               if ($col->pk&&$args['op']=='edit')  {
                   
                   $this->fieldsets['default']->addElement(new formHidden($col)); 
                   
               }else if($col->editable){

                   if (!isset($this->fieldsets[$col->fieldset])){
                     $this->fieldsets[$col->fieldset] = new fieldset($col->fieldset); //FS
                     $this->fieldsets[$col->fieldset]->displaytype = 'tab'; 
                   }
                   $this->fieldsets[$col->fieldset]->addElement($col->getFormElement()); 
                   
               }
               
            }
      
            foreach ($this->fieldsets as $fieldset) $form->addElement($fieldset);  

            $option = new Field();
            $option->fieldname='op';
            $option->value = $args['op']=='edit' ? 'update' : 'insert';
            $this->fieldsets['default']->addElement(new formHidden($option)); 

            $buttons = new fieldset('dialog-buttons');
            $buttons->displaytype = 'footer'; 
            
            $btn_submit = new Field();
            $btn_submit->type='submit';
            $btn_submit->value = $args['op']=='edit' ? 'Guardar' : 'Añadir';
            $buttons->addElement( new formButton($btn_submit ) );

            $btn_cancel = new Field();
            $btn_cancel->type='reset';
            $btn_cancel->value = 'Cancelar';
            $buttons->addElement( new formButton($btn_cancel ) );

            $form->buttons = $buttons;
            $form->addElement($buttons);  
            $form->render();
            $this->setState($oldState);
        }

        private function select($args){
            $sql = 'SELECT '.implode( ',', array_keys($this->cols)).' FROM '.$this->tablename;
            if($this->parent_key){
              if ($args['id']){
                 $this->parent_value = $args['id'];
                 $sql .= ' WHERE ' . $this->parent_key . ' = \''. $this->parent_value.'\'';
              }
            }
            
            $this->rows = Table::sqlQuery($sql); 

            $this->sql = $sql;
        }
       
        public function getPager(){
           return str_replace( array('[BUTTON_RELOAD]','[BUTTON_ADD]','[BUTTON_SETUP]'), array(BUTTON_RELOAD,BUTTON_ADD,BUTTON_SETUP), MARKUP_PAGER );
        }

        public function json(){
             $this->select(false);
             $result = array();
             $result['tablename'] = $this->tablename;
             $result['pk'] = $this->pk->fieldname;
             $result['rows'] = $this->rows;
             echo json_encode($result, JSON_PRETTY_PRINT);  
        } 

        public function getTableRow($row){
            $str_row = '';
            $this->pk->value = $row[$col->pk->fieldname];
            foreach($this->cols as $col){
                $col->value = $row[$col->fieldname];
                $str_row .=  $col->getCell( $this, $col );
            }
            return $str_row;
        }
         
        public function getTableRowEmpty(){
            $str_row = '';
            foreach($this->cols as $col){
                $str_row .=  str_replace(  array('[TABLENAME]','[PK]','[VALUE]','[FIELDNAME]','[FIELDVALUE]','[DISPLAYVALUE]'),  array($this->tablename,'', '',  '', '', '','AAA' ), MARKUP_CELL );
            }
            return $str_row;
        }
         
        public function showTable($args){
            $result = array();
            $result['error'] = 0;
            
            $this->select($args);
            
            $ncols=count($this->cols);

            // Render title
            $html_title = str_replace( array( '[COLS]', '[TITLE]' ),
                                       array( $ncols, $this->tablename), //.' ['.$this->sql.']' ),
                                       MARKUP_HEADER_TITLE );

            // Render header
            $html_header_cells = '';
            foreach($this->cols as $col){ $html_header_cells .= str_replace( '[CONTENT]', $col->label, MARKUP_HEADER_CELL ); }
            $html_header = str_replace( '[CONTENT]', $html_header_cells, MARKUP_HEADER_ROW );

            // Render rows
            $htmll_rows = '';
            if ($this->rows===false){ 
               $htmll_rows .= str_replace( '[COLS]', $ncols, MARKUP_NOT_EXISTS);                        
           // }else if (count($this->rows)==0) { 
           //    $htmll_rows .= str_replace( '[COLS]', $ncols, MARKUP_ROW_EMPTY);                        
            }else{
               foreach( $this->rows as $row){   
                   $htmll_rows .= str_replace( array('[TABLENAME]','[PK]','[VALUE]','[CONTENT]'), 
                                               array($this->tablename,$this->pk->fieldname,$row[$this->pk->fieldname],$this->getTableRow($row)), 
                                               MARKUP_ROW);            
               }
               if($this->show_empty_rows){
                  $empty_rows = $this->page_num_items  - count($this->rows);
                  if ( $empty_rows ) {
                    for($i=0; $i<$empty_rows; $i++) {

                       $htmll_rows .= str_replace( array('[TABLENAME]','[PK]','[VALUE]','[CONTENT]'), 
                                                   array($this->tablename,$this->pk->fieldname,$row[$this->pk->fieldname],$this->getTableRowEmpty()), 
                                                   MARKUP_ROW_EMPTY);            
                    
                    
                    }
                  }
               }
            }
                
            // Render footer
            $html_footer = str_replace( array( '[COLS]','[PAGER]'), array($ncols+1,$this->getPager()), MARKUP_FOOTER );
               
            $detailtables = implode(',',$this->detail_tables);
            
            // Render table
            $result['html'] = str_replace( array('[TABLENAME]','[TITLE]','[HEADER]','[ROWS]','[FOOTER]','[DETAILTABLES]','[PARENT_KEY]','[PARENT_VALUE]'),
                                           array($this->tablename, $html_title, $html_header, $htmll_rows, $html_footer,$detailtables,$this->parent_key,$this->parent_value ), 
                                           MARKUP_TABLE );

            echo json_encode($result);           
            //echo $result['html'];           

        }

        public static function show_table($id){  // $id es el nombre de una tabla o vista
            echo str_replace(array('[MODULE]','[ID]'), array(MODULE,$id),MARKUP);
        }
        
        public static function css($css=false){
            //echo '<link rel="stylesheet" type="text/css" href="/'.SCRIPT_DIR_CLASSES.'/crud/css/style.new.css?ver=1.0.5"/>'."\n";
            //echo '<link rel="stylesheet" type="text/css" href="'.($css?$css:self::$css).'"/>'."\n";
            HTML::css($css?$css:self::$css);
        }
        
        public static function js(){
            echo '    <script type="text/javascript">var eventsAssigned=false,module_name=\''.MODULE.'\',button_close=\''.BUTTON_CLOSE.'\';</script>'."\n";
           // echo '    <script type="text/javascript" src="/'.SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/script.js?ver=1.0.2"></script>'."\n";
        }
        
        // inserta una fila. 
        // $args puede ser un array asociativo, por ej. lo que venga en un $_POST
        public function insert($args){       
            
            $result = array();
            $result['error'] = 0;
            $result['errors'] = array();
            $result['op'] = $args['op'];
            /*
            if(!$args[$this->pk->fieldname]){
                $result['error'] = 1;
                $result['msg'] = 'Falta la clave primaria';
            }
            */
            if($result['error']==0){            
                $insert = array();
                foreach($this->cols as $col){
                    if ($col->editable){
                        if ($col->required && !$args[$col->fieldname]){
                           $result['error']=2;
                           $result['errors'][]='El campo '.$col->label.' no puede estar vacío.';
                        }else{
                           $insert[$col->fieldname] = $args[$col->fieldname];
                        }
                    }
                    if($result['error']==2) $result['msg']='Hay errores en los datos:<br />'.implode('<br />',$result['errors']);
                }

                if($result['error']==0){            
                    $sql = 'INSERT INTO '.$this->tablename.' ('.implode(',',array_keys($insert)).') VALUES (\''.implode('\',\'',array_values($insert)).'\')';
        
                   if (self::sqlExec($sql)){
                       if($this->parent_key) $result['id'] = $args[$this->parent_key];
                       $result['msg'] = 'Fila añadida '.self::lastInsertId();
                    }else {
                       $result['error'] = 1;
                       $result['msg']='Error en la transacción '; //.print_r($query->errorInfo(),true);                      
                    }
                }
            }  
            echo json_encode($result);           
        
        }
        
        // actualiza una fila. 
        // $args puede ser un array asociativo, por ej. lo que venga en un $_POST
        public function update($args){       
            
            $result = array();
            $result['args'] = $args;
            $result['error'] = 0;
            $update = array();
            foreach($this->cols as $col){
                if ($col->pk){
                    $where = $col->fieldname.' = \''.$args[$col->fieldname].'\'';
                }else if ($col->editable){
                    if ($col->required && !$args[$col->fieldname]){
                       $result['error']=1;
                       $result['msg']='El campo '.$col->label.' no puede estar vacío';
                    }else{
                       $update[$col->fieldname] = $col->fieldname.' = \''.$args[$col->fieldname].'\'';  //FIX: escape values
                    }
                }
            }
            if($result['error']==0){
                $sql = 'UPDATE '.$this->tablename.' SET '.implode(',',array_values($update)).' WHERE '.$where;
                if (self::sqlExec($sql)){
                   $result['msg'] = 'Fila actualizada';
                   $result['op']  = $args['op'];
                   $result['tb']  = $this->tablename;
                   $result['pk']  = $this->pk->fieldname;
                   $result['id']  = $args[$this->pk->fieldname];
                   $result['row'] = $this->getDisplayRow($args[$this->pk->fieldname]);
                }else {
                   $result['error'] = 1;
                   $result['msg'] = 'Error en la transacción ';//.print_r($query->errorInfo(),true); 
                } 
            }
            echo json_encode($result);           
        
        }
       
        // elimina una fila
        public function delete($args){
            $result = array();
            $result['error'] = 0;
            $result['op'] = $args['op'];
            $result['id']  = $args['id'];
             
            //FIX check perms
              
            //$result['msg'] = print_r($args,true);
            $sql = 'DELETE FROM '.$this->tablename.' WHERE '.$this->pk->fieldname.' = '.$args['id'];
            if (self::sqlExec($sql)){
                $result['msg'] = 'fila borrada';
            }else {
                $result['error'] = 1;
                $result['msg'] = 'Error en la transacción '; //.print_r($result['errorInfo'],true); 
            } 
            $result['tb']  = $this->tablename;
            $result['pk']  = $this->pk->fieldname;
          //$result['id']  = $args[$this->pk->fieldname];
            echo json_encode($result);       
        }

        public function reload($args){
            $result = array();
            $result['error'] = 0;
            $result['msg'] = 'reloading';
            $result['op']  = $args['op'];
            $result['id']  = $args['id'];
            echo json_encode($result);       
        }
       
        // Crea la tabla. $fields es un array de objetos Field
        public function create_table(){
            //$r  = "\nDROP TABLE IF EXISTS {$this->tablename};\n";
            $r = "CREATE TABLE {$this->tablename}(\n";
            foreach( $this->cols as $col) { 
              if(!$col->calculated) $r .= $this->create_column($col).",\n"; 
            }
            $r .= "PRIMARY KEY  (`".$this->pk->fieldname."`)\n";
            $r .= ")ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;\n";
            return $r;
        }

        private function str_sql_len($len,$deflen){ return ($len) ? $len : $deflen; }

        public function create_column($col){
          $r = $col->fieldname;
          //Vars::debug_var($col->fieldname);
          //Vars::debug_var($col->fieldname.' '.$col->type);
          if      ($col->type=='varchar')  $r .=  " VARCHAR({$this->str_sql_len($col->len,15)})";
          else if ($col->type=='select' )  $r .=  " INT({$this->str_sql_len($col->len,5)})";
          else if ($col->type=='int'    )  $r .=  " INT({$this->str_sql_len($col->len,5)})";
          else if ($col->type=='hidden' )  $r .=  " INT({$this->str_sql_len($col->len,5)})";
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
          //if($col->attribute)    $r .= " {$col->attribute}";               // 'UNSIGNED' 
          if($col->unsigned || $this->pk->fieldname==$col->fieldname)     $r .= ' unsigned ';
          if($col->not_null || $this->pk->fieldname==$col->fieldname)     $r .= ' NOT NULL';
          if($this->pk->fieldname==$col->fieldname) $r .=  ' auto_increment';
          if(isset($col->default_value)&&$col->type!='textarea') $r .= " DEFAULT '{$col->default_value}'"; // timestamp  CURRENT_TIMESTAMP
          return $r;
        }

        public function test_create_table(){
            $result = array();
            $result['error'] = 0;
           // $result['msg'] = 'Tabla :'.$this->tablename.'<br />';
            $result['msg'] = str_replace("\n",'<br />',$this->create_table());
            echo json_encode($result);       
        }

        public function check_table(){
            $result = array();
            $result['error'] = 0;
            $result['msg'] = 'Tabla :'.$this->tablename.'<br />';
            /******/
            $rows = self::sqlQuery("DESCRIBE {$this->tablename}");
            if ($rows){
              foreach ($rows as $fila){  
                $result['msg'] .= 'Columna: '.$fila['Field'].' '.$fila['Type'];
                if ($fila['Null']=='NO') $result['msg'] .= ' NOT NULL';
                if ($fila['Extra'])      $result['msg'] .= ' '.$fila['Extra'];
                
                foreach( $this->cols as $col) {
                  // if($this->pk->fieldname==$col->fieldname)$col->attribute ='unsigned';
                  if($col->fieldname==$fila['Field'] && !$col->calculated){
                    //if($this->pk->fieldname==$col->fieldname) $col->attribute ='unsigned NOT NULL auto_increment';
                    $col->attribute = '';
                    if($col->unsigned || $this->pk->fieldname==$col->fieldname)  $col->attribute .= ' unsigned ';
                    if($col->not_null || $this->pk->fieldname==$col->fieldname)  $col->attribute .= ' NOT NULL';
                    if($this->pk->fieldname==$col->fieldname)                    $col->attribute .= ' auto_increment';
                  
                  
                  
                    //if(($col->type=='varchar'||$col->type=='int')&&$col->len>0){
                      if     ($col->type=='select')  $t1 = 'int' ;
                      else if($col->type=='datetime')$t1 = 'datetime' ;
                      else if($col->type=='hidden')  $t1 = 'int' ;
                      else if($col->type=='progress')$t1 = 'int('.($col->len?$col->len:'4').')' ;
                      else if($col->type=='textarea')$t1 = 'text' ;
                      else if($col->type=='bool')    $t1 = 'int(1)' ;
                      else if($col->type=='ccc')     $t1 = 'varchar(23)' ;
                      else if($col->type=='file')    $t1 = 'varchar(200)' ;
                      else if($col->type=='color')   $t1 = 'varchar(12)' ;
                      else if($col->type=='decimal') $t1 = "decimal({$col->len},{$this->str_sql_len($col->precission,2)})" ;
                      else                           $t1 = "{$col->type}" ;
                     
                   
                      if($col->type=='varchar'||$col->type=='int'||$col->type=='select'||$col->type=='hidden') {
                        if (!$col->len) $col->len=5;
                        $t1 .= "({$col->len})" ;
                      }
                      if($col->attribute) $t1.=' '.($col->attribute);

                      if(isset($col->default_value)&&$col->type!='textarea') {
                        if($fila['Default'] != $col->default_value) $t1.= " DEFAULT {$col->default_value}";  //20131017
                      }//$r .= " DEFAULT '{$col->default_value}'"; // timestamp  CURRENT_TIMESTAMP
                      
                      $t2 = $fila['Type'];
                      if ($fila['Null']=='NO') $t2.= ' NOT NULL';
                      if ($fila['Extra']) $t2.= ' '.$fila['Extra'];
                     // $m1 = md5(implode(str_split(trim($t1))));
                     // $m2 = md5(implode(str_split(trim($t2))));
                     //echo '<span class="label label-info" style="font-size:.9em;font-weight:normal">['.similar_text($t1,$t2).']['.strlen(trim($t1)).'-'.strlen(trim($t2)).']</span>';  
                      if ( similar_text($t1,$t2) == strlen($t2)){
                     //   echo '<span class="label label-info" style="font-size:.9em;font-weight:normal">OK: '.$t1.' == '.$t2.'</span>';  
                      }else{
                        $result['msg'] .=  '<span class="label label-warning" style="font-size:.9em;font-weight:normal">FAIL: ('.$t1.') != ('.$t2.')</span>';  
                        $result['msg'] .=  "<br />Update: <span class=\"label\">ALTER TABLE {$this->tablename} CHANGE {$col->fieldname} ".$this->create_column($col).';</span>';
                        $result['affected_rows'] = self::sqlExec("ALTER TABLE {$this->tablename} CHANGE {$col->fieldname} ".$this->create_column($col).";");
                      }
                    //}
                    $col->check = true;
                    break;
                  }
                  
                }    
                $result['msg'] .=  ($col->check) ? ' <span class="label label-success"><i class="fa fa-check"></i></span>':' <span class="label label-error"><i class="fa fa-close"></i></span>';
                $result['msg'] .=  '<br />';
              }
              foreach( $this->cols as $col) {
                if(!$col->check  && !$col->calculated){
                  $result['msg'] .=  "<br />New column: <span class=\"label\">ALTER TABLE {$this->tablename} ADD COLUMN ".$this->create_column($col).';</span>';
                  $result['affected_rows'] = self::sqlExec("ALTER TABLE {$this->tablename} ADD COLUMN ".$this->create_column($col).';');
                }
              }
            }else{
              //Vars::debug_var($this->create_table(),$this->tablename);
            //  $result['sql'] = $this->create_table();
              $r = self::sqlExec($this->create_table());
              $result['msg'] = str_replace("\n",'<br />',$this->create_table()).'<br />'.(($r===false)?'Failed':'OK');
           //   Messages::success($this->create_table());
              //if($this->events && method_exists($this->events, 'OnAfterCreate')){
              //  $this->events->OnAfterCreate($this);
              //} 
            }
            /******/
            echo json_encode($result);       
        }
       
        // A tomar por (_._)
        public function drop(){
            $result = array();
            $result['error'] = 0;
           // Vars::debug_var('DROP TABLE '.$this->tablename);         
            $sql = 'DROP TABLE '.$this->tablename;         
          //////////////////////////////////////////////  $r = self::sqlExec($sql);
            $result['msg'] = ($r===false?'Failed':'Success').':<br />'.$sql;         
           // Messages::success('DROP TABLE '.$this->tablename);
            echo json_encode($result);       
        }
       

    }


 //   class TableMysql extends Table{}
 //   class TableSQLite extends Table{}
