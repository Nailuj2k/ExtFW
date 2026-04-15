<?php


$TYPE = $_ARGS['type']?$_ARGS['type']:'1';

//echo 'TYPE: '.$TYPE;
if($TYPE=='gallery'||$TYPE=='files'){
    /*
    if ( CFG::$vars['site']['langs']['enabled']!==true ||  $_SESSION['lang']=='es'){ //CFG::$vars['default_lang']){
            $_title_field_name = 'n.'.TB_PREFIX.'_TITLE';
            $_text_field_name = 'n.'.TB_PREFIX.'_TEXT';
    }else{
            $_title_field_name = "COALESCE(NULLIF(n.".TB_PREFIX."_TITLE_".$_SESSION['lang'].",''), ".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";
            $_text_field_name = "COALESCE(NULLIF(n.".TB_PREFIX."_TEXT_".$_SESSION['lang'].",''), ".TB_PREFIX."_TEXT) AS ".TB_PREFIX."_TEXT";
    }

    $sql_news = "SELECT n.".TB_PREFIX."_ID,n.".TB_PREFIX."_NAME,$_title_field_name,$_text_field_name,f.FILE_NAME FROM ".TB_PREFIX."_".TB_NAME." n,".TB_PREFIX."_".TB_NAME."_FILES f WHERE f.".TB_NAME."_ID = n.".TB_PREFIX."_ID AND f.MINI = '1' AND n.ACTIVE='1' ORDER BY ".TB_PREFIX."_TOP DESC,".TB_PREFIX."_DATE DESC  LIMIT 2";  
    echo $sql_news; 
    $last_news = Table::sqlQuery($sql_news);


    if($last_news){
        ?><div class="items-<?=MODULE?>" id="items-<?=MODULE?>"><!--<h3>Enlaces de interés</h3>--><?php
        foreach ($last_news as $row){
            ?><div class="item-<?=MODULE?> shadow">
            <div class="item-<?=MODULE?>-img"><img src="media/<?=TB_NAME?>/files/<?=$row[TB_PREFIX.'_ID'].'/'.TN_PREFIX.$row['FILE_NAME']?>"></div>
            <div class="item-<?=MODULE?>-text"><a href="<?=MODULE?>/<?=$row[TB_PREFIX.'_NAME']?>"><?=$row[TB_PREFIX.'_TITLE']?></a><br /><?=Str::truncate(strip_tags($row[TB_PREFIX.'_TEXT']), 500, '...',  true)?></div>
            </div><?php
        }
        ?><div style="text-align:right;padding-bottom:5px;"><a class="btn btn-small btn-primary" href="<?=MODULE?>"><?=t('MORE').'_'.t(MODULE)?> ... </a></div></div><?php 
    }
    */
    include(SCRIPT_DIR_MODULES.'/page/inline_widget.php');


}else if($TYPE=='1'){


    if ( CFG::$vars['site']['langs']['enabled']!==true ||  $_SESSION['lang']=='es'){ //CFG::$vars['default_lang']){
            $_title_field_name = 'n.'.TB_PREFIX.'_TITLE';
            $_text_field_name = 'n.'.TB_PREFIX.'_TEXT';
    }else{
            $_title_field_name = "COALESCE(NULLIF(n.".TB_PREFIX."_TITLE_".$_SESSION['lang'].",''), ".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";
            $_text_field_name = "COALESCE(NULLIF(n.".TB_PREFIX."_TEXT_".$_SESSION['lang'].",''), ".TB_PREFIX."_TEXT) AS ".TB_PREFIX."_TEXT";
    }

    $sql_news = "SELECT n.".TB_PREFIX."_ID,n.".TB_PREFIX."_NAME,$_title_field_name,$_text_field_name,f.FILE_NAME FROM ".TB_PREFIX."_".TB_NAME." n,".TB_PREFIX."_".TB_NAME."_FILES f WHERE f.".TB_NAME."_ID = n.".TB_PREFIX."_ID AND f.MINI = '1' AND n.ACTIVE='1' ORDER BY ".TB_PREFIX."_TOP DESC,".TB_PREFIX."_DATE DESC  LIMIT 2";  
    //echo $sql_news; 
    $last_news = Table::sqlQuery($sql_news);




    if($last_news){
        ?><div class="items-<?=MODULE?>" id="items-<?=MODULE?>"><!--<h3>Enlaces de interés</h3>--><?php
        foreach ($last_news as $row){
            ?><div class="item-<?=MODULE?> shadow">
            <div class="item-<?=MODULE?>-img"><img src="media/<?=TB_NAME?>/files/<?=$row[TB_PREFIX.'_ID'].'/'.TN_PREFIX.$row['FILE_NAME']?>"></div>
            <div class="item-<?=MODULE?>-text"><a href="<?=MODULE?>/<?=$row[TB_PREFIX.'_NAME']?>"><?=$row[TB_PREFIX.'_TITLE']?></a><br /><?=Str::truncate(strip_tags($row[TB_PREFIX.'_TEXT']), 500, '...',  true)?></div>
            </div><?php
        }
        ?><div style="text-align:right;padding-bottom:5px;"><a class="btn btn-small btn-primary" href="<?=MODULE?>"><?=t('MORE').'_'.t(MODULE)?> ... </a></div></div><?php 
    }    

}else if($TYPE=='2'){


    if ( CFG::$vars['site']['langs']['enabled']!==true ||  $_SESSION['lang']=='es'){ //CFG::$vars['default_lang']){
            $_title_field_name = 'n.'.TB_PREFIX.'_TITLE';
            $_text_field_name = 'n.'.TB_PREFIX.'_TEXT';
    }else{
            $_title_field_name = "COALESCE(NULLIF(n.".TB_PREFIX."_TITLE_".$_SESSION['lang'].",''), ".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";
            $_text_field_name = "COALESCE(NULLIF(n.".TB_PREFIX."_TEXT_".$_SESSION['lang'].",''), ".TB_PREFIX."_TEXT) AS ".TB_PREFIX."_TEXT";
    }

    $sql_news_default = "SELECT n.".TB_PREFIX."_ID"
                    . ",n.".TB_PREFIX."_NAME"
                    . ",$_title_field_name"
                    . ",$_text_field_name"
         //           . ",f.FILE_NAME "
              . " FROM ".TB_PREFIX."_".TB_NAME." n"
         //     .      ",".TB_PREFIX."_".TB_NAME."_FILES f "
              . " WHERE  n.ACTIVE='1' "
         //     . "   AND f.MINI = '1' "
         //     . "   AND f.".TB_NAME."_ID = n.".TB_PREFIX."_ID"
              . " ORDER BY ".TB_PREFIX."_DATE DESC "
              . " LIMIT 2";  

    $sql_news_most_visited = "SELECT n.".TB_PREFIX."_ID"
                    . ",n.".TB_PREFIX."_NAME"
                    . ",n.VIEWS"
                    . ",$_title_field_name"
                    . ",$_text_field_name"
         //           . ",f.FILE_NAME "
              . " FROM ".TB_PREFIX."_".TB_NAME." n"
         //     .      ",".TB_PREFIX."_".TB_NAME."_FILES f "
              . " WHERE  n.ACTIVE='1' "
         //     . "   AND f.MINI = '1' "
         //     . "   AND f.".TB_NAME."_ID = n.".TB_PREFIX."_ID"
              . " ORDER BY n.VIEWS DESC "
              . " LIMIT 2";

    $sql_news_most_commented = "SELECT n.".TB_PREFIX."_ID"
                    . ",n.".TB_PREFIX."_NAME"
                    . ",COUNT(c.id) AS COMMENTS_COUNT"
                    . ",$_title_field_name"
                    . ",$_text_field_name"
         //           . ",f.FILE_NAME "
              . " FROM ".TB_PREFIX."_".TB_NAME." n"
              . " LEFT JOIN POST_COMMENTS c ON c.module_id = n.".TB_PREFIX."_ID AND c.post_id = n.".TB_PREFIX."_ID "
         //     .      ",".TB_PREFIX."_".TB_NAME."_FILES f "
              . " WHERE  n.ACTIVE='1' "
         //     . "   AND f.MINI = '1' "
         //     . "   AND f.".TB_NAME."_ID = n.".TB_PREFIX."_ID"
            //  . " GROUP BY n.".TB_PREFIX."_ID "
              . " ORDER BY COMMENTS_COUNT DESC "
              . " LIMIT 2";

/**
 * 
 * SELECT n.NOT_ID,
       n.NOT_NAME,
       COUNT(c.id) AS COMMENTS_COUNT,
       n.NOT_TITLE,
       n.NOT_TEXT FROM NOT_NEWS n 
LEFT JOIN POST_COMMENTS c 
    ON c.module_id = 1                // 1:news 
   AND c.post_id = n.NOT_ID 
WHERE n.ACTIVE='1' 
GROUP BY n.NOT_ID 
ORDER BY COMMENTS_COUNT DESC LIMIT 2
 * 
 * 
 *  */


    $sql_news = $sql_news_most_visited;

    //echo $sql_news; 
    $last_news = Table::sqlQuery($sql_news);

    if($last_news){
        ?><div class="items-<?=MODULE?>" id="items-<?=MODULE?>"><!--<h3>Enlaces de interés</h3>--><?php
        foreach ($last_news as $row){
            echo '<ul class="menu item-'.MODULE.' shadow">'
           //    . '<div class="item-'.MODULE.'-img"><img src="media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.TN_PREFIX.$row['FILE_NAME'].'"></div>'
               . '<li class="item-'.MODULE.'-text">'
               . '<a href="'.MODULE.'/'.$row[TB_PREFIX.'_NAME'].'">'
               . $row[TB_PREFIX.'_TITLE']
               . '('.$row['VIEWS'].')'
               . '</a>'
           //    . '<br />'.Str::truncate(strip_tags($row[TB_PREFIX.'_TEXT']), 100, '...',  true)
               . '</li>'
               . '</ul>';
        }
    }    

}else{
    
   
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
    <?php


////}else{

   //include(SCRIPT_DIR_MODULES.'/page/inline_widget.php');

}














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

