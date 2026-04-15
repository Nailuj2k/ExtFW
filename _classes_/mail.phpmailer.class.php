<?php
/*
include_once(SCRIPT_DIR_LIB.'/phpmailer/class.phpmailer.php');
include_once(SCRIPT_DIR_LIB.'/phpmailer/class.pop3.php');
include_once(SCRIPT_DIR_LIB.'/phpmailer/class.smtp.php');

*/



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require SCRIPT_DIR_LIB.'/phpmailer/src/Exception.php';
require SCRIPT_DIR_LIB.'/phpmailer/src/PHPMailer.php';
require SCRIPT_DIR_LIB.'/phpmailer/src/SMTP.php';
//require SCRIPT_DIR_LIB.'/phpmailer/src/POP3.php';

class Mailer extends PHPMailer {
    var $from_name;
    var $from_email;
    var $to_name;
    var $to_email;
    //var $subject;
    var $body;
   // var $Sender;
    var $debug;
    var $template = true;
    
    public function __construct() {
    
      parent::__construct();
        
      $this->SetLanguage("es", SCRIPT_DIR_LIB."/phpmailer/language/");
      $this->IsSMTP(); 
      $this->CharSet    ='utf-8';
      $this->ContentType = 'text/html';
    //$this->SMTPDebug  = true; //($_SESSION['userlevel']>2000)?2:1; // enables SMTP debug information (for testing) // 1 = errors and messages// 2 = messages only
    //$this->SMTPAuth   = CFG::$vars['mail_use_smtp'];           // enable SMTP authentication
      $this->SMTPAuth = (!CFG::$vars['smtp']['anonymous']);  //(CFG::$vars['mail_smtp_username']&&CFG::$vars['mail_smtp_password']); 
      if(CFG::$vars['smtp']['ssl']) $this->SMTPSecure = "tls"; // sets the prefix to the servier
      $this->Host       = CFG::$vars['smtp']['server'];        //"smtp.gmail.com";      // sets GMAIL as the SMTP server
      $this->Port       = CFG::$vars['smtp']['port'];          //465;                   // set the SMTP port for the GMAIL server
      if(!CFG::$vars['smtp']['anonymous']){
        $this->Username   = CFG::$vars['smtp']['user'];      //'soporte@extralab.net';               // GMAIL username
        $this->Password = CFG::$vars['smtp']['password'];  //Crypt::md5_decrypt(CFG::$vars['smtp']['password'], CFG::$vars['prefix'].$dbpasswd);
      }
    //  $this->From($from_email); //$_POST['efrom'], $_POST['efrom']);
    //  $this->FromName($from_name); //$_POST['efrom'], $_POST['efrom']);
    //  $this->Subject  = $subject;
    
    
    //  $this->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    //  $this->AddAddress($to_mail, $to_name);
       
        if (CFG::$vars['site']['debug']['email']!==false){
            $debug_emails = explode(",", CFG::$vars['site']['debug']['email']);
            foreach ($debug_emails as $debug_email){
                $this->AddBCC( $debug_email,$debug_email);
            }
        }
 
    }
    
    public function Send() {

          // Vars::debug_var(CFG::$vars['smtp']);//
        if($this->template){        
          $body_email       = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
                            . '<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">'
                            . '<head>'
                            . '<title>'.CFG::$vars['site']['title'].'</title>'
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
      //$this->Subject = $this->subject;
      //$this->MsgHTML(eregi_replace("[\]",'',$msg_body));
      $this->IsHTML(true);
      $this->MsgHTML($body_email);
      return parent::Send();
    }

    public function SendPlain() {
      $msg_body  = $this->body ; 
      $this->ContentType = 'text/plain';
      $this->Body = strip_tags($msg_body);
      return parent::Send();
    }
}
