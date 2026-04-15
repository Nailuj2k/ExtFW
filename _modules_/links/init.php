<?php

  
  $db_engine = 'scaffold';


  define('READ','ISREAD');
  define('NVL','IFNULL');

  function Administrador() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Administradores') ); 
  }
  
  function Root() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Root') ); 
  }


