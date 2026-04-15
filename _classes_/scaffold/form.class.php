<?php

/* * * * * * * * * * *
 *
 * 
 *
 *
 * * * */
class dummyField {
    public $name;
    public $type;
}


// file:///C:/Users/tiosa/OneDrive/++JUX3.0++/++web_components++/enhanced-select/index-html-updated.html

/* * * * * * * * * * *
 *
 * fieldset
 *
 *
 * * * */
class fieldset {
  public $elements = array();
  public $legend;
  private $open = true;
  public $name;
  public $displaytype = 'default';
  public $priority = 1;
  public $tabs = false;
  public $showfileprogress = false;
  ////////////////////////////////////////////////FIX  set name from Legend
  public function __construct($name,$legend=''){ $this->setName($name); $this->setLegend($legend); }
  public $translatable = false;
  private function translate($string) {
    return ($this->translatable) ? t($string) : $string;
  }
  public function addElement($element){ 
    if($element->type=='file') $this->showfileprogress = true;
    if($this->parent_id_value) $element->parent_id_value = $this->parent_id_value;
    $this->elements[/*$element*/]=$element; 
  }
  public function setName($name){  $this->name = $name; }
  public function setLegend($legend){  $this->legend = $legend; } //?$legend:$this->name; }
  public function setOpen($open){  $this->open = $open; }
  private function open2str(){return ($this->open) ? '' : 'style="display:none;"' ;}
  
  protected function beginTag(){
    echo "<fieldset id=\"fs_{$this->name}\">";
    echo "<div id=\"fs_div_{$this->name}\" class=\"{$this->name}\">";
    if($this->displaytype=='float') echo '<legend><a onclick="$(\'#fs_div_'.$this->name.'>div\').slideToggle(\'slow\')">'.$this->translate($this->legend).'</a></legend><div>';
  }

  protected function endTag(){
    if($this->displaytype=='float') echo '</div>';
    echo '</div></fieldset>'; 
  }
  
  // display form
  public function render(){
    $header_rendered = false;
    $footer_rendered = false;
    $tabs_buttons = '';
    $ntabs = 0;
    $this->beginTag();
    foreach($this->elements as $element){  if($element->displaytype=='header')    $element->render();   }
    foreach($this->elements as $element){	      
      if($element->displaytype=='tab')  {
        $ntabs++;
        $tabs_buttons .= sprintf(TAB_TAB_TAB,$element->name,/*$element->name,*/($element->legend?$element->legend:$element->name));
      }//else if ($element->child){
        foreach($this->elements as $e){	      
           if($e->parent==$element->name){ 
             $element->element = $e;            ///////////////////////
           }
        }
      //}
    }
    foreach($this->elements as $element){	      
      if($element->displaytype=='tab')  {
        if(!$header_rendered){
          $header_rendered=true; 
          if($ntabs>1) echo sprintf(TAB_HEADER,$this->name,$tabs_buttons);
        }
        if($ntabs>1 && $element->displaytype=='tab') echo sprintf(TAB_TAB_BEGIN,$element->name);

        $element->render();   

        foreach($this->elements as $e){	      
           if($e->displaytype=='inline' && $e->parent==$element->name){ 
             $e->render();
           }
        }
        
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

/* * * * * * * * * * *
 *
 * FORM
 * static text2arrayValues($text) -> array
 * public addElement($element)    // $elment must be fieldset or form element
 * public render($submit_activated = false)
 *
 * * * */
class FORM extends fieldset{
  public $elements = array();
  private $action; // = $_SERVER['PHP_SELF'];
  public $displaytype = 'form';
  private $method = 'post';
  public $classname = false; //'exfw_form';
  public $id = false;
  public $showfileprogress = false;
  public $buttons;
  protected function beginTag(){
    if(!$this->id) $this->id = $this->name;
    $tag  = '<form accept-charset="utf-8"  enctype="multipart/form-data" enctype="multipart/form-data"';
    if($this->id) $tag .= ' id="form_'.$this->id.'"';
    if($this->classname) $tag .= ' class="'.$this->classname.'"';
    $tag .= 'name="'.$this->name.'" action="'.$this->action.'" method="'.$this->method.'">'; 
    echo $tag;  
    // echo '[FORM]';
  }
  protected function endTag(){
    // echo '[/FORM]';
    echo '</form>';

  }
  //public function __construct($name){}
  public function setAction($action){  $this->action = $action; }

  public static function text2arrayValues($text){
    $result = array();
    $rows = explode("\n",$text??'');
    foreach ($rows as $row) {
      $value = explode(',',$row);
      $result[$value[0]] = $value[1];
    }
    return  $result;
  }
  
  public function addElement($element){
    if($element->type=='file'||$element->showfileprogress) $this->showfileprogress = true;
    parent::addElement($element); 
  }
  
  public function render($submit_activated = false){
    $field = new dummyField();
    $field->name = 'formkey';
    parent::addElement(new formHidden($field,$this->name));  
    //echo '<div class="datatable-form">'; 
    parent::render();
    $this->renderAJAX($submit_activated);
    //echo '</div>';
  }


  private function renderAJAX($submit_activated = false) {
    if($this->showfileprogress){
    ?>
    <style>
    .fileprogress_progress { position:relative; width:400px; border: 1px solid #ddd; padding: 1px; border-radius: 3px; display:none;}
    .fileprogress_bar { background-color: #4594D3; width:0%; height:20px; border-radius: 2px; }
    .fileprogress_percent { position:absolute; display:inline-block; top:3px; left:48%; }
    .fileprogress_status { position:absolute; display:inline-block; top:3px; left:48%; }
    </style>
    <div class="fileprogress_progress">
      <div class="fileprogress_bar"></div >
      <div class="fileprogress_percent">0%</div >
      <div id="fileprogress_status"></div>
    </div>
    <?php }?>
    <div style="display:block;border:0px solid red;" class="datatable_result" id="result_<?=$this->id?>"><!--Name: <?=$this->name?><br />Action: <?=$this->action?>--></div>
    <script type="text/javascript">

          //ready(
             FORM_prepare('<?=$this->name?>','<?=$_SESSION['token']?>',<?=($this->showfileprogress)?'true':'false'?>,<?=($submit_activated)?'true':'false'?>);
          //)

    </script>
    <?php 
  }
  
}



/* * * * * * * * * * *
 *
 * form
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
    public $disable = false;
    public $displaytype = 'default';
    public $type;

    public $element;

    public $minmax;
    public $parent;
    public $showreset;
    public $tablename;

    public $values;
    public $value_end;
    public $parent_source_sql;
    public $parent_allow_null;
    public $child_key;
    public $child_source_table;
    public $child_source_id;
    public $child_source_name;

    public $parent_id = true;
    
    public $css_id;
    //public $size;
    public $watermark;
    public $values_visibles;
    public $html;

    public $action_if_exists_disabled;
    public $action_if_exists;
    public $javascript;
    public $filtrable;
    public $placeholder;
    public $textafter;
    public $height;
    public $calculated;

    public $readonly;
    public $colorize;

    public $child_ajax_url;
    public $child_fieldname;
    public $child_source_sql;
    public $classes;
    public $styles;
    public $image_editor;
    public $image_upload_url;
    public $image_width;
    public $image_height;
    public $accepted_extensions;
    public $prefix_filename;
    public $translatable = false;
    public $length;
    public $pk;
    public $multiselect;
    public $datepicker;
    public $parent_fieldname;
    public $html_before;
    public $langs;
    //public $translation;
    public $html_after;
    public $child_allow_null=false;
    public $child_clear_text='--';

  public function translate($string) {
    return ($this->translatable) ? t($string) : $string;
  }
  public function __construct($element,$value=false) { 
    $this->id    = $element->parent_id_value;
    $this->type  = $element->type;
    $this->minmax= $element->minmax;
    $this->name  = ($element->fieldname) ? $element->fieldname : (($element->name)?$element->name:'unnamed'); 
    $this->label = $element->label;
    $this->displaytype = $element->displaytype ? $element->displaytype : 'default'; 
    $this->parent = $element->parent; 
    $this->value = $value ? $value : ($element->value?$element->value:($element->default_value?$element->default_value:false));
    $this->showreset = $element->showreset;

  }
  //public function __toString(){ return $this->name; }
  protected function renderLabel(){ echo '<label for="'.$this->name.'">'.$this->translate($this->label).'</label>';  }
  abstract protected function render();
}

/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formElementHtml extends formElement{
  public $html;
  public function __construct($element=false,$value=false) { 
      if($value) $this->html = $value;
      else if($element->html) $this->html = $element->html;
  }
  public function render(){
    echo $this->html;
  }	
}

/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formInput extends formElement{
  private $size;
  private $maxsize;
  public $javascript = false; 
  public function __construct($element,$value=false) {
    if($element->type=='datetime' && !$value) $value = date(DATE_FORMAT." H:i");
    $this->default_value = $element->default_value;
    parent::__construct($element,$value);
    if (!$this->value) {
      if      ($element->type=='int'||$element->type=='bigint')$this->value = '0';     //ADD 20131103
      else if ($element->type=='decimal')                      $this->value = '0.00';  //ADD 20131103
     //else if ($element->type=='unixtime')                     $this->value = '';      //ADD 20131103
      else                                                     $this->value = '';      //ADD 20131103
    }else{
        if ($element->type=='unixtime') $this->value = date(DATETIME_FORMAT,$this->value); //MOD 20131103
    }

    $this->type  = ($element->type=='int'||$element->type=='bigint'||$element->type=='unixtime'||$element->type=='varchar'||$element->type=='decimal'|| $element->type=='datetime'||$element->type=='progress')
                    ? 'text' 
                    : $element->type ;
    //$this->name = $element->fieldname; //FIX
    $this->label = $element->label;
    $this->disable = $element->readonly;
    $this->calculated = $element->calculated; //ADD 20131031
    $this->required = $element->required;
    if($element->css_id) $this->css_id = $element->css_id;
    $this->placeholder = $element->placeholder;
    $this->tablename = $element->tablename;
    $this->value_end = ($element->value_end) ? $element->value_end : '';
    if( $element->html_before)$this->html_before= $element->html_before;    //MOD 20140619
    if( $element->html_after) $this->html_after = $element->html_after;     //MOD 20140619
    if( $element->textafter)  $this->textafter  = $element->textafter;
    if( $element->textbefore) $this->textbefore = $element->textbefore;
    if( $element->javascript) $this->javascript = $element->javascript;

    if     ($element->len)  $this->maxsize = $element->len;
    if     ($element->size) $this->size = $element->size;
    else if($element->len)  $this->size = $element->len;
    if     ($this->size>80) $this->size = 50;
    
  }
  public function setType($type){
   if(preg_match("/^(text|password|hidden|submit|reset|button|image)$/",$type)) $this->type = $type;
                                                                   else throw new Exception('Invalid type: '.$type);
  }  
  public function render(){
    if($this->html_before) echo $this->html_before;
    $horizontal = (preg_match("/^(text|password)$/",$this->type));
    if($this->parent) $horizontal = false;

    if($horizontal) echo '<div id="tab-'.($this->css_id?$this->css_id:$this->name).'" class="control-group">';

    echo ($this->displaytype=='inline')?'<span class="input-inline">':'';

    if($this->type!='hidden'/* && $this->displaytype!='inline'*/) if($this->label) $this->renderLabel();
    $class='';                                                                 //MOD 20131031
    if      ($this->type=='submit') $class.='btn btn-info btn-large submit';
    else if ($this->type=='reset')  $class.='btn btn-danger btn-large reset';
    else if ($this->disable/*||$this->calculated*/)   $class .='uneditable-input'; //     $class ='disabled class="uneditable-input"';   //MOD 20131031

    if($this->required)       $class .=' required';                           //MOD 20131031
    if($this->placeholder)     $placeholder  = ' placeholder = "'.$this->placeholder.'" ';
    if($this->size)    $strlen  = ' size = "'.$this->size.'" ';
    if($this->maxsize) $strlen .= ' maxlength = "'.$this->maxsize.'" ';
    if($horizontal) echo '<div class="controls">';
    if ($this->disable/*||$this->calculated*/)  $disabled =' readonly="readonly"'; //  $disabled =' disabled ';           //MOD 20131031
    if ($class) $class='class="'.$class.'"';                                    //MOD 20131031
    if($this->textbefore) echo '<span class="text-before">'.$this->textbefore.'</span>';
    if($this->minmax) echo 'Desde ';
    echo '<input '.$class.' '.$disabled.' type="'.$this->type.'" id="'.($this->css_id?$this->css_id:$this->name).'" name="'.$this->name.'" '.$strlen.$extra.$placeholder.' value="'.$this->value.'" />';  //MOD 20131031
    if($this->minmax) 
    echo ' hasta <input '.$class.' '.$disabled.' type="'.$this->type.'" id="'.($this->css_id?$this->css_id:$this->name).'_end" name="'.$this->name.'_end" '.$strlen.$extra.$placeholder.' value="'.$this->value_end.'" />';  //MOD 20131031
    //if ($this->type=='file') '<input id="'.$this->name.'_file" name="'.$this->name.'_file" type="file">';'
    if($this->textafter) echo '<span class="text-after">'.$this->textafter.'</span>';
    if($this->showreset) echo '<a class="input-icon-reset"><i class="fa fa-remove"> </i></a>';
    if($this->element) $this->element->render();
    if($horizontal) echo '</div>'; 
    echo ($this->displaytype=='inline')?'</span>':'';
    if($horizontal) echo '</div>';
    if($this->javascript||$this->parent){
    }
    if($this->html_after) echo $this->html_after;
  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formTextarea extends formElement{
  
  public $collapsed;
  public $wysiwyg;

  public function __construct($element,$value=false) {
    $this->default_value= $element->default_value;
    $this->tablename    = $element->tablename;
    $this->collapsed    = $element->collapsed;
    $this->wysiwyg      = $element->wysiwyg===true;
    $this->readonly     = $element->readonly;
    $this->height       = $element->height ? $element->height : 450;
    $this->html_before  = $element->html_before;    //MOD 20140619
    $this->html_after   = $element->html_after;     //MOD 20140619
    $this->placeholder  = $element->placeholder;
    parent::__construct($element,$value);
  }
  
  public function renderLabel2(){ 
    echo "<label style=\"/*float:right;*/cursor:pointer;\" for=\"{$this->name}\"><a onclick=\"$('.textarea-container').toggle('slow');\">Mostrar/Ocultar {$this->label}</a></label>";
  }

  
  public function render(){
   if ($this->readonly) $this->renderRO(); else $this->renderRW();
  }
  
  public function renderRO(){
    if($this->html_before) echo $this->html_before;
    echo '<div id="tab-'.$this->name.'" class="control-group textarea  disabled-textarea clearfix">';  //MOD 20140619 add id
    if($this->type!='hidden')if($this->label) $this->renderLabel();
    echo '<div class="controls">';
    echo htmlentities($this->value);
    echo '</div>';
    echo '</div>';
    if($this->html_after) echo $this->html_after;
  }
  
  public function renderRW(){
    if($this->html_before) echo $this->html_before;
      if( $this->wysiwyg && WYSIWYG_EDITOR=='medium') {
        $style = 'style ="position:relative;max-height:500px;overflow:auto;"';
        $stylet = 'background-color:white; border: 1px solid #8b8b8b;padding:4px;';
      }else if( $this->wysiwyg) {
        ?>
        <!--
    .st-tabs .control-group.textarea{min-height: 280px; height:280px;}
    .st-tabs .control-group.textarea textarea{min-height: 280px;}
    .r-tabs>.control-group.textarea{min-height: 290px; }
    .r-tabs>.control-group.textarea textarea{min-height: 280px;/* border:none;*/
         -->
         <?php
      }else{

      }
      echo '<div '.$style.' id="tab-'.$this->name.'" class="control-group textarea'.((WYSIWYG_EDITOR && $this->wysiwyg)?'-wysiwyg':'').'">';
      if     ($this->label&&$this->collapsed ) $this->renderLabel2();
      else if($this->label)                    $this->renderLabel();

      if( $this->wysiwyg && WYSIWYG_EDITOR=='medium') echo '<a class="btn btn-mini" id="btn_medium_show_source_'.$this->name.'" style="z-index:10;position:absolute;top:8px;right:12px;">Show source</a>';
      echo '<div class="controls">';
      echo '<div class="textarea-container" '.(($this->collapsed) ? 'style="display:none;overflow:hidden;'.$stylet.'"' : 'style="'.$stylet.'"').'>';
      if (WYSIWYG_EDITOR && $this->wysiwyg) {
        include(SCRIPT_DIR_CLASSES.'/scaffold/editor/'.WYSIWYG_EDITOR.'/editor_before.php'); 
        if     (WYSIWYG_EDITOR=='quill')  echo '<div id="'.$this->name.'" name="'.$this->name.'">'.$this->value.'</div>';
        else if(WYSIWYG_EDITOR=='medium') echo '<textarea  class="js-st-instance" id="'.$this->name.'" name="'.$this->name.'">'.$this->value.'</textarea>';
        else if(WYSIWYG_EDITOR=='simple') echo '<textarea  id="output_'.$this->name.'" name="'.$this->name.'">'.$this->value.'</textarea>';
        else                              echo '<textarea id="'.$this->name.'" name="'.$this->name.'">'.$this->value.'</textarea>';
      }  else echo '<textarea id="'.$this->name.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'">'.htmlentities($this->value).'</textarea>';
      if(WYSIWYG_EDITOR && $this->wysiwyg) include(SCRIPT_DIR_CLASSES.'/scaffold/editor/'.WYSIWYG_EDITOR.'/editor_after.php'); 
      echo '</div>';

      echo '</div>';
      echo '</div>'; 
      ?>
      <script type="text/javascript">
      <?php if(WYSIWYG_EDITOR && $this->wysiwyg) {?>
          //ready(function() { 

            var _ID_ = '<?=$this->id?>';
            var _TB_NAME_ = '<?=$this->tablename?>';
            if (typeof(module_name) != "undefined") var _MODULE_ = module_name;
            var _AJAX_URL_ = `page/ajax/id=${_ID_}/op=files-gallery`;
            init_editor('#<?=$this->name?>');

          //});
      <?php } ?>

      /***MOVE JS
        $(document).ready(function(e) { 
          $('#<?=$this->name?>').change( function(){ if(onChange)  onChange( '<?=$this->tablename?>', '<?=$this->name?>',0,$(this).attr('value') ); })
                                .keydown(function(){ if(onKeyDown) onKeyDown('<?=$this->tablename?>', $(this).attr('value'), e ); });
        });
      MOVE JS***/
      </script>
      <?php 
    if($this->html_after) echo $this->html_after;
  }
}



/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */
  
class formInputProgress extends formInput{
  // public $default_value = '0000-00-00'; // date("Y-m-d"); //
  public $skin = 'none'; // 'none', 'slider'
  public static $default_skin = 'none'; // 'none', 'slider'
  public function __construct($element,$value=false) {
    $element->type = 'text';
    //$element->default_value = '01-08-2012';
    $this->default_value = ($element->default_value)?$element->default_value:'0';
    $this->value = ($value) ? $value : $this->default_value;
    parent::__construct($element,$this->value);
    $this->skin = ($element->skin) ? $element->skin : self::$default_skin;
    $this->disable = $element->readonly;
    $this->classname = $element->classname;
    $this->readonly = $element->readonly;
    $this->min = ($element->min) ? $element->min : '0';
    $this->max = ($element->max) ? $element->max : '100';
  }

  public function render(){
   if ($this->readonly) $this->renderRO(); else $this->renderRW();
  }
  
  public function renderRW(){
    // https://www.google.es/search?client=firefox-b&dcr=0&ei=RGg6WvHSIIG6UK_xnqAL&q=css+form+range+slider&oq=form+css+slider&gs_l=psy-ab.1.3.0i8i30k1l4.1712.1712.0.6431.1.1.0.0.0.0.234.234.2-1.1.0....0...1c..64.psy-ab..0.1.234....0.hF24XSucvBs
    // https://codepen.io/seanstopnik/pen/CeLqA
   // parent::render();

    $class='range blue';                                                             
    if (!$this->value) $this->value=$this->min;
    if ($this->required)   $class .=' required';
    if ($this->required)   $class .=' required';                         
    if ($this->disable)    $class .=' disabled uneditable-input'; //   
    if ($this->disable)    $disabled =' readonly="readonly"'; 
    echo '<div class="control-group slider-skin-'.$this->skin.'">';
    if($this->label) $this->renderLabel();   
    echo '  <div class="controls">';
    echo '    <div class="range-slider">';
    echo '      <input id="'.$this->name.'" name="'.$this->name.'" '.$disabled.' class="'.$this->name.'-slider__range range-slider__range '.$class.'" type="range" value="'.$this->value.'" min="'.$this->min.'" max="'.$this->max.'">';
    echo '      <span id="'.$this->name.'-slider__value" class="range-slider__value">'.$this->value.'</span>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
    ?>
    <script type="text/javascript">
      /***MOVE JS
      $(document).ready(function(e) { 
        ////https://codepen.io/egrucza/pen/LEoOQZ?page=23
        ////https://codepen.io/tag/range/25/#
        var rangeSlider_init_<?=$this->name?> = function(){
             var v = $('#<?=$this->name?>').val();
             var min = $('#<?=$this->name?>').attr('min');
             var max = $('#<?=$this->name?>').attr('max');
             var v2 = Interpolar_Y(2,0,10,min,max),
                 v4 = Interpolar_Y(4,0,10,min,max),
                 v6 = Interpolar_Y(6,0,10,min,max);
              //console.log("<?=$this->name?>.val() => "+v+" min:"+min+" max:"+max+" v2:"+v2+" v4:"+v4+" v6:"+v6);
              //Change slide thumb color on way up
              if (v > v2) {  $('#<?=$this->name?>').addClass('ltpurple');   }
              if (v > v4) {  $('#<?=$this->name?>').addClass('purple');     }
              if (v > v6) {  $('#<?=$this->name?>').addClass('pink');       }
              //Change slide thumb color on way down
              if (v < v2) {  $('#<?=$this->name?>').removeClass('ltpurple');}
              if (v < v4) {  $('#<?=$this->name?>').removeClass('purple');  }
              if (v < v6) {  $('#<?=$this->name?>').removeClass('pink');    }
        }
        var rangeSlider_<?=$this->name?> = function(){
             var <?=$this->name?>_slider = $('#<?=$this->name?>'),
                 <?=$this->name?>_range = $('#<?=$this->name?>'), //-slider__range'),
                 <?=$this->name?>_value = $('#<?=$this->name?>-slider__value');
                 $(this).html($(this).prev().attr('value'));
             <?=$this->name?>_range.on('input', function(){
                 $(this).next(<?=$this->name?>_value).html(this.value);
             });
             rangeSlider_init_<?=$this->name?>();
        };
        rangeSlider_<?=$this->name?>();
        <?php if ($this->skin=='gradient') { ?>
        var <?=$this->name?>_inputRange = $('#<?=$this->name?>'), //document.getElementsByClassName('range')[0],
            <?=$this->name?>_maxValue = $('#<?=$this->name?>').attr('max'),  //100, // the higher the smoother when dragging
            <?=$this->name?>_speed = 5,
            <?=$this->name?>_currValue,
            <?=$this->name?>_rafID;
          // set min/max value
          //<?=$this->name?>_inputRange.min = $('#<?=$this->name?>').attr('min');  //;
          //<?=$this->name?>_inputRange.max = <?=$this->name?>_maxValue;
          // handle successful unlock
          function <?=$this->name?>_successHandler() {  console.log('<?=$this->name?>: Unlocked');   };
          $('body').on('mousedown mousestart',$('#<?=$this->name?>'),function(){            
              <?=$this->name?>_currValue = +$('#<?=$this->name?>').val();           // set to desired value
          });
          $('body').on('mouseup touchend',$('#<?=$this->name?>'),function(){
              <?=$this->name?>_currValue = +$('#<?=$this->name?>').val();// +$(this).val();
              console.log("<?=$this->name?>_currValue: "+<?=$this->name?>_currValue);
              if(<?=$this->name?>_currValue >= <?=$this->name?>_maxValue) {  <?=$this->name?>_successHandler(); }
          });
          $('body').on('input',$('#<?=$this->name?>'),function(){ rangeSlider_init_<?=$this->name?>();  });
          <?php  } ?>
          <?php  if($this->javascript){ echo $this->javascript; } ?>
      });
      MOVE JS***/
    </script>
    <style>

    </style>
    <?php 

  }

  public function renderRO(){
    //   http://virtualkanban.net/ 
    echo '<div class="control-group">';
    if($this->label) $this->renderLabel();
    echo '<div class="controls">';
    //echo '<progress id="progress_bar_'.$this->name.'" name="'.$this->name.'"  max="100" class="pbar" value="'.$this->value.'" style="vertical-align:bottom;"></progress>';
    echo '<div style="display:inline-block;" id="progress_bar_'.$this->name.'" class="progress '.$this->classname.'"><div class="bar" style="width: '.$this->value.'%"></div></div>';
    echo '</div>';
    echo '</div>';
  }

}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formSubmit extends formInput{
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formButton extends formElement{
  public $class = 'btn'; // btn-large'; 
  public $disabled = false;
  public $javascript = "alert('click!')"; 
  public function __construct($element,$value=false) {
    parent::__construct($element,$value);
    if ($element->class) $this->class .=' '.$element->class; 
    if( $element->javascript) $this->javascript = $element->javascript;
    $this->tablename = $element->tablename;
    $this->disabled = $element->disabled ?? false;
  }
  public function render(){
    // Añadir clases especiales para el estilo y la transición cuando está desactivado
    $btnClass = $this->class;
    if ($this->disabled) {
      $btnClass .= ' btn-inactive';
    }
    
    echo '<button id="form_'.$this->tablename.'_'.$this->name.'" '.($this->disabled?'disabled ':'').'class="'.$btnClass.'" type="button" name="'.$this->name.'">'.$this->value.'</button>';
    ?>
    <script type="text/javascript">
       /***MOVE JS **/
      $(document).ready(function(e) { 
        $('#form_<?=$this->tablename?>_<?=$this->name?>').click( function(e){ 
          <?php  echo $this->javascript; ?>
        });
      });
      /** MOVE JS***/
    </script>
    <?php 
  }	
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formHidden extends formInput{
  public function __construct($element,$value=false) {
    $element->type = 'hidden';
    parent::__construct($element,$value);
  }
}


/*
#33b075
#d8dc50 
#dc934b 
#c44145 
#9018c8 
#566cc7
*/
/*
    echo '<div class="control-group">';
  LABEL
    echo '<div class="controls">';
  FIELD
    echo '</div>';
    echo '</div>'; 
*/


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class NEW_formInputColor extends formInput{
  public $default_value = '#EFEFEF'; 
  public function __construct($element,$value=false) {
    $element->type = 'text';
    if(!$value) $value = '#EFEFEF';
    parent::__construct($element,$value);
    $this->disable = $element->readonly;
  }
  public function render(){
 
    echo '<div class="control-group">';

    if($this->label) $this->renderLabel();
    if ($this->disable)        $disabled ='disabled '; 
    ?>

    <div class="controls">
        <input type="color" <?=$disabled.$class?> 
           name="<?=$this->name?>" 
           id="<?=$this->name?>" 
           NOstyle="background-image: -moz-linear-gradient(rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0) 95%);"
           value="<?=$this->value?>">
      </div>
    </div> 

    <?php 
  }
}

class formInputColor extends formInput{
  public $default_value = '#EFEFEF'; 
  public function __construct($element,$value=false) {
    $element->type = 'text';
    if(!$value) $value = '#EFEFEF';
    parent::__construct($element,$value);
    $this->disable = $element->readonly;
  }
  public function render(){
    global $color_script_loaded;
    if(!$color_script_loaded){
      include('forms/colors_dialog_simple.php'); 
      //include('forms/colors_dialog_tone.php'); 
      ?>
      <script type="text/javascript">
      /***MOVE JS*/
      function rgb2hex(rgb){
        rgb=rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        function hex(x){return("0"+parseInt(x).toString(16)).slice(-2);}
        return "#"+hex(rgb[1])+hex(rgb[2])+hex(rgb[3]);
        }
        $('.grid-colors div').click(function(){ 
          $('#<?=$this->name?>').val(rgb2hex($(this).css('background-color'))).css('background-color',rgb2hex($(this).css('background-color'))); 
        });
       // $('.grid-colors-tone div').click(function(){ 
       //  $('#<?=$this->name?>').val(rgb2hex($(this).css('background-color'))).css('background-color',rgb2hex($(this).css('background-color')));
       // });
        /*MOVE JS***/ 
      </script>
      <?php 
      $color_script_loaded = true;
    }
 
    echo '<div class="control-group">';

    if($this->label) $this->renderLabel();
    if ($this->disable)        $disabled ='disabled '; 
    ?>

    <div class="controls">
     <!--      <div id="colorpicker<?=$this->name?>" style="display:inline-block;position:absolute;margin-left:100px; background-color:#FFFFFF;border: 2px solid #000;/*z-index:100;*/"></div>-->
        <input type="TEXT" <?=$disabled.$class?> 
           name="<?=$this->name?>" 
           id="<?=$this->name?>" 
           style="/*background-image: -moz-linear-gradient(rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0) 95%);*/border:1px solid #777777;background-color:<?=$this->value?>;"
           value="<?=$this->value?>" size="10" 
           onfocus="$('#colorpicker<?=$this->name?>').show();$('.grid-colors').hide()" 
           onblur="$('#colorpicker<?=$this->name?>').hide()">
           <img style="display:inline;border:1px solid #444;height:24px;vertical-align:bottom;cursor:pointer;" 
                src="_images_/famfam/dialog-color.png" 
                alt="Select color" 
                onclick="$('.grid-colors-dialog').toggle()">
  <!----<a class="btn-color" onclick="$('.grid-colors').hide();$('.grid-colors-tone').toggle()"><img src="_images_/famfam/dialog-color.png" alt="Select color"></a>---->
      </div>
    </div> 
    <?php 
  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formInputDate extends formInput{
   // public $default_value = '0000-00-00'; // date("Y-m-d"); //
  public function __construct($element,$value=false) {
    $element->type = 'text';
    //$element->default_value = '01-08-2012';
    $this->default_value = ($element->default_value)?$element->default_value:($element->allowNull?'':date(DATE_FORMAT));  //MOD 20140522
    $this->value = ($value) ? $value : $this->default_value;
    $this->value_end = ($element->value_end) ? $element->value_end : '';
    parent::__construct($element,$this->value);
    $this->disable = $element->readonly;
  }
  public function render(){
   echo '<div class="control-group">';
    if($this->label) $this->renderLabel();
    if ($this->disable)        $disabled ='disabled '; //class="uneditable-input"';
    $class = 'class="';                  
    if($this->required) $class .= 'required';
    $class .= '"';                  
    echo '<div class="controls">';
   //    if ($this->disable) echo '<span>['.$this->value.']</span>';
   //    else{
      if($this->minmax) echo 'Desde ';
      echo '<input type="date" '.$disabled.$class.' id="'.$this->name.'" name="'.$this->name.'"  autocomplete="off" value="'.$this->value.'" size="20">';
      if($this->minmax) echo '  hasta <input type="date" '.$disabled.$class.' id="'.$this->name.'_end" autocomplete="off" name="'.$this->name.'_end" value="'.$this->value_end.'" size="20">';
      if($this->showreset) echo '<a class="input-icon-reset"><i class="fa fa-remove"> </i></a>';
  
     if($this->textafter) echo '<span class="text-after">'.$this->textafter.'</span>';


   //    }
    echo '</div>';
    echo '</div>';
    ?>
    <script type="text/javascript">
       /***MOVE JS
        $(document).ready(function(e) { 
            $('#<?=$this->name?>').change(function(){ if(onChange) onChange('<?=$this->tablename?>', '<?=$this->name?>',0,$(this).attr('value') ); })
                                  .keydown(function(){ if(onKeyDown) onKeyDown('<?=$this->tablename?>', $(this).attr('value'), e ); });
        });
       MOVE JS***/
    </script>
    <?php 
  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formInputDateTime extends formInput{
  // public $default_value = '0000-00-00'; // date("Y-m-d"); //
  public function __construct($element,$value=false) {
    $element->type = 'text';
    //$element->default_value = '01-08-2012';
    $this->default_value = $element->default_value; //($element->default_value)?$element->default_value:($element->allowNull?'':date(DATE_FORMAT));  //MOD 20140522
    $this->value = ($value) ? $value : $this->default_value;
    $this->value_end = ($element->value_end) ? $element->value_end : '';
    parent::__construct($element,$this->value);
    $this->disable = $element->readonly;
  }

  public function render(){
    echo '<div class="control-group">';
    if($this->label) $this->renderLabel();
    if ($this->disable)        $disabled ='disabled '; //class="uneditable-input"';
    $class = 'class="';                  
    if($this->required) $class .= 'required';
    $class .= '"';                  
    echo '<div class="controls">';
      if($this->minmax) echo 'Desde ';
      echo '<input type="datetime-local" '.$disabled.$class.' step="1" id="'.$this->name.'" name="'.$this->name.'"  autocomplete="off" value="'.$this->value.'" size="20">';
      if($this->minmax) echo '  hasta <input type="datetime-local" '.$disabled.$class.' step="1" id="'.$this->name.'_end" name="'.$this->name.'_end" autocomplete="off" value="'.$this->value_end.'" size="20">';
      if($this->showreset) echo '<a class="input-icon-reset"><i class="fa fa-remove"> </i></a>';
    echo '</div>';
    echo '</div>';

  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formInputTime extends formInput{
  
  public $default_value = '00:00'; // date('H:i');  //:s'); //
  private $_value;
  private $_interval; // = 60;
  private function roundedHour($h,$m){
     $h = intval($h);
     $m = intval($m);
     if($this->_interval==15){
         if      ($m <  8) {$m = '00';  }
         else if ($m < 23) {$m = '15';  }
         else if ($m < 38) {$m = '30';  }
         else if ($m < 53) {$m = '45'; $h = $h+1; }
         else              {$m = '00'; $h = $h+1; }
     }else if ($this->_interval==30){
         if      ($m < 38) {$m = '30';  }
         else              {$m = '00'; $h = $h+1; }
     }else{
         if      ($m < 38) {$m = '00';  }
         else              {$m = '00'; $h = $h+1; }
     }
     if ($h>23) $h=0;
     return sprintf("%02d:%02d", $h, $m);
  }

  private function roundedMinutes($min){
     $m = intval($min);
     if($this->_interval==15){
         if      ($m <  8) return '00';  
         else if ($m < 23) return '15';  
         else if ($m < 38) return '30';  
         else if ($m < 53) return '45';  
         else              return '00'; 
     }else if ($this->_interval==30){
         if      ($m < 38) return '30';  
         else              return '00'; 
     }else{
         return '00';
     }     
  }
  
  public function __construct($element,$value=false) {
    $element->type = 'text';
    if(!$value) $value = $this->roundedHour(date("H"),date("i"));
    $this->_value=$value;
    $hm = explode(':',$value);
    $value=$this->roundedHour($hm[0],$this->roundedMinutes($hm[1]));
    parent::__construct($element,$value);
    $this->disable = $element->readonly;
    $this->_interval = $element->interval && in_array($element->interval,[15,30,60])  ? $element->interval : 15;
  }

  public function render(){
    echo '<div class="control-group">';
    $this->renderLabel();
    if ($this->disable) $disabled ='disabled '; //class="uneditable-input"';
    $hour_values = array();
    for($t = $this->_interval; $t <= 1440; $t += $this->_interval){
      $hour = sprintf("%02d:%02d", floor($t/60)%24, $t%60);//.':00';
      $hour_values[$hour] = $hour;  
    }
    echo '<div class="controls">';
    echo '<select '.$disabled.'name="'.$this->name.'">';
    $yet = false;
    foreach ($hour_values as $k=>$v){ 
      if(!$yet && $k>$this->_value){
          echo '<option value="'.$this->_value.'" SELECTED>'.$this->_value.'</option>';
          $yet = true;  
      }
      if($k!=$this->_value)
      echo '<option value="'.$k.'">'.$v.'</option>';
    }
    echo '</select> <span style="color:#c9c9c9;"> '.sprintf("%02d:%02d", date("H"),date("i")).'</span>'; //.'Hora:'.$this->value;$this->_value   
    //print_r($this->values);
    echo '</div>';
    echo '</div>';
  }
  
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formSelect extends formElement{
  private $allowNull=false;
  private $clearValue='0';
  private $clearText='----';
  public $multiselect = false;
  public $javascript = false; 
  public function setValues($values){
    $this->values = (is_array($values)) ? $values : FORM::text2arrayValues($values);
  }
  
  public function __construct($element,$value=false) {
    //echo '<br />'.$label.'/'.$name.':'.$value.':';
    //print_r($values);
    if(!$value) $value = $element->default_value;
    parent::__construct($element,$value);
    $this->tablename  = $element->tablename;
    $this->allowNull  = $element->allowNull;
    $this->clearValue = ($element->clearValue)? $element->clearValue : '0';
    $this->clearText  = ($element->clearText) ? $element->clearText : '--' ;
    $this->label      = $element->label;
    $this->multiselect= $element->multiselect;
    $this->setValues( $element->values );  // ($element->values_all ? $element->values_all : $element->values) );
    $this->readonly   = $element->readonly;
    $this->required   = $element->required;
    $this->value_end = ($element->value_end) ? $element->value_end : '';

    $this->parent_fieldname   = $element->parent_fieldname;
    $this->parent_source_sql  = $element->parent_source_sql;
    $this->parent_allow_null  = $element->parent_allow_null!==false;
    $this->child_ajax_url     = $element->child_ajax_url;
    $this->child_fieldname    = $element->child_fieldname;
    $this->child_key          = $element->child_key ;
    $this->child_source_table = $element->child_source_table;
    $this->child_source_id    = $element->child_source_id;
    $this->child_source_name  = $element->child_source_name;
    $this->child_allow_null   = $element->child_allow_null??false;
    $this->child_clear_text   = $element->child_clear_text??'--';
    
   //$this->child_source_sql   = "SELECT [ID] AS ID, [NAME] AS NAME FROM [table] WHERE [KEY] = %s";
    $this->child_source_sql   = $element->child_source_sql;

    $this->required   = $element->required;

    if( $element->javascript) $this->javascript = $element->javascript;
    if( $element->textafter) $this->textafter = $element->textafter;
    if( $element->textbefore) $this->textbefore = $element->textbefore;
  }
  
  public function render(){
    if ($this->readonly) $this->renderRO(); else $this->renderRW();
    if($this->javascript){
      ?>
      <script type="text/javascript">
       /***MOVE JS
       $(document).ready(function(e) { 
         var e = $('#<?=$this->name?>');
         <?php  echo $this->javascript; ?>
       });
       MOVE JS***/
      </script>
      <?php 
    }
  }
  
  public function renderRO(){
    echo '<div class="control-group" style="clear:both;">';
    if($this->type!='hidden')if($this->label) $this->renderLabel();
    echo '<div class="controls">';
    if($this->textbefore) echo '<span class="text-before">'.$this->textbefore.'</span>';
    echo '<span  id="'.$this->name.'" class="disabled">'.$this->values[$this->value].'</span>';
    //if ($this->type=='file') '<input id="'.$this->name.'_file" name="'.$this->name.'_file" type="file">';'
    if($this->textafter) echo '<span class="text-after">'.$this->textafter.'</span>';
    echo '</div>';
    echo '</div>';
  }
  
  public function renderRW(){
    global $chosen_script_loaded;
    if($this->multiselect){
      if(!$chosen_script_loaded){
       /***MOVE JS
      ?>
      <script type="text/javascript" src="_js_/chosen/chosen.jquery.min.js"></script>
      <link rel="stylesheet" href="_js_/chosen/chosen.css" type="text/css" />
      <?php 
      $chosen_script_loaded = true;
      MOVE JS***/
     }
    }

    echo ($this->displaytype=='inline')?'<div class="select-inline" style="display:inline-block;margin-left:40px;">':'<div class="control-group">';

    if ($this->displaytype=='inline') echo '<span>'.$this->label.'</span>';
                                 else $this->renderLabel();

    if ($this->multiselect) $class = 'select-chosen';
    if ($this->readonly)    $class ='disabled'; //class="uneditable-input" ';
    if ($this->required)    $class .=' required';    
    //if ($this->displaytype!=='inline')
      echo '<div class="controls">';
    if($this->textbefore) echo '<span class="text-before">'.$this->textbefore.'</span>';

    if($this->minmax) echo 'Desde ';
    echo '<select class="'.$class.'" name="'.$this->name.'" id="'.$this->name.'"  '.($this->displaytype=='inline'?'inline...':'').'    >';
    if ($this->allowNull==true) {
      //$this->values = array_merge($this->values , array($this->clearValue=>$this->clearText));
      echo '<option value="'.$this->clearValue.'">'.$this->translate($this->clearText).'</option>';
    }
    
    $found = false;
    foreach ($this->values as $k=>$v){ 
      echo '<option value="'.$k.'"';
      if ($k==$this->value){ $found=true; echo ' SELECTED';}
      echo '>'.$this->translate($v).'</option>';
    }
    if(!$found&&$this->value) echo '<option value="'.$this->value.'" SELECTED>'.$this->value.' (Inactivo)</option>';
    echo '</select>';   

    if($this->minmax){ 
        echo ' hasta <select class="'.$class.'" name="'.$this->name.'_end" id="'.$this->name.'_end">';
        if ($this->allowNull==true) echo '<option value="'.$this->clearValue.'">'.$this->translate($this->clearText).'</option>';
        $found = false;
        foreach ($this->values as $k=>$v){ 
            echo '<option value="'.$k.'"';
            if ($k==$this->value_end){ $found=true; echo ' SELECTED';}
            echo '>'.$this->translate($v).'</option>';
        }
        if(!$found&&$this->value_end) echo '<option value="'.$this->value_end.'" SELECTED>'.$this->value_end.' (Inactivo)</option>';
        echo '</select>';   
    }

    if($this->textafter) echo '<span class="text-after">'.$this->textafter.'</span>';
    //if ($this->displaytype!=='inline')
    echo '</div>';
    echo '</div>';

    if($this->multiselect){?>
    <script type="text/javascript"> 
    /***MOVE JS
    $(".select-chosen").chosen();
    MOVE JS***/
    </script>
    <?php }
    //print_r($this->values);
    ?>
    <script type="text/javascript">


       ///////////////////////////document.addEventListener('DOMContentLoaded', function(){
          var el = document.getElementById('<?=$this->name?>');
          if (el) {
              el.onchange = function(e) {
                    console.log('CHANGE','#<?=$this->name?>');

                    if(onChange) onChange('<?=$this->tablename?>', '<?=$this->name?>',0,$(this).attr('value') ); 


                    <?php  if ($this->child_fieldname && $this->child_source_sql) { ?>
                    let child_cur_val = $(this).val();
                    if(!child_cur_val) return false;
                    let child_sql = "<?=$this->child_source_sql ?>"; 
                    let child_sel_value =  $("#<?=$this->child_fieldname?>").val() ? $("#<?=$this->child_fieldname?>").val() : "0";     

                    let child_ajax_url = '<?=$this->child_ajax_url??AJAX_URL.'/'.MODULE.'/ajax/op=list/xx'?>'; 

                    let _sql_list = child_ajax_url+'/'+module_name+'/ajax/op=list/sql='+child_sql+'/value='+child_cur_val+'/selected='+child_sel_value+'<?php if($this->child_allow_null===true) echo '/null=1/nullkey=0/nullvalue='.$this->child_clear_text; ?>';

                    console.log('FORM_SELECT','#<?=$this->child_fieldname?>',_sql_list);

                    $("#<?=$this->child_fieldname?>").load(_sql_list, {}, function(){    
                        
                        // console.log( 'CHANGE---------->','#<?=$this->child_fieldname?>')     
                        setTimeout(function(){ 
                            document.getElementById('<?=$this->child_fieldname?>').dispatchEvent(new Event('change'));
                        },100);

                    }); 

                  <?php  } ?>

              };
          }
        ///////////////////////////////////////////////});

          /////////////////////////////////

      /***MOVE JS******************/
      /*
      var _NAME_ = '<?=$this->name?>';
      var _TABLE_NAME_ = '<?=$this->tablename?>';
      var _child_fieldname_ = '<?=$this->child_fieldname?>';
      var _child_source_sql_ = '<?=$this->child_source_sql?>';
      var _parent_fieldname_ = '<?=$this->parent_fieldname?>'; 
      var _parent_source_sql_ = '<?=$this->parent_source_sql?>';
      var _parent_allow_null_ = '<?=$this->parent_allow_null?>';
      */
      
      <?php  if (1==2) { ?>

      $(function(){ 

        console.log('formSelect','<?=$this->name?>','<?=$this->tablename?>'); 
       
        $('#<?=$this->name?>').change(function(){ 
        
        // console.log('CHANGE','#<?=$this->name?>')

        if(onChange) onChange('<?=$this->tablename?>', '<?=$this->name?>',0,$(this).attr('value') ); 
           
            <?php  if ($this->child_fieldname && $this->child_source_sql) { ?>
            let child_cur_val = $(this).val();
            if(!child_cur_val) return false;
            let child_sql = "<?=$this->child_source_sql ?>"; 
            let child_sel_value =  $("#<?=$this->child_fieldname?>").val() ? $("#<?=$this->child_fieldname?>").val() : "0";            
            let child_ajax_url = '<?=$this->child_ajax_url??AJAX_URL.'/'.MODULE.'/ajax/op=list/xx'?>'; 
            let _sql_list = child_ajax_url+'/'+module_name+'/ajax/op=list/sql='+child_sql+'/value='+child_cur_val+'/selected='+child_sel_value+'<?php if($this->child_allow_null===true) echo '/null=1/nullkey=0/nullvalue=--'; ?>';
            // console.log('FORM_SELECT','#<?=$this->child_fieldname?>',_sql_list);
            $("#<?=$this->child_fieldname?>").load(_sql_list, {}, function(){    
                console.log( 'CHANGE---------->','#<?=$this->child_fieldname?>')     
                setTimeout(function(){ 
                    //$("#<?=$this->child_fieldname?>").change();
                    document.getElementById('<?=$this->child_fieldname?>').dispatchEvent(new Event('change'));
                },500);
            });             
           <?php  } ?>

        }).keydown(function(){ 
           if(onKeyDown) onKeyDown('<?=$this->tablename?>', $(this).attr('value'), e ); 
        });

        <?php  if ($this->name=='id_localidad') { ?>
          $('<input type="text" id="cp" placeholder="Buscar código postal" style="height:20px;padding:0px;" title="Escriba un Código postal y pulse Intro">')
            .insertAfter("#id_localidad")
            .keypress(function(e){
                code=e.keyCode?e.keyCode:e.which;
                if(code.toString()==13) {
                    e.preventDefault();
                    let cp = $(this).val();
                    if( /^([0-9]{5,})$/.test(cp) ) {
                        $(this).css('color','green');
                        let selected_localidad =  $("#id_localidad").val() ? $("#id_localidad").val() : "0";
                        $("#id_localidad").load(AJAX_URL+'/'+module_name+'/ajax/op=list/sql=localidad_from_cp/value='+cp+'/selected='+selected_localidad, {}, function(){ 
                            let selected_municipio =  $("#id_municipio").val() ? $("#id_municipio").val() : "0"; 
                            $("#id_municipio").load(AJAX_URL+'/'+module_name+'/ajax/op=list/sql=municipio_from_cp/value='+cp+'/selected='+selected_municipio, {}, function(){ 
                                let selected_provincia =  $("#id_provincia").val() ? $("#id_provincia").val() : "0"; 
                                $("#id_provincia").load(AJAX_URL+'/'+module_name+'/ajax/op=list/sql=provincia_from_cp/value='+cp+'/selected='+selected_provincia, {}, function(){  }); 
                            }); 
                        });
                    } else { 
                        $(this).css('color','red');
                    }
                }
            });
        <?php  } ?>

        <?php  if ($this->name=='ID_COUNTY') { ?>
          $('<input type="text" id="cp" placeholder="Buscar código postal" style="height:20px;padding:0px;margin-left:20px;" title="Escriba un Código postal y pulse Intro">')
            .insertAfter("#ID_COUNTY")
            .keypress(function(e){
                code=e.keyCode?e.keyCode:e.which;
                if(code.toString()==13) {
                    e.preventDefault();
                    let cp = $(this).val();
                    if( /^([0-9]{5,})$/.test(cp) ) {
                        $('#ZIP').val(cp); 
                        $(this).css('color','green');
                        let selected_localidad =  $("#ID_COUNTY").val() ? $("#ID_COUNTY").val() : "0";
                        $("#ID_COUNTY").load(AJAX_URL+'/'+module_name+'/ajax/op=list/sql=localidad_from_cp/value='+cp+'/selected='+selected_localidad, {}, function(){ 
                            let selected_municipio =  $("#ID_CITY").val() ? $("#ID_CITY").val() : "0"; 
                            $("#ID_CITY").load(AJAX_URL+'/'+module_name+'/ajax/op=list/sql=municipio_from_cp/value='+cp+'/selected='+selected_municipio, {}, function(){ 
                                let selected_provincia =  $("#ID_STATE").val() ? $("#ID_STATE").val() : "0"; 
                                $("#ID_STATE").load(AJAX_URL+'/'+module_name+'/ajax/op=list/sql=provincia_from_cp/value='+cp+'/selected='+selected_provincia, {}, function(){  }); 
                            }); 
                        });
                    } else { 
                        $(this).css('color','red');
                    }
                }
            });
        <?php  } ?>
        
        <?php  if ($this->parent_fieldname && $this->parent_source_sql) { ?>
           $('#<?=$this->parent_fieldname?>').change(function(){ 
              let parent_cur_val = $(this).val();
              if(!parent_cur_val) return false;
              let parent_sql = "<?=$this->parent_source_sql ?>"; 
              let parent_sel_value =  $("#<?=$this->name?>").val() ? $("#<?=$this->name?>").val() : "0"; 
              let _sql_list = AJAX_URL+'/'+module_name+'/ajax/op=list/sql='+parent_sql+'/value='+parent_cur_val+'/selected='+parent_sel_value+'<?php if($this->parent_allow_null===true) echo '/null=1/nullkey=0/nullvalue=--'; ?>';
              $("#<?=$this->name?>").load(_sql_list, {}, function(){ 
                 $("#<?=$this->name?>").change();
              }); 
           });
        <?php  } ?>

       //CHANGE $('#<?=$this->name?>').change();
        setTimeout(function(){ 
            document.getElementById('<?=$this->name?>').dispatchEvent(new Event('change'));
        },500);
      
      });

      <?php  } ?>

      /*******************MOVE JS***/
    </script>
    <?php 

  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

if       (CFG::$vars['db']['type']=='mysql')  {
    class BaseFormSelectDb {
        use MysqlConnection;   
    }
}else if (CFG::$vars['db']['type']=='sqlite') {
    class BaseFormSelectDb {
        use SQLiteConnection;   
    }
}else if (CFG::$vars['db']['type']=='oracle') {
    class BaseFormSelectDb {
        //use OracleConnection;   
    }
}

class formSelectDb extends BaseFormSelectDb { 
  // use MysqlConnection;   
  private $lookupTable;
  private $fieldKey;
  private $fieldKeyName;
  private $where='';
  public function __construct($element,$value=false){
    self::connect();
    $this->lookupTable  = $element->lookupTable;
    $this->fieldKey     = $element->fieldKey;
    $this->fieldKeyName = $element->fieldKeyName;
    $this->where        = $element->where;
    $sql  = 'SELECT '.$this->fieldKey.' AS k, '.$this->fieldKeyName.' AS v ';
    $sql .= 'FROM '.$this->lookupTable.' '.$this->where.' ORDER BY '.$this->fieldKeyName;
    $this->values = self::sqlQuery($sql);
    
    parent::__construct($element,$this->value);
  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formCheckbox extends formElement{

  public $javascript = false; 

  public function __construct($element,$value=false) {
    $this->default_value = $element->default_value;
    parent::__construct($element,$value);
    $this->readonly     = $element->readonly;
    $this->label = $element->label;
    if( $element->javascript) $this->javascript = $element->javascript;
    if( $element->textafter) $this->textafter = $element->textafter;
    if( $element->textbefore) $this->textbefore = $element->textbefore;
    if( $element->class_name) $this->class_name = $element->class_name;
    $this->required   = $element->required;
  }
   /*
    echo '<div class="control-group">';
    echo '<label>'.$this->label.'</label>';
    echo '<div class="controls">';
    echo '<input name="'.$this->name.'" type="checkbox" value="1" '.(($this->value)?'checked':'').'>';
    echo '</div>';
    echo '</div>'; 
   */
   /*
  public function render(){
   if ($this->readonly) $this->renderRO(); else $this->renderRW();
  }
  
  public function renderRO(){
    echo '<div class="control-group" style="clear:both;">';
    if($this->type!='hidden')if($this->label) $this->renderLabel();
    echo '<div class="controls">';
    echo '<input class="disabled" disabled name="'.$this->name.'" id="'.$this->name.'" type="checkbox" value="1" '.(($this->value)?'checked':'').'>';
    //if($this->textafter) echo $this->textafter;
    echo '</div>';
    echo '</div>';  
  }
  */
  public function render(){
    if($this->textbefore) echo $this->textbefore;
    echo '<div id="form_cb_'.$this->name.'" class="';
    if ($this->readonly)  echo ' checkbox-disabled '; else echo 'checkbox-enabled';
    if ($this->class_name) echo ' '.$this->class_name; else echo ' control-group';
    echo '">';
    echo '<label>'.$this->label.'</label>';
    if(!$this->class_name) echo '<div class="controls">';
    echo '<input name="'.$this->name.'" id="'.$this->name.'" ';
    if ($this->readonly) echo ' disabled ';
    if ($this->required) echo ' class="required"';    
    echo ' type="checkbox" value="1" '.(($this->value)?'checked':'').'>';  
    if($this->element) echo $this->element->render();
     if($this->textafter) echo '<span class="textafter">'.$this->textafter.'</span>';
   if(!$this->class_name) echo '</div>';
    echo '</div>'; 
    if($this->javascript){
      ?>
      <script type="text/javascript">
       /***MOVE JS
       $(document).ready(function(e) { 
         var e = $('#<?=$this->name?>');
         <?php  echo $this->javascript; ?>
       });
      MOVE JS***/
      </script>
      <?php 
    }
  }
  
}

/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formRadio extends formElement{
  public function render(){
    echo '<select radio>';
  }
}


/* * * * * * * * * * *
 *
 * form
 *
 *
 * * * */

class formFile2 extends formInput{
  
  public function __construct($element,$value=false) {
    parent::__construct($element,$value);
    $this->type = 'file';
    $this->label = $element->label;
  }
  public function render(){

    echo '<div class="control-group">';
    echo '<label>'.$this->label.'</label>';
    echo '<div class="controls">';
    echo '<input id="'.$this->name.'" name="'.$this->name.'" type="file">';
    echo '</div>';
    echo '</div>'; 

  }
  
}


class formFile extends formInput{
  
  public $id_name;
  public $uploaddir;
  public $crop;
  public $crop_width;
  public $crop_height;
  public $crop_upload_url;
  public $crop_resize_url;
  public $crop_delete_url;
  public $crop_trim_button;
  public $crop_resize_button;
  public $crop_urlpath;
  public $parent_id_value;
  public $crop_face_decect;


  public function __construct($element,$value=false) {
    parent::__construct($element,$value);
    $this->type = 'file';
    $this->label = $element->label;
    $this->image_editor = $element->image_editor;
    $this->image_upload_url = $element->image_upload_url;
    $this->uploaddir = $element->uploaddir;
    $this->image_width  = $element->image_width ? $element->image_width : 'auto';
    $this->image_height = $element->image_height ? $element->image_height : 'auto';
    
    $this->crop = $element->crop;
    $this->crop_width  = $element->crop_width;
    $this->crop_height = $element->crop_height;
    $this->crop_upload_url = $element->crop_upload_url;
    $this->crop_resize_url = $element->crop_resize_url;
    $this->crop_delete_url = $element->crop_delete_url;
    $this->crop_trim_button = $element->crop_trim_button;
    $this->crop_resize_button = $element->crop_resize_button;
    $this->crop_urlpath = $element->crop_urlpath;
    $this->parent_id_value = $element->parent_id_value;
    //$this->action_if_exists = ($element->action_if_exists) ? $element->action_if_exists : 'replace';
    //$this->action_if_exists_disabled = ($element->action_if_exists_disabled) ? $element->action_if_exists_disabled : false;

    $this->crop_face_decect = $element->crop_face_detect;

  }

  public function render(){
        global $ie7;
        
        $this->id_name = 'input_'.$this->name;  //.'_'.Str::password(6);
        
        ?><style>#<?=$this->id_name?> .fakeupload .delete{display:none;}</style><?php 

        if ($this->image_editor) {
            
            $ver = time();  
            //echo '<pre>';
            //Vars::debug_var($this);
            //echo '</pre>';
            //$ajax_url =  $this->crop_upload_url.'/'.$this->parent_id_value;
             //      var url = '<?=$this->crop_upload_url   $this->parent_id_value?
             // console.log(url);
            ?>
            <!--<h3>Image</h3>-->


            <img class="editable-image" style="width:<?=$this->image_width?>;height:<?=$this->image_height?>;display:inline-block;" src="<?=$this->uploaddir?>/<?=$this->value?>" alt="Preview"><!-- data-dest="dest-img1" data-ajaxurl="ajax.php?op=image-crop"-->


            <script type="text/javascript"> 
            
                  console.log('IMAGE.EDITOR.URL','<?=$this->image_upload_url.$this->parent_id_value?>')   

               // $(function() {
                    console.log('IMAGE.EDITOR.URL','<?=$this->image_upload_url.$this->parent_id_value?>') 
                    ImageEditor.editable_images('.editable-image','<?=$this->image_upload_url.$this->parent_id_value?>');  
                    //OKIImageEditor.editable_images('.editable-image','/page/ajax/id=<?=$this->parent_id_value?>/op=image-crop');  
               // });
            </script>
            <?php


        }else{
            ?>
            <style>.preview-image-upload{max-width:100px;float:right;}.preview-image-upload img{max-width:100px;}</style>
            
            <div class="KKdivinput control-group" style="position:relative;" id="<?=$this->id_name?>">
              <div id="preview-<?=$this->id_name?>" class="preview-image-upload" style="max-width:100px;float:right;"></div>
              <label for="<?=$this->id_name?>"><?=$this->label?></label>
              <div class="controls fileupload" style="width:400px;">
                <div class="fakeupload">
                  <input type="text" id="fake_<?=$this->id_name?>"  value="<?=$this->value?>" name="fake_<?=$this->id_name?>" /> 
                  <a onclick="$('#<?=$this->id_name?> #<?=$this->name?>').click();" id="KKbutton"><?=$this->translate('Examinar')?></a> 
                  <a onclick="$('#<?=$this->id_name?> #<?=$this->name?>').val('').change();" class="delete" id="btn_del_<?=$this->id_name?>"><?=$this->translate('Eliminar')?></a>
                </div>
                <input id="<?=$this->name?>" class="my_file_element" name="<?=$this->name?>" type="file" onchange="this.form.fake_<?=$this->id_name?>.value = this.value;">
                <?php if($this->textafter) echo '<span class="text-after" style="top: 1px;position: absolute;left: 280px;font-size: 9px;text-wrap: balance;  line-height: .9em;">'.$this->textafter.'</span>'; ?>

              </div>

            </div>
            <script type="text/javascript">
            
              $(function(){
                $("#<?=$this->name?>").change(function() {
                  if ($(this).val()){
                    console.log('Value: '+$(this).val());
                    $("#btn_del_<?=$this->id_name?>").show("fast");

                    var filename_ok =  $(this).val()
                              .split(':')
                              .pop()
                              .replace('\\fakepath\\','');
                    
                    var name_ok =  filename_ok.capitalize().replace(/_|.pdf|.doc|.xml|.docx|.xmlx/g,' ');
                    var name_input = $(this).closest('form').find('#NAME');

                    if (name_input && name_input.val()=='')  name_input.val(name_ok);
                    $("#fake_<?=$this->id_name?>").val(filename_ok);
                  }else{
                    $("#btn_del_<?=$this->id_name?>").hide("fast");
                  }

                  if (this.files && this.files[0]) {
                          var fileReader = new FileReader();
                          fileReader.onload = function (event) {
                              $('#preview-<?=$this->id_name?>').html('<img src="'+event.target.result+'" width="300" height="auto"/>');
                          };
                          fileReader.readAsDataURL(this.files[0]);
                  }
                });
              });
              
            </script>
            <?php 
        }
    }

}