    <!--<link  href="<?=SCRIPT_DIR_CLASSES?>/<?=DB_ENGINE?>/css/style.new.css?ver=1.8.8" rel="stylesheet" type="text/css" />-->
    <?php
        //$isPasswordlessDefault=true;
        $hideIfPasswordless = $isPasswordlessDefault ? 'display:none;' : '';
        $showIfPasswordless = $isPasswordlessDefault ? '' : 'display:none;';
    ?>

        <?php if (OUTPUT !== 'html' && CFG::$vars['login']['nostr']['enabled']){ ?>
            <div id="nostr-login-promo" style="padding:16px 18px;margin-bottom:14px;border-radius:var(--radius, 10px);border:2px solid #8B5CF6;background:color-mix(in srgb, #8B5CF6 7%, var(--card, #fff));">
                <strong style="display:block;font-size:1.05em;margin-bottom:4px;">⚡ <?=t('NOSTR_PROMO_TITLE','Entra o regístrate con Nostr','Sign in or register with Nostr')?></strong>
                <p style="margin:0 0 12px;font-size:.9em;color:var(--muted-foreground, #666);text-align:left;">
                    <?=t('NOSTR_PROMO_TEXT','100% anónimo: sin email, sin contraseña y sin datos personales. Tu clave Nostr es tu cuenta. Puedes usar tu identidad existente, crear una nueva al instante, o firmar desde tu móvil con NostrConnect (NIP-46) sin que tu clave salga de él.','100% anonymous: no email, no password, no personal data. Your Nostr key is your account. Use your existing identity, create a new one instantly, or sign from your phone with NostrConnect (NIP-46) so your key never leaves it.')?>
                </p>
                <div style="display:flex; gap:8px;">
                    <a class="btn btn-nostr" id="btn-nostr" style="flex:1; background:#8B5CF6; color:#fff;border-radius:var(--button-border-radius);">
                        <svg style="width:16px;height:16px;vertical-align:middle;margin-right:5px;" viewBox="0 0 256 256" fill="currentColor"><path d="M128 0C57.3 0 0 57.3 0 128s57.3 128 128 128 128-57.3 128-128S198.7 0 128 0zm0 48c44.2 0 80 35.8 80 80s-35.8 80-80 80-80-35.8-80-80 35.8-80 80-80z"/></svg>
                        <?=t('LOGIN_WITH')?> Nostr
                    </a>
                    <a class="btn" id="btn-nostr-connect" href="javascript:void(0)" style="flex:1;color: #d5cbff;font-weight: 700;border-radius: var(--button-border-radius);background: linear-gradient(135deg, #8B5CF6 0%, #764ba2 100%);">
                        NostrConnect (NIP-46)
                    </a>
                </div>
                <div id="nip46-login-modal" style="display:none;margin-top:12px;padding:14px;background:var(--card, #f8f0ff);border-radius: var(--button-border-radius);border:1px solid #8B5CF6;text-align:center;">
                    <p style="font-size:12px;color:var(--muted-foreground, #555);margin:0 0 6px;"><?=t('NOSTR_PASTE_URI','Pega esta URI en tu app firmadora Nostr:','Paste this URI into your Nostr signer app:')?></p>
                    <div id="nip46-qr" style="margin:8px auto 10px;display:inline-block;"></div>
                    <textarea id="nip46-uri" rows="3" style="width:100%;font-size:10px;word-break:break-all;background:var(--card, #fff);color:inherit;padding:4px;border:1px solid var(--border, #ccc);resize:none;font-family:monospace;box-sizing:border-box;" readonly></textarea>
                    <button class="btn btn-primary" type="button" onclick="(function(){var t=document.getElementById('nip46-uri');t.select();document.execCommand('copy');})()" style="line-height: 1.3em;border-radius: var(--button-border-radius);">📋 <?=t('COPY_URI','Copiar URI','Copy URI')?></button>
                    <button class="btn btn-danger" type="button" id="nip46-cancel" style="line-height: 1.3em;border-radius: var(--button-border-radius);cursor:pointer;">✕ <?=t('CANCEL','Cancelar','Cancel')?></button>
                    <p id="nip46-status" style="margin:10px 0 4px;font-size:13px;color:var(--muted-foreground, #555);min-height:20px;"></p>
                </div>
            </div>
        <?php } ?>

        <div id="passwordless-box" style="background:var(--card, #f8f9fa);padding:20px;margin-bottom:4px;border-radius:var(--radius, 8px);border:1px solid var(--border, #0089c7);">

            <form class="login-form form-horizontal form no-ajax" method="post" name="loginform" id="loginform" action="login" style="oveflow:hidden;">
                <input type="hidden" name="op" value="login">
                <input type="hidden" name="token" value="<?=$_SESSION['token']?>">

                <div class="form-control form-group row"  <?php if(!$register_enabled){?>  autocomplete="off"<?php } ?> >
                    <input style="border-radius: var(--button-border-radius);" class="form-input XXform-control enter_as_tab " id="username" name="username" type="text" value="" placeholder=" "><i class="fa fa-check fa-fw"></i>
                    <label for="username" class="form-label col-form-label"><?=(CFG::$vars['auth']=='ldap')?t('USERNAME'):t('EMAIL_OR_USERNAME')?></label>
                </div>

                <div id="password-row" class="form-control form-group row" style="<?=$hideIfPasswordless?>">
                    <input style="border-radius: var(--button-border-radius);" class="form-input XXform-control enter_as_tab " <?php if(!$register_enabled){?>autocomplete="new-password"<?php } ?> id="password" name="password" type="password" value="" placeholder=" "><i class="fa fa-check fa-fw"></i>
                    <label for="password" class="form-label col-form-label"><?=t('PASSWORD')?></label>
                </div>

                <?php if($register_enabled || 1==1){?>
                    <div id="save-row" class="controls checkbox cookie_controlled" style="<?=$hideIfPasswordless?>">
                        <input type="checkbox" name="save" <?php if($_COOKIE['accept_cookies']!='yes') { ?>disabled<?php } ?> id="save" value="1">
                        <label for="save" <?php if($_COOKIE['accept_cookies']!='yes') { ?>style="text-decoration:line-through"<?php } ?>><?=t('REMEMBER_ME')?></label>
                    </div>
                <?php } ?>

                <button id="btnsubmit"
                        name="btnsubmit"
                        <?php  if(CFG::$vars['captcha']['google_v3']['enabled']) echo ' data-badge="inline" data-size="invisible" data-sitekey="'.CFG::$vars['captcha']['google_v3']['public'].'" data-callback="onSubmitLogin" '; ?>
                        type="submit"
                        style="<?=$hideIfPasswordless?>; border-radius: var(--button-border-radius);"
                        class="btn btn-block btn-success g-recaptcha"><!--<i class="fa fa-check fa-fw"></i> --><?=t('SIGN_IN')?>
                </button>
            </form> 

            <?php              
                $isOnion = strpos($_SERVER['HTTP_HOST'], '.onion') !== false;    // Detect Tor (.onion)
            ?>

            <?php if (CFG::$vars['login']['passwordless']['enabled']){ ?>
                <a class="btn btn-block" id="btn-passwordless" href="javascript:void(0)" style="<?=$showIfPasswordless?>; border-radius: var(--button-border-radius);line-height: 1.3em;">🔐 <?=t('LOGIN_PASSWORDLESS')?></a>
            <?php } ?>

        </div> <!-- /passwordless-box -->
            


        <div id="login-links">

            <?php if($register_enabled){?> 
                <a href="login/register"> ✨ <?=t('NO_ACCOUNT_REGISTER_HERE')?><?php if ($isPasswordlessDefault) { ?> <span class="only-passwordless"><?=t('NO_PASSWORD_NEEDED')?></span><?php } ?> </a>
            <?php } ?>
            <?php if ($isPasswordlessDefault) { ?>
                <a class="only-passwordless" href="javascript:void(0)" onclick="DeviceLinking.showLinkForm()" > 🔗 <?=t('HAVE_LINK_CODE')?> </a>
                <a  class="only-passwordless" href="javascript:void(0)" onclick="DeviceLinking.showMagicLinkForm()"> 🔑 <?=t('LOST_DEVICE_ACCESS')?> </a>
            <?php } ?>
            <?php if (CFG::$vars['login']['passwordless']['enabled']){ ?>
            <a href="javascript:void(0)" id="toggle-login-mode" onclick="DeviceLinking.toggleLoginMode()"> 🔒 <span><?=$showIfPasswordless?t('SIGN_PASSWORDLESS'):t('SIGN_IN_WITH_PASSWORD')?></span> </a>
            <?php } ?>
        </div>



        <div>
            <?php
                if (CFG::$vars['oauth']['google']['enabled']){
                    require_once './_modules_/login/google.php';
                    $authUrl = $client->createAuthUrl();
                    if ($authUrl) {
                        if (CFG::$vars['oauth']['google']['popup'])
                            $authHref = "javascript:PopupCenter('{$authUrl}','Google',450,600)"; 
                        else
                            $authHref = $authUrl;  
                        
                        ?>
                        <a class='login btn-google' style="border-radius: var(--button-border-radius);" href="<?php echo $authHref; ?>"><img src="<?=SCRIPT_DIR_IMAGES?>/logos/google.png"> <?=t('LOGIN_WITH')?> Google</a>
                        <!-- iframe not allowed by google
                        <a class='open_href btn-google' data-type="iframe" data-href="<?php echo $authUrl; ?>"><img src="<?=SCRIPT_DIR_IMAGES?>/logos/google.png"> <?=t('LOGIN_WITH')?> Google</a>
                        -->
                        <!--  ¿Y'¡¿Y'¡¿Y'¡  -->
                        <?php 
                    }
                }
            ?>
        </div>


