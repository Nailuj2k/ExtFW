<?php


/* * * * * * * * * * *
 *
 * fieldset
 *
 *
 * * * */

class fieldset {
  private $elements = array();
  public $legend;
  private $open = true;
  public $name;
  public $displaytype = 'tab'; //'default';
  public $translatable = false;

  public function __construct($name='myform',$legend=''){ 
      $this->setName( Str::sanitizeName($name) ); 
      $this->setLegend( $legend?ucfirst($legend):ucfirst($name) ); 
  }

  private function translate($string) {
    return ($this->translatable) ? t($string) : $string;
  }

  public function addElement($element){ 
    if($element instanceof formElement) 
        $this->elements[]=$element; 
  }
  
  public function setName($name){ 
      $this->name = $name; 
  }

  public function setLegend($legend){  
      $this->legend = $legend; 
  }
  
  public function setOpen($open){  
      $this->open = $open; 
  }
  
  protected function beginTag(){
     // echo '<fieldset id="fs_'.$this->name.'">';
     // echo '<div id="fs_div_'.$this->name.'" class="'.$this->name.'\">';
     if($this->displaytype=='footer') echo FORM_FIELDSET_BEGIN;  

  }

  protected function endTag(){
     // echo '</div></fieldset>'; 
     if($this->displaytype=='footer') echo FORM_FIELDSET_END;  
  }
  
  // display form
  public function render(){
    $header_rendered = false;
    $footer_rendered = false;
    $tabs_buttons = '';
    $ntabs = 0;
    
    $this->beginTag();
    
    foreach($this->elements as $element){  
        if($element->displaytype=='header')   $element->render();  
    }
    
    foreach($this->elements as $element){	      
      if($element->displaytype=='tab')  {
        $ntabs++;
        $tabs_buttons .= sprintf( TAB_TAB_TAB, $element->name, ($element->legend?$element->legend:$element->name) );
      }
    }
    
    foreach($this->elements as $element){	      
      if($element->displaytype=='tab')  {
        if(!$header_rendered){
          $header_rendered=true; 
          if($ntabs>1) echo sprintf(TAB_HEADER,$this->name,$tabs_buttons);
        }
        if($ntabs>1 && $element->displaytype=='tab') echo sprintf(TAB_TAB_BEGIN,$element->name);
        $element->render();    
        if($ntabs>1 && $element->displaytype=='tab') echo TAB_TAB_END;  
      } 
    }
    
    if($ntabs>1 && ($header_rendered&&!$footer_rendered)){$footer_rendered=true;  echo sprintf(TAB_FOOTER,$this->name);}
    
    foreach($this->elements as $element){	  if($element->displaytype=='default')   $element->render();  }
    foreach($this->elements as $element){	  if($element->displaytype=='float')     $element->render();  }
    foreach($this->elements as $element){	  if($element->displaytype=='footer')    $element->render();  }
    
    $this->endTag();
    
  }
  
}