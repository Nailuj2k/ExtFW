<?php

$tabla = new TableMysql('CLI_TAGS');
$tabla->uploaddir = './media/tag/images';


//$tabla->perms['edit']   = (Administrador() ); 
/*
$tabla->perms['edit']   = $_SESSION['PAGE_FILES_ID_PARENT'] || Administrador(); 
$tabla->perms['view']   = $_SESSION['PAGE_FILES_ID_PARENT'] || Administrador(); 
$tabla->perms['delete'] = $_SESSION['PAGE_FILES_ID_PARENT'] || Administrador(); 
$tabla->perms['add']    = $_SESSION['PAGE_FILES_ID_PARENT'] || Administrador(); 
$tabla->perms['reload'] = $_SESSION['PAGE_FILES_ID_PARENT'] || Administrador(); 
*/
$tabla->perms =$_SESSION['page_files_perms'];

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_TAGS.php');

/***
  // store.gilfamily OnInsert 

  function OnInsert($owner,&$result,&$post) { 
      $result['the_filename']=$post['NAME'].'.png';
      $post['NAME'] = trim(Str::sanitizeName($post['CAPTION']));
      if(!$post['NAME']) {
          $result['error'] = 1;
          $result['msg'] = 'Falta el identificador';
      }else{   
          $r = $owner->recordCount( 'SELECT ID FROM CLI_TAGS WHERE NAME = \''.$post['NAME'].'\'');
          if($r >0) {
              $result['error'] =5;
              $result['msg'] = 'Ya existe una categoría con el identificador '.$post['NAME'];
          }else{
          }
      }
      $result['the_header_filename']='header_'.str_replace(' ','_',strtolower($post['NAME'])).'.jpg';

  }
**/
$tabla->page_num_items=5;
$tabla->show_empty_rows = false;
$tabla->colByName('HEADER_FILE_NAME')->hide = true;
$tabla->colByName('FILE_NAME')->hide = true;
$tabla->colByName('HEADER_FILE_NAME')->editable = false;
$tabla->colByName('FILE_NAME')->editable = false;

$tabla->showtitle=true;
$tabla->show_inputsearch = false;

if(isset($_SESSION['PAGE_FILES_ID_PARENT'])){
    //$tabla->parent = true;
    $tabla->setParent('ID_PARENT',$_SESSION['PAGE_FILES_ID_PARENT']);
}

