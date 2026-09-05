<?php

  
  $db_engine = 'scaffold';


  define('READ','ISREAD');
  define('NVL','IFNULL');

  function Usuario() {
    global $_ACL; 
    return true; 
  }

  function Administrador() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Administradores')  || $_ACL->hasPermission('alerts_edit') ); 
  }
  
  function Root() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Root') ); 
  }

