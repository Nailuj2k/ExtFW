<?php
            
            //header('Content-type: text/html; charset=utf-8');

            //HTML::css( 'https://kit.fontawesome.com/9f3b6271ee.js','screen','crossorigin="anonymous"');
            if(defined('CDN_URL') && CDN_URL) HTML::css( CDN_URL.'/_lib_/font-awesome/css/font-awesome.min.css?v='.VERSION);
            else if(USE_CDN==true)            HTML::css( 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css');
            else                              HTML::css( SCRIPT_DIR_LIB.'/font-awesome/css/font-awesome.min.css?v='.VERSION);
                                           // HTML::css( 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css');

            //HTML::css( SCRIPT_DIR_JS.'/ohSnap/ohsnap.css?v='.VERSION);
            HTML::css( SCRIPT_DIR_JS.'/simpleTabs/simpleTabs.css?v='.VERSION);
            //////////////HTML::css( SCRIPT_DIR_JS.'/jquery.modalform/jquery.modalform.css?v='.VERSION);
            //HTML::css( SCRIPT_DIR_JS.'/swipebox/src/css/swipebox.css?v='.VERSION);
            //HTML::css(SCRIPT_DIR_JS . '/simple-viewer/simple-viewer.css?v='.VERSION);

            if(CFG::$vars['options']['highlight_code']===true ){
                if(CFG::$vars['options']['highlight_engine']==='prism' ){
                    HTML::css(SCRIPT_DIR_LIB.'/prism/prism.css');
                }else{
                  //HTML::css( 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css');
                    HTML::css(SCRIPT_DIR_LIB.'/highlight.js/styles/medium.css');
                }
            }
            //HTML::css('/_plugins_/widgets/minichat.css');

            HTML::css(SCRIPT_DIR_JS . '/wquery/wquery.dialog.css?v='.VERSION);

            if(defined('MODULE_SHOP'))
                if(MODULE_SHOP) HTML::css( SCRIPT_DIR_THEME.'/style.cart.css?v='.VERSION);

            $_hrefs = array();
            $canonical_url = $cfg['proto'].$_SERVER['HTTP_HOST'].$_REQUEST_URI;

            if( $db_engine=='crud'||$db_engine=='crud.new'){ 
                Table::css( $form_css ? $form_css : false );
            }else{
                HTML::css(SCRIPT_DIR_CLASSES.'/crud/css/style.float.css?v='.VERSION);
            }

            $_hrefs[] = '<link rel="canonical" href="'.$canonical_url.'" />';


            $langs =  ( CFG::$vars['site']['langs']['enabled']===true )
                   ? Table::sqlQuery("select lang_id,lang_cc,lang_name from ".TB_LANG." where lang_active=1 and lang_cc<>'".CFG::$vars['default_lang']."'")
                   : [];

            $_HREF = '';
            if (MODULE==CFG::$vars['default_module']){
                if( $_ARGS[1]){
                    if (!in_array($_ARGS[1],['theme','lang',$_SESSION['lang']])) $_HREF .= '/'.$_ARGS[1];
                }
            }else{
                $_HREF .= '/'.MODULE;
                if( isset($_ARGS[1]) && $_ARGS[1]){
                    if (!in_array($_ARGS[1],['theme','lang',$_SESSION['lang']])) $_HREF .= '/'.$_ARGS[1];
                    if( $_ARGS[2]){
                        if (!in_array($_ARGS[2],['theme','lang',$_SESSION['lang']])) $_HREF .= '/'.$_ARGS[2];
                    }
                }
            }            

            $tr_links[CFG::$vars['default_lang']] = ['Español',$_HREF.'/es'];   

            foreach ($langs as $lang){
                $tr_links[$lang['lang_cc']]= [$lang['lang_name'],$_HREF.'/'.$lang['lang_cc']];
            }

            $hreflang_xdef = $cfg['proto'].$_SERVER['HTTP_HOST'];

            if (MODULE==CFG::$vars['default_module']){
                if( $_ARGS[1]){
                    if (!in_array($_ARGS[1],['theme','lang',$_SESSION['lang']])) $hreflang_xdef .= '/'.$_ARGS[1];
                }
            }else{
                $hreflang_xdef .= '/'.MODULE;
                if( isset($_ARGS[1]) &&  $_ARGS[1]){
                    if (!in_array($_ARGS[1],['theme','lang',$_SESSION['lang']])) $hreflang_xdef .= '/'.$_ARGS[1];
                    if( $_ARGS[2]){
                        if (!in_array($_ARGS[2],['theme','lang',$_SESSION['lang']])) $hreflang_xdef .= '/'.$_ARGS[2];
                    }
                }
            }

            $_hrefs[] = '<link rel="alternate" hreflang="x-default" href="'.$hreflang_xdef.'" />';
            $_hrefs[] = '<link rel="alternate" hreflang="'.CFG::$vars['default_lang'].'" href="'.$hreflang_xdef.'" />';

           foreach($tr_links as $k => $v){
                if($k!=CFG::$vars['default_lang']){
                    if($k==$_SESSION['lang'])
                        $v1=  $canonical_url;
                    else 
                        $v1=  $cfg['proto'].$_SERVER['HTTP_HOST'].$v[1]; 
                    $_hrefs[] = '<link rel="alternate" hreflang="'.$k.'" href="'.$v1.'" />';
                }
            }

            if (file_exists(SCRIPT_DIR_MODULE  . '/head.php')) include( SCRIPT_DIR_MODULE  . '/head.php' );

            //CHECK !!!
            //if($db_engine=='scaffold'){ 
            //    echo "<script>console.log('DB_ENGINE','{$db_engine}'::'".WYSIWYG_EDITOR."');</script>';
            //  
            //    if(WYSIWYG_EDITOR) include(SCRIPT_DIR_CLASSES.'/scaffold/editor/'.WYSIWYG_EDITOR.'/editor_init.php'); 
            //}


            $rrss_url = $rrss_url ?? $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_REQUEST_URI;
            $rrss_sit = $rrss_sit ?? CFG::$vars['site']['title'] ;
            $rrss_tit = $rrss_tit ?? CFG::$vars['site']['title'] ;
            $rrss_key = $rrss_key ?? CFG::$vars['site']['keywords'] ;
            $rrss_des = $rrss_des ?? CFG::$vars['site']['description'] ;
            $rrss_img = $rrss_img ?? $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/'.'media/images/logo.png'; 

        ?>

        <meta charset="utf-8">
        <base href="<?=SCRIPT_HOST.SCRIPT_DIR?>/">

        <title><?=$rrss_tit?></title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--<meta http-equiv="Content-Security-Policy" content="script-src 'none'">-->
        <meta name="theme-color" content="#ff0">

        <?php  if($private_url){ ?>
            
        <meta name="robots" content="noindex,nofollow">
        
        <?php  } ?>        

        <meta name="keywords"    content="<?=$rrss_key?>" />
        <meta name="description" content="<?=$rrss_des?>" />
        <meta name="generator"   content="COPY CON" />

        <!-- Facebook -->
        <meta property="og:type"          content="website" />
        <meta property="og:url"           content="<?=$rrss_url?>" />
        <meta property="og:site_name"     content="<?=$rrss_sit?>" />
        <meta property="og:title"         content="<?=$rrss_tit?>" />
        <meta property="og:description"   content="<?=$rrss_des?>" />
        <meta property="og:updated_time"  content="<?=date('Y-m-d h:i:s')?>" />
        <meta property="og:image"         content="<?=$rrss_img?>" />

        <!-- Twitter -->
        <meta name="twitter:card"        content="summary" />
        <meta name="twitter:url"         content="<?=$rrss_url?>" />
        <meta name="twitter:site"        content="<?=$rrss_sit?>" />
        <meta name="twitter:title"       content="<?=$rrss_tit?>" />
        <meta name="twitter:description" content="<?=$rrss_des?>" />
        <meta name="twitter:image"       content="<?=$rrss_img?>" />   

        <?php

            echo implode("\n".'        ',$_hrefs)."\n";
            //if ($documents || $files || $gallery) {

                if(CFG::$vars['plugins']['epub']){
                    HTML::css(SCRIPT_DIR_LIB.'/epub/reader.css?v='.VERSION);
                    HTML::css(SCRIPT_DIR_LIB.'/epub/epub.css?v='.VERSION);
                }

              //HTML::css(CFG::$vars['proto'].$_SERVER['HTTP_HOST'].'/'.SCRIPT_DIR_LIB.'/epub/epub.css?v='.VERSION);
  //              HTML::css(SCRIPT_DIR_LIB.'/file_viewer/file_viewer.css?v='.VERSION);
              //HTML::css(SCRIPT_DIR_LIB.'/file_viewer/pdf_viewer.css?v='.VERSION);
                HTML::css(SCRIPT_DIR_LIB.'/file_viewer/json_viewer.css?v='.VERSION);
              //HTML::css(SCRIPT_DIR_LIB.'/file_viewer/txt_viewer.css?v='.VERSION);
              //HTML::css(SCRIPT_DIR_LIB.'/file_viewer/url_viewer.css?v='.VERSION);
           // }

            //HTML::css('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css');
            //HTML::css(SCRIPT_DIR_JS.'/image_editor/image_editor.css?v='.VERSION);


            HTML::render('css');
        ?>

        <?php include('media/icons/icons.php');  ?>

        <script>
            var console_log = false;
            const _TOKEN_       = '<?=$_SESSION['token']?>';
            const _MODULE_      = '<?=MODULE?>';      
            const _LANG_        = '<?=($_SESSION['lang'] ?? 'en' )?>';      
            const _DEFAULT_LANG_= '<?=CFG::$vars['default_lang']?>';
            const str_of        = '<?=t('OF')?>';                         
            const str_row       = '<?=t('ROW','fila')?>';          
            const str_item      = '<?=t('ITEM')?>';
            const str_copy      = '<?=t('COPY','Copiar')?>';
            const str_page      = '<?=t('PAGE')?>';
            const str_vote      = '<?=t('VOTE','voto','vote')?>';
            const str_stars     = '<?=t('STARS','estrellas','stars')?>';
            const str_votes     = '<?=t('VOTES','votos','votes')?>';       
            const str_price     = '<?=t('PRICE')?>';
            const str_accept    = '<?=t('ACCEPT','Aceptar')?>';
            const str_cancel    = '<?=t('CANCEL','Cancelar')?>';
            const str_delete    = '<?=t('DELETE')?>';
            const str_no_rating = '<?=t('NO_RATING','Sin calificación','No rating')?>';

            const str_delete_image = '<?=t('DELETE_IMAGE','Eliminar imagen')?>';
            const str_reset_styles = '<?=t('RESET_STYLES','Eliminar estilos')?>';
            const str_edit_file = '<?=t('EDIT_FILE','Editar archivo')?>';
            const str_delete_file = '<?=t('DELETE_FILE','Eliminar archivo')?>';
            const str_edit = '<?=t('EDIT','Editar')?>';
            const str_add_files = '<?=t('ADD_FILES','Añadir archivos')?>';  

            const str_sign_passwordless = '<?=t('SIGN_PASSWORDLESS','Prefiero usar acceso sin contraseña')?>';
            const str_sign_password =  '<?=t('SIGN_IN_WITH_PASSWORD','Prefiero usar contraseña')?>';


            //console.log('MODULE_NAME', module_name);
        </script>
        
        <?php include(SCRIPT_DIR_MODULES.'/'.MODULE.'/i18n.php');  ?>

        <?php  if($private_url===false){ ?>
        <?php  if (CFG::$vars['tracking']['google_analytic_id']){ ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?=CFG::$vars['tracking']['google_analytic_id']?>"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '<?=CFG::$vars['tracking']['google_analytic_id']?>');
        </script>
        <?php }?>
        <?php }?>
        
        <?php
        if(defined('MODULE_SHOP'))
            if(MODULE_SHOP) include(SCRIPT_DIR_MODULES.'/'.MODULE_SHOP.'/cart.php');  
