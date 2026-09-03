<?php

$tabla = new TableMysql('MKP_MARKETPLACE_FILES');

$tabla->uploaddir = 'media/MKP/files';
$tabla->fk = 'ITEM_ID';
$tabla->module='marketplace';
$tabla->show_empty_rows=true;
$tabla->page_num_items = 5;
$tabla->order=true;
$tabla->link_gallery_mode=false;

$tabla->main=true;
$tabla->mini=true;
$tabla->accepted_doc_extensions = ['.jpg','.png','.gif','.webp','.zip','.pdf'];

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

$tabla->colByName('ID_PROVIDER')->values = array('1'=>'Image', '2'=>'Youtube', '3'=>'DMotion', '4'=>'Document', '5'=>'PDF', '10'=>'Epub','11'=>'URL');

$tabla->setParent('ITEM_ID',$parent);

$tabla->colByName('ID_PROVIDER')->hide = !Administrador();

$tabla->perms['view']   = Usuario(); 
$tabla->perms['reload'] = Usuario();  
$tabla->perms['filter'] = Usuario();  
$tabla->perms['edit']   = Administrador() || $_ACL->hasPermission('mkp_edit');
$tabla->perms['add']    = Administrador() || $_ACL->hasPermission('mkp_add');
$tabla->perms['delete'] = Administrador() || $_ACL->hasPermission('mkp_delete');
$tabla->perms['setup']  = Root() || $_ACL->userHasRoleName('Administradores');  
