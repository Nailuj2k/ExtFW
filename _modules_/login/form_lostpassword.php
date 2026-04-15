<h1 class="subtitle"><?=t('LOST_PASSWORD')?></h1>

<?php  if (CFG::$vars['auth']=='ldap') { ?>
  <p><?=t('LOST_PASSWORD_DISABLED')?></p>
<?php  }else{ ?>

<p class="help">Escriba su nombre de usuario o el email con el que se registró y envíe el formulario. Recibirá un enlace de activación.</p>

<form class="login-form form-horizontal no-ajax" method="post" name="lostpasswordform" id="lostpasswordform" action="login">
  
  <input type="hidden" name="op" value="lostpassword">
  
    <div class="form-control form-group row">  
        <input class="form-input XXform-control enter_as_tab " id="username" name="username" type="text" value="" placeholder=" "><i class="fa fa-check fa-fw"></i>  
        <label for="username" class="form-label col-form-label"><?=(CFG::$vars['auth']=='ldap')?t('USERNAME'):t('EMAIL_OR_USERNAME')?></label>
    </div>

    <?php 
    if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']) {

    }else if(CFG::$vars['captcha']['enabled']) {
    
        $_captcha = Captcha::create();
        ?>
        <div class="form-control form-group row">  
            <input class="form-input XXform-control enter_as_tab " id="captcha" name="captcha" type="text" value=""  autocomplete="off"  placeholder="<?=$_captcha['label']?>"  title="<?=$_captcha['help']?>"><i class="fa fa-check fa-fw"></i>  
            <label for="captcha" class="form-label col-form-label" title="<?=$_captcha['help']?>">Resolver (Anti-spam)</label>
        </div>
        <?php 
    }
    ?>

    <button id="btnsubmit"  
            name="btnsubmit" 
            <?php  if(CFG::$vars['captcha']['enabled']&&CFG::$vars['captcha']['google_v3']['enabled']) echo ' data-badge="inline" data-size="invisible" data-sitekey="'.CFG::$vars['captcha']['google_v3']['public'].'" data-callback="onSubmitLostPassword" '; ?>
            type="submit" 
            class="btn btn-block btn-success g-recaptcha"><!--<i class="fa fa-check fa-fw"></i> --><?=t('SEND')?>
    </button>

</form>  
<?php  } ?>
