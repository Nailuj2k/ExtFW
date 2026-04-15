<?php 

$tabla = new TableMysql('DOC_FILES');
$tabla->download_count = 'DOWNLOAD_COUNT';
$tabla->page_num_items = 15;
//$tabla->show_empty_rows=false;
$tabla->table_tags = 'DOC_TAGS';
$tabla->uploaddir = 'media/files';
$tabla->epub  = true;
$tabla->group = true;
$tabla->order = true;
$tabla->tb_categories = 'DOC_CATEGORIES';

$tabla->fk=false;
$tabla->link_cfg = true;

$tabla->link_gallery_mode=true;
$tabla->qrcodes = true;
$tabla->hash_filenames = true; // cfg setting

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

//$tabla->orderby = 'NAME';

$tabla->perms['view']   = true;
$tabla->perms['show']   = ( Administrador() );
$tabla->perms['reload'] = ( Administrador() );  
$tabla->perms['filter'] = ( Administrador() );
$tabla->perms['add']    = ( Administrador() );
$tabla->perms['edit']   = ( Administrador() );
$tabla->perms['delete'] = ( Administrador() );
$tabla->perms['setup']  = Root();  