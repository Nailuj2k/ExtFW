<?php 
  
class Field {
    
    public $id;
    public $order;
    public $tablename;
    public $type = 'varchar';
    public $fuzzydate; // Only for datetime & tiemstamp types //COPY
    public $name; 
    //cms_functions_i18n
    //function fuzzyDate($timestamp){
    //if(!$timestamp) return '';//COPY
    public $driver = 'mysql';
    public $width;
    public $len;
    public $class;
    public $classname;
    public $fieldset = false; //'default';
    public $attr;       //CHECK
    public $attribute;  //CHECK is the same??
    public $check;
    public $default_value;
    public $fieldname;   
    public $keyName;   
    public $keyValue;   
    public $label;   
    public $value;   
    public $allowNull;
    public $format;   
    public $regexp = '^[a-zA-Z0-1_/s]+$';
//  public $markup = '<td>%s</td>';
    public $lookupTable;
    public $values;        // active values
    public $values_all;    // all_values
    public $source;
    public $source_all;
    public $visible =true;
    public $filter;
    public $sortable = true;
    public $required;   
    public $fk;   
    public $ajax_url;
    public $title;
    public $hide;
    public $searchable;
    public $max_chars;
    public $editable;
   // public $detail_field;  //WTF
    public $uploaddir  = 'media/files/';
    public $inline_edit = false;
    public $wysiwyg = true;
    public $disabled = false;
    public $accepted_doc_extensions = array( '.doc','.docx','.xls','.xlsx','.ppt','.pptx','.mdb','.dot','.dotx',
    '.pages','.numbers','.keynote',
    '.epub','.mobi',
    '.odt', '.ods', '.odp',
    '.pdf','.rtf','.txt','.dwg',
    '.html','.xml',
    '.zip','.rar','.7z','.tar','.tgz','.gz',
    '.ico','.tif','.tiff','.psd','.bmp','.eps','.ai','.svg',
    '.jpg','.jpeg','.gif','.png','.webp',
    '.avi','.wmv','.mpg','.mpeg','.mp3','.ogg','.mp4','.m4a','.mov','.mkv') ;
    public $accepted_img_extensions = array( '.jpg','.jpeg','.gif','.png','.webp','.svg') ;
    public $accepted_extensions;
    // public $accepted_extensions = array_merge($accepted_doc_extensions,$accepted_img_extensions);
    public $action_if_exists_disabled;
    public $action_if_exists;

    public $parent_id = true;
    public $css_id;
    public $size;
    public $watermark;
    public $values_visibles;
    public $html;
    
    public $clearValue;
    public $clearText;
    public $expression;

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
    public $child_allow_null;
    public $child_clear_text;
    public $classes;
    public $styles;
    public $image_editor;
    public $image_upload_url;
    public $image_width;
    public $image_height;
    public $prefix_filename;
    public $translatable;
    public $length;
    public $pk;
    public $multiselect;
    public $datepicker;
    public $parent_fieldname;
    public $html_before;
    public $langs;
    //public $translation;
    public $html_after;
    public $parent_id_value;
    public $minmax;
            
    public $parent;
    public $displaytype;

    public function __construct($fieldname=false){ if($fieldname) $this->fieldname=$fieldname; return $this;    }

    public static function field($fieldname=false){return new Field($fieldname);}

   // public function pk($v)          { $this->pk            = $v; $this->editable=false; return $this; }
    public function type($v)          { $this->type          = $v; return $this; }
    public function fieldname($v)     { $this->fieldname     = $v; return $this; }
    public function len($v)           { $this->len           = $v; return $this; }
    public function label($v)         { $this->label         = $v; return $this; }
    public function editable($v)      { $this->editable      = $v; return $this; }
    public function searchable($v)    { $this->searchable    = $v; return $this; }
    public function sortable($v)      { $this->sortable      = $v; return $this; }
    public function filtrable($v)     { $this->filtrable     = $v; return $this; }
    public function length($v)        { $this->length        = $v; return $this; }
    public function fieldset($v)      { $this->fieldset      = $v; return $this; }
    public function width($v)         { $this->width         = $v; return $this; }
    public function value($v)         { $this->value         = $v; return $this; }
    public function default_value($v) { $this->default_value = $v; return $this; }
    public function hide($v)          { $this->hide          = $v; return $this; }
    public function inline_edit($v)   { $this->inline_edit   = $v; return $this; }
    public function wysiwyg($v)       { $this->wysiwyg       = $v; return $this; }
    public function minmax($v)        { $this->minmax        = $v; return $this; }
    public function required($v)      { $this->required      = $v; return $this; }
    public function allow_null($v,$clearValue=null,$clearText=null) { 
        $this->allowNull = $v;
        if( $clearValue)$this->clearValue = $clearValue;
        if( $clearText) $this->clearText = $clearText;
        return $this;
    }
    public function textafter($v)     { $this->textafter     = $v; return $this; }
    public function precission($v)    { $this->precission    = $v; return $this; }
    public function translatable($v)  { $this->translatable  = $v; return $this; }
    public function calculated($v)    { $this->calculated    = $v; return $this; }
    public function javascript($v)    { $this->javascript    = $v; return $this; }
    public function uploaddir($v)     { $this->uploaddir     = $v; return $this; }
    public function html($v)          { $this->html          = $v; return $this; }

    public function readonly($v)      { $this->readonly      = $v; return $this; }

    public function child($ajax_url,$fieldname,$sql){
        $this->child_ajax_url   = $ajax_url;
        $this->child_fieldname  = $fieldname;
        $this->child_source_sql = $sql;
        return $this; 
    }
    public function values($v,$v_all=false)     { 
        $this->values     = $v; 
        $this->values_all = $v_all===false ? $v : $v_all; 
        return $this; 
    }
    public function langs($langs)  { 
        if(count($langs)>0) {
            $this->translatable = true;  
            $this->langs =  $langs;
        }
        return $this; 
    }

    public function parent($v)     { $this->parent     = $v; return $this; }
    public function displaytype($v)     { $this->displaytype     = $v; return $this; }
    public function placeholder($v)     { $this->placeholder     = $v; return $this; }

    public function escape($value) {                                 //ADD 20140423
      //  if($this->driver=='mysql') return mysql_real_escape_string($value);   //ADD 20140423
      //                        else return $value;                             //ADD 20140423
        if($this->driver=='sqlite') {
          $search = ["'"];
          $replace = ['\'\''];
        }else{
          $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
          $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
        }
        return str_replace($search, $replace, $value);
    }                                                                      //ADD 20140423

    public function format_value($value) {
      switch($this->type) {
        case 'enum':
          if($this->value) return "'{$this->value}'"; else return "'0'";
          break;
        case 'decimal':
        case 'float':
        case 'color':
        case 'file':
        case 'time':
        case 'ccc':
          return "'{$this->value}'";     // gmdate("H:i:s",$this->value).'"';
          break;
        case 'textarea':                               //ADD 20140423
          return "'".$this->escape($this->value)."'";  //ADD 20140423
          break;

        case 'datetime':
          if     ($this->value=='NOW()') return $this->value;  //COPY
          else if($this->value)          return "'{$this->value}'";
          else                           return "NULL";
        //case 'date':
          break;
        case 'varchar':
        //return "'".self::esc($this->value)."'"; 
          return "'".$this->escape($this->value)."'";  
          break;                                       
        case 'hidden':
        case 'select':
        case 'progress':
        case 'bigint':
        case 'timestamp':
        case 'int':
        case 'bool':
          if($this->value) return "'{$this->value}'";  
                      else return "'0'";  
          break;
        case 'date':
          if     ($this->value=='SYSDATE') return $this->value;  
          else if($this->value=='NOW()'  ) return $this->value;  
          else if($this->driver=='oracle') return "to_date('{$this->value}', 'DD-MM-YYYY HH24:MI:SS')";
          else if($this->driver=='sqlite') return  "'".$this->escape($this->value)."'";  
                                     else  return "{$this->value}";   
        case 'unixtime':
            if($this->value) return date(DATETIME_FORMAT,$this->value);  
                        else return '';  
            break;
        default:
          return $this->value; 
      }
    }
     
    public function strSql() {
      return $this->fieldname.' = '.$this->format_value($this->value);
    }
    public function strSqlFilter() {
      
      switch($this->type) {
        case 'decimal':
        case 'float':
        case 'enum':
        case 'color':
        case 'datetime':
        case 'ccc':
        case 'time':
          return "{$this->fieldname} = '{$this->value}'";     // gmdate("H:i:s",$this->value).'"';
          break;
        case 'date':
          return $this->fieldname." LIKE '%".$this->value."%'"; 
        case 'textarea':
        case 'varchar':
          return $this->fieldname." LIKE '%".self::esc($this->value)."%'"; 
          break;
        case 'select':
        case 'progress':
        case 'bigint':
        case 'int':
        case 'timestamp':
        case 'bool':
        case 'unixtime':
          if($this->value) return "{$this->fieldname} = '{$this->value}'"; 
                      else return "{$this->fieldname} = 0"; 
          break;
        default:
          return $this->fieldname.' = '.$this->format_value($this->value);
      }
      
    }
     
    
    public function get($keyValue) {
      return getFieldValue($this->tablename,$this->fieldname,' WHERE '.$this->keyName.'='.$keyValue,true,true);
    }
    
    public function __toString(){
//      return sprintf($this->markup,$this->value);
      return '';
    }
 
    public function getFormElement($value=false) {
      //print_r($this->values);
      switch($this->type) {
        case 'html':
          return new formElementHtml($this,$value); //($this->name,$this->label,$value,$this->values); //$allowNull=true,$clearValue=0,$clearText='--'
          break;
        case 'select':
          return new formSelect($this,$value); //($this->name,$this->label,$value,$this->values); //$allowNull=true,$clearValue=0,$clearText='--'
          break;
        case 'hidden':
          return new formHidden($this,$value); //($this->name,$this->label,$value); 
          break;
        case 'textarea':
          return new formTextarea($this,Str::unescape(Str::unescape($value))); //($this->name,$this->label,$value); 
          break;
        case 'bool':
          return new formCheckbox($this,$value); //($this->name,$this->label,$value); 
          break;
        //case 'ccc':
        //  return new formInputCCC($this,$value); //($this->name,$this->label,$value); 
        //  break;
        case 'file':
          return new formFile($this,$value); //($this->name,$this->label,$value); 
          break;
        case 'date':
          //if(!$value) $value = date("Y-m-d");
          return new formInputDate($this,$value); //($this->name,$this->label,$value); 
          break;
        case 'time':
          //if(!$value) $value = date("H:i:s");
          return new formInputTime($this,substr($value,0,5)); //($this->name,$this->label,$value); 
          break;
        case 'datetime':
          return new formInputDateTime($this,$value); //($this->name,$this->label,$value); 
          break;
        case 'unixtime':
          return new formInput($this,$value); //new formInputDateTime($this,$value); //($this->name,$this->label,$value); 
          break;
        case 'color':
          return new formInputColor($this,$value); //($this->name,$this->label,$value); 
          break;
      //  case 'file':
        case 'progress':
          return new formInputProgress($this,$value); //($this->name,$this->label,$value,$this->values); //$allowNull=true,$clearValue=0,$clearText='--'
          break;
        case 'decimal':
        case 'float':
          return new formInput($this,number_format ($value??'0.00' , $this->precission?$this->precission:2 , '.', '' ));
          break;
        case 'int':
        case 'enum':
        case 'varchar':
          return  $this->values ? new formSelect($this,$value) : new formInput($this,$value); //NEW
        default: 
          return new formInput($this,$value); 
//          return new formInput('varchar',$this->name,$this->label,$value); 
      }
    }
    
  /*
  private function limit_text($text,$maxchar){
     $split=explode(" ",$text);
     $i=0;
     while(TRUE){
         $len=(strlen($newtext)+strlen($split[$i]));
         if($len>$maxchar){
             break;
         }else{
         $newtext=$newtext." ".$split[$i];
         $i++;
         }
     }
     if (strlen($text)>$maxchar)$newtext=$newtext.' ...';
     return $newtext;
  }
  */
  
  private function len2style($text,$len) {
      if (strlen($text ?? '') <= $len) return '';
      else if (strlen($text)>50)    return ' font-size-50';
      else if (strlen($text)>45)    return ' font-size-45';
      else if (strlen($text)>40)    return ' font-size-40';
      else if (strlen($text)>35)    return ' font-size-35';
      else if (strlen($text)>30)    return ' font-size-30';
      else if (strlen($text)>25)    return ' font-size-25';
      else if (strlen($text)>20)    return ' font-size-20';
      else if (strlen($text)>15)    return ' font-size-15';
      else if (strlen($text)>10)    return ' font-size-10';
      else if (strlen($text)>5 )    return ' font-size-5';
      else                          return '';
  }

    public function getCell($row,$value,$editable=false,$markup='',$searchstring=false){ //,$colspan=false){
      //$editable=false;
      $class = 'cell cell_'.$this->fieldname.' ';
      if ($this->type == 'progress') {
        //$value = '<progress max="100" id="progress_bar_'.$this->name.'" class="pbar" value="'.$value.'"></progress>';
        $value = '<div style="display:inline;" id="progress_bar_'.$this->name.'" class="progress '.$this->classname.'"><div class="bar" style="width: '.$value.'%"></div></div>';
        $class .= 'ne';
        $this->width = 144;
      }else{
        $class .= ($editable) ?  'editable' : 'ne';
      }
      $style = '';
      if ($editable && $this->type == 'select') $class .= '-select';
      if ($editable && $this->type == 'bool')   $class .= ' bool';
      if ($this->type == 'textarea')            $class .= ' textarea';
      //if ($this->type == 'file')
      if ($this->type == 'textarea') $value=strip_tags($value ?? '');
      
      if($this->values_all){
        $display_value = ($this->type == 'select') ? $this->values_all[$value] : $value;
        if ($this->max_chars) $class .= $this->len2style(($this->type == 'select') ? $this->values_all[$value] : $value,$this->max_chars);
      }else{
        $display_value = ($this->type == 'select') ? $this->values[$value] : $value;
        if ($this->max_chars) $class .= $this->len2style(($this->type == 'select') ? $this->values[$value] : $value,$this->max_chars);
      }
      //if ($this->max_chars) $display_value =  $this->limit_text($display_value,$this->max_chars);

      if($this->classes){
	      $class .= ' '.$this->classes[$value];
      }
      
      if      ($this->type == 'date')     $class .= ' cell-date';
      else if ($this->type == 'time')     $class .= ' cell-time';
      else if ($this->type == 'datetime') $class .= ' cell-datetime';
      //TIMESTAMP else if ($this->type == 'timestamp')$class .= ' cell-datetime';
      else if ($this->type == 'bool')     $class .= ' cell-bool';
      else if ($this->type == 'unixtime') $class .= ' cell-int'; //' cell-datetime';
      else if ($this->type == 'int')      $class .= ' cell-int';
      else if ($this->type == 'decimal')  $class .= ' cell-decimal';

      else if ($this->type == 'color')   $style  = 'background-color:'.$value.' !important;';
      else if ($this->width)             $style  = 'width:'.$this->width.'px;max-width:'.$this->width.'px;';
      
      if ($this->classname)     $class .= ' '.$this->classname;

//      if ($this->type == 'unixtime') $display_value = $value ? date(DATETIME_FORMAT,$value) : ''; //MOD 20131103
      if ($this->type == 'unixtime') $display_value = $value ? fuzzyDate($value) : ''; //MOD 20131103

      if ($this->type == 'color')$class .= ' cell-color';
      
      if ($this->type == 'bool'){
        if($value) $class .= ' cell-bool checked';
        else       $class .= ' cell-bool unchecked';
        $display_value = '<i class="fa fa-check"></i>'; //.$display_value;
      }
      
      if($this->searchable && $searchstring && $this->type !== 'bool' && $this->type !== 'progress' && $this->colorize!==false)
                                                                $display_value = Str::colorizeSearchText($searchstring,$display_value);
      if ($this->type !== 'select') $value = ''; 
      if ($this->type ==  'datetime'  && $this->fuzzydate )     $display_value = fuzzydate($display_value); 
      //TIMESTAMP if ($this->type ==  'timestamp' && $this->fuzzydate )     $display_value = fuzzydate($display_value); 
     
      if (!$display_value) {
        if      ($this->type=='int'||$element->type=='bigint')  $display_value = '0';     //ADD 20131103
        else if ($this->type=='decimal')                        $display_value = '0.00';  //ADD 20131103
      }else{
        if ($this->type=='decimal')                             $display_value = number_format ($display_value , $this->precission?$this->precission:2 , '.', '' );
      }
      if ($this->type=='date' && $display_value =='00-00-0000') $display_value = '';  //ADD 20140522

      $a = array( '[ID]'        => 'cell-'.$row.'-'.$this->order, //REFACTOR  'cell-'.$this->tablename.'-'.$row.'-'.$this->order  //OLD 'cell-'.$row.'-'.$this->order
                  '[CLASS]'     => $class,
                  '[STYLE]'     => $style,
                  '[LABEL]'     => $this->label,
                  '[FIELDNAME]' => $this->fieldname,
                  '[VAL]'       => $value,
                  '[CONTENT]'   => $display_value,
                  '[PRECISSION]' => $this->precission );
      return str_replace( array_keys($a), array_values($a), $markup) ;
   
      //return sprintf($markup,'cell-'.$row.'-'.$this->order,$class,$style,$value,$display_value);
    }  

    public static function esc($str){
      //if(ini_get('magic_quotes_gpc'))	$str = stripslashes($str);
      //return mysql_real_escape_string(strip_tags($str));
      return stripslashes($str);
      //return $str;
    }

}