<?php

    // use _classes_/scaffold classes (Table, Field, Form, etc.)
    $db_engine = 'scaffold';
    
    // Nombres de roles de acceso al módulo ACL (parametrizables)
    if (!defined('ACL_ACCESS_ROLE_ADMIN')) define('ACL_ACCESS_ROLE_ADMIN', 'Area_Admin');
    if (!defined('ACL_ACCESS_ROLE_USER'))  define('ACL_ACCESS_ROLE_USER',  'Area_User');
    
    function Administrador() {
        global $_ACL; 
        return ( $_ACL->userHasRoleName('Administradores') ); 
    }

    function Root() {
        global $_ACL; 
        return ( $_ACL->userHasRoleName('Root') ); 
    }
