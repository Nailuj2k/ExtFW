<?php 

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->type      = 'varchar';
$name->len       = 100;
$name->editable  = true;

$url = new Field();
$url->fieldname = 'LINK';
$url->label     = 'Url';   
$url->type      = 'varchar';
$url->len       = 200;
$url->editable  = true;
$url->textafter = 'Enlace a un producto o página';  //, con fondo transparente.';

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = 'Imagen';   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = './media/slider/images';
$filename->accepted_doc_extensions = array('.jpg');
$filename->searchable = true;
$filename->textafter = 'Formato JPG';  //, con fondo transparente.';
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';

$source = new Field();
$source->fieldname = 'SOURCE';
$source->label     = 'Origen vídeo';   
$source->type      = 'varchar';
$source->len       = 200;
$source->editable  = true;
//$source->textafter = 'Youtube ID o ruta local de video';

$type = new Field();
$type->type      = 'select';
$type->values     = ['0'=>'Imagen','1'=>'Video','2'=>'YouTube'];
$type->values_all = ['0'=>'Imagen','1'=>'Video','2'=>'YouTube'];
$type->len       = 5;
$type->fieldname = 'FRAME_TYPE';
$type->label     = 'Tipo';   
$type->editable = Administrador();
$type->default_value = '0';
$type->filtrable = true;  

$color = new Field();
$color->fieldname = 'COLOR';
$color->label     = 'Color de fondo';   
$color->type      = 'color';
$color->editable  = Administrador();

$description = new Field();
$description->type       = 'textarea';
$description->fieldname  = 'DESCRIPTION';
$description->label      = 'Descripción';   
$description->editable   = Administrador();
$description->hide       = true;
$description->searchable = true;
$description->filtrable  = false;  
$description->wysiwyg    = false;  

$order            = new Field();
$order->type      = 'int';
$order->width       = 15;
$order->fieldname = 'ID_ORDER';
$order->label     = 'Orden';
$order->len       = 8;   
$order->editable  = true;

$tabla = new TableMysql('CFG_SLIDER');
$tabla->title = 'Categorias';
$tabla->showtitle = false;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 6;

$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($url);
$tabla->addCol($type);
$tabla->addCol($filename);
$tabla->addCol($source);
$tabla->addCol($color);
$tabla->addCol($description);
$tabla->addCol($order);
$tabla->addActiveCol();
$tabla->addWhoColumns();


$tabla->perms['view']   = Administrador();
$tabla->perms['filter'] = Administrador();
$tabla->perms['add']    = Administrador(); 
$tabla->perms['delete'] = Administrador(); 
$tabla->perms['edit']   = Administrador(); 
$tabla->perms['reload'] = Administrador();  
$tabla->perms['setup']  = Root();  



class slidersEvents extends defaultTableEvents implements iEvents{ 
  
  function OnDrawRow($owner,&$row,&$class){

      if($row['FRAME_TYPE'] == 1 ){

                $thumb = '_images_/filetypes/icon_mp4.png';
                $cls    = 'open_file_video';
             // $href = './media/slider/images/'.$row['FILE_NAME'];
                $href = $row['SOURCE'];

      }else if ($row['FRAME_TYPE'] == 2){
        
                $thumb  = 'https://img.youtube.com/vi/'.$row['SOURCE'].'/mqdefault.jpg';
                $cls    = 'open_file_video';
                $href   = 'https://img.youtube.com/vi/'.$row['SOURCE'];
        
      } else {

                $thumb  = $row['IMAGES']['FILE_NAME']['THUMB'];
                $cls    = 'open_file_image';
                $href   = $row['IMAGES']['FILE_NAME']['URL'];

      } 

      $row['FILE_NAME']  =  "<a class=\"{$ext} {$cls}\" href=\"{$href}\"><img style=\"height:22px;\" src=\"{$thumb}\"    ></a>";


  }
  
  
  function OnInsert($owner,&$result,&$post) {   }
  
  
  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){  }
    
}

$tabla->events = New slidersEvents();

