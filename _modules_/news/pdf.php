<?php
    include(SCRIPT_DIR_MODULES.'/news/head.php');
?>

<div id="item">

    <p id="tags"><?=$tags?></p>
    <h1 id="title"><?=$row[TB_PREFIX.'_TITLE']?></h1>
    <p id="date"><?=$row[TB_PREFIX.'_DATE']?></p>
    <h3 id="subtitle"><?=$row[TB_PREFIX.'_SUBTITLE']?></h3>

    <?php if ($row['VIDEO']=='1' && $images[0]['ID_PROVIDER']=='2'){?>

    <?php }else if($image) {?>
        <p class="image-main" id="image"><img style="max-width:100%;" src="<?=$image?>?ver=<?=$ver?>">
            <p class="image_desc"><?=$image_desc?></p>
        </p>
    <?php } ?>


    <p><?=$row[TB_PREFIX.'_TEXT']?></p>


</div> <!-- <div id="item"> -->



<?php


/*
Tabla: '.TB_PREFIX.'_'.TB_NAME.'
Columna: '.TB_PREFIX.'_ID int(10) unsigned NOT NULL auto_increment
Columna: '.TB_PREFIX.'_TITLE varchar(200)
Columna: '.TB_PREFIX.'_TITLE_fr varchar(200)
Columna: '.TB_PREFIX.'_TITLE_de varchar(200)
Columna: '.TB_PREFIX.'_TITLE_it varchar(200)
Columna: '.TB_PREFIX.'_TITLE_en varchar(200)
Columna: '.TB_PREFIX.'_SUBTITLE varchar(300)
Columna: '.TB_PREFIX.'_SUBTITLE_fr varchar(300)
Columna: '.TB_PREFIX.'_SUBTITLE_de varchar(300)
Columna: '.TB_PREFIX.'_SUBTITLE_it varchar(300)
Columna: '.TB_PREFIX.'_SUBTITLE_en varchar(300)
Columna: '.TB_PREFIX.'_NAME varchar(200)
Columna: '.TB_PREFIX.'_DATE date
Columna: '.TB_PREFIX.'_CLASS int(100)
Columna: '.TB_PREFIX.'_TEXT text
Columna: '.TB_PREFIX.'_TEXT_fr text
Columna: '.TB_PREFIX.'_TEXT_de text
Columna: '.TB_PREFIX.'_TEXT_it text
Columna: '.TB_PREFIX.'_TEXT_en text
Columna: '.TB_PREFIX.'_TOP int(1)
Columna: VIDEO int(1)
Columna: CREATED_BY int(5)
Columna: CREATION_DATE datetime
Columna: LAST_UPDATED_BY int(5)
Columna: LAST_UPDATE_DATE datetime
Columna: ACTIVE int(1)
Columna: GALLERY int(1)
Columna: FILES int(1)

*/

