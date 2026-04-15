<?php

// https://github.com/enygma/expose
// This module require installation with composer

/*
Install Composer:
# curl -s https://getcomposer.org/installer | php
Require Expose as a dependency using Composer:
# php composer.phar require enygma/expose
Install Expose:
# php composer.phar install
*/


    require 'vendor/autoload.php';
    $request = array();
    $request[] = $_GET;
    $request[] = $_POST;
    //$request[] = $_COOKIE;                                                          //   
    //$request[] = array('test'=>'test=%22><script>eval(window.name)</script>');      // test
    /**
    $request = array(
        'POST' => array(
            'test' => 'foo',
            'bar' => array(
                'baz' => 'quux',
                'testing' => '<script>test</script>'
            )
        )
    );
    **/

    $filters = new \Expose\FilterCollection();
    $filters->load();
    $logger = new \Expose\Log\Mondongo();  //instantiate a PSR-3 compatible logger // https://stackoverflow.com/questions/49746063/configuration-of-php-expose-phpids
    $manager = new \Expose\Manager($filters, $logger);
    /*******************************/

    $notify = new \Expose\Notify\Email();                          // Only for debug
    $notify->setToAddress('debud@example.com');      // Send email when detects potentially attacaks
    $notify->setFromAddress('web@example.com');                   // Delete this block when job is done.
    $manager->setNotify($notify);

    /*****************************/
    $manager->run($request,false,true);    
    $impact = $manager->getImpact();
    
  // print_r($impact);
  //  die( 'bb');

    if($impact>28){
      //  die('impact :'.$impact);
      //  $manager->run($request,false,true);

        $reports = $manager->getReports();
        //print_r($reports);
        //echo $manager->export();  // export out the report in the given format ("text" is default)
        $varname = '';

        if ($reports) {
                $security_ajax = (strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
                $phpids_ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
                $phpids_page = $_SERVER['REQUEST_URI'];

                $msg_error = '<pre>';
                $msg_error .= "<strong>Dirección IP</strong>: $phpids_ip\n";
                $msg_error .= "<strong>Request_uri</strong>: $phpids_page\n";
                $msg_error .= "<strong>Impact</strong>: $impact\n";
                $msg_error .= '</pre>';        

                foreach ($reports as $event) {
                    $msg_error .= '<div style="font-size:0.9em;margin:10px 9px 10px 5px;background-color:#efefef;padding:5px;">';
                    if($varname !=$event->getVarName()){
                        $msg_error .= '<strong>Variable</strong>: '.$event->getVarName()."<br />\n";
                        $msg_error .= '<strong>Value</strong>: <pre style="display:block;max-height:200px;overflow:hidden;">'.htmlspecialchars($event->getVarValue()).'</pre>';
                    }
                    $varname = $event->getVarName();
                    $msg_error .= '<strong>Path</strong>: '.json_encode($event->getVarPath())."<br />\n";
                    
                    foreach ($event->getFilterMatch() as $filter) {
                        $msg_error .= "<strong>Description</strong>: (".$filter->getId().") ".$filter->getDescription(). "<br />\n";
                        $msg_error .= "<strong>Impact</strong>: ".$filter->getImpact(). "<br />\n";
                        $msg_error .= "<strong>Tags</strong>: ".implode(', ', $filter->getTags()). "<br />\n";
                    } 
                    
                    $msg_error .= "</div>\n";

                }

                if ($security_ajax){
                  $fichero = '_modules_/debug/phpids_ajax_'.time().'.txt';
                  $security_message = '<div style="padding:10px;">'
                                    . '  <h3>Aviso de seguridad</h3>'
                               //     . '  <div style="display:block;height:400px;overflow:auto;">'.$msg_error.'</div>'
                                    . '  <p>Se han detectado errores en los datos recibidos y se ha interrumpido la operación.<br />'
                                    . '     Si se repite este aviso contacte con su servicio de informática para que revise la integridad del sistema.</p>'
                                    . '    <a style="display:block;text-align:right;margin:10px 20px 0 0;" href="https://github.com/enygma/expose" alt="PHPIDS (PHP-Intrusion Detection System) "><img src="_images_/logos/phpids.png" border="0"></a>'
                                    . '</div>';
                  $result = array();
                  $result['error'] = 1;
                  $result['msg'] = $security_message;
                  echo json_encode($result);
                }else{
                  $fichero = '_modules_/debug/phpids_'.time().'.txt';
           
                  header('Expires: 0');
                  header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
                  Header('Content-type: text/html');
                  Header('charset: utf-8');
                  
                  function getip() {
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $ip = (!$ip)?($_SERVER['CLIENT_IP']):$ip;
                    $ip = (!$ip)?($_SERVER['HTTP_VIA']):$ip;
                    $ip = (!$ip)?($_SERVER['HTTP_X_FORWARDER_FOR']):$ip;
                    return $ip;
                  }
                  
                  ?>
                  <div style="filter:alpha(opacity=90);-moz-opacity:0.90;position: absolute;right: 5px;top: 5px;font-size: 0.4em;border: 1px solid red;display: block;width: 83px;height: 43px;">
                      <span style="display: block;position: absolute;top: 0px;left: 0px;font-size: 5em;color: #ff0000;">&#10042;</span>
                      <span style="position: absolute;bottom: 6px;left: 25px;font-family: Impact,Arial;font-size: 3em;font-weight: bold;">ju</span>
                      <span style="position: absolute;bottom: 2px;left: 44px;color: #ff0000;font-family: Impact;font-size: 6em;font-weight: bolder;">X</span>
                      <span style="position: absolute;right: 5px;bottom: 2px;display: inline-block;font-size: 1.1em;font-family: Arial;font-weight: 300;">FRAMEWORK</span>
                  </div>
                  <div style="text-align:left;max-width:800px;margin:50px auto;padding: 15px 15px; /*background: #f4f4f4;*/">
                      <h1 style="text-align:center;margin: 35px 15px; ">AVISO DE SEGURIDAD</h1>
                      <p>Se han detectado errores en los datos recibidos y se ha interrumpido la operación.<br />
                      Si se repite este aviso contacte con su servicio de informática para que revise la integridad del sistema.</p>
                      <p>Puede ignorar este mensaje y dirigirse a la <a href=".">página principal</a>.</p>
                      <?php if(1==1){ ?>
                      <h3>Detalles de la conexión:</h3>
                      <div style="max-height:250px;overflow-x:hidden;overflow-y:auto;margin:0px 0 10px 0;padding-left:10px;font: 8pt Courier New;background:#D9D9D7">
                          <?=$msg_error?>
                      </div>
                      <div style="text-align:left;">
                          Impacto: <b><?=$manager->getImpact()?></b> <span style="color:#bbb;">(Valores superiores a 8 se toman como presuntamente peligrosos)</span>
                      </div>
                      <?php } ?>
                      <div style="margin: 20px auto;height:80px;text-align:center;display:block;"><br />Sistema protegido por<br><a href="https://github.com/enygma/expose" alt="PHPIDS (PHP-Intrusion Detection System) "><img src="../_images_/logos/phpids.png" border="0"></a></div>
                  </div>
                   <style>
                    div,p,h1,h2,h3 {font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Droid Sans,Helvetica Neue,sans-serif;font-weight: 300;}
                    ::-webkit-scrollbar              {  width: 6px;  height: 6px;}
                    ::-webkit-scrollbar-button       {  display:none;}
                    ::-webkit-scrollbar-thumb        {  background: #babac0;  border: 0px none #ffffff;  border-radius: 50px;}
                    ::-webkit-scrollbar-thumb:hover  {  background: #b3b3b9;}
                    ::-webkit-scrollbar-thumb:active {  background: #b3b3b9;}
                    ::-webkit-scrollbar-track        {  background: #f2f2f4;  border: 0px none #ffffff;  border-radius: 50px;}
                    ::-webkit-scrollbar-track:hover  {  background: #eaeaec;}
                    ::-webkit-scrollbar-track:active {  background: #eaeaec;}
                    ::-webkit-scrollbar-corner       {  background: transparent;}
                   </style>
                  <?php 
                }

                $contenido .= 'PHPIDS'."\n";
                $contenido .= "\n\nARGS\n";
                $contenido .= print_r($_ARGS,true);
                $contenido .= "\n\nGET\n";
                $contenido .= print_r($_GET,true);
                $contenido .= "\n\nPOST\n";
                $contenido .= print_r($_POST,true);
                $contenido .= "\n\nReport\n";
//                $contenido .= str_replace(array('<pre>','</pre>','<br />','|','<div>','</div'>),array('',"\n","\n","\n",'',"\n\n"),$msg_error)."\n";
                $contenido .=str_replace(array('<pre>','</pre>'),array('',"\n"),$msg_error)."\n";


                if($hfp = fopen($fichero,'w+')){
                    fwrite($hfp,stripslashes($contenido));
                }  
                fclose($hfp);

                die();
                exit; 
                /******/
        }else{
            
         //        echo 'okis';

        }

         //       echo '<a href="?test=%22><script>eval(window.name)</script>">No attack detected - click for an example attack</a>';
    }  // if($impact>20){