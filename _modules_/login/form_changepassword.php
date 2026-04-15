<h1 class="subtitle"><?=t('CHANGE_PASSWORD')?></h1>

<p class="help">Cambio de contraseña. Mínimo 8 carácteres, con letras y números. </p>

<form class="login-form form-horizontal no-ajax" method="post" name="changepasswordform" id="changepasswordform" action="login" style="oveflow:hidden;">

  <input type="hidden" name="op" value="changepassword">

        <?php  if (CFG::$vars['auth']!='ldap'){?>
        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="oldpassword" name="oldpassword" type="password" value="" placeholder=" "><i class="fa fa-check fa-fw"></i> 
            <label for="oldpassword" class="form-label col-form-label"><?=t('OLD_PASSWORD')?></label>
        </div>
        <?php }?>
        <?php  
        if (CFG::$vars['auth']=='ldap'){
          if($login->ldap->connected()){
              $admin_ladp = in_array( CFG::$vars['ldap_group_admin'], $login->ldap->get_user_groups($_SESSION['username'])) ;
          }
        }
        ?>
        <?php  if($admin_ladp){ ?>
        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="username" name="username" type="text" value="" placeholder="<?=t('EMPTY_FOR_CHANGE_YOUR_OWN_PASSWORD')?>"><i class="fa fa-check fa-fw"></i>  
            <label for="username" class="form-label col-form-label"><?=(CFG::$vars['auth']=='ldap')?t('USERNAME'):t('EMAIL_OR_USERNAME')?></label>
        </div>
        <?php }else{?>
        <input type="hidden" name="username" value="<?=$_SESSION['username']?>">
        <input type="hidden" name="user_email" value="<?=$_SESSION['user_email']?>">
        <?php }?>

        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="newpassword" name="newpassword" type="password" value="" placeholder=" "><i class="fa fa-check fa-fw"></i> 
            <label for="newpassword" class="form-label col-form-label"><?=t('NEW_PASSWORD')?></label>
            <div id="pwdMeter" class="neutral"></div>
            <div id="ajaxmonitor" title="<?=t('GENERATE_RANDOM_PASSWORD')?>"><i class="fa fa-refresh"></i><!--<img src="<?=$imagespath?>gears.png">--></div>
        </div>

        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="confirmpassword" name="confirmpassword" type="password" value="" placeholder=" "><i class="fa fa-check fa-fw"></i> 
            <label for="confirmpassword" class="form-label col-form-label"><?=t('CONFIRM_PASSWORD')?></label>
        </div>
        <button id="btnsubmit"  
                name="btnsubmit" 
                <?php  if(CFG::$vars['captcha']['google_v3']['enabled']) echo ' data-badge="inline" data-size="invisible" data-sitekey="'.CFG::$vars['captcha']['google_v3']['public'].'" data-callback="onSubmitChangePassword" '; ?>
                type="submit" 
                class="btn btn-block btn-success g-recaptcha"><!--<i class="fa fa-check fa-fw"></i> --><?=t('CHANGE_PASSWORD')?>
        </button>

</form> 
<!--<script type="text/javascript" src="_js_/jquery/mocha.js"></script>-->
<style>
/*********
.controls-input{position:relative;}
.password{    position: relative;}
.password input[type="password"]{}
.password .fa-eye,#confirmpassword .fa-eye {display:none;position: absolute;top: 10px;cursor:pointer;}

@media screen and (min-width: 500px){
.text-after{position:absolute;left:50px;top:27px;background-color:#ccc;width:auto;min-width:230px;font-size:0.85em;cursor:pointer;opacity:0.9;z-index:20;}
}

@media screen and (max-width: 500px){
.text-after{position:absolute;left:12px;top:27px;background-color:#ccc;width:auto;min-width:230px;font-size:0.85em;cursor:pointer;opacity:0.9;z-index:20;}
}
.text-after.info{background-color:#f2f0f0;border:1px solid green;color:#a9aba7;}
.text-after.success{background-color:#93ffd7;border:1px solid green;color:green;}
.text-after.error{background-color:#ffc8c8;border:1px solid red;color:red;}
*/
</style>

<script type="text/javascript">
</script>