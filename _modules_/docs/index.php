<?php    


    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if($_ARGS[1]=='install'){

        include(SCRIPT_DIR_MODULE.'/install.php');

    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');

    }else if (OUTPUT=='file'){
    
        include(SCRIPT_DIR_MODULE.'/file.php');       

    }else if (OUTPUT=='raw'){
    
        include(SCRIPT_DIR_MODULE.'/raw.php');       
        
    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');

    }
