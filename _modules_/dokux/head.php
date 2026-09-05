<?php

HTML::css(SCRIPT_DIR_MODULE.'/style.css?ver=1.3.6');
HTML::css(SCRIPT_DIR_LIB.'/animate/animate-custom-all.css?ver=1.3.2');
HTML::js(SCRIPT_DIR_LIB.'/pdf.js/pdf.js','defer');
HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.0.3','defer');
if($doku_oper) { 
    HTML::css(SCRIPT_DIR_MODULE.'/upload.css');
    HTML::js(SCRIPT_DIR_LIB.'/jquery/jquery.filedrop.js?ver=1.0.2','defer');
    HTML::js(SCRIPT_DIR_MODULE.'/upload.js?ver=1.0.8','defer');
}
//HTML::ss('//mozilla.github.io/pdf.js/build/pdf.js');