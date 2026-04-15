<?php 
/* Auto created */

$tabla = new TableMysql(TB_PREFIX.'_'.TB_NAME.'_FILES');

$tabla->uploaddir = 'media/'.TB_NAME.'/files';
$tabla->fk = TB_NAME.'_ID';
$tabla->translatable = true;
$tabla->accepted_doc_extensions = array('.jpg','.png','.gif','.webp','.mp3','.zip','.pdf','.epub','.txt','.doc','.docx'); //FIX in include
$tabla->page_num_items = 10;
//$tabla->link_gallery_mode=true;
//$tabla->epub=true;
//$tabla->show_empty_rows=false;
$tabla->mini=true;
$tabla->main=true;

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

//if(isset($_SESSION[TB_NAME.'_FILES_PARENT'])&&$_SESSION[TB_NAME.'_FILES_PARENT']>0)
//   $tabla->setParent($tabla->fk,$_SESSION[TB_NAME.'_FILES_PARENT']);
//else
    $tabla->setParent($tabla->fk,$parent);
