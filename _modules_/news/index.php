<?php


    if (OUTPUT == 'ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if (OUTPUT=='pdf'){

        include(SCRIPT_DIR_MODULE.'/pdf.php');    

    }else if (OUTPUT=='file'){

        include(SCRIPT_DIR_MODULE.'/file.php');       

    }else if (OUTPUT=='html'){

        include(SCRIPT_DIR_MODULE.'/html.php');

    }else if (OUTPUT=='txt'){

        include(SCRIPT_DIR_MODULE.'/txt.php');

    }else if($_ARGS[1]=='admin'){

        include(SCRIPT_DIR_MODULE.'/admin.php');

    }else if($_ARGS[1]=='install'){

        include(SCRIPT_DIR_MODULE.'/install.php');

    }else if($_ARGS[1]=='blogger'){

        include(SCRIPT_DIR_MODULE.'/run.0.php');

    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');

    }
