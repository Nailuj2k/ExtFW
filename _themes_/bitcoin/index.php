<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>">
    <head>

        <?php  

            //HTML::css( SCRIPT_DIR_THEME.'/fonts/Ubuntu.css?ver=1.0.0' );
            HTML::css( SCRIPT_DIR_THEME.'/style.css?ver=2.4.6' );
            HTML::css( SCRIPT_DIR_THEMES.'/default/style.buttons.css?ver=1.0.0' );

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
            <nav class="navbar">
                <div class="logo no-grayscale" id="logo"><a href="/tag/bitcoin"><img class="" src="<?=SCRIPT_DIR_MEDIA?>/images/logo.png?ver=<?=CFG::$vars['site']['lastupdate']?>" alt="Logo <?=CFG::$vars['site']['title']?>"></a></div>
                <button class="menu-toggle" aria-label="Abrir menú">
                    <span class="hamburger"></span>
                </button>
                <?php
                    
                    // La clase Menu imprime, con su método print_menu() un elemento <ul> con <li> que pueden tener <ul> anidados. 
                    // Los elementos están definidos en la tabla CLI_ITEM
                    // Cada Item de menu puede tener un <a href> que apunta a una url
                    // Esa url normalmente será una url relativa que apunte a algúna app o mñóduloi dentro de la carpeta _modules_ 
                    // o bien una url absoluta que aopunte a cualquier sitio externo.
                    // Podemos tener tantos menus como queramos.

                    $main_menu = new Menu(1);
                    $main_menu->markup['header'] = '<ul class="menu">'; 
                    $main_menu->markup['item_link']  = '<li class="[CLASSES]" [ARIA]><a href="[URL]">[CAPTION]</a>[CHILDS]</li>';
                    /*
                    $main_menu->markup['item_sep']   = '<li><span>[CAPTION]</span>[CHILDS]</li>';
                    $main_menu->markup['footer']     = '</ul>';
                    $main_menu->markup['separator']  = '';
                    */
                    $main_menu->markup['header_sub'] = '<ul class="submenu">';
                    $main_menu->markup['item_sub']   = '<li class="[CLASSES]" [ARIA]><a href="[URL]">[CAPTION]</a>[CHILDS]</li>';
                    $main_menu->markup['footer_sub'] = '</ul>';
                    

                    $main_menu->nested_menus=false;
                    $main_menu->get_items();
                    $main_menu->print_menu();
                ?>
                <!--<ul class="menu"><li><a href="/">Home</a></li><li><a href="contact">Contacto</a></li></ul>-->

                <!-- --
                <ul class="menu">
                    <li><a href="/">Inicio</a></li>
                    <li><a href="#about">Acerca de</a></li>
                    <li class="has-childs"  aria-haspopup="true" aria-expanded="false">
                        <a href="#">Servicios</a>
                        <ul class="submenu">
                            <li><a href="login/profile">Perfil</a></li>
                            <li><a href="control_panel">Control Panel</a></li>
                            <li class="has-childs"  aria-haspopup="true" aria-expanded="false">
                                <a href="#">Desarrollo</a>
                                <ul class="submenu">
                                    <li><a href="#front-end">Front-End</a></li>
                                    <li><a href="#back-end">Back-End</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><a href="contact">Contacto</a></li>
                </ul>
                -- -->
            </nav>
        </header>
        <aside class="menu-simple">
            <!--<h2>Textos</h2>-->
            <nav>
            <?php
                /*
                //$cur_item_row = Table::sqlQuery("SELECT * FROM CLI_ITEM WHERE item_url= '".(Menu::$current_item == 'home' ? '/' : Menu::$current_item)."'")[0];
                $cur_item_row = Table::sqlQuery("SELECT * FROM CLI_ITEM WHERE item_url= '/'")[0];
                if($cur_item_row)  $main_menu->print_menu($cur_item_row['item_id']);

                $menu_aside = new Menu(3);
                $menu_aside->get_items();
                $menu_aside->print_menu();      
                */
            ?>            
            
            <h2>Entradas recientes</h2>
            <?php   
                echo APP::$shortcodes->do_shortcode('[ajax url="blog/html/type=2"]');
                //echo APP::$shortcodes->do_shortcode('[ajax url="news/html/type=2"]');
            ?>
            </nav>


        </aside>

        <main>
            <?php  include(SCRIPT_DIR_MODULE.'/index.php'); ?>
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

            <p>&copy; 2025 <?=CFG::$vars['site']['name']?>. Todos los berberechos reservados. <?php if(CFG::$vars['site']['tor_url']){ ?><a class="copy-link" href="<?=CFG::$vars['site']['tor_url']?>"><img src="<?=SCRIPT_DIR_IMAGES?>/icons/tor.svg"> <span>Copy Tor URL</span></a><?php } ?></p>

        </footer>

        <?php 

            HTML::js(SCRIPT_DIR_THEME.'/script.js?ver=1.2.0','defer');
            include(SCRIPT_DIR_INCLUDES.'/footer.php');  // Este include puedes quitarlo pero perderas mucha funcionalidad.
            
        ?>
        <style>
            .st-tabs-tab {
                margin: 0 4px -1px 0;
                border-top-left-radius: 5px;
                border-top-right-radius: 5px;
                border: 2px solid #f2a900;
                background-color: #f2a900;
            }

            .st-tabs-tab:hover {
                background-color:#f2a900;
            }

            .st-tabs-tab a{
                color:white;
            }

            .st-tabs-nav {
                border-bottom: 2px solid #f2a900;
            }

            .st-tabs-tab.st-active {
                border-bottom: none;
                background-color: white;
            }

            .st-tabs-tab.st-active a{    color: #444;    cursor:default;}

            .st-tabs-tab.st-active:hover {
                background-color: white;    
            }

            .st-tabs-tab a {
                padding: 5px 10px 4px 10px;
                font-family: var(--font-family-sans-serif);
                font-size: 12px;
            }

        </style>

    </body>
</html>
