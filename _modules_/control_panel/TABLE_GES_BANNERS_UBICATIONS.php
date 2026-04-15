<?php 

$tabla = new TableMysql('GES_BANNERS_UBICATIONS');

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
$name->label     = 'Identificador';
$name->placeholder  = 'Identificador CSS (id)';
$name->editable  = true ;
$name->sortable  = true;
$name->width     = 300;
$tabla->addCol($name);

$tabla->name = 'GES_BANNERS_UBICATIONS';
$tabla->title = 'Ubicaciones';
$tabla->showtitle=true;
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


class GES_BANNERS_UBICATIONSEvents extends defaultTableEvents implements iEvents{
  function OnInsert($owner,&$result,&$post) { 
  }
  function OnUpdate($owner,&$result,&$post) { 
  }
  function OnDelete($owner,&$result,$id)    { 
  }

}
$tabla->events = New GES_BANNERS_UBICATIONSEvents();

