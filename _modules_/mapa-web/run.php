<link  href="<?=SCRIPT_DIR_MODULE?>/style.css?ver=1.0.3" rel="stylesheet" type="text/css" />


<?php

    if ($_ARGS[1]=='old'){
        ?>
            <div class="inner">
            <h1>area8.sms.carm.es</h1>
            <p>
                <a class="btn btn-small" id="expand_all">Expandir todo</a> 
                <a class="btn btn-small" id="contract_all">Contraer todo</a>
            </p> 
            <?php
                include(SCRIPT_DIR_MODULE.'/run.'.$_ARGS[1].'.php');
            ?>
            </div>
            <iframe id="iframe-a8" style="position:absolute;right:0;top:0;width:450px;height:600px;border:1px solid black;"></iframe>
         <?php

     }else{
         ?>

            <div class="inner" style="background:white;max-width:1024px;padding:20px;">
                <h1><?=ucwords(MODULE)?></h1>
                <div style="position:relative;">
                    <?php
                        include(SCRIPT_DIR_MODULE.'/tree.php');
                    ?>
                    <?php if(!isset($_ARGS['iframe'])){?> 
                    <div class="menu_page">
                        <iframe class="page_frame" id="page_frame" src="/" width="1200" height="1500" scrolling="no" border="0"></iframe>
                    </div> 
                    <?php } ?>
                </div>
             </div>
            <style>
            </style>

         <?php
     }

?>


