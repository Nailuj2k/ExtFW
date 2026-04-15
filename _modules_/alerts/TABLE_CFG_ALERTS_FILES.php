<?php 
/* Auto created */

$tabla = new TableMysql('CFG_ALERTS_FILES');

//$tabla->show_empty_rows=false;
$tabla->uploaddir = 'media/CFG_ALERTS_FILES/files';
$tabla->fk = 'ITEM_ID';
$tabla->module='alerts';
$tabla->show_empty_rows=true;
$tabla->page_num_items = 5;
$tabla->order=true;
$tabla->link_gallery_mode=false;

$tabla->main=true;

/*
$tabla->uploaddir = 'media/NEWS/images';
$tabla->fk = 'NEWS_ID';
$tabla->translatable = true;
$tabla->accepted_doc_extensions = array('.jpg','.png','.gif','.webp','.mp3','.zip','.pdf'); //FIX in include
//$tabla->link_gallery_mode=true;
//$tabla->show_empty_rows=false;
*/

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

$tabla->setParent('ITEM_ID',$parent);

$tabla->colByName('ID')->hide = true; //!Administrador();
$tabla->colByName('ID_PROVIDER')->hide = !Administrador();
//$tabla->colByName('MINI')->hide = !Administrador();
//$tabla->colByName('MAIN')->hide = !Administrador();
$tabla->colByName($tabla->fk)->hide = true; //!Administrador();

$tabla->perms['view']   = Usuario(); //Administrador() || $_ACL->hasPermission('alerts_view');      
$tabla->perms['reload'] = Administrador() || $_ACL->hasPermission('alerts_view');  
$tabla->perms['edit']   = Administrador() || $_ACL->hasPermission('alerts_edit');
$tabla->perms['add']    = Administrador() || $_ACL->hasPermission('alerts_add');
$tabla->perms['delete'] = Administrador() || $_ACL->hasPermission('alerts_delete');
$tabla->perms['setup']  = Root() || $_ACL->userHasRoleName('Administradores');  
$tabla->perms['filter']  = true;  //$_ACL->hasPermission('alerts_view');    


