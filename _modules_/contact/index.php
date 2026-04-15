<?php    


    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');


    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');
        
    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');
        //include(SCRIPT_DIR_MODULES.'/snow/index.php');

    }
