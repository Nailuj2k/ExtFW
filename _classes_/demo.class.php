<?php 

class AuthDEMO{

  function __construct(){ }
  
  public static function rdn2group($str) {
  }

  public static function s($x) { 
    if(is_array($x)) {
      echo '<pre class="code" style="font-size:0.9em;display:block;overflow:auto;max-height:450px;">';
      print_r($x);
      echo '</pre>';
    }else{
      echo '<span style="font-size:0.9em;">'.$x.'</span><br />'; 
    }
  }

  public static function IsGroup($str) {}

  public function connect(){  }

  public function connected(){  }

  public function auth(){
     return true;
  }

  public function user_auth($data) {
    return ($data['username'] && ($data['password']=='demo'));
  }

  public function get_user_groups($username) {
    $result = array();
    return $result;
  }

  public function get_groups(&$groups,$groupname) {
    $result = array();
    return true;
  }

  public function get_members(&$groups,$groupname) {  }


}

