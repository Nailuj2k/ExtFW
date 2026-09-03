<?php 

$tabla = new TableMysql(TB_CC);

$cc_id            = new Field();
$cc_id->type      = 'int';
$cc_id->len       = 5;
$cc_id->width     = 85;
$cc_id->fieldname = 'cc_id';
$cc_id->label     = 'Cc';   
//$cc_id->hide      = true;
$tabla->addCol($cc_id);

$id_str            = new Field();
$id_str->type      = 'int';
$id_str->len       = 5;
$id_str->fieldname = 'id_str';
$id_str->label     = 'Id str';
//$area->editable  = true;
$id_str->value     = $parent;
$id_str->hide      = true;
$id_str->width     = 25;
$tabla->addCol($id_str);

$id_lang            = new Field();
$id_lang->type      = 'select';
$id_lang->len       = 5;
$id_lang->fieldname = 'id_lang';
$id_lang->label     = 'Idioma';   
$id_lang->values    = $_ARGS['op']=='add'
                    ? $tabla->toarray('langs',      TB_LANG,  'lang_id', 'lang_name'  ," WHERE lang_active='1' AND lang_id NOT IN (SELECT id_lang FROM ".TB_CC." where id_str={$parent})",true)
                    : $tabla->toarray('langs',      TB_LANG,  'lang_id', 'lang_name'  ," WHERE lang_active='1'",true);
$id_lang->values_all= $tabla->toarray('langs_all',  TB_LANG,  'lang_id', 'lang_name'  ,'',true);   
$id_lang->editable  = true;
$tabla->addCol($id_lang);

$cc_string = new Field();
$cc_string->type      = 'textarea';
$cc_string->fieldname = 'cc_string';
$cc_string->label     = 'Cc string';
$cc_string->width  = 500 ;
$cc_string->editable  = true ;
$cc_string->sortable  = true;
$cc_string->wysiwyg  = false;
$tabla->addCol($cc_string);


$cc_ok = new Field();
$cc_ok->type      = 'bool';
$cc_ok->len       = 1; //'0','1';
$cc_ok->fieldname = 'cc_ok';
$cc_ok->label     = 'ok';
$cc_ok->editable  = true ;
$cc_ok->sortable  = true;
$cc_ok->default_value  = '0';
$tabla->addCol($cc_ok);

$tabla->classname = 'column right';
$tabla->name = TB_CC;
$tabla->title = 'Traducciones';
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 20;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =false;
$tabla->setParent('id_str',$parent);   // Set as detail

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root(); //Administrador();
$tabla->perms['add']    = Administrador(); //Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = Administrador();

class cc_Events extends defaultTableEvents implements iEvents{
  function OnShow($owner){
    $owner->paginator->begin_end_links = false;
    $owner->paginator->prev_next_links = false;
    $owner->paginator->page_links = true;
    $owner->paginator->aux_links  = false; //(!$this->paginator_simple);
    $owner->paginator->label_page = false; //(!$this->paginator_simple);
    $owner->paginator->label_item = false; //(!$this->paginator_simple);
    $owner->paginator->labels['add'] = '<i class="fa fa-plus fa-inverse"></i>';
    $owner->show_inputsearch = false;
  }

  function OnDrawCell($owner,&$row,&$col,&$cell){
    if ($col->fieldname=='cc_ok') 
      $col->editable = (( $row['cc_ok']!==1 ));
  }


  function OnDrawRow($owner,&$row,&$class){
       if ( $row['cc_ok']==0)  $class = ' edit'; else $class='';
  }

}

$tabla->events = New cc_Events();