<?php 

if (CFG::$vars['smtp']['transport']=='phpmailer'){

   include(SCRIPT_DIR_CLASSES.'/mail.phpmailer.class.php');

}else{

   include(SCRIPT_DIR_CLASSES.'/mail.php.class.php');

}




