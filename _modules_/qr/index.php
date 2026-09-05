<?php    


    // https://[HOST_URL]]/control_panel/ajax/update/instal/custom_module/qr

    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if($_ARGS[1]=='install'){

        include(SCRIPT_DIR_MODULE.'/install.php');

    }else if($_ARGS[1]=='zip'){

       // include(SCRIPT_DIR_MODULE.'/install.php');

    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');
        
    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');

    }




