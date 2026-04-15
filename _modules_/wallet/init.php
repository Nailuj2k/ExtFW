<?php





  function Root() {
    global $_ACL;
    return  $_ACL->userHasRoleName('Root') ;
  }

  function Administrador() {
    global $_ACL;
    return $_ACL->userHasRoleName('Administradores');
  }

  
  function Usuario() {
    global $_ACL;
    return $_SESSION['valid_user']===true;
  }


  $after_init = true;



    // Uncommet to use _classes_/scaffold classes
$db_engine = 'scaffold';
    
