<?php

    if(!defined('CAPTCHA')){
    $catpcha = false;
    function captchaInputParams(){
        if(CFG::$vars['captcha']['google_v3']) echo ' data-badge="inline" data-size="invisible" data-sitekey="'.CFG::$vars['captcha']['google_v3']['public'].'" data-callback="onSubmit" ';
    }
    function captchaInputParams(){ if(CFG::$vars['captcha']['google_v3']) echo 'g-recaptcha'; }
    }
    define('CAPTCHA','RECAPTCHA_V3');




//https://programarivm.com/envia-por-ajax-el-nuevo-recaptcha-de-google-y-validalo-con-php/
//https://programarivm.com/envia-por-ajax-el-nuevo-recaptcha-de-google-y-validalo-con-php/
//https://programarivm.com/envia-por-ajax-el-nuevo-recaptcha-de-google-y-validalo-con-php/



// https://github.com/google/recaptcha/tree/master/examples
/******
class Recaptcha{
	
    public function __construct(){
        //  $this->config = require(SCRIPT_DIR_MODULES.'/login/cfg.php');
    }
 
	public function verifyResponse($recaptcha){
		$remoteIp = $this->getIPAddress();

		// Discard empty solution submissions
		if (empty($recaptcha)) {
			return array(
				'success' => false,
				'error-codes' => 'missing-input',
			);
		}
		$getResponse = $this->getHTTP(
			array(
				'secret' => CFG::$varsg['captcha']['google_v3']['secret'], 
				'remoteip' => $remoteIp,
				'response' => $recaptcha,
			)
		);

		// get reCAPTCHA server response
		$responses = json_decode($getResponse, true);

		if (isset($responses['success']) and $responses['success'] == true) {
			$status = true;
		} else {
			$status = false;
			$error = (isset($responses['error-codes'])) ? $responses['error-codes']
				: 'invalid-input-response';
		}

		return array(
			'success' => $status,
			'error-codes' => (isset($error)) ? $error : null,
		);
	}


	private function getIPAddress(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
		{
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
		{
		 $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} 
		else 
		{
		  $ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return $ip;
	}

	private function getHTTP($data){
		
		$url = 'https://www.google.com/recaptcha/api/siteverify?'.http_build_query($data);
		$response = file_get_contents($url);

		return $response;
	}
}


**/