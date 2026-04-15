<?php 

  define('INSTALL','ok');  // ¯\_(⊙_ʖ⊙)_/¯

  define('CLASS_URL',SCRIPT_DIR_CLASSES.'/scaffold');
  define('CLASS_TABLE_LOADED',true); 
  //define('DB_EXTERNAL',false); //,true);

  if (!defined("DATE_FORMAT"))         define('DATE_FORMAT' ,'Y-m-d');
  if (!defined("DATETIME_FORMAT"))     define('DATETIME_FORMAT' ,'Y-m-d H-i-s');
  if (!defined("DATE_FORMAT_JS"))      define('DATE_FORMAT_JS' ,'dd-mm-yy');
  if (!defined("DATETIME_FORMAT_JS"))  define('DATETIME_FORMAT_JS' ,'dd-mm-yy hh:mm:ss');

  define('DATE_FORMAT_ORACLE','d-m-Y H:i:s');
  define('DATE_FORMAT_MYSQL','%Y-%m-%d');
  define('DATETIME_FORMAT_MYSQL','%Y-%m-%d %H:%i:%s');

  define('RECYCLE_ID','recycle-icon');
  define('IMG_RECYCLE_ICON',SCRIPT_DIR_IMAGES.'/famfam/trash.png');
  define('IMG_RECYCLE_ICON_FULL',SCRIPT_DIR_IMAGES.'/famfam/trash_full.png');
  define('AJAX_LOADER_ID','ajax-indicator');
  define('IMG_AJAX_LOADER',SCRIPT_DIR_IMAGES.'/indicator.gif');
  define('MARKUP_LOADING','<p class="ajaxloader" style="text-align:center;position:absolute;top:50px;width:100%;padding:20px auto;"><img style="width:56px;" src="_images_/indicator.gif"></p>');

  define('CREATED_BY','created_by');
  define('CREATION_DATE','creation_date');
  define('LAST_UPDATED_BY','last_updated_by');
  define('LAST_UPDATE_DATE','last_update_date');
  define('ACTIVE','active');

  //include_once(SCRIPT_DIR_CLASSES.'/scaffold/table.events.class.php');
  //include_once(SCRIPT_DIR_CLASSES.'/scaffold/table.events.tags.class.php');
 
  class JS {
    private $content;
    public function __construct(){}
    public function __destruct(){}
    public function addLine($line){ $this->content .= $line."\n"; }
    public function __toString(){ return "<script type=\"text/javascript\">\n/"."* dummy *"."/{$this->content}\n</script>"; }
  }
  
  class Table {
    private $_order = 0;
    private $fieldnames = array();
    public $tablename;
    public $perms = array();
    //CACHE public $cache = false;
    public $classname         = 'table table-bordered table-striped datatable-rows';     // 'table-bordered';
    public $showtitle = false;

    public $markup_header_title= '<tr><th class="tb-title" colspan="[COLS]">[TITLE]</th></tr>';  
    public $markup_header_row  = '<tr id="[ID]" class="[CLASS]">[CONTENT]<th></th></tr>';  
    public $markup_header_cell = '<th id="[ID]" class="[CLASS]" title="[HINT]" style="[STYLE]">[CONTENT]</th>';  //  width="[WIDTH]"
    public $markup_footer_row  = '<tr class="table-footer"><th colspan="[COLS]"> [CONTENT] </th></tr>';  
    
    public $markup            = '<table id="[ID]" data-pk="[PK]" class="tb_id [CLASS]">[COLGROUP]<thead>[HEADER]</thead><tbody>[BODY]</tbody><tfoot>[FOOTER]</tfoot></table>';  
    public $markup_row        = '<tr id="[ID]" class="table-row [CLASS]">[CELLS]<td class="actions"><div>[ACTIONS]</div></td></tr>';
    public $markup_cell       = '<td id="[ID]" class="[CLASS]" style="[STYLE]" data-fieldname="[FIELDNAME]" data-precission="[PRECISSION]" data-th="[LABEL]" val="[VAL]">[CONTENT]</td>';  

    public $markup_row_empty    = '<tr class="row-empty">[CONTENT]<td class="actions"><span class="actions"><img src="_images_/pixel.gif"></span></td></tr>';
    public $markup_cell_empty   = '<td>&nbsp;</td>';  // onclick="[CLICK]" ondblclick="[DBLCLICK]" 


    ///public $markup_separator  = '<tr id="[ID]" class="[CLASS]" style="[STYLE]">[CONTENT]</tr>';
    //group
    public $markup_group_title = '<div class="kbn_title">[TITLE]</div>';
    public $markup_group_begin = '<div class="kbn_wrap">[CONTENT]<div id="[ID]" class="tb_id kbn_items">'; 
    public $markup_group_group_begin = '<div id="group_[GROUP]" class="kbn_section"><p class="kbn_section_title">[CONTENT] <a class="toggle">#</a></p><ul class="kbn_section_items[SECTION]">';
    public $markup_group_row_OK= '<li id="[ID]" data-id="[ID]" class="div_item [CLASS]"><dt>[TITLE]</dt><dd>[CELLS]</dd><div style="float:right;" class="actions">[ACTIONS]</div></li>';  //<dd>[CONTENT]</dd>
    public $markup_group_row   = '<li id="row-[ID]" data-id="[ID]" class="div_item kbn_item [CLASS]"><div class="kbn_item_title">[TITLE]</div><div class="kbn_item_content">[CELLS]</div><div class="kbn_item_buttons actions">[ACTIONS]</div></li>';  //<dd>[CONTENT]</dd>
    public $markup_group_cell  = '[CONTENT]<br />'; //<span id="[ID]" class="[CLASS]" style="[STYLE]" val="[VAL]">[CONTENT]</span><br />';
    public $markup_group_group_end = '</ul></div>';
    public $markup_group_group_end_link= '</ul><a class="add_item">Añadir ...</a></div>';
    
    public $markup_group_end   = '</div>[CONTENT]</div>'; 
/*
    public static $php_viewer_markup = '<div id="pdf_viewer" class="pdf_viewer">'
                                     .     '<img id="pdf_viewer_icon" src="_images_/filetypes/icon_pdf.png">'
                                     .     '<span id="pdf_viewer_title"></span> '
                                     .     '<a id="pdf_viewer_maximize" title="Maximizar"><i class="fa fa-window-maximize"></i></a>'
                                     .     '<a id="pdf_viewer_close" class="pdf_viewer_close" title="Restaurar"><i class="fa fa-window-close"></i></a>'
                                     .     '<div id="pdf_viewer_file"></div>'
                                     . '</div>';
*/
    public $order;
    public $state = 'browse';
    public $cols = array();    // Array of Field objects
    public $rows = array();    // Array of strings
    public $fieldsets = array();    // Array of strings
    public $total;             // total rows in query
    public $cur;
    public $page = 1;
    public $page_num_items = 20;
    public $paginator_link;    
    public $pk          = false;  // 'id';
    public $js;
    public $output      = 'table';
    public $paginator   = false;
    public $driver      = 'mem';  // mysql, oracle, mem
    public $parent_key   = false;  // Foreing Key for act as detail table - false or fieldname
    public $parent_value = false;  // Foreing Key value for act as detail table - false or field value
    public $field_title = false;  // For group view
    //public $parent = false;
    public $orderby = [];
    public $verbose;
    public $detail_tables;        // Array of detail tablenames  // Field key for detail tables => $this->pk->fieldname
    public $whocolumns  = false;
    public $inline_edit = true;
    public $searchOperator = 'AND';
    public $searchstring;
    public $filterstring;
    public $strfilter;
    public $visible =true;
    public $show_empty_rows = true;
    public $filter = '';
    public $ajax_url;              // = AJAX_URL.'?module='.$module_name;
    public $events;             // instance or child of defaultTableEvents, or false if no events
    public $autocreate = true;  // if table not exists, create it when call $this->check()
    public $automodify = true;  // if $this->cols attributes changes, change table columnns when call $this->check()
    public $show_inputsearch = true;
    public $default_fieldset_name = 'default';
    //public static $oracle = false;
    public static $initialized = false;
    public static $module_name;
    public static $theme = 'default';
    public static $editor = false; // = 'wysihtml5';  // wysiwyg editor for text columns
    public static $comet;          // if true, remote changes are detected and table is updated. Require shm php functions.
    public static $sortable_script_loaded = false;
    //abstract function OnDelete($owner,&$result,$id);
    private $recurselevel = 0;
    private $level = 0;
    public $pdf_orientation = 'portrait';
    public $soft_delete = false;      //SOFT_DELETE
    public $on_delete_cascade = false;      
    public $langs = array();
    public $translatable = true;  // if false fields translatable property is ignored
    public $download_count;
    public $table_tags;
    public $uploaddir;
    public $epub;
    public $group;

    public $tb_categories;
    public $fk;
    public $link_cfg;
    public $link_gallery_mode;
    public $link_upload_files; // = false;

    public $profile;
    public $sanitize_title;
    public $module;
    public $hash_filenames;
    public $main;
    public $mini;
    public $usercode;
    
    public $accepted_doc_extensions;
    public $accepted_img_extensions;

    public $download_count_fieldname;
    public $title;
    public $gallery_mode;
    public $field_group;
    public $field_group_order;
    public $table_group;
    public $table_group_pk_fieldname;
    public $table_group_fieldname;
    public $where;
    public $default_filter;
    public $old_filter;
    public $format_item;
    public $format_detail;
    public $input_page;
    public $input_page_num;
    //public static $cache;
    public $images_dir;
    public $name;
    public $categories;
    public $tree;
    public $key_parent;
    public $detail_tables_keys;
    public $watermark;
    public $log =false;
    public $sqls = [];
    /*
    public $;
    public $;
    public $;
    */

    public function __construct($tablename=false){
      if ($tablename){
        $this->default_fieldset_name = 'fs_'.Str::sanitizeName($tablename); 
        $this->tablename = $tablename;
        $this->perms['view']   = true;
        $this->perms['detail']   = true;
        $this->js = new JS();
      //$this->js->addLine("var X_{$this->tablename} = new Array();");
        $this->js->addLine("window['X_{$this->tablename}'] = new Array();");
        $this->js->addLine("window['X_{$this->tablename}']['state'] = 'normal';");
        $this->events = New defaultTableEvents();  // This can be optionally
        $this->ajax_url = SCRIPT_DIR.'/'.(self::$module_name?self::$module_name:MODULE).'/ajax'.'/'.$_SESSION['tblang'];
        
      }

      if ($this->translatable!==false)  //FIX move later when CFG::$vars exists
          if (isset(CFG::$vars['site']['langs']['enabled']) && CFG::$vars['site']['langs']['enabled']===true){
              $query_langs =  $this->sql_query('select lang_id,lang_cc,lang_name from '.TB_LANG.' where lang_active=1 and lang_id>1');
              foreach ($query_langs as $row){
                  $this->langs[$row['lang_cc']]=$row['lang_name'];
              }
          }

     // if(!isset($_SESSION['_CACHE'][$this->tablename]['order']))
     //    $_SESSION['_CACHE'][$this->tablename]['order'] = '';  //array();
    }
    
    public function log_write($type, $subject='', $message='') {

        if (!$type || trim($subject)=='') return false;
        $_id      = $_SESSION['username'] ?? '0';
        $_email   = $_SESSION['user_email'] ?? '0';
        $_subject = Str::escape($subject);
        $_message = Str::escape($message);

        if($this->events && method_exists($this->events, 'OnLog'))  $this->events->OnLog($this, $type, $_subject, $_message);

        $log_sql  = "INSERT INTO ".TB_LOG." (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES('{$type}','{$_id}','{$_email}','{$_subject}','".$_message."')";
        Table::sqlExec($log_sql,false);
    }

    public function field($fieldname,$type){  
        foreach( $this->cols as $col) { if($col->fieldname==$fieldname) return $col; }
        ${$fieldname} = new Field();
        if($this->pk==false) ${$fieldname}->pk=true; ;

        ${$fieldname}->type = $type; 
       //if($len) ${$fieldname}->len = $len;
        ${$fieldname}->fieldname = $fieldname;
        ${$fieldname}->label = ucwords(strtolower($fieldname));
      //if(${$fieldname}->pk ==false) ${$fieldname}->editable = true; else 
        ${$fieldname}->editable = true;
      //${$fieldname}->required = true;
      //${$fieldname}->filtrable = true;
      //${$fieldname}->searchable = true;
      //if($add) $this->addCol(${$fieldname});
        return ${$fieldname};
    }

    public function addCols($fields){  
        foreach ($fields as $field){
            $this->addCol($field);
        }
    }

    public static function recycleIcon(){ return '<img id="recycle-icon" src="_images_/famfam/trash.png">';  }
    public static function ajaxLoader() { return '<span id="ajax-indicator"></span>';    }
    public function setState($state){ $oldState = $this->state; $this->state = $state; return $oldState; }
 
    public function setParent($fieldname,$value){
      global $_ARGS;
      $this->parent_key = $fieldname;        // Foreing Key for act as detail table - false or fieldname
      $this->parent_value = max(0,$value);  // Foreing Key value for act as detail table - false or field value
      $this->ajax_url = SCRIPT_DIR.(self::$module_name?self::$module_name:$_ARGS[0]).'/parent='.$value.'/ajax';
      $this->js->addLine("window['X_{$this->tablename}']['parent'] = {$value};"); }
    /*
    public function sql_query($sql){
     // $this->debug( 'Sql: '.$sql);
    }

    public function sql_fetch($query){
      //if(!$query) $this->debug( 'ERROR: no $query');
    }
    */

    public static function escape($str) {                               
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        if      (CFG::$vars['db']['type']=='mysql')  $replace = ["\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z"];
        else if (CFG::$vars['db']['type']=='sqlite') $replace = ["\\\\","\\0","\\n", "\\r", "''", '"', "\\Z"];
        else                                         $replace = ["\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z"];
        return str_replace($search, $replace, $str);
    }                                                                       //ADD 20140423

    public static function unescape($str) {                               
        if      (CFG::$vars['db']['type']=='mysql')  $search  = ["\\\\","\\0","\\n", "\\r", "\'", '"', "\\Z"];
        else if (CFG::$vars['db']['type']=='sqlite') $search  = ["\\\\","\\0","\\n", "\\r", "''", '""', "\\Z"]; 
        else                                         $search  = ["\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z"];
        $replace = ["\\", "\x00", "\n",  "\r",  "'",  '"', "\x1a"];  //,"<br />");
        return str_replace($search, $replace, $str);
    }
        
    public static function sqlQuery($sql,$cache=true){
        if (CFG::$vars['db']['type']=='mysql')
            return TableMysql::sqlQuery($sql,$cache);
        else if (CFG::$vars['db']['type']=='sqlite')
            return TableSqlite::sqlQuery($sql,$cache);
        //else if (CFG::$vars['db']['type']=='oracle')
        //    return TableOracle::sqlQuery($sql);
    }        
    public static function  sqlQueryPrepared($sql, $params = [],$cache=true){
        if (CFG::$vars['db']['type']=='mysql')
            return TableMysql::sqlQueryPrepared($sql,$params,$cache);
        else if (CFG::$vars['db']['type']=='sqlite')
            return TableSqlite::sqlQueryPrepared($sql,$params,$cache);
        //else if (CFG::$vars['db']['type']=='oracle')
        //    return TableOracle::sqlQuery($sql);
    }

    public static function asArrayValues($sql,$key,$val){
        if (CFG::$vars['db']['type']=='mysql')
            return TableMysql::asArrayValues($sql,$key,$val);
        else if (CFG::$vars['db']['type']=='sqlite')
            return TableSqlite::asArrayValues($sql,$key,$val);
       // else if (CFG::$vars['db']['type']=='oracle')
       //     return TableOracle::asArrayValues($sql,$key,$val);
    }

    public static function lastInsertId(){
        if (CFG::$vars['db']['type']=='mysql')
            return TableMysql::lastInsertId();
        else if (CFG::$vars['db']['type']=='sqlite')
            return TableSqlite::lastInsertId();
      //  else if (CFG::$vars['db']['type']=='oracle')
      //      return TableOracle::lastInsertId();
    }

    public static function lastError(){
        if (CFG::$vars['db']['type']=='mysql')
            return TableMysql::getError();
        else if (CFG::$vars['db']['type']=='sqlite')
            return TableSqlite::getError();
      //  else if (CFG::$vars['db']['type']=='oracle')
      //      return TableOracle::lastError();
    }

    public static function getFieldsValues($sql){
            //if(!self::$connection) return false;
            $row = self::sqlQuery($sql);
            if ($row && !empty($row)) return $row[0];
        return false;
    }

    public static function sqlExec($sql){
        if (CFG::$vars['db']['type']=='mysql')
            return TableMysql::sqlExec($sql);
        else if (CFG::$vars['db']['type']=='sqlite')
            return TableSqlite::sqlExec($sql);
      //  else if (CFG::$vars['db']['type']=='oracle')
      //      return TableOracle::sqlExec($sql);
    }

    
    //cada fila contiene: 
    //- un string que indica la operacion(insert, update, delete,etc)
    //- otro que indica el nombre de la tabla
    //- un id que identifica la fila (por su primary key)
    //- el id_user del usuario que ha metido el dato
    //- un array de id_users que ya hayan leído este dato
    
    public function putMessage($message) {

      //if(!(Table::$comet))  return false; 
    }

    public function readMessage() {
      if(!self::$comet) return false; 
      //if ($memvar->exists(COMET_KEY_MEM_VAR)){
      //  $dato = $memvar->getValue(COMET_KEY_MEM_VAR);
      //  print_r( 'dato: '.$dato['msg']);
      //}else{
      //  echo 'dato not exists';
      // }
    }

    public function query2array($sql){
      //echo '<pre>';  echo $sql;  echo '</pre>';
      return $this->sql_query($sql);
    }

    public function addWhoColumns() { 
      if($this->driver=='sqlite') return false;
      if($this->driver=='oracle') return false;
      $this->whocolumns = true;
      $_created_by = new Field();
      $_creation_date = new Field();
      $_last_updated_by = new Field();
      $_last_update_date = new Field();
      $_created_by->type = 'int';
      $_created_by->len = 5;
      $_creation_date->type = 'datetime';
      $_last_updated_by->type = 'int';
      $_last_updated_by->len = 5;
      $_last_update_date->type = 'datetime';
      $_created_by->fieldname = 'CREATED_BY';
      $_creation_date->fieldname = 'CREATION_DATE';
      $_last_updated_by->fieldname = 'LAST_UPDATED_BY';
      $_last_update_date->fieldname = 'LAST_UPDATE_DATE';
      $_created_by->label = 'creado por';  
      $_creation_date->label = 'creado';  
      $_last_updated_by->label = 'Actualizado por';  
      $_last_update_date->label = 'Actualizado';  
      $_created_by->editable  = false;
      $_creation_date->editable  = false;  
      $_last_updated_by->editable  = false;  
      $_last_update_date->editable  = false;  
      $_created_by->visible  = false;
      $_creation_date->visible  = false;  
      $_last_updated_by->visible  = false;  
      $_last_update_date->visible  = false;  
      $_created_by->hide = true;  
      $_creation_date->hide = true;  
      $_last_updated_by->hide = true;  
      $_last_update_date->hide = true;  
      $this->addCol($_created_by);
      $this->addCol($_creation_date);
      $this->addCol($_last_updated_by);
      $this->addCol($_last_update_date);
    }

    public function addActiveCol() { 
      global $_ACL;
      $_active = new Field();
      $_active->fieldname = 'ACTIVE';
      $_active->label     = 'Activo';   
      $_active->type      = 'bool';
      $_active->default_value   = '1';
      $_active->sortable  = true;
      $_active->editable  = $_ACL->userHasRoleName('Administradores');
      $this->addCol($_active);
    }
    
    public function addDeletedCol() {   //SOFT_DELETE
      global $_ACL;
      $_deleted = new Field();
      $_deleted->fieldname = 'DELETED';
      $_deleted->label     = 'Borrado';   
      $_deleted->type      = 'bool';
      $_deleted->default_value = '0';
      $_deleted->hide  = true;
      //$_deleted->editable  = true;
      //$_deleted->sortable  = true;
      // $_deleted->editable  = $_ACL->userHasRoleName('Administradores');
      $this->addCol($_deleted);
    }
    
    public function addFieldset($name, $legend=false, $type='tab',$priority = 1) { 
      if (!isset($this->fieldsets[$name])) $this->fieldsets[$name] = new fieldset($name,($legend?$legend:$name)); //FS
      $this->fieldsets[$name]->displaytype = $type; 
      $this->fieldsets[$name]->priority = $priority; 
    }

    public function addCol($col) { 
      ++$this->_order;
      if($this->pk===false){
          $this->pk=$col;
          $col->editable=false;
      }
      if($col->type=='html')
          $col->calculated=true;
      $col->driver    = $this->driver; //ADD 20140423
      $col->order     = $this->_order;
      $col->keyName   = $this->pk->fieldname;
      $col->tablename = $this->tablename;
      
      if(!$col->fieldset) $col->fieldset = $this->default_fieldset_name;

      if ($col->fk){
       // $this->setParent($col->fieldname,$col->fk_value);
      }   
        
      if ($col->translatable && CFG::$vars['site']['langs']['enabled']!==true && CFG::$vars['default_lang']!='es')
      $col->fieldname = $col->fieldname.'_'.CFG::$vars['default_lang'];

      if($col->type=='select') {
        if(!$col->source) $col->source = $col->fieldname.'_values';
        if($col->values){
         //CACHE if($col->values_all) $_SESSION['_CACHE']['values'][$col->source_all]=$col->values_all;
         //CACHE $_SESSION['_CACHE']['values'][$col->source]=$col->values;
        }else {
         //CACHE if($col->source_all) $col->values_all = $_SESSION['_CACHE']['values'][$col->source_all];
         //CACHE $col->values = $_SESSION['_CACHE']['values'][$col->source];
        }
        //if($col->source_all) $col->ajax_url = $this->ajax_url.'&op=list&values='.$col->source_all; 
        //                else 
                          $col->ajax_url = $this->ajax_url.'/op=list/values='.$col->source; //2.0
      }
      $this->cols[$this->_order] = $col;              // FIX !
      
      if($this->driver=='mysql'){
        if     ($col->type=='time') $this->fieldnames[]="DATE_FORMAT(".$col->fieldname.", '%H:%i') AS ".$col->fieldname;
        else if($col->type=='date') $this->fieldnames[]="DATE_FORMAT(".$col->fieldname.", '".DATE_FORMAT_MYSQL."') AS ".$col->fieldname; //DATE
                               else $this->fieldnames[]=$col->fieldname;
      } else $this->fieldnames[]=$col->fieldname;

      $this->js->addLine("window['X_{$this->tablename}']['{$this->_order}']=new Array();");
      $this->js->addLine("window['X_{$this->tablename}']['{$this->_order}']['name']='{$col->fieldname}';");
      $this->js->addLine("window['X_{$this->tablename}']['{$this->_order}']['type']='{$col->type}';");
      if($col->ajax_url!='') $this->js->addLine("window['X_{$this->tablename}']['{$this->_order}']['ajax_url']='{$col->ajax_url}';");

      if($col->title) $this->field_title = $col->fieldname;
      if(!$this->field_title && strpos('_'.$col->fieldname, 'name')>0)    $this->field_title = $col->fieldname;
      if(!$this->field_title && ($col->type == 'varchar' && $col->len>9)) $this->field_title = $col->fieldname;

      //    $description->langs    = array(5=>'en',3=>'de',37=>'ca');

      //if($_SESSION['userid']==1){
      if(is_array($col->langs)) {
        $col->html_before = '<div class="tabs-langs '.$col->type.'" id="tabs-'. ($col->css_id?$col->css_id:$col->fieldname) .'" data-simpletabs><ul>';
          $col->html_before .= '<li><a href="#tab-'.($col->css_id?$col->css_id:$col->fieldname).'">'.($col->type=='textarea'?' &nbsp; '.$col->label.' &nbsp; ':'').'<img src="'.SCRIPT_DIR_IMAGES.'/flags/16/'.CFG::$vars['default_lang'].'.png"></a></li>';
          foreach( $col->langs as $k=>$v) {                                                                                                                 
            $col->html_before .= '<li><a href="#tab-'.($col->css_id?$col->css_id:$col->fieldname).'_'.$k.'" title="'.$v.'" alt="'.$v.'"><!--'.$col->label.'_'.$k.'--><img src="'.SCRIPT_DIR_IMAGES.'/flags/16/'.$k.'.png"></a></li>';
          }
        $col->html_before .= '</ul>';           
        $n = 0;
        $numcols = count($col->langs);
        foreach( $col->langs as $k=>$v) {
          if($k!='es'){   //FIX CFG::$vars['default_lang']
              $colname = $col->fieldname.'_'.$k;
              ${$colname} = clone $col;
              ${$colname}->html_before = false;
              ${$colname}->fieldname = $colname;
              if($col->css_id) ${$colname}->css_id = $col->css_id.'_'.$k;
              ${$colname}->parent_fieldname = $col->fieldname;
              ${$colname}->label = $col->label.' '.$k;
              ${$colname}->langs = false;
              ${$colname}->hide = true;                     //NEW
              ${$colname}->default_value = '';
              //${$colname}->translation = true;
              ${$colname}->required = false;
              ${$colname}->wysiwyg = $col->wysiwyg === true;
              $n++;
              if ($n == $numcols) ${$colname}->html_after = '</div><!--<script type="text/javascript">$(function(){ $(\'#tabs-'.($col->css_id?$col->css_id:$col->fieldname).'\').tabs(); });  </script>-->';
              $this->addCol(${$colname});
          }
	    }
      }
      //}
    }

    public function getRowImages($id,&$row) {
      $row['IMAGES']=array();
      foreach( $this->cols as $col) {
        if($col->type=='file'){
          $subdir = $this->parent_key && $col->parent_id ? '/'.$this->parent_value : '';
          $url   = $col->uploaddir.$subdir.'/'.$row[$col->fieldname];
          $thumb = $col->uploaddir.$subdir.'/'.TN_PREFIX.$row[$col->fieldname];
          $big   = $col->uploaddir.$subdir.'/'.BIG_PREFIX.$row[$col->fieldname];
          //$url = MODULE.','.$this->tablename.','.$col->fieldname.','.$row[$this->pk->fieldname].',';
          //if ($this->parent_key && $col->parent_id) $url .= ','.$this->parent_value;
          //$url =  '/files/'. Crypt::base64_url_encode(Crypt::md5_encrypt($url,'fghmfgmg'));
          //$url .= '/'.$row[$col->fieldname];
          //$thumb = MODULE.','.$this->tablename.','.$col->fieldname.','.$row[$this->pk->fieldname].','.TN_PREFIX;
          //if ($this->parent_key && $col->parent_id) $thumb .= ','.$this->parent_value;
          //$thumb = '/files/'. Crypt::base64_url_encode(Crypt::md5_encrypt($thumb,'fghmfgmg'));
          //$thumb .= '/'.$row[$col->fieldname];
          $row['URL_A'] = "<a class=\"open_file_image\" href=\"{$url}\">{$row[$col->fieldname]}</a>";
          $row['URL'] = $url;
          $row['THUMB'] =  $thumb;
          $row['BIG']   =  file_exists($big) === true ? $big : $url;
          $row['EXT'] =  Str::get_file_extension($row[$col->fieldname]);
          $row['IMAGES'][$col->fieldname]=array();
        //  $row['IMAGES'][$col->fieldname]['URL_A'] = "<a class=\"swipebox\" href=\"{$url}\">{$row[$col->fieldname]}</a>";
          $row['IMAGES'][$col->fieldname]['URL_A'] = "<a  class=\"open_file_image\" href=\"{$url}\">{$row[$col->fieldname]}</a>";
          $row['IMAGES'][$col->fieldname]['URL'] = $url;
          $row['IMAGES'][$col->fieldname]['THUMB'] =  $thumb;
          $row['IMAGES'][$col->fieldname]['BIG'] =  $row['BIG'];
          $row['IMAGES'][$col->fieldname]['EXT'] =  $row['EXT'];

        }
      }
    }   
    
    public function addRow($row) { 
      $class = ($this->perms['edit']) ? ' edit' : '' ;
      $row['IMAGES']=array();
      $this->getRowImages($id,$row);
      $id    = /*'row-'.*/$row[$this->pk->fieldname];
      if($this->events && method_exists($this->events, 'OnCalculate')){ $this->events->OnCalculate($this,$row);}   //ADD 20131105
      if($this->events && method_exists($this->events, 'OnDrawRow')){ $this->events->OnDrawRow($this,$row,$class); }
      if($row['DELETED']=='1')  $class.=' deleted';
      if(!$row) return false; // Can be empty in OnDrawRow
      $title = ($this->output=='group') ? $row[$this->field_title] : '';

      if($this->tree) $cells = '<td>[EXPAND]</td>';
                 else $cells = '';
      if($this->output!=='group'){  // comment for show all non-hide fields
        foreach( $this->cols as $col) {
          if($this->output=='group' && $this->field_title == $col->fieldname) continue;
          if(!$col->hide ) { 
            $cell = $col->getCell(  $row[$this->pk->fieldname],
                                    $row[$col->fieldname],
                                    $this->inline_edit&&$col->inline_edit&&$col->editable,
                                    $this->markup_cell,
                                    $this->searchstring);   // pass $filterstring only for colorize search words
            if($this->events && method_exists($this->events, 'OnDrawCell')){ $this->events->OnDrawCell($this,$row,$col,$cell); } 
            $cells .= $cell;
          }
        }
      }  // comment for show all non-hide fields

      //if($this->inline_edit){  // FIX boolean attribute "show butons"
        $actions = ''; //<span class="actions">';
        if ($this->perms['edit'])   $actions .= ' <a href="#" title="Edit" class="edit" data-id="'.$id.'"><i class="fa fa-edit" style="color:#007fad;"></i></a> ';      
        if ($this->perms['delete']) $actions .= ' <a href="#" title="Delete" class="delete" data-id="'.$id.'"><i class="fa fa-trash-o" style="color:#f50a51;"></i></a> ';   
        if ($this->inline_edit&&$this->perms['edit'])   $actions .= ' <a href="#" class="save"><img title="Save" src="_images_/famfam/yes_gray.png"></a> ';                         

       // $actions .= '</span>';
      //}
      if($title && isset($_SESSION['_CACHE'][$this->tablename]['searchstring']) && $_SESSION['_CACHE'][$this->tablename]['searchstring'] != '')
        $title = Str::colorizeSearchText($_SESSION['_CACHE'][$this->tablename]['searchstring'],$title);
      if($this->tree) $class.=' [LEVEL]';
      $a = array('[ID]'      => $id, //REFACTOR $this->tablename.'-'.$id,  //OLD $id
                 '[CLASS]'   => $class,
                 '[TITLE]'   => $title,  
                 '[CELLS]'   => $cells,
                 '[ACTIONS]' => $actions);
      $markup = str_replace( array_keys($a), array_values($a), $this->markup_row) ;
      
      if($this->events && method_exists($this->events, 'OnAfterDrawRow')){ $this->events->OnAfterDrawRow($this,$row,$markup); }

      if ($this->output=='group'&&$this->field_group){
        if(!$this->rows[$row[$this->field_group->fieldname]]) $this->rows[$row[$this->field_group->fieldname]]=array();
        $this->rows[$row[$this->field_group->fieldname]][] =  array('row'=>$row,'text'=>$markup);
      }else if($this->tree){
        $this->rows[$row[$this->pk->fieldname]] =  array('parent'=>$row[$this->key_parent->fieldname],'markup'=>$markup);
      }else if($this->output=='custom'){
        $this->rows[$row[$this->pk->fieldname]] =  $markup;
      }else{
        $this->rows[] =  $markup;
      }
    }

    public function colByName($name){
      foreach( $this->cols as $col) { if($col->fieldname==$name) return $col; }
      return false;
    }
 
    public function rearrange($keys,$positions,$group){
      $result = array();
      $result['event']     = 'rearrange';
      $result['table'] = $this->tablename;
      $result['error']=0;
      $a = array_combine (  explode(',',$positions), explode(',',$keys) );
  	  $strVals = array();
      foreach($a as $k=>$v){
        if      ($this->driver=='oracle')$strVals[] = ' '.(int)$v.', '.((int)$k+1).' '; //.PHP_EOL;
        else if ($this->driver=='mysql') $strVals[] = 'WHEN '.(int)$v.' THEN '.((int)$k+1).' '; //.PHP_EOL;
      }

      if($group=='-1'){
        
        $sql_order = "UPDATE {$this->table_group} SET {$this->table_group_fieldname} = ";
        if      ($this->driver=='oracle') $sql_order .= "DECODE ({$this->table_group_pk_fieldname}, ".implode(',',$strVals)." , {$this->table_group_fieldname} )";
        else if ($this->driver=='mysql')  $sql_order .= "CASE {$this->table_group_pk_fieldname} ".join($strVals)." ELSE {$this->table_group_fieldname} END";
       // echo '<br />'.$sql_order.'<br />';
          if($this->sql_exec($sql_order)){
            $result['msg']='El orden de las filas ha sido actualizado'; //.'<br />'.$sql_order; 
            $result['error']='0';
          }else{
            $result['error']='1';
            $result['msg']=__LINE__.' '.self::lastError(); //$this->last_error(); //' Error en la instrucción: '.$sql_order;
          }
       // UPDATE TSK_TASKS SET TORDER = CASE TASK_ID WHEN 2 THEN 2 WHEN 1 THEN 3 WHEN 4 THEN 4 ELSE TORDER END
      }else{

        $sql_set_grp = "UPDATE {$this->tablename} SET {$this->field_group->fieldname} = {$group} WHERE {$this->pk->fieldname} IN ({$keys})";
        $result['group'] = $group;
        $result['keys']  = explode(',',$keys);


        if(!$strVals) throw new Exception("No data!");
        $sql_order = "UPDATE {$this->tablename} SET {$this->field_group_order->fieldname} = ";
        if      ($this->driver=='oracle') $sql_order .= "DECODE ({$this->pk->fieldname}, ".implode(',',$strVals)." , {$this->field_group_order->fieldname} )";
        else if ($this->driver=='mysql')  $sql_order .= "CASE {$this->pk->fieldname} ".join($strVals)." ELSE {$this->field_group_order->fieldname} END";
        if ($this->perms['edit']){
          if($this->sql_exec($sql_order)){
            //$filter = ($this->parent_key) ? ' WHERE '.$this->parent_key.'='.$post[$this->parent_key] : false;
            if($this->sql_exec($sql_set_grp)){
              $result['msg']='El orden de las filas ha sido actualizado'.'<br />'.$sql_order; 
              $result['error']='0';
              // $this->putMessage(array('table'=>$this->tablename,'event'=>$result['event']));
              $this->putMessage($result);
            }else{
              $result['msg']='Error al actualizar grupos. No es grave, pero debería hacerselo mirar. Instrucción fallida:'.$sql_set_grp; 
              $result['error']='0';
            }
          }else{
            $result['error']='1';
            $result['msg']=__LINE__.' Error en la instrucción: '.$sql_order;
          }
        } else {
          $result['error']='2';
          $result['msg']='Error '.__LINE__.': Acceso denegado';
        }

      }
      //header('Content-Type: application/json');
      echo json_encode($result);     
    }

    private function renderForm(&$form,$id=false){
      
      $buttons = new fieldset('dialog-buttons');
      $buttons->displaytype = 'footer'; 
      $form->buttons = $buttons;
      /** */
      if($this->state=='filter'||$this->state=='insert') {
        $button_reset = new Field(); $button_reset->type = 'submit';  $button_reset->fieldname = 'btn_reset';
        $button_reset->javascript = "$('#form_form_{$this->tablename}').reset();";
        $buttons->addElement(new formButton($button_reset   ,'Reset'));
      }
      /*
      $button_cancel = new Field(); $button_cancel->type = 'reset';  $button_cancel->fieldname = 'btnreset';
      $buttons->addElement(new formButton($button_cancel   ,'Cancelar'));
      */
      if(($this->state=='update'&&$this->perms['edit'])||$this->state!='update') {
        $button_submit = new Field(); 
        $button_submit->type = 'submit'; 
        $button_submit->disabled = true; 
        $button_submit->tablename = $this->tablename; 
        $button_submit->fieldname = 'btnsubmit';
        $button_submit->javascript = "$('#form_form_{$this->tablename}').submit()";
        $buttons->addElement(new formButton($button_submit   ,'Cerrar !!'));
      }
      /**/
      
      if($this->events && method_exists($this->events, 'OnBeforeShowForm')){ $this->events->OnBeforeShowForm($this,$form,$id); } //NEW

      $form->addElement($buttons);  
        
      $form->render(true);
      if($this->state!='filter' && ($this->events && method_exists($this->events, 'OnAfterShowForm'))) { $this->events->OnAfterShowForm($this,$form,$id);  } 
    }
    
    public function form_insert($idparent=false,$group=false){
      if($this->events && method_exists($this->events, 'OnBeforeInsert')){ $this->events->OnBeforeInsert($this);  } 
      if(!$this->perms['add']) { echo __LINE__.' Acceso denegado'; return false; }
      if($this->parent_key && !$this->parent_value) { echo __LINE__.' Acceso denegado'; return false; }
      $oldState = $this->setState('insert');
      $form = new FORM('form_'.$this->tablename);
      $form->setAction( $this->ajax_url.'/op=save/table='.$this->tablename );  //2.0
      $form->classname = 'form_datatable';
      $form->id = 'form_'.$this->tablename;
      foreach( $this->cols as $col) { 
        $col->minmax=false;
        if($group && $col->fieldname==$this->field_group->fieldname)$col->default_value=$group;
        if($col->type=='date'){ }
        if($col->type=='time'){ }
        //if($col->type=='file'){$col->crop=false; }
        $dummy_row = array();
        $dummy_cell = '';
        if($this->events && method_exists($this->events, 'OnDrawCell')) $this->events->OnDrawCell($this,$dummy_row,$col,$dummy_cell);
        if(($col->editable&&$col->visible)||$col->readonly){ 	        
          if (!isset($this->fieldsets[$col->fieldset])){
	           $this->fieldsets[$col->fieldset] = new fieldset($col->fieldset); //FS
	           $this->fieldsets[$col->fieldset]->displaytype = 'tab'; 
          }
          $this->fieldsets[$col->fieldset]->addElement($col->getFormElement());   //FS
        }
      }
      if($idparent){
        $id_parent = new Field();
        $id_parent->fieldname = $this->parent_key; // 'parent'  //FIX parent
        $this->fieldsets[$this->default_fieldset_name]->addElement(new formHidden($id_parent,$idparent));    //FS
      }
      foreach ($this->fieldsets as $fieldset){
        $form->addElement($fieldset);  //FS         //FIX: Error (leve) cuando no hay campos editables!
      }
      $this->renderForm($form);
      $this->setState($oldState);
    }
    
    public function form_update($id){

      if($this->events && method_exists($this->events, 'OnBeforeUpdate')){ $this->events->OnBeforeUpdate($this,$id);} 

      if(!$this->perms['edit']){ echo __LINE__.' Acceso denegado'; return false; }

      $row = $this->getRow($id);

      if($row===false){
            echo __LINE__.' No existe la fila '.$id; 
            return false; 
      }

      $oldState = $this->setState('update');
      $form = new FORM('form_'.$this->tablename);
      $this->addFieldset($this->default_fieldset_name); //FS
      $form->setAction( $this->ajax_url.'/op=update/table='.$this->tablename.'/page='.$this->page );
      $form->classname = 'form_datatable';
      $form->id = 'form_'.$this->tablename;

      if($this->parent_key && !$row[$this->parent_key]) { echo __LINE__.' Acceso denegado'; return false; }
      //echo 'parent: '.$row[$this->parent_key];
      $form->addElement(new formHidden($this->pk,$id));
      if ($this->parent_key){
        $id_parent = new Field();
        $id_parent->type      = 'int';
        $id_parent->fieldname = $this->parent_key;               
        $id_parent->label = 'Parent';
        $this->parent_value=$row[$this->parent_key];
        $this->fieldsets[$this->default_fieldset_name]->addElement(new formHidden($id_parent,$row[$this->parent_key]));    //FS
      }
      $dummy_cell = '';
      foreach( $this->cols as $col) { 
        $col->minmax=false;
        if( (!$col->translatable) && ($col->parent_fieldname)) continue;
        if($col->type=='bool')$col->default_value = false; //FIX This is a chapuza ...  or not
        $col->parent_id_value = $id;  // used for crop images
        if($this->events && method_exists($this->events, 'OnDrawCell')) $this->events->OnDrawCell($this,$row,$col,$dummy_cell);
        if(($col->editable&&$col->visible)||$col->readonly||$col->type=='html'){
          $this->addFieldset($col->fieldset); //FS
          $this->fieldsets[$col->fieldset]->addElement($col->getFormElement($row[$col->fieldname]));   //FS
        } 
      }
      foreach ($this->fieldsets as $fieldset){
        $form->addElement($fieldset);  //FS
      }
      $this->renderForm($form,$id);
      $this->setState($oldState);
    }
    
    public function insert( $post  ){  //, $files = false) {

      $sql = 'INSERT INTO '.$this->tablename.' ( ';
      $a = array();
      $n = array();
      $result = array();
      $result['event'] = 'insert';
      $result['table'] = $this->tablename;
      $result['error'] = 0;

      foreach( $this->cols as $col) {
          if($col->type=='textarea'){

              $_encrypted_text = $post[$col->fieldname];
              $_decrypted_text = Crypt::crypt2str($_encrypted_text,$_SESSION['token']);

              if($_encrypted_text !== NULL && $_decrypted_text === NULL){
  
                  $result['error'] = 1;
                  $result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION'); //<br>'.$col->fieldname.'<br>ENC: '.$_encrypted_text.'<br>DEC: '.$_decrypted_text.'<br>TOKEN'.$_SESSION['token']);

                  break;

              }else{
                  $post[$col->fieldname] = $_decrypted_text;
              }

          }
      }

      if($this->events && method_exists($this->events, 'OnInsert')){ $this->events->OnInsert($this,$result,$post); } 
      
      if($result['error']==0){
        foreach( $post as $k=>$v) { 
          foreach( $this->cols as $col) {
            if($col->fieldname===$k && $this->events && method_exists($this->events, 'OnPostCol')){ $this->events->OnPostCol($this,$result,$col,$v); } 
            if (($col->editable||$col->readonly) && ($col->fieldname===$k) && !$col->calculated) { // match !!
              $col->value=$v;
              if($col->required && !$v){                                                   //MOD 20140530 from here
                $result['error']=1;
                $result['msg']='El campo '.$col->label.' no puede estar vacío';
              }else{
                $n[] = $col->fieldname;
                $a[] = $this->format_value($col,$col->format_value($v));  //"'{$v}'";       //MOD 20140530 to here
              }  
            }
          }
        }
      
        if($this->whocolumns){ 
          $n[] = 'CREATED_BY';
          $a[] = $_SESSION['userid'];
          $n[] = 'CREATION_DATE';
          $a[] = $this->sql_currentdate();   //"'".date(DATE_FORMAT)."'"; //'d/m/Y")."'";
          $n[] = 'LAST_UPDATED_BY';
          $a[] = $_SESSION['userid'];
          $n[] = 'LAST_UPDATE_DATE';
          $a[] = $this->sql_currentdate();   //"'".date(DATE_FORMAT)."'"; //'d/m/Y")."'";
        }
      }
      
      if($result['error']==0){
        if ($this->parent_key){         // FIX check here: $post[$this->parent_key] has valid value
          if(!$post[$this->parent_key]){   //FIX parent
            $result['error']='3';
            $result['msg']='Parent value for '.$this->parent_key.' must be set!';
          }else{
            $a[] = "'{$post[$this->parent_key]}'";  //$this->parent_key]; //FIX parent
            $n[] = $this->parent_key;
            $result['parent']=$post[$this->parent_key];  //FIX parent
          }
        }
      }

      foreach( $this->cols as $col) { 
        if( $col->type=='file' && isset($_FILES[$col->fieldname]) ) {
          
          // $result['if_exists'] = $post['if_exists'];
          if ($col->prefix_filename){  
            $result['prefix_filename'] = $this->nextInsertId();
            if ($this->parent_key) $result['prefix_filename'] = $result['parent'].'_'.$result['prefix_filename'];
          }
          if ($this->savefile( $col , $_FILES[$col->fieldname] , $result ) ) {
            $a[] = "'{$result['local_file']}'";  //$this->parent_key];
            $n[] = $col->fieldname;
          }
        }
      }      
      
      if($result['error']==0){
        $result['table']=$this->tablename;
        $sql .= implode(',',$n).') VALUES ('. implode(',',$a).')';
        if ($this->perms['add']){
          $result['next_insert_id'] = $this->nextInsertId();
          if ($result['replaced']) {  
            $result['error']='2';    
            $result['msg']=__LINE__.' El arhivo ha sido reemplazado';  //ADD 20140422
          }else  if($this->sql_exec($sql)){     
             $filter = ($this->parent_key) ? ' WHERE '.$this->parent_key.'='.$post[$this->parent_key] : false;  //FIX parent
             $result['page']= ($this->parent_key) ? ceil($this->recordCount($filter)/$this->page_num_items) : 1 ;
             $result['msg']=$result['msg']?$result['msg']:'Fila añadida correctamente.'; //.$result['msg_file'];
             $result['error']='0';
             if($this->output=='group'){ 
               $result['group'] = $post[$this->field_group->fieldname]; 
             }
             $result['last_insert_id'] = $this->lastInsertId();
             if($this->events && method_exists($this->events,'OnAfterInsert')){ $this->events->OnAfterInsert($this,$result,$post); } 
             //$this->putMessage($result); //$_SESSION['username'].'::'.$this->tablename.':OnInsert:'. $result['error']); //.':'.$result['msg']);
          }else{
             $result['error']='1';
             if($this->error) $result['msg'] =__LINE__.$this->error;
                         else $result['msg'] =__LINE__.' '.self::lastError().'<br>Error en la instrucción: <pre>'.$sql.'</pre>';
          }
        } else {
          $result['error']='2';
          $result['msg']='Error '.__LINE__.': Acceso denegado';
        }
      }else{
        $result['page']=1;
        if ($this->parent_key && $this->parent_value){         // FIX check here: $post[$this->parent_key] has valid value
          $result['parent']=$this->parent_value;  //FIX parent
        }
      }
      //header('Content-Type: application/json');
      echo json_encode($result);     
    }
    
    public function file_upload_error_message($error_code) {
      switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:   return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:  return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:    return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:    return ''; //'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR: return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE: return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:  return 'File upload stopped by extension';
        default:                    return 'Unknown upload error '.$error_code;
      }
    } 

    private function mkdirs($dir, $mode = 0755, $recursive = true) {
      if( is_null($dir) || $dir === "" ) return FALSE;
      if( is_dir($dir) || $dir === "/" ) return TRUE;
      if( $this->mkdirs(dirname($dir), $mode, $recursive) ) return mkdir($dir, $mode);
      return FALSE;
    }

    public function savefile($col,$file,&$result){
      $result['local_file'] = false;
      try{
          if(is_array($file)==false) return false;
          if(count($file)<1) return false;
          $result['error']=0;
          
          //$result['msg']=print_r($file,true);

          //$this->print_var($file,'file');
          //      if($this->events && method_exists($this->events, 'OnSaveFile')) $this->events->OnSaveFile($this,$col,$file,$result);
          if($result['error']==0 && $file['error']==0){
            $ext = Str::get_file_extension($file["name"]);
            $filename = str_replace('.','_',Str::get_file_name($file["name"]));
            $valid_doc_ext = in_array('.'.strtolower($ext),$col->accepted_doc_extensions);
            $valid_img_ext = in_array('.'.strtolower($ext),$col->accepted_img_extensions);
            $result['ext'] = $ext;
            if($valid_doc_ext){
            
              $result['local_file'] = trim(Str::sanitizeName($filename.'.'.$ext,true));
              if ($col->prefix_filename) $result['local_file'] = $result['prefix_filename'].'_'.$result['local_file'];
              $dir  = $col->uploaddir;
              
              if(!file_exists($dir )) $this->mkdirs($dir);
              if(file_exists($dir )){
                if ($result['parent']&&$col->parent_id) {  //FIX parent
                  $dir .= '/'.$result['parent'];    //FIX parent
                  if(!file_exists($dir )) mkdir($dir);
                }
                if(file_exists($dir )){
                  if (file_exists($dir.'/'.$result['local_file'])){
                      @unlink( $dir.'/'.$result['local_file'] );
                      @unlink( $dir.'/'.TN_PREFIX.$result['local_file'] );
                      @unlink( $dir.'/'.BIG_PREFIX.$result['local_file'] );
                      @unlink( $dir.'/'.Str::get_file_name($result['local_file']).'.webp');
                      /**********/
                      if($this->parent_key) {
                         @unlink($col->uploaddir.'/'.$row[$this->parent_key].'/'.$row[$col->fieldname]);
                         @unlink($col->uploaddir.'/'.$row[$this->parent_key].'/'.TN_PREFIX.$row[$col->fieldname]);
                         @unlink($col->uploaddir.'/'.$row[$this->parent_key].'/'.BIG_PREFIX.$row[$col->fieldname]);
                         @unlink($col->uploaddir.'/'.$row[$this->parent_key].'/'.str_replace(array('.jpg','.png'),'.webp',$row[$col->fieldname]));
                      }else{
                         @unlink($col->uploaddir.'/'.$row[$col->fieldname]);
                         @unlink($col->uploaddir.'/'.TN_PREFIX.$row[$col->fieldname]);
                         @unlink($col->uploaddir.'/'.BIG_PREFIX.$row[$col->fieldname]);
                         @unlink($col->uploaddir.'/'.str_replace(array('.jpg','.png'),'.webp',$row[$col->fieldname]));
                      }
                      /******/
                    /*  
                    $iii = 0; 
                    if     ($result['if_exists'] == 'discard'){
                      $result['msg'] = 'Una imagen o documento con ese nombre ya existe'; 
                      $result['error'] = 4;
                    }else if($result['if_exists'] == 'replace'){


                      $result['replaced'] = 1;  
                      $result['file_msg'] = __LINE__.' Archivo reemplazado: '.$result['local_file'];  
                    }else if($result['if_exists'] == 'rename_old'){
                      $iii++; 
                      $new_filename = trim(Str::sanitizeName($filename.'.'.$iii.'.'.$ext,true));  //sanitizeName($filename.'.'.$iii,true);
                      while (file_exists($dir.'/'.$new_filename )){
                        $iii++; 
                        $new_filename = trim(Str::sanitizeName($filename.'.'.$iii.'.'.$ext,true));
                      }
                      rename($dir.'/'.$filename.'.'.$ext,$dir.'/'.$new_filename);
                      if($valid_img_ext){
                        if (file_exists($dir.'/'.TN_PREFIX.$filename.'.'.$ext))  {
                          rename($dir.'/'.TN_PREFIX.$filename.'.'.$ext,$dir.'/'.TN_PREFIX.$new_filename);
                          $result['thumb'] = $ext;
                        }
                        if (file_exists($dir.'/'.BIG_PREFIX.$filename.'.'.$ext)) {
                          rename($dir.'/'.BIG_PREFIX.$filename.'.'.$ext,$dir.'/'.BIG_PREFIX.$new_filename);
                          $result['big'] = $ext;
                        }
                      }
                      $result['file_msg'] = __LINE__.' Archivo existente renombrado: '.$new_filename;  
                    }else if($result['if_exists'] == 'rename_new'){
                      while (file_exists($dir.'/'.$result['local_file'])){
                        $iii++;
                        $result['local_file'] =  trim(Str::sanitizeName($filename.'.'.$iii.'.'.$ext,true));
                        $result['file_msg'] = __LINE__.' Archivo renombrado: '.$result['local_file'];  
                      }
                    }
                    *****/
                  }
                  if($result['error'] ==0){
                    $result['tmp_filename'] = $file["tmp_name"];
                    //$result['hash'] = sha1_file($file["tmp_name"]);
                    if($this->events && method_exists($this->events, 'OnBeforeSaveFile')) $this->events->OnBeforeSaveFile($this,$col, $dir.'/'.$result['local_file'],$result );
                    if($result['error']==0){

                        if (move_uploaded_file( $file["tmp_name"] , $dir.'/'.$result['local_file'])){
                          if(file_exists($dir.'/'.$result['local_file'])){
                            if($valid_img_ext){

                              // En este punto tenemos el archivo subido y con el nombre deseado 
                              if($this->events && method_exists($this->events, 'OnAfterSaveFile')) $this->events->OnAfterSaveFile($this,$col, $dir.'/'.$result['local_file'],$result );

                              if      (strtolower($ext) !== 'gif')
                                  img_resize($dir,$result['local_file']);
                              miniatura($dir,$result['local_file']);

                              if      (strtolower($ext) == 'jpg')  jpeg2webp( $dir.'/'.$result['local_file'] );
                              else if (strtolower($ext) == 'png')   png2webp( $dir.'/'.$result['local_file'] );

                              $result['thumb'] = $ext;
                              //if($this->events && method_exists($this->events, 'OnAfterSaveFile')) $this->events->OnAfterSaveFile($this,$col, $dir.'/'.$result['local_file'],$result );
                              //$this->print_var($file,'file');
                            }
                            $result['file_msg'] .= '<br />'.__LINE__.' Archivo ok: '.$result['local_file'];  
                            return true;
                          }else{
                            $result['error'] = 4;
                            $result['msg'] =__LINE__.' No se ha podido guardar el archivo: '.$result['local_file'];    //EXISTS!!!
                            return false;
                          }
                        }else{
                          $result['error'] = 4;
                          $result['msg'] =__LINE__.' No se ha podido copiar el archivo: '.$file["tmp_name"].' a '.$dir.'/'.$result['local_file'];    
                          return false;
                        }
                    }
                  }
                }else{
                  $result['error'] = 4;
                  //$this->print_var($result,'result '.__LINE__);
                  $err = error_get_last();
                  $result['msg'] = 'ERR: '.__LINE__.' '.$err['message'];
                  return false;
                }
              }else{
                $result['error'] = 4;
                //$err = error_get_last();
                //$result['msg'] = 'ERR: '.__LINE__.' '.$err['message'];
                $result['msg'] = 'ERR: '.__LINE__.' No existe el directorio ['.$dir.']';
                $result['root_dir'] = ROOT_DIR;
                //$this->print_var($result,'result '.__LINE__);
                return false;
              }
            }else{
              $result['error'] = 4;
              $result['errorcode'] = 'notallow';
              $result['msg'] =__LINE__.' Only '.implode(',',$col->accepted_doc_extensions).' files are allowed!';    
              return false;
            }
            
          }else if ($file['error']==4){

          }else{
            $result['error'] = 3;
            $result['msg'] = $result['msg']
                           ? $result['msg'].'<br /> '.$this->file_upload_error_message($file['error'])
                           : __LINE__.' '.$this->file_upload_error_message($file['error']);
            return false;
          }  
      }
      catch (Exception $e){   
         $result['error'] = 4;
         $result['msg'] =__LINE__.' Exception: '.$e->getMessage();    
      }
      
    }

    public function update_field( $fieldname, $fieldvalue, $fieldkey, $fieldkeyname=false, $tablename=false ) {
      if (!$fieldkeyname) $fieldkeyname = $this->pk->fieldname;
      if (!$tablename) $tablename = $this->tablename;
      $sql  = "UPDATE {$tablename} SET {$fieldname} = {$fieldvalue}";
      if($this->whocolumns) $sql .= ",LAST_UPDATED_BY = {$_SESSION['userid']},LAST_UPDATE_DATE = {$this->sql_currentdate()} ";
      $sql .= " WHERE {$fieldkeyname} = {$fieldkey}";
      if($this->sql_exec($sql)) return true; else return false;
    }
    
    public function update( $post ) {
      $sql = 'UPDATE  '.$this->tablename.' SET ';
      $a = array();
      $result = array();
      $result['event'] = 'update';
      $result['table'] = $this->tablename;
      $result['error'] = 0;
      $result['local_file'] = false;
      $result['old'] = $this->getRow($post[$this->pk->fieldname]);  //FIX Compare old with new to check for changes
      
      if($result['old']===false){

            $result['error']='1';
            $result['msg']='No existe la fila '.$post[$this->pk->fieldname]; //.' '.print_r($result['old'],true);

      }else{

          foreach( $this->cols as $col) { 
              if($col->type=='textarea'){

                  $_encrypted_text = $post[$col->fieldname];
                  $_decrypted_text = Crypt::crypt2str($_encrypted_text,$_SESSION['token']);

                  if($_encrypted_text !== NULL && $_decrypted_text === NULL){
      
                      $result['error'] = 1;
                      $result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION'); //.'<br>'.$col->fieldname.'<br>ENC: '.$_encrypted_text.'<br>DEC: '.$_decrypted_text.'<br>TOKEN'.$_SESSION['token'];


                      break;

                  }else{
                      $post[$col->fieldname] = $_decrypted_text;
                  }
              }
          }

          if($this->events && method_exists($this->events, 'OnUpdate')){ $this->events->OnUpdate($this,$result,$post); } 

      }      

      if($result['error']==0){
        if ($this->parent_key){         // FIX check here: $post[$this->parent_key] has valid value
          if($post[$this->parent_key] && $post[$this->parent_key]<1){
            $result['error']='3';
            $result['msg']='Parent value for '.$this->parent_key.' must be set!';
          }else{
            $result['parent']=$post[$this->parent_key];  //FIX parent???
          }
        }
      }
     
      if($result['error']==0){
        /************
            if (($col->editable||$col->readonly) && ($col->fieldname==$k) && !$col->calculated) { // match !!
              $col->value=$v;
              if($col->required && !$v){                                                   //MOD 20140530 from here
                $result['error']=1;
                $result['msg']='El campo '.$col->label.' no puede estar vacío';
              }else{
                $n[] = $col->fieldname;
                $a[] = $this->format_value($col,$col->format_value($v));  //"'{$v}'";       //MOD 20140530 to here
              }  
            }
        *************/
        foreach( $this->cols as $col) { // FIX emty bool and int values

          if($col->editable && !$col->calculated) {
         
            // if($col->type=='textarea'){
            //     $post[$col->fieldname] = Crypt::crypt2str($post[$col->fieldname],$_SESSION['token']);
            // }

            if($col->type == 'bool'||$col->type == 'int') {
                //FIX ?? if($this->request !== 'updatefield')
                if(!$post[$col->fieldname]) $post[$col->fieldname] =  "0"; 
            }

            if($col->type == 'select' && $post[$col->fieldname] == $col->clearValue) {
            }

            if( $col->type=='file' && isset($_FILES[$col->fieldname]) && ($_FILES[$col->fieldname]['error']!=4) ) {
  
              if($_FILES[$col->fieldname]) {
                  // $result['if_exists'] = $post['if_exists'];
                  if ($col->prefix_filename){ 
                    $result['prefix_filename'] = $post[$this->pk->fieldname]; //$post[$this->parent_key];
                    if ($this->parent_key) $result['prefix_filename'] = $post[$this->parent_key].'_'.$post[$this->pk->fieldname]; //;
                  }

                 /////////// $result['msg'] = 'SAVEFILE '.$_FILES[$col->fieldname].' '.$result['local_file'];
                 /////////// $result['error']=1;

                  if ($this->savefile( $col, $_FILES[$col->fieldname], $result)) $post[$col->fieldname] = $result['local_file'];
                                                                            else unset($post[$col->fieldname]);

              }else{
                  $post[$col->fieldname] =  ''; 
              }
            }
          }
        }
      }
      //$result['msg'] = '<pre>'; //.print_r($post,true).'</pre>';

      if($result['error']==0){
        foreach( $this->cols as $col) { 
          foreach( $post as $k=>$v) { 
            if($col->fieldname==$k && $this->events && method_exists($this->events, 'OnPostCol')){ $this->events->OnPostCol($this,$result,$col,$v); } 
            if ($col->editable && $col->fieldname===$k && !$col->calculated){ // match !!
               //$result['msg'] .=  "  {$col->editable} :: {$col->fieldname}=={$k} :: {$col->calculated}\n";
              if($col->required && !$v){
                $result['error']=1;
                $result['msg']='El campo '.$col->label.' no puede estar vacío';
              }else{
                $col->value=$this->format_value($col,$v);
                $a[] = $col->strSql();  
                //$result['msg'] .=  $col->strSql().' - '.$v."\n";
              }  
            }
          }
        }
        if($this->readscol){
          $a[] = $this->readscol->fieldname.' = '.$this->readscol->fieldname.' + 1';
        }
        if($this->whocolumns){
          $a[] = 'LAST_UPDATED_BY = '.$_SESSION['userid'];
          $a[] = 'LAST_UPDATE_DATE = '.$this->sql_currentdate();   //date("d/m/Y")
        }
      }

      //     $result['msg'] .= '</pre>';
      //     $result['error']=3;

      if($result['error']==0){
        $result['table']=$this->tablename;
        $result['page']=$this->page;
        $result['row']=$post[$this->pk->fieldname];
        $sql .= implode(',',$a);
        $sql .= ' WHERE '.$this->pk->fieldname.'='.$post[$this->pk->fieldname];
        if ($this->perms['edit']){
          if($this->sql_exec($sql)){
             if(!isset($result['msg'])) //COPY
             $result['msg']   = 'Fila actualizada correctamente.'; //.' '.$sql;//.$post[$this->pk->fieldname]; //
             $result['error'] = '0';
             $result['key']   = $this->parent_key;
             $result['value'] = $post[$this->parent_key];
             if($this->events && method_exists($this->events,'OnAfterUpdate')){ $this->events->OnAfterUpdate($this,$result,$post); } 
             //$this->putMessage(array('table'=>$this->tablename,'event'=>$result['event'],'id'=>$post[$this->pk->fieldname],'parent'=>$result['value'] )); //$result); //$_SESSION['username'].'::'.$this->tablename.':OnUpdate:'. $result['error']); //.':'.$result['msg']);
             $this->putMessage($result); //$_SESSION['username'].'::'.$this->tablename.':OnUpdate:'. $result['error']); //.':'.$result['msg']);
          }else{
             $result['error']='1';
             if($this->error) $result['msg'] =__LINE__.$this->error;
                         else $result['msg'] =__LINE__.' '.self::lastError(); //$this->last_error(); //'Error en la instrucción: <pre>'.$sql.'</pre>';
          }
        } else {
          $result['error']='2';
          $result['msg']='Error '.__LINE__.': Acceso denegado';
        }
      }else{
        $result['page']=1;
        if ($this->parent_key && $this->parent_value){         // FIX check here: $post[$this->parent_key] has valid value
          $result['parent']=$this->parent_value;  //FIX parent
        }
      }
      //header('Content-Type: application/json');
      echo json_encode($result);     
    }

    public function delete_file($filename,&$result) {
        if(is_file($filename)){
            if(file_exists($filename)){
                if(!is_writable($filename)){
                    $result['error'] = 2;
                    $result['msg'] .= 'File '.$filename.' not writable<br />';
                }else{
                    try{
                        if(!unlink($filename)){
                            $result['error'] = 2;
                            $result['msg'] = 'No se ha podido borrar el archivo '.$filename;
                        }             
                    }catch(Exception $e){
                        $result['error'] = 2;
                        $result['msg'] = $e->message;
                    }
                }
            }
        }
    }

    public function delete($id) {
      //FIX if $this->detail Check for childs and throw exception 
      $result = array();
      $result['event'] = 'delete';
      $result['table'] = $this->tablename;
      $result['error'] = 0;

      //ALT $result = call_user_func($this->OnDelete, $this, $result, $id);
      if($this->events && method_exists($this->events, 'OnDelete')){
        $this->events->OnDelete($this,$result,$id);
      } 

      if($result['error']==0){
        foreach( $this->cols as $col) { 
          if( $col->type=='file' ){                        //ADD 20131025
             $row = $this->getRow($id);
             if($this->soft_delete){            //SOFT_DELETE
                // if($this->parent_key) $result['msg_file']=$col->uploaddir.'/'.$row[$this->parent_key].'/'.$row[$col->fieldname];
                //                 else  $result['msg_file']=$col->uploaddir.'/'.$row[$col->fieldname];
             }else{
                 if($this->parent_key) {
                     $mmm = $col->uploaddir.'/'.$row[$this->parent_key].'/'.$row[$col->fieldname];
                     $this->delete_file($col->uploaddir.'/'.$row[$this->parent_key].'/'.$row[$col->fieldname],$result);
                     $this->delete_file($col->uploaddir.'/'.$row[$this->parent_key].'/'.TN_PREFIX.$row[$col->fieldname],$result);
                     $this->delete_file($col->uploaddir.'/'.$row[$this->parent_key].'/'.BIG_PREFIX.$row[$col->fieldname],$result);
                     $this->delete_file($col->uploaddir.'/'.$row[$this->parent_key].'/'.str_replace(array('.jpg','.png'),'.webp',$row[$col->fieldname]),$result);
                 }else{
                     $mmm = $col->uploaddir.'/'.$row[$col->fieldname];
                     $this->delete_file($col->uploaddir.'/'.$row[$col->fieldname],$result);
                     $this->delete_file($col->uploaddir.'/'.TN_PREFIX.$row[$col->fieldname],$result);
                     $this->delete_file($col->uploaddir.'/'.BIG_PREFIX.$row[$col->fieldname],$result);
                     $this->delete_file($col->uploaddir.'/'.str_replace(array('.jpg','.png'),'.webp',$row[$col->fieldname]),$result);
                 }
             }

          }
        }
      }

      if($result['error']==0){
        if ($this->perms['delete']) {
          if($this->soft_delete){            //SOFT_DELETE

              // delete childs
              foreach($this->detail_tables as $detail_table){
                  $this->sql_exec("UPDATE {$detail_table} SET DELETED=1 WHERE {$this->detail_tables_keys[$detail_table]}={$id}");
              }

              if ($this->sql_exec("UPDATE {$this->tablename} SET DELETED=1 WHERE {$this->pk->fieldname}={$id}")){
                  $result['id'] = $id;
                  $result['msg']=__LINE__.' Fila '.$id.' marcada como borrada.';  //.$result['msg_file'];
                  $result['deleted'] = 'soft' ;
              }else{
                  $result['msg']=__LINE__.' Fila '.$id.' no se pudo marcar para borrar. Revise la configuración de la tabla';
                  $result['error']='1';
              }
          }else{

              if($this->on_delete_cascade){
                  foreach($this->detail_tables as $detail_table){
                      $this->sql_exec("DELETE FROM {$detail_table} WHERE {$this->detail_tables_keys[$detail_table]}={$id}");
                     //$result['msg']="DELETE FROM {$detail_table} WHERE {$this->detail_tables_keys[$detail_table]}={$id}";
                  }
              }else{
                  foreach($this->detail_tables as $detail_table){
                      $childs = $this->recordCount('SELECT COUNT(0) FROM '.$detail_table.' WHERE '.$this->detail_tables_keys[$detail_table].' = '.$id);
                      if($childs >0) {
                          $result['error'] = 1;  // Abort deletion !!
                          $result['msg'] = 'Esta fila no puede eliminarse porque tiene '.$childs.' filas hijas'; // ['.$detail_table.']['.$owner->pk->fieldname.']['.$id.']';  //TODO: Translate this
                      }
                  }
              }
              if($result['error']==0){
                  if($this->sql_exec("DELETE FROM {$this->tablename} WHERE {$this->pk->fieldname}={$id}")){
                      $result['id'] = $id;
                      $result['msg']=__LINE__.' Fila '.$id.' eliminada correctamente';
                      $result['error']='0';
                      //$this->putMessage($result);
                  }else{
                    $result['error']='1';
                    $result['msg']=__LINE__.' Error en la instrucción: '."DELETE FROM {$this->tablename} WHERE {$this->pk->fieldname}={$id}"; //$sql;
                  }
              }
          }
        }else {
          $result['error'] = '2';
          $result['msg']='Error '.__LINE__.': Acceso denegado';
        }
      }
      if($this->events && method_exists($this->events, 'OnAfterDelete')){
        $this->events->OnAfterDelete($this,$result);
      }
      echo json_encode($result);   
    }
     
    private function filter2string($filter){
      $conditions = array();
      $conditionsh = array();
      foreach( $filter as $k=>$v) { 
        foreach( $this->cols as $col) { 
          if ( $col->filtrable && $col->fieldname==$k){ // match !!
                
            if($col->type=='select' && !$v){
                
            }elseif($col->type=='select' && $v=='ALL'){
              //$v='0';
              //if($this->driver=='oracle') $conditions[] =  'NVL('.$col->fieldname.',0)<>1';
              //                       else $conditions[] =  $col->fieldname.' IS NOT NULL ';
            }else  if($col->type=='date'  && $v){
            
              $conditionsh[] = $col->label.': '.$v;

              if( strlen($v)<10 ) {
                $v  = implode('-',array_reverse(explode('-',$v))); 
                $conditions[] =  "{$col->fieldname} LIKE '%{$v}%'";
              }else if($filter[$col->fieldname.'_end']){             //$filter[$this->colByName($col->fieldname.'_end')->fieldname]
                $v2 = $filter[$col->fieldname.'_end'];
                if($this->driver=='oracle'){  //FIX
                 $v  = implode('-',array_reverse(explode('-',$v)));        //        $v =  date_format( date_create_from_format('d-m-Y', $v) , 'Y-m-d');
                 $v2 = implode('-',array_reverse(explode('-',$v2)));
                }
                $conditions[] = " ({$col->fieldname} >= STR_TO_DATE('{$v}', '".DATE_FORMAT_MYSQL."') AND "
                              . "  {$col->fieldname} <= STR_TO_DATE('".$filter[$col->fieldname.'_end']."', '".DATE_FORMAT_MYSQL."'))";  
              }else{
                $conditions[] =  "{$col->fieldname} = STR_TO_DATE('{$v}', '".DATE_FORMAT_MYSQL."')";
              }
            }else  if($col->type=='datetime'  && $v){
              $v = str_replace('T',' ',$v);
              $conditionsh[] = $col->label.': '.$v;

              if( strlen($v)<10 ) {
                $v  = implode('-',array_reverse(explode('-',$v))); 
                $conditions[] =  "{$col->fieldname} LIKE '%{$v}%'";
              }else if($filter[$col->fieldname.'_end']){             //$filter[$this->colByName($col->fieldname.'_end')->fieldname]
                $v2 = $filter[$col->fieldname.'_end'];
                $v2 = str_replace('T',' ',$v2);
                //if($this->driver=='oracle'){  //FIX
                // $v  = implode('-',array_reverse(explode('-',$v)));        //        $v =  date_format( date_create_from_format('d-m-Y', $v) , 'Y-m-d');
                // $v2 = implode('-',array_reverse(explode('-',$v2)));
                //}
                $conditions[] = " ({$col->fieldname} >= STR_TO_DATE('{$v}', '".DATETIME_FORMAT_MYSQL."') AND "
                              . "  {$col->fieldname} <= STR_TO_DATE('".$v2."', '".DATETIME_FORMAT_MYSQL."'))";  
              }else{
                $conditions[] =  "{$col->fieldname} = STR_TO_DATE('{$v}', '".DATETIME_FORMAT_MYSQL."')";
              }
            }else  if($col->minmax===true  && $v){
            
              //$conditionsh[] = $col->label.': '.$v;

              
              //if( strlen($v)<10 ) {
              //  $v  = implode('-',array_reverse(explode('-',$v))); 
              //  $conditions[] =  "{$col->fieldname} LIKE '%{$v}%'";
              //}else 
              if($filter[$col->fieldname.'_end']>0){             //$filter[$this->colByName($col->fieldname.'_end')->fieldname]
                $v2 = $filter[$col->fieldname.'_end'];
                $conditions[] = " ({$col->fieldname} >= '{$v}' AND {$col->fieldname} <= '".$v2."')";  
                $conditionsh[] = " {$col->label} >= ".$v;
                $conditionsh[] = " {$col->label} <= ".$v2;  
              }else{
                $conditions[] =  "{$col->fieldname} = '{$v}'";
                $conditionsh[] = $col->label.': '.$v;
              }

            }else  if($col->type=='int'  && $v){
              $col->value=$this->format_value($col,$v);
              //$conditionsh[] = $col->label.': '.$v;
              if($filter[$col->fieldname.'_end']){         
                $conditions[] = " ({$col->fieldname} >= {$v} AND  {$col->fieldname} <= ".$filter[$col->fieldname.'_end'].")";  
                $conditionsh[] = '('.$col->label.'>='.$v.' AND '.$col->label.'<='.$filter[$col->fieldname.'_end'].')';
              }else{
                $conditions[] =  "{$col->fieldname} = {$v}";
                $conditionsh[] = $col->label.': '.$v; //col->values[$v];
              }
              //$conditionsh[] = $col->label.': '.$v;
            }else  if($col->type=='select'  && ($v  && $v!='ALL')){
              //$conditionsh[] = $col->label.': '.$v;
              if($filter[$col->fieldname.'_end'] && $filter[$col->fieldname.'_end']!='ALL'){         
                $conditions[] = " ({$col->fieldname} >= '{$v}' AND  {$col->fieldname} <= '".$filter[$col->fieldname.'_end']."')";  
                $conditionsh[] = '('.$col->label.'>='.$col->values[$v].' AND '.$col->label.'<='.$col->values[$filter[$col->fieldname.'_end']].')';
              }else{
                $conditions[] =  "{$col->fieldname} = '{$v}'";
                $conditionsh[] = $col->label.': '.$col->values[$v];
              }

            }else  if($col->type=='bool'){
              if($v=='ALL'){
                  
              }else if($v=='T'){
                $conditions[] =  $col->fieldname.' = 1 ';
                $conditionsh[] = $col->label.': Sí';
              }else if($v=='F'){
                if($this->driver=='oracle') $conditions[] =  'NVL('.$col->fieldname.',0)=0';
                                       else $conditions[] =  'IFNULL('.$col->fieldname.',0)=0';//$col->fieldname.' <> 1 '; //FIX
                $conditionsh[] = $col->label.': No';
              }
            }else  if($v){
              $col->value=$this->format_value($col,$v);
              $conditions[] =  $col->strSqlFilter();
              if($col->type=='select')$conditionsh[] = $col->label.': '.$col->values[$v];
                                 else $conditionsh[] = $col->label.': '.$v;
            }
          }
        }
      }
      $this->strfilter =  $this->strfilter = (count($conditionsh)>0) ? implode(', ',$conditionsh) : '';
      return (count($conditions)>0) ? implode(' AND ',$conditions) : false;  // in filter form can get operator (AND or OR)
    }

    public function filter($post=false){
      if(!$this->perms['filter']){
          $result['error']=1; 
          $result['msg']= __LINE__.' Acceso denegado'; 
          //header('Content-Type: application/json');
          echo json_encode($result);   
      }else{
          $oldState = $this->setState('filter');
          if($post){
            $result = array();
            $result['event']     = 'filter';
            $result['table'] = $this->tablename;
            $result['post'] = $post;
            $result['msg'] = 'Filtro activado!';
            $result['page']=1;
            $result['error']=0;

            // event OnFilter, in event can show options for use AND or OR and grouping fields.
            if($this->events && method_exists($this->events, 'OnFilter')){ $this->events->OnFilter($this,$result,$post); } 

            // if showform, if mode==filter can make something
            if($result['error']==0){
              $_SESSION['_CACHE'][$this->tablename]['filter'] = $post;
              $this->filterstring = $this->filter2string($post);
            }
            $_SESSION['_CACHE'][$this->tablename]['filterstring'] = $this->filterstring ? $this->filterstring : '';  //$this->filterstring;
            $_SESSION['_CACHE'][$this->tablename]['strfilter'] = $this->strfilter ? $this->strfilter : '';  //$this->filterstring;
            $result['msg']=$this->strfilter ? $this->strfilter : 'Filtro desactivado';
            //header('Content-Type: application/json');
            echo json_encode($result);   

            if($this->log){        
                $_subject = $this->tablename.' -> '.$this->strfilter;
                $_message = '';
                $this->log_write(__FUNCTION__,$_subject,$_message);
            }


          }else {
            
            echo '<div class="info">En los campos de texto puede usar comodines SQL.</div>';
            $form = new FORM('form_'.$this->tablename);
            $form->setAction( $this->ajax_url.'/op=filter/table='.$this->tablename.'/page=1' );
            $form->classname = 'form_datatable';
            $form->id = 'form_'.$this->tablename;
            foreach( $this->cols as $col) {    //FIX $col->filtrable      
              if(isset($_SESSION['_CACHE'][$this->tablename]['filter'][$col->fieldname])) $col->value=$_SESSION['_CACHE'][$this->tablename]['filter'][$col->fieldname];
              //  FIX if type==date show two inputs for date range, >= and <= //modify class from
              if ($col->type=='bool'){
                $col->default_value='ALL';
                $col->type='select';
                $col->values = array('ALL'=>'Todos','T'=>'Sí','F'=>'No');
              }elseif ($col->type=='date'||$col->type=='datetime'){
                $col->allowNull =true;
              }elseif ($col->type=='select'){
                $col->allowNull =true;
                $col->clearValue = '0';
                $col->clearText = 'Todos';
                $col->default_value = '0';
              } 
              if($col->filtrable) {
                if($col->readonly) $col->readonly = false;
                if ($col->minmax===true||$col->type=='date'||$col->type=='datetime'||(($col->type=='int'||$col->type=='select')&&$col->range)) {
                  $col->minmax=true;
                  if($_SESSION['_CACHE'][$this->tablename]['filter'][$col->fieldname.'_end']) 
                    $col->value_end=$_SESSION['_CACHE'][$this->tablename]['filter'][$col->fieldname.'_end'];
                }
                $form->addElement($col->getFormElement($col->value));
              }
            }
            $this->renderForm($form);
          }
          $this->setState($oldState);
        }
    }
    
    public function str_SqlFields(){
      $result = $separator = '';
      foreach( $this->cols as $col) { 
        if(!$col->calculated){
          $result .= $separator.$this->formatFieldname($col);
          $separator = ',';
        }
      }
      return $result;
    }

    //$prev_next =  Table::getPrevNext('NOT_NEWS',$_ID_,'NOT_ID','concat(\'<a href="'.MODULE.'/\',NOT_NAME,\'"> \',NOT_TITLE,\'</a>\')','NOT_ID',$_and);

    public static function getPrevNext($table,$id,$pk='ID',$col_url='NAME',$col_label='TITLE',$order_by='ID',$conditions=''){ 
            
        // Oracle, posgre, etc. con LAG y LEAD
        // SELECT LAG(URL) OVER (ORDER BY ID) AS PREV,
        //       LEAD(URL) OVER (ORDER BY ID) AS NEXT
        //       FROM BLOG WHERE ID = 2;

        // Oracle sin LAG or LEAD 
        // SELECT (SELECT URL FROM BLOG WHERE ID < 2 ORDER BY ID DESC FETCH FIRST 1 ROW ONLY) AS PREV,
        //        (SELECT URL FROM BLOG WHERE ID > 2 ORDER BY ID ASC  FETCH FIRST 1 ROW ONLY) AS NEXT
        // FROM DUAL;

        // MySQL, MariaDB, sqLite, etc.
        // SELECT (SELECT URL FROM BLOG WHERE NOT_ID<ID ORDER BY ID DESC LIMIT 1) AS PREV, 
        //        (SELECT URL FROM BLOG WHERE NOT_ID>ID ORDER BY ID      LIMIT 1) AS NEXT  

        $_module = CFG::$vars['default_module'] == MODULE ? '' : MODULE;
        
        if (CFG::$vars['db']['type']=='sqlite'){
            $href_prev = '\'<a href="'.$_module.'/\'||'.$col_url.'||\'">\'||'.$col_label.'||\'</a>\'';
            $href_next = '\'<a href="'.$_module.'/\'||'.$col_url.'||\'">\'||'.$col_label.'||\'</a>\'';
        }else{
            $href_prev = 'concat(\'<a href="'.$_module.'/\','.$col_url.',\'">\','.$col_label.',\'</a>\')';
            $href_next = 'concat(\'<a href="'.$_module.'/\','.$col_url.',\'">\','.$col_label.',\'</a>\')';
        }
        $sql = "SELECT (SELECT {$href_prev} FROM {$table} WHERE {$pk}<{$id} {$conditions} ORDER BY {$order_by} DESC LIMIT 1) AS PREV, "
             . "       (SELECT {$href_next} FROM {$table} WHERE {$pk}>{$id} {$conditions} ORDER BY {$order_by}      LIMIT 1) AS NEXT  ";
        //echo $sql;
        return Table::sqlQuery($sql)[0];
    }

    public function getRow($id) {
      $row = false;
      $this->debug('SELECT '.$this->str_SqlFields().' FROM '.$this->tablename.' WHERE '.$this->pk->fieldname.'='.$id);   //MOD 20131105
      $res = $this->sql_query('SELECT '.$this->str_SqlFields().' FROM '.$this->tablename.' WHERE '.$this->pk->fieldname.'='.$id);
      if ($res)  {
          $row = $res[0]; //FIX check $res rows
          if($this->events && method_exists($this->events, 'OnCalculate')){ $this->events->OnCalculate($this,$row);}   //ADD 20131105
          $this->getRowImages($id,$row);
      }
      return $row;
   }
    /*
    
    public function getDetail_JSON($id) {
      $row = $this->getRow($id); //MOD 20131105
      //header('Content-Type: application/json');
      echo json_encode($row);   
    }

    public function getDetail($id) {
      $row = $this->getRow($id);  //MOD 20131105
      if($this->events && method_exists($this->events, 'OnBeforeShowDetail')){$this->events->OnBeforeShowDetail($this,$row);} 
      echo $this->format_item['begin'];
      $_item_tags    = array();
      $_item_values  = $row;
      $this->debug($_item_values);
      foreach( $this->cols as $col) { 
        if($col->type=='select') {        //   $this->debug($col->fieldname.' ->'.$_item_values[$col->fieldname],true);
          $_item_values[$col->fieldname] = $col->values;  //CACHE $_SESSION['_CACHE']['values'][$col->source][$_item_values[$col->fieldname]]; // 
        }
        $_item_tags[]='['.$col->fieldname.']';
      }
      if($_SESSION['_CACHE'][$this->tablename]['searchstring'])
        $_result = Str::colorizeSearchText($_SESSION['_CACHE'][$this->tablename]['searchstring'],str_replace($_item_tags, $_item_values, $this->format_item['body']));
      else
        $_result = str_replace($_item_tags, $_item_values, $this->format_item['body']);
      echo $_result; //
      echo  $this->format_item['end']; // str_replace($_item_tags, $_item_values, $this->format_item['end']);

    }
    */
    public function getPrint($id,$template='print') {
      if(!$this->perms[$template]){ echo __LINE__.' Acceso denegado - '.$template.' - '.$id; return false;  } 
      if($this->events && method_exists($this->events, 'OnBeforePrint')) $this->events->OnBeforePrint($this, $template, $id);
      $row = $this->getRow($id); 
      $_item_tags    = array();
      $_item_values  = array(); //$row;
      foreach( $this->cols as $col) { 
        if($col->type=='select') {
          $_item_values[$col->fieldname.'_ID'] = $row[$col->fieldname]; 
          $_item_tags[]='['.$col->fieldname.'_ID]';
          $_item_values[$col->fieldname] = $col->values[$row[$col->fieldname]];  //CACHE $_SESSION['_CACHE']['values'][($col->source_all)?$col->source_all:$col->source][$row[$col->fieldname]]; 
        } else  {
          $_item_values[$col->fieldname] = $row[$col->fieldname];
        }
        $_item_tags[]='['.$col->fieldname.']';
      }

      //if($this->events && method_exists($this->events, 'OnBeforePrint')) $this->events->OnBeforePrint($this, $template, $id);

 
      if($this->events && method_exists($this->events, 'OnPrint')) {
         $this->events->OnPrint($this, $template, $_item_tags, $_item_values);
      }
      //Vars::debug_var($_item_values);

      if ($template) include(SCRIPT_DIR_CLASSES.'/scaffold/templates/'.$template.'/header.php');          
      if ($template) include(SCRIPT_DIR_CLASSES.'/scaffold/templates/'.$template.'/body.php'); 

      if (!$this->format_item){
          echo '<table class="zebra">';
          foreach ( $_item_values as $k=>$v){
              echo '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
          }
          echo '</table>';
      }

      if($this->format_detail){
        foreach ($this->format_detail as $format_detail){
          $this->debug(sprintf( $format_detail['sql'],$id ));

          if($format_detail['sql']=='') continue;
          //echo '['.$id.']<br>';
          //echo '['.$format_detail['sql'].']<br>';
          
          $d_res = $this->sql_query(  sprintf( $format_detail['sql'],$id ) );                  //'SELECT * FROM '.$format_detail['name'].' WHERE '.$format_detail['pk'].'='.$id);
          if($d_res){
                //echo $format_detail['begin'];                                                     // move to print/detail_begin.php
                if ($template) include(SCRIPT_DIR_CLASSES.'/scaffold/templates/'.$template.'/detail_begin.php');
                if($this->events && method_exists($this->events, 'OnBeforePrintDetail')) $this->events->OnBeforePrintDetail($this, $template);
                foreach($d_res as $d_row){
                    $_d_item_tags    = array(); //array_keys($d_row);
                    $_d_item_values  = $d_row;
                    foreach (array_keys($d_row) as $tag){$_d_item_tags[] ='['.$tag.']';}
                    if($this->events && method_exists($this->events, 'OnPrintDetail')) $this->events->OnPrintDetail($this, $template, $_d_item_tags, $_d_item_values);
                    //echo str_replace($_d_item_tags, $_d_item_values, $format_detail['body']);       // move to print/detail_row.php
                    if ($template) include(SCRIPT_DIR_CLASSES.'/scaffold/templates/'.$template.'/detail_row.php');
                  }
                  //echo $format_detail['end'];                                                       // move to print/detail_end.php
                if ($template) include(SCRIPT_DIR_CLASSES.'/scaffold/templates/'.$template.'/detail_end.php');
                if($this->events && method_exists($this->events, 'OnAfterPrintDetail')) $this->events->OnAfterPrintDetail($this, $template);
          }else{
                echo $format_detail['empty']??'';
          }
        }
      }

      if($this->events && method_exists($this->events, 'OnAfterPrint'))  $this->events->OnAfterPrint($this, $template);

      if ($template) include(SCRIPT_DIR_CLASSES.'/scaffold/templates/'.$template.'/footer.php');      

      if($this->log){        
          $_subject = strtoupper($template).' '.$id.' '.$this->tablename;
          $_message = print_r($_item_values,true);
          $this->log_write(__FUNCTION__,$_subject,$_message);
      }
    
    }

    public function searchForm($table,$idparent=false) { 
       if($this->show_inputsearch===false) return '';
       $class = '';
       $html .= '<div class="search-form-input';
       if($this->searchstring) $html .= ' search-active';
       $html .= '" style="/'.'*position:relative;*'.'/">';
       $html .= '<input id="input-search-'.$this->tablename.'"';
       $html .= ' type="text" placeholder="Buscar ..." title="Se admiten búsquedas parciales y múltiples, mayúsculas, tildes, etc."';
       //$html .= ' onfocus="form_search_focus = true;" onblur="form_search_focus = false;"';
       if ($table) $html .= ' table="'.$table.'"';
       if ($table) $html .= ' row="'.$idparent.'"';
       if ($this->searchstring) $html .= ' value="'.$this->searchstring.'"';
       $html .= '/>'; 
       $html .= '<a class="input-icon-reset" table="'.$this->tablename.'"><i class="fa fa-remove"> </i></a>';
       $html .= '</div>';
       return $html; 
    }

    function value2id($array,$key){
      $key = Str::word2regexp($key);
      foreach ($array as $k => $v) {
        if (preg_match("/$key/i", "$v")) return $k;      // if ($key==$v) return $k;
      }
      return false; //$key;
    }

    function calculateTotal(){
      if ( !isset($_SESSION['_CACHE'][$this->tablename]['filter'] ) ){   // If no filter is set
        if($this->default_filter) {                                         // then apply default filter
          $_SESSION['_CACHE'][$this->tablename]['filter'] = $this->default_filter;
          $_SESSION['_CACHE'][$this->tablename]['filterstring'] = $this->filter2string($this->default_filter);
        }
      }
      $str_filter = '';
      $this->old_filter = $this->filter;  
      if($_SESSION['_CACHE'][$this->tablename]['filterstring']){          // Set filter attribute
        $this->filter = $_SESSION['_CACHE'][$this->tablename]['filterstring']; 	      
      }else{
      }
      if($this->parent_key){ //&&$this->parent_value){
        if($this->parent_value){
          $this->filter = ($this->filter) 
                         ? $this->filter.' AND '.$this->parent_key.'='.$this->parent_value 
                         : $this->parent_key.'='.$this->parent_value;
        }else{
          return 0;
        }
      }else{
        //$this->filter = $this->filter;
      }
      if($this->where) {     
          if($this->filter) $this->filter .= ' AND '.$this->where;
                       else $this->filter  = $this->where;
      }
      if($this->searchstring) {     //  make search condition over searchables fields
          if($this->filter) $this->filter .= ' AND ';
          $words = explode(' ',$this->searchstring);
          $filterConditions = array();
          foreach ($words as $word) {
            if(strlen($word)>2){
              $colConditions=array();
               foreach ($this->cols as $col) {
                if($col->searchable) {
                  if($col->type=='select') {
                    $tmpCond = $this->value2id($col->values,$this->searchstring);
                    if($tmpCond) $colConditions[] = " {$col->fieldname} =  '".$tmpCond."' ";
                  }else{
                    if($this->driver=='oracle') //$filterConditions[] = " {$col->fieldname} LIKE '%{$word}%' ";
                      $colConditions[] = " translate(upper({$col->fieldname}), 'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÂÊÎÔÛ', 'AEIOUAEIOUAEIOUAEIOU') LIKE translate(upper('%{$word}%'), 'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÂÊÎÔÛ', 'AEIOUAEIOUAEIOUAEIOU') ";
                    else
                      $colConditions[] = " {$col->fieldname} LIKE '%{$word}%' ";
                  }
                }
              }
              if (count($colConditions)>0) $filterConditions[] = implode(' OR ',$colConditions);
            }
          }
          if(count($filterConditions)>0)
            $this->filter .= '('.implode(' )'.$this->searchOperator.'( ',$filterConditions).')';
         // else
         //   $this->filter = '';
          $filterConditions = false;
      }

      if($this->filter) {
        $this->debug(__LINE__.'Filter: '.$this->filter);
        $str_filter = ' WHERE '.$this->filter;
      }
      if($this->searchstring) {
	      $this->debug(__LINE__.' Busqueda: '.$this->searchstring.' :: '.$_SESSION['_CACHE'][$this->tablename]['searchstring']); //,true);
	      $_SESSION['_CACHE'][$this->tablename]['searchfilterstring'] = $str_filter;



        if($this->log){        
            $_subject = 'SEARCH '.$this->tablename.' -> '.$this->searchstring;
            $_message = '';
            $this->log_write('Search',$_subject,$_message);
        }




      }else{
	      $_SESSION['_CACHE'][$this->tablename]['searchfilterstring'] = '';
      }
      if($this->sql_total){
        $_total = $this->recordCount($this->sql_total); //,$_sql_count_where);
        $this->debug( __LINE__.'::'.$this->sql_total.'  >>> Total:'.$_total);
      }else if($this->groupby){
        $str_filter  .= ' GROUP BY '.$this->groupby;
        $_total = $this->recordCount('SELECT COUNT(*) TOTAL FROM(SELECT '.$this->str_SqlFields().' FROM '.$this->tablename.' '.$str_filter.') '); //,$_sql_count_where);
        $this->debug( __LINE__.'::SELECT COUNT(*) TOTAL FROM(SELECT '.$this->str_SqlFields().' FROM '.$this->tablename.' '.$str_filter.')  >>> Total:'.$_total);
      }else{
        $_total = $this->recordCount($str_filter); //,$_sql_count_where);
        $this->debug( __LINE__.'::str_filter: '.$str_filter.' >>> Total:'.$_total);
      }
      $this->total = $_total;
      return $_total;
    }

    function select($params=false) {
      
      if($this->output=='group'){
        $this->page=1;
        $this->page_num_items=200;
        $this->markup_cell = $this->markup_group_cell;
        $this->markup_row  = $this->markup_group_row;
      }
      
      $_total = $this->calculateTotal();

      $this->debug( __LINE__.'::'.'total: '.$_total.' / '.' parent_key: '.$this->parent_key.' / '.' parent_value: '.$this->parent_value );

      if($_SESSION['_CACHE'][$this->tablename]['order'])
        $this->orderby = $_SESSION['_CACHE'][$this->tablename]['order'];

      if( $_total > 0 ) {

        $_total_pages = ceil($_total/$this->page_num_items); 
        $_page_start = $this->page_num_items * $this->page - $this->page_num_items; 
       
        //CACHE if($this->cache) {
        //CACHE   $this->debug( '  Cache: Sí' );
        //CACHE   $r = array_slice($_SESSION['_CACHE'][$this->tablename], $_page_start, $this->page_num_items);
        //CACHE   foreach( $r as $val) {
        //CACHE     $this->addRow($val); 
        //CACHE   }
        //CACHE }else{
          $sql = $this->str_select($_page_start);
          $this->debug( __LINE__.'::'.'sql: '.$sql); //,true);
          $s = $this->sql_query($sql);
          

         if($this->driver=='oracle'){
            $ret = array();
            while ($row = $this->sql_fetch($s)) { 
              $ret[] = $row; 
              $this->addRow($row); 
            }
            
        }else{

            foreach($s as $row){
              $this->addRow($row); 
            }
          
         }
        
        if($this->log && $this->page>1){        
          $_subject = 'SELECT PAGE '. $this->page.' FROM '.$this->tablename;
          $_message = $sql;
          $this->log_write(__FUNCTION__,$_subject,$_message);
        }        
        

        //$this->putMessage($_SESSION['username'].'::'.$this->tablename.':'.$this->page.':'.$this->page_num_items);

        //if($_total_pages>1){
         $_link_back=$_link_add=$_link_config=false;                    

      }else{
        //echo '<div class="warning">La tabla está vacía</div>';
      }

      if($this->output=='custom') {
        
        if($params==2) echo '<pre>'.$sql.'</pre>'; 
        return $this->rows;

      }else{

        $this->paginator = new paginator(0, $_total, $this->page_num_items, $this->page, $this->paginator_link); //, true, true, true );  
        $this->paginator->id = 'pager_'.$this->tablename;
        $this->paginator->class        = 'pagination'; //pagination2'; // pagination-centered';
        $this->paginator->markup_link  = '<li><a data-page="%s" data-parent="'.($this->parent_value?$this->parent_value:'').'" title="%s" class="page_link %s">%s</a></li>';
        $this->paginator->markup_label = '<li><a title="%s" class="%s">%s</a></li>';
        $this->paginator->markup       = '<div id="%s" class="%s"><ul>%s</ul></div>';
        $this->paginator->num_links = 2;
        $this->paginator->input_page_num = $this->input_page_num;

      }
    }

    public function tableHeader(){ 
      if($this->output=='group'){
        $html_search = $this->searchForm($this->tablename,$this->parent_value);
  
        //GROUP
        $title = str_replace( '[TITLE]', $this->title/**.$html_search**/, $this->markup_group_title);
        $r     = str_replace( array('[ID]','[CONTENT]'), array($this->tablename,$title), $this->markup_group_begin);
       
      }else if($this->output=='custom'){
        $r = '';
      }else if($this->output=='raw'){
        $r = '<pre class="code">'.sprintf('<b>%s</b>',$this->tablename)."</pre>\n";
      }else{//}  if($this->output=='table') {
        $html = array();

        if($this->tree)   $html[] = '<th style="width:20px;" width="20"></th>';
 
        foreach( $this->cols as $col) {
           $col->classes = array();
           $col->styles = array();
           if($this->events && method_exists($this->events, 'OnDrawColTitle')){ $this->events->OnDrawColTitle($this,$col); } 
           if(!$col->hide){
            if ($col->sortable && !$col->calculated)     $col->classes[] ='order' ;
            //if ($col->type=='date') $col->classes[] ='cell-date';
            //if ($col->type=='time') $col->classes[] ='cell-time' ;
            if ($_SESSION['_CACHE'][$this->tablename]['order'] ==  $col->fieldname) $col->classes[]='asc';
            if ($_SESSION['_CACHE'][$this->tablename]['order'] ==  $col->fieldname.' ASC') $col->classes[]='asc';
            if ($_SESSION['_CACHE'][$this->tablename]['order'] ==  $col->fieldname.' DESC') $col->classes[]='desc';
            if ($col->width) $col->styles[] = 'width:'.$col->width.'px';
 
            if ($this->markup_header_cell){
              $a = array('[ID]'      => 'column_'.$col->fieldname, 
                         '[WIDTH]'   => ($col->width) ? $col->width : '100',
                         '[HINT]'    => $col->hint,
                         '[CLASS]'   => implode(' ',$col->classes),
                         '[STYLE]'   => implode(';',$col->styles),
                         '[CONTENT]' => $col->label);
              $html[] = str_replace( array_keys($a), array_values($a), $this->markup_header_cell);
            }
          }
        }
        $ncols = count($this->cols);
        $ncols++;
        if(strlen(trim($this->filter))>0){
          $this->showtitle = true;
          //if(!$_SESSION['_CACHE'][$this->tablename]['strfilter']) $_SESSION['_CACHE'][$this->tablename]['strfilter'] = $this->filter;
            $strf  = $this->searchstring 
                   ? '<span style="color:#1c74a4;font-weight:300;"><i class="fa fa-search"></i> Búsqueda:</span> '.$this->searchstring 
                   : ($this->filter       ? '<span style="color:#1c74a4;font-weight:300;margin-left:20px;"><i class="fa fa-filter"></i> Filtro:</span><span title="'.$this->filter.'"> '.(Str::limit_text($this->filter,40)).'</span>' : ''); 
            //$this->title .= '<span class="filter_info"  style="font-size:0.9em;color:#1c74a4;" title="Filtro: '.$this->filter.'"><i class="fa fa-filter"></i>&nbsp;&nbsp;&nbsp;&nbsp; Filtro: '.Str::limit_text($this->filter,140).' </span> ';
            $this->title .= '<span  class="filter_info" style="font-size:0.8em;color:red;margin-right:10px;font-weight:500;">'.$strf.'</span>';
          }
        $r = '';

        if ($this->showtitle){
          $a = array('[COLS]'  => $ncols, 
                     '[TITLE]' => $this->title);
          $r .= str_replace( array_keys($a), array_values($a), $this->markup_header_title);
        }
   
        if($this->markup_header_row){
          $a = array('[ID]'      => 'id', 
                     '[CLASS]'   => 'class',
                     '[CONTENT]' =>  implode('',$html) );
          $r .=  str_replace( array_keys($a), array_values($a), $this->markup_header_row);
        }

      }

      return $r;
    }    

    public function tableFooter(){ 
      $html_search = $this->searchForm($this->tablename,$this->parent_value);
      $html_after='';
     
      $this->paginator->link_add    = ($this->perms['add']);   
      $this->paginator->link_view   = ($this->perms['show']  || ($this->perms['view']&&$this->format_item&&!$this->perms['pdf'])); 
      $this->paginator->link_print  = ($this->perms['print']);
      //$this->paginator->link_pdf    = ($this->perms['pdf']);
      $this->paginator->link_pdf    = $this->paginator->link_pdf===false?false:$this->perms['pdf'];
      $this->paginator->link_word   = ($this->perms['word']);   //ADD 20131009
      $this->paginator->link_excel  = ($this->perms['excel']);  //ADD 20131009
      $this->paginator->link_csv    = ($this->perms['csv']);    //ADD 20210628
      $this->paginator->link_reload = ($this->paginator->link_reload||$this->perms['setup']||$this->perms['reload']);
      $this->paginator->link_setup  = ($this->perms['setup']);
      $this->paginator->link_filter = (!$this->parent_key&&$this->perms['filter']&&$this->paginator->link_filter);
      $this->paginator->link_gallery_mode = $this->link_gallery_mode;
      $this->paginator->link_upload_files = $this->perms['add'] && $this->link_upload_files;
      $this->paginator->link_cfg = $this->link_cfg && ($this->perms['add']||$this->perms['edit']||$this->perms['setup']);
      $this->paginator->parent_value = $this->parent_value;
      $this->paginator->labels['gallery']   = '<span> <i class="fa '.($this->gallery_mode?'fa-list':'fa-th').' fa-inverse"></i> </span>';

      if($this->output=='group') {
        return str_replace( '[CONTENT]',$html_search.$this->paginator, $this->markup_group_end) ;  //GROUP
      }else  if($this->output=='custom'){
        return '';
      }else  if($this->output=='raw'){
        return sprintf('<p>%s</p>',$html_after)."\n";
      }else{ //} if($this->output=='table'){ 
        $ncols = count($this->cols);
        $ncols++;
        if($this->tree) $ncols=$ncols+1;
        $a = array('[COLS]'      => $ncols, 
                   '[CONTENT]'   => $this->paginator.$html_search);
        return str_replace( array_keys($a), array_values($a), $this->markup_footer_row) ;
      }
    }    
    
    // if $this->tree
    private function makerows(&$rows,$id){
      if (++$this->recurselevel > 350) {throw new Exception('***Too many levels: '.$this->recurselevel);}
      $this->level++;
 
      foreach( $this->rows as $k=>$v) {
        
        $childs = 0;
        if($v['parent']==$id){

          foreach( $this->rows as $k2=>$v2) { 
            if($v2['parent']==$k) $childs++;  
          }

          foreach( $this->cols as $col) { } 
          $levelclass = 'level level-'.$this->level;
          if($id>0) $levelclass .= ' row-hidden';
          $levelclass .= ' parent-'.$id;
          if($childs>0) $expand  = '<span class="link-more"><i class="fa fa-plus-square-o" ></i></span>';
                   else $expand  = '';
          //$expand  = '<span class="link-more"><i class="'.( ($this->level==1) ? 'icon-expand-alt' : 'icon-collapse-alt').'"></i></span>';
          //FIX inline javscript for onclick expand .($levelclass) nexts childs rows !!
          $markup = str_replace( array('[LEVEL]','[EXPAND]'),
                                 array($levelclass,$expand),
                                 $v['markup']) ;
          $rows .= $markup;
          
          if($childs>0)  $this->makerows($rows,$k);
        }

      }
      $this->level--;
    }
    
    public function show(){

      $this->rows=array();

      if($this->driver != 'sqlite' && $this->driver != 'oracle' && $this->autocreate) { 
       $sql_describe = $this->driver=='mssql' 
                     ? "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME ={$this->tablename} ORDER BY ORDINAL_POSITION" 
                     : "DESCRIBE {$this->tablename}";
        $exists = $this->sql_query($sql_describe);  
        if (!$exists)   $this->check_table();
      }

      if($this->events && method_exists($this->events, 'OnBeforeShow')){ $this->events->OnBeforeShow($this); }

      $rows = '';
      
      if ($this->perms['view']) {

      }else if ($this->perms['view']==false) {
        
        if( !$_SESSION['userid']){
          ?><div class="warning">La sesión ha finalizado.</div>
          <script type="text/javascript">
            $.modalform({'html':'<h4>La sesión ha finalizado? </h4><br/ >La sesión ha finalizado. debe identificarse para continuar', 'buttons':'ok cancel'}, function(accept) {
              if(accept) document.location.href='/login'; else document.location.href='/';
            });
          </script><?php 
          return false;
        }else{
          ?><div class="warning">No dispone de permisos para ver este elemento</div><?php 
          return false;
        }

      }
      /*******/
      if($this->detail_tables){
        $this->js->addLine("window['X_{$this->tablename}']['detail_tables'] = new Array();");
        $n = 0;
        foreach($this->detail_tables as $detail_table){
          $this->js->addLine("window['X_{$this->tablename}']['detail_tables'].push('{$detail_table}');");
        }
      }else $this->js->addLine("window['X_{$this->tablename}']['detail_tables'] = false;");
      //if($this->detail_field) $this->js->addLine("window['X_{$this->tablename}']['detail_field'] = '{$this->detail_field}';");
      if($this->title)        $this->js->addLine("window['X_{$this->tablename}']['title'] = '{$this->title}';");
      if($this->page)         $this->js->addLine("window['X_{$this->tablename}']['page'] = {$this->page};");
                 else         $this->js->addLine("window['X_{$this->tablename}']['page'] = 1;");
      $this->js->addLine("window['X_{$this->tablename}']['output'] = '{$this->output}';");
      if($this->parent_value)     $this->js->addLine("window['X_{$this->tablename}']['parent'] = '{$this->parent_value}';");
      if($this->perms['detail'])  $this->js->addLine("window['X_{$this->tablename}']['detail'] = true;");
      /**/
      $this->select();
      
      if (count($this->rows)<1){
        ?><div class="div_empty"><p>No hay resultados</p></div><?php 
      }else{
      }
 
      if($this->output=='group'){
      
      }else if($this->output=='custom'){
     
      }else{
        
        if($this->tree){
           $this->makerows($rows,0);
        }else{
          foreach( $this->rows as $row) {
            foreach( $this->cols as $col) {  }
            $rows .= $row;
          }
        }

      }
      
      if($this->show_empty_rows && $this->output!=='group'){
        $empty_rows = $this->page_num_items  - count($this->rows);
        $ncols = count($this->cols);
        //$colspan = $ncols; //count($this->cols) + 1;
        if ( $empty_rows ) {
          for($i=0; $i<$empty_rows; $i++) {
            $html_empty_cells = '';
            foreach( $this->cols as $col) {
              if(!$col->hide) $html_empty_cells .= $this->markup_cell_empty; 
            }
            //$rows .= sprintf($this->markup_row_empty,$html_empty_cells);
            $a = array('[CONTENT]'   => $html_empty_cells);
            $rows .=  str_replace( array_keys($a), array_values($a), $this->markup_row_empty) ;
          }
        }
      }

      echo $this->js;
      //$this->debug($this->searchstring,true);
      /*
      if (count($this->rows)<1){
        echo $empty_rows;
        echo '('.$this->markup_row_empty.')';
        print_r($rows);      
      }
      */
      
      if($this->events && method_exists($this->events, 'OnShow')){ $this->events->OnShow($this); }
      
      if  ($this->output=='group'){
        //$this->debug('Filas: '.count($this->rows),true);
        $sortable_class = ($this->perms['edit']) ? ' edit' : '';
        echo $this->tableHeader();
        if (count($this->rows)>0){
          //        print_r($this->rows);
          ////$this->debug($this->field_group);
          if (!is_array($this->field_group->values_visibles)) $this->field_group->values_visibles = $this->field_group->values;
          foreach ($this->field_group->values_visibles as $group_key=>$group_val){
            //if($this->rows[$group_key]){
             
              //GROUP
              $a = array( '[GROUP]'   => $group_key, 
                          '[CONTENT]' => $group_val,//.' datatable-rows',
                          '[SECTION]' => $sortable_class );
              echo str_replace( array_keys($a), array_values($a), $this->markup_group_group_begin) ;
              //echo sprintf($this->markup_group_group_begin,$group_key,$group_val,$sortable_class);
              
              if($this->rows[$group_key]){
              foreach ($this->rows[$group_key] as $row) {
                if($row['row'][$this->field_group->fieldname]==$group_key){
                  echo ''.$row['text'].'';
                }
              }
              } 
              if($this->perms['add']) echo $this->markup_group_group_end_link; else echo $this->markup_group_group_end; 
            //}
          }
        }else{
          ?><div><p>No hay resultados...</p></div><?php 
          for($i=0; $i<10; $i++) {  
            echo '<br />';
          }
        }
        ?><script type="text/javascript">$(document).ready(function(){$('body').on('click','dt',function(e){var dd=$(this).next();if(!dd.is(":animated")){dd.slideToggle();$(this).toggleClass("opened");}});});</script><?php 
        echo $this->tableFooter();
      } else if($this->output=='custom'){ // ||  ($this->output=='group'){

      } else if($this->output=='raw'){ // ||  ($this->output=='group'){
        echo $this->tableHeader();
        $this->debug($this->rows);
        echo $this->tableFooter();
      } else { //   if($this->output=='table'){
        $colgroup ='';
        //foreach( $this->cols as $col) { $colgroup .= '<colgroup class="column_'.$col->fieldname.'"></colgroup>'; }
        $_markup_table = array( '[ID]'       => $this->tablename, 
                                '[CLASS]'    => $this->classname.($this->filter?' filtered':''),//.' datatable-rows',
                                '[COLGROUP]' => $colgroup,
                                '[HEADER]'   => $this->tableHeader(),
                                '[BODY]'     => $rows,
                                '[PK]'       => $this->pk->fieldname,
                                '[FOOTER]'   => $this->tableFooter() );
        echo str_replace( array_keys($_markup_table), array_values($_markup_table), $this->markup) ;
      }

      if($this->events && method_exists($this->events, 'OnAfterShow')){ $this->events->OnAfterShow($this); }

      //if($this->detail_field) echo '<div id="detail_'.$this->tablename.'" class="detail">...</div>';
      // echo '<div id="detail_'.$this->tablename.'" class="detail">'.print_r($this->rows,true).'</div>';


    }

    public function __toString(){
      return $this->tablename = $tablename;
    }

    public function debug($x,$force=false) { 
      if($this->verbose||$this->output=='raw'||$force) {
        if(is_array($x)) {
          echo '<pre class="code">';
          print_r($x);
          echo '</pre>';
        }elseif($this->verbose||$force){
          echo '<pre class="prettyprint linenums" style="font-size:0.8em;margin:2px 10px;">'.$x.'</pre>'; 
        }
      }     
    }

    public function message($msg,$type='alert') { 
      echo '<div class="'.$type.'">'.$msg.'</div>';
    }
    
    public static function init(){
        if (self::$initialized) {
          ?><script type="text/javascript">if(console_log) console.warn('class Table was initialized');</script><?php
          return false;
        }
        self::$initialized =true;
        Table::$module_name = MODULE; //$_ARGS[0];
        ?>
        <script type="text/javascript">
            if(console_log) console.log('class Table initialized !!!!! :)');
            if(console_log) console.log('WYSIWYG_EDITOR', '<?=WYSIWYG_EDITOR?>')
            var AJAX_URL  = '<?=SCRIPT_DIR?>';   // AJAX_URL
            var CLASS_URL = '<?=SCRIPT_DIR_CLASSES?>/scaffold';
            <?php if(WYSIWYG_EDITOR!==false){ ?>
            var wysiwyg_editor = '<?=WYSIWYG_EDITOR?>';
            <?php }else{ ?>
            var wysiwyg_editor = false;
            <?php } ?>
        </script>
        <?php  if(WYSIWYG_EDITOR) include(SCRIPT_DIR_CLASSES.'/scaffold/editor/'.WYSIWYG_EDITOR.'/editor_init.php');  ?>
        <?php 
        return true;
    }    

    public static function show_table($tablename,$modulename=false,$element=true,$page_number=1,$sortable=false){ //,$active=false) {
        global $selected_table,$modulename_set;
        $_SESSION['tblang']=$_SESSION['lang'];  // Bypass for dont reset SESSION lang !!!!!!!!!
        $markup_ajax_loader = '<p style="text-align:center;border:1px solid green;"><img style="width:56px;" src="'.IMG_AJAX_LOADER.'"></p>';
        if($element) echo '<div class="datatable" id="T-'.$tablename.'">'.$markup_ajax_loader.'</div>';
        
        if($sortable&&!self::$sortable_script_loaded){
            self::$sortable_script_loaded=true;
            ?>
            <!--
            <script type="text/javascript" src="<?=SCRIPT_DIR_JS?>/jquery/jquery-ui.min.js"></script>
            <script type="text/javascript" src="<?=SCRIPT_DIR_JS?>/jquery/sortable.js?ver=1.0.0"></script>
            -->

            <?php 
        }
        ?>
        <script type="text/javascript">
            <?php
            if(!$modulename_set){
                $modulename_set=true;
                ?>
                var module_name = '<?=(strlen($modulename)>2)?$modulename:MODULE?>';
                <?php
            }
            ?>

            window['idTimeOut_<?=$tablename?>'] = false;
            window['pageNumber_<?=$tablename?>'] = '<?=$page_number?>';
            // console.log('PAGE_NUMBER', window['pageNumber_<?=$tablename?>']);
            //if(!default_selected_table) default_selected_table = '<?=$tablename?>';
            <?php /*if($tablename==$selected_table){ echo 'setTimeout(function(){$(\'#'.$tablename.'\').addClass(\'active\').find(\'td\').first().click().focus();},1500);'; }*/?>
            <?php if($sortable){?>
                window['sortable_<?=$tablename?>'] = true;
            <?php }?>
        </script>
        <?php
    }

    public static function show_tabs( $tablename, $detail_tables){

        if ($tablename!='') Table::show_table($tablename);

        $tab_events = new Tabs('tabs_'.$tablename);   
        
        foreach ($detail_tables as $k => $v){     
            $tab_events->addTab($v,'tab_'.$k);
        }

        $tab_events->begin();
            
            foreach ($detail_tables as $k => $v){     
                $tab_events->beginTab('tab_'.$k);
                    Table::show_table($k); 
                $tab_events->endTab();
            }        

        $tab_events->end();

    }

    public function vname(&$var, $scope=false, $prefix='unique', $suffix='value'){
      $vals = ($scope) ? $scope : $GLOBALS;
      $old = $var;
      $var = $new = $prefix.rand().$suffix;
      $vname = FALSE;
      foreach($vals as $key => $val) {
        if($val === $new) $vname = $key;
      }
      $var = $old;
      return $vname;
    }

    public function print_var($varname,$name = ''){
      /*
      if(CFG::$vars['production']==false){
          if(!file_exists( SCRIPT_DIR_MODULE.'/log' )) mkdir(SCRIPT_DIR_MODULE.'/log');
          $contenido = print_r($varname,true);
          $fichero = ($name) ? $name.'_'.time().'.txt' : vname($varname,get_defined_vars()).'.txt';
          $fichero = SCRIPT_DIR_MODULE.'/log/var_'.$fichero;
          if($fp = fopen($fichero,'w+')){
            fwrite($fp,stripslashes($contenido));
            fclose($fp);
          }
      }
      */
    }    

}
