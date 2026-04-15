<?php

$tabla = new TableMysql( 'CFG_AREAS' ); 

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  = true;

$key = new Field();
$key->fieldname = 'AREAKEY';
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
$role->allowNull  = true;
$role->hide  = true;

$update   = new Field();
$update->type      = 'bool';
$update->width     = 30;
$update->fieldname = 'update_from_roles';  //_ldap';
$update->label     = 'Actualizar usuarios';   
$update->editable  = $_ACL->hasPermission('areas_edit');  //Administrador();
$update->calculated = true;
$update->hide = true;

$tabla->title =  'Áreas';       // str_replace('CFG_', '', $tabla->tablename);
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($key);
$tabla->addCol($role);
$tabla->addCol($update);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';
$tabla->classname = 'column left';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin');
$tabla->perms['add']    = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['delete'] = Root(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['setup']  = Root(); //$_ACL->userHasRoleName('Administradores');  

$tabla->detail_tables=['CFG_AREAS_APPS','CFG_AREAS_USERS','CFG_AREAS_GROUPS']; 

if (!Administrador())
    $tabla->where = 'ID IN ( SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER = '.$_SESSION['userid'].' AND ADMIN = 1)';

class AREAS_Events extends defaultTableEvents implements iEvents{ 

    function OnInsert($owner,&$result,&$post) { 
        $post['AREAKEY'] = $post['AREAKEY'] ? Str::sanitizeName($post['AREAKEY']) : Str::sanitizeName($post['NAME']);
    }
  
    function OnUpdate($owner,&$result,&$post) { 
        $post['AREAKEY'] = $post['AREAKEY'] ? Str::sanitizeName($post['AREAKEY']) : Str::sanitizeName($post['NAME']);
    }

    function OnBeforeShowForm($owner,&$form,$id){
    }

    function update_members_from_roles($owner,&$result,$post) {

        $strSQL  = 'SELECT user_id AS id_user FROM '.TB_USER.' WHERE  user_id IN ('
                 . " SELECT id_user FROM ".TB_ACL_USER_ROLES." WHERE id_role = {$post['ID_ROLE']} AND id_user NOT IN "
                 . "   (SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA = {$post['ID']})"
                 . ') AND user_active="1"';
        $msg=$strSQL.'<br />';

        $users = $owner->query2array($strSQL); 

        if(count($users)>0){
            foreach($users as $user){
                $owner->sql_query("INSERT INTO CFG_AREAS_USERS (ID_AREA,ID_USER) VALUES ({$post['ID']},{$user['id_user']})");
            }
            $result['msg'] = 'Usuarios actualidados'.$strSQL; 
            return true;
        }else{ 
            $result['msg'] = 'No se han añadido usuarios';  
            return false;
        }

    }

    function OnAfterUpdate($owner,&$result,&$post){
        if($post['update_from_roles'])  $this->update_members_from_roles($owner,$result,$post);
    }  
  
}

$tabla->events = New AREAS_Events();
