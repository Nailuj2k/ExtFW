<?php

    abstract class HTML{

        private static $index = 1;
        private static $elements = [];
    
        ////////private static array $files = [];
        //private static array $combined = ['css' => '', 'js' => ''];

        private static function removeVersion($link) {
            // Remove version query string from the link
            return preg_replace('/\?.*/', '', $link);
           // return $link;
        }

        public static function css($link,$media='screen',$extra=''){
      
            //if(strpos($link, 'https') == false) {
  
                /*
                $content = file_get_contents( self::removeVersion($link) );      
                $content = preg_replace('/\s+/', ' ', $content);         // Minify CSS
                $content = str_replace(['; ', ' {', '{ ', ' }', '} '], [';', '{', '{', '}', '}'], $content);
                self::$combined['css'] .= $content;
                */
            //}else{
                
                self::$index++; 
                $prefix  = '<link href="';
                $suffix  = '" media="'.$media.'" rel="stylesheet" type="text/css" '.$extra.'/>'.NL;
                self::$elements['css'][self::$index] = $prefix . $link . $suffix;
                   
            //}

        }

        public static function js($src,$extra=''){

            //if(strpos($src, 'https') == false) {
                /*
                self::$combined['js'] .= file_get_contents( self::removeVersion($src )) . "\n";
                */
            //}else{

                self::$index++; 
                $prefix  = '<script type="text/javascript" src="';
                $suffix  = '" '.$extra.'></script>'.NL;
                self::$elements['js'][self::$index] = $prefix . $src . $suffix;

            //}

        }

        public static function render($type){

            echo NL.implode('',self::$elements[$type]);
            
            //file_put_contents(SCRIPT_DIR_JS . '/combined.'.$type, self::$combined[$type]);
            /*
            if($type=='js'){

                echo '<script type="text/javascript" src="'.SCRIPT_DIR_JS.'/combined.js defer></script>'.NL;

            }else if($type=='css'){

                //echo '<link href="'.SCRIPT_DIR_JS.'/combined.css" media="screen" rel="stylesheet" type="text/css" />'.NL;
            
            }            
            */
        }

    }
        