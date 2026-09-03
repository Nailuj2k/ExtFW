<?php

/* * * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formHtml extends formElement{
  
    public $value;
    
    public function __construct($element) {
        
        parent::__construct($element);

    }

    public function render(){
      if ($this->label){
          $a_label = array('[NAME]'        => $this->name, 
                           '[LABEL]'       => $this->label  );

          $a_input = array('[NAME]'        => $this->name, 
                           '[ID]'          => $this->name,
                           '[VALUE]'       => $this->value
                        );

          $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                             '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_HTML)  );

          echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT) ;
      }else{

          $a_input = array('[NAME]'        => $this->name, 
                           '[ID]'          => $this->name,
                           '[VALUE]'       => $this->value       );

          echo str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_HTML );
      
      }

    }


  
}
