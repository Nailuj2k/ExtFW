<?php 
/* Auto created */

$tabla = new TableMysql('CFG_PAIS');

$pais_id = new Field();
$pais_id->type      = 'int';
$pais_id->len       = 3;
$pais_id->fieldname = 'pais_id';
$pais_id->label     = 'Pais';
$pais_id->editable  = true ;
//$pais_id->hide  = true;
$tabla->addCol($pais_id);

$pais_name = new Field();
$pais_name->type      = 'varchar';
$pais_name->len       = 100;
$pais_name->width     = 300;
$pais_name->fieldname = 'pais_name';
$pais_name->label     = 'Pais name';
$pais_name->editable  = true ;
$pais_name->sortable  = true;
$pais_name->filtrable  = true;
$pais_name->searchable  = true;
$tabla->addCol($pais_name);

$tabla->title = 'Paises';
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['locations']['options'],'num_rows',20);
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;
$tabla->classname = 'column left';

$tabla->addActiveCol();
$tabla->colByName('ACTIVE')->filtrable = true;

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = Administrador();

if((CFG::$vars['users']['field']['country']&&CFG::$vars['users']['field']['state'])||!CFG::$vars['users']['field']['county']){ 
    $tabla->detail_tables=array('CFG_PROVINCIA');
}

$tabla->filter='ACTIVE=1 AND pais_id=724';  // ACTIVE=T

class CFG_PAISEvents extends defaultTableEvents implements iEvents{
    function OnShow($owner){
        $owner->paginator->begin_end_links = false;
        $owner->paginator->prev_next_links = true;
        $owner->paginator->page_links = false;
        $owner->paginator->aux_links  = false; //(!$this->paginator_simple);
        $owner->paginator->label_page = false; //(!$this->paginator_simple);z
        $owner->paginator->label_item = false; //(!$this->paginator_simple);
        $owner->paginator->labels['add'] = '<i class="fa fa-plus fa-white"></i>';
        $owner->show_inputsearch = false;
    }

}

$tabla->events = New CFG_PAISEvents();

