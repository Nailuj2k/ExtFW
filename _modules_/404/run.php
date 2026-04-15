<link  href="<?=SCRIPT_DIR_MODULE?>/style.css" rel="stylesheet" type="text/css" />

<div id="inner" class="inner">

<h3>404 <?=t('PAGE_NOT_FOUND','Página no encontrada')?></h3>

<p><?=t('SORRY_PAGE_NOT_FOUND','Lo sentimos, la página solicitada no existe o no se encuentra disponible.')?></p>


<p><?=t('WHAT_CAN_YOU_DO','¿Qué puede hacer?')?></p>

<ul>
    <li><?=t('GO_BACK_TO_HOME','Volver a la <a href="/">página de inicio.</a>')?></li>
    <li><a onclick="javascript:history.back()"><?=t('CLICK_HERE_TO_GO_BACK','Pinche aquí')?></a> <?=t('TO_GO_BACK','para volver atrás.')?></li>
    <?php 
    /*
    if($_ARGS[1]){
        $found_rows = Table::sqlQuery("SELECT * FROM CLI_PAGES WHERE item_name= '".$_ARGS[1]."'");
        if(count($found_rows)==1){
            echo '<li>Ir a esta <a href="/page/'.$_ARGS[1].'">Entrada blog.</a></li>';
        }
    }
    */
    ?>
</ul>
</div>
