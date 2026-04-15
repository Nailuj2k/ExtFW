<?php 


$tabla = new TableMySql('DOC_CATEGORIES');

/****
define('TB_PREFIX','CLI');
define('TB_ITEM'  ,'CLI_PRODUCTS');
define('TB_ITEMS_TAGS','CLI_PRODUTCS_TAGS');
define('TB_TAGS'      ,'CLI_TAGS');
define('TB_CATEGORIES','CLI_CATEGORIES');
****/

//$tabla->uploaddir['FILE_NAME']     = './media/plan_contingencia/categories/small';
//$tabla->uploaddir['FILE_NAME_BIG'] = './media/plan_contingencia/categories/big';

// $filename->uploaddir = './media/'.MODULE_SHOP.'/categories/small';



include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_CATEGORIES.php');

/*
$tabla->page_num_items=5;
$tabla->show_empty_rows = false;
$tabla->colByName('HEADER_FILE_NAME')->hide = true;
$tabla->colByName('FILE_NAME')->hide = true;
*/


