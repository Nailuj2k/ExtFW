<?php 

$TYPE = $_ARGS['type']?$_ARGS['type']:'1';
if($TYPE=='1'){

    if (in_array($_ARGS[1],array_merge(['view','theme','lang','output','debug','control_panel','login'],CFG::$vars['outputs'],CFG::$vars['langs'])) )  $_ARGS[1]=false; 

    //if($_ARGS[1]===false&&CFG::$vars['default_module']=='page') $_ARGS[1]=CFG::$vars['default_page']?CFG::$vars['default_page']:'home';

    if ($_ARGS[1]){

        $SCRIPT_DIR_MODULE_MEDIA=SCRIPT_DIR_MEDIA.'/page/files';
        $field_text_name = $_SESSION['lang']=='es'?'item_text':'item_text_'.$_SESSION['lang'];
        $field_title_name = $_SESSION['lang']=='es'?'item_title':'item_title_'.$_SESSION['lang'];

        $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES." WHERE item_name= '".Str::sanitizeName($_ARGS[1])."'");

        if(count($rows)==1){
           
            $item_id = $rows[0]['item_id']; 
            $item_text = $rows[0][$field_text_name];
            if($_SESSION['lang']!='es' && !$item_text) $item_text = $rows[0]['item_text'];
            //Vars::debug_var($rows[0]);
                        
            //echo '<h3>'.$SCRIPT_DIR_MODULE_MEDIA.'</h3>';
            $style_gallery = '#html-gallery {margin-top:20px;}'
                           . '#html-gallery .item{display: inline-block;text-align:center; border: 1px solid #eee; width: 150px; height: 200px;  margin: 5px; padding: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.1);vertical-align: top;/*align-content:center;*/}'
                           . '#html-gallery .item img{max-width:145px;max-height:170px;}'
                           . '#html-gallery .gallery-image-desc{font-size:10px;font-family:Arial;}'
                           . '#html-gallery .gallery-desc{color:#666;}'
                           . '#html-gallery .gallery-download-count{font-size:10px; color:#999; margin-left:5px;}';


            echo '<div id="content_'.$rows[0]['item_name'].'">';
            echo '<style>'.$rows[0]['item_code_css'].$style_gallery.'</style>'.$item_text; //str_replace('"'.$SCRIPT_DIR_MODULE_MEDIA,'"/'.$SCRIPT_DIR_MODULE_MEDIA,$item_text);
            //echo '<pre>'.$rows[0]['item_code_css'].'</pre>';

            if($rows[0]['GALLERY']=='1'){
                $sql_images = "SELECT ID,NAME,DOWNLOAD_COUNT,ID_PROVIDER,FILE_NAME,DESCRIPTION,MINI,MAIN,ACTIVE,ITEM_ORDER,LAST_UPDATE_DATE FROM CLI_PAGES_FILES WHERE id_item = $item_id AND MAIN <>'1' AND MINI<>'1' AND ACTIVE='1' AND ID_PROVIDER='1' ORDER BY ITEM_ORDER,ID";
                $images = Table::sqlQuery($sql_images);
                //echo $sql_images;
                if($images){
                    echo '<div id="html-gallery">';
                    foreach ($images as $image){
                       $type     = Str::get_file_extension($image['FILE_NAME']); 
                       $filename = Str::get_file_name($image['FILE_NAME']);  
                       $name     = Str::sanitizeName(  $image['NAME'] );

                       $ver = hash('crc32b',$image['LAST_UPDATE_DATE']);
                       $image_tn   = $SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.TN_PREFIX.$image['FILE_NAME'];

                       if($_ARGS[0]=='page'){
                           $image_file     =  'page/file/filename='.$filename.'/id='.$image['ID'].'/path=media+page+files+'.$item_id.'/tb='.$tb_files.'/name='.$name.'/ext='.$type.'/counter=DOWNLOAD_COUNT/mode=inline'; 
                           $image_file_big =  'page/file/filename='.$filename.'/id='.$image['ID'].'/path=media+page+files+'.$item_id.'/tb='.$tb_files.'/name='.$name.'/ext='.$type.'/counter=DOWNLOAD_COUNT/mode=inline'; 
                           $image_file = Crypt::file_url($image_file);
                           $image_file_big = Crypt::file_url($image_file_big);

                       }else{
                           $image_file = is_file($SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.BIG_PREFIX.$image['FILE_NAME'])
                                       ? $SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.BIG_PREFIX.$image['FILE_NAME']
                                       : $SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.$image['FILE_NAME'];
                       }
                       $image_desc = $image['DESCRIPTION'];
                       echo '<div class="item'.($image['NAME']!=$filename?' item-desc':'').'" id="row-id-'.$image['ID'].'" style="">'
                           .'<a rel="gallery" class="open_file_image" href="'.$image_file.'?ver='.$ver.'" title="'.$image_desc.'"><img style="" src="'.$image_tn.'?ver='.$ver.'" alt="'.$image['NAME'].'"></a>';
                       if ($image['NAME']!=$filename ) {
                           echo  '<br><span style="" class="gallery-image-desc gallery-desc">'./*$image['ID'].'.'.*/$image['NAME'].'</span>';            
                       }
                       //if(isset($image['DOWNLOAD_COUNT'])&&$image['DOWNLOAD_COUNT']>0) echo '<span class="gallery-download-count"    title="'.$image['DOWNLOAD_COUNT'].' '.Inflect::pluralize(t('DOWNLOAD'),$image['DOWNLOAD_COUNT']).'" >'.$image['DOWNLOAD_COUNT'].'</span>';
                       echo '</div>';
                    }
                    echo '</div>';
                }  

              
            }

            echo '</div>' ;


        }elseif( count($rows)<1 && $_ACL->HasPermission('edit_items') ){

            ?>
            <h3>404 Página no encontrada</h3>
            <p>La página <b><?=$_ARGS[1]?></b> no existe</p>
            <?php 

        }

    }else{

        echo '<h2>'.t('PAGES').'</h2>';
        $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES);
        foreach($rows as $row){
            echo '<a href="'.$row['item_name'].'">'.$row['item_title'].'</a><br />';     
        }

    }

}else{

   include(SCRIPT_DIR_MODULES.'/page/inline_widget.php');

}