<?php 

class MemVar{

  private $identifier;
  private $key;
  private $size= 10000;  //  default 10000 bytes
  private $value;
  function __construct($key,$value=false){
   
    $this->key = $key;
    if($value){
        /********/
        // how to calculate the  minimum $memsize required to store the variable $foo where $foo='foobar'.
        // when shm_attach() is called for the first time, PHP writes a header to the beginning of the shared memory. 
        $shmHeaderSize = (PHP_INT_SIZE * 4) + 8;
        // when shm_put_var() is called, the variable is serialized and a small header is placed in front of it before it is written to shared memory.
        $shmVarSize = (((strlen(serialize($value))+ (4 * PHP_INT_SIZE)) /4 ) * 4 ) + 4;
        // now add the two together to get the total memory required. Of course, if you are storing more than 
        // one variable then you dont need to add $shmHeaderSize for each variable, only add it once.
        $this->size = $shmHeaderSize + $shmVarSize;
        // this will give you just enough memory to store the one variable using shm_put_var().
       // $shm_id = shm_attach ( $key, $memsize, 0666 ) ;
       // shm_put_var  ( $shm_id  , $variable_key  , $foo  );
        // any attempt to store another variable will result in a 'not enough memory' error.
        // Be aware that if you change the contents of $foo to a larger value and then you try 
        // to write it to shared memory again using shm_put_var(), then you will get a 'not enough memory'
        // error. In this case, you will have to resize your shared memory segment and then write the new value.
        /**/
        // Objects are stored serialized in shm_put_var, so to find a value for memsize, you can use strlen(serialize($object_to_store_in_shm)).
       $this->identifier = shm_attach($key, $this->size);
       //shm_put_var($this->identifier, $key, $value);
    }else{
       //$this->size  = 10000;
       $this->identifier = shm_attach($key, $this->size);
    }

   //print_r($this->identifier);
    

  }

  function setValue($key, $value){
    shm_put_var($this->identifier, $key, $value);
    //Vars::debug_var($this->size,'SIZE');
  }
 
  function getValue($key){
    return shm_get_var($this->identifier, $key);
  }
 
  function delete(){
    shm_remove($this->identifier);
  }
 
  function close(){
    shm_detach($this->identifier);
  }

  function exists(){
    return  true; // shm_has_var($this->identifier, $this->key);
  }



}