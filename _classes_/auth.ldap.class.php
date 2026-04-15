<?php
if(CFG::$vars['auth']=='ldap'){
    include(SCRIPT_DIR_CLASSES.'/ldap.class.php');
    include(SCRIPT_DIR_CLASSES.'/ldap.utils.class.php');
}else if(CFG::$vars['auth']=='demo'){
    include(SCRIPT_DIR_CLASSES.'/demo.class.php');
}
