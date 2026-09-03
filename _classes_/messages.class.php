<?php

// http://justindomingue.github.io/ohSnap/
// http://goodybag.github.io/bootstrap-notify/
// http://bootboxjs.com/examples.html#bb-confirm-dialog
// http://carlosroso.com/notyf/
// https://tutorialzine.com/2013/07/50-must-have-plugins-for-extending-twitter-bootstrap
// http://jhollingworth.github.io/bootstrap-wysihtml5/

    class Messages{
        
        //static $top = 20;
        static $id = 1;
        static $messages = array();
        /***/
        public static function message($msg,$url=false,$delay=10000,$type='info'){
           if(is_array($msg)) return false; //$msg=print_r($msg,true);  //FIX where is adding msg as array???
           else if(trim($msg)=='error') return false;     
           else if(trim($msg)=='') return false;     
           self::$id = self::$id + 1;
           self::$messages[self::$id]=array();
           self::$messages[self::$id]['msg']=$msg;
           self::$messages[self::$id]['type']=$type;
           self::$messages[self::$id]['delay']=$delay;
           self::$messages[self::$id]['url']=$url;
        }

        public static function alert  ($msg,$u=false,$d=10000){ return self::message($msg,$u,$d,'warning');  }
        public static function warning($msg,$u=false,$d=10000){ return self::message($msg,$u,$d,'warning');  }
        public static function info   ($msg,$u=false,$d=10000){ return self::message($msg,$u,$d,'info'   );  }
        public static function success($msg,$u=false,$d=10000){ return self::message($msg,$u,$d,'success');  }
        public static function error  ($msg,$u=false,$d=10000){ return self::message($msg,$u,$d,'danger' );  }
        public static function danger ($msg,$u=false,$d=10000){ return self::message($msg,$u,$d,'danger' );  }
        
        public static function show() {
           if(self::$messages){
                if(!OUTPUT) echo '<script type="text/javascript">';
                foreach( self::$messages as $message) {
                   if(trim($message['msg'])!=''){   //FIX Check empty meessages
                       if(OUTPUT) {
                           echo $message['msg'];
                       }else {
                           echo "notify( '".addslashes($message['msg'])."', '".$message['type']."', '".$message['delay']."');";
                           if($message['url']){
                               ?> setTimeout(function() { location.href='<?=$message['url']?>'; }, <?=$message['delay']?>);<?php 
                           }
                       }
                   }else{
                       echo '/*empty msg '.$message['type'].'/'.$message['url'].'*/';
                   }
                }
                if(!OUTPUT) echo '</script>';
            }
        }

    }