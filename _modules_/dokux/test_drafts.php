<?php

class notNotRecipientsEvents extends defaultTableEvents implements iEvents{ 
 
   function OnShow($owner){
    global $prefix;
    if(!defined("USE_ROLES")) {                                                                  
      $owner->colByName('RECEIVER_USER_ID')->values = $owner->toarray('values_users_recv' , "SELECT user_id AS ID,user_fullname AS NAME  
                                                                                             FROM {$prefix}_user 
                                                                                             WHERE user_id<>{$_SESSION['userid']} 
                                                                                             AND user_id IN (
                                                                                               SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA IN ( 
                                                                                                 SELECT ID FROM CFG_AREAS WHERE ID IN ( 
                                                                                                   SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER = {$_SESSION['userid']})))",true); 
      $owner->colByName('RECEIVER_USER_ID')->values_all = $owner->colByName('RECEIVER_USER_ID')->values;
      $owner->colByName('GROUP_ID')->values = $owner->toarray('values_roles_recv' , "SELECT ID,NAME 
                                                                                     FROM CFG_AREAS_GROUPS
                                                                                     WHERE ID_AREA IN (
                                                                                       SELECT ID FROM CFG_AREAS WHERE ID IN ( 
                                                                                         SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER = {$_SESSION['userid']}))",true); 
      $owner->colByName('GROUP_ID')->values_all = $owner->colByName('GROUP_ID')->values;
    }
  }


function getPerm($owner,$perm) {
    global $_ACL;
    if(defined("USE_ROLES")){
     // return ( $_ACL->hasPermission($perm) ) ? true : false ;
      return ( $_ACL->hasPermission('messages_send') || $_ACL->hasPermission('messages_receive') ) ? true : false ;
    }else{
      $sql = 'SELECT AAUP.ID , '
           . '       AAUP.ID_USER , '
           . '       AAUP.ID_AREA_APP_PERM , '
           . '       AP.PERMKEY '
           . 'FROM CFG_AREAS_APPS_USERS_PERMS AAUP, '
           . '     CFG_AREAS_APPS_PERMS AAP, '
           . '     CFG_APPS_PERMS AP,'
           . '     CFG_AREAS_APPS AA '
           . 'WHERE AAP.ID=AAUP.ID_AREA_APP_PERM '
           . '  AND AP.ID=AAP.ID_APP_PERM '
           . '  AND AAUP.ID_AREA_APP = AA.ID '
           . "  AND AP.PERMKEY = '{$perm}'"
           . '  AND AAUP.ID_USER = '.$_SESSION['userid'] .' '
           . '  AND AA.ID_APP = 1 ';  // Notificaciones = 1

      if ($owner->getFieldValue($sql)) return true; else return false;
    }
  }
 private function getAreaUsers($owner,$admin=false,$debug=false) {  //COPY
    global $prefix;
    $users = array();
    $whereadmin = ($admin)  ? ' AND ADMIN=1 ' : '' ; //FIX receive_area
    $sql = 'SELECT user_id  ,  CONCAT(user_name,\' -  \',user_fullname) AS username FROM '.$prefix.'_user '
         . 'WHERE user_id IN (SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA IN ( '
         . 'SELECT ID FROM CFG_AREAS WHERE ID IN ( SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER = '.$_SESSION['userid'].')) '.$whereadmin.'  )';
    $data = $owner->sql_query($sql);
    if($debug) echo '<ul>';
    if($data){
      while($row = jux_sql_assoc($data)){
        $users[$row['user_id']]=array('id'=>$row['user_id'],'name'=>$row['username']);
        if($debug) echo '<li>'.$row['user_id'].' '.$row['username'].'</li>';
      }
    }
    if($debug) echo '</ul>';
    return $users;
  }

private function getAreaUsersWithPerm($owner,$perm='receive_area',$debug=false) {  //COPY
    global $prefix;
    $users = array();
    $sql = "SELECT user_id  ,  CONCAT(user_name,' -  ',user_fullname) AS username FROM {$prefix}_user  WHERE user_id IN( "
         . "  SELECT APUS.ID_USER " //, AA.ID_AREA, AA.ID_APP, AAP.ID , AP.ID, AP.PERMKEY "
         . "  FROM CFG_AREAS_APPS_USERS_PERMS APUS ,  "
         . "            CFG_AREAS_APPS AA,  "
         . "            CFG_AREAS_APPS_PERMS AAP, "
         . "            CFG_APPS_PERMS AP    "
         . "  WHERE AA.ID = APUS.ID_AREA_APP  "
         . "  AND APUS.ID_AREA_APP_PERM = AAP.ID  "
         . "  AND AAP.ID_APP_PERM=AP.ID  "
         . "  AND AA.ID_AREA IN ( SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER={$_SESSION['userid']})   "
         . "  AND AA.ID_APP = 1  "
         . "  AND AP.PERMKEY = 'receive_area' "
         . "  ) ";
    $data = $owner->sql_query($sql);
    if($debug) echo '<h4>Users</h4><ul>';
    if($data){
      while($row = jux_sql_assoc($data)){
        $users[$row['user_id']]=array('id'=>$row['user_id'],'name'=>$row['username']);
        if($debug) echo '<li>'.$row['user_id'].' '.$row['username'].'</li>';
      }
    }
    if($debug) echo '</ul>';
    return $users;
  }


SELECT user_id  ,  CONCAT(user_name,' -  ',user_fullname) AS username FROM {$prefix}_user  WHERE user_id IN( 

                                 //, AA.ID_AREA, AA.ID_APP, AAP.ID , AP.ID, AP.PERMKEY 
           SELECT APUS.ID_USER 
           FROM CFG_AREAS_APPS_USERS_PERMS APUS ,  
                     CFG_AREAS_APPS AA,  
                     CFG_AREAS_APPS_PERMS AAP, 
                     CFG_APPS_PERMS AP    
           WHERE AA.ID = APUS.ID_AREA_APP 
           AND APUS.ID_AREA_APP_PERM = AAP.ID  
           AND AAP.ID_APP_PERM=AP.ID  
           AND AA.ID_AREA IN ( 
              SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER=  1
              AND AA.ID_APP = 1  
              AND AP.PERMKEY = 'receive' 
           ) 



