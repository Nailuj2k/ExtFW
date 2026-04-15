<?php 

$tabla = new TableMysql('CLI_TAGS');
$tabla->uploaddir = './media/tag/images';


$tabla->perms['edit'] = (Administrador() || $_ACL->hasPermission('tienda_edit')); 

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