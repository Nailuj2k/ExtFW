<?php

$parent      = $_POST['parent'] ? $_POST['parent'] : ( $_ARGS['parent'] ? $_ARGS['parent'] : 1 );  // users,
$module_name = $_ARGS['module'] ? $_ARGS['module'] : $_ARGS[0] ;  // users,
$option      = $_ARGS['op'];  // show,info,insert,...


//$table_name  = isset($_POST['table']) ? $_POST['table'] : $_ARGS['table'];

$table_name  = $_ARGS['table'] ?? false;

if ($table_name) $_TB_FILE_ = SCRIPT_DIR_MODULE.($_TABLES_DIR_ ?? '').'/TABLE_'.$table_name.'.php';


if(!isset($_SESSION['_CACHE']))$_SESSION['_CACHE']=[];
if($table_name){
    if(!isset($_SESSION['_CACHE'][$table_name]))$_SESSION['_CACHE'][$table_name]=[];
    if(!isset($_SESSION['_CACHE'][$table_name]['order']))$_SESSION['_CACHE'][$table_name]['order']='';
}

$page        = $_ARGS['page'] ?? 1 ;

//sleep(1);  // pausa de 1 sg. pa que se vea el gif animao!

define('INSTALL','ok');
if       (CFG::$vars['db']['type']=='mysql') {
    if($table_name && $option != 'list'){
        if(INSTALL=='ok'){
            if(!file_exists($_TB_FILE_)){
                $option = 'create';
            }
        }
    }
}

// FIX find 'TableMysql' in this file and use if db.type==sqlite
// and delete this ñapa
if       (CFG::$vars['db']['type']=='mysql') {
    //class TableBase extends TableMysql{  }
//}else if (CFG::$vars['db']['type']=='oracle') {
//    class TableMysql extends TableOracle{ }
}else if (CFG::$vars['db']['type']=='sqlite') {
    class TableMysql extends TableSqlite{ }
}

if(!isset($_SESSION['lang']))
    $_SESSION['lang']=$_SESSION['tblang'];    // Bypass for dont reset SESSION lang !!!!!!!!!
    
if ($option=='list'){  // echo select values
   
     ///plan_contingencia/ajax/op=list/plan_contingencia/ajax/op=list/sql=select_uenf/value=NEONATOLOGIA/selected=0/table=CONT_HOSPITALIZADOS

    if ($table_name)  include($_TB_FILE_); /////////////////////////////////////////////    

    $sqls = array();
    $sql = false;
    
    //FIX $sqls array would be edited in module init

  //$sqls['localidad_from_cp']        = 'SELECT localidad_id AS id, CONCAT(localidad_cp,\' \',localidad_name) AS name FROM CFG_LOCALIDAD WHERE localidad_cp=\'[VAL]\'';  
  //$sqls['localidad_from_municipio'] = 'SELECT localidad_id AS id, concat(localidad_cp,\' \',localidad_name) AS name FROM CFG_LOCALIDAD WHERE id_municipio=\'[VAL]\'';  // 4592' // /control_panel/ajax/op=list
    $sqls['localidad_from_cp']        = 'SELECT localidad_id AS id, localidad_name AS name FROM CFG_LOCALIDAD WHERE localidad_cp=\'[VAL]\'';  
    $sqls['localidad_from_municipio'] = 'SELECT localidad_id AS id, localidad_name AS name FROM CFG_LOCALIDAD WHERE id_municipio=\'[VAL]\'';  // 4592' // /control_panel/ajax/op=list
    $sqls['municipio_from_provincia'] = 'SELECT municipio_id AS id, municipio_name AS name FROM CFG_MUNICIPIO WHERE id_provincia=\'[VAL]\'';  //7'// /control_panel/ajax/op=list
    $sqls['municipio_from_cp']        = 'SELECT municipio_id AS id, municipio_name AS name FROM CFG_MUNICIPIO WHERE municipio_id IN (SELECT id_municipio FROM CFG_LOCALIDAD WHERE localidad_cp=\'[VAL]\')';  // 30002') // /login/ajax/op=list
    $sqls['provincia_from_cp']        = 'SELECT provincia_id AS id, provincia_name AS name FROM CFG_PROVINCIA WHERE provincia_id IN (SELECT id_provincia FROM CFG_LOCALIDAD WHERE localidad_cp=\'[VAL]\')'; // '30720') // /login/ajax/op=list
    $sqls['provincia_from_pais']      = 'SELECT provincia_id AS id, provincia_name AS name FROM CFG_PROVINCIA WHERE id_pais=\'[VAL]\''; //196     // /login/ajax/op=list
    $sqls['provincia_in_destino']     = 'SELECT provincia_id AS id, provincia_name AS name FROM CFG_PROVINCIA WHERE id_pais=[VAL] AND NOT FIND_IN_SET( provincia_id,(SELECT CONTENT FROM CLI_DESTINOS WHERE ID_FIELD=2))';
    $sqls['provincia_in_destinos']    = 'SELECT provincia_id AS id, provincia_name AS name FROM CFG_PROVINCIA WHERE id_pais=[VAL] AND NOT FIND_IN_SET( provincia_id,(SELECT GROUP_CONCAT(CONTENT SEPARATOR \',\') FROM CLI_DESTINOS WHERE ID_FIELD=2 AND ACTIVE=\'0\'))';                                      

    if($tabla->sqls) $sqls = array_merge($sqls,$tabla->sqls);  

    if($_ARGS['sql']&&$_ARGS['value']){     

        foreach ($sqls as $k=>$v){
            if($k == $_ARGS['sql']){
                $sql = $v;
                break;
            }
        }

        if(!$sql) {  

            // $sql = $_ARGS['sql'];  // ONLY permitted $sqls keys

            ?><option value="0" SELECTED>NO SQL</option><?php

        }else{

            // echo $sql;
            $_value = $_ARGS['value'];
            //if (Str::is_numeric($_value)){
            if ($_value && preg_match('/^[A-Z_a-z0-9]+$/', $_value)) {   // FIX check is string A-Z0-9

                $sql = str_replace('[VAL]',$_value,$sql);

                //echo $sql;

                if($tabla == null){  //FIX
                    
                    if ($_ARGS['driver']=='oracle') $tabla = new TableOracle(); 
                    if ($_ARGS['driver']=='mssql')  $tabla = new TableMsSql(); 
                    if ($_ARGS['driver']=='sqlite') $tabla = new TableSqlite(); 
                    else                            $tabla = new TableMysql();//false,DB_EXTERNAL);
                }
                
                $query = $tabla->sql_query($sql);  //FIX check count($query) > 0  || if($query)  || is_array()
                
                if(isset($_ARGS['separate'])){    //when keys/values is from stored string comma delimited
                    $fila = $query[0];
                    $items = array_combine(explode(',',$fila['val_values']),explode(',',$fila['val_names']));
                    if(count($items)>0) {
                        $selected = ($_ARGS['selected']) ? $_ARGS['selected']  : false;
                        foreach ($items as $k=>$v){  ?><option value="<?=$k?>"<?php  if($selected==$k) echo ' SELECTED'; ?>><?=$items[$k]?></option><?php  } 
                    }    

                }else{

                    $selected = ($_ARGS['selected']) ? $_ARGS['selected']  : false;
                    if(isset($_ARGS['null'])){ ?><option value="<?=$_ARGS['nullkey']?>"><?=$_ARGS['nullvalue']?></option><?php  }
                    if ($_ARGS['driver']=='oracle')
                        foreach ($query as $fila) { 
                            if ($fila['id'] && preg_match('/^[A-Z_a-z0-9]+$/', $fila['id'])) { 
                                $_caption = $fila['name'] ?? $fila['id'];
                                ?><option value="<?=$fila['ID']?>"<?php  if($selected==$fila['ID']) echo ' SELECTED'; ?>><?=$_caption?></option><?php  
                            }
                        }
                    else
                        foreach ($query as $fila) {
                            if ($fila['id'] && preg_match('/^[A-Z_a-z0-9]+$/', $fila['id'])) { 
                                $_caption = $fila['name'] ?? $fila['id'];
                                ?><option value="<?=$fila['id']?>"<?php  if($selected==$fila['id']) echo ' SELECTED'; ?>><?=$_caption?></option><?php  
                            }
                        }
  
                }

            }else{

                ?><option value="0" SELECTED>SQL ERROR: <?$_ARGS['value']?></option><?php

            }
        }
        
    }else if($_ARGS['fieldname']){

       // include($_TB_FILE_);
        $col=$tabla->colByName($_ARGS['fieldname']);
        $items = $col->values;
        if(count($items)>0) {
            $selected = ($_ARGS['selected']) ? $_ARGS['selected']  : false;
            foreach ($items as $k=>$v){  ?><option value="<?=$k?>"<?php  if($selected==$k) echo ' SELECTED'; ?>><?=$items[$k]?></option><?php  } 
        }

    }

}else if ( $option=='setvar' && $_ARGS['type'] && $_ARGS['key'] /*&& $_ARGS['value']*/){
   // /control_panel/ajax/op=setvar/type=session/key=userid/value=1  

    $result = array();
    $result['error'] = 0;
    if($_ACL->userHasRoleName('Administradores')){
        $value = $_ARGS['value']?$_ARGS['value']:false;
        if     ($_ARGS['type']=='session')  $_SESSION[$_ARGS['key']]=$_ARGS['value'];
        else if($_ARGS['type']=='cookie')   $_COOKIE[$_ARGS['key']]=$_ARGS['value'];
        $result['msg'] = $_ARGS['key'].' set to '.$_ARGS['value'];
    }else{
        $result['msg'] = __LINE__.' Access denied';
    }
    //header('Content-Type: application/json');
    echo json_encode($result);
    //Vars::debug_var($_COOKIE);

}else if ($option=='getvar' && $_ARGS['type'] && $_ARGS['key'] ){
    // /control_panel/ajax/op=getvar/type=session/key=userid

    $result = array();
    $result['error'] = 0;
    if($_ACL->userHasRoleName('Administradores')){
        if     ($_ARGS['type']=='session')  $result['value'] = $_SESSION[$_ARGS['key']];  
        else if($_ARGS['type']=='cookie')   $result['value'] = $_COOKIE[$_ARGS['key']];
    }else{
        $result['msg'] = __LINE__.' Access denied';
    }
    //header('Content-Type: application/json');
    echo json_encode($result);
  //Vars::debug_var($_COOKIE);

}else if ($option=='clear_cache'){
  
    unset($_SESSION['_CACHE']);

}else if ($option=='oracle_test'){

  //include(SCRIPT_DIR_MODULE.'/TABLE_AD_MAP_GFH_TFNO.php');
  //$tabla->sql_query("UPDATE AD_MAP_LOGIN_TFNO SET TELEFONO = '666666' WHERE ID=1");  //972804

}else if ($option=='show' && $table_name){
   // /control_panel/ajax/op=show/table=CLI_ORDERS/

    // error_reporting(E_ALL);
    //ini_set('display_errors', 1);
    //ini_set('error_reporting', E_ALL); // ^ E_NOTICE ); // 1 E_ALL);

    //Vars::debug_var($_TB_FILE_);

    //if(file_exists($_TB_FILE_))
        include($_TB_FILE_);
    //else{
    //    echo 'Table definition file not found: '.$_TB_FILE_;
    //    exit;
    //}  
    //echo __LINE__;
    //print_r($tabla);

    if($tabla->soft_delete) $tabla->addDeletedCol();  //SOFT_DELETE
  //$tabla->parent_value = $parent;
    
    //include_table($table_name);  
  
    //function include_table($tablename) {
    //    if($tables[tablename]) include(SCRIPT_DIR_MODULE.'/'.$tables[path].'TABLE_'.$tables[$tablename].'.php');
    //}
    //tables[table_name,path]
    
    $tabla->page = $page;
    $tabla->paginator_link = "%s";
  //if($tabla->parent_key && $tabla->parent_value) $tabla->paginator_link .= ",{$tabla->parent_value}";
  //$tabla->paginator_link .= ")";
    $tabla->searchstring = ($_SESSION['_CACHE'][$table_name]['searchstring'])
                         ? $_SESSION['_CACHE'][$table_name]['searchstring'] 
                         : false;
    $tabla->show();
 
}else if ($option=='search' && $table_name){
  
    $result = array();
    if($_ARGS['searchstring']){  //$searchstring = $_ARGS['searchstring'];
        $_SESSION['_CACHE'][$table_name]['searchstring'] = $_ARGS['searchstring'];
        $result['msg']='Búsqueda: '.$_SESSION['_CACHE'][$table_name]['searchstring'];
    }else{
        $_SESSION['_CACHE'][$table_name]['searchstring'] = false;
        $result['msg']='Búsqueda desactivada';
    }
    $result['error']=0;
    echo json_encode($result);   

}else if ($option=='order' && $table_name && $_POST['col']){
  
    $result = array();
    if($_ARGS['col']){
        $oldOrder = $_SESSION['_CACHE'][$table_name]['order'];
        $newOrder = $_ARGS['col'];
        $asc='asc';
        if      ($oldOrder == $newOrder.' ASC')  {$newOrder = $newOrder.' DESC';$asc='desc';}
        else if ($oldOrder == $newOrder.' DESC') {$newOrder = $newOrder.' ASC';}
        else                                     {$newOrder = $newOrder.' ASC';}
        $_SESSION['_CACHE'][$table_name]['order'] = $newOrder;
        //$result['msg']='Ordenado por: '.$_SESSION['_CACHE'][$table_name]['order'];
        $result['col'] = $_ARGS['col'];
        $result['asc'] = $asc;
    }
    $result['error']=0;
    //header('Content-Type: application/json');
    echo json_encode($result);   

}else if ($option=='reload' && $table_name){ // && $_ARGS['id']){

    $_SESSION['_CACHE'][$table_name] = false;
    $result = array();
    $result['error']=0;
    $result['msg']='Reloading ...';
    //header('Content-Type: application/json');
    echo json_encode($result);   
  
}else if ($option=='gallery' && $table_name){ // && $_ARGS['id']){

    $_SESSION['_CACHE'][$table_name]['gallery_mode'] = !$_SESSION['_CACHE'][$table_name]['gallery_mode']; 
    $result = array();
    $result['error']=0;
    $result['msg']='Reloading ...';
    //header('Content-Type: application/json');
    echo json_encode($result);   
  
}else if ($option=='create' && $table_name){
  
    if (defined("CLASS_TABLE_MSSQL_LOADED"))       $tabla = new TableMsSql($table_name);
    else if (defined("CLASS_TABLE_ORACLE_LOADED")) $tabla = new TableOracle($table_name);
    else                                           $tabla = new TableMysql($table_name);
    include(SCRIPT_DIR_MODULE.'/CREATE.php');
    $tabla->create($_TB_FILE_);
  
}else if ($option=='setup' && $table_name){ // && $_ARGS['id']){

    include($_TB_FILE_);
    //if($tabla->soft_delete) $tabla->addDeletedCol();  //SOFT_DELETE
    $tabla->check_table();

}else if ($option=='filter' && $table_name){ // && $_ARGS['id']){

    include($_TB_FILE_);
    $tabla->filter($_POST);

}else if ($option=='describe' && $table_name){ // && $_ARGS['id']){

    include($_TB_FILE_);
    echo '<pre class="prettyprint linenums:4 lang-sql" id="sql_lang" style="font-size:0.8em;">'.$tabla->create_table().'</pre>';

}else if ($option=='rearrange' && $table_name  && $_ARGS['keys'] && $_ARGS['positions'] && $_ARGS['group']){ // && $_ARGS['id']){
  // Vars::debug_var($_ARGS);
    $keys      = ($_ARGS['keys'])      ? $_ARGS['keys']      : false ;
    $positions = ($_ARGS['positions']) ? $_ARGS['positions'] : false ;
    $group     = ($_ARGS['group'])     ? $_ARGS['group']     : false ;
    include($_TB_FILE_);
    $tabla->rearrange($keys,$positions,$group);

}else if ($option=='add' && $table_name){ // && $_ARGS['id']){
  
  //FIX if parent_key
    $parent = ($_ARGS['parent']) ? $_ARGS['parent'] : false ;
    $group  = ($_ARGS['group'])  ? $_ARGS['group'] : false ;
    include($_TB_FILE_);
    $tabla->form_insert($parent,$group);

}else if ($option=='save' && $table_name){
  
    include($_TB_FILE_);
    $tabla->insert($_ARGS);

}else if ($option=='edit' && $table_name && $_ARGS['id']){
  
    $page = max(1,$_ARGS['page']);
    include($_TB_FILE_);
    if($tabla->soft_delete) $tabla->addDeletedCol();  //SOFT_DELETE
    $tabla->form_update($_ARGS['id']);

}else if ($option=='post_file' && $table_name){

    include($_TB_FILE_);
    $field_name    = $_ARGS['field'];
    $parent = $_ARGS['parent'];
    $_POST['id_user'] = $_ARGS['parent'];
    $_POST['name'] = 'File '.$_FILES[$field_name]['name'];
    //$_POST['if_exists'] = $_ARGS['if_exists'];
    $_POST['parent'] = $_ARGS['parent'];
    $_POST['module'] = $_ARGS['module'];
    $_POST['doc_date'] = date(DATE_FORMAT); //$tabla->sql_currentdate();
    //$_POST['id_gallery'] = $_ARGS['parent'];
    if($tabla->parent_key) $_POST[$tabla->parent_key] = $_ARGS['parent'];
    $tabla->insert($_POST);

}else if ($option=='changegroup' && $table_name && $_ARGS['key'] && $_ARGS['newgroup'] ){ //&& $_ARGS['value'] ){  //update_field

    include($_TB_FILE_);
    $key   = ($_ARGS['key']);
    $field = ($tabla->field_group->fieldname);
    $value = ($_ARGS['newgroup']);
    //include($_TB_FILE_);
    if($tabla->perms['edit']){
        $post = array();
        $post[$field] = $value;
        $post[$tabla->pk->fieldname] = $key;
        $tabla->page = 1;
        $tabla->update($post);
    }
}else if ($option=='updatefield' && $table_name && $_ARGS['col'] && $_ARGS['key'] && $_ARGS['field'] ){ //&& $_ARGS['value'] ){  //update_field
    // /control_panel/ajax/op=updatefield/table=CLI_USER/key=11/col=user_id/field=username/value=achosi

    $result = array();
    $result['error']=0;
    include($_TB_FILE_);
    if($tabla->perms['edit']){
        $col   = $tabla->pk->fieldname;
        $key   = ($_ARGS['key']);
        $field = ($_ARGS['field']);
        $value = ($_ARGS['value']);
        $tabla->sql_exec("UPDATE {$table_name} SET {$field} = '{$value}' WHERE {$col}='{$key}'");
        $result['field'] = $tabla->getFieldValue( "SELECT {$field} FROM {$table_name} WHERE {$col}='{$key}'" );
    }else{
        $result['msg'] = __LINE__.' Access denied';
    }
    //header('Content-Type: application/json');
    echo json_encode($result);   

}else if ($option=='getfield' && $table_name && $_ARGS['field'] && $_ARGS['key']){ //} && $_ARGS['value'] ){ //&& $_ARGS['value'] ){  //update_field
    // /control_panel/ajax/op=getfield/table=CLI_USER/field=user_email/key=user_id/value=1
    // /control_panel/ajax/op=getfield/table=CLI_USER/field=user_url_avatar/key=user_id/value=13

    $result = array();
    $result['error'] = 0;
    //if($_ACL->userHasRoleName('Administradores')){
      $fieldname = ($_ARGS['field']);  //FIX validate fieldname
      $fieldname_alt = ($_ARGS['field_alt'] ?? false); // 'content' is default fieldname
      $fieldkeyname   = ($_ARGS['key']);
      $fieldkeyvalue = ($_ARGS['value']);
      //FIX check perms!!!!!!!!!
      if($fieldname&&$fieldkeyname&&$fieldkeyvalue){
        include($_TB_FILE_);
        if($tabla->perms['edit']){
            $tabla->colByName( $fieldkeyname )->value = $fieldkeyvalue;
            $fieldkeyvalue =   $tabla->colByName( $fieldkeyname )->format_value($fieldkeyvalue);
            if ($tabla->colByName( $fieldkeyname )->type=='date') $fieldkeyvalue = "STR_TO_DATE('{$fieldkeyvalue}', '".DATE_FORMAT_MYSQL."')";
            $sql = "SELECT $fieldname FROM $table_name WHERE $fieldkeyname = $fieldkeyvalue";
            //if ($_ARGS['where']) $sql .= ' AND '.$_ARGS['where'];  //FIXED sql inject vulnerablity

            $result['field'] = $tabla->getFieldValue( $sql );
            
            if(trim($result['field']=='') && $fieldname_alt) {
                $sql = "SELECT $fieldname_alt FROM $table_name WHERE $fieldkeyname = $fieldkeyvalue";
                $result['field'] = $tabla->getFieldValue( $sql );
            }
            
            $result['field'] = Str::unescape($result['field']);
            //  echo "SELECT $fieldname FROM $table_name WHERE $fieldkeyname = $fieldkeyvalue";

            if ($_ARGS['shortcodes']=='y')
                $result['field'] = APP::$shortcodes->do_shortcode($result['field']);

            if($result['field']){
              $result['msg']='valor encontrado';
              $result['error']=0;
            }else{
              $result['msg']='valor no encontrado';
              $result['error']=1;
            }
        }else{
            $result['msg'] = __LINE__.' Access denied';
        }
      }else{
        $result['msg']='No se proporcionó un Id';
        $result['error']=2;
      }
    //}else{
    //    $result['msg'] = __LINE__.' Access denied';
    //}
    //header('Content-Type: application/json');
    echo json_encode($result);

}else if ($option=='update' && $table_name){

  $page = max(1,$_ARGS['page']);
  include($_TB_FILE_);
  //  if($tabla->soft_delete) $tabla->addDeletedCol();  //SOFT_DELETE
  $tabla->page = $page;
  $tabla->update($_ARGS);

}else if ($option=='function' && $table_name){

  include($_TB_FILE_);
  if(method_exists($tabla, $_ARGS['function'])){ 
    call_user_func_array(array($tabla, $_ARGS['function']), array($_ARGS));
  }

}else if ($option=='method' && $table_name){

  include($_TB_FILE_);
  if(method_exists($tabla->events, $_ARGS['method'])){ 
    call_user_func_array(array($tabla->events, $_ARGS['method']), array($tabla,$_ARGS));
  }

}else if ($option=='delete' && $table_name && $_ARGS['id']){
  
  include($_TB_FILE_);
  $tabla->delete($_ARGS['id']);
/*
}else if ($option=='get' && $table_name && $_ARGS['id']){

  include($_TB_FILE_);
  $tabla->getDetail($_ARGS['id']);
*/
}else if ($option=='getrow' && $table_name && $_ARGS['id']){

  include($_TB_FILE_);
  //header('Content-Type: application/json');
  echo json_encode($tabla->getRow($_ARGS['id']));

}else if ($option=='print' && $table_name && $_ARGS['id']){

  include($_TB_FILE_);
  $tabla->getPrint($_ARGS['id']);

}else if ($option=='view' && $table_name && $_ARGS['id']){  //////////// $_ARGS['id']

  include($_TB_FILE_);
  $tabla->getPrint($_ARGS['id'],'view');

}else if ($option=='word' && $table_name && $_ARGS['id']){    //ADD 20131009

  include($_TB_FILE_);
  $tabla->getPrint($_ARGS['id'],'word');

}else if ($option=='pdf' && $table_name && $_ARGS['id']){

  include($_TB_FILE_);
  $tabla->getPrint($_ARGS['id'],'pdf');

}else if ($option=='excel' && $table_name && $_ARGS['id']){

  include($_TB_FILE_);
  $tabla->getPrint($_ARGS['id'],'excel');

//}else if ($option=='csv' && $table_name && $_ARGS['id']){

  //  include($_TB_FILE_);
  //  $tabla->getPrint($_ARGS['id'],'csv');

}else if ($option=='cache' ){

  echo '<pre class="prettyprint linenums" style="font-size:0.7em;margin:2px 10px;height:500px;max-height:500px;overflow:auto;">';
  print_r($_SESSION['_CACHE']);
  echo '</pre>';

}else if ($option=='info' ){

    define('AJAX_REQUEST' ,strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
    define('DOMAIN'       ,(strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));
    define('PATH'         ,parse_url(getenv('REQUEST_URI'), PHP_URL_PATH));

    ?>
    <pre class="prettyprint linenums" style="font-size:0.8em;">
    AJAX request: <?=(AJAX_REQUEST) ? 'Sí' : 'No' ?> 
          Domain: <?=DOMAIN?> 
            Path: <?=PATH?>                                                 
    -------------------------------------------------------------------------------------------------
           info:                           -> print info
           list: values,[selected]         -> print select options [selected]
           show: table,[page],[parent]     -> $tabla->show()
            add: table,[parent]            -> $tabla->form_insert(parent);
           edit: table,id,[page]           -> $tabla->form_update(id);
           save: table,$_POST              -> $tabla->insert($_POST);
         update: table,$_ARGS,[page]       -> $tabla->update($_ARGS);
         delete: table,id                  -> $tabla->delete($id)
          setup: table                     -> $tabla->check_table();
    updatefield: table,col,key,field,value -> $tabla->update_field($field,$value,$tabla->cols[$col]->keyName,$key);
    -------------------------------------------------------------------------------------------------
    <?php  /* Vars::debug_var($_ARGS); */ ?>
    </pre>
    <?php 

}else{

    echo __LINE__.' Acceso denegado';
    // Vars::debug_var($option);
    // Vars::debug_var($table_name);
    // Vars::debug_var($_ARGS['id']);
    // Vars::debug_var($_ACL);

}


if($_ARGS['debug']??false){

  ?><pre><?php 
  print_r($_ARGS);
  ?></pre><?php 

}

