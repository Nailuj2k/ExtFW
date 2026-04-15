<?php 

    /*
    include(SCRIPT_DIR_CLASSES.'/scaffold/field.class.php');
    include(SCRIPT_DIR_CLASSES.'/scaffold/form.class.php');
    include(SCRIPT_DIR_CLASSES.'/scaffold/table.class.php');
    //include(SCRIPT_DIR_CLASSES.'/scaffold/table.oracle.class.php');
    include(SCRIPT_DIR_CLASSES.'/scaffold/table.'.CFG::$vars['db']['type'].'.class.php');
    include(SCRIPT_DIR_CLASSES.'/paginator.class.php');
    //include(SCRIPT_DIR_CLASSES.'/memvar.class.php');
    //include(SCRIPT_DIR_CLASSES.'/exceptions/IException.php');
    //include(SCRIPT_DIR_CLASSES.'/exceptions/CustomException.php');
    */
    if(CFG::$vars['db']['type']=='mysql') 
       $t = New TableMysql();
    else if(CFG::$vars['db']['type']=='sqlite')
       $t = New TableSqlite();
    //else if(CFG::$vars['db']['type']=='oracle') 
    //   $t = New TableOracle();

    $session_lang = Vars::getVar($_SESSION['lang'],$cfg['default_lang']);

    $TEXT_column = $session_lang=='es'/*$cfg['default_lang']*/ ? 'TEXT' :  "COALESCE(NULLIF(TEXT_".$session_lang.",''), TEXT)";   //FIX
    $rows = Table::sqlQuery("SELECT K, V FROM CFG_CFG WHERE ACTIVE=1 UNION ALL SELECT NAME AS K, ".$TEXT_column." AS V FROM CFG_TPL WHERE ACTIVE=1" );  
    //  $rows = $t->query2array("select K, V from CFG_CFG union all select NAME AS K, ".$TEXT_column." AS V from CFG_TPL");

    
    function _v($v){
             if($v=='true')  return true;
        else if($v=='false') return false;
        else if($v=='\'0\'') return '0';
        else return $v;
    }

    /*
    function set_cfg_option_from_array($ak,$val){
        global $cfg;
        if(!$ak) return;

        $ref =& $cfg;
        $lastIndex = count($ak) - 1;

        foreach ($ak as $index => $key) {
            if ($index === $lastIndex) {
                $ref[$key] = $val;
            } else {
                if (!isset($ref[$key]) || !is_array($ref[$key])) $ref[$key] = array();
                $ref =& $ref[$key];
            }
        }

        if(isset($cfg[$ak[0]]))  CFG::set($ak[0], $cfg[$ak[0]]);
                           else  CFG::set($ak[0], 'undefined');
    }
    */
    foreach ($rows as $k => $v) {
        $ak = explode('.',$v['K']);
        $n=count($ak);
          
        //FIX when $ak is like 'key1.key2.key3' get an error: undefined array key: 'key1'
        // set_cfg_option_from_array( $ak , _v($v['V']) );

        //FIX scalar value error 
        if     ($n==5) $cfg[$ak[0]][$ak[1]][$ak[2]][$ak[3]][$ak[4]] = _v($v['V']);
        else if($n==4) $cfg[$ak[0]][$ak[1]][$ak[2]][$ak[3]] = _v($v['V']);
        else if($n==3) $cfg[$ak[0]][$ak[1]][$ak[2]] = _v($v['V']);
        else if($n==2) $cfg[$ak[0]][$ak[1]] = _v($v['V']);
        else if($n==1) $cfg[$ak[0]] = _v($v['V']);
        
        if(isset($cfg[$ak[0]])) CFG::set($ak[0],$cfg[$ak[0]]);
                           else CFG::set($ak[0],'undefined');   
    }

    $rows = false;

    if(!OUTPUT){
        HTML::css(SCRIPT_DIR_CLASSES.'/scaffold/themes/'.Table::$theme.'/style.css?ver=2.1.0');
        HTML::css(SCRIPT_DIR_CLASSES.'/scaffold/themes/'.Table::$theme.'/style.form.css?ver=1.8.8');
        HTML::css(SCRIPT_DIR_CLASSES.'/scaffold/themes/'.Table::$theme.'/style.paginator.css?ver=1.6.6');
        HTML::css(SCRIPT_DIR_LIB.'/animate/animate-custom.css');
        HTML::css(SCRIPT_DIR_LIB.'/dropzone/dropzone.css');                  //HTML::css('https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css');
        HTML::css(SCRIPT_DIR_LIB.'/dropzone/dropzone.custom.css');           //Override some dropzone css
        HTML::css(SCRIPT_DIR_LIB.'/cropper.js/cropper.min.css');             //HTML::css('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css');
        HTML::css(SCRIPT_DIR_JS.'/image_editor/image_editor.css?ver=1.1.2');

        HTML::js(SCRIPT_DIR_LIB.'/cropper.js/cropper.min.js');              //HTML::js('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js');
        HTML::js(SCRIPT_DIR_LIB.'/dropzone/dropzone-min.js');               //HTML::js('https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js');
        HTML::js(SCRIPT_DIR_JS.'/image_editor/image_editor.js?ver=1.1.2');


    }



  
