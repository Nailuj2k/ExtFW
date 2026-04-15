<?php

$tabla = new TableMysql( 'CFG_LINKS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

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
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  = true;
$name->searchable= true;

$link = new Field();
$link->fieldname = 'LINK';
$link->label     = 'Link';   
$link->len       = 400;
$link->type      = 'varchar';
$link->editable  = true;
$link->hide  = true;

$description = new Field();
$description->type      = 'varchar';
$description->fieldname = 'DESCRIPTION';
$description->label     = 'Descripción';   
$description->hide     = true;
$description->searchable = true;
$description->editable = true;  
$description->len = 200;  
if(count($tabla->langs)>0) {
    $description->translatable = true;  
    $description->langs =  $tabla->langs;
}


$logo = new Field();
$logo->fieldname = 'LOGO';
$logo->label     = 'Logo';   
$logo->len       = 100;
$logo->length    = 40;
$logo->type      = 'file';
$logo->editable  = true;//$_ACL->userHasRoleName('Administradores');
$logo->uploaddir = './media/links/logos';
$logo->accepted_doc_extensions = array('.png','.jpg','.gif','.webp');
$logo->textafter = 'PNG, fondo transparente, anchura mínima 200px,  centrado vertical.';
//$logo->hide = true;
$logo->action_if_exists_disabled = true;
$logo->action_if_exists = 'replace';

$fixed = new Field();
$fixed->fieldname = 'FIXED';
$fixed->label     = 'Fijo';   
$fixed->len       = 1;
$fixed->type      = 'bool';
$fixed->editable  = Administrador();

$clicks = new Field();
$clicks->fieldname = 'CLICKS';
$clicks->label     = 'Clicks';   
$clicks->len       = 9;
$clicks->type      = 'int';
$clicks->editable  = Administrador();

$tabla->title =  'Links';       // str_replace('CFG_', '', $tabla->tablename);
$tabla->showtitle = true;
$tabla->show_inputsearch = true;
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['links']['options'],'num_rows',10);
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($link);
$tabla->addCol($description);
$tabla->addCol($logo);
$tabla->addCol($clicks);
$tabla->addCol($fixed);

$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';
// $tabla->classname = 'column left';

$tabla->perms['view']   = Administrador() || $_ACL->hasPermission('links_view');      
$tabla->perms['reload'] = Administrador() || $_ACL->hasPermission('links_view');  
$tabla->perms['edit']   = Administrador() || $_ACL->hasPermission('links_edit');
$tabla->perms['add']    = Administrador() || $_ACL->hasPermission('links_add');
$tabla->perms['delete'] = Administrador() || $_ACL->hasPermission('links_delete');
$tabla->perms['setup']  = Administrador() || $_ACL->userHasRoleName('Administradores');  


class Links_Events extends defaultTableEvents implements iEvents{ 

    function OnDrawRow($owner,&$row,&$class){
        $filename = './media/links/logos/'.$row['LOGO'];
        if($row['LOGO'] && file_exists($filename)){
            $row['LOGO']  =  "<a class=\"{$ext} swipebox framed\" rel=\"gallery\" href=\"{$row['IMAGES']['LOGO']['URL']}\">"
                                  ."<img style=\"height:22px;\" src=\"{$row['IMAGES']['LOGO']['THUMB']}\"    ></a>";
        }else{
            $row['LOGO']='<i class="fa fa-image"></i>';
        }

        // $row['LOGO']  =  "<a class=\"{$ext} swipebox framed\" rel=\"gallery\" href=\"{$row['IMAGES']['LOGO']['URL']}\">"
        //                ."<img src=\"{$row['IMAGES']['LOGO']['THUMB']}\"    ></a>";
        //if($_SESSION['userid']==1){
           // $c = $owner->getFieldValue( "SELECT SUM(CLICKS) AS CLICKS FROM CFG_CLICKS WHERE  ID_ITEM ={$row['ID']}");
           // $owner->sql_query( "UPDATE CFG_LINKS SET CLICKS={$c} WHERE ID={$row['ID']}");
        //}
    }
  

  
    function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
       if  ($col->fieldname=='LOGO') {
           $filename = $result['old']['ID'] ? $result['old']['ID'] : $owner->nextInsertId();
           $result['local_file'] = $filename.'.'.Str::get_file_extension($result['local_file']);
        }
    }

}

$tabla->events = New Links_Events();

