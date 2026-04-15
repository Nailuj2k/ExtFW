<header id="site-header" class="header">
        <div class="inner container">
            <nav>
                <div class="logo"><!-- tabindex="1"--><a href="<?=Vars::mkUrl('.')?>"><img  class="editable-image-png" src="<?=SCRIPT_DIR_MEDIA?>/images/logo.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>
                <div class="nav-wrap">
                    <div class="nav-button">
                        <a id="nav-toggle" href="#!" class="">
                            <span class="before"></span>
                        </a>
                    </div>
                    <?php

                        $more_items = array();
                        $more_items_id = 1000;
                    
                        if (MODULE_SHOP)  {
                            
                            if(CFG::$vars['shop']['enabled']) include(SCRIPT_DIR_MODULES.'/'.MODULE_SHOP.'/menu_categories.php'); 
        
                            $more_items_id++;
                            $more_items[$more_items_id]['id']=$more_items_id; //+$row['ID'];
                            $more_items[$more_items_id]['name']='cart';
                            $more_items[$more_items_id]['caption']='<i class="fa fa-shopping-cart"></i> '.t('VIEW_CART').'<span id="cart-items-count" class="badge jxCart_quantity"> </span>';
                            $more_items[$more_items_id]['parent']=0;
                            $more_items[$more_items_id]['classes']='header-button-cart view-shop navbar-link';
                            $more_items[$more_items_id]['url']=Vars::mkUrl(MODULE_SHOP.'/checkout/cart');
        
                            if(CFG::$vars['shop']['options']['popup']){
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
                            }
                        }

                        Menu::$current_item = ($_ARGS[2]??false)?$_ARGS[2]:($_ARGS[1]??false?$_ARGS[1]:$_ARGS[0]);
                        //Menu::$debug = true;
                        $menu1 = new Menu(1);
                        $menu1->markup['header'] = '<ul class="top_nav">'.PHP_EOL; 
                        $menu1->markup['item_link']  = '<li class="nav-item [CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link" href="[URL]">[CAPTION]</a>[CHILDS]</li>'.PHP_EOL;
                        $menu1->markup['item_sep']   = '<li class="nav-item [CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link nolink">[CAPTION]</a>[CHILDS]</li>'.PHP_EOL;
                        $menu1->markup['separator']  = '';
                        $menu1->markup['footer']     = '</ul>';
                        $menu1->markup['header_sub'] = '<ul class="sub-nav" id="menu-[NAME]">'.PHP_EOL;
                        $menu1->markup['item_sub']   = '<li class="[CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link" href="[URL]">[CAPTION]</a>[CHILDS]</li>'.PHP_EOL;
                        $menu1->markup['footer_sub'] = '</ul>';
                        $menu1->get_items();
                        $menu1->add_items($more_items);
                        $menu1->nested_menus=true;
                        $menu1->print_menu(0); //,Menu::$current_item);      
                    ?>
                </div>
            </nav>
        </div>
    </header>
