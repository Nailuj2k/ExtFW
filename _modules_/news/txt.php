<?php
    include(SCRIPT_DIR_MODULE.'/head.php');
?>


    <p id="tags"><?=$tags?></p>
    <h1 id="title"><?=$row[TB_PREFIX.'_TITLE']?></h1>
    <p id="date"><?=$row[TB_PREFIX.'_DATE']?></p>   
    <?php if ($row[TB_PREFIX.'_SUBTITLE']) {?>
    <h3 id="subtitle">SUB <?=$row[TB_PREFIX.'_SUBTITLE']?></h3>
    <?php } ?>
    <?php if ($row['VIDEO']=='1' && $images[0]['ID_PROVIDER']=='2'){?>
    <?php }else if($image) {?>
        <p> <img style="max-width:100%;" alt="<?=$image?>" src="<?=$image?>"><br><?=$image_desc?></p>
    <?php } ?>
    <p><?=$row[TB_PREFIX.'_TEXT']?></p>


