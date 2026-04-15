<?php

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$area_app            = new Field();
$area_app->type      = 'int';
$area_app->len       = 5;
$area_app->width     = 15;
$area_app->fieldname = 'ID_AREA_APP';
$area_app->label     = 'Aplicación';   
//$area_app->hide      = true;

$app_perm            = new Field();
$app_perm->type      = 'select';
$app_perm->len       = 5;
$app_perm->width     = 15;
$app_perm->fieldname = 'ID_APP_PERM';
$app_perm->label     = 'Permiso';   
//$app_perm->source    = 'app_perms';   
//$app_perm->source_all= 'app_perms_all';   
$app_perm->editable  = true;
$app_perm->width     = 140;
//$app_perm->hide      = true;


$default = new Field();
$default->type      = 'bool';
$default->fieldname = 'BYDEFAULT';
$default->label     = 'Por omisión';   
$default->editable = true;
$default->default_value = '0';
$default->editable  = true;
$default->filtrable = true;  
$default->width = 85;

$tabla = new TableMysql( 'CFG_AREAS_APPS_PERMS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );
$tabla->title =  'Áreas/Apps/Perms';       // str_replace('CFG_', '', $tabla->tablename);
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 6;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($area_app);
$tabla->addCol($app_perm);
$tabla->addCol($default);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

//$tabla->classname = 'column right';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['add']    = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->setParent('ID_AREA_APP',$parent);   // Set as detail
//$tabla->detail_tables=array('DETAIl_TABLENAME1'[,'DETAIl_TABLENAME2'] ... );  // Set as master

class AREAS_APPS_PERMS_Events extends defaultTableEvents implements iEvents{ 

    function OnInsert($owner,&$result,&$post) {   

    }

    function OnUpdate($owner,&$result,&$post) {   

    }

    function OnAfterCreate($owner){ 
    } 

    function OnBeforeShow($owner){
    $owner->colByName('ID_APP_PERM')->values_all = $owner->toarray('app_perms_all', 'CFG_APPS_PERMS', 'ID', 'NAME', '',true); 
    if($owner->recordCount('SELECT count('.$owner->pk->fieldname.') AS total FROM '.$owner->tablename.' WHERE '.$owner->parent_key.'='.$owner->parent_value)<1) {
        $owner->sql_query('INSERT INTO '.$owner->tablename.' (ID_AREA_APP,ID_APP_PERM,BYDEFAULT)   
                            SELECT 
                            AA.ID AS AREA_APP, AP.ID AS APP_PERM , AP.BYDEFAULT 
                            FROM 
                            CFG_AREAS_APPS AA,
                            CFG_APPS_PERMS AP 
                            WHERE AA.ID_AREA = (SELECT ID_AREA FROM CFG_AREAS_APPS WHERE ID = '.$owner->parent_value.')   
                            AND AA.ID_APP  = (SELECT ID_APP  FROM CFG_AREAS_APPS WHERE ID = '.$owner->parent_value.')  
                            AND AP.ID_APP  = (SELECT ID_APP  FROM CFG_AREAS_APPS WHERE ID = '.$owner->parent_value.') ');
        echo '<div class="info"><p>Cargados los permisos con sus valores por omisión</p></div>';
    }  
    }

    function OnAfterShowForm($owner,&$form,$id){
    }

    function OnDrawCell($owner,&$row,&$col,&$cell){
        if(in_array($owner->state,array('update','insert'))){
            if($col->fieldname=='ID_APP_PERM') {
                $owner->colByName('ID_APP_PERM')->values = $owner->toarray('app_perms',   
                                                                           'SELECT ID,NAME FROM CFG_APPS_PERMS '
                                                                          .' WHERE ID_APP IN (SELECT ID_APP FROM CFG_AREAS_APPS WHERE ID_APP = '
                                                                          .'(SELECT ID_APP FROM CFG_AREAS_APPS WHERE ID='.$owner->parent_value.'))',true); 
            }
        }
    }



}


$tabla->events = New AREAS_APPS_PERMS_Events();
