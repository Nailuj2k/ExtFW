<?php

if (MODULE=='page') echo '<style>'.$item_code_css.'</style>';


$_TEXT_SHORTCODED = APP::$shortcodes->do_shortcode($_TEXT_);
//APP::$plugins->debug_loaded_plugins();

//$_TEXT_SHORTCODED = $_TEXT_;


echo '<div class="'.$_CLASS_.'" id="content_'.$_NAME_.'">'.$_TEXT_SHORTCODED.'</div>';
//echo $_TEXT_;
//if (MODULE=='page') echo $item_code;
//if (MODULE=='page') echo '<script type="text/javascript">'.$item_code_js.'</script>';
echo '<div id="page-files" style="position:relative;display:none;outline: 2px dashed #e60045;"></div>';

if(MODULE=='page'){
    
    define('SCRIPT_DIR_MODULE_MEDIA',SCRIPT_DIR_MEDIA.'/'.MODULE.'/files');

}else {

    $_TB_NAME_ = strtoupper(MODULE);
    $_TB_PREFIX_ = TB_PREFIX; //$_TB_NAME_=='NEWS'?'NOT':($_TB_NAME_=='BLOG'?'BLG':$_TB_NAME_);
    
    define('SCRIPT_DIR_MODULE_MEDIA',SCRIPT_DIR_MEDIA.'/'.$_TB_NAME_.'/files');        
    
}    

if ( Vars::IsNumeric( $_ID_) ) {

    $gallery     =  $row['GALLERY'] == '1';
    $files       =  $row['FILES']   == '1';
    $documents   =  $row['DOCS']    == '1';
    $item_key    =  MODULE=='page'  ? 'id_item '         : $_TB_NAME_.'_ID';
    $tb_files    =  MODULE=='page'  ? TB_PAGES.'_FILES'  : $_TB_PREFIX_.'_'.$_TB_NAME_.'_FILES';
    $media_files =  MODULE=='page'  ? 'media/page/files' : 'media/'.$_TB_NAME_.'/files';

    if ( CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']=='es'){ 
        $text_fieldname = MODULE=='page' ? 'item_text' :  $_TB_PREFIX_.'_TEXT'; 
    }else{
        $text_fieldname = MODULE=='page' ? 'item_text_'.$_SESSION['lang'] :  $_TB_PREFIX_.'_TEXT_'.$_SESSION['lang'] ; 
    }
    $text_fieldname_alt =  MODULE=='page' ? 'item_text' :  $_TB_PREFIX_.'_TEXT'; 

    ?>
    <script type="text/javascript">
        var widget_loaded = false;
        //var pdf_viewer_initialized = false;     
        //var txt_viewer_initialized = false;     
       // var _TOKEN_       = '<?=$_SESSION['token']?>';
       // var _MODULE_      = '<?=MODULE?>';
        var _ID_          = '<?=$_ID_?>';
        var _MEDIA_FILES  = '<?=$media_files?>';
        var _NAME_        = '<?=$_NAME_?>';
        var _TB_NAME_     = '<?=$_TB_NAME_??''?>';
        var _TB_PREFIX_   = '<?=$_TB_PREFIX_??''?>';
        var _URL_AJAX_    = '<?=vars::mkUrl( MODULE, 'ajax')?>';
        var _TEXT_FNAME_  = '<?=$text_fieldname?>';
        var _TEXT_FNAME_A = '<?=$text_fieldname_alt?>';
        var _MSG_NO_TEXT_ = '<h4>No hay contenido para el idioma actual (<?=$_SESSION['lang']?>).</h4>Cambie al idioma por omisión (<?=CFG::$vars['default_lang']?>)y vuelva a intentarlo.<br /><br /><a class="btn btn-primary" href="./<?=$_NAME_.'/'.CFG::$vars['default_lang']?>">Ok, vamos allá</a>'
        var _SESSION_LANG_= '<?=$_SESSION['lang']?>';
        var _USER_ID_     = '<?=$_SESSION['userid']?>';
        var _USER_NAME_   = '<?=$_SESSION['username']?>';
        var _USER_SCORE_  = '<?=$_SESSION['user_score']?>';
        //var _DEFAULT_LANG_= '<?=CFG::$vars['default_lang']?>';
        var _CODE_EDITOR_ = '<?=CODE_EDITOR?>';
        var _HIGHLIGHT_CODE_ = '<?=CFG::$vars['options']['highlight_code']===true?'true':'false'?>';
        
    //var _DEFAULT_LANG_TEXT_ = '< ?=_DEFAULT_LANG_TEXT_? >';
    </script>
    <?php

    if($editable) {


        $_TEXT_ = str_replace(['<br>'  ,'><'  ],
                            ['<br />',">\n<"],$_TEXT_);

        // https://stackoverflow.com/questions/768215/php-pretty-print-html-not-tidy
        /*  
        include(SCRIPT_DIR_LIB.'/supertidy/supertidy.class.php');
        $Tidy = new SuperTidy($_TEXT_); 
        $Tidy->SetIndentSize(4); 
        $Tidy->SetOffset(0); 
        $_TIDY_TEXT_ = $Tidy->BeautifiedHTML();

        */
        $_TIDY_TEXT_ = $_TEXT_;
        

        $inline_edit  =  (strpos($_TIDY_TEXT_,'<script')==false) 
                   // && (strpos($_TIDY_TEXT_,'<code')==false) 
                   // && (strpos($_TIDY_TEXT_,'<pre')==false) 
                   // && (strpos($_TIDY_TEXT_,'class="code')==false)
                      ;

        $html_files = '';

        if(MODULE=='page'){
            $sql_files = 'SELECT * FROM '.TB_PAGES.'_FILES WHERE id_item= '.$_ID_.' ORDER BY ID';    
        }else{
            $sql_files = 'SELECT * FROM '.$_TB_PREFIX_.'_'.$_TB_NAME_.'_FILES WHERE '.$_TB_NAME_.'_ID= '.$_ID_;
        }

        $_files = Table::sqlQuery($sql_files);
        foreach ($_files as $_file){
            $ext = Str::get_file_extension($_file['FILE_NAME']);
            $valid_img_ext = in_array(strtolower($ext),['jpg','jpeg','gif','png','webp']);
            //if($valid_img_ext){
                //$html_files .= '<img src="'.SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$_file['FILE_NAME'].'">'; 
                $html_files .= '<div'
                            .  '       class = "gallery-item gallery-item-'.($valid_img_ext?'image':'file').' gallery-item-'.$ext.'"'
                            .  '       style = "background:url('.($valid_img_ext?SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$_file['FILE_NAME']:SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$ext.'.png').');background-size: contain;background-repeat:no-repeat;background-position:center;"'
                            .  '  data-thumb = "'.SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/.tn_'.$_file['FILE_NAME'].'"'
                            .  '    data-src = "'.SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$_file['FILE_NAME'].'"'
                            .  ' data-parent = "'.$_file[MODULE=='page'?'id_item':TB_PREFIX.'_ID'].'"'
                            .  '     data-id = "'.$_file['ID'].'"'
                            .  '   data-desc = "'.($_file['DESCRIPTION']?$_file['DESCRIPTION']:($_file['NAME']?$_file['NAME']:$_file['FILE_NAME'])).'"'
                            .  '    data-ext = "'.($valid_img_ext?'image':($ext=='pdf'?'pdf':'file')).'"'
                            .  '>'
                            .($valid_img_ext?'<button class="nobtn nobtn-small" data-op="thumb">'.t('THUMB','Miniatura').'</button>':'')
                            .($valid_img_ext?'<button class="nobtn nobtn-small" data-op="image">'.t('IMAGE','Imagen').'</button>':'')
                            .  '<button title="'.$_file['NAME'].'" class="nobtn nobtn-small" data-op="link">'.t('LINK','Enlace').'</button>'
                        // .  '<button class="nobtn nobtn-small" data-op="delete">'.t('DELETE','Eliminar').'</button>'
                            .  '</div>'; 
            //}else if($ext=='pdf'){
            //    $html_files .= '<div class="gallery-item gallery-item-file"><a href="'.SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$_file['FILE_NAME'].'" title="'.$_file['FILE_NAME'].'"><i class="fa fa-file-pdf-o"></i></a></div>'; 
            //}else {
            //    $html_files .= '<div class="gallery-item gallery-item-file"><a href="'.SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$_file['FILE_NAME'].'" title="'.$_file['FILE_NAME'].'"><i class="fa fa-file-o"></i></a></div>'; 
            //}

        }



        /*
        echo "SELECT * FROM ".TB_PAGES." WHERE item_name= '".Str::sanitizeName($_ARGS[1])."'";
        $rows1 = Table::sqlQuery("SELECT * FROM ".TB_PAGES." WHERE item_name= '".Str::sanitizeName($_ARGS[1])."'");
        print_r($items_rows[0]['item_id'].'--'.$rows1[0]['item_id']);
        */

        if (MODULE=='page')  $code_tabs = new Tabs('code-tabs');
        if (MODULE=='page')  $code_tabs->addTab('HTML','code-html');
        if (MODULE=='page')  $code_tabs->addTab('CSS','code-css');
        if (MODULE=='page')  $code_tabs->addTab('Javascript','code-js');
        if (MODULE=='page')  $code_tabs->addTab('Código','code-code');
        if (MODULE=='page')  $code_tabs->begin();
        if (MODULE=='page')  $code_tabs->beginTab('code-html');
                             echo '<div style="display:none;" class="editor-wrapper code-textarea"                    id="edit_'.$_NAME_.'"     data-filetype="html">';
                             if(CODE_EDITOR=='monaco') echo '<textarea id="monaco_content_html">';
                             echo htmlentities($_TIDY_TEXT_);
                             if(CODE_EDITOR=='monaco') echo '</textarea>';
                             echo '</div>';
        if (MODULE=='page')  $code_tabs->endTab();
        if (MODULE=='page')  $code_tabs->beginTab('code-css');
        if (MODULE=='page')  echo '<div style="display:none;" class="editor-wrapper code-textarea-css" data-reset="'.htmlentities($item_code_css).'" id="edit_'.$_NAME_.'_css" data-filetype="css">';
        if (MODULE=='page' && CODE_EDITOR=='monaco')  echo '<textarea id="monaco_content_css">';
        if (MODULE=='page')  echo htmlentities($item_code_css);
        if (MODULE=='page' && CODE_EDITOR=='monaco')  echo '</textarea>';
        if (MODULE=='page')  echo '</div>';
        if (MODULE=='page')  $code_tabs->endTab();
        if (MODULE=='page')  $code_tabs->beginTab('code-js');
        if (MODULE=='page')  echo '<div style="display:none;" class="editor-wrapper code-textarea-css" data-reset="'.htmlentities($item_code_js).'"  id="edit_'.$_NAME_.'_js"  data-filetype="javascript">';
        if (MODULE=='page' && CODE_EDITOR=='monaco')  echo '<textarea id="monaco_content_js">';
        if (MODULE=='page')  echo htmlentities($item_code_js);
        if (MODULE=='page' && CODE_EDITOR=='monaco')  echo '</textarea>';
        if (MODULE=='page')  echo '</div>';
        if (MODULE=='page')  $code_tabs->endTab();
        if (MODULE=='page')  $code_tabs->beginTab('code-code');
        if (MODULE=='page')  echo '<div style="display:none;" class="editor-wrapper code-textarea-css" data-reset="'.htmlentities($item_code).'"     id="edit_'.$_NAME_.'_code" data-filetype="html">';
        if (MODULE=='page' && CODE_EDITOR=='monaco')  echo '<textarea id="monaco_content_code">';
        if (MODULE=='page')  echo htmlentities($item_code);
        if (MODULE=='page' && CODE_EDITOR=='monaco')  echo '</textarea>';
        if (MODULE=='page')  echo '</div>';
        if (MODULE=='page')  $code_tabs->endTab();
        if (MODULE=='page')  $code_tabs->end();

        //if (MODULE=='page' || MODULE=='news' || MODULE=='blog'){

            if (CODE_EDITOR=='monaco'){ 
            }


            if(CODE_EDITOR=='monaco'){

                if(defined('CDN_URL') && CDN_URL)  {
                    HTMl::js(CDN_URL.'/_lib_/monaco-editor/min/vs/loader.js','defer');  
                    ?><script> const MONACO_VS_PATH = '<?=CDN_URL?>/_lib_/monaco-editor/min/vs'; </script><?php
                }else if(USE_CDN==true)             {
                    HTMl::js('https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js','defer'); 
                    ?><script> const MONACO_VS_PATH = 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs'; </script><?php
                }else                               {
                    HTMl::js(SCRIPT_DIR_LIB.'/monaco-editor/min/vs/loader.js','defer'); 
                    ?><script> const MONACO_VS_PATH = '<?=SCRIPT_DIR_LIB?>/monaco-editor/min/vs'; </script><?php
                }


                //HTML::js(CDN_URL.'/_js_/monaco/init.js?ver=1.0.1','defer');
                HTML::js(SCRIPT_DIR_MODULES.'/page/inline_init.js?ver=1.0.0','defer');

            }else if(CODE_EDITOR=='ace'){
                HTML::js('https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ace.js?ver=1.1.3');
                HTML::js('https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ext-language_tools.js?ver=1.1.3');
                HTML::js('https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ext-static_highlight.js');
                HTML::js('https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js?ver=2.8.3');
            }

            HTML::js(SCRIPT_DIR_MODULES.'/page/inline_edit.js?ver=1.1.5','defer');
        //}
        ?>
        <div class="cart-ajaxloader ajax-loader" style="display:none;"><div class="loader"></div></div>
        <?php 
        if ($inline_edit){
            $_HTML_toolbar = '<div id="toolbar-buttons">
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'undo\'><i class=\'fa fa-undo\'></i></button>
                    <button data-role=\'redo\'><i class=\'fa fa-repeat\'></i></button>
                </span>
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'bold\'><strong>B</strong></button>
                    <button data-role=\'italic\'><em>I</em></button>
                    <button data-role=\'underline\'><u><b>U</b></u></button>
                    <button data-role=\'strikeThrough\'><strike>a</strike></button>
                </span>
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'insertImage\' data-val="http://dummyimage.com/160x90"><i class="fa fa-picture-o"></i></button>
                    <button data-role=\'insertVideo\'"><i class="fa fa-youtube"></i></button>
                <!--<button data-role=\'insertHTML\' data-val="&lt;h3&gt;Lorem ipsum dolor sit amet&lt;/h3&gt;"><i class="fa fa-code"></i></button>-->
                </span>
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'justifyLeft\'><i class=\'fa fa-align-left\'></i></button>
                    <button data-role=\'justifyCenter\'><i class=\'fa fa-align-center\'></i></button>
                    <button data-role=\'justifyRight\'><i class=\'fa fa-align-right\'></i></button>
                    <button data-role=\'justifyFull\'><i class=\'fa fa-align-justify\'></i></button>
                </span>
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'indent\'><i class=\'fa fa-indent\'></i></button>
                    <button data-role=\'outdent\'><i class=\'fa fa-outdent\'></i></button>
                </span>
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'insertUnorderedList\'><i class=\'fa fa-list-ul\'></i></button>
                    <button data-role=\'insertOrderedList\'><i class=\'fa fa-list-ol\'></i></button>
                </span>
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'h1\'></i>H1</button>
                    <button data-role=\'h2\'></i>H2</button>
                    <button data-role=\'h3\'></i>H3</button>
                    <button data-role=\'h4\'></i>H4</button>
                    <button data-role=\'p\'>p</button>
                    <button data-role=\'pre\'>pre</button>
                </span>
                <!--            
                <span class=\'btn-editor btn-group\'>
                    <button data-role=\'subscript\'><i class=\'fa fa-subscript\'></i></button>
                    <button data-role=\'superscript\'><i class=\'fa fa-superscript\'></i></button>
                </span>
                -->
                </div>';

            echo $_HTML_toolbar ;
        }
        ?>
        <div id="edit-buttons"><span style="position:absolute;left:6px;top:7px;color: gray;font-size:18px;font-weight:600;cursor:move;">:::</span>
            <?php if($inline_edit) {?><button id="edit_page"  title="Edición rápida" style="color:#c91043;"><i class="fa fa-edit"></i> Editar ésta página</button><?php } else {?><button class="not_edit_page"  title="Edición rápida" style="color:#c91043;text-decoration: line-through;"><i class="fa fa-edit"></i>Editar ésta página</button><?php } ?>
            <button id="edit_page_source"  title="Editar código fuente" style="color:#0d6bb0;"><i class="fa fa-code"></i> Src </button>
            <?php if ($_AVD_) { ?><?php if($inline_edit) {?><button id="edit_page_advanced"  title="Editor completo" style="color:#007fad;"><i class="fa fa-list-alt"></i> Adv </button><?php } else {?> <button class="not_edit_page"  title="Edición rápida" style="color:#c91043;text-decoration: line-through;"><i class="fa fa-edit"></i>Adv</button><?php } ?><?php } ?> 
            <?php if(1==2 && MODULE=='page'){ ?><button id="add_page_files"  title="Añadir archivos" style="color:#007fad;"><i class="fa fa-upload"></i> </button><?php } ?>
            <button id="save_text"   style="display:none;color:#00aa34;"><i class="fa fa-check"></i> Guardar cambios</button>
            <button id="cancel_text" style="display:none;color:#cf0c40;"><i class="fa fa-close"></i> Descartar</button><!-- link editar -->
            <?php if (MODULE=='page') { ?><button id="clone_page"  title="Crear una nueva página usando esta como plantilla" style="color:#149c63;"><i class="fa fa-clone"></i> Clonar </button><?php } ?> 
        </div>
        <a id="files-button">Archivos</a> 
        <div id="files-panel" class="box-radius" ondragover="drag_over(event)">
            <div id="files-panel-handle"><div>&laquo;</div></div>
            <div id="drop_file_zone" class="box-radius">
                <div id="drag_upload_file" class="box-radius" ondrop="upload_file(event)" ondragover="return false" ondragleave="drag_leave(event)">
                </div>
                <div id="files-gallery" class="box-radius">
                <div id="files-gallery-help">Arrastre aquí una imagen o seleccione un  <input type="button" style="font-size:0.8em;" value="archivo" onclick="file_explorer();"><input type="file" id="selectfile"><br />Para insertar una imagen o documento haga click en donde quiera ponerla y use los botones 'Miniatura','Imagen', o 'Enlace'.</div>
                <div id="files-gallery-list"><?php echo $html_files; ?></div> 
                </div>
            </div>
        </div>

        <div id="result"></div>
        <?php

    }
    
    if($documents){



        //TEST Table::init();
        //Table::show_table('CLI_PAGES_FILES');        CLI_PAGES_FILES   tb_id kbn_items ui-sortable
        Table::show_table($tb_files,false,true,1,$editable); //Administrador());   //show_table($tablename,$modulename=false,$element=true,$page_number=1,$sortable=false)
        // Table::show_table('CLI_CATEGORIES');   //show_table($tablename,$modulename=false,$element=true,$page_number=1,$sortable=false)
        if(MODULE=='page' && ($editable||Administrador()) ) {
            ?><div id="pages_files_cfg" style="display:none;"><?php
            // Table::show_table('CLI_PAGES_FILES_TAGS');     

            $_SESSION['page_files_perms']['view']   = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador(); 
            $_SESSION['page_files_perms']['reload'] = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador(); 
            $_SESSION['page_files_perms']['add']    = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador(); 
            $_SESSION['page_files_perms']['edit']   = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador(); 
            $_SESSION['page_files_perms']['delete'] = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador(); 

            Table::show_table('CLI_CATEGORIES');     
            Table::show_table('CLI_TAGS');     
            ?></div><?php
        }
        // '? ><div id="page-widget-documents"></div><script>loadWidget('documents');</script>< ?php'
    }else{
        if($files)  { 
            ?><div id="page-widget-files"></div><?php
        }
        if($gallery) {
            ?><div id="page-widget-gallery"></div><?php
        }
    }



}else{

    ?><div class="error"><?=t('ID_NOT_VALID')?>: <?=$_ID_?></div><?php

}
