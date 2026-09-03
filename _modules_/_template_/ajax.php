<?php
    

    /**

    // Example receiving data
    // $_ARGS contanins GET and POST arrays merged

    $_ARGS['text'] =  Crypt::crypt2str($_ARGS['text'],$_ARGS['key']);

    $result = array();
    $result['error'] = 0;
    $result['msg']   = 'datos recibidos: '.print_r($_ARGS,true);      

    echo json_encode($result);

    **/

    sleep(2);  // Ponemos un retardo porque como esto va a to ostia no da
               // tiempo a ver el spinner de carga y parece que no hace nada. 
    

    $result = array();
    
    $result['error'] = 0;

    if($_ARGS){

        $_ARGS['text'] =  Crypt::crypt2str($_ARGS['text'],$_ARGS['key']);
        $result['msg']   = 'datos recibidos: '.print_r($_ARGS,true);      
        /*
        $result['key']   = $_ARGS['key'];
        $result['text']  = $_ARGS['text'];
        */
    }


    echo json_encode($result);

    
    // Uncommet to use _classes_/scaffold classes
    // include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
