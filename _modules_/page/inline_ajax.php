<?php

    include(SCRIPT_DIR_MODULE.'/functions.php');
    
    $result = array();
    $result['error'] = 0;

    $item_id=$_ARGS['id']; 

    // LOG::$messages['ajax_'.__LINE__] = $_ARGS['id']; //print_r($_ARGS,true);

    if ( Vars::IsNumeric($item_id) ){
   
        $editable = editable($item_id);

        if($editable) { 
            
            if(MODULE=='page') {

                define('SCRIPT_DIR_MODULE_MEDIA',SCRIPT_DIR_MEDIA.'/'.MODULE.'/files');        

            } else {

                $_TB_NAME_ = strtoupper(MODULE);
                $_TB_PREFIX_ = TB_PREFIX;  // $_TB_NAME_=='NEWS'?'NOT':($_TB_NAME_=='BLOG'?'BLG':$_TB_NAME_);
                define('SCRIPT_DIR_MODULE_MEDIA',SCRIPT_DIR_MEDIA.'/'.$_TB_NAME_.'/files');        

            }

            if       ($_ARGS['op'] == 'save_file'){
            
                $arr_file_types = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/webp'];
                
                $valid_img_ext = (in_array($_FILES['file']['type'], $arr_file_types)); 
                $ahora = time();
                $dir_media_module_images    = SCRIPT_DIR_MODULE_MEDIA;
                $dir_media_module_images_id = SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id;
                $filename = time() . '_' . Str::sanitizeName($_FILES['file']['name'],true);
                if (!file_exists(SCRIPT_DIR_MEDIA)) mkdir(SCRIPT_DIR_MEDIA, 0777);
                if (!file_exists($dir_media_module_images)) mkdir($dir_media_module_images, 0777);
                if (!file_exists($dir_media_module_images_id)) mkdir($dir_media_module_images_id, 0777);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $dir_media_module_images_id .'/'. $filename)){
                    chmod($dir_media_module_images_id .'/'. $filename, 0644 ); //FIX
                    if ($valid_img_ext){ 
                        $provider = '1';
                    //img_resize($dir_media_module_images_id , $filename);
                        miniatura($dir_media_module_images_id , $filename); 
                        if     ($_FILES['file']['type']=='image/png') png2webp( $dir_media_module_images_id.'/'.$filename );
                        else if($_FILES['file']['type']=='image/jpeg' 
                            || $_FILES['file']['type']=='image/jpg') jpeg2webp( $dir_media_module_images_id.'/'.$filename );
                    }else if($_FILES['file']['type']=='application/pdf'){
                        $provider = '5';
                    }else{
                        //$provider->values     = array('1'=>'Image', '2'=>'Youtube', '3'=>'Vimeo', '4'=>'Document', '5'=>'PDF', '6'=>'Google Drive File','7'=>'Google Drive Folder','8'=>'Google Form','9'=>'Google Presentation');
                        $provider = '4';
                    }

                    $sql  = MODULE=='page'
                        ? "INSERT INTO CLI_PAGES_FILES (id_item, file_date,NAME,FILE_NAME, ID_PROVIDER,ACTIVE) VALUES($item_id,$ahora,'$filename','$filename', '$provider', '1') "
                        : "INSERT INTO ".$_TB_PREFIX_.'_'.$_TB_NAME_."_FILES (".$_TB_NAME_."_ID, NAME, FILE_NAME, ID_PROVIDER, ACTIVE) VALUES($item_id,'$filename','$filename', '$provider', '1') ";   
                    //if ($insert_row) 
                    if (Table::sqlExec($sql))
                        $result['msg']  = "File uploaded successfully.";
                    else
                        $result['msg'] = Table::lastError(); //.$sql;

                    $ext = Str::get_file_extension($filename);
                    $result['sql']  = $sql;
                    $result['dir_upload']  = $dir_media_module_images_id;
                    $result['filename']  = $filename;
                    $result['url']  = SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.$filename;
                    $result['thumb']  = SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.TN_PREFIX.$filename;
                    $result['html']= '<div'
                            .  '      class = "gallery-item gallery-item-'.($valid_img_ext?'image':'file').' gallery-item-'.$ext.'"'
                            .  '      style = "background:url('.($valid_img_ext?SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.$filename:SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$ext.'.png').');background-size: contain;background-repeat:no-repeat;background-position:center;"'
                            .  ' data-thumb = "'.SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/.tn_'.$filename.'"'
                            .  '   data-src = "'.SCRIPT_DIR_MODULE_MEDIA.'/'.$item_id.'/'.$filename.'"'
                            .  '  data-desc = "'.($file['DESCRIPTION']?$file['DESCRIPTION']:$filename).'"'
                            .  '   data-ext = "'.($valid_img_ext?'image':'file').'"'
                            .  '>'
                            .  ($valid_img_ext?'<button class="nobtn nobtn-small" data-op="thumb">'.t('THUMB','Miniatura').'</button>':'')
                            .  ($valid_img_ext?'<button class="nobtn nobtn-small" data-op="image">'.t('IMAGE','Imagen').'</button>':'')
                            .  '<button class="nobtn nobtn-small" data-op="link">'.t('LINK','Enlace').'</button>'
                            .  '</div>';

                    $type = '8';
                    $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\''.MODULE.'_files - insert - inline\',\''.Table::escape(print_r($_FILES,true)."\n".$sql).'\')';
                    Table::sqlExec($log_sql);

                }else{
                    $result['msg'] .= t('ERROR_UPLOADING_IMAGE','Error al subir la imagen').' '.$_FILES['file']['tmp_name'];
                }

            }else if ($_ARGS['op'] == 'update_score'){

                // fetch('/page/ajax/op=update_score/user=' + currentUserId + '/newscore=' + score)
                // ALTER TABLE CLI_USER ADD COLUMN user_score int(10)
                $_new_score = intval($_ARGS['newscore']);
                if($_new_score>0)
                Table::sqlExec( 'UPDATE '.TB_USER.' SET user_score = '.$_new_score.' WHERE user_id= '.$_SESSION['userid'] );
     
                $result['error'] = 0;
                $result['msg']   = t('SCORE_UPDATED','Score actualizado'); // . $filePath;

            }else if ($_ARGS['op'] == 'save_page'){
                $_FNAME_ = MODULE=='page' ? 'item_text' : $_TB_PREFIX_.'_TEXT';

              //$field_text_name = $_ARGS['lang']?$_FNAME_.'_'.$_ARGS['lang']:$_FNAME_;
                $field_text_name  = $_SESSION['lang']==CFG::$vars['default_lang'] 
                                  ? $_FNAME_  
                                  : $_FNAME_.'_'.$_SESSION['lang'];
   
                $_encrypted_text = $_ARGS['text'];
                $_decrypted_text = Crypt::crypt2str($_encrypted_text,$_SESSION['token']);


                if($_decrypted_text === NULL){
    
                    $result['error'] = 1;
                    $result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION');

                }else{

                    $text = $_decrypted_text;

                    // En este punto si $_decrypted_text tiene emojis no funcionará el UPDATE sql
                    // necesitamos una función que convierta emojis de un texto a su código html
                    if (Str::has_emojis($text)) 
                        $text = Str::emoji_to_html($text);


                    //if(MODULE=='page')  {
                //      if(!Root()){
                        $AllowAttr = array('title', 'src', 'href', 'id', 'class',  'width', 'height', 'alt', 'target', 'align','placeholder', 'value', 'data-id', 'data-parent', 'type','for','step','min','max');  // 'style',
                        $AllowTag = array('a', 'img', 'br', 'strong', 'b', 'code', 'pre', 'p', 'div', 'em', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'style',
                         'table', 'colgroup', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'hr', 'li', 'u', 'input','button','textarea','figure','blockquote','figcaption');
                        //include(SCRIPT_DIR_CLASSES.'/xss.class.php');
                        $text = XSS::strip_trip($text, 'utf-8', $AllowTag, $AllowAttr);                    
                //      }
                    //}else{
                    //   include(SCRIPT_DIR_CLASSES.'/xss.class.php');
                    //   $text = XSS::strip_trip($text);
                    //}

                    
                    $search = array("[SCRIPT_DIR_MODULES]",  "[SCRIPT_DIR_THEMES]", "[SCRIPT_DIR_MEDIA]",'<<');
                    $replace = array(SCRIPT_DIR_MODULES,SCRIPT_DIR_THEMES,SCRIPT_DIR_MEDIA,'&lt;&lt;');
                    $text = Table::escape(str_replace($search, $replace, $text)); //.'<br>'.$log_sql ));
                    
                    //$text = str_replace(['<br>'  ,'><' , "style=\"background-color: initial;\"" ],
                    //                    ['<br />',">\n<", ''],$text);
                    //$text = str_replace(['<br>'  , "style=\"background-color: initial;\"" ],
                    //                    ['<br />', ''],$text);
                    
                    
                    if(MODULE=='page'){
                        if(isset($_ARGS['css'])  && isset($_ARGS['js']) && isset($_ARGS['code'])){
                            $text_css  = Table::escape(Crypt::crypt2str($_ARGS['css'] ,$_SESSION['token']));
                            $text_js   = Table::escape(Crypt::crypt2str($_ARGS['js']  ,$_SESSION['token']));
                            $text_code = Table::escape(Crypt::crypt2str($_ARGS['code'],$_SESSION['token']));
                            $upd_sql = "UPDATE ".TB_PAGES." SET ".$field_text_name."='".$text."',item_code='".$text_code."',item_code_js='".$text_js."',item_code_css='".$text_css."' WHERE item_id= '".$item_id."'";
                        }else{
                            $upd_sql = "UPDATE ".TB_PAGES." SET ".$field_text_name."='".$text."' WHERE item_id= '".$item_id."'";
                        }

                   // LOG::$messages['ajax_'.__LINE__] = 'Saving page id '.$item_id.' field '.$field_text_name;
                   // LOG::$messages['ajax_'.__LINE__] = 'SQL: '.$upd_sql;


                    }   
                    $sql = MODULE=='page'
                    ? $upd_sql
                    : "UPDATE ".$_TB_PREFIX_.'_'.$_TB_NAME_." SET ".$field_text_name."='".$text."' WHERE ".$_TB_PREFIX_."_ID= '".$item_id."'";
                    
                    $ok = Table::sqlExec($sql);
                    
                    if     ($ok==='0') $result['msg'] = t('NO_CHANGES_MADE');
                    else if($ok)       $result['msg'] = t('PAGE_SAVED_SUCCESSFULLY');//.$sql; //.print_r($item_id,true);
                    else               $result['msg'] = t('ERROR_SAVING_PAGE_TEXT').': '.Table::lastError();
                    $result['sql']  = $sql;
                    
                    $type = '8';
                    $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\''.MODULE.' - update - inline\',\''.Table::escape($sql).'\')';
                    Table::sqlExec($log_sql);
                    
                    //$result['msg']  .= '<br />'.$_SESSION['lang'].'::'.$_SESSION['tblang'];
                    //$result['msg']  .= $upd_sql . '<br />'; //.$_SESSION['lang'].'::'.$_SESSION['tblang'];
                    //$result['msg']  .='LANG '. print_r($_ARGS,true);
                    //$result['msg'] = 'SQL<br>'.$_SESSION['lang'].'<br>'.$sql;

                }
                //$result['msg']  .= '<br />'.$_SESSION['lang'].'::'.$_SESSION['tblang'];
                //$result['msg']  .= print_r($_ARGS['lang'],true);

            }else if ($_ARGS['op'] == 'clone_page' &&  MODULE=='page'){

                $title = $_ARGS['title'];
                $name = Str::sanitizeName($title);
                $ischild = $_ARGS['aschild'];
                $rows = Table::sqlQuery("SELECT item_id,item_name FROM ".TB_PAGES." WHERE item_name = (SELECT concat(p2.item_name,'-".$name."') FROM ".TB_PAGES." p2 WHERE p2.item_id=".$item_id.")");
                if($cfg['db']['type']  != 'mysql'){
                    $result['error']=1;
                    $result['msg']=t('OPTION_ONLY_AVAILABLE_FOR_MYSQL','Esta opción sólo está disponible para MySQL');
                }else if(count($rows)>0){
                    $result['error']=1;

                    $result['msg']=t('PAGE_WITH_NAME_ALREADY_EXISTS','Ya existe una página con el nombre').' '.$name;

                    if($_ARGS['addmenu']==1){

                        $rows = Table::sqlQuery("SELECT item_id,item_name FROM ".TB_PAGES." WHERE item_id=".$item_id);
                        $item_url = $rows[0]['item_name'].'-'.$name;

                        $row_menu = Table::sqlQuery("SELECT * FROM ".TB_ITEM." WHERE item_url = (SELECT item_name FROM ".TB_PAGES." WHERE item_id='".$item_id."')");
                        if($_ARGS['aschild']==1) $p = max('0',$row_menu[0]['item_id']);
                                           else  $p = max('0',$row_menu[0]['item_parent']); 
                        $sql_menu = "INSERT INTO ".TB_ITEM." ( item_parent,item_caption,item_name,item_url,id_menu,item_active,item_visible,item_order) 
                                                    VALUES ('".$p."','".$title."','". $row_menu[0]['item_name'].'-'.$name."','".$item_url."','".max('0',$row_menu[0]['id_menu'])."','1','1','".max('0',$row_menu[0]['item_order'])."')";
                        Table::sqlExec($sql_menu);
                        $result['msg'] .= '. '.t('ADDED_TO_MENU','Se ha añadido al menú.'); //.$sql_menu; 

                        $type = '8';
                        $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'menu - insert -inline\',\''.Table::escape($sql_menu).'\')';
                        Table::sqlExec($log_sql);

                    }

                }else if(Str::is_friendly_name($name, 3, 100)){
                    Table::sqlExec("CREATE TEMPORARY TABLE temp_table AS SELECT * FROM ".TB_PAGES." WHERE item_id=".$item_id);
                    Table::sqlExec("UPDATE temp_table SET item_id=0,item_name=concat(item_name,'-','".$name."'),item_title='".$title."' WHERE item_id=".$item_id);
                    Table::sqlExec("INSERT INTO ".TB_PAGES." SELECT * FROM temp_table");
                    Table::sqlExec("DROP TEMPORARY TABLE temp_table");

                    
                    $item_url = Table::sqlQuery("SELECT item_name FROM ".TB_PAGES." WHERE  item_id=(SELECT MAX(item_id) FROM ".TB_PAGES.")")[0]['item_name'];

                    $type = '8';
                    $sql_page = "CREATE TEMPORARY TABLE temp_table AS SELECT * FROM ".TB_PAGES." WHERE item_id=".$item_id."\n"
                              . "UPDATE temp_table SET item_id=0,item_name=concat(item_name,'-','".$name."'),item_title='".$title."' WHERE item_id=".$item_id."\n"
                              . "INSERT INTO ".TB_PAGES." SELECT * FROM temp_table"."\n"
                              . "DROP TEMPORARY TABLE temp_table"."\n";

                    $log_sql_page = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'page - insert - inline\',\''.Table::escape($sql_page).'\')';
                    Table::sqlExec($log_sql_page);


                    if($_ARGS['addmenu']==1){
                        $row_menu = Table::sqlQuery("SELECT * FROM ".TB_ITEM." WHERE item_url = (SELECT item_name FROM ".TB_PAGES." WHERE item_id='".$item_id."')");
                        if($_ARGS['aschild']==1) $p = max('0',$row_menu[0]['item_id']);
                                        else  $p = max('0',$row_menu[0]['item_parent']); 
                        $sql_menu = "INSERT INTO ".TB_ITEM." ( item_parent,item_caption,item_name,item_url,id_menu,item_active,item_visible,item_order) 
                                                    VALUES ('".$p."','".$title."','". $row_menu[0]['item_name'].'-'.$name."','".$item_url."','".max('0',$row_menu[0]['id_menu'])."','1','1','".max('0',$row_menu[0]['item_order'])."')";
                        Table::sqlExec($sql_menu);

                        $type = '8';
                        $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'menu - insert -inline\',\''.Table::escape($sql_menu).'\')';
                        Table::sqlExec($log_sql);

                    }

                    $result['msg']=t('PAGE_ADDED','Página añadida'); // .$sql_menu;
                    $result['name']=$name;
                }else{
                    $result['error']=1;
                    $result['msg']=t('INVALID_NAME','Nombre no válido').': '.$name;
                }





            }else if ($_ARGS['op'] == 'image-crop'){

               // Establecer el límite de tamaño de archivo (por ejemplo, 5 MB)  //FIX set from CFG var
               $maxFileSize = 2 * 1024 * 1024; // 5 MB  

               // Extensiones permitidas
               $allowedExtensions = ['jpg', 'webp', 'jpeg', 'png', 'gif'];

               if (isset($_FILES['croppedImage']) && $_FILES['croppedImage']['error'] === UPLOAD_ERR_OK) {
                        
                    $result['src'] = explode('?',$_POST['src'])[0];
                    
                    $file = $_FILES['croppedImage'];
                    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            
                    if ($_POST['saveas']){

                        //if($_POST['saveas']==='random') {
                        //    $newFileName = uniqid('img_'/*, true*/) . '.' . $fileExtension;
                        //    $newFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $newFileName);                
                        //    $filePath = $newFileName;         
                        //}else{
                            $_url = 'https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR;
                            $filePath = str_replace( $_url, '.', $_POST['saveas'] );
                        //}
                        $result['src'] = $filePath ;

                    }else {

                        $filePath = $result['src'];  

                    }

                    // Validar el tamaño del archivo           
                    if ($file['size'] > $maxFileSize) {
                        
                    $result['msg'] = t('FILE_TOO_LARGE','El archivo es demasiado grande') . ' ('.$file['size'].' > '.$maxFileSize.'). '.t('MAX_SIZE_IS','El tamaño máximo es').' 5 MB';

                        // Validar la extensión del archivo
                    } else if (!in_array($fileExtension, $allowedExtensions)) {

                        $result['msg'] = t('FILE_FORMAT_NOT_ALLOWED','Formato de archivo no permitido. Solo se permiten archivos').' JPG, PNG, WEBP y GIF.';

                    } else if (!file_exists($file['tmp_name'])) {

                        $result['msg'] = t('FILE_DOES_NOT_EXIST','No existe el archivo').' '.$file['tmp_name'];

                        //}else if (!is_writable('/media/[MODULE]/files/11'))

                        //    $result['msg'] = t('CANNOT_WRITE_TO_DIRECTORY','No se puede escribir en /media/[MODULE]/files/11');

                    } else if (move_uploaded_file($file['tmp_name'], $filePath)) {

                        $result['error'] = 0;
                        $result['msg']   = t('IMAGE_UPLOADED_SUCCESSFULLY','La imagen se ha subido correctamente. Ruta') . ': ' . $filePath;
                        $result['image'] = $filePath;

                    } else {

                        $result['msg']   = t('ERROR_UPLOADING_IMAGE','Hubo un error al subir la imagen.').' '.$filePath;

                    }
                        
               } else {

                        $result['msg']   = t('NO_IMAGE_RECEIVED','No se recibió ninguna imagen o hubo un error en la subida.');
                        
               }

            }else if ($_ARGS['op'] == 'files-gallery'){

                $_ID_ = $item_id;

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
                                    .  ' data-parent = "'.$_file[MODULE=='page'?'id_item':TB_NAME.'_ID'].'"'
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
                $result['html'] = $html_files;

                                
            }else{

            // include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');

            }
    
        }

    }else{

        $result['error'] = 1;
        $result['msg'] = t('ID_NOT_VALID');

    }

    echo json_encode($result);
