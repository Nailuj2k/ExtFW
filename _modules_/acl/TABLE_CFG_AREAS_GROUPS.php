<?php

$tabla = new TableMysql( 'CFG_AREAS_GROUPS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$area            = new Field();
$area->type      = 'int';
$area->len       = 5;
$area->width     = 15;
$area->fieldname = 'ID_AREA';
$area->label     = 'Área';   
$area->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->len       = 100;
$name->width     = 300;
$name->type      = 'varchar';
$name->editable  = true;

$key = new Field();
$key->fieldname = 'GROUPKEY';
$key->label     = 'Identificador';   
$key->len       = 30;
$key->type      = 'varchar';
$key->editable  = true;
$key->hide  = true;

//
// Field LINKED_TO_ROLENAME type select
// If set and point to a active and valid role_name this group is
// synchronized with users in role_name and must be updated when 
// the role_name is updated
// 
$role            = new Field();
$role->type      = 'select';
$role->len       = 5;
$role->width     = 200;
$role->fieldname = 'ID_ROLE';
$role->label     = 'Grupo';   
$role->values    = $tabla->toarray('web_roles',         'SELECT role_id AS ID, role_name AS NAME FROM '.TB_ACL_ROLES.' ORDER BY NAME',true);   
$role->values_all= $tabla->toarray('web_roles_all',     'SELECT role_id AS ID, role_name AS NAME FROM '.TB_ACL_ROLES,true);
$role->editable  = true;

$tabla->title =  'Grupos '.$parent; 
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($area);
$tabla->addCol($name);
$tabla->addCol($key);
//$tabla->addCol($role);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['add']    = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->setParent('ID_AREA',$parent);   // Set as detail
$tabla->detail_tables=array('CFG_AREAS_GROUPS_USERS');  // Set as master

$tabla->classname = 'column center';

class AREAS_GROUPS_Events extends defaultTableEvents implements iEvents{ 

    function OnInsert($owner,&$result,&$post) { 
        global $_ACL;
        $area = $owner->getFieldValue('SELECT AREAKEY FROM CFG_AREAS WHERE ID = '.$post['ID_AREA']);
        $post['GROUPKEY'] = $post['GROUPKEY'] ? Str::sanitizeName($post['GROUPKEY']) : Str::sanitizeName($post['NAME']);
    }
    
    function OnUpdate($owner,&$result,&$post) { 
        global $_ACL;
        $area = $owner->getFieldValue('SELECT AREAKEY FROM CFG_AREAS WHERE ID = '.$post['ID_AREA']);
        $post['GROUPKEY'] = $post['GROUPKEY'] ? Str::sanitizeName($post['GROUPKEY']) : Str::sanitizeName($post['NAME']);
    }

    function OnDelete($owner,&$result,$id){
    }

}

$tabla->events = New AREAS_GROUPS_Events();
