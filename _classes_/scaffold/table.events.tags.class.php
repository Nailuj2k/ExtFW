<?php 

  class defaultTableEventsTags extends defaultTableEvents {
    
   public $tb_tags;             // //TSK_TAGS
   public $tb_tags_pk;          // 'TAG_ID'
   public $tb_tags_color;       // 'COLOR'
   public $tb_items_tags;       // //TASK_TASKS_TAGS
   public $tb_items_tags_pk;    // pk   //TASK_TAG_ID         $owner->pk->fieldname
   public $tb_items_tags_item;  // item //TASK_ID             $this->tablename.'_TAGS
   public $tb_items_tags_tag;   // tag  //TAG_ID             $this->tablename.'_TAGS
   public $tb_tags_name;
   public $tb_tags_caption;
   public $tb_tags_displaytype = 'float';
   public $tb_tags_tabname = 'tags';
   public $tb_tags_tablabel = 'Etiquetas';
   //FIX check tables tb_tags y tb_items_tags
   //FIX Try auto create them
   public $id_parent = false;

   private function and_id_parent(){
       //return false;
       return $this->id_parent === true ? ' AND ID_PARENT  = '.$_SESSION['PAGE_FILES_ID_PARENT'] : ' AND (ID_PARENT  < 1 OR ID_PARENT IS NULL)';
       //return ' AND ID_PARENT  = '.$_SESSION['PAGE_FILES_ID_PARENT'] ;
   }

   function prepareId($id){
      return $id; //"'".str_pad($id, 6, "0", STR_PAD_LEFT)."'";
   }

   private function updateTags($owner,&$result,$post) {   
    // ARRAY OF TAG_ID OF CURRENT ENTRY
    $sql0 = 'SELECT '.$this->tb_items_tags_tag.' FROM '.$this->tb_items_tags
          .' WHERE '.$this->tb_items_tags_item.' = '.$this->prepareId($post[$owner->pk->fieldname]);  //$this->pk->fieldname  
    $query0 = $owner->sql_query($sql0); 
    $tags0 = array();
    if($query0){
      foreach($query0 as $rowtag0){
        $tags0[] = $rowtag0[$this->tb_items_tags_tag];  
      }
    }
    // ARRAY OF ACTIVE TAG_ID 
    $sql1 = 'SELECT '.$this->tb_tags_pk.' FROM '.$this->tb_tags.' WHERE ACTIVE = 1'.$this->and_id_parent();  //$this->pk->fieldname
    $query1 = $owner->sql_query($sql1); 
    if($query1){  //FIX check count
      //$result['msg'] = $sql1.'<br />';
      foreach($query1 as $rowtag1){
        $tag_ON = in_array($rowtag1[$this->tb_tags_pk], $tags0);   // tag_ON -> o in bd
        $sql2 = false;    
        if      (isset($post['chk_tag_'.$rowtag1[$this->tb_tags_pk]]) && $tag_ON){   //on  in post and on  in bd -> no action required
          $sql2 = false;    
        }else if (isset($post['chk_tag_'.$rowtag1[$this->tb_tags_pk]]) && !$tag_ON) { //on  in post and off in bd -> add entry in bd
          $sql2 = 'INSERT INTO '.$this->tb_items_tags.' ('.$this->tb_items_tags_item.','.$this->tb_items_tags_tag.') VALUES('.$this->prepareId($post[$owner->pk->fieldname]).','.$rowtag1[$this->tb_tags_pk].')';
        }else if (!isset($post['chk_tag_'.$rowtag1[$this->tb_tags_pk]]) && $tag_ON){ //off in post and on in bd -> delete entrey in bd
          $sql2 = 'DELETE FROM '.$this->tb_items_tags.' WHERE '.$this->tb_items_tags_item.'= '.$this->prepareId($post[$owner->pk->fieldname]).' AND '.$this->tb_items_tags_tag.' = '.$rowtag1[$this->tb_tags_pk];
        }
        if($sql2){
          $owner->sql_exec($sql2);
          //$result['msg'].=print_r($tags0,true).'<br />';
        }
      }
    }
  }

  public function showTagForm($owner,$form,$id) {   //(READ = 0 OR READ IS NULL)  //NVL(READ,0)=0
    if($owner->state=='insert'||$owner->state=='update'){
    if(!$this->tb_tags_caption) $this->tb_tags_caption = $this->tb_tags_name;
    $tags = array();
    if($id){
       $sql0 = 'SELECT '.$this->tb_tags_pk.' FROM '.$this->tb_tags
             .' WHERE '.$this->tb_tags_pk.' IN (SELECT '.$this->tb_items_tags_tag.' FROM '.$this->tb_items_tags.' WHERE '.$this->tb_items_tags_item.' = '.$this->prepareId($id).')'; 
       $query0 = $owner->sql_query($sql0); 
       if($query0){
         foreach($query0 as $rowtag0){
           $tags[] = $rowtag0[$this->tb_tags_pk];
         }
       }
    }
    $str_css  = '<style>';
    $fsTags=new Fieldset($this->tb_tags_tabname,$this->tb_tags_tablabel);
    $fsTags->displaytype = $this->tb_tags_displaytype;
    $sql1 = 'SELECT '.$this->tb_tags_pk.','.$this->tb_tags_color.','.$this->tb_tags_name.','.$this->tb_tags_caption.' FROM '.$this->tb_tags.' WHERE ACTIVE = 1'.$this->and_id_parent();  //$this->pk->fieldname
    $query1 = $owner->sql_query($sql1); 
    if($query1){
      $labels = array();
      foreach($query1 as $rowtag1){
        //$rowtag[$this->tb_tags_color];
        //$rowtag['NAME']; 
        //$column = 'chk_tag['.$rowtag1['TAG_ID'].']'; //.$rowtag1['TAG_ID'];
        $column = 'chk_tag_'.$rowtag1[$this->tb_tags_pk];
        ${$column} = new Field();
        ${$column}->fieldname = $column;
        ${$column}->label =$rowtag1[$this->tb_tags_caption];//.'-'.$rowtag['COLOR'];
        ${$column}->type = 'bool';
        ${$column}->editable = true;
        ${$column}->width = 20;
        $checked1 = in_array($rowtag1[$this->tb_tags_pk],$tags);
        $fsTags->addElement(new formCheckbox(${$column},$checked1));
        $str_css .= '#form_cb_'.$column.'{background-color:'.$rowtag1[$this->tb_tags_color].' !important;/*display:inline-block;width:120px;*/}';
      }
    }
    $form->addElement($fsTags);
    if($this->tb_tags_displaytype=='tab'){
      $str_css .= '#fs_'.$this->tb_tags_tabname.'{/*max-height:260px;*/ overflow:auto; border:2px orange;}#fs_'.
                         $this->tb_tags_tabname.' label{width:345px;} #fs_'.
                         $this->tb_tags_tabname.' .controls{position: absolute;left: 350px} ';
    }
    $str_css .= '</style>'; //FIX usar css this table id
    if($this->tb_tags_displaytype=='float') $str_css .= '<script type="text/javascript">$("#fs_fs_tags").draggable();</script>'; //FIX usar css this table id
    echo /*************'<b>PAGE_FILES_ID_PARENT:'.$_SESSION['PAGE_FILES_ID_PARENT'].'</b>'.*/$str_css;
    }
  }

  function OnBeforeShowForm($owner,&$form,$id) {
    //parent::OnBeforeShowForm($owner,$form,$id);
    $this->showTagForm($owner,$form,$id);
  }

  function OnDelete($owner,&$result,$id){ 
   // parent::OnDelete($owner,$result,$id);
    $owner->sql_exec('DELETE FROM '.$this->tb_items_tags.' WHERE '.$this->tb_items_tags_item.' = '.$id);
  }

  function OnDrawRow($owner,&$row,&$class){
    if(!$this->tb_tags_caption) $this->tb_tags_caption = $this->tb_tags_name;
    $sql_ntags = 'SELECT COUNT('.$this->tb_items_tags_tag.') FROM '.$this->tb_items_tags
               .' WHERE '.$this->tb_items_tags_tag.' IN ' //  $row[$this->tb_items_tags_pk] 
               .'(SELECT '.$this->tb_tags_pk.' FROM '.$this->tb_tags.' WHERE '.$this->tb_items_tags_item.' = '.$this->prepareId($row[$owner->pk->fieldname]).')'; 
    $ntags = $owner->recordCount($sql_ntags); 
    $row['A_TAG_NAMES'] = array();
    $row['A_TAG_LABELS'] = array();
    if($ntags >0) {

      $sql = 'SELECT '.$this->tb_tags_color.' AS COLOR,'.$this->tb_tags_name.' AS NAME,'.$this->tb_tags_caption.' AS CAPTION FROM '.$this->tb_tags.' WHERE '.$this->tb_tags_pk                   // $row[$this->tb_items_tags_pk]
           .' IN (SELECT '.$this->tb_items_tags_tag.' FROM '.$this->tb_items_tags.' WHERE '.$this->tb_items_tags_item.' = '.$this->prepareId($row[$owner->pk->fieldname]).')';  //
      $query = $owner->sql_query($sql); 
      if($query){
        $labels = array();
        foreach($query as $rowtag){
          $row['A_TAG_LABELS'][] = '<a href="'.MODULE.'/tag/'.Str::SanitizeName($rowtag['NAME']).'" class="label label-tag" id="tag_'.Str::SanitizeName($rowtag['NAME']).'"  data-class="'.Str::SanitizeName($rowtag['NAME']).'"  data-filter=".'.Str::SanitizeName($rowtag['NAME']).'" title="'.t($rowtag['CAPTION']).'" style="background-color:'.$rowtag[$this->tb_tags_color].'">'.t($rowtag['CAPTION']).'</a>'; 
          $row['A_TAG_NAMES'][]=Str::SanitizeName($rowtag[$this->tb_tags_name]);
        }
      }


    }

    if($owner->download_count_fieldname)
        if($row[$owner->download_count_fieldname])
            $row['A_TAG_LABELS'][] = '<span id="download-label-'.$row['ID'].'" class="label" style="background-color:#9EACBC" title="'.$row[$owner->download_count_fieldname].' descargas">'.$row[$owner->download_count_fieldname].'</span>'; 
    
    if(count($row['A_TAG_LABELS'])>0)  //DEPRECATED
        $row['TAGS'] =  '<span class="labels">'.implode('',$row['A_TAG_LABELS']).'</span>';  

    //parent::OnDrawRow($owner,$row,$class);
  }

  function OnAfterInsert($owner,&$result,&$post){
    //parent::OnAfterInsert($owner,$result,$post);
    if($result['error']==0 && $result['last_insert_id']){
     // $post[$owner->pk->fieldname] = $result['last_insert_id'];
      $this->updateTags($owner,$result,$post);
    }
  }

  function OnAfterUpdate($owner,&$result,&$post){
    //parent::OnAfterUpdate($owner,$result,$post);
    if ($result['error']==0) $this->updateTags($owner,$result,$post);
  }

  function OnAfterCreate($owner){ 
    
    //if($owner->recordCount()<1){
  
    //}
    //Create php file ¿from templates TABLE_TB_TAGS and TABLE_ITEM_TAGS ?
    /*
    $tb_tags;            // TSK_TAGS       // table tags
    $tb_tags_pk;         // TAG_ID         // fieldname pk
    $tb_items_tags;      // TSK_TASKS_TAGS // table items_tags (n to n)
    $tb_items_tags_pk;   // TASK_TAG_ID    // table items_tags pk     
    $tb_items_tags_item; // TASK_ID        // table items_tags item_id
    $tb_items_tags_tag;  // TAG_ID         // table items_tags tag_id 
    */

  }

 }