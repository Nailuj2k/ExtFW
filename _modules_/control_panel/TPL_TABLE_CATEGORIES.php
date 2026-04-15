<?php 

// $tabla = new TableMySql('CLI_CATEGORIES');


$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'CATEGORIE_ID';
$id->label     = 'Id';   
//$id->hide      = true;

//if($tabla->parent){
//if(isset($_SESSION['PAGE_FILES_ID_PARENT'])){
$id_parent = new Field();
$id_parent->type      = 'int';
$id_parent->width     = 10;
$id_parent->fieldname = 'ID_PARENT';
$id_parent->label     = 'Item';   
//$id_parent->hide      = true;
$id_parent->editable  = !isset($_SESSION['PAGE_FILES_ID_PARENT']);
//}

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->type      = 'varchar';
$name->len       = 100;
$name->editable  = true;

if($tabla->uploaddir['FILE_NAME']){
$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = 'Imagen pequeña';   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = $tabla->uploaddir['FILE_NAME']; //'./media/'.MODULE_SHOP.'/categories/small';
$filename->accepted_doc_extensions = array('.png');
$filename->searchable = true;
$filename->textafter = 'Formato PNG, con fondo transparente.';
}

if($tabla->uploaddir['FILE_NAME_BIG']){
$filename_big = new Field();
$filename_big->fieldname = 'FILE_NAME_BIG';
$filename_big->label     = 'Imagen grande';   
$filename_big->len       = 100;
$filename_big->type      = 'file';
$filename_big->editable  = Administrador();
$filename_big->uploaddir = $tabla->uploaddir['FILE_NAME_BIG']; //'./media/'.MODULE_SHOP.'/categories/big';
$filename_big->accepted_doc_extensions = array('.png','.jpg');
$filename_big->searchable = true;
$filename_big->textafter = 'Formato JPG.';
//$filename->hide = true;
}

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
$order->fieldname = 'CAT_ORDER';
$order->label     = 'Orden';
$order->len       = 8;   
$order->editable  = true;

$visible = new Field();
$visible->fieldname = 'VISIBLE';
$visible->label     = 'Visible';   
$visible->type      = 'bool';
$visible->editable  = true;
$visible->default_value  = 1;


$tabla->title = 'Categorias';
$tabla->showtitle = false;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 6;

$tabla->addCol($id);
//if($tabla->parent)
//if(isset($_SESSION['PAGE_FILES_ID_PARENT']))
    $tabla->addCol($id_parent);
$tabla->addCol($name);
if($tabla->uploaddir['FILE_NAME'])
    $tabla->addCol($filename);
if($tabla->uploaddir['FILE_NAME_BIG'])
    $tabla->addCol($filename_big);
$tabla->addCol($description);
$tabla->addCol($visible);
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

class categoriesEvents extends defaultTableEvents implements iEvents{ 
  
    function OnDrawRow($owner,&$row,&$class){
        if($owner->uploaddir['FILE_NAME_BIG'])
            $row['FILE_NAME_BIG'] =  "<a class=\"{$ext} swipebox framed\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME_BIG']['URL']}\"><img style=\"height:22px;\" src=\"{$row['IMAGES']['FILE_NAME_BIG']['THUMB']}\">{$row['NAME']}</a>";
        if($owner->uploaddir['FILE_NAME'])
            $row['FILE_NAME']     =  "<a class=\"{$ext} swipebox framed\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME']['URL']}\"    ><img style=\"height:22px;\" src=\"{$row['IMAGES']['FILE_NAME']['THUMB']}\"    >{$row['NAME']}</a>";
    }
  
  
  function OnInsert($owner,&$result,&$post) { 
	  $_SESSION['_CACHE']['values']['CLI_categories'] = false; 
	  $result['the_filename']=$post['CATEGORIE_ID'];
  }
  
  function OnUpdate($owner,&$result,&$post) { 
	  $_SESSION['_CACHE']['values']['CLI_categories_all'] = false; 
	  $result['the_filename']=$post['CATEGORIE_ID'];
	  
  }
  
  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
    if      ($col->fieldname=='FILE_NAME')      $result['local_file'] = $result['the_filename'].'.png';
    else if ($col->fieldname=='FILE_NAME_BIG')  $result['local_file'] = $result['the_filename'].'.jpg';
  }
    
  /*
  function OnDelete($owner,&$result,$id)    { 
    $childs = recordCount( TB_ITEMS_TAGS,'WHERE CATEGORIE_ID = '.$id);
    if($childs >0) {
      $result['error'] =5;
      $result['msg'] = 'Esta categoria no puede eliminarse porque tiene '.$childs.' documentos';
    }else{
      $_SESSION['_CACHE']['values']['CLI_categories'] = false; 
      $_SESSION['_CACHE']['values']['CLI_categories_all'] = false; 
    }
  }
  */
}

$tabla->events = New categoriesEvents();

