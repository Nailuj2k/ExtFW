<?php

/* * * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formHidden extends formElement{
  
    public $value;
    
    public function __construct($element) {
        
        parent::__construct($element);

    }

    public function render(){

      $a_input = array('[NAME]'        => $this->name, 
                       '[ID]'          => $this->name,
                       '[VALUE]'       => $this->value       );

      echo str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_HIDDEN );

    }
  
}
