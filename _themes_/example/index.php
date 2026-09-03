<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>" data-theme="light">
    <head>

        <script>
        // Pre-paint: fija el tema antes de pintar, si no se ve un flash blanco.
        // Sin preferencia guardada se respeta la del sistema.
        (function(){
            try {
                var t = localStorage.getItem('theme');
                if (!t && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches) t = 'dark';
                if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            } catch(e) {}
        })();
        </script>

        <?php

            //HTML::css( SCRIPT_DIR_THEME.'/fonts/style.css?ver=2.3.3' );
            HTML::css( SCRIPT_DIR_THEME.'/reset.css?ver=1.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.css?ver=2.4.0' );
            HTML::css( SCRIPT_DIR_THEMES.'/default/style.buttons.css?ver=2.0.5' );
            include(SCRIPT_DIR_INCLUDES.'/head.php'); 

            $more_items = array();
            $more_items_id = 1000;                    $more_items_id++;
            $more_items[$more_items_id]['id']=$more_items_id; //+$row['ID'];
            $more_items[$more_items_id]['name']='cart';
            //$more_items[$more_items_id]['caption']='<a class="xheader-button-cart view-shop xnavbar-link"><i class="fa fa-shopping-cart"></i> '.t('VIEW_CART').'<span id="cart-items-count" class="badge jxCart_quantity"> </span></a>';
            $more_items[$more_items_id]['caption']='<i class="fa fa-shopping-cart"></i> '.t('VIEW_CART').'<span id="cart-items-count" class="badge jxCart_quantity"> </span>';
            $more_items[$more_items_id]['parent']=0;
            $more_items[$more_items_id]['classes']='header-button-cart view-shop navbar-link';
            $more_items[$more_items_id]['url']=Vars::mkUrl(MODULE_SHOP.'/checkout/cart');
        ?>

    </head>
    <body class="body-<?=MODULE?>">

        <?php  include(SCRIPT_DIR_THEMES.'/default/index.top.php');  ?>

        <header class="menu-simple">
            <h1><?=CFG::$vars['site']['name']?></h1>
            <div class="logo"><!-- tabindex="1"--><a href="<?=Vars::mkUrl('.')?>"><img class="editable-image-png" src="<?=SCRIPT_DIR_MEDIA?>/images/logo.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>
            <div class="nav-wrap">
                <div class="nav-button"><a id="nav-toggle" href="#!"><span></span></a></div>
                <?php

                    $menu1 = new Menu(1);
                    // script.menu.js (theme default) busca .nav-wrap ul.top_nav
                    $menu1->markup['header'] = '<ul class="top_nav">';
                    $menu1->get_items();
                    $menu1->add_items($more_items);
                    // $menu1->nested_menus=true;
                    $menu1->print_menu(0);
                ?>
            </div>
            <div id="cartPopover">
                            <div id="triangle">&#x25B2;</div>
                            <div class="jxCart_items"></div>
                            <div id="cartData" class="clearfix">
                                <div class="left"><strong><?=t('ITEMS')?>: </strong><span class="jxCart_quantity"></span></div>
                                <div class="right"><strong><?=t('TOTAL')?>: </strong><span class="jxCart_total"></span></div>
                            </div>
                            <div id="popoverButtons" class="clearfix">
                                <a href="<?=Vars::mkUrl(MODULE_SHOP.'/checkout/cart')?>" class=" btn btn-success btn-small NOleft"><?=t('VIEW')?></a>
                                <a href="javascript:;" class="jxCart_checkout btn btn-danger btn-small NOright"><?=t('CHECKOUT')?></a>
                            </div>
            </div><!--End #cartPopover-->
        </header>

        <main id="content">


            <?php if(/*$_ARGS[0]??''=='home'||*/$_ARGS[1]=='home'){?>
            <section id="slider" class="inner">
                <?php include(SCRIPT_DIR_THEMES.'/default/index.slider.php'); ?>
            </section>
            <?php } ?>



            <?php  include(SCRIPT_DIR_MODULE.'/index.php'); ?>
        </main>

        <footer class="menu-simple">
            <?php
                $menu_footer = new Menu(3);
                $menu_footer->get_items();
                $menu_footer->print_menu();      
            ?>
           
            <?php include(SCRIPT_DIR_THEME.'/footer.php'); ?>
            
        </footer>

        <?php 

            widget('drawing');
            //widget('page');
            //widget('alerts'); // type=1 by default
            //widget('alerts', 'type=2' );
            //widget('alerts', 'type=3' );
            //widget('alerts', 'type=4' );
            //widget('news');
            //widget('links');
            //widget('shoutbox');
            //widget('clock'); 
            widget('snowflake');

            HTML::js(SCRIPT_DIR_THEMES.'/default/script.menu.js?ver=1.0.0','defer');
            HTML::js(SCRIPT_DIR_THEME.'/script.js?ver=1.2.0','defer');
            include(SCRIPT_DIR_INCLUDES.'/footer.php'); 
            
        ?>

    </body>
</html>
