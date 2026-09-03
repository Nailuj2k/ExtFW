<?php    


    if (OUTPUT=='ajax'){  

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if ($_ARGS[1]=='test'){
     
        include(SCRIPT_DIR_MODULE.'/test.php');

    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');
        
    } else if($_SESSION['valid_user']) {

        $_SESSION['backurl']=false;
        if ( $_ACL->hasPermission('site_edit') ) {

            include(SCRIPT_DIR_MODULE.'/run.php');

        }else{

            $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'No tiene permisos para usar esta aplicación';
            ?>
            <div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;"><?=$message_error?></p></div>
            <?php

        }

    } else {

        if(!$_SESSION['backurl']) $_SESSION['backurl']=MODULE.($_ARGS[1]?'/'.$_ARGS[1]:'');
        $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'Debe iniciar sesión con su email y su contraseña y podrá acceder a este contenido si dispone de los permisos necesarios.';
        ?>
        <div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;"><?=$message_error?></p></div>
        <div style="text-align:center;margin:60px 0 100px 0;"><a class="btn btn-large btn-primary" href="<?=Vars::mkUrl('login')?>"> &nbsp; login <i class="fa fa-chevron-right fa-inverse"></i> &nbsp; </a></div>
        <?php 

    }