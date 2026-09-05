<?php

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

/*
        $text = Table::getFieldsValues('SELECT ID,PACIENTE_NAME,NOTES FROM DOKU_FILES WHERE ID = 15');

        $ajax_result['msg'] = print_r($_ARGS,true);
        $ajax_result['text'] = print_r($text['NOTES'],true);
        echo json_encode($ajax_result);
*/

/**
$username = 'alm88rd';
    $users = Table::sqlQuery(    'SELECT user_id AS ID,username AS USER,user_fullname AS NAME '
                               . 'FROM CLI_USER '
                               . 'WHERE user_id IN ('
                               . '    SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA=1'
                               . ')'
                             );
 
Vars::debug_var(array_column($users,'USER'));

if (in_array($username, array_column($users,'USER'))) {
    echo $username.' OKIS!!';
}else{
    echo $username.' NORL :(';
}
*/

    // is_oper
    // is_admin
    // hasGroup (userName, groupName)
    // hasPerm (userName, permName )
    // getPerm for user
    //  OR
    // getGroups for user


$area = 'archivo';  // archivo: 1
$group ='opers';   // admins:  2  opers:1
$app = 'doku';      // doku:    3 (Hulammware)

echo '<h5>Área: <b>'.$area.'</b></h5>';
echo '<h5>Grupo: <b>'.$group.'</b></h5>';

// users in group
$sql =   'SELECT user_id AS ID,username AS USER,user_fullname AS NAME 
          FROM CLI_USER 
          WHERE user_id IN (
             SELECT ID_USER 
             FROM CFG_AREAS_GROUPS_USERS, CFG_AREAS_GROUPS
             WHERE ID_AREA_GROUP = (SELECT ID FROM CFG_AREAS_GROUPS WHERE GROUPKEY =\''.$group.'\' AND ACTIVE=1)
                  AND CFG_AREAS_GROUPS_USERS.ID_AREA_GROUP = CFG_AREAS_GROUPS.ID
                  AND CFG_AREAS_GROUPS_USERS.ACTIVE = 1
                  AND CFG_AREAS_GROUPS.ID_AREA = (SELECT ID FROM CFG_AREAS WHERE AREAKEY=\''.$area.'\')
          )';
Vars::debug_var(sql($sql,true));

echo '<h5>Permisos para el grupo <b>'.$group.'</b></h5>';
// perms for users in group
$sql =   "  SELECT AP.ID,AP.ID_APP,AP.NAME,AP.PERMKEY,AP.ACTIVE
              FROM CFG_APPS_PERMS AP
             WHERE (AP.ID_APP IN (SELECT AA.ID_APP 
                                    FROM CFG_AREAS_APPS AA 
                                   WHERE AA.ID_APP  = (SELECT ID FROM CFG_APPS WHERE APPKEY='".$app."') 
                                     AND AA.ID_AREA = (SELECT ID FROM CFG_AREAS WHERE AREAKEY='".$area."')
                                 )
                    AND AP.ID_APP = (SELECT ID FROM CFG_APPS WHERE APPKEY='".$app."') 
                  ) 
              AND AP.ID IN ( SELECT AAP.ID_APP_PERM FROM CFG_AREAS_APPS_PERMS AAP WHERE AAP.ACTIVE=1) 
        --      AND AP.ID = 8
";
 

//$sql = "SELECT * FROM

// perms for group for app for area
$sql0 = "SELECT AAGP.ID,AAGP.ID_AREA_APP_GROUP,AAGP.ID_AREA_APP_PERM
               -- ,AAG.ID
         FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP,
              CFG_AREAS_APPS_GROUPS AAG
              --,CFG_APPS_PERMS AP
              --,CFG_AREAS_APPS AA
        WHERE AAGP.ID_AREA_APP_GROUP = (SELECT ID 
                                   FROM CFG_AREAS_APPS_GROUPS
                                   WHERE ID_AREA_APP = (SELECT ID 
                                                          FROM CFG_AREAS_APPS
                                                          WHERE ID_APP = (SELECT ID FROM CFG_APPS WHERE APPKEY='".$app."')
                                                          AND ID_AREA  = (SELECT ID FROM CFG_AREAS WHERE AREAKEY='".$area."')
                                                       ) 
                                   AND ID_GROUP =  (SELECT ID FROM CFG_AREAS_GROUPS WHERE GROUPKEY ='".$group."' AND ACTIVE=1))
        --AND AAG.ID=AAGP.ID_AREA_APP_GROUP
";

/*
SELECT AAP.ID, AP.NAME 
                 FROM CFG_AREAS_APPS_PERMS AAP, 
                      CFG_APPS_PERMS AP 
                WHERE AAP.ID_APP_PERM = AP.ID
                  AND AAP.ID_AREA_APP = (SELECT ID_AREA_APP FROM CFG_AREAS_APPS_GROUPS WHERE ID ='.$owner->parent_value.' )';

*/

/*
     SELECT ID, ID_AREA_APP_GROUP ,ID_AREA_APP_PERM  //PERMS FOR ADMIN
          FROM CFG_AREAS_APPS_GROUPS_PERMS
           WHERE ID_AREA_APP_GROUP = 2';
*/


/*(
             SELECT ID FROM CFG_AREAS_GROUPS 
                      WHERE GROUPKEY =\''.$group.'\' 
                       AND ID_AREA_APP = (SELECT ID FROM CFG_AREAS_APPS WHERE ID_AREA=1)
      )';

*/



$sql = "SELECT ID,NAME
           FROM CFG_AREAS WHERE
           ID IN (SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER=".$_SESSION['userid'].")
";

//Vars::debug_var(sql($sql,true));


/**
$myareas = ACL::getAreasUser();
//$myareas = ACL::getAreasColumn();
//Vars::debug_var(sql($sql,true));
Vars::debug_var($myareas);
*/
/*
if(count($myareas)==1){
    
   Vars::debug_var('area: '.$myareas[0]);

}else{
  
   Vars::debug_var($myareas);

  
}
*/

   Vars::debug_var($myareas);
