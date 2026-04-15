<!-- Barra de progreso -->
        <div id="progress-container">
        <div id="progress-bar" class="rainbow"></div>
        </div>

        <div id="item">

            <div class="links-prev-next">
                <span class="link-prev"><?=$prev_next['PREV'] ?? '' ?></span>
                <span class="link-next"><?=$prev_next['NEXT'] ?? '' ?></span>
            </div>

            <p id="tags"><?=$tags?></p>
            <h1 id="title"><?=$row[TB_PREFIX.'_TITLE']?></h1>
            <p id="date"><?=$row[TB_PREFIX.'_DATE']?></p>
            <h3 id="subtitle"><?=$row[TB_PREFIX.'_SUBTITLE']?></h3>

            <?php if ($row['VIDEO']=='1' && $images[0]['ID_PROVIDER']=='2'){?>
                <div class="rvideo"><iframe src="https://www.youtube.com/embed/<?=$images[0]['LINK']?>?autoplay=1&controls=0&showinfo=0&autohide=1" frameborder="0" allowfullscreen></iframe></div>
                <p class="image_desc"><?=$image_desc?></p>    
            <?php }else if($image) {?>
                <p class="image-main" id="image"><img class="editable-image" src="<?=$image?>"><p class="image_desc"><?=$image_desc?></p></p>
            <?php }?>

            <?php if(CFG::$vars['plugins'][MODULE]['edit_item']) include(SCRIPT_DIR_MODULES.'/news/edit.php'); ?>

            <?php
                $_NAME_ = $row[TB_PREFIX.'_NAME'];
                $_ID_   = $row[TB_PREFIX.'_ID'];
                $_TEXT_ = $row[TB_PREFIX.'_TEXT'];
                $_CLASS_= 'page-content page-'.MODULE.'-content';
                $_AVD_  = true;
                
                $_TEXT_        = Table::unescape($_TEXT_);
                // $_TEXT = str_replace(['>','<'],['&gt;','&lt;']  ,$_TEXT_); // Fix para que no joda el editor inline

                include(SCRIPT_DIR_MODULES.'/page/inline_edit.php');

                $hash_id = md5(MODULE.'_views_'.$_ID_.'_'.$_SERVER['REMOTE_ADDR']);    
                $time_now = time();
                $last_view = isset($_SESSION[$hash_id]) ? $_SESSION[$hash_id] : 0;
                $diff = $time_now - $last_view;
                if($diff > 60 || !$row['VIEWS']){ // 1 hora
                    Table::sqlExec("UPDATE ".TB_PREFIX."_".TB_NAME." SET VIEWS = COALESCE(VIEWS, 0) + 1 WHERE ".TB_PREFIX."_ID = ".$_ID_);
                    $row['VIEWS']++;
                }
                $_SESSION[$hash_id] = $time_now;
                //}
                ?><div class="item-meta-data"><?php
                ?><div class="item-author"><?php  echo t('PUBLISHED_BY','Publicado por').': <b>'.$author['user_fullname'].'</b>'; ?></div><?php
                ?><div class="item-reads"><?php  echo 'Leído <b>'.$row['VIEWS'].'</b> '.Inflect::pluralize(t('TIME','vez'),$row['VIEWS']); ?></div><?php
                ?><div class="item-author-avatar"><img src="<?=$author['user_url_avatar'];?>" alt="<?=$author['user_fullname']; ?>"></div><?php
                ?><div class="item-rating"><?php   if($row['ALLOW_RATING'])    Rating::show($row[TB_PREFIX.'_ID']);  ?></div><?php
                ?></div><?php

                if (!empty($_SESSION['auth_id'])) { ?>
                    <script src="<?=SCRIPT_DIR_LIB?>/bitcoin/noble-secp256k1-1.2.14.js"></script>
                    <link href="<?=SCRIPT_DIR_JS?>/enhanced-select/enhanced-select.css?ver=1.0.0" rel="stylesheet">
                    <script src="<?=SCRIPT_DIR_JS?>/enhanced-select/enhanced-select.js?ver=1.0.0"></script>
                    <div class="item-nostr-publish">
                        <a id="btn-publish-nostr" class="btn-nostr-publish" data-id="<?=$_ID_?>" data-module="<?=MODULE?>">Publicar en Nostr</a>
                    </div>
                <?php } ?>

        </div> <!-- <div id="item"> -->

        <?php 

       // if($row['ALLOW_RATING'])    Rating::show($row[TB_PREFIX.'_ID']);
        if($row['ALLOW_COMMENTS'])  Comments::show($row[TB_PREFIX.'_ID']);

