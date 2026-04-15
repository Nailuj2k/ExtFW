<?php

   $db_engine = 'scaffold';
  //$db_engine = 'crud';

      $after_init = true;

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

$_SHOW_page = false;
$_NOT_FOUND = false;
$_CREATE_page = false;
$_PAGE_LIST = false;
$editable = false;  
$item_code=false;
$item_code_js=false;
//   define('MONACO_FROM_CDN',true);



    Comments::$module       = 1;                          // 1 = PAGES, 2 = NEWS, etc.
    Comments::$url          = MODULE.'/ajax';
    Comments::$admin        = $_ACL->userHasRoleName('Administradores')  ?? false;
    Comments::$hide_childs  = true;
    Comments::$max_level    = 4;
    Comments::$theme        = 'theme-default'; // theme-default, theme-medium, theme-dark, theme-liquid-glass
    Comments::$anon         = false;
    Comments::$wysiwyg      = false;
  //Comments::$hl_style     = 'medium';

    Rating::$url            = MODULE.'/ajax';
    Rating::$module         = 1;                          // 1 = PAGES, 2 = NEWS, etc.
    Rating::$size           = [25,25];