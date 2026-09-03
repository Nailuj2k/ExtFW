<?php 

$tabla = new TableMysql('GES_BANNERS_TYPES');

$id = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->fieldname = 'ID';
$id->label     = 'Id';
$id->hide  = true ;
$id->pk  = true ;
$tabla->addCol($id);

$name = new Field();
$name->type      = 'varchar';
$name->len       = 100;
$name->fieldname = 'NAME';
$name->label     = 'Nombre';
$name->editable  = true ;
$name->sortable  = true;
$name->width     = 300;
$tabla->addCol($name);

$w = new Field();
$w->type      = 'int';
$w->len       = 7;
$w->fieldname = 'W';
$w->label     = 'Anchura';
$w->editable  = true ;
$tabla->addCol($w);

$h = new Field();
$h->type      = 'int';
$h->len       = 7;
$h->fieldname = 'H';
$h->label     = 'Altura';
$h->editable  = true ;
$tabla->addCol($h);


$id_content = new Field();
$id_content->fieldname = 'ID_TYPE';
$id_content->label     = 'Tipo'; 
$id_content->width     = 150;
$id_content->max_chars = 30;        
$id_content->type      = 'select';
$id_content->values    = array('1'=>'Imagen local','2'=>'Imagen remota','3'=>'Código html o Js'); 
$id_content->editable  = Administrador();
$id_content->default_value = 1; //$_SESSION['socioid'];
//$id_user->not_null  = true;
//$id_type->allowNull = true;   
//$id_type->filtrable = true;   
//$id_type->multiselect = true;   
$tabla->addCol($id_content);

$tabla->name = 'GES_BANNERS_TYPES';
$tabla->title = 'Tipos de banner';
$tabla->showtitle=false;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 12;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =false;
$tabla->inline_edit = false;
$tabla->orderby='NAME';

$tabla->addActiveCol();
 
$tabla->perms['delete'] = false;
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;


class GES_BANNERS_TYPESEvents extends defaultTableEvents implements iEvents{
  function OnInsert($owner,&$result,&$post) { 
  }
  function OnUpdate($owner,&$result,&$post) { 
  }
  function OnDelete($owner,&$result,$id)    { 
  }

}
$tabla->events = New GES_BANNERS_TYPESEvents();

// Robapáginas / Square 300 300  img local
//  Megabanner 728 90              codigo
