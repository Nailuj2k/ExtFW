<?php

//  addScript https://codepen.io/jreviews/pen/JjOMmYO 

// Incluir nuestro visor básico independiente
//include_once(SCRIPT_DIR_INCLUDES . '/basic_viewer.php');

//HTML::js('https://unpkg.com/default-passive-events');
//HTML::js(SCRIPT_DIR_JS . '/default-passive-events.js');


HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.js?v=A'.VERSION);
//HTML::js(SCRIPT_DIR_JS . '/jquery/jquery-3.7.1.min.js');

//HTML::js(SCRIPT_DIR_JS . '/jquery/jquery-4.0.0-beta.2.min.js');

//HTML::js(SCRIPT_DIR_JS . '/wquery.js?v='.VERSION

//HTML::js(SCRIPT_DIR_JS . '/wquery.formcompat.js?v='.VERSION);

HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.draggable.js?v='.VERSION);
//HTML::js(SCRIPT_DIR_JS . '/jquery/jquery.draggable.js');


HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.dialog.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.dialog.overrides.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.dialog.gallery.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.flyto.js?v='.VERSION);
HTML::js(SCRIPT_DIR_LIB . '/jquery/jquery.effects.js?v='.VERSION);                      //FIX
//HTML::js(SCRIPT_DIR_JS . '/jquery/jquery.center.js');
HTML::js(SCRIPT_DIR_JS . '/simpleTabs/simpleTabs.js?v='.VERSION);
//HTML::js(SCRIPT_DIR_JS . '/wquery.tabs.js');
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.modalform.js');
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.form.js?v='.VERSION); // form-interceptor.js
//HTML::js(SCRIPT_DIR_JS . '/ohSnap/ohsnap.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/crypto-js.min.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/crypt.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/cookie.js?v='.VERSION);
HTML::js(SCRIPT_DIR_JS . '/script.js?v='.VERSION);

HTML::js(SCRIPT_DIR_JS.'/passwordless.js?ver='.VERSION,'defer');
HTML::js(SCRIPT_DIR_MODULES.'/login/device_linking.js?ver='.VERSION,'defer');

////////////////////HTML::js(SCRIPT_DIR_JS . '/simple-viewer/simple-viewer.js?v='.VERSION)


//if ($editable || MODULE=='control_panel'  || MODULE=='contact' ){
   

if ($db_engine == 'scaffold') {
    
    //HTML::js(SCRIPT_DIR_LIB.'/tinymce/tinymce.min.js');
    //HTML::js(SCRIPT_DIR_JS . '/jquery/jquery-ui.min.js');
    //if ($editable ){
        HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.sortable.js?v='.VERSION);
        HTML::js(SCRIPT_DIR_JS . '/sortable.js?v='.VERSION);
    //}else{
    //   echo '<script>function init_sortable(){}</script>';
    //}
    
} else  if ($db_engine == 'crud') {
    
    Table::js();

}

if ($db_engine)
    HTML::js(SCRIPT_DIR_CLASSES . '/' . $db_engine . '/script.js?v='.VERSION);

if ($db_engine == 'scaffold') 
    Table::init();

//HTML::js(SCRIPT_DIR_JS . '/jquery/jquery.tablednd.min.js?v='.VERSION);                       //FIX

//}

HTML::js(SCRIPT_DIR_LIB . '/jquery/jquery.qrcode.min.js');

//HTML::js(SCRIPT_DIR_JS . '/slider/slider.js?v='.VERSION);                 // ...  if set var in theme ??
//HTML::js(SCRIPT_DIR_MODULE.'/script.js?v='.VERSION);

if(defined('MODULE_SHOP'))
if (MODULE_SHOP) {

    ?>
        <script>
            var module = '<?= $_ARGS[0] ?>';
            var __storage = window.<?= CFG::$vars['shop']['localstorage'] == true ? 'local' : 'session' ?>Storage;
        </script>
    <?php

   if(CFG::$vars['shop']['options']['animation']) {
    //   HTML::js(SCRIPT_DIR_JS.'/jquery/jquery.easing.js');
      // HTML::js('https://cdn.jsdelivr.net/npm/jquery-path@0.0.1/jquery.path.js');
   }

    HTML::js(SCRIPT_DIR_LIB . '/jxCart/jxCart.js?v='.VERSION);
    HTML::js(SCRIPT_DIR_MODULES . '/' . MODULE_SHOP . '/cart.js?v='.VERSION, 'async');
    if (MODULE == 'shop')
        HTML::js(SCRIPT_DIR_MODULE . '/script.js?v='.VERSION, 'async');

}


?>
<script>
    var accept_cookies = <?= CFG::$vars['site']['cookies']['accept'] === true ? 'true' : 'false' ?>;
</script>
<?php

if ($documents || $files || $gallery) {
    
    //HTML::js(SCRIPT_DIR_JS.'/pdf.js/build/pdf.js');
    //HTML::js(SCRIPT_DIR_JS.'/pdf.js/build/pdf.worker.js');
    //HTML::js(SCRIPT_DIR_JS.'/pdfobject.min.js');
    //HTML::js('https://unpkg.com/pdfobject@2.2.6/pdfobject.min.js');
    
    //include_once(SCRIPT_DIR_LIB.'/file_viewer/pdf_viewer.php'); 
    if(CFG::$vars['plugins']['epub']){
       include_once(SCRIPT_DIR_LIB.'/file_viewer/epub_viewer.php'); 
    }else{
       ?><script>function load_epub(file){error('Plugin EPUB not activated.<br />Cane be activate by adding entry<br />plugins.epub in Control Panel / Settings ');}</script><?php
    }

    //include_once(SCRIPT_DIR_LIB.'/file_viewer/txt_viewer.php'); 
    //include_once(SCRIPT_DIR_LIB.'/file_viewer/url_viewer.php'); 
    include_once(SCRIPT_DIR_LIB.'/file_viewer/json_viewer.php');    
    
    //HTML::js(SCRIPT_DIR_LIB . '/file_viewer/pdf_viewer.js?v='.VERSION);
    //////////////////////////   HTML::js(SCRIPT_DIR_LIB . '/file_viewer/epub_viewer.js?v='.VERSION);
    HTML::js(SCRIPT_DIR_LIB . '/file_viewer/json_viewer.js?v='.VERSION);
    //HTML::js(SCRIPT_DIR_LIB . '/file_viewer/txt_viewer.js?v='.VERSION);
    //HTML::js(SCRIPT_DIR_LIB . '/file_viewer/url_viewer.js?v='.VERSION);
    
}

if(CFG::$vars['options']['highlight_code']===true ){
 
    //HTML::js('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js');
    if(CFG::$vars['options']['highlight_engine']==='prism' )
        HTML::js(SCRIPT_DIR_LIB.'/prism/prism.js');
    else
        HTML::js(SCRIPT_DIR_LIB.'/highlight.js/highlight.min.js');

    // and it's easy to individually load additional languages
    //HTML::js('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/sql.min.js');

    //HTML::js('https:///prismjs@v1.x/plugins/autoloader/prism-autoloader.min.js');

}

HTML::render('js');

?>
<script type="text/javascript">
    $(function() {
        <?=$_JS_WIDGETS?>
        <?php if(CFG::$vars['options']['highlight_code']===true ) {

            if(CFG::$vars['options']['highlight_engine']==='prism' ){  
                ?>Prism.highlightAll();<?php 
            } else {
                ?>hljs.configure({cssSelector:'pre'}); hljs.highlightAll();<?php
            }

        } ?>
    })   
</script>
<script type="module" defer>
  /*
  import { polyfillCountryFlagEmojis } from "https://cdn.skypack.dev/country-flag-emoji-polyfill";
  polyfillCountryFlagEmojis();
  */
</script>
<?php

$accept_cookies = $_COOKIE['accept_cookies'];

if (CFG::$vars['site']['cookies']['accept'] === true) {
    
    if (!$accept_cookies) {
        ?><script type="text/javascript">
            $(function() {
                setCookie('accept_cookies', 'yes', 365);
            });
            </script>
        <?php
    }
    
} else if ($accept_cookies != 'yes') {
    ?>
    <div id="div_cookies">
        <div class="inner"><?= CFG::$vars['cookies']['text'] ?></div>
        <div class="inner">
            <?php if ($check0 = 1) { ?>
                <input type="checkbox" name="op_ck_tch" id="op_ck_tch" checked><label for="op_ck_tch"> <?= t('I_ACCEPT_THECNICAL_COOKIES') ?></label> <i class="fa fa-info-circle" title="Necesarias para que nuestra web pueda funcionar."></i><br />
                <?php if (CFG::$vars['site']['all_cookies']['check']) { ?>
                    <input type="checkbox" name="op_ck_anl" nochecked id="op_ck_anl"><label for="op_ck_anl"> <?= t('I_ACCEPT_ANALYSIS_COOKIES') ?></label> <i class="fa fa-info-circle" title="Para la mejora continua de nuestra web. Puede activarlas o no."></i><br />
                    <input type="checkbox" name="op_ck_fun" nochecked id="op_ck_fun"><label for="op_ck_fun"> <?= t('I_ACCEPT_FUNCTIONAL_COOKIES') ?></label> <i class="fa fa-info-circle" title="Para mejorar la funcionalidad y personalización de nuestra web en base a sus preferencias. Puede activarlas o no."></i><br />
                    <input type="checkbox" name="op_ck_pub" nochecked id="op_ck_pub"><label for="op_ck_pub"> <?= t('I_ACCEPT_PUBLICITY_COOKIES') ?></label> <i class="fa fa-info-circle" title="Para mejorar la gestión de la publicidad mostrada en nuestra web y ajustarla a sus búsquedas, gustos e intereses personales. Puede activarlas o no"></i><br />
                <?php  } ?>
            <?php } ?>
            <button id="yes_cookies" class="btn btn-ok " alt="acepta cookies"> <?= t('Cerrar') ?> <i class="fa fa-check"></i> </button>
            <button id="no_cookies" class="btn btn-danger " alt="Rechazar cookies"> <?= t('REJECT_ALL', 'Rechazar todas') ?> <i class="fa fa-check"></i> </button>
            <!--<button id="no_cookies" class="btn btn-success " alt="oculta aviso"> <?= t('IGNORE') ?> <i class="fa fa-close"></i> </button>-->
        </div>
    </div>
    <script type="text/javascript">
        $(function() {
                    
            $('#div_cookies .fa-info-circle').click(function() {
                show_info('bottom', $(this).attr('title'), 10000);
            });
                    
            $('body').on('click', '#div_cookies #yes_cookies', function(e) {
                if ($('#op_ck_tch').is(":checked")) {
                    setCookie('accept_cookies', 'yes', 365);
                    $('.cookie_controlled input').removeAttr('disabled');
                    $('.cookie_controlled label').css('text-decoration', '');
                }
                if ($('#op_ck_anl').is(":checked")) setCookie('accept_cookies_anl', 'yes', 365);
                if ($('#op_ck_fun').is(":checked")) setCookie('accept_cookies_fun', 'yes', 365);
                if ($('#op_ck_pub').is(":checked")) setCookie('accept_cookies_pub', 'yes', 365);
                $('#div_cookies').fadeOut('fast'); //.css('display','none');
            });

            $('body').on('click', '#div_cookies #no_cookies', function(e) {
                //setCookie('ageok','no',365);
                //setCookie('accept_cookies',0,365);
                //var object = getCookies();
                //for (var property in object) { deleteCookie(object[property].key); }
                $('#div_cookies').fadeOut('fast'); //css('display','none');
            });
            
            if (getCookie('accept_cookies')) {
                $('#op_ck_tch').attr('checked', true);
            }
            
        });
    </script>
    <?php
}

           
//if (file_exists(SCRIPT_DIR_MODULE . '/footer.php')) 
include(SCRIPT_DIR_MODULE . '/footer.php');

// Karma::showUserScore();

if ($_SESSION['message_error']) Messages::error($_SESSION['message_error']); //,'/',15000); 
if ($_SESSION['message_info'])  Messages::info($_SESSION['message_info']);

Messages::show();

$_SESSION['message_error'] = false;
$_SESSION['message_info'] = false;



t('ZERO','cero');
t('ONE','uno');
t('TWO','dos');
t('THREE','tres');
t('FOUR','cuatro');
t('FIVE','cinco');
t('SIX','seis');
t('SEVEN','siete');
t('EIGHT','ocho');
t('NINE','nueve');
t('TEN','diez');
t('ELEVEN','once');
t('TWUELVE','doce');
t('THIRTEEN','trece');
t('FOURTEEN','catorce');
t('FIFTEEN','quince');
t('SIXTEEN','dieciseis');
t('TWENTY-FIVE','veinticinco');
t('THIRTY-SIX','treintayseis');
t('FORTY-NINE','cuarentaynueve');
t('SIXTY-FOUR','sesentaycuatro');
t('EIGHTY-ONE','ochentayuno');
t('HUNDRED','cien');
t('PER','por');
t('PLUS','mas');
t('MINUS','menos');
t('SQUARED', 'al cuadrado');
t('SQUARE_ROOT_OF','raiz cuadrada de');
t('RELOAD_CAPTCHA',' Cambiar captcha');
t('SPAM_PROTECTION','Protección anti-spam');

t('NEWS', 'Noticias');
t('DOCS', 'Documentos','Documents');
t('SAVE','Guardar');
t('EMAIL','Correo electrónico');
t('UNKNOWN','Desconocido');
t('SIGN_IN', 'Iniciar sesión');
t('COMMENTS','Comentarios');
t('USERNAME','Nombre de usuario');
t('REGISTER', 'Registrar');
t('PASSWORD', 'Contraseña');
t('REQUIRED','Obligatorio');
t('READ_MORE','leer más');
t('YOUR_PHONE','Su teléfono');
t('FULL_NAME', 'Nombre completo');
t('WELCOME_TO','Bienvenido a');
t('LOGIN_WITH','Login con');
t('REMEMBER_ME', 'Recuérdame','Remember me');
t('MSG_TO_USER','Mensaje al usuario');
t('REGISTER_WITH','Regístrate con');
t('REGISTER_CODE','Código de registro','Register code');
t('MSG_FROM_USER','Mensaje del usuario');
t('YOUR_DATA_SAFE','Sus datos seguros');
t('HAVE_LINK_CODE','¿Tienes un código de vinculación?','Have link code?');
t('CONFIRM_PASSWORD', 'Confirmar contraseña','Confirm password');
t('SIGN_PASSWORDLESS','Prefiero usar acceso sin contraseña');
t('REGISTER_DISABLED', 'El registro de nuevos usuarios está deshabilitado actualmente','Registration of new users is currently disabled');
t('RESOLVE_ANTI_SPAM','Resolver (Anti-spam)','Resolve (Anti-spam)');
t('NEW_PASSWORD_SENT', 'Nueva contraseña enviada. Consulte su correo electrónico','New password sent. Check your email');
t('EMAIL_OR_USERNAME', 'Email o nombre de usuario','Email or username');
t('LOGIN_PASSWORDLESS', 'Acceder sin contraseña');
t('NO_PASSWORD_NEEDED','No se necesita contraseña');
t('LOST_DEVICE_ACCESS','¿Perdiste acceso a tus dispositivos?','Lost device access?');
t('SIGN_IN_WITH_PASSWORD','Prefiero usar contraseña');
t('ACCEPT_COOKIE_POLICES','Acepto el uso de cookies para los fines indicados en la política de cookies');
t('REGISTER_PASSWORDLESS','Registrarme sin contraseña');
t('PASSWORD_REMINDER_MSG','Use el siguiente enlace para recibir una nueva contraseña:','Use this link for receive a new password:');
t('ACCEPT_PRIVACY_POLICIES','Acepto el uso de mis datos para los fines indicados en la política de privacidad');
t('NO_ACCOUNT_REGISTER_HERE','¿No tienes cuenta? Regístrate aquí.','No account? Register here. No password needed');
t('GENERATE_RANDOM_PASSWORD', 'Generar una contraseña aleatoria');
t('I_ACCEPT_ANALYSIS_COOKIES','Acepto las cookies de análisis para la mejora continua de la web');
t('BACK_TO_PASSWORD_REGISTER','Volver al registro con contraseña');
t('PASSWORD_REMINDER_SUBJECT','Recordatorio de contraseña','Password reminder');
t('ACCEPT_PUBLICITY_POLICIES','Acepto el uso de mis datos para los fines indicados en la política de publicidad');
t('NEW_PASSWORD_MAIL_SUBJECT','Nueva contraseña','New password');
t('I_ACCEPT_THECNICAL_COOKIES','Acepto las cookies técnicas necesarias para el funcionamiento de la web');
t('PREFER_REGISTER_PASSWORDLESS','¿Prefieres registrarte sin contraseña?','Prefer to register passwordless?');
t('RESET_PASSWORD_REQUEST_FAILED','No se ha podido restablecer la contraseña');
t('REMINDER_PASSWORD_REQUEST_SENT','Solicitud de recordatorio de contraseña enviada. Consulte su correo electrónico','Reminder password request sent. Check your email');
t('FAST_REGISTRATION_PASSWORDLESS','Registro rápido (sin contraseña)','Fast registration (passwordless)');
t('BASIC_INFORMATION_DATA_PROTECTION','Información básica sobre protección de datos.','Basic information about data protection.');
t('FAST_REGISTRATION_PASSWORDLESS_INFO','Solo necesitas tu email. No tendrás que recordar ninguna contraseña','You only need your email. You will not have to remember any password');



/*
if($_NOT_FOUND){

    if($_ACL->HasPermission('edit_items')){

    }else{
        //CFG::$vars['widget']['404']=true;
        ajax_load_url('404'); 

    }

}            
*/