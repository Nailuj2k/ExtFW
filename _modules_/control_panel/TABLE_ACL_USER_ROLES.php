<?php 

$tabla = new TableMysql(TB_ACL_USER_ROLES);

$user_role_id = new Field();
$user_role_id->type      = 'int';
$user_role_id->len       = 7;
$user_role_id->fieldname = 'user_role_id';
$user_role_id->label     = 'Role';
$tabla->addCol($user_role_id);

$id_user = new Field();
$id_user->type      = 'int';
//$id_user->len       = 50;
$id_user->fieldname = 'id_user';
$id_user->label     = 'User';
$id_user->editable  = true ;
//$id_user->sortable  = true;
//$id_user->searchable  = true;
$tabla->addCol($id_user);

$id_role = new Field();
$id_role->type      = 'int';
//$id_role->len       = 50;
$id_role->fieldname = 'id_role';
$id_role->label     = 'Role';
$id_role->editable  = true ;
//$id_role->sortable  = true;
//$id_role->searchable  = true;
$tabla->addCol($id_role);

//$tabla->name = TB_ACL_USER_ROLES;
$tabla->title = 'Roles';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 6;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;