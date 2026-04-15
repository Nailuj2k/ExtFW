<?php 

$tabla = new TableMysql(TB_ACL_ROLES);

$role_id = new Field();
$role_id->type      = 'int';
$role_id->len       = 7;
$role_id->fieldname = 'role_id';
$role_id->label     = 'Role';
//$role_id->editable  = true ;
//$role_id->hide  = true;
$tabla->addCol($role_id);

$role_name = new Field();
$role_name->type      = 'varchar';
$role_name->len       = 50;
$role_name->fieldname = 'role_name';
$role_name->label     = 'Role name';
$role_name->editable  = true ;
$role_name->sortable  = true;
$role_name->searchable  = true;
$tabla->addCol($role_name);

$org_id = new Field();
$org_id->type      = 'int';
$org_id->len       = 5;
$org_id->fieldname = 'org_id';
$org_id->label     = 'Org';
$org_id->editable  = true ;
$org_id->hide      = false ;
$org_id->allowNull  = true ;
$org_id->default_value   = 0;
//$tabla->addCol($org_id);
 
$role_type = new Field();
$role_type->type      = 'select';
$role_type->len       = 5;
$role_type->fieldname = 'role_type';
$role_type->label     = 'Tipo';
$role_type->editable  = true ;
$role_type->values    = array('1'=>'Default','2'=>'Active Directory');
$role_type->allowNull = true ;
$role_type->default_value   = 1;
$tabla->addCol($role_type);

$description = new Field();
$description->type      = 'textarea';
$description->fieldname = 'description';
$description->label     = 'Descripción';   
$description->height    = 100;
$description->width     = 500;
$description->editable  = true; 
$description->wysiwyg   = false;
$tabla->addCol($description);

$filtrable   = new Field();
$filtrable->type      = 'bool';
$filtrable->width     = 30;
$filtrable->fieldname = 'filtrable';
$filtrable->label     = 'Filtrable';   
$filtrable->editable  = Administrador();
$filtrable->filtrable = true;
$filtrable->default_value = true;
$tabla->addCol($filtrable);

$tabla->name = TB_ACL_ROLES;
$tabla->title = 'Roles';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['roles']['options'],'num_rows',20);
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Administrador(); //Root();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;

class erp_acl_rolesEvents extends defaultTableEvents implements iEvents{
 
   function OnBeforeShowForm($owner,&$form,$id){
    ?>
    <style>
      .zebra{ }

      .tb-roles.fixed_headers {  display:inline-block;  /*table-layout: fixed;*/  border-collapse: collapse;margin:4px;}

      .tb-roles.fixed_headers thead {  }
      .tb-roles.fixed_headers thead tr {  display: block;  position: relative; }
      .tb-roles.fixed_headers tbody {  display: block;  overflow-x: hidden;    height: 450px;}
      .tb-roles.fixed_headers tbody tr:nth-child(even) {  /*background-color: #dddddd;*/}
      .tb-roles.fixed_headers th,
      .tb-roles.fixed_headers td {  /*padding: 2px;  text-align: left; font-size:11px;*/ }

      .tb-roles.table_users.ro                                 {  width: 375px;}
      .tb-roles.table_users.ro tr                              {  width: 375px;}
      
      .tb-roles.table_users.ro tr:nth-child(1) th:nth-child(1) {  width: 375px;}

      .tb-roles.table_users.ro /*tr:nth-child(2)*/ th:nth-child(1) {  width: 20px;}
      .tb-roles.table_users.ro /*tr:nth-child(2)*/ th:nth-child(2) {  width: 100px;}
      .tb-roles.table_users.ro /*tr:nth-child(2)*/ th:nth-child(3) {  width: 230px;}
      .tb-roles.table_users.ro /*tr:nth-child(2)*/ th:nth-child(4) {  width: 30px;}
      .tb-roles.table_users.ro /*tr:nth-child(2)*/ th.thc {  width: 375px;}

      .tb-roles.table_users.ro /*tr:nth-child(1)*/ td:nth-child(1) {  width: 20px;}
      .tb-roles.table_users.ro /*tr:nth-child(1)*/ td:nth-child(2) {  width: 100px;}
      .tb-roles.table_users.ro /*tr:nth-child(1)*/ td:nth-child(3) {  width: 230px;}
      .tb-roles.table_users.ro /*tr:nth-child(1)*/ td:nth-child(4) {  width: 30px;}

      .tb-roles.table_perms.rw                                 {  width: 400px;}
  /*    .tb-roles.table_perms.rw tr:nth-child(1)                 {  width: 400px;}*/
      .tb-roles.table_perms.rw tr                              {  width: 400px;}
      .tb-roles.table_perms.rw tr:nth-child(1) th:nth-child(1) {  width: 400px;}

      .tb-roles.table_perms.rw tr:nth-child(2) th:nth-child(1) {  width: 220px !important;}
      .tb-roles.table_perms.rw tr:nth-child(2) th:nth-child(2) {  width: 60px;letter-spacing: -0.5px; font-weight: 700;}
      .tb-roles.table_perms.rw tr:nth-child(2) th:nth-child(3) {  width: 60px;letter-spacing: -0.5px; font-weight: 700;}
      .tb-roles.table_perms.rw tr:nth-child(2) th:nth-child(4) {  width: 60px;letter-spacing: -0.5px; font-weight: 700;}
      .tb-roles.table_perms.rw tr              td:nth-child(1) {  width: 220px;}
      .tb-roles.table_perms.rw tr              td:nth-child(2) {  width: 60px;}
      .tb-roles.table_perms.rw tr              td:nth-child(3) {  width: 60px;}
      .tb-roles.table_perms.rw tr              td:nth-child(4) {  width: 60px;}

      .tb-roles.fixed_headers td:contains('Allow') {color:green;}

      .old_ie_wrapper {  height: 450px;  width: 370px;  overflow-x: hidden;  overflow-y: auto;}
      .old_ie_wrapper tbody {  height: auto;}
    </style>
    <?php 
    if($owner->state == 'update'){
      global $_ACL;

      $rPerms = $_ACL->getRolePerms($id);
      $aPerms = $_ACL->getAllPerms('full');
     //echo '<pre>'.print_r($rPerms,true).'</pre>';
      $html .= '  <table class="tb-roles zebra fixed_headers table_perms rw">';
      $html .= '  <thead>';
      $html .= '  <tr><th colspan="4" class="thc">'.t('Permisos').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
      $html .= '  <tr><th> &nbsp; </th><th align="center">'.t('Permitir').'</th><th align="center">'.t('Denegar').'</th><th align="center">'.t('Ignorar').'</th></tr>';
      $html .= '  </thead>';
      $html .= '  <tbody>';

          foreach ($aPerms as $k => $v){
            $html .= '<tr><td><label>' . $v['Name'] . '</label></td>';
            
            $html .= '<td align="center"><input type="radio" name="perm_' . $v['ID'] . '" id="perm_' . $v['ID'] . '_1" value="1"';
            if ($rPerms[$v['Key']]['value'] === true && $id != '') { $html .= ' checked="checked"'; }
            $html .= ' /></td>';
            
            $html .= '<td align="center"><input type="radio" name="perm_' . $v['ID'] . '" id="perm_' . $v['ID'] . '_0" value="0"';
            if ($rPerms[$v['Key']]['value'] != true && $id != '')  { $html .= ' checked="checked"'; }
            $html .= ' /></td>';
            
            $html .= '<td align="center"><input type="radio" name="perm_' . $v['ID'] . '" id="perm_' . $v['ID'] . '_X" value="X"';
            if ($id == '' || !array_key_exists($v['Key'],$rPerms)) { $html .= ' checked="checked"'; }
            $html .= ' /></td>';
            
            $html .= '</tr>';
          }
      $html .= '</tbody>';
      $html .= '</table>';
  
      $p = new formElementHtml;
      $p->html = $html;
      $form->addElement($p);
    }

    if($id){

      $strSQL  = 'SELECT * FROM '.TB_USER
               .' WHERE user_id IN (SELECT id_user FROM '.TB_ACL_USER_ROLES.' WHERE id_role = '.$id.' ) ORDER BY user_level DESC,username';
  
      $data = $owner->sql_query($strSQL);

      $html1 .= '  <table class="tb-roles zebra fixed_headers table_users ro" id="kdiv_box_users">';
      $html1 .= '  <thead>';
      $html1 .= '  <tr><th colspan="4" class="thc">'.t('Usuarios').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
      $html1 .= '<tr>';
      $html1 .= '  <th>Id</th>';
      $html1 .= '  <th>Username</th>';
      $html1 .= '  <th>Nombre</th>';
      //$html1 .= '  <td>'.$row['user_email'].'</td>';
      $html1 .= '  <th>Nivel</th>';
      $html1 .= '</tr>';
      $html1 .= '  </thead>';
      $html1 .= '  <tbody>';

      foreach($data as $row){
        $html1 .= '<tr>';
        $html1 .= '  <td>'.$row['user_id'].'</td>';
        $html1 .= '  <td>'.$row['user_name'].'</td>';
        $html1 .= '  <td>'.$row['user_fullname'].'</td>';
        //$html1 .= '  <td>'.$row['user_email'].'</td>';
        $html1 .= '  <td>'.$row['user_level'].'</td>';
        $html1 .= '</tr>';
      }
 
      $strSQL2  = 'SELECT item_id,item_name,item_caption,item_level FROM '.TB_ITEM
               .' WHERE item_id IN (SELECT id_item FROM '.TB_ACL_ITEM_ROLES.' WHERE id_role = '.$id.' ) ORDER BY item_level DESC,item_name';
      $data2 = $owner->sql_query($strSQL2);

      $html1 .= '  <tr><th colspan="4" class="thc">'.t('Páginas').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
      $html1 .= '<tr>';
      $html1 .= '  <th>Id</th>';
      $html1 .= '  <th>Name</th>';
      $html1 .= '  <th>Caption</th>';
      //$html1 .= '  <td>'.$row['user_email'].'</td>';
      $html1 .= '  <th>Nivel</th>';
      $html1 .= '</tr>';
      foreach($data2 as $row2){
        $html1 .= '<tr>';
        $html1 .= '  <td>'.$row2['item_id'].'</td>';
        $html1 .= '  <td>'.$row2['item_name'].'</td>';
        $html1 .= '  <td>'.$row2['item_caption'].'</td>';
        //$html1 .= '  <td>'.$row['user_email'].'</td>';
        $html1 .= '  <td>'.$row2['item_level'].'</td>';
        $html1 .= '</tr>';
      }

      $html1 .= '</tbody>';
      $html1 .= '</table>';

 
      $p1 = new formElementHtml;
      $p1->html = $html1;
      $form->addElement($p1);
    
    }

  }
  
  function OnAfterShowForm($owner,&$form,$id){

  }
  

  function OnAfterUpdate($owner,&$result,&$post){
    global $_ACL;
    foreach ($post as $k => $v){
      if (substr($k,0,5) == "perm_"){
        $permID = str_replace("perm_","",$k);
        if ($v == 'X'){
          $sql_delete = sprintf("DELETE FROM ".TB_ACL_ROLE_PERMS." WHERE id_role = %u AND id_permission = %u",
                                 $post['role_id'],
                                 $permID);
          Table::sqlExec($sql_delete);
          continue;
        }
        $sql_replace = sprintf("REPLACE INTO ".TB_ACL_ROLE_PERMS." SET id_role = %u, id_permission = %u, role_perm_value = %u, role_perm_add_date = '%s'",
                                $post['role_id'],
                                $permID, 
                                $v, 
                                date ("Y-m-d H:i:s") );
        //$result['msg'] .=  $sql_replace;
        Table::sqlExec($sql_replace);
      }
    }
    $_ACL->buildACL();

  }  

  function OnAfterShow($owner){ 

  }

  /**
  function OnBeforeUpdate($owner,$id){ 
    $row = $owner->getRow($id);
    $owner->perms['edit'] = ($row['editable']==1);  
  }

  function OnInsert($owner,&$result,&$post) { 
    $post['org_id'] = $_SESSION['org_id'];  
  }  
  
  function OnUpdate($owner,&$result,&$post){ 
    $row = $owner->getRow($post[$owner->pk->fieldname]);
    $owner->perms['edit'] = ($row['editable']==1);  
    $post['org_id'] = $_SESSION['org_id'];  
  }
**/
}

$tabla->events = New erp_acl_rolesEvents();