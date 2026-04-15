<?php

$tabla = new TableMysql('CFG_TPL');

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
$name->searchable  = true;
$name->readonly  = (!Root());
$name->editable  = Root();

$description = new Field();
$description->type       = 'textarea';
$description->len        = 200;
$description->fieldname  = 'DESCRIPTION';
$description->label      = 'Descripción';   
$description->editable   = true;
$description->searchable = true;
$description->width      = 500;
$description->classname  = 'fullname';
$description->wysiwyg=false;

//if (CFG::$vars['site']['langs']['enabled']===true){
    $query =  $tabla->sql_query('select lang_id,lang_cc,lang_name from '.TB_LANG.' where lang_active=1 and lang_id>1');
    $langs=array();
    foreach($query as $row){
    //  $result[] = $row; 
       $langs[$row['lang_cc']]=$row['lang_name'];
    }
//}else{
//    $langs=false;
//}

$text = new Field();
$text->type      = 'textarea';
$text->fieldname = 'TEXT';
$text->label     = 'Texto';   
$text->editable = Administrador();
$text->hide     = true;
//$text->langs    = array(5=>'en'); //,3=>'de',37=>'ca');
$text->searchable = true;
//$description->filtrable = false;  
$text->wysiwyg = true;  
$text->translatable = true;  
$text->fieldset  = 'text';
//$text->height=400;
if (CFG::$vars['site']['langs']['enabled']===true) if(count($langs)>0) $text->langs =  $langs;

//FIX foreach row if (!TEXT_en) TEXT_en = translation_en(TEXT)

$code   = new Field();
$code->type      = 'bool';
$code->width     = 30;
$code->fieldname = 'CODE';
$code->label     = 'Código html';   
$code->editable  = Administrador();
$code->filtrable = true;
//$code->default_value = true;

$translatable   = new Field();
$translatable->type      = 'bool';
$translatable->width     = 30;
$translatable->fieldname = 'TRANSLATABLE';
$translatable->label     = 'Traducible';   
$translatable->editable  = Administrador();
$translatable->filtrable = true;
//$translatable->default_value = true;

$tabla->title = 'Plantillas';
$tabla->showtitle = false;
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['templates']['options'],'num_rows',20);

$tabla->addCol($id);
$tabla->addCol($name);
$tabla->addCol($description);
$tabla->addCol($text);
$tabla->addCol($code);
$tabla->addCol($translatable);
$tabla->addActiveCol();
$tabla->addWhoColumns();

$tabla->perms['view']   = Administrador();
$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();  

class CFG_TPL_Events extends defaultTableEvents implements iEvents{
  function OnBeforeShow($owner) {  } 
  function OnCalculate($owner,&$row){  }
  function OnDrawRow($owner,&$row,&$class){  }
  function OnInsert($owner,&$result,&$post) { 
  
      //$row['CODE']=encrypt($row['CODE'])
      // add field 'ENCRYPT' ??
  
  }
  function OnUpdate($owner,&$result,&$post) {              // TinyMCE and others wysiwyg editors path Fix
      $post['TEXT'] = str_replace('https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR,'[SITE_URL]',$post['TEXT']);
  }

  function OnPostCol($owner,&$result,&$col,&$value){
      //if($col->fieldname=='TEXT'){
      //    $value = str_replace(['../..','https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR],'[SITE_URL]',$value);  
      //}
  }

  function OnDelete($owner,&$result,$id)    {   }
  function OnBeforeShowForm($owner,&$form,$id) {
  }
  function OnBeforeInsert($owner) { }
  function OnDrawCell($owner,&$row,&$col,&$cell){ 

      if($col->fieldname=='TEXT'){
          $row['TEXT'] =  str_replace('[SITE_URL]','https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR,$row['TEXT']);
      }
  }

  function OnBeforeUpdate($owner,$id) {
     /*
     $row = $owner->getRow($id);
     $owner->colByName('TEXT')->wysiwyg = $row['CODE']!='1';     
     $owner->colByName('TEXT_en')->wysiwyg = $row['CODE']!='1';     
     $owner->colByName('TEXT')->translatable = $row['TRANSLATABLE']=='1'; 
     $owner->colByName('TEXT_en')->translatable = $row['TRANSLATABLE']=='1'; 
     if($row['TRANSLATABLE']!='1') $owner->colByName('TEXT')->html_before = '';
     */      
     global $langs;
     $row = $owner->getRow($id);
     //$owner->colByName('item_text')->wysiwyg = $row['HTML']!='1';     
     //$owner->colByName('item_text_en')->wysiwyg = $row['HTML']!='1';     
     if($row['CODE']=='1') {
         $owner->colByName('TEXT')->wysiwyg = false;
         if (CFG::$vars['site']['langs']['enabled']===true && count($langs)>0) 
         foreach ($langs as $k=>$v){
             $owner->colByName('TEXT_'.$k)->wysiwyg = false;
         }       
     }
     if (CFG::$vars['site']['langs']['enabled']===true){
         $owner->colByName('TEXT')->translatable = $row['TRANSLATABLE']=='1'; 
         if (CFG::$vars['site']['langs']['enabled']===true && count($langs)>0) 
         foreach ($langs as $k=>$v){
            if($k!=='es') $owner->colByName('TEXT_'.$k)->translatable = $row['TRANSLATABLE']=='1'; 
         }
         if($row['TRANSLATABLE']!='1') $owner->colByName('TEXT')->html_before = '';
     }

  }

}
$tabla->events = New CFG_TPL_Events();

