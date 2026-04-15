<?php


/*
 *
 *  https://medium.com/@patrick.assoa.adou/a-generic-php-singleton-1985f17eeb6f
 *
 * * * */

function singletonize(\Closure $func){

    $singled = new class($func){
        
        // Hold the class instance.
        private static $instance = null;

        public function __construct($func = null){
            if (self::$instance === null) {
                self::$instance = $func();
            }
            return self::$instance;
        }
        
        // The singleton decorates the class returned by the closure
        public function __call($method, $args){
            return call_user_func_array([self::$instance, $method], $args);
        }
        
        private function __clone(){}
        
        private function __wakeup(){}

    };

    return $singled;

}



/****  example ******

class ClassThatMarksDateOfInstantiation
{
    private $dob;
    public function __construct()
    {
        $this->dob = new \DateTimeImmutable;
    }
    public function getDob()
    {
        return $this->dob;
    }
}
$factoryOfDobClass = function () {
    return new ClassThatMarksDateOfInstantiation;
};
$DobSingleton = singletonize($factoryOfDobClass); // -> anonymous class
$dob = new $DobSingleton; // -> new instance of anonymous class
$dob2 = new $DobSingleton; // -> same instance of anonymous class
print_r($dob->getDob() === $dob2->getDob()); // -> 1


*/