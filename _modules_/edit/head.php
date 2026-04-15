<?php

    HTML::css(SCRIPT_DIR_MODULE.'/style.css?ver=2.2.0');
    HTML::css(SCRIPT_DIR_LIB.'/animate/animate-custom.css');
  
    if(defined('CDN_URL') && CDN_URL) {
        HTMl::js(CDN_URL.'/_lib_/monaco-editor/min/vs/loader.js','defer');  
        HTML::js(CDN_URL.'/_lib_/aadsm/jsmediatags.min.js');
    }else if(USE_CDN==true)            { 
        HTMl::js('https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js','defer'); 
        HTML::js('https://cdnjs.cloudflare.com/ajax/libs/jsmediatags/3.9.5/jsmediatags.min.js','integrity="sha512-YsR46MmyChktsyMMou+Bs74oCa/CDdwft7rJ5wlnmDzMj1mzqncsfJamEEf99Nk7IB0JpTMo5hS8rxB49FUktQ==" crossorigin="anonymous" referrerpolicy="no-referrer"');
    }else                              { 
        HTMl::js(SCRIPT_DIR_LIB.'/monaco-editor/min/vs/loader.js','defer'); 
        HTML::js(SCRIPT_DIR_LIB.'/aadsm/jsmediatags.min.js');
    }

    HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.0.0','defer');  
    HTML::js(SCRIPT_DIR_MODULE.'/dialog.js?ver=1.0.0');  
    HTML::js(SCRIPT_DIR_MODULE.'/menu.js?ver=1.0.0','defer');  
    

