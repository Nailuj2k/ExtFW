<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>" data-theme="light">
    <head>

        <?php  

            //HTML::css( SCRIPT_DIR_THEME.'/fonts/Ubuntu.css?ver=1.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.css?ver='.VERSION );
            HTML::css( SCRIPT_DIR_THEME.'/style.menu.css?ver='.VERSION );
            HTML::css( SCRIPT_DIR_THEMES.'/default/style.buttons.css?ver='.VERSION );
            // Podemos omitir este include y hacer nuestro propio <head>, cosa que sería una gilipollez. 
            include(SCRIPT_DIR_INCLUDES.'/head.php'); 

        ?>

    </head>
    <body class="body-<?=MODULE?> body-theme-<?=THEME?>">

        <?php
            // Esta include, opcional, pone una barra superior para login, logout, profile, etc.  
            include(SCRIPT_DIR_THEMES.'/default/index.top.php');
        ?>
        
        <header>
            <!--

            <h1><?=CFG::$vars['site']['name']?></h1>
            -->
            <?php if(1==2){ ?>
            <nav class="navbar">
                <div class="logo no-grayscale" id="logo"><a href="/tag/bitcoin"><img class="" src="<?=SCRIPT_DIR_MEDIA?>/images/logo.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>
                <button class="menu-toggle" aria-label="Abrir menú">
                    <span class="hamburger"></span>
                </button>
                <?php 
                                      
                    $main_menu = new Menu(1);
                    $main_menu->markup['header'] = '<ul class="menu">'; 
                    $main_menu->markup['item_link']  = '<li class="[CLASSES]" [ARIA]><a href="[URL]">[CAPTION]</a>[CHILDS]</li>';
                    $main_menu->markup['header_sub'] = '<ul class="submenu">';
                    $main_menu->markup['item_sub']   = '<li class="[CLASSES]" [ARIA]><a href="[URL]">[CAPTION]</a>[CHILDS]</li>';
                    $main_menu->markup['footer_sub'] = '</ul>';
                   //$main_menu->nested_menus=false;
                    $main_menu->get_items();
                    $main_menu->print_menu();
                ?>

            </nav>
            <?php } ?>


            <nav class="navbar">
                <div class="logo"><!-- tabindex="1"--><a href="<?=Vars::mkUrl('.')?>"><img  class="editable-image-png" src="<?=SCRIPT_DIR_MEDIA?>/images/logo.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>
                <div class="nav-wrap">
                    <div class="nav-button">
                        <a id="nav-toggle" href="#!" class="">
                            <span class="before"></span>
                        </a>
                    </div>



                <?php
                    //Menu::$current_item = ($_ARGS[1]=='bodega'||$_ARGS[1]=='do')?($_ARGS[2]?$_ARGS[2]:$_ARGS[1]):$_ARGS[0];
                    Menu::$current_item = ($_ARGS[2]??false)?$_ARGS[2]:($_ARGS[1]??false?$_ARGS[1]:$_ARGS[0]);
                    Menu::$debug = true;
                    $menu1 = new Menu(1);
                    $menu1->markup['header'] = '<ul class="menu top_nav">'.PHP_EOL; 
                    $menu1->markup['item_link']  = '<li class="nav-item [CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link" href="[URL]">[CAPTION]</a>[CHILDS]</li>'.PHP_EOL;
                    $menu1->markup['item_sep']   = '<li class="nav-item [CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link nolink">[CAPTION]</a>[CHILDS]</li>'.PHP_EOL;
                    $menu1->markup['separator']  = '';
                    $menu1->markup['footer']     = '</ul>';
                    $menu1->markup['header_sub'] = '<ul class="sub-nav" id="menu-[NAME]">'.PHP_EOL;
                    $menu1->markup['item_sub']   = '<li class="[CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link" href="[URL]">[CAPTION]</a>[CHILDS]</li>'.PHP_EOL;
                    $menu1->markup['footer_sub'] = '</ul>';
                    $menu1->get_items();
                    $menu1->nested_menus=true;
                    $menu1->print_menu(0); //,Menu::$current_item);      
                ?>
                </div>
            </nav>




        </header>

        <main>

            <div id="noxtr" class="noxtr-app">
                <h1 id="noxtr-title"><?=MODULE?></h1>
                <?php
                    
                    $avatar_image = SCRIPT_DIR_IMAGES.'/avatars/avatar.gif';
                    if ($_SESSION['valid_user']) $avatar_image = Login::getUrlAvatar();
                    
                    $banner_image = SCRIPT_DIR_MEDIA.'/nostr/banners/banner_'.$_SESSION['userid'].'.jpg';
                  //if (!file_exists($banner_image)) $banner_image = SCRIPT_DIR_MEDIA.'/nostr/banners/banner-default.jpg';
                    if (!file_exists($banner_image)) $banner_image = SCRIPT_DIR_MEDIA.'/nostr/banners/banner-default.jpg?ver=1.0.1';

                ?>
                
                <img id="noxtr-banner" class="editable-banner noxtr-banner theme-noxtr-banner" src="<?=$banner_image?>" alt="Banner">
                <a href="/login/profile">    
                <img id="noxtr-avatar" class="editable-avatar theme-noxtr-avatar" src="<?=$avatar_image?>"></a>

                <?php  include(SCRIPT_DIR_MODULE.'/index.php'); ?>
            </div>

        </main>

        <footer class="menu-simple">

            <?php
                $menu_footer = new Menu(2);
                $menu_footer->get_items();
                $menu_footer->print_menu();      
            ?>
            <?php                 
            CFG::$vars['site']['tor_url'] = 'http://7fj3zzrwhimzsenmlvcukqkfdv3etgpy7du6oy4oroucbhsukr467myd.onion/';
            ?>

            <div class="logo" id="logo-footer"><a href="/"><img src="<?=SCRIPT_DIR_MEDIA?>/images/logo_footer.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>

            <p>&copy; 2025 <?=CFG::$vars['site']['name']?>. Todos los berberechos reservados. <?php if(CFG::$vars['site']['tor_url']){ ?><a class="copy-link" href="<?=CFG::$vars['site']['tor_url']?>"><img src="<?=SCRIPT_DIR_IMAGES?>/icons/tor.svg"> <span>Copy Tor URL</span></a><?php } ?> <span class="noxtr-theme-switcher noxtr-theme-switcher-footer"><button id="noxtr-theme-toggle" class="noxtr-theme-toggle" type="button" aria-label="Cambiar tema" title="Cambiar tema"><i class="fa fa-moon-o" aria-hidden="true"></i></button></span></p>

        </footer>

        <?php 

            HTML::js(SCRIPT_DIR_THEME.'/script.menu.js?ver='.VERSION,'defer');
            include(SCRIPT_DIR_INCLUDES.'/footer.php');  // Este include puedes quitarlo pero perderas mucha funcionalidad.
            
        ?>
    </body>
</html>
