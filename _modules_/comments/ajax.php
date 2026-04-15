<?php
    

    if($_ARGS['test']){

        //sleep(2);  // No hay prisa :)
        

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
        
    }else{
    
        // Uncommet to use _classes_/scaffold classes
        include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
    }