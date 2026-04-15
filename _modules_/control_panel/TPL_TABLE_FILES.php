<?php 

$tabla->profile = isset($_ARGS['target'])?$_ARGS['target']:($tabla->profile?$tabla->profile:'default');
$tabla->tb_categories = $tabla->tb_categories ? $tabla->tb_categories : 'CLI_CATEGORIES';

$tabla->fk               = $tabla->fk                       ? $tabla->fk               : false; 
$tabla->uploaddir        = $tabla->uploaddir                ? $tabla->uploaddir        : 'media/'.$tabla->tablename.'/files';
$tabla->order            = $tabla->order===false            ? false                    : true; 
$tabla->sanitize_title   = $tabla->sanitize_title===false   ? false                    : true; 
$tabla->module           = $tabla->module                   ? $tabla->module           : MODULE;
$tabla->hash_filenames   = $tabla->hash_filenames           ? $tabla->hash_filenames   : false; 
$tabla->link_cfg         = $tabla->link_cfg                 ? $tabla->link_cfg         : false; 
$tabla->link_upload_files= $tabla->link_upload_files ?? true;  
$tabla->btn_download     = $tabla->btn_download     ??  true; 

//$tabla->translatable   = $tabla->translatable===false ? false                  : true; 
$tabla->epub           = $tabla->epub                   ? $tabla->epub           : false; 

$tabla->main          = $tabla->main         ? $tabla->main                  : false; 
$tabla->mini          = $tabla->mini         ? $tabla->mini                  : false; 

if(!$tabla->fk) unset($_SESSION['PAGE_FILES_ID_PARENT']);    

$id            = new Field();
$id->type      = 'int';
$id->len       = 7;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide    = $tabla->profile=='inline';

$fk = new Field();
$fk->type      = 'int';
$fk->len       = 7;
$fk->fieldname = $tabla->fk;
$fk->label     = 'Item id';
//$fk->editable  = true ;
$fk->hide  = $tabla->profile=='inline';

$name = new Field();
$name->fieldname = 'NAME';
$name->css_id    = 'IMAGE_NAME';
$name->label     = t('TITLE','Título');   
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  = true;
$name->size      = 70;
//$name->inline_edit   = true;
$name->width      = 400;
if(count($tabla->langs)>0) {
    $name->id = $tabla->tablename.'-'.$name->fieldname;
    $name->translatable = true;  
    $name->langs =  $tabla->langs;
}
$name->required = true;  
$name->searchable = true;  

$date = new Field();
$date->type      = 'date';
$date->fieldname = 'FILE_DATE';
$date->label     = t('DATE','Fecha');   
$date->editable  = true;   
//$date->readonly  = true;   

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = t('DOCUMENT','Documento');   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  = true;
$filename->uploaddir =  $tabla->uploaddir;
$filename->parent_id = true;
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';
if($tabla->accepted_img_extensions) $filename->accepted_img_extensions = $tabla->accepted_img_extensions; //array('.jpg','.png','.gif','.webp','.mp3','.zip','.pdf'); //FIX in include
if($tabla->accepted_doc_extensions) $filename->accepted_doc_extensions = $tabla->accepted_doc_extensions; //array('.jpg','.png','.gif','.webp','.mp3','.zip','.pdf'); //FIX in include
                         else 
                            $filename->accepted_doc_extensions = CFG::$vars['accepted_doc_extensions'];

$filename->watermark = isset($tabla->watermark)  ? $tabla->watermark : false;  // '../../media/images/watermark.png';


$thumbnail = new Field();
$thumbnail->fieldname = 'FILE_THUMB';
$thumbnail->label     = t('THUMBNAIL','Miniatura');   
$thumbnail->len       = 100;
$thumbnail->type      = 'file';
$thumbnail->editable  = true;
$thumbnail->uploaddir =  $tabla->uploaddir.'/thumbs';
$thumbnail->parent_id = true;
$thumbnail->action_if_exists_disabled = true;
$thumbnail->action_if_exists = 'replace';
if($tabla->accepted_doc_extensions) $thumbnail->accepted_doc_extensions = array('.jpg','.png','.gif','.webp'); //FIX in include

if ($tabla->group||$tabla->categories){
    $categorie = new Field();
    $categorie->fieldname = 'ID_CATEGORIE';
    $categorie->label     = t('CATEGORY','Categoría').' '.$parent; 
    $categorie->type      = 'select';
    $categorie->len       = 5;
    $categorie->width     = 105;

//  $_SESSION['PAGE_FILES_PARENT'] = 0;


//Vars::debug_var( $tabla->cats );
//Vars::debug_var("SELECT CATEGORIE_ID AS ID, NAME FROM CLI_CATEGORIES WHERE ACTIVE=1 AND CATEGORIE_ID IN (SELECT DISTINCT(CATEGORIE_ID) FROM ".$tabla->tablename." WHERE ".$tabla->fk." = ".$_SESSION['PAGE_FILES_ID_PARENT'].")    ORDER BY CAT_ORDER");
    if(isset($_SESSION['PAGE_FILES_ID_PARENT'])&&$_SESSION['PAGE_FILES_ID_PARENT']>0){
        $categorie->values          =  $tabla->toarray('categories',         "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND ID_PARENT  = ".$_SESSION['PAGE_FILES_ID_PARENT']." ORDER BY CAT_ORDER",true); 
        $categorie->values_all      =  $tabla->toarray('categories_all',     "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND ID_PARENT  = ".$_SESSION['PAGE_FILES_ID_PARENT']." ORDER BY CAT_ORDER",true); 
//        $categorie->values_visibles =  $tabla->toarray('categories_visible', "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND ID_PARENT  = ".$_SESSION['PAGE_FILES_ID_PARENT'].") AND CATEGORIE_ID IN (SELECT DISTINCT(ID_CATEGORIE) FROM ".$tabla->tablename." WHERE ".$tabla->fk." = ".$_SESSION['PAGE_FILES_ID_PARENT'].") AND VISIBLE=1 ORDER BY CAT_ORDER",true); 
        $categorie->values_visibles =  $tabla->toarray('categories_visible', "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1  AND CATEGORIE_ID IN (SELECT DISTINCT(ID_CATEGORIE) FROM ".$tabla->tablename." WHERE ".$tabla->fk." = ".$_SESSION['PAGE_FILES_ID_PARENT'].") AND VISIBLE=1 ORDER BY CAT_ORDER",true); 
    }elseif($parent){
        $categorie->values          =  $tabla->toarray('categories',         "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND ID_PARENT=".$parent." ORDER BY CAT_ORDER",true); 
        $categorie->values_all      =  $tabla->toarray('categories_all',     "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND ID_PARENT=".$parent." ORDER BY CAT_ORDER",true); 
        $categorie->values_visibles =  $tabla->toarray('categories_visible', "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND VISIBLE=1 AND CATEGORIE_ID IN (SELECT DISTINCT(ID_CATEGORIE) FROM ".$tabla->tablename.") ORDER BY CAT_ORDER",true); 
    }else{
        $categorie->values          =  $tabla->toarray('categories',         "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 ORDER BY CAT_ORDER",true); 
        $categorie->values_all      =  $tabla->toarray('categories_all',     "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 ORDER BY CAT_ORDER",true); 
        $categorie->values_visibles =  $tabla->toarray('categories_visible', "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND VISIBLE=1 AND CATEGORIE_ID IN (SELECT DISTINCT(ID_CATEGORIE) FROM ".$tabla->tablename.") ORDER BY CAT_ORDER",true); 
    }

//Vars::debug_var( $parent , 'parent');



//Vars::debug_var( "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1 AND ID_PARENT=".$parent." ORDER BY CAT_ORDER" , 'sql');
//Vars::debug_var( $_SESSION['PAGE_FILES_ID_PARENT'] , 'PAGE_FILES_ID_PARENT');
//Vars::debug_var($categorie->values,'values');
       // $categorie->values_visibles =  $tabla->toarray('categories_visible', "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1  AND VISIBLE=1 AND CATEGORIE_ID IN (SELECT DISTINCT(ID_CATEGORIE) FROM ".$tabla->tablename.") ORDER BY CAT_ORDER",true); 


//  $categorie->values_all      =  $tabla->toarray('categories_all',     "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories."                              ORDER BY CAT_ORDER",true); 
       // $categorie->values_visibles =  $tabla->toarray('categories_visible', "SELECT CATEGORIE_ID AS ID, NAME FROM ".$tabla->tb_categories." WHERE ACTIVE=1  AND VISIBLE=1 ORDER BY CAT_ORDER",true); 
    $categorie->default_value = 1;
    //$categorie->classname = 'fullname';
    //$categorie->filtrable = true;  
    //$categorie->hide   = true;
    $categorie->editable   = true;
    $categorie->allowNull  = false;
    $categorie->default_value='1';
}


$provider = new Field();
$provider->fieldname = 'ID_PROVIDER';
$provider->label     = t('TYPE','Tipo'); 
$provider->type      = 'select';
$provider->len       = 5;
$provider->width     = 105;
//$provider->fieldset='tipo';

$provider->values     = array('1'=>'Image', '2'=>'Youtube', '3'=>'Vimeo', '4'=>'Document', '5'=>'PDF', '6'=>'Google Drive File','7'=>'Google Drive Folder','8'=>'Google Form','9'=>'Google Presentation','10'=>'Epub','11'=>'URL');
 //$tipo->values        =  array('1'=>'Imagen','2'=>'Principal','3'=>'Miniatura','4'=>'Youtube','5'=>'Vimeo');       //NEWS
 //$provider->values     = array('1'=>'Image', '2'=>'Youtube', '3'=>'Vimeo', '4'=>'Local', '5'=>'PDF', '6'=>'Remote'); //NEWS

/**
$provider->values          =  $tabla->toarray('providers',         "SELECT PROVIDER_ID AS ID, NAME FROM CFG_FILES_PROVIDER WHERE ACTIVE=1               ORDER BY TORDER",true); 
$provider->values_all      =  $tabla->toarray('providers_all',     "SELECT PROVIDER_ID AS ID, NAME FROM CFG_FILES_PROVIDER                              ORDER BY TORDER",true); 
$provider->values_visibles =  $tabla->toarray('providers_visible', "SELECT PROVIDER_ID AS ID, NAME FROM CFG_FILES_PROVIDER WHERE ACTIVE=1 AND VISIBLE=1 ORDER BY TORDER",true); 
$provider->default_value = 1;
$provider->classname = 'fullname';
//$provider->filtrable = true;  
**/

//$provider->hide   = true;
$provider->editable   = true;
$provider->allowNull  = false;
$provider->default_value='1';
//$provider->textafter='<div class="info">No suba imágenes mas grandes de 2mb.</div>';
//$provider->child='ISBN10';
$provider->javascript = '$("#ID_PROVIDER").change(function(){'               
                        . '    yes=$(this).val()==\'10\';'
                        . '    if (yes) $("#ISBN13,#ISBN10").closest(".control-group").show("fast");'
                        . '        else $("#ISBN13,#ISBN10").closest(".control-group").hide("fast");'
                        . '});'
                        . '$("#ID_PROVIDER").change();';

$link = new Field();
$link->type      = 'varchar';
$link->fieldname = 'LINK';
$link->label     = 'Link';   
$link->editable = true;
$link->hide     = true;
$link->searchable = true;
$link->filtrable = false;  
$link->len = 200;  
$link->width = 310;  
$link->placeholder = t('PLACEHOLDER_LINK','Ponga aquí un link de Google drive, Youtube o Vimeo (de momento) ');  

$thumb = new Field();
$thumb->fieldname = 'THUMBNAIL';
$thumb->label     = '';   
$thumb->len       = 200;
$thumb->width     = 46;
$thumb->type      = 'varchar';
//$thumb->editable  = $_ACL->hasPermission('pedidos_admin');
$thumb->calculated  = true;

if($tabla->mini) {
$mini = new Field();
$mini->type      = 'bool';
$mini->fieldname = 'MINI';
$mini->label     = t('THUMBNAIL','Miniatura');   
$mini->default_value = '0';
$mini->editable  = true;
$mini->width = 25;
$mini->default_value='0';
$mini->textafter = t('USE_THIS_IMAGE_AS_THUMBNAIL','Usar ésta imagen como miniatura');
$mini->hide = !$tabla->fk;
}

if($tabla->main) {
$main = new Field();
$main->type      = 'bool';
$main->fieldname = 'MAIN';
$main->label     = t('MAIN','Principal');   
$main->default_value = '0';
$main->editable  = true;
$main->width = 25;
$main->default_value='0';
$main->textafter = t('USE_THIS_IMAGE_AS_MAIN','Usar ésta imagen como principal');
$main->hide = !$tabla->fk;
}

$isbn10 = new Field();
$isbn10->type      = 'varchar';
$isbn10->fieldname = 'ISBN10';
$isbn10->label     = 'ISBN 10';   
$isbn10->editable = true;
$isbn10->hide     = true;
$isbn10->searchable = true;
$isbn10->filtrable = false;  
$isbn10->len = 10;  
//$isbn10->width = 400;


$isbn13 = new Field();
$isbn13->type      = 'varchar';
$isbn13->fieldname = 'ISBN13';
$isbn13->label     = 'ISBN 13';   
$isbn13->editable = true;
$isbn13->hide     = true;
$isbn13->searchable = true;
$isbn13->filtrable = false;  
$isbn13->len = 13;  
$isbn13->textafter = '<a id="btn-cover-get"  class="btn btn-primary btn-secondary btn-small" style="margin-left:10px;min-width:auto;">Get cover</a>'
                   . '<a id="btn-cover-save" class="btn btn-primary btn-secondary btn-small" style="margin-left:10px;min-width:auto;display:none;">Save</a>'
                   ;

$epub_cover = new Field();
$epub_cover->type='html';
$epub_cover->html=' <img id="epub_cover" style="position:absolute;bottom:4px;right:4px;min-width:100px;min-height:100px;max-width:150px;border:1px solid red;">';

$description = new Field();
$description->type      = 'varchar';
$description->fieldname = 'DESCRIPTION';
$description->css_id    = 'IMAGE_DESCRIPTION';
$description->label     = t('DESCRIPTION','Descripción');   
$description->editable = true;
$description->hide     = true;
$description->searchable = true;
$description->filtrable = false;  
$description->len = 200;  
//$description->height = 50;  
//$description->wysiwyg = false;  
if(count($tabla->langs)>0) {
    $description->id = $tabla->tablename.'-'.$description->fieldname;
    $description->translatable = true;  
    $description->langs =  $tabla->langs;
}

$text = new Field();
$text->type      = 'textarea';
$text->fieldname = 'T3XT';
$text->label     = t('TEXT','Texto');   
$text->editable = true;
$text->hide     = true;
$text->searchable = true;
$text->filtrable = false;  
//$text->len = 200;  
$text->height = 50;  
$text->wysiwyg = false;  
if(count($tabla->langs)>0) {
    $text->id = $tabla->tablename.'-'.$text->fieldname;
    $text->translatable = true;  
    $text->langs =  $tabla->langs;
}

if($tabla->download_count){
$tabla->download_count_fieldname = $tabla->download_count_fieldname?$tabla->download_count_fieldname:'DOWNLOAD_COUNT';
$count = new Field();
$count->type      = 'int';
$count->len       = 7;
$count->width     = 30;
$count->default_value = '0';
$count->fieldname = $tabla->download_count_fieldname;
$count->label     = t('DOWNLOADS','Descargas');   
$count->editable  = true;   
$count->hide=true;
//$count->readonly=true;

}
$order = new Field();
$order->fieldname = 'ITEM_ORDER';
$order->label     = t('ORDER','Orden');   
$order->type      = 'int';
$order->len       = 5;
$order->width     = 20;
$order->editable  = true;
$order->sortable  = true;
$order->hide  = true; ////////////////////////////// !Administrador();

$tabla->title = t('FILES','Archivos').' '.$parent; //.$tabla->profile;
$tabla->showtitle = true;

$tabla->page = $page;

$tabla->addCol($id);
if($tabla->fk) $tabla->addCol($fk);
//if($tabla->epub)$tabla->gallery_mode=true;
if(!$tabla->gallery_mode)  $tabla->addCol($thumb);
$tabla->addCol($name);


if ($tabla->group||$tabla->categories)
    $tabla->addCol($categorie);

if($tabla->file_date)
    $tabla->addCol($date);
$tabla->addCol($filename);
if($tabla->thumbnail)
    $tabla->addCol($thumbnail);
//$tabla->addCol($tipo);
$tabla->addCol($provider);
$tabla->addCol($link);
if($tabla->profile=='default'){
    if($tabla->fk && $tabla->mini) $tabla->addCol($mini);
    if($tabla->fk && $tabla->main) $tabla->addCol($main);
    $tabla->addCol($description);
    //if($tabla->download_count)$tabla->addCol($count);
   // if($tabla->order) $tabla->addCol($order);
}
if($tabla->download_count)$tabla->addCol($count);
if($tabla->order) $tabla->addCol($order);
if($tabla->text)
    $tabla->addCol($text);
if($tabla->epub){
    $tabla->addCol($isbn10);
    $tabla->addCol($isbn13);
    $tabla->addCol($epub_cover);
}

$btn_download = new Field();
$btn_download->fieldname = 'BTN_DOWNLOAD';
$btn_download->label     = '';   
$btn_download->len       = 4;
$btn_download->width     = 20;
$btn_download->type      = 'varchar';
$btn_download->calculated  = true;

if($tabla->table_tags)   {   
    $tabla->gallery_mode=($_SESSION[$tabla->tablename]['view']=='thumbs');
}else if($tabla->profile=='inline'){
    $tabla->link_gallery_mode =false;
    $tabla->show_inputsearch =false;
}else{
    $tabla->link_gallery_mode = $tabla->link_gallery_mode===false?false:!$tabla->table_tags; 
}

if ($tabla->group)
    $tabla->link_gallery_mode = false;

//TEST
//$tabla->link_gallery_mode = true;

if($tabla->link_gallery_mode)
    $tabla->gallery_mode= $_SESSION['_CACHE'][$tabla->tablename]['gallery_mode'];


    if ($tabla->group){

        //$atags   =  $tabla->asArray("SELECT ID,CAPTION,NAME,COLOR FROM CLI_TAGS WHERE ID IN (SELECT DISTINCT(TAG_ID) FROM CLI_PAGES_FILES_TAGS  )"); 
        $tabla->markup_group_title = '<div class="kbn_title">[TITLE]</div>'
                                   . '<p class="labels"  id="links-filters" style="position:absolute;right:0;top:-20px;">';
        //foreach ($atags as $k=>$v) $tabla->markup_group_title .= '<span class="label" data-class="'.$v['NAME'].'" title="'.$v['CAPTION'].'" style="background-color:'.$v['COLOR'].'">'.$v['CAPTION'].'</span>';
        $tabla->markup_group_title .= '<span class="label" data-class="all" title="'.t('ALL','Todo').'" style="background-color:#9EACBC;">'.t('ALL','Todo').'</span></p>';


        $tabla->output='group';  //table
        $tabla->field_group = $categorie;
        $tabla->field_group_order = $order;
        $tabla->table_group = $tabla->tb_categories;
        $tabla->table_group_pk_fieldname = 'CATEGORIE_ID ';
        $tabla->table_group_fieldname = 'CAT_ORDER';
        $tabla->field_title='NAME';
        //$tabla->show_inputsearch =false;


    }else if($tabla->gallery_mode){ 

        $tabla->output='grid';
        $tabla->page_num_items = 25;
        $tabla->show_empty_rows = false;
        $tabla->show_inputsearch =false;
        $tabla->classname = 'achosi';

        $tabla->markup         = ''  
                               . '<div id="datatable-body" class="table-gallery">[BODY]</div>'
                               . '<div id="datatable-footer" style="position:relative;display:block;/*z-index:20000;*/border:0px solid yellow; bottom:-18px; right:0px;">[FOOTER]</div>'
                               ;  
        $tabla->markup_header_title= ''; //'<h3 style="display:none;">[TITLE]</h3>';  
        $tabla->markup_header_row  = ''; //'<h4 style="display:none;"  id="[ID]" class="[CLASS]">[CONTENT]</h4>';
        $tabla->markup_header_cell = ''; //'<span id="[ID]" class="[CLASS]" title="[HINT]" style="[STYLE]">[CONTENT]</span>';  
        $tabla->markup_footer_row  = '<span class="table-footer">[CONTENT]</span>';  

        //$tabla->markup_row         = '<div id="[ID]" class="cell [CLASS]">[CELLS]';
        //$tabla->markup_row        .= '<div class="actions"><div>[ACTIONS]</div></div></div>';
        //$tabla->markup_row         = '<span style="display:none;" id="xx[ID]" class="cell [CLASS]"></span>';

      // $tabla->markup_row       = '[CELLS]';
        $tabla->markup_row         = '<div id="[ID]" class="div_item [CLASS]">[CELLS]</div>';

        //$tabla->markup_cell        = '<div id="[ID]" class="[CLASS]"val="[VAL]">[CONTENT]</div>';    
        $tabla->markup_cell      = '[CONTENT]'; 
        if($tabla->fk) {   
            if($tabla->mini) $tabla->colByName('MINI')->hide=true;
            if($tabla->main) $tabla->colByName('MAIN')->hide=true;
        }

    }else{
        $tabla->output='table';
        $tabla->page_num_items = $tabla->page_num_items?$tabla->page_num_items:5;
        $tabla->show_empty_rows = $tabla->show_empty_rows===false?false:true;
        $tabla->page = $page;
        if ($tabla->btn_download===true)
            $tabla->addCol($btn_download);
        $tabla->colByName('NAME')->hide=true;

        $tabla->orderby=$tabla->order?'ITEM_ORDER':($tabla->orderby?$tabla->orderby:'ID');
        if($tabla->order){
            $tabla->table_group= $tabla->tablename;
            $tabla->table_group_fieldname = 'ITEM_ORDER';
            $tabla->table_group_pk_fieldname = 'ID'; //$tabla->fk;
        }
    }   //if not group

//$tabla->verbose=true;

$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->colByName('ACTIVE')->hide=true; //!Administrador();

//$tabla->verbose=true;

if($tabla->table_tags)   {   
    $tabla->where = $_SESSION[$tabla->tablename]['tag'] ? 'ID IN (SELECT FILE_ID FROM '.$tabla->tablename.'_TAGS WHERE TAG_ID = (SELECT ID FROM '.$tabla->table_tags.' WHERE NAME=\''.$_SESSION[$tabla->tablename]['tag'].'\'))' : '';
}

if($tabla->profile=='default'||$tabla->profile=='files'){
$tabla->perms['filter'] = false;
$tabla->perms['show']   = true;
$tabla->perms['reload'] = true;
$tabla->perms['add']    = Administrador() || Usuario();
$tabla->perms['delete'] = Administrador() || Usuario();
$tabla->perms['edit']   = Administrador() || Usuario();
$tabla->perms['setup']  = Administrador();  
}

if ($tabla->epub) {
//    $tabla->gallery_mode=true;
    $tabla->perms['filter'] = true;
    $tabla->colByName('ID_PROVIDER')->filtrable=true;
    $tabla->default_filter="ID_PROVIDER='10'";
}

if($tabla->group)
$tabla->perms['add']    = false;


class filesEvents extends defaultTableEventsTags implements iEvents{ 

    function OnCalculate($owner,&$row){
        if ($owner->group){
        }else if($owner->gallery_mode){ 
        }else{ 
            $filename = Str::get_file_name($row['FILE_NAME']);
            $name = Str::sanitizeName( /*  Str::get_file_name($row['NAME'])*/ $row['NAME']  /*   ,true*/);
            $ext = Str::get_file_extension($row['FILE_NAME']);
           // if($_ACL->hasPermission('doku_download')) 
            if($row['ID_PROVIDER']=='6') {      
                $cloud_url = 'https://drive.google.com/file/d/'.$row['LINK'].'';
            }else if($row['ID_PROVIDER']=='7') {
                $cloud_url = 'https://drive.google.com/folderview?id='.$row['LINK'].'';
            }else if($row['ID_PROVIDER']=='8') {
                $cloud_url = 'https://forms.gle/'.$row['LINK'].'';
            }else if($row['ID_PROVIDER']=='9') {
                $cloud_url = 'https://docs.google.com/presentation/d/'.$row['LINK'].'';
            }else if($row['ID_PROVIDER']=='11') {
                $cloud_url = $row['LINK'].'';
            }else if($row['ID_PROVIDER']=='2') {
                $download_url = 'https://www.youtube.com/watch?v='.$row['LINK'].'&feature=player_embedded';
                $class = 'open_file_video';
            }else if($row['ID_PROVIDER']=='3') {      
                $download_url = 'https://vimeo.com/'.$row['LINK'];
                $class = 'open_file_video';
            }else{

                // $this->parent_key = $fieldname;        // Foreing Key for act as detail table - false or fieldname
                // $this->parent_value
                $download_url = $owner->module.'/file/filename='.$filename.'/id='.$row['ID'].'/path='.str_replace('/','+',$owner->colByName('FILE_NAME')->uploaddir).($owner->parent_value?'+'.$owner->parent_value:'').'/tb='.$owner->tablename.'/name='.$name.'/ext='.$ext;  //FIX path
                if($owner->download_count_fieldname)
                $download_url .= '/counter='.$owner->download_count_fieldname;

                $download_url = Crypt::file_url($download_url);

            }
            if ($owner->btn_download===true)
                $row['BTN_DOWNLOAD'] = $download_url ? '<a href="'.$download_url.'" class="'.$class.'" title="'.t('DOWNLOAD_DOCUMENT','Descargar documento').'"> <i class="fa fa-cloud-download"></i></a>' : '';
        }
    }

    function OnShow($owner){
        global $parent;
        //$this->paginator->link_upload_files = $owner->link_upload_files===true;



        //if($parent){


        ?>
        <script type="text/javascript">
             // console.log('LOADFILES.parent_value:',<?=$owner->parent_value?>)
             // console.log('LOADFILES.parent:',parent)
             function load_files(module_name,T) { 
                //alert(`load_files('${module_name}','${T}') called`)
                let parent = <?=$owner->parent_value || 'false' ?>;
                let prefix = false;
                let str_ext = '<?=implode(',', $owner->colByName('FILE_NAME')->accepted_doc_extensions)?>';
                upload_files('<?=t('MULTIPLE_UPLOAD','Subida múltiple')?>','<?=MODULE?>','<?=$owner->tablename?>','FILE_NAME',parent,prefix,str_ext);
            }
        </script>
        <?php

        //}


        if($owner->link_cfg){
         //   if(Administrador()){
              $owner->paginator->link_cfg = true;
              // echo "<h5>filter: {$owner->filter}</h5>";
              //echo "<h5>tag: {$_SESSION[$this->tablename]['tag']}</h5>";
              ?>
              <script type="text/javascript">
              $(function() { 
                 $('.cfg').click( function(){ $('#pages_files_cfg').toggle('slow');return false;} );
                //longPoll(); 
                //clock();
                
              });
              </script>
              <?php
        //    }
        }
        ?>
        <script type="text/javascript">

            $(".edit_row").click(function() {
              var id =  $(this).attr('item');
              var url = AJAX_URL+'/<?=$owner->module?>/ajax/module=<?=$owner->module?>/op=edit/table=<?=$owner->tablename?>/id='+id+'/page=1';
              $.modalform({ 'title' : '<?=t('EDIT_ROW','Modificar fila')?>: '+id, 'url': url  });
              return false;
            });
            
            $(".dele_row").click(function() {
              var id =  $(this).attr('item');
              //var url = AJAX_URL+'?module=galeria&ajax=delete&table=<?=$owner->tablename?>&id='+id+'&page=1';
              //$.modalform({ 'title' : 'Eliminar fila: '+id, 'url': url  });
              
              currentROW = $(this).closest('.div_item');
              $.get('/<?=$owner->module?>/ajax/module=<?=$owner->module?>/op=delete/table=<?=$owner->tablename?>/id='+id,function(data){
                if(data.error==0){       //if( msg.toLowerCase().indexOf('ok')>-1)
                  currentROW.addClass('animated hinge');
                  setTimeout(function(){currentROW.fadeOut('slow'); },1800 );
                  showMessageInfo(data.msg);
                }else{
                  showMessageError(data.msg);
                  $.modalform({'title' : 'ERROR','text': data.msg ,'buttons':'close'});
                }
              },'json');
              return false;
            });

        </script>
        <?php
    }
 
    function OnBeforeShow($owner){

 
    }

    function OnAfterShow($owner){ 
        if ($owner->gallery_mode) {

        }else if ($owner->group){

        }else if($owner->order && $owner->perms['edit']){
            ?>
            <script type="text/javascript">
            $(function() { 
                ///$('#CLI_PAGES_FILES .level-2 .cell_item_caption').css('padding-left','30px');
                /***********
                $('#<?=$owner->tablename?>').tableDnD({
                    hierarchyLevel: 1,
                    //indentArtifact:'<div class="indent">&nbsp;</div>',
                    onDragClass: "alt",
                    onDrop: function(table, row) {
                        var rows = table.tBodies[0].rows;
                        var debugStr = "Row dropped was "+row.id+". New order: ";
                        var separator='';
                        var keys = '';
                        var positions = '';
                        for (var i=0; i<rows.length; i++) {
                            keys += separator+/[^\-]*$/.exec(rows[i].id);              // /[^\-]*$/
                            positions += separator+(i+1);
                            separator=',';
                        }
                        console.log(debugStr+'['+keys+']['+positions+']');
                        var url= '<?=$owner->module?>/ajax/op=rearrange'; 
                        //console.log(url+'/table=<?=$owner->tablename?>/keys='+keys+'/positions='+positions+'/group=-1');
                        $.post(url,{"table":'<?=$owner->tablename?>',"keys":keys,"positions":positions,"group":'-1'},function(data, textStatus, jqXHR){  
                              if(data.error==0){
                                showMessageInfo(data.msg);
                              }else{
                                showMessageError(data.msg);
                              }
                        },'json');

                    },
                    onDragStart: function(table, row) {
                        console.log("Started dragging row "+row.id);
                    }
                });
                ******/
            });
            </script>
            <style>
                .fa-plus-square-o,
                .fa-minus-square-o{cursor:pointer;}
                .row-hidden{display:none;}
                .alt{
                    -moz-transition:all 1s ease;
                    -webkit-transition:all 1s ease;
                    -o-transition:all 1s ease;
                    transition:all 1s ease;
                    /* border: 2px dashed #536d8a; */
                    cursor:move;
                    user-select:none;
                    -webkit-box-shadow:-1px 0px 13px 0px rgba(0,0,0,0.45);
                    -moz-box-shadow:-1px 0px 13px 0px rgba(0,0,0,0.45);
                    box-shadow:-1px 0px 13px 0px rgba(0,0,0,0.45);
                    -moz-transform:scale(0.99) rotate(0.1deg) translate(2px);
                    -webkit-transform:scale(0.99) rotate(0.1deg) translate(2px);
                    -o-transform:scale(0.99) rotate(0.1deg) translate(2px);
                    transform:scale(0.99) rotate(0.1deg) translate(2px);
                    z-index:999;
                }
                .alt td{
                    /* background-color:red !important;  */
                }
            </style>
            <?php 
        }

    }

    function OnBeforeShowForm($owner,&$form,$id){
        if($owner->sanitize_title && $owner->state=='insert'){ 
            ?>
            <script type="text/javascript">
                $("#form_form_<?=$owner->tablename?> input[name='FILE_NAME'], #form_form_<?=$owner->tablename?> input[name='NAME']").change(function(){
                    $("#form_form_<?=$owner->tablename?> input[name='NAME']").val( titleCase($(this).val().replace(/\.[^/.]+$/, "").replace(/^.*\\/, "").replaceAll('_',' ')) );
               });
            </script>
            <?php
        }
        if($owner->table_tags) {     
            parent::OnBeforeShowForm($owner,$form,$id);
        } 

    }

    function OnDrawRow($owner,&$row,&$class){

        //////// $ff = $row['FILE_NAME'];

        if($owner->tablename=='CLI_PAGES_FILES')
            $parent_name = $owner->getFieldValue("SELECT item_name FROM CLI_PAGES WHERE item_id={$owner->parent_value}");
        else 
            $parent_name = MODULE;

        if($owner->table_tags)   {   
            parent::OnDrawRow($owner,$row,$class);
            if(count($row['A_TAG_NAMES'])>0) $class.=' '.implode(" ", $row['A_TAG_NAMES']);
        }

        $row['THUMBNAIL'] =false;

        $title = $row['NAME'];
        $href     = '';
        $hclass   = '';
        $fclass   = 'fa fa-search';
        $url      = $row['IMAGES']['FILE_NAME']['URL'];
        $type     = Str::get_file_extension($row['FILE_NAME']); 
        $filename  = Str::get_file_name($row['FILE_NAME']);  
        $name     = Str::sanitizeName(  $row['NAME'] );
        $thumb_   = SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$type.'.png';    
        $thumb    = isset($row['IMAGES']['FILE_THUMB']['THUMB']) && is_file($row['IMAGES']['FILE_THUMB']['THUMB'])   
                  ? $row['IMAGES']['FILE_THUMB']['THUMB']
                  : SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$type.'.png';      
        $js       = '';          
        $download = '';

        $extended_link =  $owner->module.'/file/filename='.$filename.'/id='.$row['ID']
                       . '/path='.str_replace('/','+',$owner->colByName('FILE_NAME')->uploaddir).($owner->parent_value?'+'.$owner->parent_value:'')
                       . '/tb='.$owner->tablename.'/name='.$name.'/ext='.$type.($owner->download_count_fieldname?'/counter='.$owner->download_count_fieldname:'');  //FIX path

        $link          = Crypt::file_url($extended_link);
        $inline_link   = Crypt::file_url($extended_link.'/mode=inline');


        //$valid_img_ext = in_array('.'.strtolower($type),$owner->colByName('FILE_NAME')->accepted_img_extensions);
        //if($valid_img_ext ) $img_src = $row['THUMB'];

        if      ($row['ID_PROVIDER']=='1') {
            $thumb  = $type=='svg'?$url/*'_images_/filetypes/icon_jpg.png'*//*$inline_link*/:$row['THUMB']; // image
            $href   = 'href="'.$inline_link.'"';
            $hclass = 'open_file_image';
        }else if($row['ID_PROVIDER']=='2') {
            $url    = 'https://www.youtube.com/watch?v='.$row['LINK']; //&feature=player_embedded'.'"';
            $href   = 'href="https://www.youtube.com/watch?v='.$row['LINK'].'"'; //&feature=player_embedded'.'"';
            $thumb  = 'https://img.youtube.com/vi/'.$row['LINK'].'/mqdefault.jpg';
            $type   = 'video';
            $hclass = 'open_file_video';
           //$fclass = 'fa fa-play';
           // $inline_link  = '';
        }else if($row['ID_PROVIDER']=='3') {
            $hash = unserialize(file_get_contents("https://vimeo.com/api/v2/video/{$row['LINK']}.php"));
            $url  = 'https://vimeo.com/'.$row['LINK'];
            $href = 'href="https://vimeo.com/'.$row['LINK'].'"';
            $thumb = $hash[0]['thumbnail_medium'];  
            $type   = 'video';
            $hclass = 'open_file_video';
            $title = $hash[0]['title'];
            //$fclass = 'fa fa-play';
        }else if($row['ID_PROVIDER']=='4') {
            $icon     = SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$type.'.png';

            if ($type=='txt'){

                $class .= ' item_'.$type;
                $hclass = 'open_file_'.$type;
            }else if (in_array($type,['mp4','avio','mkv'])){

                $class .= ' item_'.$type;
                $hclass = 'open_file_video';

            }else{

                $href = $owner->module.'/file/filename='.$filename.'/id='.$row['ID']
                       .'/path='.str_replace('/','+',$owner->colByName('FILE_NAME')->uploaddir).($owner->parent_value?'+'.$owner->parent_value:'')
                       .'/tb='.$owner->tablename.'/name='.$name.'/ext='.$type.($owner->download_count_fieldname?'/counter='.$owner->download_count_fieldname:'');  //.'/mode=inline';  //FIX path
                $href = Crypt::file_url($href);
                $href = 'href="'.$href.'"'; 

                $download = 'download="'.$name.'"';
                $target = 'target="new"';
                $row['NAME'] = '<a target="new" href="'.$u.'" item="'.$row['ID'].'" download="'.$name.'">'.$row['NAME'].'</a>';
            }
        }else if($row['ID_PROVIDER']=='5') {
            $class .= ' item_pdf';
            $hclass = 'open_file_pdf';
            // $js = '<script type="text/javascript">$(document).ready(function(){pdf_render (\'/'.$inline_link.'\',\''.$row['ID'].'\',0.2);});</script>';
            $icon     = SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$type.'.png';
        }else if($row['ID_PROVIDER']=='6') { 
           $href   = 'href="https://drive.google.com/file/d/'.$row['LINK'].'/edit"';
           $thumb  = '_images_/logos/google.png' ;
           $target = 'target="new"';
           $icon   = $thumb;
        }else if($row['ID_PROVIDER']=='7') { 
           $href   = 'href="https://drive.google.com/folderview?id='.$row['LINK'].'"';
           $thumb  = '_images_/logos/google.png' ;
           $target = 'target="new"';
           $icon   = $thumb;
        }else if($row['ID_PROVIDER']=='8') { 
           $href   = 'href="https://forms.gle/'.$row['LINK'].'"';
           $thumb  = '_images_/logos/google.png' ;
           $target = 'target="new"';
           $icon   = $thumb;
        }else if($row['ID_PROVIDER']=='9') { 
           $href   = 'href="https://docs.google.com/presentation/d/'.$row['LINK'].'"';
           $thumb  = '_images_/logos/google.png' ;
           $target = 'target="new"';
           $icon   = $thumb;
        }else if($type=='epub'||$row['ID_PROVIDER']=='10'){   // Epub
                 if (file_exists('media/epub/'.$filename.'/cover.jpg'))               $thumb = 'media/epub/'.$filename.'/cover.jpg?ver='.$row['LAST_UPDATE_DATE'];
            else if (file_exists('media/epub/'.$filename.'/OEBPS/Images/cover.jpg'))  $thumb = 'media/epub/'.$filename.'/OEBPS/Images/cover.jpg';
            else if (file_exists('media/epub/'.$filename.'/OEBPS/image/cover.jpg'))   $thumb = 'media/epub/'.$filename.'/OEBPS/image/cover.jpg';
            else if (file_exists('media/epub/'.$filename.'/OPS/images/cover.jpg'))    $thumb = 'media/epub/'.$filename.'/OPS/images/cover.jpg';
            else if (file_exists('media/epub/'.$filename.'/Ops/images/img1.jpg'))     $thumb = 'media/epub/'.$filename.'/Ops/images/img1.jpg';
            else if (file_exists('media/epub/'.$filename.'/OPS/0.png'))               $thumb = 'media/epub/'.$filename.'/OPS/0.png';
            else if (file_exists('media/epub/'.$filename.'/images/00002.jpeg'))       $thumb = 'media/epub/'.$filename.'/images/00002.jpeg';
            else if (file_exists('media/epub/'.$filename.'/images/00001.jpeg'))       $thumb = 'media/epub/'.$filename.'/images/00001.jpeg';
            else if (file_exists('media/epub/'.$filename.'/cover1.jpg'))              $thumb = 'media/epub/'.$filename.'/cover1.jpg';
            else if (file_exists('media/epub/'.$filename.'/cover.jpeg'))              $thumb = 'media/epub/'.$filename.'/cover.jpeg';
            else if (file_exists('media/epub/'.$filename.'/cover_image.jpg'))         $thumb = 'media/epub/'.$filename.'/cover_image.jpg';
            else if (file_exists('media/epub/'.$filename.'/calibre_raster_cover.jpg'))$thumb = 'media/epub/'.$filename.'/calibre_raster_cover.jpg';
            else if (file_exists('media/epub/'.$row['ISBN13'].'.jpg'))                $thumb = 'media/epub/'.$row['ISBN13'].'.jpg';
            else if (file_exists('media/epub/'.$row['ISBN10'].'.jpg'))                $thumb = 'media/epub/'.$row['ISBN10'].'.jpg';
            else if (file_exists('media/epub/'.$row['ID'].'.webp'))      $thumb = 'media/epub/'.$row['ID'].'.webp';
            $js =  '<script type="text/javascript">$(document).ready(function(){  $(\'#load_'.$row['ID'].'\').click(function(){  load_epub(\''.$inline_link.'\');  }); });</script>';
            $class .= ' item_epub';
            $icon     = SCRIPT_DIR_IMAGES.'/filetypes/icon_'.$type.'.png';
        }else if($row['ID_PROVIDER']=='11') { 
            $href   = 'href="'.$row['LINK'].'?external"';
            $thumb  = '_images_/filetypes/icon_html.png' ;
            $target = 'target="new"';
        }

        $share_link = '';
        if  ($owner->share_links || CFG::$vars['modules'][$parent_name]['share_links'])
            if(in_array( $row['ID_PROVIDER'],[1,4,5,10]))
                $share_link .= ' &nbsp; <a target="new" class="fa fa-link" title="Enlace para compartir" style="color:#3399ff;font-weight:100;z-index: 1; position: relative;" href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$inline_link.'"> </a>';

        if ($owner->qrcodes || CFG::$vars['modules'][$parent_name]['qrcodes'])
            if(in_array( $row['ID_PROVIDER'],[5]))
                //OLD $share_link .= '<span class="qrcode qrcode-big" id="qrcode-'.$row['ID'].'"></span><script>$(\'#qrcode-'.$row['ID'].'\').qrcode("'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$inline_link.'");</script>';
                $share_link .= '<span class="qrcode qrcode-big" id="qrcode-'.$row['ID'].'"></span><script>new QRCode(document.getElementById("qrcode-'.$row['ID'].'"),{text:"'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$inline_link.'",width:150,height:150});</script>';

        $download_url = $owner->module.'/file/filename='.$filename.'/id='.$row['ID']    
                 . '/path='.str_replace('/','+',$owner->colByName('FILE_NAME')->uploaddir).($owner->parent_value?'+'.$owner->parent_value:'')
                 . '/tb='.$owner->tablename.'/name='.$name.'/ext='.$type.($owner->download_count_fieldname?'/counter='.$owner->download_count_fieldname:''); //.'/mode=inline';  //FIX path

        $download_url = Crypt::file_url($download_url);         

        if(in_array( $row['ID_PROVIDER'],[1,4,5,10]))
        $href_dl = '<a href="'.$download_url.'" title="Descargar '.$name.'.'.$type.'" data-id="'.$row['ID'].'" data-value="'.$row[$owner->download_count_fieldname].'" download="'.$name.'"><i class="fa fa-cloud-download"></i></a>';
        else
        //OLD $href_dl = '<span class="qrcode" id="qrcode-'.$row['ID'].'"></span><script>$(\'#qrcode-'.$row['ID'].'\').qrcode("'.$url.'");</script>';
        $href_dl = '<span class="qrcode" id="qrcode-'.$row['ID'].'"></span><script>new QRCode(document.getElementById("qrcode-'.$row['ID'].'"),{text:"'.$url.'",width:150,height:150});</script>';
        //$type = 'img';

        if( $row['ID_PROVIDER']=='1'){ 
            if (MODULE=='control_panel'){
                $rel = '';
            } else {
                $rel = ' rel="g2"';
                $hclass='';
            }
        }

        if($owner->group){
            $row['NAME'] =  '<div class="div_item '.$class.'" id="'.$row['ID'].'" type="'.$type.'" file="'.$inline_link.'" item="'.$row['ID'].'" '.$ltaz.'>'
                         .    '<a '.$target.' class="open_file '.$hclass.'" '.$rel.'  data-title="'.$title.'" data-href="'.$inline_link.'" item="'.$row['ID'].'" '.$download.' '.$href.'><i id="load_'.$row['ID'].'" class="'.$fclass.'"></i></a>'
                         
                         .    '<span class="thumb"><img id="row-'.$row['ID'].'" src="'.$thumb.'" alt="..." /></span>'
                         .    ($owner->table_tags? $row['TAGS'] :'')
                       //.    ($owner->table_tags?  implode(" ", $row['A_TAG_NAMES']) :'')
                       //.    $actions
                         .   '<span class="filename">'.($row['NAME']).'</span>'
                         .   ($icon ? '<img class="img_icon" src="'.$icon.'" alt="..." />' : '')
                         .    $share_link
                         .  '</div>'
                         .   ($owner->btn_download===true?'<span class="download-link">'.$href_dl.'</span>':'')
                         .$js;
        }else{

             $row['FILE_NAME'] = '<a id="load_'.$row['ID'].'" '.$target.' class="open_file '.$hclass.'" '.$rel.'  data-title="'.$title.'" data-href="'.$inline_link.'" item="'.$row['ID'].'" '.$download.' '.$href.'>'.$row['ID_PROVIDER'].' '.($row['DESCRIPTION']?$row['DESCRIPTION']:$row['NAME']).'</a>'
                               .$js;

             if($owner->table_tags) $row['FILE_NAME'] .= '[TAGS]';
                         if(!$row['THUMBNAIL'] )
                            $row['THUMBNAIL'] = '<a href="'.$link.'" class="'.(in_array($type,['jpg','jpeg','png','gif','webp'])?'open_file_image':'').'" item="'.$row['ID'].'">'
                                              . '<img src="'.$thumb.'">'
                                              . '</a>';
        }   


        //$row['FILE_NAME'] .= ' - '.$ff;


    }

    function OnDrawCell($owner,&$row,&$col,&$cell){
        if($owner->table_tags)   {   
            if ($col->fieldname=='FILE_NAME'){
                if($row['A_TAG_LABELS'] && count($row['A_TAG_LABELS'])>0) $row['TAGS'] =  '<span class="labels">'.implode('',$row['A_TAG_LABELS']).'</span>'; 
                $cell = str_replace('[TAGS]', $row['TAGS'] , $cell);
            }
        }
    }


    function OnAfterShowForm($owner,&$form,$id){  

        //if($owner->state=='insert' && !$id) $id = $_SESSION['_CACHE'][$owner->tablename]['last_insert_'.$_SESSION['userid']]['id'];
      
        if($owner->state=='insert' || $owner->state=='update') {
	              
            ?>
           <script>

            b64toBlob = function (b64Data, contentType, sliceSize) {
                contentType = contentType || '';
                sliceSize = sliceSize || 512;
                var byteCharacters = atob(b64Data);
                var byteArrays = [];
                for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                    var slice = byteCharacters.slice(offset, offset + sliceSize);
                    var byteNumbers = new Array(slice.length);
                    for (var i = 0; i < slice.length; i++) {
                        byteNumbers[i] = slice.charCodeAt(i);
                    }
                    var byteArray = new Uint8Array(byteNumbers);
                    byteArrays.push(byteArray);
                }
                var blob = new Blob(byteArrays, {type: contentType});
                return blob;
            }


            $(function() {
                //console.log('hola hola')
                $("#form_form_<?=$owner->tablename?> input#LINK,#form_form_<?=$owner->tablename?> input#NAME").keyup(function() {
                
                    var videoObj = parseURL($('#form_form_<?=$owner->tablename?> input#LINK').val());
                    if (videoObj.type == 'youtube') {
                        $("#form_form_<?=$owner->tablename?> input#LINK").val(videoObj.id);
                        $('#ID_PROVIDER').val('2');
                        //cb('//img.youtube.com/vi/' + videoObj.id + '/maxresdefault.jpg');


                        const vidurl = 'https://www.youtube.com/watch?v='+videoObj.id; //I_izvAbhExY';

                        //console.log('YTURL',`https://noembed.com/embed?url=${vidurl}`);
                        fetch(`https://noembed.com/embed?url=${vidurl}`)
                            .then(res => res.json())
                            .then(data => $('#IMAGE_NAME').val(data.title) );

                         //{"thumbnail_height":360,"version":"1.0","url":"https://www.youtube.com/watch?v=dTWIefMIOSE","width":200,"thumbnail_width":480,"provider_name":"YouTube",
                         //"provider_url":"https://www.youtube.com/","height":113,"type":"video",
                         //"html":"<iframe width=\"200\" height=\"113\" src=\"https://www.youtube.com/embed/dTWIefMIOSE?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" allowfullscreen title=\"Simon &amp; Gafunkel - The Sounds Of Silence\"></iframe>",
                         //"author_url":"https://www.youtube.com/@asuh_33",
                         //"author_name":"asuh",
                         //"thumbnail_url":"https://i.ytimg.com/vi/dTWIefMIOSE/hqdefault.jpg",
                         //"title":"Simon & Gafunkel - The Sounds Of Silence"}


                    //} else if (videoObj.type == 'vimeo') {
                    //    $(this).addClass('loading');
                    //   $.get('https://vimeo.com/api/v2/video/' + videoObj.id + '.json', function(data) {
                    //        //alert(videoObj.id);
                    //        $("#form_form_<?=$owner->tablename?> input#LINK").val(videoObj.id).removeClass('loading');
                    //        $('#ID_PROVIDER').val('3');
                    //        //cb(data[0].thumbnail_large);
                    //    });

                    } else if (videoObj.type == 'google_drive_file') {
                         $("#form_form_<?=$owner->tablename?> input#LINK").val(videoObj.id);
                         $('#ID_PROVIDER').val('6');
                    } else if (videoObj.type == 'google_drive_folder') {
                         $("#form_form_<?=$owner->tablename?> input#LINK").val(videoObj.id);
                         $('#ID_PROVIDER').val('7');
                    } else if (videoObj.type == 'google_form') {
                         $("#form_form_<?=$owner->tablename?> input#LINK").val(videoObj.id);
                         $('#ID_PROVIDER').val('8');
                    } else if (videoObj.type == 'google_presentation') {
                         $("#form_form_<?=$owner->tablename?> input#LINK").val(videoObj.id);
                         $('#ID_PROVIDER').val('9');
                    } else{
                        // $('#ID_PROVIDER').val('1');
                    }
      
                }).keydown(function( event ) {
              
                    if ( event.which == 27 ) {
                     // $('#BUTTONCLOSE').click();
                    }

                }).mousedown(function() {
              
                    //$("#form_form_<?=$owner->tablename?> input#NAME").keyup();
                    $(this).keyup();
              
                });

                $("#form_form_<?=$owner->tablename?> #NAME").focus(function() {
                    let filename = $("#form_form_<?=$owner->tablename?> #fake_input_FILE_NAME").val();
                    if (filename){
                        let ext = filename.substring(filename.lastIndexOf('.')+1);
                        if     (ext=='pdf') $('#ID_PROVIDER').val('5');
                        else if(ext=='epub') $('#ID_PROVIDER').val('10');
                    }
                });
                

                <?php if($owner->epub){ ?>
                    // Pillamos la portada de abebooks (iberlibro )

                    $( "#ISBN13" ).hover( function() {
                            var isbn = $(this).val(); //.find('.isbn').text();
                            console.log('isbn',isbn);
                            if(isbn){

                            // https://images.isbndb.com/covers/85/44/9788432228544.jpg

                                $('#epub_cover').attr('src','https://pictures.abebooks.com/isbn/'+isbn+'-es.jpg')
                                        //  .css({display: "block", position: "absolute", left: ($(this).offset().left + $(this).width()) + "px", top: $(this).offset().top-45 + "px"  });
                            }
                        }, function() {
                            $('#cover').attr('src','_images_/pixel.gif');
                    });

                    // Update inputs
                    $("body").on("click", ".btn-epub-update-data", function() {
                        console.log('CLICK',this,
                                    $(this).closest('.epub-item').find('.epub-title').text(),
                                    $(this).closest('.epub-item').find('.epub-author').text(),
                                    $(this).closest('.epub-item').find('.epub-isbn10').text(),
                                    $(this).closest('.epub-item').find('.epub-isbn13').text()
                        );
                        $('#ISBN10').val($(this).closest('.epub-item').find('.epub-isbn10').text());
                        $('#ISBN13').val($(this).closest('.epub-item').find('.epub-isbn13').text());
                    });

                    // Openlibrary no encuentra casi nada en español :(
                    $("#ISBN10").on("click",  function() {
                        var isbn = $(this).val();

                        if(!isbn){

                            console.log('No tenemos ISBN.');
                            let url = "https://www.googleapis.com/books/v1/volumes?q="+encodeURIComponent($('#NAME').val());
                            console.log(url);

                            $.ajax({
                                'url': url,
                                dataType: "jsonp",
                                success: function (data) {
                                    console.log('DATA',data);
                                        // https://www.bing.com/search?q=googleapis+books+parse+result+json&qs=n&form=QBRE&sp=-1&ghc=1&lq=0&pq=googleapis+books+parse+result+json&sc=10-34&sk=&cvid=C718B2774351441191ED2F48CD90124B&ghsh=0&ghacc=0&ghpl=
                                        // https://stackoverflow.com/questions/53138634/how-to-extract-field-from-google-books-api-using-gson-and-jsoup

                                    let epub_name =  $('#NAME').val();

                                    //$(".ajax_container").append(`<table id="epub_list" class="zebra fixed_headers"><thead><tr><th colspan="5">${epub_name}</th></tr></thead><tbody></tbody></table>`); //<!--<tr><th>TITLE</th><th>AUTHOR</th><th>ISBN</th><th>ISBN</th><th>THUMB</tr>-->
                                    $("#epub_list").remove();
                                    //$(".ajax_container").append(`<div><div>${epub_name}</div><div id="epub_list"></div></div>`); //<!--<tr><th>TITLE</th><th>AUTHOR</th><th>ISBN</th><th>ISBN</th><th>THUMB</tr>-->
                                    $(".ajax_container").append(`<div id="epub_list"></div>`); //<!--<tr><th>TITLE</th><th>AUTHOR</th><th>ISBN</th><th>ISBN</th><th>THUMB</tr>-->
                                    // console.log('NAME',epub_name)

                                    // loop through the data to get each row as an 'item'
                                    // with YQL would be something like:
                                    // $.each(data.query.results.row, function (i, item) {
                                    // with simpler object, could be 
                                    // $.each(data[0], function (i, item) {
                                    $.each(data.items, function(i, item) {
                                        // how to parse this given that isbn's are unique objects?                 
                                        console.log('ITEM',item);
                                        //// Render the template with the data and insert rendered HTML
                                        //$.tmpl("booklist", item)

                                        // Put it in the table!
                                        //.appendTo("#results");
                                        let id     = item.id;
                                        let title  = item.volumeInfo.title;
                                        let author = item.volumeInfo.authors ? item.volumeInfo.authors[0] : '';

                                        let _isbn13 = item.volumeInfo.industryIdentifiers[0].identifier;
                                        let _isbn10 = item.volumeInfo.industryIdentifiers[1] ? item.volumeInfo.industryIdentifiers[1].identifier : '';
                                        let isbn13,isbn10='';

                                        let match1 = epub_name.includes(title) && epub_name.includes(author)
                                        let match2 = epub_name.includes(title)

                                        let class_match = match1 ? 'epub-match1' : (match2 ? 'epub-match2' : '' );
                                        
                                        if (validISBN13(_isbn13)) isbn13 = _isbn13; else if (validISBN13(_isbn10)) isbn13 = _isbn10;
                                        if (validISBN10(_isbn13)) isbn10 = _isbn13; else if (validISBN10(_isbn10)) isbn10 = _isbn10;
                                        
                                        let thumb  = item.volumeInfo.imageLinks 
                                                ? (   item.volumeInfo.imageLinks.thumbnail 
                                                    ? item.volumeInfo.imageLinks.thumbnail 
                                                    : (item.volumeInfo.imageLinks.smallThumbnail?item.volumeInfo.imageLinks.smallThumbnail:'')
                                                    )
                                                : '';

                                        if (isbn13 && validISBN13(isbn13)) {

                                            //if(thumb=='')  thumb = 'https://images.isbndb.com/covers/'+isbn13.substr(9,2)+'/'+isbn13.substr(11,2)+'/'+isbn13+'.jpg';
                                            if(thumb=='')  thumb = 'https://pictures.abebooks.com/isbn/'+isbn13+'.jpg';

                                            let row = `<div class="epub-item ${class_match}"><span class="epub-data"><span class="epub-title">${title}</span><span class="epub-author">${author}</span><span class="epub-isbn13">${isbn13}</span><span class="epub-isbn10">${isbn10}</span><a class="btn btn-secondary btn-epub-update-data">Update</a></span><img class="noavatar epub-img" src="${thumb}"></div>`;   //
                                            $("#epub_list").append(row);
                                        }

                                    });

                                }
                            });

                            
                        }else{

                            console.log('ISBN:' + isbn);
                            $.ajax({
                                url: "https://openlibrary.org/api/books?jscmd=details&callback=?",
                                data: { bibkeys: isbn },
                                dataType: "jsonp",
                                success: function (data) {
                                    var bibkeys = Object.keys(data);
                                    if (bibkeys.length === 0) {
                                        console.log('NO_RESULTS_FOUND_FOR_ISBN:' + isbn);
                                    }else{
                                        var bookInfo = data[bibkeys[0]];
                                        console.log('bookInfo.details',bookInfo.details    );
                                        console.log( ' Portada: ' + bookInfo.thumbnail_url );
                                        console.log( '  Título: ' + bookInfo.details.title );
                                        console.log( '     Url: ' + bookInfo.info_url      );
                                        if(typeof bookInfo.details.isbn_10 !== 'undefined') console.log( ' ISBN 10: ' + bookInfo.details.isbn_10[0]);
                                        if(typeof bookInfo.details.isbn_13 !== 'undefined') console.log( ' ISBN 13: ' + bookInfo.details.isbn_13[0]);
                                    }
                                }
                            });
                        }
                    });

                    $( "#btn-cover-get" ).on("click",  function() {  
    
                        console.log('#btn-isbn13 click');

                        let filename = '<?=$owner->colByName('FILE_NAME')->uploaddir.($owner->parent_value?'/'.$owner->parent_value:'').'/'?>'+$("#form_form_<?=$owner->tablename?> #fake_input_FILE_NAME").val();
                        console.log('CLICK',filename);

                        let book = ePub(filename, { openAs: "epub" });
                        console.log('BOOK',book);

                        let $title  = document.getElementById("epub-title-title");
                        let $author = document.getElementById("author");
                        let $cover  = document.getElementById("epub_cover");    // var $cover = $('#epub-cover-img');  //con el puto jQuery
                        let $isbn   = document.getElementById("ISBN13");

                        if (validISBN13($isbn.value) ){

                        console.log('BOOK.ISBN13', $isbn.value);
                        console.log('BOOK.COVER.SRC', 'https://pictures.abebooks.com/isbn/'+$isbn.value+'-<?=$_SESSION['lang']?>.jpg');
                        //$cover.src = 'https://pictures.abebooks.com/isbn/'+$isbn.value+'-<?=$_SESSION['lang']?>.jpg';
                        $cover.src = 'https://pictures.abebooks.com/isbn/'+$isbn.value+'.jpg';

                        }else{
                            // METADATA cover, title, author, etc.
                            book.loaded.metadata.then(function(meta){
                                var isbn13  = meta.identifier && validISBN13(meta.identifier) ? meta.identifier : ''                  //9788466369190
                                console.log('BOOK.META.IDENTIFIER',meta.identifier);
                                $title.value = meta.title;
                                $author.value = meta.creator;
                                $isbn.value = isbn13;
                                //console.log('BOOK.COVER', book.cover);
                                if (book.archive && book.cover) {
                                    console.log('BOOK.ARCHIVE', book.cover);
                                    //console.log('BOOK.COVER', book.cover);

                                    book.archive.createUrl(book.cover)
                                    .then(function (url) {
                                        console.log('BOOK.COVER.URL', url);
                                        $cover.src = url;
                                        $( "#btn-cover-save" ).show();
                                    })            // $cover.attr('src',  url); //+'?ver=3');   // .... jQuery             

                                   
                                } else  if(validISBN13(meta.identifier)){
                                    console.log('META.IDENTIFIER', meta.identifier);
                                    console.log('BOOK.COVER.SRC', 'https://pictures.abebooks.com/isbn/'+meta.identifier+'-<?=$_SESSION['lang']?>.jpg');
                                // $cover.src = 'https://pictures.abebooks.com/isbn/'+meta.identifier+'-<?=$_SESSION['lang']?>.jpg';
                                    $cover.src = 'https://pictures.abebooks.com/isbn/'+meta.identifier+'.jpg';

                                    // BOOK.COVER.SRC https://pictures.abebooks.com/isbn/9788466369190-es.jpg

                                } else {
                                    console.log('META.IDENTIFIER', meta.identifier);
                                    console.log('BOOK.COVER', book.cover);
                                    if(meta.identifier)
                                        $cover.src = book.cover;
                                }


                            });
                        }
                    });                 

                    $( "#btn-cover-save" ).on("click",  function() {  
                                let img = document.querySelector('#epub_cover');    //img.setAttribute('crossorigin', 'anonymous'); 
                                let canvas = document.createElement('canvas');
                            
                                canvasContext = canvas.getContext && canvas.getContext('2d');
                                canvas.height = img.naturalHeight;
                                canvas.width = img.naturalWidth;
                                canvasContext.drawImage(img, 0, 0);

                                let imgBase64 = canvas.toDataURL('image/webp');   

                                //console.log('URL','docs/ajax/op=savefile/image=' + document.getElementById('epub_cover').src);   //$('#epub_cover')[0].currentSrc);
                        
                                let block        = imgBase64.split(";");      // Split the base64 string in data and contentType                 
                                let contentType  = block[0].split(":")[1];     // Get the content type                    
                                let realData     = block[1].split(",")[1];   // get the real base64 content of the file                    
                                let blob         = b64toBlob(realData, contentType);    // Convert it to a blob to upload
                                let formDataToUpload = new FormData();    // Create a FormData and append the file with "image" as parameter name
                                formDataToUpload.append("image", blob);
                                formDataToUpload.append("filename", '<?=$id?>.webp');
        
                                ////////////////// https://www.bing.com/search?q=jquery+ajax+base64img&cvid=ab2d60615e204eeabca277cc040299c5&gs_lcrp=EgZjaHJvbWUyBggAEEUYOdIBCTEyMjA1ajBqNKgCALACAA&FORM=ANAB01&PC=HCTS&ntref=1
                                ////////////////// https://stackoverflow.com/questions/34047648/how-to-post-an-image-in-base64-encoding-via-ajax
                                ////////////////// https://ourcodeworld.com/articles/read/322/how-to-convert-a-base64-image-into-a-image-file-and-upload-it-with-an-asynchronous-form-using-jquery

                                //location.href='docs/raw/op=savefile/image=' + $('#epub_cover')[0].currentSrc;
                                $.ajax({
                                method: "POST",
                                url: "docs/ajax/op=savefile",
                                processData:false,
                                contentType: false, //'application/octet-stream',
                                data: formDataToUpload ,
                                dataType: "json",
                                beforeSend: function( xhr, settings ) { }
                                }).done(function( data ) {
                                console.log('DATA',data);
                                //console.log('DATA.IMG',data.img);
                                }).fail(function(data) {
                                console.error('DATA',data);
                                }).always(function(data) {
                                console.log('ALWAYS',data);
                                });  
                    });

                    $( "#ISBN13" ).on("click",  function() {                   
                    });
               

                <?php } ?>
                
            });
            </script>
            <?php
        }
 
    }

    function OnInsert($owner,&$result,&$post) { 
        //$result['error'] = 1; 
        //$result['msg'] = print_r($_FILES,true);

        $type = '8';
        $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\''.MODULE.'_files - insert - inline\',\''.Str::escape(print_r($_FILES,true)."\n".print_r($post,true)).'\')';
        Table::sqlExec($log_sql);

        if(!$post['ID_PROVIDER']){
            if($_FILES['FILE_NAME']&&$_FILES['FILE_NAME']['error']!==4){
                $ext = Str::get_file_extension($_FILES['FILE_NAME']['name']);            

                if      ($ext=='pdf')  $post['ID_PROVIDER']='5';
                else if ($ext=='epub') $post['ID_PROVIDER']='10';
                else if (in_array('.'.strtolower($ext),$owner->colByName('FILE_NAME')->accepted_img_extensions)) $post['ID_PROVIDER']='1';
                else if (in_array('.'.strtolower($ext),$owner->colByName('FILE_NAME')->accepted_doc_extensions)) $post['ID_PROVIDER']='4';

            }
        }

        if (!$post['ID_PROVIDER']) $post['ID_PROVIDER']=1;

        if(in_array($post['ID_PROVIDER'],[1,4,5,10])){

            if($_FILES['FILE_NAME']&&$_FILES['FILE_NAME']['error']!==4){

               if(!$post['NAME']) $post['NAME']=$_FILES['FILE_NAME']['name'];
                                            
                //FIX NAME if too much upercases sanitize
                //$post['NAME']=str_replace(['.epub','_','%2C','%20','%5B','%5D','%C3%B3','%C3%A9','%C3%B6'],['',' ',',',' ','[',']','ó','é','ö'],$post['NAME']);
                $ext = Str::get_file_extension($_FILES['FILE_NAME']['name']);            
                $post['NAME']=str_replace(['.'.$ext,'_'],['',' '],urldecode($post['NAME']));
                
                // HASH FILENAMES
                if($owner->hash_filenames){


                    $hash = sha1_file($_FILES['FILE_NAME']['tmp_name']);

     
                    if($owner->fk) 
                        $pathfilename = $owner->colByName('FILE_NAME')->uploaddir.'/'.  $post[$owner->parent_key]   .'/'.$hash.'.'.$ext;
                    else 
                        $pathfilename =  $owner->colByName('FILE_NAME')->uploaddir.'/'.$hash.'.'.$ext;

                    if(file_exists($pathfilename)){

                        $_files = Table::sqlQuery('SELECT ID,FILE_NAME FROM '.$owner->tablename.' WHERE FILE_NAME = \''.$hash.'.'.$ext.'\'');
                        if(count($_files)>0){
                            $result['error'] = 1; 
                            $result['msg'] = t('FILE_ALREADY_EXISTS','Ya existe una fila con el archivo').': '.$pathfilename;
                            return;
                        }else{
                            // $result['error'] = 1; 
                            //if (unlink($pathfilename)){
                                $result['msg'] = t('IDENTICAL_FILE_FOUND','Hemos encontrado un archivo idéntico al que está subiendo, pero no asociado a ningúna fila.');
                            //}else{
                            //    $result['msg'] = t('IDENTICAL_FILE_FOUND_CANNOT_DELETE','Hemos encontrado un archivo idéntico al que está subiendo, pero no podemos eliminarlo. El archivo es:. ').$pathfilename;
                            //}
                        }
                    }

                }

            } else {// if($_FILES['FILE_NAME']){
                $ext = false;
                //if($post['NAME'])
                //   $post['NAME']=urldecode($post['NAME']);

            }
            
            if($ext!==false){
                if      ($ext=='pdf')  $post['ID_PROVIDER']='5';
                else if ($ext=='epub') $post['ID_PROVIDER']='10';
                else if (in_array('.'.strtolower($ext),$owner->colByName('FILE_NAME')->accepted_img_extensions)) $post['ID_PROVIDER']='1';
                else if (in_array('.'.strtolower($ext),$owner->colByName('FILE_NAME')->accepted_doc_extensions)) $post['ID_PROVIDER']='4';

                //$result['error']=1;
                //$result['msg']=$ext.'::'.print_r($owner->colByName('FILE_NAME')->accepted_doc_extensions,true);

                if($owner->fk){
                    if($tabla->main) {
                        if ($post['MAIN']=='1'){
                            Table::sqlExec('UPDATE '.$owner->tablename.' SET MAIN=\'0\' WHERE '.$owner->fk.' = '.$post[$owner->fk]); //.' AND ID<>'.$post['ID']);
                        }else{
                            $_imgs = Table::sqlQuery('SELECT ID,MAIN FROM '.$owner->tablename.' WHERE '.$owner->fk.' = '.$post[$owner->fk]);
                            $_main = 0;
                            foreach ($_imgs as $k => $v){ if($v['MAIN']=='1')  $_main=$v['ID']; }
                            if ($_main==0) $post['MAIN']='1';
                        }
                    }
                    if($tabla->mini) {
                        if ($post['MINI']=='1'){
                            Table::sqlExec('UPDATE '.$owner->tablename.' SET MINI=\'0\' WHERE '.$owner->fk.' = '.$post[$owner->fk]); //.' AND ID<>'.$post['ID']);
                        }else{
                            $_imgs = Table::sqlQuery('SELECT ID,MINI FROM '.$owner->tablename.' WHERE '.$owner->fk.' = '.$post[$owner->fk]);
                            $_mini = 0;
                            foreach ($_imgs as $k => $v){ if($v['MINI']=='1') $_mini=$v['ID']; }
                            if ($_mini==0) $post['MINI']='1';
                        }
                    }
                }
            }  // if($ext!==false){
        } //        if(in_array($post['ID_PROVIDER'],[1,4,5,10])){
    }
  
    function OnUpdate($owner,&$result,&$post) {   

        $type = '8';
        $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\''.MODULE.' - '.$owner->tablename.' - update - inline\',\''.Str::escape(print_r($_FILES,true)."\n".print_r($post,true)).'\')';
        Table::sqlExec($log_sql);

        //  '1'=>'Image', '2'=>'Youtube', '3'=>'Vimeo', '4'=>'Document', '5'=>'PDF', '6'=>'Google Drive File','7'=>'Google Drive Folder','8'=>'Google Form','9'=>'Google Presentation','10'=>'Epub','11'=>'URL'


        /*
           foreach ($owner->cols as $col){
                if($col->mask){
                    foreach ($post as $k => $v){
                        if($k == $col->fieldname){
                         //   $result['the_file_'.$col->fieldname] =  str_replace(['[ID]','[LANG]'],[$post[$owner->parent_key],$_SESSION['lang']], $col->mask);
                            $result['the_file_'.$col->fieldname] =  str_replace('[ID]',$post['PRODUCT_ID'], $col->mask);
                        }
                    }
                }
           }
        */

        if(in_array($post['ID_PROVIDER'],[1,4,5,10])){

            if(!$post['NAME']) $post['NAME']=Str::get_file_name($post['fake_input_FILE_NAME']);

            $result['the_filename']=$post['NAME']; //OLDOK $post['NAME'];
            /*
            if($post['fake_input_FILE_NAME']==''){
                //$post['FILE_NAME']='';
                if($owner->fk) {
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old'][$owner->parent_key].'/'.$result['old']['FILE_NAME']);
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old'][$owner->parent_key].'/'.TN_PREFIX.$result['old']['FILE_NAME']);
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old'][$owner->parent_key].'/'.BIG_PREFIX.$result['old']['FILE_NAME']);
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old'][$owner->parent_key].'/'.str_replace(array('.jpg','.png'),'.webp',$result['old']['FILE_NAME']));
                }else{
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old']['FILE_NAME']);
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.TN_PREFIX.$result['old']['FILE_NAME']);
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.BIG_PREFIX.$result['old']['FILE_NAME']);
                    @unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.str_replace(array('.jpg','.png'),'.webp',$result['old']['FILE_NAME']));
                }
            } 
            */
            $ext = Str::get_file_extension($post['fake_input_FILE_NAME']);            

           
            if      ($ext=='pdf')  $post['ID_PROVIDER']='5';
            else if ($ext=='epub') $post['ID_PROVIDER']='10';
            else if (in_array('.'.strtolower($ext),$owner->colByName('FILE_NAME')->accepted_img_extensions)) $post['ID_PROVIDER']='1';
            else if (in_array('.'.strtolower($ext),$owner->colByName('FILE_NAME')->accepted_doc_extensions)) $post['ID_PROVIDER']='4';

            $post['NAME']=str_replace(['.'.$ext,'_'],['',' '],urldecode($post['NAME']));

            // HASH FILENAMES
            if($owner->hash_filenames){
                if(is_array($_FILES)&&count($_FILES)>0&&$_FILES['FILE_NAME']['tmp_name']) {
                    $hash = sha1_file($_FILES['FILE_NAME']['tmp_name']);
                    if(file_exists( $owner->colByName('FILE_NAME')->uploaddir.'/'.$hash.'.'.$ext)){
                        $result['error'] = 1; 
                        $result['msg'] = t('FILE_ALREADY_EXISTS','Ya existe el archivo').' '.$hash.'.'.$ext;
                        return;
                    }
                }  

                /**
                $old_hash =   sha1_file($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old']['FILE_NAME']);
                if($result['old']['FILE_NAME']!=$old_hash.'.'.$ext){
                    $result['error'] = 1; 
                    $result['msg'] = $result['old']['FILE_NAME'].' rename to '.$old_hash.'.'.$ext;
                    rename($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['old']['FILE_NAME'],$owner->colByName('FILE_NAME')->uploaddir.'/'.$old_hash.'.'.$ext);
                    $post['FILE_NAME']=$old_hash.'.'.$ext;
                }
                */ 
                //if(file_exists( $owner->colByName('FILE_NAME')->uploaddir.'/'.$hash.'.'.$ext)){
            }

            //FIX NAME if too much upercases sanitize      
            if($post['ID_PROVIDER']=='10') {
                // $post['NAME']=str_replace(['.epub','_','%2C','%20','%5B','%5D','%C3%B3','%C3%A9','%C3%B6'],['',' ',',',' ','[',']','ó','é','ö'],$post['NAME']);
            } //else  if($post['ID_PROVIDER']=='6') $post['FILE_NAME']=$post['NAME'];

        }else{

            $ext = false;

        }

        if($owner->fk){
            if($tabla->main) {
                if ($post['MAIN']=='1'){
                    Table::sqlExec('UPDATE '.$owner->tablename.' SET MAIN=\'0\' WHERE '.$owner->fk.' = '.$post[$owner->fk]); //.' AND ID<>'.$post['ID']);
                }else{
                    /**
                    $_imgs = Table::sqlQuery('SELECT ID,MAIN FROM '.$owner->tablename.' WHERE '.$owner->fk.' = '.$post[$owner->fk]);
                    $_main = 0;
                    foreach ($_imgs as $k => $v){ if($v['MAIN']=='1')  $_main=$v['ID']; }
                    if ($_main==0) $post['MAIN']='1';
                    **/
                }
            }              
            if($tabla->mini) {
                if ($post['MINI']=='1'){
                    Table::sqlExec('UPDATE '.$owner->tablename.' SET MINI=\'0\' WHERE '.$owner->fk.' = '.$post[$owner->fk]); //.' AND ID<>'.$post['ID']);
                }else{
                    /**
                    $_imgs = Table::sqlQuery('SELECT ID,MINI FROM '.$owner->tablename.' WHERE '.$owner->fk.' = '.$post[$owner->fk]);
                    $_mini = 0;
                    foreach ($_imgs as $k => $v){ if($v['MINI']=='1') $_mini=$v['ID']; }
                    if ($_mini==0) $post['MINI']='1';
                    **/
                }
            }
        }
  
        if( $owner->epub && ($ext=='epub' || $post['ID_PROVIDER']=='10')){

            if(file_exists('media/epub/'.Str::get_file_name($post['fake_input_FILE_NAME']).'/cover.jpg') && $post['ACTIVE']!=='1')
                unlink('media/epub/'.Str::get_file_name($post['fake_input_FILE_NAME']).'/cover.jpg'); //FIX use calc field to delete epu bdir
                                                                                                     // or/and find other rows w/ same epub    
            /*********  
            if(!file_exists('media/epub/'.Str::get_file_name($post['fake_input_FILE_NAME']))) $this->save_epub($owner,$post['fake_input_FILE_NAME'],$result);
 
            $epub_file=Str::get_file_name($post['fake_input_FILE_NAME']);

            if (!file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'.jpg')) {

                         if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover.jpg'))                  $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover.jpg?ver='.$row['LAST_UPDATE_DATE'];
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OEBPS/Images/cover.jpg'))     $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OEBPS/Images/cover.jpg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OEBPS/image/cover.jpg'))      $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OEBPS/image/cover.jpg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OPS/images/cover.jpg'))       $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OPS/images/cover.jpg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/Ops/images/img1.jpg'))        $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/Ops/images/img1.jpg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OPS/0.png'))                  $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/OPS/0.png';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/images/00002.jpeg'))          $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/images/00002.jpeg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/images/00001.jpeg'))          $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/images/00001.jpeg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover1.jpg'))                 $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover1.jpg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover.jpeg'))                 $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover.jpeg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover_image.jpg'))            $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/cover_image.jpg';
                    else if (file_exists(SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/calibre_raster_cover.jpg'))   $cover = SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'/calibre_raster_cover.jpg';
                    
                    if(file_exists($cover)) copy($cover,  SCRIPT_DIR_MEDIA.'/epub/'.$epub_file.'.jpg');

            }
            **/


            if ($post['ISBN13'] && $post['ACTIVE']=='1'){
                if(!file_exists('media/epub/'.$post['ISBN13'].'.jpg')||$post['ACTIVE']=='1'){
                    $url_cover = 'https://pictures.abebooks.com/isbn/'.$post['ISBN13'].'-es.jpg';
                    file_put_contents('media/epub/'.$post['ISBN13'].'.jpg', file($url_cover));
                    /***   
                    $ch = curl_init($url_cover);
                    if($ch){
                        $fp = fopen('media/epub/'.Str::get_file_name($post['fake_input_FILE_NAME']).'/cover.jpg', 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);
                    }
                    ***/
                   //$result['msg']='media/epub/'.$post['ISBN13'].'/cover.jpg';
                   //$result['error']=5;
                }
            }
        }
    }

    function save_epub($owner,$filename,&$result){

        $ext = Str::get_file_extension($filename);            
        $epub_file='./'.$owner->colByName('FILE_NAME')->uploaddir.'/'.$filename;
        if (file_exists($epub_file)) {

            //$result['HASH'] = sha1_file($epub_file);
            
        }else{

        }

        include(SCRIPT_DIR_MODULES.'/ebooks/functions.php');
            
        $epub_dir='./media/epub/'.Str::get_file_name($epub_file).'/';
        if (!file_exists($epub_dir)) {
                $result['msg'] = 'Adding test.txt to '.$epub_file;
                if (addFileToZip($epub_file,SCRIPT_DIR_MEDIA.'/epub/test.txt','test'.time().'.txt',true)){
                    $result['msg'] .= ' ... OK';
                    $log = unzip($epub_file,true,$epub_dir);
                    $result['msg'] .= '<br>Unzipping '.$epub_file.' '.($log?'OK':'FAIL');
                    
                }else{
                    $result['msg'] .= ' ... FAIL';
                }
        }else{
                $result['msg'] = 'No existe'.$epub_dir;
        }
    }

    function rr_mdir($dir) { 
       if (is_dir($dir)) { 
         $objects = scandir($dir);
         foreach ($objects as $object) { 
           if ($object != "." && $object != "..") { 
             if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
               $this->rr_mdir($dir. DIRECTORY_SEPARATOR .$object);
             else
               unlink($dir. DIRECTORY_SEPARATOR .$object); 
           } 
         }
         rmdir($dir); 
       } 
    }

    function OnDelete($owner,&$result,$id){ 

        //FIX delete epub files
        /**  
        $row = $owner->getRow($id);
        if($this->soft_delete){        

        }else{
            $epub_dir = Str::get_file_name($row['FILE_NAME']);
            if(strlen(Str::get_file_name($row['FILE_NAME']))>35)  $result['epub_dir'] = 'media/epub/'.$epub_dir; 
                                                            else  $result['epub_dir'] = false;
        }
        ***/
        if($owner->table_tags)   {   
            parent::OnDelete($owner,$result,$id);
        }
    }

    function OnAfterDelete($owner,&$result){ 
        /***
        if($result['epub_dir'] && strlen($result['epub_dir'])>35 && file_exists($result['epub_dir'])){
             //FIX Search for other [tables]/rows that use same epub dir, then avoid deletion
             // OR use a deleteEpubDir propertyin Table object
             $this->rr_mdir($result['epub_dir']);
             $result['msg']='delete '.$result['epub_dir'];
        }
        ***/
    }

    function OnSaveFile($owner,&$col,&$file,&$result){
      /**  
      $ext = Str::get_file_extension($file["name"]);

        // HASH FILENAMES
        if($owner->hash_filenames){
            $result['name']       = sha1_file($file['tmp_filename']).'.'.$ext; 
        }else{
            $result['name']       = Str::sanitizeName($result['local_file'],true); 
        }
        */

    }

    function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
        //if  ($col->fieldname=='file_name')    $result['local_file'] = $result['the_filename'];

        // HASH FILENAMES
        if($owner->hash_filenames){
            $result['local_file'] = sha1_file($result['tmp_filename']).'.'.$result['ext']; 
        }else{
            $result['local_file'] = Str::sanitizeName($result['local_file'],true); 
        }
        /*
        foreach ($owner->cols as $col){
            if($col->mask){
                    if($k == $col->fieldname){
                        //$result['the_file_'.$col->fieldname] =  str_replace(['[ID]','[LANG]'],[$post[$owner->parent_key],$_SESSION['lang']], $col->mask);
                        $result['local_file'] = $result['the_file_'.$col->fieldname];
                    }
            }
        }
        */

        /** 
        if(file_exists($local_file)){
            $result['error'] = 1; 
            $result['msg'] = 'Ya existe el archivo '.$local_file;
            
        }
        */
        //$result['local_file'] =  sha1_file($result['tmp_filename']).'.'.$result['ext']; 
    }

    function OnAfterSaveFile($owner,&$col, $filename,$result ){

        $ext = Str::get_file_extension($filename);
        
        if( 1==2 && $col->watermark && in_array($ext,['jpg','jpeg']) ) {

                //$watermark = imagecreatefromstring(file_get_contents($col->watermark));
                $watermark = imagecreatefrompng($col->watermark);  
                $watermark_width = imagesx($watermark);  
                $watermark_height = imagesy($watermark);  
                //$image = imagecreatefromstring(file_get_contents($filename));
                $image = imagecreatefromjpeg($filename);

                $filename_size   = getimagesize($filename);  
                $filename_width  = $filename_size[0];
                $filename_height = $filename_size[1];

                /*
                // Coordenadas xy desde donde se dibuja la marca de agua 
                $dest_x                   = $filename_width / 4;   // Dividiendo por 4 ponemos la marca de agua un 25% de la anchura desde la izquierda 
                $dest_y                   = $filename_height / 4;  // Dividiendo por 4 ponemos la marca de agua un 25% de la altura desde arriba
                // Redimesionamos la marca de agua  (Dividiendo por 2 queda a la mitad de la imagen orginal)
                $resized_watermark        = imagecreatetruecolor($filename_width/2, $filename_height/2);
                */

                // Coordenadas xy desde donde se dibuja la marca de agua 
                $dest_x                   = $filename_width - 10 - 50;  // arriba a la izquierda, con 10px de margen izquierdo
                $dest_y                   = $filename_height - 10 - 50;  //                    y otros 10px desde arriba
                // Redimesionamos la marca de agua a 50px
                $resized_watermark        = imagecreatetruecolor(50, 50);

                $resized_watermark_width  = imagesx($resized_watermark);  
                $resized_watermark_height = imagesy($resized_watermark);  

                imagealphablending($resized_watermark, false);
                imagesavealpha($resized_watermark, true);
                imagecopyresampled($resized_watermark, $watermark, 0, 0, 0, 0, $resized_watermark_width, $resized_watermark_height,  $watermark_width, $watermark_height);
                imagecopy($image, $resized_watermark, $dest_x , $dest_y, 0, 0, $resized_watermark_width, $resized_watermark_height);
                imagejpeg($image,$filename,100);  
                imagedestroy($image);  
                imagedestroy($watermark);       
        }

    }

    function OnAfterInsert($owner,&$result,&$post){

        if(in_array($post['ID_PROVIDER'],[1,4,5,10]))
        if($post['NAME']=='') $owner->sql_exec('UPDATE '.$owner->tablename." SET NAME=FILE_NAME WHERE ID={$post['ID']}");

        if($owner->table_tags) parent::OnAfterInsert($owner,$result,$post);
  
        //if( $owner->epub && $post['ID_PROVIDER']=='10')
        //$this->save_epub($owner,$result['local_file'],$result);
    }

    function OnAfterUpdate($owner,&$result,&$post){
        if($owner->table_tags) parent::OnAfterUpdate($owner,$result,$post);
        if($post['ID']) {
            if(!$post['LINK']) {
                if($post['fake_input_FILE_NAME']=='') $owner->sql_exec('UPDATE '.$owner->tablename." SET ID_PROVIDER='4',FILE_NAME='' WHERE ID={$post['ID']}");
            }else{
               if($post['ID_PROVIDER']=='11') // && $post['fake_input_FILE_NAME']==''
                   $owner->sql_exec('UPDATE '.$owner->tablename." SET FILE_NAME='' WHERE ID={$post['ID']}");
            }
        }
     // if($post['NAME']=='') $owner->sql_exec('UPDATE '.$owner->tablename." SET NAME=file_name WHERE file_id={$post['file_id']}");
    }

}

$tabla->events = New filesEvents();

if($tabla->table_tags!==false){   

$tabla->events->tb_tags            = $tabla->table_tags; //'CLI_TAGS';     //TSK_TAGS   $tabla->table_tags = 'CONT_TAGS';
$tabla->events->tb_tags_pk         = 'ID';
$tabla->events->tb_tags_name       = 'NAME';
$tabla->events->tb_tags_caption    = 'CAPTION';
$tabla->events->tb_tags_color      = 'COLOR';

$tabla->events->tb_items_tags      = $tabla->tablename.'_TAGS';       
$tabla->events->tb_items_tags_pk   = 'ID';      
$tabla->events->tb_items_tags_item = 'FILE_ID';       
$tabla->events->tb_items_tags_tag  = 'TAG_ID';       

$tabla->events->tb_tags_displaytype = 'tab';  //'tab','float';  
$tabla->events->tb_tags_tabname     = 'tabs';
$tabla->events->tb_tags_tablabel    = t('TAGS','Etiquetas');

$tabla->events->id_parent          = $tabla->id_parent;

}