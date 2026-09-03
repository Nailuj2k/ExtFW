<?php 

class JuxLDAPutils{
/**********
  // Update roles table from AD groups
  // FIX sacar los roles desde AD en una sola vez y sincronizar
  // return true if tbroles is updated while sincronize
  public static function sincronizeRoles($ldapgroups) {
    foreach( $ldapgroups as $roleName) {
      //JuxLDAP_test::s($roleName);
      //if(substr($roleName,0,3)==='A8_'){
          $aRoles[] = $roleName; 
          $rows = Table::getFieldsValues("SELECT COUNT(role_id) as RI FROM ".TB_ACL_ROLES." WHERE role_name='$roleName'");
          if ($rows['RI']<1){
            $strSQLx = sprintf("INSERT INTO ".TB_ACL_ROLES." SET role_type=2,role_name = '%s'",$roleName);
            if (Table::sqlExec($strSQLx) ) {
              //lastInsertId();
              $needUpdate = true;
            }
          }
      // }
    }
    return $needUpdate;
  }

***/

/***********

Fatal error: Uncaught Error: 
Class "Table" not found in /var/www/area8.sms.carm.es/_classes_/ldap.utils.class.php:13
Stack trace: 
#0 /var/www/area8.sms.carm.es/_classes_/login.class.php(320): JuxLDAPutils::sincronizeRoles() 
#1 /var/www/area8.sms.carm.es/_includes_/auth.php(16): Login->login() 
#2 /var/www/area8.sms.carm.es/_includes_/init.php(144): include('...') 
#3 /var/www/area8.sms.carm.es/index.php(39): include('...') 
#4 {main} thrown in /var/www/area8.sms.carm.es/_classes_/ldap.utils.class.php on line 13

*********/

  // return true if tbroles is updated while sincronize
/************************
  public static function sincronizeUserRoles($userid,$ldapgroups) {
    if(!Vars::IsNumeric($userid)){
      $rows = Table::getFieldsValues('SELECT user_id FROM '.TB_USER.' WHERE username=\''.$userid.'\'' );
      $userid = $rows['user_id'];
      if(!Vars::IsNumeric($userid)) return false;
    } 
   
    if(count($ldapgroups)>0){
        //$result_roles = Table::sqlQuery('SELECT role_id FROM '.TB_ACL_ROLES." WHERE role_name LIKE 'A8_%' AND role_name IN ('".implode("','", $ldapgroups)."')");
          $result_roles = Table::sqlQuery('SELECT role_id FROM '.TB_ACL_ROLES." WHERE role_name IN ('".implode("','", $ldapgroups)."')");
          if($result_roles){
            foreach ($result_roles as $row) {
              $rows = Table::getFieldsValues("SELECT COUNT(id_user) as UR FROM ".TB_ACL_USER_ROLES." WHERE id_role={$row['role_id']} AND id_user={$userid}");
              if ($rows['UR']<1){
                if (Table::sqlExec( "INSERT INTO ".TB_ACL_USER_ROLES." (id_role,id_user) VALUES ({$row['role_id']},{$userid})") ) {
                  //lastInsertId();
                  $needUpdate = true;
                }
             }
            }
          }

         $sql_w = " WHERE id_user={$userid} "
                . "   AND id_role NOT IN ( SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_type=2 AND role_name IN ('".implode("','", $ldapgroups)."') ) "
                . "   AND id_role IN     ( SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_type=2 )";

     }else{
         $sql_w = " WHERE id_user={$userid} "
                . "   AND id_role IN     ( SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_type=2 )";
     }

     $rows = Table::getFieldsValues('SELECT count(0) AS UR FROM '.TB_ACL_USER_ROLES . $sql_w);
     if ($rows['UR']>0){
        $roles_to_delete = "DELETE FROM  ".TB_ACL_USER_ROLES." " . $sql_w;
        //self::s(__LINE__."<pre>$roles_to_delete</pre>");
        //$sql_debug = true;
        if (Table::sqlExec($roles_to_delete)){
          $needUpdate = (count($ldapgroups)>0);
        }
        //$sql_debug = false;
      }

    return $needUpdate;
  }

  public static function sincronizeMyRoles($ldapgroups) {
    return self::sincronizeUserRoles($_SESSION['username'],$ldapgroups);
  }
  public static function listUserRoles($userid) {
    if(!Vars::IsNumeric($userid)){
      $userid = Table::getFieldValue(TB_USER,'user_id',"WHERE username='".$userid."'" );
      if(!Vars::IsNumeric($userid)) return false;
    } 
    if($userid>0){
      $result_user_roles = Table::sqlQuery("SELECT ur.id_role,r.role_name FROM ".TB_ACL_USER_ROLES." ur,".TB_ACL_ROLES." r WHERE  ur.id_role=r.role_id AND ur.id_user = {$userid}");
  //    echo "SELECT ur.id_role,r.role_name FROM ".TB_ACL_USER_ROLES." ur,".TB_ACL_ROLES." r WHERE  ur.id_role=r.role_id AND ur.id_user = {$userid}";
      if($result_user_roles){
       foreach($result_user_roles as $rpw_user_role) {
          echo '-'.$row_user_role['id_role'].' '.$row_user_role['role_name'].'<br />';
        }
      }
    }else {
     echo 'No userid: '.$id;
    }
  }

  public static function listMyRoles() {
    return self::listUserRoles($_SESSION['user_id']);
  }

  public static function s($x) { 
    if(is_array($x)) {
      echo '<pre class="code">';
      print_r($x);
      echo '</pre>';
    }else{
      echo '<span style="font-size:0.9em;">'.$x.'</span><br />'; 
    }
  }
*********************/

}