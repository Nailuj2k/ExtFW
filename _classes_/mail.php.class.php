<?php

class Mailer {
    var $from_name;
    var $from_email;
    var $replyto_email;
    var $replyto_name;
    var $to_name;
    var $to_email;
    var $Subject;
    var $body;
    var $Sender;
    var $debug;
    var $error;    
    var $template = true;    
    var $cc=array();
    var $bcc=array();
    
    public function __construct() {
      //  parent::__construct();
    }

    public function SetFrom($email,$name) {
      $this->from_email = $email;
      $this->from_name = $name;
    }

    public function AddReplyTo($address,$name) {
      $this->replyto_email = $address;
      $this->replyto_name = $name;
    }

    public function AddAddress($address,$name) {
      $this->to_name = $name;
      $this->to_email = $address;
   }

    public function AddCC($address, $name = '') {
      array_push($this->cc, $name. ' <'.$address.'>');
    }

    public function AddBCC($address, $name = '') {
      array_push($this->bcc, $name. ' <'.$address.'>');
    }

    public function Send() {

        if (CFG::$vars['site']['debug']['email']!==false){
            $debug_emails = explode(",", CFG::$vars['site']['debug']['email']);
            foreach ($debug_emails as $debug_email){
                array_push($this->bcc, str_replace('@','.',$debug_email).'  <'.$debug_email.'>');
            }
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers.= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        $headers.= "From: ".$this->from_name." <".$this->from_email.">\r\n";
      //$headers.= "To: ".$this->to_name." <".$this->to_email.">\r\n";
        if($this->replyto_email) $headers.= "Reply-To: ".$this->replyto_name." <".$this->replyto_email.">\r\n";
        if(count($this->cc)>0)   $headers .= 'CC: ' . implode(",", $this->cc)  . "\r\n";
        if(count($this->bcc)>0)  $headers .= 'BCC: '. implode(",", $this->bcc) . "\r\n";
        $headers.= 'X-Mailer: PHP v'.phpversion()."\r\n";
      //$tpl_body_default = '<div>'
      //                  . '[BODY]<br /><br /><img src="[SITE_URL]/media/images/logo_email.png?ver=1.0"><br /></div>';

        if($this->template){        


        $body_email       = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
                          . '<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">'
                          . '<head>'
                          . '<title>[SITENAME]</title>'
                          . '    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'
                          . '    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">'
                          . '    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">'
                          . '</head>'
                          . '<body style="'.CFG::$vars['templates']['email']['style'].'">'
                          . str_replace( array( "\r\n"  
                                               ,'[SITE_NAME]'  
                                               ,'[SITE_EMAIL]' 
                                               ,'[SITE_PHONE]' 
                                                 ,'[SITE_ADDRESS]'
                                                 ,'[SITE_URL]'
                                                 ,'[BODY]'),
                                           array( '<br />'
                                                 ,CFG::$vars['site']['title']
                                                 ,CFG::$vars['site']['email']
                                                 ,CFG::$vars['site']['phone']
                                                 ,CFG::$vars['site']['address']
                                                 ,rtrim(SCRIPT_HOST.SCRIPT_DIR, '/')
                                                 ,$this->body),
                                           CFG::$vars['templates']['email']['header']
                                          .CFG::$vars['templates']['email']['body']
                                          .CFG::$vars['templates']['email']['footer'] )
                            . '</body>'
                            . '</html>';
        }else{
          $body_email       =$this->body;
        }

          //                  echo $body_email;
          if (function_exists('mail')){ 
            $this->error = mail($this->to_email,$this->Subject,$body_email,$headers);
          }else{
            $this->error = "ERROR - La función mail() está desactivada en el servidor.";
          } 
          return $this->error;
    }

}
