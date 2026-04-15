<?php    

//if($_SESSION['valid_user']){    

    if (OUTPUT=='ajax'){

        include(SCRIPT_DIR_MODULE.'/ajax.php');

    }else if ($_ARGS[1]=='test'){
    
        include(SCRIPT_DIR_MODULE.'/test.php');

    }else if (OUTPUT=='file'){
    
        include(SCRIPT_DIR_MODULE.'/file.php');       

    }else if (OUTPUT=='pdf'){
    
        include(SCRIPT_DIR_MODULE.'/pdf.php');

    }else if (OUTPUT=='api'){
    
        include(SCRIPT_DIR_MODULE.'/api.php');
        
    }else if (OUTPUT=='qr'){
    
        include(SCRIPT_DIR_MODULE.'/qr.php');
        
    }else if (OUTPUT=='raw'){
    
        include(SCRIPT_DIR_MODULE.'/raw.php');            

    }else if (OUTPUT=='html'){
    
        include(SCRIPT_DIR_MODULE.'/html.php');        

    }else if (OUTPUT=='txt'){
    
        include(SCRIPT_DIR_MODULE.'/txt.php');
        
    }else{

        include(SCRIPT_DIR_MODULE.'/run.php');
        //include(SCRIPT_DIR_MODULES.'/snow/index.php');

    }
/*
}else{

    ?>
    <link  href="<?=SCRIPT_DIR_MODULE?>/style.css?ver=1.2" rel="stylesheet" type="text/css" />
    <img src="_themes_/simple/images/demo_contact.png" style="max-width:650px;margin:20px auto;">
    <?php 
 
}   
*/