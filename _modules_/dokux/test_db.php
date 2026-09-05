<?php



 // function getPerm($owner,$perm) {
// get perms for user in app in area
$perm = 'doku_add';
$user_id=5;

/*********
      $sql = 'SELECT AAUP.ID , '
           . '       AAU.ID_USER , '
           . '       AAUP.ID_AREA_APP_USER , '
           . '       AAUP.ID_AREA_APP_PERM , '            //   SQLSTATE[42S22]: Column not found: 1054 Unknown column 'AAUP.ID_USER' in 'field list'
           . '       AP.PERMKEY '
           . 'FROM CFG_AREAS_APPS_USERS_PERMS AAUP, '
           . '     CFG_AREAS_APPS_USERS AAU, '
           . '     CFG_AREAS_APPS_PERMS AAP, '
           . '     CFG_APPS_PERMS AP,'                // SQLSTATE[42S22]: Column not found: 1054 Unknown column 'AAUP.ID_AREA_APP' in 'where clause'
           . '     CFG_AREAS_APPS AA '
           . 'WHERE AAP.ID=AAUP.ID_AREA_APP_PERM '
           . '  AND AP.ID=AAP.ID_APP_PERM '
           . "  AND AP.PERMKEY = '{$perm}'"

           . '  AND AAU.ID_AREA_APP = AAUP.ID_AREA_APP_PERM  '
           . '  AND AAU.ID_USER = '.$user_id
          
           . '  AND AAUP.ID_AREA_APP_USER = AAU.ID_USER '
           . '  AND AAUP.ID_AREA_APP_PERM = AAU.ID_AREA_APP '

           . '  AND AA.ID_APP = '.$app
            ;  // Notificaciones = 1
//}
*******/
 //$this->getPerm($owner,'add')

/**************/
/**
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
*/
$admin = false;
$whereadmin = ($admin)  ? ' AND ADMIN=1 ' : '' ; 

    // get area users  (OK)
    $sql = 'SELECT user_id  ,  CONCAT(username,\' -  \',user_fullname) AS username '
         . ' FROM '.TB_USER
         . ' WHERE user_id IN ('
         . '   SELECT ID_USER '
         . '   FROM CFG_AREAS_USERS '
         . '   WHERE ID_AREA = 1) ';     // 1.archivo

    // get users from group and area (OK)
    // (1 Archivo)     (1 Opers 2 Admin)
    // (2 Informática) (3 Desarrollo)
    
    $area  = 2; 
    $group = 2;
 
    $sql = 'SELECT user_id  ,  CONCAT(username,\' -  \',user_fullname) AS username '
         . ' FROM '.TB_USER
         . ' WHERE user_id IN ('
         . '   SELECT ID_USER '
         . '   FROM CFG_AREAS_GROUPS_USERS, CFG_AREAS_GROUPS'
         . '   WHERE ID_AREA_GROUP = '.$group.' 
                 AND CFG_AREAS_GROUPS_USERS.ID_AREA_GROUP = CFG_AREAS_GROUPS.ID
                 AND CFG_AREAS_GROUPS.ID_AREA = '.$area.' )';




$parent=1;
$app=3;         // Hulamm_ware
//get user from area_user andd area_app
//SELECT user_id,username,user_fullname FROM CLI_USER WHERE user_id IN (SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA=1)
  $sql =   'SELECT user_id AS ID,CONCAT(username,\' -  \',user_fullname) AS NAME 
                               FROM CLI_USER WHERE user_id IN (
                                   SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA IN (
                                       SELECT ID_AREA 
                                       FROM CFG_AREAS_APPS 
                                       WHERE ID_APP = '.$app.'
                                       AND ACTIVE = \'1\'
                                   )
                               )' ; 

$sql = 'SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA IN (
                                       SELECT ID_AREA 
                                       FROM CFG_AREAS_APPS 
                                       WHERE ID_APP = '.$app.'
                                       AND ACTIVE = \'1\')';

// areas with an app
$sql = ' SELECT ID_AREA FROM CFG_AREAS_APPS WHERE ID_APP = '.$app;


//  get perms for current app and current user
// 1. select app and perms from current app
// 2. select groups (and areas) for current user
// 2.1 (if not groups)  select areas for current user
// 3. select groups for current app and area  (??)
// 3. select area for current app  (??)

//$sql = 'SELECT ID_USER FROM CFG_AREAS_GROUPS_USERS WHERE ID_AREA_GROUP = 1 ';
//$sql = 'SELECT ID_USER FROM CFG_AREAS_GROUPS_USERS WHERE ID_AREA_GROUP = 2 ';

    // 1.archivo
           

/*
         . '   WHERE ID_AREA IN ( '
         . '       SELECT ID '
         . '       FROM CFG_AREAS '
         . '       WHERE ID IN ( SELECT ID_AREA '
         . '                     FROM CFG_AREAS_USERS ' 
         . '                     WHERE ID_USER = '.$_SESSION['userid'].')) '.$whereadmin.'  )';
*/

//  private function getAreaUsersWithPerm($owner,$perm='receive_area',$debug=false) {  //COPY
/*
    $sql = "SELECT user_id  ,  CONCAT(username,' -  ',user_fullname) AS username FROM ".TB_USER."  WHERE user_id IN( "
         . "  SELECT APUS.ID_USER " //, AA.ID_AREA, AA.ID_APP, AAP.ID , AP.ID, AP.PERMKEY "
         . "  FROM CFG_AREAS_APPS_USERS_PERMS APUS ,  "
         . "            CFG_AREAS_APPS AA,  "
         . "            CFG_AREAS_APPS_PERMS AAP, "
         . "            CFG_APPS_PERMS AP    "
         . "  WHERE AA.ID = APUS.ID_AREA_APP  "
         . "  AND APUS.ID_AREA_APP_PERM = AAP.ID  "
         . "  AND AAP.ID_APP_PERM=AP.ID  "
         . "  AND AA.ID_AREA IN ( SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER={$_SESSION['userid']})   "
         . "  AND AA.ID_APP = 3  "
         . "  AND AP.PERMKEY = 'doku_add' "
         . "  ) ";
*/


function sql($sql,$debug=false){
    $lines = explode("\n", $sql);
    $exclude = array();
    foreach ($lines as $line) {
        if (strpos($line, '--') !== FALSE || trim($line)=='') { continue;  }
        $exclude[] = $line;
    }
    $sql = implode("\n", $exclude);
    if($debug) Vars::debug_var($sql);
    return Table::sqlQuery($sql);
}


$sql = "
       SELECT AAP.ID, AP.NAME , AP.ID_APP
        FROM CFG_AREAS_APPS_PERMS AAP, 
             CFG_APPS_PERMS AP 
        WHERE AAP.ID_APP_PERM = AP.ID 
        AND AP.ID_APP = 3        -- ¡cual es la app?
        AND AAP.ID_AREA_APP = 1  -- es el parent_value

  --        AND AAP.ID_AREA_APP = (  SELECT ID_AREA_APP 
--                                     FROM CFG_AREAS_APPS_GROUPS 
           --                         WHERE ID_APP =5 )

-- tenemos el ID de CFG_AREAS_APPS_GOUPS
";
Vars::debug_var(sql($sql,true));




/**
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


 $this->getPerm($owner,'send')




//print_r( $_ACL->getUsersWithPermissionName('messages_receive') );
                                 else  $this->getAreaUsersWithPerm($owner,'receive_area', true);
 $recipients = $this->getAreaUsersWithPerm($owner,'receive_area');
                             //$recipients = $this->getAreaUsers($owner,true);

        foreach  ($recipients as $recipient){ 


//  echo 'test!';

*/