<?php
    
    class dummyField {
        public $name;
        public $value;        
        public $required;
        public $showreset;
        public $tablename;
        public $readonly;
        public $before;
        public $after;
        public $help;
        public $displaytype;
        public $type;
        public $minmax;
        public $fieldname;
        public $id;
        public $classes;
        public $label;
        public $classname;
        public $disable;
    }

    class Field extends dummyField {
        
        public $id;
        public $name;

        public $owner;   // Table object
        public $fieldname;
        public $label;
        public $readonly;
        public $classname;
        public $editable = false;
        public $required;
        public $placeholder;
        public $len;
        public $size;
        public $maxsize;
        public $value;
        public $displayvalue;
        public $default_value;
        public $calculated; 
        public $values;
        public $fieldset = 'default';
        public $rows;
        public $wysiwyg;
        public $class;
        public $extra;
        public $help;
        
        public function __construct() {
        }
        
        public static function values($sql){
            $result = array();
            $rows  = Table::sqlQuery($sql); 
            foreach( $rows as $row){  $result[$row['ID']]=$row['NAME'];   }
            return $result;
        }
         
        public function getCell($table,$col){
           if ($col->type=='select'){
               $col->displayvalue = $col->values[$col->value];
           }else{
               $col->displayvalue = $col->value;
           }
           return str_replace(  array('[TABLENAME]','[PK]','[VALUE]','[FIELDNAME]','[FIELDVALUE]','[DISPLAYVALUE]'), 
                                array($table->tablename,$table->pk->fieldname, $table->pk->value,  $col->fieldname, $col->value, $col->displayvalue ), 
                                MARKUP_CELL );
        }
    
        public function getFormElement() {
            switch($this->type) {
                case 'select':
                    return new formSelect($this);
                    break;
                case 'hidden':
                    return new formHidden($this); 
                    break;
                case 'html':
                    return new formHtml($this); 
                    break;
                case 'textarea':
                    return new formTextarea($this); 
                    break;
                default:
                    return new formInput($this); 
            }
        }    
    
    }