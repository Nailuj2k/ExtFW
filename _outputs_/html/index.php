<?php
    
    Header('Content-type: text/html');
    Header('charset: utf-8'); 
 
    // include(SCRIPT_DIR_MODULE.'/index.php');


    ob_start();
    include(SCRIPT_DIR_MODULE.'/index.php');
    $html = ob_get_clean();

    $html = str_replace(['./',SCRIPT_DIR_MEDIA,'\n','\"'],['','/'.SCRIPT_DIR_MEDIA,'','"'], $html);

    echo $html;