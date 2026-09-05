<?php

    define('DOKU_FILES_ROWS'     ,      5 ); 
    define('DOKU_FILES_MAX_ROWS' ,     37 ); 
    define('OCR_IMG_EXT'         , '.jpg' ); 

    if( $_SESSION['valid_user']){
                
        $doku_user     = AreasACL::hasPermission(APP_NAME,'doku_view')     || $_ACL->userHasRoleName('Administradores');
        $doku_oper     = AreasACL::hasPermission(APP_NAME,'doku_add')      || $_ACL->userHasRoleName('Administradores');
        $doku_admin    = AreasACL::hasPermission(APP_NAME,'doku_admin')    || $_ACL->userHasRoleName('Administradores');
        $doku_download = AreasACL::hasPermission(APP_NAME,'doku_download') || $_ACL->userHasRoleName('Administradores');

        define('DOKUX_ROOT_DIR', CFG::$vars['modules'][APP_NAME]['root_dir'] 
                               ? CFG::$vars['modules'][APP_NAME]['root_dir'] 
                               : 'media/'.APP_NAME); 

        if($doku_user){

            function checkCreateDir($path){
                if (!file_exists($path)) {  
                    $r = mkdir($path,0755);  
                    if($r){
                        Messages::success( 'Creada la carpeta '.$path);
                    } else {
                        Messages::error( 'No existe la carpeta '.$path.', y no se ha podido crear');
                    }   
                }
            } 

            define('DOKUX_FILES_DIR',DOKUX_ROOT_DIR.'/files'); 
            define('DOKUX_INBOX_DIR',DOKUX_ROOT_DIR.'/'.$_SESSION['username']); 
            define('DOKUX_INBOX_OCR',DOKUX_ROOT_DIR.'/'.$_SESSION['username'].'/ocr'); 
            define('DOKUX_FILES_TMB',DOKUX_ROOT_DIR.'/thumb'); 
            define('DOKUX_FILES_CNZ',DOKUX_ROOT_DIR.'/canalizaciones'); 

            checkCreateDir(DOKUX_ROOT_DIR );
            checkCreateDir(DOKUX_FILES_DIR);
            checkCreateDir(DOKUX_INBOX_DIR);
            checkCreateDir(DOKUX_INBOX_OCR);
            checkCreateDir(DOKUX_FILES_TMB);
            checkCreateDir(DOKUX_FILES_CNZ);

        }    

    }

    Vars::setDefaultSessionVar('DOKU_TABLE_FILES_ROWS'   , DOKU_FILES_ROWS  );
