<?php


if(!$_SESSION['valid_user']){

}else if($_ARGS[1]=='new'){

}else if($_ARGS[1]=='install'){
  
}else{

    HTML::css(SCRIPT_DIR_MODULE.'/forms.css?ver=2.1.0');
    HTML::css(SCRIPT_DIR_MODULE.'/style.css?ver=2.6.0');

    /**********
    HTML::js('https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ace.js');
    HTML::js('https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ext-language_tools.js');
    HTML::js('https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js');


    HTML::js('https://cdnjs.cloudflare.com/ajax/libs/d3/7.1.1/d3.min.js');
    //HTML::js(SCRIPT_DIR_MODULES.'/stats/Chart.min.js?ver=1.1');

    HTML::js('https://unpkg.com/htmx.org@2.0.0/dist/htmx.min.js');
    HTML::js('https://unpkg.com/hyperscript.org@0.9.12');
    ****/
    HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.0.0');


    //HTML::css('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css');
    //HTML::css(SCRIPT_DIR_JS.'/image_editor/image_editor.css?ver=1.0.4');


    //HTML::css(SCRIPT_DIR_JS.'/wysiwyg/editor.css?ver=1.0.1');

    //HTML::css(SCRIPT_DIR_JS.'<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>-->
    //HTML::js('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js');
    //HTML::js(SCRIPT_DIR_JS.'/image_editor/image_editor.js?ver=1.0.4');


}