<div class="inner">
<h1>Instalación módulo <?=MODULE?></h1>


<?php

    if ($_ACL->userHasRoleName('Administradores')) {

        $_ACL->addPermission('files_edit','Adjuntar archivos',true);
        $_ACL->addRolePerm('Administradores','files_edit',true);


        if (file_exists(SCRIPT_DIR_MODULE.'/TABLE_CLI_FILES.php')) {

            //rename(SCRIPT_DIR_MODULE.'/TABLE_CLI_FILES.php',     SCRIPT_DIR_MODULE.'/TABLE_DOC_FILES.php');
            //rename(SCRIPT_DIR_MODULE.'/TABLE_CLI_FILES_TAGS.php',SCRIPT_DIR_MODULE.'/TABLE_DOC_FILES_TAGS.php');
            //rename(SCRIPT_DIR_MODULE.'/TABLE_CLI_TAGS.php',      SCRIPT_DIR_MODULE.'/TABLE_DOC_TAGS.php');
            /*
            unlink(SCRIPT_DIR_MODULE.'/TABLE_CLI_FILES.php');
            unlink(SCRIPT_DIR_MODULE.'/TABLE_CLI_FILES_TAGS.php');
            unlink(SCRIPT_DIR_MODULE.'/TABLE_CLI_TAGS.php');

            Table::sqlExec('DROP TABLE DOC_FILES');
            Table::sqlExec('DROP TABLE DOC_FILES_TAGS');
            //Table::sqlExec('DROP TABLE DOC_TAGS');
            
            Table::sqlExec('RENAME TABLE CLI_FILES TO DOC_FILES');
            Table::sqlExec('RENAME TABLE CLI_FILES_TAGS TO DOC_FILES_TAGS');
            Table::sqlExec('CREATE TABLE DOC_TAGS AS SELECT * FROM CLI_TAGS');
            */
        }


    }else{

        ?><p>Acceso denegado.<br /> ¡Prueba otra vez!</p><?php

    }
?>
</div>