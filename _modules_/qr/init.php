<?php

  $after_init = true;

  $db_engine = 'scaffold';


  define('READ','ISREAD');
  define('NVL','IFNULL');

  function Usuario() {
    global $_ACL; 
    return true; //( $_ACL->userHasRoleName('A8_ESP_Usuarios') ); 
  }

  function Administrador() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Administradores')  || $_ACL->hasPermission('qrcodes_edit') ); 
  }
  
  function Root() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Root') ); 
  }

