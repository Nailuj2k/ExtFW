<?php

    trait DemoConnection {  
      
        private static $connection;
        private static $error;
        //public function __construct(){
        //}
        public static function lastError(){
          return self::$error;    
        }
        
        public static function connect(){
        }
        
        public static function sqlQuery($sql){
            return false;
        }

        public static function getFieldsValues($sql){
            return false;
        }


        
    }