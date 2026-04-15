<?php

$documents = true;

//CFG::$vars['site']['lpd_accept']['required']=true;
$login_class = 'login-'.(in_array($_ARGS[1],['changepassword','lostpassword','register','profile'])?$_ARGS[1]:'login');
?>
<div class="inner contact shadow gradient NOinner-page <?=$login_class?>">

    <?php  if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']){ ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <?php  } ?>

    <script>
        function onSubmitLogin(token) {document.getElementById("loginform").submit();}
        function onSubmitRegister(token) {document.getElementById("registerform").submit();}
        function onSubmitLostPassword(token) {document.getElementById("lostpasswordform").submit();}
        function onSubmitChangePassword(token) {document.getElementById("changepasswordform").submit();}
    </script>

    <?php 

       //  if($_POST) Vars::debug_var($_ARGS);
        foreach ($_ARGS as $k => $v){
            $_ARGS[$k] = trim($v);
        }

        /* 
        if($_ARGS['username']){
            $okis = Str::valid_email($_ARGS['username']) || Str::is_valid_username($_ARGS['username']);
            if(!$okis){
                 Messages::error( 'Nombre de usuario no válido.......');
                 //die();
            }
        }
        */

        $captcha = false;
        if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']){      // https://www.semicolonworld.com/tutorial/google-invisible-recaptcha-with-php
            if ($_ARGS['op']){   // (in_array($_ARGS['op'] , ['login','install','register','lostpassword','changepassword'])){
                $recaptcha = $_POST['g-recaptcha-response'];
                if ($recaptcha){
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                        CURLOPT_POST => 1,
                        CURLOPT_POSTFIELDS => array(
                            'secret' => CFG::$vars['captcha']['google_v3']['secret'],
                            'response' => $recaptcha
                        )
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    if(strpos($response, '"success": true') !== FALSE) {
                        $captcha = true;
                    } else {
                        Messages::error( "Error captcha");  //. Código:".$responseData->error->codes);
                    }
                }else{
                    Messages::error( 'Ha fallado la verificación. Inténtelo de nuevo.');
                }
            }
        }else if(CFG::$vars['captcha']['enabled']) {

            //include(SCRIPT_DIR_CLASSES.'/captcha.class.php'); 

            if (in_array($_ARGS['op'] , ['install','register','lostpassword'])){   // (in_array($_ARGS['op'] , ['login','install','register','lostpassword','changepassword'])){
                if (Captcha::check($_POST['captcha'])){
                   $captcha = true;
                }else{
                 Messages::error( '<nobr>'.t('Captcha incorrecto').'.<font color=red><b>!</b></font></nobr><br />');
                 //$msg_error.= 'session_captcha:'.$_SESSION['captcha'].'<br />';
                 //$msg_error.= 'post_cpatcha:'.$_POST['captcha'].'<br />';
                 //echo '<div class="error">'.$msg_error.'</div>';
                }
            }else{
                $captcha = true;
            }
        }else{
            $captcha = true;
        }
        //print_r($_ARGS);

             if ($_ARGS[1]   =='logout')         $login->logout();
        else if ($_ARGS['rfid'])                 $login->login(['auth'=>'rfid','username'=>$_ARGS['rfid'],'password'=>$_ARGS['rfid']]);
        else if ($_ARGS[1]   =='verify'   && $_ARGS['code'] && $_ARGS['type']=='passwordless') {
            // Verificación para registro passwordless (sin enviar email con contraseña)
            $verify = $login->verifyPasswordless($_ARGS);
            $verifyType = 'passwordless'; // Guardar tipo para mostrar mensaje correcto

            // Si la verificación fue exitosa, hacer auto-login
            if ($verify == 'ok') {
                $sql = "SELECT * FROM " . TB_USER . " WHERE user_confirm_code = ?";
                $userData = Table::sqlQueryPrepared($sql, [$_ARGS['code']]);

                if (!empty($userData)) {
                    $user = $userData[0];

                    // Crear sesión automáticamente
                    $_SESSION['valid_user'] = true;
                    $_SESSION['userid'] = $user['user_id'];
                    $_SESSION['userlevel'] = $user['user_level'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_fullname'] = $user['user_fullname'];
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['auth_provider'] = 'passwordless';

                    Login::updateLastLogin($user['user_id']);
                }
            }
        }
        else if ($_ARGS[1]   =='verify'   && $_ARGS['code']) {
            $verify = $login->verify($_ARGS);

            // Si la verificación fue exitosa, hacer auto-login
            if ($verify == 'ok') {
                $sql = "SELECT * FROM " . TB_USER . " WHERE user_confirm_code = ?";
                $userData = Table::sqlQueryPrepared($sql, [$_ARGS['code']]);

                if (!empty($userData)) {
                    $user = $userData[0];

                    // Crear sesión automáticamente
                    $_SESSION['valid_user'] = true;
                    $_SESSION['userid'] = $user['user_id'];
                    $_SESSION['userlevel'] = $user['user_level'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_fullname'] = $user['user_fullname'];
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['auth_provider'] = 'email_verification';

                    Login::updateLastLogin($user['user_id']);
                }
            }
        }
        else if ($_ARGS[1]   =='magic' && $_ARGS[2]) {
            // Magic Link - Recuperación de acceso temporal
            $token = $_ARGS[2];

            // Buscar usuario por token válido (no expirado)
            $sql = "SELECT * FROM " . TB_USER . "
                    WHERE device_link_token = ?
                    AND device_link_expires > ?
                    LIMIT 1";
            $result = Table::sqlQueryPrepared($sql, [$token, time()]);

            if (!empty($result)) {
                $user = $result[0];

                // Crear sesión temporal
                $_SESSION['valid_user'] = true;
                $_SESSION['userid'] = $user['user_id'];
                $_SESSION['userlevel'] = $user['user_level'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_fullname'] = $user['user_fullname'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['auth_provider'] = 'magic_link';

                Login::updateLastLogin($user['user_id']);

                // Invalidar el token (ya fue usado)
                $sql = "UPDATE " . TB_USER . "
                        SET device_link_token = NULL,
                            device_link_expires = NULL
                        WHERE user_id = ?";
                Table::sqlQueryPrepared($sql, [$user['user_id']]);

                $magicLink = 'ok';
            } else {
                $magicLink = 'ko';
            }
        }
        else if ($_ARGS[1]   =='reminder' && $_ARGS['code']) $reset = $login->lostpassword($_ARGS);
        else if ($_ARGS[1]   =='install')        include(SCRIPT_DIR_MODULE.'/install.php');
        else if ($_ARGS['op']=='login')          {if($captcha) $logged       = $login->login($_ARGS);         }
        else if ($_ARGS['op']=='register')       {if($captcha) $reg          = $login->register($_ARGS);      }
        else if ($_ARGS['op']=='lostpassword')   {if($captcha) $reminder     = $login->lostpassword($_ARGS);  }
        else if ($_ARGS['op']=='changepassword') {if($captcha) $passwchanged = $login->changepassword($_ARGS);}
        //Vars::debug_var($reset);



        if      ($register_enabled && $reg)  {  
            ?>
            <h3 class="subtitle"><?=t('REGISTER')?></h3>
            <p style="margin:40px auto;"><?=($reg=='ok')?t('REGISTER_OK_MSG'):t('REGISTER_KO_MSG')?></p>  
            <p class="login-buttons"><!---->
            <a class="btn btn-reset" href="login"><?=t('LOGIN')?></a>  
            <a class="btn btn-reset" style="display:none;" href="login/lostpassword"><?=t('LOST_PASSWORD')?></a> 
            </p>
            <?php 
        } else if ($verify)  {
            ?>
            <h3 class="subtitle"><?=t('REGISTER')?></h3>
            <p style="margin:40px auto;">
            <?php if ($verify == 'ok'): ?>
                <?php if ($verifyType == 'passwordless'): ?>
                    Su cuenta ha sido verificada.<br />Ya puede iniciar sesión sin contraseña.
                <?php else: ?>
                    <?=t('VERIFIED_OK_MSG')?>
                <?php endif; ?>
            <?php else: ?>
                <?=t('INVALID_CODE')?>
            <?php endif; ?>
            </p>
            <p class="login-buttons"><!-- style="display:none;"-->
            <?php if ($verifyType == 'passwordless' && $verify == 'ok'): ?>
                <a class="btn btn-primary" href="login/profile"><?=t('MY_ACCOUNT','Mi cuenta')?></a>
            <?php else: ?>
                <a class="btn btn-reset" href="login"><?=t('LOGIN')?></a>
            <?php endif; ?>
            <?php if($register_enabled && $verifyType != 'passwordless'){ ?>
            <a class="btn btn-reset" href="login/lostpassword"><?=t('LOST_PASSWORD')?></a>
            <?php } ?>
            </p>
            <?php
        } else if ($magicLink)  {
            ?>
            <h3 class="subtitle">🔑 Acceso Temporal</h3>
            <?php if ($magicLink == 'ok') { ?>
                <p style="margin:40px auto; color: #060; background: #efe; padding: 20px; border-radius: 8px;">
                    ✓ ¡Bienvenido de nuevo!<br>
                    Has accedido con un enlace temporal. Para poder usar login sin contraseña desde este dispositivo:
                    <ol style="text-align: left; margin: 10px auto; max-width: 500px;">
                        <li>Ve a <strong><a href="/login/profile">"Mi cuenta"</a></strong></li>
                        <li>Click en <strong>"Crear código de vinculación"</strong></li>
                        <li>Usa ese código para vincular este dispositivo</li>
                    </ol>
                </p>
            <?php } else { ?>
                <p style="margin:40px auto; color: #c00; background: #fee; padding: 20px; border-radius: 8px;">
                    ✗ Este enlace ya no es válido<br><br>
                    El enlace ha expirado o ya fue utilizado. Puedes solicitar uno nuevo.
                </p>
                <p class="login-buttons">
                    <a class="btn btn-reset" href="login">Volver al login</a>
                </p>
            <?php } ?>
            <?php
        } else if (($_ARGS[1]=='register'||$_ARGS['op']=='register'||$reg=='ko') && (!$_SESSION['auth_provider'])) {                           
            if ($register_enabled){
                 include(SCRIPT_DIR_MODULE.'/form_register.php');        
            } else {
                Messages::error( t('REGISTRATION_DISABLED','El registro de nuevos usuarios está deshabilitado.') );
            }
            ?>
            <p class="login-buttons" style="display:none;">
            <a class="btn btn-reset" href="login"><?=t('LOGIN')?></a>  
            <?php if($register_enabled){ ?>
            <a class="btn btn-reset" href="login/lostpassword"><?=t('LOST_PASSWORD')?></a>
            <?php } ?>
            </p>
            <?php 
        } else if ($_ARGS[1]=='lostpassword'||$_ARGS['op']=='lostpassword'||($_ARGS[1] =='reminder' && $reset)){
            if        ($reset)  {  
                ?><h3 class="subtitle"><?=t('LOST_PASSWORD')?></h3><?php 
                if($reset=='ok'){                
                    ?><p style="margin:40px auto;"><?=t('NEW_PASSWORD_SENT')?></p><?php 
                }else{
                    ?><p style="margin:40px auto;"><?=t('RESET_PASSWORD_REQUEST_FAILED')?></p><?php 
                }
            }else if ($reminder)  {  
                ?>
                <h3 class="subtitle"><?=t('LOST_PASSWORD')?></h3>
                <p style="margin:40px auto;">
                <?php  if($reminder=='ok'){?>
                <?=t('REMINDER_PASSWORD_REQUEST_SENT')?>
                <?php }else{?>
                <?=t('ACCOUNT_INVALID')?>
                <?php }?> 
                </p> 
                <?php 
            }else{
                if ($register_enabled){
                    include(SCRIPT_DIR_MODULE.'/form_lostpassword.php');    
                } else {
                    Messages::error( t('REGISTRATION_DISABLED','El registro de nuevos usuarios está deshabilitado.') );
                }
            }
            ?>
            <div>
            <a class="btn btn-reset btn-block" href="login"><?=t('LOGIN')?></a>  
            <?php if($register_enabled){ ?>
            <a class="btn btn-reset btn-block" href="login/register"><?=t('REGISTER')?></a>
            <?php } ?>
            </div>
            <?php 
        } else if ($_SESSION['valid_user'] && ($_ARGS[1]=='changepassword'||$_ARGS['op']=='changepassword'))     {  
            if($passwchanged){    
               ?><p style="margin:40px auto;"><?=t('PASSWORD_CHANGED','Su contraseña ha sido cambiada.')?></p><?php
            }else{
                include(SCRIPT_DIR_MODULE.'/form_changepassword.php');  
            }
            ?>
            <p class="login-buttons">
            <a class="btn btn-danger btn-block" href="login/logout"><i class="fa fa-sign-out"></i> <?=t('EXIT_SESSION')?></a> 
            <a class="btn btn-reset btn-block" href="login/profile"><i class="fa fa-user"></i> <?=t('MY_ACCOUNT')?></a>  
            </p>
            <?php 
        } else if ($_SESSION['valid_user'] && $_ARGS[1]=='profile'){  
              //include(SCRIPT_DIR_MODULE.'/INIT_'.CFG::$vars['prefix'].'.php');
             if($_SESSION[CFG::$vars['prefix'].'_username'] && $_SESSION[CFG::$vars['prefix'].'_userpass']){
                 ?>
                 <script>
                     // saveCookie('<?=CFG::$vars['prefix'].'_username'?>','<?=$_SESSION[CFG::$vars['prefix'].'_username']?>');
                     // saveCookie('<?=CFG::$vars['prefix'].'_userpass'?>','<?=$_SESSION[CFG::$vars['prefix'].'_userpass']?>');
                 </script>
                <?php
                // $_SESSION[CFG::$vars['prefix'].'_username']=false;
                // $_SESSION[CFG::$vars['prefix'].'_userpass']=false;
             }

             //Table::init();
             ?>
       
             <div id="div-profile">
             <h3><?=t('USER_AREA')?></h3>
             <?php 

                $my_tabs = file_exists( SCRIPT_DIR_THEME.'/my_tabs_buttons.php') && file_exists( SCRIPT_DIR_THEME.'/my_tabs_contents.php');
                if($my_tabs) include(SCRIPT_DIR_THEME.'/my_tabs_buttons.php');

                $tabs_buttons = sprintf( TAB_TAB_TAB, 'tab_customer_account', '<i class="fa fa-user"></i>  '.t('MY_ACCOUNT') )
                           //    . sprintf( TAB_TAB_TAB, 'tab_customer_account_edit', 'Datos personales' )
                               . (defined('MODULE_SHOP') ? (MODULE_SHOP               ?sprintf( TAB_TAB_TAB, 'tab_customer_addresses', t('MY_ADDRESSES','Mis direcciones') ):''):'')
                               . (defined('MODULE_SHOP') ? (MODULE_SHOP               ?sprintf( TAB_TAB_TAB, 'tab_customer_orders'   , t('MY_ORDERS','Mis pedidos')     ):''):'')
                               . sprintf( TAB_TAB_TAB, 'tab_user_files', t('MY_FILES','Mis archivos') )
                               . ($my_tabs                  ?$my_tabs_buttons:'')
                               . (CFG::$vars['auth']=='ldap'?sprintf( TAB_TAB_TAB, 'tab_customer_ldap', 'LDAP' ):'')
                               . (1==2 && Root()?sprintf( TAB_TAB_TAB, 'tab_test', 'Test' ):'')
                               . sprintf( TAB_TAB_TAB, 'tab_customer_messages', '<i class="fa fa-envelope"></i> '.t('MESSAGES','Mensajes') )
                               . (CFG::$vars['plugins']['tip_ln']?sprintf( TAB_TAB_TAB, 'tab_transactions', t('PAYMENTS','Pagos') ):'')
                               . (CFG::$vars['plugins']['tip_ln']?sprintf( TAB_TAB_TAB, 'tab_timestamp', 'Timestamps' ):'')
                               . sprintf( TAB_TAB_TAB, 'tab_user_keys', t('DEVICES','Dispositivos') )
                               . (Root()?sprintf( TAB_TAB_TAB, 'tab_cookies', 'Cookies' ):'')
                               . (Invitation::isRequired() ? sprintf( TAB_TAB_TAB, 'tab_invitations', '<i class="fa fa-ticket"></i> '.t('INVITATIONS','Invitaciones') ) : '')
                               ;  /* 💌 */

                echo sprintf(TAB_HEADER,'tabs-profile',$tabs_buttons); 
                     
                echo sprintf(TAB_TAB_BEGIN,'tab_customer_account');
                        // $login->profile();
                       ?>
                       <style>/*html,*//*body,*/#noxtr{  max-width: 750px ;}</style>
                       <div id="customer-profile" style="display:inline-table;vertical-align:top;width:inherit;width: -webkit-fill-available;width: -moz-available;">Loading user profile ... </div>
                       
                       <?php if (CFG::$vars['login']['nostr']['enabled']){ ?>
                       <!-- Nostr Identity -->
                       <?php 
                       $nostr_pubkey = Login::getFieldValue("SELECT nostr_pubkey FROM " . TB_USER . " WHERE user_id = " . $_SESSION['userid']);
                       ?>
                       <div id="nostr-identity" class="nostr-section" style="margin:5px 0;padding:15px;background:linear-gradient(135deg, #7b4dff15, #9c27b015);border-radius:8px;border:2px solid #9c27b040;display: inline-table;/*height: 160px;max-width:540px;*/">
                           <h4 style="margin:0 0 10px 0;color:#7b4dff;">
                               <svg width="20" height="20" viewBox="0 0 256 256" style="vertical-align:middle;margin-right:5px;">
                                   <circle cx="128" cy="128" r="120" fill="#7b4dff"/>
                                   <path d="M80 100 Q128 60 176 100 Q200 140 176 180 Q128 220 80 180 Q56 140 80 100" fill="white" stroke="white" stroke-width="4"/>
                               </svg>
                               Identidad Nostr
                           </h4>
                           <?php if ($nostr_pubkey): ?>
                               <?php 
                               //include_once(SCRIPT_DIR_CLASSES . '/nostrauth.class.php');
                               $npub = NostrAuth::hexToNpub($nostr_pubkey);
                               ?>
                               <p style="margin:0 0 10px 0;color:#28a745;"><i class="fa fa-check-circle"></i> Cuenta vinculada a Nostr</p>
                               <p style="margin:0;font-family:monospace;font-size:0.85em;word-break:break-all;background:#fff;padding:0px;border-radius:4px;border:1px solid #ddd;">
                                   <strong>npub:</strong> <?= htmlspecialchars($npub) ?>
                                   <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($npub) ?>');this.innerHTML='✓ Copiado!';setTimeout(()=>this.innerHTML='📋 Copiar',2000);" 
                                           style="margin-left:10px;padding:2px 8px;cursor:pointer;border:1px solid #ccc;border-radius:3px;background:#f5f5f5;">
                                       📋 Copiar
                                   </button>
                               </p>
                               <p style="margin:10px 0 0 0;font-size:0.85em;color:#666;position: relative;">
                                   <a href="https://primal.net/p/<?= htmlspecialchars($npub) ?>" target="_blank" style="color:#7b4dff;">Ver perfil en Primal ↗</a> ·
                                   <a href="https://snort.social/p/<?= htmlspecialchars($npub) ?>" target="_blank" style="color:#7b4dff;">Ver en Snort ↗</a>
                                   <a style="position: absolute;right:0px;bottom: -5px;" onclick="document.querySelector('#more-nostr-buttons').style.display = document.querySelector('#more-nostr-buttons').style.display === 'none' ? 'block' : 'none'; return false;">Mas opciones ... </a>
                               </p>

                               <!-- Botones de gestión -->
                               <div id="more-nostr-buttons" style="display:none;text-align:right;margin-top:15px;padding-top:15px;border-top:1px solid #e0e0e0;">
                                   <button id="btn-unlink-nostr" class="btn btn-small btn-sm btn-warning" style="margin-right:5px;">
                                       <i class="fa fa-unlink"></i> Desvincular cuenta
                                   </button>
                                   <button id="btn-delete-local-keys" class="btn btn-small btn-sm btn-danger">
                                       <i class="fa fa-trash"></i> Eliminar claves de este navegador
                                   </button>
                               </div>
                           <?php else: ?>
                               <div id="nostr-link-container">
                                   <!-- El contenido se cargará dinámicamente según el estado de IndexedDB -->
                                   <p style="margin:0 0 10px 0;color:#999;"><i class="fa fa-spinner fa-spin"></i> Comprobando...</p>
                               </div>
                           <?php endif; ?>



                       </div>
                       <?php } ?>
                       
                       <!-- Passwordless Login Setup -->
                       <div id="passwordless-setup" class="passwordless-section" style="margin:5px 0;padding:15px;background:linear-gradient(135deg, #4de6ff15, #27b08a15);border-radius:8px;border:2px solid #bcdcfb;display: inline-block;*//*height: 160px;*/">
                           <h4 style="margin:0 0 10px 0;"><i class="fa fa-key"></i> Login sin contraseña</h4>
                           <p id="passwordless-status" style="margin:0 0 10px 0;color:#666;">Cargando...</p>
                           <button id="btn-setup-passwordless" class="btn btn-primary" style="display:none;">
                               <i class="fa fa-shield"></i> Configurar login sin contraseña
                           </button>
                           <button id="btn-remove-passwordless" class="btn btn-warning" style="display:none;">
                               <i class="fa fa-trash"></i> Eliminar claves de este navegador
                           </button>
                            <button onclick="DeviceLinking.generateToken()" class="btn btn-success" style="max-width:400px;">
                                <i class="fa fa-key"></i> Generar Código de Vinculación
                            </button>
                        </div>

                       <p class="login-buttons" style="padding-bottom:15px;">
                           <a class="btn btn-danger" href="login/logout"><i class="fa fa-sign-out"></i> <?=t('EXIT_SESSION')?></a>  
                           <?php if($register_enabled){ ?>
                           <?php if (!$_SESSION['auth_provider']){ ?><a class="btn btn-secondary" href="login/changepassword"><i class="fa fa-key"></i> <?=t('CHANGE_PASSWORD')?></a><?php } ?> 
                           <?php } ?>
                           <?php  if($_ACL->hasPermission('pedidos_admin')||$_ACL->hasPermission('edit_items')){  ?>
                           <a class="btn btn-success" href="control_panel"><i class="fa fa-cog"></i> Panel de control </a>  
                           <?php }?>
                       <p>                      
                       <?php 
                echo TAB_TAB_END;  

                if(1==2 && Root()){
                echo sprintf(TAB_TAB_BEGIN,'tab_test');

                    echo 'TEST';

                echo TAB_TAB_END;  
                }

                if (defined('MODULE_SHOP'))
                    if(MODULE_SHOP){

                    echo sprintf(TAB_TAB_BEGIN,'tab_customer_addresses');
                            Table::show_table('CLI_USER_ADDRESSES');      
                            if($_ARGS[2]=='addresses'){
                                ?><p class="login-buttons"><a href="javascript:;" class="jxCart_checkout btn btn-danger NObtn-small NOright"><?=t('BACK')?></a></p><?php   
                            }
                    echo TAB_TAB_END;  
    
                    echo sprintf(TAB_TAB_BEGIN,'tab_customer_orders');
                            Table::show_table('CLI_ORDERS');      
                            Table::show_table('CLI_ORDER_LINES');    
                    echo TAB_TAB_END;  

                    }

                echo sprintf(TAB_TAB_BEGIN,'tab_user_files');
                    Table::show_table('CLI_USER_FILES');      
                echo TAB_TAB_END;  

                if($my_tabs) include(SCRIPT_DIR_THEME.'/my_tabs_contents.php');

                echo sprintf(TAB_TAB_BEGIN,'tab_customer_messages');
                            ?>
                            <div id="div-customer-messages">
                                <div class="ajax-loader" style="display:none;"><div class="loader"></div></div>
                                <p style="text-align:left;margin-bottom:0;padding:0;"><b>Enviar mensaje a <?=CFG::$vars['site']['title']?>.</b> Este mensaje podrá ser leido por los admins y/o moderadores de <?=CFG::$vars['site']['title']?>.</p>
                                <p style="text-align:left;padding:0 2px 0 0;margin:0 0 6px 0;">
                                Asunto: <input type="text" id="message_subject" value="Mensaje de <?=$_SESSION['user_fullname']?> <<?=$_SESSION['user_email']?>>" style="line-height:1.5em;display:block;width:100%;max-width: -webkit-fill-available;max-width: -moz-available;border:1px solid #d5d5d5;">
                                Mensaje:<br />
                                <textarea id="message_body" style="display:block;width:100%;height:60px;border:1px solid #d5d5d5;max-width: -webkit-fill-available;max-width: -moz-available;"></textarea>
                                </p>
                                <a class="btn btn-primary" style="text-align:center;" id="btn_last_messages"><i class="fa fa-refresh"></i> &nbsp; Actualizar &nbsp;  &nbsp; </a>
                                <a class="btn btn-success" style="text-align:center;" id="send_message_to_site"><i class="fa fa-paper-plane-o"></i> &nbsp; Enviar &nbsp;  &nbsp; </a>
                               <div id="list-messages"></div>
                            </div>
                            <?php 
                            //Table::show_table('LOG_EVENTS');  
                echo TAB_TAB_END;  

                if (CFG::$vars['auth']=='ldap') {
                     echo sprintf(TAB_TAB_BEGIN,'tab_customer_ldap');
                         $login->profile();
                     echo TAB_TAB_END;  
                }
                if (CFG::$vars['plugins']['tip_ln']) {
                    echo sprintf(TAB_TAB_BEGIN,'tab_transactions');
                        Table::show_table('CLI_USER_TRANSACTIONS');
                        ?>
                        <script type="text/javascript">
                            //var _TOKEN_ = '<?=$_SESSION['token']?>';
                            var _withdrawPending = false;
                            function setWithdrawStatus(msg){
                                var el = document.getElementById('withdraw-status');
                                if(el){ el.textContent = msg || ''; }
                            }
                            var _html5QrScanner = null;

                            async function populateCameraSelect(){
                                var camSelect = document.getElementById('qr-cam-select');
                                if(!camSelect) return [];
                                var cameras = await Html5Qrcode.getCameras();
                                camSelect.innerHTML = '';
                                cameras.forEach(function(cam, i){
                                    var opt = document.createElement('option');
                                    opt.value = cam.id;
                                    opt.text = cam.label || ('Cámara ' + (i + 1));
                                    camSelect.appendChild(opt);
                                });
                                camSelect.style.display = cameras.length > 1 ? 'inline-block' : 'none';
                                return cameras;
                            }

                            function stopQrScanner(){
                                if(_html5QrScanner){
                                    _html5QrScanner.stop().catch(function(){});
                                    _html5QrScanner = null;
                                }
                            }

                            async function startQrScanner(cameraId){
                                var container = document.getElementById('qr-scanner-container');
                                var statusEl = document.getElementById('qr-scan-status');
                                container.style.display = 'block';

                                if(_html5QrScanner){ stopQrScanner(); }

                                _html5QrScanner = new Html5Qrcode('qr-reader');

                                if(!cameraId){
                                    var cameras = await populateCameraSelect();
                                    if(cameras.length > 0) cameraId = cameras[0].id;
                                }

                                _html5QrScanner.start(
                                    cameraId,
                                    { fps: 10, qrbox: 250 },
                                    function(decodedText){
                                        document.getElementById('ln-invoice').value = decodedText;
                                        statusEl.textContent = '✅ QR detectado!';
                                        stopQrScanner();
                                        container.style.display = 'none';
                                    },
                                    function(){}
                                ).catch(function(err){ statusEl.textContent = 'Error: ' + err; });
                            }

                            function onQrCameraChange(sel){
                                if(sel.value) startQrScanner(sel.value);
                            }

                            function showDialogWithDraw(sender){

                                $("body").dialog({
                                    title: "Retitar fondos",
                                    type: 'html',
                                    width:'600px',
                                    openAnimation: 'zoom',
                                    closeAnimation: 'fade',
                                    onclose: function(){ stopQrScanner(); },
                                    content: '<div style="margin:10px;height:-webkit-fill-available">'
                                        + '<textarea id="ln-invoice" style="width:100%;min-height:150px;height:100%;font-size: 12px;line-height: 1.4em;white-space: pre-wrap;border: 1px solid silver;background: #fafafa;padding: 5px;border-radius: 4px;overflow: auto;"'
                                        + 'placeholder="Pega aquí tu factura LN, que se supone empieza con lnbc .... "></textarea>'
                                        + '<div style="margin-top:8px;text-align:center;">'
                                        + '<button type="button" id="btn-scan-qr" onclick="startQrScanner()" class="btn" style="background:#4CAF50;color:white;border:none;padding:8px 16px;cursor:pointer;border-radius:4px;">'
                                        + '<i class="fa fa-camera"></i> Escanear QR con cámara</button>'
                                        + '</div>'
                                        + '<div id="qr-scanner-container" style="display:none;margin-top:10px;text-align:center;">'
                                        + '<div style="margin-bottom:8px;">'
                                        + '<select id="qr-cam-select" onchange="onQrCameraChange(this)" style="display:none;padding:5px 10px;border-radius:4px;border:1px solid #ccc;font-size:12px;"></select>'
                                        + '</div>'
                                        + '<div id="qr-reader" style="width:100%;max-width:500px;margin:0 auto;border:2px solid #4CAF50;border-radius:4px;"></div>'
                                        + '<div style="margin-top:8px;"><button type="button" onclick="stopQrScanner();document.getElementById(\'qr-scanner-container\').style.display=\'none\';" class="btn btn-cancel" style="padding:5px 10px;font-size:11px;">Cerrar cámara</button></div>'
                                        + '</div>'
                                        + '<div id="qr-scan-status" style="margin-top:8px;font-size:12px;color:#666;text-align:center;"></div>'
                                        + '<div id="withdraw-status" style="margin-top:8px;font-size:12px;color:#666;"></div>'
                                        + '</div>',
                                    buttons: [
                                        { 
                                            text: 'Cancelar', 
                                            class: 'btn btn-cancel', 
                                            action: function(event, overlay) { document.body.removeChild(overlay); } 
                                        },
                                        { 
                                            
                                            text: 'Adelante con la retirada', 
                                            class: 'btn btn-ok', 
                                            action: async function(event, overlay) { 
   
                                                if(_withdrawPending){ return; }
                                                _withdrawPending = true;
                                                setWithdrawStatus('Procesando pago...');
                                                (overlay.querySelectorAll('.btn-ok, .btn-cancel')||[]).forEach(function(b){ b.disabled = true; });

                                                let invoice_ln = document.querySelector('#ln-invoice').value;
                                                let url = "/page/ajax/op=withdraw";
                                                let encrypted_text = str2crypt( invoice_ln, _TOKEN_ )  // ENCRYPTED TEXT

                                                fetch(url, {   
                                                    method: "POST",
                                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                                    body: new URLSearchParams({ invoice_ln: encrypted_text, token: _TOKEN_ })
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    if(data.success){
                                                        document.body.removeChild(overlay); 
                                                        success('Retirada realizada correctamente. ID: '+(data.invoiceId||''), '✅ Éxito', 7000);
                                                        return;
                                                    }else{
                                                        warning(data.error, '⚠️ Advertencia ',7000);
                                                    }   

                                               })
                                                .catch(err => {
                                                    warning('Error de red: '+err, '⚠️ Advertencia ',7000);
                                                })
                                                .finally(() => { 
                                                    _withdrawPending = false; 
                                                    setWithdrawStatus('');
                                                    (overlay.querySelectorAll('.btn-ok, .btn-cancel')||[]).forEach(function(b){ b.disabled = false; });
                                                });

                                            }

                                        }                        
                                    ]
                                });   

                            }

                        </script>
                        <div style="text-align:right;margin:10px;">
                            <a class="btn"  onclick="showDialogWithDraw(this)" style="background-color:orange;border-color:orange"><i class="fa fa-bitcoin"></i> Retirar fondos</a>
                        </div>
                        <?php 
                    echo TAB_TAB_END;  
                }
                echo sprintf(TAB_TAB_BEGIN,'tab_user_keys');
                    Table::show_table('CLI_USER_KEYS');
                echo TAB_TAB_END;  

                if (CFG::$vars['plugins']['tip_ln']) {
                    echo sprintf(TAB_TAB_BEGIN,'tab_timestamp');
                        Table::show_table('CLI_USER_TIMESTAMP');
                     echo TAB_TAB_END;  
                }

                if (Root()){
                echo sprintf(TAB_TAB_BEGIN,'tab_cookies');
                    ?>
                    <pre id="cookies-list" style="margin:10px;">
                        <?php Vars::debug_var($_COOKIE); ?>
                        token: <?=$_SESSION['token']?>
                    </pre>
                    <div style="margin:10px;">
                    <a class="btn btn-primary" id="list_cookies"><i class="fa fa-refresh"></i> <?=t('REFRESH')?></a>  
                    <a class="btn btn-danger" id="delete_cookies"><i class="fa fa-sign-out"></i> <?=t('DELETE')?></a>  
                    </div>
                    <script type="text/javascript">
                        // https://stackoverflow.com/questions/3400759/how-can-i-list-all-cookies-for-the-current-page-with-javascript

                    </script>
                    <?php
                echo TAB_TAB_END;
                } // end Root check

                if(Invitation::isRequired()) {
                    echo sprintf(TAB_TAB_BEGIN,'tab_invitations');
                ?>
                    <div id="invitations-panel" style="padding:15px;">
                        <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px; flex-wrap:wrap;">
                            <div>
                                <strong><?=t('YOUR_KARMA','Tu karma')?>:</strong> <span id="inv-karma-score">...</span>
                                &nbsp;|&nbsp;
                                <strong><?=t('COST_PER_INVITATION','Coste por invitación')?>:</strong> <?=Invitation::KARMA_COST?> pts
                            </div>
                            <button class="btn btn-primary" id="btn-generate-invitation">
                                <i class="fa fa-plus"></i> <?=t('GENERATE_INVITATION','Generar invitación')?>
                            </button>
                        </div>
                        <div id="inv-message" style="display:none; padding:10px; border-radius:4px; margin-bottom:15px;"></div>
                        <div style="margin-bottom:10px;">
                            <span id="inv-count-available">0</span> <?=t('AVAILABLE','disponibles')?> &nbsp;|&nbsp;
                            <span id="inv-count-used">0</span> <?=t('USED','usadas')?>
                        </div>
                        <table class="table table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th><?=t('CODE','Código')?></th>
                                    <th><?=t('STATUS','Estado')?></th>
                                    <th><?=t('CREATED','Creada')?></th>
                                    <th><?=t('USED_AT','Usada')?></th>
                                </tr>
                            </thead>
                            <tbody id="inv-table-body">
                                <tr><td colspan="4" style="text-align:center;color:#999;">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                <?php
                    echo TAB_TAB_END;
                } // end Invitation tab

                echo sprintf(TAB_FOOTER,'tabs-profile');
             ?></div><?php 
        } else if ($_SESSION['valid_user'])  {
            ?><p><?=t('LOGGED_IN_AS')?> <a href="/login/profile" class="navbar-link"><?=$_SESSION['username']?></a></p><?php
            if($logged['url']) {
                Messages::info(sprintf( t('HELLO_%s') , $_SESSION['username'] ),$logged['url'],2000);     
            }else if($_SESSION['backurl']){
                Messages::info('Redirect to: '.$_SESSION['backurl'],$_SESSION['backurl'],2000); 
            }else {
                Messages::info(t('LOGGED_IN_AS').' '.$_SESSION['username'].'. '.t('LOADING_PROFILE','Cargando perfil').' ...','login/profile',2000);    
            }
        } else if ($_ARGS[1] =='logout')  {
            ?>
            <p><?=t('LOGIN_OFF','Cerrando sesión ...')?></p>

            <?php
            
            if ($_ARGS[2] =='login')   Jeader('login');

        } else if (!$_SESSION['valid_user'] && $_ARGS[1] && $_ARGS[1] !='html' && $_ARGS[1] !='login' /*=='profile'*/)  {
            Jeader('login');
        } else {
            include(SCRIPT_DIR_MODULE.'/form_login.php');
            ?>
            <?php if($register_enabled){ ?>
            <p class="login-buttons">
            <a class="btn btn-reset btn-block" style="<?=$hideIfPasswordless?>" href="login/lostpassword"><?=t('LOST_PASSWORD')?></a> 
            </p>
            <?php } ?>
            <?php 
        }



    ?>
    <div id="result">  </div>
</div>
