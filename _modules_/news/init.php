<?php

  $db_engine = 'scaffold';
  $after_init = true;

  define('READ','ISREAD'); 
  
  define('TB_PREFIX',MODULE=='news'?'NOT':(MODULE=='blog'?'BLG':MODULE) );

  define('TB_NAME'  ,strtoupper(MODULE));
  define('TB_TABLE',TB_PREFIX.'_'.TB_NAME);

  function User() {
    global $_ACL; 
    return ( $_SESSION['userid']>99 ); 
  }

  function Administrador() {
    global $_ACL; 
//    return ( $_ACL->userHasRoleName('Administradores') ); 
    return ( $_ACL->hasPermission(MODULE.'_admin') ); 
  }

  function Root() {
    global $_ACL; 
    return ( $_ACL->userHasRoleName('Root') ); 
  }
  
  $logo_img = SCRIPT_DIR_MEDIA.'/images/logo_b.png';


    if($_ARGS[1]=='tag'){

        //if($_ARGS[2]){
        if($_ARGS[2] && (!in_array($_ARGS[2],array_merge(['view','theme','lang','output','debug'],CFG::$vars['outputs'],CFG::$vars['langs'])))){          
            $_SESSION['_CACHE'][TB_TABLE]['filterstring'] = TB_PREFIX.'_ID IN (SELECT '.TB_NAME.'_ID FROM '.TB_TABLE.'_TAGS WHERE TAG_ID = (SELECT TAG_ID FROM '.TB_PREFIX.'_TAGS WHERE NAME =\''.$_ARGS[2].'\'))';
        }else{
            $_SESSION['_CACHE'][TB_TABLE]['filterstring'] = false;
        }

        $_ARGS[1]=false;
        $_ARGS[2]=false;


    }else{

       //$_SESSION['_CACHE'][TB_TABLE]['filterstring'] = false;

    }

    define('MODULE_ID', MODULE == 'news' ? 2 : ( MODULE == 'blog' ? 3 : 0 )); // 1 = PAGES, 2 = NEWS, 3 = BLOG, etc.

    Comments::$module         = MODULE_ID;                       
    Comments::$url            = MODULE.'/ajax';
    Comments::$admin          = $_ACL->userHasRoleName('Administradores')  ?? false;
    Comments::$hide_childs    = true;
    Comments::$max_level      = 4;
    Comments::$theme          = 'theme-default'; // theme-default, theme-medium, theme-dark, theme-liquid-glass
    Comments::$anon           = true;
 // Comments::$wysiwyg        = true;
 // Comments::$hl_style       = 'medium'; //'base16/material'; // gradient-light hybrid ir-black kimbie-light rainbow pojoaque far atom-one-dark atom-one-light foundation

    Rating::$url            = MODULE.'/ajax';
    Rating::$module         = MODULE_ID;                        
    //Rating::$shape          = 'heart'; // thumbs star heart circle
    //Rating::$theme          = 'test';
    Rating::$size           = [25,25];