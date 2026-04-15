<?php

/* * * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formInput extends formElement{
  
    private $size;
    private $maxsize;
    public $value;
    
    public function __construct($element) {
        
        $this->default_value = $element->default_value;
        
        parent::__construct($element);
        
        if (!$this->value) {
            if      ($element->type=='int'||$element->type=='bigint') $this->value = '0'; 
            else if ($element->type=='decimal')                       $this->value = '0.00';
            else                                                      $this->value = '';
        }
        
        $this->type  = ($element->type=='text'||$element->type=='int'||$element->type=='bigint'||$element->type=='varchar'||$element->type=='decimal'|| $element->type=='datetime'||$element->type=='progress'||!$element->type)
                     ? 'text' 
                     :  $element->type ;
                     
        $this->calculated = $element->calculated; //ADD 20131031
        $this->placeholder = $element->placeholder;
        $this->disable = $element->disable;

        if     ($element->len)  $this->maxsize = $element->len;
        if     ($element->size) $this->size = $element->size;
        else if($element->len)  $this->size = $element->len;
        if     ($this->size>80) $this->size = 80;
        if     ($this->disable) $this->classes[]='disabled';
    }

    public function render(){
        // http://v4-alpha.getbootstrap.com/components/forms/

      $a_label = array('[NAME]'        => $this->name, 
                       '[LABEL]'       => $this->label  );

      $a_input = array('[NAME]'        => $this->name, 
                       '[ID]'          => $this->name,
                       '[CLASS]'       => $this->classes?implode(' ',$this->classes):'',
                       '[TYPE]'        => $this->type,
                       '[PLACEHOLDER]' => $this->placeholder ? 'placeholder = "'.$this->placeholder.'"' : '',
                       '[READONLY]'    => $this->disable     ? 'readonly="readonly"'                    : '' ,
                       '[SIZE]'        => $this->size        ? 'size = "'.$this->size.'"'               : '',  
                       '[MAXSIZE]'     => $this->maxsize     ? 'maxsize = "'.$this->maxsize.'"'         : '',
                       '[VALUE]'       => $this->value
                    );

      $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                         '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_TEXT)  );

      echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT) ;


    }
  
}
