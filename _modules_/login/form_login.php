

    <h1 class="subtitle"><?=t('REGISTERED_CUSTOMERS')?></h1>

    <?php  if($cfg['auth']  == 'ldap'){ $style='background:url(_images_/logos/windows_ad.png);background-repeat:no-repeat;background-position:98% 7%;min-height:100px;border:1px solid transparent;padding-right:171px;'; } ?>  

    <div style="<?=$style?>">
        <p class="help" style="margin-bottom:16px;margin-top:10px;border:0px solid orange;"><?=CFG::$vars['auth']=='ldap'?t('LOGIN_HELP_LDAP'):t('LOGIN_HELP')?></p>
    </div>

    <?php  if($_SESSION['message_error']){  ?>
        <p  id="message-error" class="help" style="border: 2px solid red;text-align:center;padding:4px;margin-top:0;margin-bottom:4px;"><?=$_SESSION['message_error']?></p>
        <script>
            $( document ).ready(function() {    
                $('#message-error').click(function(){$(this).hide('fast');});
                setTimeout(function(){ $('#message-error').hide('fast'); },10000);
            });
        </script>
    <?php  } ?>

    <?php
        include(SCRIPT_DIR_MODULES.'/login/form_login_login.php');
    ?> 
