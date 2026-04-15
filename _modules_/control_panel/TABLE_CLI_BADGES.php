<?php 

$tabla = new TableMysql('CLI_BADGES');

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width       = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->type      = 'varchar';
$name->len       = 100;
$name->editable  = true;

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = 'Logo';   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = './media/badges/images';
$filename->accepted_doc_extensions = array('.png'); //,'.webp');
$filename->searchable = true;
$filename->textafter = 'Formato PNG con fondo transparente.';
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';

$default_text = new Field();
$default_text->type       = 'textarea';
$default_text->fieldname  = 'DEFAULT_TEXT';
$default_text->label      = 'Descripción';   
$default_text->editable   = Administrador();
$default_text->hide       = true;
$default_text->searchable = true;
$default_text->filtrable  = false;  
$default_text->wysiwyg  = false;  

$order            = new Field();
$order->type      = 'int';
$order->width       = 15;
$order->fieldname = 'ID_ORDER';
$order->label     = 'Orden';
$order->len       = 8;   
$order->editable  = true;

//$tabla->title = 'Badges'; // ' '.$tabla->nextInsertId().' :: '.$tabla::nextInsertId($tabla->tablename);
$tabla->showtitle = true;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 20;

$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($filename);
$tabla->addCol($default_text);
$tabla->addCol($order);
$tabla->addActiveCol();
$tabla->addWhoColumns();


$tabla->perms['view']   = Administrador();
$tabla->perms['filter'] = Administrador();
$tabla->perms['add']    = Administrador(); 
$tabla->perms['delete'] = Administrador(); 
$tabla->perms['edit']   = Administrador(); 
$tabla->perms['reload'] = Administrador();  
$tabla->perms['setup']  = Root();  


//$tabla->lists = array('categoria');

class badgesEvents extends defaultTableEvents implements iEvents{ 
  
    function OnDrawRow($owner,&$row,&$class){
        $filename = './media/badges/images/'.$row['FILE_NAME'];
        if($row['FILE_NAME'] && file_exists($filename)){
          $row['FILE_NAME']  =  "<a class=\"{$ext} open_file_image framed\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME']['URL']}\">"
                                ."<img style=\"height:22px;\" src=\"{$row['IMAGES']['FILE_NAME']['THUMB']}\"    ></a>";
        }else{
          $row['FILE_NAME']='<i class="fa fa-image"></i>';
        }

    }

  function OnDelete($owner,&$result,$id)    { 
    /*
    $childs = $owner->recordCount( 'SELECT ID FROM CLI_PRODUCTS_TAGS WHERE PRODUCT_ID = '.$id);
    if($childs >0) {
      $result['error'] =5;
      $result['msg'] = 'Esta etiqueta no puede eliminarse porque aparece en '.$childs.' documentos';
    }else{
    }
    */
  }

  function OnInsert($owner,&$result,&$post) { 

      $result['the_filename']=$owner->nextInsertId().'.png';

      //$post['NAME'] = trim(Str::sanitizeName($post['CAPTION']));
      if(!$post['NAME']) {
          $result['error'] = 1;
          $result['msg'] = 'Falta el identificador';
      }else{   
          $r = $owner->recordCount( 'SELECT ID FROM CLI_BADGES WHERE NAME = \''.$post['NAME'].'\'');
          if($r >0) {
              $result['error'] =5;
              $result['msg'] = 'Ya existe una fila con el identificador '.$post['NAME'];
          }else{
          }
      }

  }
  
  function OnUpdate($owner,&$result,&$post) { 

      $result['the_filename']=$post['ID'].'.png';
  
      if($post['fake_input_FILE_NAME']==''){
           unlink('./media/badges/images/'.$result['the_filename']);
           unlink('./media/badges/images/'.TN_PREFIX.$result['the_filename']);
      } 
      
      //if(!$post['NAME']) $post['NAME'] = trim(Str::sanitizeName($post['CAPTION']));
      
      if(!$post['NAME']) {
          $result['error'] = 1;
          $result['msg'] = 'Falta el identificador';
      }else{   
          $r = $owner->recordCount( 'SELECT ID FROM CLI_BADGES WHERE NAME = \''.$post['NAME'].'\' AND ID <> '.$post['ID']);
          if($r >0) {
              $result['error'] =5;
              $result['msg'] = 'Ya existe una fila con el identificador '.$post['NAME'];
          }else{
          }
      }

      Table::sqlExec("UPDATE CFG_CFG SET V='".hash('crc32b',time())."' WHERE K='site.lastupdate'");

  }
  
  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
     if  ($col->fieldname=='FILE_NAME')    $result['local_file'] = $result['the_filename'];
  }
 
  function OnAfterUpdate($owner,&$result,&$post){
      if($post['fake_input_FILE_NAME']=='') $owner->sql_exec('UPDATE '.$owner->tablename." SET FILE_NAME='' WHERE ID={$post['ID']}");
  }
    
  function OnAfterCreate($owner){ 
    //if($owner->recordCount()<1){
      //$owner->sql_exec("INSERT INTO {$owner->tablename} (NAME) VALUES('badge_1')");
      //$owner->sql_exec("INSERT INTO {$owner->tablename} (NAME) VALUES('badge_2')");
    //}
  } 

}

$tabla->events = New badgesEvents();

