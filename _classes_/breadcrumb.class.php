<?php

class Breadcrumb{

    public $crumbs     = array(); 
    public $separator  = '<span class="breadcrumb-separator"></span>';  // ' > '
    public $bc_begin   = '<ul>';
    public $bc_end     = '</ul>';
    public $item_begin = '<li>';
    public $item_end   = '</li>';
    public $base       = '<li><a href="/" title="Home" aria-label="Home"><span class="breadcrumb-separator"></span><i class="fa fa-home"></i></a></li>';  // '/'
    public static $replace = array();
    public static $breadcrumbs = array();

    function __construct($menuId=0,$name='main'){
        //Breadcrumb::$replace['contact'] = array('contact',t('CONTACT'),'contact');
    }

    public function url2crumbs(){
        $parts = explode("/",$_SERVER["REQUEST_URI"]);
        $url = '';
        $sep = '';
        foreach ($parts as $part){
            if($part) {
                $url .= $sep.$part;
                $sep = '/';
                $this->crumbs[$part] = [$part,$url];
            }
        }
    }

    public function show() {
      
         if     (count(self::$breadcrumbs)>0)
             $this->crumbs = self::$breadcrumbs;
         else if(count($this->crumbs)<1) 
             $this->url2crumbs();   

     //echo '<pre>'.print_r($this->crumbs,true).'</pre>';

        echo $this->bc_begin;
        echo $this->base;

        foreach($this->crumbs as $k => $v){
            
            if (is_numeric($k)) continue;
            if (in_array($k,['tag','tipo','item','all',$_SESSION['lang']])) continue;

            foreach (self::$replace as $k2 => $v2){
                if($k == $k2) $v = $v2;
            }

            if($v===false){ 

            }else if($v[1]??false){
                $__name = str_replace('_', ' ', $v[0] ?? '');
                if($__name)
                echo $this->item_begin.$this->separator . '<a href="'.Vars::mkUrl($v[1]).'" aria-label="'.$__name.'">'.$__name.'</a>'.$this->item_end;
            }else{
                echo $this->item_begin.$this->separator . '<span>'.str_replace('_',' ',$v[0] ?? '').'</span>'.$this->item_end;
            }
        } 
        echo $this->bc_end;

    }

}
