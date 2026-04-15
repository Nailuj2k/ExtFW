<?php

// http://justindomingue.github.io/ohSnap/
// http://goodybag.github.io/bootstrap-notify/
// http://bootboxjs.com/examples.html#bb-confirm-dialog
// http://carlosroso.com/notyf/
// https://tutorialzine.com/2013/07/50-must-have-plugins-for-extending-twitter-bootstrap
// http://jhollingworth.github.io/bootstrap-wysihtml5/


    class Messages{
        
        //static $top = 20;
        static $messages = array();

        public static function message($msg,$ret=false,$url=false,$delay=1500,$type='info'){
            $button  = '<span  class="close"><span aria-hidden="true">';
            $button .= $url ? '<i style="font-size:.8em;" class="fa fa-sun-o fa-spin"></i>'
                            : '&times;';
            $button .= '</span></span>';
            
            $icon = '<i style="position:absolute;top:15px; left:15px;font-size:2em;" class="fa ';
            if($type=='warning') $icon .= 'fa-exclamation-triangle';
            if($type=='info')    $icon .= 'fa-info-circle';
            if($type=='success') $icon .= 'fa-check-square-o';
            if($type=='danger')  $icon .= 'fa-bug';
            $icon .= '"></i>';
            
            //$style_old= 'position:fixed;left:10px;top:'.self::$top.'px;margin:0px;width:360px;';



            $return = '<div style="'.$xxstyle.'" class="xalert xalert-'.$type.'" role="alert">'
                 //   . '<strong>'.$type.'</strong>'
                    . $icon
                    . $button
                    . '<p class="xalert-message">'
                    . $msg
                    .'</p>'
                    . '</div>'
                    ;
        
            if($url){   
                $return .= '<script type="text/javascript">'
                        .  'setTimeout( function(){ location.href="'.$url.'"; },'.$delay.');'
                        .  '</script>';
            }
            
           // self::$top = self::$top + 70;
              
            if($ret) 
               return $return;
            else
               self::$messages[] = $return;
//               echo $return;
        }
        
        public static function alert  ($msg,$r=false,$u=false,$d=1500){ return self::message($msg,$r,$u,$d,'warning');  }
        public static function warning($msg,$r=false,$u=false,$d=1500){ return self::message($msg,$r,$u,$d,'warning');  }
        public static function info   ($msg,$r=false,$u=false,$d=1500){ return self::message($msg,$r,$u,$d,'info'   );  }
        public static function success($msg,$r=false,$u=false,$d=1500){ return self::message($msg,$r,$u,$d,'success');  }
        public static function error  ($msg,$r=false,$u=false,$d=1500){ return self::message($msg,$r,$u,$d,'danger' );  }
        public static function danger ($msg,$r=false,$u=false,$d=1500){ return self::message($msg,$r,$u,$d,'danger' );  }
        
        public static function show() {
           foreach( self::$messages as $message) {
             echo $message;
            // echo "showMessageInfo('".$message."');";  //, {color: 'red'});";
            // echo "ohSnap('".$message."', {color: 'red'});";
             
           }
           echo '<script type="text/javascript">'
             .  '  $(".xalert .close").click( function(){ $(this).closest(".xalert").hide(); });'
             .  '</script>';

        }

    }

    <style>

#xalert-messages{padding: 0px;
        border: 0px solid #eed3d7;
        position: absolute;
        bottom: 20px;
        right: 10px;
        max-width:850px;
        overflow:hidden;
        }

.xalert{position:relative;display:inline-block;width:100%;margin-top:5px;min-height:50px;padding-right:5px;}
.xalert .xalert-message{display: block;margin:5px 10px 10px 50px;}

.xalert-danger   {  color:white;  background-color: #DA4453;}
.xalert-error    {  color:white;  background-color: #DA4453;}
.xalert-success  {  color:white;  background-color: #37BC9B;}
.xalert-info     {  color:white;  background-color: #4A89DC;}
.xalert-warning  {  color:white;  background-color: #F6BB42;}
.xalert-orange   {  color:white;  background-color: #E9573F;}

</style>