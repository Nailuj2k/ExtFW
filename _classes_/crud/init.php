<?php

    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/consts.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/field.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/table.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.fieldset.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.textarea.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.html.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.hidden.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.input.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.number.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.button.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.checkbox.class.php');
    include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.select.class.php');

    Table::connect();
    
    $session_lang = Vars::getVar($_SESSION['lang'],$cfg['default_lang']);
                                                                      // Exists COALESCE in SQLite ??
    $TEXT_column = $session_lang=='es'/*$cfg['default_lang']*/ ? 'TEXT' :  "COALESCE(NULLIF(TEXT_".$session_lang.",''), TEXT)";   //FIX
    $rows = Table::sqlQuery("SELECT K, V FROM CFG_CFG WHERE ACTIVE=1 UNION ALL SELECT NAME AS K, ".$TEXT_column." AS V FROM CFG_TPL WHERE ACTIVE=1" );  

    function _v($v){
             if($v=='true')  return true;
        else if($v=='false') return false;
        else if($v=='\'0\'') return '0';
        else return $v;
    }
    
    foreach ($rows as $k => $v) {
        $ak = explode('.',$v['K']);
        $n=count($ak);
        //if( !$v['V'] || $v['V']==''|| $v['V']=='false') $v['V']=false;
        //if( $v['V']==0  || $v['V'] =='0') $v['V']='0';
        if     ($n==5) $cfg[$ak[0]][$ak[1]][$ak[2]][$ak[3]][$ak[4]] = _v($v['V']);
        else if($n==4) $cfg[$ak[0]][$ak[1]][$ak[2]][$ak[3]] = _v($v['V']);
        else if($n==3) $cfg[$ak[0]][$ak[1]][$ak[2]] = _v($v['V']);
        else if($n==2) $cfg[$ak[0]][$ak[1]] = _v($v['V']);
        else if($n==1) $cfg[$ak[0]] = _v($v['V']);
        if(isset($cfg[$ak[0]])) 
            CFG::set($ak[0],$cfg[$ak[0]]);
        else
            CFG::set($ak[0],'undefined');

    }


    /*
    function _cfg_set_nested(&$cfg, $path, $value){
        if(!is_array($path) || !count($path)) return;
        $ref =& $cfg;
        $last = count($path) - 1;

        for($i=0; $i<$last; $i++){
            $k = $path[$i];
            if(!isset($ref[$k])){
                $ref[$k] = array();
            } else if(!is_array($ref[$k])){
                $ref[$k] = array('__value' => $ref[$k]);
            }
            $ref =& $ref[$k];
        }

        $ref[$path[$last]] = $value;
    }
    
    foreach ($rows as $k => $v) {
        $ak = explode('.',$v['K']);
        $n=count($ak);
        //if( !$v['V'] || $v['V']==''|| $v['V']=='false') $v['V']=false;
        //if( $v['V']==0  || $v['V'] =='0') $v['V']='0';
        if($n>=1) _cfg_set_nested($cfg, $ak, _v($v['V']));
        if(isset($cfg[$ak[0]])) 
            CFG::set($ak[0],$cfg[$ak[0]]);
        else
            CFG::set($ak[0],'undefined');

    }

    */

    define('CRUD_CSS_STYLE',vars::getVar(CFG::$vars['options']['forms_css_style'],'basic'));

    $rows = false;