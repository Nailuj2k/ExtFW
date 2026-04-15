<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>">
    <head>

        <?php  

            //HTML::css( SCRIPT_DIR_THEME.'/fonts/style.css?ver=2.3.3' );
            HTML::css( SCRIPT_DIR_THEME.'/reset.css?ver=1.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.css?ver=2.3.4' );
            HTML::css( SCRIPT_DIR_THEMES.'/default/style.buttons.css?ver=2.0.5' );
            HTML::css( SCRIPT_DIR_THEMES.'/extfw/style.shop.css?ver=1.0.9' );
            include(SCRIPT_DIR_INCLUDES.'/head.php'); 

        ?>

    </head>
    <body class="body-<?=MODULE?>">

        <?php  include(SCRIPT_DIR_THEMES.'/default/index.top.php');  ?>

        <header class="menu-simple effect-4">
            <h1><?=CFG::$vars['site']['name']?></h1>
            <div class="logo"><!-- tabindex="1"--><a href="<?=Vars::mkUrl('.')?>"><img class="editable-image-png" src="<?=SCRIPT_DIR_MEDIA?>/images/logo.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>
            <?php 

                $menu1 = new Menu(1);
                $menu1->get_items();
                // $menu1->nested_menus=true;
                $menu1->print_menu(0); 

            ?>
        </header>

        <aside>
            <h2>Barra Lateral</h2>
            <nav style="position:relative">
            <?php

                $more_items = array();
                $more_items_id = 1000;
                if (MODULE_SHOP)  {
                    
                    if(CFG::$vars['shop']['enabled']) include(SCRIPT_DIR_MODULES.'/'.MODULE_SHOP.'/menu_categories.php'); 

                    ?>
                    <!--<a class="header-button-cart view-shop navbar-link" href="<?=Vars::mkUrl(MODULE_SHOP.'/checkout/cart')?>"><i class="fa fa-shopping-cart"></i> <?=t('VIEW_CART')?><span id="cart-items-count" class="badge jxCart_quantity"> </span></a>-->
                    <?php 

                    $more_items_id++;
                    $more_items[$more_items_id]['id']=$more_items_id; //+$row['ID'];
                    $more_items[$more_items_id]['name']='cart';
                    //$more_items[$more_items_id]['caption']='<a class="xheader-button-cart view-shop xnavbar-link"><i class="fa fa-shopping-cart"></i> '.t('VIEW_CART').'<span id="cart-items-count" class="badge jxCart_quantity"> </span></a>';
                    $more_items[$more_items_id]['caption']='<i class="fa fa-shopping-cart"></i> '.t('VIEW_CART').'<span id="cart-items-count" class="badge jxCart_quantity"> </span>';
                    $more_items[$more_items_id]['parent']=0;
                    $more_items[$more_items_id]['classes']='header-button-cart view-shop navbar-link';
                    $more_items[$more_items_id]['url']=Vars::mkUrl(MODULE_SHOP.'/checkout/cart');

                    //if(CFG::$vars['shop']['options']['popup']){
                        ?>
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
                        <?php
                    //}
                }

                $menu_aside = new Menu(2);
                $menu_aside->add_items($more_items);
                $menu_aside->get_items();
                $menu_aside->print_menu();      
            ?>            
            </nav>
        </aside>

        <main id="content">
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
            widget('shoutbox');
            //widget('clock'); 
            widget('snowflake');

            HTML::js(SCRIPT_DIR_THEME.'/script.js?ver=1.1.8','defer');
            include(SCRIPT_DIR_INCLUDES.'/footer.php'); 
            
        ?>

    </body>
</html>
