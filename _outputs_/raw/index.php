<?php


//    Header('Expires: 0');
//    Header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
//    Header('Content-type: text/html');
//    Header('charset: utf-8');


      $app_loaded = include(SCRIPT_DIR_MODULE.'/index.php');

      if(!$app_loaded) 
          $message_error = 'No existe la aplicación '.$_DIR_MODULE;