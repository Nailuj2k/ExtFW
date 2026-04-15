<?php
    

    /**

    $_ARGS['text'] =  Crypt::crypt2str($_ARGS['text'],$_ARGS['key']);

    $result = array();
    
    $result['error'] = 0;
    $result['msg']   = 'datos recibidos: '.print_r($_ARGS,true);      

    sleep(2);  // No hay prisa :)
    
    echo json_encode($result);

    **/

    // include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
