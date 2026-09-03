<?php    


    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');
        
    }else if (OUTPUT=='html'){
    
        include(SCRIPT_DIR_MODULE.'/html.php');
        
    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');

    }
