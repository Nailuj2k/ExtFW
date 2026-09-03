<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>" data-theme="light">

    <head>

        <script>
        // Pre-paint: aplica el tema guardado ANTES de renderizar (evita flash blanco/negro)
        (function(){
            try {
                var t = localStorage.getItem('theme');
                if (!t && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches) t = 'dark';
                if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            } catch(e) {}
        })();
        </script>

        <?php
            HTML::css( SCRIPT_DIR_THEME.'/style.css?ver=3.0.2' );
            HTML::css( SCRIPT_DIR_THEME.'/style.loader.css?ver=1.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.buttons.css?ver=3.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.menu.css?ver=3.0.0' );
         
            //if (defined('MODULE_SHOP')) 
            //    if (MODULE_SHOP!==false) 
            //        HTML::css( SCRIPT_DIR_MODULES.'/shop/style.shop.css?ver=1.1.0' );
            
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

        <?php if(/*$_ARGS[0]??''=='home'||*/$_ARGS[1]=='home'){?>
        <section id="slider" class="inner">
            <?php include(SCRIPT_DIR_THEME.'/index.slider.php'); ?>
        </section>
        <?php } ?>   
        
        <section id="content">
             
        <?php

            if( CFG::$vars['widget']['whatsapp'] )
                echo APP::$shortcodes->do_shortcode('[whatsapp phone="'.CFG::$vars['site']['phone'].'"]');

            //echo APP::$shortcodes->do_shortcode('[minichat url="https://queesbitcoin.net/sse" history="2" limit="100"]');
            //echo APP::$shortcodes->do_shortcode('[minichat url="https://tienda.extralab.net/sse" history="2" limit="100"]');

            if($_ARGS[0]=='home'||$_ARGS[1]=='home'){               


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

            HTML::js(SCRIPT_DIR_THEME.'/script.js?ver=2.0.0','defer');
            HTML::js(SCRIPT_DIR_THEME.'/script.menu.js?ver=1.1.6','defer');
            include(SCRIPT_DIR_INCLUDES.'/footer.php');                     

        ?>

    </body>
</html>