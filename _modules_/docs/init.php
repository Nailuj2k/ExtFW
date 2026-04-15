<?php


    $db_engine = 'scaffold';


    function Root(){
        global $_ACL; 
        return $_ACL->userHasRoleName('Root');
    }

    function Administrador(){
        global $_ACL; 
        return $_ACL->hasPermission('files_edit');
       //return $_ACL->hasPermission('projects_edit');
       // return $_ACL->userHasRoleName('Administradores');
    }

    function Usuario() {
        return ( $_SESSION['userid']>1);
    }

