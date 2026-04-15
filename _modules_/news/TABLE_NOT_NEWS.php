<?php 
       
//$_SESSION['lang']=$_SESSION['tblang'];

$tabla = new TableMysql(TB_TABLE);
$tabla->profile = ($_ARGS['target']);

/*
$langs=array();
if (CFG::$vars['site']['langs']['enabled']===true){
    $query_langs =  $tabla->sql_query('select lang_id,lang_cc,lang_name from '.TB_LANG.' where lang_active=1 and lang_id>1');
    while ($row_lang = $tabla->sql_fetch($query_langs)) {
        $langs[$row_lang['lang_cc']]=$row_lang['lang_name'];
    }
}
*/

$id            = new Field();
$id->type      = 'int';
$id->width     = 15;
$id->len       = 10;
$id->fieldname = TB_PREFIX.'_ID';
$id->label     = 'Id';   
$id->hide      = true;
/*
$sql_users =  "SELECT user_id AS ID, CONCAT(username,' - ',IFNULL(user_fullname,'..')) AS NAME FROM ".TB_USER
            . " WHERE user_id IN ("
            . "  SELECT id_user FROM ".TB_ACL_USER_ROLES." WHERE id_role IN ("
            . "    SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_name IN ('Root','Administradores','Editores','Autores)"
            . "  )"
            . ") ";
*/
$sql_users = "SELECT u.user_id AS ID,CONCAT(u.username,' - ',IFNULL(u.user_fullname,'..')) AS NAME FROM ".TB_USER." u "
           . "  WHERE u.user_id IN ( "
           . "    SELECT DISTINCT(ur.id_user) FROM ACL_USER_ROLES ur WHERE ur.id_role IN ( "
           . "      SELECT rp.id_role FROM ACL_ROLE_PERMS rp WHERE rp.id_permission IN ( "
           . "        SELECT p.permission_id FROM ACL_PERMISSIONS p WHERE p.permission_key IN ( "
           . "          '".MODULE."_add','".MODULE."_edit','".MODULE."_admin' "
           . "  ))))";

            
$sql_users_all =  $sql_user . " AND u.user_active=1 ORDER BY NAME"; 
$sql_users    .=  $sql_user . " ORDER BY NAME"; 

// echo $sql_users;

$id_user = new Field();
$id_user->fieldname = 'USER_ID';
$id_user->label     = 'Usuario'; 
$id_user->len       = 5;
$id_user->type      = 'select';
$id_user->values     =  $tabla->toarray('users',     $sql_users,true);
$id_user->values_all =  $tabla->toarray('users_all', $sql_users_all,true); //"CONCAT(user_name,' -  ',user_fullname)"
$id_user->editable  = Administrador();
$id_user->multiselect  = true;
$id_user->default_value = $_SESSION['userid'];
//$id_user->width     = 55;
$id_user->filtrable = true;  


$date = new Field();
$date->type      = 'date';
$date->fieldname = TB_PREFIX.'_DATE';
$date->label     = 'Fecha';   
$date->editable  = Administrador();   
$date->sortable  = true;   

$title = new Field();
$title->fieldname = TB_PREFIX.'_TITLE';
$title->label     = t('TITLE');   
$title->len       = 200;
$title->type      = 'varchar';
$title->editable  = Administrador();
$title->searchable = true;
$title->filtrable = true;
$title->placeholder = t('EMPTY_NOT_SHOW_IN_THIS_LANG','En blanco para desactivar en este idioma');
if(count($tabla->langs)>0) {
    $title->translatable = true;  
    $title->langs =  $tabla->langs;
}

$subtitle = new Field();
$subtitle->fieldname = TB_PREFIX.'_SUBTITLE';
$subtitle->label     = t('SUBTITLE');   
$subtitle->len       = 300;
$subtitle->type      = 'varchar';
$subtitle->editable  = Administrador();
$subtitle->searchable = true;
$subtitle->filtrable = true;
if(count($tabla->langs)>0) {
    $subtitle->translatable = true;  
    $subtitle->langs =  $tabla->langs;
}

$name = new Field();
$name->fieldname = TB_PREFIX.'_NAME';
$name->label     = 'Slug';   
$name->len       = 200;
$name->type      = 'varchar';
$name->editable  = Administrador();
$name->searchable = true;
$name->filtrable = true;
$name->textafter = '<span style="font-size:0.8em;line-height: 33px;vertical-align: top;">URL amigable, sin espacios ni caracteres especiales.</span>';
if(count($tabla->langs)>0) {
    $name->translatable = true;  
    $name->langs =  $tabla->langs;
}

/*
$filename = new Field();
$filename->fieldname = TB_PREFIX.'_FILENAME';
$filename->label     = 'Imagen';   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = './media/'.TB_NAME.'/images';
$filename->accepted_doc_extensions = array('.png','.jpg','.gif');
$filename->textafter = 'Formato JPG, PNG o GIF';
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';
*/

$intro = new Field();
$intro->type      = 'textarea';
$intro->fieldname = TB_PREFIX.'_INTRO';
$intro->label     = 'Resumen';   
$intro->editable = Administrador();
//$intro->hide     = true;
$intro->searchable = true;
$intro->fieldset = 'entradilla';

$text = new Field();
$text->type      = 'textarea';
$text->fieldname = TB_PREFIX.'_TEXT';
$text->label     = 'Contenido';   
$text->editable = Administrador();
$text->hide     = true;
$text->searchable = true;
$text->fieldset = 'texto';
$text->wysiwyg=true;
if(count($tabla->langs)>0) {
    $text->translatable = true;  
    $text->langs =  $tabla->langs;
}


$class = new Field();
//$class->type      = 'varchar';
$class->type      = 'select';
$class->values    = array(/*1 column 1 fila*/ '1'=>'2 columnas 1 fila','2'=>'2 columnas 2 filas','3'=>'3 columnas 2 filas','4'=>'4 columnas 2 filas', '5'=>'1 columna 2 filas',  '6'=>'1 columna 4 filas');
$class->fieldname = TB_PREFIX.'_CLASS';
$class->label     = 'class';   
$class->len       = 100;
$class->allowNull =true;
$class->editable = Administrador();

$color = new Field();
$color->fieldname = 'COLOR';
$color->label     = 'Color';   
$color->type      = 'color';
$color->width     = 40;
$color->editable  = Administrador();

$order = new Field();
$order->type      = 'int';
$order->fieldname = TB_PREFIX.'_ORDER';
$order->label     = 'Orden';   
$order->len       = 5;

$top = new Field();
$top->type      = 'bool';
$top->default_value = '0';
$top->fieldname = TB_PREFIX.'_TOP';
$top->label     = 'Destacada';   
$top->editable  = Administrador();   

$video = new Field();
$video->type      = 'bool';
$video->default_value = '0';
$video->fieldname = 'VIDEO';
$video->label     = 'Vídeo';   
$video->editable  = Administrador();   

$top_img = new Field();
$top_img->type      = 'bool';
$top_img->default_value = '1';
$top_img->fieldname = 'TOP_IMAGE';
$top_img->label     = 'Imagen de cabecera';   
$top_img->editable  = Administrador();   

$gallery = new Field();
$gallery->type      = 'bool';
$gallery->default_value = '0';
$gallery->fieldname = 'GALLERY';
$gallery->label     = 'Galería de fotos';   
$gallery->editable  = Administrador();   

$files = new Field();
$files->type      = 'bool';
$files->default_value = '0';
$files->fieldname = 'FILES';
$files->label     = 'Archivos Anexos';   
$files->editable  = Administrador();   


$comments = new Field();
$comments->type      = 'bool';
$comments->default_value = '0';
$comments->fieldname = 'ALLOW_COMMENTS';
$comments->label     = 'Habilitar comentarios';   
$comments->default_value = '1';   
$comments->editable  = Administrador();   
$comments->hide = true;

$rating = new Field();
$rating->type      = 'bool';
$rating->default_value = '0';
$rating->fieldname = 'ALLOW_RATING';
$rating->label     = 'Habilitar Rating';
$rating->default_value = '1';   
$rating->editable  = Administrador();   
$rating->hide = true;

$reads = new Field();
$reads->type      = 'int';
$reads->width     = 30;
//$reads->default_value = 0;
$reads->fieldname = 'VIEWS';
$reads->label     = 'Lecturas';   
$reads->editable  = Administrador();   
$reads->readonly=true;

$sign = new Field();
$sign->fieldname = 'SIGN';
$sign->label     = t('SIGN','Firma');   
$sign->len       = 40;
$sign->type      = 'varchar';
$sign->editable  = Administrador();
$sign->searchable = true;

$url = new Field();
$url->fieldname = 'URL';
$url->label     = 'Url';   
$url->len       = 100;
$url->type      = 'varchar';
$url->editable  = Administrador();
$url->searchable = true;

/**/
$tags = new Field();
$tags->type      = 'varchar';
$tags->fieldname = 'TAGS';
$tags->calculated=true;

/**/
$tabla->title = t(MODULE);
$tabla->showtitle = true;

$tabla->output='grid';
$tabla->page = $page;
$tabla->page_num_items = 60;

$tabla->addCol($id);
$tabla->addCol($id_user);
$tabla->addCol($title);
$tabla->addCol($subtitle);
$tabla->addCol($name);
$tabla->addCol($date);
//$tabla->addCol($ref);
//$tabla->addCol($color);
///////////////////////////////////////////$tabla->addCol($class);
//$tabla->addCol($filename);
//$tabla->addCol($intro);
//$tabla->addCol($count);
$tabla->addCol($text);
$tabla->addCol($top);
$tabla->addCol($video);
$tabla->addCol($top_img);
$tabla->addCol($gallery);
$tabla->addCol($files);
$tabla->addCol($comments);
$tabla->addCol($rating);
$tabla->addCol($sign);
$tabla->addCol($url);
$tabla->addCol($reads);

$tabla->addCol($tags);

$tabla->addCols([
    $tabla->field(        'KEYWORDS',  'textarea' )->wysiwyg(false)->label('Keywords')->fieldset('SEO'), //->height(4),
    $tabla->field(        'DESCRIPTION',  'textarea' )->wysiwyg(false)->label('Description')->fieldset('SEO')// ->height(4)
]);

$tabla->addWhoColumns();
$tabla->addActiveCol();
//$tabla->download_count_fieldname = $count->fieldname;
$tabla->inline_edit = false;
/**/
$tabla->default_filter = array(); // 'RESOURCE_ID' => $tabla->operatorId, 'CLOSED' => 'F' );

//$tabla->classname       = 'datatable-news';//tea-modules'; //table table-bordered table-striped datatable-rows';     // 'table-bordered';
$tabla->show_empty_rows = false;    
    

$tabla->markup_header_title= false;//'<tr><th class="tb-title" colspan="%1s">%2s</th></tr>';  
$tabla->markup_header_row  = false;//'<tr id="%1s" class="%2s">%3s<th></th></tr>';  
$tabla->markup_header_cell = false;//'<th id="%1s" class="%2s" style="%s3">%4s</th>';  


$tabla->NOmarkup_footer_row  = '<div class="inner_footer noshadow">[CONTENT]</div>'; 
$tabla->markup_footer_row  = '<div class="inner_footer">[CONTENT]</div>';  
$tabla->NOmarkup_footer_row  = '<div class="table-footer"><div colspan="%1s"><div style="position:relative">%2s</div></div></div>';  

$tabla->NOmarkup = '<div id="[ID]" class="mkp-list tb_id">'
                . '[BODY]'
                . '</div>'
                . '[FOOTER]'
                . '<div class="ajax-loader" style="display:none;"><div class="loader"></div></div>'
                ;  
$tabla->markup = '<div id="[ID]" class="tb_id NNmkp-list [CLASS]">[COLGROUP]'
               . '<div id="tbody" class="clearfix">[BODY]</div>'
               . '</div>'
               . '[FOOTER]'  
             //. '<div class="ajax-loader" style="display:none;"><div class="loader"></div></div>'
               ;    
    
$tabla->NOmarkup_row  = '<div id="row-[ID]" class="mkp-item div_item cell shadow [CLASS]">'
                    . '<div class="cells">[CELLS]</div>'
                    . '<div class="actions">[ACTIONS]</div>'
                    . '</div>';
$tabla->markup_row  = '<div id="row-[ID]" class="mkp-item div_item cell NOshadow [CLASS]">[CELLS]<div class="actions">[ACTIONS]</div></div>';

$tabla->NOmarkup_cell  = '<span id="[ID]" class="[CLASS]" style="[STYLE]" data-fieldname="[FIELDNAME]" data-precission="[PRECISSION]" data-th="[LABEL]" val="[VAL]">[CONTENT]</span>';  
$tabla->markup_cell        = '<div id="[ID]" class="[CLASS]" style="[STYLE]" data-th="[LABEL]" val="[VAL]">[CONTENT]</div>';  
$tabla->NOmarkup_cell_empty   = '<div></div>';  

$tabla->markup_row_empty  = '<div class="codepen-item row-empty">[CONTENT]</div>';
$tabla->NOmarkup_row_empty    = '<div class="row-empty">[CONTENT]<div class="actions"><span class="actions"><img src="_images_/pixel.gif"></span></div></div>';







$tabla->orderby = TB_PREFIX.'_TOP DESC,'.TB_PREFIX.'_DATE DESC';

//$allow = array('147.84.199.32','5.59.21.223'); //,'156.67.63.179'); 
//if (!in_array ($_SERVER['REMOTE_ADDR'], $allow)) {
if (Administrador() && $_SESSION[$tabla->tablename]['view']=='all'){

    
}else{

    $tabla->where = "ACTIVE = '1'";    

    $tabla->where .= ' AND '.TB_PREFIX.'_DATE <= CURRENT_DATE';

    if (CFG::$vars['modules'][MODULE]['selected_langs'])
    $tabla->where .= ' AND '.TB_PREFIX.'_TITLE'. ($_SESSION['lang']!=CFG::$vars['default_lang'] ? '_'.$_SESSION['lang']:'')." <> ''";

  //$tabla->detail_tables=array();
  //$tabla->detail_tables[]=TB_PREFIX.'_'.TB_NAME.'_FILES';
}




/**/
$tabla->perms['view']   = true; //(Usuario()||Administrador());
//$tabla->perms['filter'] = true;//(Usuario()||Administrador());
$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador(); 
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();  
$tabla->perms['reload'] = true;  
//$tabla->perms['show'] = true;  

$tabla->log =true;
//$tabla->verbose=true;

class notEvents extends defaultTableEventsTags implements iEvents{ 

    function OnCalculate($owner,&$row){
        $row['TAGS']='tags!';
    }

    //function OnAfterDrawRow($owner,&$row,&$markup){
    //    $a = array('[ID]' => $row['FILE_ID'],
    //               '[ORDER]' => $row['DOC_ORDER']);
    //    $markup = str_replace( array_keys($a), array_values($a), $markup) ;
    //}

    function OnBeforeShow($owner){


            /*
            $str_select_sortable_cols = '<select id="select_sortable_cols" style="max-width:200px;">';
            foreach($owner->cols as $col) {
                if($col->sortable)
                $str_select_sortable_cols .= '<option value="'.$col->fieldname.'">'.$col->label.'</option>';
            }               
            $str_select_sortable_cols .= '</select>';
            echo $str_select_sortable_cols;
            */
            ?>
            <script type="text/javascript">
               // console.log('OnBeforeShow TABLE_NOT_NEWS');
            </script>
            <?php 
    }

    function OnShow($owner){
            ?>
            <script type="text/javascript">
               // console.log('OnShow TABLE_NOT_NEWS');
            </script>
            <?php 
    }


/*
    $sql_img = 'SELECT NAME,ID_PROVIDER,FILE_NAME,'.$field_desc.' FROM '.TB_PREFIX.'_'.TB_NAME.'_FILES WHERE '.TB_NAME.'_ID='.$row[TB_PREFIX.'_ID'].' AND MAIN=\'1\' ORDER BY ID DESC';   
    $images = Table::sqlQuery($sql_img);
    $image = './media/'.TB_NAME.'/images/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'];
    $image_desc = $images[0]['DESCRIPTION'];

*/
    function OnDrawRow($owner,&$row,&$class){
        //global $items_translated;
        parent::OnDrawRow($owner,$row,$class);
        $ver=hash('crc32b',$row['LAST_UPDATE_DATE']);
        if(count($row['A_TAG_NAMES'])>0) $class.=' '.implode(" ", $row['A_TAG_NAMES']);
        if(count($row['A_TAG_LABELS'])>0){
            $row['TAGS'] =  '<span class="labels">'.implode(', ',$row['A_TAG_LABELS']).'</span>'; 
            //$row[TB_PREFIX.'_TITLE'] = $row[TB_PREFIX.'_TITLE'].'<div>' . $row['TAGS'].'</div>'; 
        }else $row['TAGS']='';
       
        
        if( defined(TB_NAME.'_TOP')) $class .= ' not_std';
        else $class .=  $row[TB_PREFIX.'_TOP']=='1' ? ' not_top' : ' not_std';

        $row[TB_PREFIX.'_TITLE'] = $_SESSION['lang']==CFG::$vars['default_lang'] 
                          ? ($row[TB_PREFIX.'_TITLE'] ? $row[TB_PREFIX.'_TITLE']  : $row[TB_PREFIX.'_TITLE_en']  )
                          : ($row[TB_PREFIX.'_TITLE_'.$_SESSION['lang']] ? $row[TB_PREFIX.'_TITLE_'.$_SESSION['lang']] : $row[TB_PREFIX.'_TITLE']) ;

        $row[TB_PREFIX.'_NAME'] = $_SESSION['lang']==CFG::$vars['default_lang'] 
                          ? $row[TB_PREFIX.'_NAME'] 
                          : ($row[TB_PREFIX.'_NAME_'.$_SESSION['lang']] ? $row[TB_PREFIX.'_NAME_'.$_SESSION['lang']] : $row[TB_PREFIX.'_NAME']) ;

        $_intro = $_SESSION['lang']==CFG::$vars['default_lang'] 
                          ? ($row[TB_PREFIX.'_TEXT'] ? $row[TB_PREFIX.'_TEXT']  : $row[TB_PREFIX.'_TEXT_en']  )
                          : ($row[TB_PREFIX.'_TEXT_'.$_SESSION['lang']] ? $row[TB_PREFIX.'_TEXT_'.$_SESSION['lang']] : $row[TB_PREFIX.'_TEXT']) ;

        $row[TB_PREFIX.'_INTRO']=Str::truncate(strip_tags($_intro),220,  '...', false);

        //if($items_translated&&count($items_translated)>0)
        //    $mm = str_replace( array_values($items_translated[$_SESSION['lang']]), array_keys($items_translated[$_SESSION['lang']]), MODULE);
        //else
            $mm =  $_module = CFG::$vars['default_module'] == MODULE ? '' : MODULE;
        $row[TB_PREFIX.'_TITLE'] = '<a class="div_'.TB_PREFIX.'_TITLE" href="'.Vars::mkUrl($mm,$row[TB_PREFIX.'_NAME']).'" title="'.$row[TB_PREFIX.'_TITLE'].'">'.$row[TB_PREFIX.'_TITLE'].'</a>'
	                  .'<div class="div_'.TB_PREFIX.'_INTRO">'.$row[TB_PREFIX.'_INTRO'].'</div>';


        if(!$row['ACTIVE'])  $class .= ' inactive';
        if(date('Y-m-d') < $row[TB_PREFIX.'_DATE']) $class .= ' inactive';

        $row[TB_PREFIX.'_DATE'] = /**$_SESSION['lang'].'::'.**/DateTime::createFromFormat('Y-m-d', $row[TB_PREFIX.'_DATE'])->format('d / m / Y'); // => 2013-12-24

	//Vars::debug_var(  defined(TB_NAME.'_TABLE_TAGS') /*? '.TB_NAME.'_TABLE_TAGS : 'CLI_TAGS'*/,'defined('.TB_NAME.'_TABLE_TAG)');
	//Vars::debug_var(  '.TB_NAME.'_TABLE_TAGS,TB_NAME.'_TABLE_TAG');
	//Vars::debug_var(  '.TB_NAME.'_TOP,TB_NAME.'_TOP');


        if(defined(TB_NAME.'_TOP') || $row[TB_PREFIX.'_TOP']!=='1') {
            $sql_img = 'SELECT NAME,ID_PROVIDER,FILE_NAME FROM '.TB_PREFIX.'_'.TB_NAME.'_FILES WHERE '.TB_NAME.'_ID='.$row[TB_PREFIX.'_ID'].' AND MINI=\'1\' ORDER BY ID DESC';   
            $images = Table::sqlQuery($sql_img);
            $image = $images ? './media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'] : '';
        }else{
            $class .= ' image';

            //if ($_SESSION['lang']=='es'){ //CFG::$vars['default_lang']){
            //        $field_name = 'NAME'; 
            //}else{
            //        $field_name = "COALESCE(NULLIF(NAME_".$_SESSION['lang'].",''), NAME) AS NAME";
            //}
            //$sql_img = 'SELECT '.$field_name.',ID_PROVIDER,FILE_NAME FROM '.TB_PREFIX.'_'.TB_NAME.'_FILES WHERE '.TB_NAME.'_ID='.$row[TB_PREFIX.'_ID'].' AND MAIN=\'1\' ORDER BY ID DESC';   
            $sql_img = 'SELECT NAME,ID_PROVIDER,FILE_NAME FROM '.TB_PREFIX.'_'.TB_NAME.'_FILES WHERE '.TB_NAME.'_ID='.$row[TB_PREFIX.'_ID'].' AND MAIN=\'1\' ORDER BY ID DESC';   
            $images = Table::sqlQuery($sql_img);
            //if ($images[0]['ID_PROVIDER']=='2'){
            //    $class .= ' video';
            //    $video ='<div style="position: relative; display:block;width:1140px;height:600px;"><iframe style="z-index:-1" frameborder="0" height="100%" width="100%"  src="https://youtube.com/embed/'.$images[0]['NAME'].'?autoplay=1&controls=0&showinfo=0&autohide=1"></iframe></div>';
            //}else{

            if($images){
                $image_big =  './media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.BIG_PREFIX.$images[0]['FILE_NAME'];
                $image =  file_exists($image_big) ? $image_big : './media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'];
            }//else{
                $class .= ' no-img';
           // }
                //    $image = './media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'];
            //}
        }
        
        if($images){
            $row[TB_PREFIX.'_TITLE'].= '<style>#row-'.$row[TB_PREFIX.'_ID'].'{background: url('.$image.'?ver='.$ver.');</style>'; 
        }else{
            $class .= ' no-img';
        }



        //$row[TB_PREFIX.'_INTRO']='</div><pre>'.print_r($row,true).'</pre><div>';
        //$row[TB_PREFIX.'_FILENAME']=false;
        $row[TB_PREFIX.'_COLOR']=false;
        $row[TB_PREFIX.'_INTRO']=false;
        $row[TB_PREFIX.'_TEXT']=false;
        $row[TB_PREFIX.'_TOP']=false;
        $row[TB_PREFIX.'_NAME']=false;
        $row[TB_PREFIX.'_SUBTITLE']=false;
        $row['KEYWORDS']=false;
        $row['DESCRIPTION']=false;

                $row['VIEWS']=' ';
                $row['USER_ID']=' ';
                $row['SIGN']=false;

        //$row['USER_ID']=false;
        //$row['ALLOW_COMMENTS']=false;
        //$row['ALLOW_RATING']=false;
        //$row['ACTIVE']=false;
        //$row['VIDEO']=false;
        //$row[TB_PREFIX.'_DATE']=$row[TB_PREFIX.'_DATE'].' '.$classes[$row[TB_PREFIX.'_CLASS']];
    }

    function OnBeforeShowForm($owner,&$form,$id) {
        if($owner->state=='filter')return false;
        parent::OnBeforeShowForm($owner,$form,$id);
        foreach( $owner->cols as $col) { if($col->fieldname == TB_PREFIX.'_DATE') $col->default_value =  $owner->sql_currentdate(); }
        if($owner->profile=='module')return false;
        if($id && $owner->perms['edit']) {
             Table::$module_name = MODULE;
             // Table::init();
             $parent = $id;
             $markup_ajax_loader = '<p style="text-align:center;border:1px solid green;">Loading ...</p>';        
             $html_item_actions = new formElementHtml();
             $html_item_actions->html = '<div class="datatable" id="T-'.TB_TABLE.'_FILES">'.$markup_ajax_loader.'</div>'
                                     .  '<script>'
                                     .  '    load_page("'.MODULE.'","'.TB_TABLE.'_FILES",1,'.$id.',1);'
                                     .  '   console.log(\' load_page("'.MODULE.'","'.TB_TABLE.'_FILES",1,'.$id.',1);\')'
                                     .  '</script>' ;                                     
             Table::show_table(TB_TABLE.'_FILES',MODULE,false);
             $fs_item_actions = new fieldset('files','Archivos');
             $fs_item_actions->displaytype = 'tab';
             $fs_item_actions->addElement($html_item_actions);
             $form->addElement($fs_item_actions);
        }
    }

    function OnDelete($owner,&$result,$id){ 
        parent::OnDelete($owner,$result,$id);
    }

    function OnBeforeInsert($owner){}

    function OnAfterInsert($owner,&$result,&$post){
        if($result['last_insert_id']) $post[TB_PREFIX.'_ID'] = $result['last_insert_id'];
        parent::OnAfterInsert($owner,$result,$post);
    }

    function OnAfterUpdate($owner,&$result,&$post){ 
        parent::OnAfterUpdate($owner,$result,$post);
    }

    function OnInsert($owner,&$result,&$post) { 
        $post[TB_PREFIX.'_TITLE'] = str_replace(array(' "','" ','".','",') ,array(' “','” ','”.','”,'),$post[TB_PREFIX.'_TITLE']);
        if ($post[TB_PREFIX.'_TOP']=='1') Table::sqlExec("UPDATE ".TB_PREFIX."_".TB_NAME." SET ".TB_PREFIX."_TOP='0'");
        $post['READS'] = '0';
        ////$result['the_filename']='header_'.$post['item_name'].'.jpg';
        if(!$post[TB_PREFIX.'_NAME']) $post[TB_PREFIX.'_NAME'] = Str::sanitizeName($post[TB_PREFIX.'_TITLE']);
        if (CFG::$vars['options']['change_underscores'])
            $post[TB_PREFIX.'_NAME'] = Str::sanitizeName(str_replace('_','-',$post[TB_PREFIX.'_NAME']));
        else
            $post[TB_PREFIX.'_NAME'] = Str::sanitizeName($post[TB_PREFIX.'_NAME']);
    }
      
    function OnUpdate($owner,&$result,&$post) {
        $post[TB_PREFIX.'_TITLE'] = str_replace(array(' "','" ','".','",') ,array(' “','” ','”.','”,'),$post[TB_PREFIX.'_TITLE']);
        $post[TB_PREFIX.'_SUBTITLE'] = str_replace(array(' "','" ','".','",') ,array(' “','” ','”.','”,'),$post[TB_PREFIX.'_SUBTITLE']);
        if ($post[TB_PREFIX.'_TOP']=='1') Table::sqlExec("UPDATE ".TB_PREFIX."_".TB_NAME." SET ".TB_PREFIX."_TOP='0' WHERE ".TB_PREFIX."_ID<>{$post[TB_PREFIX.'_ID']}");
        if(!$post[TB_PREFIX.'_NAME']) $post[TB_PREFIX.'_NAME'] = Str::sanitizeName($post[TB_PREFIX.'_TITLE']);
        if (CFG::$vars['options']['change_underscores'])
            $post[TB_PREFIX.'_NAME'] = Str::sanitizeName(str_replace('_','-',$post[TB_PREFIX.'_NAME']));
        else
            $post[TB_PREFIX.'_NAME'] = Str::sanitizeName($post[TB_PREFIX.'_NAME']);


        /*
        $f = $owner->colByName(TB_PREFIX.'_FILENAME');//->uploaddir // = './media/'.TB_NAME.'/images';
     //   Str::get_file_extension();
        $result['the_filename']=$post[TB_PREFIX.'_ID'].'.jpg';
        if($post['FILE_NAME']==''){
            unlink($f->uploaddir.'/'.$result['the_filename']);
            unlink($f->uploaddir.'/'.TN_PREFIX.$result['the_filename']);
        }   
        */       
    }

    function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
        //if  ($col->fieldname=='FILE_NAME')    $result['local_file'] = $result['the_filename'];
    }


    function OnAfterShowForm($owner,&$form,$id){  
    ?>


    <?php
    }

    function OnAfterShow($owner){ 
    
    }

    function OnDrawCell($owner,&$row,&$col,&$cell){
        if ($col->fieldname==TB_PREFIX.'_CLASS') $cell='';
    }
  

}

$tabla->events = New notEvents();

/*
$tabla->events->tb_tags            = TB_PREFIX.'_TAGS';      //TSK_TAGS
$tabla->events->tb_tags_pk         = 'ID';
$tabla->events->tb_tags_name       = 'NAME';
$tabla->events->tb_tags_color      = 'COLOR';
$tabla->events->tb_tags_caption    = 'CAPTION';

$tabla->events->tb_items_tags      = 'TSK_TASKS_TAGS';       //TASK_TASKS_TAGS
$tabla->events->tb_items_tags_pk   = 'TASK_TAG_ID';      //TASK_TAG_ID
$tabla->events->tb_items_tags_item = 'TASK_ID';          //TASK_ID
$tabla->events->tb_items_tags_tag  = 'TAG_ID';               //TAG_ID

$tabla->events->tb_tags_displaytype = 'tab';  //'float';      //TSK_TAGS
$tabla->events->tb_tags_tabname     = 'tabs';
$tabla->events->tb_tags_tablabel    = 'Etiquetas';
*/
/**/
$tabla->events->tb_tags            = /*defined(''.TB_NAME.'_TABLE_TAG')         ? TB_NAME.'_TABLE_TAG         :*/ TB_PREFIX.'_TAGS';
$tabla->events->tb_tags_pk         = /*defined(''.TB_NAME.'_TABLE_TAG_PK')      ? TB_NAME.'_TABLE_TAG_PK      :*/ 'TAG_ID';
$tabla->events->tb_tags_name       = /*defined(''.TB_NAME.'_TABLE_TAG_NAME')    ? TB_NAME.'_TABLE_TAG_NAME    :*/ 'NAME';
$tabla->events->tb_tags_caption    = /*defined(''.TB_NAME.'_TABLE_TAG_CAPTION') ? TB_NAME.'_TABLE_TAG_CAPTION :*/ 'CAPTION';
$tabla->events->tb_tags_color      = /*defined(''.TB_NAME.'_TABLE_TAG_COLOR')   ? TB_NAME.'_TABLE_TAG_COLOR   :*/ 'COLOR';

$tabla->events->tb_items_tags      = TB_PREFIX.'_'.TB_NAME.'_TAGS';       //TASK_TASKS_TAGS
$tabla->events->tb_items_tags_pk   = TB_NAME.'_TAG_ID';      //TASK_TAG_ID
$tabla->events->tb_items_tags_item = TB_NAME.'_ID';          //TASK_ID
$tabla->events->tb_items_tags_tag  = 'TAG_ID';               //TAG_ID

$tabla->events->tb_tags_displaytype = 'tab';  //'float';      //TSK_TAGS
$tabla->events->tb_tags_tabname     = 'tabs';
$tabla->events->tb_tags_tablabel    = 'Etiquetas';
/**/
//$tabla->detail_tables=array(TB_PREFIX.'_'.TB_NAME.'_TAGS');


/*
Tabla: CLI_TAGS
Columna: ID int(5) unsigned NOT NULL auto_increment
Columna: CAPTION varchar(50)
Columna: NAME varchar(100)
Columna: HEADER_FILE_NAME varchar(200)
Columna: FILE_NAME varchar(200)
Columna: COLOR varchar(12)
Columna: DESCRIPTION text
Columna: ID_ORDER int(8)
Columna: ACTIVE int(1)
Columna: CREATED_BY int(5)
Columna: CREATION_DATE datetime
Columna: LAST_UPDATED_BY int(5)
Columna: LAST_UPDATE_DATE datetime
*/