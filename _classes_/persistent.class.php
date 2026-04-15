<?php

class Persistent{

    var $filename; 
    public $data;

    /**********************/
    
    function __construct($filename){
        $this->filename = $filename;
        if(!file_exists($this->filename)) $this->save();
    }
    
    /**********************/
    
    function save(){
        if($f = @fopen($this->filename,"w")) {
            if(@fwrite($f,serialize($this->data))){
                @fclose($f);
            }
            else Messages::error("Could not write to file ".$this->filename." at Persistant::save");
        }
        else Messages::error("Could not open file ".$this->filename." for writing, at Persistant::save");
       
    }
    
    /*
    function save(){

        $dir = dirname($this->filename);

        if ($dir && !is_dir($dir)) {
            if (!SYS::mkdirr($dir, 0775, true) && !is_dir($dir)) {
                $err = error_get_last();
                Messages::error(
                    "Could not create directory {$dir} for writing {$this->filename} at Persistent::save"
                    . ($err && isset($err['message']) ? " ({$err['message']})" : "")
                );
                return;
            }
        }

        $f = @fopen($this->filename, "w");
        if ($f === false) {
            $err = error_get_last();
            Messages::error(
                "Could not open file {$this->filename} for writing, at Persistent::save"
                . ($err && isset($err['message']) ? " ({$err['message']})" : "")
            );
            return;
        }

        $ok = @fwrite($f, serialize($this->data));
        if ($ok === false) {
            $err = error_get_last();
            @fclose($f);
            Messages::error(
                "Could not write to file {$this->filename} at Persistent::save"
                . ($err && isset($err['message']) ? " ({$err['message']})" : "")
            );
            return;
        }

        @fclose($f);
    }
    */
    /**********************/
    
    function open(){
        $this->data = unserialize(file_get_contents($this->filename));
    }

    /**********************/

}


