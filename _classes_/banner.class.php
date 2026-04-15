<?php
   

class Banner{


    public static function demo(){
    
        echo'<img src="media/fotos/images/megabanner.gif" class="megabanner"/>';
    
    }

  	public function __construct(/*$params=false*/){

        //self::$table = New TableMysql();
        //$this->get();
    }


    public static function get($id=false) {
        $NL = "\n";
        $uri = $_SERVER['REQUEST_URI'];
        //$url = '/control_panel/ajax/op=function/function=print_banner/table=GES_BANNERS/id='.$id; 
        echo  '<span class="banner" id="banner_'.$id.'" style="display:none;"></span>'.$NL
            . '<script type="text/javascript">$(function() { banner('.$id.',\''.$uri.'\'); });</script>'.$NL
            ;
    } 
      

}