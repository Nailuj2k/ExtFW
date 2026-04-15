<?php

//if(!$_ARGS['id']) $_ARGS['id'] = 999; 

if($TYPE=='documents'){

    // page/html/editable=$editable/type=gallery|files|documents/id=$_ID_
    ///////////////////////////////////////////////////////////////////////////////$_SESSION['PAGE_FILES_ID_PARENT'] = $_ARGS['id'];  //$_ID_;
    //TEST Table::init();
    //Table::show_table('CLI_PAGES_FILES');        CLI_PAGES_FILES   tb_id kbn_items ui-sortable
    Table::show_table('CLI_PAGES_FILES',false,true,1,Administrador());   //show_table($tablename,$modulename=false,$element=true,$page_number=1,$sortable=false)

}else if($TYPE=='files'||$TYPE=='gallery'){

    ///////////////////////////////////////////////////////////////////////////$_SESSION['PAGE_FILES_ID_PARENT'] = $_ARGS['id'];  //$_ID_;

    // page/html/editable=$editable/type=gallery|files|documents/id=$_ID_
    $_ID_ = $_ARGS['id'];
    $_TB_NAME_ = strtoupper($_ARGS[0]);
    $_TB_PREFIX_ = $_TB_NAME_=='NEWS'?'NOT':($_TB_NAME_=='BLOG'?'BLG':$_TB_NAME_);

    $editable =  $_ARGS['editable']=='1';
    $item_key =  $_ARGS[0]=='page' ? 'id_item '        : $_TB_NAME_.'_ID';
    $tb_files =  $_ARGS[0]=='page' ? 'CLI_PAGES_FILES' : $_TB_PREFIX_.'_'.$_TB_NAME_.'_FILES';

    if (CFG::$vars['site']['categories']['enabled']&&$_ARGS[0]=='page'){
        
        $_group_by_categories=true;

        if ( $_ARGS[0]=='page' || CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']=='es'){ 
            $field_name = 'f.ID,f.NAME'; 
            $field_desc = 'f.DESCRIPTION'; 
        }else{
            $field_name = "COALESCE(NULLIF(f.NAME_".$_SESSION['lang'].",''), f.NAME) AS NAME";
            $field_desc = "COALESCE(NULLIF(f.DESCRIPTION_".$_SESSION['lang'].",''), f.DESCRIPTION) AS DESCRIPTION";
        }
        if ($_ARGS[0]=='page') $field_name .= ',f.DOWNLOAD_COUNT';
        $sql_files  = 'SELECT '.$field_name.',f.LINK,f.ID_CATEGORIE,c.NAME AS CAT,f.ID_PROVIDER,f.FILE_NAME,'.$field_desc.',f.MINI,f.MAIN,f.ACTIVE,f.ITEM_ORDER FROM '.$tb_files.' f, CLI_CATEGORIES c '
                    . 'WHERE f.'.$item_key.'='.$_ID_.' AND f.ACTIVE=\'1\' AND f.ID_PROVIDER<>\'1\' AND f.ID_CATEGORIE = c.CATEGORIE_ID ORDER BY CAT,f.ITEM_ORDER';   //FIX coallesce
        $sql_images = 'SELECT '.$field_name.',f.ID_CATEGORIE,c.NAME AS CAT,f.ID_PROVIDER,f.FILE_NAME,'.$field_desc.',f.MINI,f.MAIN,f.ACTIVE,f.ITEM_ORDER FROM '.$tb_files.' f, CLI_CATEGORIES c '
                    . 'WHERE f.'.$item_key.'='.$_ID_.' AND f.ACTIVE=\'1\' AND f.ID_PROVIDER=\'1\' AND f.ID_CATEGORIE = c.CATEGORIE_ID ORDER BY CAT,f.ITEM_ORDER,f.ID';   //FIX coallesce

    }else{
        if ($_ARGS[0]=='page' ||  CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']=='es'){ 
            $field_name = 'ID,NAME'; 
            $field_desc = 'DESCRIPTION'; 
        }else{
            $field_name = "COALESCE(NULLIF(NAME_".$_SESSION['lang'].",''), NAME) AS NAME";
            $field_desc = "COALESCE(NULLIF(DESCRIPTION_".$_SESSION['lang'].",''), DESCRIPTION) AS DESCRIPTION";
        }
        if ($_ARGS[0]=='page') $field_name .= ',DOWNLOAD_COUNT';
        $sql_files = 'SELECT '.$field_name.',LINK,ID_PROVIDER,FILE_NAME,'.$field_desc.',MINI,MAIN,ACTIVE,ITEM_ORDER FROM '.$tb_files.' '
                    . 'WHERE '.$item_key.'='.$_ID_.' AND ACTIVE=\'1\' AND ID_PROVIDER<>\'1\' ORDER BY ITEM_ORDER';   //FIX coallesce
        $sql_images = 'SELECT '.$field_name.',ID_PROVIDER,FILE_NAME,'.$field_desc.',MINI,MAIN,ACTIVE,ITEM_ORDER,LAST_UPDATE_DATE FROM '.$tb_files.' '
                    . 'WHERE '.$item_key.'='.$_ID_.' AND MAIN <>\'1\' AND MINI<>\'1\' AND ACTIVE=\'1\' AND ID_PROVIDER=\'1\' ORDER BY ITEM_ORDER,ID';   
    }

    if($_ARGS[0]=='page')
        define('SCRIPT_DIR_MODULE_MEDIA',SCRIPT_DIR_MEDIA.'/page/files');  
    else
        define('SCRIPT_DIR_MODULE_MEDIA',SCRIPT_DIR_MEDIA.'/'.$_TB_NAME_.'/files');        

    //Vars::debug_var($sql_images);

    if($TYPE=='files'){

        $files = Table::sqlQuery($sql_files);
        if($files){
            echo '<div id="file-list" style="position:relative;">';
            //echo $sql_files;
            //include_once(SCRIPT_DIR_LIB.'/file_viewer/pdf_viewer.php'); 
            //include_once(SCRIPT_DIR_LIB.'/file_viewer/epub_viewer.php'); 
            //include_once(SCRIPT_DIR_LIB.'/file_viewer/txt_viewer.php'); 
            echo '<p class="title">Archivos adjuntos. '
                .'<i id="files-reload" class="fa fa-refresh" title="Recargar"></i> '
                .($editable /*&& $_ARGS[0]=='page'*/ ?'<i id="files-add-files" class="fa fa-upload" title="Añadir archivos"></i> ':'')
                .'</p><ul>';
            $cur_cat = '';
            foreach ($files as $file){
              if (CFG::$vars['site']['categories']['enabled']&&$_ARGS[0]=='page'){
                   if($cur_cat!=$file['CAT']){
                        $cur_cat=$file['CAT'];
                        echo '<li><b>'.$file['CAT'].'</b></li>';
                   }
               }

               //$image_file = SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$file['FILE_NAME'];

               $type     = Str::get_file_extension($file['FILE_NAME']); 
               $filename = Str::get_file_name($file['FILE_NAME']);  
               $name     = Str::sanitizeName(  $file['NAME'] );
               $ver = hash('crc32b', $file['LAST_UPDATE_DATE'] ?? '');
               if($_ARGS[0]=='page'){
                   $image_file =  'page/file/filename='.$filename.'/id='.$file['ID']
                               .'/path=media+page+files+'.$_ID_
                               .'/tb='.$tb_files.'/name='.$name.'/ext='.$type.'/counter=DOWNLOAD_COUNT/mode=inline';  //FIX path
               }else{
                   $image_file =  $_ARGS[0].'/file/filename='.$filename.'/id='.$file['ID']
                               .'/path=media+'.$_TB_NAME_.'+files+'.$_ID_
                               .'/tb='.$tb_files.'/name='.$name.'/ext='.$type.'/counter=DOWNLOAD_COUNT/mode=inline';  //FIX path
               }
               // page/file/filename=phpvnc-master/id=146/path=media+page+files+14/tb='.$tb_files.'/name=phpvnc-master/ext=zip/counter=DOWNLOAD_COUNT

               $image_file = Crypt::file_url($image_file);

               echo '<li id="file-id-'.$file['ID'].'">';
         
               if($file['ID_PROVIDER']=='2') {
                   $href   = 'href="https://www.youtube.com/watch?v='.$file['LINK'].'"'; //&feature=player_embedded'.'"';
                   $thumb  = 'https://img.youtube.com/vi/'.$file['LINK'].'/mqdefault.jpg';
                   $type   = 'video';
                   $hclass = 'swipebox swipebox-video file-video';
                   echo '<a '.$href.'" class="'.$hclass.'">'.$file['NAME'].'</a>';     
               }else if($file['ID_PROVIDER']=='3') {
                   $hash = unserialize(file_get_contents("https://vimeo.com/api/v2/video/{$file['LINK']}.php"));
                   $href = 'href="https://vimeo.com/'.$file['LINK'].'"';
                   $thumb = $hash[0]['thumbnail_medium'];  
                   $type   = 'video';
                   $hclass = 'swipebox swipebox-video file-video';
                   $title = $hash[0]['title'];
                   echo '<a '.$href.'" class="'.$hclass.'">'.$file['NAME'].'</a>';     
               }else if($file['ID_PROVIDER']=='5')
                   echo '<a data-href="'.$image_file.'" class="file-pdf open_file_pdf gallery-desc" title="'.$file['NAME'].'">'.$file['NAME'].'</a>';     
               else if($file['ID_PROVIDER']=='10')
                   echo '<a class="file-epub gallery-desc" href="javascript:load_epub(\''.$image_file.'\')" title="'.$file['NAME'].'">'.$file['NAME'].'</a>';          
               else if($type=='txt')
                   echo '<a class="file-txt open_file_txt gallery-desc" data-href="'.$image_file.'" data-title="'.$file['NAME'].'">'.$file['NAME'].'</a>';                           
               else if($type=='XXtxt')
                   echo '<a class="file-txt gallery-desc" href="javascript:load_txt(\''.$image_file.'\')" title="'.$file['NAME'].'">'.$file['NAME'].'</a>';                           
               else
                   echo '<a class="file-'.$type.' gallery-desc" href="'.$image_file.'" title="'.$file['NAME'].'">'.$file['NAME'].'</a>';  
               if($editable /*&& $_ARGS[0]=='page'*/){
                   echo ' <i data-id="'.$file['ID'].'" class="edit_page_files fa fa-edit" title="Editar archivo" style="color:#007fad;"></i>';
                   echo ' <i data-id="'.$file['ID'].'" class="dele_page_files fa fa-trash-o" title="Eliminar archivo" style="color:#f50a51;"></i>';            /// sprintf(t('DOWNLOADS_%s'),$file['DOWNLOAD_COUNT']);
               }
               if(isset($file['DOWNLOAD_COUNT'])&&$file['DOWNLOAD_COUNT']>0) echo '<span class="files-download-count"><span>'.$file['DOWNLOAD_COUNT'].'</span> '.Inflect::pluralize(t('DOWNLOAD'),$file['DOWNLOAD_COUNT']).'</span>';
               echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }                  

    }
    if($TYPE=='gallery'){
        //$t = new TableMysql();
        //$images = $t->sql_query($sql_images);
        $images = Table::sqlQuery($sql_images);
        //echo $sql_images;

        if($images){
            echo '<p class="title">Galería de fotos '
                .'<i id="gallery-zoom-out" class="fa fa-minus" title="Reducir"></i> '
                .'<i id="gallery-zoom-in" class="fa fa-plus" title="Ampliar"></i> '
                .($editable?'<i id="gallery-reload" class="fa fa-refresh" title="Recargar"></i> ':'')
                .($editable?'<i id="gallery-add-files" class="fa fa-upload" title="Añadir imágenes"></i> ':'')
                .'</p><div id="gallery">';


            $_cur_categorie = '';
            foreach ($images as $image){

                if($_group_by_categories){ 

                    if($_cur_categorie != $image['CAT']){
                        echo '<h3>'.$image['CAT'].'</h3>';
                    }
                    $_cur_categorie = $image['CAT'];

                }

               $type     = Str::get_file_extension($image['FILE_NAME']); 
               $filename = Str::get_file_name($image['FILE_NAME']);  
               $name     = Str::sanitizeName(  $image['NAME'] );

               $ver = hash('crc32b',$image['LAST_UPDATE_DATE']??'');

               $image_tn   = SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.TN_PREFIX.$image['FILE_NAME'];

               if($_ARGS[0]=='page'){
                   $image_file     =  'page/file/filename='.$filename.'/id='.$image['ID'].'/path=media+page+files+'.$_ID_.'/tb='.$tb_files.'/name='.$name.'/ext='.$type.'/counter=DOWNLOAD_COUNT/mode=inline'; 
                   $image_file_big =  'page/file/filename='.$filename.'/id='.$image['ID'].'/path=media+page+files+'.$_ID_.'/tb='.$tb_files.'/name='.$name.'/ext='.$type.'/counter=DOWNLOAD_COUNT/mode=inline'; 
                   $image_file = Crypt::file_url($image_file);
                   $image_file_big = Crypt::file_url($image_file_big);
               }else{
                   $image_file = is_file(SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.BIG_PREFIX.$image['FILE_NAME'])
                               ? SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.BIG_PREFIX.$image['FILE_NAME']
                               : SCRIPT_DIR_MODULE_MEDIA.'/'.$_ID_.'/'.$image['FILE_NAME'];
               }

               $image_desc = $image['DESCRIPTION']?$image['DESCRIPTION']:$image['NAME'];
               echo '<div class="item'.($image['NAME']!=$filename?' item-desc':'').'" id="file-id-'.$image['ID'].'">'
                   .'<a rel="gallery" class="xxxswipebox" href="'.$image_file.'?ver='.$ver.'" title="'.$image_desc.'"><img src="'.$image_tn.'?ver='.$ver.'" alt="'.$image['NAME'].'"></a>';
               if ($image['NAME']!=$filename ) {
                   echo  '<span class="gallery-image-desc gallery-desc">'./*$image['ID'].'.'.*/$image['NAME'].'</span>';            
               }
               if(isset($image['DOWNLOAD_COUNT'])&&$image['DOWNLOAD_COUNT']>0) echo '<span class="gallery-download-count"    title="'.$image['DOWNLOAD_COUNT'].' '.Inflect::pluralize(t('DOWNLOAD'),$image['DOWNLOAD_COUNT']).'" >'.$image['DOWNLOAD_COUNT'].'</span>';
               if($editable /*&& $_ARGS[0]=='page'*/){
                   echo ' <i data-id="'.$image['ID'].'" class="edit_page_files fa fa-edit" title="Editar archivo" style="color:#007fad;"></i>';
                   echo ' <i data-id="'.$image['ID'].'" class="dele_page_files fa fa-trash-o" title="Eliminar archivo" style="color:#f50a51;"></i>';
               }
               echo '</div>';
            }
            echo '</div>';
        }                  

    }

    echo '<br>';
}

