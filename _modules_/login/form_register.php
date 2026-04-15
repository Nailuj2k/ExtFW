<h1 class="subtitle"><?=t('WELCOME_TO').' '.CFG::$vars['site']['title']?></h1>

<?php 
  $_register_enabled = CFG::$vars['login']['register_disabled'] ?? false;  
?>

<?php if (CFG::$vars['auth']=='ldap' || $_register_enabled) { ?>

  <p><?=t('REGISTER_DISABLED')?></p>

<?php  }else{ ?>

    <div  class="help">
    <?php echo CFG::$vars['register_form']['text']; ?>
    </div>

    <?php if(Invitation::isRequired()) { ?>
    <div id="invitation-gate" style="text-align:center; padding:30px 20px;border:2px solid #ccc; border-radius:8px; background:#f9f9f9;">
        <p style="color:#666; margin-bottom:20px; font-size:1.05em;">
            <?=t('INVITATION_REQUIRED_MSG','Para registrarte necesitas un código de invitación.')?>
        </p>
        <div class="form-control form-group row" style="max-width:350px; margin:0 auto 15px auto;">
            <input class="form-input" id="shared-invitation-code" type="text" autocomplete="off" placeholder=" " style="text-align:center; font-size:1.2em; letter-spacing:2px;"><i class="fa fa-check fa-fw"></i>
            <label for="shared-invitation-code" class="form-label col-form-label"><?=t('INVITATION_CODE','Código de invitación')?></label>
        </div>
        <div id="invitation-gate-msg" style="display:none; padding:8px; border-radius:4px; margin-bottom:10px; max-width:350px; margin-left:auto; margin-right:auto;"></div>
        <button type="button" id="btn-validate-invitation" class="btn btn-primary"><?=t('CONTINUE','Continuar')?></button>
    </div>
    <input type="hidden" id="shared-invitation-code-value" value="">
    <div id="register-forms-container" style="display:none;">
    <?php } ?>

    <?php if(Invitation::isRequired()) { ?>
    <script>
    document.getElementById('btn-validate-invitation').addEventListener('click', function(){
        var code = document.getElementById('shared-invitation-code').value.trim();
        var msg = document.getElementById('invitation-gate-msg');
        if(!code){ msg.style.display='block'; msg.style.background='#fee'; msg.style.color='#c00'; msg.textContent='Introduce un código'; return; }
        var btn = this; btn.disabled = true; btn.textContent = '...';
        fetch('login/ajax/op=validate_invitation',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'code='+encodeURIComponent(code)})
        .then(function(r){return r.json()}).then(function(data){
            if(data.error){
                msg.style.display='block'; msg.style.background='#fee'; msg.style.color='#c00'; msg.textContent=data.msg||'Código no válido';
                btn.disabled=false; btn.textContent='<?=t('CONTINUE','Continuar')?>';
            }else{
                document.getElementById('shared-invitation-code-value').value = code;
                document.getElementById('invitation_code_hidden').value = code;
                document.getElementById('invitation-gate').style.display = 'none';
                document.getElementById('register-forms-container').style.display = 'block';
            }
        }).catch(function(){
            msg.style.display='block'; msg.style.background='#fee'; msg.style.color='#c00'; msg.textContent='Error de conexión';
            btn.disabled=false; btn.textContent='<?=t('CONTINUE','Continuar')?>';
        });
    });
    document.getElementById('shared-invitation-code').addEventListener('keydown', function(e){
        if(e.key==='Enter'){ e.preventDefault(); document.getElementById('btn-validate-invitation').click(); }
    });
    </script>
    <?php } ?>

    <!-- Opción de registro passwordless -->
    <div id="passwordless-register-container" style="<?=$isPasswordlessDefault?'display:block;':'display:none;'?> background: #f8f9fa; padding: 20px 20px 5px 20px; border-radius: 8px; margin-bottom: 10px; border: 2px solid rgba(29, 119, 193, 0.83);">
        <h3 style="margin-top: 0; color: #8B5CF6;">✨ <?=t('FAST_REGISTRATION_PASSWORDLESS')?></h3>
        <p style="color: #666; margin-bottom: 15px;">
            <?=t('FAST_REGISTRATION_PASSWORDLESS_INFO')?>
        </p>

        <div class="form-control form-group row">
            <input
                class="form-input"
                id="passwordless-email"
                name="email"
                type="email"
                placeholder=" "
            >
            <i class="fa fa-check fa-fw"></i>
            <label for="passwordless-email" class="form-label col-form-label">Email</label>
        </div>

        <div id="register-passwordless-status" style="
            display: none;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
        "></div>

        <button
            type="button"
            id="btn-register-passwordless"
            class="btn btn-block btn-success"
            style="background: rgba(29, 119, 193, 0.83);; border-color: rgba(29, 119, 193, 0.83);"
        >
            🔐 <?=t('REGISTER_PASSWORDLESS')?>
        </button>

        <p style="text-align: center; margin-top: 15px; font-size: 0.9em; color: #999;">
            <a href="javascript:void(0)" onclick="toggleRegisterMode()" style="color: #666;">
                ← <?=t('BACK_TO_PASSWORD_REGISTER')?>
            </a>
        </p>
    </div>

    <script>
    function toggleRegisterMode() {
        const passwordlessContainer = document.getElementById('passwordless-register-container');
        const traditionalForm = document.getElementById('registerform');
        const toggleLink = document.getElementById('toggle-register-link');

        if (passwordlessContainer.style.display === 'none') {
            // Mostrar passwordless, ocultar tradicional
            passwordlessContainer.style.display = 'block';
            traditionalForm.style.display = 'none';
            if (toggleLink) toggleLink.style.display = 'none';
        } else {
            // Mostrar tradicional, ocultar passwordless
            passwordlessContainer.style.display = 'none';
            traditionalForm.style.display = 'block';
            if (toggleLink) toggleLink.style.display = 'block';
        }
    }
    </script> 


<form class="login-form form-horizontal form no-ajax" method="post" name="registerform" id="registerform"  action="login" 
      style="oveflow:hidden;<?=$isPasswordlessDefault?'display:none;':'display:block;'?>background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 2px solid #8B5CF6;">

  
    <input type="hidden" name="op" value="register">
    <input type="hidden" name="invitation_code" id="invitation_code_hidden" value="">
  
    <?php 
    $_ARGS['user_fullname']='';
    $_ARGS['user_email']='';
    $_ARGS['password']='';
    $_ARGS['password2']='';
    $_ARGS['conditions']='';
    $_ARGS['user_card_id'] = '';
    $_ARGS['register_code'] = '';
    ?>

    <?php  if(CFG::$vars['login']['username']['required']) {?>
                        <div class="form-control form-group row">  
                            <input class="form-input XXform-control enter_as_tab " id="username" name="username" autocomplete="off" type="text" value="<?=$_ARGS['ID']?>" placeholder=" "><i class="fa fa-check fa-fw"></i>  
                            <label for="username" class="form-label col-form-label"><?=t('USERNAME')?></label>
                            <span style="display:none;" class="text-after"> ...</span>
                        </div>                            

    <?php  } ?>   

                        <div class="form-control form-group row">  
                            <input class="form-input XXform-control enter_as_tab required " id="user_fullname" name="user_fullname" autocomplete="off" type="text" value="<?=$_ARGS['user_fullname']?>" placeholder=" "><i class="fa fa-check fa-fw"></i>  
                            <label for="user_fullname" class="form-label col-form-label"><?=t('FULL_NAME')?></label>
                            <span style="display:none;" class="text-after"> ...</span>
                        </div>

                        <div class="form-control form-group row">  
                            <input class="form-input XXform-control enter_as_tab required" id="user_email" name="user_email" autocomplete="off" type="email" value="<?=$_ARGS['user_email']?>" placeholder=" "><i class="fa fa-check fa-fw"></i>  
                            <label for="user_email" class="form-label col-form-label"><?=t('EMAIL')?></label>
                            <span style="display:none;" class="text-after"> ...</span>
                        </div>


    <?php  if(CFG::$vars['login']['card_id']['required']) {?>
                        <div class="form-control form-group row">  
                            <input class="form-input XXform-control enter_as_tab " id="user_card_id" name="user_card_id" autocomplete="off" type="text" value="<?=$_ARGS['user_card_id']?>" placeholder=" "><i class="fa fa-check fa-fw"></i>  
                            <label for="user_card_id" class="form-label col-form-label"><?=t('CARD_ID')?></label>
                            <span style="display:none;" class="text-after"> ...</span>
                        </div>
    <?php }?>

    <?php  if(CFG::$vars['login']['register_code']['required']) {?>
                        <div class="form-control form-group row">  
                            <input class="form-input XXform-control enter_as_tab " id="register_code" name="register_code" autocomplete="off" type="text" value="<?=$_ARGS['register_code']?>" placeholder=" "><i class="fa fa-check fa-fw"></i>  
                            <label for="register_code" class="form-label col-form-label"><?=t('REGISTER_CODE')?></label>
                            <span style="display:none;" class="text-after"> ...</span>
                        </div>
    <input type="hidden" id="register_code_key" name="register_code_key" value="0">
    <?php }?>

        <div class="form-control form-group row">
            <input class="form-input XXform-control enter_as_tab " id="password" name="password" type="password" value="<?=$_ARGS['password']?>" placeholder=" "><i class="fa fa-check fa-fw"></i> 
            <label for="password" class="form-label col-form-label"><?=t('PASSWORD')?></label>
            <div id="pwdMeter" class="neutral"></div>
            <div id="ajaxmonitor" title="<?=t('GENERATE_RANDOM_PASSWORD')?>"><i class="fa fa-refresh"></i><!--<img src="<?=$imagespath?>gears.png">--></div>
        </div>

        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="password2" name="password2" type="password" value="<?=$_ARGS['password2']?>" placeholder=" "><i class="fa fa-check fa-fw"></i> 
            <label for="password2" class="form-label col-form-label"><?=t('CONFIRM_PASSWORD')?></label>
        </div>


    <?php 
    if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']) {

    }else if(CFG::$vars['captcha']['enabled']){ 
         $_captcha = Captcha::create();
        ?>
        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="captcha" name="captcha" type="text" value=""  autocomplete="off"  placeholder="<?=$_captcha['label']?>"  title="<?=$_captcha['help']?>"><i class="fa fa-check fa-fw"></i>  
            <label for="captcha" class="form-label col-form-label" title="<?=$_captcha['help']?>"><?=t('RESOLVE_ANTI_SPAM')?></label>
        </div>
        <?php 
    }else{

    }
    
    ?>

    <?php if (CFG::$vars['site']['lpd_accept']['required']) { ?>
        <div class="controls control-group row">
            <div id="data-protect-info">
                <!--<a class="btn btn-warning" style="margin:5px;/*width:384px;*/"><i class="fa fa-info-circle"></i> Información básica sobre protección de datos. (<b>leer más</b>) &nbsp; </a>-->
                <p class="xbtn xbtn-warning" ><i class="fa fa-info-circle"></i> <?=t('BASIC_INFORMATION_DATA_PROTECTION')?> (<b style="cursor:pointer;" id="btn-show-lpd"><?=t('READ_MORE')?></b>)</p>
                <?=CFG::$vars['templates']['lpd']['text']?>
            </div>
        </div>
        <div class="controls checkbox cookie_controlled">
            <input type="checkbox" name="conditions" <?php if($_COOKIE['accept_cookies']!='yes') { ?>disabled<?php } ?> id="conditions" value="1">
            <label for="conditions"><?=t('ACCEPT_PRIVACY_POLICIES')?> <a target="new" href="page/datos_seguros"><?=t('YOUR_DATA_SAFE')?></a>. <span style="color:red">(<?=t('REQUIRED')?>)</span></label>
        </div>
        <div class="controls checkbox cookie_controlled">
            <input type="checkbox" name="chk_publi" <?php if($_COOKIE['accept_cookies']!='yes') { ?>disabled<?php } ?> id="chk_publi" value="1">
            <label for="chk_publi"><?=t('ACCEPT_PUBLICITY_POLICIES')?></label>
        </div>
    <?php }else{?>    
        <div class="controls checkbox cookie_controlled">
            <input type="checkbox" name="conditions" <?php if($_COOKIE['accept_cookies']!='yes') { ?>disabled<?php } ?> id="conditions" value="1">
            <label for="conditions"><?=t('ACCEPT_COOKIE_POLICES')?></label>
        </div>
    <?php }?>

    <button id="btnsubmit"
            name="btnsubmit"
            <?php  if(CFG::$vars['captcha']['enabled']&&CFG::$vars['captcha']['google_v3']['enabled']) echo ' data-badge="inline" data-size="invisible" data-sitekey="'.CFG::$vars['captcha']['google_v3']['public'].'" data-callback="onSubmitRegister" '; ?>
            type="submit"
            class="btn btn-block btn-success g-recaptcha"><!--<i class="fa fa-check fa-fw"></i> --><?=t('REGISTER')?>
    </button>

    <!-- Link para cambiar a registro passwordless -->
    <?php if (CFG::$vars['login']['passwordless']['enabled']){ ?>
    <p id="toggle-register-link" style="text-align: center; margin-top: 15px; font-size: 0.9em;">
        <a href="javascript:void(0)" onclick="toggleRegisterMode()" style="color: #8B5CF6; font-weight: 500;">
            ✨ <?=t('PREFER_REGISTER_PASSWORDLESS')?>
        </a>
    </p>
    <?php } ?>

</form>  

    <?php

    if (CFG::$vars['login']['nostr']['enabled']){
        ?>
        <div  style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 2px solid #8B5CF6;">
            <h3 style="margin-top: 0; color: #8B5CF6;">✨ <?=t('REGISTER_WITH_NOSTR','Registro con Nostr')?></h3>
            <p style="color: #666; margin-bottom: 15px;">
                <?=t('REGISTRATION_NOSTR_INFO','El registro con Nostr es rápido y seguro.','Nostr register is fast and secure.')?>
            </p>
            <a class="btn btn-block btn-nostr" id="btn-nostr-register" style="background:#8B5CF6;color:#fff;margin-top:10px;">
                <svg style="width:16px;height:16px;vertical-align:middle;margin-right:5px;" viewBox="0 0 256 256" fill="currentColor"><path d="M128 0C57.3 0 0 57.3 0 128s57.3 128 128 128 128-57.3 128-128S198.7 0 128 0zm0 48c44.2 0 80 35.8 80 80s-35.8 80-80 80-80-35.8-80-80 35.8-80 80-80z"/></svg>
                <?=t('REGISTER_WITH')?> Nostr
            </a>
        </div>
        <?php
    }




    if (CFG::$vars['oauth']['google']['enabled']){
        ?><div><?php
        require_once './_modules_/login/google.php';
        $authUrl = $client->createAuthUrl();
        if (CFG::$vars['oauth']['google']['popup'])
            $authHref = "javascript:PopupCenter('{$authUrl}','Google',450,600)"; 
        else
            $authHref = $authUrl;  
        ?>
        <a class='Xbtn Xbtn-block Xbtn-link login btn-google' href="<?php echo $authHref; ?>"><img src="<?=SCRIPT_DIR_IMAGES?>/logos/google.png"> <?=t('REGISTER_WITH')?> Google</a>
        <!--  👣👣👣  -->
        </div>
        <?php 
    }




    if(Invitation::isRequired()) { echo '</div><!-- /register-forms-container -->'; }

    ?>



<?php  } ?>
