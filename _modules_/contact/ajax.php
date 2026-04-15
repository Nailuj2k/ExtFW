<?php

   // https://www.google.com/recaptcha/admin
   // $secret = '6Le1if8SAAAAAIsFJNuQVxsaBHd7VwEcIjlwpZVi'; // Priv Key
   // $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=".$_ARGS['g-recaptcha-response'];

    $result = array();
    $result['error'] = 0;
  //$result['msg']   = 'datos recibidos: '.print_r($_ARGS,true);      

    LOG::$messages['contact']='prueba';

    if($_ARGS[2]=='captcha'){
        $result['error'] = __LINE__;
        $result['msg']   = $_ARGS;    
        //include(SCRIPT_DIR_CLASSES.'/captcha.class.php'); 
        if($_ARGS['op']=='check'){
            if($_ARGS['value']){
                $captcha = Captcha::check($_ARGS['value']);
                $result['error'] = $captcha ? 0 : 1;     
           }else{
                $result['error'] = __LINE__;
           }
         } else if($_ARGS['op']=='reload'){
             $captcha = Captcha::create();
             $result['captcha']['help'] = $captcha['help'];
             $result['captcha']['label'] = $captcha['label'];
             $result['error'] = 0;
         }
         sleep(2);

    }else if($_ARGS[2]=='form'){


        $captcha = false;     // https://www.semicolonworld.com/tutorial/google-invisible-recaptcha-with-php
        if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']) { 


                $recaptcha = $_POST['g-recaptcha-response'];
                if ($recaptcha){

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                            CURLOPT_POST => 1,
                            CURLOPT_POSTFIELDS => array(
                                'secret' => CFG::$vars['captcha']['google_v3']['secret'],
                                'response' => $recaptcha
                            )
                        ));
                        $response = curl_exec($curl);
                        curl_close($curl);

                        if(strpos($response, '"success": true') !== FALSE) {
                            // echo '<h3>Thanks for your message!</h3>';
                            $captcha = true;
                            $result['msg'] =  "Captcha correcto";
                        } else {
                            //echo "<h3>Error</h3>";
                            $result['error'] = 1;
                            Messages::error( "Error captcha");  //. Código:".$responseData->error->codes);
                        }
                }else{
                    $result['error'] = 1;
                    $result['msg'] =   t('VERIFICATION_FAILED_TRY_AGAIN');  //'Ha fallado la verificación. Inténtelo de nuevo.';                 
                }
        } else if(CFG::$vars['captcha']['enabled']){ 
          // include(SCRIPT_DIR_CLASSES.'/captcha.class.php'); 

           if (Captcha::check($_POST['captcha'])){
               $captcha = true;
           }else{
             Messages::error( '<nobr>'.t('Captcha incorrecto').'.<font color=red><b>!</b></font></nobr><br />');
                 //$msg_error.= 'session_captcha:'.$_SESSION['captcha'].'<br />';
                 //$msg_error.= 'post_cpatcha:'.$_POST['captcha'].'<br />';
             //echo '<div class="error">'.$msg_error.'</div>';
           }
        }else{

               $captcha = true;

        }



        //log2mail($_ARGS,'_ARGS');

        // VALIDATE POST !!!!!
        $valid_email = Str::valid_email($_POST['email']);
        $valid_name  = Str::is_clean_text($_POST['nombre']);
        $valid_text  = Str::is_clean_text($_POST['notas']);

        $msg         = Str::escape($_POST['notas']);

        if(!$valid_email) {
           $result['error'] = 1;
           $result['msg'] =  'ERROR: '.t('EMAIL_INVALID');
        }else if(!$valid_name){       
           $result['error'] = 1;
           $result['msg'] =  'ERROR: '.t('NAME_INVALID');
        }else if(!$valid_text) {       
           $result['error'] = 1;
           $result['msg'] =  "ERROR - Mensaje no válido: Sólo se permiten caractéres alfanuméricos.";
        }else{
          // $verify = json_decode(file_get_contents($url));
           if ($captcha) {
              
                //$to = "soporte@extralab.net"; // CHANGE THIS TO YOUR OWN!
                

LOG::$messages['CFG.smtp'] = print_r(CFG::$vars['smtp'],true);
LOG::$messages['CFG.modules.MODULE'] = print_r(CFG::$vars['modules'][MODULE],true);

              //$subject  = t('CONTACT_FORM');
              //$message  = 'Empresa: ' . $_POST['empresa'] . "<br>";
                $message  = 'Nombre: ' . $_POST['nombre'] . "<br>";
                $message .= 'Email: '   . $_POST['email'] . "<br>";
              //$message .= 'NIF: '   . $_POST['nif'] . "<br>";
                $message .= 'Teléfono: '   . $_POST['phone'] . "<br>";
              //$message .= 'Tipo: '   . $_POST['tipo'] . "<br>";
                //$message .= 'ONG: '   . $_POST['ong'] . "<br>";
                //$message .= 'Cantidad: '   . $_POST['cantidad'] . "<br>";

                $message .= 'Mensaje: ' . $msg . "<br>";
                $m = new Mailer();

                $m->IsSMTP(); 
                //$m->SMTPDebug  = 1;
                $m->SetLanguage("es", SCRIPT_DIR_LIB."/phpmailer/language/");
                $m->CharSet    ='utf-8';
                $m->ContentType = 'text/html';                
                $m->Host       = CFG::$vars['smtp']['server'];        //"smtp.gmail.com";      // sets GMAIL as the SMTP server
                $m->Port       = CFG::$vars['smtp']['port'];          //465;                   // set the SMTP port for the GMAIL server
                $m->SMTPSecure = 'tls';

                $m->Username   = CFG::$vars['smtp']['user'];      //'soporte@extralab.net';               // GMAIL username
                $m->Password   = CFG::$vars['smtp']['password'];  //Crypt::md5_decrypt(CFG::$vars['smtp']['password'], CFG::$vars['prefix'].$dbpasswd);

                $m->MsgHTML(str_replace(['\n','\r'], ['<br />',''], $message));     

                $m->Subject = CFG::$vars['modules'][MODULE]['subject']?CFG::$vars['modules'][MODULE]['subject']:'Formulario Contacto' ;
                $m->body =  str_replace(['\n','\r'], ['<br />',''], $message);
                $m->SetFrom(CFG::$vars['smtp']['from_email'],CFG::$vars['smtp']['from_name']) ;

                if (CFG::$vars['modules'][MODULE]['reply_to_email']) 
                    $m->AddReplyTo(CFG::$vars['modules'][MODULE]['reply_to_email'],CFG::$vars['modules'][MODULE]['reply_to_name']);
                else
                    $m->addReplyTo(CFG::$vars['smtp']['from_email'],CFG::$vars['smtp']['from_name']);

                //  $m->AddBCC('soporte@extralab.net','Soporte');
                //  $m->filename = $uploaded_file;

                $m->AddAddress(CFG::$vars['modules'][MODULE]['email'],CFG::$vars['modules'][MODULE]['name']);               

                $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,EMAIL,SUBJECT,MESSAGE) VALUES(\''.'5'.'\',\''.$_POST['email'].'\',\''.$m->Subject.'\',\''.$m->body.'\')';

                if($m->Send()){
                     // $result['msg'] = "TEST - Esto es una simulación";
                      $result['msg']  = t('MESSAGE_SENT'); //'<p>Mensaje enviado <i class="fa fa-check" style="color:#40b075;"></i></p><p>Muchas gracias por ponerse en contacto con nosotros.</p>';
                      $result['text']  = CFG::$vars['contact_form']['response'];
                      Table::sqlExec($log_sql);
                      //'<p>Mensaje enviado <i class="fa fa-check" style="color:#40b075;"></i></p><p>Muchas gracias por ponerse en contacto con nosotros.</p>';
                }else{
                      $result['error'] = 1;
                      $result['msg'] = 'ERROR: '.t('CANT_SEND_MESSAGE');
                
                }


           } else {
            
              $result['error'] = 1;
              $result['msg'] = 'ERROR: '.t('CAPTCHA_INVALID'); 
            
           }
        }
    } // if form

    echo json_encode($result);