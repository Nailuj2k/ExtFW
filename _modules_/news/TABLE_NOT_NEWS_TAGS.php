<?php 

$tabla = new TableMysql(TB_TABLE.'_TAGS');

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = TB_NAME.'_TAG_ID';
$id->label     = 'Id';   
//$id->hide      = true;

$id_parent = new Field();
$id_parent->type      = 'int';
$id_parent->width     = 10;
$id_parent->fieldname = TB_NAME.'_ID';
$id_parent->label     = 'Item';   
$id_parent->editable  = true;
//$id_parent->hide      = true;

$id_tags = new Field();
$id_tags->type      = 'int'; //'select';
$id_tags->fieldname = 'TAG_ID';
$id_tags->label     = 'Etiqueta';   
$id_tags->width     = 100;
//$id_tags->source    = TB_NAME.'_tags';
//$id_tags->source_all= TB_NAME.'_tags_all';
$id_tags->editable  = true;

$tabla->title = 'Etiquetas';
$tabla->verbose=false;
$tabla->cache = false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 5;

$tabla->orderby = TB_NAME.'_ID'; 
// $tabla->setParent(TB_NAME.'_ID',$parent);

//$tabla->pk = 'taks_tags_id';
$tabla->addCol($id);
$tabla->addCol($id_parent);
$tabla->addCol($id_tags);
$tabla->addWhoColumns();

//$tabla->debug( ' parent: '.     );

//if(IsNumeric($parent)){
// if table is detail (parent_key is set) must have a parent id value before add

$tabla->perms['view']   = ( $_ACL->hasPermission(TB_NAME.'_view')   || Administrador());
$tabla->perms['delete'] = ( $_ACL->hasPermission(TB_NAME.'_delete') || Administrador() );
$tabla->perms['edit']   = ( $_ACL->hasPermission(TB_NAME.'_edit')   || Administrador() );
$tabla->perms['add']    = ( $_ACL->hasPermission(TB_NAME.'_create') || Administrador() );
$tabla->perms['reload'] = ( $_ACL->hasPermission(TB_NAME.'_reload') || Administrador() );  
$tabla->perms['setup']  = Root();  
$tabla->perms['filter'] = ( $_ACL->hasPermission(TB_NAME.'_filter') || Administrador() );




class tagsEvents extends defaultTableEvents implements iEvents{ 
  function OnInsert($owner,&$result,&$post) { }
  function OnUpdate($owner,&$result,&$post) { }
  function OnDelete($owner,&$result,$id)    { } 
  function OnDrawRow($owner,&$row,&$class){
    $sql = 'SELECT COLOR FROM '.TB_PREFIX.'_TAGS WHERE TAG_ID  = '.$row['TAG_ID'];  //$owner->pk->fieldname
    $color = $owner->getFieldvalue($sql); 
    //$row['TAG_ID'] =  '<span style="display:block;background-color:'.$color.'">' . $row['TAG_ID'].'</span>';  //+++
    //$row['TAG_ID'] = $row['TAG_ID'].' '.$color; //'<span style="display:block;background-color:'.$color.'">' . $row['TAG_ID'].'</span>';  //+++
    //NEW
    $class='';
    $sql = 'SELECT CLOSED FROM '.TB_TABLE.' WHERE '.TB_NAME.'_ID = '.$owner->parent_value;
    $v = $owner->getFieldValue($sql);
    $owner->perms['add']    =( $v!='1');
    $owner->perms['edit']   =( $v!='1');
    $owner->perms['delete'] =( $v!='1');
    if ($owner->perms['edit']===true) $class = ' edit'; 
    
  }
  function OnDrawCell($owner,&$row,&$col,&$cell){

    if ($col->fieldname=='TAG_ID') {
//      $row['TAG_ID'] = $col->values[$row['TAG_ID']];
//      $col->type='color';
    }

  }
}

$tabla->events = New tagsEvents();
