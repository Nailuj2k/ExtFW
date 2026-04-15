<?php

$tabla = new TableMysql( 'CFG_AREAS_APPS_GROUPS' ); 

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->width     = 15;
$id->hide      = true;

$area_app            = new Field();
$area_app->type      = 'int';
$area_app->len       = 5;
$area_app->fieldname = 'ID_AREA_APP';
$area_app->label     = 'AP';//Área Aplicacion';   
$area_app->value     = $parent;
$area_app->width     = 15;
$area_app->hide      = true;

$grp            = new Field();
$grp->type      = 'select';
$grp->len       = 5;
$grp->width     = 200;
$grp->fieldname = 'ID_GROUP';
$grp->label     = 'Grupo';   
$grp->source    = 'areas_apps_groups';   
$grp->values_all= $tabla->toarray('areas_apps_groups_all', 'SELECT ID,NAME,ID_AREA FROM CFG_AREAS_GROUPS',true);
$grp->editable  = true;

$tabla->title =  'Áreas/Apps/Groups';    
$tabla->showtitle = true;

$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;

$tabla->addCol($id);
$tabla->addCol($area_app);
$tabla->addCol($grp);

$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['add']    = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->classname = 'column center';

$tabla->setParent('ID_AREA_APP',$parent);   // Set as detail
$tabla->detail_tables=['CFG_AREAS_APPS_GROUPS_PERMS'];  // Set as master  

class AREAS_APPS_GROUPS_Events extends defaultTableEvents implements iEvents{ 

    function OnBeforeShow($owner){  }
    function OnDrawRow($owner,&$row,&$class){  }
    
    function OnDrawCell($owner,&$row,&$col,&$cell){
        global $prefix;
        if(in_array($owner->state,array('update','insert'))){
            if($col->fieldname=='ID_GROUP') {
                $sql = 'SELECT ID,NAME FROM CFG_AREAS_GROUPS WHERE ID_AREA IN '
                    .'(SELECT ID_AREA FROM CFG_AREAS_GROUPS WHERE ID_AREA= (SELECT ID_AREA FROM CFG_AREAS_APPS WHERE ID = '.$owner->parent_value.'))';
                $col->values = $owner->toarray('areas_apps_groups', $sql);
            }
        }
    }
    
    function OnBeforeShowForm($owner,&$form,$id){  }
    function OnAfterShowForm($owner,&$form,$id){  }
    function OnInsert($owner,&$result,&$post) {  } 
    function OnUpdate($owner,&$result,&$post) {  }

}

$tabla->events = New AREAS_APPS_GROUPS_Events(); 
