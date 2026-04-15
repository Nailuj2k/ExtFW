<?php

/* * * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formInputNumber extends formElement{
  
    private $size;
    private $maxsize;
    public $value;
    
    public function __construct($element) {
        
        $this->default_value = $element->default_value;
        parent::__construct($element);
        
        if  (!$this->value) $this->value = 1;
        if  ($element->step) $this->step = $element->step;
        if  ($element->max)  $this->max = $element->max;
    
    }
/*    
   .'<input type="button" value="-" class="button-minus" data-field="[NAME]" [READONLY]>'
    .'<input type="number" id="[ID]" name="[NAME]" step="[STEP]" max="[MAX]" value="[VALUE]" class="quantity-field [CLASS]" [READONLY]>'
    .'<input type="button" value="+" class="button-plus" data-field="[NAME]" [READONLY]>'
*/    

    public function render(){
        // http://v4-alpha.getbootstrap.com/components/forms/

      $a_label = array('[NAME]'        => $this->name, 
                       '[LABEL]'       => $this->label  );

      $a_input = array('[NAME]'        => $this->name, 
                       '[ID]'          => $this->id,
                       '[CLASS]'       => $this->classes?implode(' ',$this->classes):'',
                       '[STEP]'        => $this->step    ? $this->step           : '1',
                       '[MAX]'         => $this->max     ? $this->max            : '' ,
                       '[READONLY]'    => $this->disable ? 'readonly="readonly"' : '' ,
                       '[VALUE]'       => $this->value
                    );

      $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                         '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_NUMBER)  );

      echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT) ;


    }
  
}
