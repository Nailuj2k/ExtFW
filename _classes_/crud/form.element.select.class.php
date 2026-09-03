<?php

/* * * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formSelect extends formElement{
  
    public $values = array();
    public $allowNull=false;
    public $clearValue=0;
    public $clearText='----';

    public function __construct($element) {
        
        $this->default_value = $element->default_value;
        
        parent::__construct($element);
        
        $this->allowNull   = $element->allowNull;
        $this->clearValue  = $element->clearValue;
        $this->clearText   = $element->clearText;
        $this->values      = $element->values;
        $this->child_ajax_url     = $element->child_ajax_url;
        $this->child_fieldname    = $element->child_fieldname;
        $this->child_source_sql   = $element->child_source_sql;    
    }

    public function render(){
       
      $options = '';
      if ($this->allowNull==true)        $options .= '<option value="'.$this->clearValue.'">'.t($this->clearText).'</option>';
      foreach ($this->values as $k=>$v)  $options .=  '<option value="'.$k.'" '. ($k==$this->value?'SELECTED':'') .'>'.t($v).'</option>';

      $a_label = array('[NAME]'        => $this->name, 
                       '[LABEL]'       => $this->label  );


      $a_input = array('[NAME]'        => $this->name, 
                       '[ID]'          => $this->name,
                       '[CLASS]'       => $this->classes?implode(' ',$this->classes):'',
                       '[OPTIONS]'     => $options,
                       '[READONLY]'    => $this->disable     ? 'readonly="readonly"'                    : '' ,
                       '[VALUE]'       => $this->value
                    );

      $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                         '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_SELECT)  );

      echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT) ;
      if($this->child_fieldname && $this->child_source_sql){
          echo str_replace( 
              array('[NAME]','[AJAX_URL]','[CHILD_FIELDNAME]','[CHILD_SOURCE_SQL]'), 
              array($this->name,$this->child_ajax_url,$this->child_fieldname,$this->child_source_sql), 
              MARKUP_SELECT_CHILD) ;
       }
   }
  
}
