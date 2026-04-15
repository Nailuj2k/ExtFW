<?php

//http://www.php.net/manual/en/function.md5.php#43696
/**
Alexander Valyalkin 30-Jun-2004 08:41
Below is MD5-based block cypher (MDC-like), which works in 128bit CFB mode. 
It is very useful to encrypt secret data before transfer it over the network.
$iv_len - initialization vector's length.
0 <= $iv_len <= 512
**/

define('FILE_ENCRYPTION_BLOCKS', 10000);


Class Crypt{

    public static function get_rnd_iv($iv_len){
        $iv = '';
        while ($iv_len-- > 0) {  $iv .= chr(mt_rand() & 0xff); }
        return $iv;
    }

    public static function base64_url_encode($input){
        return strtr(base64_encode($input), '+/=', '-_,');
    }

    public static function base64_url_decode($input){
        if(trim($input)==''||$input=='null')  return '';
        return base64_decode(strtr($input, '-_,', '+/='));
    }

    public static function md5_encrypt($plain_text, $password, $iv_len = 16, $rnd=false){
        $plain_text .= "\x13";
        $n = strlen($plain_text);
        if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
        $i = 0;
        $enc_text = $rnd ? $rnd : self::get_rnd_iv($iv_len);
        $iv = substr($password ^ $enc_text, 0, 512);
        while ($i < $n) {
            $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
            $enc_text .= $block;
            $iv = substr($block . $iv, 0, 512) ^ $password;
            $i += 16;
        }
        return base64_encode($enc_text);
    }

    public static function md5_decrypt($enc_text, $password, $iv_len = 16){
        $enc_text = base64_decode($enc_text);
        $n = strlen($enc_text);
        $i = $iv_len;
        $plain_text = '';
        $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
        while ($i < $n) {
            $block = substr($enc_text, $i, 16);
            $plain_text .= $block ^ pack('H*', md5($iv));
            $iv = substr($block . $iv, 0, 512) ^ $password;
            $i += 16;
        }
        return preg_replace('/\\x13\\x00*$/', '', $plain_text);
    }

    /*
    $plain_text = 'very secret string';
    $password = 'very secret password';
    echo "plain text is: [${plain_text}]<br />\n";
    echo "password is: [${password}]<br />\n";

    $enc_text = Crypt::md5_encrypt($plain_text, $password);
    echo "encrypted text is: [${enc_text}]<br />\n";

    $plain_text2 = Crypt::md5_decrypt($enc_text, $password);
    echo "decrypted text is: [${plain_text2}]<br />\n";
    */
      
    public static function urlsafe_b64encode($string) {
       $data = base64_encode($string);
       $data = str_replace(array('+','/','='),array('-','_',''),$data);
       return $data;
    }

    public static function urlsafe_b64decode($string) {
       $data = str_replace(array('-','_'),array('+','/'),$string);
       $mod4 = strlen($data) % 4;
       if ($mod4) {
           $data .= substr('====', $mod4);
       }
       return base64_decode($data);
    }

    public static function encDir($s){return str_replace (array('/',' '), array('-','_'), $s);}
    public static function decDir($s){return str_replace (array('-','_'), array('/',' '), $s);}
      
    public static function random_str($length = 15){
        return substr(sha1(rand()), 0, $length);
    }

    public static function keys(){

        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $private_key='';                        
        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key into $private_key
        openssl_pkey_export($res, $private_key);         // NEW PRIV KEY

        // Extract the public key into $public_key
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];                // NEW PUB KEY

        //Vars::debug_var(md5($private_key),'priv');
        //Vars::debug_var(md5($public_key),'pub')
        $result = array();
        $result['private'] = $private_key;
        $result['public'] = $public_key;
        return $result;
    }

    /**
     *  Two next encrypt/decrypt funcion can be encrypt decrpt in javascript. Useful for receive encripted forms data
     *
     *  From: https://stackoverflow.com/questions/27677236/encryption-in-javascript-and-decryption-with-php
     *
     */

    //Encryption with public key
    public static function encrypt($source,$pub_key)  {
        $j=0;
        $x=strlen($source)/10;
        $y=floor($x);
        for($i=0;$i<$y;$i++){
            $crypttext='';    
            openssl_public_encrypt(substr($source,$j,10),$crypttext,$pub_key);$j=$j+10;
            $crt.=$crypttext;
            $crt.=":::";
        }
        if((strlen($source)%10)>0){
            openssl_public_encrypt(substr($source,$j),$crypttext,$pub_key);
            $crt.=$crypttext;
        }   
        return($crt);              
    }

    //Decryption with private key
    public static function decrypt($crypttext,$priv_key){
        $tt=explode(":::",$crypttext);
        $cnt=count($tt);
        $i=0;
        while($i<$cnt){
            openssl_private_decrypt($tt[$i],$str1,$priv_key);
            $str.=$str1;
            $i++;
        }
        return $str;     
    }
    
    public static function sign($source,$pub_key) {
        $has=sha1($source);
        $source.="::";
        $source.=$has;
        openssl_public_encrypt($source,$mese,$pub_key);
        return $mese;           
    }
    
    public static function verify($crypttext,$priv_key) {
        openssl_private_decrypt($crypttext,$has1,$priv_key);
        //list($c1,$c2)=split("::",$has1);
        list($c1,$c2)=explode('::', $has1);
        $has=sha1($c1);
        if($has==$c2) {
            $message=$c1;
            return $message;
        }                
    }
 
    /**
     * Decrypt data from a CryptoJS json encoding string
     *
     *
     * @param mixed $passphrase
     * @param mixed $jsonString
     * @return mixed
     */
    public static function cryptoJsAesDecrypt($passphrase, $jsonString){
        $jsondata = json_decode($jsonString, true);
        $salt = hex2bin($jsondata["s"]);
        $ct = base64_decode($jsondata["ct"]);
        $iv  = hex2bin($jsondata["iv"]);
        $concatedPassphrase = $passphrase.$salt;
        $md5 = array();
        $md5[0] = md5($concatedPassphrase, true);
        $result = $md5[0];
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
            $result .= $md5[$i];
        }
        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
        return json_decode($data, true);
    }

    /**
     * Encrypt value to a cryptojs compatible json encoding string
     *
     * @param mixed $passphrase
     * @param mixed $value
     * @return string
     */
    public static function cryptoJsAesEncrypt($passphrase, $value){
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx.$passphrase.$salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        $data = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
        return json_encode($data);
    }
    
    // For encode json before sending to prevent intrusion system alert
    public static function string2json($str) {
      return str_replace(array('_99_','_98_','_96_','_97_'),array('":"','","','{"','"}'),$str);
    }

    // trela metsys noisurtni tneverp ot gnidnes erofeb nosj edocne roF
    public static function json2string($str) {
      return str_replace(array('":"','","','{"','"}'),array('_99_','_98_','_96_','_97_'),$str);
    }
    
    // Decrypt encoded && encrypted string
    public static function crypt2str($encrypted,$key){
        /**
        if (!$encrypted || $encrypted=='') 
            return $encrypted;
        else
            return Crypt::cryptoJsAesDecrypt($key, Crypt::string2json($encrypted));
        **/

        /**/
        if (!$encrypted || $encrypted=='') {
            return $encrypted;
        }else{
            $_encrypted_text = $encrypted;
            $_decrypted_text = Crypt::cryptoJsAesDecrypt($key, Crypt::string2json($_encrypted_text));
            if((!$_decrypted_text || $_decrypted_text=='' ) && strlen($_encrypted_text)>3){
                return NULL;
            }else{
                return $_decrypted_text;
            }
        }
        /**/
    }

    // Encrypt && encode string
    public static function str2crypt($str,$key){
        if (!$str || $str=='')
            return $str;
        else
            return Crypt::string2json(Crypt::cryptoJsAesEncrypt($key, $str));
    }

    public static function file_url($url,$key=false){
        return '/file/'.Crypt::encDir(Crypt::md5_encrypt($url,$key?$key:CFG::$vars['prefix'])); //$_SESSION['token']));
    }



    // PHPNotesForProfessionals.pdf pg 459

    /**
     * Define the number of blocks that should be read from the source file for each chunk.
     * For 'AES-128-CBC' each block consist of 16 bytes.
     * So if we read 10,000 blocks we load 160kb into memory. You may adjust this value
     * to read/write shorter or longer chunks.
     */
    
    
    /**
    * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
    *
    * @param string $source Path to file that should be encrypted
    * @param string $key    The key used for the encryption
    * @param string $dest   File name where the encryped file should be written to.
    * @return string|false  Returns the file name that has been created or FALSE if an error occurred
    */

    public static function encryptFile($source, $key, $dest){
        $key = substr(sha1($key, true), 0, 16);
        $iv = openssl_random_pseudo_bytes(16);
        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            // Put the initialzation vector to the beginning of the file
            fwrite($fpOut, $iv);
            if ($fpIn = fopen($source, 'rb')) {
                while (!feof($fpIn)) {
                    $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $ciphertext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }
        return $error ? false : $dest;
    }

    /**
     * Dencrypt the passed file and saves the result in a new file, removing the
     * last 4 characters from file name.
     *
     * @param string $source Path to file that should be decrypted
     * @param string $key    The key used for the decryption (must be the same as for encryption)
     * @param string $dest   File name where the decryped file should be written to.
     * @return string|false  Returns the file name that has been created or FALSE if an error occurred
     */

    function decryptFile($source, $key, $dest){
        $key = substr(sha1($key, true), 0, 16);
        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            if ($fpIn = fopen($source, 'rb')) {
                // Get the initialzation vector from the beginning of the file
                $iv = fread($fpIn, 16);
                while (!feof($fpIn)) {
                    $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); // we have to read one block more for decrypting than for encrypting
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $plaintext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }
        return $error ? false : $dest;
    }

    /***
     *  How to use
     *
        $fileName = __DIR__.'/testfile.txt';
        $key = 'my secret key';
        file_put_contents($fileName, 'Hello World, here I am.');
        encryptFile($fileName, $key, $fileName . '.enc');
        decryptFile($fileName . '.enc', $key, $fileName . '.dec');

        This will create three files:
        1.testfile.txt with the plain text
        2.testfile.txt.enc with the encrypted file
        3.testfile.txt.dec with the decrypted file. This should have the same content as testfile.txt 

     *
     */


}