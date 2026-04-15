<?php

    Header('Content-type: text/text');
    
    //include(SCRIPT_DIR_CLASSES.'/html2text.class.php');

    ob_start();
    include(SCRIPT_DIR_MODULE.'/index.php');
    $html = ob_get_clean();

  //  $html = str_replace(['\n','\"'],['','"'], $html);
    $html = str_replace(['.big_','./',SCRIPT_DIR_MEDIA,'\n','\"'],['','','/'.SCRIPT_DIR_MEDIA,'','"'], $html);

       
    echo (new HtmlToText())->parseString($html,true); //add second true param to convert images with alt to ascii

    