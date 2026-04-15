<?php 

$tabla = new TableMysql('CLI_USER_FILES');
$tabla->uploaddir = 'media/user/files';
$tabla->fk = 'id_user';
$tabla->show_empty_rows=true;
$tabla->table_tags = false; //'RRHH_TAGS';
$tabla->order = false;
$tabla->epub=true;
$tabla->download_count = 'DOWNLOAD_COUNT';
// $tabla->download_count_fieldname = 'DOWNLOAD_COUNT';
$tabla->link_gallery_mode=true;
$tabla->page_num_items=$user_files_page_num_items?$user_files_page_num_items:5;
// $tabla->group=true;
// $tabla->hash_filenames=true;
$tabla->categories=false;
//$tabla->orderby='NAME';
$tabla->langs=[];
//$tabla->accepted_doc_extensions=array('.jpg','.png','.gif','.webp','.mp3','.zip','.pdf','.json','.txt'); 
include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

//if(isset($_SESSION['PAGE_FILES_ID_PARENT'])&&$_SESSION['PAGE_FILES_ID_PARENT']>0)
//    $tabla->setParent($tabla->fk,$_SESSION['PAGE_FILES_ID_PARENT']);
//else
    $tabla->setParent($tabla->fk,$parent);

