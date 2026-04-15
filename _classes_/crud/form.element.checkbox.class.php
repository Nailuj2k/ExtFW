<?php

/* * * * * * * * * * * *
 *
 * https://simontabor.com/labs/toggles/
 *
 *
 * * * */

class formCheckbox extends formElement{
  
    public $value;
    
    public function __construct($element) {
        
        parent::__construct($element);

    }

    public function render(){

      $a_label = array('[NAME]'        => $this->name, 
                       '[LABEL]'       => $this->label  );

      $a_input = array('[NAME]'        => $this->name, 
                       '[ID]'          => $this->name,
                     //  '[CLASS]'       => implode(' ',$this->classes),
                       '[READONLY]'    => $this->disable     ? 'readonly="readonly"'                    : '' ,
                       '[VALUE]'       => $this->value ? 'true' : 'false',
                       '[HELP]'        => $this->help ? $this->help : ''
                    );

      $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                         '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_CHECKBOX)  );

      echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT_CHECKBOX) ;      

    }
  
}
