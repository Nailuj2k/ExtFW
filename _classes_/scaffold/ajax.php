<?php 

if ($_ARGS['ajax']=='pdf'){


}else if ($_ARGS[2]=='files'){

  include('ajax_file.php');

}else{ 
 
  include('ajax_request.php');

}

?>