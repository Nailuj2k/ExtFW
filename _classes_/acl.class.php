<?php 

if       (CFG::$vars['db']['type']=='mysql') {
    class BaseACL {
        use MysqlConnection;   
    }
}else if (CFG::$vars['db']['type']=='sqlite') {
    class BaseACL {
        use SQLiteConnection;   
    }
}

class ACL extends BaseACL { 
   
    // use MysqlConnection;   

    var $perms = array();      //Array : Stores the permissions for the user
    var $userID = 0;           //Integer : Stores the ID of the current user
    var $itemID = 0;           //Integer : Stores the ID of the current item
    var $userRoles = array();  //Array : Stores the roles of the current user
    var $itemRoles = array();  //Array : Stores the roles of the current user
    var $rolePerms = array();
    var $allUserRoles = array();

    private static $session = false; //array();
    private static $groups  = array();
    private static $areas   = array();
    public static $cache = true;
    public static $debug;

    function __construct($userID = false, $itemID=false){
      self::connect();
      if(self::$cache === true) self::createCache();

      if ($userID) $this->userID = $userID;
              else $this->userID = $_SESSION['userid']??false;
      if ($itemID) $this->itemID = $itemID;
              else $this->itemID = $_SESSION['itemid']??false;  //FIX
            //else $this->itemID = $_SESSION['filaitem']['item_id']?$_SESSION['filaitem']['item_id']:false;  //FIX

      if($this->userID) $this->userRoles =  $this->getUserRoles('ids');
      if($this->itemID) $this->itemRoles =  $this->getItemRoles();

      $this->buildACL();
    }

    public static function createCache(){
        if (self::$session === false) { 
            self::$session = [];  // Inicializamos self::$session como un array si es false
        }

        $data = self::sqlQuery('SELECT role_id,role_name,filtrable,role_type FROM '.TB_ACL_ROLES.' ORDER BY role_id');
        foreach ($data as $row) {
            self::$session['roles'][$row['role_id']] = $row;
        }
        $data = self::sqlQuery('SELECT permission_id,permission_key,permission_name FROM '.TB_ACL_PERMISSIONS.' ORDER BY permission_id');
        foreach($data as $row){self::$session['permissions'][$row['permission_id']] = $row;} //array( 'name' => $row['permission_name'], 'key' => $row['permission_key']);
        self::$session['user_roles'] = self::sqlQuery('SELECT id_user,id_role FROM '.TB_ACL_USER_ROLES); 
        self::$session['user_perms'] = self::sqlQuery('SELECT user_perm_id,id_user,id_permission,user_perm_value FROM '.TB_ACL_USER_PERMS);
        self::$session['item_roles'] = self::sqlQuery('SELECT id_item,id_role FROM '.TB_ACL_ITEM_ROLES);  
        self::$session['role_perms'] = self::sqlQuery('SELECT role_perm_id,id_role,id_permission,role_perm_value FROM '.TB_ACL_ROLE_PERMS);  
    }

    function buildACL()  {

      if($this->userID>0||$this->itemID>0){
          // $this->rolePerms    = self::$cache === true ? self::$session['role_perms'] : self::sqlQuery('SELECT role_perm_id,id_role,id_permission,role_perm_value FROM '.TB_ACL_ROLE_PERMS);
          // $this->allUserRoles = self::$cache === true ? self::$session['user_roles'] : self::sqlQuery('SELECT id_user,id_role FROM '.TB_ACL_USER_ROLES); 
      }
      //first, get the rules for the user's role
      if (count($this->userRoles) > 0){
        $this->perms = array_merge($this->perms,$this->getRolePerms($this->userRoles));
      }
      //then, get the individual user permissions
      if($this->userID)
        $this->perms = array_merge($this->perms,$this->getUserPerms($this->userID));
    }

    function getPermIdFromKey($permKey){  //COPY
        if(self::$cache === true && self::$session['permissions']) {
            foreach( self::$session['permissions'] as $k => $v){
              if ($v['permission_key'] === $permKey){ 
                return $k;
              }
            }
        }else{
            $strSQL = "SELECT permission_id FROM ".TB_ACL_PERMISSIONS." WHERE permission_key = '" . $permKey . "' LIMIT 1";
            return self::getFieldValue($strSQL);
        }
    }
    
    function getPermKeyFromID($permID){
        if(self::$cache === true && self::$session['permissions']) {
            return self::$session['permissions'][$permID]['permission_key'];
        }else{
            $strSQL = "SELECT permission_key FROM ".TB_ACL_PERMISSIONS." WHERE permission_id = " . $permID . " LIMIT 1";
            return self::getFieldValue($strSQL);
        }
    }
    
    function getPermNameFromID($permID){
        if(self::$cache === true && self::$session['permissions']) {
            return self::$session['permissions'][$permID]['permission_name'];
        }else{
            $strSQL = "SELECT permission_name FROM ".TB_ACL_PERMISSIONS." WHERE permission_id = " . $permID . " LIMIT 1";
            return self::getFieldValue($strSQL);
        }
    }
    
    function getRoleNameFromID($roleID){
        if(self::$cache === true && self::$session['roles']) {
            return self::$session['roles'][$roleID]['role_name'];
        }else{
            $strSQL = "SELECT role_name FROM ".TB_ACL_ROLES." WHERE role_id = " . $roleID . " LIMIT 1";
            return self::getFieldValue($strSQL);
        }
    }
    
    public function getRoleIdFromName($roleName){
        if(self::$cache === true && self::$session['roles']) {
            foreach( self::$session['roles'] as $k => $v){
                if ($v['role_name'] === $roleName){ //FIX ===
                    return $k;
                }
            }
        }else{
            $strSQL = "SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_name = '" . $roleName . "' LIMIT 1";
            return self::getFieldValue($strSQL);
        }
    }
    
    function getUserRoles($format='ids',$mask=''){
        $resp = array();
        //CFGif(self::$cache === true && self::$session['user_roles']){
        //CFG    foreach(self::$session['user_roles'] as $k => $v){
        //CFG        //FIX if role_name LIKE mask
        //CFG        if ($v['id_user'] == $this->userID){  //FIX ===
        //CFG            if ($format == 'full'){
        //CFG              $v['role_name'] = $this->getRoleNameFromID($v['id_role']);
        //CFG              $resp[$v['id_role']] = $v;
        //CFG            } else {
        //CFG              $resp[] = $v['id_role'];
        //CFG            }
        //CFG        }
        //CFG    }
        //CFG}else{
            $where = $mask != '' ? " AND id_role IN (SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_name LIKE '{$mask}')":'';
            $strSQL = "SELECT * FROM ".TB_ACL_USER_ROLES." WHERE id_user = " . $this->userID . $where . " ORDER BY user_role_add_date ASC";
            //print_r($strSQL); 
            $rows = self::sqlQuery($strSQL);
            foreach($rows as $row){      
                //$resp[] = $row['id_role'];
                if ($format == 'full'){
                  $row['role_name'] = $this->getRoleNameFromID($row['id_role']);
                  $resp[$row['id_role']] = $row; //array("ID" => $row['role_id'],"Name" => $row['role_name']);
                } else {
                  $resp[] = $row['id_role'];
                }
            }
        //CFG}
        return $resp;
    }

    function getItemRoles($itemID=false){
        $itemID = $itemID ? $itemID : ($this->itemID?$this->itemID:false);
        if(!$itemID) return false;
        $resp = array();
        if(self::$cache === true && self::$session['item_roles']){
            foreach(self::$session['item_roles'] as $k => $v){
              if ($v['id_item'] === $itemID){ //FIX ===
                  $resp[] = $v['id_role'];
              }
            }
        }else{
            $strSQL = "SELECT id_role FROM ".TB_ACL_ITEM_ROLES." WHERE id_item = " . $itemID . " ORDER BY item_role_add_date ASC";
            $rows = self::sqlQuery($strSQL);
            foreach($rows as $row){      
               $resp[] = $row['id_role'];
            }
        }
        return $resp;
    }

    function getAllRoles($format='ids')  {
        $resp = array();          
        if(self::$cache === true && self::$session['roles']){
            foreach(self::$session['roles'] as $k => $v){      
                if ($format == 'full'){
                    $resp[] = array('ID' => $v['role_id'], 'Name' => $v['role_name']); //, 'Cmd' => $row['permission_cmd']);   ///////////////////////////////////////////////////
                } else {
                    $resp[] = $v['role_id'];
                }
            }
       }else{
            $strSQL = "SELECT * FROM ".TB_ACL_ROLES;
            if($_SESSION['org_id']) $strSQL .= "WHERE org_id = ".$_SESSION['org_id'];
            $strSQL .= " ORDER BY role_name ASC";
            $rows = self::sqlQuery($strSQL);       //session roles
            foreach($rows as $row){      
                if ($format == 'full'){
                    $resp[] = array("ID" => $row['role_id'],"Name" => $row['role_name']);
                } else {
                    $resp[] = $row['role_id'];
                }
            }
        }
        return $resp;
    }
    
    function getAllPerms($format='ids'){
        $resp = array();
        if(self::$cache === true && self::$session['permissions']){
            foreach(self::$session['permissions'] as $k => $v){      
                if ($format == 'full'){
                    $resp[ $k ] = array('ID' => $v['permission_id'], 'Name' => $v['permission_name'], 'Key' => $v['permission_key']); //, 'Cmd' => $row['permission_cmd']);
                } else {
                    $resp[] = $v['permission_id'];
                }
            }
        }else{
            $strSQL = 'SELECT * FROM '.TB_ACL_PERMISSIONS.' ORDER BY permission_name ASC';
            $rows = self::sqlQuery($strSQL);
            foreach($rows as $row){      
                if ($format == 'full'){
                    $resp[$row['permission_key']] = array('ID' => $row['permission_id'], 'Name' => $row['permission_name'], 'Key' => $row['permission_key']); //, 'Cmd' => $row['permission_cmd']);
                } else {
                    $resp[] = $row['permission_id'];
                }
            }
        }
        return $resp;
    }
    
    function getRolePerms($role){
      /*
      It would actually be more sensible for an explicit ‘deny’ 
      permission in any role to overrule an ‘allow’ permission in any other. To do this with the existing code is
      very easy; in the function getRolePerms() change the first few lines to:

      if (is_array($role)){
        $roleSQL = "SELECT * FROM ".TB_ACL_ROLE_PERMS." WHERE id_role IN (" . implode(",",$role) . ") ORDER BY user_perm_value DESC";
      } else {
        $roleSQL = "SELECT * FROM ".TB_ACL_ROLE_PERMS." WHERE id_role = " . $role . " ORDER BY user_perm_value DESC";
      }     

      What I’m doing here is simply ordering the role permissions by ‘allowed’ permissions > ‘denied’ permissions. 
      The array_merge function will then favour the ‘deny’ permissions over the ‘allow’ permissions with the same key.
      If you want the opposite behaviour (an ‘allow’ permission in any role overrides a ‘deny’ permission) then change 
      the DESC to ASC.
      */
    
      if (is_array($role)){
        $roleSQL = "SELECT role_perm_id,id_role,id_permission,role_perm_value FROM ".TB_ACL_ROLE_PERMS." WHERE id_role IN (" . implode(",",$role) . ") ORDER BY role_perm_id ASC";
      } else {
        $roleSQL = "SELECT role_perm_id,id_role,id_permission,role_perm_value FROM ".TB_ACL_ROLE_PERMS." WHERE id_role = " . $role . " ORDER BY role_perm_id ASC";
      }
      //print_r($roleSQL);
      $rows = self::sqlQuery($roleSQL);
      $perms = array();
      foreach($rows as $row){      
        $pK = $this->getPermKeyFromID($row['id_permission']); //strtolower($this->getPermKeyFromID($row['id_permission']));
        if ($pK == '') { continue; }
        if ($row['role_perm_value'] == 1) {
          $hP = true;
        } else {
          $hP = false;
        }
        $perms[$pK] = array('perm' => $pK,'inherited' => true,'value' => $hP,'Name' => $this->getPermNameFromID($row['id_permission']),'ID' => $row['id_permission']);
      }
      return $perms;
    }
    
    function getUserPerms($userID){
        $perms = array();
        $strSQL = "SELECT * FROM ".TB_ACL_USER_PERMS." WHERE id_user = " . $userID . " ORDER BY user_perm_add_date ASC";
        $rows = self::sqlQuery($strSQL);
        foreach($rows as $row){      
          $pK = $this->getPermKeyFromID($row['id_permission']); //strtolower($this->getPermKeyFromID($row['id_permission']));
          if ($pK == '') { continue; }
          if ($row['user_perm_value'] == 1) {
            $hP = true;
          } else {
            $hP = false;
          }
          $perms[$pK] = array('perm' => $pK,'inherited' => false,'value' => $hP,'Name' => $this->getPermNameFromID($row['id_permission']),'ID' => $row['id_permission']);
        }
        return $perms;
    }
    
    function userRoles2SqlArray($extra=false){
      $r = $this->userRoles;
      if(isset($extra)) $r[]=$extra;
      return implode(",", $r);
    }

    function userHasRole($roleID){
      foreach($this->userRoles as $k => $v){
        //echo "**Role: $roleID = $v\n";
        if ($v == $roleID){ //FIX ===
          return true;
        }
      }
      return false;
    }
    
    function userHasRoleName($roleName){
      $roleID = $this->getRoleIdFromName($roleName);
      return ($this->userHasRole($roleID)===true);
    }

    function itemHasRole($roleID){
      foreach($this->itemRoles as $k => $v){
        if ($v === $roleID){ //FIX ===
          return true;
        }
      }
      return false;
    }
    
    function itemRoleInUserRole($debug=false){
      $result = false;
      if($debug)  echo "<pre>-------\n";
      foreach($this->userRoles as $ur){
        if($debug) echo "ur: $ur\n"; 
        foreach($this->itemRoles as $ir){
          if($debug)  echo  "[$ur]  -- [$ir]\n";
          if ($ir==$ur) {
            if ($debug) echo "FOUND !!!";
            $result = true;
            continue;
          }
        }
        if ($result) continue;
      }
      if($debug) echo "-------\n</pre>"; 
      return $result;
    }
    
    function hasPermission($permKey){
      if (array_key_exists($permKey,$this->perms)){
        //Vars::debug_var($this->perms[$permKey],$permKey);
        if ($this->perms[$permKey]['value'] == '1' || $this->perms[$permKey]['value'] === true){
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    function getUsername($userID=false,$full=false){
      if(!$userID) { $userID=$this->userID; }
      //return self::getUser($userID,$full);
      $strSQL = "SELECT username,user_fullname,user_email FROM ".TB_USER." WHERE user_id = " . $userID . " LIMIT 1";
      $data = self::sqlQuery($strSQL);
      return $data ? ($full?$data[0]:$data[0]['username']) : false;
    }

    function getUserId($username){
      $strSQL = "SELECT user_id FROM ".TB_USER." WHERE username = '" . $username . "' LIMIT 1";
      $data = self::sqlQuery($strSQL);
      return $data ? $data[0]['user_id'] : false;
    }

    function getUserFullname($userID=false){
      if(!$userID) { $userID=$this->userID; }
      $strSQL = "SELECT username,user_fullname FROM ".TB_USER." WHERE user_id = " . $userID . " LIMIT 1";
      $data = self::sqlQuery($strSQL);
      if($data){ 
          $row=$data[0];
          return (trim($row['user_fullname'])!='') ? $row['user_fullname'] : $row['username'] ;
      }else return false;
    }

    function getItemname($itemID){
      $strSQL = "SELECT item_name FROM ".TB_ITEM." WHERE item_id = " . $itemID . " LIMIT 1";
      $data = self::sqlQuery($strSQL);
      return $data ? $data[0]['item_id'] : false;
    }

    function getItemFullname($itemID){
      $strSQL = "SELECT item_name,item_title FROM ".TB_ITEM." WHERE item_id = " . $itemID . " LIMIT 1";
      $data = self::sqlQuery($strSQL);
      if($data){ 
          $row = $data[0];
          return ($row['item_title']) ? $row['item_title'] : $row['item_name'] ;
      }else return false;
    }


    function getRolesWithPermissionName($permission_name) { 

      if(count($this->rolePerms)<1) $this->rolePerms = self::$cache === true ? self::$session['role_perms'] : self::sqlQuery('SELECT role_perm_id,id_role,id_permission,role_perm_value FROM '.TB_ACL_ROLE_PERMS);

      $roles = array();
      $pi = $this->getPermIdFromKey($permission_name);
      foreach($this->rolePerms as $k => $v){
        if ($v['id_permission'] == $pi){ // FIX ===
          if ( !isset($roles[$v['id_role']]) ) $roles[$v['id_role']]=$this->getRoleNameFromID($v['id_role']);       
        }
      }
      return $roles;
    }

    function getUsersWithPermissionName($permission_name,$full=false) {  

      if(count($this->rolePerms)<1)     $this->rolePerms    = self::$cache === true ? self::$session['role_perms'] : self::sqlQuery('SELECT role_perm_id,id_role,id_permission,role_perm_value FROM '.TB_ACL_ROLE_PERMS);
      if(count($this->allUserRoles)<1)  $this->allUserRoles = self::$cache === true ? self::$session['user_roles'] : self::sqlQuery('SELECT id_user,id_role FROM '.TB_ACL_USER_ROLES); 
  
      $users = array();
      $pi = $this->getPermIdFromKey($permission_name);
      foreach($this->rolePerms as $k => $v){
        if ($v['id_permission'] == $pi){ //FIX ===
          foreach($this->allUserRoles as $k2 => $v2){
            if($v2['id_role']==$v['id_role']){
              if ( !isset($users[$v2['id_user']]) ) $users[$v2['id_user']] = $this->getUsername($v2['id_user'],$full);  //.'  '.$this->getUserFullname($v2['id_user']);	 //   getUsername    
            }
          }
        }
      }

      return $users;
    }
    
    function getRoleUsers($role_name){
      $strSQL  = 'SELECT * FROM '.TB_USER
               .' WHERE user_id IN (SELECT id_user FROM '.TB_ACL_USER_ROLES.' WHERE id_role = '.$this->getRoleIdFromName($role_name).' ) ORDER BY user_level DESC,username';
      $rows = self::sqlQuery($strSQL);
      $users = array();
      foreach($rows as $row){      
        $users[$row['username']] = $row['user_email']; 
      }
      return $users;
    }

    function addUserRole($user_id,$role_name) {
      if($role_name=='') return false;
      $str_sql_get_role_id =sprintf("SELECT role_id FROM %s WHERE role_name = '%s'",TB_ACL_ROLES,$role_name);
      $role_id = self::getFieldValue($str_sql_get_role_id);
      if(!$role_id) {
        if (self::sqlExec(sprintf("INSERT INTO %s (role_name) VALUES('%s')",TB_ACL_ROLES,$role_name)))
          $role_id = self::getFieldValue($str_sql_get_role_id);
      }
      if(!$user_id) return false;
      if($role_id){  //FIX If exists, then take to ass
        $role_user = self::getFieldValue(sprintf("SELECT count(0) AS n FROM %s WHERE id_user=%u AND id_role = %u",TB_ACL_USER_ROLES,$user_id,$role_id));
        if($role_user<1){


          
         /*
          if(CFG::$vars['db']['type']  == 'sqlite')
              //$sql = sprintf("INSERT INTO ".TB_ACL_USER_ROLES." (id_user, id_role,user_role_add_date) VALUES (%u, %u, %s) ON CONFLICT(id_user, id_role) DO NOTHING",$post['user_id'],$roleID, date ("Y-m-d H:i:s") );
              $sql = sprintf("INSERT OR REPLACE INTO %s (id_user, id_role) VALUES (%u, %u)",TB_ACL_USER_ROLES,$user_id,$role_id);
          else 
         */
            $str_sql_add_user_role = sprintf("REPLACE INTO %s SET id_user = %u, id_role = %u, user_role_add_date = '%s'",TB_ACL_USER_ROLES,$user_id,$role_id,date ("Y-m-d H:i:s"));




          self::sqlExec($str_sql_add_user_role);
        }
      }
      return true;
    }
    
    function deleteUserRole($user_id,$role_name) {
      if( $role_name=='') return false;
      $str_sql_get_role_id =sprintf("SELECT role_id FROM %s WHERE role_name = '%s'",TB_ACL_ROLES,$role_name);
      $role_id = self::getFieldValue($str_sql_get_role_id);
      if(!$user_id) return false;
      if(!$role_id) return false;
      if($role_id){ 
         self::sqlExec(sprintf("DELETE FROM %s WHERE id_user=%u AND id_role = %u ",TB_ACL_USER_ROLES,$user_id,$role_id));
      }
    }
   
    function updateUserRole($user_id,$role_name,$value=true) {
       if($value) return $this->addUserRole($user_id,$role_name);
             else return $this->deleteUserRole($user_id,$role_name);
    }

    function addRole($role_name) {
      if(!$role_name) return false;
      $str_sql_get_role_id =sprintf("SELECT role_id FROM %s WHERE role_name = '%s'",TB_ACL_ROLES,$role_name);
      $role_id = self::getFieldValue($str_sql_get_role_id);
      if(!$role_id) {
        if (self::sqlExec(sprintf("INSERT INTO %s (role_name) VALUES('%s')",TB_ACL_ROLES,$role_name)))
        $role_id = self::getFieldValue($str_sql_get_role_id);
      }
      return $role_id;
    }

    function deleteRole($role_name) {
      if(!$role_name) return false;
      return self::sqlExec(sprintf("DELETE FROM %s WHERE role_name = '%s')",TB_ACL_ROLES,$role_name));
    }

    function addPermission($perm_key,$perm_name='',$debug=false) {
      if($perm_key=='') return false;
      // echo __LINE__.': '.$perm_key.'<br />';
      if($perm_name=='') $perm_name=$perm_key;
      if($debug) Vars::debug_var( __LINE__.'::addPermission::perm_key:'.$perm_key.',perm_name:'.$perm_name);
      $str_sql_get_perm_id =sprintf("SELECT permission_id FROM %s WHERE permission_key = '%s'",TB_ACL_PERMISSIONS,$perm_key);
      $perm_id = self::getFieldValue($str_sql_get_perm_id);
      if(!$perm_id) {
        $sql_insert = sprintf("INSERT INTO %s (permission_key,permission_name) VALUES('%s','%s')",TB_ACL_PERMISSIONS,$perm_key,$perm_name);
        if (self::sqlExec($sql_insert))
        $perm_id = self::getFieldValue($str_sql_get_perm_id);
        if($debug) Vars::debug_var( __LINE__.'::addPermission::'.$sql_insert.'::'.$perm_id );
      }
      return $perm_id;
    }

    function addRolePerm($role_name,$perm_key,$debug=false) {
      if($debug) Vars::debug_var(__LINE__.'::addRolePerm::role_name:'.$role_name.',perm_key:'.$perm_key);
      $str_sql_get_perm_id =sprintf("SELECT permission_id FROM %s WHERE permission_key = '%s'",TB_ACL_PERMISSIONS,$perm_key);
      $perm_id = self::getFieldValue($str_sql_get_perm_id);
      //echo __LINE__.': '.$str_sql_get_perm_id.' -> '.$perm_id.'<br />';

      if(!$perm_id) {
        $this->addPermission($perm_key);
        $perm_id = self::getFieldValue($str_sql_get_perm_id);
      }

      $str_sql_get_role_id =sprintf("SELECT role_id FROM %s WHERE role_name = '%s'",TB_ACL_ROLES,$role_name);
      $role_id = self::getFieldValue($str_sql_get_role_id);
      
      $str_sql_get_role_perm_id =sprintf("SELECT role_perm_id FROM %s WHERE id_permission = %d AND id_role=%d",TB_ACL_ROLE_PERMS,$perm_id,$role_id);
      $role_perm_id = self::getFieldValue($str_sql_get_role_perm_id);
      
      if(!$role_perm_id) {
        $sql_insert = sprintf("INSERT INTO %s (id_permission,id_role,role_perm_value) VALUES(%d,%d,1)",TB_ACL_ROLE_PERMS,$perm_id,$role_id);
        if (self::sqlExec($sql_insert))  $role_perm_id = self::getFieldValue($str_sql_get_role_perm_id);
        if($debug) Vars::debug_var( __LINE__.'::addRolePerm::'.$sql_insert.'::'.$role_perm_id);
      }
      return $role_perm_id;
    }
 
    function userIdHasRoleName($userid,$roleName) {
      $roleID = $this->getRoleIdFromName($roleName);
        $strSQL = "SELECT count(0) FROM ".TB_ACL_USER_ROLES." WHERE id_user = ".$userid." AND id_role=".$roleID." ORDER BY user_role_add_date ASC";
        return self::getFieldValue($strSQL);
    }

    /**
     * Migra nombres legacy en todos los .php, .css y .js de _modules_ y _themes_.
     * Sustituciones:
     *   $juxACL    → $_ACL
     *   JUX_CACHE  → _CACHE
     *   jux-dialog → wq-dialog
     *
     * Llamar desde el panel de control tras un update.
     *
     * @param bool $dry_run  true = solo muestra qué cambiaría, sin modificar nada
     * @return array         [ ['file'=>'...', 'changes'=>['patron'=>N, ...]], ... ]
     */
    public static function migrateVarName(bool $dry_run = true): array {
        $root = dirname(dirname(__FILE__));
        $scan = ['_modules_', '_themes_'];
        $exts = ['php', 'css', 'js'];

        $replacements = [
            '$juxACL'    => '$_ACL',
            'JUX_CACHE'  => '_CACHE',
            'jux-dialog' => 'wq-dialog',
            'JUX::' => 'APP::',
        ];

        $changed = [];

        foreach ($scan as $dir) {
            $path = $root . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) continue;

            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iter as $file) {
                if (!in_array($file->getExtension(), $exts)) continue;

                $content = file_get_contents($file->getRealPath());
                $counts  = [];

                foreach (array_keys($replacements) as $from) {
                    $n = substr_count($content, $from);
                    if ($n > 0) $counts[$from] = $n;
                }

                if (empty($counts)) continue;

                if (!$dry_run) {
                    $new_content = str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $content
                    );
                    file_put_contents($file->getRealPath(), $new_content);
                }

                $rel       = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                $changed[] = ['file' => $rel, 'changes' => $counts];
            }
        }

        return $changed;
    }


}

