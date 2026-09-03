<?php

// SCRIPT_DIR_MODULE contains path to module directory
//$imagespath = SCRIPT_DIR_MODULE.'/images/';

   // Ejemplo de cambio de theme sólo para este módulo
   // $_ARGS['iframe'] = true;

//include SCRIPT_DIR_MODULES.'/login/cfg.php'; 
//print_r(CFG::$vars['captcha']);

/*
  if ($_ARGS[1]   == 'auth' )  {
      define('OUTPUT','html');
  }
  */

$after_init = true;

 //if ($_ARGS[1] && $_ARGS[1]!='changepassword' && $_ARGS[1]!='lostpassword' && $_ARGS[1]!='register'/*  =='profile'*/)  {
 if ($_ARGS[1] && !in_array($_ARGS[1],array_merge(['changepassword','lostpassword','register'],CFG::$vars['langs'])) ) {

  $db_engine = 'scaffold';
 }

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

  function Cliente() {
    global $_ACL; 
    return ( $_SESSION['userid']>1);
  }

  function Usuario() {
    global $_ACL; 
    return ( $_SESSION['userid']>1);
  }  


  Breadcrumb::$replace['login']   = array(t('LOGIN'),'login');
  Breadcrumb::$replace['profile'] = array(t('MY_ACCOUNT'),'profile');

  CFG::$vars['login']['password']['strength'] = 2;

  Invitation::ensureTable();
