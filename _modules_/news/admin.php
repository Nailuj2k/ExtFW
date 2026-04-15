<div class="inner" style="padding-top:90px;text-align:center">


<?php 



if( $_ACL->userHasRoleName('Administradores') ){

    ?><h3>Administración</h3><?php


    Table::init();
    //Table::show_table(TB_TABLE);
//    Table::show_table(TB_PREFIX.'_TAGS');
//    if (defined(TB_NAME.'_TABLE_TAG'))

    Table::show_table( TB_PREFIX.'_TAGS' );  

    Table::show_table(TB_PREFIX.'_'.TB_NAME.'_TAGS');  
    //Table::show_table(TB_TABLE.'_FILES');


}else{

    ?>
    <p>Acceso denegado.<br /> ¡Prueba otra vez!</p>
    <?php

}


?>


</div>