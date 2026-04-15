<?php

    // if($_POST) log2mail($_ARGS,'_ARGS');


    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if (OUTPUT == 'pdf'){

        ?>
        <h1>Login module</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
          Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
          Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
          Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
        </p>
        <?php 

    //}else if ($_ARGS[1]   =='debug_magic_link')  {

    //    include(SCRIPT_DIR_MODULES.'/login/debug_magic_link.php');

    }else if ($_ARGS[1]   =='auth')  {

        ?>
        <style>
            body {background:#e6e6e6;display:flex;width:100%;align-items: center;height: 100vh;justify-content: center;}
            .box {font-family: Arial, sans-serif;border:0;/*width:450px;*/display:block;z-index:20;padding:30px;background:#f7fcff;/*min-height:600px;*/min-width:350px;max-width:95%;border-radius:25px;
                  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);overflow:auto;/* margin: auto auto; */ }
            .box img{padding: 10px 0px;    width: 100px; height: auto;}
            .box a{color: #427fed;cursor: pointer;text-decoration: none;}
            .heading{text-align: center;padding-top:90px;font-family:'Open Sans', Arial;color: #999;font-size:18px;font-weight:700;background:url(/_images_/logos/google.png);background-repeat:no-repeat;background-position:top;margin:28px auto;}
            .circle-image{width:100px;height:100px;-webkit-border-radius: 50%;border-radius: 50%;}
            .welcome{font-size: 16px;font-weight: bold;text-align: center;margin: 10px 0 0;min-height: 1em;}
            .oauthemail{font-size: 14px;}
            .logout{font-size: 13px;text-align: right;padding: 5px;margin: 20px 5px 0px 5px;border-top: #CCCCCC 1px solid;}
            .logout a{color:#8E009C;}
        </style>
        <?php


            require_once './_modules_/login/google.php';
            // authenticate code from Google OAuth Flow
            if (isset($_REQUEST['code'])) {
                //$client->authenticate($_GET['code']);
                //  if ($client->getAccessToken()) {
                //$google_token = $client->getAccessToken();
                // GET
                // https://tienda.extralab.net/login/auth/google?code=4%2F0AX4XfWjum5H58yyyhX0qtepJEA6vp6GEnJQLYJKvMcZMiVR-U84NCPoA6U1wGt-oNMNKCA&scope=email+profile+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile+openid&authuser=0&prompt=none#
                ////////////  https://www.webslesson.info/2019/09/how-to-make-login-with-google-account-using-php.html
                /////// https://www.webslesson.info/search/label/php
                /////// https://www.webslesson.info/2021/01/build-real-time-chat-application-in-php-mysql-using-websocket.html
                /////// https://es.linux-console.net/?p=53
                /*
                https://phppot.com/php/php-google-oauth-login/
                https://www.codexworld.com/login-with-google-api-using-php/
                https://www.codexworld.com/demos/login-with-google-api-using-php/index.php
                https://code.tutsplus.com/tutorials/how-to-authenticate-users-with-twitter-oauth-20--cms-25713
                */
                /***********OKIS***/
                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                ?>
                <!--<div class="heading">Google OAuth 2.0 Login</div>-->
                <div class="box">
                <div>
                <?php 
                
                if(!isset($token['error'])){
                    $client->setAccessToken($token['access_token']);
                    $_SESSION['auth_token'] = $token['access_token'];

                    // get profile info

                    $google_account_data = $client->fetchUserInfo();
                    if (!is_array($google_account_data)) {
                        ?><p>Unable to retrieve Google profile information.</p><?php
                        $google_account_data = [];
                    }
                    $google_account_info = (object) $google_account_data;
                    $profileEmail = $google_account_info->email ?? null;
                    $profileId = $google_account_info->id ?? null;
                    $profilePicture = $google_account_info->picture ?? null;
                    $profileName = $google_account_info->name ?? ($google_account_info->given_name ?? '');
                    $profileLink = $google_account_info->link ?? '#';

                    $_SESSION['auth_id'] = $profileId; //$userData["id"];
                    $_SESSION['auth_token'] = $token['access_token'];

                    $dummy_password =   Str::password(20,5);
                    $ok = $login->login(array('user_email'=>$profileEmail,
                                                'username'=>$profileEmail,
                                                'password'=>$dummy_password,
                                                'auth_provider'=>'google',
                                                'auth_id'=>$profileId,
                                                'auth_picture'=>$profilePicture,
                                                'user_fullname'=>$profileName ));
                    if ($ok) {
                        $result['msg']=t('REGISTER_OK_MSG');
                        ?>
                        <?php /*if (CFG::$vars['oauth']['google']['popup']) {*/ ?>
                        <style>#top,#breadcrumb,header,footer,#widget-drawing{display:none;}body{background:white;align-items:center;}</style>    

                        <?php /* } */ ?>
                        <img class="circle-image" src="<?php echo $profilePicture; ?>" width="100px" size="100px" /><br/>
                        <p class="welcome">Welcome <a href="<?php echo $profileLink; ?>" /><?php echo $profileName; ?></a>.</p>
                        <p class="oauthemail"><?php echo $profileEmail; ?></p>
                        <p>ID <?php echo $profileId; ?></p>
                        <!--<div class='logout'><a href='/login/logout'>Logout</a></div>-->
                        <p>
                            <?php
                                //Vars::debug_var($_SESSION);
                                $redir = '/'.($_SESSION['backurl']?$_SESSION['backurl']:'');
                            ?>
                            Redirect to: <?=$redir?>
                        </p>
                        <?php /* if (CFG::$vars['oauth']['google']['popup']) {*/  ?>
                        <div class="heading">Google OAuth 2.0 Login</div>
     
                        <?php /* } */ ?>
                        <script type="text/javascript">
                            setTimeout(function(){
                                <?php if (CFG::$vars['oauth']['google']['popup']) { ?>
                                    opener.location.href='<?=$redir?>';
                                    close();
                                <?php }else{ ?>
                                    location.href='<?=$redir?>';
                                <?php } ?>
                            },1000);
                        </script>
                        <?php
                    }else{
                        $result['msg']=t('REGISTER_KO_MSG');
                        ?><p>Ha ocurrido algún problemilla al identificarse en Google.</p><?php 
                    }

                }else{
                    ?><p>Google token error: <?=$token['error']?></p><?php 
                }
                
                ?>
                </div>
                </div>
                <?php                    // now you can use this profile info to create account in your website and make user logged in.
            } else{

                ?><p>Error ???</p><?php 
                require_once './_modules_/login/google.php';
                $authUrl = (isset($client) && $client) ? $client->createAuthUrl() : null;
                if ($authUrl) {
                    $authHref = $authUrl;  //"javascript:PopupCenter('{$authUrl}','Google',450,600)";  // 'javascript:poptastic(\''.$authUrl.'\')';  // $authUrl 
                    //OLD $authHref = "javascript:PopupCenter('{$authUrl}','Google',450,600)";  // 'javascript:poptastic(\''.$authUrl.'\')';  // $authUrl 
                    ?>
                    <div class="control-group row">
                        <label class="control-label empty">&nbsp;</label>
                        <div class="controls buttons">
                        <a class='btn btn-block btn-linkk login btn-google' href="<?php echo $authHref; ?>"><img src="/<?=SCRIPT_DIR_IMAGES?>/logos/google.png"> <?=t('LOGIN_WITH_GOOGLE','Sign in with Google')?></a>
                        </div>
                    </div>

                    <?php
                    Jeader('/');
                }

            }


    }else if (OUTPUT == 'html'){
    
        $isPasswordlessDefault =  (CFG::$vars['login']['default_method']=='passwordless') || false;
        if($_ARGS['op']=='nostr_import_identity_dialog'){
            include(SCRIPT_DIR_MODULES.'/login/form_import_nostr.php');
        }else  if($_ARGS['from']){
            include(SCRIPT_DIR_MODULES.'/login/after_init.php');
            include(SCRIPT_DIR_MODULES.'/login/form_login_login.php');
        }else {
            ?><link  href="<?=SCRIPT_DIR_MODULES?>/login/style.css?ver=1.9.6" rel="stylesheet" type="text/css" /><?php
            include(SCRIPT_DIR_MODULE.'/run.php');
            include(SCRIPT_DIR_MODULES.'/login/passwordless_js.php');
        }

    }else{
	    
        if ($_ARGS[1]   =='test')  {
       

                //$salt = $this->createSalt();
                $code = Str::password(40, 4);

                $k = Crypt::keys();
                Vars::debug_var($k,'Keys',false,30000);

                $text = '<p>They scrabbled through the charred ruins of houses they would not have entered before. A corpse floating in the black water of a basement among the trash and rusting ductwork. He stood in a livingroom partly burned and open to the sky. The waterbuckled boards sloping away into the yard. Soggy volumes in a bookcase. He took one down and opened it and then put it back. Everything damp. Rotting. In a drawer he found a candle. No way to light it. He put it in his pocket. He walked out in the gray light and stood and he saw for a brief moment the absolute truth of the world. The cold relentless circling of the intestate earth. Darkness implacable. The blind dogs of the sun in their running. The crushing black vacuum of the universe. And somewhere two hunted animals trembling like ground-foxes in their cover. Borrowed time and borrowed world and borrowed eyes with which to sorrow it.</p>
                <p>Once there were brook trout in the streams in the mountains. You could see them standing in the amber current where the white edges of their fins wimpled softly in the flow. They smelled of moss in your hand. Polished and muscular and torsional. On their backs were vermiculate patterns that were maps of the world in its becoming. Maps and mazes. Of a thing which could not be put back. Not be made right again. In the deep glens where they lived all things were older than man and they hummed of mystery.</p>';

                $encrypted = base64_encode(Crypt::encrypt($text,$k['public']));
                $encrypted_hex = $encrypted;
                Vars::debug_var( $encrypted_hex, 'This is the encrypted text' );

                // Decrypt the data using the private key
                //OK openssl_private_decrypt($encrypted, $decrypted, $private_key);
                $decrypted = Crypt::decrypt(base64_decode($encrypted),$k['private']);
                Vars::debug_var( $decrypted, 'This is the decrypted text');
                /**/

                //$msg = 'Gracias por registrarse con nosotros. Puede activar su cuenta pulsando en este enlace: https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR.'/login/verify/code='.$code;
                /*
                $msg = str_replace( array('<p><br></p>','<div><br></div>','[FULL_NAME]','[EMAIL]','[PASSWORD]'),
                                    array( '','','Jander Klander','bla@blabla.bla','incorrecta'),
                                    CFG::$vars['templates']['email']['register']);
                */
                //echo $msg;
                /*  
                message_mail('Registro de usuario',
                             $msg,
                             CFG::$vars['smtp']['from_email'],
                             'julian.torres.sanchez@gmail.com');
                */

        }else  include(SCRIPT_DIR_MODULE.'/run.php'); 

    }


/***********
https://medium.com/javascript-scene/passwordless-authentication-with-react-and-auth0-c4cb003c7cde#.6bt9m8ug2
https://medium.com/@ninjudd/passwords-are-obsolete-9ed56d483eb#.ix14f913z
***********/
