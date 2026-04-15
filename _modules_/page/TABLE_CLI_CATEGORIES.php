<?php 


$tabla = new TableMySql('CLI_CATEGORIES');

/****
define('TB_PREFIX','CLI');
define('TB_ITEM'  ,'CLI_PRODUCTS');
define('TB_ITEMS_TAGS','CLI_PRODUTCS_TAGS');
define('TB_TAGS'      ,'CLI_TAGS');
define('TB_CATEGORIES','CLI_CATEGORIES');
****/

//$tabla->uploaddir['FILE_NAME']     = './media/'.MODULE_SHOP.'/categories/small';
//$tabla->uploaddir['FILE_NAME_BIG'] = './media/'.MODULE_SHOP.'/categories/big';

// $filename->uploaddir = './media/'.MODULE_SHOP.'/categories/small';


//$tabla->perms['edit']   = (Administrador() ); 
$tabla->perms =$_SESSION['page_files_perms'];

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_CATEGORIES.php');

if(isset($_SESSION['PAGE_FILES_ID_PARENT'])){
    //$tabla->parent = true;
    $tabla->setParent('ID_PARENT',$_SESSION['PAGE_FILES_ID_PARENT']);
}
$tabla->showtitle=true;
$tabla->show_inputsearch = false;

/*
$tabla->page_num_items=5;
$tabla->show_empty_rows = false;
$tabla->colByName('HEADER_FILE_NAME')->hide = true;
$tabla->colByName('FILE_NAME')->hide = true;
*/
