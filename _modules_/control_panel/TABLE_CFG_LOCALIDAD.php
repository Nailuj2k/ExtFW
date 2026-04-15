<?php 
/* Auto created */

$tabla = new TableMysql('CFG_LOCALIDAD');

$localidad_id = new Field();
$localidad_id->type      = 'int';
$localidad_id->len       = 6;
$localidad_id->fieldname = 'localidad_id';       //Asturias, Orense, Palencia, Pontevedra (*)
$localidad_id->label     = 'Localidad';
//$localidad_id->editable  = true ;
//$localidad_id->hide  = true;
$tabla->addCol($localidad_id);

$localidad_cp = new Field();
$localidad_cp->type      = 'varchar';
$localidad_cp->len       = 5;
$localidad_cp->width     = 90;
$localidad_cp->fieldname = 'localidad_cp';
$localidad_cp->label     = 'CP';
$localidad_cp->editable  = true ;
$localidad_cp->sortable  = true;
$tabla->addCol($localidad_cp);

$localidad_name = new Field();
$localidad_name->type      = 'varchar';
$localidad_name->len       = 80;
$localidad_name->width     = 330;
$localidad_name->fieldname = 'localidad_name';
$localidad_name->label     = 'Localidad';
$localidad_name->editable  = true ;
$localidad_name->sortable  = true;
$localidad_name->searchable  = true;
$tabla->addCol($localidad_name);

$id_provincia = new Field();
$id_provincia->type      = 'int';
$id_provincia->len       = 2;
$id_provincia->width     = 80;
$id_provincia->fieldname = 'id_provincia';
$id_provincia->label     = 'Id provincia';
$id_provincia->readonly  = true ;
//$id_provincia->hide  = true;
$tabla->addCol($id_provincia);

$id_municipio = new Field();
$id_municipio->type      = 'int';
$id_municipio->len       = 6;
$id_municipio->fieldname = 'id_municipio';
$id_municipio->label     = 'Id municipio';
$id_municipio->editable  = true ;
$id_municipio->hide  = true;
//$id_municipio->readonly  = true;
$tabla->addCol($id_municipio);

$localidad_lat = new Field();
$localidad_lat->type      = 'varchar';
$localidad_lat->len       = 12;
$localidad_lat->fieldname = 'localidad_lat';
$localidad_lat->label     = 'Lat';
$localidad_lat->editable  = true ;
$localidad_lat->sortable  = true;
//$tabla->addCol($localidad_lat);

$localidad_lon = new Field();
$localidad_lon->type      = 'varchar';
$localidad_lon->len       = 12;
$localidad_lon->fieldname = 'localidad_lon';
$localidad_lon->label     = 'Lon';
$localidad_lon->editable  = true ;
$localidad_lon->sortable  = true;
//$tabla->addCol($localidad_lon);

$municipio_code = new Field();
$municipio_code->type      = 'varchar';
$municipio_code->len       = 6;
$municipio_code->fieldname = 'municipio_code';
$municipio_code->label     = 'Municipio';
$municipio_code->editable  = true ;
$municipio_code->hide  = true;
//$tabla->addCol($municipio_code);

$tabla->name = 'CFG_LOCALIDAD';
$tabla->title = 'Localidades';
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['locations']['options'],'num_rows',20);
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;
$tabla->classname = 'column right';

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = Administrador();

$tabla->orderby = 'localidad_name';
$tabla->setParent('id_municipio',$parent);

class CFG_LOCALIDADEvents extends defaultTableEvents implements iEvents{
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
  
  function OnInsert($owner,&$result,&$post) {  
     $post['id_provincia'] = $owner->getFieldValue( "SELECT id_provincia FROM CFG_MUNICIPIO WHERE municipio_id='{$post['id_municipio']}'" );
  }

  function OnUpdate($owner,&$result,&$post) {   
     $post['id_provincia'] = $owner->getFieldValue( "SELECT id_provincia FROM CFG_MUNICIPIO WHERE municipio_id='{$post['id_municipio']}'" );
  }

}
$tabla->events = New CFG_LOCALIDADEvents();