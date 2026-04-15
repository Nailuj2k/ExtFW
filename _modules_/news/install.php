<div class="inner">
<h1>Instalación módulo <?=MODULE?></h1>


<?php

    if ($_ACL->userHasRoleName('Administradores')) {

        $_ACL->addPermission(MODULE.'_admin',t(MODULE).' administrar',true);
        $_ACL->addRolePerm('Administradores',MODULE.'_admin',true);        //FIX rename perm noticias_Admin to MODULE_admin

        if(file_exists('media/'.TB_NAME.'/images')){
           if(rename( 'media/'.TB_NAME.'/images', 'media/'.TB_NAME.'/files'))   echo "Successfully Renamed News files dir" ;
                                                            else  echo "Error al cambiar el nombre a 'media/".TB_NAME."/images'. Cámbielo a 'media/".TB_NAME."/files'." ;
        }else{
           echo "Comprobando carpeta 'media/'.TB_NAME.'/images .... OK'";
        }

        
        if (CFG::$vars['db']['type']=='sqlite') {
        
            Table::sqlExec('CREATE UNIQUE INDEX IF NOT EXISTS unique_user_post ON POST_RATINGS(user_id, post_id, module_id)');
            /*

            CREATE UNIQUE INDEX unique_user_post
            ON POST_RATINGS(user_id, post_id, module_id);

            -- O al crear la tabla:
            CREATE TABLE POST_RATING (
                ...,
                UNIQUE(user_id, post_id, module_id)
            );


            SELECT sql FROM sqlite_master
            WHERE type = 'table'
            AND name = 'POST_RATINGS'         

            SELECT name, sql FROM sqlite_master
            WHERE type = 'index'
            AND name = 'unique_user_post';

            CREATE TABLE POST_RATINGS(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module_id INTEGER,
            post_id INTEGER,
            user_id INTEGER,
            rating INTEGER,
            ip_address VARCHAR(45),
            created_at DATETIME.
            UNIQUE(user_id, post_id, module_id))

            */

        }else{

            Table::sqlExec('ALTER TABLE POST_RATINGS ADD UNIQUE KEY unique_user_post (user_id, post_id, module_id)');

        }        












    }else{

        ?><p>Acceso denegado.<br /> ¡Prueba otra vez!</p><?php

    }
    
    // rename media/'.TB_NAME.'/images TO media/'.TB_NAME.'/files
    
?>
</div>