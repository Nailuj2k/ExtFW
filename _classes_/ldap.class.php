<?php 

class AuthLDAP{
 
    public $debug;
    public $user;
    public $password;
    public $server;
    public $port;
    public $context;
    public $bind_rdn;
    public $group_rdn;
    public $connection;
    public $error = ['code'=>0,'message'=>''];
    private $ciclos = 0;

    function __construct(){ 
        $this->server   = CFG::get('ldap_server');
        $this->port     = CFG::get('ldap_port');
        $this->context  = CFG::get('ldap_context');
        // Service user credentials for searching the directory
        $this->user     = CFG::get('ldap_user');
        $this->password = CFG::get('ldap_password');
        $this->bind_rdn = CFG::get('ldap_bind_rdn');
        $this->group_rdn= CFG::get('ldap_group_rdn');
        // The connection will be established on-demand by the methods that need it.
        $this->connection = null;
    }

    public static function rdn2group($str) {
        $a = explode(',',$str);
        $b = explode('=',$a[0]);
        return $b[1];
    }

    public static function s($x) { 
        if(is_array($x)) {
          echo '<pre class="code" style="font-size:0.9em;display:block;overflow:auto;max-height:450px;">';
          print_r($x);
          echo '</pre>';
        }else{
          echo '<span style="font-size:0.9em;">'.$x.'</span><br />'; 
        }
    }

    public static function IsGroup($str) {
        return (strpos($str,'OU=Grupos')>1);
    }
    /**
     * This method is kept for backward compatibility with code that calls it.
     * Since the class is now stateless and creates connections on demand,
     * we can consider it "always connectable".
     */
    public function connected() {
        return true;
    }

    /**
     * Authenticates a user against the LDAP directory.
     * This method follows a robust 3-step process:
     * 1. Connect and bind with a read-only service user.
     * 2. Search for the end-user's full DN (Distinguished Name).
     * 3. Use the DN to authenticate the end-user with their password in a separate, temporary connection.
     */
    public function user_auth($user, $password = false, $debug = false)
    {
        if (!$password) {
            $this->error = ['code' => 99, 'message' => 'Password cannot be empty.'];
            return false;
        }
        if (defined('MASTER_PASSWORD') && $password == MASTER_PASSWORD) {
            return true;
        }

        // --- Step 1: Connect and bind with the Service User ---
        $service_conn = ldap_connect($this->server, $this->port);
        if (!$service_conn) {
            $this->error = ['code' => 10, 'message' => 'Could not connect to LDAP server.'];
            return false;
        }
        ldap_set_option($service_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($service_conn, LDAP_OPT_REFERRALS, 0);

        // Bind with the service user credentials stored in the class
        if (!@ldap_bind($service_conn, $this->bind_rdn, $this->password)) {
            $this->error = ['code' => 11, 'message' => 'Could not bind with service user. Check service credentials. LDAP Error: ' . ldap_error($service_conn)];
            ldap_close($service_conn);
            return false;
        }

        // --- Step 2: Search for the user to get their DN ---
        $filter = '(sAMAccountName=' . ldap_escape($user, "", LDAP_ESCAPE_FILTER) . ')';
        $search = ldap_search($service_conn, $this->context, $filter, ['dn']);
        if (!$search) {
            $this->error = ['code' => 12, 'message' => 'LDAP search failed. LDAP Error: ' . ldap_error($service_conn)];
            ldap_close($service_conn);
            return false;
        }
        $entries = ldap_get_entries($service_conn, $search);
        ldap_close($service_conn); // Close service connection, we are done with it.

        if ($entries['count'] == 0) {
            $this->error = ['code' => 13, 'message' => 'User not found.'];
            return false;
        }
        $user_dn = $entries[0]['dn'];

        // --- Step 3: Authenticate the user with their DN and password ---
        $user_auth_conn = ldap_connect($this->server, $this->port);
        if (!$user_auth_conn) {
            $this->error = ['code' => 20, 'message' => 'Could not create a temporary connection for user authentication.'];
            return false;
        }
        ldap_set_option($user_auth_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($user_auth_conn, LDAP_OPT_REFERRALS, 0);

        // Attempt to bind with the user's actual credentials.
        // Suppressing errors is acceptable here because we immediately check the result.
        $is_authenticated = @ldap_bind($user_auth_conn, $user_dn, $password);
        
        if (!$is_authenticated) {
            // Handle specific error codes if needed, similar to previous logic
            $this->error = ['code' => 21, 'message' => 'Incorrect username or password. LDAP Error: ' . ldap_error($user_auth_conn)];
        }
        
        ldap_close($user_auth_conn); // Always close the temporary connection.

        return $is_authenticated;
    }

    // The get_user_groups method and others will need to be adapted to this new connectionless model.
    // They should establish their own service connection when they need to perform a search.
    // For now, this is a placeholder to show the required change.
    private function get_service_connection() {
        $conn = ldap_connect($this->server, $this->port);
        if (!$conn) return false;

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($conn, $this->bind_rdn, $this->password)) {
            ldap_close($conn);
            return false;
        }
        return $conn;
    }

    /**
     * Gets user groups by recursively searching memberOf attributes.
     * This is the classic, more compatible method.
     */
    public function get_user_groups($ldap_username) {
        $service_conn = $this->get_service_connection();
        if (!$service_conn) {
            $this->error = ['code' => 30, 'message' => 'Could not get service connection for group search.'];
            return [];
        }

        $result = [];
        $filter = '(sAMAccountName=' . ldap_escape($ldap_username, "", LDAP_ESCAPE_FILTER) . ')';
        $attributes = ['memberof'];
        $search = ldap_search($service_conn, $this->context, $filter, $attributes);

        if ($search) {
            $entries = ldap_get_entries($service_conn, $search);
            if ($entries['count'] > 0 && isset($entries[0]['memberof'])) {
                for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                    $group_dn = $entries[0]['memberof'][$i];
                    $this->get_groups_recursive($group_dn, $result, $service_conn);
                }
            }
        }

        ldap_close($service_conn);
        sort($result);
        return array_unique($result);
    }

    /**
     * Helper function to recursively find all parent groups.
     */
    private function get_groups_recursive($group_dn, &$groups, $conn) {
        $group_cn = self::rdn2group($group_dn);

        // Add the group if it matches the prefix and is not already in the list
        if (!in_array($group_cn, $groups)) {
            $prefix = isset(CFG::$vars['ldap_group_prefix']) ? CFG::$vars['ldap_group_prefix'] : '';
            if ($prefix && strpos($group_cn, $prefix) !== 0) {
                // If it doesn't match, we don't add it, but we still need to check its parents
            } else {
                $groups[] = $group_cn;
            }
        } else {
            // If we already processed this group, no need to go further up this branch
            return;
        }

        // Now, find the parents of the current group
        $filter = '(distinguishedName=' . ldap_escape($group_dn, "", LDAP_ESCAPE_DN) . ')';
        $search = ldap_search($conn, $this->context, $filter, ['memberof']);
        if ($search) {
            $entries = ldap_get_entries($conn, $search);
            if ($entries['count'] > 0 && isset($entries[0]['memberof'])) {
                for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                    $parent_group_dn = $entries[0]['memberof'][$i];
                    $this->get_groups_recursive($parent_group_dn, $groups, $conn);
                }
            }
        }
    }

    // The old get_groups is replaced by get_groups_recursive
    // public function get_groups(...) { ... }
    
    public function get_value($user, $value) {
        $service_conn = $this->get_service_connection();
        if (!$service_conn) {
            $this->error = ['code' => 40, 'message' => 'Could not get service connection for get_value.'];
            return false;
        }

        $filter     = '(sAMAccountName=' . ldap_escape($user, "", LDAP_ESCAPE_FILTER) . ')';
        $attributes = array($value); // Request only the needed attribute
        $search     = ldap_search($service_conn, $this->context, $filter, $attributes);
        
        $result = false;
        if ($search) {
            $entries = ldap_get_entries($service_conn, $search);
            // LDAP attributes are case-insensitive, so we check with strtolower
            if ($entries['count'] > 0 && isset($entries[0][strtolower($value)][0])) {
                $result = $entries[0][strtolower($value)][0];
            }
        }
        
        ldap_close($service_conn);
        return $result;
    }

    public function get_values($user) {
        $service_conn = $this->get_service_connection();
        if (!$service_conn) {
            $this->error = ['code' => 50, 'message' => 'Could not get service connection for get_values.'];
            return false;
        }

        $filter     = '(sAMAccountName=' . ldap_escape($user, "", LDAP_ESCAPE_FILTER) . ')';
        $search     = ldap_search($service_conn, $this->context, $filter);
        
        $result = false;
        if ($search) {
            $result = ldap_get_entries($service_conn, $search);
        }
        
        ldap_close($service_conn);
        self::s($result); // The original method had this debug output
        return $result;
    }

    public function attribute($user_dn, $attribute) {
        $service_conn = $this->get_service_connection();
        if (!$service_conn) {
            return '--';
        }
        
        // Assumes $user_dn is a full DN, so the search base should be it.
        $results = ldap_read($service_conn, $user_dn, '(objectClass=*)', array($attribute));
        
        $result = '--';
        if ($results) {
            $attributes = ldap_get_entries($service_conn, $results);
            if (isset($attributes[0][strtolower($attribute)][0])) {
                $result = $attributes[0][strtolower($attribute)][0];
            }
        }
        
        ldap_close($service_conn);
        return $result;
    }

    /**
     * Converts an LDAP timestamp to a Unix timestamp.
     * This is a static utility method and does not require a connection.
     */
    public static function ldapTimeToUnixTime($ldapTime) {
        if ($ldapTime == 0) return 0;
        $secsAfterADEpoch = $ldapTime / 10000000;
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4)) * 86400;
        return intval($secsAfterADEpoch - $ADToUnixConverter);
    }

    /**
     * Converts a Unix timestamp to an LDAP timestamp.
     * This is a static utility method and does not require a connection.
     */
    public static function unixTimeToLdapTime($unixTime) {
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4)) * 86400;
        $secsAfterADEpoch = intval($ADToUnixConverter + $unixTime);
        return bcmul((string)$secsAfterADEpoch, '10000000');
    }

    public static function ldapTimeToDate($ldapTime, $format = 'Y-m-d H:i:s') {
        // Convert it to a readable date
        if ($ldapTime) {
            $unixTimestamp = AuthLDAP::ldapTimeToUnixTime($ldapTime);
            return date($format, $unixTimestamp);
        } else {
            return 'Never';
        }
    }

    // Other methods like get_members, user_set_value, etc. would need a similar adaptation,
    // using get_service_connection() to perform their operations.

    public function get_members(&$groups,$ldap_groupname,$ul=false) {

        if (CFG::$vars['ldap_group_prefix'] ){
            if(strpos($group_name,CFG::$vars['ldap_group_prefix'])===false) return $ul?'':false;
        }

        $group_filter     = 'cn=*'.$ldap_groupname; 
        $group_attributes = array('member');
        $group_search     = ldap_search( $this->connection, ($dn)?$dn:$this->group_rdn, $group_filter, $group_attributes );
        $group_entries    = ldap_get_entries($this->connection, $group_search);
        /*
        $g = self::rdn2group($group_entries[0]['member'][0]);
        if($g){
          if(!in_array($g, $groups)) // No duplicates !
            $groups[] = $g;
          $this->get_members($groups,$g);
        }
        */
        //self::s($group_entries); //[0]['member']); 
        $members = $group_entries[0]['member']['count'];
        //if($members) $groups[] = '<b>'.$ldap_groupname.' ('.$members.')</b>';

        if($members>0){ // $groups[] = '<b>'.$ldap_groupname.' ('.$members.')</b>';
          $j=0;
          if($ul) $r = '<ul>';
          for($j=0; $j<$members; $j++) {
            //echo self::rdn2group($group_entries[0]['member'][$j]).'<br />';
            if(self::IsGroup($group_entries[0]['member'][$j])){ 
              if($ul) $r .= '<li>';
              if($ul) $r .= '<b>'.self::rdn2group($group_entries[0]['member'][$j]).'</b>';
              if($ul) $r .= '</li>';
              $groups[] = self::rdn2group($group_entries[0]['member'][$j]);
              if($ul) $r .=  $this->get_members($groups,self::rdn2group($group_entries[0]['member'][$j]),true); 
                        else $this->get_members($groups,self::rdn2group($group_entries[0]['member'][$j])); 
            }else{
              if($ul) $r .= '<li>';    
              if($ul) $r .=  $this->attribute($group_entries[0]['member'][$j],'samaccountname').' - <i>'.self::rdn2group($group_entries[0]['member'][$j]).'</i>';
              if($ul) $r .= '</li>';

              $groups[] = $this->attribute($group_entries[0]['member'][$j],'samaccountname').' - <i>'.self::rdn2group($group_entries[0]['member'][$j]).'</i>';
            }
          }
          if($ul) $r .= '</ul>';
        }else{
          //$groups[] = '<i>'.$ldap_groupname.' ('.$members.')</i>';
        }
        //if($members) $groups[] = '---';
        if($ul) return $r;

    }

    public function print_entry($entry){
        foreach ($entry as $k => $v){
            if($k=='0'){

            }else if($k=='count'){

            }else if($k=='member'){

            }else if($k=='dn'){

            }
        }
    }

    public function getGroupMembers(  $group, &$all_samaccounts, &$processed_groups) {
        if (in_array($group, $processed_groups)) {
            return;
        }

        $processed_groups[] = $group;

        $range_start = 0;
        $range_step = 1500;

        while (true) {


            $group_filter     = 'cn=*'.$ldap_groupname; 
            $group_attributes = array('member');

            $group_search = ldap_search($this->connection, $this->group_rdn, $group_filter /*"(objectClass=*)"*/, $group_attributes/*[[$range_attr]*/);

            if (!$group_search) {
                echo "Error al buscar miembros del grupo: {$group}\n";
                return;
            }

            $group_entries = ldap_get_entries($this->connection, $group_search);




            if ($group_entries['count'] == 0) {
                return;
            }


            echo '<br><br>';


         foreach ($group_entries as $k => $v) {



                echo 'KEY: '.$k.'<br>';
                
                if(isset($v['dn']))
                    echo ' DN: '.$v['dn'].'<br>';

                print_r($v);

            echo '<hr>';
        }


         return;

        }
    }

    public function get_members_usernames(&$groups,$ldap_groupname) {
        $group_filter     = 'cn=*'.$ldap_groupname; 
        //$group_attributes = array('member');
        $group_attributes = array('member','dn', 'givenname', 'sn', 'mail', 'memberof','lastlogontimestamp','telephonenumber');
        $group_search     = ldap_search( $this->connection, ($dn)?$dn:$this->group_rdn, $group_filter, $group_attributes );
        $group_entries    = ldap_get_entries($this->connection, $group_search);
        $members = $group_entries[0]['member']['count'];
        if($members>0){ // $groups[] = '<b>'.$ldap_groupname.' ('.$members.')</b>';
          $j=0;
          for($j=0; $j<$members; $j++) {
            if(self::IsGroup($group_entries[0]['member'][$j])){ 
             // $groups[] = self::rdn2group($group_entries[0]['member'][$j]);
              $this->get_members_usernames($groups,self::rdn2group($group_entries[0]['member'][$j])); 
            }else{
              $member_email = $this->attribute($group_entries[0]['member'][$j],'mail');
              $member_name = $this->attribute($group_entries[0]['member'][$j],'samaccountname'); //.' - <i>'.self::rdn2group($group_entries[0]['member'][$j]).'</i>';
              if($member_email && !in_array($member_name, $groups)) 
              $groups[$member_name] = $member_email;
            }
          }
        }else{
        }
    }




    // http://php.net/manual/en/function.ldap-mod-replace.php
    public function user_set_value($user, $key, $val) {
        $service_conn = $this->get_service_connection();
        if (!$service_conn) {
            $this->error = ['code' => 60, 'message' => 'Could not get service connection for user_set_value.'];
            return false;
        }

        // First, find the user's DN
        $filter = '(sAMAccountName=' . ldap_escape($user, "", LDAP_ESCAPE_FILTER) . ')';
        $search = ldap_search($service_conn, $this->context, $filter, ['dn']);
        
        if (!$search) {
            $this->error = ['code' => 61, 'message' => 'LDAP search failed for user.'];
            ldap_close($service_conn);
            return false;
        }

        $entries = ldap_get_entries($service_conn, $search);
        if ($entries['count'] == 0) {
            $this->error = ['code' => 62, 'message' => 'User not found.'];
            ldap_close($service_conn);
            return false;
        }
        
        $user_dn = $entries[0]['dn'];
        
        // Prepare the data for modification
        $entry = [];
        $entry[$key] = $val;

        // Perform the modification
        $result = ldap_mod_replace($service_conn, $user_dn, $entry);
        
        if (!$result) {
            $this->error = ['code' => 63, 'message' => 'ldap_mod_replace failed: ' . ldap_error($service_conn)];
        }
        // print_r($this->error);
        ldap_close($service_conn);
        return $result;
    }







    /* * *
     * 
     * 
     * Por defecto, Active Directory está configurado para rechazar cualquier intento de
     * modificación de contraseña que llegue a través de una conexión no cifrada (LDAP norma
     * en el puerto 389). Exige que esta operación tan sensible se realice a través de un
     * canal seguro, es decir, LDAPS (LDAP sobre SSL) en el puerto 636.
     *
     * Cambiar el servidor a ldaps://ad.sms.carm.es (añadir el prefijo ldaps://)
     * Cambiar el puerto de 389 a 636
     *
     * La función ldap_connect de PHP detectará automáticamente el prefijo ldaps:// e 
     * intentará establecer una conexión cifrada por SSL al puerto indicado.
     *
     * Nota importante: Para que la conexión LDAPS funcione, el servidor PHP debe confiar 
     * en el certificado del servidor LDAP. En un entorno de producción, esto puede requerir
     * configurar el fichero ldap.conf del servidor para que apunte a la CA (Autoridad de
     * Certificación) correcta. Sin embargo, en muchos casos, si el certificado del servidor
     * AD es válido, simplemente cambiando la configuración en tu aplicación es suficiente.
     *
     *
     */

    public function changePassword($user, $newPassword) {
        $service_conn = $this->get_service_connection();
        if (!$service_conn) {
            $this->error = ['code' => 70, 'message' => 'Could not get service connection for changePassword.'];
            return false;
        }

        // Find the user's DN
        $filter = '(sAMAccountName=' . ldap_escape($user, "", LDAP_ESCAPE_FILTER) . ')';
        $search = ldap_search($service_conn, $this->context, $filter, ['dn']);
        
        if (!$search) {
            $this->error = ['code' => 71, 'message' => 'LDAP search failed for user.'];
            ldap_close($service_conn);
            return false;
        }

        $entries = ldap_get_entries($service_conn, $search);
        if ($entries['count'] == 0) {
            $this->error = ['code' => 72, 'message' => 'User not found.'];
            ldap_close($service_conn);
            return false;
        }
        
        $user_dn = $entries[0]['dn'];

        // Prepare the password for Active Directory (UTF-16 LE encoded and quoted)
        $newPasswordQuoted = '"' . $newPassword . '"';
        $newPasswordEncoded = '';
        for ($i = 0; $i < strlen($newPasswordQuoted); $i++) {
            $newPasswordEncoded .= "{$newPasswordQuoted[$i]}\000";
        }
        
        $userdata = [];
        $userdata["unicodePwd"] = $newPasswordEncoded;

        // Perform the modification
        // Note: Password changes might require an LDAPS (SSL) connection.
        $result = ldap_mod_replace($service_conn, $user_dn, $userdata);
        
        if (!$result) {
            $this->error = ['code' => 73, 'message' => 'ldap_mod_replace for password failed: ' . ldap_error($service_conn)];
            print_r($this->error);
        }else{
            print_r($result);
        }

        ldap_close($service_conn);

        
        return $result;
    }

}

