<?php

$tabla = new TableMysql('CFG_FILES_PROVIDER');

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'PROVIDER_ID';
$id->label     = 'Id';   
//$id->hide      = true;

$provider_name = new Field();
$provider_name->fieldname = 'NAME';
$provider_name->label     = 'Nombre';   
$provider_name->len       = 100;
$provider_name->type      = 'varchar';
$provider_name->editable  = Administrador();

$provider_color = new Field();
$provider_color->fieldname = 'COLOR';
$provider_color->label     = 'Color';   
$provider_color->type      = 'color';
$provider_color->editable  = Administrador();

$provider_description = new Field();
$provider_description->fieldname = 'DESCRIPTION';
$provider_description->label     = 'Descripción';   
$provider_description->len       = 250;
$provider_description->type      = 'varchar';
$provider_description->editable  = Administrador();

$provider_visible = new Field();
$provider_visible->fieldname = 'VISIBLE';
$provider_visible->label     = 'Visible';   
$provider_visible->type      = 'bool';
$provider_visible->editable  = Administrador();
$provider_visible->default_value  = 1;

$order = new Field();
$order->type      = 'int';
$order->fieldname = 'TORDER';
$order->label     = 'Orden';   
$order->editable  = Administrador();   
$order->len       = 5;

$tabla->title = 'Tipos de archivo';
$tabla->verbose=false;
//$tabla->cache = false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 12;

$tabla->addCol($id);
$tabla->addCol($provider_name);
$tabla->addCol($provider_color);
$tabla->addCol($provider_description);
$tabla->addCol($provider_visible);
$tabla->addCol($order);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'TORDER'; //ACTIVE,

class statesEvents extends defaultTableEvents implements iEvents{ 
  function OnInsert($owner,&$result,&$post) { }
  function OnUpdate($owner,&$result,&$post) { }
  function OnDelete($owner,&$result,$id)    { 
    global $prefix;
    $childs = $owner->recordCount( 'CLI_PAGES_FILES','WHERE ID_PROVIDER = '.$id);
    if($childs >0) {
      $result['error'] =5;
      $result['msg'] = 'Esta estado no puede eliminarse porque aparece en '.$childs.' '.Inflect::pluralize('Fila',$childs);
    }else{
    }
  }

  function OnAfterCreate($owner){ 
    if($owner->recordCount()<1){
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 1,'Image',1,'#33b075')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 2,'Youtube',  2,'#d8dc50')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 3,'Vimeo',3,'#dc934b')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 4,'Document', 4,'#c44145')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 5,'PDF',5,'#9018c8')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 6,'Google Drive File', 6,'#566cc7')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 7,'Google Drive Folder', 7,'#566cc7')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 8,'Google Form', 8,'#566cc7')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES( 9,'Google Presentation', 9,'#566cc7')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES(10,'Epub', 10,'#566cc7')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (PROVIDER_ID,NAME,TORDER,COLOR) VALUES(11,'URL', 11,'#566cc7')");
    }
  } 
}

$tabla->events = New statesEvents();


$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();  