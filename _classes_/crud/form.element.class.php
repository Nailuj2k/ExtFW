<?php


/* * * * * * * * * * *
 *
 * Abstract form element
 *
 *
 * * * */

abstract class formElement{
  public $label;
  public $id;
  public $name;
  public $value;
  public $default_value;
  public $required = false;
  public $disable  = false;
  public $displaytype = 'default';
  public $type;
  public $classes = array();
  public $translatable = false;
  
  public $minmax;
  public $showreset;
  public $tablename;
  public $before;
  public $after;
  public $array_filter;
  public $help;
  public $calculated;
  public $placeholder;
  
  public function translate($string) {
    return ($this->translatable) ? t($string) : $string;
  }

  // $element es un objeto de la clase Field
  // $value es el valor
  public function __construct($element) { 
    $this->type      = $element->type;
    $this->minmax    = $element->minmax;
    $this->name      = $element->fieldname ? $element->fieldname : (($element->name)?$element->name:'element-'.Crypt::random_str(5)); 
    $this->id        = $element->id ? $element->id : $this->name.'-'.Crypt::random_str(5);
    $this->classes   = $element->classes;
    $this->label     = $element->label;
    $this->value     = $element->value ? $element->value : $this->default_value;
    $this->showreset = $element->showreset;
    $this->required  = $element->required;
    $this->tablename = $element->tablename;
    $this->disable   = $element->readonly;

    $this->before = $element->before?$element->before:'';
    $this->after  = $element->after?$element->after:'';

    if ($element->classname)  $this->classes[]=$element->classname;
    if ($this->required)      $this->classes[]='required';
    $this->help      = $element->help;
    return $this;
  }

  //public function __toString(){ return $this->name; }

//  protected function renderLabel(){ echo '<label for="'.$this->name.'">'.$this->translate($this->label).'</label>';  }

  abstract protected function render();

}

