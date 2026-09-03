<?php 
// Called ONLY from /_classes_/scaffold/js/plugin_upload.js


$module_name   = $_REQUEST['module'];
$table_name    = $_REQUEST['table'];
$_TB_FILE_ = SCRIPT_DIR_MODULE.($_TABLES_DIR_ ?? '').'/TABLE_'.$table_name.'.php';

$page   = ($_REQUEST['page'])   ? $_REQUEST['page']   : 1 ;
$parent = ($_REQUEST['parent']) ? $_REQUEST['parent'] : 0 ;

define('AJAX_REQUEST'       ,strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
define('DOMAIN'             ,(strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));
define('PATH'               ,parse_url(getenv('REQUEST_URI'), PHP_URL_PATH));


//sleep(1);  // pausa de 1 sg. pa que se vea el gif animao!
Table::$module_name = $module_name;


// RewriteRule ^download/([a-z]+)/([0-9]+)/([a-z_0-9\.]+)$ _modulos_/_common3_/mod_download.php?prefix=$1&id=$2&doc=$3 [nc,l]

//echo '<pre>';
//print_r($_REQUEST);


$field_name    = $_REQUEST['field'];


/*
echo '<br /><br />'.md5_decrypt(base64_url_decode($_REQUEST['url']),'fghmfgmg'); 
echo '<br />'.$module_name;
echo '<br />'.$table_name;
echo '<br />'.$fieldname;
echo '<br />'.$row_id;
echo '<br />'.$parent_id;
*/
//include(SCRIPT_DIR_INCLUDES.'/cms_init_acl.php' );
$parent = $_REQUEST['parent'];
include($_TB_FILE_);

//$_image_col = $tabla->colByName($field_name);
//if ($_image_col->prefix_filename)  $cfg_upload['prefix_filename'] = $tabla->nextInsertId;


//$post = $_REQUEST;
//$post['id_user'] = $_REQUEST['parent'];
//$post['name'] = 'File '.$_FILES[$field_name]['name'];
//$post['file_name'] = $_FILES[$field_name]['name'];

//print_r($post);
//$col = $table->colByName($field_name);

//echo 

$_POST['id_user'] = $_REQUEST['parent'];
$_POST['name'] = 'File '.$_FILES[$field_name]['name'];
//$_POST['if_exists'] = $_REQUEST['if_exists'];
$_POST['parent'] = $_REQUEST['parent'];
$_POST['module'] = $_REQUEST['module'];
$_POST['doc_date'] = date(DATE_FORMAT); //$tabla->sql_currentdate();
//$_POST['id_gallery'] = $_REQUEST['parent'];
if($tabla->parent_key)$_POST[$tabla->parent_key] = $_REQUEST['parent'];

$tabla->insert($_POST);


//$col = $table->colByName($field_name);

/***

$result = array();
  
$result['prefix_filename'] = $_REQUEST['tnprefix'];
$result['parent']     = $_REQUEST['parent'];
$result['if_exists']  = $_REQUEST['if_exists'];
$result['error'] = 0;
$result['msg']  = 'no error';

if(array_key_exists('pic',$_FILES) && $_FILES['pic']['error'] == 0 ){

  
  $table->savefile($col,$_FILES['pic'],$result);

}

  _log(__LINE__);

echo json_encode($result);     

//if($result['error']==0){
 
//}
