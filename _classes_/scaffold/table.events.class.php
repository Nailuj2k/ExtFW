<?php

  //***  Events interface definition

  interface iEvents {
    function OnUpdate($owner,&$result,&$post);
    function OnDelete($owner,&$result,$id);
    function OnDrawColTitle($owner,&$col);
    function OnDrawRow($owner,&$row,&$class);
    function OnAfterDrawRow($owner,&$row,&$markup);
    function OnDrawCell($owner,&$row,&$col,&$cell);
    function OnBeforeShow($owner); 
    function OnShow($owner); 
    function OnAfterShow($owner); 
    function OnBeforeShowForm($owner,&$form,$id);
    function OnAfterShowForm($owner,&$form,$id);
    function OnBeforeInsert($owner);
    function OnBeforeUpdate($owner,$id);
    function OnInsert($owner,&$result,&$post);
    function OnFilter($owner,&$result,&$post);
    function OnAfterInsert($owner,&$result,&$post);
    function OnAfterUpdate($owner,&$result,&$post);
    function OnPostCol($owner,&$result,&$col,&$value);
    function OnBeforeShowDetail($owner,&$row);
    function OnCalculate($owner,&$row);
    function OnBeforeSaveFile($owner,&$col,$filename,&$result);
    //function OnPrintDetail($owner, $template, &$names, &$values);   
    function OnAfterPrint($owner, $template);
  }

  //*** Default events
  class defaultTableEvents implements iEvents{ 
    function OnUpdate($owner,&$result,&$post){ /* Default event code here */ }
    function OnDelete($owner,&$result,$id){
      /**
      if($owner->detail_tables)
      foreach($owner->detail_tables as $detail_table){
        $childs = $owner->recordCount($detail_table,'WHERE '.$owner->pk->fieldname.' = '.$id);
        if($childs >0) {
          $result['error'] = 5;  // Abort deletion !!
          $result['msg'] = 'Esta fila no puede eliminarse porque tiene '.$childs.' filas hijas';  //TODO: Translate this
          exit;
        }
      }
      **/
    }
    function OnDrawColTitle($owner,&$col) {}
    function OnDrawRow($owner,&$row,&$class){ }
    function OnAfterDrawRow($owner,&$row,&$markup){}
    function OnDrawCell($owner,&$row,&$col,&$cell){ }
    function OnBeforeShow($owner){ } 
    function OnShow($owner){ } 
    function OnAfterShow($owner){ } 
    function OnBeforeShowForm($owner,&$form,$id){}
    function OnAfterShowForm($owner,&$form,$id){}
    function OnBeforeInsert($owner){}
    function OnBeforeUpdate($owner,$id){}
    function OnInsert($owner,&$result,&$post){ /* Sample event code: $result['error']=1; $result['msg']='Not allowed!!'; */  }
    function OnFilter($owner,&$result,&$post){}
    function OnAfterInsert($owner,&$result,&$post){}
    function OnAfterUpdate($owner,&$result,&$post){}
    function OnPostCol($owner,&$result,&$col,&$value){}
    function OnBeforeShowDetail($owner,&$row){}
    function OnCalculate($owner,&$row){}
    function OnBeforeSaveFile($owner,&$col,$filename,&$result ){}
//  function OnPrintDetail($owner, $template, &$names, &$values){}
    function OnLog($owner, $type, &$subject, &$message){}
    function OnAfterPrint($owner, $template) {}

 }