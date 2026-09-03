<?php

/* * * * * * * * * * * *
 *
 * form button
 *
 *
 * * * */

class formButton extends formElement{
    
    public $default_value = 'Aceptar';
    public $type = 'submit';
    public $javascript; // = "alert('click!')"; 
    public $class = 'btn btn-success'; 
    public $name; 
    public $extra; 
    
    public function __construct($element) {
        
        parent::__construct($element);
        
        if( $element->name) $this->name = $element->name;
        if( $element->class) $this->class = $element->class;
        if( $element->javascript) $this->javascript = $element->javascript;
        if( $element->extra) $this->extra = $element->extra;
    
    }
    
    public function render(){
      echo str_replace( array('[ID]','[NAME]','[TYPE]','[CLASS]','[VALUE]','[EXTRA]','[JAVASCRIPT]','[BEFORE]','[AFTER]'), 
                        array($this->id,$this->name,$this->type,$this->class,$this->value,$this->extra,$this->javascript,$this->before,$this->after), 
                        MARKUP_BUTTON) ;

    }
  
}
