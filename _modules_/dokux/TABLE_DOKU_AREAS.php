<?php

$tabla = new TableMysql( 'DOKU_AREAS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

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
$name->width     = 120;
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
$role->width     = 190;
$role->fieldname = 'ID_ROLE';
$role->label     = 'Grupo';   
$role->values    = $tabla->toarray('web_roles',         'SELECT role_id AS ID, role_name AS NAME FROM '.TB_ACL_ROLES.' ORDER BY NAME',true);   
$role->values_all= $tabla->toarray('web_roles_all',     'SELECT role_id AS ID, role_name AS NAME FROM '.TB_ACL_ROLES,true);
$role->editable  = true;
$role->allowNull  = true;
//$role->hide  = true;

$update   = new Field();
$update->type      = 'bool';
$update->width     = 30;
$update->fieldname = 'update_from_roles';  //_ldap';
$update->label     = 'Actualizar usuarios';   
$update->editable  = true; //Administrador();
$update->calculated = true;
$update->hide = true;

$tabla->title =  'Áreas';       // str_replace('CFG_', '', $tabla->tablename);
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 5;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($key);
$tabla->addCol($role);
//$tabla->addCol($update);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';
//$tabla->classname = 'column left';

$tabla->perms['view']   =  $doku_admin;//$_ACL->userHasRoleName('Administradores'); //$_ACL->hasPermission('doku_admin');       //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['reload'] =  $doku_admin;//$_ACL->userHasRoleName('Administradores'); //$_ACL->hasPermission('doku_admin');  
$tabla->perms['edit']   =  $doku_admin;//$_ACL->userHasRoleName('Administradores'); //$_ACL->hasPermission('doku_admin');
$tabla->perms['add']    =  $doku_admin;//$_ACL->userHasRoleName('Administradores'); //$_ACL->hasPermission('doku_admin');
$tabla->perms['delete'] =  $doku_admin;//$_ACL->userHasRoleName('Administradores'); //$_ACL->hasPermission('doku_admin');
$tabla->perms['setup']  =  $doku_admin;//$_ACL->userHasRoleName('Administradores');  

//$tabla->setParent('FK_FIELDNAME',$parent);   // Set as detail
//$tabla->detail_tables=array(/*'CFG_APPS','CFG_AREAS_APPS',*/'CFG_AREAS_USERS','CFG_AREAS_GROUPS');  // Set as master

//if ($_ACL->userHasRoleName('Area_Admin')===false)
//if (!Administrador()) $tabla->where = 'ID IN ( SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER = '.$_SESSION['userid'].' AND ADMIN = 1)';

class AREAS_Events extends defaultTableEvents implements iEvents{ 

  function OnInsert($owner,&$result,&$post) { 
    $post['AREAKEY'] = $post['AREAKEY'] ? Str::sanitizeName($post['AREAKEY']) : Str::sanitizeName($post['NAME']);
  }
  
  function OnUpdate($owner,&$result,&$post) { 
    $post['AREAKEY'] = $post['AREAKEY'] ? Str::sanitizeName($post['AREAKEY']) : Str::sanitizeName($post['NAME']);
    //Search GROUP_{$post['AREAKEY']}_* and update 
  }

  function OnBeforeShowForm($owner,&$form,$id){
  }
  /**
  function update_members_from_ldap($post) {
    global $_ACL,$juxLDAP;
    $members_usernames = array();
    $juxLDAP->get_members_usernames($members_usernames,$post['role_name']); //$_ACL->getRoleNameFromID($id));
    foreach($members_usernames as $username){
       $userid = $_ACL->getUserId($username);
       if($userid>0) { 
          $result .= $userid.' '.$username.'<br />';//.' -- '.$post['role_name'];
          $_ACL->updateUserRole( $userid,$post['role_name'],true);
       }
    }
    return 'Usuarios actualidados:<br /> '.$result; //print_r($members_usernames,true);
  }
  **/

  function update_members_from_roles($owner,$post) {
    /**************************************************************************************************************
    global $_ACL,$juxLDAP;
    $strSQL  = 'SELECT user_id AS id_user FROM '.TB_USER.' WHERE  user_id IN ('
             . " SELECT id_user FROM ".TB_ACL_USER_ROLES." WHERE id_role = {$post['ID_ROLE']} AND id_user NOT IN "
             . "   (SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA = {$post['ID']})"
             . ') AND user_active="1"';
    $msg=$strSQL.'<br />';
    $users = $owner->query2array($strSQL); 
    foreach($users as $user){
        $owner->sql_query("INSERT INTO CFG_AREAS_USERS (ID_AREA,ID_USER) VALUES ({$post['ID']},{$user['id_user']})");
    }
    return 'Usuarios actualidados'.$strSQL;  //$result; //print_r($members_usernames,true);
    ****************************************************************************************************************/
  }

  function OnAfterUpdate($owner,&$result,&$post){
  //  if($post['update_ldap']) $result['msg'] = $this->update_members_from_ldap($post);
    ////////////////////////////////////////////if($post['update_from_roles']) $result['msg'] = $this->update_members_from_roles($owner,$post);
    //$result['msg'] = $post['update_ldap']?'SI':'NO';
  }  
  
  function OnAfterCreate($owner){ 
    if($owner->recordCount()<1){
      //$owner->sql_query("INSERT INTO {$owner->tablename} (NAME) VALUES('Todos')");
      Table::sqlExec("INSERT INTO {$owner->tablename} (NAME) VALUES('Archivo')");
      Table::sqlExec("INSERT INTO {$owner->tablename} (NAME) VALUES('Informática')");
    }
  } 

}

$tabla->events = New AREAS_Events();


?>