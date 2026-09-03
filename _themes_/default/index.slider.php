<?php
   /*
    CREATE TABLE `CFG_SLIDER` (
    `ID` int(5) unsigned NOT NULL AUTO_INCREMENT,
    `NAME` varchar(100) DEFAULT NULL,
    `FILE_NAME` varchar(200) DEFAULT NULL,
    `DESCRIPTION` text DEFAULT NULL,
    `ID_ORDER` int(8) DEFAULT NULL,
    `ACTIVE` int(1) DEFAULT 1,
    `URL` varchar(200) DEFAULT NULL,
    `LINK` varchar(200) DEFAULT NULL,
    `COLOR` varchar(12) DEFAULT NULL,
    `FRAME_TYPE` int(5) DEFAULT 0,
    `SOURCE` varchar(200) DEFAULT NULL,
     PRIMARY KEY (`ID`)
    ) 

    CREATE TABLE `NOT_NEWS` (
    `NOT_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `NOT_TITLE` varchar(200) DEFAULT NULL,
    `NOT_TITLE_en` varchar(200) DEFAULT '',
    `NOT_SUBTITLE` varchar(300) DEFAULT NULL,
    `NOT_SUBTITLE_en` varchar(300) DEFAULT '',
    `NOT_NAME` varchar(200) DEFAULT NULL,
    `NOT_DATE` date DEFAULT NULL,
    `NOT_CLASS` int(100) DEFAULT NULL,
    `NOT_TEXT` text DEFAULT NULL,
    `NOT_TEXT_en` text DEFAULT NULL,
    `NOT_TOP` int(1) DEFAULT 0,
    `VIDEO` int(1) DEFAULT 0,
    `ACTIVE` int(1) DEFAULT 1,
    `GALLERY` int(1) DEFAULT 0,
    `FILES` int(1) DEFAULT 0,
    `KEYWORDS` text DEFAULT NULL,
    `DESCRIPTION` text DEFAULT NULL,
    `NOT_TITLE_it` varchar(200) DEFAULT '',
    `NOT_SUBTITLE_it` varchar(300) DEFAULT '',
    `NOT_TEXT_it` text DEFAULT NULL,
    `NOT_NAME_en` varchar(200) DEFAULT '',
    `TOP_IMAGE` int(1) DEFAULT 1,
    `NOT_NAME_it` varchar(200) DEFAULT '',
    `ALLOW_COMMENTS` int(1) DEFAULT 1,
    `ALLOW_RATING` int(1) DEFAULT 1,
    `USER_ID` int(5) DEFAULT 1,
    `SIGN` varchar(40) DEFAULT NULL,
    `URL` varchar(100) DEFAULT NULL,
    `VIEWS` int(5) DEFAULT NULL,
    `SLIDER` int(1) DEFAULT 0,
    PRIMARY KEY (`NOT_ID`)
    )

    CREATE TABLE `NOT_NEWS_FILES` (
    `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
    `NEWS_ID` int(7) DEFAULT NULL,
    `NAME` varchar(100) DEFAULT NULL,
    `NAME_en` varchar(100) DEFAULT '',
    `FILE_NAME` varchar(200) DEFAULT NULL,
    `ID_PROVIDER` int(5) DEFAULT 1,
    `MINI` int(1) DEFAULT 0,
    `MAIN` int(1) DEFAULT 0,
    `DESCRIPTION` varchar(200) DEFAULT NULL,
    `DESCRIPTION_en` varchar(200) DEFAULT '',
    `ITEM_ORDER` int(5) DEFAULT NULL,
    `ACTIVE` int(1) DEFAULT 1,
    `LINK` varchar(200) DEFAULT NULL,
    `ISBN10` varchar(10) DEFAULT NULL,
    `ISBN13` varchar(13) DEFAULT NULL,
    `SLIDER` int(1) DEFAULT 0,
    PRIMARY KEY (`ID`)


    CFG_ALERTS` (
    `ID` int(5) unsigned NOT NULL AUTO_INCREMENT,
    `DESCRIPTION` varchar(200) DEFAULT NULL,
    `DESCRIPTION_en` varchar(200) DEFAULT '',
    `TEXT` text DEFAULT NULL,
    `TEXT_en` text DEFAULT NULL,
    `TYPE` varchar(7) DEFAULT '1',
    `ACTIVE` int(1) DEFAULT 1,
    `D4TE` date DEFAULT NULL,
    `ID_CATEGORIE` int(5) DEFAULT 0,
    `D4TE_END` date DEFAULT NULL,
    `SLIDER` int(1) DEFAULT 0,


    CFG_ALERTS_FILES` (
    `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
    `ITEM_ID` int(7) DEFAULT NULL,
    `NAME` varchar(100) DEFAULT NULL,
    `FILE_NAME` varchar(200) DEFAULT NULL,
    `ID_PROVIDER` int(5) DEFAULT 1,
    `LINK` varchar(200) DEFAULT NULL,
    `DESCRIPTION` varchar(200) DEFAULT NULL,
    `ACTIVE` int(1) DEFAULT 1,
    `MINI` int(1) DEFAULT 0,
    `MAIN` int(1) DEFAULT 0,
    `ITEM_ORDER` int(5) DEFAULT NULL,
    `NAME_en` varchar(100) DEFAULT '',
    `DESCRIPTION_en` varchar(200) DEFAULT '',
   `SLIDER` int(1) DEFAULT 0,
    PRIMARY KEY (`ID`)

    */
    //   CFG_SLIDER.FRAME_TYPE     = ['0'=>'Imagen','1'=>'Video','2'=>'YouTube'];
 
    $slider_include_slider = true;  // to include slider entries in index.slider.php
    $slider_include_news   = true;  // to include news slider in index.slider.php
    $slider_include_alerts = true;  // to include alerts slider in index.slider.php
    $slider_include_shop   = true;  // to include shop slider in index.slider.php
    $slider_include_blog   = true;  // to include blog slider in index.slider.php
    
    $sql_slides = '';

    if($slider_include_slider){
        $sql_slides = "
            SELECT
                FRAME_TYPE,
                CONCAT('/media/slider/images/.big_', FILE_NAME) AS FILE_NAME,
                SOURCE,
                NAME,
                DESCRIPTION,
                LINK,
                ID_ORDER
            FROM CFG_SLIDER
            WHERE ACTIVE = '1' ";
    }

    if($slider_include_news) {
    
        // fragment of code showing having a translatable field in the SQL query for news 
        // $_lang = Str::sanitizeName($_SESSION['lang']);
        //     $fields[] = "COALESCE(NULLIF(".TB_PREFIX."_TITLE_".$_lang.",''), ".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";
        if($slider_include_slider){ 
            $sql_slides .= " UNION ALL "; 
        }

    
        // fragment of code showing having a translatable field in the SQL query for news 
        // $_lang = Str::sanitizeName($_SESSION['lang']);
        //     $fields[] = "COALESCE(NULLIF(".TB_PREFIX."_TITLE_".$_lang.",''), ".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";

        $sql_slides .= "SELECT
            0 AS FRAME_TYPE,
            CONCAT('/media/NEWS/files/', n.NOT_ID, '/', nf.FILE_NAME) AS FILE_NAME,
            NULL AS SOURCE,
            n.NOT_TITLE AS NAME,
            n.NOT_SUBTITLE AS DESCRIPTION,
            CONCAT('/news/', n.NOT_NAME) AS LINK,
            999 AS ID_ORDER
        FROM NOT_NEWS n
        JOIN NOT_NEWS_FILES nf ON nf.ID = (
            SELECT f.ID FROM NOT_NEWS_FILES f
            WHERE f.NEWS_ID = n.NOT_ID
              AND (f.SLIDER = 1 OR f.MAIN = 1)
            ORDER BY f.SLIDER DESC, f.MAIN DESC
            LIMIT 1
        )
        WHERE n.SLIDER = 1 AND n.ACTIVE = '1'";

    }

    if($slider_include_alerts) {

        // only alerts of type 3 (Image) are included in the slider, 
        // as type 1 (Video) and type 2 (YouTube) are not supported (maybe in future) in this implementation

        if($sql_slides !== ''){
            $sql_slides .= " UNION ALL ";
        }

        $sql_slides .= "SELECT
            0 AS FRAME_TYPE,
            CONCAT('/media/CFG_ALERTS_FILES/files/', a.ID, '/', af.FILE_NAME) AS FILE_NAME,
            NULL AS SOURCE,
            a.DESCRIPTION AS NAME,
            a.`TEXT` AS DESCRIPTION,
            CONCAT('/alerts/', a.ID) AS LINK,
            999 AS ID_ORDER
        FROM CFG_ALERTS a
        JOIN CFG_ALERTS_FILES af ON af.ID = (
            SELECT f.ID FROM CFG_ALERTS_FILES f
            WHERE f.ITEM_ID = a.ID
              AND (f.SLIDER = 1 OR f.MAIN = 1)
              AND f.ACTIVE = '1'
            ORDER BY f.SLIDER DESC, f.MAIN DESC
            LIMIT 1
        )
        WHERE a.TYPE = '3' AND a.SLIDER = '1'";
    }


    if($sql_slides === ''){
        return;
    }

    $sql_slides .= " ORDER BY ID_ORDER";
echo "<!-- SQL for slider: $sql_slides -->";  // Debugging line to show the SQL query
    $slides = Table::sqlQuery($sql_slides);



    if (is_array($slides) && count($slides) > 0) {

        ?>
        <div class="hero-slider" style="position:relative;">

            <a style="position:absolute;top:50%;left:10px;transform:translateY(-50%);z-index:2;cursor:pointer;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:rgba(0,0,0,0.25);color:#fff;backdrop-filter:blur(2px);transition:background .2s;" id="slide-arrow-left" onmouseover="this.style.background='rgba(0,0,0,0.6)'" onmouseout="this.style.background='rgba(0,0,0,0.25)'">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <a style="position:absolute;top:50%;right:10px;transform:translateY(-50%);z-index:2;cursor:pointer;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:rgba(0,0,0,0.25);color:#fff;backdrop-filter:blur(2px);transition:background .2s;" id="slide-arrow-right" onmouseover="this.style.background='rgba(0,0,0,0.6)'" onmouseout="this.style.background='rgba(0,0,0,0.25)'">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>

            <div class="hero-track"><?php
                foreach ($slides as $index => $slide) {
                    $type        = $slide['FRAME_TYPE'];
                    $image       = $slide['FILE_NAME']; // ruta completa ya construida en el SQL (slider y news)
                    $video_source= $slide['SOURCE'];
                    ?>
                    <div class="hero-slide">

                        <?php if ($slide['NAME'] || $slide['DESCRIPTION']){ ?>
                        <div class="slide-text" style="background:#ffffff50;width:auto;height:auto;padding:10px;position:absolute;top:10%;left:10%;max-width:80%;color:#ffffff;text-shadow: 1px 1px 2px #00000088;">
                        <?php if($slide['LINK']){?><a href="<?= htmlspecialchars($slide['LINK']) ?>"><?php }?>     
                        <div class="slide-text-title" style="font-size:3em;font-weight:bold;color:white;font-weight:bold;"><?=$slide['NAME'] ?></div>
                        <?php if($slide['LINK']){?></a><?php }?>     
                        <div class="slide-text-subtitle" style="font-size:1.7em;color:white;"><?=$slide['DESCRIPTION'] ?></div>
                        </div> 
                        <?php } ?>

                        <?php if (!empty($slide['LINK'])) { ?>
                            <a href="<?= htmlspecialchars($slide['LINK']) ?>" class="slide-full-link" aria-label="Ver trabajo"></a>
                        <?php } ?>

                        <?php if ($type == '2'): // YouTube ?>
                            <iframe 
                                src="https://www.youtube.com/embed/<?= $video_source ?>?autoplay=1&loop=1&playlist=<?= $video_source ?>&mute=1&controls=0&modestbranding=1&rel=0&showinfo=0" 
                                frameborder="0" 
                                allow="autoplay; encrypted-media" 
                                allowfullscreen
                                class="slide-bg">
                            </iframe>
                        <?php elseif ($type == '1'): // Local Video ?>
                            <video src="<?= $video_source ?>" class="slide-bg" poster="<?= $image ?>" autoplay loop muted playsinline></video>
                        <?php else: // Image ?>
                            <img src="<?= $image ?>" class="slide-bg" alt="">
                        <?php endif; ?>
                        
                        <div class="hero-overlay"></div>
                    </div>
                    <?php
                }
                ?>
            </div><!-- End hero-track -->
            
            <!-- Indicadores -->
            <div class="slider-indicators">
                <?php for ($i = 0; $i < count($slides); $i++){ ?>
                    <div class="indicator <?= ($i === 0) ? 'active' : '' ?>" data-slide="<?= $i ?>"></div>
                <?php } ?>
            </div>

        </div>

        <?php

        HTML::js(SCRIPT_DIR_THEMES.'/default/script.slider.js?ver=1.0.0','defer');

        HTML::css(SCRIPT_DIR_THEMES.'/default/style.slider.css?ver=1.0.0');
        HTML::render('css');  // Render the CSS elements immediately after adding them
        
    }