<?php

$login = new Login();

if($_ARGS[1]=='logout'){

    $login->logout();

} else if (!$_SESSION['valid_user']) {
    // if (google)
    $u = Crypt::base64_url_decode(Vars::getArrayVar($_COOKIE,CFG::$vars['prefix'].'_username',false));
    $p = Crypt::base64_url_decode(Vars::getArrayVar($_COOKIE,CFG::$vars['prefix'].'_userpass',false));
    if( $u && $p ){
        $data['username'] = Crypt::md5_decrypt($u,CFG::$vars['prefix']);
        $data['password'] = Crypt::md5_decrypt($p,CFG::$vars['prefix'].Crypt::md5_decrypt($u,CFG::$vars['prefix']));
        if ($login->login($data)){  
            //if (!$_SESSION['hits']) $_SESSION['hits']=0;
            if (!$_SESSION['user_score']) $_SESSION['user_score']=1;
        }
    }

}else{

  if($_SESSION['userid']) {
  
        //if($_SERVER['REMOTE_ADDR']=='10.166.22.127') $login->logout();

        //FIX check change ip: if user_ip <> previous_ip

        //Login::updateOfflineUsers();
        //if(OUTPUT!='ajax') 
	///////////////////////////////////////////////////////////if($_ARGS['noupdate'])  
        Login::updateLastLogin($_SESSION['userid']);
        Login::getUserScore();
        //$sql = "UPDATE ".TB_USER." SET ".USER_ONLINE."=1 WHERE ".USER_ID."='{$_SESSION['userid']}'"; 
        //Login::sqlQuery($sql);
  }
    
}


