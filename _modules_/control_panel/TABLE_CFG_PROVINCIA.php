<?php 
/* Auto created */


$provincia_id = new Field();
$provincia_id->type      = 'int';
$provincia_id->len       = 2;
$provincia_id->fieldname = 'provincia_id';
$provincia_id->label     = 'Id';
//$provincia_id->editable  = true ;
//$provincia_id->hide  = true;

$id_pais = new Field();
$id_pais->type      = 'int';
$id_pais->len       = 2;
$id_pais->fieldname = 'id_pais';
$id_pais->label     = 'Id pais';
$id_pais->editable  = true ;
$id_pais->hide  = true ;
$id_pais->default_value=$_SESSION['_CACHE']['CFG_PROVINCIA']['filterindex'];

$provincia_name = new Field();
$provincia_name->type      = 'varchar';
$provincia_name->len       = 150;
$provincia_name->fieldname = 'provincia_name';
$provincia_name->label     = 'Provincia';
$provincia_name->editable  = true ;
$provincia_name->sortable  = true;
$provincia_name->searchable  = true;

$tabla = new myTableMysql('CFG_PROVINCIA');

$tabla->addCol($provincia_id);
$tabla->addCol($id_pais);
$tabla->addCol($provincia_name);

$tabla->name = 'CFG_PROVINCIA';
$tabla->title = 'Estados / Provincias / ...';
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['locations']['options'],'num_rows',20);
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;
//$tabla->classname = 'column left';

$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Root();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;

$tabla->orderby = 'provincia_name';
if(!CFG::$vars['users']['field']['county']){
       $tabla->classname = 'column center'; 
       $tabla->setParent('id_pais',$parent);
}else if(CFG::$vars['users']['field']['country']&&CFG::$vars['users']['field']['state']){ 
   $tabla->setParent('id_pais',$parent);
   if(CFG::$vars['users']['field']['city']){
       $tabla->classname = 'column center'; 
       $tabla->setParent('id_pais',$parent);
   }else{ 
       $tabla->classname = 'column right';
   }
}else{
   $tabla->setParent('id_pais',$_SESSION['_CACHE']['CFG_PROVINCIA']['filterindex']);
   $tabla->classname = 'column left';
}

if(CFG::$vars['users']['field']['city']||!CFG::$vars['users']['field']['county']){ 
    $tabla->detail_tables=array('CFG_MUNICIPIO');
}

class myTableMysql extends TableMysql{

  public function customfilter($params){
    $filter = $params['filter']=='ALL' ? 0 : $params['filter'];
    $page = $params['page'];
    if    ($filter) {
      $_SESSION['_CACHE']['CFG_PROVINCIA']['filterstring'] = ' id_pais = '.$filter;  
      $_SESSION['_CACHE']['CFG_PROVINCIA']['filterindex'] =  $filter;  
    }else{
      $_SESSION['_CACHE']['CFG_PROVINCIA']['filterstring'] = false;
      $_SESSION['_CACHE']['CFG_PROVINCIA']['filterindex'] = false;
    }   
    $result = array();
    $result['error']=0;
    $result['msg'] = 'Filtro: '.$flt.', Page: '.$page;
    echo json_encode($result);
  }

}

class CFG_PROVINCIAEvents extends defaultTableEvents implements iEvents{
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
$tabla->events = New CFG_PROVINCIAEvents();


