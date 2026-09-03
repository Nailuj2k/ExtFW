<?php


    //HTML::css('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css');
    //HTML::css(SCRIPT_DIR_JS.'/image_editor/image_editor.css');
    //HTML::css(SCRIPT_DIR_JS.'/wysiwyg/editor.css?ver=1.0.1');
    //HTML::css(SCRIPT_DIR_JS.'<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>-->

    if ($_SESSION['valid_user'] ){
        /*
        HTML::css('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css');
        HTML::css(SCRIPT_DIR_JS.'/image_editor/image_editor.css?ver=1.0.4');

        HTML::js('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js');
        HTML::js(SCRIPT_DIR_JS.'/image_editor/image_editor.js?ver=1.0.4');
        */
    }

    HTML::css(SCRIPT_DIR_MODULE.'/style.css?ver=2.0.3');
    
    HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.4.6','defer');
    HTML::js(SCRIPT_DIR_LIB.'/jquery/jquery.pwdMeter.js','defer');
    //    HTML::js(SCRIPT_DIR_JS.'/passwordless.js?ver=1.1.5','defer');

    //ok  HTML::js(SCRIPT_DIR_MODULES.'/login/device_linking.js?ver=1.1.6','defer');

    HTML::js(SCRIPT_DIR_LIB.'/qrcode/qrcode.min.js');                   // QR generation (standalone, sin jQuery)
    HTML::js(SCRIPT_DIR_LIB.'/jsqr/jsqr.min.js','defer');               // QR scanning engine (~127 KB)
    HTML::js(SCRIPT_DIR_LIB.'/jsqr/html5qrcode-compat.js','defer');     // Html5Qrcode wrapper sobre jsQR


    $isPasswordlessDefault =  (CFG::$vars['login']['default_method']=='passwordless' && CFG::$vars['login']['passwordless']['enabled']) || false;

