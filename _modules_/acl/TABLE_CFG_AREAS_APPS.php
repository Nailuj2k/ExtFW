<?php

$tabla = new TableMysql( 'CFG_AREAS_APPS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$area            = new Field();
$area->type      = 'hidden';
$area->len       = 5;
$area->fieldname = 'ID_AREA';
//$area->editable  = true;
$area->value     = $parent;
$area->hide      = true;
$area->width     = 25;

$app            = new Field();
$app->type      = 'select';
$app->len       = 5;
$app->width     = 270;
$app->fieldname = 'ID_APP';
$app->label     = 'Aplicación';   
//$app->source    = 'cfg_apps';   
$app->editable  = $_ACL->userHasRoleName('Administradores');
$app->readonly  = !$_ACL->userHasRoleName('Administradores');
//$id->hide      = true;
$app->values = $tabla->toarray('cfg_apps' , 'CFG_APPS'  ,'ID',  'NAME','',true); 

$tabla->title =  'Áreas/Apps';       // str_replace('CFG_', '', $tabla->tablename);
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($area);
$tabla->addCol($app);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->classname = 'column center';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['add']    = $_ACL->userHasRoleName('Administradores');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Administradores');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->setParent('ID_AREA',$parent);   // Set as detail
$tabla->detail_tables=['CFG_AREAS_APPS_PERMS','CFG_AREAS_APPS_GROUPS'];  // Set as master   //20191030


class AREAS_APPS_Events extends defaultTableEvents implements iEvents{ 

    function OnBeforeShow($owner){
       // $owner->colByName('ID_APP')->values = $owner->toarray('cfg_apps' , 'CFG_APPS'  ,'ID',  'NAME','',true); 
    }
    
    function OnBeforeUpdate($owner,$id){
       // $owner->colByName('ID_APP')->readonly=true;     //FIX can be editable while not has child 
       // $owner->colByName('ID_APP')->editable=false;    //    groups or users in detail tables
    }
    
    function OnBeforeShowForm($owner,&$form,$id) { }

    function OnAfterShowForm($owner,&$form,$id){
        if($owner->state=='update'){
        ?><div class="datatable" style="min-height:150px;"><?php
            Table::$module_name = 'acl';
            Table::$theme = 'default';
            $parent = $id;
            Table::show_table('CFG_AREAS_APPS_PERMS','acl');
            ?><script type="text/javascript">load_page('acl','CFG_AREAS_APPS_PERMS',1,<?=$id?>,1);</script></div><?php
        }
    }
    }

$tabla->events = New AREAS_APPS_Events();
?>