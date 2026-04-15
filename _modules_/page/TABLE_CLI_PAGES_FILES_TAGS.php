<?php 
/* Auto created */

$tabla = new TableMysql('CLI_PAGES_FILES_TAGS');
$tabla->page_num_items = 30;

/*
$tabla->perms['view']   = ( $editable || $_ACL->hasPermission('files_view')   || Administrador() );
$tabla->perms['delete'] = ( $editable || $_ACL->hasPermission('files_delete') || Administrador() );
$tabla->perms['edit']   = ( $editable || $_ACL->hasPermission('files_edit')   || Administrador() );
$tabla->perms['add']    = ( $editable || $_ACL->hasPermission('files_create') || Administrador() );
$tabla->perms['reload'] = ( $editable || $_ACL->hasPermission('files_reload') || Administrador() );  
$tabla->perms['setup']  = Root();  
$tabla->perms['filter'] = ( $editable || $_ACL->hasPermission('files_filter') || Administrador() );
*/
/*
$tabla->perms['edit']   = true; // $editable || Administrador(); 
$tabla->perms['view']   = true; //$editable || Administrador(); 
$tabla->perms['delete']   = true; //$editable || Administrador(); 
$tabla->perms['add']   = true; //$editable || Administrador(); 
$tabla->perms['reload']   = true; //$editable || Administrador(); 
*/
$tabla->perms =$_SESSION['page_files_perms'];




include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES_TAGS.php');

$tabla->showtitle=true;
$tabla->show_inputsearch = false;
