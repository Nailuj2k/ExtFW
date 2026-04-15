<?php


       /*
        * 
        *    Location.class
        *    
        *        Connect to a location api and return data like country code, region, etc.
        *        Useful for displaying content by country, region, etc.
        *        At moment services available are ipinfo, geoplugin and freeipapi
        *  
        *    How to use:      
        *
        *        include_once(CLASSES_DIR.'/location.class.php')
        *        $country =  Location::details('freeipapi')->country;
        *        if ($country=='UK') die('blasthelezochampion')
        * 
        *    Demo: 
        *
        *        https://extfw.extralab/test/location
        *
        * 
        * */


    class Location {

        public static $plugin = 'freeipapi';

        public static function ip(){   
           $ip = 0;   
            if (!empty($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"];   
            if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {   
                $iplist = explode(", ", $_SERVER["HTTP_X_FORWARDED_FOR"]);   
                if ($ip) {   
                    array_unshift($iplist, $ip);   
                    $ip = 0;   
                }   
                foreach($iplist as $v)
                    if (!preg_match("/^(192\.168|172\.16|10|224|240|127|0)\./i", $v))   
                        return $v;   
            }   
            return ($ip) ? $ip : $_SERVER["REMOTE_ADDR"];   
        }  

        public static function empty_details($readme_text=''){   
            $ip = self::ip(); 
            return json_decode('{ "ip": "'.$ip.'", "country": "XX","readme":"'.$readme_text.'"}');
        }

        public static function ipinfo_details(){   
            $ip = self::ip(); 
            $details = file_get_contents("https://ipinfo.io/{$ip}/json");
            return $details 
                 ? json_decode($details)
                 : empty_details('No se puede conectar con ipinfo');
        }

        public static function geoplugin_details(){  

            $ip = self::ip(); 
            $details= file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip);

            if($details){

                $arr[]= unserialize($details);

                $geoplugin_details = '{
                         "api": "geoplugin",
                          "ip": "'.$ip.'",
                        "city": "'.$arr[0]['geoplugin_city'].'",
                      "region": "'.$arr[0]['geoplugin_regionName'].', '.$arr[0]['geoplugin_region'].'",
                     "country": "'.$arr[0]['geoplugin_countryCode'].'",
                         "loc": "'.$arr[0]['geoplugin_latitude'].','.$arr[0]['geoplugin_longitude'].'",
                      "postal": "'.$arr[0]['geoplugin_areaCode'].'",
                    "timezone": "'.$arr[0]['geoplugin_timezone'].'"
                }';

                return json_decode($geoplugin_details);

            }else{

                return empty_details('No se puede conectar con geoplugin.net');

            }

        }

        public static function freeipapi_details(){
            $ip = self::ip(); 
            $details = file_get_contents("https://freeipapi.com/api/json/{$ip}");
                       
            if($details){

                $obj = json_decode($details);

                $freeipapi_details = '{
                         "api": "freeipapi",
                          "ip": "'.$ip.'",
                        "city": "'.$obj->cityName.'",
                      "region": "'.$obj->regionName.'",
                     "country": "'.$obj->countryCode.'",
                         "loc": "'.$obj->latitude.','.$obj->longitude.'",
                      "postal": "'.$obj->zipCode.'",
                    "timezone": "'.$obj->timeZones.'"
                }';

                return json_decode($freeipapi_details);

            }else{

                return empty_details('No se puede conectar con freeipapi.com');

            }

        }

        public static function details($plugin=false){  
   
            if($plugin) self::$plugin = $plugin;
            
            switch(self::$plugin){
                case 'ipinfo':
                    return self::ipinfo_details();
                    break;
                case 'geoplugin':
                    return self::geoplugin_details();
                    break;
                case 'freeipapi':
                    return self::freeipapi_details();
                    break;
                default:
                    return self::empty_details();
            }                    

        }

    }