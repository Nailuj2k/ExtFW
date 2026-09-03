<?php 

$tabla = new TableMysql('CFG_MUNICIPIO');

$municipio_id = new Field();
$municipio_id->type      = 'int';
$municipio_id->len       = 6;
$municipio_id->fieldname = 'municipio_id';
$municipio_id->label     = 'Municipio';
$municipio_id->editable  = true ;
$municipio_id->hide  = true;
$tabla->addCol($municipio_id);

$id_provincia = new Field();
$id_provincia->type      = 'int';
$id_provincia->len       = 2;
$id_provincia->fieldname = 'id_provincia';
$id_provincia->label     = 'Id provincia';
$id_provincia->editable  = true ;
$id_provincia->hide  = true;
$tabla->addCol($id_provincia);

$municipio_codigo = new Field();
$municipio_codigo->type      = 'varchar';
$municipio_codigo->len       = 4;
$municipio_codigo->fieldname = 'municipio_codigo';
$municipio_codigo->label     = 'Código';
$municipio_codigo->editable  = true ;
//$municipio_codigo->hide  = true;
$tabla->addCol($municipio_codigo);

$municipio_name = new Field();
$municipio_name->type      = 'varchar';
$municipio_name->len       = 80;
$municipio_name->fieldname = 'municipio_name';
$municipio_name->label     = 'Municipio';
$municipio_name->editable  = true ;
$municipio_name->sortable  = true;
$municipio_name->searchable  = true;
$tabla->addCol($municipio_name);
/*
$municipio_code = new Field();
$municipio_code->type      = 'varchar';
$municipio_code->len       = 6;
$municipio_code->fieldname = 'municipio_code';
$municipio_code->label     = 'Code';
$municipio_code->editable  = true ;
$municipio_code->hide  = true;
//$tabla->addCol($municipio_code);
*/
$tabla->name = 'CFG_MUNICIPIO';
$tabla->title = 'Municipios';
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['locations']['options'],'num_rows',20);
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = Administrador();

$tabla->orderby = 'municipio_name';
$tabla->setParent('id_provincia',$parent);

if(CFG::$vars['users']['field']['county']/*||CFG::$vars['shop']['enabled']*/) {
    $tabla->classname = 'column center';
    $tabla->detail_tables=array('CFG_LOCALIDAD');
}else{
    $tabla->classname = 'column right';
}

class CFG_MUNICIPIOEvents extends defaultTableEvents implements iEvents{
  function OnShow($owner){
    $owner->paginator->begin_end_links = false;
    $owner->paginator->prev_next_links = false;
    $owner->paginator->page_links = true;
    $owner->paginator->aux_links  = false; //(!$this->paginator_simple);
    $owner->paginator->label_page = false; //(!$this->paginator_simple);
    $owner->paginator->label_item = false; //(!$this->paginator_simple);
    $owner->paginator->labels['add'] = '<i class="fa fa-plus"></i>';
    $owner->show_inputsearch = false;
  }
}
$tabla->events = New CFG_MUNICIPIOEvents();
