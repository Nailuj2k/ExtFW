<?php

require './vendor/autoload.php';

use phpseclib3\Net\SSH2;
	
class SSHTest{
	public $host;
	public $port;
	public $username;
	public $password;
	public $sourcefile;
	public $remotefile;
	public $remotedir;
	public $verbose = true;
	public $protocol = 'ssh2';
	private $result = array();
	private $conn_id;
	private $connected = false;
	
}

class SSHClient {
	
	public $host;
	public $port;
	public $username;
	public $password;
	public $sourcefile;
	public $remotefile;
	public $remotedir;
	public $verbose = true;
	public $protocol = 'ssh2';
	private $result = array();
	private $conn_id;
	private $connected = false;
	private $errors = array();
	
	public function __construct() {
        //parent::__construct();
        ini_set('max_execution_time', 0);
        ini_set('implicit_flush', 1);
        ob_implicit_flush(1);
      //        set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor/phpseclib');
        //set_include_path(get_include_path() . PATH_SEPARATOR . '../../_classes_/scaffold/lib/phpseclib');
       //        include('Net/SSH2.php');
      //        include('Net/SFTP.php');
        $this->result['error'] = 0;
        $this->result['msg'] = array();
        //$this->msg('New SSH client object');
    }
	
    public function connect(){
        $this->result['error'] = 0;
        if (!$this->host)       $this->host='localhost';
        if (!$this->port)       $this->port=21;
        if (!$this->username)   $this->username='anonymous';
        if (!$this->password)   $this->password='yo@mail.com';
        if ($this->verbose) $this->msg('<pre style="background-color:black;color:lime;padding:10px;">');
        $this->msg('sourcefile:<i>'.$this->sourcefile.'</i>');
        $this->msg('remotefile:<i>'.$this->remotefile.'</i>');
        $this->msg('host:<i>'.$this->host.'</i>');
        $this->msg('port:<i>'.$this->port.'</i>');
        $this->msg('username:<i>'.$this->username.'</i>');
        $this->msg('password:<i>'.$this->password.'</i>');
        $this->msg('remotedir:<i>'.$this->remotedir.'</i>');
        if($this->protocol=='ssh2'){

            $this->conn_id = new SSH2($this->host,$this->port,3);
            $this->msg('Conexión establecida usando protocolo SSH2');
        }else if($this->protocol=='sftp'){
            $this->conn_id = new Net_SFTP($this->host,$this->port,3);       
            $this->msg('Conexión establecida usando protocolo SFTP');
        }else{
            $this->result['error'] = __LINE__;
            $this->msg('No valid protocol <i>'.$this->protocol.'</i>');
            $this->errors[__LINE__] = 'No valid protocol <i>'.$this->protocol.'</i>';
        }       
        if($this->conn_id){
            if ($this->conn_id->login($this->username, $this->password)) {
                $this->msg('Conectado con <i>'.$this->host.'</i> como <i>'.$this->username.'</i> <font color="#00F03D"><b>OK</b></font>');
                //$this->msg('Directorio actual: <i>'.$conn_id->pwd().'</i>.');
                $this->connected = true;
            } else {
                $this->result['error'] = __LINE__;
                $this->msg('Nombre de usuario o contraseña incorrecta <i>'.$this->username.':'.$this->password.'</i>');
                $this->errors[__LINE__] = 'Nombre de usuario o contraseña incorrecta <i>'.$this->username.':'.$this->password.'</i>';
            }
        }else{
            $this->result['error'] = __LINE__;
            $this->msg('No se pudo conectar con <i>'.$this->host.'</i>');
            $this->errors[__LINE__] = 'No se pudo conectar con <i>'.$this->host.'</i>';
        }    
        Return $this->result;
    }
    
    public function __destruct () {
   	    if ($this->connected) $this->exec('exit');
        if ($this->verbose) $this->msg('</pre>');
    }

    private function msg($message){
	    $this->result['msg'][] = $message;
        if ($this->verbose){
            echo '<pre>'.$message.'</pre><br />';
          //sleep(1);
            ob_flush();
            flush();
        }
    }

    public function exec($command){
        $this->result['error'] = 0;
	    if ($this->connected){
            $this->msg('<br /><span style="color:orange;">~ '.$command.'</span>');
	        $output = $this->conn_id->exec($command);
            if ($output) $this->msg( $output );
            return ($output) ? trim($output) : /*'Orden ejecutada:<br />'*/'<span style="color:orange;">~ '.$command.'</span>';
        }else{
            $this->result['error'] = __LINE__;
            $this->msg('No está conectado con <i>'.$this->host.'</i>');
            $this->errors[__LINE__] = 'No está conectado con <i>'.$this->host.'</i>';
            Return $this->result;
        }
    }
    
    public function upload() {
        $this->result['error'] = 0;
        if (!$this->sourcefile){
            $this->msg('No se ha especificado un nombre de archivo');
            $this->result['error'] = __LINE__;
            $this->errors[__LINE__] = 'No se ha especificado un nombre de archivo';
            return $this->result;
        } else if (!file_exists($this->sourcefile)){
            $this->result['error'] = __LINE__;
            $this->msg('No existe el archivo <i>'.$this->sourcefile.'</i>');
            $this->errors[__LINE__] = 'No existe el archivo <i>'.$this->sourcefile.'</i>';
            return $this->result;
        }else if (!$this->remotefile){
            $this->result['error'] = __LINE__;
            $this->msg('Remote file not set: <i>'.$this->remotefile.'</i>');
            $this->errors[__LINE__] = 'Remote file not set: <i>'.$this->remotefile.'</i>';
            return $this->result;
 	    } else if ($this->connected){
            $this->msg('Put '.$this->sourcefile);
            $this->msg(' => '.$this->remotedir.'/'.$this->remotefile);
            $output = $this->conn_id->put( $this->remotedir.'/'.$this->remotefile, $this->sourcefile, NET_SFTP_LOCAL_FILE);
            $this->msg( $output );
            return ($output) ? $output : 'No output ['.print_r($this->conn_id->getLastSFTPError(),true).']';
        }else{
            $this->result['error'] = __LINE__;
            $this->msg('No está conectado con <i>'.$this->host.'</i>');
            $this->errors[__LINE__] = 'No está conectado con <i>'.$this->host.'</i>';
            return $this->result;
        }
    }
    
    public function getErrors(){
	    //$this->msg(  print_r($this->conn_id->getSFTPErrors(),true) );
	    //$this->msg(print_r($this->errors,true));
        return $this->errors;
    }
    
    public function getLastError(){
	    //$this->msg(  print_r($this->conn_id->getLastSFTPError(),true) );
	    //$this->msg(print_r($this->errors,true));
	     $tmp = array_values($this->errors);
         return end($tmp);
   }
    
    public function disconnect(){
        $this->conn_id->disconnect();
        return 'Disconnected ... '; //.print_r($this->conn_id->getLastSFTPError(),true).'.';
    }
          
}	
	