<?php

$tabla = new TableMysql( 'CFG_AREAS_APPS_GROUPS_PERMS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->width     = 15;
//$id->hide      = true;

$area_app            = new Field();
$area_app->type      = 'int';
//$area_app->type      = 'hidden';
$area_app->len       = 5;
$area_app->fieldname = 'ID_AREA_APP_GROUP';
$area_app->label     = 'AreaApp'; //'Área Aplicacion';   
$area_app->value     = $parent;
$area_app->width     = 15;

$perm            = new Field();
$perm->type      = 'select';
$perm->len       = 5;
$perm->width     = 15;
$perm->fieldname = 'ID_AREA_APP_PERM';  //'ID_AREA_APP_PERM', //
$perm->label     = 'Permiso';   
$perm->values   =  $tabla->toarray('area_apps_groups_perms', 'SELECT AAP.ID, AP.NAME 
                 FROM CFG_AREAS_APPS_PERMS AAP, 
                      CFG_APPS_PERMS AP 
                WHERE AAP.ID_APP_PERM = AP.ID
                  AND AAP.ID_AREA_APP IN (SELECT ID_AREA_APP FROM CFG_AREAS_APPS_GROUPS WHERE ID ='.$parent.' )' );
$perm->values_all=$perm->values;

$perm->editable  = true;
$perm->width     = 140;
//$id->hide      = true;

$tabla->title =  'Áreas/Apps/Groups/Perms ';
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($area_app);

$tabla->addCol($perm);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['add']    = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->classname = 'column right';

$tabla->setParent('ID_AREA_APP_GROUP',$parent);   // Set as detail

class AREAS_APPS_GROUPS_PERMS_Events extends defaultTableEvents implements iEvents{ 

    function OnDrawRow($owner,&$row,&$class){  }

    function OnAfterShowForm($owner,&$form,$id){  }

    function OnDrawCell($owner,&$row,&$col,&$cell){   }

    function OnInsert($owner,&$result,&$post) {   }
    
    function OnUpdate($owner,&$result,&$post) {   }

}

$tabla->events = New AREAS_APPS_GROUPS_PERMS_Events();
