<?php    

    if(!$doku_user){
        $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'No tiene permisos para usar esta aplicación';
        ?><div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;"><?=$message_error?></p></div><?php
        die();
    }

    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if ($_ARGS[1]=='areas'){
    
        include(SCRIPT_DIR_MODULE.'/test_areas.php');

    }else if ($_ARGS[1]=='db'){
    
        include(SCRIPT_DIR_MODULE.'/test_db.php');

    }else if ($_ARGS[1]=='install'){
    
        include(SCRIPT_DIR_MODULE.'/INSTALL.php');

    }else if ($_ARGS[1]=='cover'){
    
        include(SCRIPT_DIR_MODULE.'/test_cover_mp3.php');  //_selene.php');

    }else if ($_ARGS[1]=='test'){
 
        include(SCRIPT_DIR_MODULE.'/mp3.php');  //_selene.php');
                        
    } else if (OUTPUT=='pdf') {
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');

    } else if (OUTPUT=='csv') {
    
        include(SCRIPT_DIR_MODULE.'/csv.php');

    }else if (OUTPUT=='raw'){
    
        include(SCRIPT_DIR_MODULE.'/raw.php');
        
    } else if($_SESSION['valid_user']) {

        $_SESSION['backurl']=false;

        if (!file_exists(DOKUX_ROOT_DIR)) {  

                    $message_error = '<b>Carpeta no encontrada</b><br />'.'La carpeta '.DOKUX_ROOT_DIR.' parece que no existe o no está montada. Póngase en contacto con el Servicio de Informática.';
                    echo '<div class="inner"><div class="alert"  style="margin:80px auto;"><p style="margin:20px auto;">'.$message_error.'</p></div></div>';

        }else if($doku_user ) {

            include(SCRIPT_DIR_MODULE.'/run.php');

        }else{

            $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'No tiene permisos para usar esta aplicación';
            ?>
            <div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;"><?=$message_error?></p></div>
            <?php

        }


    } else {

            if(!$_SESSION['backurl']) $_SESSION['backurl'] =  MODULE;

            $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'Necesita estar identificado en el sistema para utilizar esta aplicación';
            ?>
            <div class="alert inner"><p style="margin:80px auto;"><?=$message_error?></p></div>
            <div style="text-align:center;margin:60px 0 100px 0;"><a class="btn btn-large btn-primary" style="color:white;" href="login"> &nbsp; login <i class="fa fa-chevron-right fa-inverse"></i> &nbsp; </a></div>
            <?php
  
    }
