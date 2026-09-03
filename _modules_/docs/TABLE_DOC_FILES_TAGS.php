<?php 
/* Auto created */

$tabla = new TableMysql('DOC_FILES_TAGS');
$tabla->page_num_items = 30;


$tabla->perms['view']   = ( $_ACL->hasPermission('files_view')   || Administrador());
$tabla->perms['delete'] = ( $_ACL->hasPermission('files_delete') || Administrador() );
$tabla->perms['edit']   = ( $_ACL->hasPermission('files_edit')   || Administrador() );
$tabla->perms['add']    = ( $_ACL->hasPermission('files_create') || Administrador() );
$tabla->perms['reload'] = ( $_ACL->hasPermission('files_reload') || Administrador() );  
$tabla->perms['setup']  = $_SESSION['userid']==1;  
$tabla->perms['filter'] = ( $_ACL->hasPermission('files_filter') || Administrador() );


include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES_TAGS.php');
