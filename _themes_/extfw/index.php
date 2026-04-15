<!DOCTYPE html>
<html lang="<?=$_SESSION['lang']?>" data-theme="light">
<head>
    <?php 
        HTML::css( SCRIPT_DIR_THEME.'/reset.css?v='.VERSION );
        HTML::css( SCRIPT_DIR_THEME.'/style.css?v='.VERSION ); 
        HTML::css( SCRIPT_DIR_THEME.'/style.menu.css?ver=2.3.0' );
        HTML::css( SCRIPT_DIR_THEME.'/style.shop.css?ver=2.3.0' );
        include(SCRIPT_DIR_INCLUDES.'/head.php'); 
    ?>
</head>
<body class="body-<?=MODULE?>">
    
    <?php         
        include(SCRIPT_DIR_THEME.'/index.top.php'); 
        include(SCRIPT_DIR_THEME.'/index.header.php'); 
    ?>>
        
    <aside>
        <?php $m = new Menu(3); $m->get_items(); $m->print_menu(); ?>
    </aside>

    <main>
        <?php include(SCRIPT_DIR_MODULE.'/index.php'); ?>
    </main>

    <footer>
        <?php $m = new Menu(2); $m->get_items(); $m->print_menu(); ?>
        <p>&copy; <?=date('Y')?> <?=CFG::$vars['site']['name']?>  <button id="theme-toggle" onclick="toggleTheme()" aria-label="Toggle dark mode">◐</button>  </p>
    </footer>

    <?php 
        HTML::js(SCRIPT_DIR_THEME.'/script.js?ver=1.1.8','defer');
        HTML::js(SCRIPT_DIR_THEME.'/script.menu.js?ver=1.1.6','defer');
        include(SCRIPT_DIR_INCLUDES.'/footer.php'); 
    ?>

</body>
</html>