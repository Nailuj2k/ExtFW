<?php    

        
    if($_SESSION['valid_user']) {

        $_SESSION['backurl']=false;


        if 
        (
                $_ACL->userHasRoleName(defined('ACL_ACCESS_ROLE_ADMIN') ? ACL_ACCESS_ROLE_ADMIN : 'Area_Admin')
             || $_ACL->userHasRoleName(defined('ACL_ACCESS_ROLE_USER')  ? ACL_ACCESS_ROLE_USER  : 'Area_User')  
        )
        {

            if (OUTPUT=='ajax'){

               include(SCRIPT_DIR_MODULE.'/ajax.php');
   
            }else if (OUTPUT=='html'){

               include(SCRIPT_DIR_MODULE.'/html.php');
   
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
