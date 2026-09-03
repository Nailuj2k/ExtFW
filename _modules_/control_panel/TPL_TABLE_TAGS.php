<?php 

// $tabla = new TableMysql('CLI_TAGS');

$tabla->uploaddir= $tabla->uploaddir?$tabla->uploaddir:'./media/tag/images';

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$id_parent = new Field();
$id_parent->type      = 'int';
$id_parent->width     = 10;
$id_parent->fieldname = 'ID_PARENT';
$id_parent->label     = 'Item';   
//$id_parent->hide      = true;
$id_parent->editable  = !isset($_SESSION['PAGE_FILES_ID_PARENT']);

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->type      = 'varchar';
$name->len       = 100;
$name->editable  = true;

$caption = new Field();
$caption->fieldname = 'CAPTION';
$caption->label     = 'Etiqueta';   
$caption->len       = 50;
$caption->type      = 'varchar';
$caption->editable  = true;
$caption->required  = true;

$color = new Field();
$color->fieldname = 'COLOR';
$color->label     = 'Color';   
$color->type      = 'color';
$color->editable  = true;//Administrador();

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = 'Logo';   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = $tabla->uploaddir;
$filename->accepted_doc_extensions = array('.png');
$filename->searchable = true;
$filename->textafter = 'Formato JPG';  //, con fondo transparente.';
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';

$header_file = new Field();
$header_file->fieldname = 'HEADER_FILE_NAME';
$header_file->label     = 'Imagen cabecera';   
$header_file->len       = 100;
$header_file->length    = 125;
$header_file->type      = 'file';
$header_file->editable  = Administrador();
$header_file->uploaddir = $tabla->uploaddir;
$header_file->accepted_doc_extensions = array('.jpg');
$header_file->textafter = 'Formato JPG, mínimo 300px de altura y anchura mínima de 900 ó 1000px'; 
$header_file->action_if_exists_disabled = true;
$header_file->action_if_exists = 'replace';

$description = new Field();
$description->type       = 'textarea';
$description->fieldname  = 'DESCRIPTION';
$description->label      = 'Descripción';   
$description->editable   = Administrador();
$description->hide       = true;
$description->searchable = true;
$description->filtrable  = false;  
$description->wysiwyg  = false;  

$order            = new Field();
$order->type      = 'int';
$order->width       = 15;
$order->fieldname = 'ID_ORDER';
$order->label     = 'Orden';
$order->len       = 8;   
$order->editable  = true;

$tabla->title = 'Etiquetas';
$tabla->showtitle = false;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 20;

$tabla->addCol($id);
$tabla->addCol($id_parent);
$tabla->addCol($caption);
$tabla->addCol($name);
$tabla->addCol($header_file);
$tabla->addCol($filename);
$tabla->addCol($color);
$tabla->addCol($description);
$tabla->addCol($order);
$tabla->addActiveCol();
$tabla->addWhoColumns();

$tabla->perms['view']   = $tabla->perms['view'] || Administrador();
$tabla->perms['filter'] = Administrador();
$tabla->perms['add']    = $tabla->perms['add'] || Administrador(); 
$tabla->perms['delete'] = $tabla->perms['delete'] || Administrador(); 
$tabla->perms['edit']   = $tabla->perms['edit'] || Administrador(); //$_ACL->hasPermission('tienda_edit'); //
$tabla->perms['reload'] = $tabla->perms['reload'] || Administrador();  
$tabla->perms['setup']  = Root();  

//$tabla->lists = array('categoria');

// ALTER TABLE RRHH_TAGS  MODIFY COLUMN ID  INT NOT NULL UNIQUE AUTO_INCREMENT FIRST;

class catStEvents extends defaultTableEvents implements iEvents{ 
  
    function OnDrawRow($owner,&$row,&$class){
        $filename = $owner->uploaddir.'/'.$row['FILE_NAME'];
        if($row['FILE_NAME'] && file_exists($filename)){
          $row['FILE_NAME']  =  "<a class=\"{$ext} open_file_image\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME']['URL']}\">"
                                ."<img style=\"height:22px;\" src=\"{$row['IMAGES']['FILE_NAME']['THUMB']}\"    ></a>";
        }else{
          $row['FILE_NAME']='<i class="fa fa-image"></i>';
        }

      $header_filename = $owner->uploaddir.'/header_'.$row['HEADER_FILE_NAME'];
      if($row['HEADER_FILE_NAME'] && file_exists($filename)){
          $row['HEADER_FILE_NAME']  =  "<a class=\"{$ext} open_file_image\" rel=\"gallery\" href=\"{$row['IMAGES']['HEADER_FILE_NAME']['URL']}\">"
                                ."<img style=\"height:22px;\" src=\"{$row['IMAGES']['HEADER_FILE_NAME']['THUMB']}?ver=".CFG::$vars['site']['lastupdate']."\"    ></a>";
      }else{
          $row['HEADER_FILE_NAME']='<i class="fa fa-image"></i>';
      }


       // $row['FILE_NAME_LOGO_MINI']  =  "<a class=\"{$ext} swipebox framed\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME_LOGO_MINI']['URL']}\">"."<img src=\"{$row['IMAGES']['FILE_NAME_LOGO_MINI']['THUMB']}\"    ></a>";
    }

  
  function OnDelete($owner,&$result,$id)    { 
    /**
    $childs = $owner->recordCount( 'SELECT ID FROM CLI_PRODUCTS_TAGS WHERE PRODUCT_ID = '.$id);
    if($childs >0) {
      $result['error'] =5;
      $result['msg'] = 'Esta etiqueta no puede eliminarse porque aparece en '.$childs.' documentos';
    }else{
    }
    */
  }

  function OnInsert($owner,&$result,&$post) { 
      $result['the_filename']=$post['NAME'].'.png';
      $post['NAME'] = trim(Str::sanitizeName($post['CAPTION']));
      if(!$post['NAME']) {
          $result['error'] = 1;
          $result['msg'] = 'Falta el identificador';
      }else{   
          $r = $owner->recordCount( 'SELECT ID FROM '.$owner->tablename.' WHERE NAME = \''.$post['NAME'].'\'');
          if($r >0) {
              $result['error'] =5;
              $result['msg'] = 'Ya existe una categoría con el identificador '.$post['NAME'];
          }else{
          }
      }
      $result['the_header_filename']='header_'.str_replace(' ','_',strtolower($post['NAME'])).'.jpg';

  }
  
  function OnUpdate($owner,&$result,&$post) { 

      $result['the_filename']=$post['NAME'].'.png';
  
      if($post['fake_input_FILE_NAME']==''){
           unlink($owner->uploaddir.'/'.$result['the_filename']);
           unlink($owner->uploaddir.'/'.TN_PREFIX.$result['the_filename']);
      } 
      
      if(!$post['NAME']) $post['NAME'] = trim(Str::sanitizeName($post['CAPTION']));
      if(!$post['CAPTION']) $post['CAPTION'] = ucwords(trim($post['NAME']));

      if(!$post['NAME']) {
          $result['error'] = 1;
          $result['msg'] = 'Falta el identificador';
      }else{   
          $r = $owner->recordCount( 'SELECT ID FROM '.$owner->tablename.' WHERE NAME = \''.$post['NAME'].'\' AND ID <> '.$post['ID']);
          if($r >0) {
              $result['error'] =5;
              $result['msg'] = 'Ya existe una categoría con el identificador '.$post['NAME'];
          }else{
          }
      }

      $result['the_header_filename']='header_'.str_replace(' ','_',strtolower($post['NAME'])).'.jpg';
      if($post['fake_input_HEADER_FILE_NAME']==''){
           unlink($owner->uploaddir.'/'.$result['the_header_filename']);
           unlink($owner->uploaddir.'/'.TN_PREFIX.$result['the_header_filename']);
      } 

      Table::sqlExec("UPDATE CFG_CFG SET V='".hash('crc32b',time())."' WHERE K='site.lastupdate'");

  }
  
  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
     if  ($col->fieldname=='FILE_NAME')    $result['local_file'] = $result['the_filename'];
     if  ($col->fieldname=='HEADER_FILE_NAME')    $result['local_file'] = $result['the_header_filename'];
  }
 
  function OnAfterUpdate($owner,&$result,&$post){
      if($post['fake_input_FILE_NAME']=='')        $owner->sql_exec('UPDATE '.$owner->tablename." SET FILE_NAME=''        WHERE ID={$post['ID']}");
      if($post['fake_input_HEADER_FILE_NAME']=='') $owner->sql_exec('UPDATE '.$owner->tablename." SET HEADER_FILE_NAME='' WHERE ID={$post['ID']}");
  }
    
  function OnAfterCreate($owner){ 
    if($owner->recordCount()<1){
      $owner->sql_exec("INSERT INTO {$owner->tablename} (CAPTION,NAME,COLOR) VALUES('Etiqueta 1','tag_1','#33b075')");
      $owner->sql_exec("INSERT INTO {$owner->tablename} (CAPTION,NAME,COLOR) VALUES('Etiqueta 2','tag_2','#ff0000')");
    }
  } 

}

$tabla->events = New catStEvents();

