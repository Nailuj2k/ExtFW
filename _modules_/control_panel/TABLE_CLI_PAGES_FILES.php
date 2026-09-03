<?php 

$tabla = new TableMysql('CLI_PAGES_FILES');
$tabla->uploaddir = 'media/page/files';
$tabla->fk = 'id_item';
$tabla->show_empty_rows=false;
$tabla->table_tags = false; //'RRHH_TAGS';
$tabla->order = true;
$tabla->epub=true;
$tabla->download_count = 'DOWNLOAD_COUNT';
// $tabla->download_count_fieldname = 'DOWNLOAD_COUNT';
$tabla->link_gallery_mode=true;
$tabla->page_num_items=50;
// $tabla->group=true;
$tabla->hash_filenames=true;
$tabla->categories=true; //(MODULE!=='control_panel');
//$tabla->orderby='NAME';
$tabla->langs=[];

$tabla->watermark = SCRIPT_DIR_MEDIA.'/images/logo.png';

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

//if(isset($_SESSION['PAGE_FILES_ID_PARENT'])&&$_SESSION['PAGE_FILES_ID_PARENT']>0)
//    $tabla->setParent($tabla->fk,$_SESSION['PAGE_FILES_ID_PARENT']);
//else
    $tabla->setParent($tabla->fk,$parent);

