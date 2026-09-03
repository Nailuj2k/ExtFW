<?php    

    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');
        
    }else if (OUTPUT=='api'){
    
       // include(SCRIPT_DIR_MODULE.'/api.php');
    } else if (OUTPUT == 'json') {
    
        include(SCRIPT_DIR_MODULE . '/json.php');
            
    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');

    }



    /*

    // Advanced mode with access control based in role or permission names

    if($_SESSION['valid_user']) {

        $_SESSION['backurl']=false;

        if 
        (
                $_ACL->userHasRoleName('example_role_name') 
             || $_ACL->hasPermission('example_permission_name') 
        )
        {

            if (OUTPUT=='ajax'){

               include(SCRIPT_DIR_MODULE.'/ajax.php');

            }else if (OUTPUT=='csv'){
   
               include(SCRIPT_DIR_MODULE.'/csv.php');

            }else if (OUTPUT=='html'){
   
                include(SCRIPT_DIR_MODULE.'/html.php');

            }else if (OUTPUT=='raw'){
   
                include(SCRIPT_DIR_MODULE.'/raw.php');
     
            }else if (OUTPUT=='pdf'){
   
               include(SCRIPT_DIR_MODULE.'/pdf.php');
   
            }else{

               include(SCRIPT_DIR_MODULE.'/run.php');

            }

        } else {

           $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'No tiene permisos para usar esta aplicación';
           echo '<div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;">'.$message_error.'</p></div>';

        }

    } else {

           if(!$_SESSION['backurl']) $_SESSION['backurl']=MODULE.($_ARGS[1]?'/'.$_ARGS[1]:'');

           $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'Necesita estar identificado en el sistema para utilizar esta aplicación';
           echo '<div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;">'.$message_error.'</p></div>';
           echo '<div style="text-align:center;margin:60px 0 100px 0;"><a class="btn btn-large btn-primary" style="color:white;" href="login"> &nbsp; login <i class="fa fa-chevron-right fa-inverse"></i> &nbsp; </a></div>';
 
    }    

    */