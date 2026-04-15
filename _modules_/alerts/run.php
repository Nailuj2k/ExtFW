<p class="inner"  style="display:none;border-bottom:1px solid #699ebe;margin-top:20px;padding-bottom:5px; text-align:right;"><a href="<?=MODULE?>" class="btn btn-success">Volver</a></p>

<div class="inner">
    
    <h1>Avisos</h1>
    
    <?php

    $documents = true;

    if($_ARGS[1])  $_SESSION['RRHH_CATEGORIE'] = $_ARGS[1];

    Table::init();
    Table::show_table('CFG_ALERTS'); 
    Table::show_table('CFG_ALERTS_FILES'); 

    ?>

</div>


<?php

    if(1==2 && $_ACL->hasPermission('alerts_edit')){

        ?>
        <p class="inner"  style="border-top:1px solid #699ebe;margin-top:100px;padding-top:5px; text-align:right;"><a href="<?=MODULE?>/admin" class="btn btn-success">Administrar avisos</a></p>
        <?php

    }
