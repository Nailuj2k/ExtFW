<?php 

$tabla = new TableMysql('CLI_PAGES');
$tabla->profile = ($_ARGS['target']);


$id            = new Field();
$id->type      = 'int';
$id->width       = 15;
$id->len       = 10;
$id->fieldname = 'item_id';
$id->label     = 'Id';   
$id->pk     = true;   

$title = new Field();
$title->type      = 'varchar';
//$title->width       = 140;
$title->len       = 150;
$title->fieldname = 'item_title';
$title->label     = 'Título';   
$title->editable  = true ;
$title->sortable  = true;
$title->searchable  = true;
//$title->inline_edit  = true;
if(count($tabla->langs)>0) {
    $title->translatable = true;  
    $title->langs =  $tabla->langs;
}

$name = new Field();
$name->type      = 'varchar';
//$name->width       = 80;
$name->len       = 150;
$name->fieldname = 'item_name';
$name->label     = 'Slug';   
$name->editable  = true ;
$name->sortable  = true;
$name->searchable  = true;
//$name->inline_edit  = true;
$name->textafter = '<span style="font-size:0.8em;line-height: 33px;vertical-align: top;">URL amigable, sin espacios ni caracteres especiales.</span>'; 
if(count($tabla->langs)>0) {
    $name->translatable = true;  
    $name->langs =  $tabla->langs;
}

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = 'Imagen fondo/cabecera';   
$filename->len       = 100;
$filename->length    = 125;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = './media/page/images';
$filename->accepted_doc_extensions = array('.jpg');
$filename->textafter = 'Formato JPG, mínimo 300px de altura y anchura mínima de 900 ó 1000px'; 
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';

$text = new Field();
$text->type      = 'textarea';
$text->fieldname = 'item_text';
$text->label     = 'Contenido HTML';   
$text->editable = true;
$text->searchable = true;
$text->filtrable = false;  
//$description->collapsed  = true;
$text->hide = true;
$text->fieldset = 'html';
$text->wysiwyg = true;
$text->height = '500px';
if(count($tabla->langs)>0) {
    $text->translatable = true;  
    $text->langs =  $tabla->langs;
}

$code_css = new Field();
$code_css->type      = 'textarea';
$code_css->fieldname = 'item_code_css';
$code_css->label     = 'CSS. ';   
$code_css->editable = true;
$code_css->searchable = false;
$code_css->filtrable = false;  
//$description->collapsed  = true;
$code_css->hide = true;
$code_css->fieldset = 'css';
$code_css->wysiwyg = false;
$code_css->translatable = false;  

$code_js = new Field();
$code_js->type      = 'textarea';
$code_js->fieldname = 'item_code_js';
$code_js->label     = 'JavaScript';   
$code_js->editable = true;
$code_js->searchable = false;
$code_js->filtrable = false;  
//$description->collapsed  = true;
$code_js->hide = true;
$code_js->fieldset = 'js';
$code_js->wysiwyg = false;
$code_js->translatable = false;  
$code_js->default_value="$(function () {\n\n});";

$code_php = new Field();
$code_php->type      = 'textarea';
$code_php->fieldname = 'item_code_php';
$code_php->label     = 'PHP';   
$code_php->editable = true;
$code_php->searchable = false;
$code_php->filtrable = false;  
//$description->collapsed  = true;
$code_php->hide = true;
$code_php->fieldset = 'php';
$code_php->wysiwyg = false;
$code_php->translatable = false;  

$code = new Field();
$code->type      = 'textarea';
$code->fieldname = 'item_code';
$code->label     = 'Código extra, css, javascript, etc. ';   
$code->editable = true;
$code->searchable = false;
$code->filtrable = false;  
//$description->collapsed  = true;
$code->hide = true;
$code->fieldset = 'code';
$code->wysiwyg = false;
$code->translatable = false;  

$translatable   = new Field();
$translatable->type      = 'bool';
$translatable->width     = 30;
$translatable->fieldname = 'TRANSLATABLE';
$translatable->label     = 'Traducible';   
$translatable->editable  = Administrador();
$translatable->filtrable = true;

$level = new Field();
$level->fieldname = 'item_level';
$level->label     = 'Level';   
$level->type      = 'int';
$level->len       = 8;
$level->width     = 40;
$level->editable  = Administrador();
$level->sortable  = true;
$level->hide  = true;
$level->default_value='100';

$menu = new Field();
$menu->fieldname = 'id_menu';
$menu->label     = 'Menú';   
$menu->type      = 'select';
$menu->len       = 5;
$menu->width     = 20;
$menu->editable  = true;
$menu->sortable  = true;
$menu->values=Menu::$menus;

$parent = new Field();
$parent->fieldname = 'item_parent';
$parent->label     = 'Parent';   
$parent->type      = 'int';
$parent->len       = 7;
$parent->width     = 20;
$parent->editable  = true;
$parent->sortable  = true;

$active = new Field();
$active->fieldname = 'item_active';
$active->label     = 'Activo';   
$active->type      = 'bool';
$active->width     = 20;
$active->editable  = true;
$active->sortable  = true;
$active->default_value = 1;

$inline_edit = new Field();
$inline_edit->fieldname = 'inline_edit';
$inline_edit->label     = 'Inline edit';   
$inline_edit->type      = 'bool';
$inline_edit->width     = 20;
$inline_edit->editable  = true;
$inline_edit->sortable  = true;
$inline_edit->default_value = 1;
$inline_edit->hide = true;

$enabled = new Field();
$enabled->fieldname = 'item_visible';  // ALTER TABLE `TB_ITEM` CHANGE `item_enabled` `item_visible` INT(1) NULL DEFAULT '1';
$enabled->label     = 'Visible';   
$enabled->type      = 'bool';
$enabled->width     = 20;
$enabled->editable  = true;
$enabled->sortable  = true;
$enabled->default_value = 1;

$order = new Field();
$order->fieldname = 'item_order';
$order->label     = 'Orden';   
$order->type      = 'int';
$order->len       = 5;
$order->width     = 20;
$order->editable  = true;
$order->sortable  = true;

$html   = new Field();
$html->type      = 'bool';
$html->width     = 30;
$html->fieldname = 'HTML';
$html->label     = 'Código HTML';   
$html->editable  = Administrador();
$html->filtrable = true;
$html->default_value = 1;

$gallery = new Field();
$gallery->type      = 'bool';
$gallery->default_value = '0';
$gallery->fieldname = 'GALLERY';
$gallery->label     = 'Galería de fotos';   
$gallery->editable  = true; //Administrador();   
$gallery->hide  = true;

$files = new Field();
$files->type      = 'bool';
$files->default_value = '0';
$files->fieldname = 'FILES';
$files->label     = 'Archivos Anexos';   
$files->editable  = true; //Administrador();   
$files->hide  = true;

$docs = new Field();
$docs->type      = 'bool';
$docs->default_value = '0';
$docs->fieldname = 'DOCS';
$docs->label     = 'Módulo documentos';   
$docs->textafter = '<span style="line-height: 33px;vertical-align: top;">Anula las dos anteriores opciones</span>';   
$docs->editable  = true; //Administrador();   
$docs->hide  = true;

$comments = new Field();
$comments->type      = 'bool';
$comments->default_value = '0';
$comments->fieldname = 'ALLOW_COMMENTS';
$comments->label     = 'Habilitar comentarios';   
$comments->default_value = '1';   
$comments->editable  = Administrador();   

$rating = new Field();
$rating->type      = 'bool';
$rating->default_value = '0';
$rating->fieldname = 'ALLOW_RATING';
$rating->label     = 'Habilitar Rating';
$rating->default_value = '1';   
$rating->editable  = Administrador();   

$tabla->title = 'Páginas';
$tabla->verbose=false;
$tabla->output='table';
//$tabla->output='custom';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['pages']['options'],'num_rows',20);
$tabla->show_empty_rows = false;
//$tabla->inline_edit = true;

$tabla->addCol($id);
$tabla->addCol($title);
//$tabla->addCol($caption);
$tabla->addCol($name);
$tabla->addCol($filename);
//$tabla->addCol($module);
$tabla->addCol($text);
$tabla->addCol($code_css);
$tabla->addCol($code_js);
//$tabla->addCol($code_php);
$tabla->addCol($code);
////////////////////$tabla->addCol($translatable);
$tabla->addCol($level);
//$tabla->addCol($menu);
//$tabla->addCol($parent);
$tabla->addCol($active);
$tabla->addCol($enabled);
//$tabla->addCol($inline_edit);
//$tabla->addCol($order);
//////////////////////
$tabla->addCol($html);
$tabla->addCol($gallery);
$tabla->addCol($files);
$tabla->addCol($docs);

$tabla->addCols([
    $tabla->field(        'KEYWORDS',  'textarea' )->wysiwyg(false)->label('Keywords')->fieldset('SEO')->hide(true), //->height(4),
    $tabla->field(        'DESCRIPTION',  'textarea' )->wysiwyg(false)->label('Description')->fieldset('SEO')->hide(true)// ->height(4)
]);

$tabla->addCol($comments);
$tabla->addCol($rating);

$tabla->detail_tables=array();

if (!$tabla->profile) 
$tabla->detail_tables[] = 'CLI_PAGES_FILES';
$tabla->detail_tables_keys['CLI_PAGES_FILES'] = 'id_item';

//$tabla->detail_tables['id_item'] ='CLI_PAGES_FILES'; 

$tabla->addFieldset($tabla->default_fieldset_name, 'Datos'); 
$tabla->addFieldset('html', 'HTML'); 
$tabla->addFieldset('css', 'CSS'); 
$tabla->addFieldset('js', 'JavaScript'); 
$tabla->addFieldset('code', 'Código'); 
//$tabla->addFieldset('php', 'PHP'); 

$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Administrador();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;

class pagesEvents extends defaultTableEvents implements iEvents{ 
  
    function editable($owner,$id=false){
        global $_ACL;
        if (!$_SESSION['userid']) return false;

        $item = TableMySql::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_url = (SELECT item_name FROM ".TB_PAGES." WHERE item_id='".$id."')");

        $parents = array();
        $module_id = $item['item_id'];
        $parent_id = $item['item_parent'];
        $ir = $_ACL->getItemRoles($module_id);
        $ur = $_ACL->getUserRoles();
        if(is_array($ir)&&is_array($ur)){
            //Vars::debug_var($module_id,'$module_id');     
            //Vars::debug_var($parent_id,'$parent_id');     
            $laps = 0;
            if($parent_id>0) {
                $parents[] = $parent_id;
                while ($parent_id>0){
                    if(++$laps>10) break;  // prevent recursion
                    //Vars::debug_var("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_id = '".$parent_id."'",'SQL');     
                    $item = TableMySql::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_id = '".$parent_id."'");
                    $module_id = $item['item_id']; 
                    $parent_id = $item['item_parent'];
                    if($parent_id>0) { 
                        $parents[] = $parent_id;
                        $ir = array_merge($ir,$_ACL->getItemRoles($module_id));
                    }
                }
            }
    
            $uir = array_intersect(array_values($ir),array_values($ur));       

            $ed = count($uir)>0;
        }
        return $ed; 
    }

  function OnDrawRow($owner,&$row,&$class){
      $filename = $owner->colByName('FILE_NAME')->uploaddir.'/'.$row['FILE_NAME'];
      if($row['FILE_NAME'] && file_exists($filename)){

          $hash = filemtime($filename);
          $row['FILE_NAME']  = "<a class=\"{$ext} open_file_image\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME']['URL']}\">"
                             . "<img style=\"height:22px;\" src=\"{$row['IMAGES']['FILE_NAME']['THUMB']}?ver={$hash}\"    ></a>";
      }else{
          $row['FILE_NAME']='<i class="fa fa-image"></i>';
      }

      //$row['item_name'] = '<a target="new" href="'.$row['item_name'].'">'.$row['item_name'].'</a>';     
      $row['item_name'] = '<a target="new" href="'.(MODULE==CFG::$vars['default_module']?'':MODULE.'/').$row['item_name'].'">'.$row['item_name'].'</a>';     

  }
  
  function OnInsert($owner,&$result,&$post) { 

      if(!$post['item_name'])
          $post['item_name']=Str::sanitizeName($post['item_title']);

      //if(!$post['item_caption'])
      //    $post['item_caption']=$post['item_title'];

      if (CFG::$vars['options']['change_underscores'])
          $post['item_name'] = Str::sanitizeName(str_replace('_','-',$post['item_name']));
      else
          $post['item_name'] = Str::sanitizeName($post['item_name']);
      $result['the_filename']=$post['item_name'].'.jpg'; 

      $type = '8';
      $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'page - insert\',\''.Str::escape(print_r($post,true)).'\')';
      Table::sqlExec($log_sql);

  }
  
  function OnBeforeUpdate($owner,$id) {
     
     $owner->perms['edit']= $owner->perms['edit'] || $this->editable($owner,$id);

     $row = $owner->getRow($id);
     
     foreach ($owner->langs as $k=>$v){
             $owner->colByName('item_text_'.$k)->wysiwyg = $row['HTML']!='1';
            //$owner->colByName('item_text_'.$k)->translatable = $row['TRANSLATABLE']=='1'; 
     }         
     $owner->colByName('item_text')->wysiwyg = $row['HTML']!='1';     
     //$owner->colByName('item_text')->translatable = $row['TRANSLATABLE']=='1'; 

     //if($row['TRANSLATABLE']!='1') $owner->colByName('item_text')->html_before = '';
  }

  function OnUpdate($owner,&$result,&$post) { 
     $owner->perms['edit']=    $owner->perms['edit'] || $this->editable($owner,$post['item_id']);

      if(!$post['item_name'])
          $post['item_name']=Str::sanitizeName($post['item_title']);
      //if(!$post['item_caption'])
      //    $post['item_caption']=$post['item_title'];

      if (CFG::$vars['options']['change_underscores'])
          $post['item_name'] = Str::sanitizeName(str_replace('_','-',$post['item_name']));
      else
          $post['item_name'] = Str::sanitizeName($post['item_name']);

      $result['the_filename']=$post['item_name'].'.jpg'; 
      if($post['fake_input_FILE_NAME']==''){
           unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['the_filename']);
           unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.TN_PREFIX.$result['the_filename']);
           unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.BIG_PREFIX.$result['the_filename']);
           unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.Str::get_file_name($result['the_filename']).'.webp' );
      }	  

      $type = '8';
      $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'page - update\',\''.Str::escape(print_r($post,true)).'\')';
      Table::sqlExec($log_sql);

  }


  function OnDelete($owner,&$result,$id){
    
    if($owner->detail_tables){
        foreach($owner->detail_tables as $detail_table){
            //$result['msg'] = 'SELECT COUNT(0) FROM '.$detail_table.' WHERE '.$owner->pk->fieldname.' = '.$id;
            $childs = $owner->recordCount('SELECT COUNT(0) FROM '.$detail_table.' WHERE '.$owner->pk->fieldname.' = '.$id);
            if($childs >0) {
                //$owner->sqlQuery('DELETE FROM '.$detail_table.' WHERE '.$owner->pk->fieldname.' = '.$id);
                $result['error'] = 1;  // Abort deletion !!
                $result['msg'] = 'Esta fila no puede eliminarse porque tiene '.$childs.' filas hijas'; // ['.$detail_table.']['.$owner->pk->fieldname.']['.$id.']';  //TODO: Translate this
            }
        }
    }

    $type = '8';
    $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'page - delete\',\''.Str::escape('DELETE FROM '.$owner->tablename.' WHERE '.$owner->pk->fieldname.' = '.$id).'\')';
    Table::sqlExec($log_sql);

    //$result['error'] = 1;  // Abort deletion !!
    //$result['msg'] = $result['msg'] ?$result['msg'] :'Esta fila no puede eliminarse'; // ['.$detail_table.']['.$owner->pk->fieldname.']['.$id.']';  //TODO: Translate this

  }


  
  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
     if  ($col->fieldname=='FILE_NAME')    $result['local_file'] = $result['the_filename'];
  }

    function OnBeforeShowForm($owner,&$form,$id) {
        if (!$owner->profile) return false;
        if($owner->state=='filter')return false;
        //parent::OnBeforeShowForm($owner,$form,$id);
        //foreach( $owner->cols as $col) { if($col->fieldname == 'NOT_DATE') $col->default_value =  $owner->sql_currentdate(); }
        /*************
        if($id && $owner->perms['edit']) {
             Table::$module_name = 'control_panel';
             $parent = $id;
             $markup_ajax_loader = '<p style="text-align:center;border:1px solid green;">Loading ...</p>';        
             $html_files = new formElementHtml();
             $html_files->html = '<div class="datatable" id="T-CLI_PAGES_FILES">'.$markup_ajax_loader.'</div>'
                               .  '<script>'
                               .  '    load_page("control_panel","CLI_PAGES_FILES",1,'.$id.',1);'
                               .  '</script>' ;                                     
             Table::show_table('CLI_PAGES_FILES','control_panel',false); //,1,true);

             $fs_files = new fieldset('files','Archivos');
             $fs_files->displaytype = 'tab';
             $fs_files->addElement($html_files);
             $form->addElement($fs_files);
        }
        ********/
    }

    
}

$tabla->events = New pagesEvents();
