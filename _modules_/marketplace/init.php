<?php

    $db_engine = 'scaffold';
    
    function Usuario() {
        global $_ACL; 
        return true; 
    }

    function Administrador() {
        global $_ACL; 
        return ( $_ACL->userHasRoleName('Administradores')  || $_ACL->hasPermission('mkp_edit') ); 
    }
    
    function Root() {
        global $_ACL; 
        return ( $_ACL->userHasRoleName('Root') ); 
    }
