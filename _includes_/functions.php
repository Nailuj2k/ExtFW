<?php

//   [꧁༒•Nailuj•༒꧂] 

function acount($o){
    //if(is_countable($o)) // php >= 7.3 
    if(is_array($o)) return count($o); else return 0;
}

$_JS_WIDGETS = '';

define('TAB_HEADER','<div class="form-tabs" id="ftabs-%s" data-simpletabs><ul>%s</ul>');
define('TAB_TAB_TAB','<li><a href="#ftab-%s">%s</a></li>'); //,true);
//define('TAB_TAB_TAB','<li><a href="#ftab-%s" id="tab-id-%s">%s</a></li>');
define('TAB_TAB_BEGIN','<div id="ftab-%s">');
define('TAB_TAB_END','</div>');
define('TAB_FOOTER','</div>'); //<script type="text/javascript">$(function(){$("#ftabs-%s").tabs();});</script>');

/*
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
*/

function widget($widget,$params=false){
    global $_JS_WIDGETS;
    if (CFG::$vars['widget'][$widget]) {
        $widget_id= $widget.($params?'_'.Str::SanitizeName($params):'');

        $_URL = $widget.'/'.($params?$params.'/':'').'html';

        $_JS_WIDGETS .= 'if(!widget_'.$widget_id.'_loaded){'."\n"
                     /** */
                     .  '    $.get(\''.$_URL.'\',function(data){'."\n"
                // .  '         console.log(\'DATA\',data);'."\n" 
                     .  '        $(\'#widget-'.$widget_id.'\').html(data);'."\n"
                     .  '    }).fail(function(){'."\n"
                     .  '         console.log(\'FAIL\');'."\n" 
                     .  '        $(\'#widget-'.$widget_id.'\').html(\'error\');'."\n"
                     .  '    });'."\n"


                     .  '}'."\n"
                     .  'var widget_'.$widget_id.'_loaded = true;'."\n";

        echo '<div id="widget-'.$widget_id.'"></div>';
    }
}

function ajax_load_url($name){
    $name_id = $name.'-'.time();
    ?>
    <script type="text/javascript">
        $(function(){
            $.get('<?=$name?>/html',function(data){
                $('#<?=$name_id?>').html(data);
            }).fail(function(){
                $('#<?=$name_id?>').html('error');
            });
        });
    </script><div id="<?=$name_id?>"></div>
    <?php 
}

function create_php_file($filename,$contenido){
  if (file_exists($filename)){
    Messages::error("El archivo: <i>$filename</i> ya existe!");
  }else {
    if($hfp = fopen($filename,'w+')){
      $contenido = '<'."?php\n".stripslashes($contenido)."\n";
      fwrite($hfp,$contenido);
      Messages::info("Archivo <i>$filename</i> creado!");
      fclose($hfp);
    }else{ 
      Messages::error("No ha sido posible crear el archivo: <i>$filename</i>!");
    }
  }
}

function check_file($filename,$create_if_not_exists=true) {
    $file_included =  include($filename);
    //  if (!@include(SCRIPT_DIR_MODULE.'/init.php')) Messages::error( sprintf(t('FILE_%s_NOT_EXISTS_OR_ERROR_IN_MODULE_%s'),SCRIPT_DIR_MODULE,MODULE) );
    if (!$file_included) {
        if(file_exists($filename)) {
            Messages::error( "Se ha producido un error al cargar el archivo <i>$filename</i>");
        }else{
            Messages::error( "No existe el archivo <i>$filename</i>");
            if($create_if_not_exists)  create_php_file($filename,"\n");
        }
    }
}

function log_url(){


  global $_ARGS;
  //if (!$_POST) return false;
  //if ($_DIR_THEME == 'ajax') return false;
  //'/debug/history/ajax'
  //if($_ARGS['log']=='false') return false;

  if($_ARGS[0]=='debug' /*&&  OUTPUT == 'ajax'*/) return false;
  //print_r($_ARGS);
   //echo '<h3>LOG_URL'.__LINE__.' - '.count($_SESSION['url_list'])."</h3>\n";

  /////////////////////if (CFG::$vars['debug']||1==1){

   //$_SESSION['last_get'] = $_GET;
   //$_SESSION['last_post'] = $_POST;
 
   if (!isset($_SESSION['url_list'])) $_SESSION['url_list'] = array();
   
    while ( count($_SESSION['url_list']) > 20 ) array_shift( $_SESSION['url_list'] );


    $t = time();
    $f = strftime("%H:%M:%S", $t);
    $u = $_SERVER['REQUEST_URI'];
    if($u=='/' || $u=='/favicon.ico' || strstr($u,'bg.jpg')) return false;
    
    $s = "<a href=\"$u\">$u</a>";
    /*
    $p=false;

         if(OUTPUT=='ajax')  {  $p = 'AJAX';  }
    else if(OUTPUT=='print') {  $p = 'PRINT'; }
    else if(OUTPUT=='raw')   {  $p = 'RAW'; }
    else if(OUTPUT=='pdf')   {  $p = '&nbsp;PDF';   }
    else if(OUTPUT=='xls')   {  $p = '&nbsp;XLS';   }
    else                     {  $p = '&nbsp;GET';   }
    */
    $p = OUTPUT??'--';
    
    if($p){
        $_SESSION['url_list'][$f] = array();
        $_SESSION['url_list'][$f]['fecha'] = $f; 
        $_SESSION['url_list'][$f]['tipo']  = $p; 
        $_SESSION['url_list'][$f]['url']   = "<a href=\"$u\">$s</a>"; 
        $_SESSION['url_list'][$f]['get']   = $_GET;
        if ($_POST) 
        $_SESSION['url_list'][$f]['post']  = $_POST;
    }

    /*
    if($_POST){ //CFG::$vars['site']['debug']['email']){
      
        $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,EMAIL,SUBJECT,MESSAGE) VALUES(\''.'4'.'\',\''.$p.'\',\''.$u.'\',\'<pre>'.print_r($_ARGS,true).'</pre>\')';
        Table::sqlExec($log_sql);

    }
    */
  /////////////////////}
}

/*
function log2mail($var,$name){
    if (CFG::$vars['debug']){
        if($_POST && CFG::$vars['site']['debug']['email']){
            $ip = get_ip();
            $m2 = new Mailer();
            $m2->Subject = CFG::$vars['site']['title'].' - Debug information - '.$ip;
            $m2->body = '<pre>'
                    //. Vars::debug_var($_GETS,'_GET',true)
                    //. Vars::debug_var($_POST,'_POST',true)  
                      . Vars::debug_var($var,$name,true)  
                    //. Vars::debug_var($_SESSION,'_SESSION',true)  

                    //. Vars::debug_var($ip,'IP',true)  
                      . Vars::debug_var($_SERVER["REMOTE_ADDR"],'_SERVER[remote_address]',true)  
                      . Vars::debug_var($_SERVER['REQUEST_URI'],'Uri:',true)  
                      . Vars::debug_var($_SERVER['HTTP_USER_AGENT'],'Navegador:',true)  
                      . Vars::debug_var($_SESSION['lang'],'_SESSION[lang]',true)  
                      . Vars::debug_var($_SESSION['theme'],'_SESSION[theme]',true)  
                      . Vars::debug_var($_SESSION['module'],'_SESSION[module]',true)  
                      . Vars::debug_var($_SESSION['token'],'_SESSION[token]',true)  
                      . Vars::debug_var($_SESSION['valid_user'],'_SESSION[valid_user]',true)  
                      . Vars::debug_var($_SESSION['userid'],'_SESSION[userid]',true)  
                      . Vars::debug_var($_SESSION['userlevel'],'_SESSION[userlevel]',true)  
                      . Vars::debug_var($_SESSION['username'],'_SESSION[username]',true)  
                      . Vars::debug_var($_SESSION['user_fullname'],'_SESSION[user_fullname]',true)  
                      . Vars::debug_var($_SESSION['user_email'],'_SESSION[user_email]',true)  

                      .'</pre>'; 
            $m2->SetFrom('soporte@extralab.net','soporte@extralab.net') ;
            $m2->AddAddress(CFG::$vars['site']['debug']['email'],CFG::$vars['site']['debug']['email']);
            $m2->Send();
        }
    }
}
*/
/*******
function get_ip() {
    $ip_real =              $_SERVER["HTTP_X_FORWARDED_FOR"];
    if (!$ip_real) $ip_real=$_SERVER["HTTP_CLIENT_IP"];
    if (!$ip_real) $ip_real=$_SERVER["REMOTE_ADDR"];
    return $ip_real;
}
*******/
function get_ip() {
    // 1. IP por defecto (la más fiable)
    $ip = $_SERVER['REMOTE_ADDR'];

    // 2. Revisar si hay proxies (solo si confías en ellos)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // X-Forwarded-For puede traer una lista de IPs separadas por coma
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $proxy_ip = trim($ips[0]); // Tomamos la primera de la lista
        
        // Validamos que sea una IP real antes de usarla
        if (filter_var($proxy_ip, FILTER_VALIDATE_IP)) {
            $ip = $proxy_ip;
        }
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        if (filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }

    return $ip;
}

function ip_as_integer() {
    $ip = get_ip();
    return sprintf( "%u", ip2long( $ip ) );
}

function get_country(){
    $ip = get_ip();
    return file_get_contents('https://ipapi.co/'.$ip .'/country_name/');
}

function str_debug($msg) {return '<span style="background-color:yellow;Font-Size : 8px;">'.stripslashes($msg).'</span>';}
function debug($msg)     {  if(!PRODUCTION) echo str_debug($msg);}


function t($s,$c1='',$c5='',$insert=false){
    
    global $strings;

    if(/*$insert||*/$_SESSION['translate']){
        if ( ctype_upper($s) || strpos($s, '_') ||strpos($s, '.') || strpos($s, '%s')  || strpos($s, '{')  || strpos($s, '}')  || strpos($s, '$') ){
        
            $oki = Table::getFieldsValues("SELECT str_id,str_string FROM ".TB_STR." WHERE str_string = '$s'");
            if(!$oki) Table::sqlExec("INSERT INTO  ".TB_STR." (str_string) VALUES ('".$s."')");

            $_id  = Table::getFieldsValues("SELECT str_id FROM ".TB_STR." WHERE str_string = '$s'");
            if($_id) {
                $id  = $_id['str_id'];

                $_cc1 = Table::getFieldsValues("SELECT id_str FROM ".TB_CC." WHERE id_lang=1 AND id_str = $id");
                $_cc5 = Table::getFieldsValues("SELECT id_str FROM ".TB_CC." WHERE id_lang=5 AND id_str = $id");
                    
                if($_cc1) $cc1 = $_cc1['id_str'];
                if($_cc5) $cc5 = $_cc5['id_str'];

                if     ($_id && !$cc1 && $c1!='') Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ($id,1,'".$c1."')");   
                else if($_id && !$cc1 && $c1=='') Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ($id,1,'".str_replace('_',' ',ucwords(strtolower($s)))."')");   
                if     ($_id && !$cc5 && $c5!='') Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ($id,5,'".$c5."')");   
                else if($_id && !$cc5 && $c5=='') Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ($id,5,'".str_replace('_',' ',ucwords(strtolower($s)))."')");   
            }            

            /**
            $r = $strings[$s];
            if($r){
                $oki2 = Table::getFieldsValues("SELECT cc_id,id_str,id_lang,cc_string FROM ".TB_CC." WHERE id_lang = (SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$_SESSION['lang']."') AND id_str = (SELECT str_id FROM  ".TB_STR." WHERE str_string = '$s')");
                if(!$oki2) Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ((SELECT str_id FROM  ".TB_STR." WHERE str_string = '$s'),(SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$_SESSION['lang']."'),'".$strings[$s]."')");   
            }
            */
        }
    }
    if(isset($strings[$s])){
        $r = $strings[$s];
        return $r ? $r : $s;
    }else{
        return $c1 ? $c1 : $s;
    }

}


// https://dev.to/wallacemaxters/simply-way-to-write-php-output-in-javascript-console-log-3lbc
// console_log(['name' => 'jander Klander']);

function console_log($data) {
    printf('<script>console.log(%s);</script>', json_encode($data));
}

function get_page($name){
    global $_ACL;
    $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES." WHERE item_name= '{$name}'");
    $field_text_name = $_SESSION['lang']=='es'?'item_text':'item_text_'.$_SESSION['lang'];
    $field_title_name = $_SESSION['lang']=='es'?'item_title':'item_title_'.$_SESSION['lang'];
  //Table::init();
    if(count($rows)==1){

        $item_id   = $rows[0]['item_id'];
        $item_title= $rows[0][$field_title_name];
        $item_text = $rows[0][$field_text_name];
        $item_code = $rows[0]['item_code'];
        if($_SESSION['lang']!='es' && !$item_text) $item_text = $rows[0]['item_text'];
    }

    $edit_link = $_ACL->HasPermission('edit_items')?'<a style="position:absolute;right:10px;top:10px;color:white;" href="'.$name.'" class="btn"><i class="fa fa-edit"></i> Editar</a>':'';

    return $item_text.$edit_link.$item_code;

}

function Jeader($url='index.php',$timeout=1000) {
   ?>
   <script type="text/javascript">
       setTimeout(function(){
       location.href='<?=$url?>';
       },<?=$timeout?>);
   </script>
   <?php
   Return true;
}



///////////////////////////////////////////////////////////////////////////////
//
//
//  Función que envia un archivo a un servidor ftp. los nombres de los
//  parametros son suficientemente explicativos.
//  Algunos parametros pueden ser omitidos (ver el código fuente)
//
function upload_file($ftp_server,$ftp_user_name,$ftp_user_pass,$source_file,$destination_file,$remote_dir,$verbose=false) {
  $messages = array();
  $messages['error'] =  0;
  $messages['msg'] =  'ok';  
  if($verbose) echo " Uploading $source_file <br>";
  if (!$source_file){
    $messages['error'] =  1; 
    $messages['msg']= "No se ha especificado un nombre de archivo";
  }else if ( Str::end_with(SCRIPT_HOST)==Str::end_with(CFG::$vars['repo']['url']) ){
      
    // $s = Str::end_with(SCRIPT_HOST);
    // $s .= ' == '.Str::end_with(CFG::$vars['repo']['url']);
    $messages['msg'] =  "El host somos nosotros :) [". $s . ']';

  }else{
      if (!$destination_file) $destination_file=$source_file;
      if (!$ftp_server)       $ftp_server='localhost';
      if (!$ftp_user_name)    $ftp_user_name='anonymous';
      if (!$ftp_user_pass)    $ftp_user_pass='yo@mail.com';
      if($verbose) echo "Intentando conectar con <I>$ftp_server</I>.<br>";
      /*****************************************
      $hostip = gethostbyname($ftp_server);
      $conn_id = ftp_connect($hostip);
      ******************************************/
      $conn_id = ftp_connect($ftp_server);
      if (!$conn_id){
         if ($verbose) echo "No se pudo conectar con <I>$ftp_server</I><br>";
         $messages['error'] =  1; 
         $messages['msg']= "No se pudo conectar con $ftp_server";
      } else {
        if ($verbose) echo "Conectado.<br>";
        if ($verbose) echo "Enviando nombre de usuario <I>$ftp_user_name</I> y contraseña.<br>";
        $login_result = @ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
        if ((!$conn_id) || (!$login_result)){
          if ($verbose) echo "No se pudo conectar como <I>$ftp_user_name</I><br>";
          $messages['error'] =  1; 
          $messages['msg']= "No se pudo conectar como $ftp_user_name";
        }else{
          $bad_dir=false;
          if ($verbose) echo "Usuario <I>$ftp_user_name</I> <font color='#00F03D'><b>OK</b></font><br>";
          if ($remote_dir) {
            if ($verbose) echo "Cambiando al directorio <I>$remote_dir</I>..";
            if (!@ftp_chdir($conn_id,$remote_dir)){
              $bad_dir=true;
              if ($verbose) echo "..<font color='red'><b>!</b>Aceso denegado!</b></font><br>";
              $messages['error'] =  1; 
              $messages['msg']= "Aceso denegado!";
            }else{
              if ($verbose)  echo "..<font color='#00F03D'><b>OK</b></font><br>";
            }
            //$actual_dir=ftp_pwd($conn_id);
          }
          if (!$bad_dir){
              if ($verbose)  echo "Enviando archivo <I>$source_file</I><br>";
              $upload = @ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
              if (!$upload)  {
                  if ($verbose)  echo "..<font color='red'><b>!</b>Error subiendo archivo<b>!</b></font><br>";
                  $messages['error'] =  1; 
                  $messages['msg']= "Error subiendo archivo ".$destination_file;
              } else  {
                  if ($verbose)  echo "..guardado como $destination_file<font color='#00F03D'><b>OK</b></font><br>";
              }
          }
          if (function_exists("ftp_close")) ftp_close($conn_id); else ftp_quit($conn_id);
        }
      }
  }
  if ($verbose) Vars::debug_var($messages);
  return $messages;
}
//
//
///////////////////////////////////////////////////////////////////////////////




function message_mail($subject,$message,$frommail = false,$tomail = false){

    if(!$frommail) $frommail = CFG::$vars['site']['from_email'];
    if(!$tomail)   return false; 

    $m = new Mailer();
    $m->SetFrom( $frommail, $frommail);
    $m->AddAddress( $tomail, $tomail);

    $m->Subject = $subject;
    $m->body = $message;
    return $m->Send();

}