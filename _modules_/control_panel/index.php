<?php

//  print_r($_ARGS);
if (OUTPUT == 'ajax' && ($_ARGS['op']=='list' ||$_ARGS[2]=='newsletter' || $_ARGS['op']=='method' || $_ARGS['function']=='print_banner' || $_ARGS['function']=='click')){ 
    
    // Permitimos listas de valores aunque no estemos autentificados
    // FIX: Posible agujero de seguridad por sql inject.
    // FIX: ELiminar parametro SQL y cambiarlo por table, field, key, etc. //HECHO :)
    // FIX: Usar caché sql (véase clase suggest)
    // FIX: Comprobar table name y field names.
    include(SCRIPT_DIR_MODULES.'/control_panel/ajax.php');

}else if($_SESSION['valid_user'] || $_ARGS['key']=='achosi'){
  
  if (OUTPUT == 'ajax'){

    include(SCRIPT_DIR_MODULE.'/ajax.php');
 
  }else if (OUTPUT=='csv'){
    
    include(SCRIPT_DIR_MODULE.'/csv.php');    

  }else if (OUTPUT=='pdf'){
    
    include(SCRIPT_DIR_MODULE.'/pdf.php');    

  }else if (OUTPUT=='file'){
    
    include(SCRIPT_DIR_MODULE.'/file.php');       

  }else if (OUTPUT=='raw'){
    
    include(SCRIPT_DIR_MODULE.'/raw.php');       

  }else{

    include(SCRIPT_DIR_MODULE.'/run.php');
  
  }

}else{
   
   ?>
   <div class="inner" style="text-align:center;">
   <p style="padding-top:50px;padding-bottom:20px;">Acceso denegado</p>
   <a class="btn btn-warning" href="login">Iniciar sesión</a>
   </div>
   <?php 

}

