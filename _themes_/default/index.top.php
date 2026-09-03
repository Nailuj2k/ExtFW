        <section id="top">
            <div class="inner">
                <div id="search"><i class="fa fa-search"></i><input type="text" id="input-search" placeholder="<?=t('SEARCH')?>" autocomplete="off"><label for="input-search" style="position:fixed;width:0;height:0;overflow:hidden;">Search</label></div>

                <!--<input type="text" placeholder="Buscar" style="/*visibility:hidden;*/">-->
                <span id="top-links">

                <button id="theme-toggle" type="button" aria-label="<?=t('THEME_TOGGLE','Cambiar tema','Toggle theme')?>" title="<?=t('THEME_TOGGLE','Cambiar tema','Toggle theme')?>"><i class="fa fa-moon-o" aria-hidden="true"></i></button>

                <?php
                    if (CFG::$vars['site']['langs']['enabled']){
                        if (  CFG::$vars['site']['langs']['enabled']) //   CFG::$vars['site']['langs']['enabled']){
                        foreach($tr_links as $k => $v){
                            if($k!=$_SESSION['lang']) echo '<a href="'.$v[1].'">'.$v[0].'</a>';                           
                        }
                    }
                    if ($_SESSION['username']){ 
                        echo '<a href="'.Vars::mkUrl('login/profile').'" class="header-button-login navbar-link" title="'.t('LOGGED_IN_AS').' '.$_SESSION['username'].' ('.$_SESSION['userid'].')"><img src="'.Login::getUrlAvatar().'" alt="'.$_SESSION['username'].'" style="display:inline;height:20px;margin:-3px 3px 0 0;vertical-align:middle;"> '.t('MY_ACCOUNT').'</a>';
                        Karma::showUserScore();
                        if($_ACL->hasPermission('pedidos_admin')||$_ACL->hasPermission('edit_items')||$_ACL->userHasRoleName('Administradores'))
                           echo '<a class="navbar-link" href="'.Vars::mkUrl('control_panel').'"><i class="fa fa-cog"></i> '.t('CONTROL_PANEL').'</a>';
                        echo '<a href="'.Vars::mkUrl('login/logout').'" class="header-button-logout navbar-link">'.t('LOGOUT').' <i class="fa fa-sign-out"></i></a>';
                    } else {
                        echo '<a  class="header-button-login navbar-link" href="'.Vars::mkUrl('login').'"><i class="fa fa-user"></i> '.t('LOGIN').'</a>';
                    }
                ?>

                </span>
                <div id="search-results" style="display:none;position:absolute;top:22px;left:0;padding:5px;border:1px solid #ccc;background:#fff;z-index:1000;"></div>
            </div>
        </section>
