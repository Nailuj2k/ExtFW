<?php 

$tabla = new TableMysql(TB_ACL_PERMISSIONS);

$permission_id = new Field();
$permission_id->type      = 'int';
$permission_id->len       = 7;
$permission_id->fieldname = 'permission_id';
$permission_id->label     = 'Permission';
$permission_id->hide  = false;
$tabla->addCol($permission_id);

$permission_key = new Field();
$permission_key->type      = 'varchar';
$permission_key->len       = 30;
$permission_key->fieldname = 'permission_key';
$permission_key->label     = 'Permission key';
$permission_key->editable  = true ;
$permission_key->sortable  = true;
$permission_key->searchable  = true;
$tabla->addCol($permission_key);

$permission_name = new Field();
$permission_name->type      = 'varchar';
$permission_name->len       = 30;
$permission_name->fieldname = 'permission_name';
$permission_name->label     = 'Permission name';
$permission_name->editable  = true ;
$permission_name->sortable  = true;
$permission_name->searchable  = true;
$tabla->addCol($permission_name);

$tabla->name = TB_ACL_PERMISSIONS;
$tabla->title = 'Permisos';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['perms']['options'],'num_rows',20);
$tabla->inline_edit = true;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = true;
$tabla->perms['setup']  = true;
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;

//$tabla->classname = "table datatable-rows table-bordered  table-striped ";  //table table-striped 
//$tabla->sqlExec('UPDATE erp_acl_permissions SET permission_cmd=NULL');
///// FIX TABLE:  DELETE  FROM ACL_USER_ROLES  WHERE id_user NOT IN (SELECT user_id FROM CLI_USER)

class erp_acl_permissionsEvents extends defaultTableEvents implements iEvents{
  
  function OnShow($owner) {
  }
  
  function OnAfterShow($owner){ 

  }
  
  function OnBeforeShowForm($owner,&$form,$id){
    ?>
    <style>
      .zebra{ }

      .tb-perms.fixed_headers {  display:inline-block;  /*table-layout: fixed;*/  border-collapse: collapse;margin:4px;overflow: hidden;/* overflow-x: hidden;   overflow-y: auto;*/ }

      .tb-perms.fixed_headers thead {  }
      .tb-perms.fixed_headers thead tr {  display: block;  position: relative; }
      .tb-perms.fixed_headers tbody {  display: block;  overflow-x: hidden;  max-height: 189px; }
      .tb-perms.fixed_headers tbody tr:nth-child(even) {  /*background-color: #dddddd;*/}
      .tb-perms.fixed_headers th,
      .tb-perms.fixed_headers td {  /*padding: 2px;  text-align: left; font-size:11px;*/ }

      .tb-perms.table_roles.ro                                 {  width: 180px; height: 230px;}
      .tb-perms.table_roles.ro tr:nth-child(1)                 {  width: 175px;}
      .tb-perms.table_roles.ro tr:nth-child(1) th:nth-child(1) {  width: 175px;}
      .tb-perms.table_roles.ro tr              td:nth-child(1) {  width: 175px;text-align:left;}

      .tb-perms.table_users.ro                                 {  width: 575px; height: 230px;}
      .tb-perms.table_users.ro tr                              {  width: 575px;}  
      .tb-perms.table_users.ro tr:nth-child(1) th:nth-child(1) {  width: 575px;}

      .tb-perms.table_users.ro tr:nth-child(2) th:nth-child(1) {  width:200px;}
      .tb-perms.table_users.ro tr:nth-child(2) th:nth-child(2) {  width:150px;}
      .tb-perms.table_users.ro tr:nth-child(2) th:nth-child(3) {  width:222px;}
      .tb-perms.table_users.ro /*tr:nth-child(1)*/ td:nth-child(1) {  width:200px;}
      .tb-perms.table_users.ro /*tr:nth-child(1)*/ td:nth-child(2) {  width:150px;}
      .tb-perms.table_users.ro /*tr:nth-child(1)*/ td:nth-child(3) {  width:222px;}

      .tb-perms.fixed_headers td:contains('Allow') {color:green;}

      .old_ie_wrapper {  height: 300px;  width: 370px;  overflow-x: hidden;  overflow-y: auto;}
      .old_ie_wrapper tbody {  height: auto;}
    </style>
    <?php 
    if($owner->state == 'update'){
      global $_ACL;

      $rRoles = $_ACL->getRolesWithPermissionName($_ACL->getPermKeyFromID($id));
      $rUsers = $_ACL->getUsersWithPermissionName($_ACL->getPermKeyFromID($id),true);

      $html .= '<table class="tb-perms zebra fixed_headers table_roles ro">';
      $html .= '<thead>';
      $html .= '<tr><th colspan="4" class="thc">'.t('Roles').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
      $html .= '</thead>';
      $html .= '<tbody>';
      foreach ($rRoles as $role){
        $html .= '<tr>';
        $html .= '  <td>'.$role.'</td>';
        $html .= '</tr>';
      }
      $html .= '</tbody>';
      $html .= '</table>';

      $html .= '<table class="tb-perms zebra fixed_headers table_users ro">';
      $html .= '<thead>';
      $html .= '<tr><th colspan="4" class="thc">'.t('Usuarios').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
      $html .= '<tr>';
      $html .= '  <th>Username</th>';
      $html .= '  <th>Nombre</th>';
      $html .= '  <th>Email</th>';
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      foreach ($rUsers as $user){
        $html .= '<tr>';
        $html .= '  <td>'.$user['username'].'</td>';
        $html .= '  <td>'.$user['user_fullname'].'</td>';
        $html .= '  <td>'.$user['user_email'].'</td>';
        $html .= '</tr>';
      }
      $html .= '</tbody>';
      $html .= '</table>';

      $p = new formElementHtml;
      $p->html = $html;
      $form->addElement($p);

    }
    
  }  

}

$tabla->events = New erp_acl_permissionsEvents();