<?php

// Table and field names. Change them to use an existing user table. 
define('USER_ID','user_id');
define('USER_IP','user_ip');
define('USER_EMAIL','user_email');
define('USER_CARD_ID','user_card_id');
define('USER_FULLNAME','user_fullname');
define('USER_ONLINE','user_online');
define('USER_ACTIVE','user_active');
define('USER_LASTLOGIN','user_last_login');
define('USER_SALT','user_salt');
define('USER_CONFIRM_CODE','user_confirm_code');
define('USER_VERIFY','user_verify');
define('USER_SIGNATURE','user_signature');
define('USER_LPD_PUBLI','user_lpd_publi');
define('USER_LPD_DATA','user_lpd_data');
define('USERNAME','username');
define('PASSWORD','user_password');

if       (CFG::$vars['db']['type']=='mysql') {
    class DbConnection {
        use MysqlConnection;   
    }
}else if (CFG::$vars['db']['type']=='sqlite') {
    class DbConnection {
        use SQLiteConnection;   
    }
}



/***********
https://medium.com/javascript-scene/passwordless-authentication-with-react-and-auth0-c4cb003c7cde#.6bt9m8ug2
https://medium.com/@ninjudd/passwords-are-obsolete-9ed56d483eb#.ix14f913z

https://www.codexworld.com/login-with-google-api-using-php/
https://phppot.com/php/php-google-oauth-login/
https://code.tutsplus.com/tutorials/how-to-authenticate-users-with-twitter-oauth-20--cms-25713
***********/

class Login extends DbConnection { 
 
    //use MysqlConnection;   
    //use SQLiteConnection;   

    public $validuser = false;
    public $perfil = false;
    public $ldap = false;
  //public $googleauth = false;
    public $demo = false;

    public function __construct() {
        self::connect();
        if (CFG::$vars['auth']=='ldap')  $this->ldap = new AuthLDAP(); 
        if (CFG::$vars['auth']=='demo')  $this->demo = new AuthDEMO(); 
    }
    /*    
    private function lastInsertId()   {
        $sql = "SELECT ".USER_ID." FROM ".TB_USER." ORDER BY ".USER_ID." DESC LIMIT 1";
        $row = self::getFieldsValues($sql);
        return $row[USER_ID];
    }
    */     

    public static function getUserScore(){
        if(!$_SESSION['userid']) return 0;
        $user_data = self::getFieldsValues('SELECT user_score FROM '.TB_USER.' WHERE USER_ID = '.$_SESSION['userid']);
        if($user_data) $_SESSION['user_score'] = $user_data['user_score'];
                  else $_SESSION['user_score'] = 1;
        return $_SESSION['user_score'];
    }
    
    public static function updateLastLogin($id,$karma_bonus=null) {
        $ahora = time();
        $ip    =  get_ip();

        if($karma_bonus===null) 
            $sql = "UPDATE ".TB_USER." SET ".USER_IP."='$ip',".USER_ONLINE."=1,".USER_LASTLOGIN."=$ahora WHERE ".USER_ID."='$id'"; //.$_SESSION['user_id'];
        else
            $sql = "UPDATE ".TB_USER." SET ".USER_IP."='$ip',".USER_ONLINE."=1,".USER_LASTLOGIN."=$ahora, user_score = user_score + $karma_bonus WHERE ".USER_ID."='$id'";

        Login::sqlExec($sql);
        Login::updateOfflineUsers();
    }
    
    public static function updateOfflineUsers() {
      //updateLastLogin($_SESSION['userid']);
      $timeout=(time()-(60*5));
      Login::sqlExec("UPDATE ".TB_USER." set ".USER_ONLINE."=0 WHERE ".USER_LASTLOGIN."<$timeout AND ".USER_ID."<>".$_SESSION['userid']);
    }

    private function createSalt(){
        $string = md5(uniqid(rand(), true));
        return substr($string, 0, 3);
    }
        
    public function logout($ajax=false){
        if($_SESSION['valid_user']){
            Login::sqlExec('UPDATE '.TB_USER.' set '.USER_ONLINE.'=0 WHERE '.USER_ID.'='.$_SESSION['userid']);
            
            if(!$ajax) Messages::info(sprintf( t('BYE_%s') , $_SESSION['username'] ) , './home',2000); //Header('Location: /');            
          //$_SESSION['message_info'] =  sprintf( t('BYE_%s') , $_SESSION['username'] );
        }else{
            if(!$ajax) Messages::info( 'Not logged in', './',2000); //Header('Location: /');            
        }
        // Vars::debug_var($_SESSION);

        $authProvider = strtolower(trim((string)($_SESSION['auth_provider'] ?? '')));
        if ($authProvider === 'google') {
            $googleConfig = [];
            if (
                isset(CFG::$vars['oauth']) &&
                is_array(CFG::$vars['oauth']) &&
                isset(CFG::$vars['oauth']['google']) &&
                is_array(CFG::$vars['oauth']['google'])
            ) {
                $googleConfig = CFG::$vars['oauth']['google'];
            }

            if (!empty($googleConfig['id']) && !empty($googleConfig['secret'])) {
                require_once './_modules_/login/google.php';
                if (isset($client) && method_exists($client, 'revokeToken')) {
                    $client->revokeToken($_SESSION['auth_token'] ?? null);
                }
            }
        }

        /**/
        /*
        if ($_SESSION['auth_provider']){
            require_once './_modules_/login/google.php';
            $client->revokeToken();
        }
        */

        unset($_COOKIE[CFG::$vars['prefix'].'_username']);
        unset($_COOKIE[CFG::$vars['prefix'].'_userpass']);

        $_SESSION[USERNAME]=false;
        $_SESSION[USER_FULLNAME]=false;
        $_SESSION[USER_EMAIL]=false;
        $_SESSION['valid_user']=false; 
        $_SESSION['userid']=false;
        $_SESSION['userlevel']=100;
        $_SESSION['user_score']=0;
        $_SESSION['ACL']=false;
        $_SESSION['_CACHE']=false;
        $_SESSION['token']=false;
        $_SESSION['user_url_avatar']=false;
        $_SESSION['auth_provider']=false;
        $_SESSION['auth_token']=false;
        $_SESSION['auth_id']=false;
        $_SESSION['auth_picture']=false;
        //$_SESSION['user_score']     = 0;

        // session_destroy();
        // unset($_SESSION);

        //echo '<script type="text/javascript">localStorage.clear();</script>';
        // $_SESSION = array(); //destroy all of the session variables
        // session_destroy();

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if(!$ajax) {
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        }
        // Finally, destroy the session.
        session_destroy();
        $_SESSION['lang'] = CFG::$vars['default_lang'];  // reset default lang
        return true;

    }
        
    // Authentication
    // This function return true if user (or email) and password (if required) is correct.
    // .. and, if auth, configure session vars for user logged in
    // This not check any perms or group membership !!!
    public function login($data){

        // Find, if allowed, user (or user email) in table user and use user custom auth type
        // In this case CFG::$vars['auth'] is only the 'default auth method'
        $result = array();
        $result['auth'] = false;
        $result['error'] = 1;
        $result['msg'] = '';
        $result['url'] = '';
        $_SESSION['message_error']='';
        //Vars::debug_var($data);

        // Regenerate session ID on login
        // session_regenerate_id(true);  //CHECK Is necessary?

        if($data['username'] && $data['password']){
                 if ($data['auth_provider']=='google')   { $result['auth'] = $this->authGoogle($data);     }
            else if ($data['auth']=='rfid')              { $result['auth'] = $this->authRFID($data);       }
            else if (CFG::$vars['auth']=='ldap')         { $result['auth'] = $this->authLDAP($data);       }
            else if (CFG::$vars['auth']=='demo')         { $result['auth'] = $this->authDEMO($data);       } 
            else if (CFG::$vars['db']['type']=='mysql')  { $result['auth'] = $this->authMySql($data);      }
            else if (CFG::$vars['db']['type']=='sqlite') { $result['auth'] = $this->authSQLITE($data);     }
            else                                           $result['auth'] = false;


            //     Vars::debug_var($data);
            //     die();


            // https://phppot.com/php/facebook-open-authentication-in-php/
            // https://www.codexworld.com/login-with-google-api-using-php/

            if ($result['auth']){
                $result['error']=0;
                $u = $data['username']; //mysql_real_escape_string($data['username']);
                $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER." WHERE ".USER_EMAIL." = '$u' OR ".USERNAME." = '$u'");
                if($user_data){
                    $_SESSION['valid_user']     = true; 
                    $_SESSION['userid']         = $user_data['user_id'];
                    $_SESSION['userlevel']      = $user_data['user_level'];
                    $_SESSION['user_score']     = $user_data['user_score'];
                    $_SESSION['user_url_avatar']= $user_data['user_url_avatar'];
                    $_SESSION['username']       = $user_data[USERNAME];
                    $_SESSION['user_fullname']  = $user_data[USER_FULLNAME];
                    $_SESSION['user_email']     = $user_data[USER_EMAIL];
                 // $_SESSION['message_info'] = sprintf( t('HELLO_%s') , $data['username'] );
                  //if (isset($_COOKIE[CFG::$vars['prefix'].'hits'])) $_SESSION['hits']=$_COOKIE[CFG::$vars['prefix'].'hits'];
                  //if (isset($_COOKIE[$configuration['prefix'].'hits'])) $_SESSION['hits']=$_COOKIE[$configuration['prefix'].'hits'];
                  //if ($data[USER_VERIFY]=='1') Login::sqlExec("UPDATE ".TB_USER." SET ".USER_VERIFY."='0' WHERE ".USER_ID."=".$data[USER_ID]);
                  //self::sqlExec("UPDATE ",TB_USER." SET user_browser='".getBrowser()."' WHERE user_id=".$data['user_id']);
                  //Messages::info('USERID: '.$user_data['user_id']);




                    $hoy   = date('Y-m-d');
                    $ultimo_login = $user_data['user_last_login'] ?? 0;
                    $dia_ultimo_login = date('Y-m-d', $ultimo_login);
                            // Solo dar punto de karma si es un día diferente al último login
                    $karma_bonus = ($dia_ultimo_login !== $hoy && Karma::$enabled) ? 1 : 0;
                    
                    //$sql = "UPDATE ".TB_USER." SET ".USER_IP."='$ip',".USER_ONLINE."=1,".USER_LASTLOGIN."=$ahora, user_score = user_score + $karma_bonus WHERE ".USER_ID."='$id'";



                    if (isset($data['auth_provider'])){ 
                        if($user_data['AUTH_PROVIDER']!==$data['auth_provider']||!$user_data['AUTH_ID']||!$user_data['AUTH_PICTURE']){
                          //Vars::debug_var("UPDATE ".TB_USER." SET AUTH_PROVIDER='".$data['auth_provider']."',AUTH_ID='".$data['auth_id']."',AUTH_PICTURE='".$data['auth_picture']."' WHERE ".USER_EMAIL." = '".$user_data[USER_EMAIL]."'" );
                            Login::sqlExec("UPDATE ".TB_USER." SET AUTH_PROVIDER='".$data['auth_provider']."',AUTH_ID='".$data['auth_id']."',AUTH_PICTURE='".$data['auth_picture']."' WHERE ".USER_EMAIL." = '".$user_data[USER_EMAIL]."'" );
                        }
                        $_SESSION['auth_provider']  = $data['auth_provider'];
                        $_SESSION['auth_id']  = $data['auth_id'];
                        $_SESSION['auth_picture']  = $data['auth_picture'];
                    }else{
                        // if($user_data['user_url_avatar'])
                        Login::sqlExec("UPDATE ".TB_USER." SET AUTH_PROVIDER='',AUTH_ID='' WHERE ".USER_EMAIL." = '".$user_data[USER_EMAIL]."'" );
                    }

                    self::updateLastLogin($user_data['user_id'],$karma_bonus);
                    
                    $_SESSION['save']= $data['save'];

                    if ( /*!$data['from']!='shop' &&*/ $data['save']){
                        $u = Crypt::md5_encrypt(strtolower($data[USERNAME]), CFG::$vars['prefix'],16); //,$user_data[USER_SALT]);
                        $p = Crypt::md5_encrypt($data['password'], CFG::$vars['prefix'].strtolower($data[USERNAME]),16); //,$user_data[USER_SALT]);
                        $_SESSION[CFG::$vars['prefix'].'_username']=Crypt::base64_url_encode($u);
                        $_SESSION[CFG::$vars['prefix'].'_userpass']=Crypt::base64_url_encode($p);
                        // Message::info( 'Cookie: '.CFG::$vars['prefix'].'_username/userpass: '.$u.":".$p.'<br />Salt: '.$user_data[USER_SALT] );
                    }
                    //  Messages::info(sprintf( t('HELLO_%s') , $data['username'] ),$_SESSION['userid']<4?'./control_panel':'/',2000); 
                    if ($data['auth_provider']!=='google') {
                        if( /*!$data['from']!='shop'&&*/ $_SESSION['backurl']){
                            Messages::info('Redirect to: '.$_SESSION['backurl']); 
                            $go_url = $_SESSION['backurl'];
                            $_SESSION['backurl']=false;
                        }else{
                            if(!$_SESSION['backurl']) $go_url = '/'; //'login/profile';
                        }
                        if($go_url)
                        $result['url'] = $go_url;
                        $result['msg'] = sprintf( t('HELLO_%s') , $data['username'] );
                       // Messages::info(sprintf( t('HELLO_%s') , $data['username'] ),$go_url,2000);     
                    }
  
                }else{  // if($user_data){
                    $ahora = time();
                    // $ip_real =              $_SERVER["HTTP_X_FORWARDED_FOR"];
                    // if (!$ip_real) $ip_real=$_SERVER["HTTP_CLIENT_IP"];
                    // if (!$ip_real) $ip_real=$_SERVER["REMOTE_ADDR"];

                    $ip_real = get_ip();

                    if ($data['auth_provider']=='google'){
                        $query = "INSERT INTO ".TB_USER
                               . "( ".USERNAME
                               . ", ".USER_EMAIL
                               . ", AUTH_PROVIDER "
                               . ", AUTH_ID "
                               . ", AUTH_PICTURE "
                               . ", ".USER_IP
                               . ", ".USER_LASTLOGIN
                               . ", ".USER_FULLNAME
                               . ", ".USER_ACTIVE
                               ." ) VALUES ( "
                               . " '".$data['username']."' "
                               . ",'".$data['user_email']."' "
                               . ",'".$data['auth_provider']."' "
                               . ",'".$data['auth_id']."' "
                               . ",'".$data['auth_picture']."' "
                               . ",'{$ip_real}' "
                               . ",'{$ahora}' "
                               . ",'".$data['user_fullname']."'"
                               . ",'1' );";
                        if(Login::sqlExec($query)){
                            $_SESSION['valid_user'] = true; 
                            $_SESSION['userid']    = Login::lastInsertId();
                            $_SESSION['userlevel'] = 100;
                            $_SESSION['username']  = $data['username'];
                            $_SESSION['user_fullname']  = $data['user_fullname'];
                            $_SESSION['user_email']  = $data['user_email'];
                            $_SESSION['auth_provider']  = $data['auth_provider'];
                            $_SESSION['auth_id']  = $data['auth_id'];
                            $_SESSION['auth_picture']  = $data['auth_picture'];
                            //$_SESSION['user_score']     = 0;

                            Messages::info(sprintf( t('HELLO_%s') , $data['username'] ),'login/profile',2000);     
                        }else{
                            Messages::error('ERROR '.$data['username'],'login',5000);     
                        }

                    }else if (CFG::$vars['auth']=='ldap'){
                        //$salt = $this->createSalt();
                        //$code = Str::password(20, 4);
                        $email = $this->ldap->get_value($data['username'],'mail');
                        $name = $this->ldap->get_value($data['username'],'givenname');
                        $dept = $this->ldap->get_value($data['username'],'department');

                        $query = "INSERT INTO ".TB_USER
                               . "( ".USERNAME
                               . ", ".USER_EMAIL
                               . ", ".USER_IP
                               . ", ".USER_LASTLOGIN
                           // . ", ".PASSWORD.", ".USER_SALT.", ".USER_CONFIRM_CODE.", ".USER_VERIFY.", ".USER_SIGNATURE
                               . ", ".USER_FULLNAME
                               . ", ".USER_ACTIVE
                               ." ) VALUES ( "
                               . " '{$data['username']}' "
                               . ",'{$email}' "
                               . ",'{$ip_real}' "
                               . ",'{$ahora}' "
                             //. ",'$hash' , '$salt', '$code', '1',''"  //{$data['password']}'"
                               . ",'{$name}'"
                               . ",'1' );";
                        if(Login::sqlExec($query)){
                            $_SESSION['valid_user'] = true; 
                            $_SESSION['userid']    = Login::lastInsertId();
                            $_SESSION['userlevel'] = 100;
                            $_SESSION['username']  = $data['username'];
                            $_SESSION['user_fullname']  = $name;
                            $_SESSION['user_email']  = $email;                           // FIX CHECK email
                            //$_SESSION['user_score']     = 0;
 
                            Messages::info(sprintf( t('HELLO_%s') , $data['username'] ),'login/profile',2000);     
                        }else{
                            Messages::error('ERROR '.$data['username'],'login',5000);     
                        }

                    }else{

                        $_SESSION['message_error'] = t('NO_USER_DATA','No hay datos para este usuario').': '.CFG::$vars['auth'].'::'.$data['username'];  ;

                    }

                   $result['error'] = 1;
                   $result['msg']=t('NO_USER_DATA','No hay datos de usuario.');
                }

                if($result['error'] == 0){
                    if (CFG::$vars['auth']=='ldap'){
                        $groups =  $this->ldap->get_user_groups($_SESSION['username'] );
                        self::LDAP_sincronizeRoles($groups);
                        self::LDAP_sincronizeMyRoles($groups);
                        //unset($_SESSION['ACL']) ;
                        //$_ACL = new ACL();
                    }

                    // ELIMINAR esta línea:
                    // Karma::rewardDailyLogin($_SESSION['userid']);

                }

            }else{
               if (CFG::$vars['auth']=='ldap'){
                   $_SESSION['message_error'] = $this->ldap->error['message']
                                              ? $this->ldap->error['message']
                                              : ($_SESSION['message_error']?$_SESSION['message_error']:t('USERNAME_OR_PASSWORD_INCORRECT'));  
               }else{
                   $_SESSION['message_error'] = $_SESSION['message_error']?$_SESSION['message_error']:t('USERNAME_OR_PASSWORD_INCORRECT');  
               }
               $result['msg']=$_SESSION['message_error'];
            }
        }else{    // if($data['username'] && $data['password'])
            $_SESSION['message_error'] = t('USERNAME_OR_PASSWORD_MISSING');  
            $result['msg']=$_SESSION['message_error'];
        }

        if(CFG::$vars['log']['auth'] ){
            if ($_SESSION['valid_user']){
                $subject = 'LOGIN '.$data['username'];

                $sql_log = "INSERT INTO ".TB_LOG." (TYPE,EMAIL,SUBJECT,MESSAGE,ID_USER) 
                                 VALUES('7','{$_SESSION['user_email']}','{$subject}','".print_r($result,true)."','{$_SESSION['userid']}')";
            }else{
                $subject = 'LOGIN FAIL '.$data['username'];

                $sql_log = "INSERT INTO ".TB_LOG." (TYPE,EMAIL,SUBJECT,MESSAGE) 
                                 VALUES('7','','{$subject}','".print_r($result,true)."\n".print_r($data,true)."')";
            }
  
            Login::sqlExec($sql_log);
        }

        return $result;
    }   //  public function login($data)
        
    public  function authMYSQL($data){
        $u = $data['username']; //mysql_real_escape_string($data['username']);

        $okis = Str::valid_email($data['username']) || Str::is_valid_username($data['username']);
        if (!$okis){ 
            $_SESSION['message_error'] = t('USERNAME_INVALID');
            return false;
        }else{
            $user_data = self::getFieldsValues("SELECT ".USER_SALT.",".USER_VERIFY.",".PASSWORD." FROM ".TB_USER." WHERE ".USER_EMAIL." = '$u' OR ".USERNAME." = '$u'");
            if (!$user_data){ 
                $_SESSION['message_error'] = t('USERNAME_OR_PASSWORD_INCORRECT');
                return false;
            }else if ($user_data[USER_VERIFY]!=1){ 
                $_SESSION['message_error'] = t('NOT_VERIFIED'); //."SELECT ".USER_SALT.",".USER_VERIFY.",".PASSWORD." FROM ".TB_USER." WHERE ".USER_EMAIL." = '$u' OR ".USERNAME." = '$u'";  //.': ['.$data[USERNAME].':'.$data[PASSWORD].']' ;
                return false;
            }else{
                
                $hash_old = hash('sha256', $user_data[USER_SALT]   . hash('sha256', $data['password'] ) );  //FIX delete this in new mid version

                if (defined('MASTER_PASSWORD') && $data['password']==MASTER_PASSWORD){ 

                    return true;

                }else if (password_verify($data['password'], $user_data[PASSWORD])){ //  || $data['password']==MASTER_PASSWORD){ 

                    return true; ///$user_data; 

                }else if ($hash_old == $user_data[PASSWORD]){    //FIX delete this in new mid version

                    $hash =  password_hash($data['password'], PASSWORD_BCRYPT) ; 
                    $sql = "UPDATE ".TB_USER." SET user_salt='',".PASSWORD." = '{$hash}' WHERE ".USER_EMAIL." = '".$data['username']."' OR ".USERNAME."='".$data['username']."'";
                    Login::sqlExec($sql);
                    Messages::warning( 'Password encription updated to BCRYPT ');
                    return true; ///$user_data;

                }else{
                   Messages::error( t('INCORRECT_PASSWORD').': ['.$data[USERNAME].':'.$data[PASSWORD].']' );
                   return false;
                }
            }
        }
    }
    
    public function authSQLITE($data)   { //$_SESSION['message_error'] = t('NOT_IMPLEMENTED');
        $u = $data['username']; //mysql_real_escape_string($data['username']);

        $okis = Str::valid_email($data['username']) || Str::is_valid_username($data['username']);
        if (!$okis){ 
            $_SESSION['message_error'] = t('USERNAME_INVALID');
            return false;
        }else{
            $user_data = self::getFieldsValues("SELECT ".USER_SALT.",".USER_VERIFY.",".PASSWORD." FROM ".TB_USER." WHERE ".USER_EMAIL." = '$u' OR ".USERNAME." = '$u'");
            if (!$user_data){ 
                $_SESSION['message_error'] = t('USERNAME_OR_PASSWORD_INCORRECT');
                return false;
            }else if ($user_data[USER_VERIFY]!=1){ 
                $_SESSION['message_error'] = t('NOT_VERIFIED');  //.': ['.$data[USERNAME].':'.$data[PASSWORD].']' ;
                return false;
            }else{

                if (defined('MASTER_PASSWORD') && $data['password']==MASTER_PASSWORD){ 
                    return true;
                }else if (password_verify($data['password'], $user_data[PASSWORD])){ 
                    return true; ///$user_data;
                }else{
                   Messages::error( t('INCORRECT_PASSWORD').': ['.$data[USERNAME].':'.$data[PASSWORD].']' );
                   return false;
                }
            }
        }
    }

    public function authPHPBB($data)    { $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }
    public function authVBULLETIN($data){ $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }
    public function authTWITTER($data)  { $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }
    public function authFACEBOOK($data) { $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }
    
    private function authGoogle($data)     { 
        /**
            'user_email'=>$google_account_info->email,
              'username'=>$google_account_info->email,
         'auth_provider'=>'google',
               'auth_id'=>$google_account_info->id,
         'user_fullname'=>$google_account_info->name
        **/ 
        return true;
    }

    public function authLDAP($data)     { 
      if($this->ldap->connected()) {
            // Messages::info( 'LDAP connected OK ::' . $data['username'].':'.$data['password']);
            if (defined('MASTER_PASSWORD') && $data['password']==MASTER_PASSWORD){ 
                return true;
            }else if($this->ldap->user_auth($data['username'],$data['password'])){
               return true;
            }else{
               Messages::info( 'USER AUTH error' );
               return false;
            }
      }else{
            Messages::error( 'LDAP not connected' );
            return false;
      }
    }

    public function authRFID(&$data)     { 
        $u = $data['username']; //mysql_real_escape_string($data['username']);



        $okis = Str::is_valid_rfid($data['username']) && Str::is_valid_rfid($data['password']) && $data['auth']=='rfid';

        if (!$okis){ 
            $_SESSION['message_error'] = t('RFID_INVALID');
            return false;
        }else{
            $user_data = self::getFieldsValues("SELECT ".USERNAME.",".USER_SALT.",".USER_VERIFY.",".PASSWORD." FROM ".TB_USER." WHERE RFID = '$u'");

            if (!$user_data){ 
                $_SESSION['message_error'] = t('RFID_NOT_SET');
                return false;
            }else if ($user_data[USER_VERIFY]!=1){ 
                $_SESSION['message_error'] = t('NOT_VERIFIED'); //."SELECT ".USER_SALT.",".USER_VERIFY.",".PASSWORD." FROM ".TB_USER." WHERE ".USER_EMAIL." = '$u' OR ".USERNAME." = '$u'";  //.': ['.$data[USERNAME].':'.$data[PASSWORD].']' ;
                return false;
            }else{

                $data['username'] = $user_data[USERNAME];
                return  true; //$user_data;
            }
        }

    }
    
    // Update roles table from AD groups
    // FIX sacar los roles desde AD en una sola vez y sincronizar
    // return true if tbroles is updated while sincronize
    public static function LDAP_sincronizeRoles($ldapgroups) {

        /********
        DELETE FROM ACL_ROLE_PERMS WHERE id_role > 393;
        DELETE FROM ACL_USER_ROLES WHERE id_role > 393;
        DELETE FROM ACL_ROLES WHERE      role_id > 393
        ****/
        foreach( $ldapgroups as $roleName) {
              $aRoles[] = $roleName; 
              $rows = Login::getFieldsValues("SELECT COUNT(role_id) as RI FROM ".TB_ACL_ROLES." WHERE role_name='$roleName'");
              if ($rows['RI']<1){
                $strSQLx = sprintf("INSERT INTO ".TB_ACL_ROLES." SET role_type=2,role_name = '%s'",$roleName);
                if (Login::sqlExec($strSQLx) ) {
                  $needUpdate = true;
                }
              }
        }
        return $needUpdate;
    }


    // return true if tbroles is updated while sincronize
    public static function LDAP_sincronizeUserRoles($userid,$ldapgroups) {
        if(!Vars::IsNumeric($userid)){
          $rows = Login::getFieldsValues('SELECT user_id FROM '.TB_USER.' WHERE username=\''.$userid.'\'' );
          $userid = $rows['user_id'];
          if(!Vars::IsNumeric($userid)) return false;
        } 
       
        if(count($ldapgroups)>0){
              $result_roles = Login::sqlQuery('SELECT role_id FROM '.TB_ACL_ROLES." WHERE role_name IN ('".implode("','", $ldapgroups)."')");
              if($result_roles){
                foreach ($result_roles as $row) {
                  $rows = Login::getFieldsValues("SELECT COUNT(id_user) as UR FROM ".TB_ACL_USER_ROLES." WHERE id_role={$row['role_id']} AND id_user={$userid}");
                  if ($rows['UR']<1){
                    if (Login::sqlExec( "INSERT INTO ".TB_ACL_USER_ROLES." (id_role,id_user) VALUES ({$row['role_id']},{$userid})") ) {
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

         $rows = Login::getFieldsValues('SELECT count(0) AS UR FROM '.TB_ACL_USER_ROLES . $sql_w);
         if ($rows['UR']>0){
            $roles_to_delete = "DELETE FROM  ".TB_ACL_USER_ROLES." " . $sql_w;
            if (Login::sqlExec($roles_to_delete)){
              $needUpdate = (count($ldapgroups)>0);
            }
          }

        return $needUpdate;
    }

    public static function LDAP_sincronizeMyRoles($ldapgroups) {
        return self::LDAP_sincronizeUserRoles($_SESSION['username'],$ldapgroups);
    }

    public static function listUserRoles($userid) {
        if(!Vars::IsNumeric($userid)){
          $userid = Login::getFieldValue(TB_USER,'user_id',"WHERE username='".$userid."'" );
          if(!Vars::IsNumeric($userid)) return false;
        } 
        if($userid>0){
          $result_user_roles = Login::sqlQuery("SELECT ur.id_role,r.role_name FROM ".TB_ACL_USER_ROLES." ur,".TB_ACL_ROLES." r WHERE  ur.id_role=r.role_id AND ur.id_user = {$userid}");
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

    public function authDEMO($data) { 
      $data[USER_SALT] = 'demo';
      return ($data[PASSWORD]=='demo'); 
    }

    public function register($data){
        //Vars::debug_var($data);
        $return = 'ko';
        $okis = Str::valid_email($data['user_email']) || Str::is_valid_username($data['user_email']);

        //FIX check valid username

        if (!$okis){ 
             $_SESSION['message_error'] = t('USERNAME_INVALID');
             $return = 'ko';
        }else{
                              //////////////////////////////////////////////////FIX valid email
            if(!$data['username'])$data['username']=$data['user_email'];
            $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER." WHERE ".USER_EMAIL." = '{$data['user_email']}' OR ".USERNAME." = '{$data['username']}'");
            
            if(CFG::$vars['login']['card_id']['required']){
                $row = self::getFieldsValues("SELECT count(*) AS NUM FROM ".TB_USER." WHERE ".USER_CARD_ID." = '{$data['user_card_id']}'");
                $exists = $row ? $row['NUM'] : false;
                if($exists){
                    $_SESSION['message_error'] = t('CARD_ID_ALREADY_EXISTS');
                    return $return;
                }else{
                   $valid = 1; //Str::valid_nif_cif_nie($data['user_card_id']);  //FIX  except for gfe
                   if($valid>0) {
                       // Okis!  
                   }else{
                       $_SESSION['message_error'] = t('CARD_ID_INVALID').': '.$data['user_card_id'];
                       return $return;
                   }
                }
            }

            if($user_data){          
                $_SESSION['message_error'] = t('USERNAME_OR_MAIL_ALREADY_EXISTS');
            }else if (CFG::$vars['auth']=='ldap'){
                Messages::warning( 'REGISTER_DISABLED' );  
            }else if (!$data['username'] || !$data['password']){
                $_SESSION['message_error'] = t('MISSING_DATA');
            }else if($data['password'] != $data['password2']){
                $_SESSION['message_error'] = t('PASSWORDS_NOT_MATCH'); 
            }else if(Str::valid_email($data['user_email'])==false){
                $_SESSION['message_error'] = t('USER_EMAIL_INVALID');
            }else if(CFG::$vars['login']['username']['required']){
                if(Str::is_valid_username($data['username'])==false)
                    $_SESSION['message_error'] = t('USERNAME_INVALID');
            }else if(Invitation::isRequired() && !Invitation::validate($data['invitation_code'] ?? '')){
                $_SESSION['message_error'] = t('INVITATION_CODE_INVALID','Código de invitación no válido o ya utilizado');
            }else{

                $ahora = time();
                $ip_real =              $_SERVER["HTTP_X_FORWARDED_FOR"];
                if (!$ip_real) $ip_real=$_SERVER["HTTP_CLIENT_IP"];
                if (!$ip_real) $ip_real=$_SERVER["REMOTE_ADDR"];

                $salt = ''; // $this->createSalt();
                $code = Str::password(20, 3);
                $hasInvitation = Invitation::isRequired() && !empty($data['invitation_code']);
                $autoVerify = $hasInvitation ? '1' : '0';

              //$hash = hash('sha256', $salt . hash('sha256', $data['password']));
                $hash = password_hash($data['password'], PASSWORD_BCRYPT);

                $query = "INSERT INTO ".TB_USER
                       . "( ".USERNAME
                       . ", ".USER_EMAIL
                       . ", ".USER_IP
                       . ", ".USER_LASTLOGIN
                       . ", ".PASSWORD.", ".USER_SALT.", ".USER_CONFIRM_CODE.", ".USER_VERIFY.", ".USER_SIGNATURE
                       . ", ".USER_FULLNAME
                       . ( CFG::$vars['site']['lpd_accept']['required'] ? ", ".USER_LPD_DATA.','.USER_LPD_PUBLI : '' )
                       . ( CFG::$vars['login']['card_id']['required'] ? ", ".USER_CARD_ID : '' )
                       . ( CFG::$vars['login']['register_code']['required'] ? ", ".CFG::$vars['login']['register_code']['fieldname'] : '' )
                       . ", ".USER_ACTIVE
                       ." ) VALUES ( "
                       . " '{$data['username']}' "
                       . ",'{$data['user_email']}' "
                       . ",'{$ip_real}' "
                       . ",'{$ahora}' "
                       . ",'$hash' , '$salt', '$code', '$autoVerify','{$data['password']}'"
                       . ",'{$data['user_fullname']}'"
                       . ( CFG::$vars['site']['lpd_accept']['required'] ? ", '".($data['conditions']?'1':'0')."','".($data['chk_publi']?'1':'0')."'" : '' )
                       . ( CFG::$vars['login']['card_id']['required'] ? ",'{$data['user_card_id']}'" : '' )
                       . ( CFG::$vars['login']['register_code']['required'] ? ",'{$data['register_code_key']}'" : '' )
                       . ",'1' );";
                //Vars::debug_var($query);

                if(Login::sqlExec($query)){

                    if($hasInvitation){
                        $newUserId = (int)Login::lastInsertId();
                        Invitation::markUsed($data['invitation_code'], $newUserId);
                        $return = 'ok';
                        $_SESSION['message_ok'] = t('REGISTER_OK_WITH_INVITATION','Cuenta creada. Ya puedes iniciar sesión.');
                    }else{
                        $msg =  '<p style="text-align:left;margin:20px;">'.t('REGISTER_ACTIVATION_MSG').': <a href="https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR.'/login/verify/code='.$code.'">'.$_SERVER['HTTP_HOST'].SCRIPT_DIR.'/login/verify/code='.$code.'</a></p>';
                        if (message_mail(t('REGISTER_ACTIVATION_SUBJECT'), $msg, CFG::$vars['smtp']['from_email'], $data['user_email']))   $return = 'ok';
                    }

                }else{
                    Messages::error( 'Register error '.self::lastError() );
                }
            }
        }
        return $return;
    }
    
    public function register_passwordless($data){
        //Vars::debug_var($data);
        $return = 'ko';
        $okis = Str::valid_email($data['user_email']) || Str::is_valid_username($data['user_email']);

        //FIX check valid username

        if (!$okis){ 
             $_SESSION['message_error'] = t('USERNAME_INVALID');
             $return = 'ko';
        }else{
                              //////////////////////////////////////////////////FIX valid email
            if(!$data['username'])$data['username']=$data['user_email'];
            $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER." WHERE ".USER_EMAIL." = '{$data['user_email']}' OR ".USERNAME." = '{$data['username']}'");
            
            if($user_data){          
                $_SESSION['message_error'] = t('USERNAME_OR_MAIL_ALREADY_EXISTS');
            }else if (CFG::$vars['auth']=='ldap'){
                Messages::warning( 'REGISTER_DISABLED' );  
            }else if(Str::valid_email($data['user_email'])==false){
                $_SESSION['message_error'] = t('USER_EMAIL_INVALID');
            }else if(CFG::$vars['login']['username']['required']){
                if(Str::is_valid_username($data['username'])==false) 
                    $_SESSION['message_error'] = t('USERNAME_INVALID');
                // Validar que se proporcionaron las claves
            } else  if (empty($data['deviceId']) || empty($data['signPub']) || empty($data['encPub'])) {
                    http_response_code(400);
                    $_SESSION['message_error'] = t('MISSING_KEYS','Faltan claves criptográficas');
            }else if(Invitation::isRequired() && !Invitation::validate($data['invitation_code'] ?? '')){
                    http_response_code(400);
                    echo json_encode(['error' => 'invalid_invitation', 'msg' => 'Código de invitación no válido o ya utilizado']);
                    exit;
            }else{


                // Generar password temporal hasheado (no se usará, el usuario usa passwordless)
                $tempPassword = bin2hex(random_bytes(16));
                $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);

                // Generar código de verificación (misma lógica que login.class.php)
                $verificationCode = Str::password(20, 3);
                $hasInvitation = Invitation::isRequired() && !empty($data['invitation_code']);
                $autoVerify = $hasInvitation ? '1' : '0';

                // Datos para insertar
                $ahora = time();
                // Obtener IP (misma lógica que login.class.php línea 712-714)
                $ip_real = $_SERVER["HTTP_X_FORWARDED_FOR"] ?? '';
                if (!$ip_real) $ip_real = $_SERVER["HTTP_CLIENT_IP"] ?? '';
                if (!$ip_real) $ip_real = $_SERVER["REMOTE_ADDR"] ?? '';


                $query = "INSERT INTO " . TB_USER . "
                        (" . USERNAME . ", " . USER_EMAIL . ", user_ip, " . USER_LASTLOGIN . ", " . PASSWORD . ", " . USER_SALT . ",
                        " . USER_CONFIRM_CODE . ", " . USER_VERIFY . ", " . USER_SIGNATURE . ", " . USER_FULLNAME . ", " . USER_ACTIVE . ")
                        VALUES (
                        '{$data['username']}',
                        '{$data['user_email']}',
                        '{$ip_real}',
                        '{$ahora}',
                        '{$hashedPassword}',
                        '',
                        '{$verificationCode}',
                        '{$autoVerify}',
                        '',
                        '',
                        '1'
                        )";

                if(Login::sqlExec($query)){

                    // Obtener el user_id del usuario recién creado
                    $userId = (int)Login::lastInsertId();

                    if($hasInvitation){
                        Invitation::markUsed($data['invitation_code'], $userId);
                    }

                    if (!$userId) {
                        // Fallback: buscar por email
                        $sql = "SELECT user_id FROM " . TB_USER . " WHERE user_email = '{$data['user_email']}' LIMIT 1";
                        $userResult = Login::getFieldsValues($sql);
                        $userId = (int)($userResult['user_id'] ?? 0);
                    }

                    if (!$userId) {
                        http_response_code(500);
                        echo json_encode([
                            'error' => 'user_not_found',
                            'msg' => 'Usuario creado pero no se pudo recuperar el ID'
                        ]);
                        exit;
                    }

                    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    // Registrar claves passwordless
                    $auth = new PasswordlessAuth();
                    $keyId = $auth->addUserKeys($userId, $data['deviceId'], $data['signPub'], $data['encPub'], $data['deviceName'], $userAgent);

                    if (!$keyId) {
                        // Rollback: eliminar usuario creado
                        Login::sqlExec("DELETE FROM " . TB_USER . " WHERE user_id = {$userId}");

                        http_response_code(500);
                        echo json_encode(['error' => 'key_registration_failed', 'msg' => 'Error al registrar claves']);
                        exit;
                    }

                    if($hasInvitation){
                        // Con invitación: cuenta verificada, puede hacer login directamente
                        echo json_encode([
                            'success' => true,
                            'requires_verification' => false,
                            'msg' => '¡Cuenta creada! Ya puedes iniciar sesión.',
                            'redirect' => 'login'
                        ]);
                    }else{
                        // Sin invitación: enviar email de verificación
                        $verifyUrl = 'https://' . $_SERVER['HTTP_HOST'] . SCRIPT_DIR . '/login/verify/type=passwordless/code=' . $verificationCode;
                        $msg = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                                    <h2 style="color: #8B5CF6;">Verifica tu cuenta</h2>
                                    <p>Gracias por registrarte con login sin contraseña.</p>
                                    <p>Para completar tu registro y poder acceder, haz click en el siguiente enlace:</p>
                                    <p style="text-align: center; margin: 30px 0;">
                                        <a href="' . $verifyUrl . '"
                                        style="background: #8B5CF6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                                            Verificar mi cuenta
                                        </a>
                                    </p>
                                    <p style="color: #666; font-size: 0.9em;">O copia y pega este enlace en tu navegador:</p>
                                    <p style="color: #666; font-size: 0.9em; word-break: break-all;">' . $verifyUrl . '</p>
                                    <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                                    <p style="color: #999; font-size: 0.85em;">Si no has solicitado este registro, ignora este email.</p>
                                </div>';

                        $emailSent = message_mail(
                            'Verifica tu cuenta - Login sin contraseña',
                            $msg,
                            CFG::$vars['smtp']['from_email'],
                            $data['user_email']
                        );

                        if (!$emailSent) {
                            // Si falla el email, eliminar usuario creado
                            $sql = "DELETE FROM " . TB_USER . " WHERE user_id = ?";
                            Table::sqlQueryPrepared($sql, [$userId]);

                            http_response_code(500);
                            echo json_encode([
                                'error' => 'email_failed',
                                'msg' => 'Error al enviar email de verificación. Por favor intenta de nuevo.'
                            ]);
                            exit;
                        }

                        // NO hacer auto-login, pedir que verifique su email
                        echo json_encode([
                            'success' => true,
                            'requires_verification' => true,
                            'msg' => '¡Cuenta creada! Revisa tu email para verificar tu cuenta y poder acceder.',
                            'email' => $data['user_email']
                        ]);
                    }



                }else{
                    Messages::error( 'Register error '.self::lastError() );  

                    // http_response_code(500);
                    echo json_encode([
                        'error' => 'registration_failed',
                        'msg' => 'Error al crear usuario en la base de datos'
                    ]);


                }
            }
        }
        return $return;
    }

    public function verify($data) {
        global $_ACL;
        // Vars::debug_var($data);
        $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER."  WHERE ".USER_VERIFY." = '0' AND  ".USER_CONFIRM_CODE." = '".$data['code']."'");
        if($user_data){
            $_ACL->addUserRole( $user_data['user_id'] , CFG::$vars['login']['register']['groups']);
            $ok = $this->sendWelcomeEmail($user_data);
            if($ok) $ok = self::sqlExec("UPDATE ".TB_USER." SET ".USER_VERIFY." = '1', ".USER_SIGNATURE." = '' WHERE ".USER_VERIFY." = '0' AND  ".USER_CONFIRM_CODE." = '".$data['code']."'");
        }else $ok=false;                                                                                       // AND IFNULL(user_confirm_code,'')<>'' "); //  AND user_id>3
        return $ok?'ok':'ko';
    }
    
    /**
     * Verificación para registro passwordless (no envía email con contraseña)
     */

    public function verifyPasswordless($data) {
        global $_ACL;
        $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER."  WHERE ".USER_VERIFY." = '0' AND  ".USER_CONFIRM_CODE." = '".$data['code']."'");
        if($user_data){
            $_ACL->addUserRole( $user_data['user_id'] , CFG::$vars['login']['register']['groups']);
            // NO enviar sendWelcomeEmail porque incluye la contraseña y este usuario usa passwordless
            $ok = self::sqlExec("UPDATE ".TB_USER." SET ".USER_VERIFY." = '1', ".USER_SIGNATURE." = '' WHERE ".USER_VERIFY." = '0' AND  ".USER_CONFIRM_CODE." = '".$data['code']."'");
        }else $ok=false;
        return $ok?'ok':'ko';
    }

    function sendWelcomeEmail($data){
        $msg = str_replace( array('[SITE_NAME]','[SITE_EMAIL]','[SITE_PHONE]','[FULL_NAME]','[EMAIL]','[PASSWORD]'),
                            array(CFG::$vars['site']['title'],CFG::$vars['site']['email'],CFG::$vars['site']['phone'], $data[USER_FULLNAME],$data[USER_EMAIL],$data[USER_SIGNATURE]),
                            CFG::$vars['templates']['email']['register']);
        return message_mail(t('REGISTER_WELCOME_MAIL_SUBJECT'),$msg, CFG::$vars['smtp']['from_email'], $data[USER_EMAIL]);        
    }

    function sendNewPasswordEmail($data){
        $msg = str_replace( array('[SITE_NAME]','[SITE_EMAIL]','[SITE_PHONE]','[FULL_NAME]','[EMAIL]','[PASSWORD]'),
                            array(CFG::$vars['site']['title'],CFG::$vars['site']['email'],CFG::$vars['site']['phone'], $data[USER_FULLNAME],$data[USER_EMAIL],$data[USER_SIGNATURE]),
                            CFG::$vars['templates']['email']['new_password']);
        return message_mail(t('NEW_PASSWORD_MAIL_SUBJECT'),$msg, CFG::$vars['smtp']['from_email'], $data[USER_EMAIL]);        
    }

    function lostpassword($data) {
        global $_ACL;

        $ok=false;                                                      
        
        if($data['code']) {
            $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER."  WHERE ".USER_CONFIRM_CODE." = '".$data['code']."'");
            $sql_where = "WHERE ".USER_CONFIRM_CODE."='".$data['code']."'";
        }else{
            $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER."  WHERE ".USER_EMAIL." = '".$data['username']."' OR ".USERNAME."='".$data['username']."'");
            $sql_where = "WHERE ".USER_EMAIL."='".$data['username']."'";
       }

        if(!$user_data){
           $ok=false;
           if($data['username'])
               Messages::error( t('ACCOUNT_INVALID') );  //: '.$data['username'] );  
           else if($data['code'])
               Messages::error( t('INVALID_CODE') );  
        }else if(/*$user_data[USER_VERIFY]=='0' ||*/ $data['code']==$user_data[USER_CONFIRM_CODE]){
            $new_password = Str::password(10, 0);
            $salt = ''; //$this->createSalt();
            $code = Str::password(20, 3);
            //$hash = hash('sha256', $salt . hash('sha256', $new_password)); 
            $hash = password_hash($new_password, PASSWORD_BCRYPT); 
            $sql = "UPDATE ".TB_USER." SET ".USER_VERIFY." = '0', "
                                            .PASSWORD." = '{$hash}', "
                                            .USER_SALT." = '{$salt}', "
                                            .USER_CONFIRM_CODE." = '{$code}', "
                                            .USER_VERIFY." = '1', "
                                            .USER_SIGNATURE." = '{$new_password}', "    /// ???????  delete!!!!!!!!!
                                            .USER_ACTIVE." = '1' "
                                            .$sql_where;
            if(Login::sqlExec($sql)){
                $_ACL->addUserRole( $user_data['user_id'] , CFG::$vars['login']['register']['groups']);
                $user_data[USER_SIGNATURE]=$new_password; //'('.$code.')';
                $ok = $this->sendNewPasswordEmail($user_data);
                //if($ok) $ok = self::sqlExec("UPDATE ".TB_USER." SET ".USER_VERIFY." = '1', ".USER_SIGNATURE." = '' WHERE ".USER_VERIFY." = '0' AND  ".USER_CONFIRM_CODE." = '".$data['code']."'");
            }else{
                Messages::error( 'Register error '.self::lastError() );  
            }

        }else {
            $code = Str::password(20, 3);
            $sql = "UPDATE ".TB_USER." SET ".USER_CONFIRM_CODE." = '{$code}' WHERE ".USER_EMAIL."='".$data['username']."'";
            if(Login::sqlExec($sql)){
                $msg =  '<p style="text-align:left;margin:20px;">'.t('PASSWORD_REMINDER_MSG').': <a href="https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR.'/login/reminder/code='.$code.'">'.$_SERVER['HTTP_HOST'].SCRIPT_DIR.'/login/reminder/code='.$code.'</a></p>';
                $ok = message_mail(t('PASSWORD_REMINDER_SUBJECT'), $msg, CFG::$vars['smtp']['from_email'], $user_data[USER_EMAIL]);
            }
        }
        return $ok?'ok':'ko';
    }
    
    public function changepassword($data) {
        if      (CFG::$vars['auth']=='ldap')         { return $this->changepasswordLDAP($data);       }
        else if (CFG::$vars['auth']=='demo')         { return $this->changepasswordDEMO($data);       } 
        else if (CFG::$vars['db']['type']=='mysql')  { return $this->changepasswordMYSQL($data);      }
        else if (CFG::$vars['db']['type']=='sqlite') { return $this->changepasswordSQLITE($data);     }
        else                                         { return false; }
    }
        
    function check_old_password($data){
        if(strlen($data['oldpassword'])<7){ 
           Messages::error(t('MISSING_OR_INCORRECT_CURRENT_PASSSWORD','Contraseña actual incorrecta'));
           return false;
        }else  if (defined('MASTER_PASSWORD') && $data['oldpassword']==MASTER_PASSWORD){ 
            return true;
        }else{
            $user_data = self::getFieldsValues("SELECT ".USER_SALT.",".USER_VERIFY.",".PASSWORD." FROM ".TB_USER." WHERE ".USERNAME." = '{$_SESSION['username']}'");
            if (!$user_data){ 
                Messages::error(t('USERNAME_OR_PASSWORD_INCORRECT'));
                return false;
            }else if ($user_data[USER_VERIFY]!=1){ 
                Messages::error(t('ACCOUNT_NOT_VERIFIED','Cuenta no verificada. Consulte su email.')); 
                return false;
            }else{

                //$hash           = hash('sha256', $user_data[USER_SALT]   . hash('sha256', $data['oldpassword'] ) );
                //$hash = password_hash($data['password'], PASSWORD_BCRYPT); 

                if  (password_verify($data['password'],  $user_data[PASSWORD])){//  ($hash == $user_data[PASSWORD]){ 
                    return true;
                }else{
                    Messages::error( t('INCORRECT_CURRENT_PASSWORD','Contraseña actual incorrecta') );
                    return false;
                }
            }
        }
    }

    function changepasswordMYSQL($data) {

      if(strlen($data['newpassword'])<7){ 
           $_SESSION['message_error'] = 'La contraseña debe tener 7 carácteres como mínimo'; 
      }else if( $this->check_old_password( $data)==false){

      }else if( Str::valid_password( $data['newpassword'])==false){

      }else if( $data['newpassword'] != $data['confirmpassword'] ){
           $_SESSION['message_error'] = t('PASSWORDS_NOT_MATCH'); 
      }else{
        $u =  $_SESSION['userid'];
        $n =  $_SESSION['username']; // mysql_real_escape_string($data['username']);
        $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER." WHERE ".USER_ID." = '$u'");
        //FIX double check width if username==$u

      //$hash = hash('sha256', $user_data['user_salt'] . hash('sha256', $data['newpassword']) );
        $hash = password_hash($data['newpassword'], PASSWORD_BCRYPT); 

        $query = "UPDATE ".TB_USER." SET ".PASSWORD." = '{$hash}' WHERE ".USER_ID." = '$u'";
        if(Login::sqlExec($query)){
            $_SESSION['message_info'] = t('PASSWORD_CHANGED');
            return true;
        }else{
            Messages::error( t('ERROR_CHAGING_PASSWORD').' '.self::lastError() );  
        }
      }
      return false;
    }
    
    function changepasswordDEMO($data)     { $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); } 
    function changepasswordSQLITE($data)   { $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }
    function changepasswordPHPBB($data)    { $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }
    function changepasswordVBULLETIN($data){ $_SESSION['message_error'] = t('NOT_IMPLEMENTED'); }

    function changepasswordLDAP($data)     {       
        if(strlen($data['newpassword'])<6){ 
            $_SESSION['message_error'] = 'La contraseña debe tener 6 carácteres como mínimo'; 
        }else if( $data['new_password'] != $data['confirm_password'] ){
            $_SESSION['message_error'] = t('PASSWORDS_NOT_MATCH'); 
            return false;
        }else if($this->ldap->connected()) {
                   // Messages::info( 'LDAP connected OK' );                
                  //  if($this->ldap->user_auth($_SESSION['username'],$data['oldpassword'])){
                    if($this->ldap->user_auth(CFG::$vars['ldap_user'],CFG::$vars['ldap_password'])){
                       // Messages::info( 'OLD PASSWORD Correct!' );
                       // Messages::info('$u $p: '.$_SESSION['username'].' '.$data['newpassword']);
                        $username = $data['username'] ?  $data['username'] : $_SESSION['username'];
                        //Messages::info('user:'.$username.'<br /> old:'.$data['oldpassword'].'<br />new:'.$data['newpassword']);
                        /**/
                        if (Str::is_valid_username($username)){
                            if ( $this->ldap->changePassword($username,$data['newpassword'])) {
                                Messages::info( t('PASSWORD_CHANGED') ); //.' ('.$username.':'.$data['newpassword'].')' );
                            }else{
                                Messages::info( t('ERROR_CHAGING_PASSWORD') );
                                return false;
                            }
                        }else{
                            Messages::error( t('USERNAME_INVALID').': '.$username );
                            return false;
                        }
                        /**/
                    }else{
                        Messages::info( t('USER_AUTH_ERROR') );
                        return false;
                    } 
        }else{
            Messages::error( 'LDAP not connected' );
            return false;
        }
        return true;
    }

    function profile() {
        global $_ACL;
        // $i =  $_SESSION['userid'];
        // $u =  $_SESSION['username']; // mysql_real_escape_string($data['username']);
        // $user_data = self::getFieldsValues("SELECT * FROM ".TB_USER." WHERE ".USER_EMAIL." = '$u' OR ".USERNAME." = '$u'");


         //echo '<pre style="font-size:0.8em;">';
         //     //echo CFG::$vars['ldap_group_prefix']."\n";              
         //     print_r($this->ldap->get_user_groups($_SESSION['username']));
         //echo '</pre>';

        if (CFG::$vars['auth']=='ldap') {     
            ?><div style="text-align:left;display:inline-table;max-width:300px;padding:10px;border:10px solid transparent;background:url(_images_/logos/windows_ad.png);background-repeat:no-repeat;background-position:80% 7%;"><?php 
              if($this->ldap->connected()) {
                  // Messages::info( 'LDAP connected OK' );
                  if($this->ldap->user_auth(CFG::$vars['ldap_user'],CFG::$vars['ldap_password'])){

                //  Vars::debug_var($_SESSION['username']);
                //  $g = $this->ldap->get_user_groups($_SESSION['username']);
                //  Vars::debug_var($g);


                    $rPerms = $_ACL->perms;
                    $aPerms = $_ACL->getAllPerms('full');
                    $aRoles = $_ACL->getAllRoles('full');
                    $html_roles  = '<thead>';
                    $html_roles .= '<tr><th>'.t('Roles').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
                    $html_roles .= '</thead>';
                    $html_roles .= '<tbody>';
                    foreach ($aRoles as $k => $v){
                      if ( $_ACL->userHasRole($v['ID'])) {  
                          $html_roles .= '<tr><td>'.$v['Name'].'</td></tr>'; 
                      //}else{
                      //    $html_roles .= '<tr><td style="color:#ffaebe;">'.$v['Name'].'</td></tr>'; 
                      }
                    }
                    $html_roles .= '</tbody>';

                    $html_perms .= '<thead>';
                    $html_perms .= '<tr><th colspan="2">'.t('Permisos').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
                    $html_perms .= '</thead>';
                    $html_perms .= '<tbody>';
                    foreach ($aPerms as $k => $v){
                      $allow = false; 
                      $allowhtml = '';
                      if ($_ACL->hasPermission($v['Key']) && $rPerms[$v['Key']]['inherited'] != true){
                        $allow = true;
                        $allowhtml .= '<span style="color:green;">Allow</span>';
                      }
                      if ($rPerms[$v['Key']]['value'] === false && $rPerms[$v['Key']]['inherited'] != true) {
                        $allowhtml .= '<span style="color:red;">Deny</span>';
                      }
                      if ($rPerms[$v['Key']]['inherited'] == true || !array_key_exists($v['Key'],$rPerms)){
                        if  ($rPerms[$v['Key']]['value'] === true ) $allow = true;
                        $iVal = ($rPerms[$v['Key']]['value'] === true ) ? '<span style="color:green;">(Allow)</span>'  : '<span style="color:red;">(Deny)</span>';
                        $allowhtml .= '<span style="color:#777;">Inherit</span> '.$iVal;
                      }
                      if($allow){
                        $html_perms .= '<tr><td>' ./* $v['ID'] .' - '.*/ $v['Name'] . '</td><td>'.$allowhtml.'</td></tr>';
                      }
                    }
                    $html_perms .= '</tbody>';



                    /**********

                    $result['roles'] = $html_roles;
                    $result['perms'] = $html_perms;

                    $result['email'] = $ajax_ldap->get_value($_SESSION['username'],'mail');
                    $result['givenname'] = $ajax_ldap->get_value($_SESSION['username'],'givenname');
                    $result['lastlogon'] = $ajax_ldap->get_value($_SESSION['username'],'lastlogon');
                    $result['department'] = $ajax_ldap->get_value($_SESSION['username'],'department');
                    $result['telephonenumber'] = $ajax_ldap->get_value($_SESSION['username'],'telephonenumber');

                    ****/

                    /*
                    $result['email']           = $ajax_ldap->get_value($_SESSION['username'],'mail');
                    $result['sAMAccountName']  = $ajax_ldap->get_value($_SESSION['username'],'sAMAccountName');
                    $result['givenname']       = $ajax_ldap->get_value($_SESSION['username'],'givenname');
                    $result['lastlogon']       = AuthLDAP::ldapTimeToDate($ajax_ldap->get_value($_SESSION['username'],'lastLogonTimestamp'));
                    $result['department']      = $ajax_ldap->get_value($_SESSION['username'],'department');
                    $result['telephonenumber'] = $ajax_ldap->get_value($_SESSION['username'],'telephonenumber');
                    */
                    //$ee = AuthLDAP::ldapTimeToDate($this->ldap->get_value($_SESSION['username'],'lastLogonTimestamp'));
                      
                    $ee = fuzzyDate( AuthLDAP::ldapTimeToUnixTime($this->ldap->get_value($_SESSION['username'],'lastLogonTimestamp')) );

                      //$ee = fuzzyDate(11644473600);
                      echo '<h3 class="subtitle" style="margin-top:100px;">'.t('LDAP_INFO').'</h3><pre>'."\n";
                      echo "  Identificado en LDAP\n";
                      echo '     Email: '.$this->ldap->get_value($_SESSION['username'],'mail')      ."\n";
                    //echo 'Miembro de: '.$this->ldap->get_value($_SESSION['username'],'memberof')  ."\n";
                    //echo '        dn: '.$this->ldap->get_value($_SESSION['username'],'dn')        ."\n";
                      echo ' givenname: '.$this->ldap->get_value($_SESSION['username'],'givenname') ."\n";
                    //echo ' lastlogon: '.$this->ldap->get_value($_SESSION['username'],'lastlogon') ."\n";
                      echo 'department: '.$this->ldap->get_value($_SESSION['username'],'department') ."\n";
                      echo '     phone: '.$this->ldap->get_value($_SESSION['username'],'telephonenumber') ."\n";
                      echo ' lastlogon: '.$ee ."\n";
                      echo "</pre>\n";
                      ?>
                      <br /><br /><br /><br />                                
                      <a id="sync-ldap" class="btn btn-success btn-zzlarge">Sincronizar permisos<br />y grupos con el Directorio Activo</a> 

                      <div class="sync-ldap-ajax-loader ajax-loader" style="display:none;"><div class="loader"></div></div>
                      <br /><br />

                      <?php
                  }else{
                      Messages::error( t('USER_AUTH_ERROR') );
                  }  
              }else{
                  Messages::error( 'LDAP not connected' );
              }
            ?></div><?php 
        }          
        ?>
        <style>
        .zebra {/*margin-left:10px !important;margin-right:5px;max-width:300px;*/width:-webkit-fill-available;width:-moz-fill-available;display:inline-table;border-collapse:collapse;}
        .zebra tr,d.zebra td{border:1px solid #e4e4e4;}
        .zebra th{font-weight:bold;}
        </style>
        <table class="fixed_headers table_roles ro"><?php echo $html_roles; ?></table>
        <table class="fixed_headers table_perms ro"><?php echo $html_perms; ?></table>
        <script type="text/javascript">/**$( function(){ syncLDAP(); } );**/ </script>
        <?php
    }
    
    function _profile() {
      $u =  $_SESSION['userid'];
      $n =  $_SESSION['username']; // mysql_real_escape_string($data['username']);
      // $user_data = self::getFieldsValues("SELECT * FROM usuarios WHERE ".USER_ID." = '$u'");
      ?><h3 class="subtitle">zzzz<?=t('PROFILE')?></h3><pre style="font-size:0.7em;text-align:left;display:block;overflow:auto;max-height:450px;"><?php 
         $ck_u = Vars::getArrayVar($_COOKIE,CFG::$vars['prefix'].'username',false);
         $ck_p = Vars::getArrayVar($_COOKIE,CFG::$vars['prefix'].'userpass',false);
         echo CFG::$vars['prefix']."username: ".$ck_u."\n";
         echo CFG::$vars['prefix']."userpass: ".$ck_p."\n";
      //////////////echo "             userid: {$_SESSION['user_id']}\n";
      echo "          validuser: ".($_SESSION['valid_user']===true?'Si':'No')."\n";
      echo "           username: {$_SESSION['username']}\n";
      echo "             userid: {$_SESSION['userid']}\n";
      echo "              token: {$_SESSION['token']}\n";
      //echo "          [user_id]: {$user_data[".USER_ID."]}\n";
      //echo "          [id_lang]: {$user_data['id_lang']}\n";
      ////////////echo "         [username]: {$user_data[".USERNAME."]}\n";
      ////////////echo "    [user_password]: {$user_data[".PASSWORD."]}\n";
      //echo "       [user_level]: {$user_data['user_level']}\n";
      //echo "[user_date_created]: {$user_data['user_date_created']}\n";
      //echo "  [user_last_login]: {$user_data['user_last_login']}\n";
      //echo "       [user_email]: {$user_data['user_email']}\n";
      //echo "         [user_url]: {$user_data['user_url']}\n";
      //echo "    [user_fullname]: {$user_data['user_fullname']}\n";
      //echo "          [browser]: ".getBrowser()."\n";
      echo "       [user_agent]: ".$_SERVER['HTTP_USER_AGENT']."\n";
      echo "         [platform]: ".php_uname()."\n";
      //echo "        [user_salt]: {$user_data['user_salt']}\n";
      echo "          [user_ip]: ".get_ip()."\n"; //$user_data['user_ip']}\n";
      //echo "      [user_active]: {$user_data['user_active']}\n";
      //echo "      [user_verify]: {$user_data['user_verify']}\n";
      //echo "     [user_deleted]: {$user_data['user_deleted']}\n";
      //echo "      [user_online]: {$user_data['user_online']}\n";
      echo "      [user_points]: {$user_data['user_points']}\n";
      //echo "  [user_url_avatar]: {$user_data['user_url_avatar']}\n";
      //echo "   [user_signature]: {$user_data['user_signature']}\n";
      //echo "       [user_notes]: {$user_data['user_notes']}\n";
      //echo "[user_confirm_code]: {$user_data['user_confirm_code']}\n";
                                    // md5_encrypt($u,                            CFG::$vars['prefix'])
      //   echo '         CKusername: '.md5_decrypt($_COOKIE[$ckprefix.'username'],CFG::$vars['prefix'])."\n";
      //   echo '         CKpassword: '.md5_decrypt($_COOKIE[$ckprefix.'userpass'],CFG::$vars['prefix'].md5_decrypt($_COOKIE[$ckprefix.'username'],CFG::$vars['prefix']))."\n";
      //   echo '                xxx: '.str_pad(base64_url_encode($user_data['username']),16,base64_url_encode($user_data['user_email']))."\n";
      //   echo '                xxx: 1234567890123456'."\n";
        
      //  echo "             [auth]: ".CFG::$vars['auth']."\n";
      echo '   [notificaciones]: <span id="notif">No</span>'."\n";

        if (CFG::$vars['auth']=='ldap')      {     
                if($this->ldap->connected()) {
                   // Messages::info( 'LDAP connected OK' );
                    if($this->ldap->user_auth(CFG::$vars['ldap_user'],CFG::$vars['ldap_password'])){
                      //  Messages::info( 'USER AUTH ok' );
                        echo '              Email: '.$this->ldap->get_value($_SESSION['username'],'mail')      ."\n";
                        // echo '         Miembro de: '.$this->ldap->get_value($_SESSION['username'],'memberof')  ."\n";
                        //   echo '                 dn: '.$this->ldap->get_value($_SESSION['username'],'dn')        ."\n";
                        echo '          givenname: '.$this->ldap->get_value($_SESSION['username'],'givenname') ."\n";
                        // $juxLDAP->get_members($groups,'PRM_TIC_Soporte');
                        // $juxLDAP->s($groups);
                     $groups = $this->ldap->get_user_groups($_SESSION['username']);
                     echo '             '.t('GROUPS').': ' .   sprintf(t('MEMBER_OF_%s_GROUPS'),count($groups))."\n";
                     foreach ($groups as $group){
                        echo '                     '.$group      ."\n";
                     }

                    }else{
                        Messages::error( t('USER_AUTH_ERROR') );
                    }  
                }else{
                    Messages::error( 'LDAP not connected' );
                }
        }
        
        
        
      //  print_r($_SESSION);
      //  print_r($_COOKIE);
      ////////////echo "   [Notificationes]: ";
      ///////   https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API/Using_the_Notifications_API
      ?>
      <script type="text/javascript">
            function dw(t) {
               $('#notif').html( t?'Si':'No' );
            }
            Notification.requestPermission().then(function(result) {  
               console.log(result); 
               dw( result=='granted' );
            });
            var notification = new Notification('Tienes un mensaje', {  icon: '/ico/android-chrome-256x256.png',body: 'texto'}); //'https://demo.extralab.net/media/fotos/images/logo.png',body: text});
      </script>
      <?php 
        //echo 21000000/7000000000; //"          T  [rnd]: ".get_rnd_iv(16)."\n"; 
        echo '<p style="float:right;">Xyлиaн Toppec</p>';
      ?>
      </pre><?php 
    }

    public static function getUrlAvatar($size=40){

   //     if(!$_SESSSION['valid_user']){
   //         return "/_images_/avatars/avatar.gif";
   //     }else{
            $data = self::getUserData( $_SESSION['userid']);
            return $data['user_url_avatar'];
   //     }
        /*
        if ($_SESSION['auth_provider']==='google'){
            $_url = $_SESSION['auth_picture'];
        }else{
            $_avatar_default_image = "/_images_/avatars/avatar.gif";
            $_avatar_size  = $size; 
            $_dirphotos = '/media/avatars/';
            $_img = $_SESSION['user_url_avatar'];      // '?hash='.time()
            $ext = Str::get_file_extension($_img);
            $_email = $_SESSION['user_email'];                   
            if (!$_img || !file_exists($_dirphotos.$_img)){ 
               $_url = $_avatar_default_image;  //"https://www.gravatar.com/avatar.php?gravatar_id=".md5($_email)."&default=".urlencode($_avatar_default_image)."&size=".$_avatar_size; 
            }else{
               $_url = $_dirphotos.$_img;
            }
        }
        return $_url;
        */
    }        
    
    public static function getUserAvatar($userdata){

        if($userdata['AUTH_PROVIDER']){ 
            $_url = $userdata['AUTH_PICTURE']; 
        }else{
            $_img = $userdata['user_url_avatar'];
            $_email = $userdata['user_email'];                            
            if (!$_img || !file_exists(SCRIPT_DIR_MEDIA.'/avatars/'.$_img)){ 
                $_url = $userdata['AUTH_PICTURE']?$userdata['AUTH_PICTURE']:'./_images_/avatars/avatar.gif';
            }else{
                $_url = SCRIPT_DIR_MEDIA.'/avatars/'.$_img.'?date='.$userdata['LAST_UPDATE_DATE'];
            }       
        }
        return '<img class="avatar" src="'.$_url.'" title="'.$userdata['user_email'].'" width="20" height="20" border="0">';
    }

    public static function getUserData( $userId=null , $userEmail=null ){
   
        $sql_users = "SELECT user_id,username,user_email,user_fullname,user_url_avatar,user_email,AUTH_PROVIDER,AUTH_PICTURE,LAST_UPDATE_DATE FROM CLI_USER ";
        if($userId){
            $sql_users .= "WHERE user_id = $userId ";
        }else if($userEmail){
            $sql_users .= " WHERE user_email = '$userEmail' ";
        } else{
            return [];
        }
        
        $userdata = Table::sqlQuery($sql_users)[0]??null;

        if($userdata['AUTH_PROVIDER']){ 
             $userdata['user_url_avatar'] = $userdata['AUTH_PICTURE']; 
        }else if($userdata['user_email']){
            $_img = $userdata['user_url_avatar'];
            $_email = $userdata['user_email'];                            
            if (!$_img || !file_exists(SCRIPT_DIR_MEDIA.'/avatars/'.$_img)){ 
                 $userdata['user_url_avatar'] = $userdata['AUTH_PICTURE']?$userdata['AUTH_PICTURE']:'./_images_/avatars/avatar.gif';
            }else{
                 $userdata['user_url_avatar'] = SCRIPT_DIR_MEDIA.'/avatars/'.$_img.'?date='.$userdata['LAST_UPDATE_DATE'];
            }       
        }

        return $userdata;
    }

}
