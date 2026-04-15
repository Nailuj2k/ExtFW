<?php 

    if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']) { 
            ?><script src='https://www.google.com/recaptcha/api.js'></script><?php  
    }else if(CFG::$vars['captcha']['enabled']) { 
            include(SCRIPT_DIR_CLASSES.'/captcha.class.php'); 
            $_captcha = Captcha::create();
            $fcaptcha = new Field();
            $fcaptcha->name = 'captcha';
            $fcaptcha->label = t('form_captcha_label','Resolver').' ('.t('Protección anti-spam').')';
            $fcaptcha->placeholder=/*'Resolver: '.*/$_captcha['label'];  //.' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (Protección anti-spam)';
            $inputCaptcha = new formInput($fcaptcha);
            //$inputCaptcha->textafter='aaaaaaaaa';

    } else { 
     
    }
?>


<div class="inner contact">
    <h1 class="contact-title" style="/*position:fixed;height:0px;wifth:0px;color:transparent;*/"><?=t('CONTACT_FORM')?></h1>

    <?php 
    if (CFG::$vars['site']['lpd_accept']['required']) { 
        $html = '
            <div class="control-group row">
                <div id="data-protect-info">
                    <p class="first"><i class="fa fa-info-circle"></i> Información básica sobre protección de datos. (<b style="cursor:pointer;" id="btn-show-lpd">leer más</b>)</p>
                    <p class="hidden"><b>Responsable del tratamiento</b> [SITE_NAME], SL</p>
                    <p class="hidden"><b>Dirección del responsable</b><br />[SITE_ADDRESS]</p>
                    <p class="hidden"><b>Finalidad</b> Sus datos serán usados para poder atender sus solicitudes y prestarle nuestros servicios.</p>
                    <p class="hidden"><b>Publicidad</b> Solo le enviaremos publicidad con su autorización previa, que podrá facilitarnos mediante la casilla correspondiente establecida al efecto.</p>
                    <p class="hidden"><b>Legitimación</b> Únicamente trataremos sus datos con su consentimiento previo, que podrá facilitarnos mediante la casilla correspondiente establecida al efecto.</p>
                    <p class="hidden"><b>Destinatarios</b> Únicamente se comunicarán los datos a terceras entidades cuando ello sea necesario para prestar los servicios contratados.</p>
                    <p class="hidden"><b>Derechos</b> Tiene derecho a saber qué información tenemos sobre usted, corregirla y eliminarla, tal y como se explica en la información adicional disponible en nuestra página web.</p>
                    <p class="hidden"><b>Información adicional</b> Más información en el apartado “SUS DATOS SEGUROS” de nuestra página web.</p>
                    <p class="last"></p>
                </div>
            </div>
            <style>
                #data-protect-info {text-align:left;margin:10px 0;padding:0px;width:100%;display:block;}
                #data-protect-info .btn {/*width:auto;*/}
                #data-protect-info p {text-align:left !important;font-size:0.9em;/*display:none;*/margin:0px;padding:5px;border-left:1px solid black;border-right:1px solid black;}
                #data-protect-info .first {border-top:1px solid black;}  
                #dNNata-protect-info p:last-child  {border-top:1px solid black;font-size:0px;line-height:0px;height:1px;}
                #data-protect-info .last  {border-top:1px solid black;font-size:0px;line-height:0px;height:0px;padding:0px;}
                #data-protect-info .hidden{display:none;}
            </style>';
    }//else{
        ?><div class="contact-form-help"><?=CFG::$vars['contact_form']['help']?></div><?php 
        //////$html = '<input type="checkbox" name="chk_priv" id="chk_priv" style="visibility:hidden;float:right;" nochecked>';
    //}
    ?>    
    
    <?php if(CFG::$vars['site']['address']){?>
    <p class="col col-left">
        <?=CFG::$vars['site']['address']?>
    </p>
    <?php } ?>

    <p class="col">
        <?php

          $nombre = new Field();
          $nombre->name = 'nombre';
          $nombre->label = t('NAME');//Nombre';
          $nombre->placeholder=' '; // t('YOUR_NAME','Nombre');
          if($_SESSION['userid']) $nombre->value=$_SESSION[USER_FULLNAME];


          $phone = new Field();
          $phone->name = 'phone';
          $phone->label = t('PHONE');
          $phone->placeholder=' '; // t('YOUR_PHONE', 'Teléfono');
          // if($_SESSION['userid']) $email->value=$_SESSION[USER_EMAIL];

          $email = new Field();
          $email->name = 'email';
          $email->label = t('EMAIL');
          $email->placeholder=' '; // t('YOUR_EMAIL','E-mail');
          if($_SESSION['userid']) $email->value=$_SESSION[USER_EMAIL];

          $notas = new Field();
          $notas->name = 'notas';
          $notas->label = t('MESSAGE');
          $notas->rows=10;
          $notas->wysiwyg=false; //'ckeditor';
          $notas->placeholder=' '; // t('COMMENTS','Comentarios');
          
          $boton = new Field();
          $boton->value = t('SUBMIT'); 
          $boton->id = 'btnsubmit';
          $boton->name = 'btnsubmit';
          $boton->class = 'btn-submit btn Xbtn-large btn-block btn-success g-recaptcha';
          $boton->extra = ' data-badge="inline" xdisabled="disabled"  data-size="invisible" data-sitekey="'.CFG::$vars['captcha']['google_v3']['public'].'" data-callback="onSubmitForm" ';

  /*        
          $captcha = new Field();
          $captcha->label = 'Captcha';
          $captcha->name = 'captcha';
          $captcha->value='<div class="g-recaptcha" data-sitekey="6Le1if8SAAAAAJAq1oZbIrGNNHhe58domPHYT_41"></div>';
*/
          if (CFG::$vars['site']['lpd_accept']['required']) { 
              $h = new Field();
              $h->type='html';
              $h->value=$html;
          }

          $chk_priv = new Field();
          $chk_priv->label = 'Haciendo clic, usted acepta la <a class="open_href" target="new" title="Política de privacidad" href="page/politica-de-privacidad">política de privacidad</a> de este sitio web. <span style="color:red">(Campo obligatorio)</span>'; 
          $chk_priv->help = '';
          $chk_priv->name = 'chk_priv';

          $chk_publi = new Field();
          $chk_publi->label = t('CONSENT_RECV_PUBLI','Consiento el uso de mis datos personales para recibir publicidad de su entidad.');
          $chk_publi->name = 'chk_publi';



          $inputNombre = new formInput($nombre);
          $inputPhone  = new formInput($phone);
          $inputEmail  = new formInput($email);
          $inputNotas  = new formTextarea($notas);
        //$inputCaptcha= new formHtml($captcha);
          $inputCheckboxPriv= new formCheckbox($chk_priv);
   	  if (CFG::$vars['site']['lpd_accept']['required']) { 
              $inputCheckboxPubli= new formCheckbox($chk_publi);
	  }
          if (CFG::$vars['site']['lpd_accept']['required']) $inputH= new formHtml($h);
          $inputButton = new formButton($boton);

         
          $form = new Form('contact',t('CONTACT_FORM','Formulario de contacto'));
          $form->addElement($inputNombre);
          $form->addElement($inputPhone);
          $form->addElement($inputEmail);
          $form->addElement($inputNotas);
          //$form->addElement($inputCaptcha);
          if (CFG::$vars['site']['lpd_accept']['required']) $form->addElement($inputH);

          /**
          if(CFG::$vars['captcha']['google_v3']['enabled']) { 
          } else { 
          $form->addElement($inputCaptcha);
          }
          **/
          if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']) { 
          } else if(CFG::$vars['captcha']['enabled']){ 
                  /*
                  $hc = new Field();
                  $hc->type='html';
                  $hc->value='<p class="text" style="margin-bottom:0px;">'.'<strong>Resolver</strong>: <span id="captcha-label" style="padding:1px 3px;"><span>'.$_captcha['label'].'</span> <i class="fa"></i></span><span style="float:right">Protección anti-spam <a id="captcha-reload" style="font-size:0.8em;background-color:#7194d5;color:white;padding:1px 4px;">Cambiar <i class="fa fa-refresh"></i>'.'</a></span></p>';
                  $inputHC= new formHtml($hc);
                  $form->addElement($inputHC);
                  */
              $form->addElement($inputCaptcha);
          }else{
          }

          $form->addElement($inputCheckboxPriv);
          $form->addElement($inputCheckboxPubli);
          $form->addElement($inputButton);
          $form->setAction( Vars::mkUrl( MODULE, 'ajax/form') );
         // $form->ajax=false;
          $form->render();   
          
          
        ?>
	    <br /><br /><br /><p id="error" class="error" style="display:none;color:red;">...</p><br /><br /><br /><br />
    </p>
</div>
    <div class="cart-ajaxloader ajax-loader" style="display:none;"><div class="loader"></div></div>
<!--<button id="bum">BUM</button>-->