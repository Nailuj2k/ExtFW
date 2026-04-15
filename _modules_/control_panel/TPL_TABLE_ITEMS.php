<?php 

$id            = new Field();
$id->type      = 'int';
$id->width       = 15;
$id->len       = 10;
$id->fieldname = 'item_id';
$id->label     = 'Id';   
//$id->hide     = true;   
/*
$title = new Field();
$title->type      = 'varchar';
//$title->width       = 140;
$title->len       = 150;
$title->fieldname = 'item_title';
$title->label     = 'Título';   
$title->editable  = true ;
$title->sortable  = true;
*/
$caption = new Field();
$caption->type      = 'varchar';
$caption->width       = 150;
$caption->len       = 50;
$caption->fieldname = 'item_caption';
$caption->label     = t('LABEL','Etiqueta');   
$caption->editable  = true ;
$caption->sortable  = true;
//$caption->required  = true;
//$caption->inline_edit  = true;
if(count($tabla->langs)>0) {
    $caption->translatable = true;  
    $caption->langs =  $tabla->langs;
}


$name = new Field();
$name->type      = 'varchar';
//$name->width       = 80;
$name->len       = 150;
$name->fieldname = 'item_name';
$name->label     = t('NAME','Nombre');   
$name->editable  = true ;
$name->sortable  = true;
//$name->inline_edit  = true;
//$name->hide  = true;

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = t('IMAGE','Imagen');   
$filename->len       = 100;
$filename->width    = 25;
$filename->type      = 'file';
$filename->editable  = Administrador();
$filename->uploaddir = './media/items/images';
$filename->accepted_doc_extensions = array('.jpg');
$filename->textafter = 'Formato JPG, proporción apróximada 3x2, anchura mínima de 900 ó 1000px'; 
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';

$module = new Field();
$module->type      = 'select';
//$module->width       = 80;
$module->fieldname = 'id_module';
$module->label     = t('MODULE','Módulo');
$module->values    = array(1=>'Sistema',2=>'Módulo',3=>'Html',4=>'Url',5=>'--');   
$module->values_all= array(1=>'Sistema',2=>'Módulo',3=>'Html',4=>'Url',5=>'--');   
$module->editable  = true ;
$module->sortable  = true;
$module->default_value = 3;

$url = new Field();
$url->type      = 'varchar';
$url->len       = 150;
//$url->width     = 220;
$url->fieldname = 'item_url';
$url->label     = 'Url';   
$url->editable = true;      // consulta sql para sacar valores
$url->searchable = true;
$url->filtrable = false;  
$url->textafetr = '<span style="line-height: 33px;vertical-align: top;">URL amigable, sin espacios ni caracteres especiales.</span>';
//$url->inline_edit = true;  
if(count($tabla->langs)>0) {
    $url->translatable = true;  
    $url->langs =  $tabla->langs;
}

/*
$text = new Field();
$text->type      = 'textarea';
$text->fieldname = 'item_text';
$text->label     = 'Contenido HTML';   
$text->editable = true;
$text->searchable = true;
$text->filtrable = false;  
//$description->collapsed  = true;
$text->hide = true;
$text->fieldset = 'Contenido';
$text->wysiwyg = true;
*/
$level = new Field();
$level->fieldname = 'item_level';
$level->label     = 'Level';   
$level->type      = 'int';
$level->len       = 8;
$level->width     = 40;
$level->editable  = true;
$level->sortable  = true;
$level->hide  = true;
$level->default_value='100';

$menu = new Field();
$menu->fieldname = 'id_menu';
$menu->label     = t('MENU','Menú');   
$menu->type      = 'select';
$menu->len       = 5;
$menu->width     = 160;
$menu->editable  = true;
$menu->sortable  = true;
//$menu->values=Menu::$menus;
$menu->values    = array('1'=>t('MAIN','Principal'),'2'=>t('CUSTOMER_SERVICE','Atención al cliente'),'3'=>t('FOOTER','Footer')); //Menu::$menus;

$parent = new Field();
$parent->fieldname = 'item_parent';
$parent->label     = 'Parent';   
$parent->type      = 'int';
$parent->len       = 7;
$parent->width     = 20;
$parent->editable  = true;
$parent->sortable  = true;

$active = new Field();
$active->fieldname = 'item_active';
$active->label     = t('ACTIVE','Activo');   
$active->type      = 'bool';
$active->width     = 20;
$active->editable  = true;
$active->sortable  = true;
$active->default_value = 1;
/*
$inline_edit = new Field();
$inline_edit->fieldname = 'inline_edit';
$inline_edit->label     = 'Inline edit';   
$inline_edit->type      = 'bool';
$inline_edit->width     = 20;
$inline_edit->editable  = true;
$inline_edit->sortable  = true;
$inline_edit->default_value = 1;
$inline_edit->hide = true;
*/
$enabled = new Field();
$enabled->fieldname = 'item_visible';  // ALTER TABLE `TB_ITEM` CHANGE `item_enabled` `item_visible` INT(1) NULL DEFAULT '1';
$enabled->label     = 'Visible';   
$enabled->type      = 'bool';
$enabled->width     = 20;
$enabled->editable  = true;
$enabled->sortable  = true;
$enabled->default_value = 1;

$public = new Field();
$public->fieldname = 'item_public';  
$public->label     = t('PUBLIC','Público');   
$public->type      = 'bool';
$public->width     = 20;
$public->editable  = true;
$public->sortable  = true;
$public->default_value = 0;

$order = new Field();
$order->fieldname = 'item_order';
$order->label     = t('ORDER','Orden') ;   
$order->type      = 'int';
$order->len       = 5;
$order->width     = 20;
$order->editable  = true;
$order->sortable  = true;
//$order->hide  = true;

$tabla->title = t('PAGES','Páginas');
$tabla->verbose=false;
$tabla->output='table';
//$tabla->output='custom';
$tabla->page = $page;
$tabla->page_num_items = 500;
$tabla->show_empty_rows = false;
//$tabla->inline_edit = true;
$tabla->classname         = 'table table-bordered datatable-rows';     // 'table-bordered';

/*
$tabla->markup      = '<table id="[ID]" class="tb_id [CLASS]">[COLGROUP]<thead><th>Level</th>[HEADER]</thead><tbody>[BODY]</tbody><tfoot>[FOOTER]</tfoot></table>';  
$tabla->markup_row  = '<tr id="[ID]" data-tt-id="[ITEM_ID]"  data-tt-parent-id="[ITEM_PARENT]" class="cell [CLASS]"><td>[INDENT]</td>[CELLS]<td class="actions"><div>[ACTIONS]</div></td></tr>';
$tabla->markup_cell = '<td id="[ID]" class="[CLASS]" style="[STYLE]" val="[VAL]">[CONTENT]</td>';  
*/
/****/
$tabla->tree = true;
$tabla->key_parent = $parent;
/****/
//$tabla->orderby = 'item_id';
//$tabla->groupby = 'item_parent';

$tabla->verbose=false;
$tabla->addCol($id);
//$tabla->addCol($title);
$tabla->addCol($caption);
$tabla->addCol($parent);
$tabla->addCol($name);
$tabla->addCol($filename);
//$tabla->addCol($module);
$tabla->addCol($url);
//$tabla->addCol($text);
$tabla->addCol($level);
$tabla->addCol($menu);
$tabla->addCol($active);
$tabla->addCol($enabled);
$tabla->addCol($public);
//$tabla->addCol($inline_edit);
$tabla->addCol($order);

$tabla->perms['delete'] = $_ACL->HasPermission('edit_items'); 
$tabla->perms['edit']   = $_ACL->HasPermission('edit_items');
$tabla->perms['add']    = $_ACL->HasPermission('edit_items');
$tabla->perms['setup']  = Root(); 
$tabla->perms['view']   = true;

/***/
$tabla->table_group= $tabla->tablename;
$tabla->table_group_fieldname = 'item_order';
$tabla->table_group_pk_fieldname = 'item_id';
/***/
$tabla->orderby = 'id_menu,item_order';
$tabla->addFieldset($tabla->default_fieldset_name,'Datos');

class itemsEvents extends defaultTableEvents implements iEvents{ 
  
  private $recurselevel = 0;    
  private $level = 0;    

  function OnBeforeShowForm($owner,&$form,$id){
    global $_ACL;
    if($owner->state == 'update'){
      $help = ' <span style="font-size:0.9em;color:red;font-weight:300;"> Los miembros de los grupos marcados tendran acceso a esta página</span>';
      $itemACL = new ACL('',$id);
      $itemACL->buildACL();
      $aRoles = $_ACL->getAllRoles('full');

    //$IR = $itemACL->getItemRoles();
    //Vars::debug_var($IR); //$aRoles);
      $html = '<style>
                 .tb-items,
                 .tb-items tr    ,
                 .tb-items.fixed_headers tr:nth-child(1) th {width:400px;}

                 .tb-items.fixed_headers tr:nth-child(2) th:nth-child(1) {width:300px;text-align:left;}
                 .tb-items.fixed_headers tr:nth-child(2) th:nth-child(2) {width:50px;text-align:center;}
                 .tb-items.fixed_headers tr:nth-child(2) th:nth-child(3) {width:50px;text-align:center;}

                 .tb-items.fixed_headers td:nth-child(1) {min-width:300px;text-align:left;}
                 .tb-items.fixed_headers td:nth-child(2) {min-width: 50px;text-align:center;}
                 .tb-items.fixed_headers td:nth-child(3) {min-width: 50px;text-align:center;}

              </style>';

      $html .= '<table class="tb-items zebra fixed_headers table_roles ro">';
      $html .= '<thead>';
      $html .= '  <tr><th colspan="3">Role '.$help.'</th></tr>';
      $html .= '  <tr><th class="thc">'.t('MEMBER','Miembro').'</th><th class="thc" id="item-role-yes">'.t('YES').'</th><th  id="item-role-no" class="thc">'.t('NO').'</th></tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      foreach ($aRoles as $k => $v){
        $_item_has_role =$itemACL->itemHasRole($v['ID']);
        $html .= '  <tr class="item-role-'.($_item_has_role?'yes':'no').'">';
        $html .= '    <td>'.$v['Name'].'</td>';
        $html .= '    <td class="tdc"><input type="radio" name="role_'.$v['ID'].'" id="role_'.$v['ID'].'_1" value="1"';
        if ( $_item_has_role) $html .= ' checked="checked"';
        $html .= '/></td>';
        $html .= '<td class="tdc"><input type="radio" name="role_'.$v['ID'].'" id="role_'.$v['ID'].'_0" value="0"';
        if (!$_item_has_role) $html .= ' checked="checked"';
        $html .='/></td>';
        $html .= '</tr>';
      }
      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '  <script>';
      $html .= '      $(\'#item-role-yes\').click(function(){  $(\'.item-role-yes\').toggle(); $(this).toggleClass(\'item-role-hidden\'); });';
      $html .= '      $(\'#item-role-no\') .click(function(){  $(\'.item-role-no\') .toggle(); $(this).toggleClass(\'item-role-hidden\'); });';
      $html .= '  </script>';

      $html .= '<script type="text/javascript">$("#div_box_perms").draggable();</script>';
      /*
      $p = new formElementHtml;
      $p->html = $html;
      $form->addElement($p);
      */
      $html_permisions = new formElementHtml();
      $html_permisions->html = $html;

      if ($owner->state != 'filter'){
        $fs_permisions = new fieldset('permisions','Roles');  //fs_roles
        $fs_permisions->displaytype = 'tab';
        $fs_permisions->addElement($html_permisions);
        $form->addElement($fs_permisions);
        
        if(Root()){       
           
            $html_childs = '<div id="delete_recursively">
               <div class = "alert"><p style="margin:20px auto;">Pulsando el botón \'Eliminar\' verá un resumen de lo que ocurrirá antes de confirmar la operación.</p></div>
               <button id = "btn_delete_recursively" 
                    class = "btn_delete_recursively btn btn-danger"  
                    style = "margin-top:40px;width:100%;padding:20px;"
                   hx-get = "/control_panel/ajax/menu_item_delete/id='.$id.'/op=test"
                hx-target = "#delete_recursively" 
             hx-indicator = ".loader"
               hx-trigger = "click"
                  hx-swap = "innerHTML"> Eliminar </button><div class="htmx-indicator loader"></div>.</div>';
            
            $html_item_childs = new formElementHtml();
            $html_item_childs->html = $html_childs;
            $fs_item_childs = new fieldset('item_childs','Borrado');  //fs_roles
            $fs_item_childs->displaytype = 'tab';
            $fs_item_childs->addElement($html_item_childs);
            $form->addElement($fs_item_childs);
            
        }
        
      }

    }
  }

  function OnAfterShowForm($owner,&$form,$id){
      ?><script>htmx.process(document.querySelector('#delete_recursively'))</script><?php
  }

  private function setRoles($owner,$post,$id) {

    if (++$this->recurselevel > 350) {throw new Exception('***Too many levels: '.$this->recurselevel);}

    // $content =  print_r($post,true);
    
    foreach ($post as $k => $v){

      //k,v=role_1,0

      if (substr($k,0,5) == "role_"){
        $roleID = str_replace("role_","",$k);
        if(trim($roleID)!==''){

            if ($v == '0' || $v == 'x'){
              $sql = sprintf("DELETE FROM ".TB_ACL_ITEM_ROLES." WHERE id_item = %u AND id_role = %u",$id,$roleID);                    
            }else {
               /**  
               if(CFG::$vars['db']['type']  == 'sqlite')
                  //$sql = sprintf("INSERT INTO ".TB_ACL_USER_ROLES." (id_user, id_role,user_role_add_date) VALUES (%u, %u, %s) ON CONFLICT(id_user, id_role) DO NOTHING",$post['user_id'],$roleID, date ("Y-m-d H:i:s") );
                  $sql = sprintf("INSERT OR REPLACE INTO ".TB_ACL_ITEM_ROLES." (id_item, id_role) VALUES (%u, %u)",$id,$roleID);
              else 
                */
                  $sql = sprintf("REPLACE INTO ".TB_ACL_ITEM_ROLES." SET id_item = %u, id_role = %u, item_role_add_date = '%s'",$id,$roleID,date ("Y-m-d H:i:s"));

            }

            //$content.= "\n\nk,v=".$k.','.$v . "\n[".$sql.']';

            /*if($sql)*/ Table::sqlExec($sql);
        }
      }
    }

    //create_php_file(SCRIPT_DIR_MODULE.'/00_setroles_'.$id,$content);
    
    $numChilds = $owner->recordCount('SELECT COUNT(item_id) AS total FROM '.TB_ITEM.' WHERE item_parent='.$id);
    if ($numChilds){
      $tmpLevel = $owner->getFieldValue('SELECT item_level FROM '.TB_ITEM.' WHERE item_id='.$id);
      Table::sqlExec('UPDATE '.TB_ITEM.' SET item_level='.$tmpLevel.' WHERE item_parent='.$id);
      $sqltmp  = 'SELECT item_id FROM '.TB_ITEM.' WHERE item_parent = '.$id;
      $tmpres  = $owner->sql_query($sqltmp); 
      foreach($tmpres as $tmpfila){ $this->setRoles($owner,$post,$tmpfila['item_id']); }
    }
    
  }
  
  function OnAfterUpdate($owner,&$result,&$post){
    global $_ACL;
    $this->recurselevel  = 0;  
    $this->setRoles($owner,$post,$post['item_id']);
    unset($_SESSION['ACL']);
    $_ACL = new ACL();
    $_ACL->buildACL();
  }  
  
  // http://ludo.cubicphuse.nl/jquery-treetable/
  // http://webdesignledger.com/resources/12-useful-jquery-plugins-for-working-with-tables
  // https://github.com/ZorGleH/jquery-tree-table
  //  https://github.com/ludo/jquery-treetable OK
  //////////////////////////////////////////////////////////////////////////////////
  
  function OnDrawRow($owner,&$row,&$class){
      
      $filename = $owner->colByName('FILE_NAME')->uploaddir.'/'.$row['FILE_NAME'];
      if($row['FILE_NAME'] && file_exists($filename)){
          $row['FILE_NAME']  = "<a class=\"{$ext} open_file_image\" rel=\"gallery\" href=\"{$row['IMAGES']['FILE_NAME']['URL']}\">"
                             . "<img style=\"height:22px;\" src=\"{$row['IMAGES']['FILE_NAME']['THUMB']}\"    ></a>";
      }else{
          $row['FILE_NAME']='<i class="fa fa-image"></i>';
      }

  }

  function OnInsert($owner,&$result,&$post) { 
      if(!$post['item_name'])$post['item_name']=Str::sanitizeName($post['item_caption']);
      $post['item_name']=Str::sanitizeName($post['item_name']);
      $result['the_filename']=$post['item_name'].'.jpg'; //'header_'.str_replace(' ','_',strtolower($post['item_name'])).'.jpg';
      ////if(!$post['item_caption'])$post['item_caption']=$post['item_title'];
	  //$result['the_filename']='header_'.$post['item_name'].'.jpg';

    $type = '8';
    $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'page - insert\',\''.Str::escape(print_r($post,true)).'\')';
    Table::sqlExec($log_sql);

  }
  
  function OnUpdate($owner,&$result,&$post) { 
      if(!$post['item_name'])$post['item_name']=Str::sanitizeName($post['item_caption']);
      $post['item_name']=Str::sanitizeName($post['item_name']);
      $result['the_filename']=$post['item_name'].'.jpg'; //'header_'.str_replace(' ','_',strtolower($post['item_name'])).'.jpg';
      if($post['fake_input_FILE_NAME']==''){
          unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.$result['the_filename']);
          unlink($owner->colByName('FILE_NAME')->uploaddir.'/'.TN_PREFIX.$result['the_filename']);
      }	  
      ////if(!$post['item_caption'])$post['item_caption']=$post['item_title'];
	  //$result['the_filename']='header_'.$post['item_name'].'.jpg';
      //if($post['FILE_NAME']==''){
      //     unlink('./media/images/'.$result['the_filename']);
      //     unlink('./media/images/'.TN_PREFIX.$result['the_filename']);
      //}

      $type = '8';
      $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$_SESSION['userid'] .',\''.$_SESSION['user_email'].'\',\'menu - update\',\''.Str::escape(print_r($post,true)).'\')';
      Table::sqlExec($log_sql);


  }

  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
     //if  ($col->fieldname=='fake_input_FILE_NAME')    
      if  ($col->fieldname=='FILE_NAME') $result['local_file'] = $result['the_filename'];
  }

  function OnAfterDrawRow($owner,&$row,&$markup){
    /*
      $a = array('[ITEM_ID]'  => $row['item_id'],
                 '[ITEM_PARENT]'  => $row['item_parent']);
      $markup = str_replace( array_keys($a), array_values($a), $markup) ;  
     **/
  }
  
  function OnAfterShow($owner){ 
    ?>
    <script type="text/javascript">
    $(function() { 
        $('#<?=TB_ITEM?> .level-2 .cell_item_caption').css('padding-left','15px');
        $('#<?=TB_ITEM?> .level-3 .cell_item_caption').css('padding-left','30px');
        $('#<?=TB_ITEM?> .level-4 .cell_item_caption').css('padding-left','45px');
        $('#<?=TB_ITEM?> .level-5 .cell_item_caption').css('padding-left','60px');
        $('#<?=TB_ITEM?> .level-6 .cell_item_caption').css('padding-left','75px');
        /**********************
        $("#<?=TB_ITEM?>").tableDnD({
            hierarchyLevel: 1,
            indentArtifact:'<div class="indent">&nbsp;</div>',
            onDragClass: "alt",
           //dragHandle:'.cell_item_url',
            onDrop: function(table, row) {
                var rows = table.tBodies[0].rows;
                var debugStr = "Row dropped was "+row.id+". New order: ";
                var separator='';
                var keys = '';
                var positions = '';
                for (var i=0; i<rows.length; i++) {
                    keys += separator+/[^\-]*$/.exec(rows[i].id);              // /[^\-]*$/
                    positions += separator+(i+1);
                    separator=',';
                }
                console.log(debugStr+'['+keys+']['+positions+']');
                var url= 'control_panel/ajax/op=rearrange'; ///table=<?=$owner->tablename?>/keys='+keys+'/positions='+positions+'/group=-1';
                $.post(url,{"table":'<?=$owner->tablename?>',"keys":keys,"positions":positions,"group":'-1'},function(data, textStatus, jqXHR){  
                      if(data.error==0){
                        showMessageInfo(data.msg);
                      }else{
                        showMessageError(data.msg);
                      }
                },'json');

            },
            onDragStart: function(table, row) {


                console.log("Started dragging row "+row.id);
            }
        });
        ************/
    });
    </script>
    <style>
    .fa-plus-square-o,
    .fa-minus-square-o{cursor:pointer;}
    .row-hidden{display:none;}
    </style>
    <?php 
  }
  
  /*********************************************************************************
  function beginTable($cols) {
    echo '<table id="erp_item" class="tb_id table table-bordered table-striped datatable-rows">';
    echo '<thead><tr><th>*</th>';
    foreach ($cols as $col){
     if(!$col->hide) echo '<th>'.$col->label.'</th>';
    }
    echo '</tr></thead>';
  }  
  
  function bodyTable($owner,$id) {

    $this->level++;
    if (++$this->recurselevel > 350) {throw new Exception('***Too many levels: '.$this->recurselevel);}
    
    $sql  = 'SELECT * ';
    $sql .= 'FROM '.$owner->tablename;
    $sql .= ' WHERE item_parent='.$id.' AND id_menu=1 ';  //"NODO_N2 IS NOT NULL";
    $sql .= ' ORDER BY item_id';
    $rows = $owner->query2array($sql);

    if($rows){

      echo '<tbody>';
      //echo '<tr><td colspan="'.count($owner->cols).'"></td></tr>';
        foreach( $rows as $row) {
            if($row['item_parent']==$id) {

                echo '<tr>';
                 // style="border-left:'.($this->level*10).'px solid orange;"
                echo '<td style="padding:0;"><div style="margin:0;border:1px solid black;width:'.($this->level*10).'px;">'.$this->level.'</div></td>';
                foreach( $owner->cols as $col) { 
                  if(!$col->hide) {
                    echo '<td>'.$row[$col->fieldname].'</td>';
                  }
                }

                echo '</tr>';

                $this->bodyTable($owner,$row['item_id']);

            }
          
        } 
        //echo '<tr><td colspan="'.count($owner->cols).'"></td></tr>';
        echo '</tbody>';

    }
    $this->level--;
  }

  function endTable() {
    echo '</table>';
  }  
  
  function OnShow($owner) {
    $this->beginTable($owner->cols);
    $this->bodyTable($owner,0);
    $this->endTable();
    //echo '<pre>'; //print_r($owner->rows);   //echo '</pre>';
  } 
 
  **********************************************************************************************/
   
}


$tabla->events = New itemsEvents();  

