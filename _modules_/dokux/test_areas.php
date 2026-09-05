<style>
hr{border-top:1px solid #666;}
</style>

<div class="inner">
<div style="display:inline-table;padding:5px; margin-right:5px;width:400px;border:1px solid red;">
<?php

   //$ret = ACL::getAreasColumn();
   //Vars::debug_var($ret,'getAreasColumn');

    $ret = ACL::getAppsInArea('fac');
    Vars::debug_var(array_column($ret,'APPKEY'),'getAppsInArea(fac)');
    $ret = ACL::getAppsInArea('informatica');
    Vars::debug_var(array_column($ret,'APPKEY'),'getAppsInArea(informatica)');


    echo '<hr>';

    $ret = ACL::getUsersInArea('fac');
    Vars::debug_var(array_column($ret,'NAME'),'getUsersInArea(fac)');

    $ret = ACL::getAdminsInArea('fac');
  //Vars::debug_var(array_column($ret,'ID'),'getAdminsInArea');
  //Vars::debug_var(array_column($ret,'USER'),'getAdminsInArea');
    Vars::debug_var(array_column($ret,'NAME'),'getAdminsInArea(fac)');

    echo '<hr>';

    $ret = ACL::getUsersInAreaAndGroup('informatica','dev');
    Vars::debug_var(array_column($ret,'NAME'),'getUsersInAreaAndGroup(informatica,dev)');
    $ret = ACL::getUsersInAreaAndGroup('informatica','sistemas');
    Vars::debug_var(array_column($ret,'NAME'),'getUsersInAreaAndGroup(informatica,sistemas)');
    $ret = ACL::getUsersInAreaAndGroup('fac','admins');
    Vars::debug_var(array_column($ret,'NAME'),'getUsersInAreaAndGroup(fac,admins)');
    $ret = ACL::getUsersInAreaAndGroup('fac','opers');
    Vars::debug_var(array_column($ret,'NAME'),'getUsersInAreaAndGroup(fac,opers)');



    //echo '<hr>';
    //Vars::debug_var(ACL::$groups,'groups');
    //echo '<pre>';
    //print_r(ACL::$groups);
    //echo '</pre>';

?>
</div>

<div style="display:inline-table;padding:5px; width:400px;border:1px solid red;">
<?php

    Vars::debug_var($_SESSION['username'],'username');  // ID, NAME, AREAKEY
    Vars::debug_var(MODULE,'module');  // ID, NAME, AREAKEY

    echo '<hr>';

    $ret = ACL::getAreasUser();
  //Vars::debug_var($ret,'getAreasUser');  // ID, NAME, AREAKEY
    Vars::debug_var(array_column($ret,'AREAKEY'),'getAreasUser');  // ID, NAME, AREAKEY


  //$ret = ACL::getAreasAppsUser();
  //Vars::debug_var($ret,'getAreasAppsUser()');

    echo '<hr>';

    $ret = ACL::getAppsInArea('informatica',true);
    Vars::debug_var(array_column($ret,'APPKEY'),'getAppsInArea(informatica)');

    $ret = ACL::getAppsInArea('fac',true);
    Vars::debug_var(array_column($ret,'APPKEY'),'getAppsInArea(fac)');

    $ret = ACL::getAppsInArea('archivo',true);
    Vars::debug_var(array_column($ret,'APPKEY'),'getAppsInArea(archivo)');

    echo '<hr>';

    $ret = ACL::adminInArea('fac');
    Vars::debug_var($ret,'adminInArea(fac)');
    $ret = ACL::adminInArea('informatica');
    Vars::debug_var($ret,'adminInArea(informatica)');

    echo '<hr>';

    $ret = ACL::userInArea('fac');
    Vars::debug_var($ret,'userInArea fac');
    $ret = ACL::userInArea('informatica');
    Vars::debug_var($ret,'userInArea informatica');


    echo '<hr>';

    $ret = ACL::userInAreaAndGroup('informatica','dev');
    Vars::debug_var($ret,'userInAreaAndGroup(informatica,dev)');

    $ret = ACL::userInAreaAndGroup('informatica','sistemas');
    Vars::debug_var($ret,'userInAreaAndGroup(informatica,sistemas)');

    $ret = ACL::userInAreaAndGroup('fac','admins');
    Vars::debug_var($ret,'userInAreaAndGroup(fac,dev)');

    $ret = ACL::userInAreaAndGroup('fac','opers');
    Vars::debug_var($ret,'userInAreaAndGroup(fac,dev)');


    echo '<hr>';

    Vars::debug_var('NULL','getUserApps(app)');
    Vars::debug_var('NULL','getUserPermsInApp(app)');
 
?>
</div>
</div>



<pre>
SELECT AAGP.ID, AAGP.ID_AREA_APP_GROUP, AAGP.ID_AREA_APP_PERM,
       AAP.ID, AAP.ID_AREA_APP, AAP.ID_APP_PERM,
       AA.ID, AA.ID_AREA, AA.ID_APP,
       A.ID, A.AREAKEY,
       P.ID,P.NAME,P.APPKEY
  FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP, 
       CFG_AREAS_APPS_PERMS AAP,
       CFG_AREAS_APPS AA,
       CFG_AREAS A,
       CFG_APPS P
 WHERE AAGP.ID_AREA_APP_GROUP = AAP.ID
   AND AAP.ID_AREA_APP = AA.ID    
   AND AA.ID_AREA = A.ID  AND A.AREAKEY = 'fac'
   AND AA.ID_APP = P.ID   AND P.APPKEY  = 'hulamm_ware'



SELECT AAGP.ID, AAGP.ID_AREA_APP_GROUP, AAGP.ID_AREA_APP_PERM
 FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP
 WHERE AAGP.ID_AREA_APP_GROUP IN (
    SELECT AAG.ID
       FROM CFG_AREAS_APPS_GROUPS AAG WHERE AAG.ID_AREA_APP IN (
          SELECT ID FROM CFG_AREAS_APPS AA WHERE AA.ID_AREA IN ( 
             SELECT A.ID FROM CFG_AREAS A WHERE A.AREAKEY='fac'
          )
       )
    )  

SELECT AAGP.ID, AAGP.ID_AREA_APP_GROUP, AAGP.ID_AREA_APP_PERM
 FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP
 WHERE AAGP.ID_AREA_APP_GROUP IN (
    SELECT AAG.ID
       FROM CFG_AREAS_APPS_GROUPS AAG,CFG_AREAS_APPS_PERMS AAP 
       WHERE AAG.ID_AREA_APP IN ( SELECT ID FROM CFG_AREAS_APPS AA WHERE AA.ID_AREA IN ( SELECT A.ID FROM CFG_AREAS A WHERE A.AREAKEY='fac' )   )
       AND AAG.ID_AREA_APP = AAP.ID_AREA_APP
    ) 
    AND AAGP.ID_AREA_APP_GROUP = 7


SELECT AAGP.ID, AAGP.ID_AREA_APP_GROUP, AAGP.ID_AREA_APP_PERM,AP.NAME
 FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP,CFG_APPS_PERMS AP
 WHERE AAGP.ID_AREA_APP_GROUP IN (
    SELECT AAG.ID
       FROM CFG_AREAS_APPS_GROUPS AAG,CFG_AREAS_APPS_PERMS AAP 
       WHERE AAG.ID_AREA_APP IN ( 
          SELECT ID FROM CFG_AREAS_APPS AA 
           WHERE AA.ID_AREA IN ( SELECT A.ID FROM CFG_AREAS A WHERE A.AREAKEY='fac' )   
             AND AA.ID_APP IN  ( SELECT P.ID FROM CFG_APPS P WHERE P.APPKEY='hulamm_ware' )  
       )
       AND AAG.ID_AREA_APP = AAP.ID_AREA_APP
    ) 
    AND AAGP.ID_AREA_APP_GROUP = 7
    AND AAGP.ID_AREA_APP_PERM = 12
</pre>
<p>
https://getemoji.com/<br>
📰 News<br>
📬 Newsletter<br>
🧩 Quizzs<br>
🎒 Resources<br>
🏘️ Community<br>
📖 About<br>
🪪 Contact<br>
📜 Policies<br>
🗳️ Feedback<br>
Courses 🎓<br>
Distro Resources 📖<br>
Guides 📒
</p>