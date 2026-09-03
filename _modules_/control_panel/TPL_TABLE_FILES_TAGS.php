<?php 

// $tabla = new TableMysql('RRHH_FILES_TAGS');

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$id_parent = new Field();
$id_parent->type      = 'int';
$id_parent->width     = 10;
$id_parent->fieldname = 'FILE_ID';
$id_parent->label     = 'Item';   
//$id_parent->hide      = true;
$id_parent->editable  = true;

$id_tag = new Field();
$id_tag->fieldname = 'TAG_ID';
$id_tag->label     = 'Etiqueta';   
$id_tag->width     = 100;
$id_tag->type      = 'int'; //'select';
//$id_tags->source    = TB_NAME.'_tags';
//$id_tags->source_all= TB_NAME.'_tags_all';
$id_tag->editable  = true;

$tabla->title = 'Etiquetas';
$tabla->verbose=false;
$tabla->cache = false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 5;

$tabla->orderby = 'FILE_ID'; 
//$tabla->setParent('FILE_ID',$parent);

//$tabla->pk = 'taks_tags_id';
$tabla->addCol($id);
$tabla->addCol($id_parent);
$tabla->addCol($id_tag);
$tabla->addWhoColumns();

//$tabla->debug( ' parent: '.     );

//if(IsNumeric($parent)){
// if table is detail (parent_key is set) must have a parent id value before add
/*
$tabla->perms['view']   = $tabla->perms['view']   ? $tabla->perms['view']   : Administrador();
$tabla->perms['filter'] = $tabla->perms['filter'] ? $tabla->perms['filter'] : Administrador();
$tabla->perms['add']    = $tabla->perms['add']    ? $tabla->perms['add']    : Administrador(); 
$tabla->perms['delete'] = $tabla->perms['delete'] ? $tabla->perms['delete'] : Administrador(); 
$tabla->perms['edit']   = $tabla->perms['edit']   ? $tabla->perms['edit']   : Administrador(); 
$tabla->perms['reload'] = $tabla->perms['reload'] ? $tabla->perms['reload'] : Administrador();  
$tabla->perms['setup']  = $tabla->perms['setup']  ? $tabla->perms['setup']  : Root();  
*/
$tabla->perms['view']   = $tabla->perms['view'] || Administrador();
$tabla->perms['filter'] = Administrador();
$tabla->perms['add']    = $tabla->perms['add'] || Administrador(); 
$tabla->perms['delete'] = $tabla->perms['delete'] || Administrador(); 
$tabla->perms['edit']   = $tabla->perms['edit'] || Administrador(); //$_ACL->hasPermission('tienda_edit'); //
$tabla->perms['reload'] = $tabla->perms['reload'] || Administrador();  
$tabla->perms['setup']  = Root();  


class tagsEvents extends defaultTableEvents implements iEvents{ 
  function OnInsert($owner,&$result,&$post) { }
  function OnUpdate($owner,&$result,&$post) { }
  function OnDelete($owner,&$result,$id)    { } 
  function OnDrawRow($owner,&$row,&$class){
    if($owner->table_tags){
        $sql = 'SELECT COLOR FROM '.$owner->table_tags.' WHERE ID  = '.$row['ID'];  //$owner->pk->fieldname
        $color = $owner->getFieldvalue($sql); 
    }
    //$row['ID'] =  '<span style="background-color:'.$color.'">' . $row['ID'].'</span>';  //+++
    //$row['TAG_ID'] = $row['TAG_ID'].' '.$color; //'<span style="display:block;background-color:'.$color.'">' . $row['TAG_ID'].'</span>';  //+++
    //NEW
    /*
    $class='';
    $sql = 'SELECT CLOSED FROM '.TB_TABLE.' WHERE '.TB_NAME.'_ID = '.$owner->parent_value;
    $v = $owner->getFieldValue($sql);
    $owner->perms['add']    =( $v!='1');
    $owner->perms['edit']   =( $v!='1');
    $owner->perms['delete'] =( $v!='1');
    if ($owner->perms['edit']===true) $class = ' edit'; 
    */
  }
  function OnDrawCell($owner,&$row,&$col,&$cell){

    if ($col->fieldname=='ID') {
//      $row['TAG_ID'] = $col->values[$row['TAG_ID']];
//      $col->type='color';
    }

  }
}

$tabla->events = New tagsEvents();

