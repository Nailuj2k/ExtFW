<?php 
/* Auto created */

$tabla = new TableMysql(TB_STR);

$str_id = new Field();
$str_id->type      = 'int';
$str_id->len       = 5;
$str_id->fieldname = 'str_id';
$str_id->label     = 'Str';
//$str_id->editable  = true ;
$str_id->sortable  = true;
$tabla->addCol($str_id);

$str_string = new Field();
$str_string->type      = 'varchar';
//$str_string->type      = 'textarea';
$str_string->len       = 200;
$str_string->fieldname = 'str_string';
$str_string->label     = 'Str string';
$str_string->editable  = true ;
$str_string->sortable  = true;
$str_string->classname='fullname';
$str_string->width    = 350;
$str_string->wysiwyg  = false;
$str_string->searchable  = true;
$tabla->addCol($str_string);

$str_ok = new Field();
$str_ok->type      = 'bool';
$str_ok->len       = 1; //'0','1';
$str_ok->fieldname = 'str_ok';
$str_ok->label     = 'ok';
$str_ok->editable  = true ;
$str_ok->sortable  = true;
$tabla->addCol($str_ok);

$tabla->classname = 'column left';
$tabla->name = TB_STR;
$tabla->title = 'Textos base';
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 20;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;
$tabla->on_delete_cascade =true;
$tabla->detail_tables=array(TB_CC);  // Set as master
$tabla->detail_tables_keys[TB_CC]='id_str';

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = Administrador();

class str_Events extends defaultTableEvents implements iEvents{ 

  function OnShow($owner){
    //$owner->paginator->begin_end_links = false;
    //$owner->paginator->prev_next_links = false;
    $owner->paginator->page_links = true;
    $owner->paginator->aux_links  = false; //(!$this->paginator_simple);
    $owner->paginator->label_page = false; //(!$this->paginator_simple);
    $owner->paginator->label_item = false; //(!$this->paginator_simple);
    $owner->paginator->labels['add'] = '<i class="fa fa-plus fa-inverse"></i>';
    //$owner->show_inputsearch = false;
  }

}

$tabla->events = New str_Events();

