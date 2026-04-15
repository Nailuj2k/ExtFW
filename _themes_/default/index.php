<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>">

    <head>

        <?php  
            HTML::css( SCRIPT_DIR_THEME.'/style.css?ver=2.3.7' );
            HTML::css( SCRIPT_DIR_THEME.'/style.loader.css?ver=1.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.buttons.css?ver=2.0.5' );
            HTML::css( SCRIPT_DIR_THEME.'/style.menu.css?ver=2.3.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.shop.css?ver=1.1.0' );
            
            if( $_ARGS[0]??''=='home'||$_ARGS[1]=='home') $documents = true;   
            include(SCRIPT_DIR_INCLUDES.'/head.php'); 

        ?>

    </head>

    <body class="body-<?=MODULE?> body-theme-<?=THEME?>">

        <?php
         
            include(SCRIPT_DIR_THEME.'/index.top.php'); 
            include(SCRIPT_DIR_THEME.'/index.header.php'); 

        ?>

        <section id="breadcrumb" class="inner">
        <?php 

            $bc = new Breadcrumb(/*$menu1->*/);
            if (MODULE == 'page')  Breadcrumb::$breadcrumbs = $menu1->crumbs();
            $bc->show();

        ?>
        </section>

        <section id="content">
             
        <?php

            if($_ARGS[0]=='home'||$_ARGS[1]=='home'){
                            
                HTML::js(SCRIPT_DIR_THEMES.'/default/newsticker.script.js?ver=1.0.0','defer');    
                
                // echo APP::$shortcodes->do_shortcode('[year]');
                // echo APP::$shortcodes->do_shortcode('[ajax url="news/html"]');
                
                //widget('page');
                widget('alerts'); // type=1 by default
                widget('alerts', 'type=2' );
                widget('alerts', 'type=3' );
                widget('alerts', 'type=4' );
               //// widget('news');
                widget('links');
                
            }

            include(SCRIPT_DIR_MODULE.'/index.php');   

        ?>
        </section>

        <?php
          widget('drawing');
          //widget('shoutbox');
          //widget('clock'); 
          //widget('snowflake');
        ?>

        <footer id="footer">
            <?php include(SCRIPT_DIR_THEME.'/footer.php'); ?>
        </footer>     
        
        <?php 

            HTML::js(SCRIPT_DIR_THEME.'/script.js?ver=1.1.8','defer');
            HTML::js(SCRIPT_DIR_THEME.'/script.menu.js?ver=1.1.6','defer');
            include(SCRIPT_DIR_INCLUDES.'/footer.php');                     

        ?>

    </body>
</html>