<?php

  
  $db_engine = 'scaffold';

  define('READ','ISREAD');
  define('NVL','IFNULL');
  
  $log_filename = SCRIPT_DIR_LOG.'/'.LOG::$logfile_prefix . date('Ymd') . LOG::$logfile_extension;


  $_SESSION['PAGE_FILES_ID_PARENT'] = false;

  function Administrador() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Administradores') ); 
  }
  
  function Root() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Root') ); 
//    return ( $_SESSION['userid']<3);  //FIX add superadmin or 'Root' role
  }

  function Cliente() {
    global $_ACL; 
    return ( $_SESSION['userid']>1);
  }

  function Usuario() {
    global $_ACL; 
    return ( $_SESSION['userid']>1);
  }  

  function Operador() {
    global $_ACL; 
    return true; //( $_SESSION['userid']>1);
  }  

