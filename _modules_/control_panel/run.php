<div class="inner" id="inner_control_panel"> 

<?php

    $documents = true;
    /*
    include_once(SCRIPT_DIR_LIB.'/file_viewer/pdf_viewer.php'); 
    include_once(SCRIPT_DIR_LIB.'/file_viewer/epub_viewer.php'); 
    include_once(SCRIPT_DIR_LIB.'/file_viewer/txt_viewer.php'); 
    include_once(SCRIPT_DIR_LIB.'/file_viewer/url_viewer.php'); 
    include_once(SCRIPT_DIR_LIB.'/file_viewer/json_viewer.php');
    */
?>

<?php

$ie = ( (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) > 0);

if($ie){

    ?>
    <div class="alert" style="margin:40px 20px 40px 20px;">
    <b>Esta usted usando Internet Explorer.</b><br />Para el correcto funcionamiento de esta aplicación se necesita un navegador compatible.<br />Contacte con su servicio de informática.
    </div>
    <?php 

}else if(!$_SESSION['valid_user']){

    $message_error = '<b>'.t('ACCESS_DENIED').'</b><br />'.'Necesita estar identificado en el sistema para utilizar esta aplicación';
    ?>
    <div class="alert"  style="margin:80px 20px 80px 20px;"><p style="margin:20px auto;"><?=$message_error?></p></div>
    <div style="text-align:center;margin:60px 0 100px 0;"><a class="btn btn-large btn-primary" style="color:white;" href="<?=Vars::mkUrl('login')?>"> &nbsp; login <i class="fa fa-chevron-right fa-inverse"></i> &nbsp; </a></div>
    <?php 

}else if($_ARGS[1]=='new'){

     if(Root() && $_ARGS[2] ){
        
         $copy_from =  $_ARGS[3] ? Str::SanitizeName($_ARGS[3]) : '_template_';
         $new_module = Str::SanitizeName($_ARGS[2]);
        
         if (!file_exists(SCRIPT_DIR_MODULES.'/'.$copy_from)){
             echo 'El módulo '.$copy_from.' NO existe. Puede que no esté instalado.';
         }else if (file_exists(SCRIPT_DIR_MODULES.'/'.$new_module)){
             echo 'El módulo '.$new_module.' ya existe';
         }else if(strlen($new_module)>3){
             include(SCRIPT_DIR_MODULE.'/functions.php');
             copyr(SCRIPT_DIR_MODULES.'/'.$copy_from,SCRIPT_DIR_MODULES.'/'.$new_module);
             if (file_exists(SCRIPT_DIR_MODULES.'/'.$new_module))
                 echo 'El módulo '.$new_module.' ha sido creado. <a class="btn" href="edit/_modules_/'.$new_module.'">Editar</a>';
             else
                 echo 'Error: El módulo '.$new_module.' no se ha podido añadir.';
         }else{
             echo 'El módulo '.$new_module.' no se puede crear';
         }
     }else{
         echo '404';
     }


}else if($_ARGS[1]=='install'){

    //Table::sqlExec('DROP TABLE CFG_EXTRA_FIELDS');
    include(SCRIPT_DIR_MODULE.'/INSTALL.php');
  
}else{
 
    $selected_table = TB_USER; 

    $php_ver = phpversion();
    if (strnatcmp($php_ver ,'8.2.8') >= 0){
    }else{
        $message_warning = 'Su versión de php es la '.$php_ver.' Se recomienda la versión 8.2 o superior para activar todas las características.';
    }

    $cp_tabs = file_exists( SCRIPT_DIR_THEME.'/control_panel_tabs_buttons.php') && file_exists( SCRIPT_DIR_THEME.'/control_panel_tabs_contents.php');
    if($cp_tabs) include(SCRIPT_DIR_THEME.'/control_panel_tabs_buttons.php');
    $cp_cfg_tabs = file_exists( SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_buttons.php') && file_exists( SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_contents.php');
    if($cp_cfg_tabs) include(SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_buttons.php');

    if($_ARGS[1]=='shop'){
        
        if( CFG::$vars['shop']['enabled'] && (Administrador()||$_ACL->hasPermission('pedidos_admin')||$_ACL->hasPermission('productos_admin')) ) { 
	        ?><p>Moved to <a href="shop/admin">shop/admin</a></p><?php
        }else{
            ?><h3>Access denied</h3><?php
        } 


    }else if(Administrador()) {

        ?> 
        <div id="tabs" class="form-tabs" data-simpletabs>                    
            <ul>

                <li><a href="#tab-5"><i class="fa fa-users"> </i> <?=t('ACCESS_CONTROL','Control de accesos')?></a></li>
                <li><a href="#tab-pages"><i class="fa fa-file-o"> </i> <?=t('PAGES','Páginas')?></a></li>

                <?php  if(CFG::$vars['modules']['banners']['enabled']){ ?>
                <li><a href="#tab-6"><i class="fa fa-window-restore"> </i> Banners</a></li>
                <?php }?>

                <?php  if(CFG::$vars['modules']['qrcodes']['enabled']){ ?>
                <li><a href="#tab-qrcode"><i class="fa fa-qrcode"> </i> QrCodes</a></li>
                <?php }?>

                <?php  if(CFG::$vars['modules']['inscripciones']['enabled']){ ?>
                <li><a href="#tab-inscripciones"><i class="fa fa-gift"> </i> Inscripciones</a></li>
                <?php }?>

                <?php  if (CFG::$vars['modules']['newsletter']['enabled'] && $_ACL->hasPermission('newsletters_admin')) {?>
                <li><a href="#tab-cfg-newsletter"><i class="fa fa-newspaper-o"></i> Newsletter </a></li>
                <?php }?>

                <?php if (CFG::$vars['modules']['slider']['enabled'] && $_ACL->hasPermission('sliders_admin')) {?>
                <li><a href="#tab-cfg-slider"><i class="fa fa-window-restore"></i> Slider</a></li>
                <?php }?>

                <?php if($cp_tabs){?> 
                <?php echo $cp_tabs_buttons; ?>
                <?php }?>

                <li><a href="#tab-4"><i class="fa fa-cog"> </i> <?=t('CONFIGURATION','Configuración')?></a></li>
                <?php if(Root()){?> 
                <li><a href="#tab-cfg-updates"><i class="fa fa-archive"></i> <?=t('UPDATES','Actualizaciones')?></a></li>
                <?php if(CFG::$vars['db']['cache']) { ?>
                <li><a href="#tab-logs">Logs</a></li>
                <?php }?>
                <?php }?>
        
                <?php  if(CFG::$vars['shop']['enabled']){ ?>
                <li><a href="control_panel/shop"><i class="fa fa-shopping-bag"></i> <?=t('SHOP','Tienda')?></a></li>
                <?php }?>
            </ul>

            <?php  if(CFG::$vars['modules']['inscripciones']['enabled']){ ?>
            <div id="tab-inscripciones">
                <?php  Table::show_table('CLI_INSCRIPCIONES');      ?>
            </div>
            <?php }?>

            <?php if (CFG::$vars['modules']['slider']['enabled']){?>                  
            <?php if ($_ACL->hasPermission('sliders_admin')) {?>
            <div id="tab-cfg-slider"><?php Table::show_table('CFG_SLIDER'); ?></div>
            <?php }?>
            <?php }?>

            <?php  if (CFG::$vars['modules']['newsletter']['enabled']){?>                  
             <?php if ($_ACL->hasPermission('newsletters_admin')) {?>
             <div id="tab-cfg-newsletter"><?php  /* Table::show_table('CFG_NEWSLETTER');*/  ?><p>Work in progress</p></div>
            <?php }?>
            <?php }?>

            <?php if($cp_tabs){?> 
            <?php if($cp_tabs) include(SCRIPT_DIR_THEME.'/control_panel_tabs_contents.php'); ?>
            <?php }?>

            <div id="tab-5">
                <div id="tabs-acl" class="form-tabs" data-simpletabs>
                    <ul>
                        <li><a href="#tab-acl-users"><?=t('USERS','Usuarios')?></a></li>
                        <li><a href="#tab-acl-perms"><?=t('PERMISSIONS','Permisos')?></a></li>
                        <li><a href="#tab-acl-roles">Roles</a></li>    
                        <li><a href="#tab-acl-challenges">Challenges</a></li>    
                    </ul>
                    <div id="tab-acl-users">
                        <div style="text-align:right;display:block;margin-bottom: -10px;padding: 8px;">
                        <?php
                        if(Administrador()  ){  
                          $selected_id_role = $_SESSION['_CACHE'][TB_USER]['filterindex'];
                          //$userACL = new ACL($id);
                          //$userACL->buildACL();
                          //$aRoles = $userACL->getAllRoles('full');   filtrable=1 AND i
                          
                          $strSQL  = "SELECT role_id, role_name FROM ".TB_ACL_ROLES;
                          if ($_SESSION['userid']>1) $strSQL .= " WHERE filtrable=1 ";
                          $strSQL .= " ORDER BY role_name ASC";
                          $aRoles = Table::sqlQuery($strSQL);
                          $html = '<div style="display:inline-block;z-index:1;margin-right:12px;">Filtrar por grupo: <select Xmultiple Xsize="8" name="customfilteruser" id="customfilteruser"> <option value="ALL">'.t('ALL','Todos').'</option>';
                          foreach ($aRoles as $role){
                            $html .= ' <option value="'.$role['role_id'].'"'.($selected_id_role==$role['role_id']?' SELECTED':'').'>'.$role['role_name'].'</option>';
                          }
                          $html .= '</select></div>'; 
                          echo $html;
                        }
                        ?>
                        <a class="btn btn-success" id="btn-download-csv-userss" href="<?=MODULE?>/csv/users"> <i class="fa fa-file-excel-o"></i> <?=t('DOWNLOAD_CSV','Descargar CSV')?> </a>
                        </div>
                        <?php 
                             Table::show_table(TB_USER);    
     
                             $tab_user = new Tabs('tab_user');
                             
                             $tab_user->addTab('Archivos','tab_user_files');
                             $tab_user->addTab('Direcciones','tab_user_addresses');                         
                             $tab_user->addTab('Dispositivos','tab_user_keys');
                             $tab_user->addTab('Timestamp','tab_user_timestamp');
                             
                             $tab_user->begin();
                             
                             $tab_user->beginTab('tab_user_files');
                             Table::show_table('CLI_USER_FILES'); 
                             $tab_user->endTab();
                             
                             $tab_user->beginTab('tab_user_addresses');
                             Table::show_table('CLI_USER_ADDRESSES');      
                             $tab_user->endTab();

                             $tab_user->beginTab('tab_user_keys');
                             Table::show_table('CLI_USER_KEYS');      
                             $tab_user->endTab();

                             $tab_user->beginTab('tab_user_timestamp');
                             Table::show_table('CLI_USER_TIMESTAMP');      
                             $tab_user->endTab();

                             $tab_user->end();

                        ?>
                        </div>
                    <div id="tab-acl-perms"><?php  Table::show_table(TB_ACL_PERMISSIONS); ?></div>
                    <div id="tab-acl-roles"><?php  Table::show_table(TB_ACL_ROLES);       ?></div>	    
                    <div id="tab-acl-challenges"><?php  Table::show_table('CLI_AUTH_CHALLENGES');       ?></div>	    

                    
                </div>    
            </div>

            <div id="tab-pages">
                <div id="tabs-pages" class="form-tabs" data-simpletabs>
                    <ul>
                        <li><a href="#tab-pages-menus"><?=t('MENUS','Menús')?></a></li>
                        <li><a href="#tab-pages-pages"><?=t('PAGES','Páginas')?></a></li>
                        <li><a href="#tab-clicks">Clicks</a></li>
                    </ul>
                    <div id="tab-pages-menus">
                        <div class="info" style="display:block;margin-top:10px;padding: 8px 8px 8px 50px;font-size:12px !important;">
                          <a style="float:right;" id="write-sitemap-file" class="btn btn-primary"> <?=t('CREATE_OR_UPDATE_SITEMAP_XML','Crear o actualizar <b>sitemap-xml</b>')?> </a>
                          <?=t('SITEMAP_XML_INFO','En <i>sitemaps.xml</i> estarán incluidas las urls cuyo campo "público" esté marcado. También se actualizará <i>robots.txt</i>. Si <i>options.submit_sitemap</i> está activada en Ajustes se notificará a Google y algún otro buscador.')?> [<i>options.submit_sitemap</i> <?=t('IS','está')?> <b><?=CFG::$vars['options']['submit_sitemap']?'activado':'desactivado'?></b>]
                        </div>
                        <?php  
                        Table::show_table(TB_ITEM);      
                        //print_r(Menu::$menus);
                        ?>  
                    </div>
                    <div id="tab-pages-pages">
                        <?php  
                        Table::show_table('CLI_PAGES'); 
                        Table::show_table('CLI_PAGES_FILES'); 
                        ?>
                    </div>
                    <div id="tab-clicks">
                        <?php  
                        Table::show_table('CFG_CLICKS'); 
                        ?>
                    </div>
                </div>    
            </div>        

            <?php  if(CFG::$vars['modules']['banners']['enabled']){ ?>
            <div id="tab-6">

                <div style="text-align:right;display:block;margin-bottom: -20px;padding: 8px;">
                    Desde: <input type="date" class="banners-date-pick " id="banners-date_from" name="banners-date_from" autocomplete="off" value="2022-01-01" size="10"> &nbsp; &nbsp; &nbsp;
                    Hasta: <input type="date" class="banners-date-pick " id="banners-date_to"   name="banners-date_to"   autocomplete="off" value="2022-12-31" size="10"> &nbsp; &nbsp; &nbsp;
                    <a class="btn btn-success" id="btn-download-csv-banners" href="<?=MODULE?>/csv/banners"> <i class="fa fa-file-excel-o"></i> <?=t('DOWNLOAD_CSV','Descargar CSV')?> </a>
                </div>

                <div id="tabs-6-1" class="form-tabs" data-simpletabs>
                    <ul>
                        <li><a href="#tab-6-1-1">Banners</a></li>
                        <li><a href="#tab-6-1-2"><?=t('TYPES','Tipos')?></a></li>
                    <!--<li><a href="#tab-6-1-3">Ubicaciones</a></li>-->
                    <!--<li><a href="#tab-6-1-4">Seguimiento</a></li>-->
                    </ul>
                    <div id="tab-6-1-1"><?php  Table::show_table('GES_BANNERS');            ?></div>
                    <div id="tab-6-1-2"><?php  Table::show_table('GES_BANNERS_TYPES');      ?></div>
                <!--<div id="tab-6-1-3"><?php  /* Table::show_table('GES_BANNERS_UBICATIONS');*/ ?></div>-->
                <!--<div id="tab-6-1-4"><?php  /* Table::show_table('GES_BANNERS_LOG');*/ ?></div>-->
                </div>    
            </div>
            <?php }?>      

            <?php  if(CFG::$vars['modules']['qrcodes']['enabled']){ ?>
            <div id="tab-qrcode">
                <?php Table::show_table('CLI_QRCODES'); ?>
            </div>
            <?php }?>      
        
            <div id="tab-4">
                <div id="tabs-cfg" class="form-tabs" data-simpletabs>
                    <ul>

                        <?php if($cp_cfg_tabs){?> 
                        <?php echo $cp_cfg_tabs_buttons; ?>
                        <?php }?>

                        <?php if(Administrador()){?>
                            <?php  if((CFG::$vars['shop']['enabled']&&CFG::$vars['shop']['field']['tags'])||CFG::$vars['site']['tags']['enabled']){ /* TAGS */ ?>
                                <li><a href="#tab-cfg-tags"><i class="fa fa-tags"></i> Tags</a></li>
                            <?php }?>
                            <?php  if((CFG::$vars['shop']['enabled']&&CFG::$vars['shop']['field']['categorie'])||CFG::$vars['site']['categories']['enabled']){ ?>
                                <li><a href="#tab-cfg-categories"><i class="fa fa-list"></i> <?=t('CATEGORIES','Categorías')?></a></li>
                            <?php }?>
                            <?php  if(CFG::$vars['site']['badges']['enabled']){ ?>
                                <li><a href="#tab-cfg-badges"><i class="fa fa-bookmark-o"></i> Badges</a></li>
                            <?php }?>
                        <?php }?>

                        <?php  if(CFG::$vars['users']['field']['country']&&CFG::$vars['users']['field']['state']){ ?>
                        <li><a href="#tab-cfg-destinos"><i class="fa fa-home"></i> <?=t('LOCATIONS','Ubicaciones')?></a></li>
                        <?php }?>
                        <?php if(Administrador()){?>
                        <?php  if(/*1==1||*/Root() || CFG::$vars['site']['langs']['enabled']){ ?>
                        <li><a href="#tab-cfg-i18n"><i class="fa fa-language"></i> i18n</a></li>
                        <?php }?>
                        <?php
                            $cp_cfg_tabs = file_exists( SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_buttons.php') && file_exists( SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_contents.php');
                            if($cp_cfg_tabs) {
                                include(SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_buttons.php');
                            }
                        ?>
                        <li><a href="#tab-cfg-providers"><i class="fa fa-files"></i> <?=t('FILE_TYPES','Tipos archivo')?></a></li>
                        <li><a href="#tab-cfg-extra-fields"><?=t('EXTRA_FIELDS','Campos extra')?></a></li>
                        <li><a href="#tab-cfg-tpl"><?=t('TEMPLATES','Plantillas')?></a></li>
                        <li><a href="#tab-cfg-cfg"><?=t('SETTINGS','Ajustes')?></a></li>
                        <li><a href="#tab-cfg-design"><?=t('DESIGN','Diseño')?></a></li>
                        <li><a href="#tab-cfg-log">Log</a></li>
                        <?php if(str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache')){ ?>
                        <li><a href="#tab-cfg-htaccess">.htaccess</a></li>
                        <?php } ?>
                        <li><a href="#tab-cfg-cfgfile">configuration.php</a></li>
                        <?php if(Root()){?>
                        <li><a href="#tab-cfg-ssh">SSH</a></li>
                        <?php }?>
                        <?php if($_ARGS['debug']){?> 
                            <li><a href="#tab-cfg-debug">Debug</a></li>
                        <?php }?>
                        <?php }?>
                    </ul>

                    <?php if($cp_cfg_tabs) include(SCRIPT_DIR_THEME.'/control_panel_cfg_tabs_contents.php'); ?>

                    <?php  if((CFG::$vars['shop']['enabled']&&CFG::$vars['shop']['field']['tags'])||CFG::$vars['site']['tags']['enabled']){ ?>
                        <div id="tab-cfg-tags"><?php Table::show_table('CLI_TAGS'); ?></div>
                    <?php } ?>

                    <?php  if((CFG::$vars['shop']['enabled']&&CFG::$vars['shop']['field']['categorie'])||CFG::$vars['site']['categories']['enabled']){ ?>
                        <div id="tab-cfg-categories"><?php Table::show_table('CLI_CATEGORIES'); ?></div>
                    <?php } ?>

                    <?php  if(CFG::$vars['site']['badges']['enabled']) { ?>
                        <div id="tab-cfg-badges"><?php Table::show_table('CLI_BADGES'); ?></div>
                    <?php } ?>


                    <?php  if(CFG::$vars['users']['field']['country']&&CFG::$vars['users']['field']['state']){ ?>
                        <style>#T-CFG_PAIS {width:350px;}#T-CFG_PROVINCIA {/*width:550px;border:0px solid green;*/}</style>
                        <div id="tab-cfg-destinos"> 
                            <div style="display: flex;">
                            <?php
                            Table::show_table('CFG_PAIS');
                            Table::show_table('CFG_PROVINCIA'); 
                            if(CFG::$vars['users']['field']['city']) Table::show_table('CFG_MUNICIPIO'); 
                            if(CFG::$vars['users']['field']['county']) Table::show_table('CFG_LOCALIDAD'); 
                            ?>
                            </div>
                        </div>
                    <?php }?>

                    <?php  if(/*1==1 ||*/ Root() ||  CFG::$vars['site']['langs']['enabled']){ ?>
                        
                        
                        
                        <div id="tab-cfg-i18n"> 
                            <div id="tabs-cfg-i18n-langs" class="form-tabs" data-simpletabs>
                                <ul>
                                    <li><a href="#tab-cfg-i18n-langs"><?=t('LANGUAGES','Idiomas')?></a></li>
                                    <li><a href="#tab-cfg-i18n-translations"><?=t('TRANSLATION','Traduccion')?></a></li>
                                </ul>
                                <div id="tab-cfg-i18n-langs">
                                    <?php  Table::show_table(TB_LANG);            ?>
                                </div>
                                <div id="tab-cfg-i18n-translations">
                                    <div class="info" style="margin:10px 0 -10px 0;"><?=t('TRANSLATION_INFO','Para cada idioma existe un archivo con las traducciones de los textos base. El botón <b>Guardar traducción</b> actualiza dicho archivo a partir de las traducciones en éstas tablas. De esta manera el sistema no necesita acceder cada vez a la base de datos para obtenerlas. Cuando en el código hay nuevos textos sin traducir, por ejemplo, porque hayan habido actualizaciones o porque se haya activado algún otro idioma, podemos poner el modo <i>Auto</i> y dichos textos base se añadirán automáticamente a la tabla para que puedan ser traducidos.')?></div>
                                    <?php
                                        Table::show_table(TB_STR);  
                                        Table::show_table(TB_CC);     
                                    ?>
                                    <div class="buttons">
                                        <a class="btn btn-secondary" id="write-cc-file" title=""><?=t('TRANSLATION_SAVE','Guardar traducción')?> <b><?=$t->getFieldValue('SELECT lang_name FROM '.TB_LANG.' WHERE lang_cc=\''.$_SESSION['lang'].'\'')?></b></a>
                                        <a class="btn btn-secondary" id="set-mode-auto" title=""><?=t('TRANSLATION_MODE')?> <b><?=$_SESSION['translate']?t('YES'):t('NO')?></b></a>
                                    </div>
                                </div>	    
                            </div>    
                        </div>

                       
                    <?php }  /* if(CFG::$vars['site']['langs']['enabled']){ */ ?>

                    <div id="tab-cfg-extra-fields"><?php  Table::show_table('CFG_EXTRA_FIELDS'); ?></div>
                    <div id="tab-cfg-providers"><?php  Table::show_table('CFG_FILES_PROVIDER'); ?></div>
                    <div id="tab-cfg-cfg"><?php  Table::show_table(TB_CFG); ?></div>
                    <div id="tab-cfg-design">
                               
                        <div class="info" style="margin:10px 5px 10px 5px;">
                            <p><h3><?=t('LOGOS_ICONS','Logos, iconos.')?></h3>
                            <p>
                            <?=t('FAVICON_INFO','Un <i>favicon</i> es una imagen que sirve para identificar una web en listados de favoritos. Pulsando <i>Actualizar</i> se genera un conjunto de iconos e imágenes que se usarán en accesos diractos o como iconos en pantallas de móvil, tablets, escritorios de sistemas operativos, etc.')?><br>
                            <?=t('LOGO_INFO','Logo, Logo email y Logo footer son para lo que su propio nombre indica.')?><br>
                            <?=t('IMAGE_FORMAT_INFO','Para cualquiera de las opciones debería usar imágenes en formto PNG o WEBP con transparencia alfa.')?>
                            </p>
                        </div>  

                        <div class="container">

                            <div class="item"><b>Favicon</b><br>
                                <div id="drop_file_zone_favicon" class="drop_file_zone">
                                    <div class="drag_upload_file"><span class="help"><br /><?=t('DRAG_HERE_PNG','Arrastre aquí una imagen PNG')?><br /><a class="btn btn-primary btn-small" style="cursor:pointer !important;z-index:1000;" id="create_favicons"><?=t('UPDATE','Actualizar')?></a></span></div>
                                </div>
                            </div>

                            <div class="item"><b>Logo</b><br>
                                <div id="drop_file_zone_logo" class="drop_file_zone">
                                    <div class="drag_upload_file"><span class="help"><br /><?=t('DRAG_HERE_PNG')?></span></div>
                                </div>
                            </div>

                            <div class="item"><b>Logo email</b><br>
                                <div id="drop_file_zone_logo_email" class="drop_file_zone">
                                    <div class="drag_upload_file"><span class="help"><br /><?=t('DRAG_HERE_PNG')?></span></div>
                                </div>
                            </div>
                            <div class="item"><b>Logo footer</b><br>
                                <div id="drop_file_zone_logo_footer" class="drop_file_zone">
                                    <div class="drag_upload_file"><span class="help"><br /><?=t('DRAG_HERE_PNG')?></span></div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div id="tab-cfg-tpl"><?php  Table::show_table(TB_TPL); ?></div>

                    <div id="tab-cfg-log">

                        <div id="tabs-cfg-log" class="form-tabs" data-simpletabs>
                            <ul>
                                <li><a href="#tab-cfg-log-live">Live</a></li>
                                <li><a href="#tab-cfg-log-log">Events Log</a></li>
                            </ul>

                            <div id="tab-cfg-log-live">
                                <?php if(CFG::$vars['log']){ ?> 
                                <div style="text-align:right;padding: 5px 5px 0 5px;">
                                    <a class="btn btn-primary" id="start-stop-log-live"><?=t('START')?>&nbsp; <i class="fa fa-play"></i></a>
                                </div>  

                                <div id="log-lines"></div>
                                <?php } else { ?> 
                                    <p><?=t('LOG_DISABLED','Log está desactivado. Puede activarlo en Configuración - configuration.php - log')?></p>
                                <?php } ?>
                            </div>	    
                            <div id="tab-cfg-log-log">
                                <?php  Table::show_table(TB_LOG); ?>
                            </div>
                        </div>  

                    </div>

                    <?php if(str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache')){ ?>
                    <div id="tab-cfg-htaccess">
                        <?php  /*include(SCRIPT_DIR_MODULES.'/edit/edit_ware.class.php');*/ /** CHECK */?>
                        <div id="NOeditor_htaccess" name="content" style="margin-top:10px;width:100%;min-height:500px;"><textarea id="editor_htaccess" style="width:100%;height:500px;"><?php  /**echo EDIT_ware::read_file_text('.htaccess');**/  ?></textarea></div>
                        <div class="buttons-edit">
                        <a id="btn-htaccess" class="btn btn-success"><?=t('EDIT','Editar')?> .htaccess</a>
                        <a id="btn-htaccess-cancel" style="display:none;" class="btn btn-danger"><?=t('REVERT_EDITION','Revertir edición')?></a>
                        </div>
                    </div>
                    <?php } ?>
                    <div id="tab-cfg-cfgfile"">
                              <!-- render -->
                    </div>
                    <?php if(Root()){?>
                    <div id="tab-cfg-ssh">
                              <!-- render -->

                       <!-- 
                        <pre id="result" style="display:none;/*width:100%;*/height:370px;overflow:auto;font-size:0.8em;line-height:0.9em;background-color:midnightblue;color:yellow;padding:4px;">
                        </pre>
                        -->

                        <style>
                        .cmd      {background-color:black;  color:white;padding:10px;margin:0px 10px 0 10px;min-height:350px;padding:0px !important;}
                        #cmd-output  {background-color:black;  color:white;  color: #ccffff;font-size:0.8em;line-height:1.2em;  margin:0;  height: 350px;overflow: auto;}
                        .prompt   {background-color:black;  color:white;           padding: 0 0 0 5px; margin: 0px 10px 10px 10px;  border-top: 1px solid #444;}
                        .prompt input {background-color:black;color:white;border:0px;width:85%;padding:4px;display:inline;}
                        ::-webkit-scrollbar { background: #00000050;}
                        </style>

                        <div class="cmd"><pre id="cmd-output"><?php

                        echo 'owner:'.get_current_user().PHP_EOL;
                        echo 'group:'.print_r(posix_getgrgid(filegroup($_SERVER['DOCUMENT_ROOT'].'/index.php'))['name'],true).PHP_EOL;

                        ?></pre></div>
                        <div class="prompt"># <input id="comando" name="comando" sitype="text" value="<?=$comando?>" /></div>



                        <?php  
                        //print_r($_ACL); 
                        //print_r(CFG::$vars);
                        //print_r(CFG::$vars);
                        //print_r($_COOKIE);
                        //print_r($_SERVER);

                        ?>







                    </div>
                    <?php }?>
                    <?php if($_ARGS['debug']){?> 
                    <div id="tab-cfg-debug">
                        <pre style="width:100%;height:370px;overflow:scroll;font-size:0.8em;line-height:0.8em;">
                        <?php  
                          print_r($_ACL); 
                          print_r(CFG::$vars);
                        ?>
                        </pre>
                    </div>
                    <?php }?>
                </div>    <!-- tabs-cfg -->

            </div>

            <?php if(Root()){?> 
                <div id="tab-cfg-updates">
                    <?php 
                        $check_mark = '<span style="color:green;font-size:18px;">&#x2714;</span>';
                    ?>
                    <div class="cfg-updates-col" id="system-info">
                        <?php
                        ini_set('default_socket_timeout', 4);
                        $script_version = file_get_contents( Str::end_with(  CFG::$vars['repo']['url'] ?? 'https://software.extralab.net' ).'version/html' ); 
                        if(!$script_version) $script_version = '0.0.0';
                        $php_ver = phpversion();
                        $php_os = php_uname('s').' '.php_uname('r'); //PHP_OS.' '.PHP_OS_FAMILY; // https://stackoverflow.com/questions/21129737/how-do-i-interpret-the-output-of-php-uname
                        $server = $_SERVER['SERVER_SOFTWARE']; //Vars::debug_var($_SERVER);  //['SERVER_SOFTWARE'];                        Vars::debug_var(DbConnection::$info);

                        
                        //if( version_compare(CFG::$vars['script']['version'], '3.2.001') < 0 )
                        //    CFG::$vars['script']['version'] = '3.0.0'; // forced update
                        
                        ?>
                        <style>td.warn{padding:5px !important;background-color:white !important;text-align:left !important;color:var(--red) !important;}pre.code{background-color:#2c466d;max-height:500px;text-align:left;color:#f9f9f9;overflow:hidden;/*width:100%;*/padding:8px;border:1px solid gray;line-height:0.9em;font-size:9px;}</style>
                        <table class="zebra table-values">
                        <tr><th colspan="2" style="text-align:center"><?=t('INFO','Información')?></th></tr>
                        <tr><td>Script Version</td><td><?php   echo CFG::$vars['script']['version'].'   '.(     version_compare(CFG::$vars['script']['version'], trim($script_version)) < 0       ?   ' <span style="color:red;">'.t('UPDATE_AVAILABLE','Actualización disponible').': <b>'.$script_version.'</b></span>':''); ?></td></tr>
                        <tr><td>           PHP</td><td><?php   echo $php_ver; /*$cfg['prefix']*/?></td></tr>
                        <tr><td>            OS</td><td><?php   echo $php_os; ?></td></tr>
                        <tr><td>      Database</td><td><?php   echo DbConnection::$info['SERVER_SOFTWARE'].' '.DbConnection::$info['SERVER_VERSION']; ?></td></tr>
                        <tr><td>    Web Server</td><td><?php   echo $server; ?></td></tr>
                        <tr><td>       Browser</td><td><?php   print_r( $_SERVER['HTTP_USER_AGENT'] ); ?></td></tr>
                        <tr><td>         Theme</td><td><?php   echo $_SESSION['theme'].' / '.CFG::$vars['default_theme']; ?></td></tr>
                        <tr><td>   Lang</td><td><?php   echo $_SESSION['lang'] .' / '.CFG::$vars['default_lang']; ?></td></tr>
                        <tr><td>       </td><td style="padding:4px;"> <b>ExtFW</b> version <?=CFG::$vars['script']['version']?>, Copyright &copy; 2022 extralab.net<br />
                        <b>ExtFW</b> comes with ABSOLUTELY NO WARRANTY<br />
                        This is free software, and you are welcome to redistribute it under certain conditions; Please, visit http://www.fsf.org/licenses/gpl.html for details.
                        </td></tr>
                        <?php
                        if (!function_exists('zip_open')){
                           ?><tr><td class="warn" colspan="2"> ZIP functions are required for PHP. Install with<br><pre class='code'>~ apt install unzip php-zip && /etc/init.d/apache2 restart</pre></td></tr><?php
                        }
                        if (!function_exists('mb_strimwidth')){
                           ?><tr><td class="warn" colspan="2"> MB string functions are required for PHP. Install with:<br><pre class='code'>~ apt install php-mbstring && /etc/init.d/apache2 restart</pre></td></tr><?php         
                        }
                        if (!function_exists('imagecreatefromjpeg')){
                           ?><tr><td class="warn" colspan="2"> GD functions are required for PHP. Install with:<br><pre class='code'>~ apt install php-gd && /etc/init.d/apache2 restart</pre></td></tr><?php         
                        }
                        if (!class_exists('DOMDocument')){
                           ?><tr><td class="warn" colspan="2"> XML functions are required for PHP. Install with:<br><pre class='code'>~ apt install php-xml && /etc/init.d/apache2 restart</pre></td></tr><?php         
                           // yum install php-xml en Centos / Fedora / Red Hat
                        }

                        if( version_compare(CFG::$vars['script']['version'], '3.0.137') < 0 ){
                        ?>                      
                        <tr><th colspan="2" style="text-align:center">Check DB charset</th></tr>
                        <tr><td>            database</td><td>ALTER DATABASE {dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</td></tr>
                        <tr><td>            table</td><td> ALTER TABLE {tbname} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</td></tr>
                        <tr><td>            column</td><td>ALTER TABLE {tbname} MODIFY COLUMN {colname} TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</td></tr>
                        <?php } ?>

                        </table>
                        <?php if(CFG::$vars['images']['webp']!=true){ ?>
                        <div style="margin-top:20px;text-align:left;" class="warning"><p>You have disabled support for Webp images.<br />You can change it in Configuration / Settings.</p></div>
                        <?php } ?>

                        </div>
                        <div class="cfg-updates-col" id="buttons-update">
                        
                        <ol>
                        <?php  if (CFG::$vars['repo']['url'] /*&& !(CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password'])*/) {  ?>
                            <?php if (CFG::$vars['script']['updates']) { ?>
                                <?php if ( version_compare(CFG::$vars['script']['version'], trim($script_version)) < 0 ) { ?>
                                <li data-op="update"><a>Update <b>ExtFW</b>  </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                                <?php } else { ?>
                                <li>The system is up to date &#128522;</li>
                                <?php }  ?>
                                <?php

                                $php_version = phpversion()[0];

                                ?>
                                <li data-op="vendor"><a>Composer utils  </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>

                            <?php } ?>
                        <?php } ?>

                        <li data-op="backup"><a>Create backup ZIP</a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>

                        <?php if(CFG::$vars['db']['type']=='mysql'||CFG::$vars['db']['type']=='sqlite' ) {?>
                            <li data-op="backdb"><a>Backup database </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                            <li data-op="restdb"><a>Restore database </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                            <li data-op="backmd"><a>Backup Media </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                        <?php }?>

                        <!--<li data-op="vendor"><a>Composer utils  </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>-->

                        <!-- ZIP -->
                
                        <?php if(Root()){?>
                            <?php
                            
                                $zip_file = 'extfw_vendor.'. $phpversion[0].'.'.$phpversion[1];
    
                            ?>
                            <?php  if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password']) { /***   IF NOT CFG::$vars['script']['updates'] ***/ ?>
                                <li data-op="zip/instal"><a>ZIP Install        </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                                <li data-op="zip/update"><a>ZIP Update  </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                                <li data-op="zip/vendor"><a>ZIP Composer </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                            <?php }else{?>
                                <li data-op="zip/vendor"><a>ZIP Composer </a> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>
                            <?php }?>
                        <?php }?>                        
        

                        <li class="nodisabled zip-module" data-op="zip/zip_module"><a>ZIP Módulo       </a> 
                        <select name="select_zip_module" id="select_zip_module" class="input_zip_module"> 
                        <?php
                        foreach (glob(SCRIPT_DIR_MODULES.'/*/README.md') as $filename) {
                            $filename = str_replace([SCRIPT_DIR_MODULES.'/','/README.md'],'',$filename);
                            echo '<option value="'.$filename.'">'.$filename.'</option>';
                        }
                        ?>
                        </select> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>


                        <li class="nodisabled zip-theme" data-op="zip/zip_theme"><a>ZIP Theme       </a>
                        <select name="select_zip_theme" id="select_zip_theme" class="input_zip_theme"> 
                        <?php
                        foreach (glob(SCRIPT_DIR_THEMES.'/*/README.md') as $filename) {
                            $filename = str_replace([SCRIPT_DIR_THEMES.'/','/README.md'],'',$filename);
                            echo '<option value="'.$filename.'">'.$filename.'</option>';
                        }
                        ?>
                        </select> &nbsp; <span class="progress_download">  </span> <span class="progress_download_info">  </span></li>

                        </ol> 

                    </div> 
                    
                </div>

                <?php if(CFG::$vars['db']['cache']) { ?>
                <div id="tab-logs">
                    <?php include(SCRIPT_DIR_MODULE.'/logs.php');  ?>
                </div>
                <?php } ?>
            <?php }?>

        </div>
        <?php 

    }else{

       ?><h3><?=t('ACCESS_DENIED')?></h3><?php 

    }
    echo Table::recycleIcon();   
    echo Table::ajaxLoader();
}
?>
</div>
<div class="inner color-buttons">
<div style="background-color: rgb(0, 31, 63); opacity: 1;"     data-label="1" ></div>
<div style="background-color: rgb(0, 116, 217); opacity: 1;"   data-label="2" ></div>
<div style="background-color: rgb(127, 219, 255); opacity: 1;" data-label="3" ></div>
<div style="background-color: #39CCCC;"                        data-label="4" ></div>
<div style="background-color: rgb(61, 153, 112); opacity: 1;"  data-label="5" ></div>
<div style="background-color: rgb(46, 204, 64); opacity: 1;"   data-label="6" ></div>
<div style="background-color: rgb(1, 255, 112); opacity: 1;"   data-label="7" ></div>
<div style="background-color: rgb(255, 220, 0); opacity: 1;"   data-label="8" ></div>
<div style="background-color: rgb(255, 133, 27); opacity: 1;"  data-label="9" ></div>
<div style="background-color: rgb(255, 65, 54); opacity: 1;"   data-label="10"></div>
<div style="background-color: rgb(133, 20, 75); opacity: 1;"   data-label="11"></div>
<div style="background-color: #B10DC9;"                        data-label="12"></div>
</div>

<div class="cart-ajaxloader ajax-loader" style="display:none;"><div class="loader"></div></div>

<style>
.color-buttons { display: flex;margin:0 auto;padding:0;/*border-top:5px solid transparent;border-bottom:5px solid transparent;*/}
.color-buttons div{flex-grow: 1;display:inline-block;height:2px;width: calc(100% / 12);color:transparent;margin:0;}</style>
