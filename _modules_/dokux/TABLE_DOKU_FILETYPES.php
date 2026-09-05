<?php 

$tabla = new TableMysql('DOKU_FILETYPES');

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->len       = 40;
$name->type      = 'varchar';
$name->editable  = true;

$regexp = new Field();
$regexp->fieldname = 'EXP';
$regexp->label     = 'RegExp';   
$regexp->len       = 100;
$regexp->type      = 'varchar';
$regexp->editable  = true;

$regexp_ed = new Field();
$regexp_ed->fieldname = 'EXP_IDENTIFIER';
$regexp_ed->label     = 'RegExp ID';   
$regexp_ed->len       = 100;
$regexp_ed->type      = 'varchar';
$regexp_ed->editable  = true;

$order = new Field();
$order->type      = 'int';
$order->fieldname = 'PRIORITY';
$order->label     = 'Prioridad';   
$order->len       = 3;
$order->editable = true;
$order->default_value = 10;

$servicio= new Field();
$servicio->type      = 'int';
$servicio->len       = 5;
$servicio->fieldname = 'SERVICIO';
$servicio->label     = 'Área / Servicio';
$servicio->type      = 'select';

//$servicio->values=array();
//$servicio->values['0']= 'Todos';
//foreach ($myareas as $area)  $servicio->values[$area['ID']]  = $area['NAME'];
/*
function _ta($rows){
    $result=[];
    foreach($rows as $row) { 
        $result[$row['ID']] = $row['NAME'];
    }
    return $result;
} 
*/
//foreach ($myareas as $area)  $servicio->values[$area['ID']]  = $area['NAME'];
$servicio->values    =  AreasACL::getAreas($_SESSION['userid']); //$tabla->toarray('doku_areas',   "SELECT ID, NAME FROM DOKU_AREAS WHERE ACTIVE=1",true);
$servicio->values_all = AreasACL::getAreas();  

$servicio->editable  =  $doku_admin; 
//$servicio->default_value=$_SESSION['DOKU_AREA']['ID']?$_SESSION['DOKU_AREA']['ID']:'0';
$servicio->allowNull=true;
$servicio->clearValue='0';
$servicio->clearText='Todos';
$servicio->width = 100;
$servicio->filtrable = true;  
$servicio->sortable = true;  
$servicio->searchable = true;
$servicio->inline_edit  = true;

$tabla->title = 'Área: '.$_SESSION['DOKU_AREA']['NAME'];
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->cache = false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 20;

$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($servicio);
$tabla->addCol($regexp);
$tabla->addCol($regexp_ed);
$tabla->addCol($order);

$tabla->addActiveCol();
$tabla->addWhoColumns();

$tabla->orderby = 'PRIORITY,SERVICIO,NAME';
//if($_SESSION['DOKU_AREA']['ID']) $tabla->where = "SERVICIO={$_SESSION['DOKU_AREA']['ID']} OR SERVICIO='0'";  //FIX: check exists
$tabla->where = "(SERVICIO={$_SESSION['DOKU_AREA']['ID']} OR SERVICIO='0')";  //FIX: check exists

$tabla->perms['view']   =  $doku_admin;
$tabla->perms['filter'] =  $doku_admin;
$tabla->perms['delete'] =  $doku_delete;
$tabla->perms['edit']   =  $doku_admin;
$tabla->perms['add']    =  $doku_admin;
$tabla->perms['reload'] =  $doku_admin;  
$tabla->perms['setup']  =  $doku_admin;

class agenciasEvents extends defaultTableEvents implements iEvents{ 

  function OnAfterCreate($owner){ 
    /*if($owner->recordCount()<1){
      $owner->sql_query("INSERT INTO {$owner->tablename} (NAME) VALUES('Petición TAC')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (NAME) VALUES('Petición RM')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (NAME) VALUES('Petición endoscopia')");
    }*/
  } 
  
  function OnInsert($owner,&$result,&$post) { 
  }
  
  function OnUpdate($owner,&$result,&$post) { 
  }

}

$tabla->events = New agenciasEvents();

