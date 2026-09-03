<?php

/* * * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formTextarea extends formElement{
  
    private $rows;
    public $value;
    public $wysiwyg = false;
    public $markup = MARKUP_TEXTAREA;


    public function __construct($element) {
        
        $this->default_value = $element->default_value;
        
        parent::__construct($element);
        
        $this->rows = $element->rows ? $element->rows : '4';
        $this->wysiwyg = $element->wysiwyg;
        $this->placeholder = $element->placeholder;

    }

    public function render(){
        // http://v4-alpha.getbootstrap.com/components/forms/
        if($this->wysiwyg=='trix'){
            $this->markup = MARKUP_WYSIWYG_TRIX;
            ?>  
            <link rel="stylesheet" type="text/css" href="<?=SCRIPT_DIR_LIB?>/trix/trix.css">
            <script type="text/javascript" src="<?=SCRIPT_DIR_LIB?>/trix/trix.js"></script>
            <?php      
        }else if($this->wysiwyg=='ckeditor'){
            $this->markup = MARKUP_WYSIWYG_CKEDITOR;
            ?> 
            <script src="https://cdn.ckeditor.com/4.13.0/basic/ckeditor.js"></script>
            <?php      
        }else if($this->wysiwyg=='jodit'){
            $this->markup = MARKUP_WYSIWYG_JODIT;
            ?> 
            <link rel="stylesheet" type="text/css" href="<?=SCRIPT_DIR_LIB?>/jodit/jodit.min.css">
            <script type="text/javascript" src="<?=SCRIPT_DIR_LIB?>/jodit/jodit.min.js"></script>
            <?php      
        }

        $a_label = array('[NAME]'        => $this->name, 
                         '[LABEL]'       => $this->label  );

        $a_input = array('[NAME]'        => $this->name, 
                         '[ID]'          => $this->name,
                         '[CLASS]'       => $this->classes?implode(' ',$this->classes):'',
                         '[READONLY]'    => $this->disable ? 'readonly="readonly"' : '' ,
                         '[PLACEHOLDER]' => $this->placeholder,
                         '[VALUE]'       => $this->value
                      );

        $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                           '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), $this->markup ) );

        echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT_TEXTAREA) ;


    }
  
}
