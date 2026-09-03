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
      ?>  
      <link rel="stylesheet" href="<?=SCRIPT_DIR_JS?>/jquery.toggle/css/toggles.css">
      <link rel="stylesheet" href="<?=SCRIPT_DIR_JS?>/jquery.toggle/css/themes/toggles-modern.css">

      <!-- ALL OF THE THEMES -->
      <!-- <link rel="stylesheet" href="<?=SCRIPT_DIR_JS?>/jquery.toggle/css/toggles-all.css"> -->

      <!-- ALL OF THE CSS AND THEMES IN ONE FILE -->
      <!-- <link rel="stylesheet" href="<?=SCRIPT_DIR_JS?>/jquery.toggle/css/toggles-full.css"> -->   
      <script src="<?=SCRIPT_DIR_JS?>/jquery.toggle/toggles.min.js" type="text/javascript"></script>
      <!-- MINIFIED JS - recommended for production -->
      <!-- <script src="<?=SCRIPT_DIR_JS?>/jquery.toggle/js/toggles.min.js" type="text/javascript"></script> -->      
      <?php 

      $a_label = array('[NAME]'        => $this->name, 
                       '[LABEL]'       => $this->label  );

      $a_input = array('[NAME]'        => 'toggle_'.$this->name, 
                       '[ID]'          => 'toggle_'.$this->name,
                       '[CLASS]'       => implode(' ',$this->classes),
                       '[READONLY]'    => $this->disable     ? 'readonly="readonly"'                    : '' ,
                       '[VALUE]'       => $this->value ? 'true' : 'false'
                    );

      $a_element = array('[LABEL]' => str_replace( array_keys($a_label), array_values($a_label), MARKUP_LABEL), 
                         '[INPUT]' => str_replace( array_keys($a_input), array_values($a_input), MARKUP_INPUT_CHECKBOX)  );

      echo str_replace( array_keys($a_element), array_values($a_element), MARKUP_ELEMENT) ;      


      ?>
      <input type="hidden" id="<?=$this->name?>" name="<?=$this->name?>" value="<?=$this->value?'1':'0'?>">
      <script type="text/javascript">
      $('#toggle_<?=$this->name?>').toggles(false);
      $('#toggle_<?=$this->name?>').on('toggle', function(e, active) {
          if (active) {
              $('#<?=$this->name?>').val("1");
          } else {
              $('#<?=$this->name?>').val("0");
         }
      });
      $('.toggle').toggles({text:{on:'<?=t('YES')?>',off:'<?=t('NO')?>'}});
      </script>
      <?php 

    }
  
}
