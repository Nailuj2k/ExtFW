<?php

  
  function usernameOk($username){
      $okis = preg_match('/^[a-z]{3}[0-9]{2}[a-z0-9]{1}$/',$username);
      return $okis; //?true:false;
  }
  $role_name=$_ARGS['role']?$_ARGS['role']:'A8_ESP_Informatica';

  $row=Table::getFieldsValues("SELECT * FROM ACL_ROLES WHERE role_name = '{$role_name}'");
  Vars::debug_var($row);


//  function getRoleUsers($role_name){
  $members_usernames=array();
  if($row['role_type']==2){ // Role is a LDAP group
      $login->ldap->get_members_usernames($members_usernames,$role_name);//,true);  //update_members_from_ldap($post);
  }else{
      $members_usernames = $_ACL->getRoleUsers($role_name);
  }




  foreach ($members_usernames as $k=>$v){
      Vars::debug_var($k.' - '.$v.' - '.usernameOk($k));
  }


