<?php

    // Uncommet to use _classes_/scaffold classes
    $db_engine = 'scaffold';
    
    function Root(){
        global $_ACL; 
        return $_ACL->userHasRoleName('Root');
    }

    function Administrador(){
        global $_ACL; 
        return $_ACL->userHasRoleName('Administradores');
    }